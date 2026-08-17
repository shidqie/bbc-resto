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
        $tipe = $request->input('tipe', 'Operasional');
        $kodePo = $this->kodePo();
        $items = collect();
        $pesanan = null;

        if ($tipe === 'Operasional') {
            // Cari bahan baku yang stok hariannya < stok minimal
            $items = BahanBaku::with('satuan')->join('stok_bahan', function($join) {
                $join->on('bahan_baku.id', '=', 'stok_bahan.bahan_baku_id')
                     ->where('stok_bahan.jenis_persediaan', StokBahan::JENIS_HARIAN);
            })
            ->select('bahan_baku.*', 'stok_bahan.jumlah_stok', 'stok_bahan.stok_minimal')
            ->whereRaw('stok_bahan.jumlah_stok < stok_bahan.stok_minimal')
            ->get()
            ->map(function($item) {
                $item->kebutuhan = max(0, $item->stok_minimal - $item->jumlah_stok);
                $item->sudah_dipesan = 0;
                $item->kebutuhan_bersih = $item->kebutuhan;
                return $item;
            });
        } elseif ($tipe === 'Catering') {
            if ($request->filled('kode_pesanan')) {
                $kodePesanan = trim($request->kode_pesanan);
                $pesanan = Pesanan::with(['detail_pesanan.menu', 'detail_pesanan.pilihan_pesanan_catering'])
                    ->where('id_pesanan', $request->kode_pesanan)
                    ->where('jenis_pesanan_id', 2)
                    ->first();

                if (!$pesanan) {
                    return back()->with('error', 'Pesanan Catering tidak ditemukan atau bukan berjenis Catering.');
                }
                
                if ($pesanan->status_pesanan_id == 6) {
                    return back()->with('error', 'Pesanan Catering telah dibatalkan dan tidak dapat digunakan untuk membuat PO.');
                }
                
                if ($pesanan->status_pesanan_id < 2) {
                    return back()->with('error', 'Pesanan Catering belum dikonfirmasi.');
                }

                $kebutuhanService = app(KebutuhanBahanService::class);
                $kebutuhan = $kebutuhanService->kebutuhanBahanPesanan($pesanan);
                $bahanIds = $kebutuhan->pluck('bahan_baku_id')->unique();
                $bahans = BahanBaku::with('satuan')->whereIn('id', $bahanIds)->get()->keyBy('id');
                
                // Ambil Stok Catering
                $stoks = StokBahan::where('jenis_persediaan', StokBahan::JENIS_CATERING)
                    ->whereIn('bahan_baku_id', $bahanIds)
                    ->get()->keyBy('bahan_baku_id');
                    
                // Hitung riwayat PO sebelumnya untuk pesanan ini
                // Detail PO -> PO -> Pengadaan
                $poSebelumnya = DetailPurchaseOrder::whereHas('purchase_order.pengadaan_bahan', function($q) use ($pesanan) {
                    $q->where('pesanan_id', $pesanan->id);
                })->whereHas('purchase_order', function($q) {
                    $q->where('status', '!=', PurchaseOrder::DIBATALKAN);
                })->select('bahan_baku_id', DB::raw('SUM(jumlah_dipesan) as total_pesan'))
                  ->groupBy('bahan_baku_id')
                  ->get()->keyBy('bahan_baku_id');

                foreach($kebutuhan as $row) {
                    $bahan = $bahans->get($row['bahan_baku_id']);
                    $stok = $stoks->get($row['bahan_baku_id']);
                    if ($bahan) {
                        $kebutuhanAwal = $row['kebutuhan'];
                        $sudahDipesan = isset($poSebelumnya[$row['bahan_baku_id']]) ? (float) $poSebelumnya[$row['bahan_baku_id']]->total_pesan : 0;
                        
                        $kebutuhanBersih = max(0, $kebutuhanAwal - $sudahDipesan);
                        
                        $stokSaatIni = $stok ? (float) $stok->jumlah_stok : 0;
                        $jumlahBeli = max(0, $kebutuhanBersih - $stokSaatIni);
                        
                        $bahan->kebutuhan_awal = $kebutuhanAwal;
                        $bahan->sudah_dipesan = $sudahDipesan;
                        $bahan->kebutuhan_bersih = $kebutuhanBersih;
                        $bahan->jumlah_stok = $stokSaatIni;
                        $bahan->jumlah_beli = $jumlahBeli;
                        
                        $items->push($bahan);
                    }
                }
                
                // Info jika sudah ada PO
                if ($poSebelumnya->isNotEmpty()) {
                    session()->now('warning', 'Pesanan ini sudah memiliki Purchase Order sebelumnya. Hanya merekomendasikan sisa kebutuhan yang belum dipesan.');
                }
            }
        }

        return view('admin.pengadaan.purchase-order.create', compact('items', 'kodePo', 'tipe', 'pesanan'));
    }

    public function storeUnified(Request $request)
    {
        $request->validate([
            'tipe' => 'required|in:Operasional,Catering',
            'supplier_nama' => 'required|string|max:150',
            'supplier_telepon' => 'nullable|string|max:20',
            'supplier_alamat' => 'nullable|string|max:500',
            'item_checked' => 'required|array|min:1',
            'jumlah_beli' => 'required|array',
        ]);

        $checkeds = collect($request->item_checked)->filter(fn ($v) => $v)->keys()->all();
        if(count($checkeds) == 0) {
            return back()->with('error', 'Pilih minimal satu bahan untuk dibuatkan PO.');
        }

        DB::transaction(function () use ($request, $checkeds) {
            // Auto match or create Pemasok
            $pemasok = Pemasok::firstOrCreate(
                ['nama_pemasok' => $request->supplier_nama],
                ['nomor_telepon' => $request->supplier_telepon, 'alamat' => $request->supplier_alamat, 'kode_pemasok' => 'SUP-'.time()]
            );

            // Create Pengadaan (Background)
            $pengadaan = PengadaanBahan::create([
                'id_pengadaan' => 'REQ-' . time(),
                'pemasok_id' => $pemasok->id,
                'pesanan_id' => $request->pesanan_id ?? null,
                'diajukan_oleh' => auth()->id() ?? 1,
                'status_pengadaan_id' => 3, // Disetujui/Diproses
                'jenis_pengadaan' => $request->tipe === 'Catering' ? 'Catering' : 'Harian',
                'tanggal_pengadaan' => now()->toDateString(),
                'total_pengadaan' => 0, // Simplified
            ]);

            // Create PO
            $po = PurchaseOrder::create([
                'nomor_po' => $this->kodePo(),
                'pengadaan_bahan_id' => $pengadaan->id,
                'supplier' => $request->supplier_nama,
                'no_telp_supplier' => $request->supplier_telepon,
                'alamat_supplier' => $request->supplier_alamat,
                'jenis_po' => $request->tipe === 'Catering' ? 'catering' : 'operasional',
                'kode_pesanan_catering' => $request->pesanan_id ?? null,
                'tanggal_po' => now()->toDateString(),
                'status' => PurchaseOrder::MENUNGGU_BARANG,
                'catatan' => $request->catatan,
                'dibuat_oleh' => auth()->id() ?? 1,
            ]);

            foreach ($checkeds as $bahanId) {
                $jumlah = $request->jumlah_beli[$bahanId] ?? 0;
                if ($jumlah > 0) {
                    $bahan = \App\Models\BahanBaku::find($bahanId);
                    
                    $detailPengadaan = \App\Models\DetailPengadaanBahan::create([
                        'pengadaan_bahan_id' => $pengadaan->id,
                        'bahan_baku_id' => $bahanId,
                        'jumlah_dipesan' => $jumlah,
                        'satuan_id' => $bahan ? $bahan->satuan_id : 1,
                        'harga_satuan' => 0,
                        'subtotal' => 0,
                    ]);
                    
                    DetailPurchaseOrder::create([
                        'purchase_order_id' => $po->id,
                        'detail_pengadaan_bahan_id' => $detailPengadaan->id,
                        'bahan_baku_id' => $bahanId,
                        'jumlah_dipesan' => $jumlah,
                        'satuan_id' => $bahan ? $bahan->satuan_id : 1,
                    ]);
                }
            }
        });

        return redirect()->route('pengadaan.po.show', $po->id)->with('success', 'Purchase Order berhasil dibuat.');
    }

    public function show(PurchaseOrder $po)
    {
        $po->load(['pengadaan_bahan', 'detail_purchase_order.bahan_baku.satuan']);
        
        // Cek penerimaan yg sudah ada per bahan
        $penerimaanList = \App\Models\DetailPenerimaanBahan::whereHas('penerimaan_bahan', function($q) use ($po) {
            $q->where('purchase_order_id', $po->id);
        })->get()->groupBy('bahan_baku_id');

        $items = $po->detail_purchase_order->map(function($detail) use ($penerimaanList) {
            $diterima = 0;
            if (isset($penerimaanList[$detail->bahan_baku_id])) {
                $diterima = $penerimaanList[$detail->bahan_baku_id]->where('kondisi', 'Baik')->sum('jumlah_diterima');
            }
            $detail->sudah_diterima = $diterima;
            $detail->sisa = max(0, $detail->jumlah_pesanan - $diterima);
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

            $jenisPersediaan = $po->jenis_po === 'catering' ? StokBahan::JENIS_CATERING : StokBahan::JENIS_HARIAN;

            $adaDiterima = false;
            $semuaLengkap = true;

            $penerimaanList = \App\Models\DetailPenerimaanBahan::whereHas('penerimaan_bahan', function($q) use ($po) {
                $q->where('purchase_order_id', $po->id);
            })->get()->groupBy('bahan_baku_id');

            foreach ($po->detail_purchase_order as $detail) {
                $qtyTerima = $request->terima[$detail->bahan_baku_id] ?? 0;
                $kondisi = $request->kondisi[$detail->bahan_baku_id] ?? 'Baik';
                
                $sudah = 0;
                if (isset($penerimaanList[$detail->bahan_baku_id])) {
                    $sudah = $penerimaanList[$detail->bahan_baku_id]->where('kondisi', 'Baik')->sum('jumlah_diterima');
                }

                if ($qtyTerima > 0) {
                    $sisa = max(0, $detail->jumlah_dipesan - $sudah);
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
                        'harga_satuan' => 0,
                        'kondisi' => $kondisi,
                    ]);

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
                
                if ($sudah + ($kondisi === 'Baik' ? $qtyTerima : 0) < $detail->jumlah_dipesan) {
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
        $pdf = Pdf::loadView('admin.pengadaan.purchase-order.print', compact('po'));
        return $pdf->stream('Purchase-Order-' . $po->nomor_po . '.pdf');
    }

    public function cancel(PurchaseOrder $po)
    {
        $po->status = PurchaseOrder::DIBATALKAN;
        $po->save();
        return back()->with('success', 'Purchase Order dibatalkan.');
    }

    protected function kodePo(): string
    {
        $date = now()->format('Ymd');
        $count = PurchaseOrder::whereDate('dibuat_pada', now()->toDateString())->count() + 1;
        return 'PO-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}