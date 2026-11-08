<?php $__env->startSection('title', __('announcements.new_announcement')); ?>
<?php $__env->startSection('page-title', __('announcements.new_announcement_title')); ?>

<?php $__env->startSection('content'); ?>
<div class="bg-white rounded-xl shadow-sm p-6 mb-6 max-w-3xl">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-[#182438]"><?php echo e(__('announcements.new_announcement')); ?></h2>
            <p class="text-sm text-[#5f6873] mt-1"><?php echo e(__('announcements.visible_to_every_teacher_and_parent')); ?></p>
        </div>
        <a href="<?php echo e(route('announcements.index')); ?>" class="border border-[#ebe8e1] hover:bg-[#faf8f3] text-[#5f6873] px-6 py-2.5 rounded-lg transition font-medium self-start md:self-auto">
            <?php echo e(__('announcements.cancel')); ?>

        </a>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm p-6 max-w-3xl">
    <form method="POST" action="<?php echo e(route('announcements.store')); ?>" class="space-y-6"
          x-data="{
              poll: <?php echo \Illuminate\Support\Js::from((bool) old('poll_enabled'))->toHtml() ?>,
              options: <?php echo \Illuminate\Support\Js::from(array_pad(array_values((array) old('poll_options', [])), \App\Models\AnnouncementPoll::MIN_OPTIONS, ''))->toHtml() ?>,
              min: <?php echo e(\App\Models\AnnouncementPoll::MIN_OPTIONS); ?>,
              max: <?php echo e(\App\Models\AnnouncementPoll::MAX_OPTIONS); ?>,
              add() { if (this.options.length < this.max) { this.options.push(''); this.$nextTick(() => { const f = this.$refs.optionList.querySelectorAll('input[type=text]'); f[f.length - 1]?.focus(); }); } },
              remove(i) { if (this.options.length > this.min) this.options.splice(i, 1); }
          }">
        <?php echo csrf_field(); ?>

        <div>
            <label for="title" class="block text-sm font-medium text-[#182438] mb-2"><?php echo e(__('announcements.title')); ?> <span class="text-[#f05d56]">*</span></label>
            <input type="text" name="title" id="title" required maxlength="255"
                value="<?php echo e(old('title')); ?>"
                class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-[#f05d56] <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                placeholder="<?php echo e(__('announcements.short_headline_placeholder')); ?>">
            <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="text-sm text-[#f05d56] mt-1"><?php echo e($message); ?></p>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div>
            <label for="body" class="block text-sm font-medium text-[#182438] mb-2"><?php echo e(__('announcements.announcement')); ?> <span class="text-[#f05d56]">*</span></label>
            <textarea name="body" id="body" rows="8" required maxlength="5000"
                class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none resize-none <?php $__errorArgs = ['body'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-[#f05d56] <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                placeholder="<?php echo e(__('announcements.write_full_announcement_here')); ?>"><?php echo e(old('body')); ?></textarea>
            <?php $__errorArgs = ['body'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="text-sm text-[#f05d56] mt-1"><?php echo e($message); ?></p>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        
        <div class="grid gap-6 sm:grid-cols-2">
            <div>
                <span class="block text-sm font-medium text-[#182438] mb-2"><?php echo e(__('Tag')); ?></span>
                <div class="inline-flex flex-wrap gap-2">
                    <?php $oldTag = old('tag', ''); ?>
                    <label class="cursor-pointer">
                        <input type="radio" name="tag" value="" class="sr-only peer" <?php if($oldTag === ''): echo 'checked'; endif; ?>>
                        <span class="block px-4 py-2 rounded-lg border border-[#ebe8e1] text-sm font-semibold text-[#5f6873] peer-checked:bg-[#182438] peer-checked:text-white peer-checked:border-[#182438] peer-focus-visible:ring-2 peer-focus-visible:ring-[#ff8555]"><?php echo e(__('None')); ?></span>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="tag" value="urgent" class="sr-only peer" <?php if($oldTag === 'urgent'): echo 'checked'; endif; ?>>
                        <span class="block px-4 py-2 rounded-lg border border-[#f5c36b] text-sm font-semibold text-[#8a5a00] peer-checked:bg-[#f5a623] peer-checked:text-[#3d2a00] peer-checked:border-[#f5a623] peer-focus-visible:ring-2 peer-focus-visible:ring-[#ff8555]"><?php echo e(__('Urgent')); ?></span>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="tag" value="emergency" class="sr-only peer" <?php if($oldTag === 'emergency'): echo 'checked'; endif; ?>>
                        <span class="block px-4 py-2 rounded-lg border border-[#f05d56] text-sm font-semibold text-[#c0261f] peer-checked:bg-[#c0261f] peer-checked:text-white peer-checked:border-[#c0261f] peer-focus-visible:ring-2 peer-focus-visible:ring-[#ff8555]"><?php echo e(__('Emergency')); ?></span>
                    </label>
                </div>
                <?php $__errorArgs = ['tag'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="text-sm text-[#f05d56] mt-1"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div>
                <span class="block text-sm font-medium text-[#182438] mb-2"><?php echo e(__('Pin')); ?></span>
                <label class="inline-flex items-center gap-3 cursor-pointer rounded-lg border border-[#ebe8e1] px-4 py-2.5 hover:bg-[#faf8f3] transition">
                    <input type="checkbox" name="is_pinned" value="1" <?php if(old('is_pinned')): echo 'checked'; endif; ?> class="w-4 h-4 text-[#ff8555] rounded focus:ring-[#ff8555]">
                    <span class="text-sm text-[#182438]"><?php echo e(__('Keep at the top of the board')); ?></span>
                </label>
            </div>
        </div>

        
        <div class="rounded-xl border border-[#ebe8e1] p-4">
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="poll_enabled" value="1" x-model="poll" class="w-4 h-4 text-[#ff8555] rounded focus:ring-[#ff8555]">
                <span class="text-sm font-semibold text-[#182438]"><?php echo e(__('Add a poll to this announcement')); ?></span>
            </label>

            <div x-show="poll" x-cloak style="display:none;" class="mt-4 space-y-4">
                <div>
                    <label for="poll_question" class="block text-sm font-medium text-[#182438] mb-2"><?php echo e(__('Poll question')); ?> <span class="text-[#f05d56]">*</span></label>
                    <input type="text" name="poll_question" id="poll_question" maxlength="255" value="<?php echo e(old('poll_question')); ?>" :required="poll"
                        class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none <?php $__errorArgs = ['poll_question'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-[#f05d56] <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                        placeholder="<?php echo e(__('e.g. Which day works best for the parents’ meeting?')); ?>">
                    <?php $__errorArgs = ['poll_question'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="text-sm text-[#f05d56] mt-1"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div>
                    <span class="block text-sm font-medium text-[#182438] mb-2"><?php echo e(__('Options')); ?> <span class="text-[#8b949d] font-normal">(<?php echo e(__('2 to 6')); ?>)</span></span>
                    <div class="space-y-2" x-ref="optionList">
                        <template x-for="(option, i) in options" :key="i">
                            <div class="flex items-center gap-2">
                                <input type="text" name="poll_options[]" maxlength="150" x-model="options[i]" :required="poll && i < min"
                                    :placeholder="<?php echo \Illuminate\Support\Js::from(__('Option'))->toHtml() ?> + ' ' + (i + 1)"
                                    class="flex-1 px-4 py-2.5 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                                <button type="button" @click="remove(i)" x-show="options.length > min" :aria-label="<?php echo \Illuminate\Support\Js::from(__('Remove option'))->toHtml() ?>"
                                    class="w-9 h-9 rounded-lg text-xl leading-none text-[#8b949d] hover:text-[#f05d56] hover:bg-[#fdecea] transition">&times;</button>
                            </div>
                        </template>
                    </div>
                    <button type="button" @click="add()" x-show="options.length < max"
                        class="mt-3 text-sm font-semibold text-[#258cc5] hover:underline">+ <?php echo e(__('Add option')); ?></button>
                    <?php $__currentLoopData = ['poll_options', 'poll_options.*']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $__errorArgs = [$field];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="text-sm text-[#f05d56] mt-1"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="poll_multiple" value="1" <?php if(old('poll_multiple')): echo 'checked'; endif; ?> class="w-4 h-4 text-[#ff8555] rounded focus:ring-[#ff8555]">
                        <span class="text-sm text-[#182438]"><?php echo e(__('Allow choosing more than one option')); ?></span>
                    </label>
                    <div>
                        <label for="poll_closes_at" class="block text-sm font-medium text-[#182438] mb-1"><?php echo e(__('Closes on')); ?> <span class="text-[#8b949d] font-normal">(<?php echo e(__('optional')); ?>)</span></label>
                        <input type="datetime-local" name="poll_closes_at" id="poll_closes_at" value="<?php echo e(old('poll_closes_at')); ?>"
                            class="w-full px-4 py-2.5 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none <?php $__errorArgs = ['poll_closes_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-[#f05d56] <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                        <?php $__errorArgs = ['poll_closes_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="text-sm text-[#f05d56] mt-1"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>
                <p class="text-xs text-[#8b949d]"><?php echo e(__('Everyone who can read the board can vote and change their answer until the poll closes. Only totals are shown, not who voted for what. A poll cannot be edited once published.')); ?></p>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-[#ebe8e1]">
            <a href="<?php echo e(route('announcements.index')); ?>" class="px-6 py-2.5 border border-[#ebe8e1] rounded-lg text-[#5f6873] hover:bg-[#faf8f3] transition"><?php echo e(__('announcements.cancel')); ?></a>
            <button type="submit" class="px-6 py-2.5 bg-[#ff8555] text-white rounded-lg hover:bg-[#f47548] transition font-medium"><?php echo e(__('announcements.publish')); ?></button>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.announcements', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kidlink-laravel\resources\views/announcements/create.blade.php ENDPATH**/ ?>