/**
 * In-place filtering for GET filter forms.
 *
 * Mark a form with `data-ajax-filter` and its submissions (and any link inside
 * it, or inside an element marked `data-ajax-filter-scope`) are fetched in the
 * background. Only <main> is swapped, so the sidebar, header, Tailwind, Chart.js
 * and Alpine are not reloaded or re-parsed. The URL is still updated, so the
 * back button, refresh and shared links keep working.
 *
 * Any failure falls back to a normal full-page navigation.
 */
(function () {
    'use strict';

    var main = document.querySelector('main');
    if (!main || !window.fetch || !window.DOMParser) return;

    var controller = null;
    var SCOPE = 'form[data-ajax-filter], [data-ajax-filter-scope]';

    function buildUrl(form) {
        var url = new URL(form.getAttribute('action') || location.href, location.href);
        var params = new URLSearchParams();
        new FormData(form).forEach(function (value, key) {
            if (typeof value === 'string' && value !== '') params.append(key, value);
        });
        url.search = params.toString();
        return url;
    }

    function runScript(old, parent) {
        // Wrapped in a block so top-level `const` declarations from the page can
        // be evaluated again without "already declared" errors.
        var el = document.createElement('script');
        el.textContent = '{\n' + old.textContent + '\n}';
        parent.appendChild(el);
        el.remove();
    }

    function destroyCharts() {
        if (!window.Chart || !Chart.instances) return;
        Object.keys(Chart.instances).forEach(function (id) {
            try { Chart.instances[id].destroy(); } catch (e) {}
        });
    }

    function setBusy(on) {
        main.style.transition = 'opacity .15s';
        main.style.opacity = on ? '0.5' : '';
        main.style.pointerEvents = on ? 'none' : '';
        main.setAttribute('aria-busy', on ? 'true' : 'false');
    }

    // Allows calendar/date widgets to use the same in-place navigation.
    window.ajaxFilterNavigate = function (url) {
        load(new URL(url, location.href), true);
    };

    function load(url, push) {
        if (controller) controller.abort();
        controller = new AbortController();
        var mine = controller;
        var scrollTop = main.scrollTop;
        setBusy(true);

        fetch(url.toString(), {
            signal: mine.signal,
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
        }).then(function (res) {
            // Redirects (e.g. session expired -> login) must be a real navigation.
            if (!res.ok || res.redirected) throw new Error('fallback');
            return res.text();
        }).then(function (html) {
            var doc = new DOMParser().parseFromString(html, 'text/html');
            var next = doc.querySelector('main');
            if (!next) throw new Error('fallback');

            destroyCharts();
            main.innerHTML = next.innerHTML;
            if (doc.title) document.title = doc.title;
            if (push) history.pushState({ ajaxFilter: true }, '', url.toString());

            // Scripts set through innerHTML do not run: re-run the ones in <main>
            // and the page's chart scripts (pushed to the end of <body>).
            main.querySelectorAll('script').forEach(function (s) { runScript(s, main); });
            doc.querySelectorAll('script[data-page-script]').forEach(function (s) {
                runScript(s, document.body);
            });

            main.scrollTop = scrollTop;
            setBusy(false);
        }).catch(function (err) {
            if (err && err.name === 'AbortError') return;
            location.href = url.toString();
        });
    }

    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form.matches || !form.matches('form[data-ajax-filter]')) return;
        if ((form.method || 'get').toLowerCase() !== 'get') return;
        e.preventDefault();
        load(buildUrl(form), true);
    });

    document.addEventListener('click', function (e) {
        if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
        var link = e.target.closest && e.target.closest('a[href]');
        if (!link || link.target || link.hasAttribute('download')) return;
        if (!link.closest(SCOPE)) return;
        var url = new URL(link.href, location.href);
        if (url.origin !== location.origin || url.pathname !== location.pathname) return;
        e.preventDefault();
        load(url, true);
    });

    window.addEventListener('popstate', function () {
        load(new URL(location.href), false);
    });
})();
