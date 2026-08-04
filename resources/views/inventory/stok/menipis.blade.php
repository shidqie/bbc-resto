{{-- Halaman: Stok Menipis --}}
@extends('layouts.pos')
@section('title', 'Stok Menipis')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800">
    <div class="w-full p-6 space-y-5">

        {{-- PAGE HEADER --}}
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-black text-gray-900 tracking-tight">Stok Menipis</h1>
                <p class="text-sm text-gray-500 font-medium mt-1">Daftar bahan baku yang stoknya sudah di bawah batas minimum, siap dibuatkan pengadaan.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('pengadaan.create', ['tipe' => 'harian']) }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-amber-500 rounded-lg px-3 py-2 hover:bg-amber-600 transition-colors">
                    <x-heroicon-o-shopping-cart class="w-3 h-3" />
                    Buat Pengadaan
                </a>
                <a href="{{ route('stok-operasional.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg px-3 py-2 hover:bg-gray-50 transition-colors">
                    Semua Stok
                </a>
            </div>
        </div>

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
                <div class="relative shrink-0">
                    <select name="kategori" class="w-full appearance-none rounded-lg border border-gray-200 bg-white py-2 pl-3 pr-9 text-sm text-gray-900 shadow-sm outline-none transition-all focus:border-gray-400 focus:ring-1 focus:ring-gray-400" onchange="this.form.submit()">
                        <option value="">Semua Kategori</option>
                        @foreach($kategoris as $kat)
                            <option value="{{ $kat->id }}" {{ request('kategori') == $kat->id ? 'selected' : '' }}>{{ $kat->nama_kategori }}</option>
                        @endforeach
                    </select>
                    <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </span>
                </div>
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
                        <td class="px-4 py-3 text-right font-bold {{ $stok <= 0 ? 'text-red-600' : 'text-amber-600' }}">{{ number_format($stok, 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-gray-600">{{ number_format($min, 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-900">{{ $kurang > 0 ? number_format($kurang, 2, ',', '.') : '-' }}</td>
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
