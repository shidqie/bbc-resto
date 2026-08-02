@extends('layouts.pos')
@section('content')
<div class="flex-1 overflow-auto bg-gray-50/50">
    <div class="w-full p-6 space-y-6">
        <x-ui.page-header title="Terima Barang: {{ $pengadaan->nomor_pengadaan }}" :breadcrumbs="['Pengadaan', 'Penerimaan Bahan', 'Proses Terima']">
            <x-slot:actions>
                <a href="{{ route('pengadaan.terima-barang') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 text-sm font-semibold rounded-3xl hover:bg-gray-200 transition">
                    &larr; Kembali
                </a>
            </x-slot:actions>
        </x-ui.page-header>
        <x-ui.alert />

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Info PO --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-[2.25rem] border border-gray-100 shadow-sm p-5 space-y-3 sticky top-6">
                    <h3 class="text-sm font-semibold text-gray-700 border-b pb-3">Info Pengadaan</h3>
                    <div class="flex justify-between text-sm"><span class="text-gray-500">No. PO</span><span class="font-mono font-semibold text-blue-600">{{ $pengadaan->nomor_pengadaan }}</span></div>
                    <div class="flex justify-between text-sm"><span class="text-gray-500">Pemasok</span><span class="font-medium">{{ $pengadaan->nama_pemasok ?? '-' }}</span></div>
                    <div class="flex justify-between text-sm"><span class="text-gray-500">Tanggal PO</span><span>{{ \Carbon\Carbon::parse($pengadaan->tanggal_pengadaan)->format('d M Y') }}</span></div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Jenis</span>
                        <span class="font-semibold {{ $pengadaan->jenis_pengadaan == 'CATERING' ? 'text-purple-600' : 'text-orange-600' }}">{{ $pengadaan->jenis_pengadaan }}</span>
                    </div>
                    <div class="mt-3 pt-3 border-t bg-amber-50 rounded-3xl p-3">
                        <p class="text-xs text-amber-700 font-semibold">⚠️ Penting</p>
                        <p class="text-xs text-amber-600 mt-1">Setelah disimpan, stok bahan baku akan otomatis bertambah sesuai jumlah yang diterima.</p>
                    </div>
                </div>
            </div>

            {{-- Form Terima --}}
            <div class="lg:col-span-2">
                <form action="{{ route('pengadaan.proses-terima', $pengadaan->id) }}" method="POST">
                    @csrf
                    <div class="bg-white rounded-[2.25rem] border border-gray-100 shadow-sm overflow-hidden">
                        <div class="p-5 border-b border-gray-100">
                            <h3 class="text-sm font-semibold text-gray-700">Konfirmasi Jumlah Diterima & Harga Aktual</h3>
                            <p class="text-xs text-gray-400 mt-0.5">Isi jumlah yang benar-benar diterima. Jika ada selisih, masukkan jumlah aktual yang datang.</p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                                    <tr>
                                        <th class="px-5 py-3 text-left">Bahan Baku</th>
                                        <th class="px-5 py-3 text-right">Dipesan</th>
                                        <th class="px-5 py-3 text-right">Jumlah Diterima *</th>
                                        <th class="px-5 py-3 text-right">Harga/Satuan (Rp)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    @foreach($pengadaan->detail_pengadaan_bahan as $detail)
                                    <tr class="hover:bg-gray-50/50 transition-colors">
                                        <td class="px-5 py-4">
                                            <p class="font-medium text-gray-800">{{ $detail->bahan_baku?->nama_bahan }}</p>
                                            <p class="text-xs text-gray-400">{{ $detail->bahan_baku?->satuan?->nama_satuan }}</p>
                                        </td>
                                        <td class="px-5 py-4 text-right font-semibold text-gray-600">
                                            {{ number_format($detail->jumlah_dipesan, 2, ',', '.') }}
                                        </td>
                                        <td class="px-5 py-4 text-right">
                                            <input type="number" 
                                                   name="jumlah_aktual[{{ $detail->id }}]" 
                                                   value="{{ $detail->jumlah_dipesan }}"
                                                   step="0.001" min="0" required
                                                   class="w-32 border border-gray-200 rounded-3xl px-3 py-2 text-sm text-right focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] bg-gray-50">
                                        </td>
                                        <td class="px-5 py-4 text-right">
                                            <div class="flex items-center justify-end gap-1">
                                                <span class="text-xs text-gray-400">Rp</span>
                                                <input type="number" 
                                                       name="harga_aktual[{{ $detail->id }}]" 
                                                       value="{{ $detail->harga_satuan ?? 0 }}"
                                                       step="1" min="0" required
                                                       class="w-32 border border-gray-200 rounded-3xl px-3 py-2 text-sm text-right focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] bg-gray-50">
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="p-5 border-t border-gray-100 space-y-4">
                            <div>
                                <label class="text-xs font-semibold text-gray-600 mb-1.5 block">Catatan Penerimaan (Opsional)</label>
                                <textarea name="catatan" rows="2" class="w-full border border-gray-200 rounded-3xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] bg-gray-50" placeholder="Catatan kondisi barang, dll..."></textarea>
                            </div>
                            <button type="submit" class="w-full py-3 bg-green-500 text-white text-sm font-bold rounded-3xl hover:bg-green-600 transition shadow-sm shadow-green-200">
                                ✅ Konfirmasi Penerimaan & Update Stok
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
