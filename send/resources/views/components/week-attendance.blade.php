@props(['days', 'unit' => 'day'])

@php
    $days = collect($days);
    $todayKey = \Carbon\Carbon::today()->toDateString();
    $sumPresent = $days->sum('present');
    $sumLate = $days->sum('late');
    $sumAbsent = $days->sum('absent');
    $sumMarks = $sumPresent + $sumLate + $sumAbsent;
    $sumRate = $sumMarks > 0 ? (int) round((($sumPresent + $sumLate) / $sumMarks) * 100) : null;
@endphp

<div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="text-left text-[11px] font-semibold uppercase tracking-wide text-[#8b949d]">
                <th class="py-2 pr-3">{{ $unit === 'week' ? __('analytics.period_column_week') : __('analytics.week_day') }}</th>
                <th class="py-2 px-2 text-center">{{ __('analytics.week_present') }}</th>
                <th class="py-2 px-2 text-center">{{ __('analytics.week_late') }}</th>
                <th class="py-2 px-2 text-center">{{ __('analytics.week_absent') }}</th>
                <th class="py-2 pl-3 w-40">{{ __('analytics.week_rate') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-[#ebe8e1]">
            @foreach($days as $day)
                @php
                    $marks = $day['present'] + $day['late'] + $day['absent'];
                    $rate = $marks > 0 ? (int) round((($day['present'] + $day['late']) / $marks) * 100) : null;
                    $isToday = $day['date'] <= $todayKey && ($day['end'] ?? $day['date']) >= $todayKey;
                @endphp
                <tr class="{{ $isToday ? 'bg-[#fff6f0]' : '' }}">
                    <td class="py-2 pr-3 font-medium text-[#182438] whitespace-nowrap">
                        {{ $day['day'] }}
                        @if($isToday)<span class="ml-1 px-1.5 py-0.5 rounded bg-[#ff8555] text-white text-[10px] font-semibold">{{ __('analytics.week_today') }}</span>@endif
                    </td>
                    <td class="py-2 px-2 text-center font-semibold text-[#68b82f]">{{ $marks ? $day['present'] : '–' }}</td>
                    <td class="py-2 px-2 text-center font-semibold text-[#d9b348]">{{ $marks ? $day['late'] : '–' }}</td>
                    <td class="py-2 px-2 text-center font-semibold text-[#f05d56]">{{ $marks ? $day['absent'] : '–' }}</td>
                    <td class="py-2 pl-3">
                        @if($rate === null)
                            <span class="text-xs text-[#8b949d]">{{ __('analytics.week_none') }}</span>
                        @else
                            <div class="flex items-center gap-2">
                                <div class="flex-1 h-2 rounded-full bg-[#e6ebf3] overflow-hidden">
                                    <div class="h-2 rounded-full {{ $rate >= 85 ? 'bg-[#68b82f]' : ($rate >= 60 ? 'bg-[#d9b348]' : 'bg-[#f05d56]') }}" style="width: {{ $rate }}%"></div>
                                </div>
                                <span class="w-10 text-right text-xs font-semibold text-[#182438]">{{ $rate }}%</span>
                            </div>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="border-t-2 border-[#ebe8e1] font-bold text-[#182438]">
                <td class="py-2 pr-3">{{ __('analytics.week_total') }}</td>
                <td class="py-2 px-2 text-center text-[#68b82f]">{{ $sumPresent }}</td>
                <td class="py-2 px-2 text-center text-[#d9b348]">{{ $sumLate }}</td>
                <td class="py-2 px-2 text-center text-[#f05d56]">{{ $sumAbsent }}</td>
                <td class="py-2 pl-3 text-right text-xs">{{ $sumRate === null ? '' : $sumRate.'%' }}</td>
            </tr>
        </tfoot>
    </table>
</div>
<p class="mt-3 text-xs text-[#5f6873]">
    @if($sumRate === null)
        {{ __('analytics.week_empty') }}
    @else
        {{ __('analytics.week_summary', ['rate' => $sumRate, 'attended' => $sumPresent + $sumLate, 'total' => $sumMarks]) }}
    @endif
</p>
