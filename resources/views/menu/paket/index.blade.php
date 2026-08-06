{{-- 
    Halaman: Daftar Paket Katering / Nasi Box
    UI: disamakan dengan Kelola Menu (slide-in drawer view, custom delete modal)
--}}
@extends('layouts.pos')

@section('title') {{ $jenis == 'nasi_box' ? 'Menu Nasi Box' : ($jenis == 'catering' ? 'Menu Katering' : 'Paket Menu') }} @endsection

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800 pb-10">
    <div class="w-full p-6 space-y-5">

        {{-- PAGE HEADER --}}
        <x-ui.page-header title="Manajemen Menu & Paket" subtitle="Kelola menu berdasarkan layanan dan kategorinya." :breadcrumbs="['Manajemen Menu', 'Paket']">
            <x-slot:actions>
                <button onclick="openPaketForm()" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-gray-900 rounded-lg px-3 py-2 hover:bg-gray-800 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Paket Baru
                </button>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.alert />

        {{-- TABS --}}
        <div class="flex items-center gap-6 border-b border-gray-200">
            <a href="{{ route('menu.index') }}" class="py-3 text-sm font-medium border-b-2 transition-colors border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                Menu Dine In
            </a>
            <a href="{{ route('paket-catering.index', ['jenis' => 'nasi_box']) }}" class="py-3 text-sm border-b-2 transition-colors {{ $jenis === 'nasi_box' ? 'font-bold border-gray-900 text-gray-900' : 'font-medium border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Paket Menu Nasi Box
            </a>
            <a href="{{ route('paket-catering.index', ['jenis' => 'catering']) }}" class="py-3 text-sm border-b-2 transition-colors {{ $jenis === 'catering' ? 'font-bold border-gray-900 text-gray-900' : 'font-medium border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Paket Menu Katering
            </a>
        </div>

        {{-- Table --}}
        <x-ui.data-table>
            <x-slot:toolbar>
                <form action="{{ route('paket-catering.index') }}" method="GET" class="flex items-center gap-2 w-full sm:w-auto">
                    <x-search-input name="search" value="{{ request('search') }}" placeholder="Cari nama paket…" width="w-full sm:w-56" />
                    <input type="hidden" name="jenis" value="{{ $jenis }}">
                </form>
            </x-slot:toolbar>

            <x-ui.table class="min-w-[900px]">
                <x-ui.table.header>
                    <th class="px-4 py-3.5 text-left w-12">No</th>
                    <th class="px-4 py-3.5 text-left">Foto</th>
                    <th class="px-4 py-3.5 text-left">Nama Paket</th>
                    <th class="px-4 py-3.5 text-left">Harga / Porsi</th>
                    <th class="px-4 py-3.5 text-left">Deskripsi</th>
                    <th class="px-4 py-3.5 text-left">Item Menu</th>
                    <th class="px-4 py-3.5 text-center">Aksi</th>
                </x-ui.table.header>
                <tbody class="divide-y divide-gray-100">
                    @forelse($pakets as $paket)
                    <x-ui.table.row>
                        <td class="px-4 py-4 text-sm text-gray-500 font-medium align-middle">
                            {{ $loop->iteration }}
                        </td>
                        <td class="px-4 py-4 align-middle">
                            @if($paket->foto)
                                <img src="{{ Storage::url($paket->foto) }}" alt="{{ $paket->nama_menu }}" class="w-10 h-10 rounded-lg object-cover">
                            @else
                                <div class="w-10 h-10 rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center text-gray-400">
                                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-4 align-middle">
                            <p class="font-semibold text-gray-900 leading-tight">{{ $paket->nama_menu }}</p>
                        </td>
                        <td class="px-4 py-4 align-middle text-sm text-gray-900 font-semibold">
                            Rp {{ number_format($paket->harga_jual, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-4 align-middle">
                            @if($paket->deskripsi)
                                <p class="text-sm text-gray-500 max-w-xs truncate" title="{{ $paket->deskripsi }}">{{ $paket->deskripsi }}</p>
                            @else
                                <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 align-middle">
                            <x-ui.badge color="gray" size="sm">{{ $paket->komponen_paket_count }} Item Menu</x-ui.badge>
                        </td>
                        <td class="px-4 py-4 align-middle text-center">
                            <div class="flex items-center justify-center gap-2">
                                {{-- View (Detail) --}}
                                <x-ui.action-button onclick="openPaketDrawer({{ json_encode($paket->load('komponen_paket.opsi')) }}, true)" title="Detail">
                                    <x-heroicon-o-eye class="w-4 h-4" />
                                </x-ui.action-button>
                                {{-- Edit (Update) --}}
                                <x-ui.action-button onclick="openPaketForm({{ $paket->id }})" title="Ubah">
                                    <x-heroicon-o-pencil-square class="w-4 h-4" />
                                </x-ui.action-button>
                                {{-- Delete (Hapus) --}}
                                <x-ui.action-button onclick="openDeleteModal({{ $paket->id }}, '{{ addslashes($paket->nama_menu) }}')" title="Hapus">
                                    <x-heroicon-o-trash class="w-4 h-4" />
                                </x-ui.action-button>
                            </div>
                        </td>
                    </x-ui.table.row>
                    @empty
                    <x-empty-state icon="document-text" title="Belum ada paket terdaftar." message="Tambahkan paket baru menggunakan tombol di atas." :colspan="7" />
                    @endforelse
                </tbody>
            </x-ui.table>
        </x-ui.data-table>

    </div>
</div>

{{-- ══════════════════════════════════════════ --}}
{{-- DRAWER: VIEW DETAIL PAKET (SLIDE-IN RIGHT) --}}
{{-- ══════════════════════════════════════════ --}}
<div id="drawerPaket" class="fixed inset-x-0 bottom-0 top-16 z-40 hidden">
    <div class="absolute inset-0 bg-black/30 backdrop-blur-[2px]" onclick="closePaketDrawer()"></div>
    <div class="absolute right-0 top-0 h-full w-full max-w-lg bg-white shadow-2xl flex flex-col translate-x-full transition-transform duration-300" id="drawerPaketPanel">

        {{-- Header --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <div>
                <h2 class="font-semibold text-gray-900" id="paketDrawerTitle">Detail Paket</h2>
                <p class="text-xs text-gray-400 mt-0.5" id="paketDrawerSubtitle">Informasi lengkap paket</p>
            </div>
            <button onclick="closePaketDrawer()" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Body --}}
        <div class="flex-1 overflow-y-auto px-5 py-5 space-y-5">

            {{-- Info Utama --}}
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-semibold text-gray-500 mb-1">Nama Paket</label>
                    <p id="vNamaPaket" class="text-sm font-semibold text-gray-900">-</p>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold text-gray-500 mb-1">Jenis</label>
                        <p id="vJenisPaket" class="text-sm font-medium text-gray-800">-</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-500 mb-1">Harga / Porsi</label>
                        <p id="vHargaPaket" class="text-sm font-semibold text-gray-900">-</p>
                    </div>
                </div>
                <div>
                    <div id="vDeskripsiWrap">
                    <label class="block text-sm font-semibold text-gray-500 mb-1">Deskripsi</label>
                    <p id="vDeskripsiPaket" class="text-sm text-gray-700">-</p>
                </div>
            </div>

            {{-- Komponen Paket --}}
            <div class="pt-3 border-t border-gray-100">
                <p class="text-xs font-semibold text-gray-700 mb-3">Item Menu & Pilihan</p>
                <div id="vKomponen" class="space-y-3">
                    <p class="text-xs text-gray-400 text-center py-4">Tidak ada item menu.</p>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="px-5 py-4 border-t border-gray-100 flex justify-between items-center bg-gray-50/80 shrink-0">
            <button type="button" id="vBtnEdit" onclick="openPaketForm(window.currentPaketDetail ? window.currentPaketDetail.id : null)" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-gray-900 rounded-lg px-4 py-2 hover:bg-gray-800 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit Paket
            </button>
            <button onclick="closePaketDrawer()" class="text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg px-4 py-2 hover:bg-gray-50 transition-colors">Tutup</button>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════ --}}
{{-- DRAWER: TAMBAH/EDIT PAKET (SLIDE-IN RIGHT) --}}
{{-- ══════════════════════════════════════════ --}}
<div id="drawerPaketForm" class="fixed inset-x-0 bottom-0 top-16 z-40 hidden">
    <div class="absolute inset-0 bg-black/30 backdrop-blur-[2px]" onclick="closePaketForm()"></div>
    <div class="absolute right-0 top-0 h-full w-full max-w-lg bg-white shadow-2xl flex flex-col translate-x-full transition-transform duration-300" id="drawerPaketFormPanel">

        {{-- Header --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <div>
                <h2 class="font-semibold text-gray-900" id="paketFormTitle">Tambah Paket Baru</h2>
                <p class="text-xs text-gray-400 mt-0.5" id="paketFormSubtitle">Isi informasi paket dan item menu</p>
            </div>
            <button onclick="closePaketForm()" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Form Body --}}
        <form id="formPaket" action="{{ route('paket-catering.store') }}" method="POST" enctype="multipart/form-data" class="flex-1 overflow-y-auto">
            @csrf
            <div id="formPaketMethod"></div>
            <div class="px-5 py-5 space-y-4">

                {{-- Nama --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nama Paket <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_paket" id="fpNama" required placeholder="Contoh: Nasi Box Paket A" class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-all">
                </div>

                {{-- Jenis + Harga --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Jenis Paket <span class="text-red-500">*</span></label>
                        <select name="jenis_paket" id="fpJenis" required class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg bg-white outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-all">
                            <option value="catering">Katering</option>
                            <option value="nasi_box">Nasi Box</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Harga (Rp) <span class="text-red-500">*</span></label>
                        <input type="number" name="harga" id="fpHarga" required min="0" placeholder="25000" class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-all">
                    </div>
                </div>

                {{-- Deskripsi --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Deskripsi</label>
                    <input type="text" name="deskripsi" id="fpDeskripsi" placeholder="Deskripsi singkat paket…" class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-all">
                </div>

                {{-- Foto --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Foto Paket</label>
                    <input type="file" name="foto" id="fpFoto" accept="image/*" class="w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 cursor-pointer">
                </div>

                {{-- Item Menu --}}
                <div class="pt-2 border-t border-gray-100">
                    @include('menu.paket.partials.komponen-builder', ['existingKomponen' => []])
                </div>
            </div>

            {{-- Footer --}}
            <div class="px-5 py-4 border-t border-gray-100 flex justify-end gap-2 bg-gray-50/80 shrink-0">
                <button type="button" onclick="closePaketForm()" class="text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg px-4 py-2 hover:bg-gray-50 transition-colors">Batal</button>
                <button type="submit" class="text-sm font-semibold text-white bg-gray-900 rounded-lg px-5 py-2 hover:bg-gray-800 transition-colors">Simpan Paket</button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════ --}}
{{-- MODAL: KONFIRMASI HAPUS                  --}}
{{-- ══════════════════════════════════════════ --}}
<div id="modalHapus" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/30 backdrop-blur-[2px]" onclick="closeDeleteModal()"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-sm mx-4 p-6 space-y-4 text-center">
        <div class="w-14 h-14 rounded-full border-2 border-orange-300 flex items-center justify-center mx-auto">
            <span class="text-orange-400 text-2xl font-bold">!</span>
        </div>
        <div>
            <h3 class="font-semibold text-gray-900 text-base">Konfirmasi</h3>
            <p class="text-sm text-gray-500 font-medium mt-1">Hapus paket <span id="deleteNama" class="font-semibold text-gray-800"></span>?</p>
        </div>
        <form id="formHapus" method="POST" action="">
            @csrf @method('DELETE')
            <div class="flex gap-3 justify-center pt-1">
                <button type="button" onclick="closeDeleteModal()"
                    class="flex-1 text-sm font-semibold text-white bg-red-500 rounded-xl px-4 py-2.5 hover:bg-red-600 transition-colors">
                    Batal
                </button>
                <button type="submit"
                    class="flex-1 text-sm font-semibold text-white bg-[#0D3024] rounded-xl px-4 py-2.5 hover:bg-[#0a1f17] transition-colors">
                    Ya, Lanjutkan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const BASE_URL = '{{ url('/') }}';
const paketsData = @json($pakets->load('komponen_paket.opsi'));

// ═══ DRAWER: VIEW DETAIL ═══
function openPaketDrawer(paket, isView = true) {
    document.getElementById('paketDrawerTitle').textContent = 'Detail Paket';
    document.getElementById('paketDrawerSubtitle').textContent = paket.nama_menu ?? '';

    document.getElementById('vNamaPaket').textContent = paket.nama_menu ?? '-';
    document.getElementById('vJenisPaket').textContent = Number(paket.jenis_menu_id) === 3 ? 'Nasi Box' : 'Katering';
    document.getElementById('vHargaPaket').textContent = 'Rp ' + Number(paket.harga_jual ?? 0).toLocaleString('id-ID');

    const deskEl = document.getElementById('vDeskripsiPaket');
    deskEl.textContent = paket.deskripsi || '-';

    // Render komponens
    const kompContainer = document.getElementById('vKomponen');
    kompContainer.innerHTML = '';
    if (paket.komponen_paket && paket.komponen_paket.length > 0) {
        paket.komponen_paket.forEach(komp => {
            const tipeBadge = komp.tipe_komponen === 'pilihan'
                ? '<span class="inline-block text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 rounded-full px-2 py-0.5 ml-1">pilih 1</span>'
                : '<span class="inline-block text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200 rounded-full px-2 py-0.5 ml-1">semua dapat</span>';

            let menuList = '';
            if (komp.opsi && komp.opsi.length > 0) {
                komp.opsi.forEach(opsi => {
                    const namaMenu = opsi.nama_pilihan ?? '-';
                    menuList += `<span class="inline-block text-xs px-2.5 py-1 bg-white border border-gray-200 rounded-full text-gray-700 font-medium">${namaMenu}</span>`;
                });
            } else {
                menuList = '<span class="text-xs text-gray-400">Tidak ada menu terpilih</span>';
            }

            kompContainer.innerHTML += `
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-3 space-y-2">
                    <div class="flex items-center gap-1">
                        <p class="text-xs font-semibold text-gray-800">${komp.nama_komponen ?? '-'}</p>
                        ${tipeBadge}
                    </div>
                    <div class="flex flex-wrap gap-1.5">${menuList}</div>
                </div>
            `;
        });
    } else {
        kompContainer.innerHTML = '<p class="text-xs text-gray-400 text-center py-4">Tidak ada item menu.</p>';
    }

    // Simpan paket aktif untuk tombol edit
    window.currentPaketDetail = paket;

    const drawer = document.getElementById('drawerPaket');
    const panel = document.getElementById('drawerPaketPanel');
    drawer.classList.remove('hidden');
    drawer.style.display = 'flex';
    requestAnimationFrame(() => {
        panel.classList.remove('translate-x-full');
    });
}

function closePaketDrawer() {
    const drawer = document.getElementById('drawerPaket');
    const panel = document.getElementById('drawerPaketPanel');
    panel.classList.add('translate-x-full');
    setTimeout(() => {
        drawer.classList.add('hidden');
        drawer.style.display = '';
    }, 300);
}

// ═══ DRAWER: TAMBAH/EDIT PAKET ═══
function openPaketForm(id = null) {
    let paket = null;
    if (id) {
        paket = paketsData.find(p => p.id == id);
    }

    const drawer = document.getElementById('drawerPaketForm');
    const panel = document.getElementById('drawerPaketFormPanel');

    document.getElementById('formPaket').action = '{{ route("paket-catering.store") }}';
    document.getElementById('formPaketMethod').innerHTML = '';

    document.getElementById('paketFormTitle').textContent = paket ? 'Edit Paket' : 'Tambah Paket Baru';
    document.getElementById('paketFormSubtitle').textContent = paket ? (paket.nama_menu ?? '') : 'Isi informasi paket dan item menu';

    document.getElementById('fpNama').value = paket ? (paket.nama_menu ?? '') : '';
    document.getElementById('fpJenis').value = paket ? (Number(paket.jenis_menu_id) === 3 ? 'nasi_box' : 'catering') : '{{ $jenis === 'nasi_box' ? 'nasi_box' : 'catering' }}';
    document.getElementById('fpHarga').value = paket ? (paket.harga_jual ?? '') : '';
    document.getElementById('fpDeskripsi').value = paket?.deskripsi ?? '';
    document.getElementById('fpFoto').value = '';

    // Reset komponen builder (Alpine)
    window.dispatchEvent(new CustomEvent('set-readonly', { detail: false }));
    window.dispatchEvent(new CustomEvent('set-komponens', { detail: (paket && paket.komponen_paket) ? paket.komponen_paket : [] }));

    if (paket) {
        document.getElementById('formPaket').action = `${BASE_URL}/paket-catering/${paket.id}`;
        document.getElementById('formPaketMethod').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    }

    drawer.classList.remove('hidden');
    drawer.style.display = 'flex';
    requestAnimationFrame(() => {
        panel.classList.remove('translate-x-full');
    });
}

function closePaketForm() {
    const drawer = document.getElementById('drawerPaketForm');
    const panel = document.getElementById('drawerPaketFormPanel');
    panel.classList.add('translate-x-full');
    setTimeout(() => {
        drawer.classList.add('hidden');
        drawer.style.display = '';
    }, 300);
}

// ═══ MODAL: KONFIRMASI HAPUS ═══
function openDeleteModal(id, nama) {
    document.getElementById('deleteNama').textContent = nama;
    document.getElementById('formHapus').action = `${BASE_URL}/paket-catering/${id}`;
    const modal = document.getElementById('modalHapus');
    modal.classList.remove('hidden');
    modal.style.display = 'flex';
}

function closeDeleteModal() {
    const modal = document.getElementById('modalHapus');
    modal.classList.add('hidden');
    modal.style.display = '';
}
</script>

@endsection
