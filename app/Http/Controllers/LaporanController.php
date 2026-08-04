<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\DetailPengadaanBahan;
use App\Models\KategoriBahanBaku;
use App\Models\MutasiStok;
use App\Models\PengadaanBahan;
use App\Models\Pesanan;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    public function penjualan(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        $jenisPenjualan = $request->input('jenis', '');
        $statusPembayaran = $request->input('status_pembayaran', '');

        $query = Pesanan::with(['jenis_pesanan', 'pelanggan', 'meja', 'pembayaran.status_pembayaran'])
            ->whereBetween('tanggal_pesanan', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->whereNotIn('status_pesanan_id', [6]);

        if ($jenisPenjualan === 'dinein') {
            $query->where('jenis_pesanan_id', 1);
        } elseif ($jenisPenjualan === 'catering') {
            $query->where('jenis_pesanan_id', 2);
        } elseif ($jenisPenjualan === 'nasibox') {
            $query->where('jenis_pesanan_id', 3);
        }

        if ($statusPembayaran) {
            $query->whereHas('pembayaran.status_pembayaran', fn ($q) => $q->where('kode_status', $statusPembayaran));
        }

        $pesanansAll = $query->orderByDesc('tanggal_pesanan')->get();
        $totalTransaksi = $pesanansAll->count();
        $totalPenjualan = $pesanansAll->sum('total_tagihan');
        $totalDibayar = $pesanansAll->sum(fn ($p) => $p->pembayaran->sum('jumlah_bayar'));
        $totalPiutang = max(0, $totalPenjualan - $totalDibayar);

        $perPage = 15;
        $page = Paginator::resolveCurrentPage() ?: 1;
        $pesanans = new LengthAwarePaginator(
            $pesanansAll->forPage($page, $perPage),
            $pesanansAll->count(), $perPage, $page,
            ['path' => Paginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        $stats = compact('totalTransaksi', 'totalPenjualan', 'totalDibayar', 'totalPiutang');

        return view('laporan.penjualan.index', compact(
            'pesanans', 'stats', 'startDate', 'endDate', 'jenisPenjualan', 'statusPembayaran'
        ));
    }

    public function cetakPenjualan(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        $jenisPenjualan = $request->input('jenis', '');
        $statusPembayaran = $request->input('status_pembayaran', '');

        $query = Pesanan::with(['jenis_pesanan', 'pelanggan', 'meja', 'pembayaran.status_pembayaran', 'detail_pesanan.menu'])
            ->whereBetween('tanggal_pesanan', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->whereNotIn('status_pesanan_id', [6]);

        if ($jenisPenjualan === 'dinein') {
            $query->where('jenis_pesanan_id', 1);
        } elseif ($jenisPenjualan === 'catering') {
            $query->where('jenis_pesanan_id', 2);
        } elseif ($jenisPenjualan === 'nasibox') {
            $query->where('jenis_pesanan_id', 3);
        }

        $pesanans = $query->orderByDesc('tanggal_pesanan')->get();
        $totalPenjualan = $pesanans->sum('total_tagihan');
        $totalDibayar = $pesanans->sum(fn ($p) => $p->pembayaran->sum('jumlah_bayar'));
        $totalPiutang = max(0, $totalPenjualan - $totalDibayar);
        $stats = compact('totalPenjualan', 'totalDibayar', 'totalPiutang');
        $cetakOleh = Auth::user()->nama ?? '-';

        $pdf = Pdf::loadView('laporan.penjualan.pdf', compact(
            'pesanans', 'stats', 'startDate', 'endDate', 'jenisPenjualan', 'cetakOleh'
        ))->setPaper('a4', 'landscape');

        return $pdf->stream('laporan-penjualan-'.$startDate.'-sd-'.$endDate.'.pdf');
    }

    public function stok(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        $jenisPersediaan = $request->input('jenis_persediaan', '');
        $kategoriId = $request->input('kategori_id', '');
        $bahanBakuId = $request->input('bahan_baku_id', '');

        $kategoris = KategoriBahanBaku::orderBy('nama_kategori')->get();
        $bahanBakus = BahanBaku::with('satuan')->where('status_aktif', true)->orderBy('nama_bahan')->get();

        $queryBahan = BahanBaku::with(['satuan', 'kategori_bahan_baku'])->where('status_aktif', true);
        if ($kategoriId) {
            $queryBahan->where('kategori_bahan_baku_id', $kategoriId);
        }
        if ($bahanBakuId) {
            $queryBahan->where('id', $bahanBakuId);
        }

        $laporanBahan = $queryBahan->orderBy('nama_bahan')->get()->map(function ($bahan) use ($startDate, $endDate, $jenisPersediaan) {
            $mutasiSebelum = MutasiStok::where('bahan_baku_id', $bahan->id)
                ->where('tanggal_mutasi', '<', $startDate.' 00:00:00')
                ->with('jenis_mutasi_stok')->get();

            $stokAwal = $mutasiSebelum->sum(function ($m) {
                return ($m->jenis_mutasi_stok->arah_stok ?? 'MASUK') === 'MASUK' ? (float) $m->jumlah : -(float) $m->jumlah;
            });

            $qMutasi = MutasiStok::where('bahan_baku_id', $bahan->id)
                ->whereBetween('tanggal_mutasi', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
                ->with('jenis_mutasi_stok');
            if ($jenisPersediaan) {
                $qMutasi->where('jenis_persediaan', strtolower($jenisPersediaan) === 'operasional' ? 'harian' : strtolower($jenisPersediaan));
            }

            $mutasiPeriode = $qMutasi->get();
            $stokMasuk = $mutasiPeriode->filter(fn ($m) => ($m->jenis_mutasi_stok->arah_stok ?? '') === 'MASUK')->sum('jumlah');
            $stokKeluar = $mutasiPeriode->filter(fn ($m) => ($m->jenis_mutasi_stok->arah_stok ?? '') === 'KELUAR')->sum('jumlah');
            $stokAkhir = $stokAwal + $stokMasuk - $stokKeluar;
            $stokMin = (float) $bahan->stok_minimal;
            $status = $stokAkhir <= 0 ? 'Habis' : ($stokAkhir <= $stokMin ? 'Minimum' : 'Aman');

            return [
                'bahan' => $bahan,
                'stok_awal' => $stokAwal,
                'stok_masuk' => $stokMasuk,
                'stok_keluar' => $stokKeluar,
                'penyesuaian' => 0,
                'stok_akhir' => $stokAkhir,
                'status' => $status,
            ];
        })->filter(fn ($item) => $item['stok_masuk'] > 0 || $item['stok_keluar'] > 0 || $item['stok_awal'] > 0);

        $stats = [
            'total_jenis' => $laporanBahan->count(),
            'total_aman' => $laporanBahan->where('status', 'Aman')->count(),
            'total_min' => $laporanBahan->where('status', 'Minimum')->count(),
            'total_habis' => $laporanBahan->where('status', 'Habis')->count(),
        ];

        return view('laporan.stok.index', compact(
            'laporanBahan', 'stats', 'startDate', 'endDate',
            'jenisPersediaan', 'kategoriId', 'bahanBakuId', 'kategoris', 'bahanBakus'
        ));
    }

    public function cetakStok(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        $jenisPersediaan = $request->input('jenis_persediaan', '');
        $kategoriId = $request->input('kategori_id', '');
        $bahanBakuId = $request->input('bahan_baku_id', '');

        $queryBahan = BahanBaku::with(['satuan', 'kategori_bahan_baku'])->where('status_aktif', true);
        if ($kategoriId) {
            $queryBahan->where('kategori_bahan_baku_id', $kategoriId);
        }
        if ($bahanBakuId) {
            $queryBahan->where('id', $bahanBakuId);
        }

        $laporanBahan = $queryBahan->orderBy('nama_bahan')->get()->map(function ($bahan) use ($startDate, $endDate, $jenisPersediaan) {
            $mutasiSebelum = MutasiStok::where('bahan_baku_id', $bahan->id)
                ->where('tanggal_mutasi', '<', $startDate.' 00:00:00')
                ->with('jenis_mutasi_stok')->get();
            $stokAwal = $mutasiSebelum->sum(function ($m) {
                return ($m->jenis_mutasi_stok->arah_stok ?? 'MASUK') === 'MASUK' ? (float) $m->jumlah : -(float) $m->jumlah;
            });
            $qMutasi = MutasiStok::where('bahan_baku_id', $bahan->id)
                ->whereBetween('tanggal_mutasi', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
                ->with('jenis_mutasi_stok');
            if ($jenisPersediaan) {
                $qMutasi->where('jenis_persediaan', strtolower($jenisPersediaan) === 'operasional' ? 'harian' : strtolower($jenisPersediaan));
            }
            $mutasiPeriode = $qMutasi->get();
            $stokMasuk = $mutasiPeriode->filter(fn ($m) => ($m->jenis_mutasi_stok->arah_stok ?? '') === 'MASUK')->sum('jumlah');
            $stokKeluar = $mutasiPeriode->filter(fn ($m) => ($m->jenis_mutasi_stok->arah_stok ?? '') === 'KELUAR')->sum('jumlah');
            $stokAkhir = $stokAwal + $stokMasuk - $stokKeluar;
            $stokMin = (float) $bahan->stok_minimal;
            $status = $stokAkhir <= 0 ? 'Habis' : ($stokAkhir <= $stokMin ? 'Minimum' : 'Aman');

            return compact('bahan', 'stokAwal', 'stokMasuk', 'stokKeluar', 'stokAkhir', 'status');
        })->filter(fn ($item) => $item['stokMasuk'] > 0 || $item['stokKeluar'] > 0 || $item['stokAwal'] > 0);

        $cetakOleh = Auth::user()->nama ?? '-';
        $pdf = Pdf::loadView('laporan.stok.pdf', compact(
            'laporanBahan', 'startDate', 'endDate', 'jenisPersediaan', 'cetakOleh'
        ))->setPaper('a4', 'landscape');

        return $pdf->stream('laporan-persediaan-'.$startDate.'-sd-'.$endDate.'.pdf');
    }

    public function pengadaan(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        $pengadaans = PengadaanBahan::with(['detail_pengadaan_bahan.bahan_baku.kategori_bahan_baku', 'diajukan_oleh_pengguna'])
            ->whereBetween('tanggal_pengadaan', [$startDate, $endDate])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $totalBiaya = PengadaanBahan::whereBetween('tanggal_pengadaan', [$startDate, $endDate])->sum('total_pengadaan');
        $totalTransaksi = PengadaanBahan::whereBetween('tanggal_pengadaan', [$startDate, $endDate])->count();

        $topBahan = DetailPengadaanBahan::with('bahan_baku')
            ->selectRaw('bahan_baku_id, SUM(jumlah_dipesan) as total_jumlah, SUM(subtotal) as total_pengadaan, COUNT(*) as frekuensi')
            ->whereHas('pengadaan_bahan', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('tanggal_pengadaan', [$startDate, $endDate]);
            })
            ->groupBy('bahan_baku_id')
            ->orderBy('total_pengadaan', 'desc')
            ->limit(10)
            ->get();

        return view('laporan.pengadaan.index', compact('pengadaans', 'totalBiaya', 'totalTransaksi', 'topBahan', 'startDate', 'endDate'));
    }

    public function cetakPengadaan(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        $pengadaans = PengadaanBahan::with(['detail_pengadaan_bahan.bahan_baku', 'diajukan_oleh_pengguna'])
            ->whereBetween('tanggal_pengadaan', [$startDate, $endDate])
            ->latest()->get();

        $totalBiaya = $pengadaans->sum('total_pengadaan');

        $pdf = Pdf::loadView('laporan.pengadaan.pdf', compact('pengadaans', 'totalBiaya', 'startDate', 'endDate'));

        return $pdf->stream('laporan-pengadaan-'.$startDate.'-sd-'.$endDate.'.pdf');
    }

    public function menuTerlaris(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        $menuDineIn = DB::table('detail_pesanan')
            ->join('menu', 'detail_pesanan.menu_id', '=', 'menu.id')
            ->join('pesanan', 'detail_pesanan.pesanan_id', '=', 'pesanan.id')
            ->select('menu.id', 'menu.nama_menu as nama', 'menu.harga_jual as harga', DB::raw('SUM(detail_pesanan.jumlah) as total_qty'), DB::raw('SUM(detail_pesanan.subtotal) as total_pendapatan'))
            ->where('pesanan.status_pesanan_id', 5)
            ->whereBetween('pesanan.dibuat_pada', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->groupBy('menu.id', 'menu.nama_menu', 'menu.harga_jual')
            ->orderBy('total_qty', 'desc')
            ->get();

        $menuTerlaris = $menuDineIn;
        $totalTerjual = $menuTerlaris->sum('total_qty');
        $totalPendapatan = $menuTerlaris->sum('total_pendapatan');

        return view('laporan.menu.index', compact('menuTerlaris', 'totalTerjual', 'totalPendapatan', 'startDate', 'endDate'));
    }

    public function cetakMenuTerlaris(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        $menuDineIn = DB::table('detail_pesanan')
            ->join('menu', 'detail_pesanan.menu_id', '=', 'menu.id')
            ->join('pesanan', 'detail_pesanan.pesanan_id', '=', 'pesanan.id')
            ->select('menu.id', 'menu.nama_menu as nama', 'menu.harga_jual as harga', DB::raw('SUM(detail_pesanan.jumlah) as total_qty'), DB::raw('SUM(detail_pesanan.subtotal) as total_pendapatan'))
            ->where('pesanan.status_pesanan_id', 5)
            ->whereBetween('pesanan.dibuat_pada', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->groupBy('menu.id', 'menu.nama_menu', 'menu.harga_jual')
            ->orderBy('total_qty', 'desc')
            ->get();

        $menuTerlaris = $menuDineIn;
        $totalTerjual = $menuTerlaris->sum('total_qty');
        $totalPendapatan = $menuTerlaris->sum('total_pendapatan');

        $pdf = Pdf::loadView('laporan.menu.pdf', compact('menuTerlaris', 'totalTerjual', 'totalPendapatan', 'startDate', 'endDate'));

        return $pdf->stream('laporan-menu-terlaris-'.$startDate.'-sd-'.$endDate.'.pdf');
    }
}
