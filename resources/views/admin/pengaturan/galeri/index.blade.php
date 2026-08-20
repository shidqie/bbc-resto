@extends('layouts.pos')

@section('title', 'Pengaturan Galeri')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800">
    <div class="w-full p-6 space-y-5">
        {{-- PAGE HEADER --}}
        <x-ui.page-header title="Pengaturan Galeri" subtitle="Kelola foto-foto galeri yang ditampilkan pada Landing Page." :breadcrumbs="['Pengaturan', 'Galeri']">
        </x-ui.page-header>

        <x-ui.alert />

        <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm shadow-gray-100/50 mb-6">
            <h2 class="text-base font-semibold text-gray-900 mb-4">Unggah Foto Baru</h2>
            <form action="{{ route('admin.pengaturan.galeri.store') }}" method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row gap-4 items-start sm:items-center">
                @csrf
                <div class="flex-1 w-full">
                    <input type="file" name="foto" required accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-soft file:text-primary hover:file:bg-primary/10 border border-gray-200 rounded-lg outline-none cursor-pointer">
                    @error('foto')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <x-ui.button type="submit" variant="primary" class="w-full sm:w-auto shrink-0 justify-center">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Simpan
                </x-ui.button>
            </form>
        </div>

        <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm shadow-gray-100/50">
            <h2 class="text-base font-semibold text-gray-900 mb-4">Daftar Foto Galeri</h2>
            
            @if($galeri->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                    @foreach($galeri as $item)
                        <div class="relative group rounded-xl overflow-hidden bg-gray-100 aspect-square border border-gray-200 shadow-sm">
                            <img src="{{ asset('storage/' . $item->foto) }}" alt="Galeri" class="w-full h-full object-cover">
                            
                            {{-- Delete Button: Always visible on mobile, visible on hover on desktop --}}
                            <div class="absolute top-2 right-2 opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity duration-200">
                                <form action="{{ route('admin.pengaturan.galeri.destroy', $item->id) }}" method="POST" data-confirm="Apakah Anda yakin ingin menghapus foto ini?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 bg-white/90 text-red-600 rounded-lg hover:bg-red-50 hover:text-red-700 transition-colors shadow-sm backdrop-blur-sm border border-red-100" title="Hapus Foto">
                                        <x-heroicon-o-trash class="w-4 h-4" />
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-10 bg-gray-50 rounded-xl border border-dashed border-gray-200">
                    <x-heroicon-o-photo class="w-12 h-12 text-gray-300 mx-auto mb-3" />
                    <p class="text-gray-500 text-sm">Belum ada foto galeri. Silakan unggah foto baru.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
