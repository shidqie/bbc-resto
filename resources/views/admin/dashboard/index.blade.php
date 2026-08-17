@extends('layouts.pos')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800 pb-10">
    <div class="w-full p-6 space-y-5">

        {{-- PAGE HEADER --}}
        <x-ui.page-header title="Dashboard" subtitle="Ringkasan performa operasional resto, katering, dan nasi box hari ini." :breadcrumbs="['Dashboard']" />

        {{-- ── STAT CARDS ── --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5">
            <div class="bg-white p-3.5 sm:p-4 rounded-xl border border-gray-200 shadow-xs flex flex-col justify-center">
                <span class="text-[10px] sm:text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Pesanan Hari Ini</span>
                <span class="text-lg sm:text-xl font-bold text-gray-900">{{ $pesananHariIni }}</span>
            </div>
            <div class="bg-white p-3.5 sm:p-4 rounded-xl border border-gray-200 shadow-xs flex flex-col justify-center">
                <span class="text-[10px] sm:text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Pendapatan Hari Ini</span>
                <span class="text-lg sm:text-xl font-bold text-gray-900 tabular-nums">Rp {{ number_format($pendapatanHariIni, 0, ',', '.') }}</span>
            </div>
            <div class="bg-white p-3.5 sm:p-4 rounded-xl border border-gray-200 shadow-xs flex flex-col justify-center">
                <span class="text-[10px] sm:text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Pesanan Menunggu</span>
                <span class="text-lg sm:text-xl font-bold text-amber-500">{{ $pesananPending }}</span>
            </div>
            <div class="bg-white p-3.5 sm:p-4 rounded-xl border border-gray-200 shadow-xs flex flex-col justify-center">
                <span class="text-[10px] sm:text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Stok Menipis</span>
                <span class="text-lg sm:text-xl font-bold text-rose-500">{{ $stokMenipis }}</span>
            </div>
        </div>

        {{-- ── CONTENT ROW (Chart & Transaksi Sejajar) ── --}}
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
            
            {{-- ── CHART --}}
            <div class="bg-white rounded-xl p-4 sm:p-5 border border-neutral-200 flex flex-col justify-between overflow-hidden">
            <div class="flex items-center justify-between mb-3 pb-2.5 border-b border-neutral-100">
                <div>
                    <h2 class="font-bold text-neutral-900 text-sm flex items-center gap-2">
                        Tren Pendapatan & Pesanan (7 Hari Terakhir)
                        <span class="px-2 py-0.5 bg-sky-50 text-sky-600 text-[10px] font-bold rounded-full uppercase tracking-wider">{{ \Carbon\Carbon::now()->translatedFormat('F Y') }}</span>
                    </h2>
                    <p class="text-xs text-neutral-400 font-medium mt-1">Grafik akumulasi omset harian serta jumlah pesanan Dine-in, Katering, dan Nasi Box</p>
                </div>
                <span class="px-2 py-0.5 bg-neutral-100 text-neutral-600 text-[11px] font-medium rounded border border-neutral-200">
                    Realtime
                </span>
            </div>
            <div class="flex-1 w-full relative min-h-[300px]">
                <canvas id="incomeChart"></canvas>
            </div>
        </div>

            {{-- ── TRANSAKSI TERBARU --}}
            <div class="bg-white rounded-xl p-4 sm:p-5 border border-neutral-200 overflow-hidden flex flex-col">
            <div class="flex justify-between items-center mb-3 pb-2.5 border-b border-neutral-100">
                <div>
                    <h3 class="font-bold text-neutral-900 text-sm">Transaksi Terbaru</h3>
                    <p class="text-xs text-neutral-400 font-medium">Pesanan resto & catering yang baru masuk</p>
                </div>
                <a href="{{ route('admin.pesanan.index') }}" class="text-xs font-semibold text-neutral-600 hover:text-neutral-900 shrink-0">
                    Lihat Semua &rarr;
                </a>
            </div>

            <x-ui.table class="min-w-[700px]">
                <x-ui.table.header>
                    <th class="px-4 py-3.5 text-left">No. Kode</th>
                    <th class="px-4 py-3.5 text-left">Jenis Pesanan</th>
                    <th class="px-4 py-3.5 text-left">Waktu</th>
                    <th class="px-4 py-3.5 text-left">Total Tagihan</th>
                    <th class="px-4 py-3.5 text-left">Status</th>
                    <th class="px-4 py-3.5 text-center">Aksi</th>
                </x-ui.table.header>
                <tbody class="divide-y divide-gray-100">
                    @forelse($pesananTerbaru as $p)
                        @php
                            $badgeColor = 'warning';
                            $badgeText = 'MENUNGGU';

                            if ($p->status === 'Selesai') {
                                $badgeColor = 'success';
                                $badgeText = 'SELESAI';
                            } elseif ($p->status === 'Dibatalkan') {
                                $badgeColor = 'danger';
                                $badgeText = 'BATAL';
                            }
                        @endphp
                        <x-ui.table.row>
                            <td class="px-4 py-4 align-middle font-semibold text-gray-900">
                                {{ $p->no }}
                            </td>
                            <td class="px-4 py-4 align-middle font-medium text-gray-700">
                                {{ $p->jenis }}
                            </td>
                            <td class="px-4 py-4 align-middle text-gray-500">
                                {{ \Carbon\Carbon::parse($p->tanggal)->diffForHumans() }}
                            </td>
                            <td class="px-4 py-4 align-middle font-semibold text-gray-900">
                                Rp {{ number_format($p->total, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-4 align-middle">
                                <x-ui.badge :color="$badgeColor" size="sm">{{ $badgeText }}</x-ui.badge>
                            </td>
                            <td class="px-4 py-4 align-middle text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <x-ui.action-button href="{{ $p->url }}" title="Detail">
                                        <x-heroicon-o-eye class="w-4 h-4" />
                                    </x-ui.action-button>
                                </div>
                            </td>
                        </x-ui.table.row>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-ui.empty-state icon="clock" title="Belum ada transaksi" message="Belum ada transaksi pesanan hari ini." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>
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
        const dataPendapatan = {!! json_encode($dataPendapatan) !!};
        const dataDineIn = {!! json_encode($dataDineIn) !!};
        const dataCatering = {!! json_encode($dataCatering) !!};
        const dataNasiBox = {!! json_encode($dataNasiBox) !!};
        
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(59, 130, 246, 0.2)');
        gradient.addColorStop(1, 'rgba(59, 130, 246, 0)');
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        type: 'line',
                        label: 'Pendapatan (Rp)',
                        data: dataPendapatan,
                        borderColor: '#3B82F6',
                        backgroundColor: gradient,
                        borderWidth: 2.5,
                        pointBackgroundColor: '#FFFFFF',
                        pointBorderColor: '#3B82F6',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        fill: true,
                        tension: 0.4,
                        yAxisID: 'y'
                    },
                    {
                        type: 'bar',
                        label: 'Dine In',
                        data: dataDineIn,
                        backgroundColor: '#8B5CF6',
                        borderRadius: 4,
                        barThickness: 24,
                        yAxisID: 'y1'
                    },
                    {
                        type: 'bar',
                        label: 'Katering',
                        data: dataCatering,
                        backgroundColor: '#16A34A',
                        borderRadius: 4,
                        barThickness: 24,
                        yAxisID: 'y1'
                    },
                    {
                        type: 'bar',
                        label: 'Nasi Box',
                        data: dataNasiBox,
                        backgroundColor: '#D97706',
                        borderRadius: 4,
                        barThickness: 24,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { 
                        display: true, 
                        position: 'top', 
                        labels: { usePointStyle: true, boxWidth: 8, font: { size: 11 } } 
                    },
                    tooltip: {
                        backgroundColor: '#0D3024',
                        padding: 10,
                        titleFont: { size: 12, weight: 'bold' },
                        bodyFont: { size: 13, weight: 'bold' },
                        callbacks: {
                            label: function(context) {
                                if (context.dataset.yAxisID === 'y') {
                                    return context.dataset.label + ': Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                                }
                                return context.dataset.label + ': ' + context.parsed.y + ' Pesanan';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        stacked: true,
                        grid: { display: false },
                        ticks: { font: { size: 10 }, color: '#A3A3A3', maxRotation: 0, minRotation: 0 }
                    },
                    y: {
                        beginAtZero: true,
                        type: 'linear',
                        display: true,
                        position: 'left',
                        grid: { color: '#F3F4F6' },
                        ticks: {
                            font: { size: 11 },
                            color: '#6B7280',
                            callback: function(value) {
                                if(value >= 1000000) return 'Rp ' + (value / 1000000) + ' Jt';
                                if(value >= 1000) return 'Rp ' + (value / 1000) + ' Rb';
                                return 'Rp ' + value;
                            }
                        }
                    },
                    y1: {
                        beginAtZero: true,
                        stacked: true,
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: { display: false },
                        ticks: {
                            stepSize: 1,
                            font: { size: 11 },
                            color: '#6B7280',
                            callback: function(value) {
                                return value + ' trx';
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
