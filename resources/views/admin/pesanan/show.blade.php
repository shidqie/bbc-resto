@extends('layouts.pos')

@section('title', 'Detail Pesanan')

@section('content')
<div class="flex flex-col h-full bg-white">
    {{-- Header --}}
    <div class="px-6 py-5 border-b border-gray-100 shrink-0 bg-white sticky top-0 z-10 shadow-sm">
        <x-ui.page-header
            title="{{ $pesanan->id_pesanan ?? 'DIN-'.$pesanan->id }}"
            subtitle="Dibuat {{ \Carbon\Carbon::parse($pesanan->dibuat_pada)->format('d F Y, H:i') }} &bull; {{ optional($pesanan->jenis_pesanan)->nama_jenis ?? '-' }}"
            :breadcrumbs="['Penjualan', 'Semua Pesanan', 'Detail']">
            <x-slot:actions>
                <div class="flex items-center gap-2">
                    @php
                        $color = 'gray';
                        if($pesanan->status_pesanan_id == 5) $color = 'emerald';
                        elseif($pesanan->status_pesanan_id == 1) $color = 'amber';
                        elseif($pesanan->status_pesanan_id == 6) $color = 'red';
                        else $color = 'blue';
                    @endphp
                    <x-ui.badge :color="$color" size="sm">
                        {{ optional($pesanan->status_pesanan)->nama_status ?? 'Unknown' }}
                    </x-ui.badge>
                    <x-ui.button href="{{ route('admin.pesanan.index') }}" variant="secondary" icon="arrow-left">
                        Kembali
                    </x-ui.button>
                </div>
            </x-slot:actions>
        </x-ui.page-header>
    </div>

    {{-- Body --}}
    <div class="flex-1 overflow-y-auto p-6 space-y-6 bg-gray-50/50">
        
        {{-- Tabel Detil Pesanan --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 bg-slate-50/50 flex justify-between items-center">
                <h4 class="text-sm font-bold text-gray-900"><x-heroicon-o-information-circle class="mr-1.5 text-gray-400 w-5 h-5 inline" /> Informasi Pesanan</h4>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <tbody class="divide-y divide-gray-100">
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 font-semibold text-gray-500 w-1/3">Kode Pesanan</td>
                            <td class="px-5 py-3 text-gray-900 font-bold">{{ $pesanan->id_pesanan ?? 'DIN-'.$pesanan->id }}</td>
                        </tr>
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 font-semibold text-gray-500">Jenis Pesanan</td>
                            <td class="px-5 py-3 text-gray-900">{{ optional($pesanan->jenis_pesanan)->nama_jenis ?? '-' }}</td>
                        </tr>
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 font-semibold text-gray-500">Tanggal dan Waktu</td>
                            <td class="px-5 py-3 text-gray-900">{{ \Carbon\Carbon::parse($pesanan->dibuat_pada)->format('j F Y, H:i') }} WIB</td>
                        </tr>
                        @php
                            $nama = 'Tamu';
                            $wa = '-';
                            if ($pesanan->pelanggan) {
                                $nama = $pesanan->pelanggan->nama;
                                $wa = $pesanan->pelanggan->nomor_telepon ? \App\Support\WhatsAppNumber::formatForDisplay($pesanan->pelanggan->nomor_telepon) : '-';
                            } elseif (!empty($pesanan->catatan)) {
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
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 font-semibold text-gray-500">Nama Pelanggan</td>
                            <td class="px-5 py-3 text-gray-900">{{ $nama }}</td>
                        </tr>
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 font-semibold text-gray-500">Nomor WhatsApp</td>
                            <td class="px-5 py-3 text-gray-900">{{ $wa }}</td>
                        </tr>
                        
                        @if($pesanan->jenis_pesanan_id != 2 && $pesanan->jenis_pesanan_id != 3)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 font-semibold text-gray-500">Meja</td>
                            <td class="px-5 py-3 text-gray-900">{{ $pesanan->meja ? 'Meja '.$pesanan->meja->nomor_meja : '-' }}</td>
                        </tr>
                        @endif

                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 font-semibold text-gray-500">Status Pesanan</td>
                            <td class="px-5 py-3 text-gray-900 font-bold">{{ optional($pesanan->status_pesanan)->nama_status ?? 'Unknown' }}</td>
                        </tr>
                        <tr class="hover:bg-slate-50">
                            @php
                                $statusPembayaran = \App\Models\StatusPembayaran::find($pesanan->status_pembayaran_id);
                                $payStatus = $statusPembayaran ? $statusPembayaran->nama_status : 'Unknown';
                            @endphp
                            <td class="px-5 py-3 font-semibold text-gray-500">Status Pembayaran</td>
                            <td class="px-5 py-3 text-gray-900 font-bold">{{ $payStatus }}</td>
                        </tr>
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 font-semibold text-gray-500">Kasir/Pelayan</td>
                            <td class="px-5 py-3 text-gray-900">
                                @if($pesanan->kasir)
                                    Kasir: {{ $pesanan->kasir->nama }}
                                @endif
                                @if($pesanan->pelayan)
                                    @if($pesanan->kasir) | @endif Pelayan: {{ $pesanan->pelayan->nama }}
                                @endif
                                @if(!$pesanan->kasir && !$pesanan->pelayan)
                                    -
                                @endif
                            </td>
                        </tr>
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 font-semibold text-gray-500">Catatan</td>
                            <td class="px-5 py-3 text-gray-900">{{ $pesanan->catatan ?? '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        @if($pesanan->jenis_pesanan_id == 1)
        {{-- Detail Khusus Dine In --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mt-6 mb-6">
            <div class="px-5 py-4 border-b border-gray-100 bg-slate-50/50 flex justify-between items-center">
                <h4 class="text-sm font-bold text-gray-900">Informasi Meja dan Dapur</h4>
            </div>
            <div class="p-5">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Nomor Meja</p>
                        <p class="text-sm font-bold text-gray-900">{{ $pesanan->meja ? 'Meja '.$pesanan->meja->nomor_meja : '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Jumlah Tamu</p>
                        <p class="text-sm font-bold text-gray-900">{{ optional($pesanan->jadwal_pesanan)->jumlah_tamu ?? '-' }} orang</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Nomor KOT</p>
                        <p class="text-sm font-bold text-gray-900">KOT-{{ \Carbon\Carbon::parse($pesanan->dibuat_pada)->format('Ymd') }}-{{ sprintf('%03d', $pesanan->id) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Status KOT</p>
                        <p class="text-sm font-bold text-gray-900">{{ optional($pesanan->status_pesanan)->nama_status ?? 'Unknown' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Waktu Masuk Dapur</p>
                        <p class="text-sm font-bold text-gray-900">{{ \Carbon\Carbon::parse($pesanan->dibuat_pada)->format('H:i') }} WIB</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Waktu Selesai</p>
                        <p class="text-sm font-bold text-gray-900">{{ $pesanan->status_pesanan_id == 5 ? \Carbon\Carbon::parse($pesanan->diperbarui_pada)->format('H:i').' WIB' : '-' }}</p>
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-4 mb-6">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Alur Status</p>
                    <div class="flex items-center text-xs font-bold gap-2 text-gray-400 flex-wrap">
                        @php
                            $steps = [
                                1 => 'Pesanan Masuk',
                                2 => 'Dikonfirmasi',
                                3 => 'Diproses Dapur',
                                4 => 'Siap Disajikan',
                                5 => 'Selesai'
                            ];
                            $current = $pesanan->status_pesanan_id;
                        @endphp
                        @foreach($steps as $k => $label)
                            <div class="flex items-center gap-2 mb-2">
                                <span class="px-2 py-1 rounded {{ $k == $current ? 'bg-primary text-white' : ($k < $current ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-400') }}">{{ $label }}</span>
                                @if(!$loop->last)
                                    <x-heroicon-o-arrow-right class="w-3 h-3" />
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-4 flex gap-3 flex-wrap">
                    <button onclick="window.showToast('info', 'Cetak KOT segera hadir')" class="px-4 py-2 border border-gray-200 text-gray-700 font-bold text-sm rounded-lg hover:bg-gray-50 shadow-sm">Cetak KOT</button>
                    <button onclick="window.showToast('info', 'Ubah Pesanan segera hadir')" class="px-4 py-2 border border-gray-200 text-gray-700 font-bold text-sm rounded-lg hover:bg-gray-50 shadow-sm">Ubah Pesanan</button>
                    <button onclick="window.showToast('info', 'Proses Pembayaran segera hadir')" class="px-4 py-2 bg-blue-50 text-blue-700 font-bold text-sm rounded-lg hover:bg-blue-100 shadow-sm">Proses Pembayaran</button>
                    <button onclick="window.showToast('info', 'Batalkan segera hadir')" class="px-4 py-2 bg-red-50 text-red-600 font-bold text-sm rounded-lg hover:bg-red-100 shadow-sm">Batalkan Pesanan</button>
                    @if($pesanan->status_pesanan_id == 5)
                        <button onclick="window.open('/pos/dinein/pesanan/{{ $pesanan->id }}/print-nota', '_blank')" class="px-4 py-2 bg-[#0D3024] text-white font-bold text-sm rounded-lg hover:bg-[#0a1f17] shadow-sm">Cetak Bukti Transaksi</button>
                        <button onclick="window.showToast('info', 'Selesaikan Pesanan segera hadir')" class="px-4 py-2 bg-emerald-50 text-emerald-700 font-bold text-sm rounded-lg hover:bg-emerald-100 shadow-sm">Selesaikan Pesanan</button>
                    @endif
                </div>
            </div>
        </div>
        @endif

        @if($pesanan->jenis_pesanan_id == 2)
        {{-- Detail Khusus Katering --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mt-6 mb-6">
            <div class="px-5 py-4 border-b border-gray-100 bg-slate-50/50 flex justify-between items-center">
                <h4 class="text-sm font-bold text-gray-900">Informasi Acara (Katering)</h4>
            </div>
            <div class="p-5">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Nama Acara</p>
                        <p class="text-sm font-bold text-gray-900">{{ optional($pesanan->jadwal_pesanan)->nama_acara ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Tanggal Acara</p>
                        <p class="text-sm font-bold text-gray-900">{{ $pesanan->jadwal_pesanan ? \Carbon\Carbon::parse($pesanan->jadwal_pesanan->tanggal_acara)->format('d F Y') : '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Waktu Acara</p>
                        <p class="text-sm font-bold text-gray-900">{{ optional($pesanan->jadwal_pesanan)->waktu_pengiriman ? \Carbon\Carbon::parse($pesanan->jadwal_pesanan->waktu_pengiriman)->format('H:i').' WIB' : '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Jumlah Porsi</p>
                        <p class="text-sm font-bold text-gray-900">{{ optional($pesanan->detail_pesanan->first())->jumlah ?? '-' }} porsi</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Paket</p>
                        <p class="text-sm font-bold text-gray-900">{{ optional(optional($pesanan->detail_pesanan->first())->menu)->nama_menu ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Metode Pemenuhan</p>
                        <p class="text-sm font-bold text-gray-900">{{ $pesanan->pengiriman ? 'Diantar' : 'Diambil Sendiri' }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-xs text-gray-500 font-medium">Alamat Acara</p>
                        <p class="text-sm font-bold text-gray-900">{{ optional($pesanan->jadwal_pesanan)->alamat_pengiriman ?? '-' }}</p>
                    </div>
                    <div class="md:col-span-4">
                        <p class="text-xs text-gray-500 font-medium">Catatan Acara</p>
                        <p class="text-sm font-bold text-gray-900">{{ optional($pesanan->jadwal_pesanan)->catatan ?? '-' }}</p>
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-4 mb-6">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Jadwal Pembayaran</p>
                    <div class="overflow-x-auto border border-gray-200 rounded-lg">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-gray-50 text-gray-600 font-semibold border-b border-gray-200">
                                <tr>
                                    <th class="px-4 py-2">Tahap</th>
                                    <th class="px-4 py-2">Nominal</th>
                                    <th class="px-4 py-2">Jatuh Tempo</th>
                                    <th class="px-4 py-2">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $dpAmount = $pesanan->total_tagihan * 0.5;
                                    $totalBayar = $pesanan->pembayaran->sum('jumlah_dibayar');
                                    $dpPaid = $totalBayar >= $dpAmount;
                                    $lunasPaid = $totalBayar >= $pesanan->total_tagihan;
                                    $jtDp = \Carbon\Carbon::parse($pesanan->dibuat_pada)->format('d F Y');
                                    $jtLunas = $pesanan->jadwal_pesanan ? \Carbon\Carbon::parse($pesanan->jadwal_pesanan->tanggal_acara)->subDays(3)->format('d F Y') : '-';
                                @endphp
                                <tr class="border-b border-gray-100">
                                    <td class="px-4 py-2 font-medium text-gray-900">DP 50%</td>
                                    <td class="px-4 py-2 text-gray-700">Rp{{ number_format($dpAmount, 0, ',', '.') }}</td>
                                    <td class="px-4 py-2 text-gray-700">{{ $jtDp }}</td>
                                    <td class="px-4 py-2 font-bold {{ $dpPaid ? 'text-emerald-600' : 'text-amber-600' }}">{{ $dpPaid ? 'Terverifikasi' : 'Belum Bayar' }}</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-2 font-medium text-gray-900">Pelunasan</td>
                                    <td class="px-4 py-2 text-gray-700">Rp{{ number_format(max(0, $pesanan->total_tagihan - $dpAmount), 0, ',', '.') }}</td>
                                    <td class="px-4 py-2 text-gray-700">{{ $jtLunas }}</td>
                                    <td class="px-4 py-2 font-bold {{ $lunasPaid ? 'text-emerald-600' : 'text-red-600' }}">{{ $lunasPaid ? 'Terverifikasi' : 'Belum Bayar' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-4 mb-6">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Status Produksi dan Pengiriman</p>
                    <div class="flex items-center text-xs font-bold gap-2 text-gray-400 flex-wrap">
                        @php
                            $stepsKat = [
                                1 => 'Menunggu Konfirmasi',
                                2 => 'Terkonfirmasi',
                                3 => 'Menunggu Produksi',
                                4 => 'Sedang Diproduksi',
                                5 => 'Siap Dikirim',
                                6 => 'Sedang Dikirim',
                                7 => 'Diterima',
                                8 => 'Selesai'
                            ];
                            $currentKat = 1; // dummy mapping for now, can map real status if needed
                        @endphp
                        @foreach($stepsKat as $k => $label)
                            <div class="flex items-center gap-2 mb-2">
                                <span class="px-2 py-1 rounded {{ $k == $currentKat ? 'bg-primary text-white' : ($k < $currentKat ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-400') }}">{{ $label }}</span>
                                @if(!$loop->last)
                                    <x-heroicon-o-arrow-right class="w-3 h-3" />
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-4 flex gap-3 flex-wrap">
                    <button onclick="window.showToast('info', 'Konfirmasi Pesanan segera hadir')" class="px-4 py-2 bg-blue-50 text-blue-700 font-bold text-sm rounded-lg hover:bg-blue-100 shadow-sm">Konfirmasi Pesanan</button>
                    <button onclick="window.showToast('info', 'Verifikasi Pembayaran segera hadir')" class="px-4 py-2 border border-gray-200 text-gray-700 font-bold text-sm rounded-lg hover:bg-gray-50 shadow-sm">Verifikasi Pembayaran</button>
                    <button onclick="window.showToast('info', 'Cetak Daftar Produksi segera hadir')" class="px-4 py-2 border border-gray-200 text-gray-700 font-bold text-sm rounded-lg hover:bg-gray-50 shadow-sm">Cetak Daftar Produksi</button>
                    <button onclick="window.showToast('info', 'Buat Pengadaan Bahan segera hadir')" class="px-4 py-2 border border-gray-200 text-gray-700 font-bold text-sm rounded-lg hover:bg-gray-50 shadow-sm">Buat Pengadaan Bahan</button>
                    <button onclick="window.showToast('info', 'Atur Pengiriman segera hadir')" class="px-4 py-2 border border-gray-200 text-gray-700 font-bold text-sm rounded-lg hover:bg-gray-50 shadow-sm">Atur Pengiriman</button>
                    <button onclick="window.showToast('info', 'Batalkan Pesanan segera hadir')" class="px-4 py-2 bg-red-50 text-red-600 font-bold text-sm rounded-lg hover:bg-red-100 shadow-sm">Batalkan Pesanan</button>
                </div>
            </div>
        </div>
        @endif

        @if($pesanan->jenis_pesanan_id == 3)
        {{-- Detail Khusus Nasi Box --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mt-6 mb-6">
            <div class="px-5 py-4 border-b border-gray-100 bg-slate-50/50 flex justify-between items-center">
                <h4 class="text-sm font-bold text-gray-900">Informasi Pesanan Nasi Box</h4>
            </div>
            <div class="p-5">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Paket</p>
                        <p class="text-sm font-bold text-gray-900">{{ optional(optional($pesanan->detail_pesanan->first())->menu)->nama_menu ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Jumlah</p>
                        <p class="text-sm font-bold text-gray-900">{{ optional($pesanan->detail_pesanan->first())->jumlah ?? '-' }} box</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Tanggal Dibutuhkan</p>
                        <p class="text-sm font-bold text-gray-900">{{ $pesanan->jadwal_pesanan ? \Carbon\Carbon::parse($pesanan->jadwal_pesanan->tanggal_acara)->format('d F Y') : '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Waktu Dibutuhkan</p>
                        <p class="text-sm font-bold text-gray-900">{{ optional($pesanan->jadwal_pesanan)->waktu_pengiriman ? \Carbon\Carbon::parse($pesanan->jadwal_pesanan->waktu_pengiriman)->format('H:i').' WIB' : '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Metode Pemenuhan</p>
                        <p class="text-sm font-bold text-gray-900">{{ $pesanan->pengiriman ? 'Diantar' : 'Diambil Sendiri' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Nama Penerima</p>
                        <p class="text-sm font-bold text-gray-900">{{ optional($pesanan->jadwal_pesanan)->nama_penerima ?? '-' }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-xs text-gray-500 font-medium">Nomor WhatsApp</p>
                        <p class="text-sm font-bold text-gray-900">{{ optional($pesanan->jadwal_pesanan)->nomor_telepon_penerima ? \App\Support\WhatsAppNumber::formatForDisplay($pesanan->jadwal_pesanan->nomor_telepon_penerima) : '-' }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-xs text-gray-500 font-medium">Alamat Pengiriman</p>
                        <p class="text-sm font-bold text-gray-900">{{ optional($pesanan->jadwal_pesanan)->alamat_pengiriman ?? '-' }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-xs text-gray-500 font-medium">Catatan</p>
                        <p class="text-sm font-bold text-gray-900">{{ optional($pesanan->jadwal_pesanan)->catatan ?? '-' }}</p>
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-4 mb-6">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Pilihan Isi Paket</p>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-y-2 gap-x-4">
                        @php
                            $pilihan = optional($pesanan->detail_pesanan->first())->pilihan_pesanan_catering;
                            $mappedPilihan = [];
                            if ($pilihan) {
                                foreach ($pilihan as $p) {
                                    $kategori = optional(optional($p->pilihan_item_paket)->komponen_paket)->nama_komponen ?? 'Lainnya';
                                    $menuName = optional(optional($p->pilihan_item_paket)->menu)->nama_menu ?? '-';
                                    $mappedPilihan[] = ['kategori' => $kategori, 'menu' => $menuName];
                                }
                            }
                        @endphp
                        @forelse($mappedPilihan as $m)
                            <div class="flex justify-between items-start border-b border-gray-50 pb-1">
                                <span class="text-gray-500 font-medium text-sm">{{ $m['kategori'] }}</span>
                                <span class="text-gray-900 font-bold text-sm text-right">{{ $m['menu'] }}</span>
                            </div>
                        @empty
                            <div class="col-span-full text-sm text-gray-400">Tidak ada rincian pilihan menu.</div>
                        @endforelse
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-4 flex gap-3 flex-wrap">
                    <button onclick="window.showToast('info', 'Konfirmasi Pesanan segera hadir')" class="px-4 py-2 bg-blue-50 text-blue-700 font-bold text-sm rounded-lg hover:bg-blue-100 shadow-sm">Konfirmasi Pesanan</button>
                    <button onclick="window.showToast('info', 'Verifikasi DP segera hadir')" class="px-4 py-2 border border-gray-200 text-gray-700 font-bold text-sm rounded-lg hover:bg-gray-50 shadow-sm">Verifikasi DP</button>
                    <button onclick="window.showToast('info', 'Cetak Daftar Produksi segera hadir')" class="px-4 py-2 border border-gray-200 text-gray-700 font-bold text-sm rounded-lg hover:bg-gray-50 shadow-sm">Cetak Daftar Produksi</button>
                    <button onclick="window.showToast('info', 'Buat Pengadaan segera hadir')" class="px-4 py-2 border border-gray-200 text-gray-700 font-bold text-sm rounded-lg hover:bg-gray-50 shadow-sm">Buat Pengadaan</button>
                    <button onclick="window.showToast('info', 'Atur Pengiriman segera hadir')" class="px-4 py-2 border border-gray-200 text-gray-700 font-bold text-sm rounded-lg hover:bg-gray-50 shadow-sm">Atur Pengiriman</button>
                    <button onclick="window.showToast('info', 'Batalkan Pesanan segera hadir')" class="px-4 py-2 bg-red-50 text-red-600 font-bold text-sm rounded-lg hover:bg-red-100 shadow-sm">Batalkan Pesanan</button>
                </div>
            </div>
        </div>
        @endif

        {{-- Daftar Item --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 bg-slate-50/50 flex justify-between items-center">
                <h4 class="text-sm font-bold text-gray-900"><x-heroicon-o-sparkles class="mr-1.5 text-gray-400 w-5 h-5" /> Daftar Item Pesanan</h4>
                <span class="text-xs font-bold bg-white border border-gray-200 px-2.5 py-1 rounded-xl text-gray-600">{{ $pesanan->detail_pesanan->count() }} Menu</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-50 text-gray-500 font-extrabold uppercase text-xs tracking-wider border-b border-gray-100">
                        <tr>
                            <th class="px-5 py-3">No.</th>
                            <th class="px-5 py-3">{{ $pesanan->jenis_pesanan_id == 1 ? 'Menu' : 'Paket' }}</th>
                            @if($pesanan->jenis_pesanan_id != 1)
                            <th class="px-5 py-3">Pilihan Menu</th>
                            @endif
                            <th class="px-5 py-3">Harga{{ $pesanan->jenis_pesanan_id != 1 ? '/Porsi' : '' }}</th>
                            <th class="px-5 py-3 text-center">Jumlah</th>
                            <th class="px-5 py-3 text-right">Subtotal</th>
                            @if($pesanan->jenis_pesanan_id == 1)
                            <th class="px-5 py-3">Catatan</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($pesanan->detail_pesanan as $idx => $item)
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-5 py-4 text-gray-500 font-medium">{{ $idx + 1 }}</td>
                                <td class="px-5 py-4 font-bold text-gray-900">{{ optional($item->menu)->nama_menu ?? 'Menu Dihapus' }}</td>
                                
                                @if($pesanan->jenis_pesanan_id != 1)
                                <td class="px-5 py-4 text-gray-600">
                                    @php
                                        // For Katering/Nasi Box, collect the chosen items
                                        $pilihan = $item->pilihan_pesanan_catering->map(function($p) {
                                            return optional($p->pilihan_item_paket->menu)->nama_menu;
                                        })->filter()->implode(', ');
                                    @endphp
                                    {{ $pilihan ?: '-' }}
                                </td>
                                @endif
                                
                                <td class="px-5 py-4 text-gray-600">Rp{{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                                <td class="px-5 py-4 text-center font-bold text-gray-900">{{ $item->jumlah }}</td>
                                <td class="px-5 py-4 text-right font-black text-[#0D3024]">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                
                                @if($pesanan->jenis_pesanan_id == 1)
                                <td class="px-5 py-4 text-gray-500 text-xs italic">{{ $item->catatan ?? '-' }}</td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $pesanan->jenis_pesanan_id == 1 ? 6 : 6 }}" class="text-center py-6 text-sm text-gray-400 font-medium">Tidak ada rincian item.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{-- Ringkasan Pembayaran --}}
            <div class="bg-slate-50 border-t border-gray-200">
                <div class="px-5 py-4 flex flex-col md:flex-row gap-6">
                    {{-- Detail Nominal --}}
                    <div class="flex-1 space-y-2.5 text-sm">
                        @php
                            $subtotal = $pesanan->detail_pesanan->sum('subtotal');
                            $diskon = $pesanan->jumlah_diskon ?? 0;
                            
                            // Check pengiriman for Katering/Nasi Box
                            $ongkir = 0;
                            if ($pesanan->pengiriman) {
                                $ongkir = $pesanan->pengiriman->biaya_pengiriman;
                            }
                        @endphp
                        
                        <div class="flex justify-between items-center text-gray-600 font-medium">
                            <span>Subtotal</span>
                            <span>Rp{{ number_format($subtotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center text-gray-600 font-medium">
                            <span>Diskon</span>
                            <span>- Rp{{ number_format($diskon, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center text-gray-600 font-medium">
                            <span>Biaya Pengiriman</span>
                            <span>Rp{{ number_format($ongkir, 0, ',', '.') }}</span>
                        </div>
                        
                        <div class="pt-3 mt-2 border-t border-gray-200 border-dashed flex justify-between items-center">
                            <span class="font-bold text-gray-800 uppercase tracking-wider">Total Tagihan</span>
                            <span class="text-lg font-black text-[#0D3024]">Rp{{ number_format($pesanan->total_tagihan, 0, ',', '.') }}</span>
                        </div>
                        
                        @php
                            $totalBayar = $pesanan->pembayaran->sum('jumlah_dibayar');
                            $sisa = max(0, $pesanan->total_tagihan - $totalBayar);
                        @endphp
                        <div class="flex justify-between items-center text-gray-600 font-medium mt-1">
                            <span>Sudah Dibayar</span>
                            <span class="text-emerald-600">Rp{{ number_format($totalBayar, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center text-gray-600 font-bold mt-1">
                            <span>Sisa Pembayaran</span>
                            <span class="text-red-600">Rp{{ number_format($sisa, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    
                    {{-- Aksi --}}
                    <div class="flex-1 md:border-l md:border-gray-200 md:pl-6 flex flex-col justify-center gap-3">
                        <div class="bg-white p-3 rounded-xl border border-gray-200 mb-2">
                            <p class="text-xs text-gray-500 mb-1">Metode Pembayaran</p>
                            <p class="text-sm font-bold text-gray-900">
                                @if($pesanan->pembayaran->count() > 0)
                                    {{ $pesanan->pembayaran->last()->metode_pembayaran ?? 'Tunai' }}
                                @else
                                    Belum dipilih
                                @endif
                            </p>
                        </div>
                        
                        <button onclick="window.showToast('info', 'Halaman Proses Pembayaran segera hadir')" class="w-full flex items-center justify-center gap-2 bg-[#0D3024] hover:bg-[#0a1f17] text-white py-2.5 px-4 rounded-xl text-sm font-bold transition-colors">
                            <x-heroicon-o-banknotes class="w-5 h-5" /> Proses Pembayaran
                        </button>
                        <button onclick="window.open('/pos/dinein/pesanan/{{ $pesanan->id }}/print-nota', '_blank')" class="w-full flex items-center justify-center gap-2 bg-white hover:bg-gray-50 text-gray-700 border border-gray-200 py-2.5 px-4 rounded-xl text-sm font-bold transition-colors">
                            <x-heroicon-o-printer class="w-5 h-5" /> Cetak Tagihan
                        </button>
                        <button onclick="window.showToast('info', 'Riwayat Pembayaran segera hadir')" class="w-full flex items-center justify-center gap-2 bg-blue-50 hover:bg-blue-100 text-blue-700 py-2.5 px-4 rounded-xl text-sm font-bold transition-colors">
                            <x-heroicon-o-clock class="w-5 h-5" /> Lihat Riwayat Pembayaran
                        </button>
                    </div>
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
                    $terbayar = $pesanan->pembayaran->sum('jumlah_dibayar');
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
                                <span class="block text-xs text-gray-400 mt-1">oleh {{ optional($bayar->diverifikasi_oleh_pengguna)->nama ?? 'Kasir' }}</span>
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
