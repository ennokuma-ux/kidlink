

<?php $__env->startSection('title', __('teacher.settings_title')); ?>
<?php $__env->startSection('page-title', __('teacher.settings_title')); ?>

<?php $__env->startSection('content'); ?>
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-6"><?php echo e(__('teacher.profile_information')); ?></h3>
        <p class="text-sm text-[#5f6873] mb-4">Your name and email can only be changed by an administrator. You can update your contact number below.</p>
        <form method="POST" action="<?php echo e(route('teacher.settings.profile')); ?>" class="space-y-4">
            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2"><?php echo e(__('teacher.first_name')); ?></label>
                <input type="text" value="<?php echo e($user->first_name); ?>" readonly
                    class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg bg-[#faf8f3] text-[#5f6873] cursor-not-allowed outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2"><?php echo e(__('teacher.last_name')); ?></label>
                <input type="text" value="<?php echo e($user->last_name); ?>" readonly
                    class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg bg-[#faf8f3] text-[#5f6873] cursor-not-allowed outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2"><?php echo e(__('teacher.email')); ?></label>
                <input type="email" value="<?php echo e($user->email); ?>" readonly
                    class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg bg-[#faf8f3] text-[#5f6873] cursor-not-allowed outline-none">
            </div>
            <div>
                <label for="contact" class="block text-sm font-medium text-[#182438] mb-2"><?php echo e(__('teacher.contact')); ?></label>
                <input id="contact" type="text" name="contact" value="<?php echo e(old('contact', $user->contact)); ?>" maxlength="255" autocomplete="tel"
                    class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none <?php $__errorArgs = ['contact'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-[#f05d56] <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                <?php $__errorArgs = ['contact'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="mt-1 text-sm text-[#f05d56]"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <button type="submit" class="bg-[#ff8555] text-white px-6 py-2.5 rounded-lg hover:bg-[#f47548] transition font-medium">Save contact</button>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-6"><?php echo e(__('teacher.change_password')); ?></h3>
        <form method="POST" action="<?php echo e(route('teacher.settings.password')); ?>" class="space-y-4">
            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2"><?php echo e(__('teacher.current_password')); ?></label>
                <input type="password" name="current_password" required
                    class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2"><?php echo e(__('teacher.new_password')); ?></label>
                <input type="password" name="password" required minlength="8"
                    class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2"><?php echo e(__('teacher.confirm_password')); ?></label>
                <input type="password" name="password_confirmation" required minlength="8"
                    class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
            </div>
            <button type="submit" class="bg-[#ff8555] text-white px-6 py-2.5 rounded-lg hover:bg-[#f47548] transition font-medium"><?php echo e(__('teacher.update_password')); ?></button>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.teacher', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kidlink-laravel\resources\views/teacher/settings.blade.php ENDPATH**/ ?>