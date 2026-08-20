<div class="p-6 space-y-6 font-sans">

    {{-- Header --}}
    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
        <div>
            <h2 class="text-lg font-extrabold text-slate-900 tracking-tight">Detail Bahan Baku</h2>
            <p class="text-xs text-slate-400 font-medium">Informasi persediaan & riwayat mutasi stok</p>
        </div>
        <button type="button" onclick="closeDetailDrawer()" class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-full transition-colors">
            <x-heroicon-o-x-mark class="w-5 h-5" />
        </button>
    </div>

    @php 
        $stokHarian = (float) ($bahanBaku->stok_harian?->jumlah_stok ?? 0);
        $stokCatering = (float) ($bahanBaku->stok_catering_balance?->jumlah_stok ?? 0);
        $stokTotal = $stokHarian + $stokCatering;
        
        $stokMinHarian = (float) ($bahanBaku->stok_harian?->stok_minimal ?? $bahanBaku->stok_minimal ?? 0);
        $stokMinCatering = (float) ($bahanBaku->stok_catering_balance?->stok_minimal ?? 0);

        $satuanNama = $bahanBaku->satuan->nama_satuan ?? $bahanBaku->satuan->singkatan ?? '';
    @endphp

    {{-- INFORMASI BAHAN (Clean List - Non Table) --}}
    <div class="space-y-3">
        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Informasi Bahan</h3>
        
        <div class="bg-white border border-slate-200/80 rounded-2xl divide-y divide-slate-100 text-xs shadow-2xs overflow-hidden">
            
            <div class="p-3.5 flex items-center justify-between">
                <span class="text-slate-500 font-medium">Kode Bahan</span>
                <span class="font-mono font-bold text-slate-900 bg-slate-100 px-2 py-0.5 rounded text-xs">{{ $bahanBaku->id_bahan_baku }}</span>
            </div>

            <div class="p-3.5 flex items-center justify-between">
                <span class="text-slate-500 font-medium">Nama Bahan</span>
                <span class="font-bold text-slate-900 text-sm">{{ $bahanBaku->nama_bahan }}</span>
            </div>

            <div class="p-3.5 flex items-center justify-between">
                <span class="text-slate-500 font-medium">Kategori Bahan</span>
                <span class="text-slate-800 font-semibold">{{ $bahanBaku->kategori_bahan_baku->nama_kategori ?? '-' }}</span>
            </div>

            <div class="p-3.5 flex items-center justify-between">
                <span class="text-slate-500 font-medium">Satuan Ukur</span>
                <span class="text-slate-800 font-semibold">{{ $satuanNama }}</span>
            </div>

            <div class="p-3.5 flex items-center justify-between">
                <span class="text-slate-500 font-medium">Status Operasional</span>
                @if($bahanBaku->status_aktif)
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-600 border border-slate-200">
                        Nonaktif
                    </span>
                @endif
            </div>

            <div class="p-3.5 flex items-center justify-between bg-slate-50/70">
                <span class="text-slate-700 font-bold">Total Stok Saat Ini</span>
                <span class="font-black text-slate-900 text-base">
                    {{ rtrim(rtrim(number_format($stokTotal, 2, ',', '.'), '0'), ',') }}
                    <span class="text-xs font-medium text-slate-500">{{ $satuanNama }}</span>
                </span>
            </div>

            <div class="p-3.5 flex items-center justify-between">
                <span class="text-slate-500 font-medium">Rincian Per-Lokasi</span>
                <div class="text-right space-y-1">
                    <div class="flex items-center justify-end gap-2">
                        <span class="text-slate-400 font-medium">Resto:</span>
                        <span class="font-bold text-slate-900">{{ \App\Helpers\UnitHelper::formatQuantity($stokHarian, $satuanNama) }}</span>
                    </div>
                    <div class="flex items-center justify-end gap-2">
                        <span class="text-slate-400 font-medium">Catering:</span>
                        <span class="font-bold text-slate-900">{{ \App\Helpers\UnitHelper::formatQuantity($stokCatering, $satuanNama) }}</span>
                    </div>
                </div>
            </div>

            <div class="p-3.5 flex items-center justify-between bg-amber-50/50">
                <span class="text-amber-900 font-bold flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-amber-600 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    Batas Stok Minimum
                </span>
                <div class="text-right">
                    <div class="font-extrabold text-amber-900 text-sm">
                        {{ \App\Helpers\UnitHelper::formatQuantity($stokMinHarian, $satuanNama) }}
                    </div>
                    @if($stokMinCatering > 0)
                        <div class="text-[11px] text-amber-700 font-medium">Min Catering: {{ \App\Helpers\UnitHelper::formatQuantity($stokMinCatering, $satuanNama) }}</div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    {{-- RIWAYAT PENGGUNAAN (List Item Cards - Non Table) --}}
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Riwayat Penggunaan</h3>
            <a href="{{ route('mutasi-stok.index', ['search' => $bahanBaku->nama_bahan]) }}" class="text-xs font-bold text-emerald-600 hover:underline">
                Lihat Semua →
            </a>
        </div>

        <div class="space-y-2">
            @forelse($mutasiStoks as $mutasi)
                @php
                    $isMasuk = $mutasi->jenis_mutasi_stok_id == 1;
                    $jenisPers = $mutasi->jenis_persediaan === \App\Models\StokBahan::JENIS_CATERING ? 'Stok Catering' : 'Stok Harian Resto';
                    $tanggal = \Carbon\Carbon::parse($mutasi->tanggal_mutasi)->format('d M Y');
                @endphp
                
                <div class="p-3.5 bg-white border border-slate-200/80 rounded-xl shadow-2xs hover:border-slate-300 transition-all flex items-start justify-between gap-3">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold {{ $isMasuk ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                {{ $isMasuk ? '↓ Masuk' : '↑ Keluar' }}
                            </span>
                            <span class="text-xs text-slate-400 font-medium">{{ $tanggal }}</span>
                        </div>

                        <div class="text-xs text-slate-700 font-semibold">
                            {{ $mutasi->catatan ?: 'Mutasi Stok Persediaan' }}
                        </div>

                        <div class="text-[11px] text-slate-400 font-medium flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
                            {{ $jenisPers }}
                        </div>
                    </div>

                    <div class="text-right shrink-0">
                        <div class="text-sm font-extrabold {{ $isMasuk ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $isMasuk ? '+' : '-' }}{{ rtrim(rtrim(number_format($mutasi->jumlah, 2, ',', '.'), '0'), ',') }}
                        </div>
                        <div class="text-[10px] text-slate-400 font-medium uppercase">{{ $satuanNama }}</div>
                    </div>
                </div>

            @empty
                <div class="p-8 bg-white border border-slate-200/80 rounded-xl text-center text-slate-400 text-xs italic">
                    Belum ada riwayat mutasi/penggunaan bahan ini.
                </div>
            @endforelse
        </div>
    </div>

</div>
