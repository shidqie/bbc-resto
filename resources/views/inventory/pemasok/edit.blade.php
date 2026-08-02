@extends('layouts.pos')
@section('content')
<div class="flex-1 overflow-auto bg-gray-50/50">
    <div class="w-full p-6 space-y-6">
        <x-ui.page-header title="Edit Data Pemasok" :breadcrumbs="['Inventory', 'Pemasok', 'Edit']">
            <x-slot:actions>
                <a href="{{ route('pemasok.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 text-sm font-semibold rounded-3xl hover:bg-gray-200 transition">
                    &larr; Batal
                </a>
            </x-slot:actions>
        </x-ui.page-header>
        
        <x-ui.alert />

        <div class="bg-white rounded-[2.25rem] border border-gray-100 shadow-sm overflow-hidden">
            <form action="{{ route('pemasok.update', $pemasok->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="p-6 space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kode Pemasok</label>
                        <input type="text" value="{{ $pemasok->kode_pemasok }}" readonly class="w-full border border-gray-200 rounded-3xl px-4 py-2.5 text-sm bg-gray-100 text-gray-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Pemasok / Perusahaan *</label>
                        <input type="text" name="nama_pemasok" value="{{ old('nama_pemasok', $pemasok->nama_pemasok) }}" required class="w-full border border-gray-200 rounded-3xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6]">
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Kontak Person</label>
                            <input type="text" name="nama_kontak" value="{{ old('nama_kontak', $pemasok->nama_kontak) }}" class="w-full border border-gray-200 rounded-3xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6]">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Telepon</label>
                            <input type="text" name="nomor_telepon" value="{{ old('nomor_telepon', $pemasok->nomor_telepon) }}" class="w-full border border-gray-200 rounded-3xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6]">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email', $pemasok->email) }}" class="w-full border border-gray-200 rounded-3xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Lengkap</label>
                        <textarea name="alamat" rows="3" class="w-full border border-gray-200 rounded-3xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6]">{{ old('alamat', $pemasok->alamat) }}</textarea>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="status_aktif" id="status_aktif" value="1" {{ $pemasok->status_aktif ? 'checked' : '' }} class="w-4 h-4 text-[#3B82F6] rounded border-gray-300">
                        <label for="status_aktif" class="text-sm font-medium text-gray-700">Pemasok Aktif</label>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex justify-end">
                    <button type="submit" class="px-6 py-2.5 bg-[#3B82F6] text-white text-sm font-semibold rounded-3xl hover:bg-blue-700 transition shadow-sm shadow-blue-200">
                        Update Pemasok
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection