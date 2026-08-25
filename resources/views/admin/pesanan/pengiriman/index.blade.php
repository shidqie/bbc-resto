{{-- 
    Halaman: Jadwal Pengiriman / Pengiriman Saya
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
<div x-data="{ 
    showUploadModal: false, 
    showDetailModal: false,
    activeDetail: null,
    actionUrl: '', 
    kodePesanan: '',
    photoPreview: null,
    fileName: '',
    openDetailModal(data) {
        this.activeDetail = data;
        this.showDetailModal = true;
    },
    openUploadModal(url, kode) {
        this.actionUrl = url;
        this.kodePesanan = kode;
        this.clearPhoto();
        this.showUploadModal = true;
    },
    handleFile(event) {
        const file = event.target.files[0];
        if (file) {
            try {
                const dt = new DataTransfer();
                dt.items.add(file);
                if (this.$refs.mainInput) {
                    this.$refs.mainInput.files = dt.files;
                }
            } catch (e) {}

            const reader = new FileReader();
            reader.onload = (e) => {
                this.photoPreview = e.target.result;
                this.fileName = file.name;
            };
            reader.readAsDataURL(file);
        }
    },
    clearPhoto() {
        this.photoPreview = null;
        this.fileName = '';
        if (this.$refs.mainInput) this.$refs.mainInput.value = '';
        if (this.$refs.cameraInput) this.$refs.cameraInput.value = '';
        if (this.$refs.galleryInput) this.$refs.galleryInput.value = '';
    }
}" class="flex-1 bg-gray-50/70 text-gray-800 font-sans min-h-screen">
    <div class="w-full p-6 space-y-6">

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

        {{-- RINGKASAN PENGANTARAN (ELEGANT STAT CARDS) --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Semua Pengiriman --}}
            <div class="bg-white rounded-2xl border border-gray-200/80 p-4 shadow-2xs flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        Semua Pengiriman
                    </p>
                    <p class="text-2xl font-black text-gray-900 mt-1">{{ $totalPengiriman }}</p>
                    <p class="text-[11px] text-gray-400 mt-0.5">{{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('d M Y') }}</p>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-gray-50 border border-gray-100 text-gray-700 flex items-center justify-center">
                    <x-heroicon-o-calendar class="w-5 h-5" />
                </div>
            </div>

            {{-- Siap Dikirim --}}
            <div class="bg-white rounded-2xl border border-gray-200/80 p-4 shadow-2xs flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-amber-700 uppercase tracking-wider">
                        Siap Dikirim
                    </p>
                    <p class="text-2xl font-black text-amber-600 mt-1">{{ $siapDikirim }}</p>
                    <p class="text-[11px] text-gray-400 mt-0.5">Menunggu kurir</p>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-amber-50 border border-amber-100 text-amber-600 flex items-center justify-center">
                    <x-heroicon-o-cube class="w-5 h-5" />
                </div>
            </div>

            {{-- Dalam Pengiriman --}}
            <div class="bg-white rounded-2xl border border-gray-200/80 p-4 shadow-2xs flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-blue-700 uppercase tracking-wider">
                        Dalam Pengiriman
                    </p>
                    <p class="text-2xl font-black text-primary mt-1">{{ $dalamPengiriman }}</p>
                    <p class="text-[11px] text-gray-400 mt-0.5">Sedang menuju lokasi</p>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-blue-50 border border-blue-100 text-primary flex items-center justify-center">
                    <x-heroicon-o-truck class="w-5 h-5" />
                </div>
            </div>

            {{-- Selesai --}}
            <div class="bg-white rounded-2xl border border-gray-200/80 p-4 shadow-2xs flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-emerald-700 uppercase tracking-wider">
                        Selesai
                    </p>
                    <p class="text-2xl font-black text-emerald-600 mt-1">{{ $selesai }}</p>
                    <p class="text-[11px] text-gray-400 mt-0.5">Berhasil diantar</p>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center">
                    <x-heroicon-o-check-circle class="w-5 h-5" />
                </div>
            </div>
        </div>

        {{-- TABLE CONTAINER --}}
        <x-ui.data-table>
            <x-slot:toolbar>
                <form
                    action="{{ route('admin.jadwal.index') }}"
                    method="GET"
                    class="flex items-center gap-3 w-full flex-wrap"
                >
                    <div class="w-full sm:w-auto xl:max-w-xs shrink-0">
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
                        placeholder="Cari kode pesanan atau nama konsumen…"
                    />

                    {{-- Filter Status --}}
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

            <x-ui.table class="min-w-[950px]">
                <x-ui.table.header>
                    <th class="px-4 py-3.5 text-left w-12 text-[11px] font-bold uppercase tracking-wider text-gray-500">No</th>
                    <th class="px-4 py-3.5 text-left text-[11px] font-bold uppercase tracking-wider text-gray-500">Jadwal Antar</th>
                    <th class="px-4 py-3.5 text-left text-[11px] font-bold uppercase tracking-wider text-gray-500">Kode Pesanan</th>
                    <th class="px-4 py-3.5 text-left text-[11px] font-bold uppercase tracking-wider text-gray-500">Konsumen</th>
                    <th class="px-4 py-3.5 text-left text-[11px] font-bold uppercase tracking-wider text-gray-500">Jenis</th>
                    <th class="px-4 py-3.5 text-left text-[11px] font-bold uppercase tracking-wider text-gray-500">Pesanan</th>
                    <th class="px-4 py-3.5 text-left text-[11px] font-bold uppercase tracking-wider text-gray-500">Status</th>
                    <th class="px-4 py-3.5 text-center text-[11px] font-bold uppercase tracking-wider text-gray-500">Aksi</th>
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

                            $waktuPengiriman = optional($jadwal)->waktu_pengiriman ?? optional($jadwal)->waktu_acara;

                            if (in_array($statusId, [1, 2], true)) {
                                $statusLabel = 'Siap Dikirim';
                                $statusClass = 'bg-amber-50 text-amber-800 border-amber-200/90';
                                $statusDot = 'bg-amber-500';
                            } elseif ($statusId === 3) {
                                $statusLabel = 'Dalam Pengiriman';
                                $statusClass = 'bg-blue-50 text-blue-800 border-blue-200/90';
                                $statusDot = 'bg-blue-500 animate-pulse';
                            } elseif ($statusId === 4) {
                                $statusLabel = 'Selesai';
                                $statusClass = 'bg-emerald-50 text-emerald-800 border-emerald-200/90';
                                $statusDot = 'bg-emerald-500';
                            } elseif ($statusId === 5) {
                                $statusLabel = 'Gagal Dikirim';
                                $statusClass = 'bg-rose-50 text-rose-800 border-rose-200/90';
                                $statusDot = 'bg-rose-500';
                            } else {
                                $statusLabel = 'Belum Dijadwalkan';
                                $statusClass = 'bg-gray-50 text-gray-600 border-gray-200';
                                $statusDot = 'bg-gray-400';
                            }

                            $kodePesanan = $order->id_pesanan
                                ?? $order->kode_pesanan
                                ?? ('ORD-' . $order->id);

                            $namaKonsumen = optional($jadwal)->nama_penerima ?? ($order->nama_konsumen ?? ($order->pelanggan->nama ?? '-'));
                            $teleponRaw = optional($jadwal)->nomor_telepon_penerima ?? ($order->pelanggan->nomor_telepon ?? null);
                            $teleponKonsumen = $teleponRaw ? \App\Support\WhatsAppNumber::formatForDisplay($teleponRaw) : '-';
                            $alamatPengiriman = optional($jadwal)->alamat_pengiriman ?? '-';

                            $isKatering = (int) $order->jenis_pesanan_id === 2;
                            $jenisPesanan = $isKatering ? 'Katering' : 'Nasi Box';
                            $satuanPorsi = $isKatering ? 'porsi' : 'box';

                            // Format Ringkasan Pesanan (e.g. Paket A – 50 porsi)
                            $pesananItems = [];
                            $daftarKomponen = [];

                            foreach ($order->detail_pesanan as $detail) {
                                $namaMenu = $detail->menu->nama_menu ?? ($detail->menu->nama ?? 'Menu');
                                $qty = $detail->jumlah;
                                $pesananItems[] = "{$namaMenu} – {$qty} {$satuanPorsi}";

                                if ($detail->pilihan_pesanan_catering && $detail->pilihan_pesanan_catering->isNotEmpty()) {
                                    foreach ($detail->pilihan_pesanan_catering as $pc) {
                                        $kompNama = $pc->pilihan_komponen_paket?->menu?->nama_menu ?? null;
                                        if ($kompNama) {
                                            $daftarKomponen[] = $kompNama;
                                        }
                                    }
                                }
                            }
                            $pesananSummary = !empty($pesananItems) ? implode(', ', $pesananItems) : '-';

                            // Data Payload untuk Modal Detail
                            $detailData = [
                                'id' => $order->id,
                                'kode_pesanan' => $kodePesanan,
                                'jenis_pesanan' => $jenisPesanan,
                                'is_catering' => $isKatering,
                                'paket_nama' => $order->detail_pesanan->first()?->menu?->nama_menu ?? '-',
                                'jumlah_porsi' => ($order->detail_pesanan->sum('jumlah') ?: 0) . ' ' . $satuanPorsi,
                                'daftar_items' => $order->detail_pesanan->map(function($d) use ($satuanPorsi) {
                                    return [
                                        'nama' => $d->menu?->nama_menu ?? '-',
                                        'jumlah' => $d->jumlah . ' ' . $satuanPorsi,
                                    ];
                                })->values(),
                                'daftar_komponen' => $daftarKomponen,
                                'nama_konsumen' => $namaKonsumen,
                                'telepon_konsumen' => $teleponKonsumen,
                                'telepon_raw' => $teleponRaw,
                                'tanggal_pengiriman' => $tanggalPengiriman ? \Carbon\Carbon::parse($tanggalPengiriman)->translatedFormat('d M Y') : '-',
                                'jam_pengiriman' => ($waktuPengiriman && $waktuPengiriman !== '00:00:00') ? \Carbon\Carbon::parse($waktuPengiriman)->format('H.i') . ' WIB' : 'Jam belum ditentukan',
                                'alamat_lengkap' => $alamatPengiriman,
                                'biaya_pengiriman' => (optional($pengiriman)->biaya_pengiriman !== null && (float) optional($pengiriman)->biaya_pengiriman > 0)
                                    ? 'Rp ' . number_format(optional($pengiriman)->biaya_pengiriman, 0, ',', '.')
                                    : 'Gratis Ongkir',
                                'jarak_pengiriman' => optional($pengiriman)->jarak_pengiriman ? optional($pengiriman)->jarak_pengiriman . ' km' : null,
                                'catatan' => optional($jadwal)->catatan ?? ($order->catatan ?? '-'),
                                'status_label' => $statusLabel,
                                'status_class' => $statusClass,
                                'status_dot' => $statusDot,
                                'foto_bukti' => optional($pengiriman)->foto_bukti_pengiriman ? asset('storage/' . optional($pengiriman)->foto_bukti_pengiriman) : null,
                                'url_pesanan' => $isKatering
                                    ? url('/admin/pesanan/catering/' . $order->id)
                                    : url('/admin/pesanan/nasi-box/' . $order->id),
                            ];
                        @endphp

                        <x-ui.table.row class="align-middle hover:bg-gray-50/60 transition-colors">
                            {{-- NO --}}
                            <td class="px-4 py-4 text-xs text-gray-500 font-bold">
                                {{ $i + 1 }}
                            </td>

                            {{-- JADWAL ANTAR --}}
                            <td class="px-4 py-4 whitespace-nowrap">
                                <span class="font-semibold text-gray-900 text-xs">
                                    {{ $tanggalPengiriman ? \Carbon\Carbon::parse($tanggalPengiriman)->translatedFormat('d M Y') : '-' }}@if($waktuPengiriman && $waktuPengiriman !== '00:00:00'), {{ \Carbon\Carbon::parse($waktuPengiriman)->format('H.i') }} WIB @endif
                                </span>
                            </td>

                            {{-- KODE PESANAN --}}
                            <td class="px-4 py-4 whitespace-nowrap">
                                <span class="font-mono text-xs font-bold text-gray-900 bg-gray-100 px-2.5 py-1 rounded-lg border border-gray-200/80 inline-block">
                                    {{ $kodePesanan }}
                                </span>
                            </td>

                            {{-- KONSUMEN --}}
                            <td class="px-4 py-4 whitespace-nowrap">
                                <p class="font-bold text-gray-900 text-xs truncate max-w-[180px]" title="{{ $namaKonsumen }}">
                                    {{ $namaKonsumen }}
                                </p>
                            </td>

                            {{-- JENIS PESANAN --}}
                            <td class="px-4 py-4 whitespace-nowrap">
                                @if($isKatering)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200/80">
                                        Katering
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-purple-50 text-purple-800 border border-purple-200/80">
                                        Nasi Box
                                    </span>
                                @endif
                            </td>

                            {{-- PESANAN --}}
                            <td class="px-4 py-4 max-w-[220px]">
                                <p class="text-xs font-bold text-gray-900 leading-snug">
                                    {{ $pesananSummary }}
                                </p>
                            </td>

                            {{-- STATUS PENGIRIMAN --}}
                            <td class="px-4 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-bold {{ $statusClass }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $statusDot }}"></span>
                                    <span>{{ $statusLabel }}</span>
                                </span>
                            </td>

                            {{-- AKSI --}}
                            <td class="px-4 py-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5">
                                    @if($isTimPengiriman && $pengiriman)
                                        @if(in_array($statusId, [1, 2], true))
                                            {{-- Mulai Pengiriman --}}
                                            <form action="{{ route('admin.jadwal.update-pengiriman-status', $pengiriman->id) }}" method="POST" class="inline-block">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status_pengiriman_id" value="3">

                                                <button
                                                    type="submit"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-primary hover:bg-primary-container text-white font-bold text-xs shadow-xs transition active:scale-[0.98] cursor-pointer"
                                                    title="Mulai Pengiriman"
                                                >
                                                    <x-heroicon-o-truck class="w-3.5 h-3.5" />
                                                    <span>Mulai</span>
                                                </button>
                                            </form>
                                        @elseif($statusId === 3)
                                            {{-- Selesaikan Pengiriman --}}
                                            <button
                                                type="button"
                                                @click="openUploadModal('{{ route('admin.jadwal.update-pengiriman-status', $pengiriman->id) }}', '{{ $kodePesanan }}')"
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition active:scale-[0.98] cursor-pointer"
                                                title="Selesaikan Pengiriman"
                                            >
                                                <x-heroicon-o-check-circle class="w-3.5 h-3.5" />
                                                <span>Selesai</span>
                                            </button>

                                            {{-- Gagal Dikirim --}}
                                            <form action="{{ route('admin.jadwal.update-pengiriman-status', $pengiriman->id) }}" method="POST" class="inline-block">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status_pengiriman_id" value="5">

                                                <button
                                                    type="submit"
                                                    onclick="window.confirmDialog({ title: 'Tandai Gagal Dikirim', name: 'Tandai pengiriman ini sebagai gagal dikirim?', message: 'Status pengiriman akan diubah menjadi gagal dikirim.', form: this.closest('form'), confirmText: 'Ya, Gagal', cancelText: 'Batal', type: 'danger' })"
                                                    class="inline-flex items-center gap-1 px-2 py-1.5 rounded-xl text-rose-600 hover:text-rose-800 hover:bg-rose-50 border border-transparent hover:border-rose-200 font-bold text-xs transition active:scale-[0.98] cursor-pointer"
                                                    title="Tandai Gagal Dikirim"
                                                >
                                                    <x-heroicon-o-x-circle class="w-3.5 h-3.5" />
                                                    <span>Gagal</span>
                                                </button>
                                            </form>
                                        @endif
                                    @endif

                                    {{-- Tombol Detail (Eye Icon) Membuka Modal Detail --}}
                                    <button
                                        type="button"
                                        @click="openDetailModal({{ json_encode($detailData) }})"
                                        class="p-2 rounded-xl text-gray-500 hover:text-gray-900 hover:bg-gray-100 border border-transparent hover:border-gray-200 transition cursor-pointer"
                                        title="Lihat Detail Pengiriman"
                                    >
                                        <x-heroicon-o-eye class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </x-ui.table.row>
                    @empty
                        <tr>
                            <td colspan="8">
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

    {{-- MODAL UPLOAD BUKTI PENGIRIMAN (TELEPORT TO BODY) --}}
    <template x-teleport="body">
        <div x-show="showUploadModal"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 sm:p-6 font-sans"
             role="dialog"
             aria-modal="true"
             style="display: none;">
             
            {{-- Backdrop --}}
            <div x-show="showUploadModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="showUploadModal = false"
                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"></div>

            {{-- Modal Dialog --}}
            <div x-show="showUploadModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative bg-white rounded-3xl shadow-2xl border border-gray-100 max-w-lg w-full overflow-hidden z-10 mx-auto">
                 
                <form :action="actionUrl" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status_pengiriman_id" value="4">

                    {{-- Modal Header --}}
                    <div class="p-6 pb-4 flex items-start gap-4 border-b border-gray-100">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center shrink-0">
                            <x-heroicon-o-camera class="w-6 h-6 stroke-[2]" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-base font-extrabold text-gray-900 leading-tight">Upload Bukti Pengiriman</h3>
                            <p class="text-xs text-gray-500 mt-1">
                                Pesanan: <span class="font-mono font-bold text-gray-800" x-text="kodePesanan"></span>
                            </p>
                        </div>
                        <button type="button" @click="showUploadModal = false" class="w-8 h-8 rounded-xl text-gray-400 hover:text-gray-600 hover:bg-gray-100 flex items-center justify-center transition cursor-pointer">
                            <x-heroicon-o-x-mark class="w-5 h-5" />
                        </button>
                    </div>

                    {{-- Modal Body --}}
                    <div class="p-6 space-y-4">
                        <p class="text-xs text-gray-600 leading-relaxed">
                            Silakan ambil foto atau unggah foto bukti pengiriman/penerimaan hidangan untuk menyelesaikan pesanan ini.
                        </p>

                        <!-- Hidden native inputs -->
                        <input type="file"
                               x-ref="cameraInput"
                               accept="image/*"
                               capture="environment"
                               @change="handleFile($event)"
                               class="hidden">

                        <input type="file"
                               x-ref="galleryInput"
                               accept="image/*"
                               @change="handleFile($event)"
                               class="hidden">

                        <input type="file"
                               x-ref="mainInput"
                               name="foto_bukti"
                               accept="image/*"
                               required
                               class="hidden">

                        <!-- Upload/Capture Options when no photo selected -->
                        <template x-if="!photoPreview">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                                <!-- Button Buka Kamera -->
                                <button type="button"
                                        @click="$refs.cameraInput.click()"
                                        class="flex flex-col items-center justify-center p-5 rounded-2xl border-2 border-dashed border-emerald-300 bg-emerald-50/50 hover:bg-emerald-100/60 hover:border-emerald-500 text-emerald-800 transition-all cursor-pointer group active:scale-[0.98]">
                                    <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center mb-2.5 shadow-sm group-hover:scale-105 transition-transform">
                                        <x-heroicon-o-camera class="w-6 h-6 stroke-[2]" />
                                    </div>
                                    <span class="text-xs font-black">Buka Kamera</span>
                                    <span class="text-[11px] text-emerald-600 font-medium mt-0.5">Ambil foto langsung</span>
                                </button>

                                <!-- Button Pilih Galeri / File -->
                                <button type="button"
                                        @click="$refs.galleryInput.click()"
                                        class="flex flex-col items-center justify-center p-5 rounded-2xl border-2 border-dashed border-gray-300 bg-gray-50/60 hover:bg-gray-100 hover:border-gray-400 text-gray-700 transition-all cursor-pointer group active:scale-[0.98]">
                                    <div class="w-12 h-12 rounded-2xl bg-gray-200 text-gray-700 flex items-center justify-center mb-2.5 group-hover:scale-105 transition-transform">
                                        <x-heroicon-o-photo class="w-6 h-6 stroke-[2]" />
                                    </div>
                                    <span class="text-xs font-black">Pilih dari Galeri</span>
                                    <span class="text-[11px] text-gray-500 font-medium mt-0.5">Unggah dari file</span>
                                </button>
                            </div>
                        </template>

                        <!-- Photo Preview when photo selected -->
                        <template x-if="photoPreview">
                            <div class="space-y-3">
                                <div class="relative rounded-2xl overflow-hidden border border-emerald-200 bg-gray-900 max-h-56 flex items-center justify-center shadow-inner">
                                    <img :src="photoPreview" alt="Bukti Pengiriman" class="w-full h-56 object-cover">
                                    <div class="absolute top-3 right-3">
                                        <button type="button"
                                                @click="clearPhoto()"
                                                class="w-8 h-8 rounded-xl bg-black/60 hover:bg-black/80 text-white flex items-center justify-center transition cursor-pointer backdrop-blur-xs shadow-md"
                                                title="Hapus foto">
                                            <x-heroicon-o-trash class="w-4 h-4" />
                                        </button>
                                    </div>
                                    <div class="absolute bottom-3 left-3 bg-emerald-600/90 backdrop-blur-xs text-white text-[11px] font-bold px-3 py-1 rounded-lg flex items-center gap-1.5 shadow-sm">
                                        <x-heroicon-o-check-circle class="w-3.5 h-3.5" />
                                        <span>Foto siap diunggah</span>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between gap-2 pt-1">
                                    <p class="text-xs text-gray-500 truncate max-w-[200px]" x-text="fileName"></p>
                                    <div class="flex gap-2">
                                        <button type="button"
                                                @click="$refs.cameraInput.click()"
                                                class="text-xs font-bold text-emerald-700 hover:text-emerald-800 underline cursor-pointer">
                                            Ambil Ulang
                                        </button>
                                        <span class="text-gray-300">|</span>
                                        <button type="button"
                                                @click="$refs.galleryInput.click()"
                                                class="text-xs font-bold text-gray-600 hover:text-gray-800 underline cursor-pointer">
                                            Ganti File
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="bg-gray-50/80 px-6 py-4 border-t border-gray-100 flex items-center justify-end gap-2.5">
                        <button type="button"
                                @click="showUploadModal = false"
                                class="px-4 py-2.5 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 font-bold text-xs shadow-2xs transition active:scale-[0.99] cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                                :disabled="!photoPreview"
                                :class="!photoPreview ? 'opacity-50 cursor-not-allowed' : 'hover:bg-emerald-700 active:scale-[0.99] cursor-pointer'"
                                class="px-5 py-2.5 rounded-xl bg-emerald-600 text-white font-extrabold text-xs shadow-xs transition flex items-center gap-1.5">
                            <x-heroicon-o-check class="w-4 h-4" />
                            <span>Upload & Selesai</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    {{-- MODAL DETAIL JADWAL PENGIRIMAN (TELEPORT TO BODY) --}}
    <template x-teleport="body">
        <div x-show="showDetailModal"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 sm:p-6 font-sans"
             role="dialog"
             aria-modal="true"
             style="display: none;">
             
            {{-- Backdrop --}}
            <div x-show="showDetailModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="showDetailModal = false"
                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"></div>

            {{-- Modal Dialog --}}
            <div x-show="showDetailModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative bg-white rounded-3xl shadow-2xl border border-gray-100 max-w-xl w-full overflow-hidden z-10 mx-auto max-h-[90vh] flex flex-col">
                 
                {{-- Modal Header --}}
                <div class="p-5 sm:p-6 pb-4 flex items-center justify-between border-b border-gray-100 bg-gray-50/50">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center border border-emerald-100 shrink-0">
                            <x-heroicon-o-truck class="w-5 h-5 stroke-[2]" />
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900 leading-tight">Detail Jadwal Pengiriman</h3>
                            <p class="text-xs text-gray-500 font-mono mt-0.5" x-text="activeDetail?.kode_pesanan"></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <template x-if="activeDetail">
                            <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-bold" :class="activeDetail.status_class">
                                <span class="w-1.5 h-1.5 rounded-full" :class="activeDetail.status_dot"></span>
                                <span x-text="activeDetail.status_label"></span>
                            </span>
                        </template>
                        <button type="button" @click="showDetailModal = false" class="w-8 h-8 rounded-xl text-gray-400 hover:text-gray-600 hover:bg-gray-200/50 flex items-center justify-center transition cursor-pointer">
                            <x-heroicon-o-x-mark class="w-5 h-5" />
                        </button>
                    </div>
                </div>

                {{-- Modal Body (Scrollable) --}}
                <div class="p-5 sm:p-6 overflow-y-auto space-y-5 text-sm">
                    <template x-if="activeDetail">
                        <div class="space-y-5">

                            {{-- SEKSI 1: INFORMASI PESANAN --}}
                            <div class="bg-gray-50/80 rounded-2xl p-4 border border-gray-200/70 space-y-3">
                                <div class="flex items-center gap-2 text-xs font-bold text-gray-600 uppercase tracking-wider">
                                    <x-heroicon-o-shopping-bag class="w-4 h-4 text-emerald-600" />
                                    <span>Informasi Pesanan</span>
                                </div>

                                <div class="grid grid-cols-2 gap-3 text-xs">
                                    <div>
                                        <span class="text-gray-400 font-medium">Kode Pesanan</span>
                                        <p class="font-bold text-gray-900 font-mono mt-0.5" x-text="activeDetail.kode_pesanan"></p>
                                    </div>
                                    <div>
                                        <span class="text-gray-400 font-medium">Jenis Pesanan</span>
                                        <p class="font-bold text-gray-900 mt-0.5" x-text="activeDetail.jenis_pesanan"></p>
                                    </div>
                                    <div>
                                        <span class="text-gray-400 font-medium">Paket Menu</span>
                                        <p class="font-bold text-gray-900 mt-0.5" x-text="activeDetail.paket_nama"></p>
                                    </div>
                                    <div>
                                        <span class="text-gray-400 font-medium">Jumlah Porsi / Box</span>
                                        <p class="font-bold text-gray-900 mt-0.5" x-text="activeDetail.jumlah_porsi"></p>
                                    </div>
                                </div>

                                {{-- Komponen Pilihan / Dishes --}}
                                <template x-if="activeDetail.daftar_komponen && activeDetail.daftar_komponen.length > 0">
                                    <div class="pt-2.5 border-t border-gray-200/60">
                                        <span class="text-[11px] font-semibold text-gray-500 block mb-1.5">Menu / Lauk Pilihan:</span>
                                        <div class="flex flex-wrap gap-1.5">
                                            <template x-for="(komp, kIdx) in activeDetail.daftar_komponen" :key="kIdx">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[11px] font-medium bg-white text-gray-700 border border-gray-200 shadow-2xs" x-text="komp"></span>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            {{-- SEKSI 2: INFORMASI KONSUMEN --}}
                            <div class="bg-gray-50/80 rounded-2xl p-4 border border-gray-200/70 space-y-3">
                                <div class="flex items-center gap-2 text-xs font-bold text-gray-600 uppercase tracking-wider">
                                    <x-heroicon-o-user class="w-4 h-4 text-primary" />
                                    <span>Informasi Konsumen</span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                    <div>
                                        <span class="text-gray-400 font-medium">Nama Konsumen / Penerima</span>
                                        <p class="font-bold text-gray-900 mt-0.5 text-sm" x-text="activeDetail.nama_konsumen"></p>
                                    </div>
                                    <div>
                                        <span class="text-gray-400 font-medium">Nomor Telepon</span>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <p class="font-bold text-gray-900 font-mono" x-text="activeDetail.telepon_konsumen"></p>
                                            <template x-if="activeDetail.telepon_raw && activeDetail.telepon_raw !== '-'">
                                                <a :href="'https://wa.me/' + activeDetail.telepon_raw.replace(/[^0-9]/g, '')" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 hover:text-emerald-800 bg-emerald-100/70 hover:bg-emerald-100 px-2 py-0.5 rounded-md border border-emerald-200 transition">
                                                    <span>WhatsApp</span>
                                                </a>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- SEKSI 3: INFORMASI PENGIRIMAN --}}
                            <div class="bg-gray-50/80 rounded-2xl p-4 border border-gray-200/70 space-y-3">
                                <div class="flex items-center gap-2 text-xs font-bold text-gray-600 uppercase tracking-wider">
                                    <x-heroicon-o-map-pin class="w-4 h-4 text-amber-600" />
                                    <span>Informasi Pengiriman</span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                    <div>
                                        <span class="text-gray-400 font-medium">Tanggal Pengiriman</span>
                                        <p class="font-bold text-gray-900 mt-0.5" x-text="activeDetail.tanggal_pengiriman"></p>
                                    </div>
                                    <div>
                                        <span class="text-gray-400 font-medium">Jam Pengantaran</span>
                                        <p class="font-bold text-gray-900 mt-0.5" x-text="activeDetail.jam_pengiriman"></p>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <span class="text-gray-400 font-medium">Alamat Lengkap</span>
                                        <p class="font-semibold text-gray-800 mt-0.5 leading-relaxed bg-white p-2.5 rounded-xl border border-gray-200/80" x-text="activeDetail.alamat_lengkap"></p>
                                    </div>
                                    <div>
                                        <span class="text-gray-400 font-medium">Biaya Pengiriman</span>
                                        <p class="font-bold text-gray-900 mt-0.5">
                                            <span x-text="activeDetail.biaya_pengiriman"></span>
                                            <template x-if="activeDetail.jarak_pengiriman">
                                                <span class="text-gray-400 font-normal text-[11px]" x-text="' (' + activeDetail.jarak_pengiriman + ')'"></span>
                                            </template>
                                        </p>
                                    </div>
                                    <div>
                                        <span class="text-gray-400 font-medium">Catatan Pengantaran</span>
                                        <p class="font-medium text-gray-700 mt-0.5" x-text="activeDetail.catatan || '-'"></p>
                                    </div>
                                </div>

                                {{-- Foto Bukti Pengiriman --}}
                                <template x-if="activeDetail.foto_bukti">
                                    <div class="pt-2.5 border-t border-gray-200/60">
                                        <span class="text-[11px] font-semibold text-gray-500 block mb-1.5">Foto Bukti Pengiriman:</span>
                                        <a :href="activeDetail.foto_bukti" target="_blank" class="block rounded-xl overflow-hidden border border-gray-200 max-w-xs hover:opacity-90 transition shadow-2xs">
                                            <img :src="activeDetail.foto_bukti" alt="Bukti Pengiriman" class="w-full h-36 object-cover">
                                        </a>
                                    </div>
                                </template>
                            </div>

                        </div>
                    </template>
                </div>

                {{-- Modal Footer --}}
                <div class="p-4 sm:p-5 border-t border-gray-100 bg-gray-50/80 flex items-center justify-between gap-3">
                    <template x-if="activeDetail?.url_pesanan">
                        <a :href="activeDetail.url_pesanan" class="text-xs font-bold text-emerald-700 hover:text-emerald-800 hover:underline flex items-center gap-1">
                            <span>Buka Halaman Pesanan</span>
                            <x-heroicon-o-arrow-top-right-on-square class="w-3.5 h-3.5" />
                        </a>
                    </template>
                    <div class="flex items-center gap-2 ml-auto">
                        <button type="button" @click="showDetailModal = false" class="px-4 py-2 rounded-xl bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold text-xs transition cursor-pointer">
                            Tutup
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </template>
</div>
@endsection