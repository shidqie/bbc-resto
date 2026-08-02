@extends('layouts.pos')

@section('content')
<div class="min-h-screen bg-[#F8FAFC] text-[#111827] font-sans p-6 md:p-8">
    <div class="space-y-6">

        {{-- ── STAT CARDS ── --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            {{-- Stat 1: Pesanan Hari Ini --}}
            <div class="bg-white rounded-2xl p-5 border border-neutral-200 flex flex-col gap-3">
                <div class="flex items-center justify-between text-neutral-500">
                    <p class="text-xs font-medium">Pesanan Hari Ini</p>
                    <x-heroicon-o-shopping-bag class="w-4 h-4 text-neutral-400" />
                </div>
                <div>
                    <p class="text-2xl font-semibold text-neutral-900">{{ $pesananHariIni }}</p>
                </div>
            </div>

            {{-- Stat 2: Pendapatan Hari Ini --}}
            <div class="bg-white rounded-2xl p-5 border border-neutral-200 flex flex-col gap-3">
                <div class="flex items-center justify-between text-neutral-500">
                    <p class="text-xs font-medium">Pendapatan Hari Ini</p>
                    <x-heroicon-o-banknotes class="w-4 h-4 text-neutral-400" />
                </div>
                <div>
                    <p class="text-2xl font-semibold text-neutral-900">Rp {{ number_format($pendapatanHariIni, 0, ',', '.') }}</p>
                </div>
            </div>

            {{-- Stat 3: Pesanan Pending --}}
            <div class="bg-white rounded-2xl p-5 border border-neutral-200 flex flex-col gap-3">
                <div class="flex items-center justify-between text-neutral-500">
                    <p class="text-xs font-medium">Pesanan Pending</p>
                    <x-heroicon-o-clock class="w-4 h-4 text-neutral-400" />
                </div>
                <div>
                    <p class="text-2xl font-semibold text-neutral-900">{{ $pesananPending }}</p>
                </div>
            </div>

            {{-- Stat 4: Stok Menipis --}}
            <div class="bg-white rounded-2xl p-5 border border-neutral-200 flex flex-col gap-3">
                <div class="flex items-center justify-between text-neutral-500">
                    <p class="text-xs font-medium">Stok Menipis</p>
                    <x-heroicon-o-cube class="w-4 h-4 text-neutral-400" />
                </div>
                <div>
                    <p class="text-2xl font-semibold text-neutral-900">{{ $stokMenipis }}</p>
                </div>
            </div>
        </div>

        {{-- ── CHART --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- 7-Day Revenue Trend Chart (2 Cols) --}}
            <div class="lg:col-span-2 bg-white rounded-2xl p-6 border border-neutral-200 flex flex-col justify-between">
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-neutral-100">
                    <div>
                        <h2 class="font-semibold text-neutral-900 text-base">Tren Pendapatan (7 Hari Terakhir)</h2>
                        <p class="text-xs text-neutral-400 font-medium">Grafik akumulasi omset harian resto & catering</p>
                    </div>
                    <span class="px-2.5 py-1 bg-neutral-100 text-neutral-600 text-xs font-medium rounded border border-neutral-200">
                        Realtime
                    </span>
                </div>
                <div class="h-64 w-full relative">
                    <canvas id="incomeChart"></canvas>
                </div>
            </div>

        </div>

        {{-- ── TRANSAKSI TERBARU & PERINGATAN STOK --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Transaksi Terbaru (2 Cols) --}}
            <div class="lg:col-span-2 bg-white rounded-2xl p-6 border border-neutral-200">
                <div class="flex justify-between items-center mb-4 pb-3 border-b border-neutral-100">
                    <div>
                        <h3 class="font-semibold text-neutral-900 text-base">Transaksi Terbaru</h3>
                        <p class="text-xs text-neutral-400 font-medium">Pesanan resto & catering yang baru masuk</p>
                    </div>
                    <a href="{{ route('admin.pesanan.index') }}" class="text-xs font-medium text-neutral-600 hover:text-neutral-900 shrink-0">
                        Lihat Semua &rarr;
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 text-xs font-semibold text-gray-400 uppercase tracking-wide">
                                <th class="px-4 py-3 text-left">No. Kode</th>
                                <th class="px-4 py-3 text-left">Jenis Pesanan</th>
                                <th class="px-4 py-3 text-left">Waktu</th>
                                <th class="px-4 py-3 text-left">Total Tagihan</th>
                                <th class="px-4 py-3 text-left">Status</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($pesananTerbaru as $p)
                                @php
                                    $badgeBg = 'bg-amber-50 text-amber-700 border-amber-200';
                                    $badgeText = 'MENUNGGU';

                                    if ($p->status === 'Selesai') {
                                        $badgeBg = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                                        $badgeText = 'SELESAI';
                                    } elseif ($p->status === 'Dibatalkan') {
                                        $badgeBg = 'bg-red-50 text-red-700 border-red-200';
                                        $badgeText = 'BATAL';
                                    }
                                @endphp
                                <tr class="hover:bg-gray-50/60 transition-colors">
                                    <td class="px-4 py-3 font-semibold text-gray-900">
                                        {{ $p->no }}
                                    </td>
                                    <td class="px-4 py-3 font-medium text-gray-700">
                                        {{ $p->jenis }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-500 text-sm">
                                        {{ \Carbon\Carbon::parse($p->tanggal)->diffForHumans() }}
                                    </td>
                                    <td class="px-4 py-3 font-semibold text-gray-900">
                                        Rp {{ number_format($p->total, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold border shrink-0 {{ $badgeBg }}">
                                            {{ $badgeText }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <a href="{{ $p->url }}" title="Detail" class="w-7 h-7 rounded-full flex items-center justify-center bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors">
                                                <x-heroicon-o-eye class="w-3 h-3" />
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-12 text-gray-400">
                                        Belum ada transaksi pesanan hari ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Peringatan Stok (1 Col) --}}
            <div class="bg-white rounded-2xl p-6 border border-neutral-200 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-center mb-4 pb-3 border-b border-neutral-100">
                        <h3 class="font-semibold text-neutral-900 text-base">Peringatan Stok</h3>
                        <a href="{{ route('bahan-baku.index') }}" class="text-xs font-medium text-neutral-600 hover:text-neutral-900 shrink-0">
                            Lihat Semua &rarr;
                        </a>
                    </div>

                    <div class="space-y-3">
                        @forelse($listStokMenipis as $stok)
                            <div class="flex items-center justify-between p-3 rounded-xl bg-neutral-50 border border-neutral-100">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-9 h-9 rounded-full bg-neutral-100 text-neutral-500 flex items-center justify-center shrink-0">
                                        <x-heroicon-o-exclamation-triangle class="w-4 h-4" />
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-medium text-neutral-900 text-xs truncate">{{ $stok->nama_bahan }}</p>
                                        <p class="text-[10px] text-neutral-400 font-medium">Min: {{ $stok->stok_minimal }} {{ $stok->satuan->nama_satuan ?? '' }}</p>
                                    </div>
                                </div>
                                <span class="px-2.5 py-1 bg-neutral-100 text-neutral-700 font-semibold text-xs rounded border border-neutral-200 shrink-0">
                                    {{ $stok->jumlah_stok }} {{ $stok->satuan->nama_satuan ?? '' }}
                                </span>
                            </div>
                        @empty
                            <div class="py-8 text-center text-neutral-400 text-xs space-y-2">
                                <div class="w-10 h-10 rounded-full bg-neutral-100 text-neutral-500 flex items-center justify-center mx-auto text-lg">
                                    <x-heroicon-o-check-circle class="w-5 h-5" />
                                </div>
                                <p class="font-medium text-neutral-700">Semua stok aman</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>

{{-- Chart.js Script --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('incomeChart').getContext('2d');
        const labels = {!! json_encode($labels) !!};
        const data = {!! json_encode($dataPendapatan) !!};
        
        const gradient = ctx.createLinearGradient(0, 0, 0, 240);
        gradient.addColorStop(0, 'rgba(23, 23, 23, 0.12)');
        gradient.addColorStop(1, 'rgba(23, 23, 23, 0)');
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Pendapatan (Rp)',
                    data: data,
                    borderColor: '#171717',
                    backgroundColor: gradient,
                    borderWidth: 2,
                    pointBackgroundColor: '#FFFFFF',
                    pointBorderColor: '#171717',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.35
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#171717',
                        padding: 10,
                        titleFont: { size: 12, weight: 'bold' },
                        bodyFont: { size: 13, weight: 'bold' },
                        callbacks: {
                            label: function(context) {
                                return 'Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11 }, color: '#A3A3A3' }
                    },
                    y: {
                        grid: { color: '#F5F5F5' },
                        ticks: {
                            font: { size: 11 },
                            color: '#A3A3A3',
                            callback: function(value) {
                                if(value >= 1000000) return 'Rp ' + (value / 1000000) + ' Jt';
                                if(value >= 1000) return 'Rp ' + (value / 1000) + ' Rb';
                                return 'Rp ' + value;
                            }
                        }
                    }
                },
                interaction: { intersect: false, mode: 'index' },
            }
        });
    });
</script>
@endsection
