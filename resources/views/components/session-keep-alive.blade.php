{{--
    Keeps the session alive on pages that stay open a long time.

    Laravel discards a session after SESSION_LIFETIME minutes of inactivity, and the
    CSRF token goes with it. Any form left on screen past that point comes back as
    "419 Page Expired". Pinging the app refreshes the session and, because the
    response carries the current token, a rotated token is picked up without a
    reload. Four pings per lifetime leaves plenty of margin.

    Pings are skipped while the tab is hidden: a tab in the background is not doing
    work, and the first ping on returning resynchronises immediately anyway.
--}}
@php
    $intervalMs = max(60, (int) (config('session.lifetime') * 60 / 4)) * 1000;
@endphp
<div id="session-keep-alive"
     data-url="{{ route('session.keep-alive') }}"
     data-interval="{{ $intervalMs }}"
     data-role="{{ auth()->check() ? auth()->user()->role : 'guest' }}"
     hidden></div>

@push('scripts')
<script>
    (function () {
        var root = document.getElementById('session-keep-alive');
        if (!root) return;

        var url = root.dataset.url;
        var interval = parseInt(root.dataset.interval, 10) || 1800000;
        var meta = document.querySelector('meta[name="csrf-token"]');
        var timer = null;

        function ping() {
            // A 419 never reaches the handlers below: the fetch guard installed at the
            // bottom of this file intercepts it, explains the situation and stops the
            // chain. So the only cases left here are "fine" and "server unreachable".
            fetch(url, {
                method: 'GET',
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' },
            })
                .then(function (res) {
                    return res.ok ? res.json() : null;
                })
                .then(function (data) {
                    if (data && data.token && meta && meta.content !== data.token) {
                        meta.content = data.token;
                    }
                })
                .catch(function () {
                    // Offline or server restarting: the next tick will retry.
                });
        }

        function start() {
            if (timer) return;
            timer = window.setInterval(function () {
                if (document.visibilityState === 'visible') ping();
            }, interval);
        }

        function stop() {
            if (timer) {
                window.clearInterval(timer);
                timer = null;
            }
        }

        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'visible') {
                ping();
                start();
            } else {
                stop();
            }
        });

        // Guard every fetch in the app. Without this a 419 comes back as a normal
        // response, so code that blindly does response.json() reads undefined out of
        // an error body and quietly renders "Score: undefined" while the real work
        // is discarded. Stopping the chain is the honest outcome.
        if (! window.__kidlinkSessionGuarded) {
            window.__kidlinkSessionGuarded = true;
            var nativeFetch = window.fetch.bind(window);

            window.fetch = function (input, init) {
                return nativeFetch(input, init).then(function (res) {
                    if (res.status !== 419) return res;

                    // A logged-out teacher or parent can recover on their own. A child
                    // mid-game cannot, so tell them who to ask instead of looping.
                    var recoverable = root.dataset.role !== 'student';

                    if (window.Swal) {
                        window.Swal.fire({
                            icon: 'warning',
                            title: @json(__('misc.signed_out_title')),
                            text: recoverable
                                ? @json(__('misc.signed_out_recover'))
                                : @json(__('misc.signed_out_ask_teacher')),
                            confirmButtonText: recoverable ? @json(__('misc.sign_in')) : @json(__('misc.ok')),
                        }).then(function () {
                            if (recoverable) window.location.href = @json(route('login'));
                        });
                    } else if (recoverable) {
                        window.location.href = @json(route('login'));
                    }

                    throw new Error('KidLink session expired (419)');
                });
            };
        }

        if (document.visibilityState === 'visible') start();
    })();
</script>
@endpush
