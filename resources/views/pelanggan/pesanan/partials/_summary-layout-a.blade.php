@php
    $summaryCfg = [
        'jenisLabel'  => $config['jenisLabel'] ?? 'Pesanan',
        'satuanLabel' => $config['satuanLabel'] ?? 'Porsi',
        'dpPersen'    => $config['dpPersen'] ?? 50,
        'batasTeks'   => $config['batasTeks'] ?? '',
        'syarat'      => $config['syarat'] ?? [],
        'hasGratisOngkir' => $config['hasGratisOngkir'] ?? false,
    ];
@endphp

<div class="bg-surface border border-primary/10 rounded-2xl shadow-sm flex flex-col">
    <div class="p-4 sm:p-5 flex-1">

        <div class="flex items-center gap-2 mb-1">
            <i class="ph-bold ph-receipt text-primary"></i>
            <h2 class="text-sm font-bold text-body">Ringkasan Pesanan</h2>
        </div>
        <p class="text-[11px] text-body/60 mb-3 pb-3 border-b border-primary/10">Periksa kembali detail pesanan sebelum melanjutkan pembayaran.</p>

        {{-- DETAIL PESANAN --}}
        <div class="mb-3">
            <h3 class="text-[10px] font-bold text-primary/50 uppercase tracking-wider mb-1.5">Detail Pesanan</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-5 gap-y-1.5 text-[11px]">
                <div class="flex justify-between items-start gap-3">
                    <span class="text-body/60 shrink-0">Jenis</span>
                    <span class="font-semibold text-body text-right">{{ $summaryCfg['jenisLabel'] }}</span>
                </div>
                <div class="flex justify-between items-start gap-3">
                    <span class="text-body/60 shrink-0">Jumlah</span>
                    <span id="summary-porsi" class="font-semibold text-body text-right">0 {{ $summaryCfg['satuanLabel'] }}</span>
                </div>
                <div class="flex justify-between items-start gap-3 col-span-full">
                    <span class="text-body/60 shrink-0">Paket</span>
                    <span id="summary-paket" class="font-semibold text-body text-right truncate max-w-[65%]">-</span>
                </div>
                <div class="flex justify-between items-start gap-3">
                    <span class="text-body/60 shrink-0">Tanggal</span>
                    <span id="summary-tgl-acara" class="font-semibold text-body text-right">-</span>
                </div>
                <div class="flex justify-between items-start gap-3">
                    <span class="text-body/60 shrink-0">Jam</span>
                    <span id="summary-jam-acara" class="font-semibold text-body text-right">-</span>
                </div>
            </div>
        </div>

        {{-- PENGIRIMAN PESANAN --}}
        <div class="mb-3 pt-3 border-t border-primary/10">
            <h3 class="text-[10px] font-bold text-primary/50 uppercase tracking-wider mb-1.5">Pengiriman</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-5 gap-y-1.5 text-[11px]">
                <div class="flex justify-between items-start gap-3">
                    <span class="text-body/60 shrink-0">Metode</span>
                    <span id="summary-metode" class="font-semibold text-body text-right">Diambil (Pickup)</span>
                </div>
                <div class="flex justify-between items-start gap-3">
                    <span class="text-body/60 shrink-0" id="summary-jam-kirim-label">Jam Ambil</span>
                    <span id="summary-jam-kirim" class="font-semibold text-body text-right">-</span>
                </div>
                <div id="summary-alamat-row" class="flex justify-between items-start gap-3 col-span-full" style="display: none;">
                    <span class="text-body/60 shrink-0">Alamat</span>
                    <span id="summary-alamat" class="font-semibold text-body text-right leading-snug">-</span>
                </div>
                <div id="summary-jarak-row" class="flex justify-between items-start gap-3" style="display: none;">
                    <span class="text-body/60 shrink-0">Jarak</span>
                    <span id="summary-jarak" class="font-semibold text-body text-right">-</span>
                </div>
            </div>
        </div>

        {{-- RINCIAN PEMBAYARAN --}}
        <div class="mb-3 pt-3 border-t border-primary/10">
            <h3 class="text-[10px] font-bold text-primary/50 uppercase tracking-wider mb-1.5">Rincian Pembayaran</h3>
            <div class="space-y-1.5 text-[11px] mb-2">
                <div class="flex justify-between items-center">
                    <span class="text-body/60 font-medium">Subtotal Menu</span>
                    <span id="subtotal-menu" class="font-bold text-body">Rp 0</span>
                </div>
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-1.5">
                        <span class="text-body/60 font-medium" id="summary-ongkir-label">Biaya Pengiriman</span>
                        <span id="badge-gratis-ongkir" class="hidden px-2 py-0.5 bg-success/10 text-success border border-success/20 rounded text-[10px] font-bold uppercase tracking-wider">Gratis Ongkir</span>
                    </div>
                    <div class="text-right">
                        <span id="summary-ongkir-coret" class="hidden text-[10px] text-body/40 line-through mr-1"></span>
                        <span id="summary-ongkir" class="font-bold text-body">Rp 0</span>
                    </div>
                </div>
            </div>
            <div class="border-t border-primary/10 border-dashed pt-2 flex justify-between items-center text-xs">
                <span class="font-bold text-body">Total Tagihan</span>
                <span id="total-tagihan" class="font-bold text-primary text-sm">Rp 0</span>
            </div>
        </div>

        {{-- DP DAN SISA --}}
        <div class="mb-3">
            <div class="flex justify-between items-center text-xs bg-amber-50 rounded-t-xl px-3 py-2.5 border border-amber-200/60 border-b-0">
                <span id="label-payment" class="text-amber-900 font-bold">DP Pembayaran <span class="text-amber-700/70 text-[10px] font-normal">({{ $summaryCfg['dpPersen'] }}%)</span></span>
                <span id="dp-amount" class="font-bold text-amber-700 text-sm">Rp 0</span>
            </div>
            <div id="sisa-pelunasan-container" class="flex justify-between items-center text-xs bg-surface rounded-b-xl px-3 py-2.5 border border-primary/10">
                <span class="text-body/60 font-bold">Sisa Pelunasan</span>
                <span id="summary-sisa-pelunasan" class="font-bold text-body">Rp 0</span>
            </div>
        </div>

        {{-- BATAS WAKTU --}}
        <div class="mb-3 px-3 py-2.5 bg-primary/[0.04] border border-primary/10 rounded-xl">
            <div class="flex justify-between items-center text-xs mb-0.5">
                <span class="text-primary font-bold">Batas Pelunasan</span>
                <span id="summary-batas-pelunasan" class="font-bold text-primary">-</span>
            </div>
            @if($summaryCfg['batasTeks'])
                <p class="text-[9px] text-body/70 leading-relaxed">{{ $summaryCfg['batasTeks'] }}</p>
            @endif
        </div>

        @if(count($summaryCfg['syarat']) > 0)
            <details class="group border border-primary/10 rounded-xl overflow-hidden">
                <summary class="flex items-center justify-between gap-2 px-3 py-2.5 cursor-pointer select-none list-none bg-primary/[0.02] hover:bg-primary/[0.05] transition-colors">
                    <span class="text-[10px] font-bold text-body flex items-center gap-1.5">
                        <i class="ph-bold ph-list-checks text-primary"></i>
                        Syarat &amp; Ketentuan {{ $summaryCfg['jenisLabel'] }}
                    </span>
                    <i class="ph-bold ph-caret-down text-body/50 text-sm transition-transform duration-200 group-open:rotate-180"></i>
                </summary>
                <div class="px-3 py-2.5 border-t border-primary/10 text-[9px] text-body/70 space-y-1 leading-relaxed bg-surface">
                    <ul class="list-disc pl-3 space-y-1">
                        @foreach($summaryCfg['syarat'] as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                </div>
            </details>
        @endif
    </div>

    {{-- STICKY SUBMIT BUTTON --}}
    <div class="p-4 sm:p-5 border-t border-primary/10 bg-surface rounded-b-2xl shrink-0">
        <button type="submit" id="submitBtn"
                class="w-full bg-primary hover:bg-primary-container text-white font-semibold text-sm py-3 rounded-xl transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed active:scale-[0.99] shadow-sm shadow-primary/20">
            Lanjut Pembayaran
        </button>
    </div>
</div>