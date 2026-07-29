@extends('layouts.pos')

@section('content')
<div class="flex-1 overflow-auto bg-gray-50/50 text-gray-800 font-sans">
    <div class="p-4 md:p-6 lg:p-8 max-w-[800px] mx-auto space-y-6">
        
        {{-- Header --}}
        <x-ui.page-header 
            title="Form Penerimaan Bahan Baku" 
            subtitle="Konfirmasi jumlah aktual barang yang dibeli dan total biaya belanja"
            :breadcrumbs="['Pengadaan', 'Penerimaan']">
            <x-slot:actions>
                <x-ui.button href="{{ route('pengadaan.index') }}" variant="outline" icon="fa-arrow-left">Kembali</x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.alert />

        <div class="bg-white rounded-2xl border border-gray-200/80 p-5 shadow-2xs space-y-4">
            
            <div class="border-b border-gray-100 pb-4 mb-4">
                <h3 class="text-sm font-bold text-gray-900">Detail Permintaan Pembelian</h3>
                <div class="mt-2 grid grid-cols-2 gap-4 text-xs text-gray-600">
                    <div>
                        <p><span class="font-semibold">ID Pembelian:</span> {{ $pengadaan->nomor_pengadaan }}</p>
                        <p><span class="font-semibold">Tanggal:</span> {{ $pengadaan->tanggal_pengadaan->format('d/m/Y') }}</p>
                    </div>
                    <div>
                        <p><span class="font-semibold">Supplier:</span> {{ $pengadaan->asal_pembelian ?? '-' }}</p>
                        <p><span class="font-semibold">Pencatat:</span> {{ $pengadaan->user->name ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <form action="{{ route('pengadaan.proses-terima', $pengadaan->id) }}" method="POST" class="space-y-4">
                @csrf
                
                <h4 class="text-sm font-bold text-gray-900 mt-2">Daftar Bahan Baku</h4>
                <p class="text-xs text-gray-500 mb-2">Ubah "Jumlah Aktual" jika barang yang dibeli berbeda dengan permintaan awal.</p>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-gray-50 text-gray-500 uppercase tracking-wider border-b border-gray-100">
                                <th class="px-3 py-2 font-semibold">Bahan Baku</th>
                                <th class="px-3 py-2 font-semibold">Jumlah Diminta</th>
                                <th class="px-3 py-2 font-semibold">Jumlah Aktual (Diterima)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($pengadaan->details as $detail)
                            <tr>
                                <td class="px-3 py-2.5 font-medium text-gray-900">
                                    {{ $detail->bahanBaku->nama_bahan ?? 'Bahan Dihapus' }}
                                </td>
                                <td class="px-3 py-2.5 text-gray-600">
                                    {{ $detail->jumlah }} {{ $detail->satuan }}
                                </td>
                                <td class="px-3 py-2.5">
                                    <div class="flex items-center gap-1 w-32">
                                        <input type="number" name="jumlah_aktual[{{ $detail->id }}]" value="{{ $detail->jumlah }}" step="0.01" min="0" required class="w-full px-2 py-1.5 bg-gray-50 border border-gray-200 rounded-lg outline-none focus:border-[#0F2E23] text-xs font-bold text-[#0F2E23]">
                                        <span class="text-[10px] text-gray-500 font-bold">{{ $detail->satuan }}</span>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 pt-4 border-t border-gray-100 bg-gray-50/50 p-4 rounded-xl">
                    <label class="block text-sm font-bold text-gray-900 mb-1">Total Keseluruhan Belanja (Rp) <span class="text-red-500">*</span></label>
                    <p class="text-[11px] text-gray-500 mb-2">Masukkan total nota / pengeluaran uang untuk seluruh barang di atas secara global.</p>
                    <input type="number" name="total_belanja" placeholder="Misal: 150000" required min="0" class="w-full md:w-1/2 px-3 py-2 bg-white border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#0F2E23]/10 focus:border-[#0F2E23] outline-none font-bold text-gray-900 text-base">
                </div>

                <div class="pt-4 flex justify-end">
                    <button type="submit" class="px-6 py-2.5 text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl font-bold text-sm transition-all shadow-sm active:scale-95 flex items-center gap-2">
                        <i class="fa-solid fa-check"></i> Simpan Penerimaan & Tambah Stok
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
