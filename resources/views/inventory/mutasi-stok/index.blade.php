{{-- Halaman: Riwayat Mutasi Stok --}}
@extends('layouts.pos')
@section('title', 'Riwayat Mutasi Stok')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800">
    <div class="w-full p-6 space-y-5">

        {{-- Page Header --}}
        <x-ui.page-header
            title="Riwayat Mutasi Stok"
            subtitle="Lacak pergerakan keluar-masuk seluruh persediaan bahan baku."
            :breadcrumbs="['Persediaan', 'Mutasi Stok']">
        </x-ui.page-header>

        {{-- Stat Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <x-ui.stat-card label="Total Transaksi" :value="$stats['total_transaksi']" icon="document-text" color="blue" />
            <x-ui.stat-card label="Masuk Hari Ini" :value="$stats['masuk_hari_ini']" icon="arrow-down-tray" color="green" />
            <x-ui.stat-card label="Keluar Hari Ini" :value="$stats['keluar_hari_ini']" icon="arrow-up-tray" color="red" />
        </div>

        {{-- Filter Bar --}}
        <div class="flex flex-col sm:flex-row gap-2 items-start sm:items-center">
            <form action="{{ route('mutasi-stok.index') }}" method="GET" class="flex items-center gap-2 w-full flex-wrap">
                <x-search-input name="search" value="{{ request('search') }}" placeholder="Cari nama bahan..." />

                <select name="jenis_mutasi_stok_id" class="border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-700 bg-white focus:outline-none focus:ring-1 focus:ring-gray-300 shrink-0" onchange="this.form.submit()">
                    <option value="">Semua Mutasi</option>
                    <option value="1" {{ request('jenis_mutasi_stok_id') == '1' ? 'selected' : '' }}>Stok Masuk</option>
                    <option value="2" {{ request('jenis_mutasi_stok_id') == '2' ? 'selected' : '' }}>Stok Keluar</option>
                </select>

                <select name="jenis_stok" class="border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-700 bg-white focus:outline-none focus:ring-1 focus:ring-gray-300 shrink-0" onchange="this.form.submit()">
                    <option value="">Semua Jenis Stok</option>
                    <option value="OPERASIONAL" {{ request('jenis_stok') == 'OPERASIONAL' ? 'selected' : '' }}>Operasional</option>
                    <option value="CATERING" {{ request('jenis_stok') == 'CATERING' ? 'selected' : '' }}>Catering</option>
                </select>

                <input type="date" name="tanggal" value="{{ request('tanggal') }}"
                       class="border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-700 bg-white focus:outline-none focus:ring-1 focus:ring-gray-300 shrink-0"
                       onchange="this.form.submit()">

                <button type="submit" class="text-sm font-medium bg-white border border-gray-200 text-gray-600 rounded-lg px-3 py-2 hover:bg-gray-50 transition-colors shrink-0">Filter</button>
                @if(request()->anyFilled(['search', 'jenis_mutasi_stok_id', 'jenis_stok', 'tanggal']))
                    <a href="{{ route('mutasi-stok.index') }}" class="text-xs font-medium text-red-500 hover:text-red-700 px-2 py-2 rounded-lg hover:bg-red-50 transition-colors shrink-0">Reset</a>
                @endif
            </form>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-sm font-semibold text-gray-500 uppercase tracking-wide">
                            <th class="px-4 py-3 text-left">Tanggal & Waktu</th>
                            <th class="px-4 py-3 text-left">Bahan Baku</th>
                            <th class="px-4 py-3 text-left">Jenis Stok</th>
                            <th class="px-4 py-3 text-right">Perubahan</th>
                            <th class="px-4 py-3 text-left">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($mutasiStoks as $mutasi)
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900 text-sm">{{ \Carbon\Carbon::parse($mutasi->tanggal_mutasi)->translatedFormat('d M Y') }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ \Carbon\Carbon::parse($mutasi->tanggal_mutasi)->format('H:i') }} WIB</p>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-semibold text-gray-900 leading-tight">{{ $mutasi->bahan_baku->nama_bahan }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $mutasi->bahan_baku->kategori_bahan_baku->nama_kategori ?? '-' }}</p>
                            </td>
                            <td class="px-4 py-3">
                                @php $jenisColor = $mutasi->jenis_stok == 'CATERING' ? 'bg-violet-50 text-violet-700' : 'bg-orange-50 text-orange-700'; @endphp
                                <span class="inline-block text-xs font-semibold px-2 py-0.5 rounded-lg {{ $jenisColor }}">{{ $mutasi->jenis_stok }}</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if($mutasi->jenis_mutasi_stok_id == 1)
                                    <span class="font-bold text-emerald-600">+{{ number_format($mutasi->jumlah, 2, ',', '.') }}</span>
                                @else
                                    <span class="font-bold text-red-600">-{{ number_format($mutasi->jumlah, 2, ',', '.') }}</span>
                                @endif
                                <span class="text-xs text-gray-400 ml-1">{{ $mutasi->bahan_baku->satuan->nama_satuan ?? '' }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-sm text-gray-700 max-w-xs truncate" title="{{ $mutasi->catatan }}">{{ $mutasi->catatan ?? '-' }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">Oleh: {{ $mutasi->dibuat_oleh_pengguna->nama_lengkap ?? 'Sistem' }}</p>
                            </td>
                        </tr>
                        @empty
                        <x-empty-state icon="arrows-right-left" title="Belum ada riwayat mutasi" message="Data pergerakan stok akan muncul di sini." :colspan="5" />
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($mutasiStoks->hasPages())
            <div class="px-5 py-3 border-t border-gray-100">{{ $mutasiStoks->links() }}</div>
            @endif
        </div>

    </div>
</div>
@endsection
