<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\KategoriMenu;
use App\Models\PaketCatering;
use App\Models\KomponenPaket;
use App\Models\OpsiKomponen;

class NasiBoxSeeder extends Seeder
{
    public function run(): void
    {
        $kategoriNasibox = KategoriMenu::firstOrCreate(['nama' => 'Nasi Box']);

        $menuNames = [
            'Nasi Putih', 'Ayam Goreng', 'Ayam Bakar', 'Rendang Daging',
            'Tahu Goreng', 'Tempe Goreng', 'Sayur Asem', 'Sayur Nangka', 'Perkedel Kentang',
            'Sambal Terasi', 'Sambal Ijo', 'Lalapan', 'Pisang', 'Jeruk', 'Air Mineral Gelas'
        ];

        $menuIds = [];
        foreach ($menuNames as $nama) {
            $menu = Menu::firstOrCreate(
                ['nama' => $nama, 'jenis_menu' => 'catering'],
                [
                    'harga' => 5000, 
                    'status' => 'tersedia',
                    'kategori_menu_id' => $kategoriNasibox->id
                ]
            );
            $menuIds[$nama] = $menu->id;
        }

        $addComponent = function ($paketId, $namaKomponen, $tipe, $urutan, $opsiMenu) use ($menuIds) {
            $komp = KomponenPaket::create([
                'paket_catering_id' => $paketId,
                'nama_komponen' => $namaKomponen,
                'tipe' => $tipe,
                'urutan' => $urutan
            ]);

            foreach ($opsiMenu as $namaMenu) {
                OpsiKomponen::create([
                    'komponen_paket_id' => $komp->id,
                    'menu_id' => $menuIds[$namaMenu]
                ]);
            }
        };

        // PAKET A
        $paketA = PaketCatering::firstOrCreate([
            'nama_paket' => 'Paket Nasi Box A',
            'jenis_paket' => 'nasi_box'
        ], [
            'harga' => 25000,
            'deskripsi' => 'Paket hemat ayam goreng dengan tahu/tempe, lengkap dengan sambal dan lalapan.',
            'is_active' => true
        ]);
        // hapus komponen lama kalau ada
        $paketA->komponens()->delete();

        $addComponent($paketA->id, 'Nasi', 'fixed', 1, ['Nasi Putih']);
        $addComponent($paketA->id, 'Lauk Utama', 'choice', 2, ['Ayam Goreng']);
        $addComponent($paketA->id, 'Lauk Pendamping', 'choice', 3, ['Tahu Goreng', 'Tempe Goreng']);
        $addComponent($paketA->id, 'Pelengkap', 'fixed', 4, ['Sambal Terasi', 'Lalapan']);

        // PAKET B
        $paketB = PaketCatering::firstOrCreate([
            'nama_paket' => 'Paket Nasi Box B',
            'jenis_paket' => 'nasi_box'
        ], [
            'harga' => 30000,
            'deskripsi' => 'Paket lengkap ayam bakar dan sayur asem segar, ditambah buah pencuci mulut.',
            'is_active' => true
        ]);
        $paketB->komponens()->delete();

        $addComponent($paketB->id, 'Nasi', 'fixed', 1, ['Nasi Putih']);
        $addComponent($paketB->id, 'Lauk Utama', 'fixed', 2, ['Ayam Bakar']);
        $addComponent($paketB->id, 'Sayur', 'fixed', 3, ['Sayur Asem']);
        $addComponent($paketB->id, 'Lauk Pendamping', 'choice', 4, ['Tahu Goreng', 'Tempe Goreng']);
        $addComponent($paketB->id, 'Pelengkap', 'fixed', 5, ['Sambal Terasi', 'Lalapan']);
        $addComponent($paketB->id, 'Buah', 'choice', 6, ['Pisang', 'Jeruk']);

        // PAKET C
        $paketC = PaketCatering::firstOrCreate([
            'nama_paket' => 'Paket Nasi Box C',
            'jenis_paket' => 'nasi_box'
        ], [
            'harga' => 35000,
            'deskripsi' => 'Paket premium dengan rendang daging sapi empuk, sayur nangka khas padang, dan perkedel.',
            'is_active' => true
        ]);
        $paketC->komponens()->delete();

        $addComponent($paketC->id, 'Nasi', 'fixed', 1, ['Nasi Putih']);
        $addComponent($paketC->id, 'Lauk Utama', 'fixed', 2, ['Rendang Daging']);
        $addComponent($paketC->id, 'Sayur', 'fixed', 3, ['Sayur Nangka']);
        $addComponent($paketC->id, 'Lauk Pendamping', 'fixed', 4, ['Perkedel Kentang']);
        $addComponent($paketC->id, 'Pelengkap', 'fixed', 5, ['Sambal Ijo']);
        $addComponent($paketC->id, 'Buah', 'choice', 6, ['Pisang', 'Jeruk']);
        $addComponent($paketC->id, 'Minuman', 'fixed', 7, ['Air Mineral Gelas']);
    }
}
