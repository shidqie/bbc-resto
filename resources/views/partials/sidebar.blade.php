{{--
|--------------------------------------------------------------------------
| Sidebar Navigation
|--------------------------------------------------------------------------
| Komponen sidebar utama aplikasi BBC Resto.
| Menggunakan Alpine.js untuk toggle open/close dan submenu.
--}}

<aside :class="sidebarOpen ? 'w-64' : 'w-20'" class="bg-[#111827] text-gray-400 flex flex-col shrink-0 transition-all duration-300 relative z-20">
    
    {{-- Logo & Toggle --}}
    <div class="h-16 flex items-center justify-between px-4 border-b border-gray-800 shrink-0 transition-all duration-300">
        <div class="flex items-center gap-3 overflow-hidden" x-show="sidebarOpen" x-transition:enter="transition delay-100 duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <x-heroicon-s-building-storefront class="w-7 h-7 text-primary shrink-0" />
            <span class="font-bold text-white text-sm tracking-widest whitespace-nowrap">SBC.</span>
        </div>
        <button @click="sidebarOpen = !sidebarOpen" 
                class="w-10 h-10 flex items-center justify-center rounded-xl text-gray-400 hover:text-white hover:bg-gray-800 transition-colors focus:outline-none"
                x-bind:class="sidebarOpen ? '' : 'mx-auto w-full'">
            <x-heroicon-o-bars-3-bottom-left class="w-6 h-6" x-show="!sidebarOpen" style="display: none;" />
            <x-heroicon-o-chevron-double-left class="w-5 h-5" x-show="sidebarOpen" />
        </button>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 py-6 px-3 space-y-1.5 no-scrollbar" :class="sidebarOpen ? 'overflow-y-auto' : 'overflow-visible'">
        
        {{-- Dashboard --}}
        @include('partials.sidebar-link', [
            'route' => 'dashboard',
            'icon' => 'o-squares-2x2',
            'label' => 'Dashboard',
            'active' => request()->routeIs('dashboard'),
        ])

        {{-- POS Kasir --}}
        @include('partials.sidebar-link', [
            'route' => 'pesanan.create',
            'icon' => 'o-computer-desktop',
            'label' => 'POS Kasir',
            'active' => request()->routeIs('pesanan.create'),
        ])

        {{-- Divider --}}
        <div class="py-2" x-show="sidebarOpen"><div class="h-px w-full bg-gray-800"></div></div>

        {{-- Pesanan (Submenu) --}}
        @include('partials.sidebar-submenu', [
            'icon' => 'o-clipboard-document-list',
            'label' => 'Pesanan',
            'isOpen' => request()->routeIs('pesanan.*'),
            'items' => [
                ['label' => 'Semua Pesanan',    'url' => route('pesanan.index'), 'active' => request()->routeIs('pesanan.index') && !request()->query('jenis') && !request()->query('status')],
                ['label' => 'Dine-in',          'url' => route('pesanan.index', ['jenis' => 'dine_in']),    'active' => request()->query('jenis') == 'dine_in'],
                ['label' => 'Take Away',        'url' => route('pesanan.index', ['jenis' => 'take_away']), 'active' => request()->query('jenis') == 'take_away'],
                ['label' => 'Catering / Nasi Box', 'url' => route('pesanan.index', ['jenis' => 'catering']), 'active' => request()->query('jenis') == 'catering'],
                ['label' => 'Pembatalan',       'url' => route('pesanan.index', ['status' => 'dibatalkan']), 'active' => request()->query('status') == 'dibatalkan'],
            ],
        ])

        {{-- Menu (Submenu) --}}
        @include('partials.sidebar-submenu', [
            'icon' => 'o-book-open',
            'label' => 'Menu',
            'isOpen' => request()->routeIs('menu.*') || request()->routeIs('kategori-menu.*'),
            'items' => [
                ['label' => 'Daftar Menu',    'url' => route('menu.index'),          'active' => request()->routeIs('menu.*')],
                ['label' => 'Kategori Menu',  'url' => route('kategori-menu.index'), 'active' => request()->routeIs('kategori-menu.*')],
            ],
        ])

        {{-- Bahan Baku (Submenu) --}}
        @include('partials.sidebar-submenu', [
            'icon' => 'o-cube',
            'label' => 'Bahan Baku',
            'isOpen' => request()->routeIs('bahan-baku.*') || request()->routeIs('mutasi-stok.*') || request()->routeIs('stok-menipis.*'),
            'items' => [
                ['label' => 'Daftar Bahan Baku', 'url' => route('bahan-baku.index'),   'active' => request()->routeIs('bahan-baku.*')],
                ['label' => 'Stok Masuk / Keluar','url' => route('mutasi-stok.index'),  'active' => request()->routeIs('mutasi-stok.*')],
                ['label' => 'Stok Menipis',       'url' => route('stok-menipis.index'), 'active' => request()->routeIs('stok-menipis.*')],
            ],
        ])

        {{-- Pengadaan (Submenu) --}}
        @include('partials.sidebar-submenu', [
            'icon' => 'o-truck',
            'label' => 'Pengadaan',
            'isOpen' => request()->routeIs('pengadaan.*'),
            'items' => [
                ['label' => 'Buat Pengadaan',    'url' => route('pengadaan.create'), 'active' => request()->routeIs('pengadaan.create')],
                ['label' => 'Riwayat Pengadaan', 'url' => route('pengadaan.index'),  'active' => request()->routeIs('pengadaan.index') || request()->routeIs('pengadaan.show')],
            ],
        ])

        {{-- Pengguna (Submenu) --}}
        @include('partials.sidebar-submenu', [
            'icon' => 'o-users',
            'label' => 'Pengguna',
            'isOpen' => request()->routeIs('users.*'),
            'items' => [
                ['label' => 'Data Pengguna', 'url' => route('users.index'), 'active' => request()->routeIs('users.*')],
            ],
        ])

    </nav>

    {{-- Footer Profile --}}
    <div class="p-3 border-t border-gray-800 shrink-0">
        <div class="flex items-center gap-3 px-3 py-2 bg-gray-800/50 rounded-xl mb-2 overflow-hidden transition-all duration-300">
            <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-sm shrink-0">
                {{ substr(auth()->user()->name ?? 'K', 0, 1) }}
            </div>
            <div class="flex-1 min-w-0" x-show="sidebarOpen" x-transition:enter="transition delay-100 duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                <div class="text-sm font-bold text-white truncate">{{ auth()->user()->name ?? 'Kasir 1' }}</div>
                <div class="text-[10px] text-gray-400 truncate">Administrator</div>
            </div>
        </div>
        
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 text-sm font-semibold text-gray-400 hover:text-white hover:bg-gray-800/50 rounded-xl transition group" title="Logout">
                <x-heroicon-o-arrow-right-on-rectangle class="w-6 h-6 shrink-0 group-hover:text-white" />
                <span x-show="sidebarOpen" class="whitespace-nowrap transition-opacity duration-200">Log Out</span>
            </button>
        </form>
    </div>
</aside>
