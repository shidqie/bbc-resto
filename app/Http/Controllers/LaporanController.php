<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use App\Models\MutasiStok;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class LaporanController extends Controller
{
    public function penjualan(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        $query = Pesanan::with(['user', 'details.menu'])
            ->where('status_pesanan', 'selesai')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        $pesanans = $query->latest()->get();
        
        $totalPendapatan = $pesanans->sum('total_harga');
        $totalTransaksi = $pesanans->count();

        return view('laporan.penjualan', compact('pesanans', 'totalPendapatan', 'totalTransaksi', 'startDate', 'endDate'));
    }

    public function cetakPenjualan(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        $pesanans = Pesanan::with(['user', 'details.menu'])
            ->where('status_pesanan', 'selesai')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->latest()
            ->get();
            
        $totalPendapatan = $pesanans->sum('total_harga');

        $pdf = Pdf::loadView('laporan.pdf-penjualan', compact('pesanans', 'totalPendapatan', 'startDate', 'endDate'));
        return $pdf->stream('laporan-penjualan-' . $startDate . '-sd-' . $endDate . '.pdf');
    }

    public function stok(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        $mutasis = MutasiStok::with(['bahanBaku.kategoriBahan', 'bahanBaku.satuan'])
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->latest()
            ->get();

        return view('laporan.stok', compact('mutasis', 'startDate', 'endDate'));
    }

    public function cetakStok(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        $mutasis = MutasiStok::with(['bahanBaku.kategoriBahan', 'bahanBaku.satuan'])
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->latest()
            ->get();

        $pdf = Pdf::loadView('laporan.pdf-stok', compact('mutasis', 'startDate', 'endDate'));
        return $pdf->stream('laporan-mutasi-stok-' . $startDate . '-sd-' . $endDate . '.pdf');
    }
}
