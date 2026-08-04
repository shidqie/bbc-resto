<x-layouts.landing>
    @php
        $dpAmount = $pesanan->nominalDP();
        $dpPersen = $pesanan->persentaseDP();
        $dpTerbayar = (float) $pesanan->pembayaran->whereIn('status_pembayaran_id', [2, 3])->sum('jumlah_bayar');
        $lunas = (float) $pesanan->pembayaran->where('status_pembayaran_id', 3)->sum('jumlah_bayar');
        $isPelunasan = $lunas >= $dpAmount && $lunas < $pesanan->total_tagihan;
        $amountToPay = $isPelunasan ? max(0, $pesanan->total_tagihan - $lunas) : max(0, $dpAmount - $dpTerbayar);
        $payTitle = $isPelunasan ? 'Pelunasan Tagihan' : 'Pembayaran Uang Muka';
        $payDesc = $isPelunasan ? 'Selesaikan pembayaran untuk memulai proses pengiriman.' : 'Selesaikan pembayaran awal untuk mengonfirmasi pesanan Anda.';
        $namaPemesan = optional($pesanan->pelanggan)->nama ?? optional($pesanan->jadwal_pesanan)->nama_penerima;
        $paket = $pesanan->detail_pesanan->first();
        $satuan = $type === 'nasi_box' ? 'Box' : 'Porsi';
        $transaksi = \App\Models\PaymentTransaction::where('din_number', $pesanan->nomor_pesanan)->latest()->first();
        $batasWaktu = $transaksi?->created_at?->addMinutes(30);
        $transStatus = $transaksi?->transaction_status;
        $transaksiExpired = in_array($transStatus, ['expire', 'deny', 'cancel']);
    @endphp

    <x-slot:title>{{ $payTitle }}</x-slot:title>

    <div class="min-h-screen bg-[#FFFFFF] text-[#111827] selection:bg-[#3B82F6] selection:text-white">
        <!-- Minimalist Header -->
        <header class="py-12 border-b border-gray-100">
            <div class="max-w-6xl mx-auto px-6 md:px-12 flex justify-between items-end">
                <div>
                    <h1 class="text-[40px] font-medium leading-tight tracking-tight mb-2 text-[#0D3024]">{{ $payTitle }}</h1>
                    <p class="text-gray-500 text-base font-light">{{ $payDesc }}</p>
                </div>
                <div class="hidden sm:block text-right">
                    <div class="font-['Anonymous_Pro'] text-sm text-gray-400 mb-1">NO. INVOICE</div>
                    <div class="font-['Anonymous_Pro'] text-lg font-medium tracking-wider text-gray-800">{{ $pesanan->nomor_pesanan }}</div>
                </div>
            </div>
        </header>

        <main class="max-w-6xl mx-auto px-6 md:px-12 py-12 lg:py-16">

            @if($transaksiExpired)
                <div class="mb-8 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm font-medium flex items-start gap-2">
                    <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Pembayaran sebelumnya kedaluwarsa atau gagal. Tekan tombol "Muat Ulang Pembayaran" di bawah untuk membuat pembayaran baru.
                </div>
            @endif
            @if(session('success'))
                <div class="mb-8 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm font-medium">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="mb-8 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm font-medium">{{ session('error') }}</div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-5 gap-10 lg:gap-12">

                <!-- LEFT: Ringkasan Transaksi & Metode Pembayaran -->
                <div class="lg:col-span-3 space-y-10">

                    <!-- 1. Ringkasan Transaksi -->
                    <div>
                        <h2 class="text-xl font-medium mb-6 text-gray-900 border-b border-gray-100 pb-3">Ringkasan Transaksi</h2>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-5">
                            <div class="space-y-1">
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Nomor Invoice</p>
                                <p class="font-['Anonymous_Pro'] text-base font-medium text-gray-800">{{ $pesanan->nomor_pesanan }}</p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Produk / Layanan</p>
                                <p class="text-base font-medium text-gray-800">{{ $paket->menu->nama_menu ?? 'Paket' }} <span class="text-gray-400 font-light">&times; {{ $paket->jumlah ?? '-' }} {{ $satuan }}</span></p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Pemesan</p>
                                <p class="text-base font-medium text-gray-800">{{ $namaPemesan }}</p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Keperluan Pembayaran</p>
                                <p class="text-base font-medium text-gray-800">{{ $isPelunasan ? 'Pelunasan Tagihan' : 'Uang Muka '.$dpPersen.'%' }}</p>
                            </div>
                            @if($pesanan->jadwal_pesanan)
                            <div class="space-y-1">
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Tanggal Acara</p>
                                <p class="text-base font-medium text-gray-800">{{ \Carbon\Carbon::parse($pesanan->jadwal_pesanan->tanggal_acara)->format('d F Y') }}</p>
                            </div>
                            @endif
                            <div class="space-y-1">
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Status Pembayaran</p>
                                @if($transaksiExpired)
                                    <span class="inline-flex items-center px-3.5 py-1 rounded-full text-xs font-bold uppercase tracking-widest bg-red-50 text-red-700 border border-red-200/60">Kedaluwarsa / Gagal</span>
                                @else
                                    <span class="inline-flex items-center px-3.5 py-1 rounded-full text-xs font-bold uppercase tracking-widest bg-amber-50 text-amber-700 border border-amber-200/60">
                                        <span class="w-1.5 h-1.5 bg-amber-500 rounded-full mr-1.5 animate-pulse"></span>
                                        {{ $isPelunasan ? 'Menunggu Pelunasan' : 'Menunggu Pembayaran' }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Jumlah Dibayar — fokus utama -->
                        <div class="mt-6 p-6 rounded-xl bg-[#0D3024] text-white relative overflow-hidden">
                            <div class="absolute -right-8 -top-8 w-40 h-40 rounded-full bg-white/5"></div>
                            <div class="absolute right-10 -bottom-10 w-28 h-28 rounded-full bg-white/5"></div>
                            <p class="text-xs font-bold uppercase tracking-widest text-[#D4A843] mb-2">
                                {{ $isPelunasan ? 'Sisa Pembayaran' : 'Total Yang Harus Dibayar' }}
                            </p>
                            <p class="text-4xl sm:text-5xl font-medium tracking-tight">Rp {{ number_format($amountToPay, 0, ',', '.') }}</p>
                            <div class="mt-4 flex flex-wrap items-center gap-x-6 gap-y-1 text-xs text-white/60">
                                <span>Total Tagihan: <strong class="text-white/90 font-medium">Rp {{ number_format($pesanan->total_tagihan, 0, ',', '.') }}</strong></span>
                                @if($dpTerbayar > 0)
                                    <span>Sudah Dibayar: <strong class="text-white/90 font-medium">- Rp {{ number_format($dpTerbayar, 0, ',', '.') }}</strong></span>
                                @endif
                            </div>
                        </div>

                        <!-- Batas Waktu Pembayaran (Countdown) -->
                        <div class="mt-4 flex items-center justify-between gap-4 p-5 rounded-xl border {{ $transaksiExpired ? 'border-red-200 bg-red-50/40' : 'border-gray-200/80 bg-gray-50/40' }}">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full {{ $transaksiExpired ? 'bg-red-100 text-red-600' : 'bg-[#0D3024]/5 text-[#0D3024]' }} flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Batas Waktu Pembayaran</p>
                                    <p class="text-sm text-gray-600">{{ $transaksiExpired ? 'Pembayaran kedaluwarsa. Silakan buat pembayaran baru.' : 'VA & QRIS berlaku hingga waktu habis.' }}</p>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                @if($batasWaktu)
                                    <div id="timer" class="font-['Anonymous_Pro'] text-3xl font-medium tracking-widest {{ $transaksiExpired ? 'text-red-600' : 'text-[#0D3024]' }}">--:--</div>
                                    <p id="timer-label" class="text-xs font-bold uppercase tracking-widest text-gray-400 mt-0.5">Sisa Waktu</p>
                                @else
                                    <div class="font-['Anonymous_Pro'] text-3xl font-medium tracking-widest text-[#0D3024]">--:--</div>
                                @endif
                            </div>
                        </div>

                        <!-- Detail Menu Pilihan -->
                        <div class="mt-8 pt-6 border-t border-gray-100">
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Detail Menu Pilihan</p>
                            <ul class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @forelse($pesanan->detail_pesanan as $detail)
                                    @forelse($detail->pilihan_pesanan_catering as $pilihan)
                                        <li class="flex items-start gap-2.5 text-sm text-gray-700">
                                            <div class="w-4 h-4 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 mt-0.5">
                                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                            </div>
                                            <span class="leading-relaxed">
                                                <span class="text-gray-400">{{ $pilihan->komponen_paket->nama_komponen }}:</span>
                                                <strong>{{ $pilihan->pilihan_komponen_paket->nama_pilihan }}</strong>
                                            </span>
                                        </li>
                                    @empty
                                        <li class="flex items-start gap-2.5 text-sm text-gray-700">
                                            <div class="w-4 h-4 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 mt-0.5">
                                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                            </div>
                                            <span class="leading-relaxed">{{ $detail->menu->nama_menu ?? 'Menu' }}</span>
                                        </li>
                                    @endforelse
                                @empty
                                    <li class="text-sm text-gray-400 italic">Tidak ada detail menu.</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>

                    <!-- 2. Pilih Metode Pembayaran (Snap Embed) -->
                    <div>
                        <h2 class="text-xl font-medium mb-6 text-gray-900 border-b border-gray-100 pb-3">Pilih Metode Pembayaran</h2>
                        <div class="border-2 border-[#0D3024]/15 rounded-xl p-5 bg-emerald-50/40">
                            <div class="flex items-center justify-between mb-1">
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Pembayaran Online — Midtrans</p>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-widest bg-emerald-100 text-emerald-700 border border-emerald-200/60">
                                    <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full mr-1.5"></span>
                                    Instan
                                </span>
                            </div>
                            <p class="text-xs text-gray-500">Pilih salah satu metode: <strong>Virtual Account, e-Wallet, QRIS, atau Kartu Kredit</strong>. Nomor VA / kode QR akan langsung ditampilkan setelah metode dipilih.</p>

                            <div id="snap-container" class="mt-4"></div>

                            <div id="snap-loading" class="mt-4 flex items-center justify-center gap-3 py-10 text-sm text-gray-500">
                                <svg class="w-5 h-5 animate-spin text-[#0D3024]" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                Menyiapkan pembayaran online...
                            </div>

                            <p id="midtrans-status" class="hidden mt-3 text-xs font-medium text-gray-600 text-center"></p>

                            <button type="button" id="btn-retry" onclick="loadSnapEmbed()" class="hidden mt-3 w-full flex items-center justify-center gap-2 px-6 py-2.5 bg-white hover:bg-emerald-50 text-[#0D3024] font-bold tracking-widest text-sm uppercase rounded-xl transition-all border border-[#0D3024]/20">
                                Muat Ulang Pembayaran
                            </button>

                            <div class="mt-4 flex items-start gap-2.5 text-xs text-gray-500">
                                <svg class="w-4 h-4 mt-0.5 shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                <span>Halaman ini otomatis memeriksa status pembayaran setiap beberapa detik — begitu pembayaran masuk, Anda akan dialihkan ke halaman hasil.</span>
                            </div>

                            <div class="mt-3 flex items-center gap-2">
                                <div class="flex-1 h-px bg-gray-200"></div>
                                <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">atau</span>
                                <div class="flex-1 h-px bg-gray-200"></div>
                            </div>
                            <a href="{{ route('pesanan.check-midtrans-status', $pesanan->nomor_pesanan) }}" class="mt-3 w-full flex items-center justify-center gap-2 px-6 py-2.5 bg-white hover:bg-emerald-50 text-[#0D3024] font-bold tracking-widest text-xs uppercase rounded-xl transition-all border border-[#0D3024]/20">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                Cek Status Pembayaran Sekarang
                            </a>
                        </div>
                    </div>

                    <!-- 3. Bayar Manual (Upload Bukti) -->
                    <div>
                        <h2 class="text-xl font-medium mb-6 text-gray-900 border-b border-gray-100 pb-3">Bayar Manual</h2>
                        <form action="{{ route('pesanan.bukti.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 border border-gray-200/80 rounded-xl p-5 bg-gray-50/40">
                            @csrf
                            <input type="hidden" name="kode_pesanan" value="{{ $pesanan->nomor_pesanan }}">
                            <input type="hidden" name="jenis_pembayaran" value="{{ $isPelunasan ? 'pelunasan' : 'dp' }}">
                            <p class="text-xs text-gray-500">Tidak bisa bayar online? Kirim bukti transfer sebesar <strong class="text-gray-700">Rp {{ number_format($amountToPay, 0, ',', '.') }}</strong> untuk diverifikasi admin (1×24 jam).</p>
                            <input type="file" name="file_bukti" accept="image/*,.pdf" required
                                   class="block w-full text-sm text-gray-600 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:tracking-widest file:uppercase file:bg-blue-600 file:text-white hover:file:bg-blue-700 file:cursor-pointer">
                            @error('file_bukti') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                            <button type="submit" class="w-full flex items-center justify-center gap-2 px-6 py-3.5 bg-[#0D3024] hover:bg-[#164032] text-white font-bold tracking-widest text-sm uppercase rounded-xl transition-all border border-[#0D3024]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 8l5-5m0 0l5 5m-5-5v12"/></svg>
                                Kirim Bukti Pembayaran
                            </button>
                        </form>
                    </div>
                </div>

                <!-- RIGHT: Status & Bantuan -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="lg:sticky lg:top-8 space-y-6">
                        <!-- Status Pembayaran -->
                        <div class="w-full border border-gray-200/80 rounded-xl overflow-hidden bg-white shadow-lg shadow-gray-200/50">
                            <div class="p-6">
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-5">Status Pembayaran</p>
                                <div class="space-y-4">
                                    <div class="flex items-center gap-4">
                                        <div class="w-9 h-9 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-gray-900">Pesanan Diterima</p>
                                            <p class="text-xs text-gray-400">Pesanan telah masuk ke sistem</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-4 {{ $dpTerbayar > 0 ? 'opacity-100' : 'opacity-40' }}">
                                        <div class="w-9 h-9 rounded-full {{ $dpTerbayar > 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-gray-100 text-gray-400' }} flex items-center justify-center shrink-0">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="{{ $dpTerbayar > 0 ? 'M5 13l4 4L19 7' : 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' }}"/></svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-gray-900">DP Dibayar</p>
                                            <p class="text-xs text-gray-400">Uang muka sebesar {{ $dpPersen }}% (Rp {{ number_format($dpAmount, 0, ',', '.') }})</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-4 {{ $lunas >= $pesanan->total_tagihan ? 'opacity-100' : 'opacity-40' }}">
                                        <div class="w-9 h-9 rounded-full {{ $lunas >= $pesanan->total_tagihan ? 'bg-emerald-50 text-emerald-600' : 'bg-gray-100 text-gray-400' }} flex items-center justify-center shrink-0">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="{{ $lunas >= $pesanan->total_tagihan ? 'M5 13l4 4L19 7' : 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' }}"/></svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-gray-900">Lunas</p>
                                            <p class="text-xs text-gray-400">Seluruh tagihan terbayar</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Butuh Bantuan? -->
                        <div class="w-full border border-gray-200/80 rounded-xl p-6 bg-gray-50/40">
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Butuh Bantuan?</p>
                            <p class="text-xs text-gray-500 mb-4">Mengalami kendala saat pembayaran? Hubungi kami via WhatsApp.</p>
                            <a href="https://wa.me/6281394616635" target="_blank" class="w-full flex items-center justify-center gap-2 px-6 py-3 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold tracking-widest text-xs uppercase rounded-xl transition-all border border-emerald-200/50">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                Chat WhatsApp
                            </a>
                            <a href="{{ route('lacak.index', ['kode_pesanan' => $pesanan->nomor_pesanan]) }}" class="mt-3 w-full flex items-center justify-center gap-2 px-6 py-3 bg-[#0D3024] hover:bg-[#164032] text-white font-bold tracking-widest text-xs uppercase rounded-xl transition-all border border-[#0D3024]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                Lacak Pesanan
                            </a>
                            <a href="{{ route('pesanan.invoice', $pesanan->nomor_pesanan) }}" target="_blank" class="mt-3 w-full flex items-center justify-center gap-2 px-6 py-3 bg-white hover:bg-emerald-50 text-[#0D3024] font-bold tracking-widest text-xs uppercase rounded-xl transition-all border border-[#0D3024]/20">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 16l5 5 5-5M12 3v12"/></svg>
                                Unduh Bukti Pesanan
                            </a>
                        </div>

                        @if(config('app.env') === 'local')
                            <div class="text-center">
                                <button onclick="triggerLocalhostSuccess()" class="w-full text-sm font-bold tracking-widest uppercase text-emerald-700 hover:text-white hover:bg-emerald-600 transition-all bg-emerald-50 px-4 py-3 rounded-xl border border-emerald-200">
                                    Simulasikan Pembayaran Berhasil (Mode Pengembangan)
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
        const KODE_PESANAN = '{{ $pesanan->nomor_pesanan }}';
        const BATAS_WAKTU = '{{ $batasWaktu?->toIso8601String() ?? '' }}';

        // ── Countdown batas waktu pembayaran ──
        function mulaiCountdown() {
            const timerEl = document.getElementById('timer');
            const labelEl = document.getElementById('timer-label');
            if (!timerEl || !BATAS_WAKTU) return;

            const akhir = new Date(BATAS_WAKTU).getTime();
            const update = () => {
                const sisa = Math.floor((akhir - Date.now()) / 1000);
                if (sisa <= 0) {
                    timerEl.textContent = '00:00';
                    timerEl.classList.add('text-red-600');
                    if (labelEl) labelEl.textContent = 'Waktu Habis — Buat Pembayaran Baru';
                    return;
                }
                const m = Math.floor(sisa / 60).toString().padStart(2, '0');
                const s = (sisa % 60).toString().padStart(2, '0');
                timerEl.textContent = m + ':' + s;
                if (sisa < 300) timerEl.classList.add('text-red-600');
            };
            update();
            setInterval(update, 1000);
        }

        // ── Polling otomatis status pembayaran ──
        function mulaiPolling() {
            setInterval(() => {
                fetch('{{ route('pesanan.bayar.status', $kodePesanan) }}', { headers: { 'Accept': 'application/json' } })
                    .then((res) => res.json())
                    .then((data) => {
                        if (data.lunas) {
                            window.location.href = "{{ route('pesanan.bayar', $kodePesanan) }}";
                        }
                    })
                    .catch(() => {});
            }, 15000);
        }

        // ── Snap Embed ──
        function loadSnapEmbed() {
            const container = document.getElementById('snap-container');
            const loading = document.getElementById('snap-loading');
            const statusEl = document.getElementById('midtrans-status');
            const retryBtn = document.getElementById('btn-retry');
            if (!container || !loading || !statusEl || !retryBtn) return;

            container.innerHTML = '';
            loading.classList.remove('hidden');
            statusEl.classList.add('hidden');
            statusEl.textContent = '';
            retryBtn.classList.add('hidden');

            fetch('{{ route('pesanan.snap-token') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ kode_pesanan: KODE_PESANAN })
            })
            .then((res) => res.json().then((data) => ({ ok: res.ok, data })))
            .then(({ ok, data }) => {
                if (!ok || !data.success) {
                    throw new Error(data.message || 'Gagal menyiapkan pembayaran.');
                }
                loading.classList.add('hidden');
                window.snap.embed(data.snap_token, {
                    embedId: 'snap-container',
                    language: 'id',
                    onSuccess: () => {
                        statusEl.classList.remove('hidden');
                        statusEl.textContent = 'Pembayaran berhasil! Mengalihkan ke halaman hasil...';
                        window.location.href = "{{ route('pesanan.bayar', $kodePesanan) }}";
                    },
                    onPending: () => {
                        statusEl.classList.remove('hidden');
                        statusEl.textContent = 'Pembayaran sedang menunggu. Setelah dibayar, halaman ini otomatis memperbarui status.';
                    },
                    onError: () => {
                        statusEl.classList.remove('hidden');
                        statusEl.textContent = 'Pembayaran gagal. Silakan coba lagi.';
                        retryBtn.classList.remove('hidden');
                    },
                    onClose: () => {
                        statusEl.classList.remove('hidden');
                        statusEl.textContent = 'Pembayaran ditutup. Klik "Cek Status Pembayaran Sekarang" jika sudah membayar.';
                    }
                });
            })
            .catch((err) => {
                loading.classList.add('hidden');
                statusEl.classList.remove('hidden');
                statusEl.textContent = err.message;
                retryBtn.classList.remove('hidden');
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            loadSnapEmbed();
            mulaiCountdown();
            mulaiPolling();
        });

        function triggerLocalhostSuccess() {
            fetch('/api/midtrans/localhost-fallback', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ kode_pesanan: KODE_PESANAN })
            }).then((res) => res.json()).then((data) => {
                if (data.success) {
                    window.location.href = "{{ route('pesanan.bayar', $kodePesanan) }}";
                } else {
                    window.showToast('error', data.message || 'Gagal memproses pembayaran.');
                }
            });
        }
    </script>
    @endpush
</x-layouts.landing>
