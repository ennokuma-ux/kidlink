<?php $__env->startSection('title', __('game.shapes_play_title', ['level' => $level])); ?>

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

<div class="min-h-screen p-4 md:p-8" style="background: linear-gradient(135deg, #f0fdf4 0%, #e0f2fe 25%, #fefce8 50%, #ffedd5 75%, #f0fdf4 100%);">
    <div class="mx-auto max-w-4xl">
        <div class="mb-5 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800" style="font-family: 'Fredoka One', cursive;"><?php echo e(__('game.shapes_heading')); ?></h1>
                <p class="text-sm text-gray-500 mt-1"><?php echo e(__('game.level_of_total', ['level' => $level, 'total' => $totalLevels])); ?> · <?php echo e($levelData['description']); ?></p>
            </div>
            <a href="<?php echo e(route('student.games.shapes.stage-select')); ?>" class="bg-white text-gray-500 px-4 py-2 rounded-lg font-bold border border-gray-200 hover:border-orange-400 hover:text-orange-400 transition"><?php echo e(__('game.back')); ?></a>
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
        </div>

        <div id="arena" class="mx-auto" style="width: min(92vw, 560px);">
            <div id="tray" class="bg-white border-2 border-gray-200 rounded-3xl p-5 mb-5 shadow-md">
                <div id="trayRow" class="flex justify-center items-center gap-4 flex-wrap" style="min-height: 80px;"></div>
            </div>
            <div id="board" class="bg-white border-2 border-gray-200 rounded-3xl p-6 shadow-lg">
                <div id="holesRow" class="flex justify-center items-center gap-4 flex-wrap" style="min-height: 120px;"></div>
            </div>
        </div>

        <div class="text-center mt-6" style="min-height: 44px;">
            <p id="feedbackText" class="text-lg font-bold text-gray-500 transition-all"><?php echo e(__('game.instructions_shapes')); ?></p>
        </div>
    </div>
</div>

<style>
.hole {
    width: 96px;
    height: 96px;
    border-radius: 20px;
    background: var(--hole-tint, #fed7aa);
    border: 3px dashed var(--hole-border, #f97316);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.25s ease;
}
.hole.drag-over {
    transform: scale(1.08);
    box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.3);
}
.hole.filled {
    background: var(--hole-color, #f97316);
    border: 3px solid var(--hole-color, #f97316);
    box-shadow: 0 6px 16px rgba(0,0,0,0.15);
}
.hole .shape-img {
    width: 60px;
    height: 60px;
    object-fit: contain;
    pointer-events: none;
    filter: brightness(0);
}
.hole.filled .shape-img {
    filter: none;
}
.tray-item {
    width: 74px;
    height: 74px;
    border-radius: 18px;
    background: var(--item-tint, #fce7f3);
    border: 2px solid var(--item-border, #ec4899);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: grab;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
    touch-action: none;
    user-select: none;
}
.tray-item:active {
    cursor: grabbing;
}
.tray-item:hover {
    transform: scale(1.06);
    box-shadow: 0 8px 20px rgba(0,0,0,0.12);
}
.tray-item.dragging { opacity: 0.25; transform: scale(0.9); }
.tray-item.used { opacity: 0.2; pointer-events: none; transform: scale(0.8); }
.tray-item.wrong { animation: shake 0.4s ease; }
.tray-item .shape-img {
    width: 44px;
    height: 44px;
    object-fit: contain;
    pointer-events: none;
    filter: brightness(0);
}
.drag-ghost {
    position: fixed;
    z-index: 1000;
    width: 84px;
    height: 84px;
    border-radius: 20px;
    background: var(--ghost-bg, #fce7f3);
    border: 2px solid var(--ghost-border, #ec4899);
    box-shadow: 0 12px 32px rgba(0,0,0,0.25);
    display: flex;
    align-items: center;
    justify-content: center;
    pointer-events: none;
    transform: translate(-50%, -50%);
}
.drag-ghost .shape-img { width: 50px; height: 50px; object-fit: contain; pointer-events: none; }
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
    const assetBase = <?php echo json_encode(asset(''), 15, 512) ?>;
    const routeStageSelect = <?php echo json_encode(route('student.games.shapes.stage-select'), 15, 512) ?>;
    const routeNextLevel = <?php echo json_encode(route('student.games.shapes.play', ['level' => $level + 1]), 512) ?>;
    const submitUrl = <?php echo json_encode(route('student.games.shapes.submit', $level), 512) ?>;
    const levelNum = <?php echo e($level); ?>;

    const L = {
        'drag_into_hole': <?php echo json_encode(__('game.drag_into_hole'), 15, 512) ?>,
        'shape_fits': <?php echo json_encode(__('game.shape_fits'), 15, 512) ?>,
        'shape_doesnt_fit': <?php echo json_encode(__('game.shape_doesnt_fit'), 15, 512) ?>,
        'drop_shape': <?php echo json_encode(__('game.drop_shape'), 15, 512) ?>,
        'level_complete_shapes': <?php echo json_encode(__('game.level_complete_shapes'), 15, 512) ?>,
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
    let holesThisRound = 0;
    let playing = false;

    var holesRow = document.getElementById('holesRow');
    var trayRow = document.getElementById('trayRow');
    var feedbackText = document.getElementById('feedbackText');

    function tint(hex, amount) {
        var n = parseInt(hex.replace('#', ''), 16);
        var r = (n >> 16) & 255, g = (n >> 8) & 255, b = n & 255;
        return 'rgb(' + Math.round(r + (255 - r) * amount) + ',' + Math.round(g + (255 - g) * amount) + ',' + Math.round(b + (255 - b) * amount) + ')';
    }

    function showCelebration(emoji) {
        var el = document.createElement('div');
        el.className = 'celebration';
        el.innerHTML = '<i class="ico ico-' + emoji + '" aria-hidden="true"></i>';
        document.body.appendChild(el);
        setTimeout(function() { el.remove(); }, 1000);
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
        var round = rounds[roundIndex];
        filledThisRound = 0;
        holesThisRound = round.holes.length;
        playing = true;

        holesRow.innerHTML = '';
        round.holes.forEach(function(hole, i) {
            var el = document.createElement('div');
            el.className = 'hole';
            el.dataset.type = hole.type;
            el.dataset.index = i;
            el.style.setProperty('--hole-color', hole.color);
            el.style.setProperty('--hole-tint', tint(hole.color, 0.75));
            el.style.setProperty('--hole-border', tint(hole.color, 0.4));
            var img = document.createElement('img');
            img.className = 'shape-img';
            img.src = assetBase + hole.image;
            img.alt = hole.name;
            el.appendChild(img);
            holesRow.appendChild(el);
        });

        trayRow.innerHTML = '';
        round.tray.forEach(function(item, i) {
            var el = document.createElement('div');
            el.className = 'tray-item';
            el.dataset.type = item.type;
            el.dataset.index = i;
            el.style.setProperty('--item-tint', tint(item.color, 0.8));
            el.style.setProperty('--item-border', tint(item.color, 0.5));
            var img = document.createElement('img');
            img.className = 'shape-img';
            img.src = assetBase + item.image;
            img.alt = item.name;
            el.appendChild(img);
            el.addEventListener('pointerdown', function(e) { startDrag(e, el, item); });
            trayRow.appendChild(el);
        });

        updateHUD();
        setFeedback(L.drag_into_hole, '#6b7280');
    }

    function startDrag(e, itemEl, item) {
        if (!playing || itemEl.classList.contains('used')) return;
        e.preventDefault();
        itemEl.classList.add('dragging');

        var ghost = document.createElement('div');
        ghost.className = 'drag-ghost';
        ghost.style.setProperty('--ghost-bg', tint(item.color, 0.8));
        ghost.style.setProperty('--ghost-border', item.color);
        var gImg = document.createElement('img');
        gImg.className = 'shape-img';
        gImg.style.filter = 'none';
        gImg.src = assetBase + item.image;
        gImg.alt = item.name;
        ghost.appendChild(gImg);
        document.body.appendChild(ghost);

        function onMove(e2) {
            ghost.style.left = e2.clientX + 'px';
            ghost.style.top = e2.clientY + 'px';
            var under = document.elementFromPoint(e2.clientX, e2.clientY);
            document.querySelectorAll('.hole.drag-over').forEach(function(h) { h.classList.remove('drag-over'); });
            var hole = under ? under.closest('.hole') : null;
            if (hole && !hole.classList.contains('filled')) {
                hole.classList.add('drag-over');
            }
        }

        function onUp(e2) {
            itemEl.classList.remove('dragging');
            document.querySelectorAll('.hole.drag-over').forEach(function(h) { h.classList.remove('drag-over'); });
            var under = document.elementFromPoint(e2.clientX, e2.clientY);
            var hole = under ? under.closest('.hole') : null;

            if (hole && hole.dataset.type === item.type && !hole.classList.contains('filled')) {
                hole.style.setProperty('--hole-color', item.color);
                hole.classList.add('filled');
                itemEl.classList.add('used');
                score++;
                filledThisRound++;
                setFeedback(L.shape_fits.replace(':name', item.name), '#22c55e');
                showCelebration('star');
                if (filledThisRound >= holesThisRound) {
                    playing = false;
                    setTimeout(function() { nextRound(); }, 900);
                }
            } else if (hole) {
                void itemEl.offsetWidth;
                itemEl.classList.add('wrong');
                mistakes++;
                setFeedback(L.shape_doesnt_fit, '#ef4444');
                setTimeout(function() { itemEl.classList.remove('wrong'); }, 500);
            } else {
                setFeedback(L.drop_shape, '#6b7280');
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
        showCelebration('🌊');
        setTimeout(function() { loadRound(currentRound); }, 700);
    }

    function finishLevel() {
        showCelebration('sparkles');
        setFeedback(L.level_complete_shapes, '#db2777');

        fetch(submitUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ score: score, total: totalQuestions, mistakes: mistakes, time_taken: 0 }),
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var card = document.createElement('div');
            card.className = 'overlay-card';
            card.innerHTML =
                '<div class="card">' +
                    '<div class="text-5xl mb-3">&#127942;</div>' +
                    '<h2 class="text-2xl font-bold text-gray-800 mb-2" style="font-family:\'Fredoka One\', cursive;">' + L.level_num_complete.replace(':level', levelNum) + '</h2>' +
                    '<p class="text-gray-500 mb-1">' + L.score_line.replace(':score', data.score).replace(':total', data.total) + '</p>' +
                    '<p class="text-gray-500 mb-1">' + L.mistakes_line.replace(':count', (data.mistakes || 0)) + '</p>' +
                    '<p class="text-orange-500 font-bold mb-4">' + L.points_earned.replace(':count', data.points_earned) + '</p>' +
                    '<div class="flex justify-center gap-3">' +
                        '<button onclick="location.href=\'' + routeStageSelect + '\'" class="bg-white text-gray-500 px-6 py-2 rounded-lg font-bold border border-gray-200 hover:border-orange-400 hover:text-orange-400 transition">' + L.back_to_map + '</button>' +
                        '<button onclick="location.href=\'' + <?php echo json_encode(route('student.home'), 15, 512) ?> + '\'" class="bg-white text-gray-500 px-6 py-2 rounded-lg font-bold border border-gray-200 hover:border-orange-400 hover:text-orange-400 transition">' + L.home + '</button>' +
                        (data.next_level_unlocked ? '<button onclick="location.href=\'' + routeNextLevel + '\'" class="bg-orange-500 text-white px-6 py-2 rounded-lg font-bold hover:bg-orange-600 transition">' + L.next_level + '</button>' : '') +
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
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kidlink-laravel\resources\views/student/games/shapes/play.blade.php ENDPATH**/ ?>