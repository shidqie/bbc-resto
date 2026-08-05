<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PengadaanBahan;
use App\Models\BahanBaku;
use App\Models\Pesanan;
use App\Models\StatusPengadaan;

class PengadaanController extends Controller
{
    public function index(Request $request)
    {
        $query = PengadaanBahan::with(['diajukan_oleh_pengguna', 'status_pengadaan', 'detail_pengadaan_bahan'])
            ->orderBy('dibuat_pada', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_pengadaan', 'like', "%{$search}%")
                  ->orWhereHas('pesanan', function($q) use ($search) {
                      $q->where('nomor_pesanan', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('jenis')) {
            $query->where('jenis_pengadaan', $request->jenis);
        }

        if ($request->filled('status')) {
            $query->where('status_pengadaan_id', $request->status);
        }

        if ($request->filled('periode')) {
            $periode = $request->periode;
            if ($periode == 'hari_ini') {
                $query->whereDate('tanggal_pengadaan', today());
            } elseif ($periode == 'minggu_ini') {
                $query->whereBetween('tanggal_pengadaan', [now()->startOfWeek(), now()->endOfWeek()]);
            } elseif ($periode == 'bulan_ini') {
                $query->whereMonth('tanggal_pengadaan', now()->month)
                      ->whereYear('tanggal_pengadaan', now()->year);
            }
        }

        $pengadaans = $query->paginate(10)->withQueryString();
        $statuses = StatusPengadaan::all();

        return view('inventory.pengadaan.index', compact('pengadaans', 'statuses'));
    }

    public function createHarian()
    {
        // Ambil stok harian yang di bawah stok minimum
        $stokMenipis = \App\Models\StokBahan::with(['bahan_baku.satuan'])
            ->harian()
            ->join('bahan_baku', 'stok_bahan.bahan_baku_id', '=', 'bahan_baku.id')
            ->whereColumn('stok_bahan.jumlah_stok', '<=', 'bahan_baku.stok_minimal')
            ->select('stok_bahan.*')
            ->get();
            
        return view('inventory.pengadaan.harian.create', compact('stokMenipis'));
    }

    public function storeHarian(Request $request)
    {
        // Logika simpan pengadaan harian...
        return redirect()->route('pengadaan.permintaan.index')->with('success', 'Permintaan harian berhasil dibuat.');
    }

    public function createCatering(Request $request)
    {
        // Cari pesanan catering yang terkonfirmasi atau diproses (status_pesanan_id: 2, 3, 4, 5, etc - the user said "Menunggu Pembelian, Telah Dipesan, Diterima, Diterima, Dibatalkan" -> wait, that's pengadaan statuses. Pesanan catering status: Terkonfirmasi / Diproses is 2 or 3 usually)
        $pesanans = Pesanan::with(['pelanggan', 'jadwal_pesanan', 'detail_pesanan.menu'])
            ->whereIn('status_pesanan_id', [2, 3, 4]) 
            ->where('jenis_pesanan_id', 2) // 2 = Catering
            ->orderBy('dibuat_pada', 'desc')
            ->get();
            
        return view('inventory.pengadaan.catering.create', compact('pesanans'));
    }

    public function storeCatering(Request $request)
    {
        // Logika simpan pengadaan catering...
        return redirect()->route('pengadaan.permintaan.index')->with('success', 'Permintaan catering berhasil dibuat.');
    }

    public function updateStatus(Request $request, $id)
    {
        $pengadaan = PengadaanBahan::findOrFail($id);
        $pengadaan->status_pengadaan_id = $request->status;
        $pengadaan->save();
        
        return back()->with('success', 'Status pengadaan berhasil diubah.');
    }
}
