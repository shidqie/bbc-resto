@extends('layouts.pos')
@section('title', 'Pesanan Katering')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800">
    <div class="w-full p-6 space-y-5">

        {{-- PAGE HEADER --}}
        <x-ui.page-header
            title="Pesanan Katering"
            subtitle="Kelola seluruh transaksi pesanan catering, tanggal acara, rincian porsi & pembayaran DP."
            :breadcrumbs="['Penjualan', 'Katering']">
        </x-ui.page-header>

        <x-ui.alert />

        {{-- Table with integrated toolbar --}}
        <x-ui.data-table :paginator="$pesanans">
            <x-slot:toolbar>
                <div class="flex flex-col sm:flex-row gap-2 items-start sm:items-center justify-between w-full">
                    <form action="{{ route('admin.pesanan.catering.index') }}" method="GET" class="flex items-center gap-2 flex-wrap">
                        <x-search-input name="search" value="{{ request('search') }}" placeholder="Cari Kode / Pemesan / HP…" />
                        <input type="hidden" name="status" value="{{ request('status', 'all') }}">
                        <button type="submit" class="text-sm font-medium bg-white border border-gray-200 text-gray-600 rounded-lg px-3 py-2 hover:bg-gray-50 transition-colors shrink-0">Cari</button>
                    </form>
                    <div class="flex items-center gap-1 text-xs font-medium overflow-x-auto no-scrollbar shrink-0">
                        <span class="text-gray-500 mr-1">Status:</span>
                        <a href="{{ route('admin.pesanan.catering.index', ['status' => 'all']) }}" class="px-3 py-1.5 rounded-lg transition-colors {{ request('status', 'all') === 'all' ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100' }}">Semua</a>
                        <a href="{{ route('admin.pesanan.catering.index', ['status' => 'ditinjau']) }}" class="px-3 py-1.5 rounded-lg transition-colors {{ request('status') === 'ditinjau' ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100' }}">Baru</a>
                        <a href="{{ route('admin.pesanan.catering.index', ['status' => 'terkonfirmasi']) }}" class="px-3 py-1.5 rounded-lg transition-colors {{ request('status') === 'terkonfirmasi' ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100' }}">Terkonfirmasi</a>
                        <a href="{{ route('admin.pesanan.catering.index', ['status' => 'diproses']) }}" class="px-3 py-1.5 rounded-lg transition-colors {{ request('status') === 'diproses' ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100' }}">Diproses</a>
                        <a href="{{ route('admin.pesanan.catering.index', ['status' => 'selesai']) }}" class="px-3 py-1.5 rounded-lg transition-colors {{ request('status') === 'selesai' ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100' }}">Selesai</a>
                    </div>
                </div>
            </x-slot:toolbar>

            <table class="w-full text-sm min-w-[1100px]">
                <thead>
                    <tr class="border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        <th class="px-4 py-3 text-left w-12">No</th>
                        <th class="px-4 py-3 text-left">Tanggal Pesan</th>
                        <th class="px-4 py-3 text-left">ID Pesanan</th>
                        <th class="px-4 py-3 text-left">Pelanggan</th>
                        <th class="px-4 py-3 text-left">Tanggal Acara</th>
                        <th class="px-4 py-3 text-left">Jumlah Porsi</th>
                        <th class="px-4 py-3 text-right">Total</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Pembayaran</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($pesanans as $p)
                    <tr class="hover:bg-gray-50/60 transition-colors group">
                        <td class="px-4 py-3 text-sm text-gray-500 font-medium align-middle">
                            {{ $loop->iteration + ($pesanans->currentPage() - 1) * $pesanans->perPage() }}
                        </td>
                        <td class="px-4 py-3 align-middle whitespace-nowrap">
                            <p class="font-medium text-gray-900 text-sm">{{ $p->dibuat_pada ? \Carbon\Carbon::parse($p->dibuat_pada)->format('d M Y') : '-' }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $p->dibuat_pada ? \Carbon\Carbon::parse($p->dibuat_pada)->format('H:i') : '' }}</p>
                        </td>
                        <td class="px-4 py-3 align-middle">
                            <span class="font-mono text-xs font-bold text-gray-900">{{ $p->nomor_pesanan }}</span>
                        </td>
                        <td class="px-4 py-3 align-middle">
                            <p class="font-medium text-gray-900 text-sm">{{ optional($p->pelanggan)->nama ?? $p->jadwal_pesanan->nama_penerima ?? '-' }}</p>
                            @if($p->pelanggan && $p->pelanggan->nomor_telepon)
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $p->pelanggan->nomor_telepon) }}" target="_blank" class="text-xs text-emerald-600 font-medium hover:underline inline-flex items-center gap-1 mt-0.5">
                                    <i class="ph-bold ph-whatsapp-logo"></i>
                                    <span class="whitespace-nowrap">{{ $p->pelanggan->nomor_telepon }}</span>
                                </a>
                            @endif
                        </td>
                        <td class="px-4 py-3 align-middle whitespace-nowrap">
                            @if($p->jadwal_pesanan?->tanggal_acara)
                                <p class="font-medium text-gray-900 text-sm">{{ \Carbon\Carbon::parse($p->jadwal_pesanan->tanggal_acara)->format('d M Y') }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ \Carbon\Carbon::parse($p->jadwal_pesanan->tanggal_acara)->format('H:i') }}</p>
                            @else
                                <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 align-middle">
                            @php $paket = $p->detail_pesanan->first(); @endphp
                            <x-ui.badge color="primary" size="sm">{{ $paket->jumlah ?? 0 }} Porsi</x-ui.badge>
                            <p class="text-xs text-gray-500 mt-1 truncate max-w-[120px]">{{ $paket->menu->nama_menu ?? 'Paket Katering' }}</p>
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-gray-900 tabular-nums whitespace-nowrap">
                            Rp {{ number_format($p->total_tagihan, 0, ',', '.') }}
                            @php $totalP = (float) $p->total_tagihan; @endphp
                        </td>
                        <td class="px-4 py-3 text-center align-middle">
                            @php
                                $sColorMap = [1 => 'warning', 2 => 'primary', 3 => 'primary', 4 => 'primary', 5 => 'success', 6 => 'danger'];
                                $sColor = $sColorMap[$p->status_pesanan_id] ?? 'gray';
                            @endphp
                            <x-ui.badge :color="$sColor" size="sm">{{ $p->status_pesanan->nama_status ?? '-' }}</x-ui.badge>
                        </td>
                        <td class="px-4 py-3 align-middle text-center">
                            @php
                                $dpP = (float) $p->pembayaran->whereIn('status_pembayaran_id', [2, 3])->sum('jumlah_bayar');
                                $lunasP = (float) $p->pembayaran->where('status_pembayaran_id', 3)->sum('jumlah_bayar');
                                $bayarP = $lunasP >= $totalP ? 'lunas' : ($dpP > 0 ? 'dp' : 'belum');
                                $bayarColor = $bayarP === 'lunas' ? 'success' : ($bayarP === 'dp' ? 'primary' : 'warning');
                                $bayarLabel = $bayarP === 'lunas' ? 'Lunas' : ($bayarP === 'dp' ? 'DP Terbayar' : 'Belum Bayar');
                            @endphp
                            <x-ui.badge :color="$bayarColor" size="sm">{{ $bayarLabel }}</x-ui.badge>
                            @if($dpP > 0)
                                <p class="text-xs text-gray-400 mt-0.5">Rp {{ number_format($dpP, 0, ',', '.') }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('admin.pesanan.catering.show', $p->id) }}" title="Detail" class="w-7 h-7 rounded-full flex items-center justify-center bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors">
                                    <x-heroicon-o-eye class="w-3 h-3" />
                                </a>
                                @php $buktiPending = $p->pembayaran->firstWhere('status_pembayaran_id', 1); @endphp
                                @if($buktiPending)
                                    <form id="form-verif-{{ $buktiPending->id }}" action="{{ route('admin.bukti.verifikasi-dp', $buktiPending->id) }}" method="POST" class="hidden">
                                        @csrf @method('PATCH')
                                    </form>
                                    <button type="button" onclick="window.confirmDialog({ title: 'Verifikasi Pembayaran', name: 'Verifikasi bukti pembayaran pesanan ini?', message: 'Pastikan bukti transfer sudah benar sebelum diverifikasi.', formId: 'form-verif-{{ $buktiPending->id }}', confirmText: 'Verifikasi', cancelText: 'Batal' })" title="Verifikasi" class="w-7 h-7 rounded-full flex items-center justify-center bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition-colors">
                                        <x-heroicon-o-check-badge class="w-3 h-3" />
                                    </button>
                                @endif
                                <a href="{{ route('admin.pesanan.catering.pdf', $p->id) }}" target="_blank" title="Cetak" class="w-7 h-7 rounded-full flex items-center justify-center bg-gray-50 text-gray-600 hover:bg-gray-100 transition-colors">
                                    <x-heroicon-o-printer class="w-3 h-3" />
                                </a>
                                @if(!in_array($p->status_pesanan_id, [5, 6]))
                                    <form id="form-batal-{{ $p->id }}" action="{{ route('admin.pesanan.catering.update-status', $p->id) }}" method="POST" class="hidden">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="6">
                                        <input type="hidden" name="alasan_batal" value="">
                                    </form>
                                    <button type="button" onclick="window.confirmPrompt({ title: 'Batalkan Pesanan', name: 'Batalkan pesanan ini?', message: 'Masukkan alasan pembatalan. Aksi ini tidak dapat dibatalkan.', formId: 'form-batal-{{ $p->id }}', confirmText: 'Batalkan', cancelText: 'Batal', promptPlaceholder: 'Tulis alasan pembatalan' })" title="Batalkan" class="w-7 h-7 rounded-full flex items-center justify-center bg-rose-50 text-rose-600 hover:bg-rose-100 transition-colors">
                                        <x-heroicon-o-no-symbol class="w-3 h-3" />
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <x-empty-state icon="clipboard-document-list" title="Tidak ada pesanan katering" message="Tidak ada data pesanan catering." :colspan="10" />
                    @endforelse
                </tbody>
            </table>
        </x-ui.data-table>

    </div>
</div>
@endsection