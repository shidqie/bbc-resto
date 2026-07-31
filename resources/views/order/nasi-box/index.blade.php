@extends('layouts.pos')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800">
    <div class="w-full p-6 space-y-5">
        
        {{-- Header --}}
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-black text-gray-900 tracking-tight">Daftar Pesanan Nasi Box</h1>
                <p class="text-sm text-gray-500 font-medium mt-1">Kelola transaksi pesanan nasi box, tanggal kirim, rincian box & status pembayaran.</p>
            </div>
        </div>

        {{-- Alert --}}
        <x-ui.alert />

        {{-- Filter Bar --}}
        <div class="flex flex-col sm:flex-row gap-2 items-start sm:items-center justify-between mb-3 shrink-0">
            <form action="{{ route('admin.pesanan.nasibox.index') }}" method="GET" class="flex items-center gap-2 w-full sm:w-auto">
                <div class="relative flex-1 sm:flex-none sm:w-56">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Kode / Pemesan / HP…" class="w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-1 focus:ring-gray-400 focus:border-gray-400 outline-none bg-white">
                    <input type="hidden" name="status" value="{{ request('status', 'all') }}">
                </div>
                <button type="submit" class="text-xs font-medium bg-white border border-gray-200 text-gray-600 rounded-lg px-3 py-2 hover:bg-gray-50 transition-colors shrink-0">Cari</button>
            </form>
            
            <div class="flex items-center gap-1 text-xs font-medium overflow-x-auto no-scrollbar shrink-0">
                <span class="text-gray-500 mr-1">Status:</span>
                <a href="{{ route('admin.pesanan.nasibox.index', ['status' => 'all']) }}" class="px-3 py-1.5 rounded-lg transition-colors {{ request('status', 'all') === 'all' ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100' }}">Semua</a>
                <a href="{{ route('admin.pesanan.nasibox.index', ['status' => 'ditinjau']) }}" class="px-3 py-1.5 rounded-lg transition-colors {{ request('status') === 'ditinjau' ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100' }}">Baru</a>
                <a href="{{ route('admin.pesanan.nasibox.index', ['status' => 'terkonfirmasi']) }}" class="px-3 py-1.5 rounded-lg transition-colors {{ request('status') === 'terkonfirmasi' ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100' }}">Terkonfirmasi</a>
                <a href="{{ route('admin.pesanan.nasibox.index', ['status' => 'diproses']) }}" class="px-3 py-1.5 rounded-lg transition-colors {{ request('status') === 'diproses' ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100' }}">Diproses</a>
                <a href="{{ route('admin.pesanan.nasibox.index', ['status' => 'selesai']) }}" class="px-3 py-1.5 rounded-lg transition-colors {{ request('status') === 'selesai' ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100' }}">Selesai</a>
            </div>
        </div>

        {{-- Order Table --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-xs font-semibold text-gray-400 uppercase tracking-wide">
                        <th class="px-4 py-3 text-left w-12">No.</th>
                        <th class="px-4 py-3 text-left">Kode & Tanggal Acara</th>
                        <th class="px-4 py-3 text-left">Pelanggan & Kontak</th>
                        <th class="px-4 py-3 text-left">Menu & Jumlah Box</th>
                        <th class="px-4 py-3 text-left">Tagihan & DP</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($pesanans as $p)
                        <tr class="hover:bg-gray-50/60 transition-colors group">
                            <td class="px-4 py-3 text-xs text-gray-500 font-medium align-middle">
                                {{ $loop->iteration + ($pesanans->currentPage() - 1) * $pesanans->perPage() }}
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-semibold text-gray-900 font-mono text-xs">{{ $p->nomor_pesanan }}</p>
                                <p class="text-xs text-gray-600 font-medium mt-0.5">Acara: {{ optional($p->jadwal_pesanan)->tanggal_acara ? \Carbon\Carbon::parse(optional($p->jadwal_pesanan)->tanggal_acara)->format('d M Y') : '-' }}</p>
                                <p class="text-[10px] text-gray-400 mt-0.5">Dibuat: {{ $p->dibuat_pada ? $p->dibuat_pada->format('d M H:i') : '-' }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-semibold text-gray-900">{{ $p->catatan }}</p>
                                @if($p->kontak || $p->phone)
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $p->kontak ?? $p->phone) }}" target="_blank" class="text-xs text-emerald-600 font-medium hover:underline inline-flex items-center gap-1 mt-0.5">
                                        <i class="ph-bold ph-whatsapp-logo"></i>
                                        <span>{{ $p->kontak ?? $p->phone }}</span>
                                    </a>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-semibold text-gray-900 text-xs">{{ $p->menu->nama ?? 'Paket Nasi Box' }}</p>
                                <span class="inline-block text-xs font-semibold px-2 py-0.5 rounded-md bg-purple-50 text-purple-700 mt-1">
                                    {{ $p->jumlah_box }} Box
                                </span>
                            </td>
                            <td class="px-4 py-3 font-semibold text-gray-900 tabular-nums">
                                Rp {{ number_format($p->total_tagihan, 0, ',', '.') }}
                                @if($p->total_tagihan)
                                    <p class="text-xs text-gray-500 font-normal mt-0.5">DP: <span class="font-semibold text-purple-600">Rp {{ number_format($p->total_tagihan, 0, ',', '.') }}</span></p>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $sColors = [
                                        1 => 'bg-amber-50 text-amber-700',
                                        2 => 'bg-blue-50 text-blue-700',
                                        3 => 'bg-indigo-50 text-indigo-700',
                                        4 => 'bg-cyan-50 text-cyan-700',
                                        5 => 'bg-emerald-50 text-emerald-700',
                                        6 => 'bg-rose-50 text-rose-700',
                                    ];
                                    $sColor = $sColors[$p->status_pesanan_id] ?? 'bg-gray-100 text-gray-700';
                                @endphp
                                <span class="inline-block text-xs font-semibold px-2.5 py-0.5 rounded-md {{ $sColor }}">
                                    {{ $p->status_pesanan->nama_status ?? '-' }}
                                </span>
                                <div class="mt-1">
                                    <span class="text-[10px] font-bold uppercase tracking-wider {{ (optional($p->pembayaran->last())->status_pembayaran_id ?? 0) == 2 ? 'text-emerald-600' : 'text-amber-600' }}">
                                        {{ (optional($p->pembayaran->last())->status_pembayaran_id ?? 0) == 2 ? "Lunas" : "Belum Lunas" }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('admin.pesanan.nasibox.show', $p->id) }}" title="Detail" class="w-7 h-7 rounded-lg flex items-center justify-center bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors">
                                        <x-heroicon-o-eye class="w-3 h-3" />
                                    </a>
                                    <button type="button" onclick="alert('Fitur Ubah Pesanan belum tersedia')" title="Ubah" class="w-7 h-7 rounded-lg flex items-center justify-center bg-amber-50 text-amber-600 hover:bg-amber-100 transition-colors">
                                        <x-heroicon-o-pencil-square class="w-3 h-3" />
                                    </button>
                                    <a href="#" onclick="alert('Fitur Cetak PDF belum tersedia')" title="Cetak" class="w-7 h-7 rounded-lg flex items-center justify-center bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors">
                                        <x-heroicon-o-printer class="w-3 h-3" />
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-gray-400">
                                <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                <p class="text-sm font-medium">Tidak ada data pesanan nasi box.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4 shrink-0">{{ $pesanans->links() }}</div>

    </div>
</div>
@endsection