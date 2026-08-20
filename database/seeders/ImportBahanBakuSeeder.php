<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ImportBahanBakuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Matikan Foreign Key Checks
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // 2. Kosongkan tabel terkait
        \Illuminate\Support\Facades\DB::table('resep_menu')->truncate();
        \Illuminate\Support\Facades\DB::table('stok_bahan')->truncate();
        \Illuminate\Support\Facades\DB::table('stok_catering')->truncate();
        \Illuminate\Support\Facades\DB::table('detail_pengadaan_bahan')->truncate();
        \Illuminate\Support\Facades\DB::table('notifikasi_stoks')->truncate();
        
        \Illuminate\Support\Facades\DB::table('bahan_baku')->truncate();
        \Illuminate\Support\Facades\DB::table('kategori_bahan_baku')->truncate();

        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 3. Baca dan Parse File Markdown
        $mdPath = base_path('docs/Data_Bahan_Baku_Saung_Babakan_Cinta.md');
        $md = file_get_contents($mdPath);
        $lines = explode("\n", $md);
        
        $categories = [];
        $currentCategory = null;

        foreach ($lines as $line) {
            if (preg_match('/^##\s+\d+\.\s+(.+)$/', trim($line), $matches)) {
                $currentCategory = trim($matches[1]);
                $categories[$currentCategory] = [];
            } elseif (preg_match('/^\d+\.\s+(.+)$/', trim($line), $matches)) {
                if ($currentCategory) {
                    $name = trim($matches[1]);
                    // Hapus format miring markdown (*)
                    $name = str_replace('*', '', $name);
                    $categories[$currentCategory][] = $name;
                }
            }
        }

        // Cari atau buat satuan default (misal: 'Pcs' atau 'Gram') 
        // Karena data markdown tidak menyebutkan satuan spesifik.
        $satuanDefault = \App\Models\Satuan::firstOrCreate(
            ['singkatan' => 'pcs'],
            ['nama_satuan' => 'Pieces']
        );

        $kodePrefix = 'BB'; // Prefix untuk kode bahan baku
        $counter = 1;

        foreach ($categories as $catName => $items) {
            // Insert Kategori
            $kategori = \App\Models\KategoriBahanBaku::create([
                'nama_kategori' => $catName,
            ]);

            foreach ($items as $itemName) {
                \App\Models\BahanBaku::create([
                    'kategori_bahan_baku_id' => $kategori->id,
                    'satuan_id' => $satuanDefault->id,
                    'nama_bahan' => $itemName,
                    'stok_minimal' => 0,
                    'status_aktif' => true,
                ]);

                $counter++;
            }
        }

        $this->command->info("Berhasil mengimpor " . ($counter - 1) . " item bahan baku.");
    }
}
