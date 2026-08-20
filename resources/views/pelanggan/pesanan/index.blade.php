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
            @if($pesanans->isEmpty())
                <div class="bg-white rounded-xl border border-dashed border-primary/20 p-14 text-center">
                    <x-heroicon-o-shopping-bag class="w-12 h-12 text-primary/30 mx-auto mb-4" />
                    <h2 class="text-lg font-bold text-primary">Belum ada pesanan</h2>
                    <p class="text-sm text-body/60 mt-1 mb-6">Pesan paket catering atau nasi box untuk mulai memantau pesanan Anda.</p>
                    <a href="{{ route('home') }}#catering" class="inline-block px-6 py-3 rounded-xl bg-primary text-white font-semibold text-sm hover:scale-105 hover:bg-primary-container transition">
                        Pesan Sekarang
                    </a>
                </div>
            @else
                <div class="bg-white rounded-xl border border-primary/10 overflow-hidden mb-5 shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse whitespace-nowrap">
                            <thead>
                                <tr class="border-b border-primary/10 bg-primary/[0.03] text-xs font-bold text-body/50 uppercase tracking-wider">
                                    <th class="py-4 px-5">Kode Pesanan</th>
                                    <th class="py-4 px-5">Tanggal Acara</th>
                                    <th class="py-4 px-5">Paket & Porsi</th>
                                    <th class="py-4 px-5">Status Pembayaran</th>
                                    <th class="py-4 px-5">Status Pesanan</th>
                                    <th class="py-4 px-5 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-primary/5 text-sm">
                                @foreach($pesanans as $o)
                                    @php
                                        $paket = $o->detail_pesanan->first();
                                        $totalO = (float) $o->total_tagihan;
                                        $dpO = (float) $o->pembayaran->where('status_verifikasi', 'diterima')->sum('jumlah_dibayar');
                                        $lunasO = (float) $o->pembayaran->where('status_verifikasi', 'diterima')->sum('jumlah_dibayar');
                                        $terakhirBayarO = $o->pembayaran->last();
                                        $statusVerifikasiO = $terakhirBayarO ? $terakhirBayarO->status_verifikasi : null;

                                        $bayarLabel = $lunasO >= $totalO ? 'Lunas' : ($statusVerifikasiO === 'menunggu_verifikasi' ? 'Menunggu Verifikasi' : ($statusVerifikasiO === 'ditolak' ? 'Ditolak' : ($dpO > 0 ? 'DP Terbayar' : 'Belum Bayar')));
                                        $bayarColor = $lunasO >= $totalO ? 'bg-emerald-100 text-emerald-700' : ($statusVerifikasiO === 'menunggu_verifikasi' ? 'bg-primary/10 text-primary' : ($statusVerifikasiO === 'ditolak' ? 'bg-red-100 text-red-700' : ($dpO > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700')));

                                        $statusMap = [
                                            1 => ['Menunggu Konfirmasi', 'bg-amber-100 text-amber-700'],
                                            2 => ['Dikonfirmasi', 'bg-primary/10 text-primary'],
                                            3 => ['Diproses', 'bg-primary/10 text-primary'],
                                            4 => ['Siap Dikirim', 'bg-cyan-100 text-cyan-700'],
                                            5 => ['Selesai', 'bg-emerald-100 text-emerald-700'],
                                            6 => ['Dibatalkan', 'bg-rose-100 text-rose-700'],
                                        ];
                                        $st = $statusMap[$o->status_pesanan_id] ?? ['-' , 'bg-gray-100 text-gray-700'];
                                    @endphp
                                    <tr class="hover:bg-primary/[0.01] transition-colors">
                                        <td class="py-4 px-5">
                                            <div class="font-mono font-bold text-primary">{{ $o->id_pesanan }}</div>
                                            <div class="text-xs text-body/50 mt-1 font-medium">{{ $o->dibuat_pada ? \Carbon\Carbon::parse($o->dibuat_pada)->format('d M Y H:i') : '-' }}</div>
                                        </td>
                                        <td class="py-4 px-5 font-medium text-gray-800">
                                            {{ $o->jadwal_pesanan?->tanggal_acara ? \Carbon\Carbon::parse($o->jadwal_pesanan->tanggal_acara)->format('d M Y') : '-' }}
                                        </td>
                                        <td class="py-4 px-5">
                                            <div class="font-medium text-gray-900">{{ $paket->menu->nama_menu ?? 'Paket' }}</div>
                                            <div class="text-xs text-body/50 mt-1 font-medium">{{ $paket->jumlah ?? 0 }} porsi</div>
                                        </td>
                                        <td class="py-4 px-5">
                                            <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ $bayarColor }}">{{ $bayarLabel }}</span>
                                        </td>
                                        <td class="py-4 px-5">
                                            <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ $st[1] }}">{{ $st[0] }}</span>
                                        </td>
                                        <td class="py-4 px-5 text-right space-x-3">
                                            <a href="{{ route('konsumen.pesanan.show', $o->id_pesanan) }}" class="text-xs font-bold text-primary hover:underline">Detail</a>
                                            @if($o->status_pesanan_id === 1 && $bayarLabel !== 'Lunas')
                                                <a href="{{ route('pesanan.bayar', $o->id_pesanan) }}" class="text-xs font-bold text-amber-600 hover:underline">Bayar</a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

        </div>
    </section>
</x-layouts.landing>
