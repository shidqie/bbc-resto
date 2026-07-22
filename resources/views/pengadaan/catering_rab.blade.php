@extends('layouts.pos')

@section('content')
<div class="flex-1 overflow-auto bg-gray-50/50 text-gray-800 font-sans">
    <div class="p-4 md:p-6 lg:p-8 max-w-[1200px] mx-auto space-y-6">
        
        <x-ui.page-header 
            title="RAB & Realisasi Catering" 
            :breadcrumbs="['Pengadaan', 'Daftar PO', $pengadaan->kode_pengadaan]">
            <x-slot:actions>
                <x-ui.button href="{{ route('pengadaan.index') }}" variant="outline" icon="fa-arrow-left">Kembali</x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.alert />

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-6">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-4">
                <h2 class="text-base font-extrabold text-[#0F2E23]">Informasi Order Pengadaan ({{ $pengadaan->jenis_label }})</h2>
                @if($pengadaan->status === 'diterima')
                    <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-emerald-100 text-emerald-900 border border-emerald-200">✓ Barang Diterima & Stok Diperbarui</span>
                @elseif($pengadaan->status === 'dibatalkan')
                    <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-red-100 text-red-900 border border-red-200">✕ Pengadaan Dibatalkan</span>
                @else
                    <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-amber-100 text-amber-900 border border-amber-300">⏳ Pending Penerimaan Supplier</span>
                @endif
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <div class="text-xs text-gray-500 font-bold uppercase">Kode PO</div>
                    <div class="font-mono font-bold text-gray-900">{{ $pengadaan->kode_pengadaan }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 font-bold uppercase">Pesanan Terkait</div>
                    <div class="font-bold text-[#0F2E23]">
                        @if($pengadaan->pesananCatering)
                            Catering: {{ $pengadaan->pesananCatering->nama_pemesan }}
                        @elseif($pengadaan->pesananNasiBox)
                            Nasi Box: {{ $pengadaan->pesananNasiBox->nama_pemesan }}
                        @else
                            Pengadaan Umum
                        @endif
                    </div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 font-bold uppercase">Tanggal Acara</div>
                    <div class="font-bold text-gray-900">
                        @if($pengadaan->pesananCatering)
                            {{ $pengadaan->pesananCatering->tanggal_acara->format('d M Y') }}
                        @elseif($pengadaan->pesananNasiBox)
                            {{ \Carbon\Carbon::parse($pengadaan->pesananNasiBox->tanggal_acara)->format('d M Y') }}
                        @else
                            -
                        @endif
                    </div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 font-bold uppercase">Jumlah Order</div>
                    <div class="font-extrabold text-gray-900">
                        @if($pengadaan->pesananCatering)
                            {{ $pengadaan->pesananCatering->jumlah_porsi }} Porsi
                        @elseif($pengadaan->pesananNasiBox)
                            {{ $pengadaan->pesananNasiBox->jumlah_box }} Box
                        @else
                            -
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <form action="{{ route('pengadaan.realisasi', $pengadaan->id) }}" method="POST">
            @csrf
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                    <h2 class="text-lg font-bold text-gray-900">Daftar Bahan Baku (Estimasi vs Realisasi)</h2>
                    @if($pengadaan->status === 'pending')
                    <button type="submit" class="px-4 py-2 bg-[#3B82F6] text-white rounded-xl hover:bg-blue-600 transition font-medium flex items-center gap-2">
                        <i class="fa-solid fa-save"></i> Simpan Realisasi & Terima Barang
                    </button>
                    @endif
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[800px]">
                        <thead>
                            <tr>
                                <th rowspan="2" class="px-4 py-3 border-b border-r border-gray-100 bg-gray-50 font-semibold text-gray-700 w-1/4">Bahan Baku</th>
                                <th colspan="3" class="px-4 py-2 border-b border-r border-gray-100 bg-blue-50/50 text-center font-bold text-[#3B82F6]">ESTIMASI (RAB)</th>
                                <th colspan="3" class="px-4 py-2 border-b border-gray-100 bg-green-50/50 text-center font-bold text-green-600">REALISASI BELANJA</th>
                            </tr>
                            <tr class="text-xs uppercase text-gray-500 tracking-wider">
                                <th class="px-3 py-2 border-b border-r border-gray-100 bg-gray-50 text-right">Qty</th>
                                <th class="px-3 py-2 border-b border-r border-gray-100 bg-gray-50 text-right">Harga</th>
                                <th class="px-3 py-2 border-b border-r border-gray-100 bg-gray-50 text-right">Total</th>
                                <th class="px-3 py-2 border-b border-r border-gray-100 bg-gray-50 text-right">Qty Real</th>
                                <th class="px-3 py-2 border-b border-r border-gray-100 bg-gray-50 text-right">Harga Real</th>
                                <th class="px-3 py-2 border-b border-gray-100 bg-gray-50 text-right">Total Real</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-gray-100">
                            @php 
                                $totalEst = 0; 
                                $totalReal = 0;
                            @endphp
                            @foreach($pengadaan->details as $item)
                                @php
                                    $estQty = $item->jumlah_estimasi ?? $item->jumlah;
                                    $estHarga = $item->harga_estimasi ?? $item->harga_satuan;
                                    $estSub = $item->subtotal_estimasi ?? $item->subtotal;
                                    
                                    $realQty = $item->jumlah_real ?? $estQty;
                                    $realHarga = $item->harga_real ?? $estHarga;
                                    $realSub = $item->subtotal_real ?? ($realQty * $realHarga);
                                    
                                    $totalEst += $estSub;
                                    $totalReal += $realSub;
                                @endphp
                                <tr class="hover:bg-gray-50/30 transition">
                                    <td class="px-4 py-3 border-r border-gray-100">
                                        <div class="font-medium text-gray-900">{{ $item->bahanBaku->nama_bahan }}</div>
                                        <div class="text-xs text-gray-500">{{ $item->satuan }}</div>
                                        <input type="hidden" name="detail_id[]" value="{{ $item->id }}">
                                    </td>
                                    
                                    <!-- Estimasi -->
                                    <td class="px-3 py-3 border-r border-gray-100 text-right font-medium text-gray-600">
                                        {{ (float)$estQty }}
                                    </td>
                                    <td class="px-3 py-3 border-r border-gray-100 text-right text-gray-600">
                                        {{ number_format($estHarga, 0, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-3 border-r border-gray-100 text-right font-bold text-gray-700 bg-blue-50/10">
                                        {{ number_format($estSub, 0, ',', '.') }}
                                    </td>

                                    <!-- Realisasi -->
                                    <td class="px-3 py-2 border-r border-gray-100">
                                        @if($pengadaan->status === 'pending')
                                            <input type="number" name="jumlah_real[]" value="{{ (float)$realQty }}" step="0.01" min="0" class="w-full text-right px-2 py-1 border border-gray-200 rounded focus:ring-green-100 focus:border-green-400 outline-none real-qty" oninput="calcRow(this)">
                                        @else
                                            <div class="text-right font-medium text-gray-900">{{ (float)$realQty }}</div>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 border-r border-gray-100">
                                        @if($pengadaan->status === 'pending')
                                            <input type="number" name="harga_real[]" value="{{ (float)$realHarga }}" step="1" min="0" class="w-full text-right px-2 py-1 border border-gray-200 rounded focus:ring-green-100 focus:border-green-400 outline-none real-harga" oninput="calcRow(this)">
                                        @else
                                            <div class="text-right font-medium text-gray-900">{{ number_format($realHarga, 0, ',', '.') }}</div>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 text-right font-bold text-gray-900 bg-green-50/10 real-sub-display">
                                        {{ number_format($realSub, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-50 border-t-2 border-gray-200">
                                <td class="px-4 py-4 font-black text-gray-900 text-right uppercase border-r border-gray-200">TOTAL BIAYA</td>
                                <td colspan="2" class="border-r border-gray-200"></td>
                                <td class="px-3 py-4 text-right font-black text-[#3B82F6] border-r border-gray-200 text-lg">
                                    Rp {{ number_format($totalEst, 0, ',', '.') }}
                                </td>
                                <td colspan="2" class="border-r border-gray-200"></td>
                                <td class="px-3 py-4 text-right font-black text-green-600 text-lg" id="total-real-display">
                                    Rp {{ number_format($totalReal, 0, ',', '.') }}
                                </td>
                            </tr>
                            <tr class="bg-white">
                                <td colspan="6" class="px-4 py-4 font-bold text-gray-700 text-right uppercase border-r border-gray-200">Selisih (Variance)</td>
                                <td class="px-3 py-4 text-right font-bold text-lg {{ $totalEst - $totalReal >= 0 ? 'text-green-500' : 'text-red-500' }}" id="variance-display">
                                    {{ $totalEst - $totalReal >= 0 ? 'Hemat: ' : 'Overbudget: ' }} Rp {{ number_format(abs($totalEst - $totalReal), 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    const totalEst = {{ $totalEst }};

    function calcRow(el) {
        const tr = el.closest('tr');
        const qty = parseFloat(tr.querySelector('.real-qty').value) || 0;
        const harga = parseFloat(tr.querySelector('.real-harga').value) || 0;
        const sub = qty * harga;
        tr.querySelector('.real-sub-display').innerText = new Intl.NumberFormat('id-ID').format(sub);
        calcTotal();
    }

    function calcTotal() {
        let totalReal = 0;
        document.querySelectorAll('.real-qty').forEach(qtyEl => {
            const tr = qtyEl.closest('tr');
            const qty = parseFloat(qtyEl.value) || 0;
            const harga = parseFloat(tr.querySelector('.real-harga').value) || 0;
            totalReal += (qty * harga);
        });

        document.getElementById('total-real-display').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(totalReal);
        
        const variance = totalEst - totalReal;
        const varEl = document.getElementById('variance-display');
        varEl.className = 'px-3 py-4 text-right font-bold text-lg ' + (variance >= 0 ? 'text-green-500' : 'text-red-500');
        varEl.innerText = (variance >= 0 ? 'Hemat: ' : 'Overbudget: ') + 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.abs(variance));
    }
</script>
@endsection
