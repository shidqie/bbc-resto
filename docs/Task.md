# Breakdown Pengerjaan (Development Checklist)

## Berdasarkan PRD v2 — Sistem Penjualan & Persediaan Saung Babakan Cinta

**Tanggal:** 13 Juli 2026
**Tujuan dokumen:** Memecah kebutuhan yang belum diimplementasikan menjadi task konkret, diurutkan berdasarkan prioritas dan dependensi, agar pengerjaan menuju sidang lebih terarah.

---

## Cara membaca dokumen ini

- 🔴 = wajib ada sebelum sidang (inti judul penelitian)
- 🟡 = penting, kuat memengaruhi nilai kelengkapan
- 🟢 = nice-to-have / bisa disederhanakan bila waktu terbatas
- Setiap modul punya urutan task dan **dependensi** (apa yang harus selesai duluan)

---

## FASE 1 — Fondasi Role & Akses 🔴

_Harus dikerjakan lebih dulu karena modul catering/nasi box butuh login Pemilik untuk fitur konfirmasi._

- [ ] Buat tabel `users` dengan field role (Pemilik, Manajer, Kasir, Pelayan)
- [ ] Buat sistem login/autentikasi (session/token)
- [ ] Middleware pembatasan akses per role:
    - Pemilik → semua modul
    - Manajer → modul persediaan + laporan
    - Kasir → modul pembayaran/POS
    - Pelayan → modul input pesanan dine-in saja
- [ ] Uji: login sebagai masing-masing role, pastikan menu/akses sesuai tabel di PRD Bagian 4

**Dependensi:** tidak ada (bisa dikerjakan dari awal)
**Estimasi dampak ke nilai:** tinggi — penguji hampir pasti akan cek ini karena eksplisit ada di BAB III (Tabel 3.10)

---

## FASE 2 — Modul Pemesanan Catering & Nasi Box 🔴

_Ini modul paling besar yang belum ada, dan merupakan inti klaim "keterpaduan" di judul._

### 2.1 Data Master Paket

- [ ] Buat tabel paket catering (nama, harga, BOM/komposisi)
- [ ] Buat tabel paket nasi box (nama, harga, BOM/komposisi)
- [ ] CRUD paket oleh Pemilik

### 2.2 Form Pemesanan Publik (Konsumen, guest — tanpa login)

- [ ] Form pemesanan catering: pilih paket, jumlah porsi, tanggal acara, detail acara
    - [ ] Validasi: tanggal acara ≥ H-14 dari hari pemesanan
- [ ] Form pemesanan nasi box: pilih paket, jumlah, tanggal acara
    - [ ] Validasi: tanggal acara ≤ H-2 dari hari pemesanan (tenggat maksimal, bukan minimal)
- [ ] Simpan pesanan dengan status awal: `menunggu_konfirmasi`

### 2.3 Pembayaran DP

- [ ] Hitung otomatis DP: 50% (catering) / 25% (nasi box) dari total
- [ ] Konsumen upload bukti bayar (jika transfer) — lihat Fase 4
- [ ] Update status pesanan setelah DP tercatat: `menunggu_konfirmasi` → tetap, sampai Pemilik konfirmasi

### 2.4 Konfirmasi oleh Pemilik

- [ ] Halaman daftar pesanan catering/nasi box masuk (status `menunggu_konfirmasi`)
- [ ] Aksi "Konfirmasi" oleh Pemilik
- [ ] **Saat konfirmasi**: sistem otomatis potong stok bahan baku sesuai BOM paket (reuse logic dari POS dine-in, Bagian 6.1 PRD)
- [ ] Validasi: tidak bisa konfirmasi setelah lewat H-3 (atau beri warning)
- [ ] Update status pesanan → `terkonfirmasi`

### 2.5 Pelunasan

- [ ] Konsumen bayar sisa (setelah DP) sebelum H-3
- [ ] Update status → `lunas`

### 2.6 Pembatalan

- [ ] Fitur batalkan pesanan (oleh Pemilik/Konsumen, sesuai flow yang disepakati)
- [ ] Catering: potongan 25% dari DP/pembayaran yang sudah masuk
- [ ] Nasi box: **⚠️ kebijakan belum ditentukan** — putuskan dulu sebelum implementasi (lihat Isu Terbuka #1 di PRD)

### 2.7 Info ke Tim Dapur

- [ ] Setelah status `terkonfirmasi`, tampilkan/kirim info pesanan (menu/paket, jumlah, tanggal) ke tampilan yang bisa diakses Tim Dapur (misal: layar khusus tanpa login, atau tercetak)

**Dependensi:** Fase 1 (role Pemilik), data BOM paket (2.1)
**Catatan:** modul ini bisa dipecah lagi jadi 2 sub-sprint: catering dulu, lalu nasi box (karena alurnya mirip, catering jadi template).

---

## FASE 3 — Kitchen Printer (Auto-print ke Dapur) 🔴

- [ ] Riset/tentukan driver printer thermal yang dipakai (ESC/POS, network/USB)
- [ ] Buat service cetak terpisah dari cetak struk pelanggan
- [ ] Trigger otomatis: begitu Pelayan submit pesanan dine-in → kirim job cetak ke printer dapur
- [ ] Format struk dapur: nama menu, jumlah, catatan khusus (**tanpa harga/total**, beda dari struk kasir)
- [ ] Uji end-to-end: input pesanan → cetak keluar di printer dapur dalam < 5 detik
- [ ] (Opsional) Mekanisme fallback jika printer offline/gagal — misal notifikasi ke Pelayan

**Dependensi:** modul dine-in (sudah ada), tidak bergantung ke Fase 1/2 — **bisa dikerjakan paralel**

---

## FASE 4 — Pembayaran: Transfer Bank & QRIS 🔴

### 4.1 Transfer Bank (manual)

- [ ] Form upload bukti pembayaran (konsumen/kasir)
- [ ] Halaman approval oleh Kasir/Pemilik (lihat bukti → approve/reject)
- [ ] Update status transaksi setelah approve

### 4.2 QRIS via Payment Gateway

- [ ] Pilih provider Midtrans
- [ ] Setup akun sandbox + API key (simpan di environment variable, jangan hardcode)
- [ ] Endpoint generate QR dinamis saat konsumen pilih QRIS
- [ ] Endpoint webhook untuk menerima notifikasi status pembayaran dari gateway
- [ ] Update status transaksi otomatis begitu webhook diterima (tanpa approval manual)
- [ ] Uji dengan skenario (biasanya provider punya simulator pembayaran)

**Dependensi:** modul transaksi dine-in (sudah ada) & catering/nasi box (Fase 2) sama-sama butuh ini

---

## FASE 5 — Notifikasi Otomatis 🟡

- [ ] Notifikasi in-app ke Pemilik: daftar pesanan yang mendekati/lewat tenggat konfirmasi (H-3)
- [ ] Notifikasi in-app ke Konsumen: reminder tenggat pelunasan
- [ ] (Opsional) Tentukan mekanisme: polling sederhana vs scheduler/cron job

**Dependensi:** Fase 2 (butuh data tanggal acara & status pesanan)

---

## FASE 6 — Laporan 🟡

- [ ] Laporan penjualan per layanan (dine-in / catering / nasi box), filter periode
- [ ] Laporan penggunaan bahan baku (agregat dari mutasi stok)
- [ ] Akses: Pemilik (semua laporan), Manajer (laporan persediaan)

**Dependensi:** Fase 1 (role), data transaksi dari Fase 2 & modul dine-in yang sudah ada

---

## FASE 7 — Perbaikan Kecil 🟢

- [ ] Hapus/ubah field "Takeaway" pada menu (tidak sesuai cakupan penelitian)
- [ ] Review konsistensi istilah jenis layanan di seluruh sistem: Dine-in, Catering, Nasi Box saja

**Dependensi:** tidak ada, bisa dikerjakan kapan saja (quick win)

---

## Rekomendasi Urutan Kerja (jika waktu terbatas)

```
1. Fase 1 (Role & Akses)         → fondasi wajib
2. Fase 2 (Catering & Nasi Box)  → modul terbesar, mulai catering dulu
3. Fase 3 (Kitchen Printer)      → bisa paralel dengan Fase 2 (tim/waktu berbeda)
4. Fase 4 (Payment: transfer)    → pymnt. gateway
5. Fase 5 (Notifikasi)           → bisa disederhanakan jadi badge/list, tidak perlu push notif
6. Fase 6 (Laporan)              → bisa berupa tabel sederhana dulu, chart menyusul
7. Fase 7 (Perbaikan kecil)      → kapan saja, cepat
```
