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
                    <x-search-input name="search" value="{{ request('search') }}" placeholder="Cari nomor permintaan, Kode pesanan..." />
                    
                    <x-ui.multi-select name="jenis" :options="['harian' => 'Harian', 'catering' => 'Catering']" :selected="request('jenis')" label="Jenis" type="radio" />
                    
                    <x-ui.multi-select name="status" :options="$statuses->pluck('nama_status', 'id')->toArray()" :selected="request('status')" label="Status" type="radio" />
                    
                    <x-ui.multi-select name="periode" :options="['hari_ini' => 'Hari Ini', 'minggu_ini' => 'Minggu Ini', 'bulan_ini' => 'Bulan Ini']" :selected="request('periode')" label="Periode" type="radio" />
                    
                    @if(request()->hasAny(['search', 'jenis', 'status', 'periode']))
                        <a href="{{ route('pengadaan.permintaan.index') }}" class="text-xs font-medium text-red-500 hover:text-red-700 px-2 py-2 rounded-lg hover:bg-red-50 transition-colors shrink-0">Reset Filter</a>
                    @endif
                </form>
            </x-slot:toolbar>

            <x-ui.table class="min-w-[950px]">
                <x-ui.table.header>
                    <th class="px-4 py-3.5 text-left w-12">No</th>
                    <th class="px-4 py-3.5 text-left">No. Permintaan</th>
                    <th class="px-4 py-3.5 text-left">Tanggal</th>
                    <th class="px-4 py-3.5 text-left">Jenis</th>
                    <th class="px-4 py-3.5 text-left">Sumber</th>
                    <th class="px-4 py-3.5 text-center">Total Bahan</th>
                    <th class="px-4 py-3.5 text-center">Status</th>
                    <th class="px-4 py-3.5 text-center">Aksi</th>
                </x-ui.table.header>
                <tbody class="divide-y divide-gray-100">
                    @forelse($pengadaans as $i => $p)
                    <x-ui.table.row>
                        <td class="px-4 py-4 text-sm text-gray-500 font-medium align-middle">
                            {{ $pengadaans->firstItem() + $i }}
                        </td>
                        <td class="px-4 py-4 align-middle">
                            <span class="font-mono font-bold text-gray-900 text-xs">{{ $p->nomor_pengadaan }}</span>
                        </td>
                        <td class="px-4 py-4 align-middle">
                            <p class="font-medium text-gray-900 text-sm">{{ \Carbon\Carbon::parse($p->tanggal_pengadaan)->format('d M Y') }}</p>
                        </td>
                        <td class="px-4 py-4 align-middle">
                            @if($p->jenis_pengadaan == 'harian')
                                <x-ui.badge color="primary" size="sm">Harian</x-ui.badge>
                            @else
                                <x-ui.badge color="warning" size="sm">Catering</x-ui.badge>
                            @endif
                        </td>
                        <td class="px-4 py-4 align-middle">
                            @if($p->jenis_pengadaan == 'catering' && $p->pesanan)
                                <p class="text-xs text-gray-500 font-medium">Pesanan Catering</p>
                                <p class="font-bold text-gray-900 text-xs font-mono">{{ $p->pesanan->nomor_pesanan }}</p>
                            @else
                                <span class="text-xs text-gray-400 font-medium">Operasional Harian</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 align-middle text-center font-bold text-gray-900">
                            {{ $p->detail_pengadaan_bahan->count() }} <span class="text-xs font-normal text-gray-500">item</span>
                        </td>
                        <td class="px-4 py-4 align-middle text-center">
                            @php
                                $kodeStatus = optional($p->status_pengadaan)->kode_status;
                                $sColor = 'gray';
                                if($kodeStatus == 'draft') $sColor = 'gray';
                                elseif($kodeStatus == 'menunggu_pembelian') $sColor = 'warning';
                                elseif($kodeStatus == 'dalam_proses') $sColor = 'primary';
                                elseif($kodeStatus == 'menunggu_penerimaan') $sColor = 'primary';
                                elseif($kodeStatus == 'diterima_sebagian') $sColor = 'warning';
                                elseif($kodeStatus == 'selesai') $sColor = 'success';
                                elseif($kodeStatus == 'dibatalkan') $sColor = 'danger';
                            @endphp
                            <x-ui.badge :color="$sColor" size="sm">{{ optional($p->status_pengadaan)->nama_status ?? 'Unknown' }}</x-ui.badge>
                        </td>
                        <td class="px-4 py-4 align-middle text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('pengadaan.permintaan.show', $p->id) }}" title="Detail" class="w-7 h-7 rounded-full flex items-center justify-center bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors">
                                    <x-heroicon-o-eye class="w-3 h-3" />
                                </a>
                                <a href="{{ route('pengadaan.permintaan.pdf', $p->id) }}" title="Unduh PDF" class="w-7 h-7 rounded-full flex items-center justify-center bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors">
                                    <x-heroicon-o-printer class="w-3 h-3" />
                                </a>
                                @if(in_array($kodeStatus, ['draft', 'menunggu_pembelian']))
                                    <a href="{{ route('pengadaan.permintaan.edit', $p->id) }}" title="Ubah" class="w-7 h-7 rounded-full flex items-center justify-center bg-amber-50 text-amber-600 hover:bg-amber-100 transition-colors">
                                        <x-heroicon-o-pencil-square class="w-3 h-3" />
                                    </a>
                                @endif
                                @if(in_array($kodeStatus, ['draft', 'menunggu_pembelian', 'dalam_proses']))
                                    <form id="form-batal-{{ $p->id }}" action="{{ route('pengadaan.permintaan.cancel', $p->id) }}" method="POST" class="inline">
                                        @csrf
                                    </form>
                                    <button type="button" title="Batalkan" onclick="window.confirmDialog({ title: 'Batalkan Permintaan', name: '{{ $p->nomor_pengadaan }}', message: 'Permintaan yang dibatalkan tidak dapat diproses kembali.', formId: 'form-batal-{{ $p->id }}', confirmText: 'Batalkan', cancelText: 'Batal', type: 'warning' })" class="w-7 h-7 rounded-full flex items-center justify-center bg-rose-50 text-rose-600 hover:bg-rose-100 transition-colors">
                                        <x-heroicon-o-x-mark class="w-3 h-3" />
                                    </button>
                                @endif
                            </div>
                        </td>
                    </x-ui.table.row>
                    @empty
                    <x-empty-state icon="clipboard" title="Tidak ada data" message="Belum ada data permintaan pengadaan." :colspan="8" />
                    @endforelse
                </tbody>
            </x-ui.table>
        </x-ui.data-table>

    </div>
</div>
@endsection
