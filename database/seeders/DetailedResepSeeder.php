<?php

namespace Database\Seeders;

use App\Models\BahanBaku;
use App\Models\KategoriBahanBaku;
use App\Models\Menu;
use App\Models\ResepMenu;
use App\Models\Satuan;
use App\Models\StokBahan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class DetailedResepSeeder extends Seeder
{
    public function run(): void
    {
        // Define all raw ingredients that we need, their category, unit, and approx price
        $bahanRawData = [
            // Sembako
            ['Beras Putih', 'Sembako', 'kg', 15000],
            ['Beras Pulen', 'Sembako', 'kg', 18000],
            ['Minyak Goreng', 'Sembako', 'liter', 18000],
            ['Gula Pasir', 'Sembako', 'kg', 16000],
            ['Tepung Tapioka', 'Sembako', 'kg', 12000],
            ['Tepung Terigu', 'Sembako', 'kg', 10000],
            ['Tepung Beras', 'Sembako', 'kg', 15000],
            ['Tepung Serbaguna', 'Sembako', 'kg', 25000],
            ['Maizena', 'Sembako', 'kg', 20000],
            ['Soun', 'Sembako', 'kg', 25000],
            ['Mie Kuning Basah', 'Sembako', 'kg', 15000],

            // Daging & Seafood
            ['Ayam Potong', 'Daging & Seafood', 'kg', 35000],
            ['Ayam Kampung', 'Daging & Seafood', 'kg', 60000],
            ['Daging Bebek', 'Daging & Seafood', 'kg', 50000],
            ['Daging Sapi', 'Daging & Seafood', 'kg', 120000],
            ['Ikan Lele', 'Daging & Seafood', 'kg', 25000],
            ['Ikan Gurame', 'Daging & Seafood', 'kg', 45000],
            ['Ikan Nila', 'Daging & Seafood', 'kg', 30000],
            ['Ikan Mas', 'Daging & Seafood', 'kg', 35000],
            ['Ikan Peda Asin', 'Daging & Seafood', 'kg', 60000],
            ['Ikan Sepat Asin', 'Daging & Seafood', 'kg', 60000],
            ['Telur Ayam', 'Daging & Seafood', 'kg', 28000],
            ['Telur Puyuh Rebus', 'Daging & Seafood', 'pcs', 1000],
            ['Jeroan/Campur Sapi', 'Daging & Seafood', 'kg', 80000],
            ['Iga Sapi', 'Daging & Seafood', 'kg', 90000],
            ['Kulit Ayam', 'Daging & Seafood', 'kg', 20000],
            ['Ati Ampela Ayam', 'Daging & Seafood', 'kg', 25000],
            ['Bakso Ikan', 'Daging & Seafood', 'kg', 40000],
            ['Bakso Sapi', 'Daging & Seafood', 'pcs', 2000],
            ['Sosis', 'Daging & Seafood', 'pcs', 3000],
            ['Kikil Sapi', 'Daging & Seafood', 'kg', 75000],
            ['Fillet Dori', 'Daging & Seafood', 'kg', 55000],

            // Sayur & Buah
            ['Timun', 'Sayur & Buah', 'kg', 8000],
            ['Selada', 'Sayur & Buah', 'ikat', 3000],
            ['Kemangi', 'Sayur & Buah', 'ikat', 2000],
            ['Wortel', 'Sayur & Buah', 'kg', 12000],
            ['Buncis', 'Sayur & Buah', 'kg', 15000],
            ['Brokoli', 'Sayur & Buah', 'kg', 25000],
            ['Kol', 'Sayur & Buah', 'kg', 10000],
            ['Sawi', 'Sayur & Buah', 'kg', 10000],
            ['Kangkung', 'Sayur & Buah', 'ikat', 3000],
            ['Jengkol', 'Sayur & Buah', 'kg', 40000],
            ['Pete', 'Sayur & Buah', 'kg', 50000],
            ['Kentang', 'Sayur & Buah', 'kg', 15000],
            ['Kacang Panjang', 'Sayur & Buah', 'kg', 12000],
            ['Tauge', 'Sayur & Buah', 'kg', 10000],
            ['Kacang Tanah', 'Sayur & Buah', 'kg', 28000],
            ['Tomat', 'Sayur & Buah', 'kg', 12000],
            ['Tomat Ceri', 'Sayur & Buah', 'kg', 25000],
            ['Jagung Manis', 'Sayur & Buah', 'kg', 10000],
            ['Bawang Bombay', 'Sayur & Buah', 'kg', 25000],
            ['Daun Bawang', 'Sayur & Buah', 'ikat', 3000],
            ['Seledri', 'Sayur & Buah', 'ikat', 3000],
            ['Jamur Kuping', 'Sayur & Buah', 'kg', 30000],
            ['Paprika', 'Sayur & Buah', 'kg', 40000],
            ['Buah Melon', 'Sayur & Buah', 'kg', 15000],
            ['Buah Semangka', 'Sayur & Buah', 'kg', 15000],
            ['Jeruk', 'Sayur & Buah', 'kg', 18000],
            ['Apel', 'Sayur & Buah', 'kg', 35000],
            ['Anggur', 'Sayur & Buah', 'kg', 45000],
            ['Bengkuang', 'Sayur & Buah', 'kg', 10000],
            ['Mangga Muda', 'Sayur & Buah', 'kg', 20000],
            ['Nanas', 'Sayur & Buah', 'kg', 12000],
            ['Jambu', 'Sayur & Buah', 'kg', 15000],
            ['Kedondong', 'Sayur & Buah', 'kg', 12000],
            ['Sirsak', 'Sayur & Buah', 'kg', 15000],
            ['Mangga', 'Sayur & Buah', 'kg', 20000],
            ['Stroberi', 'Sayur & Buah', 'kg', 40000],
            ['Buah Naga', 'Sayur & Buah', 'kg', 25000],
            ['Alpukat', 'Sayur & Buah', 'kg', 25000],

            // Bumbu & Rempah
            ['Daun Salam', 'Bumbu & Rempah', 'ikat', 2000],
            ['Serai', 'Bumbu & Rempah', 'ikat', 2000],
            ['Daun Jeruk', 'Bumbu & Rempah', 'ikat', 2000],
            ['Daun Kunyit', 'Bumbu & Rempah', 'ikat', 2000],
            ['Bawang Merah', 'Bumbu & Rempah', 'kg', 35000],
            ['Bawang Putih', 'Bumbu & Rempah', 'kg', 35000],
            ['Cabai Merah Keriting', 'Bumbu & Rempah', 'kg', 45000],
            ['Cabai Rawit Merah', 'Bumbu & Rempah', 'kg', 50000],
            ['Ketumbar Bubuk', 'Bumbu & Rempah', 'kg', 40000],
            ['Kunyit Bubuk', 'Bumbu & Rempah', 'kg', 35000],
            ['Jahe', 'Bumbu & Rempah', 'kg', 25000],
            ['Lengkuas', 'Bumbu & Rempah', 'kg', 15000],
            ['Kencur', 'Bumbu & Rempah', 'kg', 25000],
            ['Pala Bubuk', 'Bumbu & Rempah', 'kg', 80000],
            ['Merica Bubuk', 'Bumbu & Rempah', 'kg', 90000],
            ['Garam', 'Bumbu & Rempah', 'kg', 8000],
            ['Gula Merah', 'Bumbu & Rempah', 'kg', 20000],
            ['Terasi', 'Bumbu & Rempah', 'pcs', 1000],
            ['Asam Jawa', 'Bumbu & Rempah', 'kg', 20000],
            ['Jeruk Nipis', 'Bumbu & Rempah', 'kg', 15000],
            ['Jeruk Limau', 'Bumbu & Rempah', 'kg', 25000],
            ['Kecap Manis', 'Bumbu & Rempah', 'liter', 25000],
            ['Kecap Asin', 'Bumbu & Rempah', 'liter', 20000],
            ['Saus Tomat', 'Bumbu & Rempah', 'liter', 15000],
            ['Saus Sambal', 'Bumbu & Rempah', 'liter', 18000],
            ['Margarin', 'Bumbu & Rempah', 'kg', 25000],
            ['Mayones', 'Bumbu & Rempah', 'kg', 35000],
            ['Bumbu Kacang', 'Bumbu & Rempah', 'kg', 45000],
            ['Kaldu Sapi', 'Bumbu & Rempah', 'liter', 5000],
            ['Kaldu Ayam', 'Bumbu & Rempah', 'liter', 5000],
            ['Kaldu Bubuk', 'Bumbu & Rempah', 'kg', 35000],
            ['Wijen Sangrai', 'Bumbu & Rempah', 'kg', 60000],
            ['Cuka', 'Bumbu & Rempah', 'liter', 15000],
            ['Bawang Goreng', 'Bumbu & Rempah', 'kg', 80000],

            // Lainnya / Olahan
            ['Tempe', 'Lainnya', 'pcs', 2000],
            ['Tempe Mendoan', 'Lainnya', 'pcs', 1000],
            ['Tahu', 'Lainnya', 'pcs', 1500],
            ['Tahu Sumedang', 'Lainnya', 'pcs', 1000],
            ['Tahu Isi Bakso', 'Lainnya', 'pcs', 3000],
            ['Oncom', 'Lainnya', 'pcs', 2500],
            ['Kerupuk', 'Lainnya', 'pcs', 500],
            ['Kerupuk Udang', 'Lainnya', 'pcs', 1000],
            ['Santan Kental', 'Lainnya', 'liter', 25000],
            ['Susu Cair', 'Minuman', 'liter', 18000],
            ['Susu Kental Manis', 'Minuman', 'kg', 25000],
            ['Susu Bubuk', 'Minuman', 'kg', 80000],
            ['Keju Parut', 'Lainnya', 'kg', 75000],
            ['Tusuk Sate', 'Lainnya', 'pcs', 100],
            ['Puding', 'Lainnya', 'pcs', 3000],
            ['Es Krim', 'Lainnya', 'porsi', 5000],

            // Bahan Minuman
            ['Air Mineral Botol', 'Bahan Minuman', 'pcs', 3000],
            ['Es Batu', 'Bahan Minuman', 'kg', 2000],
            ['Sirup Vanilla', 'Bahan Minuman', 'liter', 60000],
            ['Sirup Karamel', 'Bahan Minuman', 'liter', 60000],
            ['Gula Cair', 'Bahan Minuman', 'liter', 15000],
            ['Kopi Bubuk Espresso', 'Bahan Minuman', 'kg', 120000],
            ['Kopi Bubuk Arabika', 'Bahan Minuman', 'kg', 100000],
            ['Kopi Bubuk Robusta', 'Bahan Minuman', 'kg', 80000],
            ['Kopi Bubuk', 'Bahan Minuman', 'kg', 50000],
            ['Kopi Sachet', 'Bahan Minuman', 'pcs', 1500],
            ['Bubuk Milo', 'Bahan Minuman', 'kg', 85000],
            ['Bubuk Matcha', 'Bahan Minuman', 'kg', 150000],
            ['Teh Celup', 'Bahan Minuman', 'pcs', 500],
        ];

        // Ensure categories exist
        $categoriesMap = [];
        foreach ($bahanRawData as $b) {
            $catName = $b[1];
            if (!isset($categoriesMap[$catName])) {
                $categoriesMap[$catName] = KategoriBahanBaku::firstOrCreate(['nama_kategori' => $catName]);
            }
        }

        // Ensure units exist
        $unitsMap = [
            'kg' => Satuan::firstOrCreate(['singkatan' => 'kg'], ['nama_satuan' => 'Kilogram']),
            'gram' => Satuan::firstOrCreate(['singkatan' => 'g'], ['nama_satuan' => 'Gram']),
            'liter' => Satuan::firstOrCreate(['singkatan' => 'L'], ['nama_satuan' => 'Liter']),
            'ml' => Satuan::firstOrCreate(['singkatan' => 'ml'], ['nama_satuan' => 'Mililiter']),
            'pcs' => Satuan::firstOrCreate(['singkatan' => 'pcs'], ['nama_satuan' => 'Pieces']),
            'porsi' => Satuan::firstOrCreate(['singkatan' => 'prsi'], ['nama_satuan' => 'Porsi']),
            'ikat' => Satuan::firstOrCreate(['singkatan' => 'ikt'], ['nama_satuan' => 'Ikat']),
        ];

        $bahanModels = [];
        foreach ($bahanRawData as $b) {
            $nama = $b[0];
            $cat = $categoriesMap[$b[1]]->id;
            $sat = $unitsMap[$b[2]]->id;
            $harga = $b[3];

            $bahan = BahanBaku::firstOrCreate(
                ['nama_bahan' => $nama],
                [
                    'kode_bahan' => 'BHN-' . strtoupper(substr(md5($nama), 0, 5)),
                    'kategori_bahan_baku_id' => $cat,
                    'satuan_id' => $sat,
                    'harga_satuan' => $harga,
                    'stok_minimal' => 10,
                    'jenis_peruntukan' => 'Semua',
                    'status_aktif' => 1,
                ]
            );
            $bahanModels[strtolower($nama)] = $bahan;

            // Pastikan stok ada
            StokBahan::firstOrCreate(
                ['bahan_baku_id' => $bahan->id, 'jenis_persediaan' => 'harian'],
                ['jumlah_stok' => 1000]
            );
            StokBahan::firstOrCreate(
                ['bahan_baku_id' => $bahan->id, 'jenis_persediaan' => 'catering'],
                ['jumlah_stok' => 1000]
            );
        }

        // Mapping helper to resolve quantity relative to unit (e.g. 100g to kg = 0.1)
        $addRecipe = function($menuName, $ingredientsList) use ($bahanModels) {
            $menu = Menu::where('nama_menu', 'like', "%{$menuName}%")->first();
            if (!$menu) return; // skip if menu not found

            // Clear existing recipe to prevent duplication
            ResepMenu::where('menu_id', $menu->id)->delete();

            foreach ($ingredientsList as $ing) {
                $bahanName = strtolower($ing[0]);
                if (!isset($bahanModels[$bahanName])) {
                    // fallbacks or warnings could be logged here
                    continue;
                }

                $bahan = $bahanModels[$bahanName];
                $qty = $ing[1];
                $inputUnit = $ing[2] ?? null;

                // Konversi unit (misal input 'gram' tapi bahan 'kg', maka dibagi 1000)
                if ($inputUnit === 'gram' && strtolower($bahan->satuan->singkatan) === 'kg') {
                    $qty = $qty / 1000;
                }
                if ($inputUnit === 'ml' && strtolower($bahan->satuan->singkatan) === 'l') {
                    $qty = $qty / 1000;
                }

                ResepMenu::create([
                    'menu_id' => $menu->id,
                    'bahan_baku_id' => $bahan->id,
                    'jumlah' => $qty,
                    'satuan_id' => $bahan->satuan_id,
                ]);
            }
        };

        // ---------------------------------------------------------------------
        // DATA RESEP DINE IN (Berdasarkan dokumen user)
        // ---------------------------------------------------------------------
        
        // Paket Nasi Liwet 5 Orang
        $addRecipe('Paket Nasi Liwet', [
            ['Beras Putih', 500, 'gram'],
            ['Santan Kental', 75, 'ml'],
            ['Daun Salam', 2, 'pcs'], // atau 2 lembar = asumsikan 0.1 ikat
            ['Serai', 2, 'pcs'],
            ['Kemangi', 0.2, 'ikat'],
            ['Bawang Merah', 20, 'gram'], // 5 siung = ~20g
            ['Garam', 5, 'gram'],
            ['Ayam Potong', 900, 'gram'], // 5 potong x 180g
            ['Tahu', 5, 'pcs'],
            ['Tempe', 5, 'pcs'],
            ['Kangkung', 250, 'gram'],
            ['Jengkol', 150, 'gram'],
            ['Ikan Peda Asin', 300, 'gram'],
            ['Timun', 150, 'gram'],
            ['Selada', 0.5, 'ikat'],
            ['Cabai Merah Keriting', 25, 'gram'],
            ['Cabai Rawit Merah', 25, 'gram'],
            ['Terasi', 1, 'pcs'],
            ['Tomat', 50, 'gram'],
        ]);

        // Nasi Ayam Goreng / Bakar
        $ayamGoreng = [
            ['Beras Putih', 100, 'gram'],
            ['Ayam Potong', 180, 'gram'],
            ['Bawang Putih', 5, 'gram'],
            ['Ketumbar Bubuk', 1, 'gram'],
            ['Kunyit Bubuk', 1, 'gram'],
            ['Garam', 2, 'gram'],
            ['Daun Salam', 0.1, 'ikat'],
        ];
        $addRecipe('Nasi Ayam Goreng', $ayamGoreng);
        $ayamBakar = array_merge($ayamGoreng, [['Kecap Manis', 15, 'ml']]);
        $addRecipe('Nasi Ayam Bakar', $ayamBakar);

        // Liwet Ayam Goreng / Bakar
        $liwetAyamGoreng = [
            ['Beras Putih', 100, 'gram'],
            ['Santan Kental', 15, 'ml'],
            ['Ayam Potong', 180, 'gram'],
            ['Bawang Putih', 5, 'gram'],
            ['Bawang Merah', 5, 'gram'],
            ['Serai', 0.1, 'ikat'],
            ['Daun Salam', 0.1, 'ikat'],
            ['Kemangi', 0.1, 'ikat'],
        ];
        $addRecipe('Liwet Ayam Goreng', $liwetAyamGoreng);
        $addRecipe('Liwet Ayam Bakar', array_merge($liwetAyamGoreng, [['Kecap Manis', 15, 'ml']]));

        // Nasi Ayam Penyet Goreng / Bakar
        $ayamPenyet = [
            ['Beras Putih', 100, 'gram'],
            ['Ayam Potong', 180, 'gram'],
            ['Cabai Rawit Merah', 10, 'gram'],
            ['Cabai Merah Keriting', 5, 'gram'],
            ['Bawang Merah', 10, 'gram'],
            ['Terasi', 0.2, 'pcs'],
            ['Tomat', 20, 'gram'],
        ];
        $addRecipe('Nasi Ayam Penyet Goreng', $ayamPenyet);
        $addRecipe('Nasi Ayam Penyet Bakar', array_merge($ayamPenyet, [['Kecap Manis', 15, 'ml']]));

        // Liwet Ayam Penyet Goreng / Bakar
        $liwetAyamPenyet = array_merge($ayamPenyet, [
            ['Santan Kental', 15, 'ml'],
            ['Serai', 0.1, 'ikat'],
            ['Daun Salam', 0.1, 'ikat'],
        ]);
        $addRecipe('Liwet Ayam Penyet Goreng', $liwetAyamPenyet);
        $addRecipe('Liwet Ayam Penyet Bakar', array_merge($liwetAyamPenyet, [['Kecap Manis', 15, 'ml']]));

        // Nasi Tutug Oncom Ayam
        $tutugOncomAyam = [
            ['Beras Putih', 100, 'gram'],
            ['Ayam Potong', 180, 'gram'],
            ['Oncom', 1, 'pcs'],
            ['Bawang Merah', 5, 'gram'],
            ['Bawang Putih', 5, 'gram'],
            ['Cabai Merah Keriting', 5, 'gram'],
            ['Kencur', 2, 'gram'],
            ['Kemangi', 0.1, 'ikat'],
        ];
        $addRecipe('Nasi Tutug Oncom Ayam Goreng', $tutugOncomAyam);
        $addRecipe('Nasi Tutug Oncom Ayam Bakar', array_merge($tutugOncomAyam, [['Kecap Manis', 15, 'ml']]));

        // Kategori Ayam Kampung
        $ayamKampung = [
            ['Beras Putih', 100, 'gram'],
            ['Ayam Kampung', 200, 'gram'],
            ['Bawang Putih', 5, 'gram'],
            ['Ketumbar Bubuk', 1, 'gram'],
            ['Kunyit Bubuk', 1, 'gram'],
            ['Garam', 2, 'gram'],
        ];
        $addRecipe('Nasi Ayam Kampung Goreng', $ayamKampung);
        $addRecipe('Nasi Ayam Kampung Bakar', array_merge($ayamKampung, [['Kecap Manis', 15, 'ml']]));

        // Bebek
        $bebek = [
            ['Beras Putih', 100, 'gram'],
            ['Daging Bebek', 220, 'gram'],
            ['Bawang Putih', 10, 'gram'],
            ['Jahe', 5, 'gram'],
            ['Daun Salam', 0.1, 'ikat'],
            ['Daun Jeruk', 0.1, 'ikat'],
            ['Garam', 2, 'gram'],
        ];
        $addRecipe('Nasi Bebek Goreng', $bebek);
        $addRecipe('Nasi Bebek Bakar', array_merge($bebek, [['Kecap Manis', 15, 'ml']]));

        // Sate
        $sateSapi = [
            ['Daging Sapi', 200, 'gram'],
            ['Kecap Manis', 45, 'ml'], // 3 sdm
            ['Bawang Merah', 15, 'gram'],
            ['Bawang Putih', 15, 'gram'],
            ['Ketumbar Bubuk', 2, 'gram'],
            ['Bumbu Kacang', 45, 'gram'],
            ['Tusuk Sate', 10, 'pcs'],
        ];
        $addRecipe('Sate Sapi', $sateSapi);
        $addRecipe('Sate Kambing', $sateSapi); // Jika tidak ada kambing, pakai sapi sbg fallback sesuai nama atau biarkan
        
        $sateAyam = [
            ['Ayam Potong', 200, 'gram'],
            ['Kecap Manis', 45, 'ml'],
            ['Bawang Merah', 15, 'gram'],
            ['Bawang Putih', 15, 'gram'],
            ['Ketumbar Bubuk', 2, 'gram'],
            ['Bumbu Kacang', 45, 'gram'],
            ['Tusuk Sate', 10, 'pcs'],
        ];
        $addRecipe('Sate Ayam', $sateAyam);

        $sateJando = [
            ['Jeroan/Campur Sapi', 200, 'gram'],
            ['Kecap Manis', 45, 'ml'],
            ['Bawang Merah', 15, 'gram'],
            ['Bawang Putih', 15, 'gram'],
            ['Ketumbar Bubuk', 2, 'gram'],
            ['Tusuk Sate', 10, 'pcs'],
            ['Oncom', 1, 'pcs'],
        ];
        $addRecipe('Sate Jando', $sateJando);

        // Sop
        $sopIga = [
            ['Iga Sapi', 250, 'gram'],
            ['Wortel', 30, 'gram'],
            ['Kentang', 30, 'gram'],
            ['Daun Bawang', 0.1, 'ikat'],
            ['Seledri', 0.1, 'ikat'],
            ['Bawang Goreng', 5, 'gram'],
            ['Pala Bubuk', 1, 'gram'],
            ['Merica Bubuk', 1, 'gram'],
            ['Kaldu Sapi', 400, 'ml'],
        ];
        $addRecipe('Sop Iga Sapi', $sopIga);

        $kulitGoreng = [
            ['Kulit Ayam', 100, 'gram'],
            ['Tepung Serbaguna', 30, 'gram'],
            ['Bawang Putih', 1, 'gram'],
        ];
        $addRecipe('Kulit Goreng Jumbo', $kulitGoreng);

        // Lauk Satuan
        $addRecipe('Ayam Bakar', [
            ['Ayam Potong', 180, 'gram'],
            ['Bawang Putih', 5, 'gram'],
            ['Ketumbar Bubuk', 1, 'gram'],
            ['Kecap Manis', 15, 'ml'],
        ]);
        $addRecipe('Ayam Goreng', [
            ['Ayam Potong', 180, 'gram'],
            ['Bawang Putih', 5, 'gram'],
            ['Ketumbar Bubuk', 1, 'gram'],
        ]);
        $addRecipe('Tahu', [['Tahu', 1, 'pcs']]);
        $addRecipe('Tempe', [['Tempe', 1, 'pcs']]);
        $addRecipe('Kol Goreng', [['Kol', 60, 'gram']]);
        $addRecipe('Karedok', [
            ['Kacang Panjang', 20, 'gram'],
            ['Tauge', 15, 'gram'],
            ['Kol', 20, 'gram'],
            ['Timun', 15, 'gram'],
            ['Kacang Tanah', 20, 'gram'],
            ['Bawang Putih', 5, 'gram'],
            ['Gula Merah', 10, 'gram'],
            ['Asam Jawa', 5, 'gram'],
        ]);

        // Minuman
        $jusSirsak = [
            ['Sirsak', 150, 'gram'],
            ['Gula Cair', 15, 'ml'],
            ['Es Batu', 100, 'gram'],
        ];
        $addRecipe('Jus Sirsak', $jusSirsak);
        
        $jusMangga = [
            ['Mangga', 150, 'gram'],
            ['Gula Cair', 15, 'ml'],
            ['Es Batu', 100, 'gram'],
        ];
        $addRecipe('Jus Mangga', $jusMangga);

        $jusAlpukat = [
            ['Alpukat', 150, 'gram'],
            ['Susu Kental Manis', 30, 'gram'],
            ['Gula Cair', 10, 'ml'],
            ['Es Batu', 100, 'gram'],
        ];
        $addRecipe('Jus Alpukat', $jusAlpukat);
        
        $bandrek = [
            ['Gula Merah', 30, 'gram'],
            ['Jahe', 10, 'gram'],
            ['Serai', 0.1, 'ikat'],
        ];
        $addRecipe('Bandrek', $bandrek);
        $addRecipe('Bandrek Susu', array_merge($bandrek, [['Susu Kental Manis', 30, 'gram']]));

        $bajigur = array_merge($bandrek, [['Santan Kental', 30, 'ml'], ['Kopi Bubuk', 1, 'gram']]);
        $addRecipe('Bajigur', $bajigur);

        $esKopiSusu = [
            ['Kopi Bubuk Espresso', 15, 'gram'],
            ['Susu Cair', 100, 'ml'],
            ['Sirup Vanilla', 15, 'ml'], // atau aren
            ['Es Batu', 100, 'gram'],
        ];
        $addRecipe('Es Kopi Susu', $esKopiSusu);
        $addRecipe('Es Kopi Susu Gula Aren', $esKopiSusu);

        $hotGreenMatcha = [
            ['Bubuk Matcha', 5, 'gram'],
            ['Susu Cair', 150, 'ml'],
            ['Gula Cair', 10, 'ml'],
        ];
        $addRecipe('Hot Green Matcha', $hotGreenMatcha);
        
        // CATERING
        $addRecipe('Sup Kimlo', [
            ['Kaldu Ayam', 200, 'ml'],
            ['Wortel', 15, 'gram'],
            ['Jamur Kuping', 15, 'gram'],
            ['Soun', 15, 'gram'],
            ['Bakso Ikan', 15, 'gram'],
            ['Telur Puyuh Rebus', 2, 'pcs'],
            ['Bawang Goreng', 2, 'gram'],
        ]);
        
        $addRecipe('Rendang', [
            ['Daging Sapi', 100, 'gram'],
            ['Santan Kental', 100, 'ml'],
            ['Cabai Merah Keriting', 15, 'gram'],
            ['Bawang Merah', 15, 'gram'],
            ['Bawang Putih', 15, 'gram'],
            ['Jahe', 2, 'gram'],
            ['Lengkuas', 2, 'gram'],
        ]);
        
        $addRecipe('Bistik', [
            ['Daging Sapi', 100, 'gram'],
            ['Bawang Bombay', 30, 'gram'],
            ['Saus Tomat', 15, 'ml'],
            ['Kecap Manis', 15, 'ml'],
            ['Margarin', 15, 'gram'],
        ]);

        $addRecipe('Gado-gado', [
            ['Kangkung', 25, 'gram'],
            ['Tauge', 25, 'gram'],
            ['Kol', 25, 'gram'],
            ['Kacang Panjang', 25, 'gram'],
            ['Tahu', 1, 'pcs'],
            ['Tempe', 1, 'pcs'],
            ['Telur Ayam', 25, 'gram'], // stgh butir
            ['Bumbu Kacang', 45, 'gram'],
            ['Kerupuk', 1, 'pcs'],
        ]);

        // Nasi Box Packages
        $addRecipe('Nasi Box Paket A', [
            ['Beras Putih', 100, 'gram'],
            ['Ayam Potong', 180, 'gram'],
            ['Ikan Lele', 200, 'gram'],
            ['Telur Ayam', 50, 'gram'],
            ['Sayur Karedok', 1, 'porsi'], // approx
            ['Timun', 30, 'gram'],
            ['Selada', 0.1, 'ikat'],
            ['Kerupuk', 1, 'pcs'],
            ['Buah Melon', 80, 'gram'],
            ['Puding', 1, 'pcs'],
            ['Air Mineral Botol', 1, 'pcs'],
        ]);

        $addRecipe('Nasi Box Paket B', [
            ['Beras Putih', 100, 'gram'],
            ['Ayam Potong', 180, 'gram'],
            ['Tempe', 1, 'pcs'],
            ['Tahu', 1, 'pcs'],
            ['Buncis', 40, 'gram'],
            ['Wortel', 30, 'gram'],
            ['Timun', 30, 'gram'],
            ['Kerupuk', 1, 'pcs'],
            ['Buah Semangka', 80, 'gram'],
            ['Air Mineral Botol', 1, 'pcs'],
        ]);
        
        $addRecipe('Nasi Box Paket C', [
            ['Beras Putih', 100, 'gram'],
            ['Ayam Potong', 180, 'gram'],
            ['Tempe', 1, 'pcs'],
            ['Brokoli', 40, 'gram'],
            ['Wortel', 30, 'gram'],
            ['Timun', 30, 'gram'],
            ['Kerupuk', 1, 'pcs'],
            ['Buah Melon', 80, 'gram'],
            ['Air Mineral Botol', 1, 'pcs'],
        ]);

    }
}
