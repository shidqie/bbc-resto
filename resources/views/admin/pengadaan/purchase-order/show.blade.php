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

        @php
            $pesanan = null;
            if ($po->kode_pesanan_catering) {
                $pesanan = \App\Models\Pesanan::where('id_pesanan', $po->kode_pesanan_catering)->first() ?: \App\Models\Pesanan::find($po->kode_pesanan_catering);
            }
            if (!$pesanan && optional($po->pengadaan_bahan)->pesanan) {
                $pesanan = $po->pengadaan_bahan->pesanan;
            }
            $pesananUrl = $pesanan ? match((int)$pesanan->jenis_pesanan_id) {
                2 => route('admin.pesanan.catering.show', $pesanan->id),
                3 => route('admin.pesanan.nasibox.show', $pesanan->id),
                default => route('admin.pesanan.show', $pesanan->id)
            } : null;
            $kodePsn = $po->kode_pesanan_catering ?: optional(optional($po->pengadaan_bahan)->pesanan)->id_pesanan;
        @endphp

        {{-- INFO CARDS (2-COLUMN COHESIVE LAYOUT) --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            {{-- CARD 1: INFORMASI PO & SUPPLIER --}}
            <div class="bg-white rounded-2xl border border-gray-200/80 p-5 shadow-2xs space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="font-bold text-gray-900 text-sm tracking-tight flex items-center gap-2">
                        <x-heroicon-o-document-text class="w-4 h-4 text-emerald-600" />
                        Informasi Purchase Order
                    </h3>
                    @if($po->status === 'selesai')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            Selesai (Diterima Penuh)
                        </span>
                    @elseif($po->status === 'diterima_sebagian')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                            Diterima Sebagian
                        </span>
                    @elseif($po->status === 'dibatalkan')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                            Dibatalkan
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-sky-50 text-sky-700 border border-sky-200">
                            Menunggu Pengiriman
                        </span>
                    @endif
                </div>

                <div class="grid grid-cols-2 gap-y-3.5 text-xs">
                    <div>
                        <span class="text-gray-400 font-medium block mb-0.5">Kode PO</span>
                        <span class="font-mono font-bold text-gray-900 text-sm">{{ $po->nomor_po }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 font-medium block mb-0.5">Tanggal PO</span>
                        <span class="font-bold text-gray-900 text-sm">{{ \Carbon\Carbon::parse($po->tanggal_po)->translatedFormat('d F Y') }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 font-medium block mb-0.5">Nama Supplier / Toko</span>
                        <span class="font-bold text-gray-900 text-sm flex items-center gap-1.5">
                            <x-heroicon-o-building-storefront class="w-3.5 h-3.5 text-gray-400" />
                            {{ $po->supplier }}
                        </span>
                        @if($po->no_telp_supplier && $po->no_telp_supplier !== '-')
                            <span class="text-[11px] text-gray-500 font-mono block mt-0.5">{{ $po->no_telp_supplier }}</span>
                        @endif
                    </div>
                    <div>
                        <span class="text-gray-400 font-medium block mb-0.5">Dibuat Oleh</span>
                        <span class="font-semibold text-gray-800">{{ optional($po->dibuat_oleh_pengguna)->nama ?? '-' }}</span>
                        <span class="text-[11px] text-gray-400 block">{{ optional($po->dibuat_pada)->translatedFormat('d M Y, H:i') }} WIB</span>
                    </div>
                </div>

                @if($po->catatan)
                <div class="pt-3 border-t border-gray-100 text-xs">
                    <span class="text-gray-400 font-medium block mb-1">Catatan Tambahan:</span>
                    <p class="text-gray-700 italic bg-gray-50 p-2.5 rounded-xl border border-gray-100 leading-relaxed">{{ $po->catatan }}</p>
                </div>
                @endif
            </div>

            {{-- CARD 2: SUMBER & ALUR PENGADAAN --}}
            <div class="bg-white rounded-2xl border border-gray-200/80 p-5 shadow-2xs space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="font-bold text-gray-900 text-sm tracking-tight flex items-center gap-2">
                        <x-heroicon-o-arrows-right-left class="w-4 h-4 text-emerald-600" />
                        Sumber & Rincian Pengadaan
                    </h3>
                    <span class="text-xs font-semibold px-2.5 py-0.5 rounded-lg {{ $po->jenis_po == 'operasional' ? 'bg-blue-50 text-blue-700 border border-blue-100' : 'bg-purple-50 text-purple-700 border border-purple-100' }}">
                        {{ $po->jenis_po == 'operasional' ? 'Stok Harian (Operasional)' : 'Pesanan Katering' }}
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-y-3.5 text-xs">
                    <div>
                        <span class="text-gray-400 font-medium block mb-0.5">Kode Permintaan</span>
                        @if($po->pengadaan_bahan)
                            <a href="{{ route('pengadaan.permintaan.show', $po->pengadaan_bahan->id) }}" class="font-mono font-bold text-emerald-700 hover:text-emerald-800 text-sm inline-flex items-center gap-1 hover:underline">
                                {{ $po->pengadaan_bahan->id_pengadaan }}
                                <x-heroicon-m-arrow-top-right-on-square class="w-3 h-3" />
                            </a>
                        @else
                            <span class="font-mono font-semibold text-gray-500">-</span>
                        @endif
                    </div>
                    <div>
                        <span class="text-gray-400 font-medium block mb-0.5">ID Pesanan Terkait</span>
                        @if($kodePsn)
                            @if($pesanan && $pesananUrl)
                                <a href="{{ $pesananUrl }}" class="font-mono font-bold text-emerald-700 hover:text-emerald-800 text-sm inline-flex items-center gap-1 hover:underline">
                                    {{ $kodePsn }}
                                    <x-heroicon-m-arrow-top-right-on-square class="w-3 h-3" />
                                </a>
                            @else
                                <span class="font-mono font-bold text-gray-900 text-sm">{{ $kodePsn }}</span>
                            @endif
                        @else
                            <span class="text-gray-400 italic">Pengadaan Stok Umum</span>
                        @endif
                    </div>
                    <div>
                        <span class="text-gray-400 font-medium block mb-0.5">Total Item Bahan</span>
                        <span class="font-bold text-gray-900 text-sm">{{ $po->detail_purchase_order->count() }} Bahan Baku</span>
                    </div>
                    <div>
                        <span class="text-gray-400 font-medium block mb-0.5">Status Barang</span>
                        @php
                            $totalDipesan = $po->detail_purchase_order->sum('jumlah_dipesan');
                            $totalDiterima = $po->detail_purchase_order->sum('jumlah_diterima');
                            $persen = $totalDipesan > 0 ? min(100, round(($totalDiterima / $totalDipesan) * 100)) : 0;
                        @endphp
                        <span class="font-bold {{ $persen >= 100 ? 'text-emerald-700' : 'text-amber-700' }} text-sm">
                            {{ $persen }}% Terpenuhi
                        </span>
                    </div>
                </div>

                @if(app(\App\Services\PengadaanStatusService::class)->poMasihBisaDiterima($po))
                <div class="pt-3 border-t border-gray-100 flex items-center justify-between text-xs bg-emerald-50/50 p-3 rounded-xl border border-emerald-100/60">
                    <div class="flex items-center gap-2 text-emerald-900">
                        <x-heroicon-o-information-circle class="w-4 h-4 text-emerald-600 shrink-0" />
                        <span>Barang dari supplier sudah tiba? Catat penerimaan bahan.</span>
                    </div>
                    <a href="{{ route('pengadaan.penerimaan.create', $po->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg text-xs transition-colors shadow-2xs shrink-0">
                        <x-heroicon-o-inbox-arrow-down class="w-3.5 h-3.5" />
                        Terima
                    </a>
                </div>
                @endif
            </div>
        </div>

        {{-- TABLE: DAFTAR BAHAN BAKU PO --}}
        <div class="bg-white rounded-2xl border border-gray-200/80 overflow-hidden shadow-2xs">
            <div class="bg-gray-50/80 px-5 py-4 border-b border-gray-200/80 flex justify-between items-center">
                <div class="flex items-center gap-2.5">
                    <h3 class="font-bold text-gray-900 text-sm tracking-tight">Daftar Bahan Baku Pesanan</h3>
                    <span class="text-xs text-gray-600 font-semibold px-2 py-0.5 bg-gray-200/70 rounded-md">
                        {{ $items->total() ?? $items->count() }} item
                    </span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-xs font-bold text-gray-500 uppercase tracking-wide bg-white">
                            <th class="px-5 py-3.5 text-center w-14">No</th>
                            <th class="px-5 py-3.5 text-left">Bahan Baku</th>
                            <th class="px-5 py-3.5 text-right w-36">Jumlah Dipesan</th>
                            <th class="px-5 py-3.5 text-right w-36">Telah Diterima</th>
                            <th class="px-5 py-3.5 text-right w-36">Kekurangan</th>
                            <th class="px-5 py-3.5 text-center w-32">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($items as $i => $detail)
                        @php
                            $satuanBeli = optional($detail->satuan)->singkatan ?? optional($detail->satuan)->nama_satuan ?? \App\Helpers\UnitHelper::getPurchasingUnit($detail->bahan_baku?->satuan);
                            $isLengkap = (float)$detail->sisa <= 0;
                        @endphp
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="px-5 py-4 text-xs text-gray-500 font-semibold text-center align-middle">
                                {{ method_exists($items, 'firstItem') ? ($items->firstItem() + $i) : ($i + 1) }}
                            </td>
                            <td class="px-5 py-4 align-middle">
                                <p class="font-bold text-gray-900 text-sm">{{ optional($detail->bahan_baku)->nama_bahan ?? '-' }}</p>
                                <span class="text-xs font-mono text-gray-400 font-medium">{{ optional($detail->bahan_baku)->id_bahan_baku ?? '-' }}</span>
                            </td>
                            <td class="px-5 py-4 align-middle text-right font-bold text-gray-900 text-sm">
                                {{ \App\Helpers\UnitHelper::formatNumber($detail->jumlah_dipesan) }} <span class="text-xs font-normal text-gray-500">{{ $satuanBeli }}</span>
                            </td>
                            <td class="px-5 py-4 align-middle text-right text-gray-700 font-medium text-sm">
                                {{ \App\Helpers\UnitHelper::formatNumber($detail->sudah_diterima) }} <span class="text-xs font-normal text-gray-500">{{ $satuanBeli }}</span>
                            </td>
                            <td class="px-5 py-4 align-middle text-right font-bold text-sm {{ $isLengkap ? 'text-emerald-600' : 'text-amber-600' }}">
                                {{ \App\Helpers\UnitHelper::formatNumber($detail->sisa) }} <span class="text-xs font-normal {{ $isLengkap ? 'text-emerald-500' : 'text-amber-500' }}">{{ $satuanBeli }}</span>
                            </td>
                            <td class="px-5 py-4 align-middle text-center">
                                @if($isLengkap)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <x-heroicon-s-check-circle class="w-3 h-3 text-emerald-500" />
                                        Lengkap
                                    </span>
                                @elseif($detail->sudah_diterima > 0)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        Sebagian
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-gray-100 text-gray-600 border border-gray-200">
                                        Belum
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-gray-500 text-sm font-medium">Belum ada bahan pada PO ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if(method_exists($items, 'hasPages') && $items->hasPages())
            <div class="px-5 py-3.5 border-t border-gray-100 bg-gray-50/50">
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