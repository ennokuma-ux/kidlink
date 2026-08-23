<?php $__env->startSection('title', 'Manage Users'); ?>
<?php $__env->startSection('page-title', 'Manage Accounts'); ?>

<?php $__env->startSection('content'); ?>
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-[#182438]">All Accounts</h2>
            <p class="text-sm text-[#5f6873] mt-1">Create and manage teacher and parent accounts</p>
        </div>
        <button onclick="document.getElementById('addUserModal').classList.remove('hidden')" class="bg-[#ff8555] hover:bg-[#f47548] text-white px-6 py-2.5 rounded-lg transition font-medium self-start md:self-auto">
            + Create Account
        </button>
    </div>
    <form method="GET" action="<?php echo e(route('admin.users.index')); ?>" class="mt-4 flex flex-col md:flex-row md:items-center gap-3">
        <div class="relative flex-1 md:max-w-xs">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#8b949d]">⌕</span>
            <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Search by name or email..." autocomplete="off"
                class="w-full pl-9 pr-4 py-2.5 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
        </div>
        <select name="role" onchange="this.form.submit()" class="px-4 py-2.5 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
            <option value="">All roles</option>
            <option value="teacher" <?php echo e(request('role') == 'teacher' ? 'selected' : ''); ?>>Teacher</option>
            <option value="parent" <?php echo e(request('role') == 'parent' ? 'selected' : ''); ?>>Parent</option>
            <option value="student" <?php echo e(request('role') == 'student' ? 'selected' : ''); ?>>Student</option>
            <option value="super_admin" <?php echo e(request('role') == 'super_admin' ? 'selected' : ''); ?>>Super Admin</option>
        </select>
        <button type="submit" class="bg-[#182438] text-white px-5 py-2.5 rounded-lg transition font-medium">Filter</button>
    </form>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-4 text-center border-l-4 border-[#258cc5]">
        <p class="text-2xl font-bold text-[#182438]"><?php echo e($stats['teachers']); ?></p>
        <p class="text-sm text-[#5f6873]">Teachers</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 text-center border-l-4 border-[#68b82f]">
        <p class="text-2xl font-bold text-[#68b82f]"><?php echo e($stats['parents']); ?></p>
        <p class="text-sm text-[#5f6873]">Parents</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 text-center border-l-4 border-[#f05d56]">
        <p class="text-2xl font-bold text-[#f05d56]"><?php echo e($stats['students']); ?></p>
        <p class="text-sm text-[#5f6873]">Students</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 text-center border-l-4 border-[#d9b348]">
        <p class="text-2xl font-bold text-[#d9b348]"><?php echo e($stats['admins']); ?></p>
        <p class="text-sm text-[#5f6873]">Super Admins</p>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-[#faf8f3]">
                <tr>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-[#182438]">Name</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-[#182438]">Email</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-[#182438]">Role</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-[#182438]">Contact</th>
                    <th class="px-6 py-4 text-center text-sm font-semibold text-[#182438]">Status</th>
                    <th class="px-6 py-4 text-center text-sm font-semibold text-[#182438]">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#ebe8e1]">
                <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="hover:bg-[#faf8f3] transition">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-[#ff8555] rounded-full flex items-center justify-center text-white font-bold text-sm">
                                <?php echo e(strtoupper(substr($user->first_name, 0, 1))); ?><?php echo e(strtoupper(substr($user->last_name, 0, 1))); ?>

                            </div>
                            <span class="font-medium text-[#182438]"><?php echo e($user->full_name); ?></span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-[#5f6873]"><?php echo e($user->email); ?></td>
                    <td class="px-6 py-4">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold capitalize
                            <?php echo e($user->role === 'teacher' ? 'bg-blue-100 text-blue-700' : ($user->role === 'parent' ? 'bg-green-100 text-green-700' : ($user->role === 'super_admin' ? 'bg-purple-100 text-purple-700' : 'bg-yellow-100 text-yellow-700'))); ?>">
                            <?php echo e(str_replace('_', ' ', $user->role)); ?>

                        </span>
                    </td>
                    <td class="px-6 py-4 text-[#5f6873]"><?php echo e($user->contact ?? '—'); ?></td>
                    <td class="px-6 py-4 text-center">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo e($user->is_active ? 'bg-green-100 text-green-700' : 'bg-[#ebe8e1] text-[#5f6873]'); ?>">
                            <?php echo e($user->is_active ? 'active' : 'inactive'); ?>

                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-center gap-2">
                            <?php if($user->id !== auth()->id() && $user->role !== 'super_admin'): ?>
                            <button type="button" title="Edit"
                                data-edit='<?php echo json_encode($editData[$user->id], 15, 512) ?>'
                                class="edit-user-btn w-9 h-9 rounded-lg border border-[#ebe8e1] hover:bg-[#faf8f3] transition text-[#258cc5]">
                                ✎
                            </button>
                            <form method="POST" action="<?php echo e(route('admin.users.destroy', $user)); ?>" class="delete-form" data-name="<?php echo e($user->full_name); ?>">
                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                <button type="submit" title="Delete"
                                    class="w-9 h-9 rounded-lg border border-[#ebe8e1] hover:bg-[#fde8ef] transition text-[#f05d56]">
                                    ✕
                                </button>
                            </form>
                            <?php else: ?>
                            <span class="text-xs text-[#8b949d]">—</span>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="6" class="px-6 py-12 text-center text-[#8b949d]">No accounts found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="addUserModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl p-8 w-full max-w-md">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-[#182438]">Create Account</h3>
            <button onclick="this.closest('#addUserModal').classList.add('hidden')" class="text-[#8b949d] hover:text-[#5f6873] text-2xl">&times;</button>
        </div>
        <form method="POST" action="<?php echo e(route('admin.users.store')); ?>" class="space-y-4">
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
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2">Email</label>
                <input type="email" name="email" required class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-[#182438] mb-2">Password</label>
                    <input type="password" name="password" required minlength="8" class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#182438] mb-2">Role</label>
                    <select name="role" required class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                        <option value="teacher">Teacher</option>
                        <option value="parent">Parent</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2">Contact (optional)</label>
                <input type="text" name="contact" class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
            </div>
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="this.closest('#addUserModal').classList.add('hidden')" class="px-6 py-2.5 border border-[#ebe8e1] rounded-lg text-[#5f6873] hover:bg-[#faf8f3] transition">Cancel</button>
                <button type="submit" class="px-6 py-2.5 bg-[#ff8555] text-white rounded-lg hover:bg-[#f47548] transition font-medium">Create Account</button>
            </div>
        </form>
    </div>
</div>

<div id="editUserModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl p-8 w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-[#182438]">Edit Account</h3>
            <button onclick="this.closest('#editUserModal').classList.add('hidden')" class="text-[#8b949d] hover:text-[#5f6873] text-2xl">&times;</button>
        </div>
        <form method="POST" id="editUserForm" class="space-y-4">
            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-[#182438] mb-2">First Name</label>
                    <input type="text" name="first_name" id="editFirstName" required class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#182438] mb-2">Last Name</label>
                    <input type="text" name="last_name" id="editLastName" required class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2">Email</label>
                <input type="email" name="email" id="editEmail" required class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-[#182438] mb-2">Contact (optional)</label>
                    <input type="text" name="contact" id="editContact" class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#182438] mb-2">New Password (optional)</label>
                    <input type="password" name="password" id="editPassword" class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none" placeholder="Leave blank to keep">
                </div>
            </div>
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="is_active" id="editIsActive" value="1" class="w-5 h-5 text-[#ff8555] rounded focus:ring-[#ff8555]">
                <span class="text-sm font-medium text-[#182438]">Account is active</span>
            </label>

            <div id="editClassField" class="hidden">
                <label class="block text-sm font-medium text-[#182438] mb-2">Class</label>
                <input type="text" name="class_name" id="editClassName" list="classNames" placeholder="e.g. Nursery, Pre-K, Kinder 1..." class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                <datalist id="classNames">
                    <option value="Nursery">
                    <option value="Pre-K">
                    <option value="Kinder 1">
                    <option value="Kinder 2">
                    <option value="Grade 1">
                </datalist>
            </div>

            <div id="editConnections" class="hidden">
                <label class="block text-sm font-medium text-[#182438] mb-2" id="editConnLabel">Connect Students</label>
                <div class="relative mb-2">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#8b949d]">⌕</span>
                    <input type="text" id="editConnSearch" placeholder="Search students..." autocomplete="off"
                        class="w-full pl-9 pr-4 py-2 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none text-sm">
                </div>
                <div id="editConnList" class="border border-[#ebe8e1] rounded-lg max-h-40 overflow-y-auto p-3 space-y-2">
                    <?php $__empty_1 = true; $__currentLoopData = $allStudents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <label class="flex items-center gap-3 cursor-pointer hover:bg-[#faf8f3] px-2 py-1 rounded-lg transition conn-item" data-name="<?php echo e($student->name); ?>">
                        <input type="checkbox" name="student_ids[]" value="<?php echo e($student->id); ?>" class="w-4 h-4 text-[#ff8555] rounded focus:ring-[#ff8555]">
                        <span class="text-sm text-[#5f6873]"><?php echo e($student->name); ?></span>
                    </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-sm text-[#8b949d] p-2">No students yet.</p>
                    <?php endif; ?>
                </div>
                <p class="text-xs text-[#8b949d] mt-1">Tick multiple to connect this parent to several students.</p>
            </div>

            <div id="editParentConnections" class="hidden">
                <label class="block text-sm font-medium text-[#182438] mb-2">Connect Parents</label>
                <div class="relative mb-2">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#8b949d]">⌕</span>
                    <input type="text" id="editParentConnSearch" placeholder="Search parents..." autocomplete="off"
                        class="w-full pl-9 pr-4 py-2 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none text-sm">
                </div>
                <div id="editParentConnList" class="border border-[#ebe8e1] rounded-lg max-h-40 overflow-y-auto p-3 space-y-2">
                    <?php $__empty_1 = true; $__currentLoopData = $allParents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $parent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <label class="flex items-center gap-3 cursor-pointer hover:bg-[#faf8f3] px-2 py-1 rounded-lg transition parent-conn-item" data-name="<?php echo e($parent->full_name); ?>">
                        <input type="checkbox" name="parent_ids[]" value="<?php echo e($parent->id); ?>" class="w-4 h-4 text-[#ff8555] rounded focus:ring-[#ff8555]">
                        <span class="text-sm text-[#5f6873]"><?php echo e($parent->full_name); ?></span>
                    </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-sm text-[#8b949d] p-2">No parent accounts yet.</p>
                    <?php endif; ?>
                </div>
                <p class="text-xs text-[#8b949d] mt-1">Tick multiple to connect this student to several parents.</p>
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="this.closest('#editUserModal').classList.add('hidden')" class="px-6 py-2.5 border border-[#ebe8e1] rounded-lg text-[#5f6873] hover:bg-[#faf8f3] transition">Cancel</button>
                <button type="submit" class="px-6 py-2.5 bg-[#ff8555] text-white rounded-lg hover:bg-[#f47548] transition font-medium">Save Changes</button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
const searchInput = document.querySelector('input[name="search"]');
const tableBody = document.querySelector('tbody');

if (searchInput) {
    searchInput.addEventListener('input', function () {
        const search = this.value.toLowerCase().trim();
        const rows = tableBody.querySelectorAll('tr');
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(search) ? '' : 'none';
        });
    });
}

document.querySelectorAll('.delete-form').forEach(form => {
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const name = this.dataset.name || 'this account';
        Swal.fire({
            title: 'Delete account?',
            text: `${name} will be permanently removed and cannot be undone.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f05d56',
            cancelButtonColor: '#5f6873',
            confirmButtonText: 'Yes, delete',
            cancelButtonText: 'Cancel',
        }).then((result) => {
            if (result.isConfirmed) {
                this.submit();
            }
        });
    });
});

const editModal = document.getElementById('editUserModal');
const editForm = document.getElementById('editUserForm');

document.querySelectorAll('.edit-user-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const d = JSON.parse(this.dataset.edit);

        document.getElementById('editFirstName').value = d.first_name;
        document.getElementById('editLastName').value = d.last_name;
        document.getElementById('editEmail').value = d.email;
        document.getElementById('editContact').value = d.contact || '';
        const editPassword = document.getElementById('editPassword');
        if (editPassword) editPassword.value = '';
        document.getElementById('editIsActive').checked = d.is_active;

        editForm.action = `/admin/users/${d.id}`;

        const classField = document.getElementById('editClassField');
        document.getElementById('editClassName').value = d.class_name || '';
        classField.classList.toggle('hidden', d.role !== 'student');

        const connBox = document.getElementById('editConnections');
        const parentConnBox = document.getElementById('editParentConnections');
        if (d.role === 'parent') {
            document.getElementById('editConnLabel').textContent = 'Connect Students';
            connBox.classList.remove('hidden');
            parentConnBox.classList.add('hidden');
            editForm.querySelectorAll('.conn-item').forEach(item => {
                item.style.display = '';
            });
            const checked = new Set(d.student_ids || []);
            editForm.querySelectorAll('.conn-item input').forEach(cb => {
                cb.checked = checked.has(Number(cb.value));
            });
        } else if (d.role === 'student') {
            connBox.classList.add('hidden');
            parentConnBox.classList.remove('hidden');
            editForm.querySelectorAll('.parent-conn-item').forEach(item => {
                item.style.display = '';
            });
            const checked = new Set(d.parent_ids || []);
            editForm.querySelectorAll('.parent-conn-item input').forEach(cb => {
                cb.checked = checked.has(Number(cb.value));
            });
        } else {
            connBox.classList.add('hidden');
            parentConnBox.classList.add('hidden');
        }

        document.getElementById('editConnSearch').value = '';
        document.getElementById('editParentConnSearch').value = '';
        editModal.classList.remove('hidden');
    });
});

const connSearch = document.getElementById('editConnSearch');
if (connSearch) {
    connSearch.addEventListener('input', function () {
        const q = this.value.toLowerCase().trim();
        editForm.querySelectorAll('.conn-item').forEach(item => {
            item.style.display = item.dataset.name.toLowerCase().includes(q) ? '' : 'none';
        });
    });
}

const parentConnSearch = document.getElementById('editParentConnSearch');
if (parentConnSearch) {
    parentConnSearch.addEventListener('input', function () {
        const q = this.value.toLowerCase().trim();
        editForm.querySelectorAll('.parent-conn-item').forEach(item => {
            item.style.display = item.dataset.name.toLowerCase().includes(q) ? '' : 'none';
        });
    });
}
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kidlink-laravel\resources\views/admin/users.blade.php ENDPATH**/ ?>