<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PenerimaanBahan;
use App\Models\PengadaanBahan;
use App\Models\StatusPengadaan;

class PenerimaanBahanController extends Controller
{
    public function index(Request $request)
    {
        $query = PenerimaanBahan::with(['pengadaan_bahan.pemasok', 'diterima_oleh_pengguna'])
            ->orderBy('diterima_pada', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_penerimaan', 'like', "%{$search}%")
                  ->orWhereHas('pengadaan_bahan', function($q) use ($search) {
                      $q->where('nomor_pengadaan', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('jenis')) {
            $jenis = $request->jenis;
            $query->whereHas('pengadaan_bahan', function($q) use ($jenis) {
                $q->where('jenis_pengadaan', $jenis);
            });
        }

        if ($request->filled('status')) {
            $status = $request->status;
            $query->whereHas('pengadaan_bahan', function($q) use ($status) {
                $q->where('status_pengadaan_id', $status);
            });
        }

        if ($request->filled('periode')) {
            $periode = $request->periode;
            if ($periode == 'hari_ini') {
                $query->whereDate('diterima_pada', today());
            } elseif ($periode == 'minggu_ini') {
                $query->whereBetween('diterima_pada', [now()->startOfWeek(), now()->endOfWeek()]);
            } elseif ($periode == 'bulan_ini') {
                $query->whereMonth('diterima_pada', now()->month)
                      ->whereYear('diterima_pada', now()->year);
            }
        }

        $penerimaans = $query->paginate(10)->withQueryString();
        // Hanya ambil status yang relevan untuk penerimaan (Diterima Sebagian, Diterima Lengkap)
        $statuses = StatusPengadaan::whereIn('id', [3, 4])->get();

        return view('inventory.pengadaan.penerimaan.index', compact('penerimaans', 'statuses'));
    }

    public function store(Request $request)
    {
        // Logika simpan penerimaan bahan...
        return redirect()->route('pengadaan.penerimaan.index')->with('success', 'Penerimaan bahan berhasil dicatat.');
    }
}
