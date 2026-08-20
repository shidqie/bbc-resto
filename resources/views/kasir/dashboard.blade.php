@extends('layouts.pos')

@section('title', 'Dashboard Kasir')

@section('content')
<div class="flex-1 bg-slate-50/60 text-slate-800 pb-10">
    <div class="w-full p-6 space-y-6">
        
        {{-- Header & Primary Action --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200/80">
            <div>
                <h1 class="text-xl font-black text-slate-900 tracking-tight">Dashboard Kasir</h1>
                <p class="text-xs text-slate-400 font-medium">Ringkasan transaksi kasir & antrean tagihan meja hari ini.</p>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="{{ route('pos.dinein.index') }}" class="px-5 py-2.5 rounded-xl bg-[#0D3024] hover:bg-slate-900 text-white font-bold text-xs shadow-sm transition-all flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                    Buka Terminal POS
                </a>
            </div>
        </div>

        {{-- 3 Stat Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            {{-- Pendapatan Hari ini --}}
            <div class="bg-white border border-slate-200/80 rounded-xl p-4 shadow-2xs">
                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block mb-1">Pendapatan Hari ini</span>
                <div class="text-xl font-extrabold text-slate-900">
                    Rp {{ number_format($omsetHariIni, 0, ',', '.') }}
                </div>
            </div>

            {{-- Total Transaksi --}}
            <div class="bg-white border border-slate-200/80 rounded-xl p-4 shadow-2xs">
                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block mb-1">Total Transaksi</span>
                <div class="text-xl font-extrabold text-slate-900">
                    {{ $transaksiSelesaiCount }}
                </div>
            </div>

            {{-- Meja --}}
            <div class="bg-white border border-slate-200/80 rounded-xl p-4 shadow-2xs">
                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block mb-1">Status Meja</span>
                <div class="text-xl font-extrabold text-slate-900 flex items-center gap-1.5">
                    <span class="text-red-600">{{ $mejaTerisiCount }} Terisi</span>
                    <span class="text-slate-300 font-normal">/</span>
                    <span class="text-emerald-600">{{ $mejaTersediaCount }} Kosong</span>
                </div>
            </div>
        </div>

        {{-- Main Section: 2 Columns --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- Left: Antrean Tagihan Belum Lunas --}}
            <div class="lg:col-span-2 space-y-4">
                <div class="bg-white border border-slate-200/80 rounded-xl shadow-2xs overflow-hidden">
                    <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h2 class="font-bold text-slate-900 text-sm">Tagihan Belum Lunas Hari Ini</h2>
                            <p class="text-[11px] text-slate-400">Pesanan resto yang menunggu proses checkout / pelunasan kasir</p>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200/80">
                            {{ $pesananBelumBayarCount }} Belum Lunas
                        </span>
                    </div>

                    @if($pesananBelumBayar->isNotEmpty())
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse text-xs">
                                <thead>
                                    <tr class="bg-slate-50 border-b border-slate-100 text-slate-400 uppercase font-bold tracking-wider">
                                        <th class="px-4 py-3">Kode / Meja</th>
                                        <th class="px-4 py-3">Waktu</th>
                                        <th class="px-4 py-3">Item Pesanan</th>
                                        <th class="px-4 py-3 text-right">Total Tagihan</th>
                                        <th class="px-4 py-3 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($pesananBelumBayar as $p)
                                        @php
                                            $mejaNama = $p->meja ? ('Meja ' . $p->meja->nomor_meja) : 'Takeaway';
                                            $itemsSummary = $p->detail_pesanan->map(function($d) {
                                                return ($d->menu->nama_menu ?? 'Item') . ' (' . $d->jumlah . 'x)';
                                            })->take(2)->join(', ');
                                            if ($p->detail_pesanan->count() > 2) {
                                                $itemsSummary .= ' + ' . ($p->detail_pesanan->count() - 2) . ' item lagi';
                                            }
                                        @endphp
                                        <tr class="hover:bg-slate-50/60">
                                            <td class="px-4 py-3">
                                                <div class="font-bold text-slate-900">{{ $p->id_pesanan }}</div>
                                                <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 mt-0.5">
                                                    {{ $mejaNama }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-slate-500">
                                                {{ $p->dibuat_pada ? \Carbon\Carbon::parse($p->dibuat_pada)->format('H:i') : '-' }} WIB
                                            </td>
                                            <td class="px-4 py-3 text-slate-600 max-w-xs truncate">
                                                {{ $itemsSummary ?: '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-right font-extrabold text-slate-900">
                                                Rp {{ number_format($p->total_tagihan, 0, ',', '.') }}
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                @if($p->meja)
                                                    <a href="{{ route('pos.dinein.checkout', ['meja' => $p->meja->id]) }}" class="inline-flex items-center gap-1 px-3 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition-colors">
                                                        Proses Bayar
                                                    </a>
                                                @else
                                                    <a href="{{ route('admin.pesanan.show', $p->id) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-xs">
                                                        Detail
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8 text-slate-400 text-xs">
                            <span class="font-medium text-slate-500 block mb-0.5">Semua Tagihan Hari Ini Lunas</span>
                            Tidak ada antrean pesanan yang menunggu pembayaran.
                        </div>
                    @endif
                </div>
            </div>

            {{-- Right: Status Meja Resto --}}
            <div class="space-y-4">
                <div class="bg-white border border-slate-200/80 rounded-xl p-4 shadow-2xs space-y-3">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <h3 class="font-bold text-slate-900 text-xs">Status Meja Resto</h3>
                        <a href="{{ route('pos.dinein.index') }}" class="text-[11px] font-bold text-emerald-600 hover:underline">Lihat POS →</a>
                    </div>

                    <div class="grid grid-cols-4 gap-2 text-center text-xs">
                        @forelse($allMeja as $m)
                            @php
                                $isTerisi = $m->status_meja_id == 2 || $m->status === 'terisi';
                                $nomorFormatted = preg_replace('/^meja\s*/i', '', $m->nomor_meja);
                            @endphp
                            @if($isTerisi)
                                <a href="{{ route('pos.dinein.checkout', ['meja' => $m->id]) }}" class="p-2 rounded-lg border border-red-200 bg-red-50 hover:bg-red-100 transition-all group" title="Meja {{ $nomorFormatted }} Terisi - Klik untuk Checkout">
                                    <div class="font-bold text-red-800 text-[11px]">Meja {{ $nomorFormatted }}</div>
                                    <div class="text-[9px] font-semibold text-red-600">Terisi</div>
                                </a>
                            @else
                                <div class="p-2 rounded-lg border border-slate-100 bg-slate-50/60">
                                    <div class="font-semibold text-slate-700 text-[11px]">Meja {{ $nomorFormatted }}</div>
                                    <div class="text-[9px] text-emerald-600 font-medium">Kosong</div>
                                </div>
                            @endif
                        @empty
                            <div class="col-span-4 text-center py-4 text-xs text-slate-400 italic">Belum ada meja.</div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>
@endsection
