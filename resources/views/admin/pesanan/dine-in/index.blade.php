@extends('layouts.pos')
@section('title', 'Pesanan Dine In')

@section('content')
@php
    $isDapur = auth()->user()->hasRole('Dapur', 'Tim Dapur') || (auth()->user()->peran?->nama_peran === 'Dapur');
@endphp
<div class="flex-1 bg-gray-50 text-gray-800">
    <div class="w-full p-6 space-y-5">

        {{-- PAGE HEADER --}}
        <x-ui.page-header
            title="Daftar Pesanan Dine-in Aktif"
            subtitle="{{ $isDapur ? 'Kelola dan proses hidangan pesanan Dine In.' : 'Kelola seluruh transaksi pesanan Dine In.' }}"
            :breadcrumbs="['Daftar Pesanan', 'Dine In']">
            @if(auth()->user()->hasRole('Kasir'))
                <x-slot:actions>
                    <x-ui.button variant="primary" icon="plus" href="{{ route('pos.dinein.index') }}">
                        Point Of Sale
                    </x-ui.button>
                </x-slot:actions>
            @endif
        </x-ui.page-header>

        <x-ui.alert />

        {{-- Table with integrated toolbar --}}
        <x-ui.data-table :paginator="$pesanans">
            <x-slot:toolbar>
                <form action="{{ route('admin.pesanan.dinein.index') }}" method="GET" class="flex items-center gap-2 w-full flex-wrap">
                    <x-search-input name="search" value="{{ request('search') }}" placeholder="Cari No. Pesanan / Meja / Pemesan…" />
                    
                    <x-ui.multi-select name="status" :options="['all' => 'Semua Status', '1' => 'Menunggu Konfirmasi', '2' => 'Dikonfirmasi', '3' => 'Sedang Diproses', '4' => 'Pesanan Siap', '8' => 'Pesanan Telah Dihidangkan', '5' => 'Selesai', '6' => 'Dibatalkan']" :selected="request('status', 'all')" label="Status Pesanan" type="radio" />
                    
                    @if(!$isDapur)
                        <x-ui.multi-select name="status_pembayaran" :options="['all' => 'Semua Pembayaran', 'belum_bayar' => 'Belum Bayar', 'lunas' => 'Lunas']" :selected="request('status_pembayaran', 'all')" label="Status Pembayaran" type="radio" />
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
                        <x-ui.button href="{{ route('admin.pesanan.dinein.index') }}" variant="danger" size="sm">Reset</x-ui.button>
                    @endif
                </form>
            </x-slot:toolbar>

            @if($isDapur)
                <x-ui.table class="min-w-[800px]">
                    <x-ui.table.header>
                        <th class="px-4 py-3.5 text-left w-12">No</th>
                        <th class="px-4 py-3.5 text-left">Waktu Pesanan</th>
                        <th class="px-4 py-3.5 text-left">Kode Pesanan</th>
                        <th class="px-4 py-3.5 text-left">Meja</th>
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
                                {{ $p->dibuat_pada ? \Carbon\Carbon::parse($p->dibuat_pada)->format('H.i') . ' WIB' : '-' }}
                            </td>
                            <td class="px-4 py-4 align-middle whitespace-nowrap">
                                <span class="font-mono text-xs font-bold text-gray-900">{{ $p->id_pesanan }}</span>
                            </td>
                            <td class="px-4 py-4 align-middle whitespace-nowrap">
                                @php
                                    $nomorMeja = $p->meja->nomor_meja ?? '-';
                                    $displayMeja = $p->meja ? (str_starts_with(strtolower($nomorMeja), 'meja') ? $nomorMeja : 'Meja ' . $nomorMeja) : '-';
                                @endphp
                                <span class="text-sm font-bold text-gray-900">{{ $displayMeja }}</span>
                            </td>
                            <td class="px-4 py-4 text-center align-middle whitespace-nowrap">
                                @php
                                    $stId = (int) $p->status_pesanan_id;
                                    $stConfig = match($stId) {
                                        1 => ['label' => 'Menunggu Konfirmasi', 'color' => 'bg-amber-50 text-amber-800 border-amber-200/90', 'dot' => 'bg-amber-500'],
                                        2 => ['label' => 'Dikonfirmasi', 'color' => 'bg-blue-50 text-blue-800 border-blue-200/90', 'dot' => 'bg-blue-500'],
                                        3 => ['label' => 'Sedang Diproses', 'color' => 'bg-indigo-50 text-indigo-800 border-indigo-200/90', 'dot' => 'bg-indigo-500 animate-pulse'],
                                        4 => ['label' => 'Pesanan Siap', 'color' => 'bg-purple-50 text-purple-800 border-purple-200/90', 'dot' => 'bg-purple-500'],
                                        8 => ['label' => 'Pesanan Telah Dihidangkan', 'color' => 'bg-teal-50 text-teal-800 border-teal-200/90', 'dot' => 'bg-teal-500'],
                                        5 => ['label' => 'Selesai', 'color' => 'bg-emerald-50 text-emerald-800 border-emerald-200/90', 'dot' => 'bg-emerald-500'],
                                        6 => ['label' => 'Dibatalkan', 'color' => 'bg-rose-50 text-rose-800 border-rose-200/90', 'dot' => 'bg-rose-500'],
                                        default => ['label' => optional($p->status_pesanan)->nama_status ?? 'Status #'.$stId, 'color' => 'bg-gray-50 text-gray-700 border-gray-200', 'dot' => 'bg-gray-400'],
                                    };
                                @endphp
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full border text-xs font-bold shadow-2xs whitespace-nowrap {{ $stConfig['color'] }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $stConfig['dot'] }}"></span>
                                    <span>{{ $stConfig['label'] }}</span>
                                </span>
                            </td>
                            <td class="px-4 py-4 text-center align-middle whitespace-nowrap">
                                @if(in_array($p->status_pesanan_id, [1, 2, 3]))
                                    <div class="flex items-center justify-center gap-2">
                                        <form action="{{ route('admin.pesanan.dinein.update-status', $p->id) }}" method="POST" class="inline-block">
                                             @csrf
                                             @method('PATCH')
                                             <input type="hidden" name="status_pesanan_id" value="4">
                                             <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold text-xs rounded-xl shadow-xs transition-all cursor-pointer">
                                                 <x-heroicon-o-check class="w-4 h-4 stroke-2" />
                                                 <span>Pesanan Siap</span>
                                             </button>
                                         </form>
                                        <button type="button" 
                                                onclick="openDineinDrawer('{{ route('admin.pesanan.show', $p->id) }}')" 
                                                class="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-colors"
                                                title="Lihat Detail Pesanan">
                                            <x-heroicon-o-eye class="w-4 h-4" />
                                        </button>
                                    </div>
                                @else
                                    <button type="button" 
                                            onclick="openDineinDrawer('{{ route('admin.pesanan.show', $p->id) }}')" 
                                            class="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-colors"
                                            title="Lihat Detail Pesanan">
                                        <x-heroicon-o-eye class="w-4 h-4" />
                                    </button>
                                @endif
                            </td>
                        </x-ui.table.row>
                        @empty
                        <tr>
                            <td colspan="6">
                                <x-ui.empty-state icon="clipboard-document-list" title="Tidak ada pesanan aktif" message="Belum ada pesanan Dine In yang perlu diproses dapur." />
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </x-ui.table>
            @else
                <x-ui.table class="min-w-[900px]">
                    <x-ui.table.header>
                        <th class="px-4 py-3.5 text-left w-12">No</th>
                        <th class="px-4 py-3.5 text-left">Waktu Pesanan</th>
                        <th class="px-4 py-3.5 text-left">Kode Pesanan</th>
                        <th class="px-4 py-3.5 text-left">Pelanggan</th>

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
                                    $stId = (int) $p->status_pesanan_id;
                                    $stConfig = match($stId) {
                                        1 => ['label' => 'Menunggu Konfirmasi', 'color' => 'bg-amber-50 text-amber-800 border-amber-200/90', 'dot' => 'bg-amber-500'],
                                        2 => ['label' => 'Dikonfirmasi', 'color' => 'bg-blue-50 text-blue-800 border-blue-200/90', 'dot' => 'bg-blue-500'],
                                        3 => ['label' => 'Sedang Diproses', 'color' => 'bg-indigo-50 text-indigo-800 border-indigo-200/90', 'dot' => 'bg-indigo-500 animate-pulse'],
                                        4 => ['label' => 'Pesanan Siap', 'color' => 'bg-purple-50 text-purple-800 border-purple-200/90', 'dot' => 'bg-purple-500'],
                                        8 => ['label' => 'Pesanan Telah Dihidangkan', 'color' => 'bg-teal-50 text-teal-800 border-teal-200/90', 'dot' => 'bg-teal-500'],
                                        5 => ['label' => 'Selesai', 'color' => 'bg-emerald-50 text-emerald-800 border-emerald-200/90', 'dot' => 'bg-emerald-500'],
                                        6 => ['label' => 'Dibatalkan', 'color' => 'bg-rose-50 text-rose-800 border-rose-200/90', 'dot' => 'bg-rose-500'],
                                        default => ['label' => optional($p->status_pesanan)->nama_status ?? 'Status #'.$stId, 'color' => 'bg-gray-50 text-gray-700 border-gray-200', 'dot' => 'bg-gray-400'],
                                    };
                                @endphp
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full border text-xs font-bold shadow-2xs whitespace-nowrap {{ $stConfig['color'] }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $stConfig['dot'] }}"></span>
                                    <span>{{ $stConfig['label'] }}</span>
                                </span>
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
                                    @if((int)$p->status_pesanan_id === 4)
                                        <form action="{{ route('admin.pesanan.dinein.update-status', $p->id) }}" method="POST" class="inline-block">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status_pesanan_id" value="8">
                                            <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs rounded-xl shadow-xs transition-all cursor-pointer" title="Tandai Telah Dihidangkan">
                                                <x-heroicon-o-check-badge class="w-3.5 h-3.5" />
                                                <span>Hidangkan</span>
                                            </button>
                                        </form>
                                    @endif
                                    <x-ui.action-button onclick="openDineinDrawer('{{ route('admin.pesanan.show', $p->id) }}')" @click="$dispatch('open-dinein-drawer', {url: '{{ route('admin.pesanan.show', $p->id) }}'})" title="Detail">
                                        <x-heroicon-o-eye class="w-4 h-4" />
                                    </x-ui.action-button>
                                    <x-ui.action-button href="{{ route('pos.dinein.print-gabungan', $p->id) }}" target="_blank" title="Cetak Struk">
                                        <x-heroicon-o-printer class="w-4 h-4" />
                                    </x-ui.action-button>
                                </div>
                            </td>
                        </x-ui.table.row>
                        @empty
                        <tr>
                            <td colspan="8">
                                <x-ui.empty-state icon="clipboard-document-list" title="Tidak ada pesanan Dine In" message="Tidak ada data pesanan Dine In." />
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </x-ui.table>
            @endif
        </x-ui.data-table>

    </div>
</div>

{{-- Detail Dine-In Drawer --}}
<div x-data="dineinDrawerApp()"
     @open-dinein-drawer.window="openDrawer($event.detail.url)"
     @close-dinein-drawer.window="open = false"
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
         class="fixed inset-y-0 right-0 max-w-2xl w-full bg-white shadow-2xl z-[9999] border-l border-gray-200 flex flex-col"
         style="display: none;">
        
        {{-- Loading Spinner --}}
        <div x-show="loading" class="flex-1 flex flex-col justify-center items-center bg-white/90 backdrop-blur-xs z-20 p-8">
            <svg class="animate-spin mb-3 h-8 w-8 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-sm font-medium text-gray-600">Memuat rincian pesanan...</span>
        </div>

        {{-- Content Rendered by show_partial --}}
        <div x-show="!loading" x-html="content" class="h-full flex flex-col overflow-hidden"></div>
    </div>
</div>

<script>
    function dineinDrawerApp() {
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
                    this.content = '<div class="p-8 text-center text-red-500 font-medium">Gagal memuat detail pesanan dine-in.</div>';
                });
            }
        };
    }

    function openDineinDrawer(url) {
        window.dispatchEvent(new CustomEvent('open-dinein-drawer', {
            detail: { url: url }
        }));
    }
</script>
@endsection