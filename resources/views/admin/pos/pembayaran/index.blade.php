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
        } elseif ($statusVerifikasi === 'menunggu_verifikasi') {
            $badgeText = 'Menunggu Verifikasi';
            $badgeClass = 'bg-blue-50 text-blue-700 border-blue-200/80';
            $badgeDot = 'bg-blue-500 animate-pulse';
        } elseif ($statusVerifikasi === 'ditolak') {
            $badgeText = 'Ditolak';
            $badgeClass = 'bg-red-50 text-red-700 border-red-200/80';
            $badgeDot = 'bg-red-500';
        } elseif ($dpTerbayar > 0) {
            $badgeText = 'DP Terbayar';
            $badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200/80';
            $badgeDot = 'bg-emerald-500';
        } else {
            $badgeText = 'Menunggu Pembayaran';
            $badgeClass = 'bg-amber-50 text-amber-700 border-amber-200/80';
            $badgeDot = 'bg-amber-500 animate-pulse';
        }

        // Expire Logic
        $expireTimeStr = null;
        if (!$isPelunasan) {
            // DP Expire: 15 mins from created_at
            $expireTime = \Carbon\Carbon::parse($pesanan->dibuat_pada)->addMinutes(15);
            $expireTimeStr = $expireTime->toIso8601String();
        } else {
            // Pelunasan Expire: H-4 dari waktu acara
            if (isset($pesanan->jadwal_pesanan->waktu_acara)) {
                $expireTime = \Carbon\Carbon::parse($pesanan->jadwal_pesanan->waktu_acara)->subDays(4);
                $expireTimeStr = $expireTime->toIso8601String();
            }
        }

        $showUploadForm = !($lunas >= $pesanan->total_tagihan) && $statusVerifikasi !== 'menunggu_verifikasi';
    @endphp

    <x-slot:title>{{ $payTitle }} — {{ $pesanan->id_pesanan }}</x-slot:title>

    <div class="min-h-screen bg-[#F9FAFB] text-[#111827] pb-16"
        x-data="paymentPage('{{ $expireTimeStr }}')">

        {{-- Header Bar --}}
        <div class="bg-white border-b border-gray-200/80 py-6 shadow-2xs">
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

        {{-- COUNTDOWN BANNER --}}
        @if($showUploadForm && $expireTimeStr)
        <div>
            <template x-if="!isExpired">
                <div class="bg-amber-50 border-b border-amber-200">
                    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-3 flex flex-col sm:flex-row items-center justify-between gap-2">
                        <p class="text-xs font-bold text-amber-900 flex items-center gap-2">
                            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                            Selesaikan {{ $isPelunasan ? 'pelunasan' : 'pembayaran DP' }} sebelum batas waktu
                        </p>
                        <div class="flex gap-1.5 items-center">
                            <template x-if="days > 0">
                                <div class="flex items-center gap-1.5">
                                    <div class="bg-amber-100 text-amber-800 font-mono font-bold text-sm px-2.5 py-1 rounded-lg"><span x-text="days"></span><span class="text-[10px] ml-0.5 font-sans uppercase">Hari</span></div>
                                    <span class="text-amber-700 font-bold">:</span>
                                </div>
                            </template>
                            <template x-if="hours > 0 || days > 0">
                                <div class="flex items-center gap-1.5">
                                    <div class="bg-amber-100 text-amber-800 font-mono font-bold text-sm px-2.5 py-1 rounded-lg"><span x-text="hours"></span><span class="text-[10px] ml-0.5 font-sans uppercase">Jam</span></div>
                                    <span class="text-amber-700 font-bold">:</span>
                                </div>
                            </template>
                            <div class="bg-amber-100 text-amber-800 font-mono font-bold text-sm px-2.5 py-1 rounded-lg"><span x-text="minutes"></span><span class="text-[10px] ml-0.5 font-sans uppercase">Mnt</span></div>
                            <span class="text-amber-700 font-bold">:</span>
                            <div class="bg-amber-100 text-amber-800 font-mono font-bold text-sm px-2.5 py-1 rounded-lg" x-text="seconds"></div>
                        </div>
                    </div>
                </div>
            </template>
            <template x-if="isExpired">
                <div class="bg-red-50 border-b border-red-200">
                    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-red-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p class="text-xs font-bold text-red-800">Batas waktu {{ $isPelunasan ? 'pelunasan (H-4)' : 'pembayaran DP (15 Menit)' }} telah habis. Pesanan dibatalkan otomatis.</p>
                    </div>
                </div>
            </template>
        </div>
        @endif

        <main class="max-w-6xl mx-auto px-4 sm:px-6 mt-8">

            @if(session('success'))
                <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-xs font-bold">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-xs font-bold">
                    {{ session('error') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                {{-- ── KIRI (58%): AKSI — Nominal, Rekening, Upload Bukti ── --}}
                <div class="lg:col-span-7 space-y-6 lg:order-1">

                    @if($lunas >= $pesanan->total_tagihan)
                        <div class="bg-white rounded-2xl border border-emerald-200 p-6 text-center">
                            <p class="text-sm font-bold text-emerald-900">Pembayaran Terverifikasi!</p>
                            <p class="text-xs text-emerald-700 mt-1">Bukti pembayaran Anda telah disetujui oleh admin.</p>
                        </div>
                    @elseif($statusVerifikasi === 'menunggu_verifikasi')
                        <div class="bg-white rounded-2xl border border-blue-200 p-6 text-center">
                            <p class="text-sm font-bold text-blue-900">Bukti Pembayaran Terkirim!</p>
                            <p class="text-xs text-blue-700 mt-1">Menunggu proses verifikasi admin Resto (1×24 Jam).</p>
                        </div>
                    @else
                        <template x-if="!isExpired || {{ $isPelunasan ? 'true' : 'false' }}">
                            <div class="bg-white rounded-2xl border border-gray-200/80 p-6 space-y-5" x-data="{ copied: false }">

                                {{-- Nominal Transfer — satu-satunya sumber angka besar, langsung di atas aksi --}}
                                <div class="bg-[#0D3024] text-white rounded-xl p-4">
                                    <p class="text-emerald-300 text-[10px] font-bold uppercase mb-1">
                                        Nominal Transfer ({{ $isPelunasan ? 'Pelunasan' : 'DP '.$dpPersen.'%' }})
                                    </p>
                                    <p class="text-2xl font-bold">Rp {{ number_format($amountToPay, 0, ',', '.') }}</p>
                                </div>

                                {{-- 1. Informasi Rekening --}}
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-2">1. Transfer ke Rekening</label>
                                    <div class="p-4 bg-emerald-50/60 border border-emerald-200/70 rounded-xl space-y-2">
                                        <div class="flex justify-between items-center">
                                            <span class="text-[11px] font-bold text-emerald-800 uppercase tracking-wider">Bank BCA</span>
                                            <span class="text-[11px] font-medium text-emerald-700">A/N HENI</span>
                                        </div>
                                        <div class="text-lg font-bold font-mono text-[#0D3024] tracking-wider">2780378231</div>
                                        <button type="button" @click="navigator.clipboard.writeText('2780378231'); copied = true; setTimeout(() => copied = false, 2000)"
                                                class="w-full py-2 bg-white hover:bg-emerald-100/50 border border-emerald-300 text-emerald-900 text-xs font-bold rounded-lg transition-all flex items-center justify-center gap-1.5">
                                            <template x-if="!copied"><span class="font-bold">Salin Nomor Rekening</span></template>
                                            <template x-if="copied"><span class="font-bold text-emerald-700">Berhasil Disalin!</span></template>
                                        </button>
                                    </div>
                                </div>

                                {{-- 2. Form Upload Bukti --}}
                                <form action="{{ route('pesanan.upload_bukti') }}" method="POST" enctype="multipart/form-data" class="space-y-4"
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
                                                    <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-[#0D3024] text-white text-xs font-semibold">Pilih File</span>
                                                </div>
                                            </template>

                                            <template x-if="filePreview">
                                                <div>
                                                    <template x-if="!filePreview.startsWith('data:application/pdf')">
                                                        <img :src="filePreview" class="max-h-36 mx-auto rounded-lg border border-gray-200 object-cover mb-2">
                                                    </template>
                                                    <template x-if="filePreview.startsWith('data:application/pdf')">
                                                        <div class="h-16 w-16 mx-auto flex items-center justify-center bg-gray-100 rounded-lg mb-2">
                                                            <svg class="w-8 h-8 text-red-500" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-9.5 8.5c0 .8-.7 1.5-1.5 1.5H7v2H5.5V9H8c.8 0 1.5.7 1.5 1.5v1zm5 2c0 .8-.7 1.5-1.5 1.5h-2.5V9H13c.8 0 1.5.7 1.5 1.5v3zm4-3H17v1.5h1.5v1.5H17V17h-1.5V9h3v1.5zM7 10.5h1v1H7v-1zm6 0h1v2h-1v-2z"/></svg>
                                                        </div>
                                                    </template>
                                                    <p class="text-xs font-bold text-gray-800 truncate" x-text="fileName"></p>
                                                    <button type="button" @click="filePreview = null; fileName = ''; $refs.fileInput.value = ''"
                                                            class="mt-2 text-xs text-red-500 font-bold hover:underline">Ganti File</button>
                                                </div>
                                            </template>

                                            <input type="file" x-ref="fileInput" name="file_bukti" accept="image/jpeg,image/png,application/pdf" class="hidden" required
                                                   @change="
                                                        const file = $event.target.files[0];
                                                        if(file) {
                                                            if (file.size > 1048576) { window.showToast('error', 'Ukuran file maksimal 1MB'); $refs.fileInput.value = ''; return; }
                                                            const validTypes = ['image/jpeg', 'image/png', 'application/pdf'];
                                                            if (!validTypes.includes(file.type)) { window.showToast('error', 'Format file tidak didukung. Harap upload JPG, PNG, atau PDF.'); $refs.fileInput.value = ''; return; }
                                                            fileName = file.name;
                                                            const reader = new FileReader();
                                                            reader.onload = (e) => { filePreview = e.target.result; };
                                                            reader.readAsDataURL(file);
                                                        }
                                                   ">
                                        </div>
                                        @error('file_bukti') <p class="text-xs text-red-600 font-medium mt-1">{{ $message }}</p> @enderror
                                    </div>

                                    <button type="submit"
                                            class="w-full py-3 bg-[#0D3024] hover:bg-[#1a4a35] text-white font-bold text-xs rounded-xl flex items-center justify-center gap-2 transition-all duration-200 active:scale-[0.99]">
                                        Kirim Bukti Pembayaran
                                    </button>
                                </form>
                            </div>
                        </template>
                    @endif
                </div>

                {{-- ── KANAN (42%): REFERENSI — Ringkasan, Rincian Pesanan, Menu, Info ── --}}
                <div class="lg:col-span-5 space-y-6 lg:order-2">

                    {{-- Ringkasan Tagihan (breakdown saja, tanpa duplikasi angka besar) --}}
                    <div class="bg-white rounded-2xl border border-gray-200/80 p-6 space-y-3">
                        <h3 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-3 mb-1">Ringkasan Tagihan</h3>
                        <div class="space-y-2.5 text-xs">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-500">Subtotal</span>
                                <span class="font-bold text-gray-900">Rp {{ number_format($pesanan->total_tagihan, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-500">DP {{ $dpPersen }}% (Uang Muka)</span>
                                <span class="font-bold text-gray-900">Rp {{ number_format($dpAmount, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center pt-2 border-t border-gray-100">
                                <span class="text-gray-500">Sisa Pelunasan</span>
                                <span class="font-bold text-red-600">Rp {{ number_format($pesanan->total_tagihan - $dpAmount, 0, ',', '.') }}</span>
                            </div>
                        </div>
                        @if($dpTerbayar > 0 || $lunas >= $pesanan->total_tagihan)
                        <div class="pt-4 border-t border-gray-100 mt-4">
                            <a href="{{ route('pesanan.invoice', $pesanan->id_pesanan) }}" target="_blank"
                               class="w-full py-2.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200/80 font-bold text-xs rounded-xl flex items-center justify-center gap-2 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                Unduh Bukti Pembayaran / Invoice
                            </a>
                        </div>
                        @endif
                    </div>

                    {{-- Rincian Pesanan --}}
                    <div class="bg-white rounded-2xl border border-gray-200/80 p-6">
                        <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-4">
                            <h2 class="text-sm font-bold text-gray-900">Rincian Pesanan</h2>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border {{ $badgeClass }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $badgeDot }}"></span>
                                {{ $badgeText }}
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

                    {{-- Detail Menu Pilihan — collapsible, default tertutup --}}
                    <div class="bg-white rounded-2xl border border-gray-200/80 overflow-hidden" x-data="{ open: false }">
                        <button type="button" @click="open = !open" class="w-full flex items-center justify-between px-6 py-4 text-left">
                            <span class="text-xs font-bold text-gray-700 uppercase tracking-wider">Detail Menu Pilihan</span>
                            <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" x-collapse class="px-6 pb-5 border-t border-gray-100">
                            <table class="w-full text-left border-collapse mt-3">
                                <thead>
                                    <tr class="border-b border-gray-100 text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                                        <th class="py-2 px-2 font-bold">Kategori</th>
                                        <th class="py-2 px-2 font-bold">Pilihan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50 text-xs">
                                    @forelse($pesanan->detail_pesanan as $detail)
                                        @forelse($detail->pilihan_pesanan_catering as $pilihan)
                                            <tr>
                                                <td class="py-2.5 px-2 text-gray-500">{{ $pilihan->komponen_paket->nama_komponen }}</td>
                                                <td class="py-2.5 px-2 font-medium text-gray-900">{{ $pilihan->pilihan_komponen_paket->nama_pilihan }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td class="py-2.5 px-2 text-gray-500">Menu</td>
                                                <td class="py-2.5 px-2 font-medium text-gray-900">{{ $detail->menu->nama_menu ?? '-' }}</td>
                                            </tr>
                                        @endforelse
                                    @empty
                                        <tr><td colspan="2" class="py-4 px-2 text-center text-gray-400 italic">Tidak ada detail menu.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Informasi Penting --}}
                    <div class="bg-blue-50/50 rounded-2xl border border-blue-100 p-6">
                        <h3 class="text-xs font-bold text-blue-900 mb-3">Informasi Penting</h3>
                        <ul class="list-disc pl-4 space-y-1.5 text-[11px] text-blue-800 leading-relaxed">
                            <li>DP (Uang Muka) tidak dapat dikembalikan jika pesanan dibatalkan oleh konsumen.</li>
                            <li>Pelunasan wajib dilakukan maksimal H-4 sebelum tanggal acara.</li>
                            <li>Jika tidak melakukan pelunasan hingga batas waktu, pesanan akan dianggap batal.</li>
                        </ul>
                    </div>
                </div>

            </div>
        </main>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('paymentPage', (expireTimeStr) => ({
                expireTime: expireTimeStr ? new Date(expireTimeStr).getTime() : null,
                days: 0,
                hours: '00',
                minutes: '15',
                seconds: '00',
                isExpired: false,
                init() {
                    if (!this.expireTime) return;
                    this.updateTimer();
                    setInterval(() => { this.updateTimer(); }, 1000);
                },
                updateTimer() {
                    if (!this.expireTime) return;
                    const now = new Date().getTime();
                    const distance = this.expireTime - now;
                    if (distance <= 0) {
                        this.isExpired = true;
                        this.days = 0;
                        this.hours = '00';
                        this.minutes = '00';
                        this.seconds = '00';
                        return;
                    }
                    const d = Math.floor(distance / (1000 * 60 * 60 * 24));
                    const h = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const m = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const s = Math.floor((distance % (1000 * 60)) / 1000);
                    
                    this.days = d;
                    this.hours = h < 10 ? '0' + h : h;
                    this.minutes = m < 10 ? '0' + m : m;
                    this.seconds = s < 10 ? '0' + s : s;
                }
            }));
        });
    </script>
    @endpush
</x-layouts.landing>