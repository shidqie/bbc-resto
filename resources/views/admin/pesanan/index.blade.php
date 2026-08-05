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
                    <x-select-input name="jenis" :options="$jenis_pesanan->pluck('nama_jenis', 'id')->toArray()" :selected="request('jenis')" placeholder="Semua Jenis" :auto-submit="true" />
                    <x-select-input name="status" :options="$status_pesanan->pluck('nama_status', 'id')->toArray()" :selected="request('status')" placeholder="Semua Status" :auto-submit="true" />
                    <button type="submit" class="text-sm font-medium bg-white border border-gray-200 text-gray-600 rounded-lg px-3 py-2 hover:bg-gray-50 transition-colors shrink-0">Cari</button>
                    @if(request()->hasAny(['search', 'jenis', 'status']))
                        <a href="{{ route('admin.pesanan.index') }}" class="text-xs font-medium text-red-500 hover:text-red-700 px-2 py-2 rounded-lg hover:bg-red-50 transition-colors shrink-0">Reset</a>
                    @endif
                </form>
            </x-slot:toolbar>

            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        <th class="px-4 py-3 text-left w-12">No</th>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-left">ID Pesanan</th>
                        <th class="px-4 py-3 text-left">Jenis</th>
                        <th class="px-4 py-3 text-left">Pelanggan</th>
                        <th class="px-4 py-3 text-left">Meja</th>
                        <th class="px-4 py-3 text-right">Total</th>
                        <th class="px-4 py-3 text-center">Status Pesanan</th>
                        <th class="px-4 py-3 text-center">Pembayaran</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($pesanans as $index => $pesanan)
                    <tr class="hover:bg-gray-50/60 transition-colors group">
                        <td class="px-4 py-3 text-sm text-gray-500 font-medium">
                            {{ ($pesanans->firstItem() ?? 1) + $index }}
                        </td>
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-900 text-sm">{{ \Carbon\Carbon::parse($pesanan->dibuat_pada)->format('d/m/Y') }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ \Carbon\Carbon::parse($pesanan->dibuat_pada)->format('H:i') }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <span class="font-mono text-xs font-bold text-gray-900">{{ $pesanan->nomor_pesanan ?? 'DIN-'.$pesanan->id }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">
                            {{ optional($pesanan->jenis_pesanan)->nama_jenis ?? 'Dine In' }}
                        </td>
                        <td class="px-4 py-3">
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
                            @endphp
                            <p class="font-medium text-gray-900 text-sm">{{ $nama }}</p>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">
                            {{ optional($pesanan->meja)->nomor_meja ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-gray-900">
                            Rp{{ number_format($pesanan->total_tagihan, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-center">
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
                        <td class="px-4 py-3 text-center">
                            @php
                                $totalBayar = $pesanan->pembayaran->sum('jumlah_bayar');
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
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <button type="button" onclick="openDetailDrawer({{ $pesanan->id }})" title="Detail" class="w-7 h-7 rounded-full flex items-center justify-center bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors">
                                    <x-heroicon-o-eye class="w-3 h-3" />
                                </button>
                                <button type="button" onclick="window.open('/pos/dinein/pesanan/{{ $pesanan->id }}/print-nota', '_blank')" title="Cetak Struk" class="w-7 h-7 rounded-full flex items-center justify-center bg-gray-50 text-gray-600 hover:bg-gray-100 transition-colors">
                                    <x-heroicon-o-printer class="w-3 h-3" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <x-empty-state icon="document-text" title="Belum ada pesanan" message="Belum ada pesanan yang sesuai kriteria pencarian." :colspan="10" />
                    @endforelse
                </tbody>
            </table>
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
