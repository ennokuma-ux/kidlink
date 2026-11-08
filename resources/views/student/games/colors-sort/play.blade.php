@extends('layouts.app')

@section('title', __('game.color_sort_stage_title', ['level' => $level]))

@section('content')
{{-- In-game mascot buddy: reacts to correct/wrong/next-stage feedback --}}
<x-mascot buddy side state="start" :size="200" />
{{-- Level 1 is the tutorial stage --}}
<x-tutorial-stage game="colors-sort" :level="$level" />
<x-game-kit game="colors-sort" :level="$level" accent="#e8454a" />

<div class="min-h-screen p-4 md:p-8" style="background: linear-gradient(135deg, #fff1f1 0%, #ffe9ea 25%, #f5d0fe 50%, #ffc9cb 75%, #fff1f1 100%);">
    <div class="mx-auto max-w-4xl">
        <div class="mb-5 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800" style="font-family: 'Fredoka One', cursive;">{{ __('game.color_sort_name') }}</h1>
                <p class="text-sm text-gray-500 mt-1">{{ __('game.level_of_total', ['level' => $level, 'total' => $totalLevels]) }} · {{ $levelData['description'] }}</p>
            </div>
            <a href="{{ route('student.games.colors-sort.stage-select') }}" class="bg-white text-gray-500 px-4 py-2 rounded-lg font-bold border border-gray-200 hover:border-[#ff595e] hover:text-[#ff595e] transition">{{ __('game.back') }}</a>
        </div>

        <div class="flex flex-wrap items-center justify-center gap-3 mb-6">
            <span class="bg-[#fff1f1] text-[#e8454a] border-2 border-[#ffc9cb] px-3 py-1 rounded-full text-xs font-extrabold">{{ __('game.round') }} <span id="roundNum">1</span> / <span id="roundTotal">{{ $totalRounds }}</span></span>
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
            <button type="button" class="aid-toggle" id="aidToggle" aria-pressed="false" title="{{ __('colour_aid.title') }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12z"/></svg>
                {{ __('colour_aid.button') }}
            </button>
        </div>

        <div id="arena" class="mx-auto" style="width: min(92vw, 560px);">
            <div id="tray" class="bg-white border-2 border-gray-200 rounded-3xl p-5 mb-5 shadow-md">
                <div id="trayRow" class="flex justify-center items-center gap-4 flex-wrap" style="min-height: 80px;"></div>
            </div>
            <div id="board" class="bg-white border-2 border-gray-200 rounded-3xl p-6 shadow-lg">
                <div id="bucketsRow" class="flex justify-center items-end gap-4 flex-wrap" style="min-height: 120px;"></div>
            </div>
        </div>

        <div class="text-center mt-6" style="min-height: 44px;">
            <p id="feedbackText" class="text-lg font-bold text-gray-500 transition-all">{{ __('game.instructions_colors_sort') }}</p>
        </div>
    </div>
</div>

<style>
.bucket {
    width: 104px;
    height: 92px;
    border-radius: 10px 10px 22px 22px;
    background: var(--bucket-tint, #ffe9ea);
    border: 3px dashed var(--bucket-border, #ff595e);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    transition: all 0.25s ease;
}
.bucket::before {
    content: '';
    position: absolute;
    top: -12px;
    left: -6px;
    right: -6px;
    height: 14px;
    border-radius: 8px;
    background: var(--bucket-border, #ff595e);
    opacity: 0.35;
}
.bucket.drag-over {
    transform: scale(1.08);
    box-shadow: 0 0 0 4px rgba(236, 72, 153, 0.3);
}
.bucket.filled {
    background: var(--bucket-color, #ff595e);
    border: 3px solid var(--bucket-color, #ff595e);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
}
.bucket.filled::before { opacity: 0.9; }
.tray-item {
    width: 74px;
    height: 74px;
    border-radius: 50%;
    background: var(--item-color, #ff595e);
    border: 3px solid rgba(0, 0, 0, 0.08);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: grab;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
    touch-action: none;
    user-select: none;
}
.tray-item:active { cursor: grabbing; }
.tray-item:hover {
    transform: scale(1.06);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.16);
}
.tray-item.dragging { opacity: 0.25; transform: scale(0.9); }
.tray-item.used { opacity: 0.2; pointer-events: none; transform: scale(0.8); }
.tray-item.wrong { animation: shake 0.4s ease; }
.drop-marker,
.bucket-marker {
    font-weight: 800;
    pointer-events: none;
    user-select: none;
}
.drop-marker { font-size: clamp(0.5rem, 2.3vw, 0.8rem); line-height: 1; text-align: center; letter-spacing: 0; }
.bucket-marker { font-size: clamp(0.75rem, 3vw, 1.1rem); line-height: 1; text-align: center; letter-spacing: 0; }
/* Colour aid: the colour's whole name on each colour, so hue is not the only channel. */
body:not(.aid-on) .drop-marker,
body:not(.aid-on) .bucket-marker { display: none; }
.bucket.filled .bucket-marker { display: block; }
.aid-toggle {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: white;
    border: 2px solid #f9a8d4;
    color: #e8454a;
    border-radius: 9999px;
    padding: 8px 16px;
    font-weight: 700;
    font-size: 0.8rem;
    cursor: pointer;
    transition: all 0.2s ease;
}
.aid-toggle:hover { border-color: #ff595e; color: #ff595e; }
.aid-toggle.active {
    background: #e8454a;
    color: white;
    border-color: #e8454a;
}
.drag-ghost {
    position: fixed;
    z-index: 1000;
    width: 84px;
    height: 84px;
    border-radius: 50%;
    background: var(--ghost-bg, #ffe9ea);
    border: 3px solid rgba(0, 0, 0, 0.08);
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.25);
    display: flex;
    align-items: center;
    justify-content: center;
    pointer-events: none;
    transform: translate(-50%, -50%);
}
.drag-ghost .drop-marker {
    font-size: 0.85rem;
    font-weight: 800;
}
body:not(.aid-on) .drag-ghost .drop-marker { display: none; }
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
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-9px); }
    50% { transform: translateX(9px); }
    75% { transform: translateX(-6px); }
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
    const routeStageSelect = @json(route('student.games.colors-sort.stage-select'));
    const routeNextLevel = @json(route('student.games.colors-sort.play', ['level' => $level + 1]));
    const submitUrl = @json(route('student.games.colors-sort.submit', $level));
    const levelNum = {{ $level }};

    const L = {
        'drag_into_bucket': @json(__('game.drag_into_bucket')),
        'paint_fits': @json(__('game.paint_fits')),
        'paint_wrong_bucket': @json(__('game.paint_wrong_bucket')),
        'drop_in_bucket': @json(__('game.drop_in_bucket')),
        'level_complete_colors_sort': @json(__('game.level_complete_colors_sort')),
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
    let filledThisRound = 0;
    let bucketsThisRound = 0;
    let playing = false;

    const bucketsRow = document.getElementById('bucketsRow');
    const trayRow = document.getElementById('trayRow');
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
    applyAid();

    // Black or white text on a colour, whichever stays readable.
    function readableTextOn(hex) {
        const n = parseInt(hex.slice(1), 16);
        const r = (n >> 16) & 255, g = (n >> 8) & 255, b = n & 255;
        const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
        return luminance > 0.6 ? '#182438' : '#ffffff';
    }

    function tint(hex, amount) {
        const n = parseInt(hex.replace('#', ''), 16);
        const r = (n >> 16) & 255, g = (n >> 8) & 255, b = n & 255;
        return 'rgb(' + Math.round(r + (255 - r) * amount) + ',' + Math.round(g + (255 - g) * amount) + ',' + Math.round(b + (255 - b) * amount) + ')';
    }

    function showCelebration(name) {
        if (window.KidLinkFx) KidLinkFx.celebrate(name);
    }

    function setFeedback(message, color) {
        feedbackText.textContent = message;
        feedbackText.style.color = color || '#6b7280';
    }

    function updateHUD() {
        document.getElementById('scoreDisplay').textContent = score;
        document.getElementById('mistakesDisplay').textContent = mistakes;
        document.getElementById('roundNum').textContent = currentRound + 1;
    }

    function loadRound(roundIndex) {
        const round = rounds[roundIndex];
        filledThisRound = 0;
        bucketsThisRound = round.buckets.length;
        playing = true;

        bucketsRow.innerHTML = '';
        round.buckets.forEach(function (bucket, i) {
            const el = document.createElement('div');
            el.className = 'bucket';
            el.dataset.type = bucket.type;
            el.dataset.index = i;
            el.style.setProperty('--bucket-tint', tint(bucket.hex, 0.82));
            el.style.setProperty('--bucket-border', bucket.hex);
            const marker = document.createElement('span');
            marker.className = 'bucket-marker';
            marker.textContent = bucket.marker;
            marker.style.color = readableTextOn(bucket.hex);
            el.appendChild(marker);
            bucketsRow.appendChild(el);
        });

        trayRow.innerHTML = '';
        round.tray.forEach(function (item, i) {
            const el = document.createElement('div');
            el.className = 'tray-item';
            el.dataset.type = item.type;
            el.dataset.index = i;
            el.style.setProperty('--item-color', item.hex);
            const marker = document.createElement('span');
            marker.className = 'drop-marker';
            marker.textContent = item.marker;
            marker.style.color = readableTextOn(item.hex);
            el.appendChild(marker);
            el.addEventListener('pointerdown', function (e) { startDrag(e, el, item); });
            trayRow.appendChild(el);
        });

        updateHUD();
        setFeedback(L.drag_into_bucket, '#6b7280');
    }

    function startDrag(e, itemEl, item) {
        if (!playing || itemEl.classList.contains('used')) return;
        e.preventDefault();
        itemEl.classList.add('dragging');

        const ghost = document.createElement('div');
        ghost.className = 'drag-ghost';
        ghost.style.setProperty('--ghost-bg', item.hex);
        const ghostMarker = document.createElement('span');
        ghostMarker.className = 'drop-marker';
        ghostMarker.textContent = item.marker;
        ghostMarker.style.color = readableTextOn(item.hex);
        ghost.appendChild(ghostMarker);
        document.body.appendChild(ghost);

        function onMove(e2) {
            ghost.style.left = e2.clientX + 'px';
            ghost.style.top = e2.clientY + 'px';
            const under = document.elementFromPoint(e2.clientX, e2.clientY);
            document.querySelectorAll('.bucket.drag-over').forEach(function (h) { h.classList.remove('drag-over'); });
            const bucket = under ? under.closest('.bucket') : null;
            if (bucket && !bucket.classList.contains('filled')) {
                bucket.classList.add('drag-over');
            }
        }

        function onUp(e2) {
            itemEl.classList.remove('dragging');
            document.querySelectorAll('.bucket.drag-over').forEach(function (h) { h.classList.remove('drag-over'); });
            const under = document.elementFromPoint(e2.clientX, e2.clientY);
            const bucket = under ? under.closest('.bucket') : null;

            if (bucket && bucket.dataset.type === item.type && !bucket.classList.contains('filled')) {
                bucket.style.setProperty('--bucket-color', item.hex);
                bucket.classList.add('filled');
                itemEl.classList.add('used');
                score++;
                filledThisRound++;
                setFeedback(L.paint_fits.replace(':name', item.name), '#8ac926');
                showCelebration('color-palette');
                if (filledThisRound >= bucketsThisRound) {
                    playing = false;
                    setTimeout(function () { nextRound(); }, currentRound + 1 >= totalRounds ? 350 : 900);
                }
            } else if (bucket) {
                void itemEl.offsetWidth;
                itemEl.classList.add('wrong');
                mistakes++;
                setFeedback(L.paint_wrong_bucket, '#ff595e');
                setTimeout(function () { itemEl.classList.remove('wrong'); }, 500);
            } else {
                setFeedback(L.drop_in_bucket, '#6b7280');
            }

            ghost.remove();
            window.removeEventListener('pointermove', onMove);
            window.removeEventListener('pointerup', onUp);
            updateHUD();
        }

        ghost.style.left = e.clientX + 'px';
        ghost.style.top = e.clientY + 'px';
        window.addEventListener('pointermove', onMove);
        window.addEventListener('pointerup', onUp);
    }

    function nextRound() {
        currentRound++;
        if (currentRound >= totalRounds) { finishLevel(); return; }
        showCelebration('party-popper');
        setTimeout(function () { loadRound(currentRound); }, 700);
    }

    function finishLevel() {
        showCelebration('sparkles');
        setFeedback(L.level_complete_colors_sort, '#e8454a');

        // The result card shows at once; the score is saved behind it.
        KidLinkGame.finish({ score: score, total: totalQuestions, mistakes: mistakes });
    }

    // Idle hint: after 15 quiet seconds a paint drop and its bucket shake and the mascot gives a clue.
    KidLinkGame.hints(function () {
        if (!playing) return null;
        const item = trayRow.querySelector('.tray-item:not(.used)');
        if (!item) return null;
        const bucket = Array.from(bucketsRow.querySelectorAll('.bucket:not(.filled)')).find(function (b) {
            return b.dataset.type === item.dataset.type;
        });
        if (!bucket) return null;
        return { round: currentRound + ':' + item.dataset.index, els: [item, bucket], clues: KidLinkGame.clues('drag') };
    });

    loadRound(0);
</script>
@endpush
@endsection
