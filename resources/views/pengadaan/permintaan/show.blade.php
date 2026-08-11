@extends('layouts.pos')
@section('title', 'Detail Permintaan')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800 pb-10">
    <div class="w-full p-6 space-y-5">

        @php $kodeStatus = optional($pengadaan->status_pengadaan)->kode_status; @endphp

        <x-ui.page-header
            :title="$pengadaan->id_pengadaan"
            subtitle="Detail permintaan pembelian bahan baku."
            :breadcrumbs="['Pengadaan', 'Semua Permintaan', 'Detail']">
            <x-slot:actions>
                @if($sisaItems->isNotEmpty() && ! in_array($kodeStatus, ['selesai', 'dibatalkan']))
                    <a href="{{ route('pengadaan.po.create', $pengadaan->id) }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-emerald-600 rounded-lg px-4 py-2 hover:bg-emerald-700 transition-colors">
                        <x-heroicon-o-shopping-cart class="w-4 h-4" />
                        Buat PO
                    </a>
                @endif
                @if(in_array($kodeStatus, ['draft', 'menunggu_pembelian', 'dalam_proses']))
                    <form id="form-batal-{{ $pengadaan->id }}" action="{{ route('pengadaan.permintaan.cancel', $pengadaan->id) }}" method="POST" class="inline">
                        @csrf
                    </form>
                    <button type="button" onclick="window.confirmDialog({ title: 'Batalkan Permintaan', name: '{{ $pengadaan->id_pengadaan }}', message: 'Permintaan yang dibatalkan tidak dapat diproses kembali.', formId: 'form-batal-{{ $pengadaan->id }}', confirmText: 'Batalkan', cancelText: 'Batal', type: 'warning' })" class="inline-flex items-center gap-1.5 text-sm font-semibold text-rose-600 bg-rose-50 rounded-lg px-4 py-2 hover:bg-rose-100 transition-colors">
                        <x-heroicon-o-x-mark class="w-4 h-4" />
                        Batalkan Permintaan
                    </button>
                @endif
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.alert />

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
            <x-ui.card>
                <p class="text-xs font-semibold text-gray-500">Kode Permintaan</p>
                <p class="font-mono font-bold text-gray-900 mt-1">{{ $pengadaan->id_pengadaan }}</p>
            </x-ui.card>
            <x-ui.card>
                <p class="text-xs font-semibold text-gray-500">Tanggal Permintaan</p>
                <p class="font-bold text-gray-900 mt-1">{{ \Carbon\Carbon::parse($pengadaan->tanggal_pengadaan)->format('d M Y') }}</p>
            </x-ui.card>
            <x-ui.card>
                <p class="text-xs font-semibold text-gray-500">Jenis</p>
                <div class="mt-1"><x-ui.badge :color="$pengadaan->jenis_pengadaan == 'harian' ? 'primary' : 'warning'" size="sm">{{ ucfirst($pengadaan->jenis_pengadaan) }}</x-ui.badge></div>
            </x-ui.card>
            <x-ui.card>
                <p class="text-xs font-semibold text-gray-500">Sumber</p>
                <p class="font-bold text-gray-900 mt-1 text-sm">{{ $pengadaan->jenis_pengadaan == 'catering' && $pengadaan->pesanan ? $pengadaan->pesanan->id_pesanan : 'Operasional Harian' }}</p>
            </x-ui.card>
            <x-ui.card>
                <p class="text-xs font-semibold text-gray-500">Status</p>
                <div class="mt-1"><x-ui.badge :color="$pengadaan->status_warna" size="sm">{{ $pengadaan->status_nama }}</x-ui.badge></div>
            </x-ui.card>
        </div>

        @if($pengadaan->catatan)
        <x-ui.card>
            <p class="text-xs font-semibold text-gray-500">Catatan</p>
            <p class="text-sm text-gray-700 mt-1">{{ $pengadaan->catatan }}</p>
        </x-ui.card>
        @endif

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <x-ui.stat-card label="Total Bahan" :value="$summary['total_bahan']" icon="cube" color="brand" hint="Jumlah bahan dalam permintaan" />
            <x-ui.stat-card label="Sudah Terpenuhi" :value="$summary['terpenuhi']" icon="check-circle" color="green" hint="Bahan diterima penuh" />
            <x-ui.stat-card label="Belum Terpenuhi" :value="$summary['belum']" icon="clock" color="orange" hint="Masih membutuhkan pengadaan" />
            <x-ui.stat-card label="Jumlah PO" :value="$summary['jumlah_po']" icon="shopping-cart" color="violet" hint="Total PO dibuat" />
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="font-bold text-gray-900 text-sm tracking-tight">Alur Pengadaan Bahan</h3>
                    <p class="text-xs text-gray-500 mt-0.5">
                        @if($kodeStatus == 'menunggu_pembelian')
                            Permintaan belum memiliki PO. Buat Purchase Order untuk memulai pengadaan.
                        @elseif($kodeStatus == 'dalam_proses')
                            Barang sedang dibeli. Setelah tiba, catat penerimaan untuk menambah stok.
                        @elseif($kodeStatus == 'menunggu_penerimaan')
                            Barang sedang dibeli. Catat penerimaan saat barang tiba.
                        @elseif($kodeStatus == 'diterima_sebagian')
                            Sebagian kebutuhan telah diterima. Terima sisa / buat PO lanjutan untuk sisa bahan.
                        @elseif($kodeStatus == 'selesai')
                            Seluruh kebutuhan bahan pada permintaan ini sudah terpenuhi.
                        @else
                            Permintaan ini dibatalkan.
                        @endif
                    </p>
                </div>
                @if($sisaItems->isNotEmpty() && ! in_array($kodeStatus, ['selesai', 'dibatalkan']))
                    <a href="{{ route('pengadaan.po.create', $pengadaan->id) }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-emerald-600 rounded-lg px-4 py-2 hover:bg-emerald-700 transition-colors shrink-0">
                        <x-heroicon-o-shopping-cart class="w-4 h-4" />
                        Buat PO Lanjutan
                    </a>
                @endif
            </div>
        </div>

        @if($sisaItems->isNotEmpty() && ! in_array($kodeStatus, ['dibatalkan']))
        <div class="bg-white rounded-xl border border-amber-200 overflow-hidden shadow-sm">
            <div class="bg-amber-50 px-4 py-3 border-b border-amber-200">
                <h3 class="font-bold text-gray-900 text-sm tracking-tight">Sisa Bahan Belum Terpenuhi</h3>
                <p class="text-xs text-gray-500 mt-0.5">Bahan berikut masih membutuhkan pengadaan.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                            <th class="px-4 py-3 text-left">Bahan Baku</th>
                            <th class="px-4 py-3 text-right">Jumlah Diminta</th>
                            <th class="px-4 py-3 text-right">Jumlah Diterima</th>
                            <th class="px-4 py-3 text-right">Sisa</th>
                            <th class="px-4 py-3 text-left">Satuan</th>
                            <th class="px-4 py-3 text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($sisaItems as $item)
                        <tr>
                            <td class="px-4 py-3 align-middle font-medium text-gray-900 text-sm">{{ $item['nama_bahan'] }}</td>
                            <td class="px-4 py-3 align-middle text-right font-semibold text-gray-900">{{ $item['jumlah_diminta'] }}</td>
                            <td class="px-4 py-3 align-middle text-right text-gray-600">{{ $item['jumlah_diterima'] }}</td>
                            <td class="px-4 py-3 align-middle text-right font-bold text-amber-600">{{ $item['sisa'] }}</td>
                            <td class="px-4 py-3 align-middle"><span class="text-xs font-semibold text-gray-500 bg-gray-100 px-2 py-1 rounded-md">{{ $item['satuan'] ?? '-' }}</span></td>
                            <td class="px-4 py-3 align-middle"><x-ui.badge :color="$item['warna']" size="sm">{{ $item['status_nama'] }}</x-ui.badge></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
            <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                <h3 class="font-bold text-gray-900 text-sm tracking-tight mb-1">Detail Kebutuhan Bahan</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wide bg-white">
                            <th class="px-4 py-3 text-left w-12">No</th>
                            <th class="px-4 py-3 text-left">Bahan Baku</th>
                            <th class="px-4 py-3 text-right">Jumlah Diminta</th>
                            <th class="px-4 py-3 text-right">Sudah di-PO</th>
                            <th class="px-4 py-3 text-right">Jumlah Diterima</th>
                            <th class="px-4 py-3 text-right">Sisa Kebutuhan</th>
                            <th class="px-4 py-3 text-left">Satuan</th>
                            <th class="px-4 py-3 text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($items as $i => $item)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-4 py-3 text-sm text-gray-500 font-medium align-middle">{{ $i + 1 }}</td>
                            <td class="px-4 py-3 align-middle font-medium text-gray-900 text-sm">{{ $item['nama_bahan'] }}</td>
                            <td class="px-4 py-3 align-middle text-right font-bold text-gray-900">{{ $item['jumlah_diminta'] }}</td>
                            <td class="px-4 py-3 align-middle text-right text-gray-600">{{ $item['sudah_di_po'] }}</td>
                            <td class="px-4 py-3 align-middle text-right text-gray-600">{{ $item['jumlah_diterima'] }}</td>
                            <td class="px-4 py-3 align-middle text-right font-bold {{ $item['sisa'] > 0 ? 'text-amber-600' : 'text-emerald-600' }}">{{ $item['sisa'] }}</td>
                            <td class="px-4 py-3 align-middle"><span class="text-xs font-semibold text-gray-500 bg-gray-100 px-2 py-1 rounded-md">{{ $item['satuan'] ?? '-' }}</span></td>
                            <td class="px-4 py-3 align-middle"><x-ui.badge :color="$item['warna']" size="sm">{{ $item['status_nama'] }}</x-ui.badge></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-4 py-10 text-center text-gray-500 text-sm">Tidak ada detail bahan baku.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($pengadaan->purchase_order->isNotEmpty())
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
            <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                <h3 class="font-bold text-gray-900 text-sm tracking-tight">Daftar Purchase Order</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                            <th class="px-4 py-3 text-left">Kode PO</th>
                            <th class="px-4 py-3 text-left">Tanggal</th>
                            <th class="px-4 py-3 text-left">Supplier/Toko</th>
                            <th class="px-4 py-3 text-center">Jumlah Item</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($pengadaan->purchase_order as $po)
                        <tr>
                            <td class="px-4 py-3 align-middle font-mono font-bold text-gray-900 text-xs">{{ $po->nomor_po }}</td>
                            <td class="px-4 py-3 align-middle text-gray-700">{{ \Carbon\Carbon::parse($po->tanggal_po)->format('d M Y') }}</td>
                            <td class="px-4 py-3 align-middle text-gray-700">{{ $po->supplier }}</td>
                            <td class="px-4 py-3 align-middle text-center font-bold text-gray-900">{{ $po->detail_purchase_order->count() }} <span class="text-xs font-normal text-gray-500">item</span></td>
                            <td class="px-4 py-3 align-middle"><x-ui.badge :color="$po->status_warna" size="sm">{{ $po->status_nama }}</x-ui.badge></td>
                            <td class="px-4 py-3 align-middle text-center">
                                <a href="{{ route('pengadaan.po.show', $po->id) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-gray-700 bg-gray-100 rounded-lg px-2.5 py-1.5 hover:bg-gray-200 transition-colors">Detail</a>
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