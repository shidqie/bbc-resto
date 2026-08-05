@extends('layouts.pos')
@section('title', 'Semua Permintaan')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800">
    <div class="w-full p-6 space-y-5">

        {{-- PAGE HEADER --}}
        <x-ui.page-header
            title="Semua Permintaan"
            subtitle="Menampilkan seluruh permintaan pengadaan bahan baku dari operasional harian maupun pesanan catering."
            :breadcrumbs="['Pengadaan', 'Semua Permintaan']">
            <x-slot:actions>
                <div x-data="{ openDropdown: false }" class="relative">
                    <button @click="openDropdown = !openDropdown" @click.away="openDropdown = false" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-gray-900 rounded-lg px-3 py-2 hover:bg-gray-800 transition-colors">
                        <x-heroicon-o-plus class="w-4 h-4" />
                        Buat Permintaan
                        <x-heroicon-o-chevron-down class="w-4 h-4 ml-1 opacity-70" />
                    </button>
                    
                    <div x-show="openDropdown" x-transition.opacity style="display: none;" class="absolute right-0 mt-2 w-56 bg-white border border-gray-100 rounded-xl shadow-lg py-1 z-50">
                        <a href="{{ route('pengadaan.harian.create') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 font-medium">Permintaan Harian</a>
                        <a href="{{ route('pengadaan.catering.create') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 font-medium">Permintaan Catering</a>
                    </div>
                </div>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.alert />

        {{-- Table with integrated toolbar --}}
        <x-ui.data-table :paginator="$pengadaans">
            <x-slot:toolbar>
                <form action="{{ route('pengadaan.permintaan.index') }}" method="GET" class="flex items-center gap-2 w-full flex-wrap">
                    <x-search-input name="search" value="{{ request('search') }}" placeholder="Cari nomor permintaan, ID pesanan..." />
                    
                    <x-select-input name="jenis" :options="['harian' => 'Harian', 'catering' => 'Catering']" :selected="request('jenis')" placeholder="Jenis Permintaan" :auto-submit="true" />
                    
                    <x-select-input name="status" :options="$statuses->pluck('nama_status', 'id')->toArray()" :selected="request('status')" placeholder="Semua Status" :auto-submit="true" />
                    
                    <x-select-input name="periode" :options="['hari_ini' => 'Hari Ini', 'minggu_ini' => 'Minggu Ini', 'bulan_ini' => 'Bulan Ini']" :selected="request('periode')" placeholder="Semua Periode" :auto-submit="true" />
                    
                    <button type="submit" class="text-sm font-medium bg-white border border-gray-200 text-gray-600 rounded-lg px-3 py-2 hover:bg-gray-50 transition-colors shrink-0">Cari</button>
                    @if(request()->hasAny(['search', 'jenis', 'status', 'periode']))
                        <a href="{{ route('pengadaan.permintaan.index') }}" class="text-xs font-medium text-red-500 hover:text-red-700 px-2 py-2 rounded-lg hover:bg-red-50 transition-colors shrink-0">Reset Filter</a>
                    @endif
                </form>
            </x-slot:toolbar>

            <table class="w-full text-sm min-w-[900px]">
                <thead>
                    <tr class="border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        <th class="px-4 py-3 text-left w-12">No</th>
                        <th class="px-4 py-3 text-left">No. Permintaan</th>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-left">Jenis</th>
                        <th class="px-4 py-3 text-left">Sumber</th>
                        <th class="px-4 py-3 text-center">Total Bahan</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($pengadaans as $i => $p)
                    <tr class="hover:bg-gray-50/60 transition-colors group">
                        <td class="px-4 py-3 text-sm text-gray-500 font-medium align-middle">
                            {{ $pengadaans->firstItem() + $i }}
                        </td>
                        <td class="px-4 py-3 align-middle">
                            <span class="font-mono font-bold text-gray-900 text-xs">{{ $p->nomor_pengadaan }}</span>
                        </td>
                        <td class="px-4 py-3 align-middle">
                            <p class="font-medium text-gray-900 text-sm">{{ \Carbon\Carbon::parse($p->tanggal_pengadaan)->format('d M Y') }}</p>
                        </td>
                        <td class="px-4 py-3 align-middle">
                            @if($p->jenis_pengadaan == 'harian')
                                <x-ui.badge color="primary" size="sm">Harian</x-ui.badge>
                            @else
                                <x-ui.badge color="warning" size="sm">Catering</x-ui.badge>
                            @endif
                        </td>
                        <td class="px-4 py-3 align-middle">
                            @if($p->jenis_pengadaan == 'catering' && $p->pesanan)
                                <p class="text-xs text-gray-500 font-medium">Pesanan Catering</p>
                                <p class="font-bold text-gray-900 text-xs font-mono">{{ $p->pesanan->nomor_pesanan }}</p>
                            @else
                                <span class="text-xs text-gray-400 font-medium">Operasional Harian</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 align-middle text-center font-bold text-gray-900">
                            {{ $p->detail_pengadaan_bahan->count() }} <span class="text-xs font-normal text-gray-500">item</span>
                        </td>
                        <td class="px-4 py-3 align-middle text-center">
                            @php
                                $sId = $p->status_pengadaan_id;
                                $sColor = 'gray';
                                if($sId == 1) $sColor = 'warning'; // Menunggu Pembelian
                                elseif($sId == 2) $sColor = 'primary'; // Telah Dipesan
                                elseif($sId == 3) $sColor = 'primary'; // Diterima Sebagian
                                elseif($sId == 4) $sColor = 'success'; // Selesai
                                elseif($sId == 5) $sColor = 'danger'; // Dibatalkan
                            @endphp
                            <x-ui.badge :color="$sColor" size="sm">{{ optional($p->status_pengadaan)->nama_status ?? 'Unknown' }}</x-ui.badge>
                        </td>
                        <td class="px-4 py-3 align-middle text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <button type="button" onclick="window.showToast('info', 'Detail belum tersedia')" title="Detail" class="w-7 h-7 rounded-full flex items-center justify-center bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors">
                                    <x-heroicon-o-eye class="w-3 h-3" />
                                </button>
                                <button type="button" onclick="window.showToast('info', 'Cetak belum tersedia')" title="Cetak" class="w-7 h-7 rounded-full flex items-center justify-center bg-gray-50 text-gray-600 hover:bg-gray-100 transition-colors">
                                    <x-heroicon-o-printer class="w-3 h-3" />
                                </button>
                                
                                <div x-data="{ open: false }" class="relative">
                                    <button @click="open = !open" @click.away="open = false" type="button" title="Ubah Status" class="w-7 h-7 rounded-full flex items-center justify-center bg-amber-50 text-amber-600 hover:bg-amber-100 transition-colors">
                                        <x-heroicon-o-ellipsis-vertical class="w-3 h-3" />
                                    </button>
                                    <div x-show="open" style="display: none;" class="absolute right-0 mt-2 w-48 bg-white border border-gray-100 rounded-xl shadow-lg py-1 z-50">
                                        @foreach($statuses as $st)
                                            <form action="{{ route('pengadaan.update-status', $p->id) }}" method="POST">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="status" value="{{ $st->id }}">
                                                <button type="submit" class="w-full text-left px-4 py-2 text-xs hover:bg-gray-50 font-medium {{ $p->status_pengadaan_id == $st->id ? 'text-emerald-600 bg-emerald-50' : 'text-gray-700' }}">
                                                    {{ $st->nama_status }}
                                                </button>
                                            </form>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <x-empty-state icon="clipboard-document-list" title="Tidak ada data" message="Belum ada data permintaan pengadaan." :colspan="8" />
                    @endforelse
                </tbody>
            </table>
        </x-ui.data-table>

    </div>
</div>
@endsection
