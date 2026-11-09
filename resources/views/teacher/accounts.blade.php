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
            <div class="flex items-center gap-1 bg-[#fdf3d6] rounded-lg p-1">
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
                        <span class="ml-1.5 px-1.5 py-0.5 rounded-full bg-[#f1e7cc] text-[#5f6873] text-xs">{{ $archivedStudents->count() + $archivedParents->count() }}</span>
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
                class="w-full md:w-80 pl-9 pr-4 py-2.5 border border-[#f1e7cc] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
        </div>
    </div>
</div>

<div id="studentsView">
@php
    $filterSections = collect(\App\Models\Student::SECTIONS)->filter(fn ($sec) => $students->contains('class_name', $sec))->values();
    $hasUnassigned = $students->contains(fn ($st) => ! $st->class_name);
    $filterAges = \App\Models\Student::ageFilterOptions($students->map(fn ($st) => $st->age));
    $filterYears = collect($academicYears)->filter(fn ($year) => $students->contains('academic_year', $year))->values();
    $hasNoYear = $students->contains(fn ($st) => ! $st->academic_year);
    $filterHands = collect(\App\Models\Student::handLabels())->filter(fn ($label, $key) => $students->contains('dominant_hand', $key));
    $filterClass = 'rounded-lg border border-[#f1e7cc] bg-white px-3 py-2 text-sm text-[#182438] outline-none focus:ring-2 focus:ring-[#ff8555]';
@endphp
<div id="studentFilters" class="mb-4 bg-white rounded-xl shadow-sm p-4" @if($students->count() < 2) hidden @endif>
    <div class="flex flex-wrap items-end gap-3">
        <label class="block text-xs font-semibold text-[#5f6873]">{{ __('Section') }}
            <select data-sf="section" class="{{ $filterClass }} mt-1 block">
                <option value="">{{ __('All sections') }}</option>
                @foreach($filterSections as $sec)<option value="{{ $sec }}">{{ $sec }}</option>@endforeach
                @if($hasUnassigned)<option value="__none">{{ __('teacher.unassigned') }}</option>@endif
            </select>
        </label>
        <label class="block text-xs font-semibold text-[#5f6873]">{{ __('Academic year') }}
            <select data-sf="year" class="{{ $filterClass }} mt-1 block">
                <option value="">{{ __('All academic years') }}</option>
                @foreach($filterYears as $year)<option value="{{ $year }}">{{ $year }}</option>@endforeach
                @if($hasNoYear)<option value="__none">{{ __('Not set') }}</option>@endif
            </select>
        </label>
        <label class="block text-xs font-semibold text-[#5f6873]">{{ __('Age') }}
            <select data-sf="age" class="{{ $filterClass }} mt-1 block">
                <option value="">{{ __('All ages') }}</option>
                @foreach($filterAges as $age)<option value="{{ $age }}">{{ \App\Models\Student::ageLabel($age) }}</option>@endforeach
            </select>
        </label>
        @if($filterHands->count() > 1)
        <label class="block text-xs font-semibold text-[#5f6873]">{{ __('student.hand_label') }}
            <select data-sf="hand" class="{{ $filterClass }} mt-1 block">
                <option value="">{{ __('All') }}</option>
                @foreach($filterHands as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach
            </select>
        </label>
        @endif
        <label class="block text-xs font-semibold text-[#5f6873]">{{ __('teacher.parent') }}
            <select data-sf="parent" class="{{ $filterClass }} mt-1 block">
                <option value="">{{ __('All') }}</option>
                <option value="linked">{{ __('Has parent account') }}</option>
                <option value="unlinked">{{ __('No parent account') }}</option>
            </select>
        </label>
        <label class="block text-xs font-semibold text-[#5f6873]">{{ __('Sort by') }}
            <select data-sf="sort" class="{{ $filterClass }} mt-1 block">
                <option value="newest">{{ __('Newest first') }}</option>
                <option value="name">{{ __('Name (A-Z)') }}</option>
                <option value="age_asc">{{ __('Age (youngest)') }}</option>
                <option value="age_desc">{{ __('Age (oldest)') }}</option>
                <option value="enrolled">{{ __('Recently enrolled') }}</option>
            </select>
        </label>
        <div class="ml-auto flex items-center gap-3 pb-1">
            <span id="studentFilterCount" class="text-xs font-semibold text-[#5f6873]" aria-live="polite"></span>
            <button type="button" id="studentFilterReset" class="hidden text-xs font-semibold text-[#ff8555] hover:text-[#f47548]">{{ __('Reset filters') }}</button>
        </div>
    </div>
</div>
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-[#fffbf0]">
<tr>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-[#182438]">{{ __('teacher.name') }}</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-[#182438]">{{ __('teacher.age') }}</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-[#182438]">{{ __('teacher.class') }}</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-[#182438]">{{ __('Academic year') }}</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-[#182438]">{{ __('student.hand_label') }}</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-[#182438]">{{ __('teacher.parent') }}</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-[#182438]">{{ __('teacher.enrolled') }}</th>
                    <th class="px-6 py-4 text-center text-sm font-semibold text-[#182438]">{{ __('teacher.status') }}</th>
                    <th class="px-6 py-4 text-center text-sm font-semibold text-[#182438]">{{ __('teacher.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#f1e7cc]">
@forelse($students as $student)
                @php $parentNames = $student->parents->pluck('full_name')->implode(', '); @endphp
                <tr class="hover:bg-[#fffbf0] transition" data-student-row
                    data-pos="{{ $loop->index }}"
                    data-name="{{ \Illuminate\Support\Str::lower($student->name) }}"
                    data-search="{{ \Illuminate\Support\Str::lower(trim($student->name.' '.$student->class_name.' '.$student->academic_year.' '.$student->handLabel().' '.$parentNames)) }}"
                    data-section="{{ $student->class_name ?? '' }}"
                    data-year="{{ $student->academic_year ?? '' }}"
                    data-age="{{ $student->age }}"
                    data-hand="{{ $student->dominant_hand ?? '' }}"
                    data-parents="{{ $student->parents->isEmpty() ? 0 : 1 }}"
                    data-enrolled="{{ $student->enrolled_date?->format('Y-m-d') ?? '' }}">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-[#ff8555] rounded-full flex items-center justify-center text-white font-bold text-sm">
                                {{ strtoupper(substr($student->first_name, 0, 1)) }}{{ strtoupper(substr($student->last_name, 0, 1)) }}
                            </div>
                            <span class="font-medium text-[#182438]">{{ $student->name }}</span>
                        </div>
                    </td>
<td class="px-6 py-4 text-[#5f6873]">{{ \App\Models\Student::ageLabel($student->age) }}</td>
                    <td class="px-6 py-4">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $student->class_name ? 'bg-[#fff0e6] text-[#ff8555]' : 'bg-[#f1e7cc] text-[#8b949d]' }}">
                            {{ $student->class_name ?? __('teacher.unassigned') }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-[#5f6873] whitespace-nowrap">{{ $student->academic_year ?? '—' }}</td>
                    <td class="px-6 py-4 text-[#5f6873]">{{ $student->handLabel() }}</td>
                    <td class="px-6 py-4 text-[#5f6873]">{{ $student->parents->isEmpty() ? '—' : $student->parents->pluck('full_name')->implode(', ') }}</td>
                    <td class="px-6 py-4 text-[#5f6873]">{{ $student->enrolled_date?->format('Y-m-d') ?? '—' }}</td>
                    <td class="px-6 py-4 text-center">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $student->is_active ? 'bg-[#e6f4c8] text-[#4f7a0f]' : 'bg-[#f1e7cc] text-[#5f6873]' }}">
                            {{ $student->is_active ? __('teacher.active') : __('teacher.inactive') }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-center gap-2">
                            <form method="POST" action="{{ route('teacher.accounts.toggle', $student) }}"
                                data-confirm="{{ __('teacher.archive_student_confirm') }}" data-confirm-title="{{ __('teacher.archive_title') }}" data-confirm-yes="{{ __('teacher.archive') }}" data-confirm-no="{{ __('teacher.cancel') }}">
                                @csrf @method('PUT')
                                <button type="submit" title="{{ __('teacher.archive') }}"
                                    class="w-9 h-9 rounded-lg border border-[#f1e7cc] hover:bg-[#fffbf0] transition text-[#1982c4]">
                                    <img src="{{ asset('assets/icons/file-tray-stacked.svg') }}" alt="Paused" class="inline-block h-5 w-5">
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="px-6 py-12 text-center text-[#8b949d]">{{ __('teacher.no_students_found') }}</td></tr>
                @endforelse
                <tr id="studentNoMatch" hidden><td colspan="9" class="px-6 py-12 text-center text-[#8b949d]">{{ __('No students match these filters.') }}</td></tr>
            </tbody>
        </table>
    </div>
    <div data-pager="students" class="flex flex-wrap items-center justify-between gap-3 border-t border-[#f1e7cc] bg-white px-4 py-3 text-sm text-[#5f6873]" style="display:none"></div>
</div>
</div>

<div id="parentsView" class="hidden">
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-[#fffbf0]">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-[#182438]">{{ __('teacher.name') }}</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-[#182438]">{{ __('teacher.email') }}</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-[#182438]">{{ __('teacher.contact') }}</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-[#182438]">{{ __('teacher.children') }}</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold text-[#182438]">{{ __('teacher.status') }}</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold text-[#182438]">{{ __('teacher.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f1e7cc]">
                    @forelse($activeParents as $parent)
                    <tr class="hover:bg-[#fffbf0] transition" data-page-row>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-[#1982c4] rounded-full flex items-center justify-center text-white font-bold text-sm">
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
                            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $parent->is_active ? 'bg-[#e6f4c8] text-[#4f7a0f]' : 'bg-[#f1e7cc] text-[#5f6873]' }}">
                                {{ $parent->is_active ? __('teacher.active') : __('teacher.inactive') }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                <form method="POST" action="{{ route('teacher.accounts.parent.toggle', $parent) }}"
                                    data-confirm="{{ __('teacher.archive_parent_confirm') }}" data-confirm-title="{{ __('teacher.archive_title') }}" data-confirm-yes="{{ __('teacher.archive') }}" data-confirm-no="{{ __('teacher.cancel') }}">
                                    @csrf @method('PUT')
                                    <button type="submit" title="{{ __('teacher.archive') }}"
                                        class="w-9 h-9 rounded-lg border border-[#f1e7cc] hover:bg-[#fffbf0] transition text-[#1982c4]">
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
        <div data-pager="parents" class="flex flex-wrap items-center justify-between gap-3 border-t border-[#f1e7cc] bg-white px-4 py-3 text-sm text-[#5f6873]" style="display:none"></div>
    </div>
</div>

<div id="archivedView" class="hidden">
    <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
        <div class="px-6 py-4 bg-[#fffbf0] border-b border-[#f1e7cc]">
            <h3 class="text-sm font-bold text-[#182438]">{{ __('teacher.archived_students') }}</h3>
            <p class="text-xs text-[#5f6873] mt-0.5">{{ __('teacher.archived_students_desc') }}</p>
        </div>
        <div class="overflow-auto max-h-[26rem]">
            <table class="w-full">
                <thead class="bg-[#fffbf0] sticky top-0 z-10 shadow-[0_1px_0_#f1e7cc]">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-[#182438]">{{ __('teacher.name') }}</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-[#182438]">{{ __('teacher.class') }}</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-[#182438]">{{ __('Academic year') }}</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-[#182438]">{{ __('student.hand_label') }}</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-[#182438]">{{ __('teacher.parent') }}</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-[#182438]">{{ __('teacher.enrolled') }}</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold text-[#182438]">{{ __('teacher.status') }}</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold text-[#182438]">{{ __('teacher.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f1e7cc]">
                    @forelse($archivedStudents as $student)
                    <tr class="hover:bg-[#fffbf0] transition" data-page-row>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-[#c9ced6] rounded-full flex items-center justify-center text-white font-bold text-sm">
                                    {{ strtoupper(substr($student->first_name, 0, 1)) }}{{ strtoupper(substr($student->last_name, 0, 1)) }}
                                </div>
                                <span class="font-medium text-[#5f6873]">{{ $student->name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-[#5f6873]">{{ $student->class_name ?? '—' }}</td>
                        <td class="px-6 py-4 text-[#5f6873] whitespace-nowrap">{{ $student->academic_year ?? '—' }}</td>
                        <td class="px-6 py-4 text-[#5f6873]">{{ $student->handLabel() }}</td>
                        <td class="px-6 py-4 text-[#5f6873]">{{ $student->parents->isEmpty() ? '—' : $student->parents->pluck('full_name')->implode(', ') }}</td>
                        <td class="px-6 py-4 text-[#5f6873]">{{ $student->enrolled_date?->format('Y-m-d') ?? '—' }}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-[#f1e7cc] text-[#5f6873]">{{ __('teacher.archived_badge') }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center">
                                <form method="POST" action="{{ route('teacher.accounts.toggle', $student) }}">
                                    @csrf @method('PUT')
                                    <button type="submit" title="{{ __('teacher.restore') }}"
                                        class="w-9 h-9 rounded-lg border border-[#f1e7cc] hover:bg-[#fffbf0] transition text-[#6fa51c]">
                                        ▶
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="px-6 py-12 text-center text-[#8b949d]">{{ __('teacher.no_archived_students') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div data-pager="archivedStudents" class="flex flex-wrap items-center justify-between gap-3 border-t border-[#f1e7cc] bg-white px-4 py-3 text-sm text-[#5f6873]" style="display:none"></div>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-[#fffbf0] border-b border-[#f1e7cc]">
            <h3 class="text-sm font-bold text-[#182438]">{{ __('teacher.archived_parent_accounts') }}</h3>
            <p class="text-xs text-[#5f6873] mt-0.5">{{ __('teacher.archived_parent_accounts_desc') }}</p>
        </div>
        <div class="overflow-auto max-h-[26rem]">
            <table class="w-full">
                <thead class="bg-[#fffbf0] sticky top-0 z-10 shadow-[0_1px_0_#f1e7cc]">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-[#182438]">{{ __('teacher.name') }}</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-[#182438]">{{ __('teacher.email') }}</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-[#182438]">{{ __('teacher.children') }}</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold text-[#182438]">{{ __('teacher.status') }}</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold text-[#182438]">{{ __('teacher.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f1e7cc]">
                    @forelse($archivedParents as $parent)
                    <tr class="hover:bg-[#fffbf0] transition" data-page-row>
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
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-[#f1e7cc] text-[#5f6873]">{{ __('teacher.archived_badge') }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center">
                                <form method="POST" action="{{ route('teacher.accounts.parent.toggle', $parent) }}">
                                    @csrf @method('PUT')
                                    <button type="submit" title="{{ __('teacher.restore') }}"
                                        class="w-9 h-9 rounded-lg border border-[#f1e7cc] hover:bg-[#fffbf0] transition text-[#6fa51c]">
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
        <div data-pager="archivedParents" class="flex flex-wrap items-center justify-between gap-3 border-t border-[#f1e7cc] bg-white px-4 py-3 text-sm text-[#5f6873]" style="display:none"></div>
    </div>
</div>

@include('teacher.partials.account-requests-list')

@include('teacher.partials.account-request-modal')
@endsection

@push('scripts')
<script>
const VIEWS = ['students', 'parents', 'archived', 'requests'];

function currentView() {
    return VIEWS.find(v => !document.getElementById(v + 'View').classList.contains('hidden')) || 'students';
}

/*
 * Pagination. Each list keeps all of its rows in the page (so filtering and
 * searching stay instant) but only shows one page of the matching rows. A pager
 * is told which rows match, in display order, and flips `hidden` only on rows
 * whose visibility actually changes.
 */
const PAGE_SIZES = [10];

function createPager(name, rowEls) {
    const el = document.querySelector('[data-pager="' + name + '"]');
    let matches = rowEls, page = 1, perPage = PAGE_SIZES[0];
    const shown = new Map(rowEls.map(r => [r, true]));

    function pageButtons(total) {
        // 1 … 4 5 6 … 12 : the first, last and neighbours of the current page.
        const wanted = new Set([1, total, page - 1, page, page + 1].filter(n => n >= 1 && n <= total));
        const list = [];
        let last = 0;
        [...wanted].sort((x, y) => x - y).forEach(n => {
            if (n - last > 1) list.push('gap');
            list.push(n);
            last = n;
        });
        return list;
    }

    function render() {
        const total = Math.max(1, Math.ceil(matches.length / perPage));
        page = Math.min(Math.max(page, 1), total);
        const start = (page - 1) * perPage;
        const windowRows = new Set(matches.slice(start, start + perPage));

        for (const r of rowEls) {
            const want = windowRows.has(r);
            if (shown.get(r) !== want) { r.hidden = !want; shown.set(r, want); }
        }

        if (!el) return;
        // Nothing to page through: keep the bar out of the way.
        // (style, not the hidden attribute: the flex class would override that.)
        const needed = matches.length > PAGE_SIZES[0];
        el.style.display = needed ? '' : 'none';
        if (!needed) return;

        const btn = 'min-w-[2.25rem] rounded-lg border px-2.5 py-1.5 text-sm font-medium transition ';
        const idle = btn + 'border-[#f1e7cc] bg-white text-[#182438] hover:border-[#ff8555]';
        const active = btn + 'border-[#ff8555] bg-[#ff8555] text-white';
        const off = btn + 'border-[#f1e7cc] bg-[#fffbf0] text-[#c9c4b8] cursor-not-allowed';

        el.innerHTML = '';
        const info = document.createElement('div');
        info.className = 'flex items-center gap-3';
        const summary = document.createElement('span');
        summary.textContent = (start + 1) + '–' + Math.min(start + perPage, matches.length) + ' / ' + matches.length;
        const size = document.createElement('select');
        size.setAttribute('aria-label', 'Rows per page');
        size.className = 'rounded-lg border border-[#f1e7cc] bg-white px-2 py-1 text-sm text-[#182438]';
        PAGE_SIZES.forEach(n => {
            const o = document.createElement('option');
            o.value = n; o.textContent = n + ' / page'; o.selected = n === perPage;
            size.appendChild(o);
        });
        size.addEventListener('change', () => { perPage = +size.value; page = 1; render(); });
        info.append(summary, size);

        const nav = document.createElement('nav');
        nav.setAttribute('aria-label', 'Pagination');
        nav.className = 'flex flex-wrap items-center gap-1';
        const add = (label, target, cls, current) => {
            const b = document.createElement('button');
            b.type = 'button';
            b.textContent = label;
            b.className = cls;
            if (current) b.setAttribute('aria-current', 'page');
            if (target === null) b.disabled = true;
            else b.addEventListener('click', () => { page = target; render(); el.scrollIntoView({ block: 'nearest' }); });
            nav.appendChild(b);
        };
        add('‹', page > 1 ? page - 1 : null, page > 1 ? idle : off);
        pageButtons(total).forEach(n => {
            if (n === 'gap') {
                const gap = document.createElement('span');
                gap.textContent = '…'; gap.className = 'px-1 text-[#8b949d]';
                nav.appendChild(gap);
            } else {
                add(String(n), n, n === page ? active : idle, n === page);
            }
        });
        add('›', page < total ? page + 1 : null, page < total ? idle : off);

        el.append(info, nav);
    }

    return {
        // Show these rows (in this order), starting from the first page.
        show(list) { matches = list; page = 1; render(); },
        render,
        all: rowEls,
    };
}

/* A simple list: search matches against the text of each row, read once. */
function createSearchList(name, bodySelector) {
    const body = document.querySelector(bodySelector);
    const rows = body ? Array.from(body.querySelectorAll('tr[data-page-row]')) : [];
    const text = new Map(rows.map(r => [r, r.textContent.toLowerCase()]));
    const pager = createPager(name, rows);
    pager.render();
    return {
        search(q) { pager.show(q ? rows.filter(r => text.get(r).includes(q)) : rows); },
        reset() { pager.show(rows); },
    };
}

const parentList = createSearchList('parents', '#parentsView tbody');
const archivedStudentList = createSearchList('archivedStudents', '#archivedView > div:nth-of-type(1) tbody');
const archivedParentList = createSearchList('archivedParents', '#archivedView > div:nth-of-type(2) tbody');

/*
 * Student filters. The rows are indexed once (their data-* attributes are read a
 * single time); every change recomputes the matching rows and hands them to the
 * pager. Typing is debounced, and sorting re-appends the rows in one fragment
 * instead of touching the table row by row.
 */
const studentFilters = (() => {
    const body = document.querySelector('#studentsView tbody');
    const bar = document.getElementById('studentFilters');
    const rows = body ? Array.from(body.querySelectorAll('tr[data-student-row]')).map(el => ({
        el,
        pos: +el.dataset.pos, name: el.dataset.name, search: el.dataset.search,
        section: el.dataset.section, year: el.dataset.year, age: +el.dataset.age, hand: el.dataset.hand,
        parents: el.dataset.parents === '1', enrolled: el.dataset.enrolled,
    })) : [];
    const pager = createPager('students', rows.map(r => r.el));
    const search = document.getElementById('studentSearch');
    const count = document.getElementById('studentFilterCount');
    const resetBtn = document.getElementById('studentFilterReset');
    const noMatch = document.getElementById('studentNoMatch');
    const fields = {};
    if (bar) bar.querySelectorAll('[data-sf]').forEach(f => fields[f.dataset.sf] = f);

    const sorters = {
        newest: (a, b) => a.pos - b.pos,
        name: (a, b) => a.name.localeCompare(b.name),
        age_asc: (a, b) => a.age - b.age || a.name.localeCompare(b.name),
        age_desc: (a, b) => b.age - a.age || a.name.localeCompare(b.name),
        enrolled: (a, b) => (b.enrolled || '').localeCompare(a.enrolled || '') || a.pos - b.pos,
    };
    let ordered = rows.slice();
    let appliedSort = 'newest';
    let timer = null;

    const value = key => fields[key] ? fields[key].value : '';
    const isFiltered = () => (search && search.value.trim() !== '') || ['section', 'year', 'age', 'hand', 'parent'].some(k => value(k) !== '');

    function apply() {
        if (!rows.length) return;
        const q = (search ? search.value : '').toLowerCase().trim();
        const section = value('section'), year = value('year'), age = value('age'), hand = value('hand'), parent = value('parent');

        const sort = value('sort') || 'newest';
        if (sort !== appliedSort) {
            ordered = rows.slice().sort(sorters[sort] || sorters.newest);
            const frag = document.createDocumentFragment();
            ordered.forEach(r => frag.appendChild(r.el));
            body.insertBefore(frag, noMatch || null);
            appliedSort = sort;
        }

        const matched = ordered.filter(r => (!q || r.search.includes(q))
            && (!section || (section === '__none' ? r.section === '' : r.section === section))
            && (!year || (year === '__none' ? r.year === '' : r.year === year))
            && (!age || r.age === +age)
            && (!hand || r.hand === hand)
            && (!parent || (parent === 'linked') === r.parents));

        pager.show(matched.map(r => r.el));
        if (noMatch) noMatch.hidden = matched.length !== 0;
        if (count) count.textContent = isFiltered() ? matched.length + ' / ' + rows.length : '';
        if (resetBtn) resetBtn.classList.toggle('hidden', !isFiltered() && sort === 'newest');
    }

    function reset() {
        Object.values(fields).forEach(f => f.value = f.dataset.sf === 'sort' ? 'newest' : '');
        if (search) search.value = '';
        apply();
    }

    Object.values(fields).forEach(f => f.addEventListener('change', apply));
    if (resetBtn) resetBtn.addEventListener('click', reset);
    if (search) {
        search.addEventListener('input', () => {
            if (currentView() !== 'students') return;
            clearTimeout(timer);
            timer = setTimeout(apply, 120);
        });
    }
    apply();
    return { apply, reset };
})();

function filterActiveTable() {
    const view = currentView();
    if (view === 'students') return; // handled by studentFilters (debounced)
    const input = document.getElementById('studentSearch');
    const search = (input ? input.value : '').toLowerCase().trim();

    if (view === 'parents') return parentList.search(search);
    if (view === 'archived') {
        archivedStudentList.search(search);
        archivedParentList.search(search);
        return;
    }

    document.querySelectorAll('#' + view + 'View tbody tr').forEach(row => {
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
    // Only the tabs that are not paged still hide rows themselves.
    document.querySelectorAll('#requestsView tbody tr').forEach(row => row.style.display = '');
    studentFilters.reset();
    parentList.reset();
    archivedStudentList.reset();
    archivedParentList.reset();
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
</script>
@endpush
