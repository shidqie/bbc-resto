@extends('layouts.pos')

@section('content')
    <!-- Midtrans Snap Script -->
    <script src="{{ config('midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}" data-client-key="{{ config('midtrans.client_key') }}"></script>

    <div class="h-[calc(100vh-65px)] flex flex-col bg-white overflow-hidden font-sans antialiased text-[#111827]"
         x-data="{
             totalTagihan: {{ $totalTagihan }},
             metodeBayar: 'cash',
             uangDiterima: 0,
             dinNumber: '{{ $pesanan->nomor_pesanan ?? ('DIN-' . str_pad($pesanan->id, 4, '0', STR_PAD_LEFT)) }}',

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

        {{-- ─── MAIN 2-COLUMN MINIMALIST POS CHECKOUT LAYOUT (FLAT) ─── --}}
        <div class="flex-1 flex flex-col lg:flex-row overflow-hidden border-t border-slate-200">

            {{-- ═════════════════ LEFT COLUMN: RINGKASAN PESANAN ═════════════════ --}}
            <div class="w-full lg:w-[400px] xl:w-[480px] bg-slate-50/50 border-r border-slate-200 flex flex-col justify-between shrink-0 overflow-hidden">

                {{-- Header & Items --}}
                <div class="flex flex-col flex-1 overflow-hidden">
                    
                    {{-- Title Header: Ringkasan Pesanan --}}
                    <div class="p-4 border-b border-slate-100 space-y-2.5 shrink-0">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('pos.dinein.index') }}?view=open_bills" 
                                   class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-[#0F2E23] hover:text-white text-slate-700 flex items-center justify-center transition-all shadow-2xs group"
                                   title="Kembali ke Pesanan Belum Dibayar">
                                    <x-heroicon-o-arrow-left class="w-3 h-3 group-hover:-translate-x-0.5 transition-transform" />
                                </a>
                                <h2 class="text-base font-extrabold text-[#0F2E23] tracking-tight">Ringkasan Pesanan</h2>
                            </div>
                            <span class="px-3 py-1 bg-[#0F2E23] text-emerald-300 rounded-xl text-xs font-black shrink-0 shadow-2xs">
                                <x-heroicon-o-users class="w-3 h-3 mr-1" />
                                {{ Str::startsWith($meja->nomor_meja, 'Meja') ? $meja->nomor_meja : 'Meja ' . $meja->nomor_meja }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between pt-1">
                            <div class="flex items-center gap-2 min-w-0">
                                <div class="w-7 h-7 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-xs shrink-0">
                                    <x-heroicon-o-user class="w-3 h-3" />
                                </div>
                                <div class="min-w-0">
                                    @php
                                        $namaKonsumen = $pesanan->nama_konsumen ?? null;
                                        if (!$namaKonsumen && $pesanan->catatan) {
                                            // Format: "Pemesan: Nama"
                                            if (preg_match('/^Pemesan:\s*(.+)$/m', $pesanan->catatan, $m)) {
                                                $namaKonsumen = trim($m[1]);
                                            }
                                            // Format: "Self-Order QR (Nama) | ..."
                                            elseif (preg_match('/Self-Order QR \(([^)]+)\)/', $pesanan->catatan, $m)) {
                                                $namaKonsumen = trim($m[1]);
                                            }
                                            // Format POS: "Nama (N tamu)"
                                            elseif (preg_match('/^(.+?)\s*\(\d+\s*tamu\)/', $pesanan->catatan, $m)) {
                                                $namaKonsumen = trim($m[1]);
                                            }
                                            // Fallback: baris pertama catatan
                                            else {
                                                $namaKonsumen = trim(explode('|', $pesanan->catatan)[0]);
                                            }
                                        }
                                        $namaKonsumen = $namaKonsumen ?: 'Tamu';
                                    @endphp
                                    <p class="text-xs font-extrabold text-slate-900 truncate">{{ $namaKonsumen }}</p>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 bg-slate-100 rounded-lg text-[11px] font-mono font-bold text-slate-600 shrink-0">
                                #{{ $pesanan->nomor_pesanan ?? 'DIN-'.str_pad($pesanan->id, 4, '0', STR_PAD_LEFT) }}
                            </span>
                        </div>
                    </div>

                    {{-- Items List --}}
                    <div class="flex-1 overflow-y-auto p-4 space-y-3 divide-y divide-slate-100">
                        @foreach($pesanan->detail_pesanan as $item)
                            <div class="pt-3 first:pt-0 space-y-1">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex gap-2">
                                        <span class="font-black text-slate-900 text-sm w-5 shrink-0">{{ $item->jumlah }}x</span>
                                        <span class="font-extrabold text-slate-800 text-sm leading-snug">{{ $item->menu->nama_menu ?? $item->menu->nama ?? 'Menu' }}</span>
                                    </div>
                                    <span class="font-black text-[#0F2E23] text-sm shrink-0">
                                        Rp {{ number_format($item->subtotal ?: ($item->jumlah * ($item->harga_satuan ?? $item->menu->harga_jual ?? 0)), 0, ',', '.') }}
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
                        <span class="font-extrabold text-slate-900">{{ $pesanan->detail_pesanan->sum('jumlah') }} Item</span>
                    </div>
                    <div class="flex justify-between text-xs pt-1 border-t border-slate-200/60">
                        <span class="font-bold text-slate-700">Subtotal</span>
                        <span class="font-bold text-slate-800">Rp {{ number_format($pesanan->jumlah_sebelum_potongan ?? ($totalTagihan / 1.1), 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-xs pt-1">
                        <span class="font-bold text-slate-700">PB1 (10%)</span>
                        <span class="font-bold text-slate-800">Rp {{ number_format($pesanan->jumlah_pajak ?? ($totalTagihan - ($totalTagihan / 1.1)), 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-sm pt-2 border-t border-slate-200/60">
                        <span class="font-bold text-slate-700">Total Tagihan</span>
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
            <div class="flex-1 bg-white flex flex-col justify-between overflow-y-auto p-6 md:p-10 lg:p-12">

                <form id="checkout-form" action="{{ route('pos.dinein.processPayment', $meja->id) }}" method="POST" @submit="submitForm" class="flex flex-col flex-1 justify-between max-w-xl mx-auto w-full space-y-6">
                    @csrf
                    <input type="hidden" name="pesanan_id" value="{{ $pesanan->id }}">
                    <input type="hidden" name="total_tagihan" value="{{ $totalTagihan }}">
                    <input type="hidden" name="metode_bayar" x-model="metodeBayar">
                    <input type="hidden" name="jumlah_bayar" x-model="uangDiterima">

                    <div class="space-y-4">

                        {{-- ── 1. SELECT PAYMENT METHOD & TOTAL ── --}}
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold text-gray-800">Select Payment Method</h3>
                            <div class="text-right">
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Total Amount</p>
                                <p class="text-2xl font-black text-[#0F2E23] leading-none mt-1">Rp {{ number_format($totalTagihan, 0, ',', '.') }}</p>
                            </div>
                        </div>

                        {{-- ── 2. TABS NAVIGASI (CASH, QRIS, BANK) ── --}}
                        <div class="grid grid-cols-2 gap-2 mb-6">
                            <button type="button" @click="selectMetode('cash')"
                                    :class="metodeBayar === 'cash' ? 'bg-[#0F2E23] text-white border-[#0F2E23]' : 'bg-white border-gray-200 text-gray-500 hover:bg-gray-50'"
                                    class="border rounded-2xl py-3 flex flex-col items-center justify-center gap-1.5 transition-all">
                                <i class="ph ph-money text-2xl"></i>
                                <span class="text-xs font-bold">TUNAI (CASH)</span>
                            </button>
                            <button type="button" @click="selectMetode('nontunai')"
                                    :class="metodeBayar === 'nontunai' ? 'bg-[#0F2E23] text-white border-[#0F2E23]' : 'bg-white border-gray-200 text-gray-500 hover:bg-gray-50'"
                                    class="border rounded-2xl py-3 flex flex-col items-center justify-center gap-1.5 transition-all">
                                <i class="ph ph-qr-code text-2xl"></i>
                                <span class="text-xs font-bold">MIDTRANS SNAP</span>
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
                                    <x-heroicon-o-exclamation-circle class="text-amber-500 w-5 h-5" />
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
                                    <x-heroicon-o-check class="w-3 h-3" x-show="uangDiterima === totalTagihan" />
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

                        {{-- ── 4. MIDTRANS SNAP SECTION ── --}}
                        <div x-show="metodeBayar === 'nontunai'" class="space-y-3 text-center py-10 border-2 border-dashed border-gray-200 rounded-3xl">
                            <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="ph ph-credit-card text-3xl"></i>
                            </div>
                            <h4 class="text-sm font-bold text-gray-800">Midtrans Snap</h4>
                            <p class="text-xs text-gray-500 px-4">Pembayaran (QRIS, Transfer Bank, E-Wallet) akan diproses melalui Payment Gateway Midtrans. Silakan klik tombol "Proses Pembayaran" di bawah.</p>
                        </div>
                    </div>

                    {{-- ── 6. BOTTOM ACTION BAR (Proses Bayar) ── --}}
                    <div class="mt-4 pt-4 border-t border-slate-100 space-y-3">
                        <button type="submit"
                                :disabled="metodeBayar === 'cash' && uangDiterima < totalTagihan"
                                :class="metodeBayar === 'cash' && uangDiterima < totalTagihan
                                    ? 'opacity-50 cursor-not-allowed bg-slate-400'
                                    : 'bg-[#0F2E23] hover:bg-[#0a1f17] active:scale-[0.99] shadow-md'"
                                class="w-full py-4 rounded-2xl text-white font-black text-sm flex items-center justify-center gap-2 transition-all">
                            <span x-text="metodeBayar === 'cash' && uangDiterima < totalTagihan ? 'NOMINAL KURANG' : 'PROSES PEMBAYARAN'">PROSES PEMBAYARAN</span>
                        </button>
                    </div>

                </form>

            </div>

        </div>

    </div>
@endsection
