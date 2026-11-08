<?php $__env->startSection('title', __('parent_ui.attendance_title')); ?>
<?php $__env->startSection('page-title', __('parent_ui.attendance_title')); ?>

<?php $__env->startSection('content'); ?>
<?php if(!$child): ?>
<div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 rounded-lg mb-6">
    <p class="text-yellow-700"><?php echo e(__('parent_ui.no_child_selected')); ?></p>
</div>
<?php else: ?>
<?php echo $__env->make('components.child_selector', ['children' => $children, 'child' => $child], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="mb-6">
    <h2 class="text-xl font-bold text-gray-800"><?php echo e(__('parent_ui.child_attendance', ['name' => $child->first_name])); ?></h2>
    <p class="text-gray-500 text-sm"><?php echo e($month->translatedFormat('F Y')); ?></p>
</div>

<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-3">
    <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-green-500">
        <p class="text-gray-400 text-xs font-semibold uppercase"><?php echo e(__('parent_ui.stat_present')); ?></p>
        <p class="text-2xl font-bold text-green-600 mt-1"><?php echo e($present); ?></p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-yellow-500">
        <p class="text-gray-400 text-xs font-semibold uppercase"><?php echo e(__('parent_ui.stat_late')); ?></p>
        <p class="text-2xl font-bold text-yellow-500 mt-1"><?php echo e($late); ?></p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-red-500">
        <p class="text-gray-400 text-xs font-semibold uppercase"><?php echo e(__('parent_ui.stat_absent')); ?></p>
        <p class="text-2xl font-bold text-red-500 mt-1"><?php echo e($absent); ?></p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-sky-400">
        <p class="text-gray-400 text-xs font-semibold uppercase"><?php echo e(__('parent_ui.stat_excused')); ?></p>
        <p class="text-2xl font-bold text-sky-500 mt-1"><?php echo e($excused); ?></p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-blue-500 col-span-2 md:col-span-1">
        <p class="text-gray-400 text-xs font-semibold uppercase"><?php echo e(__('parent_ui.stat_rate')); ?></p>
        <p class="text-2xl font-bold text-blue-500 mt-1"><?php echo e($total > 0 ? $rate.'%' : '—'); ?></p>
    </div>
</div>
<p class="text-xs text-gray-400 mb-8"><?php echo e(__('parent_ui.rate_note')); ?></p>

<!-- Month Navigation (keeps the selected child) -->
<div class="flex items-center gap-3 mb-6" data-ajax-filter-scope>
    <a href="<?php echo e(route('parent.attendance.index', ['child_id' => $child->id, 'month' => $month->copy()->subMonthNoOverflow()->format('Y-m')])); ?>" class="px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-sm font-semibold hover:border-orange-300 transition"><?php echo e(__('parent_ui.prev_month')); ?></a>
    <span class="text-sm font-bold text-gray-700"><?php echo e($month->translatedFormat('F Y')); ?></span>
    <?php if($canGoNext): ?>
    <a href="<?php echo e(route('parent.attendance.index', ['child_id' => $child->id, 'month' => $month->copy()->addMonthNoOverflow()->format('Y-m')])); ?>" class="px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-sm font-semibold hover:border-orange-300 transition"><?php echo e(__('parent_ui.next_month')); ?></a>
    <?php else: ?>
    <span class="px-3 py-1.5 bg-gray-50 border border-gray-100 rounded-lg text-sm font-semibold text-gray-300 cursor-not-allowed"><?php echo e(__('parent_ui.next_month')); ?></span>
    <?php endif; ?>
</div>

<!-- Calendar Grid -->
<?php
    $statusLabels = [
        'Present' => __('parent_ui.stat_present'),
        'Absent' => __('parent_ui.stat_absent'),
        'Late' => __('parent_ui.stat_late'),
        'Excused' => __('parent_ui.stat_excused'),
    ];
    // What a tapped day reports: its date, the status the teacher assigned, and a note when there is none.
    $dayInfo = function (array $day) use ($statusLabels) {
        $note = null;
        if (! isset($statusLabels[$day['status']])) {
            $note = $day['weekend'] ? __('Weekend') : ($day['future'] ? __('Upcoming day') : __('parent_ui.legend_no_record'));
        }

        return [
            'date' => $day['date'],
            'label' => \Carbon\Carbon::parse($day['date'])->translatedFormat('l, F j, Y'),
            'status' => isset($statusLabels[$day['status']]) ? $day['status'] : 'None',
            'text' => $statusLabels[$day['status']] ?? $note,
        ];
    };
    $today = \Carbon\Carbon::today()->format('Y-m-d');
    $todayDay = collect($monthlyChart)->firstWhere('date', $today);
?>
<div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 mb-8" x-data="{ picked: <?php echo \Illuminate\Support\Js::from($todayDay ? $dayInfo($todayDay) : null)->toHtml() ?> }">
    <?php if($total === 0): ?>
    <p class="text-sm text-gray-400 mb-4"><?php echo e(__('parent_ui.no_attendance_month')); ?></p>
    <?php endif; ?>
    <div class="grid grid-cols-7 gap-2">
        <?php $__currentLoopData = [__('parent_ui.day_sun'), __('parent_ui.day_mon'), __('parent_ui.day_tue'), __('parent_ui.day_wed'), __('parent_ui.day_thu'), __('parent_ui.day_fri'), __('parent_ui.day_sat')]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dayName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="text-center text-xs font-bold text-gray-400 pb-2"><?php echo e($dayName); ?></div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php for($i = 0; $i < $month->copy()->startOfMonth()->dayOfWeek; $i++): ?>
        <div></div>
        <?php endfor; ?>
        <?php $__currentLoopData = $monthlyChart; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
        $bg = match($day['status']) {
            'Present' => 'bg-green-100 text-green-700',
            'Absent' => 'bg-red-100 text-red-700',
            'Late' => 'bg-yellow-100 text-yellow-700',
            'Excused' => 'bg-sky-100 text-sky-700',
            default => ($day['weekend'] || $day['future']) ? 'bg-white text-gray-300' : 'bg-gray-50 text-gray-400',
        };
        $info = $dayInfo($day);
        ?>
        <button type="button" @click="picked = <?php echo \Illuminate\Support\Js::from($info)->toHtml() ?>" :aria-pressed="picked && picked.date === '<?php echo e($day['date']); ?>' ? 'true' : 'false'" :class="{ 'ring-2 ring-orange-500': picked && picked.date === '<?php echo e($day['date']); ?>' }" class="text-center p-2 rounded-lg text-sm font-semibold transition hover:ring-2 hover:ring-orange-300 focus:outline-none focus:ring-2 focus:ring-orange-400 <?php echo e($bg); ?>" title="<?php echo e($day['date']); ?> · <?php echo e($info['text']); ?>">
            <?php echo e($day['day']); ?>

        </button>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <div class="mt-5 min-h-[3.5rem] rounded-xl border border-gray-100 bg-gray-50 px-4 py-3 flex flex-wrap items-center justify-between gap-2" aria-live="polite">
        <p x-show="!picked" class="text-sm text-gray-400"><?php echo e(__('Tap a day to see its status.')); ?></p>
        <template x-if="picked">
            <p class="text-sm font-semibold text-gray-700" x-text="picked.label"></p>
        </template>
        <span x-show="picked && picked.status === 'Present'" style="display:none" class="px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700" x-text="picked && picked.text"></span>
        <span x-show="picked && picked.status === 'Late'" style="display:none" class="px-3 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-700" x-text="picked && picked.text"></span>
        <span x-show="picked && picked.status === 'Absent'" style="display:none" class="px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700" x-text="picked && picked.text"></span>
        <span x-show="picked && picked.status === 'Excused'" style="display:none" class="px-3 py-1 rounded-full text-xs font-bold bg-sky-100 text-sky-700" x-text="picked && picked.text"></span>
        <span x-show="picked && picked.status === 'None'" style="display:none" class="px-3 py-1 rounded-full text-xs font-bold bg-gray-200 text-gray-500" x-text="picked && picked.text"></span>
    </div>

    <div class="flex flex-wrap gap-x-4 gap-y-2 mt-5 text-xs text-gray-500">
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-green-100 border border-green-300"></span><?php echo e(__('parent_ui.stat_present')); ?></span>
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-yellow-100 border border-yellow-300"></span><?php echo e(__('parent_ui.stat_late')); ?></span>
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-red-100 border border-red-300"></span><?php echo e(__('parent_ui.stat_absent')); ?></span>
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-sky-100 border border-sky-300"></span><?php echo e(__('parent_ui.stat_excused')); ?></span>
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-gray-50 border border-gray-200"></span><?php echo e(__('parent_ui.legend_no_record')); ?></span>
    </div>
</div>

<!-- Monthly Summary -->
<?php if(count($monthlySummary) > 0): ?>
<div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
    <h3 class="text-lg font-bold text-gray-800 mb-4"><?php echo e(__('parent_ui.monthly_summary')); ?></h3>
    <?php $__currentLoopData = $monthlySummary; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="flex items-center gap-4 p-3 <?php echo e($loop->last ? '' : 'border-b border-gray-50'); ?>">
        <div class="w-24">
            <p class="text-sm font-semibold text-gray-700"><?php echo e($m['month']); ?></p>
        </div>
        <div class="flex-1">
            <div class="w-full bg-gray-100 rounded-full h-2">
                <div class="h-2 rounded-full bg-green-500" style="width: <?php echo e($m['recorded'] > 0 ? $m['rate'] : 0); ?>%"></div>
            </div>
            <p class="text-xs text-gray-400 mt-1">
                <?php if($m['recorded'] > 0): ?>
                <?php echo e(__('parent_ui.summary_days', ['present' => $m['present'], 'late' => $m['late'], 'absent' => $m['absent']])); ?>

                <?php else: ?>
                <?php echo e(__('parent_ui.no_attendance_month')); ?>

                <?php endif; ?>
            </p>
        </div>
        <div class="text-right">
            <span class="text-sm font-bold text-gray-600"><?php echo e($m['recorded'] > 0 ? $m['rate'].'%' : '—'); ?></span>
        </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>

<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.parent', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kidlink-laravel\resources\views/parent/attendance.blade.php ENDPATH**/ ?>