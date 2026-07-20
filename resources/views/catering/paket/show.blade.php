@extends('layouts.pos')

@section('title') Detail Paket: {{ $paketCatering->nama_paket }}
@endsection

@section('content')
<div class="p-4 md:p-6 lg:p-8 max-w-[1200px] mx-auto">
<h1 class="text-2xl font-bold mb-6">Detail Paket: {{ $paketCatering->nama_paket }}</h1>
<div class="mb-6 flex gap-3">
    <a href="{{ route('paket-catering.index', ['jenis' => $paketCatering->jenis_paket]) }}" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg font-bold shadow-sm hover:bg-gray-200 flex items-center gap-2">
        <x-heroicon-o-arrow-left class="w-5 h-5" />
        Kembali
    </a>
    <a href="{{ route('paket-catering.edit', $paketCatering->id) }}" class="bg-amber-100 text-amber-700 px-4 py-2 rounded-lg font-bold shadow-sm hover:bg-amber-200 flex items-center gap-2">
        <x-heroicon-o-pencil-square class="w-5 h-5" />
        Edit Paket
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 relative">
            <div class="absolute top-4 right-4">
                @if($paketCatering->jenis_paket == 'nasi_box')
                    <span class="bg-orange-100 text-orange-700 px-3 py-1 rounded-full text-xs font-bold">Nasi Box</span>
                @else
                    <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-bold">Catering</span>
                @endif
            </div>

            <div class="w-16 h-16 bg-primary/10 rounded-2xl flex items-center justify-center mb-4">
                <x-heroicon-o-cake class="w-8 h-8 text-primary" />
            </div>
            
            <h2 class="text-xl font-bold text-gray-900 mb-1">{{ $paketCatering->nama_paket }}</h2>
            <p class="text-sm text-gray-500 mb-4">{{ $paketCatering->deskripsi ?: 'Tidak ada deskripsi' }}</p>
            
            <div class="border-t border-gray-100 pt-4">
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Harga per Porsi</div>
                <div class="text-2xl font-bold text-gray-900">Rp {{ number_format($paketCatering->harga, 0, ',', '.') }}</div>
            </div>
            
            <div class="border-t border-gray-100 pt-4 mt-4">
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Status Website</div>
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full {{ $paketCatering->is_active ? 'bg-green-500' : 'bg-gray-300' }}"></div>
                    <span class="text-sm font-medium text-gray-700">{{ $paketCatering->is_active ? 'Aktif (Tampil)' : 'Non-aktif (Disembunyikan)' }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <h3 class="font-bold text-gray-900">Struktur Menu ({{ $paketCatering->komponens->count() }} Komponen)</h3>
                <p class="text-sm text-gray-500">Susunan menu yang akan didapatkan atau dipilih oleh pemesan.</p>
            </div>
            
            <div class="p-6">
                <div class="space-y-6">
                    @forelse($paketCatering->komponens as $komponen)
                        <div class="relative pl-6 border-l-2 {{ $komponen->tipe == 'fixed' ? 'border-primary' : 'border-amber-400' }}">
                            <div class="absolute w-3 h-3 rounded-full -left-[7px] top-1.5 {{ $komponen->tipe == 'fixed' ? 'bg-primary' : 'bg-amber-400' }}"></div>
                            
                            <div class="flex items-start justify-between mb-2">
                                <div>
                                    <h4 class="font-bold text-gray-900">{{ $komponen->nama_komponen }}</h4>
                                    <p class="text-xs text-gray-500">Urutan: {{ $komponen->urutan }}</p>
                                </div>
                                <div>
                                    @if($komponen->tipe == 'fixed')
                                        <span class="bg-blue-50 text-blue-600 border border-blue-200 px-2 py-1 rounded text-xs font-bold">Pasti Dapat</span>
                                    @else
                                        <span class="bg-amber-50 text-amber-600 border border-amber-200 px-2 py-1 rounded text-xs font-bold">Pilih Salah Satu</span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach($komponen->opsi as $opsi)
                                    <div class="flex items-center gap-2 bg-gray-50 border border-gray-200 px-3 py-1.5 rounded-lg">
                                        <div class="w-6 h-6 rounded-md overflow-hidden bg-gray-200 shrink-0">
                                            @if($opsi->menu && $opsi->menu->gambar)
                                                <img src="{{ Storage::url($opsi->menu->gambar) }}" class="w-full h-full object-cover">
                                            @endif
                                        </div>
                                        <span class="text-sm font-medium text-gray-700">{{ $opsi->menu ? $opsi->menu->nama : 'Menu tidak ditemukan' }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6 text-gray-500 text-sm">
                            Belum ada komponen menu yang ditambahkan ke paket ini.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@endsection
