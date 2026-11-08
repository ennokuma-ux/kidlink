<?php $__env->startSection('title', __('teacher.attendance_title')); ?>
<?php $__env->startSection('page-title', __('teacher.daily_attendance_title')); ?>

<?php $__env->startSection('content'); ?>
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <form method="GET" action="<?php echo e(route('teacher.attendance.index')); ?>"
        class="flex flex-col lg:flex-row lg:items-end gap-4" data-ajax-filter>
        <div>
            <label class="block text-[#182438] font-medium mb-2"><?php echo e(__('teacher.select_date')); ?></label>
            <input type="date" name="date" value="<?php echo e($date->format('Y-m-d')); ?>" 
                class="w-full px-4 py-2.5 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
        </div>
        <div class="flex-1 min-w-0">
            <label for="attendanceSearch" class="block text-[#182438] font-medium mb-2"><?php echo e(__('teacher.search_student')); ?></label>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#8b949d]">&nbsp;&#9906;</span>
                <input type="search" name="search" id="attendanceSearch" value="<?php echo e($search); ?>"
                    placeholder="<?php echo e(__('teacher.type_a_name')); ?>" autocomplete="off"
                    class="w-full pl-9 pr-4 py-2.5 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
            </div>
        </div>
        <div class="w-full lg:w-56">
            <label for="attendanceSection" class="block text-[#182438] font-medium mb-2"><?php echo e(__('teacher.section')); ?></label>
            <select name="section" id="attendanceSection" 
                class="w-full px-4 py-2.5 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                <option value=""><?php echo e(__('teacher.all_sections')); ?></option>
                <?php $__currentLoopData = $availableSections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $available): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($available); ?>" <?php echo e($section === $available ? 'selected' : ''); ?>><?php echo e($available); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="w-full lg:w-44">
            <label for="attendanceYear" class="block text-[#182438] font-medium mb-2"><?php echo e(__('Academic year')); ?></label>
            <select name="year" id="attendanceYear"
                class="w-full px-4 py-2.5 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                <option value=""><?php echo e(__('All academic years')); ?></option>
                <?php $__currentLoopData = $availableYears; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $availableYear): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($availableYear); ?>" <?php echo e($year === $availableYear ? 'selected' : ''); ?>><?php echo e($availableYear); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="flex gap-3">
            <button type="submit" class="bg-[#ff8555] text-white px-6 py-2.5 rounded-lg hover:bg-[#f47548] transition font-medium"><?php echo e(__('teacher.apply')); ?></button>
            <?php if($search !== '' || $section !== '' || $year !== ''): ?>
                <a href="<?php echo e(route('teacher.attendance.index', ['date' => $date->format('Y-m-d')])); ?>"
                    class="px-6 py-2.5 border border-[#ebe8e1] rounded-lg text-[#5f6873] hover:bg-[#faf8f3] transition"><?php echo e(__('teacher.clear')); ?></a>
            <?php endif; ?>
        </div>
    </form>

    <?php if($search !== '' || $section !== '' || $year !== ''): ?>
        <p class="text-sm text-[#5f6873] mt-4 pt-4 border-t border-[#ebe8e1]">
            <?php echo e(__('teacher.showing')); ?> <span class="font-semibold text-[#182438]"><?php echo e($students->count()); ?></span>
            <?php echo e(__('teacher.of')); ?> <?php echo e($totalStudents); ?>

            <?php echo e($totalStudents === 1 ? __('teacher.student_singular') : __('teacher.student_plural')); ?><?php if($section): ?> <?php echo e(__('teacher.in_section')); ?> <span class="font-semibold text-[#182438]"><?php echo e($section); ?></span><?php endif; ?>
            <?php if($year): ?> <?php echo e(__('in academic year')); ?> <span class="font-semibold text-[#182438]"><?php echo e($year); ?></span><?php endif; ?>
            <?php if($search): ?> <?php echo e(__('teacher.matching_query')); ?> &ldquo;<span class="font-semibold text-[#182438]"><?php echo e($search); ?></span>&rdquo;<?php endif; ?>.
            <?php echo e(__('teacher.only_shown_saved')); ?>

        </p>
    <?php endif; ?>
</div>

<div class="grid grid-cols-2 sm:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-4 text-center border-l-4 border-[#258cc5]">
<p class="text-2xl font-bold text-[#182438]"><?php echo e($stats['total']); ?></p>
        <p class="text-sm text-[#5f6873]"><?php echo e(($search !== '' || $section !== '' || $year !== '') ? __('teacher.matching_students') : __('teacher.total_students')); ?></p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 text-center border-l-4 border-[#68b82f]">
        <p class="text-2xl font-bold text-[#68b82f]"><?php echo e($stats['present']); ?></p>
        <p class="text-sm text-[#5f6873]"><?php echo e(__('teacher.present')); ?></p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 text-center border-l-4 border-[#d9b348]">
        <p class="text-2xl font-bold text-[#d9b348]"><?php echo e($stats['late']); ?></p>
        <p class="text-sm text-[#5f6873]"><?php echo e(__('teacher.late')); ?></p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 text-center border-l-4 border-[#f05d56]">
        <p class="text-2xl font-bold text-[#f05d56]"><?php echo e($stats['absent']); ?></p>
        <p class="text-sm text-[#5f6873]"><?php echo e(__('teacher.absent')); ?></p>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <form method="POST" action="<?php echo e(route('teacher.attendance.store')); ?>">
        <?php echo csrf_field(); ?>
<input type="hidden" name="date" value="<?php echo e($date->format('Y-m-d')); ?>">
        <input type="hidden" name="search" value="<?php echo e($search); ?>">
        <input type="hidden" name="section" value="<?php echo e($section); ?>">
        <input type="hidden" name="year" value="<?php echo e($year); ?>">
        <?php if($students->count() > 0): ?>
        <div class="px-6 py-4 flex justify-end border-b border-[#ebe8e1]">
            <button type="submit" class="bg-[#ff8555] text-white px-8 py-3 rounded-lg hover:bg-[#f47548] transition font-medium"><?php echo e(__('teacher.save_attendance')); ?></button>
        </div>
        <?php endif; ?>
        <table class="w-full">
            <thead class="bg-[#faf8f3]">
                <tr>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-[#5f6873]"><?php echo e(__('teacher.student')); ?></th>
                    <?php if($availableSections->count() > 1): ?>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-[#5f6873]"><?php echo e(__('teacher.section')); ?></th>
                    <?php endif; ?>
                    <th class="px-6 py-4 text-center text-sm font-semibold text-[#5f6873]"><?php echo e(__('teacher.present')); ?></th>
                    <th class="px-6 py-4 text-center text-sm font-semibold text-[#5f6873]"><?php echo e(__('teacher.late')); ?></th>
                    <th class="px-6 py-4 text-center text-sm font-semibold text-[#5f6873]"><?php echo e(__('teacher.absent')); ?></th>
                    <th class="px-6 py-4 text-center text-sm font-semibold text-[#5f6873]"><?php echo e(__('teacher.excused')); ?></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#ebe8e1]">
                <?php $__empty_1 = true; $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php $record = $attendance->get($student->id); ?>
<tr class="hover:bg-[#faf8f3] transition">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <span class="font-medium text-[#182438]"><?php echo e($student->name); ?></span>
                        </div>
                    </td>
                    <?php if($availableSections->count() > 1): ?>
                        <td class="px-6 py-4">
                            <?php if($student->class_name): ?>
                                <span class="px-2.5 py-1 bg-[#f5f3ee] rounded-full text-xs font-medium text-[#5f6873]"><?php echo e($student->class_name); ?></span>
                            <?php else: ?>
                                <span class="text-xs text-[#8b949d]">&mdash;</span>
                            <?php endif; ?>
                        </td>
                    <?php endif; ?>
                    <?php $__currentLoopData = ['Present','Late','Absent','Excused']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <td class="px-6 py-4 text-center">
                        <label class="inline-flex items-center">
                            <input type="radio" name="attendance[<?php echo e($student->id); ?>]" value="<?php echo e($status); ?>"
                                <?php echo e(($record && $record->status === $status) ? 'checked' : (!$record && $status === 'Present' ? 'checked' : '')); ?>

                                class="w-5 h-5 text-[#ff8555] focus:ring-[#ff8555]">
                            <span class="ml-1 text-sm <?php echo e($record && $record->status === $status ? 'font-semibold' : ''); ?>"></span>
                        </label>
                    </td>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="<?php echo e($availableSections->count() > 1 ? 6 : 5); ?>" class="px-6 py-12 text-center text-[#8b949d]">
                        <?php if($search !== '' || $section !== '' || $year !== ''): ?>
                            <?php echo e(__('teacher.no_students_match')); ?>

                        <?php else: ?>
                            <?php echo e(__('teacher.no_active_students')); ?>

                        <?php endif; ?>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </form>
</div>
<?php if (isset($component)) { $__componentOriginalba42444ba1f0a3a7eab130fb5c850ba8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalba42444ba1f0a3a7eab130fb5c850ba8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.attendance-calendar','data' => ['days' => $calendarDays,'date' => $date->format('Y-m-d'),'baseUrl' => route('teacher.attendance.index'),'search' => $search,'section' => $section,'year' => $year]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('attendance-calendar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['days' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($calendarDays),'date' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($date->format('Y-m-d')),'base-url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('teacher.attendance.index')),'search' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($search),'section' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($section),'year' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($year)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalba42444ba1f0a3a7eab130fb5c850ba8)): ?>
<?php $attributes = $__attributesOriginalba42444ba1f0a3a7eab130fb5c850ba8; ?>
<?php unset($__attributesOriginalba42444ba1f0a3a7eab130fb5c850ba8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalba42444ba1f0a3a7eab130fb5c850ba8)): ?>
<?php $component = $__componentOriginalba42444ba1f0a3a7eab130fb5c850ba8; ?>
<?php unset($__componentOriginalba42444ba1f0a3a7eab130fb5c850ba8); ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
(function () {
    // Delegated handlers survive the in-place table refreshes.
    let searchTimer = null;
    let dateTimer = null;

    document.addEventListener('input', function (event) {
        if (!event.target.matches('#attendanceSearch')) return;
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () {
            const form = event.target.closest('form[data-ajax-filter]');
            if (form) form.requestSubmit();
        }, 300);
    });

    document.addEventListener('change', function (event) {
        const target = event.target;
        if (target.matches('input[name="date"]')) {
            clearTimeout(dateTimer);
            dateTimer = setTimeout(function () {
                const form = target.closest('form[data-ajax-filter]');
                if (form) form.requestSubmit();
            }, 80);
        } else if (target.matches('#attendanceSection, #attendanceYear')) {
            const form = target.closest('form[data-ajax-filter]');
            if (form) form.requestSubmit();
        }

        if (target.matches('input[type="radio"][name^="attendance["]')) {
            const group = document.querySelectorAll('input[type="radio"][name="' + CSS.escape(target.name) + '"]');
            group.forEach(function (radio) {
                const label = radio.nextElementSibling;
                if (label) label.classList.toggle('font-semibold', radio.checked);
            });
        }
    });
})();
</script>
<?php $__env->stopPush(); ?>





<?php echo $__env->make('layouts.teacher', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kidlink-laravel\resources\views/teacher/attendance.blade.php ENDPATH**/ ?>