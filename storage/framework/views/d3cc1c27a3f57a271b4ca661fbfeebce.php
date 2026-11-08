<?php $__env->startSection('title', __('game.animals_match_stage_title')); ?>

<?php $__env->startPush('styles'); ?>
<style>
    @import url('/css/fonts.css');
    
    * { font-family: 'Nunito', sans-serif; }
    h1, h2, h3, h4 { font-family: 'Fredoka One', cursive; }
    
    .stage-map {
        background: linear-gradient(135deg, #f0fdfa 0%, #ccfbf1 25%, #f0fdfa 50%, #e6fffa 75%, #f0fdfa 100%);
        background-size: 200% 200%;
        animation: gradientShift 20s ease infinite;
        min-height: 100vh;
    }
    
    @keyframes gradientShift {
        0%, 100% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
    }
    
    .map-container {
        position: relative;
        width: 100%;
        aspect-ratio: 12/7;
        max-width: 100%;
        border-radius: 24px;
        border: 4px solid #99f6e4;
        background: #ccfbf1;
        overflow: hidden;
        box-shadow: 0 20px 40px rgba(20, 184, 166, 0.15);
    }
    
    .map-bg {
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, #ccfbf1 0%, #99f6e4 100%);
    }
    
    .map-image {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: 0.9;
    }
    
    .path-svg {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
    }
    
    .level-node {
        position: absolute;
        z-index: 20;
        transition: transform 0.2s ease;
        transform: translate(-50%, -50%);
    }
    
    .level-node:hover {
        transform: translate(-50%, -50%) scale(1.15);
    }
    
    .level-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 52px;
        height: 52px;
        border-radius: 50%;
        border: 4px solid white;
        font-family: 'Fredoka One', cursive;
        font-size: 1.25rem;
        font-weight: 700;
        color: white;
        text-decoration: none;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        transition: transform 0.2s ease;
    }
    
    .level-node:hover .level-btn {
        transform: scale(1.15);
    }
    
    .level-btn.unlocked {
        border-color: white;
        color: white;
    }
    
    .level-btn.locked {
        border-color: #99f6e4;
        background: #f0eef5;
        color: #c4b5c7;
        cursor: not-allowed;
    }
    
    .level-btn.locked:hover {
        transform: none;
    }
    
    .level-badge {
        position: absolute;
        bottom: -10px;
        left: 50%;
        transform: translateX(-50%);
        background: white;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.625rem;
        font-weight: 700;
        color: #0d9488;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        white-space: nowrap;
    }
    
    .locked-node {
        position: absolute;
        z-index: 20;
        transform: translate(-50%, -50%);
    }
    
    .locked-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 52px;
        height: 52px;
        border-radius: 50%;
        border: 4px solid #99f6e4;
        background: #f0eef5;
        font-size: 1.5rem;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        cursor: not-allowed;
    }
    
    .player-character {
        position: absolute;
        z-index: 25;
        transition: all 0.5s ease;
        transform: translate(-50%, -140%);
    }
    
    .player-indicator {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: #8b5cf6;
        border: 3px solid white;
        box-shadow: 0 0 14px rgba(139, 92, 246, 0.6), 0 4px 8px rgba(0,0,0,0.2);
        animation: bounce 1s ease-in-out infinite;
        pointer-events: none;
    }
    
    @keyframes bounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-8px); }
    }
    
    .flag-img {
        width: 28px;
        height: auto;
        filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
    }
    
    .coming-soon {
        position: absolute;
        color: #0d9488;
        font-size: 0.75rem;
        font-weight: 700;
        text-align: center;
        white-space: nowrap;
        text-shadow: 0 1px 2px rgba(255,255,255,0.8);
    }
    
    .header-bar {
        background: white;
        border-radius: 16px;
        padding: 16px 24px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08), 0 1px 2px rgba(0,0,0,0.04);
        border: 1px solid #ebe8e1;
    }
    
    .points-badge {
        background: white;
        border-radius: 9999px;
        padding: 8px 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        font-weight: 700;
        color: #37474f;
    }

    .btn-back-map:hover {
        border-color: #14b8a6 !important;
        color: #14b8a6 !important;
        background: #f0fdfa !important;
    }
    
    @media (max-width: 768px) {
        .map-container { aspect-ratio: 4/3; }
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<?php if (isset($component)) { $__componentOriginal1ad13fbd679f096a735ed64294b0a7b9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1ad13fbd679f096a735ed64294b0a7b9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.mascot','data' => ['float' => true,'bounce' => true,'state' => 'start','size' => 230]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mascot'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['float' => true,'bounce' => true,'state' => 'start','size' => 230]); ?>
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
<?php if (isset($component)) { $__componentOriginalac6ae03d376922f556253f00a239b60d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalac6ae03d376922f556253f00a239b60d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.game-tutorial','data' => ['game' => 'animals-match']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('game-tutorial'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['game' => 'animals-match']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalac6ae03d376922f556253f00a239b60d)): ?>
<?php $attributes = $__attributesOriginalac6ae03d376922f556253f00a239b60d; ?>
<?php unset($__attributesOriginalac6ae03d376922f556253f00a239b60d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalac6ae03d376922f556253f00a239b60d)): ?>
<?php $component = $__componentOriginalac6ae03d376922f556253f00a239b60d; ?>
<?php unset($__componentOriginalac6ae03d376922f556253f00a239b60d); ?>
<?php endif; ?>

<div class="min-h-screen bg-[#f0fdfa] p-4 md:p-8">
    <div class="mx-auto max-w-7xl">
        <!-- Header -->
        <div class="mb-6 header-bar">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="<?php echo e(route('student.home')); ?>" class="btn-back-map" style="display:inline-flex;align-items:center;gap:8px;padding:8px 14px;background:white;border:1px solid #ebe8e1;border-radius:10px;font-weight:700;font-size:0.875rem;color:#5f6873;cursor:pointer;transition:all 0.2s ease;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <?php echo e(__('game.games')); ?>

                    </a>
                    <div>
                        <h1 class="text-2xl font-bold text-[#182438]" style="font-family: 'Fredoka One', cursive;"><?php echo e(__('game.animals_match_name')); ?></h1>
                        <p class="text-sm text-[#5f6873]"><?php echo e(__('game.animals_match_tagline')); ?></p>
                    </div>
                </div>
                <div class="points-badge">
                    <span class="text-sm font-semibold text-[#37474f]">🧠 <?php echo e(__('game.stars', ['score' => $totalScore ?? 0])); ?></span>
                </div>
            </div>
        </div>

        <?php if (isset($component)) { $__componentOriginal19e37b623815d580d7a9b5b37d5e035c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal19e37b623815d580d7a9b5b37d5e035c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.games-switcher','data' => ['topic' => 'animals','current' => 'animals-match']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('games-switcher'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['topic' => 'animals','current' => 'animals-match']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal19e37b623815d580d7a9b5b37d5e035c)): ?>
<?php $attributes = $__attributesOriginal19e37b623815d580d7a9b5b37d5e035c; ?>
<?php unset($__attributesOriginal19e37b623815d580d7a9b5b37d5e035c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal19e37b623815d580d7a9b5b37d5e035c)): ?>
<?php $component = $__componentOriginal19e37b623815d580d7a9b5b37d5e035c; ?>
<?php unset($__componentOriginal19e37b623815d580d7a9b5b37d5e035c); ?>
<?php endif; ?>

        <!-- Map Container -->
        <div class="map-container relative">
            <!-- Map Background -->
            <img src="<?php echo e(asset('assets/images/game/animals/background.png')); ?>" alt="<?php echo e(__('game.animals_match_map_alt')); ?>" class="map-image">
            
            <!-- Path -->
            <svg class="path-svg" viewBox="0 0 1200 700" preserveAspectRatio="none">
                <path
                    d="M 60 420
                       C 160 320, 60 200, 210 140
                       C 350 80, 420 160, 450 270
                       C 490 370, 390 440, 500 460
                       C 620 480, 680 410, 630 260
                       C 590 130, 720 120, 830 140"
                    fill="none"
                    stroke="#0d9488"
                    stroke-width="48"
                    stroke-linecap="round"
                />
                <path
                    d="M 60 420
                       C 160 320, 60 200, 210 140
                       C 350 80, 420 160, 450 280
                       C 490 370, 390 440, 500 460
                       C 620 480, 680 410, 630 260
                       C 590 130, 720 120, 830 140"
                    fill="none"
                    stroke="#99f6e4"
                    stroke-width="38"
                    stroke-linecap="round"
                />
            </svg>

            <!-- Levels -->
            <?php $__currentLoopData = $levels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $level): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if($level['unlocked']): ?>
                    <a href="<?php echo e(route('student.games.animals-match.play', ['level' => $level['number']])); ?>" class="level-node" style="left: <?php echo e($level['left']); ?>; top: <?php echo e($level['top']); ?>;" title="<?php echo e(__('game.level_number', ['number' => $level['number']])); ?>">
                        <img src="<?php echo e(asset('assets/images/game/animals/btn_' . $level['number'] . '.png')); ?>" alt="<?php echo e(__('game.level_number', ['number' => $level['number']])); ?>" class="level-btn unlocked" style="width: 52px; height: 52px;">
                        <span class="level-badge"><?php echo e(__('game.level_number', ['number' => $level['number']])); ?></span>
                    </a>
                <?php else: ?>
                    <div class="locked-node" style="left: <?php echo e($level['left']); ?>; top: <?php echo e($level['top']); ?>;" title="<?php echo e(__('game.level_locked_title', ['number' => $level['number']])); ?>">
                        <img src="<?php echo e(asset('assets/images/game/animals/btn_locked.png')); ?>" alt="<?php echo e(__('game.locked')); ?>" class="locked-btn" style="width: 52px; height: 52px;">
                        <span class="level-badge" style="bottom: -20px; color: #c4b5c7;"><?php echo e(__('game.locked')); ?></span>
                    </div>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <!-- Player Character -->
            <div class="player-character player-current-level" id="playerCharacter" style="left: 9.19%; top: 44.89%;">
                <div class="player-indicator"></div>
            </div>

            <!-- Flag -->
            <div class="absolute right-[30%] top-[16%] z-20">
                <img src="<?php echo e(asset('assets/images/game/animals/btn_8.png')); ?>" alt="<?php echo e(__('game.finish')); ?>" class="flag-img" style="width: 28px; height: 28px;">
            </div>

            <!-- Coming Soon -->
            <div class="coming-soon absolute right-[20%] top-[10%]">
                <?php echo e(__('game.animals_match_coming_soon')); ?>

            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const player = document.getElementById('playerCharacter');
        if (!player) return;

        const levelPositions = {
            1: { left: '9.19%', top: '44.89%' },
            2: { left: '14.74%', top: '22.36%' },
            3: { left: '28.98%', top: '18.39%' },
            4: { left: '37.46%', top: '38.34%' },
            5: { left: '38.03%', top: '63.16%' },
            6: { left: '51.87%', top: '61.66%' },
            7: { left: '52.52%', top: '37.22%' },
            8: { left: '59.27%', top: '19.2%' },
        };

        const currentLevel = <?php echo e($currentLevel ?? 1); ?>;

        if (levelPositions[currentLevel]) {
            const pos = levelPositions[currentLevel];
            document.getElementById('playerCharacter').style.left = pos.left;
            document.getElementById('playerCharacter').style.top = pos.top;
        }
    });
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kidlink-laravel\resources\views/student/games/animals-match/stage-select.blade.php ENDPATH**/ ?>