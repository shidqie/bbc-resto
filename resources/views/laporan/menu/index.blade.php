@extends('layouts.pos')

@section('title', 'Laporan Menu Terlaris')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800">
    <div class="w-full p-6 space-y-5">

        {{-- PAGE HEADER --}}
        <x-ui.page-header title="Laporan Menu Terlaris" subtitle="Analisis menu dengan penjualan tertinggi pada periode yang dipilih">
            <x-slot:actions>
                <a href="{{ route('laporan.menu-terlaris.cetak', ['start_date' => $startDate, 'end_date' => $endDate]) }}" target="_blank"
                   class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-red-600 rounded-lg px-3 py-2 hover:bg-red-700 transition-colors">
                    <x-heroicon-o-document class="w-5 h-5" />
                    Cetak PDF
                </a>
            </x-slot:actions>
        </x-ui.page-header>

        {{-- Stat Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white rounded-xl border border-gray-200 px-5 py-4">
                <p class="text-sm font-medium text-gray-500">Total Pendapatan dari Menu</p>
                <p class="text-xl font-bold text-emerald-600 mt-1">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 px-5 py-4">
                <p class="text-sm font-medium text-gray-500">Total Item Terjual</p>
                <p class="text-xl font-bold text-gray-900 mt-1">{{ number_format($totalTerjual, 0, ',', '.') }} porsi</p>
            </div>
        </div>

        {{-- Filter --}}
        <div class="bg-white rounded-xl border border-gray-200 px-5 py-4">
            <form action="{{ route('laporan.menu-terlaris') }}" method="GET" class="flex flex-col md:flex-row gap-3 items-end">
                <div class="flex-1">
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Tanggal Mulai</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg outline-none focus:ring-1 focus:ring-gray-400 transition-all">
                </div>
                <div class="flex-1">
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Tanggal Akhir</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg outline-none focus:ring-1 focus:ring-gray-400 transition-all">
                </div>
                <button type="submit" class="px-5 py-2 bg-gray-900 text-white rounded-lg text-sm font-semibold hover:bg-gray-800 transition-colors shrink-0">Terapkan Filter</button>
            </form>
        </div>

        {{-- Top 3 Podium --}}
        @if($menuTerlaris->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($menuTerlaris->take(3) as $idx => $menu)
            @php
                $medals = ['🥇', '🥈', '🥉'];
                $colors = ['bg-yellow-50 border-yellow-200', 'bg-gray-50 border-gray-200', 'bg-amber-50 border-amber-200'];
                $textColors = ['text-yellow-700', 'text-gray-700', 'text-amber-700'];
            @endphp
            <div class="bg-white rounded-xl border {{ $colors[$idx] }} px-5 py-4 text-center">
                <div class="text-3xl mb-2">{{ $medals[$idx] }}</div>
                <p class="font-bold text-gray-900 text-sm">{{ $menu->nama }}</p>
                <p class="text-2xl font-black {{ $textColors[$idx] }} mt-2">{{ number_format($menu->total_qty, 0, ',', '.') }}<span class="text-sm font-normal text-gray-500"> porsi</span></p>
                <p class="text-xs text-gray-500 mt-1">Rp {{ number_format($menu->total_pendapatan, 0, ',', '.') }}</p>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Table --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-sm font-semibold text-gray-500 uppercase tracking-wide">
                        <th class="px-4 py-3 text-left w-12">Rank</th>
                        <th class="px-4 py-3 text-left">Nama Menu</th>
                        <th class="px-4 py-3 text-right">Harga Satuan</th>
                        <th class="px-4 py-3 text-right">Total Terjual</th>
                        <th class="px-4 py-3 text-right">Total Pendapatan</th>
                        <th class="px-4 py-3 text-right">% dari Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($menuTerlaris as $i => $menu)
                    <tr class="hover:bg-gray-50/60 transition-colors {{ $i < 3 ? 'bg-yellow-50/20' : '' }}">
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold {{ $i === 0 ? 'bg-yellow-100 text-yellow-700' : ($i === 1 ? 'bg-gray-100 text-gray-600' : ($i === 2 ? 'bg-amber-100 text-amber-700' : 'bg-white border border-gray-200 text-gray-500')) }}">
                                {{ $i + 1 }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <p class="font-semibold text-gray-900">{{ $menu->nama }}</p>
                        </td>
                        <td class="px-4 py-3 text-right text-gray-600">Rp {{ number_format($menu->harga, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right font-bold text-gray-900">{{ number_format($menu->total_qty, 0, ',', '.') }} porsi</td>
                        <td class="px-4 py-3 text-right font-bold text-emerald-600">Rp {{ number_format($menu->total_pendapatan, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-sm text-gray-500">
                            @if($totalTerjual > 0)
                                {{ number_format(($menu->total_qty / $totalTerjual) * 100, 1) }}%
                            @else
                                0%
                            @endif
                        </td>
                    </tr>
                    @empty
                    <x-empty-state icon="document-text" title="Belum ada data penjualan menu" message="Belum ada data penjualan menu pada periode ini." :colspan="6" />
                    @endforelse
                </tbody>
                @if($menuTerlaris->count() > 0)
                <tfoot>
                    <tr class="border-t-2 border-gray-200 bg-gray-50">
                        <td colspan="3" class="px-4 py-3 font-bold text-gray-900 text-right uppercase text-sm">Total</td>
                        <td class="px-4 py-3 text-right font-black text-gray-900">{{ number_format($totalTerjual, 0, ',', '.') }} porsi</td>
                        <td class="px-4 py-3 text-right font-black text-emerald-600">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right font-bold text-gray-900">100%</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

    </div>
</div>
@endsection
