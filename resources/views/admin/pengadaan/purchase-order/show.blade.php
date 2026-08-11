@extends('layouts.pos')
@section('title', 'Detail Purchase Order')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800 pb-10">
    <div class="w-full p-6 space-y-5">

        <x-ui.page-header
            :title="$po->nomor_po"
            subtitle="Detail pesanan pembelian ke supplier/toko."
            :breadcrumbs="['Pengadaan', 'Purchase Order', 'Detail']">
            <x-slot:actions>
                <a href="{{ route('pengadaan.po.print', $po->id) }}" target="_blank" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-blue-600 rounded-lg px-4 py-2 hover:bg-blue-700 transition-colors">
                    <x-heroicon-o-printer class="w-4 h-4" />
                    Cetak PO
                </a>
                @if($sisaItems->isNotEmpty())
                    <a href="{{ route('pengadaan.penerimaan.create', $po->id) }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-emerald-600 rounded-lg px-4 py-2 hover:bg-emerald-700 transition-colors">
                        <x-heroicon-o-inbox-arrow-down class="w-4 h-4" />
                        Terima Barang
                    </a>
                @endif
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.alert />

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
            <x-ui.card>
                <p class="text-xs font-semibold text-gray-500">Kode PO</p>
                <p class="font-mono font-bold text-gray-900 mt-1">{{ $po->nomor_po }}</p>
            </x-ui.card>
            <x-ui.card>
                <p class="text-xs font-semibold text-gray-500">Kode Permintaan</p>
                <p class="font-mono font-bold text-gray-900 mt-1 text-sm">{{ optional($po->pengadaan_bahan)->id_pengadaan ?? '-' }}</p>
            </x-ui.card>
            <x-ui.card>
                <p class="text-xs font-semibold text-gray-500">Tanggal PO</p>
                <p class="font-bold text-gray-900 mt-1">{{ \Carbon\Carbon::parse($po->tanggal_po)->format('d M Y') }}</p>
            </x-ui.card>
            <x-ui.card>
                <p class="text-xs font-semibold text-gray-500">Supplier/Toko</p>
                <p class="font-bold text-gray-900 mt-1 text-sm">{{ $po->supplier }}</p>
            </x-ui.card>
            <x-ui.card>
                <p class="text-xs font-semibold text-gray-500">Status</p>
                <div class="mt-1"><x-ui.badge :color="$po->status_warna" size="sm">{{ $po->status_nama }}</x-ui.badge></div>
            </x-ui.card>
        </div>

        @if($po->catatan)
        <x-ui.card>
            <p class="text-xs font-semibold text-gray-500">Catatan</p>
            <p class="text-sm text-gray-700 mt-1">{{ $po->catatan }}</p>
        </x-ui.card>
        @endif

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
            <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 flex justify-between items-center">
                <h3 class="font-bold text-gray-900 text-sm tracking-tight">Daftar Bahan PO</h3>
                <span class="text-xs text-gray-500 font-medium">Dibuat oleh {{ optional($po->dibuat_oleh_pengguna)->nama ?? '-' }} pada {{ optional($po->dibuat_pada)->format('d M Y H:i') }}</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wide bg-white">
                            <th class="px-4 py-3 text-left w-12">No</th>
                            <th class="px-4 py-3 text-left">Bahan Baku</th>
                            <th class="px-4 py-3 text-right">Jumlah Dipesan</th>
                            <th class="px-4 py-3 text-right">Jumlah Diterima</th>
                            <th class="px-4 py-3 text-right">Sisa</th>
                            <th class="px-4 py-3 text-left">Satuan</th>
                            <th class="px-4 py-3 text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($po->detail_purchase_order as $i => $detail)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-4 py-3 text-sm text-gray-500 font-medium align-middle">{{ $i + 1 }}</td>
                            <td class="px-4 py-3 align-middle font-medium text-gray-900 text-sm">{{ optional($detail->bahan_baku)->nama_bahan ?? '-' }}</td>
                            <td class="px-4 py-3 align-middle text-right font-bold text-gray-900">{{ $detail->jumlah_dipesan }}</td>
                            <td class="px-4 py-3 align-middle text-right text-gray-600">{{ $detail->jumlah_diterima }}</td>
                            <td class="px-4 py-3 align-middle text-right font-bold {{ $detail->sisa > 0 ? 'text-amber-600' : 'text-emerald-600' }}">{{ $detail->sisa }}</td>
                            <td class="px-4 py-3 align-middle"><span class="text-xs font-semibold text-gray-500 bg-gray-100 px-2 py-1 rounded-md">{{ optional($detail->satuan)->nama_satuan ?? '-' }}</span></td>
                            <td class="px-4 py-3 align-middle"><x-ui.badge :color="$detail->status_warna" size="sm">{{ $detail->status_nama }}</x-ui.badge></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-gray-500 text-sm">Belum ada bahan pada PO ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($po->penerimaan_bahan->isNotEmpty())
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
            <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                <h3 class="font-bold text-gray-900 text-sm tracking-tight">Riwayat Penerimaan</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                            <th class="px-4 py-3 text-left">Kode Penerimaan</th>
                            <th class="px-4 py-3 text-left">Tanggal</th>
                            <th class="px-4 py-3 text-center">Jumlah Item</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Diterima Oleh</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($po->penerimaan_bahan as $pnr)
                        <tr>
                            <td class="px-4 py-3 align-middle font-mono font-bold text-gray-900 text-xs">{{ $pnr->nomor_penerimaan }}</td>
                            <td class="px-4 py-3 align-middle text-gray-700">{{ \Carbon\Carbon::parse($pnr->diterima_pada)->format('d M Y H:i') }}</td>
                            <td class="px-4 py-3 align-middle text-center font-bold text-gray-900">{{ $pnr->detail_penerimaan_bahan->count() }} item</td>
                            <td class="px-4 py-3 align-middle"><x-ui.badge size="sm">{{ $pnr->status_nama }}</x-ui.badge></td>
                            <td class="px-4 py-3 align-middle text-gray-700">{{ optional($pnr->diterima_oleh_pengguna)->nama ?? '-' }}</td>
                            <td class="px-4 py-3 align-middle text-center">
                                <a href="{{ route('pengadaan.penerimaan.show', $pnr->id) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-gray-700 bg-gray-100 rounded-lg px-2.5 py-1.5 hover:bg-gray-200 transition-colors">Detail</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

    </div>
</div>
@endsection