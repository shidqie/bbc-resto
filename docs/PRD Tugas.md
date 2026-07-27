# Product Requirements Document (PRD)
## Fitur Pembayaran, Riwayat Transaksi, dan Manajemen Shift Kasir — BBC Resto POS

| | |
|---|---|
| **Versi Dokumen** | 1.0 |
| **Tanggal** | 25 Juli 2026 |
| **Status** | Draft |
| **Pemilik Produk** | — |
| **Modul Terkait** | Payment, Transaction History, Cashier Shift Management |

---

## 1. Latar Belakang & Tujuan

Sistem kasir (POS) BBC Resto saat ini memiliki alur pembayaran dasar (Tunai, Nontunai/Digital, Promo) namun belum memiliki cakupan penuh untuk:
1. Ragam metode pembayaran nontunai yang lengkap (QRIS, kartu, e-wallet, Virtual Account)
2. Riwayat transaksi & laporan kasir yang bisa diaudit per shift/kasir/metode bayar
3. Manajemen sesi kasir (buka-tutup shift) untuk kontrol kas fisik

Dokumen ini mendefinisikan requirement untuk ketiga area tersebut agar sistem POS memiliki fondasi pembayaran yang lengkap, auditable, dan siap untuk skala multi-outlet.

### 1.1 Tujuan Bisnis
- Mengurangi selisih kas (cash discrepancy) di akhir shift
- Mempercepat proses pembayaran di kasir (rata-rata waktu transaksi)
- Menyediakan data transaksi yang akurat untuk rekonsiliasi keuangan & pajak
- Mendukung multi metode pembayaran tanpa menambah kompleksitas operasional kasir

### 1.2 Tujuan Produk
- Kasir dapat memproses pembayaran dengan berbagai metode dalam satu alur yang konsisten
- Owner/Admin dapat melihat riwayat transaksi & laporan kasir kapan saja
- Setiap kasir wajib membuka & menutup shift dengan modal dan setoran yang tercatat sistem

---

## 2. Ruang Lingkup

### 2.1 Termasuk dalam Scope
- Metode pembayaran: Tunai, QRIS, Kartu Debit/Kredit, E-wallet, Virtual Account
- Riwayat transaksi dengan filter & pencarian
- Laporan kasir per shift
- Manajemen sesi shift kasir (buka/tutup shift)

### 2.2 Tidak Termasuk dalam Scope (Out of Scope)
- Program loyalitas/poin pelanggan
- Cicilan/installment kartu kredit
- Kredit/piutang pelanggan corporate
- Self-order/self-payment dari meja pelanggan
- Integrasi akuntansi pihak ketiga (Accurate, Jurnal, dll) — akan dibahas di PRD terpisah

---

## 3. Persona Pengguna

| Persona | Kebutuhan Utama |
|---|---|
| **Kasir** | Proses bayar cepat, tidak ribet, minim kesalahan input |
| **Supervisor/Shift Lead** | Approve void/refund, pantau transaksi real-time per shift |
| **Owner/Admin** | Lihat laporan penjualan, rekonsiliasi kas, audit histori transaksi |
| **Pelanggan** | Metode bayar fleksibel, struk akurat, proses cepat |

---

## 4. Requirement Fungsional

### 4.1 Modul Metode Pembayaran

#### 4.1.1 Tunai (Cash)

| ID | Requirement | Prioritas |
|---|---|---|
| PAY-001 | Sistem menyediakan tombol nominal cepat (5.000 / 10.000 / 20.000 / 50.000 / 100.000) dan tombol "Uang Pas" | Must Have |
| PAY-002 | Sistem menghitung kembalian otomatis: `Kembalian = Uang Diterima − Total Tagihan` | Must Have |
| PAY-003 | Sistem menolak proses bayar jika uang diterima < total tagihan, dengan pesan error yang jelas | Must Have |
| PAY-004 | Kembalian ditampilkan di layar pembayaran (bukan hanya di panel ringkasan) sebelum transaksi difinalisasi | Must Have |
| PAY-005 | Sistem memicu buka cash drawer otomatis (jika terhubung) setelah transaksi tunai selesai | Should Have |

**Acceptance Criteria:**
- Kasir input nominal 60.000 untuk tagihan 54.000 → sistem menampilkan kembalian Rp 6.000 sebelum tombol "Selesai" bisa ditekan
- Kasir input nominal 50.000 untuk tagihan 54.000 → sistem menampilkan pesan "Uang tidak cukup, kurang Rp 4.000" dan tombol proses nonaktif

#### 4.1.2 Nontunai/Digital — QRIS

| ID | Requirement | Prioritas |
|---|---|---|
| PAY-010 | Sistem generate kode QRIS dinamis melalui payment gateway (Midtrans) sesuai nominal tagihan | Must Have |
| PAY-011 | Kode QRIS memiliki batas waktu (expiry), ditampilkan sebagai countdown timer di layar | Must Have |
| PAY-012 | Status pembayaran terupdate otomatis (real-time/polling) tanpa refresh manual | Must Have |
| PAY-013 | Jika QRIS expired sebelum dibayar, sistem menampilkan opsi "Buat Ulang QR" | Must Have |
| PAY-014 | Kasir dapat membatalkan pembayaran QRIS yang belum settlement | Should Have |
| PAY-015 | Sistem mencatat setiap request QRIS ke tabel transaksi pembayaran (termasuk yang batal/expired) untuk audit | Must Have |

**Acceptance Criteria:**
- Kasir pilih QRIS untuk tagihan 54.000 → QR muncul dalam < 3 detik dengan countdown 5 menit
- Pelanggan scan & bayar → status di layar kasir berubah "Lunas" dalam maksimal 5 detik setelah settlement dikonfirmasi payment gateway

#### 4.1.3 Nontunai/Digital — Kartu Debit/Kredit

| ID | Requirement | Prioritas |
|---|---|---|
| PAY-020 | Sistem mendukung input pembayaran kartu via EDC (Electronic Data Capture) terintegrasi atau input manual approval code | Should Have |
| PAY-021 | Sistem mencatat 4 digit terakhir kartu & jenis kartu (debit/kredit) untuk keperluan rekonsiliasi, tanpa menyimpan data kartu lengkap (PCI-DSS compliance) | Must Have |
| PAY-022 | Sistem tidak menyimpan CVV atau nomor kartu penuh dalam bentuk apapun | Must Have (Compliance) |

#### 4.1.4 Nontunai/Digital — E-wallet

| ID | Requirement | Prioritas |
|---|---|---|
| PAY-030 | Sistem mendukung pembayaran via e-wallet (GoPay, OVO, Dana) melalui payment gateway | Should Have |
| PAY-031 | Alur sama seperti QRIS: generate kode/link → polling status → update otomatis | Should Have |

#### 4.1.5 Transfer Bank — Virtual Account (VA)

| ID | Requirement | Prioritas |
|---|---|---|
| PAY-040 | Sistem generate nomor VA unik per transaksi melalui payment gateway | Could Have |
| PAY-041 | Nomor VA & panduan pembayaran ditampilkan jelas di layar kasir/struk sementara | Could Have |
| PAY-042 | Status pembayaran VA terupdate otomatis via webhook payment gateway | Could Have |

#### 4.1.6 Split/Pisah Bayar

| ID | Requirement | Prioritas |
|---|---|---|
| PAY-050 | Kasir dapat memisah satu tagihan menjadi beberapa metode pembayaran (contoh: sebagian cash, sebagian QRIS) | Must Have |
| PAY-051 | Sistem memvalidasi total seluruh metode pembayaran harus sama dengan total tagihan sebelum transaksi bisa difinalisasi | Must Have |
| PAY-052 | Kasir dapat memisah tagihan berdasarkan jumlah orang (split equal) atau berdasarkan item pilihan (split by item) | Should Have |

**Acceptance Criteria:**
- Tagihan 54.000 dibagi: 30.000 cash + 24.000 QRIS → sistem menerima hanya jika kedua metode selesai diproses dan totalnya pas 54.000
- Jika total split kurang dari tagihan, sistem menolak dengan pesan "Total pembayaran belum sesuai, kurang Rp X"

#### 4.1.7 Promo/Diskon

| ID | Requirement | Prioritas |
|---|---|---|
| PAY-060 | Diskon dapat diterapkan per item atau per total tagihan, dalam bentuk persen atau nominal | Must Have |
| PAY-061 | Diskon manual (bukan dari sistem promo otomatis) memerlukan approval PIN supervisor | Must Have |
| PAY-062 | Sistem mencatat siapa yang memberikan approval diskon manual untuk keperluan audit | Must Have |

---

### 4.2 Modul Riwayat Transaksi

| ID | Requirement | Prioritas |
|---|---|---|
| TRX-001 | Sistem menampilkan daftar seluruh transaksi dengan kolom minimal: No. Order, Tanggal/Waktu, Kasir, Meja, Total, Metode Bayar, Status | Must Have |
| TRX-002 | Admin/Owner dapat memfilter transaksi berdasarkan: rentang tanggal, kasir, metode pembayaran, status (lunas/void/refund) | Must Have |
| TRX-003 | Admin dapat mencari transaksi berdasarkan nomor order atau nama pelanggan | Should Have |
| TRX-004 | Setiap transaksi dapat dibuka detailnya: daftar item, harga, diskon, pajak, metode bayar, dan riwayat perubahan (jika ada void/refund) | Must Have |
| TRX-005 | Sistem menyediakan export riwayat transaksi ke format Excel/CSV untuk kebutuhan pembukuan eksternal | Should Have |
| TRX-006 | Transaksi dengan status void/refund ditandai visual berbeda (misal warna merah) dari transaksi normal | Must Have |
| TRX-007 | Log perubahan transaksi (void, refund, edit) mencatat: siapa yang melakukan, waktu, alasan | Must Have |

**Acceptance Criteria:**
- Admin filter transaksi tanggal 25 Juli 2026, kasir "Kasir BBC", metode "QRIS" → sistem menampilkan hanya transaksi yang sesuai kriteria
- Admin klik transaksi DIN-00031 → detail lengkap termasuk item, subtotal, pajak, dan metode bayar ditampilkan

---

### 4.3 Modul Laporan Kasir

| ID | Requirement | Prioritas |
|---|---|---|
| RPT-001 | Sistem menghasilkan ringkasan penjualan per shift: total transaksi, total omzet, breakdown per metode bayar | Must Have |
| RPT-002 | Laporan menampilkan perbandingan antara total sistem (expected cash) vs setoran fisik kasir (actual cash) saat tutup shift | Must Have |
| RPT-003 | Sistem menghitung otomatis selisih kas (cash discrepancy) jika ada perbedaan | Must Have |
| RPT-004 | Laporan dapat difilter per kasir, per tanggal, per rentang tanggal (harian/mingguan/bulanan) | Should Have |
| RPT-005 | Laporan kasir individual dapat dicetak/di-export sebagai bukti serah terima shift | Should Have |

**Acceptance Criteria:**
- Kasir tutup shift dengan modal awal 200.000, total transaksi cash selama shift 1.500.000 → sistem expected cash di laci = 1.700.000. Jika kasir setor fisik 1.695.000 → sistem catat selisih -5.000 dan wajib diisi catatan alasan

---

### 4.4 Modul Manajemen Shift Kasir

| ID | Requirement | Prioritas |
|---|---|---|
| SFT-001 | Kasir wajib membuka shift sebelum dapat memproses transaksi apapun | Must Have |
| SFT-002 | Saat buka shift, kasir input modal awal (starting cash) | Must Have |
| SFT-003 | Sistem mencatat waktu mulai shift dan kasir yang bertugas | Must Have |
| SFT-004 | Kasir tidak dapat membuka shift baru jika masih ada shift aktif atas namanya yang belum ditutup | Must Have |
| SFT-005 | Saat tutup shift, kasir input jumlah setoran akhir (actual cash) | Must Have |
| SFT-006 | Sistem menampilkan ringkasan shift sebelum konfirmasi tutup: total transaksi, expected cash, actual cash, selisih | Must Have |
| SFT-007 | Jika ada selisih kas, sistem mewajibkan kasir mengisi catatan/alasan sebelum shift bisa ditutup | Must Have |
| SFT-008 | Supervisor/Admin dapat melihat status shift semua kasir secara real-time (aktif/tidak aktif) | Should Have |
| SFT-009 | Riwayat shift tersimpan dan dapat diaudit kapan saja (siapa, kapan buka/tutup, selisih kas) | Must Have |
| SFT-010 | Jika kasir lupa menutup shift dan sistem terdeteksi idle lebih dari waktu tertentu (misal 24 jam), sistem mengirim notifikasi ke supervisor | Could Have |

**Acceptance Criteria:**
- Kasir A membuka shift dengan modal 200.000 pukul 08:00 → sistem catat status shift "Aktif", kasir A, modal 200.000
- Kasir A mencoba login/buka shift lagi tanpa menutup shift sebelumnya → sistem menolak dengan pesan "Anda masih memiliki shift aktif"
- Kasir A menutup shift pukul 16:00 dengan setoran 1.695.000 dari expected 1.700.000 → sistem wajibkan isi catatan alasan selisih sebelum tombol "Tutup Shift" aktif

---

## 5. Requirement Non-Fungsional

| Kategori | Requirement |
|---|---|
| **Performa** | Update status pembayaran digital (QRIS/e-wallet) maksimal 5 detik setelah settlement dikonfirmasi gateway |
| **Keamanan** | Semua approval sensitif (void, refund, diskon manual >batas tertentu) wajib PIN/otentikasi supervisor |
| **Keamanan Data** | Tidak menyimpan data kartu kredit/debit lengkap (PCI-DSS), hanya reference/masked data |
| **Auditability** | Setiap perubahan data transaksi (void, refund, edit) tercatat dengan siapa & kapan, tidak dapat dihapus dari log |
| **Reliability** | Webhook payment gateway harus idempotent — notifikasi ganda tidak boleh menyebabkan data double-update |
| **Ketersediaan** | Fitur pembayaran tunai harus tetap berfungsi walau koneksi internet terputus (payment gateway/QRIS otomatis nonaktif dengan pesan jelas) |
| **Skalabilitas** | Sistem mendukung multi-outlet dengan data shift & transaksi terpisah per outlet |

---

## 6. Alur Utama (User Flow Ringkas)

1. **Buka Shift** → Kasir login → input modal awal → shift aktif
2. **Transaksi Berjalan** → Kasir proses beberapa order dengan berbagai metode bayar
3. **Tutup Shift** → Kasir input setoran akhir → sistem tampilkan ringkasan & selisih → kasir isi catatan jika ada selisih → shift ditutup
4. **Review Admin** → Admin/Owner cek riwayat transaksi & laporan kasir kapan saja dari dashboard

---

## 7. Metrik Keberhasilan (Success Metrics)

| Metrik | Target |
|---|---|
| Rata-rata waktu proses pembayaran per transaksi | < 15 detik (tunai), < 30 detik (QRIS sampai settlement) |
| Selisih kas per shift | < 0.5% dari total omzet shift |
| Tingkat kegagalan transaksi digital (QRIS/VA) | < 2% dari total transaksi digital |
| Waktu pembuatan laporan kasir | Real-time, tanpa proses manual rekap |

---

## 8. Dependensi

- Payment gateway (Midtrans) untuk QRIS, e-wallet, VA
- Printer thermal untuk struk & laporan shift
- Sistem otentikasi kasir/supervisor (PIN atau login terpisah)

---

## 9. Risiko & Mitigasi

| Risiko | Mitigasi |
|---|---|
| Webhook payment gateway gagal terkirim (network issue) | Sistem melakukan reconciliation job berkala yang cek status ke Midtrans API sebagai fallback |
| Kasir manipulasi setoran kas tutup shift | Selisih kas wajib diisi alasan & tercatat, supervisor dapat review laporan selisih |
| Duplikasi transaksi akibat double-klik tombol bayar | Idempotency key per request pembayaran |

---

## 10. Lampiran — Open Questions

- Apakah diperlukan batas nominal maksimal untuk diskon manual tanpa approval?
- Apakah split by item perlu mendukung pembagian item yang sama untuk >1 orang (misal 1 porsi dibagi 2)?
- Apakah sistem perlu multi-currency untuk ekspansi ke luar negeri, atau cukup Rupiah saja untuk saat ini?
