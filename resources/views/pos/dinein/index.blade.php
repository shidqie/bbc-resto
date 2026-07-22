@extends('layouts.pos')

@section('content')
{{-- ╔══════════════════════════════════════╗ --}}
{{-- ║  BBC RESTO — POINT OF SALE (POS)     ║ --}}
{{-- ╚══════════════════════════════════════╝ --}}
<style>
  .pos-root        { font-family: 'Plus Jakarta Sans', sans-serif; }
  .chip-active     { background:#0F2E23; color:#ffffff; border: 1px solid #0F2E23; }
  .chip-default    { background:#F3F4F6; color:#1F2937; border: 1px solid #E5E7EB; }
  .chip-default:hover { background:#E5E7EB; color:#111827; }
  .card-menu:hover { border-color:rgba(15,46,35,.35); box-shadow:0 4px 20px rgba(15,46,35,.08); }
  .pill            { display:inline-flex; align-items:center; gap:4px;
                     padding:3px 10px; border-radius:999px; font-size:12px; font-weight:700; }
  .pill-emerald    { background:#DEF7EC; color:#03543F; border:1px solid #BCF0DA; }
  .pill-amber      { background:#FEF08A; color:#713F12; border:1px solid #FDE047; }
  .pill-red        { background:#FEE2E2; color:#991B1B; border:1px solid #FCA5A5; }
  .cart-empty      { display:flex; flex-direction:column; align-items:center;
                     justify-content:center; height:100%; padding:40px 24px; opacity:.4; }
  .mono            { font-family:'Anonymous Pro', monospace; letter-spacing:.05em; }
</style>

<div x-data="posSystem()" class="pos-root h-screen w-full flex overflow-hidden bg-[#F8FAFC]">

  {{-- ─────────────────────────────── LEFT PANEL ────────────────────────────── --}}
  <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

    {{-- ── TOPBAR ──────────────────────────────────────────────────── --}}
    <header class="bg-white border-b border-gray-100 px-6 py-3.5 shrink-0 z-10">
      <div class="flex items-center gap-4">

        {{-- Brand Mark --}}
        <div class="shrink-0 flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-[#0F2E23] flex items-center justify-center shadow-xs">
            <x-heroicon-o-calculator class="w-5 h-5 text-emerald-300" style="width: 20px; height: 20px;" />
          </div>
          <div>
            <p class="text-[17px] font-extrabold text-[#0F2E23] leading-none tracking-tight">Point of Sale</p>
            <p class="text-[12px] text-gray-500 mt-1 font-semibold" x-show="activeShift">
              Modal: <span class="text-emerald-800 font-bold">Rp <span x-text="formatPrice(activeShift ? activeShift.modal_awal : 0)"></span></span>
            </p>
          </div>
        </div>

        {{-- Search ──────────────────── --}}
        <div class="flex-1 max-w-[360px] mx-auto">
          <div class="relative">
            <x-heroicon-o-magnifying-glass class="w-4 h-4 absolute left-3.5 top-3 text-gray-400 pointer-events-none" style="width: 16px; height: 16px;" />
            <template x-if="leftView === 'menu'">
              <input x-model="searchQuery" type="text" placeholder="Cari nama menu…"
                     class="w-full h-10 pl-10 pr-9 text-[13px] font-medium rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-[#0F2E23] focus:ring-2 focus:ring-[#0F2E23]/10 outline-none transition">
            </template>
            <template x-if="leftView === 'meja'">
              <input x-model="tableSearch" type="text" placeholder="Cari nomor meja…"
                     class="w-full h-10 pl-10 pr-9 text-[13px] font-medium rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-[#0F2E23] focus:ring-2 focus:ring-[#0F2E23]/10 outline-none transition">
            </template>
            <template x-if="leftView === 'open_bills'">
              <input x-model="openBillSearch" type="text" placeholder="Cari pelanggan / meja…"
                     class="w-full h-10 pl-10 pr-9 text-[13px] font-medium rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-[#0F2E23] focus:ring-2 focus:ring-[#0F2E23]/10 outline-none transition">
            </template>
            <button x-show="(leftView === 'menu' && searchQuery) || (leftView === 'meja' && tableSearch) || (leftView === 'open_bills' && openBillSearch)"
                    @click="searchQuery = ''; tableSearch = ''; openBillSearch = ''"
                    class="absolute right-3 top-2 text-gray-400 hover:text-gray-600 text-lg leading-none">&times;</button>
          </div>
        </div>

        {{-- View Toggle Buttons ─────── --}}
        <div class="flex items-center gap-2 ml-auto shrink-0">

          {{-- Open Bills --}}
          <button @click="leftView = (leftView === 'open_bills') ? 'menu' : 'open_bills'"
                  :class="leftView === 'open_bills' ? 'chip-active' : 'chip-default'"
                  class="inline-flex items-center gap-2 px-3.5 h-10 rounded-xl text-[13px] font-bold transition-all active:scale-95">
            <x-heroicon-o-receipt-percent class="w-4 h-4 shrink-0" style="width: 18px; height: 18px;" />
            <span>Open Bills</span>
            <span class="min-w-[20px] h-[20px] px-1.5 rounded-full text-[11px] font-extrabold flex items-center justify-center"
                  :class="leftView === 'open_bills' ? 'bg-white text-[#0F2E23]' : 'bg-[#0F2E23] text-white'"
                  x-text="openBills.length"></span>
          </button>

          {{-- Manajemen Meja --}}
          <button @click="leftView = (leftView === 'meja') ? 'menu' : 'meja'"
                  :class="leftView === 'meja' ? 'chip-active' : 'chip-default'"
                  class="inline-flex items-center gap-2 px-3.5 h-10 rounded-xl text-[13px] font-bold transition-all active:scale-95">
            <x-heroicon-o-squares-2x2 class="w-4 h-4 shrink-0" style="width: 18px; height: 18px;" />
            <span>Manajemen Meja</span>
            <span class="min-w-[20px] h-[20px] px-1.5 rounded-full text-[11px] font-extrabold flex items-center justify-center"
                  :class="leftView === 'meja' ? 'bg-white text-[#0F2E23]' : 'bg-[#0F2E23] text-white'"
                  x-text="emptyTablesCount"></span>
          </button>
        </div>
      </div>

      {{-- ── FILTER BAR (category chips / table status) ───────────── --}}
      <div x-show="leftView === 'menu'" class="flex overflow-x-auto no-scrollbar gap-2 mt-3 pb-0.5">
        <button @click="activeCategory = 'semua'"
                :class="activeCategory === 'semua' ? 'chip-active' : 'chip-default'"
                class="shrink-0 inline-flex items-center gap-1.5 px-3.5 h-8.5 rounded-xl text-[13px] font-bold transition-all">
          <x-heroicon-o-sparkles class="w-4 h-4 shrink-0" style="width: 16px; height: 16px;" />
          <span>Semua Menu</span>
        </button>
        @foreach($kategoris as $kategori)
        <button @click="activeCategory = '{{ $kategori->id }}'"
                :class="activeCategory === '{{ $kategori->id }}' ? 'chip-active' : 'chip-default'"
                class="shrink-0 inline-flex items-center px-3.5 h-8.5 rounded-xl text-[13px] font-bold whitespace-nowrap transition-all">
          {{ $kategori->nama }}
        </button>
        @endforeach
      </div>

      <div x-show="leftView === 'meja'" class="flex items-center gap-2 mt-3">
        <span class="text-[12px] text-gray-500 font-bold mr-1">Filter:</span>
        <button @click="tableFilter = 'semua'" :class="tableFilter === 'semua' ? 'chip-active' : 'chip-default'"
                class="inline-flex items-center px-3.5 h-8.5 rounded-xl text-[13px] font-bold transition-all">Semua</button>
        <button @click="tableFilter = 'kosong'" :class="tableFilter === 'kosong' ? 'bg-emerald-800 text-white font-bold' : 'bg-emerald-50 text-emerald-900 border border-emerald-200 font-semibold'"
                class="inline-flex items-center gap-2 px-3.5 h-8.5 rounded-xl text-[13px] transition-all">
          <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>Kosong
        </button>
        <button @click="tableFilter = 'terisi'" :class="tableFilter === 'terisi' ? 'bg-amber-800 text-white font-bold' : 'bg-amber-100 text-amber-950 border border-amber-300 font-semibold'"
                class="inline-flex items-center gap-2 px-3.5 h-8.5 rounded-xl text-[13px] transition-all">
          <span class="w-2.5 h-2.5 rounded-full bg-amber-600"></span>Terisi
        </button>
      </div>
    </header>

    {{-- ══════════════════════  VIEW 1 · MENU CATALOG  ══════════════════════ --}}
    <div x-show="leftView === 'menu'" class="flex-1 overflow-y-auto p-4 pb-8">
      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3.5">
        @foreach($menus as $menu)
        @php $isHabis = $menu->isHabis(); @endphp
        <div x-show="(activeCategory === 'semua' || activeCategory == '{{ $menu->kategori_menu_id }}') && ('{{ strtolower(addslashes($menu->nama)) }}'.includes(searchQuery.toLowerCase()))"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             @click="{{ $isHabis ? "alert('Stok bahan baku menu ini sedang HABIS!')" : "addToCart(".$menu->id.", '".addslashes($menu->nama)."', ".$menu->harga.")" }}"
             class="card-menu bg-white border border-gray-200/80 rounded-2xl overflow-hidden transition-all duration-150 flex flex-col {{ $isHabis ? 'opacity-50 grayscale cursor-not-allowed' : 'cursor-pointer' }}">

          {{-- Thumbnail --}}
          <div class="relative h-32 bg-gradient-to-br from-gray-50 to-emerald-50/20 flex items-center justify-center overflow-hidden">
            @if($menu->foto)
              <img src="{{ Storage::url($menu->foto) }}" class="w-full h-full object-cover" alt="{{ $menu->nama }}">
            @else
              <div class="flex flex-col items-center gap-1 text-gray-300">
                <x-heroicon-o-photo class="w-10 h-10" style="width: 40px; height: 40px;" />
              </div>
            @endif

            {{-- Category Label --}}
            <span class="absolute top-2.5 left-2.5 bg-white/95 text-gray-700 text-[11px] font-bold px-2.5 py-0.5 rounded-lg border border-gray-200/70 shadow-2xs">
              {{ Str::limit($menu->kategori->nama ?? 'Menu', 18) }}
            </span>

            {{-- Habis Badge --}}
            @if($isHabis)
            <span class="absolute top-2.5 right-2.5 pill pill-red text-[11px] font-bold animate-pulse">
              <x-heroicon-o-exclamation-triangle class="w-3.5 h-3.5" style="width: 14px; height: 14px;" />HABIS
            </span>
            @endif
          </div>

          {{-- Info --}}
          <div class="p-3.5 flex-1 flex flex-col justify-between gap-2.5">
            <p class="text-[14px] font-bold text-[#111827] leading-snug line-clamp-2">{{ $menu->nama }}</p>
            <div class="flex items-center justify-between">
              <span class="text-[15px] font-black text-[#0F2E23]">Rp {{ number_format($menu->harga, 0, ',', '.') }}</span>
              @if(!$isHabis)
              <span class="w-7 h-7 rounded-xl bg-[#0F2E23] text-white flex items-center justify-center shadow-2xs hover:bg-[#0a1f17] transition-all">
                <x-heroicon-o-plus class="w-4 h-4" style="width: 16px; height: 16px;" />
              </span>
              @endif
            </div>
          </div>
        </div>
        @endforeach
      </div>
    </div>

    {{-- ══════════════════════  VIEW 2 · MEJA MANAGEMENT  ══════════════════════ --}}
    <div x-show="leftView === 'meja'" class="flex-1 overflow-y-auto p-4 pb-8 bg-gray-50/50">
      {{-- Legend --}}
      <div class="flex items-center justify-between mb-4 bg-white border border-gray-200/80 rounded-2xl px-5 py-3.5 shadow-2xs">
        <div>
          <p class="text-[16px] font-extrabold text-[#0F2E23]">Manajemen Meja</p>
          <p class="text-[12px] text-gray-500 mt-0.5 font-medium">Pilih meja untuk ditambahkan ke pesanan</p>
        </div>
        <div class="flex items-center gap-4 text-[13px] font-bold text-gray-700">
          <span class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>Kosong</span>
          <span class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>Terisi</span>
        </div>
      </div>

      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3.5">
        @foreach($mejas as $meja)
        <div x-show="(tableFilter === 'semua' || (tableFilter === 'kosong' && '{{ $meja->status }}' === 'kosong') || (tableFilter === 'terisi' && '{{ $meja->status }}' !== 'kosong')) && ('{{ strtolower($meja->nomor_meja) }}'.includes(tableSearch.toLowerCase()))"
             :class="selectedTable == {{ $meja->id }} ? 'ring-2 ring-[#0F2E23] border-[#0F2E23]' : '{{ $meja->status === 'kosong' ? 'border-gray-200/90 hover:border-emerald-500' : 'border-amber-300 bg-amber-50/60 hover:border-amber-500 shadow-2xs' }}'"
             class="bg-white rounded-2xl border p-4 transition-all flex flex-col gap-3">

          {{-- Status badge --}}
          <div class="flex items-center justify-between">
            <span class="mono px-2.5 py-0.5 rounded-md text-[11px] font-extrabold {{ $meja->status === 'kosong' ? 'bg-emerald-100 text-emerald-900 border border-emerald-200' : 'bg-amber-100 text-amber-950 border border-amber-300' }}">
              {{ $meja->status === 'kosong' ? 'Kosong' : 'Terisi' }}
            </span>
          </div>

          {{-- Nomor Meja --}}
          <p class="text-[17px] font-extrabold text-[#111827] tracking-tight">{{ $meja->nomor_meja }}</p>

          {{-- ── Info Pelanggan & Total (hanya saat Terisi) ── --}}
          @if($meja->status !== 'kosong')
          <div x-data="{ bill: openBills.find(b => b.meja_id == {{ $meja->id }}) }">
            <template x-if="bill">
              <div class="bg-amber-100/90 border border-amber-300/80 rounded-xl px-3 py-2.5 space-y-2">
                {{-- Nama Konsumen --}}
                <div class="flex items-start gap-1.5">
                  <x-heroicon-o-user class="w-4 h-4 text-amber-800 shrink-0 mt-0.5" />
                  <p class="text-[13px] font-extrabold text-amber-950 leading-tight" x-text="bill.nama_konsumen || '—'"></p>
                </div>
                {{-- Divider --}}
                <div class="border-t border-amber-300/60"></div>
                {{-- Total Tagihan --}}
                <div class="flex items-center justify-between">
                  <span class="mono text-[11px] font-extrabold text-amber-900 uppercase tracking-wide">Total Bayar</span>
                  <span class="text-[15px] font-black text-[#0F2E23]"
                        x-text="'Rp ' + formatPrice(bill.items.reduce((s, i) => s + ((i.menu ? i.menu.harga : (i.harga_satuan || 0)) * i.qty), 0))"></span>
                </div>
              </div>
            </template>
            {{-- Fallback jika belum ada open bill --}}
            <template x-if="!bill">
              <div class="bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-center">
                <p class="mono text-[11px] text-gray-500 font-bold">Belum ada pesanan</p>
              </div>
            </template>
          </div>
          @endif

          {{-- Buttons --}}
          <div class="flex flex-col gap-1.5 mt-auto">
            <button type="button" @click="selectTable({{ $meja->id }}, '{{ addslashes($meja->nomor_meja) }}')"
                    class="w-full h-8.5 rounded-xl text-[13px] font-bold transition-all bg-[#0F2E23] hover:bg-[#0a1f17] text-white active:scale-95">
              <span x-text="selectedTable == {{ $meja->id }} ? '✓ Terpilih' : 'Pilih Meja'"></span>
            </button>
            @if($meja->status !== 'kosong')
            <form action="{{ route('pos.dinein.clear-table', $meja->id) }}" method="POST">
              @csrf @method('PATCH')
              <button type="submit" onclick="return confirm('Kosongkan {{ $meja->nomor_meja }}?')"
                      class="w-full h-7.5 rounded-lg text-[12px] font-bold text-red-700 border border-red-200 hover:bg-red-50 transition-colors">
                Kosongkan
              </button>
            </form>
            @endif
          </div>
        </div>
        @endforeach
      </div>
    </div>

    {{-- ══════════════════════  VIEW 3 · OPEN BILLS  ════════════════════════ --}}
    <div x-show="leftView === 'open_bills'" class="flex-1 overflow-y-auto p-4 pb-8 bg-gray-50/50">

      {{-- Header strip --}}
      <div class="flex items-center justify-between mb-4 bg-white border border-gray-100 rounded-2xl px-4 py-3">
        <div>
          <p class="text-[15px] font-extrabold text-[#0F2E23]">Open Bills</p>
          <p class="text-[12px] text-gray-400 mt-0.5">Pesanan aktif yang belum dibayar</p>
        </div>
        <span class="mono text-[12px] text-gray-500">
          <span class="font-black text-[#0F2E23]" x-text="openBills.length"></span> Pesanan Aktif
        </span>
      </div>

      {{-- Empty state --}}
      <template x-if="openBills.length === 0">
        <div class="bg-white rounded-2xl border border-gray-100 p-12 text-center">
          <x-heroicon-o-check-circle class="w-10 h-10 text-emerald-500 mx-auto mb-2 opacity-70" />
          <p class="text-[14px] font-bold text-gray-700">Tidak ada open bill</p>
          <p class="text-[12px] text-gray-400 mt-0.5">Semua meja kosong atau sudah lunas</p>
        </div>
      </template>

      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        <template x-for="bill in openBills" :key="bill.id">
          <div x-show="!openBillSearch || (bill.nama_konsumen && bill.nama_konsumen.toLowerCase().includes(openBillSearch.toLowerCase())) || (bill.meja && bill.meja.nomor_meja.toLowerCase().includes(openBillSearch.toLowerCase()))"
               class="bg-white border border-gray-200/90 rounded-3xl p-5 flex flex-col justify-between gap-3.5 hover:border-[#0F2E23]/40 shadow-xs hover:shadow-md transition-all">

            {{-- Bill header --}}
            <div>
              <div class="flex items-center justify-between">
                <span class="bg-[#0F2E23] text-white px-3.5 py-1 rounded-xl text-xs font-black shadow-2xs flex items-center gap-1.5">
                  <x-heroicon-o-table-cells class="w-3.5 h-3.5 text-emerald-400" />
                  <span x-text="bill.meja ? bill.meja.nomor_meja : 'Meja'"></span>
                </span>
                <span class="font-mono text-[11px] font-bold text-gray-500 bg-gray-100 border border-gray-200/60 px-2.5 py-1 rounded-lg" x-text="bill.kode_pesanan || ('DIN-' + bill.id)"></span>
              </div>

              {{-- Nama Konsumen --}}
              <div class="mt-3 flex items-center gap-2">
                <div class="w-7 h-7 rounded-xl bg-emerald-50 text-[#0F2E23] flex items-center justify-center shrink-0">
                  <x-heroicon-o-user class="w-4 h-4" />
                </div>
                <h4 class="text-sm font-extrabold text-gray-900 truncate" x-text="bill.nama_konsumen || 'Tanpa Nama'"></h4>
              </div>
            </div>

            {{-- Items list --}}
            <div class="bg-gray-50/90 rounded-2xl border border-gray-100 p-3 space-y-2 max-h-40 overflow-y-auto">
              <template x-for="item in bill.items" :key="item.id">
                <div class="flex justify-between items-center text-xs">
                  <div class="flex items-center gap-2 truncate pr-2">
                    <span class="bg-emerald-100 text-emerald-950 px-1.5 py-0.5 rounded-md font-extrabold text-[11px] shrink-0" x-text="item.qty + 'x'"></span>
                    <span class="font-bold text-gray-800 truncate" x-text="item.menu ? item.menu.nama : '—'"></span>
                  </div>
                  <span class="font-extrabold text-[#0F2E23] shrink-0" 
                        x-text="'Rp ' + formatPrice((item.menu ? item.menu.harga : (i.harga_satuan || 0)) * item.qty)"></span>
                </div>
              </template>
            </div>

            {{-- Footer: total + actions --}}
            <div class="flex items-center justify-between pt-3 border-t border-gray-100/80 mt-auto">
              <div>
                <p class="text-[10px] font-extrabold uppercase tracking-wider text-gray-400">Total Bayar</p>
                <p class="text-lg font-black text-[#0F2E23]"
                   x-text="'Rp ' + formatPrice(bill.items.reduce((s, i) => s + ((i.menu ? i.menu.harga : (i.harga_satuan || 0)) * i.qty), 0))"></p>
              </div>

              <div class="flex items-center gap-2">
                <a :href="'/pesanan/' + bill.id"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl border border-gray-200/90 bg-white hover:bg-gray-50 text-xs font-extrabold text-gray-700 transition-all shadow-2xs">
                  <x-heroicon-o-eye class="w-4 h-4 text-gray-500" />Detail
                </a>
                <a :href="'/pos/dinein/meja/' + bill.meja_id + '/checkout'"
                   class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-[#0F2E23] hover:bg-[#0a1f17] text-white text-xs font-extrabold transition-all shadow-md active:scale-95">
                  Bayar &rarr;
                </a>
              </div>
            </div>
          </div>
        </template>
      </div>
    </div>

  </div>{{-- / left panel --}}

  {{-- ────────────────────────────── RIGHT PANEL (CART) ────────────────────── --}}
  <aside class="shrink-0 w-[380px] flex flex-col bg-white border-l border-gray-100 shadow-xl z-20">

    {{-- ── Cart Header (dark emerald) ─────── --}}
    <div class="shrink-0 m-3 rounded-2xl bg-[#0F2E23] p-4 space-y-3">

      {{-- Label row --}}
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
          <x-heroicon-o-shopping-bag class="w-4 h-4 text-emerald-400" />
          <span class="mono text-[11px] text-emerald-200/80 uppercase tracking-widest">Informasi Pesanan</span>
        </div>
        <span class="mono text-[11px] text-emerald-300 bg-white/10 px-2.5 py-0.5 rounded-full border border-white/10"
              x-text="emptyTablesCount + ' Meja Kosong'"></span>
      </div>

      {{-- Pilih Meja --}}
      <select x-model="selectedTable"
              class="w-full h-9 px-3 text-[13px] font-semibold text-white bg-white/15 border border-white/20 rounded-xl focus:border-emerald-400 focus:bg-white/20 outline-none transition">
        <option value="" disabled selected class="text-gray-900 bg-white">— Pilih Nomor Meja —</option>
        @foreach($mejas as $meja)
          @if($meja->status === 'kosong' || $meja->status === 'menunggu_pembayaran')
          <option value="{{ $meja->id }}" class="text-gray-900 bg-white">
            {{ $meja->nomor_meja }} ({{ $meja->status === 'kosong' ? 'Kosong' : 'Terisi' }})
          </option>
          @endif
        @endforeach
      </select>

      {{-- Nama & HP row --}}
      <div class="grid grid-cols-2 gap-2">
        <input x-model="customerName" type="text" placeholder="Nama Konsumen *"
               class="h-9 px-3 text-[13px] font-medium text-white placeholder-emerald-200/60 bg-white/10 border border-white/20 rounded-xl focus:border-emerald-400 focus:bg-white/20 outline-none transition">
        <input x-model="customerPhone" type="tel" placeholder="No HP (opsional)"
               class="h-9 px-3 text-[13px] font-medium text-white placeholder-emerald-200/60 bg-white/10 border border-white/20 rounded-xl focus:border-emerald-400 focus:bg-white/20 outline-none transition">
      </div>
    </div>

    {{-- ── Cart Items ──────────────────────── --}}
    <div class="flex-1 overflow-y-auto px-3 py-2 space-y-1.5">

      <template x-if="cart.length === 0">
        <div class="cart-empty">
          <x-heroicon-o-shopping-bag class="w-12 h-12 text-gray-300 mb-3" />
          <p class="text-[13px] font-bold text-gray-500">Keranjang kosong</p>
          <p class="text-[12px] text-gray-400 mt-0.5 text-center">Pilih menu dari katalog di sebelah kiri</p>
        </div>
      </template>

      <template x-for="(item, index) in cart" :key="item.menu_id">
        <div class="bg-gray-50 border border-gray-200/60 rounded-xl p-2.5 space-y-2 group hover:border-gray-300 transition-all">

          {{-- Name + Subtotal --}}
          <div class="flex items-start justify-between gap-2">
            <p class="text-[13px] font-semibold text-[#111827] leading-snug flex-1" x-text="item.nama"></p>
            <span class="text-[13px] font-bold text-[#0F2E23] shrink-0" x-text="'Rp ' + formatPrice(item.harga * item.qty)"></span>
          </div>

          {{-- Controls row --}}
          <div class="flex items-center gap-2">
            {{-- Qty stepper --}}
            <div class="flex items-center bg-white border border-gray-200 rounded-lg overflow-hidden shrink-0">
              <button @click="updateQty(index, -1)" class="w-7 h-7 flex items-center justify-center text-gray-500 hover:bg-gray-50 text-sm font-bold transition-colors">−</button>
              <span class="w-7 text-center text-[13px] font-bold text-[#111827]" x-text="item.qty"></span>
              <button @click="updateQty(index, 1)" class="w-7 h-7 flex items-center justify-center text-gray-500 hover:bg-gray-50 text-sm font-bold transition-colors">+</button>
            </div>

            {{-- Catatan --}}
            <input x-model="item.catatan" type="text" placeholder="Catatan…"
                   class="flex-1 min-w-0 h-7 px-2.5 text-[12px] font-medium text-gray-700 bg-white border border-gray-200 rounded-lg placeholder-gray-300 focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 outline-none transition">

            {{-- Delete --}}
            <button @click="removeFromCart(index)" class="w-7 h-7 flex items-center justify-center text-gray-300 hover:text-red-500 transition-colors shrink-0">
              <x-heroicon-o-trash class="w-4 h-4" />
            </button>
          </div>
        </div>
      </template>
    </div>

    {{-- ── Cart Footer ─────────────────────── --}}
    <div class="shrink-0 border-t border-gray-100 px-4 py-3 space-y-3 bg-white">

      {{-- Summary --}}
      <div class="flex items-end justify-between">
        <div>
          <p class="text-[12px] text-gray-400 font-medium" x-text="totalQty + ' item'"></p>
          <p class="mono text-[11px] text-gray-400 mt-0.5 uppercase tracking-wider">Total Tagihan</p>
        </div>
        <p class="text-[26px] font-black text-[#0F2E23] leading-none" x-text="'Rp ' + formatPrice(totalPrice)"></p>
      </div>

      {{-- Action buttons --}}
      <div class="grid grid-cols-2 gap-2">
        <button @click="submitOrder('simpan')" :disabled="isSubmitting"
                class="h-11 rounded-xl text-[14px] font-bold bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-200 transition-all active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
          Simpan (KOT)
        </button>
        <button @click="submitOrder('bayar')" :disabled="isSubmitting"
                class="h-11 rounded-xl text-[14px] font-extrabold bg-[#0F2E23] hover:bg-[#0a1f17] text-white flex items-center justify-center gap-2 transition-all active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed shadow-md shadow-emerald-950/20">
          <span x-show="!isSubmitting" class="flex items-center gap-1.5">
            <x-heroicon-o-credit-card class="w-4 h-4" />Bayar
          </span>
          <svg x-show="isSubmitting" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
        </button>
      </div>
    </div>
  </aside>

  {{-- ══════════════════════  MODAL: BUKA SHIFT / KAS MODAL AWAL  ═══════════════════ --}}
  <div x-show="!activeShift && showShiftModal" class="fixed inset-0 z-[200] flex items-center justify-center p-4" style="display:none;">
    <div class="absolute inset-0 bg-[#0F2E23]/80 backdrop-blur-sm"></div>
    <div class="relative bg-white rounded-3xl w-full max-w-md p-6 space-y-5 shadow-2xl">

      <div class="flex items-center gap-3">
        <div class="w-11 h-11 rounded-2xl bg-[#0F2E23] flex items-center justify-center shadow-lg">
          <x-heroicon-o-banknotes class="w-5 h-5 text-emerald-400" />
        </div>
        <div>
          <h2 class="text-[16px] font-extrabold text-[#0F2E23]">Buka Shift Kasir</h2>
          <p class="text-[13px] text-gray-400">Masukkan jumlah kas di laci kasir saat ini</p>
        </div>
      </div>

      <div class="space-y-2">
        <label class="mono text-[11px] text-gray-500 block">Nominal Modal Awal (Rp)</label>
        <div class="relative">
          <span class="absolute left-4 top-1/2 -translate-y-1/2 text-[14px] font-bold text-gray-400">Rp</span>
          <input type="number" x-model="modalAwalInput" placeholder="0"
                 class="w-full h-12 pl-12 pr-4 text-[18px] font-extrabold text-[#0F2E23] bg-gray-50 border border-gray-200 rounded-2xl focus:bg-white focus:border-[#0F2E23] focus:ring-4 focus:ring-[#0F2E23]/10 outline-none transition">
        </div>

        <div class="flex gap-2 pt-0.5">
          @foreach([['100rb','100000'],['250rb','250000'],['500rb','500000'],['1 Jt','1000000']] as [$label, $val])
          <button type="button" @click="modalAwalInput = {{ $val }}"
                  class="flex-1 h-8 rounded-xl text-[12px] font-bold bg-gray-100 hover:bg-gray-200 text-gray-700 transition-colors">{{ $label }}</button>
          @endforeach
        </div>
      </div>

      <button type="button" @click="submitBukaShift()" :disabled="isSubmittingShift"
              class="w-full h-12 rounded-2xl text-[14px] font-extrabold bg-[#0F2E23] hover:bg-[#0a1f17] text-white transition-all disabled:opacity-60">
        <span x-show="!isSubmittingShift">Buka Shift Sekarang</span>
        <span x-show="isSubmittingShift">Memproses…</span>
      </button>
    </div>
  </div>

  {{-- ══════════════════════  MODAL: CETAK STRUK  ════════════════════════════ --}}
  <div x-show="showSavePrintModal" style="display:none;" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-gray-900/70 backdrop-blur-sm" @click="showSavePrintModal = false"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden flex flex-col max-h-[90vh]">

      {{-- Header --}}
      <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 bg-gray-50">
        <div>
          <p class="text-[15px] font-extrabold text-[#0F2E23]">Pesanan Berhasil Disimpan</p>
          <p class="text-[12px] text-gray-400 mt-0.5">Cetak Struk Dapur (KOT) atau Struk Meja</p>
        </div>
        <button @click="showSavePrintModal = false" class="text-gray-400 hover:text-gray-700 text-xl font-bold leading-none">&times;</button>
      </div>

      {{-- Tab switch --}}
      <div class="flex gap-2 p-3 bg-gray-50 border-b border-gray-100">
        <button @click="printTab = 'dapur'" :class="printTab === 'dapur' ? 'chip-active' : 'chip-default'"
                class="flex-1 h-9 rounded-xl text-[13px] font-bold flex items-center justify-center gap-2 transition-all">
          <x-heroicon-o-printer class="w-4 h-4" />Struk Dapur (KOT)
        </button>
        <button @click="printTab = 'meja'" :class="printTab === 'meja' ? 'chip-active' : 'chip-default'"
                class="flex-1 h-9 rounded-xl text-[13px] font-bold flex items-center justify-center gap-2 transition-all">
          <x-heroicon-o-document-text class="w-4 h-4" />Struk Meja
        </button>
      </div>

      {{-- Iframe preview --}}
      <div class="flex-1 bg-gray-100 flex justify-center items-start overflow-auto p-4 min-h-[55vh]">
        <template x-if="savedPesananId">
          <div class="flex justify-center w-full">
            <iframe x-show="printTab === 'dapur'" id="receiptDapurFrame"
                    :src="'/pos/dinein/pesanan/' + savedPesananId + '/print-dapur'"
                    class="w-[320px] h-[55vh] bg-white border border-gray-200 rounded-xl shadow-md"></iframe>
            <iframe x-show="printTab === 'meja'" id="receiptMejaFrame"
                    :src="'/pos/dinein/pesanan/' + savedPesananId + '/print-meja'"
                    class="w-[320px] h-[55vh] bg-white border border-gray-200 rounded-xl shadow-md"></iframe>
          </div>
        </template>
      </div>

      {{-- Footer --}}
      <div class="flex items-center justify-between px-5 py-3 border-t border-gray-100 bg-white">
        <button @click="showSavePrintModal = false"
                class="h-10 px-5 rounded-xl border border-gray-200 text-[13px] font-bold text-gray-700 hover:bg-gray-50 transition-all">Tutup</button>
        
        <div class="flex gap-2">
          <button x-show="printTab === 'dapur'"
                  onclick="document.getElementById('receiptDapurFrame').contentWindow.print()"
                  class="h-10 px-6 rounded-xl bg-[#0F2E23] hover:bg-[#0a1f17] text-white text-[13px] font-extrabold flex items-center gap-2 transition-all shadow-md active:scale-95">
            <x-heroicon-o-printer class="w-4 h-4" />Cetak Struk Dapur
          </button>
          <button x-show="printTab === 'meja'"
                  onclick="document.getElementById('receiptMejaFrame').contentWindow.print()"
                  class="h-10 px-6 rounded-xl bg-[#0F2E23] hover:bg-[#0a1f17] text-white text-[13px] font-extrabold flex items-center gap-2 transition-all shadow-md active:scale-95">
            <x-heroicon-o-printer class="w-4 h-4" />Cetak Struk Meja
          </button>
        </div>
      </div>
    </div>
  </div>

  {{-- ══════════════════════  MODAL: NOTA SETELAH BAYAR  ════════════════════════════ --}}
  @if(session('print_nota_id'))
  <div x-data="{ showPrint: true }" x-show="showPrint" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-gray-900/70 backdrop-blur-sm" @click="showPrint = false"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-3xl overflow-hidden flex flex-col max-h-[90vh]">
      <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 bg-gray-50">
        <p class="text-[15px] font-extrabold text-[#0F2E23]">Cetak Nota Pembayaran</p>
        <button @click="showPrint = false" class="text-gray-400 hover:text-gray-700 text-xl font-bold">&times;</button>
      </div>
      <div class="flex-1 bg-gray-100 flex justify-center p-4">
        <iframe id="receiptFrame" src="{{ route('pos.dinein.print-nota', session('print_nota_id')) }}"
                class="w-[340px] h-[60vh] bg-white border border-gray-200 rounded-xl shadow-md"></iframe>
      </div>
      <div class="flex items-center justify-between px-5 py-3 border-t border-gray-100 bg-white">
        <button @click="showPrint = false" class="h-9 px-4 rounded-xl border border-gray-200 text-[13px] font-semibold text-gray-700 hover:bg-gray-50 transition-all">Tutup</button>
        <button onclick="document.getElementById('receiptFrame').contentWindow.print()"
                class="h-9 px-4 rounded-xl bg-[#3B82F6] hover:bg-blue-700 text-white text-[13px] font-bold flex items-center gap-1.5">
          <x-heroicon-o-printer class="w-4 h-4" />Cetak Nota
        </button>
      </div>
    </div>
  </div>
  @endif

</div>{{-- / pos-root --}}

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
  Alpine.data('posSystem', () => ({
    // View state
    leftView: 'menu',
    activeCategory: 'semua',
    searchQuery: '',
    tableSearch: '',
    tableFilter: 'semua',
    openBillSearch: '',

    // Table
    emptyTablesCount: {{ $mejas->where('status', 'kosong')->count() }},
    selectedTable: null,
    selectedTableLabel: '',

    // Customer info
    customerName: '',
    customerPhone: '',

    // Cart
    cart: [],
    isSubmitting: false,

    // Receipt modal
    showSavePrintModal: false,
    savedPesananId: null,
    printTab: 'dapur',

    // Shift
    activeShift: @json($activeShift),
    openBills: @json($openBills),
    modalAwalInput: 500000,
    showShiftModal: true,
    isSubmittingShift: false,

    // ── Computed ────────────────────────────────────
    get totalPrice() { return this.cart.reduce((t, i) => t + i.harga * i.qty, 0); },
    get totalQty()   { return this.cart.reduce((t, i) => t + i.qty, 0); },

    formatPrice(n) {
      if (!n) return '0';
      return Number(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    },

    // ── Cart ────────────────────────────────────────
    addToCart(menuId, nama, harga) {
      const existing = this.cart.find(i => i.menu_id === menuId);
      if (existing) { existing.qty++; }
      else { this.cart.unshift({ menu_id: menuId, nama, harga, qty: 1, catatan: '' }); }
    },
    updateQty(index, change) {
      const qty = this.cart[index].qty + change;
      if (qty > 0) this.cart[index].qty = qty;
      else this.removeFromCart(index);
    },
    removeFromCart(index) { this.cart.splice(index, 1); },

    // ── Table ───────────────────────────────────────
    selectTable(id, label) {
      this.selectedTable = id;
      this.selectedTableLabel = label;
      this.leftView = 'menu';
    },

    // ── Submit Order ────────────────────────────────
    async submitOrder(action) {
      if (!this.selectedTable) return alert('Mohon pilih nomor meja terlebih dahulu!');
      if (!this.customerName.trim()) return alert('Mohon isi nama konsumen!');
      if (!this.cart.length) return alert('Keranjang masih kosong!');

      this.isSubmitting = true;
      try {
        const res = await fetch('{{ route('pos.dinein.store-pos') }}', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
          body: JSON.stringify({
            meja_id: this.selectedTable,
            nama_konsumen: this.customerPhone ? `${this.customerName} – ${this.customerPhone}` : this.customerName,
            items: this.cart
          })
        });
        const data = await res.json();
        if (res.ok && data.success) {
          if (action === 'bayar') {
            window.location.href = `/pos/dinein/meja/${this.selectedTable}/checkout`;
          } else {
            this.savedPesananId = data.pesanan_id;
            this.printTab = 'dapur';
            this.showSavePrintModal = true;
            this.cart = []; this.customerName = ''; this.customerPhone = ''; this.selectedTable = null;
          }
        } else { alert(data.message || 'Gagal menyimpan pesanan'); }
      } catch(e) { alert('Terjadi kesalahan jaringan'); }
      finally { this.isSubmitting = false; }
    },

    // ── Shift ───────────────────────────────────────
    async submitBukaShift() {
      if (!this.modalAwalInput || this.modalAwalInput < 0) return alert('Nominal modal awal tidak valid!');
      this.isSubmittingShift = true;
      try {
        const res = await fetch('{{ route('pos.shift.buka') }}', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
          body: JSON.stringify({ modal_awal: this.modalAwalInput })
        });
        const data = await res.json();
        if (res.ok && data.success) { this.activeShift = data.shift; this.showShiftModal = false; }
        else alert(data.message || 'Gagal membuka shift');
      } catch(e) { alert('Terjadi kesalahan jaringan'); }
      finally { this.isSubmittingShift = false; }
    }
  }));
});
</script>
@endpush
@endsection
