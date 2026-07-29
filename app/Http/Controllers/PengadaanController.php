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
        $query = Pengadaan::with('user')->latest();

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nomor_pengadaan', 'like', "%{$search}%")
                  ->orWhere('asal_pembelian', 'like', "%{$search}%");
            });
        }

        $pengadaans = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => Pengadaan::count(),
            'total_biaya' => Pengadaan::sum('total_biaya'),
        ];

        return view('pengadaan.index', compact('pengadaans', 'stats'));
    }

    public function create(Request $request)
    {
        $bahanBakus = BahanBaku::with('satuan')->where('status', 1)->orderBy('nama_bahan')->get();
        
        $prefillItems = [];
        $kodePesananError = null;
        
        if ($request->has('kode_pesanan')) {
            $kode = trim($request->kode_pesanan);
            if (str_starts_with($kode, 'CTR')) {
                $pid = PesananCatering::where('kode_pesanan', $kode)->value('id');
                if($pid) $request->merge(['pesanan_id' => $pid]);
                else $kodePesananError = "Pesanan Catering tidak ditemukan.";
            } else if (str_starts_with($kode, 'NBX')) {
                $pid = PesananNasiBox::where('kode_pesanan', $kode)->value('id');
                if($pid) $request->merge(['pesanan_nasibox_id' => $pid]);
                else $kodePesananError = "Pesanan Nasi Box tidak ditemukan.";
            } else {
                $kodePesananError = "Format Kode Pesanan tidak valid.";
            }
        }

        if ($request->has('pesanan_id')) {
            $pesanan = PesananCatering::with('details.menu.resep.bahanBaku')->find($request->pesanan_id);
            if ($pesanan) {
                $kebutuhan = [];
                foreach ($pesanan->details as $detail) {
                    if ($detail->menu && $detail->menu->resep) {
                        foreach ($detail->menu->resep as $resep) {
                            $bahanId = $resep->bahan_baku_id;
                            $qty = $resep->jumlah_kebutuhan * $pesanan->jumlah_porsi;
                            $kebutuhan[$bahanId] = ($kebutuhan[$bahanId] ?? 0) + $qty;
                        }
                    }
                }
                foreach ($kebutuhan as $bahanId => $totalKebutuhan) {
                    $bahan = $bahanBakus->firstWhere('id', $bahanId);
                    if ($bahan) {
                        $kurang = $totalKebutuhan - $bahan->stok;
                        if ($kurang > 0) {
                            $prefillItems[] = [
                                'bahan_baku_id' => $bahanId,
                                'jumlah_beli' => ceil($kurang * 100) / 100
                            ];
                        }
                    }
                }
            }
        } elseif ($request->has('pesanan_nasibox_id')) {
            $pesanan = PesananNasiBox::with('details.menu.resep.bahanBaku')->find($request->pesanan_nasibox_id);
            if ($pesanan) {
                $kebutuhan = [];
                foreach ($pesanan->details as $detail) {
                    if ($detail->menu && $detail->menu->resep) {
                        foreach ($detail->menu->resep as $resep) {
                            $bahanId = $resep->bahan_baku_id;
                            $qty = $resep->jumlah_kebutuhan * $pesanan->jumlah_box;
                            $kebutuhan[$bahanId] = ($kebutuhan[$bahanId] ?? 0) + $qty;
                        }
                    }
                }
                foreach ($kebutuhan as $bahanId => $totalKebutuhan) {
                    $bahan = $bahanBakus->firstWhere('id', $bahanId);
                    if ($bahan) {
                        $kurang = $totalKebutuhan - $bahan->stok;
                        if ($kurang > 0) {
                            $prefillItems[] = [
                                'bahan_baku_id' => $bahanId,
                                'jumlah_beli' => ceil($kurang * 100) / 100
                            ];
                        }
                    }
                }
            }
        }

        return view('pengadaan.create', compact('bahanBakus', 'prefillItems', 'kodePesananError'));
    }

    public function store(Request $request, StockService $stockService)
    {
        $request->validate([
            'tanggal_pengadaan' => 'required|date',
            'asal_pembelian' => 'nullable|string|max:255',
            'catatan' => 'nullable|string',
            'bahan_baku_id' => 'required|array',
            'bahan_baku_id.*' => 'required|exists:bahan_bakus,id',
            'jumlah' => 'required|array',
            'jumlah.*' => 'required|numeric|min:0.01',
        ]);

        try {
            DB::beginTransaction();

            $lastPengadaan = Pengadaan::latest()->first();
            $lastId = $lastPengadaan ? $lastPengadaan->id : 0;
            $kode = 'PO-' . date('Ymd') . '-' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);

            $pengadaan = Pengadaan::create([
                'nomor_pengadaan' => $kode,
                'asal_pembelian' => $request->asal_pembelian,
                'tanggal_pengadaan' => $request->tanggal_pengadaan,
                'catatan' => $request->catatan,
                'user_id' => Auth::id(),
                'total_biaya' => 0,
            ]);

            $totalBiaya = 0;

            foreach ($request->bahan_baku_id as $index => $bahanId) {
                $bahanBaku = BahanBaku::with('satuan')->find($bahanId);
                $qty = $request->jumlah[$index];
                $harga = 0;
                $subtotal = 0;

                DetailPengadaan::create([
                    'pengadaan_id' => $pengadaan->id,
                    'bahan_baku_id' => $bahanId,
                    'jumlah' => $qty,
                    'satuan' => $bahanBaku->satuan->nama_satuan ?? '',
                    'harga_satuan' => $harga,
                    'subtotal' => $subtotal,
                ]);

                $totalBiaya += $subtotal;
            }

            $pengadaan->update(['total_biaya' => $totalBiaya, 'status' => 'pending']);

            DB::commit();
            return redirect()->route('pengadaan.show', $pengadaan->id)->with('success', "Permintaan Pembelian (PO) {$kode} berhasil dibuat. Silakan unduh PDF untuk belanja.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat menyimpan pengadaan: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Pengadaan $pengadaan)
    {
        $pengadaan->load(['user', 'details.bahanBaku.kategoriBahan']);
        return view('pengadaan.show', compact('pengadaan'));
    }

    public function exportPdf(Pengadaan $pengadaan)
    {
        $pengadaan->load(['details.bahanBaku.satuan', 'user']);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pengadaan.pdf', compact('pengadaan'));
        $pdf->setPaper('a4', 'portrait');
        return $pdf->download('Permintaan_Pembelian_' . $pengadaan->nomor_pengadaan . '.pdf');
    }

    public function terimaBarang(Request $request)
    {
        $request->validate([
            'nomor_pengadaan' => 'required|string'
        ]);

        $kode = trim($request->nomor_pengadaan);
        $pengadaan = Pengadaan::where('nomor_pengadaan', $kode)->first();

        if (!$pengadaan) {
            return back()->with('error', "PO dengan ID {$kode} tidak ditemukan.");
        }

        if ($pengadaan->status === 'diterima') {
            return back()->with('error', "PO {$kode} sudah pernah diterima sebelumnya.");
        }

        return redirect()->route('pengadaan.form-terima', $pengadaan->id);
    }

    public function formTerima(Pengadaan $pengadaan)
    {
        if ($pengadaan->status === 'diterima') {
            return redirect()->route('pengadaan.index')->with('error', "PO {$pengadaan->nomor_pengadaan} sudah diterima.");
        }

        $pengadaan->load(['details.bahanBaku.satuan']);
        return view('pengadaan.terima', compact('pengadaan'));
    }

    public function prosesTerima(Request $request, Pengadaan $pengadaan, StockService $stockService)
    {
        if ($pengadaan->status === 'diterima') {
            return redirect()->route('pengadaan.index')->with('error', "PO sudah diterima sebelumnya.");
        }

        $request->validate([
            'jumlah_aktual' => 'required|array',
            'jumlah_aktual.*' => 'required|numeric|min:0',
            'total_belanja' => 'required|numeric|min:0'
        ]);

        try {
            DB::beginTransaction();

            $totalBelanja = $request->total_belanja;

            foreach ($pengadaan->details as $detail) {
                $actualQty = $request->jumlah_aktual[$detail->id] ?? $detail->jumlah;
                
                // Update detail with actual qty
                $detail->update([
                    'jumlah' => $actualQty
                ]);

                // Update Stok & Mutasi if actual quantity > 0
                if ($actualQty > 0) {
                    $stockService->addStock(
                        $detail->bahan_baku_id, 
                        $actualQty, 
                        "Penerimaan Bahan Baku (PO: {$pengadaan->nomor_pengadaan})",
                        null,
                        $pengadaan->nomor_pengadaan
                    );
                }
            }

            // Update pengadaan status and total cost
            $pengadaan->update([
                'status' => 'diterima',
                'total_biaya' => $totalBelanja
            ]);

            DB::commit();
            return redirect()->route('pengadaan.index')->with('success', "Bahan baku dari PO {$pengadaan->nomor_pengadaan} berhasil diterima. Stok terupdate.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat memproses penerimaan: ' . $e->getMessage());
        }
    }
}
