<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\DetailPenyesuaianStok;
use App\Models\MutasiStok;
use App\Models\PenyesuaianStok;
use App\Models\StokBahanBaku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PenyesuaianStokController extends Controller
{
    public function index(Request $request)
    {
        $query = PenyesuaianStok::with(['dibuat_oleh_pengguna', 'detail_penyesuaian_stok.bahan_baku'])
            ->orderBy('id', 'desc');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('nomor_penyesuaian', 'like', "%{$search}%")
                ->orWhere('alasan', 'like', "%{$search}%");
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
        $bahanBakus = BahanBaku::with(['satuan', 'stok_bahan_baku'])->where('status_aktif', true)->get();

        return view('inventory.penyesuaian-stok.create', compact('bahanBakus'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'alasan' => 'required|string',
            'bahan_baku_id' => 'required|array|min:1',
            'bahan_baku_id.*' => 'required|exists:bahan_baku,id',
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

            foreach ($request->bahan_baku_id as $idx => $bahanId) {
                $bahan = BahanBaku::find($bahanId);
                $stok = $bahan->stok_bahan_baku;

                $jumlahSistem = $stok ? $stok->jumlah_stok : 0;
                $jumlahFisik = $request->jumlah_fisik[$idx];
                $selisih = $jumlahFisik - $jumlahSistem;

                if ($selisih == 0) {
                    continue;
                }

                DetailPenyesuaianStok::create([
                    'penyesuaian_stok_id' => $penyesuaian->id,
                    'bahan_baku_id' => $bahanId,
                    'jumlah_sistem' => $jumlahSistem,
                    'jumlah_fisik' => $jumlahFisik,
                    'jumlah_selisih' => $selisih,
                    'satuan_id' => $bahan->satuan_id,
                    'catatan' => $request->catatan_item[$idx] ?? null,
                ]);

                // Update stok aktual
                if ($stok) {
                    $stok->jumlah_stok = $jumlahFisik;
                    $stok->save();
                } else {
                    StokBahanBaku::create([
                        'bahan_baku_id' => $bahanId,
                        'jumlah_stok' => $jumlahFisik,
                        'terakhir_diperbarui' => now(),
                    ]);
                }

                // Catat mutasi
                MutasiStok::create([
                    'bahan_baku_id' => $bahanId,
                    'dibuat_oleh' => Auth::id(),
                    'jenis_mutasi_stok_id' => $selisih > 0 ? 3 : 4, // 3=PENYESUAIAN_MASUK, 4=PENYESUAIAN_KELUAR
                    'jumlah' => abs($selisih),
                    'satuan_id' => $bahan->satuan_id ?? 1,
                    'tanggal_mutasi' => now(),
                    'jenis_stok' => 'OPERASIONAL',
                    'referensi_id' => $penyesuaian->id,
                    'catatan' => "Penyesuaian Stok: {$nomorPenyesuaian} | {$request->alasan}",
                    'detail_penyesuaian_stok_id' => DetailPenyesuaianStok::where('penyesuaian_stok_id', $penyesuaian->id)->where('bahan_baku_id', $bahanId)->value('id'),
                ]);
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
