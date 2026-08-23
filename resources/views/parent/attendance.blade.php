@extends('layouts.parent')

@section('title', 'Attendance')
@section('page-title', 'Attendance Records')

@section('content')
<div class="grid grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#68b82f]">
        <p class="text-[#5f6873] text-sm">Present</p>
        <p class="text-3xl font-bold text-[#68b82f]">{{ $present }}</p>
        <div class="w-full bg-[#ebe8e1] rounded-full h-2 mt-2">
            <div class="bg-green-500 h-2 rounded-full" style="width: {{ $rate }}%"></div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#f05d56]">
        <p class="text-[#5f6873] text-sm">Absent</p>
        <p class="text-3xl font-bold text-[#f05d56]">{{ $absent }}</p>
        <div class="w-full bg-[#ebe8e1] rounded-full h-2 mt-2">
            <div class="bg-red-500 h-2 rounded-full" style="width: {{ $total > 0 ? ($absent/$total)*100 : 0 }}%"></div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#d9b348]">
        <p class="text-[#5f6873] text-sm">Late</p>
        <p class="text-3xl font-bold text-[#d9b348]">{{ $late }}</p>
        <div class="w-full bg-[#ebe8e1] rounded-full h-2 mt-2">
            <div class="bg-yellow-500 h-2 rounded-full" style="width: {{ $total > 0 ? ($late/$total)*100 : 0 }}%"></div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#258cc5]">
        <p class="text-[#5f6873] text-sm">Attendance Rate</p>
        <p class="text-3xl font-bold text-[#258cc5]">{{ $rate }}%</p>
        <div class="w-full bg-[#ebe8e1] rounded-full h-2 mt-2">
            <div class="bg-[#258cc5] h-2 rounded-full" style="width: {{ $rate }}%"></div>
        </div>
    </div>
</div>

<form method="GET" action="{{ route('parent.attendance.index') }}" class="bg-white rounded-xl shadow-sm p-6 mb-6 flex items-center gap-4">
    <label class="text-[#182438] font-medium">Month:</label>
    <input type="month" name="month" value="{{ $month->format('Y-m') }}" onchange="this.form.submit()"
        class="px-4 py-2.5 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#258cc5] outline-none">
</form>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-4">Monthly Attendance</h3>
        <canvas id="monthlyChart" height="200"></canvas>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-4">Distribution</h3>
        <canvas id="distChart" height="200"></canvas>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="px-6 py-4 bg-[#faf8f3] border-b">
        <h3 class="font-semibold text-[#182438]">Monthly Summary (Last 6 Months)</h3>
    </div>
    <table class="w-full">
        <thead class="bg-[#faf8f3]">
            <tr>
                <th class="px-6 py-3 text-left text-sm font-semibold text-[#5f6873]">Month</th>
                <th class="px-6 py-3 text-center text-sm font-semibold text-[#5f6873]">Present</th>
                <th class="px-6 py-3 text-center text-sm font-semibold text-[#5f6873]">Absent</th>
                <th class="px-6 py-3 text-center text-sm font-semibold text-[#5f6873]">Late</th>
                <th class="px-6 py-3 text-center text-sm font-semibold text-[#5f6873]">Rate</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-[#ebe8e1]">
            @foreach($monthlySummary as $summary)
            <tr class="hover:bg-[#faf8f3] transition">
                <td class="px-6 py-4 font-medium text-[#182438]">{{ $summary['month'] }}</td>
                <td class="px-6 py-4 text-center text-[#68b82f] font-medium">{{ $summary['present'] }}</td>
                <td class="px-6 py-4 text-center text-[#f05d56] font-medium">{{ $summary['absent'] }}</td>
                <td class="px-6 py-4 text-center text-[#d9b348] font-medium">{{ $summary['late'] }}</td>
                <td class="px-6 py-4 text-center">
                    <span class="px-3 py-1 rounded-full text-sm font-medium
                        {{ $summary['rate'] >= 80 ? 'bg-green-100 text-green-700' : ($summary['rate'] >= 60 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                        {{ $summary['rate'] }}%
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

@push('scripts')
<script>
new Chart(document.getElementById('monthlyChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode(array_map(fn($d) => $d['day'], $monthlyChart)) !!},
        datasets: [{
            label: 'Status',
            data: {!! json_encode(array_map(fn($d) => $d['status'] === 'Present' ? 1 : ($d['status'] === 'Late' ? 0.5 : 0), $monthlyChart)) !!},
            backgroundColor: {!! json_encode(array_map(fn($d) => $d['status'] === 'Present' ? '#22c55e' : ($d['status'] === 'Late' ? '#eab308' : ($d['status'] === 'Absent' ? '#ef4444' : '#d1d5db')), $monthlyChart)) !!},
            borderRadius: 4
        }]
    },
    options: { responsive: true, scales: { y: { display: false } }, plugins: { legend: { display: false } } }
});

new Chart(document.getElementById('distChart'), {
    type: 'doughnut',
    data: {
        labels: ['Present', 'Absent', 'Late'],
        datasets: [{ data: [{{ $present }}, {{ $absent }}, {{ $late }}], backgroundColor: ['#22c55e', '#ef4444', '#eab308'] }]
    },
    options: { responsive: true, plugins: { legend: { position: 'right' } } }
});
</script>
@endpush



