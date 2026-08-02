@extends('layouts.pos')
@section('title', 'Penyesuaian Stok')

@section('content')
<div class="px-6 py-8 md:px-10 md:py-10">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Penyesuaian Stok</h1>
            <p class="text-sm text-gray-500 mt-1">Koreksi stok untuk barang rusak, busuk, atau selisih opname fisik</p>
        </div>
        <a href="{{ route('penyesuaian-stok.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#3B82F6] text-white text-sm font-semibold rounded-3xl hover:bg-blue-700 transition shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Buat Penyesuaian
        </a>
    </div>

    <!-- Alert -->
    <x-ui.alert />

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">
        <div class="bg-white rounded-[2.25rem] border border-gray-100 shadow-sm p-6">
            <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Total Penyesuaian</div>
            <div class="text-3xl font-bold text-gray-800">{{ $stats['total'] }}</div>
        </div>
        <div class="bg-white rounded-[2.25rem] border border-gray-100 shadow-sm p-6">
            <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Disetujui</div>
            <div class="text-3xl font-bold text-[#16A34A]">{{ $stats['disetujui'] }}</div>
        </div>
        <div class="bg-white rounded-[2.25rem] border border-gray-100 shadow-sm p-6">
            <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Menunggu</div>
            <div class="text-3xl font-bold text-[#D97706]">{{ $stats['menunggu'] }}</div>
        </div>
    </div>

    <!-- Warning Box -->
    <div class="bg-amber-50 border border-amber-200 rounded-[2.25rem] p-4 mb-6 flex items-start gap-3">
        <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        <div>
            <p class="text-sm font-semibold text-amber-800">Perhatian</p>
            <p class="text-sm text-amber-700 mt-0.5">Halaman ini <strong>satu-satunya tempat</strong> untuk melakukan koreksi manual stok. Gunakan hanya untuk kasus di luar alur normal: barang rusak, busuk, atau perbedaan saat opname fisik.</p>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-gray-100 text-xs font-semibold text-gray-400 uppercase tracking-wide">
                        <th class="px-4 py-3 text-left">No. Penyesuaian</th>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-left">Alasan</th>
                        <th class="px-4 py-3 text-left">Dibuat Oleh</th>
                        <th class="px-4 py-3 text-left">Item</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($penyesuaians as $adj)
                    <tr class="hover:bg-gray-50/60 transition-colors group">
                        <td class="px-4 py-3">
                            <span class="font-mono text-xs font-semibold text-[#3B82F6] bg-blue-50 px-2 py-1 rounded-2xl">{{ $adj->nomor_penyesuaian }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ \Carbon\Carbon::parse($adj->tanggal_penyesuaian)->format('d M Y') }}</td>
                        <td class="px-4 py-3">
                            <span class="text-sm text-gray-700 max-w-xs line-clamp-1">{{ $adj->alasan }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $adj->dibuat_oleh_pengguna->nama_lengkap ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $adj->detail_penyesuaian_stok->count() }} bahan</td>
                        <td class="px-4 py-3">
                            <span class="text-[11px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wide
                                {{ $adj->status_penyesuaian == 'DISETUJUI' ? 'bg-green-50 text-green-700' : 'bg-yellow-50 text-yellow-700' }}">
                                {{ $adj->status_penyesuaian }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('penyesuaian-stok.show', $adj->id) }}" title="Detail" class="w-7 h-7 rounded-full flex items-center justify-center bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors">
                                    <x-heroicon-o-eye class="w-3 h-3" />
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-16 text-center">
                            <div class="w-14 h-14 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3">
                                <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                            </div>
                            <div class="text-sm font-semibold text-gray-600">Belum ada penyesuaian stok</div>
                            <div class="text-sm text-gray-400 mt-1">Penyesuaian akan muncul di sini setelah dibuat.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($penyesuaians->hasPages())
        <div class="px-5 py-4 border-t border-gray-100">
            {{ $penyesuaians->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
