@extends('layouts.pos')
@section('title', 'Detail Penerimaan')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800 pb-10">
    <div class="w-full p-6 space-y-5">

        <x-ui.page-header
            :title="$penerimaan->nomor_penerimaan"
            subtitle="Detail penerimaan bahan baku."
            :breadcrumbs="['Pengadaan', 'Penerimaan Bahan Baku', 'Detail']">
            <x-slot:actions>
                @if(in_array($penerimaan->status, ['menunggu_penerimaan', 'sedang_diperiksa']))
                    <form id="form-verif" action="{{ route('pengadaan.penerimaan.verifikasi', $penerimaan->id) }}" method="POST">
                        @csrf
                    </form>
                    <button type="button" onclick="window.confirmDialog({ title: 'Verifikasi Penerimaan', name: '{{ $penerimaan->nomor_penerimaan }}', message: 'Stok bahan baku akan bertambah setelah verifikasi.', formId: 'form-verif', confirmText: 'Verifikasi', cancelText: 'Batal' })" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-emerald-600 rounded-lg px-3 py-2 hover:bg-emerald-700 transition-colors">
                        <x-heroicon-o-check-badge class="w-4 h-4" />
                        Verifikasi Penerimaan
                    </button>
                @endif
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.alert />

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
            <x-ui.card>
                <p class="text-xs font-semibold text-gray-500">Kode Penerimaan</p>
                <p class="font-mono font-bold text-gray-900 mt-1">{{ $penerimaan->nomor_penerimaan }}</p>
            </x-ui.card>
            <x-ui.card>
                <p class="text-xs font-semibold text-gray-500">Kode Permintaan</p>
                <p class="font-mono font-bold text-gray-900 mt-1">{{ $penerimaan->kode_permintaan ?? optional($penerimaan->pengadaan_bahan)->nomor_pengadaan ?? '-' }}</p>
            </x-ui.card>
            <x-ui.card>
                <p class="text-xs font-semibold text-gray-500">Tanggal Penerimaan</p>
                <p class="font-bold text-gray-900 mt-1">{{ \Carbon\Carbon::parse($penerimaan->diterima_pada)->format('d M Y H:i') }}</p>
            </x-ui.card>
            <x-ui.card>
                <p class="text-xs font-semibold text-gray-500">Supplier</p>
                <p class="font-bold text-gray-900 mt-1">{{ $penerimaan->supplier ?? '-' }}</p>
            </x-ui.card>
            <x-ui.card>
                <p class="text-xs font-semibold text-gray-500">Nomor Nota</p>
                <p class="font-bold text-gray-900 mt-1">{{ $penerimaan->nomor_nota ?? '-' }}</p>
            </x-ui.card>
            <x-ui.card>
                <p class="text-xs font-semibold text-gray-500">Status Penerimaan</p>
                @php
                    $pColor = 'gray';
                    if($penerimaan->status == 'menunggu_penerimaan') $pColor = 'primary';
                    elseif($penerimaan->status == 'sedang_diperiksa') $pColor = 'warning';
                    elseif($penerimaan->status == 'diterima_sebagian') $pColor = 'warning';
                    elseif($penerimaan->status == 'selesai') $pColor = 'success';
                    elseif($penerimaan->status == 'ditolak') $pColor = 'danger';
                @endphp
                <div class="mt-1"><x-ui.badge :color="$pColor" size="sm">{{ $penerimaan->status_nama }}</x-ui.badge></div>
            </x-ui.card>
            <x-ui.card>
                <p class="text-xs font-semibold text-gray-500">Diterima Oleh</p>
                <p class="font-bold text-gray-900 mt-1">{{ optional($penerimaan->diterima_oleh_pengguna)->nama ?? '-' }}</p>
            </x-ui.card>
            <x-ui.card>
                <p class="text-xs font-semibold text-gray-500">Diverifikasi Oleh</p>
                <p class="font-bold text-gray-900 mt-1">{{ optional($penerimaan->diverifikasi_oleh_pengguna)->nama ?? '-' }}</p>
                @if($penerimaan->waktu_verifikasi)
                    <p class="text-xs text-gray-400 mt-0.5">{{ \Carbon\Carbon::parse($penerimaan->waktu_verifikasi)->format('d M Y H:i') }}</p>
                @endif
            </x-ui.card>
        </div>

        @if($penerimaan->catatan)
        <x-ui.card>
            <p class="text-xs font-semibold text-gray-500">Catatan</p>
            <p class="text-sm text-gray-700 mt-1">{{ $penerimaan->catatan }}</p>
        </x-ui.card>
        @endif

        <x-ui.data-table :paginator="null">
            <x-ui.table>
                <x-ui.table.header>
                    <th class="px-4 py-3.5 text-left w-12">No</th>
                    <th class="px-4 py-3.5 text-left">Kode Bahan</th>
                    <th class="px-4 py-3.5 text-left">Nama Bahan Baku</th>
                    <th class="px-4 py-3.5 text-right">Jumlah Diminta</th>
                    <th class="px-4 py-3.5 text-right">Jumlah Diterima</th>
                    <th class="px-4 py-3.5 text-right">Kekurangan</th>
                    <th class="px-4 py-3.5 text-left">Satuan</th>
                    <th class="px-4 py-3.5 text-center">Kondisi</th>
                </x-ui.table.header>
                <tbody class="divide-y divide-gray-100">
                    @forelse($penerimaan->detail_penerimaan_bahan as $i => $d)
                    <x-ui.table.row>
                        <td class="px-4 py-4 text-sm text-gray-500 font-medium align-middle">{{ $i + 1 }}</td>
                        <td class="px-4 py-4 align-middle font-mono text-xs font-bold text-gray-900">{{ optional($d->bahan_baku)->kode_bahan ?? '-' }}</td>
                        <td class="px-4 py-4 align-middle font-medium text-gray-900 text-sm">{{ optional($d->bahan_baku)->nama_bahan ?? '-' }}</td>
                        <td class="px-4 py-4 align-middle text-right text-gray-700">{{ (float)$d->jumlah_diminta }}</td>
                        <td class="px-4 py-4 align-middle text-right font-bold text-gray-900">{{ (float)$d->jumlah_diterima }}</td>
                        <td class="px-4 py-4 align-middle text-right {{ (float)$d->jumlah_kurang > 0 ? 'text-rose-600 font-semibold' : 'text-gray-400' }}">{{ (float)$d->jumlah_kurang }}</td>
                        <td class="px-4 py-4 align-middle">
                            <span class="text-xs font-semibold text-gray-500 bg-gray-100 px-2 py-1 rounded-md">{{ optional($d->satuan)->nama_satuan ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-4 align-middle text-center">
                            @php
                                $cColor = 'success';
                                if($d->kondisi == 'Rusak') $cColor = 'danger';
                                elseif($d->kondisi == 'Kurang') $cColor = 'warning';
                            @endphp
                            <x-ui.badge :color="$cColor" size="sm">{{ $d->kondisi ?? 'Baik' }}</x-ui.badge>
                        </td>
                    </x-ui.table.row>
                    @empty
                    <x-empty-state icon="archive-box" title="Tidak ada data" message="Tidak ada detail penerimaan." :colspan="8" />
                    @endforelse
                </tbody>
            </x-ui.table>
        </x-ui.data-table>

    </div>
</div>
@endsection
