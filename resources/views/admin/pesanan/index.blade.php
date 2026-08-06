@extends('layouts.pos')

@section('title', 'Semua Pesanan')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800">
    <div class="w-full p-6 space-y-5">

        {{-- PAGE HEADER --}}
        <x-ui.page-header
            title="Semua Pesanan"
            subtitle="Daftar seluruh pesanan Dine In, Katering, dan Nasi Box."
            :breadcrumbs="['Penjualan', 'Semua Pesanan']">
        </x-ui.page-header>

        <x-ui.alert />

        {{-- Table with integrated toolbar --}}
        <x-ui.data-table :paginator="$pesanans">
            <x-slot:toolbar>
                <form action="{{ route('admin.pesanan.index') }}" method="GET" class="flex items-center gap-2 w-full flex-wrap">
                    <x-search-input name="search" value="{{ request('search') }}" placeholder="Cari No. Pesanan / Nama Pemesan…" />
                    <x-ui.multi-select name="jenis" :options="$jenis_pesanan->pluck('nama_jenis', 'id')->toArray()" :selected="request('jenis')" label="Jenis" type="radio" />
                    
                    <x-ui.multi-select name="periode" :options="['hari_ini' => 'Hari Ini', 'minggu_ini' => 'Minggu Ini', 'bulan_ini' => 'Bulan Ini', 'kustom' => 'Kustom']" :selected="request('periode')" label="Pilih periode" type="radio" />
                    
                    <template x-if="new URLSearchParams(window.location.search).get('periode') === 'kustom'">
                        <div class="flex items-center gap-2">
                            <input type="date" name="start_date" value="{{ request('start_date') }}" class="text-sm border-gray-300 rounded-lg shadow-sm focus:border-[#0D3024] focus:ring-[#0D3024]">
                            <span class="text-gray-500 text-sm">s/d</span>
                            <input type="date" name="end_date" value="{{ request('end_date') }}" class="text-sm border-gray-300 rounded-lg shadow-sm focus:border-[#0D3024] focus:ring-[#0D3024]">
                            <button type="submit" class="px-3 py-1.5 bg-[#0D3024] text-white text-sm font-medium rounded-lg">Terapkan</button>
                        </div>
                    </template>

                    @if(request()->hasAny(['search', 'jenis', 'periode', 'start_date', 'end_date']))
                        <a href="{{ route('admin.pesanan.index') }}" class="text-xs font-medium text-red-500 hover:text-red-700 px-2 py-2 rounded-lg hover:bg-red-50 transition-colors shrink-0">Reset</a>
                    @endif
                </form>
            </x-slot:toolbar>

            <x-ui.table class="min-w-[1000px]">
                <x-ui.table.header>
                    <th class="px-4 py-3.5 text-left w-12">No</th>
                    <th class="px-4 py-3.5 text-left">Tanggal Pesan</th>
                    <th class="px-4 py-3.5 text-left">Kode Pesanan</th>
                    <th class="px-4 py-3.5 text-left">Jenis</th>
                    <th class="px-4 py-3.5 text-left">Konsumen</th>
                    <th class="px-4 py-3.5 text-left">Meja</th>
                    <th class="px-4 py-3.5 text-right">Total</th>
                    <th class="px-4 py-3.5 text-center">Status Pesanan</th>
                    <th class="px-4 py-3.5 text-center">Pembayaran</th>
                    <th class="px-4 py-3.5 text-center">Aksi</th>
                </x-ui.table.header>
                <tbody class="divide-y divide-gray-100">
                    @forelse($pesanans as $index => $pesanan)
                    <x-ui.table.row>
                        <td class="px-4 py-4 text-sm text-gray-500 font-medium">
                            {{ ($pesanans->firstItem() ?? 1) + $index }}
                        </td>
                        <td class="px-4 py-4 text-sm text-gray-700">
                            {{ \Carbon\Carbon::parse($pesanan->dibuat_pada)->translatedFormat('d M Y, H.i') }} WIB
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-xs font-bold text-gray-900">{{ $pesanan->nomor_pesanan ?? 'DIN-'.$pesanan->id }}</span>
                                @if(\Carbon\Carbon::parse($pesanan->dibuat_pada)->diffInMinutes(now()) < 15)
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-black bg-red-500 text-white animate-pulse">BARU</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-4 text-sm text-gray-600">
                            {{ optional($pesanan->jenis_pesanan)->nama_jenis ?? 'Dine In' }}
                        </td>
                        <td class="px-4 py-4">
                            @php
                                $nama = 'Tamu';
                                if ($pesanan->pelanggan) {
                                    $nama = $pesanan->pelanggan->nama;
                                } elseif (!empty($pesanan->catatan)) {
                                    if (preg_match('/^Pemesan:\s*(.+)$/m', $pesanan->catatan, $m)) {
                                        $nama = trim($m[1]);
                                    } elseif (preg_match('/Self-Order QR \(([^)]+)\)/', $pesanan->catatan, $m)) {
                                        $nama = trim($m[1]);
                                    } elseif (preg_match('/^(.+?)\s*\(\d+\s*tamu\)/', $pesanan->catatan, $m)) {
                                        $nama = trim($m[1]);
                                    } else {
                                        $nama = trim(explode('|', $pesanan->catatan)[0]);
                                    }
                                }
                                // Remove phone number if it was appended with a dash
                                $nama = trim(explode('–', $nama)[0]);
                                $nama = trim(explode('-', $nama)[0]);
                            @endphp
                            <p class="font-medium text-gray-900 text-sm">{{ $nama }}</p>
                        </td>
                        <td class="px-4 py-4 text-sm text-gray-600">
                            {{ optional($pesanan->meja)->nomor_meja ?? '-' }}
                        </td>
                        <td class="px-4 py-4 text-right font-bold text-gray-900">
                            Rp{{ number_format($pesanan->total_tagihan, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-4 text-center">
                            @php
                                $statusColor = 'gray';
                                if($pesanan->status_pesanan_id == 5) $statusColor = 'success';
                                elseif($pesanan->status_pesanan_id == 1) $statusColor = 'warning';
                                elseif($pesanan->status_pesanan_id == 6) $statusColor = 'danger';
                                else $statusColor = 'primary';
                            @endphp
                            <x-ui.badge :color="$statusColor" size="sm">
                                {{ optional($pesanan->status_pesanan)->nama_status ?? 'Unknown' }}
                            </x-ui.badge>
                        </td>
                        <td class="px-4 py-4 text-center">
                            @php
                                $totalBayar = $pesanan->pembayaran->sum('jumlah_dibayar');
                                if($totalBayar >= $pesanan->total_tagihan && $pesanan->total_tagihan > 0) {
                                    $payStatus = 'Lunas';
                                    $payColor = 'success';
                                } elseif($totalBayar > 0) {
                                    $payStatus = 'DP';
                                    $payColor = 'warning';
                                } else {
                                    $payStatus = 'Belum Lunas';
                                    $payColor = 'danger';
                                }
                            @endphp
                            <x-ui.badge :color="$payColor" size="sm">
                                {{ $payStatus }}
                            </x-ui.badge>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <x-ui.action-button onclick="openDetailDrawer({{ $pesanan->id }})" title="Detail">
                                    <x-heroicon-o-eye class="w-4 h-4" />
                                </x-ui.action-button>
                                <x-ui.action-button onclick="window.open('/pos/dinein/pesanan/{{ $pesanan->id }}/print-nota', '_blank')" title="Cetak Struk">
                                    <x-heroicon-o-printer class="w-4 h-4" />
                                </x-ui.action-button>
                            </div>
                        </td>
                    </x-ui.table.row>
                    @empty
                    <x-empty-state icon="document-text" title="Belum ada pesanan" message="Belum ada pesanan yang sesuai kriteria pencarian." :colspan="10" />
                    @endforelse
                </tbody>
            </x-ui.table>
        </x-ui.data-table>

    </div>
</div>

{{-- DRAWER: DETAIL PESANAN (SLIDE-IN RIGHT) --}}
<div id="drawerDetail" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm opacity-0 transition-opacity duration-300" id="drawerDetailOverlay" onclick="closeDetailDrawer()"></div>
    <div class="absolute right-0 top-0 h-full w-full max-w-md bg-white shadow-2xl flex flex-col translate-x-full transition-transform duration-300" id="drawerDetailPanel">
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

        fetch(`/admin/pesanan/detail/${id}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.text())
        .then(html => {
            content.innerHTML = html;
        })
        .catch(err => {
            content.innerHTML = '<div class="p-6 text-center text-red-500">Gagal memuat detail pesanan.</div>';
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
