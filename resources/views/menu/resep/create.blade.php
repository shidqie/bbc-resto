@extends('layouts.pos')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800">
    <div class="w-full max-w-4xl mx-auto p-6 space-y-5">
        {{-- PAGE HEADER --}}
        <div class="flex items-center gap-3">
            <a href="{{ route('resep.index') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-white border border-gray-200 text-gray-500 hover:text-gray-900 hover:bg-gray-100 transition-colors">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h1 class="text-2xl font-black text-gray-900 tracking-tight">Atur Resep (BOM)</h1>
                <p class="text-sm text-gray-500 font-medium mt-1">Menu: <span class="text-gray-900 font-bold">{{ $menu->nama_menu }}</span> ({{ $menu->kode_menu }})</p>
            </div>
        </div>

        <x-ui.alert />

        {{-- Panel Summary HPP & Profit --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-2">
            <div class="bg-white rounded-3xl border border-gray-200 p-4 flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Total Modal (HPP)</p>
                    <p class="text-lg font-black text-gray-900" id="textTotalHpp">Rp 0</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-600">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            
            <div class="bg-white rounded-3xl border border-gray-200 p-4 flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Harga Jual</p>
                    <p class="text-lg font-black text-gray-900" id="textHargaJual">Rp {{ number_format($menu->harga_jual, 0, ',', '.') }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-green-50 flex items-center justify-center text-green-600">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                </div>
            </div>

            <div class="bg-white rounded-3xl border border-gray-200 p-4 flex items-center justify-between" id="cardProfit">
                <div>
                    <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Estimasi Profit Kotor</p>
                    <p class="text-lg font-black" id="textProfit">Rp 0 (0%)</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center text-gray-600" id="iconProfit">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6">
            <form action="{{ route('resep.store', $menu->id) }}" method="POST">
                @csrf
                
                <div class="mb-5 flex justify-between items-end">
                    <h2 class="text-sm font-bold text-gray-900">Komposisi Bahan Baku</h2>
                    <button type="button" onclick="addBahanBakuRow()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-gray-900 text-white rounded-2xl hover:bg-gray-800 transition-colors">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Tambah Bahan
                    </button>
                </div>

                <div class="space-y-3" id="bahanBakuContainer">
                    @forelse($menu->resep_menu as $resep)
                        <div class="flex gap-3 items-start bahan-baku-row">
                            <div class="flex-1">
                                <label class="block text-[11px] font-semibold text-gray-500 mb-1">Pilih Bahan Baku</label>
                                <select name="bahan_baku_id[]" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-2xl focus:ring-1 focus:ring-gray-400 focus:border-gray-400 outline-none bg-white hpp-select" required onchange="calculateHPP()">
                                    <option value="" disabled>-- Pilih Bahan --</option>
                                    @foreach($bahanBakus as $bb)
                                        <option value="{{ $bb->id }}" data-harga="{{ $bb->harga_satuan }}" {{ $resep->bahan_baku_id == $bb->id ? 'selected' : '' }}>
                                            {{ $bb->nama_bahan }} ({{ $bb->satuan->nama_satuan ?? '-' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="w-32">
                                <label class="block text-[11px] font-semibold text-gray-500 mb-1">Jml. Kebutuhan</label>
                                <input type="number" step="0.01" name="jumlah_kebutuhan[]" value="{{ $resep->jumlah_kebutuhan }}" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-2xl focus:ring-1 focus:ring-gray-400 focus:border-gray-400 outline-none bg-white hpp-qty" required placeholder="0.00" oninput="calculateHPP()">
                            </div>
                            <div class="pt-6">
                                <button type="button" onclick="this.closest('.bahan-baku-row').remove(); calculateHPP()" class="w-9 h-9 flex items-center justify-center rounded-full bg-red-50 text-red-600 hover:bg-red-100 transition-colors">
                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="flex gap-3 items-start bahan-baku-row">
                            <div class="flex-1">
                                <label class="block text-[11px] font-semibold text-gray-500 mb-1">Pilih Bahan Baku</label>
                                <select name="bahan_baku_id[]" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-2xl focus:ring-1 focus:ring-gray-400 focus:border-gray-400 outline-none bg-white hpp-select" required onchange="calculateHPP()">
                                    <option value="" disabled selected data-harga="0">-- Pilih Bahan --</option>
                                    @foreach($bahanBakus as $bb)
                                        <option value="{{ $bb->id }}" data-harga="{{ $bb->harga_satuan }}">{{ $bb->nama_bahan }} ({{ $bb->satuan->nama_satuan ?? '-' }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="w-32">
                                <label class="block text-[11px] font-semibold text-gray-500 mb-1">Jml. Kebutuhan</label>
                                <input type="number" step="0.01" name="jumlah_kebutuhan[]" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-2xl focus:ring-1 focus:ring-gray-400 focus:border-gray-400 outline-none bg-white hpp-qty" required placeholder="0.00" oninput="calculateHPP()">
                            </div>
                            <div class="pt-6">
                                <button type="button" onclick="this.closest('.bahan-baku-row').remove(); calculateHPP()" class="w-9 h-9 flex items-center justify-center rounded-full bg-red-50 text-red-600 hover:bg-red-100 transition-colors">
                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>
                    @endforelse
                </div>

                <div class="mt-8 pt-5 border-t border-gray-100 flex justify-end gap-2">
                    <a href="{{ route('resep.index') }}" class="px-5 py-2.5 text-sm font-semibold text-gray-600 bg-white border border-gray-200 rounded-2xl hover:bg-gray-50 transition-colors">Batal</a>
                    <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-gray-900 rounded-2xl hover:bg-gray-800 transition-colors">Simpan Resep</button>
                </div>
            </form>
        </div>
    </div>
</div>

<template id="bahanBakuTemplate">
    <div class="flex gap-3 items-start bahan-baku-row mt-3">
        <div class="flex-1">
            <select name="bahan_baku_id[]" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-2xl focus:ring-1 focus:ring-gray-400 focus:border-gray-400 outline-none bg-white hpp-select" required onchange="calculateHPP()">
                <option value="" disabled selected data-harga="0">-- Pilih Bahan --</option>
                @foreach($bahanBakus as $bb)
                    <option value="{{ $bb->id }}" data-harga="{{ $bb->harga_satuan }}">{{ $bb->nama_bahan }} ({{ $bb->satuan->nama_satuan ?? '-' }})</option>
                @endforeach
            </select>
        </div>
        <div class="w-32">
            <input type="number" step="0.01" name="jumlah_kebutuhan[]" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-2xl focus:ring-1 focus:ring-gray-400 focus:border-gray-400 outline-none bg-white hpp-qty" required placeholder="0.00" oninput="calculateHPP()">
        </div>
        <div class="pt-2">
            <button type="button" onclick="this.closest('.bahan-baku-row').remove(); calculateHPP()" class="w-9 h-9 flex items-center justify-center rounded-full bg-red-50 text-red-600 hover:bg-red-100 transition-colors">
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </button>
        </div>
    </div>
</template>

@push('scripts')
<script>
    const hargaJual = {{ $menu->harga_jual ?? 0 }};

    function addBahanBakuRow() {
        const template = document.getElementById('bahanBakuTemplate');
        const container = document.getElementById('bahanBakuContainer');
        container.insertAdjacentHTML('beforeend', template.innerHTML);
    }

    function calculateHPP() {
        let totalHpp = 0;
        const rows = document.querySelectorAll('.bahan-baku-row');
        
        rows.forEach(row => {
            const select = row.querySelector('.hpp-select');
            const qtyInput = row.querySelector('.hpp-qty');
            
            if (select && qtyInput && select.selectedIndex > -1) {
                const option = select.options[select.selectedIndex];
                const harga = parseFloat(option.getAttribute('data-harga')) || 0;
                const qty = parseFloat(qtyInput.value) || 0;
                totalHpp += (harga * qty);
            }
        });

        // Update UI
        document.getElementById('textTotalHpp').innerText = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(totalHpp);
        
        const profit = hargaJual - totalHpp;
        const profitPercent = hargaJual > 0 ? (profit / hargaJual * 100) : 0;
        
        const textProfit = document.getElementById('textProfit');
        const cardProfit = document.getElementById('cardProfit');
        const iconProfit = document.getElementById('iconProfit');

        textProfit.innerText = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(profit) + ` (${profitPercent.toFixed(1)}%)`;

        // Color coding
        if (profit < 0) {
            textProfit.className = 'text-lg font-black text-red-600';
            iconProfit.className = 'w-10 h-10 rounded-full bg-red-50 flex items-center justify-center text-red-600';
        } else if (profitPercent < 30) {
            textProfit.className = 'text-lg font-black text-yellow-600';
            iconProfit.className = 'w-10 h-10 rounded-full bg-yellow-50 flex items-center justify-center text-yellow-600';
        } else {
            textProfit.className = 'text-lg font-black text-green-600';
            iconProfit.className = 'w-10 h-10 rounded-full bg-green-50 flex items-center justify-center text-green-600';
        }
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', calculateHPP);
</script>
@endpush
@endsection
