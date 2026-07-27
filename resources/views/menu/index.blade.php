{{-- 
    Halaman: Mengelola Data Menu (Resto, Catering, Nasi Box) + Integrated BOM Breakdown
    Skenario: Skenario Utama, Skenario A1 (Kategori), Skenario A2 (Nonaktifkan), Skenario A3 (Simpan Tanpa BOM Warning)
--}}
@extends('layouts.pos')

@section('content')
<div class="flex-1 overflow-auto bg-gray-50/50 text-gray-800 font-sans">
    <div class="p-6 md:p-8 w-full space-y-6">
        
        {{-- Header --}}
        <x-ui.page-header 
            title="Daftar Menu Resto & Paket" 
            subtitle="Kelola data menu resto, catering, nasi box beserta komposisi bahan baku (BOM)"
            :breadcrumbs="['Menu', 'Daftar Menu Resto']">
            <x-slot:actions>
                <button onclick="openModalTambah()" class="inline-flex items-center gap-2 bg-[#0F2E23] hover:bg-[#0a1f17] text-white px-5 py-2.5 rounded-xl font-bold text-sm transition-all shadow-sm active:scale-95">
                    <x-heroicon-o-plus class="w-5 h-5 inline-block shrink-0" /> Tambah Menu Baru
                </button>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.alert />

        {{-- Skenario A3 Warning Banner --}}
        @if(session('warning_bom'))
        <div class="bg-amber-50 border-2 border-amber-300 rounded-2xl p-4 flex items-start gap-3 shadow-xs animate-fade-in">
            <div class="w-9 h-9 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0">
                <x-heroicon-o-exclamation-triangle class="w-5 h-5" />
            </div>
            <div class="flex-1">
                <h4 class="text-sm font-extrabold text-amber-950">Peringatan Kebutuhan Bahan Baku (Skenario A3)</h4>
                <p class="text-xs text-amber-900 mt-0.5 leading-relaxed font-medium">
                    {{ session('warning_bom') }}
                </p>
            </div>
            <button onclick="this.parentElement.remove()" class="text-amber-700 hover:text-amber-900 font-bold text-lg leading-none">&times;</button>
        </div>
        @endif

        {{-- Table Container --}}
        <x-ui.data-table :paginator="$menus">
            <x-slot:toolbar>
                <div class="flex flex-col md:flex-row gap-3 w-full justify-between items-center">
                    {{-- Search --}}
                    <div class="relative w-full md:w-80">
                        <x-heroicon-o-magnifying-glass class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 w-4 h-4 inline-block shrink-0" />
                        <form action="{{ route('menu.index') }}" method="GET">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / kode menu…" class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#0F2E23]/10 focus:border-[#0F2E23] outline-none bg-white transition-all">
                        </form>
                    </div>

                    {{-- Filter Tabs (Resto / Catering / Nasi Box) --}}
                    <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar">
                        <a href="{{ route('menu.index') }}" 
                           class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ !request('jenis_menu') ? 'bg-[#0F2E23] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                           Semua ({{ $stats['total'] }})
                        </a>
                        <a href="{{ route('menu.index', ['jenis_menu' => 'dine_in']) }}" 
                           class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ request('jenis_menu') === 'dine_in' ? 'bg-emerald-800 text-white' : 'bg-emerald-50 text-emerald-900 border border-emerald-200' }}">
                           Resto ({{ $stats['dine_in'] }})
                        </a>
                        <a href="{{ route('menu.index', ['jenis_menu' => 'catering']) }}" 
                           class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ request('jenis_menu') === 'catering' ? 'bg-blue-800 text-white' : 'bg-blue-50 text-blue-900 border border-blue-200' }}">
                           Catering ({{ $stats['catering'] }})
                        </a>
                        <a href="{{ route('menu.index', ['jenis_menu' => 'nasi_box']) }}" 
                           class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ request('jenis_menu') === 'nasi_box' ? 'bg-purple-800 text-white' : 'bg-purple-50 text-purple-900 border border-purple-200' }}">
                           Nasi Box ({{ $stats['nasi_box'] }})
                        </a>
                    </div>
                </div>
            </x-slot:toolbar>

            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                        <th class="px-5 py-4 font-bold">Kode Menu</th>
                        <th class="px-5 py-4 font-bold">Nama Menu</th>
                        <th class="px-5 py-4 font-bold">Kategori</th>
                        <th class="px-5 py-4 font-bold">Jenis</th>
                        <th class="px-5 py-4 font-bold">Harga</th>
                        <th class="px-5 py-4 font-bold">Komposisi BOM</th>
                        <th class="px-5 py-4 font-bold">Status</th>
                        <th class="px-5 py-4 font-bold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @forelse($menus as $menu)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-5 py-4 font-mono font-bold text-[#0F2E23]">
                                {{ $menu->kode_menu ?? ('MNU-' . str_pad($menu->id, 2, '0', STR_PAD_LEFT)) }}
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    @if($menu->foto)
                                        <img src="{{ Storage::url($menu->foto) }}" class="w-10 h-10 rounded-xl object-cover border border-gray-200" alt="{{ $menu->nama }}">
                                    @else
                                        <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center text-gray-400">
                                            <x-heroicon-o-photo class="w-5 h-5" />
                                        </div>
                                    @endif
                                    <div>
                                        <p class="font-bold text-gray-900">{{ $menu->nama }}</p>
                                        @if($menu->deskripsi)
                                            <p class="text-xs text-gray-400 line-clamp-1 max-w-[200px]">{{ $menu->deskripsi }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 font-semibold text-gray-700">
                                {{ $menu->kategori->nama ?? '-' }}
                            </td>
                            <td class="px-5 py-4">
                                @if($menu->jenis_menu === 'catering')
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-200">Catering</span>
                                @elseif($menu->jenis_menu === 'nasi_box')
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-100 text-purple-800 border border-purple-200">Nasi Box</span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">Resto</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 font-extrabold text-[#0F2E23]">
                                Rp {{ number_format($menu->harga, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-4">
                                @if($menu->resep->isNotEmpty())
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-900 border border-emerald-200" title="{{ $menu->resep->map(fn($r) => $r->bahanBaku->nama_bahan . ' (' . $r->jumlah_kebutuhan . ' ' . ($r->bahanBaku->satuan->nama_satuan ?? '') . ')')->join(', ') }}">
                                        <x-heroicon-o-beaker class="w-3.5 h-3.5 text-emerald-700" />
                                        {{ $menu->resep->count() }} Bahan Baku
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-xs font-bold bg-amber-50 text-amber-900 border border-amber-300" title="Skenario A3: Belum memiliki BOM">
                                        <x-heroicon-o-exclamation-triangle class="w-3.5 h-3.5 text-amber-600" />
                                        Tanpa BOM
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                @if($menu->status === 'nonaktif')
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-200 text-gray-700 border border-gray-300">Nonaktif</span>
                                @elseif($menu->isHabis())
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800 border border-red-200">Stok Habis</span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-900 border border-emerald-200">Aktif</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right space-x-1.5">
                                {{-- Skenario A2: Toggle Status (Nonaktifkan / Aktifkan) --}}
                                <form action="{{ route('menu.toggle', $menu->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Status menu {{ addslashes($menu->nama) }} akan diubah ke {{ $menu->status === 'nonaktif' ? 'Aktif' : 'Nonaktif' }}. Lanjutkan?');">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="px-3 py-1.5 text-xs font-bold {{ $menu->status === 'nonaktif' ? 'text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200' : 'text-amber-800 bg-amber-50 hover:bg-amber-100 border border-amber-200' }} rounded-xl transition-colors inline-flex items-center gap-1" title="Skenario A2: Menonaktifkan Menu">
                                        <x-heroicon-o-power class="w-3.5 h-3.5" />
                                        {{ $menu->status === 'nonaktif' ? 'Aktifkan' : 'Nonaktifkan' }}
                                    </button>
                                </form>

                                {{-- Edit Button --}}
                                <button onclick="editMenu({{ json_encode($menu) }}, {{ json_encode($menu->resep) }})" 
                                        class="px-3 py-1.5 text-xs font-bold text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-xl border border-blue-200 transition-colors inline-flex items-center gap-1">
                                    <x-heroicon-o-pencil-square class="w-3.5 h-3.5" /> Edit
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                <x-ui.empty-state icon="fa-book-open" title="Belum ada data menu." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-ui.data-table>

    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- MODAL INTEGRATED: TAMBAH MENU + BREAKDOWN BOM --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
<div id="modalMenu" class="fixed inset-0 z-[100] hidden overflow-y-auto">
    <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="closeModalMenu()"></div>
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-3xl p-6 sm:p-8 border border-gray-100 space-y-6">
            
            <div class="flex items-center justify-between border-b pb-4">
                <div>
                    <h3 class="text-lg font-extrabold text-[#0F2E23]" id="modalTitle">Tambah Menu Baru</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Input informasi menu beserta komposisi resep bahan baku (BOM)</p>
                </div>
                <button onclick="closeModalMenu()" class="text-gray-400 hover:text-gray-600 font-bold text-2xl leading-none">&times;</button>
            </div>

            <form id="formMenu" action="{{ route('menu.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <div id="methodContainer"></div>

                {{-- SECTION 1: INFORMASI UTAMA MENU --}}
                <div class="space-y-4">
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider">1. Informasi Utama Menu</h4>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Nama Menu --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Nama Menu <span class="text-red-500">*</span></label>
                            <input type="text" name="nama" id="inputNama" required placeholder="Contoh: Ayam Bakar Spesial" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0F2E23]/10 focus:border-[#0F2E23] outline-none text-sm transition-all">
                        </div>

                        {{-- Jenis Menu --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Jenis Menu <span class="text-red-500">*</span></label>
                            <select name="jenis_menu" id="inputJenisMenu" required onchange="filterKategoriOptions(this.value)" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0F2E23]/10 focus:border-[#0F2E23] outline-none text-sm transition-all font-semibold">
                                <option value="dine_in">Resto (Dine-In / Takeaway)</option>
                                <option value="catering">Catering</option>
                                <option value="nasi_box">Nasi Box</option>
                            </select>
                        </div>

                        {{-- Kategori Menu --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Kategori Menu <span class="text-red-500">*</span></label>
                            <select name="kategori_menu_id" id="inputKategori" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0F2E23]/10 focus:border-[#0F2E23] outline-none text-sm transition-all">
                                <option value="">— Pilih Kategori —</option>
                                @foreach($kategoris as $kat)
                                    <option value="{{ $kat->id }}" data-jenis="{{ $kat->jenis_menu }}">
                                        {{ $kat->nama }} ({{ $kat->jenis_label }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Harga --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Harga (Rp) <span class="text-red-500">*</span></label>
                            <input type="number" name="harga" id="inputHarga" min="0" required placeholder="25000" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0F2E23]/10 focus:border-[#0F2E23] outline-none text-sm transition-all font-extrabold text-[#0F2E23]">
                        </div>

                        {{-- Status Menu --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Status Menu <span class="text-red-500">*</span></label>
                            <select name="status" id="inputStatus" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0F2E23]/10 focus:border-[#0F2E23] outline-none text-sm transition-all font-semibold">
                                <option value="tersedia">Tersedia (Aktif)</option>
                                <option value="nonaktif">Nonaktif</option>
                                <option value="habis">Stok Habis</option>
                            </select>
                        </div>

                        {{-- Foto --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Foto Menu (Opsional)</label>
                            <input type="file" name="foto" accept="image/*" class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#0F2E23]/10 file:text-[#0F2E23] hover:file:bg-[#0F2E23]/20 cursor-pointer">
                        </div>
                    </div>

                    {{-- Deskripsi --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Deskripsi Menu</label>
                        <textarea name="deskripsi" id="inputDeskripsi" rows="2" placeholder="Penjelasan singkat mengenai racikan atau isi menu…" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0F2E23]/10 focus:border-[#0F2E23] outline-none text-sm transition-all"></textarea>
                    </div>
                </div>

                {{-- SECTION 2: BREAKDOWN KOMPOSISI BAHAN BAKU (BOM) --}}
                <div class="space-y-3 pt-4 border-t border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-xs font-bold text-[#0F2E23] uppercase tracking-wider flex items-center gap-1.5">
                                <x-heroicon-o-beaker class="w-4 h-4 text-emerald-700" />
                                2. Komposisi Bahan Baku (Bill of Materials - BOM)
                            </h4>
                            <p class="text-[11px] text-gray-400 mt-0.5">Bahan baku yang terpakai untuk 1 porsi/paket menu ini</p>
                        </div>
                        <button type="button" onclick="addRowBOM()" class="inline-flex items-center gap-1 text-xs font-bold text-[#0F2E23] bg-emerald-50 border border-emerald-200 px-3 py-1.5 rounded-xl hover:bg-emerald-100 transition-all">
                            <x-heroicon-o-plus class="w-4 h-4" /> Tambah Bahan Baku
                        </button>
                    </div>

                    <div class="bg-gray-50/70 rounded-2xl border border-gray-200/80 p-3 overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="text-gray-500 uppercase tracking-wider border-b border-gray-200/60">
                                    <th class="pb-2 font-bold w-1/2">Pilih Bahan Baku</th>
                                    <th class="pb-2 font-bold w-1/3">Jumlah Kebutuhan (Per Porsi)</th>
                                    <th class="pb-2 font-bold w-12 text-center">Satuan</th>
                                    <th class="pb-2 font-bold w-10 text-center"></th>
                                </tr>
                            </thead>
                            <tbody id="bomContainer" class="divide-y divide-gray-200/50">
                                {{-- Rows akan dirender via JS --}}
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Submit Buttons --}}
                <div class="flex justify-end gap-3 pt-4 border-t">
                    <button type="button" onclick="closeModalMenu()" class="px-5 py-2.5 text-xs font-bold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-6 py-2.5 text-xs font-extrabold text-white bg-[#0F2E23] hover:bg-[#0a1f17] rounded-xl transition-all shadow-md active:scale-95">
                        Simpan Menu & BOM
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const bahanBakusData = @json($bahanBakus);

    function filterKategoriOptions(selectedJenis) {
        const selectKat = document.getElementById('inputKategori');
        const options = selectKat.querySelectorAll('option');
        options.forEach(opt => {
            if (!opt.value) return;
            const optJenis = opt.getAttribute('data-jenis');
            if (!selectedJenis || optJenis === selectedJenis) {
                opt.style.display = '';
            } else {
                opt.style.display = 'none';
            }
        });
    }

    function addRowBOM(selectedBahanId = '', qty = '') {
        const container = document.getElementById('bomContainer');
        const tr = document.createElement('tr');
        tr.className = 'bom-row hover:bg-gray-100/50 transition-colors';
        
        let selectOptions = '<option value="">— Pilih Bahan Baku —</option>';
        bahanBakusData.forEach(b => {
            const isSel = (b.id == selectedBahanId) ? 'selected' : '';
            const satuanNama = (b.satuan && b.satuan.nama_satuan) ? b.satuan.nama_satuan : '';
            selectOptions += `<option value="${b.id}" data-satuan="${satuanNama}" ${isSel}>${b.nama_bahan}</option>`;
        });

        tr.innerHTML = `
            <td class="py-2 pr-2 align-middle">
                <select name="bahan_baku_id[]" onchange="updateBOMSatuan(this)" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl outline-none text-xs font-medium focus:border-[#0F2E23]">
                    ${selectOptions}
                </select>
            </td>
            <td class="py-2 pr-2 align-middle">
                <input type="number" name="jumlah_kebutuhan[]" step="0.01" min="0.01" value="${qty}" placeholder="0.5" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl outline-none text-xs font-bold text-[#0F2E23] focus:border-[#0F2E23]">
            </td>
            <td class="py-2 text-center align-middle">
                <span class="bom-satuan-label text-xs font-bold text-gray-500">-</span>
            </td>
            <td class="py-2 text-center align-middle">
                <button type="button" onclick="this.closest('tr').remove()" class="text-red-500 hover:text-red-700 font-bold text-base p-1">&times;</button>
            </td>
        `;

        container.appendChild(tr);

        // trigger update satuan label for prepopulated rows
        const sel = tr.querySelector('select');
        if (selectedBahanId) {
            updateBOMSatuan(sel);
        }
    }

    function updateBOMSatuan(selectElem) {
        const opt = selectElem.options[selectElem.selectedIndex];
        const satuan = opt ? opt.getAttribute('data-satuan') : '';
        const row = selectElem.closest('tr');
        const label = row.querySelector('.bom-satuan-label');
        label.textContent = satuan || '-';
    }

    function openModalTambah() {
        document.getElementById('modalTitle').textContent = 'Tambah Menu Baru';
        document.getElementById('formMenu').action = '{{ route("menu.store") }}';
        document.getElementById('methodContainer').innerHTML = '';
        
        document.getElementById('inputNama').value = '';
        document.getElementById('inputJenisMenu').value = 'dine_in';
        filterKategoriOptions('dine_in');
        document.getElementById('inputHarga').value = '';
        document.getElementById('inputStatus').value = 'tersedia';
        document.getElementById('inputDeskripsi').value = '';
        
        document.getElementById('bomContainer').innerHTML = '';
        addRowBOM(); // add 1 default row

        document.getElementById('modalMenu').classList.remove('hidden');
    }

    function editMenu(menu, reseps) {
        document.getElementById('modalTitle').textContent = 'Edit Menu ' + (menu.kode_menu || '');
        document.getElementById('formMenu').action = `/menu/${menu.id}`;
        document.getElementById('methodContainer').innerHTML = '@method("PUT")';
        
        document.getElementById('inputNama').value = menu.nama;
        document.getElementById('inputJenisMenu').value = menu.jenis_menu || 'dine_in';
        filterKategoriOptions(menu.jenis_menu || 'dine_in');
        document.getElementById('inputKategori').value = menu.kategori_menu_id;
        document.getElementById('inputHarga').value = menu.harga;
        document.getElementById('inputStatus').value = menu.status || 'tersedia';
        document.getElementById('inputDeskripsi').value = menu.deskripsi || '';
        
        document.getElementById('bomContainer').innerHTML = '';
        if (reseps && reseps.length > 0) {
            reseps.forEach(r => {
                addRowBOM(r.bahan_baku_id, r.jumlah_kebutuhan);
            });
        } else {
            addRowBOM();
        }

        document.getElementById('modalMenu').classList.remove('hidden');
    }

    function closeModalMenu() {
        document.getElementById('modalMenu').classList.add('hidden');
    }
</script>
@endsection
