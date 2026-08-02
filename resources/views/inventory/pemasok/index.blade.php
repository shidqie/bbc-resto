@extends('layouts.pos')
@section('content')
<div class="flex-1 overflow-auto bg-gray-50/50">
    <div class="w-full p-6 space-y-6">
        <x-ui.page-header title="Kelola Pemasok" :breadcrumbs="['Inventory', 'Pemasok']">
            <x-slot:actions>
                <a href="{{ route('pemasok.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-[#3B82F6] text-white text-sm font-semibold rounded-3xl hover:bg-blue-700 transition">
                    + Tambah Pemasok
                </a>
            </x-slot:actions>
        </x-ui.page-header>
        
        <x-ui.alert />

        <div class="bg-white rounded-[2.25rem] border border-gray-100 p-4 shadow-sm">
            <form method="GET" class="flex gap-3">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau kode pemasok..." class="flex-1 border border-gray-200 rounded-3xl px-4 py-2.5 text-sm text-gray-700 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6]">
                <button type="submit" class="px-4 py-2.5 bg-gray-100 rounded-3xl text-sm font-medium text-gray-600 hover:bg-gray-200 transition">Cari</button>
                <a href="{{ route('pemasok.index') }}" class="px-4 py-2.5 bg-gray-100 rounded-3xl text-sm font-medium text-gray-600 hover:bg-gray-200 transition">Reset</a>
            </form>
        </div>

        <div class="bg-white rounded-3xl border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-xs font-semibold text-gray-400 uppercase tracking-wide">
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
                            <td class="px-4 py-3"><span class="font-mono text-xs font-semibold text-blue-600 bg-blue-50 px-2 py-1 rounded-2xl">{{ $pemasok->kode_pemasok }}</span></td>
                            <td class="px-4 py-3 font-semibold text-gray-900">{{ $pemasok->nama_pemasok }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                <div>{{ $pemasok->nama_kontak ?? '-' }}</div>
                                <div class="text-xs text-gray-400">{{ $pemasok->email ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $pemasok->nomor_telepon ?? '-' }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($pemasok->status_aktif)
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-green-50 text-green-700">Aktif</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-red-50 text-red-700">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('pemasok.edit', $pemasok->id) }}" title="Ubah" class="w-7 h-7 rounded-full flex items-center justify-center bg-amber-50 text-amber-600 hover:bg-amber-100 transition-colors">
                                        <x-heroicon-o-pencil-square class="w-3 h-3" />
                                    </a>
                                    <form action="{{ route('pemasok.destroy', $pemasok->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pemasok ini?');">
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
                        <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400 text-sm">Belum ada data pemasok</td></tr>
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
