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
        $jenisFilter = $request->input('jenis', 'semua');
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        $data = $this->getGabunganPenjualan($jenisFilter, $startDate, $endDate);

        $pesanansAll = $data->sortByDesc('tanggal')->values();
        $totalPendapatan = $pesanansAll->sum('total');
        $totalTransaksi = $pesanansAll->count();

        $perPage = 15;
        $page = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;
        $pesanans = new \Illuminate\Pagination\LengthAwarePaginator(
            $pesanansAll->forPage($page, $perPage),
            $pesanansAll->count(),
            $perPage,
            $page,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        return view('laporan.penjualan', compact('pesanans', 'totalPendapatan', 'totalTransaksi', 'startDate', 'endDate', 'jenisFilter'));
    }

    public function cetakPenjualan(Request $request)
    {
        $jenisFilter = $request->input('jenis', 'semua');
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        $data = $this->getGabunganPenjualan($jenisFilter, $startDate, $endDate);

        $pesanans = $data->sortByDesc('tanggal')->values();
        $totalPendapatan = $pesanans->sum('total');

        $pdf = Pdf::loadView('laporan.pdf-penjualan', compact('pesanans', 'totalPendapatan', 'startDate', 'endDate', 'jenisFilter'));
        return $pdf->stream('laporan-penjualan-' . $startDate . '-sd-' . $endDate . '.pdf');
    }

    private function getGabunganPenjualan($jenisFilter, $startDate, $endDate)
    {
        $data = collect();

        // 1. Pesanan Reguler (Dine In)
        if ($jenisFilter === 'semua' || $jenisFilter === 'reguler') {
            $reguler = \App\Models\PesananDinein::with(['kasir', 'items.menu'])
                ->where('status', 'lunas')
                ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->get()
                ->map(function($item) {
                    $total = $item->items->sum(function($i) {
                        return $i->qty * ($i->menu ? $i->menu->harga : 0);
                    });
                    return (object) [
                        'tanggal' => $item->created_at,
                        'kode' => $item->kode_pesanan ?? 'DIN-'.$item->id,
                        'pelanggan' => $item->nama_konsumen ?? 'Pelanggan Reguler',
                        'jenis' => 'Reguler (Dine-In)',
                        'total' => $total,
                        'status' => $item->status,
                    ];
                });
            $data = $data->concat($reguler);
        }

        // 2. Pesanan Catering
        if ($jenisFilter === 'semua' || $jenisFilter === 'catering') {
            $catering = \App\Models\PesananCatering::whereIn('status', ['lunas', 'selesai'])
                ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->get()
                ->map(function($item) {
                    return (object) [
                        'tanggal' => $item->created_at,
                        'kode' => $item->kode_pesanan,
                        'pelanggan' => $item->nama_pemesan,
                        'jenis' => 'Catering',
                        'total' => $item->total_tagihan,
                        'status' => $item->status,
                    ];
                });
            $data = $data->concat($catering);
        }

        // 3. Pesanan Nasi Box
        if ($jenisFilter === 'semua' || $jenisFilter === 'nasibox') {
            $nasibox = \App\Models\PesananNasiBox::whereIn('status', ['lunas', 'selesai'])
                ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->get()
                ->map(function($item) {
                    return (object) [
                        'tanggal' => $item->created_at,
                        'kode' => $item->kode_pesanan,
                        'pelanggan' => $item->nama_pemesan,
                        'jenis' => 'Nasi Box',
                        'total' => $item->total_tagihan,
                        'status' => $item->status,
                    ];
                });
            $data = $data->concat($nasibox);
        }

        return $data;
    }

    public function stok(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        $mutasis = MutasiStok::with(['bahanBaku.kategoriBahan', 'bahanBaku.satuan'])
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->latest()
            ->paginate(15)->withQueryString();

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

    /**
     * Laporan Pengadaan Bahan Baku
     */
    public function pengadaan(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        $pengadaans = \App\Models\Pengadaan::with(['details.bahanBaku.kategoriBahan', 'user'])
            ->whereBetween('tanggal_pengadaan', [$startDate, $endDate])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $totalBiaya = \App\Models\Pengadaan::whereBetween('tanggal_pengadaan', [$startDate, $endDate])->sum('total_biaya');
        $totalTransaksi = \App\Models\Pengadaan::whereBetween('tanggal_pengadaan', [$startDate, $endDate])->count();

        // Top bahan baku yang sering diadakan
        $topBahan = \App\Models\DetailPengadaan::with('bahanBaku')
            ->selectRaw('bahan_baku_id, SUM(jumlah) as total_jumlah, SUM(subtotal) as total_biaya, COUNT(*) as frekuensi')
            ->whereHas('pengadaan', function($q) use ($startDate, $endDate) {
                $q->whereBetween('tanggal_pengadaan', [$startDate, $endDate]);
            })
            ->groupBy('bahan_baku_id')
            ->orderBy('total_biaya', 'desc')
            ->limit(10)
            ->get();

        return view('laporan.pengadaan', compact('pengadaans', 'totalBiaya', 'totalTransaksi', 'topBahan', 'startDate', 'endDate'));
    }

    public function cetakPengadaan(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        $pengadaans = \App\Models\Pengadaan::with(['details.bahanBaku', 'user'])
            ->whereBetween('tanggal_pengadaan', [$startDate, $endDate])
            ->latest()->get();

        $totalBiaya = $pengadaans->sum('total_biaya');

        $pdf = Pdf::loadView('laporan.pdf-pengadaan', compact('pengadaans', 'totalBiaya', 'startDate', 'endDate'));
        return $pdf->stream('laporan-pengadaan-' . $startDate . '-sd-' . $endDate . '.pdf');
    }

    /**
     * Laporan Menu Terlaris
     */
    public function menuTerlaris(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        // Top menu dari Dine-In
        $menuDineIn = DB::table('item_pesanan_dineins')
            ->join('menus', 'item_pesanan_dineins.menu_id', '=', 'menus.id')
            ->join('pesanan_dineins', 'item_pesanan_dineins.pesanan_dinein_id', '=', 'pesanan_dineins.id')
            ->select('menus.id', 'menus.nama', 'menus.harga', DB::raw('SUM(item_pesanan_dineins.qty) as total_qty'), DB::raw('SUM(item_pesanan_dineins.qty * menus.harga) as total_pendapatan'))
            ->where('pesanan_dineins.status', 'lunas')
            ->whereBetween('pesanan_dineins.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->groupBy('menus.id', 'menus.nama', 'menus.harga')
            ->orderBy('total_qty', 'desc')
            ->get();

        // Gabungkan dan urutkan
        $allMenu = collect();
        foreach ($menuDineIn as $m) {
            $existing = $allMenu->firstWhere('id', $m->id);
            if ($existing) {
                $existing->total_qty += $m->total_qty;
                $existing->total_pendapatan += $m->total_pendapatan;
            } else {
                $allMenu->push((object)[
                    'id' => $m->id,
                    'nama' => $m->nama,
                    'harga' => $m->harga,
                    'total_qty' => $m->total_qty,
                    'total_pendapatan' => $m->total_pendapatan,
                ]);
            }
        }

        $menuTerlaris = $allMenu->sortByDesc('total_qty')->values();
        $totalTerjual = $menuTerlaris->sum('total_qty');
        $totalPendapatan = $menuTerlaris->sum('total_pendapatan');

        return view('laporan.menu-terlaris', compact('menuTerlaris', 'totalTerjual', 'totalPendapatan', 'startDate', 'endDate'));
    }

    public function cetakMenuTerlaris(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        $menuDineIn = DB::table('item_pesanan_dineins')
            ->join('menus', 'item_pesanan_dineins.menu_id', '=', 'menus.id')
            ->join('pesanan_dineins', 'item_pesanan_dineins.pesanan_dinein_id', '=', 'pesanan_dineins.id')
            ->select('menus.id', 'menus.nama', 'menus.harga', DB::raw('SUM(item_pesanan_dineins.qty) as total_qty'), DB::raw('SUM(item_pesanan_dineins.qty * menus.harga) as total_pendapatan'))
            ->where('pesanan_dineins.status', 'lunas')
            ->whereBetween('pesanan_dineins.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->groupBy('menus.id', 'menus.nama', 'menus.harga')
            ->orderBy('total_qty', 'desc')
            ->get();

        $menuTerlaris = $menuDineIn->sortByDesc('total_qty')->values();
        $totalTerjual = $menuTerlaris->sum('total_qty');
        $totalPendapatan = $menuTerlaris->sum('total_pendapatan');

        $pdf = Pdf::loadView('laporan.pdf-menu-terlaris', compact('menuTerlaris', 'totalTerjual', 'totalPendapatan', 'startDate', 'endDate'));
        return $pdf->stream('laporan-menu-terlaris-' . $startDate . '-sd-' . $endDate . '.pdf');
    }
}