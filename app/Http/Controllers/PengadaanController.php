<?php

namespace App\Http\Controllers;

use App\Models\Pengadaan;
use App\Models\DetailPengadaan;
use App\Models\Supplier;
use App\Models\BahanBaku;
use App\Models\PesananCatering;
use App\Models\PesananNasiBox;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PengadaanController extends Controller
{
    public function index(Request $request)
    {
        $query = Pengadaan::with(['supplier', 'user', 'pesananCatering', 'pesananNasiBox'])->latest();

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('kode_pengadaan', 'like', "%{$search}%")
                  ->orWhereHas('supplier', fn($s) => $s->where('nama_supplier', 'like', "%{$search}%"));
            });
        }

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        if ($request->has('jenis_pesanan') && $request->jenis_pesanan != '') {
            $query->where('jenis_pesanan', $request->jenis_pesanan);
        }

        $pengadaans = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => Pengadaan::count(),
            'pending' => Pengadaan::where('status', 'pending')->count(),
            'diterima' => Pengadaan::where('status', 'diterima')->count(),
            'dibatalkan' => Pengadaan::where('status', 'dibatalkan')->count(),
            'catering' => Pengadaan::where('jenis_pesanan', 'catering')->count(),
            'nasi_box' => Pengadaan::where('jenis_pesanan', 'nasi_box')->count(),
        ];

        return view('pengadaan.index', compact('pengadaans', 'stats'));
    }

    public function create(Request $request)
    {
        $suppliers = Supplier::orderBy('nama_supplier')->get();
        $bahanBakus = BahanBaku::with('satuan')->where('status', 1)->orderBy('nama_bahan')->get();

        // Pesanan Catering & Nasi Box Aktif (Terkonfirmasi)
        $pesananCaterings = PesananCatering::whereIn('status', ['menunggu_dp', 'menunggu_konfirmasi', 'terkonfirmasi', 'diproses'])
            ->latest()->get();

        $pesananNasiBoxes = PesananNasiBox::whereIn('status', ['menunggu_dp', 'menunggu_konfirmasi', 'terkonfirmasi', 'diproses'])
            ->latest()->get();

        $prepopulate = [];
        $bomAnalysis = [];
        $missingBomMenus = [];
        $selectedPesanan = null;
        $pesananId = $request->query('pesanan_id');
        $jenisPesanan = $request->query('jenis_pesanan', 'catering');

        if ($pesananId) {
            $estimasi = [];
            
            if ($jenisPesanan === 'nasi_box') {
                $selectedPesanan = PesananNasiBox::with('details.menu.resep.bahanBaku.satuan')->find($pesananId);
                if ($selectedPesanan) {
                    $portion = $selectedPesanan->jumlah_box;
                    foreach ($selectedPesanan->details as $detail) {
                        if ($detail->menu) {
                            if ($detail->menu->resep->isEmpty()) {
                                $missingBomMenus[] = [
                                    'id' => $detail->menu->id,
                                    'nama' => $detail->menu->nama,
                                ];
                            } else {
                                foreach ($detail->menu->resep as $r) {
                                    $bahanId = $r->bahan_baku_id;
                                    $kebutuhan = $r->jumlah_kebutuhan * $portion;
                                    if (!isset($estimasi[$bahanId])) {
                                        $estimasi[$bahanId] = [
                                            'bahan_baku_id' => $bahanId,
                                            'nama_bahan' => $r->bahanBaku->nama_bahan ?? 'Bahan Baku',
                                            'satuan' => $r->bahanBaku->satuan->nama_satuan ?? '',
                                            'kebutuhan_total' => 0,
                                            'stok_saat_ini' => $r->bahanBaku->stok ?? 0,
                                        ];
                                    }
                                    $estimasi[$bahanId]['kebutuhan_total'] += $kebutuhan;
                                }
                            }
                        }
                    }
                }
            } else {
                // Default: Catering
                $selectedPesanan = PesananCatering::with('details.menu.resep.bahanBaku.satuan')->find($pesananId);
                if ($selectedPesanan) {
                    $portion = $selectedPesanan->jumlah_porsi;
                    foreach ($selectedPesanan->details as $detail) {
                        if ($detail->menu) {
                            if ($detail->menu->resep->isEmpty()) {
                                $missingBomMenus[] = [
                                    'id' => $detail->menu->id,
                                    'nama' => $detail->menu->nama,
                                ];
                            } else {
                                foreach ($detail->menu->resep as $r) {
                                    $bahanId = $r->bahan_baku_id;
                                    $kebutuhan = $r->jumlah_kebutuhan * $portion;
                                    if (!isset($estimasi[$bahanId])) {
                                        $estimasi[$bahanId] = [
                                            'bahan_baku_id' => $bahanId,
                                            'nama_bahan' => $r->bahanBaku->nama_bahan ?? 'Bahan Baku',
                                            'satuan' => $r->bahanBaku->satuan->nama_satuan ?? '',
                                            'kebutuhan_total' => 0,
                                            'stok_saat_ini' => $r->bahanBaku->stok ?? 0,
                                        ];
                                    }
                                    $estimasi[$bahanId]['kebutuhan_total'] += $kebutuhan;
                                }
                            }
                        }
                    }
                }
            }

            // Analysis & Prepopulate
            foreach ($estimasi as $est) {
                $kekurangan = max(0, $est['kebutuhan_total'] - $est['stok_saat_ini']);
                
                $lastDetail = DetailPengadaan::where('bahan_baku_id', $est['bahan_baku_id'])
                    ->whereNotNull('harga_real')
                    ->latest('id')
                    ->first();
                $hargaPrediksi = $lastDetail ? $lastDetail->harga_real : 0;

                $bomAnalysis[] = array_merge($est, [
                    'kekurangan' => $kekurangan,
                    'harga_estimasi' => $hargaPrediksi
                ]);

                if ($kekurangan > 0) {
                    $prepopulate[] = [
                        'bahan_baku_id' => $est['bahan_baku_id'],
                        'jumlah' => $kekurangan,
                        'harga_estimasi' => $hargaPrediksi
                    ];
                }
            }
        }

        return view('pengadaan.create', compact(
            'suppliers', 
            'bahanBakus', 
            'pesananCaterings', 
            'pesananNasiBoxes', 
            'prepopulate', 
            'bomAnalysis',
            'pesananId', 
            'jenisPesanan',
            'selectedPesanan',
            'missingBomMenus'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'jenis_pesanan' => 'required|in:catering,nasi_box,umum',
            'pesanan_catering_id' => 'nullable|exists:pesanan_caterings,id',
            'pesanan_nasi_box_id' => 'nullable|exists:pesanan_nasi_boxes,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'tanggal_pengadaan' => 'required|date',
            'catatan' => 'nullable|string',
            'bahan_baku_id' => 'required|array',
            'bahan_baku_id.*' => 'required|exists:bahan_bakus,id',
            'jumlah_estimasi' => 'nullable|array',
            'jumlah_estimasi.*' => 'nullable|numeric|min:0.01',
            'harga_estimasi' => 'nullable|array',
            'harga_estimasi.*' => 'nullable|numeric|min:0',
            'jumlah' => 'nullable|array',
            'harga_satuan' => 'nullable|array',
        ]);

        try {
            DB::beginTransaction();

            $lastPengadaan = Pengadaan::latest()->first();
            $lastId = $lastPengadaan ? $lastPengadaan->id : 0;
            $kode = 'PO-' . date('Ymd') . '-' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);

            $pengadaan = Pengadaan::create([
                'kode_pengadaan' => $kode,
                'jenis_pesanan' => $request->jenis_pesanan,
                'pesanan_catering_id' => $request->jenis_pesanan === 'catering' ? $request->pesanan_catering_id : null,
                'pesanan_nasi_box_id' => $request->jenis_pesanan === 'nasi_box' ? $request->pesanan_nasi_box_id : null,
                'supplier_id' => $request->supplier_id,
                'tanggal_pengadaan' => $request->tanggal_pengadaan,
                'status' => 'pending',
                'catatan' => $request->catatan,
                'user_id' => Auth::id(),
                'total_biaya' => 0,
            ]);

            $totalBiaya = 0;

            if ($request->has('bahan_baku_id')) {
                foreach ($request->bahan_baku_id as $index => $bahanId) {
                    $bahanBaku = BahanBaku::with('satuan')->find($bahanId);
                    $jumlahEst = $request->jumlah_estimasi[$index] ?? ($request->jumlah[$index] ?? 0);
                    $hargaEst = $request->harga_estimasi[$index] ?? ($request->harga_satuan[$index] ?? 0);
                    $subtotalEst = $jumlahEst * $hargaEst;

                    DetailPengadaan::create([
                        'pengadaan_id' => $pengadaan->id,
                        'bahan_baku_id' => $bahanId,
                        'jumlah' => $jumlahEst,
                        'satuan' => $bahanBaku->satuan->nama_satuan ?? '',
                        'harga_satuan' => $hargaEst,
                        'subtotal' => $subtotalEst,
                        'jumlah_estimasi' => $jumlahEst,
                        'harga_estimasi' => $hargaEst,
                        'subtotal_estimasi' => $subtotalEst,
                    ]);

                    $totalBiaya += $subtotalEst;
                }
            }

            $pengadaan->update(['total_biaya' => $totalBiaya]);

            DB::commit();
            return redirect()->route('pengadaan.show', $pengadaan->id)->with('success', "Form Order Bahan Baku {$kode} berhasil disimpan.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat menyimpan order pengadaan: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Pengadaan $pengadaan)
    {
        $pengadaan->load(['supplier', 'user', 'pesananCatering', 'pesananNasiBox', 'details.bahanBaku.kategoriBahan']);
        return view('pengadaan.catering_rab', compact('pengadaan'));
    }

    public function realisasi(Request $request, Pengadaan $pengadaan, StockService $stockService)
    {
        $request->validate([
            'detail_id' => 'required|array',
            'jumlah_real' => 'required|array',
            'harga_real' => 'required|array',
        ]);

        try {
            DB::beginTransaction();

            $totalReal = 0;
            $jenisLabel = $pengadaan->jenis_label;

            foreach ($request->detail_id as $index => $detailId) {
                $detail = DetailPengadaan::findOrFail($detailId);
                $qty = $request->jumlah_real[$index];
                $harga = $request->harga_real[$index];
                $sub = $qty * $harga;

                $detail->update([
                    'jumlah_real' => $qty,
                    'harga_real' => $harga,
                    'subtotal_real' => $sub,
                    'jumlah' => $qty,
                    'harga_satuan' => $harga,
                    'subtotal' => $sub
                ]);

                $totalReal += $sub;

                // Tambahkan stok bahan baku & catat mutasi stok dengan tag jenis_pesanan (catering / nasi box)
                $stockService->addStock(
                    $detail->bahan_baku_id, 
                    $qty, 
                    "Penerimaan Bahan Baku {$jenisLabel} PO: {$pengadaan->kode_pengadaan}"
                );
            }

            $pengadaan->update([
                'total_biaya' => $totalReal,
                'status' => 'diterima'
            ]);

            DB::commit();
            return redirect()->route('pengadaan.show', $pengadaan->id)->with('success', "Penerimaan Bahan Baku {$pengadaan->kode_pengadaan} berhasil disimpan, data stok telah diperbarui.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses penerimaan barang: ' . $e->getMessage());
        }
    }

    public function updateStatus(Request $request, Pengadaan $pengadaan)
    {
        $request->validate([
            'status' => 'required|in:pending,diterima,dibatalkan',
            'catatan' => 'nullable|string'
        ]);

        $pengadaan->status = $request->status;
        if ($request->has('catatan')) {
            $pengadaan->catatan = $request->catatan;
        }
        $pengadaan->save();

        return back()->with('success', "Status pengadaan {$pengadaan->kode_pengadaan} berhasil diubah menjadi " . ucfirst($request->status));
    }
}
