<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title' => '',
    'tag' => null,
    'heading' => null,
    'homeLabel' => null,
    'homeClass' => 'btn-primary',
    'homeUrl' => null,
    'actions' => null,
    'footnote' => null,
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
    'title' => '',
    'tag' => null,
    'heading' => null,
    'homeLabel' => null,
    'homeClass' => 'btn-primary',
    'homeUrl' => null,
    'actions' => null,
    'footnote' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $homeLabel = $homeLabel ?: __('errors.back_home');
    $homeUrl = $homeUrl ?: url('/');
?>

<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($title); ?></title>
    <style>
        :root { color-scheme: light dark; }
        body {
            margin: 0;
            padding: 2.5rem 1.5rem;
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            line-height: 1.6;
            background: #f4f5f7;
            color: #1f2430;
        }
        .card {
            max-width: 40rem;
            margin: 0 auto;
            background: #fff;
            border: 1px solid #e2e5ea;
            border-radius: 12px;
            padding: 2rem;
        }
        .tag {
            display: inline-block;
            font-size: .75rem;
            font-weight: 600;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: #8a4b00;
            background: #fff4e0;
            border: 1px solid #ffd9a0;
            border-radius: 999px;
            padding: .2rem .6rem;
            margin-bottom: .9rem;
        }
        h1 { margin: 0 0 .5rem; font-size: 1.4rem; }
        h2 { font-size: .95rem; margin: 1.6rem 0 .5rem; }
        p { margin: 0 0 1rem; }
        code, pre { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size: .85rem; }
        code { background: #f0f1f4; border-radius: 4px; padding: .1rem .3rem; }
        pre {
            background: #1f2430;
            color: #e8eaed;
            border-radius: 8px;
            padding: .8rem 1rem;
            overflow-x: auto;
        }
        ol { margin: 0; padding-left: 1.2rem; }
        li { margin-bottom: .6rem; }
        .actions { display: flex; flex-wrap: wrap; gap: .6rem; margin: 1.4rem 0 .5rem; }
        .btn {
            display: inline-block;
            font: inherit;
            font-weight: 600;
            text-decoration: none;
            border-radius: 8px;
            padding: .65rem 1.1rem;
            cursor: pointer;
        }
        .btn-primary { background: #ff8555; color: #fff; border: 1px solid #ff8555; }
        .btn-primary:hover { background: #f47548; border-color: #f47548; }
        .btn-quiet { background: #fff; color: #1f2430; border: 1px solid #d5d8de; }
        .btn-quiet:hover { background: #f4f5f7; }
        .muted { color: #6b7280; font-size: .85rem; }
    </style>
</head>
<body>
<div class="card">
    <?php if($tag): ?><span class="tag"><?php echo e($tag); ?></span><?php endif; ?>
    <?php if($heading): ?><h1><?php echo e($heading); ?></h1><?php endif; ?>

    <div style="margin: 0 0 1rem;"><?php if (isset($component)) { $__componentOriginal1ad13fbd679f096a735ed64294b0a7b9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1ad13fbd679f096a735ed64294b0a7b9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.mascot','data' => ['mascot' => 'kido','state' => 'question','size' => 96]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mascot'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['mascot' => 'kido','state' => 'question','size' => 96]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1ad13fbd679f096a735ed64294b0a7b9)): ?>
<?php $attributes = $__attributesOriginal1ad13fbd679f096a735ed64294b0a7b9; ?>
<?php unset($__attributesOriginal1ad13fbd679f096a735ed64294b0a7b9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1ad13fbd679f096a735ed64294b0a7b9)): ?>
<?php $component = $__componentOriginal1ad13fbd679f096a735ed64294b0a7b9; ?>
<?php unset($__componentOriginal1ad13fbd679f096a735ed64294b0a7b9); ?>
<?php endif; ?></div>

    <?php echo e($slot); ?>


    <div class="actions">
        <?php echo e($actions); ?>

        <a class="btn <?php echo e($homeClass); ?>" href="<?php echo e($homeUrl); ?>"><?php echo e($homeLabel); ?></a>
    </div>

    <?php if($footnote): ?><p class="muted"><?php echo e($footnote); ?></p><?php endif; ?>
</div>
</body>
</html><?php /**PATH C:\laragon\www\kidlink-laravel\resources\views/components/error-layout.blade.php ENDPATH**/ ?>