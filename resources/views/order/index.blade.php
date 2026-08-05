{{-- Halaman: Semua Pesanan --}}
@extends('layouts.pos')
@section('title', 'Semua Pesanan')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800">
    <div class="w-full p-6 space-y-5">

        {{-- PAGE HEADER --}}
        <x-ui.page-header
            title="Semua Pesanan"
            subtitle="Daftar seluruh pesanan Dine In, Katering, dan Nasi Box."
            :breadcrumbs="['Pesanan', 'Semua Pesanan']">
            <x-slot:actions>
                <a href="{{ route('pos.dinein.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-gray-900 rounded-lg px-3 py-2 hover:bg-gray-800 transition-colors">
                    <x-heroicon-o-plus class="w-4 h-4" />
                    Pesanan Baru (POS)
                </a>
            </x-slot:actions>
        </x-ui.page-header>

        {{-- Stat Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <x-ui.stat-card label="Pesanan Baru" :value="$stats['baru']" icon="inbox" color="blue" />
            <x-ui.stat-card label="Sedang Diproses" :value="$stats['diproses']" icon="clock" color="orange" />
            <x-ui.stat-card label="Pesanan Selesai" :value="$stats['selesai']" icon="check-circle" color="green" />
            <x-ui.stat-card label="Total Transaksi" :value="$pesanans->total()" icon="document-text" color="brand" />
        </div>

        <x-ui.alert />

        {{-- Table with integrated toolbar --}}
        <x-ui.data-table :paginator="$pesanans">
            <x-slot:toolbar>
                <form action="{{ route('pesanan.index') }}" method="GET" class="flex items-center gap-2 w-full flex-wrap">
                    <x-search-input name="search" value="{{ request('search') }}" placeholder="Cari No. Pesanan / Nama / Meja…" />
                    <x-select-input name="jenis" :options="['dine_in' => 'Dine In', 'catering' => 'Katering', 'nasi_box' => 'Nasi Box']" :selected="request('jenis')" placeholder="Semua Jenis" :auto-submit="true" />
                    <x-select-input name="status" :options="['baru' => 'Baru', 'diproses' => 'Diproses', 'selesai' => 'Selesai', 'dibatalkan' => 'Dibatalkan']" :selected="request('status')" placeholder="Semua Status" :auto-submit="true" />
                    <button type="submit" class="text-sm font-medium bg-white border border-gray-200 text-gray-600 rounded-lg px-3 py-2 hover:bg-gray-50 transition-colors shrink-0">Cari</button>
                    @if(request()->hasAny(['search', 'jenis', 'status']))
                        <a href="{{ route('pesanan.index') }}" class="text-xs font-medium text-red-500 hover:text-red-700 px-2 py-2 rounded-lg hover:bg-red-50 transition-colors shrink-0">Reset</a>
                    @endif
                </form>
            </x-slot:toolbar>

            <table class="w-full text-sm min-w-[900px]">
                <thead>
                    <tr class="border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        <th class="px-4 py-3 text-left w-12">No</th>
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
                        <td class="px-4 py-3 text-sm text-gray-500 font-medium">{{ $pesanans->firstItem() + $i }}</td>

                        {{-- Info Pesanan --}}
                        <td class="px-4 py-3">
                            <p class="font-semibold text-gray-900 font-mono text-xs">{{ $p->no_pesanan }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $p->tanggal_pesanan ? $p->tanggal_pesanan->format('d M Y, H:i') : '-' }}</p>
                            <span class="inline-block mt-1 text-xs font-semibold px-2 py-0.5 rounded-xl bg-blue-50 text-blue-700 uppercase">
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
                                <div class="w-6 h-6 rounded-full bg-gray-100 text-gray-700 font-bold text-xs flex items-center justify-center shrink-0">
                                    {{ strtoupper(substr($nama, 0, 1)) }}
                                </div>
                                <p class="font-semibold text-gray-900 text-xs truncate max-w-[120px]">{{ $nama }}</p>
                            </div>
                            @if($phone)
                                <p class="text-xs text-emerald-600 font-medium mt-0.5">{{ $phone }}</p>
                            @endif
                            @if($p->no_meja)
                                <span class="inline-block mt-1 text-xs font-semibold px-1.5 py-0.5 rounded-xl bg-amber-50 text-amber-800">Meja {{ $p->no_meja }}</span>
                            @endif
                        </td>

                        {{-- Rincian Menu --}}
                        <td class="px-4 py-3 max-w-[220px]">
                            @if($p->details && $p->details->count() > 0)
                                <div class="space-y-0.5">
                                    @foreach($p->details->take(2) as $d)
                                        <div class="text-xs text-gray-700 flex items-baseline justify-between gap-1">
                                            <span class="truncate"><span class="font-bold text-blue-500">{{ $d->jumlah }}x</span> {{ $d->menu->nama ?? 'Item' }}</span>
                                            <span class="text-xs text-gray-400 shrink-0">Rp {{ number_format($d->subtotal ?? ($d->jumlah * $d->harga_satuan), 0, ',', '.') }}</span>
                                        </div>
                                    @endforeach
                                    @if($p->details->count() > 2)
                                        <p class="text-xs text-gray-400 italic">+{{ $p->details->count() - 2 }} item lainnya...</p>
                                    @endif
                                </div>
                            @else
                                <span class="text-xs text-gray-400 italic">Tidak ada rincian</span>
                            @endif
                        </td>

                        {{-- Pembayaran --}}
                        <td class="px-4 py-3">
                            <p class="font-semibold text-gray-900 text-sm">Rp {{ number_format($p->total_harga, 0, ',', '.') }}</p>
                            @if($p->status_pembayaran == 'lunas')
                                <x-ui.badge color="success" dot>Lunas</x-ui.badge>
                            @elseif($p->status_pembayaran == 'dp')
                                <x-ui.badge color="primary" dot>DP</x-ui.badge>
                            @else
                                <x-ui.badge color="danger" dot>Belum Bayar</x-ui.badge>
                            @endif
                        </td>

                        {{-- Status Pesanan --}}
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
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('pesanan.show', $p->id) }}" title="Detail" class="w-7 h-7 rounded-full flex items-center justify-center bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors">
                                    <x-heroicon-o-eye class="w-3 h-3" />
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <x-empty-state icon="clipboard" title="Belum ada data pesanan" message="Tidak ada data pesanan." :colspan="7" />
                    @endforelse
                </tbody>
            </table>
        </x-ui.data-table>

    </div>
</div>
@endsection
