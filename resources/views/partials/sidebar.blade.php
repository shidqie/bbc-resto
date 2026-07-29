{{--
|--------------------------------------------------------------------------
| Sidebar Navigation
|--------------------------------------------------------------------------
| Komponen sidebar utama aplikasi BBC Resto.
| Menggunakan Alpine.js untuk toggle open/close dan submenu.
|--}}

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
        <div class="flex items-center gap-2.5 overflow-hidden" x-show="sidebarOpen" x-transition:enter="transition delay-100 duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <img src="/images/logo-saung.png" alt="Logo" class="w-8 h-8 rounded-full object-cover shrink-0 border border-gray-700">
            <span class="font-extrabold text-white text-sm tracking-widest whitespace-nowrap">SBC RESTO</span>
        </div>
        <img x-show="!sidebarOpen" src="/images/logo-saung.png" alt="Logo" class="w-7 h-7 rounded-full object-cover shrink-0 mx-auto border border-gray-700" x-cloak>
        <button @click="sidebarOpen = !sidebarOpen" 
                class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-500 hover:text-white hover:bg-gray-800 transition-colors focus:outline-none shrink-0"
                x-bind:class="sidebarOpen ? '' : 'hidden'"
                title="Toggle Sidebar">
            <i class="fa-solid fa-chevron-left text-sm"></i>
        </button>
        <button x-show="!sidebarOpen" @click="sidebarOpen = true"
                class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-500 hover:text-white hover:bg-gray-800 transition-colors focus:outline-none shrink-0 mx-auto mt-1"
                title="Buka Sidebar" x-cloak>
            <i class="fa-solid fa-chevron-right text-sm"></i>
        </button>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 py-6 px-3 space-y-1.5 no-scrollbar" :class="sidebarOpen ? 'overflow-y-auto' : 'overflow-visible'">
        
        {{-- Dashboard --}}
        @include('partials.sidebar-link', [
            'route' => 'dashboard',
            'icon' => 'fa-house',
            'label' => 'Dashboard',
            'active' => request()->routeIs('dashboard'),
        ])

        {{-- Divider --}}
        <div class="py-2" x-show="sidebarOpen"><div class="h-px w-full bg-gray-800"></div></div>

        {{-- Pemesanan --}}
        @php
            $pemesananItems = [];
            if ($hasRole('Kasir')) {
                $pemesananItems[] = ['label' => 'Dine In', 'url' => route('pos.dinein.index'), 'active' => request()->routeIs('pos.dinein.*') && !request()->routeIs('pos.dinein.print-qr')];
            }
            if ($hasRole('Pemilik')) {
                $pemesananItems[] = ['label' => 'Catering', 'url' => route('admin.pesanan.catering.index'),  'active' => request()->routeIs('admin.pesanan.catering.*')];
                $pemesananItems[] = ['label' => 'Nasi Box', 'url' => route('admin.pesanan.nasibox.index'),   'active' => request()->routeIs('admin.pesanan.nasibox.*')];
            }
            if ($hasRole('Tim Pengantaran')) {
                $pemesananItems[] = ['label' => 'Jadwal Pengantaran', 'url' => route('admin.jadwal.index'),  'active' => request()->routeIs('admin.jadwal.*')];
            }
        @endphp
        @if(count($pemesananItems) > 0)
        @include('partials.sidebar-submenu', [
            'icon' => 'fa-cart-shopping',
            'label' => 'Pemesanan',
            'isOpen' => request()->routeIs('pesanan.*') || request()->routeIs('admin.pesanan.*') || request()->routeIs('admin.jadwal.*') || request()->routeIs('pos.dinein.*'),
            'items' => $pemesananItems,
        ])
        @endif

        {{-- Data Menu --}}
        @if($hasRole('Manajer', 'Pemilik'))
        @include('partials.sidebar-submenu', [
            'icon' => 'fa-utensils',
            'label' => 'Data Menu',
            'isOpen' => request()->routeIs('menu.*') || request()->routeIs('kategori-menu.*') || request()->routeIs('resep.*') || request()->routeIs('pos.dinein.print-qr'),
            'items' => [
                ['label' => 'Kelola Menu',   'url' => route('menu.index'), 'active' => request()->routeIs('menu.*') || request()->routeIs('pos.dinein.print-qr')],
                ['label' => 'Kelola Kategori', 'url' => route('kategori-menu.index'), 'active' => request()->routeIs('kategori-menu.*')],
            ],
        ])
        @endif

        {{-- Data Meja --}}
        @if($hasRole('Manajer', 'Pemilik'))
        @include('partials.sidebar-submenu', [
            'icon' => 'fa-store',
            'label' => 'Data Meja',
            'isOpen' => request()->routeIs('meja.*'),
            'items' => [
                ['label' => 'Manajemen Meja', 'url' => route('meja.index'), 'active' => request()->routeIs('meja.*')],
            ],
        ])
        @endif

        {{-- Persediaan Bahan Baku --}}
        @php
            $persediaanItems = [];
            if ($hasRole('Manajer', 'Pemilik')) {
                $persediaanItems[] = ['label' => 'Data Bahan Baku', 'url' => route('bahan-baku.index'),   'active' => request()->routeIs('bahan-baku.*')];
                $persediaanItems[] = ['label' => 'Pengadaan Bahan',    'url' => route('pengadaan.create'), 'active' => request()->routeIs('pengadaan.create')];
            }
            if ($hasRole('Manajer', 'Pemilik')) {
                $persediaanItems[] = ['label' => 'Stok Bahan Baku',       'url' => route('stok-menipis.index'), 'active' => request()->routeIs('stok-menipis.*')];
                $persediaanItems[] = ['label' => 'Riwayat Stok','url' => route('mutasi-stok.index'),  'active' => request()->routeIs('mutasi-stok.*')];
            }
        @endphp
        @if(count($persediaanItems) > 0)
        @include('partials.sidebar-submenu', [
            'icon' => 'fa-boxes-stacked',
            'label' => 'Persediaan Bahan Baku',
            'isOpen' => request()->routeIs('bahan-baku.*') || request()->routeIs('mutasi-stok.*') || request()->routeIs('stok-menipis.*') || request()->routeIs('pengadaan.create'),
            'items' => $persediaanItems,
        ])
        @endif

        {{-- Laporan --}}
        @if($hasRole('Pemilik', 'Manajer'))
        @include('partials.sidebar-submenu', [
            'icon' => 'fa-chart-pie',
            'label' => 'Laporan',
            'isOpen' => request()->routeIs('laporan.*') || request()->routeIs('pengadaan.index'),
            'items' => [
                ['label' => 'Penjualan', 'url' => route('laporan.penjualan'), 'active' => request()->routeIs('laporan.penjualan')],
                ['label' => 'Persediaan', 'url' => route('laporan.stok'), 'active' => request()->requestUri === route('laporan.stok', [], false) || request()->routeIs('laporan.stok')],
                ['label' => 'Pengadaan Bahan', 'url' => route('pengadaan.index'),  'active' => request()->routeIs('pengadaan.index') || request()->routeIs('pengadaan.show')],
                ['label' => 'Menu Terlaris', 'url' => route('laporan.menu-terlaris'), 'active' => request()->routeIs('laporan.menu-terlaris')],
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
