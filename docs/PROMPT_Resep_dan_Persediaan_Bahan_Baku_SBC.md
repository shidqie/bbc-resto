# PROMPT IMPLEMENTASI

## Modul Resep & Takaran dan Persediaan Bahan Baku

Saya memiliki aplikasi Laravel yang sudah berjalan untuk Rumah Makan Saung Babakan Cinta. Aplikasi menangani layanan Dine-In, Catering, dan Nasi Box. Perbaiki serta lengkapi aplikasi yang sudah ada agar modul **Resep & Takaran** terintegrasi dengan **Persediaan Bahan Baku**.

Persediaan wajib dipisahkan menjadi dua kelompok operasional:

1. **Bahan Baku Harian** untuk pesanan Dine-In dan Nasi Box.
2. **Bahan Baku Catering** khusus untuk pesanan Catering.

Pengadaan juga wajib dipisahkan menjadi **Pengadaan Harian** dan **Pengadaan Catering**. Pemisahan berlaku pada saldo stok, mutasi, perhitungan kebutuhan, penyesuaian, penerimaan, dan laporan. Master nama bahan baku tetap satu agar tidak terjadi duplikasi data.

Jangan membuat proyek baru. Jangan mengganti teknologi utama dan jangan menghapus fitur yang sudah berjalan. Awali dengan memeriksa struktur proyek, migration, model, relasi, route, controller, service, Blade, komponen UI, JavaScript, middleware, policy, dan test yang sudah tersedia.

Jika skill `ui-design` tersedia, gunakan skill tersebut untuk menjaga kualitas dan konsistensi tampilan.

---

## 1. Tujuan

Bangun hubungan proses berikut:

```text
Data Menu
→ Resep dan Takaran
→ Bahan Baku
→ Tentukan jenis persediaan
   ├── Harian: Dine-In dan Nasi Box
   └── Catering: Catering
→ Pesanan
→ Pemakaian Bahan
→ Pengurangan Stok Sesuai Jenis Persediaan
→ Riwayat Mutasi Stok
→ Peringatan Stok Minimum
→ Pengadaan Harian/Pengadaan Catering
→ Penerimaan Bahan
→ Stok Bertambah pada Jenis Persediaan yang Sesuai
```

Sistem harus memiliki kemampuan:

- Pencatatan data dan jumlah stok bahan baku.
- Pemisahan saldo Bahan Baku Harian dan Bahan Baku Catering.
- Penentuan resep serta takaran bahan untuk setiap menu.
- Pencatatan stok masuk sesuai jenis penerimaan pengadaan.
- Pengurangan stok harian untuk pesanan Dine-In dan Nasi Box.
- Pengurangan stok Catering khusus untuk pesanan Catering.
- Perhitungan kebutuhan Nasi Box dari stok harian.
- Perhitungan kebutuhan Catering dari stok Catering.
- Peringatan ketika stok mencapai batas minimum.
- Pencatatan riwayat stok masuk dan keluar.
- Penyesuaian stok berdasarkan hasil pemeriksaan fisik.
- Rekapitulasi persediaan dan stock opname.
- Pembuatan usulan pengadaan ketika stok tidak mencukupi.
- Pemisahan Pengadaan Harian dan Pengadaan Catering.

---

## 2. Aturan Pengerjaan

1. Audit implementasi yang tersedia sebelum mengubah kode.
2. Pertahankan struktur dan data lama yang masih valid.
3. Buat migration baru untuk perubahan basis data.
4. Jangan mengedit migration lama yang telah digunakan tanpa strategi migrasi data.
5. Gunakan Form Request untuk validasi.
6. Gunakan Policy, Gate, atau middleware untuk hak akses.
7. Letakkan perhitungan resep dan stok dalam service terpusat, bukan diduplikasi di controller.
8. Gunakan database transaction pada proses perubahan stok.
9. Gunakan row locking ketika beberapa transaksi dapat mengubah bahan yang sama.
10. Semua perubahan stok harus mempunyai catatan mutasi dan referensi sumber.
11. Proses pengurangan atau penambahan stok harus idempotent agar tidak terjadi dua kali.
12. Jangan gunakan nilai float untuk jumlah bahan atau harga; gunakan decimal.
13. Jangan menjalankan migration yang menghapus data tanpa menjelaskan dampaknya.
14. Jangan membuat data dummy pada production database.
15. Setelah perubahan, jalankan test, formatter, dan pemeriksaan error.
16. Jangan menggandakan master bahan baku hanya karena persediaannya dipisahkan.
17. Gunakan jenis persediaan pada saldo, mutasi, pengadaan, penerimaan, penyesuaian, dan laporan.
18. Stok harian tidak boleh otomatis mengambil stok Catering, begitu juga sebaliknya.

---

## 3. Menu dan Halaman

Gunakan struktur navigasi berikut:

### Menu & Paket

- Data Menu.
- Kategori Menu.
- Resep & Takaran.
- Paket Catering.
- Paket Nasi Box.

### Persediaan

- Data Bahan Baku.
- Stok Bahan Baku Harian.
- Stok Bahan Baku Catering.
- Ketersediaan Menu.
- Riwayat Stok.
- Penyesuaian Stok.

### Pengadaan

- Semua Pengadaan.
- Pengadaan Harian.
- Pengadaan Catering.
- Buat Pengadaan Harian.
- Buat Pengadaan Catering.
- Penerimaan Bahan Harian.
- Penerimaan Bahan Catering.
- Riwayat Pengadaan.

Produksi tidak menjadi menu utama terpisah. Status produksi berada pada halaman Penjualan dalam bentuk kolom dan tab:

- Menunggu Produksi.
- Sedang Diproduksi.
- Produksi Selesai.

---

## 4. Data Bahan Baku

Halaman Data Bahan Baku harus mendukung tambah, lihat, ubah, dan nonaktifkan data.

Gunakan atribut minimal:

- Kode bahan.
- Nama bahan.
- Kategori bahan.
- Satuan stok dasar.
- Status aktif.
- Pemasok utama jika tersedia.
- Keterangan.

Aturan:

- Kode bahan harus unik.
- Nama bahan tidak boleh duplikat dalam satu satuan dasar.
- Bahan yang sudah digunakan pada resep atau transaksi tidak boleh dihapus permanen.
- Bahan tersebut hanya dapat dinonaktifkan.
- Status aktif berbeda dengan jumlah stok.
- Status aktif menunjukkan bahan masih dipakai oleh sistem.

Tabel minimal:

| Kolom | Keterangan |
|---|---|
| Kode | Kode unik bahan |
| Nama Bahan | Nama bahan baku |
| Kategori | Kelompok bahan |
| Satuan Dasar | Gram, ml, potong, butir, dan lainnya |
| Stok Harian | Saldo untuk Dine-In dan Nasi Box |
| Stok Catering | Saldo khusus Catering |
| Status Data | Aktif atau nonaktif |
| Aksi | Detail, ubah, riwayat, atau nonaktifkan |

---

## 4.1 Pemisahan Jenis Persediaan

Master bahan baku disimpan satu kali. Jangan membuat nama seperti “Beras Harian” dan “Beras Catering” jika sebenarnya merupakan bahan yang sama.

Gunakan struktur terpisah secara logis:

| Data | Ketentuan |
|---|---|
| Master bahan | Satu data bahan baku yang dapat digunakan pada kedua jenis persediaan |
| Stok Harian | Saldo bahan untuk Dine-In dan Nasi Box |
| Stok Catering | Saldo bahan khusus Catering |
| Mutasi | Menyimpan jenis persediaan Harian atau Catering |
| Pengadaan | Menyimpan jenis pengadaan Harian atau Catering |
| Penerimaan | Menambah stok berdasarkan jenis pengadaan asal |
| Penyesuaian | Dilakukan terpisah untuk stok Harian atau Catering |

Struktur basis data yang disarankan:

- Tabel `bahan_baku` untuk master bahan.
- Tabel `stok_bahan` untuk saldo berdasarkan `bahan_baku_id` dan `jenis_persediaan`.
- Nilai `jenis_persediaan`: `harian` atau `catering`.
- Gunakan unique constraint gabungan pada `bahan_baku_id` dan `jenis_persediaan`.
- Tabel `mutasi_stok` wajib menyimpan `jenis_persediaan`.
- Tabel `pengadaan` wajib menyimpan `jenis_pengadaan`.
- Tabel penerimaan dan penyesuaian wajib mengetahui jenis persediaan yang dipengaruhi.

Stok minimum disimpan per jenis persediaan karena kebutuhan Harian dan Catering dapat berbeda. Satu bahan dapat memiliki:

- Batas minimum stok Harian.
- Batas minimum stok Catering.

Aturan sumber stok:

| Jenis Pesanan | Sumber Stok |
|---|---|
| Dine-In | Bahan Baku Harian |
| Nasi Box | Bahan Baku Harian |
| Catering | Bahan Baku Catering |

Jika stok Harian tidak mencukupi, sistem tidak boleh mengambil stok Catering secara otomatis. Perpindahan antarpersediaan hanya boleh dilakukan melalui transaksi pemindahan stok yang mempunyai jumlah, alasan, pengguna, dan dua mutasi berpasangan. Jika fitur pemindahan belum termasuk ruang lingkup, sistem cukup menolak pemakaian lintas jenis.

---

## 5. Satuan dan Konversi

Setiap bahan harus memiliki satu satuan stok dasar. Satuan pembelian dan satuan resep dapat berbeda, tetapi harus mempunyai nilai konversi.

Contoh:

| Bahan | Satuan Pembelian | Konversi | Satuan Dasar |
|---|---:|---:|---:|
| Beras | 1 kg | 1.000 | gram |
| Minyak | 1 liter | 1.000 | ml |
| Ayam | 1 ekor | 4 | potong |

Simpan seluruh saldo dan mutasi menggunakan satuan dasar.

Contoh:

- Penerimaan beras 5 kg disimpan sebagai penambahan 5.000 gram.
- Resep menggunakan beras 200 gram per porsi.
- Penjualan 10 porsi mengurangi stok beras 2.000 gram.

---

## 6. Resep dan Takaran

Satu menu dapat mempunyai banyak bahan baku. Satu bahan dapat digunakan oleh banyak menu.

Setiap detail resep menyimpan:

- Menu.
- Bahan baku.
- Jumlah pemakaian.
- Satuan resep.
- Jumlah setelah dikonversi ke satuan dasar.
- Keterangan opsional.

Aturan validasi:

- Jumlah pemakaian harus lebih dari nol.
- Bahan tidak boleh muncul dua kali dalam resep yang sama.
- Menu yang membutuhkan stok tidak dapat dijual sebelum memiliki resep.
- Bahan nonaktif tidak dapat ditambahkan ke resep baru.
- Perubahan resep tidak boleh mengubah riwayat pemakaian transaksi lama.

Halaman Resep & Takaran harus menyediakan:

- Pencarian menu.
- Filter kategori dan status kelengkapan resep.
- Informasi jumlah bahan dalam resep.
- Status resep lengkap atau belum lengkap.
- Form tambah beberapa bahan secara dinamis.
- Pemilihan bahan dan satuan.
- Perhitungan otomatis ke satuan dasar.
- Ringkasan perkiraan kebutuhan per porsi.

---

## 7. Paket Catering dan Nasi Box

Jangan menyimpan pilihan sebagai satu nama gabungan.

Contoh salah:

```text
Ayam Goreng/Bakar
```

Contoh benar:

- Ayam Goreng.
- Ayam Bakar.

Gunakan struktur:

1. Paket.
2. Kelompok pilihan.
3. Menu pada kelompok pilihan.

Jenis kelompok:

- `pilih_satu`: konsumen memilih satu menu.
- `pilih_beberapa`: konsumen memilih sesuai batas minimum dan maksimum.
- `semua_didapat`: semua menu otomatis menjadi isi paket.

Pilihan konsumen harus disimpan dalam detail pesanan. Perhitungan kebutuhan bahan harus menggunakan resep dari menu yang benar-benar dipilih.

Contoh:

Jika konsumen memesan 100 Nasi Box Paket A dan memilih Nasi Liwet serta Ayam Bakar, hitung kebutuhan dari resep Nasi Liwet dan Ayam Bakar. Jangan menghitung bahan Nasi Putih atau Ayam Goreng.

---

## 8. Pengurangan Stok Berdasarkan Penjualan

Gunakan service terpusat, misalnya:

- `RecipeCalculationService`.
- `InventoryService`.
- `StockMutationService`.
- `MenuAvailabilityService`.

Nama dapat disesuaikan dengan konvensi proyek.

Alur pengurangan stok:

1. Ambil detail pesanan.
2. Tentukan jenis persediaan dari jenis pesanan.
3. Gunakan `harian` untuk Dine-In dan Nasi Box.
4. Gunakan `catering` untuk Catering.
5. Ambil menu dan pilihan yang benar-benar dipesan.
6. Ambil resep aktif yang berlaku pada saat transaksi.
7. Hitung kebutuhan bahan dikalikan jumlah porsi.
8. Konversi ke satuan stok dasar.
9. Kunci baris saldo stok berdasarkan bahan dan jenis persediaan.
10. Periksa ketersediaan pada jenis persediaan yang benar.
11. Kurangi saldo stok.
12. Buat mutasi stok keluar dengan jenis persediaan yang sama.
13. Tandai transaksi telah memproses stok.
14. Commit seluruh proses secara atomic.

Titik pengurangan stok:

- Dine-In: ketika transaksi dinyatakan selesai atau pembayaran berhasil.
- Nasi Box: ketika produksi dimulai atau bahan Harian resmi dikeluarkan untuk produksi.
- Catering: ketika produksi dimulai atau bahan Catering resmi dikeluarkan untuk produksi.

Pilih satu titik untuk setiap jenis layanan dan gunakan secara konsisten. Jangan mengurangi stok pada dua status berbeda.

Simpan penanda seperti:

- `stock_deducted_at`.
- `stock_deduction_reference`.

Jika endpoint dipanggil ulang, halaman dimuat ulang, atau status yang sama dikirim kembali, stok tidak boleh berkurang lagi.

---

## 9. Riwayat dan Kartu Stok

Setiap perubahan stok menghasilkan mutasi dengan data:

- Nomor mutasi.
- Tanggal dan waktu.
- Bahan baku.
- Jenis persediaan: Harian atau Catering.
- Jenis mutasi.
- Jumlah sebelum.
- Jumlah masuk.
- Jumlah keluar.
- Jumlah sesudah.
- Satuan dasar.
- Jenis referensi.
- ID referensi.
- Nomor dokumen.
- Pengguna pelaksana.
- Keterangan.

Jenis mutasi:

- Penerimaan pengadaan.
- Pemakaian penjualan.
- Pemakaian produksi.
- Penyesuaian.
- Bahan terbuang.
- Retur.
- Pembatalan atau mutasi pembalik.

Saldo stok tidak boleh diperbarui langsung tanpa membuat mutasi.

Halaman Riwayat Stok menyediakan:

- Filter tanggal.
- Filter bahan.
- Filter jenis persediaan.
- Filter jenis mutasi.
- Pencarian nomor referensi.
- Saldo sebelum dan sesudah.
- Tautan ke transaksi sumber.
- Ekspor PDF atau Excel jika fungsi ekspor sudah tersedia.

---

## 10. Ketersediaan Menu

Hitung jumlah porsi maksimal berdasarkan bahan dalam resep:

```text
porsi tersedia = nilai terkecil dari
floor(stok setiap bahan ÷ kebutuhan bahan per porsi)
```

Contoh:

- Ayam tersedia 10 potong dan kebutuhan 1 potong per porsi.
- Bumbu tersedia 300 gram dan kebutuhan 20 gram per porsi.
- Menu dapat dibuat maksimal 10 porsi.

Status:

- Tersedia.
- Stok Menipis.
- Stok Tidak Cukup.
- Resep Belum Lengkap.
- Nonaktif.

Status aktif menu berbeda dengan status ketersediaan menu.

- Aktif: menu boleh digunakan secara administratif.
- Tersedia: bahan cukup untuk dijual.

Hitung ketersediaan berdasarkan layanan:

- Menu Dine-In menggunakan saldo Harian.
- Paket Nasi Box menggunakan saldo Harian.
- Paket Catering menggunakan saldo Catering.

Menu yang tersedia untuk Dine-In belum tentu tersedia untuk Catering karena saldo keduanya dipisahkan.

---

## 11. Perhitungan Kebutuhan Nasi Box dan Catering

Gunakan rumus:

```text
kebutuhan bahan = takaran bahan per porsi × jumlah porsi × jumlah menu
```

Gabungkan kebutuhan bahan yang sama dari beberapa menu, tetapi jangan menggabungkan kebutuhan Harian dengan kebutuhan Catering.

### Kebutuhan Nasi Box

- Gunakan jenis persediaan `harian`.
- Bandingkan kebutuhan dengan Stok Bahan Baku Harian.
- Kekurangan bahan menghasilkan usulan Pengadaan Harian.

### Kebutuhan Catering

- Gunakan jenis persediaan `catering`.
- Bandingkan kebutuhan dengan Stok Bahan Baku Catering.
- Kekurangan bahan menghasilkan usulan Pengadaan Catering.

Tampilkan:

| Kolom | Keterangan |
|---|---|
| Bahan | Nama bahan |
| Kebutuhan | Total kebutuhan produksi |
| Jenis Persediaan | Harian atau Catering |
| Stok Tersedia | Saldo stok pada jenis persediaan terkait |
| Sedang Dipesan | Jumlah dalam pengadaan aktif |
| Kekurangan | Jumlah yang masih harus dibeli |
| Satuan | Satuan dasar |
| Status | Cukup atau kurang |

Pesanan yang sudah dikonfirmasi dapat menghasilkan rancangan pengadaan otomatis. Rancangan Nasi Box masuk ke Pengadaan Harian, sedangkan rancangan Catering masuk ke Pengadaan Catering. Rancangan tersebut tidak boleh langsung menambah stok.

---

## 12. Pengadaan dan Penerimaan

Pengadaan dipisahkan menjadi dua alur dan dua tampilan.

### 12.1 Pengadaan Harian

Digunakan untuk:

- Kebutuhan operasional Dine-In.
- Kebutuhan pesanan Nasi Box.
- Stok Harian yang berada di bawah batas minimum.
- Gabungan kebutuhan Harian pada tanggal yang sama.

Pengadaan Harian hanya boleh menambah Stok Bahan Baku Harian.

### 12.2 Pengadaan Catering

Digunakan untuk:

- Kekurangan bahan dari pesanan Catering yang telah dikonfirmasi.
- Stok Catering yang berada di bawah batas minimum.
- Gabungan beberapa pesanan Catering pada periode produksi yang sama.

Pengadaan Catering harus menyimpan referensi pesanan Catering yang menjadi sumber kebutuhan. Pengadaan Catering hanya boleh menambah Stok Bahan Baku Catering.

Pengadaan dapat dibuat dari:

- Kebutuhan harian.
- Stok di bawah batas minimum.
- Kekurangan bahan Nasi Box untuk Pengadaan Harian.
- Kekurangan bahan Catering untuk Pengadaan Catering.
- Gabungan beberapa pesanan pada periode yang sama.

Rumus usulan:

```text
jumlah pengadaan =
kebutuhan produksi
+ stok pengaman
- stok tersedia
- jumlah yang sedang dipesan
```

Status pengadaan:

- Draf.
- Diajukan.
- Disetujui.
- Dipesan.
- Diterima Sebagian.
- Diterima Lengkap.
- Dibatalkan.

Penerimaan harus menyimpan jumlah yang benar-benar diterima. Jumlah diterima dapat berbeda dari jumlah dipesan.

Ketika penerimaan dikonfirmasi:

1. Validasi pengadaan dan detail penerimaan.
2. Ambil jenis pengadaan dari dokumen asal.
3. Konversi jumlah diterima ke satuan dasar.
4. Tambahkan saldo Harian jika berasal dari Pengadaan Harian.
5. Tambahkan saldo Catering jika berasal dari Pengadaan Catering.
6. Buat mutasi stok masuk dengan jenis persediaan yang sesuai.
7. Perbarui jumlah yang telah diterima.
8. Tentukan status diterima sebagian atau lengkap.
9. Tandai penerimaan telah diproses agar tidak dapat menambah stok dua kali.

Pengguna tidak boleh mengubah jenis pengadaan pada saat penerimaan untuk menghindari stok masuk ke kelompok yang salah.

---

## 13. Penyesuaian dan Stock Opname

Form penyesuaian menyimpan:

- Bahan baku.
- Jenis persediaan: Harian atau Catering.
- Stok menurut sistem.
- Stok hasil pemeriksaan fisik.
- Selisih.
- Alasan.
- Tanggal pemeriksaan.
- Pengguna pelaksana.
- Catatan atau bukti.

Setelah dikonfirmasi:

- Jika fisik lebih besar, buat mutasi masuk penyesuaian.
- Jika fisik lebih kecil, buat mutasi keluar penyesuaian.
- Jangan menghapus atau mengubah mutasi lama.
- Simpan hasil pemeriksaan sebagai dokumen stock opname.

---

## 14. Hak Akses

Gunakan hak akses minimal:

| Peran | Akses |
|---|---|
| Tim Dapur | Melihat stok, kebutuhan bahan, dan status produksi |
| Kasir | Melihat ketersediaan menu dan memproses transaksi |
| Pelayan | Melihat ketersediaan menu ketika membuat pesanan |
| Pemilik | Mengelola bahan, resep, penyesuaian, pengadaan, penerimaan, dan laporan |
| Konsumen | Hanya melihat menu yang dapat dipesan |

Jangan hanya menyembunyikan tombol. Batasi juga route dan tindakan pada sisi server.

---

## 15. Ketentuan UI

Gunakan tampilan yang sederhana, profesional, konsisten, dan nyaman untuk operasional restoran.

- Gunakan ukuran teks sedang dan mudah dibaca.
- Gunakan komponen search bar yang sama.
- Gunakan dropdown dan date picker yang konsisten.
- Gunakan badge status dengan warna yang konsisten.
- Gunakan modal konfirmasi untuk tindakan penting.
- Jangan menggunakan `alert()` atau `confirm()` bawaan browser.
- Gunakan toast atau komponen notifikasi aplikasi.
- Tampilkan loading, empty state, validation state, dan error state.
- Tabel harus mendukung pencarian, filter, paginasi, dan tampilan responsif.
- Jangan mengubah identitas visual aplikasi yang sudah konsisten tanpa alasan.
- Jangan memenuhi halaman dengan terlalu banyak tombol aksi.

---

## 16. Kriteria Penerimaan

### Skenario A — Penjualan Menu

Diberikan Ayam Bakar Dine-In memakai 1 potong ayam dan 20 gram bumbu. Ketika 2 porsi selesai diproses, Stok Harian ayam berkurang 2 potong dan bumbu berkurang 40 gram. Stok Catering tidak berubah. Mutasi harus memiliki referensi pesanan dan jenis persediaan Harian.

### Skenario B — Pencegahan Duplikasi

Ketika status transaksi yang telah mengurangi stok dikirim ulang, saldo dan mutasi tidak berubah untuk kedua kalinya.

### Skenario C — Paket Pilihan

Pesanan 100 Nasi Box yang memilih Nasi Liwet dan Ayam Bakar hanya menghitung resep kedua pilihan tersebut dan menggunakan Stok Harian.

### Skenario D — Penerimaan Sebagian

Pengadaan Catering beras 50 kg menerima 30 kg. Stok Catering bertambah 30 kg, Stok Harian tidak berubah, dan pengadaan berstatus diterima sebagian dengan sisa 20 kg.

### Skenario E — Bahan Tidak Cukup

Menu membutuhkan 200 gram beras per porsi, sedangkan stok hanya 100 gram. Sistem menampilkan stok tidak cukup dan mencegah pemesanan sesuai kebijakan yang berlaku.

### Skenario F — Pembatalan

Jika stok belum dikurangi, pembatalan tidak membuat mutasi. Jika stok sudah dikurangi, pembatalan yang disetujui membuat mutasi pembalik dan tidak menghapus riwayat lama.

### Skenario G — Stock Opname

Stok Harian menurut sistem menunjukkan 10 kg, sedangkan fisik 9,5 kg. Sistem membuat mutasi penyesuaian keluar Harian sebesar 0,5 kg dan menyimpan alasannya. Stok Catering tidak berubah.

### Skenario H — Pemisahan Stok

Stok Harian ayam kosong, sedangkan Stok Catering memiliki 100 potong. Pesanan Dine-In tetap dinyatakan tidak tersedia dan sistem tidak mengambil Stok Catering secara otomatis.

### Skenario I — Pemisahan Pengadaan

Usulan kebutuhan dari Nasi Box menghasilkan Pengadaan Harian. Usulan kebutuhan dari pesanan Catering menghasilkan Pengadaan Catering. Penerimaan masing-masing menambah jenis stok yang sesuai.

---

## 17. Pengujian

Buat atau perbarui pengujian:

- Unit test konversi satuan.
- Unit test perhitungan resep.
- Unit test jumlah porsi tersedia.
- Feature test pengurangan stok.
- Feature test pencegahan pengurangan ganda.
- Feature test pilihan menu dalam paket.
- Feature test penerimaan sebagian dan lengkap.
- Feature test penyesuaian stok.
- Feature test hak akses.
- Feature test transaksi bersamaan pada bahan yang sama.
- Feature test pemisahan saldo Harian dan Catering.
- Feature test Dine-In serta Nasi Box hanya mengurangi Stok Harian.
- Feature test Catering hanya mengurangi Stok Catering.
- Feature test penerimaan Harian dan Catering tidak tertukar.
- Feature test usulan Pengadaan Harian dan Pengadaan Catering.

Jalankan pengujian yang relevan dan jangan menyatakan selesai jika test gagal.

---

## 18. Urutan Implementasi

Kerjakan bertahap:

### Tahap 1 — Audit

- Petakan fitur yang sudah tersedia.
- Temukan tabel, relasi, dan logika stok yang bermasalah.
- Tulis daftar gap terhadap prompt ini.

### Tahap 2 — Fondasi Data

- Rapikan bahan, satuan, konversi, resep, paket, dan pilihan.
- Pisahkan saldo stok Harian dan Catering tanpa menggandakan master bahan.
- Tambahkan jenis persediaan pada mutasi, penyesuaian, pengadaan, dan penerimaan.
- Buat migration aman untuk perubahan skema.

### Tahap 3 — Service Stok

- Buat perhitungan resep.
- Buat service mutasi stok.
- Terapkan idempotency dan database transaction.

### Tahap 4 — Integrasi Transaksi

- Hubungkan Dine-In ke Stok Harian.
- Hubungkan Nasi Box ke Stok Harian.
- Hubungkan Catering ke Stok Catering.
- Hubungkan Pengadaan dan Penerimaan Harian.
- Hubungkan Pengadaan dan Penerimaan Catering.
- Hubungkan penyesuaian berdasarkan jenis persediaan.

### Tahap 5 — UI

- Perbaiki halaman Resep & Takaran.
- Pisahkan tampilan Stok Harian dan Stok Catering.
- Pisahkan tampilan Pengadaan Harian dan Pengadaan Catering.
- Perbaiki halaman mutasi dan penyesuaian dengan filter jenis persediaan.
- Terapkan komponen UI yang konsisten.

### Tahap 6 — Verifikasi

- Jalankan test.
- Periksa data lama.
- Uji alur pengguna.
- Periksa risiko pengurangan stok ganda.

---

## 19. Format Laporan Hasil

Setelah pengerjaan, laporkan:

1. Ringkasan masalah yang ditemukan.
2. Fitur yang sudah tersedia sebelum perubahan.
3. Fitur yang ditambahkan atau diperbaiki.
4. Migration dan perubahan basis data.
5. File yang diubah beserta alasan.
6. Alur pengurangan dan penambahan stok.
7. Hasil pengujian.
8. Bagian yang belum selesai.
9. Risiko atau keputusan yang masih membutuhkan persetujuan.

## Gunakan skills web-design-guidelines,ui-design

Jangan hanya menjelaskan rencana. Lakukan implementasi secara bertahap pada proyek yang tersedia, verifikasi hasilnya, dan hentikan pekerjaan jika terdapat keputusan bisnis penting yang belum dapat ditentukan dari kode atau instruksi.
