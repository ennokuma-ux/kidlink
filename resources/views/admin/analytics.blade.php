@extends('layouts.admin')

@section('title', __('analytics.title_admin'))
@section('page-title', __('analytics.page_title_admin'))

@section('content')
{{-- Charts size to the screen, not to the card's width: a full-width chart used to
     grow as tall as it was wide. Chart.js fills this box (maintainAspectRatio is off). --}}
<style>
    /* Padding keeps axis labels and legends off the card edge; the cards sit a full gap apart. */
    .chart-box { position: relative; box-sizing: border-box; width: 100%; max-width: 100%; min-width: 0; overflow: hidden; padding: 0.5rem 0.5rem 0.75rem; height: clamp(210px, 32vh, 340px); }
    .chart-box > canvas { max-width: 100%; }
    .chart-box--tall { height: clamp(250px, 40vh, 420px); }
</style>


<x-analytics-scope-bar route="admin.analytics.index" :scope="$scope" :section="$section" :student="$selectedStudent"
                       :sort="$sort" :shown="count($students)" :total="$totalStudents" :legend="$sectionLegend" />

{{-- Narrative overview built from the live figures --}}
<div class="bg-gradient-to-r from-[#fff0e6] to-[#fde8ef] border border-[#ffd9c2] rounded-xl p-5 mb-6">
    <h3 class="text-sm font-bold text-[#ff8555] uppercase tracking-wide mb-2">
        {{ $selectedStudent ? __('analytics.heading_student_overview') : __('analytics.heading_system_overview') }}
    </h3>
    <p class="text-[#5f6873] leading-relaxed">
        @if($selectedStudent)
            @php
                $adminAgeWord = trans_choice('analytics.year_word', (int) $averageAge);
                $adminGameWord = trans_choice('analytics.game_level_word', $summary['games_played']);
                $adminPointWord = trans_choice('analytics.point_word', $summary['points_awarded']);
            @endphp
            {{ __('analytics.admin_student_narr', [
                'name' => $selectedStudent->name,
                'section' => \App\Models\Student::sectionLabel($selectedStudent->class_name),
                'age' => $averageAge,
                'ageword' => $adminAgeWord,
                'games' => number_format($summary['games_played']),
                'gamelevels' => $adminGameWord,
                'pct' => $summary['avg_game_pct'],
                'points' => number_format($summary['points_awarded']),
                'pointword' => $adminPointWord,
                'rate' => $summary['attendance_rate'],
            ]) }}
            @if($recommendations)
                {{ __('analytics.intervention_recommended', ['needs' => $recommendations[0]['needs'], 'plan' => $recommendations[0]['plan']]) }}
            @else
                {{ __('analytics.no_interventions_now') }}
            @endif
        @else
            @php
                $systemActWord = trans_choice('analytics.enrolment_word', $system['active_enrollments']);
                $systemTeacherWord = trans_choice('analytics.teacher_word', $system['teachers']);
                $systemAdminWord = trans_choice('analytics.admin_word', $system['admins']);
                $systemPointWord = trans_choice('analytics.point_word', $system['points_awarded']);
            @endphp
            {{ __('analytics.system_head', [
                'active' => $system['active_enrollments'],
                'actword' => $systemActWord,
                'students' => $system['students_enrolled'],
                'age' => $averageAge,
                'teachers' => $system['teachers'],
                'teacherword' => $systemTeacherWord,
                'parents' => $system['parents'],
                'admins' => $system['admins'],
                'adminword' => $systemAdminWord,
                'levels' => number_format($system['game_levels_played']),
                'points' => number_format($system['points_awarded']),
                'pointword' => $systemPointWord,
                'pct' => $summary['avg_game_pct'],
                'rate' => $summary['attendance_rate'],
            ]) }}
            @if($needsSupport)
                {{ __('analytics.system_lowest', ['pct' => $needsSupport[0]['avg_pct']]) }}
            @endif
            @if($interventionCount > 0)
                {{ trans_choice('analytics.system_interventions', $interventionCount, ['count' => $interventionCount]) }}
            @endif
        @endif
    </p>
</div>

{{-- Stated outright, above the stats: a missing record cannot be seen in an average --}}
<x-intervention-panel :interventions="$interventions" />

{{-- Headline stats. A single-student view swaps the centre-wide counters for
     that child's own numbers, so the page never mixes the two scales. --}}
@if($selectedStudent)
<div class="grid grid-cols-2 lg:grid-cols-4 gap-8 mb-8">
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#1982c4]">
        <p class="text-[#5f6873] text-sm">{{ __('analytics.stat_game_levels_played') }}</p>
        <p class="text-3xl font-bold text-[#182438]">{{ number_format($summary['games_played']) }}</p>
        <p class="text-xs text-[#6b7280] mt-1">{{ __('analytics.stat_avg_accuracy_suffix', ['pct' => $summary['avg_game_pct']]) }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#8ac926]">
        <p class="text-[#5f6873] text-sm">{{ __('analytics.stat_attendance_rate') }}</p>
        <p class="text-3xl font-bold text-[#182438]">{{ $summary['attendance_rate'] }}%</p>
        <p class="text-xs text-[#6b7280] mt-1">{{ __('analytics.line_absences_late', ['abs' => $summary['absences'], 'late' => $summary['late_count']]) }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#ff8555]">
        <p class="text-[#5f6873] text-sm">{{ __('analytics.stat_points_awarded') }}</p>
        <p class="text-3xl font-bold text-[#182438]">{{ number_format($summary['points_awarded']) }}</p>
        <p class="text-xs text-[#6b7280] mt-1">{{ __('analytics.stat_all_time') }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#ffca3a]">
        <p class="text-[#5f6873] text-sm">{{ __('analytics.stat_age') }}</p>
        <p class="text-3xl font-bold text-[#182438]">{{ $averageAge }}</p>
        <p class="text-xs text-[#6b7280] mt-1">{{ \App\Models\Student::sectionLabel($selectedStudent->class_name) }}</p>
    </div>
</div>
@else
<div class="grid grid-cols-2 lg:grid-cols-4 gap-8 mb-8">
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#1982c4]">
        <p class="text-[#5f6873] text-sm">{{ __('analytics.stat_total_accounts') }}</p>
        <p class="text-3xl font-bold text-[#182438]">{{ $system['total_users'] }}</p>
        <p class="text-xs text-[#6b7280] mt-1">{{ __('analytics.stat_inactive', ['count' => $system['inactive_users']]) }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#8ac926]">
        <p class="text-[#5f6873] text-sm">{{ __('analytics.stat_active_enrolments') }}</p>
        <p class="text-3xl font-bold text-[#182438]">{{ $system['active_enrollments'] }}</p>
        <p class="text-xs text-[#6b7280] mt-1">{{ __('analytics.stat_of_students', ['count' => $system['students_enrolled']]) }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#ff8555]">
        <p class="text-[#5f6873] text-sm">{{ __('analytics.stat_game_levels_played') }}</p>
        <p class="text-3xl font-bold text-[#182438]">{{ number_format($system['game_levels_played']) }}</p>
        <p class="text-xs text-[#6b7280] mt-1">{{ __('analytics.stat_avg_accuracy_suffix', ['pct' => $summary['avg_game_pct']]) }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#ffca3a]">
        <p class="text-[#5f6873] text-sm">{{ __('analytics.stat_points_awarded') }}</p>
        <p class="text-3xl font-bold text-[#182438]">{{ number_format($system['points_awarded']) }}</p>
        <p class="text-xs text-[#6b7280] mt-1">{{ __('analytics.stat_all_time') }}</p>
    </div>
</div>
@endif

@unless($selectedStudent)
{{-- Data quality --}}
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <h3 class="text-lg font-semibold text-[#182438] mb-1">{{ __('analytics.dq_title') }}</h3>
    <p class="text-xs text-[#6b7280] mb-4">{{ __('analytics.dq_subtitle') }}</p>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($dataQuality as $check)
            <div class="p-4 rounded-lg border-l-4
                {{ $check['severity'] === 'high' ? 'bg-[#fde8ef] border-[#ff595e]' : ($check['severity'] === 'medium' ? 'bg-[#fff6e5] border-[#ffca3a]' : ($check['severity'] === 'low' ? 'bg-[#e1f0f8] border-[#1982c4]' : 'bg-[#ecf7d6] border-[#8ac926]')) }}">
                <p class="text-2xl font-bold text-[#182438]">{{ $check['count'] }}</p>
                <p class="text-xs text-[#5f6873] mt-1">{{ $check['label'] }}</p>
            </div>
        @endforeach
    </div>
</div>

{{-- Growth --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-1">{{ __('analytics.growth_title') }}</h3>
        <p class="text-xs text-[#6b7280] mb-4">{{ __('analytics.growth_subtitle') }}</p>
        <div class="chart-box"><canvas id="roleGrowthChart"></canvas></div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-1">{{ __('analytics.enrolment_trend_title') }}</h3>
        <p class="text-xs text-[#6b7280] mb-4">{{ __('analytics.enrolment_trend_subtitle_admin') }}</p>
        <div class="chart-box"><canvas id="enrollmentChart"></canvas></div>
    </div>
</div>
@endunless

{{-- Attendance + engagement --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-1">{{ __('analytics.centre_attendance_title') }}</h3>
        <p class="text-xs text-[#6b7280] mb-4">{{ __('analytics.centre_attendance_subtitle') }}</p>
        <div class="chart-box"><canvas id="attendanceChart"></canvas></div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-1">{{ __('analytics.weekly_trend_title') }}</h3>
        <p class="text-xs text-[#6b7280] mb-4">{{ __('analytics.weekly_subtitle_short') }}</p>
        <div class="chart-box"><canvas id="gamePlayTrendChart"></canvas></div>
    </div>
</div>

{{-- This week, day by day --}}
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <x-attendance-period-header :period="$attendancePeriod" :keep="$attendanceKeep" />
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
        <div class="chart-box"><canvas id="weeklyBreakdownChart"></canvas></div>
        <x-week-attendance :days="$weeklyBreakdown" :unit="$attendancePeriod['mode'] === 'month' ? 'week' : 'day'" />
    </div>
</div>

{{-- One child gets the ECCD domain chart; a group gets the game-popularity chart. Each
     belongs to only one of the two views, so neither shows in the other. --}}
<div class="grid grid-cols-1 gap-8 mb-8">
    @if($selectedStudent)
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-1">{{ __('analytics.domain_mastery_title') }}</h3>
        <p class="text-xs text-[#6b7280] mb-4">{{ __('analytics.domain_mastery_subtitle_admin') }}</p>
        <div class="chart-box"><canvas id="domainMasteryChart"></canvas></div>
    </div>
    @else
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-1">{{ __('analytics.game_popularity_title') }}</h3>
        <p class="text-xs text-[#6b7280] mb-4">{{ __('analytics.game_popularity_subtitle') }}</p>
        <div class="chart-box"><canvas id="gamePopularityChart"></canvas></div>
    </div>
    @endif
</div>

@unless($selectedStudent)
{{-- Teacher workload --}}
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <h3 class="text-lg font-semibold text-[#182438] mb-1">{{ __('analytics.teacher_caseload') }}</h3>
    <p class="text-xs text-[#6b7280] mb-4">{{ __('analytics.tc_subtitle') }}</p>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[760px]">
            <thead class="bg-[#fffbf0]">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-[#182438] uppercase">{{ __('analytics.th_teacher') }}</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-[#182438] uppercase">{{ __('analytics.th_students') }}</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-[#182438] uppercase">{{ __('analytics.th_active') }}</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-[#182438] uppercase">{{ __('analytics.th_levels_played') }}</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-[#182438] uppercase">{{ __('analytics.th_avg_accuracy') }}</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-[#182438] uppercase">{{ __('analytics.th_eccd_achieved') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-[#182438] uppercase">{{ __('analytics.th_rating') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#f1e7cc]">
                @forelse($teacherWorkload as $t)
                <tr class="hover:bg-[#fffbf0] transition">
                    <td class="px-4 py-3 font-medium text-[#182438]">{{ $t['name'] }}</td>
                    <td class="px-4 py-3 text-center text-[#5f6873]">{{ $t['students'] }}</td>
                    <td class="px-4 py-3 text-center text-[#5f6873]">{{ $t['active_students'] }}</td>
                    <td class="px-4 py-3 text-center text-[#5f6873]">{{ $t['plays'] }}</td>
                    <td class="px-4 py-3 text-center font-bold text-[#182438]">{{ $t['avg_pct'] }}%</td>
                    <td class="px-4 py-3 text-center text-[#5f6873]">{{ $t['eccd_pct'] }}%</td>
                    <td class="px-4 py-3">
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold
                            {{ $t['avg_pct'] >= 75 ? 'bg-[#ecf7d6] text-[#3d7a1c]' : ($t['avg_pct'] >= 50 ? 'bg-[#fff6e5] text-[#8a6d1f]' : 'bg-[#fde8ef] text-[#c0392f]') }}">
                            {{ $describe($t['avg_pct']) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-[#6b7280]">{{ __('analytics.no_teacher_accounts') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Student tables --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-1">{{ __('analytics.top_performing') }}</h3>
        <p class="text-xs text-[#6b7280] mb-4">{{ __('analytics.tp_subtitle') }}</p>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[640px]">
                <thead class="bg-[#fffbf0]">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-[#182438] uppercase">{{ __('analytics.th_student') }}</th>
                        @foreach(\App\Services\AnalyticsService::GAME_TYPES as $game)
                            <th class="px-2 py-2 text-center text-xs font-semibold text-[#5f6873] uppercase">{{ ucfirst($game) }}</th>
                        @endforeach
                        <th class="px-3 py-2 text-center text-xs font-semibold text-[#182438] uppercase">{{ __('analytics.th_avg') }}</th>
                        <th class="px-3 py-2 text-center text-xs font-semibold text-[#b8860b] uppercase">{{ __('analytics.th_points') }}</th>
                        <th class="px-3 py-2 text-center text-xs font-semibold text-[#182438] uppercase">{{ __('analytics.th_plays') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f1e7cc]">
                    @forelse($topStudents as $s)
                    <tr class="hover:bg-[#fffbf0] transition">
                        <td class="px-3 py-2.5">
                            <p class="font-medium text-[#182438] whitespace-nowrap">{{ $s['name'] }}</p>
                            <p class="text-xs text-[#6b7280] inline-flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full" style="background-color: {{ \App\Models\Student::colorForSection($s['class_name']) }}"></span>
                                {{ \App\Models\Student::sectionLabel($s['class_name']) }}
                            </p>
                        </td>
                        @foreach(\App\Services\AnalyticsService::GAME_TYPES as $game)
                            <td class="px-2 py-2.5 text-center text-sm"><x-score-cell :value="$s['games'][$game] ?? null" /></td>
                        @endforeach
                        <td class="px-3 py-2.5 text-center">
                            <span class="font-bold text-[#8ac926]">{{ $s['avg_pct'] }}%</span>
                        </td>
                        <td class="px-3 py-2.5 text-center font-bold text-[#b8860b]">{{ number_format($s['total_points'] ?? 0) }}</td>
                        <td class="px-3 py-2.5 text-center text-[#5f6873]">{{ $s['plays'] }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="px-3 py-8 text-center text-[#6b7280]">{{ __('analytics.no_game_data') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-1">{{ __('analytics.needing_support') }}</h3>
        <p class="text-xs text-[#6b7280] mb-4">{{ __('analytics.rs_subtitle') }}</p>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[700px]">
                <thead class="bg-[#fffbf0]">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-[#182438] uppercase">{{ __('analytics.th_student') }}</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-[#182438] uppercase">{{ __('analytics.th_teacher') }}</th>
                        @foreach(\App\Services\AnalyticsService::GAME_TYPES as $game)
                            <th class="px-2 py-2 text-center text-xs font-semibold text-[#5f6873] uppercase">{{ ucfirst($game) }}</th>
                        @endforeach
                        <th class="px-3 py-2 text-center text-xs font-semibold text-[#182438] uppercase">{{ __('analytics.th_avg') }}</th>
                        <th class="px-3 py-2 text-center text-xs font-semibold text-[#b8860b] uppercase">{{ __('analytics.th_points') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f1e7cc]">
                    @forelse($needsSupport as $s)
                    <tr class="hover:bg-[#fffbf0] transition">
                        <td class="px-3 py-2.5">
                            <p class="font-medium text-[#182438] whitespace-nowrap">{{ $s['name'] }}</p>
                            <p class="text-xs text-[#6b7280] inline-flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full" style="background-color: {{ \App\Models\Student::colorForSection($s['class_name']) }}"></span>
                                {{ \App\Models\Student::sectionLabel($s['class_name']) }}
                            </p>
                        </td>
                        <td class="px-3 py-2.5 text-xs text-[#5f6873]">{{ $s['teacher'] ?? __('analytics.unassigned') }}</td>
                        @foreach(\App\Services\AnalyticsService::GAME_TYPES as $game)
                            <td class="px-2 py-2.5 text-center text-sm"><x-score-cell :value="$s['games'][$game] ?? null" /></td>
                        @endforeach
                        <td class="px-3 py-2.5 text-center">
                            <span class="font-bold {{ $s['avg_pct'] < 50 ? 'text-[#ff595e]' : 'text-[#ffca3a]' }}">{{ $s['avg_pct'] }}%</span>
                        </td>
                        <td class="px-3 py-2.5 text-center font-bold text-[#b8860b]">{{ number_format($s['total_points'] ?? 0) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="px-3 py-8 text-center text-[#6b7280]">{{ __('analytics.no_game_data') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endunless

{{-- Interventions + class distribution --}}
<div class="grid grid-cols-1 {{ $selectedStudent ? '' : 'lg:grid-cols-2' }} gap-6">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-1">{{ __('analytics.centre_interventions') }}</h3>
        <p class="text-xs text-[#6b7280] mb-4">{{ __('analytics.ci_subtitle') }}</p>
        <div class="space-y-3 max-h-[340px] overflow-y-auto pr-1">
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
                    <p class="text-sm text-[#5f6873] mt-1"><span class="font-medium">{{ __('analytics.needs_label') }}</span> {{ $rec['needs'] }}</p>
                    <p class="text-sm text-[#5f6873]"><span class="font-medium">{{ __('analytics.plan_label') }}</span> {{ $rec['plan'] }}</p>
                </div>
            @empty
                <div class="p-6 rounded-lg bg-[#ecf7d6] border border-[#b8e0c2] text-center">
                    <p class="text-[#182438] font-semibold">{{ __('analytics.no_interventions_needed') }}</p>
                    <p class="text-sm text-[#5f6873] mt-1">{{ __('analytics.no_interventions_desc_admin') }}</p>
                </div>
            @endforelse
        </div>
    </div>

    @unless($selectedStudent)
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-1">{{ __('analytics.students_per_class') }}</h3>
        <p class="text-xs text-[#6b7280] mb-4">{{ __('analytics.spc_subtitle') }}</p>
        @if(count($classDistribution))
            <div class="chart-box chart-box--tall"><canvas id="classChart"></canvas></div>
        @else
            <p class="text-sm text-[#6b7280] py-12 text-center">{{ __('analytics.no_enrolments_yet') }}</p>
        @endif
    </div>
    @endunless
</div>
@endsection

@push('scripts')
<script src="{{ \App\Support\Asset::v('js/chart.umd.min.js') }}"></script>
<script data-page-script>
Chart.defaults.maintainAspectRatio = false;
const chartIfPresent = (id, config) => { const el = document.getElementById(id); if (el) new Chart(el, config); };
const roleGrowth = @json($roleGrowth);
const enrollmentTrend = @json($enrollmentTrend);
const attendanceRate = @json($attendanceRate);
const weeklyBreakdown = @json($weeklyBreakdown);
const gamePopularity = @json($gamePopularity);
const domainMastery = @json($domainMastery);
const gamePlayTrend = @json($gamePlayTrend);
const classDistribution = @json($classDistribution);

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
    'teachers': @json(__('analytics.chart_teachers')),
    'parents': @json(__('analytics.chart_parents')),
    'students': @json(__('analytics.chart_students')),
    'new_enrolments': @json(__('analytics.chart_new_enrolments')),
    'present': @json(__('analytics.chart_present')),
    'absent': @json(__('analytics.chart_absent')),
    'late': @json(__('analytics.chart_late')),
    'present_rate': @json(__('analytics.chart_present_rate')),
    'levels_played': @json(__('analytics.chart_levels_played')),
    'avg_accuracy': @json(__('analytics.chart_avg_accuracy')),
    'levels': @json(__('analytics.chart_levels')),
    'pct_achieved': @json(__('analytics.chart_pct_achieved')),
    'indicators_pct': @json(__('analytics.chart_indicators', ['pct' => ':pct', 'count' => ':count'])),
    'unique_students': @json(__('analytics.chart_unique_students')),
    'students_tooltip': @json(__('analytics.chart_students_tooltip', ['label' => ':label', 'count' => ':count'])),
};

chartIfPresent('roleGrowthChart', {
    type: 'bar',
    data: {
        labels: roleGrowth.map(r => r.month),
        datasets: [
            { label: L.teachers, data: shareOf(roleGrowth.map(r => r.teachers)), backgroundColor: '#1982c4', borderRadius: 4, maxBarThickness: 28 },
            { label: L.parents, data: shareOf(roleGrowth.map(r => r.parents)), backgroundColor: '#8ac926', borderRadius: 4, maxBarThickness: 28 },
            { label: L.students, data: shareOf(roleGrowth.map(r => r.students)), backgroundColor: '#ff8555', borderRadius: 4, maxBarThickness: 28 }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' }, tooltip: withCounts([roleGrowth.map(r => r.teachers), roleGrowth.map(r => r.parents), roleGrowth.map(r => r.students)]) },
        scales: { y: { min: 0, max: 100, ticks: pctTicks, grid: { color: gridColor } }, x: { grid: { display: false } } }
    }
});

chartIfPresent('enrollmentChart', {
    type: 'line',
    data: {
        labels: enrollmentTrend.map(r => r.month),
        datasets: [{
            label: L.new_enrolments,
            data: shareOf(enrollmentTrend.map(r => r.total)),
            borderColor: '#ff8555',
            backgroundColor: 'rgba(255, 133, 85, 0.12)',
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

// Each day's present / absent / late as a share of that day
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
        plugins: {
            legend: { position: 'bottom' },
            tooltip: withCounts([weeklyBreakdown.map(r => r.present), weeklyBreakdown.map(r => r.absent), weeklyBreakdown.map(r => r.late)])
        },
        scales: { y: { min: 0, max: 100, ticks: pctTicks, grid: { color: gridColor } }, x: { grid: { display: false } } }
    }
});

// Monthly attendance: present / absent / late as stacked shares of the month (always 100%)
const monthTotals = attendanceRate.map(r => (+r.present || 0) + (+r.absent || 0) + (+r.late || 0));
const monthShare = key => attendanceRate.map((r, i) => monthTotals[i] ? Math.round(((+r[key] || 0) / monthTotals[i]) * 1000) / 10 : 0);
new Chart(document.getElementById('attendanceChart'), {
    data: {
        labels: attendanceRate.map(r => r.month),
        datasets: [
            { type: 'bar', label: L.present, data: monthShare('present'), backgroundColor: 'rgba(104, 184, 47, 0.6)', borderRadius: 4, stack: 'a', maxBarThickness: 36 },
            { type: 'bar', label: L.absent, data: monthShare('absent'), backgroundColor: 'rgba(240, 93, 86, 0.6)', borderRadius: 4, stack: 'a', maxBarThickness: 36 },
            { type: 'bar', label: L.late, data: monthShare('late'), backgroundColor: 'rgba(217, 179, 72, 0.6)', borderRadius: 4, stack: 'a', maxBarThickness: 36 },
            { type: 'line', label: L.present_rate, data: attendanceRate.map(r => r.rate), borderColor: '#182438', borderWidth: 2, tension: 0.35, pointRadius: 3 }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom' },
            tooltip: { callbacks: { label: ctx => ctx.datasetIndex < 3
                ? ctx.dataset.label + ': ' + ctx.raw + '% (' + [attendanceRate.map(r => r.present), attendanceRate.map(r => r.absent), attendanceRate.map(r => r.late)][ctx.datasetIndex][ctx.dataIndex] + ')'
                : ctx.dataset.label + ': ' + ctx.raw + '%' } }
        },
        scales: {
            y: { min: 0, max: 100, stacked: true, ticks: pctTicks, grid: { color: gridColor } },
            x: { stacked: true, grid: { display: false } }
        }
    }
});

new Chart(document.getElementById('gamePlayTrendChart'), {
    data: {
        labels: gamePlayTrend.map(r => r.week),
        datasets: [
            { type: 'bar', label: L.levels_played, data: shareOf(gamePlayTrend.map(r => r.plays)), backgroundColor: 'rgba(37, 140, 197, 0.55)', borderRadius: 4, maxBarThickness: 36, yAxisID: 'y' },
            { type: 'line', label: L.avg_accuracy, data: gamePlayTrend.map(r => r.avg_pct), borderColor: '#8ac926', backgroundColor: '#8ac926', tension: 0.35, yAxisID: 'y1', pointRadius: 3 }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom' },
            tooltip: { callbacks: { label: ctx => ctx.datasetIndex === 0
                ? ctx.dataset.label + ': ' + ctx.raw + '% (' + gamePlayTrend[ctx.dataIndex].plays + ')'
                : ctx.dataset.label + ': ' + ctx.raw + '%' } }
        },
        scales: {
            y: { min: 0, max: 100, position: 'left', ticks: pctTicks, grid: { color: gridColor }, title: { display: true, text: L.levels + ' (%)' } },
            y1: { min: 0, max: 100, position: 'right', ticks: { callback: v => v + '%' }, grid: { display: false } },
            x: { grid: { display: false } }
        }
    }
});

@if($selectedStudent)
new Chart(document.getElementById('domainMasteryChart'), {
    type: 'bar',
    data: {
        labels: domainMastery.map(d => d.domain),
        datasets: [{
            label: L.pct_achieved,
            data: domainMastery.map(d => d.pct),
            backgroundColor: domainMastery.map(d => d.pct >= 75 ? '#8ac926' : (d.pct >= 50 ? '#ffca3a' : '#ff595e')),
            borderRadius: 6
        }]
    },
    options: {
        responsive: true,
        indexAxis: 'y',
        plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: ctx => L.indicators_pct.replace(':pct', ctx.raw).replace(':count', domainMastery[ctx.dataIndex].assessed) } }
        },
        scales: {
            x: { min: 0, max: 100, ticks: { callback: v => v + '%' }, grid: { color: gridColor } },
            y: { grid: { display: false }, ticks: { font: { size: 10 } } }
        }
    }
});

@endif

chartIfPresent('gamePopularityChart', {
    data: {
        labels: Object.keys(gamePopularity),
        datasets: [
            { type: 'bar', label: L.unique_students, data: shareOf(Object.values(gamePopularity).map(g => g.students)), backgroundColor: '#ff8555', borderRadius: 4, maxBarThickness: 36, yAxisID: 'y' },
            { type: 'line', label: L.avg_accuracy, data: Object.values(gamePopularity).map(g => g.avg_pct), borderColor: '#1982c4', backgroundColor: '#1982c4', tension: 0.35, yAxisID: 'y1', pointRadius: 4 }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom' },
            tooltip: { callbacks: { label: ctx => ctx.datasetIndex === 0
                ? ctx.dataset.label + ': ' + ctx.raw + '% (' + Object.values(gamePopularity)[ctx.dataIndex].students + ')'
                : ctx.dataset.label + ': ' + ctx.raw + '%' } }
        },
        scales: {
            y: { min: 0, max: 100, position: 'left', ticks: pctTicks, grid: { color: gridColor }, title: { display: true, text: L.students + ' (%)' } },
            y1: { min: 0, max: 100, position: 'right', ticks: { callback: v => v + '%' }, grid: { display: false } },
            x: { grid: { display: false }, ticks: { capitalize: true } }
        }
    }
});

if (classDistribution.length) {
    chartIfPresent('classChart', {
        type: 'doughnut',
        data: {
            labels: classDistribution.map(c => c.class_name),
            datasets: [{
                data: classDistribution.map(c => c.total),
                // Each section keeps its own colour, matching the legend above.
                backgroundColor: classDistribution.map(c => c.color),
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            cutout: '55%',
            plugins: {
                legend: { position: 'right' },
                tooltip: { callbacks: { label: ctx => L.students_tooltip.replace(':label', ctx.label).replace(':count', ctx.raw) } }
            }
        }
    });
}
</script>
@endpush
