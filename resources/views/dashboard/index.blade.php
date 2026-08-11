@extends('layouts.pos')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800 pb-10">
    <div class="w-full p-6 space-y-5">

        {{-- PAGE HEADER --}}
        <x-ui.page-header title="Dashboard" subtitle="Ringkasan performa operasional resto, katering, dan nasi box hari ini." :breadcrumbs="['Dashboard']" />

        {{-- ── STAT CARDS ── --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
            <div class="bg-white p-3.5 sm:p-4 rounded-xl border border-gray-200 shadow-xs flex flex-col justify-center">
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Pesanan Hari Ini</span>
                <span class="text-xl font-bold text-gray-900">{{ $pesananHariIni }}</span>
            </div>
            <div class="bg-white p-3.5 sm:p-4 rounded-xl border border-gray-200 shadow-xs flex flex-col justify-center">
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Pendapatan Hari Ini</span>
                <span class="text-xl font-bold text-gray-900 tabular-nums">Rp {{ number_format($pendapatanHariIni, 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- ── CHART (Full Width) --}}
        <div class="bg-white rounded-xl p-4 sm:p-5 border border-neutral-200 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3 pb-2.5 border-b border-neutral-100">
                <div>
                    <h2 class="font-bold text-neutral-900 text-sm">Tren Pendapatan (7 Hari Terakhir)</h2>
                    <p class="text-xs text-neutral-400 font-medium">Grafik akumulasi omset harian resto & catering</p>
                </div>
                <span class="px-2 py-0.5 bg-neutral-100 text-neutral-600 text-[11px] font-medium rounded border border-neutral-200">
                    Realtime
                </span>
            </div>
            <div class="h-48 sm:h-56 w-full relative">
                <canvas id="incomeChart"></canvas>
            </div>
        </div>

        {{-- ── TRANSAKSI TERBARU (Full Width) --}}
        <div class="bg-white rounded-xl p-4 sm:p-5 border border-neutral-200">
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
                                    <a href="{{ $p->url }}" title="Detail" class="w-7 h-7 rounded-full flex items-center justify-center bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors">
                                        <x-heroicon-o-eye class="w-3 h-3" />
                                    </a>
                                </div>
                            </td>
                        </x-ui.table.row>
                    @empty
                        <x-empty-state icon="clock" title="Belum ada transaksi" message="Belum ada transaksi pesanan hari ini." :colspan="6" />
                    @endforelse
                </tbody>
            </x-ui.table>
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
                    borderColor: '#0D3024',
                    backgroundColor: gradient,
                    borderWidth: 2,
                    pointBackgroundColor: '#FFFFFF',
                    pointBorderColor: '#0D3024',
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
                        backgroundColor: '#0D3024',
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
