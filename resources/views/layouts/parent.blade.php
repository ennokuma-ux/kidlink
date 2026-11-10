<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#ff8555">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <meta name="description" content="{{ __('KidLink: early childhood learning, attendance and progress for teachers, parents and children.') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>KidLink - @yield('title', __('page.parent_dashboard'))</title>
    
    
    <link rel="preload" href="{{ asset('fonts/UcC73FwrK3iLTeHuS_nVMrMxCp50SjIa1ZL7.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link href="{{ \App\Support\Asset::v('css/fonts.css') }}" rel="stylesheet">
    <link href="{{ \App\Support\Asset::v('css/kidlink-theme.css') }}" rel="stylesheet">
    <link href="{{ \App\Support\Asset::v('css/icons.css') }}" rel="stylesheet">
    <x-swal-lazy :prefetch="true" />
    <script defer src="{{ \App\Support\Asset::v('js/alpine.min.js') }}"></script>
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
    <link href="{{ \App\Support\Asset::v('css/tailwind.css') }}" rel="stylesheet">
<style>
    input.password-entry::-ms-reveal,
    input.password-entry::-ms-clear,
    input.password-entry::-webkit-credentials-auto-fill-button {
        display: none !important;
        visibility: hidden !important;
        pointer-events: none !important;
    }
</style>
</head>
<body class="bg-[#fff8e7] font-sans antialiased">
    <div class="flex h-screen h-[100dvh] bg-[#fff8e7]" x-data="{ navOpen: false }" @keydown.escape.window="navOpen = false">
        @include('components.parent_sidebar')
        <div x-show="navOpen" x-cloak style="display:none;" @click="navOpen = false" class="fixed inset-0 bg-black/50 z-30 md:hidden" aria-hidden="true"></div>
        <div class="flex-1 flex flex-col overflow-hidden md:ml-64 min-w-0">
            <header class="bg-white shadow-sm border-b border-[#f1e7cc] px-4 md:px-6 py-3 md:py-4">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <button type="button" @click="navOpen = true" class="relative md:hidden shrink-0 w-10 h-10 -ml-1 rounded-lg border border-[#f1e7cc] text-[#182438] flex items-center justify-center" aria-label="Open menu">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                            <x-announcement-badge dot />
                        </button>
                        <h1 class="text-lg md:text-2xl font-bold text-[#182438] truncate">@yield('page-title', __('page.dashboard'))</h1>
                    </div>
                    <div class="flex items-center gap-2 md:gap-4 shrink-0">
                        <x-lang_switcher />
                        <span class="hidden lg:inline text-sm text-[#5f6873]">{{ __('header.welcome', ['name' => auth()->user()->full_name]) }}</span>
                        @include('components.child_mode')
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="bg-[#ff595e] hover:bg-[#e8454a] text-white px-3 md:px-4 py-2 rounded-lg text-sm transition">{{ __('header.logout') }}</button>
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

    <script>
    // One active device per parent account on each parent page.
    // The server issues a unique HttpOnly device cookie and validates every page request.
    (() => {
        const pageKey = @json(request()->route()->getName());
        const heartbeatUrl = @json(route('parent.page-lock.heartbeat'));
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const heartbeat = async () => {
            try {
                const body = new URLSearchParams({ page_key: pageKey });
                const response = await fetch(heartbeatUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
                    },
                    body
                });
                if (response.status === 409) {
                    window.location.reload();
                }
            } catch (error) {
                // The 90-second server lease handles brief network interruptions.
            }
        };
        const timer = window.setInterval(heartbeat, 30000);
        window.addEventListener('pagehide', () => window.clearInterval(timer));
    })();
    </script>

    <script src="{{ \App\Support\Asset::v('js/ajax-filters.js') }}"></script>
    @stack('scripts')
    @if(session('success'))
    <script>Swal.fire({ icon: 'success', title: @json(__('misc.success_title')), text: @json(session('success')), timer: 3000, showConfirmButton: false });</script>
    @endif
    @if(session('error'))
    <script>Swal.fire({ icon: 'error', title: @json(__('misc.oops_title')), text: @json(session('error')) });</script>
    @endif

    @if($errors->any())
    <script>
        Swal.fire({
            icon: 'error',
            title: @json(__('misc.error_title')),
            text: @json($errors->first()),
            confirmButtonText: @json(__('OK')),
        });
    </script>
    @endif

    @if(session('info'))
    <script>Swal.fire({ icon: 'info', title: @json(__('misc.notice_title')), text: @json(session('info')), timer: 3500, showConfirmButton: false });</script>
    @endif

    @if(auth()->check() && auth()->user()->role === 'parent' && auth()->user()->must_change_password)
    <script>
    (() => {
        const endpoint = @json(route('parent.password.initial'));
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
        Swal.fire({
            title: 'Set your new password',
            text: 'For your account security, please create a password you want to use before continuing.',
            html: '<div class="parent-password-fields"><input id="firstParentPassword" type="password" class="swal2-input password-entry" placeholder="New password" autocomplete="off" autocapitalize="off" spellcheck="false"><input id="confirmParentPassword" type="password" class="swal2-input password-entry" placeholder="Confirm new password" autocomplete="off" autocapitalize="off" spellcheck="false"></div>', 
            customClass: { popup: 'parent-password-popup' },
            confirmButtonText: 'Save password',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showCancelButton: false,
            focusConfirm: false,
            didOpen: () => {
                document.querySelectorAll('.parent-password-fields .swal2-input').forEach(input => {
                    input.style.cssText = 'display:block;width:100%;box-sizing:border-box;margin:0 0 12px;padding:13px 16px;border:2px solid #e7dcc5;border-radius:14px;background:#fff;box-shadow:none;outline:none;font-size:15px;';
                    input.addEventListener('focus', () => input.style.borderColor = '#ff8555');
                    input.addEventListener('blur', () => input.style.borderColor = '#e7dcc5');
                });
                const fields = document.querySelector('.parent-password-fields');
                if (fields) fields.style.cssText = 'display:flex;flex-direction:column;gap:4px;width:100%;';
            },
            preConfirm: async () => {
                const password = document.getElementById('firstParentPassword').value;
                const confirmation = document.getElementById('confirmParentPassword').value;
                if (password.length < 8) { Swal.showValidationMessage('Use at least 8 characters.'); return false; }
                if (password !== confirmation) { Swal.showValidationMessage('The passwords do not match.'); return false; }
                try {
                    const response = await fetch(endpoint, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                        body: JSON.stringify({ password, password_confirmation: confirmation })
                    });
                    const data = await response.json();
                    if (!response.ok) throw new Error(data.message || Object.values(data.errors || {}).flat()[0] || 'Unable to update password. Please try again.');
                    return data;
                } catch (error) {
                    Swal.showValidationMessage(error.message);
                    return false;
                }
            }
        }).then(result => { if (result.isConfirmed && result.value?.ok) window.location.reload(); });
    })();
    </script>
    @endif
</body>
</html>
