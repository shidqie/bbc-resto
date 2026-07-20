{{-- 
    Halaman: Detail Pesanan (Invoice)
    Deskripsi: Menampilkan informasi pesanan, daftar menu yang dipesan, status, dan riwayat pembayaran.
--}}
@extends('layouts.pos')

@section('content')
<div class="flex-1 overflow-auto bg-gray-50/50 text-gray-800 font-sans" id="content">
    <div class="p-4 md:p-6 lg:p-8 max-w-[1000px] mx-auto space-y-6">
        
        {{-- Header --}}
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
                <x-ui.button href="{{ route('pesanan.index') }}" variant="outline" icon="fa-arrow-left">Kembali</x-ui.button>
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" @click.away="open = false" class="inline-flex items-center gap-2 text-white bg-gray-800 hover:bg-gray-900 px-5 py-2.5 rounded-xl font-medium text-sm transition-colors shadow-sm">
                        <x-heroicon-o-printer class="w-5 h-5 inline-block shrink-0" /> Cetak Struk <x-heroicon-o-chevron-down class="w-4 h-4 ml-1" />
                    </button>
                    <div x-show="open" x-transition class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-50">
                        <a href="{{ route('pesanan.cetak', ['pesanan' => $pesanan->id, 'type' => 'konsumen']) }}" target="_blank" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-blue-600">
                            <i class="fa-solid fa-receipt mr-2"></i> Struk Konsumen
                        </a>
                        <a href="{{ route('pesanan.cetak', ['pesanan' => $pesanan->id, 'type' => 'dapur']) }}" target="_blank" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-orange-600">
                            <i class="fa-solid fa-fire-burner mr-2"></i> Struk Dapur
                        </a>
                        <a href="{{ route('pesanan.cetak', ['pesanan' => $pesanan->id, 'type' => 'meja']) }}" target="_blank" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-purple-600">
                            <i class="fa-solid fa-hashtag mr-2"></i> Nomor Meja
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <x-ui.alert />

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                {{-- Detail Item --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col">
                    <div class="p-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center print-bg">
                        <h2 class="text-lg font-bold text-gray-900">Daftar Menu Pesanan</h2>
                    </div>
                    
                    <div class="p-0 overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[500px]">
                            <thead>
                                <tr class="bg-white text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                                    <th class="px-6 py-4 font-semibold">Menu</th>
                                    <th class="px-6 py-4 font-semibold text-right">Qty</th>
                                    <th class="px-6 py-4 font-semibold text-right">Harga</th>
                                    <th class="px-6 py-4 font-semibold text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 text-sm">
                                @foreach($pesanan->details as $item)
                                    <tr class="hover:bg-gray-50/50 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="font-medium text-gray-900">{{ $item->menu->nama }}</div>
                                            @if($item->catatan)
                                                <div class="text-xs text-gray-500 italic mt-0.5">Catatan: {{ $item->catatan }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 font-medium text-gray-900 text-right">
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
                                    <td colspan="3" class="px-6 py-4 text-right font-bold text-gray-500 uppercase text-xs">Total Harga:</td>
                                    <td class="px-6 py-4 font-black text-[#3B82F6] text-xl text-right">
                                        Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1 space-y-6">
                
                {{-- Action / Update Status --}}
                <div class="bg-white rounded-2xl border border-[#3B82F6]/20 shadow-sm p-6 space-y-4 relative overflow-hidden no-print">
                    <div class="absolute top-0 right-0 p-4 opacity-10">
                        <x-heroicon-o-clipboard-document-list class="text-6xl text-[#3B82F6] w-[1em] h-[1em] inline-block shrink-0" />
                    </div>
                    <h2 class="text-lg font-bold text-gray-900 border-b border-gray-100 pb-2 relative z-10">Aksi Pesanan</h2>
                    
                    @if(!in_array($pesanan->status_pesanan, ['selesai', 'dibatalkan']))
                        <form action="{{ route('pesanan.update-status', $pesanan->id) }}" method="POST" class="relative z-10" id="form-update-status">
                            @csrf
                            @method('PATCH')
                            
                            <div class="space-y-3">
                                <label class="block text-sm font-medium text-gray-700">Update Status Ke:</label>
                                <select name="status_pesanan" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] outline-none text-sm font-medium">
                                    @if($pesanan->status_pesanan == 'baru')
                                        <option value="diproses">Proses ke Dapur (Diproses)</option>
                                    @endif
                                    <option value="selesai">Pesanan Selesai (Sajikan/Kirim)</option>
                                    <option value="dibatalkan">Batalkan Pesanan</option>
                                </select>
                                <button type="button" onclick="confirmUpdateStatus()" class="w-full bg-[#3B82F6] hover:bg-[#2563EB] text-white py-2.5 rounded-xl font-bold text-sm shadow-sm transition-all mt-2">
                                    Update Status
                                </button>
                            </div>
                        </form>
                        <p class="text-[11px] text-gray-500 mt-3 relative z-10 bg-blue-50 p-2 rounded-lg border border-blue-100">
                            <x-heroicon-o-information-circle class="text-[#3B82F6] mr-1 w-5 h-5 inline-block shrink-0" /> 
                            Stok bahan baku akan dipotong otomatis saat status menjadi <strong>Selesai</strong>.
                        </p>
                    @else
                        <div class="text-center py-4 relative z-10">
                            <div class="w-12 h-12 rounded-full {{ $pesanan->status_pesanan == 'selesai' ? 'bg-green-100 text-[#16A34A]' : 'bg-red-100 text-[#DC2626]' }} flex items-center justify-center mx-auto mb-2 text-xl">
                                @if($pesanan->status_pesanan == 'selesai')
                                    <x-heroicon-o-check class="w-5 h-5 inline-block shrink-0" />
                                @else
                                    <x-heroicon-o-x-mark class="w-5 h-5 inline-block shrink-0" />
                                @endif
                            </div>
                            <p class="font-bold text-gray-900">Pesanan {{ ucfirst($pesanan->status_pesanan) }}</p>
                            <p class="text-xs text-gray-500 mt-1">Tidak ada aksi lanjutan yang tersedia.</p>
                        </div>
                    @endif
                </div>

                {{-- Info Pelanggan & Pembayaran --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
                    <h2 class="text-lg font-bold text-gray-900 border-b border-gray-100 pb-2 mb-2">Informasi Pesanan</h2>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between items-center pb-2 border-b border-dashed border-gray-200">
                            <span class="text-xs font-medium text-gray-500">Jenis Pesanan</span>
                            <span class="text-sm font-bold text-gray-900 capitalize">{{ str_replace('_', ' ', $pesanan->jenis_pesanan) }}</span>
                        </div>
                        <div class="flex justify-between items-center pb-2 border-b border-dashed border-gray-200">
                            <span class="text-xs font-medium text-gray-500">No Meja</span>
                            <span class="text-sm font-bold text-gray-900">{{ $pesanan->no_meja ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between items-center pb-2 border-b border-dashed border-gray-200">
                            <span class="text-xs font-medium text-gray-500">Pelanggan</span>
                            <span class="text-sm font-bold text-[#3B82F6]">{{ $pesanan->nama_pelanggan ?? 'Walk-in Customer' }}</span>
                        </div>
                        <div class="flex justify-between items-center pb-2 border-b border-dashed border-gray-200">
                            <span class="text-xs font-medium text-gray-500">Kasir</span>
                            <span class="text-sm font-bold text-gray-900">{{ $pesanan->user->name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between items-center pb-2 border-b border-dashed border-gray-200">
                            <span class="text-xs font-medium text-gray-500">Waktu Pesan</span>
                            <span class="text-xs font-medium text-gray-900">{{ $pesanan->tanggal_pesanan->format('d/m/Y H:i') }}</span>
                        </div>
                        
                        <div class="mt-4 pt-2">
                            <div class="text-xs font-medium text-gray-500 mb-2">Status Pembayaran:</div>
                            @if($pesanan->status_pembayaran == 'lunas')
                                <div class="bg-green-50 text-[#16A34A] px-3 py-2 rounded-lg border border-green-200 text-center font-bold text-sm flex items-center justify-center gap-2 print-bg">
                                    <x-heroicon-o-check-circle class="w-5 h-5 inline-block shrink-0" /> LUNAS
                                </div>
                            @elseif($pesanan->status_pembayaran == 'dp')
                                <div class="bg-blue-50 text-[#3B82F6] px-3 py-2 rounded-lg border border-blue-200 text-center font-bold text-sm print-bg">
                                    DP DIBAYARKAN
                                </div>
                            @else
                                <div class="bg-red-50 text-[#DC2626] px-3 py-2 rounded-lg border border-red-200 text-center font-bold text-sm print-bg">
                                    BELUM BAYAR
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                @if($pesanan->pembayarans->count() > 0)
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
                    <h2 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-2 mb-2">Riwayat Pembayaran</h2>
                    <div class="space-y-3">
                        @foreach($pesanan->pembayarans as $pembayaran)
                            <div class="bg-gray-50 p-3 rounded-xl border border-gray-100 text-sm print-bg">
                                <div class="flex justify-between mb-1">
                                    <span class="font-bold text-gray-900 uppercase text-xs">{{ $pembayaran->metode_pembayaran }}</span>
                                    <span class="text-[#16A34A] font-bold">Rp {{ number_format($pembayaran->jumlah_bayar, 0, ',', '.') }}</span>
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $pembayaran->tanggal_bayar->format('d M Y H:i') }} ({{ ucfirst($pembayaran->jenis_pembayaran) }})
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

<script>
    function confirmUpdateStatus() {
        const select = document.querySelector('select[name="status_pesanan"]');
        const status = select.value;
        const text = select.options[select.selectedIndex].text;
        
        let msg = `Apakah Anda yakin ingin mengupdate status menjadi "${text}"?`;
        if (status === 'selesai') {
            msg += '\n\nPERHATIAN: Mengubah ke Selesai akan secara otomatis memotong stok bahan baku sesuai resep menu.';
        }
        
        if (confirm(msg)) {
            document.getElementById('form-update-status').submit();
        }
    }
</script>

{{-- CSS Print untuk cetak struk kasar --}}
<style type="text/css" media="print">
    body * {
        visibility: hidden;
    }
    #content, #content * {
        visibility: visible;
    }
    #content {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        margin: 0;
        padding: 0;
    }
    .no-print {
        display: none !important;
    }
    .shadow-sm { box-shadow: none !important; border: 1px solid #ddd !important; }
    .print-bg {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
</style>
@endsection
