{{-- Halaman: Stok Bahan Baku --}}
@extends('layouts.pos')
@section('title', 'Stok Bahan Baku')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800">
    <div class="w-full p-6 space-y-5">

        {{-- PAGE HEADER --}}
        <x-ui.page-header
            title="Stok Bahan Baku"
            subtitle="Monitor stok bahan baku untuk kebutuhan dine in dan nasi box."
            :breadcrumbs="['Persediaan', 'Stok Bahan Baku']">
            <x-slot:actions>
                <div class="relative inline-block text-left" x-data="{ open: false }" @click.outside="open = false">
                    <button @click="open = !open" type="button" class="inline-flex items-center gap-2 px-4 py-2 bg-[#0D3024] hover:bg-[#0D3024]/90 text-white font-semibold text-sm rounded-lg shadow-sm transition-all duration-150">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.5v15m7.5-7.5h-15"></path></svg>
                        <span>Buat Purchase Order</span>
                        <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>

                    <div x-show="open" x-cloak
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-52 rounded-xl bg-white shadow-xl border border-gray-100 py-1.5 z-50 text-sm">
                        
                        <a href="{{ route('pengadaan.po.create', ['tipe' => 'Harian']) }}" class="flex items-center gap-2.5 px-4 py-2.5 text-gray-700 hover:bg-gray-50 hover:text-[#0D3024] font-medium transition-colors">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <span>PO Nasi Box & Harian</span>
                        </a>
                        <a href="{{ route('pengadaan.po.create', ['tipe' => 'Catering']) }}" class="flex items-center gap-2.5 px-4 py-2.5 text-gray-700 hover:bg-gray-50 hover:text-[#0D3024] font-medium transition-colors">
                            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.701 2.701 0 01-1.5-.454M9 6v2m3-2v2m3-2v2M9 3h.01M12 3h.01M15 3h.01M21 21v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7h18z"></path></svg>
                            <span>PO Katering</span>
                        </a>
                    </div>
                </div>
                <x-ui.button variant="secondary" href="{{ route('bahan-baku.index') }}">
                    Data Bahan Baku
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.tab-list class="mb-4">
            <x-ui.tab :active="$tab === 'stok'" href="{{ route('stok-operasional.index', ['tab' => 'stok']) }}">
                Stok Saat Ini
            </x-ui.tab>
            <x-ui.tab :active="$tab === 'riwayat'" href="{{ route('stok-operasional.index', ['tab' => 'riwayat']) }}">
                Riwayat Penggunaan
            </x-ui.tab>
        </x-ui.tab-list>

        <x-ui.alert />

        @if($tab === 'stok')
            {{-- Stat Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-center">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Total Bahan</span>
                    <span class="text-2xl font-bold text-gray-900">{{ $stats['total_bahan'] }}</span>
                </div>
                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-center">
                    <span class="text-xs font-semibold text-emerald-600 uppercase tracking-wider mb-1">Stok Aman</span>
                    <span class="text-2xl font-bold text-gray-900">{{ $stats['total_aman'] }}</span>
                </div>
                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-center">
                    <span class="text-xs font-semibold text-amber-600 uppercase tracking-wider mb-1">Stok Menipis</span>
                    <span class="text-2xl font-bold text-gray-900">{{ $stats['total_menipis'] }}</span>
                </div>
                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-center">
                    <span class="text-xs font-semibold text-red-600 uppercase tracking-wider mb-1">Stok Habis</span>
                    <span class="text-2xl font-bold text-gray-900">{{ $stats['total_habis'] }}</span>
                </div>
            </div>

            {{-- Table Stok --}}
            <x-ui.data-table :paginator="$bahanBakus">
                <x-slot:toolbar>
                    <form action="{{ route('stok-operasional.index') }}" method="GET" class="flex items-center gap-2 w-full flex-wrap">
                        <input type="hidden" name="tab" value="stok">
                        <x-search-input name="search" value="{{ request('search') }}" placeholder="Cari bahan baku..." />
                        <x-ui.multi-select name="kategori" :options="$kategoris->pluck('nama_kategori', 'id')->toArray()" :selected="request('kategori')" label="Kategori" type="radio" />
                        <x-ui.multi-select name="status" :options="['aman' => 'Aman', 'menipis' => 'Menipis', 'habis' => 'Habis']" :selected="request('status')" label="Status" type="radio" />
                        @if(request()->hasAny(['search', 'kategori', 'status']))
                            <x-ui.button href="{{ route('stok-operasional.index', ['tab' => 'stok']) }}" variant="danger" size="sm">Reset</x-ui.button>
                        @endif
                    </form>
                </x-slot:toolbar>

                <x-ui.table class="min-w-[850px]">
                    <x-ui.table.header>
                        <th class="px-4 py-3.5 text-left w-12">No</th>
                        <th class="px-4 py-3.5 text-left">Nama Bahan</th>
                        <th class="px-4 py-3.5 text-right">Stok Saat Ini</th>
                        <th class="px-4 py-3.5 text-right">Stok Minimum</th>
                        <th class="px-4 py-3.5 text-left">Status</th>
                        <th class="px-4 py-3.5 text-center">Aksi</th>
                    </x-ui.table.header>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($bahanBakus as $i => $bahan)
                        @php
                            $stok = (float)$bahan->stok;
                            $min = (float)$bahan->stok_minimal;
                            $isHabis = $stok <= 0;
                            $isMenipis = !$isHabis && $stok <= $min;
                        @endphp
                        <x-ui.table.row class="{{ $isHabis ? 'bg-red-50/30' : ($isMenipis ? 'bg-amber-50/30' : '') }}">
                            <td class="px-4 py-4 text-sm text-gray-500 font-medium align-middle">{{ $bahanBakus->firstItem() + $i }}</td>
                            <td class="px-4 py-4">
                                <p class="font-semibold text-gray-900 leading-tight">{{ $bahan->nama_bahan }}</p>
                            </td>
                            <td class="px-4 py-4 text-right">
                                <span class="font-bold text-lg {{ $isHabis ? 'text-red-600' : ($isMenipis ? 'text-amber-600' : 'text-emerald-600') }}">{{ \App\Helpers\UnitHelper::formatQuantity($stok, $bahan->satuan->singkatan ?? $bahan->satuan->nama_satuan ?? 'gram') }}</span>
                            </td>
                            <td class="px-4 py-4 text-right text-sm text-gray-500 font-medium">
                                {{ \App\Helpers\UnitHelper::formatQuantity($min, $bahan->satuan->singkatan ?? $bahan->satuan->nama_satuan ?? 'gram') }}
                            </td>
                            <td class="px-4 py-4">
                                @if($isHabis)
                                    <x-ui.badge color="danger">Habis</x-ui.badge>
                                @elseif($isMenipis)
                                    <x-ui.badge color="warning">Menipis</x-ui.badge>
                                @else
                                    <x-ui.badge color="success">Aman</x-ui.badge>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <x-ui.action-button onclick="openDetailDrawer({{ $bahan->id }})" title="Detail">
                                        <x-heroicon-o-eye class="w-4 h-4" />
                                    </x-ui.action-button>
                                </div>
                            </td>
                        </x-ui.table.row>
                        @empty
                        <tr>
                            <td colspan="6">
                                <x-ui.empty-state icon="cube" title="Belum ada data stok" message="Tidak ada data stok ditemukan." />
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </x-ui.table>
            </x-ui.data-table>
        
        @else
            {{-- Table Riwayat --}}
            <x-ui.data-table :paginator="$riwayats">
                <x-slot:toolbar>
                    <form action="{{ route('stok-operasional.index') }}" method="GET" class="flex items-center gap-2 w-full flex-wrap">
                        <input type="hidden" name="tab" value="riwayat">
                        <x-search-input name="search" value="{{ request('search') }}" placeholder="Cari bahan / referensi..." />
                        <x-ui.multi-select name="jenis_penggunaan" :options="['Dine-In' => 'Dine-In', 'Nasi Box' => 'Nasi Box', 'Penyesuaian' => 'Penyesuaian Keluar']" :selected="request('jenis_penggunaan')" label="Jenis Penggunaan" type="radio" />
                        @if(request()->hasAny(['search', 'jenis_penggunaan']))
                            <x-ui.button href="{{ route('stok-operasional.index', ['tab' => 'riwayat']) }}" variant="danger" size="sm">Reset</x-ui.button>
                        @endif
                    </form>
                </x-slot:toolbar>

                <x-ui.table class="min-w-[850px]">
                    <x-ui.table.header>
                        <th class="px-4 py-3.5 text-left w-12">No</th>
                        <th class="px-4 py-3.5 text-left">Transaksi / Referensi</th>
                        <th class="px-4 py-3.5 text-left">Waktu Mutasi</th>
                        <th class="px-4 py-3.5 text-left">Total Bahan</th>
                        <th class="px-4 py-3.5 text-center">Aksi</th>
                    </x-ui.table.header>
                    
                    @php
                        $groupedRiwayat = [];
                        foreach($riwayats as $riwayat) {
                            $pesanan = $riwayat->detail_pesanan?->pesanan;
                            if ($pesanan) {
                                $key = 'pesanan_' . $pesanan->id;
                                $judul = 'Pesanan #' . $pesanan->id_pesanan;
                                $tipe = $pesanan->jenis_pesanan?->nama_jenis ?? 'Dine In';
                                $pelanggan = $pesanan->pelanggan?->nama ?? null;
                                $meja = $pesanan->meja?->nomor_meja ?? null;
                            } elseif (preg_match('/Pesanan\s*#?([A-Z0-9\-]+)/i', $riwayat->catatan, $m)) {
                                $key = 'pesanan_' . $m[1];
                                $judul = 'Pesanan #' . $m[1];
                                $tipe = str_contains($riwayat->catatan, 'Nasi Box') ? 'Nasi Box' : 'Dine In';
                                $pelanggan = null;
                                $meja = null;
                            } elseif ($riwayat->detail_penyesuaian_stok) {
                                $penyesuaian = $riwayat->detail_penyesuaian_stok->penyesuaian_stok;
                                $nomorAdj = $penyesuaian?->nomor_penyesuaian ?? $riwayat->referensi_id;
                                $key = 'penyesuaian_' . ($penyesuaian?->id ?? $riwayat->detail_penyesuaian_stok_id);
                                $judul = 'Penyesuaian Stok' . ($nomorAdj ? ' #' . $nomorAdj : '');
                                $tipe = 'Penyesuaian';
                                $pelanggan = null;
                                $meja = null;
                            } else {
                                $key = 'other_' . md5($riwayat->catatan . $riwayat->tanggal_mutasi->format('Y-m-d H:i'));
                                $judul = $riwayat->catatan ?: 'Penggunaan Stok';
                                $tipe = 'Lainnya';
                                $pelanggan = null;
                                $meja = null;
                            }

                            if (!isset($groupedRiwayat[$key])) {
                                $groupedRiwayat[$key] = [
                                    'key' => $key,
                                    'judul' => $judul,
                                    'tipe' => $tipe,
                                    'pelanggan' => $pelanggan,
                                    'meja' => $meja,
                                    'tanggal' => $riwayat->tanggal_mutasi,
                                    'referensi' => $riwayat->referensi_id,
                                    'items' => [],
                                ];
                            }

                            $bId = $riwayat->bahan_baku_id;
                            if (!isset($groupedRiwayat[$key]['items'][$bId])) {
                                $groupedRiwayat[$key]['items'][$bId] = [
                                    'nama_bahan' => $riwayat->bahan_baku?->nama_bahan ?? '-',
                                    'kode_bahan' => $riwayat->bahan_baku?->id_bahan_baku ?? '',
                                    'satuan' => $riwayat->bahan_baku?->satuan?->singkatan ?? $riwayat->bahan_baku?->satuan?->nama_satuan ?? '',
                                    'jumlah' => 0,
                                    'stok_sesudah' => $riwayat->stok_sesudah,
                                ];
                            }
                            $groupedRiwayat[$key]['items'][$bId]['jumlah'] += (float) $riwayat->jumlah;
                            $groupedRiwayat[$key]['items'][$bId]['stok_sesudah'] = $riwayat->stok_sesudah;
                        }
                        $noGroup = $riwayats->firstItem();
                    @endphp

                    @forelse($groupedRiwayat as $groupKey => $group)
                        <tbody x-data="{ expanded: false }" class="divide-y divide-gray-100">
                            <tr class="hover:bg-gray-50/80 transition-colors cursor-pointer group" @click="expanded = !expanded">
                                <td class="px-4 py-4 text-sm text-gray-500 font-medium">{{ $noGroup++ }}</td>
                                <td class="px-4 py-4">
                                    <div class="space-y-0.5">
                                        <div class="font-bold text-gray-900 text-sm flex items-center gap-2">
                                            <span>{{ $group['judul'] }}</span>
                                            @if($group['tipe'])
                                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border {{ $group['tipe'] === 'Nasi Box' ? 'bg-blue-50 text-blue-700 border-blue-200' : ($group['tipe'] === 'Penyesuaian' ? 'bg-purple-50 text-purple-700 border-purple-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200') }}">
                                                    {{ $group['tipe'] }}
                                                </span>
                                            @endif
                                        </div>
                                        @if($group['pelanggan'] || $group['meja'])
                                            <p class="text-xs text-gray-500 font-medium">
                                                @if($group['pelanggan']) Pemesan: <span class="text-gray-700 font-semibold">{{ $group['pelanggan'] }}</span> @endif
                                                @if($group['meja']) • Meja {{ $group['meja'] }} @endif
                                            </p>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-600 font-medium">
                                    {{ $group['tanggal']->translatedFormat('d M Y, H:i') }} WIB
                                </td>
                                <td class="px-4 py-4 text-sm">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 font-bold border border-emerald-200 text-xs shadow-2xs">
                                        {{ count($group['items']) }} Bahan Keluar
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <button type="button" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors cursor-pointer">
                                        <x-heroicon-o-chevron-down class="w-5 h-5 transition-transform duration-200" x-bind:class="expanded ? 'rotate-180' : ''" />
                                    </button>
                                </td>
                            </tr>
                            
                            {{-- Expanded Details --}}
                            <tr x-show="expanded" x-cloak class="bg-gray-50/40 border-b border-gray-100">
                                <td colspan="5" class="p-0 border-t-0">
                                    <div class="px-6 py-4 md:px-12 md:py-4 bg-gray-50/70 border-y border-gray-100">
                                        <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">
                                            Rincian Bahan Baku Terpotong ({{ count($group['items']) }} Bahan)
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2.5">
                                            @foreach($group['items'] as $item)
                                                <div class="flex justify-between items-center p-2.5 bg-white rounded-xl border border-gray-200/80 shadow-2xs">
                                                    <div class="min-w-0 pr-2">
                                                        <p class="text-xs font-bold text-gray-900 truncate leading-tight">{{ $item['nama_bahan'] }}</p>
                                                        <p class="text-[10px] text-gray-400 mt-0.5 font-medium">
                                                            Sisa Stok: {{ \App\Helpers\UnitHelper::formatQuantity($item['stok_sesudah'] ?? 0, $item['satuan']) }}
                                                        </p>
                                                    </div>
                                                    <div class="text-right shrink-0">
                                                        <span class="text-xs font-extrabold text-red-600 font-mono">
                                                            -{{ \App\Helpers\UnitHelper::formatQuantity($item['jumlah'], $item['satuan']) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                        @empty
                            <tbody>
                                <tr>
                                    <td colspan="5">
                                        <x-ui.empty-state icon="clock" title="Belum ada riwayat" message="Tidak ada data riwayat penggunaan stok." />
                                    </td>
                                </tr>
                            </tbody>
                        @endforelse
                </x-ui.table>
            </x-ui.data-table>
        @endif
    </div>
</div>

<!-- Drawer Detail Bahan Baku Wrapper -->
<div id="drawerDetail" class="fixed inset-0 z-[100] hidden">
    <div id="drawerDetailOverlay" class="absolute inset-0 bg-gray-900/40 backdrop-blur-xs opacity-0 transition-opacity duration-300" onclick="closeDetailDrawer()"></div>
    <div id="drawerDetailPanel" class="absolute right-0 top-0 h-full w-full max-w-md bg-white shadow-2xl flex flex-col translate-x-full transition-transform duration-300">
        <div id="drawerDetailContent" class="flex-1 overflow-y-auto">
            <div class="flex items-center justify-center h-full">
                <svg class="animate-spin h-8 w-8 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<script>
    const BASE_URL = '{{ url('/persediaan') }}';

    function openDetailDrawer(id) {
        const drawer = document.getElementById('drawerDetail');
        const overlay = document.getElementById('drawerDetailOverlay');
        const panel = document.getElementById('drawerDetailPanel');
        const content = document.getElementById('drawerDetailContent');

        content.innerHTML = `
            <div class="flex items-center justify-center h-full">
                <svg class="animate-spin h-8 w-8 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        `;

        drawer.classList.remove('hidden');
        drawer.style.display = 'block';
        
        requestAnimationFrame(() => {
            panel.classList.remove('translate-x-full');
            panel.classList.add('translate-x-0');
            overlay.classList.remove('opacity-0');
            overlay.classList.add('opacity-100');
        });

        fetch(`{{ url('/bahan-baku') }}/${id}/drawer`)
            .then(res => res.text())
            .then(html => {
                content.innerHTML = html;
            })
            .catch(err => {
                content.innerHTML = '<div class="p-6 text-center text-red-500">Gagal memuat data detail bahan baku.</div>';
            });
    }

    function closeDetailDrawer() {
        const drawer = document.getElementById('drawerDetail');
        const overlay = document.getElementById('drawerDetailOverlay');
        const panel = document.getElementById('drawerDetailPanel');

        panel.classList.remove('translate-x-0');
        panel.classList.add('translate-x-full');
        overlay.classList.remove('opacity-100');
        overlay.classList.add('opacity-0');

        setTimeout(() => {
            drawer.classList.add('hidden');
            drawer.style.display = '';
        }, 300);
    }
</script>
@endsection
