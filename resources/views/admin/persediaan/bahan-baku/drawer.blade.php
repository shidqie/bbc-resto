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

        $satuanNama = $bahanBaku->satuan->nama_satuan ?? $bahanBaku->satuan->singkatan ?? '';

        $isHabis = $stokTotal <= 0;
        $isMenipis = !$isHabis && $stokHarian <= $stokMinHarian;
        $hargaSatuan = (float) ($bahanBaku->harga_satuan ?? 0);
    @endphp

    {{-- INFORMASI BAHAN (Clean List - Non Table) --}}
    <div class="space-y-3">
        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Informasi Bahan Baku</h3>
        
        <div class="bg-white border border-slate-200/80 rounded-2xl divide-y divide-slate-100 text-xs shadow-2xs overflow-hidden">
            
            {{-- 1. Kode Bahan Baku --}}
            <div class="p-3.5 flex items-center justify-between">
                <span class="text-slate-500 font-medium">Kode Bahan Baku</span>
                <span class="font-mono font-bold text-slate-900 bg-slate-100 px-2.5 py-1 rounded-lg text-xs">{{ $bahanBaku->id_bahan_baku }}</span>
            </div>

            {{-- 2. Nama Bahan Baku --}}
            <div class="p-3.5 flex items-center justify-between">
                <span class="text-slate-500 font-medium">Nama Bahan Baku</span>
                <span class="font-bold text-slate-900 text-sm">{{ $bahanBaku->nama_bahan }}</span>
            </div>

            {{-- 3. Kategori --}}
            <div class="p-3.5 flex items-center justify-between">
                <span class="text-slate-500 font-medium">Kategori</span>
                <span class="text-slate-800 font-semibold">{{ $bahanBaku->kategori_bahan_baku->nama_kategori ?? '-' }}</span>
            </div>

            {{-- 4. Satuan --}}
            <div class="p-3.5 flex items-center justify-between">
                <span class="text-slate-500 font-medium">Satuan</span>
                <span class="text-slate-800 font-semibold">{{ $satuanNama }}</span>
            </div>

            {{-- 5. Stok Saat Ini --}}
            <div class="p-3.5 flex items-center justify-between bg-slate-50/70">
                <div>
                    <span class="text-slate-700 font-bold block">Total Stok</span>
                    <span class="text-[11px] text-slate-400">Resto: {{ \App\Helpers\UnitHelper::formatQuantity($stokHarian, $satuanNama) }} • Sisa Katering: {{ \App\Helpers\UnitHelper::formatQuantity($stokCatering, $satuanNama) }}</span>
                </div>
                <span class="font-black text-slate-900 text-base">
                    {{ \App\Helpers\UnitHelper::formatQuantity($stokTotal, $satuanNama) }}
                </span>
            </div>

            {{-- 6. Stok Minimal Harian --}}
            <div class="p-3.5 flex items-center justify-between">
                <span class="text-slate-500 font-medium">Stok Minimal Harian</span>
                <span class="font-bold text-slate-800 text-sm">{{ \App\Helpers\UnitHelper::formatQuantity($stokMinHarian, $satuanNama) }}</span>
            </div>

            {{-- 7. Stok Sisa Katering --}}
            <div class="p-3.5 flex items-center justify-between">
                <span class="text-slate-500 font-medium">Stok Sisa Katering</span>
                <span class="font-bold {{ $stokCatering > 0 ? 'text-amber-700' : 'text-slate-500' }} text-sm">
                    {{ \App\Helpers\UnitHelper::formatQuantity($stokCatering, $satuanNama) }}
                </span>
            </div>

            {{-- 8. Harga Satuan --}}
            <div class="p-3.5 flex items-center justify-between">
                <span class="text-slate-500 font-medium">Harga Satuan</span>
                <span class="font-mono font-bold text-emerald-700 text-sm">
                    Rp {{ number_format($hargaSatuan, 0, ',', '.') }} <span class="text-xs font-normal text-slate-500">/ {{ $satuanNama }}</span>
                </span>
            </div>

            {{-- 9. Status Stok --}}
            <div class="p-3.5 flex items-center justify-between">
                <span class="text-slate-500 font-medium">Status Stok</span>
                <div>
                    @if($isHabis)
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700 border border-red-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Habis
                        </span>
                    @elseif($isMenipis)
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Menipis
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aman
                        </span>
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
                    $tanggal = \Carbon\Carbon::parse($mutasi->tanggal_mutasi)->translatedFormat('d M Y');
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
