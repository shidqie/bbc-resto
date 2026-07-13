<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use App\Models\PesananCatering;
use App\Models\BahanBaku;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Statistik Hari Ini
        $today = Carbon::today();
        
        $pesananHariIni = Pesanan::whereDate('tanggal_pesanan', $today)->count();
        $pendapatanHariIni = Pesanan::whereDate('tanggal_pesanan', $today)
                                    ->where('status_pesanan', 'selesai')
                                    ->sum('total_harga');
                                    
        $pesananPending = Pesanan::whereIn('status_pesanan', ['baru', 'diproses'])->count();
        
        $stokMenipis = BahanBaku::whereColumn('stok', '<=', 'stok_minimum')->count();

        // 2. Data Grafik Pendapatan 7 Hari Terakhir
        $labels = [];
        $dataPendapatan = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $date->format('d M');
            
            $total = Pesanan::whereDate('tanggal_pesanan', $date)
                            ->where('status_pesanan', 'selesai')
                            ->sum('total_harga');
                            
            $dataPendapatan[] = $total;
        }

        // 3. Data Stok Menipis (List)
        $listStokMenipis = BahanBaku::with('satuan')
                                    ->whereColumn('stok', '<=', 'stok_minimum')
                                    ->take(5)
                                    ->get();

        // 4. Pesanan Terbaru
        $pesananTerbaru = Pesanan::with('user')->latest()->take(5)->get();

        // 5. Pesanan Catering Menunggu Konfirmasi (Notifikasi FASE 5)
        $cateringMenunggu = PesananCatering::with('paketCatering')
                                            ->where('status', 'menunggu_konfirmasi')
                                            ->latest()
                                            ->get();
        
        // 6. Pesanan Catering Mendekati Batas Konfirmasi (H-3)
        $cateringUrgent = PesananCatering::with('paketCatering')
                                          ->where('status', 'menunggu_konfirmasi')
                                          ->whereDate('tanggal_acara', '<=', Carbon::today()->addDays(3))
                                          ->get();

        return view('dashboard.index', compact(
            'pesananHariIni',
            'pendapatanHariIni',
            'pesananPending',
            'stokMenipis',
            'labels',
            'dataPendapatan',
            'listStokMenipis',
            'pesananTerbaru',
            'cateringMenunggu',
            'cateringUrgent'
        ));
    }
}
