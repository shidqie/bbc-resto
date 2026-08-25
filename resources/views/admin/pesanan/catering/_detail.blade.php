    @php
        $total = (float) $pesanan->total_tagihan;
        $dpBayar = (float) $pesanan->pembayaran->where('status_verifikasi', 'diterima')->sum('jumlah_dibayar');
        $lunas = (float) $pesanan->pembayaran->where('status_verifikasi', 'diterima')->sum('jumlah_dibayar');
        $isLunas = $lunas >= $total || $dpBayar >= $total;
        $statusBayarLabel = $isLunas ? 'Lunas' : ($dpBayar > 0 ? 'DP Terbayar' : 'Belum Bayar');
        $detailPesanan = $pesanan->detail_pesanan->first();
        $metodeKirim = $pesanan->pengiriman ? 'Diantar' : 'Diambil di Resto';
        $konsumen = $pesanan->pelanggan;
        $namaKonsumen = $konsumen->nama ?? $pesanan->jadwal_pesanan->nama_penerima ?? '-';
        $kontakKonsumen = $konsumen->nomor_telepon ?? $pesanan->jadwal_pesanan->nomor_telepon_penerima ?? '';
        $emailKonsumen = $konsumen->email ?? '';
        $alamatKonsumen = $konsumen->alamat ?? $pesanan->jadwal_pesanan->alamat_pengiriman ?? '-';
        
        $cleanWa = \App\Support\WhatsAppNumber::normalize($kontakKonsumen);
        $hasWa = !empty($cleanWa);
        $displayWa = $kontakKonsumen ? \App\Support\WhatsAppNumber::formatForDisplay($kontakKonsumen) : '';
        $waLink = $hasWa ? 'https://wa.me/' . $cleanWa : null;

        $tanggalAcara = $pesanan->jadwal_pesanan?->tanggal_acara ? \Carbon\Carbon::parse($pesanan->jadwal_pesanan->tanggal_acara)->translatedFormat('d F Y') : '-';
        $waktuAcara = $pesanan->jadwal_pesanan?->tanggal_acara ? \Carbon\Carbon::parse($pesanan->jadwal_pesanan->tanggal_acara)->format('H:i') . ' WIB' : '';
        $jenisPesanan = $pesanan->jenis_pesanan->nama_jenis ?? 'Katering';
        $namaPaket = $detailPesanan->menu->nama_menu ?? 'Paket Katering';
        $jumlahPorsi = $detailPesanan->jumlah ?? 0;
        $kodePesanan = $pesanan->id_pesanan;

        $jenisBayarLabel = ['uang_muka' => 'Uang Muka (DP)', 'pelunasan' => 'Pelunasan', 'pembayaran_penuh' => 'Pembayaran Penuh'];
        $metodeBayarLabel = ['tunai' => 'Tunai', 'transfer_bank' => 'Transfer Bank', 'qris' => 'QRIS', 'midtrans_online' => 'Online / Otomatis'];
        $statusVerifLabel = ['diterima' => 'Diterima', 'menunggu_verifikasi' => 'Menunggu Verifikasi', 'ditolak' => 'Ditolak'];
        $statusVerifColor = ['diterima' => 'bg-green-100 text-green-800', 'menunggu_verifikasi' => 'bg-yellow-100 text-yellow-800', 'ditolak' => 'bg-red-100 text-red-800'];
        $statusVerifCard = ['diterima' => 'bg-green-50/40 border-green-200/60', 'menunggu_verifikasi' => 'bg-yellow-50/40 border-yellow-200/60', 'ditolak' => 'bg-red-50/40 border-red-200/60'];
        
        $dpAmount = round($total * 0.5);
        $sisaPelunasan = max(0, $total - $dpBayar);
        $batasPelunasanStr = $pesanan->batas_pelunasan 
            ? \Carbon\Carbon::parse($pesanan->batas_pelunasan)->translatedFormat('d F Y, H:i') . ' WIB'
            : ($pesanan->jadwal_pesanan?->tanggal_acara ? \Carbon\Carbon::parse($pesanan->jadwal_pesanan->tanggal_acara)->subDays(3)->translatedFormat('d F Y') : 'H-3');
        $linkLacak = url('/lacak-pesanan?kode_pesanan=' . $pesanan->id_pesanan);
        $linkBayar = url('/pesan/bayar/' . $pesanan->id_pesanan);

        $userRole = auth()->user()->peran->nama_peran ?? '';
        $isAdminOrPemilik = in_array($userRole, ['Admin', 'Super Admin', 'Pemilik', 'Manajer']);
        $isDapur = (auth()->user()->hasRole('Dapur', 'Tim Dapur') || in_array($userRole, ['Dapur', 'Tim Dapur', 'Koki'])) && !$isAdminOrPemilik;
    @endphp

    <div class="py-4" id="detail-pesanan-container-{{ $pesanan->id }}"
         x-data="{
            showPengingat: false,
            showBatalModal: false,
            jenisPengingat: 'pesanan',
            pesanWa: '',
            nama: {{ json_encode($namaKonsumen) }},
            kode: {{ json_encode($kodePesanan) }},
            jenis: {{ json_encode($jenisPesanan) }},
            paket: {{ json_encode($namaPaket) }},
            porsi: {{ json_encode($jumlahPorsi) }},
            tanggal: {{ json_encode($tanggalAcara) }},
            waktu: {{ json_encode($waktuAcara) }},
            alamat: {{ json_encode($alamatKonsumen) }},
            metodeKirim: {{ json_encode($metodeKirim) }},
            statusBayar: {{ json_encode($statusBayarLabel) }},
            isSkemaLunas: {{ json_encode($pesanan->isSkemaLunas()) }},
            nominalDp: {{ json_encode(number_format($dpAmount, 0, ',', '.')) }},
            sisaTagihan: {{ json_encode(number_format($sisaPelunasan, 0, ',', '.')) }},
            batasPelunasan: {{ json_encode($batasPelunasanStr) }},
            linkLacak: {{ json_encode($linkLacak) }},
            linkBayar: {{ json_encode($linkBayar) }},
            isLunas: {{ json_encode($isLunas) }},
            waNumber: {{ json_encode($displayWa ?: ($cleanWa ?: ($kontakKonsumen ?: ''))) }},
            init() {
                this.updatePesan();
            },
            updatePesan() {
                const linkLacak = this.linkLacak;
                const linkBayar = this.linkBayar;
                const waktuStr = this.waktu ? ` pukul ${this.waktu}` : '';

                if (this.jenisPengingat === 'pesanan') {
                    this.pesanWa = `Halo ${this.nama}, kami dari RM BBC ingin mengonfirmasi & mengingatkan pesanan ${this.jenis} ${this.paket} sebanyak ${this.porsi} porsi (Kode Pesanan: #${this.kode}) untuk tanggal ${this.tanggal}${waktuStr}.\n\nStatus Pembayaran: ${this.statusBayar}\n\nUntuk melihat rincian dan melacak status pesanan Anda, silakan kunjungi:\n${linkLacak}\n\nMohon pastikan kembali bahwa rincian pesanan dan jadwal acara sudah sesuai. Terima kasih.`;
                } else if (this.jenisPengingat === 'pembayaran_dp') {
                    if (this.isSkemaLunas) {
                        this.pesanWa = `Halo ${this.nama}, kami dari RM BBC ingin mengingatkan pembayaran untuk pesanan ${this.jenis} ${this.paket} (${this.porsi} porsi) dengan kode #${this.kode}.\n\nNominal Tagihan: Rp ${this.nominalDp}\n\nSilakan selesaikan pembayaran dan unggah bukti transfer melalui tautan berikut:\n${linkBayar}\n\nLacak status pesanan:\n${linkLacak}\n\nTerima kasih.`;
                    } else {
                        this.pesanWa = `Halo ${this.nama}, kami dari RM BBC ingin mengingatkan pembayaran Uang Muka (DP 50%) untuk pesanan ${this.jenis} ${this.paket} (${this.porsi} porsi) dengan kode #${this.kode}.\n\nNominal DP: Rp ${this.nominalDp}\n\nSilakan selesaikan pembayaran dan unggah bukti transfer melalui tautan berikut:\n${linkBayar}\n\nLacak status pesanan:\n${linkLacak}\n\nTerima kasih.`;
                    }
                } else if (this.jenisPengingat === 'pelunasan') {
                    this.pesanWa = `Halo ${this.nama}, kami dari RM BBC ingin mengingatkan bahwa pesanan ${this.jenis} ${this.paket} (${this.porsi} porsi) untuk tanggal ${this.tanggal} masih memiliki sisa pelunasan sebesar Rp ${this.sisaTagihan}.\n\nBatas Pelunasan: Maksimal H-3 sebelum acara (${this.batasPelunasan})\n\nSilakan selesaikan pelunasan melalui tautan berikut:\n${linkBayar}\n\nLacak status pesanan:\n${linkLacak}\n\nTerima kasih.`;
                } else if (this.jenisPengingat === 'hari_acara') {
                    this.pesanWa = `Halo ${this.nama}, kami dari RM BBC menginformasikan bahwa pesanan ${this.jenis} ${this.paket} (${this.porsi} porsi) dengan kode #${this.kode} dijadwalkan untuk acara pada tanggal ${this.tanggal}${waktuStr}.\n\nLokasi / Pengiriman: ${this.alamat}\n\nLacak status pesanan:\n${linkLacak}\n\nTim kami sedang mempersiapkan pesanan Anda dengan sebaik-baiknya. Terima kasih.`;
                } else if (this.jenisPengingat === 'pengiriman') {
                    this.pesanWa = `Halo ${this.nama}, kami dari RM BBC menginformasikan bahwa pesanan ${this.jenis} ${this.paket} (${this.porsi} porsi) dengan kode #${this.kode} sedang dipersiapkan untuk ${this.metodeKirim} ke alamat:\n${this.alamat}\n\nLacak status & pengiriman secara langsung:\n${linkLacak}\n\nTerima kasih atas kepercayaan Anda.`;
                }
            },
            bukaModal() {
                const sisaNum = parseFloat(String(this.sisaTagihan || '').replace(/\./g, '')) || 0;
                if (!this.isLunas && sisaNum > 0) {
                    this.jenisPengingat = 'pelunasan';
                } else {
                    this.jenisPengingat = 'pesanan';
                }
                this.updatePesan();
                this.showPengingat = true;
            },
            kirimWa() {
                let num = String(this.waNumber || '').trim().replace(/\D/g, '');
                if (!num) {
                    alert('Mohon masukkan nomor WhatsApp tujuan.');
                    return;
                }
                if (num.startsWith('0')) {
                    num = '62' + num.substring(1);
                } else if (num.startsWith('8')) {
                    num = '62' + num;
                }
                const encoded = encodeURIComponent(this.pesanWa);
                window.open(`https://wa.me/${num}?text=${encoded}`, '_blank');
                this.showPengingat = false;
            }
         }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Top Detail Action Bar with Kirim Pengingat & Close Button --}}
            <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-gray-200 bg-white p-4 rounded-xl shadow-xs">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="font-mono text-sm font-bold bg-gray-100 text-gray-800 px-3 py-1.5 rounded-lg border border-gray-200">#{{ $pesanan->id_pesanan }}</span>
                    @if(!$isDapur)
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $isLunas ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">{{ $statusBayarLabel }}</span>
                    @endif
                    <span class="text-xs text-gray-500 font-medium hidden sm:inline">&bull; {{ $detailPesanan->menu->nama_menu ?? 'Paket Katering' }} ({{ $detailPesanan->jumlah ?? 0 }} Porsi)</span>
                </div>
                <div class="flex items-center gap-2">
                    @if(!$isDapur)
                    <button type="button" 
                            @click="bukaModal()" 
                            class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl transition-all shadow-xs cursor-pointer">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                        <span>Kirim Pengingat</span>
                    </button>
                    @endif
                    <button type="button" @click="$dispatch('close-catering-drawer'); open = false;" class="inline-flex items-center gap-1.5 px-4 py-2 bg-gray-100 hover:bg-red-50 hover:text-red-700 text-gray-700 font-bold text-xs rounded-xl transition-all border border-gray-200 hover:border-red-200 cursor-pointer shadow-xs">
                        <x-heroicon-o-x-mark class="w-4 h-4" />
                        <span>Tutup</span>
                    </button>
                </div>
            </div>

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

            @if($pesanan->status_pesanan_id === 6 && $pesanan->alasan_batal)
                <div class="bg-red-50 border border-red-200 p-4 rounded-lg flex items-start gap-3">
                    <svg class="w-5 h-5 text-red-600 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <h4 class="text-sm font-bold text-red-900">Pesanan Dibatalkan</h4>
                        <p class="text-sm text-red-700 mt-1">{{ $pesanan->alasan_batal }}</p>
                    </div>
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
                    @if(!$isDapur)
                    <div class="mt-4">
                        <a href="{{ route('pengadaan.create', ['pesanan_id' => $pesanan->id]) }}" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded text-sm">Buat Pengadaan</a>
                    </div>
                    @endif
                </div>
            @endif

            {{-- SATU CONTAINER BORDER UTAMA (KONSUMEN, PESANAN, PENGIRIMAN & PEMBAYARAN) --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-xs overflow-hidden grid {{ $isDapur ? 'grid-cols-1' : 'lg:grid-cols-2 divide-y lg:divide-y-0 lg:divide-x divide-gray-200' }}">
                
                {{-- KOLOM KIRI: Informasi Konsumen, Pesanan & Pengiriman --}}
                <div class="p-6 text-gray-900 space-y-6">
                    {{-- 1. Informasi Konsumen --}}
                    <div>
                        <h3 class="text-base font-bold text-gray-900 mb-3 border-b border-gray-100 pb-2 flex items-center gap-2">
                            <x-heroicon-o-user class="w-4 h-4 text-emerald-600" />
                            <span>Informasi Konsumen</span>
                        </h3>
                        <div class="space-y-2.5 text-sm">
                            <div class="grid grid-cols-3"><span class="text-gray-500">Nama Konsumen</span> <span class="col-span-2 font-bold text-gray-900">{{ $namaKonsumen }}</span></div>
                            <div class="grid grid-cols-3 items-center"><span class="text-gray-500">Nomor WhatsApp</span>
                                <div class="col-span-2 flex items-center gap-2.5">
                                    @if($hasWa)
                                        <span class="font-mono text-gray-900 font-semibold">{{ $displayWa }}</span>
                                        <a href="https://wa.me/{{ $cleanWa }}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 rounded-lg text-xs font-bold transition-colors">
                                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                            <span>Hubungi</span>
                                        </a>
                                    @else
                                        <span class="text-gray-400 text-xs italic">Nomor WhatsApp belum tersedia</span>
                                    @endif
                                </div>
                            </div>
                            <div class="grid grid-cols-3"><span class="text-gray-500">Email</span> <span class="col-span-2 text-gray-700">{{ $emailKonsumen ?: '-' }}</span></div>
                            <div class="grid grid-cols-3"><span class="text-gray-500">Alamat</span> <span class="col-span-2 text-gray-700">{{ $alamatKonsumen }}</span></div>
                        </div>
                    </div>

                    {{-- 2. Informasi Pesanan --}}
                    <div>
                        <h3 class="text-base font-bold text-gray-900 mb-3 border-b border-gray-100 pb-2 flex items-center gap-2">
                            <x-heroicon-o-clipboard-document-list class="w-4 h-4 text-emerald-600" />
                            <span>Informasi Pesanan</span>
                        </h3>
                        <div class="space-y-2.5 text-sm">
                            <div class="grid grid-cols-3"><span class="text-gray-500">Kode Pesanan</span> <span class="col-span-2 font-mono text-xs font-bold text-gray-900">{{ $pesanan->id_pesanan }}</span></div>
                            <div class="grid grid-cols-3"><span class="text-gray-500">Jenis Pesanan</span> <span class="col-span-2 font-semibold text-gray-900">{{ $pesanan->jenis_pesanan->nama_jenis ?? '-' }}</span></div>
                            <div class="grid grid-cols-3"><span class="text-gray-500">Paket</span> <span class="col-span-2 font-semibold text-emerald-800">{{ $detailPesanan->menu->nama_menu ?? '-' }}</span></div>
                            <div class="grid grid-cols-3"><span class="text-gray-500">Jumlah Porsi</span> <span class="col-span-2 font-bold text-gray-900">{{ $detailPesanan->jumlah ?? 0 }} Porsi</span></div>
                            <div class="grid grid-cols-3"><span class="text-gray-500">Tanggal Pesan</span> <span class="col-span-2 text-gray-700">{{ $pesanan->tanggal_pesanan ? \Carbon\Carbon::parse($pesanan->tanggal_pesanan)->translatedFormat('d M Y') : '-' }}</span></div>
                            <div class="grid grid-cols-3"><span class="text-gray-500">Tanggal Acara</span> <span class="col-span-2 font-semibold text-gray-900">{{ $pesanan->jadwal_pesanan?->tanggal_acara ? \Carbon\Carbon::parse($pesanan->jadwal_pesanan->tanggal_acara)->translatedFormat('d M Y') : '-' }}</span></div>
                            <div class="grid grid-cols-3"><span class="text-gray-500">Waktu Acara</span> <span class="col-span-2 font-semibold text-gray-900">{{ $pesanan->jadwal_pesanan?->tanggal_acara ? \Carbon\Carbon::parse($pesanan->jadwal_pesanan->tanggal_acara)->format('H:i') . ' WIB' : '-' }}</span></div>
                            <div class="grid grid-cols-3"><span class="text-gray-500">Catatan</span> <span class="col-span-2 text-gray-700">{{ $pesanan->catatan ?: '-' }}</span></div>
                        </div>

                        @php $pilihan = $detailPesanan->pilihan_pesanan_catering ?? collect(); @endphp
                        @if($pilihan->count() > 0)
                            <div class="mt-3 pt-3 border-t border-gray-100">
                                <h4 class="font-bold text-xs text-gray-500 uppercase tracking-wider mb-2">Item Menu:</h4>
                                <ul class="space-y-1.5 text-xs text-gray-700">
                                    @foreach($pilihan as $pil)
                                        <li class="flex items-center gap-1.5">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                                            <span>{{ $pil->komponen_paket->nama_komponen ?? '-' }}: <strong class="text-gray-900 font-semibold">{{ $pil->pilihan_komponen_paket->nama_pilihan ?? '-' }}</strong></span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>

                    {{-- 3. Informasi Pengiriman --}}
                    <div>
                        <h3 class="text-base font-bold text-gray-900 mb-3 border-b border-gray-100 pb-2 flex items-center gap-2">
                            <x-heroicon-o-truck class="w-4 h-4 text-emerald-600" />
                            <span>Informasi Pengiriman</span>
                        </h3>
                        @php
                            $biayaKirim = (float) ($pesanan->pengiriman?->biaya_pengiriman ?? $pesanan->ongkir ?? 0);
                            $alamatRawKirim = $pesanan->pengiriman?->alamat_pengiriman ?? $pesanan->jadwal_pesanan?->alamat_pengiriman ?? '';
                            $isPickupAddr = in_array(strtolower(trim($alamatRawKirim)), ['-', 'diambil di toko (pickup)', 'diambil di resto (pickup)', 'pickup', 'ambil_sendiri', 'diambil']);
                            $rawMetode = strtolower($pesanan->metode_pengiriman ?? optional($pesanan->pengiriman)->metode_pengiriman ?? '');

                            $isDelivery = in_array($rawMetode, ['delivery', 'diantar', 'kurir']) 
                                || !empty($pesanan->pengiriman)
                                || $biayaKirim > 0
                                || (!empty($alamatRawKirim) && !$isPickupAddr);

                            $namaPenerima = $pesanan->pengiriman?->nama_penerima ?? $pesanan->jadwal_pesanan?->nama_penerima ?? $namaKonsumen;
                            $teleponPenerima = $pesanan->pengiriman?->nomor_telepon_penerima ?? $pesanan->jadwal_pesanan?->nomor_telepon_penerima ?? $kontakKonsumen;
                            $alamatPengiriman = $isDelivery ? ($alamatRawKirim ?: ($alamatKonsumen ?: '-')) : 'Diambil di Resto (Pickup)';
                            $jarakKirim = $pesanan->pengiriman?->jarak_pengiriman;
                            $jadwalKirim = $pesanan->pengiriman?->jadwal_pengiriman 
                                ? \Carbon\Carbon::parse($pesanan->pengiriman->jadwal_pengiriman)->translatedFormat('d M Y, H:i') . ' WIB'
                                : ($pesanan->jadwal_pesanan?->waktu_pengiriman ? \Carbon\Carbon::parse($pesanan->jadwal_pesanan->waktu_pengiriman)->format('H:i') . ' WIB' : '-');
                        @endphp
                        <div class="space-y-2.5 text-sm">
                            <div class="grid grid-cols-3">
                                <span class="text-gray-500">Metode Pengiriman</span>
                                <span class="col-span-2">
                                    @if($isDelivery)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                            <x-heroicon-o-truck class="w-3.5 h-3.5 text-emerald-600" />
                                            Diantar
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-700 border border-gray-200">
                                            <x-heroicon-o-building-storefront class="w-3.5 h-3.5 text-gray-500" />
                                            Diambil di Resto
                                        </span>
                                    @endif
                                </span>
                            </div>

                            @if($isDelivery && $pesanan->pengiriman)
                                @php
                                    $shipId = (int) ($pesanan->pengiriman->status_pengiriman_id ?? 1);
                                    $shipConfig = match($shipId) {
                                        1 => ['label' => 'Dijadwalkan', 'color' => 'bg-blue-50 text-blue-800 border-blue-200/90', 'dot' => 'bg-blue-500'],
                                        2 => ['label' => 'Siap Dikirim', 'color' => 'bg-purple-50 text-purple-800 border-purple-200/90', 'dot' => 'bg-purple-500'],
                                        3 => ['label' => 'Dalam Pengantaran', 'color' => 'bg-amber-50 text-amber-800 border-amber-200/90', 'dot' => 'bg-amber-500 animate-pulse'],
                                        4 => ['label' => 'Selesai', 'color' => 'bg-emerald-50 text-emerald-800 border-emerald-200/90', 'dot' => 'bg-emerald-500'],
                                        5 => ['label' => 'Dibatalkan', 'color' => 'bg-rose-50 text-rose-800 border-rose-200/90', 'dot' => 'bg-rose-500'],
                                        default => ['label' => optional($pesanan->pengiriman->status_pengiriman)->nama_status ?? 'Status #'.$shipId, 'color' => 'bg-gray-50 text-gray-700 border-gray-200', 'dot' => 'bg-gray-400'],
                                    };
                                @endphp
                                <div class="grid grid-cols-3">
                                    <span class="text-gray-500">Status Pengiriman</span>
                                    <span class="col-span-2">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full border text-xs font-bold shadow-2xs {{ $shipConfig['color'] }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $shipConfig['dot'] }}"></span>
                                            <span>{{ $shipConfig['label'] }}</span>
                                        </span>
                                    </span>
                                </div>
                            @endif

                            @if($pesanan->pengiriman?->nomor_pengiriman)
                            <div class="grid grid-cols-3">
                                <span class="text-gray-500">No. Pengiriman (DO)</span>
                                <span class="col-span-2 font-mono text-xs font-bold text-gray-900">{{ $pesanan->pengiriman->nomor_pengiriman }}</span>
                            </div>
                            @endif

                            <div class="grid grid-cols-3">
                                <span class="text-gray-500">Penerima</span>
                                <span class="col-span-2 font-semibold text-gray-900">{{ $namaPenerima }}</span>
                            </div>

                            @if($teleponPenerima && $teleponPenerima !== '-')
                            <div class="grid grid-cols-3">
                                <span class="text-gray-500">No. WhatsApp</span>
                                <span class="col-span-2 font-mono text-gray-800">{{ $teleponPenerima }}</span>
                            </div>
                            @endif

                            <div class="grid grid-cols-3">
                                <span class="text-gray-500">Alamat Kirim</span>
                                <span class="col-span-2 text-gray-800">{{ $alamatPengiriman ?: '-' }}</span>
                            </div>

                            <div class="grid grid-cols-3">
                                <span class="text-gray-500">Jadwal Kirim</span>
                                <span class="col-span-2 font-semibold text-gray-900">{{ $jadwalKirim }}</span>
                            </div>

                            @if($jarakKirim)
                            <div class="grid grid-cols-3">
                                <span class="text-gray-500">Estimasi Jarak</span>
                                <span class="col-span-2 text-gray-800">{{ $jarakKirim }} km</span>
                            </div>
                            @endif

                            @if(!$isDapur && $biayaKirim > 0)
                            <div class="grid grid-cols-3">
                                <span class="text-gray-500">Biaya Kirim</span>
                                <span class="col-span-2 font-bold text-emerald-800">Rp {{ number_format($biayaKirim, 0, ',', '.') }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- KOLOM KANAN: Informasi Pembayaran & Riwayat Transaksi --}}
                @if(!$isDapur)
                <div class="p-6 text-gray-900 space-y-6">
                    <div>
                        <h3 class="text-base font-bold text-gray-900 border-b border-gray-100 pb-2 flex items-center gap-2 mb-3">
                            <x-heroicon-o-credit-card class="w-4 h-4 text-emerald-600" />
                            <span>Informasi Pembayaran</span>
                        </h3>

                        {{-- Ringkasan Tagihan (Clean integrated layout without heavy inner card) --}}
                        <div class="space-y-2.5 text-sm py-1">
                            <div class="flex justify-between items-center"><span class="text-gray-500">Total Tagihan</span> <span class="font-bold text-gray-900 font-mono text-base">Rp {{ number_format($total, 0, ',', '.') }}</span></div>
                            <div class="flex justify-between items-center"><span class="text-gray-500">Total Dibayar</span> <span class="font-bold text-emerald-700 font-mono text-base">Rp {{ number_format($dpBayar, 0, ',', '.') }}</span></div>
                            <div class="flex justify-between items-center border-t border-gray-100 pt-2"><span class="text-gray-500 font-semibold">Sisa Tagihan</span> <span class="font-bold text-amber-700 font-mono text-base">Rp {{ number_format(max(0, $total - $dpBayar), 0, ',', '.') }}</span></div>
                        </div>

                        {{-- INFO: Sisa Pelunasan --}}
                        @if(!$isLunas && $dpBayar > 0)
                            <div class="mt-3 bg-yellow-50/80 p-3 rounded-xl border border-yellow-200 text-xs">
                                <h4 class="text-yellow-800 font-bold mb-0.5 flex items-center gap-1.5"><x-heroicon-o-clock class="w-4 h-4" />Menunggu Pelunasan</h4>
                                <p class="text-yellow-700">Konsumen perlu melunasi sisa tagihan sebesar <strong class="text-yellow-900 font-semibold">Rp {{ number_format(max(0, $total - $dpBayar), 0, ',', '.') }}</strong> sebelum pesanan dapat diselesaikan.</p>
                            </div>
                        @endif
                    </div>

                    {{-- Riwayat Transaksi --}}
                    <div>
                        <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3 pb-2 border-b border-gray-100">Riwayat Transaksi</h4>
                        @if($pesanan->pembayaran->isEmpty())
                            <div class="flex flex-col items-center py-6 text-gray-400 bg-gray-50/50 rounded-xl border border-dashed border-gray-200">
                                <x-heroicon-o-banknotes class="w-8 h-8 mb-1.5 opacity-40" />
                                <p class="text-xs italic">Belum ada pembayaran yang diunggah.</p>
                            </div>
                        @else
                            @php
                                $jenisPesanan = $pesanan->jenis_pesanan->kode_jenis ?? 'CAT';
                                $routeVerif = $jenisPesanan === 'BOX' ? 'admin.bukti.nasibox.verifikasi-pembayaran' : 'admin.bukti.verifikasi-pembayaran';
                                $routeTolak = $jenisPesanan === 'BOX' ? 'admin.bukti.nasibox.tolak-pembayaran' : 'admin.bukti.tolak-pembayaran';
                            @endphp
                            <div class="divide-y divide-gray-100 border border-gray-100 rounded-xl overflow-hidden">
                                @foreach($pesanan->pembayaran as $pemb)
                                    @php
                                        $cardBg = $pemb->status_verifikasi === 'diterima' ? 'bg-emerald-50/30' : ($pemb->status_verifikasi === 'ditolak' ? 'bg-red-50/30' : 'bg-gray-50/40');
                                        $isPending = $pemb->status_verifikasi === 'menunggu_verifikasi';
                                    @endphp
                                    <div class="p-3.5 {{ $cardBg }}">
                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                                            <div class="flex-1 grid grid-cols-2 gap-2 text-xs">
                                                <div>
                                                    <p class="text-[10px] text-gray-400 font-semibold uppercase">Jenis</p>
                                                    <p class="font-bold text-gray-900">{{ $jenisBayarLabel[$pemb->jenis_pembayaran] ?? ucwords(str_replace('_',' ',$pemb->jenis_pembayaran ?? '')) }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-[10px] text-gray-400 font-semibold uppercase">Nominal</p>
                                                    <p class="font-bold text-emerald-800">Rp {{ number_format($pemb->jumlah_dibayar, 0, ',', '.') }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-[10px] text-gray-400 font-semibold uppercase">Tanggal</p>
                                                    <p class="font-medium text-gray-700">{{ \Carbon\Carbon::parse($pemb->tanggal_pembayaran)->translatedFormat('d M Y, H:i') }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-[10px] text-gray-400 font-semibold uppercase">Status</p>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $statusVerifColor[$pemb->status_verifikasi] ?? 'bg-gray-100 text-gray-700' }}">
                                                        {{ $statusVerifLabel[$pemb->status_verifikasi] ?? '-' }}
                                                    </span>
                                                </div>
                                            </div>

                                            {{-- Aksi: Lihat Bukti + Setujui/Tolak --}}
                                            <div class="flex items-center gap-1.5 shrink-0 pt-2 sm:pt-0 border-t sm:border-t-0 border-gray-100">
                                                @if($pemb->bukti_pembayaran && $pemb->bukti_pembayaran !== 'midtrans_online')
                                                    <button type="button" x-data="" x-on:click.prevent="$dispatch('open-modal', 'bukti-{{ $pemb->id }}')"
                                                       class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-gray-200 text-xs font-semibold text-gray-600 hover:bg-gray-50 transition-colors cursor-pointer">
                                                        <x-heroicon-o-eye class="w-3.5 h-3.5" /> Bukti
                                                    </button>

                                                    <x-modal name="bukti-{{ $pemb->id }}" maxWidth="md">
                                                        <div class="p-4">
                                                            <div class="flex justify-between items-center mb-4 border-b pb-2">
                                                                <h3 class="text-lg font-bold text-gray-900">Bukti Pembayaran</h3>
                                                                <button x-on:click="$dispatch('close-modal', 'bukti-{{ $pemb->id }}')" class="text-gray-400 hover:text-gray-600 bg-gray-100 hover:bg-gray-200 p-1 rounded-md transition-colors cursor-pointer">
                                                                    <x-heroicon-o-x-mark class="w-5 h-5" />
                                                                </button>
                                                            </div>
                                                            <div class="flex justify-center bg-gray-50 rounded-lg overflow-hidden border p-2">
                                                                @if(Str::endsWith(strtolower($pemb->bukti_pembayaran), '.pdf'))
                                                                    <iframe src="{{ asset('storage/' . $pemb->bukti_pembayaran) }}" class="w-full h-96 rounded"></iframe>
                                                                @else
                                                                    <img src="{{ asset('storage/' . $pemb->bukti_pembayaran) }}" alt="Bukti" class="max-w-full max-h-[70vh] object-contain rounded">
                                                                @endif
                                                            </div>
                                                            <div class="mt-4 flex justify-between items-center">
                                                                <p class="text-xs text-gray-500">Diupload: {{ \Carbon\Carbon::parse($pemb->tanggal_pembayaran)->translatedFormat('d M Y, H:i') }}</p>
                                                                <a href="{{ asset('storage/' . $pemb->bukti_pembayaran) }}" target="_blank" class="text-sm text-primary hover:text-primary hover:underline font-semibold flex items-center gap-1">
                                                                    Buka Penuh <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </x-modal>
                                                @endif

                                                @if($isAdminOrPemilik && $isPending)
                                                    <form id="form-verif-{{ $pemb->id }}" action="{{ route($routeVerif, $pemb->id) }}" method="POST" class="hidden">
                                                        @csrf @method('PATCH')
                                                    </form>
                                                    <x-ui.action-button type="button"
                                                        onclick="window.confirmDialog({ title: 'Verifikasi Pembayaran', name: '{{ $pemb->kode_pembayaran }}', message: 'Setujui bukti pembayaran ini? Status pesanan akan diperbarui secara otomatis.', formId: 'form-verif-{{ $pemb->id }}', confirmText: 'Setujui', cancelText: 'Batal' })"
                                                        title="Setujui"
                                                        class="text-green-600 hover:text-green-800">
                                                        <x-heroicon-o-check-circle class="w-4 h-4" />
                                                    </x-ui.action-button>

                                                    <form id="form-tolak-{{ $pemb->id }}" action="{{ route($routeTolak, $pemb->id) }}" method="POST" class="hidden">
                                                        @csrf @method('PATCH')
                                                        <input type="hidden" name="catatan_admin" id="catatan-tolak-{{ $pemb->id }}" value="">
                                                    </form>
                                                    <x-ui.action-button type="button"
                                                        onclick="window.confirmDialog({ title: 'Tolak Bukti Pembayaran', name: '{{ $pemb->kode_pembayaran }}', message: 'Tolak bukti pembayaran ini? Pelanggan akan diminta mengunggah ulang.', formId: 'form-tolak-{{ $pemb->id }}', confirmText: 'Tolak', cancelText: 'Batal', type: 'danger' })"
                                                        title="Tolak"
                                                        class="text-red-500 hover:text-red-700">
                                                        <x-heroicon-o-x-circle class="w-4 h-4" />
                                                    </x-ui.action-button>
                                                @endif
                                            </div>
                                        </div>

                                        @if($pemb->catatan_verifikasi)
                                            <p class="mt-2 text-xs text-gray-500 italic border-t border-gray-200/60 pt-1.5">
                                                <span class="font-semibold">Catatan:</span> {{ $pemb->catatan_verifikasi }}
                                            </p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
                @endif

            </div>

            {{-- Bahan Baku yang Diperlukan (BOM Resep Menu x Porsi) --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100">
                <div class="p-6 text-gray-900">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4 border-b pb-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold">
                                <x-heroicon-o-beaker class="w-5 h-5" />
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Kebutuhan Bahan Baku</h3>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                {{ count($kebutuhanBahan ?? []) }} Item Bahan Baku
                            </span>
                            @if(!$isDapur && Route::has('pengadaan.po.create'))
                                <a href="{{ route('pengadaan.po.create', ['tipe' => 'Catering', 'kode_pesanan' => $pesanan->id_pesanan]) }}" 
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#0D3024] hover:bg-[#0D3024]/90 text-white rounded-lg text-xs font-bold shadow-sm transition-all">
                                    <x-heroicon-o-shopping-cart class="w-4 h-4" />
                                    Buat Purchase Order (PO)
                                </a>
                            @endif
                        </div>
                    </div>

                    @if(empty($kebutuhanBahan) || count($kebutuhanBahan) === 0)
                        <div class="py-8 text-center text-gray-400">
                            <x-heroicon-o-cube-transparent class="w-10 h-10 mx-auto mb-2 opacity-40" />
                            <p class="text-sm italic">Belum ada resep bahan baku yang terhubung dengan menu ini.</p>
                        </div>
                    @else
                        <div x-data="{
                                page: 1,
                                perPage: 10,
                                totalItems: {{ count($kebutuhanBahan ?? []) }},
                                get totalPages() {
                                    return Math.ceil(this.totalItems / this.perPage) || 1;
                                },
                                get startItem() {
                                    return this.totalItems > 0 ? (this.page - 1) * this.perPage + 1 : 0;
                                },
                                get endItem() {
                                    return Math.min(this.page * this.perPage, this.totalItems);
                                },
                                isVisible(idx) {
                                    return idx >= (this.page - 1) * this.perPage && idx < this.page * this.perPage;
                                },
                                nextPage() {
                                    if (this.page < this.totalPages) this.page++;
                                },
                                prevPage() {
                                    if (this.page > 1) this.page--;
                                },
                                setPage(p) {
                                    this.page = p;
                                }
                             }">
                            <div class="overflow-x-auto rounded-t-xl border border-gray-100">
                                <table class="w-full text-left border-collapse text-sm">
                                    <thead>
                                        <tr class="bg-gray-50/80 border-b border-gray-200 text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            <th class="py-3 px-4 text-center w-12">No</th>
                                            <th class="py-3 px-4">Bahan Baku</th>
                                            <th class="py-3 px-4 text-right w-36">Kebutuhan</th>
                                            <th class="py-3 px-4 text-center w-36">Stok Katering</th>
                                            <th class="py-3 px-4 text-center w-36">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach($kebutuhanBahan as $idx => $bb)
                                        @php
                                            $kebVal = (float) ($bb['total_kebutuhan'] ?? 0);
                                            $stokVal = (float) ($bb['stok_katering'] ?? 0);
                                            $kebFormatted = $kebVal > 0 ? (fmod($kebVal, 1) === 0.0 ? number_format($kebVal, 0, ',', '.') : number_format($kebVal, 2, ',', '.')) : '0';
                                            $stokFormatted = $stokVal > 0 ? (fmod($stokVal, 1) === 0.0 ? number_format($stokVal, 0, ',', '.') : number_format($stokVal, 2, ',', '.')) : '0';
                                            $isCukup = $bb['cukup'] ?? ($stokVal >= $kebVal);
                                        @endphp
                                        <tr x-show="isVisible({{ $idx }})" class="hover:bg-gray-50/60 transition-colors">
                                            <td class="py-2.5 px-4 text-center text-xs text-gray-400 font-semibold">
                                                {{ $idx + 1 }}
                                            </td>
                                            <td class="py-2.5 px-4">
                                                <p class="font-bold text-gray-900 text-sm">{{ $bb['nama_bahan'] }}</p>
                                            </td>
                                            <td class="py-2.5 px-4 text-right">
                                                <span class="font-bold text-emerald-700 font-mono text-sm">{{ $kebFormatted }}</span>
                                                <span class="text-xs text-gray-500 font-medium ml-0.5">{{ $bb['satuan'] }}</span>
                                            </td>
                                            <td class="py-2.5 px-4 text-center">
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-semibold {{ $stokVal > 0 ? 'bg-gray-100 text-gray-700' : 'bg-red-50 text-red-600' }}">
                                                    <span>{{ $stokFormatted }}</span>
                                                    <span class="text-[10px] opacity-75">{{ $bb['satuan'] }}</span>
                                                </span>
                                            </td>
                                            <td class="py-2.5 px-4 text-center">
                                                @if($isCukup)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                                        Tersedia
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                                        Perlu PO
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{-- Pagination Bar --}}
                            <div class="px-4 py-3 border-t border-gray-100 bg-gray-50/40 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-gray-600 rounded-b-xl border border-t-0 border-gray-100">
                                <div>
                                    Menampilkan <span class="font-bold text-gray-900" x-text="startItem"></span> - <span class="font-bold text-gray-900" x-text="endItem"></span> dari <span class="font-bold text-gray-900" x-text="totalItems"></span> bahan baku
                                </div>
                                <div class="flex items-center gap-1.5" x-show="totalPages > 1">
                                    {{-- Prev Button --}}
                                    <button type="button" 
                                            @click="prevPage()" 
                                            :disabled="page === 1"
                                            :class="page === 1 ? 'opacity-40 cursor-not-allowed pointer-events-none' : 'hover:bg-gray-50 cursor-pointer'"
                                            class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-white border border-gray-200 text-gray-700 transition-all">
                                        Sebelumnya
                                    </button>

                                    {{-- Page Numbers --}}
                                    <template x-for="p in totalPages" :key="p">
                                        <button type="button" 
                                                x-show="p === 1 || p === totalPages || (p >= page - 1 && p <= page + 1)"
                                                @click="setPage(p)" 
                                                :class="p === page ? 'bg-primary text-white shadow-xs font-bold' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 font-semibold'"
                                                class="px-2.5 py-1 rounded-lg text-xs transition-all cursor-pointer"
                                                x-text="p">
                                        </button>
                                    </template>

                                    {{-- Next Button --}}
                                    <button type="button" 
                                            @click="nextPage()" 
                                            :disabled="page === totalPages"
                                            :class="page === totalPages ? 'opacity-40 cursor-not-allowed pointer-events-none' : 'hover:bg-gray-50 cursor-pointer'"
                                            class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-white border border-gray-200 text-gray-700 transition-all">
                                        Selanjutnya
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- TOMBOL AKSI STATUS PESANAN & PEMBATALAN MULTIAKTOR --}}
            @php
                $userRoleName = auth()->user()->peran->nama_peran ?? '';
                $isPemilikActor = in_array($userRoleName, ['Admin', 'Super Admin', 'Pemilik']);
                $isDapurActor = in_array($userRoleName, ['Dapur', 'Tim Dapur', 'Koki', 'Admin', 'Super Admin']) || (method_exists(auth()->user(), 'hasRole') && auth()->user()->hasRole('Dapur', 'Tim Dapur', 'Koki'));
                $stPesananId = (int) $pesanan->status_pesanan_id;
                $stBayarId = (int) $pesanan->status_pembayaran_id;
                $hasPengiriman = (bool) $pesanan->pengiriman;
            @endphp

            @if(!in_array($stPesananId, [5, 6]))
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-200/80 p-6" x-data="{ showBatalModal: false }">
                    <div class="flex flex-wrap gap-3 justify-center items-center">

                        {{-- 1. AKSI PEMILIK --}}
                        @if($isPemilikActor)
                            {{-- MENUNGGU (1) -> DIKONFIRMASI (2) --}}
                            @if($stPesananId === 1)
                                @if(in_array($stBayarId, [3, 4, 5]))
                                    <form id="form-konfirmasi-catering" action="{{ route('admin.pesanan.catering.update-status', $pesanan->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="2">
                                        <button type="button" onclick="window.confirmDialog({ title: 'Konfirmasi Pesanan', name: '{{ $pesanan->id_pesanan }}', message: 'Konfirmasi pesanan katering ini?', formId: 'form-konfirmasi-catering', confirmText: 'Konfirmasi', cancelText: 'Batal', type: 'warning' })" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-8 rounded-xl shadow-xs cursor-pointer transition-all flex items-center">
                                            <x-heroicon-o-check class="mr-2 w-5 h-5" />Konfirmasi Pesanan
                                        </button>
                                    </form>
                                @else
                                    <span class="inline-flex items-center px-4 py-2.5 rounded-xl bg-amber-50 text-amber-800 text-xs font-semibold border border-amber-200">
                                        <x-heroicon-o-clock class="w-4 h-4 mr-1.5 text-amber-600" />
                                        <span>Menunggu verifikasi pembayaran DP sebelum dapat dikonfirmasi</span>
                                    </span>
                                @endif
                            @endif

                            {{-- DIKONFIRMASI (2) -> TERJADWAL (7) --}}
                            @if($stPesananId === 2)
                                <form id="form-jadwalkan-catering" action="{{ route('admin.pesanan.catering.update-status', $pesanan->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="7">
                                    <button type="button" onclick="window.confirmDialog({ title: 'Tandai Terjadwal', name: '{{ $pesanan->id_pesanan }}', message: 'Tandai pesanan katering ini sebagai Terjadwal?', formId: 'form-jadwalkan-catering', confirmText: 'Tandai Terjadwal', cancelText: 'Batal', type: 'warning' })" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-xl shadow-xs cursor-pointer transition-all flex items-center">
                                        <x-heroicon-o-calendar-days class="mr-2 w-5 h-5" />Tandai Terjadwal
                                    </button>
                                </form>
                            @endif

                            {{-- SIAP (4) -> SELESAI (5) (JIKA AMBIL SENDIRI) --}}
                            @if($stPesananId === 4 && !$hasPengiriman)
                                <form id="form-selesai-catering" action="{{ route('admin.pesanan.catering.update-status', $pesanan->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="5">
                                    <button type="button" onclick="window.confirmDialog({ title: 'Pesanan Selesai', name: '{{ $pesanan->id_pesanan }}', message: 'Tandai pesanan sebagai selesai (sudah diambil oleh konsumen)?', formId: 'form-selesai-catering', confirmText: 'Selesai', cancelText: 'Batal', type: 'warning' })" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-8 rounded-xl shadow-xs cursor-pointer transition-all flex items-center">
                                        <x-heroicon-o-check-circle class="mr-2 w-5 h-5" />Tandai Pesanan Selesai
                                    </button>
                                </form>
                            @elseif($stPesananId === 4 && $hasPengiriman)
                                <span class="inline-flex items-center px-4 py-2.5 rounded-xl bg-purple-50 text-purple-800 text-xs font-semibold border border-purple-200">
                                    <x-heroicon-o-truck class="w-4 h-4 mr-1.5 text-purple-600" />
                                    <span>Pesanan Siap &bull; Menunggu proses pengantaran oleh kurir</span>
                                </span>
                            @endif
                        @endif

                        {{-- 2. AKSI DAPUR --}}
                        @if($isDapurActor)
                            {{-- DIKONFIRMASI (2) / TERJADWAL (7) -> DIPROSES (3) --}}
                            @if(in_array($stPesananId, [2, 7]))
                                @if(in_array($stBayarId, [3, 4, 5]))
                                    <form id="form-produksi-catering" action="{{ route('admin.pesanan.catering.update-status', $pesanan->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="3">
                                        <button type="button" onclick="window.confirmDialog({ title: 'Mulai Sedang Diproses', name: '{{ $pesanan->id_pesanan }}', message: 'Mulai proses dapur? Stok bahan baku akan dipotong otomatis.', formId: 'form-produksi-catering', confirmText: 'Mulai Masak', cancelText: 'Batal', type: 'warning' })" class="bg-amber-600 hover:bg-amber-700 text-white font-bold py-3 px-8 rounded-xl shadow-xs cursor-pointer transition-all flex items-center">
                                            <x-heroicon-o-fire class="mr-2 w-5 h-5" />Mulai Sedang Diproses
                                        </button>
                                    </form>
                                @else
                                    <span class="inline-flex items-center px-4 py-2.5 rounded-xl bg-yellow-50 text-yellow-800 text-xs font-semibold border border-yellow-200">
                                        <x-heroicon-o-lock-closed class="w-4 h-4 mr-1.5 text-yellow-600" />
                                        <span>Dapur dapat memproses pesanan setelah pembayaran DP atau Lunas diverifikasi</span>
                                    </span>
                                @endif
                            @endif

                            {{-- DIPROSES (3) -> SIAP (4) --}}
                            @if($stPesananId === 3)
                                <form id="form-produksi-selesai-catering" action="{{ route('admin.pesanan.catering.update-status', $pesanan->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="4">
                                    <button type="button" onclick="window.confirmDialog({ title: 'Tandai Pesanan Siap', name: '{{ $pesanan->id_pesanan }}', message: 'Tandai seluruh masakan pesanan telah selesai disiapkan oleh Dapur?', formId: 'form-produksi-selesai-catering', confirmText: 'Pesanan Siap', cancelText: 'Batal', type: 'warning' })" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-8 rounded-xl shadow-xs cursor-pointer transition-all flex items-center">
                                        <x-heroicon-o-sparkles class="mr-2 w-5 h-5" />Tandai Pesanan Siap
                                    </button>
                                </form>
                            @endif
                        @endif

                        {{-- Tombol Batalkan (Hanya Pemilik) --}}
                        @if($isPemilikActor)
                            <button @click="showBatalModal = true" type="button" class="bg-rose-600 hover:bg-rose-700 text-white font-bold py-3 px-8 rounded-xl shadow-xs cursor-pointer transition-all flex items-center">
                                <x-heroicon-o-x-mark class="mr-2 w-5 h-5" />Batalkan Pesanan
                            </button>
                        @endif

                        {{-- Tombol Tutup Drawer --}}
                        <button type="button" @click="$dispatch('close-catering-drawer'); open = false;" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-3 px-8 rounded-xl shadow-xs border border-gray-200 flex items-center cursor-pointer transition-all">
                            <x-heroicon-o-x-mark class="mr-2 w-5 h-5" />Tutup
                        </button>
                    </div>

                    {{-- Modal Pembatalan --}}
                    <template x-teleport="body">
                        <div x-show="showBatalModal" class="fixed inset-0 z-[99999] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                <div x-show="showBatalModal" x-transition.opacity class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity" @click="showBatalModal = false"></div>
                                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                                <div x-show="showBatalModal" x-transition class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100 relative z-10" @click.stop>
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
                                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                                            <button type="submit" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 sm:ml-3 sm:w-auto sm:text-sm cursor-pointer">Konfirmasi Batal</button>
                                            <button type="button" @click="showBatalModal = false" class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm cursor-pointer">Kembali</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            @endif
            @if($pesanan->status_pesanan_id === 6 && $pesanan->catatan && str_contains($pesanan->catatan, '[BATAL:'))
                <div class="bg-red-50 p-4 rounded-xl border border-red-100">
                    <h4 class="text-red-800 font-bold mb-1"><x-heroicon-o-no-symbol class="mr-2 w-5 h-5" />Alasan Pembatalan:</h4>
                    <p class="text-red-700 text-sm">{{ Str::after($pesanan->catatan, '[BATAL:') }}</p>
                </div>
            @endif

        </div>

            {{-- MODAL PENGINGAT WHATSAPP KONSUMEN --}}
        <template x-teleport="body">
            <div x-show="showPengingat" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-[99999] overflow-y-auto" 
                 role="dialog" 
                 aria-modal="true">
                <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity" 
                         @click="showPengingat = false"></div>

                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100 relative z-10"
                         @click.stop>
                        
                        {{-- Modal Header --}}
                        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/70">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold">
                                    <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                </div>
                                <div>
                                    <h3 class="text-base font-bold text-gray-900">Kirim Pengingat Konsumen</h3>
                                    <p class="text-xs text-gray-500">Pesan WhatsApp akan dibuat otomatis</p>
                                </div>
                            </div>
                            <button type="button" @click="showPengingat = false" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-gray-100 transition-colors cursor-pointer">
                                <x-heroicon-o-x-mark class="w-5 h-5" />
                            </button>
                        </div>

                        {{-- Modal Body --}}
                        <div class="p-6 space-y-4">
                            {{-- Nomor WhatsApp --}}
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                    Nomor WhatsApp Tujuan
                                </label>
                                <div class="relative flex items-center">
                                    <span class="absolute left-3 text-emerald-600">
                                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                    </span>
                                    <input type="text" 
                                           x-model="waNumber" 
                                           placeholder="Contoh: 081234567890" 
                                           class="w-full pl-9 pr-3 py-2 text-sm bg-white border border-gray-200 rounded-xl text-gray-800 font-mono font-semibold focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none shadow-xs">
                                </div>
                            </div>

                            {{-- Jenis Pengingat --}}
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                    Jenis Pengingat
                                </label>
                                <div class="relative">
                                    <select x-model="jenisPengingat" 
                                            @change="updatePesan()" 
                                            class="w-full appearance-none py-2.5 pl-3.5 pr-9 text-sm bg-white border border-gray-200 rounded-xl text-gray-900 font-semibold focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none cursor-pointer shadow-xs transition-all">
                                        <option value="pesanan">Pengingat Pesanan</option>
                                        <option value="pembayaran_dp">Pengingat Pembayaran DP</option>
                                        <option value="pelunasan">Pengingat Pelunasan Tagihan</option>
                                        <option value="hari_acara">Pengingat Hari Acara</option>
                                        <option value="pengiriman">Pengingat Pengiriman</option>
                                    </select>
                                    <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400">
                                        <x-heroicon-o-chevron-down class="w-4 h-4" />
                                    </span>
                                </div>
                            </div>

                            {{-- Isi Pesan --}}
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                                        Isi Pesan
                                    </label>
                                    <span class="text-[11px] text-gray-400 italic">Pesan dapat diedit</span>
                                </div>
                                <textarea x-model="pesanWa" 
                                          rows="7" 
                                          class="w-full p-3 text-sm border border-gray-200 rounded-xl text-gray-900 leading-relaxed focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none font-sans"></textarea>
                            </div>
                        </div>

                        {{-- Modal Footer --}}
                        <div class="px-6 py-4 bg-gray-50/80 border-t border-gray-100 flex items-center justify-end gap-2.5">
                            <button type="button" 
                                    @click="showPengingat = false" 
                                    class="px-4 py-2.5 text-xs font-bold text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors shadow-xs cursor-pointer">
                                Batal
                            </button>
                            <button type="button" 
                                    @click="kirimWa()" 
                                    class="inline-flex items-center gap-2 px-5 py-2.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition-all shadow-sm shadow-emerald-600/20 cursor-pointer">
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                <span>Kirim via WhatsApp</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
