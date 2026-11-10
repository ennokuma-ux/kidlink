@props([
    'interventions' => [],
    'heading' => null,
])

{{--
    A student who is missing attendance records, or who has no scores at all for
    a topic, looks identical to a student doing fine once the numbers are
    averaged. Nothing in a chart of averages can show an unrecorded register, so
    it gets stated outright, above the headline stats.

    Expects $interventions rows from AnalyticsService::interventionFlags():
      student_id, name, class_name, intervention_required, severity, gap_count, reasons[]
    Ordered most urgent first by the service. Every returned row needs action;
    $severity is what decides who gets picked up first.
--}}

<div class="rounded-xl border-2 mb-8
    {{ $interventions->isEmpty() ? 'bg-white border-[#b8e0c2]' : 'bg-[#fff5f7] border-[#ff595e]' }}">
    <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-4 border-b
        {{ $interventions->isEmpty() ? 'border-[#f1e7cc]' : 'border-[#f5c6cf]' }}">
        <div class="flex items-center gap-3">
            @if($interventions->isEmpty())
                <span class="w-9 h-9 rounded-full bg-[#ecf7d6] text-[#8ac926] flex items-center justify-center text-lg font-bold" aria-hidden="true">&check;</span>
            @else
                <span class="w-9 h-9 rounded-full bg-[#ff595e] text-white flex items-center justify-center font-bold" aria-hidden="true">!</span>
            @endif
            <div>
                <h3 class="text-lg font-semibold text-[#182438]">{{ $heading ?: __('intervention.heading') }}</h3>
                <p class="text-xs text-[#6b7280]">
                    {{ __('intervention.subtitle', ['days' => \App\Services\AnalyticsService::ATTENDANCE_WINDOW_DAYS]) }}
                    
                </p>
            </div>
        </div>
        @if($interventions->total() > 0)
            <span class="px-3 py-1.5 rounded-full bg-[#ff595e] text-white text-xs font-bold uppercase tracking-wide">
                {{ trans_choice('intervention.count', $interventions->total(), ['count' => $interventions->total()]) }}
                
            </span>
        @endif
    </div>

    @if($interventions->isEmpty())
        <div class="px-6 py-8 text-center">
            <p class="text-[#182438] font-semibold">{{ __('intervention.nothing_missing') }}</p>
            <p class="text-sm text-[#5f6873] mt-1">
                {{ __('intervention.all_clear') }}
            </p>
        </div>
    @else
        @if($interventions->hasPages())
            @include('components.intervention-pager', ['interventions' => $interventions, 'position' => 'top'])
        @endif
        <div class="divide-y divide-[#f1e7cc]">
            @foreach ($interventions as $flag)
                <div class="px-6 py-4">
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                        <div class="flex items-center gap-2">
                            <h4 class="font-semibold text-[#182438]">{{ $flag['name'] }}</h4>
                            <span class="inline-flex items-center gap-1.5 text-xs text-[#5f6873]">
                                <span class="w-2 h-2 rounded-full"
                                      style="background-color: {{ \App\Models\Student::colorForSection($flag['class_name']) }}"></span>
                                {{ \App\Models\Student::sectionLabel($flag['class_name']) }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold uppercase tracking-wide px-2 py-0.5 rounded-full
                                {{ $flag['severity'] === 'high' ? 'bg-[#ff595e] text-white' : ($flag['severity'] === 'medium' ? 'bg-[#ffca3a] text-[#182438]' : 'bg-[#8ac926] text-[#182438]') }}">
                                {{ __('intervention.severity_' . $flag['severity']) }}
                            </span>
                            <span class="text-xs font-bold uppercase tracking-wide px-2 py-0.5 rounded-full bg-[#ff595e] text-white">
                                {{ __('intervention.required') }}
                            </span>
                        </div>
                    </div>

                    <ul class="space-y-2 mt-2">
                        @foreach ($flag['reasons'] as $reason)
                            <li class="flex flex-col sm:flex-row sm:items-start gap-1 sm:gap-3">
                                <span class="shrink-0 text-xs font-bold uppercase tracking-wide px-2 py-0.5 rounded-full mt-0.5
                                    {{ $reason['type'] === 'Attendance' ? 'bg-[#fde8ef] text-[#ff595e]' : ($reason['type'] === 'Baseline' ? 'bg-[#fff6e5] text-[#a16207]' : 'bg-[#eef3f9] text-[#1982c4]') }}">
                                    {{ __('intervention.type_' . strtolower($reason['type'])) }}
                                </span>
                                <span class="text-sm text-[#5f6873]">
                                    <span class="font-medium text-[#182438]">{{ $reason['label'] }}.</span>
                                    {{ $reason['message'] }}
                                    <span class="block text-xs text-[#6b7280] mt-0.5">{{ __('intervention.do') }} {{ $reason['action'] }}</span>
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
        @if($interventions->hasPages())
            @include('components.intervention-pager', ['interventions' => $interventions, 'position' => 'bottom'])
        @elseif($interventions->total() > 0)
            <div class="px-5 py-3 border-t border-[#f5c6cf]">
                <p class="text-xs text-[#6b7280]">Showing all {{ $interventions->total() }} interventions</p>
            </div>
        @endif
    @endif
</div>