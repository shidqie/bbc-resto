@extends('layouts.pos')

@section('content')
    <!-- Midtrans Snap Script -->
    <script src="{{ config('midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}" data-client-key="{{ config('midtrans.client_key') }}"></script>

    <div class="h-[calc(100vh-65px)] flex flex-col bg-[#f5f5f0] overflow-hidden font-sans antialiased text-[#111827]"
         x-data="{
             totalTagihan: {{ $totalTagihan }},
             metodeBayar: 'cash',
             uangDiterima: 0,
             dinNumber: '{{ $pesanan->kode_pesanan ?? ('DIN-' . str_pad($pesanan->id, 4, '0', STR_PAD_LEFT)) }}',

             // QRIS Realtime state
             qrisLoading: false,
             qrisError: null,
             qrisOrderId: null,
             qrisUrl: null,
             qrisExpiryTime: null,
             pollingInterval: null,
             isPaymentSettled: false,

             get selisih() {
                 return this.uangDiterima - this.totalTagihan;
             },

             selectMetode(metode) {
                 this.metodeBayar = metode;
                 if (metode === 'nontunai' && !this.qrisUrl && !this.qrisLoading) {
                     this.generateQrisApi();
                 }
             },

             async generateQrisApi() {
                 this.qrisLoading = true;
                 this.qrisError = null;
                 try {
                     const res = await fetch('/api/payment/qris', {
                         method: 'POST',
                         headers: {
                             'Content-Type': 'application/json',
                             'X-CSRF-TOKEN': '{{ csrf_token() }}'
                         },
                         body: JSON.stringify({
                             amount: this.totalTagihan,
                             din_number: this.dinNumber,
                             customer_name: '{{ addslashes($pesanan->nama_konsumen ?? "Pelanggan POS") }}'
                         })
                     });
                     const json = await res.json();
                     if (json.success && json.data) {
                         this.qrisOrderId = json.data.order_id;
                         this.qrisUrl = json.data.qr_url;
                         this.qrisExpiryTime = json.data.expiry_time;
                         this.startPollingStatus();
                     } else {
                         this.qrisError = json.message || 'Gagal membuat QRIS Midtrans.';
                     }
                 } catch (e) {
                     this.qrisError = 'Terjadi kesalahan jaringan saat membuat QRIS.';
                 } finally {
                     this.qrisLoading = false;
                 }
             },

             startPollingStatus() {
                 if (this.pollingInterval) clearInterval(this.pollingInterval);
                 this.pollingInterval = setInterval(async () => {
                     if (!this.qrisOrderId || this.isPaymentSettled) return;
                     try {
                         const res = await fetch('/api/payment/status/' + this.qrisOrderId);
                         const json = await res.json();
                         if (json.success && json.data) {
                             if (json.data.is_paid || ['settlement', 'capture'].includes(json.data.transaction_status)) {
                                 this.isPaymentSettled = true;
                                 clearInterval(this.pollingInterval);
                                 Swal.fire({
                                     icon: 'success',
                                     title: 'Pembayaran QRIS Sukses! 🎉',
                                     text: 'Pembayaran lunas terverifikasi. Memproses nota & mengosongkan meja...',
                                     timer: 1800,
                                     showConfirmButton: false
                                 }).then(() => {
                                     document.getElementById('checkout-form').submit();
                                 });
                             }
                         }
                     } catch(e) {
                         console.error('Polling status error:', e);
                     }
                 }, 3000);
             },

             addShortcut(amount) {
                 if (this.uangDiterima + amount > 999999999) return;
                 this.uangDiterima += amount;
             },

             appendNum(num) {
                 let current = this.uangDiterima.toString();
                 if (current === '0') current = '';
                 if (current.length > 12) return;
                 this.uangDiterima = parseInt(current + num.toString()) || 0;
             },

             formatPrice(price) {
                 if (!price) return '0';
                 return price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
             },

             proceedPayment() {
                 let form = document.getElementById('checkout-form');
                 if (this.metodeBayar === 'cash') {
                     if (this.uangDiterima < this.totalTagihan) {
                         Swal.fire({
                             icon: 'warning',
                             title: 'Nominal Uang Kurang',
                             text: 'Uang yang diterima kurang dari total tagihan!',
                             confirmButtonColor: '#0F2E23'
                         });
                         return;
                     }
                     form.submit();
                     return;
                 }

                 // Nontunai Digital
                 @if($pesanan->snap_token)
                     if (typeof window.snap !== 'undefined') {
                         window.snap.pay('{{ $pesanan->snap_token }}', {
                             onSuccess: function(result) {
                                 Swal.fire({
                                     icon: 'success',
                                     title: 'Pembayaran Berhasil!',
                                     text: 'Pembayaran digital sukses diverifikasi.',
                                     timer: 1500,
                                     showConfirmButton: false
                                 }).then(() => {
                                     form.submit();
                                 });
                             },
                             onPending: function(result) {
                                 Swal.fire({
                                     icon: 'info',
                                     title: 'Menunggu Pembayaran',
                                     text: 'Silakan selesaikan pembayaran sesuai instruksi.',
                                     confirmButtonColor: '#0F2E23'
                                 }).then(() => {
                                     form.submit();
                                 });
                             },
                             onError: function(result) {
                                 Swal.fire({
                                     icon: 'error',
                                     title: 'Pembayaran Gagal',
                                     text: 'Transaksi digital gagal.',
                                     confirmButtonColor: '#0F2E23'
                                 });
                             }
                         });
                         return;
                     }
                 @endif

                 // Fallback untuk Nontunai / QRIS manual
                 form.submit();
             },

             submitForm(e) {
                 e.preventDefault();
                 const subStatus = '{{ $pesanan->sub_status ?? "diproses" }}';
                 if (['diproses', 'siap_diantar'].includes(subStatus)) {
                     Swal.fire({
                         title: 'Konfirmasi Pembayaran',
                         text: 'Pesanan ini masih berstatus [' + (subStatus === 'diproses' ? 'Diproses Dapur' : 'Siap Diantar') + ']. Yakin ingin memproses pembayaran sekarang?',
                         icon: 'question',
                         showCancelButton: true,
                         confirmButtonColor: '#0F2E23',
                         cancelButtonColor: '#64748b',
                         confirmButtonText: 'Ya, Lanjutkan',
                         cancelButtonText: 'Batal'
                     }).then((result) => {
                         if (result.isConfirmed) {
                             this.proceedPayment();
                         }
                     });
                     return;
                 }

                 this.proceedPayment();
             }
         }" x-init="return () => { if (pollingInterval) clearInterval(pollingInterval); }">

        @if(session('error'))
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Terjadi Kesalahan',
                        text: '{{ session('error') }}',
                        confirmButtonColor: '#0F2E23'
                    });
                });
            </script>
        @endif

        {{-- ─── MAIN 2-COLUMN MINIMALIST POS CHECKOUT LAYOUT ─── --}}
        <div class="flex-1 flex flex-col lg:flex-row overflow-hidden p-4 lg:p-6 gap-6 max-w-7xl mx-auto w-full">

            {{-- ═════════════════ LEFT COLUMN: RINGKASAN PESANAN ═════════════════ --}}
            <div class="w-full lg:w-[360px] xl:w-[400px] bg-white border border-gray-200/80 rounded-3xl flex flex-col justify-between shrink-0 overflow-hidden shadow-xs">

                {{-- Header & Items --}}
                <div class="flex flex-col flex-1 overflow-hidden">
                    
                    {{-- Title Header: Ringkasan Pesanan --}}
                    <div class="p-4 border-b border-slate-100 space-y-2.5 shrink-0">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('pos.dinein.index') }}?view=open_bills" 
                                   class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-[#0F2E23] hover:text-white text-slate-700 flex items-center justify-center transition-all shadow-2xs group"
                                   title="Kembali ke Pesanan Belum Dibayar">
                                    <i class="fa-solid fa-arrow-left text-xs group-hover:-translate-x-0.5 transition-transform"></i>
                                </a>
                                <h2 class="text-base font-extrabold text-[#0F2E23] tracking-tight">Ringkasan Pesanan</h2>
                            </div>
                            <span class="px-3 py-1 bg-[#0F2E23] text-emerald-300 rounded-xl text-xs font-black shrink-0 shadow-2xs">
                                <i class="fa-solid fa-chair text-[10px] mr-1"></i>
                                {{ Str::startsWith($meja->nomor_meja, 'Meja') ? $meja->nomor_meja : 'Meja ' . $meja->nomor_meja }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between pt-1">
                            <div class="flex items-center gap-2 min-w-0">
                                <div class="w-7 h-7 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-xs shrink-0">
                                    <i class="fa-solid fa-user text-[11px]"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-extrabold text-slate-900 truncate">{{ $pesanan->nama_konsumen ?? 'Pelanggan' }}</p>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 bg-slate-100 rounded-lg text-[11px] font-mono font-bold text-slate-600 shrink-0">
                                #{{ $pesanan->kode_pesanan ?? 'DIN-'.str_pad($pesanan->id, 4, '0', STR_PAD_LEFT) }}
                            </span>
                        </div>
                    </div>

                    {{-- Items List --}}
                    <div class="flex-1 overflow-y-auto p-4 space-y-3 divide-y divide-slate-100">
                        @foreach($pesanan->items as $item)
                            <div class="pt-3 first:pt-0 space-y-1">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex gap-2">
                                        <span class="font-black text-slate-900 text-sm w-5 shrink-0">{{ $item->qty }}x</span>
                                        <span class="font-extrabold text-slate-800 text-sm leading-snug">{{ $item->menu->nama }}</span>
                                    </div>
                                    <span class="font-black text-[#0F2E23] text-sm shrink-0">
                                        Rp {{ number_format($item->qty * ($item->menu->harga ?? 0), 0, ',', '.') }}
                                    </span>
                                </div>
                                @if($item->catatan)
                                    <p class="text-[11px] text-slate-400 pl-7 italic font-medium">Catatan: {{ $item->catatan }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Bottom Summary Card --}}
                <div class="p-4 border-t border-slate-200/80 bg-slate-50/60 space-y-2 text-xs font-medium text-slate-500 shrink-0">
                    <div class="flex justify-between text-xs">
                        <span>Total Item</span>
                        <span class="font-extrabold text-slate-900">{{ $pesanan->items->sum('qty') }} Item</span>
                    </div>
                    <div class="flex justify-between text-sm pt-1 border-t border-slate-200/60">
                        <span class="font-bold text-slate-700">Subtotal Tagihan</span>
                        <span class="font-black text-base text-[#0F2E23]">Rp {{ number_format($totalTagihan, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between pt-1">
                        <span class="text-xs font-bold text-slate-600">Kembalian</span>
                        <span class="font-black text-sm" :class="uangDiterima > 0 && selisih < 0 ? 'text-red-500' : 'text-emerald-700'"
                              x-text="uangDiterima === 0 ? 'Rp 0' : (selisih < 0 ? 'Kurang Rp ' + formatPrice(Math.abs(selisih)) : 'Rp ' + formatPrice(selisih))">
                            Rp 0
                        </span>
                    </div>
                </div>
            </div>

            {{-- ═════════════════ RIGHT COLUMN: PROSES PEMBAYARAN ═════════════════ --}}
            <div class="flex-1 bg-white border border-gray-200/80 rounded-3xl flex flex-col justify-between overflow-y-auto p-5 md:p-6 shadow-xs">

                <form id="checkout-form" action="{{ route('pos.dinein.processPayment', $meja->id) }}" method="POST" @submit="submitForm" class="flex flex-col flex-1 justify-between max-w-md mx-auto w-full space-y-5">
                    @csrf
                    <input type="hidden" name="pesanan_id" value="{{ $pesanan->id }}">
                    <input type="hidden" name="total_tagihan" value="{{ $totalTagihan }}">
                    <input type="hidden" name="metode_bayar" x-model="metodeBayar">

                    <div class="space-y-4">

                        {{-- ── 1. TOTAL TAGIHAN ── --}}
                        <div class="text-center space-y-0.5 pt-1">
                            <p class="text-[11px] font-extrabold text-slate-400 tracking-wider uppercase">Total Tagihan Kasir</p>
                            <div class="flex items-baseline justify-center gap-1">
                                <span class="text-2xl font-bold text-slate-400">Rp</span>
                                <span class="text-4xl lg:text-5xl font-black text-[#0F2E23] tracking-tight">{{ number_format($totalTagihan, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        {{-- ── 2. TABS NAVIGASI (TUNAI & NONTUNAI DIGITAL) ── --}}
                        <div class="border-b border-slate-200 flex justify-center gap-8 text-sm font-extrabold shrink-0">
                            <button type="button" @click="selectMetode('cash')"
                                    :class="metodeBayar === 'cash' ? 'text-[#0F2E23] border-b-2 border-[#0F2E23] pb-2 font-black' : 'text-slate-400 hover:text-slate-600 pb-2'"
                                    class="transition-all flex items-center gap-2">
                                <i class="fa-solid fa-money-bill-wave"></i>
                                <span>Tunai (Cash)</span>
                            </button>
                            <button type="button" @click="selectMetode('nontunai')"
                                    :class="metodeBayar === 'nontunai' || metodeBayar === 'qris' || metodeBayar === 'kartu' ? 'text-[#0F2E23] border-b-2 border-[#0F2E23] pb-2 font-black' : 'text-slate-400 hover:text-slate-600 pb-2'"
                                    class="transition-all flex items-center gap-2">
                                <i class="fa-solid fa-qrcode"></i>
                                <span>Nontunai (QRIS / Card)</span>
                            </button>
                        </div>

                        {{-- ── 3. TUNAI / CASH SECTION ── --}}
                        <div x-show="metodeBayar === 'cash'" class="space-y-3">
                            
                            {{-- MINIMALIST INPUT CASH PELANGGAN & KEMBALIAN DISPLAY --}}
                            <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-3.5 space-y-2">
                                <div class="flex items-center justify-between text-xs text-slate-500 font-bold">
                                    <span>Uang Diterima</span>
                                    <button type="button" @click="uangDiterima = 0" class="text-red-500 font-extrabold hover:underline">
                                        Reset (C)
                                    </button>
                                </div>
                                <div class="flex items-baseline justify-between bg-white border border-slate-200 rounded-xl px-3.5 py-2.5">
                                    <span class="text-lg font-bold text-slate-400">Rp</span>
                                    <span class="text-2xl lg:text-3xl font-black text-slate-900 tracking-tight" x-text="formatPrice(uangDiterima)">0</span>
                                </div>
                                <div class="flex items-center justify-between text-xs pt-1 border-t border-slate-200/60">
                                    <span class="font-bold text-slate-500">Kembalian:</span>
                                    <span class="font-black text-sm lg:text-base"
                                          :class="uangDiterima > 0 && selisih < 0 ? 'text-red-500' : 'text-emerald-700'"
                                          x-text="uangDiterima === 0 ? 'Rp 0' : (selisih < 0 ? 'Kurang Rp ' + formatPrice(Math.abs(selisih)) : 'Rp ' + formatPrice(selisih))">
                                        Rp 0
                                    </span>
                                </div>
                            </div>

                            {{-- WARNING NOMINAL KURANG --}}
                            <div x-show="uangDiterima > 0 && selisih < 0" x-transition
                                 class="bg-amber-50 border border-amber-200 text-amber-900 rounded-xl px-3.5 py-2 text-xs font-bold flex items-center justify-between">
                                <span class="flex items-center gap-1.5">
                                    <i class="fa-solid fa-circle-exclamation text-amber-500"></i>
                                    Kurang Rp <span x-text="formatPrice(Math.abs(selisih))"></span>
                                </span>
                                <button type="button" @click="uangDiterima = totalTagihan" class="underline font-extrabold text-amber-800">
                                    Set Pas
                                </button>
                            </div>

                            {{-- QUICK ACCESS BUTTONS --}}
                            <div class="grid grid-cols-3 gap-2">
                                <button type="button" @click="uangDiterima = totalTagihan"
                                        :class="uangDiterima === totalTagihan ? 'bg-[#0F2E23] text-white border-[#0F2E23]' : 'bg-slate-50 border-slate-200 text-slate-800 hover:bg-slate-100'"
                                        class="h-10 border rounded-xl text-xs font-extrabold transition-all flex items-center justify-center gap-1">
                                    <i class="fa-solid fa-check text-[10px]" x-show="uangDiterima === totalTagihan"></i>
                                    <span>Uang Pas</span>
                                </button>
                                <button type="button" @click="addShortcut(10000)"
                                        class="h-10 bg-slate-50 border border-slate-200 hover:bg-slate-100 rounded-xl text-xs font-extrabold text-slate-800 transition-all flex items-center justify-center">
                                    10.000
                                </button>
                                <button type="button" @click="addShortcut(20000)"
                                        class="h-10 bg-slate-50 border border-slate-200 hover:bg-slate-100 rounded-xl text-xs font-extrabold text-slate-800 transition-all flex items-center justify-center">
                                    20.000
                                </button>
                                <button type="button" @click="addShortcut(50000)"
                                        class="h-10 bg-slate-50 border border-slate-200 hover:bg-slate-100 rounded-xl text-xs font-extrabold text-slate-800 transition-all flex items-center justify-center">
                                    50.000
                                </button>
                                <button type="button" @click="addShortcut(100000)"
                                        class="h-10 bg-slate-50 border border-slate-200 hover:bg-slate-100 rounded-xl text-xs font-extrabold text-slate-800 transition-all flex items-center justify-center">
                                    100.000
                                </button>
                                <button type="button" @click="addShortcut(200000)"
                                        class="h-10 bg-slate-50 border border-slate-200 hover:bg-slate-100 rounded-xl text-xs font-extrabold text-slate-800 transition-all flex items-center justify-center">
                                    200.000
                                </button>
                            </div>

                            {{-- NUMPAD CALCULATOR --}}
                            <div class="grid grid-cols-3 gap-2">
                                @foreach([7,8,9,4,5,6,1,2,3] as $n)
                                <button type="button" @click="appendNum({{ $n }})"
                                        class="bg-white border border-slate-200 text-slate-900 text-xl font-black py-2.5 rounded-xl hover:bg-slate-50 active:scale-95 transition-all shadow-2xs">
                                    {{ $n }}
                                </button>
                                @endforeach
                                <button type="button" @click="appendNum('00')"
                                        class="bg-white border border-slate-200 text-slate-900 text-xl font-black py-2.5 rounded-xl hover:bg-slate-50 active:scale-95 transition-all shadow-2xs">00</button>
                                <button type="button" @click="appendNum(0)"
                                        class="bg-white border border-slate-200 text-slate-900 text-xl font-black py-2.5 rounded-xl hover:bg-slate-50 active:scale-95 transition-all shadow-2xs">0</button>
                                <button type="button" @click="uangDiterima = 0"
                                        class="bg-red-50 border border-red-200 text-red-600 text-xl font-black py-2.5 rounded-xl hover:bg-red-100 active:scale-95 transition-all shadow-2xs">C</button>
                            </div>
                        </div>

                        {{-- ── 4. NONTUNAI / OFFICIAL STANDAR QRIS INDONESIA (ASPI / BI) ── --}}
                        <div x-show="metodeBayar === 'nontunai' || metodeBayar === 'qris' || metodeBayar === 'kartu'" class="space-y-3">
                            
                            {{-- OFFICIAL STANDAR QRIS CARD CONTAINER --}}
                            <div class="bg-white border-2 border-red-600 rounded-3xl p-5 text-center shadow-lg relative overflow-hidden space-y-4">
                                
                                {{-- QRIS Header Strip --}}
                                <div class="flex items-center justify-between border-b-2 border-red-600 pb-3">
                                    <div class="flex items-center gap-2">
                                        <span class="text-2xl font-black text-red-600 tracking-tighter italic">QRIS</span>
                                        <span class="text-[10px] font-extrabold text-slate-500 uppercase tracking-widest bg-slate-100 px-2.5 py-0.5 rounded border border-slate-200">Standar Nasional</span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <span class="text-[10px] font-black text-red-600 bg-red-50 border border-red-200 px-2.5 py-0.5 rounded-full">GPN</span>
                                    </div>
                                </div>

                                {{-- Merchant Info & NMID --}}
                                <div class="space-y-0.5 text-center">
                                    <h4 class="text-base font-black text-slate-900 tracking-tight uppercase">SAUNG BABAKAN CINTA (BBC RESTO)</h4>
                                    <p class="text-[11px] font-mono text-slate-500 font-bold">NMID: ID1024391823901</p>
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 text-[#0F2E23] border border-emerald-200 rounded-xl text-xs font-black mt-1">
                                        <span>Tagihan: Rp {{ number_format($totalTagihan, 0, ',', '.') }}</span>
                                    </div>
                                </div>

                                {{-- Loading State --}}
                                <div x-show="qrisLoading" class="py-10 space-y-2">
                                    <i class="fa-solid fa-spinner fa-spin text-3xl text-red-600"></i>
                                    <p class="text-xs font-bold text-slate-600">Menghubungkan ke Standar Server QRIS...</p>
                                </div>

                                {{-- Error State --}}
                                <div x-show="qrisError && !qrisLoading" class="p-3 bg-red-50 text-red-700 border border-red-200 rounded-xl text-xs font-bold">
                                    <p x-text="qrisError"></p>
                                    <button type="button" @click="generateQrisApi()" class="mt-2 px-4 py-1.5 bg-red-600 text-white rounded-xl text-xs font-extrabold shadow-2xs">Coba Lagi</button>
                                </div>

                                {{-- QR Code Image & Scanning Corners --}}
                                <div x-show="!qrisLoading && !qrisError" class="space-y-3">
                                    <div class="relative inline-block bg-white p-4 rounded-2xl border border-slate-200 shadow-md">
                                        <template x-if="qrisUrl">
                                            <img :src="qrisUrl" alt="QRIS Standar Nasional" class="w-48 h-48 lg:w-52 lg:h-52 object-contain mx-auto rounded-lg">
                                        </template>
                                        {{-- Corner Markers --}}
                                        <div class="absolute top-2 left-2 w-3.5 h-3.5 border-t-2 border-l-2 border-red-600"></div>
                                        <div class="absolute top-2 right-2 w-3.5 h-3.5 border-t-2 border-r-2 border-red-600"></div>
                                        <div class="absolute bottom-2 left-2 w-3.5 h-3.5 border-b-2 border-l-2 border-red-600"></div>
                                        <div class="absolute bottom-2 right-2 w-3.5 h-3.5 border-b-2 border-r-2 border-red-600"></div>
                                    </div>

                                    {{-- Live Status Indicator --}}
                                    <div class="flex items-center justify-center gap-2 text-xs font-extrabold text-emerald-800 bg-emerald-50 px-3.5 py-1.5 rounded-full border border-emerald-200 max-w-xs mx-auto">
                                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-ping"></span>
                                        <span>Menunggu Scan & Pembayaran Realtime</span>
                                    </div>
                                </div>

                                {{-- Footer Supported E-Wallets & Banks --}}
                                <div class="pt-3 border-t border-slate-100 space-y-1.5">
                                    <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Dukungan Pembayaran (Semua E-Wallet & M-Banking):</p>
                                    <div class="flex flex-wrap items-center justify-center gap-1.5 text-[10px] font-bold text-slate-600">
                                        <span class="px-2 py-0.5 bg-blue-50 text-blue-700 rounded border border-blue-100">GoPay</span>
                                        <span class="px-2 py-0.5 bg-orange-50 text-orange-700 rounded border border-orange-100">ShopeePay</span>
                                        <span class="px-2 py-0.5 bg-purple-50 text-purple-700 rounded border border-purple-100">OVO</span>
                                        <span class="px-2 py-0.5 bg-sky-50 text-sky-700 rounded border border-sky-100">DANA</span>
                                        <span class="px-2 py-0.5 bg-red-50 text-red-700 rounded border border-red-100">LinkAja</span>
                                        <span class="px-2 py-0.5 bg-slate-100 text-slate-700 rounded border border-slate-200">m-Banking (BCA, Mandiri, BRI, BNI)</span>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>

                    {{-- ── 5. BOTTOM ACTION BAR (Kembali & Proses Bayar) ── --}}
                    <div class="border-t border-slate-200/80 pt-3 flex items-center justify-between gap-3 shrink-0">
                        <a href="{{ route('pos.dinein.index') }}?view=open_bills" class="py-3 px-5 text-xs font-extrabold text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-2xl transition-colors text-center flex items-center justify-center gap-2 border border-slate-200 shadow-2xs">
                            <i class="fa-solid fa-arrow-left text-xs"></i>
                            <span>Kembali ke Pesanan Belum Dibayar</span>
                        </a>
                        <button type="submit"
                                :disabled="metodeBayar === 'cash' && uangDiterima < totalTagihan"
                                :class="metodeBayar === 'cash' && uangDiterima < totalTagihan
                                    ? 'opacity-50 cursor-not-allowed bg-slate-400'
                                    : 'bg-[#0F2E23] hover:bg-[#0a1f17] active:scale-[0.99] shadow-md'"
                                class="flex-1 max-w-[260px] py-3.5 px-6 rounded-2xl text-white font-black text-sm flex items-center justify-between transition-all">
                            <span x-text="metodeBayar === 'cash' && uangDiterima < totalTagihan ? 'Nominal Kurang' : 'Proses Bayar'">Proses Bayar</span>
                            <i class="fa-solid fa-chevron-right text-xs"></i>
                        </button>
                    </div>

                </form>

            </div>

        </div>

    </div>
@endsection
