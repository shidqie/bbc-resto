@extends('layouts.pos')

@section('content')
<div class="h-[calc(100vh-65px)] flex flex-col bg-[#f5f5f0] text-[#111827] font-sans">

    <div class="w-full px-6 pt-6">
        <x-ui.page-header title="Pembayaran Berhasil!" subtitle="Pesanan #{{ $pesanan->nomor_pesanan ?? 'DIN-'.$pesanan->id }} telah berhasil dibayar." :breadcrumbs="['Penjualan', 'Dine In', 'Sukses']" />
    </div>

    <div class="flex-1 flex items-center justify-center p-6">

    <div class="w-full bg-white rounded-xl shadow-lg border border-gray-100 flex flex-col items-center text-center space-y-6 relative overflow-hidden">
        
        {{-- Background Decoration --}}
        <div class="absolute -top-16 -right-16 w-32 h-32 bg-emerald-50 rounded-full blur-2xl"></div>
        <div class="absolute -bottom-16 -left-16 w-32 h-32 bg-emerald-50 rounded-full blur-2xl"></div>

        <div class="relative z-10 text-center">
            <div class="w-20 h-20 bg-emerald-50 text-emerald-500 rounded-full flex items-center justify-center mx-auto mb-2 border-[3px] border-emerald-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>
        </div>

        <div class="w-full bg-gray-50 border border-gray-100 rounded-xl p-4 space-y-2 relative z-10">
            <div class="flex justify-between text-xs font-bold text-gray-500">
                <span>Total Tagihan</span>
                <span>Rp {{ number_format($pesanan->total_tagihan, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between text-xs font-bold text-gray-500">
                <span>Metode Pembayaran</span>
                @php
                    $metode = optional($pesanan->pembayaran->first())->metode_pembayaran;
                    $metodeText = ($metode === 'qris_manual') ? 'QRIS' : (($metode === 'tunai') ? 'TUNAI' : strtoupper($metode ?? 'CASH'));
                @endphp
                <span class="uppercase">{{ $metodeText }}</span>
            </div>
            @if(optional($pesanan->pembayaran->first())->jumlah_bayar > 0)
            <div class="flex justify-between items-center py-2 text-sm text-gray-500 font-medium">
                <span>Dibayar</span>
                <span>Rp {{ number_format(optional($pesanan->pembayaran->first())->jumlah_bayar, 0, ',', '.') }}</span>
            </div>
            @endif
            @if(optional($pesanan->pembayaran->first())->metode_pembayaran === 'tunai' && optional($pesanan->pembayaran->first())->jumlah_bayar > $pesanan->total_tagihan)
            <div class="flex justify-between items-center pt-3 mt-1 border-t border-gray-100 text-sm">
                <span class="font-bold text-gray-700">Kembalian</span>
                <span class="font-black text-emerald-600">Rp {{ number_format(optional($pesanan->pembayaran->first())->jumlah_bayar - $pesanan->total_tagihan, 0, ',', '.') }}</span>
            </div>
            @endif
        </div>

        <div class="w-full space-y-3 relative z-10 pt-2">
            

            <a href="{{ route('pos.dinein.index') }}" class="w-full py-3.5 bg-[#0D3024] hover:bg-[#0a1f17] text-white rounded-xl font-bold text-sm transition-colors flex items-center justify-center gap-2 text-center shadow-md mt-4">
                Kembali ke Kasir
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </a>

        </div>
    </div>
    </div>
</div>

<script>
    function printSilentIframe(url) {
        let iframe = document.getElementById('silent-print-iframe');
        if (!iframe) {
            iframe = document.createElement('iframe');
            iframe.id = 'silent-print-iframe';
            iframe.style.display = 'none';
            document.body.appendChild(iframe);
        }
        iframe.src = url;
        iframe.onload = function() {
            setTimeout(() => {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
            }, 500);
        };
    }
</script>
@endsection
