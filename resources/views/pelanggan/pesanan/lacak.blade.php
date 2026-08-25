<x-layouts.landing>
    <x-slot:title>Lacak Pesanan — Saung Babakan Cinta</x-slot:title>

    <section x-data="lacakPageApp()" class="py-8 bg-gray-50/50 min-h-[calc(100vh-80px)]">
        <div class="w-full {{ $pesanan ? 'max-w-5xl' : 'max-w-2xl min-h-[60vh] flex flex-col justify-center' }} mx-auto px-4 sm:px-6">

            {{-- JIKA BELUM MELACAK ATAU PESANAN TIDAK DITEMUKAN: Form Pencarian --}}
            @if(!$pesanan)
                <div class="bg-white border border-gray-200 rounded-xl p-8 shadow-xs">
                    <div class="text-center mb-6">
                        <h1 class="text-2xl font-bold text-gray-900 tracking-tight mb-2">Lacak Pesanan Nasi Box & Katering</h1>
                        <p class="text-gray-500 text-sm">Masukkan kode pesanan Nasi Box atau Katering untuk melihat detail dan status operasional.</p>
                    </div>

                    <form method="GET" action="{{ route('lacak.index') }}" class="max-w-xl mx-auto">
                        <label for="kode_pesanan" class="sr-only">Nomor pesanan</label>
                        <div class="flex gap-2">
                            <div class="relative flex-1">
                                <input
                                    id="kode_pesanan"
                                    type="text"
                                    name="kode_pesanan"
                                    value="{{ $kodePesanan ?? '' }}"
                                    placeholder="Contoh: CAT-20260822-XXXX atau NB-XXXX"
                                    autocomplete="off"
                                    class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm font-mono focus:ring-2 focus:ring-emerald-600 focus:border-emerald-600 outline-none placeholder:text-gray-400 bg-white"
                                    required
                                >
                            </div>
                            <button type="submit" class="bg-emerald-700 hover:bg-emerald-800 text-white font-semibold px-5 py-2.5 rounded-lg text-sm transition-colors flex items-center gap-1.5 cursor-pointer shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                Lacak
                            </button>
                        </div>
                    </form>

                    @if(($isDineInError ?? false))
                        <div class="mt-6 p-4 bg-amber-50 border border-amber-200 rounded-lg text-amber-800 text-sm">
                            <span class="font-bold">Pesanan Dine-In tidak dapat dilacak:</span> Kode <code class="font-mono bg-amber-100 px-1 py-0.5 rounded">{{ $kodePesanan }}</code> merupakan pesanan Dine-In. Fitur lacak pesanan hanya tersedia untuk pesanan <strong>Nasi Box</strong> dan <strong>Katering</strong>.
                        </div>
                    @elseif($kodePesanan && !$pesanan)
                        <div class="mt-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
                            <span class="font-bold">Pesanan tidak ditemukan:</span> Kode <code class="font-mono bg-red-100 px-1 py-0.5 rounded">{{ $kodePesanan }}</code> tidak terdaftar dalam sistem. Pastikan kode pesanan yang dimasukkan benar dan merupakan pesanan Nasi Box atau Katering.
                        </div>
                    @endif
                </div>
            @endif

            {{-- JIKA PESANAN DITEMUKAN (TAMPILAN OPERASIONAL FLAT) --}}
            @if($pesanan)
            @php
                // Timeline status pesanan
                $timeline = [
                    ['key' => 'ditinjau',             'label' => 'Pesanan Masuk',          'desc' => 'Diterima sistem'],
                    ['key' => 'terkonfirmasi',        'label' => 'Dikonfirmasi',           'desc' => 'Diverifikasi admin'],
                    ['key' => 'diproses',             'label' => 'Diproses Dapur',         'desc' => 'Persiapan hidangan'],
                    ['key' => 'menunggu_pengiriman',  'label' => 'Siap Kirim / Saji',      'desc' => 'Selesai dimasak'],
                    ['key' => 'dalam_pengiriman',    'label' => 'Dalam Pengiriman',       'desc' => 'Menuju lokasi'],
                    ['key' => 'selesai',              'label' => 'Selesai',                'desc' => 'Pesanan tuntas'],
                ];

                $statusIdToKey = [1 => 'ditinjau', 2 => 'terkonfirmasi', 3 => 'diproses', 4 => 'menunggu_pengiriman', 5 => 'selesai', 6 => 'dibatalkan'];
                $statusKey = $statusIdToKey[$pesanan->status_pesanan_id] ?? 'ditinjau';
                
                if ($statusKey === 'menunggu_pengiriman' && $pesanan->pengiriman && $pesanan->pengiriman->status_pengiriman_id == 3) {
                    $statusKey = 'dalam_pengiriman';
                }
                
                $isCancelled = $statusKey === 'dibatalkan';

                $statusBadge = $isCancelled
                    ? ['label' => 'Dibatalkan', 'class' => 'bg-red-100 text-red-800 border-red-200']
                    : ($statusKey === 'selesai'
                        ? ['label' => 'Selesai', 'class' => 'bg-emerald-100 text-emerald-800 border-emerald-200']
                        : ['label' => collect($timeline)->firstWhere('key', $statusKey)['label'] ?? 'Ditinjau', 'class' => 'bg-emerald-50 text-emerald-800 border-emerald-300']);

                $total = (float) $pesanan->total_tagihan;
                $dpTerbayar = (float) $pesanan->pembayaran->where('status_verifikasi', 'diterima')->sum('jumlah_dibayar');
                $terakhirBayar = $pesanan->pembayaran->last();
                $statusVerifikasi = $terakhirBayar ? $terakhirBayar->status_verifikasi : null;

                $statusBayarKey = $dpTerbayar >= $total ? 'lunas' : ($statusVerifikasi === 'menunggu_verifikasi' ? 'menunggu_verifikasi' : ($statusVerifikasi === 'ditolak' ? 'ditolak' : ($dpTerbayar > 0 ? 'dp_terbayar' : 'belum_bayar')));
                $statusBayarInfo = [
                    'belum_bayar'         => ['label' => 'Belum Bayar',         'class' => 'bg-amber-50 text-amber-800 border-amber-200'],
                    'menunggu_verifikasi' => ['label' => 'Menunggu Verifikasi', 'class' => 'bg-blue-50 text-blue-800 border-blue-200'],
                    'dp_terbayar'         => ['label' => 'DP Terbayar',         'class' => 'bg-emerald-50 text-emerald-800 border-emerald-200'],
                    'lunas'               => ['label' => 'Lunas',               'class' => 'bg-emerald-100 text-emerald-800 border-emerald-300'],
                    'ditolak'             => ['label' => 'Pembayaran Ditolak',  'class' => 'bg-red-50 text-red-800 border-red-200'],
                ][$statusBayarKey];

                $statusOrder = array_column($timeline, 'key');
                $currentStatusIndex = array_search($statusKey, $statusOrder);
                if ($currentStatusIndex === false && $isCancelled) {
                    $currentStatusIndex = count($statusOrder);
                }

                $jenisPesanan = optional($pesanan->jenis_pesanan)->nama_jenis ?? 'Pesanan';

                // Hitung Batas Waktu Pembayaran (Countdown)
                $isPelunasan = ($dpTerbayar > 0 && $statusBayarKey !== 'lunas');
                $expireTime = null;
                $expireTimeStr = null;
                $deadlineLabel = 'Batas Waktu Pembayaran';

                if ($statusBayarKey === 'belum_bayar') {
                    $pembayaranFirst = $pesanan->pembayaran()->where('jenis_pembayaran', 'uang_muka')->first();
                    $expireTime = $pembayaranFirst && $pembayaranFirst->expires_at 
                        ? \Carbon\Carbon::parse($pembayaranFirst->expires_at)
                        : \Carbon\Carbon::parse($pesanan->dibuat_pada ?? $pesanan->created_at)->addHours(12);
                    $expireTimeStr = $expireTime->toIso8601String();
                    $deadlineLabel = 'Batas Pembayaran DP (12 Jam)';
                } elseif ($isPelunasan) {
                    if (!empty($pesanan->jadwal_pesanan->tanggal_acara)) {
                        $tglAcara = \Carbon\Carbon::parse($pesanan->jadwal_pesanan->tanggal_acara);
                        $waktuAcara = $pesanan->jadwal_pesanan->waktu_acara ?? null;
                        if (!empty($waktuAcara) && $waktuAcara !== '00:00:00') {
                            $expireTime = \Carbon\Carbon::parse($tglAcara->format('Y-m-d') . ' ' . $waktuAcara)->subDays(3);
                        } else {
                            $expireTime = $tglAcara->copy()->subDays(3)->endOfDay();
                        }
                        $expireTimeStr = $expireTime->toIso8601String();
                        $deadlineLabel = 'Batas Waktu Pelunasan (H-3 Acara)';
                    }
                }
            @endphp

            {{-- Navigasi Kembali --}}
            <div class="mb-4">
                <a href="{{ route('lacak.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-600 hover:text-emerald-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Kembali ke Pencarian</span>
                </a>
            </div>

            {{-- PANEL UTAMA OPERASIONAL --}}
            <div class="bg-white border border-gray-200 rounded-xl p-6 sm:p-8 shadow-xs space-y-8">

                {{-- 1. Header Informasi Utama --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-gray-200">
                    <div>
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="px-2 py-0.5 bg-gray-100 text-gray-700 text-xs font-bold uppercase rounded border border-gray-200">
                                {{ $jenisPesanan }}
                            </span>
                            <span class="text-xs text-gray-500">
                                Dibuat: {{ \Carbon\Carbon::parse($pesanan->created_at)->translatedFormat('d M Y, H:i') }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2.5" x-data="{ copied: false }">
                            <h1 class="text-xl sm:text-2xl font-bold font-mono text-gray-900 tracking-tight">{{ $pesanan->id_pesanan }}</h1>
                            <button
                                type="button"
                                @click="navigator.clipboard.writeText('{{ $pesanan->id_pesanan }}'); copied = true; setTimeout(() => copied = false, 1500)"
                                class="text-gray-400 hover:text-gray-700 p-1 rounded hover:bg-gray-100 transition-colors cursor-pointer"
                                title="Salin Kode Pesanan"
                            >
                                <svg x-show="!copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                <svg x-show="copied" x-cloak class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </button>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold rounded-full border bg-emerald-50 text-emerald-800 border-emerald-300">
                            <span class="text-gray-500 font-medium">Status Pesanan:</span>
                            <span class="font-bold">{{ $pesanan->status_pesanan->nama_status ?? 'Menunggu Konfirmasi' }}</span>
                        </span>
                        
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold rounded-full border {{ $statusBayarInfo['class'] }}">
                            <span class="text-gray-500 font-medium">Pembayaran:</span>
                            <span class="font-bold">{{ $pesanan->status_pembayaran->nama_status ?? $statusBayarInfo['label'] }}</span>
                        </span>

                        @if($pesanan->pengiriman)
                            @php
                                $shipLabel = $pesanan->pengiriman->status_pengiriman->nama_status ?? 'Dijadwalkan';
                                $shipClass = match((int)($pesanan->pengiriman->status_pengiriman_id ?? 1)) {
                                    1 => 'bg-blue-50 text-blue-800 border-blue-200',
                                    2 => 'bg-purple-50 text-purple-800 border-purple-200',
                                    3 => 'bg-amber-50 text-amber-800 border-amber-200',
                                    4 => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                                    5 => 'bg-rose-50 text-rose-800 border-rose-200',
                                    default => 'bg-gray-50 text-gray-700 border-gray-200',
                                };
                            @endphp
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold rounded-full border {{ $shipClass }}">
                                <span class="text-gray-500 font-medium">Pengiriman:</span>
                                <span class="font-bold">{{ $shipLabel }}</span>
                            </span>
                        @endif
                    </div>
                </div>

                @if($isCancelled)
                    <div class="p-4 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm">
                        <strong>Perhatian:</strong> Pesanan ini telah dibatalkan. Silakan hubungi admin jika terdapat kendala atau pertanyaan terkait pengembalian dana.
                    </div>
                @endif

                {{-- 2. Progress Stepper (Flat langsung pada halaman) --}}
                @if($jenisPesanan !== 'Dine In' && !$isCancelled)
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xs font-bold uppercase tracking-wider text-gray-700">Status Pesanan</h2>
                        <span class="text-xs text-gray-500 font-medium">
                            Tahap {{ $currentStatusIndex !== false ? ($currentStatusIndex + 1) : 1 }} dari {{ count($timeline) }}
                        </span>
                    </div>

                    <div class="overflow-x-auto no-scrollbar py-2">
                        <div class="min-w-[620px] flex items-start justify-between relative px-2">
                            {{-- Line Background --}}
                            <div class="absolute top-4 left-6 right-6 h-0.5 bg-gray-200 -z-0"></div>

                            {{-- Active Line --}}
                            @php
                                $stepCount = count($timeline);
                                $progressPercent = ($currentStatusIndex !== false && $stepCount > 1) 
                                    ? ($currentStatusIndex / ($stepCount - 1)) * 100 
                                    : 0;
                            @endphp
                            <div class="absolute top-4 left-6 h-0.5 bg-emerald-600 transition-all -z-0" style="width: {{ $progressPercent }}%;"></div>

                            @foreach($timeline as $i => $step)
                            @php
                                $isDone = $currentStatusIndex !== false && $i < $currentStatusIndex;
                                $isCurrent = $statusKey === $step['key'];
                            @endphp
                            <div class="flex-1 flex flex-col items-center text-center relative z-10 px-1">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold mb-2 transition-colors
                                    {{ $isCurrent 
                                        ? 'bg-emerald-700 text-white ring-2 ring-emerald-700 ring-offset-2' 
                                        : ($isDone 
                                            ? 'bg-emerald-600 text-white' 
                                            : 'bg-white border-2 border-gray-300 text-gray-400') }}">
                                    @if($isDone)
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    @else
                                        {{ $i + 1 }}
                                    @endif
                                </div>
                                <p class="text-xs font-semibold {{ $isCurrent ? 'text-emerald-900 font-bold' : ($isDone ? 'text-gray-900' : 'text-gray-400') }}">
                                    {{ $step['label'] }}
                                </p>
                                <span class="text-[11px] text-gray-500 mt-0.5 leading-tight">
                                    {{ $step['desc'] }}
                                </span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-200"></div>
                @endif

                {{-- Bukti Foto Pengiriman (Jika Sudah Selesai / Dikirim) --}}
                @if($pesanan->pengiriman && $pesanan->pengiriman->foto_bukti_pengiriman)
                <div class="bg-emerald-50/70 border border-emerald-200/80 rounded-2xl p-5 shadow-xs">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-start gap-3.5">
                            <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-xs">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-emerald-950 flex items-center gap-2">
                                    Pesanan Telah Diterima
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-200/80 text-emerald-800 border border-emerald-300">
                                        Selesai
                                    </span>
                                </h3>
                                <p class="text-xs text-emerald-800/90 mt-0.5 leading-relaxed">
                                    Pesanan telah berhasil diantarkan ke lokasi tujuan
                                    @if($pesanan->pengiriman->diterima_pada)
                                        pada <strong>{{ \Carbon\Carbon::parse($pesanan->pengiriman->diterima_pada)->translatedFormat('d F Y, H:i') }} WIB</strong>
                                    @endif
                                    @if(optional($pesanan->pengiriman->ditugaskan_kepada_pengguna)->nama)
                                        oleh kurir <strong>{{ $pesanan->pengiriman->ditugaskan_kepada_pengguna->nama }}</strong>
                                    @endif.
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 shrink-0">
                            <div class="relative group cursor-pointer" @click="openBukti('{{ asset('storage/' . $pesanan->pengiriman->foto_bukti_pengiriman) }}', 'Bukti Pengiriman - {{ $pesanan->id_pesanan }}', '0', 'Bukti Foto Pengiriman')">
                                <img src="{{ asset('storage/' . $pesanan->pengiriman->foto_bukti_pengiriman) }}" alt="Bukti Pengiriman" class="w-14 h-14 rounded-xl object-cover border-2 border-emerald-300 group-hover:scale-105 transition-transform shadow-xs bg-white" />
                                <div class="absolute inset-0 bg-black/20 rounded-xl flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                    <svg class="w-5 h-5 text-white drop-shadow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                                </div>
                            </div>

                            <button type="button" 
                                    @click="openBukti('{{ asset('storage/' . $pesanan->pengiriman->foto_bukti_pengiriman) }}', 'Bukti Pengiriman - {{ $pesanan->id_pesanan }}', '0', 'Bukti Foto Pengiriman')"
                                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-emerald-700 hover:bg-emerald-800 transition-all shadow-xs cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span>Lihat Foto Bukti</span>
                            </button>
                        </div>
                    </div>
                </div>
                @endif

                {{-- 3. Grid Dua Kolom: Informasi & Pembayaran --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-12 items-start">

                    {{-- Kolom Kiri: Informasi Pesanan --}}
                    <div class="space-y-4">
                        <h2 class="text-xs font-bold uppercase tracking-wider text-gray-700 pb-2 border-b border-gray-200">
                            Informasi Pesanan
                        </h2>

                        <table class="w-full text-xs text-left">
                            <tbody class="divide-y divide-gray-100">
                                <tr>
                                    <td class="py-2.5 text-gray-500 font-medium w-1/3">Atas Nama</td>
                                    <td class="py-2.5 text-gray-900 font-semibold">{{ optional($pesanan->pelanggan)->nama ?? $pesanan->jadwal_pesanan->nama_penerima ?? 'Tamu' }}</td>
                                </tr>
                                @if($pesanan->jadwal_pesanan)
                                <tr>
                                    <td class="py-2.5 text-gray-500 font-medium">Kontak WhatsApp</td>
                                    @php 
                                        $lacakKontak = $pesanan->jadwal_pesanan->kontak ?? $pesanan->jadwal_pesanan->nomor_telepon_penerima ?? ''; 
                                    @endphp
                                    <td class="py-2.5 text-gray-900 font-mono font-medium">{{ $lacakKontak ? \App\Support\WhatsAppNumber::formatForDisplay($lacakKontak) : '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 text-gray-500 font-medium">Tanggal Acara</td>
                                    <td class="py-2.5 text-gray-900 font-semibold">{{ \Carbon\Carbon::parse($pesanan->jadwal_pesanan->tanggal_acara)->translatedFormat('d F Y') }}</td>
                                </tr>
                                @if($pesanan->jadwal_pesanan->alamat_pengiriman)
                                <tr>
                                    <td class="py-2.5 text-gray-500 font-medium align-top">Alamat Pengiriman</td>
                                    <td class="py-2.5 text-gray-900 leading-relaxed">{{ $pesanan->jadwal_pesanan->alamat_pengiriman }}</td>
                                </tr>
                                @endif
                                @endif
                                <tr>
                                    <td class="py-2.5 text-gray-500 font-medium align-top">Menu & Porsi</td>
                                    <td class="py-2.5 text-gray-900">
                                        @foreach($pesanan->detail_pesanan as $detail)
                                            <div class="{{ !$loop->first ? 'mt-2.5 pt-2.5 border-t border-gray-100' : '' }}">
                                                <div class="font-semibold text-gray-900">
                                                    {{ $detail->menu->nama_menu ?? '-' }} &times; {{ $detail->jumlah ?? '-' }} {{ $pesanan->jenis_pesanan_id == 3 ? 'Box' : 'Porsi' }}
                                                </div>
                                                @if($detail->pilihan_pesanan_catering && $detail->pilihan_pesanan_catering->count() > 0)
                                                    <div class="text-xs text-gray-600 mt-1 space-y-0.5">
                                                        @foreach($detail->pilihan_pesanan_catering as $pil)
                                                            <div class="flex items-center gap-1.5">
                                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                                                                <span>{{ $pil->komponen_paket->nama_komponen ?? '-' }}: <strong class="text-gray-900 font-medium">{{ $pil->pilihan_komponen_paket->nama_pilihan ?? '-' }}</strong></span>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </td>
                                </tr>
                                @if($pesanan->catatan)
                                <tr>
                                    <td class="py-2.5 text-gray-500 font-medium align-top">Catatan Pesanan</td>
                                    <td class="py-2.5 text-gray-900">{{ $pesanan->catatan }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td class="py-2.5 text-gray-500 font-medium">Metode Pengiriman</td>
                                    <td class="py-2.5 text-gray-900 font-semibold">
                                        @php
                                            $rawKirim = strtolower($pesanan->pengiriman->metode_pengiriman ?? $pesanan->metode_pengiriman ?? '');
                                            $isKirimDiantar = in_array($rawKirim, ['delivery', 'diantar', 'kurir']) || $pesanan->pengiriman || ((float)($pesanan->ongkir ?? 0) > 0);
                                        @endphp
                                        <div class="flex items-center justify-between">
                                            <span>{{ $isKirimDiantar ? 'Diantar' : 'Diambil di Resto' }}</span>
                                            @if($pesanan->pengiriman && $pesanan->pengiriman->foto_bukti_pengiriman)
                                                <button type="button" 
                                                        @click="openBukti('{{ asset('storage/' . $pesanan->pengiriman->foto_bukti_pengiriman) }}', 'Bukti Pengiriman - {{ $pesanan->id_pesanan }}', '0', 'Bukti Foto Pengiriman')"
                                                        class="text-emerald-700 hover:text-emerald-900 font-semibold underline text-[11px] cursor-pointer inline-flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                    <span>Foto Bukti</span>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @php
                                    $metodeBayarList = $pesanan->pembayaran
                                        ->filter(fn($p) => !empty($p->metode_pembayaran))
                                        ->map(function($p) {
                                            return match(strtolower($p->metode_pembayaran)) {
                                                'transfer_bank', 'transfer' => 'Transfer Bank',
                                                'qris' => 'QRIS',
                                                'tunai', 'cash' => 'Tunai',
                                                default => ucwords(str_replace('_', ' ', $p->metode_pembayaran))
                                            };
                                        })
                                        ->unique()
                                        ->values();

                                    $metodeBayarDisplay = $metodeBayarList->isNotEmpty() 
                                        ? $metodeBayarList->join(', ') 
                                        : (match(strtolower($pesanan->metode_pembayaran ?? '')) {
                                            'transfer_bank', 'transfer' => 'Transfer Bank',
                                            'qris' => 'QRIS',
                                            'tunai', 'cash' => 'Tunai',
                                            default => (!empty($pesanan->metode_pembayaran) ? ucwords(str_replace('_', ' ', $pesanan->metode_pembayaran)) : null)
                                        });
                                @endphp
                                @if($metodeBayarDisplay)
                                <tr>
                                    <td class="py-2.5 text-gray-500 font-medium">Metode Pembayaran</td>
                                    <td class="py-2.5 text-gray-900 font-semibold">{{ $metodeBayarDisplay }}</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    {{-- Kolom Kanan: Rincian Pembayaran & Countdown & Tombol Aksi --}}
                    <div class="space-y-4">
                        <h2 class="text-xs font-bold uppercase tracking-wider text-gray-700 pb-2 border-b border-gray-200">
                            Rincian Pembayaran
                        </h2>

                        {{-- Countdown Timer Batas Pembayaran --}}
                        @if($expireTimeStr && in_array($statusBayarKey, ['belum_bayar', 'dp_terbayar']) && !$isCancelled)
                        <div x-data="{
                                expireTime: new Date('{{ $expireTimeStr }}').getTime(),
                                now: Date.now(),
                                timer: null,
                                init() {
                                    this.timer = setInterval(() => { this.now = Date.now(); }, 1000);
                                },
                                get remaining() {
                                    return Math.max(0, this.expireTime - this.now);
                                },
                                get isExpired() {
                                    return this.remaining <= 0;
                                },
                                get days() {
                                    return Math.floor(this.remaining / (1000 * 60 * 60 * 24));
                                },
                                get hours() {
                                    return Math.floor((this.remaining % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                                },
                                get minutes() {
                                    return Math.floor((this.remaining % (1000 * 60)) / (1000 * 60));
                                },
                                get seconds() {
                                    return Math.floor((this.remaining % (1000 * 60)) / 1000);
                                },
                                pad(n) {
                                    return String(n).padStart(2, '0');
                                }
                             }"
                             class="p-3 bg-amber-50/90 border border-amber-200 rounded-lg">
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-1.5 text-xs font-semibold text-amber-900">
                                    <svg class="w-4 h-4 text-amber-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>{{ $deadlineLabel }}</span>
                                </div>
                                <template x-if="!isExpired">
                                    <div class="flex items-center font-mono font-bold text-xs text-amber-900 bg-white px-2.5 py-1 rounded border border-amber-200 shadow-2xs">
                                        <template x-if="days > 0"><span x-text="days + 'h '"></span></template>
                                        <template x-if="hours > 0 || days > 0"><span x-text="pad(hours) + ':'"></span></template>
                                        <span x-text="pad(minutes) + ':' + pad(seconds)"></span>
                                    </div>
                                </template>
                                <template x-if="isExpired">
                                    <span class="text-xs font-bold text-red-700 bg-red-100 px-2 py-0.5 rounded border border-red-200">Waktu Habis</span>
                                </template>
                            </div>
                            <div class="text-[11px] text-amber-800 mt-1 flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                                <span>Batas akhir: <strong class="text-amber-950">{{ \Carbon\Carbon::parse($expireTime)->translatedFormat('d M Y, H:i') }} WIB</strong></span>
                                @if($isPelunasan && !empty($pesanan->jadwal_pesanan->tanggal_acara))
                                    <span class="text-amber-700 font-medium">(H-3 pelaksanaan acara: {{ \Carbon\Carbon::parse($pesanan->jadwal_pesanan->tanggal_acara)->translatedFormat('d M Y') }})</span>
                                @endif
                            </div>
                        </div>
                        @endif

                        <table class="w-full text-xs text-left">
                            <tbody class="divide-y divide-gray-100">
                                <tr>
                                    <td class="py-2.5 text-gray-600 font-medium">Total Tagihan</td>
                                    <td class="py-2.5 text-right font-mono font-semibold text-gray-900 text-sm">Rp {{ number_format($total, 0, ',', '.') }}</td>
                                </tr>
                                @if($dpTerbayar > 0 && $statusBayarKey !== 'lunas')
                                <tr>
                                    <td class="py-2.5 text-emerald-700 font-medium">DP Terbayar</td>
                                    <td class="py-2.5 text-right font-mono font-semibold text-emerald-700 text-sm">- Rp {{ number_format($dpTerbayar, 0, ',', '.') }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td class="py-3 font-bold text-gray-900">
                                        @if($statusBayarKey === 'lunas')
                                            Status Lunas
                                        @else
                                            Sisa Tagihan
                                        @endif
                                    </td>
                                    <td class="py-3 text-right font-mono font-bold text-base {{ $statusBayarKey === 'lunas' ? 'text-emerald-700' : 'text-red-600' }}">
                                        @if($statusBayarKey === 'lunas')
                                            Rp 0 (LUNAS)
                                        @else
                                            Rp {{ number_format(max(0, $total - $dpTerbayar), 0, ',', '.') }}
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        {{-- Tombol Aksi Operasional --}}
                        <div class="pt-2 space-y-2">
                            @if(in_array($statusBayarKey, ['belum_bayar', 'dp_terbayar']) && !$isCancelled)
                                <a href="{{ route('pesanan.bayar', $pesanan->id_pesanan) }}" class="w-full flex items-center justify-center gap-2 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold py-2.5 px-4 rounded-lg text-xs transition-colors cursor-pointer">
                                    <span>{{ $statusBayarKey === 'dp_terbayar' ? 'Lanjutkan Pelunasan Pembayaran' : 'Bayar Sekarang' }}</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </a>
                            @endif

                            @if($jenisPesanan !== 'Dine In')
                                <a href="{{ route('pesanan.invoice', $pesanan->id_pesanan) }}" target="_blank" class="w-full flex items-center justify-center gap-2 bg-white hover:bg-gray-50 text-gray-700 font-semibold py-2.5 px-4 rounded-lg text-xs border border-gray-300 transition-colors cursor-pointer">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 16l5 5 5-5M12 3v12"/></svg>
                                    <span>Unduh Bukti Pesanan (PDF)</span>
                                </a>
                            @endif
                        </div>
                    </div>

                </div>

                {{-- 4. Riwayat Pembayaran Sebelumnya --}}
                @php
                    $riwayatPembayaran = $pesanan->pembayaran->filter(function($p) {
                        return $p->status_verifikasi !== 'belum_dibayar' || !empty($p->tanggal_pembayaran) || !empty($p->bukti_pembayaran);
                    });
                @endphp

                @if($riwayatPembayaran->isNotEmpty())
                <div class="pt-6 border-t border-gray-200 space-y-3">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xs font-bold uppercase tracking-wider text-gray-700">
                            Riwayat Pembayaran Sebelumnya
                        </h2>
                        <span class="text-xs text-gray-500 font-medium">
                            {{ $riwayatPembayaran->count() }} Transaksi
                        </span>
                    </div>

                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-gray-50 border-b border-gray-200 text-gray-600 font-bold uppercase tracking-wider">
                                <tr>
                                    <th class="py-2.5 px-3">Kode / Referensi</th>
                                    <th class="py-2.5 px-3">Jenis Pembayaran</th>
                                    <th class="py-2.5 px-3">Metode</th>
                                    <th class="py-2.5 px-3">Tanggal Pembayaran</th>
                                    <th class="py-2.5 px-3 text-right">Jumlah Dibayar</th>
                                    <th class="py-2.5 px-3 text-center">Status</th>
                                    <th class="py-2.5 px-3 text-center">Bukti</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @foreach($riwayatPembayaran as $pemb)
                                @php
                                    $isDiterima = in_array($pemb->status_verifikasi, ['diterima', 'diverifikasi', 'lunas', 'berhasil']);
                                    $isMenunggu = $pemb->status_verifikasi === 'menunggu_verifikasi';
                                    $isDitolak = $pemb->status_verifikasi === 'ditolak';
                                    
                                    $jenisBayarText = match($pemb->jenis_pembayaran) {
                                        'uang_muka', 'dp' => 'Uang Muka (DP)',
                                        'pelunasan' => 'Pelunasan',
                                        'lunas', 'penuh' => 'Pembayaran Penuh',
                                        default => ucwords(str_replace('_', ' ', $pemb->jenis_pembayaran ?? 'Pembayaran'))
                                    };

                                    $metodeBayarText = match(strtolower($pemb->metode_pembayaran ?? '')) {
                                        'transfer_bank', 'transfer' => 'Transfer Bank',
                                        'qris' => 'QRIS',
                                        'tunai', 'cash' => 'Tunai',
                                        default => strtoupper($pemb->metode_pembayaran ?? '-')
                                    };
                                @endphp
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="py-2.5 px-3 font-mono font-medium text-gray-900">
                                        {{ $pemb->kode_pembayaran }}
                                    </td>
                                    <td class="py-2.5 px-3 font-semibold text-gray-900">
                                        {{ $jenisBayarText }}
                                    </td>
                                    <td class="py-2.5 px-3 text-gray-700">
                                        {{ $metodeBayarText }}
                                    </td>
                                    <td class="py-2.5 px-3 text-gray-600">
                                        {{ $pemb->tanggal_pembayaran ? \Carbon\Carbon::parse($pemb->tanggal_pembayaran)->translatedFormat('d M Y, H:i') : '-' }}
                                    </td>
                                    <td class="py-2.5 px-3 text-right font-mono font-bold text-gray-900">
                                        Rp {{ number_format($pemb->jumlah_dibayar, 0, ',', '.') }}
                                    </td>
                                    <td class="py-2.5 px-3 text-center">
                                        @if($isDiterima)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                ✓ Diterima
                                            </span>
                                        @elseif($isMenunggu)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-blue-50 text-blue-800 border border-blue-200">
                                                Verifikasi
                                            </span>
                                        @elseif($isDitolak)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-red-100 text-red-800 border border-red-200">
                                                Ditolak
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-gray-100 text-gray-700">
                                                {{ ucwords(str_replace('_', ' ', $pemb->status_verifikasi)) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-3 text-center">
                                        @if($pemb->bukti_pembayaran && $pemb->bukti_pembayaran !== 'midtrans_online')
                                            <button type="button" 
                                                    @click="openBukti('{{ asset('storage/' . $pemb->bukti_pembayaran) }}', '{{ $pemb->kode_pembayaran }} - {{ $jenisBayarText }}', '{{ Str::endsWith(strtolower($pemb->bukti_pembayaran), ['.pdf']) ? '1' : '0' }}')"
                                                    class="text-emerald-700 hover:text-emerald-900 font-semibold underline text-[11px] cursor-pointer inline-flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                <span>Lihat Bukti</span>
                                            </button>
                                        @elseif($pemb->metode_pembayaran === 'qris')
                                            <span class="text-gray-400 text-[11px]">Otomatis QRIS</span>
                                        @else
                                            <span class="text-gray-400 text-[11px]">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @if($isDitolak && !empty($pemb->catatan_verifikasi))
                                <tr class="bg-red-50/60 text-red-700 text-[11px]">
                                    <td colspan="7" class="py-1.5 px-3">
                                        <strong>Alasan Penolakan:</strong> {{ $pemb->catatan_verifikasi }}
                                    </td>
                                </tr>
                                @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

            </div>
            @endif

        </div>

        {{-- Modal Pop-up Lihat Bukti Pembayaran --}}
        <div x-show="buktiModal" 
             x-cloak
             class="fixed inset-0 z-[99999] flex items-center justify-center p-4 overflow-y-auto"
             role="dialog" 
             aria-modal="true">
            
            {{-- Backdrop --}}
            <div x-show="buktiModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs"
                 @click="buktiModal = false"></div>

            {{-- Dialog Box --}}
            <div x-show="buktiModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="relative bg-white rounded-2xl shadow-2xl border border-gray-200 w-full max-w-2xl overflow-hidden z-10 flex flex-col max-h-[90vh]">
                
                {{-- Header --}}
                <div class="flex items-center justify-between px-5 py-3.5 border-b border-gray-200 bg-gray-50/80">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900" x-text="buktiHeader"></h3>
                            <p class="text-xs text-gray-500 font-mono" x-text="buktiTitle"></p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <a :href="buktiUrl" target="_blank" download class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 hover:text-emerald-700 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span>Unduh</span>
                        </a>
                        <button type="button" @click="buktiModal = false" class="p-1.5 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                {{-- Content View --}}
                <div class="p-5 flex-1 overflow-y-auto flex items-center justify-center bg-gray-100/50">
                    <template x-if="isPdf !== '1'">
                        <img :src="buktiUrl" alt="Foto Bukti" class="max-h-[65vh] max-w-full rounded-lg shadow-xs object-contain border border-gray-200 bg-white" />
                    </template>
                    <template x-if="isPdf === '1'">
                        <iframe :src="buktiUrl" class="w-full h-[65vh] rounded-lg border border-gray-200 bg-white"></iframe>
                    </template>
                </div>

                {{-- Footer --}}
                <div class="px-5 py-3 border-t border-gray-200 bg-white flex justify-end">
                    <button type="button" @click="buktiModal = false" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-lg transition-colors cursor-pointer">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </section>

    <script>
        function lacakPageApp() {
            return {
                buktiModal: false,
                buktiUrl: '',
                buktiTitle: '',
                buktiHeader: 'Bukti Pembayaran',
                isPdf: '0',
                openBukti(url, title, isPdf, header = 'Bukti Pembayaran') {
                    this.buktiUrl = url;
                    this.buktiTitle = title;
                    this.isPdf = isPdf;
                    this.buktiHeader = header;
                    this.buktiModal = true;
                }
            };
        }
    </script>
</x-layouts.landing>