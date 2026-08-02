<?php

namespace Database\Seeders;

use App\Models\KategoriMenu;
use App\Models\KomponenPaket;
use App\Models\Menu;
use App\Models\OpsiKomponen;
use App\Models\PaketCatering;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CateringBOMSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        PaketCatering::truncate();
        KomponenPaket::truncate();
        OpsiKomponen::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $kategoriCatering = KategoriMenu::firstOrCreate(['nama' => 'Catering']);

        // Create all menus needed
        $menuNames = [
            'Nasi putih',
            'Sup kimlo', 'Sup bakso', 'Sup ayam sosis', 'Sup sosis',
            'Sapi teriyaki', 'Rendang', 'Bistik',
            'Ayam teriyaki', 'Ayam suwir', 'Ayam rica-rica',
            'Dori asam manis', 'Dori saus mentega', 'Sambal goreng ati kentang',
            'Salad buah', 'Salad sayuran', 'Gado-gado', 'Rujak', 'Rujak buah',
            'Kerupuk udang',
            'Air mineral',
            'Bakso tahu', 'Mi kocok',
            'Buah potong', 'Es krim',
        ];

        $menuIds = [];
        foreach ($menuNames as $nama) {
            $menu = Menu::firstOrCreate(
                ['nama' => $nama, 'jenis_menu' => 'catering'],
                [
                    'harga' => 10000, // Default price
                    'status' => 'tersedia',
                    'kategori_menu_id' => $kategoriCatering->id,
                ]
            );
            $menuIds[$nama] = $menu->id;
        }

        // Helper to add component and options
        $addComponent = function ($paketId, $namaKomponen, $tipe, $urutan, $opsiMenu) use ($menuIds) {
            $komp = KomponenPaket::create([
                'paket_catering_id' => $paketId,
                'nama_komponen' => $namaKomponen,
                'tipe' => $tipe,
                'urutan' => $urutan,
            ]);

            foreach ($opsiMenu as $namaMenu) {
                OpsiKomponen::create([
                    'komponen_paket_id' => $komp->id,
                    'menu_id' => $menuIds[$namaMenu],
                ]);
            }
        };

        // PAKET A
        $paketA = PaketCatering::create([
            'nama_paket' => 'Paket A',
            'jenis_paket' => 'catering',
            'harga' => 47500,
            'deskripsi' => 'Paket prasmanan lengkap dengan pilihan daging sapi',
            'is_active' => true,
        ]);

        $addComponent($paketA->id, 'Nasi', 'fixed', 1, ['Nasi putih']);
        $addComponent($paketA->id, 'Aneka sup', 'choice', 2, ['Sup kimlo', 'Sup bakso', 'Sup ayam sosis']);
        $addComponent($paketA->id, 'Aneka olahan daging sapi', 'choice', 3, ['Sapi teriyaki', 'Rendang', 'Bistik']);
        $addComponent($paketA->id, 'Aneka olahan tambahan', 'choice', 4, ['Dori asam manis', 'Dori saus mentega', 'Sambal goreng ati kentang']);
        $addComponent($paketA->id, 'Sayuran', 'choice', 5, ['Salad buah', 'Salad sayuran', 'Gado-gado', 'Rujak']);
        $addComponent($paketA->id, 'Kerupuk', 'fixed', 6, ['Kerupuk udang']);
        $addComponent($paketA->id, 'Minuman', 'fixed', 7, ['Air mineral']);
        $addComponent($paketA->id, 'Stall', 'choice', 8, ['Bakso tahu', 'Mi kocok']);
        $addComponent($paketA->id, 'Dessert', 'choice', 9, ['Buah potong', 'Es krim']);

        // PAKET B
        $paketB = PaketCatering::create([
            'nama_paket' => 'Paket B',
            'jenis_paket' => 'catering',
            'harga' => 42500,
            'deskripsi' => 'Paket prasmanan dengan pilihan aneka olahan ayam',
            'is_active' => true,
        ]);

        $addComponent($paketB->id, 'Nasi', 'fixed', 1, ['Nasi putih']);
        $addComponent($paketB->id, 'Aneka sup', 'choice', 2, ['Sup kimlo', 'Sup bakso', 'Sup sosis']);
        $addComponent($paketB->id, 'Aneka olahan ayam', 'choice', 3, ['Ayam teriyaki', 'Ayam suwir', 'Ayam rica-rica']);
        $addComponent($paketB->id, 'Aneka olahan tambahan', 'choice', 4, ['Dori asam manis', 'Dori saus mentega', 'Sambal goreng ati kentang']);
        $addComponent($paketB->id, 'Sayuran', 'choice', 5, ['Salad buah', 'Salad sayuran', 'Gado-gado', 'Rujak buah']);
        $addComponent($paketB->id, 'Kerupuk', 'fixed', 6, ['Kerupuk udang']);
        $addComponent($paketB->id, 'Minuman', 'fixed', 7, ['Air mineral']);
        $addComponent($paketB->id, 'Stall', 'choice', 8, ['Bakso tahu', 'Mi kocok']);
        $addComponent($paketB->id, 'Dessert', 'choice', 9, ['Buah potong', 'Es krim']);
    }
}
