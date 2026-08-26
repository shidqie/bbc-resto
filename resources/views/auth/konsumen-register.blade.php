<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Daftar Konsumen · BBC Resto</title>

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

            <div class="absolute inset-0" style="background-image: url('{{ asset('images/homepage.webp') }}'); background-size: cover; background-position: center;"></div>
            <div class="absolute inset-0 bg-gradient-to-b from-black/70 via-black/50 to-black/80"></div>
            <div class="absolute inset-0 bg-primary/40"></div>

            <div class="relative z-10 flex items-center gap-2.5">
                <img src="{{ asset('images/logo-saung.png') }}" alt="BBC Resto" class="w-8 h-8 rounded-full object-contain bg-white/10 p-0.5">
                <span class="text-white/80 text-sm font-semibold tracking-wide">BBC Resto</span>
            </div>

            <div class="relative z-10">
                <div class="inline-flex items-center gap-2 bg-white/10 border border-white/15 rounded-full px-3.5 py-1.5 mb-6">
                    <x-heroicon-o-user-plus class="w-3 h-3 text-white/60" />
                    <span class="text-white/60 text-sm font-semibold uppercase tracking-[0.1em]">Customer Access</span>
                </div>
                <h1 class="text-3xl xl:text-4xl font-bold text-white leading-snug mb-4">
                    Buat Akun,<br>Monitor Pesanan.
                </h1>
                <p class="text-white/60 text-sm leading-relaxed max-w-xs">
                    Sudah pernah memesan? Daftar dengan nomor HP yang sama agar riwayat pesanan otomatis terhubung.
                </p>
            </div>

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

                <div class="lg:hidden flex items-center gap-2.5 mb-10">
                    <img src="{{ asset('images/logo-saung.png') }}" alt="BBC Resto" class="w-8 h-8 rounded-full object-contain">
                    <span class="text-sm font-bold text-body tracking-wide">BBC Resto · Konsumen</span>
                </div>

                <div class="mb-8 fu d1">
                    <h2 class="text-2xl font-bold text-body tracking-tight mb-1">Daftar Akun</h2>
                    <p class="text-sm text-gray-500 font-medium">Pantau status pesanan catering & nasi box Anda.</p>
                </div>

                <x-auth-session-status class="mb-5 text-sm" :status="session('status')" />

                @if($errors->any())
                    <div class="mb-5 p-3.5 bg-red-50 border border-red-100 rounded-xl text-sm text-red-600 font-medium fu d1">
                        <x-heroicon-o-exclamation-circle class="mr-2 text-red-400 w-5 h-5" />{{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('konsumen.register') }}" class="space-y-4" x-data="{ pw: '', pw2: '', get match() { return !this.pw2 || this.pw === this.pw2 } }">
                    @csrf

                    <div class="fu d2">
                        <label for="nama" class="block text-xs font-bold text-gray-700 mb-1">Nama Lengkap</label>
                        <input id="nama" type="text" name="nama" value="{{ old('nama') }}"
                               required autofocus autocomplete="name"
                               placeholder="Nama Anda"
                               class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-xs font-medium text-gray-900 placeholder-gray-300 transition-all duration-200 focus:border-primary focus:ring-1 focus:ring-primary/20 outline-none">
                    </div>

                    <div class="fu d2">
                        <x-input-wa name="nomor_telepon" label="Nomor WhatsApp" :value="old('nomor_telepon')" :required="true" hint="Gunakan nomor whatsapp yang aktif." :readonly="false" />
                    </div>

                    <div class="fu d3">
                        <label for="email" class="block text-xs font-bold text-gray-700 mb-1">Email <span class="text-gray-400 font-normal">(opsional)</span></label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}"
                               autocomplete="email"
                               placeholder="nama@email.com"
                               class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-xs font-medium text-gray-900 placeholder-gray-300 transition-all duration-200 focus:border-primary focus:ring-1 focus:ring-primary/20 outline-none">
                    </div>

                    <div class="fu d3">
                        <label for="kata_sandi" class="block text-xs font-bold text-gray-700 mb-1">Password</label>
                        <input id="kata_sandi" type="password" name="kata_sandi" x-model="pw" required minlength="8" autocomplete="new-password"
                               placeholder="Minimal 8 karakter"
                               class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-xs font-medium text-gray-900 placeholder-gray-300 transition-all duration-200 focus:border-primary focus:ring-1 focus:ring-primary/20 outline-none">
                        <p class="text-xs text-gray-400 font-medium mt-1">Minimal 8 karakter.</p>
                    </div>

                    <div class="fu d3">
                        <label for="kata_sandi_confirmation" class="block text-xs font-bold text-gray-700 mb-1">Ulangi Password</label>
                        <input id="kata_sandi_confirmation" type="password" name="kata_sandi_confirmation" x-model="pw2" required autocomplete="new-password"
                               placeholder="••••••••"
                               class="w-full px-3.5 py-2 bg-white border rounded-xl text-xs font-medium text-gray-900 placeholder-gray-300 transition-all duration-200 focus:border-primary focus:ring-1 focus:ring-primary/20 outline-none"
                                :class="pw2 && !match ? 'border-red-300' : 'border-gray-200'">
                        <p class="text-xs text-red-500 font-medium mt-1" x-show="pw2 && !match" x-cloak>Password tidak cocok.</p>
                    </div>

                    <div class="pt-1 fu d4">
                        <button type="submit"
                                class="w-full py-2.5 bg-primary hover:bg-primary-container text-white font-semibold text-xs rounded-xl transition-all duration-200 active:scale-[0.99]">
                            Daftar
                        </button>
                    </div>
                </form>

                <p class="text-center text-sm text-gray-500 font-medium mt-6 fu d4">
                    Sudah punya akun?
                    <a href="{{ route('konsumen.login') }}" class="text-primary font-bold hover:opacity-70 transition-opacity">Masuk</a>
                </p>

            </div>
        </div>
    </div>
</body>
</html>