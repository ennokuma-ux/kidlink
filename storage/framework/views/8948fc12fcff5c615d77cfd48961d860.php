<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['days', 'unit' => 'day']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['days', 'unit' => 'day']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $days = collect($days);
    $todayKey = \Carbon\Carbon::today()->toDateString();
    $sumPresent = $days->sum('present');
    $sumLate = $days->sum('late');
    $sumAbsent = $days->sum('absent');
    $sumMarks = $sumPresent + $sumLate + $sumAbsent;
    $sumRate = $sumMarks > 0 ? (int) round((($sumPresent + $sumLate) / $sumMarks) * 100) : null;
?>

<div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="text-left text-[11px] font-semibold uppercase tracking-wide text-[#8b949d]">
                <th class="py-2 pr-3"><?php echo e($unit === 'week' ? __('analytics.period_column_week') : __('analytics.week_day')); ?></th>
                <th class="py-2 px-2 text-center"><?php echo e(__('analytics.week_present')); ?></th>
                <th class="py-2 px-2 text-center"><?php echo e(__('analytics.week_late')); ?></th>
                <th class="py-2 px-2 text-center"><?php echo e(__('analytics.week_absent')); ?></th>
                <th class="py-2 pl-3 w-40"><?php echo e(__('analytics.week_rate')); ?></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-[#f1e7cc]">
            <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $marks = $day['present'] + $day['late'] + $day['absent'];
                    $rate = $marks > 0 ? (int) round((($day['present'] + $day['late']) / $marks) * 100) : null;
                    $isToday = $day['date'] <= $todayKey && ($day['end'] ?? $day['date']) >= $todayKey;
                ?>
                <tr class="<?php echo e($isToday ? 'bg-[#fff1f1]' : ''); ?>">
                    <td class="py-2 pr-3 font-medium text-[#182438] whitespace-nowrap">
                        <?php echo e($day['day']); ?>

                        <?php if($isToday): ?><span class="ml-1 px-1.5 py-0.5 rounded bg-[#ff595e] text-white text-[10px] font-semibold"><?php echo e(__('analytics.week_today')); ?></span><?php endif; ?>
                    </td>
                    <td class="py-2 px-2 text-center font-semibold text-[#8ac926]"><?php echo e($marks ? $day['present'] : '–'); ?></td>
                    <td class="py-2 px-2 text-center font-semibold text-[#ffca3a]"><?php echo e($marks ? $day['late'] : '–'); ?></td>
                    <td class="py-2 px-2 text-center font-semibold text-[#ff595e]"><?php echo e($marks ? $day['absent'] : '–'); ?></td>
                    <td class="py-2 pl-3">
                        <?php if($rate === null): ?>
                            <span class="text-xs text-[#8b949d]"><?php echo e(__('analytics.week_none')); ?></span>
                        <?php else: ?>
                            <div class="flex items-center gap-2">
                                <div class="flex-1 h-2 rounded-full bg-[#e6ebf3] overflow-hidden">
                                    <div class="h-2 rounded-full <?php echo e($rate >= 85 ? 'bg-[#8ac926]' : ($rate >= 60 ? 'bg-[#ffca3a]' : 'bg-[#ff595e]')); ?>" style="width: <?php echo e($rate); ?>%"></div>
                                </div>
                                <span class="w-10 text-right text-xs font-semibold text-[#182438]"><?php echo e($rate); ?>%</span>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
        <tfoot>
            <tr class="border-t-2 border-[#f1e7cc] font-bold text-[#182438]">
                <td class="py-2 pr-3"><?php echo e(__('analytics.week_total')); ?></td>
                <td class="py-2 px-2 text-center text-[#8ac926]"><?php echo e($sumPresent); ?></td>
                <td class="py-2 px-2 text-center text-[#ffca3a]"><?php echo e($sumLate); ?></td>
                <td class="py-2 px-2 text-center text-[#ff595e]"><?php echo e($sumAbsent); ?></td>
                <td class="py-2 pl-3 text-right text-xs"><?php echo e($sumRate === null ? '' : $sumRate.'%'); ?></td>
            </tr>
        </tfoot>
    </table>
</div>
<p class="mt-3 text-xs text-[#5f6873]">
    <?php if($sumRate === null): ?>
        <?php echo e(__('analytics.week_empty')); ?>

    <?php else: ?>
        <?php echo e(__('analytics.week_summary', ['rate' => $sumRate, 'attended' => $sumPresent + $sumLate, 'total' => $sumMarks])); ?>

    <?php endif; ?>
</p>
<?php /**PATH C:\laragon\www\kidlink-laravel\resources\views/components/week-attendance.blade.php ENDPATH**/ ?>