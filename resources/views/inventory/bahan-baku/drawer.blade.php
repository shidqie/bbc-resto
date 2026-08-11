<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-bold text-gray-900">Detail Bahan Baku</h2>
        <button type="button" onclick="closeDetailDrawer()" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full transition-colors">
            <x-heroicon-o-x-mark class="w-5 h-5" />
        </button>
    </div>

    <!-- Informasi Bahan -->
    <div class="mb-8">
        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">Informasi Bahan</h3>
        <div class="bg-gray-50/50 border border-gray-100 rounded-xl p-4">
            <dl class="space-y-3 text-sm">
                <div class="grid grid-cols-3 gap-4">
                    <dt class="text-gray-500 font-medium">Kode Bahan</dt>
                    <dd class="col-span-2 font-mono font-semibold text-gray-900">{{ $bahanBaku->id_bahan_baku }}</dd>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <dt class="text-gray-500 font-medium">Nama Bahan</dt>
                    <dd class="col-span-2 font-semibold text-gray-900">{{ $bahanBaku->nama_bahan }}</dd>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <dt class="text-gray-500 font-medium">Kategori</dt>
                    <dd class="col-span-2 text-gray-700">{{ $bahanBaku->kategori_bahan_baku->nama_kategori ?? '-' }}</dd>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <dt class="text-gray-500 font-medium">Satuan</dt>
                    <dd class="col-span-2 text-gray-700">{{ $bahanBaku->satuan->nama_satuan ?? '-' }}</dd>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <dt class="text-gray-500 font-medium">Status</dt>
                    <dd class="col-span-2">
                        @if($bahanBaku->status_aktif)
                            <span class="inline-block text-xs font-semibold px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-100">Aktif</span>
                        @else
                            <span class="inline-block text-xs font-semibold px-2 py-0.5 rounded-md bg-gray-100 text-gray-700 border border-gray-200">Nonaktif</span>
                        @endif
                    </dd>
                @php 
                    $stokHarian = (float) ($bahanBaku->stok_harian?->jumlah_stok ?? 0);
                    $stokCatering = (float) ($bahanBaku->stok_catering_balance?->jumlah_stok ?? 0);
                    $stok = $stokHarian + $stokCatering;
                @endphp
                <div class="grid grid-cols-3 gap-4 pt-3 mt-3 border-t border-gray-100">
                    <dt class="text-gray-500 font-medium pt-1">Total Stok</dt>
                    <dd class="col-span-2">
                        <span class="font-bold text-gray-900 text-lg">{{ rtrim(rtrim(number_format($stok, 2, ',', '.'), '0'), ',') }}</span> <span class="text-xs text-gray-500">{{ $bahanBaku->satuan->singkatan }}</span>
                        
                        <div class="mt-2 grid grid-cols-2 gap-2 text-xs">
                            <div class="bg-white p-2 rounded border border-gray-100">
                                <span class="block text-gray-400 mb-0.5">Harian</span>
                                <span class="font-semibold text-gray-700">{{ rtrim(rtrim(number_format($stokHarian, 2, ',', '.'), '0'), ',') }}</span>
                            </div>
                            <div class="bg-white p-2 rounded border border-gray-100">
                                <span class="block text-gray-400 mb-0.5">Catering</span>
                                <span class="font-semibold text-gray-700">{{ rtrim(rtrim(number_format($stokCatering, 2, ',', '.'), '0'), ',') }}</span>
                            </div>
                        </div>
                    </dd>
                </div>
            </dl>
        </div>
    </div>

    <!-- Riwayat Penggunaan -->
    <div>
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">Riwayat Penggunaan</h3>
            <a href="{{ route('mutasi-stok.index', ['search' => $bahanBaku->nama_bahan]) }}" class="text-xs font-medium text-[#3B82F6] hover:text-blue-700">Lihat Semua</a>
        </div>
        
        <div class="bg-white border border-gray-100 rounded-xl overflow-hidden">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100 text-xs font-semibold text-gray-500">
                        <th class="px-3 py-2">Tanggal</th>
                        <th class="px-3 py-2">Jenis</th>
                        <th class="px-3 py-2 text-right">Jumlah</th>
                        <th class="px-3 py-2">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($mutasiStoks as $mutasi)
                    <tr class="hover:bg-gray-50/50">
                        <td class="px-3 py-2 text-gray-600 whitespace-nowrap">{{ \Carbon\Carbon::parse($mutasi->tanggal_mutasi)->format('d/m/Y') }}</td>
                        <td class="px-3 py-2">
                            @php
                                $isMasuk = $mutasi->jenis_mutasi_stok_id == 1;
                                $jenisPers = $mutasi->jenis_persediaan === \App\Models\StokBahan::JENIS_CATERING ? 'Catering' : 'Harian';
                            @endphp
                            <span class="text-xs font-medium {{ $isMasuk ? 'text-emerald-600' : 'text-red-600' }}">
                                {{ $isMasuk ? 'Masuk' : 'Keluar' }}
                            </span>
                            <div class="text-[10px] text-gray-400">{{ $jenisPers }}</div>
                        </td>
                        <td class="px-3 py-2 text-right font-medium {{ $isMasuk ? 'text-emerald-600' : 'text-red-600' }}">
                            {{ $isMasuk ? '+' : '-' }}{{ rtrim(rtrim(number_format($mutasi->jumlah, 2, ',', '.'), '0'), ',') }}
                        </td>
                        <td class="px-3 py-2 text-gray-500 text-xs truncate max-w-[120px]" title="{{ $mutasi->catatan }}">
                            {{ $mutasi->catatan ?? '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-3 py-8 text-center text-gray-400 text-xs">Belum ada riwayat penggunaan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
