@extends('layouts.teacher')

@section('title', __('page.dashboard'))
@section('page-title', __('page.teacher_dashboard'))

@section('content')
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
<div class="grid grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#258cc5]">
        <p class="text-[#5f6873] text-sm">{{ __('analytics.stat_active_students') }}</p>
        <p class="text-3xl font-bold text-[#182438]">{{ $summary['active_students'] }}</p>
        <p class="text-xs text-[#8b949d] mt-1">{{ __('analytics.stat_of_enrolled', ['count' => $summary['students']]) }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#68b82f]">
        <p class="text-[#5f6873] text-sm">{{ __('analytics.stat_avg_game_accuracy') }}</p>
        <p class="text-3xl font-bold text-[#182438]">{{ $summary['avg_game_pct'] }}%</p>
        <p class="text-xs text-[#8b949d] mt-1">{{ __('analytics.stat_levels_played', ['count' => $summary['games_played']]) }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#ff8555]">
        <p class="text-[#5f6873] text-sm">{{ __('analytics.stat_attendance_rate') }}</p>
        <p class="text-3xl font-bold text-[#182438]">{{ $summary['attendance_rate'] }}%</p>
        <p class="text-xs text-[#8b949d] mt-1">{{ __('analytics.stat_late_arrivals', ['count' => $summary['late_count']]) }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#d9b348]">
        <p class="text-[#5f6873] text-sm">{{ __('analytics.stat_points_awarded') }}</p>
        <p class="text-3xl font-bold text-[#182438]">{{ number_format($summary['points_awarded']) }}</p>
        <p class="text-xs text-[#8b949d] mt-1">{{ __('analytics.stat_all_time') }}</p>
    </div>
</div>

{{-- Attendance --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-1">{{ __('analytics.attendance_trend_title') }}</h3>
        <p class="text-xs text-[#8b949d] mb-4">{{ __('analytics.attendance_trend_subtitle') }}</p>
        <canvas id="attendanceTrendChart" height="220"></canvas>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6">
        <x-attendance-period-header :period="$attendancePeriod" :keep="$attendanceKeep" />
        <canvas id="weeklyBreakdownChart" height="220"></canvas>
        <div class="mt-5 pt-4 border-t border-[#ebe8e1]">
            <x-week-attendance :days="$weeklyBreakdown" :unit="$attendancePeriod['mode'] === 'month' ? 'week' : 'day'" />
        </div>
    </div>
</div>

{{-- Developmental domains: ECCD charts only make sense for one student --}}
@if($selectedStudent)
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-1">{{ __('analytics.domain_mastery_title') }}</h3>
        <p class="text-xs text-[#8b949d] mb-4">
            {{ __('analytics.domain_mastery_subtitle_teacher', ['who' => __('analytics.domain_who_name', ['name' => $selectedStudent->first_name])]) }}
        </p>
        <canvas id="performanceRadarChart" height="260"></canvas>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-1">{{ __('analytics.indicator_status_title') }}</h3>
        <p class="text-xs text-[#8b949d] mb-4">{{ __('analytics.indicator_status_subtitle') }}</p>
        <canvas id="domainStatusChart" height="260"></canvas>
    </div>
</div>
@else
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center justify-center">
        <div class="text-center max-w-md">
            <p class="font-semibold text-[#182438]">{{ __('analytics.domain_selection_title') }}</p>
            <p class="text-sm text-[#5f6873] mt-1">{{ __('analytics.domain_selection_prompt') }}</p>
            <p class="text-xs text-[#8b949d] mt-1">{{ __('analytics.domain_selection_hint') }}</p>
            <button type="button" @click="$dispatch('open-student-chooser')" class="inline-block mt-3 px-4 py-2 rounded-lg bg-[#ff8555] text-white text-sm font-semibold hover:bg-[#f47548]">{{ __('analytics.domain_choose_student') }}</button>
            <x-student-chooser variant="none" :students="$studentOptions" :action="route('teacher.dashboard')" param="student" />
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-1">{{ __('analytics.age_dist_title') }}</h3>
        <p class="text-xs text-[#8b949d] mb-4">{{ __('analytics.age_dist_subtitle_cohort') }}</p>
        <canvas id="ageDistChart" height="260"></canvas>
    </div>
</div>
@endif

{{-- Game performance --}}
<div class="bg-white rounded-xl shadow-sm p-6 mb-8">
    <h3 class="text-lg font-semibold text-[#182438] mb-1">{{ __('analytics.game_perf_breakdown') }}</h3>
    <p class="text-xs text-[#8b949d] mb-4">{{ __('analytics.gp_subtitle') }}</p>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[640px]">
            <thead class="bg-[#faf8f3]">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-[#182438] uppercase">{{ __('analytics.th_game') }}</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-[#182438] uppercase">{{ __('analytics.th_levels_played') }}</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-[#182438] uppercase">{{ __('analytics.th_avg_accuracy') }}</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-[#182438] uppercase">{{ __('analytics.th_highest_level') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-[#182438] uppercase">{{ __('analytics.th_interpretation') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#ebe8e1]">
                @forelse($gamePerformance as $game => $stats)
                <tr class="hover:bg-[#faf8f3] transition">
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
                        <td class="px-4 py-3 text-center text-[#8b949d]">0</td>
                        <td class="px-4 py-3 text-center text-[#8b949d]">—</td>
                        <td class="px-4 py-3 text-center text-[#8b949d]">—</td>
                        <td class="px-4 py-3 text-[#8b949d]">{{ __('analytics.not_started') }}</td>
                    @endif
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-[#8b949d]">{{ __('analytics.no_game_data') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Engagement + enrolment --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm p-6 {{ $selectedStudent ? 'lg:col-span-3' : 'lg:col-span-2' }}">
        <h3 class="text-lg font-semibold text-[#182438] mb-1">{{ __('analytics.weekly_trend_title') }}</h3>
        <p class="text-xs text-[#8b949d] mb-4">{{ __('analytics.weekly_subtitle_long') }}</p>
        <canvas id="gamePlayTrendChart" height="240"></canvas>
    </div>
    @unless($selectedStudent)
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-1">{{ __('analytics.enrolment_trend_title') }}</h3>
        <p class="text-xs text-[#8b949d] mb-4">{{ __('analytics.enrolment_trend_subtitle_teacher') }}</p>
        <canvas id="enrollTrendChart" height="240"></canvas>
    </div>
    @endunless
</div>

{{-- Student comparison + recommendations --}}
<div class="grid grid-cols-1 {{ $selectedStudent ? '' : 'lg:grid-cols-2' }} gap-6 mb-8">
    @unless($selectedStudent)
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-1">{{ __('analytics.student_comparison') }}</h3>
        <p class="text-xs text-[#8b949d] mb-4">{{ __('analytics.sc_subtitle') }}</p>
        @if(count($studentComparison))
            <canvas id="studentComparisonChart" height="320"></canvas>
        @else
            <p class="text-sm text-[#8b949d] py-12 text-center">{{ __('analytics.no_student_data') }}</p>
        @endif
    </div>
    @endunless

    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-1">{{ __('analytics.recommended_interventions') }}</h3>
        <p class="text-xs text-[#8b949d] mb-4">{{ __('analytics.ri_subtitle') }}</p>
        <div class="space-y-3 max-h-[320px] overflow-y-auto pr-1">
            @forelse($recommendations as $rec)
                <div class="p-4 rounded-lg border-l-4
                    {{ $rec['severity'] === 'high' ? 'bg-[#fde8ef] border-[#f05d56]' : ($rec['severity'] === 'medium' ? 'bg-[#fff6e5] border-[#d9b348]' : 'bg-[#e2f4e8] border-[#68b82f]') }}">
                    <div class="flex items-center justify-between mb-1">
                        <h4 class="font-semibold text-[#182438]">{{ $rec['name'] }}</h4>
                        <span class="text-[10px] font-bold uppercase tracking-wide px-2 py-0.5 rounded-full
                            {{ $rec['severity'] === 'high' ? 'bg-[#f05d56] text-white' : ($rec['severity'] === 'medium' ? 'bg-[#d9b348] text-white' : 'bg-[#68b82f] text-white') }}">
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
                <div class="p-6 rounded-lg bg-[#e2f4e8] border border-[#b8e0c2] text-center">
                    <p class="text-[#182438] font-semibold">{{ __('analytics.no_interventions_needed') }}</p>
                    <p class="text-sm text-[#5f6873] mt-1">{{ __('analytics.no_interventions_desc_teacher') }}</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

{{-- Per-student score for every category: games and ECCD domains --}}
@if(count($categoryScores))
<div class="bg-white rounded-xl shadow-sm p-6 mb-8">
    <h3 class="text-lg font-semibold text-[#182438] mb-1">
        {{ $selectedStudent ? __('analytics.score_by_category') : __('analytics.score_by_category_per_student') }}
    </h3>
    <p class="text-xs text-[#8b949d] mb-4">
        @if($selectedStudent)
            {{ __('analytics.category_blurb_selected') }}
        @else
            {{ __('analytics.category_blurb_cohort') }}
        @endif
    </p>

    <div class="overflow-x-auto">
        <table class="w-full min-w-[980px]">
            <thead class="bg-[#faf8f3]">
                <tr>
                    <th rowspan="2" class="px-3 py-2 text-left text-xs font-semibold text-[#182438] uppercase">{{ __('analytics.th_student') }}</th>
                    <th rowspan="2" class="px-3 py-2 text-left text-xs font-semibold text-[#182438] uppercase">{{ __('analytics.th_section') }}</th>
                    <th colspan="{{ count(\App\Services\AnalyticsService::GAME_TYPES) }}"
                        class="px-3 py-2 text-center text-xs font-semibold text-[#258cc5] uppercase">
                        {{ __('analytics.th_game_accuracy') }}
                    </th>
                    <th colspan="{{ count(\App\Services\AnalyticsService::ECCD_DOMAINS) }}"
                        class="px-3 py-2 text-center text-xs font-semibold text-[#7c5cbf] uppercase">
                        {{ __('analytics.th_eccd_mastery') }}
                    </th>
                    <th rowspan="2" class="px-3 py-2 text-center text-xs font-semibold text-[#182438] uppercase">{{ __('analytics.th_game_avg') }}</th>
                    <th rowspan="2" class="px-3 py-2 text-center text-xs font-semibold text-[#182438] uppercase">{{ __('analytics.th_eccd_avg') }}</th>
                    <th colspan="{{ count(\App\Services\AnalyticsService::GAME_TYPES) }}"
                        class="px-3 py-2 text-center text-xs font-semibold text-[#b8860b] uppercase">
                        {{ __('analytics.th_points_earned') }}
                    </th>
                    <th rowspan="2" class="px-3 py-2 text-center text-xs font-semibold text-[#b8860b] uppercase">{{ __('analytics.th_total_pts') }}</th>
                </tr>
                <tr>
                    @foreach(\App\Services\AnalyticsService::GAME_TYPES as $game)
                        <th class="px-2 py-2 text-center text-[11px] font-semibold text-[#5f6873] uppercase">{{ ucfirst($game) }}</th>
                    @endforeach
                    @foreach(\App\Services\AnalyticsService::ECCD_DOMAINS as $domain)
                        <th class="px-2 py-2 text-center text-[11px] font-semibold text-[#5f6873] uppercase"
                            title="{{ $domain }}">{{ \App\Services\AnalyticsService::ECCD_DOMAIN_SHORT[$domain] ?? $domain }}</th>
                    @endforeach
                    @foreach(\App\Services\AnalyticsService::GAME_TYPES as $game)
                        <th class="px-2 py-2 text-center text-[11px] font-semibold text-[#5f6873] uppercase">{{ ucfirst($game) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-[#ebe8e1]">
                @foreach($categoryScores as $row)
                    <tr class="hover:bg-[#faf8f3] transition">
                        <td class="px-3 py-2.5 whitespace-nowrap font-medium text-[#182438]">{{ $row['name'] }}</td>
                        <td class="px-3 py-2.5">
                            <span class="inline-flex items-center gap-1.5 text-xs text-[#5f6873] whitespace-nowrap">
                                <span class="w-2 h-2 rounded-full" style="background-color: {{ \App\Models\Student::colorForSection($row['class_name']) }}"></span>
                                {{ \App\Models\Student::sectionLabel($row['class_name']) }}
                            </span>
                        </td>
                        @foreach(\App\Services\AnalyticsService::GAME_TYPES as $game)
                            <td class="px-2 py-2.5 text-center text-sm"><x-score-cell :value="$row['games'][$game] ?? null" /></td>
                        @endforeach
                        @foreach(\App\Services\AnalyticsService::ECCD_DOMAINS as $domain)
                            <td class="px-2 py-2.5 text-center text-sm"><x-score-cell :value="$row['domains'][$domain] ?? null" /></td>
                        @endforeach
                        <td class="px-3 py-2.5 text-center text-sm"><x-score-cell :value="$row['game_overall']" /></td>
                        <td class="px-3 py-2.5 text-center text-sm"><x-score-cell :value="$row['domain_overall']" /></td>
                        @foreach(\App\Services\AnalyticsService::GAME_TYPES as $game)
                            <td class="px-2 py-2.5 text-center text-sm text-[#5f6873]">
                                @if(isset($row['points'][$game]))
                                    <span>{{ number_format($row['points'][$game]) }}</span>
                                @else
                                    <span class="text-[#8b949d]" title="{{ __('analytics.no_points_yet') }}">&mdash;</span>
                                @endif
                            </td>
                        @endforeach
                        <td class="px-3 py-2.5 text-center">
                            <span class="font-bold text-[#b8860b]">{{ number_format($row['total_points']) }}</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <p class="text-xs text-[#8b949d] mt-3">
        <span class="text-[#68b82f] font-bold">&#8805;75%</span> {{ __('analytics.legend_on_track') }} &middot;
        <span class="text-[#d9b348] font-bold">50&ndash;74%</span> {{ __('analytics.legend_needs_practice') }} &middot;
        <span class="text-[#f05d56] font-bold">&lt;50%</span> {{ __('analytics.legend_priority') }}
    </p>
</div>
@endif

{{-- Rollup of the same scores per section. Always every class, never filtered. --}}
@if(!$selectedStudent && count($sectionScores))
<div class="bg-white rounded-xl shadow-sm p-6 mb-8">
    <h3 class="text-lg font-semibold text-[#182438] mb-1">{{ __('analytics.avg_score_by_section') }}</h3>
    <p class="text-xs text-[#8b949d] mb-4">{{ __('analytics.asbs_subtitle') }}</p>

    <div class="overflow-x-auto">
        <table class="w-full min-w-[520px]">
            <thead class="bg-[#faf8f3]">
                <tr>
                    <th class="px-3 py-2 text-left text-xs font-semibold text-[#182438] uppercase">{{ __('analytics.th_section') }}</th>
                    <th class="px-3 py-2 text-center text-xs font-semibold text-[#182438] uppercase">{{ __('analytics.th_students') }}</th>
                    <th class="px-3 py-2 text-center text-xs font-semibold text-[#258cc5] uppercase">{{ __('analytics.th_avg_game_accuracy') }}</th>
                    <th class="px-3 py-2 text-center text-xs font-semibold text-[#7c5cbf] uppercase">{{ __('analytics.th_avg_eccd_mastery') }}</th>
                    <th class="px-3 py-2 text-center text-xs font-semibold text-[#b8860b] uppercase">{{ __('analytics.th_avg_points') }}</th>
                    <th class="px-3 py-2 text-center text-xs font-semibold text-[#b8860b] uppercase">{{ __('analytics.th_total_points') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#ebe8e1]">
                @foreach($sectionScores as $row)
                    <tr class="hover:bg-[#faf8f3] transition">
                        <td class="px-3 py-2.5">
                            <span class="inline-flex items-center gap-2 text-sm font-medium text-[#182438]">
                                <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $row['color'] }}"></span>
                                {{ $row['label'] }}
                            </span>
                        </td>
                        <td class="px-3 py-2.5 text-center text-sm text-[#5f6873]">{{ $row['students'] }}</td>
                        <td class="px-3 py-2.5 text-center text-sm"><x-score-cell :value="$row['game_pct']" /></td>
                        <td class="px-3 py-2.5 text-center text-sm"><x-score-cell :value="$row['domain_pct']" /></td>
                        <td class="px-3 py-2.5 text-center text-sm text-[#5f6873]">
                            {{ $row['avg_points'] === null ? '—' : number_format($row['avg_points']) }}
                        </td>
                        <td class="px-3 py-2.5 text-center font-bold text-[#b8860b]">{{ number_format($row['total_points']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@unless($selectedStudent)
{{-- Who is exceeding / struggling, per topic --}}
<div class="bg-white rounded-xl shadow-sm p-6 mb-8">
    <h3 class="text-lg font-semibold text-[#182438] mb-1">{{ __('analytics.exceeding_struggling') }}</h3>
    <p class="text-xs text-[#8b949d] mb-4">
        {{ __('analytics.es_subtitle', [
            'exceeding' => \App\Services\AnalyticsService::EXCEEDING_PCT,
            'struggling' => \App\Services\AnalyticsService::STRUGGLING_PCT,
        ]) }}
    </p>

    @if(count($topicStandings))
    <div class="overflow-x-auto">
        <table class="w-full min-w-[780px]">
            <thead class="bg-[#faf8f3]">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-[#182438] uppercase">{{ __('analytics.th_topic') }}</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-[#182438] uppercase">{{ __('analytics.th_type') }}</th>
                    <th class="px-3 py-3 text-center text-xs font-semibold text-[#182438] uppercase">{{ __('analytics.th_learners') }}</th>
                    <th class="px-3 py-3 text-center text-xs font-semibold text-[#182438] uppercase">{{ __('analytics.th_cohort_avg') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-[#182438] uppercase">{{ __('analytics.th_exceeding') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-[#182438] uppercase">{{ __('analytics.th_struggling') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#ebe8e1]">
                @foreach($topicStandings as $row)
                <tr class="hover:bg-[#faf8f3] transition align-top">
                    <td class="px-4 py-3 font-medium text-[#182438] capitalize">{{ $row['topic'] }}</td>
                    <td class="px-3 py-3 text-sm text-[#5f6873]">{{ $row['basis'] }}</td>
                    <td class="px-3 py-3 text-sm text-center text-[#5f6873]">{{ $row['participants'] }}</td>
                    <td class="px-3 py-3 text-center">
                        <span class="text-sm font-semibold {{ $row['avg'] >= \App\Services\AnalyticsService::EXCEEDING_PCT ? 'text-[#68b82f]' : ($row['avg'] < \App\Services\AnalyticsService::STRUGGLING_PCT ? 'text-[#f05d56]' : 'text-[#d9b348]') }}">
                            {{ $row['avg'] }}%
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        @if(count($row['exceeding']))
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($row['exceeding'] as $p)
                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold bg-[#e2f4e8] text-[#3f7d1a] px-2 py-0.5 rounded-full whitespace-nowrap">
                                    <span class="w-1.5 h-1.5 rounded-full" style="background-color: {{ \App\Models\Student::colorForSection($sectionByStudent[$p['id']] ?? null) }}"></span>
                                    {{ $p['name'] }} · {{ $p['pct'] }}%
                                </span>
                                @endforeach
                            </div>
                        @else
                            <span class="text-xs text-[#8b949d]">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if(count($row['struggling']))
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($row['struggling'] as $p)
                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold bg-[#fde8ef] text-[#f05d56] px-2 py-0.5 rounded-full whitespace-nowrap">
                                    <span class="w-1.5 h-1.5 rounded-full" style="background-color: {{ \App\Models\Student::colorForSection($sectionByStudent[$p['id']] ?? null) }}"></span>
                                    {{ $p['name'] }} · {{ $p['pct'] }}%
                                </span>
                                @endforeach
                            </div>
                        @else
                            <span class="text-xs text-[#8b949d]">—</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <p class="text-sm text-[#8b949d] py-8 text-center">{{ __('analytics.no_scores_recorded') }}</p>
    @endif
</div>
@endunless

{{-- Activity performance --}}
@if(count($activityPerformance))
<div class="bg-white rounded-xl shadow-sm p-6 mb-8">
    <h3 class="text-lg font-semibold text-[#182438] mb-1">{{ __('analytics.activity_performance') }}</h3>
    <p class="text-xs text-[#8b949d] mb-4">{{ __('analytics.ap_subtitle') }}</p>
    <canvas id="activityChart" height="240"></canvas>
</div>
@endif

{{-- Level progression table --}}
<div class="bg-white rounded-xl shadow-sm p-6">
    <h3 class="text-lg font-semibold text-[#182438] mb-1">{{ __('analytics.level_progress_map') }}</h3>
    <p class="text-xs text-[#8b949d] mb-4">
        @if($selectedStudent)
            {{ __('analytics.lpm_subtitle_selected', ['name' => $selectedStudent->first_name]) }}
        @else
            {{ __('analytics.lpm_subtitle_cohort') }}
        @endif
    </p>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[720px]">
            <thead class="bg-[#faf8f3]">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-[#182438] uppercase">{{ __('analytics.th_game') }}</th>
                    @for($l = 1; $l <= 8; $l++)
                        <th class="px-3 py-3 text-center text-xs font-semibold text-[#182438] uppercase">L{{ $l }}</th>
                    @endfor
                </tr>
            </thead>
            <tbody class="divide-y divide-[#ebe8e1]">
                @foreach($levelProgress as $game => $levels)
                <tr class="hover:bg-[#faf8f3] transition">
                    <td class="px-4 py-3 font-medium text-[#182438] capitalize">{{ $game }}</td>
                    @for($l = 1; $l <= 8; $l++)
                        <td class="px-3 py-3 text-center">
                            @php $count = $levels[$l] ?? 0; @endphp
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-xs font-bold
                                {{ $count === 0 ? 'bg-[#f4f2ec] text-[#c9c4b8]' : ($count >= 5 ? 'bg-[#68b82f] text-white' : 'bg-[#e2f4e8] text-[#3d7a1c]') }}">
                                {{ $count }}
                            </span>
                        </td>
                    @endfor
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script data-page-script>
const chartIfPresent = (id, config) => { const el = document.getElementById(id); if (el) new Chart(el, config); };
const attendanceRate = @json($attendanceRate);
const weeklyBreakdown = @json($weeklyBreakdown);
const performanceRadar = @json($performanceRadar);
const domainStatus = @json($domainStatus);
const ageDistribution = @json($ageDistribution);
const enrollmentTrend = @json($enrollmentTrend);
const gamePlayTrend = @json($gamePlayTrend);
const gamePerformance = @json($gamePerformance);
const activityPerformance = @json($activityPerformance);
const studentComparison = @json($studentComparison);

const PALETTE = ['#ff8555', '#258cc5', '#68b82f', '#f05d56', '#d9b348', '#e78cae', '#7c5cbf', '#16a085'];
const gridColor = 'rgba(24, 36, 56, 0.08)';

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
            x: { grid: { display: false } }
        }
    }
});

// Weekly breakdown
new Chart(document.getElementById('weeklyBreakdownChart'), {
    type: 'bar',
    data: {
        labels: weeklyBreakdown.map(r => r.day),
        datasets: [
            { label: L.present, data: weeklyBreakdown.map(r => r.present), backgroundColor: '#68b82f', borderRadius: 4 },
            { label: L.absent, data: weeklyBreakdown.map(r => r.absent), backgroundColor: '#f05d56', borderRadius: 4 },
            { label: L.late, data: weeklyBreakdown.map(r => r.late), backgroundColor: '#d9b348', borderRadius: 4 }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } },
        scales: { y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: gridColor } }, x: { grid: { display: false } } }
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
            backgroundColor: ['#68b82f', '#d9b348', '#f05d56'],
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

// Age distribution
chartIfPresent('ageDistChart', {
    type: 'bar',
    data: {
        labels: Object.keys(ageDistribution),
        datasets: [{
            label: L.students,
            data: Object.values(ageDistribution),
            backgroundColor: ['#258cc5', '#68b82f', '#ff8555', '#d9b348', '#e78cae'],
            borderRadius: 6
        }]
    },
    options: {
        responsive: true,
        indexAxis: 'y',
        plugins: { legend: { display: false } },
        scales: { x: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: gridColor } }, y: { grid: { display: false } } }
    }
});

// Gameplay trend: bars + line
new Chart(document.getElementById('gamePlayTrendChart'), {
    data: {
        labels: gamePlayTrend.map(r => r.week),
        datasets: [
            { type: 'bar', label: L.levels_played, data: gamePlayTrend.map(r => r.plays), backgroundColor: 'rgba(37, 140, 197, 0.55)', borderRadius: 4, yAxisID: 'y' },
            { type: 'line', label: L.avg_accuracy, data: gamePlayTrend.map(r => r.avg_pct), borderColor: '#68b82f', backgroundColor: '#68b82f', tension: 0.35, yAxisID: 'y1', pointRadius: 3 }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } },
        scales: {
            y: { beginAtZero: true, position: 'left', ticks: { precision: 0 }, grid: { color: gridColor }, title: { display: true, text: L.levels } },
            y1: { min: 0, max: 100, position: 'right', ticks: { callback: v => v + '%' }, grid: { display: false } },
            x: { grid: { display: false } }
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
            data: enrollmentTrend.map(r => r.total),
            borderColor: '#16a085',
            backgroundColor: 'rgba(22, 160, 133, 0.12)',
            fill: true,
            tension: 0.35,
            pointRadius: 4
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } },
        scales: { y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: gridColor } }, x: { grid: { display: false } } }
    }
});

// Student comparison
const sLabels = @json(\App\Services\AnalyticsService::GAME_TYPES);
chartIfPresent('studentComparisonChart', {
    type: 'bar',
    data: {
        labels: sLabels,
        datasets: studentComparison.map((s, i) => ({
            label: s.name,
            data: sLabels.map(l => s.scores[l] === null ? 0 : s.scores[l]),
            backgroundColor: PALETTE[i % PALETTE.length]
        }))
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } },
        scales: {
            y: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%' }, grid: { color: gridColor } },
            x: { grid: { display: false } }
        }
    }
});

// Activity performance
if (activityPerformance.length) {
    new Chart(document.getElementById('activityChart'), {
        type: 'bar',
        data: {
            labels: activityPerformance.map(a => a.type),
            datasets: [
                { label: L.avg_score, data: activityPerformance.map(a => a.avg_score), backgroundColor: '#258cc5', borderRadius: 4 },
                { label: L.attempts, data: activityPerformance.map(a => a.attempts), backgroundColor: '#ff8555', borderRadius: 4, yAxisID: 'y1' }
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } },
            scales: {
                y: { beginAtZero: true, grid: { color: gridColor } },
                y1: { beginAtZero: true, position: 'right', grid: { display: false }, ticks: { precision: 0 } },
                x: { grid: { display: false } }
            }
        }
    });
}
</script>
@endpush
