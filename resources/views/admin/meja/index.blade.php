@extends('layouts.pos')

@section('content')
<div class="flex-1 overflow-auto bg-gray-50 text-gray-800">
    <div class="w-full px-4 md:px-6 py-6 space-y-5">

        {{-- PAGE HEADER --}}
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-lg font-bold text-gray-900">Manajemen Meja</h1>
                <p class="text-xs text-gray-500 mt-0.5">Kelola data meja restoran, kapasitas, dan pantau statusnya.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('pos.dinein.print-qr') }}" target="_blank" class="inline-flex items-center gap-1.5 text-sm font-semibold text-[#0F2E23] bg-emerald-50 border border-emerald-200 rounded-lg px-3 py-2 hover:bg-emerald-100 transition-colors">
                    <i class="fa-solid fa-qrcode"></i>
                    Cetak QR Meja
                </a>
                <button onclick="openMejaModal()" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-gray-900 rounded-lg px-3 py-2 hover:bg-gray-800 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Meja Baru
                </button>
            </div>
        </div>

        <x-ui.alert />

        {{-- Filter / Info Bar --}}
        <div class="flex flex-col sm:flex-row gap-2 items-start sm:items-center justify-between mb-3 shrink-0">
            <div class="flex items-center gap-2">
                <span class="text-xs font-medium text-gray-500">Total Meja Terdaftar:</span>
                <span class="text-xs font-semibold text-gray-900 bg-gray-100 rounded-full px-2.5 py-0.5">{{ count($mejas) }}</span>
            </div>
        </div>

        {{-- Table Container --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-xs font-semibold text-gray-400 uppercase tracking-wide">
                        <th class="px-4 py-3 text-left w-12">No.</th>
                        <th class="px-4 py-3 text-left">Nomor Meja</th>
                        <th class="px-4 py-3 text-left">Kapasitas</th>
                        <th class="px-4 py-3 text-left">Status Saat Ini</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($mejas as $meja)
                    <tr class="hover:bg-gray-50/60 transition-colors group">
                        <td class="px-4 py-3 text-xs text-gray-500 font-medium align-middle">
                            {{ $loop->iteration }}
                        </td>
                        <td class="px-4 py-3 font-semibold text-gray-900">
                            {{ \Illuminate\Support\Str::startsWith(strtolower($meja->nomor_meja), 'meja') ? $meja->nomor_meja : 'Meja ' . $meja->nomor_meja }}
                        </td>
                        <td class="px-4 py-3 text-gray-600 font-medium">
                            {{ $meja->kapasitas }} Orang
                        </td>
                        <td class="px-4 py-3">
                            @if($meja->status == 'kosong')
                                <span class="inline-block text-xs font-semibold text-emerald-700 bg-emerald-50 rounded-md px-2.5 py-0.5">
                                    • Kosong
                                </span>
                            @elseif($meja->status == 'terisi')
                                <span class="inline-block text-xs font-semibold text-rose-700 bg-rose-50 rounded-md px-2.5 py-0.5">
                                    • Terisi
                                </span>
                            @else
                                <span class="inline-block text-xs font-semibold text-amber-700 bg-amber-50 rounded-md px-2.5 py-0.5">
                                    • Menunggu Pembayaran
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1 opacity-70 group-hover:opacity-100 transition-opacity">
                                {{-- Edit (Update) --}}
                                <button onclick="openMejaModal({{ $meja->id }}, '{{ $meja->nomor_meja }}', {{ $meja->kapasitas }}, '{{ $meja->status }}')" 
                                        class="p-1.5 rounded-md text-gray-500 hover:bg-blue-50 hover:text-blue-600 transition-colors" title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>

                                {{-- Delete (Hapus) --}}
                                <form action="{{ route('meja.destroy', $meja->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus meja {{ addslashes($meja->nomor_meja) }}?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-md text-gray-500 hover:bg-red-50 hover:text-red-600 transition-colors" title="Hapus">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-14 text-center text-gray-400">
                            <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            <p class="text-sm font-medium">Belum ada meja terdaftar.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL TAMBAH/EDIT MEJA --}}
<div id="modalMeja" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm" onclick="closeMejaModal()"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white rounded-2xl shadow-xl overflow-hidden flex flex-col max-h-[90vh]">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="text-lg font-bold text-gray-900" id="modalMejaTitle">Tambah Meja</h3>
            <button onclick="closeMejaModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="p-6 overflow-y-auto">
            <form id="formMeja" method="POST" action="{{ route('meja.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="_method" id="formMejaMethod" value="POST">
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nomor Meja <span class="text-red-500">*</span></label>
                    <input type="text" name="nomor_meja" id="inputNomorMeja" required class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 bg-gray-50/50" placeholder="Contoh: 01, A1, VVIP-1">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Kapasitas (Orang) <span class="text-red-500">*</span></label>
                    <input type="number" name="kapasitas" id="inputKapasitas" min="1" value="4" required class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 bg-gray-50/50">
                </div>

                <div id="statusContainer" class="hidden">
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Status</label>
                    <select name="status" id="inputStatus" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 bg-gray-50/50">
                        <option value="kosong">Kosong</option>
                        <option value="terisi">Terisi</option>
                        <option value="menunggu_pembayaran">Menunggu Pembayaran</option>
                    </select>
                </div>

                <div class="pt-4 flex justify-end gap-2">
                    <button type="button" onclick="closeMejaModal()" class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-gray-900 rounded-lg hover:bg-gray-800 transition-colors">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openMejaModal(id = null, nomor = '', kapasitas = 4, status = 'kosong') {
    const form = document.getElementById('formMeja');
    const title = document.getElementById('modalMejaTitle');
    const methodInput = document.getElementById('formMejaMethod');
    const statusContainer = document.getElementById('statusContainer');
    
    document.getElementById('inputNomorMeja').value = nomor;
    document.getElementById('inputKapasitas').value = kapasitas;
    document.getElementById('inputStatus').value = status;
    
    if(id) {
        title.innerText = 'Edit Meja';
        form.action = `/admin/meja/${id}`;
        methodInput.value = 'PUT';
        statusContainer.classList.remove('hidden');
    } else {
        title.innerText = 'Tambah Meja';
        form.action = '{{ route('meja.store') }}';
        methodInput.value = 'POST';
        statusContainer.classList.add('hidden');
    }
    
    document.getElementById('modalMeja').classList.remove('hidden');
}

function closeMejaModal() {
    document.getElementById('modalMeja').classList.add('hidden');
}
</script>
@endsection
