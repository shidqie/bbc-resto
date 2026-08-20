<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ImportMenuDineInSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Matikan Foreign Key Checks
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // 2. Ambil ID menu yang berjenis Dine-In (jenis_menu_id = 1)
        $dineInMenuIds = \App\Models\Menu::where('jenis_menu_id', 1)->pluck('id');

        // 3. Hapus relasi yang terkait dengan menu Dine-In
        if ($dineInMenuIds->count() > 0) {
            \Illuminate\Support\Facades\DB::table('resep_menu')->whereIn('menu_id', $dineInMenuIds)->delete();
            \Illuminate\Support\Facades\DB::table('detail_pesanan')->whereIn('menu_id', $dineInMenuIds)->delete();
            \Illuminate\Support\Facades\DB::table('item_paket')->whereIn('menu_id', $dineInMenuIds)->delete();
            \Illuminate\Support\Facades\DB::table('pilihan_item_paket')->whereIn('menu_id', $dineInMenuIds)->delete();
            // Juga hapus jika ada yang jadi menu_id_terkait (menu satuan di dalam paket)
            \Illuminate\Support\Facades\DB::table('item_paket')->whereIn('menu_id_terkait', $dineInMenuIds)->update(['menu_id_terkait' => null]);
        }

        // 4. Hapus menu Dine-In dan ambil ID kategorinya
        $kategoriIds = \App\Models\Menu::where('jenis_menu_id', 1)->pluck('kategori_menu_id')->filter()->unique();
        \App\Models\Menu::where('jenis_menu_id', 1)->delete();
        
        // 5. Hapus kategori menu yang sebelumnya dipakai Dine-In
        if ($kategoriIds->count() > 0) {
            \App\Models\KategoriMenu::whereIn('id', $kategoriIds)->delete();
        }

        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 6. Baca dan Parse File Markdown
        $mdPath = base_path('docs/daftar_menu_dinein_lampiran.md');
        $md = file_get_contents($mdPath);
        $lines = explode("\n", $md);
        
        $categories = [];
        $currentCategory = null;

        foreach ($lines as $line) {
            // Regex untuk kategori: "## 1. Nama Kategori" atau "## 1. Nama Kategori — Info"
            if (preg_match('/^##\s+\d+\.\s+([^\—\n]+)/', trim($line), $matches)) {
                $currentCategory = trim($matches[1]);
                $categories[$currentCategory] = [];
            } 
            // Regex untuk item: "1. Nama Item — **RpXX.XXX**" atau "1. Nama Item — **RpXX.XXX/pcs**"
            elseif (preg_match('/^\d+\.\s+(.+?)\s+—\s+\*\*Rp([\d\.]+).*?\*\*$/', trim($line), $matches)) {
                if ($currentCategory) {
                    $name = trim($matches[1]);
                    // Hapus format miring markdown (*)
                    $name = str_replace('*', '', $name);
                    
                    $priceStr = str_replace('.', '', trim($matches[2]));
                    $price = (float) $priceStr;

                    $categories[$currentCategory][] = [
                        'name' => $name,
                        'price' => $price
                    ];
                }
            }
        }

        $kodePrefix = 'M-DINE-'; // Prefix kode menu
        $counter = 1;

        foreach ($categories as $catName => $items) {
            // Insert Kategori (Gunakan firstOrCreate agar tidak duplikat jika script dijalankan ulang)
            $kategori = \App\Models\KategoriMenu::firstOrCreate([
                'nama_kategori' => $catName,
            ]);

            foreach ($items as $item) {
                \App\Models\Menu::create([
                    'jenis_menu_id' => 1,
                    'kategori_menu_id' => $kategori->id,
                    'nama_menu' => $item['name'],
                    'harga_jual' => $item['price'],
                    'status_aktif' => true,
                ]);

                $counter++;
            }
        }

        $this->command->info("Berhasil mengimpor " . ($counter - 1) . " menu Dine-In.");
    }
}
