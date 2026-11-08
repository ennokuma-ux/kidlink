<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['game']));

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

foreach (array_filter((['game']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $content = trans('tutorial.games.'.$game);
    $ui = trans('tutorial.ui');
    $valid = is_array($content) && is_array($ui);
?>

<?php if($valid): ?>
<div
    x-data="gameTutorial(<?php echo \Illuminate\Support\Js::from($game)->toHtml() ?>, <?php echo \Illuminate\Support\Js::from($content['practice'])->toHtml() ?>)"
    x-init="init()"
    @keydown.escape.window="close()"
>
    
    <button type="button" @click="show()"
            class="fixed top-16 sm:top-20 right-3 sm:right-4 z-[60] inline-flex items-center gap-2 rounded-full bg-white border-4 border-[#ffd9a0] px-3 py-2 sm:px-5 sm:py-3 text-sm sm:text-base font-bold text-[#182438] shadow-lg hover:scale-105 transition"
            style="font-family: 'Fredoka One', 'Inter', sans-serif;">
        <span class="text-xl">🎓</span> <?php echo e($ui['button']); ?>

    </button>

    <div x-show="open" x-cloak style="display:none;" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 p-4"
         role="dialog" aria-modal="true" aria-label="<?php echo e($ui['title']); ?>">
        <div class="relative w-full max-w-lg rounded-3xl bg-white p-5 sm:p-8 text-center max-h-[92vh] overflow-y-auto shadow-2xl border-4 border-[#ffd9a0]"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">

            <button type="button" @click="close()" class="absolute top-3 right-4 text-3xl text-[#8b949d] hover:text-[#182438]" aria-label="<?php echo e($ui['close']); ?>">&times;</button>

            <div class="flex justify-center -mt-2 mb-2">
                <?php if (isset($component)) { $__componentOriginal1ad13fbd679f096a735ed64294b0a7b9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1ad13fbd679f096a735ed64294b0a7b9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.mascot','data' => ['state' => 'happy','size' => 120]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mascot'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['state' => 'happy','size' => 120]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1ad13fbd679f096a735ed64294b0a7b9)): ?>
<?php $attributes = $__attributesOriginal1ad13fbd679f096a735ed64294b0a7b9; ?>
<?php unset($__attributesOriginal1ad13fbd679f096a735ed64294b0a7b9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1ad13fbd679f096a735ed64294b0a7b9)): ?>
<?php $component = $__componentOriginal1ad13fbd679f096a735ed64294b0a7b9; ?>
<?php unset($__componentOriginal1ad13fbd679f096a735ed64294b0a7b9); ?>
<?php endif; ?>
            </div>

            <p class="text-xs font-semibold uppercase tracking-wide text-[#8b949d] mb-1"
               x-text="<?php echo \Illuminate\Support\Js::from($ui['step_of'])->toHtml() ?>.replace(':current', step + 1).replace(':total', 3)"></p>
            <h2 class="text-2xl font-bold text-[#182438] mb-4" style="font-family: 'Fredoka One', 'Inter', sans-serif;"><?php echo e($content['name']); ?></h2>

            
            <div x-show="step === 0">
                <p class="text-lg text-[#5f6873] leading-relaxed"><?php echo e($content['goal']); ?></p>
            </div>

            
            <div x-show="step === 1">
                <p class="text-sm text-[#8b949d] mb-2"><?php echo e($ui['try_it']); ?></p>
                <p class="text-xl font-bold text-[#182438] mb-4"><?php echo e($content['practice']['question']); ?></p>
                <div class="flex justify-center gap-3">
                    <template x-for="(option, index) in practice.options" :key="index">
                        <button type="button" @click="choose(index)" :disabled="solved"
                                class="min-w-[72px] h-[72px] px-3 rounded-2xl border-4 text-3xl font-bold transition"
                                :class="{
                                    'border-[#68b82f] bg-green-50 scale-110': solved && index === practice.answer,
                                    'border-[#f05d56] bg-red-50 animate-pulse': wrongIndex === index,
                                    'border-[#ebe8e1] bg-white hover:border-[#ff8555] hover:scale-105': !(solved && index === practice.answer) && wrongIndex !== index
                                }" x-text="option"></button>
                    </template>
                </div>
                <p class="mt-4 h-6 font-semibold" :class="solved ? 'text-[#68b82f]' : 'text-[#f05d56]'"
                   x-text="solved ? <?php echo \Illuminate\Support\Js::from($ui['correct'])->toHtml() ?> : (wrongIndex !== null ? <?php echo \Illuminate\Support\Js::from($ui['wrong'])->toHtml() ?> : '')"></p>
            </div>

            
            <div x-show="step === 2">
                <p class="text-xl font-bold text-[#182438] mb-2"><?php echo e($ui['ready_title']); ?></p>
                <p class="text-[#5f6873] mb-3"><?php echo e($ui['ready_text']); ?></p>
                <p class="text-sm text-[#5f6873] bg-[#fff0e6] rounded-xl px-4 py-3">💡 <?php echo e($content['tip']); ?></p>
            </div>

            <div class="mt-6 flex items-center justify-between gap-3">
                <button type="button" @click="back()" x-show="step > 0" class="px-5 py-3 rounded-xl border border-[#ebe8e1] text-[#5f6873] font-semibold hover:bg-[#faf8f3]"><?php echo e($ui['back']); ?></button>
                <span x-show="step === 0" class="w-1"></span>
                <button type="button" @click="next()" :disabled="step === 1 && !solved"
                        class="ml-auto px-8 py-3 rounded-xl bg-[#ff8555] text-white font-bold text-lg hover:bg-[#f47548] transition disabled:opacity-40 disabled:cursor-not-allowed"
                        x-text="step === 2 ? <?php echo \Illuminate\Support\Js::from($ui['start'])->toHtml() ?> : <?php echo \Illuminate\Support\Js::from($ui['next'])->toHtml() ?>"></button>
            </div>
            <button type="button" @click="close()" x-show="step < 2" class="mt-3 text-sm text-[#8b949d] hover:text-[#182438]"><?php echo e($ui['skip']); ?></button>
        </div>
    </div>
</div>

<script>
if (typeof window.gameTutorial !== 'function') {
window.gameTutorial = function gameTutorial(game, practice) {
    const key = 'kidlink_tutorial_seen_' + game;
    return {
        open: false, step: 0, solved: false, wrongIndex: null, practice: practice,
        init() {
            let seen = false;
            try { seen = localStorage.getItem(key) === '1'; } catch (e) {}
            if (!seen) this.show();
        },
        show() { this.step = 0; this.solved = false; this.wrongIndex = null; this.open = true; },
        close() {
            if (!this.open) return;
            this.open = false;
            try { localStorage.setItem(key, '1'); } catch (e) {}
        },
        choose(index) {
            if (this.solved) return;
            if (index === this.practice.answer) { this.solved = true; this.wrongIndex = null; }
            else { this.wrongIndex = index; setTimeout(() => { if (!this.solved) this.wrongIndex = null; }, 700); }
        },
        next() { if (this.step === 2) return this.close(); if (this.step === 1 && !this.solved) return; this.step++; },
        back() { if (this.step > 0) this.step--; },
    };
};
}
</script>
<?php endif; ?>
<?php /**PATH C:\laragon\www\kidlink-laravel\resources\views/components/game-tutorial.blade.php ENDPATH**/ ?>