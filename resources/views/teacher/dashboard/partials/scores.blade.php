{{--
    Score by category per student: heading, filters, one page of rows and the pager.
    Rendered inside the dashboard card and on its own by DashboardController::partial().
    Needs: $scoreRows, $scorePager, $scoreSection, $scoreStudent, $scoreStudents, $availableSections
--}}
@php
    $gameTypes = \App\Services\AnalyticsService::GAME_TYPES;
    $eccdDomains = \App\Services\AnalyticsService::ECCD_DOMAINS;
    $partialUrl = route('teacher.dashboard.partial', 'scores');
@endphp
<h3 class="text-lg font-semibold text-[#182438] mb-1">
    {{ $scoreStudent ? __('analytics.score_by_category') : __('analytics.score_by_category_per_student') }}
</h3>
<p class="text-xs text-[#6b7280] mb-4">
    @if($scoreStudent)
        {{ __('analytics.category_blurb_selected') }}
    @else
        {{ __('analytics.category_blurb_cohort') }}
    @endif
</p>

<form method="GET" action="{{ route('teacher.dashboard') }}" class="flex flex-wrap items-end gap-3 mb-4"
      data-partial-form data-no-loader data-partial-url="{{ $partialUrl }}" data-reset="score_page">
    {{-- Only used without JavaScript; with it the live URL is the source of truth. --}}
    @foreach(request()->except(['score_section','score_student','score_page','intervention_page']) as $key => $value)
        @if(is_scalar($value))<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif
    @endforeach
    <label class="text-xs font-semibold text-[#5f6873]">Section
        <select name="score_section" class="block mt-1 rounded-lg border border-[#d8d2c5] px-3 py-2 text-sm" onchange="this.form.score_student.value='';this.form.requestSubmit()">
            <option value="">All sections</option>
            @foreach($availableSections as $option)<option value="{{ $option }}" @selected($scoreSection === $option)>{{ \App\Models\Student::sectionLabel($option) }}</option>@endforeach
        </select>
    </label>
    <label class="text-xs font-semibold text-[#5f6873]">Individual student
        <select name="score_student" class="block mt-1 rounded-lg border border-[#d8d2c5] px-3 py-2 text-sm" onchange="this.form.requestSubmit()">
            <option value="">Show selected section</option>
            @foreach($scoreStudents as $option)<option value="{{ $option->id }}" @selected($scoreStudent && $scoreStudent->id === $option->id)>{{ $option->name }}</option>@endforeach
        </select>
    </label>
</form>

<div class="overflow-x-auto">
    <table class="w-full min-w-[980px]">
        <thead class="bg-[#fffbf0]">
            <tr>
                <th rowspan="2" class="px-3 py-2 text-left text-xs font-semibold text-[#182438] uppercase">{{ __('analytics.th_student') }}</th>
                <th rowspan="2" class="px-3 py-2 text-left text-xs font-semibold text-[#182438] uppercase">{{ __('analytics.th_section') }}</th>
                <th colspan="{{ count($gameTypes) }}" class="px-3 py-2 text-center text-xs font-semibold text-[#1982c4] uppercase">{{ __('analytics.th_game_accuracy') }}</th>
                <th colspan="{{ count($eccdDomains) }}" class="px-3 py-2 text-center text-xs font-semibold text-[#7c5cbf] uppercase">{{ __('analytics.th_eccd_mastery') }}</th>
                <th rowspan="2" class="px-3 py-2 text-center text-xs font-semibold text-[#182438] uppercase">{{ __('analytics.th_game_avg') }}</th>
                <th rowspan="2" class="px-3 py-2 text-center text-xs font-semibold text-[#182438] uppercase">{{ __('analytics.th_eccd_avg') }}</th>
                <th colspan="{{ count($gameTypes) }}" class="px-3 py-2 text-center text-xs font-semibold text-[#b8860b] uppercase">{{ __('analytics.th_points_earned') }}</th>
                <th rowspan="2" class="px-3 py-2 text-center text-xs font-semibold text-[#b8860b] uppercase">{{ __('analytics.th_total_pts') }}</th>
            </tr>
            <tr>
                @foreach($gameTypes as $game)
                    <th class="px-2 py-2 text-center text-xs font-semibold text-[#5f6873] uppercase">{{ ucfirst($game) }}</th>
                @endforeach
                @foreach($eccdDomains as $domain)
                    <th class="px-2 py-2 text-center text-xs font-semibold text-[#5f6873] uppercase" title="{{ $domain }}">{{ \App\Services\AnalyticsService::ECCD_DOMAIN_SHORT[$domain] ?? $domain }}</th>
                @endforeach
                @foreach($gameTypes as $game)
                    <th class="px-2 py-2 text-center text-xs font-semibold text-[#5f6873] uppercase">{{ ucfirst($game) }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody class="divide-y divide-[#f1e7cc]">
            @foreach($scoreRows as $row)
                <tr class="hover:bg-[#fffbf0] transition">
                    <td class="px-3 py-2.5 whitespace-nowrap font-medium text-[#182438]">{{ $row['name'] }}</td>
                    <td class="px-3 py-2.5">
                        <span class="inline-flex items-center gap-1.5 text-xs text-[#5f6873] whitespace-nowrap">
                            <span class="w-2 h-2 rounded-full" style="background-color: {{ \App\Models\Student::colorForSection($row['class_name']) }}"></span>
                            {{ \App\Models\Student::sectionLabel($row['class_name']) }}
                        </span>
                    </td>
                    @foreach($gameTypes as $game)
                        <td class="px-2 py-2.5 text-center text-sm"><x-score-cell :value="$row['games'][$game] ?? null" /></td>
                    @endforeach
                    @foreach($eccdDomains as $domain)
                        <td class="px-2 py-2.5 text-center text-sm"><x-score-cell :value="$row['domains'][$domain] ?? null" /></td>
                    @endforeach
                    <td class="px-3 py-2.5 text-center text-sm"><x-score-cell :value="$row['game_overall']" /></td>
                    <td class="px-3 py-2.5 text-center text-sm"><x-score-cell :value="$row['domain_overall']" /></td>
                    @foreach($gameTypes as $game)
                        <td class="px-2 py-2.5 text-center text-sm text-[#5f6873]">
                            @if(isset($row['points'][$game]))
                                <span>{{ number_format($row['points'][$game]) }}</span>
                            @else
                                <span class="text-[#6b7280]" title="{{ __('analytics.no_points_yet') }}">&mdash;</span>
                            @endif
                        </td>
                    @endforeach
                    <td class="px-3 py-2.5 text-center"><span class="font-bold text-[#b8860b]">{{ number_format($row['total_points']) }}</span></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<x-dash-pager :paginator="$scorePager" param="score_page" :url="$partialUrl" label="students" />

<p class="text-xs text-[#6b7280] mt-3">
    <span class="text-[#8ac926] font-bold">&#8805;75%</span> {{ __('analytics.legend_on_track') }} &middot;
    <span class="text-[#ffca3a] font-bold">50&ndash;74%</span> {{ __('analytics.legend_needs_practice') }} &middot;
    <span class="text-[#ff595e] font-bold">&lt;50%</span> {{ __('analytics.legend_priority') }}
</p>
