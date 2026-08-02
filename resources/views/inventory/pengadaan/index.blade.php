@extends('layouts.pos')
@section('content')
<div class="flex-1 overflow-auto bg-gray-50/50">
    <div class="w-full p-6 space-y-6">
        <x-ui.page-header title="Pengadaan Bahan" :breadcrumbs="['Pengadaan', 'Daftar PO']">
            <x-slot:actions>
                <a href="{{ route('pengadaan.create', ['tipe' => 'harian']) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 text-white text-sm font-semibold rounded-3xl hover:bg-amber-600 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    Pengadaan Harian
                </a>
                <a href="{{ route('pengadaan.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-[#3B82F6] text-white text-sm font-semibold rounded-3xl hover:bg-blue-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Buat Pengadaan Baru
                </a>
            </x-slot:actions>
        </x-ui.page-header>
        <x-ui.alert />
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-[2.25rem] border border-gray-100 p-4 shadow-sm">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total PO</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total'] }}</p>
            </div>
            <div class="bg-white rounded-[2.25rem] border border-gray-100 p-4 shadow-sm">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Nilai</p>
                <p class="text-lg font-bold text-gray-900 mt-1">Rp {{ number_format($stats['total_pengadaan'], 0, ',', '.') }}</p>
            </div>
        </div>
        <div class="bg-white rounded-[2.25rem] border border-gray-100 p-4 shadow-sm">
            <form method="GET" class="flex gap-3">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nomor pengadaan..." class="flex-1 border border-gray-200 rounded-3xl px-4 py-2.5 text-sm text-gray-700 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6]">
                <button type="submit" class="px-4 py-2.5 bg-gray-100 rounded-3xl text-sm font-medium text-gray-600 hover:bg-gray-200 transition">Cari</button>
                <a href="{{ route('pengadaan.index') }}" class="px-4 py-2.5 bg-gray-100 rounded-3xl text-sm font-medium text-gray-600 hover:bg-gray-200 transition">Reset</a>
            </form>
        </div>
        <div class="bg-white rounded-3xl border border-gray-200 overflow-hidden">
            <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-700">Daftar Purchase Order</h2>
                <a href="{{ route('pengadaan.terima-barang') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-green-600 bg-green-50 hover:bg-green-100 px-3 py-1.5 rounded-2xl transition">
                    Penerimaan Bahan &rarr;
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-xs font-semibold text-gray-400 uppercase tracking-wide">
                            <th class="px-4 py-3 text-left">No. PO</th>
                            <th class="px-4 py-3 text-left">Tanggal</th>
                            <th class="px-4 py-3 text-left">Pemasok</th>
                            <th class="px-4 py-3 text-left">Jenis</th>
                            <th class="px-4 py-3 text-left">Total</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($pengadaans as $po)
                        <tr class="hover:bg-gray-50/60 transition-colors group">
                            <td class="px-4 py-3">
                                <span class="font-mono text-xs font-semibold text-blue-600 bg-blue-50 px-2 py-1 rounded-2xl">{{ $po->nomor_pengadaan }}</span>
                            </td>
                            <td class="px-4 py-3 text-gray-600 text-xs">{{ \Carbon\Carbon::parse($po->tanggal_pengadaan)->format('d M Y') }}</td>
                            <td class="px-4 py-3 font-semibold text-gray-900">{{ $po->nama_pemasok ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <span class="text-xs px-2 py-0.5 rounded-full {{ $po->jenis_pengadaan == 'CATERING' ? 'bg-purple-50 text-purple-700' : 'bg-orange-50 text-orange-700' }} font-semibold">
                                    {{ $po->jenis_pengadaan }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-semibold text-gray-900">Rp {{ number_format($po->total_pengadaan, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">
                                @php $sid = $po->status_pengadaan_id; @endphp
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $sid==1 ? 'bg-amber-50 text-amber-700' : ($sid==2 ? 'bg-blue-50 text-blue-700' : ($sid==3 ? 'bg-red-50 text-red-700' : 'bg-green-50 text-green-700')) }}">
                                    {{ $po->status_pengadaan?->nama_status ?? '-' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('pengadaan.show', $po->id) }}" title="Detail" class="w-7 h-7 rounded-full flex items-center justify-center bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors">
                                        <x-heroicon-o-eye class="w-3 h-3" />
                                    </a>
                                    @if(in_array($po->status_pengadaan_id, [1, 2]))
                                    <a href="{{ route('pengadaan.form-terima', $po->id) }}" class="text-xs px-2.5 py-1.5 bg-green-100 text-green-700 rounded-2xl hover:bg-green-200 transition font-medium">Terima</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400 text-sm">Belum ada pengadaan</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($pengadaans->hasPages())
            <div class="px-5 py-3 border-t border-gray-100">{{ $pengadaans->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
