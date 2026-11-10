/**
 * Teacher dashboard: change one card without reloading the page.
 *
 *  1. Fragment reloads  - a form/link marked `data-partial-form` / `data-partial-link`
 *     fetches just its own card from `data-partial-url` and swaps it in. Nothing else
 *     on the page is recomputed, re-rendered or re-charted. Responses are cached for
 *     a minute, and pager links are prefetched when the pointer reaches them.
 *  2. Browser-side filter    - `select[data-standings-filter]` shows/hides rows that
 *     are already on the page. No request at all.
 *  3. Browser-side pager     - `[data-client-pager]` pages through rows already on the
 *     page. No request at all.
 *
 * The address bar is kept in sync (replaceState), so refresh and shared links open the
 * same view. Any failure falls back to a normal page navigation to the same URL.
 */
(function () {
    'use strict';

    if (window.__klDashPartials) return;
    window.__klDashPartials = true;

    // ------------------------------------------------------------------ helpers
    function currentParams() {
        return new URLSearchParams(location.search);
    }

    function setUrl(params) {
        var query = params.toString();
        history.replaceState(history.state, '', location.pathname + (query ? '?' + query : '') + location.hash);
    }

    // ------------------------------------------------------- 1. fragment reloads
    if (window.fetch && window.URLSearchParams) {
        var TTL = 60000;       // how long a fetched fragment is reused
        var MAX_CACHED = 40;
        var cache = new Map(); // fetch url -> { t, html }
        var pending = {};      // prefetches in flight
        var inflight = new WeakMap(); // target element -> AbortController
        var hoverTimer = null;
        var HEADERS = { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' };

        var remember = function (url, html) {
            cache.set(url, { t: Date.now(), html: html });
            if (cache.size > MAX_CACHED) cache.delete(cache.keys().next().value);
        };

        var fresh = function (url) {
            var hit = cache.get(url);
            return hit && Date.now() - hit.t < TTL ? hit : null;
        };

        // The live URL is the base; the control only overrides what it owns. Hidden
        // inputs are ignored - they are a no-JavaScript fallback and may be stale.
        var paramsFromForm = function (form) {
            var params = currentParams();
            form.querySelectorAll('select[name], input[name], textarea[name]').forEach(function (field) {
                if (field.type === 'hidden' || field.disabled) return;
                if ((field.type === 'checkbox' || field.type === 'radio') && !field.checked) return;
                params.set(field.name, field.value); // empty stays: "none selected" is a real choice
            });
            (form.getAttribute('data-reset') || '').split(',').forEach(function (key) {
                key = key.trim();
                if (key) params.delete(key);
            });
            return params;
        };

        var paramsFromLink = function (link) {
            var params = currentParams();
            var key = link.getAttribute('data-param');
            if (key) {
                var value = link.getAttribute('data-value');
                if (value === null || value === '' || value === '1') params.delete(key);
                else params.set(key, value);
            }
            return params;
        };

        var fragmentUrl = function (el, params) {
            var base = el.getAttribute('data-partial-url');
            if (!base) return null;
            var query = params.toString();
            return base + (query ? '?' + query : '');
        };

        var targetOf = function (el) {
            var selector = el.getAttribute('data-partial-target');
            return selector ? document.querySelector(selector) : el.closest('[data-partial]');
        };

        var swap = function (target, html) {
            var hadFocus = target.contains(document.activeElement);
            target.innerHTML = html;
            if (hadFocus) {
                // The control that had focus was replaced; keep keyboard users in place.
                if (!target.hasAttribute('tabindex')) target.setAttribute('tabindex', '-1');
                target.focus({ preventScroll: true });
            }
        };

        var load = function (el, params) {
            var url = fragmentUrl(el, params);
            var target = targetOf(el);
            if (!url || !target) return false;

            var done = function (html) {
                swap(target, html);
                setUrl(params);
            };

            var hit = fresh(url);
            if (hit) {
                done(hit.html);
                return true;
            }

            var previous = inflight.get(target);
            if (previous) previous.abort();
            var controller = new AbortController();
            inflight.set(target, controller);
            target.classList.add('is-loading');
            target.setAttribute('aria-busy', 'true');

            fetch(url, { signal: controller.signal, credentials: 'same-origin', headers: HEADERS })
                .then(function (res) {
                    // A redirect (e.g. session expired -> login) must be a real navigation.
                    if (!res.ok || res.redirected) throw new Error('fallback');
                    return res.text();
                })
                .then(function (html) {
                    remember(url, html);
                    done(html);
                })
                .catch(function (err) {
                    if (err && err.name === 'AbortError') return;
                    var query = params.toString();
                    location.href = location.pathname + (query ? '?' + query : '');
                })
                .then(function () {
                    if (inflight.get(target) === controller) {
                        inflight.delete(target);
                        target.classList.remove('is-loading');
                        target.removeAttribute('aria-busy');
                    }
                });

            return true;
        };

        var prefetch = function (link) {
            if (navigator.connection && navigator.connection.saveData) return;
            var url = fragmentUrl(link, paramsFromLink(link));
            if (!url || fresh(url) || pending[url]) return;
            pending[url] = true;
            fetch(url, { credentials: 'same-origin', headers: HEADERS })
                .then(function (res) {
                    if (!res.ok || res.redirected) throw new Error('skip');
                    return res.text();
                })
                .then(function (html) { remember(url, html); })
                .catch(function () {})
                .then(function () { delete pending[url]; });
        };

        document.addEventListener('submit', function (e) {
            var form = e.target;
            if (!form.matches || !form.matches('form[data-partial-form]')) return;
            if (load(form, paramsFromForm(form))) e.preventDefault();
        });

        document.addEventListener('click', function (e) {
            if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
            var link = e.target.closest && e.target.closest('a[data-partial-link]');
            if (!link) return;
            if (load(link, paramsFromLink(link))) e.preventDefault();
        });

        // By the time the click lands, the next page is usually already here.
        var warm = function (e) {
            var link = e.target.closest && e.target.closest('a[data-partial-link]');
            if (!link) return;
            clearTimeout(hoverTimer);
            hoverTimer = setTimeout(function () { prefetch(link); }, 90);
        };
        document.addEventListener('pointerover', warm);
        document.addEventListener('focusin', warm);
    }

    // ------------------------------------- 2. standings filter, in the browser
    function applyStandings(select) {
        var scope = select.closest('[data-standings]');
        if (!scope) return;

        var type = select.value;
        var shown = 0;
        scope.querySelectorAll('tr[data-basis]').forEach(function (row) {
            var basis = row.getAttribute('data-basis');
            var ok = type === 'all' || (type === 'game' && basis === 'Game') || (type === 'eccd' && basis === 'Domain');
            row.hidden = !ok;
            if (ok) shown++;
        });

        var table = scope.querySelector('[data-standings-table]');
        if (table) table.hidden = shown === 0;
        var empty = scope.querySelector('[data-standings-empty]');
        if (empty) empty.hidden = shown !== 0;

        var params = currentParams();
        if (type === 'all') params.delete('topic_type');
        else params.set('topic_type', type);
        setUrl(params);
    }

    document.addEventListener('change', function (e) {
        if (e.target.matches && e.target.matches('select[data-standings-filter]')) applyStandings(e.target);
    });

    // ------------------------------------------ 3. list paging, in the browser
    function pageWindow(page, pages) {
        var wanted = [1, pages, page - 1, page, page + 1].filter(function (n) { return n >= 1 && n <= pages; });
        wanted = wanted.filter(function (n, i) { return wanted.indexOf(n) === i; }).sort(function (a, b) { return a - b; });
        var out = [];
        wanted.forEach(function (n, i) {
            if (i && n - wanted[i - 1] > 1) out.push('…');
            out.push(n);
        });
        return out;
    }

    function pagerButton(label, page, kind, extra) {
        var el = document.createElement(kind === 'current' || kind === 'off' ? 'span' : 'button');
        el.textContent = label;
        var base = 'px-3 py-2 rounded-lg text-xs font-semibold ';
        if (kind === 'current') {
            el.className = base + 'min-w-9 bg-[#1982c4] text-white text-center font-bold';
            el.setAttribute('aria-current', 'page');
        } else if (kind === 'off') {
            el.className = base + 'border border-gray-200 text-gray-400 bg-gray-50 font-normal';
            el.setAttribute('aria-disabled', 'true');
        } else {
            el.type = 'button';
            el.className = base + 'border border-[#d7dee8] bg-white text-[#182438] hover:bg-[#eef6fc] focus:outline-none focus:ring-2 focus:ring-[#1982c4] ' + (extra || '');
            el.setAttribute('data-pager-go', String(page));
            el.setAttribute('data-allow-repeat', ''); // quick successive clicks are fine here
            if (/^\d+$/.test(label)) el.setAttribute('aria-label', 'Go to page ' + label);
        }
        return el;
    }

    function renderPager(root, page) {
        var per = parseInt(root.getAttribute('data-per-page'), 10) || 5;
        var rows = root.querySelectorAll('[data-pager-row]');
        var total = rows.length;
        var pages = Math.max(1, Math.ceil(total / per));
        page = Math.min(Math.max(1, page), pages);
        root.setAttribute('data-page', String(page));

        rows.forEach(function (row, i) {
            row.hidden = !(i >= (page - 1) * per && i < page * per);
        });

        var nav = root.querySelector('[data-pager-nav]');
        if (!nav) return;
        nav.textContent = '';
        if (pages <= 1) return;

        var from = (page - 1) * per + 1;
        var to = Math.min(total, page * per);
        var label = root.getAttribute('data-label') || 'items';

        var summary = document.createElement('p');
        summary.className = 'text-xs text-[#6b7280]';
        summary.setAttribute('aria-live', 'polite');
        summary.textContent = 'Showing ' + from + '–' + to + ' of ' + total + ' ' + label;

        var controls = document.createElement('div');
        controls.className = 'flex flex-wrap items-center gap-1.5';
        controls.appendChild(page > 1 ? pagerButton('Previous', page - 1, 'btn') : pagerButton('Previous', 0, 'off'));
        pageWindow(page, pages).forEach(function (n) {
            if (n === '…') {
                var gap = document.createElement('span');
                gap.className = 'px-1 text-xs text-[#6b7280]';
                gap.setAttribute('aria-hidden', 'true');
                gap.textContent = n;
                controls.appendChild(gap);
            } else {
                controls.appendChild(n === page ? pagerButton(String(n), n, 'current') : pagerButton(String(n), n, 'btn', 'min-w-9'));
            }
        });
        controls.appendChild(page < pages ? pagerButton('Next', page + 1, 'btn') : pagerButton('Next', 0, 'off'));

        nav.appendChild(summary);
        nav.appendChild(controls);
    }

    function initPagers() {
        document.querySelectorAll('[data-client-pager]').forEach(function (root) {
            if (root.hasAttribute('data-pager-ready')) return;
            root.setAttribute('data-pager-ready', '');
            renderPager(root, 1);
        });
    }

    document.addEventListener('click', function (e) {
        var button = e.target.closest && e.target.closest('[data-pager-go]');
        if (!button) return;
        var root = button.closest('[data-client-pager]');
        if (!root) return;
        renderPager(root, parseInt(button.getAttribute('data-pager-go'), 10));
        // The clicked button was rebuilt; hand focus to the current page marker.
        var current = root.querySelector('[aria-current="page"]');
        if (current) { current.setAttribute('tabindex', '-1'); current.focus({ preventScroll: true }); }
    });

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initPagers);
    else initPagers();

    // ajax-filters.js replaces <main>'s contents when the scope or sort changes;
    // pick up any pager that arrives that way.
    var main = document.querySelector('main');
    if (main && window.MutationObserver) {
        new MutationObserver(initPagers).observe(main, { childList: true });
    }
})();
