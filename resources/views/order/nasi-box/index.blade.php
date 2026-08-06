@extends('layouts.pos')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800">
    <div class="w-full p-6 space-y-5">
        
        {{-- Header --}}
        <x-ui.page-header title="Daftar Pesanan Nasi Box" subtitle="Kelola transaksi pesanan nasi box, tanggal kirim, rincian box & status pembayaran." :breadcrumbs="['Penjualan', 'Nasi Box']" />

        {{-- Alert --}}
        <x-ui.alert />

        {{-- Table with integrated toolbar --}}
        <x-ui.data-table :paginator="$pesanans">
            <x-slot:toolbar>
                <div class="flex flex-col sm:flex-row gap-2 items-start sm:items-center justify-between w-full">
                    <form action="{{ route('admin.pesanan.nasibox.index') }}" method="GET" class="flex items-center gap-2 w-full sm:w-auto">
                        <x-search-input name="search" value="{{ request('search') }}" placeholder="Cari Kode / Pemesan / HP…" width="w-full sm:w-56" />
                        <input type="hidden" name="status" value="{{ request('status', 'all') }}">
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
            </x-slot:toolbar>

            <x-ui.table class="min-w-[1100px]">
                <x-ui.table.header>
                    <th class="px-4 py-3.5 text-left w-12">No.</th>
                    <th class="px-4 py-3.5 text-left">Tanggal Pesan</th>
                    <th class="px-4 py-3.5 text-left">Kode Pesanan</th>
                    <th class="px-4 py-3.5 text-left">Pelanggan</th>
                    <th class="px-4 py-3.5 text-left">Tanggal Dibutuhkan</th>
                    <th class="px-4 py-3.5 text-left">Jumlah Box</th>
                    <th class="px-4 py-3.5 text-left">Total</th>
                    <th class="px-4 py-3.5 text-left">Status Pesanan</th>
                    <th class="px-4 py-3.5 text-left">Pembayaran</th>
                    <th class="px-4 py-3.5 text-right">Aksi</th>
                </x-ui.table.header>
                <tbody class="divide-y divide-gray-100">
                    @forelse($pesanans as $p)
                        <x-ui.table.row>
                            <td class="px-4 py-4 text-sm text-gray-500 font-medium align-middle">
                                {{ $loop->iteration + ($pesanans->currentPage() - 1) * $pesanans->perPage() }}
                            </td>
                            <td class="px-4 py-4 align-middle whitespace-nowrap text-sm text-gray-700">
                                {{ $p->dibuat_pada ? \Carbon\Carbon::parse($p->dibuat_pada)->translatedFormat('d M Y, H.i') . ' WIB' : '-' }}
                            </td>
                            <td class="px-4 py-4 align-middle">
                                <p class="font-semibold text-gray-900 font-mono text-xs whitespace-nowrap">{{ $p->nomor_pesanan }}</p>
                            </td>
                            <td class="px-4 py-4 align-middle">
                                <p class="font-semibold text-gray-900 text-xs whitespace-nowrap">{{ optional($p->pelanggan)->nama ?? $p->jadwal_pesanan->nama_penerima ?? 'Nasi Box' }}</p>
                                @if(optional($p->pelanggan)->nomor_telepon ?? $p->jadwal_pesanan->nomor_telepon_penerima)
                                    @php $phone = optional($p->pelanggan)->nomor_telepon ?? $p->jadwal_pesanan->nomor_telepon_penerima; @endphp
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $phone) }}" target="_blank" class="text-xs text-emerald-600 font-medium hover:underline inline-flex items-center gap-1 mt-0.5">
                                        <i class="ph-bold ph-whatsapp-logo"></i>
                                        <span class="whitespace-nowrap">{{ $phone }}</span>
                                    </a>
                                @endif
                            </td>
                            <td class="px-4 py-4 align-middle whitespace-nowrap">
                                @if($p->jadwal_pesanan?->tanggal_acara)
                                    <p class="text-xs text-gray-700 font-medium">{{ \Carbon\Carbon::parse($p->jadwal_pesanan->tanggal_acara)->format('d M Y') }}</p>
                                    <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($p->jadwal_pesanan->tanggal_acara)->format('H:i') }}</p>
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 align-middle">
                                @php $paket = $p->detail_pesanan->first(); @endphp
                                <span class="inline-block text-xs font-semibold px-2 py-0.5 rounded-xl bg-purple-50 text-purple-700 whitespace-nowrap">
                                    {{ $paket->jumlah ?? 0 }} Box
                                </span>
                                <p class="text-xs text-gray-500 mt-1 truncate max-w-[120px]">{{ $paket->menu->nama_menu ?? 'Paket Nasi Box' }}</p>
                            </td>
                            <td class="px-4 py-4 font-semibold text-gray-900 tabular-nums whitespace-nowrap">
                                Rp {{ number_format($p->total_tagihan, 0, ',', '.') }}
                                @php $totalP = (float) $p->total_tagihan; @endphp
                            </td>
                            <td class="px-4 py-4 align-middle">
                                @php
                                    $sColors = [
                                        1 => 'warning',
                                        2 => 'primary',
                                        3 => 'primary',
                                        4 => 'primary',
                                        5 => 'success',
                                        6 => 'danger',
                                    ];
                                    $sColor = $sColors[$p->status_pesanan_id] ?? 'gray';
                                @endphp
                                <x-ui.badge :color="$sColor" size="sm">
                                    {{ $p->status_pesanan->nama_status ?? '-' }}
                                </x-ui.badge>
                            </td>
                            <td class="px-4 py-4 align-middle">
                                @php
                                    $dpP = (float) $p->pembayaran->where('status_verifikasi', 'diterima')->sum('jumlah_dibayar');
                                    $lunasP = (float) $p->pembayaran->where('status_verifikasi', 'diterima')->sum('jumlah_dibayar');
                                    $bayarP = $lunasP >= $totalP ? 'lunas' : ($dpP > 0 ? 'dp' : 'belum');
                                @endphp
                                <span class="text-xs font-bold uppercase tracking-wider whitespace-nowrap {{ $bayarP === 'lunas' ? 'text-emerald-600' : ($bayarP === 'dp' ? 'text-blue-600' : 'text-amber-600') }}">
                                    {{ $bayarP === 'lunas' ? 'Lunas' : ($bayarP === 'dp' ? 'DP Terbayar' : 'Belum Bayar') }}
                                </span>
                                @if($dpP > 0)
                                    <p class="text-xs text-gray-400 mt-0.5">Rp {{ number_format($dpP, 0, ',', '.') }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('admin.pesanan.nasibox.show', $p->id) }}" title="Detail" class="text-gray-500 transition hover:text-gray-900">
                                        <x-heroicon-o-eye class="w-4 h-4" />
                                    </a>
                                    <x-ui.action-button onclick="window.showToast('info', 'Fitur Ubah Pesanan belum tersedia')" title="Ubah">
                                        <x-heroicon-o-pencil-square class="w-4 h-4" />
                                    </x-ui.action-button>
                                    <a href="#" onclick="window.showToast('info', 'Fitur Cetak PDF belum tersedia')" title="Cetak" class="text-gray-500 transition hover:text-gray-900">
                                        <x-heroicon-o-printer class="w-4 h-4" />
                                    </a>
                                </div>
                            </td>
                        </x-ui.table.row>
                    @empty
                        <x-empty-state icon="document-text" title="Tidak ada data pesanan nasi box." :colspan="10" />
                    @endforelse
                </tbody>
            </x-ui.table>
        </x-ui.data-table>

    </div>
</div>
@endsection