

<?php $__env->startSection('title', 'Students'); ?>
<?php $__env->startSection('page-title', 'Students'); ?>

<?php $__env->startSection('content'); ?>
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-[#182438]">Manage Enrolled Students</h2>
            <p class="text-sm text-[#5f6873] mt-1">Add, search, and manage student records</p>
        </div>
        <button onclick="document.getElementById('addStudentModal').classList.remove('hidden')" class="bg-[#ff8555] hover:bg-[#f47548] text-white px-6 py-2.5 rounded-lg transition font-medium self-start md:self-auto">
            + Add Student
        </button>
    </div>
    <div class="mt-4">
        <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#8b949d]">⌕</span>
            <input type="text" id="studentSearch" placeholder="Search students..." autocomplete="off"
                class="w-full md:w-80 pl-9 pr-4 py-2.5 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-[#faf8f3]">
<tr>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-[#182438]">Name</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-[#182438]">Class</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-[#182438]">Age</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-[#182438]">Parent</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-[#182438]">Enrolled</th>
                    <th class="px-6 py-4 text-center text-sm font-semibold text-[#182438]">Status</th>
                    <th class="px-6 py-4 text-center text-sm font-semibold text-[#182438]">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#ebe8e1]">
<?php $__empty_1 = true; $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $editPayload = [
                        'id' => $student->id,
                        'first_name' => $student->first_name,
                        'last_name' => $student->last_name,
                        'sex' => $student->sex,
                        'date_of_birth' => $student->date_of_birth?->format('Y-m-d'),
                        'address' => $student->address,
                        'class_name' => $student->class_name,
                        'parent_ids' => $student->parents->pluck('id')->all(),
                    ];
                ?>
                <tr class="hover:bg-[#faf8f3] transition">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-[#ff8555] rounded-full flex items-center justify-center text-white font-bold text-sm">
                                <?php echo e(strtoupper(substr($student->first_name, 0, 1))); ?><?php echo e(strtoupper(substr($student->last_name, 0, 1))); ?>

                            </div>
                            <span class="font-medium text-[#182438]"><?php echo e($student->name); ?></span>
                        </div>
                    </td>
<td class="px-6 py-4 text-[#5f6873"><?php echo e($student->age); ?> yrs</td>
                    <td class="px-6 py-4">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo e($student->class_name ? 'bg-[#fff0e6] text-[#ff8555]' : 'bg-[#ebe8e1] text-[#8b949d]'); ?>">
                            <?php echo e($student->class_name ?? 'Unassigned'); ?>

                        </span>
                    </td>
                    <td class="px-6 py-4 text-[#5f6873]"><?php echo e($student->parents->isEmpty() ? '—' : $student->parents->pluck('full_name')->implode(', ')); ?></td>
                    <td class="px-6 py-4 text-[#5f6873]"><?php echo e($student->enrolled_date?->format('Y-m-d') ?? '—'); ?></td>
                    <td class="px-6 py-4 text-center">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo e($student->is_active ? 'bg-green-100 text-green-700' : 'bg-[#ebe8e1] text-[#5f6873]'); ?>">
                            <?php echo e($student->is_active ? 'active' : 'inactive'); ?>

                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-center gap-2">
                            <button type="button" title="Edit"
                                data-edit='<?php echo json_encode($editPayload, 15, 512) ?>'
                                class="edit-student-btn w-9 h-9 rounded-lg border border-[#ebe8e1] hover:bg-[#faf8f3] transition text-[#258cc5]">
                                ✎
                            </button>
                            <form method="POST" action="<?php echo e(route('teacher.students.toggle', $student)); ?>">
                                <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                                <button type="submit" title="<?php echo e($student->is_active ? 'Deactivate' : 'Activate'); ?>"
                                    class="w-9 h-9 rounded-lg border border-[#ebe8e1] hover:bg-[#faf8f3] transition text-[#258cc5]">
                                    <?php echo e($student->is_active ? '⏸' : '▶'); ?>

                                </button>
                            </form>
                            <form method="POST" action="<?php echo e(route('teacher.students.destroy', $student)); ?>" onsubmit="return confirm('Delete this student?')">
                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                <button type="submit" title="Delete"
                                    class="w-9 h-9 rounded-lg border border-[#ebe8e1] hover:bg-[#fde8ef] transition text-[#f05d56]">
                                    ✕
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="7" class="px-6 py-12 text-center text-[#8b949d]">No students found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="addStudentModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl p-8 w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-[#182438]">Add Student</h3>
            <button onclick="this.closest('#addStudentModal').classList.add('hidden')" class="text-[#8b949d] hover:text-[#5f6873] text-2xl">&times;</button>
        </div>
        <form method="POST" action="<?php echo e(route('teacher.students.store')); ?>" class="space-y-4">
            <?php echo csrf_field(); ?>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-[#182438] mb-2">First Name</label>
                    <input type="text" name="first_name" required class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#182438] mb-2">Last Name</label>
                    <input type="text" name="last_name" required class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                </div>
            </div>
<div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-[#182438] mb-2">Sex</label>
                    <select name="sex" required class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                        <option value="Female">Female</option>
                        <option value="Male">Male</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#182438] mb-2">Date of Birth</label>
                    <input type="date" name="date_of_birth" required class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2">Class</label>
                <input type="text" name="class_name" list="classNames" placeholder="e.g. Nursery, Pre-K, Kinder 1..." class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                <datalist id="classNames">
                    <option value="Nursery">
                    <option value="Pre-K">
                    <option value="Kinder 1">
                    <option value="Kinder 2">
                    <option value="Grade 1">
                </datalist>
            </div>
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2">Address</label>
                <input type="text" name="address" class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2">Connect Parent Account(s)</label>
                <div class="relative mb-2">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#8b949d]">⌕</span>
                    <input type="text" id="parentSearch" placeholder="Search parents by name or email..." autocomplete="off"
                        class="w-full pl-9 pr-4 py-2 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none text-sm">
                </div>
                <div id="parentList" class="border border-[#ebe8e1] rounded-lg max-h-40 overflow-y-auto p-3 space-y-2">
                    <?php $__empty_1 = true; $__currentLoopData = $parents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $parent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <label class="flex items-center gap-3 cursor-pointer hover:bg-[#faf8f3] px-2 py-1 rounded-lg transition parent-item">
                        <input type="checkbox" name="parent_ids[]" value="<?php echo e($parent->id); ?>" class="w-4 h-4 text-[#ff8555] rounded focus:ring-[#ff8555]">
                        <span class="text-sm text-[#5f6873] parent-label"><?php echo e($parent->full_name); ?> <span class="text-xs text-[#8b949d]">(<?php echo e($parent->email); ?>)</span></span>
                    </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-sm text-[#8b949d] p-2">No parent accounts yet. Ask the admin to create parent accounts first.</p>
                    <?php endif; ?>
                </div>
                <p class="text-xs text-[#8b949d] mt-1">Tick multiple to connect a student to several parents.</p>
            </div>
            <div class="border-t border-[#ebe8e1] pt-4">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="create_login" id="createLoginToggle" class="w-5 h-5 text-[#ff8555] rounded focus:ring-[#ff8555]">
                    <span class="text-sm font-medium text-[#182438]">Create a student login account</span>
                </label>
            </div>
            <div id="loginFields" class="grid grid-cols-2 gap-4 hidden">
                <div>
                    <label class="block text-sm font-medium text-[#182438] mb-2">Login Email</label>
                    <input type="email" name="student_email" class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none" placeholder="student@kidlink.test">
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#182438] mb-2">Password</label>
                    <input type="password" name="student_password" minlength="8" class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none" placeholder="Min 8 characters">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-[#182438] mb-2">Father's Name</label>
                    <input type="text" name="father_name" class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#182438] mb-2">Mother's Name</label>
                    <input type="text" name="mother_name" class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="this.closest('#addStudentModal').classList.add('hidden')" class="px-6 py-2.5 border border-[#ebe8e1] rounded-lg text-[#5f6873] hover:bg-[#faf8f3] transition">Cancel</button>
                <button type="submit" class="px-6 py-2.5 bg-[#ff8555] text-white rounded-lg hover:bg-[#f47548] transition font-medium">Add Student</button>
            </div>
</form>
    </div>
</div>

<div id="editStudentModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl p-8 w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-[#182438]">Edit Student</h3>
            <button onclick="this.closest('#editStudentModal').classList.add('hidden')" class="text-[#8b949d] hover:text-[#5f6873] text-2xl">&times;</button>
        </div>
        <form method="POST" id="editStudentForm" class="space-y-4">
            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-[#182438] mb-2">First Name</label>
                    <input type="text" name="first_name" id="editSFirstName" required class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#182438] mb-2">Last Name</label>
                    <input type="text" name="last_name" id="editSLastName" required class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-[#182438] mb-2">Sex</label>
                    <select name="sex" id="editSSex" required class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                        <option value="Female">Female</option>
                        <option value="Male">Male</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#182438] mb-2">Date of Birth</label>
                    <input type="date" name="date_of_birth" id="editSDOB" required class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2">Class</label>
                <input type="text" name="class_name" id="editSClass" list="classNames" placeholder="e.g. Nursery, Pre-K, Kinder 1..." class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2">Address</label>
                <input type="text" name="address" id="editSAddress" class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2">Connect Parent Account(s)</label>
                <div class="relative mb-2">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#8b949d]">⌕</span>
                    <input type="text" id="editParentSearch" placeholder="Search parents by name or email..." autocomplete="off"
                        class="w-full pl-9 pr-4 py-2 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none text-sm">
                </div>
                <div id="editParentList" class="border border-[#ebe8e1] rounded-lg max-h-40 overflow-y-auto p-3 space-y-2">
                    <?php $__currentLoopData = $parents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $parent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <label class="flex items-center gap-3 cursor-pointer hover:bg-[#faf8f3] px-2 py-1 rounded-lg transition parent-item">
                        <input type="checkbox" name="parent_ids[]" value="<?php echo e($parent->id); ?>" class="w-4 h-4 text-[#ff8555] rounded focus:ring-[#ff8555]">
                        <span class="text-sm text-[#5f6873] parent-label"><?php echo e($parent->full_name); ?> <span class="text-xs text-[#8b949d]">(<?php echo e($parent->email); ?>)</span></span>
                    </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <p class="text-xs text-[#8b949d] mt-1">Tick multiple to connect a student to several parents.</p>
            </div>
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="this.closest('#editStudentModal').classList.add('hidden')" class="px-6 py-2.5 border border-[#ebe8e1] rounded-lg text-[#5f6873] hover:bg-[#faf8f3] transition">Cancel</button>
                <button type="submit" class="px-6 py-2.5 bg-[#ff8555] text-white rounded-lg hover:bg-[#f47548] transition font-medium">Save Changes</button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
const createLoginToggle = document.getElementById('createLoginToggle');
const loginFields = document.getElementById('loginFields');

if (createLoginToggle && loginFields) {
    createLoginToggle.addEventListener('change', function () {
        loginFields.classList.toggle('hidden', !this.checked);
    });
}

const searchInput = document.getElementById('studentSearch');
const tableBody = document.querySelector('#students tbody') ?? document.querySelector('tbody');

if (searchInput && tableBody) {
    searchInput.addEventListener('input', function () {
        const search = this.value.toLowerCase().trim();
        const rows = tableBody.querySelectorAll('tr');
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(search) ? '' : 'none';
        });
    });
}

const parentSearch = document.getElementById('parentSearch');
const parentItems = document.querySelectorAll('.parent-item');

if (parentSearch) {
    parentSearch.addEventListener('input', function () {
        const search = this.value.toLowerCase().trim();
        parentItems.forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(search) ? '' : 'none';
        });
    });
}

const editStudentModal = document.getElementById('editStudentModal');
const editStudentForm = document.getElementById('editStudentForm');

document.querySelectorAll('.edit-student-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const d = JSON.parse(this.dataset.edit);

        document.getElementById('editSFirstName').value = d.first_name;
        document.getElementById('editSLastName').value = d.last_name;
        document.getElementById('editSSex').value = d.sex;
        document.getElementById('editSDOB').value = d.date_of_birth;
        document.getElementById('editSClass').value = d.class_name || '';
        document.getElementById('editSAddress').value = d.address || '';

        editStudentForm.action = `/teacher/students/${d.id}/update`;

        const checked = new Set(d.parent_ids || []);
        editStudentForm.querySelectorAll('#editParentList input').forEach(cb => {
            cb.checked = checked.has(Number(cb.value));
        });
        document.getElementById('editParentSearch').value = '';
        editStudentForm.querySelectorAll('#editParentList .parent-item').forEach(item => item.style.display = '');

        editStudentModal.classList.remove('hidden');
    });
});

const editParentSearch = document.getElementById('editParentSearch');
if (editParentSearch) {
    editParentSearch.addEventListener('input', function () {
        const search = this.value.toLowerCase().trim();
        editStudentForm.querySelectorAll('#editParentList .parent-item').forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(search) ? '' : 'none';
        });
    });
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.teacher', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kidlink-laravel\resources\views/teacher/students.blade.php ENDPATH**/ ?>