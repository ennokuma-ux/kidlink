{{-- Level progression result. Rendered inside the card and on its own by DashboardController::partial(). --}}
<p class="text-xs text-[#6b7280] mb-4">
    @if($levelStudent)
        {{ __('analytics.lpm_subtitle_selected', ['name' => $levelStudent->first_name]) }}
    @else
        {{ __('analytics.lpm_subtitle_cohort') }}
    @endif
</p>
@if($levelStudent)
<div class="overflow-x-auto">
    <table class="w-full min-w-[720px]">
        <thead class="bg-[#fffbf0]">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-[#182438] uppercase">{{ __('analytics.th_game') }}</th>
                @for($l = 1; $l <= 8; $l++)
                    <th class="px-3 py-3 text-center text-xs font-semibold text-[#182438] uppercase">L{{ $l }}</th>
                @endfor
            </tr>
        </thead>
        <tbody class="divide-y divide-[#f1e7cc]">
            @foreach($levelProgress as $game => $levels)
            <tr class="hover:bg-[#fffbf0] transition">
                <td class="px-4 py-3 font-medium text-[#182438] capitalize">{{ $game }}</td>
                @for($l = 1; $l <= 8; $l++)
                    <td class="px-3 py-3 text-center">
                        @php $count = $levels[$l] ?? 0; @endphp
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-xs font-bold
                            {{ $count === 0 ? 'bg-[#f4f2ec] text-[#c9c4b8]' : ($count >= 5 ? 'bg-[#8ac926] text-[#182438]' : 'bg-[#ecf7d6] text-[#3d7a1c]') }}">
                            {{ $count }}
                        </span>
                    </td>
                @endfor
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@else
    <p class="py-8 text-center text-sm text-[#6b7280]">Select a student to view individual level progression.</p>
@endif
