{{-- Halaman: Stok Catering --}}
@extends('layouts.pos')
@section('title', 'Stok Catering')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800">
    <div class="w-full p-6 space-y-5">

        {{-- PAGE HEADER --}}
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-black text-gray-900 tracking-tight">Stok Catering</h1>
                <p class="text-sm text-gray-500 font-medium mt-1">Monitor kebutuhan & ketersediaan bahan baku per pesanan catering berdasarkan resep menu × jumlah porsi.</p>
            </div>
            <a href="{{ route('pengadaan.create') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-gray-900 rounded-2xl px-3 py-2 hover:bg-gray-800 transition-colors">
                <x-heroicon-o-plus class="w-3 h-3" />
                Buat Pengadaan
            </a>
        </div>

        {{-- Stat Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div class="bg-white rounded-3xl border border-gray-200 px-4 py-3">
                <p class="text-xs font-medium text-gray-500">Total Bahan Catering</p>
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
            <form action="{{ route('stok-catering.index') }}" method="GET" class="flex items-center gap-2 w-full sm:w-auto flex-wrap">
                <div class="relative flex-1 sm:flex-none sm:w-64">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nomor pesanan..." class="w-full pl-8 pr-3 py-2 text-xs border border-gray-200 rounded-2xl outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-all bg-white">
                </div>
                <button type="submit" class="text-xs font-medium bg-white border border-gray-200 text-gray-600 rounded-2xl px-3 py-2 hover:bg-gray-50 transition-colors shrink-0">Cari</button>
                @if(request()->hasAny(['search']))
                    <a href="{{ route('stok-catering.index') }}" class="text-xs font-medium text-red-500 hover:text-red-700 px-2 py-2 rounded-2xl hover:bg-red-50 transition-colors shrink-0">Reset</a>
                @endif
            </form>
        </div>

        {{-- Pesanan List --}}
        <div class="space-y-4">
            @forelse($pesanans as $pesanan)
            @php
                $totalKebutuhan = $pesanan->stok_catering->sum('kebutuhan');
                $totalDiterima = $pesanan->stok_catering->sum('diterima');
                $totalDigunakan = $pesanan->stok_catering->sum('digunakan');
                $bahanCount = $pesanan->stok_catering->count();
                $kurangCount = $pesanan->stok_catering->filter(fn($s) => $s->diterima < $s->kebutuhan)->count();
                $statusBahan = $kurangCount > 0 ? 'kurang' : ($bahanCount > 0 ? 'cukup' : 'belum');
            @endphp
            <div class="bg-white rounded-3xl border border-gray-200 overflow-hidden" x-data="{ open: false }">
                {{-- Pesanan Header --}}
                <div class="flex items-center justify-between px-5 py-4 cursor-pointer hover:bg-gray-50/50 transition-colors" @click="open = !open">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full bg-violet-100 flex items-center justify-center shrink-0">
                            <x-heroicon-o-sparkles class="w-4 h-4 text-violet-600" />
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900 text-sm">{{ $pesanan->nomor_pesanan }}</p>
                            <p class="text-sm text-gray-500 font-medium mt-1">
                                {{ $pesanan->pelanggan->nama ?? 'Tanpa Pelanggan' }} •
                                {{ \Carbon\Carbon::parse($pesanan->tanggal_pesanan)->translatedFormat('d M Y') }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        {{-- Status Badge --}}
                        @if($bahanCount === 0)
                            <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full bg-gray-100 text-gray-500">
                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>Belum Ada Data Bahan
                            </span>
                        @elseif($kurangCount > 0)
                            <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full bg-amber-100 text-amber-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>{{ $kurangCount }} Bahan Kurang
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Bahan Terpenuhi
                            </span>
                        @endif

                        {{-- Chevron --}}
                        <x-heroicon-o-chevron-down class="w-3 h-3 text-gray-400 transition-transform duration-200" x-bind:class="{ 'rotate-180': open }" />
                    </div>
                </div>

                {{-- Detail Bahan --}}
                <div x-show="open" x-collapse class="border-t border-gray-100">
                    @if($pesanan->stok_catering->count() === 0)
                    <div class="py-8 text-center text-gray-400">
                        <x-heroicon-o-archive-box class="w-10 h-10 mb-2 text-gray-300" />
                        <p class="text-sm font-medium">Belum ada data kebutuhan bahan untuk pesanan ini.</p>
                        <p class="text-xs text-gray-400 mt-1">Kebutuhan dihitung otomatis saat resep menu dikonfigurasi.</p>
                    </div>
                    @else
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 text-xs font-semibold text-gray-400 uppercase tracking-wide">
                                <th class="px-5 py-2.5 text-left">Bahan Baku</th>
                                <th class="px-5 py-2.5 text-right">Kebutuhan</th>
                                <th class="px-5 py-2.5 text-right">Diterima</th>
                                <th class="px-5 py-2.5 text-right">Digunakan</th>
                                <th class="px-5 py-2.5 text-right">Sisa</th>
                                <th class="px-5 py-2.5 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($pesanan->stok_catering as $stok)
                            @php
                                $sisa = $stok->diterima - $stok->digunakan;
                                $kurang = $stok->kebutuhan - $stok->diterima;
                                $isCukup = $stok->diterima >= $stok->kebutuhan;
                            @endphp
                            <tr class="hover:bg-gray-50/40 transition-colors">
                                <td class="px-5 py-3">
                                    <p class="font-medium text-gray-900 text-sm">{{ $stok->bahan_baku->nama_bahan ?? '-' }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $stok->bahan_baku->kode_bahan ?? '' }}</p>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <span class="font-semibold text-gray-800">{{ number_format($stok->kebutuhan, 2) }}</span>
                                    <span class="text-xs text-gray-400"> {{ $stok->bahan_baku->satuan->singkatan ?? '' }}</span>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <span class="font-semibold {{ $isCukup ? 'text-emerald-600' : 'text-amber-600' }}">{{ number_format($stok->diterima, 2) }}</span>
                                    <span class="text-xs text-gray-400"> {{ $stok->bahan_baku->satuan->singkatan ?? '' }}</span>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <span class="font-semibold text-gray-600">{{ number_format($stok->digunakan, 2) }}</span>
                                    <span class="text-xs text-gray-400"> {{ $stok->bahan_baku->satuan->singkatan ?? '' }}</span>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <span class="font-bold {{ $sisa >= 0 ? 'text-gray-800' : 'text-red-600' }}">{{ number_format($sisa, 2) }}</span>
                                    <span class="text-xs text-gray-400"> {{ $stok->bahan_baku->satuan->singkatan ?? '' }}</span>
                                </td>
                                <td class="px-5 py-3 text-center">
                                    @if($isCukup)
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-xl bg-emerald-50 text-emerald-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Cukup
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-xl bg-amber-50 text-amber-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>Kurang {{ number_format($kurang, 2) }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endif
                </div>
            </div>
            @empty
            <div class="bg-white rounded-3xl border border-gray-200 py-16 text-center text-gray-400">
                <x-heroicon-o-clipboard-document-list class="w-12 h-12 mb-3 text-gray-300" />
                <p class="text-sm font-medium">Belum ada pesanan catering yang ditemukan.</p>
                <p class="text-xs text-gray-400 mt-1">Data akan muncul setelah ada pesanan catering masuk.</p>
            </div>
            @endforelse
        </div>

        <div class="mt-4 shrink-0">{{ $pesanans->links() }}</div>

    </div>
</div>
@endsection
