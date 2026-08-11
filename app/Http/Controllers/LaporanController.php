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
    // ==========================================
    // LAPORAN PENJUALAN
    // ==========================================
    public function penjualan(Request $request)
    {
        $periode = $request->input('periode', 'bulan_ini');
        $periode = is_array($periode) ? ($periode[0] ?? 'bulan_ini') : $periode;
        $startDate = null;
        $endDate = null;

        if ($periode == 'hari_ini') {
            $startDate = Carbon::today()->format('Y-m-d');
            $endDate = Carbon::today()->format('Y-m-d');
        } elseif ($periode == 'minggu_ini') {
            $startDate = Carbon::now()->startOfWeek()->format('Y-m-d');
            $endDate = Carbon::now()->endOfWeek()->format('Y-m-d');
        } elseif ($periode == 'bulan_ini') {
            $startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
            $endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        } elseif ($periode == 'custom') {
            $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        }

        $jenisPenjualan = $request->input('jenis', []);
        $jenisPenjualan = is_array($jenisPenjualan) ? $jenisPenjualan : (array) $jenisPenjualan;
        

        
        $search = $request->input('search', '');

        $query = Pesanan::with(['jenis_pesanan', 'pelanggan', 'meja'])
            ->whereBetween('tanggal_pesanan', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->whereNotIn('status_pesanan_id', [6]);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('id_pesanan', 'like', "%{$search}%")
                  ->orWhereHas('pelanggan', function($q) use ($search) {
                      $q->where('nama', 'like', "%{$search}%");
                  });
            });
        }

        if (!empty($jenisPenjualan)) {
            $jenisIds = [];
            if(in_array('dinein', $jenisPenjualan)) $jenisIds[] = 1;
            if(in_array('catering', $jenisPenjualan)) $jenisIds[] = 2;
            if(in_array('nasibox', $jenisPenjualan)) $jenisIds[] = 3;
            
            if (!empty($jenisIds)) {
                $query->whereIn('jenis_pesanan_id', $jenisIds);
            }
        }

        // Hanya hitung penjualan yang sudah Lunas (3) atau Selesai (5)
        $query->where(function($q) {
            $q->where('status_pembayaran_id', 3)
              ->orWhere('status_pesanan_id', 5);
        });

        $pesanansAll = $query->orderByDesc('tanggal_pesanan')->get();
        
        // Statistik
        $totalTransaksi = $pesanansAll->count();
        $totalPendapatan = $pesanansAll->sum('total_tagihan');
        $totalDineIn = $pesanansAll->where('jenis_pesanan_id', 1)->count();
        $totalCatering = $pesanansAll->where('jenis_pesanan_id', 2)->count();
        $totalNasiBox = $pesanansAll->where('jenis_pesanan_id', 3)->count();

        $perPage = 10;
        $page = Paginator::resolveCurrentPage() ?: 1;
        $pesanans = new LengthAwarePaginator(
            $pesanansAll->forPage($page, $perPage),
            $pesanansAll->count(), $perPage, $page,
            ['path' => Paginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        $stats = compact('totalTransaksi', 'totalPendapatan', 'totalDineIn', 'totalCatering', 'totalNasiBox');

        return view('admin.laporan.penjualan.index', compact(
            'pesanans', 'stats', 'startDate', 'endDate', 'jenisPenjualan', 'periode'
        ));
    }

    public function detailPenjualan($id)
    {
        $pesanan = Pesanan::with(['jenis_pesanan', 'pelanggan', 'meja', 'detail_pesanan.menu'])->findOrFail($id);
        return view('admin.laporan.penjualan.detail', compact('pesanan'));
    }

    public function cetakPenjualanPdf(Request $request)
    {
        $periode = $request->input('periode', 'bulan_ini');
        $periode = is_array($periode) ? ($periode[0] ?? 'bulan_ini') : $periode;
        $startDate = null;
        $endDate = null;

        if ($periode == 'hari_ini') {
            $startDate = Carbon::today()->format('Y-m-d');
            $endDate = Carbon::today()->format('Y-m-d');
        } elseif ($periode == 'minggu_ini') {
            $startDate = Carbon::now()->startOfWeek()->format('Y-m-d');
            $endDate = Carbon::now()->endOfWeek()->format('Y-m-d');
        } elseif ($periode == 'bulan_ini') {
            $startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
            $endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        } elseif ($periode == 'custom') {
            $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        }

        $jenisPenjualan = $request->input('jenis', []);
        $jenisPenjualan = is_array($jenisPenjualan) ? $jenisPenjualan : (array) $jenisPenjualan;
        
        $search = $request->input('search', '');

        $query = Pesanan::with(['jenis_pesanan', 'pelanggan', 'meja'])
            ->whereBetween('tanggal_pesanan', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->whereNotIn('status_pesanan_id', [6]);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('id_pesanan', 'like', "%{$search}%")
                  ->orWhereHas('pelanggan', function($q) use ($search) {
                      $q->where('nama', 'like', "%{$search}%");
                  });
            });
        }

        if (!empty($jenisPenjualan)) {
            $jenisIds = [];
            if(in_array('dinein', $jenisPenjualan)) $jenisIds[] = 1;
            if(in_array('catering', $jenisPenjualan)) $jenisIds[] = 2;
            if(in_array('nasibox', $jenisPenjualan)) $jenisIds[] = 3;
            
            if (!empty($jenisIds)) {
                $query->whereIn('jenis_pesanan_id', $jenisIds);
            }
        }

        // Hanya hitung penjualan yang sudah Lunas (3) atau Selesai (5)
        $query->where(function($q) {
            $q->where('status_pembayaran_id', 3)
              ->orWhere('status_pesanan_id', 5);
        });

        $pesanans = $query->orderByDesc('tanggal_pesanan')->get();
        
        $stats = [
            'totalTransaksi' => $pesanans->count(),
            'totalPendapatan' => $pesanans->sum('total_tagihan'),
            'totalDineIn' => $pesanans->where('jenis_pesanan_id', 1)->count(),
            'totalCatering' => $pesanans->where('jenis_pesanan_id', 2)->count(),
            'totalNasiBox' => $pesanans->where('jenis_pesanan_id', 3)->count(),
        ];

        $pdf = Pdf::loadView('laporan.penjualan.pdf', compact('pesanans', 'stats', 'startDate', 'endDate'));
        return $pdf->stream('Laporan_Penjualan.pdf');
    }

    public function cetakPenjualanExcel(Request $request)
    {
        // Placeholder implementasi Export Excel
        return back()->with('info', 'Fitur Export Excel Penjualan belum diimplementasi sepenuhnya.');
    }

    // ==========================================
    // LAPORAN PERSEDIAAN
    // ==========================================
    public function persediaan(Request $request)
    {
        $periode = $request->input('periode', 'bulan_ini');
        $periode = is_array($periode) ? ($periode[0] ?? 'bulan_ini') : $periode;
        $startDate = null;
        $endDate = null;

        if ($periode == 'hari_ini') {
            $startDate = Carbon::today()->format('Y-m-d');
            $endDate = Carbon::today()->format('Y-m-d');
        } elseif ($periode == 'minggu_ini') {
            $startDate = Carbon::now()->startOfWeek()->format('Y-m-d');
            $endDate = Carbon::now()->endOfWeek()->format('Y-m-d');
        } elseif ($periode == 'bulan_ini') {
            $startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
            $endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        } elseif ($periode == 'custom') {
            $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        }

        $jenisPersediaan = $request->input('jenis_stok', []);
        $jenisPersediaan = is_array($jenisPersediaan) ? $jenisPersediaan : (array) $jenisPersediaan;
        
        $kategoriId = $request->input('kategori_id', []);
        $kategoriId = is_array($kategoriId) ? $kategoriId : (array) $kategoriId;
        
        $search = $request->input('search', '');

        $kategoris = KategoriBahanBaku::orderBy('nama_kategori')->get();

        $queryBahan = BahanBaku::with(['satuan', 'kategori_bahan_baku', 'stok_bahans'])->where('status_aktif', true);
        if (!empty($kategoriId)) {
            $queryBahan->whereIn('kategori_bahan_baku_id', $kategoriId);
        }
        if ($search) {
            $queryBahan->where('nama_bahan', 'like', "%{$search}%");
        }

        $laporanBahan = $queryBahan->orderBy('nama_bahan')->get()->map(function ($bahan) use ($startDate, $endDate, $jenisPersediaan) {
            // Karena tabel StokBahan dipisah (Harian / Catering), kita map tiap jenis stok
            $stokItems = collect();
            
            if (empty($jenisPersediaan) || in_array('harian', $jenisPersediaan)) {
                $stokHarian = $bahan->stok_bahans->firstWhere('jenis_persediaan', 'harian');
                if ($stokHarian) {
                    $stokAkhir = (float)$stokHarian->jumlah_stok;
                    $stokMin = (float)$bahan->stok_minimal;
                    $status = $stokAkhir <= 0 ? 'Habis' : ($stokAkhir <= $stokMin ? 'Menipis' : 'Aman');
                    
                    $stokItems->push([
                        'id' => $bahan->id . '_harian',
                        'bahan_baku_id' => $bahan->id,
                        'nama_bahan' => $bahan->nama_bahan,
                        'kategori' => optional($bahan->kategori_bahan_baku)->nama_kategori,
                        'satuan' => optional($bahan->satuan)->nama_satuan,
                        'jenis_stok' => 'Dine In & Nasi Box',
                        'stok_saat_ini' => $stokAkhir,
                        'status' => $status
                    ]);
                }
            }

            if (empty($jenisPersediaan) || in_array('catering', $jenisPersediaan)) {
                $stokCatering = $bahan->stok_bahans->firstWhere('jenis_persediaan', 'catering');
                if ($stokCatering) {
                    $stokAkhir = (float)$stokCatering->jumlah_stok;
                    $stokMin = (float)$bahan->stok_minimal;
                    $status = $stokAkhir <= 0 ? 'Habis' : ($stokAkhir <= $stokMin ? 'Menipis' : 'Aman');
                    
                    $stokItems->push([
                        'id' => $bahan->id . '_catering',
                        'bahan_baku_id' => $bahan->id,
                        'nama_bahan' => $bahan->nama_bahan,
                        'kategori' => optional($bahan->kategori_bahan_baku)->nama_kategori,
                        'satuan' => optional($bahan->satuan)->nama_satuan,
                        'jenis_stok' => 'Catering',
                        'stok_saat_ini' => $stokAkhir,
                        'status' => $status
                    ]);
                }
            }

            return $stokItems;
        })->flatten(1);

        $stats = [
            'total_bahan' => $laporanBahan->count(),
            'total_aman' => $laporanBahan->where('status', 'Aman')->count(),
            'total_menipis' => $laporanBahan->where('status', 'Menipis')->count(),
            'total_habis' => $laporanBahan->where('status', 'Habis')->count(),
        ];

        // Paginasi manual untuk collection
        $perPage = 15;
        $page = Paginator::resolveCurrentPage() ?: 1;
        $paginatedBahan = new LengthAwarePaginator(
            $laporanBahan->forPage($page, $perPage),
            $laporanBahan->count(), $perPage, $page,
            ['path' => Paginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        return view('admin.laporan.persediaan.index', compact(
            'paginatedBahan', 'stats', 'startDate', 'endDate',
            'jenisPersediaan', 'kategoriId', 'kategoris', 'periode'
        ));
    }

    public function detailPersediaan($id)
    {
        // Parse ID (contoh: "1_harian")
        $parts = explode('_', $id);
        $bahanBakuId = $parts[0];
        $jenisStok = $parts[1] ?? 'harian';

        $bahan = BahanBaku::with(['satuan', 'kategori_bahan_baku'])->findOrFail($bahanBakuId);
        $stok = $bahan->stok_bahans()->where('jenis_persediaan', $jenisStok)->first();
        
        // Ambil riwayat mutasi
        $mutasis = MutasiStok::with(['jenis_mutasi_stok', 'pengguna'])
            ->where('bahan_baku_id', $bahanBakuId)
            ->where('jenis_persediaan', $jenisStok)
            ->orderByDesc('tanggal_mutasi')
            ->limit(20)
            ->get();

        return view('admin.laporan.persediaan.detail', compact('bahan', 'stok', 'jenisStok', 'mutasis'));
    }

    public function cetakPersediaanPdf(Request $request)
    {
        $jenisPersediaan = $request->input('jenis_stok', []);
        $jenisPersediaan = is_array($jenisPersediaan) ? $jenisPersediaan : (array) $jenisPersediaan;
        
        $kategoriId = $request->input('kategori_id', []);
        $kategoriId = is_array($kategoriId) ? $kategoriId : (array) $kategoriId;
        
        $search = $request->input('search', '');

        $queryBahan = BahanBaku::with(['satuan', 'kategori_bahan_baku', 'stok_bahans'])->where('status_aktif', true);
        if (!empty($kategoriId)) {
            $queryBahan->whereIn('kategori_bahan_baku_id', $kategoriId);
        }
        if ($search) {
            $queryBahan->where('nama_bahan', 'like', "%{$search}%");
        }

        $laporanBahan = $queryBahan->orderBy('nama_bahan')->get()->map(function ($bahan) use ($jenisPersediaan) {
            $stokItems = collect();
            
            if (empty($jenisPersediaan) || in_array('harian', $jenisPersediaan)) {
                $stokHarian = $bahan->stok_bahans->firstWhere('jenis_persediaan', 'harian');
                if ($stokHarian) {
                    $stokAkhir = (float)$stokHarian->jumlah_stok;
                    $stokMin = (float)$bahan->stok_minimal;
                    $status = $stokAkhir <= 0 ? 'Habis' : ($stokAkhir <= $stokMin ? 'Menipis' : 'Aman');
                    
                    $stokItems->push([
                        'id' => $bahan->id . '_harian',
                        'bahan_baku_id' => $bahan->id,
                        'nama_bahan' => $bahan->nama_bahan,
                        'kategori' => optional($bahan->kategori_bahan_baku)->nama_kategori,
                        'satuan' => optional($bahan->satuan)->nama_satuan,
                        'jenis_stok' => 'Dine In & Nasi Box',
                        'stok_saat_ini' => $stokAkhir,
                        'status' => $status
                    ]);
                }
            }

            if (empty($jenisPersediaan) || in_array('catering', $jenisPersediaan)) {
                $stokCatering = $bahan->stok_bahans->firstWhere('jenis_persediaan', 'catering');
                if ($stokCatering) {
                    $stokAkhir = (float)$stokCatering->jumlah_stok;
                    $stokMin = (float)$bahan->stok_minimal;
                    $status = $stokAkhir <= 0 ? 'Habis' : ($stokAkhir <= $stokMin ? 'Menipis' : 'Aman');
                    
                    $stokItems->push([
                        'id' => $bahan->id . '_catering',
                        'bahan_baku_id' => $bahan->id,
                        'nama_bahan' => $bahan->nama_bahan,
                        'kategori' => optional($bahan->kategori_bahan_baku)->nama_kategori,
                        'satuan' => optional($bahan->satuan)->nama_satuan,
                        'jenis_stok' => 'Catering',
                        'stok_saat_ini' => $stokAkhir,
                        'status' => $status
                    ]);
                }
            }

            return $stokItems;
        })->flatten(1);

        $pdf = Pdf::loadView('laporan.persediaan.pdf', compact('laporanBahan'));
        return $pdf->stream('Laporan_Persediaan.pdf');
    }

    public function cetakPersediaanExcel(Request $request)
    {
        return back()->with('info', 'Fitur Export Excel Persediaan belum diimplementasi sepenuhnya.');
    }

    // ==========================================
    // LAPORAN PENGADAAN
    // ==========================================
    public function pengadaan(Request $request)
    {
        $periode = $request->input('periode', 'bulan_ini');
        $periode = is_array($periode) ? ($periode[0] ?? 'bulan_ini') : $periode;
        $startDate = null;
        $endDate = null;

        if ($periode == 'hari_ini') {
            $startDate = Carbon::today()->format('Y-m-d');
            $endDate = Carbon::today()->format('Y-m-d');
        } elseif ($periode == 'minggu_ini') {
            $startDate = Carbon::now()->startOfWeek()->format('Y-m-d');
            $endDate = Carbon::now()->endOfWeek()->format('Y-m-d');
        } elseif ($periode == 'bulan_ini') {
            $startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
            $endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        } elseif ($periode == 'custom') {
            $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        }

        $jenisPermintaan = $request->input('jenis_permintaan', []);
        $jenisPermintaan = is_array($jenisPermintaan) ? $jenisPermintaan : (array) $jenisPermintaan;
        
        $statusId = $request->input('status', []);
        $statusId = is_array($statusId) ? $statusId : (array) $statusId;
        
        $search = $request->input('search', '');

        $query = PengadaanBahan::with(['pemasok', 'status_pengadaan', 'diajukan_oleh_pengguna'])
            ->whereBetween('tanggal_pengadaan', [$startDate.' 00:00:00', $endDate.' 23:59:59']);

        if ($search) {
            $query->where('id_pengadaan', 'like', "%{$search}%");
        }
        if (!empty($jenisPermintaan)) {
            $query->whereIn('jenis_pengadaan', $jenisPermintaan);
        }
        if (!empty($statusId)) {
            $query->whereIn('status_pengadaan_id', $statusId);
        }

        $pengadaansAll = $query->orderByDesc('tanggal_pengadaan')->get();

        $totalPermintaan = $pengadaansAll->count();
        $totalHarian = $pengadaansAll->where('jenis_pengadaan', 'harian')->count();
        $totalCatering = $pengadaansAll->where('jenis_pengadaan', 'catering')->count();
        
        // Hitung total penerimaan dari pengadaan ini (yg statusnya Diterima Sebagian atau Selesai)
        $totalPenerimaan = $pengadaansAll->whereIn('status_pengadaan_id', [3, 4])->count();

        $perPage = 10;
        $page = Paginator::resolveCurrentPage() ?: 1;
        $pengadaans = new LengthAwarePaginator(
            $pengadaansAll->forPage($page, $perPage),
            $pengadaansAll->count(), $perPage, $page,
            ['path' => Paginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        $stats = compact('totalPermintaan', 'totalHarian', 'totalCatering', 'totalPenerimaan');

        return view('admin.laporan.pengadaan.index', compact(
            'pengadaans', 'stats', 'startDate', 'endDate', 'jenisPermintaan', 'statusId', 'periode'
        ));
    }

    public function detailPengadaan($id)
    {
        $pengadaan = PengadaanBahan::with([
            'pemasok', 
            'status_pengadaan', 
            'diajukan_oleh_pengguna',
            'detail_pengadaan_bahan.bahan_baku.satuan',
            'penerimaan_bahan.diterima_oleh_pengguna'
        ])->findOrFail($id);

        return view('admin.laporan.pengadaan.detail', compact('pengadaan'));
    }

    public function cetakPengadaanPdf(Request $request)
    {
        $periode = $request->input('periode', 'bulan_ini');
        $periode = is_array($periode) ? ($periode[0] ?? 'bulan_ini') : $periode;
        $startDate = null;
        $endDate = null;

        if ($periode == 'hari_ini') {
            $startDate = Carbon::today()->format('Y-m-d');
            $endDate = Carbon::today()->format('Y-m-d');
        } elseif ($periode == 'minggu_ini') {
            $startDate = Carbon::now()->startOfWeek()->format('Y-m-d');
            $endDate = Carbon::now()->endOfWeek()->format('Y-m-d');
        } elseif ($periode == 'bulan_ini') {
            $startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
            $endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        } elseif ($periode == 'custom') {
            $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        }

        $jenisPermintaan = $request->input('jenis_permintaan', []);
        $jenisPermintaan = is_array($jenisPermintaan) ? $jenisPermintaan : (array) $jenisPermintaan;
        
        $statusId = $request->input('status', []);
        $statusId = is_array($statusId) ? $statusId : (array) $statusId;
        
        $search = $request->input('search', '');

        $query = PengadaanBahan::with(['pemasok', 'status_pengadaan', 'diajukan_oleh_pengguna'])
            ->whereBetween('tanggal_pengadaan', [$startDate.' 00:00:00', $endDate.' 23:59:59']);

        if ($search) {
            $query->where('id_pengadaan', 'like', "%{$search}%");
        }
        if (!empty($jenisPermintaan)) {
            $query->whereIn('jenis_pengadaan', $jenisPermintaan);
        }
        if (!empty($statusId)) {
            $query->whereIn('status_pengadaan_id', $statusId);
        }

        $pengadaans = $query->orderByDesc('tanggal_pengadaan')->get();

        $pdf = Pdf::loadView('laporan.pengadaan.pdf', compact('pengadaans', 'startDate', 'endDate'));
        return $pdf->stream('Laporan_Pengadaan.pdf');
    }

    public function cetakPengadaanExcel(Request $request)
    {
        return back()->with('info', 'Fitur Export Excel Pengadaan belum diimplementasi sepenuhnya.');
    }
}
