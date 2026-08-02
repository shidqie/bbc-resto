<?php

namespace App\Services;

use App\Models\BahanBaku;
use App\Models\Menu;
use App\Models\MutasiStok;
use App\Models\PaketCatering;
use App\Models\PesananNasiBox;
use App\Models\StokBahanBaku;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PesananNasiBoxService
{
    public static function hitungOngkir($jumlahBox, $jarakKm, $metodePengiriman)
    {
        if ($metodePengiriman === 'pickup' || $jarakKm === null) {
            return 0;
        }

        if ($jumlahBox < 25) {
            throw new \Exception('Delivery tidak tersedia, minimum 25 box.');
        }
        if ($jarakKm > 30) {
            throw new \Exception('Di luar area layanan, maksimal 30 km.');
        }

        $jarakGratis = 0;
        if ($jumlahBox >= 50 && $jumlahBox < 100) {
            $jarakGratis = 10;
        } elseif ($jumlahBox >= 100) {
            $jarakGratis = 20;
        }

        $sisaJarak = max(0, $jarakKm - $jarakGratis);
        $ongkir = $sisaJarak * 3000;

        if ($ongkir > 0 && $ongkir < 10000 && $jarakKm < 3) {
            $ongkir = 10000;
        }

        return $ongkir;
    }

    public static function hitungTotal($paketId, $jumlahBox, $ongkir = 0)
    {
        $paket = PaketCatering::findOrFail($paketId);
        $hargaPerBox = $paket->harga;

        $subtotal = $hargaPerBox * $jumlahBox;
        $total = $subtotal + $ongkir;
        $dp = ceil($total * 0.25); // 25% DP

        return [
            'harga_per_box' => $hargaPerBox,
            'subtotal' => $subtotal,
            'ongkir' => $ongkir,
            'total' => $total,
            'dp' => $dp,
        ];
    }

    public static function potongStok(PesananNasiBox $pesanan)
    {
        $kebutuhan = [];
        $kekurangan = [];

        // 1. Kumpulkan kebutuhan bahan berdasarkan komponen menu yang dipilih
        $details = $pesanan->details()->with('menu.reseps')->get();

        foreach ($details as $detail) {
            $reseps = $detail->menu->reseps ?? [];
            foreach ($reseps as $resep) {
                $bahanId = $resep->bahan_baku_id;
                // Nasi Box -> setiap box mendapatkan 1 porsi dari masing-masing komponen
                $qty = $resep->jumlah_kebutuhan * $pesanan->jumlah_box;

                if (isset($kebutuhan[$bahanId])) {
                    $kebutuhan[$bahanId] += $qty;
                } else {
                    $kebutuhan[$bahanId] = $qty;
                }
            }
        }

        // 2. Bandingkan dengan stok saat ini
        foreach ($kebutuhan as $bahanId => $qty_butuh) {
            $bahan = BahanBaku::with('stok_bahan_baku')->find($bahanId);
            $stokSaatIni = $bahan?->stok_bahan_baku?->jumlah_stok ?? 0;

            if (! $bahan || $stokSaatIni < $qty_butuh) {
                $kekurangan[] = [
                    'nama_bahan' => $bahan?->nama_bahan ?? 'Unknown',
                    'stok_sekarang' => $stokSaatIni,
                    'kebutuhan' => $qty_butuh,
                    'kurang' => $qty_butuh - $stokSaatIni,
                    'satuan' => $bahan?->satuan?->nama_satuan ?? '-',
                ];
            }
        }

        // 3. Jika ada kekurangan, kembalikan array kekurangan
        if (! empty($kekurangan)) {
            return $kekurangan;
        }

        // 4. Jika cukup, potong stok dan catat mutasi
        DB::transaction(function () use ($kebutuhan, $pesanan) {
            foreach ($kebutuhan as $bahanId => $qty_butuh) {
                $bahan = BahanBaku::find($bahanId);
                if (! $bahan) {
                    continue;
                }

                $stok = StokBahanBaku::lockForUpdate()
                    ->firstOrCreate(
                        ['bahan_baku_id' => $bahanId],
                        ['jumlah_stok' => 0, 'terakhir_diperbarui' => now()]
                    );

                $stok->jumlah_stok -= $qty_butuh;
                $stok->terakhir_diperbarui = now();
                $stok->save();

                MutasiStok::create([
                    'bahan_baku_id' => $bahanId,
                    'jenis_mutasi_stok_id' => 2, // Keluar
                    'jumlah' => $qty_butuh,
                    'satuan_id' => $bahan->satuan_id,
                    'tanggal_mutasi' => now(),
                    'dibuat_oleh' => Auth::id() ?? 1,
                    'catatan' => "Potong stok otomatis saat DP diterima - Pesanan NasiBox: {$pesanan->kode_pesanan}",
                ]);
            }
        });

        return true;
    }
}
