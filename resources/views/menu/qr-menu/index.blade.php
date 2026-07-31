<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Pesan Menu — Meja {{ $selectedMeja ? (Str::startsWith($selectedMeja->nomor_meja,'Meja') ? $selectedMeja->nomor_meja : 'Meja '.$selectedMeja->nomor_meja) : 'BBC Resto' }}</title>
    <meta name="description" content="Self-order digital BBC Resto. Pilih menu, pesan dari meja Anda.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['"Plus Jakarta Sans"', 'sans-serif'] },
                    colors: {
                        brand:   '#0F2E23',
                        accent:  '#16A34A',
                        surface: '#F8FAFC',
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak]{display:none!important}
        .no-scrollbar::-webkit-scrollbar{display:none}
        .no-scrollbar{-ms-overflow-style:none;scrollbar-width:none}
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .card-shadow { box-shadow: 0 1px 3px rgba(0,0,0,.06), 0 1px 2px rgba(0,0,0,.04); }
        .card-hover { transition: transform .15s ease, box-shadow .15s ease; }
        .card-hover:active { transform: scale(.98); }
    </style>
</head>

<body class="bg-surface text-gray-900 antialiased" x-data="qrMenu()" x-cloak>

{{-- ═══ STICKY HEADER ═══ --}}
<header class="bg-white sticky top-0 z-40 border-b border-gray-100">
    <div class="max-w-lg mx-auto px-4 py-3 flex items-center justify-between">
        {{-- Brand --}}
        <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-xl bg-brand flex items-center justify-center shadow-sm">
                <x-heroicon-o-sparkles class="w-4 h-4 text-emerald-300" />
            </div>
            <div>
                <p class="text-[15px] font-extrabold text-brand leading-none">BBC Resto</p>
                <p class="text-[10px] text-gray-400 mt-0.5 leading-none">Saung Babakan Cinta</p>
            </div>
        </div>
        {{-- Meja Badge (locked from QR, tidak bisa diklik) --}}
        <div class="flex items-center gap-1.5">
            @if($selectedMeja)
            <div class="h-8 px-3 rounded-xl bg-brand text-white text-[11px] font-bold flex items-center gap-1.5 shadow-sm">
                <x-heroicon-o-users class="w-3 h-3 opacity-70" />
                <span>{{ Str::startsWith($selectedMeja->nomor_meja,'Meja') ? $selectedMeja->nomor_meja : 'Meja '.$selectedMeja->nomor_meja }}</span>
            </div>
            @endif
            <template x-if="namaUser">
                <div class="h-8 px-2.5 rounded-xl bg-gray-100 text-gray-700 text-[11px] font-semibold flex items-center gap-1.5">
                    <x-heroicon-o-user class="w-3 h-3 text-gray-400" />
                    <span x-text="namaUser" class="max-w-[80px] truncate"></span>
                </div>
            </template>
        </div>
    </div>

    {{-- Search --}}
    <div class="max-w-lg mx-auto px-4 pb-2.5">
        <div class="relative">
            <x-heroicon-o-sparkles class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 w-3 h-3" />
            <input x-model="q" type="text" placeholder="Cari menu favorit Anda..."
                   class="w-full h-9 pl-8 pr-8 bg-gray-50 border border-gray-200/70 rounded-xl text-xs font-medium placeholder-gray-400 focus:outline-none focus:bg-white focus:border-brand/40 focus:ring-2 focus:ring-brand/10 transition-all">
            <button x-show="q" @click="q=''" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-[11px]">
                <x-heroicon-o-x-mark class="w-4 h-4" />
            </button>
        </div>
    </div>

    {{-- Categories --}}
    <div class="max-w-lg mx-auto px-4 pb-2.5 overflow-x-auto no-scrollbar flex gap-1.5">
        <button @click="cat='all'"
                :class="cat==='all' ? 'bg-brand text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'"
                class="shrink-0 h-7 px-3.5 rounded-lg text-[11px] font-bold transition-all">
            Semua
        </button>
        @foreach($kategoris as $k)
        <button @click="cat='{{ $k->id }}'"
                :class="cat=='{{ $k->id }}' ? 'bg-brand text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'"
                class="shrink-0 h-7 px-3.5 rounded-lg text-[11px] font-bold transition-all whitespace-nowrap">
            {{ $k->nama_kategori ?? $k->nama }}
        </button>
        @endforeach
    </div>
</header>

{{-- ═══ MAIN CONTENT ═══ --}}
<main class="max-w-lg mx-auto px-4 pt-5 pb-32">

    {{-- Menu Grid --}}
    <div class="grid grid-cols-2 gap-3.5">
        <template x-for="m in filtered" :key="m.id">
            <div class="bg-white rounded-2xl overflow-hidden card-shadow card-hover cursor-pointer border border-gray-100"
                 @click="openDetail(m)">
                {{-- Image --}}
                <div class="relative w-full aspect-[4/3] bg-gray-50">
                    <template x-if="m.foto">
                        <img :src="'/storage/'+m.foto" :alt="m.nama"
                             class="w-full h-full object-cover" loading="lazy">
                    </template>
                    <template x-if="!m.foto">
                        <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-50">
                            <span class="text-3xl font-black text-gray-200 tracking-widest"
                                  x-text="m.nama.split(' ').slice(0,3).map(n=>n[0]).join('').toUpperCase()"></span>
                        </div>
                    </template>
                    {{-- Habis badge --}}
                    <template x-if="m.status==='habis'||m.is_habis">
                        <div class="absolute inset-0 bg-white/60 backdrop-blur-[2px] flex items-center justify-center">
                            <span class="bg-gray-800 text-white text-[9px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider shadow">HABIS</span>
                        </div>
                    </template>
                    {{-- Qty badge --}}
                    <template x-if="qty(m.id)>0">
                        <div class="absolute top-2 right-2 w-6 h-6 rounded-full bg-brand text-white text-[10px] font-black flex items-center justify-center shadow-lg"
                             x-text="qty(m.id)"></div>
                    </template>
                </div>
                {{-- Info --}}
                <div class="p-3">
                    <h3 class="text-[12.5px] font-semibold text-gray-900 leading-snug line-clamp-2 mb-2" x-text="m.nama"></h3>
                    <div class="flex items-center justify-between">
                        <span class="text-[13px] font-bold text-brand" x-text="rp(m.harga)"></span>
                        <template x-if="m.status!=='habis'&&!m.is_habis">
                            <template x-if="qty(m.id)===0">
                                <button @click.stop="add(m)"
                                        class="w-8 h-8 rounded-full bg-brand text-white flex items-center justify-center shadow-sm hover:bg-brand/90 transition-colors">
                                    <x-heroicon-o-plus class="w-3 h-3" />
                                </button>
                            </template>
                        </template>
                        <template x-if="m.status!=='habis'&&!m.is_habis&&qty(m.id)>0">
                            <div class="flex items-center gap-1 bg-brand rounded-full text-white">
                                <button @click.stop="dec(m.id)" class="w-7 h-7 flex items-center justify-center hover:bg-white/10 rounded-l-full transition">
                                    <x-heroicon-o-sparkles class="w-3 h-3" />
                                </button>
                                <span class="text-[11px] font-bold w-4 text-center" x-text="qty(m.id)"></span>
                                <button @click.stop="add(m)" class="w-7 h-7 flex items-center justify-center hover:bg-white/10 rounded-r-full transition">
                                    <x-heroicon-o-plus class="w-3 h-3" />
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </template>
    </div>

    {{-- Empty state --}}
    <div x-show="filtered.length===0" class="py-20 text-center">
        <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4 text-gray-300">
            <x-heroicon-o-sparkles class="w-8 h-8" />
        </div>
        <p class="text-sm font-semibold text-gray-800">Menu tidak ditemukan</p>
        <p class="text-xs text-gray-400 mt-1">Coba ubah kata kunci atau kategori.</p>
    </div>
</main>

{{-- ═══ FLOATING CART BAR ═══ --}}
<div x-show="totalQty>0"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="translate-y-full opacity-0"
     x-transition:enter-end="translate-y-0 opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="translate-y-0 opacity-100"
     x-transition:leave-end="translate-y-full opacity-0"
     class="fixed bottom-0 inset-x-0 z-40 p-4 flex justify-center pointer-events-none">
    <div @click="modal='cart'"
         class="pointer-events-auto w-full max-w-lg bg-brand text-white rounded-2xl px-5 py-3.5 flex items-center justify-between cursor-pointer hover:bg-brand/95 transition-all shadow-2xl shadow-brand/25 border border-emerald-800/30">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-white/15 flex items-center justify-center text-sm font-extrabold" x-text="totalQty"></div>
            <div>
                <span class="text-[9px] text-emerald-300 block uppercase font-semibold tracking-wider">Total Pesanan</span>
                <span class="text-[15px] font-bold" x-text="rp(totalPrice)"></span>
            </div>
        </div>
        <div class="flex items-center gap-2 bg-white/15 px-3.5 py-2 rounded-xl text-[11px] font-bold">
            Lihat Keranjang <x-heroicon-o-arrow-right class="w-3 h-3" />
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════
     MODAL: WELCOME — Input Nama & No HP
═══════════════════════════════════════════ --}}
<div x-show="modal==='welcome'"
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm"
     x-transition.opacity>
    <div class="relative bg-white w-full max-w-sm rounded-3xl shadow-2xl border border-gray-100 overflow-hidden">
        {{-- Top decoration --}}
        <div class="bg-gradient-to-br from-brand to-emerald-700 px-6 pt-8 pb-6 text-white text-center">
            <div class="w-14 h-14 rounded-2xl bg-white/15 flex items-center justify-center mx-auto mb-3 border border-white/20">
                <x-heroicon-o-sparkles class="w-7 h-7 text-emerald-200" />
            </div>
            <h2 class="text-lg font-extrabold leading-snug">Selamat Datang!</h2>
            <p class="text-[12px] text-emerald-200 mt-1">
                @if($selectedMeja)
                    {{ Str::startsWith($selectedMeja->nomor_meja,'Meja') ? $selectedMeja->nomor_meja : 'Meja '.$selectedMeja->nomor_meja }}
                    &bull; BBC Resto
                @else
                    BBC Resto &bull; Saung Babakan Cinta
                @endif
            </p>
        </div>

        {{-- Form --}}
        <div class="p-6 space-y-4">
            <div>
                <label class="block text-[11px] font-bold text-gray-500 mb-1.5 uppercase tracking-wider">
                    Nama Pemesan <span class="text-red-400">*</span>
                </label>
                <input x-model="namaInput" type="text" placeholder="Masukkan nama Anda..."
                       maxlength="60" autofocus
                       class="w-full h-11 px-4 bg-gray-50 border border-gray-200 rounded-xl text-sm font-medium placeholder-gray-400 focus:outline-none focus:bg-white focus:border-brand/50 focus:ring-2 focus:ring-brand/10 transition-all">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-gray-500 mb-1.5 uppercase tracking-wider">
                    No. HP <span class="text-gray-400 normal-case font-normal">(opsional)</span>
                </label>
                <input x-model="nomorHpInput" type="tel" placeholder="Contoh: 0812xxxxxxxx"
                       maxlength="16"
                       class="w-full h-11 px-4 bg-gray-50 border border-gray-200 rounded-xl text-sm font-medium placeholder-gray-400 focus:outline-none focus:bg-white focus:border-brand/50 focus:ring-2 focus:ring-brand/10 transition-all">
            </div>

            <button @click="konfirmasiPemesan()"
                    :disabled="!namaInput.trim()"
                    :class="!namaInput.trim() ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : 'bg-brand text-white hover:bg-brand/90 shadow-sm active:scale-[.98]'"
                    class="w-full h-12 rounded-xl font-bold text-sm transition-all flex items-center justify-center gap-2 mt-2">
                <x-heroicon-o-arrow-right class="w-3 h-3" />
                Mulai Pesan
            </button>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════
     MODAL: DETAIL MENU
═══════════════════════════════════════════ --}}
<div x-show="modal==='detail'"
     class="fixed inset-0 z-50 flex items-end sm:items-center justify-center"
     x-transition.opacity>
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="modal=null"></div>
    <div class="relative bg-white w-full max-w-md rounded-t-3xl sm:rounded-3xl overflow-hidden shadow-2xl border border-gray-100 max-h-[88vh] flex flex-col z-10">
        {{-- Image --}}
        <div class="relative aspect-video bg-gray-50 shrink-0">
            <template x-if="dm.foto">
                <img :src="'/storage/'+dm.foto" :alt="dm.nama" class="w-full h-full object-cover">
            </template>
            <template x-if="!dm.foto">
                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-50">
                    <span class="text-5xl font-black text-gray-200 tracking-widest"
                          x-text="dm.nama ? dm.nama.split(' ').slice(0,3).map(n=>n[0]).join('').toUpperCase() : ''"></span>
                </div>
            </template>
            <button @click="modal=null"
                    class="absolute top-3 right-3 w-8 h-8 rounded-full bg-black/40 text-white flex items-center justify-center text-xs backdrop-blur-sm hover:bg-black/60 transition">
                <x-heroicon-o-x-mark class="w-4 h-4" />
            </button>
        </div>
        {{-- Body --}}
        <div class="p-5 overflow-y-auto flex-1 space-y-4">
            <div class="flex items-start justify-between gap-3">
                <h2 class="text-base font-bold text-gray-900 leading-snug" x-text="dm.nama"></h2>
                <span class="text-base font-extrabold text-brand whitespace-nowrap" x-text="rp(dm.harga)"></span>
            </div>
            <p class="text-xs text-gray-500 leading-relaxed" x-text="dm.deskripsi||'Sajian lezat khas Saung Babakan Cinta.'"></p>
            {{-- Catatan --}}
            <div>
                <label class="block text-[11px] font-semibold text-gray-700 mb-1.5">Catatan Khusus</label>
                <input x-model="dNote" type="text" placeholder="Contoh: Tidak pedas, extra sambal..."
                       class="w-full h-9 px-3 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:outline-none focus:border-brand/40 focus:ring-2 focus:ring-brand/10 transition-all">
            </div>
            {{-- Qty stepper --}}
            <div class="flex items-center justify-between bg-gray-50 border border-gray-200/70 rounded-xl px-4 py-3">
                <span class="text-xs font-semibold text-gray-700">Jumlah</span>
                <div class="flex items-center gap-3">
                    <button @click="dQty=Math.max(1,dQty-1)"
                            class="w-8 h-8 rounded-xl bg-white border border-gray-200 flex items-center justify-center text-xs text-gray-700 hover:bg-gray-100 transition">
                        <x-heroicon-o-minus class="w-4 h-4" />
                    </button>
                    <span class="text-sm font-bold text-brand w-5 text-center" x-text="dQty"></span>
                    <button @click="dQty++"
                            class="w-8 h-8 rounded-xl bg-white border border-gray-200 flex items-center justify-center text-xs text-gray-700 hover:bg-gray-100 transition">
                        <x-heroicon-o-plus class="w-5 h-5" />
                    </button>
                </div>
            </div>
        </div>
        {{-- Footer --}}
        <div class="p-4 border-t border-gray-100 bg-gray-50/50 shrink-0">
            <button @click="addDetail()"
                    class="w-full h-12 bg-brand text-white font-bold text-sm rounded-xl hover:bg-brand/90 transition-all shadow-sm active:scale-[.98] flex items-center justify-center gap-2">
                <x-heroicon-o-sparkles class="w-3 h-3" />
                Tambah &mdash; <span x-text="rp(dm.harga*dQty)"></span>
            </button>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════
     MODAL: KERANJANG / CHECKOUT
═══════════════════════════════════════════ --}}
<div x-show="modal==='cart'"
     class="fixed inset-0 z-50 flex items-end justify-center"
     x-transition.opacity>
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="modal=null"></div>
    <div class="relative bg-white w-full max-w-md rounded-t-3xl overflow-hidden shadow-2xl border border-gray-100 max-h-[90vh] flex flex-col z-10">

        {{-- Header --}}
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between shrink-0">
            <div>
                <h2 class="text-sm font-extrabold text-gray-900">Keranjang Pesanan</h2>
                <p class="text-[10px] text-gray-400 mt-0.5">
                    @if($selectedMeja)
                        {{ Str::startsWith($selectedMeja->nomor_meja,'Meja') ? $selectedMeja->nomor_meja : 'Meja '.$selectedMeja->nomor_meja }}
                    @endif
                    &bull; <span x-text="namaUser"></span>
                </p>
            </div>
            <button @click="modal=null"
                    class="w-8 h-8 rounded-xl bg-gray-100 text-gray-500 flex items-center justify-center text-[11px] hover:bg-gray-200 transition">
                <x-heroicon-o-x-mark class="w-5 h-5" />
            </button>
        </div>

        {{-- Metode Pembayaran --}}
        <div class="px-5 py-3.5 bg-gray-50/70 border-b border-gray-100 shrink-0">
            <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-2">Metode Pembayaran</p>
            <div class="grid grid-cols-2 gap-2">
                <label @click="metodePembayaran='kasir'"
                       :class="metodePembayaran==='kasir'
                           ? 'border-brand bg-emerald-50 ring-2 ring-brand/10'
                           : 'border-gray-200 bg-white hover:bg-gray-50'"
                       class="cursor-pointer border rounded-xl p-3 flex items-center gap-2.5 transition-all">
                    <input type="radio" value="kasir" x-model="metodePembayaran" class="hidden">
                    <div class="w-8 h-8 rounded-xl bg-emerald-100 text-brand flex items-center justify-center text-sm shrink-0">
                        <x-heroicon-o-currency-dollar class="w-4 h-4" />
                    </div>
                    <div>
                        <span class="block text-[11px] font-bold text-gray-900 leading-tight">Bayar di Kasir</span>
                        <span class="block text-[9px] text-gray-400 mt-0.5">Tunai / Transfer</span>
                    </div>
                </label>
                <label @click="metodePembayaran='qris'"
                       :class="metodePembayaran==='qris'
                           ? 'border-brand bg-emerald-50 ring-2 ring-brand/10'
                           : 'border-gray-200 bg-white hover:bg-gray-50'"
                       class="cursor-pointer border rounded-xl p-3 flex items-center gap-2.5 transition-all">
                    <input type="radio" value="qris" x-model="metodePembayaran" class="hidden">
                    <div class="w-8 h-8 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center text-sm shrink-0">
                        <x-heroicon-o-qr-code class="w-4 h-4" />
                    </div>
                    <div>
                        <span class="block text-[11px] font-bold text-gray-900 leading-tight">Bayar QRIS</span>
                        <span class="block text-[9px] text-gray-400 mt-0.5">Scan langsung</span>
                    </div>
                </label>
            </div>
        </div>

        {{-- Items --}}
        <div class="px-5 overflow-y-auto flex-1 divide-y divide-gray-100 no-scrollbar">
            <template x-for="c in cart" :key="c.id">
                <div class="py-3.5 flex items-start gap-3">
                    <div class="flex-1 min-w-0">
                        <h4 class="text-xs font-semibold text-gray-900" x-text="c.nama"></h4>
                        <span class="text-[11px] font-bold text-brand" x-text="rp(c.harga)"></span>
                        <template x-if="c.catatan">
                            <p class="text-[10px] text-gray-400 italic mt-0.5 truncate" x-text="'📝 '+c.catatan"></p>
                        </template>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden">
                            <button @click="dec(c.id)" class="w-7 h-7 flex items-center justify-center text-[10px] text-gray-600 hover:bg-gray-100">
                                <x-heroicon-o-sparkles class="w-5 h-5" />
                            </button>
                            <span class="text-[11px] font-bold px-2" x-text="c.qty"></span>
                            <button @click="inc(c.id)" class="w-7 h-7 flex items-center justify-center text-[10px] text-gray-600 hover:bg-gray-100">
                                <x-heroicon-o-plus class="w-5 h-5" />
                            </button>
                        </div>
                        <button @click="rm(c.id)" class="text-gray-300 hover:text-red-400 transition text-sm p-1">
                            <x-heroicon-o-sparkles class="w-5 h-5" />
                        </button>
                    </div>
                </div>
            </template>
        </div>

        {{-- Footer --}}
        <div class="p-4 border-t border-gray-100 bg-white shrink-0 space-y-3">
            <div class="space-y-1">
                <div class="flex justify-between items-center text-[11px] text-gray-500 font-medium">
                    <span>Subtotal</span>
                    <span x-text="rp(subTotal)"></span>
                </div>
                <div class="flex justify-between items-center text-[11px] text-gray-500 font-medium pb-2 border-b border-gray-100">
                    <span>PB1 (10%)</span>
                    <span x-text="rp(totalTax)"></span>
                </div>
                <div class="flex justify-between items-end pt-1">
                    <div>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Total Tagihan</span>
                        <span class="text-xl font-extrabold text-brand" x-text="rp(totalPrice)"></span>
                    </div>
                    <span class="text-[11px] text-gray-400 font-medium mb-1" x-text="totalQty+' item'"></span>
                </div>
            </div>
            <button @click="submit()"
                    :disabled="sending||cart.length===0"
                    :class="sending||cart.length===0 ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : 'bg-brand text-white hover:bg-brand/90 active:scale-[.98]'"
                    class="w-full h-12 rounded-xl font-bold text-sm transition-all flex items-center justify-center gap-2 shadow-sm">
                <template x-if="sending">
                    <x-heroicon-o-arrow-path class="w-5 h-5 animate-spin" />
                </template>
                <template x-if="!sending && metodePembayaran === 'qris'">
                    <x-heroicon-o-qr-code class="w-5 h-5" />
                </template>
                <template x-if="!sending && metodePembayaran !== 'qris'">
                    <x-heroicon-o-paper-airplane class="w-5 h-5" />
                </template>
                <span x-text="sending?'Mengirim…':(metodePembayaran==='qris'?'Lanjut ke Pembayaran QRIS':'Kirim Pesanan ke Kasir')"></span>
            </button>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════
     MODAL: QRIS PAYMENT
═══════════════════════════════════════════ --}}
<div x-show="modal==='qris'"
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/70 backdrop-blur-sm"
     x-transition.opacity>
    <div class="relative bg-white w-full max-w-xs rounded-3xl p-6 shadow-2xl border border-gray-100 text-center">
        <button @click="closeQrisModal()" class="absolute top-3.5 right-3.5 text-gray-400 hover:text-gray-600 text-sm">
            <x-heroicon-o-x-mark class="w-5 h-5" />
        </button>

        <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 text-brand rounded-full text-[10px] font-bold border border-emerald-200 mb-3">
            <x-heroicon-o-qr-code class="w-5 h-5" /> Pembayaran QRIS
        </div>

        <h3 class="text-sm font-bold text-gray-900">Scan untuk Membayar</h3>
        <p class="text-[11px] text-gray-400 mt-0.5 mb-4">GoPay, OVO, ShopeePay, Dana, Mobile Banking</p>

        <div class="my-2 bg-white p-3 border-2 border-emerald-200 rounded-2xl inline-block">
            <template x-if="qrisData.qr_url">
                <img :src="qrisData.qr_url" alt="QRIS Code" class="w-48 h-48 object-contain rounded-lg mx-auto">
            </template>
            <template x-if="!qrisData.qr_url">
                <div class="w-48 h-48 flex items-center justify-center text-gray-400">
                    <x-heroicon-o-arrow-path class="w-8 h-8 animate-spin" />
                </div>
            </template>
        </div>

        <div class="bg-gray-50 border border-gray-200/70 rounded-xl p-3 mb-4 text-left space-y-1.5">
            <div class="flex justify-between text-xs">
                <span class="text-gray-400">Total</span>
                <span class="font-bold text-brand" x-text="rp(qrisData.gross_amount||totalPrice)"></span>
            </div>
            <div class="flex justify-between text-xs">
                <span class="text-gray-400">Kode Pesanan</span>
                <span class="font-mono font-bold text-gray-700 text-[10px]" x-text="qrisData.order_id||lastCode||'-'"></span>
            </div>
        </div>

        <div class="flex items-center justify-center gap-2 text-xs font-semibold text-amber-600 bg-amber-50 py-2.5 px-3 rounded-xl border border-amber-200">
            <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping shrink-0"></span>
            Menunggu Pembayaran...
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════
     MODAL: SUCCESS
═══════════════════════════════════════════ --}}
<div x-show="modal==='success'"
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/70 backdrop-blur-sm"
     x-transition.opacity>
    <div class="relative bg-white w-full max-w-xs rounded-3xl p-7 shadow-2xl border border-gray-100 text-center">
        <div class="w-16 h-16 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-emerald-200 shadow-sm">
            <x-heroicon-o-check class="w-7 h-7" />
        </div>
        <h2 class="text-base font-extrabold text-gray-900 mb-1">Pesanan Terkirim!</h2>
        <p class="text-xs text-gray-400 mb-5 leading-relaxed">
            Pesanan Anda sudah diterima. Silakan bersantai — hidangan akan segera disajikan.
        </p>
        <div class="bg-gray-50 border border-gray-200/70 rounded-xl p-3.5 mb-5 text-left space-y-2">
            <div class="flex justify-between text-xs">
                <span class="text-gray-400">Kode Pesanan</span>
                <span class="font-mono font-bold text-gray-900" x-text="lastCode"></span>
            </div>
            <div class="flex justify-between text-xs">
                <span class="text-gray-400">Meja</span>
                <span class="font-bold text-brand">
                    @if($selectedMeja){{ Str::startsWith($selectedMeja->nomor_meja,'Meja') ? $selectedMeja->nomor_meja : 'Meja '.$selectedMeja->nomor_meja }}@endif
                </span>
            </div>
            <div class="flex justify-between text-xs">
                <span class="text-gray-400">Pembayaran</span>
                <span class="font-bold text-gray-900"
                      x-text="metodePembayaran==='qris'?'QRIS (sudah dibayar)':'Bayar di Kasir'"></span>
            </div>
        </div>
        <button @click="modal=null; cart=[];"
                class="w-full h-11 bg-brand text-white font-bold text-sm rounded-xl hover:bg-brand/90 transition active:scale-[.98] shadow-sm">
            Tambah Pesanan Lagi
        </button>
    </div>
</div>

{{-- ═══ ALPINE LOGIC ═══ --}}
<script>
function qrMenu(){
    const allMenus = @json($menus);
    const MEJA_ID  = '{{ $selectedMeja->id ?? "" }}';
    const MEJA_NOMOR = '{{ $selectedMeja ? trim(preg_replace("/^meja\s*/i","", $selectedMeja->nomor_meja)) : "" }}';

    return {
        q:'', cat:'all', modal: MEJA_ID ? 'welcome' : null,
        mejaId: MEJA_ID,
        mejaNomor: MEJA_NOMOR,

        // Pemesan
        namaInput:'', nomorHpInput:'',
        namaUser:'', nomorHpUser:'',

        // Cart
        cart:[], sending:false, lastCode:'',
        dm:{}, dQty:1, dNote:'',
        metodePembayaran:'kasir',
        qrisData:{}, pollingTimer:null,

        // ── Computed ──────────────────────────────────
        get mejaLabel(){ return this.mejaNomor ? 'Meja '+this.mejaNomor : '—'; },
        get filtered(){
            let r = allMenus;
            if(this.q.trim()){ const s=this.q.toLowerCase(); r=r.filter(m=>m.nama.toLowerCase().includes(s)||(m.deskripsi&&m.deskripsi.toLowerCase().includes(s))); }
            if(this.cat!=='all') r=r.filter(m=>m.kategori_menu_id==this.cat);
            return r;
        },
        get totalQty(){ return this.cart.reduce((s,c)=>s+c.qty,0); },
        get subTotal(){ return this.cart.reduce((s,c)=>s+(c.harga*c.qty),0); },
        get totalTax(){ return this.subTotal * 0.10; },
        get totalPrice(){ return this.subTotal + this.totalTax; },

        // ── Cart actions ──────────────────────────────
        qty(id){ const c=this.cart.find(x=>x.id===id); return c?c.qty:0; },
        add(m){ const c=this.cart.find(x=>x.id===m.id); c?c.qty++:this.cart.push({id:m.id,nama:m.nama,harga:m.harga,qty:1,catatan:''}); },
        inc(id){ const c=this.cart.find(x=>x.id===id); if(c) c.qty++; },
        dec(id){ const c=this.cart.find(x=>x.id===id); if(c){c.qty--; if(c.qty<=0) this.cart=this.cart.filter(x=>x.id!==id);} },
        rm(id){ this.cart=this.cart.filter(x=>x.id!==id); },

        // ── Detail modal ──────────────────────────────
        openDetail(m){ this.dm=m; this.dQty=1; this.dNote=''; this.modal='detail'; },
        addDetail(){
            const c=this.cart.find(x=>x.id===this.dm.id);
            if(c){c.qty+=this.dQty; if(this.dNote) c.catatan=this.dNote;}
            else this.cart.push({id:this.dm.id,nama:this.dm.nama,harga:this.dm.harga,qty:this.dQty,catatan:this.dNote});
            this.modal=null;
        },

        // ── Welcome/konfirmasi pemesan ────────────────
        konfirmasiPemesan(){
            const nama = this.namaInput.trim();
            if(!nama) return;
            this.namaUser = nama;
            this.nomorHpUser = this.nomorHpInput.trim();
            this.modal = null;
        },

        // ── Format Rupiah ──────────────────────────────
        rp(n){ return new Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR',maximumFractionDigits:0}).format(n); },

        // ── Submit order ──────────────────────────────
        async submit(){
            if(!this.namaUser||!this.mejaId||!this.cart.length) return;
            this.sending=true;
            try{
                const r=await fetch('{{ route("qr.menu.order") }}',{
                    method:'POST',
                    headers:{
                        'Content-Type':'application/json',
                        'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content,
                        'Accept':'application/json'
                    },
                    body:JSON.stringify({
                        meja_id:this.mejaId,
                        nama_konsumen:this.namaUser,
                        nomor_hp:this.nomorHpUser||null,
                        jumlah_tamu:1,
                        metode_pembayaran:this.metodePembayaran,
                        items:this.cart.map(c=>({menu_id:c.id,qty:c.qty,catatan:c.catatan||null}))
                    })
                });
                const d=await r.json();
                if(d.success){
                    this.lastCode=d.kode_pesanan;
                    if(d.metode_pembayaran==='qris'&&d.qris){
                        this.qrisData=d.qris;
                        this.modal='qris';
                        this.startQrisPolling(d.qris.order_id);
                    } else {
                        this.cart=[];
                        this.modal='success';
                    }
                } else {
                    alert(d.message||'Gagal mengirim pesanan.');
                }
            }catch(e){
                console.error(e);
                alert('Terjadi kesalahan koneksi. Coba lagi.');
            }finally{
                this.sending=false;
            }
        },

        // ── QRIS polling ──────────────────────────────
        startQrisPolling(orderId){
            if(this.pollingTimer) clearInterval(this.pollingTimer);
            this.pollingTimer=setInterval(async()=>{
                try{
                    const res=await fetch(`/api/payment/status/${orderId}`);
                    const json=await res.json();
                    if(json.success&&json.data&&json.data.is_paid){
                        clearInterval(this.pollingTimer); this.pollingTimer=null;
                        this.cart=[]; this.modal='success';
                    }
                }catch(e){}
            },3000);
        },
        closeQrisModal(){
            if(this.pollingTimer){clearInterval(this.pollingTimer);this.pollingTimer=null;}
            this.modal=null;
        },
    };
}
</script>
</body>
</html>
