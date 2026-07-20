@extends('layouts.pos')

@section('title') Edit Paket: {{ $paketCatering->nama_paket }}
@endsection

@section('content')
<div class="p-4 md:p-6 lg:p-8 max-w-[1200px] mx-auto">
<h1 class="text-2xl font-bold mb-6">Edit Paket: {{ $paketCatering->nama_paket }}</h1>
<div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mb-6">
    <div class="p-6">
        <form action="{{ route('paket-catering.update', $paketCatering->id) }}" method="POST" id="paketForm">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Paket <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_paket" required value="{{ $paketCatering->nama_paket }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Paket <span class="text-red-500">*</span></label>
                    <select name="jenis_paket" required class="w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20">
                        <option value="catering" {{ $paketCatering->jenis_paket == 'catering' ? 'selected' : '' }}>Catering</option>
                        <option value="nasi_box" {{ $paketCatering->jenis_paket == 'nasi_box' ? 'selected' : '' }}>Nasi Box</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Harga / Porsi (Rp) <span class="text-red-500">*</span></label>
                    <input type="number" name="harga" required min="0" value="{{ $paketCatering->harga }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi Singkat</label>
                    <input type="text" name="deskripsi" value="{{ $paketCatering->deskripsi }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20">
                </div>
            </div>

            <hr class="mb-6 border-gray-100">
            
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-gray-900">Komponen Paket (Lauk, Sayur, dll)</h3>
                <button type="button" onclick="addKomponen()" class="text-sm bg-blue-50 text-blue-600 px-3 py-1.5 rounded-lg font-bold hover:bg-blue-100 border border-blue-200 transition-colors">
                    + Tambah Komponen
                </button>
            </div>

            <div id="komponenContainer" class="space-y-4">
                <!-- Template Komponen Diisi dari JS -->
            </div>

            <div class="mt-8 flex justify-end gap-3">
                <a href="{{ route('paket-catering.index') }}" class="px-5 py-2.5 text-gray-600 font-bold border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Batal</a>
                <button type="submit" class="px-5 py-2.5 bg-primary text-white font-bold rounded-lg shadow-sm hover:bg-primary/90 transition-colors">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
    let kompIndex = 0;
    const allMenus = @json($menus);
    
    // Data komponen yang sudah ada dari database
    const existingKomponen = @json($paketCatering->komponens);

    function addKomponen(data = null) {
        const container = document.getElementById('komponenContainer');
        
        let menuOptions = '';
        allMenus.forEach(menu => {
            // Check if this menu is selected in the existing data
            let isChecked = false;
            if (data && data.opsi) {
                isChecked = data.opsi.some(opsi => opsi.menu_id === menu.id);
            }
            
            menuOptions += `<label class="flex items-center gap-2 text-sm p-2 bg-gray-50 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-100 transition-colors">
                                <input type="checkbox" name="komponen[${kompIndex}][menu_id][]" value="${menu.id}" ${isChecked ? 'checked' : ''} class="text-primary rounded focus:ring-primary">
                                <span>${menu.nama}</span>
                            </label>`;
        });

        const namaVal = data ? data.nama_komponen : '';
        const tipeVal = data ? data.tipe : 'fixed';
        const urutanVal = data ? data.urutan : (kompIndex + 1);

        const html = `
        <div class="komponen-card bg-gray-50 border border-gray-200 p-4 rounded-xl relative" id="komp_${kompIndex}">
            <button type="button" onclick="document.getElementById('komp_${kompIndex}').remove()" class="absolute top-4 right-4 text-red-500 hover:text-red-700 bg-red-50 p-1 rounded-md">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4 pr-10">
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">Nama Komponen</label>
                    <input type="text" name="komponen[${kompIndex}][nama_komponen]" required value="${namaVal}" placeholder="Cth: Lauk Utama" class="w-full text-sm border-gray-300 rounded-md focus:ring-primary focus:border-primary">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">Tipe Pilihan</label>
                    <select name="komponen[${kompIndex}][tipe]" required class="w-full text-sm border-gray-300 rounded-md focus:ring-primary focus:border-primary">
                        <option value="fixed" ${tipeVal === 'fixed' ? 'selected' : ''}>Pasti Dapat Semua</option>
                        <option value="choice" ${tipeVal === 'choice' ? 'selected' : ''}>Pilih Salah Satu</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">Urutan Tampil</label>
                    <input type="number" name="komponen[${kompIndex}][urutan]" required value="${urutanVal}" class="w-full text-sm border-gray-300 rounded-md focus:ring-primary focus:border-primary">
                </div>
            </div>
            
            <div>
                <label class="block text-xs font-bold text-gray-600 mb-2">Pilih Menu yang Tersedia untuk Komponen Ini (Boleh lebih dari 1)</label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                    ${menuOptions}
                </div>
            </div>
        </div>
        `;
        
        container.insertAdjacentHTML('beforeend', html);
        kompIndex++;
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (existingKomponen && existingKomponen.length > 0) {
            existingKomponen.forEach(komp => {
                addKomponen(komp);
            });
        } else {
            addKomponen();
        }
    });
</script>
</div>
@endsection
