@extends('layouts.pos')

@section('title', 'Detail Pesanan Katering #' . $pesanan->nomor_pesanan)

@section('content')
<div class="w-full p-6 max-w-[1200px] mx-auto">
    <div class="w-full p-6 flex justify-between items-center mb-6">
        <x-ui.page-header title="Detail Pesanan Katering #{{ $pesanan->nomor_pesanan }}">
            <x-slot:actions>
                <div class="flex gap-2">
                    <a href="{{ route('admin.pesanan.catering.pdf', $pesanan->id) }}" target="_blank" class="text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-bold transition-colors">
                        <x-heroicon-o-document class="mr-1 w-5 h-5" /> Cetak Rincian (PDF)
                    </a>
                    <a href="{{ route('admin.pesanan.catering.index') }}" class="text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-bold transition-colors">&larr; Kembali</a>
                </div>
            </x-slot:actions>
        </x-ui.page-header>
    </div>

    @php
        $total = (float) $pesanan->total_tagihan;
        $dpBayar = (float) $pesanan->pembayaran->whereIn('status_pembayaran_id', [2, 3])->sum('jumlah_bayar');
        $lunas = (float) $pesanan->pembayaran->where('status_pembayaran_id', 3)->sum('jumlah_bayar');
        $isLunas = $lunas >= $total || $dpBayar >= $total;
        $statusBayarLabel = $isLunas ? 'Lunas' : ($dpBayar > 0 ? 'DP Terbayar' : 'Belum Bayar');
        $detailPesanan = $pesanan->detail_pesanan->first();
        $metodeKirim = $pesanan->pengantaran ? 'Delivery' : 'Pickup';
    @endphp

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            @if(session('kekurangan_stok'))
                <div class="bg-red-50 border border-red-200 p-6 rounded-lg shadow-sm">
                    <h3 class="text-lg font-bold text-red-800 mb-2">Konfirmasi Gagal: Stok Bahan Kurang!</h3>
                    <p class="text-sm text-red-700 mb-4">Stok bahan baku saat ini tidak mencukupi untuk memenuhi BOM pesanan ini. Silakan buat pengadaan bahan terlebih dahulu.</p>
                    <table class="w-full text-sm text-left border text-red-800">
                        <thead class="bg-red-100">
                            <tr>
                                <th class="px-4 py-2 border">Bahan Baku</th>
                                <th class="px-4 py-2 border">Stok Saat Ini</th>
                                <th class="px-4 py-2 border">Kebutuhan</th>
                                <th class="px-4 py-2 border">Kekurangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(session('kekurangan_stok') as $kurang)
                                <tr>
                                    <td class="px-4 py-2 border font-semibold">{{ $kurang['nama_bahan'] }}</td>
                                    <td class="px-4 py-2 border">{{ $kurang['stok_sekarang'] }} {{ $kurang['satuan'] }}</td>
                                    <td class="px-4 py-2 border">{{ $kurang['kebutuhan'] }} {{ $kurang['satuan'] }}</td>
                                    <td class="px-4 py-2 border font-bold text-red-600">{{ $kurang['kurang'] }} {{ $kurang['satuan'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="mt-4">
                        <a href="{{ route('pengadaan.create', ['pesanan_id' => $pesanan->id]) }}" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded text-sm">Buat Pengadaan</a>
                    </div>
                </div>
            @endif

            <div class="grid md:grid-cols-2 gap-6">
                {{-- Info Pemesan --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold mb-4 border-b pb-2">Informasi Pemesan</h3>
                        <div class="space-y-3 text-sm">
                            <div class="grid grid-cols-3"><span class="text-gray-500">Status Pesanan</span> <span class="col-span-2 font-bold">{{ $pesanan->status_pesanan->nama_status ?? '-' }}</span></div>
                            <div class="grid grid-cols-3"><span class="text-gray-500">Status Bayar</span> <span class="col-span-2 font-bold text-blue-600">{{ $statusBayarLabel }}</span></div>
                            <div class="grid grid-cols-3"><span class="text-gray-500">Nama Pemesan</span> <span class="col-span-2">{{ optional($pesanan->pelanggan)->nama ?? $pesanan->jadwal_pesanan->nama_penerima }}</span></div>
                            <div class="grid grid-cols-3"><span class="text-gray-500">Kontak</span> <span class="col-span-2">{{ optional($pesanan->pelanggan)->nomor_telepon ?? $pesanan->jadwal_pesanan->nomor_telepon_penerima ?? '-' }}</span></div>
                            <div class="grid grid-cols-3"><span class="text-gray-500">Metode Pengiriman</span> <span class="col-span-2 capitalize">{{ $metodeKirim }}</span></div>
                            <div class="grid grid-cols-3"><span class="text-gray-500">Alamat / Lokasi</span> <span class="col-span-2">{{ $pesanan->jadwal_pesanan->alamat_pengantaran ?? '-' }}</span></div>
                            @if($pesanan->pengantaran)
                                <div class="grid grid-cols-3"><span class="text-gray-500">Jadwal Pengantaran</span> <span class="col-span-2">{{ \Carbon\Carbon::parse($pesanan->pengantaran->jadwal_pengantaran)->format('d M Y H:i') }}</span></div>
                            @endif
                            <div class="grid grid-cols-3"><span class="text-gray-500">Tanggal Acara</span> <span class="col-span-2">{{ \Carbon\Carbon::parse($pesanan->jadwal_pesanan->tanggal_acara)->translatedFormat('l, d M Y') }}</span></div>
                            <div class="grid grid-cols-3"><span class="text-gray-500">Catatan</span> <span class="col-span-2">{{ $pesanan->catatan ?: '-' }}</span></div>
                        </div>
                    </div>
                </div>

                {{-- Info Paket --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold mb-4 border-b pb-2">Informasi Paket</h3>
                        <div class="space-y-3 text-sm mb-4">
                            <div class="grid grid-cols-3"><span class="text-gray-500">Paket</span> <span class="col-span-2 font-semibold">{{ $detailPesanan->menu->nama_menu ?? '-' }}</span></div>
                            <div class="grid grid-cols-3"><span class="text-gray-500">Jumlah Porsi</span> <span class="col-span-2">{{ $detailPesanan->jumlah ?? 0 }}</span></div>
                            <div class="grid grid-cols-3"><span class="text-gray-500">Total Tagihan</span> <span class="col-span-2 font-bold">Rp {{ number_format($total, 0, ',', '.') }}</span></div>
                            @if($dpBayar > 0)
                            <div class="grid grid-cols-3"><span class="text-gray-500">Telah Dibayar</span> <span class="col-span-2 text-emerald-700 font-semibold">- Rp {{ number_format($dpBayar, 0, ',', '.') }}</span></div>
                            @endif
                            @if(!$isLunas)
                            <div class="grid grid-cols-3"><span class="text-gray-500">Sisa Tagihan</span> <span class="col-span-2 font-bold text-amber-700">Rp {{ number_format(max(0, $total - $dpBayar), 0, ',', '.') }}</span></div>
                            @endif
                        </div>

                        @php $pilihan = $detailPesanan->pilihan_pesanan_catering ?? collect(); @endphp
                        @if($pilihan->count() > 0)
                            <h4 class="font-medium text-gray-700 mb-2">Menu Komponen Terpilih:</h4>
                            <ul class="list-disc list-inside text-sm text-gray-600 mb-4">
                                @foreach($pilihan as $pil)
                                    <li>{{ $pil->komponen_paket->nama_komponen ?? '-' }}: <strong>{{ $pil->pilihan_komponen_paket->nama_pilihan ?? '-' }}</strong></li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ── TABEL ANALISIS STOK BAHAN BAKU CATERING INI ── --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center border-b pb-3 mb-4 gap-2">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                                <x-heroicon-o-cube class="w-5 h-5 text-emerald-700" />
                                Rincian & Analisis Stok Bahan Baku Katering Ini
                            </h3>
                            <p class="text-sm text-gray-500 font-medium mt-1">Kebutuhan resep untuk {{ $detailPesanan->jumlah ?? 0 }} porsi pesanan catering ini</p>
                        </div>
                        @if(!in_array($pesanan->status_pesanan_id, [1, 6]))
                        <a href="{{ route('pengadaan.create', ['pesanan_id' => $pesanan->id]) }}" class="inline-flex items-center gap-2 bg-[#0D3024] hover:bg-[#0a1f17] text-white text-xs font-bold px-4 py-2 rounded-xl transition-all shadow-xs">
                            <x-heroicon-o-plus class="w-4 h-4" />
                            Buat Pengadaan Katering
                        </a>
                        @endif
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm border-collapse">
                            <thead>
                                <tr class="bg-gray-50 text-gray-500 text-sm uppercase tracking-wider border-b">
                                    <th class="px-4 py-3 font-semibold">Bahan Baku</th>
                                    <th class="px-4 py-3 font-semibold">Stok Resto Saat Ini</th>
                                    <th class="px-4 py-3 font-semibold">Total Kebutuhan Acara</th>
                                    <th class="px-4 py-3 font-semibold">Status Ketersediaan</th>
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
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800 border border-red-200">
                                                    Kurang {{ number_format($kurang, 2, ',', '.') }} {{ $item['satuan'] }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                    ✓ Stok Cukup
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-6 text-center text-gray-400 italic">
                                            Belum ada resep bahan baku yang terdaftar pada menu paket terpilih.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Bukti Pembayaran & Aksi --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4 border-b pb-2">Riwayat Pembayaran</h3>

                    @if($pesanan->pembayaran->isEmpty())
                        <p class="text-sm text-gray-500 italic">Belum ada pembayaran yang diunggah pelanggan.</p>
                    @else
                        <div class="grid md:grid-cols-2 gap-4">
                            @foreach($pesanan->pembayaran as $pemb)
                                <div class="border rounded-lg p-4 {{ $pemb->status_pembayaran_id === 3 ? 'bg-green-50/40 border-green-200/60' : 'bg-yellow-50/40 border-yellow-200/60' }}">
                                    <div class="flex justify-between mb-2">
                                        <span class="font-bold uppercase text-sm">{{ $pemb->jenis_pembayaran->nama_jenis ?? '-' }}</span>
                                        <span class="text-xs px-2 py-1 rounded {{ $pemb->status_pembayaran_id === 3 ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ $pemb->status_pembayaran->nama_status ?? '-' }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500 mb-1">No: {{ $pemb->nomor_pembayaran }}</p>
                                    <p class="text-xs text-gray-500 mb-1">Metode: {{ $pemb->metode_pembayaran->nama_metode ?? '-' }}</p>
                                    <p class="text-sm font-bold text-gray-800 mb-2">Rp {{ number_format($pemb->jumlah_bayar, 0, ',', '.') }}</p>
                                    <p class="text-xs text-gray-500 mb-3">Diunggah pada: {{ $pemb->dibuat_pada ? \Carbon\Carbon::parse($pemb->dibuat_pada)->format('d M Y H:i') : '-' }}</p>

                                    @if($pemb->bukti_pembayaran && $pemb->bukti_pembayaran !== 'midtrans_online')
                                        <a href="{{ asset('storage/' . $pemb->bukti_pembayaran) }}" target="_blank" class="block w-full text-center bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 rounded text-sm mb-3">Lihat File Bukti</a>
                                    @else
                                        <div class="w-full text-center bg-gray-100 text-gray-500 py-2 rounded text-sm mb-3">Pembayaran Online / Verifikasi Otomatis</div>
                                    @endif

                                    @if($pemb->status_pembayaran_id === 1)
                                        <form action="{{ route('admin.bukti.verifikasi-dp', $pemb->id) }}" method="POST" class="mt-2">
                                            @csrf
                                            @method('PATCH')
                                            <input type="text" name="catatan_admin" placeholder="Catatan opsional..." class="w-full text-sm border-gray-300 rounded-xl mb-2">
                                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">
                                                Verifikasi Pembayaran
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- INFO: Sisa Pelunasan --}}
                    @if(!$isLunas && $dpBayar > 0)
                        <div class="mt-6 bg-yellow-50 p-4 rounded-xl border border-yellow-200">
                            <h4 class="text-yellow-800 font-bold mb-1"><x-heroicon-o-clock class="w-4 h-4 mr-2" />Menunggu Pelunasan dari Konsumen</h4>
                            <p class="text-yellow-700 text-sm">Pesanan ini sudah memiliki pembayaran. Konsumen perlu melunasi sisa tagihan sebelum bisa diselesaikan.</p>
                            <p class="text-yellow-800 font-bold mt-2">Sisa Tagihan: Rp {{ number_format(max(0, $total - $dpBayar), 0, ',', '.') }}</p>
                        </div>
                    @endif

                    @if($pesanan->status_pesanan_id === 6 && $pesanan->catatan && str_contains($pesanan->catatan, '[BATAL:'))
                        <div class="mt-6 bg-red-50 p-4 rounded-xl border border-red-100">
                            <h4 class="text-red-800 font-bold mb-1"><x-heroicon-o-no-symbol class="mr-2 w-5 h-5" />Alasan Pembatalan:</h4>
                            <p class="text-red-700 text-sm">{{ Str::after($pesanan->catatan, '[BATAL:') }}</p>
                        </div>
                    @endif

                    {{-- Tombol Aksi Status --}}
                    @if(!in_array($pesanan->status_pesanan_id, [5, 6]))
                        <div class="mt-8 border-t pt-6" x-data="{ showBatalModal: false }">
                            <div class="flex flex-wrap gap-3 justify-center">

                                {{-- Konfirmasi (dari Menunggu) --}}
                                @if($pesanan->status_pesanan_id === 1)
                                <form id="form-konfirmasi-catering" action="{{ route('admin.pesanan.catering.konfirmasi', $pesanan->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="button" onclick="window.confirmDialog({ title: 'Konfirmasi Pesanan', name: '{{ $pesanan->nomor_pesanan }}', message: 'Anda yakin ingin mengonfirmasi pesanan ini? Pastikan DP sudah terbayar & terverifikasi.', formId: 'form-konfirmasi-catering', confirmText: 'Konfirmasi', cancelText: 'Batal', type: 'warning' })" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-8 rounded-lg shadow">
                                        <x-heroicon-o-check class="mr-2 w-5 h-5" />Konfirmasi Pesanan
                                    </button>
                                </form>
                                @endif

                                {{-- Mulai Diproses Dapur (dari Dikonfirmasi) --}}
                                @if($pesanan->status_pesanan_id === 2)
                                <form id="form-proses-catering" action="{{ route('admin.pesanan.catering.update-status', $pesanan->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="3">
                                    <button type="button" onclick="window.confirmDialog({ title: 'Mulai Proses Dapur', name: '{{ $pesanan->nomor_pesanan }}', message: 'Mulai proses dapur untuk pesanan ini?', formId: 'form-proses-catering', confirmText: 'Proses', cancelText: 'Batal', type: 'warning' })" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded-lg shadow">
                                        <x-heroicon-o-sparkles class="mr-2 w-5 h-5" />Mulai Proses Dapur
                                    </button>
                                </form>
                                @endif

                                {{-- Tandai Siap (dari Diproses) --}}
                                @if($pesanan->status_pesanan_id === 3)
                                <form id="form-siap-catering" action="{{ route('admin.pesanan.catering.update-status', $pesanan->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="4">
                                    <button type="button" onclick="window.confirmDialog({ title: 'Tandai Siap', name: '{{ $pesanan->nomor_pesanan }}', message: 'Tandai pesanan sebagai Siap?', formId: 'form-siap-catering', confirmText: 'Siap', cancelText: 'Batal', type: 'warning' })" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-8 rounded-lg shadow">
                                        <x-heroicon-o-cube class="mr-2 w-5 h-5" />Tandai Siap
                                    </button>
                                </form>
                                @endif

                                {{-- Tandai Selesai (dari Siap) --}}
                                @if($pesanan->status_pesanan_id === 4)
                                <form id="form-selesai-catering" action="{{ route('admin.pesanan.catering.update-status', $pesanan->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="5">
                                    <button type="button" onclick="window.confirmDialog({ title: 'Tandai Selesai', name: '{{ $pesanan->nomor_pesanan }}', message: 'Tandai pesanan sebagai Selesai? Stok bahan akan dipotong otomatis.', formId: 'form-selesai-catering', confirmText: 'Selesai', cancelText: 'Batal', type: 'warning' })" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-8 rounded-lg shadow">
                                        <x-heroicon-o-flag class="mr-2 w-5 h-5" />Tandai Selesai
                                    </button>
                                </form>
                                @endif

                                {{-- Batalkan (semua status kecuali selesai) --}}
                                <button @click="showBatalModal = true" type="button" class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-8 rounded-lg shadow">
                                    <x-heroicon-o-x-mark class="mr-2 w-5 h-5" />Batalkan Pesanan
                                </button>
                            </div>

                            {{-- Modal Pembatalan --}}
                            <div x-show="showBatalModal" style="display:none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                    <div x-show="showBatalModal" x-transition.opacity class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showBatalModal = false"></div>
                                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                                    <div x-show="showBatalModal" x-transition class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                        <form action="{{ route('admin.pesanan.catering.update-status', $pesanan->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="6">
                                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                                <div class="sm:flex sm:items-start">
                                                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                                        <x-heroicon-o-exclamation-triangle class="text-red-600 w-5 h-5" />
                                                    </div>
                                                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Batalkan Pesanan</h3>
                                                        <div class="mt-2">
                                                            <p class="text-sm text-gray-500 mb-2">Masukkan alasan pembatalan dan kebijakan refund (jika ada).</p>
                                                            <textarea name="alasan_batal" rows="3" required class="w-full border-gray-300 rounded-xl shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm" placeholder="Contoh: Dibatalkan pelanggan H-1, DP Hangus 50%"></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                                <button type="submit" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 sm:ml-3 sm:w-auto sm:text-sm">Konfirmasi Batal</button>
                                                <button type="button" @click="showBatalModal = false" class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Kembali</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
