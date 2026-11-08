<?php $__env->startSection('title', __('parent_ui.milestones_title')); ?>
<?php $__env->startSection('page-title', __('parent_ui.milestones_title')); ?>

<?php $__env->startSection('content'); ?>
<?php if(!$child): ?>
<div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 rounded-lg mb-6">
    <p class="text-yellow-700"><?php echo e(__('parent_ui.no_child_selected')); ?></p>
</div>
<?php else: ?>
<?php echo $__env->make('components.child_selector', ['children' => $children, 'child' => $child], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php
    $statusStyle = [
        'Achieved' => ['bg' => '#dcfce7', 'fg' => '#16a34a', 'icon' => '<i class="ico ico-checkmark-circle text-xl" aria-hidden="true"></i>', 'label' => __('parent_ui.status_achieved')],
        'In Progress' => ['bg' => '#dbeafe', 'fg' => '#2563eb', 'icon' => '<i class="ico ico-star-half text-xl" aria-hidden="true"></i>', 'label' => __('parent_ui.status_in_progress')],
        'Not Yet' => ['bg' => '#f3f4f6', 'fg' => '#6b7280', 'icon' => '<i class="ico ico-remove-circle text-xl" aria-hidden="true"></i>', 'label' => __('parent_ui.status_not_yet')],
    ];
    $barColor = fn (int $pct) => $pct >= 80 ? '#22c55e' : ($pct >= 50 ? '#3b82f6' : '#f97316');
?>

<div class="mb-6">
    <h2 class="text-xl font-bold text-gray-800"><?php echo e(__('parent_ui.child_milestones', ['name' => $child->first_name])); ?></h2>
    <p class="text-gray-500 text-sm"><?php echo e(__('parent_ui.overall_prefix')); ?> <span class="font-bold"><?php echo e($overallPercentage); ?>%</span> <?php echo e(__('parent_ui.overall_suffix')); ?></p>
</div>

<?php if($totalIndicators === 0): ?>
<div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
    <p class="text-gray-400 text-center py-8"><?php echo e(__('parent_ui.no_milestone_records')); ?></p>
</div>
<?php else: ?>

<!-- Status totals -->
<div class="grid grid-cols-3 gap-4 mb-6">
    <?php $__currentLoopData = $statusStyle; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status => $style): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100 text-center">
        <p class="text-2xl font-bold" style="color: <?php echo e($style['fg']); ?>;"><?php echo e($counts[$status]); ?></p>
        <p class="text-xs font-semibold text-gray-400 uppercase mt-1"><?php echo e($style['label']); ?></p>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<!-- Domain Stats -->
<div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-8">
    <?php $__currentLoopData = $domainStats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $domainName => $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
        <p class="text-sm font-bold text-gray-700 mb-2"><?php echo e($stat['label']); ?></p>
        <div class="flex items-center gap-2 mb-2">
            <span class="text-2xl font-bold" style="color: <?php echo e($barColor($stat['percentage'])); ?>;"><?php echo e($stat['percentage']); ?>%</span>
        </div>
        <div class="w-full bg-gray-100 rounded-full h-2">
            <div class="h-2 rounded-full" style="width: <?php echo e($stat['percentage']); ?>%; background: <?php echo e($barColor($stat['percentage'])); ?>;"></div>
        </div>
        <p class="text-xs text-gray-400 mt-1"><?php echo e($stat['achieved']); ?>/<?php echo e($stat['total']); ?> <?php echo e(__('parent_ui.achieved_label')); ?></p>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<!-- Domain Filter (keeps the selected child) -->
<div class="flex flex-wrap gap-2 mb-6" data-ajax-filter-scope>
    <a href="<?php echo e(route('parent.milestones.index', ['child_id' => $child->id])); ?>" class="px-4 py-2 rounded-lg text-sm font-semibold transition <?php echo e($activeDomain === 'all' ? 'bg-orange-500 text-white' : 'bg-white text-gray-500 border border-gray-200 hover:border-orange-300'); ?>"><?php echo e(__('parent_ui.filter_all')); ?></a>
    <?php $__currentLoopData = $domains; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <a href="<?php echo e(route('parent.milestones.index', ['child_id' => $child->id, 'domain' => $d])); ?>" class="px-4 py-2 rounded-lg text-sm font-semibold transition <?php echo e($activeDomain === $d ? 'bg-orange-500 text-white' : 'bg-white text-gray-500 border border-gray-200 hover:border-orange-300'); ?>"><?php echo e($domainStats[$d]['label']); ?></a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<!-- Records, grouped by domain -->
<?php $__currentLoopData = $recordsByDomain; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $domainName => $rows): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 mb-6">
    <h3 class="text-base font-bold text-gray-800 mb-2"><?php echo e($domainStats[$domainName]['label'] ?? $domainName); ?></h3>
    <?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php $s = $statusStyle[$record->status] ?? $statusStyle['Not Yet']; ?>
    <div class="flex items-center gap-4 py-3 <?php echo e($loop->last ? '' : 'border-b border-gray-50'); ?>">
        <div class="w-9 h-9 rounded-xl flex items-center justify-center text-sm font-bold flex-shrink-0" style="background: <?php echo e($s['bg']); ?>; color: <?php echo e($s['fg']); ?>;"><?php echo $s['icon']; ?></div>
        <p class="flex-1 text-sm font-semibold text-gray-700"><?php echo e($record->indicator_label); ?></p>
        <span class="px-3 py-1 rounded-full text-xs font-bold flex-shrink-0" style="background: <?php echo e($s['bg']); ?>; color: <?php echo e($s['fg']); ?>;"><?php echo e($s['label']); ?></span>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<?php endif; ?>

<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.parent', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kidlink-laravel\resources\views/parent/milestones.blade.php ENDPATH**/ ?>