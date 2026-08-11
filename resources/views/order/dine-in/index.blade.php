@extends('layouts.pos')
@section('title', 'Pesanan Dine In')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800">
    <div class="w-full p-6 space-y-5">

        {{-- PAGE HEADER --}}
        <x-ui.page-header
            title="Daftar Pesanan Dine In"
            subtitle="Kelola seluruh transaksi pesanan Dine In."
            :breadcrumbs="['Daftar Pesanan', 'Dine In']">
            @if(auth()->user()->hasRole('Kasir'))
                <x-slot:actions>
                    <a href="{{ route('pos.dinein.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-gray-900 text-white text-sm font-medium rounded-lg hover:bg-gray-800 transition-colors shadow-sm">
                        <x-heroicon-m-plus class="w-4 h-4" />
                        Point Of Sale
                    </a>
                </x-slot:actions>
            @endif
        </x-ui.page-header>

        <x-ui.alert />

        {{-- Table with integrated toolbar --}}
        <x-ui.data-table :paginator="$pesanans">
            <x-slot:toolbar>
                <div class="flex flex-col gap-2 w-full">
                    {{-- Row 1: Search + Status Filter --}}
                    <div class="flex flex-col sm:flex-row gap-2 items-start sm:items-center justify-between w-full">
                        <form action="{{ route('admin.pesanan.dinein.index') }}" method="GET" class="flex items-center gap-2 flex-wrap">
                            <x-search-input name="search" value="{{ request('search') }}" placeholder="Cari Kode / Pemesan / HP…" />
                            <input type="hidden" name="status" value="{{ request('status', 'all') }}">
                            <input type="hidden" name="period" value="{{ request('period', 'all') }}">
                        </form>
                        <div class="flex items-center gap-1 text-xs font-medium overflow-x-auto no-scrollbar shrink-0">
                            <span class="text-gray-500 mr-1">Status:</span>
                            <a href="{{ route('admin.pesanan.dinein.index', ['status' => 'all', 'period' => request('period', 'all')]) }}" class="px-3 py-1.5 rounded-lg transition-colors {{ request('status', 'all') === 'all' ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100' }}">Semua</a>
                            <a href="{{ route('admin.pesanan.dinein.index', ['status' => 'ditinjau', 'period' => request('period', 'all')]) }}" class="px-3 py-1.5 rounded-lg transition-colors {{ request('status') === 'ditinjau' ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100' }}">Baru</a>
                            <a href="{{ route('admin.pesanan.dinein.index', ['status' => 'terkonfirmasi', 'period' => request('period', 'all')]) }}" class="px-3 py-1.5 rounded-lg transition-colors {{ request('status') === 'terkonfirmasi' ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100' }}">Terkonfirmasi</a>
                            <a href="{{ route('admin.pesanan.dinein.index', ['status' => 'diproses', 'period' => request('period', 'all')]) }}" class="px-3 py-1.5 rounded-lg transition-colors {{ request('status') === 'diproses' ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100' }}">Diproses</a>
                            <a href="{{ route('admin.pesanan.dinein.index', ['status' => 'selesai', 'period' => request('period', 'all')]) }}" class="px-3 py-1.5 rounded-lg transition-colors {{ request('status') === 'selesai' ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100' }}">Selesai</a>
                        </div>
                    </div>

                    {{-- Row 2: Period Filter --}}
                    <div class="flex items-center gap-1 text-xs font-medium overflow-x-auto no-scrollbar">
                        <span class="text-gray-500 mr-1">Periode:</span>
                        <a href="{{ route('admin.pesanan.dinein.index', ['status' => request('status', 'all'), 'period' => 'all']) }}"
                           class="px-3 py-1.5 rounded-lg transition-colors {{ ($period ?? 'all') === 'all' ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                            Semua
                        </a>
                        <a href="{{ route('admin.pesanan.dinein.index', ['status' => request('status', 'all'), 'period' => 'today']) }}"
                           class="px-3 py-1.5 rounded-lg transition-colors {{ ($period ?? 'all') === 'today' ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                            Hari Ini
                        </a>
                        <a href="{{ route('admin.pesanan.dinein.index', ['status' => request('status', 'all'), 'period' => 'this_week']) }}"
                           class="px-3 py-1.5 rounded-lg transition-colors {{ ($period ?? 'all') === 'this_week' ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                            Minggu Ini
                        </a>
                        <a href="{{ route('admin.pesanan.dinein.index', ['status' => request('status', 'all'), 'period' => 'this_month']) }}"
                           class="px-3 py-1.5 rounded-lg transition-colors {{ ($period ?? 'all') === 'this_month' ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                            Bulan Ini
                        </a>
                    </div>
                </div>
            </x-slot:toolbar>


            <x-ui.table class="min-w-[1100px]">
                <x-ui.table.header>
                    <th class="px-4 py-3.5 text-left w-12">No</th>
                    <th class="px-4 py-3.5 text-left">Tanggal Pesan</th>
                    <th class="px-4 py-3.5 text-left">Kode Pesanan</th>
                    <th class="px-4 py-3.5 text-left">Konsumen</th>

                    <th class="px-4 py-3.5 text-right">Total Tagihan</th>
                    <th class="px-4 py-3.5 text-center">Status Pesanan</th>
                    <th class="px-4 py-3.5 text-center">Status Pembayaran</th>
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
                            <span class="font-mono text-xs font-bold text-gray-900">{{ $p->id_pesanan }}</span>
                        </td>
                        <td class="px-4 py-4 align-middle">
                            <p class="font-medium text-gray-900 text-sm">{{ optional($p->pelanggan)->nama ?? '-' }}</p>
                        </td>

                        <td class="px-4 py-4 text-right font-bold text-gray-900 tabular-nums whitespace-nowrap">
                            Rp {{ number_format($p->total_tagihan, 0, ',', '.') }}
                            @php $totalP = (float) $p->total_tagihan; @endphp
                        </td>
                        <td class="px-4 py-4 text-center align-middle">
                            @php
                                $sColorMap = [1 => 'warning', 2 => 'info', 3 => 'primary', 4 => 'primary', 5 => 'success', 6 => 'danger'];
                                $sColor = $sColorMap[$p->status_pesanan_id] ?? 'gray';
                            @endphp
                            <x-ui.badge :color="$sColor" size="sm">{{ $p->status_pesanan->nama_status ?? '-' }}</x-ui.badge>
                        </td>
                        <td class="px-4 py-4 align-middle text-center">
                            @php
                                $totalDiterimaPP = (float) $p->pembayaran->where('status_verifikasi', 'diterima')->sum('jumlah_dibayar');
                                $totalMenungguPP = (float) $p->pembayaran->where('status_verifikasi', 'menunggu_verifikasi')->sum('jumlah_dibayar');
                                
                                $bayarLabel = 'Belum Bayar';
                                $bayarColor = 'danger';

                                if ($totalDiterimaPP >= $totalP) {
                                    $bayarLabel = 'Lunas';
                                    $bayarColor = 'success';
                                } else {
                                    $hasPelunasanMenunggu = $p->pembayaran->where('jenis_pembayaran', 'pelunasan')->where('status_verifikasi', 'menunggu_verifikasi')->isNotEmpty();
                                    $hasDpMenunggu = $p->pembayaran->where('jenis_pembayaran', 'uang_muka')->where('status_verifikasi', 'menunggu_verifikasi')->isNotEmpty();
                                    
                                    if ($hasPelunasanMenunggu || ($totalMenungguPP >= $totalP)) {
                                        $bayarLabel = 'Menunggu Verifikasi Pelunasan';
                                        $bayarColor = 'warning';
                                    } elseif ($totalDiterimaPP > 0) {
                                        $bayarLabel = 'Menunggu Pelunasan';
                                        $bayarColor = 'primary';
                                    } elseif ($hasDpMenunggu || $totalMenungguPP > 0) {
                                        $bayarLabel = 'Menunggu Verifikasi DP';
                                        $bayarColor = 'warning';
                                    }
                                }
                            @endphp
                            <x-ui.badge :color="$bayarColor" size="sm" class="whitespace-nowrap">{{ $bayarLabel }}</x-ui.badge>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <button type="button" @click="$dispatch('open-catering-drawer', {url: '{{ route('admin.pesanan.show', $p->id) }}'})" title="Detail" class="text-gray-500 transition hover:text-gray-900">
                                    <x-heroicon-o-eye class="w-4 h-4" />
                                </button>
                                <a href="{{ route('pos.dinein.print-gabungan', $p->id) }}" target="_blank" title="Cetak Struk" class="text-gray-500 transition hover:text-gray-900">
                                    <x-heroicon-o-printer class="w-4 h-4" />
                                </a>
                            </div>
                        </td>
                    </x-ui.table.row>
                    @empty
                    <x-empty-state icon="clipboard-document-list" title="Tidak ada pesanan Dine In" message="Tidak ada data pesanan Dine In." :colspan="10" />
                    @endforelse
                </tbody>
            </x-ui.table>
        </x-ui.data-table>

    </div>
</div>

{{-- Detail Katering Drawer --}}
<div x-data="{
    open: false,
    content: '',
    loading: false,
    openDrawer(url) {
        this.open = true;
        this.loading = true;
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => res.text())
            .then(html => {
                this.content = html;
                this.loading = false;
            });
    }
}" @open-catering-drawer.window="openDrawer($event.detail.url)">
    
    {{-- Overlay --}}
    <div x-show="open" 
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-900/50 z-40 backdrop-blur-sm" 
         @click="open = false" 
         style="display: none;"></div>
    
    {{-- Drawer Panel --}}
    <div class="fixed top-0 right-0 h-full w-full sm:w-[600px] md:w-[700px] lg:w-[800px] bg-white shadow-2xl z-50 transform transition-transform duration-300 ease-in-out border-l border-gray-200"
         :class="open ? 'translate-x-0' : 'translate-x-full'" 
         style="display:flex; flex-direction:column;">
        
        {{-- Header --}}
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-white shadow-sm z-10">
            <h3 class="text-lg font-bold text-gray-900">Rincian Detail</h3>
            <button @click="open = false" class="text-gray-500 hover:text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-full p-2 transition-colors">
                <x-heroicon-o-x-mark class="w-5 h-5" />
            </button>
        </div>
        
        {{-- Body --}}
        <div class="flex-1 overflow-y-auto p-6 bg-white relative">
            <div x-show="loading" class="absolute inset-0 flex flex-col justify-center items-center bg-white/80 backdrop-blur-sm z-20">
                <svg class="animate-spin mb-3 h-8 w-8 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-sm font-medium text-gray-500">Memuat data pesanan...</span>
            </div>
            <div x-show="!loading" x-html="content" class="h-full"></div>
        </div>
    </div>
</div>
@endsection