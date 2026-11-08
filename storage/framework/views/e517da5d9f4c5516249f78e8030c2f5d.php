<?php $__env->startSection('title', __('game.color_sort_stage_title', ['level' => $level])); ?>

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

<div class="min-h-screen p-4 md:p-8" style="background: linear-gradient(135deg, #fdf2f8 0%, #fce7f3 25%, #f5d0fe 50%, #fbcfe8 75%, #fdf2f8 100%);">
    <div class="mx-auto max-w-4xl">
        <div class="mb-5 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800" style="font-family: 'Fredoka One', cursive;"><?php echo e(__('game.color_sort_name')); ?></h1>
                <p class="text-sm text-gray-500 mt-1"><?php echo e(__('game.level_of_total', ['level' => $level, 'total' => $totalLevels])); ?> · <?php echo e($levelData['description']); ?></p>
            </div>
            <a href="<?php echo e(route('student.games.colors-sort.stage-select')); ?>" class="bg-white text-gray-500 px-4 py-2 rounded-lg font-bold border border-gray-200 hover:border-pink-500 hover:text-pink-500 transition"><?php echo e(__('game.back')); ?></a>
        </div>

        <div class="flex flex-wrap items-center justify-center gap-3 mb-6">
            <span class="bg-pink-50 text-pink-600 border-2 border-pink-200 px-3 py-1 rounded-full text-xs font-extrabold"><?php echo e(__('game.round')); ?> <span id="roundNum">1</span> / <span id="roundTotal"><?php echo e($totalRounds); ?></span></span>
            <div class="bg-white rounded-xl shadow-sm px-5 py-2 border border-gray-200 flex items-center gap-2">
                <span class="text-gray-500 font-semibold text-sm"><?php echo e(__('game.score_label')); ?></span>
                <span class="text-2xl font-bold text-gray-800" id="scoreDisplay"><?php echo e($score ?? 0); ?></span>
                <span class="text-gray-500 font-medium text-sm">/ <?php echo e($levelData['questions']); ?></span>
            </div>
            <span class="inline-flex items-center gap-1.5 bg-white rounded-xl shadow-sm px-5 py-2 border border-gray-200">
                <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
                <span class="text-gray-500 font-semibold text-sm"><?php echo e(__('game.mistakes_label')); ?></span>
                <span class="font-bold text-gray-800 text-sm" id="mistakesDisplay"><?php echo e($mistakes ?? 0); ?></span>
            </span>
            <button type="button" class="aid-toggle" id="aidToggle" aria-pressed="false" title="<?php echo e(__('game.colour_aid_title')); ?>">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12z"/></svg>
                <?php echo e(__('game.colour_aid')); ?>

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
            <p id="feedbackText" class="text-lg font-bold text-gray-500 transition-all"><?php echo e(__('game.instructions_colors_sort')); ?></p>
        </div>
    </div>
</div>

<style>
.bucket {
    width: 104px;
    height: 92px;
    border-radius: 10px 10px 22px 22px;
    background: var(--bucket-tint, #fce7f3);
    border: 3px dashed var(--bucket-border, #ec4899);
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
    background: var(--bucket-border, #ec4899);
    opacity: 0.35;
}
.bucket.drag-over {
    transform: scale(1.08);
    box-shadow: 0 0 0 4px rgba(236, 72, 153, 0.3);
}
.bucket.filled {
    background: var(--bucket-color, #ec4899);
    border: 3px solid var(--bucket-color, #ec4899);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
}
.bucket.filled::before { opacity: 0.9; }
.tray-item {
    width: 74px;
    height: 74px;
    border-radius: 50%;
    background: var(--item-color, #ec4899);
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
.drop-marker { font-size: clamp(0.9rem, 3.6vw, 1.4rem); }
.bucket-marker { font-size: clamp(1rem, 4vw, 1.5rem); line-height: 1; }
/* Colour aid: a letter per colour, so hue is not the only channel. */
body:not(.aid-on) .drop-marker,
body:not(.aid-on) .bucket-marker { display: none; }
.bucket.filled .bucket-marker { display: block; }
.aid-toggle {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: white;
    border: 2px solid #f9a8d4;
    color: #db2777;
    border-radius: 9999px;
    padding: 8px 16px;
    font-weight: 700;
    font-size: 0.8rem;
    cursor: pointer;
    transition: all 0.2s ease;
}
.aid-toggle:hover { border-color: #ec4899; color: #ec4899; }
.aid-toggle.active {
    background: #db2777;
    color: white;
    border-color: #db2777;
}
.drag-ghost {
    position: fixed;
    z-index: 1000;
    width: 84px;
    height: 84px;
    border-radius: 50%;
    background: var(--ghost-bg, #fce7f3);
    border: 3px solid rgba(0, 0, 0, 0.08);
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.25);
    display: flex;
    align-items: center;
    justify-content: center;
    pointer-events: none;
    transform: translate(-50%, -50%);
}
.drag-ghost .drop-marker {
    font-size: 1.6rem;
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

<?php $__env->startPush('scripts'); ?>
<script>
    const rounds = <?php echo json_encode($rounds, 15, 512) ?>;
    const totalRounds = <?php echo e($totalRounds); ?>;
    const totalQuestions = <?php echo e($levelData['questions']); ?>;
    const routeStageSelect = <?php echo json_encode(route('student.games.colors-sort.stage-select'), 15, 512) ?>;
    const routeNextLevel = <?php echo json_encode(route('student.games.colors-sort.play', ['level' => $level + 1]), 512) ?>;
    const submitUrl = <?php echo json_encode(route('student.games.colors-sort.submit', $level), 512) ?>;
    const levelNum = <?php echo e($level); ?>;

    const L = {
        'drag_into_bucket': <?php echo json_encode(__('game.drag_into_bucket'), 15, 512) ?>,
        'paint_fits': <?php echo json_encode(__('game.paint_fits'), 15, 512) ?>,
        'paint_wrong_bucket': <?php echo json_encode(__('game.paint_wrong_bucket'), 15, 512) ?>,
        'drop_in_bucket': <?php echo json_encode(__('game.drop_in_bucket'), 15, 512) ?>,
        'level_complete_colors_sort': <?php echo json_encode(__('game.level_complete_colors_sort'), 15, 512) ?>,
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

    function showCelebration(emoji) {
        const el = document.createElement('div');
        el.className = 'celebration';
        el.innerHTML = emoji;
        document.body.appendChild(el);
        setTimeout(() => el.remove(), 1000);
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
                setFeedback(L.paint_fits.replace(':name', item.name), '#22c55e');
                showCelebration('<img src="<?php echo e(asset("assets/icons/color-palette.svg")); ?>" alt="" class="inline h-12 w-12" aria-hidden="true">');
                if (filledThisRound >= bucketsThisRound) {
                    playing = false;
                    setTimeout(function () { nextRound(); }, 900);
                }
            } else if (bucket) {
                void itemEl.offsetWidth;
                itemEl.classList.add('wrong');
                mistakes++;
                setFeedback(L.paint_wrong_bucket, '#ef4444');
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
        showCelebration('<img src="<?php echo e(asset("assets/icons/party-popper.svg")); ?>" alt="" class="inline h-12 w-12" aria-hidden="true">');
        setTimeout(function () { loadRound(currentRound); }, 700);
    }

    function finishLevel() {
        showCelebration('sparkles');
        setFeedback(L.level_complete_colors_sort, '#db2777');

        fetch(submitUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ score: score, total: totalQuestions, mistakes: mistakes, time_taken: 0 }),
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            const card = document.createElement('div');
            card.className = 'overlay-card';
            card.innerHTML =
                '<div class="card">' +
                    '<div class="text-5xl mb-3">&#127942;</div>' +
                    '<h2 class="text-2xl font-bold text-gray-800 mb-2" style="font-family:\'Fredoka One\', cursive;">' + L.level_num_complete.replace(':level', levelNum) + '</h2>' +
                    '<p class="text-gray-500 mb-1">' + L.score_line.replace(':score', data.score).replace(':total', data.total) + '</p>' +
                    '<p class="text-gray-500 mb-1">' + L.mistakes_line.replace(':count', (data.mistakes || 0)) + '</p>' +
                    '<p class="text-pink-500 font-bold mb-4">' + L.points_earned.replace(':count', data.points_earned) + '</p>' +
                    '<div class="flex justify-center gap-3">' +
                        '<button onclick="location.href=\'' + routeStageSelect + '\'" class="bg-white text-gray-500 px-6 py-2 rounded-lg font-bold border border-gray-200 hover:border-pink-500 hover:text-pink-500 transition">' + L.back_to_map + '</button>' +
                        '<button onclick="location.href=\'' + <?php echo json_encode(route('student.home'), 15, 512) ?> + '\'" class="bg-white text-gray-500 px-6 py-2 rounded-lg font-bold border border-gray-200 hover:border-pink-500 hover:text-pink-500 transition">' + L.home + '</button>' +
                        (data.next_level_unlocked ? '<button onclick="location.href=\'' + routeNextLevel + '\'" class="bg-pink-500 text-white px-6 py-2 rounded-lg font-bold hover:bg-pink-600 transition">' + L.next_level + '</button>' : '') +
                    '</div>' +
                '</div>';
            document.body.appendChild(card);
        })
        .catch(function () {
            setFeedback(L.score_saved, '#22c55e');
        });
    }

    loadRound(0);
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kidlink-laravel\resources\views/student/games/colors-sort/play.blade.php ENDPATH**/ ?>