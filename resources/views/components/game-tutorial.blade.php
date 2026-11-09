@props(['game'])

@php
    $content = trans('tutorial.games.'.$game);
    $ui = trans('tutorial.ui');
    $valid = is_array($content) && is_array($ui);
@endphp

@if($valid)
<div
    x-data="gameTutorial(@js($game), @js($content['practice']))"
    x-init="init()"
    @keydown.escape.window="close()"
>
    {{-- Always-available entry point, styled like a stage chip --}}
    <button type="button" @click="show()"
            class="fixed top-16 sm:top-20 right-3 sm:right-4 z-[60] inline-flex items-center gap-2 rounded-full bg-white border-4 border-[#ffd9a0] px-3 py-2 sm:px-5 sm:py-3 text-sm sm:text-base font-bold text-[#182438] shadow-lg hover:scale-105 transition"
            style="font-family: 'Fredoka One', 'Inter', sans-serif;">
        <span class="text-xl">🎓</span> {{ $ui['button'] }}
    </button>

    <div x-show="open" x-cloak style="display:none;" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 p-4"
         role="dialog" aria-modal="true" aria-label="{{ $ui['title'] }}">
        <div class="relative w-full max-w-lg rounded-3xl bg-white p-5 sm:p-8 text-center max-h-[92vh] overflow-y-auto shadow-2xl border-4 border-[#ffd9a0]"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">

            <button type="button" @click="close()" class="absolute top-3 right-4 text-3xl text-[#6b7280] hover:text-[#182438]" aria-label="{{ $ui['close'] }}">&times;</button>

            <div class="flex justify-center -mt-2 mb-2">
                <x-mascot state="happy" :size="120" />
            </div>

            <p class="text-xs font-semibold uppercase tracking-wide text-[#6b7280] mb-1"
               x-text="@js($ui['step_of']).replace(':current', step + 1).replace(':total', 3)"></p>
            <h2 class="text-2xl font-bold text-[#182438] mb-4" style="font-family: 'Fredoka One', 'Inter', sans-serif;">{{ $content['name'] }}</h2>

            {{-- Step 1: the goal --}}
            <div x-show="step === 0">
                <p class="text-lg text-[#5f6873] leading-relaxed">{{ $content['goal'] }}</p>
            </div>

            {{-- Step 2: unscored practice --}}
            <div x-show="step === 1">
                <p class="text-sm text-[#6b7280] mb-2">{{ $ui['try_it'] }}</p>
                <p class="text-xl font-bold text-[#182438] mb-4">{{ $content['practice']['question'] }}</p>
                <div class="flex justify-center gap-3">
                    <template x-for="(option, index) in practice.options" :key="index">
                        <button type="button" @click="choose(index)" :disabled="solved"
                                class="min-w-[72px] h-[72px] px-3 rounded-2xl border-4 text-3xl font-bold transition"
                                :class="{
                                    'border-[#8ac926] bg-[#f3fae4] scale-110': solved && index === practice.answer,
                                    'border-[#ff595e] bg-[#fff1f1] animate-pulse': wrongIndex === index,
                                    'border-[#f1e7cc] bg-white hover:border-[#ff8555] hover:scale-105': !(solved && index === practice.answer) && wrongIndex !== index
                                }" x-text="option"></button>
                    </template>
                </div>
                <p class="mt-4 h-6 font-semibold" :class="solved ? 'text-[#8ac926]' : 'text-[#ff595e]'"
                   x-text="solved ? @js($ui['correct']) : (wrongIndex !== null ? @js($ui['wrong']) : '')"></p>
            </div>

            {{-- Step 3: ready --}}
            <div x-show="step === 2">
                <p class="text-xl font-bold text-[#182438] mb-2">{{ $ui['ready_title'] }}</p>
                <p class="text-[#5f6873] mb-3">{{ $ui['ready_text'] }}</p>
                <p class="text-sm text-[#5f6873] bg-[#fff0e6] rounded-xl px-4 py-3">💡 {{ $content['tip'] }}</p>
            </div>

            <div class="mt-6 flex items-center justify-between gap-3">
                <button type="button" @click="back()" x-show="step > 0" class="px-5 py-3 rounded-xl border border-[#f1e7cc] text-[#5f6873] font-semibold hover:bg-[#fffbf0]">{{ $ui['back'] }}</button>
                <span x-show="step === 0" class="w-1"></span>
                <button type="button" @click="next()" :disabled="step === 1 && !solved"
                        class="ml-auto px-8 py-3 rounded-xl bg-[#ff8555] text-white font-bold text-lg hover:bg-[#f47548] transition disabled:opacity-40 disabled:cursor-not-allowed"
                        x-text="step === 2 ? @js($ui['start']) : @js($ui['next'])"></button>
            </div>
            <button type="button" @click="close()" x-show="step < 2" class="mt-3 text-sm text-[#6b7280] hover:text-[#182438]">{{ $ui['skip'] }}</button>
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
@endif
