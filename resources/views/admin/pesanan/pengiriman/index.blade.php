{{-- 
    Halaman: Jadwal Pengiriman
    Catatan:
    - Tim pengiriman hanya satu, sehingga tidak ada pemilihan/penugasan kurir.
    - Pemilik hanya memantau jadwal dan status.
    - Tim Pengantaran memperbarui status pengiriman.
--}}

@extends('layouts.pos')

@php
    $isTimPengiriman = (int) Auth::user()->peran_id === 6;

    $pageTitle = $isTimPengiriman ? 'Pengiriman Saya' : 'Jadwal Pengiriman';
    $pageSubtitle = $isTimPengiriman
        ? 'Lihat tugas pengiriman Katering dan Nasi Box serta perbarui status pengiriman.'
        : 'Pantau jadwal pengiriman pesanan Katering dan Nasi Box.';

    /*
     * Ringkasan dihitung dari data $orders yang sudah dikirim controller.
     * Status backend yang dipakai:
     * 1 = Dijadwalkan
     * 2 = Siap Dikirim
     * 3 = Dalam Perjalanan
     * 4 = Diterima
     * 5 = Gagal Dikirim
     *
     * Pada UI, status 1 dan 2 digabung menjadi "Siap Dikirim"
     * agar alur pengguna lebih sederhana.
     */
    $orderCollection = method_exists($orders, 'getCollection')
        ? $orders->getCollection()
        : collect($orders);

    $totalPengiriman = method_exists($orders, 'total')
        ? $orders->total()
        : $orderCollection->count();

    $siapDikirim = $orderCollection->filter(function ($order) {
        $status = optional($order->pengiriman)->status_pengiriman_id;
        return in_array((int) $status, [1, 2], true);
    })->count();

    $dalamPengiriman = $orderCollection->filter(function ($order) {
        return (int) optional($order->pengiriman)->status_pengiriman_id === 3;
    })->count();

    $selesai = $orderCollection->filter(function ($order) {
        return (int) optional($order->pengiriman)->status_pengiriman_id === 4;
    })->count();
@endphp

@section('title', $pageTitle)

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800">
    <div class="w-full p-6 space-y-5">

        {{-- PAGE HEADER --}}
        <x-ui.page-header
            :title="$pageTitle"
            :subtitle="$pageSubtitle"
            :breadcrumbs="['Penjualan', $pageTitle]"
        >
            <x-slot:actions>
                <x-ui.button variant="primary" href="{{ route('admin.jadwal.index') }}">
                    Hari Ini
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.alert />

        {{-- RINGKASAN PENGANTARAN --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div class="bg-white rounded-xl border border-gray-200 px-4 py-3">
                <p class="text-sm font-medium text-gray-500">
                    Semua Pengiriman ({{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('d M') }})
                </p>
                <p class="text-xl font-bold text-gray-900 mt-1">{{ $totalPengiriman }}</p>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 px-4 py-3">
                <p class="text-sm font-medium text-gray-500">Siap Dikirim</p>
                <p class="text-xl font-bold text-amber-600 mt-1">{{ $siapDikirim }}</p>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 px-4 py-3">
                <p class="text-sm font-medium text-gray-500">Dalam Pengiriman</p>
                <p class="text-xl font-bold text-primary mt-1">{{ $dalamPengiriman }}</p>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 px-4 py-3">
                <p class="text-sm font-medium text-gray-500">Selesai</p>
                <p class="text-xl font-bold text-emerald-600 mt-1">{{ $selesai }}</p>
            </div>
        </div>

        {{-- TABLE --}}
        <x-ui.data-table>
            <x-slot:toolbar>
                <form
                    action="{{ route('admin.jadwal.index') }}"
                    method="GET"
                    class="flex items-center gap-2 w-full flex-wrap"
                >
                    <div class="w-full xl:max-w-xs shrink-0">
                        <x-ui.input
                            type="date"
                            name="date"
                            value="{{ $selectedDate }}"
                            onchange="this.form.submit()"
                        />
                    </div>

                    <x-search-input
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari kode pesanan atau nama pelanggan…"
                    />

                    {{--
                        Filter status memakai ID status pengiriman.
                        Pastikan controller memfilter melalui relasi pengiriman.status_pengiriman_id.
                    --}}
                    <x-ui.multi-select
                        name="status"
                        :options="[
                            '2' => 'Siap Dikirim',
                            '3' => 'Dalam Pengiriman',
                            '4' => 'Selesai',
                            '5' => 'Gagal Dikirim'
                        ]"
                        :selected="request('status')"
                        label="Status Pengiriman"
                        type="radio"
                    />
                </form>
            </x-slot:toolbar>

            <x-ui.table class="min-w-[1050px]">
                <x-ui.table.header>
                    <th class="px-4 py-3.5 text-left w-12">No.</th>
                    <th class="px-4 py-3.5 text-left">Jadwal</th>
                    <th class="px-4 py-3.5 text-left">Kode Pesanan</th>
                    <th class="px-4 py-3.5 text-left">Pelanggan</th>
                    <th class="px-4 py-3.5 text-left">Jenis Pesanan</th>
                    <th class="px-4 py-3.5 text-left">Detail Pesanan</th>
                    <th class="px-4 py-3.5 text-left">Alamat Pengiriman</th>
                    <th class="px-4 py-3.5 text-left">Status</th>
                    <th class="px-4 py-3.5 text-right">Aksi</th>
                </x-ui.table.header>

                <tbody class="divide-y divide-gray-100">
                    @forelse($orders as $i => $order)
                        @php
                            $jadwal = $order->jadwal_pesanan;
                            $pengiriman = $order->pengiriman;

                            $statusId = (int) optional($pengiriman)->status_pengiriman_id;

                            $tanggalPengiriman = optional($jadwal)->tanggal_pengiriman
                                ?? optional($jadwal)->tanggal_acara
                                ?? $selectedDate;

                            $waktuPengiriman = optional($jadwal)->waktu_pengiriman;

                            if (in_array($statusId, [1, 2], true)) {
                                $statusLabel = 'Siap Dikirim';
                                $statusClass = 'bg-amber-50 text-amber-800 border-amber-200';
                            } elseif ($statusId === 3) {
                                $statusLabel = 'Dalam Pengiriman';
                                $statusClass = 'bg-primary-soft text-primary border-primary/20';
                            } elseif ($statusId === 4) {
                                $statusLabel = 'Selesai';
                                $statusClass = 'bg-emerald-50 text-emerald-800 border-emerald-200';
                            } elseif ($statusId === 5) {
                                $statusLabel = 'Gagal Dikirim';
                                $statusClass = 'bg-red-50 text-red-800 border-red-200';
                            } else {
                                $statusLabel = 'Belum Dijadwalkan';
                                $statusClass = 'bg-gray-50 text-gray-600 border-gray-200';
                            }

                            $kodePesanan = $order->id_pesanan
                                ?? $order->kode_pesanan
                                ?? '-';

                            $namaPelanggan = optional($jadwal)->nama_penerima ?? '-';
                            $teleponPelanggan = optional($jadwal)->nomor_telepon_penerima ?? '-';
                            $alamatPengiriman = optional($jadwal)->alamat_pengiriman ?? '-';

                            $jenisPesanan = (int) $order->jenis_pesanan_id === 2
                                ? 'Katering'
                                : 'Nasi Box';
                        @endphp

                        <x-ui.table.row class="align-top">
                            <td class="px-4 py-4 text-sm text-gray-500 font-medium">
                                {{ $i + 1 }}
                            </td>

                            {{-- JADWAL --}}
                            <td class="px-4 py-4 whitespace-nowrap">
                                <p class="font-semibold text-gray-900 text-sm">
                                    {{ $tanggalPengiriman
                                        ? \Carbon\Carbon::parse($tanggalPengiriman)->translatedFormat('d M Y')
                                        : '-'
                                    }}
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $waktuPengiriman
                                        ? \Carbon\Carbon::parse($waktuPengiriman)->format('H:i')
                                        : 'Jam belum ditentukan'
                                    }}
                                </p>
                            </td>

                            {{-- KODE PESANAN --}}
                            <td class="px-4 py-4">
                                <p class="font-mono text-xs font-semibold text-primary">
                                    {{ $kodePesanan }}
                                </p>
                            </td>

                            {{-- PELANGGAN --}}
                            <td class="px-4 py-4">
                                <p class="font-semibold text-gray-900 text-xs">
                                    {{ $namaPelanggan }}
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $teleponPelanggan }}
                                </p>
                            </td>

                            {{-- JENIS PESANAN --}}
                            <td class="px-4 py-4">
                                @if((int) $order->jenis_pesanan_id === 2)
                                    <x-ui.badge color="primary" size="sm">Katering</x-ui.badge>
                                @else
                                    <span class="inline-flex text-xs font-semibold px-2 py-1 rounded-lg bg-purple-50 text-purple-700">
                                        Nasi Box
                                    </span>
                                @endif
                            </td>

                            {{-- DETAIL PESANAN --}}
                            <td class="px-4 py-4 max-w-[200px]">
                                <ul class="list-disc list-inside text-xs text-gray-700 leading-relaxed">
                                    @forelse($order->detail_pesanan as $detail)
                                        <li class="truncate" title="{{ $detail->menu->nama_menu ?? 'Menu' }} ({{ $detail->jumlah }}x)">
                                            <span class="font-medium">{{ $detail->menu->nama_menu ?? 'Menu' }}</span>
                                            <span class="text-gray-500 ml-1">({{ $detail->jumlah }}x)</span>
                                        </li>
                                    @empty
                                        <li class="text-gray-400 italic">Tidak ada detail</li>
                                    @endforelse
                                </ul>
                            </td>

                            {{-- ALAMAT --}}
                            <td class="px-4 py-4 max-w-[280px]">
                                <p class="text-xs text-gray-700 leading-relaxed">
                                    {{ $alamatPengiriman }}
                                </p>
                            </td>

                            {{-- STATUS / AKSI STATUS --}}
                            <td class="px-4 py-4 min-w-[170px]">
                                <span class="inline-flex items-center rounded-lg border px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>

                                {{-- Hanya Tim Pengantaran yang dapat memperbarui proses pengiriman --}}
                                @if($isTimPengiriman && $pengiriman)
                                    <div class="mt-2 space-y-1.5">
                                        @if(in_array($statusId, [1, 2], true))
                                            <form
                                                action="{{ route('admin.jadwal.update-pengiriman-status', $pengiriman->id) }}"
                                                method="POST"
                                            >
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status_pengiriman_id" value="3">

                                                <button
                                                    type="submit"
                                                    class="w-full inline-flex justify-center items-center rounded-lg bg-primary px-3 py-1.5 text-xs font-semibold text-white hover:bg-primary/90 transition"
                                                >
                                                    Mulai Pengiriman
                                                </button>
                                            </form>
                                        @elseif($statusId === 3)
                                            <div x-data="{ showModal: false }">
                                                <button
                                                    @click="showModal = true"
                                                    type="button"
                                                    class="w-full inline-flex justify-center items-center rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700 transition"
                                                >
                                                    Selesaikan Pengiriman
                                                </button>

                                                <div x-show="showModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                                                    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                                        <div x-show="showModal" x-transition.opacity class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                                                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                                                        <div x-show="showModal" x-transition.scale.origin.bottom class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                                            <form action="{{ route('admin.jadwal.update-pengiriman-status', $pengiriman->id) }}" method="POST" enctype="multipart/form-data">
                                                                @csrf
                                                                @method('PATCH')
                                                                <input type="hidden" name="status_pengiriman_id" value="4">
                                                                
                                                                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                                                    <div class="sm:flex sm:items-start">
                                                                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-emerald-100 sm:mx-0 sm:h-10 sm:w-10">
                                                                            <x-heroicon-o-camera class="h-6 w-6 text-emerald-600" />
                                                                        </div>
                                                                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                                                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Upload Bukti Pengiriman</h3>
                                                                            <div class="mt-2">
                                                                                <p class="text-sm text-gray-500">Silakan unggah foto bukti pengiriman/penerimaan untuk menyelesaikan pesanan ini.</p>
                                                                                <div class="mt-4">
                                                                                    <input type="file" name="foto_bukti" accept="image/*" required class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none file:mr-4 file:py-2 file:px-4 file:rounded-l-lg file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                                                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-emerald-600 text-base font-medium text-white hover:bg-emerald-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                                                                        Upload & Selesai
                                                                    </button>
                                                                    <button type="button" @click="showModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                                                        Batal
                                                                    </button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <form
                                                action="{{ route('admin.jadwal.update-pengiriman-status', $pengiriman->id) }}"
                                                method="POST"
                                            >
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status_pengiriman_id" value="5">

                                                <button
                                                    type="submit"
                                                    onclick="window.confirmDialog({ title: 'Tandai Gagal Dikirim', name: 'Tandai pengiriman ini sebagai gagal dikirim?', message: 'Status pengiriman akan diubah menjadi gagal dikirim.', form: this.closest('form'), confirmText: 'Ya, Gagal', cancelText: 'Batal', type: 'danger' })"
                                                    class="w-full text-xs font-medium text-red-600 hover:text-red-700"
                                                >
                                                    Gagal Dikirim
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                @endif
                            </td>

                            {{-- AKSI --}}
                            <td class="px-4 py-4 text-right">
                                <x-ui.action-button
                                    href="{{ (int) $order->jenis_pesanan_id === 2
                                        ? url('/admin/pesanan/catering/' . $order->id)
                                        : url('/admin/pesanan/nasi-box/' . $order->id)
                                    }}"
                                    title="Lihat Detail Pesanan"
                                >
                                    <x-heroicon-o-eye class="w-4 h-4" />
                                </x-ui.action-button>
                            </td>
                        </x-ui.table.row>
                    @empty
                        <tr>
                            <td colspan="9">
                                <x-ui.empty-state
                                    icon="clock"
                                    title="Belum ada jadwal pengiriman"
                                    message="Tidak terdapat pesanan Katering atau Nasi Box yang dijadwalkan untuk diantar pada tanggal yang dipilih."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>
        </x-ui.data-table>

    </div>
</div>
@endsection