@extends('layouts.pos')

@php
    $pengaturan = \App\Models\PengaturanTransaksi::first();

    $subtotalCalc = (float) $pesanan->detail_pesanan->sum(function ($item) {
        return $item->subtotal ?: ($item->jumlah * ($item->harga_satuan ?? $item->menu->harga_jual ?? 0));
    });
    if (!$subtotalCalc && $pesanan->jumlah_sebelum_potongan > 0) {
        $subtotalCalc = (float) $pesanan->jumlah_sebelum_potongan;
    }

    $layananAktif = $pengaturan ? $pengaturan->layanan_aktif : true;
    $biayaLayanan = ($layananAktif && $subtotalCalc > 0) ? (float) ($pengaturan->nominal_layanan ?? 1000) : 0;
    $jumlahPajak = 0;
    $persenPajak = 0;
    $totalTagihan = $subtotalCalc + $biayaLayanan;
@endphp

@section('content')
    <div class="h-[calc(100vh-65px)] flex flex-col bg-white overflow-hidden font-sans antialiased text-body"
         x-data="{
             totalTagihan: {{ $totalTagihan }},
             metodeBayar: 'tunai',
             uangDiterima: 0,
             dinNumber: '{{ $pesanan->id_pesanan ?? ('DIN-' . str_pad($pesanan->id, 4, '0', STR_PAD_LEFT)) }}',
             showFullscreenQR: false,

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
                 if (metode === 'qris_manual') {
                     this.uangDiterima = this.totalTagihan;
                 }
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
                                #{{ $pesanan->id_pesanan ?? 'DIN-'.str_pad($pesanan->id, 4, '0', STR_PAD_LEFT) }}
                            </span>
                        </div>
                    </div>

                    {{-- Items List --}}
                    <div class="flex-1 overflow-y-auto p-4 space-y-3 divide-y divide-slate-100">
                        @foreach($pesanan->detail_pesanan as $item)
                            @php
                                $hargaSatuan = $item->harga_satuan ?? $item->menu->harga_jual ?? 0;
                                $subtotalItem = $item->subtotal ?: ($item->jumlah * $hargaSatuan);
                            @endphp
                            <div class="pt-3 first:pt-0 space-y-1">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex flex-col min-w-0">
                                        <div class="flex items-baseline gap-2">
                                            <span class="font-black text-slate-900 text-sm shrink-0">{{ $item->jumlah }}x</span>
                                            <span class="font-extrabold text-slate-800 text-sm leading-snug truncate">{{ $item->menu->nama_menu ?? $item->menu->nama ?? 'Menu' }}</span>
                                        </div>
                                        <span class="text-xs text-slate-500 font-semibold pl-7">
                                            @ Rp {{ number_format($hargaSatuan, 0, ',', '.') }} / porsi
                                        </span>
                                    </div>
                                    <span class="font-black text-primary text-sm shrink-0">
                                        Rp {{ number_format($subtotalItem, 0, ',', '.') }}
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
                        <span class="font-bold text-slate-800">Rp {{ number_format($subtotalCalc, 0, ',', '.') }}</span>
                    </div>
                    @if($biayaLayanan > 0)
                    <div class="flex justify-between text-xs pt-1">
                        <span class="font-bold text-slate-700">Biaya Layanan</span>
                        <span class="font-bold text-slate-800">Rp {{ number_format($biayaLayanan, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    @if($jumlahPajak > 0)
                    <div class="flex justify-between text-xs pt-1">
                        <span class="font-bold text-slate-700">PPN / Pajak ({{ $persenPajak }}%)</span>
                        <span class="font-bold text-slate-800">Rp {{ number_format($jumlahPajak, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between text-sm pt-2 border-t border-slate-200/60">
                        <span class="font-bold text-slate-700">Total Tagihan</span>
                        <span class="font-black text-base text-primary">Rp {{ number_format($totalTagihan, 0, ',', '.') }}</span>
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
                            <h3 class="text-base font-bold text-gray-800">Pilih Metode Pembayaran</h3>
                            <div class="text-right">
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total</p>
                                <p class="text-xl font-bold text-primary leading-none mt-1">Rp {{ number_format($totalTagihan, 0, ',', '.') }}</p>
                            </div>
                        </div>

                        {{-- ── 2. TABS NAVIGASI ── --}}
                        <div class="grid grid-cols-2 gap-3 mb-6">
                            <button type="button" @click="selectMetode('tunai')"
                                    :class="metodeBayar === 'tunai' ? 'bg-primary text-white border-primary' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100'"
                                    class="w-full border rounded-xl py-3 flex flex-col items-center justify-center gap-1.5 transition-all shadow-sm outline-none focus:outline-none focus:ring-0 active:scale-[0.98] cursor-pointer">
                                <i class="ph ph-money text-2xl"></i>
                                <span class="text-xs font-semibold uppercase tracking-wider">Tunai</span>
                            </button>
                            <button type="button" @click="selectMetode('qris_manual')"
                                    :class="metodeBayar === 'qris_manual' ? 'bg-primary text-white border-primary' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100'"
                                    class="w-full border rounded-xl py-3 flex flex-col items-center justify-center gap-1.5 transition-all shadow-sm outline-none focus:outline-none focus:ring-0 active:scale-[0.98] cursor-pointer">
                                <i class="ph ph-qr-code text-2xl"></i>
                                <span class="text-xs font-semibold uppercase tracking-wider">QRIS</span>
                            </button>
                        </div>

                        {{-- ── 3. TUNAI / CASH SECTION ── --}}
                        <div x-show="metodeBayar === 'tunai'" class="space-y-3">
                            
                            {{-- MINIMALIST INPUT CASH PELANGGAN & KEMBALIAN DISPLAY --}}
                            <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3.5 space-y-2">
                                <div class="flex items-center justify-between text-xs text-slate-500 font-medium">
                                    <span>Uang Diterima</span>
                                    <button type="button" @click="uangDiterima = 0; displayUang = ''" class="text-red-500 font-semibold hover:text-red-600 hover:underline outline-none focus:outline-none focus:ring-0 focus:border-none border-0 p-0 cursor-pointer select-none">
                                        Reset (C)
                                    </button>
                                </div>
                                <div class="flex items-center justify-between bg-white border border-slate-200 rounded-xl px-3.5 py-2">
                                    <span class="text-base font-semibold text-slate-400">Rp</span>
                                    <input type="text" x-model="displayUang" @input="formatUang($event.target.value)" class="w-full text-right text-xl font-bold text-slate-900 tracking-tight bg-transparent border-0 focus:ring-0 outline-none p-0 m-0" placeholder="0">
                                </div>
                                <div class="flex items-center justify-between text-xs pt-1 border-t border-slate-200/60">
                                    <span class="font-medium text-slate-500">Kembalian:</span>
                                    <span class="font-bold text-sm"
                                          :class="uangDiterima > 0 && selisih < 0 ? 'text-red-500' : 'text-emerald-700'"
                                          x-text="uangDiterima === 0 ? 'Rp 0' : (selisih < 0 ? 'Kurang Rp ' + formatPrice(Math.abs(selisih)) : 'Rp ' + formatPrice(selisih))">
                                        Rp 0
                                    </span>
                                </div>
                            </div>

                            {{-- QUICK ACCESS BUTTONS --}}
                            <div class="grid grid-cols-5 gap-2">
                                <button type="button" @click="uangDiterima = {{ $totalTagihan }}; displayUang = formatPrice({{ $totalTagihan }})" class="bg-white border border-slate-200 shadow-sm rounded-xl py-3 px-2 text-center text-xs font-black text-primary hover:border-primary hover:bg-primary/5 outline-none focus:outline-none focus:ring-0 active:scale-95 transition-all select-none cursor-pointer">Uang Pas</button>
                                @foreach([50000, 100000, 150000, 200000] as $nom)
                                    <button type="button" @click="uangDiterima = {{ $nom }}; displayUang = formatPrice({{ $nom }})" class="bg-white border border-slate-200 shadow-sm rounded-xl py-3 px-2 text-center text-xs font-bold text-slate-700 hover:border-primary hover:text-primary outline-none focus:outline-none focus:ring-0 active:scale-95 transition-all select-none cursor-pointer">{{ number_format($nom/1000, 0) }}K</button>
                                @endforeach
                            </div>

                            {{-- NUMPAD CALCULATOR --}}
                            <div class="grid grid-cols-3 gap-2">
                                @foreach([7,8,9,4,5,6,1,2,3] as $n)
                                <button type="button" @click="appendNum({{ $n }})"
                                        class="bg-white border border-slate-200 text-slate-900 text-base font-bold py-2.5 rounded-xl hover:bg-slate-50 active:scale-95 outline-none focus:outline-none focus:ring-0 transition-all shadow-2xs select-none cursor-pointer">
                                    {{ $n }}
                                </button>
                                @endforeach
                                <button type="button" @click="appendNum('00')"
                                        class="bg-white border border-slate-200 text-slate-900 text-base font-bold py-2.5 rounded-xl hover:bg-slate-50 active:scale-95 outline-none focus:outline-none focus:ring-0 transition-all shadow-2xs select-none cursor-pointer">00</button>
                                <button type="button" @click="appendNum(0)"
                                        class="bg-white border border-slate-200 text-slate-900 text-base font-bold py-2.5 rounded-xl hover:bg-slate-50 active:scale-95 outline-none focus:outline-none focus:ring-0 transition-all shadow-2xs select-none cursor-pointer">0</button>
                                <button type="button" @click="uangDiterima = 0; displayUang = ''"
                                        class="bg-red-50 border border-red-200 text-red-600 text-base font-bold py-2.5 rounded-xl hover:bg-red-100 active:scale-95 outline-none focus:outline-none focus:ring-0 transition-all shadow-2xs select-none cursor-pointer">C</button>
                            </div>
                        </div>

                        {{-- ── 5. QRIS MANUAL SECTION ── --}}
                        <div x-show="metodeBayar === 'qris_manual'" style="display: none;" class="space-y-4">
                            
                            {{-- Clean, Modern QRIS Card Layout --}}
                            <div class="w-full flex flex-col items-center justify-center p-5 bg-white rounded-xl shadow-sm border border-slate-200">
                                
                                {{-- Header & Instructions --}}
                                <div class="text-center space-y-1 mb-3">
                                    <div class="flex items-center justify-center gap-1.5 text-primary font-bold text-lg tracking-tight">
                                        <i class="ph ph-qr-code"></i>
                                        <span>QRIS</span>
                                    </div>
                                    <p class="text-xs text-slate-500 max-w-[220px] leading-relaxed mx-auto">
                                        Minta pelanggan untuk memindai kode QRIS untuk melakukan pembayaran
                                    </p>
                                </div>

                                {{-- QR Code --}}
                                <div @click="showFullscreenQR = true" class="p-2.5 bg-white border border-slate-100 rounded-xl shadow-[0_0_15px_-3px_rgba(0,0,0,0.05)] mb-3 cursor-pointer hover:scale-105 transition-transform duration-200" title="Klik untuk memperbesar">
                                    <div class="w-44 h-44 flex items-center justify-center [&>svg]:w-full [&>svg]:h-full">
                                        {!! SimpleSoftwareIO\QrCode\Facades\QrCode::size(180)->margin(1)->generate($qrisString) !!}
                                    </div>
                                </div>

                                {{-- Details --}}
                                <div class="text-center space-y-0.5">
                                    <p class="text-xs font-semibold text-slate-700">Rumah Makan BBC</p>
                                    <p class="text-xl font-bold text-primary">
                                        Rp {{ number_format($totalTagihan, 0, ',', '.') }}
                                    </p>
                                    <p class="text-[10px] font-medium text-slate-400 tracking-wider">
                                        NMID: ID1026476129501
                                    </p>
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
                                    : 'bg-primary hover:bg-primary-container active:scale-[0.99] shadow-md'"
                                class="w-full py-3 rounded-xl text-white font-bold text-sm flex items-center justify-center gap-2 transition-all">
                            <span x-text="metodeBayar === 'tunai' && uangDiterima < totalTagihan ? 'NOMINAL KURANG' : 'BAYAR'">BAYAR</span>
                        </button>
                    </div>

                </form>

            </div>

        </div>

        {{-- ── FULLSCREEN QR MODAL ── --}}
        <div x-show="showFullscreenQR" 
             style="display: none;"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-90"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-90">
             
            <div class="relative bg-white w-full max-w-sm rounded-2xl p-5 flex flex-col items-center justify-center shadow-xl overflow-hidden max-h-[90vh]">
                {{-- Close Button --}}
                <button @click="showFullscreenQR = false" class="absolute top-3 right-3 w-7 h-7 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-full flex items-center justify-center transition-colors">
                    <x-heroicon-o-x-mark class="w-4 h-4" />
                </button>
                
                {{-- Header --}}
                <div class="text-center mb-3 mt-1">
                    <div class="flex items-center justify-center gap-1.5 text-primary font-bold text-lg tracking-tight mb-0.5">
                        <span>QRIS</span>
                    </div>
                    <p class="text-slate-500 text-xs font-medium">Scan untuk membayar</p>
                </div>
                
                {{-- Big QR Code --}}
                <div class="bg-white border border-slate-200 p-2.5 rounded-xl shadow-sm mb-3">
                    <div class="w-48 h-48 sm:w-56 sm:h-56 flex items-center justify-center [&>svg]:w-full [&>svg]:h-full">
                        {!! SimpleSoftwareIO\QrCode\Facades\QrCode::size(220)->margin(1)->generate($qrisString) !!}
                    </div>
                </div>
                
                {{-- Store Info --}}
                <div class="text-center w-full bg-slate-50 py-2.5 px-4 rounded-xl">
                    <p class="text-xs font-semibold text-slate-700 mb-0.5">Rumah Makan BBC</p>
                    <p class="text-xl font-bold text-primary mb-0.5">
                        Rp {{ number_format($totalTagihan, 0, ',', '.') }}
                    </p>
                    <p class="text-[10px] font-medium text-slate-400 tracking-wider">
                        NMID: ID1026476129501
                    </p>
                </div>
                
                <p class="mt-3 text-[10px] text-slate-400 font-medium">Tunjukkan layar ini ke pelanggan</p>
            </div>
        </div>

    </div>
@endsection
