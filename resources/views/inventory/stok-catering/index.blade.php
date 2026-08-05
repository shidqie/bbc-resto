{{-- Halaman: Stok Katering --}}
@extends('layouts.pos')
@section('title', 'Stok Katering')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800">
    <div class="w-full p-6 space-y-5">

        {{-- PAGE HEADER --}}
        <x-ui.page-header
            title="Stok Katering"
            subtitle="Monitor kebutuhan & ketersediaan bahan baku per pesanan catering berdasarkan resep menu × jumlah porsi."
            :breadcrumbs="['Persediaan', 'Stok Katering']">
            <x-slot:actions>
                <a href="{{ route('pengadaan.catering.create') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-gray-900 rounded-lg px-3 py-2 hover:bg-gray-800 transition-colors">
                    <x-heroicon-o-plus class="w-4 h-4" />
                    Buat Permintaan
                </a>
            </x-slot:actions>
        </x-ui.page-header>

        {{-- Stat Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <x-ui.stat-card label="Total Bahan Katering" :value="$stats['total_bahan']" icon="cube" color="blue" />
            <x-ui.stat-card label="Stok Aman" :value="$stats['total_aman']" icon="check-circle" color="green" />
            <x-ui.stat-card label="Stok Menipis" :value="$stats['total_menipis']" icon="exclamation-triangle" color="orange" />
            <x-ui.stat-card label="Stok Habis" :value="$stats['total_habis']" icon="x-circle" color="red" />
        </div>

        <x-ui.alert />

        {{-- Table with integrated toolbar --}}
        <x-ui.data-table :paginator="$bahanBakus">
            <x-slot:toolbar>
                <form action="{{ route('stok-catering.index') }}" method="GET" class="flex items-center gap-2 w-full flex-wrap">
                    <x-search-input name="search" value="{{ request('search') }}" placeholder="Cari bahan baku..." />
                    <x-select-input name="kategori" :options="$kategoris->pluck('nama_kategori', 'id')->toArray()" :selected="request('kategori')" placeholder="Semua Kategori" :auto-submit="true" />
                    <x-select-input name="status" :options="['aman' => 'Aman', 'menipis' => 'Menipis', 'habis' => 'Habis']" :selected="request('status')" placeholder="Semua Status" :auto-submit="true" />
                    <button type="submit" class="text-sm font-medium bg-white border border-gray-200 text-gray-600 rounded-lg px-3 py-2 hover:bg-gray-50 transition-colors shrink-0">Cari</button>
                    @if(request()->hasAny(['search', 'kategori', 'status']))
                        <a href="{{ route('stok-catering.index') }}" class="text-xs font-medium text-red-500 hover:text-red-700 px-2 py-2 rounded-lg hover:bg-red-50 transition-colors shrink-0">Reset</a>
                    @endif
                </form>
            </x-slot:toolbar>

            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        <th class="px-4 py-3 text-left w-12">No</th>
                        <th class="px-4 py-3 text-left">Nama Bahan</th>
                        <th class="px-4 py-3 text-left">Satuan</th>
                        <th class="px-4 py-3 text-right">Stok Saat Ini</th>
                        <th class="px-4 py-3 text-right">Stok Minimum</th>
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
                        <td class="px-4 py-3 text-sm text-gray-500 font-medium align-middle">{{ $bahanBakus->firstItem() + $i }}</td>
                        <td class="px-4 py-3">
                            <p class="font-semibold text-gray-900 leading-tight">{{ $bahan->nama_bahan }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $bahan->kode_bahan }}</p>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600 font-medium">{{ $bahan->satuan->nama_satuan ?? '-' }}</td>
                        <td class="px-4 py-3 text-right">
                            <span class="font-bold text-lg {{ $isHabis ? 'text-red-600' : ($isMenipis ? 'text-amber-600' : 'text-emerald-600') }}">{{ number_format($stok, 2) }}</span>
                            <span class="text-xs text-gray-400"> {{ $bahan->satuan->singkatan ?? '' }}</span>
                        </td>
                        <td class="px-4 py-3 text-right text-sm text-gray-500 font-medium">
                            {{ number_format($min, 2) }} {{ $bahan->satuan->singkatan ?? '' }}
                        </td>
                        <td class="px-4 py-3">
                            @if($isHabis)
                                <x-ui.badge color="danger" dot>Habis</x-ui.badge>
                            @elseif($isMenipis)
                                <x-ui.badge color="warning" dot>Menipis</x-ui.badge>
                            @else
                                <x-ui.badge color="success" dot>Aman</x-ui.badge>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <button type="button" onclick="openDetailDrawer({{ $bahan->id }})" title="Detail" class="w-7 h-7 rounded-full flex items-center justify-center bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors">
                                    <x-heroicon-o-eye class="w-3 h-3" />
                                </button>
                                <a href="{{ route('mutasi-stok.index', ['bahan_baku_id' => $bahan->id, 'jenis_persediaan' => 'Catering']) }}" title="Riwayat Stok" class="w-7 h-7 rounded-full flex items-center justify-center bg-purple-50 text-purple-600 hover:bg-purple-100 transition-colors">
                                    <x-heroicon-o-clock class="w-3 h-3" />
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <x-empty-state icon="cube" title="Belum ada data stok" message="Tidak ada data stok ditemukan." :colspan="7" />
                    @endforelse
                </tbody>
            </table>
        </x-ui.data-table>

    </div>
</div>
@endsection
