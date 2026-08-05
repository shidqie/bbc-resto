@extends('layouts.pos')
@section('title', 'Form Permintaan Catering')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800 pb-10">
    <div class="w-full p-6 space-y-5">

        {{-- PAGE HEADER --}}
        <x-ui.page-header
            title="Form Permintaan Catering"
            subtitle="Buat permintaan bahan baku berdasarkan pesanan catering yang telah dikonfirmasi."
            :breadcrumbs="['Pengadaan', 'Permintaan Catering', 'Buat']">
        </x-ui.page-header>

        <form action="{{ route('pengadaan.catering.store') }}" method="POST" class="space-y-5">
            @csrf
            
            {{-- TOOLBAR ATAS --}}
            <div class="bg-white p-4 rounded-xl border border-gray-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div class="relative w-full sm:max-w-xs">
                    <x-heroicon-o-magnifying-glass class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                    <input type="text" id="searchInput" placeholder="Cari ID Pesanan Catering..." class="w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-shadow">
                </div>
                
                <button type="submit" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-emerald-600 rounded-lg px-4 py-2 hover:bg-emerald-700 transition-colors shrink-0">
                    <x-heroicon-o-check class="w-4 h-4" />
                    Simpan Permintaan
                </button>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-4 gap-5">
                
                {{-- KIRI: Pilih Pesanan & Informasi Pesanan --}}
                <div class="xl:col-span-1 space-y-5">
                    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                        <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                            <h3 class="font-bold text-gray-900 text-sm tracking-tight">Pilih Pesanan Catering</h3>
                        </div>
                        <div class="p-4 space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1">Pilih Pesanan</label>
                                <select name="pesanan_id" class="w-full border border-gray-200 text-gray-900 text-sm rounded-lg px-3 py-2 outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 bg-white" required onchange="window.showToast('info', 'Mengambil data kebutuhan bahan...')">
                                    <option value="">-- Pilih Pesanan --</option>
                                    @foreach($pesanans as $p)
                                        <option value="{{ $p->id }}">{{ $p->nomor_pesanan }} - {{ optional($p->pelanggan)->nama ?? optional($p->jadwal_pesanan)->nama_penerima ?? 'Pelanggan' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            {{-- Info Mockup (Nanti di-update via AJAX) --}}
                            <div class="pt-4 border-t border-gray-100 space-y-3">
                                <div>
                                    <label class="block text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-0.5">Tanggal Acara</label>
                                    <p class="text-sm font-medium text-gray-900">-</p>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-0.5">Jumlah Porsi</label>
                                    <p class="text-sm font-medium text-gray-900">-</p>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-0.5">Paket Catering</label>
                                    <p class="text-sm font-medium text-gray-900">-</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- KANAN: Daftar Kebutuhan Bahan --}}
                <div class="xl:col-span-3">
                    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                        <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 flex justify-between items-center">
                            <h3 class="font-bold text-gray-900 text-sm tracking-tight">Kalkulasi Kebutuhan Bahan</h3>
                            <button type="button" class="text-xs font-medium text-emerald-600 hover:text-emerald-700 bg-emerald-50 hover:bg-emerald-100 px-2.5 py-1 rounded-md transition-colors inline-flex items-center gap-1">
                                <x-heroicon-o-plus class="w-3 h-3" /> Tambah Bahan Lain
                            </button>
                        </div>
                        
                        <div class="overflow-x-auto min-h-[300px]">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wide bg-white">
                                        <th class="px-4 py-3 text-left w-12">No</th>
                                        <th class="px-4 py-3 text-left">Bahan Baku</th>
                                        <th class="px-4 py-3 text-right">Total Kebutuhan</th>
                                        <th class="px-4 py-3 text-right">Stok Catering</th>
                                        <th class="px-4 py-3 text-right text-rose-500">Kekurangan</th>
                                        <th class="px-4 py-3 text-right w-32">Jumlah Diminta</th>
                                        <th class="px-4 py-3 text-left">Satuan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    {{-- Mockup Tampilan Awal --}}
                                    <tr>
                                        <td colspan="7" class="px-4 py-16 text-center text-gray-400">
                                            <x-heroicon-o-clipboard-document-check class="w-12 h-12 mx-auto mb-3 text-gray-300" />
                                            <p class="text-sm font-bold text-gray-500">Pilih Pesanan Terlebih Dahulu</p>
                                            <p class="text-xs mt-1">Sistem akan otomatis mengkalkulasi kebutuhan bahan baku berdasarkan porsi dan resep.</p>
                                        </td>
                                    </tr>
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
