{{-- 
    Halaman: Dashboard
    Deskripsi: Halaman utama setelah login. Menampilkan ringkasan KPI,
               grafik pendapatan 7 hari, stok menipis, dan pesanan terbaru.
--}}
@extends('layouts.pos')

@section('content')
<div class="flex-1 overflow-auto bg-gray-50/50 text-gray-800 font-sans">
    <div class="p-4 md:p-6 lg:p-8 max-w-[1200px] mx-auto space-y-6">
        
        {{-- Header --}}
        <x-ui.page-header title="Dashboard" subtitle="Ringkasan aktivitas hari ini, {{ date('d F Y') }}">
            <x-slot:actions>
                <x-ui.button href="{{ route('pesanan.create') }}" icon="fa-plus">Buat Pesanan Baru</x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        {{-- 4 KPI Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
            <x-ui.stat-card label="Pesanan Hari Ini" :value="$pesananHariIni" icon="fa-shopping-bag" color="blue" />
            <x-ui.stat-card label="Pendapatan Hari Ini" :value="'Rp ' . number_format($pendapatanHariIni, 0, ',', '.')" icon="fa-wallet" color="green" />
            <x-ui.stat-card label="Pesanan Pending" :value="$pesananPending" icon="fa-clock" color="orange" />
            <x-ui.stat-card label="Stok Menipis" :value="$stokMenipis" icon="fa-exclamation-triangle" color="red" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Chart Container --}}
            <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <div class="flex justify-between items-center mb-6 border-b border-gray-50 pb-4">
                    <h2 class="text-lg font-bold text-gray-900">Trend Pendapatan (7 Hari Terakhir)</h2>
                </div>
                <div class="h-72 w-full relative">
                    <canvas id="incomeChart"></canvas>
                </div>
            </div>

            {{-- Side Lists --}}
            <div class="space-y-6">
                {{-- Stok Menipis --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="p-4 border-b border-gray-50 flex justify-between items-center">
                        <h2 class="font-bold text-gray-900 text-sm">Peringatan Stok</h2>
                        <a href="{{ route('stok-menipis.index') }}" class="text-xs text-[#3B82F6] hover:underline font-medium">Lihat Semua</a>
                    </div>
                    <div class="divide-y divide-gray-50">
                        @forelse($listStokMenipis as $stok)
                            <div class="p-4 hover:bg-gray-50/50 transition-colors flex justify-between items-center">
                                <div class="font-medium text-gray-900 text-sm">{{ $stok->nama_bahan }}</div>
                                <div class="text-right">
                                    <div class="text-xs font-bold text-red-500">{{ $stok->stok }} {{ $stok->satuan->nama_satuan ?? '' }}</div>
                                    <div class="text-[10px] text-gray-400">Min: {{ $stok->stok_minimum }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="p-6 text-center text-gray-500 text-sm">
                                <i class="fas fa-check-circle text-emerald-400 text-2xl mb-2 block"></i>
                                <p>Stok bahan baku aman.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Pesanan Terbaru --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="p-4 border-b border-gray-50 flex justify-between items-center">
                        <h2 class="font-bold text-gray-900 text-sm">Pesanan Terbaru</h2>
                        <a href="{{ route('pesanan.index') }}" class="text-xs text-[#3B82F6] hover:underline font-medium">Lihat Semua</a>
                    </div>
                    <div class="divide-y divide-gray-50">
                        @forelse($pesananTerbaru as $p)
                            <div class="p-4 hover:bg-gray-50/50 transition-colors">
                                <div class="flex justify-between items-start mb-1">
                                    <a href="{{ route('pesanan.show', $p->id) }}" class="font-bold text-[#3B82F6] text-sm hover:underline">{{ $p->no_pesanan }}</a>
                                    <span class="text-[10px] text-gray-500">{{ $p->tanggal_pesanan->diffForHumans() }}</span>
                                </div>
                                <div class="flex justify-between items-center mt-2">
                                    <span class="text-xs text-gray-600 font-medium">Rp {{ number_format($p->total_harga, 0, ',', '.') }}</span>
                                    @if($p->status_pesanan == 'baru')
                                        <x-ui.badge color="gray" size="sm">BARU</x-ui.badge>
                                    @elseif($p->status_pesanan == 'diproses')
                                        <x-ui.badge color="warning" size="sm">DIPROSES</x-ui.badge>
                                    @else
                                        <x-ui.badge color="success" size="sm">SELESAI</x-ui.badge>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="p-6 text-center text-gray-500 text-sm">
                                <p>Belum ada pesanan hari ini.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Notifikasi Pesanan Catering (FASE 5) --}}
        @if($cateringUrgent->count() > 0)
        <div class="bg-red-50 border border-red-200 rounded-2xl p-4 md:p-6">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center"><i class="fas fa-exclamation-triangle text-red-500"></i></div>
                <div>
                    <h3 class="font-bold text-red-800">Pesanan Catering Urgent!</h3>
                    <p class="text-xs text-red-600">Pesanan berikut sudah mendekati batas H-3 konfirmasi</p>
                </div>
            </div>
            <div class="space-y-2">
                @foreach($cateringUrgent as $urgent)
                <a href="{{ route('pesanan-catering.show', $urgent) }}" class="block bg-white rounded-xl p-3 hover:shadow-md transition-shadow border border-red-100">
                    <div class="flex justify-between items-center">
                        <div>
                            <span class="font-bold text-red-600">{{ $urgent->no_pesanan }}</span>
                            <span class="text-sm text-gray-600 ml-2">{{ $urgent->nama_pemesan }}</span>
                        </div>
                        <div class="text-right">
                            <div class="text-xs text-red-500 font-medium">Acara: {{ $urgent->tanggal_acara->format('d/m/Y') }}</div>
                            <div class="text-xs text-gray-400">{{ $urgent->paketCatering->nama_paket }}</div>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        @if($cateringMenunggu->count() > 0)
        <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-4 md:p-6">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center"><i class="fas fa-bell text-yellow-500"></i></div>
                <div>
                    <h3 class="font-bold text-yellow-800">Pesanan Catering Menunggu Konfirmasi ({{ $cateringMenunggu->count() }})</h3>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                @foreach($cateringMenunggu as $waiting)
                <a href="{{ route('pesanan-catering.show', $waiting) }}" class="block bg-white rounded-xl p-3 hover:shadow-md transition-shadow border border-yellow-100">
                    <div class="flex justify-between items-center">
                        <div>
                            <span class="font-semibold text-gray-900 text-sm">{{ $waiting->no_pesanan }}</span>
                            <span class="text-xs text-gray-500 ml-1">{{ $waiting->nama_pemesan }}</span>
                        </div>
                        <div class="text-right">
                            <div class="text-xs font-medium text-primary">Rp {{ number_format($waiting->total_harga, 0, ',', '.') }}</div>
                            <div class="text-[10px] text-gray-400">{{ $waiting->tanggal_acara->format('d/m/Y') }}</div>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</div>

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('incomeChart').getContext('2d');
        const labels = {!! json_encode($labels) !!};
        const data = {!! json_encode($dataPendapatan) !!};
        
        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(59, 130, 246, 0.2)');
        gradient.addColorStop(1, 'rgba(59, 130, 246, 0)');
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Pendapatan Harian (Rp)',
                    data: data,
                    borderColor: '#3B82F6',
                    backgroundColor: gradient,
                    borderWidth: 3,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#3B82F6',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#111827',
                        padding: 12,
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
                        ticks: { font: { size: 12 }, color: '#6B7280' }
                    },
                    y: {
                        grid: { color: '#F3F4F6', borderDash: [5, 5] },
                        ticks: {
                            font: { size: 12 },
                            color: '#6B7280',
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
