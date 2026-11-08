<?php $__env->startSection('title', __('parent_ui.results_title')); ?>
<?php $__env->startSection('page-title', __('parent_ui.results_title')); ?>

<?php $__env->startSection('content'); ?>
<?php if(!$child): ?>
<div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 rounded-lg mb-6">
    <p class="text-yellow-700"><?php echo e(__('parent_ui.no_child_selected')); ?></p>
</div>
<?php else: ?>
<?php echo $__env->make('components.child_selector', ['children' => $children, 'child' => $child], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php
    $tier = fn (int $pct) => $pct >= 80
        ? ['solid' => '#22c55e', 'bg' => '#dcfce7', 'fg' => '#16a34a', 'label' => __('parent_ui.label_excellent')]
        : ($pct >= 60
            ? ['solid' => '#3b82f6', 'bg' => '#dbeafe', 'fg' => '#2563eb', 'label' => __('parent_ui.label_good')]
            : ['solid' => '#ef4444', 'bg' => '#fee2e2', 'fg' => '#dc2626', 'label' => __('parent_ui.label_needs_work')]);
?>

<div class="mb-6">
    <h2 class="text-xl font-bold text-gray-800"><?php echo e(__('parent_ui.child_results', ['name' => $child->first_name])); ?></h2>
    <p class="text-gray-500 text-sm"><?php echo e(__('parent_ui.results_subtitle', ['name' => $child->first_name])); ?></p>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
    <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-blue-500">
        <p class="text-gray-400 text-xs font-semibold uppercase"><?php echo e(__('parent_ui.stat_avg_accuracy')); ?></p>
        <p class="text-2xl font-bold text-gray-800 mt-1"><?php echo e($avgAccuracy); ?>%</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-green-500">
        <p class="text-gray-400 text-xs font-semibold uppercase"><?php echo e(__('parent_ui.stat_levels_completed')); ?></p>
        <p class="text-2xl font-bold text-green-600 mt-1"><?php echo e($totalLevels); ?></p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-yellow-500">
        <p class="text-gray-400 text-xs font-semibold uppercase"><?php echo e(__('parent_ui.stat_games_played')); ?></p>
        <p class="text-2xl font-bold text-yellow-600 mt-1"><?php echo e($byGame->count()); ?></p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-orange-500">
        <p class="text-gray-400 text-xs font-semibold uppercase"><?php echo e(__('parent_ui.stat_best_game')); ?></p>
        <p class="text-lg font-bold text-orange-600 mt-1 truncate"><?php echo e($bestGame['game'] ?? '—'); ?></p>
    </div>
</div>

<?php if($totalLevels > 0): ?>
<div class="flex flex-wrap gap-2 mb-8 text-xs font-bold">
    <span class="px-3 py-1 rounded-full" style="background:#dcfce7;color:#16a34a;"><?php echo e(__('parent_ui.stat_excellent')); ?>: <?php echo e($excellent); ?></span>
    <span class="px-3 py-1 rounded-full" style="background:#dbeafe;color:#2563eb;"><?php echo e(__('parent_ui.stat_good')); ?>: <?php echo e($good); ?></span>
    <span class="px-3 py-1 rounded-full" style="background:#fee2e2;color:#dc2626;"><?php echo e(__('parent_ui.stat_needs_work')); ?>: <?php echo e($needsWork); ?></span>
</div>
<?php else: ?>
<div class="mb-8"></div>
<?php endif; ?>

<?php if($totalLevels === 0): ?>
<div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 mb-8">
    <p class="text-gray-400 text-center py-8"><?php echo e(__('parent_ui.no_game_scores', ['name' => $child->first_name])); ?></p>
</div>
<?php else: ?>


<div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 mb-8">
    <h3 class="text-lg font-bold text-gray-800 mb-4"><?php echo e(__('parent_ui.by_game')); ?></h3>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs font-semibold uppercase text-gray-400 border-b border-gray-100">
                    <th class="py-2 pr-4"><?php echo e(__('parent_ui.col_game')); ?></th>
                    <th class="py-2 px-4 text-center"><?php echo e(__('parent_ui.col_levels')); ?></th>
                    <th class="py-2 px-4 text-center"><?php echo e(__('parent_ui.col_highest_level')); ?></th>
                    <th class="py-2 px-4 text-center"><?php echo e(__('parent_ui.col_mistakes')); ?></th>
                    <th class="py-2 pl-4 w-48"><?php echo e(__('parent_ui.col_accuracy')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $byGame; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $t = $tier($row['accuracy']); ?>
                <tr class="<?php echo e($loop->last ? '' : 'border-b border-gray-50'); ?>">
                    <td class="py-3 pr-4 font-semibold text-gray-700"><?php echo e($row['game']); ?></td>
                    <td class="py-3 px-4 text-center text-gray-600"><?php echo e($row['levels']); ?></td>
                    <td class="py-3 px-4 text-center text-gray-600"><?php echo e($row['highest_level']); ?></td>
                    <td class="py-3 px-4 text-center text-gray-600"><?php echo e($row['mistakes']); ?></td>
                    <td class="py-3 pl-4">
                        <div class="flex items-center gap-2">
                            <div class="flex-1 bg-gray-100 rounded-full h-2">
                                <div class="h-2 rounded-full" style="width: <?php echo e($row['accuracy']); ?>%; background: <?php echo e($t['solid']); ?>;"></div>
                            </div>
                            <span class="text-xs font-bold w-10 text-right" style="color: <?php echo e($t['fg']); ?>;"><?php echo e($row['accuracy']); ?>%</span>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</div>


<div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 mb-8">
    <h3 class="text-lg font-bold text-gray-800 mb-4"><?php echo e(__('parent_ui.recent_game_scores')); ?></h3>

    <div class="flex flex-wrap gap-2 mb-4" data-ajax-filter-scope>
        <a href="<?php echo e(route('parent.results.index', ['child_id' => $child->id])); ?>"
           class="px-3 py-1.5 rounded-lg text-xs font-semibold transition <?php echo e($game === '' ? 'bg-orange-500 text-white' : 'bg-white text-gray-500 border border-gray-200 hover:border-orange-300'); ?>"><?php echo e(__('parent_ui.filter_all')); ?></a>
        <?php $__currentLoopData = $byGame; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a href="<?php echo e(route('parent.results.index', ['child_id' => $child->id, 'game' => $row['game_type']])); ?>"
           class="px-3 py-1.5 rounded-lg text-xs font-semibold transition <?php echo e($game === $row['game_type'] ? 'bg-orange-500 text-white' : 'bg-white text-gray-500 border border-gray-200 hover:border-orange-300'); ?>"><?php echo e($row['game']); ?></a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <?php $__currentLoopData = $history; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php $t = $tier($item['accuracy']); ?>
    <div class="flex items-center gap-4 p-4 <?php echo e($loop->last ? '' : 'border-b border-gray-50'); ?>">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center font-bold text-sm text-white flex-shrink-0" style="background: <?php echo e($t['solid']); ?>;">
            <?php echo e($item['accuracy']); ?>%
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-gray-700 truncate"><?php echo e($item['game']); ?> · <?php echo e(__('parent_ui.level_label', ['n' => $item['level']])); ?></p>
            <p class="text-xs text-gray-400">
                <?php echo e(__('parent_ui.score_of', ['score' => $item['score'], 'total' => $item['total']])); ?>

                · <?php echo e(trans_choice('parent_ui.mistakes_count', $item['mistakes'], ['count' => $item['mistakes']])); ?>

                <?php if($item['time']): ?> · <?php echo e($item['time']); ?> <?php endif; ?>
                · <?php echo e($item['played_at']?->translatedFormat('M j, Y')); ?>

            </p>
        </div>
        <span class="px-3 py-1 rounded-full text-xs font-bold flex-shrink-0" style="background: <?php echo e($t['bg']); ?>; color: <?php echo e($t['fg']); ?>;"><?php echo e($t['label']); ?></span>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <?php if($scores->count() > $historyLimit && $game === ''): ?>
    <p class="text-xs text-gray-400 text-center mt-4"><?php echo e(__('parent_ui.showing_latest', ['count' => $historyLimit])); ?></p>
    <?php endif; ?>
</div>
<?php endif; ?>


<?php if($activityResults->isNotEmpty()): ?>
<div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
    <h3 class="text-lg font-bold text-gray-800 mb-4"><?php echo e(__('parent_ui.classroom_scores')); ?></h3>
    <?php $__currentLoopData = $activityResults; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php $t = $tier((int) round($item->score ?? 0)); ?>
    <div class="flex items-center gap-4 p-4 <?php echo e($loop->last ? '' : 'border-b border-gray-50'); ?>">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center font-bold text-sm text-white flex-shrink-0" style="background: <?php echo e($t['solid']); ?>;">
            <?php echo e(round($item->score ?? 0)); ?>

        </div>
        <div class="flex-1">
            <p class="text-sm font-semibold text-gray-700"><?php echo e($item->activity->title ?? __('parent_ui.activity_fallback')); ?></p>
            <p class="text-xs text-gray-400"><?php echo e($item->created_at->diffForHumans()); ?></p>
        </div>
        <span class="px-3 py-1 rounded-full text-xs font-bold" style="background: <?php echo e($t['bg']); ?>; color: <?php echo e($t['fg']); ?>;"><?php echo e($t['label']); ?></span>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>

<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.parent', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kidlink-laravel\resources\views/parent/results.blade.php ENDPATH**/ ?>