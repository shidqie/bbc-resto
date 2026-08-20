@extends('layouts.pos')
@section('title', 'Buat Purchase Order')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800 pb-12">
    <div class="w-full p-6 space-y-6">
        
        {{-- PAGE HEADER --}}
        <x-ui.page-header
            title="BUAT PURCHASE ORDER (PO)"
            subtitle="Buat pesanan bahan baku kepada supplier."
            :breadcrumbs="['Pengadaan', 'Purchase Order', 'Buat PO']">
            <x-slot:actions>
                <x-ui.button variant="secondary" href="{{ route('pengadaan.po.index') }}">
                    Batal
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.alert />

        <form action="{{ route('pengadaan.po.store-unified') }}" method="POST" id="poForm" class="space-y-6">
            @csrf
            <input type="hidden" name="tipe" value="{{ $tipe ?? 'Operasional' }}">
            @if(isset($pesanan))
                <input type="hidden" name="pesanan_id" value="{{ $pesanan->id }}">
            @endif

            {{-- CARD 1: INFORMASI PO --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200/80 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                    <h3 class="text-xs font-extrabold text-gray-700 uppercase tracking-wider">INFORMASI PO</h3>
                    <span class="text-xs text-gray-400 font-semibold">RM BBC Resto</span>
                </div>
                <div class="p-6 space-y-5">
                    {{-- Row 1: No. PO (Full Width) --}}
                    <div>
                        <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-2">No. PO</label>
                        <input type="text" name="nomor_po" value="{{ $kodePo }}" readonly class="w-full bg-gray-100/80 border border-gray-200 rounded-xl text-gray-700 font-medium text-sm px-4 py-2.5 cursor-not-allowed font-semibold">
                        <p class="text-[11px] text-gray-400 mt-1">Dibuat otomatis oleh sistem</p>
                    </div>

                    {{-- Row 2: Supplier & Tanggal PO/Kebutuhan --}}
                    @if($tipe === 'Catering' || $tipe === 'Katering')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-5 mb-5">
                        <div>
                            <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-2">Pesanan Katering <span class="text-red-500">*</span></label>
                            <select name="kode_pesanan" onchange="window.location.href='?tipe=Catering&kode_pesanan=' + this.value" class="block w-full rounded-xl border border-gray-200 focus:border-primary/20 focus:ring-primary/20 bg-white text-sm px-4 py-2.5 transition-all font-medium outline-none">
                                <option value="">— Pilih Pesanan Katering —</option>
                                @foreach($pesananKatering as $pk)
                                    <option value="{{ $pk->id_pesanan }}" {{ (request('kode_pesanan') == $pk->id_pesanan) ? 'selected' : '' }}>
                                        {{ $pk->id_pesanan }} - {{ $pk->pelanggan->nama_pelanggan ?? 'Umum' }} ({{ \Carbon\Carbon::parse($pk->waktu_pesanan)->format('d/m/Y') }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-[11px] text-gray-400 mt-1">Pilih pesanan untuk menghitung kebutuhan bahan</p>
                        </div>
                    </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-x-8 gap-y-5 mb-5">
                        <div>
                            <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-2">Supplier / Toko <span class="text-red-500">*</span></label>
                            <input type="text" name="supplier_nama" id="supplier_nama" value="{{ old('supplier_nama') }}" placeholder="Masukkan nama supplier / toko..." class="block w-full rounded-xl border {{ $errors->has('supplier_nama') ? 'border-red-400 focus:border-red-500 focus:ring-red-500/20' : 'border-gray-200 focus:border-primary/20 focus:ring-primary/20' }} bg-white text-sm px-4 py-2.5 transition-all duration-150 outline-none font-medium" required>
                            @error('supplier_nama')
                                <p class="text-xs font-medium text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-2">Tanggal PO <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_po" value="{{ old('tanggal_po', date('Y-m-d')) }}" required class="block w-full rounded-xl border border-gray-200 focus:border-primary/20 focus:ring-2 focus:ring-primary/20 text-sm px-4 py-2.5 transition-all outline-none font-medium">
                        </div>

                        <div>
                            <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-2">Tanggal Kebutuhan <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_kebutuhan" value="{{ old('tanggal_kebutuhan', date('Y-m-d', strtotime('+1 day'))) }}" required class="block w-full rounded-xl border border-gray-200 focus:border-primary/20 focus:ring-2 focus:ring-primary/20 text-sm px-4 py-2.5 transition-all outline-none font-medium">
                        </div>
                    </div>

                    {{-- Row 4: Catatan PO (Full Width) --}}
                    <div>
                        <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-2">Catatan PO</label>
                        <input type="text" name="catatan" value="{{ old('catatan') }}" placeholder="Tambahkan catatan jika diperlukan..." class="block w-full rounded-xl border border-gray-200 focus:border-primary/20 focus:ring-2 focus:ring-primary/20 text-sm px-4 py-2.5 transition-all outline-none font-medium">
                    </div>
                </div>
            </div>

            {{-- CARD 2: DAFTAR BAHAN BAKU --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200/80 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <h3 class="text-xs font-extrabold text-gray-700 uppercase tracking-wider">DAFTAR BAHAN BAKU</h3>
                        <span class="text-xs text-gray-500 font-bold px-2.5 py-0.5 bg-gray-200/60 rounded-md" id="itemCountBadge">{{ count($items) }} item</span>
                    </div>

                    {{-- Unified Toolbar: Search & Combobox --}}
                    <div class="flex items-center gap-2">
                        <div class="relative" x-data="{
                            open: false,
                            search: '',
                            items: [
                                @foreach($allBahanBaku as $bb)
                                    {
                                        id: {{ $bb->id }},
                                        nama: '{{ addslashes($bb->nama_bahan) }}',
                                        kode: '{{ $bb->id_bahan_baku }}',
                                        satuan: '{{ addslashes($bb->satuan->singkatan ?? '-') }}',
                                        harga: {{ (float)($bb->harga_satuan ?? 0) }},
                                        stok_minimal: {{ (float)($bb->stok_minimal ?? $bb->stok_minimal_harian ?? 5) }},
                                        full: '{{ addslashes($bb->nama_bahan) }} ({{ $bb->id_bahan_baku }})'
                                    },
                                @endforeach
                            ],
                            get filtered() {
                                if (!this.search) return this.items;
                                const q = this.search.toLowerCase();
                                return this.items.filter(i => i.nama.toLowerCase().includes(q) || i.kode.toLowerCase().includes(q));
                            },
                            select(item) {
                                this.search = item.full;
                                document.getElementById('addBahanInput').value = item.full;
                                this.open = false;
                                window.addCustomBahanRow();
                            }
                        }" @click.outside="open = false">
                            
                            {{-- Magnifying Glass SVG Icon --}}
                            <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-3 pointer-events-none z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>

                            <input type="text" id="addBahanInput"
                                   x-model="search"
                                   @focus="open = true"
                                   @click="open = true"
                                   @input="open = true; window.filterAndPaginateTable(1);"
                                   @keydown.enter.prevent="open = false; window.addCustomBahanRow();"
                                   placeholder="Cari & tambah bahan baku..."
                                   class="w-64 sm:w-80 h-10 rounded-xl border border-gray-200 bg-white text-sm pl-10 pr-9 py-2 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all font-medium shadow-sm">
                            
                            <button type="button" @click="open = !open" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 focus:outline-none">
                                <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>

                            <div x-show="open"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="opacity-0 transform scale-95"
                                 x-transition:enter-end="opacity-100 transform scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="opacity-100 transform scale-100"
                                 x-transition:leave-end="opacity-0 transform scale-95"
                                 class="absolute right-0 z-50 mt-1.5 w-80 bg-white border border-gray-200 rounded-xl shadow-xl max-h-60 overflow-y-auto divide-y divide-gray-50"
                                 style="display: none;">
                                <template x-for="item in filtered" :key="item.id">
                                    <div @click="select(item)" class="px-4 py-2.5 hover:bg-emerald-50/70 cursor-pointer flex items-center justify-between transition-colors">
                                        <div>
                                            <p class="text-sm font-semibold text-gray-800" x-text="item.nama"></p>
                                            <p class="text-xs text-gray-400 font-medium" x-text="item.kode"></p>
                                        </div>
                                        <span class="text-xs px-2.5 py-0.5 bg-gray-100 text-gray-600 rounded-full font-medium" x-text="item.satuan"></span>
                                    </div>
                                </template>
                                <div x-show="filtered.length === 0" class="px-4 py-3 text-xs text-gray-400 text-center">
                                    Bahan baku tidak ditemukan
                                </div>
                            </div>
                        </div>

                        <button type="button" onclick="addCustomBahanRow()" class="h-10 px-5 bg-[#0D3024] hover:bg-[#0D3024]/90 text-white rounded-xl text-sm font-bold transition-all shrink-0 flex items-center justify-center gap-1.5 shadow-sm">
                            + Tambah
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm" id="poTable">
                        <thead>
                            <tr class="bg-gray-50/80 border-b border-gray-200 text-xs font-bold text-gray-500 uppercase tracking-wider">
                                <th class="py-3.5 px-4 text-center w-14">NO</th>
                                <th class="py-3.5 px-6">Bahan Baku</th>
                                <th class="py-3.5 px-6 text-right w-40">Jumlah Pesan</th>
                                <th class="py-3.5 px-6 text-center w-28">Satuan</th>
                                <th class="py-3.5 px-6 text-right w-48">Total Pembelian</th>
                                <th class="py-3.5 px-4 text-center w-16">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100" id="poTableBody">
                            @forelse($items as $idx => $item)
                            @php
                                $qty = $item->jumlah_beli ?? 0;
                                $harga = 0;
                                $totalPembelian = 0;
                            @endphp
                            <tr class="item-row hover:bg-gray-50/50 transition-colors"
                                data-bahan-id="{{ $item->id }}"
                                data-harga="0">
                                <input type="hidden" name="item_checked[{{ $item->id }}]" value="1">
                                <input type="hidden" name="harga_satuan[{{ $item->id }}]" class="harga-satuan-hidden" value="0">
                                <td class="py-3.5 px-4 text-center text-xs text-gray-500 font-semibold row-number">
                                    {{ $idx + 1 }}
                                </td>
                                <td class="py-3.5 px-6">
                                    <p class="font-bold text-gray-900 item-nama">{{ $item->nama_bahan }}</p>
                                    <p class="text-xs text-gray-400 font-medium item-kode">{{ $item->id_bahan_baku }}</p>
                                </td>
                                <td class="py-3.5 px-6 text-right">
                                    <input type="text" inputmode="decimal"
                                        name="jumlah_beli[{{ $item->id }}]"
                                        value="{{ $qty }}"
                                        oninput="this.value = this.value.replace(/[^0-9.]/g, ''); updateRowTotal(this)"
                                        class="w-28 text-right rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 text-sm py-2 px-3 qty-input outline-none font-bold text-gray-900">
                                </td>
                                <td class="py-3.5 px-6 text-center text-gray-600 text-sm font-medium">
                                    {{ $item->satuan->singkatan ?? '-' }}
                                </td>
                                <td class="py-3.5 px-6 text-right">
                                    <div class="relative inline-block w-40">
                                        <span class="absolute left-3 top-2.5 text-xs text-gray-400 font-bold">Rp</span>
                                        <input type="text" inputmode="numeric"
                                            name="total_pembelian[{{ $item->id }}]"
                                            value=""
                                            placeholder="0"
                                            oninput="formatRowHargaInput(this); updateRowTotal(this)"
                                            class="w-full text-right pl-8 pr-3 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 text-sm py-2 total-pembelian-input outline-none font-bold text-gray-900">
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <button type="button" onclick="removePoRow(this)" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus Bahan">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr id="emptyRow">
                                <td colspan="6" class="py-12 text-center text-gray-400 font-medium">
                                    Belum ada bahan baku di dalam daftar PO. Gunakan <span class="font-bold text-gray-700">"Cari & Tambah Bahan Baku"</span> di atas untuk menambahkan bahan.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination Bar --}}
                <div class="px-6 py-3.5 border-t border-gray-100 bg-gray-50/40 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-gray-600" id="paginationControls">
                    <div>
                        Menampilkan <span class="font-bold text-gray-900" id="pageStart">1</span> - <span class="font-bold text-gray-900" id="pageEnd">10</span> dari <span class="font-bold text-gray-900" id="totalItems">0</span> bahan baku
                    </div>
                    <div class="flex items-center gap-1.5" id="paginationButtons">
                    </div>
                </div>

                {{-- Total PO & Action Buttons --}}
                <div class="border-t border-gray-100 px-6 py-6 bg-gray-50/60 space-y-5">
                    <div class="flex justify-end">
                        <div class="w-80 text-right space-y-1">
                            <span class="text-xs font-extrabold text-gray-500 uppercase tracking-wider block">TOTAL PO</span>
                            <span id="grandtotal-display" class="font-extrabold text-gray-900 text-3xl block">Rp 0</span>
                        </div>
                    </div>

                    <div class="flex justify-end items-center gap-3 pt-4 border-t border-gray-200/60">
                        <x-ui.button type="button" variant="secondary" href="{{ route('pengadaan.po.index') }}">
                            Batal
                        </x-ui.button>
                        <x-ui.button type="submit" variant="primary" icon="check">
                            Buat PO
                        </x-ui.button>
                    </div>
                </div>
            </div>

        </form>
    </div>
</div>

@push('scripts')
<script>
    function formatRupiahValue(val) {
        if (val === '' || val === null || val === undefined) return '';
        let raw = String(val).replace(/[^0-9]/g, '');
        if (!raw) return '';
        return parseInt(raw, 10).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function formatRupiah(amount) {
        return 'Rp ' + (amount ? formatRupiahValue(Math.round(amount)) : '0');
    }

    function parseNumericValue(valStr) {
        if (!valStr) return 0;
        let clean = valStr.toString().replace(/[^0-9]/g, '');
        return parseFloat(clean) || 0;
    }

    function formatRowHargaInput(input) {
        input.value = formatRupiahValue(input.value);
    }

    function formatPhone08(input) {
        let val = input.value.replace(/[^0-9]/g, '');
        if (val.length > 0) {
            if (val.startsWith('628')) {
                val = '08' + val.substring(3);
            } else if (val.startsWith('8')) {
                val = '0' + val;
            } else if (val.startsWith('0')) {
                if (val.length >= 2 && !val.startsWith('08')) {
                    val = '08' + val.substring(1).replace(/^0+/, '');
                }
            } else {
                val = '08' + val;
            }
        }
        input.value = val;
    }

    const suppliersPhoneMap = {
        @foreach($suppliers as $sup)
            "{{ addslashes($sup->nama_pemasok) }}": "{{ $sup->nomor_telepon }}",
        @endforeach
    };

    function autoFillSupplierPhone(input) {
        const phoneInput = document.getElementById('supplier_telepon');
        if (!phoneInput) return;
        const val = input.value.trim();
        if (suppliersPhoneMap[val]) {
            phoneInput.value = suppliersPhoneMap[val];
            formatPhone08(phoneInput);
        }
    }

    function updateRowTotal(input) {
        const tr = input.closest('tr');
        if (!tr) return;

        const qtyInput = tr.querySelector('.qty-input');
        const totalInput = tr.querySelector('.total-pembelian-input');
        const hiddenHarga = tr.querySelector('.harga-satuan-hidden');

        const qty = parseNumericValue(qtyInput ? qtyInput.value : 0);
        const total = parseNumericValue(totalInput ? totalInput.value : 0);

        if (hiddenHarga && qty > 0 && total > 0) {
            hiddenHarga.value = total / qty;
        }

        recalcTotal();
    }

    function recalcTotal() {
        let grandTotal = 0;
        let visibleCount = 0;

        const rows = document.querySelectorAll('.item-row');
        rows.forEach(tr => {
            if (tr.style.display !== 'none') {
                visibleCount++;
                const totalInput = tr.querySelector('.total-pembelian-input');
                const total = parseNumericValue(totalInput ? totalInput.value : 0);
                grandTotal += total;
            }
        });

        const grandDisplay = document.getElementById('grandtotal-display');
        if (grandDisplay) {
            grandDisplay.textContent = formatRupiah(grandTotal);
        }

        const badge = document.getElementById('itemCountBadge');
        if (badge) {
            badge.textContent = visibleCount + ' item';
        }
    }

    function removePoRow(button) {
        const tr = button.closest('tr');
        if (!tr) return;

        tr.remove();
        recalcTotal();
        filterAndPaginateTable(currentPage);

        const remainingRows = document.querySelectorAll('.item-row');
        if (remainingRows.length === 0) {
            const tbody = document.getElementById('poTableBody');
            tbody.innerHTML = `
                <tr id="emptyRow">
                    <td colspan="6" class="py-12 text-center text-gray-400 font-medium">
                        Belum ada bahan baku di dalam daftar PO. Gunakan <span class="font-bold text-gray-700">"Cari & Tambah Bahan Baku"</span> di atas untuk menambahkan bahan.
                    </td>
                </tr>
            `;
        }
    }

    // Client-side Pagination & Searching
    const ITEMS_PER_PAGE = 15;
    let currentPage = 1;

    function filterAndPaginateTable(page = 1) {
        currentPage = page;
        const addBahanInput = document.getElementById('addBahanInput');
        const query = (addBahanInput ? addBahanInput.value : '').toLowerCase().trim();

        const allRows = Array.from(document.querySelectorAll('.item-row'));
        const matchedRows = allRows.filter(row => {
            if (!query) return true;
            const nama = (row.querySelector('.item-nama')?.textContent || '').toLowerCase();
            const kode = (row.querySelector('.item-kode')?.textContent || '').toLowerCase();
            return nama.includes(query) || kode.includes(query);
        });

        const totalMatched = matchedRows.length;
        const totalPages = Math.ceil(totalMatched / ITEMS_PER_PAGE) || 1;
        if (currentPage > totalPages) currentPage = totalPages;

        const startIdx = (currentPage - 1) * ITEMS_PER_PAGE;
        const endIdx = startIdx + ITEMS_PER_PAGE;

        allRows.forEach(row => row.style.display = 'none');

        matchedRows.slice(startIdx, endIdx).forEach((row, idx) => {
            row.style.display = '';
            const numCell = row.querySelector('.row-number');
            if (numCell) numCell.textContent = startIdx + idx + 1;
        });

        updatePaginationUI(totalMatched, startIdx, endIdx, totalPages);
        recalcTotal();
    }

    function updatePaginationUI(total, startIdx, endIdx, totalPages) {
        const pageStartEl = document.getElementById('pageStart');
        const pageEndEl = document.getElementById('pageEnd');
        const totalItemsEl = document.getElementById('totalItems');
        const container = document.getElementById('paginationButtons');

        if (!pageStartEl || !container) return;

        pageStartEl.textContent = total > 0 ? startIdx + 1 : 0;
        pageEndEl.textContent = Math.min(endIdx, total);
        totalItemsEl.textContent = total;

        container.innerHTML = '';
        if (totalPages <= 1) return;

        const createBtn = (text, targetPage, active = false, disabled = false) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = `px-2.5 py-1 rounded-lg text-xs font-semibold transition-all ${
                active ? 'bg-primary text-white shadow-sm' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50'
            } ${disabled ? 'opacity-40 cursor-not-allowed' : ''}`;
            btn.textContent = text;
            if (!disabled) {
                btn.onclick = () => filterAndPaginateTable(targetPage);
            }
            return btn;
        };

        // Prev Button
        container.appendChild(createBtn('Sebelumnya', currentPage - 1, false, currentPage === 1));

        for (let i = 1; i <= totalPages; i++) {
            if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                container.appendChild(createBtn(i, i, i === currentPage));
            } else if (i === currentPage - 2 || i === currentPage + 2) {
                const dots = document.createElement('span');
                dots.className = 'px-1 text-gray-400';
                dots.textContent = '...';
                container.appendChild(dots);
            }
        }

        // Next Button
        container.appendChild(createBtn('Selanjutnya', currentPage + 1, false, currentPage === totalPages));
    }

    const allBahanDataMap = {
        @foreach($allBahanBaku as $bb)
            "{{ addslashes($bb->nama_bahan) }} ({{ $bb->id_bahan_baku }})": {
                id: {{ $bb->id }},
                nama: "{{ addslashes($bb->nama_bahan) }}",
                kode: "{{ $bb->id_bahan_baku }}",
                satuan: "{{ $bb->satuan->singkatan ?? '-' }}",
                harga: {{ (float)($bb->harga_satuan ?? 0) }}
            },
        @endforeach
    };

    function addCustomBahanRow() {
        const input = document.getElementById('addBahanInput');
        if (!input || !input.value.trim()) return;

        const val = input.value.trim();
        let itemData = allBahanDataMap[val];

        if (!itemData) {
            const foundKey = Object.keys(allBahanDataMap).find(k => k.toLowerCase().includes(val.toLowerCase()) || allBahanDataMap[k].nama.toLowerCase().includes(val.toLowerCase()));
            if (foundKey) itemData = allBahanDataMap[foundKey];
        }

        if (!itemData) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'warning', title: 'Bahan Baku Tidak Ditemukan', text: 'Silakan pilih bahan baku dari daftar sugesti yang tersedia.', confirmButtonColor: '#0D3024' });
            } else {
                alert('Bahan baku tidak ditemukan. Pilih bahan baku dari daftar sugesti.');
            }
            return;
        }

        const bahanId = itemData.id;
        const existingRow = document.querySelector(`.item-row[data-bahan-id="${bahanId}"]`);
        if (existingRow) {
            existingRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
            existingRow.classList.add('bg-amber-100');
            setTimeout(() => existingRow.classList.remove('bg-amber-100'), 1500);
            input.value = '';
            recalcTotal();
            filterAndPaginateTable(currentPage);
            return;
        }

        const emptyRow = document.getElementById('emptyRow');
        if (emptyRow) emptyRow.remove();

        const tbody = document.getElementById('poTableBody');

        const tr = document.createElement('tr');
        tr.className = 'item-row hover:bg-gray-50/50 transition-colors bg-amber-50/40';
        tr.setAttribute('data-bahan-id', bahanId);
        tr.setAttribute('data-harga', itemData.harga);

        tr.innerHTML = `
            <input type="hidden" name="item_checked[${bahanId}]" value="1">
            <input type="hidden" name="harga_satuan[${bahanId}]" class="harga-satuan-hidden" value="0">
            <td class="py-3.5 px-4 text-center text-xs text-gray-500 font-semibold row-number">-</td>
            <td class="py-3.5 px-6">
                <p class="font-bold text-gray-900 item-nama">${itemData.nama}</p>
                <p class="text-xs text-gray-400 font-medium item-kode">${itemData.kode}</p>
            </td>
            <td class="py-3.5 px-6 text-right">
                <input type="text" inputmode="decimal"
                    name="jumlah_beli[${bahanId}]"
                    value="${itemData.stok_minimal ? (itemData.stok_minimal * 2) : 10}"
                    oninput="this.value = this.value.replace(/[^0-9.]/g, ''); updateRowTotal(this)"
                    class="w-28 text-right rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 text-sm py-2 px-3 qty-input outline-none font-bold text-gray-900">
            </td>
            <td class="py-3.5 px-6 text-center text-gray-600 text-sm font-medium">
                ${itemData.satuan}
            </td>
            <td class="py-3.5 px-6 text-right">
                <div class="relative inline-block w-40">
                    <span class="absolute left-3 top-2.5 text-xs text-gray-400 font-bold">Rp</span>
                    <input type="text" inputmode="numeric"
                        name="total_pembelian[${bahanId}]"
                        value=""
                        placeholder="0"
                        oninput="formatRowHargaInput(this); updateRowTotal(this)"
                        class="w-full text-right pl-8 pr-3 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 text-sm py-2 total-pembelian-input outline-none font-bold text-gray-900">
                </div>
            </td>
            <td class="py-3.5 px-4 text-center">
                <button type="button" onclick="removePoRow(this)" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus Bahan">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </button>
            </td>
        `;

        tbody.appendChild(tr);
        input.value = '';
        recalcTotal();
        filterAndPaginateTable(currentPage);
    }

    document.addEventListener('DOMContentLoaded', () => {
        recalcTotal();
        filterAndPaginateTable(1);

        const form = document.getElementById('poForm');
        if (form) {
            form.addEventListener('submit', function() {
                const rows = document.querySelectorAll('.item-row');
                rows.forEach(tr => {
                    const qtyInput = tr.querySelector('.qty-input');
                    const qty = parseNumericValue(qtyInput ? qtyInput.value : 0);
                    if (qty <= 0) {
                        tr.querySelectorAll('input').forEach(input => input.disabled = true);
                    }
                });
            });
        }
    });
</script>
@endpush
@endsection