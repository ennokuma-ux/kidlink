<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['students', 'selected' => null, 'action', 'param' => 'student', 'extra' => [], 'variant' => 'button']));

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

foreach (array_filter((['students', 'selected' => null, 'action', 'param' => 'student', 'extra' => [], 'variant' => 'button']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $list = collect($students)->map(fn ($student) => [
        'id' => $student->id,
        'name' => $student->name,
        'section' => $student->class_name ? \App\Models\Student::sectionLabel($student->class_name) : '',
        'url' => $action.'?'.http_build_query(array_merge($extra, [$param => $student->id])),
    ])->values();
    $selectedId = $selected ? $selected->id : null;
?>

<div x-data="{ open: false, search: '', students: <?php echo \Illuminate\Support\Js::from($list)->toHtml() ?>, selectedId: <?php echo \Illuminate\Support\Js::from($selectedId)->toHtml() ?>,
        get filtered() { const q = this.search.trim().toLowerCase(); return q ? this.students.filter(s => (s.name + ' ' + s.section).toLowerCase().includes(q)) : this.students; },
        show() { this.search = ''; this.open = true; this.$nextTick(() => this.$refs.q && this.$refs.q.focus()); },
        pickFirst() { if (this.filtered.length) window.location.href = this.filtered[0].url; } }"
     @open-student-chooser.window="show()" @keydown.escape.window="open = false">

    <?php if($variant === 'button'): ?>
        <button type="button" @click="show()"
                class="inline-flex items-center gap-2 rounded-lg border border-[#ebe8e1] bg-white px-4 py-2.5 text-sm font-semibold text-[#182438] hover:border-[#ff8555] hover:bg-[#fff6f0] transition">
            <svg class="w-4 h-4 text-[#ff8555]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4M16 17H4m0 0l4-4m-4 4l4 4"/></svg>
            <span><?php echo e($selected ? __('analytics.chooser_switch') : __('analytics.chooser_button')); ?></span>
            <?php if($selected): ?><span class="max-w-[10rem] truncate font-normal text-[#5f6873]">&middot; <?php echo e($selected->name); ?></span><?php endif; ?>
        </button>
    <?php endif; ?>

    <div x-show="open" x-cloak style="display:none;" class="fixed inset-0 z-[90] flex items-start sm:items-center justify-center p-4 bg-black/50 overflow-y-auto"
         role="dialog" aria-modal="true" aria-label="<?php echo e(__('analytics.chooser_title')); ?>" @click.self="open = false">
        <div class="w-full max-w-lg bg-white rounded-2xl shadow-2xl p-5 sm:p-6 mt-8 sm:mt-0"
             x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-[#182438]"><?php echo e(__('analytics.chooser_title')); ?></h3>
                <button type="button" @click="open = false" class="w-9 h-9 rounded-lg text-2xl leading-none text-[#8b949d] hover:bg-[#eef2f8]" aria-label="<?php echo e(__('analytics.chooser_close')); ?>">&times;</button>
            </div>

            <?php if($list->isEmpty()): ?>
                <p class="text-sm text-[#8b949d] text-center py-6"><?php echo e(__('analytics.chooser_empty')); ?></p>
            <?php else: ?>
                <div class="relative mb-3">
                    <svg class="w-4 h-4 text-[#8b949d] absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="search" x-ref="q" x-model="search" @keydown.enter.prevent="pickFirst()" autocomplete="off"
                           placeholder="<?php echo e(__('analytics.chooser_search')); ?>"
                           class="w-full rounded-lg border border-[#ebe8e1] pl-9 pr-3 py-2.5 text-sm focus:ring-2 focus:ring-[#ff8555] outline-none">
                </div>
                <div class="max-h-[55vh] overflow-y-auto space-y-1.5">
                    <template x-for="student in filtered" :key="student.id">
                        <a :href="student.url" class="flex items-center justify-between gap-3 rounded-xl border px-4 py-3 transition"
                           :class="student.id === selectedId ? 'border-[#ff8555] bg-[#fff6f0]' : 'border-[#ebe8e1] hover:border-[#ff8555] hover:bg-[#fff6f0]'">
                            <span class="font-medium text-[#182438] truncate" x-text="student.name"></span>
                            <span class="flex items-center gap-2 shrink-0">
                                <span class="text-xs text-[#8b949d]" x-text="student.section"></span>
                                <span x-show="student.id === selectedId" class="text-[10px] font-semibold uppercase text-[#ff8555]"><?php echo e(__('analytics.chooser_current')); ?></span>
                            </span>
                        </a>
                    </template>
                    <p x-show="filtered.length === 0" class="text-sm text-[#8b949d] text-center py-4"><?php echo e(__('analytics.chooser_none')); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php /**PATH C:\laragon\www\kidlink-laravel\resources\views/components/student-chooser.blade.php ENDPATH**/ ?>