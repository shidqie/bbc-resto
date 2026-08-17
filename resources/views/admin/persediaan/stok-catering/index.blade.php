{{-- Halaman: Stok Catering --}}
@extends('layouts.pos')
@section('title', 'Stok Catering')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800">
    <div class="w-full p-6 space-y-5">

        {{-- PAGE HEADER --}}
        <x-ui.page-header
            title="Stok Catering"
            subtitle="Monitor stok bahan baku khusus untuk pemenuhan pesanan Catering."
            :breadcrumbs="['Persediaan', 'Stok Catering']">
            <x-slot:actions>
                <x-ui.button variant="primary" icon="plus" href="{{ route('pengadaan.po.create', ['tipe' => 'Catering']) }}">
                    Buat PO Catering
                </x-ui.button>
                <x-ui.button variant="secondary" href="{{ route('bahan-baku.index') }}">
                    Data Bahan Baku
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.tab-list class="mb-4">
            <x-ui.tab :active="$tab === 'stok'" href="{{ route('stok-catering.index', ['tab' => 'stok']) }}">
                Stok Saat Ini
            </x-ui.tab>
            <x-ui.tab :active="$tab === 'riwayat'" href="{{ route('stok-catering.index', ['tab' => 'riwayat']) }}">
                Riwayat Penggunaan
            </x-ui.tab>
        </x-ui.tab-list>

        <x-ui.alert />

        @if($tab === 'stok')
            {{-- Stat Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
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

            {{-- Table Stok --}}
            <x-ui.data-table :paginator="$bahanBakus">
                <x-slot:toolbar>
                    <form action="{{ route('stok-catering.index') }}" method="GET" class="flex items-center gap-2 w-full flex-wrap">
                        <input type="hidden" name="tab" value="stok">
                        <x-search-input name="search" value="{{ request('search') }}" placeholder="Cari bahan baku..." />
                        <x-ui.multi-select name="kategori" :options="$kategoris->pluck('nama_kategori', 'id')->toArray()" :selected="request('kategori')" label="Kategori" type="radio" />
                        <x-ui.multi-select name="status" :options="['aman' => 'Aman', 'menipis' => 'Menipis', 'habis' => 'Habis']" :selected="request('status')" label="Status" type="radio" />
                        @if(request()->hasAny(['search', 'kategori', 'status']))
                            <x-ui.button href="{{ route('stok-catering.index', ['tab' => 'stok']) }}" variant="danger" size="sm">Reset</x-ui.button>
                        @endif
                    </form>
                </x-slot:toolbar>

                <x-ui.table class="min-w-[850px]">
                    <x-ui.table.header>
                        <th class="px-4 py-3.5 text-left w-12">No</th>
                        <th class="px-4 py-3.5 text-left">Nama Bahan</th>
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
                            <td class="px-4 py-4 text-right">
                                <span class="font-bold text-lg {{ $isHabis ? 'text-red-600' : ($isMenipis ? 'text-amber-600' : 'text-emerald-600') }}">{{ rtrim(rtrim(number_format($stok, 2), '0'), '.') }} {{ $bahan->satuan->singkatan ?? '' }}</span>
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
                                        <x-heroicon-o-eye class="w-4 h-4" />
                                    </x-ui.action-button>
                                </div>
                            </td>
                        </x-ui.table.row>
                        @empty
                        <tr>
                            <td colspan="6">
                                <x-ui.empty-state icon="cube" title="Belum ada data stok" message="Tidak ada data stok ditemukan." />
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </x-ui.table>
            </x-ui.data-table>
        
        @else
            {{-- Table Riwayat --}}
            <x-ui.data-table :paginator="$riwayats">
                <x-slot:toolbar>
                    <form action="{{ route('stok-catering.index') }}" method="GET" class="flex items-center gap-2 w-full flex-wrap">
                        <input type="hidden" name="tab" value="riwayat">
                        <x-search-input name="search" value="{{ request('search') }}" placeholder="Cari bahan / referensi..." />
                        <x-ui.multi-select name="jenis_penggunaan" :options="['Catering' => 'Catering', 'Penyesuaian' => 'Penyesuaian Keluar']" :selected="request('jenis_penggunaan')" label="Jenis Penggunaan" type="radio" />
                        @if(request()->hasAny(['search', 'jenis_penggunaan']))
                            <x-ui.button href="{{ route('stok-catering.index', ['tab' => 'riwayat']) }}" variant="danger" size="sm">Reset</x-ui.button>
                        @endif
                    </form>
                </x-slot:toolbar>

                <x-ui.table class="min-w-[850px]">
                    <x-ui.table.header>
                        <th class="px-4 py-3.5 text-left w-12">No</th>
                        <th class="px-4 py-3.5 text-left">Transaksi / Referensi</th>
                        <th class="px-4 py-3.5 text-left">Waktu Mutasi</th>
                        <th class="px-4 py-3.5 text-left">Total Bahan</th>
                        <th class="px-4 py-3.5 text-center">Aksi</th>
                    </x-ui.table.header>
                    
                    @php
                        $groupedRiwayat = [];
                        foreach($riwayats as $riwayat) {
                            $key = $riwayat->catatan . '|' . $riwayat->tanggal_mutasi->format('Y-m-d H:i');
                            if (!isset($groupedRiwayat[$key])) {
                                $groupedRiwayat[$key] = [
                                    'tanggal' => $riwayat->tanggal_mutasi,
                                    'catatan' => $riwayat->catatan,
                                    'referensi' => $riwayat->referensi_id,
                                    'items' => []
                                ];
                            }
                            $groupedRiwayat[$key]['items'][] = $riwayat;
                        }
                        $noGroup = $riwayats->firstItem();
                    @endphp

                    @forelse($groupedRiwayat as $groupKey => $group)
                        <tbody x-data="{ expanded: false }" class="divide-y divide-gray-100">
                            <tr class="hover:bg-gray-50/80 transition-colors cursor-pointer group" @click="expanded = !expanded">
                                <td class="px-4 py-4 text-sm text-gray-500">{{ $noGroup++ }}</td>
                                <td class="px-4 py-4">
                                    <div class="font-bold text-gray-900 text-sm flex items-center gap-2">
                                        <span>{{ $group['catatan'] ?: 'Tanpa Keterangan' }}</span>
                                        @if($group['referensi'])
                                            <span class="text-[10px] bg-gray-100 text-gray-600 px-2 py-0.5 rounded-md border border-gray-200">Ref: {{ $group['referensi'] }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-600">
                                    {{ $group['tanggal']->format('d M Y, H:i') }}
                                </td>
                                <td class="px-4 py-4 text-sm">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 font-bold border border-blue-200">
                                        {{ count($group['items']) }} Bahan Keluar
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <button type="button" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
                                        <x-heroicon-o-chevron-down class="w-5 h-5 transition-transform duration-200" x-bind:class="expanded ? 'rotate-180' : ''" />
                                    </button>
                                </td>
                            </tr>
                            
                            {{-- Expanded Details --}}
                            <tr x-show="expanded" x-cloak class="bg-white border-b border-gray-100">
                                <td colspan="5" class="p-0 border-t-0">
                                    <div class="px-6 py-4 md:px-14 md:py-5 bg-white">
                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-12 gap-y-3">
                                            @foreach($group['items'] as $item)
                                                <div class="flex justify-between items-center py-2 border-b border-gray-50 last:border-0">
                                                    <div>
                                                        <p class="text-[13px] font-bold text-gray-800 leading-none">{{ $item->bahan_baku->nama_bahan ?? '-' }}</p>
                                                        <p class="text-[10px] text-gray-400 mt-1.5 font-medium">Sisa: {{ rtrim(rtrim(number_format($item->stok_sesudah ?? 0, 2), '0'), '.') }} {{ $item->bahan_baku->satuan->singkatan ?? '' }}</p>
                                                    </div>
                                                    <div class="text-right">
                                                        <p class="text-sm font-black text-red-500">-{{ rtrim(rtrim(number_format($item->jumlah, 2), '0'), '.') }} <span class="text-xs font-bold text-red-400">{{ $item->bahan_baku->satuan->singkatan ?? '' }}</span></p>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    @empty
                        <tbody>
                            <tr>
                                <td colspan="5">
                                    <x-ui.empty-state icon="clock" title="Belum ada riwayat" message="Tidak ada data riwayat penggunaan stok." />
                                </td>
                            </tr>
                        </tbody>
                    @endforelse
                </x-ui.table>
            </x-ui.data-table>
        @endif
    </div>
</div>
@endsection
