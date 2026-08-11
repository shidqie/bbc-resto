{{-- Halaman: Stok Operasional --}}
@extends('layouts.pos')
@section('title', 'Stok Operasional')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800">
    <div class="w-full p-6 space-y-5">

        {{-- PAGE HEADER --}}
        <x-ui.page-header
            title="Stok Operasional"
            subtitle="Monitor stok bahan baku untuk kebutuhan dine in dan nasi box."
            :breadcrumbs="['Persediaan', 'Stok Operasional']">
            <x-slot:actions>
                <a href="{{ route('pengadaan.harian.create') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-gray-900 rounded-lg px-3 py-2 hover:bg-gray-800 transition-colors">
                    <x-heroicon-o-plus class="w-4 h-4" />
                    Buat Permintaan
                </a>
                <a href="{{ route('bahan-baku.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg px-3 py-2 hover:bg-gray-50 transition-colors">
                    Data Bahan Baku
                </a>
            </x-slot:actions>
        </x-ui.page-header>

        {{-- Stat Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-center">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Total Bahan</span>
                <span class="text-2xl font-bold text-gray-900">{{ $stats['total_bahan'] }}</span>
            </div>
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-center">
                <span class="text-xs font-semibold text-emerald-600 uppercase tracking-wider mb-1">Stok Aman</span>
                <span class="text-2xl font-bold text-gray-900">{{ $stats['total_aman'] }}</span>
            </div>
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-center">
                <span class="text-xs font-semibold text-amber-600 uppercase tracking-wider mb-1">Stok Menipis</span>
                <span class="text-2xl font-bold text-gray-900">{{ $stats['total_menipis'] }}</span>
            </div>
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-center">
                <span class="text-xs font-semibold text-red-600 uppercase tracking-wider mb-1">Stok Habis</span>
                <span class="text-2xl font-bold text-gray-900">{{ $stats['total_habis'] }}</span>
            </div>
        </div>

        <x-ui.alert />

        {{-- Table with integrated toolbar --}}
        <x-ui.data-table :paginator="$bahanBakus">
            <x-slot:toolbar>
                <form action="{{ route('stok-operasional.index') }}" method="GET" class="flex items-center gap-2 w-full flex-wrap">
                    <x-search-input name="search" value="{{ request('search') }}" placeholder="Cari bahan baku..." />
                    <x-ui.multi-select name="kategori" :options="$kategoris->pluck('nama_kategori', 'id')->toArray()" :selected="request('kategori')" label="Kategori" type="radio" />
                    <x-ui.multi-select name="status" :options="['aman' => 'Aman', 'menipis' => 'Menipis', 'habis' => 'Habis']" :selected="request('status')" label="Status" type="radio" />
                    @if(request()->hasAny(['search', 'kategori', 'status']))
                        <a href="{{ route('stok-operasional.index') }}" class="text-xs font-medium text-red-500 hover:text-red-700 px-2 py-2 rounded-lg hover:bg-red-50 transition-colors shrink-0">Reset</a>
                    @endif
                </form>
            </x-slot:toolbar>

            <x-ui.table class="min-w-[850px]">
                <x-ui.table.header>
                    <th class="px-4 py-3.5 text-left w-12">No</th>
                    <th class="px-4 py-3.5 text-left">Nama Bahan</th>
                    <th class="px-4 py-3.5 text-left">Satuan</th>
                    <th class="px-4 py-3.5 text-right">Stok Saat Ini</th>
                    <th class="px-4 py-3.5 text-right">Stok Minimum</th>
                    <th class="px-4 py-3.5 text-left">Status</th>
                    <th class="px-4 py-3.5 text-center">Aksi</th>
                </x-ui.table.header>
                <tbody class="divide-y divide-gray-100">
                    @forelse($bahanBakus as $i => $bahan)
                    @php
                        $stok = (float)$bahan->stok;
                        $min = (float)$bahan->stok_minimal;
                        $isHabis = $stok <= 0;
                        $isMenipis = !$isHabis && $stok <= $min;
                    @endphp
                    <x-ui.table.row class="{{ $isHabis ? 'bg-red-50/30' : ($isMenipis ? 'bg-amber-50/30' : '') }}">
                        <td class="px-4 py-4 text-sm text-gray-500 font-medium align-middle">{{ $bahanBakus->firstItem() + $i }}</td>
                        <td class="px-4 py-4">
                            <p class="font-semibold text-gray-900 leading-tight">{{ $bahan->nama_bahan }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $bahan->id_bahan_baku }}</p>
                        </td>
                        <td class="px-4 py-4 text-sm text-gray-600 font-medium">{{ $bahan->satuan->nama_satuan ?? '-' }}</td>
                        <td class="px-4 py-4 text-right">
                            <span class="font-bold text-lg {{ $isHabis ? 'text-red-600' : ($isMenipis ? 'text-amber-600' : 'text-emerald-600') }}">{{ rtrim(rtrim(number_format($stok, 2), '0'), '.') }}</span>
                            <span class="text-xs text-gray-400"> {{ $bahan->satuan->singkatan ?? '' }}</span>
                        </td>
                        <td class="px-4 py-4 text-right text-sm text-gray-500 font-medium">
                            {{ rtrim(rtrim(number_format($min, 2), '0'), '.') }} {{ $bahan->satuan->singkatan ?? '' }}
                        </td>
                        <td class="px-4 py-4">
                            @if($isHabis)
                                <x-ui.badge color="danger" dot>Habis</x-ui.badge>
                            @elseif($isMenipis)
                                <x-ui.badge color="warning" dot>Menipis</x-ui.badge>
                            @else
                                <x-ui.badge color="success" dot>Aman</x-ui.badge>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <x-ui.action-button onclick="openDetailDrawer({{ $bahan->id }})" title="Detail">
                                    <x-heroicon-o-eye class="w-3 h-3" />
                                </x-ui.action-button>
                                <a href="{{ route('mutasi-stok.index', ['bahan_baku_id' => $bahan->id, 'jenis_persediaan' => 'Harian']) }}" title="Riwayat Stok" class="w-7 h-7 rounded-full flex items-center justify-center bg-purple-50 text-purple-600 hover:bg-purple-100 transition-colors">
                                    <x-heroicon-o-clock class="w-3 h-3" />
                                </a>
                            </div>
                        </td>
                    </x-ui.table.row>
                    @empty
                    <x-empty-state icon="cube" title="Belum ada data stok" message="Tidak ada data stok ditemukan." :colspan="7" />
                    @endforelse
                </tbody>
            </x-ui.table>
        </x-ui.data-table>

    </div>
</div>
@endsection
