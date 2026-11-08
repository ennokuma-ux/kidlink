<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;

class IndividualAnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeTeacher();

        $query = trim((string) $request->query('q', ''));

        // TODO: Replace this query with your existing teacher-to-student relationship/scope.
        // Do not expose every student if teachers should only access assigned students.
        $students = Student::query()
            ->when($query !== '', function ($builder) use ($query) {
                $builder->where(function ($search) use ($query) {
                    $search->where('full_name', 'like', '%' . $query . '%')
                        ->orWhere('nickname', 'like', '%' . $query . '%');
                });
            })
            ->orderBy('full_name')
            ->paginate(10);

        return view('teacher.analytics.individual', compact('students', 'query'));
    }

    public function show(int $studentId)
    {
        $this->authorizeTeacher();

        // TODO: Restrict this lookup through the signed-in teacher's permitted-student relationship.
        $student = Student::query()->findOrFail($studentId);

        // TODO: Replace with the analytics calculation/service already used by KidLink.
        $analytics = [];

        return view('teacher.analytics.individual-show', compact('student', 'analytics'));
    }

    private function authorizeTeacher(): void
    {
        abort_unless(auth()->check() && auth()->user()->role === 'teacher', 403);
    }
}
