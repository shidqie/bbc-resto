{{-- 
    Halaman: Riwayat Pengadaan
    Deskripsi: Menampilkan daftar semua PO (Purchase Order) bahan baku.
--}}
@extends('layouts.pos')

@section('content')
<div class="flex-1 overflow-auto bg-gray-50/50 text-gray-800 font-sans">
    <div class="p-4 md:p-6 lg:p-8 max-w-[1200px] mx-auto space-y-6">
        
        {{-- Header --}}
        <x-ui.page-header title="Riwayat Pengadaan" subtitle="Kelola pembelian bahan baku dari supplier">
            <x-slot:actions>
                <x-ui.button href="{{ route('pengadaan.create') }}" icon="fa-plus">Buat Pengadaan Baru</x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        {{-- Alert --}}
        <x-ui.alert />

        {{-- Statistik --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-ui.stat-card label="Total Pengadaan" :value="$stats['total']" icon="fa-clipboard-list" color="blue" />
            <x-ui.stat-card label="Pending" :value="$stats['pending']" icon="fa-clock" color="orange" />
            <x-ui.stat-card label="Diterima" :value="$stats['diterima']" icon="fa-check-circle" color="green" />
            <x-ui.stat-card label="Dibatalkan" :value="$stats['dibatalkan']" icon="fa-times-circle" color="red" />
        </div>

        {{-- Tabel --}}
        <x-ui.data-table :paginator="$pengadaans">
            <x-slot:toolbar>
                <form action="{{ route('pengadaan.index') }}" method="GET" class="w-full sm:w-auto flex flex-col sm:flex-row gap-3">
                    <div class="relative min-w-[250px]">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Kode PO..." 
                               class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] outline-none text-sm transition-all bg-white">
                        <x-heroicon-o-magnifying-glass class="absolute left-3.5 top-2.5 text-gray-400 text-sm w-5 h-5 inline-block shrink-0" />
                    </div>
                    <select name="status" onchange="this.form.submit()" class="px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] outline-none text-sm bg-white min-w-[150px]">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="diterima" {{ request('status') == 'diterima' ? 'selected' : '' }}>Diterima</option>
                        <option value="dibatalkan" {{ request('status') == 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </form>
            </x-slot:toolbar>

            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-white text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100 sticky top-0 z-10">
                        <th class="px-6 py-4 font-semibold">Kode PO & Tanggal</th>
                        <th class="px-6 py-4 font-semibold">Supplier</th>
                        <th class="px-6 py-4 font-semibold">Total Biaya</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                        <th class="px-6 py-4 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 text-sm">
                    @forelse($pengadaans as $po)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900">{{ $po->kode_pengadaan }}</div>
                                <div class="text-xs text-gray-500">{{ $po->tanggal_pengadaan->format('d M Y') }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-gray-900 font-medium">{{ $po->supplier->nama_supplier ?? 'Tanpa Supplier' }}</div>
                                <div class="text-xs text-gray-500">Oleh: {{ $po->user->name ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-900">Rp {{ number_format($po->total_biaya, 0, ',', '.') }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @if($po->status == 'pending')
                                    <x-ui.badge color="warning" dot>Pending</x-ui.badge>
                                @elseif($po->status == 'diterima')
                                    <x-ui.badge color="success" dot>Diterima</x-ui.badge>
                                @else
                                    <x-ui.badge color="danger" dot>Dibatalkan</x-ui.badge>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('pengadaan.show', $po->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-blue-600 hover:bg-blue-50 transition-colors" title="Detail Pengadaan">
                                    <x-heroicon-o-eye class="w-5 h-5 inline-block shrink-0" />
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-ui.empty-state icon="fa-box-open" title="Tidak ada data pengadaan" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-ui.data-table>
    </div>
</div>
@endsection
