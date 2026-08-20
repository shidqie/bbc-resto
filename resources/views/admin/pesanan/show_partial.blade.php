<div class="flex flex-col h-full bg-white">
    {{-- Custom Header matching x-drawer.header but with vanilla JS close --}}
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 shrink-0 bg-white sticky top-0 z-10">
        <div>
            <h2 class="text-lg font-semibold text-gray-900 leading-tight flex items-center gap-2">
                {{ $pesanan->id_pesanan ?? 'DIN-'.$pesanan->id }}
                @php
                    $color = match($pesanan->status_pesanan_id) {
                        5 => 'success',
                        1 => 'warning',
                        6 => 'danger',
                        default => 'info',
                    };
                @endphp
                <x-drawer.badge :type="$color">
                    {{ optional($pesanan->status_pesanan)->nama_status ?? 'Unknown' }}
                </x-drawer.badge>
            </h2>
            <p class="text-sm text-gray-500 mt-1">
                Dibuat: {{ \Carbon\Carbon::parse($pesanan->dibuat_pada)->format('d F Y, H:i') }} &bull; {{ optional($pesanan->jenis_pesanan)->nama_jenis ?? '-' }}
            </p>
        </div>
        <button onclick="closeDetailDrawer()" type="button" class="flex items-center justify-center w-10 h-10 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
            <span class="sr-only">Tutup panel</span>
            <x-heroicon-o-x-mark class="w-5 h-5" />
        </button>
    </div>

    {{-- Body --}}
    <div class="flex-1 overflow-y-auto p-6 space-y-8 bg-white">
        
        {{-- Alasan Batal --}}
        @if($pesanan->status_pesanan_id === 6 && $pesanan->alasan_batal)
            <div class="p-4 rounded-xl bg-red-50 border border-red-200 flex gap-3">
                <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-red-600 shrink-0 mt-0.5" />
                <div>
                    <h4 class="text-sm font-bold text-red-900">Pesanan Dibatalkan</h4>
                    <p class="text-sm text-red-700 mt-1">{{ $pesanan->alasan_batal }}</p>
                </div>
            </div>
        @endif

        {{-- Info Panel --}}
        <x-drawer.section>
            <x-drawer.field-grid cols="4">
                @php
                    $nama = 'Tamu';
                    if (!empty($pesanan->catatan)) {
                        if (preg_match('/^Pemesan:\s*(.+)$/m', $pesanan->catatan, $m)) {
                            $nama = trim($m[1]);
                        } elseif (preg_match('/Self-Order QR \(([^)]+)\)/', $pesanan->catatan, $m)) {
                            $nama = trim($m[1]);
                        } elseif (preg_match('/^(.+?)\s*\(\d+\s*tamu\)/', $pesanan->catatan, $m)) {
                            $nama = trim($m[1]);
                        } else {
                            $nama = trim(explode('|', $pesanan->catatan)[0]);
                        }
                    }
                @endphp
                <x-drawer.field label="Pemesan" :value="$nama" />
                <x-drawer.field label="Meja/Lokasi" :value="$pesanan->meja ? 'Meja '.$pesanan->meja->nomor_meja : '-'" />
                <x-drawer.field label="Kasir" :value="optional($pesanan->kasir)->nama ?? '-'" />
                <x-drawer.field label="Pelayan" :value="optional($pesanan->pelayan)->nama ?? '-'" />
            </x-drawer.field-grid>
        </x-drawer.section>

        {{-- Jadwal Pesanan --}}
        @if($pesanan->jadwal_pesanan)
        <x-drawer.section title="Jadwal Acara / Pengiriman" icon="heroicon-o-calendar">
            <x-drawer.field-grid cols="2">
                <x-drawer.field label="Waktu" :value="\Carbon\Carbon::parse($pesanan->jadwal_pesanan->tanggal_acara)->format('d F Y, H:i')" />
                <x-drawer.field label="Lokasi" :value="$pesanan->jadwal_pesanan->lokasi_acara ?: '-'" />
            </x-drawer.field-grid>
        </x-drawer.section>
        @endif

        {{-- Daftar Item --}}
        <x-drawer.section title="Daftar Item Pesanan" icon="heroicon-o-shopping-bag">
            <x-drawer.item-list>
                @forelse($pesanan->detail_pesanan as $item)
                    <x-drawer.item 
                        :quantity="$item->jumlah . 'x'"
                        :title="optional($item->menu)->nama_menu ?? 'Menu Dihapus'"
                        :subtitle="'@ Rp' . number_format($item->harga_satuan, 0, ',', '.')"
                        :value="'Rp' . number_format($item->subtotal, 0, ',', '.')">
                        
                        @if($item->catatan)
                            <div class="mt-2 inline-block px-2 py-1 bg-amber-50 text-amber-700 text-xs rounded border border-amber-100">
                                {{ $item->catatan }}
                            </div>
                        @endif
                    </x-drawer.item>
                @empty
                    <div class="p-4 text-sm text-gray-500 text-center">Tidak ada rincian item.</div>
                @endforelse
            </x-drawer.item-list>

            {{-- Ringkasan Biaya --}}
            @php
                $subtotal = $pesanan->detail_pesanan->sum('subtotal');
                $diskon = $pesanan->diskon ?? 0;
                $pajak = $pesanan->pajak ?? 0;
                
                $summaryItems = [];
                if($diskon > 0 || $pajak > 0) {
                    $summaryItems[] = ['label' => 'Subtotal', 'value' => 'Rp' . number_format($subtotal, 0, ',', '.')];
                }
                if($diskon > 0) {
                    $summaryItems[] = ['label' => 'Diskon', 'value' => '- Rp' . number_format($diskon, 0, ',', '.')];
                }
                if($pajak > 0) {
                    $summaryItems[] = ['label' => 'Pajak & Layanan', 'value' => 'Rp' . number_format($pajak, 0, ',', '.')];
                }
            @endphp
            
            <div class="mt-4">
                <x-drawer.summary 
                    :items="$summaryItems"
                    totalLabel="Total Keseluruhan"
                    :totalValue="'Rp' . number_format($pesanan->total_tagihan, 0, ',', '.')"
                />
            </div>
        </x-drawer.section>

        {{-- Catatan Tambahan --}}
        @if(!empty($pesanan->catatan) && !preg_match('/^Pemesan:/', $pesanan->catatan) && !preg_match('/Self-Order QR/', $pesanan->catatan))
        <x-drawer.section>
            <x-drawer.note type="info">
                <strong>Catatan Pesanan:</strong><br>
                {{ $pesanan->catatan }}
            </x-drawer.note>
        </x-drawer.section>
        @endif

        {{-- Riwayat Pembayaran Lebih Rinci --}}
        <x-drawer.section title="Status & Riwayat Pembayaran" icon="heroicon-o-wallet">
            @php
                $terbayar = $pesanan->pembayaran->sum('jumlah_dibayar');
                $sisa = $pesanan->total_tagihan - $terbayar;
            @endphp
            
            <div class="mb-4">
                @if($sisa <= 0 && $pesanan->total_tagihan > 0)
                    <x-drawer.badge type="success">LUNAS</x-drawer.badge>
                @else
                    <x-drawer.badge type="danger">SISA: Rp{{ number_format(max(0, $sisa), 0, ',', '.') }}</x-drawer.badge>
                @endif
            </div>

            <x-drawer.item-list>
                @forelse($pesanan->pembayaran as $bayar)
                    <x-drawer.item 
                        :title="optional($bayar->metode_pembayaran)->nama_metode ?? 'CASH'"
                        :subtitle="\Carbon\Carbon::parse($bayar->dibayar_pada)->format('d F Y, H:i') . ' • ' . (optional($bayar->jenis_pembayaran)->nama_jenis ?? 'Lunas')"
                        :value="'+ Rp' . number_format($bayar->jumlah_bayar, 0, ',', '.')">
                        
                        @if($bayar->diproses_oleh)
                            <p class="text-[12px] text-gray-400 mt-0.5">oleh {{ optional($bayar->diverifikasi_oleh_pengguna)->nama ?? 'Kasir' }}</p>
                        @endif
                    </x-drawer.item>
                @empty
                    <div class="text-center py-4">
                        <p class="text-sm text-gray-500">Belum ada pembayaran yang masuk</p>
                    </div>
                @endforelse
            </x-drawer.item-list>
        </x-drawer.section>
        
    </div>
</div>
