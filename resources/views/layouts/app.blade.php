<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#ff8555">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <meta name="description" content="{{ __('KidLink: early childhood learning, attendance and progress for teachers, parents and children.') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>KidLink - @yield('title', __('misc.daycare_management'))</title>
    
    
    <link rel="preload" href="{{ asset('fonts/UcC73FwrK3iLTeHuS_nVMrMxCp50SjIa1ZL7.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link href="{{ \App\Support\Asset::v('css/fonts.css') }}" rel="stylesheet">
    <link href="{{ \App\Support\Asset::v('css/kidlink-theme.css') }}" rel="stylesheet">
    <link href="{{ \App\Support\Asset::v('css/mascot.css') }}" rel="stylesheet">
    <link href="{{ \App\Support\Asset::v('css/icons.css') }}" rel="stylesheet">
    <x-swal-lazy :prefetch="auth()->check()" />
    {{-- Alpine is only used behind the login; the landing and sign-in pages skip its 56 KB. --}}
    @auth
    <script defer src="{{ \App\Support\Asset::v('js/alpine.min.js') }}"></script>
    @endauth
    <script defer src="{{ \App\Support\Asset::v('js/mascot.js') }}"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
    @stack('styles')
    <link href="{{ \App\Support\Asset::v('css/tailwind.css') }}" rel="stylesheet">
</head>
<body class="bg-[#fff8e7] antialiased">
    @if(auth()->check() && auth()->user()->role === 'student')
    <x-child-transition />
    @endif
    @yield('content')

    <x-page-loader />
    <x-connection-status />
    <x-session-keep-alive />

    <script src="{{ \App\Support\Asset::v('js/effects.js') }}"></script>
    @stack('scripts')
    @if(session('success'))
    <script>
        Swal.fire({ icon: 'success', title: @json(__('misc.success_title')), text: @json(session('success')), timer: 3000, showConfirmButton: false });
    </script>
    @endif
    @if(session('info'))
    <script>
        Swal.fire({ icon: 'info', title: @json(__('misc.notice_title')), text: @json(session('info')), timer: 3000, showConfirmButton: false });
    </script>
    @endif
    @if(session('error'))
    <script>
        Swal.fire({ icon: 'error', title: @json(__('misc.error_title')), text: @json(session('error')) });
    </script>
    @endif
    @if($errors->any())
    <script>
        Swal.fire({ icon: 'error', title: @json(__('misc.error_title')), text: @json($errors->first()), timer: 5000, showConfirmButton: false });
    </script>
    @endif
</body>
</html>
