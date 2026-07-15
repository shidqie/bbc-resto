# Product Requirements Document (PRD)
# Fitur Pengaturan Ongkos Kirim (Delivery Fee)
### Studi Kasus: Saung Babakan Cinta (BBC) — Nasi Box & Catering

---

## 1. Latar Belakang

Saung Babakan Cinta (BBC) berlokasi di Jl. Ciloa Km 6, Pasirhalang, Kec. Cisarua, Kabupaten Bandung Barat, saat ini belum memiliki mekanisme perhitungan ongkos kirim (ongkir) yang terstruktur untuk layanan pemesanan Nasi Box dan Catering. Perhitungan ongkir masih dilakukan secara manual/negosiasi, sehingga tidak konsisten dan sulit diintegrasikan ke dalam sistem pemesanan online.

Dokumen ini merumuskan kebutuhan fungsional dan aturan bisnis (business rules) untuk fitur perhitungan ongkir otomatis berbasis jarak dan jumlah pesanan, yang akan diimplementasikan dalam sistem/aplikasi pemesanan.

> **Catatan penelitian:** Nilai tarif, ambang batas jarak, dan ambang batas jumlah pesanan pada dokumen ini merupakan **asumsi yang diusulkan peneliti**, karena objek penelitian belum memiliki kebijakan ongkir resmi yang terdokumentasi. Asumsi disusun berdasarkan pola umum praktik bisnis kuliner sejenis di wilayah Bandung.

---

## 2. Tujuan Fitur

1. Menyediakan perhitungan ongkir otomatis berdasarkan jarak tempuh dari restoran (titik A) ke lokasi pelanggan (titik B).
2. Memberikan insentif berupa gratis ongkir bertingkat untuk mendorong pelanggan memesan dalam jumlah lebih besar.
3. Membedakan skema ongkir antara layanan **Nasi Box** dan **Catering**, karena karakteristik logistik keduanya berbeda.
4. Menetapkan syarat minimum pemesanan agar layanan delivery efisien secara operasional.

---

## 3. Ruang Lingkup (Scope)

**Termasuk dalam scope:**
- Perhitungan jarak dari titik A (restoran) ke titik B (alamat pelanggan) menggunakan koordinat/API peta.
- Perhitungan ongkir otomatis untuk kategori Nasi Box.
- Perhitungan ongkir flat per zona untuk kategori Catering.
- Validasi minimum order sebelum opsi delivery dapat dipilih.
- Penerapan tier gratis ongkir.

**Di luar scope:**
- Perhitungan estimasi waktu pengiriman (ETA).
- Manajemen armada/kurir.
- Pembayaran ongkir terpisah dari total transaksi (ongkir digabung ke total tagihan).

---

## 4. Definisi & Parameter Dasar

| Parameter | Nilai | Keterangan |
|---|---|---|
| Titik A (asal) | Jl. Ciloa Km 6, Pasirhalang, Cisarua, Kab. Bandung Barat (Saung Babakan Cinta) | Koordinat default sistem |
| Tarif dasar per km | Rp 3.000 / km | Berlaku untuk kategori Nasi Box |
| Jarak maksimal layanan | 30 km dari titik A | Di luar radius ini, delivery tidak tersedia |
| Ongkir minimum | Rp 10.000 (flat untuk jarak < 3 km) | Mencegah ongkir mendekati Rp0 untuk jarak sangat dekat |
| Metode hitung jarak | Google Maps Distance Matrix API (jarak tempuh jalan, bukan garis lurus) | Agar sesuai kondisi rute nyata |

---

## 5. Aturan Bisnis — Kategori Nasi Box

### 5.1 Minimum Pemesanan untuk Delivery

| Kondisi | Status |
|---|---|
| Jumlah box < 25 | Delivery **tidak tersedia** — pelanggan diarahkan ke opsi pickup mandiri di lokasi |
| Jumlah box ≥ 25 | Delivery tersedia, mengikuti tabel tier ongkir di bawah |

### 5.2 Tier Ongkir Berdasarkan Jumlah Box

| Tingkatan | Jumlah Box | Kebijakan Ongkir |
|---|---|---|
| Tier 1 | 25 – 49 box | Ongkir penuh: `jarak (km) × Rp 3.000` |
| Tier 2 | 50 – 99 box | Gratis ongkir untuk 10 km pertama; jarak selebihnya dikenakan Rp 3.000/km |
| Tier 3 | ≥ 100 box | Gratis ongkir untuk 20 km pertama; jarak selebihnya dikenakan Rp 3.000/km |

### 5.3 Logika Perhitungan (Pseudocode)

```
INPUT: jumlah_box, jarak_tempuh_km

JIKA jumlah_box < 25:
    STATUS = "Delivery tidak tersedia, minimum 25 box"
    STOP

JIKA jumlah_box >= 25 DAN jumlah_box < 50:
    jarak_gratis = 0

JIKA jumlah_box >= 50 DAN jumlah_box < 100:
    jarak_gratis = 10

JIKA jumlah_box >= 100:
    jarak_gratis = 20

sisa_jarak = MAX(0, jarak_tempuh_km - jarak_gratis)
ongkir = sisa_jarak × 3000

JIKA ongkir > 0 DAN ongkir < 10000 DAN jarak_tempuh_km < 3:
    ongkir = 10000   // ongkir minimum

OUTPUT: ongkir
```

### 5.4 Simulasi Perhitungan

| Jumlah Box | Jarak | Hasil |
|---|---|---|
| 15 box | 8 km | ❌ Delivery ditolak, minimum 25 box |
| 30 box | 8 km | Rp 24.000 (8 × 3.000) |
| 60 box | 15 km | Rp 15.000 (gratis 10 km, sisa 5 km × 3.000) |
| 120 box | 25 km | Rp 15.000 (gratis 20 km, sisa 5 km × 3.000) |
| 150 box | 35 km | ❌ Di luar radius maksimal 30 km |

---

## 6. Aturan Bisnis — Kategori Catering

Catering menggunakan skema berbeda karena umumnya diantar dengan mobil box/pickup, bukan motor, sehingga biaya tidak linear terhadap jarak seperti Nasi Box. Satuan pemesanan untuk Catering menggunakan **porsi**, agar konsisten dengan satuan "box" pada Nasi Box.

### 6.1 Minimum Pemesanan untuk Delivery

| Kondisi | Status |
|---|---|
| Jumlah porsi < 50 | Delivery **tidak tersedia** — pelanggan diarahkan ke opsi pickup mandiri di lokasi |
| Jumlah porsi ≥ 50 | Delivery tersedia, mengikuti tabel tarif & tier di bawah |

### 6.2 Tarif Ongkir Flat per Zona Jarak

| Zona Jarak | Ongkir Flat |
|---|---|
| 0 – 10 km | Rp 50.000 |
| 10 – 20 km | Rp 100.000 |
| 20 – 30 km | Rp 150.000 |
| > 30 km | Di luar area layanan, perlu konfirmasi manual admin |

### 6.3 Tier Gratis Ongkir Berdasarkan Jumlah Porsi

Pola kelipatan dua kali lipat per tier (50 → 100 → 200), konsisten dengan pola tier Nasi Box (25 → 50 → 100).

| Tingkatan | Jumlah Porsi | Kebijakan Ongkir |
|---|---|---|
| Tier 1 | 50 – 99 porsi | Ongkir flat penuh sesuai zona jarak (tidak ada gratis ongkir) |
| Tier 2 | 100 – 199 porsi | Gratis ongkir untuk zona 0–10 km; zona di atasnya tetap kena tarif flat |
| Tier 3 | ≥ 200 porsi | Gratis ongkir untuk zona 0–20 km; zona di atasnya tetap kena tarif flat |

### 6.4 Logika Perhitungan (Pseudocode)

```
INPUT: jumlah_porsi, jarak_tempuh_km

JIKA jumlah_porsi < 50:
    STATUS = "Delivery tidak tersedia, minimum 50 porsi"
    STOP

JIKA jarak_tempuh_km > 30:
    STATUS = "Di luar area layanan, perlu konfirmasi manual admin"
    STOP

zona = TENTUKAN_ZONA(jarak_tempuh_km)     // "0-10" / "10-20" / "20-30"
tarif_zona = LOOKUP_TARIF(zona)           // 50000 / 100000 / 150000

JIKA jumlah_porsi >= 50 DAN jumlah_porsi < 100:
    zona_gratis = NONE

JIKA jumlah_porsi >= 100 DAN jumlah_porsi < 200:
    zona_gratis = "0-10"

JIKA jumlah_porsi >= 200:
    zona_gratis = "0-20"      // mencakup zona "0-10" dan "10-20"

JIKA zona TERMASUK DALAM zona_gratis:
    ongkir = 0
LAINNYA:
    ongkir = tarif_zona

OUTPUT: ongkir
```

### 6.5 Simulasi Perhitungan

| Jumlah Porsi | Jarak | Hasil |
|---|---|---|
| 40 porsi | 8 km | ❌ Delivery ditolak, minimum 50 porsi |
| 70 porsi | 8 km | Rp 50.000 (zona 0-10 km, belum masuk tier gratis) |
| 150 porsi | 8 km | **Gratis** (zona 0-10 km, sudah masuk zona gratis Tier 2) |
| 150 porsi | 15 km | Rp 100.000 (zona 10-20 km, belum masuk zona gratis Tier 2) |
| 250 porsi | 15 km | **Gratis** (zona 10-20 km, sudah masuk zona gratis Tier 3) |
| 250 porsi | 25 km | Rp 150.000 (zona 20-30 km, di luar zona gratis Tier 3) |
| 300 porsi | 35 km | ❌ Di luar radius maksimal 30 km, konfirmasi manual admin |

> Alternatif kebijakan: ongkir Catering dapat digabungkan ke dalam harga paket (all-in), tergantung kebijakan owner. Opsi ini perlu dikonfirmasi terpisah karena berdampak pada struktur harga paket, bukan hanya logika ongkir.

---

## 7. Alur Pengguna (User Flow)

1. Pelanggan memilih kategori pesanan: **Nasi Box** atau **Catering**.
2. Pelanggan mengisi jumlah pesanan dan alamat pengiriman.
3. Sistem menghitung jarak dari titik A ke alamat pelanggan.
4. Sistem memvalidasi apakah jumlah pesanan memenuhi syarat minimum delivery.
   - Jika tidak memenuhi → tampilkan opsi pickup mandiri.
   - Jika memenuhi → lanjut ke langkah 5.
5. Sistem menghitung ongkir sesuai kategori dan tier yang berlaku.
6. Sistem menampilkan rincian: subtotal pesanan + ongkir = total tagihan.
7. Pelanggan melanjutkan ke pembayaran.

---

## 8. Kebutuhan Non-Fungsional

| Aspek | Kebutuhan |
|---|---|
| Akurasi jarak | Menggunakan API peta (bukan estimasi garis lurus/haversine) agar sesuai kondisi jalan nyata |
| Performa | Perhitungan ongkir maksimal 2 detik setelah alamat diinput |
| Skalabilitas | Parameter tarif, tier, dan radius harus dapat diubah admin tanpa mengubah kode (disimpan sebagai konfigurasi/database, bukan hardcode) |
| Transparansi | Rincian ongkir (jarak, tarif/km, potongan gratis ongkir) ditampilkan jelas ke pelanggan sebelum checkout |

---

## 9. Batasan & Asumsi

- Semua nilai tarif, ambang jumlah box, dan ambang jarak adalah **asumsi awal** yang dapat divalidasi lebih lanjut melalui wawancara dengan pemilik usaha atau observasi harga kompetitor.
- Sistem mengasumsikan satu titik asal pengiriman (tidak ada multi-cabang).
- Perhitungan tidak memperhitungkan kondisi lalu lintas real-time.

---

## 10. Pertanyaan Terbuka untuk Validasi Lanjutan

- Apakah owner BBC setuju dengan radius maksimal 30 km, atau ingin lebih sempit/luas?
- Apakah tarif Rp 3.000/km (Nasi Box) sudah sesuai dengan biaya BBM + waktu kurir saat ini?
- Apakah minimum 50 porsi untuk Catering sudah realistis, atau perlu disesuaikan dengan kapasitas dapur/armada BBC?
- Apakah tarif flat per zona untuk Catering (Rp50.000 / Rp100.000 / Rp150.000) sudah wajar dibanding biaya sewa mobil box/pickup yang sebenarnya?
- Apakah owner ingin opsi ongkir Catering digabung ke harga paket (all-in) sebagai alternatif dari skema flat per zona ini?
