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

<div class="bg-surface border border-primary/10 rounded-2xl shadow-sm flex flex-col overflow-hidden">
    {{-- HEADER INVOICE --}}
    <div class="px-4 sm:px-5 pt-4 pb-3 bg-gradient-to-br from-primary/[0.07] to-primary/[0.02] border-b border-primary/10">
        <div class="flex items-center justify-between gap-3 mb-3">
            <div class="flex items-center gap-2.5">
                <span class="w-9 h-9 rounded-xl bg-primary text-white flex items-center justify-center shadow-sm shadow-primary/20"><i class="ph-bold ph-receipt"></i></span>
                <div>
                    <h2 class="text-sm font-bold text-body leading-tight">Ringkasan Pesanan</h2>
                    <p class="text-[10px] text-body/60">Periksa kembali sebelum pembayaran</p>
                </div>
            </div>
            <span class="px-2.5 py-1 rounded-full bg-primary/10 text-primary text-[10px] font-bold uppercase tracking-wider shrink-0">{{ $summaryCfg['jenisLabel'] }}</span>
        </div>
        <div class="flex items-end justify-between gap-3 pt-3 border-t border-dashed border-primary/15">
            <div>
                <p class="text-[10px] text-body/50 font-medium mb-1">Jumlah Pesanan</p>
                <p id="summary-porsi" class="text-xl font-extrabold text-body leading-none">0 {{ $summaryCfg['satuanLabel'] }}</p>
            </div>
            <div class="text-right">
                <p class="text-[10px] text-body/50 font-medium mb-1">Total Tagihan</p>
                <p id="total-tagihan" class="text-xl font-extrabold text-primary leading-none">Rp 0</p>
            </div>
        </div>
    </div>

    <div class="px-4 sm:px-5 py-4 flex-1 space-y-4">
        {{-- DETAIL PESANAN --}}
        <div>
            <h3 class="text-[10px] font-bold text-primary/50 uppercase tracking-wider mb-2">Detail Pesanan</h3>
            <div class="grid grid-cols-2 gap-x-5 gap-y-2.5 text-xs">
                <div class="min-w-0">
                    <p class="text-body/50 mb-0.5">Paket</p>
                    <p id="summary-paket" class="font-bold text-body truncate">-</p>
                </div>
                <div>
                    <p class="text-body/50 mb-0.5">Jenis</p>
                    <p class="font-bold text-body">{{ $summaryCfg['jenisLabel'] }}</p>
                </div>
                <div>
                    <p class="text-body/50 mb-0.5">Tanggal Acara</p>
                    <p id="summary-tgl-acara" class="font-bold text-body">-</p>
                </div>
                <div>
                    <p class="text-body/50 mb-0.5">Jam Acara</p>
                    <p id="summary-jam-acara" class="font-bold text-body">-</p>
                </div>
            </div>
        </div>

        {{-- PENGIRIMAN --}}
        <div class="pt-3 border-t border-primary/10">
            <h3 class="text-[10px] font-bold text-primary/50 uppercase tracking-wider mb-2">Pengiriman</h3>
            <div class="grid grid-cols-2 gap-x-5 gap-y-2.5 text-xs">
                <div>
                    <p class="text-body/50 mb-0.5">Metode</p>
                    <p id="summary-metode" class="font-bold text-body">Diambil (Pickup)</p>
                </div>
                <div>
                    <p class="text-body/50 mb-0.5" id="summary-jam-kirim-label">Jam Ambil</p>
                    <p id="summary-jam-kirim" class="font-bold text-body">-</p>
                </div>
                <div id="summary-jarak-row" class="hidden">
                    <p class="text-body/50 mb-0.5">Jarak Pengiriman</p>
                    <p id="summary-jarak" class="font-bold text-body">-</p>
                </div>
                <div id="summary-alamat-row" class="col-span-full hidden">
                    <p class="text-body/50 mb-0.5">Alamat</p>
                    <p id="summary-alamat" class="font-bold text-body leading-snug">-</p>
                </div>
            </div>
        </div>

        {{-- RINCIAN PEMBAYARAN --}}
        <div class="pt-3 border-t border-primary/10">
            <h3 class="text-[10px] font-bold text-primary/50 uppercase tracking-wider mb-2">Rincian Pembayaran</h3>
            <div class="space-y-2 text-xs">
                <div class="flex justify-between items-center">
                    <span class="text-body/60">Subtotal Menu</span>
                    <span id="subtotal-menu" class="font-bold text-body">Rp 0</span>
                </div>
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-1.5">
                        <span class="text-body/60" id="summary-ongkir-label">Biaya Pengiriman</span>
                        <span id="badge-gratis-ongkir" class="hidden px-1.5 py-0.5 bg-success/10 text-success border border-success/20 rounded text-[9px] font-bold uppercase tracking-wider">Gratis Ongkir</span>
                    </div>
                    <div class="text-right">
                        <span id="summary-ongkir-coret" class="hidden text-[10px] text-body/40 line-through mr-1"></span>
                        <span id="summary-ongkir" class="font-bold text-body">Rp 0</span>
                    </div>
                </div>
            </div>

            <div class="mt-3 pt-2.5 border-t border-dashed border-primary/20 bg-primary/[0.04] -mx-4 sm:-mx-5 px-4 sm:px-5 py-3 flex justify-between items-center">
                <span class="text-xs font-bold text-body">Total Tagihan</span>
                <span id="total-tagihan-big" class="font-extrabold text-primary text-base">Rp 0</span>
            </div>
        </div>

        {{-- DP DAN SISA --}}
        <div>
            <div class="flex justify-between items-center text-xs bg-amber-50 rounded-t-xl px-3.5 py-3 border border-amber-200/60 border-b-0">
                <span id="label-payment" class="text-amber-900 font-bold">DP Pembayaran <span class="text-amber-700/70 text-[10px] font-normal">({{ $summaryCfg['dpPersen'] }}%)</span></span>
                <span id="dp-amount" class="font-bold text-amber-700">Rp 0</span>
            </div>
            <div id="sisa-pelunasan-container" class="flex justify-between items-center text-xs bg-surface rounded-b-xl px-3.5 py-3 border border-primary/10">
                <span class="text-body/60 font-bold">Sisa Pelunasan</span>
                <span id="summary-sisa-pelunasan" class="font-bold text-body">Rp 0</span>
            </div>
        </div>

        {{-- BATAS WAKTU --}}
        <div class="px-3.5 py-3 bg-primary/[0.04] border border-primary/10 rounded-xl">
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
                <summary class="flex items-center justify-between gap-2 px-3.5 py-2.5 cursor-pointer select-none list-none bg-primary/[0.02] hover:bg-primary/[0.05] transition-colors">
                    <span class="text-[10px] font-bold text-body flex items-center gap-1.5">
                        <i class="ph-bold ph-list-checks text-primary"></i>
                        Syarat &amp; Ketentuan {{ $summaryCfg['jenisLabel'] }}
                    </span>
                    <i class="ph-bold ph-caret-down text-body/50 text-sm transition-transform duration-200 group-open:rotate-180"></i>
                </summary>
                <div class="px-3.5 py-2.5 border-t border-primary/10 text-[9px] text-body/70 space-y-1 leading-relaxed bg-surface">
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
                    class="w-full bg-primary hover:bg-primary-container text-white font-bold text-base py-3.5 rounded-xl transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed active:scale-[0.99] shadow-[0_4px_12px_rgba(var(--color-primary),0.2)]">
                Bayar
            </button>
    </div>
</div>