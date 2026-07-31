{{--
|--------------------------------------------------------------------------
| Sidebar Navigation
|--------------------------------------------------------------------------
| Komponen sidebar utama aplikasi BBC Resto.
| Menggunakan Alpine.js untuk toggle open/close dan submenu.
|--}}

<aside :class="sidebarOpen ? 'w-64' : 'w-20'" class="no-print bg-[#111827] text-gray-400 flex flex-col shrink-0 transition-all duration-300 relative z-20">
    @php
        $userRole = auth()->user()->peran->nama_peran ?? '';
        $hasRole = function(...$roles) use ($userRole) {
            if (in_array($userRole, ['Admin', 'Super Admin', 'Pemilik', 'Admin Sistem'])) return true;
            return in_array($userRole, $roles);
        };
    @endphp

    {{-- Logo & Toggle --}}
    <div class="h-16 flex items-center justify-between px-4 border-b border-gray-800 shrink-0 transition-all duration-300">
        <div class="flex items-center gap-2.5 overflow-hidden" x-show="sidebarOpen" x-transition:enter="transition delay-100 duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <img src="/images/logo-saung.png" alt="Logo" class="w-8 h-8 rounded-full object-cover shrink-0 border border-gray-700">
            <span class="font-extrabold text-white text-[15px] tracking-widest whitespace-nowrap">SBC RESTO</span>
        </div>
        <img x-show="!sidebarOpen" src="/images/logo-saung.png" alt="Logo" class="w-7 h-7 rounded-full object-cover shrink-0 mx-auto border border-gray-700" x-cloak>
        <button @click="sidebarOpen = !sidebarOpen" 
                class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-500 hover:text-white hover:bg-gray-800 transition-colors focus:outline-none shrink-0"
                x-bind:class="sidebarOpen ? '' : 'hidden'"
                title="Toggle Sidebar">
            <x-heroicon-o-chevron-left class="w-5 h-5" />
        </button>
        <button x-show="!sidebarOpen" @click="sidebarOpen = true"
                class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-500 hover:text-white hover:bg-gray-800 transition-colors focus:outline-none shrink-0 mx-auto mt-1"
                title="Buka Sidebar" x-cloak>
            <x-heroicon-o-chevron-right class="text-[15px] w-5 h-5" />
        </button>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 py-6 px-3 space-y-2 no-scrollbar" :class="sidebarOpen ? 'overflow-y-auto' : 'overflow-visible'">
        
        {{-- Dashboard --}}
        @include('partials.sidebar-link', [
            'route' => 'dashboard',
            'icon' => 'heroicon-s-home',
            'label' => 'Dashboard',
            'active' => request()->routeIs('dashboard'),
        ])

        {{-- Divider --}}
        <div class="py-2" x-show="sidebarOpen"><div class="h-px w-full bg-gray-800"></div></div>

        {{-- Penjualan --}}
        @if($hasRole('Kasir', 'Pemilik', 'Tim Pengantaran'))
        @include('partials.sidebar-submenu', [
            'icon' => 'ionicon-cart-sharp',
            'label' => 'Penjualan',
            'isOpen' => request()->routeIs('pos.dinein.*') || request()->routeIs('admin.pesanan.*') || request()->routeIs('admin.jadwal.*'),
            'items' => [
                ['label' => 'Semua Pesanan', 'url' => route('admin.pesanan.index'), 'active' => request()->routeIs('admin.pesanan.*')],
                ['label' => 'Dine In', 'url' => route('pos.dinein.index'), 'active' => request()->routeIs('pos.dinein.*')],
                ['label' => 'Catering', 'url' => route('admin.pesanan.catering.index'), 'active' => request()->routeIs('admin.pesanan.catering.*')],
                ['label' => 'Nasi Box', 'url' => route('admin.pesanan.nasibox.index'), 'active' => request()->routeIs('admin.pesanan.nasibox.*')],
                ['label' => 'Pengantaran', 'url' => route('admin.jadwal.index'), 'active' => request()->routeIs('admin.jadwal.*')],
            ],
        ])
        @endif

        {{-- Menu --}}
        @if($hasRole('Manajer', 'Pemilik'))
        @include('partials.sidebar-submenu', [
            'icon' => 'gmdi-menu-book-r',
            'label' => 'Menu',
            'isOpen' => request()->routeIs('menu.*') || request()->routeIs('kategori-menu.*') || request()->routeIs('resep.*'),
            'items' => [
                ['label' => 'Data Menu', 'url' => route('menu.index'), 'active' => request()->routeIs('menu.*')],
                ['label' => 'Kategori Menu', 'url' => route('kategori-menu.index'), 'active' => request()->routeIs('kategori-menu.*')],
                ['label' => 'Kelola Resep Menu', 'url' => route('resep.index'), 'active' => request()->routeIs('resep.*')],
            ],
        ])
        @endif

        {{-- Meja --}}
        @if($hasRole('Manajer', 'Pemilik'))
        @include('partials.sidebar-submenu', [
            'icon' => 'gmdi-table-bar',
            'label' => 'Meja',
            'isOpen' => request()->routeIs('meja.*'),
            'items' => [
                ['label' => 'Data Meja', 'url' => route('meja.index'), 'active' => request()->routeIs('meja.*')],
            ],
        ])
        @endif

        {{-- Persediaan Bahan Baku --}}
        @if($hasRole('Manajer', 'Pemilik'))
        @include('partials.sidebar-submenu', [
            'icon' => 'fluentui-box-16',
            'label' => 'Persediaan Bahan Baku',
            'isOpen' => request()->routeIs('bahan-baku.*') || request()->routeIs('stok-operasional.*') || request()->routeIs('mutasi-stok.*'),
            'items' => [
                ['label' => 'Data Bahan Baku', 'url' => route('bahan-baku.index'), 'active' => request()->routeIs('bahan-baku.*')],
                ['label' => 'Stok Bahan Baku', 'url' => route('stok-operasional.index'), 'active' => request()->routeIs('stok-operasional.*')],
                ['label' => 'Riwayat Stok', 'url' => route('mutasi-stok.index'), 'active' => request()->routeIs('mutasi-stok.*')],
                ['label' => 'Penyesuaian Stok', 'url' => '#', 'active' => false],
            ],
        ])
        @endif

        {{-- Pengadaan Bahan Baku --}}
        @if($hasRole('Manajer', 'Pemilik'))
        @include('partials.sidebar-submenu', [
            'icon' => 'polaris-order-draft-filled-icon',
            'label' => 'Pengadaan',
            'isOpen' => request()->routeIs('pengadaan.*'),
            'items' => [
                ['label' => 'Pengadaan Bahan', 'url' => route('pengadaan.index'), 'active' => request()->routeIs('pengadaan.index') || request()->routeIs('pengadaan.create') || request()->routeIs('pengadaan.show')],
                ['label' => 'Penerimaan Bahan', 'url' => route('pengadaan.terima-barang'), 'active' => request()->routeIs('pengadaan.terima-barang') || request()->routeIs('pengadaan.form-terima') || request()->routeIs('pengadaan.proses-terima')],
            ],
        ])
        @endif

        {{-- Laporan --}}
        @if($hasRole('Pemilik', 'Manajer'))
        @include('partials.sidebar-submenu', [
            'icon' => 'iconoir-reports-solid',
            'label' => 'Laporan',
            'isOpen' => request()->routeIs('laporan.*') || request()->routeIs('pengadaan.index'),
            'items' => [
                ['label' => 'Penjualan', 'url' => route('laporan.penjualan'), 'active' => request()->routeIs('laporan.penjualan')],
                ['label' => 'Stok Bahan', 'url' => route('laporan.stok'), 'active' => request()->routeIs('laporan.stok')],
            ],
        ])
        @endif
    </nav>

    {{-- Footer Actions --}}
    <div class="p-3 border-t border-gray-800 shrink-0">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full flex items-center gap-3 px-3 py-3 text-[15px] font-semibold text-gray-400 hover:text-white hover:bg-gray-800/50 rounded-xl transition group" title="Logout">
                <x-ri-logout-box-r-fill class="text-[18px] w-6 text-center shrink-0 text-gray-500 group-hover:text-red-500" />
                <span x-show="sidebarOpen" class="whitespace-nowrap transition-opacity duration-200">Log Out</span>
            </button>
        </form>
    </div>
</aside>
