<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>KidLink - <?php echo $__env->yieldContent('title', 'Teacher Dashboard'); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body class="bg-[#faf8f3] font-sans antialiased">
    <div class="flex h-screen bg-[#faf8f3]">
        <?php echo $__env->make('components.teacher_sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <div class="flex-1 flex flex-col overflow-hidden md:ml-64">
            <header class="bg-white shadow-sm border-b border-[#ebe8e1] px-6 py-4">
                <div class="flex items-center justify-between">
                    <h1 class="text-2xl font-bold text-[#182438]"><?php echo $__env->yieldContent('page-title', 'Dashboard'); ?></h1>
                    <div class="flex items-center gap-4">
                        <span class="text-sm text-[#5f6873]">Welcome, <?php echo e(auth()->user()->full_name); ?></span>
                        <?php echo $__env->make('components.child_mode', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <form method="POST" action="<?php echo e(route('logout')); ?>" class="inline">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="bg-[#f05d56] hover:bg-[#e04942] text-white px-4 py-2 rounded-lg text-sm transition">Logout</button>
                        </form>
                    </div>
                </div>
            </header>
            <main class="flex-1 overflow-y-auto p-6">
                <?php echo $__env->yieldContent('content'); ?>
            </main>
        </div>
    </div>
    <?php echo $__env->yieldPushContent('scripts'); ?>
    <?php if(session('success')): ?>
    <script>Swal.fire({ icon: 'success', title: 'Success!', text: '<?php echo e(session('success')); ?>', timer: 3000, showConfirmButton: false });</script>
    <?php endif; ?>
    <?php if(session('error')): ?>
    <script>Swal.fire({ icon: 'error', title: 'Oops!', text: '<?php echo e(session('error')); ?>' });</script>
    <?php endif; ?>
</body>
</html>
<?php /**PATH C:\laragon\www\kidlink-laravel\resources\views/layouts/teacher.blade.php ENDPATH**/ ?>