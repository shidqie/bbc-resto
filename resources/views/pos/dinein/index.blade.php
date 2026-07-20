@extends('layouts.pos')

@section('content')
<div x-data="posSystem()" class="h-screen w-full flex bg-gray-50 overflow-hidden font-sans">
    
    {{-- AREA KIRI: KATALOG MENU --}}
    <div class="flex-1 flex flex-col h-full overflow-hidden">
        {{-- Header & Kategori --}}
        <div class="bg-white p-4 shrink-0 shadow-sm z-10 flex flex-col gap-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Point of Sale</h1>
                    <p class="text-sm text-gray-500">Pilih menu dan meja pelanggan</p>
                </div>
                <div class="flex items-center gap-2">
                    <button @click="showTableModal = true" class="px-4 py-2 bg-orange-100 text-orange-700 hover:bg-orange-200 rounded-lg text-sm font-bold transition">
                        Atur Meja
                    </button>
                    <x-ui.button href="{{ route('dashboard') }}" variant="outline">Kembali ke Dashboard</x-ui.button>
                </div>
            </div>
            
            {{-- Kategori Tabs --}}
            <div class="px-6 py-4 border-b border-gray-100 shrink-0">
                <div class="flex overflow-x-auto hide-scrollbar gap-2 pb-2">
                <button @click="activeCategory = 'semua'"
                        :class="activeCategory === 'semua' ? 'font-bold' : 'bg-white text-gray-600 hover:bg-gray-50'"
                        :style="activeCategory === 'semua' ? 'background-color: #0F2E23; color: white;' : ''"
                        class="px-5 py-2 rounded-full text-sm whitespace-nowrap transition-colors border border-gray-200">
                    Semua Kategori
                </button>
                @foreach($kategoris as $kategori)
                <button @click="activeCategory = '{{ $kategori->id }}'"
                        :class="activeCategory === '{{ $kategori->id }}' ? 'font-bold' : 'bg-white text-gray-600 hover:bg-gray-50'"
                        :style="activeCategory === '{{ $kategori->id }}' ? 'background-color: #0F2E23; color: white;' : ''"
                        class="px-5 py-2 rounded-full text-sm whitespace-nowrap transition-colors border border-gray-200">
                    {{ $kategori->nama }}
                </button>
                @endforeach
            </div>
            </div>
        </div>

        {{-- Grid Menu --}}
        <div class="flex-1 overflow-y-auto p-4 lg:p-6 pb-24">
            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-4">
                @foreach($menus as $menu)
                <div x-show="activeCategory === 'semua' || activeCategory === '{{ $menu->kategori_id }}'" 
                     x-data="{ cardHover: false }"
                     @mouseenter="cardHover = true" 
                     @mouseleave="cardHover = false"
                     :style="cardHover ? 'border-color: #0F2E23; transform: translateY(-4px); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);' : ''"
                     class="bg-white rounded-2xl border border-gray-100 shadow-sm transition-all duration-300 cursor-pointer overflow-hidden flex flex-col relative"
                     @click="addToCart({{ $menu->id }}, '{{ addslashes($menu->nama) }}', {{ $menu->harga }})">
                    
                    {{-- Kotak Abu (Placeholder Gambar) --}}
                    <div class="h-40 bg-gray-100 flex items-center justify-center relative">
                        {{-- Kategori Badge --}}
                        <div class="absolute top-3 left-3 bg-white/90 backdrop-blur-sm px-2 py-1 rounded text-[10px] font-bold text-gray-700 tracking-wider uppercase">
                            {{ $menu->kategori->nama ?? 'Umum' }}
                        </div>
                        @if($menu->foto)
                            <img src="{{ Storage::url($menu->foto) }}" class="w-full h-full object-cover mix-blend-multiply opacity-80" alt="{{ $menu->nama }}">
                        @else
                            <x-heroicon-o-photo class="w-10 h-10 text-gray-300" />
                        @endif
                    </div>

                    {{-- Info Menu --}}
                    <div class="p-4 flex-1 flex flex-col justify-between">
                        <div>
                            <h3 class="font-bold text-gray-800 text-sm md:text-base leading-tight mb-1">{{ $menu->nama }}</h3>
                        </div>
                        <div class="flex items-center justify-between mt-3">
                            <span class="font-bold text-gray-900">Rp {{ number_format($menu->harga, 0, ',', '.') }}</span>
                            <div class="w-8 h-8 rounded-full flex items-center justify-center transition-colors"
                                 :style="cardHover ? 'background-color: #0F2E23; color: white;' : 'background-color: #F9FAFB; color: #9CA3AF;'">
                                <x-heroicon-o-plus class="w-4 h-4" />
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- AREA KANAN: SIDEBAR KERANJANG --}}
    <div class="shrink-0 bg-white border-l border-gray-100 shadow-xl flex flex-col h-full relative z-20" style="width: 380px;">
        
        {{-- Form Pelanggan & Meja --}}
        <div class="p-5 border-b border-gray-100 bg-white shrink-0 space-y-4">
            <div>
                <label class="block text-xs font-bold text-gray-500 tracking-wider mb-2 uppercase">Nama Konsumen <span class="text-red-500">*</span></label>
                <input type="text" x-model="customerName" placeholder="Masukkan nama..." 
                       class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all placeholder:text-gray-400">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 tracking-wider mb-2 uppercase">Nomor HP (Opsional)</label>
                <input type="tel" x-model="customerPhone" placeholder="08..." 
                       class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all placeholder:text-gray-400">
            </div>

            <div>
                <div class="flex justify-between items-end mb-2">
                    <label class="block text-xs font-bold text-gray-500 tracking-wider uppercase">Pilih Meja <span class="text-red-500">*</span></label>
                    <span class="text-xs text-gray-400">{{ $mejas->where('status', 'kosong')->count() }} Kosong</span>
                </div>
                <select x-model="selectedTable" class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all text-gray-700">
                    <option value="" disabled selected>-- Pilih Meja --</option>
                    @foreach($mejas as $meja)
                        @if($meja->status === 'kosong' || $meja->status === 'menunggu_pembayaran')
                            <option value="{{ $meja->id }}">
                                {{ $meja->nomor_meja }} - @if($meja->status === 'kosong') (Kosong) @else (Terisi / Menunggu Pembayaran) @endif
                            </option>
                        @endif
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Daftar Pesanan (Keranjang) --}}
        <div class="flex-1 overflow-y-auto p-5 space-y-3 bg-white">
            <template x-if="cart.length === 0">
                <div class="h-full flex flex-col items-center justify-center text-center opacity-50">
                    <x-heroicon-o-shopping-bag class="w-16 h-16 text-gray-300 mb-3" />
                    <p class="text-sm font-medium text-gray-500">Belum ada pesanan</p>
                    <p class="text-xs text-gray-400">Pilih menu di sebelah kiri</p>
                </div>
            </template>

            <template x-for="(item, index) in cart" :key="item.menu_id">
                <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm relative group">
                    <div class="mb-3">
                        <h4 class="font-bold text-gray-900 text-sm mb-0.5" x-text="item.nama"></h4>
                        <p class="text-xs font-bold text-gray-800" x-text="'Rp ' + formatPrice(item.harga)"></p>
                    </div>
                    
                    <div class="flex items-center justify-between gap-3">
                        <input type="text" x-model="item.catatan" placeholder="Catatan..." 
                               class="w-3/5 bg-gray-50 border border-gray-100 rounded-lg px-3 py-1.5 text-xs focus:ring-1 focus:ring-gray-200 outline-none placeholder:text-gray-400">
                        
                        <div class="flex items-center gap-2">
                            <div class="flex items-center bg-gray-50 rounded-lg border border-gray-100 p-0.5">
                                <button @click="updateQty(index, -1)" class="w-7 h-7 flex items-center justify-center bg-white border border-gray-100 text-gray-500 rounded-md hover:bg-gray-50 transition-colors shadow-sm">
                                    <span class="text-sm font-bold leading-none" style="margin-top:-2px">-</span>
                                </button>
                                <span class="w-8 text-center text-xs font-bold text-gray-900" x-text="item.qty"></span>
                                <button @click="updateQty(index, 1)" class="w-7 h-7 flex items-center justify-center bg-white border border-gray-100 text-gray-500 rounded-md hover:bg-gray-50 transition-colors shadow-sm">
                                    <span class="text-sm font-bold leading-none" style="margin-top:-1px">+</span>
                                </button>
                            </div>
                            
                            <button @click="removeFromCart(index)" title="Hapus" class="w-7 h-7 flex items-center justify-center bg-red-50 hover:bg-red-100 border border-red-100 text-red-500 rounded-md transition-colors shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        {{-- Footer Pembayaran --}}
        <div class="p-5 bg-white border-t border-gray-100 shrink-0">
            <div class="flex justify-between items-center mb-2">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Item</span>
                <span class="text-sm font-bold text-gray-800" x-text="totalQty + ' item'"></span>
            </div>
            <div class="flex justify-between items-center mb-5">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Harga</span>
                <span class="text-2xl font-black" style="color: #0F2E23;" x-text="'Rp ' + formatPrice(totalPrice)"></span>
            </div>
            
            <div class="grid grid-cols-2 gap-3">
                <button @click="submitOrder('simpan')" :disabled="isSubmitting || cart.length === 0 || !selectedTable || !customerName"
                        class="w-full bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-800 font-bold py-3 rounded-xl text-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    Simpan
                </button>
                <button @click="submitOrder('bayar')" :disabled="isSubmitting || cart.length === 0 || !selectedTable || !customerName"
                        style="background-color: #0F2E23; color: white;"
                        class="w-full hover:bg-black font-bold py-3 rounded-xl text-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 shadow-sm">
                    <span x-show="!isSubmitting">Bayar</span>
                    <svg x-show="isSubmitting" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            </div>
        </div>
        
    {{-- MODAL ATUR MEJA --}}
    <div x-show="showTableModal" style="display: none;" class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showTableModal" x-transition.opacity class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" aria-hidden="true" @click="showTableModal = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="showTableModal" x-transition.scale.origin.bottom class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-900" id="modal-title">Manajemen Status Meja</h3>
                    <button @click="showTableModal = false" class="text-gray-400 hover:text-gray-600 text-2xl font-bold leading-none">&times;</button>
                </div>
                
                <div class="bg-white p-6 grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3 max-h-[60vh] overflow-y-auto">
                    @foreach($mejas as $meja)
                        <div class="border rounded-xl p-3 text-center {{ $meja->status === 'kosong' ? 'border-gray-200 bg-gray-50' : 'border-red-200 bg-red-50' }}">
                            <div class="font-bold text-gray-800 mb-1">{{ $meja->nomor_meja }}</div>
                            <div class="text-[10px] font-semibold uppercase mb-2 {{ $meja->status === 'kosong' ? 'text-gray-500' : 'text-red-600' }}">
                                {{ str_replace('_', ' ', $meja->status) }}
                            </div>
                            
                            @if($meja->status !== 'kosong')
                                <form action="{{ route('pos.dinein.clear-table', $meja->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" onclick="return confirm('Kosongkan {{ $meja->nomor_meja }}?')" class="w-full bg-white border border-red-200 text-red-600 hover:bg-red-600 hover:text-white text-xs font-bold py-1.5 rounded-lg transition-colors shadow-sm">
                                        Kosongkan
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

</div>



    {{-- MODAL CETAK STRUK OTOMATIS (MUNCUL SETELAH BAYAR) --}}
    @if(session('print_receipt_id'))
        <div x-data="{ showPrint: true }" x-show="showPrint" class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" aria-hidden="true" @click="showPrint = false"></div>
                
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                    <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-lg font-bold text-gray-800">Cetak Struk Pembayaran</h3>
                        <button @click="showPrint = false" class="text-gray-400 hover:text-gray-600 font-bold text-2xl leading-none">&times;</button>
                    </div>
                    
                    <div class="p-0 bg-gray-100">
                        {{-- iframe untuk load halaman receipt --}}
                        <iframe id="receiptFrame" src="{{ route('pos.dinein.receipts', session('print_receipt_id')) }}?embedded=true" class="w-full h-[65vh] border-0"></iframe>
                    </div>
                    
                    <div class="bg-white px-4 py-4 sm:px-6 flex justify-end gap-3 border-t border-gray-200">
                        <button @click="showPrint = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-bold text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Tutup & Kembali
                        </button>
                        <button onclick="document.getElementById('receiptFrame').contentWindow.print()" type="button" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-[#3B82F6] text-base font-bold text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm flex items-center gap-2">
                            <i class="ph-bold ph-printer"></i> Cetak Sekarang
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('posSystem', () => ({
        activeCategory: 'semua',
        selectedTable: null,
        showTableModal: false,
        customerName: '',
        customerPhone: '',
        cart: [],
        isSubmitting: false,

        get totalPrice() {
            return this.cart.reduce((total, item) => total + (item.harga * item.qty), 0);
        },

        get totalQty() {
            return this.cart.reduce((total, item) => total + item.qty, 0);
        },

        formatPrice(price) {
            return price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        },
        addToCart(menuId, nama, harga) {
            if (!this.customerName || !this.selectedTable) {
                alert('Mohon lengkapi Nama Konsumen dan Pilih Meja terlebih dahulu sebelum memilih menu!');
                return;
            }

            const existingItem = this.cart.find(i => i.menu_id === menuId);
            if (existingItem) {
                existingItem.qty++;
            } else {
                this.cart.unshift({
                    menu_id: menuId,
                    nama: nama,
                    harga: harga,
                    qty: 1,
                    catatan: ''
                });
            }
        },

        updateQty(index, change) {
            const newQty = this.cart[index].qty + change;
            if (newQty > 0) {
                this.cart[index].qty = newQty;
            } else {
                this.removeFromCart(index);
            }
        },

        removeFromCart(index) {
            this.cart.splice(index, 1);
        },

        async submitOrder(action) {
            if (!this.selectedTable || !this.customerName || this.cart.length === 0) {
                alert('Mohon lengkapi Nama Pelanggan, Meja, dan minimal 1 pesanan!');
                return;
            }

            this.isSubmitting = true;

            try {
                const response = await fetch('{{ route('pos.dinein.store-pos') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        meja_id: this.selectedTable,
                        nama_konsumen: this.customerPhone ? `${this.customerName} - ${this.customerPhone}` : this.customerName,
                        items: this.cart
                    })
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    if (action === 'bayar') {
                        window.location.href = `/pos/dinein/meja/${this.selectedTable}/checkout`;
                    } else {
                        window.location.reload();
                    }
                } else {
                    alert(result.message || 'Terjadi kesalahan saat menyimpan pesanan');
                }
            } catch (error) {
                console.error(error);
                alert('Terjadi kesalahan jaringan');
            } finally {
                this.isSubmitting = false;
            }
        }
    }));
});
</script>
@endpush
@endsection
