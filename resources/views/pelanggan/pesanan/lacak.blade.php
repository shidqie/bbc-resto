<x-layouts.landing>
    <x-slot:title>Lacak Pesanan — Saung Babakan Cinta</x-slot:title>

    <section class="py-16 bg-canvas min-h-screen">
        <div class="max-w-5xl mx-auto px-4 lg:px-8">

            {{-- Header --}}
            <div class="text-center mb-12">
                <div class="w-14 h-14 bg-white shadow-sm border border-gray-100 text-emerald-600 rounded-2xl flex items-center justify-center mx-auto mb-6 transform rotate-3">
                    <svg class="w-7 h-7 -rotate-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-3 tracking-tight">Lacak Pesanan</h1>
                <p class="text-gray-500 text-sm max-w-md mx-auto leading-relaxed">Masukkan kode pesanan Anda untuk memantau status secara langsung.</p>
            </div>

            {{-- Search Form --}}
            <form method="GET" action="{{ route('lacak.index') }}" class="mb-8">
                <label for="kode_pesanan" class="sr-only">Nomor pesanan</label>
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="relative flex-1">
                        <svg class="w-4 h-4 text-gray-300 absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input
                            id="kode_pesanan"
                            type="text"
                            name="kode_pesanan"
                            value="{{ $kodePesanan ?? '' }}"
                            placeholder="Contoh: CAT-20240728-XXXX"
                            autocomplete="off"
                            class="w-full border border-gray-200 rounded-xl pl-11 pr-4 py-3 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none shadow-sm placeholder:text-gray-300"
                            required
                        >
                    </div>
                    <button type="submit" class="bg-[#0D3024] hover:bg-[#0a1f17] text-white font-bold px-6 py-3 rounded-xl text-sm transition-all shadow-sm flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Lacak
                    </button>
                </div>
            </form>

            {{-- Not found --}}
            @if($kodePesanan && !$pesanan)
                <div class="bg-red-50 border border-red-200 rounded-xl p-6 text-center">
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-red-800 mb-1">Pesanan Tidak Ditemukan</h3>
                    <p class="text-red-600 text-sm">
                        Pesanan dengan nomor <strong>{{ $kodePesanan }}</strong> tidak ditemukan.
                        Pastikan kode yang dimasukkan sudah benar, atau hubungi kami jika Anda yakin ini adalah kesalahan.
                    </p>
                </div>
            @endif

            {{-- Result --}}
            @if($pesanan)
            @php
                // Single source of truth for both the timeline and the status badge,
                // so labels/desc never drift out of sync between the two.
                $timeline = [
                    ['key' => 'ditinjau',             'label' => 'Pesanan Diterima',       'desc' => 'Pesanan telah masuk ke sistem'],
                    ['key' => 'terkonfirmasi',        'label' => 'Dikonfirmasi Admin',     'desc' => 'Pembayaran/Pesanan telah dikonfirmasi'],
                    ['key' => 'diproses',             'label' => 'Sedang Diproses',        'desc' => 'Dapur sedang menyiapkan hidangan'],
                    ['key' => 'menunggu_pengiriman',  'label' => 'Menunggu Pengiriman',    'desc' => 'Makanan siap untuk dikirim/disajikan'],
                    ['key' => 'dalam_pengiriman',    'label' => 'Dalam Pengiriman',      'desc' => 'Kurir sedang mengantar pesanan ke lokasi Anda'],
                    ['key' => 'selesai',              'label' => 'Pesanan Selesai',        'desc' => 'Pesanan berhasil diterima & selesai'],
                ];

                $statusIdToKey = [1 => 'ditinjau', 2 => 'terkonfirmasi', 3 => 'diproses', 4 => 'menunggu_pengiriman', 5 => 'selesai', 6 => 'dibatalkan'];
                $statusKey = $statusIdToKey[$pesanan->status_pesanan_id] ?? 'ditinjau';
                
                if ($statusKey === 'menunggu_pengiriman' && $pesanan->pengiriman && $pesanan->pengiriman->status_pengiriman_id == 3) {
                    $statusKey = 'dalam_pengiriman';
                }
                
                $isCancelled = $statusKey === 'dibatalkan';

                // One consistent palette: gray = not yet, blue = in progress, emerald = done/success, rose = cancelled, amber = awaiting payment.
                $statusBadge = $isCancelled
                    ? ['label' => 'Dibatalkan', 'color' => 'bg-rose-50 text-rose-700 border-rose-200/60']
                    : ($statusKey === 'selesai'
                        ? ['label' => 'Pesanan Selesai', 'color' => 'bg-emerald-50 text-emerald-700 border-emerald-200/60']
                        : ['label' => collect($timeline)->firstWhere('key', $statusKey)['label'] ?? 'Pesanan Diterima', 'color' => 'bg-blue-50 text-blue-700 border-blue-200/60']);

                $total = (float) $pesanan->total_tagihan;
                $dpTerbayar = (float) $pesanan->pembayaran->where('status_verifikasi', 'diterima')->sum('jumlah_dibayar');
                $terakhirBayar = $pesanan->pembayaran->last();
                $statusVerifikasi = $terakhirBayar ? $terakhirBayar->status_verifikasi : null;

                $statusBayarKey = $dpTerbayar >= $total ? 'lunas' : ($statusVerifikasi === 'menunggu_verifikasi' ? 'menunggu_verifikasi' : ($statusVerifikasi === 'ditolak' ? 'ditolak' : ($dpTerbayar > 0 ? 'dp_terbayar' : 'belum_bayar')));
                $statusBayarInfo = [
                    'belum_bayar'         => ['label' => 'Belum Bayar',         'color' => 'bg-amber-50 text-amber-700 border-amber-200/60'],
                    'menunggu_verifikasi' => ['label' => 'Menunggu Verifikasi', 'color' => 'bg-blue-50 text-blue-700 border-blue-200/60'],
                    'dp_terbayar'         => ['label' => 'DP Terbayar',         'color' => 'bg-emerald-50 text-emerald-700 border-emerald-200/60'],
                    'lunas'               => ['label' => 'Lunas',               'color' => 'bg-emerald-50 text-emerald-700 border-emerald-200/60'],
                    'ditolak'             => ['label' => 'Ditolak',             'color' => 'bg-red-50 text-red-700 border-red-200/60'],
                ][$statusBayarKey];

                $statusOrder = array_column($timeline, 'key');
                $currentStatusIndex = array_search($statusKey, $statusOrder);
                if ($currentStatusIndex === false && $isCancelled) {
                    $currentStatusIndex = count($statusOrder);
                }
            @endphp

            <div class="mb-12 bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-10">

                {{-- Header Status --}}
                <div class="text-center pb-8 border-b border-gray-100 mb-10">
                    <span class="inline-block px-3 py-1 bg-gray-50 text-gray-500 font-['Anonymous_Pro'] text-[11px] font-bold uppercase tracking-widest rounded-full mb-4 border border-gray-200/60">
                        {{ $jenisPesanan }}
                    </span>

                    <div class="flex items-center justify-center gap-2 mb-5" x-data="{ copied: false }">
                        <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 tracking-tight break-all">{{ $pesanan->id_pesanan }}</h2>
                        <button
                            type="button"
                            @click="navigator.clipboard.writeText('{{ $pesanan->id_pesanan }}'); copied = true; setTimeout(() => copied = false, 1500)"
                            class="text-gray-400 hover:text-emerald-600 transition-colors shrink-0 bg-gray-50 hover:bg-emerald-50 p-1.5 rounded-lg"
                            aria-label="Salin nomor pesanan"
                        >
                            <svg x-show="!copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            <svg x-show="copied" x-cloak class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </button>
                    </div>

                    <div class="flex flex-wrap justify-center items-center gap-2">
                        <span class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-widest border {{ $statusBadge['color'] }}">
                            <span class="w-1.5 h-1.5 rounded-full bg-current mr-2"></span>
                            {{ $statusBadge['label'] }}
                        </span>
                        @if(!$isCancelled)
                        <span class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-widest border {{ $statusBayarInfo['color'] }}">
                            <span class="w-1.5 h-1.5 rounded-full bg-current mr-2"></span>
                            {{ $statusBayarInfo['label'] }}
                        </span>
                        @endif
                    </div>

                    @if($isCancelled)
                        <p class="text-rose-600 text-sm mt-4">Pesanan ini telah dibatalkan dan tidak akan diproses lebih lanjut.</p>
                    @endif
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 items-start">

                    {{-- Left Column: Info & Billing --}}
                    <div class="lg:col-span-6 space-y-8">
                        <div>
                            <h3 class="font-['Anonymous_Pro'] text-xs font-bold text-gray-400 uppercase tracking-widest mb-6">Informasi Pesanan</h3>

                            <dl class="space-y-4 text-sm text-gray-600">
                                <div class="flex justify-between items-center py-2.5 border-b border-gray-50">
                                    <dt class="text-gray-400 font-light">Atas Nama</dt>
                                    <dd class="font-medium text-gray-800 text-base">{{ optional($pesanan->pelanggan)->nama ?? $pesanan->jadwal_pesanan->nama_penerima ?? 'Tamu' }}</dd>
                                </div>
                                @if($pesanan->jadwal_pesanan)
                                <div class="flex justify-between items-center py-2.5 border-b border-gray-50">
                                    <dt class="text-gray-400 font-light">Kontak</dt>
                                    @php 
                                        $lacakKontak = $pesanan->jadwal_pesanan->kontak ?? $pesanan->jadwal_pesanan->nomor_telepon_penerima ?? ''; 
                                    @endphp
                                    <dd class="font-medium text-gray-800">{{ $lacakKontak ? \App\Support\WhatsAppNumber::formatForDisplay($lacakKontak) : '-' }}</dd>
                                </div>
                                <div class="flex justify-between items-center py-2.5 border-b border-gray-50">
                                    <dt class="text-gray-400 font-light">Tanggal Acara</dt>
                                    <dd class="font-medium text-gray-800">{{ \Carbon\Carbon::parse($pesanan->jadwal_pesanan->tanggal_acara)->translatedFormat('d M Y') }}</dd>
                                </div>
                                @if($pesanan->jadwal_pesanan->alamat_pengiriman)
                                <div class="flex justify-between items-start py-2.5 border-b border-gray-50">
                                    <dt class="text-gray-400 font-light shrink-0">Alamat Acara</dt>
                                    <dd class="font-medium text-gray-800 text-right max-w-[65%] leading-relaxed">{{ $pesanan->jadwal_pesanan->alamat_pengiriman }}</dd>
                                </div>
                                @endif
                                @endif
                                <div class="flex justify-between items-center py-2.5 border-b border-gray-50">
                                    <dt class="text-gray-400 font-light">Paket</dt>
                                    <dd class="font-medium text-gray-800 text-right">{{ $pesanan->detail_pesanan->first()->menu->nama_menu ?? '-' }} &times; {{ $pesanan->detail_pesanan->first()->jumlah ?? '-' }} Porsi</dd>
                                </div>
                                @if($pesanan->detail_pesanan->count() > 1)
                                <div class="flex justify-end -mt-2 pb-2.5 border-b border-gray-50">
                                    <span class="text-[11px] text-gray-400 bg-gray-50 px-2 py-0.5 rounded-full">+ {{ $pesanan->detail_pesanan->count() - 1 }} item lainnya</span>
                                </div>
                                @endif
                                @if($pesanan->pengiriman)
                                <div class="flex justify-between items-center py-2.5 border-b border-gray-50">
                                    <dt class="text-gray-400 font-light">Metode Pengiriman</dt>
                                    <dd class="font-medium text-gray-800 capitalize">{{ $pesanan->pengiriman->metode_pengiriman ?? 'Delivery' }}</dd>
                                </div>
                                @endif
                                @if($pesanan->pembayaran->isNotEmpty())
                                <div class="flex justify-between items-center py-2.5 border-b border-gray-50">
                                    <dt class="text-gray-400 font-light">Metode Pembayaran</dt>
                                    <dd class="font-medium text-gray-800">{{ $pesanan->pembayaran->first()->metode_pembayaran ?? '-' }}</dd>
                                </div>
                                @endif
                            </dl>
                        </div>

                        {{-- Total & Payment Box --}}
                        <div class="bg-gray-50/50 rounded-2xl p-6 border border-gray-100">
                            <div class="flex justify-between items-center text-sm mb-1">
                                <span class="text-gray-500">Total Tagihan</span>
                                <span class="font-bold text-gray-900 text-lg">Rp {{ number_format($total, 0, ',', '.') }}</span>
                            </div>

                            @if($statusBayarKey === 'lunas')
                            <div class="flex justify-between items-center text-sm text-emerald-600 pt-4 border-t border-gray-100 font-bold mt-3">
                                <span>Telah Dibayar (LUNAS)</span>
                                <span class="text-xl">Rp {{ number_format($total, 0, ',', '.') }}</span>
                            </div>
                            @elseif($dpTerbayar > 0)
                            <div class="flex justify-between items-center text-sm text-emerald-600 mt-2 mb-3">
                                <span>DP Terbayar</span>
                                <span class="font-semibold">- Rp {{ number_format($dpTerbayar, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center pt-4 border-t border-gray-100 text-rose-600 font-bold">
                                <span>Sisa Pelunasan</span>
                                <span class="text-xl">Rp {{ number_format($total - $dpTerbayar, 0, ',', '.') }}</span>
                            </div>
                            @else
                            <div class="flex justify-between items-center pt-4 border-t border-gray-100 text-amber-600 font-bold mt-3">
                                <span>Belum Ada Pembayaran</span>
                                <span class="text-xl">Rp {{ number_format($total, 0, ',', '.') }}</span>
                            </div>
                            @endif
                        </div>

                        {{-- Action Buttons --}}
                        <div class="space-y-3 pt-2">
                            @if(in_array($statusBayarKey, ['belum_bayar', 'dp_terbayar']) && !$isCancelled)
                                <a href="{{ route('pesanan.bayar', $pesanan->id_pesanan) }}" class="w-full flex items-center justify-center gap-2 bg-[#3B82F6] hover:bg-blue-600 text-white font-semibold py-3.5 px-6 rounded-xl text-[13px] transition-all shadow-sm shadow-blue-500/20 active:scale-[0.99]">
                                    <span>{{ $statusBayarKey === 'dp_terbayar' ? 'Lanjutkan Pelunasan' : 'Bayar Sekarang' }}</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                                </a>
                            @endif

                            @if($jenisPesanan !== 'Dine In')
                                <a href="{{ route('pesanan.invoice', $pesanan->id_pesanan) }}" target="_blank" class="w-full flex items-center justify-center gap-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-semibold py-3.5 px-6 rounded-xl text-[13px] transition-all border border-emerald-100">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 16l5 5 5-5M12 3v12"/></svg>
                                    <span>Unduh Bukti Pesanan</span>
                                </a>
                            @endif

                            <a href="{{ route('lacak.index') }}" class="w-full flex items-center justify-center gap-2 bg-gray-50 hover:bg-gray-100 text-gray-500 hover:text-gray-700 font-semibold py-3.5 px-6 rounded-xl text-[13px] transition-all border border-gray-100">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                <span>Cari Pesanan Lain</span>
                            </a>
                        </div>
                    </div>

                    {{-- Right Column: Timeline --}}
                    @if($jenisPesanan !== 'Dine In' && !$isCancelled)
                    <div class="lg:col-span-6 lg:border-l lg:border-gray-100 lg:pl-10 h-full">
                        <h3 class="font-['Anonymous_Pro'] text-xs font-bold text-gray-400 uppercase tracking-widest mb-8">Progres Pesanan</h3>

                        {{-- Vertical stepper: works identically on mobile and desktop, no overflow-scroll needed --}}
                        <ol class="relative">
                            @foreach($timeline as $i => $step)
                            @php
                                $isDone = $currentStatusIndex !== false && $i < $currentStatusIndex;
                                $isCurrent = $statusKey === $step['key'];
                                $isLast = $i === count($timeline) - 1;
                            @endphp
                            <li class="relative pl-11 {{ $isLast ? 'pb-0' : 'pb-8' }}">
                                {{-- Connecting line --}}
                                @if(!$isLast)
                                <span class="absolute left-[17px] top-9 bottom-0 w-0.5 {{ $isDone || $isCurrent ? 'bg-emerald-400' : 'bg-gray-200' }}"></span>
                                @endif

                                {{-- Status Dot --}}
                                <span class="absolute left-0 top-0 z-10 w-9 h-9 rounded-full flex items-center justify-center transition-all
                                    {{ $isCurrent ? 'bg-[#3B82F6] text-white ring-4 ring-blue-100 shadow-md shadow-blue-500/30' : ($isDone ? 'bg-emerald-500 text-white' : 'bg-gray-100 text-gray-300 border-2 border-gray-200') }}">
                                    @if($isDone)
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    @elseif($isCurrent)
                                        <span class="w-2.5 h-2.5 rounded-full bg-white animate-pulse"></span>
                                    @else
                                        <span class="w-2.5 h-2.5 rounded-full bg-gray-300"></span>
                                    @endif
                                </span>

                                {{-- Label --}}
                                <div>
                                    <p class="font-medium text-sm leading-tight {{ $isCurrent ? 'text-blue-600 font-bold' : ($isDone ? 'text-gray-900' : 'text-gray-400') }}">
                                        {{ $step['label'] }}
                                        @if($isCurrent)
                                            <span class="inline-flex items-center gap-1 ml-2 text-[10px] font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full border border-blue-100 uppercase tracking-wider align-middle">
                                                Saat Ini
                                            </span>
                                        @endif
                                    </p>
                                    <p class="text-xs text-gray-400 font-light mt-1">{{ $step['desc'] }}</p>
                                </div>
                            </li>
                            @endforeach
                        </ol>
                    </div>
                    @endif

                </div>
            </div>
            @endif

        </div>
    </section>
</x-layouts.landing>