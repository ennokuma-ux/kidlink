<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\ActivityAttendance;
use App\Models\Attendance;
use App\Models\GameScore;
use App\Models\Student;
use App\Services\AnalyticsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class DashboardController extends Controller
{
    /**
     * How the student level rows can be ordered. "performance" leaves the
     * ranking the service already produced untouched.
     */
    public const SORT_OPTIONS = ['section', 'name', 'performance'];

    public function __construct(private AnalyticsService $analytics) {}

    public function index(Request $request)
    {
        $teacherId = auth()->id();
        $today = Carbon::today();

        $allStudents = Student::where('teacher_id', $teacherId)->where('is_active', true)->get();

        // Headline cards: today's register and this week's activities.
        $totalStudents = $allStudents->count();

        $todayAttendance = Attendance::whereDate('date', $today)
            ->whereIn('student_id', $allStudents->pluck('id'))
            ->get();
        $presentToday = $todayAttendance->where('status', 'Present')->count();
        $absentToday = $todayAttendance->where('status', 'Absent')->count();

        $weekStart = $today->copy()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $weekStart->copy()->addDays(6);

        $activitiesToday = Activity::where('teacher_id', $teacherId)
            ->where('day_of_week', $today->format('l'))
            ->whereBetween('week_start', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->count();

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
        // costs a handful of cheap queries instead of the full analytics load.
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
            return view('teacher.analytics-select', [
                'totalStudents' => $totalStudents,
                'presentToday' => $presentToday,
                'absentToday' => $absentToday,
                'activitiesToday' => $activitiesToday,
                'availableSections' => $availableSections,
                'allStudents' => $allStudents->sortBy('name')->values(),
            ]);
        }

        $students = $section === ''
            ? $allStudents->values()
            : $allStudents->where('class_name', $section)->values();

        // The per-section rollup compares classes, so it must never be narrowed
        // by the section or single-student filter: it always counts every class
        // this teacher has.
        $allStudentIds = $allStudents->pluck('id')->all();

        // The intervention panel follows the section filter but not the single
        // student, so it names every at-risk child in the chosen class.
        $sectionStudentIds = $students->pluck('id')->all();

        // Optional single-student view. Only ever resolved against this
        // teacher's own active students, so a guessed id cannot widen access.
        $studentOptions = $students; // section-scoped, drives the dropdown
        $selectedStudent = null;
        $studentId = $request->query('student');
        if ($studentId !== null && $studentId !== '') {
            $selectedStudent = $studentOptions->firstWhere('id', (int) $studentId);
        }

        if ($selectedStudent) {
            $students = collect([$selectedStudent]);
        }

        $studentIds = $students->pluck('id')->all();

        // Lets every student level row be labelled and ordered by section
        // without the service having to know about sections.
        $sectionByStudent = $allStudents
            ->mapWithKeys(fn (Student $student) => [$student->id => $student->class_name])
            ->all();

        $summary = $this->analytics->summary($studentIds);
        $performanceRadar = $this->analytics->radar($studentIds);
        $domainStatus = $this->analytics->domainStatusBreakdown($studentIds);
        $attendanceRate = $this->analytics->attendanceRateTrend($studentIds, 6);
        $attendancePeriod = $this->analytics->attendancePeriod($studentIds, (string) $request->query('att_mode', 'week'), $request->query('att_date'));
        $weeklyBreakdown = $attendancePeriod['rows'];
        $attendanceKeep = collect($request->query())->except(['att_mode', 'att_date'])->filter(fn ($value) => is_scalar($value))->all();
        $gamePerformance = $this->analytics->gamePerformance($studentIds);
        $gamePlayTrend = $this->analytics->gamePlayTrend($studentIds, 8);
        // Independent selectors for category scores and individual level progression.
        $scoreSection = trim((string) $request->query('score_section', $section));
        if ($scoreSection !== '' && ! in_array($scoreSection, $availableSections, true)) $scoreSection = '';
        $scoreStudentId = $request->query('score_student', $selectedStudent?->id);
        $scoreStudents = $allStudents;
        if ($scoreSection !== '') $scoreStudents = $scoreStudents->where('class_name', $scoreSection);
        $scoreStudent = $scoreStudents->firstWhere('id', (int) $scoreStudentId);
        $scoreIds = $scoreStudent ? [$scoreStudent->id] : $scoreStudents->pluck('id')->all();
        $categoryScores = $this->analytics->studentCategoryBreakdown($scoreIds);
        $sectionScores = [];

        $levelStudentOptions = $allStudents->sortBy('name')->values();
        $levelStudentId = $request->query('level_student', $selectedStudent?->id);
        $levelStudent = $allStudents->firstWhere('id', (int) $levelStudentId);
        $levelIds = $levelStudent ? [$levelStudent->id] : [];
        $recommendations = $this->analytics->studentRecommendations($studentIds, 6);
        $topicStandings = array_values(array_filter(
            $this->analytics->topicStandings($studentIds),
            fn ($row) => in_array($row['basis'], ['Game', 'Domain'], true)
        ));
        $topicType = strtolower((string) $request->query('topic_type', 'all'));
        if (! in_array($topicType, ['all', 'game', 'eccd'], true)) $topicType = 'all';
        if ($topicType === 'game') $topicStandings = array_values(array_filter($topicStandings, fn ($row) => $row['basis'] === 'Game'));
        elseif ($topicType === 'eccd') $topicStandings = array_values(array_filter($topicStandings, fn ($row) => $row['basis'] === 'Domain'));
        // Leaderboards rank the whole chosen class (the section filter applies, a single
        // selected child does not), so one child can still be seen against classmates.
        $topicLeaderboards = $this->analytics->topicLeaderboards($sectionStudentIds, $sectionByStudent);

        // The narrative blurb talks about the child on screen, so it keeps the
        // student-scoped flags.
        $studentInterventions = $this->analytics->interventionFlags($studentIds);

        // The intervention panel is a class-wide view: it follows the section
        // filter but keeps naming every child who needs action even when one
        // student has been selected for inspection.
        $interventions = $this->analytics->interventionFlags($sectionStudentIds);
        $interventionCount = count(array_filter($interventions, fn ($flag) => $flag['intervention_required']));

        $enrollmentTrend = $this->enrollmentTrend($teacherId, $section, $selectedStudent?->id);
        $ageDistribution = $this->ageDistribution($students);
        $levelProgress = $this->levelProgress($levelIds);

        $recommendations = $this->analytics->sortStudentRows($recommendations, $sort, $sectionByStudent);
        $interventions = $this->analytics->sortStudentRows($interventions, $sort, $sectionByStudent);
        $interventionPerPage = 7;
        $interventionTotal = count($interventions);
        $interventionLastPage = max(1, (int) ceil($interventionTotal / $interventionPerPage));
        // Keep page navigation inside the valid range so a stale page URL never
        // produces an empty panel while intervention records still exist.
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
        $categoryScores = $this->analytics->sortStudentRows($categoryScores, $sort);
        $sectionLegend = $this->analytics->sectionLegend($allStudents);

        return view('teacher.dashboard', compact(
            'totalStudents',
            'presentToday',
            'absentToday',
            'activitiesToday',
            'students',
            'studentOptions',
            'selectedStudent',
            'summary',
            'attendanceRate',
            'weeklyBreakdown',
            'attendancePeriod',
            'attendanceKeep',
            'performanceRadar',
            'domainStatus',
            'enrollmentTrend',
            'ageDistribution',
            'gamePerformance',
            'gamePlayTrend',
            'categoryScores',
            'sectionScores',
            'levelProgress',
            'scoreSection',
            'scoreStudent',
            'scoreStudents',
            'levelStudent',
            'levelStudentOptions',
            'topicType',
            'recommendations',
            'interventions',
            'studentInterventions',
            'interventionCount',
            'topicStandings',
            'topicLeaderboards',
            'scope',
            'section',
            'sort',
            'availableSections',
            'sectionByStudent',
            'sectionLegend'
        ));
    }

    private function enrollmentTrend(int $teacherId, string $section = '', ?int $studentId = null): array
    {
        $series = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::today()->startOfMonth()->subMonths($i);
            $query = Student::where('teacher_id', $teacherId)
                ->whereBetween('enrolled_date', [$month, $month->copy()->endOfMonth()]);

            if ($section !== '') {
                $query->where('class_name', $section);
            }

            // A single child enrolled once, so the trend collapses to the one
            // month they joined rather than repeating the whole class.
            if ($studentId !== null) {
                $query->whereKey($studentId);
            }

            $series[] = [
                'month' => $month->format('M Y'),
                'total' => (clone $query)->count(),
            ];
        }

        return $series;
    }

    private function ageDistribution($students): array
    {
        $buckets = ['2 years' => 0, '3 years' => 0, '4 years' => 0, '5 years' => 0, '6+ years' => 0];

        foreach ($students as $student) {
            $age = $student->age;
            if ($age <= 2) {
                $buckets['2 years']++;
            } elseif ($age === 3) {
                $buckets['3 years']++;
            } elseif ($age === 4) {
                $buckets['4 years']++;
            } elseif ($age === 5) {
                $buckets['5 years']++;
            } else {
                $buckets['6+ years']++;
            }
        }

        return $buckets;
    }

    private function activityPerformance(array $studentIds): array
    {
        if (empty($studentIds)) {
            return [];
        }

        return ActivityAttendance::whereIn('student_id', $studentIds)
            ->join('activities', 'activity_attendance.activity_id', '=', 'activities.id')
            ->selectRaw('activities.type, COUNT(*) as attempts, AVG(activity_attendance.score) as avg_score')
            ->groupBy('activities.type')
            ->orderByDesc('avg_score')
            ->get()
            ->map(fn($row) => [
                'type' => $row->type,
                'attempts' => (int) $row->attempts,
                'avg_score' => (float) $row->avg_score,
            ])
            ->values()
            ->all();
    }

    /**
     * How many students have reached each level, per game.
     */
    private function levelProgress(array $studentIds): array
    {
        if (empty($studentIds)) {
            return [];
        }

        $rows = GameScore::whereIn('student_id', $studentIds)
            ->selectRaw('game_type, level, COUNT(DISTINCT student_id) as students')
            ->groupBy('game_type', 'level')
            ->get()
            ->groupBy('game_type');

        $progress = [];
        foreach (AnalyticsService::GAME_TYPES as $game) {
            $levels = [];
            foreach ($rows->get($game, collect()) as $row) {
                $levels[(int) $row->level] = (int) $row->students;
            }
            ksort($levels);
            $progress[$game] = $levels;
        }

        return $progress;
    }
}
