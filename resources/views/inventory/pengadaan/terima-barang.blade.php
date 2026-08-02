@extends('layouts.pos')
@section('content')
<div class="flex-1 overflow-auto bg-gray-50/50">
    <div class="w-full p-6 space-y-6">
        <x-ui.page-header title="Penerimaan Bahan" :breadcrumbs="['Pengadaan', 'Penerimaan Bahan']">
            <x-slot:actions>
                <a href="{{ route('pengadaan.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 text-sm font-semibold rounded-3xl hover:bg-gray-200 transition">
                    &larr; Kembali ke Pengadaan
                </a>
            </x-slot:actions>
        </x-ui.page-header>
        <x-ui.alert />
        <div class="bg-white rounded-[2.25rem] border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-700">PO Menunggu Penerimaan</h2>
                <p class="text-xs text-gray-400 mt-0.5">Daftar Purchase Order yang telah disetujui dan menunggu konfirmasi penerimaan barang.</p>
            </div>
            @if($pengadaans->count())
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                        <tr>
                            <th class="px-5 py-3 text-left">No. PO</th>
                            <th class="px-5 py-3 text-left">Tanggal</th>
                            <th class="px-5 py-3 text-left">Pemasok</th>
                            <th class="px-5 py-3 text-left">Jenis</th>
                            <th class="px-5 py-3 text-left">Bahan (Jumlah Item)</th>
                            <th class="px-5 py-3 text-left">Status</th>
                            <th class="px-5 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($pengadaans as $po)
                        <tr class="hover:bg-gray-50/80 transition-colors">
                            <td class="px-5 py-3.5">
                                <span class="font-mono text-xs font-semibold text-blue-600 bg-blue-50 px-2 py-1 rounded-2xl">{{ $po->nomor_pengadaan }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-gray-600 text-xs">{{ \Carbon\Carbon::parse($po->tanggal_pengadaan)->format('d M Y') }}</td>
                            <td class="px-5 py-3.5 font-medium text-gray-800">{{ $po->nama_pemasok ?? '-' }}</td>
                            <td class="px-5 py-3.5">
                                <span class="text-xs px-2 py-0.5 rounded-full {{ $po->jenis_pengadaan == 'CATERING' ? 'bg-purple-50 text-purple-700' : 'bg-orange-50 text-orange-700' }} font-semibold">
                                    {{ $po->jenis_pengadaan }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-gray-600">{{ $po->detail_pengadaan_bahan->count() }} item</td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700">
                                    {{ $po->status_pengadaan?->nama_status ?? '-' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <a href="{{ route('pengadaan.form-terima', $po->id) }}" class="text-xs px-3 py-1.5 bg-green-500 text-white rounded-2xl hover:bg-green-600 transition font-semibold">
                                    Proses Terima
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($pengadaans->hasPages())
            <div class="px-5 py-3 border-t border-gray-100">{{ $pengadaans->links() }}</div>
            @endif
            @else
            <div class="px-5 py-12 text-center">
                <p class="text-gray-400 text-sm">Tidak ada PO yang menunggu penerimaan saat ini.</p>
                <a href="{{ route('pengadaan.index') }}" class="mt-3 inline-block text-sm font-semibold text-blue-600 hover:underline">Lihat semua pengadaan &rarr;</a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
