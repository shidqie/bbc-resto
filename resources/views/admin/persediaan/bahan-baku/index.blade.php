{{-- 
    Halaman: Daftar Bahan Baku
    UI: disamakan dengan Kelola Menu
--}}
@extends('layouts.pos')

@section('title', 'Data Bahan Baku')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800">
    <div class="w-full p-6 space-y-5">
        {{-- Page Header --}}
        <x-ui.page-header
            title="Data Bahan Baku"
            subtitle="Kelola persediaan bahan baku. Stok otomatis terpotong dari resep & bertambah dari pengadaan."
            :breadcrumbs="['Persediaan', 'Bahan Baku']">
            <x-slot:actions>
                @if($tab == 'semua')
                <x-ui.button variant="primary" icon="plus" onclick="openBahanBakuModal()">
                    Tambah Bahan
                </x-ui.button>
                @elseif($tab == 'kategori')
                <x-ui.button variant="primary" icon="plus" onclick="openKategoriModal()">
                    Tambah Kategori
                </x-ui.button>
                @elseif($tab == 'satuan')
                <x-ui.button variant="primary" icon="plus" onclick="openSatuanModal()">
                    Tambah Satuan
                </x-ui.button>
                @endif
            </x-slot:actions>
        </x-ui.page-header>

        {{-- Stat Cards (Only show in semua tab) --}}
        @if($tab == 'semua')
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-ui.stat-card label="Total Bahan Baku" :value="$totalBahan ?? 0" icon="cube" color="blue" />
            <x-ui.stat-card label="Total Kategori" :value="$totalKategori ?? 0" icon="tag" color="purple" />
            <x-ui.stat-card label="Bahan Aktif" :value="$bahanAktif ?? 0" icon="check-circle" color="emerald" />
        </div>
        @endif

        <x-ui.alert />

        {{-- TABS --}}
        <x-ui.tab-list>
            <x-ui.tab href="{{ route('bahan-baku.index', ['tab' => 'semua']) }}" :active="$tab == 'semua'">
                Semua Bahan Baku
            </x-ui.tab>
            <x-ui.tab href="{{ route('bahan-baku.index', ['tab' => 'kategori']) }}" :active="$tab == 'kategori'">
                Kategori Bahan
            </x-ui.tab>
            <x-ui.tab href="{{ route('bahan-baku.index', ['tab' => 'satuan']) }}" :active="$tab == 'satuan'">
                Satuan
            </x-ui.tab>
        </x-ui.tab-list>

        {{-- Filter Bar --}}
        @if($tab == 'semua')
        <div class="flex flex-col sm:flex-row gap-2 items-start sm:items-center justify-between mb-3 shrink-0">
            <form action="{{ route('bahan-baku.index') }}" method="GET" class="flex items-center gap-2 w-full sm:w-auto flex-wrap">
                <input type="hidden" name="tab" value="semua">
                <x-search-input name="search" value="{{ request('search') }}" placeholder="Cari kode atau nama bahan..." />
                <x-select-input name="kategori" :options="$kategoris->pluck('nama_kategori', 'id')->toArray()" :selected="request('kategori')" placeholder="Semua Kategori" :auto-submit="true" />
                <x-select-input name="status_stok" :options="['aman' => 'Aman', 'menipis' => 'Menipis', 'habis' => 'Habis']" :selected="request('status_stok')" placeholder="Semua Status" :auto-submit="true" />
                <button type="submit" class="text-sm font-medium bg-white border border-gray-200 text-gray-600 rounded-lg px-3 py-2 hover:bg-gray-50 transition-colors shrink-0">Cari</button>
            </form>
        </div>
        @endif

        {{-- TAB CONTENT --}}
        @if($tab == 'semua')
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-sm font-semibold text-gray-500 uppercase tracking-wide">
                            <th class="px-4 py-3 text-left w-12">No</th>
                            <th class="px-4 py-3 text-left w-28">Kode Bahan</th>
                            <th class="px-4 py-3 text-left">Nama Bahan</th>
                            <th class="px-4 py-3 text-left">Kategori</th>
                            <th class="px-4 py-3 text-left">Satuan</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($bahanBakus as $i => $item)
                        <tr class="hover:bg-gray-50/60 transition-colors group {{ !$item->status_aktif ? 'opacity-60' : '' }}">
                            <td class="px-4 py-3 text-sm text-gray-500 font-medium align-middle">{{ $bahanBakus->firstItem() + $i }}</td>
                            <td class="px-4 py-3 align-middle">
                                <span class="inline-block text-xs font-mono font-semibold text-gray-700 bg-gray-100 rounded-xl px-2 py-1">{{ $item->id_bahan_baku }}</span>
                            </td>
                            <td class="px-4 py-3 align-middle">
                                <p class="font-semibold text-gray-900 leading-tight">{{ $item->nama_bahan }}</p>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 font-medium">{{ $item->kategori_bahan_baku->nama_kategori ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 font-medium">{{ $item->satuan->nama_satuan ?? '-' }}</td>
                            <td class="px-4 py-3">
                                @if($item->status_aktif)
                                    <span class="inline-block text-xs font-semibold px-2 py-0.5 rounded-xl bg-emerald-50 text-emerald-700">Aktif</span>
                                @else
                                    <span class="inline-block text-xs font-semibold px-2 py-0.5 rounded-xl bg-gray-100 text-gray-700">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <x-ui.action-button onclick="openDetailDrawer({{ $item->id }})" title="Detail">
                                        <x-heroicon-o-eye class="w-4 h-4" />
                                    </x-ui.action-button>
                                    <x-ui.action-button onclick="openBahanBakuModal({{ $item->id }})" title="Ubah">
                                        <x-heroicon-o-pencil-square class="w-4 h-4" />
                                    </x-ui.action-button>
                                    <form id="delete-bahan-{{ $item->id }}" action="{{ route('bahan-baku.destroy', $item->id) }}" method="POST" class="inline">
                                        @csrf @method('DELETE')
                                        <x-ui.action-button type="button" title="Hapus" onclick="window.confirmDialog({ title: 'Hapus Bahan Baku', name: '{{ addslashes($item->nama_bahan) }}', message: 'Data yang dihapus tidak dapat dikembalikan.', formId: 'delete-bahan-{{ $item->id }}', confirmText: 'Hapus', cancelText: 'Batal' })">
                                            <x-heroicon-o-trash class="w-4 h-4" />
                                        </x-ui.action-button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7">
                                <x-ui.empty-state icon="cube" title="Belum ada data bahan baku" message="Belum ada data bahan baku." />
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $bahanBakus->links() }}</div>
            
        @elseif($tab == 'kategori')
            <div class="flex items-center justify-start mb-4">
                <x-ui.button variant="primary" icon="plus" onclick="openKategoriModal()">
                    Tambah Kategori
                </x-ui.button>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-sm font-semibold text-gray-500 uppercase tracking-wide">
                            <th class="px-4 py-3 text-left w-12">No</th>
                            <th class="px-4 py-3 text-left">Nama Kategori</th>
                            <th class="px-4 py-3 text-right w-32">Jumlah Bahan</th>
                            <th class="px-4 py-3 text-center w-24">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($kategorisPage as $i => $kat)
                        <tr class="hover:bg-gray-50/60 transition-colors group">
                            <td class="px-4 py-3 text-sm text-gray-500 font-medium">{{ $kategorisPage->firstItem() + $i }}</td>
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $kat->nama_kategori }}</td>
                            <td class="px-4 py-3 text-right font-medium">{{ $kat->bahan_bakus_count }}</td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <x-ui.action-button onclick="openKategoriModal({{ $kat->id }}, '{{ addslashes($kat->nama_kategori) }}')" title="Ubah">
                                        <x-heroicon-o-pencil-square class="w-4 h-4" />
                                    </x-ui.action-button>
                                    <form id="delete-kategori-{{ $kat->id }}" action="{{ route('kategori-bahan.destroy', $kat->id) }}" method="POST" class="inline">
                                        @csrf @method('DELETE')
                                        <x-ui.action-button type="button" title="Hapus" onclick="window.confirmDialog({ title: 'Hapus Kategori', name: '{{ addslashes($kat->nama_kategori) }}', message: 'Kategori ini akan dihapus dari sistem.', formId: 'delete-kategori-{{ $kat->id }}' })">
                                            <x-heroicon-o-trash class="w-4 h-4" />
                                        </x-ui.action-button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4">
                                <x-ui.empty-state icon="tag" title="Belum ada kategori bahan baku" message="Belum ada kategori bahan baku." />
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $kategorisPage->links() }}</div>
            
        @elseif($tab == 'satuan')
            <div class="flex items-center justify-start mb-4">
                <x-ui.button variant="primary" icon="plus" onclick="openSatuanModal()">
                    Tambah Satuan
                </x-ui.button>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-sm font-semibold text-gray-500 uppercase tracking-wide">
                            <th class="px-4 py-3 text-left w-12">No</th>
                            <th class="px-4 py-3 text-left">Nama Satuan</th>
                            <th class="px-4 py-3 text-left">Simbol</th>
                            <th class="px-4 py-3 text-right w-32">Digunakan Oleh</th>
                            <th class="px-4 py-3 text-center w-24">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($satuansPage as $i => $sat)
                        <tr class="hover:bg-gray-50/60 transition-colors group">
                            <td class="px-4 py-3 text-sm text-gray-500 font-medium">{{ $satuansPage->firstItem() + $i }}</td>
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $sat->nama_satuan }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $sat->singkatan ?? '-' }}</td>
                            <td class="px-4 py-3 text-right font-medium">{{ $sat->bahan_bakus_count }}</td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <x-ui.action-button onclick="openSatuanModal({{ $sat->id }}, '{{ addslashes($sat->nama_satuan) }}', '{{ addslashes($sat->singkatan ?? '') }}')" title="Ubah">
                                        <x-heroicon-o-pencil-square class="w-4 h-4" />
                                    </x-ui.action-button>
                                    <form id="delete-satuan-{{ $sat->id }}" action="{{ route('satuan.destroy', $sat->id) }}" method="POST" class="inline">
                                        @csrf @method('DELETE')
                                        <x-ui.action-button type="button" title="Hapus" onclick="window.confirmDialog({ title: 'Hapus Satuan', name: '{{ addslashes($sat->nama_satuan) }}', message: 'Satuan ini akan dihapus dari sistem.', formId: 'delete-satuan-{{ $sat->id }}' })">
                                            <x-heroicon-o-trash class="w-4 h-4" />
                                        </x-ui.action-button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5">
                                <x-ui.empty-state icon="scale" title="Belum ada data satuan" message="Belum ada data satuan." />
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $satuansPage->links() }}</div>
        @endif

    </div>
</div>

{{-- DATA JSON UNTUK MODAL BAHAN BAKU --}}
<script>
    const bahanBakusData = @json($bahanBakus ? $bahanBakus->items() : []);
    const BASE_URL = "{{ url('') }}";
</script>

{{-- ══════════════════════════════════════════ --}}
{{-- MODAL: TAMBAH/EDIT BAHAN BAKU (SLIDE-IN RIGHT) --}}
{{-- ══════════════════════════════════════════ --}}
<div id="drawerBahanBaku" class="fixed inset-x-0 bottom-0 top-16 z-40 hidden">
    <div class="absolute inset-0 bg-black/30 backdrop-blur-sm opacity-0 transition-opacity duration-300" id="drawerBahanBakuOverlay" onclick="closeBahanBakuModal()"></div>
    <div class="absolute right-0 top-0 h-full w-full max-w-md bg-white shadow-2xl flex flex-col translate-x-full transition-transform duration-300" id="drawerBahanBakuPanel">
        
        <div class="flex items-center justify-between px-5 pt-4 pb-4 border-b border-gray-100 shrink-0">
            <div>
                <h2 class="font-semibold text-gray-900" id="bbModalTitle">Tambah Bahan Baku</h2>
            </div>
            <button onclick="closeBahanBakuModal()" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form id="formBahanBaku" action="{{ route('bahan-baku.store') }}" method="POST" class="flex-1 overflow-y-auto flex flex-col">
            @csrf
            <div id="formBahanBakuMethod"></div>
            
            <div class="px-5 py-5 space-y-4 flex-1">
                <div>
                    <x-ui.input name="id_bahan_baku" id="bbKode" label="Kode Bahan" placeholder="Otomatis (BB-XXXX)" readonly class="bg-gray-50 text-gray-500" />
                </div>

                <div>
                    <x-ui.input name="nama_bahan" id="bbNama" label="Nama Bahan *" required placeholder="Contoh: Daging Sapi" />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Kategori <span class="text-red-500">*</span></label>
                        @php
                            $katOptionsBB = [];
                            foreach($kategoris as $kat) {
                                $katOptionsBB[$kat->id] = $kat->nama_kategori;
                            }
                        @endphp
                        <x-ui.searchable-select id="bbKategori" name="kategori_bahan_baku_id" :options="$katOptionsBB" placeholder="— Pilih Kategori —" required="true" />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Satuan <span class="text-red-500">*</span></label>
                        @php
                            $satOptionsBB = [];
                            foreach($satuans as $sat) {
                                $satOptionsBB[$sat->id] = $sat->singkatan ?? $sat->nama_satuan;
                            }
                        @endphp
                        <x-ui.searchable-select id="bbSatuan" name="satuan_id" :options="$satOptionsBB" placeholder="— Pilih Satuan —" required="true" />
                    </div>
                </div>

                <div id="bbStokAwalContainer">
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Stok Awal</label>
                    <x-ui.input-decimal id="bbStok" name="stok" value="0" />
                    <p class="text-xs text-gray-400 mt-1">Hanya diisi saat membuat bahan baku baru.</p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Stok Min (Harian) <span class="text-red-500">*</span></label>
                        <x-ui.input-decimal id="bbMinHarian" name="stok_minimal_harian" required="true" />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Stok Min (Catering)</label>
                        <x-ui.input-decimal id="bbMinCatering" name="stok_minimal_catering" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Peruntukan <span class="text-red-500">*</span></label>
                        <x-ui.searchable-select id="bbPeruntukan" name="jenis_peruntukan" :options="['Semua' => 'Semua', 'Reguler' => 'Reguler / Dine In', 'Catering' => 'Catering']" placeholder="— Pilih Peruntukan —" required="true" />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Status <span class="text-red-500">*</span></label>
                        <x-ui.searchable-select id="bbStatus" name="status_aktif" :options="['1' => 'Aktif', '0' => 'Nonaktif']" placeholder="— Pilih Status —" required="true" />
                    </div>
                </div>
            </div>

            <div class="px-5 py-4 border-t border-gray-100 flex justify-end gap-2 bg-gray-50/80 shrink-0">
                <x-ui.button type="button" variant="secondary" onclick="closeBahanBakuModal()">Batal</x-ui.button>
                <x-ui.button type="submit" variant="primary">Simpan</x-ui.button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════ --}}
{{-- MODAL: TAMBAH/EDIT KATEGORI BAHAN --}}
{{-- ══════════════════════════════════════════ --}}
<div id="drawerKategori" class="fixed inset-x-0 bottom-0 top-16 z-40 hidden">
    <div class="absolute inset-0 bg-black/30 backdrop-blur-sm opacity-0 transition-opacity duration-300" id="drawerKategoriOverlay" onclick="closeKategoriModal()"></div>
    <div class="absolute right-0 top-0 h-full w-full max-w-sm bg-white shadow-2xl flex flex-col translate-x-full transition-transform duration-300" id="drawerKategoriPanel">
        <div class="flex items-center justify-between px-5 pt-4 pb-4 border-b border-gray-100 shrink-0">
            <h2 class="font-semibold text-gray-900" id="kategoriModalTitle">Tambah Kategori</h2>
            <button onclick="closeKategoriModal()" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="formKategori" action="{{ route('kategori-bahan.store') }}" method="POST" class="flex-1 overflow-y-auto flex flex-col">
            @csrf
            <div id="formKategoriMethod"></div>
            <div class="px-5 py-5 space-y-4 flex-1">
                <div>
                    <x-ui.input name="nama_kategori" id="katNama" label="Nama Kategori *" required placeholder="Contoh: Daging & Unggas" />
                </div>
            </div>
            <div class="px-5 py-4 border-t border-gray-100 flex justify-end gap-2 bg-gray-50/80 shrink-0">
                <x-ui.button type="button" variant="secondary" onclick="closeKategoriModal()">Batal</x-ui.button>
                <x-ui.button type="submit" variant="primary">Simpan</x-ui.button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════ --}}
{{-- MODAL: TAMBAH/EDIT SATUAN --}}
{{-- ══════════════════════════════════════════ --}}
<div id="drawerSatuan" class="fixed inset-x-0 bottom-0 top-16 z-40 hidden">
    <div class="absolute inset-0 bg-black/30 backdrop-blur-sm opacity-0 transition-opacity duration-300" id="drawerSatuanOverlay" onclick="closeSatuanModal()"></div>
    <div class="absolute right-0 top-0 h-full w-full max-w-sm bg-white shadow-2xl flex flex-col translate-x-full transition-transform duration-300" id="drawerSatuanPanel">
        <div class="flex items-center justify-between px-5 pt-4 pb-4 border-b border-gray-100 shrink-0">
            <h2 class="font-semibold text-gray-900" id="satuanModalTitle">Tambah Satuan</h2>
            <button onclick="closeSatuanModal()" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="formSatuan" action="{{ route('satuan.store') }}" method="POST" class="flex-1 overflow-y-auto flex flex-col">
            @csrf
            <div id="formSatuanMethod"></div>
            <div class="px-5 py-5 space-y-4 flex-1">
                <div>
                    <x-ui.input name="nama_satuan" id="satNama" label="Nama Satuan *" required placeholder="Contoh: Kilogram" />
                </div>
                <div>
                    <x-ui.input name="singkatan" id="satSingkat" label="Singkatan" placeholder="Contoh: kg" />
                </div>
            </div>
            <div class="px-5 py-4 border-t border-gray-100 flex justify-end gap-2 bg-gray-50/80 shrink-0">
                <x-ui.button type="button" variant="secondary" onclick="closeSatuanModal()">Batal</x-ui.button>
                <x-ui.button type="submit" variant="primary">Simpan</x-ui.button>
            </div>
        </form>
    </div>
</div>

<!-- Drawer Detail Bahan Baku Wrapper -->
<div id="drawerDetail" class="fixed inset-0 z-[100] hidden">
    <!-- Backdrop -->
    <div id="drawerDetailOverlay" class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm opacity-0 transition-opacity duration-300" onclick="closeDetailDrawer()"></div>
    
    <!-- Drawer Panel -->
    <div id="drawerDetailPanel" class="absolute right-0 top-0 h-full w-full max-w-md bg-white shadow-2xl flex flex-col translate-x-full transition-transform duration-300">
        <div id="drawerDetailContent" class="flex-1 overflow-y-auto">
            <!-- Content will be loaded here via AJAX -->
            <div class="flex items-center justify-center h-full">
                <svg class="animate-spin h-8 w-8 text-accent" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<script>
    // --- BAHAN BAKU ---
    function openBahanBakuModal(id = null) {
        const drawer = document.getElementById('drawerBahanBaku');
        const overlay = document.getElementById('drawerBahanBakuOverlay');
        const panel = document.getElementById('drawerBahanBakuPanel');
        
        let bb = null;
        if (id) {
            bb = bahanBakusData.find(b => b.id == id);
        }

        if (bb) {
            document.getElementById('bbModalTitle').textContent = 'Edit Bahan Baku';
            document.getElementById('formBahanBaku').action = `${BASE_URL}/bahan-baku/${bb.id}`;
            document.getElementById('formBahanBakuMethod').innerHTML = '<input type="hidden" name="_method" value="PUT">';
            
            document.getElementById('bbKode').value = bb.id_bahan_baku || '';
            document.getElementById('bbNama').value = bb.nama_bahan || '';
            
            const syncValues = () => {
                window.dispatchEvent(new CustomEvent('value-updated', { detail: { id: 'bbKategori', value: bb.kategori_bahan_baku_id } }));
                window.dispatchEvent(new CustomEvent('value-updated', { detail: { id: 'bbSatuan', value: bb.satuan_id } }));
                window.dispatchEvent(new CustomEvent('value-updated', { detail: { id: 'bbPeruntukan', value: bb.jenis_peruntukan || 'Semua' } }));
                window.dispatchEvent(new CustomEvent('value-updated', { detail: { id: 'bbStatus', value: (bb.status_aktif == 1 || bb.status_aktif === true) ? '1' : '0' } }));
                
                const stokHarian = bb.stok_harian ? (bb.stok_harian.stok_minimal || bb.stok_minimal || 0) : (bb.stok_minimal || 0);
                window.dispatchEvent(new CustomEvent('value-updated', { detail: { id: 'bbMinHarian', value: stokHarian } }));
                const stokCatering = bb.stok_catering_balance ? (bb.stok_catering_balance.stok_minimal || 0) : 0;
                window.dispatchEvent(new CustomEvent('value-updated', { detail: { id: 'bbMinCatering', value: stokCatering } }));
            };

            syncValues();
            requestAnimationFrame(syncValues);
            setTimeout(syncValues, 50);

            document.getElementById('bbStokAwalContainer').classList.add('hidden');
        } else {
            document.getElementById('bbModalTitle').textContent = 'Tambah Bahan Baku';
            document.getElementById('formBahanBaku').action = `${BASE_URL}/bahan-baku`;
            document.getElementById('formBahanBakuMethod').innerHTML = '';
            
            document.getElementById('formBahanBaku').reset();
            document.getElementById('bbKode').value = '';
            
            const resetValues = () => {
                window.dispatchEvent(new CustomEvent('value-updated', { detail: { id: 'bbKategori', value: '' } }));
                window.dispatchEvent(new CustomEvent('value-updated', { detail: { id: 'bbSatuan', value: '' } }));
                window.dispatchEvent(new CustomEvent('value-updated', { detail: { id: 'bbPeruntukan', value: 'Semua' } }));
                window.dispatchEvent(new CustomEvent('value-updated', { detail: { id: 'bbStatus', value: '1' } }));
                window.dispatchEvent(new CustomEvent('value-updated', { detail: { id: 'bbMinHarian', value: 0 } }));
                window.dispatchEvent(new CustomEvent('value-updated', { detail: { id: 'bbMinCatering', value: 0 } }));
                window.dispatchEvent(new CustomEvent('value-updated', { detail: { id: 'bbStok', value: 0 } }));
            };
            resetValues();
            requestAnimationFrame(resetValues);

            document.getElementById('bbStokAwalContainer').classList.remove('hidden');
        }

        drawer.classList.remove('hidden');
        drawer.style.display = 'flex';
        
        requestAnimationFrame(() => {
            panel.classList.remove('translate-x-full');
            panel.classList.add('translate-x-0');
            overlay.classList.remove('opacity-0');
            overlay.classList.add('opacity-100');
        });
    }

    function closeBahanBakuModal() {
        const drawer = document.getElementById('drawerBahanBaku');
        const overlay = document.getElementById('drawerBahanBakuOverlay');
        const panel = document.getElementById('drawerBahanBakuPanel');

        panel.classList.remove('translate-x-0');
        panel.classList.add('translate-x-full');
        overlay.classList.remove('opacity-100');
        overlay.classList.add('opacity-0');

        setTimeout(() => {
            drawer.classList.add('hidden');
            drawer.style.display = '';
        }, 300);
    }

    // --- KATEGORI ---
    function openKategoriModal(id = null, nama = '') {
        const drawer = document.getElementById('drawerKategori');
        const overlay = document.getElementById('drawerKategoriOverlay');
        const panel = document.getElementById('drawerKategoriPanel');
        
        if (id) {
            document.getElementById('kategoriModalTitle').textContent = 'Edit Kategori';
            document.getElementById('formKategori').action = `${BASE_URL}/kategori-bahan/${id}`;
            document.getElementById('formKategoriMethod').innerHTML = '<input type="hidden" name="_method" value="PUT">';
            document.getElementById('katNama').value = nama;
        } else {
            document.getElementById('kategoriModalTitle').textContent = 'Tambah Kategori';
            document.getElementById('formKategori').action = `${BASE_URL}/kategori-bahan`;
            document.getElementById('formKategoriMethod').innerHTML = '';
            document.getElementById('katNama').value = '';
        }

        drawer.classList.remove('hidden');
        drawer.style.display = 'flex';
        
        requestAnimationFrame(() => {
            panel.classList.remove('translate-x-full');
            panel.classList.add('translate-x-0');
            overlay.classList.remove('opacity-0');
            overlay.classList.add('opacity-100');
        });
    }

    function closeKategoriModal() {
        const drawer = document.getElementById('drawerKategori');
        const overlay = document.getElementById('drawerKategoriOverlay');
        const panel = document.getElementById('drawerKategoriPanel');

        panel.classList.remove('translate-x-0');
        panel.classList.add('translate-x-full');
        overlay.classList.remove('opacity-100');
        overlay.classList.add('opacity-0');

        setTimeout(() => {
            drawer.classList.add('hidden');
            drawer.style.display = '';
        }, 300);
    }

    // --- SATUAN ---
    function openSatuanModal(id = null, nama = '', singkatan = '') {
        const drawer = document.getElementById('drawerSatuan');
        const overlay = document.getElementById('drawerSatuanOverlay');
        const panel = document.getElementById('drawerSatuanPanel');
        
        if (id) {
            document.getElementById('satuanModalTitle').textContent = 'Edit Satuan';
            document.getElementById('formSatuan').action = `${BASE_URL}/satuan/${id}`;
            document.getElementById('formSatuanMethod').innerHTML = '<input type="hidden" name="_method" value="PUT">';
            document.getElementById('satNama').value = nama;
            document.getElementById('satSingkat').value = singkatan;
        } else {
            document.getElementById('satuanModalTitle').textContent = 'Tambah Satuan';
            document.getElementById('formSatuan').action = `${BASE_URL}/satuan`;
            document.getElementById('formSatuanMethod').innerHTML = '';
            document.getElementById('satNama').value = '';
            document.getElementById('satSingkat').value = '';
        }

        drawer.classList.remove('hidden');
        drawer.style.display = 'flex';
        
        requestAnimationFrame(() => {
            panel.classList.remove('translate-x-full');
            panel.classList.add('translate-x-0');
            overlay.classList.remove('opacity-0');
            overlay.classList.add('opacity-100');
        });
    }

    function closeSatuanModal() {
        const drawer = document.getElementById('drawerSatuan');
        const overlay = document.getElementById('drawerSatuanOverlay');
        const panel = document.getElementById('drawerSatuanPanel');

        panel.classList.remove('translate-x-0');
        panel.classList.add('translate-x-full');
        overlay.classList.remove('opacity-100');
        overlay.classList.add('opacity-0');

        setTimeout(() => {
            drawer.classList.add('hidden');
            drawer.style.display = '';
        }, 300);
    }
    // --- DETAIL DRAWER ---
    function openDetailDrawer(id) {
        const drawer = document.getElementById('drawerDetail');
        const overlay = document.getElementById('drawerDetailOverlay');
        const panel = document.getElementById('drawerDetailPanel');
        const content = document.getElementById('drawerDetailContent');

        // Show loading state
        content.innerHTML = `
            <div class="flex items-center justify-center h-full">
                <svg class="animate-spin h-8 w-8 text-accent" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        `;

        drawer.classList.remove('hidden');
        drawer.style.display = 'block';
        
        requestAnimationFrame(() => {
            panel.classList.remove('translate-x-full');
            panel.classList.add('translate-x-0');
            overlay.classList.remove('opacity-0');
            overlay.classList.add('opacity-100');
        });

        // Fetch content
        fetch(`${BASE_URL}/bahan-baku/${id}/drawer`)
            .then(res => res.text())
            .then(html => {
                content.innerHTML = html;
            })
            .catch(err => {
                content.innerHTML = '<div class="p-6 text-center text-red-500">Gagal memuat data.</div>';
            });
    }

    function closeDetailDrawer() {
        const drawer = document.getElementById('drawerDetail');
        const overlay = document.getElementById('drawerDetailOverlay');
        const panel = document.getElementById('drawerDetailPanel');

        panel.classList.remove('translate-x-0');
        panel.classList.add('translate-x-full');
        overlay.classList.remove('opacity-100');
        overlay.classList.add('opacity-0');

        setTimeout(() => {
            drawer.classList.add('hidden');
            drawer.style.display = '';
        }, 300);
    }
</script>
@endsection
