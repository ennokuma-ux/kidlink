@extends('layouts.teacher')

@section('title', __('teacher.attendance_title'))
@section('page-title', __('teacher.daily_attendance_title'))

@section('content')
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <form method="GET" action="{{ route('teacher.attendance.index') }}"
        class="flex flex-col lg:flex-row lg:items-end gap-4" data-ajax-filter>
        <div>
            <label class="block text-[#182438] font-medium mb-2">{{ __('teacher.select_date') }}</label>
            <input type="date" name="date" value="{{ $date->format('Y-m-d') }}" 
                class="w-full px-4 py-2.5 border border-[#f1e7cc] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
        </div>
        <div class="flex-1 min-w-0">
            <label for="attendanceSearch" class="block text-[#182438] font-medium mb-2">{{ __('teacher.search_student') }}</label>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#6b7280]">&nbsp;&#9906;</span>
                <input type="search" name="search" id="attendanceSearch" value="{{ $search }}"
                    placeholder="{{ __('teacher.type_a_name') }}" autocomplete="off"
                    class="w-full pl-9 pr-4 py-2.5 border border-[#f1e7cc] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
            </div>
        </div>
        <div class="w-full lg:w-56">
            <label for="attendanceSection" class="block text-[#182438] font-medium mb-2">{{ __('teacher.section') }}</label>
            <select name="section" id="attendanceSection" 
                class="w-full px-4 py-2.5 border border-[#f1e7cc] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                <option value="">{{ __('teacher.all_sections') }}</option>
                @foreach($availableSections as $available)
                    <option value="{{ $available }}" {{ $section === $available ? 'selected' : '' }}>{{ $available }}</option>
                @endforeach
            </select>
        </div>
        <div class="w-full lg:w-44">
            <label for="attendanceYear" class="block text-[#182438] font-medium mb-2">{{ __('Academic year') }}</label>
            <select name="year" id="attendanceYear"
                class="w-full px-4 py-2.5 border border-[#f1e7cc] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                <option value="">{{ __('All academic years') }}</option>
                @foreach($availableYears as $availableYear)
                    <option value="{{ $availableYear }}" {{ $year === $availableYear ? 'selected' : '' }}>{{ $availableYear }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-3">
            <button type="submit" class="bg-[#ff8555] text-white px-6 py-2.5 rounded-lg hover:bg-[#f47548] transition font-medium">{{ __('teacher.apply') }}</button>
            @if($search !== '' || $section !== '' || $year !== '')
                <a href="{{ route('teacher.attendance.index', ['date' => $date->format('Y-m-d')]) }}"
                    class="px-6 py-2.5 border border-[#f1e7cc] rounded-lg text-[#5f6873] hover:bg-[#fffbf0] transition">{{ __('teacher.clear') }}</a>
            @endif
        </div>
    </form>

    @if($search !== '' || $section !== '' || $year !== '')
        <p class="text-sm text-[#5f6873] mt-4 pt-4 border-t border-[#f1e7cc]">
            {{ __('teacher.showing') }} <span class="font-semibold text-[#182438]">{{ $students->count() }}</span>
            {{ __('teacher.of') }} {{ $totalStudents }}
            {{ $totalStudents === 1 ? __('teacher.student_singular') : __('teacher.student_plural') }}@if($section) {{ __('teacher.in_section') }} <span class="font-semibold text-[#182438]">{{ $section }}</span>@endif
            @if($year) {{ __('in academic year') }} <span class="font-semibold text-[#182438]">{{ $year }}</span>@endif
            @if($search) {{ __('teacher.matching_query') }} &ldquo;<span class="font-semibold text-[#182438]">{{ $search }}</span>&rdquo;@endif.
            {{ __('teacher.only_shown_saved') }}
        </p>
    @endif
</div>

<div class="grid grid-cols-2 sm:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-4 text-center border-l-4 border-[#1982c4]">
<p class="text-2xl font-bold text-[#182438]">{{ $stats['total'] }}</p>
        <p class="text-sm text-[#5f6873]">{{ ($search !== '' || $section !== '' || $year !== '') ? __('teacher.matching_students') : __('teacher.total_students') }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 text-center border-l-4 border-[#8ac926]">
        <p class="text-2xl font-bold text-[#8ac926]">{{ $stats['present'] }}</p>
        <p class="text-sm text-[#5f6873]">{{ __('teacher.present') }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 text-center border-l-4 border-[#ffca3a]">
        <p class="text-2xl font-bold text-[#ffca3a]">{{ $stats['late'] }}</p>
        <p class="text-sm text-[#5f6873]">{{ __('teacher.late') }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 text-center border-l-4 border-[#ff595e]">
        <p class="text-2xl font-bold text-[#ff595e]">{{ $stats['absent'] }}</p>
        <p class="text-sm text-[#5f6873]">{{ __('teacher.absent') }}</p>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    @if($students->hasPages())
        <div class="px-6 py-4 border-b border-[#f1e7cc]">
            {{ $students->onEachSide(1)->links() }}
        </div>
    @endif
    <form method="POST" action="{{ route('teacher.attendance.store') }}">
        @csrf
<input type="hidden" name="date" value="{{ $date->format('Y-m-d') }}">
        <input type="hidden" name="search" value="{{ $search }}">
        <input type="hidden" name="section" value="{{ $section }}">
        <input type="hidden" name="year" value="{{ $year }}">
        @if($students->count() > 0)
        <div class="px-6 py-4 flex justify-end border-b border-[#f1e7cc]">
            <button type="submit" class="bg-[#ff8555] text-white px-8 py-3 rounded-lg hover:bg-[#f47548] transition font-medium">{{ __('teacher.save_attendance') }}</button>
        </div>
        @endif
        <table class="w-full">
            <thead class="bg-[#fffbf0]">
                <tr>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-[#5f6873]">{{ __('teacher.student') }}</th>
                    @if($availableSections->count() > 1)
                        <th class="px-6 py-4 text-left text-sm font-semibold text-[#5f6873]">{{ __('teacher.section') }}</th>
                    @endif
                    <th class="px-6 py-4 text-center text-sm font-semibold text-[#5f6873]">{{ __('teacher.present') }}</th>
                    <th class="px-6 py-4 text-center text-sm font-semibold text-[#5f6873]">{{ __('teacher.late') }}</th>
                    <th class="px-6 py-4 text-center text-sm font-semibold text-[#5f6873]">{{ __('teacher.absent') }}</th>
                    <th class="px-6 py-4 text-center text-sm font-semibold text-[#5f6873]">{{ __('teacher.excused') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#f1e7cc]">
                @forelse($students as $student)
                @php $record = $attendance->get($student->id); @endphp
<tr class="hover:bg-[#fffbf0] transition">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <span class="font-medium text-[#182438]">{{ $student->name }}</span>
                        </div>
                    </td>
                    @if($availableSections->count() > 1)
                        <td class="px-6 py-4">
                            @if($student->class_name)
                                <span class="px-2.5 py-1 bg-[#fdf3d6] rounded-full text-xs font-medium text-[#5f6873]">{{ $student->class_name }}</span>
                            @else
                                <span class="text-xs text-[#6b7280]">&mdash;</span>
                            @endif
                        </td>
                    @endif
                    @foreach(['Present','Late','Absent','Excused'] as $status)
                    <td class="px-6 py-4 text-center">
                        <label class="inline-flex items-center">
                            <input type="radio" name="attendance[{{ $student->id }}]" value="{{ $status }}"
                                {{ ($record && $record->status === $status) ? 'checked' : (!$record && $status === 'Present' ? 'checked' : '') }}
                                class="w-5 h-5 text-[#ff8555] focus:ring-[#ff8555]">
                            <span class="ml-1 text-sm {{ $record && $record->status === $status ? 'font-semibold' : '' }}"></span>
                        </label>
                    </td>
                    @endforeach
                </tr>
@empty
                <tr>
                    <td colspan="{{ $availableSections->count() > 1 ? 6 : 5 }}" class="px-6 py-12 text-center text-[#6b7280]">
                        @if($search !== '' || $section !== '' || $year !== '')
                            {{ __('teacher.no_students_match') }}
                        @else
                            {{ __('teacher.no_active_students') }}
                        @endif
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </form>
    @if($students->hasPages())
        <div class="px-6 py-4 border-t border-[#f1e7cc]">
            {{ $students->onEachSide(1)->links() }}
        </div>
    @endif
</div>
<x-attendance-calendar :days="$calendarDays" :date="$date->format('Y-m-d')" :base-url="route('teacher.attendance.index')" :search="$search" :section="$section" :year="$year" />
@endsection

@push('scripts')
<script>
(function () {
    // Delegated handlers survive the in-place table refreshes.
    let searchTimer = null;
    let dateTimer = null;

    document.addEventListener('input', function (event) {
        if (!event.target.matches('#attendanceSearch')) return;
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () {
            const form = event.target.closest('form[data-ajax-filter]');
            if (form) form.requestSubmit();
        }, 300);
    });

    document.addEventListener('change', function (event) {
        const target = event.target;
        if (target.matches('input[name="date"]')) {
            clearTimeout(dateTimer);
            dateTimer = setTimeout(function () {
                const form = target.closest('form[data-ajax-filter]');
                if (form) form.requestSubmit();
            }, 80);
        } else if (target.matches('#attendanceSection, #attendanceYear')) {
            const form = target.closest('form[data-ajax-filter]');
            if (form) form.requestSubmit();
        }

        if (target.matches('input[type="radio"][name^="attendance["]')) {
            const group = document.querySelectorAll('input[type="radio"][name="' + CSS.escape(target.name) + '"]');
            group.forEach(function (radio) {
                const label = radio.nextElementSibling;
                if (label) label.classList.toggle('font-semibold', radio.checked);
            });
        }
    });
})();
</script>
@endpush




