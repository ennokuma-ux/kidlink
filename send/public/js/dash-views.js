/**
 * View modes for the analytics dashboards: simple | detailed | individual.
 * Switching is instant (CSS only). "Individual" needs one student, so it picks
 * the first one through a live filter request when none is selected.
 */
(function () {
    if (window.klApplyView) return;
    var KEY = 'kl_dash_view';
    var VIEWS = ['simple', 'detailed', 'individual'];

    function stored() {
        var q = new URLSearchParams(location.search).get('view');
        if (VIEWS.indexOf(q) !== -1) return q;
        try { var s = localStorage.getItem(KEY); if (VIEWS.indexOf(s) !== -1) return s; } catch (e) {}
        return 'detailed';
    }

    function remember(view) {
        try { localStorage.setItem(KEY, view); } catch (e) {}
    }

    function navigate(url) {
        if (window.liveGo) window.liveGo(url, true); else window.location.href = url.href;
    }

    function apply(view, allowAutoSelect) {
        var dash = document.getElementById('dash');
        if (!dash) return;
        view = view || stored();

        if (view === 'individual' && dash.dataset.hasStudent !== '1') {
            var first = dash.dataset.firstStudent;
            if (allowAutoSelect && first) {
                var url = new URL(location.href);
                url.searchParams.set('student', first);
                url.searchParams.set('view', 'individual');
                navigate(url);
                return;
            }
            view = 'detailed';
        }

        dash.dataset.view = view;

        document.querySelectorAll('[data-view-btn]').forEach(function (b) {
            b.setAttribute('aria-pressed', b.dataset.viewBtn === view ? 'true' : 'false');
        });

        // Keep the filters' own forms carrying the current view
        document.querySelectorAll('form[data-live-filter]').forEach(function (f) {
            var input = f.querySelector('input[name="view"]');
            if (!input) {
                input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'view';
                f.appendChild(input);
            }
            input.value = view;
        });

        try {
            var u = new URL(location.href);
            u.searchParams.set('view', view);
            history.replaceState(history.state, '', u.pathname + u.search);
        } catch (e) {}

        // Charts re-measure once their container changes size
        window.dispatchEvent(new Event('resize'));
    }

    document.addEventListener('click', function (e) {
        var btn = e.target.closest && e.target.closest('[data-view-btn]');
        if (!btn) return;
        var dash = document.getElementById('dash');
        var view = btn.dataset.viewBtn;
        remember(view);

        // Leaving the single-child view returns to the whole class
        if (dash && dash.dataset.view === 'individual' && view !== 'individual' && dash.dataset.hasStudent === '1') {
            var url = new URL(location.href);
            url.searchParams.delete('student');
            url.searchParams.set('view', view);
            navigate(url);
            return;
        }
        apply(view, true);
    });

    window.klApplyView = function (fromSwap) { apply(null, !fromSwap); };

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', function () { apply(null, true); });
    else apply(null, true);
})();
