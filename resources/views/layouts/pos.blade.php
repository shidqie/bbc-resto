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

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Override primary colors back to blue for admin panel */
        .admin-shell { --tw-primary: #3B82F6; }
        .admin-shell .bg-primary { background-color: #3B82F6 !important; }
        .admin-shell .text-primary { color: #3B82F6 !important; }
        .admin-shell .border-primary { border-color: #3B82F6 !important; }
        .admin-shell .focus\:border-primary:focus { border-color: #3B82F6 !important; }
        .admin-shell .focus\:ring-primary:focus { --tw-ring-color: #3B82F6 !important; }
        .admin-shell .hover\:bg-primary:hover { background-color: #3B82F6 !important; }
        .admin-shell .hover\:text-primary:hover { color: #3B82F6 !important; }
    </style>
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
</body>
</html>
