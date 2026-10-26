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
        $weeklyBreakdown = $this->analytics->weeklyBreakdown($studentIds);
        $gamePerformance = $this->analytics->gamePerformance($studentIds);
        $gamePlayTrend = $this->analytics->gamePlayTrend($studentIds, 8);
        $studentComparison = $this->analytics->gameMasteryByStudent($studentIds);
        $categoryScores = $this->analytics->studentCategoryBreakdown($studentIds);
        $sectionScores = $this->analytics->sectionScoreBreakdown($allStudentIds);
        $recommendations = $this->analytics->studentRecommendations($studentIds, 6);
        $topicStandings = $this->analytics->topicStandings($studentIds);

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
        $activityPerformance = $this->activityPerformance($studentIds);
        $levelProgress = $this->levelProgress($studentIds);

        $recommendations = $this->analytics->sortStudentRows($recommendations, $sort, $sectionByStudent);
        $interventions = $this->analytics->sortStudentRows($interventions, $sort, $sectionByStudent);
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
            'performanceRadar',
            'domainStatus',
            'enrollmentTrend',
            'ageDistribution',
            'activityPerformance',
            'gamePerformance',
            'gamePlayTrend',
            'studentComparison',
            'categoryScores',
            'sectionScores',
            'levelProgress',
            'recommendations',
            'interventions',
            'studentInterventions',
            'interventionCount',
            'topicStandings',
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
