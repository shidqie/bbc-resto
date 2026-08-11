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
            if (in_array($userRole, ['Admin', 'Super Admin', 'Pemilik'])) return true;
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
        @if(!$hasRole('Pelanggan', 'Konsumen', 'Pengantaran'))
        @include('partials.sidebar-link', [
            'route' => 'dashboard',
            'icon' => 'heroicon-s-home',
            'label' => 'Dashboard',
            'active' => request()->routeIs('dashboard'),
        ])
        @endif

        {{-- Divider --}}
        <div class="py-1.5" x-show="sidebarOpen"><div class="h-px w-full bg-neutral-100"></div></div>
        
        {{-- Penjualan --}}
        @php
        $penjualanItems = [];
        if ($hasRole('Pemilik')) {
            $penjualanItems[] = ['label' => 'Semua Daftar Pesanan', 'url' => route('admin.pesanan.index'), 'active' => request()->routeIs('admin.pesanan.index')];
        }
        if ($hasRole('Kasir', 'Pemilik')) {
            $penjualanItems[] = ['label' => 'Daftar Pesanan Dine In', 'url' => route('admin.pesanan.dinein.index'), 'active' => request()->routeIs('admin.pesanan.dinein.*')];
        }
        if ($hasRole('Pemilik')) {
            $penjualanItems[] = ['label' => 'Daftar Pesanan Katering', 'url' => route('admin.pesanan.catering.index'), 'active' => request()->routeIs('admin.pesanan.catering.*')];
            $penjualanItems[] = ['label' => 'Daftar Pesanan Nasi Box', 'url' => route('admin.pesanan.nasibox.index'), 'active' => request()->routeIs('admin.pesanan.nasibox.*')];
        }

            
            @endphp
            @if(count($penjualanItems))
            @include('partials.sidebar-submenu', [
                'icon' => 'ionicon-cart-sharp',
                'label' => 'Daftar Pesanan',
                'isOpen' => request()->routeIs('pos.dinein.*') || request()->routeIs('admin.pesanan.*') || request()->routeIs('admin.pembayaran.*') || request()->routeIs('admin.jadwal.*'),
                'items' => $penjualanItems,
                ])
                @endif
                
                {{-- Jadwal Pengantaran --}}
                @if($hasRole('Pemilik', 'Pengantaran'))
                @include('partials.sidebar-link', [
                    'route' => 'admin.jadwal.index',
                    'icon' => 'gmdi-local-shipping-o',
                    'label' => 'Jadwal Pengantaran',
                    'active' => request()->routeIs('admin.jadwal.*') || request()->routeIs('admin.jadwal-pengantaran.*'),
                ])
                @endif
                
                {{-- Menu & Paket --}}
                @if($hasRole('Admin', 'Manajer', 'Pemilik'))
                @include('partials.sidebar-submenu', [
                    'icon' => 'gmdi-menu-book-r',
                    'label' => 'Manajemen Menu',
                    'isOpen' => request()->routeIs('menu.*') || request()->routeIs('kategori-menu.*') || request()->routeIs('paket-catering.*'),
                    'items' => [
                        ['label' => 'Data Menu', 'url' => route('menu.index'), 'active' => request()->routeIs('menu.*')],
                        ['label' => 'Kategori Menu', 'url' => route('kategori-menu.index'), 'active' => request()->routeIs('kategori-menu.*')],
                        ],
                        ])
                        @endif
                        
                        {{-- Meja --}}
                        @if($hasRole('Admin', 'Manajer', 'Pemilik'))
                            @include('partials.sidebar-submenu', [
                                'icon' => 'gmdi-table-bar',
                                'label' => 'Manajemen Meja',
                                'isOpen' => request()->routeIs('meja.*'),
                                'items' => [
                                    ['label' => 'Data Meja', 'url' => route('meja.index'), 'active' => request()->routeIs('meja.*')],
                                ],
                            ])
                        @endif

{{-- Persediaan --}}
        @if($hasRole('Admin', 'Manajer', 'Pemilik', 'Dapur', 'Tim Dapur'))
        @include('partials.sidebar-submenu', [
            'icon' => 'heroicon-s-archive-box',
            'label' => 'Persediaan',
            'isOpen' => request()->routeIs('bahan-baku.*') || request()->routeIs('kategori-bahan.*') || request()->routeIs('satuan.*') || request()->routeIs('stok-operasional.*') || request()->routeIs('stok-catering.*') || request()->routeIs('mutasi-stok.*') || request()->routeIs('penyesuaian-stok.*'),
            'items' => [
                ['label' => 'Data Bahan Baku', 'url' => route('bahan-baku.index'), 'active' => request()->routeIs('bahan-baku.*') || request()->routeIs('kategori-bahan.*') || request()->routeIs('satuan.*')],
                ['label' => 'Stok Dine In & Nasi Box', 'url' => route('stok-operasional.index'), 'active' => request()->routeIs('stok-operasional.*')],
                ['label' => 'Stok Catering', 'url' => route('stok-catering.index'), 'active' => request()->routeIs('stok-catering.*')],
                ['label' => 'Penyesuaian Stok', 'url' => route('penyesuaian-stok.index'), 'active' => request()->routeIs('penyesuaian-stok.*')],
            ],
        ])
        @endif

     {{-- Pengadaan --}}
                        @if($hasRole('Admin', 'Manajer', 'Pemilik', 'Dapur', 'Tim Dapur'))
                            @include('partials.sidebar-submenu', [
                                'icon' => 'heroicon-s-shopping-bag',
                                'label' => 'Pengadaan',
                                'isOpen' => request()->routeIs('pengadaan.*'),
                                'items' => [
                                    ['label' => 'Semua Permintaan', 'url' => route('pengadaan.permintaan.index'), 'active' => request()->routeIs('pengadaan.permintaan.*')],
                                    ['label' => 'Purchase Order', 'url' => route('pengadaan.po.index'), 'active' => request()->routeIs('pengadaan.po.*')],
                                    ['label' => 'Penerimaan Bahan Baku', 'url' => route('pengadaan.penerimaan.index'), 'active' => request()->routeIs('pengadaan.penerimaan.*')],
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
                ['label' => 'Penjualan', 'url' => route('laporan.penjualan'), 'active' => request()->routeIs('laporan.penjualan*')],
                ['label' => 'Persediaan', 'url' => route('laporan.persediaan'), 'active' => request()->routeIs('laporan.persediaan*')],
                ['label' => 'Pengadaan', 'url' => route('laporan.pengadaan'), 'active' => request()->routeIs('laporan.pengadaan*')],
            ],
        ])
        @endif

        {{-- Manajemen Pengguna --}}
        @if($hasRole('Pemilik'))
        @include('partials.sidebar-submenu', [
            'icon' => 'fluentui-people-28',
            'label' => 'Manajemen Pengguna',
            'isOpen' => request()->routeIs('users.*') || request()->routeIs('roles.*'),
            'items' => [
                ['label' => 'Data Karyawan', 'url' => route('users.index'), 'active' => request()->routeIs('users.*') && request('type') !== 'pelanggan'],
                ['label' => 'Data Konsumen', 'url' => route('users.index', ['type' => 'pelanggan']), 'active' => request()->routeIs('users.*') && request('type') === 'pelanggan'],
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
