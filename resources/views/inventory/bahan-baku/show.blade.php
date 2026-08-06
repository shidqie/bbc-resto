{{-- 
    Halaman: Detail Bahan Baku
    Deskripsi: Menampilkan informasi lengkap suatu bahan baku,
               status stok, informasi tambahan, dan riwayat mutasi stok.
--}}
@extends('layouts.pos')

@section('content')
<div class="flex-1 overflow-auto bg-gray-50/50 text-gray-800 font-sans">
    <div class="w-full p-6 ] space-y-6">
        
        {{-- Header --}}
        <x-ui.page-header 
            title="Detail Bahan Baku" 
            :breadcrumbs="['Bahan Baku', 'Daftar Bahan Baku', 'Detail']">
            <x-slot:actions>
                <x-ui.button href="{{ route('bahan-baku.index') }}" variant="outline" icon="arrow-left">Kembali</x-ui.button>
                <x-ui.button href="{{ route('bahan-baku.edit', $bahanBaku->id) }}" icon="pencil-square">Edit Data</x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        @php $stok = (float) ($bahanBaku->stok_harian?->jumlah_stok ?? 0); @endphp

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- Left Column: Info & Status --}}
            <div class="lg:col-span-1 space-y-6">
                {{-- Status Card --}}
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 text-center">
                    <div class="w-20 h-20 mx-auto rounded-full flex items-center justify-center text-4xl mb-4 
                        {{ $stok <= 0 ? 'bg-red-50 text-red-500' : ($stok <= $bahanBaku->stok_minimal ? 'bg-yellow-50 text-yellow-500' : 'bg-emerald-50 text-emerald-500') }}">
                        @if($stok <= 0)
                            <x-heroicon-o-x-circle class="w-5 h-5 inline-block shrink-0" />
                        @elseif($stok <= $bahanBaku->stok_minimal)
                            <x-heroicon-o-exclamation-triangle class="w-5 h-5 inline-block shrink-0" />
                        @else
                            <x-heroicon-o-check-circle class="w-5 h-5 inline-block shrink-0" />
                        @endif
                    </div>
                    <h2 class="text-xl font-bold text-gray-900 mb-1">{{ $bahanBaku->nama_bahan }}</h2>
                    <p class="text-sm text-gray-500 mb-4">{{ $bahanBaku->kode_bahan }}</p>
                    
                    <div class="inline-flex flex-col border border-gray-100 rounded-xl px-6 py-3 bg-gray-50/50 mb-4 w-full">
                        <span class="text-xs text-gray-500 font-medium mb-1">Stok Saat Ini</span>
                        <span class="text-3xl font-bold {{ $stok <= 0 ? 'text-red-600' : ($stok <= $bahanBaku->stok_minimal ? 'text-yellow-600' : 'text-emerald-600') }}">
                            {{ rtrim(rtrim(number_format($stok, 2, ',', '.'), '0'), ',') }} <span class="text-base font-normal text-gray-500">{{ $bahanBaku->satuan->singkatan }}</span>
                        </span>
                    </div>

                    <div>
                        @if(!$bahanBaku->status_aktif)
                            <x-ui.badge color="gray" dot>Status Nonaktif</x-ui.badge>
                        @elseif($stok <= 0)
                            <x-ui.badge color="danger" dot>Stok Habis</x-ui.badge>
                        @elseif($stok <= $bahanBaku->stok_minimal)
                            <x-ui.badge color="warning" dot>Stok Menipis</x-ui.badge>
                        @else
                            <x-ui.badge color="success" dot>Stok Aman</x-ui.badge>
                        @endif
                    </div>
                </div>

                {{-- Basic Info Card --}}
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="p-4 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="font-semibold text-gray-900">Identitas Bahan</h3>
                    </div>
                    <div class="p-4 space-y-4">
                        <div>
                            <p class="text-xs text-gray-500 font-medium">Kategori</p>
                            <p class="text-sm text-gray-900">{{ $bahanBaku->kategori_bahan_baku->nama_kategori }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 font-medium">Satuan Dasar</p>
                            <p class="text-sm text-gray-900">{{ $bahanBaku->satuan->nama_satuan }} ({{ $bahanBaku->satuan->singkatan }})</p>
                        </div>
                        <div class="pt-4 border-t border-gray-100">
                            <p class="text-xs text-gray-500 font-medium">Batas Minimum Stok</p>
                            <p class="text-sm text-gray-900">{{ rtrim(rtrim(number_format($bahanBaku->stok_minimal, 2, ',', '.'), '0'), ',') }} {{ $bahanBaku->satuan->singkatan }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Details & History --}}
            <div class="lg:col-span-2 space-y-6">
                
                {{-- Extra Details --}}
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="p-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                        <h3 class="font-semibold text-gray-900">Informasi Tambahan</h3>
                    </div>
                    <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-gray-500 font-medium mb-1"><x-heroicon-o-information-circle class="text-gray-400 w-4 h-4 inline-block shrink-0" /> Stok Minimal</p>
                            <p class="text-sm text-gray-900">{{ $bahanBaku->stok_minimal }} {{ $bahanBaku->satuan->singkatan }}</p>
                        </div>
                    </div>
                </div>

                {{-- Stock Mutations --}}
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="p-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                        <h3 class="font-semibold text-gray-900">Riwayat Stok (5 Terakhir)</h3>
                        <a href="{{ route('mutasi-stok.index', ['search' => $bahanBaku->kode_bahan]) }}" class="text-sm text-[#3B82F6] hover:text-[#2563EB] font-medium">
                            Lihat Semua Riwayat <x-heroicon-o-chevron-right class="ml-1 w-5 h-5 inline-block shrink-0" />
                        </a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left whitespace-nowrap">
                            <thead class="text-sm text-gray-500 uppercase bg-gray-50/50 border-b border-gray-100">
                                <tr>
                                    <th class="px-4 py-3 font-semibold">Tanggal</th>
                                    <th class="px-4 py-3 font-semibold">Jenis</th>
                                    <th class="px-4 py-3 font-semibold text-right">Jumlah</th>
                                    <th class="px-4 py-3 font-semibold text-right">Sisa Stok</th>
                                    <th class="px-4 py-3 font-semibold">Keterangan</th>
                                    <th class="px-4 py-3 font-semibold">Oleh</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @php $runningSisa = (float) $stok; @endphp
                                @forelse($mutasiStoks as $mutasi)
                                @php
                                    $arahStok = $mutasi->jenis_mutasi_stok->arah_stok ?? 'KELUAR';
                                    $isMasuk = $arahStok === 'MASUK';
                                    $runningSisa = $isMasuk ? $runningSisa - (float)$mutasi->jumlah : $runningSisa + (float)$mutasi->jumlah;
                                @endphp
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-3 text-gray-500">{{ $mutasi->dibuat_pada->format('d/m/Y H:i') }}</td>
                                    <td class="px-4 py-3">
                                        @if($isMasuk)
                                            <x-ui.badge color="success" size="sm">Masuk</x-ui.badge>
                                        @else
                                            <x-ui.badge color="danger" size="sm">Keluar</x-ui.badge>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right font-medium {{ $isMasuk ? 'text-emerald-600' : 'text-red-600' }}">
                                        {{ $isMasuk ? '+' : '-' }}{{ rtrim(rtrim(number_format($mutasi->jumlah, 2, ',', '.'), '0'), ',') }}
                                    </td>
                                    <td class="px-4 py-3 text-right text-gray-900 font-medium">
                                        {{ rtrim(rtrim(number_format($runningSisa, 2, ',', '.'), '0'), ',') }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 text-sm">
                                        {{ Str::limit($mutasi->catatan, 30) ?: '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-500 text-sm">
                                        {{ $mutasi->dibuat_oleh_pengguna->nama ?? 'Sistem' }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6">
                                        <x-ui.empty-state icon="clock" title="Belum ada riwayat pergerakan stok." />
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
        
    </div>
</div>
@endsection
