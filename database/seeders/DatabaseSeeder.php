<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // 1. Peran & Pengguna (7 role: Pemilik, Manajer, Kasir, Pelayan, Dapur, Pengantaran, Pelanggan)
        $this->call(UserRoleSeeder::class);

        // 3. Status Meja
        $status_meja = [
            ['kode_status' => 'TERSEDIA', 'nama_status' => 'Tersedia'],
            ['kode_status' => 'TERISI', 'nama_status' => 'Terisi'],
            ['kode_status' => 'DIPESAN', 'nama_status' => 'Dipesan'],
            ['kode_status' => 'TIDAK_AKTIF', 'nama_status' => 'Tidak Aktif'],
        ];
        DB::table('status_meja')->insert($status_meja);

        // Meja Dummy
        DB::table('meja')->insert([
            ['id' => 1, 'nomor_meja' => 'Meja 01', 'kapasitas' => 4, 'status_meja_id' => 1],
            ['id' => 2, 'nomor_meja' => 'Meja 02', 'kapasitas' => 4, 'status_meja_id' => 1],
            ['id' => 3, 'nomor_meja' => 'Meja 03', 'kapasitas' => 2, 'status_meja_id' => 1],
        ]);

        // 4. Jenis Menu
        $jenis_menu = [
            ['kode_jenis' => 'REGULER', 'nama_jenis' => 'Menu Reguler'],
            ['kode_jenis' => 'CATERING', 'nama_jenis' => 'Paket Catering'],
            ['kode_jenis' => 'NASI_BOX', 'nama_jenis' => 'Paket Nasi Box'],
        ];
        DB::table('jenis_menu')->insert($jenis_menu);

        // 5. Satuan
        $satuan = [
            ['nama_satuan' => 'Kilogram', 'singkatan' => 'kg'],
            ['nama_satuan' => 'Gram', 'singkatan' => 'g'],
            ['nama_satuan' => 'Liter', 'singkatan' => 'l'],
            ['nama_satuan' => 'Mililiter', 'singkatan' => 'ml'],
            ['nama_satuan' => 'Buah', 'singkatan' => 'buah'],
            ['nama_satuan' => 'Sendok Makan', 'singkatan' => 'sdm'],
            ['nama_satuan' => 'Sendok Teh', 'singkatan' => 'sdt'],
            ['nama_satuan' => 'Porsi', 'singkatan' => 'porsi'],
            ['nama_satuan' => 'Ikat', 'singkatan' => 'ikat'],
        ];
        DB::table('satuan')->insert($satuan);

        // 6. Jenis Pesanan
        $jenis_pesanan = [
            ['kode_jenis' => 'DIN', 'nama_jenis' => 'Dine In'],
            ['kode_jenis' => 'CAT', 'nama_jenis' => 'Catering'],
            ['kode_jenis' => 'BOX', 'nama_jenis' => 'Nasi Box'],
        ];
        DB::table('jenis_pesanan')->insert($jenis_pesanan);

        // 7. Status Pesanan
        $status_pesanan = [
            ['kode_status' => 'MENUNGGU', 'nama_status' => 'Menunggu Konfirmasi'],
            ['kode_status' => 'DIKONFIRMASI', 'nama_status' => 'Dikonfirmasi'],
            ['kode_status' => 'DIPROSES', 'nama_status' => 'Sedang Diproses'],
            ['kode_status' => 'SIAP', 'nama_status' => 'Siap Disajikan / Diambil'],
            ['kode_status' => 'SELESAI', 'nama_status' => 'Selesai'],
            ['kode_status' => 'DIBATALKAN', 'nama_status' => 'Dibatalkan'],
        ];
        DB::table('status_pesanan')->insert($status_pesanan);

        // 8. Metode Pembayaran
        $metode_pembayaran = [
            ['kode_metode' => 'TUNAI', 'nama_metode' => 'Tunai'],
            ['kode_metode' => 'QRIS', 'nama_metode' => 'QRIS'],
            ['kode_metode' => 'TRANSFER', 'nama_metode' => 'Transfer Bank'],
            ['kode_metode' => 'KARTU', 'nama_metode' => 'Kartu Debit/Kredit'],
        ];
        DB::table('metode_pembayaran')->insert($metode_pembayaran);

        // 9. Status Pembayaran
        $status_pembayaran = [
            ['kode_status' => 'MENUNGGU', 'nama_status' => 'Menunggu Pembayaran'],
            ['kode_status' => 'SEBAGIAN', 'nama_status' => 'Dibayar Sebagian'],
            ['kode_status' => 'LUNAS', 'nama_status' => 'Lunas'],
            ['kode_status' => 'GAGAL', 'nama_status' => 'Gagal'],
            ['kode_status' => 'DIKEMBALIKAN', 'nama_status' => 'Dikembalikan'],
        ];
        DB::table('status_pembayaran')->insert($status_pembayaran);

        // 10. Jenis Pembayaran
        $jenis_pembayaran = [
            ['kode_jenis' => 'PENUH', 'nama_jenis' => 'Pembayaran Penuh'],
            ['kode_jenis' => 'UANG_MUKA', 'nama_jenis' => 'Uang Muka'],
            ['kode_jenis' => 'PELUNASAN', 'nama_jenis' => 'Pelunasan'],
            ['kode_jenis' => 'PENGEMBALIAN', 'nama_jenis' => 'Pengembalian Dana'],
        ];
        DB::table('jenis_pembayaran')->insert($jenis_pembayaran);

        // 11. Status Tiket Dapur
        $status_dapur = [
            ['kode_status' => 'MENUNGGU', 'nama_status' => 'Menunggu'],
            ['kode_status' => 'DIPROSES', 'nama_status' => 'Sedang Diproses'],
            ['kode_status' => 'SELESAI', 'nama_status' => 'Selesai'],
        ];
        DB::table('status_tiket_dapur')->insert($status_dapur);

        // 12. Jenis Mutasi Stok
        $mutasi_stok = [
            ['kode_jenis' => 'PEMBELIAN', 'nama_jenis' => 'Pembelian', 'arah_stok' => 'MASUK'],
            ['kode_jenis' => 'PENJUALAN', 'nama_jenis' => 'Pemakaian Penjualan', 'arah_stok' => 'KELUAR'],
            ['kode_jenis' => 'PENYESUAIAN_MASUK', 'nama_jenis' => 'Penyesuaian Masuk', 'arah_stok' => 'MASUK'],
            ['kode_jenis' => 'PENYESUAIAN_KELUAR', 'nama_jenis' => 'Penyesuaian Keluar', 'arah_stok' => 'KELUAR'],
            ['kode_jenis' => 'RUSAK', 'nama_jenis' => 'Rusak / Terbuang', 'arah_stok' => 'KELUAR'],
            ['kode_jenis' => 'RETUR_MASUK', 'nama_jenis' => 'Retur Masuk', 'arah_stok' => 'MASUK'],
            ['kode_jenis' => 'RETUR_KELUAR', 'nama_jenis' => 'Retur Keluar', 'arah_stok' => 'KELUAR'],
        ];
        DB::table('jenis_mutasi_stok')->insert($mutasi_stok);

        // 13. Status Pengantaran
        $status_antar = [
            ['kode_status' => 'MENUNGGU', 'nama_status' => 'Menunggu'],
            ['kode_status' => 'DIJALAN', 'nama_status' => 'Sedang Di Jalan'],
            ['kode_status' => 'SELESAI', 'nama_status' => 'Selesai Diantar'],
            ['kode_status' => 'BATAL', 'nama_status' => 'Batal'],
        ];
        DB::table('status_pengantaran')->insert($status_antar);

        // 14. Status Pengadaan
        $status_pengadaan = [
            ['kode_status' => 'MENUNGGU', 'nama_status' => 'Menunggu Persetujuan'],
            ['kode_status' => 'DISETUJUI', 'nama_status' => 'Disetujui'],
            ['kode_status' => 'DITOLAK', 'nama_status' => 'Ditolak'],
            ['kode_status' => 'SELESAI', 'nama_status' => 'Selesai'],
        ];
        DB::table('status_pengadaan')->insert($status_pengadaan);

        // 15. Seeder Normalisasi Minimal (Dummy Data)
        $this->call(NormalisasiMinimalSeeder::class);

        // 16. Paket Nasi Box (opsional, idempoten)
        $this->call(NasiBoxSeeder::class);
    }
}
