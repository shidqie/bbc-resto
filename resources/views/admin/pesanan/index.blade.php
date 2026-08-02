@extends('layouts.pos')

@section('title', 'Semua Pesanan')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800">
    <div class="w-full p-6 space-y-5">
        {{-- PAGE HEADER --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-gray-900 tracking-tight">Semua Pesanan</h1>
                <p class="text-sm text-gray-500 font-medium mt-1">Daftar seluruh pesanan Dine In, Catering, dan Nasi Box</p>
            </div>
        </div>

    {{-- Filter & Pencarian --}}
    <div class="bg-white p-4 rounded-[2.25rem] shadow-sm border border-gray-100 mb-6">
        <form action="{{ route('admin.pesanan.index') }}" method="GET" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-bold text-gray-600 mb-1">Cari Pesanan</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="No Pesanan / Nama Pemesan" 
                       class="w-full border-gray-300 rounded-3xl text-sm focus:border-emerald-500 focus:ring-emerald-500">
            </div>
            <div class="w-40">
                <label class="block text-xs font-bold text-gray-600 mb-1">Jenis Pesanan</label>
                <select name="jenis" class="w-full border-gray-300 rounded-3xl text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">Semua Jenis</option>
                    @foreach($jenis_pesanan as $jp)
                        <option value="{{ $jp->id }}" {{ request('jenis') == $jp->id ? 'selected' : '' }}>{{ $jp->nama_jenis }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-48">
                <label class="block text-xs font-bold text-gray-600 mb-1">Status</label>
                <select name="status" class="w-full border-gray-300 rounded-3xl text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">Semua Status</option>
                    @foreach($status_pesanan as $sp)
                        <option value="{{ $sp->id }}" {{ request('status') == $sp->id ? 'selected' : '' }}>{{ $sp->nama_status }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-5 py-2.5 bg-[#0F2E23] hover:bg-[#0a1f17] text-white rounded-3xl text-sm font-bold shadow-sm transition-colors">
                <x-heroicon-o-funnel class="w-4 h-4 mr-1.5" /> Terapkan
            </button>
            <a href="{{ route('admin.pesanan.index') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-3xl text-sm font-bold transition-colors">
                Reset
            </a>
        </form>
    </div>

    {{-- Tabel Data --}}
    <div class="bg-white rounded-[2.25rem] shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-gray-500 font-extrabold uppercase text-[11px] tracking-wider border-b border-gray-100">
                        <th class="p-4">No.</th>
                        <th class="p-4">ID Pesanan</th>
                        <th class="p-4">Nama Konsumen</th>
                        <th class="p-4">Hari/Tanggal</th>
                        <th class="p-4">Total</th>
                        <th class="p-4 text-center">Status Pembayaran</th>
                        <th class="p-4 text-center">Status Pesanan</th>
                        <th class="p-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 text-sm">
                    @forelse($pesanans as $index => $pesanan)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="p-4 text-gray-500 font-medium">
                            {{ ($pesanans->firstItem() ?? 1) + $index }}
                        </td>
                        <td class="p-4 font-bold text-gray-900">
                            {{ $pesanan->nomor_pesanan ?? 'DIN-'.$pesanan->id }}
                        </td>
                        <td class="p-4 font-medium text-gray-800">
                            @php
                                $nama = 'Tamu';
                                if (!empty($pesanan->catatan)) {
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
                            @endphp
                            {{ $nama }}
                        </td>
                        <td class="p-4 text-gray-600">
                            {{ \Carbon\Carbon::parse($pesanan->dibuat_pada)->format('d F Y, H:i') }}
                        </td>
                        <td class="p-4 font-black text-[#0F2E23]">
                            Rp{{ number_format($pesanan->total_tagihan, 0, ',', '.') }}
                        </td>
                        <td class="p-4 text-center">
                            @php
                                $totalBayar = $pesanan->pembayaran->sum('jumlah_bayar');
                                if($totalBayar >= $pesanan->total_tagihan && $pesanan->total_tagihan > 0) {
                                    $payStatus = 'Lunas';
                                    $payColor = 'emerald';
                                } elseif($totalBayar > 0) {
                                    $payStatus = 'DP';
                                    $payColor = 'amber';
                                } else {
                                    $payStatus = 'Belum Lunas';
                                    $payColor = 'red';
                                }
                            @endphp
                            <span class="px-2.5 py-1 bg-{{$payColor}}-50 text-{{$payColor}}-700 border border-{{$payColor}}-200 rounded-2xl text-[11px] font-extrabold uppercase whitespace-nowrap">
                                {{ $payStatus }}
                            </span>
                        </td>
                        <td class="p-4 text-center">
                            @php
                                $color = 'gray';
                                if($pesanan->status_pesanan_id == 5) $color = 'emerald';
                                elseif($pesanan->status_pesanan_id == 1) $color = 'amber';
                                elseif($pesanan->status_pesanan_id == 6) $color = 'red';
                                else $color = 'blue';
                            @endphp
                            <span class="px-2.5 py-1 bg-{{$color}}-50 text-{{$color}}-700 border border-{{$color}}-200 rounded-2xl text-[11px] font-extrabold uppercase whitespace-nowrap">
                                {{ optional($pesanan->status_pesanan)->nama_status ?? 'Unknown' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <button type="button" onclick="openDetailDrawer({{ $pesanan->id }})" title="Detail" class="w-7 h-7 rounded-full flex items-center justify-center bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors">
                                    <x-heroicon-o-eye class="w-3 h-3" />
                                </button>
                                <button type="button" onclick="alert('Fitur Ubah Pesanan belum tersedia')" title="Ubah" class="w-7 h-7 rounded-full flex items-center justify-center bg-amber-50 text-amber-600 hover:bg-amber-100 transition-colors">
                                    <x-heroicon-o-pencil-square class="w-3 h-3" />
                                </button>
                                <button type="button" onclick="window.open('/pos/dinein/pesanan/{{ $pesanan->id }}/print-nota', '_blank')" title="Cetak" class="w-7 h-7 rounded-full flex items-center justify-center bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors">
                                    <x-heroicon-o-printer class="w-3 h-3" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="p-12 text-center text-gray-500 font-medium">
                            <div class="w-16 h-16 rounded-full bg-slate-50 text-slate-300 flex items-center justify-center mx-auto mb-3 text-2xl">
                                <x-heroicon-o-document-text class="w-5 h-5" />
                            </div>
                            Belum ada pesanan yang sesuai kriteria pencarian.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($pesanans->hasPages())
        <div class="p-4 border-t border-gray-100">
            {{ $pesanans->links() }}
        </div>
        @endif
    </div>
</div>

{{-- DRAWER: DETAIL PESANAN (SLIDE-IN RIGHT) --}}
<div id="drawerDetail" class="fixed inset-x-0 bottom-0 top-0 z-[100] hidden">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-[2px] transition-opacity" onclick="closeDetailDrawer()"></div>
    <div class="absolute right-0 top-0 h-full w-full max-w-md bg-white shadow-2xl flex flex-col translate-x-full transition-transform duration-300 ease-out" id="drawerDetailPanel">
        <div id="drawerDetailContent" class="h-full flex flex-col">
            {{-- Content will be loaded via AJAX --}}
            <div class="flex-1 flex items-center justify-center">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-emerald-600"></div>
            </div>
        </div>
    </div>
</div>

<script>
    function openDetailDrawer(id) {
        const drawer = document.getElementById('drawerDetail');
        const panel = document.getElementById('drawerDetailPanel');
        const content = document.getElementById('drawerDetailContent');
        
        // Reset state
        drawer.classList.remove('hidden');
        content.innerHTML = '<div class="flex-1 flex items-center justify-center"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-emerald-600"></div></div>';
        
        // Trigger slide in
        setTimeout(() => {
            panel.classList.remove('translate-x-full');
        }, 10);

        // Fetch partial content
        fetch(`/admin/pesanan/detail/${id}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.text())
        .then(html => {
            content.innerHTML = html;
        })
        .catch(err => {
            content.innerHTML = '<div class="p-6 text-center text-red-500">Gagal memuat detail pesanan.</div>';
        });
    }

    function closeDetailDrawer() {
        const drawer = document.getElementById('drawerDetail');
        const panel = document.getElementById('drawerDetailPanel');
        
        panel.classList.add('translate-x-full');
        setTimeout(() => {
            drawer.classList.add('hidden');
        }, 300);
    }
</script>
@endsection
