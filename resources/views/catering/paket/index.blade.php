{{-- 
    Halaman: Daftar Paket Catering / Nasi Box
    UI: disamakan dengan Kelola Menu (slide-in drawer view, custom delete modal)
--}}
@extends('layouts.pos')

@section('title') {{ $jenis == 'nasi_box' ? 'Menu Nasi Box' : ($jenis == 'catering' ? 'Menu Catering' : 'Paket Menu') }} @endsection

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800 font-sans">
    <div class="w-full px-4 md:px-6 py-6 space-y-5">

        {{-- PAGE HEADER --}}
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-lg font-bold text-gray-900">
                    {{ $jenis == 'nasi_box' ? 'Menu Nasi Box' : ($jenis == 'catering' ? 'Menu Catering' : 'Paket Menu') }}
                </h1>
                <p class="text-xs text-gray-500 mt-0.5">Kelola susunan paket, komponen resep, dan status tampilan di website.</p>
            </div>
            <a href="{{ route('paket-catering.create', ['jenis' => $jenis]) }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-gray-900 rounded-lg px-3 py-2 hover:bg-gray-800 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Paket Baru
            </a>
        </div>

        <x-ui.alert />

        {{-- Filter Bar --}}
        <div class="flex flex-col sm:flex-row gap-2 items-start sm:items-center justify-between mb-3 shrink-0">
            <div class="flex items-center gap-2">
                <span class="text-xs font-medium text-gray-500">Total Paket:</span>
                <span class="text-xs font-semibold text-gray-900 bg-gray-100 rounded-full px-2.5 py-0.5">{{ count($pakets) }}</span>
            </div>
            <div class="flex items-center gap-1 text-xs font-medium overflow-x-auto no-scrollbar shrink-0">
                <span class="text-gray-500 mr-1">Jenis:</span>
                <a href="{{ route('paket-catering.index', ['jenis' => 'catering']) }}"
                   class="px-3 py-1.5 rounded-lg transition-colors {{ $jenis === 'catering' ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100' }}">Catering</a>
                <a href="{{ route('paket-catering.index', ['jenis' => 'nasi_box']) }}"
                   class="px-3 py-1.5 rounded-lg transition-colors {{ $jenis === 'nasi_box' ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100' }}">Nasi Box</a>
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-xs font-semibold text-gray-400 uppercase tracking-wide">
                        <th class="px-4 py-3 text-left w-12">No.</th>
                        <th class="px-4 py-3 text-left">Nama Paket</th>
                        <th class="px-4 py-3 text-left">Jenis</th>
                        <th class="px-4 py-3 text-left">Harga / Porsi</th>
                        <th class="px-4 py-3 text-left">Komponen</th>
                        <th class="px-4 py-3 text-left">Status Website</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($pakets as $paket)
                    <tr class="hover:bg-gray-50/60 transition-colors group">
                        <td class="px-4 py-3 text-xs text-gray-500 font-medium align-middle">
                            {{ $loop->iteration }}
                        </td>
                        <td class="px-4 py-3">
                            <p class="font-semibold text-gray-900 leading-tight">{{ $paket->nama_paket }}</p>
                            @if($paket->deskripsi)
                                <p class="text-xs text-gray-400 mt-0.5 truncate max-w-xs">{{ $paket->deskripsi }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($paket->jenis_paket == 'nasi_box')
                                <span class="inline-block text-xs font-semibold px-2 py-0.5 rounded-md bg-purple-50 text-purple-700">Nasi Box</span>
                            @else
                                <span class="inline-block text-xs font-semibold px-2 py-0.5 rounded-md bg-blue-50 text-blue-700">Catering</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 font-semibold text-gray-900 tabular-nums">
                            Rp {{ number_format($paket->harga, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-block text-xs font-medium text-gray-700 bg-gray-100 rounded-full px-2.5 py-0.5">
                                {{ $paket->komponens_count }} Komponen
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <form action="{{ route('paket-catering.toggle', $paket->id) }}" method="POST">
                                @csrf @method('PATCH')
                                <button type="submit" title="{{ $paket->is_active ? 'Tampil di Website — klik untuk sembunyikan' : 'Disembunyikan — klik untuk tampilkan' }}"
                                    class="relative inline-flex items-center h-5 rounded-full w-9 focus:outline-none transition-colors ease-in-out duration-200 {{ $paket->is_active ? 'bg-emerald-500' : 'bg-gray-300' }}">
                                    <span class="inline-block w-3.5 h-3.5 transform bg-white rounded-full transition ease-in-out duration-200 {{ $paket->is_active ? 'translate-x-4' : 'translate-x-0.5' }}"></span>
                                </button>
                            </form>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1 opacity-70 group-hover:opacity-100 transition-opacity">
                                {{-- View (Detail) --}}
                                <button onclick="openPaketDrawer({{ json_encode($paket->load('komponens.opsi.menu')) }}, true)"
                                    class="p-1.5 rounded-md text-gray-500 hover:bg-gray-100 hover:text-gray-900 transition-colors" title="Lihat">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                                {{-- Edit (Update) --}}
                                <a href="{{ route('paket-catering.edit', $paket->id) }}"
                                    class="p-1.5 rounded-md text-gray-500 hover:bg-blue-50 hover:text-blue-600 transition-colors" title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                {{-- Delete (Hapus) --}}
                                <button onclick="openDeleteModal({{ $paket->id }}, '{{ addslashes($paket->nama_paket) }}')"
                                    class="p-1.5 rounded-md text-gray-500 hover:bg-red-50 hover:text-red-600 transition-colors" title="Hapus">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
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
            <button onclick="closePaketDrawer()" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
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
            <a id="vBtnEdit" href="#" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-gray-900 rounded-lg px-4 py-2 hover:bg-gray-800 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit Paket
            </a>
            <button onclick="closePaketDrawer()" class="text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg px-4 py-2 hover:bg-gray-50 transition-colors">Tutup</button>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════ --}}
{{-- MODAL: KONFIRMASI HAPUS                  --}}
{{-- ══════════════════════════════════════════ --}}
<div id="modalHapus" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/30 backdrop-blur-[2px]" onclick="closeDeleteModal()"></div>
    <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm mx-4 p-6 space-y-4 text-center">
        <div class="w-14 h-14 rounded-full border-2 border-orange-300 flex items-center justify-center mx-auto">
            <span class="text-orange-400 text-2xl font-bold">!</span>
        </div>
        <div>
            <h3 class="font-semibold text-gray-900 text-base">Konfirmasi</h3>
            <p class="text-sm text-gray-500 mt-1">Hapus paket <span id="deleteNama" class="font-semibold text-gray-800"></span>?</p>
        </div>
        <form id="formHapus" method="POST" action="">
            @csrf @method('DELETE')
            <div class="flex gap-3 justify-center pt-1">
                <button type="button" onclick="closeDeleteModal()"
                    class="flex-1 text-sm font-semibold text-white bg-red-500 rounded-xl px-4 py-2.5 hover:bg-red-600 transition-colors">
                    Batal
                </button>
                <button type="submit"
                    class="flex-1 text-sm font-semibold text-white bg-[#0F2E23] rounded-xl px-4 py-2.5 hover:bg-[#0a1f17] transition-colors">
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
    document.getElementById('paketDrawerSubtitle').textContent = paket.nama_paket ?? '';

    document.getElementById('vNamaPaket').textContent = paket.nama_paket ?? '-';
    document.getElementById('vJenisPaket').textContent = paket.jenis_paket === 'nasi_box' ? 'Nasi Box' : 'Catering';
    document.getElementById('vHargaPaket').textContent = 'Rp ' + Number(paket.harga ?? 0).toLocaleString('id-ID');

    const statusEl = document.getElementById('vStatusPaket');
    if (paket.is_active) {
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
    if (paket.komponens && paket.komponens.length > 0) {
        paket.komponens.forEach(komp => {
            const tipeBadge = komp.tipe === 'choice'
                ? '<span class="inline-block text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 rounded-full px-2 py-0.5 ml-1">pilih 1</span>'
                : '<span class="inline-block text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200 rounded-full px-2 py-0.5 ml-1">semua dapat</span>';

            let menuList = '';
            if (komp.opsi && komp.opsi.length > 0) {
                komp.opsi.forEach(opsi => {
                    const namaMenu = (opsi.menu && opsi.menu.nama) ? opsi.menu.nama : (opsi.menu_id ?? '-');
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
