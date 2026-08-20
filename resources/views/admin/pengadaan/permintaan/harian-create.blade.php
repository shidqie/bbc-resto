@extends('layouts.pos')
@section('title', 'Permintaan ' . ucfirst($jenis) . ' — Kebutuhan Bahan')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800 pb-10">
    <div class="w-full p-6 space-y-5">

        <x-ui.page-header
            title="{{ $jenis == 'harian' ? 'Buat Permintaan Harian' : 'Buat Permintaan Catering' }}"
            subtitle="{{ $jenis == 'harian' ? 'Permintaan bahan berdasarkan stok minimum untuk operasional harian.' : 'Permintaan bahan berdasarkan kebutuhan pesanan catering.' }}"
            :breadcrumbs="['Pengadaan', $jenis == 'harian' ? 'Buat Permintaan Harian' : 'Buat Permintaan Catering']">
        </x-ui.page-header>

        <x-ui.alert />

        {{-- SUMMARY --}}
        @php
            $bahanMenipis = $bahanMenipisCount;
            $totalDipilih = count($semuaBahan);
        @endphp
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-ui.stat-card label="Bahan Menipis" :value="$bahanMenipis" icon="exclamation-triangle" color="orange" hint="Stok di bawah / di ambang batas minimum" />
            <x-ui.stat-card label="Total Bahan Tersedia" :value="$totalDipilih" icon="cube" color="brand" hint="Bahan aktif pada persediaan {{ $jenis }}" />
        </div>

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
            <form action="{{ route($formRoute) }}" method="POST" id="permintaanForm" onsubmit="return validatePermintaanForm(this)">
                @csrf
                <div class="p-5 space-y-5">

                    {{-- INFORMASI --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1">Kode Permintaan</label>
                            <input type="text" readonly value="{{ $kodePreview }}" class="w-full bg-gray-50 border border-gray-200 text-gray-600 text-sm rounded-lg px-3 py-2 font-mono font-bold cursor-not-allowed">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1">Tanggal</label>
                            <input type="date" name="tanggal_pengadaan" value="{{ date('Y-m-d') }}" required class="w-full border border-gray-200 text-gray-900 text-sm rounded-lg px-3 py-2 outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1">Jenis</label>
                            <input type="text" readonly value="{{ ucfirst($jenis) }}" class="w-full bg-gray-50 border border-gray-200 text-gray-600 text-sm rounded-lg px-3 py-2 cursor-not-allowed">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-gray-500 mb-1">Catatan</label>
                            <textarea name="catatan" rows="2" placeholder="Tambahkan catatan jika perlu..." class="w-full border border-gray-200 text-gray-900 text-sm rounded-lg px-3 py-2 outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1">Dibuat Oleh</label>
                            <input type="text" readonly value="{{ auth()->user()->nama ?? 'Admin' }}" class="w-full bg-gray-50 border border-gray-200 text-gray-600 text-sm rounded-lg px-3 py-2 cursor-not-allowed">
                        </div>
                    </div>

                    {{-- DAFTAR BAHAN --}}
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm" id="bahanTable">
                            <thead>
                                <tr class="border-b border-gray-200 text-xs font-semibold text-gray-500 uppercase tracking-wide text-left">
                                    <th class="px-2 py-3 w-10 text-center">Pilih</th>
                                    <th class="px-3 py-3">Bahan Baku</th>
                                    <th class="px-3 py-3 text-right">Stok Saat Ini</th>
                                    <th class="px-3 py-3 text-right">Stok Minimum</th>
                                    <th class="px-3 py-3 text-right">Jumlah Permintaan</th>
                                    <th class="px-3 py-3">Satuan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($semuaBahan as $idx => $stok)
                                @php $bahan = $stok->bahan_baku; @endphp
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-2 py-3 text-center align-middle">
                                        <input type="checkbox" name="bahan_id[]" value="{{ $bahan->id }}" class="bahan-checkbox rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer" checked>
                                    </td>
                                    <td class="px-3 py-3 align-middle">
                                        <p class="font-bold text-gray-900 text-sm">{{ $bahan->nama_bahan }}</p>
                                        <p class="text-xs text-gray-400 font-mono mt-0.5">{{ $bahan->id_bahan_baku }}</p>
                                    </td>
                                    <td class="px-3 py-3 text-right align-middle font-medium {{ (float)$stok->jumlah_stok <= (float)$bahan->stok_minimal ? 'text-rose-600 font-bold' : 'text-gray-700' }}">{{ $stok->jumlah_stok }}</td>
                                    <td class="px-3 py-3 text-right align-middle text-gray-600">{{ $bahan->stok_minimal }}</td>
                                    <td class="px-3 py-3 align-middle">
                                        @php $defaultJumlah = max(0, (float)$bahan->stok_minimal - (float)$stok->jumlah_stok); @endphp
                                        <input type="text" name="jumlah[{{ $bahan->id }}]" value="{{ $defaultJumlah > 0 ? $defaultJumlah : '0' }}" class="jumlah-input w-28 text-right border border-gray-200 text-gray-900 text-sm rounded-lg px-2 py-1.5 outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                                    </td>
                                    <td class="px-3 py-3 align-middle">
                                        <span class="text-xs font-semibold text-gray-500 bg-gray-100 px-2 py-1 rounded-md">{{ optional($bahan->satuan)->nama_satuan ?? '-' }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($semuaBahan->isEmpty())
                    <p class="text-sm text-center text-gray-500 py-8">Tidak ada bahan baku untuk persediaan {{ $jenis }}.</p>
                    @endif

                    {{-- FOOTER --}}
                    <div class="flex items-center justify-between pt-2">
                        <a href="{{ route('pengadaan.permintaan.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-gray-700 bg-gray-100 rounded-lg px-4 py-2 hover:bg-gray-200 transition-colors">
                            Batal
                        </a>
                        <button type="submit" id="btnSubmitForm" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-emerald-600 rounded-lg px-4 py-2 hover:bg-emerald-700 transition-colors">
                            <x-heroicon-o-check class="w-4 h-4" />
                            Simpan Permintaan
                        </button>
                    </div>
                </div>
            </form>
        </div>

    </div>
</div>

@push('scripts')
<script>
    function validatePermintaanForm(form) {
        const checked = document.querySelectorAll('.bahan-checkbox:checked');
        if (checked.length === 0) {
            window.showToast('warning', 'Pilih minimal satu bahan baku.');
            return false;
        }
        let anyPositive = false;
        checked.forEach(cb => {
            const row = cb.closest('tr');
            const jml = row.querySelector('.jumlah-input');
            if (jml && parseFloat(jml.value.replace(',', '.')) > 0) anyPositive = true;
        });
        if (!anyPositive) {
            window.showToast('warning', 'Jumlah permintaan harus lebih dari 0 untuk minimal satu bahan.');
            return false;
        }
        
        const btn = document.getElementById('btnSubmitForm');
        if(btn) {
            btn.innerHTML = '<x-heroicon-o-check class=\"w-4 h-4 animate-spin\" /> Menyimpan...';
            btn.disabled = true;
        }
        return true;
    }
</script>
@endpush
@endsection