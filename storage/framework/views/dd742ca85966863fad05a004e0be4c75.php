<?php $__env->startSection('title', __('analytics.title_admin')); ?>
<?php $__env->startSection('page-title', __('analytics.page_title_admin')); ?>

<?php $__env->startSection('content'); ?>

<?php if (isset($component)) { $__componentOriginalf8e0d1960dd92790506a658880aba4ae = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8e0d1960dd92790506a658880aba4ae = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics-scope-bar','data' => ['route' => 'admin.analytics.index','scope' => $scope,'section' => $section,'student' => $selectedStudent,'sort' => $sort,'shown' => count($students),'total' => $totalStudents,'legend' => $sectionLegend]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics-scope-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['route' => 'admin.analytics.index','scope' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($scope),'section' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($section),'student' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($selectedStudent),'sort' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($sort),'shown' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(count($students)),'total' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($totalStudents),'legend' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($sectionLegend)]); ?>
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


<div class="bg-gradient-to-r from-[#fff0e6] to-[#fde8ef] border border-[#ffd9c2] rounded-xl p-5 mb-6">
    <h3 class="text-sm font-bold text-[#ff8555] uppercase tracking-wide mb-2">
        <?php echo e($selectedStudent ? __('analytics.heading_student_overview') : __('analytics.heading_system_overview')); ?>

    </h3>
    <p class="text-[#5f6873] leading-relaxed">
        <?php if($selectedStudent): ?>
            <?php
                $adminAgeWord = trans_choice('analytics.year_word', (int) $averageAge);
                $adminGameWord = trans_choice('analytics.game_level_word', $summary['games_played']);
                $adminPointWord = trans_choice('analytics.point_word', $summary['points_awarded']);
            ?>
            <?php echo e(__('analytics.admin_student_narr', [
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
            ])); ?>

            <?php if($recommendations): ?>
                <?php echo e(__('analytics.intervention_recommended', ['needs' => $recommendations[0]['needs'], 'plan' => $recommendations[0]['plan']])); ?>

            <?php else: ?>
                <?php echo e(__('analytics.no_interventions_now')); ?>

            <?php endif; ?>
        <?php else: ?>
            <?php
                $systemActWord = trans_choice('analytics.enrolment_word', $system['active_enrollments']);
                $systemTeacherWord = trans_choice('analytics.teacher_word', $system['teachers']);
                $systemAdminWord = trans_choice('analytics.admin_word', $system['admins']);
                $systemPointWord = trans_choice('analytics.point_word', $system['points_awarded']);
            ?>
            <?php echo e(__('analytics.system_head', [
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
            ])); ?>

            <?php if($needsSupport): ?>
                <?php echo e(__('analytics.system_lowest', ['pct' => $needsSupport[0]['avg_pct']])); ?>

            <?php endif; ?>
            <?php if($interventionCount > 0): ?>
                <?php echo e(trans_choice('analytics.system_interventions', $interventionCount, ['count' => $interventionCount])); ?>

            <?php endif; ?>
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


<?php if($selectedStudent): ?>
<div class="grid grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#258cc5]">
        <p class="text-[#5f6873] text-sm"><?php echo e(__('analytics.stat_game_levels_played')); ?></p>
        <p class="text-3xl font-bold text-[#182438]"><?php echo e(number_format($summary['games_played'])); ?></p>
        <p class="text-xs text-[#8b949d] mt-1"><?php echo e(__('analytics.stat_avg_accuracy_suffix', ['pct' => $summary['avg_game_pct']])); ?></p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#68b82f]">
        <p class="text-[#5f6873] text-sm"><?php echo e(__('analytics.stat_attendance_rate')); ?></p>
        <p class="text-3xl font-bold text-[#182438]"><?php echo e($summary['attendance_rate']); ?>%</p>
        <p class="text-xs text-[#8b949d] mt-1"><?php echo e(__('analytics.line_absences_late', ['abs' => $summary['absences'], 'late' => $summary['late_count']])); ?></p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#ff8555]">
        <p class="text-[#5f6873] text-sm"><?php echo e(__('analytics.stat_points_awarded')); ?></p>
        <p class="text-3xl font-bold text-[#182438]"><?php echo e(number_format($summary['points_awarded'])); ?></p>
        <p class="text-xs text-[#8b949d] mt-1"><?php echo e(__('analytics.stat_all_time')); ?></p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#d9b348]">
        <p class="text-[#5f6873] text-sm"><?php echo e(__('analytics.stat_age')); ?></p>
        <p class="text-3xl font-bold text-[#182438]"><?php echo e($averageAge); ?></p>
        <p class="text-xs text-[#8b949d] mt-1"><?php echo e(\App\Models\Student::sectionLabel($selectedStudent->class_name)); ?></p>
    </div>
</div>
<?php else: ?>
<div class="grid grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#258cc5]">
        <p class="text-[#5f6873] text-sm"><?php echo e(__('analytics.stat_total_accounts')); ?></p>
        <p class="text-3xl font-bold text-[#182438]"><?php echo e($system['total_users']); ?></p>
        <p class="text-xs text-[#8b949d] mt-1"><?php echo e(__('analytics.stat_inactive', ['count' => $system['inactive_users']])); ?></p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#68b82f]">
        <p class="text-[#5f6873] text-sm"><?php echo e(__('analytics.stat_active_enrolments')); ?></p>
        <p class="text-3xl font-bold text-[#182438]"><?php echo e($system['active_enrollments']); ?></p>
        <p class="text-xs text-[#8b949d] mt-1"><?php echo e(__('analytics.stat_of_students', ['count' => $system['students_enrolled']])); ?></p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#ff8555]">
        <p class="text-[#5f6873] text-sm"><?php echo e(__('analytics.stat_game_levels_played')); ?></p>
        <p class="text-3xl font-bold text-[#182438]"><?php echo e(number_format($system['game_levels_played'])); ?></p>
        <p class="text-xs text-[#8b949d] mt-1"><?php echo e(__('analytics.stat_avg_accuracy_suffix', ['pct' => $summary['avg_game_pct']])); ?></p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#d9b348]">
        <p class="text-[#5f6873] text-sm"><?php echo e(__('analytics.stat_points_awarded')); ?></p>
        <p class="text-3xl font-bold text-[#182438]"><?php echo e(number_format($system['points_awarded'])); ?></p>
        <p class="text-xs text-[#8b949d] mt-1"><?php echo e(__('analytics.stat_all_time')); ?></p>
    </div>
</div>
<?php endif; ?>

<?php if (! ($selectedStudent)): ?>

<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <h3 class="text-lg font-semibold text-[#182438] mb-1"><?php echo e(__('analytics.dq_title')); ?></h3>
    <p class="text-xs text-[#8b949d] mb-4"><?php echo e(__('analytics.dq_subtitle')); ?></p>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <?php $__currentLoopData = $dataQuality; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $check): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="p-4 rounded-lg border-l-4
                <?php echo e($check['severity'] === 'high' ? 'bg-[#fde8ef] border-[#f05d56]' : ($check['severity'] === 'medium' ? 'bg-[#fff6e5] border-[#d9b348]' : ($check['severity'] === 'low' ? 'bg-[#e1f0f8] border-[#258cc5]' : 'bg-[#e2f4e8] border-[#68b82f]'))); ?>">
                <p class="text-2xl font-bold text-[#182438]"><?php echo e($check['count']); ?></p>
                <p class="text-xs text-[#5f6873] mt-1"><?php echo e($check['label']); ?></p>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>


<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-1"><?php echo e(__('analytics.growth_title')); ?></h3>
        <p class="text-xs text-[#8b949d] mb-4"><?php echo e(__('analytics.growth_subtitle')); ?></p>
        <canvas id="roleGrowthChart" height="240"></canvas>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-1"><?php echo e(__('analytics.enrolment_trend_title')); ?></h3>
        <p class="text-xs text-[#8b949d] mb-4"><?php echo e(__('analytics.enrolment_trend_subtitle_admin')); ?></p>
        <canvas id="enrollmentChart" height="240"></canvas>
    </div>
</div>
<?php endif; ?>


<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-1"><?php echo e(__('analytics.centre_attendance_title')); ?></h3>
        <p class="text-xs text-[#8b949d] mb-4"><?php echo e(__('analytics.centre_attendance_subtitle')); ?></p>
        <canvas id="attendanceChart" height="240"></canvas>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-1"><?php echo e(__('analytics.weekly_trend_title')); ?></h3>
        <p class="text-xs text-[#8b949d] mb-4"><?php echo e(__('analytics.weekly_subtitle_short')); ?></p>
        <canvas id="gamePlayTrendChart" height="240"></canvas>
    </div>
</div>


<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
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
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
        <canvas id="weeklyBreakdownChart" height="220"></canvas>
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


<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <?php if($selectedStudent): ?>
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-1"><?php echo e(__('analytics.domain_mastery_title')); ?></h3>
        <p class="text-xs text-[#8b949d] mb-4"><?php echo e(__('analytics.domain_mastery_subtitle_admin')); ?></p>
        <canvas id="domainMasteryChart" height="280"></canvas>
    </div>
    <?php else: ?>
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center justify-center">
        <div class="text-center max-w-md">
            <p class="font-semibold text-[#182438]"><?php echo e(__('analytics.domain_selection_title')); ?></p>
            <p class="text-sm text-[#5f6873] mt-1"><?php echo e(__('analytics.domain_selection_prompt')); ?></p>
            <p class="text-xs text-[#8b949d] mt-1"><?php echo e(__('analytics.domain_selection_hint')); ?></p>
            <button type="button" @click="$dispatch('open-student-chooser')" class="inline-block mt-3 px-4 py-2 rounded-lg bg-[#ff8555] text-white text-sm font-semibold hover:bg-[#f47548]"><?php echo e(__('analytics.domain_choose_student')); ?></button>
            <?php if (isset($component)) { $__componentOriginal8224c6c2d1ef7589c452d8e68a79975d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8224c6c2d1ef7589c452d8e68a79975d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.student-chooser','data' => ['variant' => 'none','students' => $studentOptions,'action' => route('admin.analytics.index'),'param' => 'student']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('student-chooser'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'none','students' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($studentOptions),'action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.analytics.index')),'param' => 'student']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8224c6c2d1ef7589c452d8e68a79975d)): ?>
<?php $attributes = $__attributesOriginal8224c6c2d1ef7589c452d8e68a79975d; ?>
<?php unset($__attributesOriginal8224c6c2d1ef7589c452d8e68a79975d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8224c6c2d1ef7589c452d8e68a79975d)): ?>
<?php $component = $__componentOriginal8224c6c2d1ef7589c452d8e68a79975d; ?>
<?php unset($__componentOriginal8224c6c2d1ef7589c452d8e68a79975d); ?>
<?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-1"><?php echo e(__('analytics.game_popularity_title')); ?></h3>
        <p class="text-xs text-[#8b949d] mb-4"><?php echo e(__('analytics.game_popularity_subtitle')); ?></p>
        <canvas id="gamePopularityChart" height="280"></canvas>
    </div>
</div>

<?php if (! ($selectedStudent)): ?>

<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <h3 class="text-lg font-semibold text-[#182438] mb-1"><?php echo e(__('analytics.teacher_caseload')); ?></h3>
    <p class="text-xs text-[#8b949d] mb-4"><?php echo e(__('analytics.tc_subtitle')); ?></p>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[760px]">
            <thead class="bg-[#faf8f3]">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-[#182438] uppercase"><?php echo e(__('analytics.th_teacher')); ?></th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-[#182438] uppercase"><?php echo e(__('analytics.th_students')); ?></th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-[#182438] uppercase"><?php echo e(__('analytics.th_active')); ?></th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-[#182438] uppercase"><?php echo e(__('analytics.th_levels_played')); ?></th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-[#182438] uppercase"><?php echo e(__('analytics.th_avg_accuracy')); ?></th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-[#182438] uppercase"><?php echo e(__('analytics.th_eccd_achieved')); ?></th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-[#182438] uppercase"><?php echo e(__('analytics.th_rating')); ?></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#ebe8e1]">
                <?php $__empty_1 = true; $__currentLoopData = $teacherWorkload; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="hover:bg-[#faf8f3] transition">
                    <td class="px-4 py-3 font-medium text-[#182438]"><?php echo e($t['name']); ?></td>
                    <td class="px-4 py-3 text-center text-[#5f6873]"><?php echo e($t['students']); ?></td>
                    <td class="px-4 py-3 text-center text-[#5f6873]"><?php echo e($t['active_students']); ?></td>
                    <td class="px-4 py-3 text-center text-[#5f6873]"><?php echo e($t['plays']); ?></td>
                    <td class="px-4 py-3 text-center font-bold text-[#182438]"><?php echo e($t['avg_pct']); ?>%</td>
                    <td class="px-4 py-3 text-center text-[#5f6873]"><?php echo e($t['eccd_pct']); ?>%</td>
                    <td class="px-4 py-3">
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold
                            <?php echo e($t['avg_pct'] >= 75 ? 'bg-[#e2f4e8] text-[#3d7a1c]' : ($t['avg_pct'] >= 50 ? 'bg-[#fff6e5] text-[#8a6d1f]' : 'bg-[#fde8ef] text-[#c0392f]')); ?>">
                            <?php echo e($describe($t['avg_pct'])); ?>

                        </span>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="7" class="px-4 py-8 text-center text-[#8b949d]"><?php echo e(__('analytics.no_teacher_accounts')); ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-1"><?php echo e(__('analytics.top_performing')); ?></h3>
        <p class="text-xs text-[#8b949d] mb-4"><?php echo e(__('analytics.tp_subtitle')); ?></p>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[640px]">
                <thead class="bg-[#faf8f3]">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-[#182438] uppercase"><?php echo e(__('analytics.th_student')); ?></th>
                        <?php $__currentLoopData = \App\Services\AnalyticsService::GAME_TYPES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $game): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <th class="px-2 py-2 text-center text-[11px] font-semibold text-[#5f6873] uppercase"><?php echo e(ucfirst($game)); ?></th>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <th class="px-3 py-2 text-center text-xs font-semibold text-[#182438] uppercase"><?php echo e(__('analytics.th_avg')); ?></th>
                        <th class="px-3 py-2 text-center text-xs font-semibold text-[#b8860b] uppercase"><?php echo e(__('analytics.th_points')); ?></th>
                        <th class="px-3 py-2 text-center text-xs font-semibold text-[#182438] uppercase"><?php echo e(__('analytics.th_plays')); ?></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#ebe8e1]">
                    <?php $__empty_1 = true; $__currentLoopData = $topStudents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-[#faf8f3] transition">
                        <td class="px-3 py-2.5">
                            <p class="font-medium text-[#182438] whitespace-nowrap"><?php echo e($s['name']); ?></p>
                            <p class="text-xs text-[#8b949d] inline-flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full" style="background-color: <?php echo e(\App\Models\Student::colorForSection($s['class_name'])); ?>"></span>
                                <?php echo e(\App\Models\Student::sectionLabel($s['class_name'])); ?>

                            </p>
                        </td>
                        <?php $__currentLoopData = \App\Services\AnalyticsService::GAME_TYPES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $game): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <td class="px-2 py-2.5 text-center text-sm"><?php if (isset($component)) { $__componentOriginal283f4cd961d26d85868358213e66e135 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal283f4cd961d26d85868358213e66e135 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.score-cell','data' => ['value' => $s['games'][$game] ?? null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('score-cell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($s['games'][$game] ?? null)]); ?>
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
                        <td class="px-3 py-2.5 text-center">
                            <span class="font-bold text-[#68b82f]"><?php echo e($s['avg_pct']); ?>%</span>
                        </td>
                        <td class="px-3 py-2.5 text-center font-bold text-[#b8860b]"><?php echo e(number_format($s['total_points'] ?? 0)); ?></td>
                        <td class="px-3 py-2.5 text-center text-[#5f6873]"><?php echo e($s['plays']); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="9" class="px-3 py-8 text-center text-[#8b949d]"><?php echo e(__('analytics.no_game_data')); ?></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-1"><?php echo e(__('analytics.needing_support')); ?></h3>
        <p class="text-xs text-[#8b949d] mb-4"><?php echo e(__('analytics.rs_subtitle')); ?></p>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[700px]">
                <thead class="bg-[#faf8f3]">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-[#182438] uppercase"><?php echo e(__('analytics.th_student')); ?></th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-[#182438] uppercase"><?php echo e(__('analytics.th_teacher')); ?></th>
                        <?php $__currentLoopData = \App\Services\AnalyticsService::GAME_TYPES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $game): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <th class="px-2 py-2 text-center text-[11px] font-semibold text-[#5f6873] uppercase"><?php echo e(ucfirst($game)); ?></th>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <th class="px-3 py-2 text-center text-xs font-semibold text-[#182438] uppercase"><?php echo e(__('analytics.th_avg')); ?></th>
                        <th class="px-3 py-2 text-center text-xs font-semibold text-[#b8860b] uppercase"><?php echo e(__('analytics.th_points')); ?></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#ebe8e1]">
                    <?php $__empty_1 = true; $__currentLoopData = $needsSupport; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-[#faf8f3] transition">
                        <td class="px-3 py-2.5">
                            <p class="font-medium text-[#182438] whitespace-nowrap"><?php echo e($s['name']); ?></p>
                            <p class="text-xs text-[#8b949d] inline-flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full" style="background-color: <?php echo e(\App\Models\Student::colorForSection($s['class_name'])); ?>"></span>
                                <?php echo e(\App\Models\Student::sectionLabel($s['class_name'])); ?>

                            </p>
                        </td>
                        <td class="px-3 py-2.5 text-xs text-[#5f6873]"><?php echo e($s['teacher'] ?? __('analytics.unassigned')); ?></td>
                        <?php $__currentLoopData = \App\Services\AnalyticsService::GAME_TYPES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $game): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <td class="px-2 py-2.5 text-center text-sm"><?php if (isset($component)) { $__componentOriginal283f4cd961d26d85868358213e66e135 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal283f4cd961d26d85868358213e66e135 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.score-cell','data' => ['value' => $s['games'][$game] ?? null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('score-cell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($s['games'][$game] ?? null)]); ?>
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
                        <td class="px-3 py-2.5 text-center">
                            <span class="font-bold <?php echo e($s['avg_pct'] < 50 ? 'text-[#f05d56]' : 'text-[#d9b348]'); ?>"><?php echo e($s['avg_pct']); ?>%</span>
                        </td>
                        <td class="px-3 py-2.5 text-center font-bold text-[#b8860b]"><?php echo e(number_format($s['total_points'] ?? 0)); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="9" class="px-3 py-8 text-center text-[#8b949d]"><?php echo e(__('analytics.no_game_data')); ?></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>


<div class="grid grid-cols-1 <?php echo e($selectedStudent ? '' : 'lg:grid-cols-2'); ?> gap-6">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-1"><?php echo e(__('analytics.centre_interventions')); ?></h3>
        <p class="text-xs text-[#8b949d] mb-4"><?php echo e(__('analytics.ci_subtitle')); ?></p>
        <div class="space-y-3 max-h-[340px] overflow-y-auto pr-1">
            <?php $__empty_1 = true; $__currentLoopData = $recommendations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="p-4 rounded-lg border-l-4
                    <?php echo e($rec['severity'] === 'high' ? 'bg-[#fde8ef] border-[#f05d56]' : ($rec['severity'] === 'medium' ? 'bg-[#fff6e5] border-[#d9b348]' : 'bg-[#e2f4e8] border-[#68b82f]')); ?>">
                    <div class="flex items-center justify-between mb-1">
                        <h4 class="font-semibold text-[#182438]"><?php echo e($rec['name']); ?></h4>
                        <span class="text-[10px] font-bold uppercase tracking-wide px-2 py-0.5 rounded-full
                            <?php echo e($rec['severity'] === 'high' ? 'bg-[#f05d56] text-white' : ($rec['severity'] === 'medium' ? 'bg-[#d9b348] text-white' : 'bg-[#68b82f] text-white')); ?>">
                            <?php echo e(__("analytics.severity_{$rec['severity']}")); ?>

                        </span>
                    </div>
                    <p class="text-sm text-[#5f6873] mt-1"><span class="font-medium"><?php echo e(__('analytics.needs_label')); ?></span> <?php echo e($rec['needs']); ?></p>
                    <p class="text-sm text-[#5f6873]"><span class="font-medium"><?php echo e(__('analytics.plan_label')); ?></span> <?php echo e($rec['plan']); ?></p>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="p-6 rounded-lg bg-[#e2f4e8] border border-[#b8e0c2] text-center">
                    <p class="text-[#182438] font-semibold"><?php echo e(__('analytics.no_interventions_needed')); ?></p>
                    <p class="text-sm text-[#5f6873] mt-1"><?php echo e(__('analytics.no_interventions_desc_admin')); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (! ($selectedStudent)): ?>
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-1"><?php echo e(__('analytics.students_per_class')); ?></h3>
        <p class="text-xs text-[#8b949d] mb-4"><?php echo e(__('analytics.spc_subtitle')); ?></p>
        <?php if(count($classDistribution)): ?>
            <canvas id="classChart" height="300"></canvas>
        <?php else: ?>
            <p class="text-sm text-[#8b949d] py-12 text-center"><?php echo e(__('analytics.no_enrolments_yet')); ?></p>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script data-page-script>
const chartIfPresent = (id, config) => { const el = document.getElementById(id); if (el) new Chart(el, config); };
const roleGrowth = <?php echo json_encode($roleGrowth, 15, 512) ?>;
const enrollmentTrend = <?php echo json_encode($enrollmentTrend, 15, 512) ?>;
const attendanceRate = <?php echo json_encode($attendanceRate, 15, 512) ?>;
const weeklyBreakdown = <?php echo json_encode($weeklyBreakdown, 15, 512) ?>;
const gamePopularity = <?php echo json_encode($gamePopularity, 15, 512) ?>;
const domainMastery = <?php echo json_encode($domainMastery, 15, 512) ?>;
const gamePlayTrend = <?php echo json_encode($gamePlayTrend, 15, 512) ?>;
const classDistribution = <?php echo json_encode($classDistribution, 15, 512) ?>;

const gridColor = 'rgba(24, 36, 56, 0.08)';

const L = {
    'teachers': <?php echo json_encode(__('analytics.chart_teachers'), 15, 512) ?>,
    'parents': <?php echo json_encode(__('analytics.chart_parents'), 15, 512) ?>,
    'students': <?php echo json_encode(__('analytics.chart_students'), 15, 512) ?>,
    'new_enrolments': <?php echo json_encode(__('analytics.chart_new_enrolments'), 15, 512) ?>,
    'present': <?php echo json_encode(__('analytics.chart_present'), 15, 512) ?>,
    'absent': <?php echo json_encode(__('analytics.chart_absent'), 15, 512) ?>,
    'late': <?php echo json_encode(__('analytics.chart_late'), 15, 512) ?>,
    'present_rate': <?php echo json_encode(__('analytics.chart_present_rate'), 15, 512) ?>,
    'levels_played': <?php echo json_encode(__('analytics.chart_levels_played'), 15, 512) ?>,
    'avg_accuracy': <?php echo json_encode(__('analytics.chart_avg_accuracy'), 15, 512) ?>,
    'levels': <?php echo json_encode(__('analytics.chart_levels'), 15, 512) ?>,
    'pct_achieved': <?php echo json_encode(__('analytics.chart_pct_achieved'), 15, 512) ?>,
    'indicators_pct': <?php echo json_encode(__('analytics.chart_indicators', ['pct' => ':pct', 'count' => ':count'])) ?>,
    'unique_students': <?php echo json_encode(__('analytics.chart_unique_students'), 15, 512) ?>,
    'students_tooltip': <?php echo json_encode(__('analytics.chart_students_tooltip', ['label' => ':label', 'count' => ':count'])) ?>,
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

<?php if($selectedStudent): ?>
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

<?php endif; ?>

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
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kidlink-laravel\resources\views/admin/analytics.blade.php ENDPATH**/ ?>