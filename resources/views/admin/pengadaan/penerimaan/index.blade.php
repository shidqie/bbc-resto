@extends('layouts.pos')
@section('title', 'Penerimaan Bahan Baku')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800 pb-10">
    <div class="w-full p-6 space-y-5">

        <x-ui.page-header
            title="Penerimaan Bahan Baku"
            subtitle="Kelola barang yang diterima berdasarkan Purchase Order."
            :breadcrumbs="['Pengadaan', 'Penerimaan Bahan Baku']">
        </x-ui.page-header>

        <x-ui.alert />

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
            <form action="{{ route('pengadaan.penerimaan.index') }}" method="GET" class="p-3.5 flex flex-col lg:flex-row lg:items-end gap-3">
                <div class="flex-1">
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Pencarian</label>
                    <x-search-input name="search" value="{{ request('search') }}" placeholder="Cari kode penerimaan / kode PO..." />
                </div>
                <x-ui.multi-select name="status" :options="$statuses" :selected="request('status')" label="Status" type="radio" />
                <div class="flex-1">
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Supplier/Toko</label>
                    <input type="text" name="supplier" value="{{ request('supplier') }}" placeholder="Cari supplier/toko..." class="w-full border border-gray-200 text-gray-900 text-sm rounded-lg px-3 py-2 outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                </div>
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
                    @if(request()->hasAny(['search', 'status', 'supplier', 'dari', 'sampai']))
                        <a href="{{ route('pengadaan.penerimaan.index') }}" class="text-xs font-medium text-red-500 hover:text-red-700 px-2 py-2 rounded-lg hover:bg-red-50 transition-colors">Reset</a>
                    @endif
                </div>
            </form>
        </div>

        <x-ui.data-table :paginator="$penerimaans">
            <x-ui.table class="min-w-[950px]">
                <x-ui.table.header>
                    <th class="px-4 py-3.5 text-left w-12">No</th>
                    <th class="px-4 py-3.5 text-left">Kode Penerimaan</th>
                    <th class="px-4 py-3.5 text-left">Tanggal</th>
                    <th class="px-4 py-3.5 text-left">Kode PO</th>
                    <th class="px-4 py-3.5 text-left">Supplier/Toko</th>
                    <th class="px-4 py-3.5 text-center">Jumlah Item</th>
                    <th class="px-4 py-3.5 text-center">Status</th>
                    <th class="px-4 py-3.5 text-center">Aksi</th>
                </x-ui.table.header>
                <tbody class="divide-y divide-gray-100">
                    @forelse($penerimaans as $i => $pnr)
                    <x-ui.table.row>
                        <td class="px-4 py-4 text-sm text-gray-500 font-medium align-middle">{{ $penerimaans->firstItem() + $i }}</td>
                        <td class="px-4 py-4 align-middle">
                            <a href="{{ route('pengadaan.penerimaan.show', $pnr->id) }}" class="font-mono font-bold text-gray-900 text-xs hover:text-emerald-600">{{ $pnr->nomor_penerimaan }}</a>
                        </td>
                        <td class="px-4 py-4 align-middle font-medium text-gray-900 text-sm">{{ \Carbon\Carbon::parse($pnr->diterima_pada)->format('d M Y H:i') }}</td>
                        <td class="px-4 py-4 align-middle">
                            <span class="font-mono font-bold text-gray-500 text-xs">{{ optional($pnr->purchase_order)->nomor_po ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-4 align-middle text-gray-700">{{ $pnr->supplier ?? '-' }}</td>
                        <td class="px-4 py-4 align-middle text-center font-bold text-gray-900">{{ $pnr->detail_penerimaan_bahan->count() }} <span class="text-xs font-normal text-gray-500">item</span></td>
                        <td class="px-4 py-4 align-middle text-center">
                            <x-ui.badge :color="$pnr->status == 'selesai' ? 'success' : 'primary'" size="sm">{{ $pnr->status_nama }}</x-ui.badge>
                        </td>
                        <td class="px-4 py-4 align-middle text-center">
                            <a href="{{ route('pengadaan.penerimaan.show', $pnr->id) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-white bg-emerald-600 rounded-lg px-2.5 py-1.5 hover:bg-emerald-700 transition-colors">
                                <x-heroicon-o-eye class="w-3 h-3" />
                                Detail
                            </a>
                        </td>
                    </x-ui.table.row>
                    @empty
                    <x-empty-state icon="inbox-arrow-down" title="Belum ada penerimaan." message="Penerimaan dicatat dari Purchase Order yang masih bisa diterima." :colspan="8" />
                    @endforelse
                </tbody>
            </x-ui.table>
        </x-ui.data-table>

    </div>
</div>
@endsection