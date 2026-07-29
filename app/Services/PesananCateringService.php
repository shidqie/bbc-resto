<?php

namespace App\Services;

use App\Models\PaketCatering;
use App\Models\LayananTambahan;
use App\Models\PesananCatering;
use App\Models\ResepMenu;
use App\Models\BahanBaku;
use App\Models\MutasiStok;

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
        $paket = PaketCatering::findOrFail($paketId);
        $subtotalMenu = $paket->harga * $jumlahPorsi;
        
        $subtotalAddon = 0;
        if (!empty($layananTambahanIds)) {
            $subtotalAddon = LayananTambahan::whereIn('id', $layananTambahanIds)->sum('harga');
        }
        
        $total = $subtotalMenu + $subtotalAddon + $ongkir;
        $dp = ceil($total * 0.5); // 50% DP
        
        return [
            'subtotal_menu' => $subtotalMenu,
            'subtotal_addon' => $subtotalAddon,
            'ongkir' => $ongkir,
            'total' => $total,
            'dp' => $dp
        ];
    }

    public static function potongStok(PesananCatering $pesanan)
    {
        $kebutuhan = [];
        $kekurangan = [];
        
        // 1. Kumpulkan kebutuhan bahan berdasarkan BOM tiap komponen menu yang dipilih
        foreach ($pesanan->details as $detail) {
            $menuId = $detail->menu_id_terpilih;
            $reseps = ResepMenu::where('menu_id', $menuId)->get();
            
            foreach ($reseps as $resep) {
                $bahanId = $resep->bahan_baku_id;
                $qty = $resep->jumlah_kebutuhan * $pesanan->jumlah_porsi;
                
                if (isset($kebutuhan[$bahanId])) {
                    $kebutuhan[$bahanId] += $qty;
                } else {
                    $kebutuhan[$bahanId] = $qty;
                }
            }
        }
        
        // 2. Bandingkan dengan stok saat ini
        foreach ($kebutuhan as $bahanId => $qty_butuh) {
            $bahan = BahanBaku::find($bahanId);
            if (!$bahan || $bahan->stok < $qty_butuh) {
                $kekurangan[] = [
                    'nama_bahan' => $bahan ? $bahan->nama_bahan : 'Unknown',
                    'stok_sekarang' => $bahan ? $bahan->stok : 0,
                    'kebutuhan' => $qty_butuh,
                    'kurang' => $qty_butuh - ($bahan ? $bahan->stok : 0),
                    'satuan' => $bahan ? $bahan->satuan->nama_satuan : '-'
                ];
            }
        }
        
        // 3. Jika ada kekurangan, kembalikan array kekurangan
        if (!empty($kekurangan)) {
            return $kekurangan;
        }
        
        // 4. Jika cukup, potong stok dan catat mutasi
        foreach ($kebutuhan as $bahanId => $qty_butuh) {
            $bahan = BahanBaku::find($bahanId);
            
            MutasiStok::create([
                'bahan_baku_id' => $bahanId,
                'user_id'       => auth()->id(),
                'jenis_mutasi'  => 'keluar',
                'jumlah'        => $qty_butuh,
                'sisa_stok'     => $bahan->stok - $qty_butuh,
                'keterangan'    => "Potong stok untuk Pesanan Catering: {$pesanan->kode_pesanan}",
            ]);
            
            $bahan->stok -= $qty_butuh;
            $bahan->save();
        }
        
        return true;
    }
}
