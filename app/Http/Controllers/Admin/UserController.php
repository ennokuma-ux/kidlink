<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->with('children')
            ->when($request->search, function ($q, $search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->role, function ($q, $role) {
                $q->where('role', $role);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $studentsByUser = Student::with('parents')->get()->keyBy('user_id');

        $editData = [];
        foreach ($users as $user) {
            $linkedStudent = $user->role === 'student' ? ($studentsByUser[$user->id] ?? null) : null;
            $editData[$user->id] = [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'contact' => $user->contact,
                'role' => $user->role,
                'is_active' => (bool) $user->is_active,
                'class_name' => $linkedStudent?->class_name,
                'student_ids' => $user->role === 'parent' ? $user->children->pluck('id')->all() : [],
                'parent_ids' => $linkedStudent ? $linkedStudent->parents->pluck('id')->all() : [],
            ];
        }

        $allStudents = Student::orderBy('first_name')->get();
        $allParents = User::where('role', 'parent')->orderBy('first_name')->get();

        $stats = [
            'teachers' => User::where('role', 'teacher')->count(),
            'parents' => User::where('role', 'parent')->count(),
            'students' => User::where('role', 'student')->count(),
            'admins' => User::where('role', 'super_admin')->count(),
        ];

        return view('admin.users', compact('users', 'stats', 'editData', 'allStudents', 'allParents'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'role' => 'required|in:teacher,parent',
            'contact' => 'nullable|string|max:255',
        ]);

        User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'contact' => $request->contact,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Account created successfully!');
    }

    public function update(Request $request, User $user)
    {
        if ($user->id === Auth::id()) {
            return redirect()->route('admin.users.index')->with('error', 'You cannot edit your own account.');
        }

        if ($user->role === 'super_admin') {
            return redirect()->route('admin.users.index')->with('error', 'Super admin accounts cannot be edited.');
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'contact' => 'nullable|string|max:255',
            'password' => 'nullable|min:8',
            'is_active' => 'nullable|boolean',
            'parent_ids' => 'nullable|array',
            'parent_ids.*' => 'integer|exists:users,id',
            'student_ids' => 'nullable|array',
            'student_ids.*' => 'integer|exists:students,id',
            'class_name' => 'nullable|string|max:255',
        ]);

        $user->first_name = $validated['first_name'];
        $user->last_name = $validated['last_name'];
        $user->email = $validated['email'];
        $user->contact = $validated['contact'] ?? null;
        $user->is_active = $request->boolean('is_active');

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        if ($user->role === 'parent') {
            if ($request->has('student_ids')) {
                $studentIds = collect($request->input('student_ids', []))
                    ->filter(fn ($id) => Student::whereKey($id)->exists())
                    ->values();
                $user->children()->sync($studentIds);
            }
        }

        if ($user->role === 'student') {
            $student = Student::where('user_id', $user->id)->first();
            if ($student) {
                if ($request->has('parent_ids')) {
                    $parentIds = collect($request->input('parent_ids', []))
                        ->filter(fn ($id) => User::whereKey($id)->where('role', 'parent')->exists())
                        ->values();
                    $student->parents()->sync($parentIds);
                }
                $student->update([
                    'is_active' => $request->boolean('is_active'),
                    'class_name' => $validated['class_name'] ?? $student->class_name,
                ]);
            }
        }

        return redirect()->route('admin.users.index')->with('success', 'Account updated!');
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return redirect()->route('admin.users.index')->with('error', 'You cannot delete your own account.');
        }

        if ($user->role === 'super_admin') {
            return redirect()->route('admin.users.index')->with('error', 'Super admin accounts cannot be deleted.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Account deleted!');
    }
}