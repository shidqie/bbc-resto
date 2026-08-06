@extends('layouts.pos')

@section('content')
    <div class="h-[calc(100vh-65px)] flex flex-col bg-white overflow-hidden font-sans antialiased text-[#111827]"
         x-data="{
             totalTagihan: {{ $totalTagihan }},
             metodeBayar: 'tunai',
             uangDiterima: 0,
             dinNumber: '{{ $pesanan->nomor_pesanan ?? ('DIN-' . str_pad($pesanan->id, 4, '0', STR_PAD_LEFT)) }}',

             get selisih() {
                 return this.uangDiterima - this.totalTagihan;
             },

             displayUang: '',
             formatUang(val) {
                 let cleaned = String(val).replace(/[^0-9]/g, '');
                 this.uangDiterima = cleaned ? parseInt(cleaned, 10) : 0;
                 this.displayUang = this.uangDiterima ? Number(this.uangDiterima).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '';
             },

             selectMetode(metode) {
                 this.metodeBayar = metode;
             },

             addShortcut(amount) {
                 if (this.uangDiterima + amount > 999999999) return;
                 this.uangDiterima += amount;
                 this.displayUang = this.formatPrice(this.uangDiterima);
             },

             appendNum(num) {
                 let current = this.uangDiterima.toString();
                 if (current === '0') current = '';
                 if (current.length > 12) return;
                 this.uangDiterima = parseInt(current + num.toString()) || 0;
                 this.displayUang = this.formatPrice(this.uangDiterima);
             },

             formatPrice(price) {
                 if (!price) return '0';
                 return price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
             },

             proceedPayment() {
                 let form = document.getElementById('checkout-form');
                 if (this.metodeBayar === 'tunai') {
                     if (this.uangDiterima < this.totalTagihan) {
                         Swal.fire({
                             icon: 'warning',
                             title: 'Nominal Uang Kurang',
                             text: 'Uang yang diterima kurang dari total tagihan!',
                             confirmButtonColor: '#0D3024'
                         });
                         return;
                     }
                 }

                 form.submit();
             },

             submitForm(e) {
                 e.preventDefault();
                 this.proceedPayment();
             }
         }">

        @if(session('error'))
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Terjadi Kesalahan',
                        text: '{{ session('error') }}',
                        confirmButtonColor: '#0D3024'
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
                    <div class="p-4 border-b border-slate-100 space-y-3 shrink-0">
                        <x-ui.page-header
                            title="Ringkasan Pesanan"
                            subtitle="{{ Str::startsWith($meja->nomor_meja, 'Meja') ? $meja->nomor_meja : 'Meja ' . $meja->nomor_meja }}"
                            :breadcrumbs="['Penjualan', 'Dine In', 'Checkout']">
                            <x-slot:actions>
                                <a href="{{ route('pos.dinein.index') }}?view=open_bills"
                                   class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-700 bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 hover:bg-slate-50 transition-colors"
                                   title="Kembali ke Pesanan Belum Dibayar">
                                    <x-heroicon-o-arrow-left class="w-3 h-3" />
                                    Kembali
                                </a>
                            </x-slot:actions>
                        </x-ui.page-header>

                        <div class="flex items-center justify-between pt-1">
                            <div class="flex items-center gap-2 min-w-0">
                                <div class="w-7 h-7 rounded-full bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-xs shrink-0">
                                    <x-heroicon-o-user class="w-3 h-3" />
                                </div>
                                <div class="min-w-0">
                                    @php
                                        $namaKonsumen = $pesanan->nama_konsumen ?? null;
                                        if (!$namaKonsumen && $pesanan->catatan) {
                                            if (preg_match('/^Pemesan:\s*(.+)$/m', $pesanan->catatan, $m)) {
                                                $namaKonsumen = trim($m[1]);
                                            }
                                            elseif (preg_match('/Self-Order QR \(([^)]+)\)/', $pesanan->catatan, $m)) {
                                                $namaKonsumen = trim($m[1]);
                                            }
                                            elseif (preg_match('/^(.+?)\s*\(\d+\s*tamu\)/', $pesanan->catatan, $m)) {
                                                $namaKonsumen = trim($m[1]);
                                            }
                                            else {
                                                $namaKonsumen = trim(explode('|', $pesanan->catatan)[0]);
                                            }
                                        }
                                        $namaKonsumen = $namaKonsumen ?: 'Tamu';
                                    @endphp
                                    <p class="text-xs font-extrabold text-slate-900 truncate">{{ $namaKonsumen }}</p>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 bg-slate-100 rounded-lg text-xs font-mono font-bold text-slate-600 shrink-0">
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
                                    <span class="font-black text-[#0D3024] text-sm shrink-0">
                                        Rp {{ number_format($item->subtotal ?: ($item->jumlah * ($item->harga_satuan ?? $item->menu->harga_jual ?? 0)), 0, ',', '.') }}
                                    </span>
                                </div>
                                @if($item->catatan)
                                    <p class="text-xs text-slate-400 pl-7 italic font-medium">Catatan: {{ $item->catatan }}</p>
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
                    <div class="flex justify-between text-xs pt-1 border-t border-slate-200/60 mt-1">
                        <span class="font-bold text-slate-700">Subtotal Item</span>
                        <span class="font-bold text-slate-800">Rp {{ number_format((!empty($pesanan->jumlah_sebelum_potongan) && $pesanan->jumlah_sebelum_potongan > 0) ? $pesanan->jumlah_sebelum_potongan : ($totalTagihan / 1.05), 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-xs pt-1">
                        <span class="font-bold text-slate-700">Biaya Layanan (5%)</span>
                        <span class="font-bold text-slate-800">Rp {{ number_format((!empty($pesanan->jumlah_pajak) && $pesanan->jumlah_pajak > 0) ? $pesanan->jumlah_pajak : ($totalTagihan - ($totalTagihan / 1.05)), 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-sm pt-2 border-t border-slate-200/60">
                        <span class="font-bold text-slate-700">Total</span>
                        <span class="font-black text-base text-[#0D3024]">Rp {{ number_format($totalTagihan, 0, ',', '.') }}</span>
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

                <form id="checkout-form" action="{{ route('pos.dinein.processPayment', $meja->id) }}" method="POST" enctype="multipart/form-data" @submit="submitForm" class="flex flex-col flex-1 justify-between max-w-xl mx-auto w-full space-y-6">
                    @csrf
                    <input type="hidden" name="pesanan_id" value="{{ $pesanan->id }}">
                    <input type="hidden" name="total_tagihan" value="{{ $totalTagihan }}">
                    <input type="hidden" name="metode_bayar" x-model="metodeBayar">
                    <input type="hidden" name="jumlah_bayar" x-model="uangDiterima">

                    <div class="space-y-4">

                        {{-- ── 1. SELECT PAYMENT METHOD & TOTAL ── --}}
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold text-gray-800">Pilih Metode Pembayaran</h3>
                            <div class="text-right">
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Total</p>
                                <p class="text-2xl font-black text-[#0D3024] leading-none mt-1">Rp {{ number_format($totalTagihan, 0, ',', '.') }}</p>
                            </div>
                        </div>

                        {{-- ── 2. TABS NAVIGASI ── --}}
                        <div class="grid grid-cols-2 gap-3 mb-6">
                            <button type="button" @click="selectMetode('tunai')"
                                    :class="metodeBayar === 'tunai' ? 'bg-[#0D3024] text-white border-[#0D3024]' : 'bg-slate-50 text-slate-500 border-slate-200 hover:bg-slate-100'"
                                    class="w-full border rounded-2xl py-4 flex flex-col items-center justify-center gap-2 transition-all shadow-sm">
                                <i class="ph ph-money text-3xl"></i>
                                <span class="text-[11px] font-black uppercase tracking-wider">Tunai</span>
                            </button>
                            <button type="button" @click="selectMetode('qris_manual')"
                                    :class="metodeBayar === 'qris_manual' ? 'bg-[#0D3024] text-white border-[#0D3024]' : 'bg-slate-50 text-slate-500 border-slate-200 hover:bg-slate-100'"
                                    class="w-full border rounded-2xl py-4 flex flex-col items-center justify-center gap-2 transition-all shadow-sm">
                                <i class="ph ph-qr-code text-3xl"></i>
                                <span class="text-[11px] font-black uppercase tracking-wider">QRIS</span>
                            </button>
                        </div>

                        {{-- ── 3. TUNAI / CASH SECTION ── --}}
                        <div x-show="metodeBayar === 'tunai'" class="space-y-3">
                            
                            {{-- MINIMALIST INPUT CASH PELANGGAN & KEMBALIAN DISPLAY --}}
                            <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3.5 space-y-2">
                                <div class="flex items-center justify-between text-xs text-slate-500 font-bold">
                                    <span>Uang Diterima</span>
                                    <button type="button" @click="uangDiterima = 0; displayUang = ''" class="text-red-500 font-extrabold hover:underline">
                                        Reset (C)
                                    </button>
                                </div>
                                <div class="flex items-center justify-between bg-white border border-slate-200 rounded-xl px-3.5 py-2.5">
                                    <span class="text-lg font-bold text-slate-400">Rp</span>
                                    <input type="text" x-model="displayUang" @input="formatUang($event.target.value)" class="w-full text-right text-2xl lg:text-3xl font-black text-slate-900 tracking-tight bg-transparent border-0 focus:ring-0 p-0 m-0" placeholder="0">
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

                            {{-- QUICK ACCESS BUTTONS --}}
                            <div class="grid grid-cols-5 gap-2">
                                <button type="button" @click="uangDiterima = {{ $totalTagihan }}; displayUang = formatPrice({{ $totalTagihan }})" class="bg-white border border-slate-200 shadow-sm rounded-xl py-3 px-2 text-center text-xs font-black text-[#0D3024] hover:border-[#0D3024] hover:bg-[#0D3024] hover:text-white transition-all">Uang Pas</button>
                                @foreach([50000, 100000, 150000, 200000] as $nom)
                                    <button type="button" @click="uangDiterima = {{ $nom }}; displayUang = formatPrice({{ $nom }})" class="bg-white border border-slate-200 shadow-sm rounded-xl py-3 px-2 text-center text-xs font-bold text-slate-600 hover:border-[#0D3024] hover:text-[#0D3024] transition-all">{{ number_format($nom/1000, 0) }}K</button>
                                @endforeach
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
                                <button type="button" @click="uangDiterima = 0; displayUang = ''"
                                        class="bg-red-50 border border-red-200 text-red-600 text-xl font-black py-2.5 rounded-xl hover:bg-red-100 active:scale-95 transition-all shadow-2xs">C</button>
                            </div>
                        </div>

                        {{-- ── 5. QRIS MANUAL SECTION ── --}}
                        <div x-show="metodeBayar === 'qris_manual'" style="display: none;" class="space-y-4">
                            <div class="w-full flex items-center justify-center">
                                <img src="{{ asset('images/QRISSTATIC.jpg') }}" alt="QRIS" class="w-full max-w-sm object-contain rounded-2xl shadow-sm border border-gray-100">
                            </div>
                            
                            <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3.5 space-y-2">
                                <div class="flex items-center justify-between text-xs text-slate-500 font-bold">
                                    <span>Nominal Dibayarkan</span>
                                    <button type="button" @click="uangDiterima = totalTagihan" class="text-[#0D3024] font-extrabold hover:underline">
                                        Pas (Rp {{ number_format($totalTagihan, 0, ',', '.') }})
                                    </button>
                                </div>
                                <div class="flex items-center justify-between bg-white border border-slate-200 rounded-xl px-3.5 py-2.5">
                                    <span class="text-lg font-bold text-slate-400">Rp</span>
                                    <input type="number" x-model.number="uangDiterima" min="0" class="w-full text-right text-2xl lg:text-3xl font-black text-slate-900 tracking-tight bg-transparent border-0 focus:ring-0 p-0 m-0 [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none" placeholder="0" style="-moz-appearance: textfield;">
                                </div>
                            </div>
                        </div>


                    </div>

                    {{-- ── 6. BOTTOM ACTION BAR (Proses Bayar) ── --}}
                    <div class="mt-4 pt-4 border-t border-slate-100 space-y-3">
                        <button type="submit"
                                :disabled="metodeBayar === 'tunai' && uangDiterima < totalTagihan"
                                :class="metodeBayar === 'tunai' && uangDiterima < totalTagihan
                                    ? 'opacity-50 cursor-not-allowed bg-slate-400'
                                    : 'bg-[#0D3024] hover:bg-[#0a1f17] active:scale-[0.99] shadow-md'"
                                class="w-full py-4 rounded-xl text-white font-black text-sm flex items-center justify-center gap-2 transition-all">
                            <span x-text="metodeBayar === 'tunai' && uangDiterima < totalTagihan ? 'NOMINAL KURANG' : 'BAYAR'">BAYAR</span>
                        </button>
                    </div>

                </form>

            </div>

        </div>

    </div>
@endsection
