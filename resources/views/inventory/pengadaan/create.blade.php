@extends('layouts.pos')
@section('content')
<div class="flex-1 overflow-auto bg-gray-50/50">
    <div class="w-full p-6 space-y-6">
        @php $isHarian = request('tipe') === 'harian'; $isPesanan = request()->has('pesanan_id'); @endphp
        <x-ui.page-header 
            :title="$isHarian ? 'Pengadaan Harian (Reguler)' : ($isPesanan ? 'Pengadaan Berdasarkan Pesanan' : 'Buat Pengadaan Baru')"
            :breadcrumbs="['Pengadaan', $isHarian ? 'Harian' : 'Baru']">
            <x-slot:actions>
                <a href="{{ route('pengadaan.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 text-sm font-semibold rounded-3xl hover:bg-gray-200 transition">
                    &larr; Kembali
                </a>
            </x-slot:actions>
        </x-ui.page-header>
        <x-ui.alert />
        @if($isHarian && count($prefillItems) === 0)
        <div class="bg-green-50 border border-green-200 rounded-[2.25rem] p-4 text-sm text-green-700 font-medium">
            ✅ Semua stok bahan baku reguler masih mencukupi. Tidak ada pengadaan yang diperlukan hari ini.
        </div>
        @else
        <form action="{{ route('pengadaan.store') }}" method="POST" id="form-pengadaan">
            @csrf
            @if(request()->has('pesanan_id'))
                <input type="hidden" name="jenis_pengadaan" value="CATERING">
            @endif
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Info Pengadaan --}}
                <div class="lg:col-span-1 space-y-4">
                    <div class="bg-white rounded-[2.25rem] border border-gray-100 shadow-sm p-5 space-y-4">
                        <h2 class="text-sm font-semibold text-gray-700 border-b border-gray-100 pb-3">Info Pengadaan</h2>
                        <div>
                            <label class="text-xs font-semibold text-gray-600 mb-1.5 block">Tanggal Pengadaan *</label>
                            <input type="date" name="tanggal_pengadaan" value="{{ date('Y-m-d') }}" required class="w-full border border-gray-200 rounded-3xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] bg-gray-50">
                        </div>
                        @if(!request()->has('pesanan_id'))
                        <div>
                            <label class="text-xs font-semibold text-gray-600 mb-1.5 block">Peruntukan Stok *</label>
                            <select name="jenis_pengadaan" required class="w-full border border-gray-200 rounded-3xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] bg-gray-50">
                                <option value="REGULER" {{ $isHarian ? 'selected' : '' }}>Reguler (Semua Kebutuhan)</option>
                                <option value="DINE IN">Khusus Dine In</option>
                                <option value="NASI BOX">Khusus Nasi Box</option>
                                <option value="CATERING">Khusus Catering</option>
                            </select>
                        </div>
                        @endif
                        <div>
                            <label class="text-xs font-semibold text-gray-600 mb-1.5 block">Nama Pemasok *</label>
                            <input type="text" name="nama_pemasok" required class="w-full border border-gray-200 rounded-3xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] bg-gray-50" placeholder="Masukkan nama pemasok...">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-600 mb-1.5 block">Catatan</label>
                            <textarea name="catatan" rows="3" class="w-full border border-gray-200 rounded-3xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] bg-gray-50" placeholder="Catatan pengadaan...">{{ $isHarian ? 'Pengadaan Harian Reguler - ' . date('d/m/Y') : '' }}</textarea>
                        </div>
                        <button type="submit" class="w-full py-2.5 bg-[#3B82F6] text-white text-sm font-semibold rounded-3xl hover:bg-blue-700 transition">
                            Simpan Pengadaan
                        </button>
                    </div>
                    @if($isHarian)
                    <div class="bg-amber-50 border border-amber-200 rounded-[2.25rem] p-4">
                        <p class="text-xs font-semibold text-amber-700 mb-1">ℹ️ Pengadaan Harian</p>
                        <p class="text-xs text-amber-600">Sistem telah otomatis memuat bahan baku yang stoknya di bawah minimum. Anda bisa mengedit jumlah sebelum menyimpan.</p>
                    </div>
                    @endif
                    @if($isPesanan)
                    <div class="bg-purple-50 border border-purple-200 rounded-[2.25rem] p-4">
                        <p class="text-xs font-semibold text-purple-700 mb-1">📋 Pengadaan Berdasarkan Pesanan</p>
                        <p class="text-xs text-purple-600">Sistem memuat bahan baku yang KURANG untuk memenuhi pesanan ini. Stok yang sudah ada sudah dikurangkan dari kebutuhan.</p>
                    </div>
                    @endif
                </div>
                {{-- Tabel Bahan Baku --}}
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-[2.25rem] border border-gray-100 shadow-sm overflow-hidden">
                        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                            <h2 class="text-sm font-semibold text-gray-700">Daftar Bahan yang Dibeli</h2>
                            <button type="button" onclick="tambahBaris()" class="text-xs font-semibold text-blue-600 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-2xl transition">
                                + Tambah Bahan
                            </button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm" id="tabel-bahan">
                                <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                                    <tr>
                                        <th class="px-4 py-2.5 text-left">Bahan Baku</th>
                                        @if($isHarian)
                                        <th class="px-4 py-2.5 text-left text-orange-600">Stok / Min</th>
                                        @elseif($isPesanan)
                                        <th class="px-4 py-2.5 text-left text-purple-600">Keterangan</th>
                                        @endif
                                        <th class="px-4 py-2.5 text-left">Jumlah Beli</th>
                                        <th class="px-4 py-2.5 text-center">Hapus</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-bahan">
                                    @forelse($prefillItems as $idx => $item)
                                    @php $bahan = $bahanBakus->firstWhere('id', $item['bahan_baku_id']); @endphp
                                    <tr class="baris-bahan border-b border-gray-50">
                                        <td class="px-4 py-3">
                                            <select name="bahan_baku_id[]" required class="w-full border border-gray-200 rounded-3xl px-3 py-2 text-sm focus:outline-none focus:border-[#3B82F6] bg-gray-50">
                                                <option value="">Pilih Bahan</option>
                                                @foreach($bahanBakus as $bb)
                                                <option value="{{ $bb->id }}" {{ $bb->id == $item['bahan_baku_id'] ? 'selected' : '' }}>
                                                    {{ $bb->nama_bahan }} ({{ $bb->satuan?->singkatan }})
                                                </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        @if($isHarian || $isPesanan)
                                        <td class="px-4 py-3 text-xs text-gray-500 font-medium whitespace-nowrap">
                                            {{ $item['keterangan_tambahan'] ?? '' }}
                                        </td>
                                        @endif
                                        <td class="px-4 py-3">
                                            <input type="number" name="jumlah[]" value="{{ $item['jumlah_beli'] }}" step="0.01" min="0.01" required class="w-full border border-gray-200 rounded-3xl px-3 py-2 text-sm focus:outline-none focus:border-[#3B82F6] bg-gray-50">
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <button type="button" onclick="hapusBaris(this)" class="text-red-400 hover:text-red-600 transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr class="baris-bahan border-b border-gray-50">
                                        <td class="px-4 py-3">
                                            <select name="bahan_baku_id[]" required class="w-full border border-gray-200 rounded-3xl px-3 py-2 text-sm focus:outline-none focus:border-[#3B82F6] bg-gray-50">
                                                <option value="">Pilih Bahan</option>
                                                @foreach($bahanBakus as $bb)
                                                <option value="{{ $bb->id }}">{{ $bb->nama_bahan }} ({{ $bb->satuan?->singkatan }})</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        @if($isHarian || $isPesanan)
                                        <td class="px-4 py-3 text-xs text-gray-400">-</td>
                                        @endif
                                        <td class="px-4 py-3"><input type="number" name="jumlah[]" value="1" step="0.01" min="0.01" required class="w-full border border-gray-200 rounded-3xl px-3 py-2 text-sm focus:outline-none focus:border-[#3B82F6] bg-gray-50"></td>
                                        <td class="px-4 py-3 text-center"><button type="button" onclick="hapusBaris(this)" class="text-red-400 hover:text-red-600 transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button></td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        @endif
    </div>
</div>
<script>
const bahanOptions = `@foreach($bahanBakus as $bb)<option value="{{ $bb->id }}">{{ $bb->nama_bahan }} ({{ $bb->satuan?->singkatan }})</option>@endforeach`;
const showExtra = {{ ($isHarian || $isPesanan) ? 'true' : 'false' }};

function tambahBaris() {
    const tbody = document.getElementById('tbody-bahan');
    const tr = document.createElement('tr');
    tr.className = 'baris-bahan border-b border-gray-50';
    tr.innerHTML = `<td class="px-4 py-3"><select name="bahan_baku_id[]" required class="w-full border border-gray-200 rounded-3xl px-3 py-2 text-sm focus:outline-none focus:border-[#3B82F6] bg-gray-50"><option value="">Pilih Bahan</option>${bahanOptions}</select></td>`
        + (showExtra ? `<td class="px-4 py-3 text-xs text-gray-400">-</td>` : '')
        + `<td class="px-4 py-3"><input type="number" name="jumlah[]" value="1" step="0.01" min="0.01" required class="w-full border border-gray-200 rounded-3xl px-3 py-2 text-sm focus:outline-none focus:border-[#3B82F6] bg-gray-50"></td>
           <td class="px-4 py-3 text-center"><button type="button" onclick="hapusBaris(this)" class="text-red-400 hover:text-red-600 transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button></td>`;
    tbody.appendChild(tr);
}
function hapusBaris(btn) {
    const rows = document.querySelectorAll('.baris-bahan');
    if (rows.length > 1) btn.closest('tr').remove();
}
</script>
@endsection
