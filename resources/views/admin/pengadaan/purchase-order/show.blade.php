@extends('layouts.pos')
@section('title', 'Detail Purchase Order')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800 pb-10">
    <div class="w-full p-6 space-y-5 max-w-5xl mx-auto">

        <x-ui.page-header
            :title="$po->nomor_po"
            subtitle="Detail pesanan pembelian ke supplier/toko."
            :breadcrumbs="['Pengadaan', 'Purchase Order', 'Detail']">
            <x-slot:actions>
                <a href="{{ route('pengadaan.po.print', $po->id) }}" target="_blank" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-blue-600 rounded-lg px-4 py-2 hover:bg-blue-700 transition-colors">
                    <x-heroicon-o-printer class="w-4 h-4" />
                    Cetak PO
                </a>
                @if($items->where('sisa', '>', 0)->isNotEmpty() && !in_array($po->status, ['dibatalkan', 'selesai']))
                    <button type="button" onclick="document.getElementById('modal-terima').classList.remove('hidden')" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-emerald-600 rounded-lg px-4 py-2 hover:bg-emerald-700 transition-colors">
                        <x-heroicon-o-inbox-arrow-down class="w-4 h-4" />
                        Terima Barang
                    </button>
                @endif
                <x-ui.button variant="secondary" href="{{ route('pengadaan.po.index') }}">
                    Kembali
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.alert />

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
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
                <div class="mt-1">
                    @if($po->status === 'selesai')
                        <x-ui.badge color="success" size="sm">Diterima Lengkap</x-ui.badge>
                    @elseif($po->status === 'diterima_sebagian')
                        <x-ui.badge color="warning" size="sm">Diterima Sebagian</x-ui.badge>
                    @elseif($po->status === 'dibatalkan')
                        <x-ui.badge color="danger" size="sm">Dibatalkan</x-ui.badge>
                    @else
                        <x-ui.badge color="info" size="sm">Dipesan</x-ui.badge>
                    @endif
                </div>
            </x-ui.card>
            @if($po->kode_pesanan_catering)
            <x-ui.card>
                <p class="text-xs font-semibold text-gray-500">Sumber Pengadaan</p>
                @php
                    $pesanan = \App\Models\Pesanan::find($po->kode_pesanan_catering);
                @endphp
                @if($pesanan)
                    <a href="{{ route('admin.pesanan.catering.show', $pesanan->id) }}" class="font-mono font-bold text-emerald-600 hover:text-emerald-700 mt-1 block text-sm">
                        {{ $pesanan->id_pesanan }}
                    </a>
                @else
                    <p class="font-mono font-bold text-gray-900 mt-1 text-sm">-</p>
                @endif
            </x-ui.card>
            @endif
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
                            <th class="px-4 py-3 text-right">Dipesan</th>
                            <th class="px-4 py-3 text-right">Telah Diterima</th>
                            <th class="px-4 py-3 text-right">Kekurangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($items as $i => $detail)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-4 py-3 text-sm text-gray-500 font-medium align-middle">{{ $i + 1 }}</td>
                            <td class="px-4 py-3 align-middle font-medium text-gray-900 text-sm">
                                {{ optional($detail->bahan_baku)->nama_bahan ?? '-' }}
                                <div class="text-xs text-gray-500 font-normal">{{ optional($detail->bahan_baku)->id_bahan_baku ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3 align-middle text-right font-bold text-gray-900">
                                {{ rtrim(rtrim(number_format($detail->jumlah_pesanan, 2), '0'), '.') }} {{ optional($detail->bahan_baku->satuan)->singkatan ?? '' }}
                            </td>
                            <td class="px-4 py-3 align-middle text-right text-gray-600">
                                {{ rtrim(rtrim(number_format($detail->sudah_diterima, 2), '0'), '.') }} {{ optional($detail->bahan_baku->satuan)->singkatan ?? '' }}
                            </td>
                            <td class="px-4 py-3 align-middle text-right font-bold {{ $detail->sisa > 0 ? 'text-amber-600' : 'text-emerald-600' }}">
                                {{ rtrim(rtrim(number_format($detail->sisa, 2), '0'), '.') }} {{ optional($detail->bahan_baku->satuan)->singkatan ?? '' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-gray-500 text-sm">Belum ada bahan pada PO ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

{{-- MODAL TERIMA BARANG --}}
@if($items->where('sisa', '>', 0)->isNotEmpty() && !in_array($po->status, ['dibatalkan', 'selesai']))
<div id="modal-terima" class="fixed inset-0 z-50 hidden bg-gray-900/50 backdrop-blur-sm overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
        <div class="relative bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-3xl w-full">
            <form action="{{ route('pengadaan.po.terima', $po->id) }}" method="POST">
                @csrf
                <div class="bg-white px-6 pt-5 pb-4">
                    <h3 class="text-lg leading-6 font-semibold text-gray-900 mb-4">Terima Barang (Penerimaan)</h3>
                    
                    <div class="bg-blue-50 text-blue-800 p-4 rounded-lg text-sm mb-6 flex gap-3">
                        <x-heroicon-o-information-circle class="w-5 h-5 shrink-0" />
                        <p>Barang yang diterima dalam kondisi <strong>Baik</strong> akan otomatis ditambahkan ke Stok (Operasional/Catering sesuai tipe) dan dicatat pada riwayat Kartu Stok.</p>
                    </div>

                    <div class="overflow-x-auto border border-gray-200 rounded-lg">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-gray-700">
                                <tr>
                                    <th class="px-4 py-2 text-left">Bahan Baku</th>
                                    <th class="px-4 py-2 text-right">Sisa Dipesan</th>
                                    <th class="px-4 py-2 text-right w-40">Diterima Saat Ini</th>
                                    <th class="px-4 py-2 text-left w-36">Kondisi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($items->where('sisa', '>', 0) as $detail)
                                <tr>
                                    <td class="px-4 py-3 font-medium">{{ optional($detail->bahan_baku)->nama_bahan ?? '-' }}</td>
                                    <td class="px-4 py-3 text-right text-gray-600">
                                        {{ rtrim(rtrim(number_format($detail->sisa, 2), '0'), '.') }} {{ optional($detail->bahan_baku->satuan)->singkatan ?? '' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <input type="number" step="0.01" min="0" max="{{ $detail->sisa }}" name="terima[{{ $detail->bahan_baku_id }}]" value="{{ $detail->sisa }}" class="w-24 text-right rounded-lg border-gray-300 py-1.5 focus:ring-primary-500 focus:border-primary-500 text-sm">
                                            <span class="text-xs text-gray-500 w-8">{{ optional($detail->bahan_baku->satuan)->singkatan ?? '' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <select name="kondisi[{{ $detail->bahan_baku_id }}]" class="w-full rounded-lg border-gray-300 py-1.5 focus:ring-primary-500 text-sm">
                                            <option value="Baik">Baik</option>
                                            <option value="Rusak">Rusak</option>
                                            <option value="Kadaluarsa">Kadaluarsa</option>
                                        </select>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3 rounded-b-xl border-t border-gray-100">
                    <button type="button" onclick="document.getElementById('modal-terima').classList.add('hidden')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 border border-transparent rounded-lg hover:bg-emerald-700 flex items-center gap-1.5">
                        <x-heroicon-o-check class="w-4 h-4" />
                        Simpan Penerimaan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection