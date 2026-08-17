<?php

namespace App\Services;

use App\Models\DetailPesanan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;

class NasiBoxCapacityService
{
    /**
     * Mendapatkan kapasitas harian maksimal dari konfigurasi.
     * 
     * @return int
     */
    public function getKapasitasHarian(): int
    {
        return config('pesanan.kapasitas_harian_nasi_box', 500);
    }

    /**
     * Menghitung total jumlah box yang sudah terpesan pada tanggal tertentu.
     * Mengabaikan pesanan yang dibatalkan (status_pesanan_id = 6).
     *
     * @param string|Carbon $tanggal
     * @return int
     */
    public function getTerpesanPadaTanggal($tanggal): int
    {
        $parsedDate = Carbon::parse($tanggal)->toDateString();

        return (int) DetailPesanan::whereHas('pesanan', function ($query) {
                // Hanya jenis pesanan Nasi Box (3) dan bukan Dibatalkan (6)
                $query->where('jenis_pesanan_id', 3)
                      ->where('status_pesanan_id', '!=', 6);
            })
            ->whereHas('pesanan.jadwal_pesanan', function ($query) use ($parsedDate) {
                // Cocokkan dengan tanggal acara (mengabaikan jam)
                $query->whereDate('tanggal_acara', $parsedDate);
            })
            ->sum('jumlah');
    }

    /**
     * Mendapatkan sisa kapasitas yang tersedia pada tanggal tertentu.
     *
     * @param string|Carbon $tanggal
     * @return int
     */
    public function getSisaKapasitas($tanggal): int
    {
        $kapasitasHarian = $this->getKapasitasHarian();
        $terpesan = $this->getTerpesanPadaTanggal($tanggal);

        return max(0, $kapasitasHarian - $terpesan);
    }

    /**
     * Memeriksa ketersediaan kapasitas dan memberikan respon format pesan validasi.
     *
     * @param string|Carbon $tanggal
     * @param int $jumlahDiminta
     * @return array
     */
    public function cekKetersediaan($tanggal, int $jumlahDiminta): array
    {
        $kapasitasHarian = $this->getKapasitasHarian();
        $terpesan = $this->getTerpesanPadaTanggal($tanggal);
        $sisa = max(0, $kapasitasHarian - $terpesan);
        
        $tanggalFormatted = Carbon::parse($tanggal)->translatedFormat('j F Y');

        if ($jumlahDiminta > $sisa) {
            $isTersedia = false;
            
            if ($sisa === 0) {
                $pesan = "Kapasitas produksi Nasi Box pada tanggal {$tanggalFormatted} tidak mencukupi. Sudah terpesan {$terpesan} box dari kapasitas {$kapasitasHarian} box. Silakan pilih tanggal lain.";
            } else {
                $pesan = "Kapasitas produksi Nasi Box pada tanggal {$tanggalFormatted} tersisa {$sisa} box. Jumlah pesanan yang Anda masukkan adalah {$jumlahDiminta} box. Silakan kurangi jumlah pesanan atau pilih tanggal lain.";
            }
        } else {
            $isTersedia = true;
            $pesan = "Kapasitas mencukupi.";
        }

        return [
            'tersedia' => $isTersedia,
            'kapasitas_harian' => $kapasitasHarian,
            'terpesan' => $terpesan,
            'sisa' => $sisa,
            'pesan' => $pesan,
        ];
    }
}
