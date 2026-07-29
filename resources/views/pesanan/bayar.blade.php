<x-layouts.landing>
    @php
        $isPelunasan = in_array($pesanan->status, ['terkonfirmasi', 'menunggu_pelunasan']);
        $amountToPay = $isPelunasan ? max(0, $pesanan->total_tagihan - $pesanan->dp_amount) : $pesanan->dp_amount;
        $payTitle = $isPelunasan ? 'Pelunasan Tagihan' : 'Pembayaran Uang Muka';
        $payDesc = $isPelunasan ? 'Selesaikan pembayaran untuk memulai proses pengiriman.' : 'Selesaikan pembayaran awal untuk mengonfirmasi pesanan Anda.';
    @endphp

    <x-slot:title>{{ $payTitle }}</x-slot:title>

    <div class="min-h-screen bg-[#FFFFFF] text-[#111827] font-['Google_Sans'] selection:bg-[#3B82F6] selection:text-white">
        <!-- Minimalist Header -->
        <header class="py-12 border-b border-gray-100">
            <div class="max-w-6xl mx-auto px-6 md:px-12 flex justify-between items-end">
                <div>
                    <h1 class="text-[40px] font-medium leading-tight tracking-tight mb-2 text-[#0F2E23]">{{ $payTitle }}</h1>
                    <p class="text-gray-500 text-[16px] font-light">{{ $payDesc }}</p>
                </div>
                <div class="hidden sm:block text-right">
                    <div class="font-['Anonymous_Pro'] text-[14px] text-gray-400 mb-1">INVOICE NO.</div>
                    <div class="font-['Anonymous_Pro'] text-[18px] font-medium tracking-wider text-gray-800">{{ $pesanan->kode_pesanan }}</div>
                </div>
            </div>
        </header>

        <main class="max-w-6xl mx-auto px-6 md:px-12 py-12 lg:py-16 font-['Google_Sans']">
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16">
                
                <!-- Left Column: Order Summary & Billing -->
                <div class="space-y-10">
                    <!-- Order Details Section -->
                    <div>
                        <h2 class="text-xl font-medium mb-6 text-gray-900 border-b border-gray-100 pb-3">Ringkasan Pesanan</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-6">
                            <!-- Detail Items -->
                            <div class="space-y-1">
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Pemesan</p>
                                <p class="text-base font-medium text-gray-800">{{ $pesanan->nama_pemesan }}</p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Layanan</p>
                                @if($type === 'catering')
                                    <p class="text-base font-medium text-gray-800">{{ $pesanan->paket->nama_paket ?? 'Catering' }} <span class="text-gray-400 font-light">&times; {{ $pesanan->jumlah_porsi }} Porsi</span></p>
                                @else
                                    <p class="text-base font-medium text-gray-800">{{ $pesanan->paket->nama_paket ?? 'Nasi Box' }} <span class="text-gray-400 font-light">&times; {{ $pesanan->jumlah_box }} Box</span></p>
                                @endif
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Tanggal Acara</p>
                                <p class="text-base font-medium text-gray-800">{{ $pesanan->tanggal_acara->format('d F Y') }}</p>
                                @if($isPelunasan)
                                <p class="text-xs text-amber-600 font-medium mt-0.5">Batas Pelunasan: {{ $pesanan->tanggal_acara->subDays(2)->format('d F Y') }} (H-2)</p>
                                @endif
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Status Pesanan</p>
                                <span class="inline-flex items-center px-3.5 py-1 rounded-full text-xs font-bold uppercase tracking-widest bg-amber-50 text-amber-700 border border-amber-200/60">
                                    {{ $isPelunasan ? 'Menunggu Pelunasan' : 'Menunggu DP' }}
                                </span>
                            </div>
                        </div>

                        <!-- Menu Details -->
                        <div class="mt-8 pt-6 border-t border-gray-100">
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Detail Menu Pilihan</p>
                            <ul class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @foreach($pesanan->details as $detail)
                                    <li class="flex items-start gap-2.5 text-sm text-gray-700">
                                        <div class="w-4 h-4 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 mt-0.5">
                                            <i class="ph-bold ph-check text-[10px]"></i>
                                        </div>
                                        <span class="leading-relaxed">{{ $detail->menu->nama ?? 'Menu Tidak Ditemukan' }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    <!-- Billing Breakdown -->
                    <div class="pt-4">
                        <h2 class="text-xl font-medium mb-6 text-gray-900 border-b border-gray-100 pb-3">Rincian Pembayaran</h2>
                        <div class="space-y-3.5 text-sm text-gray-600">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-500">Total Tagihan</span>
                                <span class="text-gray-900 font-medium text-base">Rp {{ number_format($pesanan->total_tagihan, 0, ',', '.') }}</span>
                            </div>

                            @if($pesanan->metode_pengiriman === 'delivery' && isset($pesanan->ongkos_kirim) && $pesanan->ongkos_kirim > 0)
                            <div class="flex justify-between items-center">
                                <span class="text-gray-500">Ongkos Kirim</span>
                                <span class="text-gray-900 font-medium">Rp {{ number_format($pesanan->ongkos_kirim, 0, ',', '.') }}</span>
                            </div>
                            @endif

                            @if($isPelunasan)
                            <div class="flex justify-between items-center text-emerald-600">
                                <span>DP Terbayar</span>
                                <span class="font-medium">- Rp {{ number_format($pesanan->dp_amount, 0, ',', '.') }}</span>
                            </div>
                            @endif
                        </div>
                        
                        <div class="flex justify-between items-center p-5 mt-6 bg-blue-50/40 border border-blue-100/60 rounded-2xl">
                            <span class="text-xs text-blue-700 font-bold uppercase tracking-widest">
                                {{ $isPelunasan ? 'Sisa Pembayaran' : 'Total Pembayaran' }}
                            </span>
                            <span class="text-2xl sm:text-3xl font-medium text-[#3B82F6] tracking-tight">Rp {{ number_format($amountToPay, 0, ',', '.') }}</span>
                        </div>

                        <!-- Download Invoice Button -->
                        <div class="mt-6 space-y-2">
                            <a href="{{ route('pesanan.invoice', $pesanan->kode_pesanan) }}" target="_blank" class="w-full flex items-center justify-center gap-2 px-6 py-3.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold tracking-widest text-xs uppercase rounded-xl transition-all border border-emerald-200/50">
                                <i class="ph-bold ph-download-simple text-base"></i>
                                Unduh Bukti Pesanan
                            </a>
                            <p class="text-[11px] text-amber-600 font-medium text-center">
                                *Silakan unduh dokumen ini sebagai bukti pesanan Anda.
                            </p>
                            <a href="{{ route('lacak.index', ['kode_pesanan' => $pesanan->kode_pesanan]) }}" class="w-full flex items-center justify-center gap-2 px-6 py-3.5 bg-[#0F2E23] hover:bg-[#164032] text-white font-bold tracking-widest text-xs uppercase rounded-xl transition-all border border-[#0F2E23]">
                                <i class="ph-bold ph-magnifying-glass text-base"></i>
                                Lacak / Cek Status Pesanan
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Payment Widget (Midtrans Snap Embed) -->
                <div class="lg:sticky lg:top-8 h-full">
                    <div class="w-full flex flex-col h-full">
                        <!-- Midtrans Snap Container -->
                        <div id="snap-container" class="w-full flex-1 min-h-[550px] border border-gray-200/80 rounded-2xl overflow-hidden bg-white shadow-lg shadow-gray-200/50 relative z-0">
                            <!-- Loading State (will be behind the iframe) -->
                            <div class="absolute inset-0 flex flex-col items-center justify-center text-gray-400 z-[-1] pointer-events-none p-8 text-center">
                                <i class="ph-bold ph-spinner-gap animate-spin text-4xl mb-4 text-[#3B82F6]"></i>
                                <span class="text-sm font-medium text-gray-600">Memuat Sistem Pembayaran Midtrans...</span>
                                <span class="text-xs text-gray-400 mt-1">Harap tunggu sebentar</span>
                            </div>
                        </div>

                        @if(config('app.env') === 'local')
                            <div class="mt-4 text-center">
                                <button onclick="triggerLocalhostSuccess()" class="w-full text-xs font-bold tracking-widest uppercase text-emerald-700 hover:text-white hover:bg-emerald-600 transition-all bg-emerald-50 px-4 py-3 rounded-xl border border-emerald-200">
                                    Simulasikan Pembayaran Berhasil (Dev Mode)
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

            </div>

        </main>
    </div>

    @push('scripts')
    <script src="{{ config('midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}" data-client-key="{{ config('midtrans.client_key') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Check if snap token exists
            let snapToken = '{{ $pesanan->snap_token }}';
            
            if (snapToken) {
                window.snap.embed(snapToken, {
                    embedId: 'snap-container',
                    onSuccess: function(result){
                        triggerLocalhostSuccess();
                    },
                    onPending: function(result){
                        triggerLocalhostSuccess();
                    },
                    onError: function(result){
                        alert("Pembayaran gagal!");
                    },
                    onClose: function(){
                        // Optional: Handle if user closes the snap frame (though it's embedded)
                    }
                });
            } else {
                document.getElementById('snap-container').innerHTML = '<div class="p-6 text-center text-red-500 font-bold">Gagal memuat token pembayaran. Silakan muat ulang halaman.</div>';
            }
        });

        function triggerLocalhostSuccess() {
            fetch('/api/midtrans/localhost-fallback', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ kode_pesanan: '{{ $pesanan->kode_pesanan }}' })
            }).then(() => {
                window.location.href = "{{ route('lacak.index', ['kode_pesanan' => $pesanan->kode_pesanan]) }}";
            });
        }
    </script>
    @endpush
</x-layouts.landing>
