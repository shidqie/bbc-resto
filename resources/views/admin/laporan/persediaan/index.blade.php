@extends('layouts.pos')
@section('title', 'Laporan Persediaan')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800 pb-10">
    <div class="w-full p-6 space-y-5">

        {{-- PAGE HEADER --}}
        <x-ui.page-header
            title="Laporan Persediaan"
            subtitle="Ringkasan dan detail status stok bahan baku baik untuk operasional harian maupun catering."
            :breadcrumbs="['Laporan', 'Persediaan']">
            <x-slot:actions>
                <div class="flex items-center gap-2">
                    <x-ui.button variant="secondary" icon="document-text" href="{{ route('laporan.persediaan.cetak-pdf', request()->all()) }}" target="_blank">
                        Export PDF
                    </x-ui.button>
                </div>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.alert />

        {{-- STAT CARDS --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-center">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Total Item Tercatat</span>
                <span class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_bahan']) }}</span>
            </div>
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-center">
                <span class="text-xs font-semibold text-emerald-600 uppercase tracking-wider mb-1">Stok Aman</span>
                <span class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_aman']) }}</span>
            </div>
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-center">
                <span class="text-xs font-semibold text-amber-600 uppercase tracking-wider mb-1">Stok Menipis</span>
                <span class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_menipis']) }}</span>
            </div>
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-center">
                <span class="text-xs font-semibold text-rose-600 uppercase tracking-wider mb-1">Stok Habis</span>
                <span class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_habis']) }}</span>
            </div>
        </div>

        {{-- Table with integrated toolbar --}}
        <x-ui.data-table :paginator="$paginatedBahan">
            <x-slot:toolbar>
                <form action="{{ route('laporan.persediaan') }}" method="GET" class="flex flex-col xl:flex-row items-start xl:items-center justify-between gap-4 w-full">
                    <div class="w-full xl:max-w-sm shrink-0">
                        <x-search-input name="search" value="{{ request('search') }}" placeholder="Cari nama bahan baku..." width="w-full" />
                    </div>
                    <div class="flex flex-wrap items-center gap-2 w-full xl:w-auto">
                        <x-ui.multi-select name="jenis_stok" :options="['harian' => 'Dine In & Nasi Box', 'catering' => 'Catering']" :selected="request('jenis_stok', [])" label="Jenis Stok" />
                        <x-ui.multi-select name="kategori_id" :options="$kategoris->pluck('nama_kategori', 'id')->toArray()" :selected="request('kategori_id', [])" label="Kategori" />
                        
                        <div class="flex items-center gap-2 shrink-0">
                            @if(request()->hasAny(['search', 'jenis_stok', 'kategori_id']))
                                <a href="{{ route('laporan.persediaan') }}" class="text-sm font-medium text-rose-500 hover:text-rose-700 px-3 py-2 transition-colors">Reset Filter</a>
                            @endif
                        </div>
                    </div>
                </form>
            </x-slot:toolbar>

            <x-ui.table class="min-w-[950px]">
                <x-ui.table.header>
                    <th class="px-4 py-3.5 text-left w-12">No</th>
                    <th class="px-4 py-3.5 text-left">Kode Bahan</th>
                    <th class="px-4 py-3.5 text-left">Nama Bahan</th>
                    <th class="px-4 py-3.5 text-left">Kategori</th>
                    <th class="px-4 py-3.5 text-left">Jenis Stok</th>
                    <th class="px-4 py-3.5 text-right">Stok Saat Ini</th>
                    <th class="px-4 py-3.5 text-left">Satuan</th>
                    <th class="px-4 py-3.5 text-center">Status</th>
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
                        <td class="px-4 py-4 align-middle">
                            <span class="text-sm text-gray-500">{{ $item['kategori'] ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-4 align-middle">
                            <span class="text-sm font-medium text-gray-700">{{ $item['jenis_stok'] }}</span>
                        </td>
                        <td class="px-4 py-4 align-middle text-right font-bold text-gray-900 tabular-nums">
                            {{ (float)$item['stok_saat_ini'] }}
                        </td>
                        <td class="px-4 py-4 align-middle">
                            <span class="text-sm text-gray-500">{{ $item['satuan'] ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-4 align-middle text-center">
                            @php
                                $status = $item['status'];
                                $badgeColor = $status == 'Aman' ? 'success' : ($status == 'Menipis' ? 'warning' : 'danger');
                            @endphp
                            <x-ui.badge :color="$badgeColor" size="sm" dot>{{ $status }}</x-ui.badge>
                        </td>
                        <td class="px-4 py-4 align-middle text-right">
                            <div class="flex items-center justify-end gap-2">
                                <x-ui.action-button onclick="openDetailDrawer('{{ $item['id'] }}')" title="Detail">
                                    <x-heroicon-o-eye class="w-4 h-4" />
                                </x-ui.action-button>
                                <x-ui.action-button onclick="window.showToast('info', 'Fitur Lihat Riwayat Stok belum tersedia sepenuhnya')" title="Lihat Riwayat Stok">
                                    <x-heroicon-o-clock class="w-4 h-4" />
                                </x-ui.action-button>
                            </div>
                        </td>
                    </x-ui.table.row>
                    @empty
                    <tr>
                        <td colspan="9">
                            <x-ui.empty-state icon="document-text" title="Belum ada data" message="Data akan muncul setelah transaksi tersedia." />
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </x-ui.table>
        </x-ui.data-table>

    </div>
</div>

{{-- DRAWER: DETAIL PERSEDIAAN --}}
<div id="drawerDetail" class="fixed inset-0 z-[100] hidden">
    <div id="drawerDetailOverlay" class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm opacity-0 transition-opacity duration-300" onclick="closeDetailDrawer()"></div>
    <div id="drawerDetailPanel" class="absolute right-0 top-0 h-full w-full max-w-md bg-white shadow-2xl flex flex-col translate-x-full transition-transform duration-300">
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
    function openDetailDrawer(id) {
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

        fetch(`/laporan/persediaan/detail/${id}`)
            .then(res => res.text())
            .then(html => { content.innerHTML = html; })
            .catch(err => {
                content.innerHTML = '<div class="p-6 text-center text-red-500">Gagal memuat detail laporan.</div>';
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
