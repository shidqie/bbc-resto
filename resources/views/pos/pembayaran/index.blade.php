<x-layouts.landing>
    @php
        $dpAmount = $pesanan->total_tagihan * 0.5;
        $dpTerbayar = (float) $pesanan->pembayaran->whereIn('status_pembayaran_id', [2, 3])->sum('jumlah_bayar');
        $lunas = (float) $pesanan->pembayaran->where('status_pembayaran_id', 3)->sum('jumlah_bayar');
        $isPelunasan = $lunas >= $dpAmount && $lunas < $pesanan->total_tagihan;
        $amountToPay = $isPelunasan ? max(0, $pesanan->total_tagihan - $lunas) : max(0, $dpAmount - $dpTerbayar);
        $payTitle = $isPelunasan ? 'Pelunasan Tagihan' : 'Pembayaran Uang Muka';
        $payDesc = $isPelunasan ? 'Selesaikan pembayaran untuk memulai proses pengiriman.' : 'Selesaikan pembayaran awal untuk mengonfirmasi pesanan Anda.';
        $namaPemesan = optional($pesanan->pelanggan)->nama ?? $pesanan->jadwal_pesanan->nama_penerima;
        $paket = $pesanan->detail_pesanan->first();
    @endphp

    <x-slot:title>{{ $payTitle }}</x-slot:title>

    <div class="min-h-screen bg-[#FFFFFF] text-[#111827] font-['Google_Sans'] selection:bg-[#3B82F6] selection:text-white">
        <!-- Minimalist Header -->
        <header class="py-12 border-b border-gray-100">
            <div class="max-w-6xl mx-auto px-6 md:px-12 flex justify-between items-end">
                <div>
                    <h1 class="text-[40px] font-medium leading-tight tracking-tight mb-2 text-[#0F2E23]">{{ $payTitle }}</h1>
                    <p class="text-gray-500 text-[16px] font-light">{{ $payDesc }}</p>
                </div>
                <div class="hidden sm:block text-right">
                    <div class="font-['Anonymous_Pro'] text-[14px] text-gray-400 mb-1">INVOICE NO.</div>
                    <div class="font-['Anonymous_Pro'] text-[18px] font-medium tracking-wider text-gray-800">{{ $pesanan->nomor_pesanan }}</div>
                </div>
            </div>
        </header>

        <main class="max-w-6xl mx-auto px-6 md:px-12 py-12 lg:py-16 font-['Google_Sans']">
            
            @if(session('success'))
                <div class="mb-8 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-3xl text-sm font-medium">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="mb-8 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-3xl text-sm font-medium">{{ session('error') }}</div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16">
                
                <!-- Left Column: Order Summary & Billing -->
                <div class="space-y-10">
                    <!-- Order Details Section -->
                    <div>
                        <h2 class="text-xl font-medium mb-6 text-gray-900 border-b border-gray-100 pb-3">Ringkasan Pesanan</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-6">
                            <div class="space-y-1">
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Pemesan</p>
                                <p class="text-base font-medium text-gray-800">{{ $namaPemesan }}</p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Layanan</p>
                                <p class="text-base font-medium text-gray-800">{{ $paket->menu->nama_menu ?? 'Paket' }} <span class="text-gray-400 font-light">&times; {{ $paket->jumlah }} Porsi</span></p>
                            </div>
                            @if($pesanan->jadwal_pesanan)
                            <div class="space-y-1">
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Tanggal Acara</p>
                                <p class="text-base font-medium text-gray-800">{{ \Carbon\Carbon::parse($pesanan->jadwal_pesanan->tanggal_acara)->format('d F Y') }}</p>
                                @if($isPelunasan)
                                <p class="text-xs text-amber-600 font-medium mt-0.5">Batas Pelunasan: {{ \Carbon\Carbon::parse($pesanan->jadwal_pesanan->tanggal_acara)->subDays(2)->format('d F Y') }} (H-2)</p>
                                @endif
                            </div>
                            @endif
                            <div class="space-y-1">
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Status Pesanan</p>
                                <span class="inline-flex items-center px-3.5 py-1 rounded-full text-xs font-bold uppercase tracking-widest bg-amber-50 text-amber-700 border border-amber-200/60">
                                    {{ $isPelunasan ? 'Menunggu Pelunasan' : 'Menunggu DP' }}
                                </span>
                            </div>
                        </div>

                        <!-- Komponen Menu Terpilih -->
                        <div class="mt-8 pt-6 border-t border-gray-100">
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Detail Menu Pilihan</p>
                            <ul class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @forelse($pesanan->detail_pesanan as $detail)
                                    @forelse($detail->pilihan_pesanan_catering as $pilihan)
                                        <li class="flex items-start gap-2.5 text-sm text-gray-700">
                                            <div class="w-4 h-4 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 mt-0.5">
                                                <i class="ph-bold ph-check text-[10px]"></i>
                                            </div>
                                            <span class="leading-relaxed">
                                                <span class="text-gray-400">{{ $pilihan->komponen_paket->nama_komponen }}:</span>
                                                <strong>{{ $pilihan->pilihan_komponen_paket->nama_pilihan }}</strong>
                                            </span>
                                        </li>
                                    @empty
                                        <li class="flex items-start gap-2.5 text-sm text-gray-700">
                                            <div class="w-4 h-4 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 mt-0.5">
                                                <i class="ph-bold ph-check text-[10px]"></i>
                                            </div>
                                            <span class="leading-relaxed">{{ $detail->menu->nama_menu ?? 'Menu' }}</span>
                                        </li>
                                    @endforelse
                                @empty
                                    <li class="text-sm text-gray-400 italic">Tidak ada detail menu.</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>

                    <!-- Billing Breakdown -->
                    <div class="pt-4">
                        <h2 class="text-xl font-medium mb-6 text-gray-900 border-b border-gray-100 pb-3">Rincian Pembayaran</h2>
                        <div class="space-y-3.5 text-sm text-gray-600">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-500">Total Tagihan</span>
                                <span class="text-gray-900 font-medium text-base">Rp {{ number_format($pesanan->total_tagihan, 0, ',', '.') }}</span>
                            </div>

                            @if($isPelunasan)
                            <div class="flex justify-between items-center text-emerald-600">
                                <span>DP Terbayar</span>
                                <span class="font-medium">- Rp {{ number_format($lunas, 0, ',', '.') }}</span>
                            </div>
                            @elseif($dpTerbayar > 0)
                            <div class="flex justify-between items-center text-emerald-600">
                                <span>DP Terbayar</span>
                                <span class="font-medium">- Rp {{ number_format($dpTerbayar, 0, ',', '.') }}</span>
                            </div>
                            @endif
                        </div>
                        
                        <div class="flex justify-between items-center p-5 mt-6 bg-blue-50/40 border border-blue-100/60 rounded-[2.25rem]">
                            <span class="text-xs text-blue-700 font-bold uppercase tracking-widest">
                                {{ $isPelunasan ? 'Sisa Pembayaran' : 'Total Pembayaran' }}
                            </span>
                            <span class="text-2xl sm:text-3xl font-medium text-[#3B82F6] tracking-tight">Rp {{ number_format($amountToPay, 0, ',', '.') }}</span>
                        </div>

                        <!-- Upload Bukti -->
                        <form action="{{ route('pesanan.bukti.store') }}" method="POST" enctype="multipart/form-data" class="mt-6 space-y-4 border border-gray-200/80 rounded-[2.25rem] p-5 bg-gray-50/40">
                            @csrf
                            <input type="hidden" name="kode_pesanan" value="{{ $pesanan->nomor_pesanan }}">
                            <input type="hidden" name="jenis_pembayaran" value="{{ $isPelunasan ? 'pelunasan' : 'dp' }}">
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Unggah Bukti Transfer</p>
                            <p class="text-xs text-gray-500">Kirim bukti pembayaran sebesar <strong class="text-gray-700">Rp {{ number_format($amountToPay, 0, ',', '.') }}</strong> untuk diverifikasi admin (1×24 jam).</p>
                            <input type="file" name="file_bukti" accept="image/*,.pdf" required
                                   class="block w-full text-sm text-gray-600 file:mr-4 file:py-2.5 file:px-4 file:rounded-3xl file:border-0 file:text-xs file:font-bold file:tracking-widest file:uppercase file:bg-blue-600 file:text-white hover:file:bg-blue-700 file:cursor-pointer">
                            @error('file_bukti') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                            <button type="submit" class="w-full flex items-center justify-center gap-2 px-6 py-3.5 bg-[#0F2E23] hover:bg-[#164032] text-white font-bold tracking-widest text-xs uppercase rounded-3xl transition-all border border-[#0F2E23]">
                                <i class="ph-bold ph-upload-simple text-base"></i>
                                Kirim Bukti Pembayaran
                            </button>
                        </form>

                        <div class="mt-6 space-y-2">
                            <a href="{{ route('pesanan.invoice', $pesanan->nomor_pesanan) }}" target="_blank" class="w-full flex items-center justify-center gap-2 px-6 py-3.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold tracking-widest text-xs uppercase rounded-3xl transition-all border border-emerald-200/50">
                                <i class="ph-bold ph-download-simple text-base"></i>
                                Unduh Bukti Pesanan
                            </a>
                            <a href="{{ route('lacak.index', ['kode_pesanan' => $pesanan->nomor_pesanan]) }}" class="w-full flex items-center justify-center gap-2 px-6 py-3.5 bg-[#0F2E23] hover:bg-[#164032] text-white font-bold tracking-widest text-xs uppercase rounded-3xl transition-all border border-[#0F2E23]">
                                <i class="ph-bold ph-magnifying-glass text-base"></i>
                                Lacak / Cek Status Pesanan
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Status Pembayaran -->
                <div class="lg:sticky lg:top-8 h-full">
                    <div class="w-full flex flex-col h-full">
                        <div class="w-full flex-1 border border-gray-200/80 rounded-[2.25rem] overflow-hidden bg-white shadow-lg shadow-gray-200/50">
                            <div class="p-6">
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-5">Status Pembayaran</p>
                                <div class="space-y-4">
                                    <div class="flex items-center gap-4">
                                        <div class="w-9 h-9 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                                            <i class="ph-bold ph-check text-sm"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-gray-900">Pesanan Diterima</p>
                                            <p class="text-xs text-gray-400">Pesanan telah masuk ke sistem</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-4 {{ $dpTerbayar > 0 ? 'opacity-100' : 'opacity-40' }}">
                                        <div class="w-9 h-9 rounded-full {{ $dpTerbayar > 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-gray-100 text-gray-400' }} flex items-center justify-center shrink-0">
                                            <i class="ph-bold {{ $dpTerbayar > 0 ? 'ph-check' : 'ph-clock' }} text-sm"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-gray-900">DP Dibayar</p>
                                            <p class="text-xs text-gray-400">Uang muka sebesar 50% (Rp {{ number_format($dpAmount, 0, ',', '.') }})</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-4 {{ $lunas >= $pesanan->total_tagihan ? 'opacity-100' : 'opacity-40' }}">
                                        <div class="w-9 h-9 rounded-full {{ $lunas >= $pesanan->total_tagihan ? 'bg-emerald-50 text-emerald-600' : 'bg-gray-100 text-gray-400' }} flex items-center justify-center shrink-0">
                                            <i class="ph-bold {{ $lunas >= $pesanan->total_tagihan ? 'ph-check' : 'ph-clock' }} text-sm"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-gray-900">Lunas</p>
                                            <p class="text-xs text-gray-400">Seluruh tagihan terbayar</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if(config('app.env') === 'local')
                            <div class="mt-4 text-center">
                                <button onclick="triggerLocalhostSuccess()" class="w-full text-xs font-bold tracking-widest uppercase text-emerald-700 hover:text-white hover:bg-emerald-600 transition-all bg-emerald-50 px-4 py-3 rounded-3xl border border-emerald-200">
                                    Simulasikan Pembayaran Berhasil (Dev Mode)
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

            </div>

        </main>
    </div>

    @push('scripts')
    <script>
        function triggerLocalhostSuccess() {
            fetch('/api/midtrans/localhost-fallback', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ kode_pesanan: '{{ $pesanan->nomor_pesanan }}' })
            }).then((res) => res.json()).then((data) => {
                if (data.success) {
                    window.location.href = "{{ route('lacak.index', ['kode_pesanan' => $pesanan->nomor_pesanan]) }}";
                } else {
                    alert(data.message || 'Gagal memproses pembayaran.');
                }
            });
        }
    </script>
    @endpush
</x-layouts.landing>
