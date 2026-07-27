<x-layouts.landing>
    <x-slot:title>Status Pesanan — Saung Babakan Cinta</x-slot:title>

    <section class="py-16 bg-canvas min-h-screen">
        <div class="max-w-3xl mx-auto px-4">
            
            <div class="text-center mb-8">
                <h1 class="text-3xl font-serif text-primary mb-2">Status Pesanan</h1>
                <p class="text-body">Gunakan halaman ini untuk memantau status pesanan dan pelunasan Anda.</p>
            </div>

            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 rounded-xl p-4 mb-6 flex gap-3 shadow-sm">
                    <i class="fa-solid fa-circle-check text-xl shrink-0 mt-0.5 text-green-600"></i>
                    <p class="font-medium text-sm leading-relaxed">{{ session('success') }}</p>
                </div>
            @endif

            <div class="bg-surface rounded-2xl border border-primary/10 shadow-sm overflow-hidden mb-6">
                <div class="bg-primary/5 p-6 border-b border-primary/10">
                    <div class="flex flex-wrap justify-between items-center gap-4">
                        <div>
                            <p class="text-xs text-secondary font-bold uppercase tracking-wider mb-1">Kode Pesanan</p>
                            <p class="text-2xl font-bold text-primary tracking-wider">{{ $pesanan->kode_pesanan }}</p>
                        </div>
                        <div class="text-right flex flex-col gap-2 items-end">
                            <div>
                                <p class="text-xs text-secondary font-bold uppercase tracking-wider mb-1">Status Saat Ini</p>
                                @php
                                    $statusColors = [
                                        'menunggu_dp' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
                                        'menunggu_konfirmasi' => 'bg-blue-100 text-blue-800 border-blue-300',
                                        'terkonfirmasi' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                                        'lunas' => 'bg-green-600 text-white shadow-sm',
                                        'diproses' => 'bg-indigo-100 text-indigo-800 border-indigo-300',
                                        'dikirim' => 'bg-sky-100 text-sky-800 border-sky-300',
                                        'selesai' => 'bg-gray-100 text-gray-800 border-gray-300',
                                        'dibatalkan' => 'bg-red-100 text-red-800 border-red-300',
                                    ];
                                    $color = $statusColors[$pesanan->status] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                                    
                                    $statusText = [
                                        'menunggu_dp' => 'Menunggu DP',
                                        'menunggu_konfirmasi' => 'Menunggu Verifikasi',
                                        'terkonfirmasi' => 'Dikonfirmasi (Menunggu Pelunasan)',
                                        'lunas' => 'LUNAS (Siap Produksi/Pengantaran)',
                                        'diproses' => 'Dalam Produksi',
                                        'dikirim' => 'Dalam Pengantaran',
                                        'selesai' => 'Selesai',
                                        'dibatalkan' => 'Dibatalkan',
                                    ];
                                    $displayText = $statusText[$pesanan->status] ?? strtoupper(str_replace('_', ' ', $pesanan->status));
                                @endphp
                                <span class="px-3.5 py-1.5 {{ $color }} rounded-full text-xs font-bold tracking-wider inline-flex items-center gap-1.5 border">
                                    <i class="fa-solid fa-circle text-[8px]"></i> {{ $displayText }}
                                </span>
                            </div>
                            
                            @if(in_array($pesanan->status, ['terkonfirmasi', 'lunas', 'diproses', 'selesai']))
                                <a href="{{ route('pesanan.invoice', $pesanan->kode_pesanan) }}" target="_blank" class="inline-flex items-center gap-2 mt-1 px-4 py-1.5 bg-white border border-primary/20 text-primary hover:bg-primary hover:text-white rounded-xl text-xs font-bold transition-all shadow-sm">
                                    <i class="fa-solid fa-file-pdf"></i> Unduh Invoice PDF
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="p-6 space-y-6">
                    @if($pesanan->status === 'menunggu_dp')
                        <div class="bg-amber-50 border border-amber-200 text-amber-900 p-5 rounded-2xl flex items-start gap-4">
                            <i class="fa-solid fa-clock-rotate-left text-2xl text-amber-600 shrink-0 mt-1"></i>
                            <div>
                                <p class="font-bold text-base mb-1">Menunggu Pembayaran DP</p>
                                <p class="text-sm text-amber-800 leading-relaxed mb-3">Pesanan Anda telah dibuat. Silakan lakukan pembayaran DP untuk mengunci jadwal acara Anda.</p>
                                <a href="{{ route('pesanan.bayar', $pesanan->kode_pesanan) }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary text-white rounded-xl font-bold text-sm hover:bg-primary/90 transition-colors shadow-sm">
                                    <i class="fa-solid fa-credit-card"></i> Bayar DP Sekarang
                                </a>
                            </div>
                        </div>
                    @elseif($pesanan->status === 'menunggu_konfirmasi')
                        <div class="bg-blue-50 border border-blue-200 text-blue-900 p-5 rounded-2xl flex items-start gap-4">
                            <i class="fa-solid fa-spinner animate-spin text-2xl text-blue-600 shrink-0 mt-1"></i>
                            <div>
                                <p class="font-bold text-base mb-1">Pembayaran Sedang Diverifikasi</p>
                                <p class="text-sm text-blue-800 leading-relaxed">Admin kami sedang melakukan verifikasi pembayaran Anda. Status pesanan akan diperbarui setelah pembayaran dikonfirmasi.</p>
                            </div>
                        </div>
                    @elseif($pesanan->status === 'terkonfirmasi')
                        <div class="bg-emerald-50 border border-emerald-200 text-emerald-950 p-5 rounded-2xl flex items-start gap-4">
                            <i class="fa-solid fa-circle-check text-2xl text-emerald-600 shrink-0 mt-1"></i>
                            <div class="flex-1">
                                <p class="font-bold text-base mb-1 text-emerald-900">Pesanan Dikonfirmasi & DP Telah Diterima!</p>
                                @php
                                    $diff = \Carbon\Carbon::today()->diffInDays($pesanan->tanggal_acara, false);
                                    $sisa = max(0, $pesanan->total_tagihan - $pesanan->dp_amount);
                                @endphp
                                <p class="text-sm text-emerald-800 leading-relaxed mb-4">
                                    DP sebesar <strong class="text-emerald-950">Rp {{ number_format($pesanan->dp_amount, 0, ',', '.') }}</strong> telah diterima. 
                                    Silakan lakukan <strong>Pelunasan sebesar Rp {{ number_format($sisa, 0, ',', '.') }}</strong> sebelum jadwal acara/pengantaran pada <strong>{{ $pesanan->tanggal_acara->format('d M Y') }}</strong>.
                                    @if($diff > 0) (Acara berlangsung {{ $diff }} hari lagi) @endif
                                </p>
                                <div class="flex flex-wrap gap-3">
                                    <button onclick="document.getElementById('modalPelunasan').classList.remove('hidden')" class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-700 hover:bg-emerald-800 text-white rounded-xl font-bold text-sm transition-all shadow-sm">
                                        <i class="fa-solid fa-upload"></i> Upload Bukti Pelunasan
                                    </button>
                                    <a href="{{ route('pesanan.bayar', $pesanan->kode_pesanan) }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white border border-emerald-700 text-emerald-800 hover:bg-emerald-50 rounded-xl font-bold text-sm transition-all">
                                        <i class="fa-solid fa-credit-card"></i> Bayar Online via Midtrans
                                    </a>
                                </div>
                            </div>
                        </div>
                    @elseif(in_array($pesanan->status, ['lunas', 'diproses', 'dikirim']))
                        <div class="bg-green-600 text-white p-5 rounded-2xl flex items-start gap-4 shadow-md">
                            <i class="fa-solid fa-circle-check text-3xl shrink-0 mt-1"></i>
                            <div>
                                <p class="font-bold text-lg mb-1">Pesanan LUNAS & Siap Diproduksi/Diantar!</p>
                                <p class="text-sm text-green-100 leading-relaxed">
                                    Seluruh pembayaran telah LUNAS dan tercatat resmi di riwayat pembayaran. Pesanan Anda siap diproses dan dikirim sesuai jadwal tanggal <strong>{{ $pesanan->tanggal_acara->format('d M Y') }}</strong>.
                                </p>
                            </div>
                        </div>
                    @elseif($pesanan->status === 'dibatalkan')
                        <div class="bg-red-50 border border-red-200 text-red-900 p-5 rounded-2xl flex items-start gap-4">
                            <i class="fa-solid fa-circle-xmark text-2xl text-red-600 shrink-0 mt-1"></i>
                            <div>
                                <p class="font-bold text-base mb-1">Pesanan Dibatalkan</p>
                                <p class="text-sm text-red-800 leading-relaxed">Pesanan ini telah dibatalkan. Hubungi admin untuk informasi lebih lanjut.</p>
                            </div>
                        </div>
                    @endif

                    {{-- Detail Tagihan & Ringkasan Pelunasan --}}
                    <div class="border border-gray-100 rounded-2xl p-5 bg-gray-50/50">
                        <h3 class="font-serif text-lg text-primary mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-receipt text-secondary"></i> Rincian Tagihan & Pelunasan
                        </h3>
                        <div class="space-y-3 text-sm text-body">
                            <div class="flex justify-between">
                                <span>Metode Pengiriman</span>
                                <span class="font-medium capitalize">{{ $pesanan->metode_pengiriman }}</span>
                            </div>
                            @if($pesanan->metode_pengiriman === 'delivery')
                                <div class="flex justify-between">
                                    <span>Ongkos Kirim {{ $pesanan->jarak_km ? '('.$pesanan->jarak_km.' km)' : '' }}</span>
                                    <span class="font-medium">Rp {{ number_format($pesanan->ongkos_kirim, 0, ',', '.') }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between">
                                <span>Total Tagihan</span>
                                <span class="font-medium text-gray-900">Rp {{ number_format($pesanan->total_tagihan, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-emerald-700">
                                <span class="flex items-center gap-1.5"><i class="fa-solid fa-check-double text-xs"></i> DP Terbayar</span>
                                <span class="font-semibold">Rp {{ number_format($pesanan->dp_amount, 0, ',', '.') }}</span>
                            </div>
                            @php
                                $sisaTagihan = max(0, $pesanan->total_tagihan - $pesanan->dp_amount);
                            @endphp
                            <div class="pt-3 border-t border-gray-200 flex justify-between text-base">
                                <span class="font-bold text-gray-900">Sisa Pelunasan</span>
                                @if(in_array($pesanan->status, ['lunas', 'diproses', 'dikirim', 'selesai']))
                                    <span class="font-bold text-green-600 bg-green-100 px-3 py-0.5 rounded-full text-sm">Rp 0 (LUNAS)</span>
                                @else
                                    <span class="font-extrabold text-secondary text-lg">Rp {{ number_format($sisaTagihan, 0, ',', '.') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Riwayat Bukti Pembayaran --}}
                    @if($pesanan->buktiPembayarans && $pesanan->buktiPembayarans->isNotEmpty())
                        <div class="border-t border-gray-100 pt-6">
                            <h3 class="font-serif text-lg text-primary mb-4 flex items-center gap-2">
                                <i class="fa-solid fa-history text-secondary"></i> Riwayat Pembayaran
                            </h3>
                            <div class="space-y-3">
                                @foreach($pesanan->buktiPembayarans as $bukti)
                                    <div class="flex items-center justify-between p-4 bg-white border border-gray-200 rounded-xl">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-lg bg-primary/10 text-primary flex items-center justify-center font-bold">
                                                <i class="fa-solid fa-file-invoice"></i>
                                            </div>
                                            <div>
                                                <p class="font-bold text-sm text-gray-900 uppercase">Pembayaran {{ $bukti->jenis_pembayaran }}</p>
                                                <p class="text-xs text-gray-500">{{ $bukti->created_at->format('d M Y, H:i') }} WIB</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <span class="px-3 py-1 text-xs font-bold rounded-full {{ $bukti->status === 'verified' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                {{ $bukti->status === 'verified' ? 'TERVERIFIKASI' : 'MENUNGGU VERIFIKASI' }}
                                            </span>
                                            @if($bukti->file_path)
                                                <a href="{{ asset('storage/' . $bukti->file_path) }}" target="_blank" class="text-primary hover:underline text-xs font-semibold">
                                                    Lihat Bukti
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                </div>
            </div>

            {{-- Form Cek Pesanan Lain --}}
            <div class="text-center mt-10">
                <p class="text-sm text-body mb-3">Ingin mengecek pesanan lain?</p>
                <div class="max-w-xs mx-auto">
                    <form onsubmit="event.preventDefault(); window.location.href = '/pesan/status/' + document.getElementById('kodeCek').value;">
                        <div class="flex gap-2">
                            <input type="text" id="kodeCek" placeholder="Masukkan Kode Pesanan" required
                                   class="flex-1 border border-gray-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:border-primary transition">
                            <button type="submit" class="bg-primary text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-primary/90 transition">
                                Cek
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </section>

    {{-- Modal Upload Bukti Pelunasan --}}
    <div id="modalPelunasan" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl relative animate-in fade-in zoom-in duration-200">
            <div class="flex justify-between items-center mb-4 border-b border-gray-100 pb-3">
                <h3 class="font-bold text-lg text-primary flex items-center gap-2">
                    <i class="fa-solid fa-upload text-secondary"></i> Upload Bukti Pelunasan
                </h3>
                <button onclick="document.getElementById('modalPelunasan').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 text-xl font-bold">
                    &times;
                </button>
            </div>

            <form action="{{ route('pesanan.bukti.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="kode_pesanan" value="{{ $pesanan->kode_pesanan }}">
                <input type="hidden" name="jenis_pembayaran" value="pelunasan">

                <div class="mb-4 bg-primary/5 p-4 rounded-xl text-sm text-primary">
                    <p class="font-semibold mb-1">Jumlah Pelunasan:</p>
                    <p class="text-2xl font-bold text-secondary">Rp {{ number_format(max(0, $pesanan->total_tagihan - $pesanan->dp_amount), 0, ',', '.') }}</p>
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Upload Foto / Struk / PDF Bukti Transfer</label>
                    <input type="file" name="file_bukti" accept="image/*,application/pdf" required class="w-full border border-gray-300 rounded-xl p-2.5 text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary file:text-white hover:file:bg-primary/90">
                    <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG, atau PDF (Maks. 2MB)</p>
                </div>

                <div class="flex gap-3">
                    <button type="button" onclick="document.getElementById('modalPelunasan').classList.add('hidden')" class="flex-1 px-4 py-2.5 border border-gray-300 text-gray-700 rounded-xl text-sm font-semibold hover:bg-gray-50">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2.5 bg-primary text-white rounded-xl text-sm font-bold hover:bg-primary/90 shadow-sm">
                        Kirim Bukti Pelunasan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.landing>
