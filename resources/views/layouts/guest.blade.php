<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            .admin-shell .bg-primary { background-color: #3B82F6 !important; }
            .admin-shell .text-primary { color: #3B82F6 !important; }
            .admin-shell .border-primary { border-color: #3B82F6 !important; }
            .admin-shell .focus\:border-primary:focus { border-color: #3B82F6 !important; }
            .admin-shell .focus\:ring-primary:focus { --tw-ring-color: #3B82F6 !important; }
        </style>
    </head>
    <body class="admin-shell font-sans antialiased bg-[#111827] text-gray-300 min-h-screen flex">
        
        <!-- Layar Kiri: Gambar & Branding (Sembunyi di HP) -->
        <div class="hidden lg:flex lg:w-1/2 relative items-center justify-center border-r border-gray-800">
            <!-- Background Image -->
            <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1555396273-367ea4eb4db5?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80');">
                <!-- Overlay Gradient agar teks terbaca -->
                <div class="absolute inset-0 bg-gradient-to-t from-[#111827] via-[#111827]/80 to-[#111827]/40"></div>
                <div class="absolute inset-0 bg-primary/10 mix-blend-overlay"></div>
            </div>

            <!-- Teks Branding -->
            <div class="relative z-10 p-12 text-center max-w-lg mt-32">
                <x-heroicon-s-building-storefront class="w-24 h-24 text-primary mx-auto mb-6 drop-shadow-xl" />
                <h1 class="text-4xl font-bold text-white mb-4 tracking-tight">Saung Babakan Cinta</h1>
                <p class="text-gray-300 text-lg leading-relaxed">
                    Sistem Point of Sale modern untuk mengelola pesanan, meja, dan laporan dengan lebih mudah dan elegan.
                </p>
            </div>
        </div>

        <!-- Layar Kanan: Area Form -->
        <div class="w-full lg:w-1/2 flex flex-col items-center justify-center p-6 sm:p-12 relative bg-[#111827]">
            
            <!-- Logo untuk versi HP -->
            <div class="lg:hidden mb-8 flex flex-col items-center">
                <a href="/" class="flex items-center gap-3">
                    <x-heroicon-s-building-storefront class="w-12 h-12 text-primary" />
                    <span class="font-bold text-white text-2xl tracking-widest">SBC.</span>
                </a>
            </div>

            <!-- Kontainer Form (Slot) -->
            <div class="w-full sm:max-w-md bg-transparent p-8 sm:p-10 relative z-10">
                {{ $slot }}
            </div>
            
            <!-- Footer -->
            <div class="mt-12 text-center text-xs text-gray-600">
                &copy; {{ date('Y') }} SBC Resto. All rights reserved.
            </div>
        </div>
        
    </body>
</html>
