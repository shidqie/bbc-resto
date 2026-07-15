<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detail Pesanan Catering') }} #{{ $pesanan->kode_pesanan }}
            </h2>
            <a href="{{ route('admin.pesanan.catering.index') }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; Kembali</a>
        </div>
    </x-slot>

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
                            <div class="grid grid-cols-3"><span class="text-gray-500">Alamat / Lokasi</span> <span class="col-span-2">{{ $pesanan->lokasi_acara }}</span></div>
                            @if($pesanan->metode_pengiriman === 'delivery')
                                <div class="grid grid-cols-3"><span class="text-gray-500">Jarak</span> <span class="col-span-2">{{ $pesanan->jarak_km }} km</span></div>
                                <div class="grid grid-cols-3"><span class="text-gray-500">Ongkos Kirim</span> <span class="col-span-2">Rp {{ number_format($pesanan->ongkos_kirim, 0, ',', '.') }}</span></div>
                            @endif
                            <div class="grid grid-cols-3"><span class="text-gray-500">Tanggal Acara</span> <span class="col-span-2">{{ $pesanan->tanggal_acara->format('d M Y') }}</span></div>
                            <div class="grid grid-cols-3"><span class="text-gray-500">Catatan</span> <span class="col-span-2">{{ $pesanan->catatan ?: '-' }}</span></div>
                        </div>
                    </div>
                </div>

                {{-- Info Paket --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold mb-4 border-b pb-2">Informasi Paket</h3>
                        <div class="space-y-3 text-sm mb-4">
                            <div class="grid grid-cols-3"><span class="text-gray-500">Paket</span> <span class="col-span-2 font-semibold">{{ $pesanan->paket->nama_paket }}</span></div>
                            <div class="grid grid-cols-3"><span class="text-gray-500">Jumlah Porsi</span> <span class="col-span-2">{{ $pesanan->jumlah_porsi }}</span></div>
                            <div class="grid grid-cols-3"><span class="text-gray-500">Total Tagihan</span> <span class="col-span-2 font-bold">Rp {{ number_format($pesanan->total_tagihan, 0, ',', '.') }}</span></div>
                        </div>
                        
                        <h4 class="font-medium text-gray-700 mb-2">Menu Komponen Terpilih:</h4>
                        <ul class="list-disc list-inside text-sm text-gray-600 mb-4">
                            @foreach($pesanan->details as $detail)
                                <li>{{ $detail->komponen->nama_komponen }}: <strong>{{ $detail->menu->nama }}</strong></li>
                            @endforeach
                        </ul>

                        @if($pesanan->addons->isNotEmpty())
                            <h4 class="font-medium text-gray-700 mb-2">Layanan Tambahan:</h4>
                            <ul class="list-disc list-inside text-sm text-gray-600">
                                @foreach($pesanan->addons as $addon)
                                    <li>{{ $addon->layananTambahan->nama }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Bukti Pembayaran & Aksi --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4 border-b pb-2">Bukti Pembayaran</h3>
                    
                    @if($pesanan->buktiPembayarans->isEmpty())
                        <p class="text-sm text-gray-500 italic">Belum ada bukti pembayaran yang diunggah pelanggan.</p>
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
                    @if($pesanan->status === 'menunggu_konfirmasi')
                        <div class="mt-8 border-t pt-6 text-center">
                            <form action="{{ route('admin.pesanan.catering.konfirmasi', $pesanan->id) }}" method="POST" onsubmit="return confirm('Anda yakin ingin mengonfirmasi pesanan ini? Stok bahan baku akan langsung terpotong.')">
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
</x-app-layout>
