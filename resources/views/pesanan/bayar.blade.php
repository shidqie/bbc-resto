<x-layouts.landing>
    @php
        $isPelunasan = in_array($pesanan->status, ['terkonfirmasi', 'menunggu_pelunasan']);
        $amountToPay = $isPelunasan ? max(0, $pesanan->total_tagihan - $pesanan->dp_amount) : $pesanan->dp_amount;
        $payTitle = $isPelunasan ? 'Pembayaran Pelunasan Sisa Tagihan' : 'Pembayaran DP (Uang Muka)';
        $payDesc = $isPelunasan ? 'Silakan lunasi sisa tagihan pesanan Anda untuk melanjutkan ke proses produksi & pengantaran.' : 'Silakan lakukan pembayaran DP untuk mengonfirmasi pesanan Anda.';
    @endphp

    <x-slot:title>{{ $payTitle }} — Saung Babakan Cinta</x-slot:title>

    <section class="py-16 bg-canvas min-h-screen">
        <div class="max-w-3xl mx-auto px-4">
            
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <h1 class="text-3xl font-serif text-primary mb-2">{{ $payTitle }}</h1>
                <p class="text-body">{{ $payDesc }}</p>
            </div>

            <div class="bg-surface rounded-2xl border border-primary/10 shadow-sm overflow-hidden mb-6">
                <div class="bg-primary/5 p-6 border-b border-primary/10">
                    <div class="flex flex-wrap justify-between items-center gap-4">
                        <div>
                            <p class="text-sm text-secondary font-semibold mb-1">Kode Pesanan</p>
                            <p class="text-2xl font-bold text-primary tracking-wider">{{ $pesanan->kode_pesanan }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-secondary font-semibold mb-1">Status Saat Ini</p>
                            <span class="px-3 py-1 {{ $isPelunasan ? 'bg-emerald-100 text-emerald-800' : 'bg-yellow-100 text-yellow-800' }} rounded-full text-xs font-bold uppercase tracking-wider">
                                {{ $isPelunasan ? 'Dikonfirmasi (Menunggu Pelunasan)' : 'Menunggu DP' }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <h3 class="font-serif text-lg text-primary mb-4">Ringkasan Tagihan</h3>
                    <div class="space-y-3 text-sm text-body mb-6">
                        <div class="flex justify-between">
                            <span>Nama Pemesan</span>
                            <span class="font-medium">{{ $pesanan->nama_pemesan }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Tanggal Acara / Pengantaran</span>
                            <span class="font-medium">{{ $pesanan->tanggal_acara->format('d M Y') }}</span>
                        </div>
                        @if($type === 'catering')
                            <div class="flex justify-between">
                                <span>Paket Catering</span>
                                <span class="font-medium">{{ $pesanan->paket->nama_paket ?? 'Catering' }} ({{ $pesanan->jumlah_porsi }} porsi)</span>
                            </div>
                        @else
                            <div class="flex justify-between">
                                <span>Varian Nasi Box</span>
                                <span class="font-medium">{{ $pesanan->paket->nama_paket ?? 'Nasi Box' }} ({{ $pesanan->jumlah_box }} box)</span>
                            </div>
                        @endif
                        <div class="flex justify-between">
                            <span>Metode Pengiriman</span>
                            <span class="font-medium capitalize">{{ $pesanan->metode_pengiriman }}</span>
                        </div>
                        @if($pesanan->metode_pengiriman === 'delivery')
                            <div class="flex justify-between">
                                <span>Ongkos Kirim {{ $pesanan->jarak_km ? '('.$pesanan->jarak_km.' km)' : '' }}</span>
                                <span class="font-medium">Rp {{ number_format($pesanan->ongkos_kirim, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="pt-3 mt-3 border-t border-gray-100 flex justify-between">
                            <span>Total Tagihan Pesanan</span>
                            <span class="font-medium">Rp {{ number_format($pesanan->total_tagihan, 0, ',', '.') }}</span>
                        </div>
                        @if($isPelunasan)
                            <div class="flex justify-between text-emerald-700">
                                <span>DP Terbayar (Sudah Diverifikasi)</span>
                                <span class="font-semibold">Rp {{ number_format($pesanan->dp_amount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between text-base pt-2 border-t border-gray-200">
                            <span class="font-semibold text-primary">{{ $isPelunasan ? 'Sisa Tagihan Pelunasan' : 'DP yang Harus Dibayar' }}</span>
                            <span class="font-bold text-secondary text-xl">Rp {{ number_format($amountToPay, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="max-w-2xl mx-auto">
                {{-- Pembayaran Midtrans --}}
                <div class="bg-surface rounded-2xl border border-primary/10 p-6 shadow-sm text-center">
                    <h3 class="font-serif text-2xl text-primary mb-2">Pilih Metode Pembayaran</h3>
                    <p class="text-sm text-body mb-6">Selesaikan pembayaran <strong class="text-secondary">Rp {{ number_format($amountToPay, 0, ',', '.') }}</strong> secara aman melalui Midtrans (VA, QRIS, E-Wallet, Kartu Kredit).</p>
                    
                    <div id="snap-container" class="w-full"></div>

                    @if(config('app.env') === 'local')
                        <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg text-left">
                            <p class="text-sm text-yellow-800 font-bold mb-2">🛠️ Mode Developer (Localhost Fallback)</p>
                            <p class="text-xs text-yellow-700 mb-3">Karena webhook otomatis Midtrans tidak bisa masuk ke localhost, silakan klik tombol di bawah untuk menyimulasikan pembayaran sukses {{ $isPelunasan ? 'PELUNASAN' : 'DP' }}.</p>
                            <button onclick="triggerLocalhostSuccess()" class="w-full bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2.5 px-4 rounded-xl transition-colors text-sm shadow-sm">
                                Simulasikan Webhook {{ $isPelunasan ? 'Pelunasan LUNAS' : 'DP Sukses' }}
                            </button>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </section>

    @push('scripts')
    <script src="{{ config('midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}" data-client-key="{{ config('midtrans.client_key') }}"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            snap.embed('{{ $pesanan->snap_token }}', {
                embedId: 'snap-container',
                onSuccess: function (result) {
                    triggerLocalhostSuccess();
                },
                onPending: function (result) {
                    triggerLocalhostSuccess();
                },
                onError: function (result) {
                    alert("Pembayaran gagal!");
                },
                onClose: function () {
                    // Canceled
                }
            });
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
                window.location.href = "{{ route('pesanan.status', $pesanan->kode_pesanan) }}";
            });
        }
    </script>
    @endpush
</x-layouts.landing>
