@extends('layouts.pos')
@section('title', 'Laporan Penjualan')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800 pb-10">
    <div class="w-full p-6 space-y-5">

        {{-- PAGE HEADER --}}
        <x-ui.page-header
            title="Laporan Penjualan"
            subtitle="Ringkasan data transaksi penjualan untuk Dine In, Catering, dan Nasi Box."
            :breadcrumbs="['Laporan', 'Penjualan']">
            <x-slot:actions>
                <div class="flex items-center gap-2">
                    <x-ui.button variant="secondary" icon="document-text" href="{{ route('laporan.penjualan.cetak-pdf', request()->all()) }}" target="_blank">
                        Export PDF
                    </x-ui.button>
                </div>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.alert />

        {{-- STAT CARDS --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-center">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Total Transaksi</span>
                <span class="text-2xl font-bold text-gray-900">{{ number_format($stats['totalTransaksi']) }}</span>
            </div>
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-center">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Total Pendapatan</span>
                <span class="text-2xl font-bold text-gray-900 tabular-nums">Rp {{ number_format($stats['totalPendapatan'], 0, ',', '.') }}</span>
            </div>
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-center">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Total Dine In</span>
                <span class="text-2xl font-bold text-gray-900">{{ number_format($stats['totalDineIn']) }}</span>
            </div>
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-center">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Total Catering</span>
                <span class="text-2xl font-bold text-gray-900">{{ number_format($stats['totalCatering']) }}</span>
            </div>
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-center">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Total Nasi Box</span>
                <span class="text-2xl font-bold text-gray-900">{{ number_format($stats['totalNasiBox']) }}</span>
            </div>
        </div>

        {{-- Table with integrated toolbar --}}
        <x-ui.data-table :paginator="$pesanans">
            <x-slot:toolbar>
                <form action="{{ route('laporan.penjualan') }}" method="GET" class="flex flex-col xl:flex-row items-start xl:items-center justify-between gap-4 w-full">
                    <div class="w-full xl:max-w-sm shrink-0">
                        <x-search-input name="search" value="{{ request('search') }}" placeholder="Cari Kode Pesanan, Pelanggan..." width="w-full" />
                    </div>
                    <div class="flex flex-wrap items-center gap-2 w-full xl:w-auto">
                        <x-ui.multi-select name="jenis" :options="['dinein' => 'Dine In', 'catering' => 'Catering', 'nasibox' => 'Nasi Box']" :selected="request('jenis', [])" label="Jenis" />
                        <x-ui.multi-select name="periode" :options="['hari_ini' => 'Hari Ini', 'minggu_ini' => 'Minggu Ini', 'bulan_ini' => 'Bulan Ini', 'custom' => 'Kustom']" :selected="request('periode', 'bulan_ini')" label="Periode" type="radio" />
                        @if(request('periode') == 'custom' || (is_array(request('periode')) && in_array('custom', request('periode'))))
                            <div class="flex items-center gap-2 shrink-0">
                                <input type="date" name="start_date" value="{{ $startDate }}" onchange="this.closest('form').submit()" class="text-sm border border-gray-200 rounded-xl px-3 py-2 outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 w-36 transition-colors">
                                <span class="text-gray-400">-</span>
                                <input type="date" name="end_date" value="{{ $endDate }}" onchange="this.closest('form').submit()" class="text-sm border border-gray-200 rounded-xl px-3 py-2 outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 w-36 transition-colors">
                            </div>
                        @endif
                        
                        <div class="flex items-center gap-2 shrink-0">
                            @if(request()->hasAny(['search', 'jenis']) || request('periode') != 'bulan_ini')
                                <a href="{{ route('laporan.penjualan') }}" class="text-sm font-medium text-rose-500 hover:text-rose-700 px-3 py-2 transition-colors">Reset Filter</a>
                            @endif
                        </div>
                    </div>
                </form>
            </x-slot:toolbar>

            <x-ui.table class="min-w-[950px]">
                <x-ui.table.header>
                    <th class="px-4 py-3.5 text-left w-12">No</th>
                    <th class="px-4 py-3.5 text-left">Tanggal Pesan</th>
                    <th class="px-4 py-3.5 text-left">Kode Pesanan</th>
                    <th class="px-4 py-3.5 text-left">Jenis</th>
                    <th class="px-4 py-3.5 text-left">Pelanggan</th>
                    <th class="px-4 py-3.5 text-right">Total</th>
                    <th class="px-4 py-3.5 text-center">Status Pembayaran</th>
                    <th class="px-4 py-3.5 text-right">Aksi</th>
                </x-ui.table.header>
                <tbody class="divide-y divide-gray-100">
                    @forelse($pesanans as $i => $p)
                    <x-ui.table.row>
                        <td class="px-4 py-4 align-middle text-sm text-gray-500 font-medium">
                            {{ $pesanans->firstItem() + $i }}
                        </td>
                        <td class="px-4 py-4 align-middle whitespace-nowrap text-sm text-gray-700">
                            {{ \Carbon\Carbon::parse($p->tanggal_pesanan)->translatedFormat('d M Y, H.i') }} WIB
                        </td>
                        <td class="px-4 py-4 align-middle">
                            <span class="font-mono font-bold text-gray-900 text-sm">{{ $p->id_pesanan }}</span>
                        </td>
                        <td class="px-4 py-4 align-middle">
                            @php
                                $jId = $p->jenis_pesanan_id;
                                $jColor = $jId == 1 ? 'gray' : ($jId == 2 ? 'warning' : 'primary');
                            @endphp
                            <x-ui.badge :color="$jColor" size="sm">{{ optional($p->jenis_pesanan)->nama_jenis ?? 'Unknown' }}</x-ui.badge>
                        </td>
                        <td class="px-4 py-4 align-middle">
                            <p class="font-semibold text-gray-900 text-sm whitespace-nowrap">
                                @if($p->jenis_pesanan_id == 1)
                                    Meja {{ optional($p->meja)->nomor_meja ?? '-' }}
                                @else
                                    {{ optional($p->pelanggan)->nama ?? 'Pelanggan' }}
                                @endif
                            </p>
                        </td>
                        <td class="px-4 py-4 align-middle text-right font-bold text-gray-900 tabular-nums whitespace-nowrap">
                            Rp {{ number_format($p->total_tagihan, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-4 align-middle text-center">
                            @php
                                $totalP = (float) $p->total_tagihan;
                                $dpP = (float) $p->pembayaran->where('status_verifikasi', 'diterima')->sum('jumlah_dibayar');
                                $lunasP = (float) $p->pembayaran->where('status_verifikasi', 'diterima')->sum('jumlah_dibayar');
                                $bayarP = $lunasP >= $totalP ? 'lunas' : ($dpP > 0 ? 'dp' : 'belum');
                                $bayarColor = $bayarP === 'lunas' ? 'success' : ($bayarP === 'dp' ? 'warning' : 'danger');
                                $bayarLabel = $bayarP === 'lunas' ? 'Lunas' : ($bayarP === 'dp' ? 'DP Terbayar' : 'Belum Bayar');
                            @endphp
                            <x-ui.badge :color="$bayarColor" size="sm" dot>{{ $bayarLabel }}</x-ui.badge>
                        </td>
                        <td class="px-4 py-4 align-middle text-right">
                            <div class="flex items-center justify-end gap-2">
                                <x-ui.action-button onclick="openDetailDrawer({{ $p->id }})" title="Detail">
                                    <x-heroicon-o-eye class="w-4 h-4" />
                                </x-ui.action-button>
                                <x-ui.action-button onclick="window.showToast('info', 'Fitur Cetak belum tersedia sepenuhnya')" title="Cetak Bukti Transaksi">
                                    <x-heroicon-o-printer class="w-4 h-4" />
                                </x-ui.action-button>
                            </div>
                        </td>
                    </x-ui.table.row>
                    @empty
                    <tr>
                        <td colspan="8">
                            <x-ui.empty-state icon="document-text" title="Belum ada data" message="Data akan muncul setelah transaksi tersedia." />
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </x-ui.table>
        </x-ui.data-table>

    </div>
</div>

{{-- DRAWER: DETAIL PENJUALAN --}}
<div id="drawerDetail" class="fixed inset-0 z-[100] hidden">
    <div id="drawerDetailOverlay" class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm opacity-0 transition-opacity duration-300" onclick="closeDetailDrawer()"></div>
    <div id="drawerDetailPanel" class="absolute right-0 top-0 h-full w-full max-w-md bg-white shadow-2xl flex flex-col translate-x-full transition-transform duration-300">
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

        fetch(`/laporan/penjualan/detail/${id}`)
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
