
<div id="requestsView" class="hidden">
    <div class="flex flex-wrap items-center gap-2 mb-4">
        <span class="px-3 py-1 rounded-full bg-[#fdf3d7] text-[#8a6d10] text-sm font-semibold"><?php echo e(__('teacher.request_count_pending', ['count' => $requestCounts['pending']])); ?></span>
        <span class="px-3 py-1 rounded-full bg-[#ecf7d6] text-[#2f7a1a] text-sm font-semibold"><?php echo e(__('teacher.request_count_approved', ['count' => $requestCounts['approved']])); ?></span>
        <span class="px-3 py-1 rounded-full bg-[#ffe9ea] text-[#c0392b] text-sm font-semibold"><?php echo e(__('teacher.request_count_rejected', ['count' => $requestCounts['rejected']])); ?></span>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <?php if($accountRequests->isEmpty()): ?>
            <div class="p-10 text-center">
                <p class="text-[#182438] font-semibold"><?php echo e(__('teacher.request_none')); ?></p>
                <p class="text-sm text-[#8b949d] mt-1"><?php echo e(__('teacher.request_none_hint')); ?></p>
            </div>
        <?php else: ?>
            <div class="overflow-auto max-h-[32rem]">
                <table class="w-full">
                    <thead class="bg-[#fffbf0] sticky top-0 z-10 shadow-[0_1px_0_#f1e7cc]">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#8b949d]"><?php echo e(__('teacher.request_account_col')); ?></th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#8b949d]"><?php echo e(__('teacher.request_submitted')); ?></th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#8b949d]"><?php echo e(__('teacher.request_status')); ?></th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#8b949d]"><?php echo e(__('teacher.request_review_col')); ?></th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f1e7cc]">
                        <?php $__currentLoopData = $accountRequests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $accountRequest): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $statusClass = match ($accountRequest->status) {
                                    'approved' => 'bg-[#ecf7d6] text-[#2f7a1a]',
                                    'rejected' => 'bg-[#ffe9ea] text-[#c0392b]',
                                    default => 'bg-[#fdf3d7] text-[#8a6d10]',
                                };
                                $resubmit = [
                                    'role' => $accountRequest->role,
                                    'first_name' => $accountRequest->first_name,
                                    'last_name' => $accountRequest->last_name,
                                    'email' => $accountRequest->email,
                                    'contact' => $accountRequest->contact,
                                    'sex' => $accountRequest->sex,
                                    'date_of_birth' => $accountRequest->date_of_birth ? \Illuminate\Support\Carbon::parse($accountRequest->date_of_birth)->toDateString() : null,
                                    'address' => $accountRequest->address,
                                    'class_name' => $accountRequest->class_name,
                                    'academic_year' => $accountRequest->academic_year,
                                    'dominant_hand' => $accountRequest->dominant_hand,
                                    'father_name' => $accountRequest->father_name,
                                    'father_contact' => $accountRequest->father_contact,
                                    'mother_name' => $accountRequest->mother_name,
                                    'mother_contact' => $accountRequest->mother_contact,
                                    'siblings_count' => $accountRequest->siblings_count,
                                    'parent_ids' => $accountRequest->parent_ids ?? [],
                                ];
                            ?>
                            <tr class="hover:bg-[#fffbf0] transition">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-[#182438]"><?php echo e($accountRequest->first_name); ?> <?php echo e($accountRequest->last_name); ?></span>
                                        <span class="px-2 py-0.5 rounded text-[11px] font-semibold <?php echo e($accountRequest->role === 'parent' ? 'bg-[#e1f0f8] text-[#1e73a8]' : 'bg-[#fff0e6] text-[#c5582a]'); ?>">
                                            <?php echo e($accountRequest->role === 'parent' ? __('teacher.request_parent') : __('teacher.request_student')); ?>

                                        </span>
                                    </div>
                                    <?php if($accountRequest->email): ?>
                                        <p class="text-xs text-[#8b949d]"><?php echo e($accountRequest->email); ?></p>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-sm text-[#5f6873] whitespace-nowrap" title="<?php echo e($accountRequest->created_at?->format('M j, Y g:i A')); ?>">
                                    <?php echo e($accountRequest->created_at?->diffForHumans()); ?>

                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold <?php echo e($statusClass); ?>">
                                        <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                        <?php echo e(__('teacher.request_'.$accountRequest->status)); ?>

                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-[#5f6873] min-w-[14rem]">
                                    <?php if($accountRequest->status === 'pending'): ?>
                                        <span class="text-[#8b949d]"><?php echo e(__('teacher.request_awaiting')); ?></span>
                                    <?php else: ?>
                                        <p>
                                            <?php echo e(__('teacher.request_reviewed_on', ['when' => $accountRequest->reviewed_at?->diffForHumans()])); ?>

                                            <?php if($accountRequest->reviewer): ?>
                                                <?php echo e(__('teacher.request_reviewed_by', ['name' => $accountRequest->reviewer->full_name])); ?>

                                            <?php endif; ?>
                                        </p>
                                        <?php if($accountRequest->status === 'rejected' && $accountRequest->review_notes): ?>
                                            <p class="mt-1 text-[#c0392b]"><span class="font-semibold"><?php echo e(__('teacher.request_reason')); ?>:</span> <?php echo e($accountRequest->review_notes); ?></p>
                                        <?php elseif($accountRequest->status === 'approved'): ?>
                                            <p class="mt-1 text-xs text-[#8b949d]"><?php echo e(__('teacher.request_approved_hint')); ?></p>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <?php if($accountRequest->status === 'pending'): ?>
                                        <form method="POST" action="<?php echo e(route('teacher.account-requests.cancel', $accountRequest)); ?>" class="inline"
                                              data-confirm="<?php echo e(__('teacher.request_withdraw_confirm')); ?>" data-confirm-title="<?php echo e(__('teacher.request_withdraw_title')); ?>"
                                              data-confirm-yes="<?php echo e(__('teacher.request_withdraw')); ?>" data-confirm-no="<?php echo e(__('teacher.cancel')); ?>">
                                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="px-3 py-1.5 rounded-lg text-sm font-semibold text-[#ff595e] hover:bg-[#ffe9ea] transition"><?php echo e(__('teacher.request_withdraw')); ?></button>
                                        </form>
                                    <?php elseif($accountRequest->status === 'rejected'): ?>
                                        <button type="button" data-resubmit='<?php echo json_encode($resubmit, 15, 512) ?>'
                                                onclick="window.dispatchEvent(new CustomEvent('open-account-request', { detail: JSON.parse(this.dataset.resubmit) }))"
                                                class="px-3 py-1.5 rounded-lg text-sm font-semibold text-[#1982c4] hover:bg-[#e1f0f8] transition"><?php echo e(__('teacher.request_resubmit')); ?></button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php /**PATH C:\laragon\www\kidlink-laravel\resources\views/teacher/partials/account-requests-list.blade.php ENDPATH**/ ?>