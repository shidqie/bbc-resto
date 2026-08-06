@extends('layouts.pos')
@section('title', 'Penerimaan Bahan Baku')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800 pb-10">
    <div class="w-full p-6 space-y-5">

        <x-ui.page-header
            title="Penerimaan Bahan Baku"
            subtitle="Catat bahan baku yang telah diterima dari supplier sehingga stok bertambah secara otomatis setelah verifikasi."
            :breadcrumbs="['Pengadaan', 'Penerimaan Bahan Baku']">
        </x-ui.page-header>

        <x-ui.alert />

        {{-- Filter --}}
        <form action="{{ route('pengadaan.penerimaan.index') }}" method="GET" class="bg-white p-3.5 rounded-xl border border-gray-200 flex items-center gap-2 flex-wrap">
            <x-search-input name="search" value="{{ request('search') }}" placeholder="Cari kode penerimaan / kode permintaan..." />
            <x-ui.multi-select name="status" :options="[
                'menunggu_penerimaan' => 'Menunggu Penerimaan',
                'sedang_diperiksa' => 'Sedang Diperiksa',
                'diterima_sebagian' => 'Diterima Sebagian',
                'selesai' => 'Selesai',
                'ditolak' => 'Ditolak',
            ]" :selected="request('status')" label="Status" type="radio" />
            @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('pengadaan.penerimaan.index') }}" class="text-xs font-medium text-red-500 hover:text-red-700 px-2 py-2 rounded-lg hover:bg-red-50 transition-colors shrink-0">Reset Filter</a>
            @endif
        </form>

        {{-- Menunggu Penerimaan --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm flex flex-col">
            <div class="px-4 py-3 border-b border-gray-100 bg-white flex justify-between items-center">
                <h3 class="font-bold text-gray-900 text-sm tracking-tight">Menunggu Penerimaan</h3>
                <span class="text-xs font-semibold text-amber-600 bg-amber-50 px-2 py-1 rounded-md">{{ $pending->count() }} permintaan</span>
            </div>
            <div class="flex-1 overflow-x-auto">
                <x-ui.table class="min-w-[950px]">
                    <x-ui.table.header>
                        <th class="px-4 py-3.5 text-left w-12">No</th>
                        <th class="px-4 py-3.5 text-left">Tanggal</th>
                        <th class="px-4 py-3.5 text-left">Kode Penerimaan</th>
                        <th class="px-4 py-3.5 text-left">Kode Permintaan</th>
                        <th class="px-4 py-3.5 text-left">Jenis</th>
                        <th class="px-4 py-3.5 text-center">Jumlah Jenis Bahan</th>
                        <th class="px-4 py-3.5 text-left">Status Penerimaan</th>
                        <th class="px-4 py-3.5 text-left">Diterima Oleh</th>
                        <th class="px-4 py-3.5 text-center">Aksi</th>
                    </x-ui.table.header>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($pending as $i => $p)
                        <x-ui.table.row>
                            <td class="px-4 py-4 text-sm text-gray-500 font-medium align-middle">{{ $i + 1 }}</td>
                            <td class="px-4 py-4 align-middle">
                                <p class="font-medium text-gray-900 text-sm">{{ \Carbon\Carbon::parse($p->tanggal_pengadaan)->format('d M Y') }}</p>
                            </td>
                            <td class="px-4 py-4 align-middle">
                                <span class="text-xs font-semibold text-gray-400 bg-gray-50 border border-dashed border-gray-300 px-2 py-1 rounded-md">Belum Dibuat</span>
                            </td>
                            <td class="px-4 py-4 align-middle">
                                <span class="font-mono font-bold text-gray-900 text-xs">{{ $p->nomor_pengadaan }}</span>
                            </td>
                            <td class="px-4 py-4 align-middle">
                                @if($p->jenis_pengadaan == 'harian')
                                    <x-ui.badge color="primary" size="sm">Harian</x-ui.badge>
                                @else
                                    <x-ui.badge color="warning" size="sm">Catering</x-ui.badge>
                                @endif
                            </td>
                            <td class="px-4 py-4 align-middle text-center font-bold text-gray-900">{{ $p->detail_pengadaan_bahan->count() }} bahan</td>
                            <td class="px-4 py-4 align-middle">
                                <x-ui.badge color="primary" size="sm">Menunggu Penerimaan</x-ui.badge>
                            </td>
                            <td class="px-4 py-4 align-middle text-sm text-gray-500">-</td>
                            <td class="px-4 py-4 align-middle text-center">
                                <a href="{{ route('pengadaan.penerimaan.create', ['permintaan' => $p->id]) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-white bg-emerald-600 rounded-lg px-3 py-1.5 hover:bg-emerald-700 transition-colors">
                                    <x-heroicon-o-arrow-down-tray class="w-3.5 h-3.5" />
                                    Terima Bahan
                                </a>
                            </td>
                        </x-ui.table.row>
                        @empty
                        <x-empty-state icon="check-circle" title="Tidak ada yang menunggu" message="Tidak ada permintaan yang menunggu penerimaan." :colspan="9" />
                        @endforelse
                    </tbody>
                </x-ui.table>
            </div>
        </div>

        {{-- Riwayat Penerimaan --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm flex flex-col">
            <div class="px-4 py-3 border-b border-gray-100 bg-white">
                <h3 class="font-bold text-gray-900 text-sm tracking-tight">Riwayat Penerimaan</h3>
            </div>
            <div class="flex-1 overflow-x-auto">
                <x-ui.table class="min-w-[950px]">
                    <x-ui.table.header>
                        <th class="px-4 py-3.5 text-left w-12">No</th>
                        <th class="px-4 py-3.5 text-left">Tanggal</th>
                        <th class="px-4 py-3.5 text-left">Kode Penerimaan</th>
                        <th class="px-4 py-3.5 text-left">Kode Permintaan</th>
                        <th class="px-4 py-3.5 text-left">Supplier</th>
                        <th class="px-4 py-3.5 text-center">Jumlah Jenis Bahan</th>
                        <th class="px-4 py-3.5 text-left">Status Penerimaan</th>
                        <th class="px-4 py-3.5 text-left">Diterima Oleh</th>
                        <th class="px-4 py-3.5 text-center">Aksi</th>
                    </x-ui.table.header>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($riwayat as $i => $p)
                        <x-ui.table.row>
                            <td class="px-4 py-4 text-sm text-gray-500 font-medium align-middle">{{ $i + 1 }}</td>
                            <td class="px-4 py-4 align-middle">
                                <p class="font-medium text-gray-900 text-sm">{{ \Carbon\Carbon::parse($p->diterima_pada)->format('d M Y') }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ \Carbon\Carbon::parse($p->diterima_pada)->format('H:i') }}</p>
                            </td>
                            <td class="px-4 py-4 align-middle">
                                <span class="font-mono font-bold text-gray-900 text-xs">{{ $p->nomor_penerimaan }}</span>
                            </td>
                            <td class="px-4 py-4 align-middle">
                                <span class="font-mono text-gray-600 text-xs">{{ $p->kode_permintaan ?? optional($p->pengadaan_bahan)->nomor_pengadaan ?? '-' }}</span>
                            </td>
                            <td class="px-4 py-4 align-middle text-sm text-gray-700">{{ $p->supplier ?? '-' }}</td>
                            <td class="px-4 py-4 align-middle text-center font-bold text-gray-900">{{ $p->detail_penerimaan_bahan->count() }} bahan</td>
                            <td class="px-4 py-4 align-middle">
                                @php
                                    $pColor = 'gray';
                                    if($p->status == 'menunggu_penerimaan') $pColor = 'primary';
                                    elseif($p->status == 'sedang_diperiksa') $pColor = 'warning';
                                    elseif($p->status == 'diterima_sebagian') $pColor = 'warning';
                                    elseif($p->status == 'selesai') $pColor = 'success';
                                    elseif($p->status == 'ditolak') $pColor = 'danger';
                                @endphp
                                <x-ui.badge :color="$pColor" size="sm">{{ $p->status_nama }}</x-ui.badge>
                            </td>
                            <td class="px-4 py-4 align-middle text-sm text-gray-700">{{ optional($p->diterima_oleh_pengguna)->nama ?? '-' }}</td>
                            <td class="px-4 py-4 align-middle text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('pengadaan.penerimaan.show', $p->id) }}" title="Detail" class="w-7 h-7 rounded-full flex items-center justify-center bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors">
                                        <x-heroicon-o-eye class="w-3 h-3" />
                                    </a>
                                    @if(in_array($p->status, ['menunggu_penerimaan', 'sedang_diperiksa']))
                                        <form id="form-verif-{{ $p->id }}" action="{{ route('pengadaan.penerimaan.verifikasi', $p->id) }}" method="POST" class="inline">
                                            @csrf
                                        </form>
                                        <button type="button" title="Verifikasi" onclick="window.confirmDialog({ title: 'Verifikasi Penerimaan', name: '{{ $p->nomor_penerimaan }}', message: 'Stok bahan baku akan bertambah setelah verifikasi.', formId: 'form-verif-{{ $p->id }}', confirmText: 'Verifikasi', cancelText: 'Batal' })" class="w-7 h-7 rounded-full flex items-center justify-center bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition-colors">
                                            <x-heroicon-o-check-badge class="w-3 h-3" />
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </x-ui.table.row>
                        @empty
                        <x-empty-state icon="archive-box" title="Belum ada penerimaan" message="Belum ada catatan penerimaan bahan baku." :colspan="9" />
                        @endforelse
                    </tbody>
                </x-ui.table>
            </div>
        </div>

    </div>
</div>
@endsection
