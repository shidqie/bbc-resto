@extends('layouts.pos')
@section('title', 'Penyesuaian Stok')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800">
    <div class="w-full p-6 space-y-5">

        {{-- PAGE HEADER --}}
        <x-ui.page-header
            title="Penyesuaian Stok"
            subtitle="Koreksi stok untuk barang rusak, busuk, atau selisih opname fisik."
            :breadcrumbs="['Persediaan', 'Penyesuaian Stok']">
            <x-slot:actions>
                <a href="{{ route('penyesuaian-stok.create') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-gray-900 rounded-lg px-3 py-2 hover:bg-gray-800 transition-colors">
                    <x-heroicon-o-plus class="w-3 h-3" />
                    Buat Penyesuaian
                </a>
            </x-slot:actions>
        </x-ui.page-header>

        {{-- Stat Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <x-ui.stat-card label="Total Penyesuaian" :value="$stats['total']" icon="clipboard-document-list" color="blue" />
            <x-ui.stat-card label="Disetujui" :value="$stats['disetujui']" icon="check-circle" color="green" />
            <x-ui.stat-card label="Menunggu" :value="$stats['menunggu']" icon="clock" color="orange" />
        </div>

        <x-ui.alert />

        {{-- Warning Box --}}
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-start gap-3">
            <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" />
            <div>
                <p class="text-sm font-semibold text-amber-800">Perhatian</p>
                <p class="text-sm text-amber-700 mt-0.5">Halaman ini <strong>satu-satunya tempat</strong> untuk melakukan koreksi manual stok. Gunakan hanya untuk kasus di luar alur normal: barang rusak, busuk, atau perbedaan saat opname fisik.</p>
            </div>
        </div>

        {{-- Table --}}
        <x-ui.data-table :paginator="$penyesuaians">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        <th class="px-4 py-3 text-left w-12">No</th>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-left">Nama Bahan</th>
                        <th class="px-4 py-3 text-right">Stok Sistem</th>
                        <th class="px-4 py-3 text-right">Stok Fisik</th>
                        <th class="px-4 py-3 text-right">Selisih</th>
                        <th class="px-4 py-3 text-left">Alasan</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($penyesuaians as $adj)
                    <tr class="hover:bg-gray-50/60 transition-colors group">
                        <td class="px-4 py-3 text-sm text-gray-500 font-medium">{{ $penyesuaians->firstItem() + $loop->index }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ \Carbon\Carbon::parse($adj->penyesuaian_stok->tanggal_penyesuaian)->format('d M Y') }}</td>
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $adj->bahan_baku->nama_bahan ?? '-' }}</td>
                        <td class="px-4 py-3 text-right font-medium text-gray-600">{{ number_format($adj->jumlah_sistem, 2) }} <span class="text-xs text-gray-400">{{ $adj->bahan_baku->satuan->nama_satuan ?? '' }}</span></td>
                        <td class="px-4 py-3 text-right font-medium text-gray-900">{{ number_format($adj->jumlah_fisik, 2) }} <span class="text-xs text-gray-400">{{ $adj->bahan_baku->satuan->nama_satuan ?? '' }}</span></td>
                        <td class="px-4 py-3 text-right font-bold {{ $adj->jumlah_selisih < 0 ? 'text-red-600' : ($adj->jumlah_selisih > 0 ? 'text-emerald-600' : 'text-gray-500') }}">
                            {{ $adj->jumlah_selisih > 0 ? '+' : '' }}{{ number_format($adj->jumlah_selisih, 2) }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-sm text-gray-700 max-w-xs line-clamp-1">{{ $adj->penyesuaian_stok->alasan ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('penyesuaian-stok.show', $adj->penyesuaian_stok_id) }}" title="Detail" class="w-7 h-7 rounded-full flex items-center justify-center bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors">
                                    <x-heroicon-o-eye class="w-3 h-3" />
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <x-empty-state icon="clipboard-document-list" title="Belum ada penyesuaian stok" message="Penyesuaian akan muncul di sini setelah dibuat." :colspan="8" />
                    @endforelse
                </tbody>
            </table>
        </x-ui.data-table>

    </div>
</div>
@endsection
