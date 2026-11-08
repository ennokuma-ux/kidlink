/**
 * Live filters: submit GET filter forms (and same-page links) inside
 * #live-region without a full page load. The server still renders the page;
 * we fetch it in the background, swap only the region, and re-run the chart
 * script. Falls back to a normal navigation on any error.
 */
(function () {
    if (window.__liveFilters) return;
    window.__liveFilters = true;

    var TTL = 30000;
    var cache = new Map();
    var controller = null;
    var bar = null;

    function region() { return document.getElementById('live-region'); }

    function progress(on) {
        var r = region();
        if (r) {
            r.style.transition = 'opacity .15s';
            r.style.opacity = on ? '0.55' : '';
            r.setAttribute('aria-busy', on ? 'true' : 'false');
        }
        if (!bar) {
            bar = document.createElement('div');
            bar.style.cssText = 'position:fixed;top:0;left:0;height:3px;width:0;background:#ff8555;z-index:9999;transition:width .4s ease,opacity .2s;opacity:0;pointer-events:none';
            document.body.appendChild(bar);
        }
        if (on) { bar.style.opacity = '1'; bar.style.width = '70%'; }
        else { bar.style.width = '100%'; setTimeout(function () { bar.style.opacity = '0'; bar.style.width = '0'; }, 200); }
    }

    function fetchHtml(url, signal) {
        var k = url.pathname + url.search;
        var hit = cache.get(k);
        if (hit && Date.now() - hit.t < TTL) return Promise.resolve(hit.html);
        return fetch(url.href, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
            credentials: 'same-origin',
            signal: signal
        }).then(function (res) {
            if (!res.ok || new URL(res.url).pathname !== url.pathname) throw new Error('live-fallback');
            return res.text();
        }).then(function (html) {
            cache.set(k, { html: html, t: Date.now() });
            return html;
        });
    }

    function swap(html) {
        var doc = new DOMParser().parseFromString(html, 'text/html');
        var fresh = doc.getElementById('live-region');
        var cur = region();
        if (!fresh || !cur) throw new Error('live-fallback');

        var focusId = document.activeElement && document.activeElement.id;

        if (window.Chart && Chart.instances) {
            Object.values(Chart.instances).forEach(function (c) { c.destroy(); });
        }
        cur.innerHTML = fresh.innerHTML;

        var src = doc.querySelector('script[data-live-script]');
        if (src) {
            var s = document.createElement('script');
            s.textContent = src.textContent;
            document.body.appendChild(s);
            s.remove();
        }
        if (window.klApplyView) window.klApplyView(true);

        if (focusId) {
            var el = document.getElementById(focusId);
            if (el && el.focus) el.focus({ preventScroll: true });
        }
    }

    function go(url, push) {
        if (controller) controller.abort();
        controller = new AbortController();
        var mine = controller;
        progress(true);
        fetchHtml(url, mine.signal).then(function (html) {
            if (mine !== controller) return;
            if (push) history.pushState({ live: true }, '', url.pathname + url.search);
            swap(html);
            progress(false);
        }).catch(function (err) {
            if (err && err.name === 'AbortError') return;
            window.location.href = url.href;
        });
    }

    window.liveGo = go;

    function urlFromForm(form) {
        var url = new URL(form.getAttribute('action') || location.href, location.href);
        var params = new URLSearchParams();
        new FormData(form).forEach(function (v, k) {
            if (typeof v === 'string' && v !== '') params.set(k, v);
        });
        url.search = params.toString();
        return url;
    }

    document.addEventListener('change', function (e) {
        var form = e.target.closest && e.target.closest('form[data-live-filter]');
        if (form && e.target.matches('select, input[type=checkbox], input[type=radio]')) {
            form.requestSubmit();
        }
    });

    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form.matches || !form.matches('form[data-live-filter]')) return;
        e.preventDefault();
        go(urlFromForm(form), true);
    });

    document.addEventListener('click', function (e) {
        if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
        var a = e.target.closest && e.target.closest('a[href]');
        var r = region();
        if (!a || !r || !r.contains(a) || a.target || a.hasAttribute('download')) return;
        var url = new URL(a.href, location.href);
        if (url.origin !== location.origin || url.pathname !== location.pathname || url.hash) return;
        e.preventDefault();
        go(url, true);
    });

    window.addEventListener('popstate', function () {
        if (region()) go(new URL(location.href), false);
    });
})();
