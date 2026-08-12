<?php

namespace App\Services;

use App\Models\PengaturanTransaksi;
use App\Models\PengaturanPengiriman;
use App\Models\AturanPengiriman;

class KalkulasiPesananService
{
    /**
     * Hitung total tagihan (termasuk pajak dan biaya layanan) berdasarkan subtotal.
     * Mengambil nilai pengaturan transaksi saat ini.
     */
    public function hitungTotal(float $subtotal)
    {
        $pengaturan = PengaturanTransaksi::first();
        
        $pajakAktif = $pengaturan ? $pengaturan->pajak_aktif : false;
        $persentasePajak = $pajakAktif ? (float) $pengaturan->persentase_pajak : 0;
        
        $layananAktif = $pengaturan ? $pengaturan->layanan_aktif : false;
        $persentaseLayanan = $layananAktif ? (float) $pengaturan->persentase_layanan : 0;

        // Biaya Layanan = Subtotal × persentase biaya layanan
        $nominalLayanan = $subtotal * ($persentaseLayanan / 100);

        // Dasar Pajak = Subtotal + Biaya Layanan
        $dasarPajak = $subtotal + $nominalLayanan;

        // Pajak = Dasar Pajak × persentase pajak
        $nominalPajak = $dasarPajak * ($persentasePajak / 100);

        $totalTagihan = $subtotal + $nominalLayanan + $nominalPajak;

        return [
            'subtotal' => $subtotal,
            'persentase_pajak' => $persentasePajak,
            'nominal_pajak' => $nominalPajak,
            'persentase_biaya_layanan' => $persentaseLayanan,
            'nominal_biaya_layanan' => $nominalLayanan,
            'total_tagihan' => $totalTagihan,
        ];
    }

    /**
     * Hitung ongkos kirim berdasarkan porsi dan jarak.
     */
    public function hitungOngkir(int $totalPorsi, float $jarakKm)
    {
        $pengaturan = PengaturanPengiriman::first();
        $tarifPerKm = $pengaturan && $pengaturan->status_aktif ? (float) $pengaturan->tarif_per_km : 0;

        // Cari aturan yang cocok (jika ada)
        $aturan = AturanPengiriman::where('status_aktif', true)
            ->where('minimal_porsi', '<=', $totalPorsi)
            ->where(function ($q) use ($totalPorsi) {
                $q->whereNull('maksimal_porsi')
                  ->orWhere('maksimal_porsi', '>=', $totalPorsi);
            })
            ->first();

        $jarakGratis = $aturan ? (float) $aturan->kilometer_gratis : 0;
        
        $jarakBerbayar = max(0, $jarakKm - $jarakGratis);
        $biayaPengiriman = $jarakBerbayar * $tarifPerKm;

        return [
            'jarak_pengiriman' => $jarakKm,
            'jarak_gratis' => $jarakGratis,
            'jarak_berbayar' => $jarakBerbayar,
            'tarif_per_km' => $tarifPerKm,
            'biaya_pengiriman' => $biayaPengiriman,
        ];
    }
}
