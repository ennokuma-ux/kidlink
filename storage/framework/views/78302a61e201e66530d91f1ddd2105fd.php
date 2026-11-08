<aside class="fixed left-0 top-0 h-full w-64 bg-[#182438] text-white shadow-xl z-40 overflow-y-auto transition-transform duration-200 -translate-x-full md:translate-x-0" :class="{ '!translate-x-0': navOpen }">
    <div class="p-6 border-b border-white/10 relative">
        <button type="button" @click="navOpen = false" class="md:hidden absolute top-3 right-3 w-9 h-9 rounded-lg text-white/70 hover:bg-white/10 text-2xl leading-none" aria-label="Close menu">&times;</button>
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center">
                <span class="text-[#ff8555] font-bold text-lg">K</span>
            </div>
            <div>
                <h2 class="font-bold text-lg">Kid<span class="text-[#ff8555]">Link</span></h2>
                <p class="text-xs text-white/60"><?php echo e(__('sidebar.admin_portal')); ?></p>
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
                <p class="text-xs text-white/60"><?php echo e(__('sidebar.super_admin')); ?></p>
            </div>
        </div>
    </div>
    <nav class="p-4 space-y-2">
        <a href="<?php echo e(route('admin.analytics.index')); ?>" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-white/10 transition <?php echo e(request()->routeIs('admin.analytics.*') ? 'bg-[#ff8555]' : ''); ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            <?php echo e(__('nav.system_analytics')); ?>

        </a>
        <a href="<?php echo e(route('admin.users.index')); ?>" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-white/10 transition <?php echo e(request()->routeIs('admin.users.*') ? 'bg-[#ff8555]' : ''); ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            <?php echo e(__('nav.manage_users')); ?>

        </a>
        <?php
            try { $pendingAccountRequests = \App\Models\AccountRequest::where('status', 'pending')->count(); } catch (\Throwable $e) { $pendingAccountRequests = 0; }
        ?>
        <a href="<?php echo e(route('admin.account-requests.index')); ?>" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-white/10 transition <?php echo e(request()->routeIs('admin.account-requests.*') ? 'bg-[#ff8555]' : ''); ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="flex-1"><?php echo e(__('nav.account_requests')); ?></span>
            <?php if($pendingAccountRequests > 0): ?>
                <span class="min-w-[1.5rem] text-center px-1.5 py-0.5 rounded-full bg-[#f05d56] text-white text-xs font-bold"><?php echo e($pendingAccountRequests); ?></span>
            <?php endif; ?>
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
</aside><?php /**PATH C:\laragon\www\kidlink-laravel\resources\views/components/admin_sidebar.blade.php ENDPATH**/ ?>