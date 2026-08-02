@extends('layouts.pos')
@section('content')
<div class="flex-1 overflow-auto bg-gray-50/50">
    <div class="w-full p-6 space-y-6">
        <x-ui.page-header title="Detail Pengadaan: {{ $pengadaan->nomor_pengadaan }}" :breadcrumbs="['Pengadaan', 'Detail']">
            <x-slot:actions>
                <a href="{{ route('pengadaan.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 text-sm font-semibold rounded-3xl hover:bg-gray-200 transition">
                    &larr; Kembali
                </a>
                @if(in_array($pengadaan->status_pengadaan_id, [1, 2]))
                <a href="{{ route('pengadaan.form-terima', $pengadaan->id) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-green-500 text-white text-sm font-semibold rounded-3xl hover:bg-green-600 transition">
                    Proses Penerimaan
                </a>
                @endif
            </x-slot:actions>
        </x-ui.page-header>
        <x-ui.alert />
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-1 space-y-4">
                <div class="bg-white rounded-[2.25rem] border border-gray-100 shadow-sm p-5 space-y-3">
                    <h3 class="text-sm font-semibold text-gray-700 border-b pb-3">Info Pengadaan</h3>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">No. PO</span>
                        <span class="font-mono font-semibold text-blue-600">{{ $pengadaan->nomor_pengadaan }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Tanggal</span>
                        <span class="font-medium">{{ \Carbon\Carbon::parse($pengadaan->tanggal_pengadaan)->format('d M Y') }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Pemasok</span>
                        <span class="font-medium">{{ $pengadaan->nama_pemasok ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Jenis</span>
                        <span class="font-semibold {{ $pengadaan->jenis_pengadaan == 'CATERING' ? 'text-purple-600' : 'text-orange-600' }}">{{ $pengadaan->jenis_pengadaan }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Status</span>
                        @php $sid = $pengadaan->status_pengadaan_id; @endphp
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $sid==1 ? 'bg-amber-50 text-amber-700' : ($sid==2 ? 'bg-blue-50 text-blue-700' : ($sid==3 ? 'bg-red-50 text-red-700' : 'bg-green-50 text-green-700')) }}">
                            {{ $pengadaan->status_pengadaan?->nama_status }}
                        </span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Diajukan Oleh</span>
                        <span class="font-medium">{{ $pengadaan->diajukan_oleh_pengguna?->nama_lengkap ?? '-' }}</span>
                    </div>
                    @if($pengadaan->catatan)
                    <div class="mt-2 pt-3 border-t">
                        <p class="text-xs text-gray-500 mb-1">Catatan:</p>
                        <p class="text-sm text-gray-700">{{ $pengadaan->catatan }}</p>
                    </div>
                    @endif
                </div>
                <div class="bg-white rounded-[2.25rem] border border-gray-100 shadow-sm p-5">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 font-medium">Total Pengadaan</span>
                        <span class="text-lg font-bold text-gray-900">Rp {{ number_format($pengadaan->total_pengadaan, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
            <div class="lg:col-span-2">
                <div class="bg-white rounded-[2.25rem] border border-gray-100 shadow-sm overflow-hidden">
                    <div class="p-5 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-700">Daftar Bahan yang Dipesan</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                                <tr>
                                    <th class="px-5 py-3 text-left">Bahan Baku</th>
                                    <th class="px-5 py-3 text-right">Dipesan</th>
                                    <th class="px-5 py-3 text-right">Diterima</th>
                                    <th class="px-5 py-3 text-right">Harga/Satuan</th>
                                    <th class="px-5 py-3 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach($pengadaan->detail_pengadaan_bahan as $detail)
                                <tr class="hover:bg-gray-50/80 transition-colors">
                                    <td class="px-5 py-3.5 font-medium text-gray-800">
                                        {{ $detail->bahan_baku?->nama_bahan }}
                                        <span class="text-xs text-gray-400 ml-1">{{ $detail->bahan_baku?->satuan?->singkatan }}</span>
                                    </td>
                                    <td class="px-5 py-3.5 text-right font-semibold text-gray-700">{{ number_format($detail->jumlah_dipesan, 2, ',', '.') }}</td>
                                    <td class="px-5 py-3.5 text-right {{ $detail->jumlah_diterima > 0 ? 'text-green-600 font-semibold' : 'text-gray-400' }}">{{ number_format($detail->jumlah_diterima, 2, ',', '.') }}</td>
                                    <td class="px-5 py-3.5 text-right text-gray-600">Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}</td>
                                    <td class="px-5 py-3.5 text-right font-semibold text-gray-800">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
