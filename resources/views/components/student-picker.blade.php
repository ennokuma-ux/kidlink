@props(['students' => [], 'selected' => null])

@php
    $options = collect($students)->map(fn ($student) => [
        'id' => (string) $student->id,
        'name' => $student->name,
        'section' => $student->class_name ? \App\Models\Student::sectionLabel($student->class_name) : '',
    ])->values();
@endphp

<div {{ $attributes->merge(['class' => 'flex-1']) }} x-data="studentPicker(@js($options), {{ $selected ? $selected->id : 'null' }}, {{ json_encode(__('analytics.all_students'), JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP) }})">
    <label for="analyticsStudentTrigger" class="block text-xs font-semibold uppercase tracking-wide text-[#5f6873] mb-1">{{ __('analytics.student') }}</label>
    <div class="relative" @click.outside="open = false">
        <button type="button" id="analyticsStudentTrigger" @click="open = !open" :aria-expanded="open.toString()" aria-haspopup="listbox"
                class="w-full flex items-center justify-between gap-2 rounded-lg border border-[#f1e7cc] bg-white px-3 py-2 text-sm text-[#182438] text-left focus:ring-2 focus:ring-[#ff8555] outline-none">
            <span class="truncate" x-text="selectedLabel"></span>
            <svg class="w-4 h-4 text-[#8b949d] flex-shrink-0 transition" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </button>

        <div x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95 -translate-y-1" x-transition:enter-end="opacity-100 scale-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" role="listbox"
             class="absolute left-0 right-0 mt-1 z-50 bg-white rounded-xl shadow-xl border border-[#f1e7cc] overflow-hidden" style="display: none;">
            <div class="p-2 border-b border-[#f0ece4]">
                <div class="relative">
                    <svg class="w-4 h-4 text-[#8b949d] absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" x-model="search" x-ref="searchInput"
                           placeholder="{{ __('analytics.search_students') }}"
                           class="w-full pl-9 pr-3 py-2 text-sm border border-[#f1e7cc] rounded-lg focus:outline-none focus:border-[#ff8555] focus:ring-1 focus:ring-[#ff8555]"
                           @keydown.escape="open = false"
                           @keydown.enter.prevent="pickFirst()"
                           x-init="$watch('open', open => { if (open) $nextTick(() => $refs.searchInput.focus()) })">
                </div>
            </div>
            <div class="max-h-64 overflow-y-auto py-1">
                <button type="button" role="option" @click="pick('')"
                        class="w-full text-left px-4 py-2.5 text-sm text-[#182438] hover:bg-gray-50 transition" :class="selectedId === '' ? 'bg-gray-50' : ''">
                    {{ __('analytics.all_students') }}
                </button>
                <template x-for="student in filtered" :key="student.id">
                    <button type="button" role="option" @click="pick(student.id)"
                            class="w-full text-left px-4 py-2.5 text-sm hover:bg-gray-50 transition" :class="selectedId === student.id ? 'bg-gray-50' : ''">
                        <span class="font-semibold text-[#182438]" x-text="student.name"></span>
                        <span class="text-xs text-[#8b949d]" x-show="student.section" x-text="' — ' + student.section"></span>
                    </button>
                </template>
                <p x-show="filtered.length === 0" class="px-4 py-3 text-sm text-[#8b949d] text-center">{{ __('analytics.no_matching_students') }}</p>
            </div>
        </div>
    </div>
    <input type="hidden" name="student" :value="selectedId">
</div>

<script>
if (typeof window.studentPicker !== 'function') {
window.studentPicker = function studentPicker(options, selectedId, allLabel) {
    return {
        open: false,
        search: '',
        options: options,
        selectedId: selectedId === null ? '' : String(selectedId),
        get filtered() {
            if (!this.search.trim()) return this.options;
            const q = this.search.toLowerCase();
            return this.options.filter(s => s.name.toLowerCase().includes(q) || s.section.toLowerCase().includes(q));
        },
        get selectedLabel() {
            const found = this.options.find(s => s.id === this.selectedId);
            return found ? found.name + (found.section ? ' — ' + found.section : '') : allLabel;
        },
        pick(id) {
            this.selectedId = id;
            this.open = false;
            this.$nextTick(() => this.$el.closest('form').requestSubmit());
        },
        pickFirst() {
            const first = this.filtered[0];
            if (first) this.pick(first.id);
        }
    };
};
}
</script>