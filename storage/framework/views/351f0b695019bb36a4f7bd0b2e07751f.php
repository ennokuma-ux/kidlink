
<?php
    $requestRole = old('role', 'parent');
    $reopen = $errors->any() && old('role');
    $selectedParents = collect(old('parent_ids', []))->map(fn ($id) => (int) $id)->all();
?>

<div id="accountRequestModal" x-data="accountRequestForm(<?php echo \Illuminate\Support\Js::from($requestRole)->toHtml() ?>, <?php echo \Illuminate\Support\Js::from((bool) $reopen)->toHtml() ?>)"
     @open-account-request.window="openWith($event.detail)" @keydown.escape.window="open = false">
    <div x-show="open" x-cloak style="display:none;" @click.self="open = false" class="fixed inset-0 bg-black/50 flex items-start sm:items-center justify-center z-50 p-4 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="bg-white rounded-2xl p-6 sm:p-8 w-full max-w-lg max-h-[92vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-2">
                <h3 class="text-xl font-bold text-[#182438]"><?php echo e(__('teacher.request_account')); ?></h3>
                <button type="button" @click="open = false" class="text-[#8b949d] hover:text-[#5f6873] text-2xl" aria-label="<?php echo e(__('teacher.cancel')); ?>">&times;</button>
            </div>
            <p class="text-sm text-[#5f6873] mb-5"><?php echo e(__('teacher.request_note')); ?></p>

            <?php if($reopen): ?>
                <div class="mb-4 rounded-lg bg-[#ffe9ea] px-4 py-3 text-sm text-[#c0392b]">
                    <ul class="list-disc pl-5 space-y-0.5">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($message); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo e(route('teacher.account-requests.store')); ?>" class="space-y-4" x-ref="form">
                <?php echo csrf_field(); ?>

                <div>
                    <label class="block text-sm font-medium text-[#182438] mb-2"><?php echo e(__('teacher.request_type')); ?></label>
                    <div class="inline-flex rounded-lg border border-[#f1e7cc] overflow-hidden text-sm font-semibold">
                        <label class="cursor-pointer"><input type="radio" name="role" value="parent" x-model="role" class="sr-only peer">
                            <span class="block px-5 py-2 peer-checked:bg-[#1982c4] peer-checked:text-white text-[#5f6873]"><?php echo e(__('teacher.request_parent')); ?></span></label>
                        <label class="cursor-pointer"><input type="radio" name="role" value="student" x-model="role" class="sr-only peer">
                            <span class="block px-5 py-2 peer-checked:bg-[#ff8555] peer-checked:text-white text-[#5f6873]"><?php echo e(__('teacher.request_student')); ?></span></label>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-[#182438] mb-2"><?php echo e(__('teacher.first_name')); ?></label>
                        <input type="text" name="first_name" x-ref="first_name" value="<?php echo e(old('first_name')); ?>" required class="w-full px-4 py-3 border border-[#f1e7cc] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-[#182438] mb-2"><?php echo e(__('teacher.last_name')); ?></label>
                        <input type="text" name="last_name" x-ref="last_name" value="<?php echo e(old('last_name')); ?>" required class="w-full px-4 py-3 border border-[#f1e7cc] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                    </div>
                </div>

                
                <div x-show="role === 'parent'">
                    <label class="block text-sm font-medium text-[#182438] mb-2"><?php echo e(__('teacher.email')); ?></label>
                    <input type="email" name="email" x-ref="email" value="<?php echo e(old('email')); ?>" :required="role === 'parent'" :disabled="role !== 'parent'" class="w-full px-4 py-3 border border-[#f1e7cc] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                </div>

                
                <div x-show="role === 'parent'">
                    <label class="block text-sm font-medium text-[#182438] mb-2"><?php echo e(__('teacher.contact_optional')); ?></label>
                    <input type="text" name="contact" x-ref="contact" value="<?php echo e(old('contact')); ?>" class="w-full px-4 py-3 border border-[#f1e7cc] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                </div>

                
                <div x-show="role === 'student'" x-cloak style="display:none;" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-[#182438] mb-2"><?php echo e(__('teacher.sex')); ?></label>
                            <select name="sex" x-ref="sex" :required="role === 'student'" class="w-full px-4 py-3 border border-[#f1e7cc] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                                <option value="Female" <?php if(old('sex') === 'Female'): echo 'selected'; endif; ?>><?php echo e(__('teacher.female')); ?></option>
                                <option value="Male" <?php if(old('sex') === 'Male'): echo 'selected'; endif; ?>><?php echo e(__('teacher.male')); ?></option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-[#182438] mb-2"><?php echo e(__('teacher.date_of_birth')); ?></label>
                            <input type="date" name="date_of_birth" x-ref="date_of_birth" value="<?php echo e(old('date_of_birth')); ?>" max="<?php echo e(now()->toDateString()); ?>" @input="autoSection()" @change="autoSection()" :required="role === 'student'" class="w-full px-4 py-3 border border-[#f1e7cc] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-[#182438] mb-2"><?php echo e(__('teacher.class')); ?></label>
                        <select name="class_name" x-ref="class_name" class="w-full px-4 py-3 border border-[#f1e7cc] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                            <option value=""><?php echo e(__('teacher.not_set')); ?></option>
                            <?php $__currentLoopData = \App\Models\Student::SECTIONS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($section); ?>" <?php if(old('class_name') === $section): echo 'selected'; endif; ?>><?php echo e($section); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-[#182438] mb-2"><?php echo e(__('Academic year')); ?></label>
                        <select name="academic_year" x-ref="academic_year" class="w-full px-4 py-3 border border-[#f1e7cc] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                            <?php $__currentLoopData = $academicYears; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($year); ?>" <?php if(old('academic_year', \App\Models\Student::currentAcademicYear()) === $year): echo 'selected'; endif; ?>><?php echo e($year); ?><?php if($year === \App\Models\Student::currentAcademicYear()): ?> (<?php echo e(__('current')); ?>)<?php endif; ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-[#182438] mb-2"><?php echo e(__('student.hand_label')); ?></label>
                        <select name="dominant_hand" x-ref="dominant_hand" class="w-full px-4 py-3 border border-[#f1e7cc] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                            <?php $__currentLoopData = \App\Models\Student::HANDS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hand): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($hand); ?>" <?php if(old('dominant_hand', 'Not yet established') === $hand): echo 'selected'; endif; ?>><?php echo e(\App\Models\Student::handLabels()[$hand]); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-[#182438] mb-2"><?php echo e(__('teacher.address')); ?></label>
                        <input type="text" name="address" x-ref="address" value="<?php echo e(old('address')); ?>" class="w-full px-4 py-3 border border-[#f1e7cc] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#182438] mb-2"><?php echo e(__('teacher.connect_parent_accounts')); ?></label>
                        <input type="text" x-model="parentSearch" placeholder="<?php echo e(__('teacher.search_parents')); ?>" autocomplete="off"
                               class="w-full mb-2 px-4 py-2 border border-[#f1e7cc] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none text-sm">
                        <div class="border border-[#f1e7cc] rounded-lg max-h-40 overflow-y-auto p-3 space-y-2">
                            <?php $__empty_1 = true; $__currentLoopData = $activeParents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $parentUser): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <label class="flex items-center gap-3 cursor-pointer hover:bg-[#fffbf0] px-2 py-1 rounded-lg transition"
                                       x-show="!parentSearch || <?php echo \Illuminate\Support\Js::from(mb_strtolower($parentUser->full_name.' '.$parentUser->email))->toHtml() ?>.includes(parentSearch.toLowerCase())">
                                    <input type="checkbox" name="parent_ids[]" value="<?php echo e($parentUser->id); ?>" x-model="parentIds" :value="<?php echo e($parentUser->id); ?>"
                                           class="w-4 h-4 text-[#ff8555] rounded focus:ring-[#ff8555]">
                                    <span class="text-sm text-[#5f6873]"><?php echo e($parentUser->full_name); ?> <span class="text-xs text-[#8b949d]">(<?php echo e($parentUser->email); ?>)</span></span>
                                </label>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <p class="text-sm text-[#8b949d] p-2"><?php echo e(__('teacher.no_parent_accounts_yet')); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-[#182438] mb-2"><?php echo e(__('teacher.father_name')); ?></label>
                            <input type="text" name="father_name" x-ref="father_name" value="<?php echo e(old('father_name')); ?>" class="w-full px-4 py-3 border border-[#f1e7cc] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-[#182438] mb-2"><?php echo e(__('teacher.mother_name')); ?></label>
                            <input type="text" name="mother_name" x-ref="mother_name" value="<?php echo e(old('mother_name')); ?>" class="w-full px-4 py-3 border border-[#f1e7cc] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="open = false" class="px-6 py-2.5 border border-[#f1e7cc] rounded-lg text-[#5f6873] hover:bg-[#fffbf0] transition"><?php echo e(__('teacher.cancel')); ?></button>
                    <button type="submit" class="px-6 py-2.5 bg-[#ff8555] text-white rounded-lg hover:bg-[#f47548] transition font-medium"><?php echo e(__('teacher.request_submit')); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
if (typeof window.accountRequestForm !== 'function') {
window.accountRequestForm = function (role, reopen) {
    return {
        open: reopen, role: role, parentSearch: '', parentIds: <?php echo \Illuminate\Support\Js::from($selectedParents)->toHtml() ?>,
        sectionRanges: <?php echo \Illuminate\Support\Js::from(\App\Support\SectionPlacement::ranges())->toHtml() ?>,
        // Picks the section that matches the birthday; leaves it alone when the age fits none.
        autoSection() {
            const dob = this.$refs.date_of_birth.value;
            if (!dob) return;
            const b = new Date(dob + 'T00:00:00'), t = new Date();
            if (isNaN(b) || b > t) return;
            let age = t.getFullYear() - b.getFullYear();
            if (t.getMonth() < b.getMonth() || (t.getMonth() === b.getMonth() && t.getDate() < b.getDate())) age--;
            const match = Object.entries(this.sectionRanges).find(([, r]) => age >= r.min && age <= r.max);
            if (match) this.$refs.class_name.value = match[0];
        },
        openWith(detail) {
            // A fresh request starts blank; a rejected one sent again arrives with its details.
            const d = detail || {};
            const fresh = !detail || d.fresh;
            this.role = d.role || 'parent';
            this.parentSearch = '';
            this.parentIds = (d.parent_ids || []).map(Number);
            this.$nextTick(() => {
                const blank = { sex: 'Female', dominant_hand: 'Not yet established', academic_year: <?php echo \Illuminate\Support\Js::from(\App\Models\Student::currentAcademicYear())->toHtml() ?> };
                ['first_name', 'last_name', 'email', 'contact', 'sex', 'date_of_birth', 'class_name', 'academic_year', 'dominant_hand',
                 'address', 'father_name', 'mother_name'].forEach(key => {
                    const el = this.$refs[key];
                    if (!el) return;
                    if (fresh) el.value = blank[key] !== undefined ? blank[key] : '';
                    else if (d[key] !== undefined && d[key] !== null) el.value = d[key];
                });
            });
            this.open = true;
        },
    };
};
}
</script>
<?php /**PATH C:\laragon\www\kidlink-laravel\resources\views/teacher/partials/account-request-modal.blade.php ENDPATH**/ ?>