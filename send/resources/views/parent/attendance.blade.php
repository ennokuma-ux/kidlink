@extends('layouts.parent')

@section('title', __('parent_ui.attendance_title'))
@section('page-title', __('parent_ui.attendance_title'))

@section('content')
@if(!$child)
<div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 rounded-lg mb-6">
    <p class="text-yellow-700">{{ __('parent_ui.no_child_selected') }}</p>
</div>
@else
@include('components.child_selector', ['children' => $children, 'child' => $child])

<div class="mb-6">
    <h2 class="text-xl font-bold text-gray-800">{{ __('parent_ui.child_attendance', ['name' => $child->first_name]) }}</h2>
    <p class="text-gray-500 text-sm">{{ $month->translatedFormat('F Y') }}</p>
</div>

<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-3">
    <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-green-500">
        <p class="text-gray-400 text-xs font-semibold uppercase">{{ __('parent_ui.stat_present') }}</p>
        <p class="text-2xl font-bold text-green-600 mt-1">{{ $present }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-red-500">
        <p class="text-gray-400 text-xs font-semibold uppercase">{{ __('parent_ui.stat_absent') }}</p>
        <p class="text-2xl font-bold text-red-500 mt-1">{{ $absent }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-yellow-500">
        <p class="text-gray-400 text-xs font-semibold uppercase">{{ __('parent_ui.stat_late') }}</p>
        <p class="text-2xl font-bold text-yellow-500 mt-1">{{ $late }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-sky-400">
        <p class="text-gray-400 text-xs font-semibold uppercase">{{ __('parent_ui.stat_excused') }}</p>
        <p class="text-2xl font-bold text-sky-500 mt-1">{{ $excused }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-blue-500 col-span-2 md:col-span-1">
        <p class="text-gray-400 text-xs font-semibold uppercase">{{ __('parent_ui.stat_rate') }}</p>
        <p class="text-2xl font-bold text-blue-500 mt-1">{{ $total > 0 ? $rate.'%' : '—' }}</p>
    </div>
</div>
<p class="text-xs text-gray-400 mb-8">{{ __('parent_ui.rate_note') }}</p>

<!-- Month Navigation (keeps the selected child) -->
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('parent.attendance.index', ['child_id' => $child->id, 'month' => $month->copy()->subMonthNoOverflow()->format('Y-m')]) }}" class="px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-sm font-semibold hover:border-orange-300 transition">{{ __('parent_ui.prev_month') }}</a>
    <span class="text-sm font-bold text-gray-700">{{ $month->translatedFormat('F Y') }}</span>
    @if($canGoNext)
    <a href="{{ route('parent.attendance.index', ['child_id' => $child->id, 'month' => $month->copy()->addMonthNoOverflow()->format('Y-m')]) }}" class="px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-sm font-semibold hover:border-orange-300 transition">{{ __('parent_ui.next_month') }}</a>
    @else
    <span class="px-3 py-1.5 bg-gray-50 border border-gray-100 rounded-lg text-sm font-semibold text-gray-300 cursor-not-allowed">{{ __('parent_ui.next_month') }}</span>
    @endif
</div>

<!-- Calendar Grid -->
<div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 mb-8">
    @if($total === 0)
    <p class="text-sm text-gray-400 mb-4">{{ __('parent_ui.no_attendance_month') }}</p>
    @endif
    <div class="grid grid-cols-7 gap-2">
        @foreach([__('parent_ui.day_sun'), __('parent_ui.day_mon'), __('parent_ui.day_tue'), __('parent_ui.day_wed'), __('parent_ui.day_thu'), __('parent_ui.day_fri'), __('parent_ui.day_sat')] as $dayName)
        <div class="text-center text-xs font-bold text-gray-400 pb-2">{{ $dayName }}</div>
        @endforeach
        @for($i = 0; $i < $month->copy()->startOfMonth()->dayOfWeek; $i++)
        <div></div>
        @endfor
        @foreach($monthlyChart as $day)
        @php
        $bg = match($day['status']) {
            'Present' => 'bg-green-100 text-green-700',
            'Absent' => 'bg-red-100 text-red-700',
            'Late' => 'bg-yellow-100 text-yellow-700',
            'Excused' => 'bg-sky-100 text-sky-700',
            default => ($day['weekend'] || $day['future']) ? 'bg-white text-gray-300' : 'bg-gray-50 text-gray-400',
        };
        $statusLabel = match($day['status']) {
            'Present' => __('parent_ui.stat_present'),
            'Absent' => __('parent_ui.stat_absent'),
            'Late' => __('parent_ui.stat_late'),
            'Excused' => __('parent_ui.stat_excused'),
            default => __('parent_ui.legend_no_record'),
        };
        @endphp
        <div class="text-center p-2 rounded-lg text-sm font-semibold {{ $bg }}" title="{{ $day['date'] }} · {{ $statusLabel }}">
            {{ $day['day'] }}
        </div>
        @endforeach
    </div>

    <div class="flex flex-wrap gap-x-4 gap-y-2 mt-5 text-xs text-gray-500">
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-green-100 border border-green-300"></span>{{ __('parent_ui.stat_present') }}</span>
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-yellow-100 border border-yellow-300"></span>{{ __('parent_ui.stat_late') }}</span>
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-red-100 border border-red-300"></span>{{ __('parent_ui.stat_absent') }}</span>
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-sky-100 border border-sky-300"></span>{{ __('parent_ui.stat_excused') }}</span>
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
                <div class="h-2 rounded-full bg-green-500" style="width: {{ $m['recorded'] > 0 ? $m['rate'] : 0 }}%"></div>
            </div>
            <p class="text-xs text-gray-400 mt-1">
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
