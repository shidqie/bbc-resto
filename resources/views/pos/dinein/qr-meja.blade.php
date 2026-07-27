@extends('layouts.pos')

@section('title', 'Kartu QR Code Meja')

@section('content')
<style>
    @media print {
        .no-print { display: none !important; }
        body, main { background: white !important; padding: 0 !important; margin: 0 !important; overflow: visible !important; }
        .card-qr-stand { 
            break-inside: avoid; 
            page-break-inside: avoid; 
            -webkit-print-color-adjust: exact !important; 
            print-color-adjust: exact !important;
            box-shadow: none !important;
        }
        .print-container {
            padding: 0 !important;
            margin: 0 !important;
            max-width: 100% !important;
        }
    }
    
    .saung-qr-card {
        background: linear-gradient(145deg, #0F2E23 0%, #164032 50%, #0A2219 100%);
    }
</style>

<div class="flex-1 overflow-auto bg-[#f5f5f0] text-gray-800 font-sans">
    <div class="p-4 md:p-6 lg:p-8 max-w-[1400px] mx-auto space-y-6 print-container">
        
        {{-- Header Section (Hidden on Print) --}}
        <div class="no-print bg-white rounded-3xl p-5 md:p-6 border border-gray-200/80 shadow-xs flex flex-wrap items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2 text-xs font-bold text-gray-400">
                    <a href="{{ route('pos.dinein.index') }}" class="hover:text-[#0F2E23]">Point of Sale</a>
                    <span>/</span>
                    <span class="text-[#0F2E23]">QR Scan Menu</span>
                </div>
                <h1 class="text-xl md:text-2xl font-black text-[#0F2E23] flex items-center gap-2.5">
                    <i class="fa-solid fa-qrcode text-emerald-700"></i> QR Scan Menu (Kartu Meja)
                </h1>
                <p class="text-xs text-gray-500 font-medium">Kartu QR Code Meja untuk pemesanan mandiri oleh pelanggan Saung Babakan Cinta</p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('pos.dinein.index') }}" class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-2.5 rounded-2xl font-extrabold text-xs transition-all border border-gray-200">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke POS
                </a>
                <button onclick="window.print()" class="inline-flex items-center gap-2 bg-[#0F2E23] hover:bg-[#0a1f17] text-white px-5 py-2.5 rounded-2xl font-extrabold text-xs transition-all shadow-xs active:scale-95">
                    <i class="fa-solid fa-print text-emerald-400"></i> Cetak Kartu QR Meja
                </button>
            </div>
        </div>

        {{-- QR Stand Cards Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-6 justify-items-center">
            @forelse($mejas as $m)
                @php
                    $qrTargetUrl = route('qr.menu', ['meja' => $m->id]);
                    $qrApiUrl = "https://api.qrserver.com/v1/create-qr-code/?size=350x350&margin=0&data=" . urlencode($qrTargetUrl);
                    $logoUrl = asset('images/logo-saung.png');
                    $cleanNomorMeja = trim(preg_replace('/^meja\s*/i', '', $m->nomor_meja));
                @endphp
                
                <!-- Table Stand Acrylic Card Template -->
                <div class="card-qr-stand saung-qr-card w-full max-w-[300px] aspect-[1/1.55] rounded-3xl overflow-hidden shadow-xl border-4 border-emerald-500/30 flex flex-col justify-between p-5 relative text-white selection:bg-transparent">
                    
                    <!-- Dark Gradient Overlay for Depth -->
                    <div class="absolute inset-0 bg-gradient-to-b from-black/20 via-transparent to-black/40 pointer-events-none"></div>

                    <!-- Decorative Corner Lines -->
                    <div class="absolute top-3 left-3 w-4 h-4 border-t-2 border-l-2 border-amber-400/60 rounded-tl-lg"></div>
                    <div class="absolute top-3 right-3 w-4 h-4 border-t-2 border-r-2 border-amber-400/60 rounded-tr-lg"></div>
                    <div class="absolute bottom-3 left-3 w-4 h-4 border-b-2 border-l-2 border-amber-400/60 rounded-bl-lg"></div>
                    <div class="absolute bottom-3 right-3 w-4 h-4 border-b-2 border-r-2 border-amber-400/60 rounded-br-lg"></div>

                    <!-- 1. Header Section -->
                    <div class="relative z-10 text-center pt-1 space-y-0.5">
                        <h2 class="text-2xl font-black uppercase tracking-wider text-amber-400 drop-shadow-md leading-none">
                            SCAN MENU
                        </h2>

                        <!-- Table Number Badge -->
                        <div class="pt-2">
                            <span class="inline-flex items-center gap-1.5 px-3.5 py-0.5 rounded-full bg-white/15 backdrop-blur-md text-white border border-amber-400/40 text-[12px] font-extrabold shadow-sm">
                                <i class="fa-solid fa-chair text-[10px] text-amber-400"></i> Meja {{ $cleanNomorMeja }}
                            </span>
                        </div>
                    </div>

                    <!-- 2. QR Code Frame Section -->
                    <div class="relative z-10 my-auto py-1 flex flex-col items-center">
                        <div class="bg-white rounded-3xl p-3.5 shadow-2xl border-4 border-amber-400/50 relative flex items-center justify-center transform transition-transform hover:scale-[1.02]">
                            <!-- QR Image -->
                            <img src="{{ $qrApiUrl }}" alt="QR Code Meja {{ $m->nomor_meja }}" class="w-44 h-44 object-contain rounded-xl">
                            
                            <!-- Center Saung Logo Badge -->
                            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                <div class="w-11 h-11 rounded-full bg-white p-1 shadow-xl border-2 border-emerald-800 flex items-center justify-center overflow-hidden">
                                    <img src="{{ $logoUrl }}" alt="Logo Saung" class="w-full h-full object-contain">
                                </div>
                            </div>
                        </div>

                        <!-- Instruction Subtitle -->
                        <div class="mt-3 text-center">
                            <p class="text-[11px] font-bold text-white tracking-wide drop-shadow-xs">
                                Scan QR Code untuk melihat menu
                            </p>
                            <p class="text-[9px] font-medium text-amber-300 mt-0.5">
                                Arahkan kamera HP Anda memesan langsung
                            </p>
                        </div>
                    </div>

                    <!-- 3. Bottom Brand Footer with Official Logo -->
                    <div class="relative z-10 text-center pb-1 pt-1.5 border-t border-amber-400/30 flex items-center justify-center gap-2">
                        <div class="w-7 h-7 rounded-full bg-white/10 backdrop-blur-md p-1 flex items-center justify-center border border-amber-400/40 shrink-0">
                            <img src="{{ $logoUrl }}" alt="Logo Saung Babakan Cinta" class="w-full h-full object-contain">
                        </div>
                        <div class="text-left">
                            <h3 class="text-[11px] font-black tracking-wider text-white uppercase leading-none">
                                SAUNG BABAKAN CINTA
                            </h3>
                            <span class="text-[8px] font-semibold text-amber-300 block leading-tight mt-0.5">
                                Rumah Makan Khas Sunda
                            </span>
                        </div>
                    </div>

                </div>
            @empty
                <div class="col-span-full py-16 text-center text-gray-400 bg-white rounded-3xl border border-gray-200 w-full shadow-xs">
                    <i class="fa-solid fa-qrcode text-3xl mb-2 text-gray-300"></i>
                    <p class="text-sm font-semibold text-gray-700">Belum ada data meja.</p>
                    <p class="text-xs text-gray-400 mt-1">Tambahkan meja di sistem untuk membuat QR Code.</p>
                </div>
            @endforelse
        </div>

    </div>
</div>
@endsection
