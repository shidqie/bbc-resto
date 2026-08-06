@extends('layouts.pos')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800 pb-10">
    <div class="w-full p-6 space-y-5">
        {{-- PAGE HEADER --}}
        <x-ui.page-header title="Kelola Resep Menu" subtitle="Resep (BOM) untuk menu satuan dan komposisi untuk paket — Dine In, Katering, dan Nasi Box." :breadcrumbs="['Manajemen Menu', 'Resep Menu']" />

        <x-ui.alert />

        {{-- Table with integrated toolbar --}}
        <x-ui.data-table :paginator="$menus">
            <x-slot:toolbar>
                <form action="{{ route('resep.index') }}" method="GET" class="flex flex-col xl:flex-row items-start xl:items-center justify-between gap-4 w-full">
                    <div class="w-full xl:max-w-sm shrink-0">
                        <x-search-input name="search" value="{{ request('search') }}" placeholder="Cari nama menu..." width="w-full" />
                    </div>
                    <div class="flex flex-wrap items-center gap-2 w-full xl:w-auto">
                        <x-ui.multi-select name="layanan" :options="['1' => 'Dine In', '2' => 'Katering', '3' => 'Nasi Box']" :selected="request('layanan')" label="Layanan" type="radio" />
                        <x-ui.multi-select name="kategori" :options="$kategoris->pluck('nama_kategori', 'id')->toArray()" :selected="request('kategori')" label="Kategori" type="radio" />
                        <x-ui.multi-select name="status_resep" :options="['ada' => 'Sudah Ada Resep', 'belum' => 'Belum Ada Resep']" :selected="request('status_resep')" label="Status Resep" type="radio" />
                        @if(request('search') || request('kategori') || request('layanan') || request('status_resep'))
                            <a href="{{ route('resep.index') }}" class="text-xs font-medium text-red-500 hover:text-red-700 px-2 py-2 rounded-lg hover:bg-red-50 transition-colors shrink-0">Reset</a>
                        @endif
                    </div>
                </form>
            </x-slot:toolbar>

            <x-ui.table class="min-w-[1200px]">
                <x-ui.table.header>
                    <th class="px-4 py-3.5 text-left w-12">No</th>
                    <th class="px-4 py-3.5 text-left">Kode</th>
                    <th class="px-4 py-3.5 text-left">Nama Menu / Paket</th>
                    <th class="px-4 py-3.5 text-left">Layanan</th>
                    <th class="px-4 py-3.5 text-left">Kategori</th>
                    <th class="px-4 py-3.5 text-left">Status Resep</th>
                    <th class="px-4 py-3.5 text-center">Aksi</th>
                </x-ui.table.header>
                <tbody class="divide-y divide-gray-100">
                    @forelse($menus as $menu)
                    @php
                        $jenisKode = strtolower($menu->jenis_menu->kode_jenis ?? '');
                        $isPaket = in_array($jenisKode, ['catering', 'nasi_box']) && $menu->komponen_paket_count > 0;
                        $paketItems = $isPaket ? $menu->komponen_paket : collect();
                        $komposisiLengkap = true;
                        if ($isPaket) {
                            foreach ($paketItems as $k) {
                                if ($k->tipe_item == 'tetap' && ! $k->menu_id_terkait) {
                                    $komposisiLengkap = false;
                                } elseif ($k->tipe_item == 'pilihan' && $k->opsi->isEmpty()) {
                                    $komposisiLengkap = false;
                                }
                            }
                        }
                        $resepLengkap = $menu->resep_menu_count > 0 && $menu->resep_menu->every(fn ($r) => $r->dikonfirmasi);
                    @endphp
                    @if($isPaket)
                        {{-- Baris header paket --}}
                        <x-ui.table.row class="bg-amber-50/70 border-b border-amber-100">
                            <td class="px-4 py-2.5 text-gray-500 font-medium">{{ $menus->firstItem() + $loop->index }}</td>
                            <td class="px-4 py-2.5 font-mono text-xs text-gray-400">{{ $menu->kode_menu }}</td>
                            <td class="px-4 py-2.5">
                                <div class="flex items-center gap-2">
                                    <x-ui.badge color="warning" size="xs" class="uppercase">Paket</x-ui.badge>
                                    <span class="font-semibold text-gray-800">{{ $menu->nama_menu }}</span>
                                    <span class="text-xs text-gray-400 font-medium">{{ $paketItems->count() }} item</span>
                                </div>
                            </td>
                            <td class="px-4 py-2.5">
                                @if($jenisKode == 'catering')
                                    <x-ui.badge color="warning" size="sm">Katering</x-ui.badge>
                                @else
                                    <x-ui.badge color="gray" size="sm">Nasi Box</x-ui.badge>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 text-gray-500">{{ $menu->kategori_menu->nama_kategori ?? '-' }}</td>
                            <td class="px-4 py-2.5">
                                @if($komposisiLengkap)
                                    <x-ui.badge color="success" size="sm">Komposisi Lengkap</x-ui.badge>
                                @else
                                    <x-ui.badge color="warning" size="sm">Komposisi Belum Lengkap</x-ui.badge>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 text-center">
                                @include('menu.resep._aksi', [
                                    'aksiMenuId' => $menu->id,
                                    'aksiNamaMenu' => $menu->nama_menu,
                                    'aksiIsPaket' => true,
                                    'aksiUsed' => $menuUsedIds->contains($menu->id),
                                    'aksiStatus' => (bool) $menu->status_aktif,
                                ])
                            </td>
                        </x-ui.table.row>
                        {{-- Baris item komposisi paket --}}
                        @foreach($paketItems as $item)
                            @if($item->tipe_item == 'pilihan')
                                {{-- Kelompok pilihan --}}
                                <x-ui.table.row class="bg-white border-b border-dashed border-gray-100">
                                    <td class="px-4 py-1"></td>
                                    <td class="px-4 py-1"></td>
                                    <td class="px-4 py-1 pl-10">
                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-gray-500 uppercase tracking-wide">
                                            <x-heroicon-o-tag class="w-3.5 h-3.5 text-amber-500" />
                                            {{ $item->nama_item }}
                                        </span>
                                        <span class="text-[10px] text-gray-400 ml-1.5">pilih {{ $item->minimum_pilihan }}–{{ $item->maksimum_pilihan }}</span>
                                    </td>
                                    <td class="px-4 py-1"></td>
                                    <td class="px-4 py-1"></td>
                                    <td class="px-4 py-1 text-gray-300">-</td>
                                    <td class="px-4 py-1"></td>
                                </x-ui.table.row>
                                @foreach($item->opsi as $opsi)
                                    @php
                                        $linked = $opsi->menu ?? null;
                                    @endphp
                                    <x-ui.table.row>
                                        <td class="px-4 py-1.5"></td>
                                        <td class="px-4 py-1.5"></td>
                                        <td class="px-4 py-1.5 pl-16">
                                            <span class="text-sm text-gray-700">{{ $opsi->nama_pilihan }}</span>
                                        </td>
                                        <td class="px-4 py-1.5"></td>
                                        <td class="px-4 py-1.5"></td>
                                        <td class="px-4 py-1.5">
                                            @include('menu.resep._status', ['menu' => $linked])
                                        </td>
                                        <td class="px-4 py-1.5 text-center">
                                            @include('menu.resep._aksi', [
                                                'aksiMenuId' => $linked?->id,
                                                'aksiNamaMenu' => $linked?->nama_menu,
                                                'aksiIsPaket' => false,
                                                'aksiUsed' => $linked ? $menuUsedIds->contains($linked->id) : true,
                                                'aksiStatus' => $linked ? (bool) $linked->status_aktif : true,
                                            ])
                                        </td>
                                    </x-ui.table.row>
                                @endforeach
                            @else
                                @php
                                    $linked = $item->menu_terkait;
                                @endphp
                                <x-ui.table.row>
                                    <td class="px-4 py-1.5"></td>
                                    <td class="px-4 py-1.5"></td>
                                    <td class="px-4 py-1.5 pl-10">
                                        <div class="flex items-center gap-1.5">
                                            <x-heroicon-o-check-badge class="w-3.5 h-3.5 text-green-500" />
                                            <span class="text-sm text-gray-700">{{ $item->nama_item }}</span>
                                            <span class="text-[10px] text-gray-400">tetap</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-1.5"></td>
                                    <td class="px-4 py-1.5"></td>
                                    <td class="px-4 py-1.5">
                                        @include('menu.resep._status', ['menu' => $linked])
                                    </td>
                                    <td class="px-4 py-1.5 text-center">
                                        @include('menu.resep._aksi', [
                                            'aksiMenuId' => $linked?->id,
                                            'aksiNamaMenu' => $linked?->nama_menu,
                                            'aksiIsPaket' => false,
                                            'aksiUsed' => $linked ? $menuUsedIds->contains($linked->id) : true,
                                            'aksiStatus' => $linked ? (bool) $linked->status_aktif : true,
                                        ])
                                    </td>
                                </x-ui.table.row>
                            @endif
                        @endforeach
                    @else
                    <x-ui.table.row>
                        <td class="px-4 py-4 text-gray-500 font-medium align-middle">{{ $menus->firstItem() + $loop->index }}</td>
                        <td class="px-4 py-4 align-middle font-mono text-sm text-gray-500">{{ $menu->kode_menu }}</td>
                        <td class="px-4 py-4 align-middle font-medium text-gray-900">{{ $menu->nama_menu }}</td>
                        <td class="px-4 py-4 align-middle">
                            @if($jenisKode == 'dine_in' || $jenisKode == 'reguler')
                                <x-ui.badge color="primary" size="sm">Dine In</x-ui.badge>
                            @elseif($jenisKode == 'catering')
                                <x-ui.badge color="warning" size="sm">Katering</x-ui.badge>
                            @elseif($jenisKode == 'nasi_box')
                                <x-ui.badge color="gray" size="sm">Nasi Box</x-ui.badge>
                            @else
                                <span class="text-gray-500">{{ $menu->jenis_menu->nama_jenis ?? '-' }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 align-middle text-gray-500">{{ $menu->kategori_menu->nama_kategori ?? '-' }}</td>
                        <td class="px-4 py-4 align-middle">
                            @include('menu.resep._status', ['menu' => $menu])
                        </td>
                        <td class="px-4 py-4 align-middle text-center">
                            @include('menu.resep._aksi', [
                                'aksiMenuId' => $menu->id,
                                'aksiNamaMenu' => $menu->nama_menu,
                                'aksiIsPaket' => false,
                                'aksiUsed' => $menuUsedIds->contains($menu->id),
                                'aksiStatus' => (bool) $menu->status_aktif,
                            ])
                        </td>
                    </x-ui.table.row>
                    @endif
                    @empty
                    <x-empty-state icon="document-text" title="Belum ada data menu" message="Tambahkan menu terlebih dahulu" :colspan="7" />
                    @endforelse
                </tbody>
            </x-ui.table>
        </x-ui.data-table>

    </div>
</div>

{{-- ══════════════════════════════════════════ --}}
{{-- DRAWER: ATUR RESEP / BOM (SLIDE-IN RIGHT) --}}
{{-- ══════════════════════════════════════════ --}}
<div id="drawerResepForm" class="fixed inset-x-0 bottom-0 top-16 z-40 hidden">
    <div class="absolute inset-0 bg-black/30 backdrop-blur-[2px]" onclick="closeResepForm()"></div>
    <div class="absolute right-0 top-0 h-full w-full max-w-2xl bg-white shadow-2xl flex flex-col translate-x-full transition-transform duration-300" id="drawerResepFormPanel">

        {{-- Header --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 shrink-0">
            <div>
                <h2 class="font-semibold text-gray-900" id="resepFormTitle">Atur Resep (BOM)</h2>
                <p class="text-xs text-gray-400 mt-0.5" id="resepFormSubtitle">-</p>
            </div>
            <button onclick="closeResepForm()" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Info menu --}}
        <div class="px-5 py-3 bg-gray-50 border-b border-gray-100 grid grid-cols-2 md:grid-cols-4 gap-3 text-xs shrink-0" id="resepFormInfo"></div>

        {{-- Form Body --}}
        <form id="formResep" action="" method="POST" class="flex-1 overflow-y-auto">
            @csrf
            <div class="px-5 py-4">
                <div class="mb-3 flex justify-between items-end">
                    <h3 class="text-sm font-bold text-gray-900">Tabel Resep</h3>
                    <label class="flex items-center gap-1.5 text-xs font-medium text-gray-500">
                        <input type="checkbox" name="dikonfirmasi" value="1" id="resepDikonfirmasi" class="rounded border-gray-300 text-gray-900 focus:ring-gray-400">
                        Resep Lengkap (terkonfirmasi)
                    </label>
                </div>
                <div id="resepBahanContainer" class="space-y-2">
                    {{-- JS rendered rows --}}
                </div>
                <button type="button" id="btnTambahBahanResep" onclick="addBahanBakuRowResep()" class="mt-3 inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-gray-900 text-white rounded-lg hover:bg-gray-800 transition-colors">
                    <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Bahan Baku
                </button>
            </div>

            <div class="px-5 py-4 border-t border-gray-100 flex items-center justify-between gap-2 bg-gray-50/80 shrink-0">
                <button type="button" id="resepFormHapus" class="hidden inline-flex items-center gap-1.5 px-3 py-2 text-sm font-semibold text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors">
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Hapus Resep
                </button>
                <div class="flex gap-2 ml-auto">
                    <button type="button" id="btnBatalResep" onclick="closeResepForm()" class="px-4 py-2 text-sm font-semibold text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">Batal</button>
                    <button type="submit" id="btnSimpanResep" class="px-5 py-2 text-sm font-semibold text-white bg-gray-900 rounded-lg hover:bg-gray-800 transition-colors">Simpan Resep</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════ --}}
{{-- DRAWER: ATUR KOMPOSISI PAKET (SLIDE-IN RIGHT) --}}
{{-- ══════════════════════════════════════════ --}}
<div id="drawerKomposisi" class="fixed inset-x-0 bottom-0 top-16 z-40 hidden">
    <div class="absolute inset-0 bg-black/30 backdrop-blur-[2px]" onclick="closeKomposisiForm()"></div>
    <div class="absolute right-0 top-0 h-full w-full max-w-2xl bg-white shadow-2xl flex flex-col translate-x-full transition-transform duration-300" id="drawerKomposisiPanel">
        {{-- Header --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 shrink-0">
            <div>
                <h2 class="font-semibold text-gray-900">Atur Komposisi Paket</h2>
                <p class="text-xs text-gray-400 mt-0.5" id="komposisiSubtitle">-</p>
            </div>
            <button onclick="closeKomposisiForm()" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form id="formKomposisi" action="" method="POST" class="flex-1 overflow-y-auto">
            @csrf
            <div class="px-5 py-4 space-y-6">
                {{-- MENU TETAP --}}
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-bold text-gray-900 flex items-center gap-1.5">
                            <x-heroicon-o-check-badge class="w-4 h-4 text-green-500" />
                            Menu Tetap
                        </h3>
                        <button type="button" onclick="addTetapRow()" class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg hover:bg-emerald-100 transition-colors">
                            + Tambah Menu Tetap
                        </button>
                    </div>
                    <div id="tetapContainer" class="space-y-2"></div>
                </div>

                {{-- KELOMPOK PILIHAN --}}
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-bold text-gray-900 flex items-center gap-1.5">
                            <x-heroicon-o-tag class="w-4 h-4 text-amber-500" />
                            Kelompok Pilihan
                        </h3>
                        <button type="button" onclick="addKelompokRow()" class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200 rounded-lg hover:bg-amber-100 transition-colors">
                            + Tambah Kelompok Pilihan
                        </button>
                    </div>
                    <div id="kelompokContainer" class="space-y-3"></div>
                </div>
            </div>

            <div class="px-5 py-4 border-t border-gray-100 flex justify-end gap-2 bg-gray-50/80 shrink-0">
                <button type="button" onclick="closeKomposisiForm()" class="px-4 py-2 text-sm font-semibold text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">Batal</button>
                <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-gray-900 rounded-lg hover:bg-gray-800 transition-colors">Simpan Komposisi</button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════ --}}
{{-- DRAWER: KONFIRMASI HAPUS RESEP (SLIDE-IN RIGHT) --}}
{{-- ══════════════════════════════════════════ --}}
<div id="drawerHapusResep" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/30 backdrop-blur-[2px]" onclick="closeDeleteResepModal()"></div>
    <div class="absolute right-0 top-0 h-full w-full max-w-sm bg-white shadow-2xl flex flex-col translate-x-full transition-transform duration-300" id="drawerHapusResepPanel">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 shrink-0">
            <div>
                <h3 class="font-semibold text-gray-900">Hapus Resep Menu</h3>
                <p class="text-xs text-gray-400 mt-0.5">Hanya data resep yang dihapus, menu tetap ada</p>
            </div>
            <button onclick="closeDeleteResepModal()" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="px-5 py-6 flex-1">
            <div class="flex items-start gap-3">
                <div class="w-11 h-11 rounded-full bg-red-50 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <p class="text-sm text-gray-600 font-medium leading-relaxed">Yakin ingin menghapus resep <span id="hapusResepNama" class="font-bold text-gray-900"></span>? Komposisi bahan baku akan dihapus.</p>
            </div>
        </div>

        <form id="formHapusResep" method="POST" action="" class="px-5 py-4 border-t border-gray-100 flex gap-3 bg-gray-50/80 shrink-0">
            @csrf @method('DELETE')
            <button type="button" onclick="closeDeleteResepModal()" class="flex-1 text-sm font-semibold text-gray-600 bg-white border border-gray-200 rounded-lg px-4 py-2.5 hover:bg-gray-50 transition-colors">Batal</button>
            <button type="submit" class="flex-1 text-sm font-semibold text-white bg-red-500 rounded-lg px-4 py-2.5 hover:bg-red-600 transition-colors">Ya, Hapus</button>
        </form>
    </div>
</div>

<style>
    .view-mode input:disabled,
    .view-mode select:disabled,
    .view-mode textarea:disabled {
        background-color: transparent !important;
        border-color: transparent !important;
        color: #111827 !important;
        font-weight: 500;
        padding-left: 0 !important;
        padding-right: 0 !important;
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        background-image: none !important;
        resize: none;
        box-shadow: none !important;
    }
    .view-mode .border-t {
        border-color: transparent !important;
    }
</style>

@push('scripts')
<script>
const BASE_URL = '{{ url('/') }}';
const resepMenusData = @json($jsResepMenus);
const bahanBakusData = @json($bahanBakus);
const paketKomposisiData = @json($jsPaketKomposisi);
const menuSatuanOptions = @json($menuSatuanOptions);
const satuanSajianOptions = @json($daftarSatuanSajian);

// ═══ DRAWER ATUR RESEP ═══
function bahanOptionHtml(selId) {
    return bahanBakusData.map(b => {
        const sat = (b.satuan && b.satuan.nama_satuan) ? b.satuan.nama_satuan : '';
        const selected = b.id == selId ? 'selected' : '';
        return `<option value="${b.id}" data-satuan="${sat}" ${selected}>${b.nama_bahan} (${sat})</option>`;
    }).join('');
}

function satuanResepCell(bahanId) {
    const b = bahanBakusData.find(x => x.id == bahanId);
    const sat = (b && b.satuan && b.satuan.nama_satuan) ? b.satuan.nama_satuan : '-';
    return `<span class="text-xs font-medium text-gray-500">${sat}</span>`;
}

function addBahanBakuRowResep(selId = '', qty = '', readOnly = false, keterangan = '', resepSatuan = '') {
    const container = document.getElementById('resepBahanContainer');
    const row = document.createElement('div');
    row.className = 'resep-row grid grid-cols-[1fr_110px_110px_1.6fr_auto] gap-2 items-center';
    const selDisabled = readOnly ? ' disabled' : '';
    const rmDisabled = readOnly ? ' opacity-40 pointer-events-none' : '';
    row.innerHTML = `
        <div>
            <label class="block text-[10px] font-semibold text-gray-400 mb-0.5">Bahan Baku *</label>
            <select name="bahan_baku_id[]" required ${selDisabled} class="w-full px-2 py-1.5 text-xs border border-gray-200 rounded-lg focus:ring-1 focus:ring-gray-400 focus:border-gray-400 outline-none bg-white">
                <option value="" disabled ${selId ? '' : 'selected'}>-- Pilih Bahan --</option>
                ${bahanOptionHtml(selId)}
            </select>
        </div>
        <div>
            <label class="block text-[10px] font-semibold text-gray-400 mb-0.5">Takaran *</label>
            <div x-data="{ val: String('${qty}').replace('.', ','), format(v) { let c = String(v).replace(/[^0-9,]/g, ''); let p = c.split(','); if(p.length > 2) c = p[0] + ',' + p.slice(1).join(''); this.val = c; $refs.hidden.value = c.replace(',', '.'); } }" x-init="format(val)">
                <input type="text" x-model="val" @input="format($event.target.value)" placeholder="0,00" ${selDisabled} ${readOnly ? '' : 'required'} class="w-full px-2 py-1.5 text-xs border border-gray-200 rounded-lg focus:ring-1 focus:ring-gray-400 focus:border-gray-400 outline-none bg-white text-center font-medium">
                <input type="hidden" x-ref="hidden" name="jumlah_kebutuhan[]" value="${qty}" ${selDisabled}>
            </div>
        </div>
        <div>
            <label class="block text-[10px] font-semibold text-gray-400 mb-0.5">Satuan</label>
            <div class="resep-satuan text-xs font-medium text-gray-500 py-1.5 px-1 text-center" data-satuan>${satuanResepCell(selId)}</div>
        </div>
        <div>
            <label class="block text-[10px] font-semibold text-gray-400 mb-0.5">Keterangan</label>
            <input type="text" name="keterangan[]" value="${keterangan}" ${selDisabled} placeholder="cth: bumbu" class="w-full px-2 py-1.5 text-xs border border-gray-200 rounded-lg focus:ring-1 focus:ring-gray-400 focus:border-gray-400 outline-none bg-white">
        </div>
        <div class="pt-4">
            <button type="button" onclick="this.closest('.resep-row').remove()" class="w-7 h-7 flex items-center justify-center rounded-full bg-red-50 text-red-600 hover:bg-red-100 transition-colors ${rmDisabled}" title="Hapus baris">
                <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </button>
        </div>
    `;

    const select = row.querySelector('select[name="bahan_baku_id[]"]');
    select.addEventListener('change', () => {
        const sat = row.querySelector('.resep-satuan');
        const opt = select.options[select.selectedIndex];
        sat.textContent = opt.dataset.satuan || '-';
    });

    container.appendChild(row);
    return row;
}

function openResepForm(menuId, readOnly = false) {
    const menu = resepMenusData.find(m => m.id == menuId);
    if (!menu) return;

    document.getElementById('formResep').action = `${BASE_URL}/menu/${menu.id}/resep`;
    document.getElementById('resepFormTitle').textContent = readOnly ? 'Detail Resep (BOM)' : 'Atur Resep (BOM)';
    document.getElementById('resepFormSubtitle').textContent = (menu.nama_menu ?? '') + ' (' + (menu.kode_menu ?? '') + ')';

    const info = document.getElementById('resepFormInfo');
    const kat = menu.kategori_menu_id ? '' : '';
    info.innerHTML = [
        `<div><div class="text-gray-400">Nama Menu</div><div class="font-semibold text-gray-800">${menu.nama_menu ?? '-'}</div></div>`,
        `<div><div class="text-gray-400">Kode Menu</div><div class="font-semibold text-gray-800">${menu.kode_menu ?? '-'}</div></div>`,
        `<div><div class="text-gray-400">Hasil Resep</div><div class="font-semibold text-gray-800">1 porsi</div></div>`,
        `<div><div class="text-gray-400">Status</div><div class="font-semibold ${menu.status_aktif ? 'text-green-600' : 'text-red-500'}">${menu.status_aktif ? 'Aktif' : 'Nonaktif'}</div></div>`,
    ].join('');

    const container = document.getElementById('resepBahanContainer');
    container.innerHTML = '';

    const reseps = menu.resep_menu ?? [];
    if (reseps.length > 0) {
        reseps.forEach(r => {
            addBahanBakuRowResep(r.bahan_baku_id, r.jumlah_kebutuhan ?? r.jumlah ?? '', readOnly, r.keterangan ?? '', r.satuan_id);
        });
    } else if (readOnly) {
        container.innerHTML = '<p class="text-sm text-gray-400 italic py-2">Belum ada komposisi bahan baku untuk menu ini.</p>';
    } else {
        addBahanBakuRowResep();
    }

    const dikonfirmasi = (menu.resep_lengkap === true);
    document.getElementById('resepDikonfirmasi').checked = dikonfirmasi;
    document.getElementById('resepDikonfirmasi').disabled = readOnly;

    const hapusBtn = document.getElementById('resepFormHapus');
    const count = (menu.resep_menu_count ?? 0);
    if (!readOnly && count > 0) {
        hapusBtn.classList.remove('hidden');
        hapusBtn.onclick = () => openDeleteResepModal(menu.id, menu.nama_menu ?? '');
    } else {
        hapusBtn.classList.add('hidden');
        hapusBtn.onclick = null;
    }

    document.getElementById('btnTambahBahanResep').style.display = readOnly ? 'none' : '';
    document.getElementById('btnSimpanResep').style.display = readOnly ? 'none' : '';
    document.getElementById('btnBatalResep').textContent = readOnly ? 'Tutup' : 'Batal';

    document.getElementById('formResep').classList.toggle('view-mode', readOnly);

    const drawer = document.getElementById('drawerResepForm');
    const panel = document.getElementById('drawerResepFormPanel');
    drawer.classList.remove('hidden');
    drawer.style.display = 'flex';
    requestAnimationFrame(() => {
        panel.classList.remove('translate-x-full');
    });
}

function closeResepForm() {
    const drawer = document.getElementById('drawerResepForm');
    const panel = document.getElementById('drawerResepFormPanel');
    panel.classList.add('translate-x-full');
    setTimeout(() => {
        drawer.classList.add('hidden');
        drawer.style.display = '';
    }, 300);
}

// Validasi & konfirmasi simpan resep
document.addEventListener('submit', function (e) {
    if (e.target && e.target.id === 'formResep') {
        const rows = Array.from(document.querySelectorAll('#resepBahanContainer .resep-row'));
        const seen = new Set();
        for (const row of rows) {
            const sel = row.querySelector('select[name="bahan_baku_id[]"]');
            const qty = row.querySelector('input[name="jumlah_kebutuhan[]"]');
            if (sel && sel.value) {
                if (seen.has(sel.value)) {
                    e.preventDefault();
                    showToast('error', 'Bahan baku yang sama tidak boleh dipilih dua kali.');
                    sel.focus();
                    return;
                }
                seen.add(sel.value);
            }
            if (qty && (!qty.value || parseFloat(qty.value) <= 0)) {
                e.preventDefault();
                showToast('error', 'Takaran harus lebih besar dari nol.');
                qty.focus();
                return;
            }
        }
        if (rows.length === 0) {
            e.preventDefault();
            showToast('error', 'Resep minimal memiliki satu bahan baku.');
            return;
        }
        if (!e.defaultPrevented) {
            e.preventDefault();
            const confirmBtn = e.target.querySelector('button[type="submit"]');
            openConfirmModal('Simpan Resep', 'Yakin ingin menyimpan resep ini?', () => {
                confirmBtn.disabled = true;
                e.target.submit();
            });
        }
    }
});

// ═══ DRAWER ATUR KOMPOSISI ═══
function satuanSajianHtml(val) {
    return satuanSajianOptions.map(s => `<option value="${s}" ${s === val ? 'selected' : ''}>${s}</option>`).join('');
}

function menuSatuanHtml(selId, name) {
    const opts = menuSatuanOptions.map(m => {
        const st = m.resep_lengkap ? ' ✓' : (m.resep_menu_count > 0 ? ` (${m.resep_menu_count} bahan)` : '');
        return `<option value="${m.id}" ${m.id == selId ? 'selected' : ''}>${m.nama_menu}${st}</option>`;
    }).join('');
    return `<select name="${name}" required class="w-full px-2 py-1.5 text-xs border border-gray-200 rounded-lg focus:ring-1 focus:ring-gray-400 focus:border-gray-400 outline-none bg-white">` +
        `<option value="" disabled ${selId ? '' : 'selected'}>-- Pilih Menu --</option>${opts}</select>`;
}

let tetapIdx = 0;
function addTetapRow(data = null) {
    const container = document.getElementById('tetapContainer');
    const idx = tetapIdx++;
    const selId = data ? data.menu_id_terkait : '';
    const jumlah = data ? data.jumlah : 1;
    const satuan = data ? (data.satuan_sajian || 'porsi') : 'porsi';
    const div = document.createElement('div');
    div.className = 'tetap-row grid grid-cols-[1.4fr_90px_120px_auto] gap-2 items-center bg-white border border-gray-200 rounded-lg p-2';
    div.innerHTML = `
        ${menuSatuanHtml(selId, `tetap[${idx}][menu_id]`)}
        <div>
            <label class="block text-[10px] font-semibold text-gray-400 mb-0.5">Jumlah</label>
            <div x-data="{ val: String('${jumlah}').replace('.', ','), format(v) { let c = String(v).replace(/[^0-9,]/g, ''); let p = c.split(','); if(p.length > 2) c = p[0] + ',' + p.slice(1).join(''); this.val = c; $refs.hidden.value = c.replace(',', '.'); } }" x-init="format(val)">
                <input type="text" x-model="val" @input="format($event.target.value)" placeholder="0,00" required class="w-full px-2 py-1.5 text-xs border border-gray-200 rounded-lg outline-none focus:ring-1 focus:ring-gray-400 text-center font-medium">
                <input type="hidden" x-ref="hidden" name="tetap[${idx}][jumlah]" value="${jumlah}">
            </div>
        </div>
        <div>
            <label class="block text-[10px] font-semibold text-gray-400 mb-0.5">Satuan Sajian</label>
            <select name="tetap[${idx}][satuan_sajian]" class="w-full px-2 py-1.5 text-xs border border-gray-200 rounded-lg outline-none bg-white">${satuanSajianHtml(satuan)}</select>
        </div>
        <div class="pt-4">
            <button type="button" onclick="this.closest('.tetap-row').remove()" class="w-7 h-7 flex items-center justify-center rounded-full bg-red-50 text-red-600 hover:bg-red-100 transition-colors" title="Hapus">
                <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    `;
    container.appendChild(div);
}

let kelompokIdx = 0;
function addKelompokRow(data = null) {
    const container = document.getElementById('kelompokContainer');
    const idx = kelompokIdx++;
    const nama = data ? data.nama_item : '';
    const min = data ? data.minimum_pilihan : 1;
    const max = data ? data.maksimum_pilihan : 1;
    const div = document.createElement('div');
    div.className = 'kelompok-row bg-amber-50/60 border border-amber-200 rounded-xl p-3 space-y-2';
    div.innerHTML = `
        <div class="flex items-end gap-2">
            <div class="flex-1">
                <label class="block text-[10px] font-semibold text-gray-400 mb-0.5">Nama Kelompok *</label>
                <input type="text" name="kelompok[${idx}][nama_item]" value="${nama}" required placeholder="cth: Pilihan Nasi" class="w-full px-2 py-1.5 text-xs border border-gray-200 rounded-lg outline-none focus:ring-1 focus:ring-gray-400 font-semibold">
            </div>
            <div class="w-20">
                <label class="block text-[10px] font-semibold text-gray-400 mb-0.5">Min</label>
                <div x-data="{ val: '${min}', format(v) { this.val = String(v).replace(/[^0-9]/g, ''); } }">
                    <input type="text" name="kelompok[${idx}][minimum_pilihan]" x-model="val" @input="format($event.target.value)" class="w-full px-2 py-1.5 text-xs border border-gray-200 rounded-lg outline-none text-center">
                </div>
            </div>
            <div class="w-20">
                <label class="block text-[10px] font-semibold text-gray-400 mb-0.5">Maks</label>
                <div x-data="{ val: '${max}', format(v) { this.val = String(v).replace(/[^0-9]/g, ''); } }">
                    <input type="text" name="kelompok[${idx}][maksimum_pilihan]" x-model="val" @input="format($event.target.value)" class="w-full px-2 py-1.5 text-xs border border-gray-200 rounded-lg outline-none text-center">
                </div>
            </div>
            <button type="button" onclick="this.closest('.kelompok-row').remove()" class="w-7 h-7 flex items-center justify-center rounded-full bg-red-50 text-red-600 hover:bg-red-100 transition-colors" title="Hapus kelompok">
                <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="opsi-container space-y-1.5 pl-1">
            ${(data && data.opsi) ? data.opsi.map(o => opsiHtml(idx, o)).join('') : ''}
        </div>
        <button type="button" onclick="addOpsiRow(this, ${idx})" class="inline-flex items-center gap-1 px-2 py-1 text-[11px] font-semibold text-amber-700 bg-white border border-amber-200 rounded-lg hover:bg-amber-100 transition-colors">
            <x-heroicon-o-plus class="w-3 h-3" /> Tambah Opsi Menu
        </button>
    `;
    container.appendChild(div);
}

function opsiHtml(kelIdx, o = null) {
    const selId = o ? o.menu_id : '';
    const jumlah = o ? o.jumlah : 1;
    const satuan = o ? (o.satuan_sajian || 'porsi') : 'porsi';
    return `
        <div class="opsi-row grid grid-cols-[1.6fr_80px_110px_auto] gap-2 items-center bg-white border border-gray-200 rounded-lg p-1.5">
            ${menuSatuanHtml(selId, `kelompok[${kelIdx}][opsi][][menu_id]`)}
            <div>
                <div x-data="{ val: String('${jumlah}').replace('.', ','), format(v) { let c = String(v).replace(/[^0-9,]/g, ''); let p = c.split(','); if(p.length > 2) c = p[0] + ',' + p.slice(1).join(''); this.val = c; $refs.hidden.value = c.replace(',', '.'); } }" x-init="format(val)">
                    <input type="text" x-model="val" @input="format($event.target.value)" placeholder="0,00" class="w-full px-2 py-1.5 text-xs border border-gray-200 rounded-lg outline-none text-center font-medium">
                    <input type="hidden" x-ref="hidden" name="kelompok[${kelIdx}][opsi][][jumlah]" value="${jumlah}">
                </div>
            </div>
            <div>
                <select name="kelompok[${kelIdx}][opsi][][satuan_sajian]" class="w-full px-2 py-1.5 text-xs border border-gray-200 rounded-lg outline-none bg-white">${satuanSajianHtml(satuan)}</select>
            </div>
            <button type="button" onclick="this.closest('.opsi-row').remove()" class="w-6 h-6 flex items-center justify-center rounded-full text-red-500 hover:bg-red-50 transition-colors" title="Hapus">
                <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    `;
}

function addOpsiRow(btn, kelIdx) {
    btn.closest('.kelompok-row').querySelector('.opsi-container').insertAdjacentHTML('beforeend', opsiHtml(kelIdx));
}

function openKomposisiForm(paketId) {
    const data = paketKomposisiData[paketId];
    if (!data) return;

    document.getElementById('formKomposisi').action = `${BASE_URL}/menu/${paketId}/komposisi`;
    document.getElementById('komposisiSubtitle').textContent = `${data.nama_menu} (${data.kode_menu})`;

    document.getElementById('tetapContainer').innerHTML = '';
    document.getElementById('kelompokContainer').innerHTML = '';
    tetapIdx = 0;
    kelompokIdx = 0;

    (data.komponen || []).forEach(k => {
        if (k.tipe_item === 'tetap') {
            addTetapRow(k);
        } else {
            addKelompokRow(k);
        }
    });
    if (!(data.komponen || []).some(k => k.tipe_item === 'tetap')) addTetapRow();
    if (!(data.komponen || []).some(k => k.tipe_item === 'pilihan')) addKelompokRow();

    const drawer = document.getElementById('drawerKomposisi');
    const panel = document.getElementById('drawerKomposisiPanel');
    drawer.classList.remove('hidden');
    drawer.style.display = 'flex';
    requestAnimationFrame(() => {
        panel.classList.remove('translate-x-full');
    });
}

function closeKomposisiForm() {
    const drawer = document.getElementById('drawerKomposisi');
    const panel = document.getElementById('drawerKomposisiPanel');
    panel.classList.add('translate-x-full');
    setTimeout(() => {
        drawer.classList.add('hidden');
        drawer.style.display = '';
    }, 300);
}

// Konfirmasi simpan komposisi
document.addEventListener('submit', function (e) {
    if (e.target && e.target.id === 'formKomposisi') {
        const tetapRows = Array.from(document.querySelectorAll('#tetapContainer .tetap-row'));
        const kelRows = Array.from(document.querySelectorAll('#kelompokContainer .kelompok-row'));
        if (tetapRows.length === 0 && kelRows.length === 0) {
            e.preventDefault();
            showToast('error', 'Komposisi paket minimal memiliki satu item.');
            return;
        }
        if (!e.defaultPrevented) {
            e.preventDefault();
            const confirmBtn = e.target.querySelector('button[type="submit"]');
            openConfirmModal('Simpan Komposisi', 'Yakin ingin menyimpan komposisi paket ini?', () => {
                confirmBtn.disabled = true;
                e.target.submit();
            });
        }
    }
});

// ═══ KONFIRMASI MODAL (bukan confirm() bawaan) ═══
let confirmCb = null;
function openConfirmModal(title, message, cb) {
    confirmCb = cb;
    document.getElementById('confirmModalTitle').textContent = title;
    document.getElementById('confirmModalMessage').textContent = message;
    const modal = document.getElementById('confirmModal');
    modal.classList.remove('hidden');
    modal.style.display = 'flex';
}
function closeConfirmModal() {
    const modal = document.getElementById('confirmModal');
    modal.classList.add('hidden');
    modal.style.display = '';
    confirmCb = null;
}
function proceedConfirm() {
    if (confirmCb) confirmCb();
    closeConfirmModal();
}

// ═══ HAPUS RESEP ═══
function openDeleteResepModal(menuId, namaMenu) {
    document.getElementById('formHapusResep').action = `${BASE_URL}/menu/${menuId}/resep`;
    document.getElementById('hapusResepNama').textContent = `"${namaMenu}"`;
    const drawer = document.getElementById('drawerHapusResep');
    const panel = document.getElementById('drawerHapusResepPanel');
    drawer.classList.remove('hidden');
    drawer.style.display = 'flex';
    requestAnimationFrame(() => {
        panel.classList.remove('translate-x-full');
    });
}

function closeDeleteResepModal() {
    const drawer = document.getElementById('drawerHapusResep');
    const panel = document.getElementById('drawerHapusResepPanel');
    panel.classList.add('translate-x-full');
    setTimeout(() => {
        drawer.classList.add('hidden');
        drawer.style.display = '';
    }, 300);
    document.getElementById('formHapusResep').action = '';
}

function showToast(type, message) {
    if (window.showToast) window.showToast(type, message);
    else alert(message);
}

// Konfirmasi hapus menu/paket (bukan confirm() bawaan)
function confirmHapusMenu(event, nama) {
    event.preventDefault();
    const form = event.currentTarget;
    openConfirmModal('Hapus Data', 'Yakin ingin menghapus "' + nama + '"? Data tidak dapat dikembalikan.', () => {
        form.submit();
    });
    return false;
}
</script>
@endpush

{{-- ══════════════════════════════════════════ --}}
{{-- MODAL KONFIRMASI --}}
{{-- ══════════════════════════════════════════ --}}
<div id="confirmModal" class="fixed inset-0 z-[60] hidden items-center justify-center">
    <div class="absolute inset-0 bg-black/30 backdrop-blur-[2px]" onclick="closeConfirmModal()"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl p-6 w-full max-w-sm m-4">
        <div class="w-12 h-12 rounded-full bg-gray-900 text-white flex items-center justify-center mb-4">
            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <h3 class="text-base font-bold text-gray-900" id="confirmModalTitle">Konfirmasi</h3>
        <p class="text-sm text-gray-500 mt-1" id="confirmModalMessage"></p>
        <div class="flex gap-2 mt-5">
            <button type="button" onclick="closeConfirmModal()" class="flex-1 px-4 py-2 text-sm font-semibold text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">Batal</button>
            <button type="button" onclick="proceedConfirm()" class="flex-1 px-4 py-2 text-sm font-semibold text-white bg-gray-900 rounded-lg hover:bg-gray-800 transition-colors">Ya, Lanjut</button>
        </div>
    </div>
</div>
@endsection
