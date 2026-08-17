{{--
|--------------------------------------------------------------------------
| Sidebar Navigation
|--------------------------------------------------------------------------
| Komponen sidebar utama aplikasi BBC Resto.
| Menggunakan Alpine.js untuk toggle open/close dan submenu.
|--}}

<aside :class="sidebarOpen ? 'w-[280px]' : 'w-20'" class="no-print bg-white border-r border-slate-200/60 text-slate-600 flex flex-col shrink-0 transition-all duration-300 relative z-20 shadow-[4px_0_24px_rgba(0,0,0,0.02)]">
    @php
        $userRole = auth()->user()->peran->nama_peran ?? '';
        $hasRole = function(...$roles) use ($userRole) {
            if (in_array($userRole, ['Admin', 'Super Admin', 'Pemilik'])) return true;
            return in_array($userRole, $roles);
        };
    @endphp

    {{-- Logo & Toggle --}}
    <div class="h-16 flex items-center border-b border-slate-100/80 shrink-0 transition-all duration-300" :class="sidebarOpen ? 'justify-between px-4' : 'justify-center px-0'">
        <div class="flex items-center gap-3 overflow-hidden" x-show="sidebarOpen" x-transition:enter="transition delay-100 duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <img src="/images/logo-saung.png" alt="Logo" class="h-9 w-auto object-contain drop-shadow-sm shrink-0">
            <span class="font-extrabold text-slate-900 text-sm tracking-tight whitespace-nowrap bg-clip-text text-transparent bg-gradient-to-r from-slate-900 to-slate-700">RM BBC</span>
        </div>
        
        <button x-show="!sidebarOpen" @click="sidebarOpen = true"
                class="w-10 h-10 flex items-center justify-center rounded-lg hover:bg-slate-50 transition-all focus:outline-none shrink-0 border border-transparent shadow-sm shadow-transparent"
                title="Buka Sidebar" x-cloak>
            <img src="/images/logo-saung.png" alt="Logo" class="w-8 h-8 object-contain drop-shadow-sm">
        </button>

        <button @click="sidebarOpen = !sidebarOpen"
                class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-all focus:outline-none shrink-0 border border-transparent hover:border-blue-100/50 shadow-sm shadow-transparent hover:shadow-blue-500/5"
                x-bind:class="sidebarOpen ? '' : 'hidden'"
                title="Toggle Sidebar">
            <x-heroicon-o-chevron-left class="w-5 h-5" />
        </button>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 py-5 px-2.5 space-y-1 no-scrollbar" :class="sidebarOpen ? 'overflow-y-auto' : 'overflow-visible'">

        {{-- Dashboard --}}
        @if(!in_array($userRole, ['Pelanggan', 'Konsumen', 'Pengantaran']))
        @include('partials.sidebar-link', [
            'route' => 'dashboard',
            'icon' => 'heroicon-o-squares-2x2',
            'label' => 'Dashboard',
            'active' => request()->routeIs('dashboard'),
        ])
        @endif

        {{-- Divider --}}
        <div class="py-1.5 px-3" x-show="sidebarOpen"><div class="h-px w-full bg-gradient-to-r from-transparent via-slate-200/60 to-transparent"></div></div>
        
        {{-- Penjualan --}}
        @php
        $penjualanItems = [];
        if ($hasRole('Pemilik')) {
            $penjualanItems[] = ['label' => 'Semua Daftar Pesanan ', 'url' => route('admin.pesanan.index'), 'active' => request()->routeIs('admin.pesanan.index')];
        }
        if ($hasRole('Kasir', 'Pemilik')) {
            $penjualanItems[] = ['label' => 'Daftar Pesanan Dine In', 'url' => route('admin.pesanan.dinein.index'), 'active' => request()->routeIs('admin.pesanan.dinein.*')];
        }
        if ($hasRole('Pemilik')) {
            $penjualanItems[] = ['label' => 'Daftar Pesanan Katering', 'url' => route('admin.pesanan.catering.index'), 'active' => request()->routeIs('admin.pesanan.catering.*')];
            $penjualanItems[] = ['label' => 'Daftar Pesanan  Nasi Box', 'url' => route('admin.pesanan.nasibox.index'), 'active' => request()->routeIs('admin.pesanan.nasibox.*')];
        }

            
            @endphp
            @if(count($penjualanItems))
            @include('partials.sidebar-submenu', [
                'icon' => 'heroicon-o-shopping-cart',
                'label' => 'Transaksi Penjualan',
                'isOpen' => request()->routeIs('pos.dinein.*') || request()->routeIs('admin.pesanan.*') || request()->routeIs('admin.pembayaran.*') || request()->routeIs('admin.jadwal.*'),
                'items' => $penjualanItems,
                ])
                @endif
                
                {{-- Jadwal Pengiriman --}}
                @if($hasRole('Pemilik', 'Pengantaran'))
                @include('partials.sidebar-link', [
                    'route' => 'admin.jadwal.index',
                    'icon' => 'heroicon-o-truck',
                    'label' => 'Jadwal Pengiriman',
                    'active' => request()->routeIs('admin.jadwal.*') || request()->routeIs('admin.jadwal-pengiriman.*'),
                ])
                @endif
                
                {{-- Data Master --}}
                @php
                    $dataMasterItems = [];
                    if ($hasRole('Admin', 'Manajer', 'Pemilik')) {
                        $dataMasterItems[] = ['label' => 'Data Menu & Resep', 'url' => route('menu.index'), 'active' => request()->routeIs('menu.*')];
                        $dataMasterItems[] = ['label' => 'Kategori Menu', 'url' => route('kategori-menu.index'), 'active' => request()->routeIs('kategori-menu.*')];
                        $dataMasterItems[] = ['label' => 'Data Meja', 'url' => route('meja.index'), 'active' => request()->routeIs('meja.*')];
                    }
                    if ($hasRole('Admin', 'Manajer', 'Pemilik', 'Dapur', 'Tim Dapur')) {
                        $dataMasterItems[] = ['label' => 'Data Bahan Baku', 'url' => route('bahan-baku.index'), 'active' => request()->routeIs('bahan-baku.*') || request()->routeIs('kategori-bahan.*') || request()->routeIs('satuan.*')];
                    }
                @endphp
                @if(count($dataMasterItems))
                @include('partials.sidebar-submenu', [
                    'icon' => 'heroicon-o-folder',
                    'label' => 'Data Master',
                    'isOpen' => request()->routeIs('menu.*') || request()->routeIs('kategori-menu.*') || request()->routeIs('meja.*') || request()->routeIs('bahan-baku.*') || request()->routeIs('kategori-bahan.*') || request()->routeIs('satuan.*'),
                    'items' => $dataMasterItems,
                ])
                @endif

                {{-- Persediaan --}}
                @if($hasRole('Admin', 'Manajer', 'Pemilik', 'Dapur', 'Tim Dapur'))
                @include('partials.sidebar-submenu', [
                    'icon' => 'heroicon-o-archive-box',
                    'label' => 'Persediaan',
                    'isOpen' => request()->routeIs('stok-operasional.*') || request()->routeIs('stok-catering.*') || request()->routeIs('penyesuaian-stok.*'),
                    'items' => [
                        ['label' => 'Stok Operasional', 'url' => route('stok-operasional.index'), 'active' => request()->routeIs('stok-operasional.*')],
                        ['label' => 'Stok Catering', 'url' => route('stok-catering.index'), 'active' => request()->routeIs('stok-catering.*')],
                        ['label' => 'Penyesuaian Stok', 'url' => route('penyesuaian-stok.index'), 'active' => request()->routeIs('penyesuaian-stok.*')],
                    ],
                ])
                @endif

     {{-- Pengadaan --}}
                        @if($hasRole('Admin', 'Manajer', 'Pemilik', 'Dapur', 'Tim Dapur'))
                            @include('partials.sidebar-submenu', [
                                'icon' => 'heroicon-o-shopping-bag',
                                'label' => 'Pengadaan',
                                'isOpen' => request()->routeIs('pengadaan.po.*'),
                                'items' => [
                                    ['label' => 'Purchase Order', 'url' => route('pengadaan.po.index'), 'active' => request()->routeIs('pengadaan.po.*')],
                                ],
                            ])
                        @endif

        {{-- Laporan --}}
        @if($hasRole('Pemilik', 'Manajer'))
        @include('partials.sidebar-submenu', [
            'icon' => 'heroicon-o-chart-bar',
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
            'icon' => 'heroicon-o-users',
            'label' => 'Manajemen Pengguna',
            'isOpen' => request()->routeIs('users.*') || request()->routeIs('roles.*'),
            'items' => [
                ['label' => 'Data Karyawan', 'url' => route('users.index'), 'active' => request()->routeIs('users.*') && request('type') !== 'pelanggan'],
                ['label' => 'Data Konsumen', 'url' => route('users.index', ['type' => 'pelanggan']), 'active' => request()->routeIs('users.*') && request('type') === 'pelanggan'],
            ],
        ])
        @endif
        {{-- Pengaturan --}}
        @if($hasRole('Pemilik', 'Manajer'))
        @include('partials.sidebar-submenu', [
            'icon' => 'heroicon-o-cog-6-tooth',
            'label' => 'Pengaturan',
            'isOpen' => request()->routeIs('admin.pengaturan.*'),
            'items' => [
                ['label' => 'Pajak & Layanan', 'url' => route('admin.pengaturan.transaksi.index'), 'active' => request()->routeIs('admin.pengaturan.transaksi.*')],
                ['label' => 'Tarif Pengiriman', 'url' => route('admin.pengaturan.pengiriman.index'), 'active' => request()->routeIs('admin.pengaturan.pengiriman.*')],
            ],
        ])
        @endif

       
    </nav>

    {{-- Footer Actions --}}
    <div class="p-3 border-t border-slate-100/80 shrink-0">
        <div class="flex items-center justify-between p-2 -mx-2 rounded-xl hover:bg-slate-50 transition-colors duration-200 group/profile" :class="!sidebarOpen ? 'justify-center' : ''">
            
            {{-- Profil (Hanya tampil saat sidebar terbuka) --}}
            <div class="flex items-center gap-3 overflow-hidden" x-show="sidebarOpen" x-transition.opacity>
                <div class="w-9 h-9 rounded-full bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-sm shrink-0 border border-slate-200/50 group-hover/profile:bg-white group-hover/profile:shadow-sm transition-all">
                    {{ strtoupper(substr(auth()->user()->nama ?? 'A', 0, 2)) }}
                </div>
                <div class="flex flex-col min-w-0">
                    <p class="text-sm font-semibold text-slate-800 truncate">{{ auth()->user()->nama ?? 'User' }}</p>
                    <p class="text-[12px] text-slate-500 truncate">{{ auth()->user()->peran->nama_peran ?? 'Admin' }}</p>
                </div>
            </div>
            
            {{-- Tombol Logout (Tampil penuh saat minimize) --}}
            <form method="POST" action="{{ route('logout') }}" :class="!sidebarOpen ? 'w-full flex justify-center' : ''">
                @csrf
                <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition-all duration-200 focus:outline-none border border-transparent hover:border-red-100" :class="!sidebarOpen ? 'w-10 h-10 flex items-center justify-center' : ''" title="Logout">
                    <x-heroicon-o-arrow-right-on-rectangle class="w-5 h-5" />
                </button>
            </form>
        </div>
    </div>
</aside>
