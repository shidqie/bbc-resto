@extends('layouts.pos')
@section('title', 'Buat Penyesuaian Stok')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800" x-data="penyesuaianForm()">
    <div class="w-full p-6 space-y-5">
        {{-- Page Header --}}
        <x-ui.page-header
            title="Buat Penyesuaian Stok"
            subtitle="Kurangi, tambah, atau sesuaikan saldo stok bahan baku"
            :breadcrumbs="['Persediaan', 'Penyesuaian Stok', 'Buat Baru']">
            <x-slot:actions>
                <x-ui.button variant="secondary" href="{{ route('penyesuaian-stok.index') }}">
                    &larr; Kembali
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.alert />

        <form action="{{ route('penyesuaian-stok.store') }}" method="POST" id="form-penyesuaian" @submit="handleSubmit">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                {{-- Left Side: Input Data Bahan & Stok (Col 7) --}}
                <div class="lg:col-span-7 bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-5">
                    <h2 class="text-base font-bold text-gray-900 border-b border-gray-100 pb-3 flex items-center justify-between">
                        <span>Informasi Penyesuaian</span>
                        <span class="text-xs font-normal text-slate-400">Pilih bahan & tentukan aksi</span>
                    </h2>

                    {{-- 1. Jenis Stok --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-2">Jenis Persediaan <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="relative flex items-center p-3 rounded-xl border cursor-pointer transition-all"
                                   :class="jenisPersediaan === 'harian' ? 'border-primary bg-blue-50/50 text-primary font-semibold ring-1 ring-primary' : 'border-gray-200 hover:bg-gray-50 text-gray-700'">
                                <input type="radio" name="jenis_persediaan" value="harian" x-model="jenisPersediaan" @change="onJenisChange()" class="sr-only">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-4 h-4 rounded-full border flex items-center justify-center"
                                         :class="jenisPersediaan === 'harian' ? 'border-primary bg-primary' : 'border-gray-300'">
                                        <div class="w-1.5 h-1.5 rounded-full bg-white" x-show="jenisPersediaan === 'harian'"></div>
                                    </div>
                                    <div>
                                        <span class="text-sm block">Stok Harian</span>
                                        <span class="text-[11px] text-gray-400 font-normal">Resto & Nasi Box</span>
                                    </div>
                                </div>
                            </label>

                            <label class="relative flex items-center p-3 rounded-xl border cursor-pointer transition-all"
                                   :class="jenisPersediaan === 'catering' ? 'border-amber-500 bg-amber-50/50 text-amber-900 font-semibold ring-1 ring-amber-500' : 'border-gray-200 hover:bg-gray-50 text-gray-700'">
                                <input type="radio" name="jenis_persediaan" value="catering" x-model="jenisPersediaan" @change="onJenisChange()" class="sr-only">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-4 h-4 rounded-full border flex items-center justify-center"
                                         :class="jenisPersediaan === 'catering' ? 'border-amber-500 bg-amber-500' : 'border-gray-300'">
                                        <div class="w-1.5 h-1.5 rounded-full bg-white" x-show="jenisPersediaan === 'catering'"></div>
                                    </div>
                                    <div>
                                        <span class="text-sm block">Stok Katering</span>
                                        <span class="text-[11px] text-gray-400 font-normal">Pesanan Katering</span>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- 2. Bahan Baku (Searchable Combobox) --}}
                    <div class="relative">
                        <label class="block text-sm font-semibold text-gray-800 mb-2">Bahan Baku <span class="text-red-500">*</span></label>
                        
                        <input type="hidden" name="bahan_baku_id" id="bahan_baku_id" :value="selectedId" required>

                        <div class="relative">
                            <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-3 pointer-events-none z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            <input type="text"
                                   x-model="search"
                                   @focus="openDropdown = true"
                                   @click="openDropdown = true"
                                   @input="openDropdown = true; selectedId = ''; activeItem = null; recalculate();"
                                   placeholder="Cari & pilih bahan baku..."
                                   class="w-full h-10 border border-gray-200 rounded-xl pl-10 pr-9 py-2 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white font-medium cursor-pointer shadow-sm outline-none">
                            
                            <button type="button" @click.prevent="openDropdown = !openDropdown" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 focus:outline-none cursor-pointer">
                                <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': openDropdown }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                        </div>

                        {{-- Dropdown Panel --}}
                        <div x-show="openDropdown"
                             @click.outside="openDropdown = false; if(!selectedId) search = ''; else search = selectedName;"
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
                                     class="px-4 py-2.5 hover:bg-blue-50/60 cursor-pointer flex items-center justify-between transition-colors">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-800" x-text="item.nama"></p>
                                        <p class="text-xs text-gray-400 font-mono" x-text="'Stok: ' + (jenisPersediaan === 'catering' ? item.catering_fmt : item.harian_fmt)"></p>
                                    </div>
                                    <span class="text-xs px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full font-medium" x-text="item.satuan"></span>
                                </div>
                            </template>
                            <div x-show="filteredItems.length === 0" class="px-4 py-3 text-sm text-gray-400 text-center">
                                Bahan baku tidak ditemukan
                            </div>
                        </div>
                    </div>

                    {{-- 3. Pilihan Aksi Penyesuaian (Kurangi, Tambah, Set Opname) --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-2">Aksi Penyesuaian <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-3 gap-2.5">
                            {{-- Kurangi --}}
                            <button type="button"
                                    @click="setTipeAksi('kurang')"
                                    class="p-3 rounded-xl border text-center transition-all flex flex-col items-center gap-1"
                                    :class="tipeAksi === 'kurang' ? 'border-red-500 bg-red-50 text-red-700 font-bold ring-2 ring-red-500/20' : 'border-gray-200 hover:bg-gray-50 text-gray-600 font-medium'">
                                <div class="w-7 h-7 rounded-full bg-red-100 text-red-600 flex items-center justify-center font-bold text-base mb-0.5">−</div>
                                <span class="text-xs">Kurangi Stok</span>
                                <span class="text-[10px] text-gray-400 font-normal">Rusak/Tumpah/Basi</span>
                            </button>

                            {{-- Tambah --}}
                            <button type="button"
                                    @click="setTipeAksi('tambah')"
                                    class="p-3 rounded-xl border text-center transition-all flex flex-col items-center gap-1"
                                    :class="tipeAksi === 'tambah' ? 'border-emerald-500 bg-emerald-50 text-emerald-700 font-bold ring-2 ring-emerald-500/20' : 'border-gray-200 hover:bg-gray-50 text-gray-600 font-medium'">
                                <div class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-base mb-0.5">+</div>
                                <span class="text-xs">Tambah Stok</span>
                                <span class="text-[10px] text-gray-400 font-normal">Sisa/Bonus/Lebih</span>
                            </button>

                            {{-- Set Opname --}}
                            <button type="button"
                                    @click="setTipeAksi('opname')"
                                    class="p-3 rounded-xl border text-center transition-all flex flex-col items-center gap-1"
                                    :class="tipeAksi === 'opname' ? 'border-blue-500 bg-blue-50 text-blue-700 font-bold ring-2 ring-blue-500/20' : 'border-gray-200 hover:bg-gray-50 text-gray-600 font-medium'">
                                <div class="w-7 h-7 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-base mb-0.5">=</div>
                                <span class="text-xs">Atur Stok Fisik</span>
                                <span class="text-[10px] text-gray-400 font-normal">Total Opname Baru</span>
                            </button>
                        </div>
                        <input type="hidden" name="tipe_aksi" :value="tipeAksi">
                    </div>

                    {{-- 4. Input Kuantitas --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-2">
                            <span x-show="tipeAksi === 'kurang'">Jumlah yang Dikurangkan</span>
                            <span x-show="tipeAksi === 'tambah'">Jumlah yang Ditambahkan</span>
                            <span x-show="tipeAksi === 'opname'">Total Stok Fisik Hasil Opname</span>
                            <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="number"
                                   step="any"
                                   min="0"
                                   name="jumlah_input"
                                   id="jumlah_input"
                                   x-model="jumlahInput"
                                   @input="recalculate()"
                                   required
                                   :placeholder="tipeAksi === 'opname' ? 'Masukkan total stok akhir...' : 'Masukkan jumlah kuantitas...'"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-3 text-base focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white pr-20 font-bold text-gray-900 shadow-sm">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                <span class="text-sm font-bold text-gray-500" x-text="satuanDisplay"></span>
                            </div>
                        </div>
                    </div>

                    {{-- 5. Live Summary Grid 3 Box --}}
                    <div class="grid grid-cols-3 gap-3 pt-2">
                        {{-- Stok Sistem Saat Ini --}}
                        <div class="bg-gray-50 rounded-xl p-3.5 border border-gray-200 text-center">
                            <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider block mb-1">Stok Saat Ini</span>
                            <span class="text-lg font-bold text-gray-900 block" x-text="stokSistemDisplay">0</span>
                            <span class="text-[10px] text-gray-400 font-medium" x-text="satuanDisplay"></span>
                        </div>

                        {{-- Perubahan / Selisih --}}
                        <div class="rounded-xl p-3.5 border text-center transition-colors"
                             :class="tipeAksi === 'kurang' ? 'bg-red-50/70 border-red-200 text-red-700' : (tipeAksi === 'tambah' ? 'bg-emerald-50/70 border-emerald-200 text-emerald-700' : 'bg-blue-50/70 border-blue-200 text-blue-700')">
                            <span class="text-[11px] font-semibold uppercase tracking-wider block mb-1">Perubahan</span>
                            <span class="text-lg font-bold block" x-text="selisihDisplay">0</span>
                            <span class="text-[10px] font-medium" x-text="satuanDisplay"></span>
                        </div>

                        {{-- Estimasi Stok Akhir --}}
                        <div class="bg-slate-900 text-white rounded-xl p-3.5 border border-slate-800 text-center shadow-sm">
                            <span class="text-[11px] font-semibold text-slate-300 uppercase tracking-wider block mb-1">Stok Akhir</span>
                            <span class="text-lg font-bold text-emerald-400 block" x-text="stokAkhirDisplay">0</span>
                            <span class="text-[10px] text-slate-400 font-medium" x-text="satuanDisplay"></span>
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
                            <input type="text"
                                   name="alasan"
                                   id="alasan"
                                   required
                                   x-model="alasan"
                                   list="alasan-list"
                                   placeholder="Contoh: Bahan rusak, Bahan tumpah, Selisih hitung"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white">
                            
                            <datalist id="alasan-list">
                                <option value="Bahan rusak / busuk">
                                <option value="Bahan tumpah / tercecer">
                                <option value="Bahan kadaluarsa">
                                <option value="Selisih hitung opname">
                                <option value="Temuan sisa fisik lebih">
                                <option value="Koreksi pemakaian manual">
                                <option value="Bonus / penambahan ekstra">
                            </datalist>
                        </div>

                        {{-- Quick Alasan Chips --}}
                        <div>
                            <span class="text-xs text-gray-500 font-medium block mb-2">Pilihan Cepat Alasan:</span>
                            <div class="flex flex-wrap gap-1.5">
                                <button type="button" @click="alasan = 'Bahan rusak / busuk'" class="px-2.5 py-1 text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition">Bahan Rusak</button>
                                <button type="button" @click="alasan = 'Bahan tumpah / tercecer'" class="px-2.5 py-1 text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition">Bahan Tumpah</button>
                                <button type="button" @click="alasan = 'Bahan kadaluarsa'" class="px-2.5 py-1 text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition">Kadaluarsa</button>
                                <button type="button" @click="alasan = 'Selisih hitung opname'" class="px-2.5 py-1 text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition">Selisih Opname</button>
                                <button type="button" @click="alasan = 'Temuan sisa fisik lebih'" class="px-2.5 py-1 text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition">Fisik Lebih</button>
                            </div>
                        </div>

                        {{-- 7. Catatan (Opsional) --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-800 mb-2">Catatan Tambahan <span class="text-xs font-normal text-gray-400">(Opsional)</span></label>
                            <textarea name="catatan" rows="4" placeholder="Contoh: Sebagian bahan tumpah saat penataan di ruang dapur..." class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white resize-none">{{ old('catatan') }}</textarea>
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
    function penyesuaianForm() {
        return {
            openDropdown: false,
            search: '',
            selectedId: '{{ old('bahan_baku_id') }}',
            selectedName: '',
            activeItem: null,
            jenisPersediaan: '{{ old('jenis_persediaan', 'harian') }}',
            tipeAksi: '{{ old('tipe_aksi', 'kurang') }}',
            jumlahInput: '{{ old('jumlah_input', '') }}',
            alasan: '{{ old('alasan', '') }}',
            stokSistem: 0,
            stokSistemDisplay: '0',
            selisihDisplay: '0',
            stokAkhirDisplay: '0',
            satuanDisplay: '',
            items: [
                @foreach($bahanBakus as $b)
                    @php
                        $harian = (float)($b->stok_harian?->jumlah_stok ?? 0);
                        $catering = (float)($b->stok_catering_balance?->jumlah_stok ?? 0);
                        $satuan = $b->satuan->singkatan ?? $b->satuan->nama_satuan ?? 'gram';
                    @endphp
                    {
                        id: '{{ $b->id }}',
                        nama: '{{ addslashes($b->nama_bahan) }}',
                        kode: '{{ $b->id_bahan_baku }}',
                        harian: {{ $harian }},
                        catering: {{ $catering }},
                        harian_fmt: '{{ \App\Helpers\UnitHelper::formatQuantity($harian, $satuan) }}',
                        catering_fmt: '{{ \App\Helpers\UnitHelper::formatQuantity($catering, $satuan) }}',
                        satuan: '{{ addslashes($satuan) }}'
                    },
                @endforeach
            ],
            init() {
                if (this.selectedId) {
                    const found = this.items.find(i => i.id == this.selectedId);
                    if (found) {
                        this.selectItem(found);
                    }
                }
            },
            get filteredItems() {
                if (!this.search || this.search === this.selectedName) return this.items;
                const q = this.search.toLowerCase();
                return this.items.filter(i => i.nama.toLowerCase().includes(q) || i.kode.toLowerCase().includes(q));
            },
            selectItem(item) {
                this.activeItem = item;
                this.selectedId = item.id;
                this.selectedName = item.nama;
                this.search = this.selectedName;
                this.openDropdown = false;
                this.satuanDisplay = item.satuan;
                this.updateStokSistem();
                this.recalculate();
            },
            onJenisChange() {
                this.updateStokSistem();
                this.recalculate();
            },
            updateStokSistem() {
                if (!this.activeItem) {
                    this.stokSistem = 0;
                    this.stokSistemDisplay = '0';
                    return;
                }
                this.stokSistem = this.jenisPersediaan === 'catering' ? parseFloat(this.activeItem.catering || 0) : parseFloat(this.activeItem.harian || 0);
                this.stokSistemDisplay = this.stokSistem.toLocaleString('id-ID', { maximumFractionDigits: 3 });
            },
            setTipeAksi(tipe) {
                this.tipeAksi = tipe;
                this.recalculate();
            },
            recalculate() {
                if (!this.activeItem) {
                    this.selisihDisplay = '0';
                    this.stokAkhirDisplay = '0';
                    return;
                }

                const inputVal = parseFloat(this.jumlahInput);
                if (isNaN(inputVal) || this.jumlahInput === '') {
                    this.selisihDisplay = '0';
                    this.stokAkhirDisplay = this.stokSistem.toLocaleString('id-ID', { maximumFractionDigits: 3 });
                    return;
                }

                let akhir = 0;
                let selisih = 0;

                if (this.tipeAksi === 'kurang') {
                    akhir = Math.max(0, this.stokSistem - inputVal);
                    selisih = akhir - this.stokSistem;
                } else if (this.tipeAksi === 'tambah') {
                    akhir = this.stokSistem + inputVal;
                    selisih = inputVal;
                } else { // opname / set
                    akhir = inputVal;
                    selisih = akhir - this.stokSistem;
                }

                this.selisihDisplay = (selisih > 0 ? '+' : '') + selisih.toLocaleString('id-ID', { maximumFractionDigits: 3 });
                this.stokAkhirDisplay = akhir.toLocaleString('id-ID', { maximumFractionDigits: 3 });
            },
            handleSubmit(e) {
                if (!this.selectedId) {
                    e.preventDefault();
                    alert('Silakan pilih bahan baku terlebih dahulu.');
                    return false;
                }
            }
        };
    }
</script>
@endsection

