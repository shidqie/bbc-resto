@extends('layouts.pos')
@section('title', 'Laporan Pengadaan')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800 pb-10">
    <div class="w-full p-6 space-y-5">

        {{-- PAGE HEADER --}}
        <x-ui.page-header
            title="Laporan Pengadaan"
            subtitle="Ringkasan dan detail permintaan serta penerimaan bahan baku."
            :breadcrumbs="['Laporan', 'Pengadaan']">
            <x-slot:actions>
                <div class="flex items-center gap-2">
                    <a href="{{ route('laporan.pengadaan.cetak-pdf', request()->all()) }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-gray-700 bg-white border border-gray-200 rounded-lg px-3 py-2 hover:bg-gray-50 transition-colors">
                        <x-heroicon-o-document-text class="w-4 h-4 text-rose-500" />
                        Export PDF
                    </a>
                    <a href="{{ route('laporan.pengadaan.cetak-excel', request()->all()) }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-gray-700 bg-white border border-gray-200 rounded-lg px-3 py-2 hover:bg-gray-50 transition-colors">
                        <x-heroicon-o-table-cells class="w-4 h-4 text-emerald-500" />
                        Export Excel
                    </a>
                </div>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.alert />

        {{-- STAT CARDS --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-center">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Total Permintaan</span>
                <span class="text-2xl font-bold text-gray-900">{{ number_format($stats['totalPermintaan']) }}</span>
            </div>
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-center">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Permintaan Harian</span>
                <span class="text-2xl font-bold text-gray-900">{{ number_format($stats['totalHarian']) }}</span>
            </div>
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-center">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Permintaan Catering</span>
                <span class="text-2xl font-bold text-gray-900">{{ number_format($stats['totalCatering']) }}</span>
            </div>
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-center">
                <span class="text-xs font-semibold text-emerald-600 uppercase tracking-wider mb-1">Penerimaan Selesai</span>
                <span class="text-2xl font-bold text-gray-900">{{ number_format($stats['totalPenerimaan']) }}</span>
            </div>
        </div>

        {{-- Table with integrated toolbar --}}
        <x-ui.data-table :paginator="$pengadaans">
            <x-slot:toolbar>
                <form action="{{ route('laporan.pengadaan') }}" method="GET" class="flex flex-col sm:flex-row items-start sm:items-center gap-2 w-full flex-wrap">
                    <x-search-input name="search" value="{{ request('search') }}" placeholder="Cari Nomor Permintaan..." />
                    
                    <x-select-input name="jenis_permintaan" :options="['harian' => 'Harian', 'catering' => 'Catering']" :selected="request('jenis_permintaan')" placeholder="Semua Jenis" :auto-submit="true" />
                    
                    {{-- Kita hardcode status ID untuk simpel, atau bisa lempar dari controller. 
                         Sesuai seeder: 1=Menunggu, 2=Dipesan, 3=Diterima Sebagian, 4=Selesai, 5=Dibatalkan --}}
                    <x-select-input name="status" :options="['1' => 'Menunggu Pembelian', '2' => 'Telah Dipesan', '3' => 'Diterima Sebagian', '4' => 'Selesai', '5' => 'Dibatalkan']" :selected="request('status')" placeholder="Semua Status" :auto-submit="true" />
                    
                    <x-select-input name="periode" :options="['hari_ini' => 'Hari Ini', 'minggu_ini' => 'Minggu Ini', 'bulan_ini' => 'Bulan Ini', 'custom' => 'Kustom Rentang']" :selected="request('periode', 'bulan_ini')" placeholder="Semua Periode" :auto-submit="true" />
                    
                    @if(request('periode') == 'custom')
                        <div class="flex items-center gap-2">
                            <input type="date" name="start_date" value="{{ $startDate }}" class="text-sm border border-gray-200 rounded-lg px-3 py-2 outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                            <span class="text-gray-400">-</span>
                            <input type="date" name="end_date" value="{{ $endDate }}" class="text-sm border border-gray-200 rounded-lg px-3 py-2 outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                    @endif
                    
                    <button type="submit" class="text-sm font-medium bg-white border border-gray-200 text-gray-600 rounded-lg px-3 py-2 hover:bg-gray-50 transition-colors shrink-0">Cari</button>
                    
                    @if(request()->hasAny(['search', 'jenis_permintaan', 'status']) || request('periode') != 'bulan_ini')
                        <a href="{{ route('laporan.pengadaan') }}" class="text-xs font-medium text-red-500 hover:text-red-700 px-2 py-2 rounded-lg hover:bg-red-50 transition-colors shrink-0">Reset Filter</a>
                    @endif
                </form>
            </x-slot:toolbar>

            <table class="w-full text-sm min-w-[900px]">
                <thead>
                    <tr class="border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        <th class="px-4 py-3 text-left w-12">No</th>
                        <th class="px-4 py-3 text-left">Nomor Permintaan</th>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-left">Jenis</th>
                        <th class="px-4 py-3 text-left">Supplier</th>
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
                            <span class="font-mono font-bold text-gray-900 text-sm">{{ $p->nomor_pengadaan }}</span>
                        </td>
                        <td class="px-4 py-3 align-middle whitespace-nowrap">
                            <p class="font-medium text-gray-900 text-sm">{{ \Carbon\Carbon::parse($p->tanggal_pengadaan)->format('d M Y') }}</p>
                        </td>
                        <td class="px-4 py-3 align-middle">
                            @php
                                $jColor = $p->jenis_pengadaan == 'harian' ? 'primary' : 'warning';
                            @endphp
                            <x-ui.badge :color="$jColor" size="sm">{{ ucfirst($p->jenis_pengadaan) }}</x-ui.badge>
                        </td>
                        <td class="px-4 py-3 align-middle">
                            <p class="font-medium text-gray-900 text-sm">{{ optional($p->pemasok)->nama_pemasok ?? '-' }}</p>
                        </td>
                        <td class="px-4 py-3 align-middle text-center">
                            @php
                                $sId = $p->status_pengadaan_id;
                                $sColor = 'gray';
                                if($sId == 1) $sColor = 'warning'; // Menunggu
                                elseif($sId == 2) $sColor = 'primary'; // Dipesan
                                elseif($sId == 3) $sColor = 'primary'; // Diterima Sebagian
                                elseif($sId == 4) $sColor = 'success'; // Selesai
                                elseif($sId == 5) $sColor = 'danger'; // Dibatalkan
                            @endphp
                            <x-ui.badge :color="$sColor" size="sm">{{ optional($p->status_pengadaan)->nama_status ?? 'Unknown' }}</x-ui.badge>
                        </td>
                        <td class="px-4 py-3 align-middle text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <button type="button" onclick="openDetailDrawer({{ $p->id }})" title="Detail" class="w-7 h-7 rounded-full flex items-center justify-center bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors">
                                    <x-heroicon-o-eye class="w-3 h-3" />
                                </button>
                                <button type="button" onclick="window.showToast('info', 'Fitur Cetak belum tersedia sepenuhnya')" title="Cetak" class="w-7 h-7 rounded-full flex items-center justify-center bg-gray-50 text-gray-600 hover:bg-gray-100 transition-colors">
                                    <x-heroicon-o-printer class="w-3 h-3" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <x-empty-state icon="document-text" title="Tidak ada data pengadaan" message="Belum ada data permintaan pengadaan pada kriteria ini." :colspan="7" />
                    @endforelse
                </tbody>
            </table>
        </x-ui.data-table>

    </div>
</div>

{{-- DRAWER: DETAIL PENGADAAN --}}
<div id="drawerDetail" class="fixed inset-0 z-[100] hidden">
    <div id="drawerDetailOverlay" class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm opacity-0 transition-opacity duration-300" onclick="closeDetailDrawer()"></div>
    <div id="drawerDetailPanel" class="absolute right-0 top-0 h-full w-full max-w-xl bg-white shadow-2xl flex flex-col translate-x-full transition-transform duration-300">
        <div id="drawerDetailContent" class="flex-1 overflow-y-auto">
            <div class="flex items-center justify-center h-full">
                <svg class="animate-spin h-8 w-8 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<script>
    function openDetailDrawer(id) {
        const drawer = document.getElementById('drawerDetail');
        const overlay = document.getElementById('drawerDetailOverlay');
        const panel = document.getElementById('drawerDetailPanel');
        const content = document.getElementById('drawerDetailContent');

        content.innerHTML = `
            <div class="flex items-center justify-center h-full">
                <svg class="animate-spin h-8 w-8 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        `;

        drawer.classList.remove('hidden');
        drawer.style.display = 'block';

        requestAnimationFrame(() => {
            panel.classList.remove('translate-x-full');
            panel.classList.add('translate-x-0');
            overlay.classList.remove('opacity-0');
            overlay.classList.add('opacity-100');
        });

        fetch(`/laporan/pengadaan/detail/${id}`)
            .then(res => res.text())
            .then(html => { content.innerHTML = html; })
            .catch(err => {
                content.innerHTML = '<div class="p-6 text-center text-red-500">Gagal memuat detail laporan.</div>';
            });
    }

    function closeDetailDrawer() {
        const drawer = document.getElementById('drawerDetail');
        const overlay = document.getElementById('drawerDetailOverlay');
        const panel = document.getElementById('drawerDetailPanel');

        panel.classList.remove('translate-x-0');
        panel.classList.add('translate-x-full');
        overlay.classList.remove('opacity-100');
        overlay.classList.add('opacity-0');

        setTimeout(() => {
            drawer.classList.add('hidden');
            drawer.style.display = '';
        }, 300);
    }
</script>
@endsection
