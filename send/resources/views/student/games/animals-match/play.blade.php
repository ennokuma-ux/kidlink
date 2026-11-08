@extends('layouts.app')

@section('title', __('game.animals_match_play_title', ['level' => $level]))

@push('styles')
<style>
    @import url('/css/fonts.css');

    * { font-family: 'Nunito', sans-serif; }
    h1, h2, h3, h4 { font-family: 'Fredoka One', cursive; }

    .game-bg {
        background: linear-gradient(135deg, #f0fdfa 0%, #ccfbf1 25%, #f0fdfa 50%, #e6fffa 75%, #f0fdfa 100%);
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
        border-color: #14b8a6;
        color: #14b8a6;
        background: #f0fdfa;
    }

    .btn-primary {
        background: #0d9488;
        color: white;
        padding: 12px 28px;
        border-radius: 12px;
        font-weight: 800;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 1.05rem;
    }

    .btn-primary:hover { background: #0f766e; }

    .round-badge {
        background: #f0fdfa;
        color: #0f766e;
        border: 2px solid #99f6e4;
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

    .match-board {
        width: min(94vw, 640px);
        margin: 0 auto;
    }

    .panel {
        background: white;
        border: 2px solid #99f6e4;
        border-radius: 24px;
        padding: 18px;
        box-shadow: 0 12px 30px rgba(13, 148, 136, 0.08);
    }

    .targets-panel { margin-bottom: 18px; }

    .zone-label {
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #0f766e;
        margin-bottom: 12px;
    }

    .targets-row {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 14px;
        flex-wrap: wrap;
        min-height: 120px;
    }

    .match-target {
        width: 92px;
        height: 96px;
        border-radius: 20px;
        background: linear-gradient(160deg, #ffffff 0%, #ccfbf1 100%);
        border: 3px solid #5eead4;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 2px;
        transition: transform 0.15s ease, border-color 0.2s ease, background 0.2s ease;
        -webkit-user-select: none;
        user-select: none;
    }

    .game-object-icon { width: 1em; height: 1em; object-fit: contain; vertical-align: middle; }
    .match-target .t-emoji { font-size: 2.6rem; line-height: 1.1; }
    .match-target .t-name { font-size: 0.62rem; font-weight: 800; color: #0f766e; line-height: 1; }

    .match-target.matched {
        background: #ecfdf5;
        border-color: #22c55e;
        animation: correctPop 0.4s ease;
    }

    .match-target.mismatch { animation: shake 0.4s ease; }

    .tray-panel { min-height: 140px; }

    .tray-row {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        min-height: 110px;
    }

    .tray-chip {
        width: 80px;
        height: 84px;
        border-radius: 18px;
        background: white;
        border: 3px solid #14b8a6;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 2px;
        cursor: grab;
        touch-action: none;
        transition: transform 0.15s ease, opacity 0.25s ease, box-shadow 0.15s ease;
        -webkit-user-select: none;
        user-select: none;
        box-shadow: 0 6px 14px rgba(13, 148, 136, 0.12);
    }

    .tray-chip .c-emoji { font-size: 2.2rem; line-height: 1.1; }
    .tray-chip .c-name { font-size: 0.58rem; font-weight: 800; color: #0f766e; line-height: 1; }

    .tray-chip:hover { transform: scale(1.06); }

    .tray-chip.dragging {
        position: fixed;
        margin: 0;
        z-index: 999;
        pointer-events: none;
        box-shadow: 0 16px 34px rgba(13, 148, 136, 0.35);
        transform: scale(1.12);
        opacity: 0.95;
    }

    .tray-chip.matched {
        opacity: 0;
        pointer-events: none;
    }

    .tray-chip.header-hot .grip {
        background: #fed7aa;
    }

    @keyframes correctPop {
        0% { transform: scale(1); }
        40% { transform: scale(1.12); }
        100% { transform: scale(1); }
    }

    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-8px); }
        50% { transform: translateX(8px); }
        75% { transform: translateX(-5px); }
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
</style>
@endpush

@section('content')
{{-- In-game mascot buddy: reacts to correct/wrong/next-stage feedback --}}
<x-mascot buddy side state="start" :size="200" />

<div class="game-bg p-4 md:p-8">
    <div class="mx-auto max-w-4xl">
        <div class="mb-5 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-[#182438]" style="font-family: 'Fredoka One', cursive;">{{ $levelData['title'] }}</h1>
                <p class="text-sm text-[#5f6873] mt-1">{{ __('game.level_of_total', ['level' => $level, 'total' => $totalLevels]) }} · {{ $levelData['description'] }}</p>
            </div>
            <a href="{{ route('student.games.animals-match.stage-select') }}" class="btn-secondary">
                <svg class="w-5 h-5 inline -mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                {{ __('game.back_to_map') }}
            </a>
        </div>

        <div class="flex flex-wrap items-center justify-center gap-3 mb-6">
            <span class="round-badge">{{ __('game.animals_match_pairs') }} <span id="pairsNum">0</span> / <span id="pairsTotal">{{ $totalPairs }}</span></span>
            <div class="bg-white rounded-xl shadow-sm px-5 py-2 border border-[#99f6e4] flex items-center gap-2">
                <span class="text-[#5f6873] font-semibold text-sm">{{ __('game.score_label') }}</span>
                <span class="score-display" id="scoreDisplay">{{ $score ?? 0 }}</span>
            </div>
            <span class="inline-flex items-center gap-1.5 bg-white rounded-xl shadow-sm px-5 py-2 border border-[#99f6e4]">
                <span class="w-2.5 h-2.5 rounded-full bg-[#f05d56]"></span>
                <span class="text-[#5f6873] font-semibold text-sm">{{ __('game.mistakes_label') }}</span>
                <span class="font-bold text-[#182438] text-sm" id="mistakesDisplay">{{ $mistakes ?? 0 }}</span>
            </span>
        </div>

        <div class="match-board">
            <div class="panel targets-panel">
                <p class="zone-label">{{ __('game.animals_match_targets') }}</p>
                <div class="targets-row" id="targetsRow"></div>
            </div>
            <div class="panel tray-panel">
                <p class="zone-label">{{ __('game.animals_match_tray') }}</p>
                <div class="tray-row" id="trayRow"></div>
            </div>
        </div>

        <div class="text-center mt-6 min-h-[44px]">
            <p id="feedbackText" class="feedback-banner text-lg text-[#5f6873]">{{ __('game.instructions_animals_match') }}</p>
        </div>
    </div>
</div>

<div id="nextButtonContainer" style="display:none;"></div>
@endsection

@push('scripts')
<script>
    const targets = @json($targets);
    const tray = @json($tray);
    const totalPairs = {{ $totalPairs }};
    const level = {{ $level }};

    const L = {
        'instructions_animals_match': @json(__('game.instructions_animals_match')),
        'animals_match_correct': @json(__('game.animals_match_correct')),
        'animals_match_wrong': @json(__('game.animals_match_wrong')),
        'level_complete_job': @json(__('game.level_complete_job')),
        'level_num_complete': @json(__('game.level_num_complete')),
        'score_line': @json(__('game.score_line')),
        'mistakes_line': @json(__('game.mistakes_line')),
        'points_earned': @json(__('game.points_earned')),
        'back_to_map': @json(__('game.back_to_map')),
        'home': @json(__('game.home')),
        'next_level': @json(__('game.next_level')),
        'score_saved': @json(__('game.score_saved')),
    };

    let matched = 0;
    let mistakes = 0;
    let grabbed = null;
    let grabOffsetX = 0;
    let grabOffsetY = 0;
    const startTime = Date.now();

    const targetsRow = document.getElementById('targetsRow');
    const trayRow = document.getElementById('trayRow');
    const feedbackText = document.getElementById('feedbackText');

    const pairsNum = document.getElementById('pairsNum');
    const scoreDisplay = document.getElementById('scoreDisplay');
    const mistakesDisplay = document.getElementById('mistakesDisplay');

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
        pairsNum.textContent = matched;
        scoreDisplay.textContent = matched;
        mistakesDisplay.textContent = mistakes;
    }

    function chipHtml(animal) {
        return `
            <span class="c-emoji"><img src="${animal.emoji}" alt="" class="game-object-icon"></span>
            <span class="c-name">${animal.name}</span>`;
    }

    function spawnBoard() {
        targets.forEach((target) => {
            const el = document.createElement('div');
            el.className = 'match-target';
            el.dataset.pair = target.pair;
            el.innerHTML = `
                <span class="t-emoji"><img src="${target.emoji}" alt="" class="game-object-icon"></span>
                <span class="t-name">${target.name}</span>`;
            targetsRow.appendChild(el);
        });

        tray.forEach((animal, index) => {
            const el = document.createElement('div');
            el.className = 'tray-chip';
            el.dataset.pair = animal.pair;
            el.dataset.slot = index;
            el.innerHTML = chipHtml(animal);
            el.addEventListener('pointerdown', (e) => startGrab(e, el));
            trayRow.appendChild(el);
        });
    }

    function startGrab(e, chip) {
        if (chip.classList.contains('matched')) return;
        e.preventDefault();

        grabbed = chip;
        const rect = chip.getBoundingClientRect();
        grabOffsetX = e.clientX - rect.left;
        grabOffsetY = e.clientY - rect.top;

        chip.style.left = rect.left + 'px';
        chip.style.top = rect.top + 'px';
        chip.classList.add('dragging');
        document.body.style.touchAction = 'none';
    }

    window.addEventListener('pointermove', (e) => {
        if (!grabbed) return;
        grabbed.style.left = (e.clientX - grabOffsetX) + 'px';
        grabbed.style.top = (e.clientY - grabOffsetY) + 'px';
    });

    window.addEventListener('pointerup', (e) => {
        if (!grabbed) return;

        const chip = grabbed;
        grabbed = null;
        document.body.style.touchAction = '';

        const hit = document.elementFromPoint(e.clientX, e.clientY);
        const target = hit ? hit.closest('.match-target') : null;
        const name = chip.querySelector('.c-name').textContent;

        const matchFound = target && target.dataset.pair === chip.dataset.pair;

        if (matchFound) {
            chip.classList.remove('dragging');
            chip.classList.add('matched');
            target.classList.add('matched');
            matched++;
            updateHUD();
            setFeedback(L.animals_match_correct.replace(':name', name), '#22c55e');
            showCelebration('star');

            if (matched === totalPairs) {
                finishLevel();
            }
            return;
        }

        mistakes++;
        updateHUD();
        setFeedback(L.animals_match_wrong.replace(':name', name), '#ef4444');

        if (target) {
            target.classList.add('mismatch');
            setTimeout(() => target.classList.remove('mismatch'), 500);
        }

        chip.classList.add('mismatch');
        setTimeout(() => {
            chip.classList.remove('dragging', 'mismatch');
            chip.style.left = '';
            chip.style.top = '';
        }, 350);
    });

    function finishLevel() {
        setFeedback(L.level_complete_job, '#0f766e');
        showCelebration('trophy');

        fetch("{{ route('student.games.animals-match.submit', $level) }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                score: matched,
                total: totalPairs,
                mistakes: mistakes,
                time_taken: Math.round((Date.now() - startTime) / 1000),
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
                    <p class="text-[#0f766e] font-bold mb-4">${L.points_earned.replace(':count', data.points_earned)}</p>
                    <div class="flex justify-center gap-3">
                        <button onclick="location.href='{{ route('student.games.animals-match.stage-select') }}'" class="btn-secondary">${L.back_to_map}</button>
                        <button onclick="location.href='{{ route('student.home') }}'" class="btn-secondary">${L.home}</button>
                        ${data.next_level_unlocked ? `<button onclick="location.href='{{ route('student.games.animals-match.play', ['level' => $level + 1]) }}'" class="btn-primary">${L.next_level}</button>` : ''}
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
                    <p class="text-[#5f6873] mb-1">${L.score_line.replace(':score', matched).replace(':total', totalPairs)}</p>
                    <p class="text-[#5f6873] mb-1">${L.mistakes_line.replace(':count', mistakes)}</p>
                    <p class="text-[#5f6873] font-bold mb-4">${L.score_saved}</p>
                    <div class="flex justify-center gap-3">
                        <button onclick="location.href='{{ route('student.games.animals-match.stage-select') }}'" class="btn-secondary">${L.back_to_map}</button>
                        <button onclick="location.href='{{ route('student.home') }}'" class="btn-secondary">${L.home}</button>
                        <button onclick="location.href='{{ route('student.games.animals-match.play', ['level' => $level + 1]) }}'" class="btn-primary">${L.next_level}</button>
                    </div>
                </div>`;
            document.body.appendChild(card);
        });
    }

    spawnBoard();
    updateHUD();
</script>
@endpush