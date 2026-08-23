@extends('layouts.parent')

@section('title', 'Dashboard')
@section('page-title', 'Parent Dashboard')

@section('content')
@if(!isset($child) || !$child)
<div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 rounded-lg mb-6">
    <p class="text-yellow-700">No children registered yet. Please contact the daycare center to link your account.</p>
</div>
@else
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <div class="flex items-center gap-6">
        <div class="w-20 h-20 bg-gradient-to-br from-[#258cc5] to-[#ff8555] rounded-full flex items-center justify-center text-white font-bold text-3xl">
            {{ strtoupper(substr($child->first_name, 0, 1)) }}{{ strtoupper(substr($child->last_name, 0, 1)) }}
        </div>
        <div>
            <h2 class="text-2xl font-bold text-[#182438]">{{ $child->name }}</h2>
            <p class="text-[#5f6873]">Age: {{ $child->age }} years | Class: {{ $child->class_name ?? 'N/A' }}</p>
            @if($child->teacher)<p class="text-[#5f6873] text-sm">Teacher: {{ $child->teacher->full_name ?? 'N/A' }}</p>@endif
            <p class="text-[#5f6873] text-sm">Enrolled: {{ $child->enrolled_date?->format('M d, Y') ?? 'N/A' }}</p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#258cc5]">
        <p class="text-[#5f6873] text-sm">Average Score</p>
        <p class="text-3xl font-bold text-[#182438]">{{ round($avgScore) }}%</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#68b82f]">
        <p class="text-[#5f6873] text-sm">Attendance Rate</p>
        <p class="text-3xl font-bold text-[#68b82f]">{{ $attendanceRate }}%</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#f05d56]">
        <p class="text-[#5f6873] text-sm">Absences</p>
        <p class="text-3xl font-bold text-[#f05d56]">{{ $absentCount }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#d9b348]">
        <p class="text-[#5f6873] text-sm">Late</p>
        <p class="text-3xl font-bold text-[#d9b348]">{{ $lateCount }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-4">Score Trend</h3>
        <canvas id="scoreTrendChart" height="200"></canvas>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-4">Developmental Strengths</h3>
        <canvas id="radarChart" height="200"></canvas>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm p-6 mb-8">
    <h3 class="text-lg font-semibold text-[#182438] mb-4">Area Performance</h3>
    <div class="space-y-4">
        @forelse($areaScores as $type => $score)
        <div>
            <div class="flex justify-between text-sm mb-1">
                <span class="text-[#182438] font-medium">{{ $type }}</span>
                <span class="text-[#5f6873]">{{ round($score) }}%</span>
            </div>
            <div class="w-full bg-[#ebe8e1] rounded-full h-2.5">
                <div class="bg-[#258cc5] h-2.5 rounded-full" style="width: {{ $score }}%"></div>
            </div>
        </div>
        @empty
        <p class="text-[#8b949d] text-center py-4">No activity data yet.</p>
        @endforelse
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm p-6 mb-8">
    <h3 class="text-lg font-semibold text-[#182438] mb-4">Child Mode</h3>
    <p class="text-[#5f6873] text-sm mb-4">Let your child play educational games in KidLink's safe mode.</p>
    <div class="flex flex-wrap gap-3">
        @foreach($children as $c)
        <form method="POST" action="{{ route('child-mode.enter', $c) }}">
            @csrf
            <button type="submit" class="bg-gradient-to-r from-[#68b82f] to-[#4e8a2f] text-white px-6 py-3 rounded-xl font-semibold hover:from-[#5da32a] hover:to-[#3f7a26] transition shadow-lg transform hover:scale-105">
                Play as {{ $c->name }}
            </button>
        </form>
        @endforeach
    </div>
</div>
@endif
@endsection

@push('scripts')
@if(isset($child) && $child)
<script>
new Chart(document.getElementById('scoreTrendChart'), {
    type: 'line',
    data: {
        labels: {!! json_encode($gameScores->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('M d'))) !!},
        datasets: [{ label: 'Score %', data: {!! json_encode($gameScores->pluck('avg_score')->map(fn($v) => round($v))) !!}, borderColor: '#258cc5', tension: 0.3, fill: true, backgroundColor: 'rgba(37, 140, 197, 0.1)' }]
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
</script>
@endif
@endpush


