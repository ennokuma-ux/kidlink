@php
    // New words for this game fall back to English until they are added to lang/*/game.php.
    $cv = fn (string $key, string $default, array $vars = []) => \Illuminate\Support\Facades\Lang::has('game.'.$key)
        ? __('game.'.$key, $vars)
        : strtr($default, collect($vars)->mapWithKeys(fn ($v, $k) => [':'.$k => $v])->all());
@endphp
@extends('layouts.app')

@section('title', __('game.color_vision_play_title', ['level' => $level]))

@push('styles')
<style>
    @import url('/css/fonts.css');

    * { font-family: 'Nunito', sans-serif; }
    h1, h2, h3, h4 { font-family: 'Fredoka One', cursive; }

    .game-bg {
        background: linear-gradient(135deg, #fff8f0 0%, #ffe9ea 25%, #fff1f1 50%, #ffe9ea 75%, #fff8f0 100%);
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
        background: #ff595e;
        color: white;
        padding: 12px 28px;
        border-radius: 12px;
        font-weight: 800;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 1.05rem;
    }

    .btn-primary:hover { background: #e8454a; }

    .progress-bar { height: 10px; background: #ffe9ea; border-radius: 6px; overflow: hidden; }
    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #ff595e, #e8454a);
        border-radius: 6px;
        transition: width 0.4s ease;
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

.arena {
        width: min(94vw, 520px);
        margin: 0 auto;
    }

    .board {
        background: white;
        border: 2px solid #ffe9ea;
        border-radius: 28px;
        padding: 22px;
        box-shadow: 0 12px 30px rgba(236, 72, 153, 0.08);
        margin-bottom: 22px;
    }

    /* The dot plate: a disc of colored dots with a number hidden in it. */
    .plate-wrap {
        position: relative;
        width: min(100%, 400px);
        margin: 0 auto;
    }

    #plateCanvas {
        display: block;
        width: 100%;
        aspect-ratio: 1 / 1;
        border-radius: 50%;
        box-shadow: 0 10px 28px rgba(24, 36, 56, 0.14);
        background: #f7f3ea;
    }

    #plateCanvas.is-fading { opacity: 0; transition: opacity 0.15s ease; }
    #plateCanvas { transition: opacity 0.25s ease; }

    .answer-row {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        margin-top: 22px;
    }

    .answer-btn {
        font-family: 'Fredoka One', cursive;
        font-size: clamp(1.8rem, 8vw, 2.6rem);
        line-height: 1;
        padding: 14px 0;
        background: white;
        color: #182438;
        border: 3px solid #e7dfcf;
        border-radius: 18px;
        cursor: pointer;
        box-shadow: 0 4px 0 #e7dfcf;
        transition: transform 0.12s ease, box-shadow 0.12s ease, border-color 0.2s ease;
        touch-action: manipulation;
        -webkit-user-select: none;
        user-select: none;
    }

    .answer-btn:hover:not(:disabled) { border-color: #182438; transform: translateY(-2px); }
    .answer-btn:active:not(:disabled) { transform: translateY(2px); box-shadow: 0 1px 0 #e7dfcf; }
    .answer-btn:disabled { cursor: default; opacity: 1; }
    .answer-btn.correct { background: #f4fae6; border-color: #8ac926; box-shadow: 0 4px 0 #8ac926; animation: popIn 0.3s ease; }
    .answer-btn.wrong { background: #fff1f1; border-color: #ff595e; box-shadow: 0 4px 0 #ff595e; color: #b42328; animation: shake 0.4s ease; }

    .cant-see {
        display: block;
        margin: 16px auto 0;
        background: transparent;
        color: #5f6873;
        font-weight: 800;
        font-size: 0.95rem;
        padding: 10px 18px;
        border-radius: 12px;
        border: 2px dashed #d9d0bb;
        cursor: pointer;
    }
    .cant-see:hover:not(:disabled) { color: #182438; border-color: #182438; }
    .cant-see:disabled { opacity: 0.5; cursor: default; }

    .fine-print {
        max-width: 460px;
        margin: 22px auto 0;
        text-align: center;
        font-size: 0.78rem;
        line-height: 1.4;
        color: #8b949d;
    }

    @keyframes popIn {
        0% { transform: scale(0.92); }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); }
    }

    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-8px); }
        50% { transform: translateX(8px); }
        75% { transform: translateX(-5px); }
    }

    .found-badge {
        background: #f4fae6;
        color: #6fa51c;
        border: 2px solid #bbf7d0;
        padding: 5px 14px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 800;
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

    .feedback-banner {
        transition: all 0.3s ease;
        font-weight: 700;
    }
</style>
@endpush

@section('content')
{{-- In-game mascot buddy: reacts to correct/wrong/next-stage feedback --}}
<x-mascot buddy side state="start" :size="200" />
{{-- Level 1 is the tutorial stage --}}
<x-tutorial-stage game="color-vision" :level="$level" />
<x-game-kit game="color-vision" :level="$level" accent="#a21caf" />

<div class="game-bg p-4 md:p-8">
    <div class="mx-auto max-w-4xl">
        <div class="mb-5 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-[#182438]" style="font-family: 'Fredoka One', cursive;">{{ __('game.color_vision_heading') }}</h1>
                <p class="text-sm text-[#5f6873] mt-1">{{ __('game.level_of_total', ['level' => $level, 'total' => $totalLevels]) }} · {{ $levelData['description'] }}</p>
            </div>
            <a href="{{ route('student.games.color-vision.stage-select') }}" class="btn-secondary">
                <svg class="w-5 h-5 inline -mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                {{ __('game.back_to_map') }}
            </a>
        </div>

<div class="flex flex-wrap items-center justify-center gap-3 mb-6">
            <span class="round-badge">{{ __('game.round') }} <span id="roundNum">1</span> / <span id="roundTotal">{{ $totalRounds }}</span></span>
            <div class="bg-white rounded-xl shadow-sm px-5 py-2 border border-[#ffe9ea] flex items-center gap-2">
                <span class="text-[#5f6873] font-semibold text-sm">{{ __('game.score_label') }}</span>
                <span class="score-display" id="scoreDisplay">{{ $score ?? 0 }}</span>
                <span class="text-[#5f6873] font-medium text-sm">/ {{ $levelData['questions'] }}</span>
            </div>
            <span class="inline-flex items-center gap-1.5 bg-white rounded-xl shadow-sm px-5 py-2 border border-[#ffe9ea]">
                <span class="w-2.5 h-2.5 rounded-full bg-[#ff595e]"></span>
                <span class="text-[#5f6873] font-semibold text-sm">{{ __('game.mistakes_label') }}</span>
                <span class="font-bold text-[#182438] text-sm" id="mistakesDisplay">{{ $mistakes ?? 0 }}</span>
            </span>
        </div>

        <div class="arena" id="arena">
            <div class="board">
                <div class="plate-wrap">
                    <canvas id="plateCanvas" width="800" height="800" role="img" aria-label="{{ $cv('cv_plate_label', 'A picture made of colored dots with a number hidden in it') }}"></canvas>
                </div>

                <div class="answer-row" id="answerRow"></div>

                <button type="button" class="cant-see" id="cantSeeBtn" data-allow-repeat>{{ $cv('cv_cant_see', "I can't see a number") }}</button>
            </div>
        </div>

        <div class="text-center mt-6 min-h-[44px]">
            <p id="feedbackText" class="feedback-banner text-lg text-[#5f6873]">{{ $cv('cv_instructions', 'Look at the dots. Which number is hiding in them?') }}</p>
        </div>

        <p class="fine-print">{{ $cv('cv_fine_print', 'This is a fun color game, not a medical test. If you are ever unsure how a child sees colors, ask an eye doctor for a proper check.') }}</p>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const rounds = @json($rounds);
    const totalRounds = {{ $totalRounds }};
    const totalQuestions = {{ $levelData['questions'] }};
    const levelNum = {{ $level }};

    const L = {
        'instructions': @json($cv('cv_instructions', 'Look at the dots. Which number is hiding in them?')),
        'correct': @json($cv('cv_correct', 'You found it!')),
        'try_again': @json($cv('cv_try_again', 'Not quite - look again!')),
        'tricky': @json($cv('cv_tricky', 'That one is tricky! The number is :n.')),
        'level_complete_color_vision': @json(__('game.level_complete_color_vision')),
    };

    // A plate that is answered wrongly twice shows its number and moves on, so a
    // child who truly cannot see it is never stuck. It still counts as missed.
    const MAX_WRONG_TRIES = 2;

    let currentRound = 0;
    let score = {{ $score ?? 0 }};
    let mistakes = {{ $mistakes ?? 0 }};
    let wrongThisRound = 0;
    let playing = false;
    const missedAxes = [];
    let roundMissed = false;

    const canvas = document.getElementById('plateCanvas');
    const ctx = canvas.getContext('2d');
    const answerRow = document.getElementById('answerRow');
    const cantSeeBtn = document.getElementById('cantSeeBtn');
    const feedbackText = document.getElementById('feedbackText');

    // ---- small helpers ----------------------------------------------------
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
        document.getElementById('roundNum').textContent = Math.min(currentRound + 1, totalRounds);
    }

    // Seeded random numbers: one plate always draws the same dots.
    function rng(seed) {
        let a = seed >>> 0;
        return function () {
            a = (a + 0x6D2B79F5) >>> 0;
            let t = a;
            t = Math.imul(t ^ (t >>> 15), t | 1);
            t ^= t + Math.imul(t ^ (t >>> 7), t | 61);
            return ((t ^ (t >>> 14)) >>> 0) / 4294967296;
        };
    }

    // ---- the hidden number -------------------------------------------------
    // The number is drawn once, big and bold, on a hidden canvas. A dot belongs
    // to the number when it lands on that drawing.
    const MASK = 260;

    function drawDigit(g, digit, size, strokeFactor) {
        g.font = 'bold ' + Math.round(size * 0.74) + 'px Arial, "Helvetica Neue", Helvetica, sans-serif';
        g.textAlign = 'center';
        g.textBaseline = 'middle';
        g.lineJoin = 'round';
        g.lineWidth = size * strokeFactor;
        // Bold digits are thinner on some phones, so thicken them with a stroke.
        g.strokeText(String(digit), size / 2, size / 2 + size * 0.035);
        g.fillText(String(digit), size / 2, size / 2 + size * 0.035);
    }

    function buildMask(digit) {
        const m = document.createElement('canvas');
        m.width = m.height = MASK;
        const g = m.getContext('2d', { willReadFrequently: true });
        g.fillStyle = '#000';
        g.strokeStyle = '#000';
        drawDigit(g, digit, MASK, 0.035);
        return g.getImageData(0, 0, MASK, MASK).data;
    }

    // ---- packing the dots --------------------------------------------------
    // Everything is laid out on a 1000 x 1000 plate and scaled to the canvas.
    const PLATE = 1000;
    const PLATE_R = 486;

    function packDots(random) {
        const sizes = [20, 17, 15, 13, 11.5, 10, 8.5, 7];
        const gap = 2.4;
        const cell = 60;
        const cols = Math.ceil(PLATE / cell);
        const grid = new Array(cols * cols);
        const dots = [];

        function fits(x, y, r) {
            const cx = Math.floor(x / cell), cy = Math.floor(y / cell);
            for (let gx = Math.max(0, cx - 1); gx <= Math.min(cols - 1, cx + 1); gx++) {
                for (let gy = Math.max(0, cy - 1); gy <= Math.min(cols - 1, cy + 1); gy++) {
                    const bucket = grid[gx * cols + gy];
                    if (!bucket) continue;
                    for (let i = 0; i < bucket.length; i++) {
                        const o = bucket[i];
                        const dx = o.x - x, dy = o.y - y, min = o.r + r + gap;
                        if (dx * dx + dy * dy < min * min) return false;
                    }
                }
            }
            return true;
        }

        sizes.forEach(function (base, level) {
            const tries = level < 3 ? 1800 : 4200;
            for (let t = 0; t < tries; t++) {
                const r = base * (0.9 + random() * 0.2);
                const angle = random() * Math.PI * 2;
                const dist = Math.sqrt(random()) * (PLATE_R - r - 4);
                const x = PLATE / 2 + Math.cos(angle) * dist;
                const y = PLATE / 2 + Math.sin(angle) * dist;
                if (!fits(x, y, r)) continue;
                const dot = { x: x, y: y, r: r };
                dots.push(dot);
                const key = Math.floor(x / cell) * cols + Math.floor(y / cell);
                (grid[key] = grid[key] || []).push(dot);
            }
        });

        return dots;
    }

    function onFigure(mask, x, y) {
        const px = Math.floor(x / PLATE * MASK), py = Math.floor(y / PLATE * MASK);
        if (px < 0 || py < 0 || px >= MASK || py >= MASK) return false;
        // The alpha channel is 255 where the number was drawn.
        return mask[(py * MASK + px) * 4 + 3] > 127;
    }

    function drawPlate(round) {
        const random = rng(round.seed);
        const mask = buildMask(round.answer);
        const dots = packDots(random);
        const k = canvas.width / PLATE;

        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.fillStyle = '#f7f3ea';
        ctx.beginPath();
        ctx.arc(canvas.width / 2, canvas.height / 2, PLATE_R * k + 6, 0, Math.PI * 2);
        ctx.fill();

        dots.forEach(function (d) {
            // Look at the middle and four points inside the dot, so the edge of
            // the number is not ragged.
            const q = d.r * 0.55;
            const votes = [[0, 0], [q, 0], [-q, 0], [0, q], [0, -q]]
                .filter(function (o) { return onFigure(mask, d.x + o[0], d.y + o[1]); }).length;
            const palette = votes >= 3 ? round.figure : round.ground;
            ctx.fillStyle = palette[Math.floor(random() * palette.length)];
            ctx.beginPath();
            ctx.arc(d.x * k, d.y * k, d.r * k, 0, Math.PI * 2);
            ctx.fill();
        });
    }

    // When a plate is skipped or missed twice, trace the number so the child
    // can see what they were looking for.
    function revealNumber(round) {
        const size = canvas.width;
        const m = document.createElement('canvas');
        m.width = m.height = size;
        const g = m.getContext('2d');
        g.fillStyle = '#182438';
        g.strokeStyle = '#182438';
        drawDigit(g, round.answer, size, 0.035);
        // Keep only the outline: cut the inside back out.
        const inner = document.createElement('canvas');
        inner.width = inner.height = size;
        const ig = inner.getContext('2d');
        ig.fillStyle = '#000';
        ig.strokeStyle = '#000';
        drawDigit(ig, round.answer, size, 0.0);
        g.globalCompositeOperation = 'destination-out';
        g.drawImage(inner, 0, 0);
        ctx.drawImage(m, 0, 0);
    }

    // ---- the round ---------------------------------------------------------
    function loadRound(index) {
        const round = rounds[index];
        playing = true;
        wrongThisRound = 0;
        roundMissed = false;

        drawPlate(round);

        answerRow.innerHTML = '';
        round.options.forEach(function (value) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'answer-btn';
            btn.textContent = value;
            btn.dataset.value = value;
            btn.dataset.correct = value === round.answer ? '1' : '0';
            btn.setAttribute('data-allow-repeat', '');
            btn.addEventListener('click', function () { handleAnswer(btn, value); });
            answerRow.appendChild(btn);
        });

        cantSeeBtn.disabled = false;
        updateHUD();
        setFeedback(L.instructions, '#5f6873');
    }

    function lockAnswers() {
        answerRow.querySelectorAll('.answer-btn').forEach(function (b) { b.disabled = true; });
        cantSeeBtn.disabled = true;
    }

    function handleAnswer(btn, value) {
        if (!playing) return;
        const round = rounds[currentRound];

        if (value === round.answer) {
            playing = false;
            btn.classList.add('correct');
            lockAnswers();
            score++;
            if (roundMissed) missedAxes.push(round.axis);
            setFeedback(L.correct, '#6fa51c');
            showCelebration('target');
            updateHUD();
            setTimeout(nextRound, currentRound + 1 >= totalRounds ? 450 : 1000);
            return;
        }

        mistakes++;
        wrongThisRound++;
        roundMissed = true;
        btn.classList.add('wrong');
        btn.disabled = true;
        updateHUD();

        if (wrongThisRound >= MAX_WRONG_TRIES) {
            giveUpOnPlate();
        } else {
            setFeedback(L.try_again, '#ff595e');
        }
    }

    function giveUpOnPlate() {
        if (!playing) return;
        const round = rounds[currentRound];
        playing = false;
        lockAnswers();
        roundMissed = true;

        const right = answerRow.querySelector('.answer-btn[data-correct="1"]');
        if (right) right.classList.add('correct');
        revealNumber(round);

        // The plate is finished (so the level can be completed), but it stays
        // counted as missed.
        score++;
        missedAxes.push(round.axis);
        setFeedback(L.tricky.replace(':n', round.answer), '#5f6873');
        updateHUD();
        setTimeout(nextRound, 2200);
    }

    cantSeeBtn.addEventListener('click', function () {
        if (!playing) return;
        mistakes++;
        wrongThisRound = MAX_WRONG_TRIES;
        giveUpOnPlate();
    });

    function nextRound() {
        currentRound++;
        if (currentRound >= totalRounds) {
            finishLevel();
            return;
        }
        canvas.classList.add('is-fading');
        setTimeout(function () {
            loadRound(currentRound);
            canvas.classList.remove('is-fading');
        }, 180);
    }

    function finishLevel() {
        showCelebration('sparkles');
        setFeedback(L.level_complete_color_vision, '#e8454a');
        updateHUD();

        // The result card shows at once; the score is saved behind it.
        KidLinkGame.finish({ score: score, total: totalQuestions, mistakes: mistakes });
    }

    // No idle hints here: shaking the right number would give the answer away,
    // and the answer is exactly what this check is trying to find out.
    loadRound(0);
</script>
@endpush
