{{-- Halaman: Stok Operasional --}}
@extends('layouts.pos')
@section('title', 'Stok Operasional')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800">
    <div class="w-full p-6 space-y-5">

        {{-- PAGE HEADER --}}
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-black text-gray-900 tracking-tight">Stok Operasional</h1>
                <p class="text-sm text-gray-500 font-medium mt-1">Monitor stok bahan baku untuk kebutuhan harian (dine-in, restoran, nasi box).</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('pengadaan.create') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-gray-900 rounded-2xl px-3 py-2 hover:bg-gray-800 transition-colors">
                    <x-heroicon-o-plus class="w-3 h-3" />
                    Buat Pengadaan
                </a>
                <a href="{{ route('bahan-baku.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-2xl px-3 py-2 hover:bg-gray-50 transition-colors">
                    Data Bahan Baku
                </a>
            </div>
        </div>

        {{-- Stat Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div class="bg-white rounded-3xl border border-gray-200 px-4 py-3">
                <p class="text-xs font-medium text-gray-500">Total Bahan</p>
                <p class="text-xl font-bold text-gray-900 mt-1">{{ $stats['total_bahan'] }}</p>
            </div>
            <div class="bg-white rounded-3xl border border-emerald-200 px-4 py-3">
                <p class="text-xs font-medium text-gray-500">Stok Aman</p>
                <p class="text-xl font-bold text-emerald-600 mt-1">{{ $stats['total_aman'] }}</p>
            </div>
            <div class="bg-white rounded-3xl border border-amber-200 px-4 py-3">
                <p class="text-xs font-medium text-gray-500">Stok Menipis</p>
                <p class="text-xl font-bold text-amber-600 mt-1">{{ $stats['total_menipis'] }}</p>
            </div>
            <div class="bg-white rounded-3xl border border-red-200 px-4 py-3">
                <p class="text-xs font-medium text-gray-500">Stok Habis</p>
                <p class="text-xl font-bold text-red-600 mt-1">{{ $stats['total_habis'] }}</p>
            </div>
        </div>

        {{-- Tabs --}}
        <x-ui.tab-list>
            <x-ui.tab href="{{ route('stok-operasional.index') }}" :active="request()->routeIs('stok-operasional.*')">
                <x-heroicon-o-building-storefront class="w-3 h-3 shrink-0 inline-block mr-1.5" />
                Dine In & Nasi Box
            </x-ui.tab>
            <x-ui.tab href="{{ route('stok-catering.index') }}" :active="request()->routeIs('stok-catering.*')">
                <x-heroicon-o-truck class="w-3 h-3 shrink-0 inline-block mr-1.5" />
                Catering
            </x-ui.tab>
        </x-ui.tab-list>

        {{-- Filter Bar --}}
        <div class="flex flex-col sm:flex-row gap-2 items-start sm:items-center justify-between mb-3 shrink-0">
            <form action="{{ route('stok-operasional.index') }}" method="GET" class="flex items-center gap-2 w-full sm:w-auto flex-wrap">
                <div class="relative flex-1 sm:flex-none sm:w-56">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari bahan baku..." class="w-full pl-8 pr-3 py-2 text-xs border border-gray-200 rounded-2xl outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-all bg-white">
                </div>
                <select name="kategori" class="text-xs border border-gray-200 rounded-2xl bg-white px-3 py-2 outline-none focus:ring-1 focus:ring-gray-400 transition-all" onchange="this.form.submit()">
                    <option value="">Semua Kategori</option>
                    @foreach($kategoris as $kat)
                        <option value="{{ $kat->id }}" {{ request('kategori') == $kat->id ? 'selected' : '' }}>{{ $kat->nama_kategori }}</option>
                    @endforeach
                </select>
                <select name="status" class="text-xs border border-gray-200 rounded-2xl bg-white px-3 py-2 outline-none focus:ring-1 focus:ring-gray-400 transition-all" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="aman" {{ request('status') == 'aman' ? 'selected' : '' }}>Aman</option>
                    <option value="menipis" {{ request('status') == 'menipis' ? 'selected' : '' }}>Menipis</option>
                    <option value="habis" {{ request('status') == 'habis' ? 'selected' : '' }}>Habis</option>
                </select>
                <button type="submit" class="text-xs font-medium bg-white border border-gray-200 text-gray-600 rounded-2xl px-3 py-2 hover:bg-gray-50 transition-colors shrink-0">Cari</button>
                @if(request()->hasAny(['search', 'kategori', 'status']))
                    <a href="{{ route('stok-operasional.index') }}" class="text-xs font-medium text-red-500 hover:text-red-700 px-2 py-2 rounded-2xl hover:bg-red-50 transition-colors shrink-0">Reset</a>
                @endif
            </form>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-3xl border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-xs font-semibold text-gray-400 uppercase tracking-wide">
                        <th class="px-4 py-3 text-left w-12">No.</th>
                        <th class="px-4 py-3 text-left">Bahan Baku</th>
                        <th class="px-4 py-3 text-left">Kategori</th>
                        <th class="px-4 py-3 text-right">Stok Saat Ini</th>
                        <th class="px-4 py-3 text-right">Stok Min.</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($bahanBakus as $i => $bahan)
                    @php
                        $stok = (float)$bahan->stok;
                        $min = (float)$bahan->stok_minimal;
                        $isHabis = $stok <= 0;
                        $isMenipis = !$isHabis && $stok <= $min;
                    @endphp
                    <tr class="hover:bg-gray-50/60 transition-colors {{ $isHabis ? 'bg-red-50/30' : ($isMenipis ? 'bg-amber-50/30' : '') }}">
                        <td class="px-4 py-3 text-xs text-gray-500 font-medium align-middle">{{ $bahanBakus->firstItem() + $i }}</td>
                        <td class="px-4 py-3">
                            <p class="font-semibold text-gray-900 leading-tight">{{ $bahan->nama_bahan }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $bahan->kode_bahan }}</p>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-600 font-medium">{{ $bahan->kategori_bahan_baku->nama_kategori ?? '-' }}</td>
                        <td class="px-4 py-3 text-right">
                            <span class="font-bold text-lg {{ $isHabis ? 'text-red-600' : ($isMenipis ? 'text-amber-600' : 'text-emerald-600') }}">{{ number_format($stok, 2) }}</span>
                            <span class="text-xs text-gray-400"> {{ $bahan->satuan->singkatan ?? '' }}</span>
                        </td>
                        <td class="px-4 py-3 text-right text-xs text-gray-500 font-medium">
                            {{ number_format($min, 2) }} {{ $bahan->satuan->singkatan ?? '' }}
                        </td>
                        <td class="px-4 py-3">
                            @if($isHabis)
                                <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-xl bg-red-50 text-red-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>Habis
                                </span>
                            @elseif($isMenipis)
                                <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-xl bg-amber-50 text-amber-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>Menipis
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-xl bg-emerald-50 text-emerald-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Aman
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('bahan-baku.show', $bahan->id) }}" title="Detail" class="w-7 h-7 rounded-full flex items-center justify-center bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors">
                                    <x-heroicon-o-eye class="w-3 h-3" />
                                </a>
                                <a href="{{ route('penyesuaian-stok.create') }}" title="Penyesuaian Stok" class="w-7 h-7 rounded-full flex items-center justify-center bg-amber-50 text-amber-600 hover:bg-amber-100 transition-colors">
                                    <x-heroicon-o-cube class="w-3 h-3" />
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-14 text-center text-gray-400">
                            <x-heroicon-o-archive-box class="w-10 h-10 mb-3 text-gray-300" />
                            <p class="text-sm font-medium">Tidak ada data stok ditemukan.</p>
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
