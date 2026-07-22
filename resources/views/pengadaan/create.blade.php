{{-- 
    Halaman: Buat Pengadaan Bahan Baku (Minimalist Edition)
    Desain: Clean, Elegant, Minimalist, High-Contrast, Ergonomis
--}}
@extends('layouts.pos')

@section('content')
<div class="flex-1 overflow-auto bg-gray-50/50 text-gray-800 font-sans">
    <div class="p-4 md:p-6 lg:p-8 max-w-[1250px] mx-auto space-y-6">
        
        {{-- Header --}}
        <x-ui.page-header 
            title="Buat Pengadaan Bahan Baku" 
            subtitle="Hitung kebutuhan resep BOM & buat order pengadaan ke supplier"
            :breadcrumbs="['Pengadaan', 'Buat Pengadaan']">
            <x-slot:actions>
                <x-ui.button href="{{ route('pengadaan.index') }}" variant="outline" icon="fa-list-check">Riwayat Pengadaan</x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.alert />

        {{-- ── SKENARIO ALTERNATIF A1: ALERT MENU TANPA BOM ── --}}
        @if(!empty($missingBomMenus))
        <div class="bg-red-50 border border-red-200 rounded-2xl p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-2xs">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-red-600 text-white flex items-center justify-center shrink-0">
                    <x-heroicon-o-exclamation-triangle class="w-5 h-5" />
                </div>
                <div>
                    <h4 class="text-xs font-bold text-red-950">Gagal Menghitung Otomatis (Skenario A1)</h4>
                    <p class="text-xs text-red-800 mt-0.5 font-medium">
                        Terdapat <strong class="underline">{{ count($missingBomMenus) }} menu</strong> ({{ implode(', ', array_column($missingBomMenus, 'nama')) }}) yang belum diisi resep BOM-nya.
                    </p>
                </div>
            </div>
            <a href="{{ route('menu.index') }}" target="_blank" class="inline-flex items-center gap-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-bold px-3.5 py-2 rounded-xl transition-colors shrink-0">
                <x-heroicon-o-arrow-top-right-on-square class="w-3.5 h-3.5" /> Lengkapi BOM Menu
            </a>
        </div>
        @endif

        {{-- ── 1. TABEL PILIH PESANAN TERKONFIRMASI ── --}}
        <div class="bg-white rounded-2xl border border-gray-200/80 p-5 shadow-2xs space-y-4">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center border-b border-gray-100 pb-3 gap-2">
                <div>
                    <h3 class="text-sm font-bold text-gray-900">1. Pilih Pesanan Terkonfirmasi</h3>
                    <p class="text-xs text-gray-500">Pesanan Catering & Nasi Box yang siap dihitung kebutuhan bahan bakunya</p>
                </div>

                {{-- Toggle Filter Jenis Pesanan --}}
                <div class="flex items-center gap-1 bg-gray-100 p-1 rounded-xl">
                    <button type="button" onclick="switchJenisPesanan('catering')" 
                            class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ $jenisPesanan === 'catering' ? 'bg-blue-800 text-white shadow-2xs' : 'text-gray-600 hover:text-gray-900' }}">
                        Catering ({{ $pesananCaterings->count() }})
                    </button>
                    <button type="button" onclick="switchJenisPesanan('nasi_box')" 
                            class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ $jenisPesanan === 'nasi_box' ? 'bg-purple-800 text-white shadow-2xs' : 'text-gray-600 hover:text-gray-900' }}">
                        Nasi Box ({{ $pesananNasiBoxes->count() }})
                    </button>
                </div>
            </div>

            {{-- Table Seleksi Pesanan --}}
            <div class="overflow-x-auto rounded-xl border border-gray-200">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 uppercase tracking-wider border-b border-gray-200 font-semibold">
                            <th class="px-4 py-3">Kode Pesanan</th>
                            <th class="px-4 py-3">Nama Pemesan</th>
                            <th class="px-4 py-3">Jenis</th>
                            <th class="px-4 py-3">Jumlah Order</th>
                            <th class="px-4 py-3">Tanggal Acara</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @if($jenisPesanan === 'nasi_box')
                            @forelse($pesananNasiBoxes as $box)
                                <tr class="hover:bg-purple-50/30 transition-colors {{ $pesananId == $box->id ? 'bg-purple-50/60 font-bold' : '' }}">
                                    <td class="px-4 py-3 font-mono font-bold text-purple-900">
                                        {{ $box->kode_pesanan }}
                                    </td>
                                    <td class="px-4 py-3 font-bold text-gray-900">
                                        {{ $box->nama_pemesan }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-purple-100 text-purple-900 border border-purple-200">Nasi Box</span>
                                    </td>
                                    <td class="px-4 py-3 font-bold text-gray-900">
                                        {{ $box->jumlah_box }} Box
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">
                                        {{ \Carbon\Carbon::parse($box->tanggal_acara)->format('d M Y') }}
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        @if($pesananId == $box->id)
                                            <span class="px-3 py-1 bg-purple-800 text-white rounded-lg text-xs font-bold shadow-2xs">✓ Dipilih</span>
                                        @else
                                            <button onclick="onSelectPesanan('{{ $box->id }}')" class="px-3.5 py-1.5 bg-purple-50 hover:bg-purple-100 text-purple-900 border border-purple-200 rounded-lg text-xs font-bold transition-all">
                                                Pilih Pesanan &rarr;
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-400 italic">
                                        Belum ada pesanan Nasi Box terkonfirmasi.
                                    </td>
                                </tr>
                            @endforelse
                        @else
                            @forelse($pesananCaterings as $catering)
                                <tr class="hover:bg-blue-50/30 transition-colors {{ $pesananId == $catering->id ? 'bg-blue-50/60 font-bold' : '' }}">
                                    <td class="px-4 py-3 font-mono font-bold text-blue-900">
                                        {{ $catering->kode_pesanan }}
                                    </td>
                                    <td class="px-4 py-3 font-bold text-gray-900">
                                        {{ $catering->nama_pemesan }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 text-blue-900 border border-blue-200">Catering</span>
                                    </td>
                                    <td class="px-4 py-3 font-bold text-gray-900">
                                        {{ $catering->jumlah_porsi }} Porsi
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">
                                        {{ \Carbon\Carbon::parse($catering->tanggal_acara)->format('d M Y') }}
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        @if($pesananId == $catering->id)
                                            <span class="px-3 py-1 bg-blue-800 text-white rounded-lg text-xs font-bold shadow-2xs">✓ Dipilih</span>
                                        @else
                                            <button onclick="onSelectPesanan('{{ $catering->id }}')" class="px-3.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-900 border border-blue-200 rounded-lg text-xs font-bold transition-all">
                                                Pilih Pesanan &rarr;
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-400 italic">
                                        Belum ada pesanan Catering terkonfirmasi.
                                    </td>
                                </tr>
                            @endforelse
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ── 2 & 3. ANALISIS BOM & FORM ORDER (Dua Kolom) ── --}}
        @if($selectedPesanan)
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            {{-- Analisis BOM --}}
            <div class="lg:col-span-5 space-y-4">
                <div class="bg-white rounded-2xl border border-gray-200/80 p-5 shadow-2xs space-y-3">
                    <div class="border-b border-gray-100 pb-2">
                        <h3 class="text-sm font-bold text-gray-900">2. Kalkulasi BOM vs Stok Resto</h3>
                        <p class="text-xs text-gray-500">Target porsi: {{ $jenisPesanan === 'nasi_box' ? $selectedPesanan->jumlah_box . ' box' : $selectedPesanan->jumlah_porsi . ' porsi' }}</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-gray-50 text-gray-500 uppercase tracking-wider border-b border-gray-100">
                                    <th class="py-2 px-3 font-semibold">Bahan Baku</th>
                                    <th class="py-2 px-3 font-semibold">Stok Resto</th>
                                    <th class="py-2 px-3 font-semibold">Kebutuhan</th>
                                    <th class="py-2 px-3 font-semibold text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($bomAnalysis as $bom)
                                    <tr class="hover:bg-gray-50/50">
                                        <td class="py-2 px-3 font-bold text-gray-900">{{ $bom['nama_bahan'] }}</td>
                                        <td class="py-2 px-3 text-gray-600">{{ number_format($bom['stok_saat_ini'], 2, ',', '.') }} {{ $bom['satuan'] }}</td>
                                        <td class="py-2 px-3 font-bold text-blue-800">{{ number_format($bom['kebutuhan_total'], 2, ',', '.') }} {{ $bom['satuan'] }}</td>
                                        <td class="py-2 px-3 text-right">
                                            @if($bom['kekurangan'] > 0)
                                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-red-100 text-red-800 border border-red-200">
                                                    Kurang {{ number_format($bom['kekurangan'], 2, ',', '.') }}
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                    ✓ Cukup
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-4 text-center text-gray-400 italic">Tidak ada BOM terdaftar.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Form Order Supplier --}}
            <div class="lg:col-span-7">
                <form action="{{ route('pengadaan.store') }}" method="POST" id="pengadaan-form" class="bg-white rounded-2xl border border-gray-200/80 p-5 shadow-2xs space-y-4">
                    @csrf
                    <input type="hidden" name="jenis_pesanan" value="{{ $jenisPesanan }}">
                    @if($jenisPesanan === 'nasi_box')
                        <input type="hidden" name="pesanan_nasi_box_id" value="{{ $pesananId }}">
                    @else
                        <input type="hidden" name="pesanan_catering_id" value="{{ $pesananId }}">
                    @endif

                    <div class="flex justify-between items-center border-b border-gray-100 pb-2">
                        <div>
                            <h3 class="text-sm font-bold text-gray-900">3. Form Order Bahan Baku</h3>
                            <p class="text-xs text-gray-500">Disusun dari kekurangan stok hasil kalkulasi BOM</p>
                        </div>
                        <button type="button" onclick="addRow()" class="text-xs font-bold text-[#0F2E23] bg-emerald-50 border border-emerald-200 px-3 py-1.5 rounded-lg hover:bg-emerald-100 transition-colors inline-flex items-center gap-1">
                            <x-heroicon-o-plus class="w-3.5 h-3.5" /> Tambah Item
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Supplier <span class="text-red-500">*</span></label>
                            <select name="supplier_id" required class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0F2E23]/10 focus:border-[#0F2E23] outline-none text-xs font-medium">
                                <option value="">— Pilih Supplier —</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->nama_supplier }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Tanggal Pengadaan <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_pengadaan" value="{{ old('tanggal_pengadaan', date('Y-m-d')) }}" required class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0F2E23]/10 focus:border-[#0F2E23] outline-none text-xs font-semibold">
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs min-w-[450px]">
                            <thead>
                                <tr class="bg-gray-50 text-gray-500 uppercase tracking-wider border-b border-gray-100">
                                    <th class="px-2.5 py-2 font-semibold w-2/5">Bahan Baku</th>
                                    <th class="px-2.5 py-2 font-semibold w-1/4">Jumlah Order</th>
                                    <th class="px-2.5 py-2 font-semibold w-1/4">Harga Est. (Rp)</th>
                                    <th class="px-2.5 py-2 font-semibold w-1/4">Subtotal</th>
                                    <th class="px-1 py-2 font-semibold text-center w-6"></th>
                                </tr>
                            </thead>
                            <tbody id="item-container" class="divide-y divide-gray-100 text-xs">
                                {{-- Rows akan dirender via JS --}}
                            </tbody>
                            <tfoot>
                                <tr class="border-t-2 border-gray-200 bg-gray-50">
                                    <td colspan="3" class="px-3 py-2.5 text-right font-bold text-gray-900">TOTAL ESTIMASI:</td>
                                    <td class="px-3 py-2.5 font-black text-[#0F2E23] text-xs" id="total-biaya-display">Rp 0</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Catatan Order</label>
                        <textarea name="catatan" rows="2" placeholder="Instruksi pengantaran…" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0F2E23]/10 focus:border-[#0F2E23] outline-none text-xs transition-all">{{ old('catatan') }}</textarea>
                    </div>

                    <div class="pt-2 border-t flex justify-end">
                        <button type="button" onclick="submitForm()" class="px-5 py-2.5 text-white bg-[#0F2E23] hover:bg-[#0a1f17] rounded-xl font-bold text-xs transition-all shadow-sm active:scale-95 flex items-center gap-2">
                            <x-heroicon-o-paper-airplane class="w-4 h-4" /> Simpan Order & Pesan ke Supplier
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif

    </div>
</div>

<template id="row-template">
    <tr class="item-row">
        <td class="px-2 py-1.5 align-top">
            <select name="bahan_baku_id[]" required class="bahan-select w-full px-2 py-1.5 bg-gray-50 border border-gray-200 rounded-lg outline-none text-xs font-medium focus:border-[#0F2E23]" onchange="updateSatuan(this); calculateSubtotal(this);">
                <option value="">— Pilih Bahan —</option>
                @foreach($bahanBakus as $bahan)
                    <option value="{{ $bahan->id }}" data-satuan="{{ $bahan->satuan->nama_satuan ?? '' }}">
                        {{ $bahan->nama_bahan }}
                    </option>
                @endforeach
            </select>
        </td>
        <td class="px-2 py-1.5 align-top">
            <div class="flex gap-1 items-center">
                <input type="number" name="jumlah_estimasi[]" required min="0.01" step="0.01" class="jumlah-input w-full px-2 py-1.5 bg-gray-50 border border-gray-200 rounded-lg outline-none text-xs font-bold text-[#0F2E23]" oninput="calculateSubtotal(this)">
                <span class="text-[10px] text-gray-500 satuan-label font-bold min-w-[20px]">-</span>
            </div>
        </td>
        <td class="px-2 py-1.5 align-top">
            <input type="number" name="harga_estimasi[]" min="0" step="1" class="harga-input w-full px-2 py-1.5 bg-gray-50 border border-gray-200 rounded-lg outline-none text-xs font-semibold" placeholder="0" oninput="calculateSubtotal(this)">
        </td>
        <td class="px-2 py-1.5 align-top">
            <input type="text" readonly class="subtotal-display w-full px-2 py-1.5 bg-gray-100 border border-transparent rounded-lg text-gray-700 font-bold text-xs outline-none cursor-not-allowed" value="Rp 0">
            <input type="hidden" name="subtotal_hidden[]" class="subtotal-hidden" value="0">
        </td>
        <td class="px-1 py-1.5 align-top text-center pt-2">
            <button type="button" class="text-red-500 hover:text-red-700 transition-colors" onclick="removeRow(this)">&times;</button>
        </td>
    </tr>
</template>

<script>
    function switchJenisPesanan(jenis) {
        const url = new URL(window.location.href);
        url.searchParams.set('jenis_pesanan', jenis);
        url.searchParams.delete('pesanan_id');
        window.location.href = url.toString();
    }

    function onSelectPesanan(val) {
        if (!val) return;
        const jenis = '{{ $jenisPesanan }}';
        const url = new URL(window.location.href);
        url.searchParams.set('jenis_pesanan', jenis);
        url.searchParams.set('pesanan_id', val);
        window.location.href = url.toString();
    }

    function updateSatuan(select) {
        const option = select.options[select.selectedIndex];
        const satuan = option ? option.getAttribute('data-satuan') : '';
        const row = select.closest('tr');
        const label = row.querySelector('.satuan-label');
        label.textContent = satuan || '-';
    }

    function calculateSubtotal(element) {
        const row = element.closest('tr');
        const jumlah = parseFloat(row.querySelector('.jumlah-input').value) || 0;
        const harga = parseFloat(row.querySelector('.harga-input').value) || 0;
        const subtotal = jumlah * harga;
        
        row.querySelector('.subtotal-hidden').value = subtotal;
        row.querySelector('.subtotal-display').value = 'Rp ' + new Intl.NumberFormat('id-ID').format(subtotal);
        
        calculateTotal();
    }

    function calculateTotal() {
        let total = 0;
        document.querySelectorAll('.subtotal-hidden').forEach(input => {
            total += parseFloat(input.value) || 0;
        });
        
        const disp = document.getElementById('total-biaya-display');
        if (disp) {
            disp.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
        }
    }

    function addRow() {
        const container = document.getElementById('item-container');
        if (!container) return null;
        const template = document.getElementById('row-template');
        const clone = template.content.cloneNode(true);
        container.appendChild(clone);
        const rows = container.querySelectorAll('.item-row');
        return rows[rows.length - 1];
    }

    function removeRow(btn) {
        const container = document.getElementById('item-container');
        if (container.querySelectorAll('.item-row').length > 1) {
            btn.closest('tr').remove();
            calculateTotal();
        } else {
            alert('Form order pengadaan minimal harus memiliki 1 item bahan baku.');
        }
    }

    function submitForm() {
        const container = document.getElementById('item-container');
        if (!container || container.querySelectorAll('.item-row').length === 0) {
            alert('Tambahkan minimal 1 item bahan baku ke form order.');
            return;
        }
        document.getElementById('pengadaan-form').submit();
    }

    document.addEventListener('DOMContentLoaded', function() {
        const prepopulate = @json($prepopulate ?? []);
        if (prepopulate.length > 0) {
            prepopulate.forEach(item => {
                const row = addRow();
                if (row) {
                    const select = row.querySelector('.bahan-select');
                    select.value = item.bahan_baku_id;
                    updateSatuan(select);
                    
                    const jumlahInput = row.querySelector('.jumlah-input');
                    jumlahInput.value = item.jumlah;
                    
                    if (item.harga_estimasi !== undefined) {
                        const hargaInput = row.querySelector('.harga-input');
                        hargaInput.value = item.harga_estimasi;
                    }

                    calculateSubtotal(jumlahInput);
                }
            });
        } else if (document.getElementById('item-container')) {
            addRow();
        }
    });
</script>
@endsection
