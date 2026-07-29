<nav class="sticky top-0 z-50 bg-white/90 backdrop-blur-md border-b border-primary/10 shadow-sm"
     x-data="{ open: false, layanan: false }">

    <div class="max-w-7xl mx-auto px-6">
        <div class="h-20 flex items-center justify-between">

            <!-- Logo -->
            <a href="{{ route('home') }}"
               class="font-serif text-2xl font-bold tracking-tight text-primary">
                Saung Babakan Cinta
            </a>

            <!-- Desktop Menu -->
            <div class="hidden lg:flex items-center gap-8">

                <a href="{{ route('home') }}#beranda"
                   class="text-sm font-semibold text-body hover:text-primary transition">
                    Beranda
                </a>

                <a href="{{ route('home') }}#tentang"
                   class="text-sm font-semibold text-body hover:text-primary transition">
                    Tentang
                </a>

                <a href="{{ route('home') }}#menu-dinein"
                   class="text-sm font-semibold text-body hover:text-primary transition">
                    Menu
                </a>

                <!-- Dropdown Layanan -->
                <div class="relative"
                     @mouseenter="layanan = true"
                     @mouseleave="layanan = false">

                    <button
                        @click="layanan = !layanan"
                        class="flex items-center gap-1 text-sm font-semibold text-body hover:text-primary transition">

                        Layanan

                        <svg class="w-4 h-4 transition-transform duration-200"
                             :class="layanan ? 'rotate-180' : ''"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="layanan"
                         x-cloak
                         x-transition
                         class="absolute left-1/2 top-full -translate-x-1/2 mt-3 w-48 rounded-xl bg-white border border-primary/10 shadow-xl overflow-hidden">

                        <a href="{{ route('home') }}#catering"
                           class="block px-4 py-3 text-sm hover:bg-primary/5 hover:text-primary transition">
                            Catering
                        </a>

                        <a href="{{ route('home') }}#nasi-box"
                           class="block px-4 py-3 text-sm hover:bg-primary/5 hover:text-primary transition">
                            Nasi Box
                        </a>

                        <a href="{{ route('qr.scanner') }}"
                           class="block px-4 py-3 text-sm hover:bg-primary/5 hover:text-primary transition text-emerald-600 font-bold">
                            <i class="fa-solid fa-qrcode mr-1"></i> QR Self Ordering
                        </a>
                    </div>
                </div>

                <a href="{{ route('home') }}#galeri"
                   class="text-sm font-semibold text-body hover:text-primary transition">
                    Galeri
                </a>

                <a href="{{ route('home') }}#kontak"
                   class="text-sm font-semibold text-body hover:text-primary transition">
                    Kontak
                </a>

                <a href="{{ route('home') }}#catering"
                   class="px-5 py-2.5 rounded-xl bg-primary text-white font-semibold text-sm shadow hover:scale-105 hover:bg-primary-container transition">
                    Pesan Sekarang
                </a>
            </div>

            <!-- Mobile Button -->
            <button
                class="lg:hidden p-2 rounded-lg text-primary hover:bg-primary/5"
                @click="open = !open">

                <svg x-show="!open"
                     class="w-6 h-6"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke="currentColor">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M4 6h16M4 12h16M4 18h16"/>
                </svg>

                <svg x-show="open"
                     class="w-6 h-6"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke="currentColor">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

        </div>
    </div>

    <!-- Mobile Menu -->
    <div x-show="open"
         x-cloak
         x-transition
         class="lg:hidden bg-white border-t border-primary/10 shadow-sm">

        <div class="px-6 py-4 flex flex-col">

            <a href="{{ route('home') }}#beranda" class="py-3 border-b border-primary/5">Beranda</a>
            <a href="{{ route('home') }}#tentang" class="py-3 border-b border-primary/5">Tentang</a>
            <a href="{{ route('home') }}#menu-dinein" class="py-3 border-b border-primary/5">Menu</a>
            <a href="{{ route('home') }}#catering" class="py-3 border-b border-primary/5">Catering</a>
            <a href="{{ route('home') }}#nasi-box" class="py-3 border-b border-primary/5">Nasi Box</a>
            <a href="{{ route('home') }}#galeri" class="py-3 border-b border-primary/5">Galeri</a>
            <a href="{{ route('home') }}#lacak-pesanan" class="py-3 border-b border-primary/5">Lacak Pesanan</a>
            <a href="{{ route('qr.scanner') }}" class="py-3 border-b border-primary/5 text-emerald-600 font-bold"><i class="fa-solid fa-qrcode mr-1"></i> QR Self Ordering</a>
            <a href="{{ route('home') }}#kontak" class="py-3 border-b border-primary/5">Kontak</a>

            <a href="{{ route('home') }}#catering"
               class="mt-4 bg-primary text-white text-center py-3 rounded-xl font-semibold">
                🍽️ Pesan Sekarang
            </a>

                @auth
            <a href="{{ route('dashboard') }}"
            class="flex items-center gap-2 px-5 py-2.5 rounded-xl border-2 border-primary text-primary font-semibold text-sm hover:bg-primary hover:text-white transition-all duration-200">

                <svg xmlns="http://www.w3.org/2000/svg"
                    class="w-4 h-4"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M3 12h18M12 3v18" />
                </svg>

                Dasbor Admin
            </a>
        @endauth

        </div>
    </div>

</nav>