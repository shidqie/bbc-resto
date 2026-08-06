@extends('layouts.pos')

@section('title', 'Pembayaran')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800">
    <div class="w-full p-6 space-y-5">

        {{-- PAGE HEADER --}}
        <x-ui.page-header
            title="Pembayaran"
            subtitle="Verifikasi bukti pembayaran manual dari pelanggan."
            :breadcrumbs="['Penjualan', 'Pembayaran']">
        </x-ui.page-header>

        <x-ui.alert />

        {{-- Table with integrated toolbar --}}
        <x-ui.data-table :paginator="$pembayarans">
            <x-slot:toolbar>
                <form action="{{ route('admin.verifikasi_pembayaran.index') }}" method="GET" class="flex items-center gap-2 w-full flex-wrap">
                    <x-search-input name="search" value="{{ request('search') }}" placeholder="Cari Kode Pembayaran / No. Pesanan…" />

                    {{-- Status toggle --}}
                    <div class="flex items-center gap-1 bg-gray-100 p-1 rounded-xl shrink-0">
                        <a href="{{ route('admin.verifikasi_pembayaran.index', ['status' => 'menunggu_verifikasi', 'search' => request('search')]) }}"
                           class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ $status === 'menunggu_verifikasi' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                           Menunggu Verifikasi
                        </a>
                        <a href="{{ route('admin.verifikasi_pembayaran.index', ['status' => 'riwayat', 'search' => request('search')]) }}"
                           class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ $status === 'riwayat' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                           Riwayat
                        </a>
                    </div>

                    @if(request()->hasAny(['search']) || $status !== 'menunggu_verifikasi')
                        <a href="{{ route('admin.verifikasi_pembayaran.index') }}" class="text-xs font-medium text-red-500 hover:text-red-700 px-2 py-2 rounded-lg hover:bg-red-50 transition-colors shrink-0">Reset</a>
                    @endif
                </form>
            </x-slot:toolbar>

            <x-ui.table class="min-w-[1000px]">
                <x-ui.table.header>
                    <th class="px-4 py-3.5 text-left w-12">No</th>
                    <th class="px-4 py-3.5 text-left">Kode Pesanan</th>
                    <th class="px-4 py-3.5 text-left">Tanggal Pesan</th>
                    <th class="px-4 py-3.5 text-left">Tanggal Acara</th>
                    <th class="px-4 py-3.5 text-right">Total</th>
                    <th class="px-4 py-3.5 text-center">Bukti Pembayaran</th>
                    <th class="px-4 py-3.5 text-center">Status</th>
                    <th class="px-4 py-3.5 text-center">Aksi</th>
                </x-ui.table.header>
                <tbody class="divide-y divide-gray-100">
                    @forelse($pembayarans as $index => $pembayaran)
                    <x-ui.table.row>
                        <td class="px-4 py-4 text-sm text-gray-500 font-medium">
                            {{ ($pembayarans->firstItem() ?? 1) + $index }}
                        </td>
                        <td class="px-4 py-4">
                            <span class="font-mono text-xs font-bold text-gray-900">{{ optional($pembayaran->pesanan)->nomor_pesanan ?? 'DIN-'.optional($pembayaran->pesanan)->id ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-4 text-sm text-gray-700">
                            {{ \Carbon\Carbon::parse($pembayaran->dibuat_pada)->translatedFormat('d M Y, H.i') }} WIB
                        </td>
                        <td class="px-4 py-4 text-sm text-gray-700">
                            {{ optional(optional($pembayaran->pesanan)->jadwal_pesanan)->tanggal_acara ? \Carbon\Carbon::parse(optional(optional($pembayaran->pesanan)->jadwal_pesanan)->tanggal_acara)->translatedFormat('d M Y, H.i') . ' WIB' : '-' }}
                        </td>
                        <td class="px-4 py-4 text-right font-bold text-gray-900">
                            Rp{{ number_format($pembayaran->jumlah_dibayar, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-4 text-center">
                            @if($pembayaran->bukti_pembayaran)
                                <a href="{{ Storage::url($pembayaran->bukti_pembayaran) }}" target="_blank" class="text-blue-500 hover:underline text-xs font-medium">Lihat Bukti</a>
                            @else
                                <span class="text-gray-400 text-xs">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-center">
                            @if($pembayaran->status_verifikasi === 'menunggu_verifikasi')
                                <x-ui.badge color="warning" size="sm" dot>Menunggu</x-ui.badge>
                            @elseif($pembayaran->status_verifikasi === 'diterima')
                                <x-ui.badge color="success" size="sm" dot>Diterima</x-ui.badge>
                            @else
                                <x-ui.badge color="danger" size="sm" dot>Ditolak</x-ui.badge>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-center">
                            <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                @if($pembayaran->status_verifikasi === 'menunggu_verifikasi')
                                    <form action="{{ route('admin.verifikasi_pembayaran.process', $pembayaran->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="action" value="terima">
                                        <button type="submit" onclick="return confirm('Verifikasi pembayaran ini?')" class="bg-emerald-500 hover:bg-emerald-600 text-white text-xs px-2 py-1.5 rounded flex items-center gap-1 shadow-sm font-medium">
                                            <x-heroicon-o-check class="w-3.5 h-3.5" /> Verifikasi
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.verifikasi_pembayaran.process', $pembayaran->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="action" value="tolak">
                                        <button type="submit" onclick="return confirm('Batalkan pembayaran ini?')" class="bg-red-500 hover:bg-red-600 text-white text-xs px-2 py-1.5 rounded flex items-center gap-1 shadow-sm font-medium">
                                            <x-heroicon-o-x-mark class="w-3.5 h-3.5" /> Batal
                                        </button>
                                    </form>
                                @else
                                    <x-ui.action-button x-data="" @click="$dispatch('open-modal', 'modal-verifikasi-{{ $pembayaran->id }}')" title="Lihat Detail">
                                        <x-heroicon-o-eye class="w-4 h-4" />
                                    </x-ui.action-button>
                                @endif
                            </div>
                        </td>
                    </x-ui.table.row>
                    @empty
                    <x-empty-state icon="credit-card" title="Tidak ada pembayaran" message="Tidak ada data pembayaran yang sesuai dengan filter." :colspan="8" />
                    @endforelse
                </tbody>
            </x-ui.table>
        </x-ui.data-table>

    </div>
</div>

{{-- MODALS: DETAIL & VERIFIKASI --}}
@foreach($pembayarans as $pembayaran)
    <x-ui.modal name="modal-verifikasi-{{ $pembayaran->id }}" title="Detail Verifikasi: {{ $pembayaran->kode_pembayaran }}">
        <div class="space-y-4 text-sm">
            <div class="grid grid-cols-2 gap-4 bg-gray-50 p-4 rounded-xl">
                <div>
                    <span class="block text-xs text-gray-500 font-bold mb-1">Total Tagihan</span>
                    <span class="font-black text-gray-900 text-base">Rp {{ number_format($pembayaran->jumlah_tagihan, 0, ',', '.') }}</span>
                </div>
                <div>
                    <span class="block text-xs text-gray-500 font-bold mb-1">Total Dibayar (Klaim)</span>
                    <span class="font-black text-gray-900 text-base">Rp {{ number_format($pembayaran->jumlah_dibayar, 0, ',', '.') }}</span>
                </div>
            </div>

            <div>
                <span class="block text-xs text-gray-500 font-bold mb-2">Bukti Pembayaran</span>
                @if($pembayaran->bukti_pembayaran)
                    <div class="border border-gray-200 rounded-xl overflow-hidden bg-gray-50 flex items-center justify-center p-2">
                        <img src="{{ Storage::url($pembayaran->bukti_pembayaran) }}" alt="Bukti Pembayaran" class="max-h-96 object-contain rounded-lg">
                    </div>
                    <div class="mt-2 text-right">
                        <a href="{{ Storage::url($pembayaran->bukti_pembayaran) }}" target="_blank" class="text-xs font-bold text-blue-600 hover:underline">
                            Buka gambar penuh &rarr;
                        </a>
                    </div>
                @else
                    <div class="p-4 bg-gray-50 border border-gray-200 rounded-xl text-center text-gray-500 font-medium">
                        Tidak ada bukti yang diunggah
                    </div>
                @endif
            </div>

            @if($pembayaran->status_verifikasi === 'menunggu_verifikasi')
                <form action="{{ route('admin.verifikasi_pembayaran.process', $pembayaran->id) }}" method="POST" class="mt-6 border-t border-gray-100 pt-6">
                    @csrf

                    <div class="mb-4">
                        <label class="block text-xs font-bold text-gray-700 mb-1">Catatan (Opsional, wajib jika ditolak)</label>
                        <textarea name="catatan" rows="2" class="w-full border-gray-200 rounded-xl text-sm focus:border-[#0D3024] focus:ring focus:ring-[#0D3024]/10"></textarea>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" name="action" value="tolak" class="flex-1 py-2.5 bg-red-50 text-red-700 border border-red-200 font-bold rounded-xl hover:bg-red-100 transition-colors">
                            Tolak Pembayaran
                        </button>
                        <button type="submit" name="action" value="terima" class="flex-1 py-2.5 bg-[#0D3024] text-white font-bold rounded-xl shadow-sm hover:bg-[#0a1f17] transition-colors">
                            Terima & Selesai
                        </button>
                    </div>
                </form>
            @else
                <div class="mt-6 border-t border-gray-100 pt-6 space-y-2">
                    <p class="text-xs text-gray-500">Status saat ini:</p>
                    <div class="font-bold {{ $pembayaran->status_verifikasi === 'diterima' ? 'text-emerald-600' : 'text-red-600' }} uppercase">
                        {{ $pembayaran->status_verifikasi }}
                    </div>
                    @if($pembayaran->catatan_verifikasi)
                        <div class="p-3 bg-gray-50 border border-gray-200 rounded-lg text-sm mt-2">
                            <span class="block text-[10px] font-bold text-gray-400 mb-1">Catatan Verifikator</span>
                            {{ $pembayaran->catatan_verifikasi }}
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </x-ui.modal>
@endforeach

@endsection
