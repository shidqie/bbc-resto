<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Portal Staff · BBC Resto</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontSize: {
                        xs: ['13px', '1.45'],
                        sm: ['15px', '1.5'],
                        base: ['16px', '1.55'],
                        lg: ['18px', '1.5'],
                        xl: ['20px', '1.4'],
                        '2xl': ['24px', '1.3'],
                        '3xl': ['30px', '1.25'],
                        '4xl': ['36px', '1.2'],
                        '5xl': ['48px', '1.15'],
                        '6xl': ['60px', '1.1'],
                    },
                }
            }
        }
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        *, body { font-family: 'Plus Jakarta Sans', sans-serif; }
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
<body class="min-h-screen bg-[#f5f5f0] flex antialiased text-[#111827]">

    <div class="flex min-h-screen w-full">

        {{-- ── LEFT PANEL ── --}}
        <div class="hidden lg:flex lg:w-2/5 flex-col justify-between p-12 xl:p-16 relative overflow-hidden">

            {{-- Full photo background --}}
            <div class="absolute inset-0" style="background-image: url('{{ asset('images/homepage.webp') }}'); background-size: cover; background-position: center;"></div>
            {{-- Dark gradient overlay --}}
            <div class="absolute inset-0 bg-gradient-to-b from-black/70 via-black/50 to-black/80"></div>
            {{-- Color tint --}}
            <div class="absolute inset-0 bg-[#0D3024]/40"></div>

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
                <h1 class="text-3xl xl:text-4xl font-bold text-white leading-snug mb-4">
                    Portal<br>Internal.
                </h1>
                <p class="text-white/60 text-sm leading-relaxed max-w-xs">
                    Akses khusus untuk tim kasir, manajer, dapur & admin. Gunakan kredensial yang telah diberikan.
                </p>
            </div>

            {{-- Bottom --}}
            <div class="relative z-10">
                <p class="text-white/30 text-xs">© 2026 Saung Babakan Cinta</p>
            </div>
        </div>

        {{-- ── RIGHT: FORM ── --}}
        <div class="flex-1 relative flex items-center justify-center px-6 py-16 sm:px-12 bg-[#f5f5f0]">

            <a href="{{ route('home') }}"
               class="absolute top-6 left-6 sm:top-8 sm:left-8 inline-flex items-center gap-1.5 pl-2.5 pr-3.5 py-1.5 rounded-full bg-white border border-gray-200 text-sm font-semibold text-gray-600 hover:text-[#0D3024] shadow-sm transition-colors">
                <x-heroicon-o-arrow-left class="w-4 h-4" />
                Kembali
            </a>

            <div class="w-full max-w-[360px]">

                {{-- Mobile logo --}}
                <div class="lg:hidden flex items-center gap-2.5 mb-10">
                    <div class="w-8 h-8 rounded-full bg-[#0D3024] flex items-center justify-center">
                        <x-heroicon-o-shield-check class="text-white w-3 h-3" />
                    </div>
                    <span class="text-sm font-bold text-[#111827] tracking-wide">BBC Resto · Staff</span>
                </div>

                {{-- Heading --}}
                <div class="mb-8 fu d1">
                    <h2 class="text-2xl font-bold text-[#111827] tracking-tight mb-1">Masuk Portal Staff</h2>
                    <p class="text-sm text-gray-500 font-medium">Gunakan Email atau Nomor HP & password akun staff Anda.</p>
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
                        <label for="login" class="block text-sm font-semibold text-gray-400 uppercase tracking-[0.1em] mb-1.5">Email / No. HP</label>
                        <input id="login" type="text" name="login" value="{{ old('login') }}"
                               required autofocus autocomplete="username"
                               placeholder="admin@resto.com / 08xxxxxxxxxx"
                               class="w-full px-4 py-3 bg-white border rounded-xl text-sm font-medium text-gray-900 placeholder-gray-300 transition-all duration-200 focus:border-[#0D3024] {{ $errors->has('login') ? 'border-red-300' : 'border-gray-200' }}">
                    </div>

                    {{-- Password --}}
                    <div class="fu d3" x-data="{ show: false }">
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="block text-sm font-semibold text-gray-400 uppercase tracking-[0.1em]">Password</label>
                            @if(Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-xs font-semibold text-[#0D3024] hover:opacity-70 transition-opacity">Lupa password?</a>
                            @endif
                        </div>
                        <div class="relative">
                            <input id="password" :type="show ? 'text' : 'password'" name="password"
                                   required autocomplete="current-password"
                                   placeholder="••••••••"
                                   class="w-full px-4 py-3 pr-11 bg-white border rounded-xl text-sm font-medium text-gray-900 placeholder-gray-300 transition-all duration-200 focus:border-[#0D3024] {{ $errors->has('password') ? 'border-red-300' : 'border-gray-200' }}">
                            <button type="button" @click="show = !show"
                                    class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-300 hover:text-gray-500 transition-colors">
                                <x-heroicon-o-eye class="w-4 h-4" x-show="!show" />
                                <x-heroicon-o-sparkles class="w-4 h-4" x-show="show" style="display:none" />
                            </button>
                        </div>
                    </div>

                    {{-- Remember --}}
                    <div class="flex items-center gap-2 fu d3">
                        <input id="remember_me" type="checkbox" name="remember"
                               class="w-4 h-4 rounded border-gray-300 text-[#0D3024] focus:ring-[#0D3024]/20 transition-all cursor-pointer">
                        <label for="remember_me" class="text-sm text-gray-400 font-medium cursor-pointer select-none">Ingat saya</label>
                    </div>

                    {{-- Submit --}}
                    <div class="pt-1 fu d4">
                        <button type="submit"
                                class="w-full py-3.5 bg-[#0D3024] hover:bg-[#1a4a35] text-white font-semibold text-sm rounded-xl transition-all duration-200 active:scale-[0.99]">
                            Masuk
                        </button>
                    </div>
                </form>



            </div>
        </div>
    </div>
</body>
</html>
