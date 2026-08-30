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
            return redirect()->route('home');
        }

        if ($userRole === 'Pengantaran') {
            return redirect()->route('admin.jadwal.index');
        }

        if ($userRole === 'Kasir') {
            return $this->kasirDashboard();
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
        $dataDineIn = [];
        $dataCatering = [];
        $dataNasiBox = [];

        $daysIndo = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
        ];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $daysIndo[$date->format('l')] . ', ' . $date->format('d');

            $totalPendapatan = Pembayaran::whereDate('dibuat_pada', $date)
                ->where('status_verifikasi', 'diterima')
                ->sum('jumlah_dibayar');

            $dataPendapatan[] = $totalPendapatan;

            // Hitung jumlah pesanan per jenis
            $dataDineIn[] = Pesanan::whereDate('dibuat_pada', $date)->where('jenis_pesanan_id', 1)->count();
            $dataCatering[] = Pesanan::whereDate('dibuat_pada', $date)->where('jenis_pesanan_id', 2)->count();
            $dataNasiBox[] = Pesanan::whereDate('dibuat_pada', $date)->where('jenis_pesanan_id', 3)->count();
        }

        // 3. Data Stok Menipis (List)
        $listStokMenipis = StokBahan::harian()->with('bahan_baku.satuan')
            ->whereColumn('jumlah_stok', '<=', 'stok_minimal')
            ->take(5)
            ->get();

        // 4. Pesanan Terbaru
        $pesananTerbaru = collect();

        foreach (Pesanan::with('jenis_pesanan', 'status_pesanan')->latest('dibuat_pada')->take(10)->get() as $p) {
            $pesananTerbaru->push((object) [
                'id' => $p->id,
                'no' => $p->id_pesanan,
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
            ->latest('dibuat_pada')
            ->get();

        $nasiBoxMenunggu = Pesanan::where('jenis_pesanan_id', 3)
            ->where('status_pesanan_id', 1)
            ->latest('dibuat_pada')
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

        $totalPendapatan7Hari = array_sum($dataPendapatan);
        $totalPesanan7Hari = array_sum($dataDineIn) + array_sum($dataCatering) + array_sum($dataNasiBox);
        $totalDineIn7Hari = array_sum($dataDineIn);
        $totalCatering7Hari = array_sum($dataCatering);
        $totalNasiBox7Hari = array_sum($dataNasiBox);

        return view('admin.dashboard.index', compact(
            'pesananHariIni',
            'pendapatanHariIni',
            'pesananPending',
            'stokMenipis',
            'unreadNotifikasiStok',
            'labels',
            'dataPendapatan',
            'dataDineIn',
            'dataCatering',
            'dataNasiBox',
            'totalPendapatan7Hari',
            'totalPesanan7Hari',
            'totalDineIn7Hari',
            'totalCatering7Hari',
            'totalNasiBox7Hari',
            'listStokMenipis',
            'pesananTerbaru',
            'cateringMenunggu',
            'nasiBoxMenunggu',
            'cateringUrgent',
            'nasiBoxUrgent'
        ));
    }

    public function kasirDashboard()
    {
        $today = Carbon::today();

        // 1. Omset Kasir Hari Ini
        $omsetHariIni = Pembayaran::whereDate('dibuat_pada', $today)
            ->where('status_verifikasi', 'diterima')
            ->sum('jumlah_dibayar');

        // 2. Transaksi Selesai Hari Ini
        $transaksiSelesaiCount = Pesanan::whereDate('dibuat_pada', $today)
            ->where('status_pesanan_id', 4)
            ->count();

        // 3. Tagihan Belum Lunas Hari Ini (Dine In & POS)
        $pesananBelumBayar = Pesanan::with(['meja', 'detail_pesanan.menu', 'status_pesanan', 'pembayaran'])
            ->where('jenis_pesanan_id', 1)
            ->where('status_pembayaran_id', '!=', 5)
            ->whereDate('dibuat_pada', $today)
            ->latest('dibuat_pada')
            ->get();

        $pesananBelumBayarCount = $pesananBelumBayar->count();

        // 4. Meja Status
        $allMeja = \App\Models\Meja::with('status_meja')->orderBy('nomor_meja', 'asc')->get();
        $mejaTerisiCount = $allMeja->where('status_meja_id', 2)->count();
        $mejaTersediaCount = $allMeja->where('status_meja_id', 1)->count();

        // 5. Transaksi Lunas Terakhir Hari Ini
        $transaksiTerakhir = Pesanan::with(['meja', 'status_pesanan', 'pembayaran'])
            ->whereDate('dibuat_pada', $today)
            ->where('status_pembayaran_id', 5)
            ->latest('dibuat_pada')
            ->take(8)
            ->get();

        return view('kasir.dashboard', compact(
            'omsetHariIni',
            'transaksiSelesaiCount',
            'pesananBelumBayar',
            'pesananBelumBayarCount',
            'allMeja',
            'mejaTerisiCount',
            'mejaTersediaCount',
            'transaksiTerakhir'
        ));
    }
}
