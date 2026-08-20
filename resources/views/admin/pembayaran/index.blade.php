@extends('layouts.pos')

@section('title', 'Data Pembayaran')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800">
    <div class="w-full p-6 space-y-5">

        {{-- PAGE HEADER --}}
        <x-ui.page-header
            title="Data Pembayaran"
            subtitle="Daftar riwayat pembayaran dari semua jenis pesanan."
            :breadcrumbs="['Penjualan', 'Pembayaran']">
        </x-ui.page-header>

        <x-ui.alert />

        {{-- Table with integrated toolbar --}}
        <x-ui.data-table :paginator="$pembayarans">
            <x-slot:toolbar>
                <form action="{{ route('admin.pembayaran.index') }}" method="GET" class="flex items-center gap-2 w-full flex-wrap">
                    <x-search-input name="search" value="{{ request('search') }}" placeholder="Cari ID Pembayaran / Kode Pesanan…" />
                    @if(request()->hasAny(['search']))
                        <x-ui.button href="{{ route('admin.pembayaran.index') }}" variant="danger" size="sm">Reset</x-ui.button>
                    @endif
                </form>
            </x-slot:toolbar>

            <x-ui.table class="min-w-[1000px]">
                <x-ui.table.header>
                    <th class="px-4 py-3.5 text-left w-12">No</th>
                    <th class="px-4 py-3.5 text-left">Kode Pesanan</th>
                    <th class="px-4 py-3.5 text-left">Tanggal Pesan</th>
                    <th class="px-4 py-3.5 text-left">Tanggal Acara</th>
                    <th class="px-4 py-3.5 text-right">Total</th>
                    <th class="px-4 py-3.5 text-center">Bukti Pembayaran</th>
                    <th class="px-4 py-3.5 text-center">Status</th>
                    <th class="px-4 py-3.5 text-center">Aksi</th>
                </x-ui.table.header>
                <tbody class="divide-y divide-gray-100">
                    @forelse($pembayarans as $index => $bayar)
                    <x-ui.table.row>
                        <td class="px-4 py-4 text-sm text-gray-500 font-medium">
                            {{ ($pembayarans->firstItem() ?? 1) + $index }}
                        </td>
                        <td class="px-4 py-4">
                            <span class="font-mono text-xs font-bold text-gray-900">{{ optional($bayar->pesanan)->id_pesanan ?? 'DIN-'.optional($bayar->pesanan)->id ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-4 text-sm text-gray-700">
                            {{ \Carbon\Carbon::parse($bayar->dibuat_pada)->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-4 py-4 text-sm text-gray-700">
                            {{ optional(optional($bayar->pesanan)->jadwal_pesanan)->tanggal_acara ? \Carbon\Carbon::parse(optional(optional($bayar->pesanan)->jadwal_pesanan)->tanggal_acara)->format('d/m/Y H:i') : '-' }}
                        </td>
                        <td class="px-4 py-4 text-right font-bold text-gray-900">
                            Rp{{ number_format($bayar->jumlah_bayar, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-4 text-center">
                            @if($bayar->bukti_pembayaran)
                                <a href="{{ Storage::url($bayar->bukti_pembayaran) }}" target="_blank" class="text-primary hover:underline text-xs font-medium">Lihat Bukti</a>
                            @else
                                <span class="text-gray-400 text-xs">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-center">
                            @php
                                $statusVerifikasi = $bayar->status_verifikasi;
                                $payColor = 'gray';
                                $statusText = 'Menunggu';
                                if($statusVerifikasi == 'diterima') { $payColor = 'success'; $statusText = 'Diterima'; }
                                elseif($statusVerifikasi == 'ditolak') { $payColor = 'danger'; $statusText = 'Ditolak'; }
                                elseif($statusVerifikasi == 'menunggu_verifikasi') { $payColor = 'warning'; $statusText = 'Menunggu Verifikasi'; }
                            @endphp
                            <x-ui.badge :color="$payColor" size="sm">
                                {{ $statusText }}
                            </x-ui.badge>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                @if($bayar->status_verifikasi == 'menunggu_verifikasi')
                                    <form action="{{ route('admin.pembayaran.verify', $bayar->id) }}" method="POST">
                                        @csrf
                                        <x-ui.button type="submit" variant="primary" size="sm" onclick="window.confirmDialog({ title: 'Verifikasi Pembayaran', name: 'Verifikasi pembayaran ini?', message: 'Pembayaran ini akan ditandai sebagai terverifikasi.', form: this.closest('form'), confirmText: 'Verifikasi', cancelText: 'Batal', type: 'warning' })">
                                            <x-heroicon-o-check class="w-3.5 h-3.5 mr-1 inline" /> Verifikasi
                                        </x-ui.button>
                                    </form>
                                    <form action="{{ route('admin.pembayaran.cancel', $bayar->id) }}" method="POST">
                                        @csrf
                                        <x-ui.button type="submit" variant="danger" size="sm" onclick="window.confirmDialog({ title: 'Batalkan Pembayaran', name: 'Batalkan pembayaran ini?', message: 'Pembayaran ini akan dibatalkan dan statusnya diubah.', form: this.closest('form'), confirmText: 'Batalkan', cancelText: 'Batal', type: 'danger' })">
                                            <x-heroicon-o-x-mark class="w-3.5 h-3.5 mr-1 inline" /> Batal
                                        </x-ui.button>
                                    </form>
                                @else
                                    <x-ui.action-button onclick="openDetailDrawer({{ $bayar->id }})" title="Detail">
                                        <x-heroicon-o-eye class="w-4 h-4" />
                                    </x-ui.action-button>
                                @endif
                            </div>
                        </td>
                    </x-ui.table.row>
                    @empty
                    <tr>
                        <td colspan="8">
                            <x-ui.empty-state icon="clipboard-document-list" title="Belum ada pembayaran" message="Belum ada data pembayaran yang sesuai kriteria pencarian." />
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </x-ui.table>
        </x-ui.data-table>

    </div>
</div>

{{-- DRAWER: DETAIL PEMBAYARAN --}}
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

        fetch(`/admin/pembayaran/detail/${id}`)
            .then(res => res.text())
            .then(html => { content.innerHTML = html; })
            .catch(err => {
                content.innerHTML = '<div class="p-6 text-center text-red-500">Gagal memuat detail pembayaran.</div>';
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
