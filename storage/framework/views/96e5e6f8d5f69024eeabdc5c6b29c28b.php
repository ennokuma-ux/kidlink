
<?php
    $intervalMs = max(60, (int) (config('session.lifetime') * 60 / 4)) * 1000;
?>
<div id="session-keep-alive"
     data-url="<?php echo e(route('session.keep-alive')); ?>"
     data-interval="<?php echo e($intervalMs); ?>"
     data-role="<?php echo e(auth()->check() ? auth()->user()->role : 'guest'); ?>"
     hidden></div>

<?php $__env->startPush('scripts'); ?>
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
                            title: <?php echo json_encode(__('misc.signed_out_title'), 15, 512) ?>,
                            text: recoverable
                                ? <?php echo json_encode(__('misc.signed_out_recover'), 15, 512) ?>
                                : <?php echo json_encode(__('misc.signed_out_ask_teacher'), 15, 512) ?>,
                            confirmButtonText: recoverable ? <?php echo json_encode(__('misc.sign_in'), 15, 512) ?> : <?php echo json_encode(__('misc.ok'), 15, 512) ?>,
                        }).then(function () {
                            if (recoverable) window.location.href = <?php echo json_encode(route('login'), 15, 512) ?>;
                        });
                    } else if (recoverable) {
                        window.location.href = <?php echo json_encode(route('login'), 15, 512) ?>;
                    }

                    throw new Error('KidLink session expired (419)');
                });
            };
        }

        if (document.visibilityState === 'visible') start();
    })();
</script>
<?php $__env->stopPush(); ?>
<?php /**PATH C:\laragon\www\kidlink-laravel\resources\views/components/session-keep-alive.blade.php ENDPATH**/ ?>