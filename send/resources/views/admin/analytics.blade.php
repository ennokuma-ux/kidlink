@extends('layouts.admin')

@section('title', __('analytics.title_admin'))
@section('page-title', __('analytics.page_title_admin'))

@section('content')

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
<div class="grid grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#258cc5]">
        <p class="text-[#5f6873] text-sm">{{ __('analytics.stat_game_levels_played') }}</p>
        <p class="text-3xl font-bold text-[#182438]">{{ number_format($summary['games_played']) }}</p>
        <p class="text-xs text-[#8b949d] mt-1">{{ __('analytics.stat_avg_accuracy_suffix', ['pct' => $summary['avg_game_pct']]) }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#68b82f]">
        <p class="text-[#5f6873] text-sm">{{ __('analytics.stat_attendance_rate') }}</p>
        <p class="text-3xl font-bold text-[#182438]">{{ $summary['attendance_rate'] }}%</p>
        <p class="text-xs text-[#8b949d] mt-1">{{ __('analytics.line_absences_late', ['abs' => $summary['absences'], 'late' => $summary['late_count']]) }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#ff8555]">
        <p class="text-[#5f6873] text-sm">{{ __('analytics.stat_points_awarded') }}</p>
        <p class="text-3xl font-bold text-[#182438]">{{ number_format($summary['points_awarded']) }}</p>
        <p class="text-xs text-[#8b949d] mt-1">{{ __('analytics.stat_all_time') }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#d9b348]">
        <p class="text-[#5f6873] text-sm">{{ __('analytics.stat_age') }}</p>
        <p class="text-3xl font-bold text-[#182438]">{{ $averageAge }}</p>
        <p class="text-xs text-[#8b949d] mt-1">{{ \App\Models\Student::sectionLabel($selectedStudent->class_name) }}</p>
    </div>
</div>
@else
<div class="grid grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#258cc5]">
        <p class="text-[#5f6873] text-sm">{{ __('analytics.stat_total_accounts') }}</p>
        <p class="text-3xl font-bold text-[#182438]">{{ $system['total_users'] }}</p>
        <p class="text-xs text-[#8b949d] mt-1">{{ __('analytics.stat_inactive', ['count' => $system['inactive_users']]) }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#68b82f]">
        <p class="text-[#5f6873] text-sm">{{ __('analytics.stat_active_enrolments') }}</p>
        <p class="text-3xl font-bold text-[#182438]">{{ $system['active_enrollments'] }}</p>
        <p class="text-xs text-[#8b949d] mt-1">{{ __('analytics.stat_of_students', ['count' => $system['students_enrolled']]) }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#ff8555]">
        <p class="text-[#5f6873] text-sm">{{ __('analytics.stat_game_levels_played') }}</p>
        <p class="text-3xl font-bold text-[#182438]">{{ number_format($system['game_levels_played']) }}</p>
        <p class="text-xs text-[#8b949d] mt-1">{{ __('analytics.stat_avg_accuracy_suffix', ['pct' => $summary['avg_game_pct']]) }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#d9b348]">
        <p class="text-[#5f6873] text-sm">{{ __('analytics.stat_points_awarded') }}</p>
        <p class="text-3xl font-bold text-[#182438]">{{ number_format($system['points_awarded']) }}</p>
        <p class="text-xs text-[#8b949d] mt-1">{{ __('analytics.stat_all_time') }}</p>
    </div>
</div>
@endif

@unless($selectedStudent)
{{-- Data quality --}}
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <h3 class="text-lg font-semibold text-[#182438] mb-1">{{ __('analytics.dq_title') }}</h3>
    <p class="text-xs text-[#8b949d] mb-4">{{ __('analytics.dq_subtitle') }}</p>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($dataQuality as $check)
            <div class="p-4 rounded-lg border-l-4
                {{ $check['severity'] === 'high' ? 'bg-[#fde8ef] border-[#f05d56]' : ($check['severity'] === 'medium' ? 'bg-[#fff6e5] border-[#d9b348]' : ($check['severity'] === 'low' ? 'bg-[#e1f0f8] border-[#258cc5]' : 'bg-[#e2f4e8] border-[#68b82f]')) }}">
                <p class="text-2xl font-bold text-[#182438]">{{ $check['count'] }}</p>
                <p class="text-xs text-[#5f6873] mt-1">{{ $check['label'] }}</p>
            </div>
        @endforeach
    </div>
</div>

{{-- Growth --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-1">{{ __('analytics.growth_title') }}</h3>
        <p class="text-xs text-[#8b949d] mb-4">{{ __('analytics.growth_subtitle') }}</p>
        <canvas id="roleGrowthChart" height="240"></canvas>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-1">{{ __('analytics.enrolment_trend_title') }}</h3>
        <p class="text-xs text-[#8b949d] mb-4">{{ __('analytics.enrolment_trend_subtitle_admin') }}</p>
        <canvas id="enrollmentChart" height="240"></canvas>
    </div>
</div>
@endunless

{{-- Attendance + engagement --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-1">{{ __('analytics.centre_attendance_title') }}</h3>
        <p class="text-xs text-[#8b949d] mb-4">{{ __('analytics.centre_attendance_subtitle') }}</p>
        <canvas id="attendanceChart" height="240"></canvas>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-1">{{ __('analytics.weekly_trend_title') }}</h3>
        <p class="text-xs text-[#8b949d] mb-4">{{ __('analytics.weekly_subtitle_short') }}</p>
        <canvas id="gamePlayTrendChart" height="240"></canvas>
    </div>
</div>

{{-- This week, day by day --}}
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <x-attendance-period-header :period="$attendancePeriod" :keep="$attendanceKeep" />
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
        <canvas id="weeklyBreakdownChart" height="220"></canvas>
        <x-week-attendance :days="$weeklyBreakdown" :unit="$attendancePeriod['mode'] === 'month' ? 'week' : 'day'" />
    </div>
</div>

{{-- ECCD domain mastery: only makes sense for one selected student --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    @if($selectedStudent)
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-1">{{ __('analytics.domain_mastery_title') }}</h3>
        <p class="text-xs text-[#8b949d] mb-4">{{ __('analytics.domain_mastery_subtitle_admin') }}</p>
        <canvas id="domainMasteryChart" height="280"></canvas>
    </div>
    @else
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center justify-center">
        <div class="text-center max-w-md">
            <p class="font-semibold text-[#182438]">{{ __('analytics.domain_selection_title') }}</p>
            <p class="text-sm text-[#5f6873] mt-1">{{ __('analytics.domain_selection_prompt') }}</p>
            <p class="text-xs text-[#8b949d] mt-1">{{ __('analytics.domain_selection_hint') }}</p>
            <button type="button" @click="$dispatch('open-student-chooser')" class="inline-block mt-3 px-4 py-2 rounded-lg bg-[#ff8555] text-white text-sm font-semibold hover:bg-[#f47548]">{{ __('analytics.domain_choose_student') }}</button>
            <x-student-chooser variant="none" :students="$studentOptions" :action="route('admin.analytics.index')" param="student" />
        </div>
    </div>
    @endif
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-1">{{ __('analytics.game_popularity_title') }}</h3>
        <p class="text-xs text-[#8b949d] mb-4">{{ __('analytics.game_popularity_subtitle') }}</p>
        <canvas id="gamePopularityChart" height="280"></canvas>
    </div>
</div>

@unless($selectedStudent)
{{-- Teacher workload --}}
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <h3 class="text-lg font-semibold text-[#182438] mb-1">{{ __('analytics.teacher_caseload') }}</h3>
    <p class="text-xs text-[#8b949d] mb-4">{{ __('analytics.tc_subtitle') }}</p>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[760px]">
            <thead class="bg-[#faf8f3]">
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
            <tbody class="divide-y divide-[#ebe8e1]">
                @forelse($teacherWorkload as $t)
                <tr class="hover:bg-[#faf8f3] transition">
                    <td class="px-4 py-3 font-medium text-[#182438]">{{ $t['name'] }}</td>
                    <td class="px-4 py-3 text-center text-[#5f6873]">{{ $t['students'] }}</td>
                    <td class="px-4 py-3 text-center text-[#5f6873]">{{ $t['active_students'] }}</td>
                    <td class="px-4 py-3 text-center text-[#5f6873]">{{ $t['plays'] }}</td>
                    <td class="px-4 py-3 text-center font-bold text-[#182438]">{{ $t['avg_pct'] }}%</td>
                    <td class="px-4 py-3 text-center text-[#5f6873]">{{ $t['eccd_pct'] }}%</td>
                    <td class="px-4 py-3">
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold
                            {{ $t['avg_pct'] >= 75 ? 'bg-[#e2f4e8] text-[#3d7a1c]' : ($t['avg_pct'] >= 50 ? 'bg-[#fff6e5] text-[#8a6d1f]' : 'bg-[#fde8ef] text-[#c0392f]') }}">
                            {{ $describe($t['avg_pct']) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-[#8b949d]">{{ __('analytics.no_teacher_accounts') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Student tables --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-1">{{ __('analytics.top_performing') }}</h3>
        <p class="text-xs text-[#8b949d] mb-4">{{ __('analytics.tp_subtitle') }}</p>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[640px]">
                <thead class="bg-[#faf8f3]">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-[#182438] uppercase">{{ __('analytics.th_student') }}</th>
                        @foreach(\App\Services\AnalyticsService::GAME_TYPES as $game)
                            <th class="px-2 py-2 text-center text-[11px] font-semibold text-[#5f6873] uppercase">{{ ucfirst($game) }}</th>
                        @endforeach
                        <th class="px-3 py-2 text-center text-xs font-semibold text-[#182438] uppercase">{{ __('analytics.th_avg') }}</th>
                        <th class="px-3 py-2 text-center text-xs font-semibold text-[#b8860b] uppercase">{{ __('analytics.th_points') }}</th>
                        <th class="px-3 py-2 text-center text-xs font-semibold text-[#182438] uppercase">{{ __('analytics.th_plays') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#ebe8e1]">
                    @forelse($topStudents as $s)
                    <tr class="hover:bg-[#faf8f3] transition">
                        <td class="px-3 py-2.5">
                            <p class="font-medium text-[#182438] whitespace-nowrap">{{ $s['name'] }}</p>
                            <p class="text-xs text-[#8b949d] inline-flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full" style="background-color: {{ \App\Models\Student::colorForSection($s['class_name']) }}"></span>
                                {{ \App\Models\Student::sectionLabel($s['class_name']) }}
                            </p>
                        </td>
                        @foreach(\App\Services\AnalyticsService::GAME_TYPES as $game)
                            <td class="px-2 py-2.5 text-center text-sm"><x-score-cell :value="$s['games'][$game] ?? null" /></td>
                        @endforeach
                        <td class="px-3 py-2.5 text-center">
                            <span class="font-bold text-[#68b82f]">{{ $s['avg_pct'] }}%</span>
                        </td>
                        <td class="px-3 py-2.5 text-center font-bold text-[#b8860b]">{{ number_format($s['total_points'] ?? 0) }}</td>
                        <td class="px-3 py-2.5 text-center text-[#5f6873]">{{ $s['plays'] }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="px-3 py-8 text-center text-[#8b949d]">{{ __('analytics.no_game_data') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-1">{{ __('analytics.needing_support') }}</h3>
        <p class="text-xs text-[#8b949d] mb-4">{{ __('analytics.rs_subtitle') }}</p>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[700px]">
                <thead class="bg-[#faf8f3]">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-[#182438] uppercase">{{ __('analytics.th_student') }}</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-[#182438] uppercase">{{ __('analytics.th_teacher') }}</th>
                        @foreach(\App\Services\AnalyticsService::GAME_TYPES as $game)
                            <th class="px-2 py-2 text-center text-[11px] font-semibold text-[#5f6873] uppercase">{{ ucfirst($game) }}</th>
                        @endforeach
                        <th class="px-3 py-2 text-center text-xs font-semibold text-[#182438] uppercase">{{ __('analytics.th_avg') }}</th>
                        <th class="px-3 py-2 text-center text-xs font-semibold text-[#b8860b] uppercase">{{ __('analytics.th_points') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#ebe8e1]">
                    @forelse($needsSupport as $s)
                    <tr class="hover:bg-[#faf8f3] transition">
                        <td class="px-3 py-2.5">
                            <p class="font-medium text-[#182438] whitespace-nowrap">{{ $s['name'] }}</p>
                            <p class="text-xs text-[#8b949d] inline-flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full" style="background-color: {{ \App\Models\Student::colorForSection($s['class_name']) }}"></span>
                                {{ \App\Models\Student::sectionLabel($s['class_name']) }}
                            </p>
                        </td>
                        <td class="px-3 py-2.5 text-xs text-[#5f6873]">{{ $s['teacher'] ?? __('analytics.unassigned') }}</td>
                        @foreach(\App\Services\AnalyticsService::GAME_TYPES as $game)
                            <td class="px-2 py-2.5 text-center text-sm"><x-score-cell :value="$s['games'][$game] ?? null" /></td>
                        @endforeach
                        <td class="px-3 py-2.5 text-center">
                            <span class="font-bold {{ $s['avg_pct'] < 50 ? 'text-[#f05d56]' : 'text-[#d9b348]' }}">{{ $s['avg_pct'] }}%</span>
                        </td>
                        <td class="px-3 py-2.5 text-center font-bold text-[#b8860b]">{{ number_format($s['total_points'] ?? 0) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="px-3 py-8 text-center text-[#8b949d]">{{ __('analytics.no_game_data') }}</td></tr>
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
        <p class="text-xs text-[#8b949d] mb-4">{{ __('analytics.ci_subtitle') }}</p>
        <div class="space-y-3 max-h-[340px] overflow-y-auto pr-1">
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
                    <p class="text-sm text-[#5f6873] mt-1"><span class="font-medium">{{ __('analytics.needs_label') }}</span> {{ $rec['needs'] }}</p>
                    <p class="text-sm text-[#5f6873]"><span class="font-medium">{{ __('analytics.plan_label') }}</span> {{ $rec['plan'] }}</p>
                </div>
            @empty
                <div class="p-6 rounded-lg bg-[#e2f4e8] border border-[#b8e0c2] text-center">
                    <p class="text-[#182438] font-semibold">{{ __('analytics.no_interventions_needed') }}</p>
                    <p class="text-sm text-[#5f6873] mt-1">{{ __('analytics.no_interventions_desc_admin') }}</p>
                </div>
            @endforelse
        </div>
    </div>

    @unless($selectedStudent)
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-1">{{ __('analytics.students_per_class') }}</h3>
        <p class="text-xs text-[#8b949d] mb-4">{{ __('analytics.spc_subtitle') }}</p>
        @if(count($classDistribution))
            <canvas id="classChart" height="300"></canvas>
        @else
            <p class="text-sm text-[#8b949d] py-12 text-center">{{ __('analytics.no_enrolments_yet') }}</p>
        @endif
    </div>
    @endunless
</div>
@endsection

@push('scripts')
<script data-page-script>
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
            { label: L.teachers, data: roleGrowth.map(r => r.teachers), backgroundColor: '#258cc5', borderRadius: 4 },
            { label: L.parents, data: roleGrowth.map(r => r.parents), backgroundColor: '#68b82f', borderRadius: 4 },
            { label: L.students, data: roleGrowth.map(r => r.students), backgroundColor: '#ff8555', borderRadius: 4 }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } },
        scales: { y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: gridColor } }, x: { grid: { display: false } } }
    }
});

chartIfPresent('enrollmentChart', {
    type: 'line',
    data: {
        labels: enrollmentTrend.map(r => r.month),
        datasets: [{
            label: L.new_enrolments,
            data: enrollmentTrend.map(r => r.total),
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
        scales: { y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: gridColor } }, x: { grid: { display: false } } }
    }
});

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

new Chart(document.getElementById('attendanceChart'), {
    data: {
        labels: attendanceRate.map(r => r.month),
        datasets: [
            { type: 'bar', label: L.present, data: attendanceRate.map(r => r.present), backgroundColor: 'rgba(104, 184, 47, 0.6)', borderRadius: 4, stack: 'a' },
            { type: 'bar', label: L.absent, data: attendanceRate.map(r => r.absent), backgroundColor: 'rgba(240, 93, 86, 0.6)', borderRadius: 4, stack: 'a' },
            { type: 'bar', label: L.late, data: attendanceRate.map(r => r.late), backgroundColor: 'rgba(217, 179, 72, 0.6)', borderRadius: 4, stack: 'a' },
            { type: 'line', label: L.present_rate, data: attendanceRate.map(r => r.rate), borderColor: '#182438', borderWidth: 2, tension: 0.35, pointRadius: 3, yAxisID: 'y1' }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } },
        scales: {
            y: { beginAtZero: true, stacked: true, ticks: { precision: 0 }, grid: { color: gridColor } },
            y1: { min: 0, max: 100, position: 'right', ticks: { callback: v => v + '%' }, grid: { display: false } },
            x: { stacked: true, grid: { display: false } }
        }
    }
});

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

@if($selectedStudent)
new Chart(document.getElementById('domainMasteryChart'), {
    type: 'bar',
    data: {
        labels: domainMastery.map(d => d.domain),
        datasets: [{
            label: L.pct_achieved,
            data: domainMastery.map(d => d.pct),
            backgroundColor: domainMastery.map(d => d.pct >= 75 ? '#68b82f' : (d.pct >= 50 ? '#d9b348' : '#f05d56')),
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

new Chart(document.getElementById('gamePopularityChart'), {
    data: {
        labels: Object.keys(gamePopularity),
        datasets: [
            { type: 'bar', label: L.unique_students, data: Object.values(gamePopularity).map(g => g.students), backgroundColor: '#ff8555', borderRadius: 4, yAxisID: 'y' },
            { type: 'line', label: L.avg_accuracy, data: Object.values(gamePopularity).map(g => g.avg_pct), borderColor: '#258cc5', backgroundColor: '#258cc5', tension: 0.35, yAxisID: 'y1', pointRadius: 4 }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } },
        scales: {
            y: { beginAtZero: true, position: 'left', ticks: { precision: 0 }, grid: { color: gridColor }, title: { display: true, text: L.students } },
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
