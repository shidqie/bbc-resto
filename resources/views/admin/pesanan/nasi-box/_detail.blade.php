@php
        $total = (float) $pesanan->total_tagihan;
        $dpBayar = (float) $pesanan->pembayaran->where('status_verifikasi', 'diterima')->sum('jumlah_dibayar');
        $lunas = (float) $pesanan->pembayaran->where('status_verifikasi', 'diterima')->sum('jumlah_dibayar');
        $isLunas = $lunas >= $total || $dpBayar >= $total;
        
        $hasPelunasanMenunggu = $pesanan->pembayaran->where('jenis_pembayaran', 'pelunasan')->where('status_verifikasi', 'menunggu_verifikasi')->isNotEmpty();
        $hasDpMenunggu = $pesanan->pembayaran->where('jenis_pembayaran', 'uang_muka')->where('status_verifikasi', 'menunggu_verifikasi')->isNotEmpty();
        $totalMenungguPP = (float) $pesanan->pembayaran->where('status_verifikasi', 'menunggu_verifikasi')->sum('jumlah_dibayar');

        $statusBayarLabel = 'Belum Bayar';
        if ($isLunas) {
            $statusBayarLabel = 'Lunas';
        } else {
            if ($hasPelunasanMenunggu || ($totalMenungguPP >= $total)) {
                $statusBayarLabel = 'Menunggu Verifikasi Pelunasan';
            } elseif ($dpBayar > 0) {
                $statusBayarLabel = 'Menunggu Pelunasan';
            } elseif ($hasDpMenunggu || $totalMenungguPP > 0) {
                $statusBayarLabel = 'Menunggu Verifikasi DP';
            }
        }

        $detailPesanan = $pesanan->detail_pesanan->first();
        $metodeKirim = $pesanan->pengantaran ? 'Delivery' : 'Pickup';
        $konsumen = $pesanan->pelanggan;
        $namaKonsumen = $konsumen->nama ?? $pesanan->jadwal_pesanan->nama_penerima ?? '-';
        $kontakKonsumen = $konsumen->nomor_telepon ?? $pesanan->jadwal_pesanan->nomor_telepon_penerima ?? '';
        $emailKonsumen = $konsumen->email ?? '';
        $alamatKonsumen = $konsumen->alamat ?? $pesanan->jadwal_pesanan->alamat_pengantaran ?? $pesanan->catatan ?? '-';
        $waLink = $kontakKonsumen ? 'https://wa.me/' . preg_replace('/[^0-9]/', '', $kontakKonsumen) : null;
        $jenisBayarLabel = ['uang_muka' => 'Uang Muka (DP)', 'pelunasan' => 'Pelunasan', 'pembayaran_penuh' => 'Pembayaran Penuh'];
        $statusVerifLabel = ['diterima' => 'Diterima', 'menunggu_verifikasi' => 'Menunggu Verifikasi', 'ditolak' => 'Ditolak'];
        $statusVerifColor = ['diterima' => 'bg-green-100 text-green-800', 'menunggu_verifikasi' => 'bg-yellow-100 text-yellow-800', 'ditolak' => 'bg-red-100 text-red-800'];
        $statusVerifCard = ['diterima' => 'bg-green-50/40 border-green-200/60', 'menunggu_verifikasi' => 'bg-yellow-50/40 border-yellow-200/60', 'ditolak' => 'bg-red-50/40 border-red-200/60'];

        $userRole = auth()->user()->peran->nama_peran ?? '';
        $isAdminOrPemilik = in_array($userRole, ['Admin', 'Super Admin', 'Pemilik', 'Manajer']);
    @endphp

    <div class="py-4">
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
                        <a href="{{ route('pengadaan.create', ['pesanan_id' => $pesanan->id]) }}" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded text-sm">Buat Pengadaan Nasi Box</a>
                    </div>
                </div>
            @endif

            <div class="grid md:grid-cols-2 gap-6">
                {{-- Info Konsumen --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold mb-4 border-b pb-2">Informasi Konsumen</h3>
                        <div class="space-y-3 text-sm">
                            <div class="grid grid-cols-3"><span class="text-gray-500">Nama Konsumen</span> <span class="col-span-2 font-bold">{{ $namaKonsumen }}</span></div>
                            <div class="grid grid-cols-3"><span class="text-gray-500">Nomor WhatsApp</span>
                                <span class="col-span-2">
                                    @if($waLink)
                                        <a href="{{ $waLink }}" target="_blank" class="text-emerald-700 font-medium hover:underline inline-flex items-center gap-1">
                                            <i class="ph-bold ph-whatsapp-logo"></i>
                                            <span class="whitespace-nowrap">{{ $kontakKonsumen ? \App\Support\WhatsAppNumber::formatForDisplay($kontakKonsumen) : '-' }}</span>
                                        </a>
                                    @else
                                        <span>-</span>
                                    @endif
                                </span>
                            </div>
                            <div class="grid grid-cols-3"><span class="text-gray-500">Email</span> <span class="col-span-2">{{ $emailKonsumen ?: '-' }}</span></div>
                            <div class="grid grid-cols-3"><span class="text-gray-500">Alamat</span> <span class="col-span-2">{{ $alamatKonsumen }}</span></div>
                        </div>
                    </div>
                </div>

                {{-- Info Pesanan --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold mb-4 border-b pb-2">Informasi Pesanan</h3>
                        <div class="space-y-3 text-sm">
                            <div class="grid grid-cols-3"><span class="text-gray-500">Kode Pesanan</span> <span class="col-span-2 font-mono text-xs font-bold">{{ $pesanan->id_pesanan }}</span></div>
                            <div class="grid grid-cols-3"><span class="text-gray-500">Jenis Pesanan</span> <span class="col-span-2 font-semibold">{{ $pesanan->jenis_pesanan->nama_jenis ?? 'Nasi Box' }}</span></div>
                            <div class="grid grid-cols-3"><span class="text-gray-500">Varian</span> <span class="col-span-2 font-semibold">{{ $detailPesanan->menu->nama_menu ?? '-' }}</span></div>
                            <div class="grid grid-cols-3"><span class="text-gray-500">Jumlah Box</span> <span class="col-span-2">{{ $detailPesanan->jumlah ?? 0 }} Box</span></div>
                            <div class="grid grid-cols-3"><span class="text-gray-500">Tanggal Pesan</span> <span class="col-span-2">{{ $pesanan->dibuat_pada ? \Carbon\Carbon::parse($pesanan->dibuat_pada)->translatedFormat('d M Y') : '-' }}</span></div>
                            <div class="grid grid-cols-3"><span class="text-gray-500">Tanggal Acara</span> <span class="col-span-2">{{ $pesanan->jadwal_pesanan?->tanggal_acara ? \Carbon\Carbon::parse($pesanan->jadwal_pesanan->tanggal_acara)->translatedFormat('d M Y') : '-' }}</span></div>
                            <div class="grid grid-cols-3"><span class="text-gray-500">Waktu Acara</span> <span class="col-span-2">{{ $pesanan->jadwal_pesanan?->tanggal_acara ? \Carbon\Carbon::parse($pesanan->jadwal_pesanan->tanggal_acara)->format('H:i') . ' WIB' : '-' }}</span></div>
                            <div class="grid grid-cols-3"><span class="text-gray-500">Metode Pengiriman</span> <span class="col-span-2 capitalize">{{ $metodeKirim }}</span></div>
                            @if($pesanan->pengantaran)
                                <div class="grid grid-cols-3"><span class="text-gray-500">Ongkos Kirim</span> <span class="col-span-2">Rp {{ number_format($pesanan->pengantaran->biaya_pengantaran, 0, ',', '.') }}</span></div>
                            @endif
                            <div class="grid grid-cols-3"><span class="text-gray-500">Status Pesanan</span> <span class="col-span-2 font-bold">{{ $pesanan->status_pesanan->nama_status ?? 'Menunggu Konfirmasi' }}</span></div>
                            <div class="grid grid-cols-3"><span class="text-gray-500">Status Bayar</span> <span class="col-span-2 font-bold text-blue-600">{{ $statusBayarLabel }}</span></div>
                            <div class="grid grid-cols-3"><span class="text-gray-500">Catatan</span> <span class="col-span-2">{{ $pesanan->catatan ?: '-' }}</span></div>
                        </div>
                    </div>
                </div>
            </div>

            @if($isAdminOrPemilik)
            {{-- Info Pembayaran (Full Width) --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4 border-b pb-2">Informasi Pembayaran</h3>

                    @if($pesanan->pembayaran->isEmpty())
                        <p class="text-sm text-gray-500 italic">Belum ada pembayaran yang diunggah pelanggan.</p>
                    @else
                        <div class="overflow-x-auto mb-6">
                            <table class="w-full text-sm text-left border-collapse border border-gray-200">
                                <thead class="bg-gray-50 text-gray-500 uppercase">
                                    <tr>
                                        <th class="px-4 py-3 border border-gray-200 font-semibold">Jenis</th>
                                        <th class="px-4 py-3 border border-gray-200 font-semibold text-right">Nominal</th>
                                        <th class="px-4 py-3 border border-gray-200 font-semibold text-center">Status</th>
                                        <th class="px-4 py-3 border border-gray-200 font-semibold text-center">Bukti</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pesanan->pembayaran as $pemb)
                                        <tr>
                                            <td class="px-4 py-3 border border-gray-200 font-medium">
                                                {{ $jenisBayarLabel[$pemb->jenis_pembayaran] ?? ucwords(str_replace('_', ' ', $pemb->jenis_pembayaran ?? '')) }}
                                            </td>
                                            <td class="px-4 py-3 border border-gray-200 text-right font-semibold">
                                                Rp {{ number_format($pemb->jumlah_dibayar, 0, ',', '.') }}
                                            </td>
                                            <td class="px-4 py-3 border border-gray-200 text-center">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusVerifColor[$pemb->status_verifikasi] ?? 'bg-gray-100 text-gray-700' }}">
                                                    {{ $statusVerifLabel[$pemb->status_verifikasi] ?? ucwords(str_replace('_', ' ', $pemb->status_verifikasi ?? '-')) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 border border-gray-200 text-center">
                                                @if($pemb->bukti_pembayaran && $pemb->bukti_pembayaran !== 'midtrans_online')
                                                    <a href="{{ asset('storage/' . $pemb->bukti_pembayaran) }}" target="_blank" class="text-blue-600 hover:text-blue-800 hover:underline">Lihat Bukti</a>
                                                @else
                                                    <span class="text-gray-400 italic">Otomatis</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    <h3 class="text-lg font-semibold mb-4 border-b pb-2">Ringkasan</h3>
                    <div class="space-y-2 text-sm max-w-sm">
                        <div class="flex justify-between"><span class="text-gray-600 font-medium">Total Tagihan</span> <span class="font-bold text-gray-900">Rp {{ number_format($total, 0, ',', '.') }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-600 font-medium">Total Dibayar</span> <span class="font-bold text-emerald-700">Rp {{ number_format($dpBayar, 0, ',', '.') }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-600 font-medium">Sisa Tagihan</span> <span class="font-bold text-amber-700">Rp {{ number_format(max(0, $total - $dpBayar), 0, ',', '.') }}</span></div>
                    </div>

                    @if($pesanan->status_pesanan_id == 6 && $pesanan->catatan && str_contains($pesanan->catatan, '[BATAL:'))
                        <div class="mt-6 bg-red-50 p-4 rounded-xl border border-red-100">
                            <h4 class="text-red-800 font-bold mb-1"><x-heroicon-o-no-symbol class="mr-2 w-5 h-5" />Alasan Pembatalan:</h4>
                            <p class="text-red-700 text-sm">{{ Str::after($pesanan->catatan, '[BATAL:') }}</p>
                        </div>
                    @endif

                    {{-- Tombol Aksi Status --}}
                    @if(!in_array($pesanan->status_pesanan_id, [5, 6]))
                        <div class="mt-8 border-t pt-6" x-data="{ showBatalModal: false }">
                            <div class="flex flex-wrap gap-3 justify-center">

                                {{-- Konfirmasi (dari Menunggu Pembayaran) --}}
                                @if($pesanan->status_pesanan_id === 7)
                                <form id="form-konfirmasi-nasibox" action="{{ route('admin.pesanan.nasibox.update-status', $pesanan->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="8">
                                    <button type="button" onclick="window.confirmDialog({ title: 'Konfirmasi Pesanan', name: '{{ $pesanan->id_pesanan }}', message: 'Anda yakin ingin mengonfirmasi pesanan ini?', formId: 'form-konfirmasi-nasibox', confirmText: 'Konfirmasi', cancelText: 'Batal', type: 'warning' })" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-8 rounded-lg shadow">
                                        <x-heroicon-o-check class="mr-2 w-5 h-5" />Konfirmasi Pesanan
                                    </button>
                                </form>
                                @endif

                                {{-- Mulai Proses Pengadaan (dari Terkonfirmasi) --}}
                                @if($pesanan->status_pesanan_id === 8)
                                <form id="form-pengadaan-nasibox" action="{{ route('admin.pesanan.nasibox.update-status', $pesanan->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="9">
                                    <button type="button" onclick="window.confirmDialog({ title: 'Mulai Proses Pengadaan', name: '{{ $pesanan->id_pesanan }}', message: 'Mulai proses pengadaan bahan untuk pesanan ini?', formId: 'form-pengadaan-nasibox', confirmText: 'Proses', cancelText: 'Batal', type: 'warning' })" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-lg shadow">
                                        <x-heroicon-o-shopping-cart class="mr-2 w-5 h-5" />Mulai Proses Pengadaan
                                    </button>
                                </form>
                                @endif

                                {{-- Bahan Diterima (dari Proses Pengadaan) --}}
                                @if($pesanan->status_pesanan_id === 9)
                                <form id="form-bahan-diterima-nasibox" action="{{ route('admin.pesanan.nasibox.update-status', $pesanan->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="10">
                                    <button type="button" onclick="window.confirmDialog({ title: 'Bahan Diterima', name: '{{ $pesanan->id_pesanan }}', message: 'Tandai bahan baku telah diterima?', formId: 'form-bahan-diterima-nasibox', confirmText: 'Tandai', cancelText: 'Batal', type: 'warning' })" class="bg-teal-600 hover:bg-teal-700 text-white font-bold py-3 px-8 rounded-lg shadow">
                                        <x-heroicon-o-inbox-arrow-down class="mr-2 w-5 h-5" />Bahan Diterima
                                    </button>
                                </form>
                                @endif

                                {{-- Mulai Sedang Produksi (dari Terkonfirmasi atau Bahan Diterima) --}}
                                @if(in_array($pesanan->status_pesanan_id, [8, 10]))
                                <form id="form-produksi-nasibox" action="{{ route('admin.pesanan.nasibox.update-status', $pesanan->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="11">
                                    <button type="button" onclick="window.confirmDialog({ title: 'Mulai Sedang Produksi', name: '{{ $pesanan->id_pesanan }}', message: 'Mulai proses dapur? Stok bahan akan dipotong otomatis jika belum dipotong.', formId: 'form-produksi-nasibox', confirmText: 'Mulai Produksi', cancelText: 'Batal', type: 'warning' })" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded-lg shadow">
                                        <x-heroicon-o-sparkles class="mr-2 w-5 h-5" />Mulai Sedang Produksi
                                    </button>
                                </form>
                                @endif

                                {{-- Produksi Selesai (dari Sedang Produksi) --}}
                                @if($pesanan->status_pesanan_id === 11)
                                <form id="form-produksi-selesai-nasibox" action="{{ route('admin.pesanan.nasibox.update-status', $pesanan->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="12">
                                    <button type="button" onclick="window.confirmDialog({ title: 'Produksi Selesai', name: '{{ $pesanan->id_pesanan }}', message: 'Tandai produksi selesai? Jika metode pengiriman diantar, akan masuk ke Jadwal Pengantaran.', formId: 'form-produksi-selesai-nasibox', confirmText: 'Selesai Produksi', cancelText: 'Batal', type: 'warning' })" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-8 rounded-lg shadow">
                                        <x-heroicon-o-cube class="mr-2 w-5 h-5" />Produksi Selesai
                                    </button>
                                </form>
                                @endif

                                {{-- Tandai Selesai Total (dari Produksi Selesai JIKA AMBIL SENDIRI) --}}
                                @if($pesanan->status_pesanan_id === 12 && $pesanan->metode_pengiriman === 'ambil_sendiri')
                                <form id="form-selesai-nasibox" action="{{ route('admin.pesanan.nasibox.update-status', $pesanan->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="5">
                                    <button type="button" onclick="window.confirmDialog({ title: 'Pesanan Selesai', name: '{{ $pesanan->id_pesanan }}', message: 'Tandai pesanan sebagai selesai (sudah diambil)?', formId: 'form-selesai-nasibox', confirmText: 'Selesai', cancelText: 'Batal', type: 'warning' })" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-8 rounded-lg shadow">
                                        <x-heroicon-o-flag class="mr-2 w-5 h-5" />Tandai Pesanan Selesai
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
                                        <form action="{{ route('admin.pesanan.nasibox.update-status', $pesanan->id) }}" method="POST">
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
            @endif
                </div>
            </div>

        </div>
    </div>
