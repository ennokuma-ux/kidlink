

<?php $__env->startSection('title', __('student.home_title')); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen" style="background: linear-gradient(180deg, #fff8e7 0%, #b6c5de 100%);">
    <div class="container mx-auto px-4 py-6 max-w-6xl">
        <!-- Header -->
        <nav class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-orange-500 rounded-full flex items-center justify-center shadow-md">
                    <span class="text-white font-bold text-2xl">K</span>
                </div>
                <span class="text-2xl font-bold text-gray-800">Kid<span class="text-orange-500">Link</span></span>
            </div>
            <div class="flex items-center gap-3">
                <?php if(session('parent_user_id')): ?>
                <form method="POST" action="<?php echo e(route('child-mode.exit')); ?>" class="hidden sm:block">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="flex items-center gap-2 bg-white border border-gray-200 hover:border-orange-300 text-gray-600 px-3 py-2 rounded-lg text-sm font-semibold transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <?php echo e(__('student.exit')); ?>

                    </button>
                </form>
                <?php endif; ?>
                <span class="text-sm text-gray-500 hidden md:inline"><?php echo e(__('student.welcome', ['name' => $student->first_name])); ?></span>
                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                    <button @click="open = !open" class="w-10 h-10 bg-orange-500 rounded-full flex items-center justify-center text-white font-bold shadow">
                        <?php echo e(strtoupper(substr($student->first_name, 0, 1))); ?>

                    </button>
                    <div x-show="open" x-transition class="absolute right-0 mt-2 w-52 bg-white rounded-xl shadow-xl z-50 py-2" style="display: none;">
                        <div class="px-4 py-2 border-b border-gray-100">
                            <p class="text-sm font-semibold text-gray-700"><?php echo e($student->first_name); ?> <?php echo e($student->last_name); ?></p>
                            <p class="text-xs text-gray-400"><?php echo e(__('student.role_student')); ?></p>
                        </div>
                        <?php if(session('parent_user_id')): ?>
                        <form method="POST" action="<?php echo e(route('child-mode.exit')); ?>">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="w-full text-left px-4 py-3 text-sm text-orange-600 hover:bg-orange-50 flex items-center gap-2 font-semibold">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                <?php echo e(__('student.exit_child_mode')); ?>

                            </button>
                        </form>
                        <?php else: ?>
                        <form method="POST" action="<?php echo e(route('logout')); ?>">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="w-full text-left px-4 py-3 text-sm text-[#e8454a] hover:bg-[#fff1f1] flex items-center gap-2 font-semibold">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                <?php echo e(__('student.logout')); ?>

                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Welcome Card -->
        <div class="bg-white rounded-2xl shadow-sm p-6 mb-8 border border-gray-100">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-full flex items-center justify-center text-white font-bold text-2xl shadow-lg" style="background: linear-gradient(135deg, #1982c4, #ff8555);">
                    <?php echo e(strtoupper(substr($student->first_name, 0, 1))); ?><?php echo e(strtoupper(substr($student->last_name, 0, 1))); ?>

                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-800"><?php echo e($student->first_name); ?> <?php echo e($student->last_name); ?></h2>
                    <p class="text-gray-500"><?php echo e(__('student.age')); ?> <?php echo e($student->age); ?> <?php echo e(__('student.years_word')); ?> | <?php echo e(__('student.class_label')); ?> <?php echo e($student->class_name ?? 'N/A'); ?> | <?php echo e(__('student.hand_label')); ?> <?php echo e($student->handLabel()); ?></p>
                </div>
            </div>
        </div>

        <!-- Stats Row -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8" x-data="{
                stats: { total_points: <?php echo e($totalPoints); ?>, games_played: <?php echo e($totalGames); ?>, streak: <?php echo e($streak); ?>, avg_score: <?php echo e($avgScore); ?> },
                async refresh() {
                    try {
                        const res = await fetch('/student/stats', { headers: { 'Accept': 'application/json' } });
                        if (res.ok) this.stats = await res.json();
                    } catch (e) {
                        // keep the last known values if the refresh fails
                    }
                },
                init() {
                    setInterval(() => this.refresh(), 10000);
                    window.addEventListener('focus', () => this.refresh());
                    document.addEventListener('visibilitychange', () => { if (!document.hidden) this.refresh(); });
                }
            }">
            <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-orange-500">
                <p class="text-gray-400 text-xs font-semibold uppercase tracking-wide"><?php echo e(__('student.stat_total_points')); ?></p>
                <p class="text-3xl font-bold text-gray-800 mt-1" style="font-family: 'Fredoka One', cursive;" x-text="stats.total_points"><?php echo e($totalPoints); ?></p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-[#1982c4]">
                <p class="text-gray-400 text-xs font-semibold uppercase tracking-wide"><?php echo e(__('student.stat_games_played')); ?></p>
                <p class="text-3xl font-bold text-[#1982c4] mt-1" style="font-family: 'Fredoka One', cursive;" x-text="stats.games_played"><?php echo e($totalGames); ?></p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-[#8ac926]">
                <p class="text-gray-400 text-xs font-semibold uppercase tracking-wide"><?php echo e(__('student.stat_win_streak')); ?></p>
                <p class="text-3xl font-bold text-[#8ac926] mt-1" style="font-family: 'Fredoka One', cursive;" x-text="stats.streak"><?php echo e($streak); ?></p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-[#ffca3a]">
                <p class="text-gray-400 text-xs font-semibold uppercase tracking-wide"><?php echo e(__('student.stat_avg_score')); ?></p>
                <p class="text-3xl font-bold text-[#e6b020] mt-1" style="font-family: 'Fredoka One', cursive;" x-text="stats.avg_score + '%'"><?php echo e($avgScore); ?>%</p>
            </div>
        </div>

        <!-- All Games Grid -->
        <div class="flex items-center gap-3 mb-5">
            <h2 class="text-xl font-bold text-gray-800" style="font-family: 'Fredoka One', cursive;"><?php echo e(__('student.my_games')); ?></h2>
            <span class="h-1.5 flex-1 rounded-full bg-gradient-to-r from-orange-200 via-orange-100 to-transparent"></span>
        </div>
        <?php if(count($games) === 0): ?>
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-10 text-center mb-10">
            <p class="text-lg font-bold text-gray-800" style="font-family: 'Fredoka One', cursive;"><?php echo e(__('No games yet!')); ?></p>
            <p class="text-sm text-gray-500 mt-1"><?php echo e(__('Your teacher has not picked any games for you yet. Check back soon.')); ?></p>
        </div>
        <?php endif; ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-10">
            <?php $__currentLoopData = $games; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $game): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="group relative bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-200 flex flex-col">
                <a href="<?php echo e(route('student.games.' . $game['slug'] . '.stage-select')); ?>" class="block flex-1">
                    <!-- Banner -->
                    <div class="relative h-32 flex items-center justify-center overflow-hidden" style="background: <?php echo e($game['gradient']); ?>;">
                        <div class="absolute inset-0 opacity-50" style="background: radial-gradient(circle at 30% 15%, rgba(255,255,255,0.55), transparent 60%);"></div>
                        <div class="absolute -bottom-8 -left-6 w-24 h-24 rounded-full bg-white/15"></div>
                        <img src="<?php echo e(asset('storage/img/'.$game['img'])); ?>" alt="<?php echo e($game['name']); ?>"
                             class="relative w-20 h-20 object-contain drop-shadow-xl transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-3">
                        <?php if($game['played'] > 0): ?>
                        <span class="absolute top-3 right-3 inline-flex items-center gap-1 bg-white/95 rounded-full px-2.5 py-1 text-xs font-bold text-gray-700 shadow-sm"><i class="ico ico-star text-[#ffca3a]" aria-hidden="true"></i> <?php echo e(number_format($game['points'])); ?></span>
                        <?php else: ?>
                        <span class="absolute top-3 right-3 inline-flex items-center rounded-full bg-white/95 px-2.5 py-1 text-[11px] font-extrabold uppercase tracking-wide text-orange-500 shadow-sm"><?php echo e(__('student.new_badge')); ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Body -->
                    <div class="p-5 pb-3">
                        <div class="flex items-start justify-between gap-2">
                            <h3 class="font-bold text-base text-gray-800" style="font-family: 'Fredoka One', cursive;"><?php echo e($game['name']); ?></h3>
                            <?php if($game['played'] > 0): ?>
                            <span class="shrink-0 rounded-full bg-gray-50 px-2 py-0.5 text-[11px] font-bold text-gray-400"><?php echo e($game['played']); ?>x</span>
                            <?php endif; ?>
                        </div>
                        <p class="text-gray-400 text-xs mt-1 leading-relaxed"><?php echo e($game['desc']); ?></p>

                        <?php if($game['played'] > 0): ?>
                        <div class="mt-4">
                            <div class="flex items-center justify-between text-xs mb-1.5">
                                <span class="text-gray-400 font-semibold"><?php echo e(__('student.level')); ?> <?php echo e($game['highestLevel']); ?>/8</span>
                                <span class="font-bold text-gray-600"><?php echo e(number_format($game['points'])); ?> <?php echo e(__('student.pts')); ?></span>
                            </div>
                            <div class="flex gap-1">
                                <?php for($i = 1; $i <= 8; $i++): ?>
                                <span class="h-1.5 flex-1 rounded-full" style="background: <?php echo e($i <= $game['highestLevel'] ? $game['gradient'] : '#eef1f5'); ?>;"></span>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <?php else: ?>
                        <p class="text-gray-300 text-xs mt-4 font-semibold"><?php echo e(__('student.not_played')); ?></p>
                        <?php endif; ?>
                    </div>
                </a>

                <!-- Play / Continue -->
                <div class="px-5 pb-5 pt-1">
                    <a href="<?php echo e(route('student.games.' . $game['slug'] . '.stage-select')); ?>"
                       class="flex items-center justify-center gap-2 w-full py-2.5 rounded-2xl text-white text-sm font-bold shadow-md transition hover:shadow-lg hover:brightness-105 active:scale-[0.98]"
                       style="background: <?php echo e($game['gradient']); ?>;">
                        <?php echo e($game['played'] > 0 ? __('student.continue') : __('student.play')); ?>

                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </a>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <!-- Recent Activity -->
        <?php if($recentActivity->count() > 0): ?>
        <h2 class="text-xl font-bold text-gray-800 mb-5" style="font-family: 'Fredoka One', cursive;"><?php echo e(__('student.recent_activity')); ?></h2>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <?php $__currentLoopData = $recentActivity; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="flex items-center gap-4 px-5 py-3 <?php echo e(!$loop->last ? 'border-b border-gray-50' : ''); ?>">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg" style="background: <?php echo e(match($activity->game_type) {
                    'shapes' => '#fee2e2',
                    'colors' => '#ecf7d6',
                    'letters' => '#fff7ed',
                    'numbers' => '#d6eaf7',
                    'animals' => '#fef9c3',
                    default => '#f3f4f6',
                }); ?>">
                    <?php echo match($activity->game_type) {
                        'shapes' => '<img src="' . asset('assets/icons/calendar.svg') . '" alt="" class="h-6 w-6">',
                        'colors' => '<img src="' . asset('assets/icons/pencil.svg') . '" alt="" class="h-6 w-6">',
                        'letters' => '<img src="' . asset('assets/icons/language.svg') . '" alt="" class="h-6 w-6">',
                        'numbers' => '<img src="' . asset('assets/icons/clipboard.svg') . '" alt="" class="h-6 w-6">',
                        'animals' => '<img src="' . asset('assets/icons/people.svg') . '" alt="" class="h-6 w-6">',
                        default => '<i class="ico ico-game-controller" aria-hidden="true"></i>',
                    }; ?>

                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-gray-700 capitalize"><?php echo e($activity->game_type); ?> - <?php echo e(__('student.level')); ?> <?php echo e($activity->level); ?></p>
                    <p class="text-xs text-gray-400"><?php echo e($activity->created_at->diffForHumans()); ?></p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-bold text-[#6fa51c]">+<?php echo e($activity->points_earned); ?> <?php echo e(__('student.pts')); ?></p>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php if(session('parent_user_id')): ?>
<form method="POST" action="<?php echo e(route('child-mode.exit')); ?>" class="sm:hidden fixed bottom-6 left-1/2 -translate-x-1/2 z-50">
    <?php echo csrf_field(); ?>
    <button type="submit" class="flex items-center gap-2 bg-white border border-gray-200 shadow-lg text-gray-600 px-6 py-3 rounded-full text-sm font-bold hover:bg-gray-50 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
        <?php echo e(__('student.exit_child_mode')); ?>

    </button>
</form>
<?php endif; ?>
<?php if (isset($component)) { $__componentOriginal1ad13fbd679f096a735ed64294b0a7b9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1ad13fbd679f096a735ed64294b0a7b9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.mascot','data' => ['float' => true,'bounce' => true,'state' => 'happy','size' => 230]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mascot'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['float' => true,'bounce' => true,'state' => 'happy','size' => 230]); ?>
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kidlink-laravel\resources\views/student/home.blade.php ENDPATH**/ ?>