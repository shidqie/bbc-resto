<x-layouts.member>
    <x-slot:title>Riwayat Pesanan</x-slot:title>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-clock-rotate-left text-primary text-xl"></i>
                <h1 class="text-2xl font-bold text-gray-900">Riwayat Pesanan</h1>
            </div>
            <div class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-sm font-semibold">
                {{ $nasiboxOrders->count() + $cateringOrders->count() + $dineinOrders->count() }} Riwayat
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
                            <i class="fa-solid fa-clock-rotate-left text-3xl text-gray-400"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-1">Belum Ada Riwayat</h3>
                        <p class="text-gray-500">Riwayat pesanan yang sudah selesai atau dibatalkan akan muncul di sini.</p>
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
                            @endphp
                            
                            <div class="block border border-gray-200 rounded-xl p-5 hover:border-gray-300 hover:bg-gray-50 transition-all group opacity-90 hover:opacity-100">
                                <div class="flex flex-wrap md:flex-nowrap justify-between items-start gap-4 mb-3">
                                    <div>
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="bg-gray-200 text-gray-700 text-xs px-2.5 py-1 rounded font-semibold">
                                                <i class="fa-solid {{ $icon }} mr-1"></i> {{ $jenis }}
                                            </span>
                                            <span class="font-bold text-gray-700">#{{ $code }}</span>
                                        </div>
                                        <p class="text-sm text-gray-500">Dipesan pada: {{ $order->created_at->format('d M Y, H:i') }} WIB</p>
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-block text-xs px-3 py-1.5 rounded-full font-semibold {{ 
                                            in_array($status, ['selesai', 'lunas']) ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                        }}">
                                            {{ str_replace('_', ' ', strtoupper($status)) }}
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="flex flex-wrap md:flex-nowrap items-end justify-between gap-4 pt-3 border-t border-gray-100 mt-2">
                                    <div>
                                        @if($isDinein)
                                            <p class="text-sm text-gray-500 mb-1">Jumlah Porsi:</p>
                                            <p class="font-medium text-gray-700">
                                                <i class="fa-solid fa-utensils mr-1"></i> {{ $order->jumlah_porsi }} Porsi
                                            </p>
                                        @else
                                            <p class="text-sm text-gray-500 mb-1">Tanggal Acara:</p>
                                            <p class="font-medium text-gray-700">
                                                <i class="fa-regular fa-calendar mr-1"></i> 
                                                {{ \Carbon\Carbon::parse($order->tanggal_acara)->format('d M Y') }}
                                            </p>
                                        @endif
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm text-gray-500 mb-1">Total Tagihan:</p>
                                        <p class="font-bold text-lg text-gray-700">Rp {{ number_format($total, 0, ',', '.') }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

            </div>
        </div>
    </div>
</x-layouts.member>
