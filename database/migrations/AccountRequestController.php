<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccountRequest;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AccountRequestController extends Controller
{
    public const FILTERS = ['pending', 'approved', 'rejected', 'all'];

    public function index(Request $request)
    {
        // Opens on what needs a decision; the other tabs are history.
        $filter = in_array($request->query('status'), self::FILTERS, true) ? $request->query('status') : 'pending';

        $counts = AccountRequest::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
        $pendingCount = (int) ($counts['pending'] ?? 0);
        $counts = [
            'pending' => $pendingCount,
            'approved' => (int) ($counts['approved'] ?? 0),
            'rejected' => (int) ($counts['rejected'] ?? 0),
        ];
        $counts['all'] = array_sum($counts);

        $requests = AccountRequest::with(['requester', 'student', 'reviewer'])
            ->when($filter !== 'all', fn ($q) => $q->where('status', $filter))
            ->latest()
            ->get();

        return view('admin.account-requests', compact('requests', 'pendingCount', 'filter', 'counts'));
    }

    public function approve(AccountRequest $accountRequest)
    {
        if ($accountRequest->status !== 'pending') {
            return back()->with('error', 'This request has already been reviewed.');
        }
        if ($accountRequest->email && User::where('email', $accountRequest->email)->exists()) {
            return back()->with('error', 'This email already belongs to an account. Reject this request and ask the teacher to submit a corrected request.');
        }

        $hasLogin = (bool) $accountRequest->email;
        $usesDefaultParentPassword = $accountRequest->role === 'parent' && ! $accountRequest->password;
        $generatedPassword = $usesDefaultParentPassword ? 'password123' : ($hasLogin && $accountRequest->role !== 'parent' ? Str::password(12, symbols: false, spaces: false) : null);
        $passwordHash = null;
        if ($accountRequest->role === 'parent') {
            $requestedPassword = (string) ($accountRequest->password ?? '');
            if ($requestedPassword === '') {
                $passwordHash = Hash::make('password123');
            } else {
                // Requests created by the current teacher form already contain
                // a hash. Also safely hash any legacy/plaintext pending request.
                $passwordInfo = password_get_info($requestedPassword);
                $passwordHash = ($passwordInfo['algo'] ?? null) === null
                    ? Hash::make($requestedPassword)
                    : $requestedPassword;
            }
        } elseif ($generatedPassword !== null) {
            $passwordHash = Hash::make($generatedPassword);
        }
        $user = DB::transaction(function () use ($accountRequest, $passwordHash, $hasLogin) {
            $user = ! $hasLogin ? null : User::create([
                'first_name' => $accountRequest->first_name,
                'last_name' => $accountRequest->last_name,
                'email' => $accountRequest->email,
                'password' => $passwordHash,
                'must_change_password' => $accountRequest->role === 'parent',
                'role' => $accountRequest->role,
                'contact' => $accountRequest->contact,
                'is_active' => true,
            ]);

            if ($accountRequest->role === 'student') {
                $parentIds = User::whereIn('id', $accountRequest->parent_ids ?? [])
                    ->where('role', 'parent')->where('is_active', true)->pluck('id')->all();
                $student = Student::create([
                    'first_name' => $accountRequest->first_name,
                    'last_name' => $accountRequest->last_name,
                    'full_name' => trim($accountRequest->first_name . ' ' . $accountRequest->last_name),
                    'sex' => $accountRequest->sex,
                    'date_of_birth' => $accountRequest->date_of_birth,
                    'address' => $accountRequest->address,
                    'class_name' => $accountRequest->class_name,
                    'academic_year' => $accountRequest->academic_year ?: Student::currentAcademicYear(),
                    'dominant_hand' => $accountRequest->dominant_hand ?: 'Not yet established',
                    'teacher_id' => $accountRequest->teacher_id ?? $accountRequest->requested_by,
                    'user_id' => $user?->id,
                    'enrolled_date' => now()->toDateString(),
                    'is_active' => true,
                    'status' => 'active',
                    'father_name' => $accountRequest->father_name,
                    'father_contact' => $accountRequest->father_contact,
                    'mother_name' => $accountRequest->mother_name,
                    'mother_contact' => $accountRequest->mother_contact,
                    'siblings_count' => $accountRequest->siblings_count ?? 0,
                    'parent_id' => $parentIds[0] ?? null,
                ]);
                $student->parents()->attach($parentIds);
            }

            $accountRequest->update([
                'status' => 'approved',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'review_notes' => null,
            ]);
            return $user;
        });

        $redirect = redirect()->route('admin.account-requests.index');
        if (! $user) {
            return $redirect->with('success', 'Student account approved (no login was requested).');
        }

        $redirect->with('success', 'Account request approved.');
        if ($generatedPassword !== null) {
            $redirect->with('generated_credentials', ['name' => $user->full_name, 'email' => $user->email, 'password' => $generatedPassword]);
        }
        return $redirect;
    }

    public function reject(Request $request, AccountRequest $accountRequest)
    {
        if ($accountRequest->status !== 'pending') {
            return back()->with('error', 'This request has already been reviewed.');
        }
        $validated = $request->validate(['review_notes' => 'nullable|string|max:1000']);
        $accountRequest->update([
            'status' => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'review_notes' => $validated['review_notes'] ?? null,
        ]);
        return redirect()->route('admin.account-requests.index', ['status' => $request->query('status', 'pending')])->with('success', 'Account request rejected.');
    }
}
