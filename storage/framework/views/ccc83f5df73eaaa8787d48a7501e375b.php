<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([]));

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

foreach (array_filter(([]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>


<form method="POST" action="<?php echo e(route('language.switch')); ?>" class="inline shrink-0">
    <?php echo csrf_field(); ?>
    <div class="inline-flex items-center gap-0.5 rounded-lg border border-[#f1e7cc] bg-[#fffbf0] p-0.5"
         role="group" aria-label="<?php echo e(__('common.language')); ?>">
        <svg class="w-4 h-4 ml-1.5 text-[#8b949d]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
        </svg>
        <?php $__currentLoopData = config('app.supported_locales'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php ($active = app()->getLocale() === $code); ?>
            <button type="submit"
                    name="locale"
                    value="<?php echo e($code); ?>"
                    title="<?php echo e($label); ?>"
                    aria-label="<?php echo e($label); ?>"
                    aria-current="<?php echo e($active ? 'true' : 'false'); ?>"
                    class="px-2.5 py-1.5 text-xs font-semibold rounded-md transition <?php echo e($active
                        ? 'bg-[#182438] text-white shadow-sm'
                        : 'text-[#5f6873] hover:bg-white hover:text-[#182438]'); ?>">
                <?php echo e(strtoupper($code)); ?>

            </button>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</form>
<?php /**PATH C:\laragon\www\kidlink-laravel\resources\views/components/lang_switcher.blade.php ENDPATH**/ ?>