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
                <a href="{{ route('pengadaan.po.print', $po->id) }}" target="_blank" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-primary rounded-lg px-4 py-2 hover:bg-primary-container transition-colors shadow-2xs">
                    <x-heroicon-o-printer class="w-4 h-4" />
                    Cetak Surat PO
                </a>
                @if(app(\App\Services\PengadaanStatusService::class)->poMasihBisaDiterima($po))
                    <a href="{{ route('pengadaan.penerimaan.create', $po->id) }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-emerald-600 rounded-lg px-4 py-2 hover:bg-emerald-700 transition-colors">
                        <x-heroicon-o-inbox-arrow-down class="w-4 h-4" />
                        Terima Barang
                    </a>
                @endif
                <x-ui.button variant="secondary" href="{{ route('pengadaan.po.index') }}">
                    Kembali
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.alert />

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <x-ui.card>
                <p class="text-xs font-semibold text-gray-500">Kode PO</p>
                <p class="font-mono font-bold text-gray-900 mt-1">{{ $po->nomor_po }}</p>
            </x-ui.card>
            <x-ui.card>
                <p class="text-xs font-semibold text-gray-500">Tanggal PO</p>
                <p class="font-bold text-gray-900 mt-1">{{ \Carbon\Carbon::parse($po->tanggal_po)->translatedFormat('d M Y') }}</p>
            </x-ui.card>
            <x-ui.card>
                <p class="text-xs font-semibold text-gray-500">Nama Supplier</p>
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
            @if($po->kode_pesanan_catering || optional($po->pengadaan_bahan)->pesanan_id)
            <x-ui.card>
                <p class="text-xs font-semibold text-gray-500">Sumber Pengadaan</p>
                @php
                    $pesanan = null;
                    if ($po->kode_pesanan_catering) {
                        $pesanan = \App\Models\Pesanan::where('id_pesanan', $po->kode_pesanan_catering)->first() ?: \App\Models\Pesanan::find($po->kode_pesanan_catering);
                    }
                    if (!$pesanan && optional($po->pengadaan_bahan)->pesanan) {
                        $pesanan = $po->pengadaan_bahan->pesanan;
                    }
                @endphp
                @if($pesanan)
                    <a href="{{ route('admin.pesanan.catering.show', $pesanan->id) }}" class="font-mono font-bold text-emerald-600 hover:text-emerald-700 mt-1 block text-sm">
                        {{ $pesanan->id_pesanan }}
                    </a>
                @else
                    <p class="font-mono font-bold text-gray-900 mt-1 text-sm">{{ $po->kode_pesanan_catering ?: '-' }}</p>
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
                <span class="text-xs text-gray-500 font-medium">Dibuat oleh {{ optional($po->dibuat_oleh_pengguna)->nama ?? '-' }} pada {{ optional($po->dibuat_pada)->translatedFormat('d M Y H:i') }}</span>
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
                        @php
                            $satuanBeli = optional($detail->satuan)->singkatan ?? optional($detail->satuan)->nama_satuan ?? \App\Helpers\UnitHelper::getPurchasingUnit($detail->bahan_baku?->satuan);
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-4 py-3 text-sm text-gray-500 font-medium align-middle">{{ $items->firstItem() + $i }}</td>
                            <td class="px-4 py-3 align-middle font-medium text-gray-900 text-sm">
                                {{ optional($detail->bahan_baku)->nama_bahan ?? '-' }}
                                <div class="text-xs text-gray-500 font-normal">{{ optional($detail->bahan_baku)->id_bahan_baku ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3 align-middle text-right font-bold text-gray-900">
                                {{ \App\Helpers\UnitHelper::formatNumber($detail->jumlah_dipesan) }} {{ $satuanBeli }}
                            </td>
                            <td class="px-4 py-3 align-middle text-right text-gray-600">
                                {{ \App\Helpers\UnitHelper::formatNumber($detail->sudah_diterima) }} {{ $satuanBeli }}
                            </td>
                            <td class="px-4 py-3 align-middle text-right font-bold {{ $detail->sisa > 0 ? 'text-amber-600' : 'text-emerald-600' }}">
                                {{ \App\Helpers\UnitHelper::formatNumber($detail->sisa) }} {{ $satuanBeli }}
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
            
            @if($items->hasPages())
            <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">
                {{ $items->links() }}
            </div>
            @endif
        </div>

    </div>
</div>
@endsection

@push('scripts')
@if(session('penerimaan_berhasil'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Bahan Baku Berhasil Diterima!',
                html: '<div class="text-left bg-emerald-50/70 p-4 rounded-xl border border-emerald-100 text-xs text-emerald-950 space-y-1.5">' +
                      '<p class="font-bold flex items-center gap-1.5 text-emerald-800"><svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Stok Bahan Otomatis Bertambah</p>' +
                      '<p class="text-gray-600">Penerimaan nomor <b>{{ session('penerimaan_nomor') }}</b> telah berhasil dicatat ke dalam sistem persediaan.</p>' +
                      '</div>',
                confirmButtonColor: '#059669',
                confirmButtonText: 'Selesai',
                customClass: {
                    popup: 'rounded-2xl',
                    confirmButton: 'rounded-xl px-6 py-2.5 font-bold text-sm shadow-xs'
                }
            });
        }
    });
</script>
@endif
@endpush