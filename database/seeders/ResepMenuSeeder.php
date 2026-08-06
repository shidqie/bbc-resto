<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ResepMenuSeeder extends Seeder
{
    public function run()
    {
        // Hapus resep yang sudah ada
        DB::table('resep_menu')->truncate();
        
        $this->seedNasiBoxResep();
        $this->seedDineInResep();
        $this->seedCateringResep();
    }
    
    private function seedNasiBoxResep()
    {
        // Resep untuk Nasi Box Paket A
        $paketA = DB::table('menu')->where('kode_menu', 'NB-A')->first();
        if ($paketA) {
            $this->insertResep($paketA->id, [
                ['bahan' => 'Beras', 'jumlah' => 150, 'satuan' => 'g'],
                ['bahan' => 'Ayam Broiler', 'jumlah' => 100, 'satuan' => 'g'],
                ['bahan' => 'Ikan Gurame', 'jumlah' => 80, 'satuan' => 'g'],
                ['bahan' => 'Telur Ayam', 'jumlah' => 0.5, 'satuan' => 'buah'],
                ['bahan' => 'Minyak Goreng', 'jumlah' => 50, 'satuan' => 'ml'],
                ['bahan' => 'Bawang Merah', 'jumlah' => 25, 'satuan' => 'g'],
                ['bahan' => 'Bawang Putih', 'jumlah' => 15, 'satuan' => 'g'],
                ['bahan' => 'Cabai Merah Keriting', 'jumlah' => 20, 'satuan' => 'g'],
                ['bahan' => 'Tomat', 'jumlah' => 30, 'satuan' => 'g'],
                ['bahan' => 'Garam Dapur', 'jumlah' => 5, 'satuan' => 'g'],
                ['bahan' => 'Kemangi', 'jumlah' => 0.1, 'satuan' => 'ikat'],
                ['bahan' => 'Kotak Catering Mika', 'jumlah' => 1, 'satuan' => 'pcs'],
            ]);
        }
        
        // Resep untuk Nasi Box Paket B
        $paketB = DB::table('menu')->where('kode_menu', 'NB-B')->first();
        if ($paketB) {
            $this->insertResep($paketB->id, [
                ['bahan' => 'Beras', 'jumlah' => 150, 'satuan' => 'g'],
                ['bahan' => 'Ayam Broiler', 'jumlah' => 100, 'satuan' => 'g'],
                ['bahan' => 'Tempe', 'jumlah' => 0.5, 'satuan' => 'buah'],
                ['bahan' => 'Tahu Putih', 'jumlah' => 0.5, 'satuan' => 'buah'],
                ['bahan' => 'Minyak Goreng', 'jumlah' => 40, 'satuan' => 'ml'],
                ['bahan' => 'Bawang Merah', 'jumlah' => 20, 'satuan' => 'g'],
                ['bahan' => 'Bawang Putih', 'jumlah' => 10, 'satuan' => 'g'],
                ['bahan' => 'Cabai Merah Keriting', 'jumlah' => 15, 'satuan' => 'g'],
                ['bahan' => 'Tomat', 'jumlah' => 25, 'satuan' => 'g'],
                ['bahan' => 'Garam Dapur', 'jumlah' => 4, 'satuan' => 'g'],
                ['bahan' => 'Kemangi', 'jumlah' => 0.1, 'satuan' => 'ikat'],
                ['bahan' => 'Kotak Catering Mika', 'jumlah' => 1, 'satuan' => 'pcs'],
            ]);
        }
        
        // Resep untuk Nasi Box Paket C
        $paketC = DB::table('menu')->where('kode_menu', 'NB-C')->first();
        if ($paketC) {
            $this->insertResep($paketC->id, [
                ['bahan' => 'Beras', 'jumlah' => 150, 'satuan' => 'g'],
                ['bahan' => 'Ayam Broiler', 'jumlah' => 80, 'satuan' => 'g'],
                ['bahan' => 'Tempe', 'jumlah' => 0.3, 'satuan' => 'buah'],
                ['bahan' => 'Minyak Goreng', 'jumlah' => 35, 'satuan' => 'ml'],
                ['bahan' => 'Bawang Merah', 'jumlah' => 15, 'satuan' => 'g'],
                ['bahan' => 'Bawang Putih', 'jumlah' => 8, 'satuan' => 'g'],
                ['bahan' => 'Cabai Merah Keriting', 'jumlah' => 10, 'satuan' => 'g'],
                ['bahan' => 'Tomat', 'jumlah' => 20, 'satuan' => 'g'],
                ['bahan' => 'Garam Dapur', 'jumlah' => 3, 'satuan' => 'g'],
                ['bahan' => 'Kemangi', 'jumlah' => 0.08, 'satuan' => 'ikat'],
                ['bahan' => 'Kotak Catering Mika', 'jumlah' => 1, 'satuan' => 'pcs'],
            ]);
        }
        
        // Resep untuk Nasi Box Paket D
        $paketD = DB::table('menu')->where('kode_menu', 'NB-D')->first();
        if ($paketD) {
            $this->insertResep($paketD->id, [
                ['bahan' => 'Beras', 'jumlah' => 150, 'satuan' => 'g'],
                ['bahan' => 'Ayam Broiler', 'jumlah' => 70, 'satuan' => 'g'],
                ['bahan' => 'Tempe', 'jumlah' => 0.25, 'satuan' => 'buah'],
                ['bahan' => 'Minyak Goreng', 'jumlah' => 30, 'satuan' => 'ml'],
                ['bahan' => 'Bawang Merah', 'jumlah' => 12, 'satuan' => 'g'],
                ['bahan' => 'Bawang Putih', 'jumlah' => 6, 'satuan' => 'g'],
                ['bahan' => 'Cabai Merah Keriting', 'jumlah' => 8, 'satuan' => 'g'],
                ['bahan' => 'Garam Dapur', 'jumlah' => 2.5, 'satuan' => 'g'],
                ['bahan' => 'Kemangi', 'jumlah' => 0.05, 'satuan' => 'ikat'],
                ['bahan' => 'Kotak Catering Mika', 'jumlah' => 1, 'satuan' => 'pcs'],
            ]);
        }
        
        // Resep untuk Nasi Box Paket E
        $paketE = DB::table('menu')->where('kode_menu', 'NB-E')->first();
        if ($paketE) {
            $this->insertResep($paketE->id, [
                ['bahan' => 'Beras', 'jumlah' => 150, 'satuan' => 'g'],
                ['bahan' => 'Ayam Broiler', 'jumlah' => 60, 'satuan' => 'g'],
                ['bahan' => 'Minyak Goreng', 'jumlah' => 25, 'satuan' => 'ml'],
                ['bahan' => 'Bawang Merah', 'jumlah' => 10, 'satuan' => 'g'],
                ['bahan' => 'Bawang Putih', 'jumlah' => 5, 'satuan' => 'g'],
                ['bahan' => 'Garam Dapur', 'jumlah' => 2, 'satuan' => 'g'],
                ['bahan' => 'Kemangi', 'jumlah' => 0.03, 'satuan' => 'ikat'],
                ['bahan' => 'Kotak Catering Mika', 'jumlah' => 1, 'satuan' => 'pcs'],
            ]);
        }
    }
    
    private function seedDineInResep()
    {
        // Contoh resep untuk menu dine-in utama
        $this->createDineInMenu('Nasi Liwet Komplit', 'NL-001', [
            ['bahan' => 'Beras', 'jumlah' => 200, 'satuan' => 'g'],
            ['bahan' => 'Santan Kelapa Instan', 'jumlah' => 300, 'satuan' => 'ml'],
            ['bahan' => 'Ayam Kampung', 'jumlah' => 150, 'satuan' => 'g'],
            ['bahan' => 'Daun Salam', 'jumlah' => 5, 'satuan' => 'g'],
            ['bahan' => 'Serai', 'jumlah' => 10, 'satuan' => 'g'],
            ['bahan' => 'Lengkuas', 'jumlah' => 15, 'satuan' => 'g'],
            ['bahan' => 'Bawang Merah', 'jumlah' => 20, 'satuan' => 'g'],
            ['bahan' => 'Bawang Putih', 'jumlah' => 15, 'satuan' => 'g'],
            ['bahan' => 'Garam Dapur', 'jumlah' => 8, 'satuan' => 'g'],
        ], 35000, 1); // kategori 1 = Dine In
        
        $this->createDineInMenu('Ayam Goreng Kalasan', 'AY-001', [
            ['bahan' => 'Ayam Broiler', 'jumlah' => 250, 'satuan' => 'g'],
            ['bahan' => 'Kunyit', 'jumlah' => 10, 'satuan' => 'g'],
            ['bahan' => 'Lengkuas', 'jumlah' => 8, 'satuan' => 'g'],
            ['bahan' => 'Daun Salam', 'jumlah' => 3, 'satuan' => 'g'],
            ['bahan' => 'Gula Merah', 'jumlah' => 15, 'satuan' => 'g'],
            ['bahan' => 'Garam Dapur', 'jumlah' => 5, 'satuan' => 'g'],
            ['bahan' => 'Minyak Goreng', 'jumlah' => 100, 'satuan' => 'ml'],
        ], 28000, 1);
        
        $this->createDineInMenu('Gurame Bakar Sambal Dabu', 'IK-001', [
            ['bahan' => 'Ikan Gurame', 'jumlah' => 300, 'satuan' => 'g'],
            ['bahan' => 'Tomat', 'jumlah' => 50, 'satuan' => 'g'],
            ['bahan' => 'Cabai Rawit', 'jumlah' => 15, 'satuan' => 'g'],
            ['bahan' => 'Bawang Merah', 'jumlah' => 25, 'satuan' => 'g'],
            ['bahan' => 'Jeruk Nipis', 'jumlah' => 0.5, 'satuan' => 'buah'],
            ['bahan' => 'Garam Dapur', 'jumlah' => 3, 'satuan' => 'g'],
            ['bahan' => 'Minyak Goreng', 'jumlah' => 50, 'satuan' => 'ml'],
        ], 45000, 1);
        
        $this->createDineInMenu('Bebek Goreng Kremes', 'BE-001', [
            ['bahan' => 'Daging Bebek', 'jumlah' => 200, 'satuan' => 'g'],
            ['bahan' => 'Tepung Terigu', 'jumlah' => 30, 'satuan' => 'g'],
            ['bahan' => 'Kunyit', 'jumlah' => 8, 'satuan' => 'g'],
            ['bahan' => 'Ketumbar', 'jumlah' => 5, 'satuan' => 'g'],
            ['bahan' => 'Bawang Putih', 'jumlah' => 12, 'satuan' => 'g'],
            ['bahan' => 'Garam Dapur', 'jumlah' => 6, 'satuan' => 'g'],
            ['bahan' => 'Minyak Goreng', 'jumlah' => 80, 'satuan' => 'ml'],
        ], 38000, 1);
        
        $this->createDineInMenu('Sate Ayam (10 tusuk)', 'ST-001', [
            ['bahan' => 'Ayam Broiler', 'jumlah' => 200, 'satuan' => 'g'],
            ['bahan' => 'Kecap Manis', 'jumlah' => 30, 'satuan' => 'ml'],
            ['bahan' => 'Bawang Merah', 'jumlah' => 15, 'satuan' => 'g'],
            ['bahan' => 'Bawang Putih', 'jumlah' => 10, 'satuan' => 'g'],
            ['bahan' => 'Cabai Rawit', 'jumlah' => 8, 'satuan' => 'g'],
            ['bahan' => 'Gula Merah', 'jumlah' => 20, 'satuan' => 'g'],
            ['bahan' => 'Tusuk Sate', 'jumlah' => 10, 'satuan' => 'pcs'],
        ], 25000, 1);
        
        $this->createDineInMenu('Sup Buntut Sapi', 'SP-001', [
            ['bahan' => 'Iga Sapi', 'jumlah' => 250, 'satuan' => 'g'],
            ['bahan' => 'Tomat', 'jumlah' => 40, 'satuan' => 'g'],
            ['bahan' => 'Bawang Merah', 'jumlah' => 20, 'satuan' => 'g'],
            ['bahan' => 'Bawang Putih', 'jumlah' => 15, 'satuan' => 'g'],
            ['bahan' => 'Serai', 'jumlah' => 8, 'satuan' => 'g'],
            ['bahan' => 'Daun Salam', 'jumlah' => 3, 'satuan' => 'g'],
            ['bahan' => 'Garam Dapur', 'jumlah' => 5, 'satuan' => 'g'],
            ['bahan' => 'Merica Bubuk', 'jumlah' => 2, 'satuan' => 'g'],
        ], 42000, 1);
        
        // Menu Minuman
        $this->createDineInMenu('Es Teh Manis', 'MN-001', [
            ['bahan' => 'Teh Celup', 'jumlah' => 2, 'satuan' => 'buah'],
            ['bahan' => 'Gula Pasir', 'jumlah' => 25, 'satuan' => 'g'],
            ['bahan' => 'Air Mineral', 'jumlah' => 300, 'satuan' => 'ml'],
        ], 8000, 1);
        
        $this->createDineInMenu('Kopi Susu Gula Aren', 'MN-002', [
            ['bahan' => 'Kopi Bubuk', 'jumlah' => 20, 'satuan' => 'g'],
            ['bahan' => 'Susu UHT', 'jumlah' => 100, 'satuan' => 'ml'],
            ['bahan' => 'Sirup Gula Aren', 'jumlah' => 30, 'satuan' => 'ml'],
            ['bahan' => 'Air Mineral', 'jumlah' => 200, 'satuan' => 'ml'],
        ], 15000, 1);
        
        $this->createDineInMenu('Jus Alpukat', 'MN-003', [
            ['bahan' => 'Alpukat Mentega', 'jumlah' => 1, 'satuan' => 'buah'],
            ['bahan' => 'Susu UHT', 'jumlah' => 150, 'satuan' => 'ml'],
            ['bahan' => 'Gula Pasir', 'jumlah' => 30, 'satuan' => 'g'],
            ['bahan' => 'Air Mineral', 'jumlah' => 100, 'satuan' => 'ml'],
        ], 18000, 1);
    }
    
    private function seedCateringResep()
    {
        // Paket Catering A (50 porsi)
        $this->createCateringMenu('Catering Paket A (50 porsi)', 'CT-A', [
            ['bahan' => 'Beras', 'jumlah' => 7500, 'satuan' => 'g'], // 150g x 50
            ['bahan' => 'Ayam Broiler', 'jumlah' => 5000, 'satuan' => 'g'], // 100g x 50
            ['bahan' => 'Ikan Gurame', 'jumlah' => 4000, 'satuan' => 'g'], // 80g x 50
            ['bahan' => 'Telur Ayam', 'jumlah' => 25, 'satuan' => 'buah'], // 0.5 x 50
            ['bahan' => 'Tempe', 'jumlah' => 25, 'satuan' => 'buah'], // 0.5 x 50
            ['bahan' => 'Tahu Putih', 'jumlah' => 25, 'satuan' => 'buah'], // 0.5 x 50
            ['bahan' => 'Minyak Goreng', 'jumlah' => 2500, 'satuan' => 'ml'], // 50ml x 50
            ['bahan' => 'Bawang Merah', 'jumlah' => 1250, 'satuan' => 'g'], // 25g x 50
            ['bahan' => 'Bawang Putih', 'jumlah' => 750, 'satuan' => 'g'], // 15g x 50
            ['bahan' => 'Cabai Merah Keriting', 'jumlah' => 1000, 'satuan' => 'g'], // 20g x 50
            ['bahan' => 'Tomat', 'jumlah' => 1500, 'satuan' => 'g'], // 30g x 50
            ['bahan' => 'Garam Dapur', 'jumlah' => 250, 'satuan' => 'g'], // 5g x 50
            ['bahan' => 'Kemangi', 'jumlah' => 5, 'satuan' => 'ikat'], // 0.1 x 50
            ['bahan' => 'Kotak Catering Mika', 'jumlah' => 50, 'satuan' => 'pcs'],
        ], 2000000, 2); // kategori 2 = Catering
        
        // Paket Catering B (100 porsi)
        $this->createCateringMenu('Catering Paket B (100 porsi)', 'CT-B', [
            ['bahan' => 'Beras', 'jumlah' => 15000, 'satuan' => 'g'], // 150g x 100
            ['bahan' => 'Ayam Broiler', 'jumlah' => 8000, 'satuan' => 'g'], // 80g x 100
            ['bahan' => 'Daging Sapi', 'jumlah' => 5000, 'satuan' => 'g'], // 50g x 100
            ['bahan' => 'Tempe', 'jumlah' => 50, 'satuan' => 'buah'], // 0.5 x 100
            ['bahan' => 'Tahu Putih', 'jumlah' => 50, 'satuan' => 'buah'], // 0.5 x 100
            ['bahan' => 'Minyak Goreng', 'jumlah' => 4000, 'satuan' => 'ml'], // 40ml x 100
            ['bahan' => 'Bawang Merah', 'jumlah' => 2000, 'satuan' => 'g'], // 20g x 100
            ['bahan' => 'Bawang Putih', 'jumlah' => 1200, 'satuan' => 'g'], // 12g x 100
            ['bahan' => 'Cabai Merah Keriting', 'jumlah' => 1500, 'satuan' => 'g'], // 15g x 100
            ['bahan' => 'Tomat', 'jumlah' => 2500, 'satuan' => 'g'], // 25g x 100
            ['bahan' => 'Garam Dapur', 'jumlah' => 400, 'satuan' => 'g'], // 4g x 100
            ['bahan' => 'Kemangi', 'jumlah' => 10, 'satuan' => 'ikat'], // 0.1 x 100
            ['bahan' => 'Kotak Catering Mika', 'jumlah' => 100, 'satuan' => 'pcs'],
        ], 3500000, 2);
    }
    
    private function insertResep($menuId, $resepData)
    {
        foreach ($resepData as $item) {
            $bahanBaku = DB::table('bahan_baku')->where('nama_bahan', $item['bahan'])->first();
            if (!$bahanBaku) {
                continue; // Skip jika bahan baku tidak ditemukan
            }
            
            // Convert jumlah ke satuan gram/ml/buah sesuai dengan satuan dasar bahan baku
            $jumlahSatuan = $this->convertToBaseSatuan($item['jumlah'], $item['satuan'], $bahanBaku->satuan_id);
            
            DB::table('resep_menu')->insert([
                'menu_id' => $menuId,
                'bahan_baku_id' => $bahanBaku->id,
                'jumlah' => $jumlahSatuan,
                'satuan_id' => $bahanBaku->satuan_id,
                'dikonfirmasi' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
    
    private function convertToBaseSatuan($jumlah, $satuanInput, $satuanIdBahanBaku)
    {
        // Konversi satuan ke gram/ml/buah/pcs/ikat sesuai dengan satuan bahan baku
        switch($satuanInput) {
            case 'kg':
                return $jumlah * 1000; // ke gram
            case 'liter':
            case 'l':
                return $jumlah * 1000; // ke ml
            default:
                return $jumlah; // sudah dalam satuan yang benar
        }
    }
    
    private function createDineInMenu($namaMenu, $kodeMenu, $resepData, $harga, $kategoriId)
    {
        // Cek apakah menu sudah ada
        $existingMenu = DB::table('menu')->where('kode_menu', $kodeMenu)->first();
        if ($existingMenu) {
            $this->insertResep($existingMenu->id, $resepData);
            return;
        }
        
        // Buat menu baru jika belum ada
        $menuId = DB::table('menu')->insertGetId([
            'nama_menu' => $namaMenu,
            'kode_menu' => $kodeMenu,
            'harga_jual' => $harga,
            'kategori_menu_id' => $kategoriId,
            'jenis_menu_id' => 1, // Dine In
            'status_aktif' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $this->insertResep($menuId, $resepData);
    }
    
    private function createCateringMenu($namaMenu, $kodeMenu, $resepData, $harga, $kategoriId)
    {
        // Cek apakah menu sudah ada
        $existingMenu = DB::table('menu')->where('kode_menu', $kodeMenu)->first();
        if ($existingMenu) {
            $this->insertResep($existingMenu->id, $resepData);
            return;
        }
        
        // Buat menu baru jika belum ada
        $menuId = DB::table('menu')->insertGetId([
            'nama_menu' => $namaMenu,
            'kode_menu' => $kodeMenu,
            'harga_jual' => $harga,
            'kategori_menu_id' => $kategoriId,
            'jenis_menu_id' => 2, // Catering
            'status_aktif' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $this->insertResep($menuId, $resepData);
    }
}