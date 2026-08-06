@extends('layouts.pos')
@section('title', 'Terima Bahan')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800 pb-10">
    <div class="w-full p-6 space-y-5">

        <x-ui.page-header
            title="Terima Bahan"
            subtitle="Catat penerimaan bahan baku berdasarkan permintaan yang telah dibuat."
            :breadcrumbs="['Pengadaan', 'Penerimaan Bahan Baku', 'Terima Bahan']">
        </x-ui.page-header>

        <x-ui.alert />

        @if(! $pengadaan)
            {{-- Pilih permintaan --}}
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                    <h3 class="font-bold text-gray-900 text-sm tracking-tight">Pilih Kode Permintaan</h3>
                </div>
                <div class="p-5">
                    @if($pilihan->isEmpty())
                        <x-empty-state icon="clipboard" title="Tidak ada permintaan" message="Tidak ada permintaan yang menunggu penerimaan." :colspan="1" />
                    @else
                        <form action="{{ route('pengadaan.penerimaan.create') }}" method="GET" class="flex flex-col sm:flex-row gap-3 items-end">
                            <div class="w-full">
                                <label class="block text-xs font-semibold text-gray-500 mb-1">Kode Permintaan</label>
                                <select name="permintaan" required class="w-full border border-gray-200 text-gray-900 text-sm rounded-lg px-3 py-2 outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                                    <option value="">-- Pilih Permintaan --</option>
                                    @foreach($pilihan as $p)
                                        <option value="{{ $p->id }}">
                                            {{ $p->nomor_pengadaan }} — {{ $p->detail_pengadaan_bahan->count() }} bahan ({{ ucfirst($p->jenis_pengadaan) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-emerald-600 rounded-lg px-4 py-2 hover:bg-emerald-700 transition-colors shrink-0">
                                <x-heroicon-o-arrow-right class="w-4 h-4" />
                                Lanjut
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @else
            <form action="{{ route('pengadaan.penerimaan.store') }}" method="POST" class="space-y-5">
                @csrf
                <input type="hidden" name="pengadaan_bahan_id" value="{{ $pengadaan->id }}">

                <div class="grid grid-cols-1 lg:grid-cols-4 gap-5">
                    <div class="lg:col-span-1 space-y-5">
                        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                            <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                                <h3 class="font-bold text-gray-900 text-sm tracking-tight">Informasi Penerimaan</h3>
                            </div>
                            <div class="p-4 space-y-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Kode Penerimaan</label>
                                    <input type="text" readonly value="{{ $kodePenerimaan }}" class="w-full bg-gray-50 border border-gray-200 text-gray-600 text-sm rounded-lg px-3 py-2 font-mono font-bold cursor-not-allowed">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Kode Permintaan</label>
                                    <input type="text" readonly value="{{ $pengadaan->nomor_pengadaan }}" class="w-full bg-gray-50 border border-gray-200 text-gray-600 text-sm rounded-lg px-3 py-2 font-mono font-bold cursor-not-allowed">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Tanggal Penerimaan</label>
                                    <input type="date" name="tanggal_penerimaan" value="{{ date('Y-m-d') }}" class="w-full border border-gray-200 text-gray-900 text-sm rounded-lg px-3 py-2 outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Nama Supplier</label>
                                    <input type="text" name="supplier" placeholder="Nama supplier" class="w-full border border-gray-200 text-gray-900 text-sm rounded-lg px-3 py-2 outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500" required>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Nomor Nota</label>
                                    <input type="text" name="nomor_nota" placeholder="Nomor nota pembelian" class="w-full border border-gray-200 text-gray-900 text-sm rounded-lg px-3 py-2 outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Catatan</label>
                                    <textarea name="catatan" rows="2" class="w-full border border-gray-200 text-gray-900 text-sm rounded-lg px-3 py-2 outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"></textarea>
                                </div>
                                <p class="text-xs text-gray-400 leading-relaxed">Setelah disimpan, verifikasi penerimaan pada daftar untuk menambah stok bahan baku.</p>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-3">
                        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                            <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 flex justify-between items-center">
                                <h3 class="font-bold text-gray-900 text-sm tracking-tight">Daftar Bahan Diterima</h3>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wide bg-white">
                                            <th class="px-4 py-3 text-left w-12">No</th>
                                            <th class="px-4 py-3 text-left">Bahan Baku</th>
                                            <th class="px-4 py-3 text-right">Jumlah Diminta</th>
                                            <th class="px-4 py-3 text-right w-32">Jumlah Diterima</th>
                                            <th class="px-4 py-3 text-left">Satuan</th>
                                            <th class="px-4 py-3 text-left">Kondisi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-50">
                                        @forelse($items as $idx => $row)
                                        @php $detail = $row['detail']; @endphp
                                        <tr>
                                            <td class="px-4 py-3 text-sm text-gray-500 font-medium align-middle">{{ $idx + 1 }}</td>
                                            <td class="px-4 py-3 align-middle">
                                                <p class="font-bold text-gray-900 text-sm">{{ optional($detail->bahan_baku)->nama_bahan }}</p>
                                                <p class="text-xs text-gray-400 font-mono mt-0.5">{{ optional($detail->bahan_baku)->kode_bahan }}</p>
                                            </td>
                                            <td class="px-4 py-3 text-right font-semibold text-gray-900 align-middle">{{ $row['sisa'] }}</td>
                                            <td class="px-4 py-3 align-middle">
                                                <input type="text" name="jumlah_diterima[{{ $detail->id }}]" value="{{ $row['sisa'] }}" class="w-full text-right border border-gray-200 text-gray-900 text-sm rounded-lg px-2 py-1.5 outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500" required>
                                            </td>
                                            <td class="px-4 py-3 align-middle">
                                                <span class="text-xs font-semibold text-gray-500 bg-gray-100 px-2 py-1 rounded-md">{{ optional($detail->satuan)->nama_satuan ?? '-' }}</span>
                                            </td>
                                            <td class="px-4 py-3 align-middle">
                                                <select name="kondisi[{{ $detail->id }}]" class="w-full border border-gray-200 text-gray-900 text-sm rounded-lg px-2 py-1.5 outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                                                    <option value="Baik">Baik</option>
                                                    <option value="Rusak">Rusak</option>
                                                    <option value="Kurang">Kurang</option>
                                                </select>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="px-4 py-10 text-center text-gray-500 text-sm">Semua bahan pada permintaan ini sudah diterima.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 justify-end">
                            <a href="{{ route('pengadaan.penerimaan.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-gray-700 bg-gray-100 rounded-lg px-4 py-2 hover:bg-gray-200 transition-colors">
                                Batal
                            </a>
                            <button type="submit" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-emerald-600 rounded-lg px-4 py-2 hover:bg-emerald-700 transition-colors">
                                <x-heroicon-o-check class="w-4 h-4" />
                                Simpan Penerimaan
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        @endif

    </div>
</div>
@endsection
