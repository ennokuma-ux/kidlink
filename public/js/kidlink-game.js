/**
 * KidLink game kit, shared by every game's play screen.
 *
 *  - KidLinkGame.elapsed()      seconds actually spent playing (tab hidden = paused),
 *                               sent as time_taken so game records show a real time.
 *  - KidLinkGame.hints(fn)      15 quiet seconds -> the right answer(s) shake and the
 *                               mascot gives a clue. See "Hints" below.
 *  - KidLinkGame.finish(opts)   shows the level-complete card straight away and saves
 *                               the score in the background.
 *
 * The page configures it through window.KidLinkGameConfig (see <x-game-kit>).
 */
(function () {
    'use strict';

    if (window.KidLinkGame) {
        return;
    }

    var cfg = window.KidLinkGameConfig || {};
    var text = cfg.text || {};
    var HINT_DELAY = 15000;

    function fill(template, vars) {
        var out = String(template == null ? '' : template);
        Object.keys(vars || {}).forEach(function (key) {
            out = out.split(':' + key).join(String(vars[key]));
        });
        return out;
    }

    function $(selector) {
        return document.querySelector(selector);
    }

    /* ------------------------------------------------------------------ */
    /* Play time                                                           */
    /* ------------------------------------------------------------------ */

    var activeMs = 0;
    var lastTick = Date.now();
    var clockRunning = !document.hidden;

    function tickClock() {
        var now = Date.now();
        if (clockRunning) {
            activeMs += now - lastTick;
        }
        lastTick = now;
    }

    document.addEventListener('visibilitychange', function () {
        tickClock();
        clockRunning = !document.hidden;
    });

    function elapsed() {
        tickClock();
        // A finished level never took 0 seconds, and 0 reads as "not recorded".
        return Math.max(1, Math.round(activeMs / 1000));
    }

    function formatTime(seconds) {
        seconds = Math.max(0, Math.round(seconds));
        if (seconds >= 60) {
            return Math.floor(seconds / 60) + 'm ' + ('0' + (seconds % 60)).slice(-2) + 's';
        }
        return seconds + 's';
    }

    /* ------------------------------------------------------------------ */
    /* Hints                                                               */
    /* ------------------------------------------------------------------ */

    var finished = false;
    var probe = null;
    var lastActivity = Date.now();
    var pointerIsDown = false;
var pointerDownAt = 0;
    var hintLevel = 0;
    var hintSignature = null;
    var shaken = [];

    var SHAKE_FRAMES = [
        { translate: '0 0', rotate: '0deg', offset: 0 },
        { translate: '-6px 0', rotate: '-6deg', offset: 0.15 },
        { translate: '6px -3px', rotate: '6deg', offset: 0.3 },
        { translate: '-5px 0', rotate: '-4deg', offset: 0.45 },
        { translate: '5px -2px', rotate: '4deg', offset: 0.6 },
        { translate: '-3px 0', rotate: '-2deg', offset: 0.75 },
        { translate: '3px 0', rotate: '2deg', offset: 0.9 },
        { translate: '0 0', rotate: '0deg', offset: 1 },
    ];

    function prefersReducedMotion() {
        return !!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);
    }

    function clearShake() {
        shaken.forEach(function (entry) {
            entry.el.classList.remove('kl-hint');
            if (entry.animation) {
                entry.animation.cancel();
            }
        });
        shaken = [];
    }

    // The shake uses the Web Animations API on the individual translate/rotate
    // properties, so it adds to an element's own transform and CSS animation
    // (positioned circles and letters, pop-in) instead of replacing them.
    function shake(elements) {
        clearShake();
        elements.forEach(function (el) {
            if (!el) {
                return;
            }
            var animation = null;
            if (el.animate && !prefersReducedMotion()) {
                try {
                    animation = el.animate(SHAKE_FRAMES, { duration: 900, iterations: 3, easing: 'ease-in-out' });
                } catch (error) {
                    animation = null;
                }
            }
            el.classList.add('kl-hint');
            shaken.push({ el: el, animation: animation });
        });
    }

    function mascotSay(message) {
        var buddy = window.KidLinkMascot;
        if (buddy && typeof buddy.say === 'function') {
            buddy.say(message, 'hint');
        }
    }

    function mascotRest() {
        var buddy = window.KidLinkMascot;
        if (buddy && buddy.state === 'hint') {
            buddy.setState('start');
        }
    }

    function noteActivity() {
        lastActivity = Date.now();
        if (shaken.length) {
            clearShake();
            mascotRest();
        }
    }

    ['pointerdown', 'keydown', 'touchstart'].forEach(function (name) {
        document.addEventListener(name, function (event) {
            if (name === 'pointerdown') {
                pointerIsDown = true;
                pointerDownAt = Date.now();
            }
            noteActivity();
        }, true);
    });

    ['pointerup', 'pointercancel', 'touchend'].forEach(function (name) {
        document.addEventListener(name, function () {
            pointerIsDown = false;
            lastActivity = Date.now();
        }, true);
    });

    // Something else already has the child's attention: do not pile a hint on top.
    function somethingIsOpen() {
        var intro = document.getElementById('kl-tut-intro');
        return !!(intro && intro.classList.contains('is-open')) || !!$('.swal2-container');
    }

    function checkIdle() {
        // A finger still down means a drag is in progress. A missed pointerup
        // (released outside the window) must not silence hints for good, so the
        // flag is only trusted for a few seconds.
        var holding = pointerIsDown && Date.now() - pointerDownAt < 10000;
        if (finished || document.hidden || holding || typeof probe !== 'function') {
            return;
        }
        if (somethingIsOpen()) {
            lastActivity = Date.now();
            return;
        }
        if (Date.now() - lastActivity < HINT_DELAY) {
            return;
        }

        // Whatever happens next, the following hint is another 15 quiet seconds away.
        lastActivity = Date.now();

        var hint = null;
        try {
            hint = probe();
        } catch (error) {
            hint = null; // the round is not set up yet
        }
        if (!hint || !hint.els || !hint.els.length) {
            return;
        }

        var signature = String(hint.round);
        if (signature !== hintSignature) {
            hintSignature = signature;
            hintLevel = 0;
        }

        var clues = hint.clues && hint.clues.length ? hint.clues : [text.buddy_hint || ''];
        var clue = clues[Math.min(hintLevel, clues.length - 1)];
        hintLevel++;

        shake((hint.cues || []).concat(hint.els));

        if (clue) {
            mascotSay(clue);
            // The mascot's speech bubble is hidden on phones, so say it in the
            // feedback line under the game as well.
            if (typeof window.setFeedback === 'function') {
                window.setFeedback(clue, '#6a4c93');
            }
        }
    }

    setInterval(checkIdle, 500);

    /** The clue list for a game's hint key, with :placeholders filled in. */
    function clues(key, vars) {
        var list = (cfg.hints || {})[key] || [];
        return list.map(function (line) {
            return fill(line, vars);
        });
    }

    /* ------------------------------------------------------------------ */
    /* Level complete                                                      */
    /* ------------------------------------------------------------------ */

    var STYLE_ID = 'kl-finish-style';

    function injectStyle() {
        if (document.getElementById(STYLE_ID)) {
            return;
        }
        var accent = cfg.accent || '#f47548';
        var style = document.createElement('style');
        style.id = STYLE_ID;
        style.textContent =
            '.kl-finish{position:fixed;inset:0;z-index:950;display:flex;align-items:center;justify-content:center;padding:16px;' +
            'background:rgba(24,36,56,.55);backdrop-filter:blur(3px);animation:klFinishFade .25s ease}' +
            '.kl-finish__card{background:#fff;border-radius:24px;padding:32px 36px;text-align:center;width:min(100%,440px);' +
            'box-shadow:0 24px 60px rgba(0,0,0,.25);animation:klFinishPop .35s cubic-bezier(.2,1.2,.4,1);font-family:Nunito,Inter,sans-serif}' +
            '.kl-finish__icon{font-size:3rem;line-height:1;margin-bottom:10px;color:#ffca3a}' +
            '.kl-finish__title{font-family:"Fredoka One",cursive;font-size:1.6rem;color:#182438;margin:0 0 4px}' +
            '.kl-finish__sub{color:#5f6873;margin:0 0 4px}' +
            '.kl-finish__stats{margin:12px 0 4px}' +
            '.kl-finish__stats p{margin:2px 0;color:#5f6873}' +
            '.kl-finish__perfect{color:#6fa51c;font-weight:800}' +
            '.kl-finish__points{margin:10px 0 4px;color:' + accent + ';font-weight:800;font-size:1.1rem}' +
            '.kl-finish__status{min-height:22px;margin:4px 0 12px;font-size:.9rem;font-weight:700;color:#5f6873}' +
            '.kl-finish__status.is-ok{color:#6fa51c}.kl-finish__status.is-bad{color:#dc2626}' +
            '.kl-finish__spinner{display:inline-block;width:12px;height:12px;margin-right:6px;vertical-align:-1px;border-radius:50%;' +
            'border:2px solid #d6dde8;border-top-color:' + accent + ';animation:klSpin .7s linear infinite}' +
            '.kl-finish__buttons{display:flex;flex-wrap:wrap;justify-content:center;gap:10px}' +
            '.kl-finish__btn{border:1px solid #e0e7ef;background:#fff;color:#5f6873;padding:10px 18px;border-radius:12px;' +
            'font-weight:800;font-size:1rem;cursor:pointer;transition:transform .15s ease,border-color .15s ease,color .15s ease}' +
            '.kl-finish__btn:hover{border-color:' + accent + ';color:' + accent + '}' +
            '.kl-finish__btn:active{transform:scale(.97)}' +
            '.kl-finish__btn--primary{background:' + accent + ';border-color:' + accent + ';color:#fff}' +
            '.kl-finish__btn--primary:hover{filter:brightness(.92);color:#fff}' +
            '.kl-finish__btn[disabled]{opacity:.6;cursor:progress}' +
            '@keyframes klFinishFade{from{opacity:0}to{opacity:1}}' +
            '@keyframes klFinishPop{from{opacity:0;transform:scale(.9) translateY(10px)}to{opacity:1;transform:none}}' +
            '@keyframes klSpin{to{transform:rotate(360deg)}}' +

            /* Hinted answers keep a bright outline after the shake finishes. */
            '.kl-hint{outline:4px solid rgba(250,204,21,.95);outline-offset:3px}';
        document.head.appendChild(style);
    }

    injectStyle();

    function el(tag, className, content) {
        var node = document.createElement(tag);
        if (className) {
            node.className = className;
        }
        if (content != null) {
            node.textContent = content;
        }
        return node;
    }

    /**
     * A paragraph whose text may carry <b>...</b> emphasis (the translations
     * use it for the numbers). Built from DOM nodes rather than innerHTML, so
     * the bold renders but nothing else in the string is ever treated as markup.
     */
    function richLine(content) {
        var node = document.createElement('p');
        String(content == null ? '' : content).split(/(<b>.*?<\/b>)/i).forEach(function (part) {
            if (!part) {
                return;
            }
            var bold = /^<b>(.*)<\/b>$/i.exec(part);
            if (bold) {
                var strong = document.createElement('b');
                strong.textContent = bold[1];
                node.appendChild(strong);
            } else {
                node.appendChild(document.createTextNode(part));
            }
        });
        return node;
    }

    /** Same formula as LevelCompletionRecorder::percentage(): mistakes count as extra attempts. */
    function accuracy(score, total, mistakes) {
        var attempts = total + mistakes;
        return attempts > 0 ? Math.round((score / attempts) * 100) : 0;
    }

    function finish(options) {
        if (finished) {
            return;
        }
        finished = true;
        clearShake();
        mascotRest();

        options = options || {};
        var score = Number(options.score) || 0;
        var total = Number(options.total) || score;
        var mistakes = Number(options.mistakes) || 0;
        var seconds = elapsed();
        var urls = cfg.urls || {};
        var fin = text.finish || {};

        var saving = true;
        var wantsNext = false;
        var queuedUrl = null;

        var overlay = el('div', 'kl-finish');
        overlay.setAttribute('role', 'dialog');
        overlay.setAttribute('aria-modal', 'true');
        var card = el('div', 'kl-finish__card');
        overlay.appendChild(card);

        var icon = el('div', 'kl-finish__icon');
        var iconGlyph = el('i', 'ico ico-' + (options.icon || 'trophy'));
        iconGlyph.setAttribute('aria-hidden', 'true');
        icon.appendChild(iconGlyph);
        card.appendChild(icon);

        var levelTitle = fill(text.level_num_complete, { level: cfg.level });
        card.appendChild(el('h2', 'kl-finish__title', options.heading || levelTitle));
        if (options.heading) {
            card.appendChild(el('p', 'kl-finish__sub', levelTitle));
        }

        var stats = el('div', 'kl-finish__stats');
        stats.appendChild(richLine(fill(text.score_line, { score: score, total: total })));
        if (mistakes > 0) {
            stats.appendChild(richLine(fill(text.mistakes_line, { count: mistakes })));
        } else {
            stats.appendChild(el('p', 'kl-finish__perfect', fin.perfect));
        }
        stats.appendChild(richLine(fill(fin.accuracy_line, { percent: accuracy(score, total, mistakes) })));
        stats.appendChild(richLine(fill(fin.time_line, { time: formatTime(seconds) })));
        card.appendChild(stats);

        var points = el('p', 'kl-finish__points', fill(text.points_earned, { count: '...' }));
        card.appendChild(points);

        var status = el('p', 'kl-finish__status');
        card.appendChild(status);

        var buttons = el('div', 'kl-finish__buttons');
        var mapBtn = el('button', 'kl-finish__btn', text.back_to_map);
        var homeBtn = el('button', 'kl-finish__btn', text.home);
        var nextBtn = urls.next ? el('button', 'kl-finish__btn kl-finish__btn--primary', text.next_level) : null;
        var retryBtn = el('button', 'kl-finish__btn kl-finish__btn--primary', fin.retry);
        retryBtn.hidden = true;
        [mapBtn, homeBtn, nextBtn, retryBtn].forEach(function (btn) {
            if (btn) {
                btn.type = 'button';
                buttons.appendChild(btn);
            }
        });
        card.appendChild(buttons);
        document.body.appendChild(overlay);

        function setStatus(kind) {
            status.className = 'kl-finish__status' + (kind ? ' is-' + kind : '');
            status.textContent = '';
            if (kind === 'saving') {
                status.className = 'kl-finish__status';
                status.appendChild(el('span', 'kl-finish__spinner'));
                status.appendChild(document.createTextNode(fin.saving));
            } else if (kind === 'ok') {
                status.textContent = fin.saved;
            } else if (kind === 'bad') {
                status.textContent = fin.save_failed;
            }
        }

        // Navigating while the save is still in flight would hit a locked level
        // (or lose the result), so a click made now is carried out once it lands.
        function go(url, isNext) {
            if (!url) {
                return;
            }
            if (saving) {
                queuedUrl = url;
                wantsNext = !!isNext;
                [mapBtn, homeBtn, nextBtn].forEach(function (btn) {
                    if (btn) {
                        btn.disabled = true;
                    }
                });
                return;
            }
            window.location.href = url;
        }

        mapBtn.addEventListener('click', function () { go(urls.stage, false); });
        homeBtn.addEventListener('click', function () { go(urls.home, false); });
        if (nextBtn) {
            nextBtn.addEventListener('click', function () { go(urls.next, true); });
        }

        function settle(data) {
            saving = false;
            retryBtn.hidden = !!data;
            [mapBtn, homeBtn, nextBtn].forEach(function (btn) {
                if (btn) {
                    btn.disabled = false;
                }
            });

            if (data) {
                setStatus('ok');
                var earned = Number(data.points_earned) || 0;
                // A replay that adds nothing says so, rather than a bare "+0 points".
                points.textContent = earned > 0
                    ? fill(text.points_earned, { count: earned })
                    : (fin.no_new_points || '');
                if (nextBtn && !data.next_level_unlocked) {
                    nextBtn.hidden = true;
                }
            } else {
                setStatus('bad');
                points.textContent = '';
                // The save failed, so the next level is not unlocked: do not offer it.
                if (nextBtn) {
                    nextBtn.hidden = true;
                }
            }

            var queued = queuedUrl;
            queuedUrl = null;
            if (queued && (!wantsNext || (data && data.next_level_unlocked))) {
                window.location.href = queued;
            }
        }

        function save() {
            saving = true;
            retryBtn.hidden = true;
            setStatus('saving');

            var token = $('meta[name="csrf-token"]');
            var body = {
                score: score,
                total: total,
                mistakes: mistakes,
                time_taken: seconds,
            };

            fetch(cfg.urls.submit, {
                method: 'POST',
                keepalive: true,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token ? token.content : '',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(body),
            })
                .then(function (response) {
                    return response.json().then(function (data) {
                        return { ok: response.ok, data: data };
                    });
                })
                .then(function (result) {
                    settle(result.ok ? result.data : null);
                })
                .catch(function () {
                    settle(null);
                });
        }

        retryBtn.addEventListener('click', save);
        save();
    }

    window.KidLinkGame = {
        config: cfg,
        elapsed: elapsed,
        hints: function (fn) {
            probe = fn;
        },
        clues: clues,
        finish: finish,
    };
})();
