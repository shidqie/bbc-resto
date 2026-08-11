@extends('layouts.pos')
@section('title', 'Detail Penerimaan')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800 pb-10">
    <div class="w-full p-6 space-y-5">

        <x-ui.page-header
            :title="$penerimaan->nomor_penerimaan"
            subtitle="Detail penerimaan bahan baku dari supplier."
            :breadcrumbs="['Pengadaan', 'Penerimaan Bahan Baku', 'Detail']">
        </x-ui.page-header>

        <x-ui.alert />

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
            <x-ui.card>
                <p class="text-xs font-semibold text-gray-500">Kode Penerimaan</p>
                <p class="font-mono font-bold text-gray-900 mt-1">{{ $penerimaan->nomor_penerimaan }}</p>
            </x-ui.card>
            <x-ui.card>
                <p class="text-xs font-semibold text-gray-500">Kode PO</p>
                <p class="font-mono font-bold text-gray-900 mt-1 text-sm">{{ optional($penerimaan->purchase_order)->nomor_po ?? '-' }}</p>
            </x-ui.card>
            <x-ui.card>
                <p class="text-xs font-semibold text-gray-500">Kode Permintaan</p>
                <p class="font-mono font-bold text-gray-900 mt-1 text-sm">{{ optional($penerimaan->pengadaan_bahan)->id_pengadaan ?? '-' }}</p>
            </x-ui.card>
            <x-ui.card>
                <p class="text-xs font-semibold text-gray-500">Supplier/Toko</p>
                <p class="font-bold text-gray-900 mt-1 text-sm">{{ $penerimaan->supplier ?? '-' }}</p>
            </x-ui.card>
            <x-ui.card>
                <p class="text-xs font-semibold text-gray-500">Status</p>
                <div class="mt-1"><x-ui.badge :color="$penerimaan->status == 'selesai' ? 'success' : 'primary'" size="sm">{{ $penerimaan->status_nama }}</x-ui.badge></div>
            </x-ui.card>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <x-ui.card>
                <p class="text-xs font-semibold text-gray-500">Tanggal</p>
                <p class="font-bold text-gray-900 mt-1">{{ \Carbon\Carbon::parse($penerimaan->diterima_pada)->format('d M Y H:i') }}</p>
            </x-ui.card>
            <x-ui.card>
                <p class="text-xs font-semibold text-gray-500">Nomor Nota</p>
                <p class="font-bold text-gray-900 mt-1">{{ $penerimaan->nomor_nota ?? '-' }}</p>
            </x-ui.card>
            <x-ui.card>
                <p class="text-xs font-semibold text-gray-500">Diterima Oleh</p>
                <p class="font-bold text-gray-900 mt-1 text-sm">{{ optional($penerimaan->diterima_oleh_pengguna)->nama ?? '-' }}</p>
            </x-ui.card>
            <x-ui.card>
                <p class="text-xs font-semibold text-gray-500">Total Pembelian</p>
                <p class="font-bold text-gray-900 mt-1">Rp {{ number_format($total_pembelian, 0, ',', '.') }}</p>
            </x-ui.card>
        </div>

        @if($penerimaan->catatan)
        <x-ui.card>
            <p class="text-xs font-semibold text-gray-500">Catatan</p>
            <p class="text-sm text-gray-700 mt-1">{{ $penerimaan->catatan }}</p>
        </x-ui.card>
        @endif

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
            <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                <h3 class="font-bold text-gray-900 text-sm tracking-tight">Daftar Bahan Diterima</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wide bg-white">
                            <th class="px-4 py-3 text-left w-12">No</th>
                            <th class="px-4 py-3 text-left">Bahan Baku</th>
                            <th class="px-4 py-3 text-right">Dipesan</th>
                            <th class="px-4 py-3 text-right">Diterima</th>
                            <th class="px-4 py-3 text-right">Harga Satuan</th>
                            <th class="px-4 py-3 text-right">Subtotal</th>
                            <th class="px-4 py-3 text-left">Kondisi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($penerimaan->detail_penerimaan_bahan as $i => $d)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-4 py-3 text-sm text-gray-500 font-medium align-middle">{{ $i + 1 }}</td>
                            <td class="px-4 py-3 align-middle font-medium text-gray-900 text-sm">{{ optional($d->bahan_baku)->nama_bahan ?? '-' }}</td>
                            <td class="px-4 py-3 align-middle text-right text-gray-600">{{ (float) $d->jumlah_diminta }}</td>
                            <td class="px-4 py-3 align-middle text-right font-bold text-gray-900">{{ (float) $d->jumlah_diterima }} <span class="text-xs font-normal text-gray-500">{{ optional($d->satuan)->nama_satuan ?? '' }}</span></td>
                            <td class="px-4 py-3 align-middle text-right text-gray-600">Rp {{ number_format((float) $d->harga_satuan, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 align-middle text-right font-bold text-gray-900">Rp {{ number_format((float) $d->jumlah_diterima * (float) $d->harga_satuan, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 align-middle"><x-ui.badge :color="$d->kondisi == 'Rusak' ? 'danger' : 'success'" size="sm">{{ $d->kondisi ?? 'Baik' }}</x-ui.badge></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-gray-500 text-sm">Belum ada detail penerimaan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($sisaItems->isNotEmpty())
        <div class="bg-white rounded-xl border border-amber-200 overflow-hidden shadow-sm">
            <div class="bg-amber-50 px-4 py-3 border-b border-amber-200">
                <h3 class="font-bold text-gray-900 text-sm tracking-tight">Masih Ada Bahan Belum Terpenuhi</h3>
                <p class="text-xs text-gray-500 mt-0.5">Buat PO lanjutan untuk sisa bahan berikut.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                            <th class="px-4 py-3 text-left">Bahan Baku</th>
                            <th class="px-4 py-3 text-right">Sisa</th>
                            <th class="px-4 py-3 text-left">Satuan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($sisaItems as $item)
                        <tr>
                            <td class="px-4 py-3 align-middle font-medium text-gray-900 text-sm">{{ $item['nama_bahan'] }}</td>
                            <td class="px-4 py-3 align-middle text-right font-bold text-amber-600">{{ $item['sisa'] }}</td>
                            <td class="px-4 py-3 align-middle"><span class="text-xs font-semibold text-gray-500 bg-gray-100 px-2 py-1 rounded-md">{{ $item['satuan'] ?? '-' }}</span></td>
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