<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $teacher = auth()->user();
        $teacherId = $teacher->id;
        $date = $request->date ? Carbon::parse($request->date) : Carbon::today();

        $students = Cache::remember("teacher.{$teacherId}.students", 300, function () use ($teacherId) {
            return Student::where('teacher_id', $teacherId)
                ->where('is_active', true)
                ->orderBy('first_name')
                ->get();
        });

        // Only the sections this teacher actually has, canonical order first so
        // any legacy class_name still shows up rather than becoming unreachable.
        $availableSections = $students->pluck('class_name')
            ->filter()
            ->unique()
            ->sortBy(fn ($name) => ($index = array_search($name, Student::SECTIONS, true)) === false
                ? count(Student::SECTIONS)
                : $index)
            ->values();

        $search = trim((string) $request->query('search', ''));
        $section = (string) $request->query('section', '');

        // A stale bookmark should not silently blank the table.
        if ($section !== '' && !in_array($section, $availableSections->all(), true)) {
            $section = '';
        }

        // The attendance cache stays keyed by teacher + date only, so switching
        // filters never re-queries; filtering happens over the cached students.
        $cacheKey = "teacher.{$teacherId}.attendance.{$date->format('Y-m-d')}";
        $attendance = Cache::remember($cacheKey, 60, function () use ($date, $students) {
            return Attendance::whereDate('date', $date)
                ->whereIn('student_id', $students->pluck('id'))
                ->get()
                ->keyBy('student_id');
        });

        $filtered = $students->filter(function ($student) use ($search, $section) {
            if ($section !== '' && $student->class_name !== $section) {
                return false;
            }

            if ($search !== '') {
                $haystack = strtolower(trim($student->name . ' ' . $student->first_name . ' ' . $student->last_name));
                if (!str_contains($haystack, strtolower($search))) {
                    return false;
                }
            }

            return true;
        })->values();

        $countStatus = fn ($status) => $filtered
            ->filter(fn ($student) => optional($attendance->get($student->id))->status === $status)
            ->count();

        $stats = [
            'total' => $filtered->count(),
            'present' => $countStatus('Present'),
            'absent' => $countStatus('Absent'),
            'late' => $countStatus('Late'),
        ];

        return view('teacher.attendance', [
            'students' => $filtered,
            'totalStudents' => $students->count(),
            'attendance' => $attendance,
            'date' => $date,
            'stats' => $stats,
            'search' => $search,
            'section' => $section,
            'availableSections' => $availableSections,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*' => 'required|in:Present,Absent,Late,Excused,None',
            'search' => 'nullable|string|max:255',
            'section' => 'nullable|string|max:255',
        ]);

        $teacher = auth()->user();
        $teacherId = $teacher->id;
        $date = $request->date;

        foreach ($request->attendance as $studentId => $status) {
            Attendance::updateOrCreate(
                ['student_id' => $studentId, 'date' => $date],
                ['status' => $status, 'marked_by' => $teacherId]
            );
        }

        $cacheKey = "teacher.{$teacherId}.attendance.{$date}";
        Cache::forget($cacheKey);

        // Keep the teacher where they were: same date, same search, same section.
        return redirect()->route('teacher.attendance.index', array_filter([
            'date' => $date,
            'search' => $request->input('search'),
            'section' => $request->input('section'),
        ]))
            ->with('success', 'Attendance saved!');
    }
}
