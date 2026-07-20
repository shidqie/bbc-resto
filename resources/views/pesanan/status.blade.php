<x-layouts.landing>
    <x-slot:title>Status Pesanan — Saung Babakan Cinta</x-slot:title>

    <section class="py-16 bg-canvas min-h-screen">
        <div class="max-w-3xl mx-auto px-4">
            
            <div class="text-center mb-8">
                <h1 class="text-3xl font-serif text-primary mb-2">Status Pesanan</h1>
                <p class="text-body">Gunakan halaman ini untuk memantau status pesanan Anda.</p>
            </div>

            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 rounded-xl p-4 mb-6 flex gap-3">
                    <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            <div class="bg-surface rounded-2xl border border-primary/10 shadow-sm overflow-hidden mb-6">
                <div class="bg-primary/5 p-6 border-b border-primary/10">
                    <div class="flex flex-wrap justify-between items-center gap-4">
                        <div>
                            <p class="text-sm text-secondary font-semibold mb-1">Kode Pesanan</p>
                            <p class="text-2xl font-bold text-primary tracking-wider">{{ $pesanan->kode_pesanan }}</p>
                        </div>
                        <div class="text-right flex flex-col gap-2 items-end">
                            <div>
                                <p class="text-sm text-secondary font-semibold mb-1">Status Saat Ini</p>
                                @php
                                    $statusColors = [
                                        'menunggu_dp' => 'bg-yellow-100 text-yellow-800',
                                        'menunggu_konfirmasi' => 'bg-blue-100 text-blue-800',
                                        'terkonfirmasi' => 'bg-green-100 text-green-800',
                                        'lunas' => 'bg-green-200 text-green-900',
                                        'dibatalkan' => 'bg-red-100 text-red-800',
                                    ];
                                    $color = $statusColors[$pesanan->status] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="px-3 py-1 {{ $color }} rounded-full text-xs font-bold uppercase tracking-wider">
                                    {{ str_replace('_', ' ', $pesanan->status) }}
                                </span>
                            </div>
                            
                            @if(in_array($pesanan->status, ['terkonfirmasi', 'lunas']))
                                <a href="{{ route('pesanan.invoice', $pesanan->kode_pesanan) }}" target="_blank" class="inline-flex items-center gap-2 mt-2 px-4 py-2 bg-white border border-primary text-primary hover:bg-primary hover:text-white rounded-lg text-sm font-semibold transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                    Unduh Invoice PDF
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="p-6 space-y-6">
                    @if($pesanan->status === 'menunggu_konfirmasi')
                        <div class="bg-blue-50 border border-blue-200 text-blue-800 p-4 rounded-xl flex gap-3">
                            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <div>
                                <p class="font-semibold mb-1">Pembayaran Sedang Diverifikasi</p>
                                <p class="text-sm">Admin kami sedang melakukan verifikasi pembayaran Anda. Status pesanan akan berubah setelah pembayaran dikonfirmasi.</p>
                            </div>
                        </div>
                    @elseif($pesanan->status === 'terkonfirmasi')
                        <div class="bg-green-50 border border-green-200 text-green-800 p-4 rounded-xl flex gap-3">
                            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <div>
                                <p class="font-semibold mb-1">Pesanan Terkonfirmasi!</p>
                                @php
                                    $diff = \Carbon\Carbon::today()->diffInDays($pesanan->tanggal_acara, false);
                                @endphp
                                <p class="text-sm">Pesanan Anda telah kami jadwalkan pada {{ $pesanan->tanggal_acara->format('d M Y') }}. 
                                @if($diff > 0) Acara akan berlangsung dalam {{ $diff }} hari. @endif
                                </p>
                            </div>
                        </div>
                    @elseif($pesanan->status === 'dibatalkan')
                        <div class="bg-red-50 border border-red-200 text-red-800 p-4 rounded-xl flex gap-3">
                            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            <div>
                                <p class="font-semibold mb-1">Pesanan Dibatalkan</p>
                                <p class="text-sm">Pesanan ini telah dibatalkan.</p>
                            </div>
                        </div>
                    @endif

                    <h3 class="font-serif text-lg text-primary">Detail Tagihan</h3>
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
                            <span class="font-medium">Rp {{ number_format($pesanan->total_tagihan, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>DP Dibayarkan</span>
                            <span class="font-medium">Rp {{ number_format($pesanan->dp_amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="pt-3 border-t border-gray-100 flex justify-between text-base">
                            <span class="font-semibold">Sisa Pelunasan</span>
                            <span class="font-bold text-secondary">Rp {{ number_format($pesanan->total_tagihan - $pesanan->dp_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    @if($pesanan->status === 'terkonfirmasi')
                        <div class="mt-6 border-t border-gray-100 pt-6">
                            <h3 class="font-serif text-lg text-primary mb-4">Pelunasan</h3>
                            <p class="text-sm text-body mb-4">Silakan lakukan pelunasan maksimal H-1 sebelum acara. (Pembayaran akan diproses melalui sistem Payment Gateway)</p>
                            
                            {{-- Todo: Tambahkan tombol bayar Midtrans pelunasan di sini jika diperlukan --}}
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
                            <button type="submit" class="bg-primary text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-primary-dark transition">
                                Cek
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </section>
</x-layouts.landing>
