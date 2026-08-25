@extends('layouts.pos')

@section('title', 'Pembayaran Selesai')

@section('content')
<div class="relative min-h-[calc(100vh-65px)] w-full bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4 sm:p-6 lg:p-8 font-sans">

    {{-- MAIN 2-COLUMN CONTAINER --}}
    <div class="relative w-full max-w-5xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch animate-in fade-in zoom-in duration-200">
        
        {{-- LEFT COLUMN: STATUS & PAYMENT SUMMARY --}}
        <div class="lg:col-span-6 bg-white rounded-3xl shadow-2xl border border-gray-100 p-6 sm:p-7 flex flex-col justify-between text-center relative">
            
            {{-- Close / Back icon button in top right --}}
            <a href="{{ route('pos.dinein.index') }}"
               class="absolute top-4 right-4 w-9 h-9 rounded-xl text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors flex items-center justify-center cursor-pointer"
               title="Kembali ke Pesanan Dine-In">
                <x-heroicon-o-x-mark class="w-5 h-5" />
            </a>

            <div>
                {{-- Success Badge Icon --}}
                <div class="w-16 h-16 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center mx-auto mb-3.5 shadow-xs">
                    <x-heroicon-o-check-circle class="w-10 h-10 stroke-[2.2]" />
                </div>

                {{-- Title & Subtitle --}}
                <h2 class="text-xl font-black text-gray-900 leading-tight">Pembayaran Selesai!</h2>
                <p class="text-xs text-gray-500 font-medium mt-1">Pembayaran telah lunas dan transaksi selesai dicatat ke sistem.</p>

                @php
                    $pembayaran = $pesanan->pembayaran ? $pesanan->pembayaran->first() : null;
                    $mejaRaw = $pesanan->meja ? ($pesanan->meja->nomor_meja ?? '-') : '-';
                    $mejaStr = str_starts_with($mejaRaw, 'Meja') ? $mejaRaw : ($mejaRaw === '-' ? '-' : 'Meja ' . $mejaRaw);
                    $namaKonsumen = $pesanan->nama_konsumen ?? ($pesanan->pelanggan->nama ?? 'Tamu');
                    $namaKasir = $pesanan->kasir->nama ?? ($pesanan->pelayan->nama ?? (auth()->user()->nama ?? 'Kasir BBC'));
                    $metodeBayarRaw = $pembayaran->metode_pembayaran ?? ($pesanan->metode_bayar ?? 'Tunai');
                    $cleanedMetode = strtolower(str_replace(['_', '-'], ' ', $metodeBayarRaw));
                    $metodeBayar = match($cleanedMetode) {
                        'cash', 'tunai' => 'Tunai',
                        'qris', 'qris manual', 'qris_manual' => 'QRIS',
                        'transfer', 'transfer manual', 'bank transfer', 'bank_transfer' => 'Transfer Bank',
                        'debit', 'kartu debit', 'edc debit', 'edc_debit' => 'Kartu Debit',
                        'kredit', 'kartu kredit' => 'Kartu Kredit',
                        default => ucwords(str_replace('_', ' ', $metodeBayarRaw)),
                    };
                    $totalTagihan = $pesanan->total_tagihan ?? 0;
                    $uangDiterima = $pembayaran->jumlah_bayar ?? $totalTagihan;
                    $kembalian = max(0, $uangDiterima - $totalTagihan);
                @endphp

                {{-- Order Summary Details --}}
                <div class="w-full mt-5 bg-gray-50/80 rounded-2xl p-4 border border-gray-100 space-y-2.5 text-xs text-left">
                    <div class="flex items-center justify-between pb-2 border-b border-gray-200/60">
                        <span class="text-gray-500 font-medium">Kode Pesanan</span>
                        <span class="font-bold text-gray-900 font-mono tracking-tight">{{ $pesanan->id_pesanan ?? ('DIN-' . $pesanan->id) }}</span>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 font-medium">Meja</span>
                        <span class="font-bold text-gray-900">{{ $mejaStr }}</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 font-medium">Nama Konsumen</span>
                        <span class="font-bold text-gray-900">{{ $namaKonsumen }}</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 font-medium">Metode Pembayaran</span>
                        <span class="font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200/60">{{ $metodeBayar }}</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 font-medium">Kasir</span>
                        <span class="font-semibold text-gray-700">{{ $namaKasir }}</span>
                    </div>

                    <div class="flex items-center justify-between pt-2.5 border-t border-gray-200/60">
                        <span class="font-bold text-gray-700">Total Pembayaran</span>
                        <span class="font-black text-base text-primary">Rp {{ number_format($totalTagihan, 0, ',', '.') }}</span>
                    </div>

                    @if(strtolower($metodeBayarRaw) === 'cash' || strtolower($metodeBayarRaw) === 'tunai')
                    <div class="flex items-center justify-between text-[11px] text-gray-500 pt-1">
                        <span>Diterima: Rp {{ number_format($uangDiterima, 0, ',', '.') }}</span>
                        <span>Kembalian: <strong class="text-gray-700">Rp {{ number_format($kembalian, 0, ',', '.') }}</strong></span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="w-full mt-6 space-y-2.5">
                {{-- Button 1: Cetak Struk Pembayaran --}}
                <button type="button"
                        onclick="printReceiptDirect()"
                        class="w-full h-11 bg-primary hover:bg-primary-container text-white font-extrabold text-xs rounded-xl shadow-xs flex items-center justify-center gap-2 transition-all active:scale-[0.98] cursor-pointer">
                    <x-heroicon-o-printer class="w-4 h-4 text-emerald-400" />
                    <span>Cetak Struk Pembayaran</span>
                </button>

                {{-- Button 2: Kembali ke Halaman Sebelumnya --}}
                <a href="{{ route('pos.dinein.index') }}"
                   class="w-full h-11 bg-white hover:bg-gray-50 text-gray-700 border border-gray-200 hover:border-gray-300 font-bold text-xs rounded-xl transition-all shadow-2xs flex items-center justify-center gap-2 active:scale-[0.98] cursor-pointer">
                    <x-heroicon-o-arrow-left class="w-4 h-4 text-gray-500" />
                    <span>Kembali ke Halaman Sebelumnya</span>
                </a>
            </div>

        </div>

        {{-- RIGHT COLUMN: PRATINJAU STRUK CETAK (THERMAL RECEIPT PREVIEW) --}}
        <div class="lg:col-span-6 bg-white rounded-3xl shadow-2xl border border-gray-100 p-5 flex flex-col justify-between">
            
            {{-- Preview Card Header --}}
            <div class="w-full flex items-center justify-between pb-3.5 border-b border-gray-100 shrink-0">
                <div class="flex items-center gap-2">
                    <span class="p-1.5 bg-emerald-50 text-emerald-700 rounded-lg">
                        <x-heroicon-o-document-text class="w-4 h-4" />
                    </span>
                    <div class="text-left">
                        <h3 class="font-bold text-gray-900 text-sm leading-tight">Pratinjau Struk Cetak</h3>
                        <p class="text-[11px] text-gray-400">Format Kertas Thermal 80mm</p>
                    </div>
                </div>
                <button type="button"
                        onclick="printReceiptDirect()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 active:scale-[0.98] text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer"
                        title="Cetak Sekarang">
                    <x-heroicon-o-printer class="w-3.5 h-3.5" />
                    <span>Cetak</span>
                </button>
            </div>

            {{-- Embedded Thermal Paper Frame --}}
            <div class="w-full my-3.5 bg-slate-100/80 p-3 sm:p-4 rounded-2xl flex justify-center items-center overflow-hidden border border-slate-200/60 shadow-inner flex-1 min-h-[460px]">
                <iframe id="receiptPreviewIframe"
                        src="{{ route('pos.dinein.print-nota', $pesanan->id) }}?preview=1&auto_print=0&embed=1"
                        class="w-full h-[460px] max-w-[340px] rounded-xl bg-white shadow-md border border-gray-200/90 overflow-y-auto"
                        frameborder="0">
                </iframe>
            </div>

        </div>

    </div>

</div>

<script>
    function printReceiptDirect() {
        const frame = document.getElementById('receiptPreviewIframe');
        if (frame && frame.contentWindow) {
            frame.contentWindow.focus();
            frame.contentWindow.print();
        } else {
            printReceiptPopup('{{ route('pos.dinein.print-nota', $pesanan->id) }}', 'PrintNota_{{ $pesanan->id }}');
        }
    }

    function printReceiptPopup(url, title) {
        const width = 450;
        const height = 650;
        const left = (window.screen.width / 2) - (width / 2);
        const top = (window.screen.height / 2) - (height / 2);
        window.open(url, title, `toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, copyhistory=no, width=${width}, height=${height}, top=${top}, left=${left}`);
    }
</script>
@endsection
