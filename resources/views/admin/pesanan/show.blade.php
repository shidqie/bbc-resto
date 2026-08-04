@extends('layouts.pos')

@section('title', 'Detail Pesanan')

@section('content')
<div class="flex flex-col h-full bg-white">
    {{-- Header --}}
    <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 shrink-0 bg-white sticky top-0 z-10 shadow-sm">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.pesanan.index') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-50 text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors border border-slate-200">
                <x-heroicon-o-arrow-left class="w-5 h-5" />
            </a>
            <div>
                <h3 class="font-bold text-gray-900 text-lg flex items-center gap-2">
                    {{ $pesanan->nomor_pesanan ?? 'DIN-'.$pesanan->id }}
                    @php
                        $color = 'gray';
                        if($pesanan->status_pesanan_id == 5) $color = 'emerald';
                        elseif($pesanan->status_pesanan_id == 1) $color = 'amber';
                        elseif($pesanan->status_pesanan_id == 6) $color = 'red';
                        else $color = 'blue';
                    @endphp
                    <span class="px-2.5 py-1 bg-{{$color}}-50 text-{{$color}}-700 border border-{{$color}}-200 rounded-lg text-xs font-extrabold uppercase tracking-wider shadow-sm">
                        {{ optional($pesanan->status_pesanan)->nama_status ?? 'Unknown' }}
                    </span>
                </h3>
                <p class="text-xs text-gray-500 mt-1">
                    <x-heroicon-o-clock class="mr-1 w-5 h-5" /> Dibuat: {{ \Carbon\Carbon::parse($pesanan->dibuat_pada)->format('d F Y, H:i') }} &bull; 
                    <span class="font-semibold text-gray-700 bg-gray-100 px-2 py-0.5 rounded-xl">{{ optional($pesanan->jenis_pesanan)->nama_jenis ?? '-' }}</span>
                </p>
            </div>
        </div>
    </div>

    {{-- Body --}}
    <div class="flex-1 overflow-y-auto p-6 space-y-6 bg-gray-50/50">
        
        {{-- Info Panel (Lebih Rinci) --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
            <div>
                <label class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-1 block"><x-heroicon-o-user class="mr-1 w-5 h-5" /> Pemesan</label>
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
                <p class="text-sm font-bold text-gray-900">{{ $nama }}</p>
            </div>
            
            <div>
                <label class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-1 block"><x-heroicon-o-users class="mr-1 w-5 h-5" /> Meja/Lokasi</label>
                <p class="text-sm font-bold text-gray-900">{{ $pesanan->meja ? 'Meja '.$pesanan->meja->nomor_meja : '-' }}</p>
            </div>
            
            <div>
                <label class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-1 block"><x-heroicon-o-sparkles class="mr-1 w-5 h-5" /> Kasir</label>
                <p class="text-sm font-bold text-gray-900">{{ optional($pesanan->kasir)->nama ?? '-' }}</p>
            </div>

            <div>
                <label class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-1 block"><x-heroicon-o-sparkles class="mr-1 w-5 h-5" /> Pelayan</label>
                <p class="text-sm font-bold text-gray-900">{{ optional($pesanan->pelayan)->nama ?? '-' }}</p>
            </div>
        </div>

        {{-- Jadwal Pesanan (Hanya Muncul jika ada) --}}
        @if($pesanan->jadwal_pesanan)
        <div class="bg-amber-50/50 p-4 rounded-xl border border-amber-200/60 shadow-sm flex items-start gap-3">
            <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center text-amber-600 shrink-0">
                <x-heroicon-o-sparkles class="w-6 h-6" />
            </div>
            <div>
                <h4 class="text-sm font-bold text-gray-900">Jadwal Acara / Pengiriman</h4>
                <p class="text-xs font-medium text-gray-600 mt-0.5">
                    {{ \Carbon\Carbon::parse($pesanan->jadwal_pesanan->tanggal_acara)->format('l, d F Y - H:i') }}
                </p>
                @if($pesanan->jadwal_pesanan->lokasi_acara)
                    <p class="text-xs text-gray-500 mt-1"><x-heroicon-o-sparkles class="mr-1 text-gray-400 w-5 h-5" /> {{ $pesanan->jadwal_pesanan->lokasi_acara }}</p>
                @endif
            </div>
        </div>
        @endif

        {{-- Daftar Item --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 bg-slate-50/50 flex justify-between items-center">
                <h4 class="text-sm font-bold text-gray-900"><x-heroicon-o-sparkles class="mr-1.5 text-gray-400 w-5 h-5" /> Daftar Item Pesanan</h4>
                <span class="text-xs font-bold bg-white border border-gray-200 px-2.5 py-1 rounded-xl text-gray-600">{{ $pesanan->detail_pesanan->count() }} Menu</span>
            </div>
            <div class="p-5 space-y-4">
                @forelse($pesanan->detail_pesanan as $item)
                    <div class="flex items-start justify-between group">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 bg-slate-100 rounded-full border border-slate-200 flex items-center justify-center font-black text-slate-500 text-xs shadow-sm">
                                {{ $item->jumlah }}x
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-gray-900">{{ optional($item->menu)->nama_menu ?? 'Menu Dihapus' }}</h4>
                                <p class="text-sm font-medium text-gray-500 mt-0.5">@ Rp{{ number_format($item->harga_satuan, 0, ',', '.') }}</p>
                                @if($item->catatan)
                                    <p class="text-xs text-amber-600 font-medium mt-1 bg-amber-50 px-2 py-0.5 rounded border border-amber-100 inline-block">
                                        <x-heroicon-o-sparkles class="mr-1 w-5 h-5" />{{ $item->catatan }}
                                    </p>
                                @endif
                            </div>
                        </div>
                        <div class="font-black text-gray-900 text-sm">
                            Rp{{ number_format($item->subtotal, 0, ',', '.') }}
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4 text-sm text-gray-400 font-medium">Tidak ada rincian item.</div>
                @endforelse
            </div>
            
            {{-- Ringkasan Biaya --}}
            <div class="px-5 py-4 bg-slate-50 border-t border-gray-200 space-y-2">
                @php
                    $subtotal = $pesanan->detail_pesanan->sum('subtotal');
                    $diskon = $pesanan->diskon ?? 0;
                    $pajak = $pesanan->pajak ?? 0;
                @endphp
                
                @if($diskon > 0 || $pajak > 0)
                <div class="flex justify-between items-center text-xs text-gray-500 font-medium">
                    <span>Subtotal</span>
                    <span>Rp{{ number_format($subtotal, 0, ',', '.') }}</span>
                </div>
                @endif
                
                @if($diskon > 0)
                <div class="flex justify-between items-center text-xs text-red-500 font-medium">
                    <span>Diskon</span>
                    <span>- Rp{{ number_format($diskon, 0, ',', '.') }}</span>
                </div>
                @endif
                
                @if($pajak > 0)
                <div class="flex justify-between items-center text-xs text-gray-500 font-medium">
                    <span>Pajak & Layanan</span>
                    <span>+ Rp{{ number_format($pajak, 0, ',', '.') }}</span>
                </div>
                @endif
                
                <div class="flex justify-between items-center pt-2 mt-2 border-t border-gray-200 border-dashed">
                    <span class="text-sm font-bold text-gray-700 uppercase tracking-wider">Total Keseluruhan</span>
                    <span class="text-xl font-black text-[#0D3024]">Rp{{ number_format($pesanan->total_tagihan, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        {{-- Catatan Tambahan (Bila ada, selain ekstrak nama) --}}
        @if(!empty($pesanan->catatan) && !preg_match('/^Pemesan:/', $pesanan->catatan) && !preg_match('/Self-Order QR/', $pesanan->catatan))
        <div class="bg-blue-50/50 p-4 rounded-xl border border-blue-100 shadow-sm flex items-start gap-3">
            <div class="text-blue-400 shrink-0 mt-0.5"><x-heroicon-o-sparkles class="w-5 h-5" /></div>
            <div>
                <h4 class="text-xs font-bold text-blue-800 uppercase tracking-wider mb-1">Catatan Pesanan</h4>
                <p class="text-sm text-blue-900 font-medium leading-relaxed">{{ $pesanan->catatan }}</p>
            </div>
        </div>
        @endif

        {{-- Riwayat Pembayaran Lebih Rinci --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 bg-slate-50/50 flex justify-between items-center">
                <h4 class="text-sm font-bold text-gray-900"><x-heroicon-o-wallet class="mr-1.5 text-gray-400 w-5 h-5" /> Status & Riwayat Pembayaran</h4>
                @php
                    $terbayar = $pesanan->pembayaran->sum('jumlah_bayar');
                    $sisa = $pesanan->total_tagihan - $terbayar;
                @endphp
                @if($sisa <= 0 && $pesanan->total_tagihan > 0)
                    <span class="text-xs font-black bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full border border-emerald-200">LUNAS</span>
                @else
                    <span class="text-xs font-black bg-red-100 text-red-700 px-3 py-1 rounded-full border border-red-200">SISA: Rp{{ number_format(max(0, $sisa), 0, ',', '.') }}</span>
                @endif
            </div>
            
            <div class="p-5 space-y-3">
                @forelse($pesanan->pembayaran as $bayar)
                    <div class="bg-white p-3.5 rounded-xl border border-slate-200 flex items-center justify-between shadow-sm hover:border-emerald-200 transition-colors group">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-emerald-50 group-hover:text-emerald-500 transition-colors">
                                <x-heroicon-o-sparkles class="w-5 h-5" />
                            </div>
                            <div>
                                <span class="block text-sm font-bold text-gray-900">{{ optional($bayar->metode_pembayaran)->nama_metode ?? 'CASH' }}</span>
                                <span class="block text-xs text-gray-500 font-medium mt-0.5">
                                    {{ \Carbon\Carbon::parse($bayar->dibayar_pada)->format('d M Y, H:i') }} &bull; {{ optional($bayar->jenis_pembayaran)->nama_jenis ?? 'Lunas' }}
                                </span>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="block text-sm font-black text-emerald-600">+ Rp{{ number_format($bayar->jumlah_bayar, 0, ',', '.') }}</span>
                            @if($bayar->diproses_oleh)
                                <span class="block text-xs text-gray-400 mt-1">oleh {{ optional($bayar->diproses_oleh_pengguna)->nama ?? 'Kasir' }}</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-6">
                        <div class="w-12 h-12 rounded-full bg-slate-50 flex items-center justify-center mx-auto mb-2 text-slate-300 text-xl">
                            <x-heroicon-o-sparkles class="w-5 h-5" />
                        </div>
                        <p class="text-sm font-medium text-gray-500">Belum ada pembayaran yang masuk</p>
                    </div>
                @endforelse
            </div>
            
            {{-- Progress Bar Pembayaran --}}
            @if($pesanan->total_tagihan > 0)
            <div class="px-5 py-4 border-t border-gray-100 bg-slate-50">
                <div class="flex justify-between text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                    <span>Progress Pembayaran</span>
                    <span>{{ min(100, round(($terbayar / max(1, $pesanan->total_tagihan)) * 100)) }}%</span>
                </div>
                <div class="w-full bg-slate-200 rounded-full h-2.5 overflow-hidden">
                    <div class="bg-emerald-500 h-2.5 rounded-full" style="width: {{ min(100, ($terbayar / max(1, $pesanan->total_tagihan)) * 100) }}%"></div>
                </div>
            </div>
            @endif
        </div>
        
    </div>
</div>
@endsection
