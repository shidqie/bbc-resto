@extends('layouts.pos')
@section('title', 'Form Permintaan Harian')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800 pb-10">
    <div class="w-full p-6 space-y-5">

        {{-- PAGE HEADER --}}
        <x-ui.page-header
            title="Form Permintaan Harian"
            subtitle="Buat permintaan pembelian bahan baku operasional berdasarkan stok yang telah mencapai batas minimum."
            :breadcrumbs="['Pengadaan', 'Permintaan Harian', 'Buat']">
        </x-ui.page-header>

        <form action="{{ route('pengadaan.harian.store') }}" method="POST" class="space-y-5">
            @csrf
            
            {{-- TOOLBAR ATAS --}}
            <div class="bg-white p-4 rounded-xl border border-gray-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div class="relative w-full sm:max-w-xs">
                    <x-heroicon-o-magnifying-glass class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                    <input type="text" id="searchInput" placeholder="Cari bahan baku (opsional)..." class="w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-shadow">
                </div>
                
                <button type="submit" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-emerald-600 rounded-lg px-4 py-2 hover:bg-emerald-700 transition-colors shrink-0">
                    <x-heroicon-o-check class="w-4 h-4" />
                    Simpan Permintaan
                </button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-5">
                
                {{-- KIRI: Informasi Permintaan --}}
                <div class="lg:col-span-1 space-y-5">
                    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                        <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                            <h3 class="font-bold text-gray-900 text-sm tracking-tight">Informasi Permintaan</h3>
                        </div>
                        <div class="p-4 space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1">Nomor Permintaan</label>
                                <input type="text" readonly value="REQ-{{ date('ymd') }}-{{ rand(100, 999) }}" class="w-full bg-gray-50 border border-gray-200 text-gray-600 text-sm rounded-lg px-3 py-2 font-mono font-bold cursor-not-allowed">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1">Tanggal Permintaan</label>
                                <input type="date" name="tanggal_pengadaan" value="{{ date('Y-m-d') }}" class="w-full border border-gray-200 text-gray-900 text-sm rounded-lg px-3 py-2 outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500" required>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1">Perkiraan Tanggal Datang</label>
                                <input type="date" name="perkiraan_tanggal_datang" class="w-full border border-gray-200 text-gray-900 text-sm rounded-lg px-3 py-2 outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
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
                <div class="lg:col-span-3">
                    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                        <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 flex justify-between items-center">
                            <h3 class="font-bold text-gray-900 text-sm tracking-tight">Daftar Bahan Baku (Stok Menipis)</h3>
                            <button type="button" class="text-xs font-medium text-emerald-600 hover:text-emerald-700 bg-emerald-50 hover:bg-emerald-100 px-2.5 py-1 rounded-md transition-colors inline-flex items-center gap-1">
                                <x-heroicon-o-plus class="w-3 h-3" /> Tambah Bahan Lain
                            </button>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wide bg-white">
                                        <th class="px-4 py-3 text-left w-12">No</th>
                                        <th class="px-4 py-3 text-left">Bahan Baku</th>
                                        <th class="px-4 py-3 text-right">Stok Saat Ini</th>
                                        <th class="px-4 py-3 text-right">Stok Minimum</th>
                                        <th class="px-4 py-3 text-right w-32">Jumlah Diminta</th>
                                        <th class="px-4 py-3 text-left">Satuan</th>
                                        <th class="px-4 py-3 text-center w-16">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    @forelse($stokMenipis as $i => $stok)
                                    <tr class="hover:bg-gray-50/60 transition-colors group">
                                        <td class="px-4 py-3 text-sm text-gray-500 font-medium align-middle">
                                            {{ $i + 1 }}
                                        </td>
                                        <td class="px-4 py-3 align-middle">
                                            <p class="font-bold text-gray-900 text-sm">{{ $stok->bahan_baku->nama_bahan }}</p>
                                            <p class="text-xs text-gray-400 font-mono mt-0.5">{{ $stok->bahan_baku->kode_bahan }}</p>
                                            <input type="hidden" name="bahan_id[]" value="{{ $stok->bahan_baku->id }}">
                                        </td>
                                        <td class="px-4 py-3 text-right font-medium text-rose-600 align-middle">
                                            {{ (float)$stok->jumlah_stok }}
                                        </td>
                                        <td class="px-4 py-3 text-right font-medium text-gray-600 align-middle">
                                            {{ (float)$stok->bahan_baku->stok_minimal }}
                                        </td>
                                        <td class="px-4 py-3 align-middle">
                                            <input type="number" step="0.01" min="0.01" name="jumlah[]" class="w-full text-right border border-gray-200 text-gray-900 text-sm rounded-lg px-2 py-1.5 outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-shadow" placeholder="0">
                                        </td>
                                        <td class="px-4 py-3 align-middle">
                                            <span class="text-xs font-semibold text-gray-500 bg-gray-100 px-2 py-1 rounded-md">{{ optional($stok->bahan_baku->satuan)->nama_satuan ?? '-' }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-center align-middle">
                                            <button type="button" class="w-7 h-7 rounded-full flex items-center justify-center bg-rose-50 text-rose-600 hover:bg-rose-100 transition-colors mx-auto" onclick="this.closest('tr').remove()">
                                                <x-heroicon-o-trash class="w-3.5 h-3.5" />
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr class="bg-emerald-50/50">
                                        <td colspan="7" class="px-4 py-10 text-center text-emerald-700">
                                            <x-heroicon-o-check-circle class="w-10 h-10 mx-auto mb-2 text-emerald-500 opacity-50" />
                                            <p class="text-sm font-bold">Semua Stok Aman</p>
                                            <p class="text-xs mt-1 opacity-80">Tidak ada bahan baku operasional yang mencapai stok minimum.</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </form>

    </div>
</div>
@endsection
