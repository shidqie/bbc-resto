@extends('layouts.pos')

@section('title', 'Detail Pengguna')

@section('content')
<div class="flex flex-col h-full bg-white">
    {{-- Header --}}
    <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 shrink-0 bg-white sticky top-0 z-10 shadow-sm">
        <div class="flex items-center gap-4">
            <a href="{{ route('users.index') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-50 text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors border border-slate-200">
                <x-heroicon-o-arrow-left class="w-5 h-5" />
            </a>
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-base shrink-0">
                    {{ strtoupper(substr($user->nama, 0, 1)) }}
                </div>
                <div>
                    <h3 class="font-bold text-gray-900 text-lg flex items-center gap-2">
                        {{ $user->nama }}
                        @if($user->status_aktif)
                            <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 border border-emerald-100 px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Aktif
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 bg-gray-100 text-gray-500 border border-gray-200 px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider">
                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>Nonaktif
                            </span>
                        @endif
                    </h3>
                    <p class="text-xs text-gray-500 mt-1 flex items-center gap-1">
                        <span class="font-semibold text-gray-700 bg-gray-100 px-2 py-0.5 rounded-xl">{{ $user->peran->nama_peran ?? '-' }}</span>
                        &bull; {{ $user->email }}
                    </p>
                </div>
            </div>
        </div>
        <a href="{{ route('users.index', ['type' => $user->isPelanggan() ? 'pelanggan' : 'pegawai']) }}" class="hidden md:inline-flex items-center gap-2 text-sm font-medium text-gray-600 hover:text-gray-900 bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-2xl px-4 py-2 transition-colors">
            <x-heroicon-o-arrow-left class="w-4 h-4" />
            Kembali
        </a>
    </div>

    {{-- Body --}}
    <div class="flex-1 overflow-y-auto p-6 space-y-6 bg-gray-50/50">

        {{-- Info Panel --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 bg-white p-5 rounded-[2.25rem] border border-gray-200 shadow-sm">
            <div>
                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1 block"><x-heroicon-o-user class="mr-1 w-5 h-5" /> Nama Lengkap</label>
                <p class="text-sm font-bold text-gray-900">{{ $user->nama }}</p>
            </div>
            <div>
                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1 block"><x-heroicon-o-phone class="mr-1 w-5 h-5" /> Nomor HP</label>
                <p class="text-sm font-bold text-gray-900">{{ $user->nomor_telepon ?? '-' }}</p>
            </div>
            <div>
                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1 block"><x-heroicon-o-envelope class="mr-1 w-5 h-5" /> Email</label>
                <p class="text-sm font-bold text-gray-900 truncate">{{ $user->email }}</p>
            </div>
            <div>
                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1 block"><x-heroicon-o-shield-check class="mr-1 w-5 h-5" /> Role / Hak Akses</label>
                <p class="text-sm font-bold text-gray-900">{{ $user->peran->nama_peran ?? '-' }}</p>
            </div>
            <div>
                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1 block"><x-heroicon-o-clock class="mr-1 w-5 h-5" /> Terakhir Masuk</label>
                <p class="text-sm font-bold text-gray-900">{{ $user->terakhir_masuk ? $user->terakhir_masuk->format('d M Y H:i') : '-' }}</p>
            </div>
            <div>
                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1 block"><x-heroicon-o-calendar class="mr-1 w-5 h-5" /> Bergabung Sejak</label>
                <p class="text-sm font-bold text-gray-900">{{ $user->dibuat_pada->format('d M Y') }}</p>
            </div>
            <div>
                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1 block"><x-heroicon-o-receipt-percent class="mr-1 w-5 h-5" /> Total Pesanan</label>
                <p class="text-sm font-bold text-gray-900">{{ number_format($pesananCount, 0, ',', '.') }} pesanan</p>
            </div>
            <div>
                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1 block"><x-heroicon-o-identification class="mr-1 w-5 h-5" /> ID Akun</label>
                <p class="text-sm font-bold text-gray-900">#{{ $user->id }}</p>
            </div>
        </div>

        {{-- Riwayat Pesanan --}}
        <div class="bg-white rounded-[2.25rem] border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 bg-slate-50/50 flex justify-between items-center">
                <h4 class="text-sm font-bold text-gray-900"><x-heroicon-o-receipt-percent class="mr-1.5 text-gray-400 w-5 h-5" /> Riwayat Pesanan Terbaru</h4>
                <span class="text-xs font-bold bg-white border border-gray-200 px-2.5 py-1 rounded-xl text-gray-600">{{ $pesananDineIn->count() + $pesananCatering->count() }} ditampilkan</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-xs font-semibold text-gray-400 uppercase tracking-wide">
                            <th class="px-5 py-3 text-left">Nomor Pesanan</th>
                            <th class="px-5 py-3 text-left">Tanggal</th>
                            <th class="px-5 py-3 text-left">Jenis</th>
                            <th class="px-5 py-3 text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($pesananDineIn->merge($pesananCatering) as $pesanan)
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="px-5 py-3 font-medium text-gray-900">{{ $pesanan->nomor_pesanan }}</td>
                            <td class="px-5 py-3 text-gray-600">{{ \Carbon\Carbon::parse($pesanan->dibuat_pada)->format('d M Y H:i') }}</td>
                            <td class="px-5 py-3">
                                <span class="font-semibold text-gray-700 bg-gray-100 px-2 py-0.5 rounded-xl text-xs">{{ optional($pesanan->jenis_pesanan)->nama_jenis ?? '-' }}</span>
                            </td>
                            <td class="px-5 py-3">
                                <span class="px-2.5 py-1 bg-slate-100 text-slate-700 border border-slate-200 rounded-2xl text-[10px] font-extrabold uppercase tracking-wider">
                                    {{ optional($pesanan->status_pesanan)->nama_status ?? 'Unknown' }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-5 py-10 text-center text-gray-400">Belum ada riwayat pesanan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Aksi --}}
        @if(!$user->isPelanggan())
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('users.index') }}?edit={{ $user->id }}" class="inline-flex items-center gap-2 text-sm font-medium text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200 rounded-2xl px-4 py-2.5 transition-colors">
                <x-heroicon-o-pencil-square class="w-4 h-4" />
                Ubah Data
            </a>
            <a href="{{ route('users.index') }}?reset={{ $user->id }}" class="inline-flex items-center gap-2 text-sm font-medium text-violet-700 bg-violet-50 hover:bg-violet-100 border border-violet-200 rounded-2xl px-4 py-2.5 transition-colors">
                <x-heroicon-o-key class="w-4 h-4" />
                Atur Ulang Kata Sandi
            </a>
        </div>
        @endif
    </div>
</div>
@endsection
