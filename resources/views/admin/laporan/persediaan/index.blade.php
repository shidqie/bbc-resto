@extends('layouts.pos')
@section('title', 'Laporan Persediaan Bahan Baku')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800 pb-10">
    <div class="w-full p-6 space-y-5">

        {{-- PAGE HEADER --}}
        <x-ui.page-header
            title="Laporan Persediaan Bahan Baku"
            subtitle="Informasi kondisi dan pergerakan persediaan bahan baku operasional dan katering."
            :breadcrumbs="['Laporan', 'Persediaan']">
        </x-ui.page-header>

        {{-- TAB NAVIGATION: HARIAN VS KATERING --}}
        <x-ui.tab-list class="mb-2">
            <x-ui.tab :active="$tab === 'harian'" href="{{ route('laporan.persediaan', array_merge(request()->except(['page']), ['tab' => 'harian'])) }}">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    <span>Stok Harian (Dine-In & Nasi Box)</span>
                </div>
            </x-ui.tab>
            <x-ui.tab :active="$tab === 'katering'" href="{{ route('laporan.persediaan', array_merge(request()->except(['page']), ['tab' => 'katering'])) }}">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.701 2.701 0 01-1.5-.454M9 6v2m3-2v2m3-2v2M9 3h.01M12 3h.01M15 3h.01M21 21v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7h18z"></path></svg>
                    <span>Stok Katering</span>
                </div>
            </x-ui.tab>
        </x-ui.tab-list>

        <x-ui.alert />

        {{-- KPI CARDS --}}
        @if($tab === 'harian')
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-center">
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Total Bahan Baku Harian</span>
                    <span class="text-2xl font-black text-gray-900">{{ number_format($stats['total_bahan']) }}</span>
                </div>
                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-center">
                    <span class="text-xs font-bold text-amber-600 uppercase tracking-wider mb-1">Stok Menipis</span>
                    <span class="text-2xl font-black text-amber-700">{{ number_format($stats['total_menipis']) }}</span>
                </div>
                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-center">
                    <span class="text-xs font-bold text-rose-600 uppercase tracking-wider mb-1">Stok Habis</span>
                    <span class="text-2xl font-black text-rose-700">{{ number_format($stats['total_habis']) }}</span>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-center">
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Total Bahan Baku Katering</span>
                    <span class="text-2xl font-black text-gray-900">{{ number_format($stats['total_bahan']) }}</span>
                </div>
                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-center">
                    <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider mb-1">Stok Sisa Tersedia</span>
                    <span class="text-2xl font-black text-emerald-700">{{ number_format($stats['total_tersedia']) }}</span>
                </div>
                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-center">
                    <span class="text-xs font-bold text-rose-600 uppercase tracking-wider mb-1">Stok Kosong</span>
                    <span class="text-2xl font-black text-rose-700">{{ number_format($stats['total_habis']) }}</span>
                </div>
            </div>
        @endif

        {{-- Table with integrated toolbar --}}
        <x-ui.data-table :paginator="$paginatedBahan">
            <x-slot:toolbar>
                <form action="{{ route('laporan.persediaan') }}" method="GET" class="flex flex-col xl:flex-row items-start xl:items-center justify-between gap-4 w-full">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    
                    <div class="flex flex-wrap items-center gap-2 w-full xl:w-auto">
                        <div class="w-full sm:w-auto shrink-0">
                            <x-search-input name="search" value="{{ request('search') }}" placeholder="Cari kode atau nama bahan baku..." width="w-full" />
                        </div>
                        
                        @if($tab === 'harian')
                            <x-ui.multi-select name="kondisi" :options="['semua' => 'Semua Kondisi', 'Aman' => 'Aman', 'Menipis' => 'Menipis', 'Habis' => 'Habis']" :selected="request('kondisi', 'semua')" label="Kondisi" type="radio" />
                        @else
                            <x-ui.multi-select name="kondisi" :options="['semua' => 'Semua Status', 'Tersedia' => 'Stok Tersedia', 'Habis' => 'Stok Kosong']" :selected="request('kondisi', 'semua')" label="Status" type="radio" />
                        @endif

                        @if(count($kategoris) > 0)
                            <x-ui.multi-select name="kategori_id" :options="$kategoris->pluck('nama_kategori', 'id')->toArray()" :selected="request('kategori_id', [])" label="Kategori Bahan" />
                        @endif
                        
                        <div class="flex items-center gap-2 shrink-0">
                            <button type="submit" class="inline-flex items-center gap-1.5 text-xs font-bold text-white bg-emerald-600 rounded-xl px-3.5 py-2.5 hover:bg-emerald-700 transition-colors shadow-2xs">
                                <x-heroicon-o-funnel class="w-4 h-4" />
                                Terapkan
                            </button>
                            @if(request()->hasAny(['search', 'kategori_id']) || (request('kondisi') && request('kondisi') !== 'semua'))
                                <a href="{{ route('laporan.persediaan', ['tab' => $tab]) }}" class="text-xs font-semibold text-rose-600 hover:text-rose-700 px-2 py-2 transition-colors">Reset</a>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <x-ui.button variant="secondary" icon="document-text" href="{{ route('laporan.persediaan.cetak-pdf', array_merge(request()->all(), ['tab' => $tab])) }}" target="_blank" size="sm">
                            Export PDF
                        </x-ui.button>
                    </div>
                </form>
            </x-slot:toolbar>

            <x-ui.table class="min-w-[950px]">
                <x-ui.table.header>
                    <th class="px-4 py-3.5 text-left w-12">No</th>
                    <th class="px-4 py-3.5 text-left">Kode</th>
                    <th class="px-4 py-3.5 text-left">Nama Bahan Baku</th>
                    <th class="px-4 py-3.5 text-left">Kategori</th>
                    <th class="px-4 py-3.5 text-center">Satuan</th>
                    @if($tab === 'harian')
                        <th class="px-4 py-3.5 text-right">Stok Saat Ini</th>
                        <th class="px-4 py-3.5 text-right">Stok Minimum</th>
                        <th class="px-4 py-3.5 text-center">Kondisi</th>
                    @else
                        <th class="px-4 py-3.5 text-right">Stok Sisa Katering</th>
                        <th class="px-4 py-3.5 text-center">Status</th>
                    @endif
                    <th class="px-4 py-3.5 text-right">Aksi</th>
                </x-ui.table.header>
                <tbody class="divide-y divide-gray-100">
                    @forelse($paginatedBahan as $i => $item)
                    <x-ui.table.row>
                        <td class="px-4 py-4 align-middle text-sm text-gray-500 font-medium">
                            {{ $paginatedBahan->firstItem() + $i }}
                        </td>
                        <td class="px-4 py-4 align-middle">
                            <span class="font-mono font-bold text-gray-900 text-sm">{{ $item['id_bahan_baku'] ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-4 align-middle">
                            <p class="font-semibold text-gray-900 text-sm">{{ $item['nama_bahan'] }}</p>
                        </td>
                        <td class="px-4 py-4 align-middle text-sm text-gray-600 font-medium">
                            {{ $item['kategori'] }}
                        </td>
                        <td class="px-4 py-4 align-middle text-center text-sm text-gray-600 font-medium">
                            {{ $item['satuan'] }}
                        </td>
                        
                        @if($tab === 'harian')
                            <td class="px-4 py-4 align-middle text-right font-bold text-gray-900 tabular-nums">
                                {{ \App\Helpers\UnitHelper::formatQuantity($item['stok_saat_ini'], $item['satuan']) }}
                            </td>
                            <td class="px-4 py-4 align-middle text-right font-semibold text-gray-600 tabular-nums">
                                {{ \App\Helpers\UnitHelper::formatQuantity($item['stok_minimum'], $item['satuan']) }}
                            </td>
                            <td class="px-4 py-4 align-middle text-center">
                                @php
                                    $status = $item['status'];
                                    $badgeColor = $status == 'Aman' ? 'success' : ($status == 'Menipis' ? 'warning' : 'danger');
                                @endphp
                                <x-ui.badge :color="$badgeColor" size="sm">{{ $status }}</x-ui.badge>
                            </td>
                        @else
                            <td class="px-4 py-4 align-middle text-right font-bold {{ $item['stok_saat_ini'] > 0 ? 'text-emerald-700' : 'text-gray-500' }} tabular-nums">
                                {{ \App\Helpers\UnitHelper::formatQuantity($item['stok_saat_ini'], $item['satuan']) }}
                            </td>
                            <td class="px-4 py-4 align-middle text-center">
                                @php
                                    $status = $item['status'];
                                    $badgeColor = $status == 'Tersedia' ? 'success' : 'secondary';
                                @endphp
                                <x-ui.badge :color="$badgeColor" size="sm">{{ $status }}</x-ui.badge>
                            </td>
                        @endif

                        <td class="px-4 py-4 align-middle text-right">
                            <button type="button" onclick="openDetailDrawer('{{ $item['id'] }}', '{{ $tab }}')" class="inline-flex items-center gap-1 text-xs font-bold text-gray-700 bg-white hover:bg-gray-50 border border-gray-200 rounded-lg px-3 py-1.5 transition-colors shadow-2xs">
                                <x-heroicon-o-clock class="w-3.5 h-3.5 text-gray-500" />
                                Riwayat
                            </button>
                        </td>
                    </x-ui.table.row>
                    @empty
                    <tr>
                        <td colspan="{{ $tab === 'harian' ? 8 : 7 }}" class="px-4 py-12 text-center text-gray-500 font-medium">
                            Data persediaan {{ $tab === 'harian' ? 'stok harian' : 'stok katering' }} belum tersedia.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </x-ui.table>
        </x-ui.data-table>

    </div>
</div>

{{-- DRAWER/MODAL: RIWAYAT KARTU STOK --}}
<div id="drawerDetail" class="fixed inset-0 z-[100] hidden">
    <div id="drawerDetailOverlay" class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm opacity-0 transition-opacity duration-300" onclick="closeDetailDrawer()"></div>
    <div id="drawerDetailPanel" class="absolute right-0 top-0 h-full w-full max-w-xl bg-white shadow-2xl flex flex-col translate-x-full transition-transform duration-300">
        <div id="drawerDetailContent" class="flex-1 overflow-y-auto">
            <div class="flex items-center justify-center h-full">
                <svg class="animate-spin h-8 w-8 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<script>
    function openDetailDrawer(id, jenis = '{{ $tab }}') {
        const drawer = document.getElementById('drawerDetail');
        const overlay = document.getElementById('drawerDetailOverlay');
        const panel = document.getElementById('drawerDetailPanel');
        const content = document.getElementById('drawerDetailContent');

        content.innerHTML = `
            <div class="flex items-center justify-center h-full">
                <svg class="animate-spin h-8 w-8 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        `;

        drawer.classList.remove('hidden');
        drawer.style.display = 'block';

        requestAnimationFrame(() => {
            panel.classList.remove('translate-x-full');
            panel.classList.add('translate-x-0');
            overlay.classList.remove('opacity-0');
            overlay.classList.add('opacity-100');
        });

        fetch(`/laporan/persediaan/detail/${id}?jenis=${encodeURIComponent(jenis)}`)
            .then(res => res.text())
            .then(html => { content.innerHTML = html; })
            .catch(err => {
                content.innerHTML = '<div class="p-6 text-center text-red-500 font-semibold">Gagal memuat riwayat stok.</div>';
            });
    }

    function closeDetailDrawer() {
        const drawer = document.getElementById('drawerDetail');
        const overlay = document.getElementById('drawerDetailOverlay');
        const panel = document.getElementById('drawerDetailPanel');

        panel.classList.remove('translate-x-0');
        panel.classList.add('translate-x-full');
        overlay.classList.remove('opacity-100');
        overlay.classList.add('opacity-0');

        setTimeout(() => {
            drawer.classList.add('hidden');
            drawer.style.display = '';
        }, 300);
    }
</script>
@endsection
