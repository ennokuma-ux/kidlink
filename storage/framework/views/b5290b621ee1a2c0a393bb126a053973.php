
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['game', 'level' => 1]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['game', 'level' => 1]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $content = trans('tutorial_stage.games.'.$game);
    $valid = (int) $level === 1 && is_array($content) && isset($content['steps']);

    if ($valid) {
        $mascotKey = \App\Support\Mascot::forCurrentStudent();
        $guided = ['numbers' => 1, 'letters' => 2, 'colors' => 2, 'colors-sort' => 2, 'shapes' => 2, 'animals' => 1, 'animals-match' => 1, 'whats-missing' => 1, 'shapes-bingo' => 2][$game] ?? 1;

        $config = [
            'game' => $game,
            'guided' => $guided,
            'text' => [
                'oops' => __('tutorial_stage.oops'),
                'idle' => __('tutorial_stage.idle'),
                'solo' => $content['solo'] ?? '',
                'count' => $content['count'] ?? '',
                'answer' => $content['answer'] ?? '',
                'tap' => $content['tap'] ?? '',
                'drag' => $content['drag'] ?? '',
            ],
        ];
    }
?>

<?php if($valid): ?>
<style>
    body.kl-tut-on { padding-bottom: 132px; }

    /* ---- welcome card ---- */
    #kl-tut-intro { position: fixed; inset: 0; z-index: 1200; display: none; align-items: center; justify-content: center; padding: 16px; background: rgba(24, 36, 56, .6); backdrop-filter: blur(3px); }
    #kl-tut-intro.is-open { display: flex; }
    #kl-tut-intro .kl-tut-card { position: relative; width: min(100%, 460px); max-height: 94vh; overflow-y: auto; padding: 22px 22px 24px; border-radius: 28px; background: #fff; border: 4px solid #ffd9a0; box-shadow: 0 24px 60px rgba(0, 0, 0, .3); text-align: center; animation: klTutPop .35s cubic-bezier(.3, 1.4, .5, 1); }
    #kl-tut-intro .kl-tut-mascot { display: flex; justify-content: center; margin: 4px 0 18px; }
    #kl-tut-intro .kl-tut-tag { display: inline-flex; align-items: center; gap: 6px; padding: 4px 14px; border-radius: 999px; background: #fff0e6; color: #d4572a; font-weight: 800; font-size: .78rem; letter-spacing: .04em; text-transform: uppercase; }
    #kl-tut-intro h2 { margin: 10px 0 4px; font-family: 'Fredoka One', cursive; font-weight: 400; font-size: 1.7rem; line-height: 1.15; color: #182438; }
    #kl-tut-intro .kl-tut-sub { margin: 0 0 12px; color: #5f6873; font-weight: 600; font-size: .95rem; }
    #kl-tut-intro .kl-tut-goal { margin: 0 0 14px; padding: 12px 14px; border-radius: 16px; background: #fff8ec; border: 2px solid #ffd9a0; color: #182438; font-weight: 800; font-size: 1.05rem; line-height: 1.35; }
    #kl-tut-intro ol { list-style: none; margin: 0 0 18px; padding: 0; display: grid; gap: 8px; text-align: left; }
    #kl-tut-intro li { display: flex; align-items: center; gap: 12px; padding: 8px 12px; border-radius: 14px; background: #fffbf0; border: 1px solid #f1e7cc; color: #182438; font-weight: 700; font-size: 1rem; }
    #kl-tut-intro li b { flex: none; display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; border-radius: 50%; background: #ff8555; color: #fff; font-family: 'Fredoka One', cursive; font-weight: 400; font-size: 1rem; }
    #kl-tut-intro .kl-tut-go { width: 100%; padding: 14px 20px; border: 0; border-radius: 16px; background: #ff8555; color: #fff; font-family: 'Fredoka One', cursive; font-size: 1.3rem; cursor: pointer; box-shadow: 0 5px 0 #d4572a; transition: transform .12s ease, box-shadow .12s ease; }
    #kl-tut-intro .kl-tut-go:hover { background: #f47548; }
    #kl-tut-intro .kl-tut-go:active { transform: translateY(3px); box-shadow: 0 2px 0 #d4572a; }

    /* ---- coach bar ---- */
    #kl-tut-bar { position: fixed; z-index: 840; bottom: 14px; left: 50%; transform: translateX(-50%); width: min(560px, calc(100vw - 24px)); display: none; align-items: center; gap: 12px; padding: 10px 12px 10px 14px; border-radius: 22px; background: #fff; border: 4px solid #ffd9a0; box-shadow: 0 12px 30px rgba(24, 36, 56, .22); }
    #kl-tut-bar.is-on { display: flex; }
    #kl-tut-bar .kl-tut-pill { flex: none; padding: 4px 10px; border-radius: 999px; background: #ff8555; color: #fff; font-family: 'Fredoka One', cursive; font-size: .8rem; letter-spacing: .03em; }
    #kl-tut-bar .kl-tut-say { flex: 1; min-width: 0; margin: 0; color: #182438; font-weight: 800; font-size: 1rem; line-height: 1.3; }
    #kl-tut-bar .kl-tut-say.is-oops { color: #d4572a; }
    #kl-tut-bar button { flex: none; padding: 8px 14px; border-radius: 12px; border: 2px solid #ffd9a0; background: #fff8ec; color: #d4572a; font-weight: 800; font-size: .85rem; cursor: pointer; transition: background .15s ease, transform .15s ease; }
    #kl-tut-bar button:hover { background: #ffe9cc; transform: scale(1.04); }
    #kl-tut-bar button[hidden] { display: none; }

    /* ---- pointer: a pulsing ring and a hand on the right answer ---- */
    #kl-tut-ring, #kl-tut-hand { position: fixed; z-index: 850; pointer-events: none; opacity: 0; transition: left .5s ease, top .5s ease, width .5s ease, height .5s ease, opacity .25s ease; }
    #kl-tut-ring { box-sizing: border-box; border: 5px solid #ff8555; background: rgba(255, 133, 85, .14); box-shadow: 0 0 0 4px rgba(255, 255, 255, .9), 0 0 22px 6px rgba(255, 133, 85, .55); animation: klTutPulse 1.1s ease-in-out infinite; }
    #kl-tut-hand { font-size: 2.6rem; line-height: 1; transform: translate(-50%, 0); filter: drop-shadow(0 4px 4px rgba(0, 0, 0, .3)); animation: klTutPoke .9s ease-in-out infinite; }
    #kl-tut-ring.is-on, #kl-tut-hand.is-on { opacity: 1; }

    @keyframes klTutPulse { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.07); } }
    @keyframes klTutPoke { 0%, 100% { margin-top: 0; } 50% { margin-top: -10px; } }
    @keyframes klTutPop { from { transform: scale(.88); opacity: 0; } to { transform: none; opacity: 1; } }

    /* keep clear of the in-game buddy card pinned to the bottom-left */
    @media (min-width: 641px) and (max-width: 1023px) { #kl-tut-bar { left: 204px; right: 12px; width: auto; max-width: 560px; transform: none; } }
    @media (min-width: 1024px) and (max-width: 1199px) { #kl-tut-bar { left: 300px; right: 12px; width: auto; max-width: 560px; transform: none; } }
    @media (max-width: 640px) {
        #kl-tut-bar { left: 108px; right: 10px; width: auto; transform: none; bottom: 10px; gap: 8px; padding: 8px 10px; }
        #kl-tut-bar .kl-tut-pill { display: none; }
        #kl-tut-bar .kl-tut-say { font-size: .9rem; }
        #kl-tut-hand { font-size: 2.1rem; }
    }
    @media (prefers-reduced-motion: reduce) {
        #kl-tut-ring, #kl-tut-hand, #kl-tut-intro .kl-tut-card { animation: none; }
        #kl-tut-ring, #kl-tut-hand { transition: opacity .2s ease; }
    }
</style>

<div id="kl-tut-intro" role="dialog" aria-modal="true" aria-labelledby="kl-tut-title">
    <div class="kl-tut-card">
        <div class="kl-tut-mascot"><?php if (isset($component)) { $__componentOriginal1ad13fbd679f096a735ed64294b0a7b9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1ad13fbd679f096a735ed64294b0a7b9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.mascot','data' => ['circle' => true,'bounce' => true,'state' => 'start','size' => 124,'mascot' => $mascotKey]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mascot'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['circle' => true,'bounce' => true,'state' => 'start','size' => 124,'mascot' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($mascotKey)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1ad13fbd679f096a735ed64294b0a7b9)): ?>
<?php $attributes = $__attributesOriginal1ad13fbd679f096a735ed64294b0a7b9; ?>
<?php unset($__attributesOriginal1ad13fbd679f096a735ed64294b0a7b9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1ad13fbd679f096a735ed64294b0a7b9)): ?>
<?php $component = $__componentOriginal1ad13fbd679f096a735ed64294b0a7b9; ?>
<?php unset($__componentOriginal1ad13fbd679f096a735ed64294b0a7b9); ?>
<?php endif; ?></div>
        <span class="kl-tut-tag"><?php echo e(__('tutorial_stage.tag')); ?> &middot; <?php echo e($content['name'] ?? ''); ?></span>
        <h2 id="kl-tut-title"><?php echo e(__('tutorial_stage.welcome_title')); ?></h2>
        <p class="kl-tut-sub"><?php echo e(__('tutorial_stage.welcome_sub')); ?></p>
        <p class="kl-tut-goal"><?php echo e($content['goal']); ?></p>
        <ol>
            <?php $__currentLoopData = $content['steps']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><b><?php echo e($i + 1); ?></b><span><?php echo e($step); ?></span></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ol>
        <button type="button" class="kl-tut-go" id="kl-tut-go" data-allow-repeat><?php echo e(__('tutorial_stage.start')); ?></button>
    </div>
</div>

<div id="kl-tut-bar" role="status" aria-live="polite">
    <span class="kl-tut-pill"><?php echo e(__('tutorial_stage.tag')); ?></span>
    <p class="kl-tut-say" id="kl-tut-say"></p>
    <button type="button" id="kl-tut-show" data-allow-repeat><?php echo e(__('tutorial_stage.show_me')); ?></button>
    <button type="button" id="kl-tut-how" data-allow-repeat><?php echo e(__('tutorial_stage.how_to')); ?></button>
</div>
<div id="kl-tut-ring" aria-hidden="true"></div>
<div id="kl-tut-hand" aria-hidden="true">&#128070;</div>

<script type="application/json" id="kl-tut-config"><?php echo json_encode($config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT, 512) ?></script>
<script>
window.addEventListener('load', function () {
    'use strict';
    if (window.__klTutorialStage) return;
    window.__klTutorialStage = true;

    var cfg = JSON.parse(document.getElementById('kl-tut-config').textContent);
    var T = cfg.text;
    var intro = document.getElementById('kl-tut-intro');
    var bar = document.getElementById('kl-tut-bar');
    var say = document.getElementById('kl-tut-say');
    var showBtn = document.getElementById('kl-tut-show');
    var howBtn = document.getElementById('kl-tut-how');
    var goBtn = document.getElementById('kl-tut-go');
    var ring = document.getElementById('kl-tut-ring');
    var hand = document.getElementById('kl-tut-hand');
    var SEEN_KEY = 'kidlink_tutorial_stage_seen_' + cfg.game;

    function $(sel) { return document.querySelector(sel); }
    function $$(sel) { return Array.prototype.slice.call(document.querySelectorAll(sel)); }
    function num(sel) { var e = $(sel); return e ? (parseInt(e.textContent, 10) || 0) : 0; }
    function has(el, cls) { return el.classList.contains(cls); }

    // ---- what is the right thing to do right now? -------------------------
    // Each probe only reads the page (and the game's own round data) and
    // returns { key, els }: which instruction to say, and what to point at.
    // Drag games return [piece, destination] so the hand can travel between.
    var PROBES = {
        'numbers': function () {
            var objs = $$('#objectsGrid .object-item');
            if (!objs.length) return null;
            var left = objs.filter(function (o) { return !has(o, 'counted'); });
            if (left.length) return { key: 'count', els: [left[0]] };
            var tray = $$('#trayRow .tray-item').filter(function (t) {
                return Number(t.dataset.num) === objs.length && !has(t, 'correct');
            });
            return tray.length ? { key: 'answer', els: [tray[0]] } : null;
        },
        'letters': function () {
            var m = $$('.letter-option[data-is-match="1"]:not(.matched)');
            return m.length ? { key: 'tap', els: [m[0]] } : null;
        },
        'colors': function () {
            var circles = $$('#boardStage .circle');
            var data = rounds[currentRound].circles;
            for (var i = 0; i < circles.length; i++) {
                if (data[i] && data[i].isMatch && !has(circles[i], 'found')) return { key: 'tap', els: [circles[i]] };
            }
            return null;
        },
        'colors-sort': function () {
            var items = $$('#trayRow .tray-item:not(.used)');
            if (!items.length) return null;
            var dest = $$('#bucketsRow .bucket:not(.filled)').filter(function (b) { return b.dataset.type === items[0].dataset.type; });
            return dest.length ? { key: 'drag', els: [items[0], dest[0]] } : null;
        },
        'shapes': function () {
            var items = $$('#trayRow .tray-item:not(.used)');
            if (!items.length) return null;
            var dest = $$('#holesRow .hole:not(.filled)').filter(function (h) { return h.dataset.type === items[0].dataset.type; });
            return dest.length ? { key: 'drag', els: [items[0], dest[0]] } : null;
        },
        'animals': function () {
            var opts = rounds[currentRound].options;
            var btns = $$('#trayRow .option-btn');
            for (var i = 0; i < btns.length; i++) {
                if (opts[i] && opts[i].isMatch && !has(btns[i], 'correct')) return { key: 'tap', els: [btns[i]] };
            }
            return null;
        },
        'animals-match': function () {
            var chips = $$('#trayRow .tray-chip:not(.matched)');
            if (!chips.length) return null;
            var dest = $$('#targetsRow .match-target:not(.matched)').filter(function (t) { return t.dataset.pair === chips[0].dataset.pair; });
            return dest.length ? { key: 'drag', els: [chips[0], dest[0]] } : null;
        },
        'whats-missing': function () {
            var answer = String(rounds[currentRound].answer);
            var btn = $$('#choices .choice-btn').filter(function (b) { return b.textContent.trim() === answer && !b.disabled; });
            return btn.length ? { key: 'tap', els: [btn[0]] } : null;
        },
        'shapes-bingo': function () {
            var call = calls[callIndex];
            if (!call) return null;
            var cell = $$('#boardGrid .bingo-cell:not(.covered)').filter(function (c) { return c.dataset.type === call.type; });
            return cell.length ? { key: 'tap', els: [cell[0]] } : null;
        }
    };

    function probe() {
        try { return (PROBES[cfg.game] || function () { return null; })(); }
        catch (e) { return null; }   // the game has not set up its round yet
    }

    // ---- state ------------------------------------------------------------
    var introOpen = false;
    var correct = 0;
    var lastScore = num('#scoreDisplay');
    var lastMistakes = num('#mistakesDisplay');
    var lastAction = Date.now();
    var wrongUntil = 0;
    var forced = false;
    var cycleFor = null;
    var cycleStart = 0;
    var lastText = null;
    var finished = false;

    document.body.classList.add('kl-tut-on');

    function openIntro() {
        introOpen = true;
        intro.classList.add('is-open');
        try { goBtn.focus({ preventScroll: true }); } catch (e) {}
    }
    function closeIntro() {
        if (!introOpen) return;
        introOpen = false;
        intro.classList.remove('is-open');
        try { localStorage.setItem(SEEN_KEY, '1'); } catch (e) {}
        lastAction = Date.now();
    }

    goBtn.addEventListener('click', closeIntro);
    howBtn.addEventListener('click', openIntro);
    document.addEventListener('keydown', function (e) { if (introOpen && (e.key === 'Escape')) closeIntro(); });
    showBtn.addEventListener('click', function () { forced = true; lastAction = Date.now(); });

    ['pointerdown', 'keydown'].forEach(function (name) {
        document.addEventListener(name, function () { lastAction = Date.now(); }, true);
    });

    var seen = false;
    try { seen = localStorage.getItem(SEEN_KEY) === '1'; } catch (e) {}
    if (!seen) openIntro();

    // ---- drawing ----------------------------------------------------------
    function hidePointer() { ring.classList.remove('is-on'); hand.classList.remove('is-on'); }

    function point(el) {
        var r = el.getBoundingClientRect();
        if (!r.width || !r.height) { hidePointer(); return; }
        var pad = 6;
        ring.style.left = (r.left - pad) + 'px';
        ring.style.top = (r.top - pad) + 'px';
        ring.style.width = (r.width + pad * 2) + 'px';
        ring.style.height = (r.height + pad * 2) + 'px';
        ring.style.borderRadius = getComputedStyle(el).borderRadius || '16px';
        hand.style.left = (r.left + r.width / 2) + 'px';
        hand.style.top = (r.top + r.height * 0.55) + 'px';
        ring.classList.add('is-on');
        hand.classList.add('is-on');
    }

    function setSay(text, oops) {
        if (text === lastText) return;
        lastText = text;
        say.textContent = text;
        say.classList.toggle('is-oops', !!oops);
    }

    // ---- the loop ---------------------------------------------------------
    function tick() {
        if (finished) return;

        // Level finished: the game's own result card takes over.
        if ($('.overlay-card, .kl-finish')) {
            finished = true;
            hidePointer();
            bar.classList.remove('is-on');
            document.body.classList.remove('kl-tut-on');
            return;
        }

        var now = Date.now();

        var score = num('#scoreDisplay');
        if (score > lastScore) { correct += score - lastScore; forced = false; lastAction = now; wrongUntil = 0; }
        lastScore = score;

        var mistakes = num('#mistakesDisplay');
        if (mistakes > lastMistakes) { wrongUntil = now + 6000; lastAction = now; }
        lastMistakes = mistakes;

        bar.classList.add('is-on');

        var found = introOpen ? null : probe();
        var guided = correct < cfg.guided;
        var wrong = now < wrongUntil;
        var idle = now - lastAction > 7000;
        var hinting = !!found && (guided || wrong || idle || forced);

        // Words
        if (wrong) setSay(T.oops, true);
        else if (hinting) setSay(T[found.key] || T.solo, false);
        else setSay(found ? T.solo : (T.solo || ''), false);
        showBtn.hidden = hinting;

        // Pointer
        if (!hinting) { hidePointer(); cycleFor = null; return; }
        if (cycleFor !== found.els[0]) { cycleFor = found.els[0]; cycleStart = now; }
        var step = found.els.length > 1 ? Math.floor((now - cycleStart) / 1500) % found.els.length : 0;
        point(found.els[step]);
    }

    setInterval(tick, 150);
    tick();
});
</script>
<?php endif; ?>
<?php /**PATH C:\laragon\www\kidlink-laravel\resources\views/components/tutorial-stage.blade.php ENDPATH**/ ?>