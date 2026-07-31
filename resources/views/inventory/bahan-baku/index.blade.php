{{-- 
    Halaman: Daftar Bahan Baku
    UI: disamakan dengan Kelola Menu
--}}
@extends('layouts.pos')

@section('title', 'Daftar Bahan Baku')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800">
    <div class="w-full p-6 space-y-5">

        {{-- PAGE HEADER --}}
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-black text-gray-900 tracking-tight">Daftar Bahan Baku</h1>
                <p class="text-sm text-gray-500 font-medium mt-1">Kelola persediaan bahan baku (Stok otomatis terpotong dari resep, bertambah dari pengadaan)</p>
            </div>
            <a href="{{ route('bahan-baku.create') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-gray-900 rounded-lg px-3 py-2 hover:bg-gray-800 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Bahan Baku
            </a>
        </div>

        {{-- Stat Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="bg-white rounded-xl border border-gray-200 px-4 py-3">
                <p class="text-xs font-medium text-gray-500">Total Bahan Baku</p>
                <p class="text-xl font-bold text-gray-900 mt-1">{{ $totalBahan }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 px-4 py-3">
                <p class="text-xs font-medium text-gray-500">Stok Aman</p>
                <p class="text-xl font-bold text-emerald-600 mt-1">{{ $stokAman }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 px-4 py-3">
                <p class="text-xs font-medium text-gray-500">Stok Menipis</p>
                <p class="text-xl font-bold text-amber-600 mt-1">{{ $stokMenipis }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 px-4 py-3">
                <p class="text-xs font-medium text-gray-500">Stok Habis</p>
                <p class="text-xl font-bold text-red-600 mt-1">{{ $stokHabis }}</p>
            </div>
        </div>

        <x-ui.alert />

        {{-- Filter Bar --}}
        <div class="flex flex-col sm:flex-row gap-2 items-start sm:items-center justify-between mb-3 shrink-0">
            <form action="{{ route('bahan-baku.index') }}" method="GET" class="flex items-center gap-2 w-full sm:w-auto flex-wrap">
                <div class="relative flex-1 sm:flex-none sm:w-56">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kode atau nama bahan…" class="w-full pl-8 pr-3 py-2 text-xs border border-gray-200 rounded-lg outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-all bg-white">
                </div>
                <select name="kategori" class="text-xs border border-gray-200 rounded-lg bg-white px-3 py-2 outline-none focus:ring-1 focus:ring-gray-400 transition-all" onchange="this.form.submit()">
                    <option value="">Semua Kategori</option>
                    @foreach($kategoris as $kat)
                        <option value="{{ $kat->id }}" {{ request('kategori') == $kat->id ? 'selected' : '' }}>{{ $kat->nama_kategori }}</option>
                    @endforeach
                </select>
                <select name="status_stok" class="text-xs border border-gray-200 rounded-lg bg-white px-3 py-2 outline-none focus:ring-1 focus:ring-gray-400 transition-all" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="aman" {{ request('status_stok') == 'aman' ? 'selected' : '' }}>Aman</option>
                    <option value="menipis" {{ request('status_stok') == 'menipis' ? 'selected' : '' }}>Menipis</option>
                    <option value="habis" {{ request('status_stok') == 'habis' ? 'selected' : '' }}>Habis</option>
                </select>
                <button type="submit" class="text-xs font-medium bg-white border border-gray-200 text-gray-600 rounded-lg px-3 py-2 hover:bg-gray-50 transition-colors shrink-0">Cari</button>
                @if(request()->hasAny(['search', 'kategori', 'status_stok']))
                    <a href="{{ route('bahan-baku.index') }}" class="text-xs font-medium text-red-500 hover:text-red-700 px-2 py-2 rounded-lg hover:bg-red-50 transition-colors shrink-0">Reset</a>
                @endif
            </form>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-xs font-semibold text-gray-400 uppercase tracking-wide">
                        <th class="px-4 py-3 text-left w-12">No.</th>
                        <th class="px-4 py-3 text-left">Kode &amp; Nama Bahan</th>
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
                        <td class="px-4 py-3 text-xs text-gray-500 font-medium align-middle">{{ $bahanBakus->firstItem() + $i }}</td>
                        <td class="px-4 py-3">
                            <p class="font-semibold text-gray-900 leading-tight">{{ $item->nama_bahan_baku }}</p>
                            <p class="text-xs text-gray-400 font-mono mt-0.5">{{ $item->kode_bahan_baku }}</p>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-600 font-medium">{{ $item->kategori_bahan_baku->nama_kategori ?? '-' }}</td>
                        <td class="px-4 py-3 text-right">
                            <span class="font-semibold text-gray-900">{{ number_format($item->stok, 2, ',', '.') }}</span>
                            <span class="text-xs text-gray-400">{{ $item->satuan->nama_satuan ?? '' }}</span>
                        </td>
                        <td class="px-4 py-3 text-right text-xs text-gray-500 font-medium">
                            {{ number_format($item->stok_minimal, 2, ',', '.') }} {{ $item->satuan->nama_satuan ?? '' }}
                        </td>
                        <td class="px-4 py-3">
                            @if($item->stok <= 0)
                                <span class="inline-block text-xs font-semibold px-2 py-0.5 rounded-md bg-red-50 text-red-700">Habis</span>
                            @elseif($item->stok <= $item->stok_minimal)
                                <span class="inline-block text-xs font-semibold px-2 py-0.5 rounded-md bg-amber-50 text-amber-700">Menipis</span>
                            @else
                                <span class="inline-block text-xs font-semibold px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700">Aman</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('bahan-baku.show', $item->id) }}" title="Detail" class="w-7 h-7 rounded-lg flex items-center justify-center bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors">
                                    <x-heroicon-o-eye class="w-3 h-3" />
                                </a>
                                <a href="{{ route('bahan-baku.edit', $item->id) }}" title="Ubah" class="w-7 h-7 rounded-lg flex items-center justify-center bg-amber-50 text-amber-600 hover:bg-amber-100 transition-colors">
                                    <x-heroicon-o-pencil-square class="w-3 h-3" />
                                </a>
                                <form action="{{ route('bahan-baku.destroy', $item->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus bahan baku {{ addslashes($item->nama_bahan_baku) }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" title="Hapus" class="w-7 h-7 rounded-lg flex items-center justify-center bg-red-50 text-red-600 hover:bg-red-100 transition-colors">
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
