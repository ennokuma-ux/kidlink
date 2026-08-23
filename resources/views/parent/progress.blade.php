@extends('layouts.parent')

@section('title', 'Progress')
@section('page-title', 'Progress & Analytics')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#258cc5]">
        <p class="text-[#5f6873] text-sm">Overall Average</p>
        <p class="text-3xl font-bold text-[#182438]">{{ round($overallAvg) }}%</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#68b82f]">
        <p class="text-[#5f6873] text-sm">Best Area</p>
        <p class="text-lg font-bold text-[#68b82f]">{{ $bestArea }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#f05d56]">
        <p class="text-[#5f6873] text-sm">Needs Attention</p>
        <p class="text-lg font-bold text-[#f05d56]">{{ $needsAttention }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#ff8555]">
        <p class="text-[#5f6873] text-sm">Activities Scored</p>
        <p class="text-3xl font-bold text-[#182438]">{{ $scoredActivities }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-4">Score Trend</h3>
        <canvas id="trendChart" height="200"></canvas>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-4">Area Strengths</h3>
        <canvas id="radarChart" height="200"></canvas>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-4">Area Scores</h3>
        <canvas id="barChart" height="250"></canvas>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-4">Recent Activity Scores</h3>
        <div class="space-y-3">
            @forelse($recentScores as $score)
            <div class="flex items-center justify-between p-3 bg-[#faf8f3] rounded-lg">
                <div>
                    <p class="font-medium text-[#182438] text-sm">{{ $score->activity->title ?? 'Activity' }}</p>
                    <p class="text-xs text-[#5f6873]">{{ $score->created_at?->format('M d, Y') }}</p>
                </div>
                <span class="px-3 py-1 rounded-full text-sm font-semibold
                    {{ $score->score >= 80 ? 'bg-green-100 text-green-700' : ($score->score >= 60 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                    {{ $score->score }}%
                </span>
            </div>
            @empty
            <p class="text-[#8b949d] text-center py-4">No scores yet.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
new Chart(document.getElementById('trendChart'), {
    type: 'line',
    data: {
        labels: {!! json_encode($trend->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('M d'))) !!},
        datasets: [{ label: 'Score %', data: {!! json_encode($trend->pluck('avg_score')->map(fn($v) => round($v))) !!}, borderColor: '#258cc5', tension: 0.3, fill: true, backgroundColor: 'rgba(37, 140, 197, 0.1)' }]
    },
    options: { responsive: true, scales: { y: { min: 0, max: 100 } } }
});

new Chart(document.getElementById('radarChart'), {
    type: 'radar',
    data: {
        labels: {!! json_encode(array_keys($radarData)) !!},
        datasets: [{ label: 'Development', data: {!! json_encode(array_values($radarData)) !!}, backgroundColor: 'rgba(37, 140, 197, 0.2)', borderColor: '#258cc5', pointBackgroundColor: '#258cc5' }]
    },
    options: { responsive: true, scales: { r: { min: 0, max: 100 } } }
});

new Chart(document.getElementById('barChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($areaScores->keys()) !!},
        datasets: [{ label: 'Score %', data: {!! json_encode($areaScores->values()->map(fn($v) => round($v))) !!}, backgroundColor: '#258cc5', borderRadius: 4 }]
    },
    options: { responsive: true, indexAxis: 'y', scales: { x: { min: 0, max: 100 } } }
});
</script>
@endpush


