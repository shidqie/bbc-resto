<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\Pemasok;
use App\Models\PengadaanBahan;
use App\Models\PurchaseOrder;
use App\Models\DetailPurchaseOrder;
use App\Models\Pesanan;
use App\Models\StokBahan;
use App\Models\JenisPesanan;
use App\Models\MutasiStok;
use App\Services\KebutuhanBahanService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['pengadaan_bahan', 'detail_purchase_order.bahan_baku', 'dibuat_oleh_pengguna'])
            ->orderBy('tanggal_po', 'desc')
            ->orderByDesc('dibuat_pada');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_po', 'like', "%{$search}%")
                    ->orWhere('supplier', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $pos = $query->paginate(10)->withQueryString();

        $statuses = [
            PurchaseOrder::MENUNGGU_BARANG => 'Dipesan',
            PurchaseOrder::DITERIMA_SEBAGIAN => 'Diterima Sebagian',
            PurchaseOrder::SELESAI => 'Diterima',
            PurchaseOrder::DIBATALKAN => 'Dibatalkan',
        ];

        return view('admin.pengadaan.purchase-order.index', compact('pos', 'statuses'));
    }

    public function create(Request $request)
    {
        $userRole = auth()->user()->peran->nama_peran ?? '';
        if (in_array($userRole, ['Dapur', 'Tim Dapur']) && !in_array($userRole, ['Admin', 'Super Admin', 'Manajer', 'Pemilik'])) {
            abort(403, 'Akses ditolak: Dapur hanya dapat menerima bahan baku.');
        }

        $tipe = $request->input('tipe', 'Harian');
        $kodePo = $this->kodePo();
        $items = collect();
        $pesanan = null;
        $itemsCukup = collect();
        $resepBelumLengkap = false;
        $missingMenus = [];

        $suppliers = \App\Models\Pemasok::where('status_aktif', true)->orderBy('nama_pemasok')->get();
        $isCateringType = ($tipe === 'Catering' || $tipe === 'Katering');

        if ($isCateringType) {
            // KHUSUS KATERING (jenis_pesanan_id = 2)
            $pesananList = Pesanan::with(['pelanggan', 'detail_pesanan.menu'])
                ->where('jenis_pesanan_id', 2)
                ->where('status_pesanan_id', '>=', 2)
                ->where('status_pesanan_id', '!=', 6)
                ->orderBy('dibuat_pada', 'desc')
                ->get();

            $kodePesanan = $request->filled('kode_pesanan') 
                ? trim($request->kode_pesanan) 
                : optional($pesananList->first())->id_pesanan;

            if ($kodePesanan) {
                $pesanan = Pesanan::with(['pelanggan', 'detail_pesanan.menu', 'detail_pesanan.pilihan_pesanan_catering.pilihan_komponen_paket.menu'])
                    ->where('id_pesanan', $kodePesanan)
                    ->where('jenis_pesanan_id', 2)
                    ->first();

                if ($pesanan && $pesanan->status_pesanan_id != 6) {
                    $kebutuhanService = app(KebutuhanBahanService::class);
                    $hasilPengadaan = $kebutuhanService->hitungPengadaanPesanan($pesanan, 'catering');

                    $resepBelumLengkap = ! $hasilPengadaan['resep_lengkap'];
                    $missingMenus = $hasilPengadaan['missing_menus'];
                    $items = $hasilPengadaan['items_kurang'];
                    $itemsCukup = $hasilPengadaan['items_cukup'];
                }
            }
        } else {
            // KHUSUS NASI BOX & HARIAN (jenis_pesanan_id = 3 & Stok Harian)
            $pesananList = Pesanan::with(['pelanggan', 'detail_pesanan.menu'])
                ->where('jenis_pesanan_id', 3)
                ->where('status_pesanan_id', '>=', 2)
                ->where('status_pesanan_id', '!=', 6)
                ->orderBy('dibuat_pada', 'desc')
                ->get();

            $kodePesanan = $request->filled('kode_pesanan') ? trim($request->kode_pesanan) : null;

            if ($kodePesanan) {
                // Ada pesanan Nasi Box yang dipilih
                $pesanan = Pesanan::with(['pelanggan', 'detail_pesanan.menu', 'detail_pesanan.pilihan_pesanan_catering.pilihan_komponen_paket.menu'])
                    ->where('id_pesanan', $kodePesanan)
                    ->where('jenis_pesanan_id', 3)
                    ->first();

                if ($pesanan && $pesanan->status_pesanan_id != 6) {
                    $kebutuhanService = app(KebutuhanBahanService::class);
                    $hasilPengadaan = $kebutuhanService->hitungPengadaanPesanan($pesanan, 'harian');

                    $resepBelumLengkap = ! $hasilPengadaan['resep_lengkap'];
                    $missingMenus = $hasilPengadaan['missing_menus'];
                    $items = $hasilPengadaan['items_kurang'];
                    $itemsCukup = $hasilPengadaan['items_cukup'];
                }
            } else {
                // Tanpa pesanan -> Restock operasional harian biasa (filter stok kosong/minim)
                $rawItems = BahanBaku::with('satuan')->leftJoin('stok_bahan', function($join) {
                    $join->on('bahan_baku.id', '=', 'stok_bahan.bahan_baku_id')
                         ->where('stok_bahan.jenis_persediaan', StokBahan::JENIS_HARIAN);
                })
                ->select('bahan_baku.*', 'stok_bahan.jumlah_stok as stok_saat_ini', 'stok_bahan.stok_minimal')
                ->orderBy('bahan_baku.nama_bahan')
                ->get();

                $items = $rawItems->filter(function($item) {
                    $stokSaatIni = (float) ($item->stok_saat_ini ?? 0);
                    return $stokSaatIni <= 0;
                })->values();

                $items->transform(function($item) {
                    $stokMinimal = (float) ($item->stok_minimal ?? 0);
                    if ($stokMinimal <= 0) {
                        $stokMinimal = (float) ($item->stok_minimal_harian ?? 5);
                    }
                    if ($stokMinimal <= 0) {
                        $stokMinimal = 5;
                    }

                    $suggestedBase = $stokMinimal * 2;
                    $item->satuan_beli = \App\Helpers\UnitHelper::getPurchasingUnit($item->satuan);
                    $item->satuan_beli_id = \App\Helpers\UnitHelper::getPurchasingSatuanId($item->satuan);
                    $item->satuan_dasar = \App\Helpers\UnitHelper::getBaseUnit($item->satuan);
                    $item->jumlah_beli = \App\Helpers\UnitHelper::toPurchasingQuantity($suggestedBase, $item->satuan);
                    $item->kebutuhan = $item->jumlah_beli;
                    $item->kebutuhan_bersih = $item->jumlah_beli;
                    $item->harga_satuan = \App\Helpers\UnitHelper::toPurchasingPrice($item->harga_satuan ?? 0, $item->satuan);

                    return $item;
                });
            }
        }

        $allBahanBaku = BahanBaku::with(['satuan', 'stok_catering_balance', 'stok_harian'])->orderBy('nama_bahan')->get();

        return view('admin.pengadaan.purchase-order.create', compact(
            'items', 'itemsCukup', 'kodePo', 'tipe', 'pesanan', 'suppliers', 'allBahanBaku',
            'pesananList', 'resepBelumLengkap', 'missingMenus'
        ));
    }

    public function storeUnified(Request $request)
    {
        $request->validate([
            'supplier_nama' => 'required|string|max:150',
            'supplier_telepon' => 'nullable|string|max:50',
            'supplier_alamat' => 'nullable|string|max:500',
            'jumlah_beli' => 'required|array',
            'tanggal_po' => 'nullable|date',
        ], [
            'supplier_nama.required' => 'Nama supplier wajib diisi.',
            'jumlah_beli.required' => 'Daftar bahan baku wajib diisi.',
        ]);

        $itemIds = [];
        if ($request->has('jumlah_beli') && is_array($request->jumlah_beli)) {
            $itemIds = array_keys(array_filter($request->jumlah_beli, fn ($val) => (float)$val > 0));
        }

        if (count($itemIds) == 0) {
            return back()->withInput()->with('error', 'Masukkan minimal satu bahan baku dengan jumlah pesan > 0 untuk dibuatkan PO.');
        }

        $checkeds = $itemIds;

        try {
            $po = DB::transaction(function () use ($request, $checkeds) {
                // Auto match or create Pemasok
                $pemasok = Pemasok::firstOrCreate(
                    ['nama_pemasok' => trim($request->supplier_nama)],
                    [
                        'nomor_telepon' => $request->supplier_telepon ?? '-',
                        'alamat' => $request->supplier_alamat ?? '-',
                        'kode_pemasok' => 'SUP-' . time() . rand(100, 999)
                    ]
                );

            $tipePO = $request->input('tipe', 'Operasional');
            $pesananId = $request->pesanan_id ?? null;
            $kodePesananCatering = null;
            $pesananObj = null;
            if ($pesananId) {
                $pesananObj = Pesanan::find($pesananId);
                $kodePesananCatering = $pesananObj ? $pesananObj->id_pesanan : null;
            }

            $isNasiBox = $pesananObj && (int) $pesananObj->jenis_pesanan_id === 3;
            $isDineIn = $pesananObj && (int) $pesananObj->jenis_pesanan_id === 1;
            $isCatering = (($tipePO === 'Catering' || $tipePO === 'Katering') && !$isNasiBox && !$isDineIn);

            // Create Pengadaan (Background)
            $pengadaan = PengadaanBahan::create([
                'id_pengadaan' => 'REQ-' . time(),
                'pemasok_id' => $pemasok->id,
                'pesanan_id' => $pesananId,
                'diajukan_oleh' => auth()->id() ?? 1,
                'status_pengadaan_id' => 3, // Disetujui/Diproses
                'jenis_pengadaan' => $isCatering ? 'Catering' : 'Harian',
                'tanggal_pengadaan' => $request->tanggal_po ?? now()->toDateString(),
                'total_pengadaan' => 0,
            ]);

            // Ensure unique PO code
            $nomorPo = $request->nomor_po;
            if (!$nomorPo || PurchaseOrder::where('nomor_po', $nomorPo)->exists()) {
                $nomorPo = $this->kodePo();
            }

            // Create PO
            $po = PurchaseOrder::create([
                'nomor_po' => $nomorPo,
                'pengadaan_bahan_id' => $pengadaan->id,
                'supplier' => $request->supplier_nama,
                'no_telp_supplier' => $request->supplier_telepon,
                'alamat_supplier' => $request->supplier_alamat,
                'jenis_po' => $isCatering ? 'catering' : 'operasional',
                'kode_pesanan_catering' => $kodePesananCatering,
                'tanggal_po' => $request->tanggal_po ?? now()->toDateString(),
                'status' => PurchaseOrder::MENUNGGU_BARANG,
                'catatan' => $request->catatan,
                'dibuat_oleh' => auth()->id() ?? 1,
            ]);

            $totalPengadaan = 0;
            foreach ($checkeds as $bahanId) {
                $jumlah = (float) ($request->jumlah_beli[$bahanId] ?? 0);
                if ($jumlah > 0) {
                    $bahan = \App\Models\BahanBaku::find($bahanId);
                    
                    $rawTotal = str_replace(['.', ','], ['', '.'], $request->total_pembelian[$bahanId] ?? 0);
                    $totalPembelian = (float) $rawTotal;
                    $hargaSatuan = (float) ($request->harga_satuan[$bahanId] ?? 0);
                    
                    if ($totalPembelian > 0) {
                        $subtotal = $totalPembelian;
                        $hargaSatuan = $totalPembelian / $jumlah;
                    } else {
                        $subtotal = $jumlah * $hargaSatuan;
                    }
                    
                    $totalPengadaan += $subtotal;

                    // Update harga_satuan on BahanBaku if user provided one
                    if ($hargaSatuan > 0 && $bahan) {
                        $bahan->harga_satuan = $hargaSatuan;
                        $bahan->save();
                    }

                    $satuanBeliId = $bahan ? \App\Helpers\UnitHelper::getPurchasingSatuanId($bahan->satuan) : 1;
                    
                    $detailPengadaan = \App\Models\DetailPengadaanBahan::create([
                        'pengadaan_bahan_id' => $pengadaan->id,
                        'bahan_baku_id' => $bahanId,
                        'jumlah_dipesan' => $jumlah,
                        'satuan_id' => $satuanBeliId,
                        'harga_satuan' => $hargaSatuan,
                        'subtotal' => $subtotal,
                    ]);
                    
                    DetailPurchaseOrder::create([
                        'purchase_order_id' => $po->id,
                        'detail_pengadaan_bahan_id' => $detailPengadaan->id,
                        'bahan_baku_id' => $bahanId,
                        'jumlah_dipesan' => $jumlah,
                        'satuan_id' => $satuanBeliId,
                    ]);
                }
            }
            // Update total pengadaan
            $pengadaan->total_pengadaan = $totalPengadaan;
            $pengadaan->save();
            
            return $po;
        });

        return redirect()->route('pengadaan.po.index')
            ->with('success', 'Purchase Order ' . $po->nomor_po . ' berhasil dibuat.')
            ->with('po_berhasil', true)
            ->with('po_nomor', $po->nomor_po);
    } catch (\Throwable $e) {
        \Illuminate\Support\Facades\Log::error('Error storing PO: ' . $e->getMessage());
        return back()->withInput()->with('error', 'Gagal membuat Purchase Order: ' . $e->getMessage());
    }
}

    public function show(PurchaseOrder $po)
    {
        $po->load(['pengadaan_bahan']);
        
        $items = $po->detail_purchase_order()
            ->with(['bahan_baku.satuan'])
            ->paginate(15);
        
        // Cek penerimaan yg sudah ada per bahan
        $penerimaanList = \App\Models\DetailPenerimaanBahan::whereHas('penerimaan_bahan', function($q) use ($po) {
            $q->where('purchase_order_id', $po->id);
        })->get()->groupBy('bahan_baku_id');

        $items->getCollection()->transform(function($detail) use ($penerimaanList) {
            $diterima = 0;
            if (isset($penerimaanList[$detail->bahan_baku_id])) {
                $diterima = $penerimaanList[$detail->bahan_baku_id]->where('kondisi', 'Baik')->sum('jumlah_diterima');
            }
            $detail->sudah_diterima = $diterima;
            $detail->sisa = max(0, $detail->jumlah_dipesan - $diterima);
            return $detail;
        });

        return view('admin.pengadaan.purchase-order.show', compact('po', 'items'));
    }

    public function terimaBarang(Request $request, PurchaseOrder $po)
    {
        $request->validate([
            'terima' => 'required|array',
            'kondisi' => 'required|array',
        ]);

        DB::transaction(function () use ($request, $po) {
            $penerimaan = \App\Models\PenerimaanBahan::create([
                'nomor_penerimaan' => 'RCV-' . time(),
                'purchase_order_id' => $po->id,
                'diterima_pada' => now(),
                'diterima_oleh' => auth()->id() ?? 1,
            ]);

            $pengadaan = $po->pengadaan_bahan;
            $pesanan = null;
            if ($po->kode_pesanan_catering) {
                $pesanan = Pesanan::where('id_pesanan', $po->kode_pesanan_catering)->first() ?: Pesanan::find($po->kode_pesanan_catering);
            }
            if (!$pesanan && $pengadaan && $pengadaan->pesanan_id) {
                $pesanan = $pengadaan->pesanan;
            }

            $isNasiBox = $pesanan && (int) $pesanan->jenis_pesanan_id === 3;
            $isDineIn = $pesanan && (int) $pesanan->jenis_pesanan_id === 1;

            $isCatering = (strtolower($po->jenis_po ?? '') === 'catering') && !$isNasiBox && !$isDineIn;
            $jenisPersediaan = $isCatering ? StokBahan::JENIS_CATERING : StokBahan::JENIS_HARIAN;

            $adaDiterima = false;
            $semuaLengkap = true;

            $penerimaanList = \App\Models\DetailPenerimaanBahan::whereHas('penerimaan_bahan', function($q) use ($po) {
                $q->where('purchase_order_id', $po->id);
            })->get()->groupBy('bahan_baku_id');

            foreach ($po->detail_purchase_order as $detail) {
                $qtyTerima = (float) ($request->terima[$detail->bahan_baku_id] ?? 0);
                $kondisi = $request->kondisi[$detail->bahan_baku_id] ?? 'Baik';
                
                $sudah = 0;
                if (isset($penerimaanList[$detail->bahan_baku_id])) {
                    $sudah = (float) $penerimaanList[$detail->bahan_baku_id]->where('kondisi', 'Baik')->sum('jumlah_diterima');
                }

                if ($qtyTerima > 0) {
                    $sisa = max(0, (float)$detail->jumlah_dipesan - $sudah);
                    if ($kondisi === 'Baik' && $qtyTerima > $sisa) {
                        $qtyTerima = $sisa;
                    }
                    
                    if ($qtyTerima <= 0 && $kondisi === 'Baik') {
                        continue;
                    }

                    $adaDiterima = true;
                    $detailPenerimaan = \App\Models\DetailPenerimaanBahan::create([
                        'penerimaan_bahan_id' => $penerimaan->id,
                        'detail_purchase_order_id' => $detail->id,
                        'bahan_baku_id' => $detail->bahan_baku_id,
                        'jumlah_diterima' => $qtyTerima,
                        'harga_satuan' => (float) ($detail->detail_pengadaan_bahan?->harga_satuan ?? $detail->bahan_baku?->harga_satuan ?? 0),
                        'kondisi' => $kondisi,
                    ]);

                    // Update jumlah_diterima pada DetailPurchaseOrder
                    $detail->jumlah_diterima = (float) $detail->jumlah_diterima + $qtyTerima;
                    $detail->save();

                    // Update jumlah_diterima pada DetailPengadaanBahan jika ada
                    if ($detail->detail_pengadaan_bahan) {
                        $detail->detail_pengadaan_bahan->jumlah_diterima = (float) $detail->detail_pengadaan_bahan->jumlah_diterima + $qtyTerima;
                        $detail->detail_pengadaan_bahan->save();
                    }

                    if ($kondisi === 'Baik') {
                        // Gunakan StockService agar atomic dan mencatat kartu stok dengan benar
                        $stockService = app(\App\Services\StockService::class);
                        $stockService->addStock(
                            $detail->bahan_baku_id,
                            $qtyTerima,
                            'Penerimaan PO: ' . $po->nomor_po,
                            1, // 1 = Masuk
                            auth()->id() ?? 1,
                            ['detail_penerimaan_bahan_id' => $detailPenerimaan->id],
                            $jenisPersediaan
                        );
                    }
                }
                
                if ((float)$detail->jumlah_diterima < (float)$detail->jumlah_dipesan) {
                    $semuaLengkap = false;
                }
            }

            if ($adaDiterima) {
                $po->status = $semuaLengkap ? PurchaseOrder::SELESAI : PurchaseOrder::DITERIMA_SEBAGIAN;
                $po->save();
            }
        });

        return back()->with('success', 'Penerimaan barang berhasil diproses.');
    }

    public function print(PurchaseOrder $po)
    {
        $po->load(['pengadaan_bahan', 'detail_purchase_order.bahan_baku.satuan']);
        $pdf = Pdf::loadView('pdf.po', compact('po'));
        return $pdf->stream('Purchase-Order-' . $po->nomor_po . '.pdf');
    }

    public function cancel(PurchaseOrder $po)
    {
        $po->status = PurchaseOrder::DIBATALKAN;
        $po->save();
        return back()->with('success', 'Purchase Order dibatalkan.');
    }

    public function destroy(PurchaseOrder $po)
    {
        DB::transaction(function () use ($po) {
            $po->detail_purchase_order()->delete();
            if ($po->pengadaan_bahan) {
                $po->pengadaan_bahan->detail_pengadaan_bahan()->delete();
                $po->pengadaan_bahan->delete();
            }
            $po->delete();
        });

        return redirect()->route('pengadaan.po.index')->with('success', 'Purchase Order berhasil dihapus.');
    }

    protected function kodePo(): string
    {
        $date = now()->format('Ymd');
        $count = PurchaseOrder::whereDate('dibuat_pada', now()->toDateString())->count() + 1;
        return 'PO-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}