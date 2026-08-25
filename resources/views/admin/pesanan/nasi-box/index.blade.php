@extends('layouts.pos')
@section('title', 'Pesanan Nasi Box')

@section('content')
@php
    $isDapur = auth()->user()->hasRole('Dapur', 'Tim Dapur') || in_array(auth()->user()->peran?->nama_peran, ['Dapur', 'Tim Dapur', 'Koki']);
@endphp
<div class="flex-1 bg-gray-50 text-gray-800">
    <div class="w-full p-6 space-y-5">
        
        {{-- Header --}}
        <x-ui.page-header title="Daftar Pesanan Nasi Box" subtitle="{{ $isDapur ? 'Kelola dan proses persiapan hidangan pesanan Nasi Box.' : 'Kelola transaksi pesanan nasi box, tanggal kirim, rincian box & status pembayaran.' }}" :breadcrumbs="['Penjualan', 'Nasi Box']" />

        {{-- Alert --}}
        <x-ui.alert />

        {{-- Table with integrated toolbar --}}
        <x-ui.data-table :paginator="$pesanans">
            <x-slot:toolbar>
                <form action="{{ route('admin.pesanan.nasibox.index') }}" method="GET" class="flex items-center gap-2 w-full flex-wrap">
                    <x-search-input name="search" value="{{ request('search') }}" placeholder="Cari Kode / Pemesan / HP…" />
                    
                    <x-ui.multi-select name="status" :options="['all' => 'Semua Status', 'ditinjau' => 'Baru', 'terkonfirmasi' => 'Terkonfirmasi', 'diproses' => 'Diproses', 'selesai' => 'Selesai', 'dibatalkan' => 'Dibatalkan']" :selected="request('status', 'all')" label="Status Pesanan" type="radio" />
                    
                    @if(!$isDapur)
                        <x-ui.multi-select name="status_pembayaran" :options="['all' => 'Semua Pembayaran', 'menunggu_dp' => 'Menunggu Pembayaran DP', 'verifikasi_dp' => 'Menunggu Verifikasi DP', 'menunggu_pelunasan' => 'Menunggu Pelunasan', 'verifikasi_lunas' => 'Menunggu Verifikasi Pelunasan', 'lunas' => 'Lunas']" :selected="request('status_pembayaran', 'all')" label="Status Pembayaran" type="radio" />
                    @endif
                    
                    <x-ui.multi-select name="periode" :options="['hari_ini' => 'Hari Ini', 'minggu_ini' => 'Minggu Ini', 'bulan_ini' => 'Bulan Ini', 'kustom' => 'Kustom']" :selected="request('periode')" label="Periode Pesanan" type="radio" />
                    
                    <template x-if="new URLSearchParams(window.location.search).get('periode') === 'kustom'">
                        <div class="flex items-center gap-2">
                            <x-ui.input type="date" name="start_date" value="{{ request('start_date') }}" />
                            <span class="text-gray-500 text-sm">s/d</span>
                            <x-ui.input type="date" name="end_date" value="{{ request('end_date') }}" />
                            <x-ui.button type="submit" variant="primary">Terapkan</x-ui.button>
                        </div>
                    </template>

                    @if(request()->hasAny(['search', 'status', 'status_pembayaran', 'periode', 'start_date', 'end_date']))
                        <x-ui.button href="{{ route('admin.pesanan.nasibox.index') }}" variant="danger" size="sm">Reset</x-ui.button>
                    @endif
                </form>
            </x-slot:toolbar>

            @if($isDapur)
                <x-ui.table class="min-w-[850px]">
                    <x-ui.table.header>
                        <th class="px-4 py-3.5 text-left w-12">No</th>
                        <th class="px-4 py-3.5 text-left">Waktu Pesanan</th>
                        <th class="px-4 py-3.5 text-left">Kode Pesanan</th>
                        <th class="px-4 py-3.5 text-left">Menu / Paket</th>
                        <th class="px-4 py-3.5 text-left">Tanggal Acara</th>
                        <th class="px-4 py-3.5 text-center">Status Pesanan</th>
                        <th class="px-4 py-3.5 text-center">Aksi</th>
                    </x-ui.table.header>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($pesanans as $p)
                        <x-ui.table.row>
                            <td class="px-4 py-4 text-sm text-gray-500 font-medium align-middle">
                                {{ $loop->iteration + ($pesanans->currentPage() - 1) * $pesanans->perPage() }}
                            </td>
                            <td class="px-4 py-4 align-middle whitespace-nowrap text-sm text-gray-700 font-medium">
                                {{ $p->dibuat_pada ? \Carbon\Carbon::parse($p->dibuat_pada)->translatedFormat('d M Y, H.i') . ' WIB' : '-' }}
                            </td>
                            <td class="px-4 py-4 align-middle whitespace-nowrap">
                                <span class="font-mono text-xs font-bold text-gray-900">{{ $p->id_pesanan }}</span>
                            </td>
                            <td class="px-4 py-4 align-middle">
                                <p class="font-bold text-gray-900 text-sm">{{ $p->detail_pesanan->first()->menu->nama_menu ?? 'Paket Nasi Box' }}</p>
                                <p class="text-xs text-gray-500">{{ $p->detail_pesanan->first()->jumlah ?? 0 }} Box &bull; {{ optional($p->pelanggan)->nama ?? $p->jadwal_pesanan->nama_penerima ?? '-' }}</p>
                            </td>
                            <td class="px-4 py-4 align-middle whitespace-nowrap">
                                @if($p->jadwal_pesanan?->tanggal_acara)
                                    <p class="font-bold text-gray-900 text-sm">{{ \Carbon\Carbon::parse($p->jadwal_pesanan->tanggal_acara)->translatedFormat('d M Y') }}</p>
                                    @if($p->jadwal_pesanan?->waktu_acara)
                                        <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($p->jadwal_pesanan->waktu_acara)->format('H:i') }} WIB</p>
                                    @endif
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-center align-middle whitespace-nowrap">
                                @php
                                    $stId = (int) $p->status_pesanan_id;
                                    $stConfig = match($stId) {
                                        1 => ['label' => 'Menunggu Konfirmasi', 'color' => 'bg-amber-50 text-amber-800 border-amber-200/90', 'dot' => 'bg-amber-500'],
                                        2 => ['label' => 'Dikonfirmasi', 'color' => 'bg-blue-50 text-blue-800 border-blue-200/90', 'dot' => 'bg-blue-500'],
                                        3 => ['label' => 'Sedang Diproses', 'color' => 'bg-indigo-50 text-indigo-800 border-indigo-200/90', 'dot' => 'bg-indigo-500 animate-pulse'],
                                        4 => ['label' => 'Pesanan Siap', 'color' => 'bg-purple-50 text-purple-800 border-purple-200/90', 'dot' => 'bg-purple-500'],
                                        5 => ['label' => 'Selesai', 'color' => 'bg-emerald-50 text-emerald-800 border-emerald-200/90', 'dot' => 'bg-emerald-500'],
                                        6 => ['label' => 'Dibatalkan', 'color' => 'bg-rose-50 text-rose-800 border-rose-200/90', 'dot' => 'bg-rose-500'],
                                        7 => ['label' => 'Terjadwal', 'color' => 'bg-sky-50 text-sky-800 border-sky-200/90', 'dot' => 'bg-sky-500'],
                                        default => ['label' => optional($p->status_pesanan)->nama_status ?? 'Status #'.$stId, 'color' => 'bg-gray-50 text-gray-700 border-gray-200', 'dot' => 'bg-gray-400'],
                                    };
                                @endphp
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full border text-xs font-bold shadow-2xs whitespace-nowrap {{ $stConfig['color'] }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $stConfig['dot'] }}"></span>
                                    <span>{{ $stConfig['label'] }}</span>
                                </span>
                            </td>
                            <td class="px-4 py-4 text-center align-middle whitespace-nowrap">
                                <div class="flex items-center justify-center gap-2">
                                    @if(in_array($p->status_pesanan_id, [2, 7]))
                                        @if(in_array($p->status_pembayaran_id, [3, 4, 5]))
                                            <form id="form-proses-nb-{{ $p->id }}" action="{{ route('admin.pesanan.nasibox.update-status', $p->id) }}" method="POST" class="inline-block">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="3">
                                                <button type="button" 
                                                        onclick="window.confirmDialog({ 
                                                            title: 'Mulai Proses Masak', 
                                                            name: 'Pesanan #{{ $p->id_pesanan }}', 
                                                            message: 'Mulai proses masak pesanan ini? Stok bahan akan dipotong otomatis.', 
                                                            formId: 'form-proses-nb-{{ $p->id }}', 
                                                            confirmText: 'Mulai Masak', 
                                                            cancelText: 'Batal', 
                                                            type: 'warning' 
                                                        })" 
                                                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-amber-600 hover:bg-amber-700 active:scale-95 text-white font-bold text-xs rounded-xl shadow-xs transition-all cursor-pointer">
                                                    <x-heroicon-o-fire class="w-4 h-4 stroke-2" />
                                                    <span>Mulai Proses</span>
                                                </button>
                                            </form>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-yellow-50 text-yellow-800 text-[11px] font-semibold border border-yellow-200">
                                                <x-heroicon-o-lock-closed class="w-3.5 h-3.5 text-yellow-600" />
                                                <span>Menunggu DP</span>
                                            </span>
                                        @endif
                                    @elseif($p->status_pesanan_id == 3)
                                        <form id="form-siap-nb-{{ $p->id }}" action="{{ route('admin.pesanan.nasibox.update-status', $p->id) }}" method="POST" class="inline-block">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="4">
                                            <button type="button" 
                                                    onclick="window.confirmDialog({ 
                                                        title: 'Tandai Pesanan Siap', 
                                                        name: 'Pesanan #{{ $p->id_pesanan }}', 
                                                        message: 'Tandai seluruh hidangan Nasi Box telah selesai disiapkan oleh Dapur?', 
                                                        formId: 'form-siap-nb-{{ $p->id }}', 
                                                        confirmText: 'Pesanan Siap', 
                                                        cancelText: 'Batal', 
                                                        type: 'warning' 
                                                    })" 
                                                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold text-xs rounded-xl shadow-xs transition-all cursor-pointer">
                                                <x-heroicon-o-check class="w-4 h-4 stroke-2" />
                                                <span>Pesanan Siap</span>
                                            </button>
                                        </form>
                                    @endif

                                    <button type="button" 
                                            onclick="openNasiboxDrawer('{{ route('admin.pesanan.nasibox.show', $p->id) }}')" 
                                            class="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-colors cursor-pointer"
                                            title="Lihat Detail Pesanan">
                                        <x-heroicon-o-eye class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </x-ui.table.row>
                        @empty
                        <tr>
                            <td colspan="7">
                                <x-ui.empty-state icon="clipboard-document-list" title="Tidak ada pesanan aktif" message="Belum ada pesanan nasi box yang perlu diproses dapur." />
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </x-ui.table>
            @else
                <x-ui.table class="min-w-[1100px]">
                    <x-ui.table.header>
                        <th class="px-4 py-3.5 text-left w-12">No</th>
                        <th class="px-4 py-3.5 text-left">Tanggal Pesan</th>
                        <th class="px-4 py-3.5 text-left">Kode Pesanan</th>
                        <th class="px-4 py-3.5 text-left">Konsumen</th>
                        <th class="px-4 py-3.5 text-left">Tanggal Acara</th>
                        <th class="px-4 py-3.5 text-right">Total Tagihan</th>
                        <th class="px-4 py-3.5 text-center">Status Pesanan</th>
                        <th class="px-4 py-3.5 text-center">Status Pembayaran</th>
                        <th class="px-4 py-3.5 text-center">Status Pengiriman</th>
                        <th class="px-4 py-3.5 text-center">Aksi</th>
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
                                <p class="font-semibold text-gray-900 font-mono text-xs whitespace-nowrap">{{ $p->id_pesanan }}</p>
                            </td>
                            <td class="px-4 py-4 align-middle">
                                <p class="font-semibold text-gray-900 text-xs whitespace-nowrap">{{ optional($p->pelanggan)->nama ?? $p->jadwal_pesanan->nama_penerima ?? 'Nasi Box' }}</p>
                            </td>
                            <td class="px-4 py-4 align-middle whitespace-nowrap">
                                @if($p->jadwal_pesanan?->tanggal_acara)
                                    <p class="text-xs text-gray-700 font-medium">{{ \Carbon\Carbon::parse($p->jadwal_pesanan->tanggal_acara)->translatedFormat('d M Y') }}</p>
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-right font-semibold text-gray-900 tabular-nums whitespace-nowrap">
                                Rp {{ number_format($p->total_tagihan, 0, ',', '.') }}
                            </td>
                            {{-- 1. STATUS PESANAN --}}
                            <td class="px-4 py-4 align-middle text-center whitespace-nowrap">
                                @php
                                    $stId = (int) $p->status_pesanan_id;
                                    $stConfig = match($stId) {
                                        1 => ['label' => 'Menunggu Konfirmasi', 'color' => 'bg-amber-50 text-amber-800 border-amber-200/90', 'dot' => 'bg-amber-500'],
                                        2 => ['label' => 'Dikonfirmasi', 'color' => 'bg-blue-50 text-blue-800 border-blue-200/90', 'dot' => 'bg-blue-500'],
                                        3 => ['label' => 'Sedang Diproses', 'color' => 'bg-indigo-50 text-indigo-800 border-indigo-200/90', 'dot' => 'bg-indigo-500 animate-pulse'],
                                        4 => ['label' => 'Pesanan Siap', 'color' => 'bg-purple-50 text-purple-800 border-purple-200/90', 'dot' => 'bg-purple-500'],
                                        5 => ['label' => 'Selesai', 'color' => 'bg-emerald-50 text-emerald-800 border-emerald-200/90', 'dot' => 'bg-emerald-500'],
                                        6 => ['label' => 'Dibatalkan', 'color' => 'bg-rose-50 text-rose-800 border-rose-200/90', 'dot' => 'bg-rose-500'],
                                        7 => ['label' => 'Terjadwal', 'color' => 'bg-sky-50 text-sky-800 border-sky-200/90', 'dot' => 'bg-sky-500'],
                                        default => ['label' => optional($p->status_pesanan)->nama_status ?? 'Status #'.$stId, 'color' => 'bg-gray-50 text-gray-700 border-gray-200', 'dot' => 'bg-gray-400'],
                                    };
                                @endphp
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full border text-xs font-bold shadow-2xs {{ $stConfig['color'] }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $stConfig['dot'] }}"></span>
                                    <span>{{ $stConfig['label'] }}</span>
                                </span>
                            </td>
                            {{-- 2. STATUS PEMBAYARAN --}}
                            <td class="px-4 py-4 align-middle text-center whitespace-nowrap">
                                @php
                                    $payId = (int) $p->status_pembayaran_id;
                                    $payConfig = match($payId) {
                                        1 => ['label' => 'Belum Bayar', 'color' => 'bg-rose-50 text-rose-800 border-rose-200/90', 'dot' => 'bg-rose-500'],
                                        2 => ['label' => 'Menunggu Verifikasi', 'color' => 'bg-amber-50 text-amber-800 border-amber-200/90', 'dot' => 'bg-amber-500 animate-pulse'],
                                        3 => ['label' => 'DP Terverifikasi', 'color' => 'bg-blue-50 text-blue-800 border-blue-200/90', 'dot' => 'bg-blue-500'],
                                        4 => ['label' => 'Menunggu Pelunasan', 'color' => 'bg-indigo-50 text-indigo-800 border-indigo-200/90', 'dot' => 'bg-indigo-500'],
                                        5 => ['label' => 'Lunas', 'color' => 'bg-emerald-50 text-emerald-800 border-emerald-200/90', 'dot' => 'bg-emerald-500'],
                                        6 => ['label' => 'Pembayaran Ditolak', 'color' => 'bg-rose-50 text-rose-800 border-rose-200/90', 'dot' => 'bg-rose-500'],
                                        default => ['label' => optional($p->status_pembayaran)->nama_status ?? 'Status #'.$payId, 'color' => 'bg-gray-50 text-gray-700 border-gray-200', 'dot' => 'bg-gray-400'],
                                    };
                                @endphp
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full border text-xs font-bold shadow-2xs {{ $payConfig['color'] }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $payConfig['dot'] }}"></span>
                                    <span>{{ $payConfig['label'] }}</span>
                                </span>
                            </td>
                            {{-- 3. STATUS PENGIRIMAN (HANYA JIKA METODE PENERIMAAN DIANTAR) --}}
                            <td class="px-4 py-4 align-middle text-center whitespace-nowrap">
                                @if($p->pengiriman)
                                    @php
                                        $shipId = (int) ($p->pengiriman->status_pengiriman_id ?? 1);
                                        $shipConfig = match($shipId) {
                                            1 => ['label' => 'Dijadwalkan', 'color' => 'bg-blue-50 text-blue-800 border-blue-200/90', 'dot' => 'bg-blue-500'],
                                            2 => ['label' => 'Siap Dikirim', 'color' => 'bg-purple-50 text-purple-800 border-purple-200/90', 'dot' => 'bg-purple-500'],
                                            3 => ['label' => 'Dalam Pengantaran', 'color' => 'bg-amber-50 text-amber-800 border-amber-200/90', 'dot' => 'bg-amber-500 animate-pulse'],
                                            4 => ['label' => 'Selesai', 'color' => 'bg-emerald-50 text-emerald-800 border-emerald-200/90', 'dot' => 'bg-emerald-500'],
                                            5 => ['label' => 'Dibatalkan', 'color' => 'bg-rose-50 text-rose-800 border-rose-200/90', 'dot' => 'bg-rose-500'],
                                            default => ['label' => optional($p->pengiriman->status_pengiriman)->nama_status ?? 'Status #'.$shipId, 'color' => 'bg-gray-50 text-gray-700 border-gray-200', 'dot' => 'bg-gray-400'],
                                        };
                                    @endphp
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full border text-xs font-bold shadow-2xs {{ $shipConfig['color'] }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $shipConfig['dot'] }}"></span>
                                        <span>{{ $shipConfig['label'] }}</span>
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400 font-medium">Diambil di Resto</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <x-ui.action-button onclick="openNasiBoxDrawer('{{ route('admin.pesanan.nasibox.show', $p->id) }}')" @click="$dispatch('open-nasibox-drawer', {url: '{{ route('admin.pesanan.nasibox.show', $p->id) }}'})" title="Detail">
                                        <x-heroicon-o-eye class="w-4 h-4" />
                                    </x-ui.action-button>
                                    
                                    @php
                                        $buktiPending = $p->pembayaran->firstWhere('status_verifikasi', 'menunggu_verifikasi');
                                    @endphp

                                    @if($buktiPending)
                                        <form id="form-verif-{{ $buktiPending->id }}" action="{{ route('admin.bukti.nasibox.verifikasi-pembayaran', $buktiPending->id) }}" method="POST" class="hidden">
                                            @csrf @method('PATCH')
                                        </form>
                                        <x-ui.action-button type="button" onclick="window.confirmDialog({ title: 'Verifikasi Pembayaran', name: 'Verifikasi bukti pembayaran pesanan ini?', message: 'Pastikan bukti transfer sudah benar sebelum diverifikasi.', formId: 'form-verif-{{ $buktiPending->id }}', confirmText: 'Verifikasi', cancelText: 'Batal' })" title="Verifikasi" class="text-green-600 hover:text-green-800">
                                            <x-heroicon-o-check-badge class="w-4 h-4" />
                                        </x-ui.action-button>
                                    @endif

                                    @if(!in_array($p->status_pesanan_id, [5, 6]))
                                        <form id="form-batal-nb-{{ $p->id }}" action="{{ route('admin.pesanan.nasibox.update-status', $p->id) }}" method="POST" class="hidden">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="6">
                                            <input type="hidden" name="alasan_batal" value="">
                                        </form>
                                        <x-ui.action-button onclick="window.confirmPrompt({ title: 'Batalkan Pesanan', name: 'Batalkan pesanan ini?', message: 'Masukkan alasan pembatalan. Aksi ini tidak dapat dibatalkan.', formId: 'form-batal-nb-{{ $p->id }}', confirmText: 'Batalkan', cancelText: 'Batal', promptPlaceholder: 'Tulis alasan pembatalan' })" title="Batalkan">
                                            <x-heroicon-o-no-symbol class="w-4 h-4" />
                                        </x-ui.action-button>
                                    @endif
                                </div>
                            </td>
                        </x-ui.table.row>
                    @empty
                        <tr>
                            <td colspan="10">
                                <x-ui.empty-state icon="document-text" title="Tidak ada data pesanan nasi box." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>
            @endif
        </x-ui.data-table>

    </div>
</div>{{-- Detail Nasi Box Drawer --}}
<div x-data="nasiboxDrawerApp()"
     @open-nasibox-drawer.window="openDrawer($event.detail.url)"
     @close-nasibox-drawer.window="open = false"
     @keydown.escape.window="open = false">
    
    {{-- Overlay --}}
    <div x-show="open" 
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-900/50 z-[9990] backdrop-blur-xs" 
         @click="open = false" 
         style="display: none;"></div>
    
    {{-- Drawer Panel (Slide-Over Panel) --}}
    <div x-show="open"
         x-cloak
         x-transition:enter="transform transition ease-in-out duration-300"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transform transition ease-in-out duration-300"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full"
         class="fixed inset-y-0 right-0 max-w-4xl w-full bg-white shadow-2xl z-[9999] border-l border-gray-200 flex flex-col"
         style="display: none;">
        
        {{-- Header Drawer --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 bg-white shrink-0 shadow-2xs">
            <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                <x-heroicon-o-clipboard-document-list class="w-5 h-5 text-emerald-600" />
                <span>Detail Pesanan Nasi Box</span>
            </h3>
            <button type="button" @click="open = false" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-gray-700 bg-gray-100 hover:bg-red-50 hover:text-red-700 border border-gray-200 hover:border-red-200 transition-colors cursor-pointer shadow-2xs">
                <x-heroicon-o-x-mark class="w-4 h-4" />
                <span>Tutup</span>
            </button>
        </div>

        {{-- Body --}}
        <div class="flex-1 overflow-y-auto p-6 lg:p-8 bg-gray-50/50 relative">
            <div x-show="loading" class="absolute inset-0 flex flex-col justify-center items-center bg-white/90 backdrop-blur-xs z-20">
                <svg class="animate-spin mb-3 h-8 w-8 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-sm font-medium text-gray-600">Memuat data pesanan...</span>
            </div>
            <div x-show="!loading" x-html="content" class="h-full"></div>
        </div>
    </div>
</div>

<script>
    function nasiboxDrawerApp() {
        return {
            open: false,
            content: '',
            loading: false,
            openDrawer(url) {
                this.open = true;
                this.loading = true;
                this.content = '';
                fetch(url, { 
                    headers: { 
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html'
                    } 
                })
                .then(res => {
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    return res.text();
                })
                .then(html => {
                    this.content = html;
                    this.loading = false;
                    this.$nextTick(() => {
                        const container = this.$el.querySelector('[x-html]');
                        if (container) {
                            container.querySelectorAll('[x-cloak]').forEach(el => el.removeAttribute('x-cloak'));
                            if (window.Alpine) {
                                window.Alpine.initTree(container);
                            }
                        }
                    });
                })
                .catch(err => {
                    console.error('Drawer error:', err);
                    this.loading = false;
                    this.content = '<div class="p-8 text-center text-red-500 font-medium">Gagal memuat detail pesanan nasi box.</div>';
                });
            }
        };
    }

    function openNasiBoxDrawer(url) {
        window.dispatchEvent(new CustomEvent('open-nasibox-drawer', {
            detail: { url: url }
        }));
    }
</script>
@endsection