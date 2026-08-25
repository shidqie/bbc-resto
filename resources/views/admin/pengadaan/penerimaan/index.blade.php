@extends('layouts.pos')
@section('title', 'Penerimaan Bahan Baku')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800 pb-10">
    <div class="w-full p-6 space-y-5">

        <x-ui.page-header
            title="Penerimaan Bahan Baku"
            subtitle="Kelola barang yang diterima berdasarkan Purchase Order."
            :breadcrumbs="['Pengadaan', 'Penerimaan Bahan Baku']">
            <x-slot:actions>
                <a href="{{ route('pengadaan.po.index', ['status' => 'menunggu_barang']) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#0D3024] hover:bg-[#0D3024]/90 text-white font-semibold text-sm rounded-lg shadow-sm transition-all duration-150">
                    <x-heroicon-o-inbox-arrow-down class="w-4 h-4" />
                    <span>Terima dari Purchase Order</span>
                </a>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.alert />

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
            <form action="{{ route('pengadaan.penerimaan.index') }}" method="GET" class="p-3.5 flex flex-col lg:flex-row lg:items-end gap-3">
                <div class="flex-1">
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Pencarian</label>
                    <x-search-input name="search" value="{{ request('search') }}" placeholder="Cari kode penerimaan / kode PO..." />
                </div>
                <x-ui.multi-select name="status" :options="$statuses" :selected="request('status')" label="Status" type="radio" />
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Nama Supplier</label>
                    <input type="text" name="supplier" value="{{ request('supplier') }}" placeholder="Cari nama supplier..." class="w-full border border-gray-200 text-gray-900 text-sm rounded-lg px-3 py-2 outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Dari Tanggal</label>
                    <input type="date" name="dari" value="{{ request('dari') }}" class="w-full border border-gray-200 text-gray-900 text-sm rounded-lg px-3 py-2 outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Sampai Tanggal</label>
                    <input type="date" name="sampai" value="{{ request('sampai') }}" class="w-full border border-gray-200 text-gray-900 text-sm rounded-lg px-3 py-2 outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                </div>
                <div class="flex items-end gap-2">
                    <x-ui.button type="submit" variant="primary" icon="magnifying-glass">Cari</x-ui.button>
                    @if(request()->hasAny(['search', 'supplier', 'dari', 'sampai']))
                        <x-ui.button href="{{ route('pengadaan.penerimaan.index') }}" variant="danger">Reset</x-ui.button>
                    @endif
                </div>
            </form>
        </div>

        <x-ui.data-table :paginator="$penerimaans">
            <x-ui.table class="min-w-[900px]">
                <x-ui.table.header>
                    <th class="px-4 py-3.5 text-left w-12">No</th>
                    <th class="px-4 py-3.5 text-left">Kode Penerimaan</th>
                    <th class="px-4 py-3.5 text-left">Tanggal</th>
                    <th class="px-4 py-3.5 text-left">Kode PO</th>
                    <th class="px-4 py-3.5 text-left">Nama Supplier</th>
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
                        <td class="px-4 py-4 align-middle font-medium text-gray-900 text-sm">{{ \Carbon\Carbon::parse($pnr->diterima_pada)->translatedFormat('d M Y H:i') }}</td>
                        <td class="px-4 py-4 align-middle">
                            <span class="font-mono font-bold text-gray-500 text-xs">{{ optional($pnr->purchase_order)->nomor_po ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-4 align-middle text-gray-700">{{ $pnr->supplier ?? '-' }}</td>
                        <td class="px-4 py-4 align-middle text-center font-bold text-gray-900">{{ $pnr->detail_penerimaan_bahan->count() }} <span class="text-xs font-normal text-gray-500">item</span></td>
                        <td class="px-4 py-4 align-middle text-center">
                            <x-ui.badge :color="$pnr->status == 'selesai' ? 'success' : 'primary'" size="sm">{{ $pnr->status_nama }}</x-ui.badge>
                        </td>
                        <td class="px-4 py-4 align-middle text-center">
                                <x-ui.action-button href="{{ route('pengadaan.penerimaan.show', $pnr->id) }}" title="Detail">
                                    <x-heroicon-o-eye class="w-4 h-4" />
                                </x-ui.action-button>
                        </td>
                    </x-ui.table.row>
                    @empty
                    <tr>
                        <td colspan="8">
                            <x-ui.empty-state icon="inbox-arrow-down" title="Belum ada penerimaan." message="Penerimaan dicatat dari Purchase Order yang masih bisa diterima." />
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </x-ui.table>
        </x-ui.data-table>

    </div>
</div>
@endsection