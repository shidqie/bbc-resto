<?php

namespace App\Services;

use App\Models\BahanBaku;
use App\Models\LayananTambahan;
use App\Models\Menu;
use App\Models\MutasiStok;
use App\Models\PesananCatering;
use App\Models\StokBahanBaku;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PesananCateringService
{
    public static function hitungOngkir($jumlahPorsi, $jarakKm, $metodePengiriman)
    {
        if ($metodePengiriman === 'pickup' || $jarakKm === null) {
            return 0;
        }

        if ($jumlahPorsi < 50) {
            throw new \Exception('Delivery tidak tersedia, minimum 50 porsi.');
        }
        if ($jarakKm > 30) {
            throw new \Exception('Di luar area layanan (maks 30 km). Harap hubungi admin.');
        }

        $zona = '';
        $tarifZona = 0;

        if ($jarakKm <= 10) {
            $zona = '0-10';
            $tarifZona = 50000;
        } elseif ($jarakKm <= 20) {
            $zona = '10-20';
            $tarifZona = 100000;
        } else {
            $zona = '20-30';
            $tarifZona = 150000;
        }

        $zonaGratis = [];
        if ($jumlahPorsi >= 100 && $jumlahPorsi < 200) {
            $zonaGratis = ['0-10'];
        } elseif ($jumlahPorsi >= 200) {
            $zonaGratis = ['0-10', '10-20'];
        }

        if (in_array($zona, $zonaGratis)) {
            $ongkir = 0;
        } else {
            $ongkir = $tarifZona;
        }

        return $ongkir;
    }

    public static function hitungTotal($paketId, $jumlahPorsi, $layananTambahanIds = [], $ongkir = 0)
    {
        $paket = Menu::findOrFail($paketId);
        $subtotalMenu = $paket->harga_jual * $jumlahPorsi;

        $subtotalAddon = 0;
        if (! empty($layananTambahanIds)) {
            $subtotalAddon = LayananTambahan::whereIn('id', $layananTambahanIds)->sum('harga');
        }

        $total = $subtotalMenu + $subtotalAddon + $ongkir;
        $dp = ceil($total * 0.5); // 50% DP

        return [
            'subtotal_menu' => $subtotalMenu,
            'subtotal_addon' => $subtotalAddon,
            'ongkir' => $ongkir,
            'total' => $total,
            'dp' => $dp,
        ];
    }

    public static function potongStok($pesanan)
    {
        $kebutuhan = [];
        $kekurangan = [];

        // Cari relasi yang benar (bisa PesananCatering atau Pesanan)
        $pilihanMenus = method_exists($pesanan, 'pilihanKomponenPaket')
            ? $pesanan->pilihanKomponenPaket()->with('menu.resep_menu.bahan_baku')->get()
            : collect();

        // Juga dari detail_pesanan jika ada
        if (method_exists($pesanan, 'detail_pesanan')) {
            $pesanan->loadMissing('detail_pesanan.menu.resep_menu.bahan_baku');
            foreach ($pesanan->detail_pesanan as $detail) {
                if ($detail->menu && $detail->menu->resep_menu) {
                    foreach ($detail->menu->resep_menu as $resep) {
                        $bahanId = $resep->bahan_baku_id;
                        $qty = $resep->jumlah_kebutuhan * $detail->jumlah;
                        $kebutuhan[$bahanId] = ($kebutuhan[$bahanId] ?? 0) + $qty;
                    }
                }
            }
        }

        // Dari pilihan komponen paket (format lama PesananCatering)
        foreach ($pilihanMenus as $pilihan) {
            if ($pilihan->menu && $pilihan->menu->resep_menu) {
                $jumlahPorsi = $pesanan->jumlah_porsi ?? 1;
                foreach ($pilihan->menu->resep_menu as $resep) {
                    $bahanId = $resep->bahan_baku_id;
                    $qty = $resep->jumlah_kebutuhan * $jumlahPorsi;
                    $kebutuhan[$bahanId] = ($kebutuhan[$bahanId] ?? 0) + $qty;
                }
            }
        }

        if (empty($kebutuhan)) {
            return true;
        } // Tidak ada resep, skip

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

        // 3. Jika ada kekurangan, kembalikan array kekurangan (stok tidak cukup)
        if (! empty($kekurangan)) {
            return $kekurangan;
        }

        // 4. Jika stok cukup, potong stok dan catat mutasi
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
                    'catatan' => "Potong stok otomatis saat DP diterima - Pesanan: {$pesanan->kode_pesanan}",
                ]);
            }
        });

        return true;
    }
}
