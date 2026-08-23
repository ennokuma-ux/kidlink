<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Attendance;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $teacherId = auth()->id();
        $today = Carbon::today();

        $totalStudents = Student::where('teacher_id', $teacherId)->where('is_active', true)->count();

        $todayAttendance = Attendance::whereDate('date', $today)
            ->whereIn('student_id', Student::where('teacher_id', $teacherId)->pluck('id'))
            ->get();
        $presentToday = $todayAttendance->where('status', 'Present')->count();
        $absentToday = $todayAttendance->where('status', 'Absent')->count();

        $activitiesToday = Activity::where('teacher_id', $teacherId)
            ->where('day_of_week', $today->format('l'))
            ->count();

        $weeklyAttendance = Attendance::whereBetween('date', [$today->copy()->startOfWeek(), $today->copy()->endOfWeek()])
            ->whereIn('student_id', Student::where('teacher_id', $teacherId)->pluck('id'))
            ->selectRaw("DATE(date) as date, COUNT(*) as total, SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present")
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $enrollmentTrend = Student::where('teacher_id', $teacherId)
            ->selectRaw("TO_CHAR(created_at, 'YYYY-MM') as month, COUNT(*) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $ageDistribution = Student::where('teacher_id', $teacherId)
            ->where('is_active', true)
            ->get()
            ->groupBy(function ($s) {
                $age = $s->age;
                return $age >= 5 ? '5+' : ($age >= 4 ? '4' : ($age >= 3 ? '3' : ($age >= 2 ? '2' : '1')));
            })
            ->map(fn($g) => $g->count());

        $activityCompletion = Activity::where('teacher_id', $teacherId)
            ->selectRaw("type, COUNT(*) as total, SUM(CASE WHEN is_completed THEN 1 ELSE 0 END) as completed")
            ->groupBy('type')
            ->get();

        return view('teacher.dashboard', compact(
            'totalStudents', 'presentToday', 'absentToday', 'activitiesToday',
            'weeklyAttendance', 'enrollmentTrend', 'ageDistribution', 'activityCompletion'
        ));
    }
}
