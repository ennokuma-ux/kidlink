<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ChildModeController extends Controller
{
    public function enter(Student $student)
    {
        $user = Auth::user();

        if ($user->role === 'parent') {
            if (!$student->parents()->where('users.id', $user->id)->exists()) {
                return redirect()->back()->with('error', 'You can only enter child mode for your own children.');
            }
        } elseif ($user->role === 'teacher') {
            if ((int) $student->teacher_id !== (int) $user->id) {
                return redirect()->back()->with('error', 'You can only enter child mode for your assigned students.');
            }
        } else {
            return redirect()->back()->with('error', 'You are not allowed to enter child mode.');
        }

        $studentUser = $student->user;
        if (!$studentUser) {
            $studentUser = User::create([
                'first_name' => $student->first_name ?: ($student->full_name ?: $student->name),
                'last_name' => $student->last_name,
                'email' => 'student.' . $student->id . '@kidlink.test',
                'password' => Hash::make(Str::random(32)),
                'role' => 'student',
            ]);
            $student->update(['user_id' => $studentUser->id]);
        }

        session([
            'parent_user_id' => $user->id,
            'parent_role' => $user->role,
            'parent_first_name' => $user->first_name,
            'parent_last_name' => $user->last_name,
            'parent_name' => $user->full_name,
            'student_mode' => true,
            'student_child_id' => $student->id,
            'student_child_name' => $student->name,
        ]);

        Auth::loginUsingId($studentUser->id);

        return redirect()->route('student.home');
    }

    public function exit()
    {
        if (!session()->has('parent_user_id')) {
            return redirect()->route('home');
        }

        $parentId = session('parent_user_id');
        $parent = User::find($parentId);

        if ($parent) {
            Auth::loginUsingId($parent->id);
        }

        $role = session('parent_role', 'parent');

        session()->forget([
            'parent_user_id', 'parent_role', 'parent_first_name',
            'parent_last_name', 'parent_name', 'student_mode',
            'student_child_id', 'student_child_name',
        ]);

        return redirect()->route($role === 'teacher' ? 'teacher.dashboard' : 'parent.dashboard');
    }

    public function set(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'student_name' => 'required|string',
        ]);

        session([
            'student_mode' => true,
            'student_child_id' => $request->student_id,
            'student_child_name' => $request->student_name,
        ]);

        return response()->json(['ok' => true]);
    }
}
