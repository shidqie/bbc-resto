@extends('layouts.pos')
@section('title', 'Purchase Order')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800 pb-10">
    @php
        $userRole = auth()->user()->peran->nama_peran ?? '';
        $isAdminOrPemilik = in_array($userRole, ['Admin', 'Super Admin', 'Pemilik', 'Manajer']);
        $isDapur = (auth()->user()->hasRole('Dapur', 'Tim Dapur') || in_array($userRole, ['Dapur', 'Tim Dapur', 'Koki'])) && !$isAdminOrPemilik;
    @endphp

    <div class="w-full p-6 space-y-5">

        <x-ui.page-header
            title="Purchase Order"
            subtitle="{{ $isDapur ? 'Daftar pemesanan bahan baku untuk penerimaan barang.' : 'Kelola pemesanan bahan baku ke supplier atau toko.' }}"
            :breadcrumbs="['Pengadaan', 'Purchase Order']">
            <x-slot:actions>
                @if(!$isDapur)
                <div class="relative inline-block text-left" x-data="{ open: false }" @click.outside="open = false">
                    <button @click="open = !open" type="button" class="inline-flex items-center gap-2 px-4 py-2 bg-[#0D3024] hover:bg-[#0D3024]/90 text-white font-semibold text-sm rounded-lg shadow-sm transition-all duration-150">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.5v15m7.5-7.5h-15"></path></svg>
                        <span>Buat Purchase Order</span>
                        <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>

                    <div x-show="open" x-cloak
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-52 rounded-xl bg-white shadow-xl border border-gray-100 py-1.5 z-50 text-sm">
                        
                        <a href="{{ route('pengadaan.po.create', ['tipe' => 'Harian']) }}" class="flex items-center gap-2.5 px-4 py-2.5 text-gray-700 hover:bg-gray-50 hover:text-[#0D3024] font-medium transition-colors">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <span>PO Nasi Box & Harian</span>
                        </a>
                        <a href="{{ route('pengadaan.po.create', ['tipe' => 'Catering']) }}" class="flex items-center gap-2.5 px-4 py-2.5 text-gray-700 hover:bg-gray-50 hover:text-[#0D3024] font-medium transition-colors">
                            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.701 2.701 0 01-1.5-.454M9 6v2m3-2v2m3-2v2M9 3h.01M12 3h.01M15 3h.01M21 21v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7h18z"></path></svg>
                            <span>PO Katering</span>
                        </a>
                    </div>
                </div>
                @endif
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.alert />

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm relative z-30">
            <form action="{{ route('pengadaan.po.index') }}" method="GET" class="p-3.5 flex flex-wrap items-center justify-start gap-3">
                <div class="w-full sm:w-64 lg:w-72">
                    <x-search-input name="search" value="{{ request('search') }}" placeholder="Cari kode PO / supplier..." />
                </div>
                <div class="relative z-40">
                    <x-ui.multi-select name="status" :options="$statuses" :selected="request('status')" label="Status PO" type="radio" />
                </div>
                <div>
                    <input type="date" name="dari" value="{{ request('dari') }}" class="border border-gray-200 text-gray-900 text-sm rounded-lg px-3 py-2 outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500" title="Dari Tanggal">
                </div>
                <div>
                    <input type="date" name="sampai" value="{{ request('sampai') }}" class="border border-gray-200 text-gray-900 text-sm rounded-lg px-3 py-2 outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500" title="Sampai Tanggal">
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button type="submit" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-emerald-600 rounded-lg px-4 py-2 hover:bg-emerald-700 transition-colors">
                        <x-heroicon-o-funnel class="w-4 h-4" />
                        Terapkan Filter
                    </button>
                    @if(request()->hasAny(['search', 'status', 'dari', 'sampai']))
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
                    <th class="px-4 py-3.5 text-left">Sumber</th>
                    <th class="px-4 py-3.5 text-left">Nama Supplier</th>
                    <th class="px-4 py-3.5 text-center">Jumlah Item</th>
                    <th class="px-4 py-3.5 text-center">Status</th>
                    <th class="px-4 py-3.5 text-center">Aksi</th>
                </x-ui.table.header>
                <tbody class="divide-y divide-gray-100">
                    @forelse($pos as $i => $po)
                    <x-ui.table.row>
                        <td class="px-4 py-4 text-sm text-gray-500 font-medium align-middle">{{ $pos->firstItem() + $i }}</td>
                        <td class="px-4 py-4 align-middle">
                            <span class="font-mono font-bold text-gray-900 text-xs">{{ $po->nomor_po }}</span>
                        </td>
                        <td class="px-4 py-4 align-middle text-gray-600 text-xs whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($po->tanggal_po)->translatedFormat('d M Y') }}
                        </td>
                        <td class="px-4 py-4 align-middle text-gray-700">
                            <span class="font-semibold">{{ $po->jenis_po == 'operasional' ? 'Harian' : 'Katering' }}</span>
                            @php
                                $sumberKode = $po->kode_pesanan_catering ?: optional(optional($po->pengadaan_bahan)->pesanan)->id_pesanan;
                            @endphp
                            @if($sumberKode)
                                <p class="text-[11px] font-mono text-emerald-700 font-bold mt-0.5">
                                    {{ $sumberKode }}
                                </p>
                            @endif
                        </td>
                        <td class="px-4 py-4 align-middle text-gray-700 font-medium">{{ $po->supplier }}</td>
                        <td class="px-4 py-4 align-middle text-center font-bold text-gray-900">{{ $po->detail_purchase_order->count() }} <span class="text-xs font-normal text-gray-500">item</span></td>
                        <td class="px-4 py-4 align-middle text-center">
                            <x-ui.badge :color="$po->status_warna" size="sm">{{ $po->status_nama }}</x-ui.badge>
                        </td>
                        <td class="px-4 py-4 align-middle text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('pengadaan.po.print', $po->id) }}" target="_blank" class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-50 border border-gray-200 rounded-lg transition-colors shadow-2xs gap-1" title="Cetak Surat PO ke Supplier">
                                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                    Cetak Surat PO
                                </a>
                                @if(app(\App\Services\PengadaanStatusService::class)->poMasihBisaDiterima($po))
                                    <a href="{{ route('pengadaan.penerimaan.create', $po->id) }}" class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-lg transition-colors">
                                        Terima Barang
                                    </a>
                                @else
                                    <a href="{{ route('pengadaan.po.show', $po->id) }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-lg transition-colors">
                                        Lihat Detail
                                    </a>
                                @endif
                            </div>
                        </td>
                    </x-ui.table.row>
                    @empty
                    <tr>
                        <td colspan="8">
                            <x-ui.empty-state icon="document-text" title="Belum ada purchase order." message="Gunakan tombol '+ Buat Purchase Order' di atas untuk membuat PO baru." />
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </x-ui.table>
        </x-ui.data-table>

    </div>
</div>
@endsection

@push('scripts')
@if(session('penerimaan_berhasil'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Bahan Baku Berhasil Diterima!',
                html: '<div class="text-left bg-emerald-50/70 p-4 rounded-xl border border-emerald-100 text-xs text-emerald-950 space-y-1.5">' +
                      '<p class="font-bold flex items-center gap-1.5 text-emerald-800"><svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Stok Bahan Otomatis Bertambah</p>' +
                      '<p class="text-gray-600">Penerimaan bahan baku telah berhasil dicatat dan stok persediaan telah diperbarui.</p>' +
                      '</div>',
                confirmButtonColor: '#059669',
                confirmButtonText: 'Selesai',
                customClass: {
                    popup: 'rounded-2xl',
                    confirmButton: 'rounded-xl px-6 py-2.5 font-bold text-sm shadow-xs'
                }
            });
        }
    });
</script>
@endif
@endpush