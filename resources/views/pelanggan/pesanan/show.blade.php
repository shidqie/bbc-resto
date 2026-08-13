<x-layouts.landing>
    <x-slot:title>Detail Pesanan — Saung Babakan Cinta</x-slot:title>

    <section class="py-16 bg-canvas min-h-screen">
        <div class="max-w-5xl mx-auto px-4 lg:px-8">

            {{-- Header --}}
            <div class="mb-10 flex items-center gap-4">
                <a href="{{ route('konsumen.pesanan.index') }}" class="w-10 h-10 bg-white border border-gray-200 rounded-full flex items-center justify-center text-gray-500 hover:text-gray-900 hover:bg-gray-50 transition-colors shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Rincian Pesanan</h1>
                    <p class="text-gray-500 text-sm mt-1">Detail status terkini dari pesanan Anda</p>
                </div>
            </div>

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
                <div class="text-center pb-8 border-b border-gray-200/60 mb-10">
                    <span class="inline-block px-3.5 py-1 bg-amber-50 text-amber-800 font-['Anonymous_Pro'] text-xs font-bold uppercase tracking-widest rounded-full mb-3 border border-amber-200/50">
                        {{ $jenisPesanan }}
                    </span>

                    <div class="flex items-center justify-center gap-2 mb-4" x-data="{ copied: false }">
                        <h2 class="text-2xl sm:text-4xl font-serif text-gray-900 tracking-wide font-normal break-all">{{ $pesanan->id_pesanan }}</h2>
                        <button
                            type="button"
                            @click="navigator.clipboard.writeText('{{ $pesanan->id_pesanan }}'); copied = true; setTimeout(() => copied = false, 1500)"
                            class="text-gray-300 hover:text-gray-500 transition-colors shrink-0"
                            aria-label="Salin nomor pesanan"
                        >
                            <svg x-show="!copied" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            <svg x-show="copied" x-cloak class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
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

                <div class="flex justify-center items-start w-full">

                    {{-- Center Column: Info & Billing --}}
                    <div class="w-full max-w-2xl space-y-8">
                        <div>
                            <h3 class="font-['Anonymous_Pro'] text-xs font-bold text-gray-400 uppercase tracking-widest mb-6">Informasi Pesanan</h3>

                            <dl class="space-y-4 text-sm text-gray-600">
                                <div class="flex justify-between items-center py-2.5 border-b border-gray-100/60">
                                    <dt class="text-gray-400 font-light">Atas Nama</dt>
                                    <dd class="font-medium text-gray-800 text-base">{{ optional($pesanan->pelanggan)->nama ?? $pesanan->jadwal_pesanan->nama_penerima ?? 'Tamu' }}</dd>
                                </div>
                                @if($pesanan->jadwal_pesanan)
                                <div class="flex justify-between items-center py-2.5 border-b border-gray-100/60">
                                    <dt class="text-gray-400 font-light">Kontak</dt>
                                    @php
                                        $lacakKontak = $pesanan->jadwal_pesanan->kontak ?? $pesanan->jadwal_pesanan->nomor_telepon_penerima ?? '';
                                    @endphp
                                    <dd class="font-medium text-gray-800">{{ $lacakKontak ? \App\Support\WhatsAppNumber::formatForDisplay($lacakKontak) : '-' }}</dd>
                                </div>
                                <div class="flex justify-between items-center py-2.5 border-b border-gray-100/60">
                                    <dt class="text-gray-400 font-light">Tanggal Acara</dt>
                                    <dd class="font-medium text-gray-800">{{ \Carbon\Carbon::parse($pesanan->jadwal_pesanan->tanggal_acara)->translatedFormat('d M Y') }}</dd>
                                </div>
                                @if($pesanan->jadwal_pesanan->alamat_pengiriman)
                                <div class="flex justify-between items-start py-2.5 border-b border-gray-100/60">
                                    <dt class="text-gray-400 font-light shrink-0">Alamat / Venue Acara</dt>
                                    <dd class="font-medium text-gray-800 text-right max-w-[65%] leading-relaxed">{{ $pesanan->jadwal_pesanan->alamat_pengiriman }}</dd>
                                </div>
                                @endif
                                @endif
                                <div class="flex justify-between items-center py-2.5 border-b border-gray-100/60">
                                    <dt class="text-gray-400 font-light">Paket</dt>
                                    <dd class="font-medium text-gray-800 text-right">{{ $pesanan->detail_pesanan->first()->menu->nama_menu ?? '-' }} &times; {{ $pesanan->detail_pesanan->first()->jumlah ?? '-' }} Porsi</dd>
                                </div>
                                @if($pesanan->detail_pesanan->count() > 1)
                                <div class="flex justify-end -mt-2 pb-2.5 border-b border-gray-100/60">
                                    <span class="text-xs text-gray-400">+ {{ $pesanan->detail_pesanan->count() - 1 }} item lainnya</span>
                                </div>
                                @endif
                                @if($pesanan->pengiriman)
                                <div class="flex justify-between items-center py-2.5 border-b border-gray-100/60">
                                    <dt class="text-gray-400 font-light">Metode Pengiriman</dt>
                                    <dd class="font-medium text-gray-800 capitalize">{{ $pesanan->pengiriman->metode_pengiriman ?? 'Delivery' }}</dd>
                                </div>
                                @endif
                                @if($pesanan->pembayaran->isNotEmpty())
                                <div class="flex justify-between items-center py-2.5 border-b border-gray-100/60">
                                    <dt class="text-gray-400 font-light">Metode Pembayaran</dt>
                                    <dd class="font-medium text-gray-800">{{ $pesanan->pembayaran->first()->metode_pembayaran ?? '-' }}</dd>
                                </div>
                                @endif
                            </dl>
                        </div>

                        {{-- Total & Payment Box --}}
                        <div class="bg-gray-50/70 border border-gray-100 rounded-xl p-5 space-y-3">
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-500">Total Tagihan</span>
                                <span class="font-semibold text-gray-900 text-lg">Rp {{ number_format($total, 0, ',', '.') }}</span>
                            </div>

                            @if($statusBayarKey === 'lunas')
                            <div class="flex justify-between items-center text-sm text-emerald-600 pt-3 border-t border-gray-200/60 font-bold">
                                <span>Telah Dibayar (LUNAS)</span>
                                <span class="text-xl">Rp {{ number_format($total, 0, ',', '.') }}</span>
                            </div>
                            @elseif($dpTerbayar > 0)
                            <div class="flex justify-between items-center text-sm text-emerald-600">
                                <span>DP Terbayar</span>
                                <span class="font-medium">- Rp {{ number_format($dpTerbayar, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center pt-3 border-t border-gray-200/60 text-rose-600 font-bold">
                                <span>Sisa Pelunasan</span>
                                <span class="text-xl">Rp {{ number_format($total - $dpTerbayar, 0, ',', '.') }}</span>
                            </div>
                            @else
                            <div class="flex justify-between items-center pt-3 border-t border-gray-200/60 text-amber-600 font-bold">
                                <span>Belum Ada Pembayaran</span>
                                <span class="text-xl">Rp {{ number_format($total, 0, ',', '.') }}</span>
                            </div>
                            @endif
                        </div>

                        {{-- Action Buttons --}}
                        <div class="space-y-3 pt-2">
                            @if(in_array($statusBayarKey, ['belum_bayar', 'dp_terbayar']) && !$isCancelled)
                                <a href="{{ route('pesanan.bayar', $pesanan->id_pesanan) }}" class="w-full flex items-center justify-center gap-2 bg-[#3B82F6] hover:bg-blue-600 text-white font-bold py-4 px-6 rounded-xl text-xs tracking-widest uppercase transition-all shadow-md shadow-blue-500/20 active:scale-[0.99]">
                                    <span>{{ $statusBayarKey === 'dp_terbayar' ? 'Lanjutkan Pelunasan' : 'Bayar Sekarang' }}</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                                </a>
                            @endif



                        </div>
                    </div>



                </div>
            </div>

        </div>
    </section>
</x-layouts.landing>
