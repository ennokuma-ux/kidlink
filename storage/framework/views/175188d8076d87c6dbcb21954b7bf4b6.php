<!DOCTYPE html>
<html lang="<?php echo e(app()->getLocale()); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>KidLink - <?php echo $__env->yieldContent('title', __('misc.daycare_management')); ?></title>
    
    
    <link href="<?php echo e(asset('css/fonts.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('css/mascot.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('css/icons.css')); ?>" rel="stylesheet">
    <script src="<?php echo e(asset('js/tailwind.min.js')); ?>"></script>
    <?php if (isset($component)) { $__componentOriginala219c84179cedd61bd9076a3fdaf8379 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala219c84179cedd61bd9076a3fdaf8379 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stronger-shadows','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stronger-shadows'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala219c84179cedd61bd9076a3fdaf8379)): ?>
<?php $attributes = $__attributesOriginala219c84179cedd61bd9076a3fdaf8379; ?>
<?php unset($__attributesOriginala219c84179cedd61bd9076a3fdaf8379); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala219c84179cedd61bd9076a3fdaf8379)): ?>
<?php $component = $__componentOriginala219c84179cedd61bd9076a3fdaf8379; ?>
<?php unset($__componentOriginala219c84179cedd61bd9076a3fdaf8379); ?>
<?php endif; ?>
    <script src="<?php echo e(asset('js/chart.umd.min.js')); ?>"></script>
    <script src="<?php echo e(asset('js/sweetalert2.min.js')); ?>"></script>
    <script defer src="<?php echo e(asset('js/alpine.min.js')); ?>"></script>
    <script defer src="<?php echo e(asset('js/mascot.js')); ?>"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body class="bg-[#fff8e7] antialiased">
    <?php if(auth()->check() && auth()->user()->role === 'student'): ?>
    <?php if (isset($component)) { $__componentOriginal9c9895f7fbf5679e527f730494859d8f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9c9895f7fbf5679e527f730494859d8f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.child-transition','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('child-transition'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9c9895f7fbf5679e527f730494859d8f)): ?>
<?php $attributes = $__attributesOriginal9c9895f7fbf5679e527f730494859d8f; ?>
<?php unset($__attributesOriginal9c9895f7fbf5679e527f730494859d8f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9c9895f7fbf5679e527f730494859d8f)): ?>
<?php $component = $__componentOriginal9c9895f7fbf5679e527f730494859d8f; ?>
<?php unset($__componentOriginal9c9895f7fbf5679e527f730494859d8f); ?>
<?php endif; ?>
    <?php endif; ?>
    <?php echo $__env->yieldContent('content'); ?>

    <?php if (isset($component)) { $__componentOriginal4d6bed2ebceb29e0a9932fbda627422a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4d6bed2ebceb29e0a9932fbda627422a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-loader','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-loader'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4d6bed2ebceb29e0a9932fbda627422a)): ?>
<?php $attributes = $__attributesOriginal4d6bed2ebceb29e0a9932fbda627422a; ?>
<?php unset($__attributesOriginal4d6bed2ebceb29e0a9932fbda627422a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4d6bed2ebceb29e0a9932fbda627422a)): ?>
<?php $component = $__componentOriginal4d6bed2ebceb29e0a9932fbda627422a; ?>
<?php unset($__componentOriginal4d6bed2ebceb29e0a9932fbda627422a); ?>
<?php endif; ?>
    <?php if (isset($component)) { $__componentOriginalb8b230e6993df4947ec1cd7beb492b6a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb8b230e6993df4947ec1cd7beb492b6a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.connection-status','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('connection-status'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb8b230e6993df4947ec1cd7beb492b6a)): ?>
<?php $attributes = $__attributesOriginalb8b230e6993df4947ec1cd7beb492b6a; ?>
<?php unset($__attributesOriginalb8b230e6993df4947ec1cd7beb492b6a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb8b230e6993df4947ec1cd7beb492b6a)): ?>
<?php $component = $__componentOriginalb8b230e6993df4947ec1cd7beb492b6a; ?>
<?php unset($__componentOriginalb8b230e6993df4947ec1cd7beb492b6a); ?>
<?php endif; ?>
    <?php if (isset($component)) { $__componentOriginal19737a51bfae7124ebbf6cd99387b232 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal19737a51bfae7124ebbf6cd99387b232 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.session-keep-alive','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('session-keep-alive'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal19737a51bfae7124ebbf6cd99387b232)): ?>
<?php $attributes = $__attributesOriginal19737a51bfae7124ebbf6cd99387b232; ?>
<?php unset($__attributesOriginal19737a51bfae7124ebbf6cd99387b232); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal19737a51bfae7124ebbf6cd99387b232)): ?>
<?php $component = $__componentOriginal19737a51bfae7124ebbf6cd99387b232; ?>
<?php unset($__componentOriginal19737a51bfae7124ebbf6cd99387b232); ?>
<?php endif; ?>

    <script src="<?php echo e(asset('js/effects.js')); ?>"></script>
    <?php echo $__env->yieldPushContent('scripts'); ?>
    <?php if(session('success')): ?>
    <script>
        Swal.fire({ icon: 'success', title: <?php echo json_encode(__('misc.success_title'), 15, 512) ?>, text: <?php echo json_encode(session('success'), 15, 512) ?>, timer: 3000, showConfirmButton: false });
    </script>
    <?php endif; ?>
    <?php if(session('info')): ?>
    <script>
        Swal.fire({ icon: 'info', title: <?php echo json_encode(__('misc.notice_title'), 15, 512) ?>, text: <?php echo json_encode(session('info'), 15, 512) ?>, timer: 3000, showConfirmButton: false });
    </script>
    <?php endif; ?>
    <?php if(session('error')): ?>
    <script>
        Swal.fire({ icon: 'info', title: <?php echo json_encode(__('misc.notice_title'), 15, 512) ?>, text: <?php echo json_encode(session('error'), 15, 512) ?> });
    </script>
    <?php endif; ?>
    <?php if($errors->any()): ?>
    <script>
        Swal.fire({ icon: 'error', title: <?php echo json_encode(__('misc.error_title'), 15, 512) ?>, text: <?php echo json_encode($errors->first(), 15, 512) ?>, timer: 5000, showConfirmButton: false });
    </script>
    <?php endif; ?>
</body>
</html>
<?php /**PATH C:\laragon\www\kidlink-laravel\resources\views/layouts/app.blade.php ENDPATH**/ ?>