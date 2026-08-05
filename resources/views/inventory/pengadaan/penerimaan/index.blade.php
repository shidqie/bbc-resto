@extends('layouts.pos')
@section('title', 'Penerimaan Bahan')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800 pb-10">
    <div class="w-full p-6 space-y-5">

        {{-- PAGE HEADER --}}
        <x-ui.page-header
            title="Penerimaan Bahan"
            subtitle="Catat bahan baku yang telah diterima dari supplier sehingga stok bertambah secara otomatis."
            :breadcrumbs="['Pengadaan', 'Penerimaan Bahan']">
            <x-slot:actions>
                <button type="button" onclick="window.showToast('info', 'Form pencatatan penerimaan sedang dikembangkan')" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-gray-900 rounded-lg px-3 py-2 hover:bg-gray-800 transition-colors">
                    <x-heroicon-o-plus class="w-4 h-4" />
                    Catat Penerimaan
                </button>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.alert />

        {{-- Table with integrated toolbar --}}
        <x-ui.data-table :paginator="$penerimaans">
            <x-slot:toolbar>
                <form action="{{ route('pengadaan.penerimaan.index') }}" method="GET" class="flex items-center gap-2 w-full flex-wrap">
                    <x-search-input name="search" value="{{ request('search') }}" placeholder="Cari nomor penerimaan / permintaan..." />
                    
                    <x-select-input name="jenis" :options="['harian' => 'Harian', 'catering' => 'Catering']" :selected="request('jenis')" placeholder="Jenis Permintaan" :auto-submit="true" />
                    
                    <x-select-input name="status" :options="$statuses->pluck('nama_status', 'id')->toArray()" :selected="request('status')" placeholder="Semua Status" :auto-submit="true" />
                    
                    <x-select-input name="periode" :options="['hari_ini' => 'Hari Ini', 'minggu_ini' => 'Minggu Ini', 'bulan_ini' => 'Bulan Ini']" :selected="request('periode')" placeholder="Semua Periode" :auto-submit="true" />
                    
                    <button type="submit" class="text-sm font-medium bg-white border border-gray-200 text-gray-600 rounded-lg px-3 py-2 hover:bg-gray-50 transition-colors shrink-0">Cari</button>
                    @if(request()->hasAny(['search', 'jenis', 'status', 'periode']))
                        <a href="{{ route('pengadaan.penerimaan.index') }}" class="text-xs font-medium text-red-500 hover:text-red-700 px-2 py-2 rounded-lg hover:bg-red-50 transition-colors shrink-0">Reset Filter</a>
                    @endif
                </form>
            </x-slot:toolbar>

            <table class="w-full text-sm min-w-[900px]">
                <thead>
                    <tr class="border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        <th class="px-4 py-3 text-left w-12">No</th>
                        <th class="px-4 py-3 text-left">No. Penerimaan</th>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-left">No. Permintaan</th>
                        <th class="px-4 py-3 text-left">Jenis</th>
                        <th class="px-4 py-3 text-left">Supplier</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($penerimaans as $i => $p)
                    <tr class="hover:bg-gray-50/60 transition-colors group">
                        <td class="px-4 py-3 text-sm text-gray-500 font-medium align-middle">
                            {{ $penerimaans->firstItem() + $i }}
                        </td>
                        <td class="px-4 py-3 align-middle">
                            <span class="font-mono font-bold text-gray-900 text-xs">{{ $p->nomor_penerimaan }}</span>
                        </td>
                        <td class="px-4 py-3 align-middle">
                            <p class="font-medium text-gray-900 text-sm">{{ \Carbon\Carbon::parse($p->diterima_pada)->format('d M Y') }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ \Carbon\Carbon::parse($p->diterima_pada)->format('H:i') }}</p>
                        </td>
                        <td class="px-4 py-3 align-middle">
                            <span class="font-mono text-gray-600 text-xs">{{ optional($p->pengadaan_bahan)->nomor_pengadaan ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-3 align-middle">
                            @if(optional($p->pengadaan_bahan)->jenis_pengadaan == 'harian')
                                <x-ui.badge color="primary" size="sm">Harian</x-ui.badge>
                            @elseif(optional($p->pengadaan_bahan)->jenis_pengadaan == 'catering')
                                <x-ui.badge color="warning" size="sm">Catering</x-ui.badge>
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-4 py-3 align-middle text-sm text-gray-700">
                            {{ optional(optional($p->pengadaan_bahan)->pemasok)->nama_pemasok ?? '-' }}
                        </td>
                        <td class="px-4 py-3 align-middle text-center">
                            @php
                                $sId = optional($p->pengadaan_bahan)->status_pengadaan_id;
                                $sColor = 'gray';
                                if($sId == 3) $sColor = 'primary'; // Diterima Sebagian
                                elseif($sId == 4) $sColor = 'success'; // Selesai / Diterima Lengkap
                            @endphp
                            <x-ui.badge :color="$sColor" size="sm">{{ optional(optional($p->pengadaan_bahan)->status_pengadaan)->nama_status ?? 'Unknown' }}</x-ui.badge>
                        </td>
                        <td class="px-4 py-3 align-middle text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <button type="button" onclick="window.showToast('info', 'Detail penerimaan belum tersedia')" title="Detail" class="w-7 h-7 rounded-full flex items-center justify-center bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors">
                                    <x-heroicon-o-eye class="w-3 h-3" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <x-empty-state icon="truck" title="Belum ada penerimaan" message="Tidak ada catatan penerimaan bahan baku yang sesuai kriteria pencarian." :colspan="8" />
                    @endforelse
                </tbody>
            </table>
        </x-ui.data-table>

    </div>
</div>
@endsection
