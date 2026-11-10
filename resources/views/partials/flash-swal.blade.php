{{--
    Flash messages for the public layout.

    SweetAlert2 is loaded here instead of in every page, so the landing page
    ships no dialog library at all and a page with nothing to announce costs
    nothing extra.
--}}
@php
    $flash = [
        'success' => session('success'),
        'info' => session('info'),
        'error' => session('error'),
    ];
    $firstError = $errors->any() ? $errors->first() : null;
    $hasFlash = filled($flash['success']) || filled($flash['info']) || filled($flash['error']) || $firstError;
@endphp

@if($hasFlash)
    <script src="{{ asset('js/sweetalert2.min.js') }}"></script>
    <script>
        (function () {
            const fire = (icon, title, text, timer) => Swal.fire(
                timer
                    ? { icon, title, text, timer, showConfirmButton: false }
                    : { icon, title, text }
            );

            @if(filled($flash['success']))
                fire('success', @json(__('misc.success_title')), @json($flash['success']), 3000);
            @endif
            @if(filled($flash['info']))
                fire('info', @json(__('misc.notice_title')), @json($flash['info']), 3000);
            @endif
            @if(filled($flash['error']))
                fire('error', @json(__('misc.error_title')), @json($flash['error']));
            @endif
            @if($firstError)
                fire('error', @json(__('misc.error_title')), @json($firstError), 5000);
            @endif
        })();
    </script>
@endif
