@props(['existingKomponen' => [], 'readonly' => false])

<style>[x-cloak]{display:none!important}</style>

<div x-data="paketBuilder({{ \Illuminate\Support\Js::from($existingKomponen) }}, {{ $readonly ? 'true' : 'false' }})"
     @set-komponens.window="setFromExisting($event.detail)"
     @set-readonly.window="setReadonly($event.detail)"
     class="space-y-4">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-100 pb-3">
        <div>
            <h3 class="text-sm font-extrabold text-gray-900">Item Menu & Pilihan</h3>
            <p class="text-xs text-gray-500">Susun komponen paket — konsumen akan memilih sesuai tipe di bawah ini.</p>
        </div>
        <button type="button" @click="add()" x-show="!readonly"
                class="shrink-0 inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-50 hover:bg-emerald-100 text-[#0D3024] border border-emerald-200 rounded-xl text-sm font-bold transition-all shadow-2xs">
            <x-heroicon-o-plus class="w-4 h-4" /> Tambah Item Menu
        </button>
    </div>

    <template x-for="(k, i) in komponens" :key="i">
        <div class="komponen-card rounded-xl border border-gray-200/90 bg-white shadow-2xs overflow-hidden">

            <div class="flex items-center gap-2.5 px-4 py-2.5 bg-gray-50/80 border-b border-gray-100">
                <span class="w-7 h-7 rounded-lg bg-[#0D3024] text-white text-xs font-black flex items-center justify-center shrink-0" x-text="i + 1"></span>
                <span class="text-xs font-extrabold text-gray-800 truncate max-w-[180px]" x-text="k.nama || 'Item baru'"></span>
                <span :class="k.tipe === 'fixed' ? 'bg-gray-100 text-gray-600 border-gray-200' : 'bg-blue-50 text-blue-700 border-blue-200'"
                      class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border shrink-0"
                      x-text="k.tipe === 'fixed' ? 'Pasti Dapat' : 'Pilihan Konsumen'"></span>
                <div class="ml-auto flex items-center gap-1" x-show="!readonly">
                    <button type="button" @click="move(i, -1)" :disabled="i === 0"
                            :class="i === 0 ? 'opacity-30 cursor-not-allowed' : 'hover:bg-gray-200'"
                            class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-500 bg-white border border-gray-200 transition-colors" title="Naikkan urutan">
                        <x-heroicon-o-chevron-up class="w-4 h-4" />
                    </button>
                    <button type="button" @click="move(i, 1)" :disabled="i === komponens.length - 1"
                            :class="i === komponens.length - 1 ? 'opacity-30 cursor-not-allowed' : 'hover:bg-gray-200'"
                            class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-500 bg-white border border-gray-200 transition-colors" title="Turunkan urutan">
                        <x-heroicon-o-chevron-down class="w-4 h-4" />
                    </button>
                    <button type="button" @click="k.confirming = true"
                            class="w-7 h-7 rounded-lg flex items-center justify-center text-red-500 bg-red-50 border border-red-100 hover:bg-red-100 transition-colors" title="Hapus item menu">
                        <x-heroicon-o-trash class="w-4 h-4" />
                    </button>
                </div>
            </div>

            <div class="p-4 space-y-3" x-show="!k.confirming">
                <div :class="readonly ? 'grid grid-cols-1 gap-2' : 'grid grid-cols-1 md:grid-cols-2 gap-3'">
                    {{-- Read-only header row --}}
                    <div class="grid grid-cols-2 gap-4" x-show="readonly" x-cloak>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Nama Item Menu</p>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Tipe Pilihan</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4" x-show="readonly" x-cloak>
                        <p class="text-sm font-semibold text-gray-800" x-text="k.nama"></p>
                        <p class="text-sm font-semibold text-gray-800" x-text="k.tipe === 'fixed' ? 'Pasti Dapat' : 'Pilih 1'"></p>
                    </div>
                    <div x-show="!readonly">
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-1">Nama Item Menu <span class="text-red-500">*</span></label>
                        <input type="text" x-model="k.nama" :name="'komponen[' + i + '][nama_komponen]'" required
                               placeholder="Cth: Lauk Ayam / Aneka Sup"
                               class="w-full text-sm font-bold px-3.5 py-2 border border-gray-200 bg-white rounded-xl focus:border-[#0D3024] focus:ring-2 focus:ring-[#0D3024]/10 outline-none">
                    </div>
                    <div x-show="!readonly">
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-1">Tipe Pilihan</label>
                        <input type="hidden" :name="'komponen[' + i + '][tipe]'" :value="k.tipe">
                        <div class="grid grid-cols-2 gap-1 p-1 bg-gray-100 rounded-xl">
                            <button type="button" @click="k.tipe = 'choice'"
                                    :class="k.tipe === 'choice' ? 'bg-white text-[#0D3024] shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                                    class="px-2 py-1.5 rounded-lg text-xs font-bold transition-all">Pilih 1</button>
                            <button type="button" @click="k.tipe = 'fixed'"
                                    :class="k.tipe === 'fixed' ? 'bg-white text-[#0D3024] shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                                    class="px-2 py-1.5 rounded-lg text-xs font-bold transition-all">Pasti Dapat</button>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-1">"Pilih 1" = konsumen memilih satu opsi.</p>
                    </div>
                </div>

                <div x-show="k.tipe === 'choice'" x-cloak class="pt-1">
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-1.5">Pilihan Menu</label>
                    <div x-show="k.opsi.length" class="flex flex-wrap gap-1.5 mb-2">
                        <template x-for="(o, oi) in k.opsi" :key="oi">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-50 border border-emerald-200 text-[#0D3024] rounded-full text-xs font-bold">
                                <span x-text="o"></span>
                                <template x-if="!readonly">
                                    <button type="button" @click="rmOpsi(k, oi)" class="text-emerald-700 hover:text-red-600 transition-colors">
                                        <x-heroicon-o-x-mark class="w-3 h-3" />
                                    </button>
                                </template>
                            </span>
                        </template>
                    </div>
                    <div class="flex gap-2" x-show="!readonly">
                        <input type="text" x-model="k.newOpsi" @keydown.enter.prevent="addOpsi(k)"
                               placeholder="Tulis pilihan… (Enter untuk menambah)"
                               class="flex-1 min-w-0 text-sm px-3.5 py-2 border border-gray-200 bg-white rounded-xl outline-none focus:border-[#0D3024]">
                        <button type="button" @click="addOpsi(k)"
                                class="shrink-0 px-3.5 py-2 rounded-xl border border-gray-200 text-xs font-bold text-gray-600 bg-white hover:bg-gray-50 transition-colors inline-flex items-center gap-1">
                            <x-heroicon-o-plus class="w-3.5 h-3.5" /> Tambah
                        </button>
                    </div>
                    <p class="text-[10px] text-gray-400 mt-1.5" x-show="!readonly">Contoh: Ayam Goreng, Ayam Bakar — konsumen memilih 1 dari daftar ini.</p>
                </div>

                <input type="hidden" :name="'komponen[' + i + '][urutan]'" :value="i + 1" :disabled="readonly">
                <input type="hidden" :name="'komponen[' + i + '][pilihan]'" :value="k.opsi.join(', ')" :disabled="readonly">
            </div>

            <div x-show="k.confirming && !readonly" x-cloak class="px-4 py-3 bg-red-50/70 border-t border-red-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                <p class="text-xs font-bold text-red-700">Hapus item menu <span class="italic" x-text="k.nama || 'ini'"></span>?</p>
                <div class="flex items-center gap-2">
                    <button type="button" @click="k.confirming = false"
                            class="px-3 py-1.5 rounded-lg text-xs font-bold text-gray-600 bg-white border border-gray-200 hover:bg-gray-50 transition-colors">Batal</button>
                    <button type="button" @click="remove(i)"
                            class="px-3 py-1.5 rounded-lg text-xs font-black text-white bg-red-600 hover:bg-red-700 transition-colors inline-flex items-center gap-1">
                        <x-heroicon-o-trash class="w-3.5 h-3.5" /> Ya, Hapus
                    </button>
                </div>
            </div>
        </div>
    </template>

    <div x-show="komponens.length === 0" x-cloak class="text-center py-10 border-2 border-dashed border-gray-200 rounded-xl bg-gray-50/50">
        <p class="text-sm font-semibold text-gray-500">Belum ada item menu.</p>
        <p class="text-xs text-gray-400 mt-1">Klik "Tambah Item Menu" untuk menambahkan komponen paket.</p>
    </div>

</div>

<script>
function paketBuilder(existing = [], readonly = false) {
    const normalize = (e) => ({
        nama: e.nama_komponen || e.nama_item || '',
        tipe: (e.tipe_komponen || e.tipe_item) === 'tetap' ? 'fixed' : 'choice',
        opsi: (e.opsi || []).map(o => o.nama_pilihan).filter(Boolean),
        newOpsi: '',
        confirming: false,
    });

    const komponens = (existing || []).map(normalize);
    if (komponens.length === 0 && !readonly) komponens.push(normalize({}));

    return {
        komponens,
        readonly: !!readonly,
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
            this.komponens.push({ nama: '', tipe: 'choice', opsi: [], newOpsi: '', confirming: false });
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
        addOpsi(k) {
            const v = (k.newOpsi || '').trim();
            if (!v) return;
            k.opsi.push(v);
            k.newOpsi = '';
        },
        rmOpsi(k, oi) {
            k.opsi.splice(oi, 1);
        },
    };
}
</script>
