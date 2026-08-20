@extends('layouts.pos')
@section('title', 'Penyesuaian Stok')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800">
    <div class="w-full p-6 space-y-5">

        {{-- PAGE HEADER --}}
        <x-ui.page-header
            title="Penyesuaian Stok"
            subtitle="Daftar Penyesuaian Stok Bila mana terjadi bahan baku busuk/rusak atau tidak sesuai dengn aktual "
            :breadcrumbs="['Persediaan', 'Penyesuaian Stok']">
            <x-slot:actions>
                <x-ui.button variant="primary" icon="plus" href="{{ route('penyesuaian-stok.create') }}">
                    Buat Penyesuaian
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        {{-- Table --}}
        <x-ui.data-table :paginator="$penyesuaians">
            <x-ui.table class="min-w-[950px]">
                <x-ui.table.header>
                    <th class="px-4 py-3.5 text-left w-12">No</th>
                    <th class="px-4 py-3.5 text-left">Tanggal</th>
                    <th class="px-4 py-3.5 text-left">Bahan Baku</th>
                    <th class="px-4 py-3.5 text-right">Stok Awal</th>
                    <th class="px-4 py-3.5 text-right">Fisik</th>
                    <th class="px-4 py-3.5 text-right">Selisih</th>
                    <th class="px-4 py-3.5 text-left">Alasan</th>
                </x-ui.table.header>
                <tbody class="divide-y divide-gray-100">
                    @forelse($penyesuaians as $adj)
                    <x-ui.table.row>
                        <td class="px-4 py-4 text-sm text-gray-500 font-medium align-middle">{{ $penyesuaians->firstItem() + $loop->index }}</td>
                        <td class="px-4 py-4 text-sm text-gray-600 font-medium">{{ \Carbon\Carbon::parse($adj->penyesuaian_stok->tanggal_penyesuaian)->format('d/m/Y') }}</td>
                        <td class="px-4 py-4">
                            <p class="font-semibold text-gray-900 leading-tight">{{ $adj->bahan_baku->nama_bahan ?? '-' }}</p>
                            @php
                                $isCat = strtolower($adj->jenis_persediaan ?? '') === 'catering';
                            @endphp
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium {{ $isCat ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800' }} mt-0.5">
                                {{ $isCat ? 'Katering' : 'Harian' }}
                            </span>
                        </td>
                        <td class="px-4 py-4 text-right font-medium text-gray-700">
                            {{ \App\Helpers\UnitHelper::formatQuantity($adj->jumlah_sistem, $adj->bahan_baku->satuan->singkatan ?? $adj->bahan_baku->satuan->nama_satuan ?? 'gram') }}
                        </td>
                        <td class="px-4 py-4 text-right font-semibold text-gray-900">
                            {{ \App\Helpers\UnitHelper::formatQuantity($adj->jumlah_fisik, $adj->bahan_baku->satuan->singkatan ?? $adj->bahan_baku->satuan->nama_satuan ?? 'gram') }}
                        </td>
                        <td class="px-4 py-4 text-right font-bold {{ $adj->jumlah_selisih < 0 ? 'text-red-600' : ($adj->jumlah_selisih > 0 ? 'text-emerald-600' : 'text-gray-500') }}">
                            {{ $adj->jumlah_selisih > 0 ? '+' : '' }}{{ \App\Helpers\UnitHelper::formatQuantity($adj->jumlah_selisih, $adj->bahan_baku->satuan->singkatan ?? $adj->bahan_baku->satuan->nama_satuan ?? 'gram') }}
                        </td>
                        <td class="px-4 py-4">
                            <span class="text-sm font-medium text-gray-800">{{ $adj->penyesuaian_stok->alasan ?? '-' }}</span>
                            @if(!empty($adj->catatan))
                                <p class="text-xs text-gray-400 italic mt-0.5">{{ $adj->catatan }}</p>
                            @endif
                        </td>
                    </x-ui.table.row>
                    @empty
                    <tr>
                        <td colspan="7">
                            <x-ui.empty-state icon="clipboard-document-list" title="Belum ada penyesuaian stok" message="Penyesuaian akan muncul di sini setelah dibuat." />
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </x-ui.table>
        </x-ui.data-table>

    </div>
</div>
@endsection
