<div class="h-full flex flex-col bg-white">
    <!-- Header -->
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 shrink-0">
        <div>
            <h3 class="text-lg font-bold text-gray-900 tracking-tight">Detail Bahan Baku</h3>
            <p class="text-xs text-gray-500 font-medium mt-0.5">{{ $bahan->id_bahan_baku }} &bull; {{ $jenisStokLabel ?? 'Stok Persediaan' }}</p>
        </div>
        <button type="button" onclick="closeDetailDrawer()" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full transition-colors">
            <x-heroicon-o-x-mark class="w-5 h-5" />
        </button>
    </div>

    <!-- Content -->
    <div class="flex-1 overflow-y-auto p-6 space-y-6 bg-gray-50/50">
        
        <!-- Info Bahan -->
        <div class="bg-white border border-gray-100 rounded-xl p-4 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <span class="text-sm font-bold text-gray-900">Informasi Bahan</span>
                @php
                    $isCatering = ($jenisStok === 'catering' || $jenisStok === 'katering');
                    $stokAkhir = (float)($stokSaatIni ?? (optional($stok)->jumlah_stok ?? 0));
                    $satuanNama = optional($bahan->satuan)->singkatan ?? optional($bahan->satuan)->nama_satuan ?? 'pcs';
                    
                    if ($isCatering) {
                        $badgeColor = $status == 'Tersedia' ? 'success' : 'secondary';
                    } else {
                        $badgeColor = $status == 'Aman' ? 'success' : ($status == 'Menipis' ? 'warning' : 'danger');
                    }
                @endphp
                <x-ui.badge :color="$badgeColor" size="sm">{{ $status }}</x-ui.badge>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <span class="block text-xs font-semibold text-gray-500 mb-1">Nama Bahan</span>
                    <span class="block text-sm font-bold text-gray-900">{{ $bahan->nama_bahan }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-gray-500 mb-1">Kategori</span>
                    <span class="block text-sm font-medium text-gray-900">{{ optional($bahan->kategori_bahan_baku)->nama_kategori ?? '-' }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-gray-500 mb-1">Cakupan Stok</span>
                    <span class="block text-sm font-medium text-gray-900">{{ $isCatering ? 'Katering (Pesanan Khusus)' : 'Harian (Dine-In & Nasi Box)' }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-gray-500 mb-1">Satuan</span>
                    <span class="block text-sm font-medium text-gray-900">{{ $satuanNama }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-gray-500 mb-1">Stok Minimum</span>
                    @if($isCatering)
                        <span class="block text-sm font-medium text-gray-400 italic">- (Tidak Ada Min)</span>
                    @else
                        <span class="block text-sm font-semibold text-gray-700 tabular-nums">
                            {{ \App\Helpers\UnitHelper::formatQuantity($stokMin, $satuanNama) }}
                        </span>
                    @endif
                </div>
                <div>
                    <span class="block text-xs font-semibold text-gray-500 mb-1">{{ $isCatering ? 'Stok Sisa Katering' : 'Stok Saat Ini' }}</span>
                    <span class="block text-xl font-bold {{ $badgeColor == 'success' ? 'text-emerald-600' : ($badgeColor == 'warning' ? 'text-amber-600' : 'text-rose-600') }} tabular-nums">
                        {{ \App\Helpers\UnitHelper::formatQuantity($stokAkhir, $satuanNama) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Riwayat Mutasi (Terakhir) -->
        <div class="bg-white border border-gray-100 rounded-xl overflow-hidden shadow-sm">
            <div class="px-4 py-3 bg-gray-50/50 border-b border-gray-100 flex items-center justify-between">
                <span class="text-sm font-bold text-gray-900">Riwayat Mutasi (20 Terakhir)</span>
                <span class="text-xs text-gray-500 font-medium">{{ $isCatering ? 'Mutasi Katering' : 'Mutasi Harian' }}</span>
            </div>
            <ul class="divide-y divide-gray-50">
                @forelse($mutasis as $mutasi)
                <li class="px-4 py-3 flex justify-between items-center">
                    <div>
                        <p class="text-sm font-semibold text-gray-900">{{ optional($mutasi->jenis_mutasi_stok)->nama_jenis_mutasi ?? '-' }}</p>
                        <p class="text-xs font-medium text-gray-500 mt-0.5">{{ \Carbon\Carbon::parse($mutasi->tanggal_mutasi)->translatedFormat('d M Y, H:i') }} | {{ optional($mutasi->pengguna)->nama ?? 'Sistem' }}</p>
                        @if($mutasi->catatan || $mutasi->keterangan)
                            <p class="text-xs text-gray-400 mt-0.5 italic">{{ $mutasi->catatan ?? $mutasi->keterangan }}</p>
                        @endif
                    </div>
                    @php
                        $arah = optional($mutasi->jenis_mutasi_stok)->arah_stok;
                        $color = $arah == 'MASUK' ? 'text-emerald-600' : 'text-rose-600';
                        $sign = $arah == 'MASUK' ? '+' : '-';
                    @endphp
                    <span class="text-sm font-bold {{ $color }} tabular-nums">{{ $sign }} {{ \App\Helpers\UnitHelper::formatQuantity((float)$mutasi->jumlah, $satuanNama) }}</span>
                </li>
                @empty
                <li class="px-4 py-8 text-center text-gray-400">
                    <p class="text-sm">Belum ada riwayat mutasi.</p>
                </li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
