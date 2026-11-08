<aside class="fixed left-0 top-0 h-full w-64 bg-[#182438] text-white shadow-xl z-40 overflow-y-auto transition-transform duration-200 -translate-x-full md:translate-x-0" :class="{ '!translate-x-0': navOpen }">
    <div class="p-6 border-b border-white/10 relative">
        <button type="button" @click="navOpen = false" class="md:hidden absolute top-3 right-3 w-9 h-9 rounded-lg text-white/70 hover:bg-white/10 text-2xl leading-none" aria-label="Close menu">&times;</button>
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center">
                <span class="text-[#ff8555] font-bold text-lg">K</span>
            </div>
            <div>
                <h2 class="font-bold text-lg">Kid<span class="text-[#ff8555]">Link</span></h2>
                <p class="text-xs text-white/60"><?php echo e(__('sidebar.teacher_portal')); ?></p>
            </div>
        </div>
    </div>
    <div class="p-4 border-b border-white/10">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-[#ff8555] rounded-full flex items-center justify-center text-white font-bold text-lg">
                <?php echo e(strtoupper(substr(auth()->user()->first_name, 0, 1))); ?><?php echo e(strtoupper(substr(auth()->user()->last_name, 0, 1))); ?>

            </div>
            <div>
                <p class="font-medium text-sm"><?php echo e(auth()->user()->full_name); ?></p>
                <p class="text-xs text-white/60"><?php echo e(__('sidebar.teacher')); ?></p>
            </div>
        </div>
    </div>
    <nav class="p-4 space-y-2">
        <a href="<?php echo e(route('teacher.dashboard')); ?>" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-white/10 transition <?php echo e(request()->routeIs('teacher.dashboard') ? 'bg-[#ff8555]' : ''); ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            <?php echo e(__('nav.dashboard')); ?>

        </a>
        <a href="<?php echo e(route('teacher.accounts.index')); ?>" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-white/10 transition <?php echo e(request()->routeIs('teacher.accounts.*') ? 'bg-[#ff8555]' : ''); ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            <?php echo e(__('nav.accounts')); ?>

        </a>
        <a href="<?php echo e(route('teacher.attendance.index')); ?>" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-white/10 transition <?php echo e(request()->routeIs('teacher.attendance.*') ? 'bg-[#ff8555]' : ''); ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            <?php echo e(__('nav.attendance')); ?>

        </a>
        <a href="<?php echo e(route('teacher.eccd.index')); ?>" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-white/10 transition <?php echo e(request()->routeIs('teacher.eccd.*') ? 'bg-[#ff8555]' : ''); ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <?php echo e(__('nav.eccd')); ?>

        </a>
        <a href="<?php echo e(route('teacher.game-assignments.index')); ?>" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-white/10 transition <?php echo e(request()->routeIs('teacher.game-assignments.*') ? 'bg-[#ff8555]' : ''); ?>">
            <img src="<?php echo e(asset('assets/icons/game-controller.svg')); ?>" alt="" class="w-5 h-5 brightness-0 invert">
            <?php echo e(__('Game Assignments')); ?>

        </a>
        <a href="<?php echo e(route('teacher.activities.index')); ?>" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-white/10 transition <?php echo e(request()->routeIs('teacher.activities.*') ? 'bg-[#ff8555]' : ''); ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <?php echo e(__('nav.activities')); ?>

        </a>
        <a href="<?php echo e(route('teacher.settings.index')); ?>" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-white/10 transition <?php echo e(request()->routeIs('teacher.settings.*') ? 'bg-[#ff8555]' : ''); ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <?php echo e(__('nav.settings')); ?>

        </a>
        <a href="<?php echo e(route('announcements.index')); ?>" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-white/10 transition <?php echo e(request()->routeIs('announcements.*') ? 'bg-[#ff8555]' : ''); ?>">
            <i class="ico ico-megaphone w-5 h-5" aria-hidden="true"></i>
            <span class="flex-1"><?php echo e(__('nav.announcements')); ?></span>
            <?php if (isset($component)) { $__componentOriginalac881d43daa35e07172aab3e71a1168b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalac881d43daa35e07172aab3e71a1168b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.announcement-badge','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('announcement-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalac881d43daa35e07172aab3e71a1168b)): ?>
<?php $attributes = $__attributesOriginalac881d43daa35e07172aab3e71a1168b; ?>
<?php unset($__attributesOriginalac881d43daa35e07172aab3e71a1168b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalac881d43daa35e07172aab3e71a1168b)): ?>
<?php $component = $__componentOriginalac881d43daa35e07172aab3e71a1168b; ?>
<?php unset($__componentOriginalac881d43daa35e07172aab3e71a1168b); ?>
<?php endif; ?>
        </a>
    </nav>
</aside><?php /**PATH C:\laragon\www\kidlink-laravel\resources\views/components/teacher_sidebar.blade.php ENDPATH**/ ?>