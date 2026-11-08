@extends('layouts.teacher')

@section('title', __('teacher.accounts_title'))
@section('page-title', __('teacher.accounts_title'))

@section('content')
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-[#182438]">{{ __('teacher.manage_accounts') }}</h2>
            <p class="text-sm text-[#5f6873] mt-1">{{ __('teacher.manage_accounts_subtitle') }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-3 self-start md:self-auto">
            <div class="flex items-center gap-1 bg-[#f5f3ee] rounded-lg p-1">
                <button type="button" id="viewStudentsBtn" onclick="switchView('students')"
                    class="px-5 py-2 rounded-md text-sm font-semibold transition bg-[#ff8555] text-white">
                    {{ __('teacher.students') }}
                </button>
                <button type="button" id="viewParentsBtn" onclick="switchView('parents')"
                    class="px-5 py-2 rounded-md text-sm font-semibold transition text-[#5f6873] hover:text-[#182438]">
                    {{ __('teacher.parents') }}
                </button>
                <button type="button" id="viewArchivedBtn" onclick="switchView('archived')"
                    class="px-5 py-2 rounded-md text-sm font-semibold transition text-[#5f6873] hover:text-[#182438]">
                    {{ __('teacher.archived') }}
                    @if($archivedStudents->count() + $archivedParents->count() > 0)
                        <span class="ml-1.5 px-1.5 py-0.5 rounded-full bg-[#ebe8e1] text-[#5f6873] text-xs">{{ $archivedStudents->count() + $archivedParents->count() }}</span>
                    @endif
                </button>
                <button type="button" id="viewRequestsBtn" onclick="switchView('requests')"
                    class="px-5 py-2 rounded-md text-sm font-semibold transition text-[#5f6873] hover:text-[#182438]">
                    {{ __('teacher.requests_tab') }}
                    @if($requestCounts['pending'] > 0)
                        <span class="ml-1.5 px-1.5 py-0.5 rounded-full bg-[#fdf3d7] text-[#8a6d10] text-xs">{{ $requestCounts['pending'] }}</span>
                    @endif
                </button>
            </div>
            <div id="requestActions" class="flex flex-wrap gap-2">
                <button type="button" onclick="openAccountRequest()" class="bg-[#ff8555] hover:bg-[#f47548] text-white px-6 py-2.5 rounded-lg transition font-medium">
                    <i class="ico ico-add-circle mr-1" aria-hidden="true"></i> {{ __('teacher.request_account') }}
                </button>
            </div>
        </div>
    </div>
    <div class="mt-4">
        <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#8b949d]">⌕</span>
            <input type="text" id="studentSearch" placeholder="{{ __('teacher.search_accounts') }}" autocomplete="off"
                class="w-full md:w-80 pl-9 pr-4 py-2.5 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
        </div>
    </div>
</div>

<div id="studentsView">
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-[#faf8f3]">
<tr>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-[#182438]">{{ __('teacher.name') }}</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-[#182438]">{{ __('teacher.age') }}</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-[#182438]">{{ __('teacher.class') }}</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-[#182438]">{{ __('student.hand_label') }}</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-[#182438]">{{ __('teacher.parent') }}</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-[#182438]">{{ __('teacher.enrolled') }}</th>
                    <th class="px-6 py-4 text-center text-sm font-semibold text-[#182438]">{{ __('teacher.status') }}</th>
                    <th class="px-6 py-4 text-center text-sm font-semibold text-[#182438]">{{ __('teacher.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#ebe8e1]">
@forelse($students as $student)
                @php
                    $editPayload = [
                        'id' => $student->id,
                        'first_name' => $student->first_name,
                        'last_name' => $student->last_name,
                        'sex' => $student->sex,
                        'date_of_birth' => $student->date_of_birth?->format('Y-m-d'),
                        'address' => $student->address,
                        'class_name' => $student->class_name,
                        'dominant_hand' => $student->dominant_hand,
                        'parent_ids' => $student->parents->pluck('id')->all(),
                    ];
                @endphp
                <tr class="hover:bg-[#faf8f3] transition">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-[#ff8555] rounded-full flex items-center justify-center text-white font-bold text-sm">
                                {{ strtoupper(substr($student->first_name, 0, 1)) }}{{ strtoupper(substr($student->last_name, 0, 1)) }}
                            </div>
                            <span class="font-medium text-[#182438]">{{ $student->name }}</span>
                        </div>
                    </td>
<td class="px-6 py-4 text-[#5f6873]">{{ $student->age }} {{ __('teacher.yrs') }}</td>
                    <td class="px-6 py-4">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $student->class_name ? 'bg-[#fff0e6] text-[#ff8555]' : 'bg-[#ebe8e1] text-[#8b949d]' }}">
                            {{ $student->class_name ?? __('teacher.unassigned') }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-[#5f6873]">{{ $student->handLabel() }}</td>
                    <td class="px-6 py-4 text-[#5f6873]">{{ $student->parents->isEmpty() ? '—' : $student->parents->pluck('full_name')->implode(', ') }}</td>
                    <td class="px-6 py-4 text-[#5f6873]">{{ $student->enrolled_date?->format('Y-m-d') ?? '—' }}</td>
                    <td class="px-6 py-4 text-center">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $student->is_active ? 'bg-green-100 text-green-700' : 'bg-[#ebe8e1] text-[#5f6873]' }}">
                            {{ $student->is_active ? __('teacher.active') : __('teacher.inactive') }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-center gap-2">
                            <button type="button" title="{{ __('teacher.edit') }}"
                                data-edit='@json($editPayload)'
                                class="edit-student-btn w-9 h-9 rounded-lg border border-[#ebe8e1] hover:bg-[#faf8f3] transition text-[#258cc5]">
                                ✎
                            </button>
                            <form method="POST" action="{{ route('teacher.accounts.toggle', $student) }}"
                                data-confirm="{{ __('teacher.archive_student_confirm') }}" data-confirm-title="{{ __('teacher.archive_title') }}" data-confirm-yes="{{ __('teacher.archive') }}" data-confirm-no="{{ __('teacher.cancel') }}">
                                @csrf @method('PUT')
                                <button type="submit" title="{{ __('teacher.archive') }}"
                                    class="w-9 h-9 rounded-lg border border-[#ebe8e1] hover:bg-[#faf8f3] transition text-[#258cc5]">
                                    <img src="{{ asset('assets/icons/file-tray-stacked.svg') }}" alt="Paused" class="inline-block h-5 w-5">
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-6 py-12 text-center text-[#8b949d]">{{ __('teacher.no_students_found') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
</div>

<div id="parentsView" class="hidden">
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-[#faf8f3]">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-[#182438]">{{ __('teacher.name') }}</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-[#182438]">{{ __('teacher.email') }}</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-[#182438]">{{ __('teacher.contact') }}</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-[#182438]">{{ __('teacher.children') }}</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold text-[#182438]">{{ __('teacher.status') }}</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold text-[#182438]">{{ __('teacher.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#ebe8e1]">
                    @forelse($activeParents as $parent)
                    <tr class="hover:bg-[#faf8f3] transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-[#258cc5] rounded-full flex items-center justify-center text-white font-bold text-sm">
                                    {{ strtoupper(substr($parent->first_name, 0, 1)) }}{{ strtoupper(substr($parent->last_name, 0, 1)) }}
                                </div>
                                <span class="font-medium text-[#182438]">{{ $parent->full_name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-[#5f6873]">{{ $parent->email }}</td>
                        <td class="px-6 py-4 text-[#5f6873]">{{ $parent->contact ?? '—' }}</td>
                        <td class="px-6 py-4">
                            @if($parent->children->isEmpty())
                                <span class="text-[#8b949d]">—</span>
                            @else
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-[#fff0e6] text-[#ff8555]">
                                    {{ $parent->children->count() }} {{ Str::plural('child', $parent->children->count()) }}
                                </span>
                                <span class="ml-2 text-sm text-[#5f6873]">{{ $parent->children->pluck('name')->implode(', ') }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $parent->is_active ? 'bg-green-100 text-green-700' : 'bg-[#ebe8e1] text-[#5f6873]' }}">
                                {{ $parent->is_active ? __('teacher.active') : __('teacher.inactive') }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                @php
                                    $parentEditPayload = [
                                        'id' => $parent->id,
                                        'first_name' => $parent->first_name,
                                        'last_name' => $parent->last_name,
                                        'email' => $parent->email,
                                        'contact' => $parent->contact,
                                    ];
                                @endphp
                                <button type="button" title="{{ __('teacher.edit') }}"
                                    data-edit-parent='@json($parentEditPayload)'
                                    class="edit-parent-btn w-9 h-9 rounded-lg border border-[#ebe8e1] hover:bg-[#faf8f3] transition text-[#258cc5]">
                                    ✎
                                </button>
                                <form method="POST" action="{{ route('teacher.accounts.parent.toggle', $parent) }}"
                                    data-confirm="{{ __('teacher.archive_parent_confirm') }}" data-confirm-title="{{ __('teacher.archive_title') }}" data-confirm-yes="{{ __('teacher.archive') }}" data-confirm-no="{{ __('teacher.cancel') }}">
                                    @csrf @method('PUT')
                                    <button type="submit" title="{{ __('teacher.archive') }}"
                                        class="w-9 h-9 rounded-lg border border-[#ebe8e1] hover:bg-[#faf8f3] transition text-[#258cc5]">
                                        <img src="{{ asset('assets/icons/file-tray-stacked.svg') }}" alt="Paused" class="inline-block h-5 w-5">
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-6 py-12 text-center text-[#8b949d]">{{ __('teacher.no_parent_accounts_found') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="archivedView" class="hidden">
    <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
        <div class="px-6 py-4 bg-[#faf8f3] border-b border-[#ebe8e1]">
            <h3 class="text-sm font-bold text-[#182438]">{{ __('teacher.archived_students') }}</h3>
            <p class="text-xs text-[#5f6873] mt-0.5">{{ __('teacher.archived_students_desc') }}</p>
        </div>
        <div class="overflow-auto max-h-[26rem]">
            <table class="w-full">
                <thead class="bg-[#faf8f3] sticky top-0 z-10 shadow-[0_1px_0_#ebe8e1]">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-[#182438]">{{ __('teacher.name') }}</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-[#182438]">{{ __('teacher.class') }}</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-[#182438]">{{ __('student.hand_label') }}</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-[#182438]">{{ __('teacher.parent') }}</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-[#182438]">{{ __('teacher.enrolled') }}</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold text-[#182438]">{{ __('teacher.status') }}</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold text-[#182438]">{{ __('teacher.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#ebe8e1]">
                    @forelse($archivedStudents as $student)
                    <tr class="hover:bg-[#faf8f3] transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-[#c9ced6] rounded-full flex items-center justify-center text-white font-bold text-sm">
                                    {{ strtoupper(substr($student->first_name, 0, 1)) }}{{ strtoupper(substr($student->last_name, 0, 1)) }}
                                </div>
                                <span class="font-medium text-[#5f6873]">{{ $student->name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-[#5f6873]">{{ $student->class_name ?? '—' }}</td>
                        <td class="px-6 py-4 text-[#5f6873]">{{ $student->handLabel() }}</td>
                        <td class="px-6 py-4 text-[#5f6873]">{{ $student->parents->isEmpty() ? '—' : $student->parents->pluck('full_name')->implode(', ') }}</td>
                        <td class="px-6 py-4 text-[#5f6873]">{{ $student->enrolled_date?->format('Y-m-d') ?? '—' }}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-[#ebe8e1] text-[#5f6873]">{{ __('teacher.archived_badge') }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center">
                                <form method="POST" action="{{ route('teacher.accounts.toggle', $student) }}">
                                    @csrf @method('PUT')
                                    <button type="submit" title="{{ __('teacher.restore') }}"
                                        class="w-9 h-9 rounded-lg border border-[#ebe8e1] hover:bg-[#faf8f3] transition text-green-600">
                                        ▶
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-6 py-12 text-center text-[#8b949d]">{{ __('teacher.no_archived_students') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-[#faf8f3] border-b border-[#ebe8e1]">
            <h3 class="text-sm font-bold text-[#182438]">{{ __('teacher.archived_parent_accounts') }}</h3>
            <p class="text-xs text-[#5f6873] mt-0.5">{{ __('teacher.archived_parent_accounts_desc') }}</p>
        </div>
        <div class="overflow-auto max-h-[26rem]">
            <table class="w-full">
                <thead class="bg-[#faf8f3] sticky top-0 z-10 shadow-[0_1px_0_#ebe8e1]">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-[#182438]">{{ __('teacher.name') }}</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-[#182438]">{{ __('teacher.email') }}</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-[#182438]">{{ __('teacher.children') }}</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold text-[#182438]">{{ __('teacher.status') }}</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold text-[#182438]">{{ __('teacher.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#ebe8e1]">
                    @forelse($archivedParents as $parent)
                    <tr class="hover:bg-[#faf8f3] transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-[#c9ced6] rounded-full flex items-center justify-center text-white font-bold text-sm">
                                    {{ strtoupper(substr($parent->first_name, 0, 1)) }}{{ strtoupper(substr($parent->last_name, 0, 1)) }}
                                </div>
                                <span class="font-medium text-[#5f6873]">{{ $parent->full_name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-[#5f6873]">{{ $parent->email }}</td>
                        <td class="px-6 py-4 text-[#5f6873]">
                            {{ $parent->children->isEmpty() ? '—' : $parent->children->count() . ' ' . Str::plural('child', $parent->children->count()) }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-[#ebe8e1] text-[#5f6873]">{{ __('teacher.archived_badge') }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center">
                                <form method="POST" action="{{ route('teacher.accounts.parent.toggle', $parent) }}">
                                    @csrf @method('PUT')
                                    <button type="submit" title="{{ __('teacher.restore') }}"
                                        class="w-9 h-9 rounded-lg border border-[#ebe8e1] hover:bg-[#faf8f3] transition text-green-600">
                                        ▶
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-6 py-12 text-center text-[#8b949d]">{{ __('teacher.no_archived_parent_accounts') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@include('teacher.partials.account-requests-list')

@include('teacher.partials.account-request-modal')

<div id="editParentAccountModal" class="fixed inset-0 bg-black/50 flex items-start sm:items-center justify-center z-50 hidden p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl p-8 w-full max-w-md">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-[#182438]">{{ __('teacher.edit_parent_account') }}</h3>
            <button type="button" onclick="this.closest('#editParentAccountModal').classList.add('hidden')" class="text-[#8b949d] hover:text-[#5f6873] text-2xl">&times;</button>
        </div>
        <form method="POST" id="editParentAccountForm" class="space-y-4">
            @csrf @method('PUT')
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-[#182438] mb-2">{{ __('teacher.first_name') }}</label>
                    <input type="text" name="first_name" id="editPFirstName" required class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#182438] mb-2">{{ __('teacher.last_name') }}</label>
                    <input type="text" name="last_name" id="editPLastName" required class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2">{{ __('teacher.email') }}</label>
                <input type="email" name="email" id="editPEmail" required class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2">{{ __('teacher.new_password_optional') }}</label>
                <input type="password" name="password" id="editPPassword" minlength="8" autocomplete="new-password" class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2">{{ __('teacher.contact_optional') }}</label>
                <input type="text" name="contact" id="editPContact" class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
            </div>
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="this.closest('#editParentAccountModal').classList.add('hidden')" class="px-6 py-2.5 border border-[#ebe8e1] rounded-lg text-[#5f6873] hover:bg-[#faf8f3] transition">{{ __('teacher.cancel') }}</button>
                <button type="submit" class="px-6 py-2.5 bg-[#258cc5] text-white rounded-lg hover:bg-[#1e73a8] transition font-medium">{{ __('teacher.save_changes') }}</button>
            </div>
        </form>
    </div>
</div>

<div id="editStudentModal" class="fixed inset-0 bg-black/50 flex items-start sm:items-center justify-center z-50 hidden p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl p-8 w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-[#182438]">{{ __('teacher.edit_student') }}</h3>
            <button onclick="this.closest('#editStudentModal').classList.add('hidden')" class="text-[#8b949d] hover:text-[#5f6873] text-2xl">&times;</button>
        </div>
        <form method="POST" id="editStudentForm" class="space-y-4">
            @csrf @method('PUT')
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-[#182438] mb-2">{{ __('teacher.first_name') }}</label>
                    <input type="text" name="first_name" id="editSFirstName" required class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#182438] mb-2">{{ __('teacher.last_name') }}</label>
                    <input type="text" name="last_name" id="editSLastName" required class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-[#182438] mb-2">{{ __('teacher.sex') }}</label>
                    <select name="sex" id="editSSex" required class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                        <option value="Female">{{ __('teacher.female') }}</option>
                        <option value="Male">{{ __('teacher.male') }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#182438] mb-2">{{ __('teacher.date_of_birth') }}</label>
                    <input type="date" name="date_of_birth" id="editSDOB" required class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2">{{ __('teacher.class') }}</label>
                <select name="class_name" id="editSClass" class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                    <option value="">{{ __('teacher.not_set') }}</option>
                    @foreach(\App\Models\Student::SECTIONS as $section)
                        <option value="{{ $section }}">{{ $section }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2">{{ __('student.hand_label') }}</label>
                <select name="dominant_hand" id="editSHand" class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                    @foreach(\App\Models\Student::HANDS as $hand)
                        <option value="{{ $hand }}" @selected($hand === 'Not yet established')>{{ \App\Models\Student::handLabels()[$hand] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2">{{ __('teacher.address') }}</label>
                <input type="text" name="address" id="editSAddress" class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2">{{ __('teacher.connect_parent_accounts') }}</label>
                <div class="relative mb-2">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#8b949d]">⌕</span>
                    <input type="text" id="editParentSearch" placeholder="{{ __('teacher.search_parents') }}" autocomplete="off"
                        class="w-full pl-9 pr-4 py-2 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none text-sm">
                </div>
                <div id="editParentList" class="border border-[#ebe8e1] rounded-lg max-h-40 overflow-y-auto p-3 space-y-2">
                    @foreach($parents as $parent)
                    <label class="flex items-center gap-3 cursor-pointer hover:bg-[#faf8f3] px-2 py-1 rounded-lg transition parent-item">
                        <input type="checkbox" name="parent_ids[]" value="{{ $parent->id }}" class="w-4 h-4 text-[#ff8555] rounded focus:ring-[#ff8555]">
                        <span class="text-sm text-[#5f6873] parent-label">{{ $parent->full_name }} <span class="text-xs text-[#8b949d]">({{ $parent->email }})</span></span>
                    </label>
                    @endforeach
                </div>
                <p class="text-xs text-[#8b949d] mt-1">{{ __('teacher.tick_multiple_parents') }}</p>
            </div>
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="this.closest('#editStudentModal').classList.add('hidden')" class="px-6 py-2.5 border border-[#ebe8e1] rounded-lg text-[#5f6873] hover:bg-[#faf8f3] transition">{{ __('teacher.cancel') }}</button>
                <button type="submit" class="px-6 py-2.5 bg-[#ff8555] text-white rounded-lg hover:bg-[#f47548] transition font-medium">{{ __('teacher.save_changes') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
const VIEWS = ['students', 'parents', 'archived', 'requests'];

function currentView() {
    return VIEWS.find(v => !document.getElementById(v + 'View').classList.contains('hidden')) || 'students';
}

function filterActiveTable() {
    const input = document.getElementById('studentSearch');
    const search = (input ? input.value : '').toLowerCase().trim();
    document.querySelectorAll('#' + currentView() + 'View tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(search) ? '' : 'none';
    });
}

// The button asks for the kind of account that matches the tab you are on.
function openAccountRequest() {
    const view = currentView();
    window.dispatchEvent(new CustomEvent('open-account-request', {
        detail: { fresh: true, role: view === 'students' ? 'student' : 'parent' }
    }));
}

function switchView(view) {
    VIEWS.forEach(v => document.getElementById(v + 'View').classList.toggle('hidden', v !== view));

    const btnActive = 'px-5 py-2 rounded-md text-sm font-semibold transition bg-[#ff8555] text-white';
    const btnIdle = 'px-5 py-2 rounded-md text-sm font-semibold transition text-[#5f6873] hover:text-[#182438]';
    VIEWS.forEach(v => {
        document.getElementById('view' + v.charAt(0).toUpperCase() + v.slice(1) + 'Btn').className = v === view ? btnActive : btnIdle;
    });

    // Archiving lives on the live accounts; the archived list is restore-only.
    // Accounts are requested from the live lists and the Requests tab; the archive is restore-only.
    document.getElementById('requestActions').style.display = view === 'archived' ? 'none' : '';

    const input = document.getElementById('studentSearch');
    if (input) input.value = '';
    VIEWS.forEach(v => {
        document.querySelectorAll('#' + v + 'View tbody tr').forEach(row => row.style.display = '');
    });
}

@if(session('open_view') === 'requests' || ($errors->any() && old('role')))
switchView('requests');
@endif

const searchInput = document.getElementById('studentSearch');

if (searchInput) {
    searchInput.addEventListener('input', filterActiveTable);
}

const parentSearch = document.getElementById('parentSearch');
const parentItems = document.querySelectorAll('.parent-item');

if (parentSearch) {
    parentSearch.addEventListener('input', function () {
        const search = this.value.toLowerCase().trim();
        parentItems.forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(search) ? '' : 'none';
        });
    });
}

const editParentAccountModal = document.getElementById('editParentAccountModal');
const editParentAccountForm = document.getElementById('editParentAccountForm');
const parentUpdateUrl = @json(url('teacher/parents'));

document.querySelectorAll('.edit-parent-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const d = JSON.parse(this.dataset.editParent);

        document.getElementById('editPFirstName').value = d.first_name || '';
        document.getElementById('editPLastName').value = d.last_name || '';
        document.getElementById('editPEmail').value = d.email || '';
        document.getElementById('editPContact').value = d.contact || '';
        document.getElementById('editPPassword').value = '';

        editParentAccountForm.action = `${parentUpdateUrl}/${d.id}`;
        editParentAccountModal.classList.remove('hidden');
    });
});

const editStudentModal = document.getElementById('editStudentModal');
const editStudentForm = document.getElementById('editStudentForm');

document.querySelectorAll('.edit-student-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const d = JSON.parse(this.dataset.edit);

        document.getElementById('editSFirstName').value = d.first_name;
        document.getElementById('editSLastName').value = d.last_name;
        document.getElementById('editSSex').value = d.sex;
        document.getElementById('editSDOB').value = d.date_of_birth;
        document.getElementById('editSClass').value = d.class_name || '';
        document.getElementById('editSHand').value = d.dominant_hand || 'Not yet established';
        document.getElementById('editSAddress').value = d.address || '';

        editStudentForm.action = `${window.location.origin}/teacher/accounts/${d.id}/update`;

        const checked = new Set(d.parent_ids || []);
        editStudentForm.querySelectorAll('#editParentList input').forEach(cb => {
            cb.checked = checked.has(Number(cb.value));
        });
        document.getElementById('editParentSearch').value = '';
        editStudentForm.querySelectorAll('#editParentList .parent-item').forEach(item => item.style.display = '');

        editStudentModal.classList.remove('hidden');
    });
});

const editParentSearch = document.getElementById('editParentSearch');
if (editParentSearch) {
    editParentSearch.addEventListener('input', function () {
        const search = this.value.toLowerCase().trim();
        editStudentForm.querySelectorAll('#editParentList .parent-item').forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(search) ? '' : 'none';
        });
    });
}
</script>
@endpush
