<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['route', 'students', 'sections' => []]));

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

foreach (array_filter((['route', 'students', 'sections' => []]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $studentList = collect($students)->map(fn ($student) => [
        'id' => $student->id,
        'name' => $student->name,
        'section' => $student->class_name ? \App\Models\Student::sectionLabel($student->class_name) : '',
        'url' => route($route, ['student' => $student->id]),
    ])->values();
?>

<div class="max-w-5xl mx-auto" x-data="{ pane: null, search: '', students: <?php echo \Illuminate\Support\Js::from($studentList)->toHtml() ?>,
        get filtered() { const q = this.search.trim().toLowerCase(); return q ? this.students.filter(s => (s.name + ' ' + s.section).toLowerCase().includes(q)) : this.students; } }">
    <div class="text-center mb-8">
        <h2 class="text-2xl font-bold text-[#182438]"><?php echo e(__('analytics.scope_title')); ?></h2>
        <p class="text-[#5f6873] mt-1"><?php echo e(__('analytics.scope_subtitle')); ?></p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6">
        
        <a href="<?php echo e(route($route, ['scope' => 'all'])); ?>"
           class="group bg-white rounded-2xl shadow-sm border-2 border-transparent hover:border-[#ff8555] hover:shadow-md transition p-6 text-left">
            <div class="w-12 h-12 rounded-xl bg-[#fff0e6] text-[#ff8555] flex items-center justify-center mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <h3 class="text-lg font-bold text-[#182438]"><?php echo e(__('analytics.scope_all_title')); ?></h3>
            <p class="text-sm text-[#5f6873] mt-1"><?php echo e(__('analytics.scope_all_text')); ?></p>
            <p class="text-xs font-semibold text-[#ff8555] mt-4"><?php echo e(__('analytics.scope_students_count', ['count' => $studentList->count()])); ?> &rarr;</p>
        </a>

        
        <button type="button" @click="pane = pane === 'section' ? null : 'section'"
                class="bg-white rounded-2xl shadow-sm border-2 hover:border-[#258cc5] hover:shadow-md transition p-6 text-left"
                :class="pane === 'section' ? 'border-[#258cc5]' : 'border-transparent'" :aria-expanded="(pane === 'section').toString()">
            <div class="w-12 h-12 rounded-xl bg-[#e1f0f8] text-[#258cc5] flex items-center justify-center mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            </div>
            <h3 class="text-lg font-bold text-[#182438]"><?php echo e(__('analytics.scope_section_title')); ?></h3>
            <p class="text-sm text-[#5f6873] mt-1"><?php echo e(__('analytics.scope_section_text')); ?></p>
            <p class="text-xs font-semibold text-[#258cc5] mt-4"><span x-text="pane === 'section' ? '&uarr;' : '&darr;'"></span> <?php echo e(count($sections)); ?></p>
        </button>

        
        <button type="button" @click="pane = pane === 'individual' ? null : 'individual'"
                class="bg-white rounded-2xl shadow-sm border-2 hover:border-[#68b82f] hover:shadow-md transition p-6 text-left"
                :class="pane === 'individual' ? 'border-[#68b82f]' : 'border-transparent'" :aria-expanded="(pane === 'individual').toString()">
            <div class="w-12 h-12 rounded-xl bg-[#e2f4e8] text-[#68b82f] flex items-center justify-center mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <h3 class="text-lg font-bold text-[#182438]"><?php echo e(__('analytics.scope_individual_title')); ?></h3>
            <p class="text-sm text-[#5f6873] mt-1"><?php echo e(__('analytics.scope_individual_text')); ?></p>
            <p class="text-xs font-semibold text-[#68b82f] mt-4"><span x-text="pane === 'individual' ? '&uarr;' : '&darr;'"></span> <?php echo e($studentList->count()); ?></p>
        </button>
    </div>

    
    <div x-show="pane === 'section'" x-cloak style="display:none;" x-transition.opacity class="mt-6 bg-white rounded-2xl shadow-sm p-5">
        <?php $__empty_1 = true; $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php $count = collect($students)->where('class_name', $name)->count(); ?>
            <?php if($loop->first): ?><div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3"><?php endif; ?>
                <a href="<?php echo e(route($route, ['section' => $name])); ?>"
                   class="flex items-center gap-3 rounded-xl border border-[#ebe8e1] px-4 py-3 hover:border-[#258cc5] hover:bg-[#f2f8fc] transition">
                    <span class="w-3 h-3 rounded-full shrink-0" style="background-color: <?php echo e(\App\Models\Student::colorForSection($name)); ?>"></span>
                    <span class="font-semibold text-[#182438] truncate"><?php echo e(\App\Models\Student::sectionLabel($name)); ?></span>
                    <span class="ml-auto text-sm text-[#8b949d]"><?php echo e($count); ?></span>
                </a>
            <?php if($loop->last): ?></div><?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="text-sm text-[#8b949d] text-center py-2"><?php echo e(__('analytics.scope_no_sections')); ?></p>
        <?php endif; ?>
    </div>

    
    <div x-show="pane === 'individual'" x-cloak style="display:none;" x-transition.opacity class="mt-6 bg-white rounded-2xl shadow-sm p-5">
        <?php if($studentList->isEmpty()): ?>
            <p class="text-sm text-[#8b949d] text-center py-2"><?php echo e(__('analytics.scope_no_students')); ?></p>
        <?php else: ?>
            <input type="text" x-model="search" placeholder="<?php echo e(__('analytics.scope_search')); ?>"
                   class="w-full mb-3 rounded-lg border border-[#ebe8e1] px-3 py-2 text-sm focus:ring-2 focus:ring-[#68b82f] outline-none">
            <div class="max-h-80 overflow-y-auto grid grid-cols-1 sm:grid-cols-2 gap-2">
                <template x-for="student in filtered" :key="student.id">
                    <a :href="student.url" class="flex items-center justify-between gap-3 rounded-xl border border-[#ebe8e1] px-4 py-3 hover:border-[#68b82f] hover:bg-[#f3faee] transition">
                        <span class="font-medium text-[#182438] truncate" x-text="student.name"></span>
                        <span class="text-xs text-[#8b949d] shrink-0" x-text="student.section"></span>
                    </a>
                </template>
            </div>
            <p x-show="filtered.length === 0" class="text-sm text-[#8b949d] text-center py-3"><?php echo e(__('analytics.scope_no_match')); ?></p>
        <?php endif; ?>
    </div>
</div>
<?php /**PATH C:\laragon\www\kidlink-laravel\resources\views/components/analytics-scope-select.blade.php ENDPATH**/ ?>