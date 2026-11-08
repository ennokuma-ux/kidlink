<?php $__env->startSection('title', __('game.animals_play_title', ['level' => $level])); ?>

<?php $__env->startPush('styles'); ?>
<style>
    @import url('/css/fonts.css');

    * { font-family: 'Nunito', sans-serif; }
    h1, h2, h3, h4 { font-family: 'Fredoka One', cursive; }

    .game-bg {
        background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 25%, #f5f3ff 50%, #ede9fe 75%, #f5f3ff 100%);
        min-height: 100vh;
    }

    .btn-secondary {
        background: white;
        color: #5f6873;
        padding: 10px 20px;
        border-radius: 10px;
        font-weight: 700;
        border: 1px solid #ebe8e1;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-secondary:hover {
        border-color: #8b5cf6;
        color: #8b5cf6;
        background: #f5f3ff;
    }

    .btn-primary {
        background: #8b5cf6;
        color: white;
        padding: 12px 28px;
        border-radius: 12px;
        font-weight: 800;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 1.05rem;
    }

    .btn-primary:hover { background: #7c3aed; }

    .progress-bar { height: 10px; background: #ede9fe; border-radius: 6px; overflow: hidden; }
    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #8b5cf6, #7c3aed);
        border-radius: 6px;
        transition: width 0.4s ease;
    }

    .round-badge {
        background: #f5f3ff;
        color: #7c3aed;
        border: 2px solid #ddd6fe;
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

    .arena {
        width: min(92vw, 560px);
        margin: 0 auto;
    }

    .board {
        background: white;
        border: 2px solid #ede9fe;
        border-radius: 28px;
        padding: 32px 16px;
        box-shadow: 0 12px 30px rgba(139, 92, 246, 0.08);
        margin-bottom: 22px;
        text-align: center;
    }

    .game-object-icon { display: block; width: 100%; height: 100%; object-fit: contain; }
    .target-emoji {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        min-height: 112px;
        margin: 0 auto 8px;
        animation: popIn 0.4s ease;
    }
    .target-emoji img {
        width: 112px;
        height: 112px;
        max-width: 100%;
        object-fit: contain;
        object-position: center;
        margin: 0 auto;
    }

    .target-name {
        font-family: 'Fredoka One', cursive;
        font-size: 1.6rem;
        color: #182438;
    }

    @keyframes popIn {
        0% { transform: scale(0.5); opacity: 0; }
        60% { transform: scale(1.1); }
        100% { transform: scale(1); opacity: 1; }
    }

    .tray {
        background: #faf5ff;
        border: 2px solid #ede9fe;
        border-radius: 28px;
        padding: 20px 16px;
        box-shadow: 0 12px 30px rgba(139, 92, 246, 0.06);
    }

    #trayRow {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 14px;
        flex-wrap: wrap;
        min-height: 90px;
    }

    .option-btn {
        width: 90px;
        height: 90px;
        border-radius: 20px;
        background: #ede9fe;
        border: 2px solid #ddd6fe;
        box-shadow: 0 6px 16px rgba(139, 92, 246, 0.08);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        -webkit-user-select: none;
        user-select: none;
        gap: 2px;
    }

    .option-btn:hover {
        transform: scale(1.06);
        border-color: #8b5cf6;
        box-shadow: 0 10px 22px rgba(139, 92, 246, 0.15);
    }

    .option-btn:active {
        transform: scale(0.97);
    }

    .option-btn .opt-emoji {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        font-size: 32px;
        line-height: 1.2;
    }

    .option-btn .opt-emoji img {
        width: 40px;
        height: 40px;
        object-fit: contain;
        object-position: center;
    }

    .option-btn .opt-name {
        font-size: 0.65rem;
        font-weight: 700;
        color: #6b21a8;
        line-height: 1;
    }

    .option-btn.correct {
        background: #dcfce7;
        border-color: #22c55e;
        animation: correctPop 0.4s ease;
    }

    .option-btn.wrong {
        background: #fee2e2;
        border-color: #ef4444;
        animation: shake 0.4s ease;
    }

    @keyframes correctPop {
        0% { transform: scale(1); }
        40% { transform: scale(1.15); }
        100% { transform: scale(1); }
    }

    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-9px); }
        50% { transform: translateX(9px); }
        75% { transform: translateX(-6px); }
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
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: scale(0.9); }
        to { opacity: 1; transform: scale(1); }
    }

    .feedback-banner {
        transition: all 0.3s ease;
        font-weight: 700;
    }

    @media (max-width: 640px) {
        .target-emoji { min-height: 88px; }
        .target-emoji img { width: 88px; height: 88px; }
        .option-btn .opt-emoji { width: 40px; height: 40px; }
        .option-btn .opt-emoji img { width: 36px; height: 36px; }
    }

    .multiplier-badge {
        background: #fef3c7;
        color: #d97706;
        border: 2px solid #fde68a;
        padding: 4px 10px;
        border-radius: 14px;
        font-size: 0.7rem;
        font-weight: 800;
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

<div class="game-bg p-4 md:p-8">
    <div class="mx-auto max-w-4xl">
        <div class="mb-5 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-[#182438]" style="font-family: 'Fredoka One', cursive;"><?php echo e($levelData['title']); ?></h1>
                <p class="text-sm text-[#5f6873] mt-1"><?php echo e(__('game.level_of_total', ['level' => $level, 'total' => $totalLevels])); ?> · <?php echo e($levelData['description']); ?></p>
            </div>
            <a href="<?php echo e(route('student.games.animals.stage-select')); ?>" class="btn-secondary">
                <svg class="w-5 h-5 inline -mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <?php echo e(__('game.back_to_map')); ?>

            </a>
        </div>

        <div class="flex flex-wrap items-center justify-center gap-3 mb-6">
            <span class="round-badge"><?php echo e(__('game.round')); ?> <span id="roundNum">1</span> / <span id="roundTotal"><?php echo e($totalRounds); ?></span></span>
            <div class="bg-white rounded-xl shadow-sm px-5 py-2 border border-[#ede9fe] flex items-center gap-2">
                <span class="text-[#5f6873] font-semibold text-sm"><?php echo e(__('game.score_label')); ?></span>
                <span class="score-display" id="scoreDisplay"><?php echo e($score ?? 0); ?></span>
            </div>
            <span class="inline-flex items-center gap-1.5 bg-white rounded-xl shadow-sm px-5 py-2 border border-[#ede9fe]">
                <span class="w-2.5 h-2.5 rounded-full bg-[#f05d56]"></span>
                <span class="text-[#5f6873] font-semibold text-sm"><?php echo e(__('game.mistakes_label')); ?></span>
                <span class="font-bold text-[#182438] text-sm" id="mistakesDisplay"><?php echo e($mistakes ?? 0); ?></span>
            </span>
        </div>

        <div class="arena" id="arena">
            <div class="board">
                <div id="targetEmoji" class="target-emoji"></div>
                <div id="targetName" class="target-name"></div>
            </div>
            <div class="tray">
                <div id="trayRow"></div>
            </div>
        </div>

        <div class="text-center mt-6 min-h-[44px]">
            <p id="feedbackText" class="feedback-banner text-lg text-[#5f6873]"><?php echo e(__('game.instructions_animals')); ?></p>
        </div>
    </div>
</div>

<div id="nextButtonContainer" style="display:none;"></div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    const rounds = <?php echo json_encode($rounds, 15, 512) ?>;
    const totalRounds = <?php echo e($totalRounds); ?>;
    const level = <?php echo e($level); ?>;

    const L = {
        'instructions_animals': <?php echo json_encode(__('game.instructions_animals'), 15, 512) ?>,
        'animals_correct': <?php echo json_encode(__('game.animals_correct'), 15, 512) ?>,
        'animals_wrong': <?php echo json_encode(__('game.animals_wrong'), 15, 512) ?>,
        'round_coming_up': <?php echo json_encode(__('game.round_coming_up'), 15, 512) ?>,
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
    let playing = false;

    const targetEmoji = document.getElementById('targetEmoji');
    const targetName = document.getElementById('targetName');
    const trayRow = document.getElementById('trayRow');
    const feedbackText = document.getElementById('feedbackText');

    function showCelebration(emoji) {
        const el = document.createElement('div');
        el.className = 'celebration';
        el.innerHTML = '<i class="ico ico-' + emoji + '" aria-hidden="true"></i>';
        document.body.appendChild(el);
        setTimeout(() => el.remove(), 1000);
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
        playing = true;

        targetEmoji.innerHTML = `<img src="${round.targetEmoji}" alt="" class="game-object-icon">`;
        targetEmoji.style.animation = 'none';
        void targetEmoji.offsetWidth;
        targetEmoji.style.animation = 'popIn 0.4s ease';

        targetName.textContent = round.targetName;

        trayRow.innerHTML = '';
        round.options.forEach((option) => {
            const btn = document.createElement('div');
            btn.className = 'option-btn';
            btn.dataset.type = option.type;
            btn.innerHTML = `
                <span class="opt-emoji"><img src="${option.emoji}" alt="" class="game-object-icon"></span>
                <span class="opt-name">${option.name}</span>
            `;
            btn.addEventListener('pointerdown', function (e) {
                e.preventDefault();
                if (!playing || btn.classList.contains('correct') || btn.classList.contains('wrong')) return;
                handleChoice(btn, option);
            });
            trayRow.appendChild(btn);
        });

        updateHUD();
        setFeedback(L.instructions_animals, '#5f6873');
    }

    function handleChoice(btn, option) {
        playing = false;

        if (option.isMatch) {
            btn.classList.add('correct');
            score++;
            setFeedback(L.animals_correct.replace(':name', option.name), '#22c55e');
            showCelebration('star');
            updateHUD();
            setTimeout(() => {
                nextRound();
            }, 600);
        } else {
            btn.classList.add('wrong');
            mistakes++;
            setFeedback(L.animals_wrong.replace(':name', option.name), '#ef4444');
            updateHUD();
            setTimeout(() => {
                btn.classList.remove('wrong');
                playing = true;
            }, 500);
        }
    }

    function nextRound() {
        currentRound++;
        if (currentRound >= totalRounds) {
            finishLevel();
            return;
        }
        setFeedback(L.round_coming_up.replace(':number', currentRound + 1), '#8b5cf6');
        showCelebration('sparkles');
        setTimeout(() => loadRound(currentRound), 500);
    }

    function finishLevel() {
        playing = false;
        showCelebration('trophy');
        setFeedback(L.level_complete_job, '#8b5cf6');

        fetch("<?php echo e(route('student.games.animals.submit', $level)); ?>", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                score: score,
                total: totalRounds,
                mistakes: mistakes,
                time_taken: 0,
            }),
        })
        .then(r => r.json())
        .then(data => {
            const card = document.createElement('div');
            card.className = 'overlay-card';
            card.innerHTML = `
                <div class="card">
                    <div class="text-5xl mb-3 text-[#f59e0b]"><i class="ico ico-trophy" aria-hidden="true"></i></div>
                    <h2 class="text-2xl font-bold text-[#182438] mb-2" style="font-family:'Fredoka One', cursive;">${L.level_num_complete.replace(':level', level)}</h2>
                    <p class="text-[#5f6873] mb-1">${L.score_line.replace(':score', data.score).replace(':total', data.total)}</p>
                    <p class="text-[#5f6873] mb-1">${L.mistakes_line.replace(':count', data.mistakes ?? 0)}</p>
                    <p class="text-[#8b5cf6] font-bold mb-4">${L.points_earned.replace(':count', data.points_earned)}</p>
                    <div class="flex justify-center gap-3">
                        <button onclick="location.href='<?php echo e(route('student.games.animals.stage-select')); ?>'" class="btn-secondary">${L.back_to_map}</button>
                        <button onclick="location.href='<?php echo e(route('student.home')); ?>'" class="btn-secondary">${L.home}</button>
                        ${data.next_level_unlocked ? `<button onclick="location.href='<?php echo e(route('student.games.animals.play', ['level' => $level + 1])); ?>'" class="btn-primary">${L.next_level}</button>` : ''}
                    </div>
                </div>`;
            document.body.appendChild(card);
        })
        .catch(() => {
            const card = document.createElement('div');
            card.className = 'overlay-card';
            card.innerHTML = `
                <div class="card">
                    <div class="text-5xl mb-3 text-[#f59e0b]"><i class="ico ico-trophy" aria-hidden="true"></i></div>
                    <h2 class="text-2xl font-bold text-[#182438] mb-2" style="font-family:'Fredoka One', cursive;">${L.level_num_complete.replace(':level', level)}</h2>
                    <p class="text-[#5f6873] mb-1">${L.score_line.replace(':score', score).replace(':total', totalRounds)}</p>
                    <p class="text-[#5f6873] mb-1">${L.mistakes_line.replace(':count', mistakes)}</p>
                    <p class="text-[#8b5cf6] font-bold mb-4">${L.score_saved}</p>
                    <div class="flex justify-center gap-3">
                        <button onclick="location.href='<?php echo e(route('student.games.animals.stage-select')); ?>'" class="btn-secondary">${L.back_to_map}</button>
                        <button onclick="location.href='<?php echo e(route('student.home')); ?>'" class="btn-secondary">${L.home}</button>
                        <button onclick="location.href='<?php echo e(route('student.games.animals.play', ['level' => $level + 1])); ?>'" class="btn-primary">${L.next_level}</button>
                    </div>
                </div>`;
            document.body.appendChild(card);
        });
    }

    loadRound(0);
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kidlink-laravel\resources\views/student/games/animals/play.blade.php ENDPATH**/ ?>