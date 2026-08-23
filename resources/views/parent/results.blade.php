@extends('layouts.parent')

@section('title', 'Activity Results')
@section('page-title', 'Activity Results')

@section('content')
<div class="grid grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#258cc5]">
        <p class="text-[#5f6873] text-sm">Average Score</p>
        <p class="text-3xl font-bold text-[#182438]">{{ round($avgScore) }}%</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#68b82f]">
        <p class="text-[#5f6873] text-sm">Excellent (80%+)</p>
        <p class="text-3xl font-bold text-[#68b82f]">{{ $excellent }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#d9b348]">
        <p class="text-[#5f6873] text-sm">Good (60-79%)</p>
        <p class="text-3xl font-bold text-[#d9b348]">{{ $good }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#f05d56]">
        <p class="text-[#5f6873] text-sm">Needs Work (&lt;60%)</p>
        <p class="text-3xl font-bold text-[#f05d56]">{{ $needsWork }}</p>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full">
        <thead class="bg-[#faf8f3]">
            <tr>
                <th class="px-6 py-4 text-left text-sm font-semibold text-[#5f6873]">Activity</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-[#5f6873]">Type</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-[#5f6873]">Date/Time</th>
                <th class="px-6 py-4 text-center text-sm font-semibold text-[#5f6873]">Score</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-[#5f6873]">Feedback</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-[#ebe8e1]">
            @forelse($results as $result)
            <tr class="hover:bg-[#faf8f3] transition">
                <td class="px-6 py-4 font-medium text-[#182438]">{{ $result->activity->title ?? 'N/A' }}</td>
                <td class="px-6 py-4">
                    <span class="px-3 py-1 rounded-full text-xs font-medium
                        {{ $result->activity->type === 'Lesson' ? 'bg-blue-100 text-blue-700' : '' }}
                        {{ $result->activity->type === 'Craft' ? 'bg-orange-100 text-orange-700' : '' }}
                        {{ $result->activity->type === 'Music' ? 'bg-pink-100 text-pink-700' : '' }}
                        {{ $result->activity->type === 'Story' ? 'bg-green-100 text-green-700' : '' }}
                        {{ $result->activity->type === 'Movement' ? 'bg-yellow-100 text-yellow-700' : '' }}">
                        {{ $result->activity->type ?? 'N/A' }}
                    </span>
                </td>
                <td class="px-6 py-4 text-[#5f6873] text-sm">
                    {{ $result->activity->day_of_week ?? '' }} {{ $result->activity ? substr($result->activity->start_time, 0, 5) : '' }}
                </td>
                <td class="px-6 py-4 text-center">
                    <span class="px-4 py-2 rounded-lg text-sm font-bold
                        {{ $result->score >= 80 ? 'bg-green-100 text-green-700' : ($result->score >= 60 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                        {{ $result->score }}%
                    </span>
                </td>
                <td class="px-6 py-4 text-sm text-[#5f6873] max-w-xs">{{ $result->feedback ?? '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-6 py-12 text-center text-[#8b949d] text-lg">No activity results yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection


