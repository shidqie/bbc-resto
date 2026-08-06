{{-- Halaman: Riwayat Mutasi Stok --}}
@extends('layouts.pos')
@section('title', 'Riwayat Mutasi Stok')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800">
    <div class="w-full p-6 space-y-5">

        {{-- Page Header --}}
        <x-ui.page-header
            title="Riwayat Stok"
            subtitle="Lacak pergerakan keluar-masuk seluruh persediaan bahan baku."
            :breadcrumbs="['Persediaan', 'Riwayat Stok']">
        </x-ui.page-header>

        {{-- Stat Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-center">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Total Transaksi</span>
                <span class="text-2xl font-bold text-gray-900">{{ $stats['total_transaksi'] }}</span>
            </div>
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-center">
                <span class="text-xs font-semibold text-emerald-600 uppercase tracking-wider mb-1">Masuk Hari Ini</span>
                <span class="text-2xl font-bold text-gray-900">{{ $stats['masuk_hari_ini'] }}</span>
            </div>
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-center">
                <span class="text-xs font-semibold text-red-600 uppercase tracking-wider mb-1">Keluar Hari Ini</span>
                <span class="text-2xl font-bold text-gray-900">{{ $stats['keluar_hari_ini'] }}</span>
            </div>
        </div>

        <x-ui.alert />

        {{-- Table with integrated toolbar --}}
        <x-ui.data-table :paginator="$mutasiStoks">
            <x-slot:toolbar>
                <form action="{{ route('mutasi-stok.index') }}" method="GET" class="flex items-center gap-2 w-full flex-wrap">
                    <x-search-input name="search" value="{{ request('search') }}" placeholder="Cari nama bahan..." />
                    <x-ui.multi-select name="jenis_mutasi_stok_id" :options="['1' => 'Stok Masuk', '2' => 'Stok Keluar']" :selected="request('jenis_mutasi_stok_id')" label="Mutasi" type="radio" />
                    <x-ui.multi-select name="jenis_stok" :options="['OPERASIONAL' => 'Operasional', 'CATERING' => 'Katering']" :selected="request('jenis_stok')" label="Jenis Stok" type="radio" />
                    <input type="date" name="tanggal" value="{{ request('tanggal') }}"
                           class="border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-700 bg-white shadow-sm outline-none transition-all focus:border-gray-400 focus:ring-1 focus:ring-gray-400 shrink-0"
                           onchange="this.form.submit()">
                    @if(request()->anyFilled(['search', 'jenis_mutasi_stok_id', 'jenis_stok', 'tanggal']))
                        <a href="{{ route('mutasi-stok.index') }}" class="text-xs font-medium text-red-500 hover:text-red-700 px-2 py-2 rounded-lg hover:bg-red-50 transition-colors shrink-0">Reset</a>
                    @endif
                </form>
            </x-slot:toolbar>

            <x-ui.table class="min-w-[900px]">
                <x-ui.table.header>
                    <th class="px-4 py-3.5 text-left w-12">No</th>
                    <th class="px-4 py-3.5 text-left">Tanggal</th>
                    <th class="px-4 py-3.5 text-left">Bahan Baku</th>
                    <th class="px-4 py-3.5 text-left">Jenis Transaksi</th>
                    <th class="px-4 py-3.5 text-right">Jumlah</th>
                    <th class="px-4 py-3.5 text-right">Stok Akhir</th>
                    <th class="px-4 py-3.5 text-left">Keterangan</th>
                </x-ui.table.header>
                <tbody class="divide-y divide-gray-100">
                    @forelse($mutasiStoks as $mutasi)
                    <x-ui.table.row>
                        <td class="px-4 py-4 text-sm text-gray-500 font-medium">{{ $mutasiStoks->firstItem() + $loop->index }}</td>
                        <td class="px-4 py-4">
                            <p class="font-medium text-gray-900 text-sm">{{ \Carbon\Carbon::parse($mutasi->tanggal_mutasi)->translatedFormat('d M Y') }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ \Carbon\Carbon::parse($mutasi->tanggal_mutasi)->format('H:i') }} WIB</p>
                        </td>
                        <td class="px-4 py-4">
                            <p class="font-semibold text-gray-900 leading-tight">{{ $mutasi->bahan_baku->nama_bahan ?? '-' }}</p>
                        </td>
                        <td class="px-4 py-4">
                            @php
                                $jenisColor = $mutasi->jenis_mutasi_stok_id == 1 ? 'success' : 'danger';
                                if(str_contains(strtolower($mutasi->catatan ?? ''), 'penyesuaian') || str_contains(strtolower($mutasi->catatan ?? ''), 'opname')) {
                                    $jenisColor = 'warning';
                                }
                            @endphp
                            <x-ui.badge :color="$jenisColor" size="sm">
                                @if(str_contains(strtolower($mutasi->catatan ?? ''), 'penyesuaian') || str_contains(strtolower($mutasi->catatan ?? ''), 'opname'))
                                    Penyesuaian
                                @elseif($mutasi->jenis_mutasi_stok_id == 1)
                                    Stok Masuk
                                @else
                                    Stok Keluar
                                @endif
                            </x-ui.badge>
                        </td>
                        <td class="px-4 py-4 text-right">
                            @if($mutasi->jenis_mutasi_stok_id == 1)
                                <span class="font-bold text-emerald-600">+{{ number_format($mutasi->jumlah, 2, ',', '.') }}</span>
                            @else
                                <span class="font-bold text-red-600">-{{ number_format($mutasi->jumlah, 2, ',', '.') }}</span>
                            @endif
                            <span class="text-xs text-gray-400 ml-1">{{ $mutasi->bahan_baku->satuan->nama_satuan ?? '' }}</span>
                        </td>
                        <td class="px-4 py-4 text-right font-medium text-gray-900">
                            {{ number_format($mutasi->stok_sesudah ?? 0, 2, ',', '.') }} <span class="text-xs text-gray-400">{{ $mutasi->bahan_baku->satuan->nama_satuan ?? '' }}</span>
                        </td>
                        <td class="px-4 py-4">
                            <p class="text-sm text-gray-700 max-w-xs truncate" title="{{ $mutasi->catatan }}">{{ $mutasi->catatan ?? '-' }}</p>
                        </td>
                    </x-ui.table.row>
                    @empty
                    <x-empty-state icon="clock" title="Belum ada riwayat mutasi" message="Data pergerakan stok akan muncul di sini." :colspan="7" />
                    @endforelse
                </tbody>
            </x-ui.table>
        </x-ui.data-table>

    </div>
</div>
@endsection
