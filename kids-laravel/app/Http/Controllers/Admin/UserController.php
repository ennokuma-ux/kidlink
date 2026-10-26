<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $filtered = function ($query) use ($request) {
            return $query
                ->when($request->search, function ($q, $search) {
                    $q->where(function ($q2) use ($search) {
                        $q2->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
                })
                ->when($request->role, fn ($q, $role) => $q->where('role', $role));
        };

        $users = $filtered(User::query()->with('children')->where('is_active', true))->get();
        $archivedUsers = $filtered(User::query()->with('children')->where('is_active', false))->get();

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
                'dominant_hand' => $linkedStudent?->dominant_hand,
                'student_ids' => $user->role === 'parent' ? $user->children->pluck('id')->all() : [],
                'parent_ids' => $linkedStudent ? $linkedStudent->parents->pluck('id')->all() : [],
            ];
        }

        // Archived accounts stay out of the account maker entirely: you cannot
        // hand a new account to a teacher or parent who has been archived.
        $allStudents = Student::where('is_active', true)->orderBy('first_name')->get();
        $allParents = User::where('role', 'parent')->where('is_active', true)->orderBy('first_name')->get();
        $allTeachers = User::where('role', 'teacher')->where('is_active', true)->orderBy('first_name')->get();

        $stats = [
            'teachers' => User::where('role', 'teacher')->where('is_active', true)->count(),
            'parents' => User::where('role', 'parent')->where('is_active', true)->count(),
            'students' => User::where('role', 'student')->where('is_active', true)->count(),
            'admins' => User::where('role', 'super_admin')->count(),
        ];

        return view('admin.users', compact('users', 'archivedUsers', 'stats', 'editData', 'allStudents', 'allParents', 'allTeachers'));
    }

    public function store(Request $request)
    {
        $isStudent = $request->input('role') === 'student';

        // Students are placed in a section/class rather than being given a
        // password by hand, so their login is generated for them instead.
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ($isStudent ? 'nullable' : 'required') . '|string|min:8',
            'role' => 'required|in:super_admin,teacher,parent,student',
            'contact' => 'nullable|string|max:255',
            'class_name' => $isStudent
                ? ['required', 'string', 'max:255', Rule::in(Student::SECTIONS)]
                : 'nullable|string|max:255',
            'dominant_hand' => $isStudent
                ? ['nullable', 'string', 'max:255', Rule::in(Student::HANDS)]
                : 'nullable|string|max:255',
            'teacher_id' => [
                'nullable',
                Rule::exists('users', 'id')
                    ->where(fn ($query) => $query->where('role', 'teacher')->where('is_active', true)),
            ],
            'parent_ids' => 'nullable|array',
            'parent_ids.*' => [
                Rule::exists('users', 'id')
                    ->where(fn ($query) => $query->where('role', 'parent')->where('is_active', true)),
            ],
        ]);

        $teacherId = $request->filled('teacher_id')
            ? User::where('id', $request->teacher_id)
                ->where('role', 'teacher')
                ->where('is_active', true)
                ->value('id')
            : null;

        // Letters and digits only: a preschool parent has to read this out and
        // type it in, and it keeps the value safe to render and copy.
        $generatedPassword = $isStudent
            ? Str::password(10, symbols: false, spaces: false)
            : null;

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($generatedPassword ?? $request->password),
            'role' => $request->role,
            'contact' => $request->contact,
        ]);

        if ($request->role === 'student') {
            $student = \App\Models\Student::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'sex' => 'Female',
                'date_of_birth' => Student::dateOfBirthForSection($request->class_name),
                'address' => '',
                'class_name' => $request->class_name,
                'dominant_hand' => $request->dominant_hand ?? 'Not yet established',
                'parent_id' => $request->parent_ids[0] ?? null,
                'teacher_id' => $teacherId,
                'user_id' => $user->id,
                'enrolled_date' => now(),
                'is_active' => true,
                'father_name' => '',
                'father_contact' => '',
                'mother_name' => '',
                'mother_contact' => '',
                'siblings_count' => 0,
            ]);

            $parentIds = collect($request->input('parent_ids', []))
                ->filter(fn($id) => User::where('id', $id)->where('role', 'parent')->where('is_active', true)->exists())
                ->values()
                ->all();
            $student->parents()->attach($parentIds);
            $student->update(['parent_id' => $parentIds[0] ?? null]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Account created successfully!')
            ->with('generated_credentials', $generatedPassword ? [
                'email' => $user->email,
                'password' => $generatedPassword,
            ] : null);
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
            'class_name' => ['nullable', 'string', 'max:255', Rule::in(Student::SECTIONS)],
            'dominant_hand' => ['nullable', 'string', 'max:255', Rule::in(Student::HANDS)],
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
                    'dominant_hand' => $validated['dominant_hand'] ?? $student->dominant_hand,
                ]);
            }
        }

        return redirect()->route('admin.users.index')->with('success', 'Account updated!');
    }

    public function archive(User $user)
    {
        if ($user->id === Auth::id()) {
            return redirect()->route('admin.users.index')->with('error', 'You cannot archive your own account.');
        }

        if ($user->role === 'super_admin') {
            return redirect()->route('admin.users.index')->with('error', 'Super admin accounts cannot be archived.');
        }

        $archiving = (bool) $user->is_active;
        $user->update(['is_active' => !$archiving]);

        // Keep the linked student record in step so archived children also drop
        // out of teacher lists, attendance, ECCD and analytics.
        if ($user->role === 'student') {
            $student = Student::where('user_id', $user->id)->first();
            $student?->update(['is_active' => !$archiving]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', $archiving ? 'Account archived.' : 'Account restored.');
    }
}