{{-- Halaman: Daftar Pemasok --}}
@extends('layouts.pos')
@section('title', 'Kelola Pemasok')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800 pb-10">
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

        {{-- Table with integrated toolbar --}}
        <x-ui.data-table :paginator="$pemasoks">
            <x-slot:toolbar>
                <form action="{{ route('pemasok.index') }}" method="GET" class="flex flex-col xl:flex-row items-start xl:items-center justify-between gap-4 w-full">
                    <div class="w-full xl:max-w-sm shrink-0">
                        <x-search-input name="search" value="{{ request('search') }}" placeholder="Cari nama atau kode pemasok..." width="w-full" />
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        @if(request()->filled('search'))
                            <a href="{{ route('pemasok.index') }}" class="text-xs font-medium text-red-500 hover:text-red-700 px-2 py-2 rounded-lg hover:bg-red-50 transition-colors shrink-0">Reset</a>
                        @endif
                    </div>
                </form>
            </x-slot:toolbar>

            <x-ui.table class="min-w-[900px]">
                <x-ui.table.header>
                    <th class="px-4 py-3.5 text-left">Kode</th>
                    <th class="px-4 py-3.5 text-left">Nama Pemasok</th>
                    <th class="px-4 py-3.5 text-left">Kontak & Email</th>
                    <th class="px-4 py-3.5 text-left">Telepon</th>
                    <th class="px-4 py-3.5 text-center">Status</th>
                    <th class="px-4 py-3.5 text-center">Aksi</th>
                </x-ui.table.header>
                <tbody class="divide-y divide-gray-100">
                    @forelse($pemasoks as $pemasok)
                    <x-ui.table.row>
                        <td class="px-4 py-4 align-middle">
                            <span class="font-mono text-xs font-semibold text-blue-600 bg-blue-50 px-2 py-1 rounded-lg">{{ $pemasok->kode_pemasok }}</span>
                        </td>
                        <td class="px-4 py-4 align-middle">
                            <p class="font-semibold text-gray-900 leading-tight text-sm">{{ $pemasok->nama_pemasok }}</p>
                        </td>
                        <td class="px-4 py-4 align-middle">
                            <p class="text-sm text-gray-700 font-medium">{{ $pemasok->nama_kontak ?? '-' }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $pemasok->email ?? '-' }}</p>
                        </td>
                        <td class="px-4 py-4 align-middle text-sm text-gray-600 font-medium">{{ $pemasok->nomor_telepon ?? '-' }}</td>
                        <td class="px-4 py-4 align-middle text-center">
                            @php
                                $stColor = $pemasok->status_aktif ? 'success' : 'danger';
                            @endphp
                            <x-ui.badge :color="$stColor" size="sm" dot>{{ $pemasok->status_aktif ? 'Aktif' : 'Nonaktif' }}</x-ui.badge>
                        </td>
                        <td class="px-4 py-4 align-middle text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('pemasok.edit', $pemasok->id) }}" title="Ubah" class="w-7 h-7 rounded-full flex items-center justify-center bg-amber-50 text-amber-600 hover:bg-amber-100 transition-colors">
                                    <x-heroicon-o-pencil-square class="w-4 h-4" />
                                </a>
                                <form action="{{ route('pemasok.destroy', $pemasok->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus pemasok ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <x-ui.action-button type="submit" title="Hapus">
                                        <x-heroicon-o-trash class="w-4 h-4" />
                                    </x-ui.action-button>
                                </form>
                            </div>
                        </td>
                    </x-ui.table.row>
                    @empty
                    <x-empty-state icon="users" title="Belum ada pemasok" message="Tambah pemasok baru untuk memulai pengadaan." :colspan="6" />
                    @endforelse
                </tbody>
            </x-ui.table>
        </x-ui.data-table>

    </div>
</div>
@endsection
