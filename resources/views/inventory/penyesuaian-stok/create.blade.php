@extends('layouts.pos')
@section('title', 'Buat Penyesuaian Stok')

@section('content')
<div class="px-6 py-8 md:px-10 md:py-10">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
                <a href="{{ route('penyesuaian-stok.index') }}" class="hover:text-[#3B82F6] transition">Penyesuaian Stok</a>
                <span>/</span>
                <span>Buat Baru</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Buat Penyesuaian Stok</h1>
            <p class="text-sm text-gray-500 mt-1">Masukkan jumlah fisik aktual setiap bahan baku setelah opname</p>
        </div>
        <a href="{{ route('penyesuaian-stok.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 text-sm font-semibold rounded-3xl hover:bg-gray-200 transition">
            &larr; Kembali
        </a>
    </div>

    <x-ui.alert />

    <div class="bg-amber-50 border border-amber-200 rounded-[2.25rem] p-4 mb-6 flex items-start gap-3">
        <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        <p class="text-sm text-amber-700">Isi <strong>Jumlah Fisik</strong> sesuai stok nyata yang Anda hitung. Kolom yang tidak diubah tidak akan mempengaruhi stok. Sistem akan otomatis menghitung selisih dan memperbarui stok.</p>
    </div>

    <form action="{{ route('penyesuaian-stok.store') }}" method="POST" id="form-penyesuaian">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Left: Info -->
            <div class="lg:col-span-1 space-y-4">
                <div class="bg-white rounded-[2.25rem] border border-gray-100 shadow-sm p-5 space-y-4">
                    <h2 class="text-sm font-semibold text-gray-700 border-b border-gray-100 pb-3">Info Penyesuaian</h2>
                    <div>
                        <label class="text-xs font-semibold text-gray-600 mb-1.5 block">Alasan / Keterangan *</label>
                        <textarea name="alasan" rows="4" required class="w-full border border-gray-200 rounded-3xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] bg-gray-50 resize-none" placeholder="Contoh: Opname fisik gudang, Barang rusak karena banjir...">{{ old('alasan') }}</textarea>
                    </div>
                    <button type="submit" class="w-full py-2.5 bg-[#3B82F6] text-white text-sm font-semibold rounded-3xl hover:bg-blue-700 transition">
                        Simpan & Perbarui Stok
                    </button>
                </div>
                <div class="bg-blue-50 border border-blue-200 rounded-[2.25rem] p-4">
                    <p class="text-xs font-semibold text-blue-800 mb-1">ℹ️ Cara Penggunaan</p>
                    <ul class="text-xs text-blue-700 space-y-1 list-disc list-inside">
                        <li>Isi kolom <strong>Jumlah Fisik</strong> sesuai hitungan nyata</li>
                        <li>Biarkan kosong jika bahan tidak perlu disesuaikan</li>
                        <li>Setelah simpan, stok sistem akan langsung diperbarui</li>
                    </ul>
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
                                        <input type="number" name="jumlah_fisik[]" step="0.001" min="0"
                                            placeholder="{{ number_format($stokSistem, 3) }}"
                                            class="w-28 border border-gray-200 rounded-2xl px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] bg-gray-50"
                                            onchange="hitungSelisih(this, {{ $stokSistem }})">
                                        <span class="selisih-label ml-1 text-xs font-bold hidden"></span>
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
