<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\DetailPenyesuaianStok;
use App\Models\PenyesuaianStok;
use App\Models\StokBahan;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PenyesuaianStokController extends Controller
{
    public function index(Request $request)
    {
        $query = DetailPenyesuaianStok::with(['penyesuaian_stok.dibuat_oleh_pengguna', 'bahan_baku.satuan'])
            ->join('penyesuaian_stok', 'detail_penyesuaian_stok.penyesuaian_stok_id', '=', 'penyesuaian_stok.id')
            ->select('detail_penyesuaian_stok.*')
            ->orderBy('penyesuaian_stok.tanggal_penyesuaian', 'desc')
            ->orderBy('detail_penyesuaian_stok.id', 'desc');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->whereHas('bahan_baku', function($q) use ($search) {
                $q->where('nama_bahan', 'like', "%{$search}%");
            });
        }

        if ($request->has('jenis_persediaan') && $request->jenis_persediaan != '') {
            $query->where('detail_penyesuaian_stok.jenis_persediaan', $request->jenis_persediaan);
        }

        $penyesuaians = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => PenyesuaianStok::count(),
            'disetujui' => PenyesuaianStok::where('status_penyesuaian', 'DISETUJUI')->count(),
            'menunggu' => PenyesuaianStok::where('status_penyesuaian', 'MENUNGGU')->count(),
        ];

        return view('inventory.penyesuaian-stok.index', compact('penyesuaians', 'stats'));
    }

    public function create()
    {
        $bahanBakus = BahanBaku::with(['satuan', 'stok_harian', 'stok_catering_balance'])->where('status_aktif', true)->get();

        return view('inventory.penyesuaian-stok.create', compact('bahanBakus'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'alasan' => 'required|string',
            'bahan_baku_id' => 'required|array|min:1',
            'bahan_baku_id.*' => 'required|exists:bahan_baku,id',
            'jenis_persediaan' => 'required|array',
            'jenis_persediaan.*' => 'required|in:harian,catering',
            'jumlah_fisik' => 'required|array',
            'jumlah_fisik.*' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $nomorPenyesuaian = 'ADJ-'.date('Ymd').'-'.rand(100, 999);

            $penyesuaian = PenyesuaianStok::create([
                'nomor_penyesuaian' => $nomorPenyesuaian,
                'tanggal_penyesuaian' => now(),
                'dibuat_oleh' => Auth::id(),
                'alasan' => $request->alasan,
                'status_penyesuaian' => 'DISETUJUI', // auto approve
            ]);

            $stockService = app(StockService::class);

            foreach ($request->bahan_baku_id as $idx => $bahanId) {
                $bahan = BahanBaku::find($bahanId);
                $jenisPersediaan = $request->jenis_persediaan[$idx] ?? StokBahan::JENIS_HARIAN;
                $stokRow = StokBahan::where('bahan_baku_id', $bahanId)
                    ->where('jenis_persediaan', $jenisPersediaan)->first();

                $jumlahSistem = $stokRow ? (float) $stokRow->jumlah_stok : 0;
                $jumlahFisik = $request->jumlah_fisik[$idx];
                $selisih = $jumlahFisik - $jumlahSistem;

                if (abs($selisih) < 0.0001) {
                    continue;
                }

                $detail = DetailPenyesuaianStok::create([
                    'penyesuaian_stok_id' => $penyesuaian->id,
                    'bahan_baku_id' => $bahanId,
                    'jenis_persediaan' => $jenisPersediaan,
                    'jumlah_sistem' => $jumlahSistem,
                    'jumlah_fisik' => $jumlahFisik,
                    'jumlah_selisih' => $selisih,
                    'satuan_id' => $bahan->satuan_id,
                    'catatan' => $request->catatan_item[$idx] ?? null,
                ]);

                // Update saldo + buat mutasi penyesuaian secara atomic (kartu stok).
                $stockService->adjustStock(
                    $bahanId,
                    (float) $jumlahFisik,
                    "Penyesuaian Stok: {$nomorPenyesuaian} | {$request->alasan}",
                    null,
                    Auth::id(),
                    ['detail_penyesuaian_stok_id' => $detail->id],
                    $jenisPersediaan,
                );
            }

            DB::commit();

            return redirect()->route('penyesuaian-stok.index')->with('success', "Penyesuaian Stok {$nomorPenyesuaian} berhasil disimpan dan stok telah diperbarui.");

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }

    public function show($id)
    {
        $penyesuaian = PenyesuaianStok::with([
            'dibuat_oleh_pengguna',
            'disetujui_oleh_pengguna',
            'detail_penyesuaian_stok.bahan_baku.satuan',
        ])->findOrFail($id);

        return view('inventory.penyesuaian-stok.show', compact('penyesuaian'));
    }
}
