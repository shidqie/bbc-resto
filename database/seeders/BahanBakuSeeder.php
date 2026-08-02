<?php

namespace Database\Seeders;

use App\Models\BahanBaku;
use App\Models\KategoriBahan;
use App\Models\Satuan;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class BahanBakuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Kategori Bahan
        $kategoriPokok = KategoriBahan::create(['nama_kategori' => 'Bahan Pokok']);
        $kategoriDaging = KategoriBahan::create(['nama_kategori' => 'Daging']);
        $kategoriSayuran = KategoriBahan::create(['nama_kategori' => 'Sayuran dan Bumbu']);
        $kategoriSaus = KategoriBahan::create(['nama_kategori' => 'Saus dan Kecap']);

        // 2. Satuan
        $satuanKg = Satuan::create(['nama_satuan' => 'Kilogram', 'singkatan' => 'kg']);
        $satuanLiter = Satuan::create(['nama_satuan' => 'Liter', 'singkatan' => 'L']);
        $satuanBotol = Satuan::create(['nama_satuan' => 'Botol', 'singkatan' => 'btl']);

        // 3. Supplier (optional example)
        $supplierBerkah = Supplier::create([
            'nama_supplier' => 'Supplier Berkah',
            'kontak' => '08123456789',
            'alamat' => 'Jl. Pasar Induk No. 1',
        ]);

        // 4. Bahan Baku Example Data
        BahanBaku::create([
            'kode_bahan' => 'BB-001',
            'kategori_bahan_id' => $kategoriPokok->id,
            'supplier_id' => $supplierBerkah->id,
            'nama_bahan' => 'Beras',
            'satuan_id' => $satuanKg->id,
            'stok' => 25,
            'stok_minimum' => 10,
            'harga_terakhir' => 15000,
            'status' => true,
        ]);

        BahanBaku::create([
            'kode_bahan' => 'BB-002',
            'kategori_bahan_id' => $kategoriDaging->id,
            'supplier_id' => null,
            'nama_bahan' => 'Ayam',
            'satuan_id' => $satuanKg->id,
            'stok' => 8,
            'stok_minimum' => 10,
            'harga_terakhir' => 35000,
            'status' => true,
        ]);

        BahanBaku::create([
            'kode_bahan' => 'BB-003',
            'kategori_bahan_id' => $kategoriPokok->id,
            'supplier_id' => null,
            'nama_bahan' => 'Minyak Goreng',
            'satuan_id' => $satuanLiter->id,
            'stok' => 0,
            'stok_minimum' => 5,
            'harga_terakhir' => 18000,
            'status' => true,
        ]);

        BahanBaku::create([
            'kode_bahan' => 'BB-004',
            'kategori_bahan_id' => $kategoriSayuran->id,
            'supplier_id' => null,
            'nama_bahan' => 'Cabai Merah',
            'satuan_id' => $satuanKg->id,
            'stok' => 4,
            'stok_minimum' => 3,
            'harga_terakhir' => 50000,
            'status' => true,
        ]);

        BahanBaku::create([
            'kode_bahan' => 'BB-005',
            'kategori_bahan_id' => $kategoriSaus->id,
            'supplier_id' => null,
            'nama_bahan' => 'Kecap Manis',
            'satuan_id' => $satuanBotol->id,
            'stok' => 12,
            'stok_minimum' => 5,
            'harga_terakhir' => 25000,
            'status' => true,
        ]);
    }
}
