@extends('layouts.admin')

@section('title', __('admin_ui.manage_users'))
@section('page-title', __('admin_ui.manage_accounts'))

@section('content')
@php
    $accountView = request('view') === 'archived' ? 'archived' : 'active';
@endphp
<style>
    input.password-entry::-ms-reveal,
    input.password-entry::-ms-clear,
    input.password-entry::-webkit-credentials-auto-fill-button {
        display: none !important;
        visibility: hidden !important;
        pointer-events: none !important;
    }
</style>
@if(session('generated_credentials'))
<div id="generatedCredentials" class="bg-white rounded-xl shadow-sm p-6 mb-6 border-l-4 border-[#ff8555]">
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            @if((session('generated_credentials')['role'] ?? null) === 'parent')
                <h2 class="text-lg font-bold text-[#182438]">Parent login created</h2>
                <p class="text-sm text-[#5f6873] mt-1">Share these credentials with the parent. They will be asked to change the temporary password on first login.</p>
            @else
                <h2 class="text-lg font-bold text-[#182438]">{{ __('admin_ui.student_login_created') }}</h2>
                <p class="text-sm text-[#5f6873] mt-1">{{ __('admin_ui.student_login_created_description') }}</p>
            @endif
            <div class="flex flex-wrap items-center gap-3 mt-4">
                <code class="px-3 py-2 bg-[#fdf3d6] rounded-lg text-sm text-[#182438]">{{ session('generated_credentials')['email'] }}</code>
                <code class="px-3 py-2 bg-[#fdf3d6] rounded-lg text-sm font-semibold text-[#182438]">{{ session('generated_credentials')['password'] }}</code>
                <button type="button" onclick="navigator.clipboard.writeText(@js(__('admin_ui.email_label') . ': ' . session('generated_credentials')['email'] . PHP_EOL . __('admin_ui.password_label') . ': ' . session('generated_credentials')['password']))"
                    class="px-4 py-2 border border-[#f1e7cc] rounded-lg text-sm text-[#5f6873] hover:bg-[#fffbf0] transition">{{ __('admin_ui.copy') }}</button>
            </div>
        </div>
        <button type="button" onclick="this.closest('#generatedCredentials').remove()"
            class="text-[#6b7280] hover:text-[#5f6873] text-2xl leading-none">&times;</button>
    </div>
</div>
@endif
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-[#182438]">{{ __('admin_ui.all_accounts') }}</h2>
            <p class="text-sm text-[#5f6873] mt-1">{{ __('admin_ui.create_manage_teachers_parents') }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-3 self-start md:self-auto">
            <div class="flex flex-wrap items-center gap-1 bg-[#fdf3d6] rounded-lg p-1 w-full sm:w-auto">
                <button type="button" id="viewActiveBtn" onclick="switchView('active')"
                    class="px-3 sm:px-5 py-2 rounded-md text-sm font-semibold transition {{ $accountView === 'active' ? 'bg-[#ff8555] text-white' : 'text-[#5f6873] hover:text-[#182438]' }}">
                    {{ __('admin_ui.active') }}
                </button>
                <button type="button" id="viewArchivedBtn" onclick="switchView('archived')"
                    class="px-3 sm:px-5 py-2 rounded-md text-sm font-semibold transition {{ $accountView === 'archived' ? 'bg-[#ff8555] text-white' : 'text-[#5f6873] hover:text-[#182438]' }}">
                    {{ __('admin_ui.archived') }}
                    @if($archivedUsers->total() > 0)
                        <span class="ml-1.5 px-1.5 py-0.5 rounded-full bg-[#f1e7cc] text-[#5f6873] text-xs">{{ $archivedUsers->total() }}</span>
                    @endif
                </button>
            </div>
            @php
                try { $pendingApprovals = \App\Models\AccountRequest::where('status', 'pending')->count(); } catch (\Throwable $e) { $pendingApprovals = 0; }
            @endphp
            <a href="{{ route('admin.account-requests.index') }}" class="inline-flex items-center gap-2 border border-[#f1e7cc] hover:bg-[#fffbf0] text-[#182438] px-5 py-2.5 rounded-lg transition font-medium">
                {{ __('admin_ui.pending_approvals') }}
                @if($pendingApprovals > 0)<span class="min-w-[1.5rem] text-center px-1.5 py-0.5 rounded-full bg-[#ff595e] text-white text-xs font-bold">{{ $pendingApprovals }}</span>@endif
            </a>
            <button onclick="document.getElementById('addUserModal').classList.remove('hidden')" class="bg-[#ff8555] hover:bg-[#f47548] text-white px-6 py-2.5 rounded-lg transition font-medium">
                {{ __('admin_ui.create_account') }}
            </button>
        </div>
    </div>
    <form id="accountFilterForm" method="GET" action="{{ route('admin.users.index') }}" class="mt-4 flex flex-col md:flex-row md:items-center gap-3">
        <input type="hidden" name="view" value="{{ $accountView }}">
        <div class="relative flex-1 md:max-w-xs">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#6b7280]">⌕</span>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('admin_ui.search_by_name_or_email') }}" autocomplete="off"
                class="w-full pl-9 pr-4 py-2.5 border border-[#f1e7cc] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
        </div>
        <select name="role" onchange="this.form.submit()" class="px-4 py-2.5 border border-[#f1e7cc] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
            <option value="">{{ __('admin_ui.all_roles') }}</option>
            <option value="teacher" {{ request('role') == 'teacher' ? 'selected' : '' }}>{{ __('admin_ui.teacher') }}</option>
            <option value="parent" {{ request('role') == 'parent' ? 'selected' : '' }}>{{ __('admin_ui.parent') }}</option>
            <option value="student" {{ request('role') == 'student' ? 'selected' : '' }}>{{ __('admin_ui.student') }}</option>
            <option value="super_admin" {{ request('role') == 'super_admin' ? 'selected' : '' }}>{{ __('admin_ui.super_admin') }}</option>
        </select>
        <button type="submit" class="bg-[#ff8555] hover:bg-[#f47548] text-white px-5 py-2.5 rounded-lg transition font-medium">{{ __('admin_ui.filter') }}</button>
    </form>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-4 text-center border-l-4 border-[#1982c4]">
        <p class="text-2xl font-bold text-[#182438]">{{ $stats['teachers'] }}</p>
        <p class="text-sm text-[#5f6873]">{{ __('admin_ui.teachers') }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 text-center border-l-4 border-[#8ac926]">
        <p class="text-2xl font-bold text-[#8ac926]">{{ $stats['parents'] }}</p>
        <p class="text-sm text-[#5f6873]">{{ __('admin_ui.parents') }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 text-center border-l-4 border-[#ff595e]">
        <p class="text-2xl font-bold text-[#ff595e]">{{ $stats['students'] }}</p>
        <p class="text-sm text-[#5f6873]">{{ __('admin_ui.students') }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 text-center border-l-4 border-[#ffca3a]">
        <p class="text-2xl font-bold text-[#ffca3a]">{{ $stats['admins'] }}</p>
        <p class="text-sm text-[#5f6873]">{{ __('admin_ui.super_admins') }}</p>
    </div>
</div>

<div id="activeView" class="{{ $accountView === 'active' ? '' : 'hidden' }} bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-[#fffbf0]">
                <tr>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-[#182438]">{{ __('admin_ui.name') }}</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-[#182438]">{{ __('admin_ui.email') }}</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-[#182438]">{{ __('admin_ui.role') }}</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-[#182438]">{{ __('admin_ui.contact') }}</th>
                    <th class="px-6 py-4 text-center text-sm font-semibold text-[#182438]">{{ __('admin_ui.status') }}</th>
                    <th class="px-6 py-4 text-center text-sm font-semibold text-[#182438]">{{ __('admin_ui.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#f1e7cc]">
                @forelse($users as $user)
                <tr class="hover:bg-[#fffbf0] transition">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-[#ff8555] rounded-full flex items-center justify-center text-white font-bold text-sm">
                                {{ strtoupper(substr($user->first_name, 0, 1)) }}{{ strtoupper(substr($user->last_name, 0, 1)) }}
                            </div>
                            <span class="font-medium text-[#182438]">{{ $user->full_name }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-[#5f6873]">{{ $user->email }}</td>
                    <td class="px-6 py-4">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold capitalize
                            {{ $user->role === 'teacher' ? 'bg-[#d3e8f6] text-[#0f5483]' : ($user->role === 'parent' ? 'bg-[#e6f4c8] text-[#4f7a0f]' : ($user->role === 'super_admin' ? 'bg-[#e8e0f0] text-[#473163]' : 'bg-[#fff0bf] text-[#8a6500]')) }}">
                            {{ str_replace('_', ' ', $user->role) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-[#5f6873]">{{ $user->contact ?? '—' }}</td>
                    <td class="px-6 py-4 text-center">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $user->is_active ? 'bg-[#e6f4c8] text-[#4f7a0f]' : 'bg-[#f1e7cc] text-[#5f6873]' }}">
                            {{ $user->is_active ? __('admin_ui.active') : __('admin_ui.inactive') }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-center gap-2">
                            @if($user->id !== auth()->id() && $user->role !== 'super_admin')
                            <button type="button" title="{{ __('admin_ui.edit') }}"
                                data-edit='@json($editData[$user->id])'
                                class="edit-user-btn w-9 h-9 rounded-lg border border-[#f1e7cc] hover:bg-[#fffbf0] transition text-[#1982c4]">
                                <svg class="w-4 h-4 mx-auto" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"></path><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L8 18l-4 1 1-4Z"></path></svg>
                            </button>
                            <form method="POST" action="{{ route('admin.users.archive', $user) }}" class="archive-form" data-name="{{ $user->full_name }}" data-archived="0">
                                @csrf @method('PUT')
                                <button type="submit" title="{{ __('admin_ui.archive') }}"
                                    class="w-9 h-9 rounded-lg border border-[#f1e7cc] hover:bg-[#fffbf0] transition text-[#1982c4]">
                                    <img src="{{ asset('assets/icons/file-tray-stacked.svg') }}" alt="Paused" class="inline-block h-5 w-5">
                                </button>
                            </form>
                            @else
                            <span class="text-xs text-[#6b7280]">—</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-12 text-center text-[#6b7280]">{{ __('admin_ui.no_accounts_found') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $users->links() }}</div>
</div>

<div id="archivedView" class="{{ $accountView === 'archived' ? '' : 'hidden' }}">
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-[#fffbf0] border-b border-[#f1e7cc]">
            <h3 class="text-sm font-bold text-[#182438]">{{ __('admin_ui.archived_accounts') }}</h3>
            <p class="text-xs text-[#5f6873] mt-0.5">{{ __('admin_ui.archived_accounts_description') }}</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-[#fffbf0]">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-[#182438]">{{ __('admin_ui.name') }}</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-[#182438]">{{ __('admin_ui.email') }}</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-[#182438]">{{ __('admin_ui.role') }}</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-[#182438]">{{ __('admin_ui.contact') }}</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold text-[#182438]">{{ __('admin_ui.status') }}</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold text-[#182438]">{{ __('admin_ui.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f1e7cc]">
                    @forelse($archivedUsers as $user)
                    <tr class="hover:bg-[#fffbf0] transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-[#c9ced6] rounded-full flex items-center justify-center text-white font-bold text-sm">
                                    {{ strtoupper(substr($user->first_name, 0, 1)) }}{{ strtoupper(substr($user->last_name, 0, 1)) }}
                                </div>
                                <span class="font-medium text-[#5f6873]">{{ $user->full_name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-[#5f6873]">{{ $user->email }}</td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold capitalize
                                {{ $user->role === 'teacher' ? 'bg-[#d3e8f6] text-[#0f5483]' : ($user->role === 'parent' ? 'bg-[#e6f4c8] text-[#4f7a0f]' : ($user->role === 'super_admin' ? 'bg-[#e8e0f0] text-[#473163]' : 'bg-[#fff0bf] text-[#8a6500]')) }}">
                                {{ str_replace('_', ' ', $user->role) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-[#5f6873]">{{ $user->contact ?? '—' }}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-[#f1e7cc] text-[#5f6873]">{{ __('admin_ui.archived') }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center">
                                <form method="POST" action="{{ route('admin.users.archive', $user) }}" class="archive-form" data-name="{{ $user->full_name }}" data-archived="1">
                                    @csrf @method('PUT')
                                    <button type="submit" title="{{ __('admin_ui.restore') }}"
                                        class="w-9 h-9 rounded-lg border border-[#f1e7cc] hover:bg-[#fffbf0] transition text-[#6fa51c]">
                                        ▶
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-6 py-12 text-center text-[#6b7280]">{{ __('admin_ui.no_archived_accounts') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $archivedUsers->links() }}</div>
</div>

<div id="addUserModal" class="fixed inset-0 bg-black/50 flex items-start sm:items-center justify-center z-50 hidden p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl p-5 sm:p-8 w-full max-w-md max-h-[90vh] flex flex-col">
        <div class="flex justify-between items-center mb-6 shrink-0">
            <h3 class="text-xl font-bold text-[#182438]">{{ __('admin_ui.create_account_title') }}</h3>
            <button onclick="this.closest('#addUserModal').classList.add('hidden')" class="text-[#6b7280] hover:text-[#5f6873] text-2xl">&times;</button>
        </div>
        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4 overflow-y-auto pr-1 -mr-2">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-[#182438] mb-2">{{ __('admin_ui.first_name') }}</label>
                    <input type="text" name="first_name" required class="w-full px-4 py-3 border border-[#f1e7cc] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#182438] mb-2">{{ __('admin_ui.last_name') }}</label>
                    <input type="text" name="last_name" required class="w-full px-4 py-3 border border-[#f1e7cc] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2">{{ __('admin_ui.email') }}</label>
                <input type="email" name="email" required class="w-full px-4 py-3 border border-[#f1e7cc] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div id="addPasswordField">
                    <label class="block text-sm font-medium text-[#182438] mb-2">{{ __('admin_ui.password') }}</label>
                    <input type="password" name="password" id="addPassword" required minlength="8" autocomplete="off" class="password-entry w-full px-4 py-3 border-2 border-[#e7dcc5] rounded-xl focus:border-[#ff8555] focus:ring-2 focus:ring-[#ff8555] outline-none">
                    <p id="addPasswordHelp" class="text-xs text-[#6b7280] mt-1 hidden">Optional for parents. Leave blank to use password123; they will be asked to change it on first login.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#182438] mb-2">{{ __('admin_ui.role') }}</label>
                    <select name="role" required class="w-full px-4 py-3 border border-[#f1e7cc] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                        <option value="super_admin">{{ __('admin_ui.super_admin') }}</option>
                        <option value="teacher">{{ __('admin_ui.teacher') }}</option>
                        <option value="parent">{{ __('admin_ui.parent') }}</option>
                        <option value="student">{{ __('admin_ui.student') }}</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2">Contact (optional)</label>
                <input type="text" name="contact" class="w-full px-4 py-3 border border-[#f1e7cc] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
            </div>
            <div id="addStudentClassSection" class="hidden">
                <label class="block text-sm font-medium text-[#182438] mb-2">{{ __('admin_ui.section_class') }}</label>
                <select name="class_name" id="addStudentClassName" required
                    class="w-full px-4 py-3 border border-[#f1e7cc] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                    <option value="" selected disabled>{{ __('admin_ui.select_a_section') }}</option>
                    @foreach(\App\Models\Student::SECTIONS as $section)
                        <option value="{{ $section }}">{{ $section }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-[#6b7280] mt-1">{{ __('admin_ui.section_class_help') }}</p>
            </div>
            <div id="addStudentYearSection" class="hidden">
                <label class="block text-sm font-medium text-[#182438] mb-2">{{ __('Academic year') }}</label>
                <x-academic-year-input name="academic_year" id="addAcademicYear" :value="\App\Models\Student::currentAcademicYear()" class="px-4 py-3" />
            </div>
            <div id="addStudentHandSection" class="hidden">
                <label class="block text-sm font-medium text-[#182438] mb-2">{{ __('student.hand_label') }}</label>
                <select name="dominant_hand"
                    class="w-full px-4 py-3 border border-[#f1e7cc] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                    @foreach(\App\Models\Student::HANDS as $hand)
                        <option value="{{ $hand }}" @selected($hand === 'Not yet established')>{{ \App\Models\Student::handLabels()[$hand] }}</option>
                    @endforeach
                </select>
            </div>
            <div id="addStudentTeacherSection" class="hidden">
                <label class="block text-sm font-medium text-[#182438] mb-2">{{ __('admin_ui.assign_teacher') }}</label>
                <select name="teacher_id" class="w-full px-4 py-3 border border-[#f1e7cc] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                    <option value="">{{ __('admin_ui.unassigned') }}</option>
                    @foreach($allTeachers as $teacher)
                        <option value="{{ $teacher->id }}">{{ $teacher->full_name }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-[#6b7280] mt-1">{{ __('admin_ui.assign_teacher_help') }}</p>
            </div>
            <div id="addStudentParentsSection" class="hidden">
                <label class="block text-sm font-medium text-[#182438] mb-2">{{ __('admin_ui.connect_parent_account_s') }}</label>
                <div class="relative mb-2">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#6b7280]">⌕</span>
                    <input type="text" id="addStudentParentSearch" placeholder="{{ __('admin_ui.search_parents_by_name_or_email') }}" autocomplete="off"
                        class="w-full pl-9 pr-4 py-2 border border-[#f1e7cc] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none text-sm">
                </div>
                <div id="addStudentParentList" class="border border-[#f1e7cc] rounded-lg max-h-40 overflow-y-auto p-3 space-y-2">
                    @forelse($allParents as $parent)
                    <label class="flex items-center gap-3 cursor-pointer hover:bg-[#fffbf0] px-2 py-1 rounded-lg transition parent-item">
                        <input type="checkbox" name="parent_ids[]" value="{{ $parent->id }}" class="w-4 h-4 text-[#ff8555] rounded focus:ring-[#ff8555]">
                        <span class="text-sm text-[#5f6873] parent-label">{{ $parent->full_name }} <span class="text-xs text-[#6b7280]">({{ $parent->email }})</span></span>
                    </label>
                    @empty
                    <p class="text-sm text-[#6b7280] p-2">{{ __('admin_ui.no_parent_accounts_yet') }}</p>
                    @endforelse
                </div>
                <p class="text-xs text-[#6b7280] mt-1">{{ __('admin_ui.connect_parent_help') }}</p>
            </div>
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="this.closest('#addUserModal').classList.add('hidden')" class="px-6 py-2.5 border border-[#f1e7cc] rounded-lg text-[#5f6873] hover:bg-[#fffbf0] transition">{{ __('admin_ui.cancel') }}</button>
                <button type="submit" class="px-6 py-2.5 bg-[#ff8555] text-white rounded-lg hover:bg-[#f47548] transition font-medium">{{ __('admin_ui.create_account_title') }}</button>
            </div>
        </form>
    </div>
</div>

<div id="editUserModal" class="fixed inset-0 bg-black/50 flex items-start sm:items-center justify-center z-50 hidden p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl p-5 sm:p-8 w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-[#182438]">{{ __('admin_ui.edit_account') }}</h3>
            <button onclick="this.closest('#editUserModal').classList.add('hidden')" class="text-[#6b7280] hover:text-[#5f6873] text-2xl">&times;</button>
        </div>
        <form method="POST" id="editUserForm" class="space-y-4">
            @csrf @method('PUT')
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-[#182438] mb-2">{{ __('admin_ui.first_name') }}</label>
                    <input type="text" name="first_name" id="editFirstName" required class="w-full px-4 py-3 border border-[#f1e7cc] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#182438] mb-2">{{ __('admin_ui.last_name') }}</label>
                    <input type="text" name="last_name" id="editLastName" required class="w-full px-4 py-3 border border-[#f1e7cc] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2">{{ __('admin_ui.email') }}</label>
                <input type="email" name="email" id="editEmail" required class="w-full px-4 py-3 border border-[#f1e7cc] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                <label class="block text-sm font-medium text-[#182438] mb-2">{{ __('admin_ui.contact_optional') }}</label>
                    <input type="text" name="contact" id="editContact" class="w-full px-4 py-3 border border-[#f1e7cc] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#182438] mb-2">{{ __('admin_ui.new_password_optional') }}</label>
                    <input type="password" name="password" id="editPassword" autocomplete="off" class="password-entry w-full px-4 py-3 border-2 border-[#e7dcc5] rounded-xl focus:border-[#ff8555] focus:ring-2 focus:ring-[#ff8555] outline-none" placeholder="{{ __('admin_ui.leave_blank_to_keep') }}">
                </div>
            </div>
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="is_active" id="editIsActive" value="1" class="w-5 h-5 text-[#ff8555] rounded focus:ring-[#ff8555]">
                <span class="text-sm font-medium text-[#182438]">{{ __('admin_ui.account_is_active') }}</span>
            </label>

            <div id="editClassField" class="hidden">
                <label class="block text-sm font-medium text-[#182438] mb-2">{{ __('admin_ui.class') }}</label>
                <select name="class_name" id="editClassName" class="w-full px-4 py-3 border border-[#f1e7cc] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                    <option value="">{{ __('admin_ui.not_set') }}</option>
                    @foreach(\App\Models\Student::SECTIONS as $section)
                        <option value="{{ $section }}">{{ $section }}</option>
                    @endforeach
                </select>
            </div>

            <div id="editYearField" class="hidden">
                <label class="block text-sm font-medium text-[#182438] mb-2">{{ __('Academic year') }}</label>
                <x-academic-year-input name="academic_year" id="editAcademicYear" class="px-4 py-3" />
            </div>

            <div id="editHandField" class="hidden">
                <label class="block text-sm font-medium text-[#182438] mb-2">{{ __('student.hand_label') }}</label>
                <select name="dominant_hand" id="editDominantHand" class="w-full px-4 py-3 border border-[#f1e7cc] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                    @foreach(\App\Models\Student::HANDS as $hand)
                        <option value="{{ $hand }}" @selected($hand === 'Not yet established')>{{ \App\Models\Student::handLabels()[$hand] }}</option>
                    @endforeach
                </select>
            </div>

            <div id="editConnections" class="hidden">
                <label class="block text-sm font-medium text-[#182438] mb-2" id="editConnLabel">{{ __('admin_ui.connect_students') }}</label>
                <div class="relative mb-2">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#6b7280]">⌕</span>
                    <input type="text" id="editConnSearch" placeholder="{{ __('admin_ui.search_students') }}" autocomplete="off"
                        class="w-full pl-9 pr-4 py-2 border border-[#f1e7cc] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none text-sm">
                </div>
                <div id="editConnList" class="border border-[#f1e7cc] rounded-lg max-h-40 overflow-y-auto p-3 space-y-2">
                    @forelse($allStudents as $student)
                    <label class="flex items-center gap-3 cursor-pointer hover:bg-[#fffbf0] px-2 py-1 rounded-lg transition conn-item" data-name="{{ $student->name }}">
                        <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" class="w-4 h-4 text-[#ff8555] rounded focus:ring-[#ff8555]">
                        <span class="text-sm text-[#5f6873]">{{ $student->name }}</span>
                    </label>
                    @empty
                    <p class="text-sm text-[#6b7280] p-2">{{ __('admin_ui.no_students_yet') }}</p>
                    @endforelse
                </div>
                <p class="text-xs text-[#6b7280] mt-1">{{ __('admin_ui.connect_parent_students_help') }}</p>
            </div>

            <div id="editParentConnections" class="hidden">
                <label class="block text-sm font-medium text-[#182438] mb-2">{{ __('admin_ui.connect_parents') }}</label>
                <div class="relative mb-2">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#6b7280]">⌕</span>
                    <input type="text" id="editParentConnSearch" placeholder="{{ __('admin_ui.search_parents') }}" autocomplete="off"
                        class="w-full pl-9 pr-4 py-2 border border-[#f1e7cc] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none text-sm">
                </div>
                <div id="editParentConnList" class="border border-[#f1e7cc] rounded-lg max-h-40 overflow-y-auto p-3 space-y-2">
                    @forelse($allParents as $parent)
                    <label class="flex items-center gap-3 cursor-pointer hover:bg-[#fffbf0] px-2 py-1 rounded-lg transition parent-conn-item" data-name="{{ $parent->full_name }}">
                        <input type="checkbox" name="parent_ids[]" value="{{ $parent->id }}" class="w-4 h-4 text-[#ff8555] rounded focus:ring-[#ff8555]">
                        <span class="text-sm text-[#5f6873]">{{ $parent->full_name }}</span>
                    </label>
                    @empty
                    <p class="text-sm text-[#6b7280] p-2">{{ __('admin_ui.no_parent_accounts_yet') }}</p>
                    @endforelse
                </div>
                <p class="text-xs text-[#6b7280] mt-1">{{ __('admin_ui.connect_parent_help') }}</p>
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="this.closest('#editUserModal').classList.add('hidden')" class="px-6 py-2.5 border border-[#f1e7cc] rounded-lg text-[#5f6873] hover:bg-[#fffbf0] transition">{{ __('admin_ui.cancel') }}</button>
                <button type="submit" class="px-6 py-2.5 bg-[#ff8555] text-white rounded-lg hover:bg-[#f47548] transition font-medium">{{ __('admin_ui.save_changes') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
const VIEWS = ['active', 'archived'];

function currentView() {
    return VIEWS.find(v => !document.getElementById(v + 'View').classList.contains('hidden')) || 'active';
}

function switchView(view) {
    const url = new URL(window.location.href);
    url.searchParams.set('view', view);
    url.searchParams.delete('active_page');
    url.searchParams.delete('archived_page');
    window.location.assign(url.toString());
}

// Search against the database, not just the ten rows currently on screen.
// Debouncing avoids a request for every keystroke.
const searchInput = document.querySelector('input[name="search"]');
const filterForm = document.getElementById('accountFilterForm');
let searchDebounce;
if (searchInput && filterForm) {
    searchInput.addEventListener('input', () => {
        window.clearTimeout(searchDebounce);
        searchDebounce = window.setTimeout(() => {
            filterForm.querySelector('input[name="view"]').value = currentView();
            filterForm.requestSubmit();
        }, 400);
    });
}

document.querySelectorAll('.archive-form').forEach(form => {
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const name = this.dataset.name || '{{ __('admin_ui.student') }}';  // fallback not critical
        const restoring = this.dataset.archived === '1';
        Swal.fire({
            title: restoring ? '{{ __('admin_ui.restore_account_question') }}' : '{{ __('admin_ui.archive_account_question') }}',
            text: restoring
                ? '{{ __('admin_ui.account_will_be_able_to_login', ['name' => ':name']) }}'.replace(':name', name)
                : '{{ __('admin_ui.account_will_be_signed_out', ['name' => ':name']) }}'.replace(':name', name),
            icon: restoring ? 'question' : 'warning',
            showCancelButton: true,
            confirmButtonColor: restoring ? '#8ac926' : '#ff595e',
            cancelButtonColor: '#5f6873',
            confirmButtonText: restoring ? '{{ __('admin_ui.yes_restore') }}' : '{{ __('admin_ui.yes_archive') }}',
            cancelButtonText: '{{ __('admin_ui.cancel') }}',
        }).then((result) => {
            if (result.isConfirmed) {
                this.submit();
            }
        });
    });
});

const editModal = document.getElementById('editUserModal');
const editForm = document.getElementById('editUserForm');

document.querySelectorAll('.edit-user-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const d = JSON.parse(this.dataset.edit);

        document.getElementById('editFirstName').value = d.first_name;
        document.getElementById('editLastName').value = d.last_name;
        document.getElementById('editEmail').value = d.email;
        document.getElementById('editContact').value = d.contact || '';
        const editPassword = document.getElementById('editPassword');
        if (editPassword) editPassword.value = '';
        document.getElementById('editIsActive').checked = d.is_active;

        editForm.action = `/admin/users/${d.id}`;

        const classField = document.getElementById('editClassField');
        document.getElementById('editClassName').value = d.class_name || '';
        classField.classList.toggle('hidden', d.role !== 'student');

        const yearField = document.getElementById('editYearField');
        window.setAcademicYear(document.getElementById('editAcademicYear'), d.academic_year || '');
        yearField.classList.toggle('hidden', d.role !== 'student');

        const handField = document.getElementById('editHandField');
        document.getElementById('editDominantHand').value = d.dominant_hand || 'Not yet established';
        handField.classList.toggle('hidden', d.role !== 'student');

        const connBox = document.getElementById('editConnections');
        const parentConnBox = document.getElementById('editParentConnections');
        if (d.role === 'parent') {
            document.getElementById('editConnLabel').textContent = '{{ __('admin_ui.connect_students') }}';
            connBox.classList.remove('hidden');
            parentConnBox.classList.add('hidden');
            editForm.querySelectorAll('.conn-item').forEach(item => {
                item.style.display = '';
            });
            const checked = new Set(d.student_ids || []);
            editForm.querySelectorAll('.conn-item input').forEach(cb => {
                cb.checked = checked.has(Number(cb.value));
            });
        } else if (d.role === 'student') {
            connBox.classList.add('hidden');
            parentConnBox.classList.remove('hidden');
            editForm.querySelectorAll('.parent-conn-item').forEach(item => {
                item.style.display = '';
            });
            const checked = new Set(d.parent_ids || []);
            editForm.querySelectorAll('.parent-conn-item input').forEach(cb => {
                cb.checked = checked.has(Number(cb.value));
            });
        } else {
            connBox.classList.add('hidden');
            parentConnBox.classList.add('hidden');
        }

        document.getElementById('editConnSearch').value = '';
        document.getElementById('editParentConnSearch').value = '';
        editModal.classList.remove('hidden');
    });
});

const connSearch = document.getElementById('editConnSearch');
if (connSearch) {
    connSearch.addEventListener('input', function () {
        const q = this.value.toLowerCase().trim();
        editForm.querySelectorAll('.conn-item').forEach(item => {
            item.style.display = item.dataset.name.toLowerCase().includes(q) ? '' : 'none';
        });
    });
}

const parentConnSearch = document.getElementById('editParentConnSearch');
if (parentConnSearch) {
    parentConnSearch.addEventListener('input', function () {
        const q = this.value.toLowerCase().trim();
        editForm.querySelectorAll('.parent-conn-item').forEach(item => {
            item.style.display = item.dataset.name.toLowerCase().includes(q) ? '' : 'none';
        });
    });
}


const addUserRole = document.querySelector('#addUserModal select[name="role"]');
const addStudentParentsSection = document.getElementById('addStudentParentsSection');
const addStudentTeacherSection = document.getElementById('addStudentTeacherSection');
const addStudentClassSection = document.getElementById('addStudentClassSection');
const addStudentHandSection = document.getElementById('addStudentHandSection');
const addStudentYearSection = document.getElementById('addStudentYearSection');
const addPasswordField = document.getElementById('addPasswordField');
const addPasswordInput = document.getElementById('addPassword');
const addPasswordHelp = document.getElementById('addPasswordHelp');
const addStudentClassInput = document.getElementById('addStudentClassName');

if (addUserRole) {
    // Students get a Section / Class instead of a hand-typed password, so the
    // password field is swapped out and the generated password is not requested.
    const applyRoleFields = function () {
        const isStudent = this.value === 'student';
        const isParent = this.value === 'parent';

        if (addStudentParentsSection) {
            addStudentParentsSection.classList.toggle('hidden', !isStudent);
        }
        if (addStudentTeacherSection) {
            addStudentTeacherSection.classList.toggle('hidden', !isStudent);
        }
        if (addStudentClassSection) {
            addStudentClassSection.classList.toggle('hidden', !isStudent);
        }
        if (addStudentHandSection) {
            addStudentHandSection.classList.toggle('hidden', !isStudent);
        }
        if (addStudentYearSection) {
            addStudentYearSection.classList.toggle('hidden', !isStudent);
        }
        if (addPasswordField) {
            addPasswordField.classList.toggle('hidden', isStudent);
        }
        if (addPasswordInput) {
            addPasswordInput.disabled = isStudent;
            addPasswordInput.required = !isStudent && !isParent;
        }
        if (addPasswordHelp) {
            addPasswordHelp.classList.toggle('hidden', !isParent);
        }
        if (addStudentClassInput) {
            addStudentClassInput.required = isStudent;
        }
    };

    addUserRole.addEventListener('change', applyRoleFields);
    applyRoleFields.call(addUserRole);
}

const addStudentParentSearch = document.getElementById('addStudentParentSearch');
if (addStudentParentSearch) {
    addStudentParentSearch.addEventListener('input', function () {
        const q = this.value.toLowerCase().trim();
        document.querySelectorAll('#addStudentParentList .parent-item').forEach(item => {
            item.style.display = item.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });
}
</script>
@endpush