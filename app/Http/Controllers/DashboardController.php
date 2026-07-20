<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use App\Models\PesananCatering;
use App\Models\PesananNasiBox;
use App\Models\BahanBaku;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        $userRole = $user->role->name ?? null;

        if (!in_array($userRole, ['Admin', 'Super Admin', 'Manajer', 'Kasir'])) {
            return redirect()->route('member.dashboard');
        }

        // 1. Statistik Hari Ini
        $today = Carbon::today();
        
        $pesananHariIni = Pesanan::whereDate('tanggal_pesanan', $today)->count() + PesananCatering::whereDate('created_at', $today)->count() + PesananNasiBox::whereDate('created_at', $today)->count();
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
        // Menggabungkan pesanan terbaru dari 3 tabel menjadi format yang sama
        $pesananTerbaru = collect();
        
        foreach(Pesanan::latest()->take(5)->get() as $p) { 
            $pesananTerbaru->push((object)[
                'id' => $p->id,
                'no' => $p->no_pesanan,
                'tanggal' => $p->tanggal_pesanan,
                'total' => $p->total_harga,
                'status' => $p->status_pesanan,
                'jenis' => 'Resto',
                'url' => route('pesanan.show', $p->id)
            ]); 
        }
        
        foreach(PesananCatering::latest()->take(5)->get() as $p) { 
            $pesananTerbaru->push((object)[
                'id' => $p->id,
                'no' => $p->kode_pesanan,
                'tanggal' => $p->created_at,
                'total' => $p->total_harga,
                'status' => $p->status,
                'jenis' => 'Catering',
                'url' => route('admin.pesanan.catering.show', $p->id)
            ]); 
        }
        
        foreach(PesananNasiBox::latest()->take(5)->get() as $p) { 
            $pesananTerbaru->push((object)[
                'id' => $p->id,
                'no' => $p->kode_pesanan,
                'tanggal' => $p->created_at,
                'total' => $p->total_harga,
                'status' => $p->status,
                'jenis' => 'Nasi Box',
                'url' => route('admin.pesanan.nasibox.show', $p->id)
            ]); 
        }
        
        $pesananTerbaru = $pesananTerbaru->sortByDesc('tanggal')->take(5);

        // 5. Pesanan Catering & Nasi Box Menunggu Konfirmasi
        $cateringMenunggu = PesananCatering::with('paket')
                                            ->where('status', 'menunggu_konfirmasi')
                                            ->latest()
                                            ->get();
                                            
        $nasiBoxMenunggu = PesananNasiBox::with('menu')
                                            ->where('status', 'menunggu_konfirmasi')
                                            ->latest()
                                            ->get();
        
        // 6. Pesanan Mendekati Batas Konfirmasi (H-3)
        $cateringUrgent = PesananCatering::with('paket')
                                          ->where('status', 'menunggu_konfirmasi')
                                          ->whereDate('tanggal_acara', '<=', Carbon::today()->addDays(3))
                                          ->get();

        $nasiBoxUrgent = PesananNasiBox::with('menu')
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
            'nasiBoxMenunggu',
            'cateringUrgent',
            'nasiBoxUrgent'
        ));
    }
}
