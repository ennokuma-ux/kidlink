@extends('layouts.parent')

@section('title', __('parent_ui.milestones_title'))
@section('page-title', __('parent_ui.milestones_title'))

@section('content')
@if(!$child)
<div class="bg-[#fff9e6] border-l-4 border-[#ffd04a] p-6 rounded-lg mb-6">
    <p class="text-[#8a6500]">{{ __('parent_ui.no_child_selected') }}</p>
</div>
@else
@include('components.child_selector', ['children' => $children, 'child' => $child])

@php
    $statusStyle = [
        'Achieved' => ['bg' => '#ecf7d6', 'fg' => '#6fa51c', 'icon' => '<i class="ico ico-checkmark-circle text-xl" aria-hidden="true"></i>', 'label' => __('parent_ui.status_achieved')],
        'In Progress' => ['bg' => '#d6eaf7', 'fg' => '#1982c4', 'icon' => '<i class="ico ico-star-half text-xl" aria-hidden="true"></i>', 'label' => __('parent_ui.status_in_progress')],
        'Not Yet' => ['bg' => '#f3f4f6', 'fg' => '#6b7280', 'icon' => '<i class="ico ico-remove-circle text-xl" aria-hidden="true"></i>', 'label' => __('parent_ui.status_not_yet')],
    ];
    $barColor = fn (int $pct) => $pct >= 80 ? '#8ac926' : ($pct >= 50 ? '#1982c4' : '#f97316');
@endphp

<div class="mb-6">
    <h2 class="text-xl font-bold text-gray-800">{{ __('parent_ui.child_milestones', ['name' => $child->first_name]) }}</h2>
    <p class="text-gray-500 text-sm">{{ __('parent_ui.overall_prefix') }} <span class="font-bold">{{ $overallPercentage }}%</span> {{ __('parent_ui.overall_suffix') }}</p>
</div>

@if($totalIndicators === 0)
<div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
    <p class="text-gray-400 text-center py-8">{{ __('parent_ui.no_milestone_records') }}</p>
</div>
@else

<!-- Status totals -->
<div class="grid grid-cols-3 gap-4 mb-6">
    @foreach($statusStyle as $status => $style)
    <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100 text-center">
        <p class="text-2xl font-bold" style="color: {{ $style['fg'] }};">{{ $counts[$status] }}</p>
        <p class="text-xs font-semibold text-gray-400 uppercase mt-1">{{ $style['label'] }}</p>
    </div>
    @endforeach
</div>

<!-- Domain Stats -->
<div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-8">
    @foreach($domainStats as $domainName => $stat)
    <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
        <p class="text-sm font-bold text-gray-700 mb-2">{{ $stat['label'] }}</p>
        <div class="flex items-center gap-2 mb-2">
            <span class="text-2xl font-bold" style="color: {{ $barColor($stat['percentage']) }};">{{ $stat['percentage'] }}%</span>
        </div>
        <div class="w-full bg-gray-100 rounded-full h-2">
            <div class="h-2 rounded-full" style="width: {{ $stat['percentage'] }}%; background: {{ $barColor($stat['percentage']) }};"></div>
        </div>
        <p class="text-xs text-gray-400 mt-1">{{ $stat['achieved'] }}/{{ $stat['total'] }} {{ __('parent_ui.achieved_label') }}</p>
    </div>
    @endforeach
</div>

<!-- Domain Filter (keeps the selected child) -->
<div class="flex flex-wrap gap-2 mb-6" data-ajax-filter-scope>
    <a href="{{ route('parent.milestones.index', ['child_id' => $child->id]) }}" class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ $activeDomain === 'all' ? 'bg-orange-500 text-white' : 'bg-white text-gray-500 border border-gray-200 hover:border-orange-300' }}">{{ __('parent_ui.filter_all') }}</a>
    @foreach($domains as $d)
    <a href="{{ route('parent.milestones.index', ['child_id' => $child->id, 'domain' => $d]) }}" class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ $activeDomain === $d ? 'bg-orange-500 text-white' : 'bg-white text-gray-500 border border-gray-200 hover:border-orange-300' }}">{{ $domainStats[$d]['label'] }}</a>
    @endforeach
</div>

<!-- Records, grouped by domain -->
@foreach($recordsByDomain as $domainName => $rows)
<div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 mb-6">
    <h3 class="text-base font-bold text-gray-800 mb-2">{{ $domainStats[$domainName]['label'] ?? $domainName }}</h3>
    @foreach($rows as $record)
    @php $s = $statusStyle[$record->status] ?? $statusStyle['Not Yet']; @endphp
    <div class="flex items-center gap-4 py-3 {{ $loop->last ? '' : 'border-b border-gray-50' }}">
        <div class="w-9 h-9 rounded-xl flex items-center justify-center text-sm font-bold flex-shrink-0" style="background: {{ $s['bg'] }}; color: {{ $s['fg'] }};">{!! $s['icon'] !!}</div>
        <p class="flex-1 text-sm font-semibold text-gray-700">{{ $record->indicator_label }}</p>
        <span class="px-3 py-1 rounded-full text-xs font-bold flex-shrink-0" style="background: {{ $s['bg'] }}; color: {{ $s['fg'] }};">{{ $s['label'] }}</span>
    </div>
    @endforeach
</div>
@endforeach

@endif

@endif
@endsection
