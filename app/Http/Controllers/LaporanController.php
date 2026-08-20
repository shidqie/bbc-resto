<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\DetailPengadaanBahan;
use App\Models\KategoriBahanBaku;
use App\Models\MutasiStok;
use App\Models\PengadaanBahan;
use App\Models\PurchaseOrder;
use App\Models\Pesanan;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    // ==========================================
    // 1. LAPORAN PENJUALAN
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
            ->whereNotIn('status_pesanan_id', [6]) // Bukan Dibatalkan
            ->where('status_pembayaran_id', 5); // Hanya Lunas

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

        $pesanansAll = $query->orderByDesc('tanggal_pesanan')->get();
        
        // KPI 3 Kartu Sesuai Skripsi (promt.md)
        $totalTransaksi = $pesanansAll->count();
        $totalPendapatan = $pesanansAll->sum('total_tagihan');
        $rataRataTransaksi = $totalTransaksi > 0 ? round($totalPendapatan / $totalTransaksi) : 0;

        $perPage = 10;
        $page = Paginator::resolveCurrentPage() ?: 1;
        $pesanans = new LengthAwarePaginator(
            $pesanansAll->forPage($page, $perPage),
            $pesanansAll->count(), $perPage, $page,
            ['path' => Paginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        $stats = compact('totalPendapatan', 'totalTransaksi', 'rataRataTransaksi');

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
            ->whereNotIn('status_pesanan_id', [6])
            ->where('status_pembayaran_id', 5);

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

        $pesanans = $query->orderByDesc('tanggal_pesanan')->get();
        $totalTransaksi = $pesanans->count();
        $totalPendapatan = $pesanans->sum('total_tagihan');
        $rataRataTransaksi = $totalTransaksi > 0 ? round($totalPendapatan / $totalTransaksi) : 0;

        $stats = compact('totalPendapatan', 'totalTransaksi', 'rataRataTransaksi');

        $pdf = Pdf::loadView('pdf.laporan-penjualan', compact('pesanans', 'stats', 'startDate', 'endDate'));
        return $pdf->stream('Laporan_Penjualan.pdf');
    }

    public function cetakPenjualanExcel(Request $request)
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
            ->whereNotIn('status_pesanan_id', [6])
            ->where('status_pembayaran_id', 5);

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

        $pesanans = $query->orderByDesc('tanggal_pesanan')->get();

        $filename = "Laporan_Penjualan_" . date('Ymd_His') . ".csv";
        $headers = [
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$filename}",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function() use ($pesanans) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // UTF-8 BOM
            fputcsv($file, ['No', 'Tanggal', 'Kode Pesanan', 'Jenis Pesanan', 'Pelanggan', 'Total Transaksi', 'Status']);

            foreach ($pesanans as $idx => $p) {
                $jenisNama = optional($p->jenis_pesanan)->nama_jenis ?? 'Dine-In';
                $pelanggan = $p->jenis_pesanan_id == 1 ? ('Meja ' . (optional($p->meja)->nomor_meja ?? '-')) : (optional($p->pelanggan)->nama ?? 'Umum');
                fputcsv($file, [
                    $idx + 1,
                    Carbon::parse($p->tanggal_pesanan)->format('d/m/Y H:i'),
                    $p->id_pesanan,
                    $jenisNama,
                    $pelanggan,
                    (float)$p->total_tagihan,
                    'Selesai'
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ==========================================
    // 2. LAPORAN PERSEDIAAN
    // ==========================================
    public function persediaan(Request $request)
    {
        $kategoriId = $request->input('kategori_id', []);
        $kategoriId = is_array($kategoriId) ? $kategoriId : (array) $kategoriId;
        $kondisi = $request->input('kondisi', 'semua');
        $search = $request->input('search', '');

        $kategoris = KategoriBahanBaku::orderBy('nama_kategori')->get();

        $queryBahan = BahanBaku::with(['satuan', 'kategori_bahan_baku', 'stok_harian', 'stok_catering'])->where('status_aktif', true);
        if (!empty($kategoriId)) {
            $queryBahan->whereIn('kategori_bahan_baku_id', $kategoriId);
        }
        if ($search) {
            $queryBahan->where(function($q) use ($search) {
                $q->where('id_bahan_baku', 'like', "%{$search}%")
                  ->orWhere('nama_bahan', 'like', "%{$search}%");
            });
        }

        $laporanBahan = $queryBahan->orderBy('nama_bahan')->get()->map(function ($bahan) {
            $stokHarian = optional($bahan->stok_harian)->jumlah_stok ?? 0;
            $stokMin = (float)($bahan->stok_minimal ?? optional($bahan->stok_harian)->stok_minimal ?? 5);
            $stokSaatIni = (float) $stokHarian;

            // Logika promt.md:
            // jika stok = 0 -> Habis
            // jika stok > 0 dan stok <= stok_minimum -> Menipis
            // jika stok > stok_minimum -> Aman
            if ($stokSaatIni <= 0) {
                $status = 'Habis';
            } elseif ($stokSaatIni <= $stokMin) {
                $status = 'Menipis';
            } else {
                $status = 'Aman';
            }

            return [
                'id' => $bahan->id,
                'id_bahan_baku' => $bahan->id_bahan_baku,
                'nama_bahan' => $bahan->nama_bahan,
                'kategori' => optional($bahan->kategori_bahan_baku)->nama_kategori ?? '-',
                'satuan' => optional($bahan->satuan)->singkatan ?? optional($bahan->satuan)->nama_satuan ?? 'pcs',
                'stok_saat_ini' => $stokSaatIni,
                'stok_minimum' => $stokMin,
                'status' => $status
            ];
        });

        // Filter berdasarkan Kondisi (Aman, Menipis, Habis)
        if ($kondisi && $kondisi !== 'semua') {
            $laporanBahan = $laporanBahan->filter(function($b) use ($kondisi) {
                return strtolower($b['status']) === strtolower($kondisi);
            })->values();
        }

        // KPI 3 Kartu Sesuai Skripsi (promt.md): Total Bahan Baku, Stok Menipis, Stok Habis
        $stats = [
            'total_bahan' => $laporanBahan->count(),
            'total_menipis' => $laporanBahan->where('status', 'Menipis')->count(),
            'total_habis' => $laporanBahan->where('status', 'Habis')->count(),
        ];

        $perPage = 15;
        $page = Paginator::resolveCurrentPage() ?: 1;
        $paginatedBahan = new LengthAwarePaginator(
            $laporanBahan->forPage($page, $perPage),
            $laporanBahan->count(), $perPage, $page,
            ['path' => Paginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        return view('admin.laporan.persediaan.index', compact(
            'paginatedBahan', 'stats', 'kategoriId', 'kategoris', 'kondisi'
        ));
    }

    public function detailPersediaan($id)
    {
        $bahan = BahanBaku::with(['satuan', 'kategori_bahan_baku', 'stok_harian'])->findOrFail($id);
        $stok = $bahan->stok_harian;
        $stokHarian = (float)(optional($stok)->jumlah_stok ?? 0);
        $jenisStok = 'harian';
        
        // Ambil riwayat kartu stok dari mutasi_stok
        $mutasis = MutasiStok::with(['jenis_mutasi_stok', 'pengguna'])
            ->where('bahan_baku_id', $id)
            ->orderByDesc('tanggal_mutasi')
            ->limit(20)
            ->get();

        return view('admin.laporan.persediaan.detail', compact('bahan', 'stok', 'stokHarian', 'jenisStok', 'mutasis'));
    }

    public function cetakPersediaanPdf(Request $request)
    {
        $kategoriId = $request->input('kategori_id', []);
        $kategoriId = is_array($kategoriId) ? $kategoriId : (array) $kategoriId;
        $kondisi = $request->input('kondisi', 'semua');
        $search = $request->input('search', '');

        $queryBahan = BahanBaku::with(['satuan', 'kategori_bahan_baku', 'stok_harian'])->where('status_aktif', true);
        if (!empty($kategoriId)) {
            $queryBahan->whereIn('kategori_bahan_baku_id', $kategoriId);
        }
        if ($search) {
            $queryBahan->where(function($q) use ($search) {
                $q->where('id_bahan_baku', 'like', "%{$search}%")
                  ->orWhere('nama_bahan', 'like', "%{$search}%");
            });
        }

        $laporanBahan = $queryBahan->orderBy('nama_bahan')->get()->map(function ($bahan) {
            $stokHarian = optional($bahan->stok_harian)->jumlah_stok ?? 0;
            $stokMin = (float)($bahan->stok_minimal ?? 5);
            $stokSaatIni = (float) $stokHarian;

            if ($stokSaatIni <= 0) {
                $status = 'Habis';
            } elseif ($stokSaatIni <= $stokMin) {
                $status = 'Menipis';
            } else {
                $status = 'Aman';
            }

            return [
                'id_bahan_baku' => $bahan->id_bahan_baku,
                'nama_bahan' => $bahan->nama_bahan,
                'satuan' => optional($bahan->satuan)->singkatan ?? 'pcs',
                'stok_saat_ini' => $stokSaatIni,
                'stok_minimum' => $stokMin,
                'status' => $status
            ];
        });

        if ($kondisi && $kondisi !== 'semua') {
            $laporanBahan = $laporanBahan->filter(function($b) use ($kondisi) {
                return strtolower($b['status']) === strtolower($kondisi);
            })->values();
        }

        $pdf = Pdf::loadView('pdf.laporan-persediaan', compact('laporanBahan'));
        return $pdf->stream('Laporan_Persediaan.pdf');
    }

    public function cetakPersediaanExcel(Request $request)
    {
        $kategoriId = $request->input('kategori_id', []);
        $kategoriId = is_array($kategoriId) ? $kategoriId : (array) $kategoriId;
        $kondisi = $request->input('kondisi', 'semua');
        $search = $request->input('search', '');

        $queryBahan = BahanBaku::with(['satuan', 'kategori_bahan_baku', 'stok_harian'])->where('status_aktif', true);
        if (!empty($kategoriId)) {
            $queryBahan->whereIn('kategori_bahan_baku_id', $kategoriId);
        }
        if ($search) {
            $queryBahan->where(function($q) use ($search) {
                $q->where('id_bahan_baku', 'like', "%{$search}%")
                  ->orWhere('nama_bahan', 'like', "%{$search}%");
            });
        }

        $laporanBahan = $queryBahan->orderBy('nama_bahan')->get()->map(function ($bahan) {
            $stokHarian = optional($bahan->stok_harian)->jumlah_stok ?? 0;
            $stokMin = (float)($bahan->stok_minimal ?? 5);
            $stokSaatIni = (float) $stokHarian;

            if ($stokSaatIni <= 0) {
                $status = 'Habis';
            } elseif ($stokSaatIni <= $stokMin) {
                $status = 'Menipis';
            } else {
                $status = 'Aman';
            }

            return [
                'id_bahan_baku' => $bahan->id_bahan_baku,
                'nama_bahan' => $bahan->nama_bahan,
                'satuan' => optional($bahan->satuan)->singkatan ?? 'pcs',
                'stok_saat_ini' => $stokSaatIni,
                'stok_minimum' => $stokMin,
                'status' => $status
            ];
        });

        if ($kondisi && $kondisi !== 'semua') {
            $laporanBahan = $laporanBahan->filter(function($b) use ($kondisi) {
                return strtolower($b['status']) === strtolower($kondisi);
            })->values();
        }

        $filename = "Laporan_Persediaan_" . date('Ymd_His') . ".csv";
        $headers = [
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$filename}",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function() use ($laporanBahan) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");
            fputcsv($file, ['No', 'Kode Bahan', 'Nama Bahan Baku', 'Satuan', 'Stok Saat Ini', 'Stok Minimum', 'Kondisi']);

            foreach ($laporanBahan as $idx => $b) {
                fputcsv($file, [
                    $idx + 1,
                    $b['id_bahan_baku'],
                    $b['nama_bahan'],
                    $b['satuan'],
                    $b['stok_saat_ini'],
                    $b['stok_minimum'],
                    $b['status']
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ==========================================
    // 3. LAPORAN PENGADAAN
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

        $statusStr = $request->input('status', 'semua');
        $search = $request->input('search', '');

        $query = PurchaseOrder::with(['pengadaan_bahan', 'detail_purchase_order.bahan_baku', 'dibuat_oleh_pengguna'])
            ->whereBetween('tanggal_po', [$startDate.' 00:00:00', $endDate.' 23:59:59']);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nomor_po', 'like', "%{$search}%")
                  ->orWhere('supplier', 'like', "%{$search}%");
            });
        }

        if ($statusStr && $statusStr !== 'semua') {
            $query->where('status', $statusStr);
        }

        $posAll = $query->orderByDesc('tanggal_po')->get();

        // KPI 3 Kartu Sesuai Skripsi (promt.md): Total Pengadaan, Total Pembelian, Pengadaan Selesai
        $totalPengadaan = $posAll->count();
        $totalPembelian = 0;
        foreach ($posAll as $po) {
            foreach ($po->detail_purchase_order as $d) {
                $qty = (float) $d->jumlah_dipesan;
                $harga = (float) $d->harga_satuan;
                if ($harga <= 0) $harga = (float) optional($d->bahan_baku)->harga_satuan;
                if ($harga <= 0) $harga = 15000;
                $totalPembelian += ($qty * $harga);
            }
        }
        $pengadaanSelesai = $posAll->where('status', PurchaseOrder::SELESAI)->count();

        $stats = compact('totalPengadaan', 'totalPembelian', 'pengadaanSelesai');

        $perPage = 10;
        $page = Paginator::resolveCurrentPage() ?: 1;
        $pengadaans = new LengthAwarePaginator(
            $posAll->forPage($page, $perPage),
            $posAll->count(), $perPage, $page,
            ['path' => Paginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        return view('admin.laporan.pengadaan.index', compact(
            'pengadaans', 'stats', 'startDate', 'endDate', 'statusStr', 'periode'
        ));
    }

    public function detailPengadaan($id)
    {
        $po = PurchaseOrder::with([
            'pengadaan_bahan',
            'detail_purchase_order.bahan_baku.satuan',
            'dibuat_oleh_pengguna'
        ])->findOrFail($id);

        return view('admin.laporan.pengadaan.detail', compact('po'));
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

        $statusStr = $request->input('status', 'semua');
        $search = $request->input('search', '');

        $query = PurchaseOrder::with(['pengadaan_bahan', 'detail_purchase_order.bahan_baku'])
            ->whereBetween('tanggal_po', [$startDate.' 00:00:00', $endDate.' 23:59:59']);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nomor_po', 'like', "%{$search}%")
                  ->orWhere('supplier', 'like', "%{$search}%");
            });
        }

        if ($statusStr && $statusStr !== 'semua') {
            $query->where('status', $statusStr);
        }

        $pos = $query->orderByDesc('tanggal_po')->get();

        $pdf = Pdf::loadView('pdf.laporan-pengadaan', compact('pos', 'startDate', 'endDate'));
        return $pdf->stream('Laporan_Pengadaan.pdf');
    }

    public function cetakPengadaanExcel(Request $request)
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

        $statusStr = $request->input('status', 'semua');
        $search = $request->input('search', '');

        $query = PurchaseOrder::with(['pengadaan_bahan', 'detail_purchase_order.bahan_baku'])
            ->whereBetween('tanggal_po', [$startDate.' 00:00:00', $endDate.' 23:59:59']);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nomor_po', 'like', "%{$search}%")
                  ->orWhere('supplier', 'like', "%{$search}%");
            });
        }

        if ($statusStr && $statusStr !== 'semua') {
            $query->where('status', $statusStr);
        }

        $pos = $query->orderByDesc('tanggal_po')->get();

        $filename = "Laporan_Pengadaan_" . date('Ymd_His') . ".csv";
        $headers = [
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$filename}",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function() use ($pos) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");
            fputcsv($file, ['No', 'Kode Pengadaan', 'Tanggal', 'Supplier', 'Jumlah Pembelian', 'Penerimaan', 'Status']);

            foreach ($pos as $idx => $p) {
                $totalBeli = 0;
                $penerimaanStatus = 'Belum Diterima';
                if ($p->status === 'selesai') {
                    $penerimaanStatus = 'Diterima Semua';
                } elseif ($p->status === 'diterima_sebagian') {
                    $penerimaanStatus = 'Diterima Sebagian';
                }

                foreach ($p->detail_purchase_order as $d) {
                    $qty = (float) $d->jumlah_dipesan;
                    $harga = (float) $d->harga_satuan;
                    if ($harga <= 0) $harga = (float) optional($d->bahan_baku)->harga_satuan;
                    if ($harga <= 0) $harga = 15000;
                    $totalBeli += ($qty * $harga);
                }

                fputcsv($file, [
                    $idx + 1,
                    $p->nomor_po,
                    Carbon::parse($p->tanggal_po)->format('d/m/Y'),
                    $p->supplier ?? '-',
                    $totalBeli,
                    $penerimaanStatus,
                    ucfirst($p->status)
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
