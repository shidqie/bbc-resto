@extends('layouts.pos')
@section('title', 'Ubah Permintaan')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800 pb-10">
    <div class="w-full p-6 space-y-5">

        <x-ui.page-header
            :title="'Ubah ' . $pengadaan->nomor_pengadaan"
            subtitle="Perbarui permintaan pembelian bahan baku yang masih dapat diubah."
            :breadcrumbs="['Pengadaan', 'Semua Permintaan', 'Ubah']">
        </x-ui.page-header>

        <x-ui.alert />

        <form action="{{ route('pengadaan.permintaan.update', $pengadaan->id) }}" method="POST" class="space-y-5">
            @csrf @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-5">
                <div class="lg:col-span-1 space-y-5">
                    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                        <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                            <h3 class="font-bold text-gray-900 text-sm tracking-tight">Informasi Permintaan</h3>
                        </div>
                        <div class="p-4 space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1">Kode Permintaan</label>
                                <input type="text" readonly value="{{ $pengadaan->nomor_pengadaan }}" class="w-full bg-gray-50 border border-gray-200 text-gray-600 text-sm rounded-lg px-3 py-2 font-mono font-bold cursor-not-allowed">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1">Tanggal Permintaan</label>
                                <input type="date" name="tanggal_pengadaan" value="{{ \Carbon\Carbon::parse($pengadaan->tanggal_pengadaan)->format('Y-m-d') }}" class="w-full border border-gray-200 text-gray-900 text-sm rounded-lg px-3 py-2 outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500" required>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1">Dibuat Oleh</label>
                                <input type="text" readonly value="{{ optional($pengadaan->diajukan_oleh_pengguna)->nama ?? '-' }}" class="w-full bg-gray-50 border border-gray-200 text-gray-600 text-sm rounded-lg px-3 py-2 cursor-not-allowed">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1">Catatan</label>
                                <textarea name="catatan" rows="3" class="w-full border border-gray-200 text-gray-900 text-sm rounded-lg px-3 py-2 outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">{{ $pengadaan->catatan }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-3">
                    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                        <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 flex justify-between items-center">
                            <h3 class="font-bold text-gray-900 text-sm tracking-tight">Daftar Bahan Baku</h3>
                            <button type="submit" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-emerald-600 rounded-lg px-3 py-2 hover:bg-emerald-700 transition-colors">
                                <x-heroicon-o-check class="w-4 h-4" />
                                Simpan Perubahan
                            </button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wide bg-white">
                                        <th class="px-4 py-3 text-left w-12">No</th>
                                        <th class="px-4 py-3 text-left">Bahan Baku</th>
                                        <th class="px-4 py-3 text-right">Stok Saat Ini</th>
                                        <th class="px-4 py-3 text-right w-40">Jumlah Permintaan</th>
                                        <th class="px-4 py-3 text-left">Satuan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    @foreach($pengadaan->detail_pengadaan_bahan as $i => $detail)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-500 font-medium align-middle">{{ $i + 1 }}</td>
                                        <td class="px-4 py-3 align-middle">
                                            <p class="font-bold text-gray-900 text-sm">{{ optional($detail->bahan_baku)->nama_bahan }}</p>
                                            <p class="text-xs text-gray-400 font-mono mt-0.5">{{ optional($detail->bahan_baku)->kode_bahan }}</p>
                                        </td>
                                        <td class="px-4 py-3 text-right font-medium text-rose-600 align-middle">{{ (float)$detail->stok_saat_ini }}</td>
                                        <td class="px-4 py-3 align-middle">
                                            <input type="text" name="jumlah[{{ $detail->id }}]" value="{{ (float)$detail->jumlah_dipesan }}" class="w-full text-right border border-gray-200 text-gray-900 text-sm rounded-lg px-2 py-1.5 outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                                        </td>
                                        <td class="px-4 py-3 align-middle">
                                            <span class="text-xs font-semibold text-gray-500 bg-gray-100 px-2 py-1 rounded-md">{{ optional($detail->satuan)->nama_satuan ?? '-' }}</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </form>

    </div>
</div>
@endsection
