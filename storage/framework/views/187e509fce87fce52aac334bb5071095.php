
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['game', 'level' => 1, 'accent' => '#f47548']));

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

foreach (array_filter((['game', 'level' => 1, 'accent' => '#f47548']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $routePrefix = 'student.games.'.$game.'.';

    $config = [
        'game' => $game,
        'level' => (int) $level,
        'accent' => $accent,
        'urls' => [
            'stage' => route($routePrefix.'stage-select'),
            'home' => route('student.home'),
            'next' => route($routePrefix.'play', ['level' => (int) $level + 1]),
            'submit' => route($routePrefix.'submit', ['level' => (int) $level]),
        ],
        'text' => [
            'level_num_complete' => __('game.level_num_complete'),
            'score_line' => __('game.score_line'),
            'mistakes_line' => __('game.mistakes_line'),
            'points_earned' => __('game.points_earned'),
            'back_to_map' => __('game.back_to_map'),
            'home' => __('game.home'),
            'next_level' => __('game.next_level'),
            'buddy_hint' => __('gamekit.buddy_hint'),
            'finish' => trans('gamekit.finish'),
        ],
        'hints' => trans('gamekit.hints.'.$game),
    ];
?>

<script>
    window.KidLinkGameConfig = <?php echo json_encode($config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT, 512) ?>;
</script>
<script src="<?php echo e(asset('js/kidlink-game.js')); ?>"></script>
<?php /**PATH C:\laragon\www\kidlink-laravel\resources\views/components/game-kit.blade.php ENDPATH**/ ?>