{{--
|--------------------------------------------------------------------------
| Sidebar Navigation
|--------------------------------------------------------------------------
| Komponen sidebar utama aplikasi BBC Resto.
| Menggunakan Alpine.js untuk toggle open/close dan submenu.
--}}

<aside :class="sidebarOpen ? 'w-64' : 'w-20'" class="no-print bg-[#111827] text-gray-400 flex flex-col shrink-0 transition-all duration-300 relative z-20">
    @php
        $userRole = auth()->user()->role->name ?? '';
        $hasRole = function(...$roles) use ($userRole) {
            if ($userRole === 'Admin' || $userRole === 'Super Admin') return true;
            return in_array($userRole, $roles);
        };
    @endphp

    {{-- Logo & Toggle --}}
    <div class="h-16 flex items-center justify-between px-4 border-b border-gray-800 shrink-0 transition-all duration-300">
        <div class="flex items-center gap-3 overflow-hidden" x-show="sidebarOpen" x-transition:enter="transition delay-100 duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <div class="w-8 h-8 rounded-xl bg-[#0F2E23] flex items-center justify-center text-emerald-400 shrink-0">
                <i class="fa-solid fa-utensils text-sm"></i>
            </div>
            <span class="font-extrabold text-white text-sm tracking-widest whitespace-nowrap">SBC RESTO</span>
        </div>
        <button @click="sidebarOpen = !sidebarOpen" 
                class="w-10 h-10 flex items-center justify-center rounded-xl text-gray-400 hover:text-white hover:bg-gray-800 transition-colors focus:outline-none"
                x-bind:class="sidebarOpen ? '' : 'mx-auto'"
                title="Toggle Sidebar">
            <i class="fa-solid fa-indent text-lg transition-transform duration-200" x-show="!sidebarOpen"></i>
            <i class="fa-solid fa-dedent text-lg transition-transform duration-200" x-show="sidebarOpen"></i>
        </button>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 py-6 px-3 space-y-1.5 no-scrollbar" :class="sidebarOpen ? 'overflow-y-auto' : 'overflow-visible'">
        
        {{-- Dashboard --}}
        @include('partials.sidebar-link', [
            'route' => 'dashboard',
            'icon' => 'fa-gauge-high',
            'label' => 'Dashboard',
            'active' => request()->routeIs('dashboard'),
        ])

        {{-- Pesanan Baru (Dine-In Only) --}}
        @if($hasRole('Kasir'))
        @include('partials.sidebar-link', [
            'route' => 'pos.dinein.index',
            'icon' => 'fa-cash-register',
            'label' => 'Pesanan Baru',
            'active' => request()->routeIs('pos.dinein.*'),
        ])
        @endif

        {{-- Divider --}}
        <div class="py-2" x-show="sidebarOpen"><div class="h-px w-full bg-gray-800"></div></div>

        {{-- Pesanan (Submenu) --}}
        @php
            $pesananItems = [];
            if ($hasRole('Manajer', 'Pemilik')) {
                $pesananItems[] = ['label' => 'Daftar Pesanan Dine-In',  'url' => route('pesanan.index'), 'active' => request()->routeIs('pesanan.index')];
                $pesananItems[] = ['label' => 'Daftar Pesanan Catering', 'url' => route('admin.pesanan.catering.index'),  'active' => request()->routeIs('admin.pesanan.catering.*')];
                $pesananItems[] = ['label' => 'Daftar Pesanan Nasi Box', 'url' => route('admin.pesanan.nasibox.index'),   'active' => request()->routeIs('admin.pesanan.nasibox.*')];
            }
            if ($hasRole('Tim Pengantaran')) {
                $pesananItems[] = ['label' => 'Jadwal Pengantaran',      'url' => route('admin.jadwal.index'),  'active' => request()->routeIs('admin.jadwal.*')];
            }
        @endphp
        @if(count($pesananItems) > 0)
        @include('partials.sidebar-submenu', [
            'icon' => 'fa-clipboard-list',
            'label' => 'Pesanan',
            'isOpen' => request()->routeIs('pesanan.*') || request()->routeIs('admin.pesanan.*') || request()->routeIs('admin.jadwal.*'),
            'items' => $pesananItems,
        ])
        @endif

        {{-- Menu (Submenu) --}}
        @if($hasRole('Manajer', 'Pemilik'))
        @include('partials.sidebar-submenu', [
            'icon' => 'fa-utensils',
            'label' => 'Menu',
            'isOpen' => request()->routeIs('menu.*') || request()->routeIs('kategori-menu.*'),
            'items' => [
                ['label' => 'Daftar Menu Resto',   'url' => route('menu.index'),          'active' => request()->routeIs('menu.*')],
                ['label' => 'Daftar Menu Catering', 'url' => route('paket-catering.index', ['jenis' => 'catering']), 'active' => request()->fullUrlIs(route('paket-catering.index', ['jenis' => 'catering']))],
                ['label' => 'Daftar Menu Nasi Box', 'url' => route('paket-catering.index', ['jenis' => 'nasi_box']), 'active' => request()->fullUrlIs(route('paket-catering.index', ['jenis' => 'nasi_box']))],
                ['label' => 'Kategori Menu', 'url' => route('kategori-menu.index'), 'active' => request()->routeIs('kategori-menu.*')],
            ],
        ])
        @endif

        {{-- Bahan Baku (Submenu) --}}
        @php
            $bahanBakuItems = [];
            if ($hasRole('Manajer', 'Pemilik')) {
                $bahanBakuItems[] = ['label' => 'Daftar Bahan Baku', 'url' => route('bahan-baku.index'),   'active' => request()->routeIs('bahan-baku.*')];
            }
            if ($hasRole('Manajer', 'Tim Dapur')) {
                $bahanBakuItems[] = ['label' => 'Stok Masuk / Keluar','url' => route('mutasi-stok.index'),  'active' => request()->routeIs('mutasi-stok.*')];
                $bahanBakuItems[] = ['label' => 'Stok Menipis',       'url' => route('stok-menipis.index'), 'active' => request()->routeIs('stok-menipis.*')];
            }
        @endphp
        @if(count($bahanBakuItems) > 0)
        @include('partials.sidebar-submenu', [
            'icon' => 'fa-boxes-stacked',
            'label' => 'Bahan Baku',
            'isOpen' => request()->routeIs('bahan-baku.*') || request()->routeIs('mutasi-stok.*') || request()->routeIs('stok-menipis.*'),
            'items' => $bahanBakuItems,
        ])
        @endif

        {{-- Pengadaan (Submenu) --}}
        @if($hasRole('Pemilik'))
        @include('partials.sidebar-submenu', [
            'icon' => 'fa-truck-field',
            'label' => 'Pengadaan',
            'isOpen' => request()->routeIs('pengadaan.*'),
            'items' => [
                ['label' => 'Buat Pengadaan',    'url' => route('pengadaan.create'), 'active' => request()->routeIs('pengadaan.create')],
                ['label' => 'Riwayat Pengadaan', 'url' => route('pengadaan.index'),  'active' => request()->routeIs('pengadaan.index') || request()->routeIs('pengadaan.show')],
            ],
        ])
        @endif

        {{-- Laporan (Submenu) --}}
        @if($hasRole('Manajer', 'Pemilik'))
        @include('partials.sidebar-submenu', [
            'icon' => 'fa-chart-pie',
            'label' => 'Laporan',
            'isOpen' => request()->routeIs('laporan.*'),
            'items' => [
                ['label' => 'Lap. Penjualan', 'url' => route('laporan.penjualan'), 'active' => request()->routeIs('laporan.penjualan')],
                ['label' => 'Lap. Persediaan Bahan Baku', 'url' => route('laporan.stok'), 'active' => request()->routeIs('laporan.stok')],
                ['label' => 'Lap. Catering',  'url' => route('laporan.catering'),   'active' => request()->routeIs('laporan.catering')],
                ['label' => 'Lap. Nasi Box',  'url' => route('laporan.nasibox'),    'active' => request()->routeIs('laporan.nasibox')],
            ],
        ])
        @endif

        {{-- Pengguna (Submenu) --}}
        @if($hasRole('Admin'))
        @include('partials.sidebar-submenu', [
            'icon' => 'fa-users-gear',
            'label' => 'Pengguna',
            'isOpen' => request()->routeIs('users.*'),
            'items' => [
                ['label' => 'Data Pegawai', 'url' => route('users.index', ['type' => 'pegawai']), 'active' => request('type') !== 'pelanggan' && request()->routeIs('users.*')],
                ['label' => 'Data Pelanggan', 'url' => route('users.index', ['type' => 'pelanggan']), 'active' => request('type') === 'pelanggan' && request()->routeIs('users.*')],
            ],
        ])
        @endif

    </nav>

    {{-- Footer Actions --}}
    <div class="p-3 border-t border-gray-800 shrink-0">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 text-sm font-semibold text-gray-400 hover:text-white hover:bg-gray-800/50 rounded-xl transition group" title="Logout">
                <i class="fa-solid fa-right-from-bracket text-[16px] w-6 text-center shrink-0 text-red-400 group-hover:text-red-300"></i>
                <span x-show="sidebarOpen" class="whitespace-nowrap transition-opacity duration-200">Log Out</span>
            </button>
        </form>
    </div>
</aside>
