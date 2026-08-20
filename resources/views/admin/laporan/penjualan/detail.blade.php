<div class="h-full flex flex-col bg-white">
    <!-- Header -->
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 shrink-0">
        <div>
            <h3 class="text-lg font-bold text-gray-900 tracking-tight">Detail Pesanan</h3>
            <p class="text-xs text-gray-500 font-medium mt-0.5">{{ $pesanan->id_pesanan }}</p>
        </div>
        <button type="button" onclick="closeDetailDrawer()" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full transition-colors">
            <x-heroicon-o-x-mark class="w-5 h-5" />
        </button>
    </div>

    <!-- Content -->
    <div class="flex-1 overflow-y-auto p-6 space-y-6 bg-gray-50/50">
        
        <!-- Info Pesanan -->
        <div class="bg-white border border-gray-100 rounded-xl p-4 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <span class="text-sm font-bold text-gray-900">Informasi Pelanggan</span>
                @php
                    $jId = $pesanan->jenis_pesanan_id;
                    $jColor = $jId == 1 ? 'gray' : ($jId == 2 ? 'warning' : 'primary');
                @endphp
                <x-ui.badge :color="$jColor" size="sm">{{ optional($pesanan->jenis_pesanan)->nama_jenis ?? 'Unknown' }}</x-ui.badge>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <span class="block text-xs font-semibold text-gray-500 mb-1">Tanggal Pesanan</span>
                    <span class="block text-sm font-medium text-gray-900">{{ \Carbon\Carbon::parse($pesanan->tanggal_pesanan)->format('d M Y, H:i') }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-gray-500 mb-1">Kasir</span>
                    <span class="block text-sm font-medium text-gray-900">{{ optional($pesanan->kasir)->nama ?? '-' }}</span>
                </div>
                <div class="col-span-2">
                    <span class="block text-xs font-semibold text-gray-500 mb-1">Pemesan</span>
                    <span class="block text-sm font-medium text-gray-900">
                        @if($pesanan->jenis_pesanan_id == 1)
                            Meja {{ optional($pesanan->meja)->nomor_meja ?? '-' }}
                        @else
                            {{ optional($pesanan->pelanggan)->nama ?? '-' }}
                            @if(optional($pesanan->pelanggan)->nomor_telepon)
                                <span class="text-xs text-gray-400 block mt-0.5">{{ \App\Support\WhatsAppNumber::formatForDisplay($pesanan->pelanggan->nomor_telepon) }}</span>
                            @endif
                        @endif
                    </span>
                </div>
            </div>
        </div>

        <!-- Detail Produk -->
        <div class="bg-white border border-gray-100 rounded-xl overflow-hidden shadow-sm">
            <div class="px-4 py-3 bg-gray-50/50 border-b border-gray-100">
                <span class="text-sm font-bold text-gray-900">Detail Produk</span>
            </div>
            <ul class="divide-y divide-gray-50">
                @foreach($pesanan->detail_pesanan as $detail)
                <li class="px-4 py-3 flex justify-between items-start">
                    <div>
                        <p class="text-sm font-semibold text-gray-900">{{ optional($detail->menu)->nama_menu ?? '-' }}</p>
                        <p class="text-xs font-medium text-gray-500 mt-0.5">{{ $detail->jumlah }} x Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}</p>
                    </div>
                    <span class="text-sm font-bold text-gray-900 tabular-nums">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</span>
                </li>
                @endforeach
            </ul>
        </div>

        <!-- Pembayaran -->
        <div class="bg-white border border-gray-100 rounded-xl p-4 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <span class="text-sm font-bold text-gray-900">Total Pembayaran</span>
                @php
                    $totalP = (float) $pesanan->total_tagihan;
                    $dpP = (float) $pesanan->pembayaran->where('status_verifikasi', 'diterima')->sum('jumlah_dibayar');
                    $lunasP = (float) $pesanan->pembayaran->where('status_verifikasi', 'diterima')->sum('jumlah_dibayar');
                    $bayarP = $lunasP >= $totalP ? 'lunas' : ($dpP > 0 ? 'dp' : 'belum');
                    $bayarColor = $bayarP === 'lunas' ? 'success' : ($bayarP === 'dp' ? 'primary' : 'warning');
                    $bayarLabel = $bayarP === 'lunas' ? 'Lunas' : ($bayarP === 'dp' ? 'DP Terbayar' : 'Belum Bayar');
                @endphp
                <x-ui.badge :color="$bayarColor" size="sm">{{ $bayarLabel }}</x-ui.badge>
            </div>
            
            <div class="space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="font-medium text-gray-500">Total Tagihan</span>
                    <span class="font-bold text-gray-900 tabular-nums">Rp {{ number_format($pesanan->total_tagihan, 0, ',', '.') }}</span>
                </div>
                
                @if($pesanan->pembayaran->count() > 0)
                    <div class="pt-2 mt-2 border-t border-dashed border-gray-200 space-y-2">
                        <span class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Riwayat Pembayaran</span>
                        @foreach($pesanan->pembayaran as $pay)
                            <div class="flex justify-between text-xs items-center">
                                <div>
                                    <span class="font-medium text-gray-700 block">{{ \Carbon\Carbon::parse($pay->dibuat_pada)->format('d M Y, H:i') }}</span>
                                    <span class="text-gray-400 block">{{ optional($pay->metode_pembayaran)->nama_metode ?? '-' }}</span>
                                </div>
                                <div class="text-right">
                                    <span class="font-bold text-gray-900 block tabular-nums">Rp {{ number_format($pay->jumlah_bayar, 0, ',', '.') }}</span>
                                    @php
                                        $sId = $pay->status_pembayaran_id;
                                        $sColor = $sId == 3 ? 'text-emerald-600' : ($sId == 1 ? 'text-amber-500' : 'text-gray-500');
                                    @endphp
                                    <span class="font-medium {{ $sColor }}">{{ optional($pay->status_pembayaran)->nama_status ?? '-' }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

    </div>
    
    <!-- Footer -->
    <div class="p-4 border-t border-gray-100 bg-white shrink-0">
        <a href="{{ route('laporan.penjualan.cetak-pdf', ['id' => $pesanan->id]) }}" class="w-full inline-flex justify-center items-center gap-1.5 text-sm font-semibold text-white bg-primary rounded-xl px-4 py-3 hover:bg-primary/90 transition-colors shadow-sm">
            <x-heroicon-o-printer class="w-4 h-4" />
            Cetak Struk Detail
        </a>
    </div>
</div>
