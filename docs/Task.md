# PRD: Sistem POS Restoran dengan QR Menu Digital

**Versi:** 1.0
**Tanggal:** 21 Juli 2026
**Status:** Draft

---

## 1. Latar Belakang

Restoran membutuhkan sistem pemesanan yang menggabungkan kenyamanan menu digital (QR code) dengan pengalaman layanan manual melalui pelayan, tanpa menghilangkan sentuhan personal antara pelayan dan konsumen. Selain itu, sistem perlu menjamin akurasi pesanan sampai ke dapur, mencegah kesalahan antar makanan, serta secara otomatis mengelola stok bahan baku setiap kali transaksi terjadi.

## 2. Tujuan

- Menyediakan menu digital via QR code sebagai katalog interaktif bagi konsumen.
- Memastikan pesanan konsumen tercatat akurat dari pelayan hingga ke sistem.
- Mencegah kesalahan antar makanan melalui mekanisme verifikasi di meja.
- Mengotomasi pengurangan stok bahan baku berdasarkan resep tiap menu.
- Menghasilkan 3 jenis struk dengan fungsi berbeda: struk dapur, struk meja (konsumen), dan struk pembayaran (nota).

## 3. Aktor Sistem

| Aktor                                                                                  | Peran                                                                                  |
| -------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------- |
| -------------------------------------------------------------------------------------- |
| Konsumen                                                                               | Melihat menu via QR, menerima struk meja, melakukan pembayaran                         |
| Pelayan                                                                                | Mencatat pesanan manual dari konsumen, menyerahkan ke kasir, mengantar struk & makanan |
| Kasir                                                                                  | Menginput pesanan ke sistem POS, memproses pembayaran                                  |
| Dapur                                                                                  | Mengolah pesanan berdasarkan struk dapur                                               |
| Sistem POS                                                                             | Mencatat transaksi, mencetak struk, mengurangi stok otomatis                           |

## 4. Ruang Lingkup (Scope)

**Termasuk dalam scope:**

- Menu digital via QR code (read-only, tanpa self-order)
- Pencatatan manual pesanan oleh pelayan
- Input pesanan oleh kasir ke sistem
- Cetak otomatis 3 jenis struk
- Pengurangan stok otomatis berbasis resep (BOM)
- Proses pembayaran dan status transaksi pembayaran menggunaka paymnt gateaway midtrans

**Tidak termasuk dalam scope (fase ini):**

- Self-order langsung dari HP konsumen
- Sistem reservasi meja
- Program loyalty/membership terintegrasi

## 5. Alur Proses (User Flow)

### 5.1 Alur Pemesanan

1. Konsumen duduk di meja, scan QR code, melihat menu digital.
2. Konsumen memanggil pelayan dan menyebutkan pesanan.
3. Pelayan mencatat manual, mencakup: hari/tanggal, nomor meja, nama konsumen, dan detail pesanan (termasuk catatan khusus per item).
4. Pelayan menyerahkan catatan ke kasir.
5. Kasir menginput data ke sistem POS: nomor meja, nama konsumen, item pesanan beserta catatan khusus.
6. Sistem menghasilkan:
    - Struk dapur (item + catatan khusus, tanpa harga)
    - Struk meja (tanggal, nama, nomor meja, item + jumlah)
    - Pengurangan stok bahan baku otomatis berdasarkan resep tiap menu
7. Pelayan mengantar struk meja ke konsumen sebagai bukti pesanan masuk.

### 5.2 Alur Produksi & Verifikasi

8. Dapur mengolah pesanan sesuai instruksi di struk dapur.
9. Pelayan mengantar makanan ke meja.
10. Pelayan memverifikasi kesesuaian makanan dengan struk meja sebelum diantar ke konsumen.

### 5.3 Alur Pembayaran

11. Konsumen selesai makan, langsung mendatangi kasir untuk membayar.
12. Kasir membuka kembali data order berdasarkan nomor meja/nama konsumen.
13. Konsumen melakukan pembayaran (tunai/kartu/QRIS).
14. Sistem mencetak struk konsumen (nota) berisi rincian harga dan total, serta mengubah status transaksi menjadi lunas.

## 6. Spesifikasi 3 Jenis Struk

| Struk                 | Waktu cetak             | Pemegang                 | Isi                                                             |
| --------------------- | ----------------------- | ------------------------ | --------------------------------------------------------------- |
| Struk dapur           | Saat kasir submit order | Dapur                    | Item, jumlah, catatan khusus (tanpa harga, tanpa nama konsumen) |
| Struk meja            | Saat kasir submit order | Konsumen (di meja)       | Tanggal, nama konsumen, nomor meja, item + jumlah               |
| Struk konsumen (nota) | Saat pembayaran selesai | Konsumen (dibawa pulang) | Tanggal, nama, rincian item, harga, total, metode pembayaran    |

## 7. Kebutuhan Data (Data Requirements)

### 7.1 Data Master

- **Menu**: nama, kategori, harga, deskripsi
- **Resep/BOM**: daftar bahan baku dan takaran per menu
- **Bahan baku**: nama, satuan, stok saat ini, ambang batas minimum

### 7.2 Data Transaksi

- Nomor meja
- Nama konsumen
- Tanggal/waktu transaksi
- Daftar item pesanan (menu, jumlah, catatan khusus)
- Status transaksi (baru, diproses, selesai, lunas)
- Metode pembayaran

## 8. Kebutuhan Fungsional

| ID   | Kebutuhan                                                                         |
| ---- | --------------------------------------------------------------------------------- |
| F-01 | Sistem dapat menampilkan menu digital saat QR di-scan                             |
| F-02 | Kasir dapat menginput pesanan berdasarkan catatan pelayan                         |
| F-03 | Sistem mencetak struk dapur otomatis setelah input pesanan                        |
| F-04 | Sistem mencetak struk meja otomatis setelah input pesanan                         |
| F-05 | Sistem mengurangi stok bahan baku otomatis berdasarkan resep saat pesanan diinput |
| F-06 | Sistem dapat memanggil kembali data order berdasarkan nomor meja/nama konsumen    |
| F-07 | Sistem mencetak struk konsumen (nota) setelah pembayaran dikonfirmasi             |
| F-08 | Sistem memberikan peringatan jika stok bahan baku di bawah ambang batas minimum   |

## 9. Kebutuhan Non-Fungsional

- Waktu cetak struk tidak lebih dari 5 detik setelah submit.
- Sistem harus tetap dapat mencatat transaksi meski printer dapur/kasir sedang offline (antrian cetak).
- Data transaksi tersimpan minimal 1 tahun untuk keperluan audit/laporan.

## 10. Pertanyaan Terbuka (Open Questions)

Hal-hal berikut masih perlu diputuskan sebelum masuk ke tahap desain teknis:

1. Apakah nama konsumen wajib diisi setiap transaksi, atau opsional? wajib
2. Apakah struk meja mencantumkan harga, atau murni tanpa harga? murni tanpa harga
3. Bagaimana prosedur jika terjadi ketidaksesuaian antara makanan yang diantar dengan struk meja (revisi pesanan, komplain langsung, dsb)? komplain langsung
4. Apakah diperlukan mekanisme cetak ulang struk jika terjadi perubahan pesanan setelah struk pertama tercetak? tidak

## 11. Metrik Keberhasilan (Success Metrics)

- Penurunan tingkat kesalahan antar makanan (dibandingkan sebelum ada struk meja).
- Akurasi stok bahan baku (selisih stok fisik vs sistem mendekati 0%).
- Waktu rata-rata dari pesanan masuk hingga struk dapur tercetak.

---

_Dokumen ini adalah draft awal dan dapat berkembang seiring pembahasan lebih lanjut mengenai desain teknis dan implementasi sistem._
