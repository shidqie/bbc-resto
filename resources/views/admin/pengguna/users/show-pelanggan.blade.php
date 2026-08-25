@extends('layouts.pos')

@section('title', 'Detail Data Konsumen — ' . $pelanggan->nama)

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800 pb-12 w-full h-full flex flex-col">
    <div class="w-full p-6 space-y-6 flex flex-col flex-1 min-h-0">

        <!-- Header Area -->
        <x-ui.page-header
            title="Detail Data Konsumen"
            subtitle="Informasi profil konsumen dan seluruh riwayat pemesanan katering & nasi box."
            :breadcrumbs="['Manajemen Pengguna', 'Data Konsumen', $pelanggan->nama]">
            <x-slot:actions>
                <x-ui.button variant="secondary" href="{{ route('users.index', ['type' => 'pelanggan']) }}">
                    <x-heroicon-o-arrow-left class="w-4 h-4 mr-1.5" />
                    Kembali
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Profile Card -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white rounded-2xl border border-gray-200 shadow-xs overflow-hidden">
                    <!-- Profile Header -->
                    <div class="p-6 border-b border-gray-100 flex items-center gap-4 bg-gradient-to-r from-emerald-50/80 via-white to-teal-50/40">
                        <div class="w-14 h-14 rounded-2xl bg-emerald-600 text-white flex items-center justify-center font-black text-xl shadow-sm shrink-0">
                            {{ strtoupper(substr($pelanggan->nama, 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <h2 class="text-lg font-bold text-gray-900 truncate tracking-tight" style="color: #111827 !important;">{{ $pelanggan->nama }}</h2>
                            <div class="flex items-center gap-2 mt-1">
                                @if($pelanggan->status_akun === 'Terdaftar')
                                    <x-ui.badge color="success" size="sm">Terdaftar</x-ui.badge>
                                @else
                                    <x-ui.badge color="gray" size="sm">Tamu</x-ui.badge>
                                @endif
                                <span class="text-xs text-gray-500 font-mono font-medium">{{ $pelanggan->kode_pelanggan }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Profile Details -->
                    <div class="p-6 space-y-4 text-sm divide-y divide-gray-100">
                        <div class="pt-1 first:pt-0">
                            <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Nama Lengkap</span>
                            <span class="text-gray-900 font-bold">{{ $pelanggan->nama }}</span>
                        </div>

                        <div class="pt-3">
                            <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Nomor Telepon / WhatsApp</span>
                            @if($pelanggan->nomor_telepon)
                                <a href="https://wa.me/{{ str_replace(['+', '-', ' '], '', $pelanggan->nomor_telepon) }}" target="_blank" class="text-emerald-600 hover:text-emerald-700 font-bold inline-flex items-center gap-1.5">
                                    <x-heroicon-o-chat-bubble-left-right class="w-4 h-4 text-emerald-500" />
                                    {{ \App\Support\WhatsAppNumber::formatForDisplay($pelanggan->nomor_telepon) }}
                                    <x-heroicon-o-arrow-top-right-on-square class="w-3.5 h-3.5 opacity-70" />
                                </a>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </div>

                        <div class="pt-3">
                            <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Email</span>
                            <span class="text-gray-700 font-medium">{{ $pelanggan->email ?? '-' }}</span>
                        </div>

                        <div class="pt-3">
                            <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Alamat Konsumen</span>
                            <span class="text-gray-700 font-medium leading-relaxed">{{ $pelanggan->alamat && $pelanggan->alamat !== '-' ? $pelanggan->alamat : 'Belum ada alamat tersimpan' }}</span>
                        </div>

                        <div class="pt-3">
                            <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Status Akun</span>
                            <div>
                                @if($pelanggan->status_akun === 'Terdaftar')
                                    <x-ui.badge color="success" size="sm">Akun Terdaftar</x-ui.badge>
                                    <p class="text-[11px] text-gray-400 mt-1">Konsumen memiliki akun terdaftar di sistem.</p>
                                @else
                                    <x-ui.badge color="gray" size="sm">Pelanggan Tamu</x-ui.badge>
                                    <p class="text-[11px] text-gray-400 mt-1">Data tersimpan otomatis dari pemesanan tanpa akun.</p>
                                @endif
                            </div>
                        </div>

                        <div class="pt-3">
                            <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Pertama Kali Transaksi</span>
                            <span class="text-gray-700 font-medium">{{ $pelanggan->dibuat_pada ? $pelanggan->dibuat_pada->translatedFormat('d F Y, H:i') : '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activity & Orders -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Summary Statistics -->
                @php
                    $orders = $semuaPesanan ?? $pelanggan->pesanan()->latest('tanggal_pesanan')->get();
                    $totalCount = $orders->count();
                    $selesaiCount = $orders->filter(fn($p) => optional($p->status_pesanan)->kode_status === 'selesai' || $p->status_pesanan_id == 5)->count();
                    $totalNominal = $orders->whereNotIn('status_pesanan_id', [6])->sum('total_tagihan');
                @endphp

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-xs flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Pesanan</p>
                            <p class="text-2xl font-black text-gray-900 mt-1">{{ $totalCount }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-primary-soft flex items-center justify-center text-primary">
                            <x-heroicon-o-shopping-bag class="w-6 h-6" />
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-xs flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Pesanan Selesai</p>
                            <p class="text-2xl font-black text-emerald-600 mt-1">{{ $selesaiCount }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                            <x-heroicon-o-check-badge class="w-6 h-6" />
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-xs flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Belanja</p>
                            <p class="text-xl font-black text-gray-900 mt-1">Rp {{ number_format($totalNominal, 0, ',', '.') }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600">
                            <x-heroicon-o-banknotes class="w-6 h-6" />
                        </div>
                    </div>
                </div>

                <!-- Riwayat Pesanan Table -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-xs overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                        <div>
                            <h3 class="text-xs font-extrabold text-gray-700 uppercase tracking-wider">Riwayat Pemesanan</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Daftar transaksi pesanan katering, nasi box, dan lainnya oleh konsumen ini.</p>
                        </div>
                        <span class="text-xs font-bold text-gray-700 bg-gray-200/80 px-2.5 py-1 rounded-lg">{{ $orders->count() }} Transaksi</span>
                    </div>

                    @if($orders->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-100 bg-gray-50/30 text-xs font-bold text-gray-500 uppercase tracking-wider text-left">
                                    <th class="px-5 py-3.5">ID Pesanan</th>
                                    <th class="px-4 py-3.5">Layanan</th>
                                    <th class="px-4 py-3.5">Tanggal Pesan</th>
                                    <th class="px-4 py-3.5 text-center">Status</th>
                                    <th class="px-5 py-3.5 text-right">Total Tagihan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 font-medium">
                                @foreach($orders as $pesanan)
                                <tr class="hover:bg-gray-50/60 transition-colors">
                                    <td class="px-5 py-4 align-middle">
                                        <span class="font-mono font-bold text-gray-900 block">#{{ $pesanan->id_pesanan ?? 'PES-' . $pesanan->id }}</span>
                                        @if($pesanan->catatan)
                                            <span class="text-xs text-gray-400 truncate max-w-xs block mt-0.5">{{ $pesanan->catatan }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 align-middle">
                                        @php
                                            $jenisKode = optional($pesanan->jenis_pesanan)->kode_jenis ?? '';
                                            $jenisNama = optional($pesanan->jenis_pesanan)->nama_jenis ?? 'Pesanan';
                                        @endphp
                                        <span class="inline-flex items-center gap-1 text-xs font-bold px-2.5 py-1 rounded-lg {{ $pesanan->jenis_pesanan_id == 2 ? 'bg-purple-100 text-purple-800' : ($pesanan->jenis_pesanan_id == 3 ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800') }}">
                                            {{ $jenisNama }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 align-middle text-gray-700 text-xs">
                                        <div class="font-bold text-gray-900">{{ $pesanan->tanggal_pesanan ? \Carbon\Carbon::parse($pesanan->tanggal_pesanan)->translatedFormat('d M Y') : '-' }}</div>
                                        <div class="text-gray-400 mt-0.5">{{ $pesanan->tanggal_pesanan ? \Carbon\Carbon::parse($pesanan->tanggal_pesanan)->format('H:i') . ' WIB' : '-' }}</div>
                                    </td>
                                    <td class="px-4 py-4 align-middle text-center">
                                        @php
                                            $namaStatus = optional($pesanan->status_pesanan)->nama_status ?? 'Menunggu';
                                            $badgeColor = match($pesanan->status_pesanan_id) {
                                                1 => 'warning',
                                                2, 3, 4 => 'primary',
                                                5 => 'success',
                                                6 => 'danger',
                                                default => 'gray'
                                            };
                                        @endphp
                                        <x-ui.badge :color="$badgeColor" size="sm">
                                            {{ $namaStatus }}
                                        </x-ui.badge>
                                    </td>
                                    <td class="px-5 py-4 align-middle text-right font-extrabold text-gray-900">
                                        Rp {{ number_format($pesanan->total_tagihan ?? 0, 0, ',', '.') }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="p-12 text-center text-gray-500">
                        <x-heroicon-o-shopping-bag class="w-12 h-12 mx-auto text-gray-300 mb-3" />
                        <p class="text-sm font-semibold text-gray-700">Belum ada riwayat pesanan</p>
                        <p class="text-xs text-gray-400 mt-1">Konsumen ini belum memiliki transaksi pemesanan.</p>
                    </div>
                    @endif
                </div>

            </div>
        </div>

    </div>
</div>
@endsection