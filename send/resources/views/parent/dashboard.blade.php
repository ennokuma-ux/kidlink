@extends('layouts.parent')

@section('title', __('page.dashboard'))
@section('page-title', __('page.parent_dashboard'))

@section('content')
@if(!isset($child) || !$child)
<div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 rounded-lg mb-6">
    <p class="text-yellow-700">{{ __('dashboard.no_children') }}</p>
</div>
@else
@include('components.child_selector', ['children' => $children, 'child' => $child])

{{-- Child profile card --}}
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <div class="flex items-center gap-6">
        <div class="w-20 h-20 bg-gradient-to-br from-blue-400 to-purple-500 rounded-full flex items-center justify-center text-white font-bold text-3xl">
            {{ strtoupper(substr($child->first_name, 0, 1)) }}{{ strtoupper(substr($child->last_name, 0, 1)) }}
        </div>
        <div>
            <h2 class="text-2xl font-bold text-gray-800">{{ $child->name }}</h2>
            <p class="text-gray-500">{{ __('dashboard.age') }} {{ $child->age }} | {{ __('dashboard.class_label') }} {{ $child->class_name ?? 'N/A' }} | {{ __('student.hand_label') }} {{ $child->handLabel() }}</p>
            @if($child->teacher)<p class="text-gray-500 text-sm">{{ __('dashboard.teacher_label') }} {{ $child->teacher->full_name ?? 'N/A' }}</p>@endif
            <p class="text-gray-500 text-sm">{{ __('dashboard.enrolled') }} {{ $child->enrolled_date?->format('M d, Y') ?? 'N/A' }}</p>
        </div>
    </div>
</div>

<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-xl font-bold text-gray-800">{{ __('analytics.analytics_title_for', ['name' => $child->first_name]) }}</h2>
        <p class="text-gray-500 text-sm">{{ __('analytics.analytics_subtitle') }}</p>
    </div>
</div>

{{-- Headline stats: overall average, attendance, domain mastery and engagement --}}
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
    <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-blue-500">
        <p class="text-gray-400 text-xs font-semibold uppercase">{{ __('analytics.stat_overall_average') }}</p>
        <p class="text-2xl font-bold text-gray-800 mt-1">{{ round($overallAvg) }}%</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-green-500">
        <p class="text-gray-400 text-xs font-semibold uppercase">{{ __('analytics.stat_attendance_rate') }}</p>
        <p class="text-2xl font-bold text-green-600 mt-1">{{ $attendanceRate }}%</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-purple-500">
        <p class="text-gray-400 text-xs font-semibold uppercase">{{ __('analytics.stat_domain_mastery') }}</p>
        <p class="text-2xl font-bold text-purple-600 mt-1">{{ $domainMastery }}%</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-orange-500">
        <p class="text-gray-400 text-xs font-semibold uppercase">{{ __('analytics.stat_activities_scored') }}</p>
        <p class="text-2xl font-bold text-gray-800 mt-1">{{ $scoredActivities }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-red-500">
        <p class="text-gray-400 text-xs font-semibold uppercase">{{ __('dashboard.absences') }}</p>
        <p class="text-2xl font-bold text-red-600 mt-1">{{ $absentCount }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-yellow-500">
        <p class="text-gray-400 text-xs font-semibold uppercase">{{ __('dashboard.late') }}</p>
        <p class="text-2xl font-bold text-yellow-600 mt-1">{{ $lateCount }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <h3 class="text-lg font-semibold text-gray-800 mb-1">{{ __('analytics.score_trend') }}</h3>
        <p class="text-xs text-gray-400 mb-4">{{ __('analytics.recorded_days', ['count' => $scoreTrend->count()]) }}</p>
        @if($scoreTrend->count())
        <canvas id="scoreTrendChart" height="220"></canvas>
        @else
        <div class="h-[220px] flex items-center justify-center text-sm text-gray-400 bg-gray-50 rounded-lg">{{ __('analytics.no_game_scores') }}</div>
        @endif
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <h3 class="text-lg font-semibold text-gray-800 mb-1">{{ __('analytics.developmental_domains') }}</h3>
        <p class="text-xs text-gray-400 mb-4">{{ __('analytics.indicators_assessed', ['count' => $assessedIndicators]) }}</p>
        @if($assessedIndicators > 0)
        <canvas id="domainRadarChart" height="220"></canvas>
        @else
        <div class="h-[220px] flex items-center justify-center text-sm text-gray-400 bg-gray-50 rounded-lg">{{ __('analytics.no_indicators_assessed') }}</div>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <h3 class="text-lg font-semibold text-gray-800 mb-1">{{ __('analytics.indicator_status_title') }}</h3>
        <p class="text-xs text-gray-400 mb-4">{{ __('analytics.status_subtitle_parent') }}</p>
        @if($assessedIndicators > 0)
        <canvas id="domainStatusChart" height="240"></canvas>
        @else
        <div class="h-[240px] flex items-center justify-center text-sm text-gray-400 bg-gray-50 rounded-lg">{{ __('analytics.no_indicators_assessed_short') }}</div>
        @endif
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <h3 class="text-lg font-semibold text-gray-800 mb-1">{{ __('analytics.area_performance') }}</h3>
        <p class="text-xs text-gray-400 mb-4">{{ __('analytics.area_subtitle') }}</p>
        @if($areaScores->count())
        <canvas id="areaPerformanceChart" height="240"></canvas>
        @else
        <div class="h-[240px] flex items-center justify-center text-sm text-gray-400 bg-gray-50 rounded-lg">{{ __('analytics.no_activity_scores') }}</div>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <h3 class="text-lg font-semibold text-gray-800 mb-1">{{ __('analytics.game_performance') }}</h3>
        <p class="text-xs text-gray-400 mb-4">{{ __('analytics.sessions_played', ['count' => $totalPlays]) }}</p>
        @if($gamePerformance->count())
        <canvas id="gamePerformanceChart" height="240"></canvas>
        @else
        <div class="h-[240px] flex items-center justify-center text-sm text-gray-400 bg-gray-50 rounded-lg">{{ __('analytics.no_games_played') }}</div>
        @endif
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <h3 class="text-lg font-semibold text-gray-800 mb-1">{{ __('analytics.attendance_trend') }}</h3>
        <p class="text-xs text-gray-400 mb-4">{{ __('analytics.last_8_weeks') }}</p>
        @if($attendanceTrend->count())
        <canvas id="attendanceTrendChart" height="240"></canvas>
        @else
        <div class="h-[240px] flex items-center justify-center text-sm text-gray-400 bg-gray-50 rounded-lg">{{ __('analytics.no_attendance_recent') }}</div>
        @endif
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('analytics.insights') }}</h3>
    @if(count($insights))
    <ul class="space-y-3">
        @foreach($insights as $insight)
        <li class="flex items-start gap-3 text-sm text-gray-600">
            <span class="mt-0.5 w-2 h-2 rounded-full bg-blue-500 flex-shrink-0"></span>
            <span>{{ $insight }}</span>
        </li>
        @endforeach
    </ul>
    @else
    <p class="text-gray-400 text-sm">{{ __('analytics.no_data_recorded') }}</p>
    @endif
</div>
@endif
@endsection

@push('scripts')
@if($child)
<script>
const scoreTrend = {!! json_encode($scoreTrend) !!};
const domainScores = {!! json_encode($domainScores) !!};
const domainStatus = {!! json_encode($domainStatus) !!};
const areaScores = {!! json_encode($areaScores) !!};
const gamePerformance = {!! json_encode($gamePerformance) !!};
const attendanceTrend = {!! json_encode($attendanceTrend) !!};
const has = id => !!document.getElementById(id);

const L = {
    'score': @json(__('analytics.chart_score')),
    'average': @json(__('analytics.chart_average')),
    'avg_score_pct': @json(__('analytics.chart_avg_score_pct')),
    'sessions': @json(__('analytics.chart_sessions')),
    'present': @json(__('analytics.chart_present')),
    'late': @json(__('analytics.chart_late')),
    'absent': @json(__('analytics.chart_absent')),
    'achieved': @json(__('analytics.status_achieved')),
    'in_progress': @json(__('analytics.status_in_progress')),
    'not_yet': @json(__('analytics.status_not_yet')),
};

if (has('scoreTrendChart')) {
    new Chart(document.getElementById('scoreTrendChart'), {
        type: 'line',
        data: {
            labels: scoreTrend.map(r => new Date(r.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })),
            datasets: [{
                label: L.score,
                data: scoreTrend.map(r => Math.round(Number(r.avg_score))),
                borderColor: '#3b82f6',
                tension: 0.3,
                fill: true,
                backgroundColor: 'rgba(59,130,246,0.1)'
            }]
        },
        options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { min: 0, max: 100 } } }
    });
}

if (has('domainRadarChart')) {
    new Chart(document.getElementById('domainRadarChart'), {
        type: 'radar',
        data: {
            labels: Object.keys(domainScores),
            datasets: [{
                label: L.achieved,
                data: Object.values(domainScores),
                backgroundColor: 'rgba(147,51,234,0.2)',
                borderColor: '#9333ea',
                pointBackgroundColor: '#9333ea'
            }]
        },
        options: { responsive: true, plugins: { legend: { display: false } }, scales: { r: { min: 0, max: 100 } } }
    });
}

if (has('domainStatusChart')) {
    new Chart(document.getElementById('domainStatusChart'), {
        type: 'doughnut',
        data: {
            labels: [L.achieved, L.in_progress, L.not_yet],
            datasets: [{
                data: [
                    Number(domainStatus['Achieved'] || 0),
                    Number(domainStatus['In Progress'] || 0),
                    Number(domainStatus['Not Yet'] || 0)
                ],
                backgroundColor: ['#22c55e', '#3b82f6', '#f59e0b']
            }]
        },
        options: { responsive: true, plugins: { legend: { position: 'right' } } }
    });
}

if (has('areaPerformanceChart')) {
    new Chart(document.getElementById('areaPerformanceChart'), {
        type: 'bar',
        data: {
            labels: Object.keys(areaScores),
            datasets: [{
                label: L.average,
                data: Object.values(areaScores).map(v => Math.round(Number(v))),
                backgroundColor: '#3b82f6',
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            indexAxis: 'y',
            plugins: { legend: { display: false } },
            scales: { x: { min: 0, max: 100 } }
        }
    });
}

if (has('gamePerformanceChart') && gamePerformance.length) {
    new Chart(document.getElementById('gamePerformanceChart'), {
        type: 'bar',
        data: {
            labels: gamePerformance.map(g => g.game_type),
            datasets: [
                { label: L.avg_score_pct, data: gamePerformance.map(g => Math.round(Number(g.avg_pct) || 0)), backgroundColor: '#3b82f6', borderRadius: 4 },
                { label: L.sessions, data: gamePerformance.map(g => Number(g.plays)), backgroundColor: '#bfdbfe', borderRadius: 4 }
            ]
        },
        options: { responsive: true, plugins: { legend: { position: 'top' } }, scales: { y: { beginAtZero: true } } }
    });
}

if (has('attendanceTrendChart') && attendanceTrend.length) {
    new Chart(document.getElementById('attendanceTrendChart'), {
        type: 'bar',
        data: {
            labels: attendanceTrend.map(r => new Date(r.week).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })),
            datasets: [
                { label: L.present, data: attendanceTrend.map(r => Number(r.present)), backgroundColor: '#22c55e', borderRadius: 4 },
                { label: L.late, data: attendanceTrend.map(r => Number(r.late)), backgroundColor: '#f59e0b', borderRadius: 4 },
                { label: L.absent, data: attendanceTrend.map(r => Number(r.absent)), backgroundColor: '#ef4444', borderRadius: 4 }
            ]
        },
        options: { responsive: true, plugins: { legend: { position: 'top' } }, scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true } } }
    });
}
</script>
@endif
@endpush
