<x-app-layout>
    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-bold text-gray-900">Detail Pesanan {{ $pesananCatering->no_pesanan }}</h2>
                <a href="{{ route('pesanan-catering.index') }}" class="text-sm text-gray-500 hover:text-gray-700"><i class="fa-solid fa-arrow-left mr-1"></i> Kembali</a>
            </div>

            @if(session('success'))
            <div class="p-4 bg-green-50 border border-green-200 rounded-lg text-green-700">{{ session('success') }}</div>
            @endif
            @if(session('error'))
            <div class="p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">{{ session('error') }}</div>
            @endif

            <!-- Info Pesanan -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div><span class="text-gray-500">Pemesan:</span> <span class="font-semibold">{{ $pesananCatering->nama_pemesan }}</span></div>
                    <div><span class="text-gray-500">Telepon:</span> <span class="font-semibold">{{ $pesananCatering->no_telepon }}</span></div>
                    <div><span class="text-gray-500">Email:</span> <span class="font-semibold">{{ $pesananCatering->email ?? '-' }}</span></div>
                    <div><span class="text-gray-500">Alamat:</span> <span class="font-semibold">{{ $pesananCatering->alamat_pengiriman }}</span></div>
                    <div><span class="text-gray-500">Paket:</span> <span class="font-semibold">{{ $pesananCatering->paketCatering->nama_paket }}</span></div>
                    <div><span class="text-gray-500">Jenis:</span> <span class="font-semibold">{{ $pesananCatering->paketCatering->jenis_paket === 'catering' ? 'Catering' : 'Nasi Box' }}</span></div>
                    <div><span class="text-gray-500">Tanggal Acara:</span> <span class="font-semibold text-primary">{{ $pesananCatering->tanggal_acara->format('d F Y') }}</span></div>
                    <div><span class="text-gray-500">Jumlah Porsi:</span> <span class="font-semibold">{{ $pesananCatering->jumlah_porsi }}</span></div>
                    <div><span class="text-gray-500">Total Harga:</span> <span class="font-bold text-lg">Rp {{ number_format($pesananCatering->total_harga, 0, ',', '.') }}</span></div>
                    <div><span class="text-gray-500">DP ({{ $pesananCatering->dp_percentage }}%):</span> <span class="font-semibold">Rp {{ number_format($pesananCatering->dp_amount, 0, ',', '.') }}</span></div>
                    <div><span class="text-gray-500">Sisa Pembayaran:</span> <span class="font-bold text-red-600">Rp {{ number_format($pesananCatering->sisa_pembayaran, 0, ',', '.') }}</span></div>
                    <div><span class="text-gray-500">Status:</span>
                        @php
                            $sc = ['menunggu_konfirmasi' => 'bg-yellow-100 text-yellow-800', 'terkonfirmasi' => 'bg-blue-100 text-blue-800', 'lunas' => 'bg-green-100 text-green-800', 'dibatalkan' => 'bg-red-100 text-red-800', 'selesai' => 'bg-gray-100 text-gray-800'];
                        @endphp
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $sc[$pesananCatering->status] ?? '' }}">{{ ucfirst(str_replace('_', ' ', $pesananCatering->status)) }}</span>
                    </div>
                    @if($pesananCatering->detail_acara)
                    <div class="col-span-2"><span class="text-gray-500">Detail Acara:</span> <p class="font-semibold">{{ $pesananCatering->detail_acara }}</p></div>
                    @endif
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap gap-3">
                @if($pesananCatering->status === 'menunggu_konfirmasi')
                <form action="{{ route('pesanan-catering.confirm', $pesananCatering) }}" method="POST" onsubmit="return confirm('Konfirmasi pesanan ini? Stok bahan baku akan dipotong otomatis.')">
                    @csrf @method('PATCH')
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium"><i class="fa-solid fa-check mr-1"></i> Konfirmasi Pesanan</button>
                </form>
                @endif

                @if(in_array($pesananCatering->status, ['terkonfirmasi', 'lunas']))
                <form action="{{ route('pesanan-catering.complete', $pesananCatering) }}" method="POST" onsubmit="return confirm('Tandai pesanan ini selesai?')">
                    @csrf @method('PATCH')
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm font-medium"><i class="fa-solid fa-flag-checkered mr-1"></i> Selesai</button>
                </form>
                @endif

                @if(!in_array($pesananCatering->status, ['dibatalkan', 'selesai']))
                <button onclick="document.getElementById('cancelModal').classList.remove('hidden')" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors text-sm font-medium"><i class="fa-solid fa-times mr-1"></i> Batalkan</button>
                @endif
            </div>

            <!-- Riwayat Pembayaran -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold mb-4">Riwayat Pembayaran</h3>
                @if($pesananCatering->pembayarans->count())
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Jenis</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Metode</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($pesananCatering->pembayarans as $bayar)
                        <tr>
                            <td class="px-4 py-3">{{ $bayar->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3 capitalize">{{ $bayar->jenis_pembayaran }}</td>
                            <td class="px-4 py-3 uppercase">{{ $bayar->metode }}</td>
                            <td class="px-4 py-3 text-right font-medium">Rp {{ number_format($bayar->jumlah_bayar, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $bayar->status === 'verified' ? 'bg-green-100 text-green-800' : ($bayar->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">{{ ucfirst($bayar->status) }}</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if($bayar->status === 'pending')
                                <form action="{{ route('pesanan-catering.verify-pembayaran', $bayar) }}" method="POST" class="inline">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="action" value="verify">
                                    <button class="text-green-600 hover:text-green-800 text-xs font-medium mr-1">Verifikasi</button>
                                </form>
                                <form action="{{ route('pesanan-catering.verify-pembayaran', $bayar) }}" method="POST" class="inline">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="action" value="reject">
                                    <button class="text-red-600 hover:text-red-800 text-xs font-medium">Tolak</button>
                                </form>
                                @else
                                <span class="text-xs text-gray-400">{{ $bayar->verifiedBy->name ?? '-' }}</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <p class="text-gray-500 text-sm">Belum ada pembayaran.</p>
                @endif

                <!-- Form Catat Pembayaran -->
                @if(!in_array($pesananCatering->status, ['dibatalkan', 'selesai']) && $pesananCatering->sisa_pembayaran > 0)
                <div class="mt-6 pt-6 border-t">
                    <h4 class="font-semibold text-sm mb-3">Catat Pembayaran Baru</h4>
                    <form action="{{ route('pesanan-catering.upload-bukti', $pesananCatering) }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                        @csrf
                        <div>
                            <label class="text-xs text-gray-500">Jenis</label>
                            <select name="jenis_pembayaran" required class="w-full rounded-lg border-gray-300 text-sm">
                                <option value="dp">DP</option>
                                <option value="pelunasan">Pelunasan</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500">Jumlah (Rp)</label>
                            <input type="number" name="jumlah_bayar" required min="1" class="w-full rounded-lg border-gray-300 text-sm">
                        </div>
                        <div>
                            <label class="text-xs text-gray-500">Metode</label>
                            <select name="metode" required class="w-full rounded-lg border-gray-300 text-sm">
                                <option value="cash">Cash</option>
                                <option value="transfer">Transfer</option>
                                <option value="qris">QRIS</option>
                            </select>
                        </div>
                        <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-orange-700 transition-colors text-sm font-medium">Catat</button>
                    </form>
                </div>
                @endif
            </div>

            <!-- Modal Pembatalan -->
            <div id="cancelModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white rounded-xl p-6 w-full max-w-md">
                    <h3 class="text-lg font-bold mb-4">Batalkan Pesanan</h3>
                    <form action="{{ route('pesanan-catering.cancel', $pesananCatering) }}" method="POST">
                        @csrf @method('PATCH')
                        <textarea name="catatan_pembatalan" required placeholder="Alasan pembatalan..." rows="3" class="w-full rounded-lg border-gray-300 mb-4"></textarea>
                        <div class="flex gap-3 justify-end">
                            <button type="button" onclick="document.getElementById('cancelModal').classList.add('hidden')" class="px-4 py-2 bg-gray-100 rounded-lg text-sm">Batal</button>
                            <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded-lg text-sm">Konfirmasi Batalkan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
