@extends('layouts.pos')

@section('title') Daftar Menu {{ $jenis == 'nasi_box' ? 'Nasi Box' : ($jenis == 'catering' ? 'Catering' : 'Layanan Luar') }}
@endsection

@section('content')
<div class="p-4 md:p-6 lg:p-8 max-w-[1200px] mx-auto">
<h1 class="text-2xl font-bold mb-6">Daftar Menu {{ $jenis == 'nasi_box' ? 'Nasi Box' : ($jenis == 'catering' ? 'Catering' : 'Layanan Luar') }}</h1>
<div class="mb-6 flex justify-between items-center">
    <div>
        <p class="text-gray-500">Kelola daftar paket dan menu yang tampil di website utama.</p>
    </div>
    <a href="{{ route('paket-catering.create') }}" class="bg-primary text-white px-4 py-2 rounded-lg font-bold shadow-sm hover:bg-primary/90 flex items-center gap-2">
        <x-heroicon-o-plus class="w-5 h-5" />
        Tambah Paket Baru
    </a>
</div>

<div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm whitespace-nowrap">
            <thead class="bg-gray-50 text-gray-600 font-medium border-b border-gray-200">
                <tr>
                    <th class="px-6 py-4">Nama Paket</th>
                    <th class="px-6 py-4">Jenis</th>
                    <th class="px-6 py-4">Harga / Porsi</th>
                    <th class="px-6 py-4 text-center">Komponen</th>
                    <th class="px-6 py-4 text-center">Status Website</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($pakets as $paket)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4">
                        <div class="font-bold text-gray-900">{{ $paket->nama_paket }}</div>
                        <div class="text-xs text-gray-500 truncate max-w-xs">{{ $paket->deskripsi }}</div>
                    </td>
                    <td class="px-6 py-4">
                        @if($paket->jenis_paket == 'nasi_box')
                            <span class="bg-orange-100 text-orange-700 px-2 py-1 rounded-md text-xs font-bold">Nasi Box</span>
                        @else
                            <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded-md text-xs font-bold">Catering</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 font-medium text-gray-700">
                        Rp {{ number_format($paket->harga, 0, ',', '.') }}
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded-md text-xs font-bold">{{ $paket->komponens_count }} Komponen</span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <form action="{{ route('paket-catering.toggle', $paket->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="relative inline-flex items-center h-6 rounded-full w-11 focus:outline-none transition-colors ease-in-out duration-200 {{ $paket->is_active ? 'bg-green-500' : 'bg-gray-300' }}">
                                <span class="inline-block w-4 h-4 transform bg-white rounded-full transition ease-in-out duration-200 {{ $paket->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                            </button>
                        </form>
                    </td>
                    <td class="px-6 py-4 text-right space-x-2">
                        <a href="{{ route('paket-catering.show', $paket->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-blue-600 hover:bg-blue-50 border border-transparent hover:border-blue-100 transition-colors">
                            <x-heroicon-o-eye class="w-4 h-4" />
                        </a>
                        <a href="{{ route('paket-catering.edit', $paket->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-amber-600 hover:bg-amber-50 border border-transparent hover:border-amber-100 transition-colors">
                            <x-heroicon-o-pencil-square class="w-4 h-4" />
                        </a>
                        <form action="{{ route('paket-catering.destroy', $paket->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus paket ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-red-600 hover:bg-red-50 border border-transparent hover:border-red-100 transition-colors">
                                <x-heroicon-o-trash class="w-4 h-4" />
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                        <div class="flex flex-col items-center justify-center">
                            <x-heroicon-o-cube class="w-12 h-12 text-gray-300 mb-3" />
                            <p class="font-medium text-gray-900 mb-1">Belum ada paket</p>
                            <p class="text-sm">Silakan tambah paket {{ $jenis == 'nasi_box' ? 'Nasi Box' : 'Catering' }} baru.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
</div>
@endsection
