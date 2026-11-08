<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['dot' => false]));

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

foreach (array_filter((['dot' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<?php
    $unseenAnnouncements = \App\Models\Announcement::unseenCountFor(auth()->user());
?>
<?php if($unseenAnnouncements > 0): ?>
    <?php if($dot): ?>
        <span class="absolute -top-1 -right-1 w-3.5 h-3.5 rounded-full bg-[#ff595e] ring-2 ring-white" role="status" aria-label="New announcements"></span>
    <?php else: ?>
        <span class="min-w-[1.5rem] text-center px-1.5 py-0.5 rounded-full bg-[#ff595e] text-white text-xs font-bold" role="status"
              title="New announcements" aria-label="<?php echo e($unseenAnnouncements); ?> new announcements"><?php echo e($unseenAnnouncements > 99 ? '99+' : $unseenAnnouncements); ?></span>
    <?php endif; ?>
<?php endif; ?>
<?php /**PATH C:\laragon\www\kidlink-laravel\resources\views/components/announcement-badge.blade.php ENDPATH**/ ?>