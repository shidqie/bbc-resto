{{-- Halaman: Stok Menipis --}}
@extends('layouts.pos')
@section('title', 'Stok Menipis')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800">
    <div class="w-full p-6 space-y-5">

        {{-- PAGE HEADER --}}
        <x-ui.page-header title="Stok Menipis" subtitle="Daftar bahan baku yang stoknya sudah di bawah batas minimum, siap dibuatkan pengadaan." :breadcrumbs="['Persediaan', 'Stok Menipis']">
            <x-slot:actions>
                <div class="flex items-center gap-2">
                    <a href="{{ route('pengadaan.harian.create') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-amber-500 rounded-lg px-3 py-2 hover:bg-amber-600 transition-colors">
                        <x-heroicon-o-shopping-cart class="w-3 h-3" />
                        Buat Pengadaan
                    </a>
                    <a href="{{ route('stok-operasional.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg px-3 py-2 hover:bg-gray-50 transition-colors">
                        Semua Stok
                    </a>
                </div>
            </x-slot:actions>
        </x-ui.page-header>

        {{-- Stat Cards --}}
        <div class="grid grid-cols-2 gap-3">
            <div class="bg-white rounded-xl border border-amber-200 px-4 py-3">
                <p class="text-sm font-medium text-gray-500">Stok Menipis</p>
                <p class="text-xl font-bold text-amber-600 mt-1">{{ $stats['total_menipis'] }}</p>
            </div>
            <div class="bg-white rounded-xl border border-red-200 px-4 py-3">
                <p class="text-sm font-medium text-gray-500">Stok Habis</p>
                <p class="text-xl font-bold text-red-600 mt-1">{{ $stats['total_habis'] }}</p>
            </div>
        </div>

        {{-- Filter Bar --}}
        <div class="flex flex-col sm:flex-row gap-2 items-start sm:items-center justify-between shrink-0">
            <form action="{{ route('stok-menipis.index') }}" method="GET" class="flex items-center gap-2 w-full sm:w-auto flex-wrap">
                <x-search-input name="search" value="{{ request('search') }}" placeholder="Cari bahan baku..." />
                <x-select-input name="kategori" :options="$kategoris->pluck('nama_kategori', 'id')->toArray()" :selected="request('kategori')" placeholder="Semua Kategori" :auto-submit="true" />
                <button type="submit" class="text-sm font-medium bg-white border border-gray-200 text-gray-600 rounded-lg px-3 py-2 hover:bg-gray-50 transition-colors shrink-0">Cari</button>
                @if(request()->hasAny(['search', 'kategori']))
                    <a href="{{ route('stok-menipis.index') }}" class="text-xs font-medium text-red-500 hover:text-red-700 px-2 py-2 rounded-lg hover:bg-red-50 transition-colors shrink-0">Reset</a>
                @endif
            </form>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-sm font-semibold text-gray-500 uppercase tracking-wide">
                        <th class="px-4 py-3 text-left">Bahan Baku</th>
                        <th class="px-4 py-3 text-left">Kategori</th>
                        <th class="px-4 py-3 text-right">Stok Saat Ini</th>
                        <th class="px-4 py-3 text-right">Stok Minimum</th>
                        <th class="px-4 py-3 text-right">Kekurangan</th>
                        <th class="px-4 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($bahanBakus as $bahan)
                    @php
                        $stok = (float) $bahan->stok;
                        $min = (float) $bahan->stok_minimal;
                        $kurang = max(0, $min - $stok);
                        $status = $stok <= 0 ? 'Habis' : 'Menipis';
                    @endphp
                    <tr class="hover:bg-gray-50/60 transition-colors">
                        <td class="px-4 py-3">
                            <p class="font-semibold text-gray-900">{{ $bahan->nama_bahan }}</p>
                            <p class="text-xs text-gray-400">{{ $bahan->satuan?->nama_satuan }}</p>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $bahan->kategori_bahan_baku?->nama_kategori ?? '-' }}</td>
                        <td class="px-4 py-3 text-right font-bold {{ $stok <= 0 ? 'text-red-600' : 'text-amber-600' }}">{{ \App\Helpers\UnitHelper::formatQuantity($stok, $bahan->satuan?->singkatan ?? $bahan->satuan?->nama_satuan ?? 'gram') }}</td>
                        <td class="px-4 py-3 text-right text-gray-600">{{ \App\Helpers\UnitHelper::formatQuantity($min, $bahan->satuan?->singkatan ?? $bahan->satuan?->nama_satuan ?? 'gram') }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-900">{{ $kurang > 0 ? \App\Helpers\UnitHelper::formatQuantity($kurang, $bahan->satuan?->singkatan ?? $bahan->satuan?->nama_satuan ?? 'gram') : '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $status === 'Habis' ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                {{ $status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <x-empty-state icon="cube" title="Semua stok bahan baku mencukupi" message="Tidak ada bahan baku dengan stok di bawah batas minimum." :colspan="6" />
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($bahanBakus->hasPages())
        <div class="mt-4">{{ $bahanBakus->links() }}</div>
        @endif

        {{-- Info --}}
        <div class="bg-amber-50 border border-amber-200 rounded-xl px-5 py-4">
            <p class="text-xs font-semibold text-amber-700 mb-1">ℹ️ Pengadaan dari Stok Menipis</p>
            <p class="text-xs text-amber-600">Klik <strong>Buat Pengadaan</strong> untuk membuat pengadaan harian — sistem akan otomatis memuat semua bahan baku yang stoknya di bawah minimum beserta jumlah yang perlu dibeli.</p>
        </div>

    </div>
</div>
@endsection
