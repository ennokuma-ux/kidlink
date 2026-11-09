<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>KidLink - @yield('title', __('misc.daycare_management'))</title>
    
    
    <link href="{{ asset('css/fonts.css') }}" rel="stylesheet">
    <link href="{{ asset('css/kidlink-theme.css') }}?v={{ filemtime(public_path('css/kidlink-theme.css')) }}" rel="stylesheet">
    <link href="{{ asset('css/mascot.css') }}" rel="stylesheet">
    <link href="{{ asset('css/icons.css') }}" rel="stylesheet">
    <script src="{{ asset('js/tailwind.min.js') }}"></script>
    <x-stronger-shadows />
    <script src="{{ asset('js/chart.umd.min.js') }}"></script>
    <script src="{{ asset('js/sweetalert2.min.js') }}"></script>
    <script defer src="{{ asset('js/alpine.min.js') }}"></script>
    <script defer src="{{ asset('js/mascot.js') }}"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
    @stack('styles')
</head>
<body class="bg-[#fff8e7] antialiased">
    @if(auth()->check() && auth()->user()->role === 'student')
    <x-child-transition />
    @endif
    @yield('content')

    <x-page-loader />
    <x-connection-status />
    <x-session-keep-alive />

    <script src="{{ asset('js/effects.js') }}"></script>
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
