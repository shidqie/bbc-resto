@extends('layouts.pos')

@section('title', 'Detail Data Konsumen')

@section('content')
<div class="p-4 md:p-8 w-full h-full flex flex-col bg-gray-100">
    <!-- Header Area -->
    <x-ui.page-header title="Detail Data Konsumen" subtitle="Informasi lengkap konsumen dan riwayat aktivitas." class="mb-6" :breadcrumbs="['Manajemen Pengguna', 'Data Konsumen', 'Detail']">
        <x-slot:actions>
            <button onclick="window.history.back()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2.5 px-5 rounded-lg flex items-center gap-2 shadow-sm transition-colors text-sm">
                <x-heroicon-o-arrow-left class="w-4 h-4" />
                Kembali
            </button>
        </x-slot:actions>
    </x-ui.page-header>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Profile Card -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <!-- Profile Header -->
                <div class="bg-gradient-to-br from-primary to-primary/80 p-6 text-white">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center font-bold text-xl">
                            {{ strtoupper(substr($pelanggan->nama, 0, 1)) }}
                        </div>
                        <div>
                            <h2 class="text-xl font-bold">{{ $pelanggan->nama }}</h2>
                            <p class="text-white/90 text-sm">
                                Konsumen
                            </p>
                        </div>
                    </div>
                </div>
                <!-- Profile Details -->
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Nama</label>
                        <div class="text-gray-900 font-medium">{{ $pelanggan->nama }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Email</label>
                        <div class="text-gray-900">{{ $pelanggan->email ?? '-' }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Nomor WhatsApp</label>
                        <div class="text-gray-900">
                            @if($pelanggan->nomor_telepon)
                                <a href="https://wa.me/{{ str_replace(['+', '-', ' '], '', $pelanggan->nomor_telepon) }}" target="_blank" class="text-green-600 hover:text-green-800 flex items-center gap-1">
                                    {{ \App\Support\WhatsAppNumber::formatForDisplay($pelanggan->nomor_telepon) }}
                                    <x-heroicon-o-arrow-top-right-on-square class="w-3 h-3" />
                                </a>
                            @else
                                -
                            @endif
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Alamat Default</label>
                        <div class="text-gray-900">
                            {{ $pelanggan->alamat ?? 'Belum diisi' }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Tanggal Daftar</label>
                        <div class="text-gray-900">{{ $pelanggan->dibuat_pada ? $pelanggan->dibuat_pada->translatedFormat('d M Y') : '-' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity & Orders -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Riwayat Pesanan -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Riwayat Pesanan</h3>
                    <p class="text-sm text-gray-500 mt-1">Daftar pesanan yang pernah dibuat</p>
                </div>

                @php
                    $pesananList = $pelanggan->pesanan()->latest()->take(10)->get();
                @endphp

                @if($pesananList->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 text-sm font-semibold text-gray-500 uppercase tracking-wide">
                                <th class="px-4 py-3 text-left">Pesanan</th>
                                <th class="px-4 py-3 text-left">Tanggal</th>
                                <th class="px-4 py-3 text-left">Status</th>
                                <th class="px-4 py-3 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($pesananList as $pesanan)
                            <tr class="hover:bg-gray-50/60 transition-colors">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900">#{{ $pesanan->id_pesanan ?? 'PES-' . $pesanan->id }}</div>
                                    <div class="text-xs text-gray-500">{{ optional($pesanan->jenis_pesanan)->nama_jenis ?? 'Pesanan' }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-gray-900">{{ $pesanan->tanggal_pesanan ? \Carbon\Carbon::parse($pesanan->tanggal_pesanan)->translatedFormat('d M Y') : '-' }}</div>
                                    <div class="text-xs text-gray-500">{{ $pesanan->tanggal_pesanan ? \Carbon\Carbon::parse($pesanan->tanggal_pesanan)->format('H:i') : '-' }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $kodeStatus = optional($pesanan->status_pesanan)->kode_status ?? 'pending';
                                        $namaStatus = optional($pesanan->status_pesanan)->nama_status ?? 'Pending';
                                        $statusColors = [
                                            'pending' => 'bg-yellow-50 text-yellow-700 border-yellow-100',
                                            'diproses' => 'bg-primary-soft text-primary border-primary/15',
                                            'selesai' => 'bg-green-50 text-green-700 border-green-100',
                                            'dibatalkan' => 'bg-red-50 text-red-700 border-red-100',
                                        ];
                                        $statusColor = $statusColors[$kodeStatus] ?? 'bg-gray-50 text-gray-700 border-gray-100';
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium border {{ $statusColor }}">
                                        {{ ucfirst($namaStatus) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="font-medium text-gray-900">Rp {{ number_format($pesanan->total_tagihan ?? 0, 0, ',', '.') }}</div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="p-6 text-center text-gray-500">
                    <x-heroicon-o-shopping-bag class="w-12 h-12 mx-auto text-gray-300 mb-3" />
                    <p>Belum ada riwayat pesanan</p>
                </div>
                @endif
            </div>

            <!-- Summary Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Total Pesanan</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $pesananList->count() }}</p>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-primary-soft flex items-center justify-center">
                            <x-heroicon-o-shopping-bag class="w-5 h-5 text-primary" />
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Pesanan Selesai</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $pesananList->filter(fn($p) => optional($p->status_pesanan)->kode_status === 'selesai')->count() }}</p>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-green-50 flex items-center justify-center">
                            <x-heroicon-o-check-circle class="w-5 h-5 text-green-600" />
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Total Nilai</p>
                            <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($pesananList->sum('total_tagihan') ?? 0, 0, ',', '.') }}</p>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-emerald-50 flex items-center justify-center">
                            <x-heroicon-o-banknotes class="w-5 h-5 text-emerald-600" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection