@extends('layouts.teacher')

@section('title', 'Attendance')
@section('page-title', 'Daily Attendance')

@section('content')
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <form method="GET" action="{{ route('teacher.attendance.index') }}" class="flex items-center gap-4">
        <label class="text-[#182438] font-medium">Select Date:</label>
        <input type="date" name="date" value="{{ $date->format('Y-m-d') }}" onchange="this.form.submit()"
            class="px-4 py-2.5 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
    </form>
</div>

<div class="grid grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-4 text-center border-l-4 border-[#258cc5]">
        <p class="text-2xl font-bold text-[#182438]">{{ $stats['total'] }}</p>
        <p class="text-sm text-[#5f6873]">Total Students</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 text-center border-l-4 border-[#68b82f]">
        <p class="text-2xl font-bold text-[#68b82f]">{{ $stats['present'] }}</p>
        <p class="text-sm text-[#5f6873]">Present</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 text-center border-l-4 border-[#f05d56]">
        <p class="text-2xl font-bold text-[#f05d56]">{{ $stats['absent'] }}</p>
        <p class="text-sm text-[#5f6873]">Absent</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 text-center border-l-4 border-[#d9b348]">
        <p class="text-2xl font-bold text-[#d9b348]">{{ $stats['late'] }}</p>
        <p class="text-sm text-[#5f6873]">Late</p>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <form method="POST" action="{{ route('teacher.attendance.store') }}">
        @csrf
        <input type="hidden" name="date" value="{{ $date->format('Y-m-d') }}">
        <table class="w-full">
            <thead class="bg-[#faf8f3]">
                <tr>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-[#5f6873]">Student</th>
                    <th class="px-6 py-4 text-center text-sm font-semibold text-[#5f6873]">Present</th>
                    <th class="px-6 py-4 text-center text-sm font-semibold text-[#5f6873]">Absent</th>
                    <th class="px-6 py-4 text-center text-sm font-semibold text-[#5f6873]">Late</th>
                    <th class="px-6 py-4 text-center text-sm font-semibold text-[#5f6873]">Excused</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#ebe8e1]">
                @forelse($students as $student)
                @php $record = $attendance->get($student->id); @endphp
                <tr class="hover:bg-[#faf8f3] transition">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <span class="font-medium text-[#182438]">{{ $student->name }}</span>
                        </div>
                    </td>
                    @foreach(['Present','Absent','Late','Excused'] as $status)
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
                <tr><td colspan="5" class="px-6 py-12 text-center text-[#8b949d]">No active students.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($students->count() > 0)
        <div class="p-6 border-t">
            <button type="submit" class="bg-[#ff8555] text-white px-8 py-3 rounded-lg hover:bg-[#f47548] transition font-medium">Save Attendance</button>
        </div>
        @endif
    </form>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('input[type="radio"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const name = this.name;
        document.querySelectorAll(`input[name="${name}"]`).forEach(r => {
            const span = r.nextElementSibling;
            if (span) span.classList.remove('font-semibold');
        });
        if (this.checked) {
            const span = this.nextElementSibling;
            if (span) span.classList.add('font-semibold');
        }
    });
});
</script>
@endpush




