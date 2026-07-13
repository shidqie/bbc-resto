{{-- 
    Halaman: Buat Pesanan (POS)
    Deskripsi: Antarmuka Point of Sales untuk kasir memasukkan pesanan pelanggan.
--}}
@extends('layouts.pos')

@section('content')
<div class="flex-1 overflow-auto bg-gray-50/50 text-gray-800 font-sans h-screen flex flex-col">
    {{-- POS Header --}}
    <div class="bg-white border-b border-gray-100 p-4 shrink-0 flex justify-between items-center z-10">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Kasir / Buat Pesanan Baru</h1>
            <p class="text-xs text-gray-500">Pilih menu untuk ditambahkan ke pesanan</p>
        </div>
        <div>
            <x-ui.button href="{{ route('pesanan.index') }}" variant="outline">Kembali</x-ui.button>
        </div>
    </div>

    @if($errors->any())
        <div class="m-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl flex flex-col gap-1 shrink-0">
            @foreach($errors->all() as $error)
                <div class="flex items-center gap-2">
                    <i class="fas fa-exclamation-circle text-xs"></i>
                    <p class="text-sm">{{ $error }}</p>
                </div>
            @endforeach
        </div>
    @endif

    <form action="{{ route('pesanan.store') }}" method="POST" id="form-pesanan" class="flex-1 overflow-hidden flex flex-col lg:flex-row">
        @csrf
        
        {{-- Menu Selection Area --}}
        <div class="flex-1 overflow-y-auto p-4 lg:p-6 pb-24 lg:pb-6">
            
            {{-- Category Filter --}}
            <div class="flex gap-2 overflow-x-auto pb-4 mb-4 hide-scrollbar">
                <button type="button" class="whitespace-nowrap px-4 py-2 rounded-xl bg-[#3B82F6] text-white font-medium text-sm shadow-sm">Semua</button>
                @foreach($menus->pluck('kategori')->unique('id') as $kat)
                    @if($kat)
                        <button type="button" class="kat-filter whitespace-nowrap px-4 py-2 rounded-xl bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 font-medium text-sm transition-colors" data-kategori="{{ $kat->id }}">{{ $kat->nama_kategori ?? $kat->nama }}</button>
                    @endif
                @endforeach
            </div>

            {{-- Menu Grid --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                @foreach($menus as $menu)
                    <div class="menu-card bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden hover:shadow-md transition-shadow cursor-pointer flex flex-col h-full" 
                         data-id="{{ $menu->id }}" 
                         data-nama="{{ $menu->nama }}" 
                         data-harga="{{ $menu->harga }}"
                         data-kategori="{{ $menu->kategori_id }}"
                         onclick="addToCart(this)">
                        <div class="h-32 bg-gray-100 relative">
                            @if($menu->foto)
                                <img src="{{ Storage::url($menu->foto) }}" alt="{{ $menu->nama }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-300">
                                    <i class="fas fa-utensils text-4xl"></i>
                                </div>
                            @endif
                            <div class="absolute top-2 right-2 bg-white/90 backdrop-blur text-[#3B82F6] font-bold text-xs px-2 py-1 rounded-lg">
                                Rp {{ number_format($menu->harga, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="p-3 flex-1 flex flex-col justify-between">
                            <div>
                                <h3 class="font-bold text-gray-900 text-sm leading-tight mb-1">{{ $menu->nama }}</h3>
                                <p class="text-[11px] text-gray-500">{{ $menu->kategori->nama_kategori ?? 'Tanpa Kategori' }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Cart / Checkout Panel --}}
        <div class="w-full lg:w-[400px] bg-white border-l border-gray-100 flex flex-col h-[50vh] lg:h-auto z-20 shadow-[-4px_0_15px_-3px_rgba(0,0,0,0.05)] lg:shadow-none absolute bottom-0 lg:static">
            
            <div class="p-4 border-b border-gray-100 bg-gray-50 shrink-0 cursor-pointer lg:cursor-default" onclick="toggleCartMobile()">
                <div class="flex justify-between items-center">
                    <h2 class="font-bold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-shopping-cart text-[#3B82F6]"></i> Detail Pesanan
                    </h2>
                    <div class="lg:hidden">
                        <i class="fas fa-chevron-up text-gray-400" id="cart-toggle-icon"></i>
                    </div>
                </div>
            </div>

            <div id="cart-content" class="flex-1 flex flex-col overflow-hidden hidden lg:flex">
                {{-- Cart Items --}}
                <div class="flex-1 overflow-y-auto p-4 space-y-3" id="cart-items">
                    <div class="text-center py-8 text-gray-400 text-sm" id="empty-cart-msg">
                        <i class="fas fa-box-open text-3xl mb-2"></i>
                        <p>Belum ada menu yang dipilih</p>
                    </div>
                    {{-- Items will be injected here via JS --}}
                </div>

                {{-- Order Info Form --}}
                <div class="p-4 border-t border-gray-100 space-y-3 bg-gray-50 shrink-0 max-h-[40%] overflow-y-auto">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Jenis Pesanan</label>
                            <select name="jenis_pesanan" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] outline-none text-sm font-medium transition-all">
                                <option value="dine_in">Dine In</option>
                                <option value="take_away">Take Away</option>
                                <option value="catering">Catering</option>
                                <option value="nasi_box">Nasi Box</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">No Meja</label>
                            <input type="text" name="no_meja" placeholder="Contoh: A1" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] outline-none text-sm font-medium transition-all">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Nama Pelanggan</label>
                        <input type="text" name="nama_pelanggan" placeholder="Opsional" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] outline-none text-sm font-medium transition-all">
                    </div>
                    
                    <div class="pt-2 border-t border-gray-200">
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Pembayaran</label>
                        <div class="grid grid-cols-2 gap-3 mb-2">
                            <select name="metode_pembayaran" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] outline-none text-sm font-medium transition-all">
                                <option value="tunai">Tunai</option>
                                <option value="transfer">Transfer</option>
                                <option value="qris">QRIS</option>
                            </select>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">Rp</span>
                            </div>
                            <input type="number" name="jumlah_bayar" id="jumlah_bayar" placeholder="0" class="w-full pl-9 pr-3 py-2 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] outline-none text-base font-bold text-gray-900 transition-all" oninput="calculateChange()">
                        </div>
                    </div>
                </div>

                {{-- Totals & Submit --}}
                <div class="p-4 bg-white border-t border-gray-100 shrink-0">
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-sm text-gray-500">Total Harga</span>
                        <span class="text-lg font-black text-gray-900" id="total-display">Rp 0</span>
                        <input type="hidden" id="total_harga_hidden" value="0">
                    </div>
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-sm text-gray-500 font-medium">Kembalian</span>
                        <span class="text-sm font-bold text-[#16A34A]" id="kembalian-display">Rp 0</span>
                    </div>
                    <button type="button" onclick="submitOrder()" class="w-full bg-[#3B82F6] hover:bg-[#2563EB] text-white py-3.5 rounded-xl font-bold text-sm shadow-md transition-all flex justify-center items-center gap-2">
                        <i class="fas fa-check-circle"></i> Proses Pesanan
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- Template for Cart Item --}}
<template id="cart-item-template">
    <div class="cart-item bg-white border border-gray-100 rounded-xl p-3 flex flex-col gap-2 relative group shadow-sm">
        <input type="hidden" name="menu_id[]" class="menu-id-input">
        
        <div class="flex justify-between items-start pr-6">
            <div>
                <div class="font-bold text-gray-900 text-sm menu-nama">Nama Menu</div>
                <div class="text-xs text-[#3B82F6] font-medium menu-harga-display">Rp 0</div>
            </div>
            <button type="button" class="absolute top-3 right-3 text-gray-300 hover:text-[#DC2626] transition-colors" onclick="removeCartItem(this)">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="flex justify-between items-center mt-1">
            {{-- Qty Control --}}
            <div class="flex items-center bg-gray-50 border border-gray-200 rounded-lg overflow-hidden">
                <button type="button" class="px-3 py-1 text-gray-600 hover:bg-gray-200 transition-colors" onclick="updateQty(this, -1)">-</button>
                <input type="number" name="jumlah[]" class="qty-input w-12 text-center bg-transparent border-none outline-none text-sm font-bold p-0 hide-arrows" value="1" min="1" onchange="calculateCart()">
                <button type="button" class="px-3 py-1 text-gray-600 hover:bg-gray-200 transition-colors" onclick="updateQty(this, 1)">+</button>
            </div>
            
            <div class="font-bold text-gray-900 text-sm item-subtotal">Rp 0</div>
        </div>
        
        {{-- Optional Catatan --}}
        <div>
            <input type="text" name="catatan[]" placeholder="Catatan (opsional)..." class="w-full text-xs px-2 py-1.5 bg-gray-50 border border-gray-100 rounded focus:bg-white focus:border-blue-100 outline-none transition-colors">
        </div>
    </div>
</template>

<style>
    /* Hide number arrows */
    input[type="number"].hide-arrows::-webkit-inner-spin-button,
    input[type="number"].hide-arrows::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    input[type="number"].hide-arrows {
        -moz-appearance: textfield;
    }
    .hide-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .hide-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>

<script>
    let cart = {};

    function toggleCartMobile() {
        const content = document.getElementById('cart-content');
        const icon = document.getElementById('cart-toggle-icon');
        if (window.innerWidth < 1024) {
            content.classList.toggle('hidden');
            if (content.classList.contains('hidden')) {
                icon.classList.replace('fa-chevron-down', 'fa-chevron-up');
            } else {
                icon.classList.replace('fa-chevron-up', 'fa-chevron-down');
            }
        }
    }

    function addToCart(card) {
        const id = card.getAttribute('data-id');
        const nama = card.getAttribute('data-nama');
        const harga = parseFloat(card.getAttribute('data-harga'));

        if (cart[id]) {
            // Already in cart, increase qty
            const input = document.querySelector(`.cart-item input.menu-id-input[value="${id}"]`);
            if (input) {
                const qtyInput = input.closest('.cart-item').querySelector('.qty-input');
                qtyInput.value = parseInt(qtyInput.value) + 1;
            }
        } else {
            // Add new
            cart[id] = { nama, harga };
            renderNewCartItem(id, nama, harga);
        }
        
        document.getElementById('empty-cart-msg').style.display = 'none';
        calculateCart();
        
        // Optional visual feedback
        card.classList.add('ring-2', 'ring-[#3B82F6]');
        setTimeout(() => card.classList.remove('ring-2', 'ring-[#3B82F6]'), 200);
    }

    function renderNewCartItem(id, nama, harga) {
        const template = document.getElementById('cart-item-template');
        const clone = template.content.cloneNode(true);
        
        const wrapper = clone.querySelector('.cart-item');
        wrapper.querySelector('.menu-id-input').value = id;
        wrapper.querySelector('.menu-nama').textContent = nama;
        wrapper.querySelector('.menu-harga-display').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(harga);
        
        document.getElementById('cart-items').appendChild(clone);
    }

    function updateQty(btn, delta) {
        const input = btn.closest('.cart-item').querySelector('.qty-input');
        let val = parseInt(input.value) + delta;
        if (val < 1) val = 1;
        input.value = val;
        calculateCart();
    }

    function removeCartItem(btn) {
        const item = btn.closest('.cart-item');
        const id = item.querySelector('.menu-id-input').value;
        delete cart[id];
        item.remove();
        
        if (Object.keys(cart).length === 0) {
            document.getElementById('empty-cart-msg').style.display = 'block';
        }
        calculateCart();
    }

    function calculateCart() {
        let total = 0;
        document.querySelectorAll('.cart-item').forEach(item => {
            const id = item.querySelector('.menu-id-input').value;
            const qty = parseInt(item.querySelector('.qty-input').value) || 1;
            const harga = cart[id].harga;
            const subtotal = qty * harga;
            
            item.querySelector('.item-subtotal').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(subtotal);
            total += subtotal;
        });

        document.getElementById('total-display').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
        document.getElementById('total_harga_hidden').value = total;
        
        // Set default jumlah bayar to total if it's currently 0 or empty
        const bayarInput = document.getElementById('jumlah_bayar');
        if (!bayarInput.value || parseFloat(bayarInput.value) === 0) {
            bayarInput.value = total;
        }
        
        calculateChange();
    }

    function calculateChange() {
        const total = parseFloat(document.getElementById('total_harga_hidden').value) || 0;
        const bayar = parseFloat(document.getElementById('jumlah_bayar').value) || 0;
        
        let kembalian = bayar - total;
        const display = document.getElementById('kembalian-display');
        
        if (kembalian >= 0) {
            display.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(kembalian);
            display.classList.replace('text-[#DC2626]', 'text-[#16A34A]');
        } else {
            // Kurang bayar
            display.textContent = '- Rp ' + new Intl.NumberFormat('id-ID').format(Math.abs(kembalian)) + ' (Kurang)';
            display.classList.replace('text-[#16A34A]', 'text-[#DC2626]');
        }
    }

    function submitOrder() {
        if (Object.keys(cart).length === 0) {
            alert('Pilih minimal satu menu untuk dipesan.');
            return;
        }
        
        const total = parseFloat(document.getElementById('total_harga_hidden').value) || 0;
        const bayar = parseFloat(document.getElementById('jumlah_bayar').value) || 0;
        
        if (bayar < 0) {
            alert('Jumlah bayar tidak valid.');
            return;
        }
        
        // Disable button to prevent double submit
        document.getElementById('form-pesanan').submit();
    }

    // Category filtering
    document.querySelectorAll('.kat-filter').forEach(btn => {
        btn.addEventListener('click', function() {
            // Reset active state
            document.querySelectorAll('.kat-filter').forEach(b => {
                b.classList.remove('bg-[#3B82F6]', 'text-white');
                b.classList.add('bg-white', 'text-gray-600');
            });
            const allBtn = btn.parentElement.firstElementChild;
            allBtn.classList.remove('bg-[#3B82F6]', 'text-white');
            allBtn.classList.add('bg-white', 'text-gray-600');
            
            btn.classList.remove('bg-white', 'text-gray-600');
            btn.classList.add('bg-[#3B82F6]', 'text-white');

            const katId = btn.getAttribute('data-kategori');
            document.querySelectorAll('.menu-card').forEach(card => {
                if (card.getAttribute('data-kategori') == katId) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });

    // "Semua" button
    document.querySelector('.hide-scrollbar').firstElementChild.addEventListener('click', function() {
        document.querySelectorAll('.kat-filter').forEach(b => {
            b.classList.remove('bg-[#3B82F6]', 'text-white');
            b.classList.add('bg-white', 'text-gray-600');
        });
        this.classList.remove('bg-white', 'text-gray-600');
        this.classList.add('bg-[#3B82F6]', 'text-white');
        
        document.querySelectorAll('.menu-card').forEach(card => {
            card.style.display = 'flex';
        });
    });
</script>
@endsection
