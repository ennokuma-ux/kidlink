@extends('layouts.teacher')

@section('title', __('teacher.participation_title'))
@section('page-title', __('teacher.participation_title'))

@section('content')
<div class="mb-6">
    <a href="{{ route('teacher.activities.index', ['week' => $activity->week_start ? \Illuminate\Support\Carbon::parse($activity->week_start)->startOfWeek(\Illuminate\Support\Carbon::MONDAY)->toDateString() : null]) }}"
       class="text-sm text-[#ff8555] hover:text-[#f47548] font-medium">&larr; {{ __('teacher.back_to_planner') }}</a>
</div>

<div class="bg-white rounded-xl shadow-sm p-5 mb-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h2 class="text-lg font-bold text-[#182438]">{{ $activity->title }}</h2>
            <p class="text-sm text-[#5f6873] mt-1">
                {{ $activity->type }} &middot; {{ $activity->day_of_week }}
                {{ substr($activity->start_time, 0, 5) }} - {{ substr($activity->end_time, 0, 5) }}
                @if($activity->group_name) &middot; {{ $activity->group_name }} @endif
            </p>
        </div>
        <div class="flex gap-4 text-center">
            <div>
                <p class="text-xl font-bold text-[#182438]">{{ $stats['participating'] }}</p>
                <p class="text-xs text-[#5f6873]">{{ __('teacher.participating') }}</p>
            </div>
            <div>
                <p class="text-xl font-bold text-[#1982c4]">{{ $stats['scored'] }}</p>
                <p class="text-xs text-[#5f6873]">{{ __('teacher.scored') }}</p>
            </div>
            <div>
                <p class="text-xl font-bold text-[#8ac926]">{{ $stats['average'] !== null ? $stats['average'] : '--' }}</p>
                <p class="text-xs text-[#5f6873]">{{ __('teacher.average') }}</p>
            </div>
        </div>
    </div>
</div>

@if ($students->isEmpty())
    <div class="bg-white rounded-xl shadow-sm p-8 text-center">
        <p class="text-[#5f6873]">{{ __('teacher.no_students_to_record') }}</p>
    </div>
@else
    @if ($errors->any())
        <div class="mb-4 p-3 rounded-lg bg-[#fff1f1] border border-[#ffc9cb] text-sm text-[#c9343a]">
            <ul class="list-disc pl-4 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('teacher.activities.participation.save', $activity) }}">
        @csrf @method('PUT')
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-[#fffbf0] text-left">
                    <tr>
                        <th class="px-4 py-3 font-semibold text-[#182438]">{{ __('teacher.took_part') }}</th>
                        <th class="px-4 py-3 font-semibold text-[#182438]">{{ __('teacher.student') }}</th>
                        <th class="px-4 py-3 font-semibold text-[#182438] w-28">{{ __('teacher.score') }}</th>
                        <th class="px-4 py-3 font-semibold text-[#182438]">{{ __('teacher.feedback') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $student)
                        @php($record = $records->get($student->id))
                        <tr class="border-t border-[#f1e7cc] align-top">
                            <td class="px-4 py-3">
                                <input type="checkbox" name="participating[]" value="{{ $student->id }}"
                                       class="participation-toggle w-5 h-5 rounded border-[#f1e7cc] text-[#ff8555] focus:ring-[#ff8555]"
                                       @checked($record)>
                            </td>
                            <td class="px-4 py-3">
                                <span class="font-medium text-[#182438]">{{ $student->first_name }} {{ $student->last_name }}</span>
                                @if($student->class_name)
                                    <span class="block text-xs text-[#8b949d]">{{ $student->class_name }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <input type="number" name="score[{{ $student->id }}]" min="0" max="100"
                                       value="{{ old('score.'.$student->id, $record?->score) }}"
                                       data-score class="participation-field w-20 px-2 py-1.5 border border-[#f1e7cc] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none disabled:bg-[#f5f4f1] disabled:text-[#b8b4ab]">
                            </td>
                            <td class="px-4 py-3">
                                <input type="text" name="feedback[{{ $student->id }}]" maxlength="1000"
                                       value="{{ old('feedback.'.$student->id, $record?->feedback) }}"
                                       data-feedback placeholder="{{ __('teacher.optional_note') }}"
                                       class="participation-field w-full px-2 py-1.5 border border-[#f1e7cc] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none disabled:bg-[#f5f4f1] disabled:text-[#b8b4ab]">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4 flex items-center justify-between">
            <p class="text-xs text-[#8b949d]">{{ __('teacher.unchecking_removes_record') }}</p>
            <button type="submit" class="px-6 py-2.5 bg-[#ff8555] text-white rounded-lg hover:bg-[#f47548] transition font-medium">{{ __('teacher.save_participation') }}</button>
        </div>
    </form>
@endif
@endsection

@push('scripts')
<script>
// A student who did not take part cannot carry a score or a note, so grey the
// two inputs out rather than accepting values that would be discarded on save.
function syncParticipationFields() {
    document.querySelectorAll('input[name="participating[]"]').forEach(toggle => {
        const row = toggle.closest('tr');
        row.querySelectorAll('[data-score], [data-feedback]').forEach(field => {
            field.disabled = !toggle.checked;
        });
    });
}

document.querySelectorAll('input[name="participating[]"]').forEach(toggle => {
    toggle.addEventListener('change', syncParticipationFields);
});

syncParticipationFields();
</script>
@endpush
