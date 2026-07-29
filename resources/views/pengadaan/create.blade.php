{{-- 
    Halaman: Buat Pengadaan Bahan Baku (Minimalist Edition)
    Desain: Clean, Elegant, Minimalist, High-Contrast, Ergonomis
--}}
@extends('layouts.pos')

@section('content')
<div class="flex-1 overflow-auto bg-gray-50/50 text-gray-800 font-sans">
    <div class="p-4 md:p-6 lg:p-8 max-w-[1000px] mx-auto space-y-6">
        
        {{-- Header --}}
        <div class="mb-2">
            <h1 class="text-lg font-bold text-gray-900">Pengadaan Bahan Baku</h1>
            <p class="text-xs text-gray-500 mt-0.5">Kelola permintaan pembelian dan penerimaan stok gudang</p>
        </div>

        {{-- TAB NAVIGASI / SUB MENU --}}
        <div class="flex items-center gap-1 bg-white border border-gray-200 p-1.5 rounded-xl mb-4 w-max shadow-sm">
            <a href="{{ route('pengadaan.index') }}" class="px-4 py-2 text-sm font-bold rounded-lg transition-colors {{ request()->routeIs('pengadaan.index') ? 'bg-[#0F2E23] text-white' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100' }}">
                Riwayat Pengadaan
            </a>
            <a href="{{ route('pengadaan.create') }}" class="px-4 py-2 text-sm font-bold rounded-lg transition-colors {{ request()->routeIs('pengadaan.create') ? 'bg-[#0F2E23] text-white' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100' }}">
                Buat Pembelian Bahan Baku
            </a>
        </div>

        <x-ui.alert />

        @if(isset($kodePesananError))
        <div class="bg-red-50 text-red-600 p-4 rounded-xl border border-red-200 text-sm mb-4">
            {{ $kodePesananError }}
        </div>
        @endif

        {{-- Tarik Kebutuhan dari Pesanan --}}
        <div class="bg-white rounded-2xl border border-gray-200/80 p-5 shadow-2xs space-y-3 mb-4">
            <div>
                <h3 class="text-sm font-bold text-gray-900">Tarik dari ID Pesanan</h3>
            </div>
            <form action="{{ route('pengadaan.create') }}" method="GET" class="flex gap-2">
                <input type="text" name="kode_pesanan" value="{{ request('kode_pesanan') }}" placeholder="Contoh: CTR-202607290001" class="w-full md:w-1/2 px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0F2E23]/10 focus:border-[#0F2E23] outline-none text-xs font-semibold">
                <button type="submit" class="px-4 py-2 bg-gray-800 hover:bg-gray-900 text-white rounded-xl text-xs font-bold transition-colors whitespace-nowrap">
                    Tarik Data
                </button>
            </form>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200/80 p-5 shadow-2xs space-y-4">
            <form action="{{ route('pengadaan.store') }}" method="POST" id="pengadaan-form" class="space-y-4">
                @csrf
                <div class="flex justify-between items-center border-b border-gray-100 pb-2">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">Form Permintaan Pembelian Bahan Baku</h3>
                        <p class="text-xs text-gray-500">Form ini akan menghasilkan dokumen pembelian. Stok bahan baku baru akan bertambah setelah pembelian "Diterima".</p>
                    </div>
                    <button type="button" onclick="addRow()" class="text-xs font-bold text-[#0F2E23] bg-emerald-50 border border-emerald-200 px-3 py-1.5 rounded-lg hover:bg-emerald-100 transition-colors inline-flex items-center gap-1">
                        <x-heroicon-o-plus class="w-3.5 h-3.5" /> Tambah Item
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Supplier <span class="text-gray-400 font-normal lowercase">(Opsional)</span></label>
                        <input type="text" name="asal_pembelian" value="{{ old('asal_pembelian') }}" placeholder="Contoh: PT. ABC / Pasar Induk" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0F2E23]/10 focus:border-[#0F2E23] outline-none text-xs font-medium">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Tanggal Pengadaan <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal_pengadaan" value="{{ old('tanggal_pengadaan', date('Y-m-d')) }}" required class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0F2E23]/10 focus:border-[#0F2E23] outline-none text-xs font-semibold">
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs min-w-[450px]">
                        <thead>
                            <tr class="bg-gray-50 text-gray-500 uppercase tracking-wider border-b border-gray-100">
                                <th class="px-2.5 py-2 font-semibold w-3/5">Bahan Baku</th>
                                <th class="px-2.5 py-2 font-semibold w-1/3">Jumlah Pembelian</th>
                                <th class="px-1 py-2 font-semibold text-center w-6"></th>
                            </tr>
                        </thead>
                        <tbody id="item-container" class="divide-y divide-gray-100 text-xs">
                            {{-- Rows akan dirender via JS --}}
                        </tbody>
                    </table>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Keterangan / Catatan Tambahan <span class="text-gray-400 font-normal lowercase">(Opsional)</span></label>
                    <textarea name="catatan" rows="2" placeholder="Catatan transaksi..." class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0F2E23]/10 focus:border-[#0F2E23] outline-none text-xs transition-all">{{ old('catatan') }}</textarea>
                </div>

                <div class="pt-2 border-t flex justify-end">
                    <button type="button" onclick="submitForm()" class="px-5 py-2.5 text-white bg-[#0F2E23] hover:bg-[#0a1f17] rounded-xl font-bold text-xs transition-all shadow-sm active:scale-95 flex items-center gap-2">
                        <x-heroicon-o-document-text class="w-4 h-4" /> Simpan & Hasilkan Form Pembelian
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>

<template id="row-template">
    <tr class="item-row">
        <td class="px-2 py-1.5 align-top">
            <select name="bahan_baku_id[]" required class="bahan-select w-full px-2 py-1.5 bg-gray-50 border border-gray-200 rounded-lg outline-none text-xs font-medium focus:border-[#0F2E23]" onchange="updateSatuan(this);">
                <option value="">— Pilih Bahan —</option>
                @foreach($bahanBakus as $bahan)
                    <option value="{{ $bahan->id }}" data-satuan="{{ $bahan->satuan->nama_satuan ?? '' }}">
                        {{ $bahan->nama_bahan }}
                    </option>
                @endforeach
            </select>
        </td>
        <td class="px-2 py-1.5 align-top">
            <div class="flex gap-1 items-center">
                <input type="number" name="jumlah[]" required min="0.01" step="0.01" class="jumlah-input w-full px-2 py-1.5 bg-gray-50 border border-gray-200 rounded-lg outline-none text-xs font-bold text-[#0F2E23]">
                <span class="text-[10px] text-gray-500 satuan-label font-bold min-w-[20px]">-</span>
            </div>
        </td>
        <td class="px-1 py-1.5 align-top text-center pt-2">
            <button type="button" class="text-red-500 hover:text-red-700 transition-colors" onclick="removeRow(this)">&times;</button>
        </td>
    </tr>
</template>

<script>
    function updateSatuan(select) {
        const option = select.options[select.selectedIndex];
        const satuan = option ? option.getAttribute('data-satuan') : '';
        const row = select.closest('tr');
        const label = row.querySelector('.satuan-label');
        label.textContent = satuan || '-';
    }

    function addRow() {
        const container = document.getElementById('item-container');
        if (!container) return null;
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
            if(container.children.length === 0) {
            addRow();
        }
        } else {
            alert('Form pengadaan minimal harus memiliki 1 item bahan baku.');
        }
    }

    function submitForm() {
        const container = document.getElementById('item-container');
        if (!container || container.querySelectorAll('.item-row').length === 0) {
            alert('Tambahkan minimal 1 item bahan baku ke form pengadaan.');
            return;
        }
        document.getElementById('pengadaan-form').submit();
    }

    document.addEventListener('DOMContentLoaded', function() {
        const prefillItems = @json($prefillItems ?? []);
        
        if (prefillItems && prefillItems.length > 0) {
            prefillItems.forEach(item => {
                const row = addRow();
                const select = row.querySelector('.bahan-select');
                const input = row.querySelector('.jumlah-input');
                select.value = item.bahan_baku_id;
                input.value = item.jumlah_beli;
                updateSatuan(select);
            });
        } else {
            addRow();
        }
    });
</script>
@endsection
