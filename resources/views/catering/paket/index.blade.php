{{-- 
    Halaman: Daftar Paket Catering / Nasi Box (Minimalist Edition)
--}}
@extends('layouts.pos')

@section('title') Daftar Paket {{ $jenis == 'nasi_box' ? 'Nasi Box' : ($jenis == 'catering' ? 'Catering' : 'Paket Menu') }}
@endsection

@section('content')
<div class="flex-1 overflow-auto bg-gray-50/50 text-gray-800 font-sans">
    <div class="p-4 md:p-6 lg:p-8 max-w-[1250px] mx-auto space-y-6">
        
        {{-- Header --}}
        <x-ui.page-header 
            title="Daftar Paket {{ $jenis == 'nasi_box' ? 'Nasi Box' : ($jenis == 'catering' ? 'Catering' : 'Paket Menu') }}" 
            subtitle="Kelola susunan paket, komponen resep, dan status tampilan di website"
            :breadcrumbs="['Paket Menu', $jenis == 'nasi_box' ? 'Nasi Box' : 'Catering']">
            <x-slot:actions>
                <x-ui.button href="{{ route('paket-catering.create', ['jenis' => $jenis]) }}" icon="fa-plus">Tambah Paket Baru</x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.alert />

        {{-- Tabel Data Paket --}}
        <div class="bg-white rounded-2xl border border-gray-200/80 shadow-2xs overflow-hidden">
            <div class="p-4 border-b border-gray-100 bg-gray-50/50 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Paket:</span>
                    <span class="text-xs font-black text-[#0F2E23] bg-emerald-50 px-2.5 py-0.5 rounded-lg border border-emerald-200/60">{{ count($pakets) }} Paket</span>
                </div>

                {{-- Filter Jenis Paket Tabs --}}
                <div class="flex items-center gap-1 bg-gray-100 p-1 rounded-xl">
                    <a href="{{ route('paket-catering.index', ['jenis' => 'catering']) }}" 
                       class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ $jenis === 'catering' ? 'bg-[#0F2E23] text-white shadow-2xs' : 'text-gray-600 hover:text-gray-900' }}">
                        Catering
                    </a>
                    <a href="{{ route('paket-catering.index', ['jenis' => 'nasi_box']) }}" 
                       class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ $jenis === 'nasi_box' ? 'bg-[#0F2E23] text-white shadow-2xs' : 'text-gray-600 hover:text-gray-900' }}">
                        Nasi Box
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs whitespace-nowrap">
                    <thead class="bg-gray-50 text-gray-500 uppercase tracking-wider border-b border-gray-100 font-bold">
                        <tr>
                            <th class="px-5 py-4">Nama Paket</th>
                            <th class="px-5 py-4">Jenis</th>
                            <th class="px-5 py-4">Harga / Porsi</th>
                            <th class="px-5 py-4 text-center">Komponen</th>
                            <th class="px-5 py-4 text-center">Status Website</th>
                            <th class="px-5 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($pakets as $paket)
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="px-5 py-4">
                                <div class="font-extrabold text-gray-900 text-sm">{{ $paket->nama_paket }}</div>
                                <div class="text-xs text-gray-500 font-medium truncate max-w-xs mt-0.5">{{ $paket->deskripsi ?: '-' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                @if($paket->jenis_paket == 'nasi_box')
                                    <span class="bg-purple-100 text-purple-900 border border-purple-200 px-3 py-1 rounded-full text-[11px] font-bold">Nasi Box</span>
                                @else
                                    <span class="bg-blue-100 text-blue-900 border border-blue-200 px-3 py-1 rounded-full text-[11px] font-bold">Catering</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 font-black text-[#0F2E23] text-sm">
                                Rp {{ number_format($paket->harga, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-xl text-xs font-bold border border-gray-200/80">{{ $paket->komponens_count }} Komponen</span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <form action="{{ route('paket-catering.toggle', $paket->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="relative inline-flex items-center h-6 rounded-full w-11 focus:outline-none transition-colors ease-in-out duration-200 {{ $paket->is_active ? 'bg-emerald-600' : 'bg-gray-300' }}">
                                        <span class="inline-block w-4 h-4 transform bg-white rounded-full transition ease-in-out duration-200 {{ $paket->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                    </button>
                                </form>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex justify-end items-center gap-1.5">
                                    <a href="{{ route('paket-catering.show', $paket->id) }}" class="p-1.5 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors" title="Detail">
                                        <x-heroicon-o-eye class="w-4 h-4" />
                                    </a>
                                    <a href="{{ route('paket-catering.edit', $paket->id) }}" class="p-1.5 text-blue-600 hover:text-blue-900 hover:bg-blue-50 rounded-lg transition-colors" title="Edit">
                                        <x-heroicon-o-pencil-square class="w-4 h-4" />
                                    </a>
                                    <form action="{{ route('paket-catering.destroy', $paket->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus paket ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-red-600 hover:text-red-900 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                                            <x-heroicon-o-trash class="w-4 h-4" />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center">
                                    <x-heroicon-o-cube class="w-10 h-10 text-gray-300 mb-2" />
                                    <p class="font-bold text-gray-800 text-sm mb-0.5">Belum ada paket terdaftar</p>
                                    <p class="text-xs text-gray-400">Silakan tambah paket {{ $jenis == 'nasi_box' ? 'Nasi Box' : 'Catering' }} baru.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection
