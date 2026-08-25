@extends('layouts.pos')
@section('title', 'Semua Permintaan')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800 pb-10">
    <div class="w-full p-6 space-y-5">

        <x-ui.page-header
            title="Semua Permintaan"
            subtitle="Kelola permintaan bahan baku operasional dan catering."
            :breadcrumbs="['Pengadaan', 'Semua Permintaan']">
            <x-slot:actions>
                <div x-data="{ openDropdown: false }" class="relative">
                    <button @click="openDropdown = !openDropdown" @click.away="openDropdown = false" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-primary rounded-lg px-3 py-2 hover:bg-primary/90 transition-colors">
                        <x-heroicon-o-plus class="w-4 h-4" />
                        Buat Permintaan
                        <x-heroicon-o-chevron-down class="w-4 h-4 ml-1 opacity-70" />
                    </button>
                        <a href="{{ route('pengadaan.harian.create') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 font-medium">Permintaan Nasi Box & Harian</a>
                        <a href="{{ route('pengadaan.catering.create') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 font-medium">Permintaan Katering</a>
                    </div>
                </div>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.alert />

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
            <form action="{{ route('pengadaan.permintaan.index') }}" method="GET" class="p-3.5 flex flex-col lg:flex-row lg:items-end gap-3">
                <div class="flex-1">
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Pencarian</label>
                    <x-search-input name="search" value="{{ request('search') }}" placeholder="Cari kode permintaan / sumber..." />
                </div>
                <x-ui.multi-select name="jenis" :options="['harian' => 'Harian', 'catering' => 'Catering']" :selected="request('jenis')" label="Jenis Permintaan" type="radio" />
                <x-ui.multi-select name="status" :options="$statuses->pluck('nama_status', 'id')->toArray()" :selected="request('status')" label="Status" type="radio" />
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Dari Tanggal</label>
                    <input type="date" name="dari" value="{{ request('dari') }}" class="w-full border border-gray-200 text-gray-900 text-sm rounded-lg px-3 py-2 outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Sampai Tanggal</label>
                    <input type="date" name="sampai" value="{{ request('sampai') }}" class="w-full border border-gray-200 text-gray-900 text-sm rounded-lg px-3 py-2 outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button type="submit" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-emerald-600 rounded-lg px-4 py-2 hover:bg-emerald-700 transition-colors">
                        <x-heroicon-o-funnel class="w-4 h-4" />
                        Terapkan Filter
                    </button>
                    @if(request()->hasAny(['search', 'jenis', 'status', 'dari', 'sampai']))
                        <x-ui.button href="{{ route('pengadaan.permintaan.index') }}" variant="danger" size="sm">Reset</x-ui.button>
                    @endif
                </div>
            </form>
        </div>

        <x-ui.data-table :paginator="$pengadaans">
            <x-ui.table class="min-w-[950px]">
                <x-ui.table.header>
                    <th class="px-4 py-3.5 text-left w-12">No</th>
                    <th class="px-4 py-3.5 text-left">Kode Permintaan</th>
                    <th class="px-4 py-3.5 text-left">Tanggal</th>
                    <th class="px-4 py-3.5 text-left">Jenis</th>
                    <th class="px-4 py-3.5 text-left">Sumber</th>
                    <th class="px-4 py-3.5 text-center">Total Bahan</th>
                    <th class="px-4 py-3.5 text-center">Status</th>
                    <th class="px-4 py-3.5 text-center">Aksi</th>
                </x-ui.table.header>
                <tbody class="divide-y divide-gray-100">
                    @forelse($pengadaans as $i => $p)
                    @php $kodeStatus = optional($p->status_pengadaan)->kode_status; @endphp
                    <x-ui.table.row>
                        <td class="px-4 py-4 text-sm text-gray-500 font-medium align-middle">{{ $pengadaans->firstItem() + $i }}</td>
                        <td class="px-4 py-4 align-middle">
                            <a href="{{ route('pengadaan.permintaan.show', $p->id) }}" class="font-mono font-bold text-gray-900 text-xs hover:text-emerald-600">{{ $p->id_pengadaan }}</a>
                        </td>
                        <td class="px-4 py-4 align-middle font-medium text-gray-900 text-sm">{{ \Carbon\Carbon::parse($p->tanggal_pengadaan)->translatedFormat('d M Y') }}</td>
                        <td class="px-4 py-4 align-middle">
                            @if($p->jenis_pengadaan == 'harian')
                                <x-ui.badge color="primary" size="sm">Harian</x-ui.badge>
                            @else
                                <x-ui.badge color="warning" size="sm">Catering</x-ui.badge>
                            @endif
                        </td>
                        <td class="px-4 py-4 align-middle">
                            @if($p->jenis_pengadaan == 'catering' && $p->pesanan)
                                <p class="text-xs text-gray-500 font-medium">Pesanan Catering</p>
                                <p class="font-bold text-gray-900 text-xs font-mono">{{ $p->pesanan->id_pesanan }}</p>
                            @else
                                <span class="text-xs text-gray-400 font-medium">Harian</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 align-middle text-center font-bold text-gray-900">{{ $p->detail_pengadaan_bahan->count() }} <span class="text-xs font-normal text-gray-500">item</span></td>
                        <td class="px-4 py-4 align-middle text-center">
                            <x-ui.badge :color="$p->status_warna" size="sm">{{ $p->status_nama }}</x-ui.badge>
                        </td>
                        <td class="px-4 py-4 align-middle">
                            <div class="flex items-center justify-center gap-1.5">
                                <x-ui.action-button href="{{ route('pengadaan.permintaan.show', $p->id) }}" title="Detail">
                                    <x-heroicon-o-eye class="w-4 h-4" />
                                </x-ui.action-button>
                                @if(! in_array($kodeStatus, ['selesai', 'dibatalkan']))
                                    <x-ui.button href="{{ route('pengadaan.po.create', $p->id) }}" variant="primary" size="sm">
                                        <x-heroicon-o-shopping-cart class="w-3 h-3 mr-1 inline" />
                                        Buat PO
                                    </x-ui.button>
                                @endif
                                @if(in_array($kodeStatus, ['draft', 'menunggu_pembelian']))
                                    <form id="form-batal-{{ $p->id }}" action="{{ route('pengadaan.permintaan.cancel', $p->id) }}" method="POST" class="inline">
                                        @csrf
                                    </form>
                                    <x-ui.action-button type="button" title="Batalkan" onclick="window.confirmDialog({ title: 'Batalkan Permintaan', name: '{{ $p->id_pengadaan }}', message: 'Permintaan yang dibatalkan tidak dapat diproses kembali.', formId: 'form-batal-{{ $p->id }}', confirmText: 'Batalkan', cancelText: 'Batal', type: 'warning' })" class="text-rose-600 hover:text-rose-700">
                                        <x-heroicon-o-x-mark class="w-4 h-4" />
                                    </x-ui.action-button>
                                @endif
                            </div>
                        </td>
                    </x-ui.table.row>
                    @empty
                    <tr>
                        <td colspan="8">
                            <x-ui.empty-state icon="clipboard-document-list" title="Belum ada permintaan bahan." message="Buat permintaan baru untuk kebutuhan harian atau Catering." />
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </x-ui.table>
        </x-ui.data-table>

    </div>
</div>
@endsection
