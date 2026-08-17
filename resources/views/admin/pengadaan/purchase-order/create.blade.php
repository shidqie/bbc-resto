@extends('layouts.pos')
@section('title', 'Buat Purchase Order')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800 pb-10">
    <div class="w-full p-6 space-y-5 max-w-5xl mx-auto">
        
        <x-ui.page-header
            title="Buat Purchase Order (PO)"
            subtitle="Buat pesanan bahan baku ke supplier untuk kebutuhan {{ $tipe }}."
            :breadcrumbs="['Pengadaan', 'Purchase Order', 'Buat PO']">
            <x-slot:actions>
                <x-ui.button variant="secondary" href="{{ route('pengadaan.po.index') }}">
                    Batal
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.alert />

        <form action="{{ route('pengadaan.po.create') }}" method="GET" class="mb-6 bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex items-end gap-4" x-data="{ tipe: '{{ $tipe }}' }">
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">Jenis Purchase Order</label>
                <div class="flex items-center gap-4">
                    <label class="inline-flex items-center">
                        <input type="radio" name="tipe" value="Operasional" x-model="tipe" class="text-primary-600 focus:ring-primary-500" @change="$el.form.submit()">
                        <span class="ml-2 text-sm text-gray-700">Operasional</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="tipe" value="Catering" x-model="tipe" class="text-primary-600 focus:ring-primary-500" @change="$el.form.submit()">
                        <span class="ml-2 text-sm text-gray-700">Catering</span>
                    </label>
                </div>
            </div>
            
            <template x-if="tipe === 'Catering'">
                <div class="flex-1 flex items-end gap-4 ml-6 pl-6 border-l border-gray-200">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kode Pesanan Catering</label>
                        <input type="text" name="kode_pesanan" value="{{ request('kode_pesanan') }}" placeholder="Contoh: PSN-KTR-2026..." class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring focus:ring-primary-200">
                    </div>
                    <x-ui.button type="submit" variant="secondary" icon="magnifying-glass">Cari Pesanan</x-ui.button>
                </div>
            </template>
        </form>

        @if($tipe === 'Catering' && $pesanan)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="text-base font-semibold text-gray-900">Informasi Pesanan Catering</h3>
                </div>
                <div class="p-6 grid grid-cols-2 md:grid-cols-4 gap-6">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Kode Pesanan</p>
                        <p class="font-bold text-gray-900">{{ $pesanan->id_pesanan }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Nama Pelanggan</p>
                        <p class="font-bold text-gray-900">{{ $pesanan->nama_pemesan }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Tanggal Acara</p>
                        <p class="font-bold text-gray-900">{{ $pesanan->waktu_diambil ? \Carbon\Carbon::parse($pesanan->waktu_diambil)->format('d M Y') : '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Total Porsi</p>
                        <p class="font-bold text-gray-900">{{ $pesanan->detail_pesanan->sum('jumlah') }} Porsi</p>
                    </div>
                </div>
            </div>
        @endif

        @if($tipe === 'Operasional' || ($tipe === 'Catering' && $pesanan))
        <form action="{{ route('pengadaan.po.store-unified') }}" method="POST" class="space-y-6">
            @csrf
            <input type="hidden" name="tipe" value="{{ $tipe }}">
            @if(isset($pesanan))
                <input type="hidden" name="pesanan_id" value="{{ $pesanan->id }}">
            @endif

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="text-base font-semibold text-gray-900">Informasi Supplier</h3>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Supplier/Toko <span class="text-red-500">*</span></label>
                            <input type="text" name="supplier_nama" class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring focus:ring-primary-200" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Telepon</label>
                            <input type="text" name="supplier_telepon" class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring focus:ring-primary-200">
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Supplier</label>
                            <textarea name="supplier_alamat" rows="2" class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring focus:ring-primary-200"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan Tambahan</label>
                            <input type="text" name="catatan" class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring focus:ring-primary-200">
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                    <h3 class="text-base font-semibold text-gray-900">Daftar Bahan Baku</h3>
                    <span class="text-sm text-gray-500">
                        @if($tipe === 'Operasional')
                            Otomatis dipilih dari sistem yang stoknya kurang
                        @else
                            Berdasarkan perhitungan resep
                        @endif
                    </span>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50 border-b border-gray-200 text-sm">
                                <th class="py-3 px-4 w-12 text-center">Pilih</th>
                                <th class="py-3 px-4">Bahan Baku</th>
                                @if($tipe === 'Catering')
                                    <th class="py-3 px-4 text-right">Kebutuhan (Resep)</th>
                                    <th class="py-3 px-4 text-right">Sudah di-PO</th>
                                    <th class="py-3 px-4 text-right">Kebutuhan Bersih</th>
                                @else
                                    <th class="py-3 px-4 text-right">Stok Minimum</th>
                                    <th class="py-3 px-4 text-right">Kebutuhan</th>
                                @endif
                                <th class="py-3 px-4 text-right">Stok Saat Ini</th>
                                <th class="py-3 px-4 w-48 text-right">Jumlah Beli</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($items as $item)
                            @php
                                $isDisabled = $item->jumlah_beli <= 0 && $tipe === 'Catering';
                            @endphp
                            <tr class="{{ $isDisabled ? 'bg-gray-50/50 opacity-60' : 'hover:bg-gray-50/50 transition-colors' }}">
                                <td class="py-3 px-4 text-center">
                                    <input type="checkbox" name="item_checked[{{ $item->id }}]" value="1" {{ !$isDisabled ? 'checked' : '' }} class="rounded border-gray-300 text-primary-600 focus:ring-primary-500 w-4 h-4 cursor-pointer">
                                </td>
                                <td class="py-3 px-4 font-medium text-gray-900">
                                    {{ $item->nama_bahan }}
                                    <div class="text-xs text-gray-500 font-normal">{{ $item->id_bahan_baku }}</div>
                                </td>
                                @if($tipe === 'Catering')
                                    <td class="py-3 px-4 text-right text-gray-600">
                                        {{ rtrim(rtrim(number_format($item->kebutuhan_awal ?? 0, 2), '0'), '.') }} {{ $item->satuan->singkatan ?? '' }}
                                    </td>
                                    <td class="py-3 px-4 text-right text-gray-600">
                                        {{ rtrim(rtrim(number_format($item->sudah_dipesan ?? 0, 2), '0'), '.') }} {{ $item->satuan->singkatan ?? '' }}
                                    </td>
                                    <td class="py-3 px-4 text-right text-red-600 font-medium">
                                        {{ rtrim(rtrim(number_format($item->kebutuhan_bersih ?? 0, 2), '0'), '.') }} {{ $item->satuan->singkatan ?? '' }}
                                    </td>
                                @else
                                    <td class="py-3 px-4 text-right text-gray-600">
                                        {{ rtrim(rtrim(number_format($item->stok_minimal ?? 0, 2), '0'), '.') }} {{ $item->satuan->singkatan ?? '' }}
                                    </td>
                                    <td class="py-3 px-4 text-right text-red-600 font-medium">
                                        {{ rtrim(rtrim(number_format($item->kebutuhan_bersih ?? 0, 2), '0'), '.') }} {{ $item->satuan->singkatan ?? '' }}
                                    </td>
                                @endif
                                
                                <td class="py-3 px-4 text-right text-gray-600">
                                    {{ rtrim(rtrim(number_format($item->jumlah_stok ?? 0, 2), '0'), '.') }} {{ $item->satuan->singkatan ?? '' }}
                                </td>
                                
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <input type="number" step="0.01" min="0" name="jumlah_beli[{{ $item->id }}]" value="{{ $tipe === 'Catering' ? $item->jumlah_beli : $item->kebutuhan_bersih }}" class="w-24 text-right rounded-lg border-gray-300 focus:border-primary-500 focus:ring focus:ring-primary-200 text-sm py-1.5" required>
                                        <span class="text-sm text-gray-500 w-8 text-left">{{ $item->satuan->singkatan ?? '' }}</span>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="{{ $tipe === 'Catering' ? 8 : 7 }}" class="py-8 text-center text-gray-500">
                                    @if($tipe === 'Catering')
                                        Pesanan ini tidak membutuhkan bahan atau resep belum diatur.
                                    @else
                                        Tidak ada rekomendasi PO. Semua stok aman.
                                    @endif
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <x-ui.button type="button" variant="secondary" href="{{ route('pengadaan.po.index') }}">
                    Batal
                </x-ui.button>
                <x-ui.button type="submit" variant="primary" icon="check">
                    Proses Purchase Order
                </x-ui.button>
            </div>
        </form>
        @elseif($tipe === 'Catering' && request('kode_pesanan') && !$pesanan)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center text-gray-500">
                Data pesanan Catering tidak valid atau tidak ditemukan.
            </div>
        @elseif($tipe === 'Catering')
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center text-gray-500">
                Silakan cari Kode Pesanan Catering terlebih dahulu.
            </div>
        @endif

    </div>
</div>
@endsection