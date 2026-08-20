@props(['existingKomponen' => [], 'readonly' => false, 'menus' => []])

<style>[x-cloak]{display:none!important}</style>

<div x-data="paketBuilder({{ \Illuminate\Support\Js::from($existingKomponen) }}, {{ $readonly ? 'true' : 'false' }}, {{ \Illuminate\Support\Js::from($menus) }})"
     @set-komponens.window="setFromExisting($event.detail)"
     @set-readonly.window="setReadonly($event.detail)"
     class="space-y-4">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-100 pb-3">
        <div>
            <h3 class="text-sm font-extrabold text-gray-900">Item Paket</h3>
            <p class="text-xs text-gray-500">Susun menu yang otomatis didapat, menu pilihan, atau kelompok menu.</p>
        </div>
        <button type="button" @click="add()" x-show="!readonly"
                class="shrink-0 inline-flex items-center gap-1.5 px-4 py-2 bg-[#0D3024] hover:bg-[#0D3024]/90 text-white border border-[#0D3024] rounded-xl text-sm font-bold transition-all shadow-sm">
            <x-heroicon-o-plus class="w-4 h-4" /> Tambah Item Paket
        </button>
    </div>

    <div class="space-y-4">
        <template x-for="(k, i) in komponens" :key="i">
            <div class="relative">
                <!-- Main Card -->
                <div class="flex flex-col sm:flex-row items-start gap-3 p-4 bg-white border border-gray-200 rounded-xl shadow-sm" x-show="!k.confirming">
                    
                    <!-- 1. Urutan & Mover -->
                    <div class="flex flex-col items-center gap-1 mt-1 shrink-0">
                        <button type="button" @click="move(i, -1)" :disabled="i === 0" :class="i === 0 ? 'opacity-30 cursor-not-allowed' : 'hover:bg-gray-100 text-gray-500'" class="w-6 h-6 rounded flex items-center justify-center transition-colors" x-show="!readonly" title="Naikkan">
                            <x-heroicon-o-chevron-up class="w-4 h-4" />
                        </button>
                        <span class="w-7 h-7 rounded-full bg-gray-100 text-gray-700 text-xs font-bold flex items-center justify-center border border-gray-200" x-text="i + 1"></span>
                        <button type="button" @click="move(i, 1)" :disabled="i === komponens.length - 1" :class="i === komponens.length - 1 ? 'opacity-30 cursor-not-allowed' : 'hover:bg-gray-100 text-gray-500'" class="w-6 h-6 rounded flex items-center justify-center transition-colors" x-show="!readonly" title="Turunkan">
                            <x-heroicon-o-chevron-down class="w-4 h-4" />
                        </button>
                        <input type="hidden" :name="'komponen[' + i + '][urutan]'" :value="i + 1" :disabled="readonly">
                    </div>

                    <!-- 2. Content Area -->
                    <div class="flex-1 w-full min-w-0 flex flex-col gap-4">
                        <!-- Top Row: Nama & Tipe -->
                        <div class="flex flex-col sm:flex-row gap-4">
                            <div class="flex-1">
                                <label class="block text-xs font-semibold text-gray-500 mb-1">Nama Kelompok <span class="text-red-500">*</span></label>
                                <div x-show="readonly" class="font-bold text-gray-800" x-text="k.nama"></div>
                                <input x-show="!readonly" type="text" x-model="k.nama" :name="'komponen[' + i + '][nama_komponen]'" required placeholder="Cth: Nasi, Lauk Utama, Minuman" class="w-full text-sm font-semibold px-3 py-2 border border-gray-200 bg-white rounded-lg focus:border-primary focus:ring-1 focus:ring-primary/20 outline-none transition-shadow">
                            </div>
                            
                            <div class="sm:w-64 shrink-0">
                                <label class="block text-xs font-semibold text-gray-500 mb-1">Tipe Kelompok <span class="text-red-500">*</span></label>
                                <div x-show="readonly" class="pt-1.5">
                                    <span :class="k.tipe === 'wajib' ? 'bg-gray-100 text-gray-700' : (k.tipe === 'semua_didapat' ? 'bg-indigo-50 text-indigo-700' : 'bg-blue-50 text-blue-700')" class="px-3 py-1 rounded-full text-xs font-bold border" x-text="k.tipe === 'wajib' ? 'Wajib (1 Menu)' : (k.tipe === 'semua_didapat' ? 'Semua Didapat' : 'Pilihan Konsumen')"></span>
                                </div>
                                <div x-show="!readonly" class="flex p-0.5 bg-gray-100 rounded-lg border border-gray-200/60">
                                    <button type="button" @click="k.tipe = 'wajib'" :class="k.tipe === 'wajib' ? 'bg-white text-gray-900 shadow-sm border border-gray-200/60' : 'text-gray-500 hover:text-gray-700'" class="flex-1 px-2 py-1.5 rounded-md text-[11px] font-bold transition-all">Wajib</button>
                                    <button type="button" @click="k.tipe = 'pilihan'" :class="k.tipe === 'pilihan' ? 'bg-white text-blue-700 shadow-sm border border-blue-100' : 'text-gray-500 hover:text-gray-700'" class="flex-1 px-2 py-1.5 rounded-md text-[11px] font-bold transition-all">Pilihan</button>
                                    <button type="button" @click="k.tipe = 'semua_didapat'" :class="k.tipe === 'semua_didapat' ? 'bg-white text-indigo-700 shadow-sm border border-indigo-100' : 'text-gray-500 hover:text-gray-700'" class="flex-1 px-2 py-1.5 rounded-md text-[11px] font-bold transition-all whitespace-nowrap">Semua Didapat</button>
                                </div>
                                <input type="hidden" :name="'komponen[' + i + '][tipe]'" :value="k.tipe">
                            </div>
                        </div>

                        <!-- Main Config Area -->
                        <div class="bg-gray-50/50 rounded-xl p-4 border border-gray-100">
                            
                            <!-- Item Type: Wajib / Semua Didapat -->
                            <div x-show="k.tipe !== 'pilihan'" class="flex items-start gap-4">
                                <div class="flex-1">
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wider">Nama Menu / Item</label>
                                    <!-- TEXT INPUT INSTEAD OF DROPDOWN -->
                                    <input type="text" x-model="k.nama_item_manual" placeholder="Ketik nama menu..." class="w-full text-sm px-3 py-2 border border-gray-200 bg-white rounded-lg outline-none focus:border-primary focus:ring-1 focus:ring-primary/20">
                                    <input type="hidden" :name="'komponen[' + i + '][nama_item_manual]'" :value="k.nama_item_manual">
                                    <input type="hidden" :name="'komponen[' + i + '][menu_id]'" value="">
                                    
                                    <p class="text-xs text-gray-500 mt-2 flex items-center gap-1">
                                        <x-heroicon-s-information-circle class="w-4 h-4 text-primary" />
                                        Menu yang diketik di sini akan otomatis masuk ke dalam paket.
                                    </p>
                                </div>
                            </div>

                            <!-- Item Type: Pilihan -->
                            <div x-show="k.tipe === 'pilihan'">
                                <label class="block text-xs font-semibold text-gray-700 mb-2 uppercase tracking-wider">Daftar Pilihan Menu</label>
                                
                                <!-- Opsi List Grid -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                                    <template x-for="(o, oi) in k.opsi" :key="oi">
                                        <div class="group relative flex items-center gap-3 p-2 bg-white border border-gray-200 rounded-lg shadow-sm hover:border-gray-300 transition-colors">
                                            <input type="hidden" :name="'komponen[' + i + '][pilihan][' + oi + ']'" :value="o.nama">
                                            
                                            <div class="flex-1 min-w-0 pr-8">
                                                <input type="text" x-model="o.nama" required placeholder="Nama pilihan..." class="w-full text-sm font-semibold px-2 py-1.5 border border-transparent rounded bg-transparent focus:border-gray-200 focus:bg-gray-50 outline-none transition-colors">
                                            </div>
                                            
                                            <!-- Delete Button -->
                                            <button type="button" @click="rmOpsi(i, oi)" class="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 flex items-center justify-center rounded-md text-gray-300 hover:text-red-600 hover:bg-red-50 transition-colors shrink-0" title="Hapus">
                                                <x-heroicon-o-trash class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </template>
                                </div>

                                <!-- Input Add -->
                                <div class="flex gap-2 mb-3 max-w-sm">
                                    <input type="text" x-model="komponens[i].newOpsiText" @keydown.enter.prevent="addOpsi(i)" placeholder="Ketik nama pilihan menu..." class="flex-1 text-sm px-3 py-2 border border-gray-200 bg-white rounded-lg outline-none focus:border-primary focus:ring-1 focus:ring-primary/20">
                                    <button type="button" @click="addOpsi(i)" class="shrink-0 px-4 py-2 rounded-lg border border-gray-200 text-sm font-semibold text-gray-700 bg-white hover:bg-gray-50 transition-colors shadow-sm">
                                        Tambah Pilihan
                                    </button>
                                </div>

                                <!-- Min/Max -->
                                <div class="flex items-center gap-4 mt-4 pt-3 border-t border-gray-200/60">
                                    <div class="flex items-center gap-2">
                                        <label class="text-xs font-medium text-gray-500">Minimal Pilih:</label>
                                        <input type="number" x-model="k.min_pilihan" :name="'komponen[' + i + '][min_pilihan]'" min="1" class="w-16 px-2 py-1 text-sm border border-gray-200 rounded outline-none text-center">
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <label class="text-xs font-medium text-gray-500">Maksimal Pilih:</label>
                                        <input type="number" x-model="k.max_pilihan" :name="'komponen[' + i + '][max_pilihan]'" min="1" class="w-16 px-2 py-1 text-sm border border-gray-200 rounded outline-none text-center">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Delete Row -->
                    <button type="button" @click="k.confirming = true" class="shrink-0 w-7 h-7 flex items-center justify-center rounded-lg text-gray-300 hover:text-red-600 hover:bg-red-50 transition-colors" title="Hapus item paket">
                        <x-heroicon-o-trash class="w-4 h-4" />
                    </button>
                </div>

                <!-- Confirm Delete Overlay -->
                <div x-show="k.confirming" x-cloak class="absolute inset-0 bg-white/95 rounded-xl flex flex-col sm:flex-row items-center justify-center gap-3 z-10 p-4 border border-red-200 shadow-sm backdrop-blur-[2px]">
                    <p class="text-sm text-gray-700">Hapus kelompok menu <span class="font-semibold" x-text="k.nama || 'ini'"></span>?</p>
                    <div class="flex gap-2">
                        <button type="button" @click="k.confirming = false" class="px-3 py-1.5 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 transition-colors">Batal</button>
                        <button type="button" @click="remove(i)" class="px-3 py-1.5 rounded-lg text-sm font-semibold text-white bg-red-600 hover:bg-red-700 transition-colors">Hapus</button>
                    </div>
                </div>
            </div>
        </template>

        <div x-show="komponens.length === 0" x-cloak class="text-center py-10 bg-gray-50 border border-dashed border-gray-200 rounded-xl">
            <p class="text-sm font-medium text-gray-500">Belum ada item paket.</p>
            <p class="text-xs text-gray-400 mt-1">Klik "Tambah Item Paket" untuk mulai menyusun paket.</p>
        </div>
    </div>
</div>

<script>
function paketBuilder(existing = [], readonly = false, menus = []) {
    const normalize = (e) => {
        let menuNama = '';
        if (e.menu_id_terkait) {
            const found = menus.find(m => m.id == e.menu_id_terkait);
            if (found) menuNama = found.nama_menu;
        }

        let tipe = e.tipe_komponen || e.tipe_item || 'wajib';
        if (tipe === 'tetap') tipe = 'wajib';

        return {
            nama: e.nama_komponen || e.nama_item || '',
            tipe: tipe,
            menu_id: e.menu_id_terkait || '',
            nama_item_manual: (!e.menu_id_terkait && e.nama_item) ? e.nama_item : (menuNama || ''),
            min_pilihan: e.minimum_pilihan || 1,
            max_pilihan: e.maksimum_pilihan || 1,
            opsi: (e.opsi || e.pilihan || []).map(o => ({
                id: '',
                nama: o.nama_pilihan || (o.menu ? o.menu.nama_menu : '') || o.nama || '',
            })).filter(o => o.nama),
            newOpsiText: '',
            confirming: false,
        };
    };

    const komponens = (existing || []).map(normalize);
    if (komponens.length === 0 && !readonly) komponens.push(normalize({}));

    return {
        komponens,
        readonly: !!readonly,
        menus: menus || [],
        setFromExisting(existing) {
            this.komponens = (existing || []).map(normalize);
            if (this.komponens.length === 0 && !this.readonly) this.komponens.push(normalize({}));
        },
        setReadonly(v) {
            this.readonly = !!v;
            if (this.readonly) {
                this.komponens.forEach(k => { k.confirming = false; });
            }
        },
        add() {
            this.komponens.push({ nama: '', tipe: 'wajib', menu_id: '', nama_item_manual: '', min_pilihan: 1, max_pilihan: 1, opsi: [], newOpsiText: '', confirming: false });
        },
        remove(i) {
            this.komponens.splice(i, 1);
        },
        move(i, dir) {
            const j = i + dir;
            if (j < 0 || j >= this.komponens.length) return;
            const tmp = this.komponens[i];
            this.komponens[i] = this.komponens[j];
            this.komponens[j] = tmp;
        },
        addOpsi(i) {
            const k = this.komponens[i];
            const text = k.newOpsiText;
            if (!text || !text.trim()) return;

            if (k.opsi.find(o => o.nama.toLowerCase() === text.trim().toLowerCase())) {
                k.newOpsiText = '';
                return;
            }

            k.opsi.push({ id: '', nama: text.trim() });
            k.newOpsiText = '';
        },
        rmOpsi(i, oi) {
            this.komponens[i].opsi.splice(oi, 1);
        }
    };
}
</script>
