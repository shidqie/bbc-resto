@extends('layouts.pos')

@section('title', 'Semua Pesanan')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800">
    <div class="w-full p-6 space-y-5">

        {{-- PAGE HEADER --}}
        <x-ui.page-header
            title="Semua Pesanan"
            subtitle="Daftar seluruh pesanan Dine In, Katering, dan Nasi Box."
            :breadcrumbs="['Penjualan', 'Semua Pesanan']">
        </x-ui.page-header>

        <x-ui.alert />

        {{-- Table with integrated toolbar --}}
        <x-ui.data-table :paginator="$pesanans">
            <x-slot:toolbar>
                <form action="{{ route('admin.pesanan.index') }}" method="GET" class="flex items-center gap-2 w-full flex-wrap">
                    <x-search-input name="search" value="{{ request('search') }}" placeholder="Cari No. Pesanan / Nama Pemesan…" />
                    <x-ui.multi-select name="jenis" :options="['all' => 'Semua Jenis'] + $jenis_pesanan->pluck('nama_jenis', 'id')->toArray()" :selected="request('jenis', 'all')" label="Jenis" type="radio" />
                    
                    <x-ui.multi-select name="status" :options="['all' => 'Semua Status'] + $status_pesanan->pluck('nama_status', 'id')->toArray()" :selected="request('status', 'all')" label="Status Pesanan" type="radio" />
                    
                    <x-ui.multi-select name="status_pembayaran" :options="['all' => 'Semua Pembayaran'] + $status_pembayaran->pluck('nama_status', 'id')->toArray()" :selected="request('status_pembayaran', 'all')" label="Status Pembayaran" type="radio" />

                    <x-ui.multi-select name="periode" :options="['hari_ini' => 'Hari Ini', 'minggu_ini' => 'Minggu Ini', 'bulan_ini' => 'Bulan Ini', 'kustom' => 'Kustom']" :selected="request('periode')" label="Periode Pesanan" type="radio" />
                    
                    <template x-if="new URLSearchParams(window.location.search).get('periode') === 'kustom'">
                        <div class="flex items-center gap-2">
                            <x-ui.input type="date" name="start_date" value="{{ request('start_date') }}" />
                            <span class="text-gray-500 text-sm">s/d</span>
                            <x-ui.input type="date" name="end_date" value="{{ request('end_date') }}" />
                            <x-ui.button type="submit" variant="primary">Terapkan</x-ui.button>
                        </div>
                    </template>

                    @if(request()->hasAny(['search', 'jenis', 'status', 'status_pembayaran', 'periode', 'start_date', 'end_date']))
                        <x-ui.button href="{{ route('admin.pesanan.index') }}" variant="danger" size="sm">Reset</x-ui.button>
                    @endif
                </form>
            </x-slot:toolbar>

            <x-ui.table class="min-w-[1100px]">
                <x-ui.table.header>
                    <th class="px-4 py-3.5 text-left w-12">No</th>
                    <th class="px-4 py-3.5 text-left">Tanggal Pesan</th>
                    <th class="px-4 py-3.5 text-left">ID Pesanan</th>
                    <th class="px-4 py-3.5 text-left">Jenis Pesanan</th>
                    <th class="px-4 py-3.5 text-left">Konsumen</th>

                    <th class="px-4 py-3.5 text-right">Total Tagihan</th>
                    <th class="px-4 py-3.5 text-center">Status Pesanan</th>
                    <th class="px-4 py-3.5 text-center">Status Pembayaran</th>
                    <th class="px-4 py-3.5 text-center">Status Pengiriman</th>
                    <th class="px-4 py-3.5 text-center">Aksi</th>
                </x-ui.table.header>
                <tbody class="divide-y divide-gray-100">
                    @forelse($pesanans as $index => $pesanan)
                    <x-ui.table.row>
                        <td class="px-4 py-4 text-sm text-gray-500 font-medium">
                            {{ ($pesanans->firstItem() ?? 1) + $index }}
                        </td>
                        <td class="px-4 py-4 text-sm text-gray-700 whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($pesanan->dibuat_pada)->translatedFormat('d M Y, H.i') }} WIB
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-xs font-bold text-gray-900">{{ $pesanan->id_pesanan ?? 'DIN-'.$pesanan->id }}</span>
                                @if(\Carbon\Carbon::parse($pesanan->dibuat_pada)->diffInMinutes(now()) < 15)
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-black bg-red-500 text-white animate-pulse">BARU</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-4 text-sm text-gray-600 whitespace-nowrap">
                            {{ optional($pesanan->jenis_pesanan)->nama_jenis ?? 'Dine In' }}
                        </td>
                        <td class="px-4 py-4">
                            @php
                                $nama = 'Tamu';
                                if ($pesanan->pelanggan) {
                                    $nama = $pesanan->pelanggan->nama;
                                } elseif (!empty($pesanan->catatan)) {
                                    if (preg_match('/^Pemesan:\s*(.+)$/m', $pesanan->catatan, $m)) {
                                        $nama = trim($m[1]);
                                    } elseif (preg_match('/Self-Order QR \(([^)]+)\)/', $pesanan->catatan, $m)) {
                                        $nama = trim($m[1]);
                                    } elseif (preg_match('/^(.+?)\s*\(\d+\s*tamu\)/', $pesanan->catatan, $m)) {
                                        $nama = trim($m[1]);
                                    } else {
                                        $nama = trim(explode('|', $pesanan->catatan)[0]);
                                    }
                                }
                                $nama = trim(explode('–', $nama)[0]);
                                $nama = trim(explode('-', $nama)[0]);
                            @endphp
                            <p class="font-medium text-gray-900 text-sm">{{ $nama }}</p>
                        </td>

                        <td class="px-4 py-4 text-right font-bold text-gray-900 whitespace-nowrap">
                            Rp{{ number_format($pesanan->total_tagihan, 0, ',', '.') }}
                        </td>
                        {{-- 1. STATUS PESANAN --}}
                        <td class="px-4 py-4 text-center align-middle whitespace-nowrap">
                            @php
                                $stId = (int) $pesanan->status_pesanan_id;
                                $stConfig = match($stId) {
                                    1 => ['label' => 'Menunggu Konfirmasi', 'color' => 'bg-amber-50 text-amber-800 border-amber-200/90', 'dot' => 'bg-amber-500'],
                                    2 => ['label' => 'Dikonfirmasi', 'color' => 'bg-blue-50 text-blue-800 border-blue-200/90', 'dot' => 'bg-blue-500'],
                                    3 => ['label' => 'Sedang Diproses', 'color' => 'bg-indigo-50 text-indigo-800 border-indigo-200/90', 'dot' => 'bg-indigo-500 animate-pulse'],
                                    4 => ['label' => 'Pesanan Siap', 'color' => 'bg-purple-50 text-purple-800 border-purple-200/90', 'dot' => 'bg-purple-500'],
                                    8 => ['label' => 'Pesanan Telah Dihidangkan', 'color' => 'bg-teal-50 text-teal-800 border-teal-200/90', 'dot' => 'bg-teal-500'],
                                    5 => ['label' => 'Selesai', 'color' => 'bg-emerald-50 text-emerald-800 border-emerald-200/90', 'dot' => 'bg-emerald-500'],
                                    6 => ['label' => 'Dibatalkan', 'color' => 'bg-rose-50 text-rose-800 border-rose-200/90', 'dot' => 'bg-rose-500'],
                                    7 => ['label' => 'Terjadwal', 'color' => 'bg-sky-50 text-sky-800 border-sky-200/90', 'dot' => 'bg-sky-500'],
                                    default => ['label' => optional($pesanan->status_pesanan)->nama_status ?? 'Status #'.$stId, 'color' => 'bg-gray-50 text-gray-700 border-gray-200', 'dot' => 'bg-gray-400'],
                                };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full border text-xs font-semibold {{ $stConfig['color'] }}">
                                <span>{{ $stConfig['label'] }}</span>
                            </span>
                        </td>
                        {{-- 2. STATUS PEMBAYARAN --}}
                        <td class="px-4 py-4 align-middle text-center whitespace-nowrap">
                            @php
                                $payId = (int) $pesanan->status_pembayaran_id;
                                $payConfig = match($payId) {
                                    1 => ['label' => 'Belum Bayar', 'color' => 'bg-rose-50 text-rose-800 border-rose-200/90'],
                                    2 => ['label' => 'Menunggu Verifikasi', 'color' => 'bg-amber-50 text-amber-800 border-amber-200/90'],
                                    3 => ['label' => 'DP Terverifikasi', 'color' => 'bg-blue-50 text-blue-800 border-blue-200/90'],
                                    4 => ['label' => 'Menunggu Pelunasan', 'color' => 'bg-indigo-50 text-indigo-800 border-indigo-200/90'],
                                    5 => ['label' => 'Lunas', 'color' => 'bg-emerald-50 text-emerald-800 border-emerald-200/90'],
                                    6 => ['label' => 'Pembayaran Ditolak', 'color' => 'bg-rose-50 text-rose-800 border-rose-200/90'],
                                    default => ['label' => optional($pesanan->status_pembayaran)->nama_status ?? 'Status #'.$payId, 'color' => 'bg-gray-50 text-gray-700 border-gray-200'],
                                };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full border text-xs font-semibold {{ $payConfig['color'] }}">
                                <span>{{ $payConfig['label'] }}</span>
                            </span>
                        </td>
                        {{-- 3. STATUS PENGIRIMAN (HANYA JIKA DIANTAR) --}}
                        <td class="px-4 py-4 align-middle text-center whitespace-nowrap">
                            @if($pesanan->pengiriman)
                                @php
                                    $shipId = (int) ($pesanan->pengiriman->status_pengiriman_id ?? 1);
                                    $shipConfig = match($shipId) {
                                        1 => ['label' => 'Dijadwalkan', 'color' => 'bg-blue-50 text-blue-800 border-blue-200/90'],
                                        2 => ['label' => 'Siap Dikirim', 'color' => 'bg-purple-50 text-purple-800 border-purple-200/90'],
                                        3 => ['label' => 'Dalam Pengantaran', 'color' => 'bg-amber-50 text-amber-800 border-amber-200/90'],
                                        4 => ['label' => 'Selesai', 'color' => 'bg-emerald-50 text-emerald-800 border-emerald-200/90'],
                                        5 => ['label' => 'Dibatalkan', 'color' => 'bg-rose-50 text-rose-800 border-rose-200/90'],
                                        default => ['label' => optional($pesanan->pengiriman->status_pengiriman)->nama_status ?? 'Status #'.$shipId, 'color' => 'bg-gray-50 text-gray-700 border-gray-200'],
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full border text-xs font-semibold {{ $shipConfig['color'] }}">
                                    <span>{{ $shipConfig['label'] }}</span>
                                </span>
                            @else
                                <span class="text-xs text-gray-400 font-medium">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <x-ui.action-button onclick="openDetailDrawer({{ $pesanan->id }})" title="Detail" label="Detail">
                                    <x-heroicon-o-eye class="w-3.5 h-3.5" />
                                </x-ui.action-button>
                                @php
                                    $printUrl = match($pesanan->jenis_pesanan_id) {
                                        2 => route('admin.pesanan.catering.pdf', $pesanan->id),
                                        3 => route('admin.pesanan.nasibox.pdf', $pesanan->id),
                                        default => url('/pos/dinein/pesanan/' . $pesanan->id . '/print-nota')
                                    };
                                    $printLabel = in_array($pesanan->jenis_pesanan_id, [2, 3]) ? 'Bukti' : 'Struk';
                                @endphp
                                <x-ui.action-button onclick="window.open('{{ $printUrl }}', '_blank')" title="{{ in_array($pesanan->jenis_pesanan_id, [2, 3]) ? 'Cetak Bukti Pemesanan' : 'Cetak Struk' }}" label="{{ $printLabel }}">
                                    <x-heroicon-o-printer class="w-3.5 h-3.5" />
                                </x-ui.action-button>
                            </div>
                        </td>
                    </x-ui.table.row>
                    @empty
                    <tr>
                        <td colspan="10">
                            <x-ui.empty-state icon="document-text" title="Tidak ada data pesanan." message="Belum ada pesanan yang sesuai kriteria pencarian." />
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </x-ui.table>
        </x-ui.data-table>

    </div>
</div>

{{-- Detail Pesanan Drawer --}}
<div x-data="pesananDrawerApp()"
     @open-detail-drawer.window="openDrawer($event.detail.url)"
     @close-detail-drawer.window="open = false"
     @keydown.escape.window="open = false"
     id="drawerDetailApp">
    
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
        
        {{-- Body --}}
        <div class="flex-1 overflow-y-auto bg-white relative">
            <div x-show="loading" class="absolute inset-0 flex flex-col justify-center items-center bg-white/90 backdrop-blur-xs z-20">
                <svg class="animate-spin mb-3 h-8 w-8 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-sm font-medium text-gray-500">Memuat detail pesanan...</span>
            </div>
            <div x-show="!loading" x-html="content" class="h-full"></div>
        </div>
    </div>
</div>

<script>
    function pesananDrawerApp() {
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
                    this.content = '<div class="p-8 text-center text-red-500 font-medium">Gagal memuat detail pesanan.</div>';
                });
            }
        };
    }

    function openDetailDrawer(id) {
        window.dispatchEvent(new CustomEvent('open-detail-drawer', {
            detail: { url: `/admin/pesanan/detail/${id}` }
        }));
    }

    function closeDetailDrawer() {
        window.dispatchEvent(new CustomEvent('close-detail-drawer'));
    }
</script>
@endsection
