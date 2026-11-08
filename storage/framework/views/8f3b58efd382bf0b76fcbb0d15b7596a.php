
<?php
    $isChild = auth()->check() && auth()->user()->role === 'student';
    $offlineConfig = [
        'ping' => url('/up'),
        'child' => $isChild,
        'text' => [
            'checking' => __('connection.checking'),
            'retryIn' => __('connection.retry_in'),
            'backOnline' => __($isChild ? 'connection.child_back_online' : 'connection.back_online'),
        ],
    ];
?>
<style>
    #kl-offline{position:fixed;inset:0;z-index:2147483500;display:none;align-items:center;justify-content:center;padding:20px;background:rgba(24,36,56,.72);-webkit-backdrop-filter:blur(3px);backdrop-filter:blur(3px)}
    #kl-offline.is-visible{display:flex;animation:klo-fade .2s ease-out}
    #kl-offline .klo-card{width:100%;max-width:420px;padding:32px 28px 26px;background:#fff;border-radius:22px;box-shadow:0 18px 50px rgba(0,0,0,.35);text-align:center;font-family:'Inter',system-ui,-apple-system,"Segoe UI",sans-serif;color:#182438}
    #kl-offline .klo-icon{width:92px;height:92px;margin:0 auto 14px;border-radius:50%;background:#fff1ea;display:flex;align-items:center;justify-content:center}
    #kl-offline .klo-icon svg{width:54px;height:54px}
    #kl-offline .klo-zzz{display:none;font:800 15px/1 'Fredoka One',cursive,sans-serif;fill:#1982c4}
    #kl-offline .klo-title{margin:0 0 8px;font-size:22px;font-weight:800;line-height:1.2}
    #kl-offline .klo-body{margin:0 0 18px;font-size:14px;line-height:1.55;color:#5f6873}
    #kl-offline .klo-status{min-height:20px;margin:0 0 14px;font-size:13px;font-weight:600;color:#8b949d}
    #kl-offline .klo-btn{appearance:none;border:0;cursor:pointer;padding:12px 26px;border-radius:12px;background:#ff8555;color:#fff;font:700 15px/1 'Inter',system-ui,-apple-system,"Segoe UI",sans-serif;box-shadow:0 4px 0 #e2663a;transition:transform .08s,box-shadow .08s}
    #kl-offline .klo-btn:active{transform:translateY(3px);box-shadow:0 1px 0 #e2663a}
    #kl-offline .klo-btn:disabled{opacity:.6;cursor:wait}
    #kl-offline .klo-btn:focus-visible{outline:3px solid #1982c4;outline-offset:3px}
    #kl-offline.is-restored .klo-card{box-shadow:0 18px 50px rgba(34,197,94,.45)}
    #kl-offline.is-restored .klo-icon{background:#ecf7d6}
    #kl-offline.is-restored .klo-wifi{stroke:#6fa51c}
    #kl-offline.is-restored .klo-slash{display:none}
    #kl-offline.is-restored .klo-btn{display:none}
    #kl-offline.is-restored .klo-status{color:#6fa51c;font-size:15px;font-weight:800}

    /* Child version: bigger, rounder, brighter. */
    #kl-offline.is-child .klo-card{max-width:460px;border-radius:32px;padding:36px 28px 30px;background:linear-gradient(160deg,#fff 0%,#fff7f1 100%);border:6px solid #ffd9c7}
    #kl-offline.is-child .klo-icon{width:120px;height:120px;background:#e4f2fb;animation:klo-float 2.6s ease-in-out infinite}
    #kl-offline.is-child .klo-icon svg{width:76px;height:76px}
    #kl-offline.is-child .klo-zzz{display:block;animation:klo-zzz 2.2s ease-in-out infinite}
    #kl-offline.is-child .klo-title{font-family:'Fredoka One',cursive,sans-serif;font-size:28px;font-weight:400;color:#1982c4}
    #kl-offline.is-child .klo-body{font-size:17px;font-weight:600}
    #kl-offline.is-child .klo-btn{padding:16px 34px;font-size:18px;border-radius:18px}
    #kl-offline.is-child.is-restored .klo-icon{animation:none}
    #kl-offline.is-child.is-restored .klo-zzz{display:none}

    @keyframes klo-fade{from{opacity:0}to{opacity:1}}
    @keyframes klo-float{0%,100%{transform:translateY(0)}50%{transform:translateY(-8px)}}
    @keyframes klo-zzz{0%,100%{opacity:.25}50%{opacity:1}}
    @media (prefers-reduced-motion:reduce){#kl-offline.is-visible,#kl-offline .klo-icon,#kl-offline .klo-zzz{animation:none!important}}
</style>

<div id="kl-offline" role="alertdialog" aria-modal="true" aria-labelledby="klo-title" aria-describedby="klo-body" class="<?php echo e($isChild ? 'is-child' : ''); ?>">
    <div class="klo-card">
        <div class="klo-icon" aria-hidden="true">
            <svg viewBox="0 0 64 64" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <g class="klo-wifi" stroke="#ff8555" stroke-width="5">
                    <path d="M8 24c13-11 35-11 48 0"/>
                    <path d="M16 33c9-7.5 23-7.5 32 0"/>
                    <path d="M24 42c4.6-3.6 11.4-3.6 16 0"/>
                </g>
                <circle cx="32" cy="51" r="3.2" fill="#ff8555"/>
                <path class="klo-slash" d="M10 8l44 48" stroke="#182438" stroke-width="5"/>
                <text class="klo-zzz" x="42" y="13">z z</text>
            </svg>
        </div>
        <h2 class="klo-title" id="klo-title"><?php echo e($isChild ? __('connection.child_title') : __('connection.title')); ?></h2>
        <p class="klo-body" id="klo-body"><?php echo e($isChild ? __('connection.child_body') : __('connection.body')); ?></p>
        <p class="klo-status" role="status" aria-live="polite"></p>
        <button type="button" class="klo-btn" data-allow-repeat><?php echo e(__('connection.retry')); ?></button>
    </div>
</div>
<script type="application/json" id="kl-offline-config"><?php echo json_encode($offlineConfig, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT, 512) ?></script>
<script>
(function () {
    var root = document.getElementById('kl-offline');
    var cfgEl = document.getElementById('kl-offline-config');
    if (!root || !cfgEl || window.__kidlinkConnectionWatch) return;
    window.__kidlinkConnectionWatch = true;

    var cfg = JSON.parse(cfgEl.textContent);
    var statusEl = root.querySelector('.klo-status');
    var retryBtn = root.querySelector('.klo-btn');

    var IDLE_MS = 30000;                 // heartbeat while everything is fine
    var RETRY_STEPS = [3, 5, 8, 10];     // seconds between retries while offline
    var TIMEOUT_MS = 6000;               // a ping slower than this counts as failed
    var RESTORED_MS = 1400;              // how long "back online" stays on screen

    var nativeFetch = window.fetch.bind(window);
    var offline = false;
    var checking = false;
    var attempt = 0;
    var timer = null;
    var countdown = null;
    var hideTimer = null;

    function setStatus(text) { statusEl.textContent = text || ''; }
    function clearTimers() { clearTimeout(timer); clearInterval(countdown); timer = countdown = null; }

    function show() {
        clearTimeout(hideTimer);
        root.classList.remove('is-restored');
        root.classList.add('is-visible');
        retryBtn.disabled = false;
        if (document.activeElement !== retryBtn) { try { retryBtn.focus({ preventScroll: true }); } catch (e) {} }
    }

    function hide() {
        root.classList.remove('is-visible', 'is-restored');
        setStatus('');
    }

    function goOffline() {
        if (!offline) {
            offline = true;
            attempt = 0;
            show();
            window.dispatchEvent(new CustomEvent('kidlink:offline'));
        }
        scheduleRetry();
    }

    function goOnline() {
        if (!offline) return;
        offline = false;
        attempt = 0;
        clearTimers();
        root.classList.add('is-restored');
        setStatus(cfg.text.backOnline);
        window.dispatchEvent(new CustomEvent('kidlink:online'));
        clearTimeout(hideTimer);
        hideTimer = setTimeout(hide, RESTORED_MS);
        scheduleIdle();
    }

    function scheduleIdle() {
        clearTimers();
        timer = setTimeout(function () {
            if (document.visibilityState === 'visible') check(); else scheduleIdle();
        }, IDLE_MS);
    }

    function scheduleRetry() {
        clearTimers();
        var seconds = RETRY_STEPS[Math.min(attempt, RETRY_STEPS.length - 1)];
        var left = seconds;
        setStatus(cfg.text.retryIn.replace(':seconds', left));
        countdown = setInterval(function () {
            left -= 1;
            if (left > 0) setStatus(cfg.text.retryIn.replace(':seconds', left));
        }, 1000);
        timer = setTimeout(function () { attempt += 1; check(); }, seconds * 1000);
    }

    // Any answer from the server, even an error page, proves we are connected.
    function ping() {
        var controller = typeof AbortController === 'function' ? new AbortController() : null;
        var abort = setTimeout(function () { if (controller) controller.abort(); }, TIMEOUT_MS);
        return nativeFetch(cfg.ping + (cfg.ping.indexOf('?') === -1 ? '?' : '&') + 't=' + Date.now(), {
            method: 'GET',
            cache: 'no-store',
            credentials: 'omit',
            signal: controller ? controller.signal : undefined,
        }).then(function () { clearTimeout(abort); return true; },
                function () { clearTimeout(abort); return false; });
    }

    function check() {
        if (checking) return;
        checking = true;
        clearTimers();
        if (offline) { setStatus(cfg.text.checking); retryBtn.disabled = true; }

        ping().then(function (ok) {
            checking = false;
            retryBtn.disabled = false;
            if (ok) { offline ? goOnline() : scheduleIdle(); }
            else { goOffline(); }
        });
    }

    retryBtn.addEventListener('click', function () { check(); });

    // The browser knows for certain when the adapter itself is down.
    window.addEventListener('offline', goOffline);
    window.addEventListener('online', check);
    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'visible') check();
    });

    // A failed request is only a hint: confirm it with the health ping before
    // putting a screen over the page.
    window.fetch = function (input, init) {
        return nativeFetch(input, init).catch(function (error) {
            // fetch rejects with a TypeError when the network itself failed.
            if (error && error.name === 'TypeError') { setTimeout(check, 0); }
            throw error;
        });
    };

    // While offline, never start a navigation: the browser would replace the
    // page with its own error screen.
    function block(event) {
        if (!offline) return;
        event.preventDefault();
        event.stopImmediatePropagation();
    }
    document.addEventListener('click', function (event) {
        var link = event.target.closest && event.target.closest('a[href]');
        if (link) block(event);
    }, true);
    document.addEventListener('submit', block, true);

    if (navigator.onLine === false) goOffline(); else scheduleIdle();
})();
</script>
<?php /**PATH C:\laragon\www\kidlink-laravel\resources\views/components/connection-status.blade.php ENDPATH**/ ?>