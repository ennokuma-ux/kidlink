<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>KidLink - @yield('title', 'Daycare Management')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
    @stack('styles')
</head>
<body class="bg-[#faf8f3] antialiased">
    @yield('content')

    @stack('scripts')
    @if(session('success'))
    <script>
        Swal.fire({ icon: 'success', title: 'Success!', text: '{{ session('success') }}', timer: 3000, showConfirmButton: false });
    </script>
    @endif
    @if(session('info'))
    <script>
        Swal.fire({ icon: 'info', title: 'Notice', text: '{{ session('info') }}', timer: 3000, showConfirmButton: false });
    </script>
    @endif
    @if($errors->any())
    <script>
        Swal.fire({ icon: 'error', title: 'Error!', text: '{{ $errors->first() }}', timer: 5000, showConfirmButton: false });
    </script>
    @endif
</body>
</html>
