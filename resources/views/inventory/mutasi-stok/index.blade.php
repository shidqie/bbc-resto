@extends('layouts.pos')

@section('title', 'Riwayat Mutasi Stok')

@section('content')
<div class="px-6 py-8 md:px-10 md:py-10">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Riwayat Mutasi Stok</h1>
            <p class="text-sm text-gray-500 mt-1">Lacak pergerakan keluar-masuk seluruh persediaan bahan baku</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">
        <div class="bg-white rounded-[2.25rem] border border-gray-100 shadow-sm p-6">
            <div class="text-sm font-semibold text-gray-500 mb-2">Total Transaksi</div>
            <div class="text-3xl font-bold text-gray-800">{{ $stats['total_transaksi'] }}</div>
        </div>
        <div class="bg-white rounded-[2.25rem] border border-gray-100 shadow-sm p-6">
            <div class="text-sm font-semibold text-gray-500 mb-2">Masuk Hari Ini</div>
            <div class="text-3xl font-bold text-[#16A34A]">{{ $stats['masuk_hari_ini'] }}</div>
        </div>
        <div class="bg-white rounded-[2.25rem] border border-gray-100 shadow-sm p-6">
            <div class="text-sm font-semibold text-gray-500 mb-2">Keluar Hari Ini</div>
            <div class="text-3xl font-bold text-[#DC2626]">{{ $stats['keluar_hari_ini'] }}</div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-200 overflow-hidden">
        
        <!-- Filter Bar -->
        <div class="p-5 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
            <form action="{{ route('mutasi-stok.index') }}" method="GET" class="flex gap-3 items-center">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama bahan..." class="border border-gray-200 rounded-3xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] min-w-[200px]">
                
                <select name="jenis_mutasi_stok_id" class="border border-gray-200 rounded-3xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6]">
                    <option value="">Semua Mutasi</option>
                    <option value="1" {{ request('jenis_mutasi_stok_id') == '1' ? 'selected' : '' }}>Stok Masuk</option>
                    <option value="2" {{ request('jenis_mutasi_stok_id') == '2' ? 'selected' : '' }}>Stok Keluar</option>
                </select>

                <select name="jenis_stok" class="border border-gray-200 rounded-3xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6]">
                    <option value="">Semua Jenis Stok</option>
                    <option value="OPERASIONAL" {{ request('jenis_stok') == 'OPERASIONAL' ? 'selected' : '' }}>Operasional (Reguler)</option>
                    <option value="CATERING" {{ request('jenis_stok') == 'CATERING' ? 'selected' : '' }}>Catering</option>
                </select>

                <input type="date" name="tanggal" value="{{ request('tanggal') }}" class="border border-gray-200 rounded-3xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6]">

                <button type="submit" class="bg-white border border-gray-200 text-gray-700 px-4 py-2 rounded-3xl text-sm font-semibold hover:bg-gray-50 transition-colors">Filter</button>
                @if(request()->anyFilled(['search', 'jenis_mutasi_stok_id', 'jenis_stok', 'tanggal']))
                <a href="{{ route('mutasi-stok.index') }}" class="text-sm font-semibold text-gray-500 hover:text-gray-800 transition-colors">Reset</a>
                @endif
            </form>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-white border-b border-gray-100">
                        <th class="px-5 py-4 text-[11px] font-bold text-gray-500 uppercase tracking-wider">Tanggal & Waktu</th>
                        <th class="px-5 py-4 text-[11px] font-bold text-gray-500 uppercase tracking-wider">Bahan Baku</th>
                        <th class="px-5 py-4 text-[11px] font-bold text-gray-500 uppercase tracking-wider">Jenis Stok</th>
                        <th class="px-5 py-4 text-[11px] font-bold text-gray-500 uppercase tracking-wider text-right">Perubahan</th>
                        <th class="px-5 py-4 text-[11px] font-bold text-gray-500 uppercase tracking-wider">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($mutasiStoks as $mutasi)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-5 py-3.5">
                                <div class="text-sm font-medium text-gray-800">{{ \Carbon\Carbon::parse($mutasi->tanggal_mutasi)->format('d M Y') }}</div>
                                <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($mutasi->tanggal_mutasi)->format('H:i') }} WIB</div>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="text-sm font-semibold text-gray-800">{{ $mutasi->bahan_baku->nama_bahan }}</div>
                                <div class="text-xs text-gray-500">{{ $mutasi->bahan_baku->kategori_bahan_baku->nama_kategori ?? '-' }}</div>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="text-[10px] uppercase font-bold px-2 py-1 rounded-xl {{ $mutasi->jenis_stok == 'CATERING' ? 'bg-purple-50 text-purple-600' : 'bg-orange-50 text-orange-600' }}">
                                    {{ $mutasi->jenis_stok }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                @if($mutasi->jenis_mutasi_stok_id == 1)
                                    <span class="text-sm font-bold text-[#16A34A]">+{{ number_format($mutasi->jumlah, 2, ',', '.') }}</span>
                                @else
                                    <span class="text-sm font-bold text-[#DC2626]">-{{ number_format($mutasi->jumlah, 2, ',', '.') }}</span>
                                @endif
                                <span class="text-xs text-gray-500 ml-1">{{ $mutasi->bahan_baku->satuan->nama_satuan ?? '' }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="text-sm text-gray-700 max-w-xs truncate" title="{{ $mutasi->catatan }}">{{ $mutasi->catatan ?? '-' }}</div>
                                <div class="text-xs text-gray-400">Oleh: {{ $mutasi->dibuat_oleh_pengguna->nama_lengkap ?? 'Sistem' }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center">
                                <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                </div>
                                <div class="text-sm font-medium text-gray-800">Belum ada riwayat mutasi</div>
                                <div class="text-sm text-gray-500 mt-1">Data pergerakan stok akan muncul di sini.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($mutasiStoks->hasPages())
        <div class="px-5 py-4 border-t border-gray-100">
            {{ $mutasiStoks->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
