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
        <x-ui.page-header title="Jadwal Pengantaran" subtitle="Kelola jadwal pengiriman pesanan Katering &amp; Nasi Box" :breadcrumbs="['Penjualan', 'Jadwal Pengantaran']">
            <x-slot:actions>
                <a href="{{ route('admin.jadwal.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-gray-900 rounded-lg px-3 py-2 hover:bg-gray-800 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Hari Ini
                </a>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.alert />

        {{-- Stat Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div class="bg-white rounded-xl border border-gray-200 px-4 py-3">
                <p class="text-sm font-medium text-gray-500">Semua Pesanan ({{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('d M') }})</p>
                <p class="text-xl font-bold text-gray-900 mt-1">{{ $summary['Semua'] }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 px-4 py-3">
                <p class="text-sm font-medium text-gray-500">Baru / Diproses</p>
                <p class="text-xl font-bold text-amber-600 mt-1">{{ $summary['baru'] + $summary['diproses'] }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 px-4 py-3">
                <p class="text-sm font-medium text-gray-500">Dibatalkan</p>
                <p class="text-xl font-bold text-purple-600 mt-1">{{ $summary['dibatalkan'] }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 px-4 py-3">
                <p class="text-sm font-medium text-gray-500">Selesai</p>
                <p class="text-xl font-bold text-emerald-600 mt-1">{{ $summary['selesai'] }}</p>
            </div>
        </div>

        {{-- Table with integrated toolbar --}}
        <x-ui.data-table>
            <x-slot:toolbar>
                <form action="{{ route('admin.jadwal.index') }}" method="GET" class="flex items-center gap-2 w-full flex-wrap">
                    <input type="date" name="date" value="{{ $selectedDate }}" class="text-sm border border-gray-200 rounded-lg px-3 py-2 outline-none focus:ring-1 focus:ring-gray-400 transition-all bg-white" onchange="this.form.submit()">
                    
                    <x-search-input name="search" value="{{ request('search') }}" placeholder="Cari nama atau No. Pesanan…" />
                    
                    <x-ui.multi-select name="status" :options="['menunggu_konfirmasi' => 'Menunggu', 'diproses' => 'Diproses', 'dikirim' => 'Sedang Diantar', 'selesai' => 'Selesai']" :selected="request('status')" label="Status" type="radio" />
                </form>
            </x-slot:toolbar>

            <x-ui.table class="min-w-[900px]">
                <x-ui.table.header>
                    <th class="px-4 py-3.5 text-left w-12">No.</th>
                    <th class="px-4 py-3.5 text-left">Waktu &amp; Info Pesanan</th>
                    <th class="px-4 py-3.5 text-left">Pelanggan</th>
                    <th class="px-4 py-3.5 text-left">Lokasi / Alamat</th>
                    <th class="px-4 py-3.5 text-left">Rincian Paket</th>
                    <th class="px-4 py-3.5 text-left">Status Pengantaran</th>
                    <th class="px-4 py-3.5 text-right">Aksi</th>
                </x-ui.table.header>
                <tbody class="divide-y divide-gray-100">
                    @forelse($orders as $i => $order)
                    <x-ui.table.row class="align-top">
                        <td class="px-4 py-4 text-sm text-gray-500 font-medium">{{ $i + 1 }}</td>
                        
                        {{-- Waktu & Info --}}
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-bold text-gray-900 text-sm">
                                    {{ $order->jadwal_pesanan->tanggal_acara ? \Carbon\Carbon::parse($order->jadwal_pesanan->tanggal_acara)->format('H:i') : 'Belum Diset' }}
                                </span>
                            </div>
                            <p class="font-semibold text-gray-600 font-mono text-xs">{{ $order->nomor_pesanan }}</p>
                            @if($order->jenis_pesanan_id == 2)
                                <x-ui.badge color="primary" size="sm" class="mt-1">Katering</x-ui.badge>
                            @else
                                <span class="inline-block mt-1 text-xs font-semibold px-2 py-0.5 rounded-xl bg-purple-50 text-purple-700">Nasi Box</span>
                            @endif
                        </td>

                        {{-- Pelanggan --}}
                        <td class="px-4 py-4">
                            <p class="font-semibold text-gray-900 text-xs">{{ $order->catatan }}</p>
                            <p class="text-xs text-gray-500 mt-0.5 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                {{ $order->catatan }}
                            </p>
                        </td>

                        {{-- Lokasi --}}
                        <td class="px-4 py-4 max-w-[200px]">
                            <p class="text-xs text-gray-700 leading-relaxed">
                                {{ $order->jadwal_pesanan->alamat_pengantaran ?? '-' }}
                            </p>
                        </td>

                        {{-- Rincian Paket --}}
                        <td class="px-4 py-4">
                            <p class="text-xs font-semibold text-gray-800">{{ $order->detail_pesanan->first()->menu->nama_menu ?? 'Paket Kustom' }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $order->detail_pesanan->first()->jumlah ?? 0 }} {{ $order->jenis_pesanan_id == 2 ? 'Porsi' : 'Box' }}</p>
                        </td>

                        {{-- Status Pengantaran --}}
                        <td class="px-4 py-4">
                            @php
                                $pengantaran = $order->pengantaran;
                                $pSelClass = '';
                                if($pengantaran) {
                                    if($pengantaran->status_pengantaran_id <= 2) $pSelClass = 'bg-amber-50 text-amber-900 border-amber-200';
                                    elseif($pengantaran->status_pengantaran_id == 3) $pSelClass = 'bg-blue-50 text-blue-900 border-blue-200';
                                    elseif($pengantaran->status_pengantaran_id == 4) $pSelClass = 'bg-emerald-50 text-emerald-900 border-emerald-200';
                                    elseif($pengantaran->status_pengantaran_id == 5) $pSelClass = 'bg-red-50 text-red-800 border-red-200';
                                }
                            @endphp
                            @if($pengantaran)
                            <form action="{{ route('admin.jadwal-pengantaran.update-pengantaran-status', $pengantaran->id) }}" method="POST">
                                @csrf @method('PATCH')
                                <select name="status_pengantaran_id" onchange="this.form.submit()"
                                        class="text-xs font-semibold rounded-lg border px-2 py-1 outline-none cursor-pointer transition-colors w-full {{ $pSelClass }}">
                                    <option value="1" {{ $pengantaran->status_pengantaran_id == 1 ? 'selected' : '' }}>• Dijadwalkan</option>
                                    <option value="2" {{ $pengantaran->status_pengantaran_id == 2 ? 'selected' : '' }}>• Siap Dikirim</option>
                                    <option value="3" {{ $pengantaran->status_pengantaran_id == 3 ? 'selected' : '' }}>• Dalam Perjalanan</option>
                                    <option value="4" {{ $pengantaran->status_pengantaran_id == 4 ? 'selected' : '' }}>• Diterima</option>
                                    <option value="5" {{ $pengantaran->status_pengantaran_id == 5 ? 'selected' : '' }}>• Gagal Dikirim</option>
                                </select>
                            </form>
                            @else
                            <span class="text-xs text-gray-400 font-medium">-</span>
                            @endif
                        </td>

                        {{-- Aksi --}}
                        <td class="px-4 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ $order->jenis_pesanan_id == 2 ? url('/admin/pesanan/catering/' . $order->id) : url('/admin/pesanan/nasi-box/' . $order->id) }}" class="text-gray-500 transition hover:text-gray-900" title="Lihat Detail Pesanan">
                                    <x-heroicon-o-eye class="w-4 h-4" />
                                </a>
                            </div>
                        </td>
                    </x-ui.table.row>
                    @empty
                    <x-empty-state icon="clock" title="Tidak ada jadwal pengantaran" message="Tidak ada jadwal pengantaran untuk tanggal ini." :colspan="7" />
                    @endforelse
                </tbody>
            </x-ui.table>
        </x-ui.data-table>

    </div>
</div>
@endsection
