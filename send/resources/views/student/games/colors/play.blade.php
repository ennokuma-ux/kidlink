@extends('layouts.app')

@section('title', __('game.colors_play_title', ['level' => $level]))

@push('styles')
<style>
    @import url('/css/fonts.css');

    * { font-family: 'Nunito', sans-serif; }
    h1, h2, h3, h4 { font-family: 'Fredoka One', cursive; }

    .game-bg {
        background: linear-gradient(135deg, #fff8f0 0%, #fce7f3 25%, #fdf2f8 50%, #fce7f3 75%, #fff8f0 100%);
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
        border-color: #ec4899;
        color: #ec4899;
        background: #fdf2f8;
    }

    .btn-primary {
        background: #ec4899;
        color: white;
        padding: 12px 28px;
        border-radius: 12px;
        font-weight: 800;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 1.05rem;
    }

    .btn-primary:hover { background: #db2777; }

    .progress-bar { height: 10px; background: #fce7f3; border-radius: 6px; overflow: hidden; }
    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #ec4899, #db2777);
        border-radius: 6px;
        transition: width 0.4s ease;
    }

    .round-badge {
        background: #fdf2f8;
        color: #db2777;
        border: 2px solid #fbcfe8;
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
        border: 2px solid #fce7f3;
        border-radius: 28px;
        padding: 22px;
        box-shadow: 0 12px 30px rgba(236, 72, 153, 0.08);
        margin-bottom: 22px;
    }

    /* The square the circles are matched against. */
    .target-square {
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        width: clamp(76px, 24vw, 116px);
        aspect-ratio: 1 / 1;
        border-radius: 10px;
        border: 3px solid rgba(0, 0, 0, 0.12);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.16);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 4px;
        font-family: 'Fredoka One', cursive;
        font-size: clamp(0.8rem, 3.2vw, 1.05rem);
        line-height: 1.15;
        overflow: hidden;
    }

    .board-stage {
        position: relative;
        width: 100%;
        aspect-ratio: 1 / 1;
    }

    .circle {
        position: absolute;
        transform: translate(-50%, -50%);
        width: clamp(42px, 13vw, 62px);
        aspect-ratio: 1 / 1;
        border-radius: 50%;
        border: 3px solid rgba(0, 0, 0, 0.28);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
        cursor: pointer;
        transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.2s ease;
        -webkit-user-select: none;
        user-select: none;
        touch-action: manipulation;
    }

    .circle:hover {
        transform: translate(-50%, -50%) scale(1.1);
        box-shadow: 0 10px 22px rgba(0, 0, 0, 0.16);
    }

    .circle:active {
        transform: translate(-50%, -50%) scale(0.95);
    }

    /* Colour aid: a letter per colour, so hue is not the only channel. */
    .circle-marker,
    .square-marker {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Fredoka One', cursive;
        font-weight: 600;
        pointer-events: none;
        -webkit-user-select: none;
        user-select: none;
    }

    .circle-marker { font-size: clamp(0.9rem, 3.6vw, 1.4rem); }
    .square-marker { position: static; font-size: clamp(1rem, 4vw, 1.5rem); line-height: 1; }

    body:not(.aid-on) .circle-marker,
    body:not(.aid-on) .square-marker { display: none; }

    /* Right and wrong are marked with a glyph and a neutral double ring, never
       with red/green alone. */
    .circle.found {
        border-color: #111827;
        box-shadow: 0 0 0 3px #ffffff, 0 0 0 6px #111827;
    }

    .circle.found::after,
    .circle.wrong::after {
        content: '';
        position: absolute;
        right: -7px;
        top: -7px;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        border: 2px solid white;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.35);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        font-weight: 900;
        color: white;
    }

    .circle.found::after { content: '\2713'; background: #111827; }
    .circle.wrong::after  { content: '\2718'; background: #7f1d1d; }

    .circle.correct {
        animation: popIn 0.3s ease;
    }

    @keyframes popIn {
        0% { transform: translate(-50%, -50%) scale(0.9); }
        50% { transform: translate(-50%, -50%) scale(1.15); }
        100% { transform: translate(-50%, -50%) scale(1); }
    }

    .circle.wrong {
        animation: shake 0.4s ease;
        border-color: #111827;
    }

    @keyframes shake {
        0%, 100% { transform: translate(-50%, -50%) translateX(0); }
        25% { transform: translate(-50%, -50%) translateX(-8px); }
        50% { transform: translate(-50%, -50%) translateX(8px); }
        75% { transform: translate(-50%, -50%) translateX(-5px); }
    }

    .aid-toggle {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: white;
        color: #5f6873;
        border: 2px solid #fce7f3;
        padding: 7px 14px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 800;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .aid-toggle:hover { border-color: #ec4899; color: #ec4899; }

    .aid-toggle.active {
        background: #182438;
        border-color: #182438;
        color: white;
    }

    .found-badge {
        background: #f0fdf4;
        color: #16a34a;
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

<div class="game-bg p-4 md:p-8">
    <div class="mx-auto max-w-4xl">
        <div class="mb-5 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-[#182438]" style="font-family: 'Fredoka One', cursive;">{{ __('game.colors_heading') }}</h1>
                <p class="text-sm text-[#5f6873] mt-1">{{ __('game.level_of_total', ['level' => $level, 'total' => $totalLevels]) }} · {{ $levelData['description'] }}</p>
            </div>
            <a href="{{ route('student.games.colors.stage-select') }}" class="btn-secondary">
                <svg class="w-5 h-5 inline -mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                {{ __('game.back_to_map') }}
            </a>
        </div>

<div class="flex flex-wrap items-center justify-center gap-3 mb-6">
            <span class="round-badge">{{ __('game.round') }} <span id="roundNum">1</span> / <span id="roundTotal">{{ $totalRounds }}</span></span>
            <span class="found-badge">{{ __('game.found') }} <span id="foundNum">0</span> {{ __('game.of') }} <span id="foundTotal">0</span></span>
            <div class="bg-white rounded-xl shadow-sm px-5 py-2 border border-[#fce7f3] flex items-center gap-2">
                <span class="text-[#5f6873] font-semibold text-sm">{{ __('game.score_label') }}</span>
                <span class="score-display" id="scoreDisplay">{{ $score ?? 0 }}</span>
                <span class="text-[#5f6873] font-medium text-sm">/ {{ $levelData['questions'] }}</span>
            </div>
            <span class="inline-flex items-center gap-1.5 bg-white rounded-xl shadow-sm px-5 py-2 border border-[#fce7f3]">
                <span class="w-2.5 h-2.5 rounded-full bg-[#ef4444]"></span>
                <span class="text-[#5f6873] font-semibold text-sm">{{ __('game.mistakes_label') }}</span>
                <span class="font-bold text-[#182438] text-sm" id="mistakesDisplay">{{ $mistakes ?? 0 }}</span>
            </span>
            <button type="button" class="aid-toggle" id="aidToggle" aria-pressed="false" title="{{ __('game.colour_aid_title') }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12z"/></svg>
                {{ __('game.colour_aid') }}
            </button>
        </div>

        <div class="arena" id="arena">
            <div class="board">
                <div class="board-stage" id="boardStage">
                    <div class="target-square" id="targetSquare">
                        <span class="square-marker" id="targetMarker"></span>
                        <span id="targetName"></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-6 min-h-[44px]">
            <p id="feedbackText" class="feedback-banner text-lg text-[#5f6873]">{{ __('game.instructions_colors') }}</p>
        </div>
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
        'instructions_colors': @json(__('game.instructions_colors')),
        'found_all': @json(__('game.found_all')),
        'more_to_find': @json(__('game.more_to_find')),
        'wrong_keep_looking': @json(__('game.wrong_keep_looking')),
        'level_complete_colors': @json(__('game.level_complete_colors')),
        'level_num_complete': @json(__('game.level_num_complete')),
        'score_line': @json(__('game.score_line')),
        'mistakes_line': @json(__('game.mistakes_line')),
        'points_earned': @json(__('game.points_earned')),
        'back_to_map': @json(__('game.back_to_map')),
        'home': @json(__('game.home')),
        'next_level': @json(__('game.next_level')),
        'score_saved': @json(__('game.score_saved')),
    };

    let currentRound = 0;
    let score = {{ $score ?? 0 }};
    let mistakes = {{ $mistakes ?? 0 }};
    let found = 0;
    let playing = false;

    const boardStage = document.getElementById('boardStage');
    const targetSquare = document.getElementById('targetSquare');
    const targetName = document.getElementById('targetName');
    const targetMarker = document.getElementById('targetMarker');
    const feedbackText = document.getElementById('feedbackText');
    const aidToggle = document.getElementById('aidToggle');

    const AID_KEY = 'kidlink.colorsAid';
    let colorAid = false;
    try {
        colorAid = localStorage.getItem(AID_KEY) === '1';
    } catch (e) {
        colorAid = false;
    }

    function applyAid() {
        document.body.classList.toggle('aid-on', colorAid);
        aidToggle.classList.toggle('active', colorAid);
        aidToggle.setAttribute('aria-pressed', String(colorAid));
    }

    aidToggle.addEventListener('click', function () {
        colorAid = !colorAid;
        try {
            localStorage.setItem(AID_KEY, colorAid ? '1' : '0');
        } catch (e) {
            // Private browsing: the toggle still works for this page.
        }
        applyAid();
    });

    function showCelebration(emoji) {
        const el = document.createElement('div');
        el.className = 'celebration';
        el.innerHTML = emoji;
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
        document.getElementById('roundNum').textContent = currentRound + 1;
        document.getElementById('foundNum').textContent = found;
    }

    // Black or white text on a colour, whichever stays readable.
    function readableTextOn(hex) {
        const n = parseInt(hex.slice(1), 16);
        const [r, g, b] = [(n >> 16) & 255, (n >> 8) & 255, n & 255];
        const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
        return luminance > 0.6 ? '#182438' : '#ffffff';
    }

    // Yellow and white vanish against the white board, and black circles need a
    // lighter rim, so the outline flips with the brightness of the fill.
    function outlineOn(hex) {
        const n = parseInt(hex.slice(1), 16);
        const [r, g, b] = [(n >> 16) & 255, (n >> 8) & 255, n & 255];
        const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
        return luminance > 0.55 ? 'rgba(0, 0, 0, 0.32)' : 'rgba(255, 255, 255, 0.55)';
    }

    function loadRound(roundIndex) {
        const round = rounds[roundIndex];
        playing = true;
        found = 0;

        targetSquare.style.background = round.targetHex;
        targetSquare.style.color = readableTextOn(round.targetHex);
        targetName.textContent = round.targetName;
        targetMarker.textContent = round.targetMarker;
        document.getElementById('foundTotal').textContent = round.matchesNeeded;

        boardStage.querySelectorAll('.circle').forEach((el) => el.remove());

        const count = round.circles.length;
        round.circles.forEach((circle, i) => {
            const el = document.createElement('div');
            el.className = 'circle';
            el.style.background = circle.hex;
            el.style.borderColor = outlineOn(circle.hex);
            el.setAttribute('role', 'button');
            el.setAttribute('aria-label', circle.name);

            const marker = document.createElement('span');
            marker.className = 'circle-marker';
            marker.textContent = circle.marker;
            marker.style.color = readableTextOn(circle.hex);
            el.appendChild(marker);

            // Spread the circles evenly around the square, starting at the top.
            const angle = (2 * Math.PI * i / count) - (Math.PI / 2);
            el.style.left = (50 + 34 * Math.cos(angle)) + '%';
            el.style.top = (50 + 34 * Math.sin(angle)) + '%';

            el.addEventListener('pointerdown', function (e) {
                e.preventDefault();
                handleTap(el, circle);
            });
            boardStage.appendChild(el);
        });

        updateHUD();
        setFeedback(L.instructions_colors, '#5f6873');
    }

    function handleTap(el, circle) {
        if (!playing) return;
        if (el.classList.contains('found')) return;

        if (circle.isMatch) {
            el.classList.add('found');
            el.classList.add('correct');
            found++;
            score++;
            showCelebration('<img src="{{ asset("assets/icons/target.svg") }}" alt="" class="inline h-12 w-12" aria-hidden="true">');

            if (found >= rounds[currentRound].matchesNeeded) {
                playing = false;
                setFeedback(L.found_all, '#22c55e');
                setTimeout(() => nextRound(), 1000);
            } else {
                const left = rounds[currentRound].matchesNeeded - found;
                setFeedback(L.more_to_find.replace(':count', left), '#22c55e');
            }
        } else {
            el.classList.add('wrong');
            mistakes++;
            setFeedback(L.wrong_keep_looking, '#ef4444');
            setTimeout(() => el.classList.remove('wrong'), 400);
        }
        updateHUD();
    }

    function nextRound() {
        currentRound++;
        if (currentRound >= totalRounds) {
            finishLevel();
            return;
        }
        showCelebration('<img src="{{ asset("assets/icons/color-palette.svg") }}" alt="" class="inline h-12 w-12" aria-hidden="true">');
        setTimeout(() => loadRound(currentRound), 300);
    }

    function finishLevel() {
        showCelebration('sparkles');
        setFeedback(L.level_complete_colors, '#db2777');

        fetch("{{ route('student.games.colors.submit', $level) }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                score: score,
                total: totalQuestions,
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
                    <h2 class="text-2xl font-bold text-[#182438] mb-2" style="font-family:'Fredoka One', cursive;">${L.level_num_complete.replace(':level', levelNum)}</h2>
                    <p class="text-[#5f6873] mb-1">${L.score_line.replace(':score', data.score).replace(':total', data.total)}</p>
                    <p class="text-[#5f6873] mb-1">${L.mistakes_line.replace(':count', data.mistakes ?? 0)}</p>
                    <p class="text-[#ec4899] font-bold mb-4">${L.points_earned.replace(':count', data.points_earned)}</p>
                    <div class="flex justify-center gap-3">
                        <button onclick="location.href='{{ route('student.games.colors.stage-select') }}'" class="btn-secondary">${L.back_to_map}</button>
                        <button onclick="location.href='{{ route('student.home') }}'" class="btn-secondary">${L.home}</button>
                        ${data.next_level_unlocked ? `<button onclick="location.href='{{ route('student.games.colors.play', ['level' => $level + 1]) }}'" class="btn-primary">${L.next_level}</button>` : ''}
                    </div>
                </div>`;
            document.body.appendChild(card);
        })
        .catch(() => {
            setFeedback(L.score_saved, '#22c55e');
        });
    }

applyAid();
    loadRound(0);
</script>
@endpush
