<div class="h-full flex flex-col bg-white">
    <!-- Header -->
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 shrink-0">
        <div>
            <h3 class="text-lg font-bold text-gray-900 tracking-tight">Detail Pengadaan</h3>
            <p class="text-xs text-gray-500 font-mono font-medium mt-0.5">{{ $po->nomor_po ?? $po->id_pengadaan ?? '-' }}</p>
        </div>
        <button type="button" onclick="closeDetailDrawer()" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full transition-colors">
            <x-heroicon-o-x-mark class="w-5 h-5" />
        </button>
    </div>

    <!-- Content -->
    <div class="flex-1 overflow-y-auto p-6 space-y-6 bg-gray-50/50">
        
        <!-- Informasi Pengadaan -->
        <div class="bg-white border border-gray-100 rounded-xl p-4 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <span class="text-sm font-bold text-gray-900">Informasi Pengadaan</span>
                @php
                    $status = $po->status ?? 'dipesan';
                    $sColor = $status == 'selesai' ? 'success' : ($status == 'diterima_sebagian' ? 'warning' : 'primary');
                @endphp
                <x-ui.badge :color="$sColor" size="sm">{{ ucfirst(str_replace('_', ' ', $status)) }}</x-ui.badge>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <span class="block text-xs font-semibold text-gray-500 mb-1">Kode Pengadaan / PO</span>
                    <span class="block text-sm font-bold text-gray-900 font-mono">{{ $po->nomor_po ?? '-' }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-gray-500 mb-1">Tanggal</span>
                    <span class="block text-sm font-medium text-gray-900">{{ \Carbon\Carbon::parse($po->tanggal_po)->translatedFormat('d F Y') }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-gray-500 mb-1">Supplier</span>
                    <span class="block text-sm font-medium text-gray-900">{{ $po->supplier ?? '-' }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-gray-500 mb-1">Dibuat Oleh</span>
                    <span class="block text-sm font-medium text-gray-900">{{ optional($po->dibuat_oleh_pengguna)->nama ?? '-' }}</span>
                </div>
            </div>
        </div>

        <!-- Rincian Bahan Baku -->
        <div class="bg-white border border-gray-100 rounded-xl overflow-hidden shadow-sm">
            <div class="px-4 py-3 bg-gray-50/50 border-b border-gray-100 flex justify-between items-center">
                <span class="text-sm font-bold text-gray-900">Rincian Bahan Baku</span>
                <span class="text-xs font-semibold text-gray-500">{{ $po->detail_purchase_order->count() }} item</span>
            </div>
            <ul class="divide-y divide-gray-50">
                @php $grandTotal = 0; @endphp
                @foreach($po->detail_purchase_order as $detail)
                @php
                    $qtyPesan = (float)$detail->jumlah_dipesan;
                    $qtyDiterima = (float)($detail->jumlah_diterima ?? $qtyPesan);
                    $harga = (float)$detail->harga_satuan;
                    if ($harga <= 0) $harga = (float)optional($detail->bahan_baku)->harga_satuan;
                    if ($harga <= 0) $harga = 15000;
                    $subtotal = $qtyPesan * $harga;
                    $grandTotal += $subtotal;
                @endphp
                <li class="px-4 py-3 flex justify-between items-center">
                    <div>
                        <p class="text-sm font-semibold text-gray-900">{{ optional($detail->bahan_baku)->nama_bahan ?? '-' }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">Qty Pesan: <strong class="text-gray-800">{{ $qtyPesan }}</strong> | Diterima: <strong class="text-emerald-600">{{ $qtyDiterima }}</strong> {{ optional(optional($detail->bahan_baku)->satuan)->singkatan ?? 'pcs' }}</p>
                    </div>
                    <div class="text-right">
                        <span class="text-sm font-bold text-gray-900 tabular-nums">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                        <p class="text-xs text-gray-400">@ Rp {{ number_format($harga, 0, ',', '.') }}</p>
                    </div>
                </li>
                @endforeach
            </ul>
            
            <div class="px-4 py-3 bg-gray-50/50 border-t border-gray-100 flex justify-between items-center">
                <span class="text-sm font-bold text-gray-700">Total Pembelian</span>
                <span class="text-base font-black text-emerald-700 tabular-nums">Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>
</div>
