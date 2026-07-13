# Product Requirements Document (PRD) v2
## Sistem Informasi Penjualan dan Persediaan Bahan Baku
### Rumah Makan Saung Babakan Cinta

**Versi:** 2.0
**Tanggal:** 13 Juli 2026
**Perubahan dari v1:** Menyesuaikan dengan status implementasi aktual (fitur yang sudah dibangun vs yang belum), sekaligus memperjelas skenario per role dan alur data.

---

## 1. Latar Belakang

Rumah Makan Saung Babakan Cinta menjalankan tiga jenis layanan — dine-in, catering, dan nasi box — namun pencatatan penjualan dan persediaan bahan baku masih dilakukan secara terpisah dan sebagian manual. Sistem yang dibangun bertujuan mengintegrasikan proses penjualan ketiga layanan tersebut dengan pengelolaan persediaan bahan baku secara otomatis dan real-time.

## 2. Tujuan Produk

1. Mengintegrasikan transaksi penjualan (dine-in, catering, nasi box) dengan pengurangan stok bahan baku otomatis melalui data BOM.
2. Mempercepat penyampaian pesanan dine-in ke dapur melalui cetak otomatis (kitchen printer).
3. Mendigitalkan pemesanan dan konfirmasi catering/nasi box, termasuk notifikasi otomatis untuk tenggat konfirmasi dan pelunasan.
4. Mendukung pembayaran non-tunai (QRIS) terintegrasi payment gateway (sandbox).
5. Memberi visibilitas stok bahan baku real-time untuk mendukung keputusan pemesanan ke supplier.

## 3. Ruang Lingkup

### Termasuk
- Pemesanan dine-in, catering, nasi box.
- Pembayaran: cash, transfer bank (manual), QRIS (payment gateway sandbox).
- Persediaan bahan baku terintegrasi dengan BOM.
- Cetak otomatis pesanan ke dapur.
- Notifikasi otomatis (in-app).
- Data master (menu, paket, bahan baku, supplier, pengguna & role).
- Hak akses berbasis role.

### Tidak termasuk
- Reservasi meja.
- Kitchen Display System (KDS) — dapur pakai struk cetak.
- Payment gateway untuk transfer bank (tetap manual).
- Fitur "Takeaway" — **dihapus dari cakupan**, karena tidak ada di rumusan masalah/tujuan penelitian. Jenis layanan yang sah hanya: Dine-in, Catering, Nasi Box.

## 4. Aktor dan Peran

| Aktor | Login? | Modul/Akses | Tanggung Jawab |
|---|---|---|---|
| Pemilik | Ya | Penuh (admin) | Kelola master data, pantau laporan, konfirmasi pesanan catering/nasi box, verifikasi transfer bank |
| Manajer | Ya | Modul persediaan | Pantau stok, pesan ke supplier, lihat laporan penggunaan bahan baku |
| Kasir | Ya | Modul pembayaran | Proses pembayaran dine-in, verifikasi transfer bank |
| Pelayan | Ya | Modul input pesanan dine-in | Input pesanan konsumen di meja |
| Konsumen | Tidak (guest) | Modul pemesanan publik | Lihat menu, pesan catering/nasi box, bayar, upload bukti transfer |
| Tim Dapur | Tidak | — (via struk cetak) | Proses pesanan dari cetakan otomatis / info pesanan terkonfirmasi |

## 5. Status Implementasi Saat Ini

### ✅ Sudah dibangun dan berjalan
| Modul | Fitur |
|---|---|
| Inventori | Master data gudang (supplier, kategori, satuan), data bahan baku + stok minimum, pengadaan/restock, mutasi stok (log), notifikasi stok menipis |
| F&B (Menu) | Kategori menu, daftar menu (nama, harga, foto, status), BOM/resep per menu |
| POS Kasir (dine-in) | UI kasir interaktif, checkout, hitung kembalian, potong stok otomatis dari resep, cetak struk pelanggan |
| UI/UX | Desain konsisten, komponen reusable |

### ❌ Belum dibangun — perlu dikejar
| Modul | Fitur | Referensi kebutuhan |
|---|---|---|
| Catering | Form pemesanan publik, DP 50%, jadwal H-14, konfirmasi Pemilik H-3, potong stok saat konfirmasi | 3.4.7.b |
| Nasi Box | Form pemesanan publik, DP 25%, jadwal H-2, konfirmasi Pemilik H-3, potong stok saat konfirmasi | 3.4.7.c |
| Role & Akses | Login terpisah + hak akses berbeda untuk Pemilik/Manajer/Kasir/Pelayan | 3.4.2 |
| Kitchen Printer | Auto-print tiket dapur (bukan struk pelanggan) saat pesanan dine-in diinput | 3.4.3 |
| Payment Gateway | Integrasi QRIS dinamis + webhook (sandbox: Midtrans/Xendit) | 3.4.4 |
| Verifikasi Transfer Bank | Upload bukti + approval manual kasir/pemilik | 3.4.4 |
| Notifikasi | In-app reminder konfirmasi (Pemilik) & pelunasan (Konsumen) | 3.4.5 |
| Laporan | Laporan penjualan per layanan, laporan penggunaan bahan baku | 3.4.6 |

### ⚠️ Perlu dikoreksi
- Field jenis menu "Dine-in/**Takeaway**" harus diubah/dihapus. Takeaway bukan bagian dari cakupan penelitian; jenis layanan yang valid adalah Dine-in, Catering, Nasi Box (bukan atribut menu, melainkan jenis transaksi).

## 6. Kebutuhan Fungsional (lengkap, termasuk yang belum dibangun)

### 6.1 Keterkaitan Penjualan–Persediaan (BOM) — ✅ sebagian (dine-in), ❌ catering/nasi box
- Dine-in: potong stok otomatis saat transaksi kasir. **Sudah berjalan.**
- Catering/nasi box: potong stok otomatis saat pesanan **dikonfirmasi Pemilik**, bukan saat konsumen memesan. **Belum ada.**

### 6.2 Hak Akses (Role) — ❌ belum ada
- Autentikasi terpisah per role, dengan modul yang dibatasi sesuai tabel Bagian 4.

### 6.3 Cetak Otomatis ke Dapur — ❌ belum ada
- Saat Pelayan input pesanan dine-in → sistem kirim data ke printer thermal dapur.
- Struk dapur ≠ struk kasir: isi hanya daftar menu, jumlah, catatan khusus (tanpa harga/total).

### 6.4 Pembayaran
- Cash: ✅ sudah ada (di POS kasir).
- Transfer bank: ❌ belum ada alur upload bukti + verifikasi.
- QRIS: ❌ belum terintegrasi payment gateway.

### 6.5 Pemesanan Catering & Nasi Box — ❌ belum ada
- Form publik (guest, tanpa akun): detail acara, tanggal, jumlah porsi/paket.
- Validasi jadwal: catering ≥ H-14, nasi box ≤ H-2 sebelum hari acara sebagai batas pemesanan.
- Pembayaran DP: 50% (catering) / 25% (nasi box).
- Konfirmasi akhir oleh Pemilik ≤ H-3 sebelum acara.
- Potong stok BOM saat konfirmasi.
- Pelunasan ≤ H-3 sebelum acara.

### 6.6 Notifikasi Otomatis — ❌ belum ada
- Ke Pemilik: reminder pesanan yang perlu dikonfirmasi.
- Ke Konsumen: reminder tenggat pelunasan.

### 6.7 Data Master — ✅ sebagian ada (bahan baku, supplier, menu); ❌ belum ada (paket catering/nasi box, pengguna & role)

### 6.8 Laporan — ❌ belum ada (kecuali notifikasi stok menipis)

## 7. Aturan Bisnis

| # | Aturan | Status |
|---|---|---|
| BR-1 | DP catering 50%, nasi box 25% dari total pemesanan | Belum diimplementasikan |
| BR-2 | Pelunasan ≤ H-3 sebelum acara | Belum diimplementasikan |
| BR-3 | Pemesanan catering ≥ H-14 sebelum acara | Belum diimplementasikan |
| BR-4 | Pemesanan nasi box ≤ H-2 sebelum acara | Belum diimplementasikan |
| BR-5 | Konfirmasi akhir Pemilik ≤ H-3 sebelum acara | Belum diimplementasikan |
| BR-6 | Pembatalan catering → potongan 25% dari DP | Belum diimplementasikan |
| BR-7 | Kebijakan pembatalan nasi box | **Belum ditentukan** — perlu keputusan |
| BR-8 | Potong stok catering/nasi box saat konfirmasi, bukan saat pesan | Belum diimplementasikan |
| BR-9 | Potong stok dine-in saat transaksi tercatat | ✅ Sudah berjalan |

## 8. Kebutuhan Non-Fungsional

- **Performa:** potong stok & cetak struk dapur real-time (< 5 detik).
- **Keandalan:** mekanisme retry/notifikasi jika printer dapur gagal cetak.
- **Keamanan:** kredensial payment gateway sandbox disimpan aman (env var, bukan hardcode); autentikasi role-based.
- **Hardware:** printer thermal di dapur (tambahkan ke kebutuhan non-fungsional BAB III jika belum ada).

## 9. Integrasi Payment Gateway (QRIS)

- Provider sandbox: perlu ditentukan (Midtrans/Xendit).
- Alur: pilih QRIS → generate QR dinamis dari gateway → konsumen bayar → gateway kirim webhook → sistem update status otomatis.
- Transfer bank tetap manual (di luar gateway).

## 10. Isu Terbuka

1. Kebijakan pembatalan nasi box (BR-7) belum ditentukan.
2. Pemilihan provider payment gateway sandbox belum final.
3. Field "Takeaway" pada menu perlu dihapus/diubah agar sesuai cakupan penelitian.
4. Perlu dipastikan apakah laporan penjualan per layanan akan dibangun sebagai dashboard atau cukup tabel/export.

---

*PRD ini menjadi acuan untuk breakdown pengerjaan pada dokumen terpisah.*
