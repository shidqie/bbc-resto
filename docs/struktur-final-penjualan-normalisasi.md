# Struktur Final Modul Penjualan dan Database Ternormalisasi

## 1. Struktur Sidebar

```text
Penjualan
├── Semua Pesanan
├── Dine In
├── Catering
├── Nasi Box
└── Pembayaran
```

## 2. Tabel Halaman

### Semua Pesanan

```text
No. | Tanggal & Waktu | ID Pesanan | Jenis | Pelanggan | Meja | Total | Status Pesanan | Pembayaran | Aksi
```

Aksi utama:

```text
Detail | Proses
```

Isi tombol **Proses** menyesuaikan kondisi pesanan:

```text
Konfirmasi
Tolak
Bayar
Validasi Pembayaran
Batalkan
Cetak Bukti
```

### Dine In

```text
No. | Tanggal & Waktu | ID Pesanan | Pelanggan | Meja | Jumlah Item | Total | Status Pesanan | Status KOT | Pembayaran | Aksi
```

`Jumlah Item` dihitung dari tabel detail pesanan dan tidak disimpan sebagai kolom tetap.

```php
->withCount('detailPesanan')
```

### Catering

```text
No. | Tanggal Pesan | ID Pesanan | Pelanggan | Tanggal Acara | Jumlah Porsi | Total | Status Pesanan | Pembayaran | Aksi
```

### Nasi Box

```text
No. | Tanggal Pesan | ID Pesanan | Pelanggan | Tanggal Dibutuhkan | Jumlah Box | Total | Status Pesanan | Pembayaran | Aksi
```

### Pembayaran

```text
No. | Tanggal Bayar | ID Pembayaran | ID Pesanan | Jenis Pesanan | Pelanggan/Meja | Jenis Pembayaran | Metode | Nominal | Status | Aksi
```

---

# 3. Struktur Database

## Tabel `pesanan`

```text
pesanan
- id
- kode_pesanan
- pelanggan_id nullable
- meja_id nullable
- jenis_pesanan
- tanggal_pesanan
- status_pesanan
- subtotal
- diskon
- biaya_pengantaran
- total
- created_by nullable
- created_at
- updated_at
```

Nilai `jenis_pesanan`:

```text
dine_in
catering
nasi_box
```

Data berikut tidak perlu disimpan ulang di tabel `pesanan`:

```text
nama_pelanggan
nama_meja
jumlah_item
status_pembayaran
```

## Tabel `detail_pesanan`

```text
detail_pesanan
- id
- pesanan_id
- menu_id nullable
- paket_id nullable
- jumlah
- harga_satuan
- subtotal
- catatan nullable
- created_at
- updated_at
```

## Tabel `detail_pesanan_pilihan`

```text
detail_pesanan_pilihan
- id
- detail_pesanan_id
- paket_kelompok_id
- menu_id
- jumlah
- harga_tambahan
- created_at
- updated_at
```

Tabel ini menyimpan pilihan aktual paket, misalnya:

```text
Nasi Box Paket B
- Nasi Liwet
- Ayam Bakar
- Semangka
```

## Tabel `pesanan_dine_in`

```text
pesanan_dine_in
- id
- pesanan_id
- jumlah_tamu
- nama_pemesan nullable
- sesi_meja_id nullable
- created_at
- updated_at
```

## Tabel `pesanan_catering`

```text
pesanan_catering
- id
- pesanan_id
- tanggal_acara
- waktu_acara
- jumlah_porsi
- jenis_layanan
- alamat_acara
- catatan_acara nullable
- created_at
- updated_at
```

## Tabel `pesanan_nasi_box`

```text
pesanan_nasi_box
- id
- pesanan_id
- tanggal_dibutuhkan
- waktu_dibutuhkan
- jumlah_box
- metode_penerimaan
- alamat_pengantaran nullable
- catatan nullable
- created_at
- updated_at
```

## Tabel `pembayaran`

```text
pembayaran
- id
- kode_pembayaran
- pesanan_id
- tanggal_pembayaran
- jenis_pembayaran
- metode_pembayaran
- nominal
- bukti_pembayaran nullable
- status_pembayaran
- diverifikasi_oleh nullable
- diverifikasi_pada nullable
- catatan nullable
- created_at
- updated_at
```

Nilai `jenis_pembayaran`:

```text
dp
pelunasan
pembayaran_penuh
```

Nilai `status_pembayaran`:

```text
menunggu_validasi
terverifikasi
ditolak
```

Status keseluruhan pembayaran dihitung dari total pembayaran terverifikasi dibandingkan dengan total pesanan.

## Tabel `kot`

```text
kot
- id
- kode_kot
- pesanan_id
- tanggal_kot
- status_kot
- dicetak_pada nullable
- diproses_pada nullable
- selesai_pada nullable
- created_at
- updated_at
```

Satu pesanan dapat memiliki beberapa KOT, terutama saat konsumen menambah pesanan.

## Tabel `detail_kot`

```text
detail_kot
- id
- kot_id
- detail_pesanan_id
- jumlah
- catatan nullable
- created_at
- updated_at
```

## Tabel `pelanggan`

```text
pelanggan
- id
- user_id nullable
- nama_pelanggan
- email nullable
- nomor_telepon
- alamat_default nullable
- created_at
- updated_at
```

Alamat pengantaran aktual tetap disimpan pada data pesanan catering atau nasi box.

## Tabel `meja`

```text
meja
- id
- nomor_meja
- kapasitas
- token_qr
- status_aktif
- created_at
- updated_at
```

Status meja saat ini sebaiknya diperoleh dari sesi meja atau pesanan aktif.

---

# 4. Relasi Database

```text
pelanggan
    └──< pesanan

meja
    └──< pesanan

pesanan
    ├──< detail_pesanan
    │       └──< detail_pesanan_pilihan
    ├──< pembayaran
    ├──< kot
    │       └──< detail_kot
    ├─── pesanan_dine_in
    ├─── pesanan_catering
    └─── pesanan_nasi_box
```

Relasi paket sampai bahan baku:

```text
paket
    └──< paket_kelompok
            └──< paket_kelompok_item
                    └── menu
                            └──< resep_menu
                                    └── bahan_baku
```

---

# 5. Data yang Dihitung, Bukan Disimpan

```text
Jumlah Item
Jumlah Menu per Kategori
Total Pembayaran
Sisa Tagihan
Status Lunas atau Belum Lunas
Jumlah Pesanan Pelanggan
Ketersediaan Menu
```

Contoh:

```php
$jumlahItem = $pesanan->detailPesanan()->sum('jumlah');

$totalPembayaran = $pesanan->pembayaran()
    ->where('status_pembayaran', 'terverifikasi')
    ->sum('nominal');

$sisaTagihan = $pesanan->total - $totalPembayaran;
```

---

# 6. Kesimpulan Normalisasi

Struktur ini sudah konsisten dengan normalisasi karena:

1. Data pelanggan tidak disalin ulang ke tabel pesanan.
2. Data meja direferensikan menggunakan foreign key.
3. Pembayaran dipisahkan karena satu pesanan dapat memiliki beberapa pembayaran.
4. KOT dipisahkan karena satu pesanan dapat memiliki beberapa KOT.
5. Data khusus dine-in, catering, dan nasi box disimpan pada tabel masing-masing.
6. Pilihan paket disimpan per item, bukan digabung dalam satu kolom teks.
7. Jumlah item, total pembayaran, sisa tagihan, dan status lunas dihitung dari relasi.
