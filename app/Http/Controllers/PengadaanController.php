<?php

namespace App\Http\Controllers;

use App\Models\Pengadaan;
use App\Models\DetailPengadaan;
use App\Models\Supplier;
use App\Models\BahanBaku;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PengadaanController extends Controller
{
    public function index(Request $request)
    {
        $query = Pengadaan::with(['supplier', 'user'])->latest();

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('kode_pengadaan', 'like', "%{$search}%");
        }

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        $pengadaans = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => Pengadaan::count(),
            'pending' => Pengadaan::where('status', 'pending')->count(),
            'diterima' => Pengadaan::where('status', 'diterima')->count(),
            'dibatalkan' => Pengadaan::where('status', 'dibatalkan')->count(),
        ];

        return view('pengadaan.index', compact('pengadaans', 'stats'));
    }

    public function create(Request $request)
    {
        $suppliers = Supplier::orderBy('nama_supplier')->get();
        // Hanya tampilkan bahan baku aktif
        $bahanBakus = BahanBaku::with('satuan')->where('status', 1)->orderBy('nama_bahan')->get();

        $prepopulate = [];
        $pesananId = $request->query('pesanan_id');
        if ($pesananId) {
            $pesananCatering = \App\Models\PesananCatering::with('details.menu.resep.bahanBaku.satuan')->find($pesananId);
            if ($pesananCatering && in_array($pesananCatering->status, ['menunggu_dp', 'menunggu_konfirmasi', 'terkonfirmasi', 'diproses'])) {
                $estimasi = [];
                foreach ($pesananCatering->details as $detail) {
                    if ($detail->menu) {
                        foreach ($detail->menu->resep as $r) {
                            $bahanId = $r->bahan_baku_id;
                            $kebutuhan = $r->jumlah_kebutuhan * $pesananCatering->jumlah_porsi;
                            
                            if (!isset($estimasi[$bahanId])) {
                                $estimasi[$bahanId] = [
                                    'bahan_baku_id' => $bahanId,
                                    'kebutuhan_total' => 0,
                                    'stok_saat_ini' => $r->bahanBaku->stok ?? 0,
                                ];
                            }
                            $estimasi[$bahanId]['kebutuhan_total'] += $kebutuhan;
                        }
                    }
                }
                
                foreach ($estimasi as $est) {
                    $kekurangan = max(0, $est['kebutuhan_total'] - $est['stok_saat_ini']);
                    if ($kekurangan > 0) {
                        // Cari harga riwayat terakhir untuk prediksi harga estimasi
                        $lastDetail = DetailPengadaan::where('bahan_baku_id', $est['bahan_baku_id'])
                            ->whereNotNull('harga_real')
                            ->latest('id')
                            ->first();
                        $hargaPrediksi = $lastDetail ? $lastDetail->harga_real : 0;

                        $prepopulate[] = [
                            'bahan_baku_id' => $est['bahan_baku_id'],
                            'jumlah' => $kekurangan,
                            'harga_estimasi' => $hargaPrediksi
                        ];
                    }
                }
            }
        }

        return view('pengadaan.create', compact('suppliers', 'bahanBakus', 'prepopulate', 'pesananId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'pesanan_catering_id' => 'nullable|exists:pesanan_caterings,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'tanggal_pengadaan' => 'required|date',
            'catatan' => 'nullable|string',
            'bahan_baku_id' => 'required|array',
            'bahan_baku_id.*' => 'required|exists:bahan_bakus,id',
            'jumlah_estimasi' => 'nullable|array',
            'jumlah_estimasi.*' => 'nullable|numeric|min:0.01',
            'harga_estimasi' => 'nullable|array',
            'harga_estimasi.*' => 'nullable|numeric|min:0',
            // Default input arrays for regular / old views
            'jumlah' => 'nullable|array',
            'harga_satuan' => 'nullable|array',
        ]);

        try {
            DB::beginTransaction();

            // Generate Kode Pengadaan
            $lastPengadaan = Pengadaan::latest()->first();
            $lastId = $lastPengadaan ? $lastPengadaan->id : 0;
            $kode = 'PO-' . date('Ymd') . '-' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);

            $pengadaan = Pengadaan::create([
                'kode_pengadaan' => $kode,
                'pesanan_catering_id' => $request->pesanan_catering_id,
                'supplier_id' => $request->supplier_id,
                'tanggal_pengadaan' => $request->tanggal_pengadaan,
                'status' => 'pending', // Status awal selalu pending
                'catatan' => $request->catatan,
                'user_id' => Auth::id(),
                'total_biaya' => 0, // Akan dihitung nanti
            ]);

            $totalBiaya = 0;

            if ($request->has('bahan_baku_id')) {
                foreach ($request->bahan_baku_id as $index => $bahanId) {
                    $bahanBaku = BahanBaku::with('satuan')->find($bahanId);
                    
                    // Gunakan estimasi jika ada, jika tidak fallback ke input reguler
                    $jumlahEst = $request->jumlah_estimasi[$index] ?? ($request->jumlah[$index] ?? 0);
                    $hargaEst = $request->harga_estimasi[$index] ?? ($request->harga_satuan[$index] ?? 0);
                    $subtotalEst = $jumlahEst * $hargaEst;

                    DetailPengadaan::create([
                        'pengadaan_id' => $pengadaan->id,
                        'bahan_baku_id' => $bahanId,
                        'jumlah' => $jumlahEst, // Legacy fallback
                        'satuan' => $bahanBaku->satuan->nama_satuan ?? '',
                        'harga_satuan' => $hargaEst, // Legacy fallback
                        'subtotal' => $subtotalEst, // Legacy fallback
                        'jumlah_estimasi' => $jumlahEst,
                        'harga_estimasi' => $hargaEst,
                        'subtotal_estimasi' => $subtotalEst,
                    ]);

                    $totalBiaya += $subtotalEst;
                }
            }

            $pengadaan->update(['total_biaya' => $totalBiaya]);

            DB::commit();
            return redirect()->route('pengadaan.show', $pengadaan->id)->with('success', 'Pengadaan berhasil dibuat dengan status Pending.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat menyimpan pengadaan: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Pengadaan $pengadaan)
    {
        $pengadaan->load(['supplier', 'user', 'pesananCatering', 'details.bahanBaku.kategoriBahan']);
        
        if ($pengadaan->pesanan_catering_id) {
            return view('pengadaan.catering_rab', compact('pengadaan'));
        }

        return view('pengadaan.show', compact('pengadaan'));
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
            foreach ($request->detail_id as $index => $detailId) {
                $detail = DetailPengadaan::findOrFail($detailId);
                $qty = $request->jumlah_real[$index];
                $harga = $request->harga_real[$index];
                $sub = $qty * $harga;

                $detail->update([
                    'jumlah_real' => $qty,
                    'harga_real' => $harga,
                    'subtotal_real' => $sub,
                    // Legacy sync for normal systems to work without errors
                    'jumlah' => $qty,
                    'harga_satuan' => $harga,
                    'subtotal' => $sub
                ]);

                $totalReal += $sub;

                // Add stock
                $stockService->addStock(
                    $detail->bahan_baku_id, 
                    $qty, 
                    "Realisasi Belanja Catering: {$pengadaan->kode_pengadaan}"
                );
            }

            $pengadaan->update([
                'status' => 'diterima',
                'total_biaya' => $totalReal
            ]);

            DB::commit();
            return back()->with('success', 'Realisasi belanja berhasil disimpan dan stok otomatis bertambah.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function updateStatus(Request $request, Pengadaan $pengadaan, StockService $stockService)
    {
        $request->validate([
            'status' => 'required|in:diterima,dibatalkan'
        ]);

        if ($pengadaan->status != 'pending') {
            return back()->with('error', 'Hanya pengadaan berstatus pending yang dapat diubah statusnya.');
        }

        try {
            DB::beginTransaction();

            $pengadaan->status = $request->status;
            $pengadaan->save();

            // Jika diterima, tambah stok bahan baku
            if ($request->status == 'diterima') {
                foreach ($pengadaan->details as $detail) {
                    $stockService->addStock(
                        $detail->bahan_baku_id, 
                        $detail->jumlah, 
                        "Pengadaan Diterima: {$pengadaan->kode_pengadaan}"
                    );
                }
            }

            DB::commit();
            
            $pesan = $request->status == 'diterima' ? 'Pengadaan berhasil diterima dan stok telah ditambahkan.' : 'Pengadaan berhasil dibatalkan.';
            return back()->with('success', $pesan);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
