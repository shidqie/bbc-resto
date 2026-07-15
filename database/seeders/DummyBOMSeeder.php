<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\ResepMenu;

class DummyBOMSeeder extends Seeder
{
    public function run(): void
    {
        // Berikan BOM dummy untuk semua menu catering/nasi box yang belum punya BOM
        $menus = Menu::whereIn('jenis_menu', ['catering', 'nasi_box'])->get();
        // Daftar ID bahan baku yang ada (berdasarkan hasil query sebelumnya)
        $bahanIds = [1, 2, 3, 4, 5];

        foreach ($menus as $menu) {
            $count = ResepMenu::where('menu_id', $menu->id)->count();
            if ($count == 0) {
                $randomBahan = array_rand(array_flip($bahanIds), 2);
                foreach ($randomBahan as $bId) {
                    ResepMenu::create([
                        'menu_id' => $menu->id,
                        'bahan_baku_id' => $bId,
                        'jumlah_kebutuhan' => rand(1, 5) / 10 // 0.1 to 0.5
                    ]);
                }
            }
        }
    }
}
