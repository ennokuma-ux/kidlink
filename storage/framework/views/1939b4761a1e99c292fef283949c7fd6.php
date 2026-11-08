<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['topic', 'current']));

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

foreach (array_filter((['topic', 'current']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $games = \App\Games\GameCatalog::gamesForTopic($topic);
    $switcherStudent = \App\Models\Student::where('user_id', auth()->id())->first();
    if ($switcherStudent) {
        $allowedSlugs = \App\Support\GameAssignmentAccess::slugsFor($switcherStudent);
        $games = array_values(array_filter($games, fn ($g) => in_array($g['slug'], $allowedSlugs, true) || $g['slug'] === $current));
    }
?>

<?php if(count($games)): ?>
<div class="mb-5">
    <p class="text-[10px] font-extrabold uppercase tracking-widest text-[#8b949d] mb-2"><?php echo e(__('game.switch_label')); ?></p>
    <div class="flex flex-wrap items-center gap-2">
        <?php $__currentLoopData = $games; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $game): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if($game['slug'] === $current): ?>
                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-sm font-extrabold text-white shadow-sm" style="background: <?php echo e($game['gradient']); ?>;">
                    <?php echo e($game['name']); ?>

                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                </span>
            <?php else: ?>
                <a href="<?php echo e(route('student.games.' . $game['slug'] . '.stage-select')); ?>"
                   class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-sm font-extrabold text-[#5f6873] bg-white border-2 border-[#f1e7cc] hover:bg-[#182438] hover:text-white hover:border-[#182438] transition">
                    <?php echo e($game['name']); ?>

                </a>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>
<?php endif; ?><?php /**PATH C:\laragon\www\kidlink-laravel\resources\views/components/games-switcher.blade.php ENDPATH**/ ?>