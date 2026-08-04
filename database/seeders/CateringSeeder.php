<?php

namespace Database\Seeders;

use App\Models\ItemPaket;
use App\Models\KategoriMenu;
use App\Models\Menu;
use App\Models\PilihanItemPaket;
use Illuminate\Database\Seeder;

class CateringSeeder extends Seeder
{
    public function run(): void
    {
        $kategoriCatering = KategoriMenu::firstOrCreate(
            ['nama_kategori' => 'Catering'],
            ['status_aktif' => true]
        );

        $dataPaket = [
            [
                'nama' => 'Paket A',
                'kode' => 'CAT001',
                'harga' => 47500,
                'deskripsi' => 'Paket prasmanan lengkap dengan pilihan aneka olahan daging sapi.',
                'item' => [
                    ['nama' => 'Nasi', 'tipe' => 'tetap', 'urutan' => 1, 'pilihan' => ['Nasi Putih']],
                    ['nama' => 'Aneka Sup', 'tipe' => 'pilihan', 'urutan' => 2, 'pilihan' => ['Sup Kimlo', 'Sup Bakso', 'Sup Ayam Sosis']],
                    ['nama' => 'Aneka Olahan Daging Sapi', 'tipe' => 'pilihan', 'urutan' => 3, 'pilihan' => ['Sapi Teriyaki', 'Rendang', 'Bistik']],
                    ['nama' => 'Aneka Olahan Tambahan', 'tipe' => 'pilihan', 'urutan' => 4, 'pilihan' => ['Dori Asam Manis', 'Dori Saus Mentega', 'Sambal Goreng Ati Kentang']],
                    ['nama' => 'Sayuran', 'tipe' => 'pilihan', 'urutan' => 5, 'pilihan' => ['Salad Buah', 'Salad Sayuran', 'Gado-Gado', 'Rujak']],
                    ['nama' => 'Kerupuk', 'tipe' => 'tetap', 'urutan' => 6, 'pilihan' => ['Kerupuk Udang']],
                    ['nama' => 'Minuman', 'tipe' => 'tetap', 'urutan' => 7, 'pilihan' => ['Air Mineral']],
                    ['nama' => 'Stall', 'tipe' => 'pilihan', 'urutan' => 8, 'pilihan' => ['Bakso Tahu', 'Mi Kocok']],
                    ['nama' => 'Dessert', 'tipe' => 'pilihan', 'urutan' => 9, 'pilihan' => ['Buah Potong', 'Es Krim']],
                ],
            ],
            [
                'nama' => 'Paket B',
                'kode' => 'CAT002',
                'harga' => 42500,
                'deskripsi' => 'Paket prasmanan dengan pilihan aneka olahan ayam.',
                'item' => [
                    ['nama' => 'Nasi', 'tipe' => 'tetap', 'urutan' => 1, 'pilihan' => ['Nasi Putih']],
                    ['nama' => 'Aneka Sup', 'tipe' => 'pilihan', 'urutan' => 2, 'pilihan' => ['Sup Kimlo', 'Sup Bakso', 'Sup Sosis']],
                    ['nama' => 'Aneka Olahan Ayam', 'tipe' => 'pilihan', 'urutan' => 3, 'pilihan' => ['Ayam Teriyaki', 'Ayam Suwir', 'Ayam Rica-Rica']],
                    ['nama' => 'Aneka Olahan Tambahan', 'tipe' => 'pilihan', 'urutan' => 4, 'pilihan' => ['Dori Asam Manis', 'Dori Saus Mentega', 'Sambal Goreng Ati Kentang']],
                    ['nama' => 'Sayuran', 'tipe' => 'pilihan', 'urutan' => 5, 'pilihan' => ['Salad Buah', 'Salad Sayuran', 'Gado-Gado', 'Rujak Buah']],
                    ['nama' => 'Kerupuk', 'tipe' => 'tetap', 'urutan' => 6, 'pilihan' => ['Kerupuk Udang']],
                    ['nama' => 'Minuman', 'tipe' => 'tetap', 'urutan' => 7, 'pilihan' => ['Air Mineral']],
                    ['nama' => 'Stall', 'tipe' => 'pilihan', 'urutan' => 8, 'pilihan' => ['Bakso Tahu', 'Mi Kocok']],
                    ['nama' => 'Dessert', 'tipe' => 'pilihan', 'urutan' => 9, 'pilihan' => ['Buah Potong', 'Es Krim']],
                ],
            ],
        ];

        foreach ($dataPaket as $p) {
            $paket = Menu::updateOrCreate(
                ['kode_menu' => $p['kode']],
                [
                    'nama_menu' => $p['nama'],
                    'jenis_menu_id' => 2, // CATERING
                    'kategori_menu_id' => $kategoriCatering->id,
                    'harga_jual' => $p['harga'],
                    'deskripsi' => $p['deskripsi'],
                    'status_aktif' => true,
                ]
            );

            // Hapus item lama lalu buat ulang agar seeder idempoten & selalu terbaru
            $paket->item_paket()->delete();

            foreach ($p['item'] as $idx => $k) {
                $tipe = $k['tipe'] === 'pilihan' ? 'pilihan' : 'tetap';
                $item = ItemPaket::create([
                    'menu_id' => $paket->id,
                    'nama_item' => $k['nama'],
                    'tipe_item' => $tipe,
                    'minimum_pilihan' => $tipe === 'pilihan' ? 1 : 0,
                    'maksimum_pilihan' => $tipe === 'pilihan' ? 1 : 0,
                    'urutan' => $k['urutan'],
                ]);

                foreach ($k['pilihan'] as $i => $namaPilihan) {
                    PilihanItemPaket::create([
                        'item_paket_id' => $item->id,
                        'nama_pilihan' => $namaPilihan,
                        'urutan' => $i + 1,
                    ]);
                }
            }
        }

        $this->command->info('Paket Catering A & B berhasil disiapkan.');
    }
}
