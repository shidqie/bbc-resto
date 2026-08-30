@php
    $isCatering = ($tipe === 'Catering' || $tipe === 'Katering');
    $pageTitle = $isCatering ? 'BUAT PURCHASE ORDER (PO) KATERING' : 'BUAT PURCHASE ORDER (PO) NASI BOX & HARIAN';
    $pageSubtitle = $isCatering 
        ? 'Buat pesanan bahan baku ke supplier khusus pesanan katering berdasarkan kebutuhan BOM.' 
        : 'Buat pesanan bahan baku ke supplier untuk pesanan nasi box dan restock operasional harian.';
    $breadcrumbLabel = $isCatering ? 'Buat PO Katering' : 'Buat PO Nasi Box & Harian';
@endphp
@extends('layouts.pos')
@section('title', $isCatering ? 'Buat PO Katering' : 'Buat PO Nasi Box & Harian')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800 pb-12">
    <div class="w-full p-6 space-y-6">
        
        {{-- PAGE HEADER --}}
        <x-ui.page-header
            :title="$pageTitle"
            :subtitle="$pageSubtitle"
            :breadcrumbs="['Pengadaan', 'Purchase Order', $breadcrumbLabel]">
            <x-slot:actions>
                <x-ui.button variant="secondary" href="{{ route('pengadaan.po.index') }}">
                    Batal
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.alert />

        @if(isset($resepBelumLengkap) && $resepBelumLengkap)
        <div class="p-5 bg-amber-50 border border-amber-200 rounded-2xl text-amber-900 space-y-2 shadow-xs">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <h4 class="font-extrabold text-sm uppercase tracking-wide text-amber-800">Status: Resep Belum Lengkap</h4>
            </div>
            <p class="text-xs text-amber-800 leading-relaxed">
                Terdapat menu pada pesanan ini yang belum memiliki data resep/Bill of Materials (BOM) lengkap di master resep. Silakan lengkapi resep pada menu berikut agar perhitungan kebutuhan otomatis akurat:
            </p>
            <div class="flex flex-wrap gap-2 pt-1">
                @foreach($missingMenus as $mm)
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-200/70 text-amber-900 border border-amber-300">
                        {{ $mm }}
                    </span>
                @endforeach
            </div>
        </div>
        @endif

        @if(!empty($menuHabisList) && count($menuHabisList) > 0 && !isset($pesanan))
        <div class="p-4 bg-rose-50 border border-rose-200/90 rounded-2xl text-rose-900 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <p class="text-sm font-semibold text-rose-800">
                    Terdapat <strong>{{ count($menuHabisList) }} menu</strong> yang berstatus <strong>Habis</strong> karena kekurangan stok bahan baku.
                </p>
            </div>
        </div>
        @endif

        <form action="{{ route('pengadaan.po.store-unified') }}" method="POST" id="poForm" class="space-y-6">
            @csrf
            <input type="hidden" name="tipe" value="{{ $tipe ?? 'Operasional' }}">
            @if(isset($pesanan))
                <input type="hidden" name="pesanan_id" value="{{ $pesanan->id }}">
            @endif

            {{-- CARD 1: INFORMASI PO & PESANAN --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200/80 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                    <h3 class="text-xs font-extrabold text-gray-700 uppercase tracking-wider">INFORMASI PO & SUMBER KEBUTUHAN</h3>
                    <span class="text-xs text-gray-400 font-semibold">RM Saung Babakan Cinta</span>
                </div>
                <div class="p-6 space-y-5">
                    {{-- Row 1: No. PO --}}
                    <div>
                        <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-2">No. PO</label>
                        <input type="text" name="nomor_po" value="{{ $kodePo }}" readonly class="w-full bg-gray-100/80 border border-gray-200 rounded-xl text-gray-700 font-semibold text-sm px-4 py-2.5 cursor-not-allowed">
                        <p class="text-[11px] text-gray-400 mt-1">Dibuat otomatis oleh sistem</p>
                    </div>

                    {{-- Row 2: Pemilihan Pesanan Katering / Nasi Box --}}
                    @if($tipe === 'Catering' || $tipe === 'Katering')
                    <div class="grid grid-cols-1 gap-y-3 p-5 bg-emerald-50/40 border border-emerald-100 rounded-2xl">
                        <div>
                            <label class="block text-xs font-extrabold text-emerald-900 uppercase tracking-wider mb-2">
                                Pesanan Katering (Sumber Kebutuhan BOM) <span class="text-red-500">*</span>
                            </label>

                            <div x-data="{
                                open: false,
                                selectedCode: '{{ optional($pesanan)->id_pesanan ?? request('kode_pesanan', '') }}',
                                selectOrder(code) {
                                    if (code !== this.selectedCode) {
                                        window.location.href = '?tipe=Catering' + (code ? '&kode_pesanan=' + encodeURIComponent(code) : '');
                                    }
                                }
                            }" class="relative">
                                <button type="button"
                                        @click="open = !open"
                                        @click.outside="open = false"
                                        class="w-full flex items-center justify-between gap-3 px-4 py-3 bg-white border border-emerald-200 hover:border-emerald-400 rounded-xl text-left transition shadow-xs cursor-pointer focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-9 h-9 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                                            <x-heroicon-o-clipboard-document-list class="w-5 h-5" />
                                        </div>
                                        <div class="min-w-0">
                                            @if($pesanan)
                                                @php
                                                    $namaCust = optional($pesanan->pelanggan)->nama ?? optional($pesanan->pelanggan)->nama_pelanggan ?? 'Umum';
                                                    $tglPesanan = optional($pesanan->jadwal_pesanan)->tanggal_acara ?? $pesanan->waktu_pesanan ?? $pesanan->dibuat_pada;
                                                    $tglFormatted = $tglPesanan ? \Carbon\Carbon::parse($tglPesanan)->translatedFormat('d M Y') : '-';
                                                    $qtyPorsi = optional($pesanan->detail_pesanan->first())->jumlah ?? 0;
                                                    $menuNama = optional(optional($pesanan->detail_pesanan->first())->menu)->nama_menu ?? 'Paket';
                                                @endphp
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <span class="font-mono font-bold text-gray-900 text-sm">{{ $pesanan->id_pesanan }}</span>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800">Dikonfirmasi</span>
                                                </div>
                                                <p class="text-xs text-gray-600 truncate mt-0.5">
                                                    <span class="font-bold text-gray-800">{{ $namaCust }}</span> &bull; {{ $menuNama }} ({{ $qtyPorsi }} Porsi) &bull; Acara: {{ $tglFormatted }}
                                                </p>
                                            @else
                                                <span class="text-sm font-medium text-gray-400">
                                                    — Pilih Pesanan Katering (Status Dikonfirmasi) —
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <x-heroicon-o-chevron-down class="w-4 h-4 text-emerald-600 shrink-0 transition-transform duration-200" x-bind:class="{ 'rotate-180': open }" />
                                </button>

                                {{-- Dropdown Popover --}}
                                <div x-show="open"
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="opacity-100 scale-100"
                                     x-transition:leave-end="opacity-0 scale-95"
                                     class="absolute z-50 mt-1.5 w-full bg-white border border-gray-200 rounded-2xl shadow-xl max-h-72 overflow-y-auto divide-y divide-gray-100 left-0 p-1.5"
                                     style="display: none;">

                                    @forelse($pesananList as $pk)
                                        @php
                                            $namaCust = optional($pk->pelanggan)->nama ?? optional($pk->pelanggan)->nama_pelanggan ?? 'Umum';
                                            $tglAcara = optional($pk->jadwal_pesanan)->tanggal_acara 
                                                ?? $pk->waktu_pesanan 
                                                ?? $pk->dibuat_pada;
                                            $tglFormatted = $tglAcara ? \Carbon\Carbon::parse($tglAcara)->translatedFormat('d M Y') : '-';
                                            $qtyPorsi = optional($pk->detail_pesanan->first())->jumlah ?? 0;
                                            $menuNama = optional(optional($pk->detail_pesanan->first())->menu)->nama_menu ?? 'Paket';
                                            $isSelected = (optional($pesanan)->id_pesanan === $pk->id_pesanan);
                                        @endphp
                                        <div @click="selectOrder('{{ $pk->id_pesanan }}'); open = false;"
                                             class="p-3 rounded-xl hover:bg-emerald-50/70 cursor-pointer flex items-center justify-between transition group {{ $isSelected ? 'bg-emerald-50/80 border border-emerald-200' : '' }}">
                                            <div class="min-w-0 pr-3">
                                                <div class="flex items-center gap-2">
                                                    <span class="font-mono font-bold text-xs text-gray-900">{{ $pk->id_pesanan }}</span>
                                                    <span class="px-1.5 py-0.5 text-[10px] font-bold rounded bg-emerald-100 text-emerald-800">Dikonfirmasi</span>
                                                </div>
                                                <p class="text-xs font-bold text-gray-800 mt-1">
                                                    {{ $namaCust }} &bull; <span class="font-normal text-gray-600">{{ $menuNama }} ({{ $qtyPorsi }} Porsi)</span>
                                                </p>
                                                <p class="text-[11px] text-gray-400 mt-0.5 flex items-center gap-1">
                                                    <x-heroicon-o-calendar class="w-3 h-3 text-gray-400" />
                                                    <span>Tanggal Acara: {{ $tglFormatted }}</span>
                                                </p>
                                            </div>
                                            @if($isSelected)
                                                <div class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center shrink-0">
                                                    <x-heroicon-o-check class="w-3.5 h-3.5 stroke-[3]" />
                                                </div>
                                            @endif
                                        </div>
                                    @empty
                                        <div class="p-5 text-center text-xs text-gray-500">
                                            <p class="font-bold text-gray-700">Tidak ada pesanan katering berstatus Dikonfirmasi.</p>
                                            <p class="text-[11px] text-gray-400 mt-1">Semua pesanan katering sudah selesai diproses atau belum terkonfirmasi.</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        @if($pesanan)
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-3 text-xs border-t border-emerald-100">
                            <div>
                                <span class="text-gray-500 block">Pemesan:</span>
                                <span class="font-bold text-gray-800">{{ optional($pesanan->pelanggan)->nama ?? optional($pesanan->pelanggan)->nama_pelanggan ?? 'Umum' }} ({{ optional($pesanan->pelanggan)->no_telp ?? optional($pesanan->pelanggan)->telepon ?? '-' }})</span>
                            </div>
                            <div>
                                <span class="text-gray-500 block">Menu Dipesan:</span>
                                <span class="font-bold text-gray-800">
                                    @foreach($pesanan->detail_pesanan as $dp)
                                        {{ optional($dp->menu)->nama_menu }} ({{ $dp->jumlah }} Porsi){{ !$loop->last ? ', ' : '' }}
                                    @endforeach
                                </span>
                            </div>
                            <div>
                                <span class="text-gray-500 block">Perhitungan Stok:</span>
                                <span class="font-bold text-emerald-700">Otomatis BOM &minus; Saldo Stok Katering</span>
                            </div>
                        </div>
                        @endif
                    </div>
                    @else
                    <div class="grid grid-cols-1 gap-y-3 p-5 bg-emerald-50/40 border border-emerald-100 rounded-2xl">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-xs font-extrabold text-emerald-900 uppercase tracking-wider">Pesanan Nasi Box (Sumber Kebutuhan BOM)</label>
                                <span class="text-[11px] font-semibold text-emerald-700 bg-emerald-100/60 px-2 py-0.5 rounded">Opsional</span>
                            </div>

                            <div x-data="{
                                open: false,
                                selectedCode: '{{ optional($pesanan)->id_pesanan ?? request('kode_pesanan', '') }}',
                                selectOrder(code) {
                                    if (code !== this.selectedCode) {
                                        window.location.href = '?tipe=Harian' + (code ? '&kode_pesanan=' + encodeURIComponent(code) : '');
                                    }
                                }
                            }" class="relative">
                                <button type="button"
                                        @click="open = !open"
                                        @click.outside="open = false"
                                        class="w-full flex items-center justify-between gap-3 px-4 py-3 bg-white border border-emerald-200 hover:border-emerald-400 rounded-xl text-left transition shadow-xs cursor-pointer focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-9 h-9 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                                            <x-heroicon-o-clipboard-document-list class="w-5 h-5" />
                                        </div>
                                        <div class="min-w-0">
                                            @if($pesanan)
                                                @php
                                                    $namaCust = optional($pesanan->pelanggan)->nama ?? optional($pesanan->pelanggan)->nama_pelanggan ?? 'Umum';
                                                    $tglPesanan = optional($pesanan->jadwal_pesanan)->tanggal_acara ?? $pesanan->waktu_pesanan ?? $pesanan->dibuat_pada;
                                                    $tglFormatted = $tglPesanan ? \Carbon\Carbon::parse($tglPesanan)->translatedFormat('d M Y') : '-';
                                                    $qtyPorsi = optional($pesanan->detail_pesanan->first())->jumlah ?? 0;
                                                    $menuNama = optional(optional($pesanan->detail_pesanan->first())->menu)->nama_menu ?? 'Paket';
                                                @endphp
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <span class="font-mono font-bold text-gray-900 text-sm">{{ $pesanan->id_pesanan }}</span>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800">Dikonfirmasi</span>
                                                </div>
                                                <p class="text-xs text-gray-600 truncate mt-0.5">
                                                    <span class="font-bold text-gray-800">{{ $namaCust }}</span> &bull; {{ $menuNama }} ({{ $qtyPorsi }} Box) &bull; Acara: {{ $tglFormatted }}
                                                </p>
                                            @else
                                                <span class="text-sm font-medium text-gray-600">
                                                    — Tanpa Pesanan (Restock Operasional Harian Rutin) —
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <x-heroicon-o-chevron-down class="w-4 h-4 text-emerald-600 shrink-0 transition-transform duration-200" x-bind:class="{ 'rotate-180': open }" />
                                </button>

                                {{-- Dropdown Popover --}}
                                <div x-show="open"
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="opacity-100 scale-100"
                                     x-transition:leave-end="opacity-0 scale-95"
                                     class="absolute z-50 mt-1.5 w-full bg-white border border-gray-200 rounded-2xl shadow-xl max-h-72 overflow-y-auto divide-y divide-gray-100 left-0 p-1.5"
                                     style="display: none;">

                                    <div @click="selectOrder(''); open = false;"
                                         class="p-3 rounded-xl hover:bg-emerald-50/70 cursor-pointer flex items-center justify-between transition group {{ !$pesanan ? 'bg-emerald-50/80 border border-emerald-200' : '' }}">
                                        <div>
                                            <p class="text-xs font-bold text-gray-800 group-hover:text-emerald-800">
                                                Tanpa Pesanan Khusus
                                            </p>
                                            <p class="text-[11px] text-gray-500 mt-0.5">Restock operasional harian rutin</p>
                                        </div>
                                        @if(!$pesanan)
                                            <div class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center shrink-0">
                                                <x-heroicon-o-check class="w-3.5 h-3.5 stroke-[3]" />
                                            </div>
                                        @endif
                                    </div>

                                    @forelse($pesananList as $pk)
                                        @php
                                            $namaCust = optional($pk->pelanggan)->nama ?? optional($pk->pelanggan)->nama_pelanggan ?? 'Umum';
                                            $tglAcara = optional($pk->jadwal_pesanan)->tanggal_acara 
                                                ?? $pk->waktu_pesanan 
                                                ?? $pk->dibuat_pada;
                                            $tglFormatted = $tglAcara ? \Carbon\Carbon::parse($tglAcara)->translatedFormat('d M Y') : '-';
                                            $qtyPorsi = optional($pk->detail_pesanan->first())->jumlah ?? 0;
                                            $menuNama = optional(optional($pk->detail_pesanan->first())->menu)->nama_menu ?? 'Paket';
                                            $isSelected = (optional($pesanan)->id_pesanan === $pk->id_pesanan);
                                        @endphp
                                        <div @click="selectOrder('{{ $pk->id_pesanan }}'); open = false;"
                                             class="p-3 rounded-xl hover:bg-emerald-50/70 cursor-pointer flex items-center justify-between transition group {{ $isSelected ? 'bg-emerald-50/80 border border-emerald-200' : '' }}">
                                            <div class="min-w-0 pr-3">
                                                <div class="flex items-center gap-2">
                                                    <span class="font-mono font-bold text-xs text-gray-900">{{ $pk->id_pesanan }}</span>
                                                    <span class="px-1.5 py-0.5 text-[10px] font-bold rounded bg-emerald-100 text-emerald-800">Dikonfirmasi</span>
                                                </div>
                                                <p class="text-xs font-bold text-gray-800 mt-1">
                                                    {{ $namaCust }} &bull; <span class="font-normal text-gray-600">{{ $menuNama }} ({{ $qtyPorsi }} Box)</span>
                                                </p>
                                                <p class="text-[11px] text-gray-400 mt-0.5 flex items-center gap-1">
                                                    <x-heroicon-o-calendar class="w-3 h-3 text-gray-400" />
                                                    <span>Tanggal Acara: {{ $tglFormatted }}</span>
                                                </p>
                                            </div>
                                            @if($isSelected)
                                                <div class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center shrink-0">
                                                    <x-heroicon-o-check class="w-3.5 h-3.5 stroke-[3]" />
                                                </div>
                                            @endif
                                        </div>
                                    @empty
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        @if($pesanan)
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-3 text-xs border-t border-emerald-100">
                            <div>
                                <span class="text-gray-500 block">Pemesan:</span>
                                <span class="font-bold text-gray-800">{{ optional($pesanan->pelanggan)->nama ?? optional($pesanan->pelanggan)->nama_pelanggan ?? 'Umum' }} ({{ optional($pesanan->pelanggan)->no_telp ?? optional($pesanan->pelanggan)->telepon ?? '-' }})</span>
                            </div>
                            <div>
                                <span class="text-gray-500 block">Menu Dipesan:</span>
                                <span class="font-bold text-gray-800">
                                    @foreach($pesanan->detail_pesanan as $dp)
                                        {{ optional($dp->menu)->nama_menu }} ({{ $dp->jumlah }} Box){{ !$loop->last ? ', ' : '' }}
                                    @endforeach
                                </span>
                            </div>
                            <div>
                                <span class="text-gray-500 block">Perhitungan Stok:</span>
                                <span class="font-bold text-emerald-700">Otomatis BOM &minus; Saldo Stok Harian</span>
                            </div>
                        </div>
                        @endif
                    </div>
                    @endif

                    {{-- Row 3: Supplier, Kontak, Tanggal PO & Tanggal Kebutuhan --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-5">
                        <div>
                            <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-2">Supplier / Toko <span class="text-red-500">*</span></label>
                            <input type="text" name="supplier_nama" id="supplier_nama" value="{{ old('supplier_nama') }}" placeholder="Nama supplier / toko..." class="block w-full rounded-xl border {{ $errors->has('supplier_nama') ? 'border-red-400 focus:border-red-500 focus:ring-red-500/20' : 'border-gray-200 focus:border-primary/20 focus:ring-primary/20' }} bg-white text-sm px-4 py-2.5 transition-all duration-150 outline-none font-medium" required>
                            @error('supplier_nama')
                                <p class="text-xs font-medium text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider">No. Kontak / WA</label>
                                <span class="text-[10px] text-gray-400 font-medium">Opsional</span>
                            </div>
                            <input type="text" name="no_telp_supplier" id="no_telp_supplier" value="{{ old('no_telp_supplier') }}" placeholder="Contoh: 0812-3456-7890" class="block w-full rounded-xl border border-gray-200 focus:border-primary/20 focus:ring-2 focus:ring-primary/20 bg-white text-sm px-4 py-2.5 transition-all outline-none font-medium">
                        </div>

                        <div>
                            <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-2">Tanggal PO <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_po" value="{{ old('tanggal_po', date('Y-m-d')) }}" required class="block w-full rounded-xl border border-gray-200 focus:border-primary/20 focus:ring-2 focus:ring-primary/20 text-sm px-4 py-2.5 transition-all outline-none font-medium">
                        </div>

                        <div>
                            <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-2">Tanggal Kebutuhan <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_kebutuhan" value="{{ old('tanggal_kebutuhan', date('Y-m-d', strtotime('+1 day'))) }}" required class="block w-full rounded-xl border border-gray-200 focus:border-primary/20 focus:ring-2 focus:ring-primary/20 text-sm px-4 py-2.5 transition-all outline-none font-medium">
                        </div>
                    </div>

                    {{-- Row 4: Catatan PO --}}
                    <div>
                        <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-2">Catatan PO</label>
                        <input type="text" name="catatan" value="{{ old('catatan') }}" placeholder="Tambahkan catatan jika diperlukan..." class="block w-full rounded-xl border border-gray-200 focus:border-primary/20 focus:ring-2 focus:ring-primary/20 text-sm px-4 py-2.5 transition-all outline-none font-medium">
                    </div>
                </div>
            </div>

            {{-- CARD 2: DAFTAR BAHAN BAKU YANG WAJIB DIPESAN (KEKURANGAN > 0) --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200/80 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <h3 class="text-xs font-extrabold text-gray-700 uppercase tracking-wider">DAFTAR KEBUTUHAN PENGADAAN (STOK KURANG)</h3>
                        <span class="text-xs text-gray-700 font-bold px-2.5 py-0.5 bg-emerald-100 text-emerald-800 rounded-md" id="itemCountBadge">{{ count($items) }} item wajib dibeli</span>
                    </div>

                    {{-- Search & Combobox Toolbar --}}
                    <div class="flex items-center gap-2">
                        <div class="relative" x-data="{
                            open: false,
                            search: '',
                            items: [
                                @foreach($allBahanBaku as $bb)
                                    {
                                        id: {{ $bb->id }},
                                        nama: '{{ addslashes($bb->nama_bahan) }}',
                                        kode: '{{ $bb->id_bahan_baku }}',
                                        satuan: '{{ addslashes(\App\Helpers\UnitHelper::getPurchasingUnit($bb->satuan)) }}',
                                        harga: {{ (float)\App\Helpers\UnitHelper::toPurchasingPrice($bb->harga_satuan ?? 0, $bb->satuan) }},
                                        stok_minimal: {{ (float)\App\Helpers\UnitHelper::toPurchasingQuantity($bb->stok_minimal ?? 5, $bb->satuan) }},
                                        full: '{{ addslashes($bb->nama_bahan) }} ({{ $bb->id_bahan_baku }})'
                                    },
                                @endforeach
                            ],
                            get filtered() {
                                if (!this.search) return this.items;
                                const q = this.search.toLowerCase();
                                return this.items.filter(i => i.nama.toLowerCase().includes(q) || i.kode.toLowerCase().includes(q));
                            },
                            select(item) {
                                this.search = item.full;
                                document.getElementById('addBahanInput').value = item.full;
                                this.open = false;
                                window.addCustomBahanRow();
                            }
                        }" @click.outside="open = false">
                            
                            <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-3 pointer-events-none z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>

                            <input type="text" id="addBahanInput"
                                   x-model="search"
                                   @focus="open = true"
                                   @click="open = true"
                                   @input="open = true; window.filterAndPaginateTable(1);"
                                   @keydown.enter.prevent="open = false; window.addCustomBahanRow();"
                                   placeholder="Cari & tambah bahan lain..."
                                   class="w-64 sm:w-80 h-10 rounded-xl border border-gray-200 bg-white text-sm pl-10 pr-9 py-2 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all font-medium shadow-xs">
                            
                            <button type="button" @click="open = !open" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 focus:outline-none">
                                <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>

                            <div x-show="open"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="opacity-0 transform scale-95"
                                 x-transition:enter-end="opacity-100 transform scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="opacity-100 transform scale-100"
                                 x-transition:leave-end="opacity-0 transform scale-95"
                                 class="absolute right-0 z-50 mt-1.5 w-80 bg-white border border-gray-200 rounded-xl shadow-xl max-h-60 overflow-y-auto divide-y divide-gray-50"
                                 style="display: none;">
                                <template x-for="item in filtered" :key="item.id">
                                    <div @click="select(item)" class="px-4 py-2.5 hover:bg-emerald-50/70 cursor-pointer flex items-center justify-between transition-colors">
                                        <div>
                                            <p class="text-sm font-semibold text-gray-800" x-text="item.nama"></p>
                                            <p class="text-xs text-gray-400 font-medium" x-text="item.kode"></p>
                                        </div>
                                        <span class="text-xs px-2.5 py-0.5 bg-gray-100 text-gray-600 rounded-full font-medium" x-text="item.satuan"></span>
                                    </div>
                                </template>
                                <div x-show="filtered.length === 0" class="px-4 py-3 text-xs text-gray-400 text-center">
                                    Bahan baku tidak ditemukan
                                </div>
                            </div>
                        </div>

                        <button type="button" onclick="addCustomBahanRow()" class="h-10 px-5 bg-[#0D3024] hover:bg-[#0D3024]/90 text-white rounded-xl text-sm font-bold transition-all shrink-0 flex items-center justify-center gap-1.5 shadow-xs">
                            + Tambah
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm" id="poTable">
                        <thead>
                            <tr class="bg-gray-50/80 border-b border-gray-200 text-xs font-bold text-gray-500 uppercase tracking-wider">
                                <th class="py-3.5 px-4 text-center w-12">NO</th>
                                <th class="py-3.5 px-6">Bahan Baku</th>
                                <th class="py-3.5 px-4 text-center w-28">Kebutuhan BOM</th>
                                <th class="py-3.5 px-4 text-center w-28">Stok Tersedia</th>
                                <th class="py-3.5 px-4 text-center w-28">Sudah Dipesan</th>
                                <th class="py-3.5 px-6 text-right w-36">Kekurangan (Beli)</th>
                                <th class="py-3.5 px-4 text-center w-24">Satuan Beli</th>
                                <th class="py-3.5 px-4 text-center w-16">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100" id="poTableBody">
                            @forelse($items as $idx => $item)
                            @php
                                $qty = (float) ($item->jumlah_beli ?? $item->kekurangan ?? $item->kebutuhan_bersih ?? 0);
                                $kebutuhanTotal = (float) ($item->kebutuhan_total ?? $item->kebutuhan_awal ?? $qty);
                                $stokVal = (float) ($item->stok_saat_ini ?? 0);
                                $sudahPesanVal = (float) ($item->sudah_dipesan ?? 0);
                                $harga = (float) ($item->harga_satuan ?? 0);
                                $satuanBeli = $item->satuan_beli ?? \App\Helpers\UnitHelper::getPurchasingUnit($item->satuan);
                                $satuanDasar = $item->satuan_dasar ?? \App\Helpers\UnitHelper::getBaseUnit($item->satuan);
                            @endphp
                            <tr class="item-row hover:bg-gray-50/50 transition-colors"
                                data-bahan-id="{{ $item->id }}"
                                data-kode="{{ $item->id_bahan_baku }}"
                                data-harga="{{ $harga }}">
                                <input type="hidden" name="item_checked[{{ $item->id }}]" value="1">
                                <input type="hidden" name="harga_satuan[{{ $item->id }}]" class="harga-satuan-hidden" value="{{ $harga }}">
                                
                                <td class="py-3.5 px-4 text-center text-xs text-gray-500 font-semibold row-number">
                                    {{ $idx + 1 }}
                                </td>
                                <td class="py-3.5 px-6">
                                    <p class="font-bold text-gray-900 item-nama">{{ $item->nama_bahan }}</p>
                                </td>
                                <td class="py-3.5 px-4 text-center text-xs font-semibold text-gray-700">
                                    @if(!empty($item->kebutuhan_10_porsi_base))
                                        <span title="Total kebutuhan 10 porsi menu habis">{{ \App\Helpers\UnitHelper::formatQuantity($item->kebutuhan_10_porsi_base, $item->satuan) }}</span>
                                    @else
                                        {{ number_format($kebutuhanTotal, fmod($kebutuhanTotal, 1) === 0.0 ? 0 : 2, ',', '.') }} {{ $satuanDasar }}
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-bold {{ $stokVal > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-gray-100 text-gray-500' }}">
                                        {{ \App\Helpers\UnitHelper::formatQuantity($stokVal, $item->satuan) }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center text-xs font-semibold text-gray-500">
                                    {{ $sudahPesanVal > 0 ? number_format($sudahPesanVal, fmod($sudahPesanVal, 1) === 0.0 ? 0 : 2, ',', '.') . ' ' . $satuanDasar : '-' }}
                                </td>
                                <td class="py-3.5 px-6 text-right">
                                    <input type="text" inputmode="decimal"
                                        name="jumlah_beli[{{ $item->id }}]"
                                        value="{{ $qty > 0 ? (fmod($qty, 1) === 0.0 ? (int)$qty : $qty) : 0 }}"
                                        oninput="this.value = this.value.replace(/[^0-9.]/g, ''); updateRowTotal(this)"
                                        class="w-28 text-right rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 text-sm py-2 px-3 qty-input outline-none font-bold text-gray-900">
                                </td>
                                <td class="py-3.5 px-4 text-center text-gray-800 text-xs font-bold">
                                    {{ $satuanBeli }}
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <button type="button" onclick="removePoRow(this)" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus Bahan">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr id="emptyRow">
                                <td colspan="8" class="py-12 text-center text-gray-400 font-medium">
                                    @if(isset($pesanan))
                                        <div class="space-y-1">
                                            <p class="font-bold text-emerald-700 text-sm">Semua bahan baku untuk pesanan ini sudah mencukupi di stok!</p>
                                            <p class="text-xs text-gray-500">Tidak ada kekurangan bahan baku yang perlu dipesan. Anda tetap dapat menambahkan bahan lain menggunakan pencarian di atas.</p>
                                        </div>
                                    @else
                                        Belum ada bahan baku di dalam daftar PO. Pilih pesanan katering atau gunakan <span class="font-bold text-gray-700">"Cari & Tambah Bahan Baku"</span> di atas.
                                    @endif
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination Bar --}}
                <div class="px-6 py-3.5 border-t border-gray-100 bg-gray-50/40 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-gray-600" id="paginationControls">
                    <div>
                        Menampilkan <span class="font-bold text-gray-900" id="pageStart">1</span> - <span class="font-bold text-gray-900" id="pageEnd">10</span> dari <span class="font-bold text-gray-900" id="totalItems">0</span> bahan baku
                    </div>
                    <div class="flex items-center gap-1.5" id="paginationButtons">
                    </div>
                </div>

                {{-- Action Buttons & Summary --}}
                <div class="border-t border-gray-100 px-6 py-4 bg-gray-50/60 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div class="text-xs text-gray-500 font-medium">
                        Total item yang akan dipesan: <span class="font-bold text-gray-900" id="totalItemsSummary">0</span> bahan baku
                    </div>
                    <div class="flex items-center gap-3">
                        <x-ui.button type="button" variant="secondary" href="{{ route('pengadaan.po.index') }}">
                            Batal
                        </x-ui.button>
                        <x-ui.button type="submit" variant="primary" icon="check">
                            Buat PO
                        </x-ui.button>
                    </div>
                </div>
            </div>

            {{-- CARD 3: BAHAN BAKU DENGAN STOK MENCUKUPI (INFORMASI TRANSPARANSI) --}}
            @if(isset($itemsCukup) && $itemsCukup->isNotEmpty())
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200/80 overflow-hidden" x-data="{ open: false }">
                <button type="button" @click="open = !open" class="w-full px-6 py-4 bg-gray-50/60 hover:bg-gray-100/60 border-b border-gray-100 flex items-center justify-between text-left transition-colors">
                    <div class="flex items-center gap-3">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        <h3 class="text-xs font-extrabold text-gray-700 uppercase tracking-wider">Bahan Baku dengan Stok Mencukupi ({{ $itemsCukup->count() }} item - Tidak Perlu Dipesan)</h3>
                    </div>
                    <span class="text-xs text-gray-500 font-semibold flex items-center gap-1">
                        <span x-text="open ? 'Sembunyikan' : 'Lihat Rincian'"></span>
                        <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </span>
                </button>

                <div x-show="open" class="p-6 space-y-3" style="display: none;">
                    <p class="text-xs text-gray-500">Bahan baku di bawah ini telah tersedia dalam jumlah yang mencukupi saldo stok {{ (optional($pesanan)->jenis_pesanan_id == 3) ? 'harian' : 'katering' }}, sehingga otomatis tidak dimasukkan ke dalam pesanan pembelian (PO).</p>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200 text-gray-500 font-bold uppercase">
                                    <th class="py-2.5 px-4">Bahan Baku</th>
                                    <th class="py-2.5 px-4">Menu Pengguna</th>
                                    <th class="py-2.5 px-4 text-center">Kebutuhan BOM</th>
                                    <th class="py-2.5 px-4 text-center">Stok Tersedia</th>
                                    <th class="py-2.5 px-4 text-center">Kekurangan</th>
                                    <th class="py-2.5 px-4 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($itemsCukup as $ic)
                                @php
                                    $satuanTxt = optional($ic->satuan)->singkatan ?? optional($ic->satuan)->nama_satuan ?? '-';
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="py-2 px-4 font-semibold text-gray-800">{{ $ic->nama_bahan }}</td>
                                    <td class="py-2 px-4 text-gray-500">{{ $ic->menu_nama ?: '-' }}</td>
                                    <td class="py-2 px-4 text-center font-bold text-gray-700">{{ number_format($ic->kebutuhan_total, fmod($ic->kebutuhan_total, 1) === 0.0 ? 0 : 2, ',', '.') }} {{ $satuanTxt }}</td>
                                    <td class="py-2 px-4 text-center font-bold text-emerald-700">{{ number_format($ic->stok_saat_ini, fmod($ic->stok_saat_ini, 1) === 0.0 ? 0 : 2, ',', '.') }} {{ $satuanTxt }}</td>
                                    <td class="py-2 px-4 text-center font-bold text-gray-400">0 {{ $satuanTxt }}</td>
                                    <td class="py-2 px-4 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Stok Cukup
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

        </form>
    </div>
</div>

@push('scripts')
<script>
    function formatRupiahValue(val) {
        if (val === '' || val === null || val === undefined) return '';
        let raw = String(val).replace(/[^0-9]/g, '');
        if (!raw) return '';
        return parseInt(raw, 10).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function formatRupiah(amount) {
        return 'Rp ' + (amount ? formatRupiahValue(Math.round(amount)) : '0');
    }

    function parseNumericValue(valStr) {
        if (!valStr) return 0;
        let clean = valStr.toString().replace(/[^0-9]/g, '');
        return parseFloat(clean) || 0;
    }

    function formatRowHargaInput(input) {
        input.value = formatRupiahValue(input.value);
    }

    function formatPhone08(input) {
        let val = input.value.replace(/[^0-9]/g, '');
        if (val.length > 0) {
            if (val.startsWith('628')) {
                val = '08' + val.substring(3);
            } else if (val.startsWith('8')) {
                val = '0' + val;
            } else if (val.startsWith('0')) {
                if (val.length >= 2 && !val.startsWith('08')) {
                    val = '08' + val.substring(1).replace(/^0+/, '');
                }
            } else {
                val = '08' + val;
            }
        }
        input.value = val;
    }

    const suppliersPhoneMap = {
        @foreach($suppliers as $sup)
            "{{ addslashes($sup->nama_pemasok) }}": "{{ $sup->nomor_telepon }}",
        @endforeach
    };

    function autoFillSupplierPhone(input) {
        const phoneInput = document.getElementById('supplier_telepon');
        if (!phoneInput) return;
        const val = input.value.trim();
        if (suppliersPhoneMap[val]) {
            phoneInput.value = suppliersPhoneMap[val];
            formatPhone08(phoneInput);
        }
    }

    function updateRowTotal(input) {
        recalcTotal();
    }

    function recalcTotal() {
        const rows = document.querySelectorAll('.item-row');
        const count = rows.length;

        const totalSummaryEl = document.getElementById('totalItemsSummary');
        if (totalSummaryEl) {
            totalSummaryEl.textContent = count;
        }

        const badge = document.getElementById('itemCountBadge');
        if (badge) {
            badge.textContent = count + ' item wajib dibeli';
        }
    }

    function removePoRow(button) {
        const tr = button.closest('tr');
        if (!tr) return;

        tr.remove();
        recalcTotal();
        filterAndPaginateTable(currentPage);

        const remainingRows = document.querySelectorAll('.item-row');
        if (remainingRows.length === 0) {
            const tbody = document.getElementById('poTableBody');
            tbody.innerHTML = `
                <tr id="emptyRow">
                    <td colspan="8" class="py-12 text-center text-gray-400 font-medium">
                        Belum ada bahan baku di dalam daftar PO. Gunakan <span class="font-bold text-gray-700">"Cari & Tambah Bahan Baku"</span> di atas untuk menambahkan bahan.
                    </td>
                </tr>
            `;
        }
    }

    // Client-side Pagination & Searching
    const ITEMS_PER_PAGE = 15;
    let currentPage = 1;

    function filterAndPaginateTable(page = 1) {
        const addBahanInput = document.getElementById('addBahanInput');
        const query = (addBahanInput ? addBahanInput.value : '').toLowerCase().trim();

        const allRows = Array.from(document.querySelectorAll('.item-row'));
        const matchedRows = allRows.filter(row => {
            if (!query) return true;
            const nama = (row.querySelector('.item-nama')?.textContent || '').toLowerCase();
            const kode = (row.getAttribute('data-kode') || '').toLowerCase();
            return nama.includes(query) || kode.includes(query);
        });

        const totalMatched = matchedRows.length;
        const totalPages = Math.ceil(totalMatched / ITEMS_PER_PAGE) || 1;
        if (currentPage > totalPages) currentPage = totalPages;

        const startIdx = (currentPage - 1) * ITEMS_PER_PAGE;
        const endIdx = startIdx + ITEMS_PER_PAGE;

        allRows.forEach(row => row.style.display = 'none');

        matchedRows.slice(startIdx, endIdx).forEach((row, idx) => {
            row.style.display = '';
            const numCell = row.querySelector('.row-number');
            if (numCell) numCell.textContent = startIdx + idx + 1;
        });

        updatePaginationUI(totalMatched, startIdx, endIdx, totalPages);
        recalcTotal();
    }

    function updatePaginationUI(total, startIdx, endIdx, totalPages) {
        const pageStartEl = document.getElementById('pageStart');
        const pageEndEl = document.getElementById('pageEnd');
        const totalItemsEl = document.getElementById('totalItems');
        const container = document.getElementById('paginationButtons');

        if (!pageStartEl || !container) return;

        pageStartEl.textContent = total > 0 ? startIdx + 1 : 0;
        pageEndEl.textContent = Math.min(endIdx, total);
        totalItemsEl.textContent = total;

        container.innerHTML = '';
        if (totalPages <= 1) return;

        const createBtn = (text, targetPage, active = false, disabled = false) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = `px-2.5 py-1 rounded-lg text-xs font-semibold transition-all ${
                active ? 'bg-primary text-white shadow-xs' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50'
            } ${disabled ? 'opacity-40 cursor-not-allowed' : ''}`;
            btn.textContent = text;
            if (!disabled) {
                btn.onclick = () => {
                    currentPage = targetPage;
                    filterAndPaginateTable(targetPage);
                };
            }
            return btn;
        };

        // Prev Button
        container.appendChild(createBtn('Sebelumnya', currentPage - 1, false, currentPage === 1));

        for (let i = 1; i <= totalPages; i++) {
            if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                container.appendChild(createBtn(i, i, i === currentPage));
            } else if (i === currentPage - 2 || i === currentPage + 2) {
                const dots = document.createElement('span');
                dots.className = 'px-1 text-gray-400';
                dots.textContent = '...';
                container.appendChild(dots);
            }
        }

        // Next Button
        container.appendChild(createBtn('Selanjutnya', currentPage + 1, false, currentPage === totalPages));
    }

    const allBahanDataMap = {
        @foreach($allBahanBaku as $bb)
            "{{ addslashes($bb->nama_bahan) }} ({{ $bb->id_bahan_baku }})": {
                id: {{ $bb->id }},
                nama: "{{ addslashes($bb->nama_bahan) }}",
                kode: "{{ $bb->id_bahan_baku }}",
                satuan: "{{ \App\Helpers\UnitHelper::getPurchasingUnit($bb->satuan) }}",
                satuan_dasar: "{{ \App\Helpers\UnitHelper::getBaseUnit($bb->satuan) }}",
                harga: {{ (float)\App\Helpers\UnitHelper::toPurchasingPrice($bb->harga_satuan ?? 0, $bb->satuan) }},
                stok_minimal: {{ (float)\App\Helpers\UnitHelper::toPurchasingQuantity($bb->stok_minimal ?? 5, $bb->satuan) }},
                stok_katering: {{ (float)(optional($bb->stok_catering_balance)->jumlah_stok ?? 0) }},
                stok_harian: {{ (float)(optional($bb->stok_harian)->jumlah_stok ?? 0) }}
            },
        @endforeach
    };

    function addCustomBahanRow() {
        const input = document.getElementById('addBahanInput');
        if (!input || !input.value.trim()) return;

        const val = input.value.trim();
        let itemData = allBahanDataMap[val];

        if (!itemData) {
            const foundKey = Object.keys(allBahanDataMap).find(k => k.toLowerCase().includes(val.toLowerCase()) || allBahanDataMap[k].nama.toLowerCase().includes(val.toLowerCase()));
            if (foundKey) itemData = allBahanDataMap[foundKey];
        }

        if (!itemData) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'warning', title: 'Bahan Baku Tidak Ditemukan', text: 'Silakan pilih bahan baku dari daftar sugesti yang tersedia.', confirmButtonColor: '#0D3024' });
            } else {
                alert('Bahan baku tidak ditemukan. Pilih bahan baku dari daftar sugesti.');
            }
            return;
        }

        const bahanId = itemData.id;
        const existingRow = document.querySelector(`.item-row[data-bahan-id="${bahanId}"]`);
        if (existingRow) {
            existingRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
            existingRow.classList.add('bg-amber-100');
            setTimeout(() => existingRow.classList.remove('bg-amber-100'), 1500);
            input.value = '';
            recalcTotal();
            filterAndPaginateTable(currentPage);
            return;
        }

        const emptyRow = document.getElementById('emptyRow');
        if (emptyRow) emptyRow.remove();

        const tbody = document.getElementById('poTableBody');

        const isNasiBox = '{{ optional($pesanan)->jenis_pesanan_id }}' === '3';
        const isCatering = ('{{ $tipe }}' === 'Catering' || '{{ $tipe }}' === 'Katering') && !isNasiBox;
        const stokVal = isCatering ? (itemData.stok_katering || 0) : (itemData.stok_harian || 0);
        const stokFormatted = Number(stokVal).toLocaleString('id-ID');

        const unitPrice = itemData.harga || 0;
        const defaultQty = itemData.stok_minimal ? (itemData.stok_minimal * 2) : 1;

        const tr = document.createElement('tr');
        tr.className = 'item-row hover:bg-gray-50/50 transition-colors bg-amber-50/40';
        tr.setAttribute('data-bahan-id', bahanId);
        tr.setAttribute('data-kode', itemData.kode);
        tr.setAttribute('data-harga', unitPrice);

        tr.innerHTML = `
            <input type="hidden" name="item_checked[${bahanId}]" value="1">
            <input type="hidden" name="harga_satuan[${bahanId}]" class="harga-satuan-hidden" value="${unitPrice}">
            <td class="py-3.5 px-4 text-center text-xs text-gray-500 font-semibold row-number">-</td>
            <td class="py-3.5 px-6">
                <p class="font-bold text-gray-900 item-nama">${itemData.nama}</p>
            </td>
            <td class="py-3.5 px-4 text-center text-xs font-semibold text-gray-700">-</td>
            <td class="py-3.5 px-4 text-center">
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-bold ${stokVal > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-gray-100 text-gray-500'}">
                    ${stokFormatted} ${itemData.satuan_dasar}
                </span>
            </td>
            <td class="py-3.5 px-4 text-center text-xs font-semibold text-gray-500">-</td>
            <td class="py-3.5 px-6 text-right">
                <input type="text" inputmode="decimal"
                    name="jumlah_beli[${bahanId}]"
                    value="${defaultQty}"
                    oninput="this.value = this.value.replace(/[^0-9.]/g, ''); updateRowTotal(this)"
                    class="w-28 text-right rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 text-sm py-2 px-3 qty-input outline-none font-bold text-gray-900">
            </td>
            <td class="py-3.5 px-4 text-center text-gray-800 text-xs font-bold">
                ${itemData.satuan}
            </td>
            <td class="py-3.5 px-4 text-center">
                <button type="button" onclick="removePoRow(this)" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus Bahan">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </button>
            </td>
        `;

        tbody.appendChild(tr);
        input.value = '';
        recalcTotal();
        filterAndPaginateTable(currentPage);
    }

    document.addEventListener('DOMContentLoaded', () => {
        recalcTotal();
        filterAndPaginateTable(1);

        const form = document.getElementById('poForm');
        if (form) {
            form.addEventListener('submit', function() {
                const rows = document.querySelectorAll('.item-row');
                rows.forEach(tr => {
                    const qtyInput = tr.querySelector('.qty-input');
                    const qty = parseNumericValue(qtyInput ? qtyInput.value : 0);
                    if (qty <= 0) {
                        tr.querySelectorAll('input').forEach(input => input.disabled = true);
                    }
                });
            });
        }
    });
</script>
@endpush
@endsection