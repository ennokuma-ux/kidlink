@extends('layouts.teacher')

@section('title', __('teacher.activities_title'))
@section('page-title', __('teacher.activities_title'))

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <div class="flex items-center gap-2">
        <a href="{{ route('teacher.activities.index', ['week' => $prevWeek]) }}"
           class="px-3 py-2 border border-[#f1e7cc] rounded-lg text-[#182438] hover:bg-[#fffbf0] transition text-sm">&larr; {{ __('teacher.prev') }}</a>

        <div class="px-4 py-2 bg-white rounded-lg border border-[#f1e7cc] shadow-sm text-center">
            <span class="font-semibold text-[#182438]">{{ $weekStart->format('M j') }} &ndash; {{ $weekEnd->format('M j, Y') }}</span>
            @unless ($isCurrentWeek)
                <span class="ml-2 px-2 py-0.5 rounded text-xs font-medium bg-[#ffca3a] text-[#182438]">{{ __('teacher.planning_ahead') }}</span>
            @endunless
        </div>

        <a href="{{ route('teacher.activities.index', ['week' => $nextWeek]) }}"
           class="px-3 py-2 border border-[#f1e7cc] rounded-lg text-[#182438] hover:bg-[#fffbf0] transition text-sm">{{ __('teacher.next') }} &rarr;</a>

        @unless ($isCurrentWeek)
            <a href="{{ route('teacher.activities.index') }}"
               class="px-3 py-2 text-sm text-[#ff8555] hover:text-[#f47548] font-medium">{{ __('teacher.this_week') }}</a>
        @endunless
    </div>

    <div class="flex gap-3">
        <button onclick="document.getElementById('addActivityModal').classList.remove('hidden')" class="bg-[#ff8555] text-white px-6 py-2.5 rounded-lg hover:bg-[#f47548] transition font-medium">
            {{ __('teacher.add_activity_plus') }}
        </button>
        <select aria-label="{{ __('teacher.type') }}" id="typeFilter" onchange="filterActivities()" class="px-4 py-2.5 border border-[#f1e7cc] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
            <option value="all">{{ __('teacher.all_types') }}</option>
            <option value="Lesson">{{ __('teacher.type_lesson') }}</option>
            <option value="Craft">{{ __('teacher.type_craft') }}</option>
            <option value="Music">{{ __('teacher.type_music') }}</option>
            <option value="Story">{{ __('teacher.type_story') }}</option>
            <option value="Movement">{{ __('teacher.type_movement') }}</option>
        </select>
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-4 text-center border-l-4 border-[#ff8555]">
        <p class="text-2xl font-bold text-[#182438]">{{ $stats['total'] }}</p>
        <p class="text-sm text-[#5f6873]">{{ __('teacher.activities_this_week') }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 text-center border-l-4 border-[#8ac926]">
        <p class="text-2xl font-bold text-[#8ac926]">{{ $stats['completed'] }}</p>
        <p class="text-sm text-[#5f6873]">{{ __('teacher.completed') }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 text-center border-l-4 border-[#ffca3a]">
        <p class="text-2xl font-bold text-[#ffca3a]">{{ $stats['pending'] }}</p>
        <p class="text-sm text-[#5f6873]">{{ __('teacher.pending') }}</p>
    </div>
</div>

@if ($stats['total'] === 0)
    <div class="bg-white rounded-xl shadow-sm p-8 mb-6 text-center">
        <p class="text-[#5f6873]">{{ __('teacher.no_activities_this_week') }}</p>
        <p class="text-sm text-[#6b7280] mt-1">{{ __('teacher.use_add_activity') }}</p>
    </div>
@endif

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
    @foreach($days as $offset => $day)
        @php
            $date = $weekStart->copy()->addDays($offset);
        @endphp
        <div class="bg-white rounded-xl shadow-sm overflow-hidden {{ $date->isToday() ? 'ring-2 ring-[#ff8555]' : '' }}">
            <div class="text-center py-3 font-semibold {{ $date->isToday() ? 'bg-[#ff8555] text-white' : 'bg-[#f1e7cc] text-[#182438]' }}">
                {{ $date->format('D') }}
                <span class="block text-xs font-normal opacity-80">{{ $date->format('M j') }}</span>
            </div>
            <div class="p-3 space-y-2 min-h-[200px]">
                @forelse(($activities[$day] ?? collect()) as $activity)
                <div class="p-3 rounded-lg border text-sm activity-card {{ $activity->is_completed ? 'opacity-60' : '' }}"
                     data-type="{{ $activity->type }}"
                     data-id="{{ $activity->id }}"
                     data-title="{{ $activity->title }}"
                     data-day="{{ $activity->day_of_week }}"
                     data-start="{{ substr($activity->start_time, 0, 5) }}"
                     data-end="{{ substr($activity->end_time, 0, 5) }}"
                     data-group="{{ $activity->group_name }}"
                     data-mode="{{ $activity->target_mode ?? 'section' }}"
                     data-students='@json($activity->target_student_ids ?? [])'
                     data-update-url="{{ route('teacher.activities.update', $activity) }}">
                    <div class="flex items-center justify-between mb-1">
                        <span class="font-semibold text-[#182438]">{{ $activity->title }}</span>
                        <span class="px-2 py-0.5 rounded text-xs font-medium
                            {{ $activity->type === 'Lesson' ? 'bg-[#d3e8f6] text-[#0f5483]' : '' }}
                            {{ $activity->type === 'Craft' ? 'bg-orange-100 text-orange-700' : '' }}
                            {{ $activity->type === 'Music' ? 'bg-[#ffe2e3] text-[#c9343a]' : '' }}
                            {{ $activity->type === 'Story' ? 'bg-[#e6f4c8] text-[#4f7a0f]' : '' }}
                            {{ $activity->type === 'Movement' ? 'bg-[#fff0bf] text-[#8a6500]' : '' }}">
                            {{ $activity->type }}
                        </span>
                    </div>
                    <p class="text-[#5f6873] text-xs">{{ substr($activity->start_time, 0, 5) }} - {{ substr($activity->end_time, 0, 5) }}</p>
                    @if(($activity->target_mode ?? 'section') === 'individual')
                        @php
                            $count = count($activity->target_student_ids ?? []);
                        @endphp
                        <p class="text-[#6b7280] text-xs mt-1">{{ __('Students') }}: {{ $count }}</p>
                    @elseif($activity->group_name)
                        <p class="text-[#6b7280] text-xs mt-1">{{ __('teacher.group_label') }} {{ $activity->group_name }}</p>
                    @endif
                    <div class="flex items-center justify-between mt-2 pt-2 border-t">
                        <form method="POST" action="{{ route('teacher.activities.toggle', $activity) }}" class="inline">
                            @csrf @method('PUT')
                            <button type="submit" class="text-xs {{ $activity->is_completed ? 'text-[#8ac926]' : 'text-[#6b7280]' }}">
                                {{ $activity->is_completed ? __('teacher.completed') : __('teacher.mark_done') }}
                            </button>
                        </form>
                        <div class="flex items-center gap-3">
                            <a href="{{ route('teacher.activities.participation', $activity) }}" class="text-xs text-[#1982c4] hover:underline">{{ __('teacher.record') }}</a>
                            <button type="button" onclick="openEditModal(this)" class="text-xs text-[#6b7280] hover:text-[#182438]">{{ __('teacher.edit') }}</button>
                            <form method="POST" action="{{ route('teacher.activities.destroy', $activity) }}" class="inline" data-confirm="{{ __('teacher.delete_activity_confirm') }}" data-confirm-title="{{ __('teacher.delete_activity_title') }}" data-confirm-yes="{{ __('teacher.delete') }}" data-confirm-no="{{ __('teacher.cancel') }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs text-[#ff595e]">{{ __('teacher.delete') }}</button>
                            </form>
                        </div>
                    </div>
                </div>
                @empty
                <p class="text-[#6b7280] text-xs text-center py-4">{{ __('teacher.no_activities') }}</p>
                @endforelse
            </div>
        </div>
    @endforeach
</div>

{{-- Interactive calendar: click any date to jump to its week --}}
<div class="mt-8 bg-white rounded-xl shadow-sm p-6 w-full"
     x-data="activityCalendar(@js($calendarDays), @js($weekStart->toDateString()), @js(route('teacher.activities.index')), @js([
        'today' => __('teacher.calendar_today'), 'go' => __('teacher.calendar_go'), 'none' => __('teacher.calendar_none'),
        'one' => __('teacher.calendar_activity'), 'many' => __('teacher.calendar_activities'), 'done' => __('teacher.calendar_done'),
     ]))">
    <div class="flex items-center justify-between mb-4">
        <button type="button" @click="shift(-1)" class="w-9 h-9 rounded-lg border border-[#f1e7cc] hover:bg-[#fffbf0] text-[#182438]" aria-label="Previous month">&lsaquo;</button>
        <h3 class="font-semibold text-[#182438]" x-text="monthLabel"></h3>
        <button type="button" @click="shift(1)" class="w-9 h-9 rounded-lg border border-[#f1e7cc] hover:bg-[#fffbf0] text-[#182438]" aria-label="Next month">&rsaquo;</button>
    </div>
    <div class="grid grid-cols-7 text-center text-xs font-semibold uppercase text-[#6b7280] mb-1">
        <template x-for="d in ['Mon','Tue','Wed','Thu','Fri','Sat','Sun']" :key="d"><span x-text="d"></span></template>
    </div>
    <div class="grid grid-cols-7 gap-y-1">
        <template x-for="cell in cells" :key="cell.date">
            <button type="button" @click="go(cell.date)" @mouseenter="hover = cell.date" @mouseleave="hover = null" :title="describe(cell.date)"
                    class="relative h-12 md:h-16 text-sm transition hover:bg-[#ffe3d1] flex flex-col items-center justify-start pt-1.5"
                    :class="{ 'text-[#c9ced6]': !cell.inMonth, 'text-[#182438]': cell.inMonth, 'bg-[#fff0e6]': cell.inWeek, 'rounded-l-lg': cell.weekStart, 'rounded-r-lg': cell.weekEnd }">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full" :class="cell.today ? 'ring-2 ring-[#ff8555] font-bold' : ''" x-text="cell.day"></span>
                <span x-show="days[cell.date]" class="mt-0.5 flex items-center gap-1">
                    <span class="block w-2 h-2 rounded-full" :class="days[cell.date] && days[cell.date].done === days[cell.date].total ? 'bg-[#8ac926]' : 'bg-[#ff8555]'"></span>
                    <span class="hidden md:inline text-xs text-[#5f6873]" x-text="days[cell.date] ? days[cell.date].total + ' ' + (days[cell.date].total === 1 ? labels.one : labels.many) : ''"></span>
                </span>
            </button>
        </template>
    </div>
    <div class="mt-4 pt-3 border-t border-[#f1e7cc] flex items-center justify-between text-xs text-[#5f6873] min-h-[2rem]">
        <span x-text="hover ? describe(hover) : ''"></span>
        <button type="button" @click="go(todayStr)" class="font-medium text-[#ff8555] hover:text-[#f47548]" x-text="labels.today"></button>
    </div>
</div>

<div id="addActivityModal" class="fixed inset-0 bg-black/50 flex items-start sm:items-center justify-center z-50 hidden p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl p-5 sm:p-8 w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-[#182438]">{{ __('teacher.new_activity') }}</h3>
            <button onclick="this.closest('#addActivityModal').classList.add('hidden')" class="text-[#6b7280] hover:text-[#5f6873] text-2xl">&times;</button>
        </div>

        @if ($errors->any())
            <div class="mb-4 p-3 rounded-lg bg-[#fff1f1] border border-[#ffc9cb] text-sm text-[#c9343a]">
                <ul class="list-disc pl-4 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('teacher.activities.store') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="week" value="{{ $weekStart->toDateString() }}">
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2">{{ __('teacher.title') }}</label>
                <input aria-label="{{ __('teacher.title') }}" type="text" name="title" required maxlength="255" value="{{ old('title') }}" class="w-full px-4 py-3 border border-[#f1e7cc] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2">{{ __('teacher.type') }}</label>
                <select aria-label="{{ __('teacher.type') }}" name="type" required class="w-full px-4 py-3 border border-[#f1e7cc] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                    <option value="Lesson">{{ __('teacher.type_lesson') }}</option>
                    <option value="Craft">{{ __('teacher.type_craft') }}</option>
                    <option value="Music">{{ __('teacher.type_music') }}</option>
                    <option value="Story">{{ __('teacher.type_story') }}</option>
                    <option value="Movement">{{ __('teacher.type_movement') }}</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2">{{ __('teacher.day') }}</label>
                <select aria-label="{{ __('teacher.day') }}" name="day_of_week" required class="w-full px-4 py-3 border border-[#f1e7cc] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                    @foreach($days as $offset => $day)
                        <option value="{{ $day }}">{{ $weekStart->copy()->addDays($offset)->format('D') }}, {{ $weekStart->copy()->addDays($offset)->format('M j') }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-[#182438] mb-2">{{ __('teacher.start_time') }}</label>
                    <input aria-label="{{ __('teacher.start_time') }}" type="time" name="start_time" required value="{{ old('start_time') }}" class="w-full px-4 py-3 border border-[#f1e7cc] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#182438] mb-2">{{ __('teacher.end_time') }}</label>
                    <input aria-label="{{ __('teacher.end_time') }}" type="time" name="end_time" required value="{{ old('end_time') }}" class="w-full px-4 py-3 border border-[#f1e7cc] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                </div>
            </div>
            @include('teacher.partials.activity-target', ['prefix' => 'add'])
            <p class="text-xs text-[#6b7280]">
                {{ __('teacher.scheduled_for_week', ['date' => $weekStart->format('M j, Y')]) }}
            </p>
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="this.closest('#addActivityModal').classList.add('hidden')" class="px-6 py-2.5 border border-[#f1e7cc] rounded-lg text-[#182438] hover:bg-[#fffbf0] transition">{{ __('teacher.cancel') }}</button>
                <button type="submit" class="px-6 py-2.5 bg-[#ff8555] text-white rounded-lg hover:bg-[#f47548] transition font-medium">{{ __('teacher.add_activity') }}</button>
            </div>
        </form>
    </div>
</div>
<div id="editActivityModal" class="fixed inset-0 bg-black/50 flex items-start sm:items-center justify-center z-50 hidden p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl p-5 sm:p-8 w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-[#182438]">{{ __('teacher.edit_activity') }}</h3>
            <button onclick="this.closest('#editActivityModal').classList.add('hidden')" class="text-[#6b7280] hover:text-[#5f6873] text-2xl">&times;</button>
        </div>

        @if ($errors->any())
            <div class="mb-4 p-3 rounded-lg bg-[#fff1f1] border border-[#ffc9cb] text-sm text-[#c9343a]">
                <ul class="list-disc pl-4 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" id="editActivityForm" class="space-y-4">
            @csrf @method('PUT')
            <input type="hidden" name="_activity_id" id="editActivityId">
            <input type="hidden" name="week" id="editWeek" value="{{ $weekStart->toDateString() }}">
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2">{{ __('teacher.title') }}</label>
                <input aria-label="{{ __('teacher.title') }}" type="text" name="title" id="editTitle" required maxlength="255" class="w-full px-4 py-3 border border-[#f1e7cc] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2">{{ __('teacher.type') }}</label>
                <select aria-label="{{ __('teacher.type') }}" name="type" id="editType" required class="w-full px-4 py-3 border border-[#f1e7cc] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                    <option value="Lesson">{{ __('teacher.type_lesson') }}</option>
                    <option value="Craft">{{ __('teacher.type_craft') }}</option>
                    <option value="Music">{{ __('teacher.type_music') }}</option>
                    <option value="Story">{{ __('teacher.type_story') }}</option>
                    <option value="Movement">{{ __('teacher.type_movement') }}</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2">{{ __('teacher.day') }}</label>
                <select aria-label="{{ __('teacher.day') }}" name="day_of_week" id="editDay" required class="w-full px-4 py-3 border border-[#f1e7cc] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                    @foreach($days as $day)
                        <option value="{{ $day }}">{{ $day }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-[#182438] mb-2">{{ __('teacher.start_time') }}</label>
                    <input aria-label="{{ __('teacher.start_time') }}" type="time" name="start_time" id="editStart" required class="w-full px-4 py-3 border border-[#f1e7cc] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#182438] mb-2">{{ __('teacher.end_time') }}</label>
                    <input aria-label="{{ __('teacher.end_time') }}" type="time" name="end_time" id="editEnd" required class="w-full px-4 py-3 border border-[#f1e7cc] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                </div>
            </div>
            @include('teacher.partials.activity-target', ['prefix' => 'edit'])
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="this.closest('#editActivityModal').classList.add('hidden')" class="px-6 py-2.5 border border-[#f1e7cc] rounded-lg text-[#182438] hover:bg-[#fffbf0] transition">{{ __('teacher.cancel') }}</button>
                <button type="submit" class="px-6 py-2.5 bg-[#ff8555] text-white rounded-lg hover:bg-[#f47548] transition font-medium">{{ __('teacher.save_changes') }}</button>
            </div>
        </form>
    </div>
</div>
@include('teacher.partials.activity-student-picker')
@endsection

@push('scripts')
<script>
window.activityCalendar = function (days, weekStart, baseUrl, labels) {
    const iso = d => d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
    const parse = s => { const [y, m, d] = s.split('-').map(Number); return new Date(y, m - 1, d); };
    const start = parse(weekStart);
    return {
        open: false, hover: null, days: days, labels: labels,
        view: new Date(start.getFullYear(), start.getMonth(), 1),
        todayStr: iso(new Date()),
        get monthLabel() { return this.view.toLocaleDateString(undefined, { month: 'long', year: 'numeric' }); },
        get cells() {
            const first = new Date(this.view.getFullYear(), this.view.getMonth(), 1);
            const offset = (first.getDay() + 6) % 7;            // Monday first
            const gridStart = new Date(first.getFullYear(), first.getMonth(), 1 - offset);
            const end = new Date(start.getFullYear(), start.getMonth(), start.getDate() + 6);
            const out = [];
            for (let i = 0; i < 42; i++) {
                const d = new Date(gridStart.getFullYear(), gridStart.getMonth(), gridStart.getDate() + i);
                out.push({
                    date: iso(d), day: d.getDate(), inMonth: d.getMonth() === this.view.getMonth(),
                    today: iso(d) === this.todayStr, inWeek: d >= start && d <= end,
                    weekStart: iso(d) === iso(start), weekEnd: iso(d) === iso(end),
                });
            }
            return out;
        },
        shift(n) { this.view = new Date(this.view.getFullYear(), this.view.getMonth() + n, 1); },
        describe(date) {
            const e = this.days[date];
            const label = parse(date).toLocaleDateString(undefined, { weekday: 'short', month: 'short', day: 'numeric' });
            if (!e) return label + ' · ' + this.labels.none;
            return label + ' · ' + e.total + ' ' + (e.total === 1 ? this.labels.one : this.labels.many) + ' (' + e.done + ' ' + this.labels.done + ')';
        },
        go(date) { window.location.href = baseUrl + '?week=' + date; },
    };
};

// ---- Who the activity is for: a group, or individual students ---------------
window.activityTarget = (function () {
    @php
        // Built here instead of inline: the JSON directive splits its argument on every comma and
        // drops everything after the third piece, which turned this list into a ParseError.
        $pickerStudents = $students->map(fn ($st) => [
            'id' => $st->id,
            'name' => $st->name,
            'section' => $st->class_name ?: '',
            'year' => $st->academic_year ?: '',
            'age' => (int) $st->age,
        ])->values();
    @endphp
    const STUDENTS = @json($pickerStudents);
    const AGE_LABEL = age => age < 1 ? @json(__('Below 1')) : age + ' ' + @json(__('teacher.yrs'));
    const byId = new Map(STUDENTS.map(st => [st.id, st]));

    const modal = document.getElementById('studentPickerModal');
    const $ = id => document.getElementById(id);
    const list = $('pickerList'), search = $('pickerSearch');
    const fSection = $('pickerSection'), fYear = $('pickerYear'), fAge = $('pickerAge');
    let activeRoot = null;
    let working = new Set();

    const ON = ['bg-[#ff8555]', 'text-white', 'shadow-sm'];
    const OFF = ['text-[#5f6873]', 'hover:bg-white'];

    function idsOf(root) { return Array.from(root.querySelectorAll('[data-target-ids] input')).map(i => +i.value); }

    function renderRoot(root) {
        const mode = root.querySelector('[data-target-mode]').value;
        root.querySelectorAll('[data-mode-button]').forEach(btn => {
            const on = btn.dataset.modeButton === mode;
            ON.forEach(c => btn.classList.toggle(c, on));
            OFF.forEach(c => btn.classList.toggle(c, !on));
            btn.setAttribute('aria-pressed', on ? 'true' : 'false');
        });
        root.querySelector('[data-panel="section"]').hidden = mode !== 'section';
        root.querySelector('[data-panel="individual"]').hidden = mode !== 'individual';

        const ids = idsOf(root);
        root.querySelector('[data-target-count]').textContent = ids.length;
        const chips = root.querySelector('[data-target-chips]');
        chips.innerHTML = '';
        ids.forEach(id => {
            const st = byId.get(id); if (!st) return;
            const chip = document.createElement('span');
            chip.className = 'inline-flex items-center gap-1 pl-3 pr-1 py-1 rounded-full bg-[#fff0e6] text-[#9a3f12] text-xs font-semibold';
            chip.append(st.name);
            const x = document.createElement('button');
            x.type = 'button'; x.textContent = '×'; x.setAttribute('aria-label', @json(__('Remove')) + ' ' + st.name);
            x.className = 'w-5 h-5 rounded-full hover:bg-[#ffd9c2] leading-none';
            x.addEventListener('click', () => { writeIds(root, idsOf(root).filter(i => i !== id)); });
            chip.append(x); chips.append(chip);
        });
    }

    function writeIds(root, ids) {
        const box = root.querySelector('[data-target-ids]');
        box.innerHTML = '';
        ids.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden'; input.name = 'target_student_ids[]'; input.value = id;
            box.append(input);
        });
        root.querySelector('[data-target-error]').hidden = true;
        renderRoot(root);
    }

    function setMode(root, mode) {
        root.querySelector('[data-target-mode]').value = mode;
        renderRoot(root);
    }

    function set(root, mode, group, ids) {
        if (!root) return;
        const select = root.querySelector('[data-target-group]');
        if (group && !Array.from(select.options).some(o => o.value === group)) {
            select.add(new Option(group, group));       // an older free-text group still shows
        }
        select.value = group || '';
        writeIds(root, (ids || []).map(Number).filter(id => byId.has(id)));
        setMode(root, mode === 'individual' ? 'individual' : 'section');
    }

    // ---- the picker pop-up ----------------------------------------------------
    function fillFilters() {
        const sections = [...new Set(STUDENTS.map(s => s.section).filter(Boolean))].sort();
        const years = [...new Set(STUDENTS.map(s => s.year).filter(Boolean))].sort().reverse();
        const ages = [...new Set([0, 1, 2, 3, 4, 5, ...STUDENTS.map(s => s.age)])].sort((a, b) => a - b);
        sections.forEach(v => fSection.add(new Option(v, v)));
        years.forEach(v => fYear.add(new Option(v, v)));
        ages.forEach(v => fAge.add(new Option(AGE_LABEL(v), String(v))));
    }

    function visible() {
        const q = search.value.trim().toLowerCase();
        return STUDENTS.filter(s =>
            (!q || s.name.toLowerCase().includes(q))
            && (!fSection.value || s.section === fSection.value)
            && (!fYear.value || s.year === fYear.value)
            && (fAge.value === '' || s.age === +fAge.value));
    }

    function renderList() {
        const rows = visible();
        list.innerHTML = '';
        rows.forEach(s => {
            const li = document.createElement('li');
            const label = document.createElement('label');
            label.className = 'flex items-center gap-3 px-3 py-2.5 rounded-lg cursor-pointer hover:bg-[#fffbf0]';
            const box = document.createElement('input');
            box.type = 'checkbox'; box.value = s.id; box.checked = working.has(s.id);
            box.className = 'w-4 h-4 accent-[#ff8555]';
            box.addEventListener('change', () => { box.checked ? working.add(s.id) : working.delete(s.id); counts(); });
            const text = document.createElement('span');
            text.className = 'min-w-0 flex-1';
            const name = document.createElement('span');
            name.className = 'block text-sm font-semibold text-[#182438] truncate'; name.textContent = s.name;
            const meta = document.createElement('span');
            meta.className = 'block text-xs text-[#6b7280] truncate';
            meta.textContent = [s.section, s.year, AGE_LABEL(s.age)].filter(Boolean).join(' · ');
            text.append(name, meta); label.append(box, text); li.append(label); list.append(li);
        });
        $('pickerEmpty').hidden = rows.length > 0;
        $('pickerShownCount').textContent = rows.length;
        counts();
    }

    function counts() { $('pickerSelectedCount').textContent = working.size; }

    function openPicker(root) {
        activeRoot = root;
        working = new Set(idsOf(root));
        search.value = ''; fSection.value = ''; fYear.value = ''; fAge.value = '';
        renderList();
        modal.classList.remove('hidden');
        setTimeout(() => search.focus(), 0);
    }

    function closePicker() { modal.classList.add('hidden'); activeRoot = null; }

    document.addEventListener('click', event => {
        const modeBtn = event.target.closest('[data-mode-button]');
        if (modeBtn) { setMode(modeBtn.closest('[data-activity-target]'), modeBtn.dataset.modeButton); return; }
        const open = event.target.closest('[data-open-picker]');
        if (open) openPicker(open.closest('[data-activity-target]'));
    });

    [search, fSection, fYear, fAge].forEach(el => el.addEventListener('input', renderList));
    $('pickerSelectShown').addEventListener('click', () => { visible().forEach(s => working.add(s.id)); renderList(); });
    $('pickerClear').addEventListener('click', () => { working.clear(); renderList(); });
    $('pickerDone').addEventListener('click', () => {
        if (activeRoot) writeIds(activeRoot, STUDENTS.filter(s => working.has(s.id)).map(s => s.id));
        closePicker();
    });
    modal.querySelectorAll('[data-picker-close]').forEach(b => b.addEventListener('click', closePicker));
    modal.addEventListener('click', e => { if (e.target === modal) closePicker(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && !modal.classList.contains('hidden')) closePicker(); });

    // A form set to "individual" needs at least one student before it is sent.
    document.addEventListener('submit', event => {
        const root = event.target.querySelector && event.target.querySelector('[data-activity-target]');
        if (!root) return;
        if (root.querySelector('[data-target-mode]').value === 'individual' && idsOf(root).length === 0) {
            event.preventDefault();
            root.querySelector('[data-target-error]').hidden = false;
        }
    });

    fillFilters();
    document.querySelectorAll('[data-activity-target]').forEach(renderRoot);
    return { set, setMode };
})();

function filterActivities() {
    const type = document.getElementById('typeFilter').value;
    document.querySelectorAll('.activity-card').forEach(card => {
        card.style.display = (type === 'all' || card.dataset.type === type) ? '' : 'none';
    });
}

function openEditModal(button) {
    const card = button.closest('.activity-card');
    document.getElementById('editActivityForm').action = card.dataset.updateUrl;
    document.getElementById('editActivityId').value = card.dataset.id;
    document.getElementById('editTitle').value = card.dataset.title || '';
    document.getElementById('editType').value = card.dataset.type || 'Lesson';
    document.getElementById('editDay').value = card.dataset.day || 'Monday';
    document.getElementById('editStart').value = card.dataset.start || '';
    document.getElementById('editEnd').value = card.dataset.end || '';
    let ids = [];
    try { ids = JSON.parse(card.dataset.students || '[]'); } catch (e) {}
    activityTarget.set(document.querySelector('#editActivityModal [data-activity-target]'), card.dataset.mode || 'section', card.dataset.group || '', ids);
    document.getElementById('editActivityModal').classList.remove('hidden');
}

@if ($errors->any())
    @if (old('_activity_id'))
        // The edit form bounced: restore it instead of the create form.
        document.getElementById('editActivityForm').action = @json(route('teacher.activities.update', old('_activity_id')));
        document.getElementById('editActivityId').value = @json(old('_activity_id'));
        document.getElementById('editTitle').value = @json(old('title'));
        document.getElementById('editType').value = @json(old('type'));
        document.getElementById('editDay').value = @json(old('day_of_week'));
        document.getElementById('editStart').value = @json(old('start_time'));
        document.getElementById('editEnd').value = @json(old('end_time'));
        document.getElementById('editWeek').value = @json(old('week'));
        activityTarget.set(document.querySelector('#editActivityModal [data-activity-target]'), @json(old('target_mode', 'section')), @json(old('group_name', '')), @json(old('target_student_ids', [])));
        document.getElementById('editActivityModal').classList.remove('hidden');
    @else
        activityTarget.set(document.querySelector('#addActivityModal [data-activity-target]'), @json(old('target_mode', 'section')), @json(old('group_name', '')), @json(old('target_student_ids', [])));
        document.getElementById('addActivityModal').classList.remove('hidden');
    @endif
@endif
</script>
@endpush
