@extends('layouts.pos')

@section('content')
<div class="flex-1 overflow-auto bg-gray-50 text-gray-800">
    <div class="w-full p-6 space-y-5">

        {{-- PAGE HEADER --}}
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-black text-gray-900 tracking-tight">Manajemen Meja</h1>
                <p class="text-sm text-gray-500 font-medium mt-1">Kelola data meja restoran, kapasitas, dan pantau statusnya.</p>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="openMejaModal()" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-gray-900 rounded-lg px-3 py-2 hover:bg-gray-800 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Meja Baru
                </button>
            </div>
        </div>

        <x-ui.alert />

        {{-- Filter / Info Bar --}}
        <div class="flex flex-col sm:flex-row gap-2 items-start sm:items-center justify-between mb-3 shrink-0">
            <form action="{{ route('meja.index') }}" method="GET" class="flex items-center gap-2 w-full sm:w-auto">
                <div class="relative flex-1 sm:flex-none sm:w-56">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nomor meja…" class="w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-1 focus:ring-gray-400 focus:border-gray-400 outline-none bg-white">
                </div>
                <button type="submit" class="text-xs font-medium bg-white border border-gray-200 text-gray-600 rounded-lg px-3 py-2 hover:bg-gray-50 transition-colors shrink-0">Cari</button>
            </form>
            
            <div class="flex items-center gap-2">
                <a href="{{ url('pos/dinein/qr-codes') }}" target="_blank" class="inline-flex items-center gap-1.5 text-sm font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg px-3 py-2 hover:bg-emerald-100 transition-colors shadow-sm">
                    <x-heroicon-o-document class="w-4 h-4" />
                    Unduh Semua QR
                </a>
            </div>
        </div>

        {{-- Table Container --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-xs font-semibold text-gray-400 uppercase tracking-wide">
                        <th class="px-4 py-3 text-left w-12">No.</th>
                        <th class="px-4 py-3 text-left">Nomor Meja</th>
                        <th class="px-4 py-3 text-left">Area</th>
                        <th class="px-4 py-3 text-left">Kapasitas</th>
                        <th class="px-4 py-3 text-left">Status Saat Ini</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($mejas as $meja)
                    <tr class="hover:bg-gray-50/60 transition-colors group">
                        <td class="px-4 py-3 text-xs text-gray-500 font-medium align-middle">
                            {{ $mejas->firstItem() + $loop->index }}
                        </td>
                        <td class="px-4 py-3 font-semibold text-gray-900">
                            {{ \Illuminate\Support\Str::startsWith(strtolower($meja->nomor_meja), 'meja') ? $meja->nomor_meja : 'Meja ' . $meja->nomor_meja }}
                        </td>
                        <td class="px-4 py-3 text-gray-600 font-medium">
                            {{ $meja->area ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-gray-600 font-medium">
                            {{ $meja->kapasitas }} Orang
                        </td>
                        <td class="px-4 py-3">
                            @if($meja->status_meja_id == 1)
                                <span class="inline-block text-xs font-semibold text-emerald-700 bg-emerald-50 rounded-md px-2.5 py-0.5">
                                    • Tersedia
                                </span>
                            @elseif($meja->status_meja_id == 2)
                                <span class="inline-block text-xs font-semibold text-rose-700 bg-rose-50 rounded-md px-2.5 py-0.5">
                                    • Terisi
                                </span>
                            @else
                                <span class="inline-block text-xs font-semibold text-gray-700 bg-gray-100 rounded-md px-2.5 py-0.5">
                                    • Tidak Aktif
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1.5 opacity-70 group-hover:opacity-100 transition-opacity">
                                {{-- Detail (QR) --}}
                                <button onclick="openQrDrawer({{ $meja->id }}, '{{ $meja->nomor_meja }}', '{{ $meja->kode_meja }}', {{ $meja->kapasitas }}, '{{ $meja->qr_token }}', '{{ $meja->area }}')" 
                                   title="Detail" class="w-7 h-7 rounded-lg flex items-center justify-center bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors">
                                    <x-heroicon-o-eye class="w-3 h-3" />
                                </button>

                                {{-- Edit (Update) --}}
                                <button onclick="openMejaModal({{ $meja->id }}, '{{ $meja->nomor_meja }}', {{ $meja->kapasitas }}, {{ $meja->status_meja_id ?? 1 }}, '{{ $meja->area }}')" 
                                        title="Ubah" class="w-7 h-7 rounded-lg flex items-center justify-center bg-amber-50 text-amber-600 hover:bg-amber-100 transition-colors">
                                    <x-heroicon-o-pencil-square class="w-3 h-3" />
                                </button>

                                {{-- Delete (Hapus) --}}
                                <form action="{{ route('meja.destroy', $meja->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus meja {{ addslashes($meja->nomor_meja) }}?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" title="Hapus" class="w-7 h-7 rounded-lg flex items-center justify-center bg-red-50 text-red-600 hover:bg-red-100 transition-colors">
                                        <x-heroicon-o-trash class="w-3 h-3" />
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-14 text-center text-gray-400">
                            <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            <p class="text-sm font-medium">Belum ada meja terdaftar.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            
            <div class="p-4 border-t border-gray-100 bg-white">
                {{ $mejas->links() }}
            </div>
        </div>
    </div>
</div>

{{-- DRAWER TAMBAH/EDIT MEJA --}}
<div id="modalMeja" class="fixed inset-0 z-[100] hidden">
    <div id="drawerOverlay" class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm opacity-0 transition-opacity duration-300" onclick="closeMejaModal()"></div>
    <div id="drawerContent" class="absolute top-0 right-0 h-full w-full max-w-md bg-white shadow-2xl flex flex-col translate-x-full transition-transform duration-300 ease-in-out">
        <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
            <div>
                <h3 class="text-lg font-bold text-gray-900" id="modalMejaTitle">Tambah Meja Baru</h3>
                <p class="text-xs text-gray-500 mt-1">Isi informasi detail meja yang akan ditambahkan</p>
            </div>
            <button onclick="closeMejaModal()" class="text-gray-400 hover:text-gray-900 bg-gray-50 hover:bg-gray-100 rounded-full p-2 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        
        <div class="flex-1 overflow-y-auto p-6">
            <form id="formMeja" method="POST" action="{{ route('meja.store') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="_method" id="formMejaMethod" value="POST">
                
                <div>
                    <label class="block text-xs font-semibold text-gray-900 mb-1.5">Nomor Meja <span class="text-red-500">*</span></label>
                    <input type="text" name="nomor_meja" id="inputNomorMeja" required class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 bg-white" placeholder="Contoh: 01, A1, VVIP-1">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-900 mb-1.5">Kapasitas (Orang) <span class="text-red-500">*</span></label>
                    <input type="number" name="kapasitas" id="inputKapasitas" min="1" value="4" required class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-900 mb-1.5">Area</label>
                    <input type="text" name="area" id="inputArea" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 bg-white" placeholder="Contoh: Indoor, Outdoor, VIP">
                </div>

                <div id="statusContainer" class="hidden">
                    <label class="block text-xs font-semibold text-gray-900 mb-1.5">Status <span class="text-red-500">*</span></label>
                    <select name="status_meja_id" id="inputStatus" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                        <option value="1">Tersedia</option>
                        <option value="2">Terisi</option>
                        <option value="4">Tidak Aktif</option>
                    </select>
                </div>
            </form>
        </div>
        
        <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-2 bg-gray-50/50">
            <button type="button" onclick="closeMejaModal()" class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">Batal</button>
            <button type="submit" form="formMeja" class="px-5 py-2.5 text-sm font-semibold text-white bg-gray-900 hover:bg-gray-800 rounded-xl transition-colors shadow-sm">Simpan Meja</button>
        </div>
    </div>
</div>

{{-- DRAWER LIHAT QR --}}
<div id="drawerQr" class="fixed inset-0 z-[100] hidden">
    <div id="drawerQrOverlay" class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm opacity-0 transition-opacity duration-300" onclick="closeQrDrawer()"></div>
    <div id="drawerQrContent" class="absolute top-0 right-0 h-full w-full max-w-md bg-white shadow-2xl flex flex-col translate-x-full transition-transform duration-300 ease-in-out">
        <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
            <div>
                <h3 class="text-lg font-bold text-gray-900" id="drawerQrTitle">QR Code Meja</h3>
                <p class="text-xs text-gray-500 mt-1">Scan untuk pemesanan mandiri oleh pelanggan</p>
            </div>
            <button onclick="closeQrDrawer()" class="text-gray-400 hover:text-gray-900 bg-gray-50 hover:bg-gray-100 rounded-full p-2 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        
        <div class="flex-1 overflow-y-auto p-6 flex flex-col items-center bg-gray-100">
            
            <!-- Detail Meja -->
            <div class="w-full max-w-[300px] mb-6 bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <h4 class="text-base font-bold text-gray-900 mb-3" id="detailNomorMeja">Meja 01</h4>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500">Kode Meja</span> <span class="font-medium text-gray-900" id="detailKodeMeja">-</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Kapasitas</span> <span class="font-medium text-gray-900" id="detailKapasitas">-</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Area</span> <span class="font-medium text-gray-900" id="detailArea">Indoor</span></div>
                </div>
                <div class="mt-4 pt-3 border-t border-gray-100" id="qrLinkContainer">
                    <span class="text-xs text-gray-500 block mb-1 font-semibold uppercase tracking-wider">URL QR Self Ordering</span>
                    <a href="#" id="detailQrLink" target="_blank" class="text-xs text-emerald-600 hover:text-emerald-700 font-medium break-all underline underline-offset-2">https://...</a>
                </div>
                <div class="mt-4 pt-3 border-t border-gray-100 hidden" id="noQrContainer">
                    <span class="text-xs text-amber-600 font-medium flex items-center gap-1.5">
                        <x-heroicon-o-exclamation-triangle class="w-4 h-4" /> QR Code belum digenerate.
                    </span>
                </div>
            </div>

            <!-- Kartu QR -->
            <div id="qrCardContainer" class="w-full max-w-[300px] aspect-[1/1.55] rounded-3xl overflow-hidden shadow-xl border-4 border-emerald-500/30 flex flex-col justify-between p-5 relative text-white" style="background: linear-gradient(145deg, #0F2E23 0%, #164032 50%, #0A2219 100%);">
                <div class="absolute inset-0 bg-gradient-to-b from-black/20 via-transparent to-black/40 pointer-events-none"></div>
                <div class="absolute top-3 left-3 w-4 h-4 border-t-2 border-l-2 border-amber-400/60 rounded-tl-lg"></div>
                <div class="absolute top-3 right-3 w-4 h-4 border-t-2 border-r-2 border-amber-400/60 rounded-tr-lg"></div>
                <div class="absolute bottom-3 left-3 w-4 h-4 border-b-2 border-l-2 border-amber-400/60 rounded-bl-lg"></div>
                <div class="absolute bottom-3 right-3 w-4 h-4 border-b-2 border-r-2 border-amber-400/60 rounded-br-lg"></div>

                <div class="relative z-10 text-center pt-1 space-y-0.5">
                    <h2 class="text-2xl font-black uppercase tracking-wider text-amber-400 drop-shadow-md leading-none">SCAN MENU</h2>
                    <div class="pt-2">
                        <span class="inline-flex items-center gap-1.5 px-3.5 py-0.5 rounded-full bg-white/15 backdrop-blur-md text-white border border-amber-400/40 text-[12px] font-extrabold shadow-sm">
                            <x-heroicon-o-users class="w-3 h-3 text-amber-400" /> <span id="qrMejaName">Meja</span>
                        </span>
                    </div>
                </div>

                <div class="relative z-10 my-auto py-1 flex flex-col items-center">
                    <div class="bg-white rounded-3xl p-3.5 shadow-2xl border-4 border-amber-400/50 relative flex items-center justify-center">
                        <img id="drawerQrImage" src="" alt="QR Code" class="w-44 h-44 object-contain rounded-xl">
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                            <div class="w-11 h-11 rounded-full bg-white p-1 shadow-xl border-2 border-emerald-800 flex items-center justify-center overflow-hidden">
                                <img src="{{ asset('images/logo-saung.png') }}" alt="Logo" class="w-full h-full object-contain">
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 text-center">
                        <p class="text-[11px] font-bold text-white tracking-wide">Scan QR Code untuk pesan sendiri</p>
                        <p class="text-[9px] font-medium text-amber-300 mt-0.5">Arahkan kamera HP Anda</p>
                    </div>
                </div>

                <div class="relative z-10 text-center pb-1 pt-1.5 border-t border-amber-400/30 flex items-center justify-center gap-2">
                    <div class="w-7 h-7 rounded-full bg-white/10 backdrop-blur-md p-1 flex items-center justify-center border border-amber-400/40 shrink-0">
                        <img src="{{ asset('images/logo-saung.png') }}" alt="Logo" class="w-full h-full object-contain">
                    </div>
                    <div class="text-left">
                        <h3 class="text-[11px] font-black tracking-wider text-white uppercase leading-none">SAUNG BABAKAN CINTA</h3>
                        <span class="text-[8px] font-semibold text-amber-300 block leading-tight mt-0.5">Rumah Makan Khas Sunda</span>
                    </div>
                </div>
            </div>
            <!-- Kartu QR Selesai -->
        </div>
        
        <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-2 bg-gray-50/50">
            <button type="button" onclick="closeQrDrawer()" class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">Tutup</button>
            
            <form id="formGenerateQr" method="POST" action="" class="hidden">
                @csrf
                <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-amber-600 hover:bg-amber-700 rounded-xl transition-colors shadow-sm flex items-center gap-1.5">
                    <x-heroicon-o-arrow-path class="w-4 h-4" /> Generate QR
                </button>
            </form>

            <div id="btnGroupQrActions" class="flex items-center gap-2 hidden">
                <button type="button" onclick="downloadQrPng()" class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors shadow-sm flex items-center gap-1.5 hidden" title="Fitur coming soon">
                    <x-heroicon-o-arrow-down-tray class="w-5 h-5" /> Unduh PNG
                </button>
                <a id="btnPrintQr" href="#" target="_blank" class="px-5 py-2.5 text-sm font-semibold text-white bg-emerald-700 hover:bg-emerald-800 rounded-xl transition-colors shadow-sm flex items-center gap-1.5">
                    <x-heroicon-o-printer class="w-5 h-5" /> Cetak QR
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function openMejaModal(id = null, nomor = '', kapasitas = 4, status = 1, area = 'Indoor') {
    const form = document.getElementById('formMeja');
    const title = document.getElementById('modalMejaTitle');
    const methodInput = document.getElementById('formMejaMethod');
    const statusContainer = document.getElementById('statusContainer');
    
    document.getElementById('inputNomorMeja').value = nomor;
    document.getElementById('inputKapasitas').value = kapasitas;
    document.getElementById('inputStatus').value = status;
    document.getElementById('inputArea').value = area || 'Indoor';
    
    if(id) {
        title.innerText = 'Edit Meja';
        form.action = `/meja/${id}`;
        methodInput.value = 'PUT';
        statusContainer.classList.remove('hidden');
    } else {
        title.innerText = 'Tambah Meja';
        form.action = '{{ route('meja.store') }}';
        methodInput.value = 'POST';
        statusContainer.classList.add('hidden');
    }
    
    document.getElementById('modalMeja').classList.remove('hidden');
    // Animate opening
    setTimeout(() => {
        document.getElementById('drawerOverlay').classList.remove('opacity-0');
        document.getElementById('drawerContent').classList.remove('translate-x-full');
    }, 10);
}

function closeMejaModal() {
    // Animate closing
    document.getElementById('drawerOverlay').classList.add('opacity-0');
    document.getElementById('drawerContent').classList.add('translate-x-full');
    
    setTimeout(() => {
        document.getElementById('modalMeja').classList.add('hidden');
    }, 300);
}

function openQrDrawer(id, nomor_meja, kode_meja, kapasitas, qr_token, area) {
    const appUrl = '{{ url('/') }}';
    
    // Set Detail Data
    const cleanNomor = nomor_meja.replace(/^meja\s*/i, '');
    document.getElementById('detailNomorMeja').innerText = 'Meja ' + cleanNomor;
    document.getElementById('detailKodeMeja').innerText = kode_meja || '-';
    document.getElementById('detailKapasitas').innerText = kapasitas + ' Orang';
    document.getElementById('detailArea').innerText = area || 'Indoor';
    
    const linkContainer = document.getElementById('qrLinkContainer');
    const noQrContainer = document.getElementById('noQrContainer');
    const qrCardContainer = document.getElementById('qrCardContainer');
    const formGenerate = document.getElementById('formGenerateQr');
    const btnGroupQr = document.getElementById('btnGroupQrActions');

    if (qr_token) {
        // QR exists
        const qrUrl = appUrl + '/qr-menu/' + qr_token;
        const apiUrl = `https://api.qrserver.com/v1/create-qr-code/?size=350x350&margin=0&data=${encodeURIComponent(qrUrl)}`;
        
        document.getElementById('detailQrLink').href = qrUrl;
        document.getElementById('detailQrLink').innerText = qrUrl;
        
        document.getElementById('drawerQrImage').src = apiUrl;
        document.getElementById('qrMejaName').innerText = 'Meja ' + cleanNomor;
        // Ganti parameter cetak qr menggunakan token atau tetep id
        document.getElementById('btnPrintQr').href = '{{ url("pos/dinein/qr-codes") }}?meja_id=' + id;
        
        linkContainer.classList.remove('hidden');
        noQrContainer.classList.add('hidden');
        qrCardContainer.classList.remove('hidden');
        btnGroupQr.classList.remove('hidden');
        formGenerate.classList.add('hidden');
    } else {
        // No QR
        linkContainer.classList.add('hidden');
        noQrContainer.classList.remove('hidden');
        qrCardContainer.classList.add('hidden');
        btnGroupQr.classList.add('hidden');
        
        formGenerate.classList.remove('hidden');
        formGenerate.action = `/meja/${id}/generate-qr`;
    }
    
    document.getElementById('drawerQr').classList.remove('hidden');
    setTimeout(() => {
        document.getElementById('drawerQrOverlay').classList.remove('opacity-0');
        document.getElementById('drawerQrContent').classList.remove('translate-x-full');
    }, 10);
}

function closeQrDrawer() {
    document.getElementById('drawerQrOverlay').classList.add('opacity-0');
    document.getElementById('drawerQrContent').classList.add('translate-x-full');
    
    setTimeout(() => {
        document.getElementById('drawerQr').classList.add('hidden');
    }, 300);
}
</script>
@endsection
