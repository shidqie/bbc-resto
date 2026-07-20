<nav class="bg-white/90 backdrop-blur-md sticky top-0 z-50 border-b border-primary/10 shadow-sm" x-data="{ open: false, layanan: false }">
    <div class="max-w-[1280px] mx-auto px-6 h-20 flex items-center justify-between">
        <a href="{{ route('home') }}" class="font-serif text-2xl font-bold tracking-tight text-primary shrink-0">
            Saung Babakan Cinta
        </a>
        <!-- Desktop -->
        <div class="hidden lg:flex gap-6 items-center">
            <a href="{{ route('home') }}#beranda" class="text-sm font-semibold hover:text-secondary transition-colors">Beranda</a>
            <a href="{{ route('home') }}#tentang" class="text-sm font-semibold hover:text-secondary transition-colors">Tentang</a>
            <a href="{{ route('home') }}#menu-dinein" class="text-sm font-semibold hover:text-secondary transition-colors">Menu</a>
            <!-- Dropdown Layanan -->
            <div class="relative" @mouseenter="layanan = true" @mouseleave="layanan = false">
                <button @click="layanan = !layanan" class="text-sm font-semibold hover:text-secondary transition-colors flex items-center gap-1">
                    Layanan
                    <svg class="w-3 h-3 transition-transform" :class="layanan ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="layanan" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="absolute top-full left-1/2 -translate-x-1/2 mt-3 w-44 bg-white rounded-lg shadow-xl border border-primary/10 py-1.5 z-50">
                    <a href="{{ route('home') }}#catering" @click="layanan = false" class="block px-4 py-2 text-sm text-body hover:bg-primary/5 hover:text-primary transition">Catering</a>
                    <a href="{{ route('home') }}#nasi-box" @click="layanan = false" class="block px-4 py-2 text-sm text-body hover:bg-primary/5 hover:text-primary transition">Nasi Box</a>
                </div>
            </div>
            <a href="{{ route('home') }}#galeri" class="text-sm font-semibold hover:text-secondary transition-colors">Galeri</a>
            <a href="{{ route('home') }}#kontak" class="text-sm font-semibold hover:text-secondary transition-colors">Kontak</a>
            @auth
                @if((Auth::user()->role?->name ?? 'Konsumen') === 'Konsumen')
                    <div class="relative" x-data="{ userMenu: false }" @mouseenter="userMenu = true" @mouseleave="userMenu = false">
                        <button @click="userMenu = !userMenu" class="flex items-center gap-2 text-sm font-semibold text-gray-700 hover:text-primary transition-colors">
                            <div class="w-8 h-8 bg-primary text-white rounded-full flex items-center justify-center font-bold text-xs">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                            <span class="max-w-[100px] truncate">{{ Auth::user()->name }}</span>
                            <svg class="w-3 h-3 transition-transform" :class="userMenu ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="userMenu" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="absolute top-full right-0 mt-3 w-48 bg-white rounded-xl shadow-xl border border-primary/10 py-2 z-50" style="display: none;">
                            <a href="{{ route('member.profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-primary/5 hover:text-primary transition">Akun Saya</a>
                            <a href="{{ route('member.pesanan.aktif') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-primary/5 hover:text-primary transition">Pesanan Saya</a>
                            <div class="my-1 border-t border-gray-100"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-danger hover:bg-red-50 transition">Log Out</button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('dashboard') }}" class="border-2 border-primary text-primary px-4 py-1.5 rounded-md font-bold text-sm hover:bg-primary hover:text-white transition-all shadow-sm">
                        Dasbor Admin
                    </a>
                @endif
            @else
                <div class="flex items-center text-sm font-semibold text-gray-600">
                    <a href="{{ route('register') }}" class="px-3 py-1.5 rounded-lg hover:text-primary hover:bg-primary/5 transition-all duration-200">
                        Daftar
                    </a>
                    <span class="text-gray-300 mx-1">|</span>
                    <a href="{{ route('login') }}" class="px-3 py-1.5 rounded-lg hover:text-primary hover:bg-primary/5 transition-all duration-200">
                        Log In
                    </a>
                </div>
            @endauth
        </div>
        <!-- Hamburger -->
        <button class="lg:hidden p-2 text-primary" onclick="document.getElementById('mobileNav').classList.toggle('active')" aria-label="Menu">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
    </div>
    <!-- Mobile Nav -->
    <div id="mobileNav" class="mobile-nav flex-col lg:hidden bg-white border-t border-primary/10 px-6 pb-4 gap-1">
        <a href="{{ route('home') }}#beranda" class="py-3 text-sm font-semibold hover:text-secondary transition-colors border-b border-primary/5" onclick="document.getElementById('mobileNav').classList.remove('active')">Beranda</a>
        <a href="{{ route('home') }}#tentang" class="py-3 text-sm font-semibold hover:text-secondary transition-colors border-b border-primary/5" onclick="document.getElementById('mobileNav').classList.remove('active')">Tentang</a>
        <a href="{{ route('home') }}#menu-dinein" class="py-3 text-sm font-semibold hover:text-secondary transition-colors border-b border-primary/5" onclick="document.getElementById('mobileNav').classList.remove('active')">Menu</a>
        <a href="{{ route('home') }}#catering" class="py-3 text-sm font-semibold hover:text-secondary transition-colors border-b border-primary/5" onclick="document.getElementById('mobileNav').classList.remove('active')">Catering</a>
        <a href="{{ route('home') }}#nasi-box" class="py-3 text-sm font-semibold hover:text-secondary transition-colors border-b border-primary/5" onclick="document.getElementById('mobileNav').classList.remove('active')">Nasi Box</a>
        <a href="{{ route('home') }}#galeri" class="py-3 text-sm font-semibold hover:text-secondary transition-colors border-b border-primary/5" onclick="document.getElementById('mobileNav').classList.remove('active')">Galeri</a>
        <a href="{{ route('home') }}#kontak" class="py-3 text-sm font-semibold hover:text-secondary transition-colors border-b border-primary/5" onclick="document.getElementById('mobileNav').classList.remove('active')">Kontak</a>
        @auth
            @if((Auth::user()->role?->name ?? 'Konsumen') === 'Konsumen')
                <div class="mt-4 border-t border-primary/10 pt-4">
                    <p class="text-xs text-gray-500 mb-2">Akun Saya ({{ Auth::user()->name }})</p>
                    <a href="{{ route('member.profile') }}" class="block py-2 text-sm font-semibold text-gray-700 hover:text-primary" onclick="document.getElementById('mobileNav').classList.remove('active')">Pengaturan Akun</a>
                    <a href="{{ route('member.pesanan.aktif') }}" class="block py-2 text-sm font-semibold text-gray-700 hover:text-primary" onclick="document.getElementById('mobileNav').classList.remove('active')">Pesanan Saya</a>
                    <form method="POST" action="{{ route('logout') }}" class="mt-2">
                        @csrf
                        <button type="submit" class="w-full text-left py-2 text-sm font-semibold text-danger hover:text-red-700">Log Out</button>
                    </form>
                </div>
            @else
                <a href="{{ route('dashboard') }}" class="mt-4 border-2 border-primary text-primary px-5 py-3 rounded-md font-bold text-sm hover:bg-primary hover:text-white transition-all shadow-sm w-full text-center" onclick="document.getElementById('mobileNav').classList.remove('active')">
                    Dasbor Admin
                </a>
            @endif
        @else
            <div class="mt-4 border-t border-primary/10 pt-4 flex flex-col gap-2">
                <a href="{{ route('register') }}" class="py-2.5 rounded-lg text-sm font-semibold text-gray-700 hover:text-primary hover:bg-primary/5 text-center transition-all duration-200" onclick="document.getElementById('mobileNav').classList.remove('active')">
                    Daftar
                </a>
                <a href="{{ route('login') }}" class="py-2.5 rounded-lg text-sm font-semibold text-gray-700 hover:text-primary hover:bg-primary/5 text-center transition-all duration-200" onclick="document.getElementById('mobileNav').classList.remove('active')">
                    Log In
                </a>
            </div>
        @endauth
    </div>
</nav>
