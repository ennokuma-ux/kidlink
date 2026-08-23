<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    public function index()
    {
        $teacherId = auth()->id();
        $students = Student::where('teacher_id', $teacherId)
            ->with('parents')
            ->orderBy('created_at', 'desc')
            ->get();
        $parents = User::where('role', 'parent')->orderBy('first_name')->get();
        return view('teacher.students', compact('students', 'parents'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'sex' => 'required|in:Female,Male',
            'date_of_birth' => 'required|date',
            'address' => 'nullable|string',
            'class_name' => 'nullable|string|max:255',
            'dominant_hand' => 'nullable|in:Right,Left,Both',
            'father_name' => 'nullable|string',
            'father_contact' => 'nullable|string',
            'mother_name' => 'nullable|string',
            'mother_contact' => 'nullable|string',
            'siblings_count' => 'nullable|integer|min:0',
            'parent_ids' => 'nullable|array',
            'parent_ids.*' => 'exists:users,id',
            'create_login' => 'nullable',
            'student_email' => 'nullable|required_if:create_login,on|email|unique:users,email',
            'student_password' => 'nullable|required_if:create_login,on|min:8',
        ]);

        $parentIds = collect($request->parent_ids ?? [])
            ->filter(fn($id) => User::where('id', $id)->where('role', 'parent')->exists())
            ->values()
            ->all();

        $userId = null;
        if ($request->create_login) {
            $studentUser = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->student_email,
                'password' => Hash::make($request->student_password),
                'role' => 'student',
            ]);
            $userId = $studentUser->id;
        }

        $student = Student::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'sex' => $request->sex,
            'date_of_birth' => $request->date_of_birth,
            'address' => $request->address,
            'class_name' => $request->class_name,
            'dominant_hand' => $request->dominant_hand ?? 'Right',
            'parent_id' => $parentIds[0] ?? null,
            'teacher_id' => auth()->id(),
            'user_id' => $userId,
            'enrolled_date' => Carbon::today(),
            'is_active' => true,
            'father_name' => $request->father_name,
            'father_contact' => $request->father_contact,
            'mother_name' => $request->mother_name,
            'mother_contact' => $request->mother_contact,
            'siblings_count' => $request->siblings_count ?? 0,
        ]);

        $student->parents()->attach($parentIds);

        return redirect()->route('teacher.students.index')->with('success', $request->create_login
            ? "Student added with login account ($request->student_email)."
            : 'Student added successfully!');
    }

    public function update(Request $request, Student $student)
    {
        if ($student->teacher_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'sex' => 'required|in:Female,Male',
            'date_of_birth' => 'required|date',
            'address' => 'nullable|string',
            'class_name' => 'nullable|string|max:255',
            'parent_ids' => 'nullable|array',
            'parent_ids.*' => 'exists:users,id',
        ]);

        $parentIds = collect($request->parent_ids ?? [])
            ->filter(fn($id) => User::where('id', $id)->where('role', 'parent')->exists())
            ->values()
            ->all();

        $student->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'sex' => $validated['sex'],
            'date_of_birth' => $validated['date_of_birth'],
            'address' => $validated['address'] ?? null,
            'class_name' => $validated['class_name'] ?? null,
            'parent_id' => $parentIds[0] ?? $student->parent_id,
        ]);

        if ($request->has('parent_ids')) {
            $student->parents()->sync($parentIds);
        }

        return redirect()->route('teacher.students.index')->with('success', 'Student updated!');
    }

    public function toggleActive(Student $student)
    {
        if ($student->teacher_id !== auth()->id()) {
            abort(403);
        }
        $student->update(['is_active' => !$student->is_active]);
        return redirect()->route('teacher.students.index')->with('success', 'Student status updated!');
    }

    public function destroy(Student $student)
    {
        if ($student->teacher_id !== auth()->id()) {
            abort(403);
        }
        $student->delete();
        return redirect()->route('teacher.students.index')->with('success', 'Student deleted!');
    }
}
