@extends('layouts.pos')
@section('title', 'Laporan Penjualan')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800 pb-10">
    <div class="w-full p-6 space-y-5">

        {{-- PAGE HEADER --}}
        <x-ui.page-header
            title="Laporan Penjualan"
            subtitle="Rekap transaksi penjualan berdasarkan periode dan jenis pesanan."
            :breadcrumbs="['Laporan', 'Penjualan']">
        </x-ui.page-header>

        <x-ui.alert />

        {{-- KPI CARDS (3 KARTU SKRIPSI PROMT.MD) --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-center">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Total Pendapatan</span>
                <span class="text-2xl font-black text-emerald-700 tabular-nums">Rp {{ number_format($stats['totalPendapatan'], 0, ',', '.') }}</span>
            </div>
            <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-center">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Jumlah Transaksi</span>
                <span class="text-2xl font-black text-gray-900">{{ number_format($stats['totalTransaksi']) }} Pesanan</span>
            </div>
            <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-center">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Rata-rata Transaksi</span>
                <span class="text-2xl font-black text-gray-900 tabular-nums">Rp {{ number_format($stats['rataRataTransaksi'], 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- Table with integrated toolbar --}}
        <x-ui.data-table :paginator="$pesanans">
            <x-slot:toolbar>
                <form action="{{ route('laporan.penjualan') }}" method="GET" class="flex flex-col xl:flex-row items-start xl:items-center justify-between gap-4 w-full">
                    <div class="flex flex-wrap items-center gap-2 w-full xl:w-auto">
                        <div class="w-full sm:w-auto shrink-0">
                            <x-search-input name="search" value="{{ request('search') }}" placeholder="Cari ID Pesanan..." width="w-full" />
                        </div>
                        <x-ui.multi-select name="jenis" :options="['dinein' => 'Dine-In', 'catering' => 'Katering', 'nasibox' => 'Nasi Box']" :selected="request('jenis', [])" label="Jenis Pesanan" />
                        <x-ui.multi-select name="periode" :options="['hari_ini' => 'Hari Ini', 'minggu_ini' => 'Minggu Ini', 'bulan_ini' => 'Bulan Ini', 'custom' => 'Custom Date']" :selected="request('periode', 'bulan_ini')" label="Periode" type="radio" />
                        
                        @if(request('periode') == 'custom' || (is_array(request('periode')) && in_array('custom', request('periode'))))
                            <div class="flex items-center gap-2 shrink-0">
                                <input type="date" name="start_date" value="{{ $startDate }}" onchange="this.closest('form').submit()" class="text-sm border border-gray-200 rounded-xl px-3 py-2 outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 w-36 transition-colors">
                                <span class="text-gray-400">-</span>
                                <input type="date" name="end_date" value="{{ $endDate }}" onchange="this.closest('form').submit()" class="text-sm border border-gray-200 rounded-xl px-3 py-2 outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 w-36 transition-colors">
                            </div>
                        @endif
                        
                        <div class="flex items-center gap-2 shrink-0">
                            <button type="submit" class="inline-flex items-center gap-1.5 text-xs font-bold text-white bg-emerald-600 rounded-xl px-3.5 py-2.5 hover:bg-emerald-700 transition-colors shadow-2xs">
                                <x-heroicon-o-funnel class="w-4 h-4" />
                                Terapkan Filter
                            </button>
                            @if(request()->hasAny(['search', 'jenis']) || request('periode') != 'bulan_ini')
                                <a href="{{ route('laporan.penjualan') }}" class="text-xs font-semibold text-rose-600 hover:text-rose-700 px-2 py-2 transition-colors">Reset</a>
                            @endif
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-2 shrink-0">
                        <x-ui.button variant="secondary" icon="document-text" href="{{ route('laporan.penjualan.cetak-pdf', array_merge(request()->query(), ['periode' => $periode, 'start_date' => $startDate, 'end_date' => $endDate])) }}" target="_blank" size="sm" onclick="this.href=buildExportReportUrl('{{ route('laporan.penjualan.cetak-pdf') }}', this)">
                            Export PDF
                        </x-ui.button>
                    </div>
                </form>
            </x-slot:toolbar>

            <x-ui.table class="min-w-[950px]">
                <x-ui.table.header>
                    <th class="px-4 py-3.5 text-left w-12">No</th>
                    <th class="px-4 py-3.5 text-left">Tanggal</th>
                    <th class="px-4 py-3.5 text-left">ID Pesanan</th>
                    <th class="px-4 py-3.5 text-left">Jenis Pesanan</th>
                    <th class="px-4 py-3.5 text-right">Total Transaksi</th>
                    <th class="px-4 py-3.5 text-center">Status</th>
                    <th class="px-4 py-3.5 text-right">Aksi</th>
                </x-ui.table.header>
                <tbody class="divide-y divide-gray-100">
                    @forelse($pesanans as $i => $p)
                    <x-ui.table.row>
                        <td class="px-4 py-4 align-middle text-sm text-gray-500 font-medium">
                            {{ $pesanans->firstItem() + $i }}
                        </td>
                        <td class="px-4 py-4 align-middle whitespace-nowrap text-sm text-gray-700 font-medium">
                            {{ \Carbon\Carbon::parse($p->tanggal_pesanan)->translatedFormat('d F Y') }}
                        </td>
                        <td class="px-4 py-4 align-middle">
                            <span class="font-mono font-bold text-gray-900 text-sm">{{ $p->id_pesanan }}</span>
                        </td>
                        <td class="px-4 py-4 align-middle">
                            @php
                                $jId = $p->jenis_pesanan_id;
                                $jNama = $jId == 1 ? 'Dine-In' : ($jId == 2 ? 'Katering' : 'Nasi Box');
                                $jColor = $jId == 1 ? 'gray' : ($jId == 2 ? 'warning' : 'primary');
                            @endphp
                            <x-ui.badge :color="$jColor" size="sm">{{ $jNama }}</x-ui.badge>
                        </td>
                        <td class="px-4 py-4 align-middle text-right font-bold text-gray-900 tabular-nums whitespace-nowrap">
                            Rp {{ number_format($p->total_tagihan, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-4 align-middle text-center">
                            <x-ui.badge color="success" size="sm">Selesai</x-ui.badge>
                        <td class="px-4 py-4 align-middle text-right">
                            <x-ui.action-button onclick="openDetailDrawer({{ $p->id }})" title="Detail" label="Detail">
                                <x-heroicon-o-eye class="w-3.5 h-3.5" />
                            </x-ui.action-button>
                        </td>
                    </x-ui.table.row>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-gray-500 font-medium">
                            Belum ada transaksi penjualan pada periode yang dipilih.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($pesanans->count() > 0)
                <tfoot>
                    <tr class="bg-gray-50/80 border-t border-gray-200 font-bold text-sm text-gray-900">
                        <td colspan="4" class="px-4 py-3 text-right">Total Pendapatan:</td>
                        <td class="px-4 py-3 text-right tabular-nums text-emerald-700 font-black">
                            Rp {{ number_format($stats['totalPendapatan'], 0, ',', '.') }}
                        </td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
                @endif
            </x-ui.table>
        </x-ui.data-table>

    </div>
</div>

{{-- DRAWER/MODAL: DETAIL PENJUALAN --}}
<div id="drawerDetail" class="fixed inset-0 z-[100] hidden">
    <div id="drawerDetailOverlay" class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm opacity-0 transition-opacity duration-300" onclick="closeDetailDrawer()"></div>
    <div id="drawerDetailPanel" class="absolute right-0 top-0 h-full w-full max-w-lg bg-white shadow-2xl flex flex-col translate-x-full transition-transform duration-300">
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
                content.innerHTML = '<div class="p-6 text-center text-red-500 font-semibold">Gagal memuat detail laporan.</div>';
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
    function buildExportReportUrl(baseRoute, btnEl) {
        const form = btnEl.closest('form');
        if (!form) return btnEl.href;
        const formData = new FormData(form);
        const params = new URLSearchParams();
        for (const [key, val] of formData.entries()) {
            if (val !== null && val !== '') {
                params.append(key, val);
            }
        }
        return baseRoute + (baseRoute.includes('?') ? '&' : '?') + params.toString();
    }
</script>
@endsection
