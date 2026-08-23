<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $teacherId = auth()->id();
        $date = $request->date ? Carbon::parse($request->date) : Carbon::today();

        $students = Student::where('teacher_id', $teacherId)
            ->where('is_active', true)
            ->orderBy('first_name')
            ->get();

        $attendance = Attendance::whereDate('date', $date)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->keyBy('student_id');

        $stats = [
            'total' => $students->count(),
            'present' => $attendance->where('status', 'Present')->count(),
            'absent' => $attendance->where('status', 'Absent')->count(),
            'late' => $attendance->where('status', 'Late')->count(),
        ];

        return view('teacher.attendance', compact('students', 'attendance', 'date', 'stats'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*' => 'required|in:Present,Absent,Late,Excused',
        ]);

        $teacherId = auth()->id();
        $date = $request->date;

        foreach ($request->attendance as $studentId => $status) {
            Attendance::updateOrCreate(
                ['student_id' => $studentId, 'date' => $date],
                ['status' => $status, 'marked_by' => $teacherId]
            );
        }

        return redirect()->route('teacher.attendance.index', ['date' => $date])
            ->with('success', 'Attendance saved!');
    }
}
