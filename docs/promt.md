Tolong revisi dan standarkan seluruh dokumen PDF pada sistem RM Saung Babakan Cinta agar memiliki tampilan yang konsisten, formal, rapi, dan seragam. Fokus hanya pada hasil PDF/cetak, bukan tampilan halaman web.

Dokumen yang harus menggunakan standar yang sama:

* Surat Pesanan Pembelian (*Purchase Order / PO*)
* Laporan Penjualan
* Laporan Persediaan Bahan Baku
* Laporan Pengadaan
* *Invoice / Bukti Pemesanan* Nasi Box
* *Invoice / Bukti Pemesanan* Katering

## 1. Konsep Desain Umum

Gunakan satu template global untuk seluruh dokumen PDF agar semuanya terlihat berasal dari sistem dan instansi yang sama.

Ketentuan desain:

* Formal
* Minimalis
* Bersih
* Monokrom
* Jangan menggunakan warna-warna mencolok
* Gunakan **hitam, putih, dan abu-abu seperlunya**
* Jangan gunakan elemen dekoratif berlebihan
* Jangan gunakan kartu, gradien, *badge* berwarna, atau gaya seperti *dashboard*

Seluruh dokumen harus terlihat seperti dokumen administrasi resmi.

---

## 2. Ukuran dan Tata Letak

Gunakan standar berikut:

* Ukuran kertas: **A4**
* Margin dokumen: konsisten di semua PDF
* Gunakan orientasi **portrait** secara default
* Gunakan **landscape** hanya jika tabel terlalu lebar dan memang diperlukan
* Jarak antar elemen harus rapi dan konsisten

---

## 3. Header Dokumen

Seluruh PDF harus memiliki *header* yang sama.

Struktur *header*:

* Logo RM Saung Babakan Cinta di bagian kiri
* Logo menggunakan **format SVG** jika tersedia agar hasil cetak tetap tajam
* Di sebelah kanan logo tampilkan identitas usaha berikut:

**RM SAUNG BABAKAN CINTA**

**Alamat**
Jl. Ciloa No.km 6, Pasirhalang, Kec. Cisarua, Kabupaten Bandung Barat, Jawa Barat 40551

**WhatsApp**
+62 813-9461-6635

Setelah itu tambahkan garis horizontal sebagai pembatas.

Gunakan tampilan *header* yang sama pada semua dokumen. Jangan membuat bentuk *header* berbeda-beda.

---

## 4. Gaya Visual Header

Ketentuan visual *header*:

* Semua teks berwarna hitam
* Garis pembatas berwarna hitam atau abu gelap
* Tidak ada warna lain
* Nama usaha dibuat paling menonjol
* Alamat dan WhatsApp ditampilkan lebih kecil daripada nama usaha
* Tata letak rapi dan seimbang

---

## 5. Judul Dokumen

Setelah *header*, tampilkan judul dokumen dengan format yang sama di semua PDF.

Contoh judul:

* SURAT PESANAN PEMBELIAN
* LAPORAN PENJUALAN
* LAPORAN PERSEDIAAN BAHAN BAKU
* LAPORAN PENGADAAN BAHAN BAKU
* INVOICE / BUKTI PEMESANAN

Ketentuan:

* Huruf kapital
* Tebal
* Posisi tengah
* Warna hitam
* Konsisten ukuran dan jaraknya

Jika ada subjudul, letakkan tepat di bawah judul.

Contoh:

* Periode 01 Agustus 2026 s.d. 31 Agustus 2026
* No. PO: PO-20260820-001
* Kode Pesanan: PSN-20260820-001

---

## 6. Tipografi

Gunakan font yang aman untuk PDF dan konsisten, misalnya:

* DejaVu Sans
* Arial
* Helvetica
* Font standar DomPDF yang mudah dibaca

Aturan tipografi:

* Judul lebih besar
* Isi tabel dan informasi dokumen tetap terbaca jelas
* Gunakan ukuran font yang stabil
* Seluruh teks menggunakan warna hitam atau abu-abu gelap
* Jangan pakai warna lain

---

## 7. Format Informasi Dokumen

Untuk informasi seperti nomor dokumen, tanggal, supplier, pelanggan, periode, dan sebagainya, gunakan format yang seragam.

Contoh untuk PO:

| Informasi         | Data             |
| ----------------- | ---------------- |
| Nomor PO          | PO-20260820-001  |
| Tanggal PO        | 20 Agustus 2026  |
| Tanggal Kebutuhan | 21 Agustus 2026  |
| Supplier          | CV Sumber Makmur |
| WhatsApp Supplier | 081234567890     |

Contoh untuk *invoice*:

| Informasi          | Data                    |
| ------------------ | ----------------------- |
| Kode Pesanan       | PSN-20260820-001        |
| Tanggal Pesanan    | 20 Agustus 2026         |
| Nama Pelanggan     | Rina Aulia              |
| No. Telepon        | 081234567890            |
| Jenis Pesanan      | Nasi Box                |
| Tanggal Pengiriman | 24 Agustus 2026         |
| Alamat Pengiriman  | Jl. Raya Lembang No. 10 |

Jangan gunakan desain kartu atau blok berwarna.

---

## 8. Tabel Global

Gunakan desain tabel yang sama pada semua PDF.

Ketentuan tabel:

* Garis tabel tipis dan rapi
* Header tabel tebal
* Header tabel boleh diberi latar abu muda jika diperlukan, tetapi tetap monokrom
* Teks tabel berwarna hitam
* Nomor rata tengah
* Nama bahan/menu rata kiri
* Kuantitas rata kanan atau tengah secara konsisten
* Nominal uang rata kanan
* Jangan menampilkan tombol, ikon UI, *badge*, atau elemen halaman web

Contoh gaya tabel:

| No | Nama Item | Jumlah | Satuan | Harga | Total |
| -: | --------- | -----: | ------ | ----: | ----: |

---

## 9. Format Rupiah

Semua nominal harus konsisten menggunakan format Rupiah Indonesia.

Contoh:

* Rp15.000
* Rp250.000
* Rp1.750.000

Jangan gunakan format selain itu.

---

## 10. Format Tanggal

Gunakan format tanggal yang seragam dan formal.

Contoh:

* 20 Agustus 2026

Jangan campurkan beberapa format dalam satu sistem PDF.

---

## 11. Format Satuan

Gunakan format satuan yang konsisten mengikuti sistem bahan baku:

* gram
* kg
* ml
* liter
* pcs

Aturan tampilan:

* Jika berat besar, tampilkan dalam kg
* Jika cairan besar, tampilkan dalam liter
* *pcs* tetap *pcs*

Contoh:

* 500 gram
* 25 kg
* 750 ml
* 20 liter
* 10 pcs

Jangan tampilkan angka yang sulit dibaca seperti 50000 gram jika pada dokumen bisa ditampilkan sebagai 50 kg.

---

## 12. Footer Global

Semua dokumen harus memiliki *footer* yang sama.

Isi *footer*:

* Dicetak dari Sistem Informasi RM Saung Babakan Cinta
* Tanggal cetak
* Nomor halaman

Contoh:

Dicetak dari Sistem Informasi RM Saung Babakan Cinta
Tanggal Cetak: 20 Agustus 2026, 16:00 WIB
Halaman 1 dari 2

Ketentuan:

* Footer muncul di setiap halaman
* Warna hitam / abu-abu
* Posisi konsisten
* Rapi dan tidak mengganggu isi dokumen

---

## 13. Tanpa Tanda Tangan

Jangan tambahkan area tanda tangan pada dokumen PDF.

Artinya:

* Tidak perlu kolom tanda tangan supplier
* Tidak perlu kolom tanda tangan pihak rumah makan
* Tidak perlu blok “mengetahui”, “disetujui”, atau sejenisnya
* Tidak perlu ruang kosong tanda tangan di bagian bawah

Semua dokumen cukup berupa dokumen cetak resmi tanpa tanda tangan.

---

## 14. Ketentuan Khusus Purchase Order

Judul:

SURAT PESANAN PEMBELIAN
(PURCHASE ORDER)

Tampilkan informasi:

* No. PO
* Tanggal PO
* Tanggal Kebutuhan
* Supplier
* No. Telepon / WhatsApp Supplier
* Catatan PO jika ada

Gunakan tabel item:

| No | Nama Bahan Baku | Jumlah | Satuan | Harga Satuan | Total |
| -: | --------------- | -----: | ------ | -----------: | ----: |

Di bagian bawah tampilkan:

* Total Pembelian

Jika ada catatan tambahan, tampilkan sebagai teks biasa, bukan kotak berwarna.

Jangan gunakan tanda tangan.

---

## 15. Ketentuan Khusus Laporan Penjualan

Judul:

LAPORAN PENJUALAN

Tampilkan informasi:

* Periode laporan
* Filter jenis pesanan jika ada
* Tanggal cetak

Tampilkan ringkasan KPI secara formal dan sederhana, misalnya dalam tabel:

| Keterangan          |        Nilai |
| ------------------- | -----------: |
| Total Pendapatan    | Rp25.750.000 |
| Jumlah Transaksi    |          145 |
| Rata-rata Transaksi |    Rp177.586 |

Kemudian tampilkan tabel transaksi:

| No | Kode Pesanan | Tanggal | Jenis | Pelanggan | Total | Status |
| -: | ------------ | ------- | ----- | --------- | ----: | ------ |

Semua elemen tetap hitam / abu-abu.

---

## 16. Ketentuan Khusus Laporan Persediaan

Judul:

LAPORAN PERSEDIAAN BAHAN BAKU

Tampilkan tabel:

| No | Kode | Nama Bahan | Satuan | Stok Saat Ini | Stok Minimum | Status |
| -: | ---- | ---------- | ------ | ------------: | -----------: | ------ |

Status cukup ditampilkan sebagai teks:

* Aman
* Menipis
* Habis

Jangan gunakan *badge* warna hijau, kuning, atau merah.

---

## 17. Ketentuan Khusus Laporan Pengadaan

Judul:

LAPORAN PENGADAAN BAHAN BAKU

Tampilkan tabel:

| No | No. PO | Tanggal | Supplier | Jumlah Item | Total Pembelian | Status |
| -: | ------ | ------- | -------- | ----------: | --------------: | ------ |

Jika perlu, tampilkan ringkasan di bawah tabel:

* Total Transaksi Pengadaan
* Total Nilai Pengadaan

Semua tetap monokrom.

---

## 18. Ketentuan Khusus Invoice / Bukti Pemesanan

Gunakan satu desain *invoice* yang sama untuk Nasi Box dan Katering.

Judul:

INVOICE / BUKTI PEMESANAN

Informasi dokumen:

* Kode Pesanan
* Tanggal Pesanan
* Nama Pelanggan
* No. Telepon
* Jenis Pesanan
* Tanggal Pengiriman / Acara
* Alamat Pengiriman

Tabel rincian:

| No | Menu / Paket | Jumlah | Harga | Subtotal |
| -: | ------------ | -----: | ----: | -------: |

Bagian total:

* Subtotal
* Biaya Pengiriman jika ada
* Total Tagihan

Bagian pembayaran:

* Status Pembayaran
* DP Dibayar
* Sisa Pembayaran

Semua dalam bentuk teks dan tabel sederhana. Jangan gunakan warna.

---

## 19. Page Break

Pastikan dokumen panjang tetap rapi ketika pindah halaman.

Aturan:

* Header tabel harus muncul lagi di halaman berikutnya
* Satu baris data jangan terpotong
* Footer tetap muncul
* Judul jangan menggantung sendiri di bagian bawah halaman
* Tata letak tetap stabil ketika dicetak

---

## 20. Struktur Template Laravel

Jika menggunakan Laravel dan DomPDF, buat satu sistem template PDF global.

Contoh struktur:

resources/views/pdf/

* layout.blade.php
* components/

  * header.blade.php
  * footer.blade.php
  * document-info.blade.php
  * table-style.blade.php
* po.blade.php
* laporan-penjualan.blade.php
* laporan-persediaan.blade.php
* laporan-pengadaan.blade.php
* invoice.blade.php

Seluruh file PDF harus menggunakan layout yang sama agar konsisten.

---

## 21. Target Hasil Akhir

Saya ingin semua PDF mempunyai pola seperti ini:

1. Logo SVG di kiri
2. Identitas RM Saung Babakan Cinta di kanan:

   * RM SAUNG BABAKAN CINTA
   * Alamat: Jl. Ciloa No.km 6, Pasirhalang, Kec. Cisarua, Kabupaten Bandung Barat, Jawa Barat 40551
   * WhatsApp: +62 813-9461-6635
3. Garis pemisah
4. Judul dokumen di tengah
5. Informasi dokumen
6. Tabel isi
7. Total / ringkasan bila ada
8. Footer standar
9. Tanpa tanda tangan
10. Semua visual hitam, putih, dan abu-abu saja

Pastikan hasil akhirnya konsisten dari sisi:

* header
* logo
* alamat
* WhatsApp
* font
* ukuran font
* margin
* tabel
* format Rupiah
* format tanggal
* format satuan
* footer
* nomor halaman
* gaya visual monokrom
* tanpa tanda tangan

Jangan ubah isi data utama atau alur bisnis. Fokus pada konsistensi tampilan seluruh PDF.
