@extends('layouts.pos')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800 font-sans">
    <div class="w-full px-4 md:px-6 py-6 space-y-5">

        {{-- PAGE HEADER --}}
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-lg font-bold text-gray-900">Kelola Komposisi Bahan</h1>
                <p class="text-xs text-gray-500 mt-0.5">Kelola resep atau Bill of Materials (BOM) untuk setiap menu.</p>
            </div>
        </div>

        <x-ui.alert />

        {{-- Filter & Search --}}
        <div class="flex flex-col sm:flex-row gap-2 items-start sm:items-center justify-between mb-3 shrink-0">
            <form action="{{ route('resep.index') }}" method="GET" class="flex items-center gap-2 w-full sm:w-auto">
                <div class="relative flex-1 sm:flex-none sm:w-56">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau kode…" class="w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-1 focus:ring-gray-400 focus:border-gray-400 outline-none bg-white">
                </div>
                <button type="submit" class="text-xs font-medium bg-white border border-gray-200 text-gray-600 rounded-lg px-3 py-2 hover:bg-gray-50 transition-colors shrink-0">Cari</button>
            </form>
            <div class="flex items-center gap-2">
                <span class="text-xs font-medium text-gray-500">Total Menu:</span>
                <span class="text-xs font-semibold text-gray-900 bg-gray-100 rounded-full px-2.5 py-0.5">{{ $menus->total() }}</span>
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-xs font-semibold text-gray-400 uppercase tracking-wide">
                        <th class="px-4 py-3 text-left w-12">No.</th>
                        <th class="px-4 py-3 text-left">Menu</th>
                        <th class="px-4 py-3 text-left">Kategori & Layanan</th>
                        <th class="px-4 py-3 text-left">Status Resep (BOM)</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($menus as $menu)
                    <tr class="hover:bg-gray-50/60 transition-colors group">
                        <td class="px-4 py-3 text-xs text-gray-500 font-medium align-middle">
                            {{ $loop->iteration }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                @if($menu->foto)
                                    <img src="{{ Storage::url($menu->foto) }}" class="w-9 h-9 rounded-lg object-cover border border-gray-100 shrink-0" alt="">
                                @else
                                    <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center shrink-0">
                                        <span class="text-xs font-bold text-gray-400">{{ substr($menu->nama, 0, 1) }}</span>
                                    </div>
                                @endif
                                <div>
                                    <p class="font-semibold text-gray-900 leading-tight">{{ $menu->nama }}</p>
                                    <p class="text-xs text-gray-400 font-mono mt-0.5">{{ $menu->kode_menu ?? 'MNU-'.str_pad($menu->id,2,'0',STR_PAD_LEFT) }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs text-gray-700 font-medium">{{ $menu->kategori->nama ?? '-' }}</span>
                            <span class="inline-block text-[11px] font-semibold px-2 py-0.5 rounded-md bg-gray-100 text-gray-600 ml-1 capitalize">{{ str_replace('_', ' ', $menu->jenis_menu) }}</span>
                        </td>
                        <td class="px-4 py-3">
                            @if($menu->resep_count > 0)
                                <span class="inline-block text-xs font-medium text-emerald-700 bg-emerald-50 rounded-full px-2.5 py-0.5">
                                    ✓ {{ $menu->resep_count }} Bahan baku
                                </span>
                            @else
                                <span class="inline-block text-xs font-medium text-amber-700 bg-amber-50 rounded-full px-2.5 py-0.5">
                                    • Belum Ada Resep
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1 opacity-70 group-hover:opacity-100 transition-opacity">
                                <a href="{{ route('resep.create', $menu->id) }}" class="p-1.5 rounded-md text-gray-500 hover:bg-blue-50 hover:text-blue-600 transition-colors" title="{{ $menu->resep_count > 0 ? 'Edit Resep' : 'Buat Resep' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-14 text-center text-gray-400">
                            <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            <p class="text-sm font-medium">Tidak ada menu yang ditemukan.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4 shrink-0">{{ $menus->links() }}</div>
    </div>
</div>
@endsection
