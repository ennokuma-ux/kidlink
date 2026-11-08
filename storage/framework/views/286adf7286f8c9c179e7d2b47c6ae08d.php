<?php $__env->startSection('title', __('analytics.title_admin')); ?>
<?php $__env->startSection('page-title', __('analytics.page_title_admin')); ?>

<?php $__env->startSection('content'); ?>
<?php if (isset($component)) { $__componentOriginalf60238e076ffb0fa836ff7a63be06ac9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf60238e076ffb0fa836ff7a63be06ac9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics-scope-select','data' => ['route' => 'admin.analytics.index','students' => $allStudents,'sections' => $availableSections]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics-scope-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['route' => 'admin.analytics.index','students' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($allStudents),'sections' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($availableSections)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf60238e076ffb0fa836ff7a63be06ac9)): ?>
<?php $attributes = $__attributesOriginalf60238e076ffb0fa836ff7a63be06ac9; ?>
<?php unset($__attributesOriginalf60238e076ffb0fa836ff7a63be06ac9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf60238e076ffb0fa836ff7a63be06ac9)): ?>
<?php $component = $__componentOriginalf60238e076ffb0fa836ff7a63be06ac9; ?>
<?php unset($__componentOriginalf60238e076ffb0fa836ff7a63be06ac9); ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kidlink-laravel\resources\views/admin/analytics-select.blade.php ENDPATH**/ ?>