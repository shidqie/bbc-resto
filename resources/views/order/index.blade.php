{{-- 
    Halaman: Daftar Pesanan Dine-In
    UI: disamakan dengan Kelola Menu
--}}
@extends('layouts.pos')

@section('title', 'Daftar Pesanan Dine-In')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800">
    <div class="w-full p-6 space-y-5">

        {{-- PAGE HEADER --}}
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-black text-gray-900 tracking-tight">Daftar Pesanan Resto</h1>
                <p class="text-sm text-gray-500 font-medium mt-1">Kelola transaksi pesanan dine-in, rincian menu, status dapur, dan pembayaran</p>
            </div>
            <a href="{{ route('pos.dinein.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-gray-900 rounded-lg px-3 py-2 hover:bg-gray-800 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Pesanan Baru (POS)
            </a>
        </div>

        <x-ui.alert />

        {{-- Stat Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div class="bg-white rounded-xl border border-gray-200 px-4 py-3">
                <p class="text-xs font-medium text-gray-500">Pesanan Baru</p>
                <p class="text-xl font-bold text-blue-600 mt-1">{{ $stats['baru'] }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 px-4 py-3">
                <p class="text-xs font-medium text-gray-500">Sedang Diproses</p>
                <p class="text-xl font-bold text-amber-600 mt-1">{{ $stats['diproses'] }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 px-4 py-3">
                <p class="text-xs font-medium text-gray-500">Pesanan Selesai</p>
                <p class="text-xl font-bold text-emerald-600 mt-1">{{ $stats['selesai'] }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 px-4 py-3">
                <p class="text-xs font-medium text-gray-500">Total Transaksi</p>
                <p class="text-xl font-bold text-gray-900 mt-1">{{ $pesanans->total() }}</p>
            </div>
        </div>

        {{-- Filter Bar --}}
        <div class="flex flex-col sm:flex-row gap-2 items-start sm:items-center justify-between mb-3 shrink-0">
            <form action="{{ route('pesanan.index') }}" method="GET" class="flex items-center gap-2 w-full sm:w-auto flex-wrap">
                <div class="relative flex-1 sm:flex-none sm:w-64">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari No. Pesanan / Nama / Meja…" class="w-full pl-8 pr-3 py-2 text-xs border border-gray-200 rounded-lg outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-all bg-white">
                </div>
                <select name="jenis" class="text-xs border border-gray-200 rounded-lg bg-white px-3 py-2 outline-none focus:ring-1 focus:ring-gray-400 transition-all" onchange="this.form.submit()">
                    <option value="">Semua Jenis</option>
                    <option value="dine_in" {{ request('jenis') == 'dine_in' ? 'selected' : '' }}>Dine In</option>
                    <option value="catering" {{ request('jenis') == 'catering' ? 'selected' : '' }}>Catering</option>
                    <option value="nasi_box" {{ request('jenis') == 'nasi_box' ? 'selected' : '' }}>Nasi Box</option>
                </select>
                <select name="status" class="text-xs border border-gray-200 rounded-lg bg-white px-3 py-2 outline-none focus:ring-1 focus:ring-gray-400 transition-all" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="baru" {{ request('status') == 'baru' ? 'selected' : '' }}>Baru</option>
                    <option value="diproses" {{ request('status') == 'diproses' ? 'selected' : '' }}>Diproses</option>
                    <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                    <option value="dibatalkan" {{ request('status') == 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
                <button type="submit" class="text-xs font-medium bg-white border border-gray-200 text-gray-600 rounded-lg px-3 py-2 hover:bg-gray-50 transition-colors shrink-0">Cari</button>
            </form>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden overflow-x-auto">
            <table class="w-full text-sm min-w-[900px]">
                <thead>
                    <tr class="border-b border-gray-100 text-xs font-semibold text-gray-400 uppercase tracking-wide">
                        <th class="px-4 py-3 text-left w-12">No.</th>
                        <th class="px-4 py-3 text-left">Info Pesanan</th>
                        <th class="px-4 py-3 text-left">Pelanggan &amp; Lokasi</th>
                        <th class="px-4 py-3 text-left">Rincian Menu</th>
                        <th class="px-4 py-3 text-left">Pembayaran</th>
                        <th class="px-4 py-3 text-left">Status Pesanan</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($pesanans as $i => $p)
                    <tr class="hover:bg-gray-50/60 transition-colors group align-top">
                        <td class="px-4 py-3 text-xs text-gray-500 font-medium">{{ $pesanans->firstItem() + $i }}</td>

                        {{-- Info Pesanan --}}
                        <td class="px-4 py-3">
                            <p class="font-semibold text-gray-900 font-mono text-xs">{{ $p->no_pesanan }}</p>
                            <p class="text-[10px] text-gray-400 mt-0.5">{{ $p->tanggal_pesanan ? $p->tanggal_pesanan->format('d M Y, H:i') : '-' }}</p>
                            <span class="inline-block mt-1 text-[10px] font-semibold px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 uppercase">
                                {{ str_replace('_', ' ', $p->jenis_pesanan) }}
                            </span>
                        </td>

                        {{-- Pelanggan & Lokasi --}}
                        <td class="px-4 py-3">
                            @php
                                $namaPelanggan = $p->nama_pelanggan ?? 'Pelanggan Umum';
                                $parts = explode(' - ', $namaPelanggan);
                                $nama = $parts[0];
                                $phone = isset($parts[1]) ? $parts[1] : null;
                            @endphp
                            <div class="flex items-center gap-1.5">
                                <div class="w-6 h-6 rounded-full bg-gray-100 text-gray-700 font-bold text-[10px] flex items-center justify-center shrink-0">
                                    {{ strtoupper(substr($nama, 0, 1)) }}
                                </div>
                                <p class="font-semibold text-gray-900 text-xs truncate max-w-[120px]">{{ $nama }}</p>
                            </div>
                            @if($phone)
                                <p class="text-[10px] text-emerald-600 font-medium mt-0.5">{{ $phone }}</p>
                            @endif
                            @if($p->no_meja)
                                <span class="inline-block mt-1 text-[10px] font-semibold px-1.5 py-0.5 rounded-md bg-amber-50 text-amber-800">Meja {{ $p->no_meja }}</span>
                            @endif
                        </td>

                        {{-- Rincian Menu --}}
                        <td class="px-4 py-3 max-w-[220px]">
                            @if($p->details && $p->details->count() > 0)
                                <div class="space-y-0.5">
                                    @foreach($p->details->take(2) as $d)
                                        <div class="text-[11px] text-gray-700 flex items-baseline justify-between gap-1">
                                            <span class="truncate"><span class="font-bold text-blue-500">{{ $d->jumlah }}x</span> {{ $d->menu->nama ?? 'Item' }}</span>
                                            <span class="text-[10px] text-gray-400 shrink-0">Rp {{ number_format($d->subtotal ?? ($d->jumlah * $d->harga_satuan), 0, ',', '.') }}</span>
                                        </div>
                                    @endforeach
                                    @if($p->details->count() > 2)
                                        <p class="text-[10px] text-gray-400 italic">+{{ $p->details->count() - 2 }} item lainnya...</p>
                                    @endif
                                </div>
                            @else
                                <span class="text-[11px] text-gray-400 italic">Tidak ada rincian</span>
                            @endif
                        </td>

                        {{-- Pembayaran --}}
                        <td class="px-4 py-3">
                            <p class="font-semibold text-gray-900 text-sm">Rp {{ number_format($p->total_harga, 0, ',', '.') }}</p>
                            @if($p->status_pembayaran == 'lunas')
                                <span class="inline-block text-[10px] font-semibold px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 mt-1">Lunas</span>
                            @elseif($p->status_pembayaran == 'dp')
                                <span class="inline-block text-[10px] font-semibold px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 mt-1">DP</span>
                            @else
                                <span class="inline-block text-[10px] font-semibold px-2 py-0.5 rounded-md bg-red-50 text-red-700 mt-1">Belum Bayar</span>
                            @endif
                        </td>

                        {{-- Status Pesanan (inline changer) --}}
                        <td class="px-4 py-3">
                            <form action="{{ route('pesanan.update-status', $p->id) }}" method="POST">
                                @csrf @method('PATCH')
                                <select name="status_pesanan" onchange="this.form.submit()"
                                        class="text-xs font-semibold rounded-lg border px-2 py-1 outline-none cursor-pointer transition-colors
                                               {{ $p->status_pesanan == 'baru' ? 'bg-gray-100 text-gray-800 border-gray-200' : '' }}
                                               {{ $p->status_pesanan == 'diproses' ? 'bg-amber-50 text-amber-900 border-amber-200' : '' }}
                                               {{ $p->status_pesanan == 'selesai' ? 'bg-emerald-50 text-emerald-900 border-emerald-200' : '' }}
                                               {{ $p->status_pesanan == 'dibatalkan' ? 'bg-red-50 text-red-800 border-red-200' : '' }}">
                                    <option value="baru" {{ $p->status_pesanan == 'baru' ? 'selected' : '' }}>• Baru</option>
                                    <option value="diproses" {{ $p->status_pesanan == 'diproses' ? 'selected' : '' }}>• Diproses</option>
                                    <option value="selesai" {{ $p->status_pesanan == 'selesai' ? 'selected' : '' }}>• Selesai</option>
                                    <option value="dibatalkan" {{ $p->status_pesanan == 'dibatalkan' ? 'selected' : '' }}>• Dibatalkan</option>
                                </select>
                            </form>
                        </td>

                        {{-- Aksi --}}
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1 opacity-70 group-hover:opacity-100 transition-opacity">
                                <a href="{{ route('pesanan.show', $p->id) }}" class="p-1.5 rounded-md text-gray-500 hover:bg-gray-100 hover:text-gray-900 transition-colors" title="Lihat Detail">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-14 text-center text-gray-400">
                            <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            <p class="text-sm font-medium">Tidak ada data pesanan.</p>
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
