<?php $__env->startSection('title', __('game.letters_play_title', ['level' => $level])); ?>

<?php $__env->startPush('styles'); ?>
<style>
    @import url('/css/fonts.css');

    * { font-family: 'Nunito', sans-serif; }
    h1, h2, h3, h4 { font-family: 'Fredoka One', cursive; }

    .game-bg {
        background: linear-gradient(135deg, #fff8f0 0%, #ffe8dc 25%, #fff3e0 50%, #fef8f5 75%, #fff8f0 100%);
        min-height: 100vh;
    }

    .btn-secondary {
        background: white;
        color: #5f6873;
        padding: 10px 20px;
        border-radius: 10px;
        font-weight: 700;
        border: 1px solid #f1e7cc;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-secondary:hover {
        border-color: #ff595e;
        color: #ff595e;
        background: #fff1f1;
    }

    .btn-primary {
        background: linear-gradient(135deg, #ff595e, #f43f5e);
        color: white;
        padding: 12px 28px;
        border-radius: 12px;
        font-weight: 800;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 1.05rem;
    }

    .btn-primary:hover { filter: brightness(1.05); }

    .progress-bar { height: 10px; background: #f1e7cc; border-radius: 6px; overflow: hidden; }
    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #ff595e, #f43f5e);
        border-radius: 6px;
        transition: width 0.4s ease;
    }

    .level-badge {
        background: linear-gradient(135deg, #ff595e, #f43f5e);
        color: white;
        padding: 5px 14px;
        border-radius: 20px;
        font-size: 0.78rem;
        font-weight: 800;
    }

    .round-badge {
        background: #fff1f1;
        color: #e8454a;
        border: 2px solid #ffc9cb;
        padding: 5px 14px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 800;
    }

    .score-display {
        font-family: 'Fredoka One', cursive;
        font-size: 2rem;
        color: #182438;
    }

    /* Play arena */
    .arena {
        position: relative;
        width: min(92vw, 560px);
        height: min(92vw, 560px);
        margin: 0 auto;
    }

    .target-slot {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 34%;
        height: 34%;
        background: linear-gradient(135deg, #ffe8dc, #fff3e0);
        border: 3px dashed #f47548;
        border-radius: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 10px 30px rgba(244, 117, 72, 0.15);
        animation: targetPulse 2s ease-in-out infinite;
    }

    @keyframes targetPulse {
        0%, 100% { transform: translate(-50%, -50%) scale(1); }
        50% { transform: translate(-50%, -50%) scale(1.04); }
    }

    .target-letter {
        font-family: 'Fredoka One', cursive;
        font-size: clamp(2.6rem, 9vw, 4.6rem);
        color: #182438;
        line-height: 1;
        -webkit-user-select: none;
        user-select: none;
    }

    .target-label {
        position: absolute;
        top: calc(50% + 20%);
        left: 50%;
        transform: translateX(-50%);
        font-size: 0.82rem;
        font-weight: 800;
        color: #f47548;
        letter-spacing: 0.5px;
        white-space: nowrap;
    }

    /* Floating letter options */
    .letter-option {
        position: absolute;
        width: 25%;
        height: 25%;
        transform: translate(-50%, -50%);
        cursor: pointer;
        transition: transform 0.2s ease;
        -webkit-tap-highlight-color: transparent;
    }

    .letter-option .chip {
        position: relative;
        width: 100%;
        height: 100%;
        background: white;
        border-radius: 22px;
        border: 2px solid #f1e7cc;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }

    .letter-option:hover .chip {
        border-color: #ff595e;
        transform: scale(1.06);
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.12);
    }

    .letter-option .letter {
        font-family: 'Fredoka One', cursive;
        font-size: clamp(1.8rem, 6vw, 3.2rem);
        color: #182438;
        line-height: 1;
        transition: color 0.3s ease, transform 0.3s ease;
        -webkit-user-select: none;
        user-select: none;
    }

    /* Clicked wrong once - shows shake, stays clickable-capped */
    .letter-option.mistaken .chip {
        border-color: #ff595e;
        box-shadow: 0 0 0 4px rgba(240, 93, 86, 0.12);
    }

    .letter-option.wrong-click .chip {
        animation: shake 0.4s ease;
    }

    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-8px); }
        50% { transform: translateX(8px); }
        75% { transform: translateX(-5px); }
    }

    /* Correct match */
    .letter-option.matched {
        cursor: default;
        pointer-events: none;
    }

    .letter-option.matched .chip {
        border-color: var(--match-color, #ff595e);
        box-shadow: 0 0 0 4px rgba(236, 72, 153, 0.18);
        background: var(--match-color, #ff595e);
        animation: pop 0.45s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    .letter-option.matched .letter {
        color: white;
    }

    @keyframes pop {
        0% { transform: scale(1); }
        50% { transform: scale(1.35); }
        100% { transform: scale(1); }
    }

    .celebration {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 3.5rem;
        z-index: 1000;
        pointer-events: none;
        animation: floatUp 1s ease-out forwards;
    }

    @keyframes floatUp {
        0% { opacity: 0; transform: translate(-50%, -30%) scale(0.6); }
        30% { opacity: 1; transform: translate(-50%, -55%) scale(1.15); }
        100% { opacity: 0; transform: translate(-50%, -120%) scale(1); }
    }

    .overlay-card {
        position: fixed;
        inset: 0;
        background: rgba(24, 36, 56, 0.55);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 900;
        backdrop-filter: blur(3px);
    }

    .overlay-card .card {
        background: white;
        border-radius: 24px;
        padding: 40px 48px;
        text-align: center;
        max-width: 420px;
        box-shadow: 0 24px 60px rgba(0, 0, 0, 0.25);
        animation: pop 0.4s ease;
    }

    .feedback-banner {
        transition: all 0.3s ease;
        font-weight: 700;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<?php if (isset($component)) { $__componentOriginal1ad13fbd679f096a735ed64294b0a7b9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1ad13fbd679f096a735ed64294b0a7b9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.mascot','data' => ['buddy' => true,'side' => true,'state' => 'start','size' => 200]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mascot'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['buddy' => true,'side' => true,'state' => 'start','size' => 200]); ?>
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

<?php if (isset($component)) { $__componentOriginala1114ca271d34bf1b6b83a60680e6437 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala1114ca271d34bf1b6b83a60680e6437 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.tutorial-stage','data' => ['game' => 'letters','level' => $level]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('tutorial-stage'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['game' => 'letters','level' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($level)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala1114ca271d34bf1b6b83a60680e6437)): ?>
<?php $attributes = $__attributesOriginala1114ca271d34bf1b6b83a60680e6437; ?>
<?php unset($__attributesOriginala1114ca271d34bf1b6b83a60680e6437); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala1114ca271d34bf1b6b83a60680e6437)): ?>
<?php $component = $__componentOriginala1114ca271d34bf1b6b83a60680e6437; ?>
<?php unset($__componentOriginala1114ca271d34bf1b6b83a60680e6437); ?>
<?php endif; ?>
<?php if (isset($component)) { $__componentOriginalcf5c456501434f8b2f9daa809493af82 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcf5c456501434f8b2f9daa809493af82 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.game-kit','data' => ['game' => 'letters','level' => $level,'accent' => '#ff595e']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('game-kit'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['game' => 'letters','level' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($level),'accent' => '#ff595e']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcf5c456501434f8b2f9daa809493af82)): ?>
<?php $attributes = $__attributesOriginalcf5c456501434f8b2f9daa809493af82; ?>
<?php unset($__attributesOriginalcf5c456501434f8b2f9daa809493af82); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcf5c456501434f8b2f9daa809493af82)): ?>
<?php $component = $__componentOriginalcf5c456501434f8b2f9daa809493af82; ?>
<?php unset($__componentOriginalcf5c456501434f8b2f9daa809493af82); ?>
<?php endif; ?>

<div class="game-bg p-4 md:p-8">
    <div class="mx-auto max-w-4xl">
        <!-- Header -->
        <div class="mb-5 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-[#182438]" style="font-family: 'Fredoka One', cursive;"><?php echo e(__('game.letters_heading')); ?></h1>
                <p class="text-sm text-[#5f6873] mt-1"><?php echo e(__('game.level_of_total', ['level' => $level, 'total' => $totalLevels])); ?> · <?php echo e($levelData['description']); ?></p>
            </div>
            <a href="<?php echo e(route('student.games.letters.stage-select')); ?>" class="btn-secondary">
                <svg class="w-5 h-5 inline -mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <?php echo e(__('game.back_to_map')); ?>

            </a>
        </div>

        <!-- Stats strip -->
        <div class="flex flex-wrap items-center justify-center gap-3 mb-6">
            <span class="round-badge"><?php echo e(__('game.round')); ?> <span id="roundNum">1</span> / <span id="roundTotal"><?php echo e($totalRounds); ?></span></span>
            <div class="bg-white rounded-xl shadow-sm px-5 py-2 border border-[#f1e7cc] flex items-center gap-2">
                <span class="text-[#5f6873] font-semibold text-sm"><?php echo e(__('game.score_label')); ?></span>
                <span class="score-display" id="scoreDisplay"><?php echo e($score ?? 0); ?></span>
                <span class="text-[#5f6873] font-medium text-sm">/ <?php echo e($levelData['questions']); ?></span>
            </div>
            <span class="inline-flex items-center gap-1.5 bg-white rounded-xl shadow-sm px-5 py-2 border border-[#f1e7cc]">
                <span class="w-2.5 h-2.5 rounded-full bg-[#ff595e]"></span>
                <span class="text-[#5f6873] font-semibold text-sm"><?php echo e(__('game.mistakes_label')); ?></span>
                <span class="font-bold text-[#182438] text-sm" id="mistakesDisplay"><?php echo e($mistakes ?? 0); ?></span>
            </span>
        </div>

        <!-- Arena -->
        <div class="arena" id="arena">
            <div class="target-slot" id="targetSlot">
                <span class="target-letter" id="targetLetter">A</span>
            </div>
            <span class="target-label" id="targetLabel"><?php echo e(__('letters.find_small_matches')); ?></span>
            <div id="letterOptions"></div>
        </div>

        <!-- Feedback -->
        <div class="text-center mt-6 min-h-[44px]">
            <p id="feedbackText" class="feedback-banner text-lg text-[#5f6873]"><?php echo e(__('letters.instructions')); ?></p>
        </div>
    </div>
</div>

<div id="nextButtonContainer" style="display:none;"></div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    const rounds = <?php echo json_encode($rounds, 15, 512) ?>;
    const totalRounds = <?php echo e($totalRounds); ?>;
    const totalQuestions = <?php echo e($levelData['questions']); ?>;

    const L = {
        'find_small_x': <?php echo json_encode(__('letters.find_small_x'), 15, 512) ?>,
        'tap_all_small': <?php echo json_encode(__('letters.tap_all_small'), 15, 512) ?>,
        'nice_match': <?php echo json_encode(__('game.nice_match'), 15, 512) ?>,
        'keep_looking': <?php echo json_encode(__('game.keep_looking'), 15, 512) ?>,
        'round_complete_next': <?php echo json_encode(__('game.round_complete_next'), 15, 512) ?>,
        'level_complete_job': <?php echo json_encode(__('game.level_complete_job'), 15, 512) ?>,
        'level_num_complete': <?php echo json_encode(__('game.level_num_complete'), 15, 512) ?>,
        'score_line': <?php echo json_encode(__('game.score_line'), 15, 512) ?>,
        'mistakes_line': <?php echo json_encode(__('game.mistakes_line'), 15, 512) ?>,
        'points_earned': <?php echo json_encode(__('game.points_earned'), 15, 512) ?>,
        'back_to_map': <?php echo json_encode(__('game.back_to_map'), 15, 512) ?>,
        'home': <?php echo json_encode(__('game.home'), 15, 512) ?>,
        'next_level': <?php echo json_encode(__('game.next_level'), 15, 512) ?>,
        'score_saved': <?php echo json_encode(__('game.score_saved'), 15, 512) ?>,
    };

    let currentRound = 0;
    let score = <?php echo e($score ?? 0); ?>;
    let mistakes = <?php echo e($mistakes ?? 0); ?>;
    let foundThisRound = 0;
    let playing = false;

    const arena = document.getElementById('arena');
    const letterOptions = document.getElementById('letterOptions');
    const feedbackText = document.getElementById('feedbackText');

    // 8 positions around the ring (angles 45deg apart, top = -90deg)
    function ringPosition(index) {
        const angle = (index * 45 - 90) * Math.PI / 180;
        const radius = 38;
        return {
            left: 50 + radius * Math.cos(angle),
            top: 50 + radius * Math.sin(angle),
        };
    }

    function showCelebration(name) {
        if (window.KidLinkFx) KidLinkFx.celebrate(name);
    }

    function setFeedback(message, color) {
        feedbackText.textContent = message;
        feedbackText.style.color = color || '#5f6873';
    }

    function updateHUD() {
        document.getElementById('scoreDisplay').textContent = score;
        document.getElementById('mistakesDisplay').textContent = mistakes;
        document.getElementById('roundNum').textContent = (currentRound + 1);
    }

    function loadRound(roundIndex) {
        const round = rounds[roundIndex];
        foundThisRound = 0;
        playing = true;

        document.getElementById('targetLetter').textContent = round.targetUpper;
        document.getElementById('targetSlot').style.borderColor = round.targetColor;
        document.getElementById('targetLetter').style.color = round.targetColor;
        document.getElementById('targetLabel').textContent = L.find_small_x.replace(':letter', round.targetLower);
        document.getElementById('targetLabel').style.color = round.targetColor;

        // Rebuild options in a ring
        letterOptions.innerHTML = '';
        round.options.forEach((opt, i) => {
            const pos = ringPosition(i);
            const el = document.createElement('div');
            el.className = 'letter-option';
            el.style.left = pos.left + '%';
            el.style.top = pos.top + '%';
            el.dataset.isMatch = opt.isMatch ? '1' : '0';
            el.innerHTML = '<div class="chip"><span class="letter">' + opt.char.toLowerCase() + '</span></div>';
            el.addEventListener('click', function () {
                handleClick(this, opt);
            });
            letterOptions.appendChild(el);
        });

        updateHUD();
        setFeedback(L.tap_all_small, '#5f6873');
    }

    function handleClick(el, opt) {
        if (!playing) return;

        if (el.classList.contains('matched')) return;

        if (opt.isMatch) {
            // Correct: color the letter
            el.style.setProperty('--match-color', opt.color);
            el.classList.add('matched');
            score++;
            foundThisRound++;
            setFeedback(L.nice_match.replace(':letter', opt.char.toLowerCase()), '#8ac926');
            showCelebration('star');

            if (foundThisRound >= 3) {
                playing = false;
                setTimeout(() => {
                    nextRound();
                }, currentRound + 1 >= totalRounds ? 350 : 900);
            }
        } else {
            // Wrong: record once per option
            if (!el.classList.contains('mistaken')) {
                el.classList.add('mistaken');
                mistakes++;
                setFeedback(L.keep_looking, '#ff595e');
            }
            el.classList.remove('wrong-click');
            void el.offsetWidth;
            el.classList.add('wrong-click');
        }

        updateHUD();
    }

    function nextRound() {
        currentRound++;
        if (currentRound >= totalRounds) {
            finishLevel();
            return;
        }
        setFeedback(L.round_complete_next.replace(':number', currentRound + 1), '#ff595e');
        showCelebration('sparkles');
        setTimeout(() => loadRound(currentRound), 700);
    }

    function finishLevel() {
        showCelebration('sparkles');
        setFeedback(L.level_complete_job, '#e8454a');

        // The result card shows at once; the score is saved behind it.
        KidLinkGame.finish({ score: score, total: totalQuestions, mistakes: mistakes });
    }

    // Idle hint: after 15 quiet seconds the matching letters shake and the mascot gives a clue.
    KidLinkGame.hints(function () {
        const round = rounds[currentRound];
        if (!round || !playing) return null;
        const matches = Array.from(letterOptions.querySelectorAll('.letter-option[data-is-match="1"]:not(.matched)'));
        if (!matches.length) return null;
        return {
            round: currentRound,
            els: matches,
            cues: [document.getElementById('targetSlot')],
            clues: KidLinkGame.clues('tap', { upper: round.targetUpper, lower: round.targetLower }),
        };
    });

    loadRound(0);
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kidlink-laravel\resources\views/student/games/letters/play.blade.php ENDPATH**/ ?>