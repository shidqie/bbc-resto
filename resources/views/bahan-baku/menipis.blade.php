{{-- 
    Halaman: Stok Menipis
    UI: disamakan dengan Kelola Menu
--}}
@extends('layouts.pos')

@section('title', 'Stok Menipis')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800 font-sans">
    <div class="w-full px-4 md:px-6 py-6 space-y-5">

        {{-- PAGE HEADER --}}
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-lg font-bold text-gray-900">Stok Menipis</h1>
                <p class="text-xs text-gray-500 mt-0.5">Daftar bahan baku yang perlu segera di-restock karena sudah mencapai atau melewati batas minimum.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('pengadaan.create') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-gray-900 rounded-lg px-3 py-2 hover:bg-gray-800 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Buat Pengadaan
                </a>
                <a href="{{ route('bahan-baku.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg px-3 py-2 hover:bg-gray-50 transition-colors">
                    Semua Bahan Baku
                </a>
            </div>
        </div>

        {{-- Stat Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div class="bg-white rounded-xl border border-amber-200 px-4 py-3">
                <p class="text-xs font-medium text-gray-500">Stok Menipis (Warning)</p>
                <p class="text-xl font-bold text-amber-600 mt-1">{{ $stats['total_menipis'] }}</p>
            </div>
            <div class="bg-white rounded-xl border border-red-200 px-4 py-3">
                <p class="text-xs font-medium text-gray-500">Stok Habis (Kritis)</p>
                <p class="text-xl font-bold text-red-600 mt-1">{{ $stats['total_habis'] }}</p>
            </div>
        </div>

        {{-- Filter Bar --}}
        <div class="flex flex-col sm:flex-row gap-2 items-start sm:items-center justify-between mb-3 shrink-0">
            <form action="{{ route('stok-menipis.index') }}" method="GET" class="flex items-center gap-2 w-full sm:w-auto flex-wrap">
                <div class="relative flex-1 sm:flex-none sm:w-56">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari bahan baku..." class="w-full pl-8 pr-3 py-2 text-xs border border-gray-200 rounded-lg outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-all bg-white">
                </div>
                <select name="kategori" class="text-xs border border-gray-200 rounded-lg bg-white px-3 py-2 outline-none focus:ring-1 focus:ring-gray-400 transition-all" onchange="this.form.submit()">
                    <option value="">Semua Kategori</option>
                    @foreach($kategoris as $kat)
                        <option value="{{ $kat->id }}" {{ request('kategori') == $kat->id ? 'selected' : '' }}>{{ $kat->nama_kategori }}</option>
                    @endforeach
                </select>
                <button type="submit" class="text-xs font-medium bg-white border border-gray-200 text-gray-600 rounded-lg px-3 py-2 hover:bg-gray-50 transition-colors shrink-0">Cari</button>
                @if(request()->hasAny(['search', 'kategori']))
                    <a href="{{ route('stok-menipis.index') }}" class="text-xs font-medium text-red-500 hover:text-red-700 px-2 py-2 rounded-lg hover:bg-red-50 transition-colors shrink-0">Reset</a>
                @endif
            </form>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-xs font-semibold text-gray-400 uppercase tracking-wide">
                        <th class="px-4 py-3 text-left w-12">No.</th>
                        <th class="px-4 py-3 text-left">Bahan Baku</th>
                        <th class="px-4 py-3 text-left">Kategori</th>
                        <th class="px-4 py-3 text-right">Sisa Stok</th>
                        <th class="px-4 py-3 text-right">Batas Min.</th>
                        <th class="px-4 py-3 text-left">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($bahanBakus as $i => $bahan)
                    @php $isHabis = $bahan->stok <= 0; @endphp
                    <tr class="hover:bg-gray-50/60 transition-colors">
                        <td class="px-4 py-3 text-xs text-gray-500 font-medium align-middle">{{ $bahanBakus->firstItem() + $i }}</td>
                        <td class="px-4 py-3">
                            <p class="font-semibold text-gray-900 leading-tight">{{ $bahan->nama_bahan }}</p>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-600 font-medium">{{ $bahan->kategoriBahan->nama_kategori ?? '-' }}</td>
                        <td class="px-4 py-3 text-right">
                            <span class="font-bold text-lg {{ $isHabis ? 'text-red-600' : 'text-amber-600' }}">{{ (float)$bahan->stok }}</span>
                            <span class="text-xs text-gray-400"> {{ $bahan->satuan->nama_satuan ?? '' }}</span>
                        </td>
                        <td class="px-4 py-3 text-right text-xs text-gray-500 font-medium">
                            {{ (float)$bahan->stok_minimum }} {{ $bahan->satuan->nama_satuan ?? '' }}
                        </td>
                        <td class="px-4 py-3">
                            @if($isHabis)
                                <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-md bg-red-50 text-red-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>Stok Habis
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-md bg-amber-50 text-amber-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>Menipis
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-14 text-center text-gray-400">
                            <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            <p class="text-sm font-medium">Semua stok bahan baku terpantau aman.</p>
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
