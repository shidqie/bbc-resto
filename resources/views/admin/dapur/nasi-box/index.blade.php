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
                <form action="{{ route('admin.pesanan.nasibox.index') }}" method="GET" class="flex items-center gap-2 w-full flex-wrap">
                    <x-search-input name="search" value="{{ request('search') }}" placeholder="Cari Kode / Pemesan / HP…" />
                    
                    <x-ui.multi-select name="status" :options="['all' => 'Semua Status', 'ditinjau' => 'Baru', 'terkonfirmasi' => 'Terkonfirmasi', 'diproses' => 'Diproses', 'selesai' => 'Selesai', 'dibatalkan' => 'Dibatalkan']" :selected="request('status', 'all')" label="Status Pesanan" type="radio" />
                    
                    <x-ui.multi-select name="status_pembayaran" :options="['all' => 'Semua Pembayaran', 'menunggu_dp' => 'Menunggu Pembayaran DP', 'verifikasi_dp' => 'Menunggu Verifikasi DP', 'menunggu_pelunasan' => 'Menunggu Pelunasan', 'verifikasi_lunas' => 'Menunggu Verifikasi Pelunasan', 'lunas' => 'Lunas']" :selected="request('status_pembayaran', 'all')" label="Status Pembayaran" type="radio" />
                    
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
                                @php $totalP = (float) $p->total_tagihan; @endphp
                            </td>
                            <td class="px-4 py-4 align-middle text-center">
                                @if(in_array($p->status_pesanan_id, [5, 6]))
                                    @php
                                        $sColorMap = [5 => 'success', 6 => 'danger'];
                                        $sColor = $sColorMap[$p->status_pesanan_id] ?? 'gray';
                                    @endphp
                                    <x-ui.badge :color="$sColor" size="sm">{{ $p->status_pesanan->nama_status ?? '-' }}</x-ui.badge>
                                @else
                                    <form action="{{ route('admin.pesanan.nasibox.update-status', $p->id) }}" method="POST" class="inline-block">
                                        @csrf @method('PATCH')
                                        @php
                                            $allowed = [
                                                1 => [1, 2],
                                                2 => [2, 3],
                                                3 => [3, 4],
                                                4 => [4, 5],
                                            ];
                                            $validNext = $allowed[$p->status_pesanan_id] ?? [$p->status_pesanan_id];
                                        @endphp
                                        <x-ui.table-status-select 
                                            :id="'dapur-nb-' . $p->id" 
                                            name="status" 
                                            :current="$p->status_pesanan_id" 
                                            :allowed="$validNext" />
                                    </form>
                                @endif
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
                                    <x-ui.action-button @click="$dispatch('open-nasibox-drawer', {url: '{{ route('admin.pesanan.nasibox.show', $p->id) }}'})" title="Detail">
                                        <x-heroicon-o-eye class="w-4 h-4" />
                                    </x-ui.action-button>
                                    
                                    @php
                                        $buktiPending = $p->pembayaran->firstWhere('status_verifikasi', 'menunggu_verifikasi');
                                        $buktiTerakhir = $p->pembayaran->whereNotNull('bukti_pembayaran')->last();
                                    @endphp
                                    

    
                                    @if($buktiPending)
                                        <form id="form-verif-{{ $buktiPending->id }}" action="{{ route('admin.bukti.verifikasi-pembayaran', $buktiPending->id) }}" method="POST" class="hidden">
                                            @csrf @method('PATCH')
                                        </form>
                                        <x-ui.action-button type="button" onclick="window.confirmDialog({ title: 'Verifikasi Pembayaran', name: 'Verifikasi bukti pembayaran pesanan ini?', message: 'Pastikan bukti transfer sudah benar sebelum diverifikasi.', formId: 'form-verif-{{ $buktiPending->id }}', confirmText: 'Verifikasi', cancelText: 'Batal' })" title="Verifikasi" class="text-green-600 hover:text-green-800">
                                            <x-heroicon-o-check-badge class="w-4 h-4" />
                                        </x-ui.action-button>
                                    @endif


                                </div>
                            </td>
                        </x-ui.table.row>
                    @empty
                        <tr>
                            <td colspan="9">
                                <x-ui.empty-state icon="document-text" title="Tidak ada data pesanan nasi box." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>
        </x-ui.data-table>

    </div>
</div>{{-- Detail Nasi Box Drawer --}}
<div x-data="dapurNasiboxDrawerApp()"
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
                <span>Detail Produksi Nasi Box</span>
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
    function dapurNasiboxDrawerApp() {
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
                        if (container && window.Alpine) {
                            window.Alpine.initTree(container);
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
</script>
@endsection