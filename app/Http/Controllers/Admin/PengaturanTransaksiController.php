<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PengaturanTransaksi;
use App\Models\RiwayatPengaturanTransaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PengaturanTransaksiController extends Controller
{
    public function index()
    {
        $pengaturan = PengaturanTransaksi::first();
        if (!$pengaturan) {
            $pengaturan = new PengaturanTransaksi([
                'pajak_aktif' => true,
                'persentase_pajak' => 10,
                'layanan_aktif' => true,
                'persentase_layanan' => 5,
            ]);
        }
        $riwayats = RiwayatPengaturanTransaksi::with('diubahOleh')->latest('dibuat_pada')->paginate(5);
        return view('admin.pengaturan.transaksi', compact('pengaturan', 'riwayats'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'layanan_aktif' => 'nullable',
            'nominal_layanan' => 'required_if:layanan_aktif,1|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $pengaturan = PengaturanTransaksi::first();
            $layanan_aktif = $request->has('layanan_aktif');
            
            $dataBaru = [
                'pajak_aktif' => false,
                'persentase_pajak' => 0,
                'layanan_aktif' => $layanan_aktif,
                'persentase_layanan' => 0,
                'nominal_layanan' => $layanan_aktif ? (float)$request->nominal_layanan : 0,
            ];

            if ($pengaturan) {
                $dataLama = $pengaturan->only(['pajak_aktif', 'persentase_pajak', 'layanan_aktif', 'persentase_layanan', 'nominal_layanan']);
                $pengaturan->update(array_merge($dataBaru, ['diperbarui_oleh' => auth()->id()]));
            } else {
                $dataLama = [];
                $pengaturan = PengaturanTransaksi::create(array_merge($dataBaru, ['diperbarui_oleh' => auth()->id()]));
            }

            // Catat riwayat jika ada perubahan
            if (!$dataLama || json_encode($dataLama) !== json_encode($dataBaru)) {
                RiwayatPengaturanTransaksi::create([
                    'nilai_lama' => $dataLama,
                    'nilai_baru' => $dataBaru,
                    'diubah_oleh' => auth()->id(),
                ]);
            }
        });

        return redirect()->back()->with('success', 'Pengaturan transaksi berhasil disimpan.');
    }
}
