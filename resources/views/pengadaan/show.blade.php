{{-- 
    Halaman: Detail Pengadaan
    Deskripsi: Menampilkan informasi lengkap PO dan daftar item yang dipesan.
--}}
@extends('layouts.pos')

@section('content')
<div class="flex-1 overflow-auto bg-gray-50/50 text-gray-800 font-sans">
    <div class="p-4 md:p-6 lg:p-8 max-w-[1000px] mx-auto space-y-6">
        
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
            <div>
                <nav class="flex text-gray-500 text-xs mb-2" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-2">
                        <li class="inline-flex items-center">Pengadaan</li>
                        <li><span class="mx-2">/</span></li>
                        <li><a href="{{ route('pengadaan.index') }}" class="hover:text-blue-600 transition-colors">Daftar Pengadaan</a></li>
                        <li><span class="mx-2">/</span></li>
                        <li class="text-gray-900 font-medium">Detail PO</li>
                    </ol>
                </nav>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                    Detail Pengadaan
                    @if($pengadaan->status == 'pending')
                        <x-ui.badge color="warning" dot>Pending</x-ui.badge>
                    @elseif($pengadaan->status == 'diterima')
                        <x-ui.badge color="success" dot>Diterima</x-ui.badge>
                    @else
                        <x-ui.badge color="danger" dot>Dibatalkan</x-ui.badge>
                    @endif
                </h1>
            </div>
            <div class="mt-4 sm:mt-0 flex gap-2">
                @if($pengadaan->status == 'pending')
                    <form action="{{ route('pengadaan.update-status', $pengadaan->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin barang sudah diterima? Stok akan bertambah secara otomatis.')">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="diterima">
                        <x-ui.button type="submit" variant="success" icon="fa-check">Terima Barang</x-ui.button>
                    </form>
                    <form action="{{ route('pengadaan.update-status', $pengadaan->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin membatalkan pesanan pengadaan ini?')">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="dibatalkan">
                        <x-ui.button type="submit" variant="danger" icon="fa-times">Batalkan PO</x-ui.button>
                    </form>
                @endif
                <x-ui.button href="{{ route('pengadaan.index') }}" variant="outline" icon="fa-arrow-left">Kembali</x-ui.button>
            </div>
        </div>

        <x-ui.alert />

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-1 space-y-6">
                {{-- Info Pengadaan --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
                    <h2 class="text-lg font-bold text-gray-900 border-b border-gray-100 pb-2 mb-2">Informasi PO</h2>
                    
                    <div>
                        <div class="text-xs font-medium text-gray-500">Kode PO</div>
                        <div class="text-sm font-bold text-gray-900">{{ $pengadaan->kode_pengadaan }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-medium text-gray-500">Tanggal Pengadaan</div>
                        <div class="text-sm font-medium text-gray-900">{{ $pengadaan->tanggal_pengadaan->format('d F Y') }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-medium text-gray-500">Supplier</div>
                        <div class="text-sm font-medium text-[#3B82F6]">{{ $pengadaan->supplier->nama_supplier ?? 'Tanpa Supplier' }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-medium text-gray-500">Dibuat Oleh</div>
                        <div class="text-sm font-medium text-gray-900">{{ $pengadaan->user->name ?? '-' }}</div>
                    </div>
                    @if($pengadaan->catatan)
                    <div>
                        <div class="text-xs font-medium text-gray-500">Catatan</div>
                        <div class="text-sm text-gray-700 italic bg-gray-50 p-3 rounded-xl mt-1 border border-gray-100">{{ $pengadaan->catatan }}</div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Detail Item --}}
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col h-full">
                    <div class="p-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                        <h2 class="text-lg font-bold text-gray-900">Daftar Item</h2>
                    </div>
                    
                    <div class="p-0 flex-1 overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[600px]">
                            <thead>
                                <tr class="bg-white text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                                    <th class="px-6 py-4 font-semibold">Bahan Baku</th>
                                    <th class="px-6 py-4 font-semibold text-right">Jumlah</th>
                                    <th class="px-6 py-4 font-semibold text-right">Harga Satuan</th>
                                    <th class="px-6 py-4 font-semibold text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 text-sm">
                                @foreach($pengadaan->details as $item)
                                    <tr class="hover:bg-gray-50/50 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="font-medium text-gray-900">{{ $item->bahanBaku->nama_bahan }}</div>
                                            <div class="text-xs text-gray-500">{{ $item->bahanBaku->kategoriBahan->nama_kategori ?? '-' }}</div>
                                        </td>
                                        <td class="px-6 py-4 font-medium text-gray-900 text-right">
                                            {{ (float)$item->jumlah }} {{ $item->satuan }}
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4 font-semibold text-gray-900 text-right">
                                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="border-t-2 border-gray-100 bg-gray-50/80">
                                    <td colspan="3" class="px-6 py-4 text-right font-bold text-gray-900 text-base uppercase">Total Biaya:</td>
                                    <td class="px-6 py-4 font-black text-[#3B82F6] text-xl text-right">
                                        Rp {{ number_format($pengadaan->total_biaya, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
