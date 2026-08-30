@extends('layouts.pos')

@push('scripts')
<script src="{{ asset('js/qrcode.min.js') }}"></script>
@endpush

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800 pb-10">
    <div class="w-full p-6 space-y-5">

        {{-- PAGE HEADER --}}
        <x-ui.page-header title="Manajemen Meja" subtitle="Kelola data meja restoran, kapasitas, dan pantau statusnya." :breadcrumbs="['Meja', 'Data Meja']">
            <x-slot:actions>
                <x-ui.button variant="primary" icon="plus" onclick="openMejaModal()">
                    Meja Baru
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.alert />

        {{-- Table --}}
        <x-ui.data-table :paginator="$mejas">
            <x-slot:toolbar>
                <div class="flex flex-col xl:flex-row items-start xl:items-center justify-between gap-4 w-full">
                    <form action="{{ route('meja.index') }}" method="GET" class="flex items-center gap-2 w-full sm:w-auto">
                        <x-search-input name="search" value="{{ request('search') }}" placeholder="Cari nomor meja…" width="w-full sm:w-56" />
                    </form>

                    <div class="flex items-center gap-2">
                        <a href="{{ url('pos/dinein/qr-codes') }}" target="_blank" class="inline-flex items-center gap-1.5 text-sm font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg px-3 py-2 hover:bg-emerald-100 transition-colors shadow-sm">
                            <x-heroicon-o-document class="w-4 h-4" />
                            Unduh Semua QR
                        </a>
                    </div>
                </div>
            </x-slot:toolbar>

            <x-ui.table class="min-w-[800px]">
                <x-ui.table.header>
                    <th class="px-4 py-3.5 text-left w-12">No.</th>
                    <th class="px-4 py-3.5 text-left">Nomor Meja</th>
                    <th class="px-4 py-3.5 text-left">Area</th>
                    <th class="px-4 py-3.5 text-left">Kapasitas</th>
                    <th class="px-4 py-3.5 text-left">Status Saat Ini</th>
                    <th class="px-4 py-3.5 text-center">Aksi</th>
                </x-ui.table.header>
                <tbody class="divide-y divide-gray-100">
                    @forelse($mejas as $meja)
                    <x-ui.table.row>
                        <td class="px-4 py-4 text-sm text-gray-500 font-medium align-middle">
                            {{ $mejas->firstItem() + $loop->index }}
                        </td>
                        <td class="px-4 py-4 align-middle font-semibold text-gray-900">
                            {{ \Illuminate\Support\Str::startsWith(strtolower($meja->nomor_meja), 'meja') ? $meja->nomor_meja : 'Meja ' . $meja->nomor_meja }}
                        </td>
                        <td class="px-4 py-4 align-middle text-gray-600 font-medium">
                            {{ $meja->area ?? '-' }}
                        </td>
                        <td class="px-4 py-4 align-middle text-gray-600 font-medium">
                            {{ $meja->kapasitas }} Orang
                        </td>
                        <td class="px-4 py-4 align-middle">
                            @php
                                $sColor = 'gray';
                                if($meja->status_meja_id == 1) $sColor = 'success'; // Tersedia
                                elseif($meja->status_meja_id == 2) $sColor = 'danger'; // Terisi
                            @endphp
                            <x-ui.badge :color="$sColor" size="sm" dot>
                                {{ $meja->status_meja_id == 1 ? 'Tersedia' : ($meja->status_meja_id == 2 ? 'Terisi' : 'Tidak Aktif') }}
                            </x-ui.badge>
                        </td>
                        <td class="px-4 py-4 align-middle text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                {{-- Detail (QR) --}}
                                <x-ui.action-button onclick="openQrDrawer({{ $meja->id }}, '{{ $meja->nomor_meja }}', '{{ $meja->kode_meja }}', {{ $meja->kapasitas }}, '{{ $meja->qr_token }}', '{{ $meja->area }}')" title="Detail" label="Detail">
                                    <x-heroicon-o-eye class="w-3.5 h-3.5" />
                                </x-ui.action-button>

                                {{-- Edit (Update) --}}
                                <x-ui.action-button onclick="openMejaModal({{ $meja->id }}, '{{ $meja->nomor_meja }}', {{ $meja->kapasitas }}, {{ $meja->status_meja_id ?? 1 }}, '{{ $meja->area }}')" title="Ubah" label="Edit">
                                    <x-heroicon-o-pencil-square class="w-3.5 h-3.5" />
                                </x-ui.action-button>

                                {{-- Delete (Hapus) --}}
                                <form id="delete-meja-{{ $meja->id }}" action="{{ route('meja.destroy', $meja->id) }}" method="POST" class="inline">
                                    @csrf @method('DELETE')
                                    <x-ui.action-button onclick="window.confirmDialog({ title: 'Hapus Meja', name: 'Meja {{ addslashes($meja->nomor_meja) }}', message: 'Data yang dihapus tidak dapat dikembalikan.', formId: 'delete-meja-{{ $meja->id }}', confirmText: 'Hapus', cancelText: 'Batal' })" title="Hapus" label="Hapus">
                                        <x-heroicon-o-trash class="w-3.5 h-3.5" />
                                    </x-ui.action-button>
                                </form>
                            </div>
                        </td>
                    </x-ui.table.row>
                    @empty
                    <tr>
                        <td colspan="6">
                            <x-ui.empty-state icon="clipboard" title="Belum ada meja terdaftar." message="Tambahkan meja baru menggunakan tombol di atas." />
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </x-ui.table>
        </x-ui.data-table>
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
                    <x-ui.input name="nomor_meja" id="inputNomorMeja" label="Nomor Meja *" required placeholder="Contoh: 1, A1, VVIP-1" />
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-1.5">Kapasitas (Orang) <span class="text-red-500">*</span></label>
                    <x-ui.input-qty id="inputKapasitas" name="kapasitas" value="4" min="1" :required="true" />
                </div>

                <div>
                    <x-ui.input name="area" id="inputArea" label="Area" placeholder="Contoh: Indoor, Outdoor, VIP" />
                </div>

                <div id="statusContainer" class="hidden">
                    <label class="block text-sm font-semibold text-gray-900 mb-1.5">Status <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <select name="status_meja_id" id="inputStatus" class="w-full appearance-none border border-gray-200 rounded-xl px-4 py-2.5 pr-10 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white shadow-xs cursor-pointer">
                            <option value="1">Tersedia</option>
                            <option value="2">Terisi</option>
                            <option value="4">Tidak Aktif</option>
                        </select>
                        <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400">
                            <x-heroicon-o-chevron-down class="w-4 h-4" />
                        </span>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-2 bg-gray-50/50">
            <x-ui.button type="button" variant="secondary" onclick="closeMejaModal()">Batal</x-ui.button>
            <x-ui.button type="submit" variant="primary" form="formMeja">Simpan Meja</x-ui.button>
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
                <h4 class="text-base font-bold text-gray-900 mb-3" id="detailNomorMeja">Meja 1</h4>
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
            <div id="qrCardContainer" class="w-full max-w-[300px] aspect-[1/1.55] rounded-xl overflow-hidden shadow-xl border-4 border-emerald-500/30 flex flex-col justify-between p-5 relative text-white" style="background: linear-gradient(145deg, #0D3024 0%, #164032 50%, #0A2219 100%);">
                <div class="absolute inset-0 bg-gradient-to-b from-black/20 via-transparent to-black/40 pointer-events-none"></div>
                <div class="absolute top-3 left-3 w-4 h-4 border-t-2 border-l-2 border-amber-400/60 rounded-tl-3xl"></div>
                <div class="absolute top-3 right-3 w-4 h-4 border-t-2 border-r-2 border-amber-400/60 rounded-tr-3xl"></div>
                <div class="absolute bottom-3 left-3 w-4 h-4 border-b-2 border-l-2 border-amber-400/60 rounded-bl-3xl"></div>
                <div class="absolute bottom-3 right-3 w-4 h-4 border-b-2 border-r-2 border-amber-400/60 rounded-br-3xl"></div>

                <div class="relative z-10 text-center pt-1 space-y-0.5">
                    <h2 class="text-2xl font-black uppercase tracking-wider text-amber-400 drop-shadow-md leading-none">SCAN MENU</h2>
                    <div class="pt-2">
                        <span class="inline-flex items-center gap-1.5 px-3.5 py-0.5 rounded-full bg-white/15 backdrop-blur-md text-white border border-amber-400/40 text-xs font-extrabold shadow-sm">
                            <x-heroicon-o-users class="w-3 h-3 text-amber-400" /> <span id="qrMejaName">Meja</span>
                        </span>
                    </div>
                </div>

                <div class="relative z-10 my-auto py-1 flex flex-col items-center">
                    <div class="bg-white rounded-xl p-3.5 shadow-2xl border-4 border-amber-400/50 relative flex items-center justify-center">
                        <div id="drawerQrCanvas" class="w-44 h-44 flex items-center justify-center"></div>
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                            <div class="w-11 h-11 rounded-full bg-white p-1 shadow-xl border-2 border-emerald-800 flex items-center justify-center overflow-hidden">
                                <img src="{{ asset('images/logo-saung.png') }}" alt="Logo" class="w-full h-full object-contain">
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 text-center">
                        <p class="text-xs font-bold text-white tracking-wide">Scan QR Code untuk pesan sendiri</p>
                        <p class="text-xs font-medium text-amber-300 mt-0.5">Arahkan kamera HP Anda</p>
                    </div>
                </div>

                <div class="relative z-10 text-center pb-1 pt-1.5 border-t border-amber-400/30 flex items-center justify-center gap-2">
                    <div class="w-7 h-7 rounded-full bg-white/10 backdrop-blur-md p-1 flex items-center justify-center border border-amber-400/40 shrink-0">
                        <img src="{{ asset('images/logo-saung.png') }}" alt="Logo" class="w-full h-full object-contain">
                    </div>
                    <div class="text-left">
                        <h3 class="text-xs font-black tracking-wider text-white uppercase leading-none">SAUNG BABAKAN CINTA</h3>
                        <span class="text-xs font-semibold text-amber-300 block leading-tight mt-0.5">Rumah Makan Khas Sunda</span>
                    </div>
                </div>
            </div>
            <!-- Kartu QR Selesai -->
        </div>
        
        <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-2 bg-gray-50/50">
            <x-ui.button type="button" variant="secondary" onclick="closeQrDrawer()">Tutup</x-ui.button>
            
            <form id="formGenerateQr" method="POST" action="" class="hidden">
                @csrf
                <x-ui.button type="submit" variant="primary" icon="arrow-path">
                    Generate QR
                </x-ui.button>
            </form>

            <div id="btnGroupQrActions" class="flex items-center gap-2 hidden">
                <x-ui.button type="button" variant="secondary" icon="arrow-down-tray" onclick="downloadQrPng()" title="Unduh QR sebagai gambar PNG">
                    Unduh PNG
                </x-ui.button>
                <x-ui.button href="#" id="btnPrintQr" target="_blank" variant="primary" icon="printer">
                    Cetak QR
                </x-ui.button>
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
    document.getElementById('inputKapasitas').dispatchEvent(new Event('input', { bubbles: true }));
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
    const appUrl = '{{ \App\Helpers\IdCodeGenerator::getLanBaseUrl() }}';
    
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
        
        document.getElementById('detailQrLink').href = qrUrl;
        document.getElementById('detailQrLink').innerText = qrUrl;
        
        const qrContainer = document.getElementById('drawerQrCanvas');
        qrContainer.innerHTML = '';
        new QRCode(qrContainer, {
            text: qrUrl,
            width: 176,
            height: 176,
            colorDark: '#0D3024',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.M
        });
        
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

function downloadQrPng() {
    const qrContainer = document.getElementById('drawerQrCanvas');
    const canvas = qrContainer ? qrContainer.querySelector('canvas') : null;
    const img = qrContainer ? qrContainer.querySelector('img') : null;
    
    const mejaName = document.getElementById('qrMejaName')?.innerText || 'Meja';
    const filename = 'QR_' + mejaName.replace(/\s+/g, '_') + '.png';

    if (canvas) {
        const link = document.createElement('a');
        link.download = filename;
        link.href = canvas.toDataURL('image/png');
        link.click();
    } else if (img && img.src) {
        const link = document.createElement('a');
        link.download = filename;
        link.href = img.src;
        link.click();
    } else {
        window.showToast('warning', 'QR Code belum siap. Coba buka drawer meja terlebih dahulu.');
    }
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
