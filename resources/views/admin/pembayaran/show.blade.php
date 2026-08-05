<div class="space-y-6">
    <!-- Informasi Pesanan -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 bg-slate-50/50">
            <h4 class="text-sm font-bold text-gray-900">Informasi Pesanan</h4>
        </div>
        <div class="p-5">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-gray-500 font-medium">ID Pesanan</p>
                    <p class="text-sm font-bold text-gray-900 font-mono">{{ optional($pembayaran->pesanan)->nomor_pesanan ?? 'DIN-'.optional($pembayaran->pesanan)->id ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 font-medium">Nama Pelanggan</p>
                    <p class="text-sm font-bold text-gray-900">
                        @php
                            $namaOrMeja = '-';
                            if($pembayaran->pesanan) {
                                if(optional($pembayaran->pesanan->jenis_pesanan)->id == 1) { // Dine In
                                    $namaOrMeja = 'Meja ' . (optional($pembayaran->pesanan->meja)->nomor_meja ?? '-');
                                } else {
                                    $namaOrMeja = optional($pembayaran->pesanan->pelanggan)->nama ?? optional($pembayaran->pesanan->jadwal_pesanan)->nama_penerima ?? 'Pelanggan';
                                }
                            }
                        @endphp
                        {{ $namaOrMeja }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 font-medium">Jenis Pesanan</p>
                    <p class="text-sm font-bold text-gray-900">{{ optional(optional($pembayaran->pesanan)->jenis_pesanan)->nama_jenis ?? 'Dine In' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 font-medium">Total Tagihan</p>
                    <p class="text-sm font-bold text-gray-900">Rp{{ number_format(optional($pembayaran->pesanan)->total_tagihan ?? 0, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Riwayat Pembayaran -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 bg-slate-50/50">
            <h4 class="text-sm font-bold text-gray-900">Riwayat Pembayaran</h4>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-gray-500 font-extrabold uppercase text-[10px] tracking-wider border-b border-gray-100">
                        <th class="px-4 py-3">Tahap</th>
                        <th class="px-4 py-3">Nominal</th>
                        <th class="px-4 py-3">Metode</th>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 text-xs">
                    @if($pembayaran->pesanan && $pembayaran->pesanan->pembayaran->count() > 0)
                        @foreach($pembayaran->pesanan->pembayaran as $riwayat)
                            <tr class="hover:bg-slate-50/50 transition-colors {{ $riwayat->id == $pembayaran->id ? 'bg-blue-50/30' : '' }}">
                                <td class="px-4 py-3 font-medium text-gray-900">
                                    {{ optional($riwayat->jenis_pembayaran)->nama_jenis ?? 'Penuh' }}
                                    @if($riwayat->id == $pembayaran->id)
                                        <span class="ml-1 inline-block w-1.5 h-1.5 rounded-full bg-blue-500" title="Pembayaran Terpilih"></span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-bold text-gray-900 whitespace-nowrap">
                                    Rp{{ number_format($riwayat->jumlah_bayar, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ optional($riwayat->metode_pembayaran)->nama_metode ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                                    {{ $riwayat->dibayar_pada ? \Carbon\Carbon::parse($riwayat->dibayar_pada)->format('d-m-Y') : '-' }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @php
                                        $sId = optional($riwayat->status_pembayaran)->id ?? 1;
                                        $c = 'gray';
                                        if($sId == 3 || $sId == 2) $c = 'emerald';
                                        elseif($sId == 1) $c = 'amber';
                                        elseif($sId == 4) $c = 'red';
                                    @endphp
                                    <span class="px-2 py-0.5 bg-{{$c}}-50 text-{{$c}}-700 border border-{{$c}}-200 rounded text-[10px] font-bold uppercase whitespace-nowrap">
                                        {{ optional($riwayat->status_pembayaran)->nama_status ?? 'Menunggu' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">Tidak ada riwayat pembayaran.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bukti Pembayaran -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 bg-slate-50/50">
            <h4 class="text-sm font-bold text-gray-900">Bukti Pembayaran</h4>
        </div>
        <div class="p-5">
            <div class="grid grid-cols-1 gap-4 mb-5">
                <div>
                    <p class="text-xs text-gray-500 font-medium">Foto Bukti Transfer</p>
                    @if($pembayaran->bukti_pembayaran)
                        @if($pembayaran->bukti_pembayaran == 'midtrans_online')
                            <div class="mt-2 w-full py-4 px-3 bg-emerald-50 rounded-xl border border-emerald-100 flex items-center justify-center flex-col text-emerald-600">
                                <x-heroicon-o-check-badge class="w-8 h-8 mb-1" />
                                <span class="text-xs font-bold">Terverifikasi Otomatis (Gateway)</span>
                            </div>
                        @else
                            <div class="mt-2 rounded-xl border border-gray-200 overflow-hidden relative group cursor-pointer" onclick="window.open('{{ asset('storage/' . $pembayaran->bukti_pembayaran) }}', '_blank')">
                                <img src="{{ asset('storage/' . $pembayaran->bukti_pembayaran) }}" alt="Bukti Transfer" class="w-full h-auto object-cover max-h-48 bg-gray-50">
                                <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                    <x-heroicon-o-magnifying-glass-plus class="w-8 h-8 text-white" />
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="mt-2 w-full h-20 bg-gray-50 rounded-xl border border-dashed border-gray-300 flex items-center justify-center text-gray-400">
                            <span class="text-xs font-medium">Tidak ada foto bukti</span>
                        </div>
                    @endif
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-gray-500 font-medium">Transaction ID / Nomor</p>
                    <p class="text-sm font-bold text-gray-900 font-mono">{{ $pembayaran->nomor_pembayaran ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 font-medium">Nomor Referensi</p>
                    <p class="text-sm font-bold text-gray-900 font-mono">{{ $pembayaran->nomor_referensi ?? '-' }}</p>
                </div>
                <div class="col-span-2">
                    <p class="text-xs text-gray-500 font-medium">Waktu Pembayaran</p>
                    <p class="text-sm font-bold text-gray-900">{{ $pembayaran->dibayar_pada ? \Carbon\Carbon::parse($pembayaran->dibayar_pada)->format('d F Y, H:i') . ' WIB' : '-' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Aksi -->
    <div class="grid grid-cols-2 gap-3 pb-8">
        <button type="button" onclick="window.showToast('info', 'Verifikasi segera hadir')" class="w-full py-2.5 px-4 bg-[#0D3024] hover:bg-[#0a1f17] text-white rounded-xl text-sm font-bold shadow-sm transition-colors text-center">
            Verifikasi Pembayaran
        </button>
        <button type="button" onclick="window.showToast('info', 'Cetak segera hadir')" class="w-full py-2.5 px-4 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 rounded-xl text-sm font-bold shadow-sm transition-colors text-center">
            Cetak Bukti Pembayaran
        </button>
        <button type="button" onclick="window.showToast('info', 'Invoice segera hadir')" class="w-full py-2.5 px-4 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 rounded-xl text-sm font-bold shadow-sm transition-colors text-center">
            Kirim Invoice
        </button>
        <button type="button" onclick="window.showToast('info', 'Refund segera hadir')" class="w-full py-2.5 px-4 bg-red-50 hover:bg-red-100 text-red-600 rounded-xl text-sm font-bold transition-colors text-center">
            Refund (Opsional)
        </button>
    </div>
</div>
