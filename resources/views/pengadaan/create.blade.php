{{-- 
    Halaman: Buat Pengadaan
    Deskripsi: Form untuk mencatat pengadaan (pembelian) bahan baku.
--}}
@extends('layouts.pos')

@section('content')
<div class="flex-1 overflow-auto bg-gray-50/50 text-gray-800 font-sans">
    <div class="p-4 md:p-6 lg:p-8 max-w-[1000px] mx-auto space-y-6">
        
        {{-- Header --}}
        <x-ui.page-header 
            title="Buat Pengadaan" 
            :breadcrumbs="['Pengadaan', 'Daftar Pengadaan', 'Buat Baru']">
            <x-slot:actions>
                <x-ui.button href="{{ route('pengadaan.index') }}" variant="outline" icon="fa-arrow-left">Kembali</x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.alert />

        <form action="{{ route('pengadaan.store') }}" method="POST" id="pengadaan-form">
            @csrf
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Data Pengadaan --}}
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
                        <h2 class="text-lg font-bold text-gray-900 border-b border-gray-100 pb-2 mb-4">Informasi Utama</h2>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Pengadaan <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_pengadaan" value="{{ old('tanggal_pengadaan', date('Y-m-d')) }}" required class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] outline-none text-sm transition-all">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Supplier <span class="text-red-500">*</span></label>
                            <select name="supplier_id" required class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] outline-none text-sm transition-all">
                                <option value="">Pilih Supplier</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->nama_supplier }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                            <textarea name="catatan" rows="3" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] outline-none text-sm transition-all">{{ old('catatan') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Detail Item --}}
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                        <div class="flex justify-between items-center mb-4 border-b border-gray-100 pb-2">
                            <h2 class="text-lg font-bold text-gray-900">Item Bahan Baku</h2>
                            <button type="button" onclick="addRow()" class="inline-flex items-center gap-2 text-sm font-medium text-[#3B82F6] bg-blue-50 px-3 py-1.5 rounded-lg hover:bg-blue-100 transition-colors">
                                <x-heroicon-o-plus class="w-5 h-5 inline-block shrink-0" /> Tambah Item
                            </button>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse min-w-[600px]">
                                <thead>
                                    <tr class="bg-gray-50/50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                                        <th class="px-3 py-2 font-semibold w-2/5">Bahan Baku</th>
                                        <th class="px-3 py-2 font-semibold w-1/5">Jumlah</th>
                                        <th class="px-3 py-2 font-semibold w-1/4">Harga Satuan</th>
                                        <th class="px-3 py-2 font-semibold w-1/4">Subtotal</th>
                                        <th class="px-2 py-2 font-semibold text-center w-10"></th>
                                    </tr>
                                </thead>
                                <tbody id="item-container" class="divide-y divide-gray-100 text-sm">
                                    {{-- Rows akan dirender di sini via JS --}}
                                </tbody>
                                <tfoot>
                                    <tr class="border-t-2 border-gray-100 bg-gray-50">
                                        <td colspan="3" class="px-3 py-4 text-right font-bold text-gray-900">TOTAL BIAYA:</td>
                                        <td class="px-3 py-4 font-black text-[#3B82F6] text-lg" id="total-biaya-display">Rp 0</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="mt-8 flex justify-end gap-3 pt-6 border-t border-gray-100">
                            <x-ui.button href="{{ route('pengadaan.index') }}" variant="outline">Batal</x-ui.button>
                            <button type="button" onclick="submitForm()" class="px-5 py-2.5 text-white bg-[#3B82F6] hover:bg-[#2563EB] rounded-xl font-medium transition-colors shadow-sm flex items-center gap-2">
                                <x-heroicon-o-document-check class="w-5 h-5 inline-block shrink-0" /> Simpan Pengadaan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<template id="row-template">
    <tr class="item-row">
        <td class="px-2 py-3 align-top">
            <select name="bahan_baku_id[]" required class="bahan-select w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] outline-none text-sm transition-all" onchange="updateSatuan(this); calculateSubtotal(this);">
                <option value="">Pilih Bahan</option>
                @foreach($bahanBakus as $bahan)
                    <option value="{{ $bahan->id }}" data-satuan="{{ $bahan->satuan->nama_satuan ?? '' }}">
                        {{ $bahan->nama_bahan }}
                    </option>
                @endforeach
            </select>
        </td>
        <td class="px-2 py-3 align-top">
            <div class="flex gap-2 items-center">
                <input type="number" name="jumlah[]" required min="0.01" step="0.01" class="jumlah-input w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] outline-none text-sm transition-all" oninput="calculateSubtotal(this)">
                <span class="text-xs text-gray-500 satuan-label font-medium min-w-[30px]">-</span>
            </div>
        </td>
        <td class="px-2 py-3 align-top">
            <input type="number" name="harga_satuan[]" required min="0" step="1" class="harga-input w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] outline-none text-sm transition-all" oninput="calculateSubtotal(this)">
        </td>
        <td class="px-2 py-3 align-top">
            <input type="text" readonly class="subtotal-display w-full px-3 py-2 bg-gray-100 border border-transparent rounded-xl text-gray-500 font-medium text-sm outline-none cursor-not-allowed" value="Rp 0">
            <input type="hidden" name="subtotal_hidden[]" class="subtotal-hidden" value="0">
        </td>
        <td class="px-2 py-3 align-top text-center pt-4">
            <button type="button" class="text-red-500 hover:text-red-700 transition-colors" onclick="removeRow(this)">
                <x-heroicon-o-trash class="w-5 h-5 inline-block shrink-0" />
            </button>
        </td>
    </tr>
</template>

<script>
    function updateSatuan(select) {
        const option = select.options[select.selectedIndex];
        const satuan = option.getAttribute('data-satuan');
        const row = select.closest('tr');
        const label = row.querySelector('.satuan-label');
        if (satuan) {
            label.textContent = satuan;
        } else {
            label.textContent = '-';
        }
    }

    function calculateSubtotal(element) {
        const row = element.closest('tr');
        const jumlah = parseFloat(row.querySelector('.jumlah-input').value) || 0;
        const harga = parseFloat(row.querySelector('.harga-input').value) || 0;
        
        const subtotal = jumlah * harga;
        
        row.querySelector('.subtotal-hidden').value = subtotal;
        row.querySelector('.subtotal-display').value = 'Rp ' + new Intl.NumberFormat('id-ID').format(subtotal);
        
        calculateTotal();
    }

    function calculateTotal() {
        let total = 0;
        document.querySelectorAll('.subtotal-hidden').forEach(input => {
            total += parseFloat(input.value) || 0;
        });
        
        document.getElementById('total-biaya-display').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
    }

    function addRow() {
        const container = document.getElementById('item-container');
        const template = document.getElementById('row-template');
        const clone = template.content.cloneNode(true);
        container.appendChild(clone);
        const rows = container.querySelectorAll('.item-row');
        return rows[rows.length - 1];
    }

    function removeRow(btn) {
        const container = document.getElementById('item-container');
        if (container.querySelectorAll('.item-row').length > 1) {
            btn.closest('tr').remove();
            calculateTotal();
        } else {
            alert('Pengadaan minimal harus memiliki 1 item.');
        }
    }

    function submitForm() {
        const container = document.getElementById('item-container');
        if (container.querySelectorAll('.item-row').length === 0) {
            alert('Tambahkan minimal 1 item pengadaan.');
            return;
        }
        document.getElementById('pengadaan-form').submit();
    }

    document.addEventListener('DOMContentLoaded', function() {
        const prepopulate = @json($prepopulate ?? []);
        if (prepopulate.length > 0) {
            prepopulate.forEach(item => {
                const row = addRow();
                if (row) {
                    const select = row.querySelector('.bahan-select');
                    select.value = item.bahan_baku_id;
                    updateSatuan(select);
                    
                    const jumlahInput = row.querySelector('.jumlah-input');
                    jumlahInput.value = item.jumlah;
                    calculateSubtotal(jumlahInput);
                }
            });
        } else {
            // Add 1 default row on load
            addRow();
        }
    });
</script>
@endsection
