<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NasiBoxSeeder extends Seeder
{
    public function run()
    {
        $jenisMenuNB = 3; // Nasi Box

        // ==================== PAKET A (NB-A) ====================
        $paketA = DB::table('menu')->insertGetId([
            'nama_menu' => 'Nasi Box Paket A',
            'kode_menu' => 'NB-A',
            'deskripsi' => 'Paket lengkap dengan pilihan lauk ayam, ikan, dan buah',
            'harga_jual' => 47500,
            'jenis_menu_id' => $jenisMenuNB,
            'status_aktif' => true,
            'dibuat_pada' => now(),
            'diperbarui_pada' => now(),
        ]);

        // Komponen Paket A
        $itemA1 = DB::table('item_paket')->insertGetId(['menu_id' => $paketA, 'nama_item' => 'Nasi', 'tipe_item' => 'pilihan', 'urutan' => 1]);
        DB::table('pilihan_item_paket')->insert([['item_paket_id' => $itemA1, 'nama_pilihan' => 'Nasi Putih', 'urutan' => 1], ['item_paket_id' => $itemA1, 'nama_pilihan' => 'Nasi Liwet', 'urutan' => 2]]);

        $itemA2 = DB::table('item_paket')->insertGetId(['menu_id' => $paketA, 'nama_item' => 'Lauk Ayam', 'tipe_item' => 'pilihan', 'urutan' => 2]);
        DB::table('pilihan_item_paket')->insert([['item_paket_id' => $itemA2, 'nama_pilihan' => 'Ayam Goreng', 'urutan' => 1], ['item_paket_id' => $itemA2, 'nama_pilihan' => 'Ayam Bakar', 'urutan' => 2]]);

        $itemA3 = DB::table('item_paket')->insertGetId(['menu_id' => $paketA, 'nama_item' => 'Lauk Ikan', 'tipe_item' => 'pilihan', 'urutan' => 3]);
        DB::table('pilihan_item_paket')->insert([['item_paket_id' => $itemA3, 'nama_pilihan' => 'Ikan Goreng', 'urutan' => 1], ['item_paket_id' => $itemA3, 'nama_pilihan' => 'Lele Goreng', 'urutan' => 2]]);

        $itemA4 = DB::table('item_paket')->insertGetId(['menu_id' => $paketA, 'nama_item' => 'Lauk Tambahan', 'tipe_item' => 'pilihan', 'urutan' => 4]);
        DB::table('pilihan_item_paket')->insert([['item_paket_id' => $itemA4, 'nama_pilihan' => 'Telur Balado', 'urutan' => 1], ['item_paket_id' => $itemA4, 'nama_pilihan' => 'Kentang Balado', 'urutan' => 2]]);

        DB::table('item_paket')->insert(['menu_id' => $paketA, 'nama_item' => 'Sayuran: Karedok', 'tipe_item' => 'tetap', 'urutan' => 5]);

        DB::table('item_paket')->insert(['menu_id' => $paketA, 'nama_item' => 'Lalapan: Timun, Selada, Kemangi', 'tipe_item' => 'tetap', 'urutan' => 6]);

        DB::table('item_paket')->insert(['menu_id' => $paketA, 'nama_item' => 'Pelengkap: Sambal, Kerupuk', 'tipe_item' => 'tetap', 'urutan' => 7]);

        $itemA8 = DB::table('item_paket')->insertGetId(['menu_id' => $paketA, 'nama_item' => 'Buah', 'tipe_item' => 'pilihan', 'urutan' => 8]);
        DB::table('pilihan_item_paket')->insert([['item_paket_id' => $itemA8, 'nama_pilihan' => 'Melon', 'urutan' => 1], ['item_paket_id' => $itemA8, 'nama_pilihan' => 'Semangka', 'urutan' => 2], ['item_paket_id' => $itemA8, 'nama_pilihan' => 'Jeruk', 'urutan' => 3]]);

        DB::table('item_paket')->insert(['menu_id' => $paketA, 'nama_item' => 'Makanan Penutup: Puding', 'tipe_item' => 'tetap', 'urutan' => 9]);

        DB::table('item_paket')->insert(['menu_id' => $paketA, 'nama_item' => 'Minuman: Air Mineral', 'tipe_item' => 'tetap', 'urutan' => 10]);

        // ==================== PAKET B (NB-B) ====================
        $paketB = DB::table('menu')->insertGetId([
            'nama_menu' => 'Nasi Box Paket B',
            'kode_menu' => 'NB-B',
            'deskripsi' => 'Paket ekonomis dengan pilihan lauk utama',
            'harga_jual' => 35000,
            'jenis_menu_id' => $jenisMenuNB,
            'status_aktif' => true,
            'dibuat_pada' => now(),
            'diperbarui_pada' => now(),
        ]);

        $itemB1 = DB::table('item_paket')->insertGetId(['menu_id' => $paketB, 'nama_item' => 'Nasi', 'tipe_item' => 'pilihan', 'urutan' => 1]);
        DB::table('pilihan_item_paket')->insert([['item_paket_id' => $itemB1, 'nama_pilihan' => 'Nasi Putih', 'urutan' => 1], ['item_paket_id' => $itemB1, 'nama_pilihan' => 'Nasi Liwet', 'urutan' => 2]]);

        $itemB2 = DB::table('item_paket')->insertGetId(['menu_id' => $paketB, 'nama_item' => 'Lauk Utama', 'tipe_item' => 'pilihan', 'urutan' => 2]);
        DB::table('pilihan_item_paket')->insert([['item_paket_id' => $itemB2, 'nama_pilihan' => 'Ayam Goreng', 'urutan' => 1], ['item_paket_id' => $itemB2, 'nama_pilihan' => 'Ayam Bakar', 'urutan' => 2], ['item_paket_id' => $itemB2, 'nama_pilihan' => 'Ikan Goreng', 'urutan' => 3]]);

        DB::table('item_paket')->insert(['menu_id' => $paketB, 'nama_item' => 'Lauk Tambahan: Tempe, Tahu', 'tipe_item' => 'tetap', 'urutan' => 3]);

        DB::table('item_paket')->insert(['menu_id' => $paketB, 'nama_item' => 'Sayuran: Tumis Buncis Wortel', 'tipe_item' => 'tetap', 'urutan' => 4]);

        DB::table('item_paket')->insert(['menu_id' => $paketB, 'nama_item' => 'Lalapan: Timun, Selada, Kemangi', 'tipe_item' => 'tetap', 'urutan' => 5]);

        DB::table('item_paket')->insert(['menu_id' => $paketB, 'nama_item' => 'Pelengkap: Sambal, Kerupuk', 'tipe_item' => 'tetap', 'urutan' => 6]);

        $itemB7 = DB::table('item_paket')->insertGetId(['menu_id' => $paketB, 'nama_item' => 'Buah', 'tipe_item' => 'pilihan', 'urutan' => 7]);
        DB::table('pilihan_item_paket')->insert([['item_paket_id' => $itemB7, 'nama_pilihan' => 'Melon', 'urutan' => 1], ['item_paket_id' => $itemB7, 'nama_pilihan' => 'Semangka', 'urutan' => 2], ['item_paket_id' => $itemB7, 'nama_pilihan' => 'Jeruk', 'urutan' => 3]]);

        DB::table('item_paket')->insert(['menu_id' => $paketB, 'nama_item' => 'Minuman: Air Mineral', 'tipe_item' => 'tetap', 'urutan' => 8]);

        // ==================== PAKET C (NB-C) ====================
        $paketC = DB::table('menu')->insertGetId([
            'nama_menu' => 'Nasi Box Paket C',
            'kode_menu' => 'NB-C',
            'deskripsi' => 'Paket hemat dengan tumis buncis wortel',
            'harga_jual' => 30000,
            'jenis_menu_id' => $jenisMenuNB,
            'status_aktif' => true,
            'dibuat_pada' => now(),
            'diperbarui_pada' => now(),
        ]);

        DB::table('item_paket')->insert(['menu_id' => $paketC, 'nama_item' => 'Nasi: Nasi Putih', 'tipe_item' => 'tetap', 'urutan' => 1]);

        $itemC2 = DB::table('item_paket')->insertGetId(['menu_id' => $paketC, 'nama_item' => 'Lauk Utama', 'tipe_item' => 'pilihan', 'urutan' => 2]);
        DB::table('pilihan_item_paket')->insert([['item_paket_id' => $itemC2, 'nama_pilihan' => 'Ayam Goreng', 'urutan' => 1], ['item_paket_id' => $itemC2, 'nama_pilihan' => 'Ayam Bakar', 'urutan' => 2], ['item_paket_id' => $itemC2, 'nama_pilihan' => 'Ikan Goreng', 'urutan' => 3]]);

        $itemC3 = DB::table('item_paket')->insertGetId(['menu_id' => $paketC, 'nama_item' => 'Lauk Tambahan', 'tipe_item' => 'pilihan', 'urutan' => 3]);
        DB::table('pilihan_item_paket')->insert([['item_paket_id' => $itemC3, 'nama_pilihan' => 'Tempe', 'urutan' => 1], ['item_paket_id' => $itemC3, 'nama_pilihan' => 'Tahu', 'urutan' => 2]]);

        DB::table('item_paket')->insert(['menu_id' => $paketC, 'nama_item' => 'Sayuran: Cah Brokoli Wortel', 'tipe_item' => 'tetap', 'urutan' => 4]);

        DB::table('item_paket')->insert(['menu_id' => $paketC, 'nama_item' => 'Lalapan: Timun, Selada, Kemangi', 'tipe_item' => 'tetap', 'urutan' => 5]);

        DB::table('item_paket')->insert(['menu_id' => $paketC, 'nama_item' => 'Pelengkap: Sambal, Kerupuk', 'tipe_item' => 'tetap', 'urutan' => 6]);

        $itemC7 = DB::table('item_paket')->insertGetId(['menu_id' => $paketC, 'nama_item' => 'Buah', 'tipe_item' => 'pilihan', 'urutan' => 7]);
        DB::table('pilihan_item_paket')->insert([['item_paket_id' => $itemC7, 'nama_pilihan' => 'Melon', 'urutan' => 1], ['item_paket_id' => $itemC7, 'nama_pilihan' => 'Semangka', 'urutan' => 2], ['item_paket_id' => $itemC7, 'nama_pilihan' => 'Jeruk', 'urutan' => 3]]);

        DB::table('item_paket')->insert(['menu_id' => $paketC, 'nama_item' => 'Minuman: Air Mineral', 'tipe_item' => 'tetap', 'urutan' => 8]);

        // ==================== PAKET D (NB-D) ====================
        $paketD = DB::table('menu')->insertGetId([
            'nama_menu' => 'Nasi Box Paket D',
            'kode_menu' => 'NB-D',
            'deskripsi' => 'Paket paling hemat',
            'harga_jual' => 25000,
            'jenis_menu_id' => $jenisMenuNB,
            'status_aktif' => true,
            'dibuat_pada' => now(),
            'diperbarui_pada' => now(),
        ]);

        DB::table('item_paket')->insert(['menu_id' => $paketD, 'nama_item' => 'Nasi: Nasi Putih', 'tipe_item' => 'tetap', 'urutan' => 1]);

        $itemD2 = DB::table('item_paket')->insertGetId(['menu_id' => $paketD, 'nama_item' => 'Lauk Utama', 'tipe_item' => 'pilihan', 'urutan' => 2]);
        DB::table('pilihan_item_paket')->insert([['item_paket_id' => $itemD2, 'nama_pilihan' => 'Ayam Goreng', 'urutan' => 1], ['item_paket_id' => $itemD2, 'nama_pilihan' => 'Ayam Bakar', 'urutan' => 2]]);

        $itemD3 = DB::table('item_paket')->insertGetId(['menu_id' => $paketD, 'nama_item' => 'Lauk Tambahan', 'tipe_item' => 'pilihan', 'urutan' => 3]);
        DB::table('pilihan_item_paket')->insert([['item_paket_id' => $itemD3, 'nama_pilihan' => 'Tempe', 'urutan' => 1], ['item_paket_id' => $itemD3, 'nama_pilihan' => 'Tahu', 'urutan' => 2]]);

        DB::table('item_paket')->insert(['menu_id' => $paketD, 'nama_item' => 'Sayuran: Capcay', 'tipe_item' => 'tetap', 'urutan' => 4]);

        DB::table('item_paket')->insert(['menu_id' => $paketD, 'nama_item' => 'Lalapan: Timun, Selada, Kemangi', 'tipe_item' => 'tetap', 'urutan' => 5]);

        DB::table('item_paket')->insert(['menu_id' => $paketD, 'nama_item' => 'Pelengkap: Sambal, Kerupuk', 'tipe_item' => 'tetap', 'urutan' => 6]);

        DB::table('item_paket')->insert(['menu_id' => $paketD, 'nama_item' => 'Minuman: Air Mineral', 'tipe_item' => 'tetap', 'urutan' => 7]);

        // ==================== PAKET E (NB-E) ====================
        $paketE = DB::table('menu')->insertGetId([
            'nama_menu' => 'Nasi Box Paket E',
            'kode_menu' => 'NB-E',
            'deskripsi' => 'Paket super hemat',
            'harga_jual' => 20000,
            'jenis_menu_id' => $jenisMenuNB,
            'status_aktif' => true,
            'dibuat_pada' => now(),
            'diperbarui_pada' => now(),
        ]);

        DB::table('item_paket')->insert(['menu_id' => $paketE, 'nama_item' => 'Nasi: Nasi Putih', 'tipe_item' => 'tetap', 'urutan' => 1]);

        $itemE2 = DB::table('item_paket')->insertGetId(['menu_id' => $paketE, 'nama_item' => 'Lauk Utama', 'tipe_item' => 'pilihan', 'urutan' => 2]);
        DB::table('pilihan_item_paket')->insert([['item_paket_id' => $itemE2, 'nama_pilihan' => 'Ayam Goreng', 'urutan' => 1], ['item_paket_id' => $itemE2, 'nama_pilihan' => 'Ayam Bakar', 'urutan' => 2]]);

        DB::table('item_paket')->insert(['menu_id' => $paketE, 'nama_item' => 'Lalapan: Timun, Selada, Kemangi', 'tipe_item' => 'tetap', 'urutan' => 3]);

        DB::table('item_paket')->insert(['menu_id' => $paketE, 'nama_item' => 'Pelengkap: Sambal, Kerupuk', 'tipe_item' => 'tetap', 'urutan' => 4]);

        DB::table('item_paket')->insert(['menu_id' => $paketE, 'nama_item' => 'Minuman: Air Mineral', 'tipe_item' => 'tetap', 'urutan' => 5]);
    }
}