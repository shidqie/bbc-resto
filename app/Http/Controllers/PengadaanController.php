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

    public function create()
    {
        $suppliers = Supplier::orderBy('nama_supplier')->get();
        // Hanya tampilkan bahan baku aktif
        $bahanBakus = BahanBaku::with('satuan')->where('status', 1)->orderBy('nama_bahan')->get();
        return view('pengadaan.create', compact('suppliers', 'bahanBakus'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'tanggal_pengadaan' => 'required|date',
            'catatan' => 'nullable|string',
            'bahan_baku_id' => 'required|array',
            'bahan_baku_id.*' => 'required|exists:bahan_bakus,id',
            'jumlah' => 'required|array',
            'jumlah.*' => 'required|numeric|min:0.01',
            'harga_satuan' => 'required|array',
            'harga_satuan.*' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Generate Kode Pengadaan
            $lastPengadaan = Pengadaan::latest()->first();
            $lastId = $lastPengadaan ? $lastPengadaan->id : 0;
            $kode = 'PO-' . date('Ymd') . '-' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);

            $pengadaan = Pengadaan::create([
                'kode_pengadaan' => $kode,
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
                    $jumlah = $request->jumlah[$index];
                    $hargaSatuan = $request->harga_satuan[$index];
                    $subtotal = $jumlah * $hargaSatuan;

                    DetailPengadaan::create([
                        'pengadaan_id' => $pengadaan->id,
                        'bahan_baku_id' => $bahanId,
                        'jumlah' => $jumlah,
                        'satuan' => $bahanBaku->satuan->nama_satuan ?? '',
                        'harga_satuan' => $hargaSatuan,
                        'subtotal' => $subtotal,
                    ]);

                    $totalBiaya += $subtotal;
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
        $pengadaan->load(['supplier', 'user', 'details.bahanBaku.kategoriBahan']);
        return view('pengadaan.show', compact('pengadaan'));
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
