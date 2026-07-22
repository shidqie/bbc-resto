{{-- 
    Halaman: Pengadaan Bahan Baku (Catering & Nasi Box)
    Deskripsi: Daftar PO pengadaan bahan baku terikat pesanan Catering dan Nasi Box.
--}}
@extends('layouts.pos')

@section('content')
<div class="flex-1 overflow-auto bg-gray-50/50 text-gray-800 font-sans">
    <div class="p-4 md:p-6 lg:p-8 max-w-[1250px] mx-auto space-y-6">
        
        {{-- Header --}}
        <x-ui.page-header title="Pengadaan Bahan Baku Catering & Nasi Box" subtitle="Kelola pengadaan & penerimaan bahan baku khusus pesanan Catering dan Nasi Box">
            <x-slot:actions>
                <x-ui.button href="{{ route('pengadaan.create') }}" icon="fa-plus">Buat Pengadaan Baru</x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        {{-- Alert --}}
        <x-ui.alert />

        {{-- Statistik --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <x-ui.stat-card label="Total Pengadaan" :value="$stats['total']" icon="fa-clipboard-list" color="blue" />
            <x-ui.stat-card label="Pending Penerimaan" :value="$stats['pending']" icon="fa-clock" color="orange" />
            <x-ui.stat-card label="Barang Diterima" :value="$stats['diterima']" icon="fa-check-circle" color="green" />
            <x-ui.stat-card label="Dibatalkan" :value="$stats['dibatalkan']" icon="fa-times-circle" color="red" />
        </div>

        {{-- Tabel --}}
        <x-ui.data-table :paginator="$pengadaans">
            <x-slot:toolbar>
                <form action="{{ route('pengadaan.index') }}" method="GET" class="w-full flex flex-col sm:flex-row gap-3 justify-between items-center">
                    <div class="relative min-w-[250px] w-full sm:w-72">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Kode PO / Supplier…" 
                               class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0F2E23]/10 focus:border-[#0F2E23] outline-none text-xs transition-all bg-white font-medium">
                        <x-heroicon-o-magnifying-glass class="absolute left-3.5 top-2.5 text-gray-400 w-4 h-4 inline-block shrink-0" />
                    </div>

                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <select name="jenis_pesanan" onchange="this.form.submit()" class="px-3.5 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0F2E23]/10 focus:border-[#0F2E23] outline-none text-xs bg-white font-bold text-gray-700">
                            <option value="">Semua Jenis (Catering & Nasi Box)</option>
                            <option value="catering" {{ request('jenis_pesanan') == 'catering' ? 'selected' : '' }}>Catering</option>
                            <option value="nasi_box" {{ request('jenis_pesanan') == 'nasi_box' ? 'selected' : '' }}>Nasi Box</option>
                        </select>

                        <select name="status" onchange="this.form.submit()" class="px-3.5 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0F2E23]/10 focus:border-[#0F2E23] outline-none text-xs bg-white font-bold text-gray-700">
                            <option value="">Semua Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="diterima" {{ request('status') == 'diterima' ? 'selected' : '' }}>Diterima</option>
                            <option value="dibatalkan" {{ request('status') == 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                        </select>
                    </div>
                </form>
            </x-slot:toolbar>

            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/60 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100 sticky top-0 z-10">
                        <th class="px-5 py-4 font-bold">Kode PO & Tanggal</th>
                        <th class="px-5 py-4 font-bold">Jenis & Pesanan Terkait</th>
                        <th class="px-5 py-4 font-bold">Supplier</th>
                        <th class="px-5 py-4 font-bold">Total Estimasi / Real</th>
                        <th class="px-5 py-4 font-bold">Status</th>
                        <th class="px-5 py-4 font-bold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @forelse($pengadaans as $po)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-5 py-4">
                                <div class="font-mono font-bold text-[#0F2E23]">{{ $po->kode_pengadaan }}</div>
                                <div class="text-xs text-gray-400 font-medium mt-0.5">{{ $po->tanggal_pengadaan->format('d M Y') }}</div>
                            </td>
                            <td class="px-5 py-4">
                                @if($po->jenis_pesanan === 'nasi_box')
                                    <div class="inline-flex items-center gap-1 font-bold text-purple-900 text-xs bg-purple-50 px-2.5 py-1 rounded-xl border border-purple-200">
                                        <x-heroicon-o-gift class="w-3.5 h-3.5" />
                                        Nasi Box: {{ $po->pesananNasiBox->nama_pemesan ?? ($po->pesananNasiBox->kode_pesanan ?? '-') }}
                                    </div>
                                @elseif($po->jenis_pesanan === 'catering')
                                    <div class="inline-flex items-center gap-1 font-bold text-blue-900 text-xs bg-blue-50 px-2.5 py-1 rounded-xl border border-blue-200">
                                        <x-heroicon-o-clipboard-document-list class="w-3.5 h-3.5" />
                                        Catering: {{ $po->pesananCatering->nama_pemesan ?? ($po->pesananCatering->kode_pesanan ?? '-') }}
                                    </div>
                                @else
                                    <span class="text-xs text-gray-500 font-bold">Pengadaan Umum</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <div class="text-gray-900 font-bold">{{ $po->supplier->nama_supplier ?? 'Tanpa Supplier' }}</div>
                                <div class="text-xs text-gray-400">Oleh: {{ $po->user->name ?? '-' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-extrabold text-gray-900">Rp {{ number_format($po->total_biaya, 0, ',', '.') }}</div>
                            </td>
                            <td class="px-5 py-4">
                                @if($po->status == 'pending')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-900 border border-amber-300">⏳ Pending</span>
                                @elseif($po->status == 'diterima')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-900 border border-emerald-200">✓ Diterima</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-900 border border-red-200">✕ Dibatalkan</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('pengadaan.show', $po->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-xl border border-blue-200 transition-colors" title="Detail & Realisasi Penerimaan">
                                    <x-heroicon-o-eye class="w-4 h-4" /> Detail & Realisasi
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <x-ui.empty-state icon="fa-box-open" title="Tidak ada data pengadaan." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-ui.data-table>

    </div>
</div>
@endsection
