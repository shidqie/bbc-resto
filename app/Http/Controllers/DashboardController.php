<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\NotifikasiStok;
use App\Models\Pembayaran;
use App\Models\Pesanan;
use App\Models\StokBahan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $userRole = $user->peran->nama_peran ?? null;

        if ($userRole === 'Konsumen' || empty($userRole)) {
            return redirect()->route('member.dashboard');
        }

        // 1. Statistik Hari Ini
        $today = Carbon::today();

        $pesananHariIni = Pesanan::whereDate('dibuat_pada', $today)->count();

        // 3 = LUNAS, asumsi status_pembayaran_id = 3 untuk Lunas
        $pendapatanHariIni = Pembayaran::whereDate('dibuat_pada', $today)
            ->where('status_verifikasi', 'diterima')
            ->sum('jumlah_dibayar');

        // Status 1 = Menunggu Konfirmasi, 3 = Sedang Diproses
        $pesananPending = Pesanan::whereIn('status_pesanan_id', [1, 3])->count();

        // stok_minimal bukannya stok_minimum (Stok Harian: Dine-In & Nasi Box)
        $stokMenipis = StokBahan::harian()
            ->whereColumn('jumlah_stok', '<=', 'stok_minimal')
            ->count();

        $unreadNotifikasiStok = NotifikasiStok::where('dibaca', false)->count();

        // 2. Data Grafik Pendapatan 7 Hari Terakhir
        $labels = [];
        $dataPendapatan = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $date->format('d M');

            $totalPendapatan = Pembayaran::whereDate('dibuat_pada', $date)
                ->where('status_verifikasi', 'diterima')
                ->sum('jumlah_dibayar');

            $dataPendapatan[] = $totalPendapatan;
        }

        // 3. Data Stok Menipis (List)
        $listStokMenipis = StokBahan::harian()->with('bahan_baku.satuan')
            ->whereColumn('jumlah_stok', '<=', 'stok_minimal')
            ->take(5)
            ->get();

        // 4. Pesanan Terbaru
        $pesananTerbaru = collect();

        foreach (Pesanan::with('jenis_pesanan', 'status_pesanan')->latest()->take(10)->get() as $p) {
            $pesananTerbaru->push((object) [
                'id' => $p->id,
                'no' => $p->nomor_pesanan,
                'tanggal' => $p->dibuat_pada,
                'total' => $p->total_tagihan,
                'status' => $p->status_pesanan->nama_status ?? '-',
                'jenis' => $p->jenis_pesanan->nama_jenis ?? '-',
                'url' => '#', // TODO: Fix routing later
            ]);
        }

        // 5. Pesanan Catering & Nasi Box Menunggu Konfirmasi (Ditinjau)
        // 2 = Catering, 3 = Nasi Box, status 1 = Menunggu
        $cateringMenunggu = Pesanan::where('jenis_pesanan_id', 2)
            ->where('status_pesanan_id', 1)
            ->latest()
            ->get();

        $nasiBoxMenunggu = Pesanan::where('jenis_pesanan_id', 3)
            ->where('status_pesanan_id', 1)
            ->latest()
            ->get();

        // 6. Pesanan Mendekati Batas Konfirmasi (H-3)
        $cateringUrgent = Pesanan::where('jenis_pesanan_id', 2)
            ->whereIn('status_pesanan_id', [2, 3])
            ->join('jadwal_pesanan', 'pesanan.id', '=', 'jadwal_pesanan.pesanan_id')
            ->whereDate('jadwal_pesanan.tanggal_acara', '<=', Carbon::today()->addDays(3))
            ->get();

        $nasiBoxUrgent = Pesanan::where('jenis_pesanan_id', 3)
            ->whereIn('status_pesanan_id', [2, 3])
            ->join('jadwal_pesanan', 'pesanan.id', '=', 'jadwal_pesanan.pesanan_id')
            ->whereDate('jadwal_pesanan.tanggal_acara', '<=', Carbon::today()->addDays(3))
            ->get();

        return view('dashboard.index', compact(
            'pesananHariIni',
            'pendapatanHariIni',
            'pesananPending',
            'stokMenipis',
            'unreadNotifikasiStok',
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
