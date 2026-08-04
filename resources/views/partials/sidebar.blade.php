{{--
|--------------------------------------------------------------------------
| Sidebar Navigation
|--------------------------------------------------------------------------
| Komponen sidebar utama aplikasi BBC Resto.
| Menggunakan Alpine.js untuk toggle open/close dan submenu.
|--}}

<aside :class="sidebarOpen ? 'w-64' : 'w-20'" class="no-print bg-white border-r border-neutral-200 text-neutral-600 flex flex-col shrink-0 transition-all duration-300 relative z-20">
    @php
        $userRole = auth()->user()->peran->nama_peran ?? '';
        $hasRole = function(...$roles) use ($userRole) {
            if (in_array($userRole, ['Admin', 'Super Admin', 'Pemilik', 'Admin Sistem'])) return true;
            return in_array($userRole, $roles);
        };
    @endphp

    {{-- Logo & Toggle --}}
    <div class="h-16 flex items-center justify-between px-4 border-b border-neutral-100 shrink-0 transition-all duration-300">
        <div class="flex items-center gap-2.5 overflow-hidden" x-show="sidebarOpen" x-transition:enter="transition delay-100 duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <img src="/images/logo-saung.png" alt="Logo" class="w-8 h-8 rounded object-contain shrink-0">
            <span class="font-semibold text-neutral-900 text-sm tracking-tight whitespace-nowrap">SBC RESTO</span>
        </div>
        <img x-show="!sidebarOpen" src="/images/logo-saung.png" alt="Logo" class="w-7 h-7 rounded object-contain shrink-0 mx-auto" x-cloak>
        <button @click="sidebarOpen = !sidebarOpen"
                class="w-8 h-8 flex items-center justify-center rounded-full text-neutral-400 hover:text-neutral-900 hover:bg-neutral-100 transition-colors focus:outline-none shrink-0"
                x-bind:class="sidebarOpen ? '' : 'hidden'"
                title="Toggle Sidebar">
            <x-heroicon-o-chevron-left class="w-5 h-5" />
        </button>
        <button x-show="!sidebarOpen" @click="sidebarOpen = true"
                class="w-8 h-8 flex items-center justify-center rounded-full text-neutral-400 hover:text-neutral-900 hover:bg-neutral-100 transition-colors focus:outline-none shrink-0 mx-auto mt-1"
                title="Buka Sidebar" x-cloak>
            <x-heroicon-o-chevron-right class="text-sm w-5 h-5" />
        </button>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 py-5 px-2.5 space-y-1 no-scrollbar" :class="sidebarOpen ? 'overflow-y-auto' : 'overflow-visible'">

        {{-- Dashboard --}}
        @include('partials.sidebar-link', [
            'route' => 'dashboard',
            'icon' => 'heroicon-s-home',
            'label' => 'Dashboard',
            'active' => request()->routeIs('dashboard'),
        ])

        {{-- Divider --}}
        <div class="py-1.5" x-show="sidebarOpen"><div class="h-px w-full bg-neutral-100"></div></div>

        {{-- Penjualan --}}
        @php
            $penjualanItems = [];
            if ($hasRole('Kasir', 'Pemilik')) {
                $penjualanItems[] = ['label' => 'Semua Pesanan', 'url' => route('admin.pesanan.index'), 'active' => request()->routeIs('admin.pesanan.*')];
            }
            if ($hasRole('Kasir', 'Pelayan', 'Pemilik')) {
                $penjualanItems[] = ['label' => 'Dine In', 'url' => route('pos.dinein.index'), 'active' => request()->routeIs('pos.dinein.*')];
            }
            if ($hasRole('Pemilik')) {
                $penjualanItems[] = ['label' => 'Catering', 'url' => route('admin.pesanan.catering.index'), 'active' => request()->routeIs('admin.pesanan.catering.*')];
                $penjualanItems[] = ['label' => 'Nasi Box', 'url' => route('admin.pesanan.nasibox.index'), 'active' => request()->routeIs('admin.pesanan.nasibox.*')];
                $penjualanItems[] = ['label' => 'Pengantaran', 'url' => route('admin.jadwal.index'), 'active' => request()->routeIs('admin.jadwal.*')];
            }
      
        @endphp
        @if(count($penjualanItems))
        @include('partials.sidebar-submenu', [
            'icon' => 'ionicon-cart-sharp',
            'label' => 'Penjualan',
            'isOpen' => request()->routeIs('pos.dinein.*') || request()->routeIs('admin.pesanan.*') || request()->routeIs('admin.jadwal.*'),
            'items' => $penjualanItems,
        ])
        @endif

        {{-- Menu & Paket --}}
        @if($hasRole('Admin', 'Manajer', 'Pemilik'))
        @include('partials.sidebar-submenu', [
            'icon' => 'gmdi-menu-book-r',
            'label' => 'Menu & Paket',
            'isOpen' => request()->routeIs('menu.*') || request()->routeIs('kategori-menu.*') || request()->routeIs('resep.*') || request()->routeIs('paket-catering.*'),
            'items' => [
                ['label' => 'Data Menu', 'url' => route('menu.index'), 'active' => request()->routeIs('menu.*')],
                ['label' => 'Kategori Menu', 'url' => route('kategori-menu.index'), 'active' => request()->routeIs('kategori-menu.*')],
                ['label' => 'Resep & Takaran', 'url' => route('resep.index'), 'active' => request()->routeIs('resep.*')],
                ['label' => 'Paket Catering', 'url' => route('paket-catering.index', ['jenis' => 'catering']), 'active' => request()->routeIs('paket-catering.*') && request('jenis', 'catering') === 'catering'],
                ['label' => 'Paket Nasi Box', 'url' => route('paket-catering.index', ['jenis' => 'nasi_box']), 'active' => request()->routeIs('paket-catering.*') && request('jenis') === 'nasi_box'],
            ],
        ])
        @endif

        {{-- Meja --}}
        @if($hasRole('Admin', 'Manajer', 'Pemilik'))
        @include('partials.sidebar-submenu', [
            'icon' => 'gmdi-table-bar',
            'label' => 'Meja',
            'isOpen' => request()->routeIs('meja.*'),
            'items' => [
                ['label' => 'Data Meja', 'url' => route('meja.index'), 'active' => request()->routeIs('meja.*')],
            ],
        ])
        @endif

        {{-- Persediaan --}}
        @if($hasRole('Admin', 'Manajer', 'Pemilik'))
        @include('partials.sidebar-submenu', [
            'icon' => 'fluentui-box-16',
            'label' => 'Persediaan',
            'isOpen' => request()->routeIs('bahan-baku.*') || request()->routeIs('stok-operasional.*') || request()->routeIs('stok-catering.*') || request()->routeIs('ketersediaan-menu.*') || request()->routeIs('mutasi-stok.*') || request()->routeIs('penyesuaian-stok.*') || request()->routeIs('notifikasi-stok.*') || request()->routeIs('stok-menipis.*') || request()->routeIs('pemasok.*'),
            'items' => [
                ['label' => 'Data Bahan Baku', 'url' => route('bahan-baku.index'), 'active' => request()->routeIs('bahan-baku.*')],
                ['label' => 'Stok Bahan Baku Harian', 'url' => route('stok-operasional.index'), 'active' => request()->routeIs('stok-operasional.*')],
                ['label' => 'Stok Bahan Baku Catering', 'url' => route('stok-catering.index'), 'active' => request()->routeIs('stok-catering.*')],
                ['label' => 'Ketersediaan Menu', 'url' => route('ketersediaan-menu.index'), 'active' => request()->routeIs('ketersediaan-menu.*')],
                ['label' => 'Riwayat Stok', 'url' => route('mutasi-stok.index'), 'active' => request()->routeIs('mutasi-stok.*')],
                ['label' => 'Stok Menipis', 'url' => route('stok-menipis.index'), 'active' => request()->routeIs('stok-menipis.*')],
                ['label' => 'Penyesuaian Stok', 'url' => route('penyesuaian-stok.index'), 'active' => request()->routeIs('penyesuaian-stok.*')],
                ['label' => 'Notifikasi Stok', 'url' => route('notifikasi-stok.index'), 'active' => request()->routeIs('notifikasi-stok.*')],
                ['label' => 'Pemasok', 'url' => route('pemasok.index'), 'active' => request()->routeIs('pemasok.*')],
            ],
        ])
        @endif

        {{-- Pengadaan --}}
        @if($hasRole('Admin', 'Manajer', 'Pemilik'))
        @include('partials.sidebar-submenu', [
            'icon' => 'polaris-order-draft-filled-icon',
            'label' => 'Pengadaan',
            'isOpen' => request()->routeIs('pengadaan.*') || request()->routeIs('laporan.pengadaan'),
            'items' => [
                ['label' => 'Semua Pengadaan', 'url' => route('pengadaan.index'), 'active' => (request()->routeIs('pengadaan.index') || request()->routeIs('pengadaan.show') || request()->routeIs('pengadaan.proses-terima')) && !request()->has('jenis')],
                ['label' => 'Pengadaan Harian', 'url' => route('pengadaan.harian'), 'active' => request()->routeIs('pengadaan.harian')],
                ['label' => 'Pengadaan Catering', 'url' => route('pengadaan.catering'), 'active' => request()->routeIs('pengadaan.catering')],
                ['label' => 'Buat Pengadaan Harian', 'url' => route('pengadaan.create-harian'), 'active' => request()->routeIs('pengadaan.create') && request('jenis', 'harian') === 'harian'],
                ['label' => 'Buat Pengadaan Catering', 'url' => route('pengadaan.create-catering'), 'active' => request()->routeIs('pengadaan.create') && request('jenis') === 'catering'],
                ['label' => 'Penerimaan Bahan Harian', 'url' => route('pengadaan.terima-barang-harian'), 'active' => request()->routeIs('pengadaan.terima-barang') && request('jenis', 'harian') === 'harian'],
                ['label' => 'Penerimaan Bahan Catering', 'url' => route('pengadaan.terima-barang-catering'), 'active' => request()->routeIs('pengadaan.terima-barang') && request('jenis') === 'catering'],
                ['label' => 'Riwayat Pengadaan', 'url' => route('laporan.pengadaan'), 'active' => request()->routeIs('laporan.pengadaan')],
            ],
        ])
        @endif

        {{-- Laporan --}}
        @if($hasRole('Pemilik', 'Manajer'))
        @include('partials.sidebar-submenu', [
            'icon' => 'iconoir-reports-solid',
            'label' => 'Laporan',
            'isOpen' => request()->routeIs('laporan.*'),
            'items' => [
                ['label' => 'Penjualan', 'url' => route('laporan.penjualan'), 'active' => request()->routeIs('laporan.penjualan')],
                ['label' => 'Persediaan Bahan Baku', 'url' => route('laporan.stok'), 'active' => request()->routeIs('laporan.stok')],
                ['label' => 'Riwayat Pengadaan', 'url' => route('laporan.pengadaan'), 'active' => request()->routeIs('laporan.pengadaan')],
                ['label' => 'Menu Terlaris', 'url' => route('laporan.menu-terlaris'), 'active' => request()->routeIs('laporan.menu-terlaris')],
            ],
        ])
        @endif

        {{-- Manajemen Pengguna --}}
        @if($hasRole('Pemilik', 'Manajer'))
        @include('partials.sidebar-submenu', [
            'icon' => 'fluentui-people-28',
            'label' => 'Manajemen Pengguna',
            'isOpen' => request()->routeIs('users.*') || request()->routeIs('roles.*'),
            'items' => [
                ['label' => 'Data Pengguna', 'url' => route('users.index'), 'active' => request()->routeIs('users.*') && request('type') !== 'pelanggan'],
                ['label' => 'Data Pelanggan', 'url' => route('users.index', ['type' => 'pelanggan']), 'active' => request()->routeIs('users.*') && request('type') === 'pelanggan'],
                ['label' => 'Hak Akses', 'url' => route('roles.index'), 'active' => request()->routeIs('roles.*')],
            ],
        ])
        @endif
    </nav>

    {{-- Footer Actions --}}
    <div class="p-2.5 border-t border-neutral-100 shrink-0">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-neutral-600 hover:text-neutral-900 hover:bg-neutral-100 rounded-xl transition group" title="Logout">
                <x-ri-logout-box-r-fill class="text-lg w-6 text-center shrink-0 text-neutral-400 group-hover:text-neutral-900" />
                <span x-show="sidebarOpen" class="whitespace-nowrap transition-opacity duration-200">Log Out</span>
            </button>
        </form>
    </div>
</aside>
