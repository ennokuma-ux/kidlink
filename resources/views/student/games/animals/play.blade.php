@extends('layouts.app')

@section('title', __('game.animals_play_title', ['level' => $level]))

@push('styles')
<style>
    @import url('/css/fonts.css');

    * { font-family: 'Nunito', sans-serif; }
    h1, h2, h3, h4 { font-family: 'Fredoka One', cursive; }

    .game-bg {
        background: linear-gradient(135deg, #f4f0f8 0%, #e8e0f0 25%, #f4f0f8 50%, #e8e0f0 75%, #f4f0f8 100%);
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
        border-color: #6a4c93;
        color: #6a4c93;
        background: #f4f0f8;
    }

    .btn-primary {
        background: #6a4c93;
        color: white;
        padding: 12px 28px;
        border-radius: 12px;
        font-weight: 800;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 1.05rem;
    }

    .btn-primary:hover { background: #6a4c93; }

    .progress-bar { height: 10px; background: #e8e0f0; border-radius: 6px; overflow: hidden; }
    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #6a4c93, #6a4c93);
        border-radius: 6px;
        transition: width 0.4s ease;
    }

    .round-badge {
        background: #f4f0f8;
        color: #6a4c93;
        border: 2px solid #d4c8e4;
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
        border: 2px solid #e8e0f0;
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
        border: 2px solid #e8e0f0;
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
        background: #e8e0f0;
        border: 2px solid #d4c8e4;
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
        border-color: #6a4c93;
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
        background: #ecf7d6;
        border-color: #8ac926;
        animation: correctPop 0.4s ease;
    }

    .option-btn.wrong {
        background: #fee2e2;
        border-color: #ff595e;
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
        color: #e6b020;
        border: 2px solid #fde68a;
        padding: 4px 10px;
        border-radius: 14px;
        font-size: 0.7rem;
        font-weight: 800;
    }
</style>
@endpush

@section('content')
{{-- In-game mascot buddy: reacts to correct/wrong/next-stage feedback --}}
<x-mascot buddy side state="start" :size="200" />
{{-- Level 1 is the tutorial stage --}}
<x-tutorial-stage game="animals" :level="$level" />
<x-game-kit game="animals" :level="$level" accent="#b8942e" />

<div class="game-bg p-4 md:p-8">
    <div class="mx-auto max-w-4xl">
        <div class="mb-5 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-[#182438]" style="font-family: 'Fredoka One', cursive;">{{ $levelData['title'] }}</h1>
                <p class="text-sm text-[#5f6873] mt-1">{{ __('game.level_of_total', ['level' => $level, 'total' => $totalLevels]) }} · {{ $levelData['description'] }}</p>
            </div>
            <a href="{{ route('student.games.animals.stage-select') }}" class="btn-secondary">
                <svg class="w-5 h-5 inline -mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                {{ __('game.back_to_map') }}
            </a>
        </div>

        <div class="flex flex-wrap items-center justify-center gap-3 mb-6">
            <span class="round-badge">{{ __('game.round') }} <span id="roundNum">1</span> / <span id="roundTotal">{{ $totalRounds }}</span></span>
            <div class="bg-white rounded-xl shadow-sm px-5 py-2 border border-[#e8e0f0] flex items-center gap-2">
                <span class="text-[#5f6873] font-semibold text-sm">{{ __('game.score_label') }}</span>
                <span class="score-display" id="scoreDisplay">{{ $score ?? 0 }}</span>
            </div>
            <span class="inline-flex items-center gap-1.5 bg-white rounded-xl shadow-sm px-5 py-2 border border-[#e8e0f0]">
                <span class="w-2.5 h-2.5 rounded-full bg-[#ff595e]"></span>
                <span class="text-[#5f6873] font-semibold text-sm">{{ __('game.mistakes_label') }}</span>
                <span class="font-bold text-[#182438] text-sm" id="mistakesDisplay">{{ $mistakes ?? 0 }}</span>
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
            <p id="feedbackText" class="feedback-banner text-lg text-[#5f6873]">{{ __('game.instructions_animals') }}</p>
        </div>
    </div>
</div>

<div id="nextButtonContainer" style="display:none;"></div>
@endsection

@push('scripts')
<script>
    const rounds = @json($rounds);
    const totalRounds = {{ $totalRounds }};
    const level = {{ $level }};

    const L = {
        'instructions_animals': @json(__('game.instructions_animals')),
        'animals_correct': @json(__('game.animals_correct')),
        'animals_wrong': @json(__('game.animals_wrong')),
        'round_coming_up': @json(__('game.round_coming_up')),
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


    let currentRound = 0;
    let score = {{ $score ?? 0 }};
    let mistakes = {{ $mistakes ?? 0 }};
    let playing = false;

    const targetEmoji = document.getElementById('targetEmoji');
    const targetName = document.getElementById('targetName');
    const trayRow = document.getElementById('trayRow');
    const feedbackText = document.getElementById('feedbackText');

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
            setFeedback(L.animals_correct.replace(':name', option.name), '#8ac926');
            showCelebration('star');
            updateHUD();
            setTimeout(() => {
                nextRound();
            }, currentRound + 1 >= totalRounds ? 300 : 600);
        } else {
            btn.classList.add('wrong');
            mistakes++;
            setFeedback(L.animals_wrong.replace(':name', option.name), '#ff595e');
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
        setFeedback(L.round_coming_up.replace(':number', currentRound + 1), '#6a4c93');
        showCelebration('sparkles');
        setTimeout(() => loadRound(currentRound), 500);
    }

    function finishLevel() {
        playing = false;
        showCelebration('trophy');
        setFeedback(L.level_complete_job, '#6a4c93');

        // The result card shows at once; the score is saved behind it.
        KidLinkGame.finish({ score: score, total: totalRounds, mistakes: mistakes });
    }

    // Idle hint: after 15 quiet seconds the right animal shakes and the mascot gives a clue.
    KidLinkGame.hints(function () {
        const round = rounds[currentRound];
        if (!round || !playing) return null;
        const right = Array.from(trayRow.querySelectorAll('.option-btn')).filter(function (btn, i) {
            return round.options[i] && round.options[i].isMatch && !btn.classList.contains('correct');
        });
        if (!right.length) return null;
        return {
            round: currentRound,
            els: right,
            cues: [document.getElementById('targetEmoji')],
            clues: KidLinkGame.clues('tap', { name: round.targetName }),
        };
    });

    loadRound(0);
</script>
@endpush
