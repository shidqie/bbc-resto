{{-- 
    Halaman: Daftar Pesanan Dine-In & Master
    Deskripsi: Menampilkan data pesanan lengkap dengan rincian item menu, pelanggan, status pembayaran, dan aksi cepat.
--}}
@extends('layouts.pos')

@section('content')
<div class="flex-1 overflow-auto bg-gray-50/50 text-gray-800 font-sans">
    <div class="p-6 md:p-8 w-full space-y-6">
        
        {{-- Header --}}
        <x-ui.page-header title="Daftar Pesanan Resto" subtitle="Kelola transaksi pesanan dine-in, rincian menu, status dapur, dan pembayaran">
            <x-slot:actions>
                <x-ui.button href="{{ route('pos.dinein.index') }}" icon="fa-plus">Pesanan Baru (POS)</x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        {{-- Alert --}}
        <x-ui.alert />

        {{-- Statistik Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-ui.stat-card label="Pesanan Baru" :value="$stats['baru']" icon="fa-shopping-bag" color="blue" />
            <x-ui.stat-card label="Sedang Diproses" :value="$stats['diproses']" icon="fa-clock" color="orange" />
            <x-ui.stat-card label="Pesanan Selesai" :value="$stats['selesai']" icon="fa-check-circle" color="green" />
            <x-ui.stat-card label="Total Transaksi" :value="$pesanans->total()" icon="fa-receipt" color="purple" />
        </div>

        {{-- Data Table Section --}}
        <x-ui.data-table :paginator="$pesanans">
            <x-slot:toolbar>
                <form action="{{ route('pesanan.index') }}" method="GET" class="w-full flex flex-col sm:flex-row gap-3">
                    <div class="relative min-w-[280px] flex-1">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari No. Pesanan / Nama / Meja / HP..." 
                               class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] outline-none text-xs font-medium transition-all bg-white">
                        <x-heroicon-o-magnifying-glass class="absolute left-3.5 top-2.5 text-gray-400 text-sm w-4 h-4 inline-block shrink-0" />
                    </div>
                    <select name="jenis" onchange="this.form.submit()" class="px-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] outline-none text-xs font-semibold bg-white min-w-[140px]">
                        <option value="">Semua Jenis</option>
                        <option value="dine_in" {{ request('jenis') == 'dine_in' ? 'selected' : '' }}>Dine In</option>
                        <option value="catering" {{ request('jenis') == 'catering' ? 'selected' : '' }}>Catering</option>
                        <option value="nasi_box" {{ request('jenis') == 'nasi_box' ? 'selected' : '' }}>Nasi Box</option>
                    </select>
                    <select name="status" onchange="this.form.submit()" class="px-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] outline-none text-xs font-semibold bg-white min-w-[140px]">
                        <option value="">Semua Status</option>
                        <option value="baru" {{ request('status') == 'baru' ? 'selected' : '' }}>Baru</option>
                        <option value="diproses" {{ request('status') == 'diproses' ? 'selected' : '' }}>Diproses</option>
                        <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                        <option value="dibatalkan" {{ request('status') == 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </form>
            </x-slot:toolbar>

            <!-- Table with Highly Detailed Columns -->
            <table class="w-full text-left border-collapse min-w-[1100px]">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-[11px] font-bold uppercase tracking-wider border-b border-gray-200 sticky top-0 z-10">
                        <th class="px-4 py-3.5">Info Pesanan & Waktu</th>
                        <th class="px-4 py-3.5">Pelanggan & Lokasi</th>
                        <th class="px-4 py-3.5">Rincian Item Menu</th>
                        <th class="px-4 py-3.5">Pembayaran & Tagihan</th>
                        <th class="px-4 py-3.5">Status & Alur Dapur</th>
                        <th class="px-4 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-xs">
                    @forelse($pesanans as $p)
                        <tr class="hover:bg-blue-50/20 transition-colors align-top">
                            
                            <!-- 1. Info Pesanan & Waktu -->
                            <td class="px-4 py-3.5">
                                <div class="font-mono font-extrabold text-gray-900 text-xs flex items-center gap-1.5">
                                    <i class="fa-solid fa-receipt text-blue-500 text-[11px]"></i>
                                    <span>{{ $p->no_pesanan }}</span>
                                </div>
                                <div class="text-[11px] text-gray-500 font-medium mt-1 flex items-center gap-1">
                                    <i class="fa-regular fa-clock text-[10px] text-gray-400"></i>
                                    <span>{{ $p->tanggal_pesanan ? $p->tanggal_pesanan->format('d M Y, H:i') : '-' }}</span>
                                </div>
                                <div class="mt-1.5 flex items-center gap-1.5 flex-wrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-blue-50 text-blue-700 border border-blue-100 uppercase">
                                        {{ str_replace('_', ' ', $p->jenis_pesanan) }}
                                    </span>
                                    @if($p->user)
                                        <span class="text-[10px] text-gray-400 font-medium" title="Staff Input">
                                            <i class="fa-solid fa-user-gear mr-0.5"></i>{{ $p->user->name }}
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- 2. Pelanggan & Lokasi / Meja -->
                            <td class="px-4 py-3.5">
                                @php
                                    $namaPelanggan = $p->nama_pelanggan ?? 'Pelanggan Umum';
                                    $parts = explode(' - ', $namaPelanggan);
                                    $nama = $parts[0];
                                    $phone = isset($parts[1]) ? $parts[1] : null;
                                @endphp
                                <div class="font-bold text-gray-900 flex items-center gap-1.5">
                                    <div class="w-6 h-6 rounded-full bg-gray-100 text-gray-700 font-bold text-[10px] flex items-center justify-center flex-shrink-0">
                                        {{ strtoupper(substr($nama, 0, 1)) }}
                                    </div>
                                    <span class="truncate max-w-[140px]">{{ $nama }}</span>
                                </div>

                                @if($phone)
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $phone) }}" target="_blank" class="text-[11px] text-emerald-600 hover:underline font-semibold mt-1 flex items-center gap-1">
                                        <i class="fa-brands fa-whatsapp"></i>{{ $phone }}
                                    </a>
                                @endif

                                @if($p->no_meja)
                                    <div class="mt-1.5">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-amber-50 text-amber-900 border border-amber-200">
                                            <i class="fa-solid fa-location-dot text-[9px]"></i> Meja {{ $p->no_meja }}
                                        </span>
                                    </div>
                                @endif
                            </td>

                            <!-- 3. Rincian Item Menu (Detailed Menu Items & Notes) -->
                            <td class="px-4 py-3.5 max-w-[280px]">
                                @if($p->details && $p->details->count() > 0)
                                    <div class="space-y-1">
                                        @foreach($p->details->take(3) as $d)
                                            <div class="text-[11px] text-gray-800 flex items-start justify-between gap-1 leading-snug">
                                                <div class="truncate">
                                                    <span class="font-bold text-blue-600">{{ $d->jumlah }}x</span>
                                                    <span>{{ $d->menu->nama ?? 'Item Menu' }}</span>
                                                </div>
                                                <span class="text-gray-400 font-medium text-[10px] whitespace-nowrap">Rp {{ number_format($d->subtotal ?? ($d->jumlah * $d->harga_satuan), 0, ',', '.') }}</span>
                                            </div>
                                            @if($d->catatan)
                                                <p class="text-[10px] text-gray-400 italic pl-3 border-l border-gray-200 line-clamp-1" title="{{ $d->catatan }}">
                                                    * {{ $d->catatan }}
                                                </p>
                                            @endif
                                        @endforeach

                                        @if($p->details->count() > 3)
                                            <p class="text-[10px] text-gray-400 font-bold italic mt-0.5">
                                                + {{ $p->details->count() - 3 }} item menu lainnya...
                                            </p>
                                        @endif
                                    </div>
                                    <div class="mt-1.5 pt-1 border-t border-gray-100 flex items-center justify-between text-[10px] text-gray-500 font-semibold">
                                        <span>Total Items: {{ $p->details->sum('jumlah') }} Porsi</span>
                                    </div>
                                @else
                                    <span class="text-gray-400 italic text-[11px]">Tidak ada rincian item</span>
                                @endif
                            </td>

                            <!-- 4. Pembayaran & Tagihan -->
                            <td class="px-4 py-3.5">
                                <div class="font-extrabold text-gray-900 text-sm">
                                    Rp {{ number_format($p->total_harga, 0, ',', '.') }}
                                </div>
                                <div class="mt-1 flex items-center gap-1 flex-wrap">
                                    @if($p->status_pembayaran == 'lunas')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <i class="fa-solid fa-circle-check text-[9px] mr-1"></i> Lunas
                                        </span>
                                    @elseif($p->status_pembayaran == 'dp')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                            <i class="fa-solid fa-clock text-[9px] mr-1"></i> DP Terbayar
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-50 text-red-700 border border-red-200">
                                            <i class="fa-solid fa-circle-exclamation text-[9px] mr-1"></i> Belum Bayar
                                        </span>
                                    @endif

                                    @if($p->pembayarans && $p->pembayarans->count() > 0)
                                        <span class="text-[10px] font-mono uppercase bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded">
                                            {{ $p->pembayarans->last()->metode_pembayaran ?? 'POS' }}
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- 5. Status Workflow & Inline Changer -->
                            <td class="px-4 py-3.5">
                                <form action="{{ route('pesanan.update-status', $p->id) }}" method="POST" class="inline-block">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status_pesanan" onchange="this.form.submit()" 
                                            class="py-1 px-2 text-[11px] font-bold rounded-lg border focus:outline-none transition-all cursor-pointer
                                                   {{ $p->status_pesanan == 'baru' ? 'bg-gray-100 text-gray-800 border-gray-300' : '' }}
                                                   {{ $p->status_pesanan == 'diproses' ? 'bg-amber-50 text-amber-900 border-amber-300' : '' }}
                                                   {{ $p->status_pesanan == 'selesai' ? 'bg-emerald-50 text-emerald-900 border-emerald-300' : '' }}
                                                   {{ $p->status_pesanan == 'dibatalkan' ? 'bg-red-50 text-red-800 border-red-300' : '' }}">
                                        <option value="baru" {{ $p->status_pesanan == 'baru' ? 'selected' : '' }}>• Baru</option>
                                        <option value="diproses" {{ $p->status_pesanan == 'diproses' ? 'selected' : '' }}>• Diproses Dapur</option>
                                        <option value="selesai" {{ $p->status_pesanan == 'selesai' ? 'selected' : '' }}>• Selesai</option>
                                        <option value="dibatalkan" {{ $p->status_pesanan == 'dibatalkan' ? 'selected' : '' }}>• Dibatalkan</option>
                                    </select>
                                </form>
                            </td>

                            <!-- 6. Action Column -->
                            <td class="px-4 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('pesanan.show', $p->id) }}" 
                                       class="px-2.5 py-1.5 bg-gray-900 hover:bg-gray-800 text-white rounded-lg text-[11px] font-bold transition-all shadow-xs flex items-center gap-1"
                                       title="Lihat Detail Rincian">
                                        <i class="fa-solid fa-eye"></i> Detail
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center">
                                <x-ui.empty-state icon="fa-receipt" title="Tidak ada data pesanan" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-ui.data-table>
    </div>
</div>
@endsection
