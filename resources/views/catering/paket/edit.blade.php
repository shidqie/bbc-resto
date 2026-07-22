{{-- 
    Halaman: Edit Paket Catering / Nasi Box (Minimalist Edition - Exact Mockup Style)
--}}
@extends('layouts.pos')

@section('title', 'Edit Paket: ' . $paketCatering->nama_paket)

@section('content')
<div class="flex-1 overflow-auto bg-gray-50/50 text-gray-800 font-sans">
    <div class="p-4 md:p-6 lg:p-8 max-w-[1100px] mx-auto space-y-6">
        
        {{-- Header --}}
        <x-ui.page-header 
            title="Edit Paket: {{ $paketCatering->nama_paket }}" 
            subtitle="Ubah informasi harga, susunan komponen, dan pilihan menu paket"
            :breadcrumbs="['Paket Menu', 'Edit Paket']">
            <x-slot:actions>
                <x-ui.button href="{{ route('paket-catering.index', ['jenis' => $paketCatering->jenis_paket]) }}" variant="outline" icon="fa-arrow-left">Kembali</x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.alert />

        <div class="bg-white rounded-2xl border border-gray-200/80 shadow-2xs p-6 space-y-6">
            <form action="{{ route('paket-catering.update', $paketCatering->id) }}" method="POST" id="paketForm" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- Informasi Utama Paket --}}
                <div class="space-y-4">
                    <h3 class="text-sm font-extrabold text-gray-900 border-b border-gray-100 pb-2">Informasi Utama Paket</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Nama Paket <span class="text-red-500">*</span></label>
                            <input type="text" name="nama_paket" required value="{{ old('nama_paket', $paketCatering->nama_paket) }}" 
                                   class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0F2E23]/10 focus:border-[#0F2E23] outline-none text-xs font-extrabold text-gray-900">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Jenis Paket <span class="text-red-500">*</span></label>
                            <select name="jenis_paket" required class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0F2E23]/10 focus:border-[#0F2E23] outline-none text-xs font-bold text-gray-900">
                                <option value="catering" {{ old('jenis_paket', $paketCatering->jenis_paket) == 'catering' ? 'selected' : '' }}>Catering</option>
                                <option value="nasi_box" {{ old('jenis_paket', $paketCatering->jenis_paket) == 'nasi_box' ? 'selected' : '' }}>Nasi Box</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Harga / Porsi (Rp) <span class="text-red-500">*</span></label>
                            <input type="number" name="harga" required min="0" value="{{ old('harga', $paketCatering->harga) }}" 
                                   class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0F2E23]/10 focus:border-[#0F2E23] outline-none text-xs font-black text-[#0F2E23]">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Deskripsi Singkat</label>
                            <input type="text" name="deskripsi" value="{{ old('deskripsi', $paketCatering->deskripsi) }}" placeholder="Contoh: Paket hemat prasmanan lengkap…" 
                                   class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0F2E23]/10 focus:border-[#0F2E23] outline-none text-xs font-medium">
                        </div>
                    </div>
                </div>

                {{-- Komponen Paket --}}
                <div class="space-y-4 pt-2">
                    <div class="flex justify-between items-center border-b border-gray-100 pb-2">
                        <div>
                            <h3 class="text-sm font-extrabold text-gray-900">Komponen & Pilihan Menu</h3>
                            <p class="text-xs text-gray-500">Kelompokkan menu dalam bentuk pill pilihan (seperti Aneka Sup, Aneka Daging, dll)</p>
                        </div>
                        
                        <button type="button" onclick="addKomponen()" class="px-4 py-2 bg-emerald-50 hover:bg-emerald-100 text-[#0F2E23] border border-emerald-200 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 shadow-2xs">
                            <x-heroicon-o-plus class="w-4 h-4" /> Tambah Komponen
                        </button>
                    </div>

                    <div id="komponenContainer" class="space-y-4">
                        {{-- Rendered via JS --}}
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-100 flex justify-end gap-2">
                    <a href="{{ route('paket-catering.index', ['jenis' => $paketCatering->jenis_paket]) }}" class="px-5 py-2.5 rounded-xl border border-gray-200 text-xs font-bold text-gray-600 hover:bg-gray-50 transition-all">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#0F2E23] hover:bg-[#0a1f17] text-white text-xs font-extrabold transition-all shadow-sm active:scale-95 flex items-center gap-2">
                        <x-heroicon-o-check class="w-4 h-4" /> Simpan Perubahan Paket
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>

<script>
    let kompIndex = 0;
    const allMenus = @json($menus);
    const existingKomponen = @json($paketCatering->komponens);

    function addKomponen(data = null) {
        const container = document.getElementById('komponenContainer');
        
        const currentKompIndex = kompIndex;
        const namaVal = data ? data.nama_komponen : '';
        const tipeVal = data ? data.tipe : 'choice';
        const urutanVal = data ? data.urutan : (container.children.length + 1);

        let menuChipsHtml = '';
        allMenus.forEach(menu => {
            let isChecked = false;
            if (data && data.opsi) {
                isChecked = data.opsi.some(opsi => opsi.menu_id === menu.id);
            }
            
            menuChipsHtml += `
                <label class="menu-chip-label inline-flex items-center gap-1.5 px-4 py-2 rounded-full border cursor-pointer select-none transition-all text-xs ${isChecked ? 'bg-[#0F2E23] text-white border-[#0F2E23] font-bold shadow-2xs' : 'bg-white text-gray-800 border-gray-200 hover:bg-gray-50 font-medium shadow-2xs'}" data-menu-name="${menu.nama.toLowerCase()}">
                    <input type="checkbox" name="komponen[${currentKompIndex}][menu_id][]" value="${menu.id}" ${isChecked ? 'checked' : ''} class="hidden" onchange="toggleChipStyle(this)">
                    <span>${menu.nama}</span>
                    <span class="check-icon text-emerald-400 font-bold ${isChecked ? '' : 'hidden'}">✓</span>
                </label>
            `;
        });

        const html = `
        <div class="komponen-card bg-gray-50/80 border border-gray-200/90 p-4 rounded-2xl relative shadow-2xs space-y-3" id="komp_${currentKompIndex}">
            <button type="button" onclick="document.getElementById('komp_${currentKompIndex}').remove()" class="absolute top-3.5 right-3.5 text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 p-1.5 rounded-xl transition-colors" title="Hapus Komponen">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 pr-10">
                <div>
                    <label class="block text-[11px] font-bold text-gray-600 uppercase tracking-wide mb-1">Nama Komponen</label>
                    <input type="text" name="komponen[${currentKompIndex}][nama_komponen]" required value="${namaVal}" placeholder="Cth: Aneka sup / Sayuran" class="w-full text-xs font-bold px-3.5 py-2 border border-gray-200 bg-white rounded-xl focus:border-[#0F2E23] outline-none">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-600 uppercase tracking-wide mb-1">Tipe Pilihan</label>
                    <select name="komponen[${currentKompIndex}][tipe]" required class="w-full text-xs font-bold px-3.5 py-2 border border-gray-200 bg-white rounded-xl focus:border-[#0F2E23] outline-none">
                        <option value="choice" ${tipeVal === 'choice' ? 'selected' : ''}>Pilih 1 (Pilihan Konsumen)</option>
                        <option value="fixed" ${tipeVal === 'fixed' ? 'selected' : ''}>Pasti Dapat (Semua)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-600 uppercase tracking-wide mb-1">Urutan Tampil</label>
                    <input type="number" name="komponen[${currentKompIndex}][urutan]" required value="${urutanVal}" class="w-full text-xs font-bold px-3.5 py-2 border border-gray-200 bg-white rounded-xl focus:border-[#0F2E23] outline-none">
                </div>
            </div>
            
            <div class="pt-2 border-t border-gray-200/70">
                <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-2 mb-2.5">
                    <label class="text-[11px] font-extrabold text-gray-700 uppercase tracking-wide">Pilih Menu yang Masuk dalam Komponen Ini:</label>
                    <input type="text" placeholder="🔍 Cari menu…" onkeyup="filterMenuChips(this)" class="px-3.5 py-1 bg-white border border-gray-200 rounded-xl text-xs outline-none w-full sm:w-48">
                </div>
                <div class="menu-chips-wrapper flex flex-wrap gap-2 max-h-48 overflow-y-auto p-2.5 bg-white rounded-xl border border-gray-200/80">
                    ${menuChipsHtml}
                </div>
            </div>
        </div>
        `;

        container.insertAdjacentHTML('beforeend', html);
        kompIndex++;
    }

    function toggleChipStyle(checkbox) {
        const label = checkbox.closest('label');
        const checkIcon = label.querySelector('.check-icon');
        if (checkbox.checked) {
            label.className = 'menu-chip-label inline-flex items-center gap-1.5 px-4 py-2 rounded-full border cursor-pointer select-none transition-all text-xs bg-[#0F2E23] text-white border-[#0F2E23] font-bold shadow-2xs';
            if (checkIcon) checkIcon.classList.remove('hidden');
        } else {
            label.className = 'menu-chip-label inline-flex items-center gap-1.5 px-4 py-2 rounded-full border cursor-pointer select-none transition-all text-xs bg-white text-gray-800 border-gray-200 hover:bg-gray-50 font-medium shadow-2xs';
            if (checkIcon) checkIcon.classList.add('hidden');
        }
    }

    function filterMenuChips(input) {
        const query = input.value.toLowerCase();
        const wrapper = input.closest('.komponen-card').querySelector('.menu-chips-wrapper');
        const labels = wrapper.querySelectorAll('.menu-chip-label');
        
        labels.forEach(label => {
            const name = label.getAttribute('data-menu-name') || '';
            if (name.includes(query)) {
                label.style.display = 'inline-flex';
            } else {
                label.style.display = 'none';
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (existingKomponen && existingKomponen.length > 0) {
            existingKomponen.forEach(komp => addKomponen(komp));
        } else {
            addKomponen();
        }
    });
</script>
@endsection
