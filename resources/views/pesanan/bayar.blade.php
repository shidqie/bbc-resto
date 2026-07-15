<x-layouts.landing>
    <x-slot:title>Pembayaran DP — Saung Babakan Cinta</x-slot:title>

    <section class="py-16 bg-canvas min-h-screen">
        <div class="max-w-3xl mx-auto px-4">
            
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <h1 class="text-3xl font-serif text-primary mb-2">Pesanan Berhasil Dibuat!</h1>
                <p class="text-body">Silakan lakukan pembayaran DP untuk mengonfirmasi pesanan Anda.</p>
            </div>

            <div class="bg-surface rounded-2xl border border-primary/10 shadow-sm overflow-hidden mb-6">
                <div class="bg-primary/5 p-6 border-b border-primary/10">
                    <div class="flex flex-wrap justify-between items-center gap-4">
                        <div>
                            <p class="text-sm text-secondary font-semibold mb-1">Kode Pesanan</p>
                            <p class="text-2xl font-bold text-primary tracking-wider">{{ $pesanan->kode_pesanan }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-secondary font-semibold mb-1">Status</p>
                            <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-bold uppercase tracking-wider">Menunggu DP</span>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <h3 class="font-serif text-lg text-primary mb-4">Ringkasan Pesanan</h3>
                    <div class="space-y-3 text-sm text-body mb-6">
                        <div class="flex justify-between">
                            <span>Nama Pemesan</span>
                            <span class="font-medium">{{ $pesanan->nama_pemesan }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Tanggal Acara</span>
                            <span class="font-medium">{{ $pesanan->tanggal_acara->format('d M Y') }}</span>
                        </div>
                        @if($type === 'catering')
                            <div class="flex justify-between">
                                <span>Paket</span>
                                <span class="font-medium">{{ $pesanan->paket->nama_paket }} ({{ $pesanan->jumlah_porsi }} porsi)</span>
                            </div>
                        @else
                            <div class="flex justify-between">
                                <span>Varian Nasi Box</span>
                                <span class="font-medium">{{ $pesanan->menu->nama }} ({{ $pesanan->jumlah_box }} box)</span>
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
                            <div class="flex flex-col gap-1 text-xs">
                                <span class="text-body/70">Alamat / Lokasi Acara:</span>
                                <span class="font-medium">{{ $pesanan->alamat ?? $pesanan->lokasi_acara }}</span>
                            </div>
                        @endif
                        <div class="pt-3 mt-3 border-t border-gray-100 flex justify-between">
                            <span>Total Tagihan</span>
                            <span class="font-medium">Rp {{ number_format($pesanan->total_tagihan, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-base">
                            <span class="font-semibold text-primary">DP yang Harus Dibayar</span>
                            <span class="font-bold text-secondary text-xl">Rp {{ number_format($pesanan->dp_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-6">
                {{-- Instruksi Pembayaran --}}
                <div class="bg-surface rounded-2xl border border-primary/10 p-6 shadow-sm h-fit">
                    <h3 class="font-serif text-lg text-primary mb-4">Instruksi Pembayaran</h3>
                    <p class="text-sm text-body mb-4">Silakan transfer sesuai nominal DP ke rekening berikut:</p>
                    
                    <div class="bg-primary/5 rounded-xl p-4 mb-4 border border-primary/10 text-center">
                        <p class="text-sm font-semibold text-body mb-1">Bank BCA</p>
                        <p class="text-2xl font-bold tracking-widest text-primary mb-1">1234 5678 90</p>
                        <p class="text-sm text-secondary">a.n. Saung Babakan Cinta</p>
                    </div>
                    
                    <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 text-xs p-3 rounded-xl flex gap-2">
                        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <p>Mohon cantumkan <strong>Kode Pesanan</strong> pada berita acara transfer untuk memudahkan proses verifikasi.</p>
                    </div>
                </div>

                {{-- Form Upload Bukti --}}
                <div class="bg-surface rounded-2xl border border-primary/10 p-6 shadow-sm">
                    <h3 class="font-serif text-lg text-primary mb-4">Konfirmasi Pembayaran</h3>
                    
                    <form action="{{ route('pesanan.bukti.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="kode_pesanan" value="{{ $pesanan->kode_pesanan }}">
                        <input type="hidden" name="jenis_pembayaran" value="dp">
                        
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-body mb-2">Upload Bukti Transfer</label>
                            <input type="file" name="file_bukti" accept=".jpg,.jpeg,.png,.pdf" required
                                   class="w-full text-sm text-body file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
                            <p class="text-xs text-secondary mt-2">Format: JPG, PNG, PDF. Maks 2MB.</p>
                        </div>
                        
                        <button type="submit" class="w-full bg-primary hover:bg-primary-dark text-white font-bold py-3 rounded-xl transition-all duration-200">
                            Kirim Bukti Pembayaran
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </section>
</x-layouts.landing>
