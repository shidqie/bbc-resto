<x-layouts.landing>
    @php
        $dpAmount = $pesanan->nominalDP();
        $dpPersen = $pesanan->persentaseDP();
        $dpTerbayar = (float) $pesanan->pembayaran->where('status_verifikasi', 'diterima')->sum('jumlah_dibayar');
        $lunas = (float) $pesanan->pembayaran->where('status_verifikasi', 'diterima')->sum('jumlah_dibayar');
        $isPelunasan = $lunas >= $dpAmount && $lunas < $pesanan->total_tagihan;
        $amountToPay = $isPelunasan ? max(0, $pesanan->total_tagihan - $lunas) : max(0, $dpAmount - $dpTerbayar);
        $payTitle = $isPelunasan ? 'Pelunasan Tagihan' : 'Pembayaran Uang Muka';
        $payDesc = $isPelunasan ? 'Selesaikan pembayaran sisa untuk mengonfirmasi pengiriman pesanan.' : 'Selesaikan DP ' . $dpPersen . '% untuk mengonfirmasi pesanan Anda.';
        $namaPemesan = optional($pesanan->pelanggan)->nama ?? optional($pesanan->jadwal_pesanan)->nama_penerima;
        $paket = $pesanan->detail_pesanan->first();
        $satuan = $type === 'nasi_box' ? 'Box' : 'Porsi';
        
        // Status Badge Logic
        $terakhirBayar = $pesanan->pembayaran->last();
        $statusVerifikasi = $terakhirBayar ? $terakhirBayar->status_verifikasi : null;
        
        if ($lunas >= $pesanan->total_tagihan) {
            $badgeText = 'Lunas';
            $badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200/80';
            $badgeDot = 'bg-emerald-500';
            $badgeIcon = '';
        } elseif ($statusVerifikasi === 'menunggu_verifikasi') {
            $badgeText = 'Menunggu Verifikasi';
            $badgeClass = 'bg-blue-50 text-blue-700 border-blue-200/80';
            $badgeDot = 'bg-blue-500 animate-pulse';
            $badgeIcon = '';
        } elseif ($statusVerifikasi === 'ditolak') {
            $badgeText = 'Ditolak';
            $badgeClass = 'bg-red-50 text-red-700 border-red-200/80';
            $badgeDot = 'bg-red-500';
            $badgeIcon = '';
        } elseif ($dpTerbayar > 0) {
            $badgeText = 'DP Terbayar';
            $badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200/80';
            $badgeDot = 'bg-emerald-500';
            $badgeIcon = '';
        } else {
            $badgeText = 'Menunggu Pembayaran';
            $badgeClass = 'bg-amber-50 text-amber-700 border-amber-200/80';
            $badgeDot = 'bg-amber-500 animate-pulse';
            $badgeIcon = '';
        }
    @endphp

    <x-slot:title>{{ $payTitle }} — {{ $pesanan->id_pesanan }}</x-slot:title>

    <div class="min-h-screen bg-[#F9FAFB] text-[#111827] pb-16">
        
        {{-- Header Bar --}}
        <div class="bg-white border-b border-gray-200/80 py-6 mb-8 shadow-2xs">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">{{ $payTitle }}</h1>
                    <p class="text-xs text-gray-500 font-medium mt-0.5">{{ $payDesc }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Invoice:</span>
                    <span class="font-mono text-xs font-bold bg-gray-100 text-gray-800 px-3 py-1.5 rounded-xl border border-gray-200/80">{{ $pesanan->id_pesanan }}</span>
                </div>
            </div>
        </div>

        <main class="max-w-6xl mx-auto px-4 sm:px-6">

            @if(session('success'))
                <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-xs font-bold flex items-center gap-2">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-xs font-bold flex items-center gap-2">
                    {{ session('error') }}
                </div>
            @endif

            {{-- 2-Column Main Layout: KIRI (65% / 7 cols) & KANAN (35% / 5 cols) --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                {{-- ── KIRI (65%): Informasi Pesanan, Ringkasan Tagihan & Detail Menu ── --}}
                <div class="lg:col-span-7 space-y-6">

                    {{-- 1. Ringkasan Pembayaran --}}
                    <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-none space-y-4">
                        <h3 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-3 mb-4">Ringkasan Pembayaran</h3>
                        
                        <div class="space-y-3 text-xs">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-500">Subtotal</span>
                                <span class="font-bold text-gray-900">Rp {{ number_format($pesanan->total_tagihan, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-500">DP {{ $dpPersen }}% (Uang Muka)</span>
                                <span class="font-bold text-gray-900">Rp {{ number_format($dpAmount, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-500">Sisa Pelunasan</span>
                                <span class="font-bold text-red-600">Rp {{ number_format($pesanan->total_tagihan - $dpAmount, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <div class="bg-[#0D3024] text-white rounded-xl p-4 mt-4">
                            <p class="text-emerald-300 text-[10px] font-bold uppercase mb-1">Total yang Harus Dibayar ({{ $isPelunasan ? 'Pelunasan' : 'DP '.$dpPersen.'%' }})</p>
                            <p class="text-2xl font-bold">Rp {{ number_format($amountToPay, 0, ',', '.') }}</p>
                        </div>

                        @if(!$isPelunasan)
                        <div class="bg-amber-50 rounded-xl border border-amber-100 p-3 mt-3 flex gap-2">
                            <span class="text-amber-600 mt-0.5">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                            </span>
                            <p class="text-[10px] text-amber-800 font-medium leading-relaxed">Pembayaran DP sebesar {{ $dpPersen }}% wajib dilakukan untuk mengonfirmasi pesanan Anda.</p>
                        </div>
                        @endif
                    </div>

                    {{-- 2. Kartu Informasi Pesanan --}}
                    <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-none">
                        <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-4">
                            <h2 class="text-sm font-bold text-gray-900">Rincian Pesanan</h2>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border {{ $badgeClass }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $badgeDot }}"></span>
                                {{ $badgeIcon }} {{ $badgeText }}
                            </span>
                        </div>

                        <div class="space-y-3.5 text-xs">
                            <div class="grid grid-cols-3">
                                <span class="text-gray-500 font-medium">Invoice</span>
                                <span class="col-span-2 font-bold text-gray-900">{{ $pesanan->id_pesanan }}</span>
                            </div>
                            <div class="grid grid-cols-3">
                                <span class="text-gray-500 font-medium">Pemesan</span>
                                <span class="col-span-2 font-bold text-gray-900">{{ $namaPemesan }}</span>
                            </div>
                            <div class="grid grid-cols-3">
                                <span class="text-gray-500 font-medium">Produk / Layanan</span>
                                <span class="col-span-2 font-bold text-gray-900">{{ $paket->menu->nama_menu ?? 'Paket' }} <span class="text-gray-500 font-normal">({{ $paket->jumlah ?? '-' }} {{ $satuan }})</span></span>
                            </div>
                            @if($pesanan->jadwal_pesanan)
                            <div class="grid grid-cols-3">
                                <span class="text-gray-500 font-medium">Tanggal Acara</span>
                                <span class="col-span-2 font-bold text-gray-900">{{ \Carbon\Carbon::parse($pesanan->jadwal_pesanan->tanggal_acara)->format('d F Y') }}</span>
                            </div>
                            @endif
                            <div class="grid grid-cols-3">
                                <span class="text-gray-500 font-medium">Jenis Layanan</span>
                                <span class="col-span-2 font-bold text-gray-900">{{ $type === 'nasi_box' ? 'Nasi Box' : 'Catering' }}</span>
                            </div>
                            <div class="grid grid-cols-3">
                                <span class="text-gray-500 font-medium">Metode Pengambilan</span>
                                <span class="col-span-2 font-bold text-gray-900">{{ strtolower($pesanan->metode_pengiriman) === 'delivery' ? 'Diantar' : 'Diambil' }}</span>
                            </div>
                            @if(strtolower($pesanan->metode_pengiriman) === 'delivery' && $pesanan->jadwal_pesanan)
                            <div class="grid grid-cols-3">
                                <span class="text-gray-500 font-medium">Alamat Pengiriman</span>
                                <span class="col-span-2 font-bold text-gray-900">{{ $pesanan->jadwal_pesanan->alamat_pengiriman ?? '-' }}</span>
                            </div>
                            @endif
                            @php
                                $noTelepon = optional($pesanan->pelanggan)->no_telepon ?? optional($pesanan->jadwal_pesanan)->nomor_telepon_penerima;
                            @endphp
                            @if($noTelepon)
                            <div class="grid grid-cols-3">
                                <span class="text-gray-500 font-medium">No. Telepon / WhatsApp</span>
                                <span class="col-span-2 font-bold text-gray-900">{{ $noTelepon }}</span>
                            </div>
                            @endif
                            @if($pesanan->jadwal_pesanan && $pesanan->jadwal_pesanan->keterangan)
                            <div class="grid grid-cols-3">
                                <span class="text-gray-500 font-medium">Catatan</span>
                                <span class="col-span-2 font-bold text-gray-900">{{ $pesanan->jadwal_pesanan->keterangan }}</span>
                            </div>
                            @endif
                        </div>
                    </div>



                    {{-- 3. Detail Menu Pilihan --}}
                    <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-none space-y-4">
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100 pb-3">Detail Menu Pilihan</h3>
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-gray-100 text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                                    <th class="py-3 px-2 font-bold">KATEGORI</th>
                                    <th class="py-3 px-2 font-bold">PILIHAN</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 text-xs">
                                @forelse($pesanan->detail_pesanan as $detail)
                                    @forelse($detail->pilihan_pesanan_catering as $pilihan)
                                        <tr class="hover:bg-gray-50/50 transition-colors">
                                            <td class="py-3 px-2 text-gray-500">{{ $pilihan->komponen_paket->nama_komponen }}</td>
                                            <td class="py-3 px-2 font-medium text-gray-900">{{ $pilihan->pilihan_komponen_paket->nama_pilihan }}</td>
                                        </tr>
                                    @empty
                                        <tr class="hover:bg-gray-50/50 transition-colors">
                                            <td class="py-3 px-2 text-gray-500">Menu</td>
                                            <td class="py-3 px-2 font-medium text-gray-900">{{ $detail->menu->nama_menu ?? '-' }}</td>
                                        </tr>
                                    @endforelse
                                @empty
                                    <tr>
                                        <td colspan="2" class="py-4 px-2 text-center text-gray-400 italic">Tidak ada detail menu.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>



                </div>

                {{-- ── KANAN (35%): Timeline Status, Rekening & Upload Bukti ── --}}
                <div class="lg:col-span-5 space-y-6">

                    {{-- Informasi Penting --}}
                    <div class="bg-blue-50/50 rounded-2xl border border-blue-100 p-6 shadow-none">
                        <h3 class="text-xs font-bold text-blue-900 mb-3">Informasi Penting</h3>
                        <ul class="list-disc pl-4 space-y-1.5 text-[11px] text-blue-800 leading-relaxed">
                            <li>DP (Uang Muka) tidak dapat dikembalikan jika pesanan dibatalkan oleh konsumen.</li>
                            <li>Pelunasan wajib dilakukan maksimal H-7 sebelum tanggal acara.</li>
                            <li>Jika tidak melakukan pelunasan hingga batas waktu, pesanan akan dianggap batal.</li>
                        </ul>
                    </div>



                    {{-- 2. Card Pembayaran & Upload Bukti --}}
                    @if($lunas >= $pesanan->total_tagihan)
                        {{-- APABILA SUDAH DIVERIFIKASI ADMIN --}}
                        <div class="bg-white rounded-2xl border border-emerald-200 p-6 shadow-none space-y-4">
                            <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-100 text-emerald-800 text-center">
                                <p class="text-xs font-bold text-emerald-900">Pembayaran Terverifikasi!</p>
                                <p class="text-[11px] text-emerald-700 mt-0.5">Bukti pembayaran Anda telah disetujui oleh admin.</p>
                            </div>

                        </div>
                    @elseif($statusVerifikasi === 'menunggu_verifikasi')
                        {{-- APABILA BUKTI SUDAH DIUNGGAH DAN MENUNGGU VERIFIKASI ADMIN --}}
                        <div class="bg-white rounded-2xl border border-blue-200 p-6 shadow-none space-y-4">
                            <div class="p-3.5 bg-blue-50 rounded-xl border border-blue-100 text-blue-900 text-center">
                                <p class="text-xs font-bold text-blue-900">Bukti Pembayaran Terkirim!</p>
                                <p class="text-[11px] text-blue-700 mt-0.5">Menunggu proses verifikasi admin Resto (1×24 Jam).</p>
                            </div>

                        </div>
                    @else
                        {{-- FORM UPLOAD BUKTI UNTUK PENGGUNA BARU --}}
                        <div class="bg-white rounded-2xl border border-gray-200/80 p-5 shadow-none space-y-5" x-data="{ copied: false }">
                            
                            {{-- Clean Nominal Transfer Row --}}
                            <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                                <span class="text-xs font-bold text-gray-700">Nominal Transfer</span>
                                <span class="text-sm font-extrabold text-[#0D3024]">Rp {{ number_format($amountToPay, 0, ',', '.') }}</span>
                            </div>

                            {{-- 1. Informasi Rekening --}}
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-2">1. Informasi Rekening</label>
                                <div class="p-4 bg-emerald-50/60 border border-emerald-200/70 rounded-xl space-y-2">
                                    <div class="flex justify-between items-center">
                                        <span class="text-[11px] font-bold text-emerald-800 uppercase tracking-wider">Bank BCA</span>
                                        <span class="text-[11px] font-medium text-emerald-700">A/N HENI</span>
                                    </div>
                                    <div class="text-lg font-bold font-mono text-[#0D3024] tracking-wider">
                                        2780378231
                                    </div>
                                    <button type="button" @click="navigator.clipboard.writeText('2780378231'); copied = true; setTimeout(() => copied = false, 2000)"
                                            class="w-full py-2 bg-white hover:bg-emerald-100/50 border border-emerald-300 text-emerald-900 text-xs font-bold rounded-lg transition-all flex items-center justify-center gap-1.5 shadow-2xs">
                                        <template x-if="!copied">
                                            <span class="font-bold">Salin Nomor Rekening</span>
                                        </template>
                                        <template x-if="copied">
                                            <span class="font-bold text-emerald-700">Berhasil Disalin!</span>
                                        </template>
                                    </button>
                                </div>
                            </div>

                            {{-- 2. Form Drag & Drop Upload Bukti --}}
                            <form action="{{ route('pesanan.bukti.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4"
                                  x-data="{ filePreview: null, fileName: '', isDragging: false }">
                                @csrf
                                <input type="hidden" name="kode_pesanan" value="{{ $pesanan->id_pesanan }}">
                                <input type="hidden" name="jenis_pembayaran" value="{{ $isPelunasan ? 'pelunasan' : 'dp' }}">

                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-2">2. Upload Bukti Pembayaran</label>
                                    
                                    <div @dragover.prevent="isDragging = true"
                                         @dragleave.prevent="isDragging = false"
                                         @drop.prevent="isDragging = false; const files = $event.dataTransfer.files; if(files.length > 0) { $refs.fileInput.files = files; handleFileSelect({ target: $refs.fileInput }); }"
                                         class="border-2 border-dashed rounded-xl p-5 text-center transition-all duration-200 cursor-pointer"
                                         :class="isDragging ? 'border-[#0D3024] bg-[#0D3024]/5' : 'border-gray-200 bg-gray-50/50 hover:bg-gray-50 hover:border-gray-300'">
                                        
                                        <template x-if="!filePreview">
                                            <div @click="$refs.fileInput.click()">
                                                <p class="text-xs font-bold text-gray-800 mb-0.5">Klik atau tarik file ke sini</p>
                                                <p class="text-[11px] text-gray-400 font-medium mb-3">JPG • PNG • PDF • Maks 1 MB</p>
                                                <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-[#0D3024] text-white text-xs font-semibold shadow-2xs">
                                                    Pilih File
                                                </span>
                                            </div>
                                        </template>

                                        <template x-if="filePreview">
                                            <div>
                                                <template x-if="!filePreview.startsWith('data:application/pdf')">
                                                    <img :src="filePreview" class="max-h-36 mx-auto rounded-lg shadow-none border border-gray-200 object-cover mb-2">
                                                </template>
                                                <template x-if="filePreview.startsWith('data:application/pdf')">
                                                    <div class="h-16 w-16 mx-auto flex items-center justify-center bg-gray-100 rounded-lg mb-2">
                                                        <svg class="w-8 h-8 text-red-500" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-9.5 8.5c0 .8-.7 1.5-1.5 1.5H7v2H5.5V9H8c.8 0 1.5.7 1.5 1.5v1zm5 2c0 .8-.7 1.5-1.5 1.5h-2.5V9H13c.8 0 1.5.7 1.5 1.5v3zm4-3H17v1.5h1.5v1.5H17V17h-1.5V9h3v1.5zM7 10.5h1v1H7v-1zm6 0h1v2h-1v-2z"/></svg>
                                                    </div>
                                                </template>
                                                <p class="text-xs font-bold text-gray-800 truncate" x-text="fileName"></p>
                                                <button type="button" @click="filePreview = null; fileName = ''; $refs.fileInput.value = ''"
                                                        class="mt-2 text-xs text-red-500 font-bold hover:underline">
                                                    Ganti File
                                                </button>
                                            </div>
                                        </template>

                                        <input type="file" x-ref="fileInput" name="file_bukti" accept="image/jpeg,image/png,application/pdf" class="hidden" required
                                               @change="
                                                    const file = $event.target.files[0];
                                                    if(file) {
                                                        if (file.size > 1048576) {
                                                            alert('Ukuran file maksimal 1MB');
                                                            $refs.fileInput.value = '';
                                                            return;
                                                        }
                                                        const validTypes = ['image/jpeg', 'image/png', 'application/pdf'];
                                                        if (!validTypes.includes(file.type)) {
                                                            alert('Format file tidak didukung. Harap upload JPG, PNG, atau PDF.');
                                                            $refs.fileInput.value = '';
                                                            return;
                                                        }
                                                        fileName = file.name;
                                                        const reader = new FileReader();
                                                        reader.onload = (e) => { filePreview = e.target.result; };
                                                        reader.readAsDataURL(file);
                                                    }
                                               ">
                                    </div>
                                    @error('file_bukti') <p class="text-xs text-red-600 font-medium mt-1">{{ $message }}</p> @enderror
                                </div>

                                {{-- 4. Tombol Submit (Full Width dengan Icon Check) --}}
                                <button type="submit"
                                        class="w-full py-3 bg-[#0D3024] hover:bg-[#1a4a35] text-white font-bold text-xs rounded-xl shadow-none flex items-center justify-center gap-2 transition-all duration-200 active:scale-[0.99]">
                                    Kirim Bukti Pembayaran
                                </button>
                            </form>

                        </div>
                    @endif

                </div>

            </div>

        </main>
    </div>
</x-layouts.landing>
