<?php $__env->startSection('title', __('game.numbers_play_title', ['level' => $level])); ?>

<?php $__env->startPush('styles'); ?>
<style>
    @import url('/css/fonts.css');

    * { font-family: 'Nunito', sans-serif; }
    h1, h2, h3 { font-family: 'Fredoka One', cursive; }

    .game-bg {
        background: linear-gradient(135deg, #fef9c3 0%, #fde68a 30%, #fef9c3 60%, #fde68a 100%);
        min-height: 100vh;
    }

    .btn-secondary {
        background: white;
        color: #5f6873;
        padding: 10px 20px;
        border-radius: 10px;
        font-weight: 700;
        border: 1px solid #e0e7ef;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-secondary:hover { border-color: #f59e0b; color: #f59e0b; background: #fffbeb; }

    .btn-primary {
        background: #f59e0b;
        color: white;
        padding: 12px 28px;
        border-radius: 12px;
        font-weight: 800;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 1.05rem;
    }

    .btn-primary:hover { background: #d97706; }

    .progress-bar { height: 10px; background: #fde68a; border-radius: 6px; overflow: hidden; }
    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #f59e0b, #d97706);
        border-radius: 6px;
        transition: width 0.4s ease;
    }

    .round-badge {
        background: #fffbeb;
        color: #d97706;
        border: 2px solid #fcd34d;
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

    .arena { width: min(92vw, 560px); margin: 0 auto; }

    .board {
        background: white;
        border: 3px solid #fcd34d;
        border-radius: 28px;
        padding: 32px 20px;
        box-shadow: 0 12px 30px rgba(245, 158, 11, 0.12);
        margin-bottom: 22px;
        text-align: center;
        min-height: 260px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .question-text {
        font-family: 'Fredoka One', cursive;
        font-size: 1.4rem;
        color: #182438;
        margin-bottom: 20px;
    }

    .objects-grid {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 10px;
        max-width: 360px;
        margin: 0 auto;
    }

    .game-object-icon { width: 1em; height: 1em; object-fit: contain; vertical-align: middle; }
    .object-item {
        font-size: 2.2rem;
        animation: popIn 0.3s ease backwards;
        cursor: pointer;
        transition: transform 0.15s ease;
        user-select: none;
        -webkit-user-select: none;
        touch-action: manipulation;
    }

    .object-item:hover { transform: scale(1.2); }
    .object-item:active { transform: scale(0.9); }
    .object-item.counted { transform: scale(1.15); filter: brightness(1.1); }

    .fruit-pop {
        position: absolute;
        pointer-events: none;
        font-size: 1.2rem;
        z-index: 100;
        animation: fruitBurst 0.6s ease-out forwards;
    }

    @keyframes fruitBurst {
        0% { opacity: 1; transform: scale(0.5) translateY(0); }
        50% { opacity: 1; transform: scale(1.3) translateY(-20px); }
        100% { opacity: 0; transform: scale(0.8) translateY(-45px); }
    }

    @keyframes popIn {
        0% { opacity: 0; transform: scale(0.3) rotate(-10deg); }
        70% { transform: scale(1.1) rotate(3deg); }
        100% { opacity: 1; transform: scale(1) rotate(0deg); }
    }

    .tray {
        background: white;
        border: 2px solid #fcd34d;
        border-radius: 28px;
        padding: 20px 16px;
        box-shadow: 0 12px 30px rgba(245, 158, 11, 0.08);
    }

    .tray-label {
        text-align: center;
        font-weight: 700;
        color: #92400e;
        margin-bottom: 12px;
        font-size: 0.9rem;
    }

    #trayRow {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 12px;
    }

    .tray-item {
        width: 72px;
        height: 72px;
        border-radius: 18px;
        background: #fffbeb;
        border: 2px solid #fcd34d;
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.06);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
        -webkit-user-select: none;
        user-select: none;
        touch-action: manipulation;
        font-family: 'Fredoka One', cursive;
        font-size: 28px;
        color: #92400e;
    }

    .tray-item:hover {
        transform: scale(1.06);
        border-color: #f59e0b;
        box-shadow: 0 10px 22px rgba(245, 158, 11, 0.25);
        background: #fef3c7;
    }

    .tray-item.correct {
        background: #dcfce7;
        border-color: #22c55e;
        color: #15803d;
        pointer-events: none;
        transform: scale(1.05);
        animation: correctPop 0.35s ease;
    }

    .tray-item.wrong {
        animation: shake 0.4s ease;
        border-color: #ef4444;
        background: #fef2f2;
        color: #dc2626;
    }

    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-9px); }
        50% { transform: translateX(9px); }
        75% { transform: translateX(-6px); }
    }

    @keyframes correctPop {
        0% { transform: scale(1); }
        50% { transform: scale(1.15); }
        100% { transform: scale(1.05); }
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
        from { opacity: 0; transform: scale(0.92); }
        to { opacity: 1; transform: scale(1); }
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

<div class="game-bg p-4 md:p-8">
    <div class="mx-auto max-w-4xl">
        <div class="mb-5 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-[#182438]" style="font-family: 'Fredoka One', cursive;"><?php echo e(__('game.numbers_heading')); ?></h1>
                <p class="text-sm text-[#92400e] mt-1"><?php echo e(__('game.level_of_total', ['level' => $level, 'total' => $totalLevels])); ?> · <?php echo e($levelData['description']); ?></p>
            </div>
            <a href="<?php echo e(route('student.games.numbers.stage-select')); ?>" class="btn-secondary">
                <svg class="w-5 h-5 inline -mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <?php echo e(__('game.back')); ?>

            </a>
        </div>

        <div class="flex flex-wrap items-center justify-center gap-3 mb-6">
            <span class="round-badge"><?php echo e(__('game.round')); ?> <span id="roundNum">1</span> / <span id="roundTotal"><?php echo e($totalRounds); ?></span></span>
            <div class="bg-white rounded-xl shadow-sm px-5 py-2 border border-[#fcd34d] flex items-center gap-2">
                <span class="text-[#92400e] font-semibold text-sm"><?php echo e(__('game.score_label')); ?></span>
                <span class="score-display" id="scoreDisplay"><?php echo e($score ?? 0); ?></span>
                <span class="text-[#92400e] font-medium text-sm">/ <?php echo e($totalRounds); ?></span>
            </div>
            <span class="inline-flex items-center gap-1.5 bg-white rounded-xl shadow-sm px-5 py-2 border border-[#fcd34d]">
                <span class="w-2.5 h-2.5 rounded-full bg-[#f05d56]"></span>
                <span class="text-[#92400e] font-semibold text-sm"><?php echo e(__('game.mistakes_label')); ?></span>
                <span class="font-bold text-[#182438] text-sm" id="mistakesDisplay"><?php echo e($mistakes ?? 0); ?></span>
            </span>
        </div>

        <div class="progress-bar mb-6">
            <div class="progress-fill" id="progressFill" style="width: 0%"></div>
        </div>

        <div class="arena" id="arena">
            <div class="board">
                <div class="question-text" id="questionText"><?php echo e(__('game.how_many')); ?></div>
                <div class="objects-grid" id="objectsGrid"></div>
            </div>
            <div class="tray">
                <div class="tray-label"><?php echo e(__('game.tap_correct_number')); ?></div>
                <div id="trayRow"></div>
            </div>
        </div>

        <div class="text-center mt-6 min-h-[44px]">
            <p id="feedbackText" class="feedback-banner text-lg text-[#92400e]"><?php echo e(__('game.instructions_numbers')); ?></p>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    const rounds = <?php echo json_encode($rounds, 15, 512) ?>;
    const totalRounds = <?php echo e($totalRounds); ?>;
    const routeStageSelect = <?php echo json_encode(route('student.games.numbers.stage-select'), 15, 512) ?>;
    const routeNextLevel = <?php echo json_encode(route('student.games.numbers.play', ['level' => $level + 1]), 512) ?>;
    const submitUrl = <?php echo json_encode(route('student.games.numbers.submit', $level), 512) ?>;
    const levelNum = <?php echo e($level); ?>;

    const L = {
        'how_many_x': <?php echo json_encode(__('game.how_many_x'), 15, 512) ?>,
        'count_then_tap': <?php echo json_encode(__('game.count_then_tap'), 15, 512) ?>,
        'correct_count': <?php echo json_encode(__('game.correct_count'), 15, 512) ?>,
        'great_counting': <?php echo json_encode(__('game.great_counting'), 15, 512) ?>,
        'not_quite': <?php echo json_encode(__('game.not_quite'), 15, 512) ?>,
        'level_complete_numbers': <?php echo json_encode(__('game.level_complete_numbers'), 15, 512) ?>,
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
    let counted = 0;

    const questionText = document.getElementById('questionText');
    const objectsGrid = document.getElementById('objectsGrid');
    const trayRow = document.getElementById('trayRow');
    const feedbackText = document.getElementById('feedbackText');
    const progressFill = document.getElementById('progressFill');

    function showCelebration(emoji) {
        var el = document.createElement('div');
        el.className = 'celebration';
        el.innerHTML = '<i class="ico ico-' + emoji + '" aria-hidden="true"></i>';
        document.body.appendChild(el);
        setTimeout(function() { el.remove(); }, 1000);
    }

    function setFeedback(message, color) {
        feedbackText.textContent = message;
        feedbackText.style.color = color || '#92400e';
    }

    function updateHUD() {
        document.getElementById('scoreDisplay').textContent = score;
        document.getElementById('mistakesDisplay').textContent = mistakes;
        document.getElementById('roundNum').textContent = (currentRound + 1);
        progressFill.style.width = Math.round((currentRound / totalRounds) * 100) + '%';
    }

    function fruitBurst(el, emoji) {
        var rect = el.getBoundingClientRect();
        for (var i = 0; i < 5; i++) {
            var spark = document.createElement('span');
            spark.className = 'fruit-pop';
            spark.innerHTML = '<i class="ico ico-' + ['sparkles', 'star', 'sparkles', 'star-half', 'sparkles'][i % 5] + '" aria-hidden="true"></i>';
            spark.style.left = (rect.left + rect.width / 2 + (Math.random() - 0.5) * 50) + 'px';
            spark.style.top = (rect.top + rect.height / 2 + (Math.random() - 0.5) * 30) + 'px';
            spark.style.animationDelay = (i * 0.05) + 's';
            document.body.appendChild(spark);
            setTimeout(function(s) { s.remove(); }, 700);
        }
        var bigPop = document.createElement('span');
        bigPop.className = 'fruit-pop';
        bigPop.innerHTML = `<img src="${emoji}" alt="" class="game-object-icon">`;
        bigPop.style.fontSize = '2.5rem';
        bigPop.style.left = (rect.left + rect.width / 2) + 'px';
        bigPop.style.top = (rect.top + rect.height / 2) + 'px';
        document.body.appendChild(bigPop);
        setTimeout(function() { bigPop.remove(); }, 700);
    }

    function loadRound(roundIndex) {
        var round = rounds[roundIndex];
        playing = true;
        counted = 0;

        questionText.textContent = L.how_many_x.replace(':name', round.name);

        objectsGrid.innerHTML = '';
        for (var i = 0; i < round.target; i++) {
            var el = document.createElement('span');
            el.className = 'object-item';
            el.innerHTML = `<img src="${round.icon}" alt="" class="game-object-icon">`;
            el.style.animationDelay = (i * 0.08) + 's';
            el.dataset.index = i;
            el.addEventListener('pointerdown', function(e) {
                e.preventDefault();
                if (!playing) return;
                if (this.classList.contains('counted')) return;
                this.classList.add('counted');
                counted++;
                fruitBurst(this, round.icon);
            });
            objectsGrid.appendChild(el);
        }

        trayRow.innerHTML = '';
        round.options.forEach(function(num) {
            var el = document.createElement('div');
            el.className = 'tray-item';
            el.textContent = num;
            el.dataset.num = num;
            el.addEventListener('pointerdown', function(e) {
                handleTap(e, el, num, round);
            });
            trayRow.appendChild(el);
        });

        updateHUD();
        setFeedback(L.count_then_tap, '#92400e');
    }

    function handleTap(e, el, num, round) {
        if (!playing) return;
        if (el.classList.contains('correct')) return;

        e.preventDefault();

        if (num === round.target) {
            playing = false;
            el.classList.add('correct');
            score++;
            setFeedback(L.correct_count.replace(':count', num).replace(':name', round.name), '#22c55e');
            showCelebration('star');

            setTimeout(function() {
                currentRound++;
                if (currentRound >= totalRounds) {
                    finishLevel();
                } else {
                    setFeedback(L.great_counting, '#f59e0b');
                    showCelebration('star');
                    setTimeout(function() { loadRound(currentRound); }, 700);
                }
            }, 600);
        } else {
            el.classList.add('wrong');
            mistakes++;
            setFeedback(L.not_quite, '#ef4444');
            setTimeout(function() { el.classList.remove('wrong'); }, 500);
        }

        updateHUD();
    }

    function finishLevel() {
        showCelebration('sparkles');
        setFeedback(L.level_complete_numbers, '#f59e0b');

        fetch(submitUrl, {
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
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var card = document.createElement('div');
            card.className = 'overlay-card';
            card.innerHTML =
                '<div class="card">' +
                    '<div class="text-5xl mb-3 text-[#f59e0b]"><i class="ico ico-trophy" aria-hidden="true"></i></div>' +
                    '<h2 class="text-2xl font-bold text-[#182438] mb-2" style="font-family:\'Fredoka One\', cursive;">' + L.level_num_complete.replace(':level', levelNum) + '</h2>' +
                    '<p class="text-[#92400e] mb-1">' + L.score_line.replace(':score', data.score).replace(':total', data.total) + '</p>' +
                    '<p class="text-[#92400e] mb-1">' + L.mistakes_line.replace(':count', (data.mistakes || 0)) + '</p>' +
                    '<p class="text-[#f59e0b] font-bold mb-4">' + L.points_earned.replace(':count', data.points_earned) + '</p>' +
                    '<div class="flex justify-center gap-3">' +
                        '<button onclick="location.href=\'' + routeStageSelect + '\'" class="btn-secondary">' + L.back_to_map + '</button>' +
                        '<button onclick="location.href=\'' + <?php echo json_encode(route('student.home'), 15, 512) ?> + '\'" class="btn-secondary">' + L.home + '</button>' +
                        (data.next_level_unlocked ? '<button onclick="location.href=\'' + routeNextLevel + '\'" class="btn-primary">' + L.next_level + '</button>' : '') +
                    '</div>' +
                '</div>';
            document.body.appendChild(card);
        })
        .catch(function() {
            setFeedback(L.score_saved, '#22c55e');
        });
    }

    loadRound(0);
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kidlink-laravel\resources\views/student/games/numbers/play.blade.php ENDPATH**/ ?>