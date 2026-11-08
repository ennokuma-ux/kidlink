

<?php $__env->startSection('title', __('page.dashboard')); ?>
<?php $__env->startSection('page-title', __('page.teacher_dashboard')); ?>

<?php $__env->startSection('content'); ?>

<style>
    /* Padding keeps axis labels and legends off the card edge; the cards sit a full gap apart. */
    .chart-box { position: relative; box-sizing: border-box; width: 100%; max-width: 100%; min-width: 0; overflow: hidden; padding: 0.5rem 0.5rem 0.75rem; height: clamp(210px, 32vh, 340px); }
    .chart-box > canvas { max-width: 100%; }
    .chart-box--tall { height: clamp(250px, 40vh, 420px); }
</style>

<?php if (! ($scope === 'individual')): ?>
    <?php echo $__env->make('teacher.partials.headline-cards', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php endif; ?>

<?php $teacherScopeStudent = $selectedStudent; ?>
<?php if (isset($component)) { $__componentOriginalf8e0d1960dd92790506a658880aba4ae = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8e0d1960dd92790506a658880aba4ae = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics-scope-bar','data' => ['route' => 'teacher.dashboard','scope' => $scope,'section' => $section,'student' => $teacherScopeStudent,'sort' => $sort,'shown' => count($students),'total' => $totalStudents,'legend' => $sectionLegend]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics-scope-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['route' => 'teacher.dashboard','scope' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($scope),'section' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($section),'student' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($teacherScopeStudent),'sort' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($sort),'shown' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(count($students)),'total' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($totalStudents),'legend' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($sectionLegend)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf8e0d1960dd92790506a658880aba4ae)): ?>
<?php $attributes = $__attributesOriginalf8e0d1960dd92790506a658880aba4ae; ?>
<?php unset($__attributesOriginalf8e0d1960dd92790506a658880aba4ae); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf8e0d1960dd92790506a658880aba4ae)): ?>
<?php $component = $__componentOriginalf8e0d1960dd92790506a658880aba4ae; ?>
<?php unset($__componentOriginalf8e0d1960dd92790506a658880aba4ae); ?>
<?php endif; ?>


<div class="bg-gradient-to-r from-[#ffe9ea] to-[#fde8ef] border border-[#ffd9c2] rounded-xl p-5 mb-6">
    <h3 class="text-sm font-bold text-[#ff595e] uppercase tracking-wide mb-2">
        <?php echo e($selectedStudent ? __('analytics.heading_student_summary') : __('analytics.heading_cohort_summary')); ?>

    </h3>
    <p class="text-[#5f6873] leading-relaxed">
        <?php if($selectedStudent): ?>
            <?php if($summary['students'] > 0): ?>
                <?php
                    $studentAgeWord = trans_choice('analytics.year_word', $selectedStudent->age);
                    $studentGameWord = trans_choice('analytics.game_level_word', $summary['games_played']);
                ?>
                <?php echo e(__('analytics.student_narr', [
                    'name' => $selectedStudent->name,
                    'section' => \App\Models\Student::sectionLabel($selectedStudent->class_name),
                    'age' => $selectedStudent->age,
                    'ageword' => $studentAgeWord,
                    'games' => $summary['games_played'],
                    'gamelevels' => $studentGameWord,
                    'pct' => $summary['avg_game_pct'],
                    'points' => number_format($summary['points_awarded']),
                    'rate' => $summary['attendance_rate'],
                ])); ?>

                <?php if($summary['absences'] > 0 && $summary['late_count'] > 0): ?>
                    <?php echo e(__('analytics.attclause_both', [
                        'abs' => $summary['absences'],
                        'absencesword' => trans_choice('analytics.absence_word', $summary['absences']),
                        'late' => $summary['late_count'],
                        'arrivalsword' => trans_choice('analytics.arrival_word', $summary['late_count']),
                    ])); ?>

                <?php elseif($summary['absences'] > 0): ?>
                    <?php echo e(__('analytics.attclause_abs', [
                        'abs' => $summary['absences'],
                        'absencesword' => trans_choice('analytics.absence_word', $summary['absences']),
                    ])); ?>

                <?php else: ?>
                    <?php echo e(__('analytics.attclause_none')); ?>

                <?php endif; ?>
                <?php if($studentInterventions && collect($studentInterventions)->contains(fn ($f) => $f['intervention_required'])): ?>
                    <?php echo e(__('analytics.intervention_required', ['reasons' => collect(collect($studentInterventions)->firstWhere('intervention_required', true)['reasons'])->pluck('label')->implode('; ')])); ?>

                <?php elseif($recommendations): ?>
                    <?php echo e(__('analytics.intervention_recommended', ['needs' => $recommendations[0]['needs'], 'plan' => $recommendations[0]['plan']])); ?>

                <?php else: ?>
                    <?php echo e(__('analytics.no_interventions_now')); ?>

                <?php endif; ?>
            <?php else: ?>
                <?php echo e(__('analytics.no_records_yet')); ?>

            <?php endif; ?>
        <?php elseif($summary['students'] > 0): ?>
            <?php
                $cohortStudWord = trans_choice('analytics.student_word', $summary['students']);
                $cohortActWord = trans_choice('analytics.active_word', $summary['active_students']);
                $cohortGameWord = trans_choice('analytics.game_level_word', $summary['games_played']);
            ?>
            <?php echo e(__('analytics.cohort_head', [
                'students' => $summary['students'],
                'studword' => $cohortStudWord,
                'active' => $summary['active_students'],
                'actword' => $cohortActWord,
                'games' => $summary['games_played'],
                'gamelevels' => $cohortGameWord,
                'pct' => $summary['avg_game_pct'],
                'rate' => $summary['attendance_rate'],
            ])); ?>

            <?php if($summary['absences'] > 0 && $summary['late_count'] > 0): ?>
                <?php echo e(__('analytics.attclause_both', [
                    'abs' => $summary['absences'],
                    'absencesword' => trans_choice('analytics.absence_word', $summary['absences']),
                    'late' => $summary['late_count'],
                    'arrivalsword' => trans_choice('analytics.arrival_word', $summary['late_count']),
                ])); ?>

            <?php elseif($summary['absences'] > 0): ?>
                <?php echo e(__('analytics.attclause_abs', [
                    'abs' => $summary['absences'],
                    'absencesword' => trans_choice('analytics.absence_word', $summary['absences']),
                ])); ?>

            <?php else: ?>
                <?php echo e(__('analytics.attclause_none')); ?>

            <?php endif; ?>
            <?php if($interventionCount > 0): ?>
                <?php echo e(trans_choice('analytics.cohort_interventions', $interventionCount, ['count' => $interventionCount])); ?>

            <?php elseif($recommendations): ?>
                <?php echo e(trans_choice('analytics.cohort_recommended', count($recommendations), ['count' => count($recommendations)])); ?>

            <?php else: ?>
                <?php echo e(__('analytics.cohort_none')); ?>

            <?php endif; ?>
        <?php else: ?>
            <?php echo e(__('analytics.no_students_yet')); ?>

        <?php endif; ?>
    </p>
</div>


<?php if (isset($component)) { $__componentOriginal96c468e28549bbf4819278dfe26915e3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal96c468e28549bbf4819278dfe26915e3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.intervention-panel','data' => ['interventions' => $interventions]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('intervention-panel'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['interventions' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($interventions)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal96c468e28549bbf4819278dfe26915e3)): ?>
<?php $attributes = $__attributesOriginal96c468e28549bbf4819278dfe26915e3; ?>
<?php unset($__attributesOriginal96c468e28549bbf4819278dfe26915e3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal96c468e28549bbf4819278dfe26915e3)): ?>
<?php $component = $__componentOriginal96c468e28549bbf4819278dfe26915e3; ?>
<?php unset($__componentOriginal96c468e28549bbf4819278dfe26915e3); ?>
<?php endif; ?>


<div class="grid grid-cols-2 lg:grid-cols-4 gap-8 mb-10">
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#1982c4]">
        <p class="text-[#5f6873] text-sm"><?php echo e(__('analytics.stat_active_students')); ?></p>
        <p class="text-3xl font-bold text-[#182438]"><?php echo e($summary['active_students']); ?></p>
        <p class="text-xs text-[#8b949d] mt-1"><?php echo e(__('analytics.stat_of_enrolled', ['count' => $summary['students']])); ?></p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#8ac926]">
        <p class="text-[#5f6873] text-sm"><?php echo e(__('analytics.stat_avg_game_accuracy')); ?></p>
        <p class="text-3xl font-bold text-[#182438]"><?php echo e($summary['avg_game_pct']); ?>%</p>
        <p class="text-xs text-[#8b949d] mt-1"><?php echo e(__('analytics.stat_levels_played', ['count' => $summary['games_played']])); ?></p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#ff595e]">
        <p class="text-[#5f6873] text-sm"><?php echo e(__('analytics.stat_attendance_rate')); ?></p>
        <p class="text-3xl font-bold text-[#182438]"><?php echo e($summary['attendance_rate']); ?>%</p>
        <p class="text-xs text-[#8b949d] mt-1"><?php echo e(__('analytics.stat_late_arrivals', ['count' => $summary['late_count']])); ?></p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#ffca3a]">
        <p class="text-[#5f6873] text-sm"><?php echo e(__('analytics.stat_points_awarded')); ?></p>
        <p class="text-3xl font-bold text-[#182438]"><?php echo e(number_format($summary['points_awarded'])); ?></p>
        <p class="text-xs text-[#8b949d] mt-1"><?php echo e(__('analytics.stat_all_time')); ?></p>
    </div>
</div>


<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-1"><?php echo e(__('analytics.attendance_trend_title')); ?></h3>
        <p class="text-xs text-[#8b949d] mb-4"><?php echo e(__('analytics.attendance_trend_subtitle')); ?></p>
        <div class="chart-box"><canvas id="attendanceTrendChart"></canvas></div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6">
        <?php if (isset($component)) { $__componentOriginal3823fe2ca014a1c4759122f770fc1012 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3823fe2ca014a1c4759122f770fc1012 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.attendance-period-header','data' => ['period' => $attendancePeriod,'keep' => $attendanceKeep]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('attendance-period-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['period' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($attendancePeriod),'keep' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($attendanceKeep)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3823fe2ca014a1c4759122f770fc1012)): ?>
<?php $attributes = $__attributesOriginal3823fe2ca014a1c4759122f770fc1012; ?>
<?php unset($__attributesOriginal3823fe2ca014a1c4759122f770fc1012); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3823fe2ca014a1c4759122f770fc1012)): ?>
<?php $component = $__componentOriginal3823fe2ca014a1c4759122f770fc1012; ?>
<?php unset($__componentOriginal3823fe2ca014a1c4759122f770fc1012); ?>
<?php endif; ?>
        <div class="chart-box"><canvas id="weeklyBreakdownChart"></canvas></div>
        <div class="mt-5 pt-4 border-t border-[#f1e7cc]">
            <?php if (isset($component)) { $__componentOriginalbb43c8b0be18792a6cad213bfd24ec5c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbb43c8b0be18792a6cad213bfd24ec5c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.week-attendance','data' => ['days' => $weeklyBreakdown,'unit' => $attendancePeriod['mode'] === 'month' ? 'week' : 'day']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('week-attendance'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['days' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($weeklyBreakdown),'unit' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($attendancePeriod['mode'] === 'month' ? 'week' : 'day')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbb43c8b0be18792a6cad213bfd24ec5c)): ?>
<?php $attributes = $__attributesOriginalbb43c8b0be18792a6cad213bfd24ec5c; ?>
<?php unset($__attributesOriginalbb43c8b0be18792a6cad213bfd24ec5c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbb43c8b0be18792a6cad213bfd24ec5c)): ?>
<?php $component = $__componentOriginalbb43c8b0be18792a6cad213bfd24ec5c; ?>
<?php unset($__componentOriginalbb43c8b0be18792a6cad213bfd24ec5c); ?>
<?php endif; ?>
        </div>
    </div>
</div>


<?php if($selectedStudent): ?>
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-1"><?php echo e(__('analytics.domain_mastery_title')); ?></h3>
        <p class="text-xs text-[#8b949d] mb-4">
            <?php echo e(__('analytics.domain_mastery_subtitle_teacher', ['who' => __('analytics.domain_who_name', ['name' => $selectedStudent->first_name])])); ?>

        </p>
        <div class="chart-box"><canvas id="performanceRadarChart"></canvas></div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-1"><?php echo e(__('analytics.indicator_status_title')); ?></h3>
        <p class="text-xs text-[#8b949d] mb-4"><?php echo e(__('analytics.indicator_status_subtitle')); ?></p>
        <div class="chart-box"><canvas id="domainStatusChart"></canvas></div>
    </div>
</div>
<?php else: ?>

<div class="grid grid-cols-1 gap-8 mb-10">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-1"><?php echo e(__('analytics.age_dist_title')); ?></h3>
        <p class="text-xs text-[#8b949d] mb-4"><?php echo e(__('analytics.age_dist_subtitle_cohort')); ?></p>
        <div class="chart-box"><canvas id="ageDistChart"></canvas></div>
    </div>
</div>
<?php endif; ?>


<div class="bg-white rounded-xl shadow-sm p-6 mb-8">
    <h3 class="text-lg font-semibold text-[#182438] mb-1"><?php echo e(__('analytics.game_perf_breakdown')); ?></h3>
    <p class="text-xs text-[#8b949d] mb-4"><?php echo e(__('analytics.gp_subtitle')); ?></p>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[640px]">
            <thead class="bg-[#fffbf0]">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-[#182438] uppercase"><?php echo e(__('analytics.th_game')); ?></th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-[#182438] uppercase"><?php echo e(__('analytics.th_levels_played')); ?></th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-[#182438] uppercase"><?php echo e(__('analytics.th_avg_accuracy')); ?></th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-[#182438] uppercase"><?php echo e(__('analytics.th_highest_level')); ?></th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-[#182438] uppercase"><?php echo e(__('analytics.th_interpretation')); ?></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#f1e7cc]">
                <?php $__empty_1 = true; $__currentLoopData = $gamePerformance; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $game => $stats): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="hover:bg-[#fffbf0] transition">
                    <td class="px-4 py-3 font-medium text-[#182438] capitalize"><?php echo e($game); ?></td>
                    <?php if($stats): ?>
                        <td class="px-4 py-3 text-center text-[#5f6873]"><?php echo e($stats['attempts']); ?></td>
                        <td class="px-4 py-3 text-center">
                            <span class="font-bold text-[#182438]"><?php echo e($stats['avg_pct']); ?>%</span>
                        </td>
                        <td class="px-4 py-3 text-center text-[#5f6873]"><?php echo e($stats['best_level']); ?></td>
                        <td class="px-4 py-3 text-[#5f6873]">
                            <?php echo e($stats['avg_pct'] >= 75 ? __('analytics.interp_confident') : ($stats['avg_pct'] >= 50 ? __('analytics.interp_progress') : __('analytics.interp_reteach'))); ?>

                        </td>
                    <?php else: ?>
                        <td class="px-4 py-3 text-center text-[#8b949d]">0</td>
                        <td class="px-4 py-3 text-center text-[#8b949d]">—</td>
                        <td class="px-4 py-3 text-center text-[#8b949d]">—</td>
                        <td class="px-4 py-3 text-[#8b949d]"><?php echo e(__('analytics.not_started')); ?></td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="5" class="px-4 py-8 text-center text-[#8b949d]"><?php echo e(__('analytics.no_game_data')); ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
    <div class="bg-white rounded-xl shadow-sm p-6 <?php echo e($selectedStudent ? 'lg:col-span-3' : 'lg:col-span-2'); ?>">
        <h3 class="text-lg font-semibold text-[#182438] mb-1"><?php echo e(__('analytics.weekly_trend_title')); ?></h3>
        <p class="text-xs text-[#8b949d] mb-4"><?php echo e(__('analytics.weekly_subtitle_long')); ?></p>
        <div class="chart-box"><canvas id="gamePlayTrendChart"></canvas></div>
    </div>
    <?php if (! ($selectedStudent)): ?>
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-1"><?php echo e(__('analytics.enrolment_trend_title')); ?></h3>
        <p class="text-xs text-[#8b949d] mb-4"><?php echo e(__('analytics.enrolment_trend_subtitle_teacher')); ?></p>
        <div class="chart-box"><canvas id="enrollTrendChart"></canvas></div>
    </div>
    <?php endif; ?>
</div>


<div class="grid grid-cols-1 gap-8 mb-10">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-1"><?php echo e(__('analytics.recommended_interventions')); ?></h3>
        <p class="text-xs text-[#8b949d] mb-4"><?php echo e(__('analytics.ri_subtitle')); ?></p>
        <div class="space-y-3 max-h-[320px] overflow-y-auto pr-1">
            <?php $__empty_1 = true; $__currentLoopData = $recommendations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="p-4 rounded-lg border-l-4
                    <?php echo e($rec['severity'] === 'high' ? 'bg-[#fde8ef] border-[#ff595e]' : ($rec['severity'] === 'medium' ? 'bg-[#fff6e5] border-[#ffca3a]' : 'bg-[#ecf7d6] border-[#8ac926]')); ?>">
                    <div class="flex items-center justify-between mb-1">
                        <h4 class="font-semibold text-[#182438]"><?php echo e($rec['name']); ?></h4>
                        <span class="text-[10px] font-bold uppercase tracking-wide px-2 py-0.5 rounded-full
                            <?php echo e($rec['severity'] === 'high' ? 'bg-[#ff595e] text-white' : ($rec['severity'] === 'medium' ? 'bg-[#ffca3a] text-[#182438]' : 'bg-[#8ac926] text-[#182438]')); ?>">
                            <?php echo e(__("analytics.severity_{$rec['severity']}")); ?>

                        </span>
                    </div>
                    <span class="inline-flex items-center gap-1.5 text-xs text-[#5f6873] mb-1">
                        <span class="w-2 h-2 rounded-full"
                              style="background-color: <?php echo e(\App\Models\Student::colorForSection($sectionByStudent[$rec['student_id']] ?? null)); ?>"></span>
                        <?php echo e(\App\Models\Student::sectionLabel($sectionByStudent[$rec['student_id']] ?? null)); ?>

                    </span>
                    <p class="text-sm text-[#5f6873] mt-1"><span class="font-medium"><?php echo e(__('analytics.needs_label')); ?></span> <?php echo e($rec['needs']); ?></p>
                    <p class="text-sm text-[#5f6873]"><span class="font-medium"><?php echo e(__('analytics.plan_label')); ?></span> <?php echo e($rec['plan']); ?></p>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="p-6 rounded-lg bg-[#ecf7d6] border border-[#b8e0c2] text-center">
                    <p class="text-[#182438] font-semibold"><?php echo e(__('analytics.no_interventions_needed')); ?></p>
                    <p class="text-sm text-[#5f6873] mt-1"><?php echo e(__('analytics.no_interventions_desc_teacher')); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>


<?php if(count($categoryScores)): ?>
<div class="bg-white rounded-xl shadow-sm p-6 mb-8">
    <h3 class="text-lg font-semibold text-[#182438] mb-1">
        <?php echo e($selectedStudent ? __('analytics.score_by_category') : __('analytics.score_by_category_per_student')); ?>

    </h3>
    <p class="text-xs text-[#8b949d] mb-4">
        <?php if($selectedStudent): ?>
            <?php echo e(__('analytics.category_blurb_selected')); ?>

        <?php else: ?>
            <?php echo e(__('analytics.category_blurb_cohort')); ?>

        <?php endif; ?>
    </p>

    <div class="overflow-x-auto">
        <table class="w-full min-w-[980px]">
            <thead class="bg-[#fffbf0]">
                <tr>
                    <th rowspan="2" class="px-3 py-2 text-left text-xs font-semibold text-[#182438] uppercase"><?php echo e(__('analytics.th_student')); ?></th>
                    <th rowspan="2" class="px-3 py-2 text-left text-xs font-semibold text-[#182438] uppercase"><?php echo e(__('analytics.th_section')); ?></th>
                    <th colspan="<?php echo e(count(\App\Services\AnalyticsService::GAME_TYPES)); ?>"
                        class="px-3 py-2 text-center text-xs font-semibold text-[#1982c4] uppercase">
                        <?php echo e(__('analytics.th_game_accuracy')); ?>

                    </th>
                    <th colspan="<?php echo e(count(\App\Services\AnalyticsService::ECCD_DOMAINS)); ?>"
                        class="px-3 py-2 text-center text-xs font-semibold text-[#7c5cbf] uppercase">
                        <?php echo e(__('analytics.th_eccd_mastery')); ?>

                    </th>
                    <th rowspan="2" class="px-3 py-2 text-center text-xs font-semibold text-[#182438] uppercase"><?php echo e(__('analytics.th_game_avg')); ?></th>
                    <th rowspan="2" class="px-3 py-2 text-center text-xs font-semibold text-[#182438] uppercase"><?php echo e(__('analytics.th_eccd_avg')); ?></th>
                    <th colspan="<?php echo e(count(\App\Services\AnalyticsService::GAME_TYPES)); ?>"
                        class="px-3 py-2 text-center text-xs font-semibold text-[#b8860b] uppercase">
                        <?php echo e(__('analytics.th_points_earned')); ?>

                    </th>
                    <th rowspan="2" class="px-3 py-2 text-center text-xs font-semibold text-[#b8860b] uppercase"><?php echo e(__('analytics.th_total_pts')); ?></th>
                </tr>
                <tr>
                    <?php $__currentLoopData = \App\Services\AnalyticsService::GAME_TYPES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $game): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <th class="px-2 py-2 text-center text-[11px] font-semibold text-[#5f6873] uppercase"><?php echo e(ucfirst($game)); ?></th>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php $__currentLoopData = \App\Services\AnalyticsService::ECCD_DOMAINS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $domain): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <th class="px-2 py-2 text-center text-[11px] font-semibold text-[#5f6873] uppercase"
                            title="<?php echo e($domain); ?>"><?php echo e(\App\Services\AnalyticsService::ECCD_DOMAIN_SHORT[$domain] ?? $domain); ?></th>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php $__currentLoopData = \App\Services\AnalyticsService::GAME_TYPES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $game): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <th class="px-2 py-2 text-center text-[11px] font-semibold text-[#5f6873] uppercase"><?php echo e(ucfirst($game)); ?></th>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#f1e7cc]">
                <?php $__currentLoopData = $categoryScores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="hover:bg-[#fffbf0] transition">
                        <td class="px-3 py-2.5 whitespace-nowrap font-medium text-[#182438]"><?php echo e($row['name']); ?></td>
                        <td class="px-3 py-2.5">
                            <span class="inline-flex items-center gap-1.5 text-xs text-[#5f6873] whitespace-nowrap">
                                <span class="w-2 h-2 rounded-full" style="background-color: <?php echo e(\App\Models\Student::colorForSection($row['class_name'])); ?>"></span>
                                <?php echo e(\App\Models\Student::sectionLabel($row['class_name'])); ?>

                            </span>
                        </td>
                        <?php $__currentLoopData = \App\Services\AnalyticsService::GAME_TYPES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $game): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <td class="px-2 py-2.5 text-center text-sm"><?php if (isset($component)) { $__componentOriginal283f4cd961d26d85868358213e66e135 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal283f4cd961d26d85868358213e66e135 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.score-cell','data' => ['value' => $row['games'][$game] ?? null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('score-cell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($row['games'][$game] ?? null)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal283f4cd961d26d85868358213e66e135)): ?>
<?php $attributes = $__attributesOriginal283f4cd961d26d85868358213e66e135; ?>
<?php unset($__attributesOriginal283f4cd961d26d85868358213e66e135); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal283f4cd961d26d85868358213e66e135)): ?>
<?php $component = $__componentOriginal283f4cd961d26d85868358213e66e135; ?>
<?php unset($__componentOriginal283f4cd961d26d85868358213e66e135); ?>
<?php endif; ?></td>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php $__currentLoopData = \App\Services\AnalyticsService::ECCD_DOMAINS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $domain): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <td class="px-2 py-2.5 text-center text-sm"><?php if (isset($component)) { $__componentOriginal283f4cd961d26d85868358213e66e135 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal283f4cd961d26d85868358213e66e135 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.score-cell','data' => ['value' => $row['domains'][$domain] ?? null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('score-cell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($row['domains'][$domain] ?? null)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal283f4cd961d26d85868358213e66e135)): ?>
<?php $attributes = $__attributesOriginal283f4cd961d26d85868358213e66e135; ?>
<?php unset($__attributesOriginal283f4cd961d26d85868358213e66e135); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal283f4cd961d26d85868358213e66e135)): ?>
<?php $component = $__componentOriginal283f4cd961d26d85868358213e66e135; ?>
<?php unset($__componentOriginal283f4cd961d26d85868358213e66e135); ?>
<?php endif; ?></td>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <td class="px-3 py-2.5 text-center text-sm"><?php if (isset($component)) { $__componentOriginal283f4cd961d26d85868358213e66e135 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal283f4cd961d26d85868358213e66e135 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.score-cell','data' => ['value' => $row['game_overall']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('score-cell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($row['game_overall'])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal283f4cd961d26d85868358213e66e135)): ?>
<?php $attributes = $__attributesOriginal283f4cd961d26d85868358213e66e135; ?>
<?php unset($__attributesOriginal283f4cd961d26d85868358213e66e135); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal283f4cd961d26d85868358213e66e135)): ?>
<?php $component = $__componentOriginal283f4cd961d26d85868358213e66e135; ?>
<?php unset($__componentOriginal283f4cd961d26d85868358213e66e135); ?>
<?php endif; ?></td>
                        <td class="px-3 py-2.5 text-center text-sm"><?php if (isset($component)) { $__componentOriginal283f4cd961d26d85868358213e66e135 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal283f4cd961d26d85868358213e66e135 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.score-cell','data' => ['value' => $row['domain_overall']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('score-cell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($row['domain_overall'])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal283f4cd961d26d85868358213e66e135)): ?>
<?php $attributes = $__attributesOriginal283f4cd961d26d85868358213e66e135; ?>
<?php unset($__attributesOriginal283f4cd961d26d85868358213e66e135); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal283f4cd961d26d85868358213e66e135)): ?>
<?php $component = $__componentOriginal283f4cd961d26d85868358213e66e135; ?>
<?php unset($__componentOriginal283f4cd961d26d85868358213e66e135); ?>
<?php endif; ?></td>
                        <?php $__currentLoopData = \App\Services\AnalyticsService::GAME_TYPES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $game): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <td class="px-2 py-2.5 text-center text-sm text-[#5f6873]">
                                <?php if(isset($row['points'][$game])): ?>
                                    <span><?php echo e(number_format($row['points'][$game])); ?></span>
                                <?php else: ?>
                                    <span class="text-[#8b949d]" title="<?php echo e(__('analytics.no_points_yet')); ?>">&mdash;</span>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <td class="px-3 py-2.5 text-center">
                            <span class="font-bold text-[#b8860b]"><?php echo e(number_format($row['total_points'])); ?></span>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>

    <p class="text-xs text-[#8b949d] mt-3">
        <span class="text-[#8ac926] font-bold">&#8805;75%</span> <?php echo e(__('analytics.legend_on_track')); ?> &middot;
        <span class="text-[#ffca3a] font-bold">50&ndash;74%</span> <?php echo e(__('analytics.legend_needs_practice')); ?> &middot;
        <span class="text-[#ff595e] font-bold">&lt;50%</span> <?php echo e(__('analytics.legend_priority')); ?>

    </p>
</div>
<?php endif; ?>


<?php if(!$selectedStudent && count($sectionScores)): ?>
<div class="bg-white rounded-xl shadow-sm p-6 mb-8">
    <h3 class="text-lg font-semibold text-[#182438] mb-1"><?php echo e(__('analytics.avg_score_by_section')); ?></h3>
    <p class="text-xs text-[#8b949d] mb-4"><?php echo e(__('analytics.asbs_subtitle')); ?></p>

    <div class="overflow-x-auto">
        <table class="w-full min-w-[520px]">
            <thead class="bg-[#fffbf0]">
                <tr>
                    <th class="px-3 py-2 text-left text-xs font-semibold text-[#182438] uppercase"><?php echo e(__('analytics.th_section')); ?></th>
                    <th class="px-3 py-2 text-center text-xs font-semibold text-[#182438] uppercase"><?php echo e(__('analytics.th_students')); ?></th>
                    <th class="px-3 py-2 text-center text-xs font-semibold text-[#1982c4] uppercase"><?php echo e(__('analytics.th_avg_game_accuracy')); ?></th>
                    <th class="px-3 py-2 text-center text-xs font-semibold text-[#7c5cbf] uppercase"><?php echo e(__('analytics.th_avg_eccd_mastery')); ?></th>
                    <th class="px-3 py-2 text-center text-xs font-semibold text-[#b8860b] uppercase"><?php echo e(__('analytics.th_avg_points')); ?></th>
                    <th class="px-3 py-2 text-center text-xs font-semibold text-[#b8860b] uppercase"><?php echo e(__('analytics.th_total_points')); ?></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#f1e7cc]">
                <?php $__currentLoopData = $sectionScores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="hover:bg-[#fffbf0] transition">
                        <td class="px-3 py-2.5">
                            <span class="inline-flex items-center gap-2 text-sm font-medium text-[#182438]">
                                <span class="w-2.5 h-2.5 rounded-full" style="background-color: <?php echo e($row['color']); ?>"></span>
                                <?php echo e($row['label']); ?>

                            </span>
                        </td>
                        <td class="px-3 py-2.5 text-center text-sm text-[#5f6873]"><?php echo e($row['students']); ?></td>
                        <td class="px-3 py-2.5 text-center text-sm"><?php if (isset($component)) { $__componentOriginal283f4cd961d26d85868358213e66e135 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal283f4cd961d26d85868358213e66e135 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.score-cell','data' => ['value' => $row['game_pct']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('score-cell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($row['game_pct'])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal283f4cd961d26d85868358213e66e135)): ?>
<?php $attributes = $__attributesOriginal283f4cd961d26d85868358213e66e135; ?>
<?php unset($__attributesOriginal283f4cd961d26d85868358213e66e135); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal283f4cd961d26d85868358213e66e135)): ?>
<?php $component = $__componentOriginal283f4cd961d26d85868358213e66e135; ?>
<?php unset($__componentOriginal283f4cd961d26d85868358213e66e135); ?>
<?php endif; ?></td>
                        <td class="px-3 py-2.5 text-center text-sm"><?php if (isset($component)) { $__componentOriginal283f4cd961d26d85868358213e66e135 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal283f4cd961d26d85868358213e66e135 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.score-cell','data' => ['value' => $row['domain_pct']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('score-cell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($row['domain_pct'])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal283f4cd961d26d85868358213e66e135)): ?>
<?php $attributes = $__attributesOriginal283f4cd961d26d85868358213e66e135; ?>
<?php unset($__attributesOriginal283f4cd961d26d85868358213e66e135); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal283f4cd961d26d85868358213e66e135)): ?>
<?php $component = $__componentOriginal283f4cd961d26d85868358213e66e135; ?>
<?php unset($__componentOriginal283f4cd961d26d85868358213e66e135); ?>
<?php endif; ?></td>
                        <td class="px-3 py-2.5 text-center text-sm text-[#5f6873]">
                            <?php echo e($row['avg_points'] === null ? '—' : number_format($row['avg_points'])); ?>

                        </td>
                        <td class="px-3 py-2.5 text-center font-bold text-[#b8860b]"><?php echo e(number_format($row['total_points'])); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php if (! ($selectedStudent)): ?>

<div class="bg-white rounded-xl shadow-sm p-6 mb-8">
    <h3 class="text-lg font-semibold text-[#182438] mb-1"><?php echo e(__('analytics.exceeding_struggling')); ?></h3>
    <p class="text-xs text-[#8b949d] mb-4">
        <?php echo e(__('analytics.es_subtitle', [
            'exceeding' => \App\Services\AnalyticsService::EXCEEDING_PCT,
            'struggling' => \App\Services\AnalyticsService::STRUGGLING_PCT,
        ])); ?>

    </p>

    <?php if(count($topicStandings)): ?>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[780px]">
            <thead class="bg-[#fffbf0]">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-[#182438] uppercase"><?php echo e(__('analytics.th_topic')); ?></th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-[#182438] uppercase"><?php echo e(__('analytics.th_type')); ?></th>
                    <th class="px-3 py-3 text-center text-xs font-semibold text-[#182438] uppercase"><?php echo e(__('analytics.th_learners')); ?></th>
                    <th class="px-3 py-3 text-center text-xs font-semibold text-[#182438] uppercase"><?php echo e(__('analytics.th_cohort_avg')); ?></th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-[#182438] uppercase"><?php echo e(__('analytics.th_exceeding')); ?></th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-[#182438] uppercase"><?php echo e(__('analytics.th_struggling')); ?></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#f1e7cc]">
                <?php $__currentLoopData = $topicStandings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr class="hover:bg-[#fffbf0] transition align-top">
                    <td class="px-4 py-3 font-medium text-[#182438] capitalize"><?php echo e($row['topic']); ?></td>
                    <td class="px-3 py-3 text-sm text-[#5f6873]"><?php echo e($row['basis']); ?></td>
                    <td class="px-3 py-3 text-sm text-center text-[#5f6873]"><?php echo e($row['participants']); ?></td>
                    <td class="px-3 py-3 text-center">
                        <span class="text-sm font-semibold <?php echo e($row['avg'] >= \App\Services\AnalyticsService::EXCEEDING_PCT ? 'text-[#8ac926]' : ($row['avg'] < \App\Services\AnalyticsService::STRUGGLING_PCT ? 'text-[#ff595e]' : 'text-[#ffca3a]')); ?>">
                            <?php echo e($row['avg']); ?>%
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <?php if(count($row['exceeding'])): ?>
                            <div class="flex flex-wrap gap-1.5">
                                <?php $__currentLoopData = $row['exceeding']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold bg-[#ecf7d6] text-[#3f7d1a] px-2 py-0.5 rounded-full whitespace-nowrap">
                                    <span class="w-1.5 h-1.5 rounded-full" style="background-color: <?php echo e(\App\Models\Student::colorForSection($sectionByStudent[$p['id']] ?? null)); ?>"></span>
                                    <?php echo e($p['name']); ?> · <?php echo e($p['pct']); ?>%
                                </span>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php else: ?>
                            <span class="text-xs text-[#8b949d]">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3">
                        <?php if(count($row['struggling'])): ?>
                            <div class="flex flex-wrap gap-1.5">
                                <?php $__currentLoopData = $row['struggling']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold bg-[#fde8ef] text-[#ff595e] px-2 py-0.5 rounded-full whitespace-nowrap">
                                    <span class="w-1.5 h-1.5 rounded-full" style="background-color: <?php echo e(\App\Models\Student::colorForSection($sectionByStudent[$p['id']] ?? null)); ?>"></span>
                                    <?php echo e($p['name']); ?> · <?php echo e($p['pct']); ?>%
                                </span>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php else: ?>
                            <span class="text-xs text-[#8b949d]">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <p class="text-sm text-[#8b949d] py-8 text-center"><?php echo e(__('analytics.no_scores_recorded')); ?></p>
    <?php endif; ?>
</div>
<?php endif; ?>


<?php if (! ($selectedStudent)): ?>
    <?php echo $__env->make('teacher.analytics.partials.leaderboards', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php endif; ?>


<?php if(count($activityPerformance)): ?>
<div class="bg-white rounded-xl shadow-sm p-6 mb-8">
    <h3 class="text-lg font-semibold text-[#182438] mb-1"><?php echo e(__('analytics.activity_performance')); ?></h3>
    <p class="text-xs text-[#8b949d] mb-4"><?php echo e(__('analytics.ap_subtitle')); ?></p>
    <div class="chart-box"><canvas id="activityChart"></canvas></div>
</div>
<?php endif; ?>


<div class="bg-white rounded-xl shadow-sm p-6">
    <h3 class="text-lg font-semibold text-[#182438] mb-1"><?php echo e(__('analytics.level_progress_map')); ?></h3>
    <p class="text-xs text-[#8b949d] mb-4">
        <?php if($selectedStudent): ?>
            <?php echo e(__('analytics.lpm_subtitle_selected', ['name' => $selectedStudent->first_name])); ?>

        <?php else: ?>
            <?php echo e(__('analytics.lpm_subtitle_cohort')); ?>

        <?php endif; ?>
    </p>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[720px]">
            <thead class="bg-[#fffbf0]">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-[#182438] uppercase"><?php echo e(__('analytics.th_game')); ?></th>
                    <?php for($l = 1; $l <= 8; $l++): ?>
                        <th class="px-3 py-3 text-center text-xs font-semibold text-[#182438] uppercase">L<?php echo e($l); ?></th>
                    <?php endfor; ?>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#f1e7cc]">
                <?php $__currentLoopData = $levelProgress; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $game => $levels): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr class="hover:bg-[#fffbf0] transition">
                    <td class="px-4 py-3 font-medium text-[#182438] capitalize"><?php echo e($game); ?></td>
                    <?php for($l = 1; $l <= 8; $l++): ?>
                        <td class="px-3 py-3 text-center">
                            <?php $count = $levels[$l] ?? 0; ?>
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-xs font-bold
                                <?php echo e($count === 0 ? 'bg-[#f4f2ec] text-[#c9c4b8]' : ($count >= 5 ? 'bg-[#8ac926] text-[#182438]' : 'bg-[#ecf7d6] text-[#3d7a1c]')); ?>">
                                <?php echo e($count); ?>

                            </span>
                        </td>
                    <?php endfor; ?>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script data-page-script>
Chart.defaults.maintainAspectRatio = false;
const chartIfPresent = (id, config) => { const el = document.getElementById(id); if (el) new Chart(el, config); };
const attendanceRate = <?php echo json_encode($attendanceRate, 15, 512) ?>;
const weeklyBreakdown = <?php echo json_encode($weeklyBreakdown, 15, 512) ?>;
const performanceRadar = <?php echo json_encode($performanceRadar, 15, 512) ?>;
const domainStatus = <?php echo json_encode($domainStatus, 15, 512) ?>;
const ageDistribution = <?php echo json_encode($ageDistribution, 15, 512) ?>;
const enrollmentTrend = <?php echo json_encode($enrollmentTrend, 15, 512) ?>;
const gamePlayTrend = <?php echo json_encode($gamePlayTrend, 15, 512) ?>;
const gamePerformance = <?php echo json_encode($gamePerformance, 15, 512) ?>;
const activityPerformance = <?php echo json_encode($activityPerformance, 15, 512) ?>;

const PALETTE = ['#ff595e', '#1982c4', '#8ac926', '#ff595e', '#ffca3a', '#e78cae', '#7c5cbf', '#16a085'];
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
    'present_rate': <?php echo json_encode(__('analytics.chart_present_rate'), 15, 512) ?>,
    'present': <?php echo json_encode(__('analytics.chart_present'), 15, 512) ?>,
    'absent': <?php echo json_encode(__('analytics.chart_absent'), 15, 512) ?>,
    'late': <?php echo json_encode(__('analytics.chart_late'), 15, 512) ?>,
    'achieved': <?php echo json_encode(__('analytics.chart_achieved'), 15, 512) ?>,
    'students': <?php echo json_encode(__('analytics.chart_students'), 15, 512) ?>,
    'levels_played': <?php echo json_encode(__('analytics.chart_levels_played'), 15, 512) ?>,
    'avg_accuracy': <?php echo json_encode(__('analytics.chart_avg_accuracy'), 15, 512) ?>,
    'levels': <?php echo json_encode(__('analytics.chart_levels'), 15, 512) ?>,
    'new_enrolments': <?php echo json_encode(__('analytics.chart_new_enrolments'), 15, 512) ?>,
    'avg_score': <?php echo json_encode(__('analytics.chart_avg_score'), 15, 512) ?>,
    'attempts': <?php echo json_encode(__('analytics.chart_attempts'), 15, 512) ?>,
};

// Attendance rate trend
new Chart(document.getElementById('attendanceTrendChart'), {
    type: 'line',
    data: {
        labels: attendanceRate.map(r => r.month),
        datasets: [{
            label: L.present_rate,
            data: attendanceRate.map(r => r.rate),
            borderColor: '#ff595e',
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
        plugins: {
            legend: { position: 'bottom' },
            tooltip: withCounts([weeklyBreakdown.map(r => r.present), weeklyBreakdown.map(r => r.absent), weeklyBreakdown.map(r => r.late)])
        },
        scales: { y: { min: 0, max: 100, ticks: pctTicks, grid: { color: gridColor } }, x: { grid: { display: false } } }
    }
});

<?php if($selectedStudent): ?>
// ECCD radar
new Chart(document.getElementById('performanceRadarChart'), {
    type: 'radar',
    data: {
        labels: Object.keys(performanceRadar),
        datasets: [{
            label: L.achieved,
            data: Object.values(performanceRadar),
            backgroundColor: 'rgba(255, 133, 85, 0.2)',
            borderColor: '#ff595e',
            pointBackgroundColor: '#ff595e',
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
<?php endif; ?>

// Age distribution: share of the students in each age group
chartIfPresent('ageDistChart', {
    type: 'bar',
    data: {
        labels: Object.keys(ageDistribution),
        datasets: [{
            label: L.students,
            data: shareOf(Object.values(ageDistribution)),
            backgroundColor: ['#1982c4', '#8ac926', '#ff595e', '#ffca3a', '#e78cae'],
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

// Activity performance
if (activityPerformance.length) {
    new Chart(document.getElementById('activityChart'), {
        type: 'bar',
        data: {
            labels: activityPerformance.map(a => a.type),
            datasets: [
                { label: L.avg_score, data: activityPerformance.map(a => Math.min(100, +a.avg_score || 0)), backgroundColor: '#1982c4', borderRadius: 4, maxBarThickness: 36 },
                { label: L.attempts, data: shareOf(activityPerformance.map(a => a.attempts)), backgroundColor: '#ff595e', borderRadius: 4, maxBarThickness: 36, yAxisID: 'y1' }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' },
                tooltip: { callbacks: { label: ctx => ctx.datasetIndex === 1
                    ? ctx.dataset.label + ': ' + ctx.raw + '% (' + activityPerformance[ctx.dataIndex].attempts + ')'
                    : ctx.dataset.label + ': ' + ctx.raw + '%' } }
            },
            scales: {
                y: { min: 0, max: 100, ticks: pctTicks, grid: { color: gridColor } },
                y1: { min: 0, max: 100, position: 'right', grid: { display: false }, ticks: pctTicks },
                x: { grid: { display: false } }
            }
        }
    });
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.teacher', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kidlink-laravel\resources\views/teacher/dashboard.blade.php ENDPATH**/ ?>