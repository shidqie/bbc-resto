{{-- 
    Halaman: Atur Resep
    Deskripsi: Form untuk memetakan bahan baku ke dalam sebuah menu (resep).
--}}
@extends('layouts.pos')

@section('content')
<div class="flex-1 overflow-auto bg-gray-50/50 text-gray-800 font-sans">
    <div class="w-full p-6 ] space-y-6">
        
        {{-- Header --}}
        <x-ui.page-header 
            title="Atur Resep Menu" 
            subtitle="Pemetaan bahan baku untuk: {{ $menu->nama }}"
            :breadcrumbs="['Manajemen Menu', 'Resep Menu', 'Detail']">
            <x-slot:actions>
                <x-ui.button href="{{ route('menu.show', $menu->id) }}" variant="outline" icon="arrow-left">Kembali</x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.alert />

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <form action="{{ route('resep.store', $menu->id) }}" method="POST" class="p-6">
                @csrf
                
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6 flex items-start gap-3">
                    <x-heroicon-o-information-circle class="text-blue-500 mt-0.5 w-5 h-5 inline-block shrink-0" />
                    <div>
                        <h4 class="text-sm font-bold text-blue-900">Petunjuk Pengisian</h4>
                        <p class="text-xs text-blue-700 mt-1">
                            Pilih bahan baku dan masukkan jumlah kebutuhan untuk <strong>1 porsi</strong>. Sistem akan mengalikan kebutuhan ini saat ada pesanan masuk.
                        </p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[700px]">
                        <thead>
                            <tr class="bg-gray-50/50 text-gray-500 text-sm uppercase tracking-wider border-b border-gray-100">
                                <th class="px-4 py-3 font-semibold w-1/3">Bahan Baku</th>
                                <th class="px-4 py-3 font-semibold w-1/4">Kebutuhan</th>
                                <th class="px-4 py-3 font-semibold w-1/3">Keterangan</th>
                                <th class="px-4 py-3 font-semibold w-12 text-center"></th>
                            </tr>
                        </thead>
                        <tbody id="resep-container" class="divide-y divide-gray-100 text-sm">
                            @if(old('bahan_baku_id'))
                                @foreach(old('bahan_baku_id') as $index => $oldBahanId)
                                    <tr class="resep-row">
                                        <td class="px-4 py-3 align-top">
                                            <select name="bahan_baku_id[]" required class="bahan-select w-full px-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] outline-none bg-white text-sm" onchange="updateSatuan(this)">
                                                <option value="">Pilih Bahan</option>
                                                @foreach($bahanBakus as $bahan)
                                                    <option value="{{ $bahan->id }}" data-satuan="{{ $bahan->satuan->nama_satuan ?? '' }}" {{ $oldBahanId == $bahan->id ? 'selected' : '' }}>
                                                        {{ $bahan->nama_bahan }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="px-4 py-3 align-top">
                                            <div class="flex gap-2 items-center">
                                                <input type="number" name="jumlah_kebutuhan[]" value="{{ old('jumlah_kebutuhan')[$index] }}" required min="0.01" step="0.01" class="w-24 px-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] outline-none text-sm">
                                                <span class="text-xs text-gray-500 satuan-label font-medium min-w-[40px]">
                                                    {{-- Let JS handle this on load --}}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 align-top">
                                            <input type="text" name="keterangan[]" value="{{ old('keterangan')[$index] }}" placeholder="Misal: untuk garnish" class="w-full px-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] outline-none text-sm">
                                        </td>
                                        <td class="px-4 py-3 align-top text-center pt-4">
                                            <button type="button" class="text-red-500 hover:text-red-700 transition-colors" onclick="removeRow(this)">
                                                <x-heroicon-o-x-mark class="w-5 h-5 inline-block shrink-0" />
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            @elseif($menu->resep->count() > 0)
                                @foreach($menu->resep as $resep)
                                    <tr class="resep-row">
                                        <td class="px-4 py-3 align-top">
                                            <select name="bahan_baku_id[]" required class="bahan-select w-full px-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] outline-none bg-white text-sm" onchange="updateSatuan(this)">
                                                <option value="">Pilih Bahan</option>
                                                @foreach($bahanBakus as $bahan)
                                                    <option value="{{ $bahan->id }}" data-satuan="{{ $bahan->satuan->nama_satuan ?? '' }}" {{ $resep->bahan_baku_id == $bahan->id ? 'selected' : '' }}>
                                                        {{ $bahan->nama_bahan }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="px-4 py-3 align-top">
                                            <div class="flex gap-2 items-center">
                                                <input type="number" name="jumlah_kebutuhan[]" value="{{ (float)$resep->jumlah_kebutuhan }}" required min="0.01" step="0.01" class="w-24 px-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] outline-none text-sm">
                                                <span class="text-xs text-gray-500 satuan-label font-medium min-w-[40px]">
                                                    {{ $resep->satuan }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 align-top">
                                            <input type="text" name="keterangan[]" value="{{ $resep->keterangan }}" placeholder="Opsional" class="w-full px-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] outline-none text-sm">
                                        </td>
                                        <td class="px-4 py-3 align-top text-center pt-4">
                                            <button type="button" class="text-red-500 hover:text-red-700 transition-colors" onclick="removeRow(this)">
                                                <x-heroicon-o-x-mark class="w-5 h-5 inline-block shrink-0" />
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <!-- Baris Default Jika Kosong -->
                                <tr class="resep-row">
                                    <td class="px-4 py-3 align-top">
                                        <select name="bahan_baku_id[]" required class="bahan-select w-full px-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] outline-none bg-white text-sm" onchange="updateSatuan(this)">
                                            <option value="">Pilih Bahan</option>
                                            @foreach($bahanBakus as $bahan)
                                                <option value="{{ $bahan->id }}" data-satuan="{{ $bahan->satuan->nama_satuan ?? '' }}">
                                                    {{ $bahan->nama_bahan }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-4 py-3 align-top">
                                        <div class="flex gap-2 items-center">
                                            <input type="number" name="jumlah_kebutuhan[]" required min="0.01" step="0.01" class="w-24 px-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] outline-none text-sm">
                                            <span class="text-xs text-gray-500 satuan-label font-medium min-w-[40px]">-</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 align-top">
                                        <input type="text" name="keterangan[]" placeholder="Opsional" class="w-full px-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] outline-none text-sm">
                                    </td>
                                    <td class="px-4 py-3 align-top text-center pt-4">
                                        <button type="button" class="text-red-500 hover:text-red-700 transition-colors" onclick="removeRow(this)">
                                            <x-heroicon-o-x-mark class="w-5 h-5 inline-block shrink-0" />
                                        </button>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    <button type="button" onclick="addRow()" class="inline-flex items-center gap-2 text-sm font-medium text-blue-600 bg-blue-50 px-4 py-2 rounded-xl hover:bg-blue-100 transition-colors">
                        <x-heroicon-o-plus class="w-5 h-5 inline-block shrink-0" /> Tambah Bahan
                    </button>
                </div>

                <div class="flex justify-end gap-3 pt-6 mt-6 border-t border-gray-100">
                    <x-ui.button href="{{ route('menu.show', $menu->id) }}" variant="outline">Batal</x-ui.button>
                    <x-ui.button type="submit" icon="document-arrow-down">Simpan Resep</x-ui.button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Template untuk baris baru --}}
<template id="row-template">
    <tr class="resep-row">
        <td class="px-4 py-3 align-top">
            <select name="bahan_baku_id[]" required class="bahan-select w-full px-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] outline-none bg-white text-sm" onchange="updateSatuan(this)">
                <option value="">Pilih Bahan</option>
                @foreach($bahanBakus as $bahan)
                    <option value="{{ $bahan->id }}" data-satuan="{{ $bahan->satuan->nama_satuan ?? '' }}">
                        {{ $bahan->nama_bahan }}
                    </option>
                @endforeach
            </select>
        </td>
        <td class="px-4 py-3 align-top">
            <div class="flex gap-2 items-center">
                <input type="number" name="jumlah_kebutuhan[]" required min="0.01" step="0.01" class="w-24 px-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] outline-none text-sm">
                <span class="text-xs text-gray-500 satuan-label font-medium min-w-[40px]">-</span>
            </div>
        </td>
        <td class="px-4 py-3 align-top">
            <input type="text" name="keterangan[]" placeholder="Opsional" class="w-full px-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] outline-none text-sm">
        </td>
        <td class="px-4 py-3 align-top text-center pt-4">
            <button type="button" class="text-red-500 hover:text-red-700 transition-colors" onclick="removeRow(this)">
                <x-heroicon-o-x-mark class="w-5 h-5 inline-block shrink-0" />
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

    function addRow() {
        const container = document.getElementById('resep-container');
        const template = document.getElementById('row-template');
        const clone = template.content.cloneNode(true);
        container.appendChild(clone);
    }

    function removeRow(btn) {
        const container = document.getElementById('resep-container');
        if (container.querySelectorAll('.resep-row').length > 1) {
            btn.closest('tr').remove();
        } else {
            window.showToast('info', 'Resep harus memiliki minimal 1 bahan baku.');
        }
    }

    // Update satuan on load for old inputs
    document.addEventListener('DOMContentLoaded', function() {
        const selects = document.querySelectorAll('.bahan-select');
        selects.forEach(select => {
            if (select.value) {
                updateSatuan(select);
            }
        });
    });
</script>
@endsection
