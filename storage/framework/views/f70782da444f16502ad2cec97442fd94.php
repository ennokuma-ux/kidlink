<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'interventions' => [],
    'heading' => null,
]));

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

foreach (array_filter(([
    'interventions' => [],
    'heading' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>



<div class="rounded-xl border-2 mb-8
    <?php echo e($interventions === [] ? 'bg-white border-[#b8e0c2]' : 'bg-[#fff5f7] border-[#ff595e]'); ?>">
    <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-4 border-b
        <?php echo e($interventions === [] ? 'border-[#f1e7cc]' : 'border-[#f5c6cf]'); ?>">
        <div class="flex items-center gap-3">
            <?php if($interventions === []): ?>
                <span class="w-9 h-9 rounded-full bg-[#ecf7d6] text-[#8ac926] flex items-center justify-center text-lg font-bold" aria-hidden="true">&check;</span>
            <?php else: ?>
                <span class="w-9 h-9 rounded-full bg-[#ff595e] text-white flex items-center justify-center font-bold" aria-hidden="true">!</span>
            <?php endif; ?>
            <div>
                <h3 class="text-lg font-semibold text-[#182438]"><?php echo e($heading ?: __('intervention.heading')); ?></h3>
                <p class="text-xs text-[#8b949d]">
                    <?php echo e(__('intervention.subtitle', ['days' => \App\Services\AnalyticsService::ATTENDANCE_WINDOW_DAYS])); ?>

                    
                </p>
            </div>
        </div>
        <?php if($interventions !== []): ?>
            <span class="px-3 py-1.5 rounded-full bg-[#ff595e] text-white text-xs font-bold uppercase tracking-wide">
                <?php echo e(trans_choice('intervention.count', count($interventions), ['count' => count($interventions)])); ?>

                
            </span>
        <?php endif; ?>
    </div>

    <?php if($interventions === []): ?>
        <div class="px-6 py-8 text-center">
            <p class="text-[#182438] font-semibold"><?php echo e(__('intervention.nothing_missing')); ?></p>
            <p class="text-sm text-[#5f6873] mt-1">
                <?php echo e(__('intervention.all_clear')); ?>

            </p>
        </div>
    <?php else: ?>
        <div class="divide-y divide-[#f1e7cc]">
            <?php $__currentLoopData = $interventions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $flag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="px-6 py-4">
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                        <div class="flex items-center gap-2">
                            <h4 class="font-semibold text-[#182438]"><?php echo e($flag['name']); ?></h4>
                            <span class="inline-flex items-center gap-1.5 text-xs text-[#5f6873]">
                                <span class="w-2 h-2 rounded-full"
                                      style="background-color: <?php echo e(\App\Models\Student::colorForSection($flag['class_name'])); ?>"></span>
                                <?php echo e(\App\Models\Student::sectionLabel($flag['class_name'])); ?>

                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-bold uppercase tracking-wide px-2 py-0.5 rounded-full
                                <?php echo e($flag['severity'] === 'high' ? 'bg-[#ff595e] text-white' : ($flag['severity'] === 'medium' ? 'bg-[#ffca3a] text-[#182438]' : 'bg-[#8ac926] text-[#182438]')); ?>">
                                <?php echo e(__('intervention.severity_' . $flag['severity'])); ?>

                            </span>
                            <span class="text-[10px] font-bold uppercase tracking-wide px-2 py-0.5 rounded-full bg-[#ff595e] text-white">
                                <?php echo e(__('intervention.required')); ?>

                            </span>
                        </div>
                    </div>

                    <ul class="space-y-2 mt-2">
                        <?php $__currentLoopData = $flag['reasons']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reason): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="flex flex-col sm:flex-row sm:items-start gap-1 sm:gap-3">
                                <span class="shrink-0 text-[10px] font-bold uppercase tracking-wide px-2 py-0.5 rounded-full mt-0.5
                                    <?php echo e($reason['type'] === 'Attendance' ? 'bg-[#fde8ef] text-[#ff595e]' : ($reason['type'] === 'Baseline' ? 'bg-[#fff6e5] text-[#a16207]' : 'bg-[#eef3f9] text-[#1982c4]')); ?>">
                                    <?php echo e(__('intervention.type_' . strtolower($reason['type']))); ?>

                                </span>
                                <span class="text-sm text-[#5f6873]">
                                    <span class="font-medium text-[#182438]"><?php echo e($reason['label']); ?>.</span>
                                    <?php echo e($reason['message']); ?>

                                    <span class="block text-xs text-[#8b949d] mt-0.5"><?php echo e(__('intervention.do')); ?> <?php echo e($reason['action']); ?></span>
                                </span>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>
</div><?php /**PATH C:\laragon\www\kidlink-laravel\resources\views/components/intervention-panel.blade.php ENDPATH**/ ?>