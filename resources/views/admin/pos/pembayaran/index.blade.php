<x-layouts.landing>
    @php
        $isSkemaLunas = $pesanan->isSkemaLunas();
        $dpAmount = $pesanan->nominalDP();
        $dpPersen = $pesanan->persentaseDP();
        $dpTerbayar = (float) $pesanan->pembayaran->where('status_verifikasi', 'diterima')->sum('jumlah_dibayar');
        $lunas = (float) $pesanan->pembayaran->where('status_verifikasi', 'diterima')->sum('jumlah_dibayar');
        $isPelunasan = $isSkemaLunas || ($lunas >= $dpAmount && $lunas < $pesanan->total_tagihan);
        $amountToPay = $isPelunasan ? max(0, $pesanan->total_tagihan - $lunas) : max(0, $dpAmount - $dpTerbayar);
        
        if ($isSkemaLunas) {
            $payTitle = 'Pembayaran Lunas';
            $payDesc = 'Selesaikan pembayaran penuh (100%) untuk mengonfirmasi pesanan Anda.';
        } elseif ($isPelunasan) {
            $payTitle = 'Pelunasan Tagihan';
            $payDesc = 'Selesaikan pembayaran sisa untuk mengonfirmasi pengiriman pesanan.';
        } else {
            $payTitle = 'Pembayaran Uang Muka';
            $payDesc = 'Selesaikan DP ' . $dpPersen . '% untuk mengonfirmasi pesanan Anda.';
        }

        $namaPemesan = optional($pesanan->pelanggan)->nama ?? optional($pesanan->jadwal_pesanan)->nama_penerima;
        $paket = $pesanan->detail_pesanan->first();
        $satuan = $type === 'nasi_box' ? 'Box' : 'Porsi';

        // Status Badge Logic
        $terakhirBayar = $pesanan->pembayaran->last();
        $statusVerifikasi = $terakhirBayar ? $terakhirBayar->status_verifikasi : null;

        if ($lunas >= $pesanan->total_tagihan) {
            $badgeText = 'Lunas';
            $badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200/80';
            $badgeDot = 'bg-emerald-500';
        } elseif ($statusVerifikasi === 'menunggu_verifikasi') {
            $badgeText = 'Menunggu Verifikasi';
            $badgeClass = 'bg-primary-soft text-primary border-primary/30';
            $badgeDot = 'bg-primary animate-pulse';
        } elseif ($statusVerifikasi === 'ditolak') {
            $badgeText = 'Ditolak';
            $badgeClass = 'bg-red-50 text-red-700 border-red-200/80';
            $badgeDot = 'bg-red-500';
        } elseif ($dpTerbayar > 0 && !$isSkemaLunas) {
            $badgeText = 'DP Terbayar';
            $badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200/80';
            $badgeDot = 'bg-emerald-500';
        } else {
            $badgeText = 'Menunggu Pembayaran';
            $badgeClass = 'bg-amber-50 text-amber-700 border-amber-200/80';
            $badgeDot = 'bg-amber-500 animate-pulse';
        }

        // Expire Logic — Pastikan selalu ada timestamp masa depan valid agar countdown berjalan mulus
        $expireTimestamp = null;
        $expireCarbon = null;
        if (!$isPelunasan || $isSkemaLunas) {
            $pembayaranFirst = $pesanan->pembayaran()->orderBy('id', 'asc')->first();
            if ($pembayaranFirst && $pembayaranFirst->expires_at && \Carbon\Carbon::parse($pembayaranFirst->expires_at)->isFuture()) {
                $expireTime = \Carbon\Carbon::parse($pembayaranFirst->expires_at);
            } else {
                $orderCreated = \Carbon\Carbon::parse($pesanan->dibuat_pada ?? $pesanan->created_at);
                $diff = $orderCreated->copy()->addHours(12);
                $expireTime = $diff->isFuture() ? $diff : now()->addHours(12);
            }
            $expireCarbon = $expireTime;
            $expireTimestamp = $expireTime->timestamp * 1000;
        } else {
            if (!empty($pesanan->jadwal_pesanan->tanggal_acara)) {
                $tglAcara = \Carbon\Carbon::parse($pesanan->jadwal_pesanan->tanggal_acara);
                $waktuAcara = $pesanan->jadwal_pesanan->waktu_acara ?? null;
                if (!empty($waktuAcara) && $waktuAcara !== '00:00:00') {
                    $expireTime = \Carbon\Carbon::parse($tglAcara->format('Y-m-d') . ' ' . $waktuAcara)->subDays(3);
                } else {
                    $expireTime = $tglAcara->copy()->subDays(3)->endOfDay();
                }
                if ($expireTime->isPast()) {
                    $expireTime = now()->addHours(12);
                }
                $expireCarbon = $expireTime;
                $expireTimestamp = $expireTime->timestamp * 1000;
            } else {
                $expireTime = now()->addHours(12);
                $expireCarbon = $expireTime;
                $expireTimestamp = $expireTime->timestamp * 1000;
            }
        }

        $showUploadForm = !($lunas >= $pesanan->total_tagihan) && $statusVerifikasi !== 'menunggu_verifikasi';

        $staticQris = "00020101021126590013ID.NOBUPAN.WWW01189360050300000881530215ID10264761295010303UMI51440014ID.LINKAJA.WWW0118936009140000881530215ID10264761295010303UMI5204581253033605802ID5915RUMAH MAKAN BBC6013KAB SUMEDANG 61054536362070703A016304";
        $dynamicQris = \App\Helpers\QrisHelper::generateDynamicQris($staticQris, $amountToPay);
    @endphp

    <x-slot:title>{{ $payTitle }} — {{ $pesanan->id_pesanan }}</x-slot:title>

    <div class="min-h-screen bg-gray-50/60 text-body pb-16 font-sans"
        x-data="paymentPage({{ $expireTimestamp ? $expireTimestamp : 'null' }})">

        {{-- Header Bar --}}
        <div class="bg-white border-b border-gray-200/80 py-5 sm:py-6 shadow-2xs">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
                <div>
                    <h1 class="text-lg sm:text-2xl font-extrabold text-gray-900 tracking-tight">{{ $payTitle }}</h1>
                    <p class="text-xs text-gray-500 font-medium mt-0.5">{{ $payDesc }}</p>
                </div>
                <div class="flex items-center gap-2 self-start sm:self-auto">
                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Invoice:</span>
                    <span class="font-mono text-xs font-bold bg-gray-100 text-gray-800 px-3 py-1.5 rounded-xl border border-gray-200/80">{{ $pesanan->id_pesanan }}</span>
                </div>
            </div>
        </div>

        {{-- COUNTDOWN BANNER (RESPONSIF & SELALU BERJALAN AKTIF) --}}
        @if($showUploadForm)
        <div class="w-full">
            <div class="bg-amber-50 border-b border-amber-200">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex flex-col sm:flex-row items-center justify-between gap-2.5 text-center sm:text-left">
                    <p class="text-xs font-bold text-amber-900 flex items-center justify-center sm:justify-start gap-1.5">
                        <svg class="w-4 h-4 shrink-0 text-amber-600 animate-pulse" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                        <span>Selesaikan {{ $isSkemaLunas ? 'pembayaran lunas' : ($isPelunasan ? 'pelunasan' : 'pembayaran DP') }} sebelum batas waktu:</span>
                    </p>
                    <div class="flex gap-1.5 items-center justify-center">
                        <template x-if="days > 0">
                            <div class="flex items-center gap-1">
                                <div class="bg-amber-100 text-amber-900 font-mono font-black text-xs sm:text-sm px-2.5 py-1 rounded-lg border border-amber-200/80 shadow-2xs"><span x-text="days"></span><span class="text-[10px] ml-0.5 font-sans font-bold uppercase">Hari</span></div>
                                <span class="text-amber-700 font-bold">:</span>
                            </div>
                        </template>
                        <div class="flex items-center gap-1">
                            <div class="bg-amber-100 text-amber-900 font-mono font-black text-xs sm:text-sm px-2.5 py-1 rounded-lg border border-amber-200/80 shadow-2xs"><span x-text="hours"></span><span class="text-[10px] ml-0.5 font-sans font-bold uppercase">Jam</span></div>
                            <span class="text-amber-700 font-bold">:</span>
                        </div>
                        <div class="bg-amber-100 text-amber-900 font-mono font-black text-xs sm:text-sm px-2.5 py-1 rounded-lg border border-amber-200/80 shadow-2xs"><span x-text="minutes"></span><span class="text-[10px] ml-0.5 font-sans font-bold uppercase">Mnt</span></div>
                        <span class="text-amber-700 font-bold">:</span>
                        <div class="bg-amber-100 text-amber-900 font-mono font-black text-xs sm:text-sm px-2.5 py-1 rounded-lg border border-amber-200/80 shadow-2xs"><span x-text="seconds"></span><span class="text-[10px] ml-0.5 font-sans font-bold uppercase">Dtk</span></div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6 sm:mt-8">

            @if(session('success'))
                <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-2xl text-xs font-bold shadow-2xs">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-2xl text-xs font-bold shadow-2xs">
                    {{ session('error') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">

                {{-- ── KIRI (7 Kolom): AKSI — Nominal, Rekening, Upload Bukti ── --}}
                <div class="lg:col-span-7 space-y-6">

                    @if($lunas >= $pesanan->total_tagihan)
                        <div class="bg-white rounded-3xl border border-emerald-200 p-5 sm:p-8 space-y-6 shadow-sm" x-data="{ previewModal: false }">
                            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-4 p-4 sm:p-5 rounded-2xl bg-emerald-50 border border-emerald-200">
                                <div class="w-12 h-12 bg-emerald-600 text-white rounded-2xl flex items-center justify-center shrink-0 shadow-xs">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <div class="text-center sm:text-left flex-1">
                                    <div class="flex flex-wrap items-center justify-center sm:justify-between gap-2">
                                        <h3 class="text-base font-bold text-emerald-950">Pembayaran Terverifikasi (Lunas)!</h3>
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                            Lunas
                                        </span>
                                    </div>
                                    <p class="text-xs text-emerald-700 mt-1">Seluruh tagihan pesanan telah lunas dan diverifikasi oleh pihak resto.</p>
                                </div>
                            </div>

                            @if($terakhirBayar)
                            <div class="bg-gray-50/70 rounded-2xl border border-gray-200/80 p-4 sm:p-5 space-y-3.5">
                                <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider flex items-center gap-2">
                                    <x-heroicon-o-receipt-percent class="w-4 h-4 text-emerald-600" />
                                    <span>Rincian Pembayaran</span>
                                </h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                    <div class="space-y-0.5">
                                        <span class="text-gray-400 font-medium">Nomor Transaksi</span>
                                        <p class="font-mono font-bold text-gray-800">{{ $terakhirBayar->kode_pembayaran }}</p>
                                    </div>
                                    <div class="space-y-0.5 sm:text-right">
                                        <span class="text-gray-400 font-medium">Nominal Pembayaran</span>
                                        <p class="font-mono font-black text-emerald-600 text-sm">Rp {{ number_format($terakhirBayar->jumlah_dibayar, 0, ',', '.') }}</p>
                                    </div>
                                    <div class="space-y-0.5">
                                        <span class="text-gray-400 font-medium">Tahap Pembayaran</span>
                                        <p class="font-bold text-gray-800">{{ $terakhirBayar->jenis_pembayaran === 'uang_muka' ? 'Uang Muka (DP)' : ($isSkemaLunas ? 'Pembayaran Penuh (Lunas)' : 'Pelunasan Tagihan') }}</p>
                                    </div>
                                    <div class="space-y-0.5 sm:text-right">
                                        <span class="text-gray-400 font-medium">Waktu Verifikasi</span>
                                        <p class="font-semibold text-gray-700">{{ $terakhirBayar->tanggal_verifikasi ? \Carbon\Carbon::parse($terakhirBayar->tanggal_verifikasi)->translatedFormat('d M Y, H:i') . ' WIB' : ($terakhirBayar->tanggal_pembayaran ? \Carbon\Carbon::parse($terakhirBayar->tanggal_pembayaran)->translatedFormat('d M Y, H:i') . ' WIB' : '-') }}</p>
                                    </div>
                                </div>
                            </div>

                            @if($terakhirBayar->bukti_pembayaran && $terakhirBayar->bukti_pembayaran !== 'midtrans_online')
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <label class="block text-xs font-bold text-gray-800 uppercase tracking-wider">Bukti Transfer yang Diunggah</label>
                                    <a href="{{ asset('storage/' . $terakhirBayar->bukti_pembayaran) }}" target="_blank" class="text-xs text-emerald-600 hover:underline font-bold inline-flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        <span>Buka Ukuran Asli</span>
                                    </a>
                                </div>

                                @if(Str::endsWith(strtolower($terakhirBayar->bukti_pembayaran), ['.pdf']))
                                    <div class="p-6 bg-gray-50 rounded-2xl border border-gray-200 text-center space-y-2">
                                        <div class="w-12 h-12 rounded-xl bg-red-100 text-red-600 flex items-center justify-center mx-auto">
                                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14H9v-2h2v2zm0-4H9V7h2v5z"/></svg>
                                        </div>
                                        <p class="text-xs font-bold text-gray-800">Dokumen PDF Terlampir</p>
                                        <a href="{{ asset('storage/' . $terakhirBayar->bukti_pembayaran) }}" target="_blank" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-xl text-xs font-bold hover:bg-gray-100 shadow-2xs transition-colors">
                                            <span>Lihat Dokumen PDF</span>
                                        </a>
                                    </div>
                                @else
                                    <div class="relative group rounded-2xl border border-gray-200 bg-gray-900/5 overflow-hidden flex items-center justify-center p-3">
                                        <img src="{{ asset('storage/' . $terakhirBayar->bukti_pembayaran) }}" 
                                             alt="Bukti Pembayaran" 
                                             class="max-h-72 w-auto object-contain rounded-xl shadow-xs transition-transform duration-300 group-hover:scale-[1.01] cursor-pointer"
                                             @click="previewModal = true">
                                        <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2 cursor-pointer pointer-events-none">
                                            <span class="bg-white/90 text-gray-900 font-bold text-xs px-3 py-1.5 rounded-xl shadow-md flex items-center gap-1.5">
                                                <svg class="w-4 h-4 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                Perbesar
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Modal Lightbox --}}
                                    <div x-show="previewModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4" @click.self="previewModal = false" @keydown.escape.window="previewModal = false">
                                        <div class="relative max-w-3xl max-h-[90vh] bg-white rounded-2xl overflow-hidden p-2 shadow-2xl flex flex-col items-center w-full mx-auto">
                                            <div class="w-full flex justify-between items-center px-4 py-2 border-b border-gray-100">
                                                <span class="text-xs font-bold text-gray-700">Bukti Transfer — {{ $pesanan->id_pesanan }}</span>
                                                <button type="button" @click="previewModal = false" class="text-gray-400 hover:text-gray-700 font-bold text-lg p-1">✕</button>
                                            </div>
                                            <div class="p-2 overflow-auto max-h-[75vh] w-full flex items-center justify-center">
                                                <img src="{{ asset('storage/' . $terakhirBayar->bukti_pembayaran) }}" alt="Bukti Pembayaran Full" class="max-w-full max-h-[70vh] h-auto object-contain rounded-lg">
                                            </div>
                                            <div class="w-full px-4 py-2 border-t border-gray-100 flex justify-end">
                                                <a href="{{ asset('storage/' . $terakhirBayar->bukti_pembayaran) }}" target="_blank" download class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-lg transition-colors">
                                                    Unduh Berkas
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            @endif
                            @endif

                            <div class="pt-3 border-t border-gray-100 flex justify-end">
                                <a href="{{ route('lacak.index') }}?kode_pesanan={{ $pesanan->id_pesanan }}" class="text-xs font-bold text-white bg-primary hover:bg-primary-container px-5 py-2.5 rounded-xl shadow-xs transition-colors inline-flex items-center gap-1.5">
                                    <span>Lacak Status Pesanan</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </div>
                        </div>
                    @elseif($statusVerifikasi === 'menunggu_verifikasi')
                        <div class="bg-white rounded-3xl border border-primary/20 p-5 sm:p-8 space-y-6 shadow-sm" x-data="{ reupload: false, previewModal: false }">
                            {{-- Header Alert Status --}}
                            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-4 p-4 sm:p-5 rounded-2xl bg-primary-soft/50 border border-primary/20">
                                <div class="w-12 h-12 bg-primary text-white rounded-2xl flex items-center justify-center shrink-0 shadow-xs">
                                    <svg class="w-6 h-6 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <div class="text-center sm:text-left flex-1">
                                    <div class="flex flex-wrap items-center justify-center sm:justify-between gap-2">
                                        <h3 class="text-base font-bold text-gray-900">Bukti Pembayaran Terkirim!</h3>
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                            Menunggu Verifikasi
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-600 mt-1">Pembayaran Anda sedang dalam antrean verifikasi oleh pihak resto (Estimasi maksimal 1×24 Jam).</p>
                                </div>
                            </div>

                            {{-- Rincian Pembayaran yang Dikirim --}}
                            @if($terakhirBayar)
                            <div class="bg-gray-50/70 rounded-2xl border border-gray-200/80 p-4 sm:p-5 space-y-3.5">
                                <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider flex items-center gap-2">
                                    <x-heroicon-o-receipt-percent class="w-4 h-4 text-primary" />
                                    <span>Rincian Pembayaran Terkirim</span>
                                </h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                    <div class="space-y-0.5">
                                        <span class="text-gray-400 font-medium">Nomor Transaksi</span>
                                        <p class="font-mono font-bold text-gray-800">{{ $terakhirBayar->kode_pembayaran }}</p>
                                    </div>
                                    <div class="space-y-0.5 sm:text-right">
                                        <span class="text-gray-400 font-medium">Nominal Pembayaran</span>
                                        <p class="font-mono font-black text-primary text-sm">Rp {{ number_format($terakhirBayar->jumlah_dibayar, 0, ',', '.') }}</p>
                                    </div>
                                    <div class="space-y-0.5">
                                        <span class="text-gray-400 font-medium">Tahap Pembayaran</span>
                                        <p class="font-bold text-gray-800">{{ $terakhirBayar->jenis_pembayaran === 'uang_muka' ? 'Uang Muka (DP)' : ($isSkemaLunas ? 'Pembayaran Penuh (Lunas)' : 'Pelunasan Tagihan') }}</p>
                                    </div>
                                    <div class="space-y-0.5 sm:text-right">
                                        <span class="text-gray-400 font-medium">Waktu Unggah</span>
                                        <p class="font-semibold text-gray-700">{{ $terakhirBayar->tanggal_pembayaran ? \Carbon\Carbon::parse($terakhirBayar->tanggal_pembayaran)->translatedFormat('d M Y, H:i') . ' WIB' : '-' }}</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Gambar Bukti Pembayaran --}}
                            @if($terakhirBayar->bukti_pembayaran)
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <label class="block text-xs font-bold text-gray-800 uppercase tracking-wider">Bukti Transfer yang Diunggah</label>
                                    <a href="{{ asset('storage/' . $terakhirBayar->bukti_pembayaran) }}" target="_blank" class="text-xs text-primary hover:underline font-bold inline-flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        <span>Buka Ukuran Asli</span>
                                    </a>
                                </div>

                                @if(Str::endsWith(strtolower($terakhirBayar->bukti_pembayaran), ['.pdf']))
                                    <div class="p-6 bg-gray-50 rounded-2xl border border-gray-200 text-center space-y-2">
                                        <div class="w-12 h-12 rounded-xl bg-red-100 text-red-600 flex items-center justify-center mx-auto">
                                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14H9v-2h2v2zm0-4H9V7h2v5z"/></svg>
                                        </div>
                                        <p class="text-xs font-bold text-gray-800">Dokumen PDF Terlampir</p>
                                        <a href="{{ asset('storage/' . $terakhirBayar->bukti_pembayaran) }}" target="_blank" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-xl text-xs font-bold hover:bg-gray-100 shadow-2xs transition-colors">
                                            <span>Lihat Dokumen PDF</span>
                                        </a>
                                    </div>
                                @else
                                    <div class="relative group rounded-2xl border border-gray-200 bg-gray-900/5 overflow-hidden flex items-center justify-center p-3">
                                        <img src="{{ asset('storage/' . $terakhirBayar->bukti_pembayaran) }}" 
                                             alt="Bukti Pembayaran" 
                                             class="max-h-72 w-auto object-contain rounded-xl shadow-xs transition-transform duration-300 group-hover:scale-[1.01] cursor-pointer"
                                             @click="previewModal = true">
                                        <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2 cursor-pointer pointer-events-none">
                                            <span class="bg-white/90 text-gray-900 font-bold text-xs px-3 py-1.5 rounded-xl shadow-md flex items-center gap-1.5">
                                                <svg class="w-4 h-4 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                Perbesar
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Modal Lightbox Perbesar Gambar --}}
                                    <div x-show="previewModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4" @click.self="previewModal = false" @keydown.escape.window="previewModal = false">
                                        <div class="relative max-w-3xl max-h-[90vh] bg-white rounded-2xl overflow-hidden p-2 shadow-2xl flex flex-col items-center w-full mx-auto">
                                            <div class="w-full flex justify-between items-center px-4 py-2 border-b border-gray-100">
                                                <span class="text-xs font-bold text-gray-700">Bukti Transfer — {{ $pesanan->id_pesanan }}</span>
                                                <button type="button" @click="previewModal = false" class="text-gray-400 hover:text-gray-700 font-bold text-lg p-1">✕</button>
                                            </div>
                                            <div class="p-2 overflow-auto max-h-[75vh] w-full flex items-center justify-center">
                                                <img src="{{ asset('storage/' . $terakhirBayar->bukti_pembayaran) }}" alt="Bukti Pembayaran Full" class="max-w-full max-h-[70vh] h-auto object-contain rounded-lg">
                                            </div>
                                            <div class="w-full px-4 py-2 border-t border-gray-100 flex justify-end">
                                                <a href="{{ asset('storage/' . $terakhirBayar->bukti_pembayaran) }}" target="_blank" download class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-lg transition-colors">
                                                    Unduh Berkas
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            @endif
                            @endif

                            {{-- Tombol Tindakan & Opsi Reupload --}}
                            <div class="pt-3 border-t border-gray-100 flex flex-wrap items-center justify-between gap-3">
                                <button type="button" @click="reupload = !reupload" class="text-xs font-bold text-gray-600 hover:text-gray-900 border border-gray-200 bg-gray-50 hover:bg-gray-100 px-4 py-2.5 rounded-xl transition-all inline-flex items-center gap-2 cursor-pointer">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    <span x-text="reupload ? 'Tutup Form Ganti Bukti' : 'Unggah Ulang / Ganti Bukti Transfer'"></span>
                                </button>

                                <a href="{{ route('lacak.index') }}?kode_pesanan={{ $pesanan->id_pesanan }}" class="text-xs font-bold text-white bg-primary hover:bg-primary-container px-4 py-2.5 rounded-xl shadow-xs transition-colors inline-flex items-center gap-1.5 cursor-pointer">
                                    <span>Lacak Pesanan</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </div>

                            {{-- Form Unggah Ulang Bukti (Hidden by default, can be toggled) --}}
                            <div x-show="reupload" x-transition x-cloak class="pt-4 border-t border-dashed border-gray-200 space-y-4"
                                 x-data="uploadForm()">
                                <div class="bg-amber-50 border border-amber-200 text-amber-800 p-3.5 rounded-xl text-xs">
                                    <p class="font-bold mb-0.5">Unggah Ulang Bukti Pembayaran</p>
                                    <p>Gunakan opsi ini jika foto sebelumnya buram atau Anda ingin memperbarui struk transfer.</p>
                                </div>
                                <form action="{{ route('pesanan.upload_bukti') }}" method="POST" enctype="multipart/form-data" class="space-y-4" @submit="isSubmitting = true">
                                    @csrf
                                    <input type="hidden" name="kode_pesanan" value="{{ $pesanan->id_pesanan }}">
                                    <input type="hidden" name="jenis_pembayaran" value="{{ $isPelunasan ? 'pelunasan' : 'dp' }}">

                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Pilih File Bukti Baru</label>
                                        
                                        <div class="relative rounded-2xl border-2 border-dashed transition-all duration-200"
                                             :class="isDragging ? 'border-primary bg-primary/5' : 'border-gray-300 bg-gray-50/50 hover:bg-gray-50 hover:border-gray-400'"
                                             @dragover.prevent="isDragging = true"
                                             @dragleave.prevent="isDragging = false"
                                             @drop.prevent="isDragging = false; handleDrop($event)">

                                            <input type="file"
                                                   x-ref="fileInput"
                                                   name="file_bukti"
                                                   accept="image/jpeg,image/png,application/pdf,.jpg,.jpeg,.png,.pdf"
                                                   class="absolute inset-0 w-full h-full opacity-0 z-10 cursor-pointer"
                                                   :class="filePreview ? 'pointer-events-none -z-10' : ''"
                                                   required
                                                   @change="handleFileChange($event)">

                                            <div x-show="!filePreview" class="p-6 text-center space-y-2 pointer-events-none">
                                                <svg class="w-7 h-7 text-gray-400 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                                <p class="text-xs text-gray-600 font-bold">Pilih foto/struk transfer baru</p>
                                                <p class="text-[10px] text-gray-400">JPG, PNG, atau PDF (Maks. 1MB)</p>
                                            </div>

                                            <div x-show="filePreview" style="display: none;" class="p-4 text-center space-y-2 relative z-20">
                                                <p class="text-xs font-bold text-emerald-700 truncate" x-text="fileName"></p>
                                                <button type="button" @click="clearFile()" class="text-xs font-bold text-red-600 hover:text-red-800 underline cursor-pointer">
                                                    Ganti File
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="submit"
                                            :disabled="isSubmitting"
                                            class="w-full py-3 bg-primary hover:bg-primary-container text-white text-xs font-bold rounded-xl transition-all shadow-xs flex items-center justify-center gap-2 cursor-pointer">
                                        <span x-show="!isSubmitting">Kirim Pembaruan Bukti</span>
                                        <span x-show="isSubmitting" style="display: none;" class="flex items-center gap-2">
                                            <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                            <span>Mengunggah...</span>
                                        </span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        {{-- KARTU PEMBAYARAN: NOMINAL, METODE TRANSFER / QRIS, & UPLOAD BUKTI --}}
                        <div class="bg-white rounded-3xl border border-gray-200/80 p-5 sm:p-8 space-y-6 shadow-sm" x-data="{ copied: false }">

                            {{-- Nominal Transfer Card --}}
                            <div class="bg-primary text-white rounded-2xl p-5 sm:p-6 shadow-xs space-y-3">
                                <div class="space-y-1">
                                    <p class="text-emerald-300 text-[11px] font-bold uppercase tracking-wider">
                                        Nominal Transfer ({{ $isSkemaLunas ? 'Pembayaran Lunas 100%' : ($isPelunasan ? 'Pelunasan' : 'DP '.$dpPersen.'%') }})
                                    </p>
                                    <p class="text-2xl sm:text-4xl font-extrabold tracking-tight font-mono break-all">Rp {{ number_format($amountToPay, 0, ',', '.') }}</p>
                                </div>

                                @if($expireCarbon)
                                <div class="pt-3 border-t border-white/15 flex flex-col sm:flex-row sm:items-center justify-between gap-1.5 text-xs">
                                    <span class="text-emerald-200/90 font-medium flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-emerald-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Batas Tanggal Pembayaran:
                                    </span>
                                    <span class="font-bold text-white font-mono bg-white/10 px-2.5 py-1 rounded-lg border border-white/10 w-fit">
                                        {{ $expireCarbon->translatedFormat('d F Y, H:i') }} WIB
                                    </span>
                                </div>
                                @endif
                            </div>

                            {{-- Informasi Penting --}}
                            <div class="bg-emerald-50/50 rounded-2xl border border-emerald-100 p-4 sm:p-5">
                                <h3 class="text-xs font-bold text-emerald-900 mb-2 flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                                    Informasi Penting
                                </h3>
                                <ul class="list-disc pl-4 space-y-1.5 text-xs text-gray-600 leading-relaxed">
                                    <li>DP (Uang Muka) tidak dapat dikembalikan jika pesanan dibatalkan oleh konsumen.</li>
                                    <li>Pelunasan wajib dilakukan maksimal H-3 sebelum tanggal acara.</li>
                                    <li>Jika tidak melakukan pelunasan hingga batas waktu, pesanan akan dianggap batal.</li>
                                </ul>
                            </div>

                            {{-- 1. Pilihan Metode Pembayaran (Transfer di Kiri, QRIS di Kanan) --}}
                            <div x-data="{ tabPay: 'transfer' }" class="space-y-4">
                                <label class="block text-xs font-bold text-gray-800 uppercase tracking-wider">1. Pilih Metode Pembayaran (Transfer / QRIS)</label>
                                
                                {{-- Tabs Metode Pembayaran --}}
                                <div class="grid grid-cols-2 gap-2.5 sm:gap-3">
                                    {{-- Tab Transfer (Kiri) --}}
                                    <button type="button" @click="tabPay = 'transfer'"
                                            :class="tabPay === 'transfer' ? 'bg-primary text-white border-primary shadow-xs font-extrabold' : 'bg-gray-50 text-gray-600 border-gray-200 hover:bg-gray-100 font-semibold'"
                                            class="py-3 px-2 sm:px-3 rounded-2xl border text-xs transition-all flex items-center justify-center gap-1.5 sm:gap-2 uppercase tracking-wider cursor-pointer">
                                        <x-heroicon-o-building-library class="w-4 h-4 shrink-0" />
                                        <span>Transfer</span>
                                    </button>

                                    {{-- Tab QRIS (Kanan) --}}
                                    <button type="button" @click="tabPay = 'qris'"
                                            :class="tabPay === 'qris' ? 'bg-primary text-white border-primary shadow-xs font-extrabold' : 'bg-gray-50 text-gray-600 border-gray-200 hover:bg-gray-100 font-semibold'"
                                            class="py-3 px-2 sm:px-3 rounded-2xl border text-xs transition-all flex items-center justify-center gap-1.5 sm:gap-2 uppercase tracking-wider cursor-pointer">
                                        <x-heroicon-o-qr-code class="w-4 h-4 shrink-0" />
                                        <span>QRIS</span>
                                    </button>
                                </div>

                                {{-- Konten Transfer --}}
                                <div x-show="tabPay === 'transfer'" class="p-4 sm:p-5 bg-emerald-50/50 border border-emerald-200/80 rounded-2xl space-y-3">
                                    <div class="flex justify-between items-center">
                                        <span class="text-xs font-bold text-emerald-900 uppercase tracking-wider">Bank BCA</span>
                                        <span class="text-xs font-bold text-emerald-700 bg-white px-2.5 py-0.5 rounded-lg border border-emerald-200 shadow-2xs">A/N HENI</span>
                                    </div>
                                    <div class="text-xl sm:text-2xl font-extrabold font-mono text-primary tracking-wider break-all">2780378231</div>
                                    <button type="button" @click="navigator.clipboard.writeText('2780378231'); copied = true; setTimeout(() => copied = false, 2000)"
                                            class="w-full py-2.5 bg-white hover:bg-emerald-100/60 border border-emerald-300 text-emerald-900 text-xs font-bold rounded-xl transition-all flex items-center justify-center gap-2 cursor-pointer shadow-2xs">
                                        <template x-if="!copied">
                                            <span class="flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                                <span>Salin Nomor Rekening</span>
                                            </span>
                                        </template>
                                        <template x-if="copied">
                                            <span class="text-emerald-700 font-extrabold flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                <span>Berhasil Disalin!</span>
                                            </span>
                                        </template>
                                    </button>
                                </div>

                                {{-- Konten QRIS --}}
                                <div x-show="tabPay === 'qris'" style="display:none;" class="p-5 sm:p-6 bg-white border border-gray-200 rounded-2xl shadow-xs space-y-4 text-center">
                                    <div class="text-center space-y-1">
                                        <div class="flex items-center justify-center gap-1.5 text-primary font-bold text-lg tracking-tight">
                                            <x-heroicon-o-qr-code class="w-5 h-5 text-primary" />
                                            <span>QRIS</span>
                                        </div>
                                        <p class="text-xs text-gray-500 max-w-[280px] leading-relaxed mx-auto">
                                            Silakan pindai kode QRIS di bawah ini melalui aplikasi e-Wallet atau M-Banking Anda
                                        </p>
                                    </div>

                                    {{-- QR Code Image --}}
                                    <div @click="showFullscreenQR = true" class="p-3 bg-white border border-gray-200 rounded-2xl shadow-xs inline-block cursor-pointer hover:scale-105 transition-transform duration-200" title="Klik untuk memperbesar QR Code">
                                        <div class="w-40 h-40 sm:w-48 sm:h-48 mx-auto flex items-center justify-center [&>svg]:w-full [&>svg]:h-full">
                                            {!! SimpleSoftwareIO\QrCode\Facades\QrCode::size(180)->margin(1)->generate($dynamicQris) !!}
                                        </div>
                                    </div>

                                    <div class="text-center space-y-0.5">
                                        <p class="text-xs font-semibold text-gray-700">Rumah Makan BBC</p>
                                        <p class="text-xl font-extrabold text-primary font-mono">
                                            Rp {{ number_format($amountToPay, 0, ',', '.') }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {{-- 2. Form Upload Bukti --}}
                            <form action="{{ route('pesanan.upload_bukti') }}" method="POST" enctype="multipart/form-data" class="space-y-4 pt-2"
                                  x-data="uploadForm()" @submit="isSubmitting = true">
                                @csrf
                                <input type="hidden" name="kode_pesanan" value="{{ $pesanan->id_pesanan }}">
                                <input type="hidden" name="jenis_pembayaran" value="{{ $isPelunasan ? 'pelunasan' : 'dp' }}">

                                <div>
                                    <label class="block text-xs font-bold text-gray-800 uppercase tracking-wider mb-2">2. Upload Bukti Pembayaran</label>

                                    {{-- Dropzone / File Picker (Natively Clickable everywhere on mobile and desktop) --}}
                                    <div class="relative rounded-2xl border-2 border-dashed transition-all duration-200"
                                         :class="isDragging ? 'border-primary bg-primary/5' : 'border-gray-300 bg-gray-50/50 hover:bg-gray-50 hover:border-gray-400'"
                                         @dragover.prevent="isDragging = true"
                                         @dragleave.prevent="isDragging = false"
                                         @drop.prevent="isDragging = false; handleDrop($event)">

                                        {{-- Native transparent file input, covers the full card area for instant tap/click --}}
                                        <input type="file"
                                               x-ref="fileInput"
                                               name="file_bukti"
                                               id="file_bukti"
                                               accept="image/jpeg,image/png,application/pdf,.jpg,.jpeg,.png,.pdf"
                                               class="absolute inset-0 w-full h-full opacity-0 z-10 cursor-pointer"
                                               :class="filePreview ? 'pointer-events-none -z-10' : ''"
                                               required
                                               @change="handleFileChange($event)">

                                        {{-- State: Belum ada file --}}
                                        <div x-show="!filePreview" class="p-6 text-center space-y-2.5 pointer-events-none">
                                            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center mx-auto border border-emerald-100 shadow-2xs">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                            </div>
                                            <div>
                                                <p class="text-xs sm:text-sm font-bold text-gray-800">Klik / Sentuh untuk memilih file bukti</p>
                                                <p class="text-[11px] text-gray-400 font-medium mt-0.5">JPG • PNG • PDF (Maksimal 1 MB)</p>
                                            </div>
                                            <div class="pt-1">
                                                <span class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-primary text-white text-xs font-bold shadow-xs">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                    <span>Pilih File Dari Perangkat</span>
                                                </span>
                                            </div>
                                        </div>

                                        {{-- State: File Dipilih --}}
                                        <div x-show="filePreview" style="display: none;" class="p-4 sm:p-5 text-center space-y-3 relative z-20">
                                            <template x-if="isImage">
                                                <div class="relative inline-block rounded-xl overflow-hidden border border-gray-200 bg-gray-900 shadow-xs max-h-44">
                                                    <img :src="filePreview" alt="Preview Bukti" class="max-h-44 w-auto object-contain mx-auto">
                                                </div>
                                            </template>
                                            <template x-if="isPdf">
                                                <div class="p-4 bg-red-50 rounded-xl border border-red-200 flex items-center justify-center gap-3 text-red-700 max-w-xs mx-auto">
                                                    <svg class="w-8 h-8 text-red-500 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-9.5 8.5c0 .8-.7 1.5-1.5 1.5H7v2H5.5V9H8c.8 0 1.5.7 1.5 1.5v1zm5 2c0 .8-.7 1.5-1.5 1.5h-2.5V9H13c.8 0 1.5.7 1.5 1.5v3zm4-3H17v1.5h1.5v1.5H17V17h-1.5V9h3v1.5zM7 10.5h1v1H7v-1zm6 0h1v2h-1v-2z"/></svg>
                                                    <div class="text-left">
                                                        <p class="text-xs font-bold">Dokumen PDF Terpilih</p>
                                                        <p class="text-[11px] text-red-600 truncate max-w-[180px]" x-text="fileName"></p>
                                                    </div>
                                                </div>
                                            </template>

                                            <div class="flex items-center justify-center gap-3 pt-1">
                                                <p class="text-xs font-bold text-gray-800 truncate max-w-[200px] sm:max-w-xs" x-text="fileName"></p>
                                                <button type="button" @click="clearFile()" class="text-xs font-bold text-red-600 hover:text-red-800 underline cursor-pointer">
                                                    Ganti File
                                                </button>
                                            </div>
                                        </div>

                                    </div>
                                    @error('file_bukti') <p class="text-xs text-red-600 font-medium mt-1">{{ $message }}</p> @enderror
                                </div>

                                <button type="submit"
                                        :disabled="isSubmitting"
                                        class="w-full py-3.5 bg-primary hover:bg-primary-container text-white font-bold text-xs rounded-xl flex items-center justify-center gap-2 transition-all duration-200 active:scale-[0.99] cursor-pointer shadow-xs">
                                    <span x-show="!isSubmitting">Kirim Bukti Pembayaran</span>
                                    <span x-show="isSubmitting" style="display: none;" class="flex items-center gap-2">
                                        <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                        <span>Mengunggah...</span>
                                    </span>
                                    <svg x-show="!isSubmitting" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </button>
                            </form>
                        </div>
                    @endif
                </div>

                {{-- ── KANAN (5 Kolom): REFERENSI — Ringkasan Tagihan & Rincian Pesanan + Detail Menu Pilihan ── --}}
                <div class="lg:col-span-5 space-y-6">

                    {{-- 1. Ringkasan Tagihan --}}
                    <div class="bg-white rounded-3xl border border-gray-200/80 p-5 sm:p-8 space-y-4 shadow-sm">
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest border-b border-gray-100 pb-3 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            <span>Ringkasan Tagihan</span>
                        </h3>
                        <div class="space-y-2.5 text-xs">
                            <div class="flex justify-between items-center py-1 border-b border-gray-50">
                                <span class="text-gray-500 font-medium">Subtotal Tagihan</span>
                                <span class="font-bold text-gray-900 font-mono text-sm">Rp {{ number_format($pesanan->total_tagihan, 0, ',', '.') }}</span>
                            </div>
                            @if($isSkemaLunas)
                            <div class="flex justify-between items-center py-1 border-b border-gray-50">
                                <span class="text-gray-500 font-medium">Skema Pembayaran</span>
                                <span class="font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">Bayar Lunas (100%)</span>
                            </div>
                            <div class="flex justify-between items-center pt-2">
                                <span class="text-gray-500 font-bold">Total Pembayaran</span>
                                <span class="font-bold text-primary font-mono text-sm sm:text-base">Rp {{ number_format($pesanan->total_tagihan, 0, ',', '.') }}</span>
                            </div>
                            @else
                            <div class="flex justify-between items-center py-1 border-b border-gray-50">
                                <span class="text-gray-500 font-medium">DP {{ $dpPersen }}% (Uang Muka)</span>
                                <span class="font-bold text-gray-900 font-mono text-sm">Rp {{ number_format($dpAmount, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center pt-2">
                                <span class="text-gray-500 font-bold">Sisa Pelunasan</span>
                                <span class="font-bold text-red-600 font-mono text-sm sm:text-base">Rp {{ number_format($pesanan->total_tagihan - $dpAmount, 0, ',', '.') }}</span>
                            </div>
                            @endif
                        </div>

                        @if($dpTerbayar > 0 || $lunas >= $pesanan->total_tagihan)
                        <div class="pt-3 border-t border-gray-100">
                            <a href="{{ route('pesanan.invoice', $pesanan->id_pesanan) }}" target="_blank"
                               class="w-full py-3 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 font-bold text-xs rounded-xl flex items-center justify-center gap-2 transition-all cursor-pointer shadow-2xs">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                <span>Unduh Bukti Pembayaran</span>
                            </a>
                        </div>
                        @endif
                    </div>

                    {{-- 2. Rincian Pesanan DENGAN Detail Menu Pilihan Langsung Terbuka --}}
                    <div class="bg-white rounded-3xl border border-gray-200/80 p-5 sm:p-8 shadow-sm">
                        <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-4 gap-2">
                            <h2 class="text-xs font-bold text-gray-400 uppercase tracking-widest flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span>Rincian Pesanan</span>
                            </h2>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold border {{ $badgeClass }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $badgeDot }}"></span>
                                <span>{{ $badgeText }}</span>
                            </span>
                        </div>

                        <div class="space-y-3 text-xs">
                            <div class="flex flex-col sm:flex-row sm:justify-between py-1 border-b border-gray-50 gap-0.5">
                                <span class="text-gray-400 font-medium">Invoice</span>
                                <span class="font-bold text-gray-900 font-mono">{{ $pesanan->id_pesanan }}</span>
                            </div>
                            <div class="flex flex-col sm:flex-row sm:justify-between py-1 border-b border-gray-50 gap-0.5">
                                <span class="text-gray-400 font-medium">Pemesan</span>
                                <span class="font-bold text-gray-900">{{ $namaPemesan }}</span>
                            </div>
                            <div class="flex flex-col sm:flex-row sm:justify-between py-1 border-b border-gray-50 gap-0.5">
                                <span class="text-gray-400 font-medium">Produk / Layanan</span>
                                <span class="font-bold text-gray-900">{{ $paket->menu->nama_menu ?? 'Paket' }} <span class="text-gray-500 font-normal">({{ $paket->jumlah ?? '-' }} {{ $satuan }})</span></span>
                            </div>
                            @if($pesanan->jadwal_pesanan)
                            <div class="flex flex-col sm:flex-row sm:justify-between py-1 border-b border-gray-50 gap-0.5">
                                <span class="text-gray-400 font-medium">Tanggal Acara</span>
                                <span class="font-bold text-gray-900">{{ \Carbon\Carbon::parse($pesanan->jadwal_pesanan->tanggal_acara)->translatedFormat('d F Y') }}</span>
                            </div>
                            @endif
                            <div class="flex flex-col sm:flex-row sm:justify-between py-1 border-b border-gray-50 gap-0.5">
                                <span class="text-gray-400 font-medium">Jenis Layanan</span>
                                <span class="font-bold text-gray-900">{{ $type === 'nasi_box' ? 'Nasi Box' : 'Catering' }}</span>
                            </div>
                            <div class="flex flex-col sm:flex-row sm:justify-between py-1 border-b border-gray-50 gap-0.5">
                                <span class="text-gray-400 font-medium">Metode Pengambilan</span>
                                <span class="font-bold text-gray-900 capitalize">{{ strtolower($pesanan->metode_pengiriman) === 'delivery' ? 'Diantar' : 'Diambil' }}</span>
                            </div>
                            @if(strtolower($pesanan->metode_pengiriman) === 'delivery' && $pesanan->jadwal_pesanan)
                            <div class="flex flex-col py-1 border-b border-gray-50 gap-1">
                                <span class="text-gray-400 font-medium">Alamat Pengiriman</span>
                                <span class="font-bold text-gray-900 leading-relaxed bg-gray-50 p-2.5 rounded-xl border border-gray-100">{{ $pesanan->jadwal_pesanan->alamat_pengiriman ?? '-' }}</span>
                            </div>
                            @endif
                            @php
                                $noTelepon = optional($pesanan->pelanggan)->no_telepon ?? optional($pesanan->jadwal_pesanan)->nomor_telepon_penerima;
                            @endphp
                            @if($noTelepon)
                            <div class="flex flex-col sm:flex-row sm:justify-between py-1 border-b border-gray-50 gap-0.5">
                                <span class="text-gray-400 font-medium">No. WhatsApp</span>
                                <span class="font-bold text-gray-900 font-mono">{{ $noTelepon }}</span>
                            </div>
                            @endif
                            @if($pesanan->jadwal_pesanan && $pesanan->jadwal_pesanan->keterangan)
                            <div class="flex flex-col py-1 gap-1">
                                <span class="text-gray-400 font-medium">Catatan</span>
                                <span class="font-medium text-gray-700 leading-relaxed bg-gray-50 p-2.5 rounded-xl border border-gray-100">{{ $pesanan->jadwal_pesanan->keterangan }}</span>
                            </div>
                            @endif
                        </div>

                        {{-- SEKSI DETAIL MENU PILIHAN — LANGSUNG DITAMPILKAN SECARA JELAS --}}
                        <div class="mt-6 pt-5 border-t border-gray-100">
                            <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wider mb-3 flex items-center justify-between">
                                <span class="flex items-center gap-1.5">
                                    <x-heroicon-o-clipboard-document-list class="w-4 h-4 text-emerald-600 shrink-0" />
                                    <span>Detail Menu Pilihan</span>
                                </span>
                                <span class="text-[10px] text-gray-400 font-normal font-mono">{{ $paket->jumlah ?? '-' }} {{ $satuan }}</span>
                            </h3>

                            <div class="bg-gray-50/80 rounded-2xl p-3.5 sm:p-4 border border-gray-100">
                                @php
                                    $hasKomponen = false;
                                    foreach($pesanan->detail_pesanan as $d) {
                                        if($d->pilihan_pesanan_catering && $d->pilihan_pesanan_catering->isNotEmpty()) {
                                            $hasKomponen = true;
                                            break;
                                        }
                                    }
                                @endphp

                                @if($hasKomponen)
                                    <div class="space-y-2 divide-y divide-gray-100">
                                        @foreach($pesanan->detail_pesanan as $detail)
                                            @foreach($detail->pilihan_pesanan_catering as $pilihan)
                                                <div class="flex justify-between items-center text-xs py-1.5 first:pt-0 last:pb-0 gap-2">
                                                    <span class="text-gray-500 font-medium">{{ $pilihan->komponen_paket->nama_komponen ?? '-' }}</span>
                                                    <span class="font-bold text-gray-900 text-right">{{ $pilihan->pilihan_komponen_paket->nama_pilihan ?? '-' }}</span>
                                                </div>
                                            @endforeach
                                        @endforeach
                                    </div>
                                @else
                                    <div class="space-y-2">
                                        @foreach($pesanan->detail_pesanan as $detail)
                                            <div class="flex justify-between items-center text-xs py-1 gap-2">
                                                <span class="text-gray-500 font-medium">Menu</span>
                                                <span class="font-bold text-gray-900">{{ $detail->menu->nama_menu ?? '-' }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </main>

        {{-- FULLSCREEN QR MODAL --}}
        <div x-show="showFullscreenQR" 
             style="display: none;"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/75 p-4"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-90"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-90">
            <div class="relative bg-white rounded-3xl p-6 sm:p-8 max-w-sm w-full shadow-2xl text-center space-y-4 font-sans mx-auto">
                {{-- Close Button --}}
                <button type="button" @click="showFullscreenQR = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 bg-gray-100 rounded-full p-1.5 transition-colors cursor-pointer">
                    <x-heroicon-o-x-mark class="w-5 h-5" />
                </button>

                <div class="space-y-1">
                    <h3 class="text-lg font-bold text-gray-900 tracking-tight">QRIS</h3>
                    <p class="text-xs text-gray-500 font-medium">Scan untuk membayar</p>
                </div>

                {{-- QR Code Image --}}
                <div class="p-3 bg-white border border-gray-200 rounded-2xl shadow-xs inline-block">
                    <div class="w-48 h-48 sm:w-56 sm:h-56 mx-auto flex items-center justify-center [&>svg]:w-full [&>svg]:h-full">
                        {!! SimpleSoftwareIO\QrCode\Facades\QrCode::size(220)->margin(1)->generate($dynamicQris) !!}
                    </div>
                </div>

                {{-- Details Box --}}
                <div class="bg-gray-50 rounded-2xl p-3.5 space-y-0.5 border border-gray-100">
                    <p class="text-xs font-semibold text-gray-700">Rumah Makan BBC</p>
                    <p class="text-xl sm:text-2xl font-black text-primary tracking-tight font-mono break-all">
                        Rp {{ number_format($amountToPay, 0, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function uploadForm() {
            return {
                filePreview: null,
                fileName: '',
                isImage: false,
                isPdf: false,
                isDragging: false,
                isSubmitting: false,
                handleFileChange(event) {
                    const file = event.target.files[0];
                    this.processFile(file);
                },
                handleDrop(event) {
                    const files = event.dataTransfer.files;
                    if (files && files.length > 0) {
                        try {
                            const dt = new DataTransfer();
                            dt.items.add(files[0]);
                            this.$refs.fileInput.files = dt.files;
                        } catch(e) {}
                        this.processFile(files[0]);
                    }
                },
                processFile(file) {
                    if (!file) return;
                    if (file.size > 1048576) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ icon: 'error', title: 'Ukuran Terlalu Besar', text: 'Ukuran file maksimal adalah 1 MB' });
                        } else {
                            alert('Ukuran file maksimal adalah 1 MB');
                        }
                        this.clearFile();
                        return;
                    }
                    const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
                    const fileExt = '.' + file.name.split('.').pop().toLowerCase();
                    if (!validTypes.includes(file.type) && !['.jpg', '.jpeg', '.png', '.pdf'].includes(fileExt)) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ icon: 'error', title: 'Format Tidak Didukung', text: 'Harap unggah file berformat JPG, PNG, atau PDF.' });
                        } else {
                            alert('Harap unggah file berformat JPG, PNG, atau PDF.');
                        }
                        this.clearFile();
                        return;
                    }
                    this.fileName = file.name;
                    this.isPdf = file.type === 'application/pdf' || fileExt === '.pdf';
                    this.isImage = !this.isPdf;

                    if (this.isImage) {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.filePreview = e.target.result;
                        };
                        reader.readAsDataURL(file);
                    } else {
                        this.filePreview = 'pdf_loaded';
                    }
                },
                clearFile() {
                    this.filePreview = null;
                    this.fileName = '';
                    this.isImage = false;
                    this.isPdf = false;
                    if (this.$refs.fileInput) {
                        this.$refs.fileInput.value = '';
                    }
                }
            };
        }
        window.uploadForm = uploadForm;

        function paymentPage(expireTimestamp) {
            return {
                showFullscreenQR: false,
                expireTime: expireTimestamp ? Number(expireTimestamp) : null,
                days: 0,
                hours: '00',
                minutes: '00',
                seconds: '00',
                isExpired: false,
                isSimulating: false,
                qrisTimer: null,
                init() {
                    if (this.expireTime) {
                        this.updateTimer();
                        setInterval(() => { this.updateTimer(); }, 1000);
                    }
                    this.startQrisPolling('{{ $pesanan->id_pesanan }}');
                },
                startQrisPolling(kode) {
                    if (this.qrisTimer) clearInterval(this.qrisTimer);
                    this.qrisTimer = setInterval(async () => {
                        try {
                            const res = await fetch('/pesan/bayar/status/' + kode);
                            const json = await res.json();
                            if (json && json.lunas) {
                                window.location.reload();
                            }
                        } catch (e) {}
                    }, 4000);
                },
                async simulasiQris(kode) {
                    this.isSimulating = true;
                    try {
                        const res = await fetch('{{ route("pesanan.simulasi_qris") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ kode_pesanan: kode })
                        });
                        const data = await res.json();
                        if (data.success) {
                            if (typeof window.showToast === 'function') {
                                window.showToast('success', data.message || 'Pembayaran QRIS Berhasil!');
                            } else if (typeof Swal !== 'undefined') {
                                Swal.fire({ icon: 'success', title: 'Berhasil', text: data.message || 'Pembayaran QRIS Berhasil!' });
                            }
                            setTimeout(() => { window.location.reload(); }, 600);
                        } else {
                            if (typeof window.showToast === 'function') {
                                window.showToast('error', data.message || 'Gagal verifikasi QRIS.');
                            } else if (typeof Swal !== 'undefined') {
                                Swal.fire({ icon: 'error', title: 'Gagal', text: data.message || 'Gagal verifikasi QRIS.' });
                            }
                        }
                    } catch(e) {
                        if (typeof window.showToast === 'function') {
                            window.showToast('error', 'Terjadi kesalahan jaringan.');
                        }
                    } finally {
                        this.isSimulating = false;
                    }
                },
                updateTimer() {
                    if (!this.expireTime) return;
                    const now = Date.now();
                    const distance = this.expireTime - now;
                    if (distance <= 0) {
                        this.isExpired = true;
                        this.days = 0;
                        this.hours = '00';
                        this.minutes = '00';
                        this.seconds = '00';
                        return;
                    }
                    const d = Math.floor(distance / (1000 * 60 * 60 * 24));
                    const h = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const m = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const s = Math.floor((distance % (1000 * 60)) / 1000);
                    
                    this.days = d;
                    this.hours = String(h).padStart(2, '0');
                    this.minutes = String(m).padStart(2, '0');
                    this.seconds = String(s).padStart(2, '0');
                }
            };
        }
        window.paymentPage = paymentPage;

        if (window.Alpine) {
            Alpine.data('uploadForm', uploadForm);
            Alpine.data('paymentPage', paymentPage);
        } else {
            document.addEventListener('alpine:init', () => {
                Alpine.data('uploadForm', uploadForm);
                Alpine.data('paymentPage', paymentPage);
            });
        }
    </script>
    @endpush
</x-layouts.landing>