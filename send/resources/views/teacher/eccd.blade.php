@extends('layouts.teacher')

@section('title', __('teacher.eccd_title'))
@section('page-title', __('teacher.eccd_title'))

@section('content')
<div class="mb-6 bg-white rounded-xl shadow-sm p-3">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h3 class="text-lg font-semibold text-[#182438]">{{ __('teacher.eccd_heading') }}</h3>
            <p class="text-sm text-[#5f6873] mt-1">{{ __('teacher.eccd_administer_note') }} <span class="font-medium text-[#ff8555]">{{ __('teacher.evaluation_number') }}{{ $evaluationNumber }}</span></p>
        </div>
        <x-student-chooser :students="$students" :selected="$selectedStudent"
                           :action="route('teacher.eccd.index')" param="student_id"
                           :extra="['evaluation' => $evaluationNumber]" />
    </div>
</div>

@if($selectedStudent)
<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
    <div class="bg-white rounded-xl shadow-sm p-3 border-l-4 border-[#ff8555]">
        <p class="text-[#5f6873] text-sm">{{ __('teacher.child_name') }}</p>
        <p class="text-base font-bold text-[#182438]">{{ $selectedStudent->name }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-3 border-l-4 border-[#258cc5]">
        <p class="text-[#5f6873] text-sm">{{ __('teacher.age') }}</p>
        <p class="text-base font-bold text-[#182438]">{{ $selectedStudent->age }} {{ __('teacher.yrs') }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-3 border-l-4 border-[#68b82f]">
        <p class="text-[#5f6873] text-sm">{{ __('teacher.parent') }}</p>
        <p class="text-base font-bold text-[#182438]">{{ $selectedStudent->parent->full_name ?? '—' }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-3 border-l-4 border-[#d9b348]">
        <p class="text-[#5f6873] text-sm">{{ __('teacher.overall_progress') }}</p>
        <p class="text-base font-bold text-[#182438]">
            {{ $records->flatten()->count() > 0 ? round($records->flatten()->where('status','Achieved')->count() / max($records->flatten()->count(),1) * 100) : 0 }}%
        </p>
    </div>
</div>

<div class="flex flex-wrap gap-2 mb-4" aria-label="Filter ECCD evaluation domains">
    <button type="button" data-eccd-filter="all" aria-pressed="true" class="eccd-filter px-3 py-1.5 rounded-full border border-[#ff8555] bg-[#ff8555] text-white text-sm font-medium">All domains</button>
    @foreach($domains as $domain)
    <button type="button" data-eccd-filter="{{ \Illuminate\Support\Str::slug($domain) }}" aria-pressed="false"
       class="eccd-filter px-3 py-1.5 rounded-full border border-[#ebe8e1] bg-white text-sm font-medium text-[#182438] hover:border-[#ff8555] hover:text-[#f47548]">
        {{ $labels['domains'][$domain] ?? $domain }}
    </button>
    @endforeach
</div>

<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3 mb-4">
    @foreach($domains as $domain)
    <div class="bg-white rounded-xl shadow-sm p-3">
        <div class="flex items-center justify-between mb-3">
            <h4 class="font-semibold text-[#182438]">{{ $labels['domains'][$domain] ?? $domain }}</h4>
            <span class="text-sm font-bold text-[#ff8555]">{{ $scores[$domain] ?? 0 }}%</span>
        </div>
        <div class="w-full bg-[#ebe8e1] rounded-full h-1.5 mb-2">
            <div class="bg-gradient-to-r from-[#ff8555] to-[#f47548] h-1.5 rounded-full" style="width: {{ min($scores[$domain] ?? 0, 100) }}%"></div>
        </div>
        <p class="text-xs text-[#5f6873]">{{ __('teacher.expected') }} {{ $expectedScores[$domain] ?? 0 }}%</p>
    </div>
    @endforeach
</div>

@foreach($domains as $domain)
@php $domainRecords = $records->get($domain, collect()); @endphp
<div data-eccd-domain="{{ \Illuminate\Support\Str::slug($domain) }}" class="bg-white rounded-xl shadow-sm overflow-hidden mb-4 scroll-mt-24">
    <div class="px-4 py-2 bg-[#182438] text-white flex items-center justify-between">
        <div>
            <h3 class="font-semibold">{{ $labels['domains'][$domain] ?? $domain }}</h3>
            <p class="text-xs text-white/60">{{ __('teacher.evaluation_number') }}{{ $evaluationNumber }}</p>
        </div>
        <div class="text-right">
            <p class="text-xs text-white/60">{{ __('teacher.score') }}</p>
            <p class="text-base font-bold">{{ $scores[$domain] ?? 0 }}%</p>
        </div>
    </div>
    <table class="w-full">
        <thead class="bg-[#faf8f3]">
            <tr>
                <th class="px-4 py-2 text-left text-xs font-semibold text-[#5f6873] w-10">#</th>
                <th class="px-4 py-2 text-left text-xs font-semibold text-[#5f6873]">{{ __('teacher.indicator') }}</th>
                <th class="px-4 py-2 text-center text-xs font-semibold text-[#5f6873] w-56">{{ __('teacher.status') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-[#ebe8e1]">
            @forelse($domainRecords as $i => $record)
            <tr class="hover:bg-[#faf8f3] transition">
                <td class="px-4 py-2 text-[#5f6873]">{{ $i + 1 }}</td>
                <td class="px-4 py-2 text-sm font-medium text-[#182438]">{{ $labels['indicators'][$record->indicator] ?? $record->indicator }}</td>
                <td class="px-4 py-2">
                    <form method="POST" action="{{ route('teacher.eccd.update') }}" class="flex items-center gap-2">
                        @csrf
                        <input type="hidden" name="student_id" value="{{ $selectedStudent->id }}">
                        <input type="hidden" name="record_id" value="{{ $record->id }}">
                        <input type="hidden" name="evaluation" value="{{ $evaluationNumber }}">
                        @foreach(['Not Yet','In Progress','Achieved'] as $status)
                        <button type="submit" name="status" value="{{ $status }}"
                            class="px-2.5 py-1 rounded-full text-[11px] font-medium transition
                            {{ $record->status === $status
                                ? ($status === 'Achieved' ? 'bg-[#68b82f] text-white' : ($status === 'In Progress' ? 'bg-[#d9b348] text-white' : 'bg-[#f05d56] text-white'))
                                : 'bg-[#ebe8e1] text-[#5f6873] hover:bg-[#e0dcd3]' }}">
                            {{ ['Not Yet' => __('teacher.status_not_yet'), 'In Progress' => __('teacher.status_in_progress'), 'Achieved' => __('teacher.status_achieved')][$status] }}
                        </button>
                        @endforeach
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="3" class="px-6 py-8 text-center text-[#5f6873]">{{ __('teacher.no_records_domain') }}</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endforeach

@if(count($recommendations) > 0)
<div class="bg-white rounded-xl shadow-sm p-3">
    <h3 class="text-lg font-semibold text-[#182438] mb-4">{{ __('teacher.expert_recommendations') }}</h3>
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
@elseif($students->isNotEmpty())
<x-student-chooser-prompt />
@else
<div class="bg-white rounded-xl shadow-sm p-10 text-center">
    <p class="text-[#5f6873]">{{ __('teacher.no_students_assigned') }}</p>
    <a href="{{ route('teacher.accounts.index') }}" class="inline-block mt-4 bg-[#ff8555] hover:bg-[#f47548] text-white px-6 py-2.5 rounded-lg transition font-medium">{{ __('teacher.go_to_accounts') }}</a>
</div>
@endif
@endsection
@push('scripts')
<script>
document.querySelectorAll('[data-eccd-filter]').forEach(button => {
    button.addEventListener('click', () => {
        const selected = button.dataset.eccdFilter;
        document.querySelectorAll('[data-eccd-domain]').forEach(section => {
            section.hidden = selected !== 'all' && section.dataset.eccdDomain !== selected;
        });
        document.querySelectorAll('[data-eccd-filter]').forEach(item => {
            const active = item === button;
            item.setAttribute('aria-pressed', active ? 'true' : 'false');
            item.classList.toggle('bg-[#ff8555]', active);
            item.classList.toggle('text-white', active);
            item.classList.toggle('border-[#ff8555]', active);
            item.classList.toggle('bg-white', !active);
        });
    });
});
</script>
@endpush
