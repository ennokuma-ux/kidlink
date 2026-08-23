<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ActivityAttendance;
use App\Models\Attendance;
use App\Models\GameScore;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index()
    {
        $teacherId = auth()->id();
        $studentIds = Student::where('teacher_id', $teacherId)->pluck('id');

        $attendanceRate = [];
        $weeklyBreakdown = [];
        $performanceRadar = [];
        $ageDistribution = [];
        $enrollmentTrend = [];
        $studentComparison = [];

        $last6Months = collect(range(5, 0))->map(function ($i) {
            return Carbon::today()->subMonths($i)->format('Y-m');
        });

        foreach ($last6Months as $month) {
            $monthStart = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
            $monthEnd = Carbon::createFromFormat('Y-m', $month)->endOfMonth();
            $total = Attendance::whereIn('student_id', $studentIds)
                ->whereBetween('date', [$monthStart, $monthEnd])
                ->count();
            $present = Attendance::whereIn('student_id', $studentIds)
                ->whereBetween('date', [$monthStart, $monthEnd])
                ->where('status', 'Present')
                ->count();
            $attendanceRate[] = [
                'month' => $month,
                'rate' => $total > 0 ? round(($present / $total) * 100) : 0,
            ];
        }

        $weekDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        $weekStart = Carbon::today()->subDays(Carbon::today()->dayOfWeek - 1);
        foreach ($weekDays as $day) {
            $dayDate = $weekStart->copy()->modify($day);
            $total = Attendance::whereIn('student_id', $studentIds)->whereDate('date', $dayDate)->count();
            $present = Attendance::whereIn('student_id', $studentIds)->whereDate('date', $dayDate)->where('status', 'Present')->count();
            $absent = Attendance::whereIn('student_id', $studentIds)->whereDate('date', $dayDate)->where('status', 'Absent')->count();
            $late = Attendance::whereIn('student_id', $studentIds)->whereDate('date', $dayDate)->where('status', 'Late')->count();
            $weeklyBreakdown[] = ['day' => $day, 'present' => $present, 'absent' => $absent, 'late' => $late];
        }

        $students = Student::whereIn('id', $studentIds)->where('is_active', true)->get();
        $ageDistribution = $students->groupBy(function ($s) {
            $age = $s->age;
            return $age >= 5 ? '5+' : ($age >= 4 ? '4' : ($age >= 3 ? '3' : '1-2'));
        })->map(fn($g) => $g->count())->toArray();

        $enrollmentTrend = Student::where('teacher_id', $teacherId)
            ->selectRaw("TO_CHAR(created_at, 'YYYY-MM') as month, COUNT(*) as total")
            ->groupBy('month')->orderBy('month')->get()->toArray();

        $gameTypes = collect(['letters', 'numbers', 'colors', 'shapes', 'animals']);
        foreach ($students as $student) {
            $scores = GameScore::where('student_id', $student->id)
                ->selectRaw('game_type, AVG(score * 100.0 / total) as avg_score')
                ->groupBy('game_type')
                ->pluck('avg_score', 'game_type');
            $studentComparison[] = [
                'name' => $student->name,
                'scores' => $gameTypes->mapWithKeys(fn($t) => [$t => round($scores[$t] ?? 0)])->toArray(),
            ];
        }

        $performanceRadar = ['Gross Motor' => 70, 'Fine Motor' => 65, 'Cognitive' => 80, 'Language' => 75, 'Social' => 60];

        $recommendations = [
            ['name' => 'Juan', 'needs' => 'Colors, Numbers', 'plan' => 'Color matching games, counting exercises'],
            ['name' => 'Maria', 'needs' => 'Letters, Motor Skills', 'plan' => 'Letter tracing, fine motor activities'],
            ['name' => 'Jose', 'needs' => 'Shapes, Animals', 'plan' => 'Shape sorting, animal flashcards'],
        ];

        return view('teacher.analytics', compact(
            'attendanceRate', 'weeklyBreakdown', 'performanceRadar',
            'ageDistribution', 'enrollmentTrend', 'studentComparison', 'recommendations'
        ));
    }
}
