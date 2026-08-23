<aside class="fixed left-0 top-0 h-full w-64 bg-[#182438] text-white shadow-xl z-30 hidden md:block">
    <div class="p-6 border-b border-white/10">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center">
                <span class="text-[#ff8555] font-bold text-lg">K</span>
            </div>
            <div>
                <h2 class="font-bold text-lg">Kid<span class="text-[#ff8555]">Link</span></h2>
                <p class="text-xs text-white/60">Admin Portal</p>
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
                <p class="text-xs text-white/60">Super Admin</p>
            </div>
        </div>
    </div>
    <nav class="p-4 space-y-2">
        <a href="<?php echo e(route('admin.users.index')); ?>" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-white/10 transition <?php echo e(request()->routeIs('admin.users.*') ? 'bg-[#ff8555]' : ''); ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
            Manage Users
        </a>
    </nav>
</aside><?php /**PATH C:\laragon\www\kidlink-laravel\resources\views/components/admin_sidebar.blade.php ENDPATH**/ ?>