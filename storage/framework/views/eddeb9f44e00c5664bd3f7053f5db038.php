<!DOCTYPE html>
<html lang="<?php echo e(app()->getLocale()); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>KidLink - <?php echo $__env->yieldContent('title', __('page.parent_dashboard')); ?></title>
    
    
    <link href="<?php echo e(asset('css/fonts.css')); ?>" rel="stylesheet">
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
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
    <style>
        /* Phones: wide tables scroll sideways inside their card instead of stretching the page */
        @media (max-width: 767px) {
            main div:has(> table) { overflow-x: auto; -webkit-overflow-scrolling: touch; }
            main table { min-width: 34rem; }
            .swal2-popup { width: 92% !important; }
        }
        button, a, select, input[type="checkbox"], input[type="radio"] { touch-action: manipulation; }
    </style>
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body class="bg-[#c5d1e4] font-sans antialiased">
    <div class="flex h-screen h-[100dvh] bg-[#c5d1e4]" x-data="{ navOpen: false }" @keydown.escape.window="navOpen = false">
        <?php echo $__env->make('components.parent_sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <div x-show="navOpen" x-cloak style="display:none;" @click="navOpen = false" class="fixed inset-0 bg-black/50 z-30 md:hidden" aria-hidden="true"></div>
        <div class="flex-1 flex flex-col overflow-hidden md:ml-64 min-w-0">
            <header class="bg-white shadow-sm border-b border-[#ebe8e1] px-4 md:px-6 py-3 md:py-4">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <button type="button" @click="navOpen = true" class="relative md:hidden shrink-0 w-10 h-10 -ml-1 rounded-lg border border-[#ebe8e1] text-[#182438] flex items-center justify-center" aria-label="Open menu">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                            <?php if (isset($component)) { $__componentOriginalac881d43daa35e07172aab3e71a1168b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalac881d43daa35e07172aab3e71a1168b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.announcement-badge','data' => ['dot' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('announcement-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['dot' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalac881d43daa35e07172aab3e71a1168b)): ?>
<?php $attributes = $__attributesOriginalac881d43daa35e07172aab3e71a1168b; ?>
<?php unset($__attributesOriginalac881d43daa35e07172aab3e71a1168b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalac881d43daa35e07172aab3e71a1168b)): ?>
<?php $component = $__componentOriginalac881d43daa35e07172aab3e71a1168b; ?>
<?php unset($__componentOriginalac881d43daa35e07172aab3e71a1168b); ?>
<?php endif; ?>
                        </button>
                        <h1 class="text-lg md:text-2xl font-bold text-[#182438] truncate"><?php echo $__env->yieldContent('page-title', __('page.dashboard')); ?></h1>
                    </div>
                    <div class="flex items-center gap-2 md:gap-4 shrink-0">
                        <?php if (isset($component)) { $__componentOriginal2c6f6a1f4acdf7871e5a6eed76d01a2e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2c6f6a1f4acdf7871e5a6eed76d01a2e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.lang_switcher','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('lang_switcher'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2c6f6a1f4acdf7871e5a6eed76d01a2e)): ?>
<?php $attributes = $__attributesOriginal2c6f6a1f4acdf7871e5a6eed76d01a2e; ?>
<?php unset($__attributesOriginal2c6f6a1f4acdf7871e5a6eed76d01a2e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2c6f6a1f4acdf7871e5a6eed76d01a2e)): ?>
<?php $component = $__componentOriginal2c6f6a1f4acdf7871e5a6eed76d01a2e; ?>
<?php unset($__componentOriginal2c6f6a1f4acdf7871e5a6eed76d01a2e); ?>
<?php endif; ?>
                        <span class="hidden lg:inline text-sm text-[#5f6873]"><?php echo e(__('header.welcome', ['name' => auth()->user()->full_name])); ?></span>
                        <?php echo $__env->make('components.child_mode', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <form method="POST" action="<?php echo e(route('logout')); ?>" class="inline">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="bg-[#f05d56] hover:bg-[#e04942] text-white px-3 md:px-4 py-2 rounded-lg text-sm transition"><?php echo e(__('header.logout')); ?></button>
                        </form>
                    </div>
                </div>
            </header>
            <main class="flex-1 overflow-y-auto p-4 md:p-6">
                <?php echo $__env->yieldContent('content'); ?>
            </main>
        </div>
    </div>
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

    <script src="<?php echo e(asset('js/ajax-filters.js')); ?>"></script>
    <?php echo $__env->yieldPushContent('scripts'); ?>
    <?php if(session('success')): ?>
    <script>Swal.fire({ icon: 'success', title: <?php echo json_encode(__('misc.success_title'), 15, 512) ?>, text: <?php echo json_encode(session('success'), 15, 512) ?>, timer: 3000, showConfirmButton: false });</script>
    <?php endif; ?>
    <?php if(session('error')): ?>
    <script>Swal.fire({ icon: 'error', title: <?php echo json_encode(__('misc.oops_title'), 15, 512) ?>, text: <?php echo json_encode(session('error'), 15, 512) ?> });</script>
    <?php endif; ?>
</body>
</html>
<?php /**PATH C:\laragon\www\kidlink-laravel\resources\views/layouts/parent.blade.php ENDPATH**/ ?>