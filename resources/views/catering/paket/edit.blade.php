<x-app-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <h2 class="text-2xl font-bold text-gray-900">Edit Paket: {{ $paketCatering->nama_paket }}</h2>

            <form action="{{ route('paket-catering.update', $paketCatering) }}" method="POST" class="space-y-6">
                @csrf @method('PUT')

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Paket</label>
                            <input type="text" name="nama_paket" value="{{ old('nama_paket', $paketCatering->nama_paket) }}" required class="w-full rounded-lg border-gray-300 focus:border-primary focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Paket</label>
                            <select name="jenis_paket" required class="w-full rounded-lg border-gray-300 focus:border-primary focus:ring-primary">
                                <option value="catering" {{ $paketCatering->jenis_paket === 'catering' ? 'selected' : '' }}>Catering</option>
                                <option value="nasi_box" {{ $paketCatering->jenis_paket === 'nasi_box' ? 'selected' : '' }}>Nasi Box</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Harga per Porsi (Rp)</label>
                        <input type="number" name="harga" value="{{ old('harga', $paketCatering->harga) }}" required min="0" class="w-full rounded-lg border-gray-300 focus:border-primary focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                        <textarea name="deskripsi" rows="3" class="w-full rounded-lg border-gray-300 focus:border-primary focus:ring-primary">{{ old('deskripsi', $paketCatering->deskripsi) }}</textarea>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6" x-data="bomForm()">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Komposisi Bahan (per 1 porsi)</h3>
                    
                    <template x-for="(item, index) in items" :key="index">
                        <div class="flex gap-3 items-end mb-3">
                            <div class="flex-1">
                                <select :name="'bahan_baku_id[' + index + ']'" x-model="item.bahan_baku_id" required class="w-full rounded-lg border-gray-300 focus:border-primary focus:ring-primary text-sm">
                                    <option value="">— Pilih —</option>
                                    @foreach($bahanBakus as $bahan)
                                    <option value="{{ $bahan->id }}">{{ $bahan->nama_bahan }} ({{ $bahan->satuan->nama_satuan }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="w-32">
                                <input type="number" step="0.01" :name="'jumlah_kebutuhan[' + index + ']'" x-model="item.jumlah_kebutuhan" required min="0.01" class="w-full rounded-lg border-gray-300 focus:border-primary focus:ring-primary text-sm">
                            </div>
                            <button type="button" @click="removeItem(index)" x-show="items.length > 1" class="p-2 text-red-500 hover:bg-red-50 rounded-lg">
                                <i class="fa-solid fa-times"></i>
                            </button>
                        </div>
                    </template>

                    <button type="button" @click="addItem()" class="mt-2 text-sm text-primary hover:text-orange-700 font-medium">
                        <i class="fa-solid fa-plus mr-1"></i> Tambah Bahan
                    </button>
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('paket-catering.index') }}" class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">Batal</a>
                    <button type="submit" class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-orange-700 transition-colors">Perbarui Paket</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function bomForm() {
        return {
            items: @json($paketCatering->detailBahan->map(fn($d) => ['bahan_baku_id' => (string)$d->bahan_baku_id, 'jumlah_kebutuhan' => (string)$d->jumlah_kebutuhan])->values()),
            addItem() { this.items.push({ bahan_baku_id: '', jumlah_kebutuhan: '' }); },
            removeItem(index) { this.items.splice(index, 1); }
        }
    }
    </script>
</x-app-layout>
