<x-layouts.member>
    <x-slot:title>Pesanan Aktif</x-slot:title>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-box-open text-primary text-xl"></i>
                <h1 class="text-2xl font-bold text-gray-900">Pesanan Aktif</h1>
            </div>
            <div class="bg-primary/10 text-primary px-3 py-1 rounded-full text-sm font-semibold">
                {{ $nasiboxOrders->count() + $cateringOrders->count() + $dineinOrders->count() }} Pesanan
            </div>
        </div>

        <div class="p-6">
            <div class="space-y-6">
                
                {{-- Gabungkan dan urutkan pesanan terbaru di atas --}}
                @php
                    $allOrders = collect($nasiboxOrders)->concat($cateringOrders)->concat($dineinOrders)->sortByDesc('created_at');
                @endphp

                @if($allOrders->isEmpty())
                    <div class="text-center py-12">
                        <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fa-solid fa-receipt text-3xl text-gray-400"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-1">Belum Ada Pesanan Aktif</h3>
                        <p class="text-gray-500 mb-6">Anda belum memiliki pesanan yang sedang diproses.</p>
                        <a href="{{ route('home') }}" class="inline-flex px-6 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 font-medium transition-colors">
                            Lihat Menu Kami
                        </a>
                    </div>
                @else
                    <div class="grid gap-4">
                        @foreach($allOrders as $order)
                            @php
                                $isDinein = isset($order->no_meja);
                                $isCatering = isset($order->paket_catering_id);
                                $cleanMeja = $isDinein ? trim(preg_replace('/^meja\s*/i', '', $order->no_meja)) : '';
                                $jenis = $isDinein ? ('Resto Dine-In (Meja ' . $cleanMeja . ')') : ($isCatering ? 'Catering' : 'Nasi Box');
                                $icon = $isDinein ? 'fa-chair' : ($isCatering ? 'fa-utensils' : 'fa-box');
                                $code = $isDinein ? $order->no_pesanan : $order->kode_pesanan;
                                $status = $isDinein ? $order->status_pesanan : $order->status;
                                $total = $isDinein ? $order->total_harga : $order->total_tagihan;
                                $url = $isDinein ? '#' : route('pesanan.status', $code);
                            @endphp
                            
                            <a href="{{ $url }}" class="block border border-gray-200 rounded-xl p-5 hover:border-primary hover:shadow-md transition-all group">
                                <div class="flex flex-wrap md:flex-nowrap justify-between items-start gap-4 mb-3">
                                    <div>
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="bg-gray-100 text-gray-700 text-xs px-2.5 py-1 rounded font-semibold">
                                                <i class="fa-solid {{ $icon }} mr-1 text-primary"></i> {{ $jenis }}
                                            </span>
                                            <span class="font-bold text-gray-900 group-hover:text-primary transition-colors">#{{ $code }}</span>
                                        </div>
                                        <p class="text-sm text-gray-500">Dipesan pada: {{ $order->created_at->format('d M Y, H:i') }} WIB</p>
                                    </div>
                                    <div class="text-right">
                                        @php
                                            $badgeStyle = 'bg-gray-100 text-gray-800';
                                            $statusLabel = str_replace('_', ' ', strtoupper($status));
                                            if ($status === 'terkonfirmasi') {
                                                $badgeStyle = 'bg-emerald-100 text-emerald-900 border border-emerald-300';
                                                $statusLabel = 'DIKONFIRMASI (MENUNGGU PELUNASAN)';
                                            } elseif ($status === 'lunas') {
                                                $badgeStyle = 'bg-green-600 text-white font-bold shadow-sm';
                                                $statusLabel = 'LUNAS (SIAP PRODUKSI/PENGANTARAN)';
                                            } elseif (in_array($status, ['menunggu_dp', 'menunggu_konfirmasi', 'baru', 'menunggu_pembayaran'])) {
                                                $badgeStyle = 'bg-yellow-100 text-yellow-800 border border-yellow-300';
                                            } elseif (in_array($status, ['diproses', 'dikirim', 'diantar'])) {
                                                $badgeStyle = 'bg-blue-100 text-blue-800 border border-blue-300';
                                            }
                                        @endphp
                                        <span class="inline-block text-xs px-3 py-1.5 rounded-full font-bold {{ $badgeStyle }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="flex flex-wrap md:flex-nowrap items-end justify-between gap-4 pt-3 border-t border-gray-100 mt-2">
                                    <div>
                                        @if($isDinein)
                                            <p class="text-sm text-gray-500 mb-1">Jumlah Porsi:</p>
                                            <p class="font-medium text-gray-900">
                                                <i class="fa-solid fa-utensils text-primary mr-1"></i> {{ $order->jumlah_porsi }} Porsi
                                            </p>
                                        @else
                                            <p class="text-sm text-gray-500 mb-1">Tanggal Acara:</p>
                                            <p class="font-medium text-gray-900">
                                                <i class="fa-regular fa-calendar text-primary mr-1"></i> 
                                                {{ \Carbon\Carbon::parse($order->tanggal_acara)->format('d M Y') }}
                                                @if($order->waktu_acara)
                                                    , {{ \Carbon\Carbon::parse($order->waktu_acara)->format('H:i') }} WIB
                                                @endif
                                            </p>
                                        @endif
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm text-gray-500 mb-1">Total Tagihan:</p>
                                        <p class="font-bold text-lg text-primary">Rp {{ number_format($total, 0, ',', '.') }}</p>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif

            </div>
        </div>
    </div>
</x-layouts.member>
