@extends('layouts.pos')

@section('title', 'Detail ' . ($user->peran ? 'Karyawan' : 'Konsumen'))

@section('content')
<div class="p-4 md:p-8 w-full h-full flex flex-col bg-gray-100">
    <!-- Header Area -->
    <x-ui.page-header title="Detail {{ $user->peran ? 'Karyawan' : 'Konsumen' }}" subtitle="Informasi lengkap {{ strtolower($user->peran ? 'karyawan' : 'konsumen') }} dan riwayat aktivitas." class="mb-6" :breadcrumbs="['Manajemen Pengguna', $user->peran ? 'Data Karyawan' : 'Data Konsumen', 'Detail']">
        <x-slot:actions>
            <div class="flex gap-2">
                <button onclick="window.history.back()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2.5 px-5 rounded-lg flex items-center gap-2 shadow-sm transition-colors text-sm">
                    <x-heroicon-o-arrow-left class="w-4 h-4" />
                    Kembali
                </button>
            </div>
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
                            {{ strtoupper(substr($user->nama, 0, 1)) }}
                        </div>
                        <div>
                            <h2 class="text-xl font-bold">{{ $user->nama }}</h2>
                            <p class="text-white/90 text-sm">
                                @if($user->peran)
                                    {{ $user->peran->nama_peran }}
                                @else
                                    Konsumen
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Profile Details -->
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Nama</label>
                        <div class="text-gray-900 font-medium">{{ $user->nama }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Email</label>
                        <div class="text-gray-900">{{ $user->email ?? '-' }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Nomor WhatsApp</label>
                        <div class="text-gray-900">
                            @if($user->nomor_telepon)
                                <a href="https://wa.me/{{ str_replace(['+', '-', ' '], '', $user->nomor_telepon) }}" target="_blank" class="text-green-600 hover:text-green-800 flex items-center gap-1">
                                    {{ \App\Support\WhatsAppNumber::formatForDisplay($user->nomor_telepon) }}
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
                            @php
                                $alamat = null;
                                if ($user->pelanggan && $user->pelanggan->alamat) {
                                    $alamat = $user->pelanggan->alamat;
                                } elseif (isset($user->alamat) && $user->alamat) {
                                    $alamat = $user->alamat;
                                }
                            @endphp
                            {{ $alamat ?? 'Belum diisi' }}
                        </div>
                    </div>

                    @if($user->peran)


                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Bergabung</label>
                        <div class="text-gray-900">
                            @if($user->dibuat_pada)
                                {{ is_string($user->dibuat_pada) ? \Carbon\Carbon::parse($user->dibuat_pada)->translatedFormat('d M Y') : $user->dibuat_pada->translatedFormat('d M Y') }}
                            @else
                                -
                            @endif
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Terakhir Masuk</label>
                        <div class="text-gray-900">
                            @if($user->terakhir_masuk)
                                {{ is_string($user->terakhir_masuk) ? \Carbon\Carbon::parse($user->terakhir_masuk)->translatedFormat('d M Y H:i') : $user->terakhir_masuk->translatedFormat('d M Y H:i') }}
                            @else
                                Belum pernah masuk
                            @endif
                        </div>
                    </div>
                    @else
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Tanggal Daftar</label>
                        <div class="text-gray-900">
                            @if($user->dibuat_pada)
                                {{ is_string($user->dibuat_pada) ? \Carbon\Carbon::parse($user->dibuat_pada)->translatedFormat('d M Y') : $user->dibuat_pada->translatedFormat('d M Y') }}
                            @else
                                -
                            @endif
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Status Akun</label>
                        <div>
                            @if($user->status_aktif)
                                <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 border border-emerald-100 py-1 px-3 rounded-full text-sm font-medium">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 bg-gray-100 text-gray-500 border border-gray-200 py-1 px-3 rounded-full text-sm font-medium">
                                    <span class="w-2 h-2 rounded-full bg-gray-400"></span>Nonaktif
                                </span>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Activity & Orders -->
        @if(!$user->peran)
        <div class="lg:col-span-2 space-y-6">
            <!-- Riwayat Pesanan -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Riwayat Pesanan</h3>
                    <p class="text-sm text-gray-500 mt-1">Daftar pesanan yang pernah dibuat</p>
                </div>

                @php
                    $pesananList = collect();
                    
                    // Cek apakah user punya data pelanggan dan pesanan
                    if ($user->pelanggan) {
                        $pesananList = $user->pelanggan->pesanan()->latest()->take(10)->get();
                    }
                    
                    // Fallback: cek pesanan langsung dari user_id di tabel pesanan
                    if ($pesananList->isEmpty()) {
                        $pesananList = collect();
                        // Bisa tambahkan query lain jika diperlukan
                    }
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
                                    <div class="font-medium text-gray-900">#{{ $pesanan->kode_pesanan ?? 'PES-' . $pesanan->id }}</div>
                                    <div class="text-xs text-gray-500">{{ $pesanan->jenis_pesanan ?? 'Pesanan' }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-gray-900">
                                        @if($pesanan->tanggal_pesanan)
                                            {{ is_string($pesanan->tanggal_pesanan) ? \Carbon\Carbon::parse($pesanan->tanggal_pesanan)->translatedFormat('d M Y') : $pesanan->tanggal_pesanan->translatedFormat('d M Y') }}
                                        @else
                                            -
                                        @endif
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        @if($pesanan->tanggal_pesanan)
                                            {{ is_string($pesanan->tanggal_pesanan) ? \Carbon\Carbon::parse($pesanan->tanggal_pesanan)->format('H:i') : $pesanan->tanggal_pesanan->format('H:i') }}
                                        @else
                                            -
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $statusColors = [
                                            'pending' => 'bg-yellow-50 text-yellow-700 border-yellow-100',
                                            'diproses' => 'bg-primary-soft text-primary border-primary/15',
                                            'selesai' => 'bg-green-50 text-green-700 border-green-100',
                                            'dibatalkan' => 'bg-red-50 text-red-700 border-red-100',
                                        ];
                                        $statusColor = $statusColors[$pesanan->status_pesanan ?? 'pending'] ?? 'bg-gray-50 text-gray-700 border-gray-100';
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium border {{ $statusColor }}">
                                        {{ ucfirst($pesanan->status_pesanan ?? 'Pending') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="font-medium text-gray-900">Rp {{ number_format($pesanan->total_harga ?? 0, 0, ',', '.') }}</div>
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
                            <p class="text-2xl font-bold text-gray-900">{{ $pesananList->where('status_pesanan', 'selesai')->count() }}</p>
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
                            <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($pesananList->sum('total_harga') ?? 0, 0, ',', '.') }}</p>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-emerald-50 flex items-center justify-center">
                            <x-heroicon-o-banknotes class="w-5 h-5 text-emerald-600" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection