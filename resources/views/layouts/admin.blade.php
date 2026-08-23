<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>KidLink - @yield('title', 'Admin')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
    @stack('styles')
</head>
<body class="bg-[#faf8f3] font-sans antialiased">
    <div class="flex h-screen bg-[#faf8f3]">
        @include('components.admin_sidebar')
        <div class="flex-1 flex flex-col overflow-hidden md:ml-64">
            <header class="bg-white shadow-sm border-b border-[#ebe8e1] px-6 py-4">
                <div class="flex items-center justify-between">
                    <h1 class="text-2xl font-bold text-[#182438]">@yield('page-title', 'Admin Dashboard')</h1>
                    <div class="flex items-center gap-4">
                        <span class="text-sm text-[#5f6873]">Welcome, {{ auth()->user()->full_name }}</span>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="bg-[#f05d56] hover:bg-[#e04942] text-white px-4 py-2 rounded-lg text-sm transition">Logout</button>
                        </form>
                    </div>
                </div>
            </header>
            <main class="flex-1 overflow-y-auto p-6">
                @yield('content')
            </main>
        </div>
    </div>
    @stack('scripts')
    @if(session('success'))
    <script>Swal.fire({ icon: 'success', title: 'Success!', text: '{{ session('success') }}', timer: 3000, showConfirmButton: false });</script>
    @endif
    @if(session('error'))
    <script>Swal.fire({ icon: 'error', title: 'Oops!', text: '{{ session('error') }}' });</script>
    @endif
</body>
</html>