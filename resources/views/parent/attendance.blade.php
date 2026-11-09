@extends('layouts.parent')

@section('title', __('parent_ui.attendance_title'))
@section('page-title', __('parent_ui.attendance_title'))

@section('content')
@if(!$child)
<div class="bg-[#fff9e6] border-l-4 border-[#ffd04a] p-6 rounded-lg mb-6">
    <p class="text-[#8a6500]">{{ __('parent_ui.no_child_selected') }}</p>
</div>
@else
@include('components.child_selector', ['children' => $children, 'child' => $child])

<div class="mb-6">
    <h2 class="text-xl font-bold text-gray-800">{{ __('parent_ui.child_attendance', ['name' => $child->first_name]) }}</h2>
    <p class="text-gray-500 text-sm">{{ $month->translatedFormat('F Y') }}</p>
</div>

<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-3">
    <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-[#8ac926]">
        <p class="text-gray-500 text-xs font-semibold uppercase">{{ __('parent_ui.stat_present') }}</p>
        <p class="text-2xl font-bold text-[#6fa51c] mt-1">{{ $present }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-[#ffca3a]">
        <p class="text-gray-500 text-xs font-semibold uppercase">{{ __('parent_ui.stat_late') }}</p>
        <p class="text-2xl font-bold text-[#ffca3a] mt-1">{{ $late }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-[#ff595e]">
        <p class="text-gray-500 text-xs font-semibold uppercase">{{ __('parent_ui.stat_absent') }}</p>
        <p class="text-2xl font-bold text-[#ff595e] mt-1">{{ $absent }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-[#4a9fd2]">
        <p class="text-gray-500 text-xs font-semibold uppercase">{{ __('parent_ui.stat_excused') }}</p>
        <p class="text-2xl font-bold text-[#1982c4] mt-1">{{ $excused }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-[#1982c4] col-span-2 md:col-span-1">
        <p class="text-gray-500 text-xs font-semibold uppercase">{{ __('parent_ui.stat_rate') }}</p>
        <p class="text-2xl font-bold text-[#1982c4] mt-1">{{ $total > 0 ? $rate.'%' : '—' }}</p>
    </div>
</div>
<p class="text-xs text-gray-500 mb-8">{{ __('parent_ui.rate_note') }}</p>

<!-- Month Navigation (keeps the selected child) -->
<div class="flex items-center gap-3 mb-6" data-ajax-filter-scope>
    <a href="{{ route('parent.attendance.index', ['child_id' => $child->id, 'month' => $month->copy()->subMonthNoOverflow()->format('Y-m')]) }}" class="px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-sm font-semibold hover:border-orange-300 transition">{{ __('parent_ui.prev_month') }}</a>
    <span class="text-sm font-bold text-gray-700">{{ $month->translatedFormat('F Y') }}</span>
    @if($canGoNext)
    <a href="{{ route('parent.attendance.index', ['child_id' => $child->id, 'month' => $month->copy()->addMonthNoOverflow()->format('Y-m')]) }}" class="px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-sm font-semibold hover:border-orange-300 transition">{{ __('parent_ui.next_month') }}</a>
    @else
    <span class="px-3 py-1.5 bg-gray-50 border border-gray-100 rounded-lg text-sm font-semibold text-gray-300 cursor-not-allowed">{{ __('parent_ui.next_month') }}</span>
    @endif
</div>

<!-- Calendar Grid -->
@php
    $statusLabels = [
        'Present' => __('parent_ui.stat_present'),
        'Absent' => __('parent_ui.stat_absent'),
        'Late' => __('parent_ui.stat_late'),
        'Excused' => __('parent_ui.stat_excused'),
    ];
    // What a tapped day reports: its date, the status the teacher assigned, and a note when there is none.
    $dayInfo = function (array $day) use ($statusLabels) {
        $note = null;
        if (! isset($statusLabels[$day['status']])) {
            $note = $day['weekend'] ? __('Weekend') : ($day['future'] ? __('Upcoming day') : __('parent_ui.legend_no_record'));
        }

        return [
            'date' => $day['date'],
            'label' => \Carbon\Carbon::parse($day['date'])->translatedFormat('l, F j, Y'),
            'status' => isset($statusLabels[$day['status']]) ? $day['status'] : 'None',
            'text' => $statusLabels[$day['status']] ?? $note,
        ];
    };
    $today = \Carbon\Carbon::today()->format('Y-m-d');
    $todayDay = collect($monthlyChart)->firstWhere('date', $today);
@endphp
<div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 mb-8" x-data="{ picked: @js($todayDay ? $dayInfo($todayDay) : null) }">
    @if($total === 0)
    <p class="text-sm text-gray-500 mb-4">{{ __('parent_ui.no_attendance_month') }}</p>
    @endif
    <div class="grid grid-cols-7 gap-2">
        @foreach([__('parent_ui.day_sun'), __('parent_ui.day_mon'), __('parent_ui.day_tue'), __('parent_ui.day_wed'), __('parent_ui.day_thu'), __('parent_ui.day_fri'), __('parent_ui.day_sat')] as $dayName)
        <div class="text-center text-xs font-bold text-gray-500 pb-2">{{ $dayName }}</div>
        @endforeach
        @for($i = 0; $i < $month->copy()->startOfMonth()->dayOfWeek; $i++)
        <div></div>
        @endfor
        @foreach($monthlyChart as $day)
        @php
        $bg = match($day['status']) {
            'Present' => 'bg-[#e6f4c8] text-[#4f7a0f]',
            'Absent' => 'bg-[#ffe2e3] text-[#c9343a]',
            'Late' => 'bg-[#fff0bf] text-[#8a6500]',
            'Excused' => 'bg-[#d3e8f6] text-[#0f5483]',
            default => ($day['weekend'] || $day['future']) ? 'bg-white text-gray-300' : 'bg-gray-50 text-gray-500',
        };
        $info = $dayInfo($day);
        @endphp
        <button type="button" @click="picked = @js($info)" :aria-pressed="picked && picked.date === '{{ $day['date'] }}' ? 'true' : 'false'" :class="{ 'ring-2 ring-orange-500': picked && picked.date === '{{ $day['date'] }}' }" class="text-center p-2 rounded-lg text-sm font-semibold transition hover:ring-2 hover:ring-orange-300 focus:outline-none focus:ring-2 focus:ring-orange-400 {{ $bg }}" title="{{ $day['date'] }} · {{ $info['text'] }}">
            {{ $day['day'] }}
        </button>
        @endforeach
    </div>

    <div class="mt-5 min-h-[3.5rem] rounded-xl border border-gray-100 bg-gray-50 px-4 py-3 flex flex-wrap items-center justify-between gap-2" aria-live="polite">
        <p x-show="!picked" class="text-sm text-gray-500">{{ __('Tap a day to see its status.') }}</p>
        <template x-if="picked">
            <p class="text-sm font-semibold text-gray-700" x-text="picked.label"></p>
        </template>
        <span x-show="picked && picked.status === 'Present'" style="display:none" class="px-3 py-1 rounded-full text-xs font-bold bg-[#e6f4c8] text-[#4f7a0f]" x-text="picked && picked.text"></span>
        <span x-show="picked && picked.status === 'Late'" style="display:none" class="px-3 py-1 rounded-full text-xs font-bold bg-[#fff0bf] text-[#8a6500]" x-text="picked && picked.text"></span>
        <span x-show="picked && picked.status === 'Absent'" style="display:none" class="px-3 py-1 rounded-full text-xs font-bold bg-[#ffe2e3] text-[#c9343a]" x-text="picked && picked.text"></span>
        <span x-show="picked && picked.status === 'Excused'" style="display:none" class="px-3 py-1 rounded-full text-xs font-bold bg-[#d3e8f6] text-[#0f5483]" x-text="picked && picked.text"></span>
        <span x-show="picked && picked.status === 'None'" style="display:none" class="px-3 py-1 rounded-full text-xs font-bold bg-gray-200 text-gray-500" x-text="picked && picked.text"></span>
    </div>

    <div class="flex flex-wrap gap-x-4 gap-y-2 mt-5 text-xs text-gray-500">
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-[#e6f4c8] border border-[#b8e170]"></span>{{ __('parent_ui.stat_present') }}</span>
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-[#fff0bf] border border-[#ffd85f]"></span>{{ __('parent_ui.stat_late') }}</span>
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-[#ffe2e3] border border-[#ffa3a6]"></span>{{ __('parent_ui.stat_absent') }}</span>
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-[#d3e8f6] border border-[#7bb9e0]"></span>{{ __('parent_ui.stat_excused') }}</span>
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-gray-50 border border-gray-200"></span>{{ __('parent_ui.legend_no_record') }}</span>
    </div>
</div>

<!-- Monthly Summary -->
@if(count($monthlySummary) > 0)
<div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
    <h3 class="text-lg font-bold text-gray-800 mb-4">{{ __('parent_ui.monthly_summary') }}</h3>
    @foreach($monthlySummary as $m)
    <div class="flex items-center gap-4 p-3 {{ $loop->last ? '' : 'border-b border-gray-50' }}">
        <div class="w-24">
            <p class="text-sm font-semibold text-gray-700">{{ $m['month'] }}</p>
        </div>
        <div class="flex-1">
            <div class="w-full bg-gray-100 rounded-full h-2">
                <div class="h-2 rounded-full bg-[#8ac926]" style="width: {{ $m['recorded'] > 0 ? $m['rate'] : 0 }}%"></div>
            </div>
            <p class="text-xs text-gray-500 mt-1">
                @if($m['recorded'] > 0)
                {{ __('parent_ui.summary_days', ['present' => $m['present'], 'late' => $m['late'], 'absent' => $m['absent']]) }}
                @else
                {{ __('parent_ui.no_attendance_month') }}
                @endif
            </p>
        </div>
        <div class="text-right">
            <span class="text-sm font-bold text-gray-600">{{ $m['recorded'] > 0 ? $m['rate'].'%' : '—' }}</span>
        </div>
    </div>
    @endforeach
</div>
@endif

@endif
@endsection
