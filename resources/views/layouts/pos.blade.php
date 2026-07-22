<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Saung Babakan Cinta') }} - @yield('title', 'Point of Sale')</title>

    <!-- Google Fonts Design System Tokens -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anonymous+Pro:wght@400;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --font-primary: 'Plus Jakarta Sans', 'Google Sans', sans-serif;
            --font-mono: 'Anonymous Pro', monospace;
            --color-primary: #0F2E23;
            --color-secondary: #3B82F6;
            --color-surface: #FFFFFF;
            --color-text: #111827;
            --radius-sm: 4px;
            --radius-md: 8px;
            --radius-lg: 12px;
        }

        body {
            font-family: var(--font-primary);
            color: var(--color-text);
            background-color: #F8FAFC;
        }

        .font-mono-caps {
            font-family: var(--font-mono);
            font-size: 0.875rem; /* 14px */
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>
<body class="admin-shell antialiased bg-[#F8FAFC] text-[#111827] h-screen overflow-hidden flex" x-data="{ sidebarOpen: window.innerWidth > 1024 }" @resize.window="sidebarOpen = window.innerWidth > 1024">

    <!-- Sidebar -->
    @include('partials.sidebar')

    <!-- Main Content -->
    <main class="flex-1 flex flex-col min-w-0 relative z-10 transition-all duration-300 bg-[#F8FAFC]">
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
