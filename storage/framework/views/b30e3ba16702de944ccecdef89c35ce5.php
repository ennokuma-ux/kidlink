<?php $__env->startSection('title', 'Where Kids Learn, Teachers Manage, & Parents Connect'); ?>

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
            <a href="#features" class="text-[13.5px] font-medium hover:text-[#182438] transition" style="color: #5f6873;">Features</a>
            <a href="#students" class="text-[13.5px] font-medium hover:text-[#182438] transition" style="color: #5f6873;">For Students</a>
            <a href="#teachers" class="text-[13.5px] font-medium hover:text-[#182438] transition" style="color: #5f6873;">For Teachers</a>
            <a href="#parents" class="text-[13.5px] font-medium hover:text-[#182438] transition" style="color: #5f6873;">For Parents</a>
            <a href="#about" class="text-[13.5px] font-medium hover:text-[#182438] transition" style="color: #5f6873;">About</a>
        </nav>

        <div class="hidden md:flex items-center gap-4">
            <a href="<?php echo e(route('login')); ?>" class="flex items-center justify-center h-[34px] px-4 rounded-full font-semibold text-white transition hover:bg-[#f47548]" style="background: #ff8555; font-size: 13px;">Log In</a>
        </div>

        <button class="menu-button md:hidden ml-auto flex flex-col gap-[3px] w-[27px] h-[27px] items-center justify-center bg-transparent border-0" id="menuButton">
            <span class="block w-4 h-[1.5px]" style="background: #182438;"></span>
            <span class="block w-4 h-[1.5px]" style="background: #182438;"></span>
            <span class="block w-4 h-[1.5px]" style="background: #182438;"></span>
        </button>
    </div>

    <div class="mobile-nav hidden flex-col md:hidden px-6 py-5 bg-white border-t border-[#eeeeeb]" id="mobileNav">
        <a href="#features" class="text-[16px] font-medium" style="color: #182438;">Features</a>
        <a href="#students" class="text-[16px] font-medium" style="color: #182438;">For Students</a>
        <a href="#teachers" class="text-[16px] font-medium" style="color: #182438;">For Teachers</a>
        <a href="#parents" class="text-[16px] font-medium" style="color: #182438;">For Parents</a>
        <a href="#about" class="text-[16px] font-medium" style="color: #182438;">About</a>
        <a href="<?php echo e(route('login')); ?>" class="text-[16px] font-medium text-white text-center rounded-full px-5 py-2.5" style="background: #ff8555;">Log In</a>
    </div>
</header>

<section class="relative flex items-center justify-center overflow-hidden min-h-[420px] px-5 py-[60px]" style="background: linear-gradient(125deg, #f9f3d5 0%, #e3f1f2 38%, #e3f2f4 65%, #f9e9ed 100%);">
    <div class="absolute w-[230px] h-[230px] rounded-full blur-[70px] opacity-20 pointer-events-none" style="background: #f5e5a6; left: -100px; top: 20px;"></div>
    <div class="absolute w-[230px] h-[230px] rounded-full blur-[70px] opacity-20 pointer-events-none" style="background: #b8dfe7; right: 5%; top: -120px;"></div>
    <div class="absolute w-[230px] h-[230px] rounded-full blur-[70px] opacity-20 pointer-events-none" style="background: #f0ccd6; right: -80px; bottom: -120px;"></div>

    <div class="relative z-[2] w-full max-w-[700px] px-5 flex flex-col items-center text-center">
        <div class="w-[10px] h-[10px] rounded-full mb-6" style="background: rgba(220, 211, 195, 0.55);"></div>

        <h1 class="font-extrabold tracking-tight leading-[1.05] text-[clamp(38px,5vw,52px)]" style="color: #182438;">
            Where Kids <span class="text-[#f05d56]">Learn</span>,<br>
            Teachers <span class="text-[#258cc5]">Manage</span>, &<br>
            Parents <span class="text-[#68b82f]">Connect</span>
        </h1>

        <p class="max-w-[520px] mt-6 text-[16px] leading-[1.75]" style="color: #5f6873;">
            KidLink is a web-application daycare center management system with
            gamified learning for students, descriptive analytics for teachers,
            and an expert system.
        </p>

        <a href="<?php echo e(route('login')); ?>" class="flex items-center justify-center h-[48px] min-w-[130px] px-[30px] mt-8 rounded-full text-white text-[16px] font-semibold transition hover:bg-[#f47548] hover:-translate-y-0.5" style="background: #ff8555; box-shadow: 0 4px 12px rgba(255,133,85,0.14);">Get Started</a>
    </div>
</section>

<section class="py-[90px] px-5" id="features" style="background: #faf9f6;">
    <div class="max-w-[640px] mx-auto text-center">
        <span class="block text-[13px] font-bold mb-4" style="color: #ff8555;">FEATURES</span>
        <h2 class="text-[34px] font-extrabold tracking-tight leading-[1.15]" style="color: #182438;">Everything Your Daycare Needs</h2>
        <p class="max-w-[480px] mx-auto mt-[18px] text-[15px] leading-[1.7]" style="color: #5f6873;">
            A complete management ecosystem with powerful tools and
            intelligent decision support.
        </p>
    </div>

    <div class="max-w-[1120px] mx-auto mt-14 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
        <article class="bg-white border border-[#ebe8e1] rounded-[14px] p-[30px] min-h-[190px] shadow-sm">
            <div class="w-[44px] h-[44px] flex items-center justify-center rounded-[10px] mb-[18px] text-[18px] bg-[#fcf3d7]" style="color: #d9b348;">✦</div>
            <h3 class="text-[17px] font-bold leading-[1.3]" style="color: #182438;">Gamified Learning</h3>
            <p class="mt-2.5 text-[13.5px] leading-[1.65]" style="color: #5f6873;">
                Interactive, game-based activities that make learning fun
                and engaging for children.
            </p>
        </article>

        <article class="bg-white border border-[#ebe8e1] rounded-[14px] p-[30px] min-h-[190px] shadow-sm">
            <div class="w-[44px] h-[44px] flex items-center justify-center rounded-[10px] mb-[18px] text-[18px] bg-[#e1f0f8]" style="color: #5da6d0;">▥</div>
            <h3 class="text-[17px] font-bold leading-[1.3]" style="color: #182438;">Descriptive Analytics</h3>
            <p class="mt-2.5 text-[13.5px] leading-[1.65]" style="color: #5f6873;">
                Comprehensive charts and reports that give teachers and
                parents a clearer picture of student progress.
            </p>
        </article>

        <article class="bg-white border border-[#ebe8e1] rounded-[14px] p-[30px] min-h-[190px] shadow-sm">
            <div class="w-[44px] h-[44px] flex items-center justify-center rounded-[10px] mb-[18px] text-[18px] bg-[#f0e7fa]" style="color: #a16ed1;">▣</div>
            <h3 class="text-[17px] font-bold leading-[1.3]" style="color: #182438;">Expert System</h3>
            <p class="mt-2.5 text-[13.5px] leading-[1.65]" style="color: #5f6873;">
                AI-powered recommendations and insights for daycare
                management, helping teachers make informed decisions.
            </p>
        </article>

        <article class="bg-white border border-[#ebe8e1] rounded-[14px] p-[30px] min-h-[190px] shadow-sm">
            <div class="w-[44px] h-[44px] flex items-center justify-center rounded-[10px] mb-[18px] text-[18px] bg-[#e2f4e8]" style="color: #62b57e;">♧</div>
            <h3 class="text-[17px] font-bold leading-[1.3]" style="color: #182438;">Multi-Role Access</h3>
            <p class="mt-2.5 text-[13.5px] leading-[1.65]" style="color: #5f6873;">
                Tailored experiences for students, teachers, and parents —
                keeping everyone connected.
            </p>
        </article>

        <article class="bg-white border border-[#ebe8e1] rounded-[14px] p-[30px] min-h-[190px] shadow-sm">
            <div class="w-[44px] h-[44px] flex items-center justify-center rounded-[10px] mb-[18px] text-[18px] bg-[#fde8ef]" style="color: #e78cae;">♡</div>
            <h3 class="text-[17px] font-bold leading-[1.3]" style="color: #182438;">Secure &amp; Reliable</h3>
            <p class="mt-2.5 text-[13.5px] leading-[1.65]" style="color: #5f6873;">
                Built with PHP and modern security practices to keep all
                daycare information safe.
            </p>
        </article>

        <article class="bg-white border border-[#ebe8e1] rounded-[14px] p-[30px] min-h-[190px] shadow-sm">
            <div class="w-[44px] h-[44px] flex items-center justify-center rounded-[10px] mb-[18px] text-[18px] bg-[#fff0e6]" style="color: #e69a69;">♧</div>
            <h3 class="text-[17px] font-bold leading-[1.3]" style="color: #182438;">Real-Time Updates</h3>
            <p class="mt-2.5 text-[13.5px] leading-[1.65]" style="color: #5f6873;">
                Instant notifications keep everyone informed about important
                daycare activities and updates.
            </p>
        </article>
    </div>
</section>

<section class="py-[90px] px-5" id="about" style="background: #f8f6f1;">
    <div class="max-w-[640px] mx-auto text-center mb-16">
        <span class="block text-[13px] font-bold mb-4" style="color: #182438;">KIDLINK</span>
        <h2 class="text-[34px] font-extrabold tracking-tight leading-[1.15]" style="color: #182438;">Tailored for Everyone</h2>
        <p class="max-w-[480px] mx-auto mt-[18px] text-[15px] leading-[1.7]" style="color: #5f6873;">
            Three unique experiences designed for students, teachers, and parents.
        </p>
    </div>

    <div class="max-w-[1080px] mx-auto grid grid-cols-1 md:grid-cols-2 gap-[64px] items-center mb-14" id="students">
        <div class="w-full h-[200px] rounded-[16px]" style="background: #f8edca;"></div>
        <div>
            <span class="inline-flex px-3 py-1.5 rounded-full text-[12px] font-bold mb-4" style="color: #db9751; background: #fff0cf;">FOR STUDENTS</span>
            <h3 class="text-[27px] font-extrabold tracking-tight leading-[1.25]" style="color: #182438;">Learn Through Play</h3>
            <p class="mt-4 max-w-[380px] text-[15px] leading-[1.75]" style="color: #5f6873;">
                A gamified learning experience. Children earn points, unlock
                achievements, and progress through interactive educational
                activities — all while having fun.
            </p>
        </div>
    </div>

    <div class="max-w-[1080px] mx-auto grid grid-cols-1 md:grid-cols-2 gap-[64px] items-center mb-14" id="teachers">
        <div class="md:order-2 w-full h-[200px] rounded-[16px]" style="background: #d3e4eb;"></div>
        <div class="md:order-1">
            <span class="inline-flex px-3 py-1.5 rounded-full text-[12px] font-bold mb-4" style="color: #5798bd; background: #e1f0f7;">FOR TEACHERS</span>
            <h3 class="text-[27px] font-extrabold tracking-tight leading-[1.25]" style="color: #182438;">Manage &amp; Analyze</h3>
            <p class="mt-4 max-w-[380px] text-[15px] leading-[1.75]" style="color: #5f6873;">
                A powerful management dashboard with descriptive analytics
                and charts. Digitized class records, monitoring, student
                progress, schedules, and expert system support for data-driven
                decisions.
            </p>
        </div>
    </div>

    <div class="max-w-[1080px] mx-auto grid grid-cols-1 md:grid-cols-2 gap-[64px] items-center" id="parents">
        <div class="w-full h-[200px] rounded-[16px]" style="background: #d6ead8;"></div>
        <div>
            <span class="inline-flex px-3 py-1.5 rounded-full text-[12px] font-bold mb-4" style="color: #60a46c; background: #e1f1e3;">FOR PARENTS</span>
            <h3 class="text-[27px] font-extrabold tracking-tight leading-[1.25]" style="color: #182438;">Stay Connected</h3>
            <p class="mt-4 max-w-[380px] text-[15px] leading-[1.75]" style="color: #5f6873;">
                View your child's learning progress, track activities, and
                stay informed about important daycare updates. Get notified
                about milestones and stay engaged with your child's learning
                journey.
            </p>
        </div>
    </div>
</section>

<section class="relative flex items-center justify-center overflow-hidden min-h-[280px] px-5 py-[80px]" style="background: linear-gradient(120deg, #f7f4da 0%, #e3f2f3 45%, #f7edef 100%);">
    <div class="absolute w-[230px] h-[230px] rounded-full blur-[70px] opacity-20 pointer-events-none" style="background: #f3dfa0; left: -100px; bottom: -120px;"></div>
    <div class="absolute w-[230px] h-[230px] rounded-full blur-[70px] opacity-20 pointer-events-none" style="background: #b8dfe5; left: 45%; top: -150px;"></div>
    <div class="absolute w-[230px] h-[230px] rounded-full blur-[70px] opacity-20 pointer-events-none" style="background: #efcbd5; right: -100px; bottom: -110px;"></div>

    <div class="relative z-[2] text-center">
        <h2 class="text-[34px] font-extrabold tracking-tight leading-[1.15]" style="color: #182438;">Ready to Learn?</h2>
        <p class="max-w-[420px] mx-auto mt-[18px] text-[15px] leading-[1.75]" style="color: #5f6873;">
            KidLink brings together guided learning and powerful
            systems into one platform.
        </p>
        <a href="<?php echo e(route('login')); ?>" class="flex items-center justify-center h-[48px] min-w-[130px] px-[30px] mt-[30px] rounded-full text-white text-[16px] font-semibold transition hover:bg-[#f47548] hover:-translate-y-0.5" style="background: #ff8555; box-shadow: 0 4px 12px rgba(255,133,85,0.14);">Get Started</a>
    </div>
</section>

<footer class="bg-white border-t border-[#eeeeeb]">
    <div class="max-w-[1120px] mx-auto px-6 min-h-[72px] flex items-center justify-between">
        <a href="<?php echo e(url('/')); ?>" class="text-[17px] font-extrabold tracking-tight">
            <span style="color: #1b2738;">Kid</span><strong style="color: #ff8555;">Link</strong>
        </a>
        <p class="text-[13.5px]" style="color: #a0a5aa;">© <?php echo e(date('Y')); ?> KidLink — The Future</p>
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