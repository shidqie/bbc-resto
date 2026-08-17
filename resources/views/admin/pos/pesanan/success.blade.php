@extends('layouts.pos')

@section('content')
<div x-data="posSuccessPreview()" class="relative h-[calc(100vh-65px)] w-full bg-slate-100 flex items-center justify-center font-sans overflow-hidden">
    {{-- Dimmer Overlay / Background untuk ilusi Modal --}}
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm z-0"></div>

    {{-- SUCCESS MODAL --}}
    <div x-show="!showPrintPreview" class="relative z-10 w-full max-w-[480px] bg-white rounded-2xl shadow-2xl p-8 flex flex-col items-center text-center mx-4"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-90"
         x-transition:enter-end="opacity-100 scale-100">
        
        {{-- Close Button di ujung kanan atas (Opsional, tapi ada di screenshot) --}}
        <a href="{{ route('pos.dinein.index') }}" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600">
            <i class="ph ph-x text-xl"></i>
        </a>

        {{-- Icon Centang --}}
        <div class="w-20 h-20 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-500 mb-5 relative">
            <div class="absolute inset-0 rounded-full border border-emerald-200 animate-ping opacity-50"></div>
            <i class="ph-bold ph-check text-4xl"></i>
        </div>

        {{-- Teks Berhasil --}}
        <h2 class="text-2xl font-bold text-slate-800 mb-1">Pesanan Berhasil!</h2>
        <p class="text-xs text-slate-500 mb-4 font-medium uppercase tracking-wider">Nomor Pesanan</p>
        
        <div class="text-lg font-bold text-slate-800 tracking-wide mb-3">
            {{ $pesanan->id_pesanan ?? 'ORD-'.date('Ymd').'-'.str_pad($pesanan->id, 3, '0', STR_PAD_LEFT) }}
        </div>
        
        <div class="text-3xl font-black text-[#0D3024] mb-8">
            Rp {{ number_format($pesanan->total_tagihan, 0, ',', '.') }}
        </div>

        {{-- Tombol Aksi --}}
        <div class="w-full space-y-3">
            <button @click="window.open('{{ route('pos.dinein.print-nota', $pesanan->id) }}', '_blank', 'width=400,height=700')" class="w-full py-3.5 bg-[#0D3024] text-white rounded-xl font-bold text-sm hover:bg-[#0a241b] transition flex justify-center items-center gap-2 shadow-sm">
                <i class="ph-bold ph-printer text-lg text-emerald-400"></i> Cetak Struk
            </button>
            <button @click="window.open('{{ route('pos.dinein.print-dapur', $pesanan->id) }}', '_blank', 'width=400,height=700')" class="w-full py-3.5 bg-emerald-50 border border-emerald-200 text-[#0D3024] rounded-xl font-bold text-sm hover:bg-emerald-100 transition flex justify-center items-center gap-2 shadow-sm">
                <i class="ph-bold ph-printer text-lg text-[#0D3024]"></i> Cetak Struk Dapur
            </button>
            <a href="{{ route('pos.dinein.index') }}" class="w-full py-3.5 bg-slate-100 text-slate-700 hover:bg-slate-200 rounded-xl font-bold text-sm transition flex justify-center items-center">
                Pesanan Baru
            </a>
        </div>
    </div>

    {{-- PRINT PREVIEW MODAL --}}
    <div x-show="showPrintPreview" class="relative z-20 w-full max-w-5xl bg-white rounded-xl shadow-2xl flex flex-col mx-4 h-[85vh] overflow-hidden"
         style="display: none;"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-90"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95">
        
        {{-- Header Preview Modal --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 shrink-0">
            <div class="flex items-center gap-3">
                <i class="ph-bold ph-printer text-2xl text-slate-700"></i>
                <div>
                    <h2 class="text-lg font-bold text-slate-800">Print Receipt</h2>
                    <p class="text-xs text-slate-500">Configure printer settings and preview the receipt.</p>
                </div>
            </div>
            <button @click="closePrintPreview()" class="text-slate-400 hover:text-slate-600 transition">
                <i class="ph ph-x text-xl"></i>
            </button>
        </div>

        {{-- Body Preview Modal --}}
        <div class="flex flex-1 overflow-hidden bg-slate-50/50">
            
            {{-- Kiri: Settings --}}
            <div class="w-80 bg-white border-r border-slate-100 p-6 overflow-y-auto custom-scrollbar shrink-0">
                
                {{-- Print Method --}}
                <div class="mb-6">
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">Print Method</p>
                    <div class="flex p-1 bg-slate-100 rounded-lg">
                        <button class="flex-1 py-1.5 text-slate-700 bg-white rounded-md shadow-sm text-sm font-semibold flex items-center justify-center gap-1"><i class="ph-bold ph-globe"></i></button>
                        <button class="flex-1 py-1.5 text-slate-400 hover:text-slate-700 rounded-md text-sm font-semibold flex items-center justify-center gap-1 cursor-not-allowed"><i class="ph-bold ph-usb"></i></button>
                        <button class="flex-1 py-1.5 text-slate-400 hover:text-slate-700 rounded-md text-sm font-semibold flex items-center justify-center gap-1 cursor-not-allowed"><i class="ph-bold ph-bluetooth"></i></button>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-2">Uses standard browser print dialog.</p>
                </div>

                {{-- Paper Size --}}
                <div class="mb-6">
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">Paper Size</p>
                    <div class="space-y-2">
                        <label class="flex items-center justify-between p-3 border rounded-xl cursor-pointer transition-colors"
                               :class="settings.paper_size === '58' ? 'border-[#ea580c] bg-orange-50/30' : 'border-slate-200 hover:border-slate-300'">
                            <div class="flex items-center gap-3">
                                <input type="radio" x-model="settings.paper_size" value="58" class="text-[#ea580c] focus:ring-[#ea580c]" @change="updatePreviewUrl()">
                                <div>
                                    <p class="text-sm font-bold text-slate-800">58mm</p>
                                    <p class="text-xs text-slate-500">Small thermal</p>
                                </div>
                            </div>
                            <div class="w-5 h-6 border-2 border-slate-300 rounded-sm"></div>
                        </label>
                        <label class="flex items-center justify-between p-3 border rounded-xl cursor-pointer transition-colors"
                               :class="settings.paper_size === '80' ? 'border-[#ea580c] bg-orange-50/30' : 'border-slate-200 hover:border-slate-300'">
                            <div class="flex items-center gap-3">
                                <input type="radio" x-model="settings.paper_size" value="80" class="text-[#ea580c] focus:ring-[#ea580c]" @change="updatePreviewUrl()">
                                <div>
                                    <p class="text-sm font-bold text-slate-800">80mm</p>
                                    <p class="text-xs text-slate-500">Standard thermal</p>
                                </div>
                            </div>
                            <div class="w-7 h-6 border-2 border-slate-300 rounded-sm"></div>
                        </label>
                    </div>
                </div>

                {{-- Tampilan Nota (Toggles) --}}
                <div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">Tampilan Nota</p>
                    <div class="space-y-3">
                        <template x-for="(label, key) in toggles" :key="key">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-semibold text-slate-700" x-text="label"></span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="settings[key]" class="sr-only peer" @change="updatePreviewUrl()">
                                    <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#ea580c]"></div>
                                </label>
                            </div>
                        </template>
                    </div>
                </div>

            </div>

            {{-- Kanan: Iframe Preview --}}
            <div class="flex-1 bg-slate-100 flex flex-col p-6 items-center overflow-y-auto">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Preview — <span x-text="settings.paper_size"></span>MM Portrait</div>
                <div class="bg-white shadow-md w-full flex-1 max-w-[400px] border border-slate-200 rounded overflow-hidden">
                    {{-- Iframe for Receipt Preview --}}
                    <iframe x-ref="receiptFrame" :src="previewUrl" class="w-full h-full border-none"></iframe>
                </div>
            </div>

        </div>

        {{-- Footer Preview Modal --}}
        <div class="flex items-center justify-end px-6 py-4 border-t border-slate-100 shrink-0 gap-3 bg-white">
            <button @click="closePrintPreview()" class="px-5 py-2.5 rounded-xl text-sm font-bold text-slate-600 border border-slate-200 hover:bg-slate-50 transition">
                Cancel
            </button>
            <button @click="executePrint()" class="px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-[#ea580c] hover:bg-orange-600 transition flex items-center gap-2 shadow-sm">
                <i class="ph-bold ph-printer"></i> Print Receipt
            </button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('posSuccessPreview', () => ({
            showPrintPreview: false,
            baseUrl: '{{ route('pos.dinein.print-nota', $pesanan->id) }}',
            previewUrl: '',
            
            settings: {
                paper_size: '58',
                show_alamat: true,
                show_telepon: true,
                show_waktu: true,
                show_kasir: true,
                show_pelanggan: true,
                show_meja: true,
                show_footer: true,
                show_pencetak: true,
                show_branding: true,
            },

            toggles: {
                show_alamat: 'Alamat Toko',
                show_telepon: 'Telepon Toko',
                show_waktu: 'Tanggal & Waktu',
                show_kasir: 'Nama Kasir',
                show_pelanggan: 'Nama Pelanggan',
                show_meja: 'Nomor Meja',
                show_footer: 'Footer Kustom',
                show_pencetak: 'Pencetak & Jam Cetak',
                show_branding: 'Branding POS',
            },

            init() {
                this.updatePreviewUrl();
            },

            openPrintPreview() {
                this.showPrintPreview = true;
            },

            closePrintPreview() {
                this.showPrintPreview = false;
            },

            updatePreviewUrl() {
                let params = new URLSearchParams();
                for (const key in this.settings) {
                    // Convert boolean to 1 or 0 for query param, keep string as is
                    let val = typeof this.settings[key] === 'boolean' ? (this.settings[key] ? '1' : '0') : this.settings[key];
                    params.append(key, val);
                }
                this.previewUrl = `${this.baseUrl}?${params.toString()}`;
            },

            executePrint() {
                if (this.$refs.receiptFrame && this.$refs.receiptFrame.contentWindow) {
                    this.$refs.receiptFrame.contentWindow.focus();
                    this.$refs.receiptFrame.contentWindow.print();
                }
            },

            printDapur() {
                // Cetak dapur menggunakan route /admin/pos/pesanan/{id}/print-dapur
                let url = '{{ route("pos.dinein.print-dapur", $pesanan->id) }}';
                let iframe = document.getElementById('kitchen-print-iframe');
                if (!iframe) {
                    iframe = document.createElement('iframe');
                    iframe.id = 'kitchen-print-iframe';
                    iframe.style.display = 'none';
                    document.body.appendChild(iframe);
                }
                iframe.src = url;
                iframe.onload = function() {
                    setTimeout(() => {
                        iframe.contentWindow.focus();
                        iframe.contentWindow.print();
                    }, 500);
                };
            }
        }));
    });
</script>
@endsection
