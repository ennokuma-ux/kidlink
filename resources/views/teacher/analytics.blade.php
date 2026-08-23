@extends('layouts.teacher')

@section('title', 'Analytics')
@section('page-title', 'Descriptive Analytics')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-4">Attendance Rate Trend</h3>
        <canvas id="attendanceTrendChart" height="200"></canvas>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-4">Weekly Attendance Breakdown</h3>
        <canvas id="weeklyBreakdownChart" height="200"></canvas>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-4">Performance Radar</h3>
        <canvas id="performanceRadarChart" height="250"></canvas>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-4">Age Distribution</h3>
        <canvas id="ageDistChart" height="250"></canvas>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-4">Enrollment Trend</h3>
        <canvas id="enrollTrendChart" height="250"></canvas>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-4">Student Comparison - Game Scores</h3>
        <canvas id="studentComparisonChart" height="300"></canvas>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-4">Recommended Interventions</h3>
        <div class="space-y-3">
            @foreach($recommendations as $rec)
            <div class="p-4 rounded-lg bg-gradient-to-r from-[#fff0e6] to-[#fde8ef] border border-[#ffd9c2]">
                <h4 class="font-semibold text-[#182438]">{{ $rec['name'] }}</h4>
                <p class="text-sm text-[#5f6873] mt-1"><span class="font-medium">Needs:</span> {{ $rec['needs'] }}</p>
                <p class="text-sm text-[#5f6873]"><span class="font-medium">Plan:</span> {{ $rec['plan'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
new Chart(document.getElementById('attendanceTrendChart'), {
    type: 'line',
    data: {
        labels: {!! json_encode(array_column($attendanceRate, 'month')) !!},
        datasets: [{ label: 'Attendance Rate %', data: {!! json_encode(array_column($attendanceRate, 'rate')) !!}, borderColor: '#ff8555', tension: 0.3, fill: true, backgroundColor: 'rgba(255,133,85,0.1)' }]
    },
    options: { responsive: true, scales: { y: { min: 0, max: 100 } } }
});

new Chart(document.getElementById('weeklyBreakdownChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode(array_column($weeklyBreakdown, 'day')) !!},
        datasets: [
            { label: 'Present', data: {!! json_encode(array_column($weeklyBreakdown, 'present')) !!}, backgroundColor: '#22c55e' },
            { label: 'Absent', data: {!! json_encode(array_column($weeklyBreakdown, 'absent')) !!}, backgroundColor: '#ef4444' },
            { label: 'Late', data: {!! json_encode(array_column($weeklyBreakdown, 'late')) !!}, backgroundColor: '#eab308' }
        ]
    },
    options: { responsive: true }
});

new Chart(document.getElementById('performanceRadarChart'), {
    type: 'radar',
    data: {
        labels: {!! json_encode(array_keys($performanceRadar)) !!},
        datasets: [{ label: 'Performance', data: {!! json_encode(array_values($performanceRadar)) !!}, backgroundColor: 'rgba(255,133,85,0.2)', borderColor: '#ff8555', pointBackgroundColor: '#ff8555' }]
    },
    options: { responsive: true, scales: { r: { min: 0, max: 100 } } }
});

new Chart(document.getElementById('ageDistChart'), {
    type: 'doughnut',
    data: {
        labels: {!! json_encode(array_keys($ageDistribution)) !!},
        datasets: [{ data: {!! json_encode(array_values($ageDistribution)) !!}, backgroundColor: ['#ff8555','#f05d56','#258cc5','#68b82f'] }]
    },
    options: { responsive: true }
});

new Chart(document.getElementById('enrollTrendChart'), {
    type: 'line',
    data: {
        labels: {!! json_encode(array_column($enrollmentTrend, 'month')) !!},
        datasets: [{ label: 'Enrollments', data: {!! json_encode(array_column($enrollmentTrend, 'total')) !!}, borderColor: '#10b981', tension: 0.3, fill: true, backgroundColor: 'rgba(16,185,129,0.1)' }]
    },
    options: { responsive: true }
});

const sLabels = ['letters','numbers','colors','shapes','animals'];
const sDatasets = {!! json_encode($studentComparison) !!};
const sColors = ['#ff8555','#f05d56','#258cc5','#68b82f','#d9b348','#e78cae'];
new Chart(document.getElementById('studentComparisonChart'), {
    type: 'bar',
    data: {
        labels: sLabels,
        datasets: sDatasets.map((s, i) => ({ label: s.name, data: sLabels.map(l => s.scores[l] || 0), backgroundColor: sColors[i % sColors.length] }))
    },
    options: { responsive: true }
});
</script>
@endpush

