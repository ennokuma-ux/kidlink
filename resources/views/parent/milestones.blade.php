@extends('layouts.parent')

@section('title', 'Milestones')
@section('page-title', 'Developmental Milestones')

@section('content')
<div class="bg-white rounded-xl shadow-sm p-6 mb-6 text-center">
    <div class="relative w-32 h-32 mx-auto mb-4">
        <svg class="w-32 h-32 transform -rotate-90" viewBox="0 0 36 36">
            <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#e5e7eb" stroke-width="3"/>
            <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#258cc5" stroke-width="3" stroke-dasharray="{{ $overallPercentage }}, 100"/>
        </svg>
        <div class="absolute inset-0 flex items-center justify-center">
            <span class="text-3xl font-bold text-[#258cc5]">{{ $overallPercentage }}%</span>
        </div>
    </div>
    <h3 class="text-xl font-bold text-[#182438]">Overall Development</h3>
    <p class="text-[#5f6873]">{{ $child->name }}'s developmental progress</p>
</div>

<div class="grid grid-cols-3 gap-4 mb-6">
    @foreach($domains as $d)
    @php $stat = $domainStats[$d]; @endphp
    <div class="bg-white rounded-xl shadow-sm p-4 text-center">
        <p class="text-2xl font-bold {{ $stat['percentage'] >= 75 ? 'text-green-600' : ($stat['percentage'] >= 50 ? 'text-yellow-600' : 'text-red-600') }}">
            {{ $stat['percentage'] }}%
        </p>
        <p class="text-xs text-[#5f6873] mt-1">{{ $d }}</p>
        <p class="text-xs text-[#8b949d]">{{ $stat['achieved'] }}/{{ $stat['total'] }}</p>
    </div>
    @endforeach
</div>

<form method="GET" action="{{ route('parent.milestones.index') }}" class="mb-6">
    <div class="flex items-center gap-4">
        <label class="text-[#182438] font-medium">Filter by Domain:</label>
        <select name="domain" onchange="this.form.submit()" class="px-4 py-2.5 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#258cc5] outline-none">
            <option value="all" {{ $domain === 'all' ? 'selected' : '' }}>All Domains</option>
            @foreach($domains as $d)
            <option value="{{ $d }}" {{ $domain === $d ? 'selected' : '' }}>{{ $d }}</option>
            @endforeach
        </select>
    </div>
</form>

<div class="space-y-6">
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-green-50 border-b border-green-200">
            <h3 class="font-bold text-green-700">Achieved ({{ $achieved->count() }})</h3>
        </div>
        <div class="p-6 grid md:grid-cols-2 gap-4">
            @forelse($achieved as $record)
            <div class="flex items-center gap-3 p-3 bg-green-50 rounded-lg">
                <svg class="w-6 h-6 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div>
                    <p class="text-sm font-medium text-[#182438]">{{ $record->indicator }}</p>
                    <span class="text-xs px-2 py-0.5 rounded bg-green-200 text-green-800">{{ $record->domain }}</span>
                </div>
            </div>
            @empty
            <p class="text-[#8b949d] col-span-2 text-center py-4">No achieved milestones yet.</p>
            @endforelse
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-yellow-50 border-b border-yellow-200">
            <h3 class="font-bold text-yellow-700">In Progress ({{ $inProgress->count() }})</h3>
        </div>
        <div class="p-6 grid md:grid-cols-2 gap-4">
            @forelse($inProgress as $record)
            <div class="flex items-center gap-3 p-3 bg-yellow-50 rounded-lg">
                <svg class="w-6 h-6 text-yellow-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div>
                    <p class="text-sm font-medium text-[#182438]">{{ $record->indicator }}</p>
                    <span class="text-xs px-2 py-0.5 rounded bg-yellow-200 text-yellow-800">{{ $record->domain }}</span>
                </div>
            </div>
            @empty
            <p class="text-[#8b949d] col-span-2 text-center py-4">No milestones in progress.</p>
            @endforelse
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-[#faf8f3] border-b border-[#ebe8e1]">
            <h3 class="font-bold text-[#182438]">Not Yet ({{ $notYet->count() }})</h3>
        </div>
        <div class="p-6 grid md:grid-cols-2 gap-4">
            @forelse($notYet as $record)
            <div class="flex items-center gap-3 p-3 bg-[#faf8f3] rounded-lg">
                <svg class="w-6 h-6 text-[#8b949d] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div>
                    <p class="text-sm font-medium text-[#182438]">{{ $record->indicator }}</p>
                    <span class="text-xs px-2 py-0.5 rounded bg-[#ebe8e1] text-[#182438]">{{ $record->domain }}</span>
                </div>
            </div>
            @empty
            <p class="text-[#8b949d] col-span-2 text-center py-4">All milestones achieved!</p>
            @endforelse
        </div>
    </div>
</div>
@endsection




