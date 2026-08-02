@extends('layouts.pos')

@section('content')
<div class="h-[calc(100vh-65px)] flex items-center justify-center bg-[#f5f5f0] text-[#111827] font-sans">
    
    <div class="w-full p-6 bg-white rounded-[32px] w-full shadow-lg border border-gray-100 flex flex-col items-center text-center space-y-6 relative overflow-hidden">
        
        {{-- Background Decoration --}}
        <div class="absolute -top-16 -right-16 w-32 h-32 bg-emerald-50 rounded-full blur-2xl"></div>
        <div class="absolute -bottom-16 -left-16 w-32 h-32 bg-emerald-50 rounded-full blur-2xl"></div>

        <div class="relative z-10 text-center">
            <div class="w-20 h-20 bg-emerald-50 text-emerald-500 rounded-full flex items-center justify-center mx-auto mb-5 border-[3px] border-emerald-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            
            <h2 class="text-2xl font-black text-gray-800 tracking-tight">Pembayaran Berhasil!</h2>
            <p class="text-sm font-medium text-gray-500 mt-2">
                Pesanan <span class="font-bold text-[#0F2E23]">#{{ $pesanan->nomor_pesanan ?? 'DIN-'.$pesanan->id }}</span> telah berhasil dibayar.
            </p>
        </div>

        <div class="w-full bg-gray-50 border border-gray-100 rounded-[2.25rem] p-4 space-y-2 relative z-10">
            <div class="flex justify-between text-xs font-bold text-gray-500">
                <span>Total Tagihan</span>
                <span>Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between text-xs font-bold text-gray-500">
                <span>Metode Pembayaran</span>
                <span class="uppercase">{{ optional(optional($pesanan->pembayaran->first())->metode_pembayaran)->nama_metode ?? 'CASH' }}</span>
            </div>
            @if(optional($pesanan->pembayaran->first())->jumlah_bayar > 0)
            <div class="flex justify-between items-center py-2 text-sm text-gray-500 font-medium">
                <span>Dibayar</span>
                <span>Rp {{ number_format(optional($pesanan->pembayaran->first())->jumlah_bayar, 0, ',', '.') }}</span>
            </div>
            @endif
            @if(optional($pesanan->pembayaran->first())->metode_pembayaran_id === 1 && optional($pesanan->pembayaran->first())->jumlah_bayar > $pesanan->total_tagihan)
            <div class="flex justify-between items-center pt-3 mt-1 border-t border-gray-100 text-sm">
                <span class="font-bold text-gray-700">Kembalian</span>
                <span class="font-black text-emerald-600">Rp {{ number_format(optional($pesanan->pembayaran->first())->jumlah_bayar - $pesanan->total_tagihan, 0, ',', '.') }}</span>
                </div>
            @endif
        </div>

        <div class="w-full space-y-3 relative z-10 pt-2">
            
            <div class="grid grid-cols-1 gap-3">
                {{-- Print Button Modal Trigger --}}
                <div x-data="{ showSavePrintModal: false }">
                    <button type="button" @click="showSavePrintModal = true" class="w-full py-3 bg-white hover:bg-gray-50 text-gray-700 border border-gray-200 rounded-3xl font-bold text-sm transition-colors flex items-center justify-center gap-2 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Cetak Struk
                    </button>

                    {{-- Inject Print Modal --}}
                    <div x-show="showSavePrintModal" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-center justify-center p-4 text-left" style="display: none;">
                        <div class="bg-white rounded-[24px] p-6 max-w-lg w-full shadow-2xl border border-gray-100 relative" @click.outside="showSavePrintModal = false">
                            
                            {{-- Modal Header --}}
                            <div class="text-center mb-6 relative">
                                <h3 class="text-lg font-bold text-gray-800">Cetak Struk</h3>
                                <p class="text-xs font-medium text-gray-500 mt-1">Pilih struk yang akan dicetak</p>
                                <button type="button" @click="showSavePrintModal = false" class="absolute right-0 top-0 w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 text-gray-500 transition-colors">
                                    <x-heroicon-o-x-mark class="w-4 h-4" />
                                </button>
                            </div>

                            {{-- List Options --}}
                            <div class="space-y-3">
                                {{-- Option 1: Struk Pelanggan --}}
                                <button type="button" onclick="printSilentIframe('/pos/dinein/pesanan/{{ $pesanan->id }}/print-nota')" class="w-full group bg-white border border-gray-100 hover:border-emerald-200 hover:bg-emerald-50/30 rounded-[2.25rem] p-4 flex items-center justify-between transition-all cursor-pointer">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-gray-50 group-hover:bg-white flex items-center justify-center text-gray-500 group-hover:text-emerald-600 shadow-sm border border-gray-100 transition-colors">
                                        <i class="ph ph-receipt text-xl"></i>
                                        </div>
                                        <span class="font-semibold text-gray-700 group-hover:text-emerald-800 text-sm">Struk Pelanggan</span>
                                    </div>
                                    <x-heroicon-o-chevron-right class="text-gray-300 group-hover:text-emerald-500 w-3 h-3" />
                                </button>

                                {{-- Option 2: Struk Dapur --}}
                                <button type="button" onclick="printSilentIframe('/pos/dinein/pesanan/{{ $pesanan->id }}/print-dapur')" class="w-full group bg-white border border-gray-100 hover:border-emerald-200 hover:bg-emerald-50/30 rounded-[2.25rem] p-4 flex items-center justify-between transition-all cursor-pointer">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-gray-50 group-hover:bg-white flex items-center justify-center text-gray-500 group-hover:text-emerald-600 shadow-sm border border-gray-100 transition-colors">
                                        <i class="ph ph-cooking-pot text-xl"></i>
                                        </div>
                                        <span class="font-semibold text-gray-700 group-hover:text-emerald-800 text-sm">Struk Dapur</span>
                                    </div>
                                    <x-heroicon-o-chevron-right class="text-gray-300 group-hover:text-emerald-500 w-3 h-3" />
                                </button>

                                {{-- Option 3: Struk Checker Pesanan --}}
                                <button type="button" onclick="printSilentIframe('/pos/dinein/pesanan/{{ $pesanan->id }}/print-meja')" class="w-full group bg-white border border-gray-100 hover:border-emerald-200 hover:bg-emerald-50/30 rounded-[2.25rem] p-4 flex items-center justify-between transition-all cursor-pointer">
                                    <div class="flex items-center gap-3">
                                         <div class="w-10 h-10 rounded-full bg-gray-50 group-hover:bg-white flex items-center justify-center text-gray-500 group-hover:text-emerald-600 shadow-sm border border-gray-100 transition-colors">
                                        <i class="ph ph-clipboard-text text-xl"></i>
                                        </div>
                                        <span class="font-semibold text-gray-700 group-hover:text-emerald-800 text-sm">Struk Checker Pesanan</span>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <x-heroicon-o-check class="text-emerald-500 w-3 h-3" />
                                        <x-heroicon-o-chevron-right class="text-gray-300 group-hover:text-emerald-500 w-3 h-3" />
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <a href="{{ route('pos.dinein.index') }}" class="w-full py-3.5 bg-[#0F2E23] hover:bg-[#0a1f17] text-white rounded-3xl font-bold text-sm transition-colors flex items-center justify-center gap-2 text-center shadow-md mt-4">
                Kembali ke Kasir
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </a>

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
