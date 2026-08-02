@extends('layouts.pos')

@section('title', 'Detail Pesanan Nasi Box #' . $pesanan->nomor_pesanan)

@section('content')
<div class="w-full p-6 max-w-[1200px] mx-auto">
    <div class="w-full p-6 flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Detail Pesanan Nasi Box #{{ $pesanan->nomor_pesanan }}</h1>
        <div class="flex gap-2">
            <a href="{{ route('admin.pesanan.nasibox.pdf', $pesanan->id) }}" target="_blank" class="text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-2xl font-bold transition-colors">
                <x-heroicon-o-document class="mr-1 w-5 h-5" /> Cetak Rincian (PDF)
            </a>
            <a href="{{ route('admin.pesanan.nasibox.index') }}" class="text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-2xl font-bold transition-colors">&larr; Kembali</a>
        </div>
    </div>

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
                <div class="bg-red-50 border border-red-200 p-6 rounded-2xl shadow-sm">
                    <h3 class="text-lg font-bold text-red-800 mb-2">Konfirmasi Gagal: Stok Bahan Kurang!</h3>
                    <p class="text-sm text-red-700 mb-4">Stok bahan baku saat ini tidak mencukupi untuk memenuhi pesanan ini. Silakan buat pengadaan bahan terlebih dahulu.</p>
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
                        <a href="{{ route('pengadaan.create', ['pesanan_id' => $pesanan->id]) }}" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded text-sm">Buat Pengadaan Nasi Box</a>
                    </div>
                </div>
            @endif

            <div class="grid md:grid-cols-2 gap-6">
                {{-- Info Pemesan --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold mb-4 border-b pb-2">Informasi Pemesan</h3>
                        <div class="space-y-3 text-sm">
                            <div class="grid grid-cols-3"><span class="text-gray-500">Status Pesanan</span> <span class="col-span-2 font-bold">{{ $pesanan->status_pesanan->nama_status ?? 'Menunggu Konfirmasi' }}</span></div>
                            <div class="grid grid-cols-3"><span class="text-gray-500">Status Bayar</span> <span class="col-span-2 font-bold text-orange-600">{{ $pesanan->pembayaran->first()?->status_pembayaran?->nama_status ?? 'Menunggu Pembayaran' }}</span></div>
                            <div class="grid grid-cols-3"><span class="text-gray-500">Nama Pemesan</span> <span class="col-span-2">{{ $pesanan->jadwal_pesanan->nama_penerima ?? '-' }}</span></div>
                            <div class="grid grid-cols-3"><span class="text-gray-500">Kontak</span> <span class="col-span-2">{{ $pesanan->jadwal_pesanan->nomor_telepon_penerima ?? '-' }}</span></div>
                            <div class="grid grid-cols-3"><span class="text-gray-500">Metode Pengiriman</span> <span class="col-span-2 capitalize">{{ $pesanan->pengantaran ? 'Delivery' : 'Pickup' }}</span></div>
                            <div class="grid grid-cols-3"><span class="text-gray-500">Alamat / Lokasi</span> <span class="col-span-2">{{ $pesanan->jadwal_pesanan->alamat_pengantaran ?? $pesanan->catatan }}</span></div>
                            @if($pesanan->pengantaran)
                                <div class="grid grid-cols-3"><span class="text-gray-500">Ongkos Kirim</span> <span class="col-span-2">Rp {{ number_format($pesanan->pengantaran->biaya_pengantaran, 0, ',', '.') }}</span></div>
                            @endif
                            <div class="grid grid-cols-3"><span class="text-gray-500">Tanggal Acara</span> <span class="col-span-2">{{ \Carbon\Carbon::parse($pesanan->jadwal_pesanan->tanggal_acara)->format('d M Y') }}</span></div>
                            <div class="grid grid-cols-3"><span class="text-gray-500">Catatan</span> <span class="col-span-2">{{ $pesanan->catatan ?: '-' }}</span></div>
                        </div>
                    </div>
                </div>

                {{-- Info Nasi Box --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold mb-4 border-b pb-2">Informasi Pesanan</h3>
                        <div class="space-y-3 text-sm mb-4">
                            <div class="grid grid-cols-3"><span class="text-gray-500">Varian Nasi Box</span> <span class="col-span-2 font-semibold">{{ $pesanan->detail_pesanan->first()->menu->nama_menu ?? '-' }}</span></div>
                            <div class="grid grid-cols-3"><span class="text-gray-500">Jumlah Box</span> <span class="col-span-2">{{ $pesanan->detail_pesanan->first()->jumlah ?? 0 }}</span></div>
                            <div class="grid grid-cols-3"><span class="text-gray-500">Harga per Box</span> <span class="col-span-2">Rp {{ number_format($pesanan->detail_pesanan->first()->harga_satuan ?? 0, 0, ',', '.') }}</span></div>
                            <div class="grid grid-cols-3"><span class="text-gray-500">Total Tagihan</span> <span class="col-span-2 font-bold text-lg text-blue-600">Rp {{ number_format($pesanan->total_tagihan, 0, ',', '.') }}</span></div>
                        </div>
                        
                        <h4 class="font-medium text-gray-700 mb-2">Pilihan Lauk/Sayur:</h4>
                        @if($pesanan->detail_pesanan && $pesanan->detail_pesanan->count() > 0)
                            <ul class="list-disc list-inside text-sm text-gray-600 space-y-1">
                                @foreach($pesanan->detail_pesanan as $detail)
                                    <li>
                                        <span class="font-semibold">{{ $detail->menu->nama_menu ?? 'Komponen' }}:</span> 
                                        {{ $detail->jumlah }} x Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-sm text-gray-500 italic">Tidak ada detail komponen tercatat (Format lama).</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Bukti Pembayaran & Aksi --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4 border-b pb-2">Bukti Pembayaran</h3>

                    @php $bayar = $pesanan->pembayaran->first(); @endphp
                    @if($bayar)
                        <div class="border rounded-2xl p-4 flex flex-wrap items-center gap-4">
                            <div class="flex-1 min-w-[200px]">
                                <p class="font-bold uppercase text-sm">{{ $bayar->metode_pembayaran->nama_metode ?? 'Pembayaran' }}</p>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $bayar->nomor_pembayaran }} •
                                    {{ $bayar->dibayar_pada ? \Carbon\Carbon::parse($bayar->dibayar_pada)->format('d M Y H:i') : 'Belum dibayar' }}
                                </p>
                            </div>
                            <div class="text-right">
                                <span class="text-xs px-2 py-1 rounded {{ $bayar->status_pembayaran_id == 3 ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ $bayar->status_pembayaran->nama_status ?? 'Menunggu' }}
                                </span>
                                <p class="font-bold mt-1">Rp {{ number_format($bayar->jumlah_bayar, 0, ',', '.') }}</p>
                            </div>
                            @if($bayar->bukti_pembayaran)
                                <a href="{{ asset('storage/' . $bayar->bukti_pembayaran) }}" target="_blank" class="bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 px-4 rounded text-sm font-bold">
                                    Lihat Bukti
                                </a>
                            @endif
                        </div>
                    @else
                        <p class="text-sm text-gray-500 italic">Belum ada pembayaran yang tercatat untuk pesanan ini.</p>
                    @endif

                    @if($pesanan->status_pesanan_id == 6 && $pesanan->catatan && str_contains($pesanan->catatan, '[BATAL:'))
                        <div class="mt-6 bg-red-50 p-4 rounded-3xl border border-red-100">
                            <h4 class="text-red-800 font-bold mb-1"><x-heroicon-o-no-symbol class="mr-2 w-5 h-5" />Alasan Pembatalan:</h4>
                            <p class="text-red-700 text-sm">{{ Str::after($pesanan->catatan, '[BATAL:') }}</p>
                        </div>
                    @endif

                    {{-- Tombol Aksi Status --}}
                    @if(!in_array($pesanan->status_pesanan_id, [5, 6]))
                        <div class="mt-8 border-t pt-6" x-data="{ showBatalModal: false }">
                            <div class="flex flex-wrap gap-3 justify-center">

                                @if($pesanan->status_pesanan_id == 1)
                                <form action="{{ route('admin.pesanan.nasibox.konfirmasi', $pesanan->id) }}" method="POST" onsubmit="return confirm('Anda yakin ingin mengonfirmasi pesanan ini?')">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-8 rounded-2xl shadow">
                                        <x-heroicon-o-check class="mr-2 w-5 h-5" />Konfirmasi Pesanan
                                    </button>
                                </form>
                                @endif

                                @if($pesanan->status_pesanan_id == 2)
                                <form action="{{ route('admin.pesanan.nasibox.update-status', $pesanan->id) }}" method="POST" onsubmit="return confirm('Mulai proses dapur untuk pesanan ini?')">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="diproses">
                                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded-2xl shadow">
                                        <x-heroicon-o-sparkles class="mr-2 w-5 h-5" />Mulai Proses Dapur
                                    </button>
                                </form>
                                @endif

                                @if($pesanan->status_pesanan_id == 3)
                                <form action="{{ route('admin.pesanan.nasibox.update-status', $pesanan->id) }}" method="POST" onsubmit="return confirm('Tandai pesanan sebagai Siap Dikirim?')">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="menunggu_pengiriman">
                                    <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-8 rounded-2xl shadow">
                                        <x-heroicon-o-cube class="mr-2 w-5 h-5" />Tandai Siap Dikirim
                                    </button>
                                </form>
                                @endif

                                @if($pesanan->status_pesanan_id == 4)
                                <form action="{{ route('admin.pesanan.nasibox.update-status', $pesanan->id) }}" method="POST" onsubmit="return confirm('Tandai pesanan sebagai Selesai?')">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="selesai">
                                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-8 rounded-2xl shadow">
                                        <x-heroicon-o-flag class="mr-2 w-5 h-5" />Tandai Selesai
                                    </button>
                                </form>
                                @endif

                                <button @click="showBatalModal = true" type="button" class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-8 rounded-2xl shadow">
                                    <x-heroicon-o-x-mark class="mr-2 w-5 h-5" />Batalkan Pesanan
                                </button>
                            </div>

                            {{-- Modal Pembatalan --}}
                            <div x-show="showBatalModal" style="display:none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                    <div x-show="showBatalModal" x-transition.opacity class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showBatalModal = false"></div>
                                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                                    <div x-show="showBatalModal" x-transition class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                        <form action="{{ route('admin.pesanan.nasibox.update-status', $pesanan->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="dibatalkan">
                                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                                <div class="sm:flex sm:items-start">
                                                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                                        <x-heroicon-o-exclamation-triangle class="text-red-600 w-5 h-5" />
                                                    </div>
                                                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Batalkan Pesanan</h3>
                                                        <div class="mt-2">
                                                            <p class="text-sm text-gray-500 mb-2">Masukkan alasan pembatalan (akan dicatat pada catatan pesanan).</p>
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
    </div>
</div>
@endsection
