@extends('layouts.teacher')

@section('title', __('page.dashboard'))
@section('page-title', __('page.teacher_dashboard'))

@section('content')
{{-- Charts size to the screen, not to the card's width: a full-width chart used to
     grow as tall as it was wide. Chart.js fills this box (maintainAspectRatio is off). --}}
<style>
    /* Padding keeps axis labels and legends off the card edge; the cards sit a full gap apart. */
    .chart-box { position: relative; box-sizing: border-box; width: 100%; max-width: 100%; min-width: 0; overflow: hidden; padding: 0.5rem 0.5rem 0.75rem; height: clamp(210px, 32vh, 340px); }
    .chart-box > canvas { max-width: 100%; }
    .chart-box--tall { height: clamp(250px, 40vh, 420px); }
    /* A card that is reloading on its own (dash-partials.js) dims instead of blanking the page. */
    [data-partial].is-loading, [data-partial-body].is-loading { opacity: .55; pointer-events: none; transition: opacity .15s; }
</style>

@unless($scope === 'individual')
    @include('teacher.partials.headline-cards')
@endunless

@php $teacherScopeStudent = $selectedStudent; @endphp
<x-analytics-scope-bar route="teacher.dashboard" :scope="$scope" :section="$section" :student="$teacherScopeStudent"
                       :sort="$sort" :shown="count($students)" :total="$totalStudents" :legend="$sectionLegend" />

{{-- Narrative summary, written from the live numbers --}}
<div class="bg-gradient-to-r from-[#fff0e6] to-[#fde8ef] border border-[#ffd9c2] rounded-xl p-5 mb-6">
    <h3 class="text-sm font-bold text-[#ff8555] uppercase tracking-wide mb-2">
        {{ $selectedStudent ? __('analytics.heading_student_summary') : __('analytics.heading_cohort_summary') }}
    </h3>
    <p class="text-[#5f6873] leading-relaxed">
        @if($selectedStudent)
            @if($summary['students'] > 0)
                @php
                    $studentAgeWord = trans_choice('analytics.year_word', $selectedStudent->age);
                    $studentGameWord = trans_choice('analytics.game_level_word', $summary['games_played']);
                @endphp
                {{ __('analytics.student_narr', [
                    'name' => $selectedStudent->name,
                    'section' => \App\Models\Student::sectionLabel($selectedStudent->class_name),
                    'age' => $selectedStudent->age,
                    'ageword' => $studentAgeWord,
                    'games' => $summary['games_played'],
                    'gamelevels' => $studentGameWord,
                    'pct' => $summary['avg_game_pct'],
                    'points' => number_format($summary['points_awarded']),
                    'rate' => $summary['attendance_rate'],
                ]) }}
                @if($summary['absences'] > 0 && $summary['late_count'] > 0)
                    {{ __('analytics.attclause_both', [
                        'abs' => $summary['absences'],
                        'absencesword' => trans_choice('analytics.absence_word', $summary['absences']),
                        'late' => $summary['late_count'],
                        'arrivalsword' => trans_choice('analytics.arrival_word', $summary['late_count']),
                    ]) }}
                @elseif($summary['absences'] > 0)
                    {{ __('analytics.attclause_abs', [
                        'abs' => $summary['absences'],
                        'absencesword' => trans_choice('analytics.absence_word', $summary['absences']),
                    ]) }}
                @else
                    {{ __('analytics.attclause_none') }}
                @endif
                @if($studentInterventions && collect($studentInterventions)->contains(fn ($f) => $f['intervention_required']))
                    {{ __('analytics.intervention_required', ['reasons' => collect(collect($studentInterventions)->firstWhere('intervention_required', true)['reasons'])->pluck('label')->implode('; ')]) }}
                @elseif($recommendations)
                    {{ __('analytics.intervention_recommended', ['needs' => $recommendations[0]['needs'], 'plan' => $recommendations[0]['plan']]) }}
                @else
                    {{ __('analytics.no_interventions_now') }}
                @endif
            @else
                {{ __('analytics.no_records_yet') }}
            @endif
        @elseif($summary['students'] > 0)
            @php
                $cohortStudWord = trans_choice('analytics.student_word', $summary['students']);
                $cohortActWord = trans_choice('analytics.active_word', $summary['active_students']);
                $cohortGameWord = trans_choice('analytics.game_level_word', $summary['games_played']);
            @endphp
            {{ __('analytics.cohort_head', [
                'students' => $summary['students'],
                'studword' => $cohortStudWord,
                'active' => $summary['active_students'],
                'actword' => $cohortActWord,
                'games' => $summary['games_played'],
                'gamelevels' => $cohortGameWord,
                'pct' => $summary['avg_game_pct'],
                'rate' => $summary['attendance_rate'],
            ]) }}
            @if($summary['absences'] > 0 && $summary['late_count'] > 0)
                {{ __('analytics.attclause_both', [
                    'abs' => $summary['absences'],
                    'absencesword' => trans_choice('analytics.absence_word', $summary['absences']),
                    'late' => $summary['late_count'],
                    'arrivalsword' => trans_choice('analytics.arrival_word', $summary['late_count']),
                ]) }}
            @elseif($summary['absences'] > 0)
                {{ __('analytics.attclause_abs', [
                    'abs' => $summary['absences'],
                    'absencesword' => trans_choice('analytics.absence_word', $summary['absences']),
                ]) }}
            @else
                {{ __('analytics.attclause_none') }}
            @endif
            @if($interventionCount > 0)
                {{ trans_choice('analytics.cohort_interventions', $interventionCount, ['count' => $interventionCount]) }}
            @elseif($recommendations)
                {{ trans_choice('analytics.cohort_recommended', count($recommendations), ['count' => count($recommendations)]) }}
            @else
                {{ __('analytics.cohort_none') }}
            @endif
        @else
            {{ __('analytics.no_students_yet') }}
        @endif
    </p>
</div>

{{-- Stated outright, above the stats: a missing record cannot be seen in an average --}}
<x-intervention-panel :interventions="$interventions" />

{{-- Headline stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-[#1982c4]">
        <p class="text-[#5f6873] text-sm">{{ __('analytics.stat_active_students') }}</p>
        <p class="text-3xl font-bold text-[#182438]">{{ $summary['active_students'] }}</p>
        <p class="text-xs text-[#6b7280] mt-1">{{ __('analytics.stat_of_enrolled', ['count' => $summary['students']]) }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-[#8ac926]">
        <p class="text-[#5f6873] text-sm">{{ __('analytics.stat_avg_game_accuracy') }}</p>
        <p class="text-3xl font-bold text-[#182438]">{{ $summary['avg_game_pct'] }}%</p>
        <p class="text-xs text-[#6b7280] mt-1">{{ __('analytics.stat_levels_played', ['count' => $summary['games_played']]) }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-[#ff8555]">
        <p class="text-[#5f6873] text-sm">{{ __('analytics.stat_attendance_rate') }}</p>
        <p class="text-3xl font-bold text-[#182438]">{{ $summary['attendance_rate'] }}%</p>
        <p class="text-xs text-[#6b7280] mt-1">{{ __('analytics.stat_late_arrivals', ['count' => $summary['late_count']]) }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-[#ffca3a]">
        <p class="text-[#5f6873] text-sm">{{ __('analytics.stat_points_awarded') }}</p>
        <p class="text-3xl font-bold text-[#182438]">{{ number_format($summary['points_awarded']) }}</p>
        <p class="text-xs text-[#6b7280] mt-1">{{ __('analytics.stat_all_time') }}</p>
    </div>
</div>

{{-- Attendance --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-4">
        <h3 class="text-lg font-semibold text-[#182438] mb-1">{{ __('analytics.attendance_trend_title') }}</h3>
        <p class="text-xs text-[#6b7280] mb-4">{{ __('analytics.attendance_trend_subtitle') }}</p>
        <div class="chart-box"><canvas id="attendanceTrendChart"></canvas></div>

        {{-- Per-student attendance, same window as the chart. Every row is in the page and
             paged in the browser (public/js/dash-partials.js), so turning a page costs nothing. --}}
        @php
            $attPerPage = 5;
            $attCount = count($attendanceStudents);
            $attMin = \App\Services\AnalyticsService::ATTENDANCE_MIN_RATE;
        @endphp
        <div class="mt-5 pt-4 border-t border-[#f1e7cc]" data-client-pager data-per-page="{{ $attPerPage }}" data-label="students">
            <div class="flex flex-wrap items-baseline justify-between gap-x-3 mb-2">
                <h4 class="text-sm font-semibold text-[#182438]">Attendance by student</h4>
                <span class="text-xs text-[#6b7280]">Last 6 months &middot; below {{ $attMin }}% needs a look</span>
            </div>
            @if($attCount > 0)
                <ul class="divide-y divide-[#f1e7cc]">
                    @foreach($attendanceStudents as $i => $att)
                        @php
                            $rate = $att['rate'];
                            $rateColor = $rate === null ? '#9aa1ab' : ($rate >= 90 ? '#8ac926' : ($rate >= $attMin ? '#ffca3a' : '#ff595e'));
                        @endphp
                        <li data-pager-row>
                            <div class="flex items-center gap-3 py-2.5">
                                <span class="w-2 h-2 rounded-full shrink-0" style="background-color: {{ \App\Models\Student::colorForSection($att['class_name']) }}" title="{{ \App\Models\Student::sectionLabel($att['class_name']) }}"></span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-[#182438] truncate">{{ $att['name'] }}</p>
                                    <p class="text-xs text-[#6b7280]">
                                        @if($att['recorded'] > 0)
                                            {{ $att['present'] }} present &middot; {{ $att['late'] }} late &middot; {{ $att['absent'] }} absent
                                            @if(!empty($att['last_absent']))
                                                &middot; last absent {{ $att['last_absent'] }}
                                            @endif
                                        @else
                                            No attendance recorded yet
                                        @endif
                                    </p>
                                </div>
                                <div class="w-20 shrink-0 text-right">
                                    @if($rate !== null)
                                        <span class="text-sm font-bold" style="color: {{ $rateColor }}">{{ $rate }}%</span>
                                        <div class="h-1.5 rounded-full bg-[#f4f2ec] mt-1" role="img" aria-label="{{ $rate }}% attendance"><div class="h-1.5 rounded-full" style="width: {{ $rate }}%; background-color: {{ $rateColor }}"></div></div>
                                    @else
                                        <span class="text-xs text-[#6b7280]">&mdash;</span>
                                    @endif
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
                @if($attCount > $attPerPage)
                    <nav data-pager-nav class="mt-3 flex flex-wrap items-center justify-between gap-2" aria-label="Attendance by student pages"></nav>
                @endif
            @else
                <p class="text-sm text-[#6b7280] py-4 text-center">No students to show.</p>
            @endif
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4">
        <x-attendance-period-header :period="$attendancePeriod" :keep="$attendanceKeep" />
        <div class="chart-box"><canvas id="weeklyBreakdownChart"></canvas></div>
        <div class="mt-5 pt-4 border-t border-[#f1e7cc]">
            <x-week-attendance :days="$weeklyBreakdown" :unit="$attendancePeriod['mode'] === 'month' ? 'week' : 'day'" />
        </div>
    </div>
</div>

{{-- Developmental domains: ECCD charts only make sense for one student --}}
@if($selectedStudent)
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-4">
        <h3 class="text-lg font-semibold text-[#182438] mb-1">{{ __('analytics.domain_mastery_title') }}</h3>
        <p class="text-xs text-[#6b7280] mb-4">
            {{ __('analytics.domain_mastery_subtitle_teacher', ['who' => __('analytics.domain_who_name', ['name' => $selectedStudent->first_name])]) }}
        </p>
        <div class="chart-box"><canvas id="performanceRadarChart"></canvas></div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4">
        <h3 class="text-lg font-semibold text-[#182438] mb-1">{{ __('analytics.indicator_status_title') }}</h3>
        <p class="text-xs text-[#6b7280] mb-4">{{ __('analytics.indicator_status_subtitle') }}</p>
        <div class="chart-box"><canvas id="domainStatusChart"></canvas></div>
    </div>
</div>
@else
{{-- Group view: group charts only (the ECCD charts above belong to one child). --}}
<div class="grid grid-cols-1 gap-5 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-4">
        <h3 class="text-lg font-semibold text-[#182438] mb-1">{{ __('analytics.age_dist_title') }}</h3>
        <p class="text-xs text-[#6b7280] mb-4">{{ __('analytics.age_dist_subtitle_cohort') }}</p>
        <div class="chart-box"><canvas id="ageDistChart"></canvas></div>
    </div>
</div>
@endif

{{-- Game performance --}}
<div class="bg-white rounded-xl shadow-sm p-4 mb-8">
    <h3 class="text-lg font-semibold text-[#182438] mb-1">{{ __('analytics.game_perf_breakdown') }}</h3>
    <p class="text-xs text-[#6b7280] mb-4">{{ __('analytics.gp_subtitle') }}</p>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[640px]">
            <thead class="bg-[#fffbf0]">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-[#182438] uppercase">{{ __('analytics.th_game') }}</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-[#182438] uppercase">{{ __('analytics.th_levels_played') }}</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-[#182438] uppercase">{{ __('analytics.th_avg_accuracy') }}</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-[#182438] uppercase">{{ __('analytics.th_highest_level') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-[#182438] uppercase">{{ __('analytics.th_interpretation') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#f1e7cc]">
                @forelse($gamePerformance as $game => $stats)
                <tr class="hover:bg-[#fffbf0] transition">
                    <td class="px-4 py-3 font-medium text-[#182438] capitalize">{{ $game }}</td>
                    @if($stats)
                        <td class="px-4 py-3 text-center text-[#5f6873]">{{ $stats['attempts'] }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="font-bold text-[#182438]">{{ $stats['avg_pct'] }}%</span>
                        </td>
                        <td class="px-4 py-3 text-center text-[#5f6873]">{{ $stats['best_level'] }}</td>
                        <td class="px-4 py-3 text-[#5f6873]">
                            {{ $stats['avg_pct'] >= 75 ? __('analytics.interp_confident') : ($stats['avg_pct'] >= 50 ? __('analytics.interp_progress') : __('analytics.interp_reteach')) }}
                        </td>
                    @else
                        <td class="px-4 py-3 text-center text-[#6b7280]">0</td>
                        <td class="px-4 py-3 text-center text-[#6b7280]">—</td>
                        <td class="px-4 py-3 text-center text-[#6b7280]">—</td>
                        <td class="px-4 py-3 text-[#6b7280]">{{ __('analytics.not_started') }}</td>
                    @endif
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-[#6b7280]">{{ __('analytics.no_game_data') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Engagement + enrolment --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-4 {{ $selectedStudent ? 'lg:col-span-3' : 'lg:col-span-2' }}">
        <h3 class="text-lg font-semibold text-[#182438] mb-1">{{ __('analytics.weekly_trend_title') }}</h3>
        <p class="text-xs text-[#6b7280] mb-4">{{ __('analytics.weekly_subtitle_long') }}</p>
        <div class="chart-box chart-box--tall"><canvas id="gamePlayTrendChart"></canvas></div>
    </div>
    @unless($selectedStudent)
    <div class="bg-white rounded-xl shadow-sm p-4">
        <h3 class="text-lg font-semibold text-[#182438] mb-1">{{ __('analytics.enrolment_trend_title') }}</h3>
        <p class="text-xs text-[#6b7280] mb-4">{{ __('analytics.enrolment_trend_subtitle_teacher') }}</p>
        <div class="chart-box"><canvas id="enrollTrendChart"></canvas></div>
    </div>
    @endunless
</div>

{{-- Recommendations. (A per-student comparison chart used to sit beside them; individual
     data does not belong in a group view, so it is gone.) --}}
<div class="grid grid-cols-1 gap-5 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-4">
        <h3 class="text-lg font-semibold text-[#182438] mb-1">{{ __('analytics.recommended_interventions') }}</h3>
        <p class="text-xs text-[#6b7280] mb-4">{{ __('analytics.ri_subtitle') }}</p>
        <div class="space-y-3 max-h-[320px] overflow-y-auto pr-1">
            @forelse($recommendations as $rec)
                <div class="p-4 rounded-lg border-l-4
                    {{ $rec['severity'] === 'high' ? 'bg-[#fde8ef] border-[#ff595e]' : ($rec['severity'] === 'medium' ? 'bg-[#fff6e5] border-[#ffca3a]' : 'bg-[#ecf7d6] border-[#8ac926]') }}">
                    <div class="flex items-center justify-between mb-1">
                        <h4 class="font-semibold text-[#182438]">{{ $rec['name'] }}</h4>
                        <span class="text-xs font-bold uppercase tracking-wide px-2 py-0.5 rounded-full
                            {{ $rec['severity'] === 'high' ? 'bg-[#ff595e] text-white' : ($rec['severity'] === 'medium' ? 'bg-[#ffca3a] text-[#182438]' : 'bg-[#8ac926] text-[#182438]') }}">
                            {{ __("analytics.severity_{$rec['severity']}") }}
                        </span>
                    </div>
                    <span class="inline-flex items-center gap-1.5 text-xs text-[#5f6873] mb-1">
                        <span class="w-2 h-2 rounded-full"
                              style="background-color: {{ \App\Models\Student::colorForSection($sectionByStudent[$rec['student_id']] ?? null) }}"></span>
                        {{ \App\Models\Student::sectionLabel($sectionByStudent[$rec['student_id']] ?? null) }}
                    </span>
                    <p class="text-sm text-[#5f6873] mt-1"><span class="font-medium">{{ __('analytics.needs_label') }}</span> {{ $rec['needs'] }}</p>
                    <p class="text-sm text-[#5f6873]"><span class="font-medium">{{ __('analytics.plan_label') }}</span> {{ $rec['plan'] }}</p>
                </div>
            @empty
                <div class="p-6 rounded-lg bg-[#ecf7d6] border border-[#b8e0c2] text-center">
                    <p class="text-[#182438] font-semibold">{{ __('analytics.no_interventions_needed') }}</p>
                    <p class="text-sm text-[#5f6873] mt-1">{{ __('analytics.no_interventions_desc_teacher') }}</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

{{-- Per-student score for every category: games and ECCD domains. 8 students a page;
     the filters and pager reload only this card (DashboardController::partial). --}}
@if($scorePager->total() > 0 || $scoreSection !== '')
<div class="bg-white rounded-xl shadow-sm p-4 mb-8" data-partial="scores">
    @include('teacher.dashboard.partials.scores')
</div>
@endif

@unless($selectedStudent)
{{-- Who is exceeding / struggling, per topic --}}
<div class="bg-white rounded-xl shadow-sm p-4 mb-8">
    <h3 class="text-lg font-semibold text-[#182438] mb-1">{{ __('analytics.exceeding_struggling') }}</h3>
    <p class="text-xs text-[#6b7280] mb-4">
        {{ __('analytics.es_subtitle', [
            'exceeding' => \App\Services\AnalyticsService::EXCEEDING_PCT,
            'struggling' => \App\Services\AnalyticsService::STRUGGLING_PCT,
        ]) }}
    </p>

    @php
        $standingVisible = fn (string $basis) => $topicType === 'all' || ($topicType === 'game' && $basis === 'Game') || ($topicType === 'eccd' && $basis === 'Domain');
        $standingShown = count(array_filter($topicStandings, fn ($row) => $standingVisible($row['basis'])));
    @endphp
    {{-- Every row is already on the page; this filter just shows and hides them in the browser,
         so changing it makes no request and recomputes nothing. --}}
    <div data-standings>
    <div class="mb-4 flex items-center gap-3">
        <label class="text-xs font-semibold text-[#5f6873]">Show
            <select data-standings-filter class="ml-2 rounded-lg border border-[#d8d2c5] px-3 py-2 text-sm">
                <option value="all" @selected($topicType === 'all')>Games and ECCD</option>
                <option value="game" @selected($topicType === 'game')>Games only</option>
                <option value="eccd" @selected($topicType === 'eccd')>ECCD only</option>
            </select>
        </label>
    </div>
    @if(count($topicStandings))
    <div class="overflow-x-auto" data-standings-table @if($standingShown === 0) hidden @endif>
        <table class="w-full min-w-[780px]">
            <thead class="bg-[#fffbf0]">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-[#182438] uppercase">{{ __('analytics.th_topic') }}</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-[#182438] uppercase">{{ __('analytics.th_type') }}</th>
                    <th class="px-3 py-3 text-center text-xs font-semibold text-[#182438] uppercase">{{ __('analytics.th_learners') }}</th>
                    <th class="px-3 py-3 text-center text-xs font-semibold text-[#182438] uppercase">{{ __('analytics.th_cohort_avg') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-[#182438] uppercase">{{ __('analytics.th_exceeding') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-[#182438] uppercase">{{ __('analytics.th_struggling') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#f1e7cc]">
                @foreach($topicStandings as $row)
                <tr class="hover:bg-[#fffbf0] transition align-top" data-basis="{{ $row['basis'] }}" @if(! $standingVisible($row['basis'])) hidden @endif>
                    <td class="px-4 py-3 font-medium text-[#182438] capitalize">{{ $row['topic'] }}</td>
                    <td class="px-3 py-3 text-sm text-[#5f6873]">{{ $row['basis'] }}</td>
                    <td class="px-3 py-3 text-sm text-center text-[#5f6873]">{{ $row['participants'] }}</td>
                    <td class="px-3 py-3 text-center">
                        <span class="text-sm font-semibold {{ $row['avg'] >= \App\Services\AnalyticsService::EXCEEDING_PCT ? 'text-[#8ac926]' : ($row['avg'] < \App\Services\AnalyticsService::STRUGGLING_PCT ? 'text-[#ff595e]' : 'text-[#ffca3a]') }}">
                            {{ $row['avg'] }}%
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        @if(count($row['exceeding']))
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($row['exceeding'] as $p)
                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold bg-[#ecf7d6] text-[#3f7d1a] px-2 py-0.5 rounded-full whitespace-nowrap">
                                    <span class="w-1.5 h-1.5 rounded-full" style="background-color: {{ \App\Models\Student::colorForSection($sectionByStudent[$p['id']] ?? null) }}"></span>
                                    {{ $p['name'] }} · {{ $p['pct'] }}%
                                </span>
                                @endforeach
                            </div>
                        @else
                            <span class="text-xs text-[#6b7280]">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if(count($row['struggling']))
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($row['struggling'] as $p)
                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold bg-[#fde8ef] text-[#ff595e] px-2 py-0.5 rounded-full whitespace-nowrap">
                                    <span class="w-1.5 h-1.5 rounded-full" style="background-color: {{ \App\Models\Student::colorForSection($sectionByStudent[$p['id']] ?? null) }}"></span>
                                    {{ $p['name'] }} · {{ $p['pct'] }}%
                                </span>
                                @endforeach
                            </div>
                        @else
                            <span class="text-xs text-[#6b7280]">—</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
    <p data-standings-empty class="text-sm text-[#6b7280] py-8 text-center" @if($standingShown > 0) hidden @endif>{{ __('analytics.no_scores_recorded') }}</p>
    </div>
</div>
@endunless

{{-- Leaderboards: one ranking per game, ECCD domain and activity type --}}
@unless($selectedStudent)
    @include('teacher.analytics.partials.leaderboards')
@endunless

{{-- Level progression. Picking a student reloads only the table below (DashboardController::partial). --}}
<div class="bg-white rounded-xl shadow-sm p-4">
    <h3 class="text-lg font-semibold text-[#182438] mb-3">{{ __('analytics.level_progress_map') }}</h3>
    <form method="GET" action="{{ route('teacher.dashboard') }}" class="mb-4 flex flex-wrap items-end gap-3"
          data-partial-form data-no-loader data-partial-url="{{ route('teacher.dashboard.partial', 'levels') }}" data-partial-target="#levelProgressBody">
        {{-- Only used without JavaScript; with it the live URL is the source of truth. --}}
        @foreach(request()->except(['level_student','intervention_page']) as $key => $value)
            @if(is_scalar($value))<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif
        @endforeach
        <label class="text-xs font-semibold text-[#5f6873]">Individual student
            <select name="level_student" class="block mt-1 rounded-lg border border-[#d8d2c5] px-3 py-2 text-sm" onchange="this.form.requestSubmit()">
                <option value="">Choose a student</option>
                @foreach($levelStudentOptions as $option)<option value="{{ $option->id }}" @selected($levelStudent && $levelStudent->id === $option->id)>{{ $option->name }}</option>@endforeach
            </select>
        </label>
    </form>
    <div id="levelProgressBody" data-partial-body>
        @include('teacher.dashboard.partials.levels-body')
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ \App\Support\Asset::v('js/chart.umd.min.js') }}"></script>
<script src="{{ \App\Support\Asset::v('js/dash-partials.js') }}"></script>
<script data-page-script>
Chart.defaults.maintainAspectRatio = false;
const chartIfPresent = (id, config) => { const el = document.getElementById(id); if (el) new Chart(el, config); };
const attendanceRate = @json($attendanceRate);
const weeklyBreakdown = @json($weeklyBreakdown);
const performanceRadar = @json($performanceRadar);
const domainStatus = @json($domainStatus);
const ageDistribution = @json($ageDistribution);
const enrollmentTrend = @json($enrollmentTrend);
const gamePlayTrend = @json($gamePlayTrend);
const gamePerformance = @json($gamePerformance);

const PALETTE = ['#ff8555', '#1982c4', '#8ac926', '#ff595e', '#ffca3a', '#e78cae', '#7c5cbf', '#16a085'];
const gridColor = 'rgba(24, 36, 56, 0.08)';

// Counts are charted as a share (%) of their total, so every axis tops out at 100
// and a chart can never grow past its box. Tooltips still show the real number.
const shareOf = values => {
    const total = values.reduce((sum, v) => sum + (+v || 0), 0);
    return values.map(v => total > 0 ? Math.round(((+v || 0) / total) * 1000) / 10 : 0);
};
const pctTicks = { callback: v => v + '%' };
const withCounts = counts => ({ callbacks: { label: ctx => ctx.dataset.label + ': ' + ctx.raw + '% (' + counts[ctx.datasetIndex][ctx.dataIndex] + ')' } });
Chart.defaults.layout.padding = { top: 8, right: 12, bottom: 8, left: 8 };
Chart.defaults.plugins.legend.labels.boxWidth = 12;
Chart.defaults.plugins.legend.labels.padding = 16;
if (Chart.defaults.scale && Chart.defaults.scale.ticks) { Chart.defaults.scale.ticks.padding = 6; }

const L = {
    'present_rate': @json(__('analytics.chart_present_rate')),
    'present': @json(__('analytics.chart_present')),
    'absent': @json(__('analytics.chart_absent')),
    'late': @json(__('analytics.chart_late')),
    'achieved': @json(__('analytics.chart_achieved')),
    'students': @json(__('analytics.chart_students')),
    'levels_played': @json(__('analytics.chart_levels_played')),
    'avg_accuracy': @json(__('analytics.chart_avg_accuracy')),
    'levels': @json(__('analytics.chart_levels')),
    'new_enrolments': @json(__('analytics.chart_new_enrolments')),
    'avg_score': @json(__('analytics.chart_avg_score')),
    'attempts': @json(__('analytics.chart_attempts')),
};

// Attendance rate trend
new Chart(document.getElementById('attendanceTrendChart'), {
    type: 'line',
    data: {
        labels: attendanceRate.map(r => r.month),
        datasets: [{
            label: L.present_rate,
            data: attendanceRate.map(r => r.rate),
            borderColor: '#ff8555',
            backgroundColor: 'rgba(255, 133, 85, 0.12)',
            fill: true,
            tension: 0.35,
            pointRadius: 4
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } },
        scales: {
            y: { min: 0, max: 100, ticks: { callback: v => v + '%' }, grid: { color: gridColor } },
            x: { grid: { display: false }, ticks: { autoSkip: false, maxRotation: 0, minRotation: 0 } }
        }
    }
});

// Weekly breakdown: each day's present / absent / late as a share of that day
const dayTotals = weeklyBreakdown.map(r => (+r.present || 0) + (+r.absent || 0) + (+r.late || 0));
const dayShare = key => weeklyBreakdown.map((r, i) => dayTotals[i] ? Math.round(((+r[key] || 0) / dayTotals[i]) * 1000) / 10 : 0);
new Chart(document.getElementById('weeklyBreakdownChart'), {
    type: 'bar',
    data: {
        labels: weeklyBreakdown.map(r => r.day),
        datasets: [
            { label: L.present, data: dayShare('present'), backgroundColor: '#8ac926', borderRadius: 4, maxBarThickness: 36 },
            { label: L.absent, data: dayShare('absent'), backgroundColor: '#ff595e', borderRadius: 4, maxBarThickness: 36 },
            { label: L.late, data: dayShare('late'), backgroundColor: '#ffca3a', borderRadius: 4, maxBarThickness: 36 }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        layout: { padding: { left: 12, right: 18, top: 10, bottom: 10 } },
        plugins: {
            legend: { position: 'bottom' },
            tooltip: withCounts([weeklyBreakdown.map(r => r.present), weeklyBreakdown.map(r => r.absent), weeklyBreakdown.map(r => r.late)])
        },
        scales: { y: { min: 0, max: 100, ticks: pctTicks, grid: { color: gridColor } }, x: { grid: { display: false }, ticks: { autoSkip: false, maxRotation: 0, minRotation: 0 } } }
    }
});

@if($selectedStudent)
// ECCD radar
new Chart(document.getElementById('performanceRadarChart'), {
    type: 'radar',
    data: {
        labels: Object.keys(performanceRadar),
        datasets: [{
            label: L.achieved,
            data: Object.values(performanceRadar),
            backgroundColor: 'rgba(255, 133, 85, 0.2)',
            borderColor: '#ff8555',
            pointBackgroundColor: '#ff8555',
            pointRadius: 3
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } },
        scales: { r: { min: 0, max: 100, ticks: { stepSize: 25, backdropColor: 'transparent' }, grid: { color: gridColor }, angleLines: { color: gridColor }, pointLabels: { font: { size: 10 } } } }
    }
});

// Indicator status split
new Chart(document.getElementById('domainStatusChart'), {
    type: 'doughnut',
    data: {
        labels: Object.keys(domainStatus),
        datasets: [{
            data: Object.values(domainStatus),
            backgroundColor: ['#8ac926', '#ffca3a', '#ff595e'],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        cutout: '55%',
        plugins: {
            legend: { position: 'bottom' },
            tooltip: {
                callbacks: {
                    label: ctx => {
                        const vals = ctx.dataset.data;
                        const total = vals.reduce((a, b) => a + b, 0);
                        const pct = total > 0 ? Math.round((ctx.raw / total) * 100) : 0;
                        return ctx.label + ': ' + ctx.raw + ' (' + pct + '%)';
                    }
                }
            }
        }
    }
});
@endif

// Age distribution: share of the students in each age group
chartIfPresent('ageDistChart', {
    type: 'bar',
    data: {
        labels: Object.keys(ageDistribution),
        datasets: [{
            label: L.students,
            data: shareOf(Object.values(ageDistribution)),
            backgroundColor: ['#1982c4', '#8ac926', '#ff8555', '#ffca3a', '#e78cae'],
            borderRadius: 6,
            maxBarThickness: 36
        }]
    },
    options: {
        responsive: true,
        indexAxis: 'y',
        plugins: { legend: { display: false }, tooltip: withCounts([Object.values(ageDistribution)]) },
        scales: { x: { min: 0, max: 100, ticks: pctTicks, grid: { color: gridColor } }, y: { grid: { display: false } } }
    }
});

// Gameplay trend: bars + line
new Chart(document.getElementById('gamePlayTrendChart'), {
    data: {
        labels: gamePlayTrend.map(r => r.week),
        datasets: [
            { type: 'bar', label: L.levels_played, data: shareOf(gamePlayTrend.map(r => r.plays)), backgroundColor: 'rgba(37, 140, 197, 0.55)', borderRadius: 4, maxBarThickness: 36, yAxisID: 'y' },
            { type: 'line', label: L.avg_accuracy, data: gamePlayTrend.map(r => r.avg_pct), borderColor: '#8ac926', backgroundColor: '#8ac926', tension: 0.35, yAxisID: 'y1', pointRadius: 3, clip: false }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        layout: { padding: { left: 12, right: 18, top: 10, bottom: 10 } },
        plugins: {
            legend: { position: 'bottom' },
            tooltip: { callbacks: { label: ctx => ctx.datasetIndex === 0
                ? ctx.dataset.label + ': ' + ctx.raw + '% (' + gamePlayTrend[ctx.dataIndex].plays + ')'
                : ctx.dataset.label + ': ' + ctx.raw + '%' } }
        },
        scales: {
            y: { min: 0, max: 100, position: 'left', ticks: pctTicks, grid: { color: gridColor }, title: { display: true, text: L.levels + ' (%)' } },
            y1: { min: 0, max: 100, position: 'right', ticks: { callback: v => v + '%' }, grid: { display: false } },
            x: { grid: { display: false }, ticks: { autoSkip: false, maxRotation: 0, minRotation: 0 } }
        }
    }
});

// Enrollment trend
chartIfPresent('enrollTrendChart', {
    type: 'line',
    data: {
        labels: enrollmentTrend.map(r => r.month),
        datasets: [{
            label: L.new_enrolments,
            data: shareOf(enrollmentTrend.map(r => r.total)),
            borderColor: '#16a085',
            backgroundColor: 'rgba(22, 160, 133, 0.12)',
            fill: true,
            tension: 0.35,
            pointRadius: 4
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' }, tooltip: withCounts([enrollmentTrend.map(r => r.total)]) },
        scales: { y: { min: 0, max: 100, ticks: pctTicks, grid: { color: gridColor } }, x: { grid: { display: false } } }
    }
});

</script>
@endpush
