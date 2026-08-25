<nav id="landing-navbar" class="sticky top-0 z-[100] bg-white/95 dark:bg-surface/95 backdrop-blur-md border-b border-neutral-200 dark:border-neutral-800">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="h-16 flex items-center justify-between gap-4" x-data="{ layanan: false, akun: false }">

            {{-- 1. LEFT: Logo --}}
            <div class="flex items-center shrink-0">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                    <img src="{{ asset('images/logo-saung.png') }}" alt="Saung Babakan Cinta" class="w-8 h-8 rounded object-contain">
                    <span class="text-sm font-bold tracking-tight text-neutral-900">Saung Babakan Cinta</span>
                </a>
            </div>

            {{-- 2. CENTER: Nav Links --}}
            <div class="hidden lg:flex items-center justify-center gap-6 xl:gap-8">
                <a href="{{ route('home') }}#beranda" class="text-sm font-medium text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-100 transition">Beranda</a>
                <a href="{{ route('home') }}#tentang" class="text-sm font-medium text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-100 transition">Tentang</a>
                <a href="{{ route('home') }}#menu-dinein" class="text-sm font-medium text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-100 transition">Menu</a>

                {{-- Dropdown Layanan --}}
                <div class="relative" @mouseenter="layanan = true" @mouseleave="layanan = false">
                    <button @click="layanan = !layanan" class="flex items-center gap-1 text-sm font-medium text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-100 transition">
                        Layanan
                        <svg class="w-3.5 h-3.5 transition-transform duration-200 opacity-70" :class="layanan ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="layanan" x-cloak x-transition class="absolute left-0 top-full mt-2 w-44 rounded-xl bg-white dark:bg-surface border border-neutral-200/80 dark:border-neutral-700 shadow-lg py-1.5 overflow-hidden">
                        <a href="{{ route('home') }}#menu-dinein" class="block px-4 py-2 text-xs font-medium text-neutral-600 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 hover:text-neutral-900 dark:hover:text-white transition">Dine in</a>
                        <a href="{{ route('home') }}#catering" class="block px-4 py-2 text-xs font-medium text-neutral-600 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 hover:text-neutral-900 dark:hover:text-white transition">Katering</a>
                        <a href="{{ route('home') }}#nasi-box" class="block px-4 py-2 text-xs font-medium text-neutral-600 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 hover:text-neutral-900 dark:hover:text-white transition">Nasi Box</a>
                        <div class="my-1 border-t border-neutral-100 dark:border-neutral-700"></div>
                        <a href="{{ route('lacak.index') }}" class="block px-4 py-2 text-xs font-medium text-neutral-600 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 hover:text-neutral-900 dark:hover:text-white transition">Lacak Pesanan</a>
                    </div>
                </div>

                <a href="{{ route('home') }}#galeri" class="text-sm font-medium text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-100 transition">Galeri</a>
                <a href="{{ route('home') }}#kontak" class="text-sm font-medium text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-100 transition">Kontak</a>
            </div>

            {{-- 3. RIGHT: Actions (Auth & Pesan Sekarang) --}}
            <div class="hidden lg:flex items-center justify-end gap-4 shrink-0">
                @if(Auth::guard('web')->check())
                    @php
                        $roleName = Auth::guard('web')->user()->peran->nama_peran ?? 'Admin';
                        $dashboardText = match($roleName) {
                            'Pemilik' => 'Dasbor Pemilik',
                            'Manajer' => 'Dasbor Manajer',
                            'Kasir' => 'Dasbor Kasir',
                            'Dapur', 'Tim Dapur' => 'Dasbor Dapur',
                            'Pengantaran', 'Tim Pengantaran' => 'Dasbor Pengantaran',
                            default => 'Dasbor ' . $roleName,
                        };
                    @endphp
                    <a href="{{ route('dashboard') }}" class="text-sm font-medium text-neutral-600 hover:text-neutral-900 transition">{{ $dashboardText }}</a>
                @elseif(Auth::guard('pelanggan')->check())
                    <x-ui.notification-dropdown type="external" />
                    <div class="relative" @mouseenter="akun = true" @mouseleave="akun = false">
                        <button @click="akun = !akun" class="flex items-center gap-1.5 text-sm font-medium text-neutral-600 hover:text-neutral-900 transition">
                            Profil Saya
                            <svg class="w-3.5 h-3.5 transition-transform duration-200 opacity-70" :class="akun ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="akun" x-cloak x-transition class="absolute right-0 top-full mt-2 w-44 rounded-xl bg-white border border-neutral-200/80 shadow-lg py-1.5 overflow-hidden">
                            <a href="{{ route('konsumen.pesanan.index') }}" class="block px-4 py-2 text-xs font-medium text-neutral-600 hover:bg-neutral-50 hover:text-neutral-900 transition">Pesanan Saya</a>
                            <a href="{{ route('konsumen.profile') }}" class="block px-4 py-2 text-xs font-medium text-neutral-600 hover:bg-neutral-50 hover:text-neutral-900 transition">Profil</a>
                            <form method="POST" action="{{ route('konsumen.logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-xs font-medium text-neutral-600 hover:bg-neutral-50 hover:text-neutral-900 transition">Keluar</button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="flex items-center text-sm font-medium text-neutral-600">
                        <a href="{{ route('konsumen.login') }}" class="hover:text-neutral-900 transition">Login</a>
                        <span class="mx-1.5 text-neutral-300">|</span>
                        <a href="{{ route('konsumen.register') }}" class="hover:text-neutral-900 transition">Daftar</a>
                    </div>
                @endif

                <x-theme-toggle />
            </div>

            {{-- Mobile Hamburger Button --}}
            <button id="mobile-menu-toggle" class="lg:hidden flex items-center justify-center w-10 h-10 rounded-lg text-neutral-700 hover:bg-neutral-100 transition-colors" aria-label="Toggle menu" aria-expanded="false">
                <svg id="icon-hamburger" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16"/></svg>
                <svg id="icon-close" class="w-5 h-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

        </div>
    </div>

    {{-- Minimalist Mobile Menu (NO ICONS) --}}
    <div id="mobile-nav-menu" class="absolute top-full left-0 w-full bg-white dark:bg-surface border-b border-neutral-200 dark:border-neutral-800 shadow-xl lg:hidden">
        <div class="px-6 py-4 flex flex-col space-y-1">

            {{-- Nav Links --}}
            <a href="{{ route('home') }}#beranda" class="mobile-nav-link py-2.5 text-sm font-medium text-neutral-800 hover:text-neutral-900 transition-colors">
                Beranda
            </a>
            <a href="{{ route('home') }}#tentang" class="mobile-nav-link py-2.5 text-sm font-medium text-neutral-800 hover:text-neutral-900 transition-colors">
                Tentang
            </a>
            <a href="{{ route('home') }}#menu-dinein" class="mobile-nav-link py-2.5 text-sm font-medium text-neutral-800 hover:text-neutral-900 transition-colors">
                Menu
            </a>

            {{-- Layanan Group --}}
            <div class="py-2 space-y-1">
                <p class="text-xs font-semibold text-neutral-400 uppercase tracking-wider mb-1">Layanan</p>
                <a href="{{ route('home') }}#menu-dinein" class="mobile-nav-link block py-2 pl-2 text-sm font-medium text-neutral-700 hover:text-neutral-900 transition-colors">
                    Dine in
                </a>
                <a href="{{ route('home') }}#catering" class="mobile-nav-link block py-2 pl-2 text-sm font-medium text-neutral-700 hover:text-neutral-900 transition-colors">
                    Katering
                </a>
                <a href="{{ route('home') }}#nasi-box" class="mobile-nav-link block py-2 pl-2 text-sm font-medium text-neutral-700 hover:text-neutral-900 transition-colors">
                    Nasi Box
                </a>
                <a href="{{ route('lacak.index') }}" class="mobile-nav-link block py-2 pl-2 text-sm font-medium text-neutral-700 hover:text-neutral-900 transition-colors">
                    Lacak Pesanan
                </a>
            </div>

            <a href="{{ route('home') }}#galeri" class="mobile-nav-link py-2.5 text-sm font-medium text-neutral-800 hover:text-neutral-900 transition-colors">
                Galeri
            </a>
            <a href="{{ route('home') }}#kontak" class="mobile-nav-link py-2.5 text-sm font-medium text-neutral-800 hover:text-neutral-900 transition-colors">
                Kontak
            </a>

            {{-- Auth Section --}}
            @if(Auth::guard('web')->check())
                @php
                    $roleNameMobile = Auth::guard('web')->user()->peran->nama_peran ?? 'Admin';
                    $dashboardTextMobile = match($roleNameMobile) {
                        'Pemilik' => 'Dasbor Pemilik',
                        'Manajer' => 'Dasbor Manajer',
                        'Kasir' => 'Dasbor Kasir',
                        'Dapur', 'Tim Dapur' => 'Dasbor Dapur',
                        'Pengantaran', 'Tim Pengantaran' => 'Dasbor Pengantaran',
                        default => 'Dasbor ' . $roleNameMobile,
                    };
                @endphp
                <a href="{{ route('dashboard') }}" class="mobile-nav-link py-2.5 text-sm font-semibold text-neutral-900">
                    {{ $dashboardTextMobile }}
                </a>
            @elseif(Auth::guard('pelanggan')->check())
                <a href="{{ route('konsumen.pesanan.index') }}" class="mobile-nav-link py-2.5 text-sm font-medium text-neutral-800">
                    Pesanan Saya
                </a>
                <a href="{{ route('konsumen.profile') }}" class="mobile-nav-link py-2.5 text-sm font-medium text-neutral-800">
                    Profil
                </a>
                <form method="POST" action="{{ route('konsumen.logout') }}" class="py-2">
                    @csrf
                    <button type="submit" class="w-full py-2.5 rounded-xl border border-neutral-200 text-sm text-neutral-800 font-medium hover:bg-neutral-50 transition-colors">
                        Keluar
                    </button>
                </form>
            @else
                <div class="pt-3 pb-1 flex gap-3">
                    <a href="{{ route('konsumen.login') }}"
                       class="flex-1 py-2.5 rounded-xl border border-neutral-200 text-sm text-neutral-900 text-center font-medium hover:bg-neutral-50 transition-colors">
                        Masuk
                    </a>
                    <a href="{{ route('konsumen.register') }}"
                       class="flex-1 py-2.5 rounded-xl bg-neutral-900 text-white text-sm text-center font-semibold hover:bg-neutral-800 transition-colors">
                        Daftar
                    </a>
                </div>
            @endif

            {{-- Dark Mode Toggle --}}
            <div class="pt-3 pb-1 flex items-center justify-between border-t border-neutral-200 mt-2">
                <span class="text-xs font-medium text-neutral-500">Mode Gelap</span>
                <x-theme-toggle />
            </div>

        </div>
    </div>

</nav>

<script>
(function() {
    const toggle = document.getElementById('mobile-menu-toggle');
    const menu = document.getElementById('mobile-nav-menu');
    const iconHamburger = document.getElementById('icon-hamburger');
    const iconClose = document.getElementById('icon-close');
    const navLinks = document.querySelectorAll('.mobile-nav-link');

    if (!toggle || !menu) return;

    function openMenu() {
        menu.classList.add('is-open');
        iconHamburger.classList.add('hidden');
        iconClose.classList.remove('hidden');
        toggle.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
    }

    function closeMenu() {
        menu.classList.remove('is-open');
        iconHamburger.classList.remove('hidden');
        iconClose.classList.add('hidden');
        toggle.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
    }

    toggle.addEventListener('click', function() {
        if (menu.classList.contains('is-open')) {
            closeMenu();
        } else {
            openMenu();
        }
    });

    // Close when any nav link is clicked
    navLinks.forEach(function(link) {
        link.addEventListener('click', function() {
            closeMenu();
        });
    });

    // Close when clicking outside
    document.addEventListener('click', function(e) {
        const navbar = document.getElementById('landing-navbar');
        if (navbar && !navbar.contains(e.target) && menu.classList.contains('is-open')) {
            closeMenu();
        }
    });

    // Close on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && menu.classList.contains('is-open')) {
            closeMenu();
        }
    });
})();
</script>
