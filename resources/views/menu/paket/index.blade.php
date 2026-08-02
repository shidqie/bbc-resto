{{-- 
    Halaman: Daftar Paket Catering / Nasi Box
    UI: disamakan dengan Kelola Menu (slide-in drawer view, custom delete modal)
--}}
@extends('layouts.pos')

@section('title') {{ $jenis == 'nasi_box' ? 'Menu Nasi Box' : ($jenis == 'catering' ? 'Menu Catering' : 'Paket Menu') }} @endsection

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800">
    <div class="w-full p-6 space-y-5">

        {{-- PAGE HEADER --}}
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-black text-gray-900 tracking-tight">Manajemen Menu</h1>
                <p class="text-sm text-gray-500 font-medium mt-1">Kelola menu berdasarkan layanan dan kategorinya.</p>
            </div>
            <a href="{{ route('paket-catering.create', ['jenis' => $jenis]) }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-gray-900 rounded-2xl px-3 py-2 hover:bg-gray-800 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Paket Baru
            </a>
        </div>

        <x-ui.alert />

        {{-- TABS --}}
        <div class="flex items-center gap-6 border-b border-gray-200">
            <a href="{{ route('menu.index') }}" class="py-3 text-sm font-medium border-b-2 transition-colors border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                Menu Dine In
            </a>
            <a href="{{ route('paket-catering.index', ['jenis' => 'nasi_box']) }}" class="py-3 text-sm border-b-2 transition-colors {{ $jenis === 'nasi_box' ? 'font-bold border-gray-900 text-gray-900' : 'font-medium border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Menu Nasi Box
            </a>
            <a href="{{ route('paket-catering.index', ['jenis' => 'catering']) }}" class="py-3 text-sm border-b-2 transition-colors {{ $jenis === 'catering' ? 'font-bold border-gray-900 text-gray-900' : 'font-medium border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Menu Katering
            </a>
        </div>

        {{-- Filter Bar --}}
        <div class="flex flex-col sm:flex-row gap-2 items-start sm:items-center justify-between mb-3 shrink-0">
            <div class="flex items-center gap-2">
                <span class="text-xs font-medium text-gray-500">Total Paket:</span>
                <span class="text-xs font-semibold text-gray-900 bg-gray-100 rounded-full px-2.5 py-0.5">{{ count($pakets) }}</span>
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-3xl border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-xs font-semibold text-gray-400 uppercase tracking-wide">
                        <th class="px-4 py-3 text-left w-12">No</th>
                        <th class="px-4 py-3 text-left">Nama Paket</th>
                        <th class="px-4 py-3 text-left">Harga / Porsi</th>
                        <th class="px-4 py-3 text-left">Komponen</th>
                        <th class="px-4 py-3 text-left">Status Website</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($pakets as $paket)
                    <tr class="hover:bg-gray-50/60 transition-colors group">
                        <td class="px-4 py-3 text-xs text-gray-500 font-medium align-middle">
                            {{ $loop->iteration }}
                        </td>
                        <td class="px-4 py-3">
                            <p class="font-semibold text-gray-900 leading-tight">{{ $paket->nama_menu }}</p>
                            @if($paket->deskripsi)
                                <p class="text-xs text-gray-400 mt-0.5 truncate max-w-xs">{{ $paket->deskripsi }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900 font-semibold">
                            Rp {{ number_format($paket->harga_jual, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-block text-xs font-medium text-gray-700 bg-gray-100 rounded-full px-2.5 py-0.5">
                                {{ $paket->komponen_paket_count }} Komponen
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <form action="{{ route('paket-catering.toggle', $paket->id) }}" method="POST">
                                @csrf @method('PATCH')
                                <button type="submit" title="{{ $paket->status_aktif ? 'Tampil di Website — klik untuk sembunyikan' : 'Disembunyikan — klik untuk tampilkan' }}"
                                    class="relative inline-flex items-center h-5 rounded-full w-9 focus:outline-none transition-colors ease-in-out duration-200 {{ $paket->status_aktif ? 'bg-emerald-500' : 'bg-gray-300' }}">
                                    <span class="inline-block w-3.5 h-3.5 transform bg-white rounded-full transition ease-in-out duration-200 {{ $paket->status_aktif ? 'translate-x-4' : 'translate-x-0.5' }}"></span>
                                </button>
                            </form>
                        </td>
                        <td class="px-4 py-3 text-center text-sm font-medium">
                            <div class="flex items-center justify-center gap-1.5">
                                {{-- View (Detail) --}}
                                <button onclick="openPaketDrawer({{ json_encode($paket->load('komponen_paket.opsi')) }}, true)" title="Detail" class="w-7 h-7 rounded-full flex items-center justify-center bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors">
                                    <x-heroicon-o-eye class="w-3 h-3" />
                                </button>
                                {{-- Edit (Update) --}}
                                <a href="{{ route('paket-catering.edit', $paket->id) }}" title="Ubah" class="w-7 h-7 rounded-full flex items-center justify-center bg-amber-50 text-amber-600 hover:bg-amber-100 transition-colors">
                                    <x-heroicon-o-pencil-square class="w-3 h-3" />
                                </a>
                                {{-- Delete (Hapus) --}}
                                <button onclick="openDeleteModal({{ $paket->id }}, '{{ addslashes($paket->nama_menu) }}')" title="Hapus" class="w-7 h-7 rounded-full flex items-center justify-center bg-red-50 text-red-600 hover:bg-red-100 transition-colors">
                                    <x-heroicon-o-trash class="w-3 h-3" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-14 text-center text-gray-400">
                            <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            <p class="text-sm font-medium">Belum ada paket {{ $jenis == 'nasi_box' ? 'Nasi Box' : 'Catering' }} terdaftar.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

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
            <button onclick="closePaketDrawer()" class="p-1.5 rounded-2xl text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Body --}}
        <div class="flex-1 overflow-y-auto px-5 py-5 space-y-5">

            {{-- Info Utama --}}
            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Nama Paket</label>
                    <p id="vNamaPaket" class="text-sm font-semibold text-gray-900">-</p>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Jenis</label>
                        <p id="vJenisPaket" class="text-sm font-medium text-gray-800">-</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Harga / Porsi</label>
                        <p id="vHargaPaket" class="text-sm font-semibold text-gray-900">-</p>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Status Website</label>
                    <p id="vStatusPaket" class="text-sm font-medium">-</p>
                </div>
                <div id="vDeskripsiWrap">
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Deskripsi</label>
                    <p id="vDeskripsiPaket" class="text-sm text-gray-700">-</p>
                </div>
            </div>

            {{-- Komponen Paket --}}
            <div class="pt-3 border-t border-gray-100">
                <p class="text-xs font-semibold text-gray-700 mb-3">Komponen & Pilihan Menu</p>
                <div id="vKomponen" class="space-y-3">
                    <p class="text-xs text-gray-400 text-center py-4">Tidak ada komponen.</p>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="px-5 py-4 border-t border-gray-100 flex justify-between items-center bg-gray-50/80 shrink-0">
            <a id="vBtnEdit" href="#" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-gray-900 rounded-2xl px-4 py-2 hover:bg-gray-800 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit Paket
            </a>
            <button onclick="closePaketDrawer()" class="text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-2xl px-4 py-2 hover:bg-gray-50 transition-colors">Tutup</button>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════ --}}
{{-- MODAL: KONFIRMASI HAPUS                  --}}
{{-- ══════════════════════════════════════════ --}}
<div id="modalHapus" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/30 backdrop-blur-[2px]" onclick="closeDeleteModal()"></div>
    <div class="relative bg-white rounded-[2.25rem] shadow-xl w-full max-w-sm mx-4 p-6 space-y-4 text-center">
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
                    class="flex-1 text-sm font-semibold text-white bg-red-500 rounded-3xl px-4 py-2.5 hover:bg-red-600 transition-colors">
                    Batal
                </button>
                <button type="submit"
                    class="flex-1 text-sm font-semibold text-white bg-[#0F2E23] rounded-3xl px-4 py-2.5 hover:bg-[#0a1f17] transition-colors">
                    Ya, Lanjutkan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const BASE_URL = '{{ url('/') }}';

// ═══ DRAWER: VIEW DETAIL ═══
function openPaketDrawer(paket, isView = true) {
    document.getElementById('paketDrawerTitle').textContent = 'Detail Paket';
    document.getElementById('paketDrawerSubtitle').textContent = paket.nama_menu ?? '';

    document.getElementById('vNamaPaket').textContent = paket.nama_menu ?? '-';
    document.getElementById('vJenisPaket').textContent = Number(paket.jenis_menu_id) === 3 ? 'Nasi Box' : 'Catering';
    document.getElementById('vHargaPaket').textContent = 'Rp ' + Number(paket.harga_jual ?? 0).toLocaleString('id-ID');

    const statusEl = document.getElementById('vStatusPaket');
    if (paket.status_aktif) {
        statusEl.textContent = '● Tampil di Website';
        statusEl.className = 'text-sm font-medium text-emerald-600';
    } else {
        statusEl.textContent = '● Disembunyikan';
        statusEl.className = 'text-sm font-medium text-gray-500';
    }

    const deskEl = document.getElementById('vDeskripsiPaket');
    deskEl.textContent = paket.deskripsi || '-';

    // Render komponens
    const kompContainer = document.getElementById('vKomponen');
    kompContainer.innerHTML = '';
    if (paket.komponen_paket && paket.komponen_paket.length > 0) {
        paket.komponen_paket.forEach(komp => {
            const tipeBadge = komp.tipe_komponen === 'pilihan'
                ? '<span class="inline-block text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 rounded-full px-2 py-0.5 ml-1">pilih 1</span>'
                : '<span class="inline-block text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200 rounded-full px-2 py-0.5 ml-1">semua dapat</span>';

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
                <div class="bg-gray-50 border border-gray-200 rounded-3xl p-3 space-y-2">
                    <div class="flex items-center gap-1">
                        <p class="text-xs font-semibold text-gray-800">${komp.nama_komponen ?? '-'}</p>
                        ${tipeBadge}
                    </div>
                    <div class="flex flex-wrap gap-1.5">${menuList}</div>
                </div>
            `;
        });
    } else {
        kompContainer.innerHTML = '<p class="text-xs text-gray-400 text-center py-4">Tidak ada komponen.</p>';
    }

    // Set edit button URL
    document.getElementById('vBtnEdit').href = `${BASE_URL}/paket-catering/${paket.id}/edit`;

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
