<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Saung Babakan Cinta') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        
        <style>
            body { font-family: 'Google Sans', 'Outfit', sans-serif; }
        </style>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    </head>
    <body class="font-sans antialiased bg-gray-50 text-[#111827] min-h-screen flex selection:bg-[#3B82F6] selection:text-white">
        
        <!-- Layar Kiri: Gambar & Branding (Sembunyi di HP) -->
        <div class="hidden lg:flex lg:w-1/2 relative items-center justify-center border-r border-gray-200 bg-white">
            <!-- Background Image -->
            <div class="absolute inset-0 bg-cover bg-center opacity-40" style="background-image: url('{{ asset('images/homepage.webp') }}'); filter: saturate(0.8) contrast(1.1);"></div>
            <!-- Overlay Gradient agar elegan -->
            <div class="absolute inset-0 bg-gradient-to-tr from-white via-white/90 to-white/50"></div>
            <div class="absolute inset-0 bg-[#3B82F6]/5 mix-blend-overlay"></div>

            <!-- Teks Branding -->
            <div class="relative z-10 p-12 text-center max-w-lg mt-12">
                <div class="inline-flex items-center justify-center w-24 h-24 rounded-3xl bg-white shadow-xl shadow-[#3B82F6]/10 mb-8 border border-gray-100">
                    <x-heroicon-s-building-storefront class="w-12 h-12 text-[#3B82F6]" />
                </div>
                <h1 class="text-4xl font-bold text-[#111827] mb-4 tracking-tight">Saung Babakan Cinta</h1>
                <p class="text-gray-600 text-lg leading-relaxed font-light">
                    Sistem terpadu untuk kemudahan pemesanan menu favorit Anda, serta kelola operasional restoran secara modern dan elegan.
                </p>
            </div>
        </div>

        <!-- Layar Kanan: Area Form -->
        <div class="w-full lg:w-1/2 flex flex-col items-center justify-center p-6 sm:p-12 relative bg-white lg:bg-gray-50/50">
            
            <!-- Logo untuk versi HP -->
            <div class="lg:hidden mb-8 flex flex-col items-center">
                <a href="/" class="flex items-center gap-3">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-white shadow-lg shadow-[#3B82F6]/10 border border-gray-100">
                        <x-heroicon-s-building-storefront class="w-7 h-7 text-[#3B82F6]" />
                    </div>
                    <span class="font-bold text-[#111827] text-2xl tracking-widest">SBC.</span>
                </a>
            </div>

            <!-- Kontainer Form (Slot) -->
            <div class="w-full sm:max-w-md bg-white p-8 sm:p-10 rounded-[24px] shadow-2xl shadow-gray-200/50 relative z-10 transition-all duration-300 hover:shadow-gray-300/50">
                {{ $slot }}
            </div>
        </div>
        
    </body>
</html>
