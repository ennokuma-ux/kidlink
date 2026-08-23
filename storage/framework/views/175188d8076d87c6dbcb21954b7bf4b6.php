<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>KidLink - <?php echo $__env->yieldContent('title', 'Daycare Management'); ?></title>
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
<body class="bg-[#faf8f3] antialiased">
    <?php echo $__env->yieldContent('content'); ?>

    <?php echo $__env->yieldPushContent('scripts'); ?>
    <?php if(session('success')): ?>
    <script>
        Swal.fire({ icon: 'success', title: 'Success!', text: '<?php echo e(session('success')); ?>', timer: 3000, showConfirmButton: false });
    </script>
    <?php endif; ?>
    <?php if(session('info')): ?>
    <script>
        Swal.fire({ icon: 'info', title: 'Notice', text: '<?php echo e(session('info')); ?>', timer: 3000, showConfirmButton: false });
    </script>
    <?php endif; ?>
    <?php if($errors->any()): ?>
    <script>
        Swal.fire({ icon: 'error', title: 'Error!', text: '<?php echo e($errors->first()); ?>', timer: 5000, showConfirmButton: false });
    </script>
    <?php endif; ?>
</body>
</html>
<?php /**PATH C:\laragon\www\kidlink-laravel\resources\views/layouts/app.blade.php ENDPATH**/ ?>