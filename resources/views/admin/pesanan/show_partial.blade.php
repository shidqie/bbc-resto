<div class="flex flex-col h-full bg-gray-50/50 text-gray-800">
    @php
        $userRole = auth()->user()->peran->nama_peran ?? '';
        $isAdminOrPemilik = in_array($userRole, ['Admin', 'Super Admin', 'Pemilik', 'Manajer']);
        $isDapur = (auth()->user()->hasRole('Dapur', 'Tim Dapur') || in_array($userRole, ['Dapur', 'Tim Dapur', 'Koki'])) && !$isAdminOrPemilik;
        $statusId = (int) $pesanan->status_pesanan_id;

        $statusConfig = match($statusId) {
            1 => ['label' => 'Menunggu Konfirmasi', 'color' => 'bg-amber-50 text-amber-800 border-amber-200/90', 'dot' => 'bg-amber-500'],
            2 => ['label' => 'Dikonfirmasi', 'color' => 'bg-blue-50 text-blue-800 border-blue-200/90', 'dot' => 'bg-blue-500'],
            3 => ['label' => 'Sedang Diproses', 'color' => 'bg-indigo-50 text-indigo-800 border-indigo-200/90', 'dot' => 'bg-indigo-500 animate-pulse'],
            4 => ['label' => 'Pesanan Siap', 'color' => 'bg-purple-50 text-purple-800 border-purple-200/90', 'dot' => 'bg-purple-500'],
            8 => ['label' => 'Pesanan Telah Dihidangkan', 'color' => 'bg-teal-50 text-teal-800 border-teal-200/90', 'dot' => 'bg-teal-500'],
            5 => ['label' => 'Selesai', 'color' => 'bg-emerald-50 text-emerald-800 border-emerald-200/90', 'dot' => 'bg-emerald-500'],
            6 => ['label' => 'Dibatalkan', 'color' => 'bg-rose-50 text-rose-800 border-rose-200/90', 'dot' => 'bg-rose-500'],
            default => ['label' => optional($pesanan->status_pesanan)->nama_status ?? 'Status #' . $statusId, 'color' => 'bg-gray-50 text-gray-700 border-gray-200', 'dot' => 'bg-gray-400'],
        };

        $nama = 'Tamu';
        if (!empty($pesanan->catatan)) {
            if (preg_match('/^Pemesan:\s*(.+)$/m', $pesanan->catatan, $m)) {
                $nama = trim($m[1]);
            } elseif (preg_match('/Self-Order QR \(([^)]+)\)/', $pesanan->catatan, $m)) {
                $nama = trim($m[1]);
            } elseif (preg_match('/^(.+?)\s*\(\d+\s*tamu\)/', $pesanan->catatan, $m)) {
                $nama = trim($m[1]);
            } else {
                $nama = trim(explode('|', $pesanan->catatan)[0]);
            }
        } elseif ($pesanan->pelanggan) {
            $nama = $pesanan->pelanggan->nama;
        }

        $rawMeja = $pesanan->meja->nomor_meja ?? null;
        $mejaLabel = $rawMeja ? (str_starts_with(strtolower($rawMeja), 'meja') ? $rawMeja : 'Meja ' . $rawMeja) : '-';

        $namaKasir = $pesanan->kasir->nama ?? ($pesanan->pembayaran->whereNotNull('diverifikasi_oleh')->first()?->diverifikasi_oleh_pengguna?->nama ?? '-');

        $terbayar = (float) $pesanan->pembayaran->where('status_verifikasi', 'diterima')->sum('jumlah_dibayar');
        if ($terbayar == 0) {
            $terbayar = (float) $pesanan->pembayaran->sum('jumlah_dibayar');
        }
        $totalTagihan = (float) $pesanan->total_tagihan;
        $sisa = $totalTagihan - $terbayar;
        $isLunas = ($sisa <= 0 && $totalTagihan > 0) || ($pesanan->status_pembayaran_id == 5);
    @endphp

    {{-- HEADER DRAWER (MINIMALIST) --}}
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200/80 shrink-0 bg-white sticky top-0 z-30 shadow-2xs">
        <div>
            <div class="flex items-center gap-2 flex-wrap">
                <h2 class="text-base sm:text-lg font-bold text-gray-900 font-mono tracking-tight leading-none">
                    {{ $pesanan->id_pesanan ?? 'DIN-'.$pesanan->id }}
                </h2>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $statusConfig['color'] }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $statusConfig['dot'] }}"></span>
                    <span>{{ $statusConfig['label'] }}</span>
                </span>
                <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-gray-100 text-gray-600 border border-gray-200/60">
                    {{ optional($pesanan->jenis_pesanan)->nama_jenis ?? 'Dine In' }}
                </span>
            </div>
            <p class="text-xs text-gray-500 font-medium mt-1">
                Dibuat: {{ \Carbon\Carbon::parse($pesanan->dibuat_pada)->translatedFormat('d F Y, H.i') }} WIB
            </p>
        </div>
        
        <div class="flex items-center gap-2">
            <button type="button" @click="open = false; $dispatch('close-dinein-drawer')" onclick="window.dispatchEvent(new CustomEvent('close-dinein-drawer'))" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold text-gray-700 bg-gray-100 hover:bg-red-50 hover:text-red-700 border border-gray-200 hover:border-red-200 shadow-2xs transition-all cursor-pointer">
                <span>Tutup</span>
            </button>
        </div>
    </div>

    {{-- SCROLLABLE BODY --}}
    <div class="flex-1 overflow-y-auto p-6 space-y-5">
        
        {{-- Banner Jika Dibatalkan --}}
        @if($pesanan->status_pesanan_id === 6 && $pesanan->alasan_batal)
            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200/90">
                <h4 class="text-sm font-bold text-rose-900">Pesanan Dibatalkan</h4>
                <p class="text-xs text-rose-700 mt-0.5">{{ $pesanan->alasan_batal }}</p>
            </div>
        @endif

        {{-- CARD INFORMASI UTAMA (MINIMALIST) --}}
        <div class="grid grid-cols-1 {{ $isDapur ? 'sm:grid-cols-2' : 'sm:grid-cols-3' }} gap-3">
            {{-- Card Pemesan --}}
            <div class="bg-white border border-gray-200/80 rounded-2xl p-3.5 shadow-2xs flex flex-col justify-between">
                <span class="text-gray-400 text-[11px] font-bold uppercase tracking-wider mb-1">Pemesan</span>
                <p class="text-sm font-bold text-gray-900 truncate" title="{{ $nama }}">{{ $nama }}</p>
            </div>

            {{-- Card Meja --}}
            <div class="bg-white border border-gray-200/80 rounded-2xl p-3.5 shadow-2xs flex flex-col justify-between">
                <span class="text-gray-400 text-[11px] font-bold uppercase tracking-wider mb-1">Meja</span>
                <p class="text-sm font-extrabold text-emerald-700 truncate">{{ $mejaLabel }}</p>
            </div>

            @if(!$isDapur)
            {{-- Card Kasir --}}
            <div class="bg-white border border-gray-200/80 rounded-2xl p-3.5 shadow-2xs flex flex-col justify-between">
                <span class="text-gray-400 text-[11px] font-bold uppercase tracking-wider mb-1">Kasir</span>
                <p class="text-sm font-bold text-gray-900 truncate">{{ $namaKasir }}</p>
            </div>
            @endif
        </div>

        {{-- DAFTAR ITEM PESANAN --}}
        <div class="bg-white rounded-2xl border border-gray-200/80 shadow-2xs overflow-hidden">
            <div class="px-5 py-3.5 bg-gray-50/70 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wider">Daftar Item Pesanan</h3>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200/80">
                    {{ $pesanan->detail_pesanan->sum('jumlah') }} Porsi
                </span>
            </div>

            <div class="divide-y divide-gray-100 p-2 sm:p-3">
                @forelse($pesanan->detail_pesanan as $item)
                    <div class="flex items-start justify-between gap-3 p-3 rounded-xl hover:bg-gray-50/80 transition-colors">
                        <div class="flex items-start gap-3 min-w-0">
                            <div class="w-8 h-8 rounded-xl bg-gray-100 text-gray-800 font-bold text-xs flex items-center justify-center shrink-0 border border-gray-200 mt-0.5">
                                {{ $item->jumlah }}x
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-gray-900 leading-snug truncate">
                                    {{ optional($item->menu)->nama_menu ?? optional($item->menu)->nama_produk ?? 'Menu Dihapus' }}
                                </p>
                                @if(!$isDapur)
                                <p class="text-xs text-gray-400 font-medium mt-0.5">
                                    @ Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}
                                </p>
                                @endif
                                @if($item->catatan)
                                    <div class="mt-1.5 inline-block px-2 py-0.5 rounded-md bg-amber-50 text-amber-800 text-[11px] font-medium border border-amber-200/70">
                                        Catatan: {{ $item->catatan }}
                                    </div>
                                @endif
                                @if($item->is_tambahan)
                                    <span class="mt-1 inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                        Tambahan (Batch {{ $item->batch_pesanan }})
                                    </span>
                                @endif
                            </div>
                        </div>
                        @if(!$isDapur)
                        <div class="text-right shrink-0">
                            <span class="text-sm font-bold text-gray-900 tabular-nums">
                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                            </span>
                        </div>
                        @endif
                    </div>
                @empty
                    <div class="p-6 text-sm text-gray-400 text-center font-medium">
                        Tidak ada rincian item pesanan.
                    </div>
                @endforelse
            </div>

            @if(!$isDapur)
            {{-- Kalkulasi & Ringkasan Biaya --}}
            @php
                $subtotal = $pesanan->detail_pesanan->sum('subtotal');
                $diskon = $pesanan->diskon ?? 0;
                $pajak = $pesanan->jumlah_pajak ?? $pesanan->pajak ?? 0;
                $layanan = $pesanan->biaya_pelayanan ?? 0;
            @endphp
            <div class="bg-gray-50/80 border-t border-gray-100 p-4 sm:p-5 space-y-2">
                <div class="flex justify-between text-xs text-gray-600 font-medium">
                    <span>Subtotal</span>
                    <span class="tabular-nums font-semibold text-gray-800">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                </div>
                @if($diskon > 0)
                    <div class="flex justify-between text-xs text-rose-600 font-medium">
                        <span>Diskon</span>
                        <span class="tabular-nums font-semibold">- Rp {{ number_format($diskon, 0, ',', '.') }}</span>
                    </div>
                @endif
                @if($layanan > 0)
                    <div class="flex justify-between text-xs text-gray-600 font-medium">
                        <span>Biaya Pelayanan</span>
                        <span class="tabular-nums font-semibold text-gray-800">+ Rp {{ number_format($layanan, 0, ',', '.') }}</span>
                    </div>
                @endif
                @if($pajak > 0)
                    <div class="flex justify-between text-xs text-gray-600 font-medium">
                        <span>Pajak (PB1)</span>
                        <span class="tabular-nums font-semibold text-gray-800">+ Rp {{ number_format($pajak, 0, ',', '.') }}</span>
                    </div>
                @endif
                <div class="border-t border-gray-200/80 pt-2.5 flex justify-between items-center">
                    <span class="text-sm font-bold text-gray-900">Total Keseluruhan</span>
                    <span class="text-base sm:text-lg font-extrabold text-emerald-700 tabular-nums">
                        Rp {{ number_format($pesanan->total_tagihan, 0, ',', '.') }}
                    </span>
                </div>
            </div>
            @endif
        </div>

        {{-- Catatan Tambahan Khusus --}}
        @if(!empty($pesanan->catatan) && !preg_match('/^Pemesan:/', $pesanan->catatan) && !preg_match('/Self-Order QR/', $pesanan->catatan))
            <div class="bg-amber-50/60 border border-amber-200/80 rounded-2xl p-4">
                <span class="text-xs font-bold text-amber-900 block mb-1">Catatan Tambahan Pesanan</span>
                <p class="text-xs text-amber-800 leading-relaxed">{{ $pesanan->catatan }}</p>
            </div>
        @endif

        @if(!$isDapur)
        {{-- STATUS & RIWAYAT PEMBAYARAN --}}
        <div class="bg-white rounded-2xl border border-gray-200/80 shadow-2xs overflow-hidden">
            <div class="px-5 py-3.5 bg-gray-50/70 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wider">Status & Riwayat Pembayaran</h3>
                @if($isLunas)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                        LUNAS
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-800 border border-rose-200">
                        Belum Lunas &bull; Sisa Rp {{ number_format(max(0, $sisa), 0, ',', '.') }}
                    </span>
                @endif
            </div>

            <div class="p-4 sm:p-5">
                @if($pesanan->pembayaran->isNotEmpty())
                    <div class="space-y-2">
                        @foreach($pesanan->pembayaran as $bayar)
                            <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50/70 border border-gray-100">
                                <div>
                                    <p class="text-xs font-bold text-gray-900">
                                        {{ optional($bayar->metode_pembayaran)->nama_metode ?? $bayar->metode_bayar ?? 'Tunai / Cash' }}
                                    </p>
                                    <p class="text-[11px] text-gray-400 font-medium">
                                        {{ \Carbon\Carbon::parse($bayar->dibayar_pada ?? $bayar->dibuat_pada)->translatedFormat('d M Y, H.i') }} WIB
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span class="text-xs font-bold text-emerald-700 tabular-nums">
                                        + Rp {{ number_format($bayar->jumlah_dibayar ?? $bayar->jumlah_bayar ?? $bayar->total, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-5 text-center">
                        <p class="text-xs font-bold text-gray-700">Belum Ada Pembayaran Masuk</p>
                        <p class="text-[11px] text-gray-400 mt-0.5">Pelanggan belum melakukan pembayaran di kasir.</p>
                    </div>
                @endif
            </div>
        </div>
        @endif
        
    </div>

    {{-- STICKY FOOTER ACTIONS --}}
    <div class="px-6 py-4 bg-white border-t border-gray-200/80 shrink-0 flex items-center justify-between gap-3 shadow-lg">
        <div class="flex items-center gap-2">
            <a href="{{ route('pos.dinein.print-dapur', $pesanan->id) }}" target="_blank" class="inline-flex items-center px-3.5 py-2 bg-white hover:bg-gray-50 text-gray-700 font-bold text-xs rounded-xl border border-gray-200 transition-all cursor-pointer shadow-2xs">
                <span>Cetak Struk Dapur Checker</span>
            </a>
        </div>

        <div class="flex items-center gap-2">
            @if(in_array($statusId, [1, 2, 3]) && $isDapur)
                <form action="{{ route('admin.pesanan.dinein.update-status', $pesanan->id) }}" method="POST" class="inline-block">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status_pesanan_id" value="4">
                    <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold text-xs rounded-xl shadow-xs transition-all cursor-pointer">
                        <span>Tandai Pesanan Siap</span>
                    </button>
                </form>
            @elseif($statusId == 4)
                @if(!$isDapur)
                    <form action="{{ route('admin.pesanan.dinein.update-status', $pesanan->id) }}" method="POST" class="inline-block">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status_pesanan_id" value="8">
                        <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-teal-600 hover:bg-teal-700 active:scale-95 text-white font-bold text-xs rounded-xl shadow-xs transition-all cursor-pointer">
                            <x-heroicon-o-check-badge class="w-4 h-4 mr-1.5" />
                            <span>Tandai Telah Dihidangkan</span>
                        </button>
                    </form>
                @else
                    <span class="inline-flex items-center text-xs font-bold text-purple-700 bg-purple-50 px-3 py-2 rounded-xl border border-purple-200">
                        <span>Pesanan Siap</span>
                    </span>
                @endif
            @elseif($statusId == 8)
                <span class="inline-flex items-center text-xs font-bold text-teal-700 bg-teal-50 px-3 py-2 rounded-xl border border-teal-200">
                    <span>Pesanan Telah Dihidangkan</span>
                </span>
            @endif
        </div>
    </div>
</div>
