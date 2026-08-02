@extends('layouts.pos')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800">
    <div class="w-full p-6 space-y-5">
        
        {{-- Header --}}
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-black text-gray-900 tracking-tight">Daftar Pesanan Catering</h1>
                <p class="text-sm text-gray-500 font-medium mt-1">Kelola seluruh transaksi pesanan catering, tanggal acara, rincian porsi & pembayaran DP.</p>
            </div>
        </div>

        {{-- Alert --}}
        <x-ui.alert />

        {{-- Filter Bar --}}
        <div class="flex flex-col sm:flex-row gap-2 items-start sm:items-center justify-between mb-3 shrink-0">
            <form action="{{ route('admin.pesanan.catering.index') }}" method="GET" class="flex items-center gap-2 w-full sm:w-auto">
                <div class="relative flex-1 sm:flex-none sm:w-56">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Kode / Pemesan / HP…" class="w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-2xl focus:ring-1 focus:ring-gray-400 focus:border-gray-400 outline-none bg-white">
                    <input type="hidden" name="status" value="{{ request('status', 'all') }}">
                </div>
                <button type="submit" class="text-xs font-medium bg-white border border-gray-200 text-gray-600 rounded-2xl px-3 py-2 hover:bg-gray-50 transition-colors shrink-0">Cari</button>
            </form>
            
            <div class="flex items-center gap-1 text-xs font-medium overflow-x-auto no-scrollbar shrink-0">
                <span class="text-gray-500 mr-1">Status:</span>
                <a href="{{ route('admin.pesanan.catering.index', ['status' => 'all']) }}" class="px-3 py-1.5 rounded-2xl transition-colors {{ request('status', 'all') === 'all' ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100' }}">Semua</a>
                <a href="{{ route('admin.pesanan.catering.index', ['status' => 'ditinjau']) }}" class="px-3 py-1.5 rounded-2xl transition-colors {{ request('status') === 'ditinjau' ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100' }}">Baru</a>
                <a href="{{ route('admin.pesanan.catering.index', ['status' => 'terkonfirmasi']) }}" class="px-3 py-1.5 rounded-2xl transition-colors {{ request('status') === 'terkonfirmasi' ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100' }}">Terkonfirmasi</a>
                <a href="{{ route('admin.pesanan.catering.index', ['status' => 'diproses']) }}" class="px-3 py-1.5 rounded-2xl transition-colors {{ request('status') === 'diproses' ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100' }}">Diproses</a>
                <a href="{{ route('admin.pesanan.catering.index', ['status' => 'selesai']) }}" class="px-3 py-1.5 rounded-2xl transition-colors {{ request('status') === 'selesai' ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100' }}">Selesai</a>
            </div>
        </div>

        {{-- Order Table --}}
        <div class="bg-white rounded-3xl border border-gray-200 overflow-x-auto">
            <table class="w-full text-sm min-w-[1100px]">
                <thead>
                    <tr class="border-b border-gray-100 text-xs font-semibold text-gray-400 uppercase tracking-wide">
                        <th class="px-4 py-3 text-left w-10">No.</th>
                        <th class="px-4 py-3 text-left">No. Pesanan</th>
                        <th class="px-4 py-3 text-left">Tanggal Pesan</th>
                        <th class="px-4 py-3 text-left">Pelanggan</th>
                        <th class="px-4 py-3 text-left">Tanggal Acara</th>
                        <th class="px-4 py-3 text-left">Paket</th>
                        <th class="px-4 py-3 text-left">Jumlah Porsi</th>
                        <th class="px-4 py-3 text-left">Total Tagihan</th>
                        <th class="px-4 py-3 text-left">Pembayaran</th>
                        <th class="px-4 py-3 text-left">Pengantaran</th>
                        <th class="px-4 py-3 text-left">Status Pesanan</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($pesanans as $p)
                        <tr class="hover:bg-gray-50/60 transition-colors group">
                            <td class="px-4 py-3 text-xs text-gray-500 font-medium align-middle">
                                {{ $loop->iteration + ($pesanans->currentPage() - 1) * $pesanans->perPage() }}
                            </td>
                            <td class="px-4 py-3 align-middle">
                                <p class="font-semibold text-gray-900 font-mono text-xs whitespace-nowrap">{{ $p->nomor_pesanan }}</p>
                            </td>
                            <td class="px-4 py-3 align-middle whitespace-nowrap">
                                <p class="text-xs text-gray-700 font-medium">{{ $p->dibuat_pada ? \Carbon\Carbon::parse($p->dibuat_pada)->format('d M Y') : '-' }}</p>
                                <p class="text-[10px] text-gray-400">{{ $p->dibuat_pada ? \Carbon\Carbon::parse($p->dibuat_pada)->format('H:i') : '' }}</p>
                            </td>
                            <td class="px-4 py-3 align-middle">
                                <p class="font-semibold text-gray-900 text-xs whitespace-nowrap">{{ optional($p->pelanggan)->nama ?? $p->jadwal_pesanan->nama_penerima ?? '-' }}</p>
                                @if($p->pelanggan && $p->pelanggan->nomor_telepon)
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $p->pelanggan->nomor_telepon) }}" target="_blank" class="text-xs text-emerald-600 font-medium hover:underline inline-flex items-center gap-1 mt-0.5">
                                        <i class="ph-bold ph-whatsapp-logo"></i>
                                        <span class="whitespace-nowrap">{{ $p->pelanggan->nomor_telepon }}</span>
                                    </a>
                                @endif
                            </td>
                            <td class="px-4 py-3 align-middle whitespace-nowrap">
                                @if($p->jadwal_pesanan?->tanggal_acara)
                                    <p class="text-xs text-gray-700 font-medium">{{ \Carbon\Carbon::parse($p->jadwal_pesanan->tanggal_acara)->format('d M Y') }}</p>
                                    <p class="text-[10px] text-gray-400">{{ \Carbon\Carbon::parse($p->jadwal_pesanan->tanggal_acara)->format('H:i') }}</p>
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 align-middle">
                                @php $paket = $p->detail_pesanan->first(); @endphp
                                <p class="font-semibold text-gray-900 text-xs">{{ $paket->menu->nama_menu ?? 'Paket Catering' }}</p>
                            </td>
                            <td class="px-4 py-3 align-middle">
                                <span class="inline-block text-xs font-semibold px-2 py-0.5 rounded-xl bg-blue-50 text-blue-700 whitespace-nowrap">
                                    {{ $paket->jumlah ?? 0 }} Porsi
                                </span>
                            </td>
                            <td class="px-4 py-3 font-semibold text-gray-900 tabular-nums whitespace-nowrap">
                                Rp {{ number_format($p->total_tagihan, 0, ',', '.') }}
                                @php $totalP = (float) $p->total_tagihan; @endphp
                            </td>
                            <td class="px-4 py-3 align-middle">
                                @php
                                    $dpP = (float) $p->pembayaran->whereIn('status_pembayaran_id', [2, 3])->sum('jumlah_bayar');
                                    $lunasP = (float) $p->pembayaran->where('status_pembayaran_id', 3)->sum('jumlah_bayar');
                                    $bayarP = $lunasP >= $totalP ? 'lunas' : ($dpP > 0 ? 'dp' : 'belum');
                                @endphp
                                <span class="text-xs font-bold uppercase tracking-wider whitespace-nowrap {{ $bayarP === 'lunas' ? 'text-emerald-600' : ($bayarP === 'dp' ? 'text-blue-600' : 'text-amber-600') }}">
                                    {{ $bayarP === 'lunas' ? 'Lunas' : ($bayarP === 'dp' ? 'DP Terbayar' : 'Belum Bayar') }}
                                </span>
                                @if($dpP > 0)
                                    <p class="text-[10px] text-gray-400 mt-0.5">Rp {{ number_format($dpP, 0, ',', '.') }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 align-middle">
                                @php $metodeKirim = $p->pengantaran ? 'Diantar' : 'Ambil Sendiri'; @endphp
                                <span class="inline-block text-xs font-semibold px-2 py-0.5 rounded-xl {{ $p->pengantaran ? 'bg-violet-50 text-violet-700' : 'bg-gray-100 text-gray-600' }} whitespace-nowrap">
                                    {{ $metodeKirim }}
                                </span>
                            </td>
                            <td class="px-4 py-3 align-middle">
                                @php
                                    $sColors = [
                                        1 => 'bg-amber-50 text-amber-700',
                                        2 => 'bg-blue-50 text-blue-700',
                                        3 => 'bg-indigo-50 text-indigo-700',
                                        4 => 'bg-cyan-50 text-cyan-700',
                                        5 => 'bg-emerald-50 text-emerald-700',
                                        6 => 'bg-rose-50 text-rose-700',
                                    ];
                                    $sColor = $sColors[$p->status_pesanan_id] ?? 'bg-gray-100 text-gray-700';
                                @endphp
                                <span class="inline-block text-xs font-semibold px-2.5 py-0.5 rounded-xl {{ $sColor }} whitespace-nowrap">
                                    {{ $p->status_pesanan->nama_status ?? '-' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('admin.pesanan.catering.show', $p->id) }}" title="Detail" class="w-7 h-7 rounded-full flex items-center justify-center bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors">
                                        <x-heroicon-o-eye class="w-3 h-3" />
                                    </a>
                                    <button type="button" onclick="alert('Fitur Ubah Pesanan belum tersedia')" title="Ubah" class="w-7 h-7 rounded-full flex items-center justify-center bg-amber-50 text-amber-600 hover:bg-amber-100 transition-colors">
                                        <x-heroicon-o-pencil-square class="w-3 h-3" />
                                    </button>
                                    @php $buktiPending = $p->pembayaran->firstWhere('status_pembayaran_id', 1); @endphp
                                    @if($buktiPending)
                                        <form action="{{ route('admin.bukti.verifikasi-dp', $buktiPending->id) }}" method="POST" onsubmit="return confirm('Verifikasi bukti pembayaran pesanan ini?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" title="Konfirmasi Pembayaran" class="w-7 h-7 rounded-full flex items-center justify-center bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition-colors">
                                                <x-heroicon-o-check-badge class="w-3 h-3" />
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('admin.pesanan.catering.pdf', $p->id) }}" target="_blank" title="Cetak" class="w-7 h-7 rounded-full flex items-center justify-center bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors">
                                        <x-heroicon-o-printer class="w-3 h-3" />
                                    </a>
                                    @if(!in_array($p->status_pesanan_id, [5, 6]))
                                        <form action="{{ route('admin.pesanan.catering.update-status', $p->id) }}" method="POST" onsubmit="var r = prompt('Alasan pembatalan:'); if (!r) return false; this.querySelector('[name=alasan_batal]').value = r; return confirm('Batalkan pesanan ini?');">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="6">
                                            <input type="hidden" name="alasan_batal" value="">
                                            <button type="submit" title="Batalkan" class="w-7 h-7 rounded-full flex items-center justify-center bg-rose-50 text-rose-600 hover:bg-rose-100 transition-colors">
                                                <x-heroicon-o-no-symbol class="w-3 h-3" />
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="px-4 py-12 text-center text-gray-400">
                                <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                <p class="text-sm font-medium">Tidak ada data pesanan catering.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4 shrink-0">{{ $pesanans->links() }}</div>

    </div>
</div>
@endsection