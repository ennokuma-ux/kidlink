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
                class="inline-flex items-center gap-2 rounded-lg border border-[#f1e7cc] bg-white px-4 py-2.5 text-sm font-semibold text-[#182438] hover:border-[#1982c4] hover:bg-[#fff8e7] transition">
            <svg class="w-4 h-4 text-[#1982c4]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4M16 17H4m0 0l4-4m-4 4l4 4"/></svg>
            <span>{{ $selected ? __('analytics.chooser_switch') : __('analytics.chooser_button') }}</span>
            @if($selected)<span class="max-w-[10rem] truncate font-normal text-[#5f6873]">&middot; {{ $selected->name }}</span>@endif
        </button>
    @endif

    <div x-show="open" x-cloak style="display:none;" class="fixed inset-0 z-[90] flex items-start sm:items-center justify-center p-4 bg-black/50 overflow-y-auto"
         role="dialog" aria-modal="true" aria-label="{{ __('analytics.chooser_title') }}" @click.self="open = false">
        <div class="w-full max-w-xl bg-white rounded-3xl shadow-2xl p-5 sm:p-6 mt-8 sm:mt-0 border border-slate-100"
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
                           class="w-full rounded-lg border border-[#f1e7cc] pl-9 pr-3 py-2.5 text-sm focus:ring-2 focus:ring-[#1982c4] outline-none">
                </div>
                <div class="max-h-[58vh] overflow-y-auto space-y-2 pr-1">
                    <template x-for="student in filtered" :key="student.id">
                        <a :href="student.url" class="kidlink-student-option group flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3.5 transition duration-150 hover:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-300"
                           :class="student.id === selectedId ? 'border-slate-500 is-current' : ''">
                            <span class="kidlink-avatar grid h-10 w-10 shrink-0 place-items-center rounded-full bg-gray-100 text-sm font-bold text-gray-700" x-text="student.name.trim().charAt(0).toUpperCase()"></span>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate font-semibold text-slate-800" x-text="student.name"></span>
                                <span class="mt-0.5 block truncate text-xs text-slate-500" x-text="student.section || 'Student'"></span>
                            </span>
                            <span x-show="student.id === selectedId" class="kidlink-chip shrink-0 rounded-full bg-gray-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-gray-600">{{ __('analytics.chooser_current') }}</span>
                            <svg x-show="student.id !== selectedId" class="h-4 w-4 shrink-0 text-slate-300 transition group-hover:translate-x-0.5 group-hover:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 18 6-6-6-6"/></svg>
                        </a>
                    </template>
                    <p x-show="filtered.length === 0" class="text-sm text-[#8b949d] text-center py-4">{{ __('analytics.chooser_none') }}</p>
                </div>
            @endif
        </div>
    </div>
</div>
