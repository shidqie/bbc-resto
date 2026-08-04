<nav class="sticky top-0 z-50 bg-white border-b border-neutral-200"
     x-data="{ open: false, layanan: false, akun: false }">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="h-16 flex items-center justify-between gap-4">

            {{-- Logo --}}
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 shrink-0">
                <img src="{{ asset('images/logo-saung.png') }}" alt="Saung Babakan Cinta"
                     class="w-8 h-8 rounded object-contain">
                <span class="text-sm font-semibold tracking-tight text-neutral-900">Saung Babakan Cinta</span>
            </a>

            {{-- Desktop Menu --}}
            <div class="hidden lg:flex items-center gap-8">

                <a href="{{ route('home') }}#beranda" class="text-sm text-neutral-600 hover:text-neutral-900 transition">
                    Beranda
                </a>
                <a href="{{ route('home') }}#tentang" class="text-sm text-neutral-600 hover:text-neutral-900 transition">
                    Tentang
                </a>
                <a href="{{ route('home') }}#menu-dinein" class="text-sm text-neutral-600 hover:text-neutral-900 transition">
                    Menu
                </a>

                {{-- Dropdown Layanan --}}
                <div class="relative"
                     @mouseenter="layanan = true"
                     @mouseleave="layanan = false">
                    <button
                        @click="layanan = !layanan"
                        @focus="layanan = true"
                        @blur="layanan = false"
                        class="flex items-center gap-1 text-sm text-neutral-600 hover:text-neutral-900 transition">
                        Layanan
                        <svg class="w-3.5 h-3.5 transition-transform duration-200"
                             :class="layanan ? 'rotate-180' : ''"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="layanan"
                         x-cloak
                         x-transition
                         class="absolute left-0 top-full mt-2 w-48 rounded-xl bg-white border border-neutral-200 overflow-hidden">
                        <a href="{{ route('qr.scanner') }}"
                           class="block px-4 py-2.5 text-sm text-neutral-600 hover:bg-neutral-50 hover:text-neutral-900 transition">
                            Dine in
                        </a>
                        <a href="{{ route('home') }}#catering"
                           class="block px-4 py-2.5 text-sm text-neutral-600 hover:bg-neutral-50 hover:text-neutral-900 transition">
                            Catering
                        </a>
                        <a href="{{ route('home') }}#nasi-box"
                           class="block px-4 py-2.5 text-sm text-neutral-600 hover:bg-neutral-50 hover:text-neutral-900 transition">
                            Nasi Box
                        </a>
                    </div>
                </div>

                <a href="{{ route('home') }}#galeri" class="text-sm text-neutral-600 hover:text-neutral-900 transition">
                    Galeri
                </a>
                <a href="{{ route('home') }}#lacak-pesanan" class="text-sm text-neutral-600 hover:text-neutral-900 transition">
                    Lacak Pesanan
                </a>
                <a href="{{ route('home') }}#kontak" class="text-sm text-neutral-600 hover:text-neutral-900 transition">
                    Kontak
                </a>
            </div>

            {{-- Desktop Actions --}}
            <div class="hidden lg:flex items-center gap-6 shrink-0">

                @auth
                    <a href="{{ route('dashboard') }}"
                       class="text-sm text-neutral-600 hover:text-neutral-900 transition">
                        Dasbor Admin
                    </a>
                @endauth

                @auth('pelanggan')
                    <div class="relative"
                         @mouseenter="akun = true"
                         @mouseleave="akun = false">
                        <button
                            @click="akun = !akun"
                            @focus="akun = true"
                            @blur="akun = false"
                            class="flex items-center gap-1.5 text-sm text-neutral-600 hover:text-neutral-900 transition">
                            {{ Auth::guard('pelanggan')->user()->nama }}
                            <svg class="w-3.5 h-3.5 transition-transform duration-200"
                                 :class="akun ? 'rotate-180' : ''"
                                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="akun"
                             x-cloak
                             x-transition
                             class="absolute right-0 top-full mt-2 w-48 rounded-xl bg-white border border-neutral-200 overflow-hidden">
                            <a href="{{ route('konsumen.pesanan.index') }}"
                               class="block px-4 py-2.5 text-sm text-neutral-600 hover:bg-neutral-50 hover:text-neutral-900 transition">
                                Pesanan Saya
                            </a>
                            <a href="{{ route('konsumen.profile') }}"
                               class="block px-4 py-2.5 text-sm text-neutral-600 hover:bg-neutral-50 hover:text-neutral-900 transition">
                                Profil
                            </a>
                            <form method="POST" action="{{ route('konsumen.logout') }}">
                                @csrf
                                <button type="submit"
                                        class="w-full text-left px-4 py-2.5 text-sm text-neutral-600 hover:bg-neutral-50 hover:text-neutral-900 transition">
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('konsumen.login') }}"
                       class="text-sm text-neutral-600 hover:text-neutral-900 transition">
                        Masuk
                    </a>
                @endauth

                <a href="{{ route('home') }}#catering"
                   class="px-4 py-2 rounded-xl bg-neutral-900 text-white text-sm font-medium hover:bg-neutral-700 transition">
                    Pesan Sekarang
                </a>
            </div>

            {{-- Mobile Button --}}
            <button class="lg:hidden p-2 -mr-2 text-neutral-900" @click="open = !open" aria-label="Menu">
                <svg x-show="!open" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <svg x-show="open" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

        </div>
    </div>

    {{-- Mobile Menu --}}
    <div x-show="open"
         x-cloak
         x-transition
         class="lg:hidden border-t border-neutral-100">

        <div class="px-6 py-4 flex flex-col">

            <a href="{{ route('home') }}#beranda" class="py-3 text-sm text-neutral-600 border-b border-neutral-100">Beranda</a>
            <a href="{{ route('home') }}#tentang" class="py-3 text-sm text-neutral-600 border-b border-neutral-100">Tentang</a>
            <a href="{{ route('home') }}#menu-dinein" class="py-3 text-sm text-neutral-600 border-b border-neutral-100">Menu</a>
            <a href="{{ route('qr.scanner') }}" class="py-3 text-sm text-neutral-600 border-b border-neutral-100">Dine in</a>
            <a href="{{ route('home') }}#catering" class="py-3 text-sm text-neutral-600 border-b border-neutral-100">Catering</a>
            <a href="{{ route('home') }}#nasi-box" class="py-3 text-sm text-neutral-600 border-b border-neutral-100">Nasi Box</a>
            <a href="{{ route('home') }}#galeri" class="py-3 text-sm text-neutral-600 border-b border-neutral-100">Galeri</a>
            <a href="{{ route('home') }}#lacak-pesanan" class="py-3 text-sm text-neutral-600 border-b border-neutral-100">Lacak Pesanan</a>
            <a href="{{ route('home') }}#kontak" class="py-3 text-sm text-neutral-600 border-b border-neutral-100">Kontak</a>

            @auth
                <a href="{{ route('dashboard') }}" class="py-3 text-sm text-neutral-900 font-medium border-b border-neutral-100">Dasbor Admin</a>
            @endauth

            @auth('pelanggan')
                <a href="{{ route('konsumen.pesanan.index') }}" class="py-3 text-sm text-neutral-900 font-medium border-b border-neutral-100">Pesanan Saya</a>
                <a href="{{ route('konsumen.profile') }}" class="py-3 text-sm text-neutral-900 font-medium border-b border-neutral-100">Profil</a>
                <form method="POST" action="{{ route('konsumen.logout') }}" class="pt-4">
                    @csrf
                    <button type="submit" class="w-full py-2.5 rounded-xl border border-neutral-200 text-sm text-neutral-700 font-medium">
                        Keluar
                    </button>
                </form>
            @else
                <div class="pt-4 flex gap-2">
                    <a href="{{ route('konsumen.login') }}"
                       class="flex-1 py-2.5 rounded-xl border border-neutral-200 text-sm text-neutral-900 text-center font-medium">
                        Masuk
                    </a>
                    <a href="{{ route('konsumen.register') }}"
                       class="flex-1 py-2.5 rounded-xl bg-neutral-900 text-white text-sm text-center font-medium">
                        Daftar
                    </a>
                </div>
            @endauth

            <a href="{{ route('home') }}#catering"
               class="mt-3 py-2.5 rounded-xl bg-neutral-900 text-white text-center text-sm font-medium">
                Pesan Sekarang
            </a>

        </div>
    </div>

</nav>
