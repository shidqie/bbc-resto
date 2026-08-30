@extends('layouts.pos')
@section('title', 'Permintaan Catering')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800 pb-10">
    <div class="w-full p-6 space-y-5">

        <x-ui.page-header
            title="Permintaan Catering"
            subtitle="Buat permintaan pembelian bahan baku berdasarkan pesanan catering. Kebutuhan dihitung dari resep BOM dikali jumlah porsi yang dipesan."
            :breadcrumbs="['Pengadaan', 'Permintaan Catering', 'Buat']">
        </x-ui.page-header>

        <x-ui.alert />

        @if($error)
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700 flex items-start gap-2">
            <x-heroicon-o-exclamation-triangle class="w-5 h-5 shrink-0" />
            {{ $error }}
        </div>
        @endif

        @if(isset($resepBelumLengkap) && $resepBelumLengkap)
        <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl text-amber-900 space-y-1.5 shadow-xs">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <h4 class="font-bold text-xs uppercase tracking-wide text-amber-800">Status: Resep Belum Lengkap</h4>
            </div>
            <p class="text-xs text-amber-800">
                Menu berikut belum memiliki resep/BOM di master resep:
                <span class="font-bold text-amber-950">{{ implode(', ', $missingMenus) }}</span>. Lengkapi resep agar perhitungan otomatis akurat.
            </p>
        </div>
        @endif

        @if(! $pesanan)
            {{-- Pilih / cari pesanan --}}
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                    <h3 class="font-bold text-gray-900 text-sm tracking-tight">Pilih Pesanan Catering</h3>
                </div>
                <div class="p-5">
                    <form action="{{ route('pengadaan.catering.create') }}" method="GET" class="flex flex-col sm:flex-row gap-3 items-end">
                        <div class="w-full">
                            <label class="block text-xs font-semibold text-gray-500 mb-1">ID Pesanan</label>
                            <input type="text" name="kode_pesanan" list="daftarPesananList" placeholder="Contoh: CTR-20260807-001" required class="w-full border border-gray-200 text-gray-900 text-sm rounded-lg px-3 py-2 outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                            <datalist id="daftarPesananList">
                                @foreach($daftarPesanan as $dp)
                                    <option value="{{ $dp->id_pesanan }}">
                                @endforeach
                            </datalist>
                            <p class="text-xs text-gray-400 mt-1">Ketik atau pilih ID pesanan, lalu sistem menghitung kebutuhan bahan baku dari resep.</p>
                        </div>
                        <button type="submit" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-emerald-600 rounded-lg px-4 py-2 hover:bg-emerald-700 transition-colors shrink-0">
                            <x-heroicon-o-calculator class="w-4 h-4" />
                            Hitung Kebutuhan
                        </button>
                    </form>

                    @if($daftarPesanan->isNotEmpty())
                    <div class="mt-5">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Pesanan Catering Tersedia</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                            @foreach($daftarPesanan as $dp)
                            <a href="{{ route('pengadaan.catering.create', ['pesanan_id' => $dp->id]) }}" class="flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50 hover:bg-emerald-50 hover:border-emerald-300 px-3 py-2 transition-colors">
                                <span class="font-mono font-bold text-gray-800 text-xs">{{ $dp->id_pesanan }}</span>
                                <span class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($dp->tanggal_pesanan)->translatedFormat('d M Y') }}</span>
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        @else
            <form action="{{ route('pengadaan.catering.store') }}" method="POST" class="space-y-5">
                @csrf
                <input type="hidden" name="pesanan_id" value="{{ $pesanan->id }}">

                <div class="grid grid-cols-1 lg:grid-cols-4 gap-5">

                    {{-- KIRI: Informasi Permintaan --}}
                    <div class="lg:col-span-1 space-y-5">
                        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                            <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                                <h3 class="font-bold text-gray-900 text-sm tracking-tight">Informasi Permintaan</h3>
                            </div>
                            <div class="p-4 space-y-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Kode Permintaan</label>
                                    <input type="text" readonly value="{{ $kodePreview }}" class="w-full bg-gray-50 border border-gray-200 text-gray-600 text-sm rounded-lg px-3 py-2 font-mono font-bold cursor-not-allowed">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">ID Pesanan</label>
                                    <input type="text" readonly value="{{ $pesanan->id_pesanan }}" class="w-full bg-gray-50 border border-gray-200 text-gray-600 text-sm rounded-lg px-3 py-2 font-mono font-bold cursor-not-allowed">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Tanggal Pesanan</label>
                                    <input type="text" readonly value="{{ \Carbon\Carbon::parse($pesanan->tanggal_pesanan)->format('d/m/Y H:i') }}" class="w-full bg-gray-50 border border-gray-200 text-gray-600 text-sm rounded-lg px-3 py-2 cursor-not-allowed">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Tanggal Permintaan</label>
                                    <input type="date" name="tanggal_pengadaan" value="{{ date('Y-m-d') }}" class="w-full border border-gray-200 text-gray-900 text-sm rounded-lg px-3 py-2 outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500" required>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Dibuat Oleh</label>
                                    <input type="text" readonly value="{{ auth()->user()->nama ?? 'Admin' }}" class="w-full bg-gray-50 border border-gray-200 text-gray-600 text-sm rounded-lg px-3 py-2 cursor-not-allowed">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Catatan</label>
                                    <textarea name="catatan" rows="3" placeholder="Tambahkan catatan jika perlu..." class="w-full border border-gray-200 text-gray-900 text-sm rounded-lg px-3 py-2 outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- KANAN: Daftar Bahan Baku --}}
                    <div class="lg:col-span-3 space-y-4">
                        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                            <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 flex justify-between items-center">
                                <h3 class="font-bold text-gray-900 text-sm tracking-tight">Daftar Bahan Baku Kurang ({{ $items->count() }} item wajib dibeli)</h3>
                            </div>

                            @if($items->isEmpty())
                                <div class="px-6 py-12 text-center">
                                    <x-heroicon-o-check-circle class="w-10 h-10 mx-auto mb-2 text-emerald-500" />
                                    <p class="text-sm font-bold text-gray-800">Semua bahan baku sudah mencukupi di stok!</p>
                                    <p class="text-xs mt-1 text-gray-500">Tidak ada kekurangan bahan baku yang perlu diajukan permintaan pengadaan.</p>
                                </div>
                            @else
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wide bg-white">
                                            <th class="px-4 py-3 text-left w-12">No</th>
                                            <th class="px-4 py-3 text-left">Bahan Baku</th>
                                            <th class="px-4 py-3 text-right">Kebutuhan BOM</th>
                                            <th class="px-4 py-3 text-right">Stok Saat Ini</th>
                                            <th class="px-4 py-3 text-right w-36">Jumlah Beli (Kurang)</th>
                                            <th class="px-4 py-3 text-left">Satuan</th>
                                            <th class="px-4 py-3 text-center w-16">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-50" id="bahanTableBody">
                                        @foreach($items as $i => $item)
                                        @php
                                            $bahanId = $item->id;
                                            $namaBahan = $item->nama_bahan;
                                            $kodeBahan = $item->id_bahan_baku;
                                            $stokSaatIni = (float) $item->stok_saat_ini;
                                            $kebutuhan = (float) $item->kebutuhan_total;
                                            $kekurangan = (float) $item->kekurangan;
                                            $satuanStr = optional($item->satuan)->singkatan ?? optional($item->satuan)->nama_satuan ?? '-';
                                        @endphp
                                        <tr class="hover:bg-gray-50/60 transition-colors group" data-stok="{{ $stokSaatIni }}">
                                            <td class="px-4 py-3 text-sm text-gray-500 font-medium align-middle row-number">{{ $i + 1 }}</td>
                                            <td class="px-4 py-3 align-middle">
                                                <p class="font-bold text-gray-900 text-sm">{{ $namaBahan }}</p>
                                                <p class="text-xs text-gray-400 font-mono mt-0.5">{{ $kodeBahan }}</p>
                                                @if(!empty($item->menu_nama))
                                                    <p class="text-[11px] text-emerald-700 font-medium mt-0.5">Menu: {{ $item->menu_nama }}</p>
                                                @endif
                                                <input type="hidden" name="bahan_id[]" value="{{ $bahanId }}">
                                            </td>
                                            <td class="px-4 py-3 text-right font-semibold text-gray-900 align-middle">
                                                {{ number_format($kebutuhan, fmod($kebutuhan, 1) === 0.0 ? 0 : 2, ',', '.') }}
                                            </td>
                                            <td class="px-4 py-3 text-right font-medium text-gray-500 align-middle">
                                                {{ number_format($stokSaatIni, fmod($stokSaatIni, 1) === 0.0 ? 0 : 2, ',', '.') }}
                                            </td>
                                            <td class="px-4 py-3 align-middle">
                                                <input type="text" class="jumlah-input w-full text-right border border-gray-200 text-gray-900 font-bold text-sm rounded-lg px-2 py-1.5 outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500" name="jumlah[]" value="{{ $kekurangan }}" required>
                                            </td>
                                            <td class="px-4 py-3 align-middle">
                                                <span class="text-xs font-semibold text-gray-600 bg-gray-100 px-2 py-1 rounded-md">{{ $satuanStr }}</span>
                                            </td>
                                            <td class="px-4 py-3 text-center align-middle">
                                                <button type="button" class="w-7 h-7 rounded-full flex items-center justify-center bg-rose-50 text-rose-600 hover:bg-rose-100 transition-colors mx-auto btn-remove-row">
                                                    <x-heroicon-o-trash class="w-3.5 h-3.5" />
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @endif
                        </div>

                        {{-- Card Transparansi Stok Cukup --}}
                        @if(isset($itemsCukup) && $itemsCukup->isNotEmpty())
                        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden" x-data="{ open: false }">
                            <button type="button" @click="open = !open" class="w-full px-4 py-3 bg-gray-50 hover:bg-gray-100 border-b border-gray-200 flex items-center justify-between text-left transition-colors">
                                <span class="text-xs font-bold text-gray-700">Bahan Baku dengan Stok Mencukupi ({{ $itemsCukup->count() }} item - Tidak Perlu Dipesan)</span>
                                <span class="text-xs text-gray-500 font-medium" x-text="open ? 'Sembunyikan' : 'Lihat Rincian'"></span>
                            </button>
                            <div x-show="open" class="p-4 space-y-2" style="display: none;">
                                <div class="overflow-x-auto">
                                    <table class="w-full text-left text-xs">
                                        <thead>
                                            <tr class="border-b border-gray-200 text-gray-500 font-bold uppercase">
                                                <th class="py-2 px-3">Bahan Baku</th>
                                                <th class="py-2 px-3 text-right">Kebutuhan BOM</th>
                                                <th class="py-2 px-3 text-right">Stok Tersedia</th>
                                                <th class="py-2 px-3 text-center">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            @foreach($itemsCukup as $ic)
                                            @php
                                                $satuanStr = optional($ic->satuan)->singkatan ?? optional($ic->satuan)->nama_satuan ?? '-';
                                            @endphp
                                            <tr>
                                                <td class="py-2 px-3 font-semibold text-gray-800">{{ $ic->nama_bahan }}</td>
                                                <td class="py-2 px-3 text-right font-bold text-gray-700">{{ number_format($ic->kebutuhan_total, fmod($ic->kebutuhan_total, 1) === 0.0 ? 0 : 2, ',', '.') }} {{ $satuanStr }}</td>
                                                <td class="py-2 px-3 text-right font-bold text-emerald-700">{{ number_format($ic->stok_saat_ini, fmod($ic->stok_saat_ini, 1) === 0.0 ? 0 : 2, ',', '.') }} {{ $satuanStr }}</td>
                                                <td class="py-2 px-3 text-center">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-50 text-emerald-700">Stok Cukup</span>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($items->isNotEmpty())
                        <div class="flex items-center gap-3 justify-end mt-4">
                            <a href="{{ route('pengadaan.catering.create') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-gray-700 bg-gray-100 rounded-lg px-4 py-2 hover:bg-gray-200 transition-colors">
                                Batal
                            </a>
                            <button type="submit" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-emerald-600 rounded-lg px-4 py-2 hover:bg-emerald-700 transition-colors">
                                <x-heroicon-o-check class="w-4 h-4" />
                                Simpan Permintaan
                            </button>
                        </div>
                        @endif
                    </div>
                </div>
            </form>
        @endif

    </div>
</div>

@push('scripts')
<script>
    const tbody = document.getElementById('bahanTableBody');
    if (tbody) {
        function updateRowNumbers() {
            tbody.querySelectorAll('tr').forEach((row, index) => {
                const noTd = row.querySelector('.row-number');
                if (noTd) noTd.textContent = index + 1;
            });
        }
        tbody.querySelectorAll('.btn-remove-row').forEach(btn => {
            btn.addEventListener('click', function() {
                const tr = this.closest('tr');
                if (tr) tr.remove();
                updateRowNumbers();
            });
        });
    }
</script>
@endpush
@endsection
