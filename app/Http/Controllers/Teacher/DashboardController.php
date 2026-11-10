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
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    /**
     * How the student level rows can be ordered. "performance" leaves the
     * ranking the service already produced untouched.
     */
    public const SORT_OPTIONS = ['section', 'name', 'performance'];

    /** Students per page in the "score by category per student" table. */
    public const SCORES_PER_PAGE = 8;

    /** Fragments the dashboard can reload on their own, without recomputing the page. */
    public const PARTIALS = ['scores', 'levels'];

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
        // The two ECCD charts only exist for a single child, so a group view
        // never pays for their queries.
        $performanceRadar = $selectedStudent ? $this->analytics->radar($studentIds) : [];
        $domainStatus = $selectedStudent ? $this->analytics->domainStatusBreakdown($studentIds) : [];
        $attendanceRate = $this->analytics->attendanceRateTrend($studentIds, 6);

        // Per-student attendance listed under the trend chart (paged in the browser).
        $attendanceStudents = $this->analytics->attendanceByStudent(
            $this->analytics->orderStudents($students, $sort)
        );
        if ($sort === 'performance') {
            // Lowest rate first so the children who need a look come up first;
            // children with no records have no rate to rank and go last.
            usort($attendanceStudents, fn ($a, $b) => [$a['rate'] === null, $a['rate'] ?? 0] <=> [$b['rate'] === null, $b['rate'] ?? 0]);
        }
        $attendancePeriod = $this->analytics->attendancePeriod($studentIds, (string) $request->query('att_mode', 'week'), $request->query('att_date'));
        $weeklyBreakdown = $attendancePeriod['rows'];
        $attendanceKeep = collect($request->query())->except(['att_mode', 'att_date'])->filter(fn ($value) => is_scalar($value))->all();
        $gamePerformance = $this->analytics->gamePerformance($studentIds);
        $gamePlayTrend = $this->analytics->gamePlayTrend($studentIds, 8);
        // Independent selectors for category scores and individual level progression.
        // Only the first page of students is scored; the other pages are fetched
        // on demand through partial() so they cost nothing until they are opened.
        $scores = $this->scoreData($request, $allStudents, $availableSections, $section, $selectedStudent, $sort);

        $levelStudentOptions = $allStudents->sortBy('name')->values();
        $levelStudentId = $request->query('level_student', $selectedStudent?->id);
        $levelStudent = $allStudents->firstWhere('id', (int) $levelStudentId);
        $levelProgress = $this->levelProgress($levelStudent ? [$levelStudent->id] : []);
        $recommendations = $this->analytics->studentRecommendations($studentIds, 6);
        // Standings and leaderboards are group views: the page hides them for a
        // single child, so they are not computed for one. The standings table
        // holds every game/ECCD row and is filtered in the browser, so changing
        // its filter never reaches the server.
        $topicStandings = [];
        $topicLeaderboards = [];
        if (! $selectedStudent) {
            $topicStandings = $this->analytics->topicStandings($studentIds, 4, ['Game', 'Domain']);
            // Leaderboards rank the whole chosen class (the section filter applies, a single
            // selected child does not), so one child can still be seen against classmates.
            $topicLeaderboards = $this->analytics->topicLeaderboards($sectionStudentIds, $sectionByStudent);
        }
        $topicType = strtolower((string) $request->query('topic_type', 'all'));
        if (! in_array($topicType, ['all', 'game', 'eccd'], true)) $topicType = 'all';

        // The narrative blurb talks about the child on screen, so it keeps the
        // student-scoped flags.
        // Only the single-child narrative reads these flags.
        $studentInterventions = $selectedStudent ? $this->analytics->interventionFlags($studentIds) : [];

        // The intervention panel is a class-wide view: it follows the section
        // filter but keeps naming every child who needs action even when one
        // student has been selected for inspection.
        $interventions = $this->analytics->interventionFlags($sectionStudentIds);
        $interventionCount = count(array_filter($interventions, fn ($flag) => $flag['intervention_required']));

        // Both charts are group-only (hidden for one child).
        $enrollmentTrend = $selectedStudent ? [] : $this->enrollmentTrend($teacherId, $section);
        $ageDistribution = $selectedStudent ? [] : $this->ageDistribution($students);

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
        $sectionLegend = $this->analytics->sectionLegend($allStudents);

        return view('teacher.dashboard', $scores + compact(
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
            'attendanceStudents',
            'levelProgress',
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

    private function enrollmentTrend(int $teacherId, string $section = ''): array
    {
        $first = Carbon::today()->startOfMonth()->subMonths(5);

        $query = Student::where('teacher_id', $teacherId)
            ->whereBetween('enrolled_date', [$first, Carbon::today()->endOfMonth()]);

        if ($section !== '') {
            $query->where('class_name', $section);
        }

        // One query for the six months, counted per month here.
        $perMonth = [];
        foreach ($query->toBase()->get(['enrolled_date']) as $row) {
            $key = Carbon::parse($row->enrolled_date)->format('Y-m');
            $perMonth[$key] = ($perMonth[$key] ?? 0) + 1;
        }

        $series = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::today()->startOfMonth()->subMonths($i);
            $series[] = [
                'month' => $month->format('M Y'),
                'total' => $perMonth[$month->format('Y-m')] ?? 0,
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
            ->toBase()
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

    /**
     * Reloads one piece of the dashboard on its own. The page's filters send
     * only the fragment they change, so picking a student or turning a page
     * runs a couple of small queries instead of rebuilding every chart, table
     * and ranking on the page.
     */
    public function partial(Request $request, string $name)
    {
        abort_unless(in_array($name, self::PARTIALS, true), 404);

        $teacherId = auth()->id();

        if ($name === 'levels') {
            // One child at a time: look that child up directly rather than
            // loading the whole class just to find them.
            $levelId = $request->query('level_student', $request->query('student'));
            $levelStudent = ($levelId !== null && $levelId !== '')
                ? Student::where('teacher_id', $teacherId)->where('is_active', true)
                    ->find((int) $levelId, ['id', 'first_name', 'last_name', 'full_name'])
                : null;

            return view('teacher.dashboard.partials.levels-body', [
                'levelStudent' => $levelStudent,
                'levelProgress' => $this->levelProgress($levelStudent ? [$levelStudent->id] : []),
            ]);
        }

        // Only the columns the score table reads.
        $allStudents = Student::where('teacher_id', $teacherId)->where('is_active', true)
            ->get(['id', 'first_name', 'last_name', 'full_name', 'class_name']);
        $availableSections = $this->analytics->availableSections($allStudents);

        $section = trim((string) $request->query('section', ''));
        if ($section !== '' && ! in_array($section, $availableSections, true)) {
            $section = '';
        }

        $sort = (string) $request->query('sort', 'section');
        if (! in_array($sort, self::SORT_OPTIONS, true)) {
            $sort = 'section';
        }

        $studentParam = $request->query('student');
        $selectedStudent = ($studentParam !== null && $studentParam !== '')
            ? ($section === '' ? $allStudents : $allStudents->where('class_name', $section))->firstWhere('id', (int) $studentParam)
            : null;

        return view('teacher.dashboard.partials.scores',
            $this->scoreData($request, $allStudents, $availableSections, $section, $selectedStudent, $sort)
            + ['availableSections' => $availableSections]
        );
    }

    /**
     * Everything the category score table needs. Students are ordered and cut
     * to the requested page first, so only that page's scores are ever loaded.
     */
    private function scoreData(
        Request $request,
        Collection $allStudents,
        array $availableSections,
        string $section,
        ?Student $selectedStudent,
        string $sort
    ): array {
        $scoreSection = trim((string) $request->query('score_section', $section));
        if ($scoreSection !== '' && ! in_array($scoreSection, $availableSections, true)) {
            $scoreSection = '';
        }

        $scoreStudentId = $request->query('score_student', $selectedStudent?->id);
        $scoreStudents = $scoreSection !== '' ? $allStudents->where('class_name', $scoreSection) : $allStudents;
        $scoreStudent = $scoreStudents->firstWhere('id', (int) $scoreStudentId);

        $ordered = $this->analytics->orderStudents(
            $scoreStudent ? collect([$scoreStudent]) : $scoreStudents->values(),
            $sort
        );

        $perPage = self::SCORES_PER_PAGE;
        $total = $ordered->count();
        $page = min(max(1, (int) $request->query('score_page', 1)), max(1, (int) ceil($total / $perPage)));
        $pageStudents = $ordered->slice(($page - 1) * $perPage, $perPage)->values();

        $rows = $this->analytics->studentCategoryBreakdown($pageStudents->pluck('id')->all(), $pageStudents);

        $pager = new LengthAwarePaginator($rows, $total, $perPage, $page, [
            'path' => route('teacher.dashboard'),
            'pageName' => 'score_page',
            'query' => $request->except('score_page'),
        ]);

        return [
            'scoreRows' => $rows,
            'scorePager' => $pager,
            'scoreSection' => $scoreSection,
            'scoreStudent' => $scoreStudent,
            'scoreStudents' => $scoreStudents,
        ];
    }
}
