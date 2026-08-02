@extends('layouts.pos')
@section('title', 'Detail Penyesuaian Stok')

@section('content')
<div class="px-6 py-8 md:px-10 md:py-10">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
                <a href="{{ route('penyesuaian-stok.index') }}" class="hover:text-[#3B82F6] transition">Penyesuaian Stok</a>
                <span>/</span>
                <span>{{ $penyesuaian->nomor_penyesuaian }}</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-800 tracking-tight">{{ $penyesuaian->nomor_penyesuaian }}</h1>
        </div>
        <a href="{{ route('penyesuaian-stok.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 text-sm font-semibold rounded-3xl hover:bg-gray-200 transition">
            &larr; Kembali
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Info sidebar -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-[2.25rem] border border-gray-100 shadow-sm p-5 space-y-3">
                <h3 class="text-sm font-semibold text-gray-700 border-b border-gray-100 pb-3">Info Penyesuaian</h3>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Nomor</span>
                    <span class="font-mono font-semibold text-[#3B82F6]">{{ $penyesuaian->nomor_penyesuaian }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Tanggal</span>
                    <span class="font-medium">{{ \Carbon\Carbon::parse($penyesuaian->tanggal_penyesuaian)->format('d M Y H:i') }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Dibuat Oleh</span>
                    <span class="font-medium">{{ $penyesuaian->dibuat_oleh_pengguna->nama_lengkap ?? '-' }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Status</span>
                    <span class="font-bold text-xs px-2 py-1 rounded-full uppercase {{ $penyesuaian->status_penyesuaian == 'DISETUJUI' ? 'bg-green-50 text-green-700' : 'bg-yellow-50 text-yellow-700' }}">
                        {{ $penyesuaian->status_penyesuaian }}
                    </span>
                </div>
                <div class="pt-2 border-t border-gray-100">
                    <div class="text-xs text-gray-500 mb-1">Alasan / Keterangan</div>
                    <p class="text-sm text-gray-700">{{ $penyesuaian->alasan }}</p>
                </div>
            </div>
        </div>

        <!-- Detail items -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-[2.25rem] border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="text-sm font-semibold text-gray-700">Detail Penyesuaian ({{ $penyesuaian->detail_penyesuaian_stok->count() }} Bahan)</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-gray-100">
                                <th class="px-5 py-3 text-[11px] font-bold text-gray-500 uppercase tracking-wider">Bahan Baku</th>
                                <th class="px-5 py-3 text-[11px] font-bold text-gray-500 uppercase tracking-wider text-right">Stok Sistem</th>
                                <th class="px-5 py-3 text-[11px] font-bold text-gray-500 uppercase tracking-wider text-right">Stok Fisik</th>
                                <th class="px-5 py-3 text-[11px] font-bold text-gray-500 uppercase tracking-wider text-right">Selisih</th>
                                <th class="px-5 py-3 text-[11px] font-bold text-gray-500 uppercase tracking-wider">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($penyesuaian->detail_penyesuaian_stok as $detail)
                            <tr class="hover:bg-gray-50/40 transition-colors">
                                <td class="px-5 py-3">
                                    <div class="font-semibold text-sm text-gray-800">{{ $detail->bahan_baku->nama_bahan }}</div>
                                    <div class="text-xs text-gray-500">{{ $detail->satuan->nama_satuan ?? '-' }}</div>
                                </td>
                                <td class="px-5 py-3 text-right text-sm text-gray-700">{{ number_format($detail->jumlah_sistem, 2, ',', '.') }}</td>
                                <td class="px-5 py-3 text-right text-sm font-semibold text-gray-800">{{ number_format($detail->jumlah_fisik, 2, ',', '.') }}</td>
                                <td class="px-5 py-3 text-right">
                                    <span class="text-sm font-bold {{ $detail->jumlah_selisih > 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $detail->jumlah_selisih > 0 ? '+' : '' }}{{ number_format($detail->jumlah_selisih, 2, ',', '.') }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-sm text-gray-600">{{ $detail->catatan ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
