@extends('layouts.pos')

@section('content')
<div class="flex-1 overflow-auto bg-gray-50/50 text-gray-800 font-sans">
    <div class="p-4 md:p-6 lg:p-8 max-w-[1400px] mx-auto space-y-6">
        
        {{-- Header --}}
        <x-ui.page-header title="Daftar Pesanan Nasi Box" subtitle="Kelola transaksi pesanan nasi box, tanggal kirim, rincian box & status pembayaran">
        </x-ui.page-header>

        {{-- Alert --}}
        <x-ui.alert />

        {{-- Statistik --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-ui.stat-card label="Pesanan Baru" :value="$stats['baru']" icon="fa-shopping-bag" color="blue" />
            <x-ui.stat-card label="Sedang Diproses" :value="$stats['diproses']" icon="fa-clock" color="orange" />
            <x-ui.stat-card label="Pesanan Selesai" :value="$stats['selesai']" icon="fa-check-circle" color="green" />
        </div>

        {{-- Tabel --}}
        <x-ui.data-table :paginator="$pesanans">
            <x-slot:toolbar>
                <form action="{{ route('admin.pesanan.nasibox.index') }}" method="GET" class="w-full flex flex-col sm:flex-row gap-3">
                    <div class="relative min-w-[280px] flex-1">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Kode Pesanan / Pemesan / HP..." 
                               class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] outline-none text-xs font-medium transition-all bg-white">
                        <x-heroicon-o-magnifying-glass class="absolute left-3.5 top-2.5 text-gray-400 text-sm w-4 h-4 inline-block shrink-0" />
                    </div>
                    <select name="status" onchange="this.form.submit()" class="px-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] outline-none text-xs font-semibold bg-white min-w-[160px]">
                        <option value="all">Semua Status</option>
                        <option value="menunggu_dp" {{ request('status') == 'menunggu_dp' ? 'selected' : '' }}>Menunggu DP</option>
                        <option value="menunggu_konfirmasi" {{ request('status') == 'menunggu_konfirmasi' ? 'selected' : '' }}>Menunggu Konfirmasi</option>
                        <option value="terkonfirmasi" {{ request('status') == 'terkonfirmasi' ? 'selected' : '' }}>Terkonfirmasi (DP Lunas)</option>
                        <option value="diproses" {{ request('status') == 'diproses' ? 'selected' : '' }}>Diproses</option>
                        <option value="dikirim" {{ request('status') == 'dikirim' ? 'selected' : '' }}>Dikirim</option>
                        <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                        <option value="dibatalkan" {{ request('status') == 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </form>
            </x-slot:toolbar>

            <table class="w-full text-left border-collapse min-w-[1100px]">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-[11px] font-bold uppercase tracking-wider border-b border-gray-200 sticky top-0 z-10">
                        <th class="px-4 py-3.5">Kode & Tanggal Acara</th>
                        <th class="px-4 py-3.5">Pelanggan & Kontak</th>
                        <th class="px-4 py-3.5">Menu & Jumlah Box</th>
                        <th class="px-4 py-3.5">Tagihan & DP</th>
                        <th class="px-4 py-3.5">Status Pesanan</th>
                        <th class="px-4 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-xs">
                    @forelse($pesanans as $p)
                        <tr class="hover:bg-blue-50/20 transition-colors align-top">
                            <!-- 1. Kode & Tanggal -->
                            <td class="px-4 py-3.5">
                                <div class="font-mono font-extrabold text-gray-900 text-xs flex items-center gap-1">
                                    <i class="fa-solid fa-box text-orange-500 text-[11px]"></i>
                                    <span>{{ $p->kode_pesanan }}</span>
                                </div>
                                <div class="text-[11px] text-gray-600 font-semibold mt-1 flex items-center gap-1">
                                    <i class="fa-regular fa-calendar-check text-[10px] text-orange-600"></i>
                                    <span>Acara: {{ $p->tanggal_acara ? $p->tanggal_acara->format('d M Y') : '-' }}</span>
                                </div>
                                <div class="text-[10px] text-gray-400 font-medium mt-0.5">
                                    Dibuat: {{ $p->created_at ? $p->created_at->format('d M H:i') : '-' }}
                                </div>
                            </td>

                            <!-- 2. Pelanggan & Kontak -->
                            <td class="px-4 py-3.5">
                                <div class="font-bold text-gray-900 flex items-center gap-1.5">
                                    <div class="w-6 h-6 rounded-full bg-orange-100 text-orange-800 font-bold text-[10px] flex items-center justify-center flex-shrink-0">
                                        {{ strtoupper(substr($p->nama_pemesan, 0, 1)) }}
                                    </div>
                                    <span>{{ $p->nama_pemesan }}</span>
                                </div>
                                @if($p->phone)
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $p->phone) }}" target="_blank" class="text-[11px] text-emerald-600 hover:underline font-semibold mt-1 flex items-center gap-1">
                                        <i class="fa-brands fa-whatsapp"></i>{{ $p->phone }}
                                    </a>
                                @endif
                                @if($p->alamat)
                                    <div class="text-[10px] text-gray-500 mt-1 line-clamp-1" title="{{ $p->alamat }}">
                                        <i class="fa-solid fa-location-dot text-gray-400 mr-0.5"></i>{{ $p->alamat }}
                                    </div>
                                @endif
                            </td>

                            <!-- 3. Menu & Jumlah Box -->
                            <td class="px-4 py-3.5">
                                <div class="font-bold text-gray-900 text-xs">
                                    {{ $p->menu->nama ?? 'Paket Nasi Box' }}
                                </div>
                                <div class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-extrabold bg-orange-50 text-orange-800 border border-orange-200 mt-1">
                                    {{ $p->jumlah_box }} Box
                                </div>
                            </td>

                            <!-- 4. Tagihan & DP -->
                            <td class="px-4 py-3.5">
                                <div class="font-extrabold text-gray-900 text-sm">
                                    Rp {{ number_format($p->total_tagihan, 0, ',', '.') }}
                                </div>
                                @if($p->dp_amount)
                                    <div class="text-[10px] text-gray-500 font-medium mt-0.5">
                                        DP: <span class="font-bold text-orange-600">Rp {{ number_format($p->dp_amount, 0, ',', '.') }}</span>
                                    </div>
                                @endif
                            </td>

                            <!-- 5. Status Pesanan -->
                            <td class="px-4 py-3.5">
                                @if(in_array($p->status, ['menunggu_dp', 'menunggu_konfirmasi']))
                                    <x-ui.badge color="warning" dot>{{ ucwords(str_replace('_', ' ', $p->status)) }}</x-ui.badge>
                                @elseif($p->status == 'selesai' || $p->status == 'lunas')
                                    <x-ui.badge color="success" dot>Selesai</x-ui.badge>
                                @else
                                    <x-ui.badge color="primary" dot>{{ ucwords(str_replace('_', ' ', $p->status)) }}</x-ui.badge>
                                @endif
                            </td>

                            <!-- 6. Aksi -->
                            <td class="px-4 py-3.5 text-right">
                                <x-ui.button href="{{ route('admin.pesanan.nasibox.show', $p->id) }}" size="sm">
                                    <i class="fa-solid fa-eye mr-1"></i>Detail
                                </x-ui.button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center">
                                <x-ui.empty-state icon="fa-receipt" title="Tidak ada data pesanan nasi box" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-ui.data-table>
    </div>
</div>
@endsection