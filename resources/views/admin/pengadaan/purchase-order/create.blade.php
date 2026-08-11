@extends('layouts.pos')
@section('title', 'Buat Purchase Order')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800 pb-10">
    <div class="w-full p-6 space-y-5">

        <x-ui.page-header
            title="Buat Purchase Order"
            subtitle="Buat pesanan pembelian berdasarkan sisa kebutuhan permintaan."
            :breadcrumbs="['Pengadaan', 'Semua Permintaan', 'Buat Purchase Order']">
        </x-ui.page-header>

        <x-ui.alert />

        <form action="{{ route('pengadaan.po.store', $pengadaan->id) }}" method="POST" id="poForm" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-5">
                <div class="lg:col-span-1 space-y-5">
                    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                        <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                            <h3 class="font-bold text-gray-900 text-sm tracking-tight">Informasi PO</h3>
                        </div>
                        <div class="p-4 space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1">Kode PO</label>
                                <input type="text" readonly value="{{ $kodePo }}" class="w-full bg-gray-50 border border-gray-200 text-gray-600 text-sm rounded-lg px-3 py-2 font-mono font-bold cursor-not-allowed">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1">Kode Permintaan</label>
                                <input type="text" readonly value="{{ $pengadaan->id_pengadaan }}" class="w-full bg-gray-50 border border-gray-200 text-gray-600 text-sm rounded-lg px-3 py-2 font-mono font-bold cursor-not-allowed">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1">Tanggal PO</label>
                                <input type="date" name="tanggal_po" value="{{ date('Y-m-d') }}" required class="w-full border border-gray-200 text-gray-900 text-sm rounded-lg px-3 py-2 outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1">Supplier / Toko <span class="text-rose-500">*</span></label>
                                <input type="text" name="supplier" required placeholder="Contoh: Toko Sumber Makmur" class="w-full border border-gray-200 text-gray-900 text-sm rounded-lg px-3 py-2 outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1">Catatan</label>
                                <textarea name="catatan" rows="3" placeholder="Catatan untuk supplier atau pengadaan ini..." class="w-full border border-gray-200 text-gray-900 text-sm rounded-lg px-3 py-2 outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-3">
                    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                        <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 flex justify-between items-center">
                            <h3 class="font-bold text-gray-900 text-sm tracking-tight">Daftar Bahan Untuk PO ({{ $items->count() }})</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wide bg-white">
                                        <th class="px-4 py-3 text-center w-12">Pilih</th>
                                        <th class="px-4 py-3 text-left">Bahan Baku</th>
                                        <th class="px-4 py-3 text-right">Jumlah Diminta</th>
                                        <th class="px-4 py-3 text-right">Jumlah Diterima</th>
                                        <th class="px-4 py-3 text-right">Sisa Kebutuhan</th>
                                        <th class="px-4 py-3 text-right w-32">Jumlah Dipesan</th>
                                        <th class="px-4 py-3 text-left">Satuan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    @forelse($items as $idx => $row)
                                    <tr class="item-row">
                                        <td class="px-4 py-3 text-center align-middle">
                                            <input type="checkbox" name="item_checked[{{ $row['detail_id'] }}]" value="1" class="item-checkbox rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer" checked onchange="toggleRowInputs(this, '{{ $row['detail_id'] }}')">
                                        </td>
                                        <td class="px-4 py-3 align-middle">
                                            <p class="font-bold text-gray-900 text-sm">{{ $row['nama_bahan'] }}</p>
                                        </td>
                                        <td class="px-4 py-3 text-right align-middle text-gray-700">{{ $row['jumlah_diminta'] }}</td>
                                        <td class="px-4 py-3 text-right align-middle text-gray-600">{{ $row['jumlah_diterima'] }}</td>
                                        <td class="px-4 py-3 text-right align-middle font-bold text-amber-600">{{ $row['sisa'] }} <span class="text-xs font-normal text-gray-500">{{ $row['satuan'] }}</span></td>
                                        <td class="px-4 py-3 align-middle">
                                                <input type="text" name="jumlah_pesanan[{{ $row['detail_id'] }}]" id="jml_{{ $row['detail_id'] }}" value="{{ $row['sisa'] }}" class="w-full text-right border border-gray-200 text-gray-900 text-sm rounded-lg px-2 py-1.5 outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 disabled:bg-gray-100 disabled:text-gray-400" required>
                                        </td>
                                        <td class="px-4 py-3 align-middle">
                                            <span class="text-xs font-semibold text-gray-500 bg-gray-100 px-2 py-1 rounded-md">{{ $row['satuan'] ?? '-' }}</span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-10 text-center text-gray-500 text-sm">Semua kebutuhan pada permintaan ini sudah terpenuhi.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="px-4 py-3 border-t border-gray-200 bg-gray-50 flex items-center justify-between">
                            <span class="text-sm text-gray-500" id="pageInfo">Menampilkan 0 dari 0 bahan</span>
                            <div class="flex items-center gap-1" id="formPagination"></div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 justify-end mt-4">
                        <a href="{{ route('pengadaan.permintaan.show', $pengadaan) }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-gray-700 bg-gray-100 rounded-lg px-4 py-2 hover:bg-gray-200 transition-colors">
                            Batal
                        </a>
                        <button type="submit" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-emerald-600 rounded-lg px-4 py-2 hover:bg-emerald-700 transition-colors" onclick="return validateForm(this)">
                            <x-heroicon-o-check class="w-4 h-4" />
                            Simpan & Buat PO
                        </button>
                    </div>
                </div>
            </div>
        </form>

    </div>
</div>

@push('scripts')
<script>
    function toggleRowInputs(checkbox, id) {
        const input = document.getElementById('jml_' + id);
        if (input) input.disabled = !checkbox.checked;
    }
    function validateForm(btn) {
        if (btn.form && !btn.form.checkValidity()) {
            btn.form.reportValidity();
            return false;
        }
        
        const checked = document.querySelectorAll('.item-checkbox:checked');
        if (checked.length === 0) {
            alert('Pilih minimal satu bahan untuk dipesan.');
            return false;
        }
        setTimeout(() => {
            btn.disabled = true;
            btn.textContent = 'Menyimpan...';
        }, 50);
        return true;
    }

    // Pagination Logic for Form
    let currentPage = 1;
    const itemsPerPage = 7; // Number of rows per page
    let totalRows = [];

    document.addEventListener('DOMContentLoaded', () => {
        totalRows = document.querySelectorAll('.item-row');
        if(totalRows.length > 0) {
            showPage(1);
        } else {
            document.getElementById('pageInfo').style.display = 'none';
        }
    });

    function showPage(page) {
        const totalPages = Math.ceil(totalRows.length / itemsPerPage);
        if (page < 1) page = 1;
        if (page > totalPages) page = totalPages;
        currentPage = page;

        const start = (page - 1) * itemsPerPage;
        const end = start + itemsPerPage;

        totalRows.forEach((row, index) => {
            row.style.display = (index >= start && index < end) ? '' : 'none';
        });

        // Update info text
        const actualEnd = Math.min(end, totalRows.length);
        document.getElementById('pageInfo').innerText = `Menampilkan ${start + 1}-${actualEnd} dari ${totalRows.length} bahan`;

        renderPaginationControls(totalPages);
    }

    function renderPaginationControls(totalPages) {
        if (totalPages <= 1) {
            document.getElementById('formPagination').innerHTML = '';
            return;
        }
        
        let html = '';
        
        // Prev button
        html += `<button type="button" onclick="showPage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''} class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
        </button>`;

        // Page numbers
        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, currentPage + 2);
        
        if (startPage > 1) {
            html += `<button type="button" onclick="showPage(1)" class="w-8 h-8 flex items-center justify-center rounded-lg text-sm font-semibold transition-colors bg-white border border-gray-200 text-gray-700 hover:bg-gray-50">1</button>`;
            if (startPage > 2) html += `<span class="px-1 text-gray-400">...</span>`;
        }

        for(let i = startPage; i <= endPage; i++) {
            html += `<button type="button" onclick="showPage(${i})" class="w-8 h-8 flex items-center justify-center rounded-lg text-sm font-semibold transition-colors border ${i === currentPage ? 'bg-emerald-600 border-emerald-600 text-white' : 'bg-white border-gray-200 text-gray-700 hover:bg-gray-50'}">${i}</button>`;
        }
        
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) html += `<span class="px-1 text-gray-400">...</span>`;
            html += `<button type="button" onclick="showPage(${totalPages})" class="w-8 h-8 flex items-center justify-center rounded-lg text-sm font-semibold transition-colors bg-white border border-gray-200 text-gray-700 hover:bg-gray-50">${totalPages}</button>`;
        }

        // Next button
        html += `<button type="button" onclick="showPage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''} class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
        </button>`;

        document.getElementById('formPagination').innerHTML = html;
    }
</script>
@endpush
@endsection