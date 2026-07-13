{{-- 
    Halaman: Daftar Pesanan
    Deskripsi: Menampilkan semua data pesanan dengan filter status & jenis.
--}}
@extends('layouts.pos')

@section('content')
<div class="flex-1 overflow-auto bg-gray-50/50 text-gray-800 font-sans">
    <div class="p-4 md:p-6 lg:p-8 max-w-[1200px] mx-auto space-y-6">
        
        {{-- Header --}}
        <x-ui.page-header title="Daftar Pesanan" subtitle="Kelola seluruh transaksi pesanan pelanggan">
            <x-slot:actions>
                <x-ui.button href="{{ route('pesanan.create') }}" icon="fa-plus">Pesanan Baru</x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        {{-- Alert --}}
        <x-ui.alert />

        {{-- Statistik --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-ui.stat-card label="Pesanan Baru" :value="$stats['baru']" icon="fa-shopping-bag" color="blue" />
            <x-ui.stat-card label="Diproses" :value="$stats['diproses']" icon="fa-clock" color="orange" />
            <x-ui.stat-card label="Selesai" :value="$stats['selesai']" icon="fa-check-circle" color="green" />
        </div>

        {{-- Tabel --}}
        <x-ui.data-table :paginator="$pesanans">
            <x-slot:toolbar>
                <form action="{{ route('pesanan.index') }}" method="GET" class="w-full flex flex-col sm:flex-row gap-3">
                    <div class="relative min-w-[250px] flex-1 sm:flex-none">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari No Pesanan / Pelanggan..." 
                               class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] outline-none text-sm transition-all bg-white">
                        <i class="fas fa-search absolute left-3.5 top-2.5 text-gray-400 text-sm"></i>
                    </div>
                    <select name="jenis" onchange="this.form.submit()" class="px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] outline-none text-sm bg-white min-w-[150px]">
                        <option value="">Semua Jenis</option>
                        <option value="dine_in" {{ request('jenis') == 'dine_in' ? 'selected' : '' }}>Dine In</option>
                        <option value="take_away" {{ request('jenis') == 'take_away' ? 'selected' : '' }}>Take Away</option>
                        <option value="catering" {{ request('jenis') == 'catering' ? 'selected' : '' }}>Catering</option>
                        <option value="nasi_box" {{ request('jenis') == 'nasi_box' ? 'selected' : '' }}>Nasi Box</option>
                    </select>
                    <select name="status" onchange="this.form.submit()" class="px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] outline-none text-sm bg-white min-w-[150px]">
                        <option value="">Semua Status</option>
                        <option value="baru" {{ request('status') == 'baru' ? 'selected' : '' }}>Baru</option>
                        <option value="diproses" {{ request('status') == 'diproses' ? 'selected' : '' }}>Diproses</option>
                        <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                        <option value="dibatalkan" {{ request('status') == 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </form>
            </x-slot:toolbar>

            <table class="w-full text-left border-collapse min-w-[800px]">
                <thead>
                    <tr class="bg-white text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100 sticky top-0 z-10">
                        <th class="px-6 py-4 font-semibold">No. Pesanan</th>
                        <th class="px-6 py-4 font-semibold">Pelanggan</th>
                        <th class="px-6 py-4 font-semibold">Total Harga</th>
                        <th class="px-6 py-4 font-semibold">Pembayaran</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                        <th class="px-6 py-4 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 text-sm">
                    @forelse($pesanans as $p)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-900">{{ $p->no_pesanan }}</div>
                                <div class="text-xs text-gray-500">{{ $p->tanggal_pesanan->format('d M Y H:i') }}</div>
                                <div class="text-xs text-[#3B82F6] font-medium capitalize mt-0.5">{{ str_replace('_', ' ', $p->jenis_pesanan) }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-gray-900 font-medium">{{ $p->nama_pelanggan ?? 'Walk-in Customer' }}</div>
                                @if($p->no_meja)
                                    <div class="text-xs text-gray-500">Meja: {{ $p->no_meja }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-900">Rp {{ number_format($p->total_harga, 0, ',', '.') }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @if($p->status_pembayaran == 'lunas')
                                    <x-ui.badge color="success">Lunas</x-ui.badge>
                                @elseif($p->status_pembayaran == 'dp')
                                    <x-ui.badge color="primary">DP Terbayar</x-ui.badge>
                                @else
                                    <x-ui.badge color="danger">Belum Bayar</x-ui.badge>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($p->status_pesanan == 'baru')
                                    <x-ui.badge color="gray" dot>Baru</x-ui.badge>
                                @elseif($p->status_pesanan == 'diproses')
                                    <x-ui.badge color="warning" dot>Diproses</x-ui.badge>
                                @elseif($p->status_pesanan == 'selesai')
                                    <x-ui.badge color="success" dot>Selesai</x-ui.badge>
                                @else
                                    <x-ui.badge color="danger" dot>{{ ucfirst($p->status_pesanan) }}</x-ui.badge>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <x-ui.button href="{{ route('pesanan.show', $p->id) }}" size="sm">Detail</x-ui.button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-ui.empty-state icon="fa-receipt" title="Tidak ada data pesanan" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-ui.data-table>
    </div>
</div>
@endsection
