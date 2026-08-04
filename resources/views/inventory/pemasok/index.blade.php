{{-- Halaman: Daftar Pemasok --}}
@extends('layouts.pos')
@section('title', 'Kelola Pemasok')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800">
    <div class="w-full p-6 space-y-5">

        {{-- Page Header --}}
        <x-ui.page-header
            title="Kelola Pemasok"
            subtitle="Manajemen data pemasok bahan baku untuk kebutuhan pengadaan."
            :breadcrumbs="['Persediaan', 'Pemasok']">
            <x-slot:actions>
                <x-ui.button href="{{ route('pemasok.create') }}" variant="primary" icon="plus">
                    Tambah Pemasok
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.alert />

        {{-- Filter Bar --}}
        <div class="flex flex-col sm:flex-row gap-2 items-start sm:items-center mb-3">
            <form method="GET" action="{{ route('pemasok.index') }}" class="flex items-center gap-2 w-full sm:w-auto flex-wrap">
                <x-search-input name="search" value="{{ request('search') }}" placeholder="Cari nama atau kode pemasok..." />
                <button type="submit" class="text-sm font-medium bg-white border border-gray-200 text-gray-600 rounded-lg px-3 py-2 hover:bg-gray-50 transition-colors shrink-0">Cari</button>
                @if(request()->filled('search'))
                    <a href="{{ route('pemasok.index') }}" class="text-xs font-medium text-red-500 hover:text-red-700 px-2 py-2 rounded-lg hover:bg-red-50 transition-colors shrink-0">Reset</a>
                @endif
            </form>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-sm font-semibold text-gray-500 uppercase tracking-wide">
                            <th class="px-4 py-3 text-left">Kode</th>
                            <th class="px-4 py-3 text-left">Nama Pemasok</th>
                            <th class="px-4 py-3 text-left">Kontak & Email</th>
                            <th class="px-4 py-3 text-left">Telepon</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($pemasoks as $pemasok)
                        <tr class="hover:bg-gray-50/60 transition-colors group">
                            <td class="px-4 py-3">
                                <span class="font-mono text-xs font-semibold text-blue-600 bg-blue-50 px-2 py-1 rounded-lg">{{ $pemasok->kode_pemasok }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-semibold text-gray-900 leading-tight">{{ $pemasok->nama_pemasok }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-sm text-gray-700 font-medium">{{ $pemasok->nama_kontak ?? '-' }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $pemasok->email ?? '-' }}</p>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 font-medium">{{ $pemasok->nomor_telepon ?? '-' }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($pemasok->status_aktif)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-semibold bg-red-50 text-red-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('pemasok.edit', $pemasok->id) }}" title="Ubah" class="w-7 h-7 rounded-full flex items-center justify-center bg-amber-50 text-amber-600 hover:bg-amber-100 transition-colors">
                                        <x-heroicon-o-pencil-square class="w-3 h-3" />
                                    </a>
                                    <form action="{{ route('pemasok.destroy', $pemasok->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus pemasok ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus" class="w-7 h-7 rounded-full flex items-center justify-center bg-red-50 text-red-600 hover:bg-red-100 transition-colors">
                                            <x-heroicon-o-trash class="w-3 h-3" />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <x-empty-state icon="user-group" title="Belum ada pemasok" message="Tambah pemasok baru untuk memulai pengadaan." :colspan="6" />
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($pemasoks->hasPages())
            <div class="px-5 py-3 border-t border-gray-100">{{ $pemasoks->links() }}</div>
            @endif
        </div>

    </div>
</div>
@endsection
