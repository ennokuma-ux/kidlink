@props(['prefetch' => true])
{{--
    SweetAlert2, loaded the first time a dialog is actually needed.

    The library is ~79 KB and most page views never open a dialog, so loading it in
    <head> only delays first paint. This stand-in keeps every existing
    Swal.fire(...) call working: the first call fetches the real library, then
    runs the dialog and hands back the same promise the real one returns.

    Logged-in layouts also prefetch the file at idle priority so the first
    dialog still opens instantly.
--}}
<script>
(function () {
    if (window.Swal) return;
    var SRC = @json(\App\Support\Asset::v('js/sweetalert2.min.js'));
    var pending = null;
    var shim = {
        fire: function () {
            var args = arguments;
            return load().then(function () { return window.Swal.fire.apply(window.Swal, args); });
        }
    };
    function load() {
        if (pending) return pending;
        pending = new Promise(function (resolve, reject) {
            var s = document.createElement('script');
            s.src = SRC;
            s.onload = function () {
                if (window.Swal && window.Swal !== shim) resolve();
                else { pending = null; reject(new Error('SweetAlert2 did not initialise')); }
            };
            s.onerror = function () { pending = null; reject(new Error('SweetAlert2 failed to load')); };
            document.head.appendChild(s);
        });
        return pending;
    }
    window.Swal = shim;
})();
</script>
@if($prefetch)
<link rel="prefetch" href="{{ \App\Support\Asset::v('js/sweetalert2.min.js') }}" as="script">
@endif
