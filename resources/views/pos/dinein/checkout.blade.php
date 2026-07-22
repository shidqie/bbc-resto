@extends('layouts.pos')

@section('content')
    <!-- Midtrans Snap Script -->
    <script src="{{ config('midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}" data-client-key="{{ config('midtrans.client_key') }}"></script>

    <div class="p-6 max-w-4xl mx-auto">
        
        <div class="flex items-center gap-4 mb-6">
            <a href="{{ route('pos.dinein.index') }}" class="w-10 h-10 bg-white border border-gray-200 rounded-xl flex items-center justify-center text-gray-500 hover:text-primary transition shadow-sm">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <h1 class="text-2xl font-bold text-gray-800">Checkout Tagihan - {{ $meja->nomor_meja }}</h1>
        </div>

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Ringkasan Pesanan --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col justify-between">
                <div>
                    <h2 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2 flex justify-between items-center">
                        <span>Detail Pesanan</span>
                        <span class="text-xs font-semibold px-2.5 py-1 bg-blue-50 text-blue-700 rounded-full border border-blue-100">
                            {{ $pesanan->kode_pesanan ?? 'DIN-'.str_pad($pesanan->id, 5, '0', STR_PAD_LEFT) }}
                        </span>
                    </h2>

                    {{-- Informasi Konsumen & Transaksi --}}
                    <div class="bg-gray-50 rounded-xl p-3.5 mb-5 space-y-1.5 text-xs border border-gray-100">
                        <div class="flex justify-between">
                            <span class="text-gray-500 font-medium">Nomor Meja:</span>
                            <span class="font-bold text-gray-800">{{ $meja->nomor_meja }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 font-medium">Nama Pelanggan:</span>
                            <span class="font-bold text-gray-800">{{ $pesanan->nama_konsumen ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 font-medium">Waktu Pesanan:</span>
                            <span class="font-semibold text-gray-700">{{ \Carbon\Carbon::parse($pesanan->dibuka_pada)->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>

                    {{-- List Item Pesanan --}}
                    <div class="space-y-4 mb-6 max-h-[350px] overflow-y-auto pr-1">
                        @foreach($pesanan->items as $item)
                            <div class="flex justify-between items-start border-b border-gray-50 pb-3 last:border-0">
                                <div>
                                    <p class="font-bold text-gray-800 text-sm">{{ $item->menu->nama }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $item->qty }} x Rp {{ number_format($item->menu->harga, 0, ',', '.') }}</p>
                                    @if($item->catatan)
                                        <p class="text-[11px] text-yellow-700 italic bg-yellow-50 px-2 py-0.5 mt-1 rounded border border-yellow-100 inline-block">
                                            Catatan: {{ $item->catatan }}
                                        </p>
                                    @endif
                                </div>
                                <div class="font-bold text-gray-800 text-sm">
                                    Rp {{ number_format($item->qty * $item->menu->harga, 0, ',', '.') }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="border-t pt-4 flex justify-between items-center text-lg">
                    <span class="font-bold text-gray-800">Total Tagihan</span>
                    <span class="font-bold text-secondary text-2xl" style="color: #3B82F6;">Rp {{ number_format($totalTagihan, 0, ',', '.') }}</span>
                </div>
            </div>

            {{-- Form Pembayaran --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100" x-data="checkoutPage({{ $totalTagihan }})">
                <h2 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Pembayaran</h2>
                
                <form action="{{ route('pos.dinein.processPayment', $meja->id) }}" method="POST" @submit="submitForm">
                    @csrf
                    <input type="hidden" name="pesanan_id" value="{{ $pesanan->id }}">
                    <input type="hidden" name="total_tagihan" value="{{ $totalTagihan }}">
                    <input type="hidden" name="metode_bayar" x-model="metodeBayar">

                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Metode Pembayaran</label>
                        <div class="grid grid-cols-3 gap-3">
                            <button type="button" @click="metodeBayar = 'cash'" :class="metodeBayar === 'cash' ? 'bg-green-50 border-green-600 text-green-700 ring-1 ring-green-600' : 'border-gray-200 hover:bg-gray-50 text-gray-600'" class="border rounded-xl p-3 text-center transition">
                                <i class="fa-solid fa-money-bill text-xl mb-1"></i>
                                <p class="font-semibold text-sm">Tunai</p>
                            </button>
                            <button type="button" @click="metodeBayar = 'qris'" :class="metodeBayar === 'qris' ? 'bg-green-50 border-green-600 text-green-700 ring-1 ring-green-600' : 'border-gray-200 hover:bg-gray-50 text-gray-600'" class="border rounded-xl p-3 text-center transition">
                                <i class="fa-solid fa-qrcode text-xl mb-1"></i>
                                <p class="font-semibold text-sm">QRIS</p>
                            </button>
                            <button type="button" @click="metodeBayar = 'kartu'" :class="metodeBayar === 'kartu' ? 'bg-green-50 border-green-600 text-green-700 ring-1 ring-green-600' : 'border-gray-200 hover:bg-gray-50 text-gray-600'" class="border rounded-xl p-3 text-center transition">
                                <i class="fa-solid fa-credit-card text-xl mb-1"></i>
                                <p class="font-semibold text-sm">Kartu</p>
                            </button>
                        </div>
                    </div>

                    <div x-show="metodeBayar === 'cash'" x-collapse class="mb-6 mt-6">
                        
                        {{-- Kembalian Box --}}
                        <div class="border border-gray-200 rounded-xl px-4 py-3 flex justify-between items-center mb-6">
                            <span class="font-semibold" :class="uangDiterima > 0 && selisih < 0 ? 'text-red-500' : 'text-gray-600'" x-text="uangDiterima > 0 && selisih < 0 ? 'Kurang' : 'Kembalian'">Kembalian</span>
                            <span class="font-bold text-lg" :class="uangDiterima > 0 && selisih < 0 ? 'text-red-500' : 'text-gray-800'" x-text="uangDiterima === 0 ? 'Rp0' : (selisih < 0 ? '-Rp' + formatPrice(Math.abs(selisih)) : 'Rp' + formatPrice(selisih))">Rp0</span>
                        </div>

                        {{-- Input Uang dari Pelanggan --}}
                        <div class="mb-4">
                            <x-ui.input readonly x-bind:value="uangDiterima > 0 ? 'Rp' + formatPrice(uangDiterima) : ''" placeholder="Rp0" class="text-xl py-3 w-full" label="Input uang dari pelanggan*" />
                        </div>

                        {{-- Quick Amounts --}}
                        <div class="flex flex-wrap gap-2 mb-6">
                            <button type="button" @click="uangDiterima = totalTagihan" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2.5 px-4 rounded-full text-sm transition-colors">Uang Pas</button>
                            <button type="button" @click="addShortcut(5000)" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2.5 px-4 rounded-full text-sm transition-colors">Rp5.000</button>
                            <button type="button" @click="addShortcut(10000)" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2.5 px-4 rounded-full text-sm transition-colors">Rp10.000</button>
                            <button type="button" @click="addShortcut(20000)" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2.5 px-4 rounded-full text-sm transition-colors">Rp20.000</button>
                            <button type="button" @click="addShortcut(50000)" class="flex-grow bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2.5 px-4 rounded-full text-sm transition-colors text-center">Rp50.000</button>
                            <button type="button" @click="addShortcut(100000)" class="flex-grow bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2.5 px-4 rounded-full text-sm transition-colors text-center">Rp100.000</button>
                        </div>

                        {{-- Numpad (Rounded Borders) --}}
                        <div class="grid grid-cols-3 gap-3">
                            <!-- Row 1 -->
                            <button type="button" @click="appendNum(7)" class="bg-white border border-gray-200 text-gray-800 text-2xl font-bold py-4 rounded-xl hover:bg-gray-50 active:bg-gray-100 transition-colors shadow-sm">7</button>
                            <button type="button" @click="appendNum(8)" class="bg-white border border-gray-200 text-gray-800 text-2xl font-bold py-4 rounded-xl hover:bg-gray-50 active:bg-gray-100 transition-colors shadow-sm">8</button>
                            <button type="button" @click="appendNum(9)" class="bg-white border border-gray-200 text-gray-800 text-2xl font-bold py-4 rounded-xl hover:bg-gray-50 active:bg-gray-100 transition-colors shadow-sm">9</button>
                            
                            <!-- Row 2 -->
                            <button type="button" @click="appendNum(4)" class="bg-white border border-gray-200 text-gray-800 text-2xl font-bold py-4 rounded-xl hover:bg-gray-50 active:bg-gray-100 transition-colors shadow-sm">4</button>
                            <button type="button" @click="appendNum(5)" class="bg-white border border-gray-200 text-gray-800 text-2xl font-bold py-4 rounded-xl hover:bg-gray-50 active:bg-gray-100 transition-colors shadow-sm">5</button>
                            <button type="button" @click="appendNum(6)" class="bg-white border border-gray-200 text-gray-800 text-2xl font-bold py-4 rounded-xl hover:bg-gray-50 active:bg-gray-100 transition-colors shadow-sm">6</button>
                            
                            <!-- Row 3 -->
                            <button type="button" @click="appendNum(1)" class="bg-white border border-gray-200 text-gray-800 text-2xl font-bold py-4 rounded-xl hover:bg-gray-50 active:bg-gray-100 transition-colors shadow-sm">1</button>
                            <button type="button" @click="appendNum(2)" class="bg-white border border-gray-200 text-gray-800 text-2xl font-bold py-4 rounded-xl hover:bg-gray-50 active:bg-gray-100 transition-colors shadow-sm">2</button>
                            <button type="button" @click="appendNum(3)" class="bg-white border border-gray-200 text-gray-800 text-2xl font-bold py-4 rounded-xl hover:bg-gray-50 active:bg-gray-100 transition-colors shadow-sm">3</button>
                            
                            <!-- Row 4 -->
                            <button type="button" @click="appendNum('00')" class="bg-white border border-gray-200 text-gray-800 text-2xl font-bold py-4 rounded-xl hover:bg-gray-50 active:bg-gray-100 transition-colors shadow-sm">00</button>
                            <button type="button" @click="appendNum(0)" class="bg-white border border-gray-200 text-gray-800 text-2xl font-bold py-4 rounded-xl hover:bg-gray-50 active:bg-gray-100 transition-colors shadow-sm">0</button>
                            <button type="button" @click="uangDiterima = 0" class="bg-white border border-red-500 text-red-500 text-2xl font-bold py-4 rounded-xl hover:bg-red-50 active:bg-red-100 transition-colors shadow-sm">C</button>
                        </div>
                    </div>

                    <x-ui.button type="submit" variant="primary" x-bind:disabled="metodeBayar === 'cash' && uangDiterima < totalTagihan" class="w-full py-4 text-lg justify-center mt-4 !rounded-xl" style="background-color: #3B82F6;">
                        Bayar Sekarang
                    </x-ui.button>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('checkoutPage', (total) => ({
                totalTagihan: total,
                metodeBayar: 'cash',
                uangDiterima: 0,
                
                get selisih() {
                    return this.uangDiterima - this.totalTagihan;
                },
                
                addShortcut(amount) {
                    if (this.uangDiterima + amount > 999999999) return;
                    this.uangDiterima += amount;
                },
                
                appendNum(num) {
                    let current = this.uangDiterima.toString();
                    if (current === '0') current = '';
                    
                    // Batasi maksimal panjang input agar tidak error
                    if (current.length > 12) return;
                    
                    this.uangDiterima = parseInt(current + num.toString()) || 0;
                },
                
                formatPrice(price) {
                    if (!price) return '0';
                    return price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                },

                submitForm(e) {
                    if (this.metodeBayar === 'cash') {
                        if (this.uangDiterima < this.totalTagihan) {
                            e.preventDefault();
                            alert('Uang yang diterima kurang dari total tagihan!');
                        }
                        return; // proceed normal form submission
                    }

                    // For QRIS / Kartu -> Midtrans
                    e.preventDefault();
                    let form = e.target;
                    
                    @if($pesanan->snap_token)
                        window.snap.pay('{{ $pesanan->snap_token }}', {
                            onSuccess: function(result) {
                                // Payment success, submit the form to backend
                                form.submit();
                            },
                            onPending: function(result) {
                                alert("Menunggu pembayaran Anda!");
                            },
                            onError: function(result) {
                                alert("Pembayaran gagal!");
                            },
                            onClose: function() {
                                alert("Anda menutup popup tanpa menyelesaikan pembayaran");
                            }
                        });
                    @else
                        alert('Snap token tidak tersedia. Mohon refresh halaman atau gunakan metode Cash.');
                    @endif
                }
            }));
        });
    </script>
    @endpush
@endsection
