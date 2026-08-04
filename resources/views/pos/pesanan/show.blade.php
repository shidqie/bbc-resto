{{-- 
    Halaman: Detail Pesanan (Invoice)
    Deskripsi: Menampilkan informasi pesanan detail tanpa tombol aksi & cetak struk.
--}}
@extends('layouts.pos')

@section('content')
<div class="flex-1 overflow-auto bg-gray-50/50 text-gray-800 font-sans" id="content">
    <div class="w-full p-6 max-w-[1000px] mx-auto space-y-6">
        
        {{-- Header Navigation & Title --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center no-print">
            <div>
                <nav class="flex text-gray-500 text-xs mb-2" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-2">
                        <li class="inline-flex items-center">Pesanan</li>
                        <li><span class="mx-2">/</span></li>
                        <li><a href="{{ route('pesanan.index') }}" class="hover:text-[#3B82F6] transition-colors">Daftar Pesanan</a></li>
                        <li><span class="mx-2">/</span></li>
                        <li class="text-gray-900 font-medium">Detail Invoice</li>
                    </ol>
                </nav>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                    Invoice #{{ $pesanan->no_pesanan }}
                    
                    @if($pesanan->status_pesanan == 'baru')
                        <x-ui.badge color="neutral" dot>Baru</x-ui.badge>
                    @elseif($pesanan->status_pesanan == 'diproses')
                        <x-ui.badge color="warning" dot>Diproses</x-ui.badge>
                    @elseif($pesanan->status_pesanan == 'selesai')
                        <x-ui.badge color="success" dot>Selesai</x-ui.badge>
                    @else
                        <x-ui.badge color="danger" dot>{{ ucfirst($pesanan->status_pesanan) }}</x-ui.badge>
                    @endif
                </h1>
            </div>
            <div class="mt-4 sm:mt-0 flex gap-2">
                <x-ui.button href="{{ route('pesanan.index') }}" variant="outline" icon="arrow-left">
                    <x-heroicon-o-arrow-left class="w-4 h-4 mr-1 inline-block" /> Kembali
                </x-ui.button>
            </div>
        </div>

        <x-ui.alert />

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Kolom Kiri: Tabel Daftar Menu Pesanan --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden flex flex-col">
                    <div class="p-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center print-bg">
                        <h2 class="text-base font-extrabold text-gray-900">Daftar Menu Pesanan</h2>
                    </div>
                    
                    <div class="p-0 overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[500px]">
                            <thead>
                                <tr class="bg-white text-gray-500 text-sm uppercase tracking-wider border-b border-gray-100">
                                    <th class="px-6 py-4 font-semibold">Menu</th>
                                    <th class="px-6 py-4 font-semibold text-center">Qty</th>
                                    <th class="px-6 py-4 font-semibold text-right">Harga</th>
                                    <th class="px-6 py-4 font-semibold text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 text-sm">
                                @foreach($pesanan->details as $item)
                                    <tr class="hover:bg-gray-50/50 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-gray-900">{{ $item->menu->nama }}</div>
                                            @if($item->catatan)
                                                <div class="text-xs text-gray-500 italic mt-0.5">Catatan: {{ $item->catatan }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 font-bold text-gray-900 text-center">
                                            x{{ $item->jumlah }}
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4 font-bold text-gray-900 text-right">
                                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="border-t-2 border-gray-100 bg-gray-50/80 print-bg">
                                    <td colspan="3" class="px-6 py-4 text-right font-extrabold text-gray-500 uppercase text-sm">Total Harga:</td>
                                    <td class="px-6 py-4 font-black text-[#3B82F6] text-xl text-right">
                                        Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan: Informasi Pesanan & Riwayat Pembayaran (Tanpa Aksi & Tanpa Struk) --}}
            <div class="lg:col-span-1 space-y-6">
                
                {{-- Info Pelanggan & Pembayaran --}}
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 space-y-4">
                    <h2 class="text-base font-extrabold text-gray-900 border-b border-gray-100 pb-3">Informasi Pesanan</h2>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between items-center pb-2 border-b border-dashed border-gray-200">
                            <span class="text-sm font-medium text-gray-500">Jenis Pesanan</span>
                            <span class="text-xs font-bold text-gray-900 capitalize">{{ str_replace('_', ' ', $pesanan->jenis_pesanan) }}</span>
                        </div>
                        <div class="flex justify-between items-center pb-2 border-b border-dashed border-gray-200">
                            <span class="text-sm font-medium text-gray-500">No Meja</span>
                            <span class="text-xs font-bold text-gray-900">{{ $pesanan->no_meja ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between items-center pb-2 border-b border-dashed border-gray-200">
                            <span class="text-sm font-medium text-gray-500">Pelanggan</span>
                            <span class="text-xs font-bold text-[#3B82F6]">{{ $pesanan->nama_pelanggan ?? 'Walk-in Customer' }}</span>
                        </div>
                        <div class="flex justify-between items-center pb-2 border-b border-dashed border-gray-200">
                            <span class="text-sm font-medium text-gray-500">Kasir</span>
                            <span class="text-xs font-bold text-gray-900">{{ $pesanan->user->name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between items-center pb-2 border-b border-dashed border-gray-200">
                            <span class="text-sm font-medium text-gray-500">Waktu Pesan</span>
                            <span class="text-xs font-medium text-gray-900">{{ $pesanan->tanggal_pesanan->format('d/m/Y H:i') }}</span>
                        </div>
                        
                        <div class="mt-4 pt-2">
                            <div class="text-sm font-medium text-gray-500 mb-2">Status Pembayaran:</div>
                            @if($pesanan->status_pembayaran == 'lunas')
                                <div class="bg-green-50 text-[#16A34A] px-3 py-2 rounded-xl border border-green-200 text-center font-extrabold text-xs flex items-center justify-center gap-2 print-bg">
                                    <x-heroicon-o-check-circle class="w-4 h-4 inline-block shrink-0 text-[#16A34A]" /> LUNAS
                                </div>
                            @elseif($pesanan->status_pembayaran == 'dp')
                                <div class="bg-blue-50 text-[#3B82F6] px-3 py-2 rounded-xl border border-blue-200 text-center font-extrabold text-xs print-bg">
                                    DP DIBAYARKAN
                                </div>
                            @else
                                <div class="bg-red-50 text-[#DC2626] px-3 py-2 rounded-xl border border-red-200 text-center font-extrabold text-xs print-bg">
                                    BELUM BAYAR
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Riwayat Pembayaran (Jika ada) --}}
                @if($pesanan->pembayaran->count() > 0)
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 space-y-4">
                    <h2 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-2 mb-2">Riwayat Pembayaran</h2>
                    <div class="space-y-3">
                        @foreach($pesanan->pembayaran as $pembayaran)
                            <div class="bg-gray-50 p-3 rounded-xl border border-gray-100 text-sm print-bg">
                                <div class="flex justify-between mb-1">
                                    <span class="font-bold text-gray-900 uppercase text-xs">{{ optional($pembayaran->metode_pembayaran)->nama_metode ?? 'CASH' }}</span>
                                    <span class="text-[#16A34A] font-bold">Rp {{ number_format($pembayaran->jumlah_bayar, 0, ',', '.') }}</span>
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ \Carbon\Carbon::parse($pembayaran->dibayar_pada)->format('d M Y H:i') }} ({{ optional($pembayaran->jenis_pembayaran)->nama_jenis ?? 'Pembayaran Penuh' }})
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
                
            </div>
        </div>

    </div>
</div>
@endsection
