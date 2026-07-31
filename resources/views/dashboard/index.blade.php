@extends('layouts.pos')

@section('content')
<div class="min-h-screen bg-[#F8FAFC] text-[#111827] font-sans p-6 md:p-8 space-y-6">
    <div class="w-full p-6 space-y-6">



        {{-- ── 2. HIGH-CONTRAST STAT CARDS ── --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            {{-- Stat 1: Pesanan Hari Ini --}}
            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex flex-col gap-3 transition-all hover:shadow-md hover:border-gray-200">
                <div class="flex items-center justify-between text-gray-500">
                    <p class="text-xs font-semibold">Pesanan Hari Ini</p>
                    <i class="ph-bold ph-shopping-bag text-lg"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $pesananHariIni }}</p>
                </div>
            </div>

            {{-- Stat 2: Pendapatan Hari Ini --}}
            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex flex-col gap-3 transition-all hover:shadow-md hover:border-gray-200">
                <div class="flex items-center justify-between text-gray-500">
                    <p class="text-xs font-semibold">Pendapatan Hari Ini</p>
                    <i class="ph-bold ph-wallet text-lg text-emerald-600"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($pendapatanHariIni, 0, ',', '.') }}</p>
                </div>
            </div>

            {{-- Stat 3: Pesanan Pending --}}
            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex flex-col gap-3 transition-all hover:shadow-md hover:border-gray-200">
                <div class="flex items-center justify-between text-gray-500">
                    <p class="text-xs font-semibold">Pesanan Pending</p>
                    <i class="ph-bold ph-hourglass-high text-lg text-amber-500"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $pesananPending }}</p>
                </div>
            </div>

            {{-- Stat 4: Stok Menipis --}}
            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex flex-col gap-3 transition-all hover:shadow-md hover:border-gray-200">
                <div class="flex items-center justify-between text-gray-500">
                    <p class="text-xs font-semibold">Stok Menipis</p>
                    <i class="ph-bold ph-package text-lg text-red-500"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $stokMenipis }}</p>
                </div>
            </div>
        </div>

        {{-- ── 3. MAIN CONTENT: CHART & TOP MENUS ── --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- 7-Day Revenue Trend Chart (2 Cols) --}}
            <div class="lg:col-span-2 bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                    <div>
                        <h2 class="font-extrabold text-slate-900 text-base">Tren Pendapatan (7 Hari Terakhir)</h2>
                        <p class="text-xs text-slate-400 font-medium">Grafik akumulasi omset harian resto & catering</p>
                    </div>
                    <span class="px-3 py-1 bg-emerald-50 text-[#0F2E23] text-xs font-bold rounded-xl border border-emerald-100">
                        <x-heroicon-o-chart-bar class="mr-1 w-5 h-5" /> Realtime
                    </span>
                </div>
                <div class="h-64 w-full relative">
                    <canvas id="incomeChart"></canvas>
                </div>
            </div>

        </div>

        {{-- ── 4. BOTTOM ROW: TRANSAKSI TERBARU & PERINGATAN STOK ── --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Transaksi Terbaru (2 Cols) --}}
            <div class="lg:col-span-2 bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs">
                <div class="flex justify-between items-center mb-4 pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="font-extrabold text-slate-900 text-base">Transaksi Terbaru</h3>
                        <p class="text-xs text-slate-400 font-medium">Pesanan resto & catering yang baru masuk</p>
                    </div>
                    <a href="{{ route('pesanan.index') }}" class="text-xs font-extrabold text-[#0F2E23] hover:underline shrink-0">
                        Lihat Semua &rarr;
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                                <th class="pb-3 px-2">No. Kode</th>
                                <th class="pb-3 px-2">Jenis Pesanan</th>
                                <th class="pb-3 px-2">Waktu</th>
                                <th class="pb-3 px-2">Total Tagihan</th>
                                <th class="pb-3 px-2">Status</th>
                                <th class="pb-3 px-2 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs font-medium">
                            @forelse($pesananTerbaru as $p)
                                @php
                                    $badgeBg = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                                    $badgeText = strtoupper(str_replace('_', ' ', $p->status));

                                    if (in_array($p->status, ['baru', 'menunggu_dp', 'menunggu_konfirmasi'])) {
                                        $badgeBg = 'bg-amber-50 text-amber-700 border-amber-200';
                                        $badgeText = 'MENUNGGU';
                                    } elseif (in_array($p->status, ['dibatalkan', 'batal'])) {
                                        $badgeBg = 'bg-red-50 text-red-700 border-red-200';
                                        $badgeText = 'BATAL';
                                    }
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="py-3.5 px-2 font-black text-[#0F2E23]">
                                        {{ $p->no }}
                                    </td>
                                    <td class="py-3.5 px-2 font-bold text-slate-800">
                                        {{ $p->jenis }}
                                    </td>
                                    <td class="py-3.5 px-2 text-slate-400 text-[11px]">
                                        {{ \Carbon\Carbon::parse($p->tanggal)->diffForHumans() }}
                                    </td>
                                    <td class="py-3.5 px-2 font-extrabold text-slate-900">
                                        Rp {{ number_format($p->total, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3.5 px-2">
                                        <span class="px-3 py-1 rounded-full text-[10px] font-extrabold border shrink-0 {{ $badgeBg }}">
                                            {{ $badgeText }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-2 text-right">
                                        <a href="{{ $p->url }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-800 text-[11px] font-bold rounded-xl transition-colors inline-block">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-slate-400 text-xs">
                                        Belum ada transaksi pesanan hari ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Peringatan Stok (1 Col) --}}
            <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-center mb-4 pb-3 border-b border-slate-100">
                        <h3 class="font-extrabold text-slate-900 text-base">Peringatan Stok</h3>
                        <a href="{{ route('bahan-baku.index') }}" class="text-xs font-extrabold text-red-600 hover:underline shrink-0">
                            Lihat Semua &rarr;
                        </a>
                    </div>

                    <div class="space-y-3">
                        @forelse($listStokMenipis as $stok)
                            <div class="flex items-center justify-between p-3 rounded-2xl bg-slate-50 border border-slate-100 hover:bg-slate-100/80 transition-colors">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-9 h-9 rounded-xl bg-red-100 text-red-600 flex items-center justify-center text-xs font-bold shrink-0">
                                        <x-heroicon-o-exclamation-triangle class="w-5 h-5" />
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-extrabold text-slate-900 text-xs truncate">{{ $stok->nama_bahan }}</p>
                                        <p class="text-[10px] text-slate-400 font-medium">Min: {{ $stok->stok_minimum }} {{ $stok->satuan->nama_satuan ?? '' }}</p>
                                    </div>
                                </div>
                                <span class="px-2.5 py-1 bg-red-50 text-red-600 font-black text-xs rounded-xl border border-red-100 shrink-0">
                                    {{ $stok->stok }} {{ $stok->satuan->nama_satuan ?? '' }}
                                </span>
                            </div>
                        @empty
                            <div class="py-8 text-center text-slate-400 text-xs space-y-2">
                                <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto text-lg">
                                    <x-heroicon-o-check-circle class="w-5 h-5" />
                                </div>
                                <p class="font-bold text-slate-700">Semua stok aman</p>
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
        gradient.addColorStop(0, 'rgba(15, 46, 35, 0.25)');
        gradient.addColorStop(1, 'rgba(15, 46, 35, 0)');
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Pendapatan (Rp)',
                    data: data,
                    borderColor: '#0F2E23',
                    backgroundColor: gradient,
                    borderWidth: 3,
                    pointBackgroundColor: '#FFFFFF',
                    pointBorderColor: '#0F2E23',
                    pointBorderWidth: 2.5,
                    pointRadius: 5,
                    pointHoverRadius: 7,
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
                        backgroundColor: '#0F2E23',
                        padding: 12,
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
                        ticks: { font: { size: 11, weight: '600' }, color: '#9CA3AF' }
                    },
                    y: {
                        grid: { color: '#F1F5F9', strokeDash: [4, 4] },
                        ticks: {
                            font: { size: 11, weight: '600' },
                            color: '#9CA3AF',
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
