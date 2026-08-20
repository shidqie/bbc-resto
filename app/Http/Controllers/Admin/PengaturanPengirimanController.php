<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PengaturanPengiriman;
use App\Models\AturanPengiriman;
use App\Models\RiwayatPengaturanPengiriman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PengaturanPengirimanController extends Controller
{
    public function index()
    {
        $pengaturan = PengaturanPengiriman::first();
        if (!$pengaturan) {
            $pengaturan = new PengaturanPengiriman([
                'tarif_per_km' => 5000,
                'status_aktif' => true,
            ]);
        }
        
        $aturan = AturanPengiriman::orderBy('minimal_porsi', 'asc')->get()->map(function ($a) {
            $a->kilometer_gratis = (float) $a->kilometer_gratis;
            return $a;
        });
        $riwayats = RiwayatPengaturanPengiriman::with('diubahOleh')->latest('dibuat_pada')->paginate(5);
        
        return view('admin.pengaturan.pengiriman', compact('pengaturan', 'aturan', 'riwayats'));
    }

    public function update(Request $request)
    {
        $rules = [];
        if ($request->has('tarif_dasar') || $request->has('tarif_per_km')) {
            $rules['tarif_dasar'] = 'required|numeric|min:0';
            $rules['tarif_per_km'] = 'required|numeric|min:0';
        }

        if ($request->has('aturan') || $request->has('simpan_aturan')) {
            $rules['aturan.*.id'] = 'nullable|exists:aturan_pengiriman,id';
            $rules['aturan.*.minimal_porsi'] = 'required|integer|min:0';
            $rules['aturan.*.maksimal_porsi'] = 'nullable|integer|gt:aturan.*.minimal_porsi';
            $rules['aturan.*.kilometer_gratis'] = 'required|numeric|min:0';
        }

        $request->validate($rules);

        DB::transaction(function () use ($request) {
            // Update pengaturan umum tarif
            if ($request->has('tarif_dasar') && $request->has('tarif_per_km')) {
                $pengaturan = PengaturanPengiriman::first();
                $dataBaru = [
                    'tarif_dasar' => $request->tarif_dasar,
                    'tarif_per_km' => $request->tarif_per_km,
                    'status_aktif' => true,
                ];

                if ($pengaturan) {
                    $dataLama = $pengaturan->only(['tarif_dasar', 'tarif_per_km', 'status_aktif']);
                    $pengaturan->update(array_merge($dataBaru, ['diperbarui_oleh' => auth()->id()]));
                } else {
                    $dataLama = [];
                    $pengaturan = PengaturanPengiriman::create(array_merge($dataBaru, ['diperbarui_oleh' => auth()->id()]));
                }

                if (!$dataLama || json_encode($dataLama) !== json_encode($dataBaru)) {
                    RiwayatPengaturanPengiriman::create([
                        'nilai_lama' => $dataLama,
                        'nilai_baru' => $dataBaru,
                        'diubah_oleh' => auth()->id(),
                    ]);
                }
            }

            // Update aturan gratis ongkir
            if ($request->has('aturan') || $request->has('simpan_aturan')) {
                $aturanArr = $request->input('aturan', []);
                $aturanIds = collect($aturanArr)->pluck('id')->filter()->toArray();
                AturanPengiriman::whereNotIn('id', $aturanIds)->delete();

                foreach ($aturanArr as $aturData) {
                    if (!empty($aturData['id'])) {
                        $aturan = AturanPengiriman::find($aturData['id']);
                        if ($aturan) {
                            $aturan->update([
                                'minimal_porsi' => $aturData['minimal_porsi'],
                                'maksimal_porsi' => $aturData['maksimal_porsi'] ?? null,
                                'kilometer_gratis' => $aturData['kilometer_gratis'],
                            ]);
                        }
                    } else {
                        AturanPengiriman::create([
                            'minimal_porsi' => $aturData['minimal_porsi'],
                            'maksimal_porsi' => $aturData['maksimal_porsi'] ?? null,
                            'kilometer_gratis' => $aturData['kilometer_gratis'],
                            'status_aktif' => true,
                        ]);
                    }
                }
            }
        });

        return redirect()->back()->with('success', 'Pengaturan berhasil disimpan.');
    }
}
