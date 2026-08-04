{{-- 
    Halaman: Daftar Bahan Baku
    UI: disamakan dengan Kelola Menu
--}}
@extends('layouts.pos')

@section('title', 'Daftar Bahan Baku')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800">
    <div class="w-full p-6 space-y-5">

        {{-- Page Header --}}
        <x-ui.page-header
            title="Daftar Bahan Baku"
            subtitle="Kelola persediaan bahan baku. Stok otomatis terpotong dari resep & bertambah dari pengadaan."
            :breadcrumbs="['Persediaan', 'Bahan Baku']">
            <x-slot:actions>
                <x-ui.button href="{{ route('bahan-baku.create') }}" variant="primary" icon="plus">
                    Tambah Bahan Baku
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        {{-- Stat Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <x-ui.stat-card label="Total Bahan Baku" :value="$totalBahan" icon="cube" color="blue" />
            <x-ui.stat-card label="Stok Aman" :value="$stokAman" icon="check-circle" color="green" />
            <x-ui.stat-card label="Stok Menipis" :value="$stokMenipis" icon="exclamation-triangle" color="orange" />
            <x-ui.stat-card label="Stok Habis" :value="$stokHabis" icon="x-circle" color="red" />
        </div>

        <x-ui.alert />

        {{-- Filter Bar --}}
        <div class="flex flex-col sm:flex-row gap-2 items-start sm:items-center justify-between mb-3 shrink-0">
            <form action="{{ route('bahan-baku.index') }}" method="GET" class="flex items-center gap-2 w-full sm:w-auto flex-wrap">
                <x-search-input name="search" value="{{ request('search') }}" placeholder="Cari kode atau nama bahan..." />
                <select name="kategori" class="text-sm border border-gray-200 rounded-lg bg-white px-3 py-2 outline-none focus:ring-1 focus:ring-gray-400 transition-all shrink-0" onchange="this.form.submit()">
                    <option value="">Semua Kategori</option>
                    @foreach($kategoris as $kat)
                        <option value="{{ $kat->id }}" {{ request('kategori') == $kat->id ? 'selected' : '' }}>{{ $kat->nama_kategori }}</option>
                    @endforeach
                </select>
                <select name="status_stok" class="text-sm border border-gray-200 rounded-lg bg-white px-3 py-2 outline-none focus:ring-1 focus:ring-gray-400 transition-all shrink-0" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="aman" {{ request('status_stok') == 'aman' ? 'selected' : '' }}>Aman</option>
                    <option value="menipis" {{ request('status_stok') == 'menipis' ? 'selected' : '' }}>Menipis</option>
                    <option value="habis" {{ request('status_stok') == 'habis' ? 'selected' : '' }}>Habis</option>
                </select>
                <button type="submit" class="text-sm font-medium bg-white border border-gray-200 text-gray-600 rounded-lg px-3 py-2 hover:bg-gray-50 transition-colors shrink-0">Cari</button>
                @if(request()->hasAny(['search', 'kategori', 'status_stok']))
                    <a href="{{ route('bahan-baku.index') }}" class="text-xs font-medium text-red-500 hover:text-red-700 px-2 py-2 rounded-lg hover:bg-red-50 transition-colors shrink-0">Reset</a>
                @endif
            </form>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-sm font-semibold text-gray-500 uppercase tracking-wide">
                        <th class="px-4 py-3 text-left w-12">No.</th>
                        <th class="px-4 py-3 text-left w-28">Kode</th>
                        <th class="px-4 py-3 text-left">Nama Bahan</th>
                        <th class="px-4 py-3 text-left">Kategori</th>
                        <th class="px-4 py-3 text-right">Stok Saat Ini</th>
                        <th class="px-4 py-3 text-right">Stok Min.</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($bahanBakus as $i => $item)
                    <tr class="hover:bg-gray-50/60 transition-colors group {{ !$item->status_aktif ? 'opacity-60' : '' }}">
                        <td class="px-4 py-3 text-sm text-gray-500 font-medium align-middle">{{ $bahanBakus->firstItem() + $i }}</td>
                        <td class="px-4 py-3 align-middle">
                            <span class="inline-block text-xs font-mono font-semibold text-gray-700 bg-gray-100 rounded-xl px-2 py-1">{{ $item->kode_bahan }}</span>
                        </td>
                        <td class="px-4 py-3 align-middle">
                            <p class="font-semibold text-gray-900 leading-tight">{{ $item->nama_bahan }}</p>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600 font-medium">{{ $item->kategori_bahan_baku->nama_kategori ?? '-' }}</td>
                        <td class="px-4 py-3 text-right">
                            <span class="font-semibold text-gray-900">{{ number_format($item->total_stok, 2, ',', '.') }}</span>
                            <span class="text-xs text-gray-400">{{ $item->satuan->nama_satuan ?? '' }}</span>
                        </td>
                        <td class="px-4 py-3 text-right text-sm text-gray-500 font-medium">
                            {{ number_format($item->stok_minimal, 2, ',', '.') }} {{ $item->satuan->nama_satuan ?? '' }}
                        </td>
                        <td class="px-4 py-3">
                            @if($item->total_stok <= 0)
                                <span class="inline-block text-xs font-semibold px-2 py-0.5 rounded-xl bg-red-50 text-red-700">Habis</span>
                            @elseif($item->total_stok <= $item->stok_minimal)
                                <span class="inline-block text-xs font-semibold px-2 py-0.5 rounded-xl bg-amber-50 text-amber-700">Menipis</span>
                            @else
                                <span class="inline-block text-xs font-semibold px-2 py-0.5 rounded-xl bg-emerald-50 text-emerald-700">Aman</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('bahan-baku.show', $item->id) }}" title="Detail" class="w-7 h-7 rounded-full flex items-center justify-center bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors">
                                    <x-heroicon-o-eye class="w-3 h-3" />
                                </a>
                                <a href="{{ route('bahan-baku.edit', $item->id) }}" title="Ubah" class="w-7 h-7 rounded-full flex items-center justify-center bg-amber-50 text-amber-600 hover:bg-amber-100 transition-colors">
                                    <x-heroicon-o-pencil-square class="w-3 h-3" />
                                </a>
                                <form id="delete-bahan-{{ $item->id }}" action="{{ route('bahan-baku.destroy', $item->id) }}" method="POST" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="button" title="Hapus" onclick="window.confirmDialog({ title: 'Hapus Bahan Baku', name: '{{ addslashes($item->nama_bahan) }}', message: 'Data yang dihapus tidak dapat dikembalikan.', formId: 'delete-bahan-{{ $item->id }}', confirmText: 'Hapus', cancelText: 'Batal' })" class="w-7 h-7 rounded-full flex items-center justify-center bg-red-50 text-red-600 hover:bg-red-100 transition-colors">
                                        <x-heroicon-o-trash class="w-3 h-3" />
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-14 text-center text-gray-400">
                            <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            <p class="text-sm font-medium">Belum ada data bahan baku.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4 shrink-0">{{ $bahanBakus->links() }}</div>

    </div>
</div>
@endsection
