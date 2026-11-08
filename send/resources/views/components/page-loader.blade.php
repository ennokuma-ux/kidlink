{{--
    Loading screen + double-click protection, shared by every layout.

    - Shows a full-screen loader whenever the page is about to change: link
      clicks, form submits (including forms submitted from code after a
      confirmation dialog) and reloads. It appears only if the change takes
      longer than a moment, so quick page changes do not flash it.
    - Locks a form's buttons and ignores further link clicks once navigation has
      started, so one tap can never create two records.
    - Any button also ignores a second click on itself for a short moment.
      Opt out with data-allow-repeat on the button.
    - Opt a link or form out of the loader with data-no-loader.
    - Never fires for clicks/submits another script has already cancelled
      (e.g. a declined confirm()), so cancelling leaves the page usable.
--}}
<style>
    #kl-loader{position:fixed;inset:0;z-index:2147483000;display:none;align-items:center;justify-content:center;background:rgba(24,36,56,.55)}
    #kl-loader.is-visible{display:flex}
    #kl-loader .kl-spinner{width:56px;height:56px;border-radius:50%;border:6px solid #ffd9c7;border-top-color:#ff8555;animation:kl-spin .8s linear infinite}
    #kl-loader .kl-card{display:flex;flex-direction:column;align-items:center;gap:14px;padding:26px 38px;background:#fff;border-radius:18px;box-shadow:0 12px 40px rgba(0,0,0,.25)}
    #kl-loader .kl-text{font:600 14px/1.2 system-ui,-apple-system,"Segoe UI",sans-serif;color:#182438;letter-spacing:.02em}
    /* One consistent dim behind every dialog that sits on top of the page. */
    .swal2-container.swal2-backdrop-show,.swal2-container.swal2-noanimation{background:rgba(24,36,56,.55)!important}
    @keyframes kl-spin{to{transform:rotate(360deg)}}
    @media (prefers-reduced-motion:reduce){#kl-loader .kl-spinner{animation-duration:2.4s}}
</style>
<div id="kl-loader" role="status" aria-live="polite" aria-label="{{ __('connection.loading') }}">
    <div class="kl-card">
        <div class="kl-spinner" aria-hidden="true"></div>
        <div class="kl-text">{{ __('connection.loading') }}</div>
    </div>
</div>
<script>
(() => {
    const loader = document.getElementById('kl-loader');
    if (!loader) return;

    // Wait this long before covering the page; a faster page change never shows it.
    const SHOW_DELAY_MS = 150;

    let navigating = false;
    let failSafe = null;
    let showTimer = null;
    const lockedForms = new Set();

    const show = () => {
        navigating = true;
        // Clicks are already ignored from this moment; only the picture waits.
        clearTimeout(showTimer);
        showTimer = setTimeout(() => { if (navigating) loader.classList.add('is-visible'); }, SHOW_DELAY_MS);
        // If the response never replaces the page (a file download, an error),
        // give the screen back instead of leaving it stuck.
        clearTimeout(failSafe);
        failSafe = setTimeout(reset, 20000);
    };

    const lockForm = (form) => {
        if (!form || form.dataset.allowRepeat === 'true') return;
        lockedForms.add(form);
        form.dataset.submitting = 'true';
        form.querySelectorAll('button, input[type="submit"]').forEach((b) => {
            if (b.type === 'button' && !b.hasAttribute('data-prevent-double-click')) return;
            if (b.dataset.keepEnabled === 'true') return;
            b.dataset.klWasDisabled = b.disabled ? '1' : '0';
            b.disabled = true;
            b.classList.add('opacity-60', 'cursor-not-allowed');
            b.setAttribute('aria-busy', 'true');
        });
    };

    const unlockForms = () => {
        lockedForms.forEach((form) => {
            delete form.dataset.submitting;
            form.querySelectorAll('[data-kl-was-disabled]').forEach((b) => {
                b.disabled = b.dataset.klWasDisabled === '1';
                delete b.dataset.klWasDisabled;
                b.classList.remove('opacity-60', 'cursor-not-allowed');
                b.removeAttribute('aria-busy');
            });
        });
        lockedForms.clear();
    };

    function reset() {
        navigating = false;
        clearTimeout(failSafe);
        clearTimeout(showTimer);
        loader.classList.remove('is-visible');
        unlockForms();
    }

    // Back/forward cache restores the page as it was, loader and all.
    window.addEventListener('pageshow', (e) => { if (e.persisted || navigating) reset(); });

    // ---- links -----------------------------------------------------------
    document.addEventListener('click', (event) => {
        const link = event.target.closest && event.target.closest('a[href]');
        if (!link) return;

        if (navigating) { event.preventDefault(); return; }   // second click while loading
        if (event.defaultPrevented || event.button !== 0) return;
        if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        if (link.dataset.noLoader !== undefined || link.hasAttribute('download')) return;
        if (link.target && link.target !== '_self') return;

        const raw = link.getAttribute('href') || '';
        if (/^(#|javascript:|mailto:|tel:|sms:|blob:|data:)/i.test(raw)) return;

        let url;
        try { url = new URL(link.href, window.location.href); } catch (e) { return; }
        if (url.origin !== window.location.origin) return;
        if (url.pathname === window.location.pathname && url.search === window.location.search && url.hash) return;

        show();
    });

    // ---- forms -----------------------------------------------------------
    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) return;

        if (navigating || form.dataset.submitting === 'true') { event.preventDefault(); return; }
        if (event.defaultPrevented) return;                     // cancelled by a confirm() etc.
        if (form.dataset.noLoader !== undefined || (form.target && form.target !== '_self')) return;
        if ((form.getAttribute('method') || '').toLowerCase() === 'dialog') return;

        // Lock after the browser has built the request so disabled buttons are
        // still included in what is sent.
        show();
        setTimeout(() => lockForm(form), 0);
    });

    // Forms submitted from code (e.g. after a SweetAlert confirmation) skip the
    // submit event, so handle them the same way.
    const nativeSubmit = HTMLFormElement.prototype.submit;
    HTMLFormElement.prototype.submit = function () {
        if (navigating || this.dataset.submitting === 'true') return;
        if (this.dataset.noLoader === undefined && (!this.target || this.target === '_self')) {
            show();
            const form = this;
            setTimeout(() => lockForm(form), 0);
        }
        return nativeSubmit.call(this);
    };

    window.addEventListener('beforeunload', () => { if (!navigating) show(); });

    // ---- any button: ignore a second click on the same button -------------
    const lastClick = new WeakMap();
    document.addEventListener('click', (event) => {
        const btn = event.target.closest && event.target.closest('button, input[type="button"], input[type="submit"], [role="button"]');
        if (!btn || btn.dataset.allowRepeat !== undefined || btn.closest('[data-allow-repeat]')) return;
        const now = Date.now();
        const prev = lastClick.get(btn) || 0;
        if (now - prev < Number(btn.dataset.lockDuration || 700)) {
            event.preventDefault();
            event.stopImmediatePropagation();
            return;
        }
        lastClick.set(btn, now);
    }, true);
})();
</script>
