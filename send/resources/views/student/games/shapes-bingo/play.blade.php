@extends('layouts.app')

@section('title', __('game.shapes_bingo_play_title', ['level' => $level]))

@push('styles')
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
        background: #7c3aed;
        color: white;
        padding: 12px 28px;
        border-radius: 12px;
        font-weight: 800;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 1.05rem;
    }

    .btn-primary:hover { background: #6d28d9; }

    .round-badge {
        background: #f5f3ff;
        color: #6d28d9;
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

    .bingo-board {
        width: min(94vw, 620px);
        margin: 0 auto;
    }

    .panel {
        background: white;
        border: 2px solid #ddd6fe;
        border-radius: 24px;
        padding: 18px;
        box-shadow: 0 12px 30px rgba(124, 58, 237, 0.08);
    }

    .caller-panel { margin-bottom: 18px; }

    .zone-label {
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #6d28d9;
        margin-bottom: 12px;
    }

    .caller-row {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 20px;
        flex-wrap: wrap;
        min-height: 116px;
    }

    .caller-shape-wrap {
        width: 100px;
        height: 100px;
        border-radius: 24px;
        background: linear-gradient(160deg, #faf5ff 0%, #ede9fe 100%);
        border: 3px dashed #c4b5fd;
        display: flex;
        align-items: center;
        justify-content: center;
        animation: callerPop 0.35s ease;
    }

    .caller-shape {
        display: block;
        width: 64px;
        height: 64px;
        background-color: var(--caller-color, #7c3aed);
        -webkit-mask-image: var(--caller-img);
        mask-image: var(--caller-img);
        -webkit-mask-repeat: no-repeat;
        mask-repeat: no-repeat;
        -webkit-mask-position: center;
        mask-position: center;
        -webkit-mask-size: contain;
        mask-size: contain;
        filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.12));
    }

    .caller-text { text-align: left; }
    .caller-text .caller-name {
        font-family: 'Fredoka One', cursive;
        font-size: 1.9rem;
        color: #182438;
        line-height: 1.05;
    }
    .caller-text .caller-find { font-size: 0.95rem; color: #5f6873; font-weight: 700; }

    @keyframes callerPop {
        0% { transform: scale(0.85); opacity: 0.4; }
        100% { transform: scale(1); opacity: 1; }
    }

    .board-grid {
        display: grid;
        gap: 12px;
    }

    .bingo-cell {
        position: relative;
        aspect-ratio: 1 / 1;
        border-radius: 18px;
        background: linear-gradient(160deg, #ffffff 0%, #f5f3ff 100%);
        border: 3px solid #ddd6fe;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 4px;
        padding: 6px;
        cursor: pointer;
        transition: transform 0.12s ease, border-color 0.2s ease, background 0.2s ease;
        -webkit-user-select: none;
        user-select: none;
    }

    .bingo-cell:hover {
        transform: translateY(-3px);
        border-color: #a78bfa;
    }

    .bingo-cell .cell-shape {
        width: 54%;
        aspect-ratio: 1 / 1;
        background-color: var(--shape-color, #7c3aed);
        -webkit-mask-image: var(--shape-img);
        mask-image: var(--shape-img);
        -webkit-mask-repeat: no-repeat;
        mask-repeat: no-repeat;
        -webkit-mask-position: center;
        mask-position: center;
        -webkit-mask-size: contain;
        mask-size: contain;
        transition: opacity 0.2s ease;
    }

    .bingo-cell .cell-name {
        font-size: 0.62rem;
        font-weight: 800;
        color: #6d28d9;
        line-height: 1;
    }

    .bingo-cell .dab {
        position: absolute;
        inset: 0;
        display: none;
        align-items: center;
        justify-content: center;
        font-size: 2.2rem;
        color: #22c55e;
        text-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
    }

    .bingo-cell.covered {
        background: linear-gradient(160deg, #ede9fe 0%, #ddd6fe 100%);
        border-color: #8b5cf6;
        transform: none;
        cursor: default;
    }

    .bingo-cell.covered .cell-shape { opacity: 0.25; }
    .bingo-cell.covered .cell-name { opacity: 0.35; }
    .bingo-cell.covered .dab { display: flex; animation: correctPop 0.35s ease; }

    .bingo-cell.wrong {
        border-color: #ef4444;
        animation: shake 0.4s ease;
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
    <div class="mx-auto max-w-3xl">
        <div class="mb-5 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-[#182438]" style="font-family: 'Fredoka One', cursive;">{{ $levelData['title'] }}</h1>
                <p class="text-sm text-[#5f6873] mt-1">{{ __('game.level_of_total', ['level' => $level, 'total' => $totalLevels]) }} · {{ $levelData['description'] }}</p>
            </div>
            <a href="{{ route('student.games.shapes-bingo.stage-select') }}" class="btn-secondary">
                <svg class="w-5 h-5 inline -mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                {{ __('game.back_to_map') }}
            </a>
        </div>

        <div class="flex flex-wrap items-center justify-center gap-3 mb-6">
            <span class="round-badge">{{ __('game.shapes_bingo_cards') }} <span id="coveredNum">0</span> / <span id="coveredTotal">{{ $totalCells }}</span></span>
            <div class="bg-white rounded-xl shadow-sm px-5 py-2 border border-[#ddd6fe] flex items-center gap-2">
                <span class="text-[#5f6873] font-semibold text-sm">{{ __('game.score_label') }}</span>
                <span class="score-display" id="scoreDisplay">{{ $score ?? 0 }}</span>
            </div>
            <span class="inline-flex items-center gap-1.5 bg-white rounded-xl shadow-sm px-5 py-2 border border-[#ddd6fe]">
                <span class="w-2.5 h-2.5 rounded-full bg-[#f05d56]"></span>
                <span class="text-[#5f6873] font-semibold text-sm">{{ __('game.mistakes_label') }}</span>
                <span class="font-bold text-[#182438] text-sm" id="mistakesDisplay">{{ $mistakes ?? 0 }}</span>
            </span>
        </div>

        <div class="bingo-board">
            <div class="panel caller-panel">
                <p class="zone-label">{{ __('game.shapes_bingo_caller') }}</p>
                <div class="caller-row">
                    <div class="caller-shape-wrap">
                        <span id="callerShape" class="caller-shape" role="img" aria-label="{{ $calls[0]['name'] ?? '' }}" style="--caller-img: url('{{ $calls[0]['image'] ?? '' }}'); --caller-color: {{ $calls[0]['color'] ?? '#7c3aed' }};"></span>
                    </div>
                    <div class="caller-text">
                        <div class="caller-name" id="callerName">{{ $calls[0]['name'] ?? '' }}</div>
                        <div class="caller-find" id="callerFind">{{ __('game.shapes_bingo_find', ['name' => $calls[0]['name'] ?? '']) }}</div>
                    </div>
                </div>
            </div>

            <div class="panel">
                <p class="zone-label">{{ __('game.shapes_bingo_card') }}</p>
                <div class="board-grid" id="boardGrid" style="grid-template-columns: repeat({{ $columns }}, minmax(0, 1fr));"></div>
            </div>
        </div>

        <div class="text-center mt-6 min-h-[44px]">
            <p id="feedbackText" class="feedback-banner text-lg text-[#5f6873]">{{ __('game.instructions_shapes_bingo') }}</p>
        </div>
    </div>
</div>

<div id="nextButtonContainer" style="display:none;"></div>
@endsection

@push('scripts')
<script>
    const cells = @json($cells);
    const calls = @json($calls);
    const totalCells = {{ $totalCells }};
    const level = {{ $level }};

    const L = {
        'find': @json(__('game.shapes_bingo_find')),
        'correct': @json(__('game.shapes_bingo_correct')),
        'wrong': @json(__('game.shapes_bingo_wrong')),
        'bingo': @json(__('game.shapes_bingo_complete')),
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

    let callIndex = 0;
    let covered = 0;
    let mistakes = 0;
    const startTime = Date.now();

    const boardGrid = document.getElementById('boardGrid');
    const callerShape = document.getElementById('callerShape');
    const callerName = document.getElementById('callerName');
    const callerFind = document.getElementById('callerFind');
    const feedbackText = document.getElementById('feedbackText');

    const coveredNum = document.getElementById('coveredNum');
    const coveredTotal = document.getElementById('coveredTotal');
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
        coveredNum.textContent = covered;
        scoreDisplay.textContent = covered;
        mistakesDisplay.textContent = mistakes;
    }

    function renderCaller() {
        if (callIndex >= calls.length) {
            callerName.textContent = L.bingo;
            callerFind.textContent = '';
            return;
        }
        const call = calls[callIndex];
        callerShape.style.setProperty('--caller-img', `url("${call.image}")`);
        callerShape.style.setProperty('--caller-color', call.color);
        callerShape.setAttribute('aria-label', call.name);
        callerName.textContent = call.name;
        callerFind.textContent = L.find.replace(':name', call.name);
        setFeedback(L.find.replace(':name', call.name), '#7c3aed');
    }

    function spawnBoard() {
        cells.forEach((cell) => {
            const el = document.createElement('button');
            el.type = 'button';
            el.className = 'bingo-cell';
            el.dataset.type = cell.type;
            el.dataset.name = cell.name;
            el.style.setProperty('--shape-color', cell.color);
            el.style.setProperty('--shape-img', `url("${cell.image}")`);
            el.innerHTML = `
                <span class="cell-shape" role="img" aria-label="${cell.name}"></span>
                <span class="cell-name">${cell.name}</span>
                <span class="dab" aria-hidden="true"><i class="ico ico-checkmark-circle" aria-hidden="true"></i></span>`;
            el.addEventListener('click', () => handleTap(el, cell));
            boardGrid.appendChild(el);
        });
    }

    function handleTap(el, cell) {
        if (el.classList.contains('covered')) return;
        if (callIndex >= calls.length) return;

        const call = calls[callIndex];

        if (cell.type === call.type) {
            el.classList.add('covered');
            covered++;
            updateHUD();
            setFeedback(L.correct.replace(':name', call.name), '#22c55e');
            showCelebration('star');
            callIndex++;
            renderCaller();

            if (covered === totalCells) {
                finishLevel();
            }
            return;
        }

        mistakes++;
        updateHUD();
        setFeedback(L.wrong.replace(':name', call.name), '#ef4444');
        el.classList.add('wrong');
        setTimeout(() => el.classList.remove('wrong'), 450);
    }

    function finishLevel() {
        setFeedback(L.level_complete_job, '#6d28d9');
        showCelebration('sparkles');

        fetch("{{ route('student.games.shapes-bingo.submit', $level) }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                score: covered,
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
                    <div class="text-5xl mb-3 text-[#f472b6]"><i class="ico ico-sparkles" aria-hidden="true"></i></div>
                    <h2 class="text-2xl font-bold text-[#182438] mb-2" style="font-family:'Fredoka One', cursive;">${L.bingo}</h2>
                    <p class="text-[#5f6873] mb-1">${L.level_num_complete.replace(':level', level)}</p>
                    <p class="text-[#5f6873] mb-1">${L.score_line.replace(':score', data.score).replace(':total', data.total)}</p>
                    <p class="text-[#5f6873] mb-1">${L.mistakes_line.replace(':count', data.mistakes ?? 0)}</p>
                    <p class="text-[#6d28d9] font-bold mb-4">${L.points_earned.replace(':count', data.points_earned)}</p>
                    <div class="flex justify-center gap-3">
                        <button onclick="location.href='{{ route('student.games.shapes-bingo.stage-select') }}'" class="btn-secondary">${L.back_to_map}</button>
                        <button onclick="location.href='{{ route('student.home') }}'" class="btn-secondary">${L.home}</button>
                        ${data.next_level_unlocked ? `<button onclick="location.href='{{ route('student.games.shapes-bingo.play', ['level' => $level + 1]) }}'" class="btn-primary">${L.next_level}</button>` : ''}
                    </div>
                </div>`;
            document.body.appendChild(card);
        })
        .catch(() => {
            const card = document.createElement('div');
            card.className = 'overlay-card';
            card.innerHTML = `
                <div class="card">
                    <div class="text-5xl mb-3 text-[#f472b6]"><i class="ico ico-sparkles" aria-hidden="true"></i></div>
                    <h2 class="text-2xl font-bold text-[#182438] mb-2" style="font-family:'Fredoka One', cursive;">${L.bingo}</h2>
                    <p class="text-[#5f6873] mb-1">${L.score_line.replace(':score', covered).replace(':total', totalCells)}</p>
                    <p class="text-[#5f6873] mb-1">${L.mistakes_line.replace(':count', mistakes)}</p>
                    <p class="text-[#5f6873] font-bold mb-4">${L.score_saved}</p>
                    <div class="flex justify-center gap-3">
                        <button onclick="location.href='{{ route('student.games.shapes-bingo.stage-select') }}'" class="btn-secondary">${L.back_to_map}</button>
                        <button onclick="location.href='{{ route('student.home') }}'" class="btn-secondary">${L.home}</button>
                        <button onclick="location.href='{{ route('student.games.shapes-bingo.play', ['level' => $level + 1]) }}'" class="btn-primary">${L.next_level}</button>
                    </div>
                </div>`;
            document.body.appendChild(card);
        });
    }

    spawnBoard();
    renderCaller();
    updateHUD();
</script>
@endpush
