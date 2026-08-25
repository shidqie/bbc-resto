<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Portal Staff · BBC Resto</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo-saung.png') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo-saung.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">


    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        *, body { font-family: 'Outfit', sans-serif; }
        input:focus { outline: none; box-shadow: 0 0 0 3px rgba(15,46,35,0.08); }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .fu  { animation: fadeUp .45s ease both; }
        .d1  { animation-delay: .08s; }
        .d2  { animation-delay: .16s; }
        .d3  { animation-delay: .24s; }
        .d4  { animation-delay: .32s; }
    </style>
</head>
<body class="min-h-screen bg-secondary-soft flex antialiased text-body">

    <div class="flex min-h-screen w-full">

        {{-- ── LEFT PANEL ── --}}
        <div class="hidden lg:flex lg:w-2/5 flex-col justify-between p-12 xl:p-16 relative overflow-hidden">

            {{-- Full photo background --}}
            <div class="absolute inset-0" style="background-image: url('{{ asset('images/homepage.webp') }}'); background-size: cover; background-position: center;"></div>
            {{-- Dark gradient overlay --}}
            <div class="absolute inset-0 bg-gradient-to-b from-black/70 via-black/50 to-black/80"></div>
            {{-- Color tint --}}
            <div class="absolute inset-0 bg-primary/40"></div>

            {{-- Top: Logo --}}
            <div class="relative z-10 flex items-center gap-2.5">
                <img src="{{ asset('images/logo-saung.png') }}" alt="BBC Resto" class="w-8 h-8 rounded-full object-contain bg-white/10 p-0.5">
                <span class="text-white/80 text-sm font-semibold tracking-wide">BBC Resto</span>
            </div>

            {{-- Middle --}}
            <div class="relative z-10">
                <div class="inline-flex items-center gap-2 bg-white/10 border border-white/15 rounded-full px-3.5 py-1.5 mb-6">
                    <x-heroicon-o-shield-check class="w-3 h-3 text-white/60" />
                    <span class="text-white/60 text-xs font-semibold uppercase tracking-[0.1em]">Staff Access</span>
                </div>
                <h1 class="text-2xl font-bold text-white leading-snug mb-3">
                    Portal<br>Internal.
                </h1>
                <p class="text-white/70 text-xs leading-relaxed max-w-xs">
                    Akses khusus untuk tim kasir, manajer, dapur & admin. Gunakan kredensial yang telah diberikan.
                </p>
            </div>

            {{-- Bottom --}}
            <div class="relative z-10">
                <p class="text-white/30 text-xs">© 2026 Saung Babakan Cinta</p>
            </div>
        </div>

        {{-- ── RIGHT: FORM ── --}}
        <div class="flex-1 relative flex items-center justify-center px-6 py-16 sm:px-12 bg-secondary-soft">

            <a href="{{ route('home') }}"
               class="absolute top-6 left-6 sm:top-8 sm:left-8 inline-flex items-center gap-1.5 text-xs font-bold text-gray-500 hover:text-primary transition-colors fu d1">
                <x-heroicon-o-arrow-left class="w-4 h-4" />
                Kembali
            </a>

            <div class="w-full max-w-[360px]">

                {{-- Mobile logo --}}
                <div class="lg:hidden flex items-center gap-2.5 mb-10">
                    <img src="{{ asset('images/logo-saung.png') }}" alt="BBC Resto" class="w-8 h-8 rounded-full object-contain">
                    <span class="text-sm font-bold text-body tracking-wide">BBC Resto · Staff</span>
                </div>

                {{-- Heading --}}
                <div class="mb-6 fu d1">
                    <h2 class="text-xl font-bold text-body tracking-tight mb-1">Masuk Portal Staff</h2>
                    <p class="text-xs text-gray-500 font-medium">Gunakan Email atau No. WhatsApp & password akun staff Anda.</p>
                </div>

                <x-auth-session-status class="mb-5 text-sm" :status="session('status')" />

                @if($errors->any())
                    <div class="mb-5 p-3.5 bg-red-50 border border-red-100 rounded-xl text-sm text-red-600 font-medium fu d1">
                        <x-heroicon-o-exclamation-circle class="mr-2 text-red-400 w-5 h-5" />{{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.login') }}" class="space-y-4">
                    @csrf

                    {{-- Email / HP --}}
                    <div class="fu d2">
                        <label for="login" class="block text-xs font-bold text-gray-700 mb-1">Email / No. WhatsApp</label>
                        <input id="login" type="text" name="login" value="{{ old('login') }}"
                               required autofocus autocomplete="username"
                               placeholder="admin@resto.com / 08xxxxxxxxxx"
                               class="w-full px-3.5 py-2 bg-white border rounded-xl text-xs font-medium text-gray-900 placeholder-gray-300 transition-all duration-200 focus:border-primary focus:ring-1 focus:ring-primary/20 outline-none {{ $errors->has('login') ? 'border-red-300' : 'border-gray-200' }}">
                    </div>

                    {{-- Password --}}
                    <div class="fu d3" x-data="{ show: false }">
                        <div class="flex items-center justify-between mb-1">
                            <label for="password" class="block text-xs font-bold text-gray-700">Password</label>
                            @if(Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-xs font-semibold text-primary hover:opacity-70 transition-opacity">Lupa password?</a>
                            @endif
                        </div>
                        <div class="relative">
                            <input id="password" :type="show ? 'text' : 'password'" name="password"
                                   required autocomplete="current-password"
                                   placeholder="••••••••"
                                   class="w-full px-3.5 py-2 pr-10 bg-white border rounded-xl text-xs font-medium text-gray-900 placeholder-gray-300 transition-all duration-200 focus:border-primary focus:ring-1 focus:ring-primary/20 outline-none {{ $errors->has('password') ? 'border-red-300' : 'border-gray-200' }}">
                            <button type="button" @click="show = !show"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-300 hover:text-gray-500 transition-colors">
                                <x-heroicon-o-eye class="w-4 h-4" x-show="!show" />
                                <x-heroicon-o-sparkles class="w-4 h-4" x-show="show" style="display:none" />
                            </button>
                        </div>
                    </div>

                    {{-- Remember --}}
                    <div class="flex items-center gap-2 fu d3">
                        <input id="remember_me" type="checkbox" name="remember"
                               class="w-3.5 h-3.5 rounded border-gray-300 text-primary focus:ring-primary/20 transition-all cursor-pointer">
                        <label for="remember_me" class="text-xs text-gray-500 font-medium cursor-pointer select-none">Ingat saya</label>
                    </div>

                    {{-- Submit --}}
                    <div class="pt-1 fu d4">
                        <button type="submit"
                                class="w-full py-2.5 bg-primary hover:bg-primary-container text-white font-semibold text-xs rounded-xl transition-all duration-200 active:scale-[0.99]">
                            Masuk
                        </button>
                    </div>
                </form>



            </div>
        </div>
    </div>
</body>
</html>
