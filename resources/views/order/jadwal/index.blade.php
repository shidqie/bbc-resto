{{-- 
    Halaman: Jadwal Pengantaran (Sederhana)
    UI: Standar komponen tabel
--}}
@extends('layouts.pos')

@section('title', 'Jadwal Pengantaran')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800">
    <div class="w-full p-6 space-y-5">

        {{-- PAGE HEADER --}}
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-black text-gray-900 tracking-tight">Jadwal Pengantaran</h1>
                <p class="text-sm text-gray-500 font-medium mt-1">Kelola jadwal pengiriman pesanan Catering &amp; Nasi Box</p>
            </div>
            <a href="{{ route('admin.jadwal.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-gray-900 rounded-2xl px-3 py-2 hover:bg-gray-800 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Hari Ini
            </a>
        </div>

        <x-ui.alert />

        {{-- Stat Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div class="bg-white rounded-3xl border border-gray-200 px-4 py-3">
                <p class="text-xs font-medium text-gray-500">Semua Pesanan ({{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('d M') }})</p>
                <p class="text-xl font-bold text-gray-900 mt-1">{{ $summary['Semua'] }}</p>
            </div>
            <div class="bg-white rounded-3xl border border-gray-200 px-4 py-3">
                <p class="text-xs font-medium text-gray-500">Baru / Diproses</p>
                <p class="text-xl font-bold text-amber-600 mt-1">{{ $summary['baru'] + $summary['diproses'] }}</p>
            </div>
            <div class="bg-white rounded-3xl border border-gray-200 px-4 py-3">
                <p class="text-xs font-medium text-gray-500">Dibatalkan</p>
                <p class="text-xl font-bold text-purple-600 mt-1">{{ $summary['dibatalkan'] }}</p>
            </div>
            <div class="bg-white rounded-3xl border border-gray-200 px-4 py-3">
                <p class="text-xs font-medium text-gray-500">Selesai</p>
                <p class="text-xl font-bold text-emerald-600 mt-1">{{ $summary['selesai'] }}</p>
            </div>
        </div>

        {{-- Filter Bar --}}
        <div class="flex flex-col sm:flex-row gap-2 items-start sm:items-center justify-between mb-3 shrink-0">
            <form action="{{ route('admin.jadwal.index') }}" method="GET" class="flex items-center gap-2 w-full sm:w-auto flex-wrap">
                <input type="date" name="date" value="{{ $selectedDate }}" class="text-xs border border-gray-200 rounded-2xl px-3 py-2 outline-none focus:ring-1 focus:ring-gray-400 transition-all bg-white" onchange="this.form.submit()">
                
                <div class="relative flex-1 sm:flex-none sm:w-56">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau No. Pesanan…" class="w-full pl-8 pr-3 py-2 text-xs border border-gray-200 rounded-2xl outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-all bg-white">
                </div>
                
                <select name="status" class="text-xs border border-gray-200 rounded-2xl bg-white px-3 py-2 outline-none focus:ring-1 focus:ring-gray-400 transition-all" onchange="this.form.submit()">
                    <option value="Semua" {{ request('status') == 'Semua' ? 'selected' : '' }}>Semua Status</option>
                    <option value="menunggu_konfirmasi" {{ request('status') == 1 ? 'selected' : '' }}>Menunggu</option>
                    <option value="diproses" {{ request('status') == 3 ? 'selected' : '' }}>Diproses</option>
                    <option value="dikirim" {{ request('status') == 4 ? 'selected' : '' }}>Sedang Diantar</option>
                    <option value="selesai" {{ request('status') == 5 ? 'selected' : '' }}>Selesai</option>
                </select>
                <button type="submit" class="text-xs font-medium bg-white border border-gray-200 text-gray-600 rounded-2xl px-3 py-2 hover:bg-gray-50 transition-colors shrink-0">Cari</button>
            </form>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-3xl border border-gray-200 overflow-hidden overflow-x-auto">
            <table class="w-full text-sm min-w-[900px]">
                <thead>
                    <tr class="border-b border-gray-100 text-xs font-semibold text-gray-400 uppercase tracking-wide">
                        <th class="px-4 py-3 text-left w-12">No.</th>
                        <th class="px-4 py-3 text-left">Waktu &amp; Info Pesanan</th>
                        <th class="px-4 py-3 text-left">Pelanggan</th>
                        <th class="px-4 py-3 text-left">Lokasi / Alamat</th>
                        <th class="px-4 py-3 text-left">Rincian Paket</th>
                        <th class="px-4 py-3 text-left">Status Pengantaran</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($orders as $i => $order)
                    <tr class="hover:bg-gray-50/60 transition-colors group align-top">
                        <td class="px-4 py-3 text-xs text-gray-500 font-medium">{{ $i + 1 }}</td>
                        
                        {{-- Waktu & Info --}}
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-bold text-gray-900 text-sm">
                                    {{ $order->jadwal_pesanan->tanggal_acara ? \Carbon\Carbon::parse($order->jadwal_pesanan->tanggal_acara)->format('H:i') : 'Belum Diset' }}
                                </span>
                            </div>
                            <p class="font-semibold text-gray-600 font-mono text-[10px]">{{ $order->nomor_pesanan }}</p>
                            @if($order->jenis_pesanan_id == 2)
                                <span class="inline-block mt-1 text-[10px] font-semibold px-2 py-0.5 rounded-xl bg-blue-50 text-blue-700">Catering</span>
                            @else
                                <span class="inline-block mt-1 text-[10px] font-semibold px-2 py-0.5 rounded-xl bg-purple-50 text-purple-700">Nasi Box</span>
                            @endif
                        </td>

                        {{-- Pelanggan --}}
                        <td class="px-4 py-3">
                            <p class="font-semibold text-gray-900 text-xs">{{ $order->catatan }}</p>
                            <p class="text-[10px] text-gray-500 mt-0.5 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                {{ $order->catatan }}
                            </p>
                        </td>

                        {{-- Lokasi --}}
                        <td class="px-4 py-3 max-w-[200px]">
                            <p class="text-xs text-gray-700 leading-relaxed">
                                {{ $order->jenis_pesanan_id == 2 ? $order->jadwal_pesanan->lokasi_acara ?? '-' : $order->jadwal_pesanan->lokasi_acara ?? '-' }}
                            </p>
                        </td>

                        {{-- Rincian Paket --}}
                        <td class="px-4 py-3">
                            <p class="text-xs font-semibold text-gray-800">{{ $order->detail_pesanan->first()->menu->nama_menu ?? '-' ?? 'Paket Kustom' }}</p>
                            <p class="text-[10px] text-gray-500 mt-0.5">{{ $order->jenis_pesanan_id == 2 ? $order->detail_pesanan->first()->kuantitas ?? 0 . ' Porsi' : $order->detail_pesanan->first()->kuantitas ?? 0 . ' Box' }}</p>
                        </td>

                        {{-- Status Pengantaran --}}
                        <td class="px-4 py-3">
                            <form action="{{ route('admin.jadwal.update-status', ['jenis' => $order->jenis_pesanan_id == 2 ? 'Catering' : 'Nasi Box', 'id' => $order->id]) }}" method="POST">
                                @csrf @method('PATCH')
                                <select name="status" onchange="this.form.submit()"
                                        class="text-[11px] font-semibold rounded-2xl border px-2 py-1 outline-none cursor-pointer transition-colors w-full
                                               {{ in_array($order->status_pesanan_id, [3, 1]) ? 'bg-amber-50 text-amber-900 border-amber-200' : '' }}
                                               {{ $order->status_pesanan_id == 4 ? 'bg-purple-50 text-purple-900 border-purple-200' : '' }}
                                               {{ in_array($order->status_pesanan_id, [5, 'lunas']) ? 'bg-emerald-50 text-emerald-900 border-emerald-200' : '' }}
                                               {{ $order->status_pesanan_id == 6 ? 'bg-red-50 text-red-800 border-red-200' : '' }}">
                                    <option value="menunggu_konfirmasi" {{ in_array($order->status_pesanan_id, ['menunggu_dp', 1, 'terkonfirmasi']) ? 'selected' : '' }}>• Menunggu</option>
                                    <option value="diproses" {{ $order->status_pesanan_id == 3 ? 'selected' : '' }}>• Diproses (Dapur)</option>
                                    <option value="dikirim" {{ $order->status_pesanan_id == 4 ? 'selected' : '' }}>• Sedang Diantar</option>
                                    <option value="selesai" {{ in_array($order->status_pesanan_id, [5, 'lunas']) ? 'selected' : '' }}>• Selesai</option>
                                </select>
                            </form>
                        </td>

                        {{-- Aksi --}}
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1 opacity-70 group-hover:opacity-100 transition-opacity">
                                <a href="{{ $order->jenis_pesanan_id == 2 ? url('/admin/pesanan/catering/' . $order->id) : url('/admin/pesanan/nasi-box/' . $order->id) }}" 
                                   class="p-1.5 rounded-xl text-gray-500 hover:bg-gray-100 hover:text-gray-900 transition-colors" title="Lihat Detail Pesanan">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-14 text-center text-gray-400">
                            <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            <p class="text-sm font-medium">Tidak ada jadwal pengantaran untuk tanggal ini.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>
@endsection
