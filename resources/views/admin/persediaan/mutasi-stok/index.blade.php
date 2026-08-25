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
        {{-- Table with integrated toolbar --}}
        <x-ui.data-table :paginator="$mutasiStoks">
            <x-slot:toolbar>
                <form action="{{ route('mutasi-stok.index') }}" method="GET" class="flex items-center gap-2 w-full flex-wrap">
                    <x-search-input name="search" value="{{ request('search') }}" placeholder="Cari nama bahan..." />
                    <x-ui.multi-select name="jenis_stok" :options="['' => 'Semua', 'harian' => 'Harian', 'catering' => 'Katering']" :selected="request('jenis_stok', '')" label="Jenis Stok" type="radio" />
                    <x-ui.multi-select name="jenis_mutasi_stok_id" :options="['' => 'Semua Mutasi', '1' => 'Stok Masuk', '2' => 'Stok Keluar']" :selected="request('jenis_mutasi_stok_id', '')" label="Aktivitas" type="radio" />
                    <div class="w-full xl:max-w-xs shrink-0">
                        <x-ui.input type="date" name="tanggal" value="{{ request('tanggal') }}" onchange="this.form.submit()" />
                    </div>
                    @if(request()->anyFilled(['search', 'jenis_mutasi_stok_id', 'jenis_stok', 'tanggal']))
                        <x-ui.button href="{{ route('mutasi-stok.index') }}" variant="danger" size="sm">Reset</x-ui.button>
                    @endif
                </form>
            </x-slot:toolbar>

            <x-ui.table class="min-w-[950px]">
                <x-ui.table.header>
                    <th class="px-4 py-3.5 text-left w-12">No</th>
                    <th class="px-4 py-3.5 text-left">Tanggal</th>
                    <th class="px-4 py-3.5 text-left">Bahan Baku</th>
                    <th class="px-4 py-3.5 text-center">Jenis Stok</th>
                    <th class="px-4 py-3.5 text-left">Aktivitas</th>
                    <th class="px-4 py-3.5 text-right">Masuk</th>
                    <th class="px-4 py-3.5 text-right">Keluar</th>
                    <th class="px-4 py-3.5 text-right">Stok Akhir</th>
                </x-ui.table.header>
                <tbody class="divide-y divide-gray-100">
                    @forelse($mutasiStoks as $mutasi)
                    <x-ui.table.row>
                        <td class="px-4 py-4 text-sm text-gray-500 font-medium align-middle">{{ $mutasiStoks->firstItem() + $loop->index }}</td>
                        <td class="px-4 py-4">
                            <p class="font-medium text-gray-900 text-sm">{{ \Carbon\Carbon::parse($mutasi->tanggal_mutasi)->translatedFormat('d M Y') }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ \Carbon\Carbon::parse($mutasi->tanggal_mutasi)->format('H:i') }} WIB</p>
                        </td>
                        <td class="px-4 py-4">
                            <p class="font-semibold text-gray-900 leading-tight">{{ $mutasi->bahan_baku->nama_bahan ?? '-' }}</p>
                        </td>
                        <td class="px-4 py-4 text-center">
                            @php
                                $isCat = strtolower($mutasi->jenis_persediaan ?? '') === 'catering';
                            @endphp
                            <x-ui.badge :color="$isCat ? 'warning' : 'info'" size="sm">
                                {{ $isCat ? 'Katering' : 'Harian' }}
                            </x-ui.badge>
                        </td>
                        <td class="px-4 py-4">
                            <p class="text-sm font-medium text-gray-800">{{ $mutasi->catatan ?? '-' }}</p>
                        </td>
                        <td class="px-4 py-4 text-right">
                            @if($mutasi->jenis_mutasi_stok_id == 1 || $mutasi->jumlah > 0)
                                <span class="font-bold text-emerald-600">+{{ \App\Helpers\UnitHelper::formatQuantity(abs($mutasi->jumlah), $mutasi->bahan_baku->satuan->singkatan ?? $mutasi->bahan_baku->satuan->nama_satuan ?? 'gram') }}</span>
                            @else
                                <span class="text-gray-400 text-sm">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-right">
                            @if($mutasi->jenis_mutasi_stok_id == 2 || $mutasi->jumlah < 0)
                                <span class="font-bold text-red-600">-{{ \App\Helpers\UnitHelper::formatQuantity(abs($mutasi->jumlah), $mutasi->bahan_baku->satuan->singkatan ?? $mutasi->bahan_baku->satuan->nama_satuan ?? 'gram') }}</span>
                            @else
                                <span class="text-gray-400 text-sm">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-right font-bold text-gray-900 font-mono">
                            {{ \App\Helpers\UnitHelper::formatQuantity($mutasi->stok_sesudah ?? 0, $mutasi->bahan_baku->satuan->singkatan ?? $mutasi->bahan_baku->satuan->nama_satuan ?? 'gram') }}
                        </td>
                    </x-ui.table.row>
                    @empty
                    <tr>
                        <td colspan="8">
                            <x-ui.empty-state icon="clock" title="Belum ada riwayat mutasi" message="Data pergerakan stok akan muncul di sini." />
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </x-ui.table>
        </x-ui.data-table>

    </div>
</div>
@endsection
