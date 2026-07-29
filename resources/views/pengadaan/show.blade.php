@extends('layouts.pos')

@section('content')
<div class="flex-1 overflow-auto bg-gray-50/50 text-gray-800 font-sans">
    <div class="p-4 md:p-6 lg:p-8 max-w-[900px] mx-auto space-y-6">
        
        <x-ui.page-header 
            title="Detail Pengadaan Bahan Baku" 
            :breadcrumbs="['Pengadaan', 'Detail', $pengadaan->nomor_pengadaan]">
            <x-slot:actions>
                <x-ui.button href="{{ route('pengadaan.index') }}" variant="outline" icon="fa-arrow-left">Kembali</x-ui.button>
                <x-ui.button href="{{ route('pengadaan.pdf', $pengadaan->id) }}" icon="fa-file-pdf" variant="primary">Download PDF</x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.alert />

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-xl font-extrabold text-gray-900 mb-1">Bukti Pengadaan Bahan Baku</h2>
                    <p class="text-sm text-gray-500 font-medium">Nomor PO: <span class="text-gray-900 font-mono">{{ $pengadaan->nomor_pengadaan }}</span></p>
                </div>
                <div class="text-left sm:text-right">
                    <p class="text-sm font-bold text-gray-900">{{ $pengadaan->tanggal_pengadaan->format('d F Y') }}</p>
                    <p class="text-xs text-gray-500 mt-1">Dicatat oleh: <span class="font-medium text-gray-700">{{ $pengadaan->user->name ?? '-' }}</span></p>
                </div>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6 border-b border-gray-100">
                <div>
                    <div class="text-xs text-gray-500 font-bold uppercase mb-1">Supplier</div>
                    <div class="font-bold text-gray-900 text-base">{{ $pengadaan->asal_pembelian ?: 'Tidak ditentukan' }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 font-bold uppercase mb-1">Catatan Tambahan</div>
                    <div class="text-sm text-gray-700">{{ $pengadaan->catatan ?: '-' }}</div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100 text-gray-500 uppercase tracking-wider text-xs font-semibold">
                            <th class="px-6 py-3 w-12 text-center">No.</th>
                            <th class="px-6 py-3">Nama Bahan Baku</th>
                            <th class="px-6 py-3 text-right">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($pengadaan->details as $index => $item)
                        <tr class="hover:bg-gray-50/30 transition-colors">
                            <td class="px-6 py-4 text-center text-gray-500 font-medium">{{ $index + 1 }}</td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-900">{{ $item->bahanBaku->nama_bahan ?? 'Unknown' }}</div>
                                <div class="text-xs text-gray-500">{{ $item->bahanBaku->kategoriBahan->nama_kategori ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 text-right font-medium text-gray-900">
                                {{ rtrim(rtrim(number_format($item->jumlah, 2, ',', '.'), '0'), ',') }} <span class="text-xs text-gray-500">{{ $item->satuan }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-6 py-8 text-center text-gray-400">Tidak ada item pengadaan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="bg-blue-50/50 p-4 text-center border-t border-gray-100">
                <p class="text-xs font-semibold text-blue-800">
                    <x-heroicon-o-information-circle class="w-4 h-4 inline-block align-text-bottom mr-1" /> 
                    Stok bahan baku telah otomatis ditambahkan ke sistem.
                </p>
            </div>
        </div>

    </div>
</div>
@endsection
