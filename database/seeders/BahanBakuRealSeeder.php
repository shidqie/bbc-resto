<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BahanBakuRealSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // 1. Tambahkan satuan yang belum ada (jangan truncate, aman dijalankan ulang)
        $satuanBaru = [
            ['id' => 8, 'nama_satuan' => 'Ikat', 'singkatan' => 'ikat'],
            ['id' => 9, 'nama_satuan' => 'Botol', 'singkatan' => 'botol'],
            ['id' => 10, 'nama_satuan' => 'Pcs', 'singkatan' => 'pcs'],
            ['id' => 11, 'nama_satuan' => 'Bungkus', 'singkatan' => 'bks'],
            ['id' => 12, 'nama_satuan' => 'Kardus', 'singkatan' => 'kardus'],
            ['id' => 13, 'nama_satuan' => 'Lembar', 'singkatan' => 'lbr'],
            ['id' => 14, 'nama_satuan' => 'Sachet', 'singkatan' => 'sct'],
        ];
        foreach ($satuanBaru as $s) {
            DB::table('satuan')->updateOrInsert(['id' => $s['id']], $s);
        }

        // 2. Data Bahan Baku Realistis (Restoran Sunda)
        // satuan_id: 1=kg, 2=g, 3=liter, 4=ml, 5=buah, 8=ikat, 9=botol, 10=pcs, 11=bks, 13=lbr, 14=sct
        // harga_satuan = harga terakhir per satuan dasar (gram / ml / buah / dll)
        $bahan = [
            // ===== Bahan Pokok (kategori 1) =====
            ['id' => 1, 'kategori_bahan_baku_id' => 1, 'satuan_id' => 2, 'kode_bahan' => 'BB001', 'nama_bahan' => 'Beras', 'stok_minimal' => 10000, 'harga_satuan' => 12.00, 'stok' => 69750, 'peruntukan' => 'Semua'],
            ['id' => 11, 'kategori_bahan_baku_id' => 1, 'satuan_id' => 2, 'kode_bahan' => 'BB011', 'nama_bahan' => 'Beras Ketan Putih', 'stok_minimal' => 5000, 'harga_satuan' => 18.00, 'stok' => 12400, 'peruntukan' => 'Semua'],
            ['id' => 12, 'kategori_bahan_baku_id' => 1, 'satuan_id' => 3, 'kode_bahan' => 'BB012', 'nama_bahan' => 'Minyak Goreng', 'stok_minimal' => 15, 'harga_satuan' => 18000.00, 'stok' => 42.5, 'peruntukan' => 'Semua'],
            ['id' => 13, 'kategori_bahan_baku_id' => 1, 'satuan_id' => 2, 'kode_bahan' => 'BB013', 'nama_bahan' => 'Tepung Terigu', 'stok_minimal' => 5000, 'harga_satuan' => 12.00, 'stok' => 18250, 'peruntukan' => 'Semua'],
            ['id' => 14, 'kategori_bahan_baku_id' => 1, 'satuan_id' => 2, 'kode_bahan' => 'BB014', 'nama_bahan' => 'Tepung Beras', 'stok_minimal' => 3000, 'harga_satuan' => 14.00, 'stok' => 8750, 'peruntukan' => 'Semua'],
            ['id' => 24, 'kategori_bahan_baku_id' => 1, 'satuan_id' => 4, 'kode_bahan' => 'BB024', 'nama_bahan' => 'Santan Kelapa Instan', 'stok_minimal' => 6000, 'harga_satuan' => 40.00, 'stok' => 7200, 'peruntukan' => 'Semua'],
            ['id' => 9, 'kategori_bahan_baku_id' => 1, 'satuan_id' => 2, 'kode_bahan' => 'BB009', 'nama_bahan' => 'Gula Pasir', 'stok_minimal' => 3000, 'harga_satuan' => 15.00, 'stok' => 15400, 'peruntukan' => 'Semua'],

            // ===== Protein (kategori 2) =====
            ['id' => 2, 'kategori_bahan_baku_id' => 2, 'satuan_id' => 2, 'kode_bahan' => 'BB002', 'nama_bahan' => 'Ayam Broiler', 'stok_minimal' => 8000, 'harga_satuan' => 45.00, 'stok' => 9760, 'peruntukan' => 'Semua'],
            ['id' => 19, 'kategori_bahan_baku_id' => 2, 'satuan_id' => 2, 'kode_bahan' => 'BB019', 'nama_bahan' => 'Ayam Kampung', 'stok_minimal' => 5000, 'harga_satuan' => 55.00, 'stok' => 6400, 'peruntukan' => 'Semua'],
            ['id' => 18, 'kategori_bahan_baku_id' => 2, 'satuan_id' => 2, 'kode_bahan' => 'BB018', 'nama_bahan' => 'Daging Bebek', 'stok_minimal' => 8000, 'harga_satuan' => 75.00, 'stok' => 14200, 'peruntukan' => 'Semua'],
            ['id' => 3, 'kategori_bahan_baku_id' => 2, 'satuan_id' => 2, 'kode_bahan' => 'BB003', 'nama_bahan' => 'Ikan Gurame', 'stok_minimal' => 5000, 'harga_satuan' => 60.00, 'stok' => 15000, 'peruntukan' => 'Semua'],
            ['id' => 4, 'kategori_bahan_baku_id' => 2, 'satuan_id' => 2, 'kode_bahan' => 'BB004', 'nama_bahan' => 'Iga Sapi', 'stok_minimal' => 5000, 'harga_satuan' => 120.00, 'stok' => 12350, 'peruntukan' => 'Semua'],
            ['id' => 20, 'kategori_bahan_baku_id' => 2, 'satuan_id' => 2, 'kode_bahan' => 'BB020', 'nama_bahan' => 'Daging Sapi', 'stok_minimal' => 5000, 'harga_satuan' => 130.00, 'stok' => 8650, 'peruntukan' => 'Semua'],
            ['id' => 21, 'kategori_bahan_baku_id' => 2, 'satuan_id' => 2, 'kode_bahan' => 'BB021', 'nama_bahan' => 'Udang Segar', 'stok_minimal' => 3000, 'harga_satuan' => 95.00, 'stok' => 5200, 'peruntukan' => 'Reguler'],
            ['id' => 22, 'kategori_bahan_baku_id' => 2, 'satuan_id' => 2, 'kode_bahan' => 'BB022', 'nama_bahan' => 'Ikan Teri Medan', 'stok_minimal' => 2000, 'harga_satuan' => 90.00, 'stok' => 3800, 'peruntukan' => 'Semua'],
            ['id' => 23, 'kategori_bahan_baku_id' => 2, 'satuan_id' => 2, 'kode_bahan' => 'BB023', 'nama_bahan' => 'Ceker Ayam', 'stok_minimal' => 2000, 'harga_satuan' => 25.00, 'stok' => 4600, 'peruntukan' => 'Reguler'],
            ['id' => 15, 'kategori_bahan_baku_id' => 2, 'satuan_id' => 5, 'kode_bahan' => 'BB015', 'nama_bahan' => 'Telur Ayam', 'stok_minimal' => 120, 'harga_satuan' => 2800.00, 'stok' => 240, 'peruntukan' => 'Semua'],
            ['id' => 16, 'kategori_bahan_baku_id' => 2, 'satuan_id' => 5, 'kode_bahan' => 'BB016', 'nama_bahan' => 'Tahu Putih', 'stok_minimal' => 60, 'harga_satuan' => 1500.00, 'stok' => 140, 'peruntukan' => 'Semua'],
            ['id' => 17, 'kategori_bahan_baku_id' => 2, 'satuan_id' => 5, 'kode_bahan' => 'BB017', 'nama_bahan' => 'Tempe', 'stok_minimal' => 60, 'harga_satuan' => 2000.00, 'stok' => 90, 'peruntukan' => 'Semua'],

            // ===== Bumbu (kategori 3) =====
            ['id' => 5, 'kategori_bahan_baku_id' => 3, 'satuan_id' => 2, 'kode_bahan' => 'BB005', 'nama_bahan' => 'Bawang Merah', 'stok_minimal' => 2000, 'harga_satuan' => 30.00, 'stok' => 8200, 'peruntukan' => 'Semua'],
            ['id' => 6, 'kategori_bahan_baku_id' => 3, 'satuan_id' => 2, 'kode_bahan' => 'BB006', 'nama_bahan' => 'Bawang Putih', 'stok_minimal' => 2000, 'harga_satuan' => 40.00, 'stok' => 7100, 'peruntukan' => 'Semua'],
            ['id' => 7, 'kategori_bahan_baku_id' => 3, 'satuan_id' => 2, 'kode_bahan' => 'BB007', 'nama_bahan' => 'Cabai Merah Keriting', 'stok_minimal' => 2000, 'harga_satuan' => 60.00, 'stok' => 1700, 'peruntukan' => 'Semua'],
            ['id' => 26, 'kategori_bahan_baku_id' => 3, 'satuan_id' => 2, 'kode_bahan' => 'BB026', 'nama_bahan' => 'Cabai Rawit', 'stok_minimal' => 1500, 'harga_satuan' => 70.00, 'stok' => 2100, 'peruntukan' => 'Semua'],
            ['id' => 27, 'kategori_bahan_baku_id' => 3, 'satuan_id' => 2, 'kode_bahan' => 'BB027', 'nama_bahan' => 'Tomat', 'stok_minimal' => 3000, 'harga_satuan' => 20.00, 'stok' => 9500, 'peruntukan' => 'Semua'],
            ['id' => 28, 'kategori_bahan_baku_id' => 3, 'satuan_id' => 2, 'kode_bahan' => 'BB028', 'nama_bahan' => 'Jahe', 'stok_minimal' => 1500, 'harga_satuan' => 28.00, 'stok' => 4000, 'peruntukan' => 'Semua'],
            ['id' => 29, 'kategori_bahan_baku_id' => 3, 'satuan_id' => 2, 'kode_bahan' => 'BB029', 'nama_bahan' => 'Lengkuas', 'stok_minimal' => 1000, 'harga_satuan' => 20.00, 'stok' => 3500, 'peruntukan' => 'Semua'],
            ['id' => 30, 'kategori_bahan_baku_id' => 3, 'satuan_id' => 2, 'kode_bahan' => 'BB030', 'nama_bahan' => 'Kunyit', 'stok_minimal' => 1500, 'harga_satuan' => 15.00, 'stok' => 6100, 'peruntukan' => 'Semua'],
            ['id' => 31, 'kategori_bahan_baku_id' => 3, 'satuan_id' => 2, 'kode_bahan' => 'BB031', 'nama_bahan' => 'Kencur', 'stok_minimal' => 500, 'harga_satuan' => 30.00, 'stok' => 1500, 'peruntukan' => 'Semua'],
            ['id' => 32, 'kategori_bahan_baku_id' => 3, 'satuan_id' => 2, 'kode_bahan' => 'BB032', 'nama_bahan' => 'Serai', 'stok_minimal' => 1000, 'harga_satuan' => 8.00, 'stok' => 2600, 'peruntukan' => 'Semua'],
            ['id' => 33, 'kategori_bahan_baku_id' => 3, 'satuan_id' => 2, 'kode_bahan' => 'BB033', 'nama_bahan' => 'Daun Salam', 'stok_minimal' => 500, 'harga_satuan' => 6.00, 'stok' => 900, 'peruntukan' => 'Semua'],
            ['id' => 34, 'kategori_bahan_baku_id' => 3, 'satuan_id' => 2, 'kode_bahan' => 'BB034', 'nama_bahan' => 'Daun Jeruk', 'stok_minimal' => 500, 'harga_satuan' => 7.00, 'stok' => 750, 'peruntukan' => 'Semua'],
            ['id' => 35, 'kategori_bahan_baku_id' => 3, 'satuan_id' => 8, 'kode_bahan' => 'BB035', 'nama_bahan' => 'Kemangi', 'stok_minimal' => 10, 'harga_satuan' => 4000.00, 'stok' => 24, 'peruntukan' => 'Semua'],
            ['id' => 36, 'kategori_bahan_baku_id' => 3, 'satuan_id' => 9, 'kode_bahan' => 'BB036', 'nama_bahan' => 'Kecap Manis', 'stok_minimal' => 10, 'harga_satuan' => 22000.00, 'stok' => 18, 'peruntukan' => 'Semua'],
            ['id' => 37, 'kategori_bahan_baku_id' => 3, 'satuan_id' => 9, 'kode_bahan' => 'BB037', 'nama_bahan' => 'Kecap Asin', 'stok_minimal' => 6, 'harga_satuan' => 18000.00, 'stok' => 9, 'peruntukan' => 'Semua'],
            ['id' => 38, 'kategori_bahan_baku_id' => 3, 'satuan_id' => 9, 'kode_bahan' => 'BB038', 'nama_bahan' => 'Saus Sambal', 'stok_minimal' => 8, 'harga_satuan' => 21000.00, 'stok' => 12, 'peruntukan' => 'Semua'],
            ['id' => 39, 'kategori_bahan_baku_id' => 3, 'satuan_id' => 9, 'kode_bahan' => 'BB039', 'nama_bahan' => 'Saus Tomat', 'stok_minimal' => 5, 'harga_satuan' => 19000.00, 'stok' => 7, 'peruntukan' => 'Semua'],
            ['id' => 40, 'kategori_bahan_baku_id' => 3, 'satuan_id' => 11, 'kode_bahan' => 'BB040', 'nama_bahan' => 'Terasi Udang', 'stok_minimal' => 10, 'harga_satuan' => 8000.00, 'stok' => 15, 'peruntukan' => 'Semua'],
            ['id' => 41, 'kategori_bahan_baku_id' => 3, 'satuan_id' => 2, 'kode_bahan' => 'BB041', 'nama_bahan' => 'Garam Dapur', 'stok_minimal' => 5000, 'harga_satuan' => 5.00, 'stok' => 12800, 'peruntukan' => 'Semua'],
            ['id' => 42, 'kategori_bahan_baku_id' => 3, 'satuan_id' => 2, 'kode_bahan' => 'BB042', 'nama_bahan' => 'Merica Bubuk', 'stok_minimal' => 500, 'harga_satuan' => 30.00, 'stok' => 1850, 'peruntukan' => 'Semua'],
            ['id' => 43, 'kategori_bahan_baku_id' => 3, 'satuan_id' => 2, 'kode_bahan' => 'BB043', 'nama_bahan' => 'Ketumbar', 'stok_minimal' => 500, 'harga_satuan' => 15.00, 'stok' => 2200, 'peruntukan' => 'Semua'],
            ['id' => 44, 'kategori_bahan_baku_id' => 3, 'satuan_id' => 14, 'kode_bahan' => 'BB044', 'nama_bahan' => 'Penyedap Rasa', 'stok_minimal' => 60, 'harga_satuan' => 500.00, 'stok' => 200, 'peruntukan' => 'Semua'],
            ['id' => 45, 'kategori_bahan_baku_id' => 3, 'satuan_id' => 2, 'kode_bahan' => 'BB045', 'nama_bahan' => 'Gula Merah', 'stok_minimal' => 2000, 'harga_satuan' => 25.00, 'stok' => 4600, 'peruntukan' => 'Semua'],

            // ===== Minuman (kategori 4) =====
            ['id' => 8, 'kategori_bahan_baku_id' => 4, 'satuan_id' => 5, 'kode_bahan' => 'BB008', 'nama_bahan' => 'Teh Celup', 'stok_minimal' => 500, 'harga_satuan' => 10.00, 'stok' => 3200, 'peruntukan' => 'Reguler'],
            ['id' => 46, 'kategori_bahan_baku_id' => 4, 'satuan_id' => 2, 'kode_bahan' => 'BB046', 'nama_bahan' => 'Kopi Bubuk', 'stok_minimal' => 3000, 'harga_satuan' => 90.00, 'stok' => 5250, 'peruntukan' => 'Reguler'],
            ['id' => 47, 'kategori_bahan_baku_id' => 4, 'satuan_id' => 2, 'kode_bahan' => 'BB047', 'nama_bahan' => 'Biji Kopi Arabika', 'stok_minimal' => 5000, 'harga_satuan' => 130.00, 'stok' => 8400, 'peruntukan' => 'Reguler'],
            ['id' => 48, 'kategori_bahan_baku_id' => 4, 'satuan_id' => 4, 'kode_bahan' => 'BB048', 'nama_bahan' => 'Susu UHT', 'stok_minimal' => 20000, 'harga_satuan' => 20.00, 'stok' => 36000, 'peruntukan' => 'Reguler'],
            ['id' => 49, 'kategori_bahan_baku_id' => 4, 'satuan_id' => 3, 'kode_bahan' => 'BB049', 'nama_bahan' => 'Sirup Gula Aren', 'stok_minimal' => 8, 'harga_satuan' => 32000.00, 'stok' => 14, 'peruntukan' => 'Reguler'],
            ['id' => 25, 'kategori_bahan_baku_id' => 4, 'satuan_id' => 3, 'kode_bahan' => 'BB025', 'nama_bahan' => 'Air Mineral', 'stok_minimal' => 30, 'harga_satuan' => 5000.00, 'stok' => 55, 'peruntukan' => 'Semua'],
            ['id' => 50, 'kategori_bahan_baku_id' => 4, 'satuan_id' => 5, 'kode_bahan' => 'BB050', 'nama_bahan' => 'Jeruk Nipis', 'stok_minimal' => 30, 'harga_satuan' => 3000.00, 'stok' => 60, 'peruntukan' => 'Semua'],
            ['id' => 51, 'kategori_bahan_baku_id' => 4, 'satuan_id' => 5, 'kode_bahan' => 'BB051', 'nama_bahan' => 'Alpukat Mentega', 'stok_minimal' => 20, 'harga_satuan' => 20000.00, 'stok' => 25, 'peruntukan' => 'Reguler'],
            ['id' => 52, 'kategori_bahan_baku_id' => 4, 'satuan_id' => 5, 'kode_bahan' => 'BB052', 'nama_bahan' => 'Mangga Harum Manis', 'stok_minimal' => 15, 'harga_satuan' => 15000.00, 'stok' => 18, 'peruntukan' => 'Reguler'],
            ['id' => 53, 'kategori_bahan_baku_id' => 4, 'satuan_id' => 2, 'kode_bahan' => 'BB053', 'nama_bahan' => 'Stroberi', 'stok_minimal' => 2000, 'harga_satuan' => 60.00, 'stok' => 3500, 'peruntukan' => 'Reguler'],
            ['id' => 54, 'kategori_bahan_baku_id' => 4, 'satuan_id' => 5, 'kode_bahan' => 'BB054', 'nama_bahan' => 'Jeruk Peras', 'stok_minimal' => 20, 'harga_satuan' => 12000.00, 'stok' => 28, 'peruntukan' => 'Reguler'],

            // ===== Kemasan (kategori 5) =====
            ['id' => 10, 'kategori_bahan_baku_id' => 5, 'satuan_id' => 5, 'kode_bahan' => 'BB010', 'nama_bahan' => 'Kotak Nasi', 'stok_minimal' => 50, 'harga_satuan' => 2500.00, 'stok' => 40, 'peruntukan' => 'Catering'],
            ['id' => 55, 'kategori_bahan_baku_id' => 5, 'satuan_id' => 10, 'kode_bahan' => 'BB055', 'nama_bahan' => 'Kotak Catering Mika', 'stok_minimal' => 80, 'harga_satuan' => 3500.00, 'stok' => 150, 'peruntukan' => 'Catering'],
            ['id' => 56, 'kategori_bahan_baku_id' => 5, 'satuan_id' => 10, 'kode_bahan' => 'BB056', 'nama_bahan' => 'Sendok Plastik', 'stok_minimal' => 500, 'harga_satuan' => 300.00, 'stok' => 1200, 'peruntukan' => 'Catering'],
            ['id' => 57, 'kategori_bahan_baku_id' => 5, 'satuan_id' => 10, 'kode_bahan' => 'BB057', 'nama_bahan' => 'Tusuk Sate', 'stok_minimal' => 200, 'harga_satuan' => 100.00, 'stok' => 320, 'peruntukan' => 'Reguler'],
            ['id' => 58, 'kategori_bahan_baku_id' => 5, 'satuan_id' => 13, 'kode_bahan' => 'BB058', 'nama_bahan' => 'Daun Pisang', 'stok_minimal' => 200, 'harga_satuan' => 500.00, 'stok' => 450, 'peruntukan' => 'Semua'],
            ['id' => 59, 'kategori_bahan_baku_id' => 5, 'satuan_id' => 10, 'kode_bahan' => 'BB059', 'nama_bahan' => 'Kertas Minyak', 'stok_minimal' => 300, 'harga_satuan' => 250.00, 'stok' => 800, 'peruntukan' => 'Reguler'],
            ['id' => 60, 'kategori_bahan_baku_id' => 5, 'satuan_id' => 10, 'kode_bahan' => 'BB060', 'nama_bahan' => 'Plastik Kemasan', 'stok_minimal' => 300, 'harga_satuan' => 200.00, 'stok' => 500, 'peruntukan' => 'Semua'],
        ];

        foreach ($bahan as $item) {
            DB::table('bahan_baku')->updateOrInsert(
                ['id' => $item['id']],
                [
                    'id' => $item['id'],
                    'kategori_bahan_baku_id' => $item['kategori_bahan_baku_id'],
                    'satuan_id' => $item['satuan_id'],
                    'kode_bahan' => $item['kode_bahan'],
                    'nama_bahan' => $item['nama_bahan'],
                    'stok_minimal' => $item['stok_minimal'],
                    'harga_satuan' => $item['harga_satuan'],
                    'jenis_peruntukan' => $item['peruntukan'],
                    'status_aktif' => true,
                ]
            );

            if (DB::getSchemaBuilder()->hasTable('stok_bahan_baku')) {
                $stokRecord = ['jumlah_stok' => $item['stok']];
                $stokTimeCol = DB::getSchemaBuilder()->hasColumn('stok_bahan_baku', 'terakhir_diperbarui') ? 'terakhir_diperbarui' : 'diperbarui_pada';
                if ($stokTimeCol) {
                    $stokRecord[$stokTimeCol] = now();
                }
                DB::table('stok_bahan_baku')->updateOrInsert(
                    ['bahan_baku_id' => $item['id']],
                    $stokRecord
                );
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('Seeder Bahan Baku Realistis selesai: '.count($bahan).' bahan baku.');
    }
}
