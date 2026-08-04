{{-- Halaman: Usulan Pengadaan (FR-14) --}}
@extends('layouts.pos')
@section('title', 'Usulan Pengadaan')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800">
    <div class="w-full p-6 space-y-5">

        {{-- PAGE HEADER --}}
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-black text-gray-900 tracking-tight">Usulan Pengadaan</h1>
                <p class="text-sm text-gray-500 font-medium mt-1">Kebutuhan produksi + stok pengaman − stok tersedia − sedang dipesan.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('pengadaan.create', ['tipe' => 'harian']) }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-amber-500 rounded-lg px-3 py-2 hover:bg-amber-600 transition-colors">
                    <x-heroicon-o-shopping-cart class="w-3 h-3" />
                    Buat Pengadaan
                </a>
                <a href="{{ route('pengadaan.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg px-3 py-2 hover:bg-gray-50 transition-colors">
                    Daftar Pengadaan
                </a>
            </div>
        </div>

        {{-- Rentang Hari --}}
        <form method="GET" action="{{ route('pengadaan.usulan') }}" class="flex flex-wrap items-center gap-2">
            <span class="text-sm font-semibold text-gray-600">Rentang pesanan mendatang:</span>
            @foreach([7, 14, 30] as $opt)
            <button type="submit" name="hari" value="{{ $opt }}"
                class="text-sm font-semibold rounded-lg px-3 py-2 transition-colors {{ $hari == $opt ? 'bg-gray-900 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
                {{ $opt }} hari
            </button>
            @endforeach
        </form>

        {{-- Stat Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div class="bg-white rounded-xl border border-red-200 px-4 py-3">
                <p class="text-sm font-medium text-gray-500">Bahan Perlu Dibeli</p>
                <p class="text-xl font-bold text-red-600 mt-1">{{ $stats['bahan_kurang'] }}</p>
            </div>
            <div class="bg-white rounded-xl border border-green-200 px-4 py-3">
                <p class="text-sm font-medium text-gray-500">Bahan Cukup</p>
                <p class="text-xl font-bold text-green-600 mt-1">{{ $stats['bahan_cukup'] }}</p>
            </div>
            <div class="bg-white rounded-xl border border-amber-200 px-4 py-3">
                <p class="text-sm font-medium text-gray-500">Total Usulan Beli</p>
                <p class="text-xl font-bold text-amber-600 mt-1">{{ number_format($stats['total_usulan'], 3, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-xl border border-blue-200 px-4 py-3">
                <p class="text-sm font-medium text-gray-500">Sedang Dipesan</p>
                <p class="text-xl font-bold text-blue-600 mt-1">{{ number_format($stats['sedang_dipesan'], 3, ',', '.') }}</p>
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-700">Rincian Usulan</h2>
                <span class="text-xs text-gray-400 font-medium">Rumus: (kebutuhan + pengaman − tersedia − dipesan)</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-xs font-semibold text-gray-400 uppercase tracking-wide">
                            <th class="px-4 py-3 text-left">Bahan Baku</th>
                            <th class="px-4 py-3 text-right">Kebutuhan Produksi</th>
                            <th class="px-4 py-3 text-right">Stok Tersedia</th>
                            <th class="px-4 py-3 text-right">Stok Pengaman</th>
                            <th class="px-4 py-3 text-right">Sedang Dipesan</th>
                            <th class="px-4 py-3 text-right">Usulan Beli</th>
                            <th class="px-4 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($usulan as $item)
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="px-4 py-3">
                                <p class="font-semibold text-gray-900">{{ $item['bahan']->nama_bahan }}</p>
                                <p class="text-xs text-gray-400">{{ $item['bahan']->kode_bahan }} · {{ $item['bahan']->satuan?->nama_satuan ?? '-' }}</p>
                            </td>
                            <td class="px-4 py-3 text-right text-gray-600">{{ number_format($item['kebutuhan_produksi'], 3, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right text-gray-600">{{ number_format($item['stok_tersedia'], 3, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right text-gray-600">{{ number_format($item['stok_pengaman'], 3, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right text-gray-600">{{ number_format($item['sedang_dipesan'], 3, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-bold {{ $item['cukup'] ? 'text-gray-400' : 'text-amber-600' }}">{{ number_format($item['usulan'], 3, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $item['cukup'] ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200' }}">
                                    {{ $item['cukup'] ? 'Cukup' : 'Kurang' }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-gray-400 text-sm">
                                Tidak ada bahan yang perlu diusulkan — semua stok mencukupi untuk {{ $hari }} hari ke depan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Info --}}
        <div class="bg-amber-50 border border-amber-200 rounded-xl px-5 py-4">
            <p class="text-xs font-semibold text-amber-700 mb-1">ℹ️ Tentang Usulan Pengadaan</p>
            <p class="text-xs text-amber-600">Usulan menghitung kebutuhan bahan dari pesanan Catering/Nasi Box pada {{ $hari }} hari ke depan. Klik <strong>Buat Pengadaan</strong> untuk mengubah usulan menjadi purchase order — jumlah tetap dapat diverifikasi pemilik sebelum disimpan.</p>
        </div>

    </div>
</div>
@endsection
