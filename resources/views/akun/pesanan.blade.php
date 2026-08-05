<x-layouts.landing>
    <x-slot:title>Akun Saya — Saung Babakan Cinta</x-slot:title>

    <section class="py-12 bg-canvas min-h-screen">
        <div class="max-w-5xl mx-auto px-4 lg:px-8">

            {{-- Header --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-primary">Halo, {{ $pelanggan->nama }}</h1>
                    <p class="text-sm text-body/70 mt-1">Pantau status pesanan catering & nasi box Anda di sini.</p>
                </div>
            <div class="flex items-center gap-2 self-start">
                <a href="{{ route('konsumen.profile') }}" class="px-5 py-2.5 rounded-xl border-2 border-primary text-primary font-semibold text-sm hover:bg-primary hover:text-white transition-all duration-200">
                    Profil
                </a>
                <a href="{{ route('home') }}#catering" class="px-5 py-2.5 rounded-xl bg-primary text-white font-semibold text-sm shadow hover:scale-105 hover:bg-primary-container transition">
                    + Pesan Baru
                </a>
            </div>
            </div>

            @php
                $berjalan = $pesanans->filter(fn ($o) => in_array($o->status_pesanan_id, [1, 2, 3, 4]));
                $totalBelanja = (float) $pesanans->sum('total_tagihan');
            @endphp

            {{-- Statistik --}}
            <div class="grid grid-cols-3 gap-4 mb-8">
                <div class="bg-white rounded-xl border border-primary/10 p-5">
                    <p class="text-xs font-semibold text-body/50 uppercase tracking-wide">Total Pesanan</p>
                    <p class="text-2xl font-bold text-primary mt-1">{{ $pesanans->count() }}</p>
                </div>
                <div class="bg-white rounded-xl border border-primary/10 p-5">
                    <p class="text-xs font-semibold text-body/50 uppercase tracking-wide">Sedang Berjalan</p>
                    <p class="text-2xl font-bold text-amber-600 mt-1">{{ $berjalan->count() }}</p>
                </div>
                <div class="bg-white rounded-xl border border-primary/10 p-5">
                    <p class="text-xs font-semibold text-body/50 uppercase tracking-wide">Total Belanja</p>
                    <p class="text-2xl font-bold text-emerald-600 mt-1">Rp {{ number_format($totalBelanja, 0, ',', '.') }}</p>
                </div>
            </div>

            {{-- Daftar Pesanan --}}
            @forelse($pesanans as $o)
                @php
                    $paket = $o->detail_pesanan->first();
                    $totalO = (float) $o->total_tagihan;
                    $dpO = (float) $o->pembayaran->whereIn('status_pembayaran_id', [2, 3])->sum('jumlah_bayar');
                    $lunasO = (float) $o->pembayaran->where('status_pembayaran_id', 3)->sum('jumlah_bayar');
                    $bayarLabel = $lunasO >= $totalO ? 'Lunas' : ($dpO > 0 ? 'DP Terbayar' : 'Belum Bayar');
                    $bayarColor = $lunasO >= $totalO ? 'bg-emerald-100 text-emerald-700' : ($dpO > 0 ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700');

                    $statusMap = [
                        1 => ['Menunggu Konfirmasi', 'bg-amber-100 text-amber-700'],
                        2 => ['Dikonfirmasi', 'bg-blue-100 text-blue-700'],
                        3 => ['Diproses', 'bg-indigo-100 text-indigo-700'],
                        4 => ['Siap Dikirim', 'bg-cyan-100 text-cyan-700'],
                        5 => ['Selesai', 'bg-emerald-100 text-emerald-700'],
                        6 => ['Dibatalkan', 'bg-rose-100 text-rose-700'],
                    ];
                    $st = $statusMap[$o->status_pesanan_id] ?? ['-' , 'bg-gray-100 text-gray-700'];
                @endphp

                <div class="bg-white rounded-xl border border-primary/10 overflow-hidden mb-5">
                    <div class="flex flex-wrap items-center gap-3 px-6 py-4 border-b border-primary/5 bg-primary/[0.03]">
                        <span class="font-mono text-sm font-bold text-primary">{{ $o->nomor_pesanan }}</span>
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $o->jenis_pesanan_id === 2 ? 'bg-emerald-100 text-emerald-700' : 'bg-orange-100 text-orange-700' }}">
                            {{ $o->jenis_pesanan_id === 2 ? 'Katering' : 'Nasi Box' }}
                        </span>
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $st[1] }}">{{ $st[0] }}</span>
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $bayarColor }}">{{ $bayarLabel }}</span>
                        <span class="ml-auto text-xs text-body/50 font-medium">
                            {{ $o->pengantaran ? 'Diantar' : 'Ambil Sendiri' }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 px-6 py-5 text-sm">
                        <div>
                            <p class="text-xs font-semibold text-body/50 uppercase tracking-wide mb-1">Tanggal Pesan</p>
                            <p class="font-medium">{{ $o->dibuat_pada ? \Carbon\Carbon::parse($o->dibuat_pada)->format('d M Y H:i') : '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-body/50 uppercase tracking-wide mb-1">Tanggal Acara</p>
                            <p class="font-medium">{{ $o->jadwal_pesanan?->tanggal_acara ? \Carbon\Carbon::parse($o->jadwal_pesanan->tanggal_acara)->format('d M Y') : '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-body/50 uppercase tracking-wide mb-1">Paket & Porsi</p>
                            <p class="font-medium">{{ $paket->menu->nama_menu ?? 'Paket' }} · {{ $paket->jumlah ?? 0 }} porsi</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-body/50 uppercase tracking-wide mb-1">Total Tagihan</p>
                            <p class="font-bold text-primary">Rp {{ number_format($totalO, 0, ',', '.') }}</p>
                            @if($dpO > 0)
                                <p class="text-xs text-body/50">DP: Rp {{ number_format($dpO, 0, ',', '.') }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="px-6 py-3.5 border-t border-primary/5 flex flex-wrap items-center gap-2 bg-canvas/50">
                        <a href="{{ route('lacak.index', ['nomor' => $o->nomor_pesanan]) }}" class="text-xs font-semibold text-primary hover:underline">Lacak Status</a>
                        @if($o->status_pesanan_id === 1)
                            <span class="text-primary/20">|</span>
                            <a href="{{ route('pesanan.bayar', $o->nomor_pesanan) }}" class="text-xs font-semibold text-amber-600 hover:underline">Bayar</a>
                        @endif
                        <span class="text-primary/20">|</span>
                        <a href="{{ route('pesanan.invoice', $o->nomor_pesanan) }}" target="_blank" class="text-xs font-semibold text-body/60 hover:underline">Lihat Invoice</a>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-xl border border-dashed border-primary/20 p-14 text-center">
                    <x-heroicon-o-shopping-bag class="w-12 h-12 text-primary/30 mx-auto mb-4" />
                    <h2 class="text-lg font-bold text-primary">Belum ada pesanan</h2>
                    <p class="text-sm text-body/60 mt-1 mb-6">Pesan paket catering atau nasi box untuk mulai memantau pesanan Anda.</p>
                    <a href="{{ route('home') }}#catering" class="inline-block px-6 py-3 rounded-xl bg-primary text-white font-semibold text-sm hover:scale-105 hover:bg-primary-container transition">
                        Pesan Sekarang
                    </a>
                </div>
            @endforelse

        </div>
    </section>
</x-layouts.landing>
