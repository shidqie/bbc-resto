@extends('layouts.pos')
@section('title', 'Buat Penyesuaian Stok')

@section('content')
<div class="px-6 py-8 md:px-10 md:py-10">
    {{-- PAGE HEADER --}}
    <x-ui.page-header
        title="Buat Penyesuaian Stok"
        subtitle="Masukkan jumlah fisik aktual setiap bahan baku setelah opname"
        :breadcrumbs="['Persediaan', 'Penyesuaian Stok', 'Buat Baru']"
        class="mb-8">
        <x-slot:actions>
            <a href="{{ route('penyesuaian-stok.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 text-sm font-semibold rounded-3xl hover:bg-gray-200 transition">
                &larr; Kembali
            </a>
        </x-slot:actions>
    </x-ui.page-header>


    <form action="{{ route('penyesuaian-stok.store') }}" method="POST" id="form-penyesuaian">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Left: Info -->
            <div class="lg:col-span-1 space-y-4">
                <div class="bg-white rounded-[2.25rem] border border-gray-100 shadow-sm p-5 space-y-4">
                    <h2 class="text-sm font-semibold text-gray-700 border-b border-gray-100 pb-3">Info Penyesuaian</h2>
                    <div>
                        <label class="text-xs font-semibold text-gray-600 mb-1.5 block">Alasan / Keterangan *</label>
                        <textarea name="alasan" rows="4" required class="w-full border border-gray-200 rounded-3xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] bg-gray-50 resize-none" placeholder="Masukkan alasan penyesuaian stok...">{{ old('alasan') }}</textarea>
                    </div>
                    <button type="submit" class="w-full py-2.5 bg-[#3B82F6] text-white text-sm font-semibold rounded-3xl hover:bg-blue-700 transition">
                        Simpan & Perbarui Stok
                    </button>
                </div>
                
            </div>

            <!-- Right: Tabel Bahan Baku -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-[2.25rem] border border-gray-100 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                        <h2 class="text-sm font-semibold text-gray-700">Daftar Bahan Baku</h2>
                        <input type="text" id="search-bahan" placeholder="Cari bahan baku..." class="border border-gray-200 rounded-3xl px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] min-w-[200px]">
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="border-b border-gray-100">
                                    <th class="px-4 py-3 text-[11px] font-bold text-gray-500 uppercase tracking-wider">Bahan Baku</th>
                                    <th class="px-4 py-3 text-[11px] font-bold text-gray-500 uppercase tracking-wider text-right">Stok Sistem</th>
                                    <th class="px-4 py-3 text-[11px] font-bold text-gray-500 uppercase tracking-wider">Jumlah Fisik</th>
                                    <th class="px-4 py-3 text-[11px] font-bold text-gray-500 uppercase tracking-wider">Catatan Item</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50" id="bahan-table-body">
                                @foreach($bahanBakus as $i => $bahan)
                                @php $stokSistem = $bahan->stok_bahan_baku?->jumlah_stok ?? 0; @endphp
                                <tr class="bahan-row hover:bg-gray-50/40 transition-colors" data-nama="{{ strtolower($bahan->nama_bahan) }}">
                                    <td class="px-4 py-3">
                                        <input type="hidden" name="bahan_baku_id[]" value="{{ $bahan->id }}">
                                        <div class="font-semibold text-sm text-gray-800">{{ $bahan->nama_bahan }}</div>
                                        <div class="text-xs text-gray-500">{{ $bahan->satuan->nama_satuan ?? '-' }} &bull; {{ $bahan->kategori_bahan_baku->nama_kategori ?? '-' }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <span class="text-sm font-semibold text-gray-800">{{ number_format($stokSistem, 2, ',', '.') }}</span>
                                        <span class="text-xs text-gray-500 ml-1">{{ $bahan->satuan->nama_satuan ?? '' }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center">
                                            <div x-data="{ val: '', format(v) { let c = String(v).replace(/[^0-9,]/g, ''); let p = c.split(','); if(p.length > 2) c = p[0] + ',' + p.slice(1).join(''); this.val = c; $refs.hidden.value = c.replace(',', '.'); } }" x-init="format(val)">
                                                <input type="text" x-model="val" @input="format($event.target.value); hitungSelisih($refs.hidden, {{ $stokSistem }})" placeholder="{{ number_format($stokSistem, 2, ',', '.') }}" class="w-28 border border-gray-200 rounded-2xl px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] bg-gray-50 text-right">
                                                <input type="hidden" x-ref="hidden" name="jumlah_fisik[]">
                                            </div>
                                            <span class="selisih-label ml-2 text-xs font-bold hidden"></span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="text" name="catatan_item[]" placeholder="Opsional..."
                                            class="w-full border border-gray-200 rounded-2xl px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] bg-gray-50">
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    function hitungSelisih(input, stokSistem) {
        const label = input.parentElement.querySelector('.selisih-label');
        const val = parseFloat(input.value);
        if (isNaN(val)) { label.classList.add('hidden'); return; }
        const selisih = val - stokSistem;
        label.classList.remove('hidden', 'text-green-600', 'text-red-600', 'text-gray-500');
        if (selisih > 0) {
            label.textContent = '+' + selisih.toFixed(2);
            label.classList.add('text-green-600');
        } else if (selisih < 0) {
            label.textContent = selisih.toFixed(2);
            label.classList.add('text-red-600');
        } else {
            label.textContent = '±0';
            label.classList.add('text-gray-500');
        }
    }

    document.getElementById('search-bahan').addEventListener('input', function () {
        const q = this.value.toLowerCase();
        document.querySelectorAll('.bahan-row').forEach(row => {
            row.style.display = row.dataset.nama.includes(q) ? '' : 'none';
        });
    });
</script>
@endsection
