<?php

namespace Database\Seeders;

use App\Models\ItemPaket;
use App\Models\KategoriMenu;
use App\Models\KetentuanPaket;
use App\Models\Menu;
use App\Models\PilihanItemPaket;
use Illuminate\Database\Seeder;

class NasiBoxSeeder extends Seeder
{
    public function run(): void
    {
        $kategoriNasibox = KategoriMenu::firstOrCreate(
            ['nama_kategori' => 'Nasi Box'],
            ['status_aktif' => true]
        );

        $dataPaket = [
            [
                'nama' => 'Nasi Box Paket A',
                'kode' => 'NB-A',
                'harga' => 47500,
                'deskripsi' => 'Paket nasi box paling lengkap untuk rapat, seminar, dan acara kantor.',
                'item' => [
                    ['nama' => 'Nasi Putih/Liwet', 'tipe' => 'tetap', 'urutan' => 1, 'pilihan' => []],
                    ['nama' => 'Ayam Goreng/Bakar', 'tipe' => 'tetap', 'urutan' => 2, 'pilihan' => []],
                    ['nama' => 'Ikan/Lele Goreng', 'tipe' => 'tetap', 'urutan' => 3, 'pilihan' => []],
                    ['nama' => 'Telur Balado/Kentang Balado', 'tipe' => 'tetap', 'urutan' => 4, 'pilihan' => []],
                    ['nama' => 'Karedok', 'tipe' => 'tetap', 'urutan' => 5, 'pilihan' => []],
                    ['nama' => 'Lalapan', 'tipe' => 'tetap', 'urutan' => 6, 'pilihan' => []],
                    ['nama' => 'Sambal', 'tipe' => 'tetap', 'urutan' => 7, 'pilihan' => []],
                    ['nama' => 'Kerupuk', 'tipe' => 'tetap', 'urutan' => 8, 'pilihan' => []],
                    ['nama' => 'Buah Potong', 'tipe' => 'tetap', 'urutan' => 9, 'pilihan' => []],
                    ['nama' => 'Puding', 'tipe' => 'tetap', 'urutan' => 10, 'pilihan' => []],
                    ['nama' => 'Air Mineral', 'tipe' => 'tetap', 'urutan' => 11, 'pilihan' => []],
                ],
            ],
            [
                'nama' => 'Nasi Box Paket B',
                'kode' => 'NB-B',
                'harga' => 35000,
                'deskripsi' => 'Paket nasi box istimewa dengan pilihan aneka lauk yang menggugah selera.',
                'item' => [
                    ['nama' => 'Pilihan Nasi', 'tipe' => 'pilihan', 'urutan' => 1, 'pilihan' => ['Nasi Putih', 'Nasi Liwet']],
                    ['nama' => 'Pilihan Lauk Utama', 'tipe' => 'pilihan', 'urutan' => 2, 'pilihan' => ['Ayam Goreng', 'Ayam Bakar', 'Ikan Goreng']],
                    ['nama' => 'Lauk Tambahan', 'tipe' => 'tetap', 'urutan' => 3, 'pilihan' => ['Tempe, Tahu']],
                    ['nama' => 'Sayuran', 'tipe' => 'tetap', 'urutan' => 4, 'pilihan' => ['Tumis Buncis Wortel']],
                    ['nama' => 'Lalapan', 'tipe' => 'tetap', 'urutan' => 5, 'pilihan' => ['Timun, Selada, Kemangi']],
                    ['nama' => 'Pelengkap', 'tipe' => 'tetap', 'urutan' => 6, 'pilihan' => ['Sambal, Kerupuk']],
                    ['nama' => 'Pilihan Buah', 'tipe' => 'pilihan', 'urutan' => 7, 'pilihan' => ['Melon', 'Semangka', 'Jeruk']],
                    ['nama' => 'Minuman', 'tipe' => 'tetap', 'urutan' => 8, 'pilihan' => ['Air Mineral']],
                ],
            ],
            [
                'nama' => 'Nasi Box Paket C',
                'kode' => 'NB-C',
                'harga' => 30000,
                'deskripsi' => 'Paket nasi box hemat dengan aneka lauk pilihan yang lezat.',
                'item' => [
                    ['nama' => 'Nasi', 'tipe' => 'tetap', 'urutan' => 1, 'pilihan' => ['Nasi Putih']],
                    ['nama' => 'Pilihan Lauk Utama', 'tipe' => 'pilihan', 'urutan' => 2, 'pilihan' => ['Ayam Goreng', 'Ayam Bakar', 'Ikan Goreng']],
                    ['nama' => 'Pilihan Lauk Tambahan', 'tipe' => 'pilihan', 'urutan' => 3, 'pilihan' => ['Tempe', 'Tahu']],
                    ['nama' => 'Sayuran', 'tipe' => 'tetap', 'urutan' => 4, 'pilihan' => ['Cah Brokoli Wortel']],
                    ['nama' => 'Lalapan', 'tipe' => 'tetap', 'urutan' => 5, 'pilihan' => ['Timun, Selada, Kemangi']],
                    ['nama' => 'Pelengkap', 'tipe' => 'tetap', 'urutan' => 6, 'pilihan' => ['Sambal, Kerupuk']],
                    ['nama' => 'Pilihan Buah', 'tipe' => 'pilihan', 'urutan' => 7, 'pilihan' => ['Melon', 'Semangka', 'Jeruk']],
                    ['nama' => 'Minuman', 'tipe' => 'tetap', 'urutan' => 8, 'pilihan' => ['Air Mineral']],
                ],
            ],
            [
                'nama' => 'Nasi Box Paket D',
                'kode' => 'NB-D',
                'harga' => 25000,
                'deskripsi' => 'Paket nasi box praktis dan mengenyangkan untuk santap siang.',
                'item' => [
                    ['nama' => 'Nasi', 'tipe' => 'tetap', 'urutan' => 1, 'pilihan' => ['Nasi Putih']],
                    ['nama' => 'Pilihan Lauk Utama', 'tipe' => 'pilihan', 'urutan' => 2, 'pilihan' => ['Ayam Goreng', 'Ayam Bakar']],
                    ['nama' => 'Pilihan Lauk Tambahan', 'tipe' => 'pilihan', 'urutan' => 3, 'pilihan' => ['Tempe', 'Tahu']],
                    ['nama' => 'Sayuran', 'tipe' => 'tetap', 'urutan' => 4, 'pilihan' => ['Capcay']],
                    ['nama' => 'Lalapan', 'tipe' => 'tetap', 'urutan' => 5, 'pilihan' => ['Timun, Selada, Kemangi']],
                    ['nama' => 'Pelengkap', 'tipe' => 'tetap', 'urutan' => 6, 'pilihan' => ['Sambal, Kerupuk']],
                    ['nama' => 'Minuman', 'tipe' => 'tetap', 'urutan' => 7, 'pilihan' => ['Air Mineral']],
                ],
            ],
            [
                'nama' => 'Nasi Box Paket E',
                'kode' => 'NB-E',
                'harga' => 20000,
                'deskripsi' => 'Paket nasi box ekonomis untuk kebutuhan acara harian.',
                'item' => [
                    ['nama' => 'Nasi', 'tipe' => 'tetap', 'urutan' => 1, 'pilihan' => ['Nasi Putih']],
                    ['nama' => 'Pilihan Lauk Utama', 'tipe' => 'pilihan', 'urutan' => 2, 'pilihan' => ['Ayam Goreng', 'Ayam Bakar']],
                    ['nama' => 'Lalapan', 'tipe' => 'tetap', 'urutan' => 3, 'pilihan' => ['Timun, Selada, Kemangi']],
                    ['nama' => 'Pelengkap', 'tipe' => 'tetap', 'urutan' => 4, 'pilihan' => ['Sambal, Kerupuk']],
                    ['nama' => 'Minuman', 'tipe' => 'tetap', 'urutan' => 5, 'pilihan' => ['Air Mineral']],
                ],
            ],
        ];

        foreach ($dataPaket as $p) {
            $paket = Menu::updateOrCreate(
                ['kode_menu' => $p['kode']],
                [
                    'nama_menu' => $p['nama'],
                    'jenis_menu_id' => 3, // NASI_BOX
                    'kategori_menu_id' => $kategoriNasibox->id,
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

        $this->command->info('Paket Nasi Box A, B, C, D, E berhasil disiapkan.');
    }
}
