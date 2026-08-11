<div class="h-full flex flex-col bg-white">
    <!-- Header -->
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 shrink-0">
        <div>
            <h3 class="text-lg font-bold text-gray-900 tracking-tight">Detail Pengadaan</h3>
            <p class="text-xs text-gray-500 font-medium mt-0.5">{{ $pengadaan->id_pengadaan }}</p>
        </div>
        <button type="button" onclick="closeDetailDrawer()" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full transition-colors">
            <x-heroicon-o-x-mark class="w-5 h-5" />
        </button>
    </div>

    <!-- Content -->
    <div class="flex-1 overflow-y-auto p-6 space-y-6 bg-gray-50/50">
        
        <!-- Info Permintaan -->
        <div class="bg-white border border-gray-100 rounded-xl p-4 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <span class="text-sm font-bold text-gray-900">Informasi Permintaan</span>
                @php
                    $sId = $pengadaan->status_pengadaan_id;
                    $sColor = 'gray';
                    if($sId == 1) $sColor = 'warning';
                    elseif($sId == 2) $sColor = 'primary';
                    elseif($sId == 3) $sColor = 'primary';
                    elseif($sId == 4) $sColor = 'success';
                    elseif($sId == 5) $sColor = 'danger';
                @endphp
                <x-ui.badge :color="$sColor" size="sm">{{ optional($pengadaan->status_pengadaan)->nama_status ?? 'Unknown' }}</x-ui.badge>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <span class="block text-xs font-semibold text-gray-500 mb-1">Tanggal Permintaan</span>
                    <span class="block text-sm font-medium text-gray-900">{{ \Carbon\Carbon::parse($pengadaan->tanggal_pengadaan)->format('d M Y, H:i') }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-gray-500 mb-1">Pemohon</span>
                    <span class="block text-sm font-medium text-gray-900">{{ optional($pengadaan->diajukan_oleh_pengguna)->nama ?? '-' }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-gray-500 mb-1">Jenis Permintaan</span>
                    <span class="block text-sm font-medium text-gray-900">{{ ucfirst($pengadaan->jenis_pengadaan) }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-gray-500 mb-1">Supplier</span>
                    <span class="block text-sm font-medium text-gray-900">{{ optional($pengadaan->pemasok)->nama_pemasok ?? 'Belum Ditentukan' }}</span>
                </div>
                <div class="col-span-2">
                    <span class="block text-xs font-semibold text-gray-500 mb-1">Catatan</span>
                    <span class="block text-sm font-medium text-gray-900">{{ $pengadaan->catatan ?: '-' }}</span>
                </div>
            </div>
        </div>

        <!-- Daftar Bahan -->
        <div class="bg-white border border-gray-100 rounded-xl overflow-hidden shadow-sm">
            <div class="px-4 py-3 bg-gray-50/50 border-b border-gray-100 flex justify-between items-center">
                <span class="text-sm font-bold text-gray-900">Daftar Kebutuhan Bahan</span>
                <span class="text-xs font-semibold text-gray-500">{{ $pengadaan->detail_pengadaan_bahan->count() }} item</span>
            </div>
            <ul class="divide-y divide-gray-50">
                @foreach($pengadaan->detail_pengadaan_bahan as $detail)
                <li class="px-4 py-3 flex justify-between items-center">
                    <div>
                        <p class="text-sm font-semibold text-gray-900">{{ optional($detail->bahan_baku)->nama_bahan ?? '-' }}</p>
                        <p class="text-xs text-gray-400 font-mono mt-0.5">{{ optional($detail->bahan_baku)->id_bahan_baku ?? '-' }}</p>
                    </div>
                    <div class="text-right">
                        <span class="text-sm font-bold text-gray-900 tabular-nums">{{ (float)$detail->jumlah_dipesan }}</span>
                        <span class="text-xs font-medium text-gray-500">{{ optional(optional($detail->bahan_baku)->satuan)->nama_satuan ?? '-' }}</span>
                    </div>
                </li>
                @endforeach
            </ul>
            
            <div class="px-4 py-3 bg-gray-50/50 border-t border-gray-100 flex justify-between items-center">
                <span class="text-sm font-bold text-gray-700">Total Biaya (Estimasi/Aktual)</span>
                <span class="text-base font-bold text-gray-900 tabular-nums">Rp {{ number_format($pengadaan->total_pengadaan, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Riwayat Penerimaan -->
        <div class="bg-white border border-gray-100 rounded-xl overflow-hidden shadow-sm">
            <div class="px-4 py-3 bg-gray-50/50 border-b border-gray-100 flex justify-between items-center">
                <span class="text-sm font-bold text-gray-900">Riwayat Penerimaan</span>
                <span class="text-xs font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">{{ $pengadaan->penerimaan_bahan->count() }} Surat Jalan</span>
            </div>
            <ul class="divide-y divide-gray-50">
                @forelse($pengadaan->penerimaan_bahan as $penerimaan)
                <li class="px-4 py-3 flex justify-between items-center">
                    <div>
                        <p class="text-sm font-semibold text-gray-900">{{ $penerimaan->nomor_penerimaan }}</p>
                        <p class="text-xs font-medium text-gray-500 mt-0.5">{{ \Carbon\Carbon::parse($penerimaan->diterima_pada)->format('d M Y, H:i') }} | Penerima: {{ optional($penerimaan->diterima_oleh_pengguna)->nama ?? '-' }}</p>
                    </div>
                    <button type="button" class="text-xs font-semibold text-blue-600 hover:text-blue-700">Lihat SJ</button>
                </li>
                @empty
                <li class="px-4 py-8 text-center text-gray-400">
                    <p class="text-sm">Bahan belum diterima.</p>
                </li>
                @endforelse
            </ul>
        </div>
    </div>
    
    <!-- Footer -->
    <div class="p-4 border-t border-gray-100 bg-white shrink-0">
        <a href="{{ route('laporan.pengadaan.cetak-pdf', ['id' => $pengadaan->id]) }}" class="w-full inline-flex justify-center items-center gap-1.5 text-sm font-semibold text-white bg-gray-900 rounded-xl px-4 py-3 hover:bg-gray-800 transition-colors shadow-sm">
            <x-heroicon-o-printer class="w-4 h-4" />
            Cetak Detail Pengadaan
        </a>
    </div>
</div>
