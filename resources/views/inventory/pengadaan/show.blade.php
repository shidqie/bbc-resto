@extends('layouts.pos')
@section('title', 'Detail Permintaan')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800 pb-10">
    <div class="w-full p-6 space-y-5">

        <x-ui.page-header
            :title="$pengadaan->nomor_pengadaan"
            subtitle="Detail permintaan pembelian bahan baku."
            :breadcrumbs="['Pengadaan', 'Semua Permintaan', 'Detail']">
            <x-slot:actions>
                <a href="{{ route('pengadaan.permintaan.pdf', $pengadaan->id) }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-blue-600 rounded-lg px-3 py-2 hover:bg-blue-700 transition-colors">
                    <x-heroicon-o-printer class="w-4 h-4" />
                    Unduh PDF
                </a>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.alert />

        {{-- Informasi Permintaan --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
            <x-ui.card>
                <p class="text-xs font-semibold text-gray-500">Kode Permintaan</p>
                <p class="font-mono font-bold text-gray-900 mt-1">{{ $pengadaan->nomor_pengadaan }}</p>
            </x-ui.card>
            <x-ui.card>
                <p class="text-xs font-semibold text-gray-500">Tanggal Permintaan</p>
                <p class="font-bold text-gray-900 mt-1">{{ \Carbon\Carbon::parse($pengadaan->tanggal_pengadaan)->format('d M Y') }}</p>
            </x-ui.card>
            <x-ui.card>
                <p class="text-xs font-semibold text-gray-500">Dibuat Oleh</p>
                <p class="font-bold text-gray-900 mt-1">{{ optional($pengadaan->diajukan_oleh_pengguna)->nama ?? '-' }}</p>
            </x-ui.card>
            <x-ui.card>
                <p class="text-xs font-semibold text-gray-500">Jenis</p>
                <p class="font-bold text-gray-900 mt-1">{{ ucfirst($pengadaan->jenis_pengadaan) }}</p>
            </x-ui.card>
            <x-ui.card>
                <p class="text-xs font-semibold text-gray-500">Status</p>
                @php
                    $kodeStatus = optional($pengadaan->status_pengadaan)->kode_status;
                    $sColor = 'gray';
                    if($kodeStatus == 'draft') $sColor = 'gray';
                    elseif($kodeStatus == 'menunggu_pembelian') $sColor = 'warning';
                    elseif($kodeStatus == 'dalam_proses') $sColor = 'primary';
                    elseif($kodeStatus == 'menunggu_penerimaan') $sColor = 'primary';
                    elseif($kodeStatus == 'diterima_sebagian') $sColor = 'warning';
                    elseif($kodeStatus == 'selesai') $sColor = 'success';
                    elseif($kodeStatus == 'dibatalkan') $sColor = 'danger';
                @endphp
                <div class="mt-1"><x-ui.badge :color="$sColor" size="sm">{{ optional($pengadaan->status_pengadaan)->nama_status ?? 'Unknown' }}</x-ui.badge></div>
            </x-ui.card>
        </div>

        @if($pengadaan->catatan)
        <x-ui.card>
            <p class="text-xs font-semibold text-gray-500">Catatan</p>
            <p class="text-sm text-gray-700 mt-1">{{ $pengadaan->catatan }}</p>
        </x-ui.card>
        @endif

        {{-- Detail Bahan Baku --}}
        <x-ui.data-table :paginator="null">
            <x-ui.table>
                <x-ui.table.header>
                    <th class="px-4 py-3.5 text-left w-12">No</th>
                    <th class="px-4 py-3.5 text-left">Kode Bahan</th>
                    <th class="px-4 py-3.5 text-left">Nama Bahan Baku</th>
                    <th class="px-4 py-3.5 text-right">Stok Saat Ini</th>
                    <th class="px-4 py-3.5 text-right">Stok Minimum</th>
                    <th class="px-4 py-3.5 text-right">Jumlah Permintaan</th>
                    <th class="px-4 py-3.5 text-left">Satuan</th>
                </x-ui.table.header>
                <tbody class="divide-y divide-gray-100">
                    @forelse($pengadaan->detail_pengadaan_bahan as $i => $detail)
                    <x-ui.table.row>
                        <td class="px-4 py-4 text-sm text-gray-500 font-medium align-middle">{{ $i + 1 }}</td>
                        <td class="px-4 py-4 align-middle font-mono text-xs font-bold text-gray-900">{{ optional($detail->bahan_baku)->kode_bahan ?? '-' }}</td>
                        <td class="px-4 py-4 align-middle font-medium text-gray-900 text-sm">{{ optional($detail->bahan_baku)->nama_bahan ?? '-' }}</td>
                        <td class="px-4 py-4 align-middle text-right font-medium {{ (float)$detail->stok_saat_ini <= (float)$detail->stok_minimum ? 'text-rose-600' : 'text-gray-900' }}">{{ (float)$detail->stok_saat_ini }}</td>
                        <td class="px-4 py-4 align-middle text-right text-gray-600">{{ (float)$detail->stok_minimum }}</td>
                        <td class="px-4 py-4 align-middle text-right font-bold text-gray-900">{{ (float)$detail->jumlah_dipesan }}</td>
                        <td class="px-4 py-4 align-middle">
                            <span class="text-xs font-semibold text-gray-500 bg-gray-100 px-2 py-1 rounded-md">{{ optional($detail->satuan)->nama_satuan ?? '-' }}</span>
                        </td>
                    </x-ui.table.row>
                    @empty
                    <x-empty-state icon="clipboard" title="Tidak ada data" message="Tidak ada detail bahan baku." :colspan="7" />
                    @endforelse
                </tbody>
            </x-ui.table>
        </x-ui.data-table>

        @if($pengadaan->penerimaan_bahan->isNotEmpty())
        <x-ui.card>
            <h3 class="font-bold text-gray-900 text-sm tracking-tight mb-3">Riwayat Penerimaan</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                            <th class="px-4 py-3 text-left">Kode Penerimaan</th>
                            <th class="px-4 py-3 text-left">Tanggal</th>
                            <th class="px-4 py-3 text-left">Supplier</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Diterima Oleh</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($pengadaan->penerimaan_bahan as $pnr)
                        <tr>
                            <td class="px-4 py-3 align-middle font-mono font-bold text-gray-900 text-xs">{{ $pnr->nomor_penerimaan }}</td>
                            <td class="px-4 py-3 align-middle text-gray-700">{{ \Carbon\Carbon::parse($pnr->diterima_pada)->format('d M Y H:i') }}</td>
                            <td class="px-4 py-3 align-middle text-gray-700">{{ $pnr->supplier ?? '-' }}</td>
                            <td class="px-4 py-3 align-middle"><x-ui.badge size="sm">{{ $pnr->status_nama }}</x-ui.badge></td>
                            <td class="px-4 py-3 align-middle text-gray-700">{{ optional($pnr->diterima_oleh_pengguna)->nama ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-ui.card>
        @endif

    </div>
</div>
@endsection
