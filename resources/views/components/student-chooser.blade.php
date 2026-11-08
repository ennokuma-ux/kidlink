@props(['students', 'selected' => null, 'action', 'param' => 'student', 'extra' => [], 'variant' => 'button'])

@php
    $list = collect($students)->map(fn ($student) => [
        'id' => $student->id,
        'name' => $student->name,
        'section' => $student->class_name ? \App\Models\Student::sectionLabel($student->class_name) : '',
        'url' => $action.'?'.http_build_query(array_merge($extra, [$param => $student->id])),
    ])->values();
    $selectedId = $selected ? $selected->id : null;
@endphp

<div x-data="{ open: false, search: '', students: @js($list), selectedId: @js($selectedId),
        get filtered() { const q = this.search.trim().toLowerCase(); return q ? this.students.filter(s => (s.name + ' ' + s.section).toLowerCase().includes(q)) : this.students; },
        show() { this.search = ''; this.open = true; this.$nextTick(() => this.$refs.q && this.$refs.q.focus()); },
        pickFirst() { if (this.filtered.length) window.location.href = this.filtered[0].url; } }"
     @open-student-chooser.window="show()" @keydown.escape.window="open = false">

    @if($variant === 'button')
        <button type="button" @click="show()"
                class="inline-flex items-center gap-2 rounded-lg border border-[#f1e7cc] bg-white px-4 py-2.5 text-sm font-semibold text-[#182438] hover:border-[#ff8555] hover:bg-[#fff6f0] transition">
            <svg class="w-4 h-4 text-[#ff8555]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4M16 17H4m0 0l4-4m-4 4l4 4"/></svg>
            <span>{{ $selected ? __('analytics.chooser_switch') : __('analytics.chooser_button') }}</span>
            @if($selected)<span class="max-w-[10rem] truncate font-normal text-[#5f6873]">&middot; {{ $selected->name }}</span>@endif
        </button>
    @endif

    <div x-show="open" x-cloak style="display:none;" class="fixed inset-0 z-[90] flex items-start sm:items-center justify-center p-4 bg-black/50 overflow-y-auto"
         role="dialog" aria-modal="true" aria-label="{{ __('analytics.chooser_title') }}" @click.self="open = false">
        <div class="w-full max-w-lg bg-white rounded-2xl shadow-2xl p-5 sm:p-6 mt-8 sm:mt-0"
             x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-[#182438]">{{ __('analytics.chooser_title') }}</h3>
                <button type="button" @click="open = false" class="w-9 h-9 rounded-lg text-2xl leading-none text-[#8b949d] hover:bg-[#fbf0d6]" aria-label="{{ __('analytics.chooser_close') }}">&times;</button>
            </div>

            @if($list->isEmpty())
                <p class="text-sm text-[#8b949d] text-center py-6">{{ __('analytics.chooser_empty') }}</p>
            @else
                <div class="relative mb-3">
                    <svg class="w-4 h-4 text-[#8b949d] absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="search" x-ref="q" x-model="search" @keydown.enter.prevent="pickFirst()" autocomplete="off"
                           placeholder="{{ __('analytics.chooser_search') }}"
                           class="w-full rounded-lg border border-[#f1e7cc] pl-9 pr-3 py-2.5 text-sm focus:ring-2 focus:ring-[#ff8555] outline-none">
                </div>
                <div class="max-h-[55vh] overflow-y-auto space-y-1.5">
                    <template x-for="student in filtered" :key="student.id">
                        <a :href="student.url" class="flex items-center justify-between gap-3 rounded-xl border px-4 py-3 transition"
                           :class="student.id === selectedId ? 'border-[#ff8555] bg-[#fff6f0]' : 'border-[#f1e7cc] hover:border-[#ff8555] hover:bg-[#fff6f0]'">
                            <span class="font-medium text-[#182438] truncate" x-text="student.name"></span>
                            <span class="flex items-center gap-2 shrink-0">
                                <span class="text-xs text-[#8b949d]" x-text="student.section"></span>
                                <span x-show="student.id === selectedId" class="text-[10px] font-semibold uppercase text-[#ff8555]">{{ __('analytics.chooser_current') }}</span>
                            </span>
                        </a>
                    </template>
                    <p x-show="filtered.length === 0" class="text-sm text-[#8b949d] text-center py-4">{{ __('analytics.chooser_none') }}</p>
                </div>
            @endif
        </div>
    </div>
</div>
