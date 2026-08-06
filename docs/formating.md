# Standar Format Input Angka Sistem
## Perancangan Sistem Informasi Penjualan dan Persediaan Bahan Baku Berbasis Web
### Rumah Makan Saung Babakan Cinta

## Tujuan

Dokumen ini digunakan sebagai standar implementasi seluruh field numerik pada sistem agar tampilan, validasi, dan penyimpanan data konsisten pada setiap modul.

---

# 1. Standar Format Angka

## 1.1 Bilangan Bulat

Digunakan untuk data yang tidak memerlukan nilai pecahan.

Contoh:

- Jumlah Pesanan
- Kapasitas Meja
- Nomor Meja
- Jumlah Porsi
- Jumlah Box
- Jumlah Orang
- Masa Simpan (Hari)

Format:

```
1
5
10
100
```

Validasi

- Minimal 1
- Tidak boleh negatif
- Tidak boleh huruf
- Tidak boleh simbol selain angka

---

## 1.2 Bilangan Desimal

Digunakan untuk data berat, volume, dan takaran.

Contoh:

- Berat
- Volume
- Takaran Resep
- Stok Bahan Baku
- Konversi Satuan
- Jarak Pengiriman

Format Tampilan

```
0,25
1,50
12,75
```

Format Database

```
0.25
1.50
12.75
```

---

# 2. Standar Format Rupiah

Seluruh nominal uang wajib menggunakan format Rupiah.

Format Tampilan

```
Rp 25.000
Rp 150.000
Rp 2.500.000
```

Format Database

```
25000
150000
2500000
```

Aturan

- Menggunakan simbol Rp
- Menggunakan pemisah ribuan titik
- Tidak menggunakan angka desimal
- Tidak disimpan beserta simbol Rp di database

---

# 3. Standar Input Nomor Telepon

Format Tampilan

```
0812 3456 7890
```

Format Database

```
081234567890
```

Validasi

- Minimal 10 digit
- Maksimal 15 digit
- Hanya angka
- Menggunakan tipe Text
- Angka nol di depan tidak boleh hilang

---

# 4. Standar Nomor Meja

Gunakan format sederhana.

```
Meja 1
Meja 2
Meja 3
```

Tidak disarankan

```
Meja-01
M001
Table01
```

---

# 5. Standar Persentase

Contoh

```
10%
25%
50%
100%
```

Digunakan pada

- Pajak
- DP
- Diskon

Validasi

Nilai

```
0 - 100
```

---

# 6. Standar Format Tanggal

Gunakan format tampilan

```
06 Agustus 2026
```

atau

```
06 Agu 2026
```

Jika menggunakan waktu

```
06 Agu 2026, 14.35 WIB
```

Kolom tabel transaksi menggunakan nama

```
Tanggal Pesan
```

Bukan

```
Tanggal
Tgl
Tanggal Order
```

---

# 7. Standar Input Jumlah

Gunakan komponen Quantity.

```
[-]   1   [+]
```

Aturan

- Minimal 1
- Tidak boleh negatif
- Tidak boleh melebihi stok
- Update subtotal otomatis

---

# 8. Standar Placeholder

Gunakan placeholder yang jelas.

Benar

```
Masukkan harga menu
Masukkan stok
Masukkan jumlah pesanan
Masukkan takaran resep
Masukkan nomor WhatsApp
```

Salah

```
Input...
Masukkan...
Isi data...
```

---

# 9. Standar Satuan

Satuan tidak diketik manual.

Gunakan Dropdown.

Contoh

```
Kilogram (kg)
Gram (gr)
Liter (L)
Mililiter (mL)
Butir
Buah
Botol
Pack
Porsi
Box
Karung
Ikat
Lembar
```

---

# 10. Standar Validasi Input

Seluruh input angka harus memiliki validasi.

- Tidak boleh kosong
- Tidak boleh huruf
- Tidak boleh karakter khusus
- Tidak boleh negatif
- Tidak boleh melebihi batas maksimum
- Menampilkan pesan kesalahan secara realtime

---

# Standar Format Setiap Modul

---

# Dashboard

| Field | Format |
|--------|--------|
| Total Penjualan | Rp 25.000.000 |
| Total Pesanan | 120 |
| Total Menu | 80 |
| Total Pelanggan | 560 |

---

# Data Menu

| Field | Format |
|--------|--------|
| Harga | Rp 25.000 |
| Estimasi Waktu | 15 Menit |
| Stok Menu | 25 Porsi |

---

# Paket Catering

| Field | Format |
|--------|--------|
| Harga per Porsi | Rp 47.500 |
| Minimal Pemesanan | 50 Porsi |

---

# Paket Nasi Box

| Field | Format |
|--------|--------|
| Harga | Rp 35.000 |
| Minimal Pesanan | 10 Box |

---

# Data Meja

| Field | Format |
|--------|--------|
| Nomor Meja | Meja 1 |
| Kapasitas | 4 Orang |

---

# Data Bahan Baku

| Field | Format Tampilan |
|--------|----------------|
| Harga Beli | Rp 18.000 |
| Stok Awal | 25 kg |
| Stok Saat Ini | 18,5 kg |
| Stok Minimum | 5 kg |
| Isi Kemasan | 5 kg |
| Berat Bersih | 1 kg |

Validasi

- Mendukung angka desimal
- Tidak boleh stok negatif
- Harga otomatis format Rupiah

---

# Stok Bahan Baku

| Field | Format |
|--------|--------|
| Stok Masuk | 20 kg |
| Stok Keluar | 5,5 kg |
| Saldo | 45,5 kg |

---

# Resep Menu

| Field | Format |
|--------|--------|
| Takaran | 0,25 kg |
| Jumlah | 500 gr |
| Pemakaian | 1,5 Liter |

Mendukung angka desimal.

---

# Pengadaan

| Field | Format |
|--------|--------|
| Jumlah Dibeli | 25 kg |
| Harga Satuan | Rp 16.000 |
| Total Harga | Rp 400.000 |

---

# Penerimaan Bahan

| Field | Format |
|--------|--------|
| Jumlah Diterima | 24,5 kg |
| Selisih | 0,5 kg |

---

# Penyesuaian Stok

| Field | Format |
|--------|--------|
| Penambahan | 5 kg |
| Pengurangan | 2 kg |
| Saldo Baru | 28 kg |

---

# Dine In

| Field | Format |
|--------|--------|
| Qty | 2 |
| Harga | Rp 25.000 |
| Subtotal | Rp 50.000 |
| Pajak | Rp 5.000 |
| Total | Rp 55.000 |

---

# Catering

| Field | Format |
|--------|--------|
| Jumlah Porsi | 150 |
| Harga/Porsi | Rp 47.500 |
| DP | Rp 3.562.500 |
| Pelunasan | Rp 3.562.500 |

---

# Nasi Box

| Field | Format |
|--------|--------|
| Jumlah Box | 50 |
| Harga/Box | Rp 35.000 |
| Total | Rp 1.750.000 |

---

# Pembayaran

| Field | Format |
|--------|--------|
| Total Tagihan | Rp 250.000 |
| Nominal Dibayar | Rp 300.000 |
| Kembalian | Rp 50.000 |

---

# Pengantaran

| Field | Format |
|--------|--------|
| Jarak | 15,5 km |
| Ongkir | Rp 100.000 |

---

# Laporan

Seluruh nominal menggunakan format Rupiah.

Seluruh jumlah menggunakan pemisah ribuan.

Contoh

```
Rp 25.000.000
```

Seluruh tanggal menggunakan

```
06 Agu 2026
```

atau

```
06 Agu 2026, 14.35 WIB
```

---

# Standar Komponen Input

## Input Rupiah

```
┌──────────────────────────────┐
│ Harga Menu                   │
│ Rp 25.000                    │
└──────────────────────────────┘
```

---

## Input Quantity

```
┌──────────────────────────────┐
│ Jumlah                       │
│  [-]   5   [+]               │
└──────────────────────────────┘
```

---

## Input Berat

```
┌──────────────────────────────┐
│ Berat                        │
│ 2,5            [ kg ▼ ]      │
└──────────────────────────────┘
```

---

## Input Nomor WhatsApp

```
┌──────────────────────────────┐
│ Nomor WhatsApp               │
│ 081234567890                 │
└──────────────────────────────┘
```

---

# Kesimpulan Standar

- Semua nominal uang menggunakan format **Rp** dengan pemisah ribuan.
- Semua tanggal transaksi menggunakan **Tanggal Pesan**.
- Semua nomor telepon menggunakan tipe **Text**.
- Semua satuan dipilih melalui **Dropdown**.
- Semua harga disimpan sebagai angka tanpa format Rupiah.
- Semua stok bahan baku mendukung angka desimal.
- Seluruh field numerik memiliki validasi otomatis.
- Tampilan angka, tanggal, dan mata uang harus konsisten di seluruh modul sistem.