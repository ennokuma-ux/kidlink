<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'mascot' => null,
    'state' => 'happy',
    'size' => 120,
    'buddy' => false,
    'speech' => null,
    'float' => false,
    'side' => false,
    'bounce' => false,
    'circle' => false,
]));

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

foreach (array_filter(([
    'mascot' => null,
    'state' => 'happy',
    'size' => 120,
    'buddy' => false,
    'speech' => null,
    'float' => false,
    'side' => false,
    'bounce' => false,
    'circle' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $mascot = $mascot ?: \App\Support\Mascot::forCurrentStudent();
    if (! \App\Support\Mascot::exists($mascot)) {
        $mascot = \App\Support\Mascot::KIDO;
    }

    $expression = \App\Support\Mascot::expression($mascot, $state);
    $src = asset(\App\Support\Mascot::expressions($mascot)[$expression]);

    $buddyStates = $buddy
        ? collect(\App\Support\Mascot::buddyStates($mascot))->map(fn ($path) => asset($path))->all()
        : [];

    $buddySpeech = $buddy
        ? [
            'start' => __('mascot.buddy_start'),
            'correct' => __('mascot.buddy_correct'),
            'wrong' => __('mascot.buddy_wrong'),
            'next' => __('mascot.buddy_next'),
            'hint' => __('gamekit.buddy_hint'),
        ]
        : [];

    $classes = trim(implode(' ', array_filter([
        'kidlink-mascot',
        'kidlink-mascot--'.($mascot === \App\Support\Mascot::LINKA ? 'linka' : 'kido'),
        $float ? 'kidlink-mascot--float' : '',
        ($float || $circle) ? 'kidlink-mascot--circle' : '',
        $side ? 'kidlink-mascot--side' : '',
        $bounce ? 'kidlink-mascot--bounce' : '',
        $buddy ? 'kidlink-mascot--buddy' : '',
    ])));
?>

<div
    <?php echo e($attributes->merge(['class' => $classes, 'style' => '--mascot-size: '.((int) $size).'px'])); ?>

    <?php if($buddy): ?>
        data-mascot-buddy
        data-mascot-state="<?php echo e($expression); ?>"
        data-expressions="<?php echo e(json_encode($buddyStates)); ?>"
        data-speech="<?php echo e(json_encode($buddySpeech)); ?>"
    <?php endif; ?>
>
    <div class="kidlink-mascot__stage">
        <img
            src="<?php echo e($src); ?>"
            alt="<?php echo e(__('mascot.alt_'.$mascot)); ?>"
            width="<?php echo e($size); ?>"
            height="<?php echo e($size); ?>"
            class="kidlink-mascot__img"
            loading="lazy"
            decoding="async"
        >
        <?php if($speech || $buddy): ?>
            <div
                class="kidlink-mascot__speech"
                <?php if($buddy): ?> data-mascot-speech <?php endif; ?>
            ><?php echo e($speech ?? __('mascot.buddy_start')); ?></div>
        <?php endif; ?>
    </div>
</div>
<?php /**PATH C:\laragon\www\kidlink-laravel\resources\views/components/mascot.blade.php ENDPATH**/ ?>