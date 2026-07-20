<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Saung Babakan Cinta') }} - @yield('title', 'Point of Sale')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anonymous+Pro:ital,wght@0,400;0,700;1,400;1,700&family=Inter:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])


</head>
<body class="admin-shell font-sans antialiased bg-[#f8f9fa] text-gray-800 h-screen overflow-hidden flex" x-data="{ sidebarOpen: window.innerWidth > 1024 }" @resize.window="sidebarOpen = window.innerWidth > 1024">

    <!-- Sidebar -->
    @include('partials.sidebar')

    <!-- Main Content -->
    <main class="flex-1 flex flex-col min-w-0 relative z-10 transition-all duration-300 bg-gray-50">
        @yield('content')
    </main>

    <style>
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>

    @stack('scripts')
</body>
</html>
