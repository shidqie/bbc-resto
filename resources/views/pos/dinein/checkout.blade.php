@extends('layouts.pos')

@section('content')
    <div class="p-6 max-w-4xl mx-auto">
        
        <div class="flex items-center gap-4 mb-6">
            <a href="{{ route('pos.dinein.index') }}" class="w-10 h-10 bg-white border border-gray-200 rounded-xl flex items-center justify-center text-gray-500 hover:text-primary transition shadow-sm">
                <i class="ph-bold ph-arrow-left"></i>
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
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <h2 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Detail Pesanan</h2>
                <div class="space-y-4 mb-6">
                    @foreach($pesanan->items as $item)
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-semibold text-gray-800">{{ $item->menu->nama }}</p>
                                <p class="text-sm text-gray-500">{{ $item->qty }} x Rp {{ number_format($item->menu->harga, 0, ',', '.') }}</p>
                                @if($item->catatan)
                                    <p class="text-xs text-yellow-600 italic bg-yellow-50 px-2 py-1 mt-1 rounded inline-block">Catatan: {{ $item->catatan }}</p>
                                @endif
                            </div>
                            <div class="font-bold text-gray-800">
                                Rp {{ number_format($item->qty * $item->menu->harga, 0, ',', '.') }}
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="border-t pt-4 flex justify-between items-center text-lg">
                    <span class="font-bold text-gray-800">Total Tagihan</span>
                    <span class="font-bold text-secondary text-2xl">Rp {{ number_format($totalTagihan, 0, ',', '.') }}</span>
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
                                <i class="ph-bold ph-money text-xl mb-1"></i>
                                <p class="font-semibold text-sm">Tunai</p>
                            </button>
                            <button type="button" @click="metodeBayar = 'qris'" :class="metodeBayar === 'qris' ? 'bg-green-50 border-green-600 text-green-700 ring-1 ring-green-600' : 'border-gray-200 hover:bg-gray-50 text-gray-600'" class="border rounded-xl p-3 text-center transition">
                                <i class="ph-bold ph-qr-code text-xl mb-1"></i>
                                <p class="font-semibold text-sm">QRIS</p>
                            </button>
                            <button type="button" @click="metodeBayar = 'kartu'" :class="metodeBayar === 'kartu' ? 'bg-green-50 border-green-600 text-green-700 ring-1 ring-green-600' : 'border-gray-200 hover:bg-gray-50 text-gray-600'" class="border rounded-xl p-3 text-center transition">
                                <i class="ph-bold ph-credit-card text-xl mb-1"></i>
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
                            <label class="block text-sm font-bold text-gray-800 mb-2">Input uang dari pelanggan*</label>
                            <input type="text" readonly :value="uangDiterima > 0 ? 'Rp' + formatPrice(uangDiterima) : ''" placeholder="Rp0" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-xl text-gray-800 focus:outline-none placeholder-gray-400">
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

                    <button type="submit" :disabled="metodeBayar === 'cash' && uangDiterima < totalTagihan" class="w-full bg-[#EF4444] hover:bg-red-600 text-white font-bold py-4 rounded-xl transition-all shadow-sm mt-2 disabled:bg-gray-200 disabled:text-gray-400 disabled:cursor-not-allowed text-lg">
                        Bayar Sekarang
                    </button>
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
                    if (this.metodeBayar === 'cash' && this.uangDiterima < this.totalTagihan) {
                        e.preventDefault();
                        alert('Uang yang diterima kurang dari total tagihan!');
                    }
                }
            }));
        });
    </script>
    @endpush
@endsection
