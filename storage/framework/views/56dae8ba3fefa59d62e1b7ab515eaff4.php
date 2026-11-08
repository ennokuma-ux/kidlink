<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['route', 'scope', 'section' => '', 'student' => null, 'sort' => 'section', 'shown' => 0, 'total' => 0, 'legend' => []]));

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

foreach (array_filter((['route', 'scope', 'section' => '', 'student' => null, 'sort' => 'section', 'shown' => 0, 'total' => 0, 'legend' => []]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $label = match ($scope) {
        'individual' => __('analytics.scope_viewing_student', ['name' => $student?->name]),
        'section' => __('analytics.scope_viewing_section', ['name' => \App\Models\Student::sectionLabel($section)]),
        default => __('analytics.scope_viewing_all'),
    };
    $keep = match ($scope) {
        'individual' => ['student' => $student?->id],
        'section' => ['section' => $section],
        default => ['scope' => 'all'],
    };
?>

<div class="bg-white rounded-xl shadow-sm border border-[#f1e7cc] p-4 mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
        <a href="<?php echo e(route($route)); ?>"
           class="inline-flex items-center gap-2 self-start px-4 py-2 rounded-lg border border-[#f1e7cc] text-sm font-semibold text-[#182438] hover:bg-[#fbf0d6] transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            <?php echo e(__('analytics.scope_back')); ?>

        </a>

        <div class="min-w-0 sm:flex-1">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-[#8b949d]"><?php echo e(__('analytics.scope_viewing')); ?></p>
            <p class="font-bold text-[#182438] truncate"><?php echo e($label); ?></p>
        </div>

        <form method="GET" data-ajax-filter action="<?php echo e(route($route)); ?>" class="flex items-center gap-2">
            <?php $__currentLoopData = $keep; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <input type="hidden" name="<?php echo e($key); ?>" value="<?php echo e($value); ?>">
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <label for="analyticsSort" class="text-xs font-semibold uppercase tracking-wide text-[#5f6873]"><?php echo e(__('analytics.sort_label')); ?></label>
            <select name="sort" id="analyticsSort" onchange="this.form.requestSubmit()"
                    class="rounded-lg border border-[#f1e7cc] bg-white px-3 py-2 text-sm text-[#182438] focus:ring-2 focus:ring-[#ff595e] outline-none">
                <option value="section" <?php if($sort === 'section'): echo 'selected'; endif; ?>><?php echo e(__('analytics.sort_section')); ?></option>
                <option value="name" <?php if($sort === 'name'): echo 'selected'; endif; ?>><?php echo e(__('analytics.sort_name')); ?></option>
                <option value="performance" <?php if($sort === 'performance'): echo 'selected'; endif; ?>><?php echo e(__('analytics.sort_performance')); ?></option>
            </select>
        </form>
    </div>

    <?php if($scope === 'all' && count($legend)): ?>
        <div class="flex flex-wrap items-center gap-x-5 gap-y-2 mt-4 pt-4 border-t border-[#f1e7cc]">
            <span class="text-xs font-semibold uppercase tracking-wide text-[#8b949d]"><?php echo e(__('analytics.sections_label')); ?></span>
            <?php $__currentLoopData = $legend; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <span class="inline-flex items-center gap-2 text-sm text-[#5f6873]">
                    <span class="w-2.5 h-2.5 rounded-full" style="background-color: <?php echo e($item['color']); ?>"></span>
                    <span class="text-[#182438]"><?php echo e($item['label']); ?></span>
                    <span class="text-[#8b949d]"><?php echo e($item['total']); ?></span>
                </span>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>
</div>
<?php /**PATH C:\laragon\www\kidlink-laravel\resources\views/components/analytics-scope-bar.blade.php ENDPATH**/ ?>