# STRUKTUR DATABASE SISTEM INFORMASI PENJUALAN DAN PERSEDIAAN BAHAN BAKU

Dokumen ini berisi rancangan basis data yang telah dinormalisasi hingga bentuk ketiga atau **3NF**. Nama tabel, kolom, status, dan relasi menggunakan bahasa Indonesia agar lebih mudah dipahami dan konsisten dengan proyek.

> Catatan: Untuk Laravel, kolom `created_at` dan `updated_at` lebih aman tetap digunakan karena merupakan bawaan framework. Namun, jika seluruh proyek memang memakai bahasa Indonesia, kolom tersebut dapat disesuaikan menjadi `dibuat_pada` dan `diperbarui_pada` dengan pengaturan tambahan pada model.

---

# A. Pengguna dan Hak Akses

## 1. Tabel `peran`

Menyimpan jenis peran pengguna dalam sistem.

| Nama Kolom | Tipe Data | Keterangan |
|---|---|---|
| `id` | BIGINT, PK | ID peran |
| `nama_peran` | VARCHAR(50), UNIQUE | Nama peran |
| `dibuat_pada` | TIMESTAMP | Waktu data dibuat |
| `diperbarui_pada` | TIMESTAMP | Waktu data diperbarui |

Contoh data:

| id | nama_peran |
|---:|---|
| 1 | Pemilik |
| 2 | Manajer |
| 3 | Kasir |
| 4 | Pelayan |
| 5 | Tim Dapur |
| 6 | Konsumen |

---

## 2. Tabel `pengguna`

Menyimpan akun pengguna sistem.

| Nama Kolom | Tipe Data | Keterangan |
|---|---|---|
| `id` | BIGINT, PK | ID pengguna |
| `peran_id` | BIGINT, FK | Relasi ke `peran.id` |
| `nama` | VARCHAR(100) | Nama pengguna |
| `email` | VARCHAR(150), UNIQUE | Alamat email |
| `nomor_telepon` | VARCHAR(20), NULL | Nomor telepon |
| `kata_sandi` | VARCHAR(255) | Kata sandi |
| `status_aktif` | BOOLEAN | Status akun |
| `dibuat_pada` | TIMESTAMP | Waktu dibuat |
| `diperbarui_pada` | TIMESTAMP | Waktu diperbarui |

Semua akun pemilik, manajer, kasir, pelayan, dan tim dapur disimpan di tabel `pengguna`. Perbedaannya ditentukan melalui `peran_id`.

---

# B. Pelanggan dan Meja

## 3. Tabel `pelanggan`

| Nama Kolom | Tipe Data | Keterangan |
|---|---|---|
| `id` | BIGINT, PK | ID pelanggan |
| `nama` | VARCHAR(100) | Nama pelanggan |
| `nomor_telepon` | VARCHAR(20), NULL | Nomor telepon |
| `email` | VARCHAR(150), NULL | Email |
| `alamat` | TEXT, NULL | Alamat pelanggan |
| `dibuat_pada` | TIMESTAMP | Waktu dibuat |
| `diperbarui_pada` | TIMESTAMP | Waktu diperbarui |

Nama dan nomor telepon tidak boleh digabung dalam satu kolom.

Contoh salah:

```text
Rudi – 08123123123
```

Contoh benar:

```text
nama = Rudi
nomor_telepon = 08123123123
```

---

## 4. Tabel `status_meja`

| Nama Kolom | Tipe Data | Keterangan |
|---|---|---|
| `id` | BIGINT, PK | ID status meja |
| `kode_status` | VARCHAR(30), UNIQUE | Kode status |
| `nama_status` | VARCHAR(50), UNIQUE | Nama status |

Contoh data:

| kode_status | nama_status |
|---|---|
| `TERSEDIA` | Tersedia |
| `TERISI` | Terisi |
| `DIPESAN` | Dipesan |
| `TIDAK_AKTIF` | Tidak Aktif |

---

## 5. Tabel `meja`

| Nama Kolom | Tipe Data | Keterangan |
|---|---|---|
| `id` | BIGINT, PK | ID meja |
| `nomor_meja` | VARCHAR(20), UNIQUE | Nomor meja |
| `kapasitas` | INT | Kapasitas meja |
| `status_meja_id` | BIGINT, FK | Relasi ke `status_meja.id` |
| `dibuat_pada` | TIMESTAMP | Waktu dibuat |
| `diperbarui_pada` | TIMESTAMP | Waktu diperbarui |

Tabel pesanan hanya menyimpan `meja_id`, bukan teks seperti `Meja 4`.

---

# C. Menu dan Paket

## 6. Tabel `jenis_produk`

| Nama Kolom | Tipe Data | Keterangan |
|---|---|---|
| `id` | BIGINT, PK | ID jenis produk |
| `kode_jenis` | VARCHAR(30), UNIQUE | Kode jenis |
| `nama_jenis` | VARCHAR(50), UNIQUE | Nama jenis produk |

Contoh data:

| kode_jenis | nama_jenis |
|---|---|
| `REGULER` | Menu Reguler |
| `CATERING` | Paket Catering |
| `NASI_BOX` | Paket Nasi Box |

---

## 7. Tabel `kategori_menu`

| Nama Kolom | Tipe Data | Keterangan |
|---|---|---|
| `id` | BIGINT, PK | ID kategori |
| `nama_kategori` | VARCHAR(100), UNIQUE | Nama kategori |
| `deskripsi` | TEXT, NULL | Deskripsi kategori |
| `status_aktif` | BOOLEAN | Status kategori |
| `dibuat_pada` | TIMESTAMP | Waktu dibuat |
| `diperbarui_pada` | TIMESTAMP | Waktu diperbarui |

Contoh kategori:

- Nasi
- Seafood
- Steak dan Iga
- Mie dan Bakso
- Minuman
- Paket Catering
- Paket Nasi Box

---

## 8. Tabel `produk`

Menyimpan seluruh menu reguler, paket catering, dan nasi box.

| Nama Kolom | Tipe Data | Keterangan |
|---|---|---|
| `id` | BIGINT, PK | ID produk |
| `jenis_produk_id` | BIGINT, FK | Relasi ke `jenis_produk.id` |
| `kategori_menu_id` | BIGINT, FK, NULL | Relasi ke `kategori_menu.id` |
| `kode_produk` | VARCHAR(30), UNIQUE | Kode produk |
| `nama_produk` | VARCHAR(150) | Nama produk |
| `deskripsi` | TEXT, NULL | Deskripsi produk |
| `harga_jual` | DECIMAL(15,2) | Harga jual |
| `foto` | VARCHAR(255), NULL | Lokasi foto |
| `status_aktif` | BOOLEAN | Status produk |
| `dibuat_pada` | TIMESTAMP | Waktu dibuat |
| `diperbarui_pada` | TIMESTAMP | Waktu diperbarui |

Contoh data:

| kode_produk | nama_produk | jenis | harga_jual |
|---|---|---|---:|
| MNU001 | Nasi Liwet | Menu Reguler | Rp25.000 |
| CAT001 | Paket Catering A | Paket Catering | Rp50.000 |
| BOX001 | Nasi Box Hemat | Paket Nasi Box | Rp17.000 |

---

## 9. Tabel `ketentuan_paket`

Digunakan khusus untuk catering dan nasi box.

| Nama Kolom | Tipe Data | Keterangan |
|---|---|---|
| `produk_id` | BIGINT, PK, FK | Relasi ke `produk.id` |
| `minimal_pemesanan` | INT | Minimal jumlah pesanan |
| `minimal_hari_pemesanan` | INT | Minimal waktu pemesanan |
| `persentase_uang_muka` | DECIMAL(5,2) | Persentase uang muka |
| `batas_konfirmasi_hari` | INT | Batas konfirmasi |
| `keterangan` | TEXT, NULL | Ketentuan tambahan |

---

# D. Bahan Baku dan Resep

## 10. Tabel `satuan`

| Nama Kolom | Tipe Data | Keterangan |
|---|---|---|
| `id` | BIGINT, PK | ID satuan |
| `nama_satuan` | VARCHAR(50) | Nama satuan |
| `singkatan` | VARCHAR(20) | Singkatan satuan |

Contoh data:

| nama_satuan | singkatan |
|---|---|
| Kilogram | kg |
| Gram | g |
| Liter | l |
| Mililiter | ml |
| Buah | buah |
| Sendok Makan | sdm |
| Sendok Teh | sdt |

---

## 11. Tabel `kategori_bahan_baku`

| Nama Kolom | Tipe Data | Keterangan |
|---|---|---|
| `id` | BIGINT, PK | ID kategori bahan |
| `nama_kategori` | VARCHAR(100), UNIQUE | Nama kategori |

---

## 12. Tabel `bahan_baku`

| Nama Kolom | Tipe Data | Keterangan |
|---|---|---|
| `id` | BIGINT, PK | ID bahan baku |
| `kategori_bahan_baku_id` | BIGINT, FK, NULL | Kategori bahan |
| `satuan_id` | BIGINT, FK | Satuan utama |
| `kode_bahan` | VARCHAR(30), UNIQUE | Kode bahan |
| `nama_bahan` | VARCHAR(150) | Nama bahan |
| `stok_minimal` | DECIMAL(15,3) | Batas stok minimum |
| `status_aktif` | BOOLEAN | Status bahan |
| `dibuat_pada` | TIMESTAMP | Waktu dibuat |
| `diperbarui_pada` | TIMESTAMP | Waktu diperbarui |

---

## 13. Tabel `resep_produk`

Menyimpan komposisi bahan baku setiap menu.

| Nama Kolom | Tipe Data | Keterangan |
|---|---|---|
| `id` | BIGINT, PK | ID resep |
| `produk_id` | BIGINT, FK | Relasi ke `produk.id` |
| `bahan_baku_id` | BIGINT, FK | Relasi ke `bahan_baku.id` |
| `jumlah` | DECIMAL(15,3) | Jumlah bahan |
| `satuan_id` | BIGINT, FK | Satuan resep |
| `dibuat_pada` | TIMESTAMP | Waktu dibuat |
| `diperbarui_pada` | TIMESTAMP | Waktu diperbarui |

Tambahkan batas unik:

```sql
UNIQUE(produk_id, bahan_baku_id)
```

---

# E. Pesanan

## 14. Tabel `jenis_pesanan`

| Nama Kolom | Tipe Data | Keterangan |
|---|---|---|
| `id` | BIGINT, PK | ID jenis pesanan |
| `kode_jenis` | VARCHAR(20), UNIQUE | Kode jenis |
| `nama_jenis` | VARCHAR(50), UNIQUE | Nama jenis pesanan |

Contoh:

| kode_jenis | nama_jenis |
|---|---|
| `DIN` | Dine In |
| `CAT` | Catering |
| `BOX` | Nasi Box |

---

## 15. Tabel `status_pesanan`

| Nama Kolom | Tipe Data | Keterangan |
|---|---|---|
| `id` | BIGINT, PK | ID status pesanan |
| `kode_status` | VARCHAR(30), UNIQUE | Kode status |
| `nama_status` | VARCHAR(50), UNIQUE | Nama status |

Contoh data:

| kode_status | nama_status |
|---|---|
| `MENUNGGU` | Menunggu Konfirmasi |
| `DIKONFIRMASI` | Dikonfirmasi |
| `DIPROSES` | Sedang Diproses |
| `SIAP` | Siap Disajikan |
| `SELESAI` | Selesai |
| `DIBATALKAN` | Dibatalkan |

---

## 16. Tabel `pesanan`

| Nama Kolom | Tipe Data | Keterangan |
|---|---|---|
| `id` | BIGINT, PK | ID pesanan |
| `nomor_pesanan` | VARCHAR(50), UNIQUE | Nomor pesanan |
| `jenis_pesanan_id` | BIGINT, FK | Jenis pesanan |
| `pelanggan_id` | BIGINT, FK, NULL | Pelanggan |
| `meja_id` | BIGINT, FK, NULL | Meja dine-in |
| `pelayan_id` | BIGINT, FK, NULL | Pengguna sebagai pelayan |
| `kasir_id` | BIGINT, FK, NULL | Pengguna sebagai kasir |
| `status_pesanan_id` | BIGINT, FK | Status pesanan |
| `tanggal_pesanan` | DATETIME | Waktu pesanan |
| `jumlah_sebelum_potongan` | DECIMAL(15,2) | Jumlah sebelum diskon |
| `jumlah_diskon` | DECIMAL(15,2) | Jumlah diskon |
| `jumlah_pajak` | DECIMAL(15,2) | Jumlah pajak |
| `biaya_pelayanan` | DECIMAL(15,2) | Biaya pelayanan |
| `total_tagihan` | DECIMAL(15,2) | Total akhir |
| `catatan` | TEXT, NULL | Catatan pesanan |
| `dibuat_pada` | TIMESTAMP | Waktu dibuat |
| `diperbarui_pada` | TIMESTAMP | Waktu diperbarui |

---

## 17. Tabel `detail_pesanan`

| Nama Kolom | Tipe Data | Keterangan |
|---|---|---|
| `id` | BIGINT, PK | ID detail pesanan |
| `pesanan_id` | BIGINT, FK | Relasi ke `pesanan.id` |
| `produk_id` | BIGINT, FK | Produk yang dipesan |
| `jumlah` | INT | Jumlah produk |
| `harga_satuan` | DECIMAL(15,2) | Harga saat transaksi |
| `jumlah_diskon` | DECIMAL(15,2) | Diskon item |
| `subtotal` | DECIMAL(15,2) | Total item |
| `catatan` | TEXT, NULL | Catatan pelanggan |
| `status_item` | VARCHAR(30) | Status item |
| `dibuat_pada` | TIMESTAMP | Waktu dibuat |
| `diperbarui_pada` | TIMESTAMP | Waktu diperbarui |

Rumus:

```text
subtotal = jumlah × harga_satuan - jumlah_diskon
```

---

## 18. Tabel `jadwal_pesanan`

Digunakan untuk catering dan nasi box.

| Nama Kolom | Tipe Data | Keterangan |
|---|---|---|
| `pesanan_id` | BIGINT, PK, FK | Relasi ke `pesanan.id` |
| `tanggal_acara` | DATE | Tanggal acara |
| `waktu_pengantaran` | TIME, NULL | Waktu pengantaran |
| `alamat_pengantaran` | TEXT | Alamat pengantaran |
| `nama_penerima` | VARCHAR(100) | Nama penerima |
| `nomor_telepon_penerima` | VARCHAR(20) | Nomor telepon |
| `jumlah_tamu` | INT, NULL | Jumlah tamu |
| `nama_acara` | VARCHAR(100), NULL | Nama acara |
| `catatan` | TEXT, NULL | Catatan tambahan |

---

# F. Pembayaran

## 19. Tabel `metode_pembayaran`

| Nama Kolom | Tipe Data | Keterangan |
|---|---|---|
| `id` | BIGINT, PK | ID metode |
| `kode_metode` | VARCHAR(30), UNIQUE | Kode metode |
| `nama_metode` | VARCHAR(50), UNIQUE | Nama metode |
| `status_aktif` | BOOLEAN | Status metode |

Contoh:

| kode_metode | nama_metode |
|---|---|
| `TUNAI` | Tunai |
| `QRIS` | QRIS |
| `TRANSFER` | Transfer Bank |
| `KARTU` | Kartu |

---

## 20. Tabel `status_pembayaran`

| Nama Kolom | Tipe Data | Keterangan |
|---|---|---|
| `id` | BIGINT, PK | ID status pembayaran |
| `kode_status` | VARCHAR(30), UNIQUE | Kode status |
| `nama_status` | VARCHAR(50), UNIQUE | Nama status |

Contoh:

| kode_status | nama_status |
|---|---|
| `MENUNGGU` | Menunggu Pembayaran |
| `SEBAGIAN` | Dibayar Sebagian |
| `LUNAS` | Lunas |
| `GAGAL` | Gagal |
| `DIKEMBALIKAN` | Dikembalikan |

---

## 21. Tabel `jenis_pembayaran`

| Nama Kolom | Tipe Data | Keterangan |
|---|---|---|
| `id` | BIGINT, PK | ID jenis pembayaran |
| `kode_jenis` | VARCHAR(30), UNIQUE | Kode jenis |
| `nama_jenis` | VARCHAR(50), UNIQUE | Nama jenis pembayaran |

Contoh:

| kode_jenis | nama_jenis |
|---|---|
| `PENUH` | Pembayaran Penuh |
| `UANG_MUKA` | Uang Muka |
| `PELUNASAN` | Pelunasan |
| `PENGEMBALIAN` | Pengembalian Dana |

---

## 22. Tabel `pembayaran`

| Nama Kolom | Tipe Data | Keterangan |
|---|---|---|
| `id` | BIGINT, PK | ID pembayaran |
| `nomor_pembayaran` | VARCHAR(50), UNIQUE | Nomor pembayaran |
| `pesanan_id` | BIGINT, FK | Relasi ke `pesanan.id` |
| `metode_pembayaran_id` | BIGINT, FK | Metode pembayaran |
| `status_pembayaran_id` | BIGINT, FK | Status pembayaran |
| `jenis_pembayaran_id` | BIGINT, FK | Jenis pembayaran |
| `diproses_oleh` | BIGINT, FK, NULL | Pengguna yang memproses |
| `jumlah_bayar` | DECIMAL(15,2) | Nominal pembayaran |
| `dibayar_pada` | DATETIME, NULL | Waktu pembayaran |
| `bukti_pembayaran` | VARCHAR(255), NULL | Berkas bukti |
| `nomor_referensi` | VARCHAR(100), NULL | Nomor referensi |
| `catatan` | TEXT, NULL | Catatan |
| `dibuat_pada` | TIMESTAMP | Waktu dibuat |
| `diperbarui_pada` | TIMESTAMP | Waktu diperbarui |

---

# G. Pesanan Dapur dan KOT

## 23. Tabel `status_tiket_dapur`

| Nama Kolom | Tipe Data | Keterangan |
|---|---|---|
| `id` | BIGINT, PK | ID status |
| `kode_status` | VARCHAR(30), UNIQUE | Kode status |
| `nama_status` | VARCHAR(50), UNIQUE | Nama status |

---

## 24. Tabel `tiket_dapur`

| Nama Kolom | Tipe Data | Keterangan |
|---|---|---|
| `id` | BIGINT, PK | ID tiket dapur |
| `nomor_tiket` | VARCHAR(50), UNIQUE | Nomor KOT |
| `pesanan_id` | BIGINT, FK | Relasi ke pesanan |
| `status_tiket_dapur_id` | BIGINT, FK | Status tiket |
| `dicetak_pada` | DATETIME, NULL | Waktu dicetak |
| `diproses_pada` | DATETIME, NULL | Waktu mulai diproses |
| `diselesaikan_pada` | DATETIME, NULL | Waktu selesai |
| `dibuat_pada` | TIMESTAMP | Waktu dibuat |
| `diperbarui_pada` | TIMESTAMP | Waktu diperbarui |

---

## 25. Tabel `detail_tiket_dapur`

| Nama Kolom | Tipe Data | Keterangan |
|---|---|---|
| `id` | BIGINT, PK | ID detail |
| `tiket_dapur_id` | BIGINT, FK | Relasi tiket dapur |
| `detail_pesanan_id` | BIGINT, FK | Relasi detail pesanan |
| `jumlah` | INT | Jumlah item |
| `catatan` | TEXT, NULL | Catatan dapur |

---

# H. Pemasok dan Pengadaan Bahan

## 26. Tabel `pemasok`

| Nama Kolom | Tipe Data | Keterangan |
|---|---|---|
| `id` | BIGINT, PK | ID pemasok |
| `kode_pemasok` | VARCHAR(30), UNIQUE | Kode pemasok |
| `nama_pemasok` | VARCHAR(150) | Nama pemasok |
| `nomor_telepon` | VARCHAR(20), NULL | Nomor telepon |
| `email` | VARCHAR(150), NULL | Email |
| `alamat` | TEXT, NULL | Alamat |
| `nama_kontak` | VARCHAR(100), NULL | Nama kontak |
| `status_aktif` | BOOLEAN | Status pemasok |
| `dibuat_pada` | TIMESTAMP | Waktu dibuat |
| `diperbarui_pada` | TIMESTAMP | Waktu diperbarui |

---

## 27. Tabel `status_pengadaan`

| Nama Kolom | Tipe Data | Keterangan |
|---|---|---|
| `id` | BIGINT, PK | ID status |
| `kode_status` | VARCHAR(30), UNIQUE | Kode status |
| `nama_status` | VARCHAR(50), UNIQUE | Nama status |

---

## 28. Tabel `pengadaan_bahan`

| Nama Kolom | Tipe Data | Keterangan |
|---|---|---|
| `id` | BIGINT, PK | ID pengadaan |
| `nomor_pengadaan` | VARCHAR(50), UNIQUE | Nomor pengadaan |
| `pemasok_id` | BIGINT, FK, NULL | Pemasok |
| `pesanan_id` | BIGINT, FK, NULL | Pesanan catering terkait |
| `diajukan_oleh` | BIGINT, FK | Pengguna pengaju |
| `disetujui_oleh` | BIGINT, FK, NULL | Pengguna penyetuju |
| `status_pengadaan_id` | BIGINT, FK | Status pengadaan |
| `jenis_pengadaan` | VARCHAR(30) | Harian atau catering |
| `tanggal_pengadaan` | DATE | Tanggal pengadaan |
| `perkiraan_tanggal_datang` | DATE, NULL | Perkiraan barang datang |
| `total_pengadaan` | DECIMAL(15,2) | Total biaya |
| `catatan` | TEXT, NULL | Catatan |
| `dibuat_pada` | TIMESTAMP | Waktu dibuat |
| `diperbarui_pada` | TIMESTAMP | Waktu diperbarui |

---

## 29. Tabel `detail_pengadaan_bahan`

| Nama Kolom | Tipe Data | Keterangan |
|---|---|---|
| `id` | BIGINT, PK | ID detail |
| `pengadaan_bahan_id` | BIGINT, FK | Relasi pengadaan |
| `bahan_baku_id` | BIGINT, FK | Bahan baku |
| `jumlah_dipesan` | DECIMAL(15,3) | Jumlah dipesan |
| `jumlah_diterima` | DECIMAL(15,3) | Jumlah diterima |
| `satuan_id` | BIGINT, FK | Satuan |
| `harga_satuan` | DECIMAL(15,2) | Harga satuan |
| `subtotal` | DECIMAL(15,2) | Total item |
| `catatan` | TEXT, NULL | Catatan |

Tambahkan batas unik:

```sql
UNIQUE(pengadaan_bahan_id, bahan_baku_id)
```

---

## 30. Tabel `penerimaan_bahan`

| Nama Kolom | Tipe Data | Keterangan |
|---|---|---|
| `id` | BIGINT, PK | ID penerimaan |
| `nomor_penerimaan` | VARCHAR(50), UNIQUE | Nomor penerimaan |
| `pengadaan_bahan_id` | BIGINT, FK | Relasi pengadaan |
| `diterima_oleh` | BIGINT, FK | Pengguna penerima |
| `diterima_pada` | DATETIME | Waktu penerimaan |
| `nomor_nota` | VARCHAR(100), NULL | Nomor nota |
| `berkas_nota` | VARCHAR(255), NULL | Berkas nota |
| `catatan` | TEXT, NULL | Catatan |

---

## 31. Tabel `detail_penerimaan_bahan`

| Nama Kolom | Tipe Data | Keterangan |
|---|---|---|
| `id` | BIGINT, PK | ID detail |
| `penerimaan_bahan_id` | BIGINT, FK | Relasi penerimaan |
| `detail_pengadaan_bahan_id` | BIGINT, FK | Relasi detail pengadaan |
| `jumlah_diterima` | DECIMAL(15,3) | Jumlah diterima |
| `harga_satuan` | DECIMAL(15,2) | Harga aktual |
| `tanggal_kedaluwarsa` | DATE, NULL | Tanggal kedaluwarsa |
| `catatan` | TEXT, NULL | Catatan |

---

# I. Persediaan Bahan Baku

## 32. Tabel `jenis_mutasi_stok`

| Nama Kolom | Tipe Data | Keterangan |
|---|---|---|
| `id` | BIGINT, PK | ID jenis mutasi |
| `kode_jenis` | VARCHAR(30), UNIQUE | Kode mutasi |
| `nama_jenis` | VARCHAR(50), UNIQUE | Nama mutasi |
| `arah_stok` | ENUM(`MASUK`,`KELUAR`) | Arah perubahan stok |

Contoh:

| kode_jenis | nama_jenis | arah_stok |
|---|---|---|
| `PEMBELIAN` | Pembelian | MASUK |
| `PENJUALAN` | Pemakaian Penjualan | KELUAR |
| `PENYESUAIAN_MASUK` | Penyesuaian Masuk | MASUK |
| `PENYESUAIAN_KELUAR` | Penyesuaian Keluar | KELUAR |
| `RUSAK` | Rusak atau Terbuang | KELUAR |
| `RETUR_MASUK` | Retur Masuk | MASUK |
| `RETUR_KELUAR` | Retur Keluar | KELUAR |

---

## 33. Tabel `mutasi_stok`

| Nama Kolom | Tipe Data | Keterangan |
|---|---|---|
| `id` | BIGINT, PK | ID mutasi stok |
| `bahan_baku_id` | BIGINT, FK | Bahan baku |
| `jenis_mutasi_stok_id` | BIGINT, FK | Jenis mutasi |
| `jumlah` | DECIMAL(15,3) | Jumlah mutasi |
| `satuan_id` | BIGINT, FK | Satuan |
| `tanggal_mutasi` | DATETIME | Waktu mutasi |
| `detail_pesanan_id` | BIGINT, FK, NULL | Sumber penjualan |
| `detail_penerimaan_bahan_id` | BIGINT, FK, NULL | Sumber pembelian |
| `detail_penyesuaian_stok_id` | BIGINT, FK, NULL | Sumber penyesuaian |
| `dibuat_oleh` | BIGINT, FK | Pengguna pembuat |
| `catatan` | TEXT, NULL | Catatan |
| `dibuat_pada` | TIMESTAMP | Waktu dibuat |

---

## 34. Tabel `stok_bahan_baku`

| Nama Kolom | Tipe Data | Keterangan |
|---|---|---|
| `bahan_baku_id` | BIGINT, PK, FK | Bahan baku |
| `jumlah_stok` | DECIMAL(15,3) | Saldo stok |
| `terakhir_diperbarui` | DATETIME | Waktu pembaruan |

---

## 35. Tabel `penyesuaian_stok`

| Nama Kolom | Tipe Data | Keterangan |
|---|---|---|
| `id` | BIGINT, PK | ID penyesuaian |
| `nomor_penyesuaian` | VARCHAR(50), UNIQUE | Nomor penyesuaian |
| `tanggal_penyesuaian` | DATETIME | Waktu penyesuaian |
| `dibuat_oleh` | BIGINT, FK | Pengguna pembuat |
| `disetujui_oleh` | BIGINT, FK, NULL | Pengguna penyetuju |
| `alasan` | TEXT | Alasan penyesuaian |
| `status_penyesuaian` | VARCHAR(30) | Status |
| `dibuat_pada` | TIMESTAMP | Waktu dibuat |
| `diperbarui_pada` | TIMESTAMP | Waktu diperbarui |

---

## 36. Tabel `detail_penyesuaian_stok`

| Nama Kolom | Tipe Data | Keterangan |
|---|---|---|
| `id` | BIGINT, PK | ID detail |
| `penyesuaian_stok_id` | BIGINT, FK | Relasi penyesuaian |
| `bahan_baku_id` | BIGINT, FK | Bahan baku |
| `jumlah_sistem` | DECIMAL(15,3) | Jumlah menurut sistem |
| `jumlah_fisik` | DECIMAL(15,3) | Jumlah hasil pemeriksaan |
| `jumlah_selisih` | DECIMAL(15,3) | Selisih |
| `satuan_id` | BIGINT, FK | Satuan |
| `catatan` | TEXT, NULL | Catatan |

---

# J. Pengantaran

## 37. Tabel `status_pengantaran`

| Nama Kolom | Tipe Data | Keterangan |
|---|---|---|
| `id` | BIGINT, PK | ID status |
| `kode_status` | VARCHAR(30), UNIQUE | Kode status |
| `nama_status` | VARCHAR(50), UNIQUE | Nama status |

---

## 38. Tabel `pengantaran`

| Nama Kolom | Tipe Data | Keterangan |
|---|---|---|
| `id` | BIGINT, PK | ID pengantaran |
| `nomor_pengantaran` | VARCHAR(50), UNIQUE | Nomor pengantaran |
| `pesanan_id` | BIGINT, FK, UNIQUE | Pesanan |
| `status_pengantaran_id` | BIGINT, FK | Status pengantaran |
| `ditugaskan_kepada` | BIGINT, FK, NULL | Petugas pengantaran |
| `jadwal_pengantaran` | DATETIME | Jadwal |
| `berangkat_pada` | DATETIME, NULL | Waktu berangkat |
| `diterima_pada` | DATETIME, NULL | Waktu diterima |
| `nama_penerima` | VARCHAR(100) | Nama penerima |
| `nomor_telepon_penerima` | VARCHAR(20) | Nomor telepon |
| `alamat_pengantaran` | TEXT | Alamat |
| `catatan` | TEXT, NULL | Catatan |
| `dibuat_pada` | TIMESTAMP | Waktu dibuat |
| `diperbarui_pada` | TIMESTAMP | Waktu diperbarui |

---

# RELASI UTAMA ANTARTABEL

```text
peran
└── pengguna

pelanggan
└── pesanan

status_meja
└── meja
    └── pesanan

jenis_pesanan
└── pesanan

status_pesanan
└── pesanan

pesanan
├── detail_pesanan
├── pembayaran
├── jadwal_pesanan
├── tiket_dapur
├── pengantaran
└── pengadaan_bahan

produk
├── detail_pesanan
├── resep_produk
└── ketentuan_paket

bahan_baku
├── resep_produk
├── detail_pengadaan_bahan
├── mutasi_stok
└── stok_bahan_baku

pengadaan_bahan
├── detail_pengadaan_bahan
└── penerimaan_bahan

penerimaan_bahan
└── detail_penerimaan_bahan
```

---

# KUERI UNTUK MENAMPILKAN DAFTAR PESANAN

```sql
SELECT
    p.id,
    p.nomor_pesanan,
    p.tanggal_pesanan,
    kasir.nama AS nama_kasir,
    COALESCE(pl.nama, 'Pelanggan Umum') AS nama_pelanggan,
    pl.nomor_telepon,
    m.nomor_meja,
    COALESCE(SUM(dp.jumlah), 0) AS jumlah_item,
    p.total_tagihan,
    mp.nama_metode,
    spb.nama_status AS status_pembayaran,
    sp.nama_status AS status_pesanan
FROM pesanan p
LEFT JOIN pengguna kasir
    ON kasir.id = p.kasir_id
LEFT JOIN pelanggan pl
    ON pl.id = p.pelanggan_id
LEFT JOIN meja m
    ON m.id = p.meja_id
LEFT JOIN detail_pesanan dp
    ON dp.pesanan_id = p.id
LEFT JOIN pembayaran pb
    ON pb.id = (
        SELECT pb2.id
        FROM pembayaran pb2
        WHERE pb2.pesanan_id = p.id
        ORDER BY pb2.dibuat_pada DESC
        LIMIT 1
    )
LEFT JOIN metode_pembayaran mp
    ON mp.id = pb.metode_pembayaran_id
LEFT JOIN status_pembayaran spb
    ON spb.id = pb.status_pembayaran_id
LEFT JOIN status_pesanan sp
    ON sp.id = p.status_pesanan_id
GROUP BY
    p.id,
    p.nomor_pesanan,
    p.tanggal_pesanan,
    kasir.nama,
    pl.nama,
    pl.nomor_telepon,
    m.nomor_meja,
    p.total_tagihan,
    mp.nama_metode,
    spb.nama_status,
    sp.nama_status
ORDER BY p.tanggal_pesanan DESC;
```

---

# HASIL TAMPILAN YANG BENAR

| Nomor Pesanan | Kasir | Pelanggan | Meja | Jumlah Item | Total Tagihan | Metode Pembayaran | Status Pembayaran | Status Pesanan |
|---|---|---|---|---:|---:|---|---|---|
| DIN-20260729-0014 | Kasir BBC | Rudi | Meja 4 | 3 | Rp131.000 | Tunai | Lunas | Selesai |
| DIN-20260729-0013 | Kasir BBC | aksdjakj | Meja 3 | 2 | Rp55.000 | Tunai | Belum Dibayar | Menunggu |
| DIN-20260729-0012 | Kasir BBC | Rudi | Meja 2 | 6 | Rp162.000 | Tunai | Belum Dibayar | Menunggu |
| DIN-20260729-0002 | Kasir BBC | Ardi | Meja 1 | 2 | Rp53.000 | Tunai | Belum Dibayar | Menunggu |
| POS202607290001 | Admin Sistem | Lidya Usada | Meja 9 | 4 | Rp62.000 | Tunai | Lunas | Selesai |
| POS202607290002 | Admin Sistem | Heryanto | Meja 23 | 2 | Rp40.000 | QRIS | Lunas | Selesai |
| POS202607290003 | Admin Sistem | Almira Usamah | Meja 12 | 3 | Rp47.000 | Kartu | Lunas | Selesai |
| POS202607290004 | Admin Sistem | Gandewa Ramadan | Meja 10 | 1 | Rp5.000 | QRIS | Lunas | Selesai |

---

# URUTAN PEMBUATAN MIGRATION

```text
1. peran
2. pengguna
3. pelanggan
4. status_meja
5. meja
6. jenis_produk
7. kategori_menu
8. produk
9. ketentuan_paket
10. satuan
11. kategori_bahan_baku
12. bahan_baku
13. resep_produk
14. jenis_pesanan
15. status_pesanan
16. pesanan
17. detail_pesanan
18. jadwal_pesanan
19. metode_pembayaran
20. status_pembayaran
21. jenis_pembayaran
22. pembayaran
23. status_tiket_dapur
24. tiket_dapur
25. detail_tiket_dapur
26. pemasok
27. status_pengadaan
28. pengadaan_bahan
29. detail_pengadaan_bahan
30. penerimaan_bahan
31. detail_penerimaan_bahan
32. jenis_mutasi_stok
33. penyesuaian_stok
34. detail_penyesuaian_stok
35. mutasi_stok
36. stok_bahan_baku
37. status_pengantaran
38. pengantaran
```

Struktur ini telah memisahkan data master, transaksi, pembayaran, pengadaan, resep, persediaan, dan pengantaran. Dengan demikian, data tidak lagi tercampur dan proses menampilkan data dapat dilakukan melalui relasi yang jelas.
