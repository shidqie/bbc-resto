# Prompt: Bangun Fitur POS Dine In — Sistem Katering & Nasi Box (SBC)

## Konteks Proyek

Aplikasi ini adalah sistem manajemen restoran/katering bernama **SBC** dengan modul yang sudah ada: Dashboard, POS Kasir, Pesanan, Pesanan Khusus, Menu, Bahan Baku, Pengadaan, Laporan, dan Pengguna (Data Pegawai & Data Pelanggan). Sistem ini melayani 3 jenis transaksi: **Dine In**, **Nasi Box**, dan **Catering**, masing-masing dengan alur bisnis berbeda.

Tugas kamu: implementasikan modul **POS Dine In** sesuai spesifikasi di bawah, terintegrasi dengan modul Bahan Baku (potong stok) dan Pengguna (role & permission) yang sudah ada.

---

## 1. Model Bisnis: Bayar Dulu, Terpusat di Kasir

Dine In di sistem ini pakai **Model A (pay first)**, TAPI dengan pembagian tugas berikut:

- **Input pesanan**: FLEKSIBEL, bisa dilakukan oleh Kasir maupun Pelayan (dari tablet/HP masing-masing)
- **Pembayaran**: TERPUSAT, hanya bisa diproses di satu titik yaitu Kasir

Pesanan **tidak** dikirim ke dapur sebelum pembayaran selesai. Ini pilihan sadar demi kontrol keuangan yang ketat (semua uang lewat satu pintu), dengan trade-off tamu/pelayan tetap perlu ke kasir untuk menuntaskan pembayaran.

---

## 2. Konsep Inti: Manajemen Meja (Table Management)

Satu meja = satu tab pesanan yang menampung item-item sebelum dibayar sekali di kasir.

### Status Meja

- `kosong` — siap dipakai
- `menunggu_pembayaran` — sudah ada pesanan tercatat, belum dibayar
- `terisi` — sudah dibayar, makanan diproses/disajikan, tamu masih di tempat

Tidak ada fitur reservasi/booking meja di versi ini — status meja hanya mengikuti siklus transaksi aktual.

### Siklus Status Meja

```
kosong
  → (pesanan pertama diinput, oleh kasir ATAU pelayan) → menunggu_pembayaran
menunggu_pembayaran
  → (kasir proses pembayaran) → terisi
terisi
  → (tamu selesai & pergi) → kosong lagi
```

Catatan: kalau tamu mau nambah pesanan setelah pembayaran pertama selesai, itu dianggap **transaksi baru** yang juga harus dibayar di kasir sebelum dikirim ke dapur (karena stok baru terpotong saat bayar, bukan saat input).

---

## 3. Role & Permission

| Role        | Buka Meja | Input Pesanan | Proses Pembayaran | Cetak Struk | Void/Refund |
| ----------- | --------- | ------------- | ----------------- | ----------- | ----------- |
| **Kasir**   | ✅        | ✅            | ✅                | ✅          | ✅          |
| **Pelayan** | ✅        | ✅            | ❌                | ❌          | ❌          |

Sistem harus mendukung skenario campuran: kadang kasir yang input pesanan sendiri (tamu datang langsung ke kasir), kadang pelayan yang input dari meja (tamu dilayani di tempat duduk). Keduanya sah, gate lewat permission role, bukan lewat device.

### Requirement teknis penting: Real-time sync & audit log

- Kalau pesanan diinput pelayan dari tablet, kasir harus langsung melihat pesanan itu muncul di sistem (real-time/polling pendek) begitu tamu/pelayan datang untuk bayar — tidak perlu input ulang manual.
- Setiap item pesanan menyimpan `diinput_oleh` (staff_id) untuk audit/tracing.
- Setiap pembayaran menyimpan `diproses_oleh` (staff_id, harus role kasir) untuk kontrol kas.

---

## 4. Alur Lengkap Dine In

```
1. Staf (kasir ATAU pelayan) pilih/buka meja → input item pesanan ke tab meja
   → status meja: "menunggu_pembayaran"
   → pesanan BELUM terkirim ke dapur di titik ini

2. Tamu (atau pelayan yang mewakili) menuju KASIR untuk menuntaskan pembayaran
   → Kasir cari nomor meja di sistem, tagihan otomatis muncul
     (tidak perlu input ulang item)

3. Kasir proses pembayaran: cash / QRIS / kartu

4. Begitu status pembayaran = lunas, sistem generate 3 jenis struk sekaligus
   (lihat detail di bagian 4a) dan otomatis:
   → Potong stok bahan baku, dihitung dari resep tiap menu di tagihan
     dikali qty (integrasi ke modul Bahan Baku)
   → Status meja berubah jadi "terisi" (makanan sedang diproses/disajikan)

5. Kalau tamu mau nambah pesanan → ulangi dari langkah 1 sebagai
   transaksi baru terhubung ke meja yang sama

6. Tamu selesai & pergi → staf reset status meja jadi "kosong"
```

### Poin kritis yang jangan sampai terlewat:

- Stok **hanya** terpotong setelah status pembayaran = lunas, dikonfirmasi oleh Kasir. Tidak ada potong stok di titik input pesanan.
- Kalau kitchen printer gagal cetak (mati/kertas habis), sistem harus punya fallback notifikasi ke kasir (alert di UI POS Kasir) supaya order tidak "hilang" di dapur tanpa disadari.
- Void/refund transaksi HANYA bisa dilakukan oleh role Kasir, tidak oleh Pelayan.

---

## 4a. Tiga Jenis Struk (Dicetak Bersamaan Saat Pembayaran Lunas)

Begitu kasir menyelesaikan pembayaran, sistem generate & cetak **3 dokumen berbeda sekaligus**, dari 1 titik cetak (kasir) kecuali disebutkan lain:

| #   | Nama Struk               | Isi                                                                                         | Dicetak di                 | Diserahkan ke             |
| --- | ------------------------ | ------------------------------------------------------------------------------------------- | -------------------------- | ------------------------- |
| 1   | **Struk Pemesanan**      | Rincian item, harga, total, metode bayar — bukti transaksi resmi                            | Printer kasir              | Konsumen                  |
| 2   | **Struk Meja / Checker** | Nomor meja + ringkasan item (tanpa harga) — dipakai untuk pencocokan saat makanan diantar   | Printer kasir              | Konsumen (dibawa ke meja) |
| 3   | **Struk Dapur (KOT)**    | Nomor meja + daftar item yang perlu dimasak + catatan (misal "pedas level 2") — TANPA harga | Kitchen printer (di dapur) | Dapur (tidak ke konsumen) |

### Alur distribusi struk:

```
Kasir selesaikan pembayaran
        ↓
Sistem cetak 3 struk sekaligus (2 di printer kasir, 1 otomatis ke kitchen printer)
        ↓
Kasir serahkan ke konsumen: Struk Pemesanan + Struk Meja/Checker
        ↓
Konsumen bawa Struk Meja/Checker ke meja duduknya
        ↓
Pelayan/pengantar makanan mencocokkan Struk Meja/Checker (yang dipegang
konsumen di meja) dengan pesanan yang siap diantar dari dapur,
supaya makanan sampai ke meja yang benar
```

### Fungsi tiap struk secara spesifik:

- **Struk Pemesanan** = bukti transaksi finansial untuk konsumen (kalau butuh reimburse/komplain harga)
- **Struk Meja/Checker** = alat bantu operasional supaya pelayan tidak salah antar makanan ke meja lain, terutama saat restoran ramai dan banyak meja aktif bersamaan
- **Struk Dapur (KOT)** = instruksi masak untuk dapur, tidak memuat info harga sama sekali

### Requirement teknis:

- Kasir idealnya punya 1 printer struk (untuk Struk Pemesanan & Struk Meja/Checker — bisa 2 lembar terpisah atau dari 1 printer yang sama, cetak 2x)
- Kitchen printer terpisah secara fisik/network dari printer kasir, otomatis terima print job begitu status pembayaran = lunas
- Struk Meja/Checker sebaiknya punya nomor meja dalam font besar/mencolok di bagian atas, supaya gampang terlihat sekilas oleh pelayan yang sedang sibuk

---

## 5. Fungsi POS Kasir (Peran sebagai Hub)

POS Kasir bukan cuma titik transaksi, tapi juga pusat kontrol:

1. **Transaksi langsung** — untuk tamu yang datang sendiri ke kasir / takeaway
2. **Cari & proses tagihan meja** — dari pesanan yang sudah diinput pelayan
3. **Peta status semua meja** — grid visual dengan warna sesuai status (kosong/menunggu_pembayaran/terisi), supaya kasir tahu meja mana yang perlu segera diproses pembayarannya
4. **Void/refund** — kalau ada kesalahan transaksi
5. **Rekap harian** — sumber data ke modul Laporan

---

## 6. Struktur Data yang Disarankan

```
tabel: meja
- id, nomor_meja, kapasitas, status (enum: kosong/menunggu_pembayaran/terisi/reserved)

tabel: pesanan_dinein  (satu row = satu transaksi per meja, bisa lebih dari satu per hari)
- id, meja_id, status (enum: menunggu_pembayaran/lunas/selesai), dibuka_oleh (staff_id),
  dibuka_pada (timestamp), dibayar_pada (timestamp, nullable)

tabel: item_pesanan_dinein
- id, pesanan_dinein_id, menu_id, qty, catatan (misal "pedas level 2, tanpa bawang"),
  diinput_oleh (staff_id), diinput_pada (timestamp)

tabel: pembayaran_dinein
- id, pesanan_dinein_id, metode_bayar (enum: cash/qris/kartu), total,
  diproses_oleh (staff_id, WAJIB role kasir), diproses_pada (timestamp),
  status (enum: lunas/void/refund)
```

Trigger saat `pembayaran_dinein.status = lunas` tersimpan:

1. Update `pesanan_dinein.status` → `lunas`, isi `dibayar_pada`
2. Generate & kirim KOT ke kitchen printer
3. Potong stok bahan baku (bedah resep tiap `menu_id` di `item_pesanan_dinein` dikali `qty`)
4. Update `meja.status` → `terisi`

---

## 7. UI/UX yang Dibutuhkan

1. **Halaman Peta Meja** — grid visual semua meja dengan warna sesuai status, dipakai baik oleh kasir maupun pelayan (dengan tampilan/permission berbeda sesuai role)
2. **Halaman Input Pesanan** (kasir & pelayan) — pilih menu dari katalog (grid dengan gambar/kategori), tambahkan ke tab meja, kolom catatan per item
3. **Halaman Kasir: Cari & Bayar Tagihan** (khusus kasir) — cari nomor meja, tampilkan rekap item, pilih metode bayar, tombol proses pembayaran & cetak struk
4. Desain mengikuti gaya existing sistem: sidebar dark navy, konten area putih, komponen rounded dengan aksen biru untuk elemen interaktif.

---

## 8. Out of Scope (catat sebagai future enhancement)

- Split bill per orang/per item
- Reservasi/booking meja — TIDAK termasuk di versi ini sama sekali, jangan bangun tabel/status terkait reservasi
- Pembayaran langsung di meja (portable payment/printer) — versi ini sengaja disentralisasi ke kasir; bisa di-extend nanti tanpa merombak struktur data di atas
- Integrasi payment gateway spesifik — baru ditentukan metode (cash/QRIS/kartu), provider belum dipilih
