# Rancangan Data Menu Catering

## 1. Tujuan

Dokumen ini menjelaskan rancangan input dan pengelolaan **menu catering** pada Sistem Informasi Penjualan dan Persediaan Bahan Baku Rumah Makan Saung Babakan Cinta.

Istilah yang digunakan:

- **Paket Catering**
- **Kelompok Pilihan**
- **Pilihan Menu**

Istilah **variant** tidak digunakan karena Sup Kimlo, Sup Bakso, dan Sup Ayam Sosis bukan varian produk, melainkan pilihan menu dalam satu kelompok pilihan.

---

## 2. Struktur Menu pada Sidebar

```text
Menu
├── Data Menu
├── Kategori Menu
├── Resep Menu
└── Paket Catering
```

Keterangan:

- **Data Menu** digunakan untuk mengelola seluruh produk yang dijual.
- **Kategori Menu** digunakan untuk kategori menu reguler, seperti makanan utama, minuman, seafood, dan cemilan.
- **Resep Menu** digunakan untuk mengatur komposisi bahan baku setiap menu.
- **Paket Catering** digunakan untuk mengatur paket, kelompok pilihan, dan pilihan menu catering.

---

## 3. Data Menu

Pada halaman **Data Menu**, Paket Catering A dan Paket Catering B disimpan sebagai produk.

| Kode | Nama Produk | Jenis Produk | Kategori | Harga | Satuan Harga |
|---|---|---|---|---:|---|
| CAT001 | Paket Catering A | Catering | - | Rp47.500 | Per porsi |
| CAT002 | Paket Catering B | Catering | - | Rp42.500 | Per porsi |

Catatan:

- Paket Catering A dan Paket Catering B tidak perlu dimasukkan ke kategori menu reguler.
- Jenis produk diisi **Catering**.
- `kategori_menu_id` dapat bernilai `NULL`.
- Harga disimpan per porsi.

---

## 4. Konsep Isi Paket

Setiap paket catering terdiri atas dua jenis komponen.

### 4.1 Menu Tetap

Menu tetap langsung termasuk dalam paket dan tidak dipilih pelanggan.

Contoh:

- Nasi putih
- Kerupuk udang
- Air mineral

### 4.2 Kelompok Pilihan

Menu pilihan dikelompokkan berdasarkan jenisnya dan pelanggan memilih sesuai aturan.

Contoh kelompok:

- Sup
- Olahan daging
- Olahan tambahan
- Sayuran
- Stall
- Dessert

Aturan untuk keterangan **pilih satu**:

```text
Minimum pilihan : 1
Maksimum pilihan: 1
```

---

## 5. Contoh Paket Catering A

### Informasi Paket

| Atribut | Nilai |
|---|---|
| Kode Paket | CAT001 |
| Nama Paket | Paket Catering A |
| Harga | Rp47.500 per porsi |
| Jenis Produk | Catering |

### Menu Tetap

| No | Nama Menu |
|---:|---|
| 1 | Nasi Putih |
| 2 | Kerupuk Udang |
| 3 | Air Mineral |

### Kelompok Pilihan

#### Sup — Pilih 1

| No | Pilihan Menu |
|---:|---|
| 1 | Sup Kimlo |
| 2 | Sup Bakso |
| 3 | Sup Ayam Sosis |

#### Olahan Daging Sapi — Pilih 1

| No | Pilihan Menu |
|---:|---|
| 1 | Sapi Teriyaki |
| 2 | Rendang |
| 3 | Bistik |

#### Olahan Tambahan — Pilih 1

| No | Pilihan Menu |
|---:|---|
| 1 | Dori Asam Manis |
| 2 | Dori Saus Mentega |
| 3 | Sambal Goreng Ati Kentang |

#### Sayuran — Pilih 1

| No | Pilihan Menu |
|---:|---|
| 1 | Salad Buah |
| 2 | Salad Sayuran |
| 3 | Gado-gado |
| 4 | Rujak |

#### Stall — Pilih 1

| No | Pilihan Menu |
|---:|---|
| 1 | Bakso Tahu |
| 2 | Mi Kocok |

#### Dessert — Pilih 1

| No | Pilihan Menu |
|---:|---|
| 1 | Buah Potong |
| 2 | Es Krim |

---

## 6. Contoh Paket Catering B

### Informasi Paket

| Atribut | Nilai |
|---|---|
| Kode Paket | CAT002 |
| Nama Paket | Paket Catering B |
| Harga | Rp42.500 per porsi |
| Jenis Produk | Catering |

### Menu Tetap

| No | Nama Menu |
|---:|---|
| 1 | Nasi Putih |
| 2 | Kerupuk Udang |
| 3 | Air Mineral |

### Kelompok Pilihan

#### Sup — Pilih 1

- Sup Kimlo
- Sup Bakso
- Sup Sosis

#### Olahan Ayam — Pilih 1

- Ayam Teriyaki
- Ayam Suwir
- Ayam Rica-rica

#### Olahan Tambahan — Pilih 1

- Dori Asam Manis
- Dori Saus Mentega
- Sambal Goreng Ati Kentang

#### Sayuran — Pilih 1

- Salad Buah
- Salad Sayuran
- Gado-gado
- Rujak Buah

#### Stall — Pilih 1

- Bakso Tahu
- Mi Kocok

#### Dessert — Pilih 1

- Buah Potong
- Es Krim

---

## 7. Rancangan Form Input

### 7.1 Form Informasi Paket

```text
Kode Paket      : [CAT001]
Nama Paket      : [Paket Catering A]
Harga per Porsi : [47500]
Deskripsi       : [.............................]
Status          : [Aktif]
```

### 7.2 Form Tambah Menu Tetap

```text
Nama Menu      : [Nasi Putih]
Jenis Komponen : [Menu Tetap]
Urutan Tampil  : [1]

[Simpan]
```

### 7.3 Form Tambah Kelompok Pilihan

```text
Nama Kelompok    : [Sup]
Minimum Pilihan  : [1]
Maksimum Pilihan : [1]
Urutan Tampil    : [2]

[Simpan]
```

### 7.4 Form Tambah Pilihan Menu

```text
Kelompok Pilihan : [Sup]
Nama Pilihan     : [Sup Kimlo]
Urutan Tampil    : [1]

[Simpan]
```

Pilihan berikutnya ditambahkan dengan cara yang sama:

- Sup Bakso
- Sup Ayam Sosis

---

## 8. Tampilan Halaman Admin

### Daftar Paket Catering

| Kode | Nama Paket | Harga per Porsi | Jumlah Kelompok | Aksi |
|---|---|---:|---:|---|
| CAT001 | Paket Catering A | Rp47.500 | 6 | Detail · Ubah · Kelola Isi · Hapus |
| CAT002 | Paket Catering B | Rp42.500 | 6 | Detail · Ubah · Kelola Isi · Hapus |

Urutan aksi:

```text
Detail → Ubah → Kelola Isi → Hapus
```

### Halaman Kelola Isi Paket

```text
Paket Catering A
Harga: Rp47.500 per porsi

[Menu Tetap] [Kelompok Pilihan]
```

#### Tab Menu Tetap

| Urutan | Nama Menu | Aksi |
|---:|---|---|
| 1 | Nasi Putih | Ubah · Hapus |
| 2 | Kerupuk Udang | Ubah · Hapus |
| 3 | Air Mineral | Ubah · Hapus |

#### Tab Kelompok Pilihan

| Urutan | Nama Kelompok | Aturan | Jumlah Opsi | Aksi |
|---:|---|---|---:|---|
| 1 | Sup | Pilih 1 | 3 | Detail · Ubah · Kelola Pilihan · Hapus |
| 2 | Olahan Daging Sapi | Pilih 1 | 3 | Detail · Ubah · Kelola Pilihan · Hapus |
| 3 | Olahan Tambahan | Pilih 1 | 3 | Detail · Ubah · Kelola Pilihan · Hapus |
| 4 | Sayuran | Pilih 1 | 4 | Detail · Ubah · Kelola Pilihan · Hapus |
| 5 | Stall | Pilih 1 | 2 | Detail · Ubah · Kelola Pilihan · Hapus |
| 6 | Dessert | Pilih 1 | 2 | Detail · Ubah · Kelola Pilihan · Hapus |

---

## 9. Tampilan Form Pemesanan Pelanggan

```text
Jumlah Porsi: [100]

Sup — pilih 1
( ) Sup Kimlo
( ) Sup Bakso
( ) Sup Ayam Sosis

Olahan Daging Sapi — pilih 1
( ) Sapi Teriyaki
( ) Rendang
( ) Bistik

Olahan Tambahan — pilih 1
( ) Dori Asam Manis
( ) Dori Saus Mentega
( ) Sambal Goreng Ati Kentang

Sayuran — pilih 1
( ) Salad Buah
( ) Salad Sayuran
( ) Gado-gado
( ) Rujak

Stall — pilih 1
( ) Bakso Tahu
( ) Mi Kocok

Dessert — pilih 1
( ) Buah Potong
( ) Es Krim
```

Karena setiap kelompok hanya boleh memilih satu, gunakan **radio button**, bukan checkbox.

---

## 10. Struktur Tabel Database

### 10.1 `produk`

```text
produk
- id
- kode_produk
- jenis_produk_id
- kategori_menu_id
- nama_produk
- harga
- satuan_harga
- deskripsi
- status
- created_at
- updated_at
```

Contoh:

| id | kode_produk | nama_produk | jenis_produk | kategori_menu_id | harga |
|---:|---|---|---|---:|---:|
| 1 | CAT001 | Paket Catering A | Catering | NULL | 47500 |
| 2 | CAT002 | Paket Catering B | Catering | NULL | 42500 |

### 10.2 `komponen_paket`

```text
komponen_paket
- id
- produk_id
- nama_komponen
- tipe_komponen
- minimum_pilihan
- maksimum_pilihan
- urutan
- created_at
- updated_at
```

Nilai `tipe_komponen`:

```text
tetap
pilihan
```

Contoh:

| id | produk_id | nama_komponen | tipe | minimum | maksimum |
|---:|---:|---|---|---:|---:|
| 1 | 1 | Nasi Putih | tetap | 0 | 0 |
| 2 | 1 | Sup | pilihan | 1 | 1 |
| 3 | 1 | Olahan Daging Sapi | pilihan | 1 | 1 |
| 4 | 1 | Kerupuk Udang | tetap | 0 | 0 |

### 10.3 `pilihan_komponen_paket`

```text
pilihan_komponen_paket
- id
- komponen_paket_id
- nama_pilihan
- urutan
- created_at
- updated_at
```

Contoh:

| id | komponen_paket_id | nama_pilihan |
|---:|---:|---|
| 1 | 2 | Sup Kimlo |
| 2 | 2 | Sup Bakso |
| 3 | 2 | Sup Ayam Sosis |

### 10.4 `pilihan_pesanan_catering`

```text
pilihan_pesanan_catering
- id
- detail_pesanan_id
- komponen_paket_id
- pilihan_komponen_paket_id
- created_at
- updated_at
```

Contoh:

| detail_pesanan_id | Kelompok | Pilihan Pelanggan |
|---:|---|---|
| 15 | Sup | Sup Bakso |
| 15 | Olahan Daging Sapi | Rendang |
| 15 | Dessert | Es Krim |

---

## 11. Relasi Data

```text
Produk
  │
  └── Paket Catering A
          │
          ├── Komponen Tetap
          │     ├── Nasi Putih
          │     ├── Kerupuk Udang
          │     └── Air Mineral
          │
          └── Kelompok Pilihan
                ├── Sup
                │     ├── Sup Kimlo
                │     ├── Sup Bakso
                │     └── Sup Ayam Sosis
                │
                ├── Olahan Daging Sapi
                │     ├── Sapi Teriyaki
                │     ├── Rendang
                │     └── Bistik
                │
                └── Dessert
                      ├── Buah Potong
                      └── Es Krim
```

---

## 12. Aturan Validasi

1. Setiap paket catering wajib memiliki nama dan harga.
2. Harga paket disimpan per porsi.
3. Komponen tetap tidak memerlukan pilihan pelanggan.
4. Kelompok pilihan wajib memiliki nilai minimum dan maksimum pilihan.
5. Untuk aturan **pilih satu**, nilai minimum dan maksimum sama-sama `1`.
6. Pesanan tidak dapat disimpan sebelum seluruh kelompok wajib dipilih.
7. Pilihan menu tidak dianggap sebagai paket catering tersendiri.
8. Pilihan menu hanya ditampilkan pada paket yang terkait.
9. Paket yang sudah pernah digunakan dalam transaksi sebaiknya dinonaktifkan, bukan dihapus permanen.
10. Perubahan harga paket tidak boleh mengubah harga pada transaksi lama.

---

## 13. Kesimpulan

Struktur yang digunakan:

```text
Paket Catering
    ├── Menu Tetap
    └── Kelompok Pilihan
            └── Pilihan Menu
```

Contoh:

```text
Paket Catering A
    ├── Nasi Putih
    ├── Kerupuk Udang
    ├── Air Mineral
    └── Sup — pilih 1
            ├── Sup Kimlo
            ├── Sup Bakso
            └── Sup Ayam Sosis
```

Dengan rancangan ini:

- Paket Catering A dan Paket Catering B disimpan sebagai produk.
- Sup Kimlo, Sup Bakso, dan Sup Ayam Sosis disimpan sebagai pilihan menu.
- Tidak perlu menggunakan istilah atau tabel `variant`.
- Struktur data lebih jelas, tidak redundan, dan sesuai dengan proses pemesanan catering.
