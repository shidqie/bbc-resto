<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use App\Models\MutasiStok;
use App\Models\PesananCatering;
use App\Models\PesananNasiBox;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        // Rekap Penggunaan Harian Stok (Grouped per tanggal & bahan baku)
        $penggunaanHarian = MutasiStok::with(['bahanBaku.satuan'])
            ->selectRaw('DATE(created_at) as tanggal, bahan_baku_id, SUM(ABS(jumlah)) as total_penggunaan')
            ->where('jenis_mutasi', 'keluar')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->groupBy(DB::raw('DATE(created_at)'), 'bahan_baku_id')
            ->orderBy('tanggal', 'desc')
            ->get();

        // Total akumulasi penggunaan per bahan baku
        $totalPenggunaanPerBahan = MutasiStok::with(['bahanBaku.satuan'])
            ->selectRaw('bahan_baku_id, SUM(ABS(jumlah)) as total_penggunaan')
            ->where('jenis_mutasi', 'keluar')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->groupBy('bahan_baku_id')
            ->orderBy('total_penggunaan', 'desc')
            ->get();

        return view('laporan.stok', compact('mutasis', 'penggunaanHarian', 'totalPenggunaanPerBahan', 'startDate', 'endDate'));
    }

    public function cetakStok(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        $mutasis = MutasiStok::with(['bahanBaku.kategoriBahan', 'bahanBaku.satuan'])
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->latest()
            ->get();

        $penggunaanHarian = MutasiStok::with(['bahanBaku.satuan'])
            ->selectRaw('DATE(created_at) as tanggal, bahan_baku_id, SUM(ABS(jumlah)) as total_penggunaan')
            ->where('jenis_mutasi', 'keluar')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->groupBy(DB::raw('DATE(created_at)'), 'bahan_baku_id')
            ->orderBy('tanggal', 'desc')
            ->get();

        $totalPenggunaanPerBahan = MutasiStok::with(['bahanBaku.satuan'])
            ->selectRaw('bahan_baku_id, SUM(ABS(jumlah)) as total_penggunaan')
            ->where('jenis_mutasi', 'keluar')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->groupBy('bahan_baku_id')
            ->orderBy('total_penggunaan', 'desc')
            ->get();

        $pdf = Pdf::loadView('laporan.pdf-stok', compact('mutasis', 'penggunaanHarian', 'totalPenggunaanPerBahan', 'startDate', 'endDate'));
        return $pdf->stream('laporan-mutasi-stok-' . $startDate . '-sd-' . $endDate . '.pdf');
    }

    public function catering(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        $query = PesananCatering::with(['paket'])
            ->whereIn('status', ['terkonfirmasi', 'lunas'])
            ->whereBetween('tanggal_acara', [$startDate, $endDate]);

        $pesanans = $query->latest()->get();
        
        $totalPendapatan = $pesanans->sum('total_tagihan');
        $totalTransaksi = $pesanans->count();

        return view('laporan.catering', compact('pesanans', 'totalPendapatan', 'totalTransaksi', 'startDate', 'endDate'));
    }

    public function cetakCatering(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        $pesanans = PesananCatering::with(['paket'])
            ->whereIn('status', ['terkonfirmasi', 'lunas'])
            ->whereBetween('tanggal_acara', [$startDate, $endDate])
            ->latest()
            ->get();
            
        $totalPendapatan = $pesanans->sum('total_tagihan');

        $pdf = Pdf::loadView('laporan.pdf-catering', compact('pesanans', 'totalPendapatan', 'startDate', 'endDate'));
        return $pdf->stream('laporan-catering-' . $startDate . '-sd-' . $endDate . '.pdf');
    }

    public function nasibox(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        $query = PesananNasiBox::with(['menu'])
            ->whereIn('status', ['terkonfirmasi', 'lunas'])
            ->whereBetween('tanggal_acara', [$startDate, $endDate]);

        $pesanans = $query->latest()->get();
        
        $totalPendapatan = $pesanans->sum('total_tagihan');
        $totalTransaksi = $pesanans->count();

        return view('laporan.nasibox', compact('pesanans', 'totalPendapatan', 'totalTransaksi', 'startDate', 'endDate'));
    }

    public function cetakNasiBox(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        $pesanans = PesananNasiBox::with(['menu'])
            ->whereIn('status', ['terkonfirmasi', 'lunas'])
            ->whereBetween('tanggal_acara', [$startDate, $endDate])
            ->latest()
            ->get();
            
        $totalPendapatan = $pesanans->sum('total_tagihan');

        $pdf = Pdf::loadView('laporan.pdf-nasibox', compact('pesanans', 'totalPendapatan', 'startDate', 'endDate'));
        return $pdf->stream('laporan-nasibox-' . $startDate . '-sd-' . $endDate . '.pdf');
    }
}