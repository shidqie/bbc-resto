@extends('layouts.pos')

@section('content')
<div class="flex-1 bg-gray-50/70 text-gray-800 pb-10">
    <div class="w-full p-4 sm:p-6 lg:p-8 space-y-6">

        {{-- PAGE HEADER --}}
        <x-ui.page-header title="Dashboard" subtitle="Ringkasan performa operasional resto, katering, dan nasi box hari ini." :breadcrumbs="['Dashboard']" />

        {{-- ── STAT CARDS ── --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Card 1: Pesanan Hari Ini --}}
            <div class="bg-white p-4 sm:p-5 rounded-2xl border border-gray-200/80 shadow-2xs hover:shadow-xs transition-shadow">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Pesanan Hari Ini</span>
                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">{{ $pesananHariIni }}</span>
                    <span class="text-xs text-gray-400 font-medium">Transaksi</span>
                </div>
            </div>

            {{-- Card 2: Pendapatan Hari Ini --}}
            <div class="bg-white p-4 sm:p-5 rounded-2xl border border-gray-200/80 shadow-2xs hover:shadow-xs transition-shadow">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Pendapatan Hari Ini</span>
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-1">
                    <span class="text-lg sm:text-2xl font-extrabold text-gray-900 tabular-nums tracking-tight">Rp {{ number_format($pendapatanHariIni, 0, ',', '.') }}</span>
                </div>
            </div>

            {{-- Card 3: Pesanan Menunggu --}}
            <div class="bg-white p-4 sm:p-5 rounded-2xl border border-gray-200/80 shadow-2xs hover:shadow-xs transition-shadow">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Pesanan Menunggu</span>
                    <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-2xl sm:text-3xl font-extrabold text-amber-600 tracking-tight">{{ $pesananPending }}</span>
                    <span class="text-xs text-amber-600/80 font-medium">Perlu Konfirmasi</span>
                </div>
            </div>

            {{-- Card 4: Stok Menipis --}}
            <div class="bg-white p-4 sm:p-5 rounded-2xl border border-gray-200/80 shadow-2xs hover:shadow-xs transition-shadow">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Stok Menipis</span>
                    <div class="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-2xl sm:text-3xl font-extrabold text-rose-600 tracking-tight">{{ $stokMenipis }}</span>
                    <span class="text-xs text-rose-600/80 font-medium">Bahan Baku</span>
                </div>
            </div>
        </div>

        {{-- ── CONTENT ROW (Chart & Transaksi Sejajar) ── --}}
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 items-start">
            
            {{-- ── REDESIGNED CHART CARD ── --}}
            <div class="bg-white rounded-2xl border border-gray-200/80 shadow-2xs p-5 sm:p-6 flex flex-col justify-between" x-data="dashboardChart()">
                
                {{-- Card Header --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3.5 pb-4 border-b border-gray-100">
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="font-extrabold text-gray-900 text-base tracking-tight" x-text="mode === 'income' ? 'Tren Pendapatan Harian' : (mode === 'orders' ? 'Tren Volume Pesanan per Kategori' : 'Tren Pendapatan & Pesanan')"></h2>
                            <span class="px-2.5 py-0.5 bg-emerald-50 text-emerald-700 text-[10px] font-bold rounded-full border border-emerald-200/60 uppercase tracking-wider">
                                7 Hari
                            </span>
                        </div>
                        <p class="text-xs text-gray-400 font-medium mt-0.5">
                            {{ \Carbon\Carbon::now()->subDays(6)->translatedFormat('d M') }} - {{ \Carbon\Carbon::now()->translatedFormat('d M Y') }}
                        </p>
                    </div>

                    {{-- View Mode Toggle --}}
                    <div class="inline-flex p-1 bg-gray-100/90 rounded-xl text-xs font-semibold text-gray-600 self-start sm:self-auto border border-gray-200/50">
                        <button type="button" @click="setMode('all')" :class="mode === 'all' ? 'bg-white text-gray-900 shadow-2xs' : 'text-gray-500 hover:text-gray-900'" class="px-3 py-1.5 rounded-lg transition-all cursor-pointer">
                            Semua
                        </button>
                        <button type="button" @click="setMode('income')" :class="mode === 'income' ? 'bg-white text-emerald-700 shadow-2xs' : 'text-gray-500 hover:text-gray-900'" class="px-3 py-1.5 rounded-lg transition-all cursor-pointer">
                            Pendapatan
                        </button>
                        <button type="button" @click="setMode('orders')" :class="mode === 'orders' ? 'bg-white text-indigo-700 shadow-2xs' : 'text-gray-500 hover:text-gray-900'" class="px-3 py-1.5 rounded-lg transition-all cursor-pointer">
                            Pesanan
                        </button>
                    </div>
                </div>

                {{-- Metric Summary Badges --}}
                <div class="grid grid-cols-3 gap-3 py-3.5 my-1 bg-gray-50/70 rounded-xl px-3 border border-gray-100 text-center">
                    <div>
                        <span class="text-[10px] uppercase font-bold text-gray-400 tracking-wider block">Total Omset</span>
                        <span class="text-xs sm:text-sm font-extrabold text-emerald-600 tabular-nums">Rp {{ number_format($totalPendapatan7Hari, 0, ',', '.') }}</span>
                    </div>
                    <div class="border-x border-gray-200/60">
                        <span class="text-[10px] uppercase font-bold text-gray-400 tracking-wider block">Total Pesanan</span>
                        <span class="text-xs sm:text-sm font-extrabold text-gray-900">{{ $totalPesanan7Hari }} Trx</span>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-bold text-gray-400 tracking-wider block">Rata-rata / Hari</span>
                        <span class="text-xs sm:text-sm font-extrabold text-gray-700 tabular-nums">Rp {{ number_format($totalPendapatan7Hari / 7, 0, ',', '.') }}</span>
                    </div>
                </div>

                {{-- Interactive Custom Legend Pills --}}
                <div class="flex flex-wrap items-center gap-2 py-2 text-xs">
                    {{-- Pendapatan (in all & income modes) --}}
                    <template x-if="mode === 'all' || mode === 'income'">
                        <button type="button" @click="toggleDataset('income')" :class="activeDatasets.includes('income') ? 'bg-emerald-50 border-emerald-300 text-emerald-800' : 'bg-gray-50 border-gray-200 text-gray-400 opacity-50'" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg border font-semibold text-[11px] transition-all cursor-pointer">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
                            <span>Pendapatan (Rp {{ number_format($totalPendapatan7Hari, 0, ',', '.') }})</span>
                        </button>
                    </template>

                    {{-- Total Pesanan (in all mode) --}}
                    <template x-if="mode === 'all'">
                        <button type="button" @click="toggleDataset('total_orders')" :class="activeDatasets.includes('total_orders') ? 'bg-indigo-50 border-indigo-300 text-indigo-800' : 'bg-gray-50 border-gray-200 text-gray-400 opacity-50'" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg border font-semibold text-[11px] transition-all cursor-pointer">
                            <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                            <span>Total Pesanan ({{ $totalPesanan7Hari }} trx)</span>
                        </button>
                    </template>

                    {{-- Per-category pills (in orders mode) --}}
                    <template x-if="mode === 'orders'">
                        <div class="flex flex-wrap items-center gap-2">
                            <button type="button" @click="toggleDataset('dine_in')" :class="activeDatasets.includes('dine_in') ? 'bg-indigo-50 border-indigo-300 text-indigo-800' : 'bg-gray-50 border-gray-200 text-gray-400 opacity-50'" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg border font-semibold text-[11px] transition-all cursor-pointer">
                                <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                                <span>Dine In ({{ $totalDineIn7Hari }})</span>
                            </button>
                            <button type="button" @click="toggleDataset('catering')" :class="activeDatasets.includes('catering') ? 'bg-teal-50 border-teal-300 text-teal-800' : 'bg-gray-50 border-gray-200 text-gray-400 opacity-50'" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg border font-semibold text-[11px] transition-all cursor-pointer">
                                <span class="w-2.5 h-2.5 rounded-full bg-teal-500"></span>
                                <span>Katering ({{ $totalCatering7Hari }})</span>
                            </button>
                            <button type="button" @click="toggleDataset('nasi_box')" :class="activeDatasets.includes('nasi_box') ? 'bg-amber-50 border-amber-300 text-amber-800' : 'bg-gray-50 border-gray-200 text-gray-400 opacity-50'" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg border font-semibold text-[11px] transition-all cursor-pointer">
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                                <span>Nasi Box ({{ $totalNasiBox7Hari }})</span>
                            </button>
                        </div>
                    </template>
                </div>

                {{-- Chart Canvas Container --}}
                <div class="w-full relative h-[310px] mt-2">
                    <canvas id="incomeChart"></canvas>
                </div>

                {{-- Channel Breakdown Summary Row (Always visible for fast insights) --}}
                <div class="grid grid-cols-3 gap-2 mt-4 pt-3.5 border-t border-gray-100 text-xs">
                    <div class="flex items-center gap-2 p-2 rounded-xl bg-indigo-50/50 border border-indigo-100/60">
                        <div class="w-2 h-2 rounded-full bg-indigo-500 shrink-0"></div>
                        <div class="min-w-0">
                            <span class="text-[10px] font-semibold text-gray-500 block truncate">Dine In</span>
                            <span class="font-extrabold text-gray-900">{{ $totalDineIn7Hari }} <span class="text-[10px] font-normal text-gray-400">({{ $totalPesanan7Hari > 0 ? round(($totalDineIn7Hari/$totalPesanan7Hari)*100) : 0 }}%)</span></span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 p-2 rounded-xl bg-teal-50/50 border border-teal-100/60">
                        <div class="w-2 h-2 rounded-full bg-teal-500 shrink-0"></div>
                        <div class="min-w-0">
                            <span class="text-[10px] font-semibold text-gray-500 block truncate">Katering</span>
                            <span class="font-extrabold text-gray-900">{{ $totalCatering7Hari }} <span class="text-[10px] font-normal text-gray-400">({{ $totalPesanan7Hari > 0 ? round(($totalCatering7Hari/$totalPesanan7Hari)*100) : 0 }}%)</span></span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 p-2 rounded-xl bg-amber-50/50 border border-amber-100/60">
                        <div class="w-2 h-2 rounded-full bg-amber-500 shrink-0"></div>
                        <div class="min-w-0">
                            <span class="text-[10px] font-semibold text-gray-500 block truncate">Nasi Box</span>
                            <span class="font-extrabold text-gray-900">{{ $totalNasiBox7Hari }} <span class="text-[10px] font-normal text-gray-400">({{ $totalPesanan7Hari > 0 ? round(($totalNasiBox7Hari/$totalPesanan7Hari)*100) : 0 }}%)</span></span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── TRANSAKSI TERBARU CARD ── --}}
            <div class="bg-white rounded-2xl border border-gray-200/80 shadow-2xs p-5 sm:p-6 flex flex-col justify-between">
                <div class="flex justify-between items-center pb-4 mb-3 border-b border-gray-100">
                    <div>
                        <h3 class="font-extrabold text-gray-900 text-base tracking-tight">Transaksi Terbaru</h3>
                        <p class="text-xs text-gray-400 font-medium mt-0.5">Pesanan resto & catering yang baru masuk</p>
                    </div>
                    <a href="{{ route('admin.pesanan.index') }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 bg-emerald-50 hover:bg-emerald-100/70 border border-emerald-200/80 px-3 py-1.5 rounded-lg transition-colors inline-flex items-center gap-1">
                        Lihat Semua &rarr;
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <x-ui.table class="min-w-[580px]">
                        <x-ui.table.header>
                            <th class="px-3.5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">No. Kode</th>
                            <th class="px-3.5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Jenis Pesanan</th>
                            <th class="px-3.5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Waktu</th>
                            <th class="px-3.5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Total Tagihan</th>
                            <th class="px-3.5 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                        </x-ui.table.header>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($pesananTerbaru as $p)
                                @php
                                    $badgeColor = 'bg-amber-50 text-amber-700 border-amber-200';
                                    $badgeText = 'Menunggu';

                                    if ($p->status === 'Selesai') {
                                        $badgeColor = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                                        $badgeText = 'Selesai';
                                    } elseif ($p->status === 'Dibatalkan') {
                                        $badgeColor = 'bg-rose-50 text-rose-700 border-rose-200';
                                        $badgeText = 'Batal';
                                    } elseif ($p->status === 'Sedang Diproses') {
                                        $badgeColor = 'bg-blue-50 text-blue-700 border-blue-200';
                                        $badgeText = 'Diproses';
                                    }
                                @endphp
                                <tr class="hover:bg-gray-50/60 transition-colors">
                                    <td class="px-3.5 py-3 align-middle font-bold text-gray-900 text-xs font-mono">
                                        {{ $p->no }}
                                    </td>
                                    <td class="px-3.5 py-3 align-middle">
                                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full border {{ $p->jenis === 'Nasi Box' ? 'bg-amber-50 text-amber-700 border-amber-200' : ($p->jenis === 'Catering' ? 'bg-teal-50 text-teal-700 border-teal-200' : 'bg-indigo-50 text-indigo-700 border-indigo-200') }}">
                                            {{ $p->jenis }}
                                        </span>
                                    </td>
                                    <td class="px-3.5 py-3 align-middle text-xs text-gray-500">
                                        {{ \Carbon\Carbon::parse($p->tanggal)->locale('id')->diffForHumans() }}
                                    </td>
                                    <td class="px-3.5 py-3 align-middle font-bold text-gray-900 text-xs tabular-nums">
                                        Rp {{ number_format($p->total, 0, ',', '.') }}
                                    </td>
                                    <td class="px-3.5 py-3 align-middle text-center">
                                        <span class="inline-flex items-center text-[11px] font-bold px-2.5 py-0.5 rounded-full border {{ $badgeColor }}">
                                            {{ $badgeText }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <x-ui.empty-state icon="clock" title="Belum ada transaksi" message="Belum ada transaksi pesanan terbaru." />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </x-ui.table>
                </div>
            </div>

        </div>

    </div>
</div>

{{-- Chart.js Script with Alpine.js Reactive Controller --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let chartInstance = null;

    function dashboardChart() {
        return {
            mode: 'all',
            activeDatasets: ['income', 'total_orders', 'dine_in', 'catering', 'nasi_box'],

            labels: {!! json_encode($labels) !!},
            dataPendapatan: {!! json_encode($dataPendapatan) !!},
            dataDineIn: {!! json_encode($dataDineIn) !!},
            dataCatering: {!! json_encode($dataCatering) !!},
            dataNasiBox: {!! json_encode($dataNasiBox) !!},

            init() {
                this.renderChart();
            },

            renderChart() {
                const ctx = document.getElementById('incomeChart').getContext('2d');
                if (chartInstance) {
                    chartInstance.destroy();
                }

                const totalOrdersPerDay = this.dataDineIn.map((v, i) => v + this.dataCatering[i] + this.dataNasiBox[i]);

                // Gradient for Area Line
                const emeraldGradient = ctx.createLinearGradient(0, 0, 0, 300);
                emeraldGradient.addColorStop(0, 'rgba(5, 150, 105, 0.22)');
                emeraldGradient.addColorStop(0.7, 'rgba(5, 150, 105, 0.04)');
                emeraldGradient.addColorStop(1, 'rgba(5, 150, 105, 0.0)');

                const indigoGradient = ctx.createLinearGradient(0, 0, 0, 300);
                indigoGradient.addColorStop(0, 'rgba(99, 102, 241, 0.18)');
                indigoGradient.addColorStop(1, 'rgba(99, 102, 241, 0.0)');

                let datasets = [];
                let scalesConfig = {};

                if (this.mode === 'all') {
                    // MODE: SEMUA (Dual Line: Smooth Revenue Spline + Smooth Total Orders Line)
                    datasets = [
                        {
                            type: 'line',
                            id: 'income',
                            label: 'Pendapatan',
                            data: this.dataPendapatan,
                            borderColor: '#059669',
                            backgroundColor: emeraldGradient,
                            borderWidth: 3,
                            pointBackgroundColor: '#FFFFFF',
                            pointBorderColor: '#059669',
                            pointBorderWidth: 2.5,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            fill: true,
                            tension: 0.38,
                            yAxisID: 'y',
                            order: 1,
                            hidden: !this.activeDatasets.includes('income')
                        },
                        {
                            type: 'line',
                            id: 'total_orders',
                            label: 'Total Pesanan',
                            data: totalOrdersPerDay,
                            borderColor: '#6366F1',
                            backgroundColor: indigoGradient,
                            borderWidth: 2.5,
                            borderDash: [5, 5],
                            pointBackgroundColor: '#FFFFFF',
                            pointBorderColor: '#6366F1',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            fill: true,
                            tension: 0.35,
                            yAxisID: 'y1',
                            order: 2,
                            hidden: !this.activeDatasets.includes('total_orders')
                        }
                    ];

                    scalesConfig = {
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 11, family: 'Google Sans, sans-serif' }, color: '#64748B' }
                        },
                        y: {
                            beginAtZero: true,
                            type: 'linear',
                            display: this.activeDatasets.includes('income'),
                            position: 'left',
                            grace: '20%',
                            grid: { color: 'rgba(241, 245, 249, 0.9)', drawBorder: false },
                            ticks: {
                                font: { size: 11, family: 'Google Sans, sans-serif' },
                                color: '#64748B',
                                callback: function(value) {
                                    if (value >= 1000000) return 'Rp ' + (value / 1000000).toLocaleString('id-ID') + ' Jt';
                                    if (value >= 1000) return 'Rp ' + (value / 1000).toLocaleString('id-ID') + ' Rb';
                                    return 'Rp ' + value;
                                }
                            }
                        },
                        y1: {
                            beginAtZero: true,
                            type: 'linear',
                            display: this.activeDatasets.includes('total_orders'),
                            position: 'right',
                            grace: '25%',
                            grid: { display: false },
                            ticks: {
                                stepSize: 5,
                                font: { size: 11, family: 'Google Sans, sans-serif' },
                                color: '#6366F1',
                                callback: function(value) {
                                    return value + ' trx';
                                }
                            }
                        }
                    };
                } else if (this.mode === 'income') {
                    // MODE: PENDAPATAN (Full Area Spline)
                    datasets = [
                        {
                            type: 'line',
                            id: 'income',
                            label: 'Pendapatan',
                            data: this.dataPendapatan,
                            borderColor: '#059669',
                            backgroundColor: emeraldGradient,
                            borderWidth: 3.5,
                            pointBackgroundColor: '#FFFFFF',
                            pointBorderColor: '#059669',
                            pointBorderWidth: 2.5,
                            pointRadius: 5,
                            pointHoverRadius: 7,
                            fill: true,
                            tension: 0.38,
                            yAxisID: 'y',
                            hidden: !this.activeDatasets.includes('income')
                        }
                    ];

                    scalesConfig = {
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 11, family: 'Google Sans, sans-serif' }, color: '#64748B' }
                        },
                        y: {
                            beginAtZero: true,
                            type: 'linear',
                            position: 'left',
                            grace: '20%',
                            grid: { color: 'rgba(241, 245, 249, 0.9)', drawBorder: false },
                            ticks: {
                                font: { size: 11, family: 'Google Sans, sans-serif' },
                                color: '#64748B',
                                callback: function(value) {
                                    if (value >= 1000000) return 'Rp ' + (value / 1000000).toLocaleString('id-ID') + ' Jt';
                                    if (value >= 1000) return 'Rp ' + (value / 1000).toLocaleString('id-ID') + ' Rb';
                                    return 'Rp ' + value;
                                }
                            }
                        }
                    };
                } else if (this.mode === 'orders') {
                    // MODE: PESANAN (Grouped Clustered Bars per Channel with rounded corners)
                    datasets = [
                        {
                            type: 'bar',
                            id: 'dine_in',
                            label: 'Dine In',
                            data: this.dataDineIn,
                            backgroundColor: '#6366F1',
                            hoverBackgroundColor: '#4F46E5',
                            borderRadius: 6,
                            maxBarThickness: 18,
                            hidden: !this.activeDatasets.includes('dine_in')
                        },
                        {
                            type: 'bar',
                            id: 'catering',
                            label: 'Katering',
                            data: this.dataCatering,
                            backgroundColor: '#0D9488',
                            hoverBackgroundColor: '#0F766E',
                            borderRadius: 6,
                            maxBarThickness: 18,
                            hidden: !this.activeDatasets.includes('catering')
                        },
                        {
                            type: 'bar',
                            id: 'nasi_box',
                            label: 'Nasi Box',
                            data: this.dataNasiBox,
                            backgroundColor: '#F59E0B',
                            hoverBackgroundColor: '#D97706',
                            borderRadius: 6,
                            maxBarThickness: 18,
                            hidden: !this.activeDatasets.includes('nasi_box')
                        }
                    ];

                    scalesConfig = {
                        x: {
                            stacked: false,
                            grid: { display: false },
                            ticks: { font: { size: 11, family: 'Google Sans, sans-serif' }, color: '#64748B' }
                        },
                        y: {
                            beginAtZero: true,
                            type: 'linear',
                            position: 'left',
                            grace: '20%',
                            grid: { color: 'rgba(241, 245, 249, 0.9)', drawBorder: false },
                            ticks: {
                                stepSize: 5,
                                font: { size: 11, family: 'Google Sans, sans-serif' },
                                color: '#64748B',
                                callback: function(value) {
                                    return value + ' pesanan';
                                }
                            }
                        }
                    };
                }

                chartInstance = new Chart(ctx, {
                    data: {
                        labels: this.labels,
                        datasets: datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#0F172A',
                                titleFont: { size: 12, weight: '700', family: 'Google Sans, sans-serif' },
                                bodyFont: { size: 12, weight: '500', family: 'Google Sans, sans-serif' },
                                padding: 12,
                                cornerRadius: 10,
                                boxPadding: 6,
                                usePointStyle: true,
                                callbacks: {
                                    label: function(context) {
                                        if (context.dataset.yAxisID === 'y' || context.dataset.id === 'income') {
                                            return ' ' + context.dataset.label + ': Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                                        }
                                        return ' ' + context.dataset.label + ': ' + context.parsed.y + ' Pesanan';
                                    }
                                }
                            }
                        },
                        scales: scalesConfig,
                        interaction: {
                            intersect: false,
                            mode: 'index'
                        }
                    }
                });
            },

            setMode(newMode) {
                this.mode = newMode;
                this.renderChart();
            },

            toggleDataset(datasetId) {
                if (this.activeDatasets.includes(datasetId)) {
                    this.activeDatasets = this.activeDatasets.filter(id => id !== datasetId);
                } else {
                    this.activeDatasets.push(datasetId);
                }
                this.renderChart();
            }
        };
    }
</script>
@endsection
