{{--
    Transition screens for child mode.

    Place this directly after <body> so it is in the page before the first
    paint. Only the student layout includes it.

    How it works
    - Leaving: a link click to another page does not jump straight away. The
      screen swells in over the old page for a moment, then the browser goes
      to the new page.
    - Arriving: when a student page opens straight after another one, the new
      page starts covered by the same screen and lifts it once it has loaded,
      so the two pages feel like one continuous change.
    - The arriving half is also what covers moves started by game code
      (location.href after a level), form posts and entering child mode, which
      cannot be intercepted on the way out. The old page leaves a note in
      sessionStorage ("klTransition") for the new one.
    - Message and colours come from the destination: home, a game's level map,
      a level, or coming back after finishing one.
    - A refresh, the back button and any note older than 10 seconds show
      nothing. A CSS animation hides the screen by itself after 8 seconds, so a
      script problem can never leave a child stuck behind it.
    - Opt a link out with data-no-transition.
--}}
@php
    $student = \App\Support\Mascot::currentStudent();
    $mascot = \App\Support\Mascot::forSex($student?->sex);

    $games = collect(\App\Games\GameCatalog::all())
        ->mapWithKeys(fn ($game) => [$game['slug'] => [
            'name' => $game['name'],
            'gradient' => $game['gradient'],
            'img' => asset('storage/img/'.$game['img']),
        ]])
        ->all();

    $transitionConfig = [
        'base' => rtrim((string) parse_url(url('/student'), PHP_URL_PATH), '/'),
        'name' => $student?->first_name ?? '',
        'gradient' => 'linear-gradient(135deg, #258cc5 0%, #ff8555 100%)',
        'mascot' => [
            'wave' => asset(\App\Support\Mascot::path($mascot, 'start')),
            'cheer' => asset(\App\Support\Mascot::path($mascot, 'correct')),
        ],
        'games' => $games,
        'text' => collect([
            'home_title', 'home_sub', 'home_back_title', 'home_back_sub',
            'game_title', 'game_sub', 'game_back_title', 'game_back_sub',
            'level_title', 'level_sub', 'enter_title', 'enter_sub',
            'default_title', 'default_sub',
        ])->mapWithKeys(fn ($key) => [$key => __('transition.'.$key)])->all(),
    ];
@endphp
<style>
    #kl-transition{position:fixed;inset:0;z-index:2147483200;display:flex;align-items:center;justify-content:center;overflow:hidden;background:var(--klt-bg,linear-gradient(135deg,#258cc5,#ff8555));color:#fff;text-align:center;font-family:'Inter',system-ui,-apple-system,"Segoe UI",sans-serif;visibility:hidden;pointer-events:none;clip-path:circle(0% at 50% 50%);opacity:0;transition:clip-path .38s cubic-bezier(.65,0,.35,1),opacity .2s ease-out}
    /* last resort on .is-in: never stay up longer than 8 seconds (klt-failsafe) */
    #kl-transition.is-in{visibility:visible;pointer-events:auto;clip-path:circle(150% at 50% 50%);opacity:1;animation:klt-failsafe 0s linear 8s forwards}
    #kl-transition.is-instant{transition:none}
    #kl-transition.is-out{visibility:visible;pointer-events:none;clip-path:circle(0% at 50% 50%);opacity:0;transition:clip-path .45s cubic-bezier(.65,0,.35,1),opacity .45s ease-in}
    #kl-transition .klt-shade{position:absolute;inset:0;background:radial-gradient(circle at 50% 38%,rgba(255,255,255,.28),rgba(255,255,255,0) 55%)}
    #kl-transition .klt-star{position:absolute;color:rgba(255,255,255,.75);font-size:28px;line-height:1;animation:klt-twinkle 2.4s ease-in-out infinite}
    #kl-transition .klt-star:nth-child(2){left:8%;top:18%;font-size:34px}
    #kl-transition .klt-star:nth-child(3){right:10%;top:14%;animation-delay:.5s}
    #kl-transition .klt-star:nth-child(4){left:14%;bottom:20%;animation-delay:1s;font-size:22px}
    #kl-transition .klt-star:nth-child(5){right:16%;bottom:24%;animation-delay:1.5s;font-size:38px}
    #kl-transition .klt-star:nth-child(6){left:46%;top:8%;animation-delay:.9s;font-size:20px}
    #kl-transition .klt-body{position:relative;display:flex;flex-direction:column;align-items:center;gap:10px;padding:24px;max-width:92vw}
    #kl-transition .klt-figure{position:relative;width:min(44vw,190px);height:min(44vw,190px);display:flex;align-items:center;justify-content:center}
    #kl-transition .klt-figure::before{content:"";position:absolute;inset:6%;border-radius:50%;background:rgba(255,255,255,.28);box-shadow:0 0 0 14px rgba(255,255,255,.14)}
    #kl-transition .klt-mascot{position:relative;width:100%;height:100%;object-fit:contain;filter:drop-shadow(0 8px 10px rgba(0,0,0,.22));animation:klt-bounce .9s ease-in-out infinite}
    #kl-transition .klt-badge{position:absolute;right:-6%;bottom:2%;width:34%;height:34%;border-radius:22%;background:#fff;box-shadow:0 6px 14px rgba(0,0,0,.25);display:none;align-items:center;justify-content:center;transform:rotate(8deg);animation:klt-pop .5s cubic-bezier(.3,1.6,.5,1) .15s both}
    #kl-transition .klt-badge img{width:70%;height:70%;object-fit:contain}
    #kl-transition .klt-title{margin:10px 0 0;font-family:'Fredoka One',cursive,sans-serif;font-weight:400;font-size:clamp(30px,7vw,52px);line-height:1.1;text-shadow:0 3px 0 rgba(0,0,0,.18)}
    #kl-transition .klt-sub{margin:0;font-size:clamp(16px,3.4vw,22px);font-weight:700;opacity:.95}
    #kl-transition .klt-dots{display:flex;gap:10px;margin-top:14px}
    #kl-transition .klt-dots i{width:14px;height:14px;border-radius:50%;background:#fff;animation:klt-dot .9s ease-in-out infinite}
    #kl-transition .klt-dots i:nth-child(2){animation-delay:.15s}
    #kl-transition .klt-dots i:nth-child(3){animation-delay:.3s}
    #kl-transition.is-in .klt-body{animation:klt-rise .45s cubic-bezier(.3,1.4,.5,1) .12s both}
    #kl-transition.is-instant .klt-body{animation:none}

    @keyframes klt-bounce{0%,100%{transform:translateY(0) rotate(-2deg)}50%{transform:translateY(-14px) rotate(2deg)}}
    @keyframes klt-dot{0%,80%,100%{transform:scale(.6);opacity:.55}40%{transform:scale(1.15);opacity:1}}
    @keyframes klt-twinkle{0%,100%{transform:scale(.8) rotate(0);opacity:.35}50%{transform:scale(1.15) rotate(20deg);opacity:1}}
    @keyframes klt-pop{from{transform:scale(0) rotate(-20deg)}to{transform:scale(1) rotate(8deg)}}
    @keyframes klt-rise{from{transform:translateY(24px) scale(.92);opacity:0}to{transform:none;opacity:1}}
    @keyframes klt-failsafe{to{visibility:hidden;pointer-events:none}}
    @media (prefers-reduced-motion:reduce){
        #kl-transition,#kl-transition.is-out{transition:opacity .2s linear;clip-path:none}
        #kl-transition.is-in{clip-path:none}
        #kl-transition .klt-star,#kl-transition .klt-mascot,#kl-transition .klt-dots i,#kl-transition .klt-badge,#kl-transition.is-in .klt-body{animation:none}
    }
</style>

<div id="kl-transition" role="status" aria-live="polite" aria-hidden="true">
    <div class="klt-shade"></div>
    <span class="klt-star" aria-hidden="true">&#10022;</span>
    <span class="klt-star" aria-hidden="true">&#10022;</span>
    <span class="klt-star" aria-hidden="true">&#10022;</span>
    <span class="klt-star" aria-hidden="true">&#10022;</span>
    <span class="klt-star" aria-hidden="true">&#10022;</span>
    <div class="klt-body">
        <div class="klt-figure">
            <img class="klt-mascot" alt="" decoding="async">
            <span class="klt-badge"><img alt="" decoding="async"></span>
        </div>
        <h2 class="klt-title"></h2>
        <p class="klt-sub"></p>
        <div class="klt-dots" aria-hidden="true"><i></i><i></i><i></i></div>
    </div>
</div>
<script type="application/json" id="kl-transition-config">@json($transitionConfig, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)</script>
<script>
(function () {
    var root = document.getElementById('kl-transition');
    var cfgEl = document.getElementById('kl-transition-config');
    if (!root || !cfgEl || window.__kidlinkChildTransition) return;
    window.__kidlinkChildTransition = true;

    var cfg = JSON.parse(cfgEl.textContent);
    var KEY = 'klTransition';
    var MAX_AGE_MS = 10000;      // a note older than this is ignored
    var LEAVE_MS = 340;          // the screen swells in, then we go
    var MIN_SHOW_MS = 700;       // the arriving screen stays at least this long
    var HARD_MAX_MS = 6500;      // ...and never longer than this
    var OUT_MS = 460;

    var titleEl = root.querySelector('.klt-title');
    var subEl = root.querySelector('.klt-sub');
    var mascotEl = root.querySelector('.klt-mascot');
    var badgeEl = root.querySelector('.klt-badge');
    var badgeImg = badgeEl.querySelector('img');
    var leaving = false;
    var hideTimer = null;

    function fmt(text, vars) {
        return String(text).replace(/:(\w+)/g, function (m, key) {
            return Object.prototype.hasOwnProperty.call(vars || {}, key) ? vars[key] : m;
        });
    }

    // Path relative to /student, without a trailing slash ("/" for the root).
    function rel(pathname) {
        var p = pathname;
        if (cfg.base && p.indexOf(cfg.base) === 0) p = p.slice(cfg.base.length);
        p = p.replace(/\/+$/, '');
        return p === '' ? '/' : p;
    }

    // What to say and show for a destination. "from" is where the child is
    // coming from, so finishing a level reads differently from starting one.
    function describe(path, from, kind) {
        var T = cfg.text;
        var fromGame = /^\/games?\//.test(from);
        var m;

        if (kind === 'enter') {
            return { title: fmt(T.enter_title, { name: cfg.name }), sub: T.enter_sub, mascot: 'wave' };
        }
        if (path === '/home') {
            return fromGame
                ? { title: T.home_back_title, sub: T.home_back_sub, mascot: 'cheer' }
                : { title: T.home_title, sub: T.home_sub, mascot: 'wave' };
        }
        if ((m = path.match(/^\/games\/([^\/]+)\/play\/(\d+)/))) {
            return { title: fmt(T.level_title, { n: m[2] }), sub: T.level_sub, mascot: 'wave', game: m[1] };
        }
        if ((m = path.match(/^\/games\/([^\/]+)$/))) {
            var cameBack = from.indexOf('/games/' + m[1] + '/play/') === 0;
            var game = cfg.games[m[1]];
            return cameBack
                ? { title: T.game_back_title, sub: T.game_back_sub, mascot: 'cheer', game: m[1] }
                : { title: fmt(T.game_title, { game: game ? game.name : '' }), sub: T.game_sub, mascot: 'wave', game: m[1] };
        }
        return { title: T.default_title, sub: T.default_sub, mascot: 'wave' };
    }

    function render(info) {
        var game = info.game ? cfg.games[info.game] : null;
        titleEl.textContent = info.title;
        subEl.textContent = info.sub;
        mascotEl.src = cfg.mascot[info.mascot] || cfg.mascot.wave;
        root.style.setProperty('--klt-bg', game ? game.gradient : cfg.gradient);
        if (game) {
            badgeImg.src = game.img;
            badgeEl.style.display = 'flex';
        } else {
            badgeEl.style.display = 'none';
        }
    }

    function reveal(instant) {
        clearTimeout(hideTimer);
        root.classList.remove('is-out');
        root.classList.toggle('is-instant', !!instant);
        root.setAttribute('aria-hidden', 'false');
        root.classList.add('is-in');
    }

    function lift() {
        root.classList.remove('is-instant', 'is-in');
        root.classList.add('is-out');
        root.setAttribute('aria-hidden', 'true');
        clearTimeout(hideTimer);
        hideTimer = setTimeout(function () { root.classList.remove('is-out'); }, OUT_MS);
    }

    function reset() {
        leaving = false;
        clearTimeout(hideTimer);
        root.classList.remove('is-in', 'is-out', 'is-instant');
        root.setAttribute('aria-hidden', 'true');
    }

    function note(extra) {
        try {
            var data = { ts: Date.now(), from: rel(window.location.pathname) };
            if (extra) for (var k in extra) data[k] = extra[k];
            sessionStorage.setItem(KEY, JSON.stringify(data));
        } catch (e) { /* private mode: the arrival screen is simply skipped */ }
    }

    // ---- arriving ----------------------------------------------------------
    (function arrive() {
        var flag = null;
        try {
            flag = JSON.parse(sessionStorage.getItem(KEY));
            sessionStorage.removeItem(KEY);
        } catch (e) { return; }
        if (!flag || !flag.ts || Date.now() - flag.ts > MAX_AGE_MS) return;

        var navEntry = window.performance && performance.getEntriesByType
            ? performance.getEntriesByType('navigation')[0] : null;
        if (navEntry && (navEntry.type === 'reload' || navEntry.type === 'back_forward')) return;

        var startedAt = Date.now();
        var finished = false;
        render(describe(rel(window.location.pathname), flag.from || '/', flag.kind));
        reveal(true);

        function finish() {
            if (finished) return;
            finished = true;
            setTimeout(lift, Math.max(0, MIN_SHOW_MS - (Date.now() - startedAt)));
        }
        if (document.readyState === 'complete') finish();
        else window.addEventListener('load', finish);
        setTimeout(finish, HARD_MAX_MS);
    })();

    // ---- leaving -----------------------------------------------------------
    // Registered before the page loader's own handlers (this component sits at
    // the top of <body>), so a link we take over is already cancelled by the
    // time the loader looks at it.
    document.addEventListener('click', function (event) {
        if (event.defaultPrevented || event.button !== 0) return;
        if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

        var link = event.target.closest && event.target.closest('a[href]');
        if (!link) return;
        if (link.hasAttribute('download') || link.hasAttribute('data-no-transition')) return;
        if (link.target && link.target !== '_self') return;

        var raw = link.getAttribute('href') || '';
        if (/^(#|javascript:|mailto:|tel:|sms:|blob:|data:)/i.test(raw)) return;

        var url;
        try { url = new URL(link.href, window.location.href); } catch (e) { return; }
        if (url.origin !== window.location.origin) return;
        if (url.pathname === window.location.pathname && url.search === window.location.search) return;

        event.preventDefault();
        if (leaving) return;
        leaving = true;

        // Other scripts' handlers on the same click have already run (we are on
        // document), so if one of them cancelled the click we never get here.
        render(describe(rel(url.pathname), rel(window.location.pathname)));
        reveal(false);
        setTimeout(function () { window.location.href = url.href; }, LEAVE_MS);
        // If the next page never answers, give the page back instead of
        // swallowing every later tap.
        setTimeout(reset, HARD_MAX_MS + 2500);
    });

    // Every other way of leaving (game code, form posts) is covered on arrival.
    // Always leave the note, even when the browser keeps this page in its
    // back/forward cache (persisted): that is the common case, and the note
    // simply expires if nothing reads it.
    window.addEventListener('pagehide', function () { note(); });

    // Back/forward cache restores the page exactly as it was, screen and all.
    window.addEventListener('pageshow', function (event) {
        if (event.persisted) reset();
    });

    // Have the mascots and game icons ready so they are there when it counts.
    window.addEventListener('load', function () {
        var urls = [cfg.mascot.wave, cfg.mascot.cheer];
        for (var id in cfg.games) urls.push(cfg.games[id].img);
        urls.forEach(function (src) { var img = new Image(); img.src = src; });
    });
})();
</script>
