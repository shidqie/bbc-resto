<x-layouts.landing>
    @php
        $dpAmount = $pesanan->nominalDP();
        $dpPersen = $pesanan->persentaseDP();
        $dpTerbayar = (float) $pesanan->pembayaran->where('status_verifikasi', 'diterima')->sum('jumlah_dibayar');
        $lunas = (float) $pesanan->pembayaran->where('status_verifikasi', 'diterima')->sum('jumlah_dibayar');
        $isPelunasan = $lunas >= $dpAmount && $lunas < $pesanan->total_tagihan;
        $amountToPay = $isPelunasan ? max(0, $pesanan->total_tagihan - $lunas) : max(0, $dpAmount - $dpTerbayar);
        $payTitle = $isPelunasan ? 'Pelunasan Tagihan' : 'Pembayaran Uang Muka';
        $payDesc = $isPelunasan ? 'Selesaikan pembayaran untuk memulai proses pengiriman.' : 'Selesaikan pembayaran awal untuk mengonfirmasi pesanan Anda.';
        $namaPemesan = optional($pesanan->pelanggan)->nama ?? optional($pesanan->jadwal_pesanan)->nama_penerima;
        $paket = $pesanan->detail_pesanan->first();
        $satuan = $type === 'nasi_box' ? 'Box' : 'Porsi';
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
                                <span class="inline-flex items-center px-3.5 py-1 rounded-full text-xs font-bold uppercase tracking-widest bg-amber-50 text-amber-700 border border-amber-200/60">
                                    <span class="w-1.5 h-1.5 bg-amber-500 rounded-full mr-1.5 animate-pulse"></span>
                                    {{ $isPelunasan ? 'Menunggu Pelunasan' : 'Menunggu Pembayaran' }}
                                </span>
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
                </div>

                <!-- RIGHT: Upload Bukti & Status -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="lg:sticky lg:top-8 space-y-6">

                        <!-- Bayar Manual (Upload Bukti) -->
                        <div class="w-full border border-gray-200/80 rounded-xl overflow-hidden bg-white shadow-lg shadow-gray-200/50">
                            <div class="p-6">
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-5">Upload Bukti Pembayaran</p>
                                <form action="{{ route('pesanan.bukti.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                    @csrf
                                    <input type="hidden" name="kode_pesanan" value="{{ $pesanan->nomor_pesanan }}">
                                    <input type="hidden" name="jenis_pembayaran" value="{{ $isPelunasan ? 'pelunasan' : 'dp' }}">
                                    
                                    <div class="p-4 bg-blue-50/50 border border-blue-100 rounded-xl">
                                        <p class="text-xs text-blue-700 mb-1">Total yang harus ditransfer:</p>
                                        <p class="text-xl font-bold text-blue-900">Rp {{ number_format($amountToPay, 0, ',', '.') }}</p>
                                    </div>
                                    
                                    <p class="text-xs text-gray-500">Silakan transfer sesuai nominal di atas ke rekening berikut:</p>
                                    
                                    <div class="p-4 bg-emerald-50/50 border border-emerald-100 rounded-xl space-y-1">
                                        <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest">Bank Tujuan</p>
                                        <p class="text-lg font-bold text-emerald-900">BCA</p>
                                        <p class="text-xl font-black font-['Anonymous_Pro'] text-[#0D3024] tracking-wider my-1">2780378231</p>
                                        <p class="text-sm font-medium text-emerald-800">A/N HENI</p>
                                    </div>
                                    
                                    <p class="text-xs text-gray-500">Lalu unggah bukti transfer di bawah ini. Admin akan memverifikasi dalam 1×24 jam.</p>
                                    
                                    <input type="file" name="file_bukti" accept="image/*,.pdf" required
                                           class="block w-full text-sm text-gray-600 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:tracking-widest file:uppercase file:bg-blue-600 file:text-white hover:file:bg-blue-700 file:cursor-pointer bg-gray-50 border border-gray-200 rounded-xl">
                                    @error('file_bukti') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                    
                                    <button type="submit" class="w-full flex items-center justify-center gap-2 px-6 py-3.5 bg-[#0D3024] hover:bg-[#164032] text-white font-bold tracking-widest text-sm uppercase rounded-xl transition-all shadow-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 8l5-5m0 0l5 5m-5-5v12"/></svg>
                                        Kirim Bukti Pembayaran
                                    </button>
                                </form>
                            </div>
                        </div>

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
                        <!-- Bukti Pesanan -->
                        <div class="w-full">
                            <a href="{{ route('pesanan.invoice', $pesanan->nomor_pesanan) }}" target="_blank" class="w-full flex items-center justify-center gap-2 px-6 py-3 bg-white hover:bg-emerald-50 text-[#0D3024] font-bold tracking-widest text-xs uppercase rounded-xl transition-all border border-gray-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 16l5 5 5-5M12 3v12"/></svg>
                                Unduh Bukti Pesanan
                            </a>
                        </div>
                    </div>
                </div>

            </div>

        </main>
    </div>

</x-layouts.landing>
