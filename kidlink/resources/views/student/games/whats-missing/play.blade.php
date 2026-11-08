@extends('layouts.app')

@section('title', __('game.whats_missing_play_title', ['level' => $level]))

@section('content')
{{-- In-game mascot buddy: reacts to correct/wrong/next-stage feedback --}}
<x-mascot buddy side state="start" :size="200" />
{{-- Level 1 is the tutorial stage --}}
<x-tutorial-stage game="whats-missing" :level="$level" />
<x-game-kit game="whats-missing" :level="$level" accent="#1982c4" />

<div class="min-h-screen p-4 md:p-8" style="background: linear-gradient(135deg, #eaf4fb 0%, #d6eaf7 25%, #b3d6ee 50%, #fefce8 75%, #eaf4fb 100%);">
    <div class="mx-auto max-w-4xl">
        <div class="mb-5 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800" style="font-family: 'Fredoka One', cursive;">{{ __('game.whats_missing_name') }}</h1>
                <p class="text-sm text-gray-500 mt-1">{{ __('game.level_of_total', ['level' => $level, 'total' => $totalLevels]) }} · {{ $levelData['description'] }}</p>
            </div>
            <a href="{{ route('student.games.whats-missing.stage-select') }}" class="bg-white text-gray-500 px-4 py-2 rounded-lg font-bold border border-gray-200 hover:border-[#1982c4] hover:text-[#1982c4] transition">{{ __('game.back') }}</a>
        </div>

        <div class="flex flex-wrap items-center justify-center gap-3 mb-6">
            <span class="bg-[#eaf4fb] text-[#146aa3] border-2 border-[#a9d2ec] px-3 py-1 rounded-full text-xs font-extrabold">{{ __('game.round') }} <span id="roundNum">1</span> / <span id="roundTotal">{{ $totalRounds }}</span></span>
            <div class="bg-white rounded-xl shadow-sm px-5 py-2 border border-gray-200 flex items-center gap-2">
                <span class="text-gray-500 font-semibold text-sm">{{ __('game.score_label') }}</span>
                <span class="text-2xl font-bold text-gray-800" id="scoreDisplay">{{ $score ?? 0 }}</span>
                <span class="text-gray-500 font-medium text-sm">/ {{ $levelData['questions'] }}</span>
            </div>
            <span class="inline-flex items-center gap-1.5 bg-white rounded-xl shadow-sm px-5 py-2 border border-gray-200">
                <span class="w-2.5 h-2.5 rounded-full bg-[#ff595e]"></span>
                <span class="text-gray-500 font-semibold text-sm">{{ __('game.mistakes_label') }}</span>
                <span class="font-bold text-gray-800 text-sm" id="mistakesDisplay">{{ $mistakes ?? 0 }}</span>
            </span>
        </div>

        <div id="arena" class="mx-auto" style="width: min(92vw, 640px);">
            <div class="bg-white border-2 border-gray-200 rounded-3xl p-6 md:p-8 mb-6 shadow-lg text-center">
                <div id="sequence" class="flex flex-wrap justify-center items-center gap-4 md:gap-5" style="min-height: 80px;"></div>
            </div>
            <div id="choices" class="flex flex-wrap justify-center items-center gap-3 md:gap-4" style="min-height: 80px;"></div>
        </div>

        <div class="text-center mt-6" style="min-height: 44px;">
            <p id="feedbackText" class="text-lg font-bold text-gray-500 transition-all">{{ __('game.instructions_whats_missing') }}</p>
        </div>
    </div>
</div>

<style>
.number-box {
    width: clamp(56px, 10vw, 72px);
    height: clamp(56px, 10vw, 72px);
    border-radius: 16px;
    background: #f8fafc;
    border: 3px solid #d6eaf7;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: clamp(1.75rem, 5vw, 2.5rem);
    color: #1f2937;
    box-shadow: 0 4px 10px rgba(37, 99, 235, 0.08);
    transition: all 0.2s ease;
}
.number-box.empty {
    background: #fef3c7;
    border: 3px dashed #ffca3a;
    color: transparent;
    position: relative;
}
.number-box.empty::after {
    content: '?';
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #b45309;
    font-size: clamp(2rem, 5vw, 2.75rem);
}
.number-box.revealed {
    background: #ecf7d6;
    border-color: #6fa51c;
    color: #166534;
    transform: scale(1.08);
}
.choice-btn {
    min-width: clamp(48px, 10vw, 64px);
    min-height: clamp(48px, 10vw, 64px);
    padding: 0 12px;
    border-radius: 9999px;
    background: white;
    border: 3px solid #d6eaf7;
    color: #1f2937;
    font-weight: 800;
    font-size: clamp(1.25rem, 4vw, 1.75rem);
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);
    cursor: pointer;
    transition: transform 0.15s ease, box-shadow 0.15s ease, background 0.15s ease, border-color 0.15s ease;
    user-select: none;
}
.choice-btn:hover {
    transform: scale(1.06);
    border-color: #1982c4;
    box-shadow: 0 8px 18px rgba(59, 130, 246, 0.15);
}
.choice-btn:active {
    transform: scale(0.98);
}
.choice-btn.correct {
    background: #8ac926;
    border-color: #6fa51c;
    color: white;
    pointer-events: none;
}
.choice-btn.wrong {
    background: #ff595e;
    border-color: #b91c1c;
    color: white;
    pointer-events: none;
    animation: shake 0.4s ease;
}
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-8px); }
    50% { transform: translateX(8px); }
    75% { transform: translateX(-5px); }
}
.celebration {
    position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%);
    font-size: 3.5rem; z-index: 1000; pointer-events: none;
    animation: floatUp 1s ease-out forwards;
}
@keyframes floatUp {
    0% { opacity: 0; transform: translate(-50%, -30%) scale(0.6); }
    30% { opacity: 1; transform: translate(-50%, -55%) scale(1.15); }
    100% { opacity: 0; transform: translate(-50%, -120%) scale(1); }
}
.overlay-card {
    position: fixed; inset: 0; background: rgba(0,0,0,0.4);
    display: flex; align-items: center; justify-content: center;
    z-index: 900; backdrop-filter: blur(4px);
}
.overlay-card .card {
    background: white; border-radius: 24px; padding: 40px 48px;
    text-align: center; max-width: 400px; box-shadow: 0 24px 60px rgba(0,0,0,0.25);
}
</style>

@push('scripts')
<script>
    const rounds = @json($rounds);
    const totalRounds = {{ $totalRounds }};
    const totalQuestions = {{ $levelData['questions'] }};
    const routeStageSelect = @json(route('student.games.whats-missing.stage-select'));
    const routeNextLevel = @json(route('student.games.whats-missing.play', ['level' => $level + 1]));
    const submitUrl = @json(route('student.games.whats-missing.submit', $level));
    const levelNum = {{ $level }};

    const L = {
        'instructions_whats_missing': @json(__('game.instructions_whats_missing')),
        'missing_found': @json(__('game.missing_found')),
        'missing_try_again': @json(__('game.missing_try_again')),
        'level_complete_whats_missing': @json(__('game.level_complete_whats_missing')),
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

    const sequenceEl = document.getElementById('sequence');
    const choicesEl = document.getElementById('choices');
    const feedbackText = document.getElementById('feedbackText');
    const scoreDisplay = document.getElementById('scoreDisplay');
    const mistakesDisplay = document.getElementById('mistakesDisplay');
    const roundNumEl = document.getElementById('roundNum');

    function showCelebration(name) {
        if (window.KidLinkFx) KidLinkFx.celebrate(name);
    }

    function setFeedback(message, color) {
        feedbackText.textContent = message;
        feedbackText.style.color = color || '#6b7280';
    }

    function updateHUD() {
        scoreDisplay.textContent = score;
        mistakesDisplay.textContent = mistakes;
        roundNumEl.textContent = currentRound + 1;
    }

    function loadRound(roundIndex) {
        const round = rounds[roundIndex];
        playing = true;

        sequenceEl.innerHTML = '';
        round.sequence.forEach(function (val, i) {
            const box = document.createElement('div');
            box.className = 'number-box' + (val === null ? ' empty' : '');
            box.dataset.index = i;
            if (val !== null) {
                box.textContent = val;
            }
            sequenceEl.appendChild(box);
        });

        choicesEl.innerHTML = '';
        round.choices.forEach(function (choice) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'choice-btn';
            btn.textContent = choice;
            btn.addEventListener('click', function () {
                handleChoice(btn, choice, round);
            });
            choicesEl.appendChild(btn);
        });

        updateHUD();
        setFeedback(L.instructions_whats_missing, '#6b7280');
    }

    function revealAnswer(round) {
        const emptyBox = sequenceEl.querySelector('.number-box.empty');
        if (emptyBox) {
            emptyBox.classList.remove('empty');
            emptyBox.classList.add('revealed');
            emptyBox.textContent = round.answer;
        }
    }

    function disableChoices() {
        choicesEl.querySelectorAll('.choice-btn').forEach(function (b) {
            b.disabled = true;
        });
    }

    function handleChoice(btn, choice, round) {
        if (!playing) return;

        if (choice === round.answer) {
            playing = false;
            btn.classList.add('correct');
            revealAnswer(round);
            score++;
            setFeedback(L.missing_found.replace(':number', round.answer), '#8ac926');
            showCelebration('star');
            disableChoices();
            setTimeout(function () {
                nextRound();
            }, currentRound + 1 >= totalRounds ? 350 : 900);
        } else {
            btn.classList.add('wrong');
            mistakes++;
            setFeedback(L.missing_try_again, '#ff595e');
            setTimeout(function () {
                btn.classList.remove('wrong');
            }, 500);
            updateHUD();
        }
    }

    function nextRound() {
        currentRound++;
        if (currentRound >= totalRounds) {
            finishLevel();
            return;
        }
        showCelebration('sparkles');
        setTimeout(function () {
            loadRound(currentRound);
        }, 700);
    }

    function finishLevel() {
        showCelebration('sparkles');
        setFeedback(L.level_complete_whats_missing, '#1982c4');

        // The result card shows at once; the score is saved behind it.
        KidLinkGame.finish({ score: score, total: totalQuestions, mistakes: mistakes });
    }

    // Idle hint: after 15 quiet seconds the missing number and its empty box shake and the mascot gives a clue.
    KidLinkGame.hints(function () {
        const round = rounds[currentRound];
        if (!round || !playing) return null;
        const right = Array.from(choicesEl.querySelectorAll('.choice-btn')).filter(function (btn) {
            return btn.textContent.trim() === String(round.answer) && !btn.disabled;
        });
        if (!right.length) return null;
        const gap = sequenceEl.querySelector('.number-box.empty');
        return {
            round: currentRound,
            els: right,
            cues: gap ? [gap] : [],
            clues: KidLinkGame.clues('tap', { answer: round.answer }),
        };
    });

    loadRound(0);
</script>
@endpush
@endsection
