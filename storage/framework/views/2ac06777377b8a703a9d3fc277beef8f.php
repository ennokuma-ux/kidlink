<?php $__env->startSection('title', __('Archived announcements')); ?>
<?php $__env->startSection('page-title', __('Archived announcements')); ?>

<?php $__env->startSection('content'); ?>
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-[#182438]"><?php echo e(__('Archived announcements')); ?></h2>
            <p class="text-sm text-[#5f6873] mt-1"><?php echo e(__('Announcements taken off the board. Only teachers and admins can see this list.')); ?></p>
        </div>
        <a href="<?php echo e(route('announcements.index')); ?>" class="inline-flex items-center gap-2 border border-[#ebe8e1] text-[#5f6873] hover:bg-[#f3f6fb] px-4 py-2.5 rounded-lg transition font-medium self-start md:self-auto">
            &larr; <?php echo e(__('Back to announcements')); ?>

        </a>
    </div>
</div>

<div class="space-y-4">
    <?php $__empty_1 = true; $__currentLoopData = $announcements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $announcement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php
            $authorName = $announcement->author?->full_name ?? __('announcements.removed_account');
            $archiverName = $announcement->archiver?->full_name ?? __('announcements.removed_account');
            $commentCount = $announcement->comments->count();
        ?>
        <article id="announcement-<?php echo e($announcement->id); ?>" x-data="{ open: false }" class="bg-white rounded-xl shadow-sm overflow-hidden">
            <button type="button" @click="open = !open" :aria-expanded="open.toString()"
                    class="w-full flex items-center gap-3 sm:gap-4 p-4 text-left hover:bg-[#f3f6fb] transition">
                <span class="w-10 h-10 bg-[#8b949d] rounded-full flex items-center justify-center text-white font-bold text-sm shrink-0">
                    <?php echo e(strtoupper(mb_substr($authorName, 0, 1))); ?>

                </span>
                <span class="flex-1 min-w-0">
                    <span class="flex items-center gap-2 min-w-0">
                        <span class="font-semibold text-[#182438] truncate"><?php echo e($announcement->title); ?></span>
                        <span class="shrink-0 rounded-full bg-[#eef2f8] px-2 py-0.5 text-[11px] font-semibold text-[#5f6873]"><?php echo e(__('Archived')); ?></span>
                        <?php if($commentCount): ?>
                            <span class="shrink-0 rounded-full bg-[#eef2f8] px-2 py-0.5 text-[11px] font-semibold text-[#5f6873]"><?php echo e($commentCount); ?> <?php echo e(__('announcements.comments')); ?></span>
                        <?php endif; ?>
                    </span>
                    <span class="block text-xs text-[#8b949d] mt-0.5 truncate">
                        <?php echo e($authorName); ?> &middot; <?php echo e(__('posted')); ?> <?php echo e($announcement->created_at?->format('M j, Y')); ?>

                        &middot; <?php echo e(__('archived')); ?> <?php echo e($announcement->archived_at?->format('M j, Y')); ?> <?php echo e(__('by')); ?> <?php echo e($archiverName); ?>

                    </span>
                </span>
                <svg class="w-5 h-5 shrink-0 text-[#8b949d] transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>

            <div x-show="open" x-cloak style="display:none;" class="px-5 pb-5 pt-4 border-t border-[#ebe8e1]">
                <p class="text-sm text-[#182438] whitespace-pre-line leading-relaxed"><?php echo e($announcement->body); ?></p>

                <?php if($commentCount): ?>
                    <div class="mt-5 pt-4 border-t border-dashed border-[#ebe8e1] space-y-3">
                        <h4 class="text-sm font-semibold text-[#182438]"><?php echo e(__('announcements.comments')); ?> <span class="text-[#8b949d] font-normal">(<?php echo e($commentCount); ?>)</span></h4>
                        <?php $__currentLoopData = $announcement->comments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php $commentAuthor = $comment->author?->full_name ?? __('announcements.removed_account'); ?>
                            <div class="flex items-start gap-3">
                                <span class="w-8 h-8 rounded-full bg-[#258cc5] text-white text-xs font-bold flex items-center justify-center shrink-0"><?php echo e(strtoupper(mb_substr($commentAuthor, 0, 1))); ?></span>
                                <div class="flex-1 min-w-0 rounded-xl bg-[#eef2f8] px-4 py-2.5">
                                    <p class="text-xs text-[#8b949d]"><span class="font-semibold text-[#182438]"><?php echo e($commentAuthor); ?></span> &middot; <?php echo e($comment->created_at?->diffForHumans()); ?></p>
                                    <p class="text-sm text-[#182438] mt-1 whitespace-pre-line break-words"><?php echo e($comment->body); ?></p>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>
            </div>
        </article>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="bg-white rounded-xl shadow-sm p-12 text-center">
            <p class="text-[#5f6873]"><?php echo e(__('No archived announcements.')); ?></p>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.announcements', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kidlink-laravel\resources\views/announcements/archived.blade.php ENDPATH**/ ?>