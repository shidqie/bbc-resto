<nav class="bg-white/90 backdrop-blur-md sticky top-0 z-50 border-b border-primary/10 shadow-sm" x-data="{ open: false, layanan: false }">
    <div class="max-w-[1280px] mx-auto px-6 h-20 flex items-center justify-between">
        <a href="{{ route('home') }}" class="font-serif text-2xl font-bold tracking-tight text-primary shrink-0">
            Saung Babakan Cinta
        </a>
        <!-- Desktop -->
        <div class="hidden lg:flex gap-6 items-center">
            <a href="{{ route('home') }}#beranda" class="text-sm font-semibold hover:text-secondary transition-colors">Beranda</a>
            <a href="{{ route('home') }}#tentang" class="text-sm font-semibold hover:text-secondary transition-colors">Tentang</a>
            <a href="{{ route('home') }}#menu-dinein" class="text-sm font-semibold hover:text-secondary transition-colors">Menu Dine-in</a>
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
            <a href="{{ route('pesan.catering') }}" class="bg-primary text-white px-5 py-2 rounded-md font-bold text-sm hover:bg-primary-container transition-all shadow-sm">
                Pesan Sekarang
            </a>
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
        <a href="{{ route('home') }}#menu-dinein" class="py-3 text-sm font-semibold hover:text-secondary transition-colors border-b border-primary/5" onclick="document.getElementById('mobileNav').classList.remove('active')">Menu Dine-in</a>
        <a href="{{ route('home') }}#catering" class="py-3 text-sm font-semibold hover:text-secondary transition-colors border-b border-primary/5" onclick="document.getElementById('mobileNav').classList.remove('active')">Catering</a>
        <a href="{{ route('home') }}#nasi-box" class="py-3 text-sm font-semibold hover:text-secondary transition-colors border-b border-primary/5" onclick="document.getElementById('mobileNav').classList.remove('active')">Nasi Box</a>
        <a href="{{ route('home') }}#galeri" class="py-3 text-sm font-semibold hover:text-secondary transition-colors border-b border-primary/5" onclick="document.getElementById('mobileNav').classList.remove('active')">Galeri</a>
        <a href="{{ route('home') }}#kontak" class="py-3 text-sm font-semibold hover:text-secondary transition-colors border-b border-primary/5" onclick="document.getElementById('mobileNav').classList.remove('active')">Kontak</a>
        <a href="{{ route('pesan.catering') }}" class="mt-4 bg-primary text-white px-5 py-3 rounded-md font-bold text-sm hover:bg-primary-container transition-all shadow-sm w-full text-center">
            Pesan Sekarang
        </a>
    </div>
</nav>
