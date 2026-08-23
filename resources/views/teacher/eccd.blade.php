@extends('layouts.teacher')

@section('title', 'ECCD Checklist')
@section('page-title', 'ECCD Checklist')

@section('content')
<div class="mb-6 bg-white rounded-xl shadow-sm p-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h3 class="text-lg font-semibold text-[#182438]">Early Childhood Care & Development</h3>
            <p class="text-sm text-[#5f6873] mt-1">Administer all items per evaluation. Status: <span class="font-medium text-[#ff8555]">Evaluation #{{ $evaluationNumber }}</span></p>
        </div>
        <form method="GET" action="{{ route('teacher.eccd.index') }}" class="flex items-center gap-3">
            <select name="student_id" onchange="this.form.submit()"
                class="px-4 py-2.5 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none text-sm">
                @foreach($students as $s)
                <option value="{{ $s->id }}" {{ $selectedStudent && $selectedStudent->id === $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                @endforeach
            </select>
            <input type="hidden" name="evaluation" value="{{ $evaluationNumber }}">
        </form>
    </div>
</div>

@if($selectedStudent)
<div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#ff8555]">
        <p class="text-[#5f6873] text-sm">Child Name</p>
        <p class="text-2xl font-bold text-[#182438]">{{ $selectedStudent->name }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#258cc5]">
        <p class="text-[#5f6873] text-sm">Age</p>
        <p class="text-2xl font-bold text-[#182438]">{{ $selectedStudent->age }} yrs</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#68b82f]">
        <p class="text-[#5f6873] text-sm">Parent</p>
        <p class="text-2xl font-bold text-[#182438]">{{ $selectedStudent->parent->full_name ?? '—' }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#d9b348]">
        <p class="text-[#5f6873] text-sm">Overall Progress</p>
        <p class="text-2xl font-bold text-[#182438]">
            {{ $records->flatten()->count() > 0 ? round($records->flatten()->where('status','Achieved')->count() / max($records->flatten()->count(),1) * 100) : 0 }}%
        </p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    @foreach($domains as $domain)
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-3">
            <h4 class="font-semibold text-[#182438]">{{ $domain }}</h4>
            <span class="text-sm font-bold text-[#ff8555]">{{ $scores[$domain] ?? 0 }}%</span>
        </div>
        <div class="w-full bg-[#ebe8e1] rounded-full h-2 mb-3">
            <div class="bg-gradient-to-r from-[#ff8555] to-[#f47548] h-2 rounded-full" style="width: {{ min($scores[$domain] ?? 0, 100) }}%"></div>
        </div>
        <p class="text-xs text-[#5f6873]">Expected: {{ $expectedScores[$domain] ?? 0 }}%</p>
    </div>
    @endforeach
</div>

@foreach($domains as $domain)
@php $domainRecords = $records->get($domain, collect()); @endphp
<div class="bg-white rounded-xl shadow-sm overflow-hidden mb-8">
    <div class="px-6 py-4 bg-[#182438] text-white flex items-center justify-between">
        <div>
            <h3 class="font-semibold">{{ $domain }}</h3>
            <p class="text-xs text-white/60">Evaluation #{{ $evaluationNumber }}</p>
        </div>
        <div class="text-right">
            <p class="text-xs text-white/60">Score</p>
            <p class="text-xl font-bold">{{ $scores[$domain] ?? 0 }}%</p>
        </div>
    </div>
    <table class="w-full">
        <thead class="bg-[#faf8f3]">
            <tr>
                <th class="px-6 py-3 text-left text-sm font-semibold text-[#5f6873] w-10">#</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-[#5f6873]">Indicator</th>
                <th class="px-6 py-3 text-center text-sm font-semibold text-[#5f6873] w-64">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-[#ebe8e1]">
            @forelse($domainRecords as $i => $record)
            <tr class="hover:bg-[#faf8f3] transition">
                <td class="px-6 py-4 text-[#5f6873]">{{ $i + 1 }}</td>
                <td class="px-6 py-4 font-medium text-[#182438]">{{ $record->indicator }}</td>
                <td class="px-6 py-4">
                    <form method="POST" action="{{ route('teacher.eccd.update') }}" class="flex items-center gap-2">
                        @csrf
                        <input type="hidden" name="student_id" value="{{ $selectedStudent->id }}">
                        <input type="hidden" name="record_id" value="{{ $record->id }}">
                        <input type="hidden" name="evaluation" value="{{ $evaluationNumber }}">
                        @foreach(['Not Yet','In Progress','Achieved'] as $status)
                        <button type="submit" name="status" value="{{ $status }}"
                            class="px-3 py-1.5 rounded-full text-xs font-medium transition
                            {{ $record->status === $status
                                ? ($status === 'Achieved' ? 'bg-[#68b82f] text-white' : ($status === 'In Progress' ? 'bg-[#d9b348] text-white' : 'bg-[#f05d56] text-white'))
                                : 'bg-[#ebe8e1] text-[#5f6873] hover:bg-[#e0dcd3]' }}">
                            {{ $status }}
                        </button>
                        @endforeach
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="3" class="px-6 py-8 text-center text-[#5f6873]">No records yet for this domain.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endforeach

@if(count($recommendations) > 0)
<div class="bg-white rounded-xl shadow-sm p-6">
    <h3 class="text-lg font-semibold text-[#182438] mb-4">Expert Recommendations</h3>
    <div class="space-y-3">
        @foreach($recommendations as $rec)
        <div class="p-4 rounded-lg border
            {{ $rec['severity'] === 'success' ? 'border-[#68b82f] bg-green-50' : ($rec['severity'] === 'warning' ? 'border-[#d9b348] bg-yellow-50' : 'border-[#f05d56] bg-red-50') }}">
            <p class="font-semibold text-[#182438] text-sm">{{ $rec['title'] }}</p>
            <p class="text-sm text-[#5f6873] mt-1">{{ $rec['message'] }}</p>
        </div>
        @endforeach
    </div>
</div>
@endif
@else
<div class="bg-white rounded-xl shadow-sm p-10 text-center">
    <p class="text-[#5f6873]">No active students assigned. Add a student first.</p>
    <a href="{{ route('teacher.students.index') }}" class="inline-block mt-4 bg-[#ff8555] hover:bg-[#f47548] text-white px-6 py-2.5 rounded-lg transition font-medium">Go to Students</a>
</div>
@endif
@endsection