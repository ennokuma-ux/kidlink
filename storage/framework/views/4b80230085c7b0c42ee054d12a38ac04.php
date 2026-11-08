<?php $__env->startSection('title', 'Account Requests'); ?>
<?php $__env->startSection('page-title', 'Account Requests'); ?>

<?php $__env->startSection('content'); ?>
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
            <h2 class="text-xl font-bold text-[#182438]">Account Requests</h2>
            <p class="text-sm text-[#5f6873] mt-1">Review account requests submitted by teachers. Accounts are not created until you approve them.</p>
        </div>
        <div class="px-4 py-2 rounded-lg bg-amber-50 text-amber-800 font-semibold text-sm"><?php echo e($pendingCount); ?> pending approval<?php echo e($pendingCount === 1 ? '' : 's'); ?></div>
    </div>
</div>

<?php if(session('success')): ?><div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800"><?php echo e(session('success')); ?></div><?php endif; ?>
<?php if(session('error')): ?><div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"><?php echo e(session('error')); ?></div><?php endif; ?>

<div class="flex flex-wrap items-center gap-1 bg-white rounded-xl shadow-sm p-1.5 mb-4 w-fit max-w-full">
    <?php $__currentLoopData = ['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'all' => 'All']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a href="<?php echo e(route('admin.account-requests.index', ['status' => $key])); ?>"
           class="px-4 py-2 rounded-lg text-sm font-semibold transition <?php echo e($filter === $key ? 'bg-[#ff8555] text-white' : 'text-[#5f6873] hover:bg-[#f5f3ee]'); ?>">
            <?php echo e($label); ?> <span class="ml-1 text-xs <?php echo e($filter === $key ? 'text-white/80' : 'text-[#8b949d]'); ?>"><?php echo e($counts[$key]); ?></span>
        </a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-[#faf8f3]"><tr>
                <th class="px-5 py-4 text-left font-semibold text-[#182438]">Requested Account</th>
                <th class="px-5 py-4 text-left font-semibold text-[#182438]">Email / Contact</th>
                <th class="px-5 py-4 text-left font-semibold text-[#182438]">Account Type</th>
                <th class="px-5 py-4 text-left font-semibold text-[#182438]">Requested By</th>
                <th class="px-5 py-4 text-left font-semibold text-[#182438]">Submitted</th>
                <th class="px-5 py-4 text-center font-semibold text-[#182438]">Status</th>
                <th class="px-5 py-4 text-center font-semibold text-[#182438]">Action</th>
            </tr></thead>
            <tbody class="divide-y divide-[#ebe8e1]">
            <?php $__empty_1 = true; $__currentLoopData = $requests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $accountRequest): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="hover:bg-[#faf8f3]">
                    <td class="px-5 py-4"><div class="font-semibold text-[#182438]"><?php echo e($accountRequest->first_name); ?> <?php echo e($accountRequest->last_name); ?></div><?php if($accountRequest->student): ?><div class="text-xs text-[#5f6873] mt-1">Student: <?php echo e($accountRequest->student->name); ?></div><?php endif; ?></td>
                    <td class="px-5 py-4"><div><?php echo e($accountRequest->email); ?></div><div class="text-xs text-[#5f6873] mt-1"><?php echo e($accountRequest->contact ?: 'No contact provided'); ?></div></td>
                    <td class="px-5 py-4"><span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo e($accountRequest->role === 'parent' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700'); ?>"><?php echo e($accountRequest->role === 'student' ? 'Child' : 'Parent'); ?></span></td>
                    <td class="px-5 py-4"><?php echo e($accountRequest->requester?->full_name ?? 'Deleted user'); ?></td>
                    <td class="px-5 py-4 whitespace-nowrap"><?php echo e($accountRequest->created_at?->format('M d, Y g:i A')); ?></td>
                    <td class="px-5 py-4 text-center"><span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo e($accountRequest->status === 'approved' ? 'bg-green-100 text-green-700' : ($accountRequest->status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700')); ?>"><?php echo e(ucfirst($accountRequest->status)); ?></span><?php if($accountRequest->review_notes): ?><p class="text-xs text-[#5f6873] mt-2 max-w-40"><?php echo e($accountRequest->review_notes); ?></p><?php endif; ?></td>
                    <td class="px-5 py-4">
                        <?php if($accountRequest->status === 'pending'): ?>
                        <div class="flex items-center justify-center gap-2">
                            <form method="POST" action="<?php echo e(route('admin.account-requests.approve', $accountRequest)); ?>" class="approve-request-form" data-name="<?php echo e($accountRequest->first_name); ?> <?php echo e($accountRequest->last_name); ?>"><?php echo csrf_field(); ?><button class="px-3 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white font-medium" type="submit">Approve</button></form>
                            <form method="POST" action="<?php echo e(route('admin.account-requests.reject', ['accountRequest' => $accountRequest, 'status' => $filter])); ?>" class="reject-request-form" data-name="<?php echo e($accountRequest->first_name); ?> <?php echo e($accountRequest->last_name); ?>"><?php echo csrf_field(); ?><input type="hidden" name="review_notes" value=""><button class="px-3 py-2 rounded-lg bg-red-100 hover:bg-red-200 text-red-700 font-medium" type="submit">Reject</button></form>
                        </div>
                        <?php else: ?>
                        <div class="text-center text-xs text-[#5f6873]"><?php echo e($accountRequest->reviewer?->full_name ?? 'Reviewed'); ?><br><?php echo e($accountRequest->reviewed_at?->format('M d, Y')); ?></div>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php if($accountRequest->role === 'student'): ?>
                <tr class="bg-[#fcfbf8]">
                    <td colspan="7" class="px-5 py-4">
                        <details>
                            <summary class="cursor-pointer font-semibold text-[#258cc5]">View submitted child profile details</summary>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 mt-4 text-sm text-[#5f6873]">
                                <div><span class="font-semibold text-[#182438]">Sex:</span> <?php echo e($accountRequest->sex ?: 'Not provided'); ?></div>
                                <div><span class="font-semibold text-[#182438]">Date of birth:</span> <?php echo e($accountRequest->date_of_birth ?: 'Not provided'); ?></div>
                                <div><span class="font-semibold text-[#182438]">Class:</span> <?php echo e($accountRequest->class_name ?: 'Not provided'); ?></div>
                                <div><span class="font-semibold text-[#182438]">Address:</span> <?php echo e($accountRequest->address ?: 'Not provided'); ?></div>
                                <div><span class="font-semibold text-[#182438]">Dominant hand:</span> <?php echo e($accountRequest->dominant_hand ?: 'Not yet established'); ?></div>
                                <div><span class="font-semibold text-[#182438]">Siblings:</span> <?php echo e($accountRequest->siblings_count ?? 0); ?></div>
                                <div><span class="font-semibold text-[#182438]">Father:</span> <?php echo e($accountRequest->father_name ?: 'Not provided'); ?><?php echo e($accountRequest->father_contact ? ' · '.$accountRequest->father_contact : ''); ?></div>
                                <div><span class="font-semibold text-[#182438]">Mother:</span> <?php echo e($accountRequest->mother_name ?: 'Not provided'); ?><?php echo e($accountRequest->mother_contact ? ' · '.$accountRequest->mother_contact : ''); ?></div>
                            </div>
                        </details>
                    </td>
                </tr>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="7" class="px-6 py-14 text-center text-[#8b949d]"><?php echo e($filter === 'pending' ? 'Nothing is waiting for approval.' : 'No account requests here yet.'); ?></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.querySelectorAll('.approve-request-form').forEach(form => form.addEventListener('submit', event => {
    event.preventDefault();
    if (typeof Swal === 'undefined') {
        if (confirm('Approve this account request and create the account?')) form.submit();
        return;
    }
    Swal.fire({title:'Approve account request?',text:`Create an account for ${form.dataset.name}?`,icon:'question',showCancelButton:true,confirmButtonText:'Approve and create',cancelButtonText:'Cancel',confirmButtonColor:'#16a34a',reverseButtons:true}).then(result => { if(result.isConfirmed) form.submit(); });
}));
document.querySelectorAll('.reject-request-form').forEach(form => form.addEventListener('submit', event => {
    event.preventDefault();
    const notes = form.querySelector('input[name="review_notes"]');
    if (typeof Swal === 'undefined') {
        if (confirm('Reject this account request?')) { notes.value = prompt('Reason (optional)') || ''; form.submit(); }
        return;
    }
    Swal.fire({title:'Reject account request?',text:`The account for ${form.dataset.name} will not be created.`,icon:'warning',input:'textarea',inputPlaceholder:'Reason for the teacher (optional)',inputAttributes:{maxlength:1000},showCancelButton:true,confirmButtonText:'Reject request',cancelButtonText:'Keep pending',confirmButtonColor:'#dc2626',reverseButtons:true}).then(result => { if(result.isConfirmed) { notes.value = result.value || ''; form.submit(); } });
}));
<?php if(session('generated_credentials')): ?>
const credentials = <?php echo json_encode(session('generated_credentials'), 15, 512) ?>;
Swal.fire({title:'Account created',icon:'success',html:'<div class="text-left"><p id="credentialName" class="mb-2 font-semibold"></p><p class="mb-2"><b>Email:</b> <span id="credentialEmail"></span></p><p><b>Temporary password:</b> <code id="credentialPassword"></code></p><p class="text-xs mt-3">Copy these credentials now and share them securely. This temporary password will not be shown again.</p></div>',didOpen:()=>{document.getElementById('credentialName').textContent=credentials.name;document.getElementById('credentialEmail').textContent=credentials.email;document.getElementById('credentialPassword').textContent=credentials.password;},confirmButtonText:'Done'});
<?php endif; ?>
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kidlink-laravel\resources\views/admin/account-requests.blade.php ENDPATH**/ ?>