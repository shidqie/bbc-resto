@extends('layouts.pos')
@section('title', 'Buat Penyesuaian Stok')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800">
    <div class="w-full p-6 space-y-5">
        {{-- Page Header --}}
        <x-ui.page-header
            title="Buat Penyesuaian Stok"
            subtitle="Masukkan stok fisik hasil opname persediaan bahan baku"
            :breadcrumbs="['Persediaan', 'Penyesuaian Stok', 'Buat Baru']">
            <x-slot:actions>
                <x-ui.button variant="secondary" href="{{ route('penyesuaian-stok.index') }}">
                    &larr; Kembali
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.alert />

        <form action="{{ route('penyesuaian-stok.store') }}" method="POST" id="form-penyesuaian-single">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                {{-- Left Side: Input Data Bahan & Stok (Col 7) --}}
                <div class="lg:col-span-7 bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-5">
                    <h2 class="text-base font-bold text-gray-900 border-b border-gray-100 pb-3">Informasi Bahan & Opname</h2>

                    {{-- 1. Jenis Stok --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-2">Jenis Stok <span class="text-red-500">*</span></label>
                        <x-ui.searchable-select id="jenis_persediaan" name="jenis_persediaan" :options="['harian' => 'Harian', 'catering' => 'Katering']" :selected="old('jenis_persediaan', 'harian')" placeholder="-- Pilih Jenis Stok --" required="true" />
                    </div>

                    {{-- 2. Bahan Baku (Searchable Combobox & Dropdown) --}}
                    <div class="relative" x-data="{
                        open: false,
                        search: '',
                        selectedId: '{{ old('bahan_baku_id') }}',
                        selectedName: '',
                        items: [
                            @foreach($bahanBakus as $b)
                                @php
                                    $harian = (float)($b->stok_harian?->jumlah_stok ?? 0);
                                    $catering = (float)($b->stok_catering_balance?->jumlah_stok ?? 0);
                                    $satuan = $b->satuan->singkatan ?? $b->satuan->nama_satuan ?? 'unit';
                                @endphp
                                {
                                    id: '{{ $b->id }}',
                                    nama: '{{ addslashes($b->nama_bahan) }}',
                                    kode: '{{ $b->id_bahan_baku }}',
                                    harian: {{ $harian }},
                                    catering: {{ $catering }},
                                    satuan: '{{ addslashes($satuan) }}'
                                },
                            @endforeach
                        ],
                        init() {
                            if (this.selectedId) {
                                const found = this.items.find(i => i.id == this.selectedId);
                                if (found) {
                                    this.selectedName = found.nama + ' (' + found.kode + ')';
                                    this.search = this.selectedName;
                                    this.$nextTick(() => window.updateFormStateFromItem(found));
                                }
                            }
                        },
                        get filteredItems() {
                            if (!this.search || this.search === this.selectedName) return this.items;
                            const q = this.search.toLowerCase();
                            return this.items.filter(i => i.nama.toLowerCase().includes(q) || i.kode.toLowerCase().includes(q));
                        },
                        selectItem(item) {
                            this.selectedId = item.id;
                            this.selectedName = item.nama + ' (' + item.kode + ')';
                            this.search = this.selectedName;
                            this.open = false;
                            window.updateFormStateFromItem(item);
                        },
                        toggleOpen() {
                            this.open = !this.open;
                            if (this.open && this.selectedName && this.search === this.selectedName) {
                                // Keep current search text to allow smooth typing
                            }
                        }
                    }" @click.outside="open = false; if(!selectedId) search = ''; else search = selectedName;">

                        <label class="block text-sm font-semibold text-gray-800 mb-2">Bahan Baku <span class="text-red-500">*</span></label>
                        
                        <input type="hidden" name="bahan_baku_id" id="bahan_baku_id" :value="selectedId" required>

                        <div class="relative">
                            <input type="text"
                                   x-model="search"
                                   @focus="open = true"
                                   @click="open = true"
                                   @input="open = true; selectedId = ''; window.updateFormStateFromItem(null);"
                                   placeholder="-- Cari atau Pilih Bahan Baku --"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white font-medium pr-10 cursor-pointer">
                            
                            <button type="button" @click.prevent="toggleOpen()" class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-gray-400 hover:text-gray-600 focus:outline-none">
                                <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                        </div>

                        {{-- Dropdown Panel --}}
                        <div x-show="open"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 transform scale-100"
                             x-transition:leave-end="opacity-0 transform scale-95"
                             class="absolute z-50 mt-1.5 w-full bg-white border border-gray-200 rounded-xl shadow-lg max-h-60 overflow-y-auto divide-y divide-gray-50"
                             style="display: none;">
                            <template x-for="item in filteredItems" :key="item.id">
                                <div @click="selectItem(item)"
                                     class="px-4 py-2.5 hover:bg-emerald-50/60 cursor-pointer flex items-center justify-between transition-colors">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-800" x-text="item.nama"></p>
                                        <p class="text-xs text-gray-400 font-mono" x-text="item.kode"></p>
                                    </div>
                                    <span class="text-xs px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full font-medium" x-text="item.satuan"></span>
                                </div>
                            </template>
                            <div x-show="filteredItems.length === 0" class="px-4 py-3 text-sm text-gray-400 text-center">
                                Bahan baku tidak ditemukan
                            </div>
                        </div>
                    </div>

                    {{-- Grid 2 Box: Stok Sistem & Stok Fisik --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- 3. Stok Sistem (Display Only) --}}
                        <div class="bg-gray-50/80 rounded-xl p-4 border border-gray-200">
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider block">Stok Sistem</span>
                            <div class="mt-2 flex items-baseline justify-between">
                                <span id="display-stok-sistem" class="text-2xl font-bold text-gray-900">0</span>
                                <span id="display-satuan-1" class="text-sm font-medium text-gray-500"></span>
                            </div>
                            <span class="text-[11px] text-gray-400 mt-1 block">Dihitung otomatis oleh sistem</span>
                        </div>

                        {{-- 5. Selisih (Auto Calculated) --}}
                        <div class="bg-amber-50/60 rounded-xl p-4 border border-amber-200/60">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-amber-900 uppercase tracking-wider">Selisih</span>
                                <span class="text-[10px] text-amber-700 font-bold bg-amber-100 px-1.5 py-0.5 rounded">Otomatis</span>
                            </div>
                            <div class="mt-2 flex items-baseline justify-between">
                                <span id="display-selisih" class="text-2xl font-bold text-gray-400">0</span>
                                <span id="display-satuan-3" class="text-sm font-medium text-gray-500"></span>
                            </div>
                            <span class="text-[11px] text-amber-700 mt-1 block">Fisik − Sistem</span>
                        </div>
                    </div>

                    {{-- 4. Stok Fisik Input --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-2">Stok Fisik Hasil Opname <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="number" step="any" name="jumlah_fisik" id="jumlah_fisik" required placeholder="0" value="{{ old('jumlah_fisik') }}" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-base focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white pr-16 font-bold text-gray-900">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                <span id="display-satuan-2" class="text-sm font-bold text-gray-500"></span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right Side: Alasan, Catatan & Action (Col 5) --}}
                <div class="lg:col-span-5 bg-white rounded-2xl border border-gray-200 shadow-sm p-6 flex flex-col justify-between space-y-5">
                    <div class="space-y-5">
                        <h2 class="text-base font-bold text-gray-900 border-b border-gray-100 pb-3">Alasan & Catatan</h2>

                        {{-- 6. Alasan --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-800 mb-2">Alasan Penyesuaian <span class="text-red-500">*</span></label>
                            <input type="text" name="alasan" id="alasan" required list="alasan-list" placeholder="Contoh: Bahan tumpah, Bahan rusak, Selisih hitung" value="{{ old('alasan') }}" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white">
                            <datalist id="alasan-list">
                                <option value="Bahan tumpah">
                                <option value="Bahan rusak">
                                <option value="Bahan kadaluarsa">
                                <option value="Selisih hitung opname">
                                <option value="Pengurangan manual">
                            </datalist>
                        </div>

                        {{-- 7. Catatan (Opsional) --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-800 mb-2">Catatan Tambahan <span class="text-xs font-normal text-gray-400">(Opsional)</span></label>
                            <textarea name="catatan" rows="4" placeholder="Contoh: Sebagian beras tumpah saat penyimpanan di gudang" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white resize-none">{{ old('catatan') }}</textarea>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-100">
                        <a href="{{ route('penyesuaian-stok.index') }}" class="px-5 py-2.5 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition text-sm">
                            Batal
                        </a>
                        <button type="submit" class="px-6 py-2.5 bg-primary text-white font-semibold rounded-xl hover:bg-primary-container transition text-sm shadow-sm flex items-center gap-2">
                            <x-heroicon-o-check-circle class="w-4 h-4" />
                            Simpan Penyesuaian
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    let activeItem = null;

    window.updateFormStateFromItem = function(item) {
        activeItem = item;
        const jenisSelect = document.getElementById('jenis_persediaan');
        const fisikInput = document.getElementById('jumlah_fisik');
        const displayStokSistem = document.getElementById('display-stok-sistem');
        const displaySelisih = document.getElementById('display-selisih');
        const displaySatuan1 = document.getElementById('display-satuan-1');
        const displaySatuan2 = document.getElementById('display-satuan-2');
        const displaySatuan3 = document.getElementById('display-satuan-3');

        if (!item) {
            displayStokSistem.textContent = '0';
            displaySatuan1.textContent = '';
            displaySatuan2.textContent = '';
            displaySatuan3.textContent = '';
            updateSelisih(0);
            return;
        }

        const jenis = jenisSelect.value;
        const stokSistem = jenis === 'catering' ? parseFloat(item.catering || 0) : parseFloat(item.harian || 0);
        const satuan = item.satuan || '';

        displayStokSistem.textContent = stokSistem.toLocaleString('id-ID', { maximumFractionDigits: 3 });
        displaySatuan1.textContent = satuan;
        displaySatuan2.textContent = satuan;
        displaySatuan3.textContent = satuan;

        updateSelisih(stokSistem);
    };

    function updateSelisih(stokSistemOverride) {
        const jenisSelect = document.getElementById('jenis_persediaan');
        const fisikInput = document.getElementById('jumlah_fisik');
        const displaySelisih = document.getElementById('display-selisih');

        let stokSistem = stokSistemOverride;
        if (stokSistem === undefined && activeItem) {
            stokSistem = jenisSelect.value === 'catering' ? parseFloat(activeItem.catering || 0) : parseFloat(activeItem.harian || 0);
        } else if (stokSistem === undefined) {
            stokSistem = 0;
        }

        const fisikVal = parseFloat(fisikInput.value);
        if (isNaN(fisikVal) || fisikInput.value === '') {
            displaySelisih.textContent = '0';
            displaySelisih.className = 'text-2xl font-bold text-gray-400';
            return;
        }

        const selisih = fisikVal - stokSistem;
        const formatted = (selisih > 0 ? '+' : '') + selisih.toLocaleString('id-ID', { maximumFractionDigits: 3 });

        displaySelisih.textContent = formatted;

        if (selisih > 0) {
            displaySelisih.className = 'text-2xl font-bold text-emerald-600';
        } else if (selisih < 0) {
            displaySelisih.className = 'text-2xl font-bold text-red-600';
        } else {
            displaySelisih.className = 'text-2xl font-bold text-gray-600';
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const jenisSelect = document.getElementById('jenis_persediaan');
        const fisikInput = document.getElementById('jumlah_fisik');

        jenisSelect.addEventListener('change', function() {
            if (activeItem) window.updateFormStateFromItem(activeItem);
        });

        fisikInput.addEventListener('input', function() {
            updateSelisih();
        });
    });
</script>
@endsection
