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

        if ($userRole === 'Konsumen' || empty($userRole)) {
            return redirect()->route('member.dashboard');
        }

        // 1. Statistik Hari Ini
        $today = Carbon::today();
        
        $pesananHariIni = \App\Models\PesananDinein::whereDate('created_at', $today)->count() 
                        + \App\Models\PesananCatering::whereDate('created_at', $today)->count() 
                        + \App\Models\PesananNasiBox::whereDate('created_at', $today)->count();

        $pendapatanHariIni = \App\Models\PembayaranDinein::whereDate('created_at', $today)->where('status', 'lunas')->sum('total')
                           + \App\Models\PesananCatering::whereDate('created_at', $today)->whereIn('status_bayar', ['lunas'])->sum('total_tagihan')
                           + \App\Models\PesananNasiBox::whereDate('created_at', $today)->whereIn('status_bayar', ['lunas'])->sum('total_tagihan');
                                    
        $pesananPending = \App\Models\PesananDinein::whereIn('status', ['menunggu_pembayaran'])->count()
                        + \App\Models\PesananCatering::whereIn('status', ['ditinjau', 'dikonfirmasi', 'diproses', 'menunggu_pengiriman'])->count()
                        + \App\Models\PesananNasiBox::whereIn('status', ['ditinjau', 'dikonfirmasi', 'diproses', 'menunggu_pengiriman'])->count();
        
        $stokMenipis = BahanBaku::whereColumn('stok', '<=', 'stok_minimum')->count();

        // 2. Data Grafik Pendapatan 7 Hari Terakhir
        $labels = [];
        $dataPendapatan = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $date->format('d M');
            
            $totalDinein = \App\Models\PembayaranDinein::whereDate('created_at', $date)->where('status', 'lunas')->sum('total');
            $totalCatering = \App\Models\PesananCatering::whereDate('created_at', $date)->whereIn('status_bayar', ['lunas'])->sum('total_tagihan');
            $totalNasiBox = \App\Models\PesananNasiBox::whereDate('created_at', $date)->whereIn('status_bayar', ['lunas'])->sum('total_tagihan');
                            
            $dataPendapatan[] = $totalDinein + $totalCatering + $totalNasiBox;
        }

        // 3. Data Stok Menipis (List)
        $listStokMenipis = BahanBaku::with('satuan')
                                    ->whereColumn('stok', '<=', 'stok_minimum')
                                    ->take(5)
                                    ->get();

        // 4. Pesanan Terbaru
        $pesananTerbaru = collect();
        
        foreach(\App\Models\PesananDinein::latest()->take(5)->get() as $p) { 
            $pesananTerbaru->push((object)[
                'id' => $p->id,
                'no' => $p->kode_pesanan ?? 'DI-'.$p->id,
                'tanggal' => $p->created_at,
                'total' => $p->pembayaran ? $p->pembayaran->total : 0,
                'status' => $p->status,
                'jenis' => 'Dine In',
                'url' => route('pos.dinein.index')
            ]); 
        }
        
        foreach(PesananCatering::latest()->take(5)->get() as $p) { 
            $pesananTerbaru->push((object)[
                'id' => $p->id,
                'no' => $p->kode_pesanan,
                'tanggal' => $p->created_at,
                'total' => $p->total_tagihan,
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
                'total' => $p->total_tagihan,
                'status' => $p->status,
                'jenis' => 'Nasi Box',
                'url' => route('admin.pesanan.nasibox.show', $p->id)
            ]); 
        }
        
        $pesananTerbaru = $pesananTerbaru->sortByDesc('tanggal')->take(5);

        // 5. Pesanan Catering & Nasi Box Menunggu Konfirmasi (Ditinjau)
        $cateringMenunggu = PesananCatering::with('paket')
                                            ->where('status', 'ditinjau')
                                            ->latest()
                                            ->get();
                                            
        $nasiBoxMenunggu = PesananNasiBox::with('paket')
                                            ->where('status', 'ditinjau')
                                            ->latest()
                                            ->get();
        
        // 6. Pesanan Mendekati Batas Konfirmasi (H-3) - using diproses instead of menunggu_konfirmasi
        $cateringUrgent = PesananCatering::with('paket')
                                          ->whereIn('status', ['dikonfirmasi', 'diproses'])
                                          ->whereDate('tanggal_acara', '<=', Carbon::today()->addDays(3))
                                          ->get();

        $nasiBoxUrgent = PesananNasiBox::with('paket')
                                          ->whereIn('status', ['dikonfirmasi', 'diproses'])
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
