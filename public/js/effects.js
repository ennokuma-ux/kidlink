/**
 * KidLink effects: the pop, burst and confetti shown when a child gets
 * something right. Everything is drawn with the icon set (star, sparkles,
 * party-popper ...) in the app palette, never with emoji, so it looks the
 * same on every device.
 *
 *   KidLinkFx.celebrate('star')        big icon in the middle + a burst
 *   KidLinkFx.burst(element, 12)       icons fly out of an element
 *   KidLinkFx.confetti(40)             icons rain down the whole screen
 */
(function () {
    'use strict';

    var PALETTE = ['#ff595e', '#ffca3a', '#8ac926', '#1982c4', '#6a4c93', '#ff8555'];
    var SHAPES = ['star', 'sparkles', 'star-half', 'party-popper'];
    var ICON_COLOR = {
        'star': '#ffca3a', 'star-half': '#ffca3a', 'trophy': '#ffca3a', 'lightbulb': '#ffca3a',
        'sparkles': '#6a4c93', 'party-popper': '#ff595e', 'color-palette': '#1982c4', 'target': '#ff595e'
    };
    var reduceMotion = !!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);
    var layer = null;

    function getLayer() {
        if (!layer || !layer.isConnected) {
            layer = document.createElement('div');
            layer.className = 'kl-fx-layer';
            layer.setAttribute('aria-hidden', 'true');
            document.body.appendChild(layer);
        }
        return layer;
    }

    function rand(min, max) { return min + Math.random() * (max - min); }
    function pick(list) { return list[Math.floor(Math.random() * list.length)]; }

    // Games pass an icon name. Anything else (an old emoji or markup) becomes sparkles.
    function iconName(value) {
        return typeof value === 'string' && /^[a-z0-9-]+$/.test(value) ? value : 'sparkles';
    }

    function piece(name, color, size) {
        var i = document.createElement('i');
        i.className = 'ico ico-' + name + ' kl-fx-p';
        i.style.color = color;
        i.style.fontSize = size + 'px';
        i.style.marginLeft = i.style.marginTop = (-size / 2) + 'px';
        return i;
    }

    function pointOf(origin) {
        if (origin && origin.getBoundingClientRect) {
            var r = origin.getBoundingClientRect();
            return { x: r.left + r.width / 2, y: r.top + r.height / 2 };
        }
        if (origin && typeof origin.x === 'number') return origin;
        return { x: window.innerWidth / 2, y: window.innerHeight / 2 };
    }

    function play(el, frames, options) {
        if (!el.animate) {
            setTimeout(function () { el.remove(); }, options.duration);
            return;
        }
        var animation = el.animate(frames, options);
        animation.onfinish = function () { el.remove(); };
    }

    /** Icons fly out of a point or an element and fade. */
    function burst(origin, count) {
        if (reduceMotion) return;
        var from = pointOf(origin);
        var host = getLayer();
        var total = count || 14;
        var offset = Math.floor(Math.random() * PALETTE.length);

        for (var n = 0; n < total; n++) {
            var size = rand(14, 30);
            var el = piece(SHAPES[n % SHAPES.length], PALETTE[(n + offset) % PALETTE.length], size);
            var angle = (n / total) * Math.PI * 2 + rand(-0.3, 0.3);
            var distance = rand(50, 140);
            var dx = Math.cos(angle) * distance;
            var dy = Math.sin(angle) * distance;
            var spin = rand(-300, 300);
            host.appendChild(el);
            play(el, [
                { transform: 'translate(' + from.x + 'px,' + from.y + 'px) scale(0.3) rotate(0deg)', opacity: 1 },
                { transform: 'translate(' + (from.x + dx) + 'px,' + (from.y + dy - 30) + 'px) scale(1) rotate(' + spin + 'deg)', opacity: 1, offset: 0.6 },
                { transform: 'translate(' + (from.x + dx * 1.1) + 'px,' + (from.y + dy + 50) + 'px) scale(0.8) rotate(' + spin * 1.3 + 'deg)', opacity: 0 }
            ], { duration: rand(800, 1300), easing: 'cubic-bezier(0.2, 0.7, 0.3, 1)', delay: rand(0, 120) });
        }
    }

    /** Icons rain down the whole screen. */
    function confetti(count) {
        if (reduceMotion) return;
        var host = getLayer();
        var total = count || 36;
        var width = window.innerWidth, height = window.innerHeight;

        for (var n = 0; n < total; n++) {
            var size = rand(14, 28);
            var el = piece(SHAPES[n % SHAPES.length], PALETTE[n % PALETTE.length], size);
            var x = rand(0, width);
            var sway = rand(-60, 60);
            var spin = rand(-540, 540);
            host.appendChild(el);
            play(el, [
                { transform: 'translate(' + x + 'px,-40px) rotate(0deg)', opacity: 1 },
                { transform: 'translate(' + (x + sway) + 'px,' + height * 0.5 + 'px) rotate(' + spin / 2 + 'deg)', opacity: 1, offset: 0.6 },
                { transform: 'translate(' + (x - sway) + 'px,' + (height + 40) + 'px) rotate(' + spin + 'deg)', opacity: 0.9 }
            ], { duration: rand(2000, 3400), easing: 'ease-in', delay: rand(0, 500) });
        }
    }

    /** A big icon pops in the middle of the screen with a burst around it. */
    function celebrate(name) {
        var icon = iconName(name);
        var size = Math.max(64, Math.min(120, window.innerWidth * 0.14));
        var el = piece(icon, ICON_COLOR[icon] || pick(PALETTE), size);
        el.classList.add('kl-fx-pop');
        el.style.left = '50%';
        el.style.top = '50%';
        getLayer().appendChild(el);

        if (reduceMotion) {
            play(el, [{ opacity: 1 }, { opacity: 0 }], { duration: 900 });
            return;
        }

        play(el, [
            { transform: 'translate(-50%, -50%) scale(0.3)', opacity: 0 },
            { transform: 'translate(-50%, -50%) scale(1.25)', opacity: 1, offset: 0.35 },
            { transform: 'translate(-50%, -50%) scale(1)', opacity: 1, offset: 0.7 },
            { transform: 'translate(-50%, -62%) scale(1)', opacity: 0 }
        ], { duration: 1100, easing: 'ease-out' });

        burst(null, 16);
        if (icon === 'trophy' || icon === 'party-popper') confetti(36);
    }

    window.KidLinkFx = { celebrate: celebrate, burst: burst, confetti: confetti };

    // The level-complete card from the game kit gets its confetti too.
    document.addEventListener('DOMContentLoaded', function () {
        var game = window.KidLinkGame;
        if (game && typeof game.finish === 'function' && !game.__fx) {
            var original = game.finish;
            game.finish = function () {
                confetti(44);
                return original.apply(this, arguments);
            };
            game.__fx = true;
        }
    });
})();
