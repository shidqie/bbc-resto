@extends('layouts.pos')

@section('title') Tambah Paket Baru
@endsection

@section('content')
<div class="p-4 md:p-6 lg:p-8 max-w-[1200px] mx-auto">
<h1 class="text-2xl font-bold mb-6">Tambah Paket Baru</h1>
<div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mb-6">
    <div class="p-6">
        <form action="{{ route('paket-catering.store') }}" method="POST" id="paketForm">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Paket <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_paket" required class="w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20" placeholder="Contoh: Paket Hemat A">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Paket <span class="text-red-500">*</span></label>
                    <select name="jenis_paket" required class="w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20">
                        <option value="catering" {{ request('jenis') == 'catering' ? 'selected' : '' }}>Catering</option>
                        <option value="nasi_box" {{ request('jenis') == 'nasi_box' ? 'selected' : '' }}>Nasi Box</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Harga / Porsi (Rp) <span class="text-red-500">*</span></label>
                    <input type="number" name="harga" required min="0" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20" placeholder="25000">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi Singkat</label>
                    <input type="text" name="deskripsi" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20" placeholder="Cocok untuk acara kantor">
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
                <!-- Template Komponen -->
            </div>

            <div class="mt-8 flex justify-end gap-3">
                <a href="{{ url()->previous() }}" class="px-5 py-2.5 text-gray-600 font-bold border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Batal</a>
                <button type="submit" class="px-5 py-2.5 bg-primary text-white font-bold rounded-lg shadow-sm hover:bg-primary/90 transition-colors">Simpan Paket</button>
            </div>
        </form>
    </div>
</div>

<script>
    let kompIndex = 0;
    
    // Data menu dari server
    const allMenus = @json($menus);

    function addKomponen() {
        const container = document.getElementById('komponenContainer');
        
        let menuOptions = '';
        allMenus.forEach(menu => {
            menuOptions += `<label class="flex items-center gap-2 text-sm p-2 bg-gray-50 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-100 transition-colors">
                                <input type="checkbox" name="komponen[${kompIndex}][menu_id][]" value="${menu.id}" class="text-primary rounded focus:ring-primary">
                                <span>${menu.nama}</span>
                            </label>`;
        });

        const html = `
        <div class="komponen-card bg-gray-50 border border-gray-200 p-4 rounded-xl relative" id="komp_${kompIndex}">
            <button type="button" onclick="document.getElementById('komp_${kompIndex}').remove()" class="absolute top-4 right-4 text-red-500 hover:text-red-700 bg-red-50 p-1 rounded-md">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4 pr-10">
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">Nama Komponen</label>
                    <input type="text" name="komponen[${kompIndex}][nama_komponen]" required placeholder="Cth: Lauk Utama" class="w-full text-sm border-gray-300 rounded-md focus:ring-primary focus:border-primary">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">Tipe Pilihan</label>
                    <select name="komponen[${kompIndex}][tipe]" required class="w-full text-sm border-gray-300 rounded-md focus:ring-primary focus:border-primary">
                        <option value="fixed">Pasti Dapat Semua</option>
                        <option value="choice">Pilih Salah Satu</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">Urutan Tampil</label>
                    <input type="number" name="komponen[${kompIndex}][urutan]" required value="${kompIndex + 1}" class="w-full text-sm border-gray-300 rounded-md focus:ring-primary focus:border-primary">
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

    // Add first component by default
    document.addEventListener('DOMContentLoaded', () => {
        addKomponen();
    });
</script>
</div>
@endsection
