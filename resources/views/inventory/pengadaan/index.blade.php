{{-- Halaman: Daftar Pengadaan Bahan Baku --}}
@extends('layouts.pos')
@section('title', 'Pengadaan Bahan')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800">
    <div class="w-full p-6 space-y-5">

        {{-- Page Header --}}
        <x-ui.page-header
            title="Pengadaan Bahan"
            subtitle="Buat dan pantau Purchase Order bahan baku untuk kebutuhan operasional & catering."
            :breadcrumbs="['Persediaan', 'Pengadaan']">
            <x-slot:actions>
                <x-ui.button href="{{ route('pengadaan.create', ['tipe' => 'harian']) }}" variant="outline" icon="clipboard-document-list">
                    Pengadaan Harian
                </x-ui.button>
                <x-ui.button href="{{ route('pengadaan.create') }}" variant="primary" icon="plus">
                    Buat Pengadaan Baru
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.alert />

        {{-- Stat Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <x-ui.stat-card label="Total PO" :value="$stats['total']" icon="document-text" color="blue" />
            <x-ui.stat-card label="Total Nilai" :value="'Rp ' . number_format($stats['total_pengadaan'], 0, ',', '.')" icon="banknotes" color="green" />
        </div>

        {{-- Filter Bar --}}
        <div class="flex flex-col sm:flex-row gap-2 items-start sm:items-center mb-3">
            <form method="GET" action="{{ route('pengadaan.index') }}" class="flex items-center gap-2 w-full sm:w-auto flex-wrap">
                <x-search-input name="search" value="{{ request('search') }}" placeholder="Cari nomor pengadaan..." />
                <button type="submit" class="text-sm font-medium bg-white border border-gray-200 text-gray-600 rounded-lg px-3 py-2 hover:bg-gray-50 transition-colors shrink-0">Cari</button>
                @if(request()->filled('search'))
                    <a href="{{ route('pengadaan.index') }}" class="text-xs font-medium text-red-500 hover:text-red-700 px-2 py-2 rounded-lg hover:bg-red-50 transition-colors shrink-0">Reset</a>
                @endif
            </form>
            <div class="ml-auto">
                <a href="{{ route('pengadaan.terima-barang') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-600 bg-emerald-50 hover:bg-emerald-100 px-3 py-1.5 rounded-lg transition">
                    <x-heroicon-o-inbox-arrow-down class="w-3.5 h-3.5" />
                    Penerimaan Bahan
                </a>
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-sm font-semibold text-gray-500 uppercase tracking-wide">
                            <th class="px-4 py-3 text-left">No. PO</th>
                            <th class="px-4 py-3 text-left">Tanggal</th>
                            <th class="px-4 py-3 text-left">Pemasok</th>
                            <th class="px-4 py-3 text-left">Jenis</th>
                            <th class="px-4 py-3 text-right">Total</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($pengadaans as $po)
                        <tr class="hover:bg-gray-50/60 transition-colors group">
                            <td class="px-4 py-3">
                                <span class="font-mono text-xs font-semibold text-blue-600 bg-blue-50 px-2 py-1 rounded-lg">{{ $po->nomor_pengadaan }}</span>
                            </td>
                            <td class="px-4 py-3 text-gray-600 text-xs font-medium">{{ \Carbon\Carbon::parse($po->tanggal_pengadaan)->translatedFormat('d M Y') }}</td>
                            <td class="px-4 py-3 font-semibold text-gray-900">{{ $po->nama_pemasok ?? '-' }}</td>
                            <td class="px-4 py-3">
                                @php $jenisColor = $po->jenis_pengadaan == 'CATERING' ? 'bg-violet-50 text-violet-700' : 'bg-orange-50 text-orange-700'; @endphp
                                <span class="text-xs px-2 py-0.5 rounded-lg {{ $jenisColor }} font-semibold">{{ $po->jenis_pengadaan }}</span>
                            </td>
                            <td class="px-4 py-3 font-semibold text-gray-900 text-right">Rp {{ number_format($po->total_pengadaan, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">
                                @php $sid = $po->status_pengadaan_id; @endphp
                                @php $statusColor = $sid==1 ? 'bg-amber-50 text-amber-700' : ($sid==2 ? 'bg-blue-50 text-blue-700' : ($sid==3 ? 'bg-red-50 text-red-700' : 'bg-emerald-50 text-emerald-700')); @endphp
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-semibold {{ $statusColor }}">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                    {{ $po->status_pengadaan?->nama_status ?? '-' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('pengadaan.show', $po->id) }}" title="Detail" class="w-7 h-7 rounded-full flex items-center justify-center bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors">
                                        <x-heroicon-o-eye class="w-3 h-3" />
                                    </a>
                                    @if(in_array($po->status_pengadaan_id, [1, 2]))
                                    <a href="{{ route('pengadaan.form-terima', $po->id) }}" title="Terima Barang" class="w-7 h-7 rounded-full flex items-center justify-center bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition-colors">
                                        <x-heroicon-o-inbox-arrow-down class="w-3 h-3" />
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <x-empty-state icon="document-text" title="Belum ada pengadaan" message="Buat pengadaan baru untuk memulai." :colspan="7" />
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($pengadaans->hasPages())
            <div class="px-5 py-3 border-t border-gray-100">{{ $pengadaans->links() }}</div>
            @endif
        </div>

    </div>
</div>
@endsection
