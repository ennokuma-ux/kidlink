
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#ff595e]">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[#5f6873] text-sm"><?php echo e(__('dashboard.total_students')); ?></p>
                <p class="text-3xl font-bold text-[#182438]"><?php echo e($totalStudents); ?></p>
            </div>
            <div class="w-12 h-12 bg-[#ffe9ea] rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-[#ff595e]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#8ac926]">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[#5f6873] text-sm"><?php echo e(__('dashboard.present_today')); ?></p>
                <p class="text-3xl font-bold text-[#182438]"><?php echo e($presentToday); ?></p>
            </div>
            <div class="w-12 h-12 bg-[#ecf7d6] rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-[#8ac926]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#ff595e]">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[#5f6873] text-sm"><?php echo e(__('dashboard.absent_today')); ?></p>
                <p class="text-3xl font-bold text-[#182438]"><?php echo e($absentToday); ?></p>
            </div>
            <div class="w-12 h-12 bg-[#fde8ef] rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-[#ff595e]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#1982c4]">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[#5f6873] text-sm"><?php echo e(__('dashboard.activities_today')); ?></p>
                <p class="text-3xl font-bold text-[#182438]"><?php echo e($activitiesToday); ?></p>
            </div>
            <div class="w-12 h-12 bg-[#e1f0f8] rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-[#1982c4]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\laragon\www\kidlink-laravel\resources\views/teacher/partials/headline-cards.blade.php ENDPATH**/ ?>