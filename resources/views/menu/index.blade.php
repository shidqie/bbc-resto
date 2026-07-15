{{-- 
    Halaman: Daftar Menu
    Deskripsi: Mengelola daftar menu makanan, minuman, dan paket untuk pelanggan.
--}}
@extends('layouts.pos')

@section('content')
<div class="flex-1 overflow-auto bg-gray-50/50 text-gray-800 font-sans">
    <div class="p-4 md:p-6 lg:p-8 max-w-[1600px] mx-auto space-y-6">
        
        {{-- Header --}}
        <x-ui.page-header 
            title="Daftar Menu" 
            subtitle="Kelola daftar menu makanan, minuman, dan paket untuk pelanggan."
            :breadcrumbs="['Menu', 'Daftar Menu']">
            <x-slot:actions>
                <x-ui.button href="{{ route('menu.create') }}" icon="fa-plus">Tambah Menu</x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>
        
        {{-- Summary Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <x-ui.stat-card label="Total Menu" :value="$stats['total']" icon="fa-utensils" color="blue" />
            <x-ui.stat-card label="Menu Dine-in" :value="$stats['dine_in']" icon="fa-store" color="orange" />
            <x-ui.stat-card label="Menu Catering" :value="$stats['catering']" icon="fa-concierge-bell" color="green" />
            <x-ui.stat-card label="Menu Nasi Box" :value="$stats['nasi_box']" icon="fa-box" color="purple" />
        </div>

        {{-- Alert --}}
        <x-ui.alert />
        
        {{-- Data Table --}}
        <x-ui.data-table :paginator="$menus">
            <x-slot:toolbar>
                <form action="{{ route('menu.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                    <div class="relative w-full sm:w-64">
                        <x-heroicon-o-magnifying-glass class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 w-5 h-5 inline-block shrink-0" />
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama menu..." class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] outline-none bg-white transition-all">
                    </div>
                    
                    <select name="kategori" class="px-4 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] outline-none bg-white min-w-[150px]">
                        <option value="">Semua Kategori</option>
                        @foreach($kategoris as $kat)
                            <option value="{{ $kat->id }}" {{ request('kategori') == $kat->id ? 'selected' : '' }}>{{ $kat->nama_kategori }}</option>
                        @endforeach
                    </select>

                    <select name="jenis_menu" class="px-4 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] outline-none bg-white min-w-[150px]">
                        <option value="">Semua Jenis</option>
                        <option value="dine_in" {{ request('jenis_menu') == 'dine_in' ? 'selected' : '' }}>Dine-in</option>
                        <option value="catering" {{ request('jenis_menu') == 'catering' ? 'selected' : '' }}>Catering</option>
                        <option value="nasi_box" {{ request('jenis_menu') == 'nasi_box' ? 'selected' : '' }}>Nasi Box</option>
                    </select>
                    
                    <x-ui.button type="submit">Filter</x-ui.button>
                    @if(request()->hasAny(['search', 'kategori', 'jenis_menu']))
                        <x-ui.button href="{{ route('menu.index') }}" variant="outline">Reset</x-ui.button>
                    @endif
                </form>
            </x-slot:toolbar>

            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                        <th class="px-6 py-4 font-semibold whitespace-nowrap">Menu</th>
                        <th class="px-6 py-4 font-semibold whitespace-nowrap">Kategori</th>
                        <th class="px-6 py-4 font-semibold whitespace-nowrap">Jenis</th>
                        <th class="px-6 py-4 font-semibold whitespace-nowrap">Harga</th>
                        <th class="px-6 py-4 font-semibold whitespace-nowrap text-center">Bahan Resep</th>
                        <th class="px-6 py-4 font-semibold whitespace-nowrap">Status</th>
                        <th class="px-6 py-4 font-semibold whitespace-nowrap text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @forelse($menus as $menu)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    @if($menu->foto)
                                        <img src="{{ Storage::url($menu->foto) }}" class="w-12 h-12 rounded-lg object-cover border border-gray-200" alt="{{ $menu->nama }}">
                                    @else
                                        <div class="w-12 h-12 rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center text-gray-400">
                                            <x-heroicon-o-photo class="w-5 h-5 inline-block shrink-0" />
                                        </div>
                                    @endif
                                    <div>
                                        <div class="font-bold text-gray-900">{{ $menu->nama }}</div>
                                        @if($menu->deskripsi)
                                            <div class="text-[11px] text-gray-500 truncate max-w-[200px] mt-0.5">{{ $menu->deskripsi }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-600">
                                {{ $menu->kategori->nama_kategori ?? '-' }}
                            </td>
                            <td class="px-6 py-4">
                                @if($menu->jenis_menu == 'dine_in')
                                    <span class="inline-flex items-center gap-1 text-xs font-medium text-orange-700 bg-orange-50 px-2.5 py-1 rounded-full border border-orange-200/50">
                                        <x-heroicon-o-building-storefront class="text-[10px] w-5 h-5 inline-block shrink-0" /> Dine-in
                                    </span>
                                @elseif($menu->jenis_menu == 'catering')
                                    <span class="inline-flex items-center gap-1 text-xs font-medium text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200/50">
                                        <x-heroicon-o-bell-alert class="text-[10px] w-5 h-5 inline-block shrink-0" /> Catering
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-xs font-medium text-purple-700 bg-purple-50 px-2.5 py-1 rounded-full border border-purple-200/50">
                                        <x-heroicon-o-cube class="text-[10px] w-5 h-5 inline-block shrink-0" /> Nasi Box
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-semibold text-gray-900">
                                Rp {{ number_format($menu->harga, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($menu->resep->count() > 0)
                                    <span class="bg-blue-50 text-blue-700 px-3 py-1 rounded-full text-xs font-medium border border-blue-200">{{ $menu->resep->count() }} Bahan</span>
                                @else
                                    <span class="text-xs text-gray-400 italic">Belum diset</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($menu->status == 'tersedia')
                                    <x-ui.badge color="success" dot>Tersedia</x-ui.badge>
                                @else
                                    <x-ui.badge color="danger" dot>Habis</x-ui.badge>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right space-x-1">
                                <a href="{{ route('menu.show', $menu->id) }}" class="inline-block p-2 px-3 text-indigo-600 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition-colors font-medium text-xs" title="Detail & Resep">
                                    <x-heroicon-o-list-bullet class="mr-1 w-5 h-5 inline-block shrink-0" /> Resep
                                </a>
                                <a href="{{ route('menu.edit', $menu->id) }}" class="inline-block p-2 text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors" title="Edit">
                                    <x-heroicon-o-pencil-square class="w-5 h-5 inline-block shrink-0" />
                                </a>
                                <form action="{{ route('menu.destroy', $menu->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus menu ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors" title="Hapus">
                                        <x-heroicon-o-trash class="w-5 h-5 inline-block shrink-0" />
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-ui.empty-state icon="fa-book-open" title="Belum ada data menu." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-ui.data-table>

    </div>
</div>
@endsection
