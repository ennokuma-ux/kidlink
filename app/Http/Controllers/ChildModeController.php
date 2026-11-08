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
    /**
     * The accounts the current user may play as, always read fresh from the
     * database so the chooser can never show a stale list.
     */
    public function accounts()
    {
        return response()->json([
            'accounts' => $this->availableAccounts()->map(fn (Student $student) => [
                'id' => $student->id,
                'first' => $student->first_name,
                'name' => $student->name,
                'class_name' => $student->class_name,
                'initial' => strtoupper(substr($student->first_name ?: '?', 0, 1)),
                'url' => route('child-mode.enter', $student),
            ])->values(),
        ]);
    }

    public function enter(Student $student)
    {
        $user = Auth::user();

        if (!$this->canPlayAs($user, $student)) {
            return redirect()->back()->with('error', match ($user->role) {
                'parent' => 'You can only enter child mode for your own children.',
                'teacher' => 'You can only enter child mode for your assigned students.',
                default => 'You are not allowed to enter child mode.',
            });
        }

        if (!$student->is_active) {
            return redirect()->back()->with('error', 'That account is no longer active.');
        }

        $studentUser = $this->resolveStudentUser($student);

        if (!$studentUser) {
            return redirect()->back()->with('error', 'A student account could not be prepared for that child.');
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

        return redirect()->route(match ($role) {
            'teacher' => 'teacher.dashboard',
            'super_admin', 'admin' => 'admin.analytics.index',
            default => 'parent.dashboard',
        });
    }

    public function set(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
        ]);

        $user = auth()->user();
        $student = Student::findOrFail($request->student_id);

        if (!$this->canPlayAs($user, $student)) {
            abort(403, 'You are not allowed to select that account.');
        }

        if (!$student->is_active) {
            abort(422, 'That account is no longer active.');
        }

        session([
            'student_mode' => true,
            'student_child_id' => $student->id,
            'student_child_name' => $student->name,
        ]);

        return response()->json([
            'ok' => true,
            'student' => ['id' => $student->id, 'name' => $student->name],
        ]);
    }

    private function availableAccounts()
    {
        return $this->playableStudents(auth()->user())
            ->where('students.is_active', true)
            ->orderBy('first_name')
            ->get();
    }

    /**
     * The students a user may open child mode for. Admins oversee the whole
     * centre, so they see every child; everyone else is limited to their own.
     */
    private function playableStudents(User $user)
    {
        return match ($user->role) {
            'teacher' => $user->teacherStudents(),
            'parent' => $user->children(),
            'super_admin', 'admin' => Student::query(),
            default => Student::query()->whereRaw('1 = 0'),
        };
    }

    private function canPlayAs(User $user, Student $student): bool
    {
        if (!$student->is_active) {
            return false;
        }

        return match ($user->role) {
            'teacher' => (int) $student->teacher_id === (int) $user->id,
            'parent' => $student->parents()->where('users.id', $user->id)->exists(),
            'super_admin', 'admin' => true,
            default => false,
        };
    }

    /**
     * Returns the student's login account, repairing the link when it is
     * missing or points at the wrong kind of account.
     */
    private function resolveStudentUser(Student $student): ?User
    {
        $linked = $student->user;

        if ($linked && $linked->role === 'student' && $linked->is_active) {
            return $linked;
        }

        $email = 'student.' . $student->id . '@kidlink.test';
        $account = User::where('email', $email)->first();

        if (!$account) {
            $account = User::create([
                'first_name' => $student->first_name ?: ($student->full_name ?: $student->name),
                'last_name' => $student->last_name,
                'email' => $email,
                'password' => Hash::make(Str::random(32)),
                'role' => 'student',
            ]);
        } elseif ($account->role !== 'student') {
            return null;
        }

        if (!$account->is_active) {
            $account->update(['is_active' => true]);
        }

        if ((int) $student->user_id !== (int) $account->id) {
            $student->update(['user_id' => $account->id]);
        }

        return $account;
    }
}
