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
        <x-ui.page-header title="Jadwal Pengantaran" subtitle="Kelola jadwal pengiriman pesanan Katering &amp; Nasi Box">
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

        {{-- Filter Bar --}}
        <div class="flex flex-col sm:flex-row gap-2 items-start sm:items-center justify-between mb-3 shrink-0">
            <form action="{{ route('admin.jadwal.index') }}" method="GET" class="flex items-center gap-2 w-full sm:w-auto flex-wrap">
                <input type="date" name="date" value="{{ $selectedDate }}" class="text-sm border border-gray-200 rounded-lg px-3 py-2 outline-none focus:ring-1 focus:ring-gray-400 transition-all bg-white" onchange="this.form.submit()">
                
                <x-search-input name="search" value="{{ request('search') }}" placeholder="Cari nama atau No. Pesanan…" />
                
                <x-select-input name="status" :options="['menunggu_konfirmasi' => 'Menunggu', 'diproses' => 'Diproses', 'dikirim' => 'Sedang Diantar', 'selesai' => 'Selesai']" :selected="request('status')" placeholder="Semua Status" blank-value="Semua" auto-submit="true" />
                <button type="submit" class="text-sm font-medium bg-white border border-gray-200 text-gray-600 rounded-lg px-3 py-2 hover:bg-gray-50 transition-colors shrink-0">Cari</button>
            </form>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden overflow-x-auto">
            <table class="w-full text-sm min-w-[900px]">
                <thead>
                    <tr class="border-b border-gray-100 text-sm font-semibold text-gray-500 uppercase tracking-wide">
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
                        <td class="px-4 py-3 text-sm text-gray-500 font-medium">{{ $i + 1 }}</td>
                        
                        {{-- Waktu & Info --}}
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-bold text-gray-900 text-sm">
                                    {{ $order->jadwal_pesanan->tanggal_acara ? \Carbon\Carbon::parse($order->jadwal_pesanan->tanggal_acara)->format('H:i') : 'Belum Diset' }}
                                </span>
                            </div>
                            <p class="font-semibold text-gray-600 font-mono text-xs">{{ $order->nomor_pesanan }}</p>
                            @if($order->jenis_pesanan_id == 2)
                                <span class="inline-block mt-1 text-xs font-semibold px-2 py-0.5 rounded-xl bg-blue-50 text-blue-700">Katering</span>
                            @else
                                <span class="inline-block mt-1 text-xs font-semibold px-2 py-0.5 rounded-xl bg-purple-50 text-purple-700">Nasi Box</span>
                            @endif
                        </td>

                        {{-- Pelanggan --}}
                        <td class="px-4 py-3">
                            <p class="font-semibold text-gray-900 text-xs">{{ $order->catatan }}</p>
                            <p class="text-xs text-gray-500 mt-0.5 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                {{ $order->catatan }}
                            </p>
                        </td>

                        {{-- Lokasi --}}
                        <td class="px-4 py-3 max-w-[200px]">
                            <p class="text-xs text-gray-700 leading-relaxed">
                                {{ $order->jadwal_pesanan->alamat_pengantaran ?? '-' }}
                            </p>
                        </td>

                        {{-- Rincian Paket --}}
                        <td class="px-4 py-3">
                            <p class="text-xs font-semibold text-gray-800">{{ $order->detail_pesanan->first()->menu->nama_menu ?? 'Paket Kustom' }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $order->detail_pesanan->first()->jumlah ?? 0 }} {{ $order->jenis_pesanan_id == 2 ? 'Porsi' : 'Box' }}</p>
                        </td>

                        {{-- Status Pengantaran --}}
                        <td class="px-4 py-3">
                            @php $pengantaran = $order->pengantaran; @endphp
                            @if($pengantaran)
                            <form action="{{ route('admin.jadwal-pengantaran.update-pengantaran-status', $pengantaran->id) }}" method="POST">
                                @csrf @method('PATCH')
                                <select name="status_pengantaran_id" onchange="this.form.submit()"
                                        class="text-xs font-semibold rounded-lg border px-2 py-1 outline-none cursor-pointer transition-colors w-full
                                               {{ $pengantaran->status_pengantaran_id <= 2 ? 'bg-amber-50 text-amber-900 border-amber-200' : '' }}
                                               {{ $pengantaran->status_pengantaran_id == 3 ? 'bg-blue-50 text-blue-900 border-blue-200' : '' }}
                                               {{ $pengantaran->status_pengantaran_id == 4 ? 'bg-emerald-50 text-emerald-900 border-emerald-200' : '' }}
                                               {{ $pengantaran->status_pengantaran_id == 5 ? 'bg-red-50 text-red-800 border-red-200' : '' }}">
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
                    <x-empty-state icon="clock" title="Tidak ada jadwal pengantaran" message="Tidak ada jadwal pengantaran untuk tanggal ini." :colspan="7" />
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>
@endsection
