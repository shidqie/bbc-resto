@extends('layouts.pos')

@section('title', 'Detail Pesanan Nasi Box #' . $pesanan->kode_pesanan)

@section('content')
<div class="p-4 md:p-6 lg:p-8 max-w-[1200px] mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Detail Pesanan Nasi Box #{{ $pesanan->kode_pesanan }}</h1>
        <a href="{{ route('admin.pesanan.nasibox.index') }}" class="text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-bold transition-colors">&larr; Kembali</a>
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
                <div class="bg-red-50 border border-red-200 p-6 rounded-lg shadow-sm">
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
                        <a href="{{ route('pengadaan.create') }}" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded text-sm">Buat Pengadaan</a>
                    </div>
                </div>
            @endif

            <div class="grid md:grid-cols-2 gap-6">
                {{-- Info Pemesan --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold mb-4 border-b pb-2">Informasi Pemesan</h3>
                        <div class="space-y-3 text-sm">
                            <div class="grid grid-cols-3"><span class="text-gray-500">Status</span> <span class="col-span-2 font-bold">{{ strtoupper(str_replace('_', ' ', $pesanan->status)) }}</span></div>
                            <div class="grid grid-cols-3"><span class="text-gray-500">Nama Pemesan</span> <span class="col-span-2">{{ $pesanan->nama_pemesan }}</span></div>
                            <div class="grid grid-cols-3"><span class="text-gray-500">Kontak</span> <span class="col-span-2">{{ $pesanan->kontak }}</span></div>
                            <div class="grid grid-cols-3"><span class="text-gray-500">Metode Pengiriman</span> <span class="col-span-2 capitalize">{{ $pesanan->metode_pengiriman }}</span></div>
                            <div class="grid grid-cols-3"><span class="text-gray-500">Alamat / Lokasi</span> <span class="col-span-2">{{ $pesanan->alamat ?? $pesanan->lokasi_acara }}</span></div>
                            @if($pesanan->metode_pengiriman === 'delivery')
                                <div class="grid grid-cols-3"><span class="text-gray-500">Jarak</span> <span class="col-span-2">{{ $pesanan->jarak_km }} km</span></div>
                                <div class="grid grid-cols-3"><span class="text-gray-500">Ongkos Kirim</span> <span class="col-span-2">Rp {{ number_format($pesanan->ongkos_kirim, 0, ',', '.') }}</span></div>
                            @endif
                            <div class="grid grid-cols-3"><span class="text-gray-500">Tanggal Acara</span> <span class="col-span-2">{{ $pesanan->tanggal_acara->format('d M Y') }}</span></div>
                            <div class="grid grid-cols-3"><span class="text-gray-500">Catatan</span> <span class="col-span-2">{{ $pesanan->catatan ?: '-' }}</span></div>
                        </div>
                    </div>
                </div>

                {{-- Info Nasi Box --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold mb-4 border-b pb-2">Informasi Pesanan</h3>
                        <div class="space-y-3 text-sm mb-4">
                            <div class="grid grid-cols-3"><span class="text-gray-500">Varian Nasi Box</span> <span class="col-span-2 font-semibold">{{ $pesanan->paket->nama_paket ?? '-' }}</span></div>
                            <div class="grid grid-cols-3"><span class="text-gray-500">Jumlah Box</span> <span class="col-span-2">{{ $pesanan->jumlah_box }}</span></div>
                            <div class="grid grid-cols-3"><span class="text-gray-500">Harga per Box</span> <span class="col-span-2">Rp {{ number_format($pesanan->paket->harga ?? 0, 0, ',', '.') }}</span></div>
                            <div class="grid grid-cols-3"><span class="text-gray-500">Total Tagihan</span> <span class="col-span-2 font-bold text-lg text-blue-600">Rp {{ number_format($pesanan->total_tagihan, 0, ',', '.') }}</span></div>
                        </div>
                        
                        <h4 class="font-medium text-gray-700 mb-2">Pilihan Lauk/Sayur:</h4>
                        @if($pesanan->details && $pesanan->details->count() > 0)
                            <ul class="list-disc list-inside text-sm text-gray-600 space-y-1">
                                @foreach($pesanan->details as $detail)
                                    <li>
                                        <span class="font-semibold">{{ $detail->komponenPaket->nama_komponen ?? 'Komponen' }}:</span> 
                                        {{ $detail->menu->nama ?? '-' }}
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
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4 border-b pb-2">Bukti Pembayaran</h3>
                    
                    @if($pesanan->buktiPembayarans->isEmpty())
                        @if($pesanan->snap_token)
                            <div class="bg-blue-50 border border-blue-200 text-blue-800 rounded-lg p-4 flex items-start gap-3">
                                <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <div>
                                    <p class="font-bold mb-1">Pembayaran Terintegrasi (Midtrans)</p>
                                    <p class="text-sm">Pelanggan menggunakan metode pembayaran otomatis Midtrans. 
                                        @if($pesanan->status === 'menunggu_dp')
                                            Saat ini sistem sedang menunggu pembayaran diselesaikan.
                                        @else
                                            Pembayaran telah berhasil diverifikasi secara otomatis oleh sistem.
                                        @endif
                                    </p>
                                </div>
                            </div>
                        @else
                            <p class="text-sm text-gray-500 italic">Belum ada bukti pembayaran yang diunggah pelanggan.</p>
                        @endif
                    @else
                        <div class="grid md:grid-cols-2 gap-4">
                            @foreach($pesanan->buktiPembayarans as $bukti)
                                <div class="border rounded-lg p-4">
                                    <div class="flex justify-between mb-2">
                                        <span class="font-bold uppercase">{{ $bukti->jenis_pembayaran }}</span>
                                        <span class="text-xs px-2 py-1 rounded {{ $bukti->status === 'verified' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ $bukti->status }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500 mb-3">Diunggah pada: {{ $bukti->created_at->format('d M Y H:i') }}</p>
                                    
                                    <a href="{{ asset('storage/' . $bukti->file_path) }}" target="_blank" class="block w-full text-center bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 rounded text-sm mb-3">Lihat File</a>
                                    
                                    @if($bukti->status === 'menunggu_verifikasi')
                                        {{-- Gunakan route dari Controller Catering yg sudah diset di web.php untuk update bukti (Polymorphic) --}}
                                        <form action="{{ route('admin.bukti.verifikasi-dp', $bukti->id) }}" method="POST" class="mt-2">
                                            @csrf
                                            @method('PATCH')
                                            <input type="text" name="catatan_admin" placeholder="Catatan opsional..." class="w-full text-sm border-gray-300 rounded-md mb-2">
                                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">
                                                Verifikasi Pembayaran
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Tombol Konfirmasi --}}
                    @if($pesanan->status === 'dibatalkan' && $pesanan->alasan_batal)
                        <div class="mt-6 bg-red-50 p-4 rounded-xl border border-red-100">
                            <h4 class="text-red-800 font-bold mb-1"><i class="fa-solid fa-ban mr-2"></i>Alasan Pembatalan:</h4>
                            <p class="text-red-700 text-sm">{{ $pesanan->alasan_batal }}</p>
                        </div>
                    @endif

                                        {{-- Tombol Pelunasan dan Pembatalan --}}
                    @if(!in_array($pesanan->status, ['lunas', 'dibatalkan', 'selesai']))
                        <div class="mt-8 border-t pt-6 flex gap-4 justify-center" x-data="{ showBatalModal: false }">
                            
                            @if(in_array($pesanan->status, ['terkonfirmasi', 'diproses', 'dikirim']))
                            <form action="{{ route('admin.pesanan.nasibox.update-status', $pesanan->id) }}" method="POST" onsubmit="return confirm('Anda yakin pesanan ini sudah LUNAS?')">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="lunas">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-lg shadow">
                                    <i class="fa-solid fa-check-double mr-2"></i>Tandai Lunas
                                </button>
                            </form>
                            @endif
                            
                            <button @click="showBatalModal = true" type="button" class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-8 rounded-lg shadow">
                                <i class="fa-solid fa-times mr-2"></i>Batalkan Pesanan
                            </button>

                            {{-- Modal Pembatalan --}}
                            <div x-show="showBatalModal" style="display:none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                    <div x-show="showBatalModal" x-transition.opacity class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showBatalModal = false"></div>
                                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                                    <div x-show="showBatalModal" x-transition class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                        <form action="{{ route('admin.pesanan.nasibox.update-status', $pesanan->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="dibatalkan">
                                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                                <div class="sm:flex sm:items-start">
                                                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                                        <i class="fa-solid fa-exclamation-triangle text-red-600"></i>
                                                    </div>
                                                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                                            Batalkan Pesanan
                                                        </h3>
                                                        <div class="mt-2">
                                                            <p class="text-sm text-gray-500 mb-2">
                                                                Masukkan alasan pembatalan dan kebijakan refund (jika ada).
                                                            </p>
                                                            <textarea name="alasan_batal" rows="3" required class="w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm" placeholder="Contoh: Dibatalkan pelanggan H-1, DP Hangus 50% atau Pelanggan tidak bayar DP"></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                                <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                                                    Konfirmasi Batal
                                                </button>
                                                <button type="button" @click="showBatalModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                                    Kembali
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($pesanan->status === 'menunggu_konfirmasi')
                        <div class="mt-8 border-t pt-6 text-center">
                            <form action="{{ route('admin.pesanan.nasibox.konfirmasi', $pesanan->id) }}" method="POST" onsubmit="return confirm('Anda yakin ingin mengonfirmasi pesanan ini? Stok bahan baku akan langsung terpotong.')">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-8 rounded-lg shadow">
                                    Konfirmasi Pesanan & Potong Stok
                                </button>
                                <p class="text-xs text-gray-500 mt-2">Pastikan DP sudah diverifikasi sebelum mengonfirmasi pesanan.</p>
                            </form>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
