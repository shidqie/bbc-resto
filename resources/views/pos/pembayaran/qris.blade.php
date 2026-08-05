@extends('layouts.pos')

@section('content')
<div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8 font-sans antialiased text-[#111827]">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-100 mb-4 shadow-sm">
                <x-heroicon-o-qr-code class="w-8 h-8 text-[#3B82F6]" />
            </div>
            <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">Pembayaran QRIS</h2>
            <p class="mt-2 text-sm text-gray-500 font-medium">Scan QR Code di bawah menggunakan aplikasi E-Wallet atau M-Banking Anda.</p>
        </div>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md" 
         x-data="qrisPayment()" 
         x-init="initPayment()">
        
        <div class="bg-white py-8 px-4 shadow-xl sm:rounded-2xl sm:px-10 border border-gray-100 relative overflow-hidden">
            
            <!-- Decorator -->
            <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-[#3B82F6] to-emerald-400"></div>

            <div class="space-y-6">
                <!-- Total Amount -->
                <div class="text-center pb-6 border-b border-gray-100">
                    <p class="text-sm font-bold text-gray-500 uppercase tracking-wide">Total Tagihan</p>
                    <p class="mt-1 text-4xl font-black text-[#0D3024]">Rp{{ number_format($pembayaran->jumlah_bayar, 0, ',', '.') }}</p>
                    <p class="mt-2 text-xs font-semibold text-gray-400">ID Pesanan: <span class="text-gray-600 font-mono font-bold">#{{ optional($pembayaran->pesanan)->nomor_pesanan ?? $pembayaran->pesanan_id }}</span></p>
                </div>

                <!-- QR Code Display -->
                <div class="flex justify-center py-4">
                    <div class="relative p-4 bg-white rounded-2xl border-2 border-dashed border-gray-200 shadow-sm transition-all hover:border-[#3B82F6] hover:shadow-md">
                        @if($pembayaran->qr_code_url)
                            <img src="{{ $pembayaran->qr_code_url }}" alt="QRIS Code" class="w-64 h-64 object-contain">
                            
                            <!-- Success Overlay -->
                            <div x-show="status === 'success'" 
                                 x-transition.opacity.duration.500ms
                                 class="absolute inset-0 bg-white/90 backdrop-blur-sm rounded-2xl flex flex-col items-center justify-center" style="display: none;">
                                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-3 text-green-500 shadow-sm">
                                    <x-heroicon-o-check-circle class="w-10 h-10 font-bold" />
                                </div>
                                <p class="text-lg font-black text-green-600 tracking-tight">Pembayaran Lunas!</p>
                            </div>
                        @else
                            <div class="w-64 h-64 flex items-center justify-center bg-gray-50 rounded-xl text-gray-400">
                                <span class="text-sm font-medium">QR Code tidak tersedia</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Expiry Countdown -->
                <div class="text-center bg-orange-50 rounded-xl p-4 border border-orange-100">
                    <p class="text-xs font-bold text-orange-600 uppercase tracking-wider mb-1">Menunggu Pembayaran</p>
                    <div class="text-2xl font-mono font-bold text-orange-700" x-text="formatTime(timeLeft)">00:00</div>
                    <p class="text-xs text-orange-500 mt-1 font-medium">Harap selesaikan sebelum waktu habis</p>
                </div>

                <!-- Actions -->
                <div class="flex flex-col gap-3 pt-2">
                    <button @click="checkStatusManual" 
                            type="button" 
                            class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-[#0D3024] hover:bg-[#0a2018] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0D3024] transition-all">
                        <svg x-show="isChecking" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <x-heroicon-o-arrow-path x-show="!isChecking" class="w-5 h-5 mr-2" />
                        <span x-text="isChecking ? 'Mengecek...' : 'Cek Status Pembayaran'"></span>
                    </button>
                    
                    <a href="{{ route('pos.dinein.checkout', $pembayaran->pesanan->meja_id ?? $pembayaran->pesanan_id) }}" 
                       class="w-full flex justify-center py-3 px-4 border border-gray-300 rounded-xl shadow-sm text-sm font-bold text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#3B82F6] transition-all">
                        Batalkan Transaksi
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function qrisPayment() {
        return {
            status: 'pending',
            timeLeft: 0,
            expiryTime: new Date('{{ $pembayaran->expired_at }}').getTime(),
            timerInterval: null,
            pollingInterval: null,
            isChecking: false,

            initPayment() {
                this.startTimer();
                this.startPolling();
            },

            startTimer() {
                this.updateTimeLeft();
                this.timerInterval = setInterval(() => {
                    this.updateTimeLeft();
                }, 1000);
            },

            updateTimeLeft() {
                const now = new Date().getTime();
                const distance = this.expiryTime - now;

                if (distance < 0) {
                    clearInterval(this.timerInterval);
                    this.timeLeft = 0;
                    this.status = 'expired';
                    Swal.fire({
                        icon: 'error',
                        title: 'Waktu Habis',
                        text: 'Waktu pembayaran telah kedaluwarsa.',
                        confirmButtonColor: '#0D3024'
                    }).then(() => {
                        window.location.href = "{{ route('pos.dinein.checkout', $pembayaran->pesanan->meja_id ?? $pembayaran->pesanan_id) }}";
                    });
                } else {
                    this.timeLeft = Math.floor(distance / 1000);
                }
            },

            formatTime(seconds) {
                const m = Math.floor(seconds / 60);
                const s = seconds % 60;
                return `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
            },

            startPolling() {
                this.pollingInterval = setInterval(() => {
                    this.checkStatus(false);
                }, 3000); // Poll every 3 seconds
            },

            async checkStatusManual() {
                this.isChecking = true;
                await this.checkStatus(true);
                this.isChecking = false;
            },

            async checkStatus(manual = false) {
                if (this.status === 'success' || this.status === 'expired') return;

                try {
                    const response = await fetch(`/pos/dinein/pembayaran/{{ $pembayaran->id }}/status`);
                    const data = await response.json();

                    if (data.status === 'success') {
                        this.status = 'success';
                        clearInterval(this.pollingInterval);
                        clearInterval(this.timerInterval);
                        
                        setTimeout(() => {
                            window.location.href = `/pos/dinein/success/${data.pesanan_id}`;
                        }, 2000);
                    } else if (manual) {
                        Swal.fire({
                            icon: 'info',
                            title: 'Belum Lunas',
                            text: 'Pembayaran Anda belum kami terima. Silakan coba lagi.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                } catch (error) {
                    console.error("Gagal mengecek status:", error);
                }
            }
        }
    }
</script>
@endsection
