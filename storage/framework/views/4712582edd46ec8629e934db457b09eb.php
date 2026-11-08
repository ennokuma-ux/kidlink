<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['value', 'suffix' => '%']));

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

foreach (array_filter((['value', 'suffix' => '%']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php if(is_null($value)): ?>
    <span class="text-[#8b949d]" title="<?php echo e(__('ui.no_data')); ?>">&mdash;</span>
<?php else: ?>
    <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
        'font-bold',
        'text-[#ff595e]' => $value < 50,
        'text-[#ffca3a]' => $value >= 50 && $value < 75,
        'text-[#8ac926]' => $value >= 75,
    ]); ?>"><?php echo e($value); ?><?php echo e($suffix); ?></span>
<?php endif; ?>
<?php /**PATH C:\laragon\www\kidlink-laravel\resources\views/components/score-cell.blade.php ENDPATH**/ ?>