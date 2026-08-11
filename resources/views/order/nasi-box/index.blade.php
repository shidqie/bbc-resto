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
                                    <p class="text-xs text-gray-700 font-medium">{{ \Carbon\Carbon::parse($p->jadwal_pesanan->tanggal_acara)->format('d M Y') }}</p>
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
                                        <select name="status" onchange="this.form.submit()" class="text-xs font-semibold rounded-lg border-gray-300 py-1.5 pl-3 pr-8 focus:ring-[#0D3024] focus:border-[#0D3024] bg-gray-50 hover:bg-white transition-colors cursor-pointer">
                                            @if(in_array(1, $validNext)) <option value="1" {{ $p->status_pesanan_id == 1 ? 'selected' : '' }}>Menunggu Konfirmasi</option> @endif
                                            @if(in_array(2, $validNext)) <option value="2" {{ $p->status_pesanan_id == 2 ? 'selected' : '' }}>Dikonfirmasi</option> @endif
                                            @if(in_array(3, $validNext)) <option value="3" {{ $p->status_pesanan_id == 3 ? 'selected' : '' }}>Sedang Diproses</option> @endif
                                            @if(in_array(4, $validNext)) <option value="4" {{ $p->status_pesanan_id == 4 ? 'selected' : '' }}>Siap Dikirim</option> @endif
                                            @if(in_array(5, $validNext)) <option value="5" {{ $p->status_pesanan_id == 5 ? 'selected' : '' }}>Selesai</option> @endif
                                        </select>
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
                                    <button type="button" @click="$dispatch('open-nasibox-drawer', {url: '{{ route('admin.pesanan.nasibox.show', $p->id) }}'})" title="Detail" class="text-gray-500 transition hover:text-gray-900">
                                        <x-heroicon-o-eye class="w-4 h-4" />
                                    </button>
                                    
                                    @php
                                        $buktiPending = $p->pembayaran->firstWhere('status_verifikasi', 'menunggu_verifikasi');
                                        $buktiTerakhir = $p->pembayaran->whereNotNull('bukti_pembayaran')->last();
                                    @endphp
                                    

    
                                    @if($buktiPending)
                                        <form id="form-verif-{{ $buktiPending->id }}" action="{{ route('admin.bukti.verifikasi-dp', $buktiPending->id) }}" method="POST" class="hidden">
                                            @csrf @method('PATCH')
                                        </form>
                                        <button type="button" onclick="window.confirmDialog({ title: 'Verifikasi Pembayaran', name: 'Verifikasi bukti pembayaran pesanan ini?', message: 'Pastikan bukti transfer sudah benar sebelum diverifikasi.', formId: 'form-verif-{{ $buktiPending->id }}', confirmText: 'Verifikasi', cancelText: 'Batal' })" title="Verifikasi" class="text-green-600 transition hover:text-green-800">
                                            <x-heroicon-o-check-badge class="w-4 h-4" />
                                        </button>
                                    @endif


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

{{-- Detail Nasi Box Drawer --}}
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
}" @open-nasibox-drawer.window="openDrawer($event.detail.url)">
    
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