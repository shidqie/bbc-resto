@props(['existingKomponen' => [], 'readonly' => false, 'menus' => []])

<style>[x-cloak]{display:none!important}</style>

<div x-data="paketBuilder({{ \Illuminate\Support\Js::from($existingKomponen) }}, {{ $readonly ? 'true' : 'false' }}, {{ \Illuminate\Support\Js::from($menus) }})"
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

    <div class="border border-gray-200 rounded-xl bg-white overflow-hidden shadow-2xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 font-semibold text-center w-24">No / Urut</th>
                        <th class="px-4 py-3 font-semibold min-w-[200px]">Nama Item Menu</th>
                        <th class="px-4 py-3 font-semibold text-center w-28">Takaran</th>
                        <th class="px-4 py-3 font-semibold text-center w-36">Tipe Pilihan</th>
                        <th class="px-4 py-3 font-semibold min-w-[250px]">Pilihan Menu</th>
                        <th class="px-4 py-3 font-semibold text-center w-20" x-show="!readonly">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <template x-for="(k, i) in komponens" :key="i">
                        <!-- Main Row -->
                        <tr class="hover:bg-gray-50/30 transition-colors bg-white group" x-show="!k.confirming">
                            
                            <!-- 1. Urutan -->
                            <td class="px-4 py-3 align-top">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" @click="move(i, -1)" :disabled="i === 0" :class="i === 0 ? 'opacity-30 cursor-not-allowed' : 'hover:bg-gray-200'" class="w-6 h-6 rounded flex items-center justify-center text-gray-500 transition-colors" x-show="!readonly" title="Naikkan urutan">
                                        <x-heroicon-o-chevron-up class="w-3.5 h-3.5" />
                                    </button>
                                    <span class="w-7 h-7 rounded-lg bg-[#0D3024] text-white text-xs font-black flex items-center justify-center shrink-0" x-text="i + 1"></span>
                                    <button type="button" @click="move(i, 1)" :disabled="i === komponens.length - 1" :class="i === komponens.length - 1 ? 'opacity-30 cursor-not-allowed' : 'hover:bg-gray-200'" class="w-6 h-6 rounded flex items-center justify-center text-gray-500 transition-colors" x-show="!readonly" title="Turunkan urutan">
                                        <x-heroicon-o-chevron-down class="w-3.5 h-3.5" />
                                    </button>
                                </div>
                                <input type="hidden" :name="'komponen[' + i + '][urutan]'" :value="i + 1" :disabled="readonly">
                            </td>

                            <!-- 2. Nama Item -->
                            <td class="px-4 py-3 align-top">
                                <div x-show="readonly" class="font-bold text-gray-800" x-text="k.nama"></div>
                                <div x-show="!readonly">
                                    <template x-if="k.tipe === 'fixed'">
                                        <select x-model="k.menu_id" :name="'komponen[' + i + '][menu_id]'" x-bind:required="k.tipe === 'fixed'" class="w-full text-sm font-bold px-3 py-2 border border-gray-200 bg-white rounded-lg focus:border-[#0D3024] focus:ring-1 focus:ring-[#0D3024]/10 outline-none">
                                            <option value="">-- Pilih Menu --</option>
                                            <template x-for="m in menus" :key="m.id">
                                                <option :value="m.id" x-text="m.nama_menu" :selected="k.menu_id == m.id"></option>
                                            </template>
                                        </select>
                                    </template>
                                    <template x-if="k.tipe === 'choice'">
                                        <input type="text" x-model="k.nama" :name="'komponen[' + i + '][nama_komponen]'" x-bind:required="k.tipe === 'choice'" placeholder="Cth: Lauk Utama" class="w-full text-sm font-bold px-3 py-2 border border-gray-200 bg-white rounded-lg focus:border-[#0D3024] focus:ring-1 focus:ring-[#0D3024]/10 outline-none">
                                    </template>
                                </div>
                            </td>

                            <!-- 3. Takaran -->
                            <td class="px-4 py-3 align-top">
                                <div x-show="readonly" class="text-gray-800 text-center font-medium" x-text="k.jumlah"></div>
                                <div x-show="!readonly">
                                    <input type="number" step="0.01" min="0.01" x-model="k.jumlah" :name="'komponen[' + i + '][jumlah]'" required placeholder="1" class="w-full text-center text-sm font-bold px-2 py-2 border border-gray-200 bg-white rounded-lg focus:border-[#0D3024] focus:ring-1 focus:ring-[#0D3024]/10 outline-none">
                                </div>
                            </td>

                            <!-- 4. Tipe Pilihan -->
                            <td class="px-4 py-3 align-top">
                                <div x-show="readonly" class="text-center pt-1.5">
                                    <span :class="k.tipe === 'fixed' ? 'bg-gray-100 text-gray-600 border-gray-200' : 'bg-blue-50 text-blue-700 border-blue-200'" class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border" x-text="k.tipe === 'fixed' ? 'Pasti Dapat' : 'Pilihan Konsumen'"></span>
                                </div>
                                <div x-show="!readonly" class="flex justify-center">
                                    <input type="hidden" :name="'komponen[' + i + '][tipe]'" :value="k.tipe">
                                    <div class="flex flex-col gap-1.5 w-full">
                                        <button type="button" @click="k.tipe = 'choice'" :class="k.tipe === 'choice' ? 'bg-[#0D3024] text-white border-[#0D3024]' : 'bg-gray-50 text-gray-500 border-gray-200 hover:bg-gray-100'" class="px-2 py-1.5 rounded-lg text-xs font-bold transition-colors border w-full text-center">Pilih 1 (Pilihan)</button>
                                        <button type="button" @click="k.tipe = 'fixed'" :class="k.tipe === 'fixed' ? 'bg-[#0D3024] text-white border-[#0D3024]' : 'bg-gray-50 text-gray-500 border-gray-200 hover:bg-gray-100'" class="px-2 py-1.5 rounded-lg text-xs font-bold transition-colors border w-full text-center">Pasti Dapat (Wajib)</button>
                                    </div>
                                </div>
                            </td>

                            <!-- 5. Pilihan Menu -->
                            <td class="px-4 py-3 align-top">
                                <div x-show="k.tipe === 'fixed'" class="text-xs text-gray-400 italic py-2">
                                    Hanya 1 menu utama (pilihan di sebelah kiri).
                                </div>
                                <div x-show="k.tipe === 'choice'">
                                    <div x-show="k.opsi.length" class="flex flex-wrap gap-1.5 mb-2.5">
                                        <template x-for="(o, oi) in k.opsi" :key="oi">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-50 border border-emerald-200 text-[#0D3024] rounded-full text-xs font-bold shadow-2xs">
                                                <span x-text="o.nama"></span>
                                                <template x-if="!readonly">
                                                    <button type="button" @click="rmOpsi(k, oi)" class="text-emerald-700 hover:text-red-600 transition-colors bg-white/50 hover:bg-white rounded-full p-0.5"><x-heroicon-o-x-mark class="w-3 h-3" /></button>
                                                </template>
                                            </span>
                                        </template>
                                    </div>
                                    <div class="flex gap-1.5" x-show="!readonly">
                                        <select x-model="k.newOpsiMenuId" class="flex-1 min-w-0 text-xs px-2.5 py-2 border border-gray-200 bg-white rounded-lg outline-none focus:border-[#0D3024] focus:ring-1 focus:ring-[#0D3024]/10 font-medium">
                                            <option value="">-- Tambah Menu Opsi --</option>
                                            <template x-for="m in menus" :key="m.id">
                                                <option :value="m.id" x-text="m.nama_menu"></option>
                                            </template>
                                        </select>
                                        <button type="button" @click="addOpsi(k)" class="shrink-0 px-3 py-2 rounded-lg border border-emerald-200 text-xs font-bold text-emerald-800 bg-emerald-100 hover:bg-emerald-200 transition-colors shadow-2xs"><x-heroicon-o-plus class="w-4 h-4" /></button>
                                    </div>
                                </div>
                                <template x-for="(o, oi) in k.opsi" :key="oi">
                                    <input type="hidden" :name="'komponen[' + i + '][pilihan][]'" :value="o.id" :disabled="readonly">
                                </template>
                            </td>

                            <!-- 6. Aksi -->
                            <td class="px-4 py-3 align-top text-center" x-show="!readonly">
                                <button type="button" @click="k.confirming = true" class="p-2 mt-0.5 rounded-lg text-red-500 hover:text-red-700 hover:bg-red-50 transition-colors mx-auto block" title="Hapus baris">
                                    <x-heroicon-o-trash class="w-5 h-5" />
                                </button>
                            </td>

                        </tr>

                        <!-- Confirm Delete Row -->
                        <tr x-show="k.confirming && !readonly" x-cloak class="bg-red-50 border-y border-red-100">
                            <td colspan="6" class="px-5 py-3">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-bold text-red-700">Yakin ingin menghapus baris item menu <span class="italic underline" x-text="k.nama || (k.tipe === 'fixed' ? 'Pasti Dapat' : 'baru')"></span> ini?</p>
                                    <div class="flex gap-2">
                                        <button type="button" @click="k.confirming = false" class="px-3 py-1.5 rounded-lg text-xs font-bold text-gray-700 bg-white border border-gray-300 hover:bg-gray-100 transition-colors">Batal</button>
                                        <button type="button" @click="remove(i)" class="px-3 py-1.5 rounded-lg text-xs font-black text-white bg-red-600 hover:bg-red-700 transition-colors shadow-2xs inline-flex items-center gap-1"><x-heroicon-o-trash class="w-3.5 h-3.5" /> Ya, Hapus</button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div x-show="komponens.length === 0" x-cloak class="text-center py-10 bg-gray-50/50">
            <p class="text-sm font-semibold text-gray-500">Belum ada item menu.</p>
            <p class="text-xs text-gray-400 mt-1">Klik "Tambah Item Menu" di atas untuk menambahkan komponen paket.</p>
        </div>
    </div>

</div>

<script>
function paketBuilder(existing = [], readonly = false, menus = []) {
    const normalize = (e) => ({
        nama: e.nama_komponen || e.nama_item || '',
        tipe: (e.tipe_komponen || e.tipe_item) === 'tetap' ? 'fixed' : 'choice',
        menu_id: e.menu_id_terkait || '',
        jumlah: e.jumlah || 1,
        opsi: (e.opsi || []).map(o => ({
            id: o.menu_id || '',
            nama: o.menu ? o.menu.nama_menu : o.nama_pilihan
        })).filter(o => o.nama),
        newOpsiMenuId: '',
        confirming: false,
    });

    const komponens = (existing || []).map(normalize);
    if (komponens.length === 0 && !readonly) komponens.push(normalize({}));

    return {
        komponens,
        readonly: !!readonly,
        menus: menus,
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
            this.komponens.push({ nama: '', tipe: 'choice', menu_id: '', jumlah: 1, opsi: [], newOpsiMenuId: '', confirming: false });
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
            const menuId = k.newOpsiMenuId;
            if (!menuId) return;
            
            // Cek jika sudah ada
            if (k.opsi.find(o => o.id == menuId)) {
                k.newOpsiMenuId = '';
                return;
            }
            
            const menu = this.menus.find(m => m.id == menuId);
            if (menu) {
                k.opsi.push({ id: menu.id, nama: menu.nama_menu });
            }
            k.newOpsiMenuId = '';
        },
        rmOpsi(k, oi) {
            k.opsi.splice(oi, 1);
        },
    };
}
</script>
