<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BahanBaku;
use App\Models\StokBahan;

class BahanBakuDokumenSeeder extends Seeder
{
    public function run(): void
    {
        // Item dari docs/Data_Bahan_Baku_Saung_Babakan_Cinta.md yang belum ada di tabel bahan_baku.
        // idempoten: firstOrCreate berdasarkan nama_bahan, aman dijalankan ulang.
        $bahanRawData = [
            ['Daging Kambing', 7, 1, 130000],
            ['Dada Ayam Fillet', 7, 1, 40000],
            ['Susu Segar', 4, 3, 18000],
            ['Cabai Merah (giling)', 9, 1, 50000],
            ['Sayuran Hijau / Jukut', 8, 1, 10000],
            ['Tepung Bumbu Serbaguna', 6, 1, 30000],
            ['Merica / Lada Bubuk', 9, 1, 90000],
            ['Mi Kuning Basah', 6, 1, 15000],
            ['Box Nasi (kemasan)', 5, 10, 2500],
            ['Daging Campur / Jeroan', 7, 1, 80000],
        ];

        foreach ($bahanRawData as $b) {
            $nama = $b[0];
            $kategoriId = $b[1];
            $satuanId = $b[2];
            $harga = $b[3];

            $bahan = BahanBaku::firstOrCreate(
                ['nama_bahan' => $nama],
                [
                    'kode_bahan' => 'BHN-' . strtoupper(substr(md5($nama), 0, 5)),
                    'kategori_bahan_baku_id' => $kategoriId,
                    'satuan_id' => $satuanId,
                    'harga_satuan' => $harga,
                    'stok_minimal' => 10,
                    'jenis_peruntukan' => 'Semua',
                    'status_aktif' => 1,
                ]
            );

            StokBahan::firstOrCreate(
                ['bahan_baku_id' => $bahan->id, 'jenis_persediaan' => 'harian'],
                ['jumlah_stok' => 1000]
            );
            StokBahan::firstOrCreate(
                ['bahan_baku_id' => $bahan->id, 'jenis_persediaan' => 'catering'],
                ['jumlah_stok' => 1000]
            );
        }
    }
}
