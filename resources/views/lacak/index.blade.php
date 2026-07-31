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
                <p class="text-gray-500 text-sm">Masukkan nomor pesanan atau nomor handphone Anda untuk melihat status terkini</p>
            </div>

            {{-- Search Form --}}
            <form method="GET" action="{{ route('lacak.index') }}" class="mb-8">
                <div class="flex gap-3">
                    <input
                        type="text"
                        name="kode_pesanan"
                        value="{{ $kodePesanan ?? '' }}"
                        placeholder="Contoh: CAT-20240728-XXXX atau 08123456789"
                        class="flex-1 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none shadow-sm"
                        required
                    >
                    <button type="submit" class="bg-[#0F2E23] hover:bg-[#0a1f17] text-white font-bold px-6 py-3 rounded-xl text-sm transition-all shadow-sm flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Lacak
                    </button>
                </div>
            </form>

            {{-- Not found --}}
            @if($kodePesanan && !$pesanan)
                <div class="bg-red-50 border border-red-200 rounded-2xl p-6 text-center">
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-red-800 mb-1">Pesanan Tidak Ditemukan</h3>
                    <p class="text-red-600 text-sm">Pesanan dengan nomor <strong>{{ $kodePesanan }}</strong> tidak ditemukan. Pastikan kode atau nomor handphone yang dimasukkan sudah benar.</p>
                </div>
            @endif

            {{-- Result --}}
            @if($pesanan)
            @php
                $statusColors = [
                    'ditinjau'           => 'bg-amber-50 text-amber-700 border-amber-200/60',
                    'terkonfirmasi'      => 'bg-blue-50 text-blue-700 border-blue-200/60',
                    'dikonfirmasi'       => 'bg-blue-50 text-blue-700 border-blue-200/60',
                    'diproses'           => 'bg-indigo-50 text-indigo-700 border-indigo-200/60',
                    'menunggu_pengiriman'=> 'bg-purple-50 text-purple-700 border-purple-200/60',
                    'dikirim'            => 'bg-cyan-50 text-cyan-700 border-cyan-200/60',
                    'selesai'            => 'bg-emerald-50 text-emerald-700 border-emerald-200/60',
                    'dibatalkan'         => 'bg-rose-50 text-rose-700 border-rose-200/60',
                    'aktif'              => 'bg-blue-50 text-blue-700 border-blue-200/60',
                ];
                $statusBayarColors = [
                    'belum_bayar'  => 'bg-amber-50 text-amber-700 border-amber-200/60',
                    'dp_terbayar'  => 'bg-indigo-50 text-indigo-700 border-indigo-200/60',
                    'lunas'        => 'bg-emerald-50 text-emerald-700 border-emerald-200/60',
                    'paid'         => 'bg-emerald-50 text-emerald-700 border-emerald-200/60',
                ];
                $statusColor = $statusColors[$pesanan->status] ?? 'bg-gray-50 text-gray-700 border-gray-200/60';
                $statusBayarColor = $statusBayarColors[$pesanan->status_bayar ?? 'belum_bayar'] ?? 'bg-gray-50 text-gray-700 border-gray-200/60';

                $timeline = [
                    ['key' => 'ditinjau',            'label' => 'Pesanan Diterima',      'desc' => 'Pesanan telah masuk ke sistem'],
                    ['key' => 'terkonfirmasi',       'label' => 'Dikonfirmasi Admin',    'desc' => 'Pembayaran/Pesanan telah dikonfirmasi'],
                    ['key' => 'diproses',             'label' => 'Sedang Diproses',       'desc' => 'Dapur sedang menyiapkan hidangan'],
                    ['key' => 'menunggu_pengiriman',  'label' => 'Menunggu Pengiriman',   'desc' => 'Makanan siap untuk dikirim/disajikan'],
                    ['key' => 'dikirim',              'label' => 'Dalam Perjalanan',      'desc' => 'Driver menuju lokasi pengiriman'],
                    ['key' => 'selesai',              'label' => 'Pesanan Selesai',       'desc' => 'Pesanan berhasil diterima & selesai'],
                ];

                $statusOrder = array_column($timeline, 'key');
                $mappedStatus = in_array($pesanan->status, ['dikonfirmasi', 'lunas', 'paid']) ? 'terkonfirmasi' : $pesanan->status;
                $currentStatusIndex = array_search($mappedStatus, $statusOrder);
                if ($currentStatusIndex === false && in_array($pesanan->status_bayar ?? '', ['lunas', 'paid', 'dp_terbayar'])) {
                    $currentStatusIndex = 1; // Default to 'terkonfirmasi'
                }
            @endphp

            <div class="mb-12">
                
                {{-- Header Status --}}
                <div class="text-center pb-8 border-b border-gray-200/60 mb-10">
                    <span class="inline-block px-3.5 py-1 bg-amber-50 text-amber-800 font-['Anonymous_Pro'] text-xs font-bold uppercase tracking-widest rounded-full mb-3 border border-amber-200/50">
                        {{ $jenisPesanan }}
                    </span>
                    <h2 class="text-3xl sm:text-4xl font-serif text-gray-900 tracking-wide mb-4 font-normal">{{ $pesanan->kode_pesanan }}</h2>
                    <div class="flex flex-wrap justify-center items-center gap-2">
                        <span class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-widest border {{ $statusColor }}">
                            <span class="w-1.5 h-1.5 rounded-full bg-current mr-2"></span>
                            {{ ucwords(str_replace('_', ' ', $pesanan->status)) }}
                        </span>
                        @if(isset($pesanan->status_bayar))
                            <span class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-widest border {{ $statusBayarColor }}">
                                <span class="w-1.5 h-1.5 rounded-full bg-current mr-2"></span>
                                {{ ucwords(str_replace('_', ' ', $pesanan->status_bayar)) }}
                            </span>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 items-start">
                    
                    {{-- Left Column: Info & Billing --}}
                    <div class="lg:col-span-6 space-y-8">
                        <div>
                            <h3 class="font-['Anonymous_Pro'] text-xs font-bold text-gray-400 uppercase tracking-widest mb-6">Informasi Booking</h3>
                            
                            <div class="space-y-4 text-sm text-gray-600">
                                <div class="flex justify-between items-center py-2.5 border-b border-gray-100/60">
                                    <span class="text-gray-400 font-light">Atas Nama</span>
                                    <span class="font-medium text-gray-800 text-base">{{ $pesanan->nama_pemesan }}</span>
                                </div>
                                <div class="flex justify-between items-center py-2.5 border-b border-gray-100/60">
                                    <span class="text-gray-400 font-light">Kontak</span>
                                    <span class="font-medium text-gray-800">{{ $pesanan->kontak ?? $pesanan->no_hp ?? '-' }}</span>
                                </div>
                                @if(isset($pesanan->tanggal_acara))
                                <div class="flex justify-between items-center py-2.5 border-b border-gray-100/60">
                                    <span class="text-gray-400 font-light">Tanggal Acara</span>
                                    <span class="font-medium text-gray-800">{{ \Carbon\Carbon::parse($pesanan->tanggal_acara)->format('d M Y') }}</span>
                                </div>
                                @if(in_array($pesanan->status_bayar ?? '', ['lunas', 'paid']))
                                <div class="flex justify-between items-center py-2.5 border-b border-gray-100/60">
                                    <span class="text-gray-400 font-light">Tanggal Pelunasan</span>
                                    <span class="font-medium text-emerald-600">{{ \Carbon\Carbon::parse($pesanan->diperbarui_pada)->format('d M Y, H:i') }} WIB</span>
                                </div>
                                @elseif(($pesanan->status_bayar ?? '') === 'dp_terbayar')
                                <div class="flex justify-between items-center py-2.5 border-b border-gray-100/60">
                                    <span class="text-gray-400 font-light">Batas Pelunasan</span>
                                    <span class="font-medium text-amber-600">{{ \Carbon\Carbon::parse($pesanan->tanggal_acara)->subDays(2)->format('d M Y') }} (H-2 Acara)</span>
                                </div>
                                @endif
                                @endif
                                @if(isset($pesanan->lokasi_acara) || isset($pesanan->alamat_lengkap))
                                <div class="flex justify-between items-start py-2.5 border-b border-gray-100/60">
                                    <span class="text-gray-400 font-light shrink-0">Alamat / Venue Acara</span>
                                    <span class="font-medium text-gray-800 text-right max-w-[65%] leading-relaxed">{{ $pesanan->lokasi_acara ?? $pesanan->alamat_lengkap }}</span>
                                </div>
                                @endif
                                @if(isset($pesanan->metode_pengiriman))
                                <div class="flex justify-between items-center py-2.5 border-b border-gray-100/60">
                                    <span class="text-gray-400 font-light">Metode Pengiriman</span>
                                    <span class="font-medium text-gray-800 capitalize">{{ $pesanan->metode_pengiriman }}</span>
                                </div>
                                @endif
                                @if($pesanan->status === 'dibatalkan' && isset($pesanan->alasan_batal))
                                <div class="flex justify-between items-center py-2.5 border-b border-gray-100/60">
                                    <span class="text-rose-500 font-medium">Alasan Batal</span>
                                    <span class="font-medium text-rose-600 text-right max-w-[60%]">{{ $pesanan->alasan_batal }}</span>
                                </div>
                                @endif
                            </div>
                        </div>

                        {{-- Total & Payment Box --}}
                        <div class="bg-gray-50/70 border border-gray-100 rounded-2xl p-5 space-y-3">
                            @if(isset($pesanan->total_tagihan))
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-500">Total Tagihan</span>
                                <span class="font-semibold text-gray-900 text-lg">Rp {{ number_format($pesanan->total_tagihan, 0, ',', '.') }}</span>
                            </div>
                            @endif
                            
                            @if(isset($pesanan->status_bayar) && $pesanan->status_bayar === 'lunas')
                            <div class="flex justify-between items-center text-sm text-emerald-600 pt-3 border-t border-gray-200/60 font-bold">
                                <span>Telah Dibayar (LUNAS)</span>
                                <span class="text-xl">Rp {{ number_format($pesanan->total_tagihan, 0, ',', '.') }}</span>
                            </div>
                            @elseif(isset($pesanan->dp_amount) && $pesanan->dp_amount > 0)
                            <div class="flex justify-between items-center text-sm text-emerald-600">
                                <span>DP Terbayar</span>
                                <span class="font-medium">- Rp {{ number_format($pesanan->dp_amount, 0, ',', '.') }}</span>
                            </div>
                            @if($pesanan->dp_amount < $pesanan->total_tagihan)
                            <div class="flex justify-between items-center pt-3 border-t border-gray-200/60 text-rose-600 font-bold">
                                <span>Sisa Pelunasan</span>
                                <span class="text-xl">Rp {{ number_format($pesanan->total_tagihan - $pesanan->dp_amount, 0, ',', '.') }}</span>
                            </div>
                            @endif
                            @endif
                        </div>

                        {{-- Action Buttons --}}
                        <div class="space-y-3 pt-2">
                            @if(isset($pesanan->status_bayar) && in_array($pesanan->status_bayar, ['belum_bayar', 'dp_terbayar']) && isset($pesanan->kode_pesanan) && $pesanan->status !== 'dibatalkan')
                                <a href="{{ route('pos.pembayaran.index', $pesanan->kode_pesanan) }}" class="w-full flex items-center justify-center gap-2 bg-[#3B82F6] hover:bg-blue-600 text-white font-bold py-4 px-6 rounded-2xl text-xs tracking-widest uppercase transition-all shadow-md shadow-blue-500/20 active:scale-[0.99]">
                                    <span>{{ $pesanan->status_bayar === 'dp_terbayar' ? 'Lanjutkan Pelunasan' : 'Bayar Sekarang' }}</span>
                                    <i class="ph-bold ph-arrow-right text-base"></i>
                                </a>
                            @endif
                            
                            @if(isset($pesanan->kode_pesanan) && $jenisPesanan !== 'Dine In / Takeaway')
                                <a href="{{ route('pesanan.invoice', $pesanan->kode_pesanan) }}" target="_blank" class="w-full flex items-center justify-center gap-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200/60 font-bold py-3.5 px-6 rounded-2xl text-xs tracking-widest uppercase transition-all">
                                    <i class="ph-bold ph-download-simple text-base"></i>
                                    <span>Unduh Bukti Pesanan</span>
                                </a>
                            @endif

                            <a href="{{ route('lacak.index') }}" class="w-full flex items-center justify-center gap-2 bg-gray-50 hover:bg-gray-100 text-gray-500 hover:text-gray-700 font-bold py-3.5 px-6 rounded-2xl text-xs tracking-widest uppercase transition-all">
                                <i class="ph-bold ph-magnifying-glass text-base"></i>
                                <span>Cari Pesanan Lain</span>
                            </a>
                        </div>
                    </div>

                    {{-- Right Column: Timeline --}}
                    @if($jenisPesanan !== 'Dine In / Takeaway' && $pesanan->status !== 'dibatalkan')
                    <div class="lg:col-span-6 lg:border-l lg:border-gray-100 lg:pl-10 h-full">
                        <h3 class="font-['Anonymous_Pro'] text-xs font-bold text-gray-400 uppercase tracking-widest mb-8">Progres Pesanan</h3>
                        
                        <div class="relative pl-6">
                            {{-- Vertical Line --}}
                            <div class="absolute left-3.5 top-3 bottom-3 w-0.5 bg-gray-100"></div>

                            <div class="space-y-8">
                                @foreach($timeline as $i => $step)
                                @php
                                    $isDone = $currentStatusIndex !== false && $i <= $currentStatusIndex;
                                    $isCurrent = $pesanan->status === $step['key'];
                                @endphp
                                <div class="flex items-start gap-4 relative group">
                                    {{-- Status Dot --}}
                                    <div class="w-7 h-7 -ml-9 rounded-full flex items-center justify-center shrink-0 z-10 transition-all
                                        {{ $isCurrent ? 'bg-[#3B82F6] text-white ring-4 ring-blue-100 shadow-md shadow-blue-500/30' : ($isDone ? 'bg-emerald-500 text-white' : 'bg-gray-100 text-gray-300 border border-gray-200') }}">
                                        @if($isDone || $isCurrent)
                                            <i class="ph-bold ph-check text-xs"></i>
                                        @else
                                            <span class="w-2 h-2 rounded-full bg-gray-300"></span>
                                        @endif
                                    </div>

                                    {{-- Content --}}
                                    <div class="pt-0.5">
                                        <p class="font-medium text-sm leading-none {{ $isCurrent ? 'text-blue-600 font-bold' : ($isDone ? 'text-gray-900' : 'text-gray-400') }}">
                                            {{ $step['label'] }}
                                        </p>
                                        <p class="text-xs text-gray-400 font-light mt-1.5">
                                            {{ $step['desc'] }}
                                        </p>
                                        @if($isCurrent)
                                            <span class="inline-flex items-center gap-1.5 mt-2 text-[10px] font-bold text-blue-600 bg-blue-50 px-2.5 py-1 rounded-full border border-blue-100 uppercase tracking-wider">
                                                <span class="w-1.5 h-1.5 rounded-full bg-blue-600 animate-pulse"></span>
                                                Tahap Saat Ini
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
