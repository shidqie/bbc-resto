<x-layouts.landing>
    @php
        $lunas = (float) $pesanan->pembayaran->where('status_verifikasi', 'diterima')->sum('jumlah_dibayar');
        $namaPemesan = optional($pesanan->pelanggan)->nama ?? optional($pesanan->jadwal_pesanan)->nama_penerima;
        $paket = $pesanan->detail_pesanan->first();
        $satuan = $type === 'nasi_box' ? 'Box' : 'Porsi';
    @endphp

    <x-slot:title>Pembayaran Berhasil</x-slot:title>

    <div class="min-h-screen bg-surface text-body">
        <header class="py-12 border-b border-gray-100">
            <div class="max-w-6xl mx-auto px-6 md:px-12">
                <h1 class="text-[40px] font-medium leading-tight tracking-tight mb-2 text-primary">Hasil Pembayaran</h1>
                <p class="text-gray-500 text-base font-light">Status terakhir transaksi Anda.</p>
            </div>
        </header>

        <main class="max-w-6xl mx-auto px-6 md:px-12 py-12 lg:py-16">
            <div class="max-w-2xl mx-auto text-center">
                <!-- Ikon Sukses -->
                <div class="w-20 h-20 mx-auto rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mb-6">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                </div>
                <h2 class="text-3xl font-medium text-gray-900 tracking-tight mb-2">Pembayaran Berhasil</h2>
                <p class="text-gray-500 text-sm font-light mb-10">Tagihan Anda telah dibayar lunas. Terima kasih atas kepercayaan Anda!</p>

                <!-- Ringkasan -->
                <div class="text-left border border-gray-200/80 rounded-xl overflow-hidden bg-white shadow-lg shadow-gray-200/50">
                    <div class="p-6 border-b border-gray-100 bg-gray-50/40">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Nomor Invoice</p>
                        <p class="font-['Anonymous_Pro'] text-xl font-medium tracking-wider text-primary">{{ $pesanan->id_pesanan }}</p>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex justify-between items-start gap-4">
                            <div>
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Produk / Layanan</p>
                                <p class="text-base font-medium text-gray-800">{{ $paket->menu->nama_menu ?? 'Paket' }} <span class="text-gray-400 font-light">&times; {{ $paket->jumlah ?? '-' }} {{ $satuan }}</span></p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Pemesan</p>
                                <p class="text-base font-medium text-gray-800">{{ $namaPemesan }}</p>
                            </div>
                        </div>
                        @if($pesanan->jadwal_pesanan)
                        <div class="flex justify-between items-center">
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Tanggal Acara</p>
                            <p class="text-sm font-medium text-gray-800">{{ \Carbon\Carbon::parse($pesanan->jadwal_pesanan->tanggal_acara)->format('d F Y') }}</p>
                        </div>
                        @endif
                        <div class="flex justify-between items-center">
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Status Pesanan</p>
                            <span class="inline-flex items-center px-3.5 py-1 rounded-full text-xs font-bold uppercase tracking-widest bg-emerald-50 text-emerald-700 border border-emerald-200/60">Sudah Dikonfirmasi</span>
                        </div>
                    </div>
                    <div class="p-6 bg-primary text-white flex justify-between items-center">
                        <span class="text-xs font-bold uppercase tracking-widest text-accent">Total Dibayar</span>
                        <span class="text-2xl font-medium tracking-tight">Rp {{ number_format($lunas, 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- Aksi -->
                <div class="mt-8 space-y-3">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <a href="{{ route('pesanan.invoice', $pesanan->id_pesanan) }}" target="_blank" class="flex items-center justify-center gap-2 px-6 py-3.5 bg-primary hover:bg-primary-container text-white font-bold tracking-widest text-xs uppercase rounded-xl transition-all border border-primary">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 16l5 5 5-5M12 3v12"/></svg>
                            Unduh Bukti Pesanan
                        </a>
                        <a href="{{ route('lacak.index', ['kode_pesanan' => $pesanan->id_pesanan]) }}" class="flex items-center justify-center gap-2 px-6 py-3.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold tracking-widest text-xs uppercase rounded-xl transition-all border border-emerald-200/50">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            Lacak Pesanan
                        </a>
                    </div>
                    <a href="{{ route('home') }}" class="inline-flex items-center justify-center gap-2 px-6 py-3 text-primary font-bold tracking-widest text-xs uppercase rounded-xl transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l9-9 9 9M5 10v10a1 1 0 001 1h4v-6h4v6h4a1 1 0 001-1V10"/></svg>
                        Kembali ke Beranda
                    </a>
                </div>
            </div>
        </main>
    </div>
</x-layouts.landing>
