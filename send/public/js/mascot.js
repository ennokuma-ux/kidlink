/**
 * KidLink mascot runtime.
 *
 * Wires up the in-game buddy: it reads its expression artwork and speech from
 * data attributes and reacts to the shared game feedback helpers
 * (setFeedback, showCelebration, finishLevel) that every game exposes.
 */
(function () {
    'use strict';

    function parse(value, fallback) {
        try {
            return JSON.parse(value || '') || fallback;
        } catch (error) {
            return fallback;
        }
    }

    function createBuddy(element) {
        var expressions = parse(element.dataset.expressions, {});
        var speech = parse(element.dataset.speech, {});
        var image = element.querySelector('.kidlink-mascot__img');
        var bubble = element.querySelector('[data-mascot-speech]');
        var state = null;

        function render(next) {
            var path = expressions[next] || expressions.start || expressions.happy;
            if (path && image) {
                image.src = path;
            }
            if (bubble && speech[next]) {
                bubble.textContent = speech[next];
            }
            element.dataset.mascotState = next;
        }

        function setState(next) {
            if (!expressions[next] || next === state) {
                return;
            }
            state = next;
            render(next);
            element.classList.remove('kidlink-mascot--pop');
            void element.offsetWidth;
            element.classList.add('kidlink-mascot--pop');
        }

        return {
            setState: setState,
            element: element,
            get state() {
                return state;
            },
        };
    }

    function isWrongColor(color) {
        if (!color) {
            return false;
        }
        var value = String(color).toLowerCase();
        if (value.indexOf('red') !== -1) {
            return true;
        }
        return /(ef4444|dc2626|b91c1c|f43f5e|e11d48|d32f2f|e53935|c62828|f05d56|ff4d4f|ff5a5f)/.test(value);
    }

    function patchGameHooks(api) {
        if (!api || api.__patched) {
            return;
        }
        api.__patched = true;

        if (typeof window.setFeedback === 'function') {
            var originalFeedback = window.setFeedback;
            window.setFeedback = function (message, color) {
                if (isWrongColor(color)) {
                    api.setState('wrong');
                }
                return originalFeedback.apply(this, arguments);
            };
        }

        if (typeof window.showCelebration === 'function') {
            var originalCelebration = window.showCelebration;
            window.showCelebration = function () {
                if (api.state !== 'next') {
                    api.setState('correct');
                }
                return originalCelebration.apply(this, arguments);
            };
        }

        if (typeof window.finishLevel === 'function') {
            var originalFinish = window.finishLevel;
            window.finishLevel = function () {
                var result = originalFinish.apply(this, arguments);
                api.setState('next');
                return result;
            };
        }
    }

    function boot() {
        var buddies = document.querySelectorAll('[data-mascot-buddy]');
        var api = null;

        Array.prototype.forEach.call(buddies, function (element) {
            if (element.dataset.mascotReady) {
                return;
            }
            element.dataset.mascotReady = '1';
            api = createBuddy(element);
            window.KidLinkMascot = api;
            api.setState('start');
        });

        if (api) {
            patchGameHooks(api);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
