@extends('layouts.pos')
@section('title', 'Terima Bahan Baku')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800 pb-10">
    <div class="w-full p-6 space-y-5">

        <x-ui.page-header
            title="Terima Bahan Baku"
            subtitle="Catat bahan yang benar-benar diterima dari supplier/toko."
            :breadcrumbs="['Pengadaan', 'Penerimaan Bahan Baku', 'Terima Bahan']">
        </x-ui.page-header>

        <x-ui.alert />

        <form action="{{ route('pengadaan.penerimaan.store', $po->id) }}" method="POST" id="penerimaanForm" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-5">
                <div class="lg:col-span-3"
                     x-data="{
                         page: 1,
                         perPage: 10,
                         search: '',
                         items: [
                             @foreach($items as $idx => $row)
                             {
                                 id: '{{ $row['detail_id'] }}',
                                 name: '{{ strtolower(addslashes($row['nama_bahan'])) }}',
                                 code: '{{ strtolower(addslashes($row['kode_bahan'])) }}'
                             }{{ !$loop->last ? ',' : '' }}
                             @endforeach
                         ],
                         get filteredItems() {
                             if (!this.search.trim()) return this.items;
                             const q = this.search.toLowerCase().trim();
                             return this.items.filter(i => i.name.includes(q) || i.code.includes(q));
                         },
                         get totalItems() {
                             return this.filteredItems.length;
                         },
                         get totalPages() {
                             return Math.ceil(this.totalItems / this.perPage) || 1;
                         },
                         get startItem() {
                             return this.totalItems > 0 ? (this.page - 1) * this.perPage + 1 : 0;
                         },
                         get endItem() {
                             return Math.min(this.page * this.perPage, this.totalItems);
                         },
                         isItemVisible(id) {
                             const idx = this.filteredItems.findIndex(i => i.id == id);
                             if (idx === -1) return false;
                             return idx >= (this.page - 1) * this.perPage && idx < this.page * this.perPage;
                         },
                         nextPage() {
                             if (this.page < this.totalPages) this.page++;
                         },
                         prevPage() {
                             if (this.page > 1) this.page--;
                         },
                         setPage(p) {
                             this.page = p;
                         },
                         onSearch() {
                             this.page = 1;
                         }
                     }">
                    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
                        <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <h3 class="font-bold text-gray-900 text-sm tracking-tight uppercase">DAFTAR BAHAN DITERIMA</h3>
                                <span class="text-xs text-gray-600 font-semibold px-2.5 py-0.5 bg-gray-200/70 rounded-md" id="itemCountBadge">
                                    <span x-text="totalItems"></span> item
                                </span>
                            </div>
                            <div class="relative">
                                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                <input type="text" x-model="search" @input="onSearch()" placeholder="Cari bahan baku..." class="w-full sm:w-64 pl-9 pr-3 py-1.5 text-sm border border-gray-200 rounded-lg outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/20">
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm" id="itemsTable">
                                <thead>
                                    <tr class="border-b border-gray-100 text-xs font-bold text-gray-500 uppercase tracking-wide bg-white">
                                        <th class="px-4 py-3 text-center w-14">Pilih</th>
                                        <th class="px-4 py-3 text-left">Bahan Baku</th>
                                        <th class="px-4 py-3 text-right w-32">Dipesan</th>
                                        <th class="px-4 py-3 text-right w-36">Diterima</th>
                                        <th class="px-4 py-3 text-right w-44">Harga Satuan <span class="text-[10px] font-normal text-gray-400 lowercase italic">(opsional)</span></th>
                                        <th class="px-4 py-3 text-right w-44">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    @forelse($items as $idx => $row)
                                    @php
                                        $dipesanVal = (float) $row['jumlah_dipesan'];
                                        $sisaVal = (float) $row['sisa'];
                                        $hargaSatuanPO = (float) ($row['harga_satuan'] ?? 0);
                                        $subtotalInitial = $sisaVal * $hargaSatuanPO;
                                    @endphp
                                    <tr class="item-row hover:bg-gray-50/40 transition-colors" x-show="isItemVisible('{{ $row['detail_id'] }}')">
                                        <td class="px-4 py-3 text-center align-middle">
                                            <input type="checkbox" name="item_checked[{{ $row['detail_id'] }}]" value="1" class="item-checkbox rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer" checked onchange="toggleRowInputs(this, '{{ $row['detail_id'] }}')">
                                        </td>
                                        <td class="px-4 py-3 align-middle">
                                            <p class="font-bold text-gray-900 text-sm">{{ $row['nama_bahan'] }}</p>
                                            <p class="text-xs text-gray-400 font-medium mt-0.5">{{ $row['kode_bahan'] }}</p>
                                        </td>
                                        <td class="px-4 py-3 text-right align-middle font-bold text-gray-900" id="dipesan_{{ $row['detail_id'] }}" data-dipesan="{{ $dipesanVal }}">
                                            {{ \App\Helpers\UnitHelper::formatNumber($dipesanVal) }} <span class="text-xs font-normal text-gray-500">{{ $row['satuan'] }}</span>
                                        </td>
                                        <td class="px-4 py-3 align-middle">
                                            <div class="flex items-center gap-1.5 justify-end">
                                                <input type="text" name="jumlah_diterima[{{ $row['detail_id'] }}]" id="jml_{{ $row['detail_id'] }}" value="{{ (float) $sisaVal }}" oninput="this.value = this.value.replace(/[^0-9.,]/g, ''); updateRowCalc('{{ $row['detail_id'] }}')" class="w-24 text-right border border-gray-200 text-gray-900 text-sm rounded-xl px-3 py-1.5 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 font-bold" required>
                                                <span class="text-xs text-gray-500 font-medium shrink-0">{{ $row['satuan'] }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 align-middle">
                                            <div class="relative inline-block w-full">
                                                <span class="absolute left-3 top-2 text-xs text-gray-400 font-bold">Rp</span>
                                                <input type="text" name="harga_beli[{{ $row['detail_id'] }}]" id="hrg_{{ $row['detail_id'] }}" value="{{ $hargaSatuanPO > 0 ? number_format($hargaSatuanPO, 0, ',', '.') : '' }}" placeholder="0" oninput="formatRupiahInput(this); updateRowCalc('{{ $row['detail_id'] }}')" class="w-full text-right pl-8 pr-3 border border-gray-200 text-gray-900 text-sm rounded-xl py-1.5 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 font-bold hrg-input">
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-right align-middle">
                                            <div class="font-bold text-gray-900" id="subtotal_{{ $row['detail_id'] }}">
                                                Rp {{ number_format($subtotalInitial, 0, ',', '.') }}
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-10 text-center text-gray-500 text-sm font-medium">Semua bahan pada PO ini sudah diterima.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination Bar --}}
                        <div class="px-6 py-3.5 border-t border-gray-100 bg-gray-50/40 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-gray-600" id="paginationControls">
                            <div>
                                Menampilkan <span class="font-bold text-gray-900" x-text="startItem"></span> - <span class="font-bold text-gray-900" x-text="endItem"></span> dari <span class="font-bold text-gray-900" x-text="totalItems"></span> bahan baku
                            </div>
                            <div class="flex items-center gap-1.5" x-show="totalPages > 1">
                                {{-- Prev Button --}}
                                <button type="button" 
                                        @click="prevPage()" 
                                        :disabled="page === 1"
                                        :class="page === 1 ? 'opacity-40 cursor-not-allowed pointer-events-none' : 'hover:bg-gray-50 cursor-pointer'"
                                        class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-white border border-gray-200 text-gray-700 transition-all shadow-2xs">
                                    Sebelumnya
                                </button>

                                {{-- Page Numbers --}}
                                <template x-for="p in totalPages" :key="p">
                                    <button type="button" 
                                            x-show="p === 1 || p === totalPages || (p >= page - 1 && p <= page + 1)"
                                            @click="setPage(p)" 
                                            :class="p === page ? 'bg-emerald-600 text-white shadow-xs font-bold' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 font-semibold'"
                                            class="min-w-[32px] px-2.5 py-1.5 rounded-lg text-xs transition-all cursor-pointer"
                                            x-text="p">
                                    </button>
                                </template>

                                {{-- Next Button --}}
                                <button type="button" 
                                        @click="nextPage()" 
                                        :disabled="page === totalPages"
                                        :class="page === totalPages ? 'opacity-40 cursor-not-allowed pointer-events-none' : 'hover:bg-gray-50 cursor-pointer'"
                                        class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-white border border-gray-200 text-gray-700 transition-all shadow-2xs">
                                    Selanjutnya
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 justify-end mt-4">
                        <a href="{{ route('pengadaan.po.show', $po->id) }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-gray-700 bg-gray-100 rounded-xl px-4 py-2.5 hover:bg-gray-200 transition-colors">
                            Batal
                        </a>
                        <button type="submit" class="inline-flex items-center gap-1.5 text-sm font-bold text-white bg-emerald-600 rounded-xl px-5 py-2.5 hover:bg-emerald-700 transition-colors shadow-sm" onclick="return validateForm(this)">
                            <x-heroicon-o-check class="w-4 h-4" />
                            Simpan Penerimaan
                        </button>
                    </div>
                </div>

                <div class="lg:col-span-1 space-y-5">  
                    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
                        <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                            <h3 class="font-bold text-gray-900 text-sm tracking-tight">Informasi Penerimaan</h3>
                        </div>
                        <div class="p-4 space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1">Tanggal Penerimaan</label>
                                <input type="date" name="tanggal_penerimaan" value="{{ date('Y-m-d') }}" class="w-full border border-gray-200 text-gray-900 text-sm rounded-xl px-3 py-2 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 font-medium">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1">Total Pembelian (Rp)</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2.5 text-sm text-gray-500 font-bold">Rp</span>
                                    <input type="text" name="total_nota" id="grandTotalNota" value="" placeholder="0" 
                                           oninput="formatRupiahInput(this); isManualInput = true;" 
                                           class="w-full pl-9 pr-3 border border-gray-200 text-gray-900 text-base rounded-xl py-2 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 font-extrabold text-right">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1">Catatan <span class="font-normal italic">(opsional)</span></label>
                                <textarea name="catatan" rows="3" placeholder="Keterangan tambahan..." class="w-full border border-gray-200 text-gray-900 text-sm rounded-xl px-3 py-2 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 font-medium"></textarea>
                            </div>
                            
                            <p class="text-[11px] text-gray-400 leading-relaxed pt-2 border-t border-gray-100">Jika harga satuan diisi, sistem akan menghitung Total Pembelian secara otomatis. Namun Anda tetap dapat mengubah Total Pembelian sesuai dengan tagihan aktual di nota.</p>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    let isManualInput = false;

    function parseDecimal(val) {
        if (val === '' || val === null || val === undefined) return 0;
        let str = String(val).replace(/[^0-9.,]/g, '').replace(',', '.');
        let parsed = parseFloat(str);
        return isNaN(parsed) ? 0 : parsed;
    }

    function parseInteger(val) {
        if (val === '' || val === null || val === undefined) return 0;
        let str = String(val).replace(/[^0-9]/g, '');
        let parsed = parseInt(str, 10);
        return isNaN(parsed) ? 0 : parsed;
    }

    function toggleRowInputs(checkbox, id) {
        const targets = ['jml_', 'hrg_'];
        targets.forEach(prefix => {
            const el = document.getElementById(prefix + id);
            if (el) el.disabled = !checkbox.checked;
        });
        updateRowCalc(id);
    }

    function formatRupiahValue(val) {
        if (val === '' || val === null || val === undefined) return '0';
        let raw = String(val).replace(/[^0-9]/g, '');
        if (!raw) return '0';
        return parseInt(raw, 10).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function formatRupiahInput(input) {
        input.value = formatRupiahValue(input.value);
    }

    function updateRowCalc(id) {
        const jmlInput = document.getElementById('jml_' + id);
        const hrgInput = document.getElementById('hrg_' + id);
        const subtotalCell = document.getElementById('subtotal_' + id);
        const checkbox = document.querySelector(`input[name="item_checked[${id}]"]`);

        if (!jmlInput || !hrgInput || !subtotalCell || !checkbox) return;

        let subtotal = 0;
        if (checkbox.checked) {
            const diterima = parseDecimal(jmlInput.value);
            const harga = parseInteger(hrgInput.value);
            subtotal = diterima * harga;
        }

        subtotalCell.innerHTML = 'Rp ' + formatRupiahValue(subtotal);
        recalcGrandTotal();
    }

    function recalcGrandTotal() {
        let grandTotal = 0;

        document.querySelectorAll('.item-row').forEach(row => {
            const checkbox = row.querySelector('.item-checkbox');
            if (checkbox && checkbox.checked) {
                const idMatch = checkbox.name.match(/\[(.*?)\]/);
                if (idMatch) {
                    const id = idMatch[1];
                    const jmlInput = document.getElementById('jml_' + id);
                    const hrgInput = document.getElementById('hrg_' + id);
                    const diterima = parseDecimal(jmlInput ? jmlInput.value : 0);
                    const harga = parseInteger(hrgInput ? hrgInput.value : 0);
                    grandTotal += (diterima * harga);
                }
            }
        });

        const grandTotalInput = document.getElementById('grandTotalNota');
        if (grandTotalInput && !isManualInput) {
            grandTotalInput.value = formatRupiahValue(grandTotal);
        }
    }

    function validateForm(btn) {
        if (btn.form && !btn.form.checkValidity()) {
            btn.form.reportValidity();
            return false;
        }
        
        const checked = document.querySelectorAll('.item-checkbox:checked');
        if (checked.length === 0) {
            if(typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'warning', title: 'Pilih Bahan', text: 'Pilih minimal satu bahan untuk diterima.', confirmButtonColor: '#0D3024' });
            } else {
                alert('Pilih minimal satu bahan untuk diterima.');
            }
            return false;
        }
        
        setTimeout(() => {
            btn.disabled = true;
            btn.innerHTML = '<svg class="w-4 h-4 animate-spin inline-block mr-1" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Menyimpan...';
        }, 50);

        return true;
    }

    function initPageCalculations() {
        document.querySelectorAll('.item-row').forEach(row => {
            const checkbox = row.querySelector('.item-checkbox');
            if (checkbox) {
                const match = checkbox.name.match(/\[(.*?)\]/);
                if (match) updateRowCalc(match[1]);
            }
        });
        
        recalcGrandTotal();

        const grandTotalInput = document.getElementById('grandTotalNota');
        if (grandTotalInput) {
            grandTotalInput.addEventListener('blur', function() {
                if (!this.value || this.value === '0') {
                    isManualInput = false;
                    recalcGrandTotal();
                }
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPageCalculations);
    } else {
        initPageCalculations();
    }
</script>
@endpush
@endsection