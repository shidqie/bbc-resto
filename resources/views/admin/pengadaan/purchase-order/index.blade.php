@extends('layouts.pos')
@section('title', 'Purchase Order')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800 pb-10">
    <div class="w-full p-6 space-y-5">

        <x-ui.page-header
            title="Purchase Order"
            subtitle="Kelola pemesanan bahan baku ke supplier atau toko."
            :breadcrumbs="['Pengadaan', 'Purchase Order']">
        </x-ui.page-header>

        <x-ui.alert />

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
            <form action="{{ route('pengadaan.po.index') }}" method="GET" class="p-3.5 flex flex-col lg:flex-row lg:items-end gap-3">
                <div class="flex-1">
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Pencarian</label>
                    <x-search-input name="search" value="{{ request('search') }}" placeholder="Cari kode PO / kode permintaan..." />
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
                        <x-ui.button href="{{ route('pengadaan.po.index') }}" variant="danger" size="sm">Reset</x-ui.button>
                    @endif
                </div>
            </form>
        </div>

        <x-ui.data-table :paginator="$pos">
            <x-ui.table class="min-w-[950px]">
                <x-ui.table.header>
                    <th class="px-4 py-3.5 text-left w-12">No</th>
                    <th class="px-4 py-3.5 text-left">Kode PO</th>
                    <th class="px-4 py-3.5 text-left">Tanggal</th>
                    <th class="px-4 py-3.5 text-left">Supplier/Toko</th>
                    <th class="px-4 py-3.5 text-left">Kode Permintaan</th>
                    <th class="px-4 py-3.5 text-center">Jumlah Item</th>
                    <th class="px-4 py-3.5 text-center">Status</th>
                    <th class="px-4 py-3.5 text-center">Aksi</th>
                </x-ui.table.header>
                <tbody class="divide-y divide-gray-100">
                    @forelse($pos as $i => $po)
                    <x-ui.table.row>
                        <td class="px-4 py-4 text-sm text-gray-500 font-medium align-middle">{{ $pos->firstItem() + $i }}</td>
                        <td class="px-4 py-4 align-middle">
                            <a href="{{ route('pengadaan.po.show', $po->id) }}" class="font-mono font-bold text-gray-900 text-xs hover:text-emerald-600">{{ $po->nomor_po }}</a>
                        </td>
                        <td class="px-4 py-4 align-middle font-medium text-gray-900 text-sm">{{ \Carbon\Carbon::parse($po->tanggal_po)->format('d M Y') }}</td>
                        <td class="px-4 py-4 align-middle text-gray-700">{{ $po->supplier }}</td>
                        <td class="px-4 py-4 align-middle">
                            <span class="font-mono font-bold text-gray-500 text-xs">{{ optional($po->pengadaan_bahan)->id_pengadaan ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-4 align-middle text-center font-bold text-gray-900">{{ $po->detail_purchase_order->count() }} <span class="text-xs font-normal text-gray-500">item</span></td>
                        <td class="px-4 py-4 align-middle text-center">
                            <x-ui.badge :color="$po->status_warna" size="sm">{{ $po->status_nama }}</x-ui.badge>
                        </td>
                        <td class="px-4 py-4 align-middle">
                            <div class="flex items-center justify-center gap-1.5">
                                <x-ui.action-button href="{{ route('pengadaan.po.show', $po->id) }}" title="Detail">
                                    <x-heroicon-o-eye class="w-4 h-4" />
                                </x-ui.action-button>
                                <x-ui.action-button href="{{ route('pengadaan.po.print', $po->id) }}" target="_blank" title="Cetak PO">
                                    <x-heroicon-o-printer class="w-4 h-4" />
                                </x-ui.action-button>
                                @if(app(\App\Services\PengadaanStatusService::class)->poMasihBisaDiterima($po))
                                    <x-ui.action-button href="{{ route('pengadaan.penerimaan.create', $po->id) }}" title="Terima Barang">
                                        <x-heroicon-o-inbox-arrow-down class="w-4 h-4" />
                                    </x-ui.action-button>
                                @endif
                            </div>
                        </td>
                    </x-ui.table.row>
                    @empty
                    <tr>
                        <td colspan="8">
                            <x-ui.empty-state icon="document-text" title="Belum ada purchase order." message="Buat PO dari halaman detail permintaan yang masih memiliki sisa kebutuhan." />
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </x-ui.table>
        </x-ui.data-table>

    </div>
</div>
@endsection