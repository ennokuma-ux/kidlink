<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>KidLink - @yield('title', __('page.announcements'))</title>

    <link href="{{ asset('css/fonts.css') }}" rel="stylesheet">
    <link href="{{ asset('css/icons.css') }}" rel="stylesheet">
    <script src="{{ asset('js/tailwind.min.js') }}"></script>
    <script src="{{ asset('js/sweetalert2.min.js') }}"></script>
    <script defer src="{{ asset('js/alpine.min.js') }}"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
    <style>
        /* Phones: wide tables scroll sideways inside their card instead of stretching the page */
        @media (max-width: 767px) {
            main div:has(> table) { overflow-x: auto; -webkit-overflow-scrolling: touch; }
            main table { min-width: 34rem; }
            .swal2-popup { width: 92% !important; }
        }
        button, a, select, input[type="checkbox"], input[type="radio"] { touch-action: manipulation; }
    </style>
    @stack('styles')
</head>
<body class="bg-[#c5d1e4] font-sans antialiased">
    <div class="flex h-screen h-[100dvh] bg-[#c5d1e4]" x-data="{ navOpen: false }" @keydown.escape.window="navOpen = false">
        @if(auth()->user()->role === 'super_admin' || auth()->user()->role === 'admin')
            @include('components.admin_sidebar')
        @elseif(auth()->user()->role === 'teacher')
            @include('components.teacher_sidebar')
        @else
            @include('components.parent_sidebar')
        @endif
        <div x-show="navOpen" x-cloak style="display:none;" @click="navOpen = false" class="fixed inset-0 bg-black/50 z-30 md:hidden" aria-hidden="true"></div>
        <div class="flex-1 flex flex-col overflow-hidden md:ml-64 min-w-0">
            <header class="bg-white shadow-sm border-b border-[#ebe8e1] px-4 md:px-6 py-3 md:py-4">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <button type="button" @click="navOpen = true" class="md:hidden shrink-0 w-10 h-10 -ml-1 rounded-lg border border-[#ebe8e1] text-[#182438] flex items-center justify-center" aria-label="Open menu">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </button>
                        <h1 class="text-lg md:text-2xl font-bold text-[#182438] truncate">@yield('page-title', __('page.announcements'))</h1>
                    </div>
                    <div class="flex items-center gap-2 md:gap-4 shrink-0">
                        <x-lang_switcher />
                        <span class="hidden lg:inline text-sm text-[#5f6873]">{{ __('header.welcome', ['name' => auth()->user()->full_name]) }}</span>
                        @include('components.child_mode')
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="bg-[#f05d56] hover:bg-[#e04942] text-white px-3 md:px-4 py-2 rounded-lg text-sm transition">{{ __('header.logout') }}</button>
                        </form>
                    </div>
                </div>
            </header>
            <main class="flex-1 overflow-y-auto p-4 md:p-6">
                @yield('content')
            </main>
        </div>
    </div>
    <x-page-loader />
    <x-connection-status />
    <x-session-keep-alive />

    <script src="{{ asset('js/confirm-swal.js') }}"></script>
    @stack('scripts')
    @if(session('success'))
    <script>Swal.fire({ icon: 'success', title: '{{ __('announcements.success') }}', text: @json(session('success')), timer: 3000, showConfirmButton: false });</script>
    @endif
    @if(session('error'))
    <script>Swal.fire({ icon: 'error', title: '{{ __('announcements.oops') }}', text: @json(session('error')) });</script>
    @endif
</body>
</html>
