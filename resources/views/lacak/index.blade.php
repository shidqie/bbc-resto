<x-layouts.landing>
    <x-slot:title>Lacak Pesanan — Saung Babakan Cinta</x-slot:title>

    <section class="py-16 bg-canvas min-h-screen">
        <div class="max-w-5xl mx-auto px-4 lg:px-8">

            {{-- Header --}}
            <div class="text-center mb-10">
                <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Lacak Pesanan</h1>
                <p class="text-gray-500 text-sm">Masukkan nomor pesanan Anda untuk melihat status terkini</p>
            </div>

            {{-- Search Form --}}
            <form method="GET" action="{{ route('lacak.index') }}" class="mb-8">
                <div class="flex gap-3">
                    <input
                        type="text"
                        name="kode_pesanan"
                        value="{{ $kodePesanan ?? '' }}"
                        placeholder="Contoh: CAT-20240728-XXXX"
                        class="flex-1 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none shadow-sm"
                        required
                    >
                    <button type="submit" class="bg-[#0D3024] hover:bg-[#0a1f17] text-white font-bold px-6 py-3 rounded-xl text-sm transition-all shadow-sm flex items-center gap-2">
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
                    <p class="text-red-600 text-sm">Pesanan dengan nomor <strong>{{ $kodePesanan }}</strong> tidak ditemukan. Pastikan kode yang dimasukkan sudah benar.</p>
                </div>
            @endif

            {{-- Result --}}
            @if($pesanan)
            @php
                $statusMap = [
                    1 => ['key' => 'ditinjau', 'label' => 'Pesanan Diterima', 'desc' => 'Pesanan telah masuk ke sistem', 'color' => 'bg-amber-50 text-amber-700 border-amber-200/60'],
                    2 => ['key' => 'terkonfirmasi', 'label' => 'Dikonfirmasi Admin', 'desc' => 'Pembayaran/Pesanan telah dikonfirmasi', 'color' => 'bg-blue-50 text-blue-700 border-blue-200/60'],
                    3 => ['key' => 'diproses', 'label' => 'Sedang Diproses', 'desc' => 'Dapur sedang menyiapkan hidangan', 'color' => 'bg-indigo-50 text-indigo-700 border-indigo-200/60'],
                    4 => ['key' => 'menunggu_pengiriman', 'label' => 'Menunggu Pengiriman', 'desc' => 'Makanan siap untuk dikirim/disajikan', 'color' => 'bg-purple-50 text-purple-700 border-purple-200/60'],
                    5 => ['key' => 'selesai', 'label' => 'Pesanan Selesai', 'desc' => 'Pesanan berhasil diterima & selesai', 'color' => 'bg-emerald-50 text-emerald-700 border-emerald-200/60'],
                    6 => ['key' => 'dibatalkan', 'label' => 'Dibatalkan', 'desc' => 'Pesanan dibatalkan', 'color' => 'bg-rose-50 text-rose-700 border-rose-200/60'],
                ];
                $statusInfo = $statusMap[$pesanan->status_pesanan_id] ?? $statusMap[1];
                $statusKey = $statusInfo['key'];

                $total = (float) $pesanan->total_tagihan;
                $lunas = (float) $pesanan->pembayaran->where('status_verifikasi', 'diterima')->sum('jumlah_dibayar');
                $dpTerbayar = (float) $pesanan->pembayaran->where('status_verifikasi', 'diterima')->sum('jumlah_dibayar');
                $statusBayarKey = $lunas >= $total ? 'lunas' : ($dpTerbayar > 0 ? 'dp_terbayar' : 'belum_bayar');
                $statusBayarInfo = [
                    'belum_bayar' => ['label' => 'Belum Bayar', 'color' => 'bg-amber-50 text-amber-700 border-amber-200/60'],
                    'dp_terbayar' => ['label' => 'DP Terbayar', 'color' => 'bg-indigo-50 text-indigo-700 border-indigo-200/60'],
                    'lunas'       => ['label' => 'Lunas', 'color' => 'bg-emerald-50 text-emerald-700 border-emerald-200/60'],
                ][$statusBayarKey];

                $timeline = [
                    ['key' => 'ditinjau', 'label' => 'Pesanan Diterima', 'desc' => 'Pesanan telah masuk ke sistem'],
                    ['key' => 'terkonfirmasi', 'label' => 'Dikonfirmasi Admin', 'desc' => 'Pembayaran/Pesanan telah dikonfirmasi'],
                    ['key' => 'diproses', 'label' => 'Sedang Diproses', 'desc' => 'Dapur sedang menyiapkan hidangan'],
                    ['key' => 'menunggu_pengiriman', 'label' => 'Menunggu Pengiriman', 'desc' => 'Makanan siap untuk dikirim/disajikan'],
                    ['key' => 'selesai', 'label' => 'Pesanan Selesai', 'desc' => 'Pesanan berhasil diterima & selesai'],
                ];

                $statusOrder = array_column($timeline, 'key');
                $currentStatusIndex = array_search($statusKey, $statusOrder);
                if ($currentStatusIndex === false && $statusKey === 'dibatalkan') {
                    $currentStatusIndex = count($statusOrder);
                }
            @endphp

            <div class="mb-12">
                
                {{-- Header Status --}}
                <div class="text-center pb-8 border-b border-gray-200/60 mb-10">
                    <span class="inline-block px-3.5 py-1 bg-amber-50 text-amber-800 font-['Anonymous_Pro'] text-xs font-bold uppercase tracking-widest rounded-full mb-3 border border-amber-200/50">
                        {{ $jenisPesanan }}
                    </span>
                    <h2 class="text-3xl sm:text-4xl font-serif text-gray-900 tracking-wide mb-4 font-normal">{{ $pesanan->nomor_pesanan }}</h2>
                    <div class="flex flex-wrap justify-center items-center gap-2">
                        <span class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-widest border {{ $statusInfo['color'] }}">
                            <span class="w-1.5 h-1.5 rounded-full bg-current mr-2"></span>
                            {{ $statusInfo['label'] }}
                        </span>
                        <span class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-widest border {{ $statusBayarInfo['color'] }}">
                            <span class="w-1.5 h-1.5 rounded-full bg-current mr-2"></span>
                            {{ $statusBayarInfo['label'] }}
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 items-start">
                    
                    {{-- Left Column: Info & Billing --}}
                    <div class="lg:col-span-6 space-y-8">
                        <div>
                            <h3 class="font-['Anonymous_Pro'] text-xs font-bold text-gray-400 uppercase tracking-widest mb-6">Informasi Pesanan</h3>
                            
                            <div class="space-y-4 text-sm text-gray-600">
                                <div class="flex justify-between items-center py-2.5 border-b border-gray-100/60">
                                    <span class="text-gray-400 font-light">Atas Nama</span>
                                    <span class="font-medium text-gray-800 text-base">{{ optional($pesanan->pelanggan)->nama ?? $pesanan->jadwal_pesanan->nama_penerima ?? 'Tamu' }}</span>
                                </div>
                                @if($pesanan->jadwal_pesanan)
                                <div class="flex justify-between items-center py-2.5 border-b border-gray-100/60">
                                    <span class="text-gray-400 font-light">Kontak</span>
                                    <span class="font-medium text-gray-800">{{ $pesanan->jadwal_pesanan->kontak ?? $pesanan->jadwal_pesanan->nomor_telepon_penerima ?? '-' }}</span>
                                </div>
                                <div class="flex justify-between items-center py-2.5 border-b border-gray-100/60">
                                    <span class="text-gray-400 font-light">Tanggal Acara</span>
                                    <span class="font-medium text-gray-800">{{ \Carbon\Carbon::parse($pesanan->jadwal_pesanan->tanggal_acara)->format('d M Y') }}</span>
                                </div>
                                @if($pesanan->jadwal_pesanan->alamat_pengantaran)
                                <div class="flex justify-between items-start py-2.5 border-b border-gray-100/60">
                                    <span class="text-gray-400 font-light shrink-0">Alamat / Venue Acara</span>
                                    <span class="font-medium text-gray-800 text-right max-w-[65%] leading-relaxed">{{ $pesanan->jadwal_pesanan->alamat_pengantaran }}</span>
                                </div>
                                @endif
                                @endif
                                <div class="flex justify-between items-center py-2.5 border-b border-gray-100/60">
                                    <span class="text-gray-400 font-light">Paket</span>
                                    <span class="font-medium text-gray-800">{{ $pesanan->detail_pesanan->first()->menu->nama_menu ?? '-' }} &times; {{ $pesanan->detail_pesanan->first()->jumlah ?? '-' }} Porsi</span>
                                </div>
                                @if($pesanan->pengantaran)
                                <div class="flex justify-between items-center py-2.5 border-b border-gray-100/60">
                                    <span class="text-gray-400 font-light">Metode Pengiriman</span>
                                    <span class="font-medium text-gray-800 capitalize">Delivery</span>
                                </div>
                                @endif
                                @if($pesanan->pembayaran->isNotEmpty())
                                <div class="flex justify-between items-center py-2.5 border-b border-gray-100/60">
                                    <span class="text-gray-400 font-light">Metode Pembayaran</span>
                                    <span class="font-medium text-gray-800">
                                        {{ $pesanan->pembayaran->first()->metode_pembayaran ?? '-' }}
                                    </span>
                                </div>
                                @endif
                            </div>
                        </div>

                        {{-- Total & Payment Box --}}
                        <div class="bg-gray-50/70 border border-gray-100 rounded-xl p-5 space-y-3">
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-500">Total Tagihan</span>
                                <span class="font-semibold text-gray-900 text-lg">Rp {{ number_format($pesanan->total_tagihan, 0, ',', '.') }}</span>
                            </div>
                            
                            @if($statusBayarKey === 'lunas')
                            <div class="flex justify-between items-center text-sm text-emerald-600 pt-3 border-t border-gray-200/60 font-bold">
                                <span>Telah Dibayar (LUNAS)</span>
                                <span class="text-xl">Rp {{ number_format($pesanan->total_tagihan, 0, ',', '.') }}</span>
                            </div>
                            @elseif($dpTerbayar > 0)
                            <div class="flex justify-between items-center text-sm text-emerald-600">
                                <span>DP Terbayar</span>
                                <span class="font-medium">- Rp {{ number_format($dpTerbayar, 0, ',', '.') }}</span>
                            </div>
                            @if($dpTerbayar < $total)
                            <div class="flex justify-between items-center pt-3 border-t border-gray-200/60 text-rose-600 font-bold">
                                <span>Sisa Pelunasan</span>
                                <span class="text-xl">Rp {{ number_format($total - $dpTerbayar, 0, ',', '.') }}</span>
                            </div>
                            @endif
                            @endif
                        </div>

                        {{-- Action Buttons --}}
                        <div class="space-y-3 pt-2">
                            @if(in_array($statusBayarKey, ['belum_bayar', 'dp_terbayar']) && $pesanan->status_pesanan_id != 6)
                                <a href="{{ route('pesanan.bayar', $pesanan->nomor_pesanan) }}" class="w-full flex items-center justify-center gap-2 bg-[#3B82F6] hover:bg-blue-600 text-white font-bold py-4 px-6 rounded-xl text-xs tracking-widest uppercase transition-all shadow-md shadow-blue-500/20 active:scale-[0.99]">
                                    <span>{{ $statusBayarKey === 'dp_terbayar' ? 'Lanjutkan Pelunasan' : 'Bayar Sekarang' }}</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                                </a>
                            @endif
                            
                            @if($jenisPesanan !== 'Dine In / Takeaway')
                                <a href="{{ route('pesanan.invoice', $pesanan->nomor_pesanan) }}" target="_blank" class="w-full flex items-center justify-center gap-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200/60 font-bold py-3.5 px-6 rounded-xl text-xs tracking-widest uppercase transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 16l5 5 5-5M12 3v12"/></svg>
                                    <span>Unduh Bukti Pesanan</span>
                                </a>
                            @endif

                            <a href="{{ route('lacak.index') }}" class="w-full flex items-center justify-center gap-2 bg-gray-50 hover:bg-gray-100 text-gray-500 hover:text-gray-700 font-bold py-3.5 px-6 rounded-xl text-xs tracking-widest uppercase transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                <span>Cari Pesanan Lain</span>
                            </a>
                        </div>
                    </div>

                    {{-- Right Column: Timeline --}}
                    @if($jenisPesanan !== 'Dine In / Takeaway' && $pesanan->status_pesanan_id != 6)
                    <div class="lg:col-span-6 lg:border-l lg:border-gray-100 lg:pl-10 h-full">
                        <h3 class="font-['Anonymous_Pro'] text-xs font-bold text-gray-400 uppercase tracking-widest mb-8">Progres Pesanan</h3>
                        
                        <div class="overflow-x-auto pb-4">
                            <div class="flex items-start gap-4 min-w-max">
                                @foreach($timeline as $i => $step)
                                @php
                                    $isDone = $currentStatusIndex !== false && $i <= $currentStatusIndex;
                                    $isCurrent = $statusKey === $step['key'];
                                    $isLast = $i === count($timeline) - 1;
                                @endphp
                                <div class="flex flex-col items-center relative shrink-0" style="min-width: 160px;">
                                    {{-- Connecting Line (except first) --}}
                                    @if($i > 0)
                                    <div class="absolute left-[-80px] top-[18px] w-16 h-0.5 {{ $isDone ? 'bg-emerald-500' : 'bg-gray-200' }} z-0" style="width: calc(50% - 40px);"></div>
                                    @endif
                                    
                                    {{-- Status Dot --}}
                                    <div class="relative z-10 w-9 h-9 rounded-full flex items-center justify-center transition-all
                                        {{ $isCurrent ? 'bg-[#3B82F6] text-white ring-4 ring-blue-100 shadow-md shadow-blue-500/30' : ($isDone ? 'bg-emerald-500 text-white' : 'bg-gray-100 text-gray-300 border-2 border-gray-200') }}">
                                        @if($isDone || $isCurrent)
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                        @else
                                            <span class="w-2.5 h-2.5 rounded-full bg-gray-300"></span>
                                        @endif
                                    </div>
                                    
                                    {{-- Label --}}
                                    <div class="mt-3 text-center w-36">
                                        <p class="font-medium text-sm leading-tight {{ $isCurrent ? 'text-blue-600 font-bold' : ($isDone ? 'text-gray-900' : 'text-gray-400') }}">
                                            {{ $step['label'] }}
                                        </p>
                                        <p class="text-xs text-gray-400 font-light mt-1">
                                            {{ $step['desc'] }}
                                        </p>
                                        @if($isCurrent)
                                            <span class="inline-flex items-center gap-1 mt-2 text-xs font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full border border-blue-100 uppercase tracking-wider">
                                                <span class="w-1 h-1 rounded-full bg-blue-600 animate-pulse"></span>
                                                Saat Ini
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                </div>
            </div>
            @endif

        </div>
    </section>
</x-layouts.landing>
