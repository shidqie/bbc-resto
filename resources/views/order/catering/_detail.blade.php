@php
    $total = (float) $pesanan->total_tagihan;
    $dpBayar = (float) $pesanan->pembayaran->where('status_verifikasi', 'diterima')->sum('jumlah_dibayar');
    $lunas = (float) $pesanan->pembayaran->where('status_verifikasi', 'diterima')->sum('jumlah_dibayar');
    $isLunas = $lunas >= $total || $dpBayar >= $total;
    $statusBayarLabel = $isLunas ? 'Lunas' : ($dpBayar > 0 ? 'DP Terbayar' : 'Belum Bayar');
    $detailPesanan = $pesanan->detail_pesanan->first();
    $metodeKirim = $pesanan->pengantaran ? 'Delivery' : 'Pickup';
@endphp

<div class="space-y-6">
    {{-- Actions --}}
    <div class="flex justify-between items-center bg-gray-50 p-4 rounded-xl border border-gray-100">
        <h2 class="text-lg font-bold text-gray-900">Pesanan #{{ $pesanan->nomor_pesanan }}</h2>
        <a href="{{ route('admin.pesanan.catering.pdf', $pesanan->id) }}" target="_blank" class="inline-flex items-center text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-bold transition-colors shadow-sm">
            <x-heroicon-o-document class="mr-1 w-4 h-4" /> Cetak PDF
        </a>
    </div>

    {{-- Informasi Pemesan & Paket (Tabel) --}}
    <div>
        <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-3">Informasi Pesanan</h3>
        <div class="overflow-x-auto border border-gray-200 rounded-xl">
            <table class="w-full text-sm text-left">
                <tbody class="divide-y divide-gray-100">
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 font-medium text-gray-500 w-1/3 bg-gray-50/50">Status Pesanan</td>
                        <td class="px-4 py-3 font-bold text-gray-900">{{ $pesanan->status_pesanan->nama_status ?? '-' }}</td>
                    </tr>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 font-medium text-gray-500 w-1/3 bg-gray-50/50">Status Bayar</td>
                        <td class="px-4 py-3 font-bold text-blue-600">{{ $statusBayarLabel }}</td>
                    </tr>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 font-medium text-gray-500 w-1/3 bg-gray-50/50">Nama Pemesan</td>
                        <td class="px-4 py-3 text-gray-900 font-medium">{{ optional($pesanan->pelanggan)->nama ?? $pesanan->jadwal_pesanan->nama_penerima }}</td>
                    </tr>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 font-medium text-gray-500 w-1/3 bg-gray-50/50">Kontak</td>
                        <td class="px-4 py-3 text-gray-900">{{ optional($pesanan->pelanggan)->nomor_telepon ?? $pesanan->jadwal_pesanan->nomor_telepon_penerima ?? '-' }}</td>
                    </tr>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 font-medium text-gray-500 w-1/3 bg-gray-50/50">Tanggal Acara</td>
                        <td class="px-4 py-3 text-gray-900">{{ \Carbon\Carbon::parse($pesanan->jadwal_pesanan->tanggal_acara)->translatedFormat('l, d M Y') }}</td>
                    </tr>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 font-medium text-gray-500 w-1/3 bg-gray-50/50">Metode & Alamat</td>
                        <td class="px-4 py-3 text-gray-900">
                            <span class="capitalize font-semibold">{{ $metodeKirim }}</span><br>
                            <span class="text-xs text-gray-500">{{ $pesanan->jadwal_pesanan->alamat_pengantaran ?? '-' }}</span>
                            @if($pesanan->pengantaran)
                                <div class="mt-1 text-xs text-gray-500">Jadwal Kirim: {{ \Carbon\Carbon::parse($pesanan->pengantaran->jadwal_pengantaran)->format('d M Y H:i') }}</div>
                            @endif
                        </td>
                    </tr>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 font-medium text-gray-500 w-1/3 bg-gray-50/50">Paket & Porsi</td>
                        <td class="px-4 py-3 text-gray-900">
                            <span class="font-bold">{{ $detailPesanan->menu->nama_menu ?? '-' }}</span><br>
                            <span class="text-gray-600">{{ $detailPesanan->jumlah ?? 0 }} Porsi</span>
                        </td>
                    </tr>
                    @php $pilihan = $detailPesanan->pilihan_pesanan_catering ?? collect(); @endphp
                    @if($pilihan->count() > 0)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 font-medium text-gray-500 w-1/3 bg-gray-50/50 align-top">Menu Komponen</td>
                        <td class="px-4 py-3 text-gray-900">
                            <ul class="list-disc list-inside text-xs text-gray-600 space-y-1">
                                @foreach($pilihan as $pil)
                                    <li>{{ $pil->komponen_paket->nama_komponen ?? '-' }}: <strong class="text-gray-800">{{ $pil->pilihan_komponen_paket->nama_pilihan ?? '-' }}</strong></li>
                                @endforeach
                            </ul>
                        </td>
                    </tr>
                    @endif
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 font-medium text-gray-500 w-1/3 bg-gray-50/50">Total Tagihan</td>
                        <td class="px-4 py-3 text-gray-900 font-bold tabular-nums">Rp {{ number_format($total, 0, ',', '.') }}</td>
                    </tr>
                    @if($dpBayar > 0)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 font-medium text-gray-500 w-1/3 bg-gray-50/50">Telah Dibayar</td>
                        <td class="px-4 py-3 text-emerald-600 font-bold tabular-nums">- Rp {{ number_format($dpBayar, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    @if(!$isLunas)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 font-medium text-gray-500 w-1/3 bg-gray-50/50">Sisa Tagihan</td>
                        <td class="px-4 py-3 text-amber-700 font-bold tabular-nums">Rp {{ number_format(max(0, $total - $dpBayar), 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 font-medium text-gray-500 w-1/3 bg-gray-50/50">Catatan</td>
                        <td class="px-4 py-3 text-gray-900">{{ $pesanan->catatan ?: '-' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Kebutuhan Bahan Baku --}}
    <div>
        <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-3">Rincian Stok Bahan Baku</h3>
        <div class="overflow-x-auto border border-gray-200 rounded-xl">
            <table class="w-full text-left text-sm border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider border-b">
                        <th class="px-4 py-3 font-semibold">Bahan Baku</th>
                        <th class="px-4 py-3 font-semibold">Stok Saat Ini</th>
                        <th class="px-4 py-3 font-semibold">Kebutuhan</th>
                        <th class="px-4 py-3 font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($kebutuhanBahan as $item)
                        @php $kurang = max(0, $item['total_kebutuhan'] - $item['stok_sekarang']); @endphp
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-4 py-3 font-bold text-gray-900">{{ $item['nama_bahan'] }}</td>
                            <td class="px-4 py-3 font-medium text-gray-700">{{ number_format($item['stok_sekarang'], 2, ',', '.') }} {{ $item['satuan'] }}</td>
                            <td class="px-4 py-3 font-bold text-blue-700">{{ number_format($item['total_kebutuhan'], 2, ',', '.') }} {{ $item['satuan'] }}</td>
                            <td class="px-4 py-3">
                                @if($kurang > 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-800 border border-red-200 uppercase tracking-wider">
                                        Kurang {{ number_format($kurang, 2, ',', '.') }} {{ $item['satuan'] }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 uppercase tracking-wider">
                                        Cukup
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-gray-400 italic">
                                Belum ada resep bahan baku.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(!in_array($pesanan->status_pesanan_id, [1, 6]))
        <div class="mt-3 text-right">
            <a href="{{ route('pengadaan.create', ['pesanan_id' => $pesanan->id]) }}" class="inline-flex items-center gap-2 bg-[#0D3024] hover:bg-[#0a1f17] text-white text-xs font-bold px-4 py-2 rounded-lg transition-all shadow-sm">
                <x-heroicon-o-plus class="w-4 h-4" />
                Buat Pengadaan
            </a>
        </div>
        @endif
    </div>

    {{-- Riwayat Pembayaran --}}
    <div>
        <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-3">Riwayat Pembayaran</h3>
        @if($pesanan->pembayaran->isEmpty())
            <p class="text-sm text-gray-500 italic px-2">Belum ada pembayaran.</p>
        @else
            <div class="space-y-3">
                @foreach($pesanan->pembayaran as $pemb)
                    <div class="border rounded-xl p-4 flex flex-col sm:flex-row justify-between gap-4 {{ $pemb->status_verifikasi === 'diterima' ? 'bg-green-50/40 border-green-200/60' : 'bg-yellow-50/40 border-yellow-200/60' }}">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-bold text-gray-900 text-sm">{{ $pemb->metode_pembayaran ?? '-' }}</span>
                                <span class="text-[10px] uppercase tracking-wider font-bold px-2 py-0.5 rounded-full {{ $pemb->status_verifikasi === 'diterima' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ $pemb->status_pembayaran->nama_status ?? '-' }}
                                </span>
                            </div>
                            <div class="text-xs text-gray-500">{{ $pemb->dibuat_pada ? \Carbon\Carbon::parse($pemb->dibuat_pada)->format('d M Y H:i') : '-' }}</div>
                            @if($pemb->bukti_pembayaran && $pemb->bukti_pembayaran !== 'midtrans_online')
                                <a href="{{ asset('storage/' . $pemb->bukti_pembayaran) }}" target="_blank" class="inline-block mt-2 text-xs font-medium text-blue-600 hover:underline">Lihat Bukti &rarr;</a>
                            @else
                                <div class="mt-2 text-xs text-gray-400 italic">Online / Otomatis</div>
                            @endif
                        </div>
                        <div class="text-left sm:text-right flex flex-col justify-between">
                            <span class="text-base font-bold text-gray-900 tabular-nums">Rp {{ number_format($pemb->jumlah_bayar, 0, ',', '.') }}</span>
                            @if($pemb->status_verifikasi === 'menunggu_verifikasi')
                                <form action="{{ route('admin.bukti.verifikasi-dp', $pemb->id) }}" method="POST" class="mt-2">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="text-[11px] bg-blue-600 hover:bg-blue-700 text-white font-bold py-1.5 px-3 rounded-lg shadow-sm">
                                        Verifikasi
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Tombol Aksi Status --}}
    @if(!in_array($pesanan->status_pesanan_id, [5, 6]))
        <div class="mt-4 pt-6 border-t border-gray-200" x-data="{ showBatalModal: false }">
            <div class="flex flex-col sm:flex-row flex-wrap gap-3">
                {{-- Konfirmasi --}}
                @if($pesanan->status_pesanan_id === 7)
                <form id="form-konfirmasi-catering" action="{{ route('admin.pesanan.catering.update-status', $pesanan->id) }}" method="POST" class="w-full sm:w-auto">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="8">
                    <button type="button" onclick="window.confirmDialog({ title: 'Konfirmasi Pesanan', name: '{{ $pesanan->nomor_pesanan }}', message: 'Anda yakin ingin mengonfirmasi pesanan ini? Pastikan DP sudah terbayar & terverifikasi.', formId: 'form-konfirmasi-catering', confirmText: 'Konfirmasi', cancelText: 'Batal', type: 'warning' })" class="w-full bg-green-600 hover:bg-green-700 text-white text-sm font-bold py-2.5 px-6 rounded-lg shadow-sm transition-colors flex items-center justify-center">
                        <x-heroicon-o-check class="mr-1.5 w-4 h-4" />Konfirmasi Pesanan
                    </button>
                </form>
                @endif
                {{-- Proses Pengadaan --}}
                @if($pesanan->status_pesanan_id === 8)
                <form id="form-pengadaan-catering" action="{{ route('admin.pesanan.catering.update-status', $pesanan->id) }}" method="POST" class="w-full sm:w-auto">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="9">
                    <button type="button" onclick="window.confirmDialog({ title: 'Mulai Pengadaan', name: '{{ $pesanan->nomor_pesanan }}', message: 'Mulai proses pengadaan bahan untuk pesanan ini?', formId: 'form-pengadaan-catering', confirmText: 'Proses', cancelText: 'Batal', type: 'warning' })" class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold py-2.5 px-6 rounded-lg shadow-sm transition-colors flex items-center justify-center">
                        <x-heroicon-o-shopping-cart class="mr-1.5 w-4 h-4" />Mulai Pengadaan
                    </button>
                </form>
                @endif
                {{-- Bahan Diterima --}}
                @if($pesanan->status_pesanan_id === 9)
                <form id="form-bahan-diterima-catering" action="{{ route('admin.pesanan.catering.update-status', $pesanan->id) }}" method="POST" class="w-full sm:w-auto">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="10">
                    <button type="button" onclick="window.confirmDialog({ title: 'Bahan Diterima', name: '{{ $pesanan->nomor_pesanan }}', message: 'Tandai bahan baku telah diterima?', formId: 'form-bahan-diterima-catering', confirmText: 'Tandai', cancelText: 'Batal', type: 'warning' })" class="w-full bg-teal-600 hover:bg-teal-700 text-white text-sm font-bold py-2.5 px-6 rounded-lg shadow-sm transition-colors flex items-center justify-center">
                        <x-heroicon-o-inbox-arrow-down class="mr-1.5 w-4 h-4" />Bahan Diterima
                    </button>
                </form>
                @endif
                {{-- Mulai Produksi --}}
                @if(in_array($pesanan->status_pesanan_id, [8, 10]))
                <form id="form-produksi-catering" action="{{ route('admin.pesanan.catering.update-status', $pesanan->id) }}" method="POST" class="w-full sm:w-auto">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="11">
                    <button type="button" onclick="window.confirmDialog({ title: 'Mulai Produksi', name: '{{ $pesanan->nomor_pesanan }}', message: 'Mulai proses dapur? Stok bahan akan dipotong.', formId: 'form-produksi-catering', confirmText: 'Mulai Produksi', cancelText: 'Batal', type: 'warning' })" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold py-2.5 px-6 rounded-lg shadow-sm transition-colors flex items-center justify-center">
                        <x-heroicon-o-sparkles class="mr-1.5 w-4 h-4" />Mulai Produksi
                    </button>
                </form>
                @endif
                {{-- Produksi Selesai --}}
                @if($pesanan->status_pesanan_id === 11)
                <form id="form-produksi-selesai-catering" action="{{ route('admin.pesanan.catering.update-status', $pesanan->id) }}" method="POST" class="w-full sm:w-auto">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="12">
                    <button type="button" onclick="window.confirmDialog({ title: 'Produksi Selesai', name: '{{ $pesanan->nomor_pesanan }}', message: 'Tandai produksi selesai?', formId: 'form-produksi-selesai-catering', confirmText: 'Selesai', cancelText: 'Batal', type: 'warning' })" class="w-full bg-purple-600 hover:bg-purple-700 text-white text-sm font-bold py-2.5 px-6 rounded-lg shadow-sm transition-colors flex items-center justify-center">
                        <x-heroicon-o-cube class="mr-1.5 w-4 h-4" />Produksi Selesai
                    </button>
                </form>
                @endif
                {{-- Tandai Selesai Total (Ambil Sendiri) --}}
                @if($pesanan->status_pesanan_id === 12 && $pesanan->metode_pengiriman === 'ambil_sendiri')
                <form id="form-selesai-catering" action="{{ route('admin.pesanan.catering.update-status', $pesanan->id) }}" method="POST" class="w-full sm:w-auto">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="5">
                    <button type="button" onclick="window.confirmDialog({ title: 'Selesai', name: '{{ $pesanan->nomor_pesanan }}', message: 'Tandai pesanan selesai (sudah diambil)?', formId: 'form-selesai-catering', confirmText: 'Selesai', cancelText: 'Batal', type: 'warning' })" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold py-2.5 px-6 rounded-lg shadow-sm transition-colors flex items-center justify-center">
                        <x-heroicon-o-flag class="mr-1.5 w-4 h-4" />Tandai Selesai
                    </button>
                </form>
                @endif
                {{-- Batalkan --}}
                <button @click="showBatalModal = true" type="button" class="w-full sm:w-auto bg-red-50 hover:bg-red-100 text-red-600 text-sm font-bold py-2.5 px-6 rounded-lg transition-colors flex items-center justify-center">
                    <x-heroicon-o-x-mark class="mr-1.5 w-4 h-4" />Batalkan
                </button>
            </div>

            {{-- Modal Batal --}}
            <div x-show="showBatalModal" style="display:none;" class="fixed inset-0 z-[60] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-center justify-center min-h-screen p-4 text-center">
                    <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" @click="showBatalModal = false"></div>
                    <div x-show="showBatalModal" x-transition class="relative bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all w-full max-w-md">
                        <form action="{{ route('admin.pesanan.catering.update-status', $pesanan->id) }}" method="POST">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="6">
                            <div class="p-6">
                                <h3 class="text-lg font-bold text-gray-900 mb-2">Batalkan Pesanan</h3>
                                <p class="text-sm text-gray-500 mb-4">Masukkan alasan pembatalan. Aksi ini tidak dapat dibatalkan.</p>
                                <textarea name="alasan_batal" rows="3" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm" placeholder="Contoh: Dibatalkan pelanggan..."></textarea>
                            </div>
                            <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3">
                                <button type="button" @click="showBatalModal = false" class="px-4 py-2 bg-white text-gray-700 font-medium rounded-lg border border-gray-300 hover:bg-gray-50">Kembali</button>
                                <button type="submit" class="px-4 py-2 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 shadow-sm">Konfirmasi Batal</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
