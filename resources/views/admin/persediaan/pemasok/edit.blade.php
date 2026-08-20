@extends('layouts.pos')
@section('content')
<div class="flex-1 overflow-auto bg-gray-50/50">
    <div class="w-full p-6 space-y-6">
        <x-ui.page-header title="Edit Data Pemasok" :breadcrumbs="['Inventory', 'Pemasok', 'Edit']">
            <x-slot:actions>
                <x-ui.button variant="secondary" href="{{ route('pemasok.index') }}">
                    &larr; Batal
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>
        
        <x-ui.alert />

        <div class="bg-white rounded-[2.25rem] border border-gray-100 shadow-sm overflow-hidden">
            <form action="{{ route('pemasok.update', $pemasok->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="p-6 space-y-5">
                    <div>
                        <x-ui.input name="kode_pemasok" label="Kode Pemasok" :value="$pemasok->kode_pemasok" disabled class="bg-gray-100" />
                    </div>
                    <div>
                        <x-ui.input name="nama_pemasok" label="Nama Pemasok / Perusahaan *" :value="old('nama_pemasok', $pemasok->nama_pemasok)" required />
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <x-ui.input name="nama_kontak" label="Nama Kontak Person" :value="old('nama_kontak', $pemasok->nama_kontak)" />
                        </div>
                        <div>
                            <x-input-wa name="nomor_telepon" label="Nomor WhatsApp" :value="old('nomor_telepon', $pemasok->nomor_telepon)" placeholder="08xxxxxxxxxx" />
                        </div>
                    </div>
                    <div>
                        <x-ui.input type="email" name="email" label="Email" :value="old('email', $pemasok->email)" />
                    </div>
                    <div>
                        <x-ui.textarea name="alamat" label="Alamat Lengkap" rows="3" :value="old('alamat', $pemasok->alamat)" />
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="status_aktif" id="status_aktif" value="1" {{ $pemasok->status_aktif ? 'checked' : '' }} class="w-4 h-4 text-primary rounded border-gray-300">
                        <label for="status_aktif" class="text-sm font-medium text-gray-700">Pemasok Aktif</label>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex justify-end">
                    <x-ui.button type="submit" variant="primary">
                        Update Pemasok
                    </x-ui.button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection