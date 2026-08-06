@extends('layouts.pos')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800 pb-10">
    <div class="w-full p-6 space-y-5">

        {{-- PAGE HEADER --}}
        <x-ui.page-header title="Kelola Komposisi Bahan" subtitle="Kelola resep atau Bill of Materials (BOM) untuk setiap menu." :breadcrumbs="['Persediaan', 'Kelola Komposisi Bahan']" />

        <x-ui.alert />

        {{-- Table with integrated toolbar --}}
        <x-ui.data-table :paginator="$menus">
            <x-slot:toolbar>
                <form action="{{ route('resep.index') }}" method="GET" class="flex flex-col xl:flex-row items-start xl:items-center justify-between gap-4 w-full">
                    <div class="w-full xl:max-w-sm shrink-0">
                        <x-search-input name="search" value="{{ request('search') }}" placeholder="Cari nama atau kode…" width="w-full" />
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <span class="text-sm font-medium text-gray-500">Total Menu:</span>
                        <span class="text-xs font-semibold text-gray-900 bg-gray-100 rounded-full px-2.5 py-0.5">{{ $menus->total() }}</span>
                        @if(request()->filled('search'))
                            <a href="{{ route('resep.index') }}" class="text-xs font-medium text-red-500 hover:text-red-700 px-2 py-2 rounded-lg hover:bg-red-50 transition-colors shrink-0">Reset</a>
                        @endif
                    </div>
                </form>
            </x-slot:toolbar>

            <x-ui.table class="min-w-[820px]">
                <x-ui.table.header>
                    <th class="px-4 py-3.5 text-left w-12">No.</th>
                    <th class="px-4 py-3.5 text-left">Menu</th>
                    <th class="px-4 py-3.5 text-left">Kategori & Layanan</th>
                    <th class="px-4 py-3.5 text-left">Status Resep (BOM)</th>
                    <th class="px-4 py-3.5 text-right">Aksi</th>
                </x-ui.table.header>
                <tbody class="divide-y divide-gray-100">
                    @forelse($menus as $menu)
                    <x-ui.table.row>
                        <td class="px-4 py-4 text-sm text-gray-500 font-medium align-middle">
                            {{ $loop->iteration }}
                        </td>
                        <td class="px-4 py-4 align-middle">
                            <div class="flex items-center gap-3">
                                @if($menu->foto)
                                    <img src="{{ Storage::url($menu->foto) }}" class="w-9 h-9 rounded-full object-cover border border-gray-100 shrink-0" alt="">
                                @else
                                    <div class="w-9 h-9 rounded-full bg-gray-100 flex items-center justify-center shrink-0">
                                        <span class="text-xs font-bold text-gray-400">{{ substr($menu->nama, 0, 1) }}</span>
                                    </div>
                                @endif
                                <div>
                                    <p class="font-semibold text-gray-900 leading-tight text-sm">{{ $menu->nama }}</p>
                                    <p class="text-xs text-gray-400 font-mono mt-0.5">{{ $menu->kode_menu ?? 'MNU-'.str_pad($menu->id,2,'0',STR_PAD_LEFT) }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4 align-middle">
                            <span class="text-xs text-gray-700 font-medium">{{ $menu->kategori->nama ?? '-' }}</span>
                            <span class="inline-block text-xs font-semibold px-2 py-0.5 rounded-xl bg-gray-100 text-gray-600 ml-1 capitalize">{{ str_replace('_', ' ', $menu->jenis_menu) }}</span>
                        </td>
                        <td class="px-4 py-4 align-middle">
                            @php
                                $rColor = $menu->resep_count > 0 ? 'success' : 'warning';
                            @endphp
                            <x-ui.badge :color="$rColor" size="sm">{{ $menu->resep_count > 0 ? '✓ '.$menu->resep_count.' Bahan baku' : '• Belum Ada Resep' }}</x-ui.badge>
                        </td>
                        <td class="px-4 py-4 align-middle text-right">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('resep.create', $menu->id) }}" class="p-1.5 rounded-xl text-gray-500 hover:bg-blue-50 hover:text-blue-600 transition-colors" title="{{ $menu->resep_count > 0 ? 'Edit Resep' : 'Buat Resep' }}">
                                    <x-heroicon-o-pencil-square class="w-4 h-4" />
                                </a>
                            </div>
                        </td>
                    </x-ui.table.row>
                    @empty
                    <x-empty-state icon="document-text" title="Belum ada data menu" message="Tidak ada menu yang ditemukan." :colspan="5" />
                    @endforelse
                </tbody>
            </x-ui.table>
        </x-ui.data-table>

    </div>
</div>
@endsection
