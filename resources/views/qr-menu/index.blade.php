<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Menu Digital - Saung Babakan Cinta</title>
    <meta name="description" content="Menu digital restoran Saung Babakan Cinta. Scan QR, pilih menu, pesan langsung dari meja Anda.">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Anonymous+Pro:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', '"Google Sans"', 'sans-serif'],
                        mono: ['"Anonymous Pro"', 'monospace'],
                    },
                    colors: {
                        brand:   '#0F2E23',
                        primary: '#3B82F6',
                        surface: '#FFFFFF',
                        text:    '#111827',
                    }
                }
            }
        }
    </script>
    <style>
        .no-scrollbar::-webkit-scrollbar{display:none}
        .no-scrollbar{-ms-overflow-style:none;scrollbar-width:none}
        [x-cloak]{display:none!important}
        .mono{font-family:'Anonymous Pro',monospace;letter-spacing:.04em}
    </style>
</head>

<body class="bg-[#F8FAFC] text-text antialiased" x-data="qrMenu()" x-cloak>

<!-- ═══════════════ STICKY HEADER ═══════════════ -->
<header class="bg-white/95 backdrop-blur-md sticky top-0 z-40 border-b border-gray-200/60">
    <div class="max-w-lg mx-auto px-4 py-3 flex items-center justify-between">
        <!-- brand -->
        <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-xl bg-brand flex items-center justify-center text-emerald-300 shadow-sm">
                <i class="fas fa-utensils text-sm"></i>
            </div>
            <div>
                <h1 class="text-[15px] font-extrabold text-brand leading-none tracking-tight">BBC Resto</h1>
                <p class="text-[10px] text-gray-400 font-medium mt-0.5 leading-none">Saung Babakan Cinta</p>
            </div>
        </div>
        <!-- actions -->
        <div class="flex items-center gap-1.5">
            <a href="{{ route('qr.scanner') }}" class="h-8 px-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-[11px] font-bold text-brand hover:bg-emerald-100 transition-all flex items-center gap-1 shadow-2xs" title="Scan QR Kamera">
                <i class="fas fa-qrcode text-emerald-700 text-xs"></i>
                <span class="hidden sm:inline">Scan QR</span>
            </a>
            <button @click="modal='waiter'" class="h-8 px-2.5 rounded-xl bg-white border border-gray-200 text-[11px] font-semibold text-gray-600 hover:bg-gray-50 transition-all flex items-center gap-1.5">
                <i class="far fa-bell text-gray-400"></i>Bantuan
            </button>
            <button @click="modal='table'" class="h-8 px-3 rounded-xl bg-brand text-white text-[11px] font-bold hover:bg-brand/90 transition-all flex items-center gap-1.5 shadow-sm">
                <i class="fas fa-map-pin text-[9px] opacity-70"></i>
                <span x-text="mejaLabel"></span>
                <i class="fas fa-chevron-down text-[8px] opacity-50"></i>
            </button>
        </div>
    </div>

    <!-- search -->
    <div class="max-w-lg mx-auto px-4 pb-2.5">
        <div class="relative">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-[11px]"></i>
            <input x-model="q" type="text" placeholder="Cari menu favorit Anda..."
                   class="w-full h-9 pl-8 pr-8 bg-gray-50 border border-gray-200/60 rounded-xl text-xs font-medium placeholder-gray-400 focus:outline-none focus:bg-white focus:border-brand/40 focus:ring-2 focus:ring-brand/10 transition-all">
            <button x-show="q" @click="q=''" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-[11px]">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <!-- categories -->
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
            {{ $k->nama }}
        </button>
        @endforeach
    </div>
</header>

<!-- ═══════════════ MAIN CONTENT ═══════════════ -->
<main class="max-w-lg mx-auto px-4 pt-4 pb-28">

    <!-- table alert -->
    <div x-show="!mejaId" class="mb-4 flex items-center justify-between gap-3 bg-white border border-gray-200 rounded-xl px-4 py-2.5">
        <p class="text-[11px] text-gray-500 flex items-center gap-1.5"><i class="fas fa-info-circle text-gray-400"></i>Nomor meja belum dipilih</p>
        <button @click="modal='table'" class="text-[11px] font-bold text-brand hover:underline">Pilih</button>
    </div>

    <!-- menu grid -->
        <div class="grid grid-cols-2 gap-4">
        <template x-for="m in filtered" :key="m.id">
            <div class="group cursor-pointer flex flex-col gap-3" @click="detail(m)">
                <!-- image -->
                <div class="relative w-full aspect-[4/3] rounded-2xl overflow-hidden bg-gray-50 border border-gray-100">
                    <template x-if="m.foto">
                        <img :src="'/storage/'+m.foto" :alt="m.nama" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy">
                    </template>
                    <template x-if="!m.foto">
                        <div class="w-full h-full flex items-center justify-center bg-gray-50 text-gray-300">
                            <span class="text-3xl font-black tracking-widest" x-text="m.nama.split(' ').slice(0,3).map(n => n[0]).join('').toUpperCase()"></span>
                        </div>
                    </template>
                    <template x-if="m.status==='habis' || m.is_habis">
                        <div class="absolute inset-0 bg-white/50 backdrop-blur-[1px] flex items-center justify-center">
                            <span class="bg-gray-800 text-white text-[10px] font-bold px-3 py-1 rounded-full uppercase shadow-md">HABIS</span>
                        </div>
                    </template>
                </div>
                <!-- info -->
                <div class="flex flex-col flex-1 px-1">
                    <h3 class="text-[13px] font-semibold text-gray-900 leading-snug line-clamp-2" x-text="m.nama"></h3>
                    
                    <div class="mt-auto pt-3 flex items-center justify-between">
                        <span class="text-[13px] font-bold text-gray-900" x-text="rp(m.harga)"></span>
                        <template x-if="m.status!=='habis' && !m.is_habis">
                            <div>
                                <template x-if="qty(m.id)===0">
                                    <button @click.stop="add(m)" class="bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-full w-8 h-8 flex items-center justify-center transition-colors">
                                        <i class="fas fa-plus text-xs"></i>
                                    </button>
                                </template>
                                <template x-if="qty(m.id)>0">
                                    <div class="flex items-center gap-0.5 bg-gray-100 rounded-full text-gray-700">
                                        <button @click.stop="dec(m.id)" class="w-8 h-8 flex items-center justify-center rounded-l-full hover:bg-gray-200"><i class="fas fa-minus text-xs"></i></button>
                                        <span class="text-[11px] font-bold w-4 text-center" x-text="qty(m.id)"></span>
                                        <button @click.stop="add(m)" class="w-8 h-8 flex items-center justify-center rounded-r-full hover:bg-gray-200"><i class="fas fa-plus text-xs"></i></button>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- empty -->
    <div x-show="filtered.length===0" class="py-16 text-center">
        <div class="w-14 h-14 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3 text-gray-300"><i class="fas fa-utensils text-xl"></i></div>
        <p class="text-sm font-medium text-gray-900">Menu tidak ditemukan</p>
        <p class="text-xs text-gray-400 mt-1">Coba ubah kata kunci atau filter kategori.</p>
    </div>
</main>

<!-- ═══════════════ FLOATING CART BAR ═══════════════ -->
<div x-show="totalQty>0" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-y-0" x-transition:leave-end="translate-y-full"
     class="fixed bottom-0 inset-x-0 z-40 p-3 flex justify-center pointer-events-none">
    <div @click="modal='cart'" class="pointer-events-auto w-full max-w-lg bg-brand text-white rounded-2xl px-4 py-3 flex items-center justify-between cursor-pointer hover:bg-brand/95 transition-all shadow-xl shadow-brand/20 border border-emerald-800/20">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-white/15 flex items-center justify-center text-sm font-extrabold" x-text="totalQty"></div>
            <div>
                <span class="mono text-[9px] text-emerald-300 block uppercase">Total Pesanan</span>
                <span class="text-[15px] font-bold" x-text="rp(totalPrice)"></span>
            </div>
        </div>
        <div class="flex items-center gap-1.5 bg-white/15 px-3.5 py-1.5 rounded-xl text-[11px] font-bold">
            Lihat Pesanan <i class="fas fa-arrow-right text-[9px]"></i>
        </div>
    </div>
</div>

<!-- ═══════════════ MODAL: DETAIL MENU ═══════════════ -->
<div x-show="modal==='detail'" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center" x-transition.opacity>
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="modal=null"></div>
    <div class="relative bg-white w-full max-w-md rounded-t-2xl sm:rounded-2xl overflow-hidden shadow-2xl border border-gray-100 max-h-[88vh] flex flex-col z-10">
        <!-- image -->
        <div class="relative aspect-video bg-gray-50 shrink-0">
            <template x-if="dm.foto"><img :src="'/storage/'+dm.foto" :alt="dm.nama" class="w-full h-full object-cover"></template>
            <template x-if="!dm.foto">
                <div class="w-full h-full flex items-center justify-center bg-gray-100 text-gray-300">
                    <span class="text-5xl font-black tracking-widest" x-text="dm.nama.split(' ').slice(0,3).map(n => n[0]).join('').toUpperCase()"></span>
                </div>
            </template>
            <button @click="modal=null" class="absolute top-3 right-3 w-8 h-8 rounded-full bg-black/40 text-white flex items-center justify-center text-xs backdrop-blur-sm hover:bg-black/60 transition"><i class="fas fa-times"></i></button>
        </div>
        <!-- body -->
        <div class="p-5 overflow-y-auto flex-1 space-y-4">
            <div class="flex items-start justify-between gap-3">
                <h2 class="text-base font-bold text-gray-900 leading-snug" x-text="dm.nama"></h2>
                <span class="text-base font-extrabold text-brand whitespace-nowrap" x-text="rp(dm.harga)"></span>
            </div>
            <p class="text-xs text-gray-500 leading-relaxed" x-text="dm.deskripsi||'Sajian lezat khas Saung Babakan Cinta.'"></p>
            <!-- notes -->
            <div>
                <label class="block text-[11px] font-semibold text-gray-700 mb-1.5">Catatan Khusus</label>
                <input x-model="dNote" type="text" placeholder="Contoh: Tidak pedas, extra sambal..."
                       class="w-full h-9 px-3 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:outline-none focus:border-brand/40 focus:ring-2 focus:ring-brand/10 transition-all">
            </div>
            <!-- qty stepper -->
            <div class="flex items-center justify-between bg-gray-50 border border-gray-200/60 rounded-xl px-4 py-2.5">
                <span class="text-xs font-semibold text-gray-700">Jumlah</span>
                <div class="flex items-center gap-2.5">
                    <button @click="dQty=Math.max(1,dQty-1)" class="w-7 h-7 rounded-lg bg-white border border-gray-200 flex items-center justify-center text-xs text-gray-700 hover:bg-gray-100 transition"><i class="fas fa-minus"></i></button>
                    <span class="text-sm font-bold text-brand w-5 text-center" x-text="dQty"></span>
                    <button @click="dQty++" class="w-7 h-7 rounded-lg bg-white border border-gray-200 flex items-center justify-center text-xs text-gray-700 hover:bg-gray-100 transition"><i class="fas fa-plus"></i></button>
                </div>
            </div>
        </div>
        <!-- footer -->
        <div class="p-4 border-t border-gray-100 bg-gray-50/50">
            <button @click="addDetail()" class="w-full h-11 bg-brand text-white font-bold text-xs rounded-xl hover:bg-brand/90 transition-all shadow-sm flex items-center justify-center gap-2">
                <i class="fas fa-cart-plus"></i>Tambah — <span x-text="rp(dm.harga*dQty)"></span>
            </button>
        </div>
    </div>
</div>

<!-- ═══════════════ MODAL: CART / CHECKOUT ═══════════════ -->
<div x-show="modal==='cart'" class="fixed inset-0 z-50 flex items-end justify-center" x-transition.opacity>
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="modal=null"></div>
    <div class="relative bg-white w-full max-w-md rounded-t-2xl overflow-hidden shadow-2xl border border-gray-100 max-h-[88vh] flex flex-col z-10">
        <!-- header -->
        <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between shrink-0">
            <h2 class="mono text-[11px] font-bold text-gray-900 uppercase">Pesanan Saya</h2>
            <button @click="modal=null" class="w-7 h-7 rounded-lg bg-gray-100 text-gray-500 flex items-center justify-center text-[11px] hover:bg-gray-200 transition"><i class="fas fa-times"></i></button>
        </div>
        <!-- customer form -->
        <div class="px-5 py-3 bg-gray-50/80 border-b border-gray-100 grid grid-cols-2 gap-2.5 shrink-0">
            <div>
                <label class="block text-[10px] font-semibold text-gray-500 mb-1">Nama Pemesan</label>
                <input x-model="nama" type="text" placeholder="Nama Anda"
                       class="w-full h-8 px-2.5 bg-white border border-gray-200 rounded-lg text-xs font-medium focus:outline-none focus:border-brand/40 transition">
            </div>
            <div>
                <label class="block text-[10px] font-semibold text-gray-500 mb-1">Nomor Meja</label>
                <select x-model="mejaId" @change="syncMeja()" class="w-full h-8 px-2 bg-white border border-gray-200 rounded-lg text-xs font-medium focus:outline-none focus:border-brand/40 transition">
                    <option value="">Pilih</option>
                    @foreach($mejas as $m)
                    @php $cleanNomor = trim(preg_replace('/^meja\s*/i', '', $m->nomor_meja)); @endphp
                    <option value="{{ $m->id }}">Meja {{ $cleanNomor }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <!-- items -->
        <div class="px-5 overflow-y-auto flex-1 divide-y divide-gray-100">
            <template x-for="c in cart" :key="c.id">
                <div class="py-3 flex items-start gap-3">
                    <div class="flex-1 min-w-0">
                        <h4 class="text-xs font-semibold text-gray-900 truncate" x-text="c.nama"></h4>
                        <span class="text-[11px] font-bold text-brand" x-text="rp(c.harga)"></span>
                        <template x-if="c.catatan">
                            <p class="text-[10px] text-gray-400 italic mt-0.5 truncate" x-text="c.catatan"></p>
                        </template>
                    </div>
                    <div class="flex items-center gap-1.5 shrink-0">
                        <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden">
                            <button @click="dec(c.id)" class="w-6 h-7 flex items-center justify-center text-[10px] text-gray-600 hover:bg-gray-50"><i class="fas fa-minus"></i></button>
                            <span class="text-[11px] font-bold px-1.5" x-text="c.qty"></span>
                            <button @click="inc(c.id)" class="w-6 h-7 flex items-center justify-center text-[10px] text-gray-600 hover:bg-gray-50"><i class="fas fa-plus"></i></button>
                        </div>
                        <button @click="rm(c.id)" class="text-gray-400 hover:text-red-500 transition text-[11px] p-1"><i class="fas fa-trash-alt"></i></button>
                    </div>
                </div>
            </template>
        </div>
        <!-- footer -->
        <div class="p-4 border-t border-gray-100 bg-white shrink-0 space-y-3">
            <div class="flex justify-between items-baseline">
                <span class="mono text-[10px] font-bold text-gray-500 uppercase">Total Tagihan</span>
                <span class="text-lg font-extrabold text-brand" x-text="rp(totalPrice)"></span>
            </div>
            <button @click="submit()" :disabled="sending||!nama||!mejaId||cart.length===0"
                    :class="sending||!nama||!mejaId ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : 'bg-brand text-white hover:bg-brand/90 shadow-sm'"
                    class="w-full h-11 rounded-xl font-bold text-xs transition-all flex items-center justify-center gap-2">
                <i class="fas" :class="sending?'fa-spinner animate-spin':'fa-paper-plane'"></i>
                <span x-text="sending?'Mengirim…':'Kirim Pesanan ke Dapur'"></span>
            </button>
        </div>
    </div>
</div>

<!-- ═══════════════ MODAL: TABLE PICKER ═══════════════ -->
<div x-show="modal==='table'" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-transition.opacity>
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="modal=null"></div>
    <div class="relative bg-white w-full max-w-xs rounded-2xl p-5 shadow-2xl border border-gray-100 z-10">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-gray-900">Pilih Meja Anda</h3>
            <button @click="modal=null" class="text-gray-400 hover:text-gray-600 text-sm"><i class="fas fa-times"></i></button>
        </div>
        <div class="grid grid-cols-3 gap-2 max-h-52 overflow-y-auto">
            @foreach($mejas as $m)
            @php $cleanNomor = trim(preg_replace('/^meja\s*/i', '', $m->nomor_meja)); @endphp
            <button @click="mejaId='{{ $m->id }}';mejaNomor='{{ $cleanNomor }}';modal=null"
                    :class="mejaId=='{{ $m->id }}' ? 'bg-brand text-white border-brand shadow-sm' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50'"
                    class="border rounded-xl py-2.5 text-center transition-all">
                <span class="block text-[9px] text-gray-400 uppercase font-semibold leading-none" :class="mejaId=='{{ $m->id }}'&&'text-emerald-300'">Meja</span>
                <span class="block text-sm font-bold mt-0.5" :class="mejaId=='{{ $m->id }}'&&'text-white'">{{ $cleanNomor }}</span>
            </button>
            @endforeach
        </div>
    </div>
</div>

<!-- ═══════════════ MODAL: CALL WAITER ═══════════════ -->
<div x-show="modal==='waiter'" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-transition.opacity>
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="modal=null"></div>
    <div class="relative bg-white w-full max-w-xs rounded-2xl p-5 shadow-2xl border border-gray-100 z-10">
        <div class="flex items-center justify-between mb-1">
            <h3 class="text-sm font-bold text-gray-900">Minta Bantuan</h3>
            <button @click="modal=null" class="text-gray-400 hover:text-gray-600 text-sm"><i class="fas fa-times"></i></button>
        </div>
        <p class="text-[11px] text-gray-400 mb-3.5">Staf kami akan segera menuju meja Anda.</p>
        <div class="space-y-1.5">
            <button @click="callWaiter('Panggil Pelayan')" class="w-full h-10 bg-white border border-gray-200 hover:bg-gray-50 rounded-xl text-xs font-semibold text-gray-700 text-left px-4 flex items-center gap-2.5 transition">
                <i class="fas fa-user text-brand/60 w-4 text-center"></i>Panggil Pelayan
            </button>
            <button @click="callWaiter('Minta Bill')" class="w-full h-10 bg-white border border-gray-200 hover:bg-gray-50 rounded-xl text-xs font-semibold text-gray-700 text-left px-4 flex items-center gap-2.5 transition">
                <i class="fas fa-receipt text-brand/60 w-4 text-center"></i>Minta Bill / Struk
            </button>
            <button @click="callWaiter('Tambah Alat Makan')" class="w-full h-10 bg-white border border-gray-200 hover:bg-gray-50 rounded-xl text-xs font-semibold text-gray-700 text-left px-4 flex items-center gap-2.5 transition">
                <i class="fas fa-spoon text-brand/60 w-4 text-center"></i>Tambah Sendok & Garpu
            </button>
        </div>
    </div>
</div>

<!-- ═══════════════ MODAL: SUCCESS ═══════════════ -->
<div x-show="modal==='success'" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-transition.opacity>
    <div class="absolute inset-0 bg-gray-900/70 backdrop-blur-sm"></div>
    <div class="relative bg-white w-full max-w-xs rounded-2xl p-6 shadow-2xl border border-gray-100 text-center z-10">
        <div class="w-14 h-14 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-4 text-xl border border-emerald-200">
            <i class="fas fa-check"></i>
        </div>
        <h2 class="text-base font-bold text-gray-900 mb-1">Pesanan Terkirim!</h2>
        <p class="text-xs text-gray-400 mb-4 leading-relaxed">Pesanan Anda telah diterima oleh dapur. Silakan bersantai — hidangan segera disajikan.</p>
        <div class="bg-gray-50 border border-gray-200/70 rounded-xl p-3.5 mb-4 text-left space-y-1.5">
            <div class="flex justify-between text-xs">
                <span class="text-gray-400">Kode</span>
                <span class="mono font-bold text-gray-900" x-text="lastCode"></span>
            </div>
            <div class="flex justify-between text-xs">
                <span class="text-gray-400">Meja</span>
                <span class="font-bold text-brand" x-text="'Meja '+mejaNomor"></span>
            </div>
        </div>
        <button @click="modal=null" class="w-full h-10 bg-brand text-white font-bold text-xs rounded-xl hover:bg-brand/90 transition shadow-sm">
            Kembali ke Menu
        </button>
    </div>
</div>

<!-- ═══════════════ ALPINE LOGIC ═══════════════ -->
<script>
function qrMenu(){
    const allMenus = @json($menus);
    const allMejas = @json($mejas);
    return {
        q:'', cat:'all', modal:null,
        mejaId:'{{ $selectedMeja->id ?? "" }}', 
        mejaNomor:'{{ isset($selectedMeja) ? trim(preg_replace("/^meja\s*/i", "", $selectedMeja->nomor_meja)) : "" }}',
        cart:[], nama:'', sending:false, lastCode:'',
        dm:{}, dQty:1, dNote:'',

        init(){
            if(!this.mejaId && allMejas.length){
                this.mejaId = String(allMejas[0].id);
                this.mejaNomor = allMejas[0].nomor_meja.replace(/^meja\s*/i, '').trim();
            }
        },

        get mejaLabel(){ return this.mejaNomor ? 'Meja '+this.mejaNomor : 'Pilih Meja'; },

        get filtered(){
            let r = allMenus;
            if(this.q.trim()){ const s=this.q.toLowerCase(); r=r.filter(m=>m.nama.toLowerCase().includes(s)||(m.deskripsi&&m.deskripsi.toLowerCase().includes(s))); }
            if(this.cat!=='all') r=r.filter(m=>m.kategori_menu_id==this.cat);
            return r;
        },

        get totalQty(){ return this.cart.reduce((s,c)=>s+c.qty,0); },
        get totalPrice(){ return this.cart.reduce((s,c)=>s+(c.harga*c.qty),0); },

        qty(id){ const c=this.cart.find(x=>x.id===id); return c?c.qty:0; },

        add(m){ const c=this.cart.find(x=>x.id===m.id); c?c.qty++:this.cart.push({id:m.id,nama:m.nama,harga:m.harga,qty:1,catatan:''}); },
        inc(id){ const c=this.cart.find(x=>x.id===id); if(c) c.qty++; },
        dec(id){ const c=this.cart.find(x=>x.id===id); if(c){c.qty--; if(c.qty<=0) this.cart=this.cart.filter(x=>x.id!==id);} },
        rm(id){ this.cart=this.cart.filter(x=>x.id!==id); },

        detail(m){ this.dm=m; this.dQty=1; this.dNote=''; this.modal='detail'; },
        addDetail(){
            const c=this.cart.find(x=>x.id===this.dm.id);
            if(c){c.qty+=this.dQty; if(this.dNote) c.catatan=this.dNote;}
            else this.cart.push({id:this.dm.id,nama:this.dm.nama,harga:this.dm.harga,qty:this.dQty,catatan:this.dNote});
            this.modal=null;
        },

        syncMeja(){ const m=allMejas.find(x=>x.id==this.mejaId); if(m) this.mejaNomor=m.nomor_meja.replace(/^meja\s*/i, '').trim(); },

        rp(n){ return new Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR',maximumFractionDigits:0}).format(n); },

        async submit(){
            if(!this.nama||!this.mejaId||!this.cart.length) return;
            this.sending=true;
            try{
                const r=await fetch('{{ route("qr.menu.order") }}',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content,'Accept':'application/json'},body:JSON.stringify({meja_id:this.mejaId,nama_konsumen:this.nama,jumlah_tamu:1,items:this.cart.map(c=>({menu_id:c.id,qty:c.qty,catatan:c.catatan}))})});
                const d=await r.json();
                if(d.success){this.lastCode=d.kode_pesanan;this.cart=[];this.modal='success';}
                else alert(d.message||'Gagal mengirim pesanan.');
            }catch(e){console.error(e);alert('Terjadi kesalahan koneksi.');}
            finally{this.sending=false;}
        },

        async callWaiter(reason){
            if(!this.mejaId){this.modal='table';return;}
            try{await fetch('{{ route("qr.menu.panggil-pelayan") }}',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content,'Accept':'application/json'},body:JSON.stringify({meja_id:this.mejaId,alasan:reason})});
            alert('Panggilan dikirim untuk Meja '+this.mejaNomor);}catch(e){alert('Panggilan dikirim.');}
            this.modal=null;
        }
    };
}
</script>
</body>
</html>
