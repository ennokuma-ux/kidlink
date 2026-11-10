<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class AnalyticsController extends Controller
{
    /**
     * How the student level tables can be ordered. "performance" leaves the
     * ranking the query produced untouched.
     */
    public const SORT_OPTIONS = ['section', 'name', 'performance'];

    public function __construct(private AnalyticsService $analytics) {}

    public function index(Request $request)
    {
        $allStudents = Student::where('is_active', true)->get();
        $availableSections = $this->analytics->availableSections($allStudents);

        $section = trim((string) $request->query('section', ''));
        if ($section !== '' && ! in_array($section, $availableSections, true)) {
            $section = '';
        }

        $sort = (string) $request->query('sort', 'section');
        if (! in_array($sort, self::SORT_OPTIONS, true)) {
            $sort = 'section';
        }

        // The analytics open on a choice: everyone, one section, or one child.
        // Nothing below runs until one has been made, so the selection screen
        // stays cheap.
        $studentParam = $request->query('student');
        $hasStudent = $studentParam !== null && $studentParam !== ''
            && $allStudents->contains('id', (int) $studentParam);

        $scope = match (true) {
            $hasStudent => 'individual',
            $section !== '' => 'section',
            $request->query('scope') === 'all' => 'all',
            default => null,
        };

        if ($scope === null) {
            return view('admin.analytics-select', [
                'availableSections' => $availableSections,
                'allStudents' => $allStudents->sortBy('name')->values(),
            ]);
        }

        $students = $section === ''
            ? $allStudents->values()
            : $allStudents->where('class_name', $section)->values();

        // The intervention panel follows the section filter but must not
        // collapse to one child. Captured before the single-student narrowing.
        $sectionScopedIds = $students->pluck('id')->all();

        // The per-class table compares classes, so it is never narrowed by the
        // section or student filter: it always counts every active enrolment.
        $allScoredIds = $allStudents->pluck('id')->all();

        // Optional single-student view, resolved only against active enrolments
        // so a guessed id cannot pull in an archived or unknown child.
        $studentOptions = $students;
        $selectedStudent = null;
        $studentId = $request->query('student');
        if ($studentId !== null && $studentId !== '') {
            $selectedStudent = $studentOptions->firstWhere('id', (int) $studentId);
        }

        if ($selectedStudent) {
            $students = collect([$selectedStudent]);
        }

        // null keeps the service's whole-system semantics untouched when no
        // section is picked; an empty array would mean "nobody" and zero the page.
        $scopedIds = match (true) {
            (bool) $selectedStudent => [$selectedStudent->id],
            $section !== '' => $students->pluck('id')->all(),
            default => null,
        };

        // Recommendations take a plain list, so it needs a real id set.
        $recommendationIds = $scopedIds ?? $allStudents->pluck('id')->all();

        $system = $this->analytics->systemSummary();
        $summary = $this->analytics->summary($scopedIds);

        $roleGrowth = $this->analytics->roleGrowthTrend(6);
        $enrollmentTrend = $this->analytics->enrollmentTrend(6, $scopedIds);
        $attendanceRate = $this->analytics->attendanceRateTrend($scopedIds, 6);
        $attendancePeriod = $this->analytics->attendancePeriod($scopedIds, (string) $request->query('att_mode', 'week'), $request->query('att_date'));
        $weeklyBreakdown = $attendancePeriod['rows'];
        $attendanceKeep = collect($request->query())->except(['att_mode', 'att_date'])->filter(fn ($value) => is_scalar($value))->all();
        // Use the same per-game attempt and accuracy calculation as the teacher dashboard.
        $analyticsStudentIds = $scopedIds ?? $allStudents->pluck('id')->all();
        $gamePerformance = $this->analytics->gamePerformance($analyticsStudentIds);
        $domainMastery = $this->analytics->globalDomainMastery($scopedIds);
        $domainStatus = $this->analytics->domainStatusBreakdown($scopedIds);
        $gamePlayTrend = $this->analytics->gamePlayTrend($scopedIds, 8);
        $teacherWorkload = $this->analytics->teacherWorkload(10);
        $topStudents = $this->analytics->topStudents(8, $scopedIds);
        $needsSupport = $this->analytics->studentsNeedingSupport(8, $scopedIds);
        // "Students per class" compares classes, so it is never narrowed by the
        // section or student filter: it always counts every active enrolment.
        $classDistribution = $this->analytics->classDistribution(null);
        $recommendations = $this->analytics->studentRecommendations($recommendationIds, 6);

        // The intervention panel follows the active analytics scope. When an
        // individual child is selected, show only that child's intervention
        // flags; otherwise show the selected section or all active students.
        $interventionStudentIds = $selectedStudent
            ? [$selectedStudent->id]
            : $sectionScopedIds;
        $interventions = $this->analytics->interventionFlags($interventionStudentIds);
        $interventionCount = count(array_filter($interventions, fn ($flag) => $flag['intervention_required']));
        $interventions = $this->analytics->sortStudentRows($interventions, $sort);
        $interventionPerPage = 7;
        $interventionTotal = count($interventions);
        $interventionLastPage = max(1, (int) ceil($interventionTotal / $interventionPerPage));
        $interventionPage = min(
            max(1, (int) $request->query('intervention_page', 1)),
            $interventionLastPage
        );
        $interventions = new LengthAwarePaginator(
            array_slice($interventions, ($interventionPage - 1) * $interventionPerPage, $interventionPerPage),
            $interventionTotal,
            $interventionPerPage,
            $interventionPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
                'pageName' => 'intervention_page',
            ]
        );

        // Per-category scores for the leaderboard tables. The scoped list is
        // only known when filtering, so fall back to every active enrolment.
        $breakdownIds = $scopedIds ?? $recommendationIds;
        $breakdown = collect($this->analytics->studentCategoryBreakdown($breakdownIds))
            ->keyBy('student_id');
        $sectionScores = $this->analytics->sectionScoreBreakdown($allScoredIds);

        $attachScores = function (array $rows) use ($breakdown) {
            return array_map(function (array $row) use ($breakdown) {
                $scores = $breakdown->get($row['student_id']);

                return array_merge($row, [
                    'games' => $scores['games'] ?? [],
                    'domain_overall' => $scores['domain_overall'] ?? null,
                    'total_points' => $scores['total_points'] ?? 0,
                ]);
            }, $rows);
        };

        $topStudents = $attachScores($topStudents);
        $needsSupport = $attachScores($needsSupport);

        $topStudents = $this->analytics->sortStudentRows($topStudents, $sort);
        $needsSupport = $this->analytics->sortStudentRows($needsSupport, $sort);

        $sectionLegend = $this->analytics->sectionLegend($allStudents);
        $totalStudents = $allStudents->count();
        $dataQuality = [
            ['label' => __('analytics.dq_no_teacher'), 'count' => $system['unassigned_students'], 'severity' => $system['unassigned_students'] > 0 ? 'high' : 'ok'],
            ['label' => __('analytics.dq_no_parent'), 'count' => $system['unlinked_students'], 'severity' => $system['unlinked_students'] > 0 ? 'medium' : 'ok'],
            ['label' => __('analytics.dq_inactive_users'), 'count' => $system['inactive_users'], 'severity' => $system['inactive_users'] > 0 ? 'low' : 'ok'],
            ['label' => __('analytics.dq_teachers_no_students'), 'count' => max($system['teachers'] - $system['teachers_with_students'], 0), 'severity' => ($system['teachers'] - $system['teachers_with_students']) > 0 ? 'low' : 'ok'],
        ];

        $averageAge = $this->analytics->averageAge($scopedIds);
        $describe = fn($pct) => $this->analytics->describe((float) $pct);

        return view('admin.analytics', compact(
            'students',
            'studentOptions',
            'selectedStudent',
            'system',
            'summary',
            'roleGrowth',
            'enrollmentTrend',
            'attendanceRate',
            'weeklyBreakdown',
            'attendancePeriod',
            'attendanceKeep',
            'gamePerformance',
            'domainMastery',
            'domainStatus',
            'gamePlayTrend',
            'teacherWorkload',
            'topStudents',
            'needsSupport',
            'classDistribution',
            'sectionScores',
            'recommendations',
            'interventions',
            'interventionCount',
            'dataQuality',
            'averageAge',
            'describe',
            'scope',
            'section',
            'sort',
            'availableSections',
            'sectionLegend',
            'totalStudents'
        ));
    }
}
