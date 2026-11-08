@props(['compact' => false])

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl shadow-sm text-center '.($compact ? 'p-5' : 'p-10')]) }}>
    <div class="w-14 h-14 mx-auto rounded-2xl bg-[#fff0e6] text-[#ff8555] flex items-center justify-center mb-4">
        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
    </div>
    <h3 class="text-lg font-bold text-[#182438]">{{ __('analytics.chooser_prompt_title') }}</h3>
    <p class="text-sm text-[#5f6873] mt-1 max-w-md mx-auto">{{ $slot->isEmpty() ? __('analytics.chooser_prompt_text') : $slot }}</p>
    <button type="button" @click="$dispatch('open-student-chooser')"
            class="mt-5 inline-flex items-center gap-2 px-6 py-2.5 rounded-lg bg-[#ff8555] text-white font-semibold hover:bg-[#f47548] transition">
        {{ __('analytics.chooser_button') }}
    </button>
</div>
