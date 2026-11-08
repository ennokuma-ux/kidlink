<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AccountRequest;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    public function index()
    {
        $teacher = auth()->user();
        $teacherId = $teacher->id;

        // Students belong to a teacher, but an account created by an admin may
        // have no teacher yet. Those are shown too, otherwise they would be
        // invisible on every teacher's account list with no way to find them.
        $scope = fn ($query) => $query->where(function ($q) use ($teacherId) {
            $q->where('teacher_id', $teacherId)->orWhereNull('teacher_id');
        });

        // Newest first, with a stable fallback so accounts created without a
        // created_at timestamp still appear at a predictable spot.
        $order = fn ($query) => $query
            ->orderByRaw('created_at IS NULL')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc');

        $students = $order($scope(Student::with('parents')))
            ->where('is_active', true)
            ->get();

        $archivedStudents = $order($scope(Student::with('parents')))
            ->where('is_active', false)
            ->get();

        // Also backs the "connect parent account" pickers in the add/edit modals.
        $parents = User::where('role', 'parent')
            ->with('children')
            ->orderBy('first_name')
            ->get();

        $activeParents = $parents->where('is_active', true)->values();
        $archivedParents = $parents->where('is_active', false)->values();

        // The teacher's own requests for an admin to approve, newest first.
        $accountRequests = AccountRequest::with('reviewer')
            ->where('requested_by', $teacherId)
            ->latest()
            ->get();

        $requestCounts = [
            'pending' => $accountRequests->where('status', 'pending')->count(),
            'approved' => $accountRequests->where('status', 'approved')->count(),
            'rejected' => $accountRequests->where('status', 'rejected')->count(),
        ];

        return view('teacher.accounts', compact(
            'students', 'archivedStudents', 'parents', 'activeParents', 'archivedParents',
            'accountRequests', 'requestCounts'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'sex' => 'required|in:Female,Male',
            'date_of_birth' => 'required|date',
            'address' => 'nullable|string',
            'class_name' => ['nullable', 'string', 'max:255', Rule::in(Student::SECTIONS)],
            'dominant_hand' => ['nullable', 'string', 'max:255', Rule::in(Student::HANDS)],
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
            'dominant_hand' => $request->dominant_hand ?? 'Not yet established',
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

        return redirect()->route('teacher.accounts.index')->with('success', $request->create_login
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
            'class_name' => ['nullable', 'string', 'max:255', Rule::in(Student::SECTIONS)],
            'dominant_hand' => ['nullable', 'string', 'max:255', Rule::in(Student::HANDS)],
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
            'dominant_hand' => $validated['dominant_hand'] ?? $student->dominant_hand,
            'parent_id' => $parentIds[0] ?? $student->parent_id,
        ]);

        if ($request->has('parent_ids')) {
            $student->parents()->sync($parentIds);
        }

        return redirect()->route('teacher.accounts.index')->with('success', 'Student updated!');
    }

    public function toggleActive(Student $student)
    {
        if ($student->teacher_id !== auth()->id()) {
            abort(403);
        }

        $archiving = (bool) $student->is_active;

        $student->update(['is_active' => !$archiving]);

        // Keep the child's own login in step so an archived student cannot sign in.
        if ($student->user_id && $student->user && $student->user->role === 'student') {
            $student->user->update(['is_active' => !$archiving]);
        }

        return redirect()->route('teacher.accounts.index')
            ->with('success', $archiving ? 'Student archived.' : 'Student restored.');
    }

    public function toggleParentActive(User $user)
    {
        if ($user->role !== 'parent') {
            abort(404);
        }

        $archiving = (bool) $user->is_active;
        $user->update(['is_active' => !$archiving]);

        return redirect()->route('teacher.accounts.index')
            ->with('success', $archiving ? 'Parent account archived.' : 'Parent account restored.');
    }

    public function storeParent(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'contact' => 'nullable|string|max:255',
        ]);

        User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'parent',
            'contact' => $request->contact,
        ]);

        return redirect()->route('teacher.accounts.index')->with('success', 'Parent account created successfully!');
    }

    public function updateParent(Request $request, User $user)
    {
        if ($user->role !== 'parent') {
            abort(404);
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'password' => 'nullable|min:8',
            'contact' => 'nullable|string|max:255',
        ]);

        $data = [
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'contact' => $validated['contact'] ?? null,
        ];

        // Left blank means keep the current password.
        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        return redirect()->route('teacher.accounts.index')->with('success', 'Parent account updated successfully!');
    }

    /**
     * A teacher cannot create the account directly here: this sends a request
     * that an admin approves or rejects. Admin\AccountRequestController does
     * the creating once it is approved.
     */
    public function storeAccountRequest(Request $request)
    {
        $role = $request->input('role');

        $rules = [
            'role' => ['required', Rule::in(['parent', 'student'])],
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => [$role === 'parent' ? 'required' : 'nullable', 'email', 'max:255', 'unique:users,email'],
            'contact' => 'nullable|string|max:255',
        ];

        if ($role === 'student') {
            $rules += [
                'sex' => 'required|in:Female,Male',
                'date_of_birth' => 'required|date',
                'address' => 'nullable|string|max:1000',
                'class_name' => ['nullable', 'string', 'max:255', Rule::in(Student::SECTIONS)],
                'dominant_hand' => ['nullable', 'string', 'max:255', Rule::in(Student::HANDS)],
                'father_name' => 'nullable|string|max:255',
                'father_contact' => 'nullable|string|max:255',
                'mother_name' => 'nullable|string|max:255',
                'mother_contact' => 'nullable|string|max:255',
                'siblings_count' => 'nullable|integer|min:0',
                'parent_ids' => 'nullable|array',
                'parent_ids.*' => 'integer|exists:users,id',
            ];
        }

        $validated = $request->validate($rules);
        $email = $validated['email'] ?? null;

        // Two requests for one login would collide the moment the first is approved.
        if ($email && AccountRequest::where('status', 'pending')->where('email', $email)->exists()) {
            return back()->withInput()->withErrors(['email' => __('teacher.request_email_pending')]);
        }

        $parentIds = $role === 'student'
            ? User::whereIn('id', $validated['parent_ids'] ?? [])->where('role', 'parent')->where('is_active', true)->pluck('id')->all()
            : [];

        $teacherId = auth()->id();

        AccountRequest::create([
            'requested_by' => $teacherId,
            'teacher_id' => $teacherId,
            'role' => $role,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $email,
            'contact' => $validated['contact'] ?? null,
            'parent_ids' => $parentIds ?: null,
            'sex' => $validated['sex'] ?? null,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'address' => $validated['address'] ?? null,
            'class_name' => $validated['class_name'] ?? null,
            'dominant_hand' => $validated['dominant_hand'] ?? null,
            'father_name' => $validated['father_name'] ?? null,
            'father_contact' => $validated['father_contact'] ?? null,
            'mother_name' => $validated['mother_name'] ?? null,
            'mother_contact' => $validated['mother_contact'] ?? null,
            'siblings_count' => $validated['siblings_count'] ?? null,
            'status' => 'pending',
        ]);

        return redirect()->route('teacher.accounts.index')
            ->with('success', __('teacher.request_sent'))
            ->with('open_view', 'requests');
    }

    /**
     * Take back a request that has not been decided yet. Decided requests stay
     * as a record, and only the teacher who sent one can withdraw it.
     */
    public function cancelAccountRequest(AccountRequest $accountRequest)
    {
        abort_unless($accountRequest->requested_by === auth()->id(), 403);

        if ($accountRequest->status !== 'pending') {
            return redirect()->route('teacher.accounts.index')
                ->with('error', __('teacher.request_not_withdrawable'))
                ->with('open_view', 'requests');
        }

        $accountRequest->delete();

        return redirect()->route('teacher.accounts.index')
            ->with('success', __('teacher.request_withdrawn'))
            ->with('open_view', 'requests');
    }
}
