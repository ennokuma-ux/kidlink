<?php $__env->startSection('title', __('landing.title')); ?>

<?php $__env->startPush('styles'); ?>
<style>
    html { scroll-behavior: smooth; }
    .menu-button.active span:nth-child(1) { transform: translateY(4px) rotate(45deg); }
    .menu-button.active span:nth-child(2) { opacity: 0; }
    .menu-button.active span:nth-child(3) { transform: translateY(-4px) rotate(-45deg); }
    .menu-button span { transition: all .2s ease; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<header class="navbar sticky top-0 z-20 h-[70px] bg-white border-b border-[#eeeeeb]">
    <div class="max-w-[1120px] h-full mx-auto flex items-center px-6">
        <a href="<?php echo e(url('/')); ?>" class="text-2xl font-extrabold tracking-tight whitespace-nowrap">
            <span style="color: #1b2738;">Kid</span><strong style="color: #ff8555;">Link</strong>
        </a>

        <nav class="hidden md:flex items-center gap-[30px] mx-auto">
            <a href="#features" class="text-[13.5px] font-medium hover:text-[#182438] transition" style="color: #5f6873;"><?php echo e(__('landing.nav_features')); ?></a>
            <a href="#students" class="text-[13.5px] font-medium hover:text-[#182438] transition" style="color: #5f6873;"><?php echo e(__('landing.nav_students')); ?></a>
            <a href="#teachers" class="text-[13.5px] font-medium hover:text-[#182438] transition" style="color: #5f6873;"><?php echo e(__('landing.nav_teachers')); ?></a>
            <a href="#parents" class="text-[13.5px] font-medium hover:text-[#182438] transition" style="color: #5f6873;"><?php echo e(__('landing.nav_parents')); ?></a>
            <a href="#about" class="text-[13.5px] font-medium hover:text-[#182438] transition" style="color: #5f6873;"><?php echo e(__('landing.nav_about')); ?></a>
        </nav>

        <div class="flex items-center gap-3 ml-auto md:ml-0">
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
            <a href="<?php echo e(route('login')); ?>" class="flex items-center justify-center h-[34px] px-4 rounded-full font-semibold text-white transition hover:bg-[#f47548]" style="background: #ff8555; font-size: 13px;"><?php echo e(__('landing.login')); ?></a>
        </div>

        <button class="menu-button md:hidden ml-auto flex flex-col gap-[3px] w-[27px] h-[27px] items-center justify-center bg-transparent border-0" id="menuButton">
            <span class="block w-4 h-[1.5px]" style="background: #182438;"></span>
            <span class="block w-4 h-[1.5px]" style="background: #182438;"></span>
            <span class="block w-4 h-[1.5px]" style="background: #182438;"></span>
        </button>
    </div>

    <div class="mobile-nav hidden flex-col md:hidden px-6 py-5 bg-white border-t border-[#eeeeeb]" id="mobileNav">
        <a href="#features" class="text-[16px] font-medium" style="color: #182438;"><?php echo e(__('landing.nav_features')); ?></a>
        <a href="#students" class="text-[16px] font-medium" style="color: #182438;"><?php echo e(__('landing.nav_students')); ?></a>
        <a href="#teachers" class="text-[16px] font-medium" style="color: #182438;"><?php echo e(__('landing.nav_teachers')); ?></a>
        <a href="#parents" class="text-[16px] font-medium" style="color: #182438;"><?php echo e(__('landing.nav_parents')); ?></a>
        <a href="#about" class="text-[16px] font-medium" style="color: #182438;"><?php echo e(__('landing.nav_about')); ?></a>
        <a href="<?php echo e(route('login')); ?>" class="text-[16px] font-medium text-white text-center rounded-full px-5 py-2.5" style="background: #ff8555;"><?php echo e(__('landing.login')); ?></a>
    </div>
</header>

<section class="relative flex items-center justify-center overflow-hidden min-h-[420px] px-5 py-[60px]" style="background: linear-gradient(125deg, #f9f3d5 0%, #e3f1f2 38%, #e3f2f4 65%, #f9e9ed 100%);">
    <div class="absolute w-[230px] h-[230px] rounded-full blur-[70px] opacity-20 pointer-events-none" style="background: #f5e5a6; left: -100px; top: 20px;"></div>
    <div class="absolute w-[230px] h-[230px] rounded-full blur-[70px] opacity-20 pointer-events-none" style="background: #b8dfe7; right: 5%; top: -120px;"></div>
    <div class="absolute w-[230px] h-[230px] rounded-full blur-[70px] opacity-20 pointer-events-none" style="background: #f0ccd6; right: -80px; bottom: -120px;"></div>

    <div class="relative z-[2] w-full max-w-[700px] px-5 flex flex-col items-center text-center">
        <div class="w-[10px] h-[10px] rounded-full mb-6" style="background: rgba(220, 211, 195, 0.55);"></div>

        <h1 class="font-extrabold tracking-tight leading-[1.05] text-[clamp(38px,5vw,52px)]" style="color: #182438;">
            <?php echo __('landing.hero'); ?>

        </h1>

        <p class="max-w-[520px] mt-6 text-[16px] leading-[1.75]" style="color: #5f6873;">
            <?php echo e(__('landing.hero_subtitle')); ?>

        </p>

        <a href="<?php echo e(route('login')); ?>" class="flex items-center justify-center h-[48px] min-w-[130px] px-[30px] mt-8 rounded-full text-white text-[16px] font-semibold transition hover:bg-[#f47548] hover:-translate-y-0.5" style="background: #ff8555; box-shadow: 0 4px 12px rgba(255,133,85,0.14);"><?php echo e(__('landing.get_started')); ?></a>

        <div class="mt-10 flex items-end justify-center gap-6 sm:gap-10">
            <?php if (isset($component)) { $__componentOriginal1ad13fbd679f096a735ed64294b0a7b9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1ad13fbd679f096a735ed64294b0a7b9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.mascot','data' => ['mascot' => 'kido','state' => 'happy','size' => 116,'bounce' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mascot'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['mascot' => 'kido','state' => 'happy','size' => 116,'bounce' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1ad13fbd679f096a735ed64294b0a7b9)): ?>
<?php $attributes = $__attributesOriginal1ad13fbd679f096a735ed64294b0a7b9; ?>
<?php unset($__attributesOriginal1ad13fbd679f096a735ed64294b0a7b9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1ad13fbd679f096a735ed64294b0a7b9)): ?>
<?php $component = $__componentOriginal1ad13fbd679f096a735ed64294b0a7b9; ?>
<?php unset($__componentOriginal1ad13fbd679f096a735ed64294b0a7b9); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginal1ad13fbd679f096a735ed64294b0a7b9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1ad13fbd679f096a735ed64294b0a7b9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.mascot','data' => ['mascot' => 'linka','state' => 'wave','size' => 116,'bounce' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mascot'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['mascot' => 'linka','state' => 'wave','size' => 116,'bounce' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1ad13fbd679f096a735ed64294b0a7b9)): ?>
<?php $attributes = $__attributesOriginal1ad13fbd679f096a735ed64294b0a7b9; ?>
<?php unset($__attributesOriginal1ad13fbd679f096a735ed64294b0a7b9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1ad13fbd679f096a735ed64294b0a7b9)): ?>
<?php $component = $__componentOriginal1ad13fbd679f096a735ed64294b0a7b9; ?>
<?php unset($__componentOriginal1ad13fbd679f096a735ed64294b0a7b9); ?>
<?php endif; ?>
        </div>
        <p class="mt-4 text-[14px] font-medium" style="color: #5f6873;"><?php echo e(__('mascot.landing_hello')); ?></p>
    </div>
</section>

<section class="py-[90px] px-5" id="features" style="background: #c5d1e4;">
    <div class="max-w-[640px] mx-auto text-center">
        <span class="block text-[13px] font-bold mb-4" style="color: #ff8555;"><?php echo e(__('landing.features_tag')); ?></span>
        <h2 class="text-[34px] font-extrabold tracking-tight leading-[1.15]" style="color: #182438;"><?php echo e(__('landing.features_title')); ?></h2>
        <p class="max-w-[480px] mx-auto mt-[18px] text-[15px] leading-[1.7]" style="color: #5f6873;">
            <?php echo e(__('landing.features_subtitle')); ?>

        </p>
    </div>

    <div class="max-w-[1120px] mx-auto mt-14 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
        <article class="bg-white border border-[#ebe8e1] rounded-[14px] p-[30px] min-h-[190px] shadow-sm">
            <div class="w-[44px] h-[44px] flex items-center justify-center rounded-[10px] mb-[18px] text-[18px] bg-[#fcf3d7]" style="color: #d9b348;"><i class="ico ico-sparkles text-[22px]" aria-hidden="true"></i></div>
            <h3 class="text-[17px] font-bold leading-[1.3]" style="color: #182438;"><?php echo e(__('landing.feature_gamified_title')); ?></h3>
            <p class="mt-2.5 text-[13.5px] leading-[1.65]" style="color: #5f6873;">
                <?php echo e(__('landing.feature_gamified_body')); ?>

            </p>
        </article>

        <article class="bg-white border border-[#ebe8e1] rounded-[14px] p-[30px] min-h-[190px] shadow-sm">
            <div class="w-[44px] h-[44px] flex items-center justify-center rounded-[10px] mb-[18px] text-[18px] bg-[#e1f0f8]" style="color: #5da6d0;">▥</div>
            <h3 class="text-[17px] font-bold leading-[1.3]" style="color: #182438;"><?php echo e(__('landing.feature_analytics_title')); ?></h3>
            <p class="mt-2.5 text-[13.5px] leading-[1.65]" style="color: #5f6873;">
                <?php echo e(__('landing.feature_analytics_body')); ?>

            </p>
        </article>

        <article class="bg-white border border-[#ebe8e1] rounded-[14px] p-[30px] min-h-[190px] shadow-sm">
            <div class="w-[44px] h-[44px] flex items-center justify-center rounded-[10px] mb-[18px] text-[18px] bg-[#f0e7fa]" style="color: #a16ed1;">▣</div>
            <h3 class="text-[17px] font-bold leading-[1.3]" style="color: #182438;"><?php echo e(__('landing.feature_expert_title')); ?></h3>
            <p class="mt-2.5 text-[13.5px] leading-[1.65]" style="color: #5f6873;">
                <?php echo e(__('landing.feature_expert_body')); ?>

            </p>
        </article>

        <article class="bg-white border border-[#ebe8e1] rounded-[14px] p-[30px] min-h-[190px] shadow-sm">
            <div class="w-[44px] h-[44px] flex items-center justify-center rounded-[10px] mb-[18px] text-[18px] bg-[#e2f4e8]" style="color: #62b57e;">♧</div>
            <h3 class="text-[17px] font-bold leading-[1.3]" style="color: #182438;"><?php echo e(__('landing.feature_roles_title')); ?></h3>
            <p class="mt-2.5 text-[13.5px] leading-[1.65]" style="color: #5f6873;">
                <?php echo e(__('landing.feature_roles_body')); ?>

            </p>
        </article>

        <article class="bg-white border border-[#ebe8e1] rounded-[14px] p-[30px] min-h-[190px] shadow-sm">
            <div class="w-[44px] h-[44px] flex items-center justify-center rounded-[10px] mb-[18px] text-[18px] bg-[#fde8ef]" style="color: #e78cae;">♡</div>
            <h3 class="text-[17px] font-bold leading-[1.3]" style="color: #182438;"><?php echo e(__('landing.feature_secure_title')); ?></h3>
            <p class="mt-2.5 text-[13.5px] leading-[1.65]" style="color: #5f6873;">
                <?php echo e(__('landing.feature_secure_body')); ?>

            </p>
        </article>

        <article class="bg-white border border-[#ebe8e1] rounded-[14px] p-[30px] min-h-[190px] shadow-sm">
            <div class="w-[44px] h-[44px] flex items-center justify-center rounded-[10px] mb-[18px] text-[18px] bg-[#fff0e6]" style="color: #e69a69;">♧</div>
            <h3 class="text-[17px] font-bold leading-[1.3]" style="color: #182438;"><?php echo e(__('landing.feature_updates_title')); ?></h3>
            <p class="mt-2.5 text-[13.5px] leading-[1.65]" style="color: #5f6873;">
                <?php echo e(__('landing.feature_updates_body')); ?>

            </p>
        </article>
    </div>
</section>

<section class="py-[90px] px-5" id="about" style="background: #c5d1e4;">
    <div class="max-w-[640px] mx-auto text-center mb-16">
        <span class="block text-[13px] font-bold mb-4" style="color: #182438;"><?php echo e(__('landing.about_tag')); ?></span>
        <h2 class="text-[34px] font-extrabold tracking-tight leading-[1.15]" style="color: #182438;"><?php echo e(__('landing.about_title')); ?></h2>
        <p class="max-w-[480px] mx-auto mt-[18px] text-[15px] leading-[1.7]" style="color: #5f6873;">
            <?php echo e(__('landing.about_subtitle')); ?>

        </p>
    </div>

    <div class="max-w-[1080px] mx-auto grid grid-cols-1 md:grid-cols-2 gap-[64px] items-center mb-14" id="students">
        <div class="w-full h-[200px] rounded-[16px]" style="background: #f8edca;"></div>
        <div>
            <span class="inline-flex px-3 py-1.5 rounded-full text-[12px] font-bold mb-4" style="color: #db9751; background: #fff0cf;"><?php echo e(__('landing.students_tag')); ?></span>
            <h3 class="text-[27px] font-extrabold tracking-tight leading-[1.25]" style="color: #182438;"><?php echo e(__('landing.students_title')); ?></h3>
            <p class="mt-4 max-w-[380px] text-[15px] leading-[1.75]" style="color: #5f6873;">
                <?php echo e(__('landing.students_body')); ?>

            </p>
        </div>
    </div>

    <div class="max-w-[1080px] mx-auto grid grid-cols-1 md:grid-cols-2 gap-[64px] items-center mb-14" id="teachers">
        <div class="md:order-2 w-full h-[200px] rounded-[16px]" style="background: #d3e4eb;"></div>
        <div class="md:order-1">
            <span class="inline-flex px-3 py-1.5 rounded-full text-[12px] font-bold mb-4" style="color: #5798bd; background: #e1f0f7;"><?php echo e(__('landing.teachers_tag')); ?></span>
            <h3 class="text-[27px] font-extrabold tracking-tight leading-[1.25]" style="color: #182438;"><?php echo e(__('landing.teachers_title')); ?></h3>
            <p class="mt-4 max-w-[380px] text-[15px] leading-[1.75]" style="color: #5f6873;">
                <?php echo e(__('landing.teachers_body')); ?>

            </p>
        </div>
    </div>

    <div class="max-w-[1080px] mx-auto grid grid-cols-1 md:grid-cols-2 gap-[64px] items-center" id="parents">
        <div class="w-full h-[200px] rounded-[16px]" style="background: #d6ead8;"></div>
        <div>
            <span class="inline-flex px-3 py-1.5 rounded-full text-[12px] font-bold mb-4" style="color: #60a46c; background: #e1f1e3;"><?php echo e(__('landing.parents_tag')); ?></span>
            <h3 class="text-[27px] font-extrabold tracking-tight leading-[1.25]" style="color: #182438;"><?php echo e(__('landing.parents_title')); ?></h3>
            <p class="mt-4 max-w-[380px] text-[15px] leading-[1.75]" style="color: #5f6873;">
                <?php echo e(__('landing.parents_body')); ?>

            </p>
        </div>
    </div>
</section>

<section class="relative flex items-center justify-center overflow-hidden min-h-[280px] px-5 py-[80px]" style="background: linear-gradient(120deg, #f7f4da 0%, #e3f2f3 45%, #f7edef 100%);">
    <div class="absolute w-[230px] h-[230px] rounded-full blur-[70px] opacity-20 pointer-events-none" style="background: #f3dfa0; left: -100px; bottom: -120px;"></div>
    <div class="absolute w-[230px] h-[230px] rounded-full blur-[70px] opacity-20 pointer-events-none" style="background: #b8dfe5; left: 45%; top: -150px;"></div>
    <div class="absolute w-[230px] h-[230px] rounded-full blur-[70px] opacity-20 pointer-events-none" style="background: #efcbd5; right: -100px; bottom: -110px;"></div>

    <div class="relative z-[2] text-center">
        <h2 class="text-[34px] font-extrabold tracking-tight leading-[1.15]" style="color: #182438;"><?php echo e(__('landing.cta_title')); ?></h2>
        <p class="max-w-[420px] mx-auto mt-[18px] text-[15px] leading-[1.75]" style="color: #5f6873;">
            <?php echo e(__('landing.cta_subtitle')); ?>

        </p>
        <a href="<?php echo e(route('login')); ?>" class="flex items-center justify-center h-[48px] min-w-[130px] px-[30px] mt-[30px] rounded-full text-white text-[16px] font-semibold transition hover:bg-[#f47548] hover:-translate-y-0.5" style="background: #ff8555; box-shadow: 0 4px 12px rgba(255,133,85,0.14);"><?php echo e(__('landing.get_started')); ?></a>
    </div>
</section>

<footer class="bg-white border-t border-[#eeeeeb]">
    <div class="max-w-[1120px] mx-auto px-6 min-h-[72px] flex items-center justify-between">
        <a href="<?php echo e(url('/')); ?>" class="text-[17px] font-extrabold tracking-tight">
            <span style="color: #1b2738;">Kid</span><strong style="color: #ff8555;">Link</strong>
        </a>
        <p class="text-[13.5px]" style="color: #a0a5aa;"><?php echo e(__('landing.footer_tagline', ['year' => date('Y')])); ?></p>
    </div>
</footer>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    const menuButton = document.getElementById('menuButton');
    const mobileNav = document.getElementById('mobileNav');

    menuButton.addEventListener('click', () => {
        mobileNav.classList.toggle('hidden');
        menuButton.classList.toggle('active');
    });

    document.querySelectorAll('.mobile-nav a').forEach(link => {
        link.addEventListener('click', () => {
            mobileNav.classList.add('hidden');
            menuButton.classList.remove('active');
        });
    });
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kidlink-laravel\resources\views/landing.blade.php ENDPATH**/ ?>