# PRD — Modul Pemesanan Catering & Nasi Box
**Rumah Makan Saung Babakan Cinta — Sistem Informasi Penjualan Berbasis Web**

> Dokumen ini disusun dari alur yang sudah dideskripsikan untuk memverifikasi apakah program yang dibangun sudah sesuai dengan rancangan. Gunakan kolom checklist di setiap tahap untuk mencentang saat sudah dicek pada aplikasi berjalan.

---

## 1. Ringkasan (Overview)

Modul ini menangani pemesanan **catering** dan **nasi box** secara end-to-end: mulai dari konsumen memilih paket di website, mengisi data pemesanan, membayar (DP/lunas), diverifikasi pemilik, diproses produksinya (termasuk pengadaan bahan baku), hingga dikirim dan selesai. Konsumen dapat memantau status pesanannya kapan saja melalui fitur pelacakan.

## 2. Aktor (Roles)

| Aktor | Peran dalam alur ini |
|---|---|
| Konsumen | Memilih paket, mengisi form, membayar, melacak status, melunasi sisa tagihan |
| Pemilik (Admin/Owner) | Meninjau & memverifikasi pesanan, membuat pengadaan bahan baku, mengubah status pesanan |
| Sistem | Menghitung total biaya, generate PDF bukti pesanan & rincian bahan baku, mengelola status otomatis sesuai kondisi pembayaran |

---

## 3. Alur Proses & Checklist Verifikasi (Sisi Konsumen)

### 3.1 Pemilihan Paket
- [x] Konsumen dapat memilih layanan **Catering** atau **Nasi Box** dari website
- [x] Konsumen dapat memilih menu/paket yang tersedia untuk masing-masing layanan
- [x] Harga per paket/menu tampil dengan jelas sebelum lanjut ke form

### 3.2 Form Data Pemesanan
Field yang harus tersedia di form (berdasarkan alur & screenshot form "Data Pemesan"):
- [x] Nama Pemesan *(wajib)*
- [x] Nomor Kontak/WhatsApp *(wajib)*
- [x] Nama/Alamat Venue *(opsional)*
- [x] Alamat Lengkap / tujuan pengiriman *(wajib)*
- [x] **Tanggal acara / pengiriman** *(wajib — pastikan field ini ada, karena krusial untuk penjadwalan produksi)*
- [x] Informasi lain sesuai kebutuhan (misal jumlah porsi jika belum dipilih di step sebelumnya, catatan tambahan)

### 3.3 Rincian Pesanan & Pembayaran
- [x] Sistem menampilkan ringkasan pesanan (menu/paket, jumlah, total biaya)
- [x] Konsumen dapat memilih metode pembayaran: **DP (uang muka)** atau **Lunas**
- [x] Konsumen dapat melakukan pembayaran otomatis menggunakan **Midtrans** (menggantikan unggah bukti manual)
- [x] Sistem menyimpan data pesanan setelah pembayaran diinput
- [x] Sistem generate **bukti pemesanan format PDF** yang bisa diunduh konsumen

### 3.4 Peninjauan oleh Pemilik
- [x] Pesanan baru masuk dengan status **"Pesanan Ditinjau"**
- [x] Pemilik dapat membuka daftar pesanan masuk beserta bukti pembayaran
- [x] Jika valid → pemilik konfirmasi → status jadi **"Pesanan Dikonfirmasi"**
- [x] Jika pembayaran **lunas** → status otomatis lanjut ke **"Sedang Diproses"**
- [x] Jika pembayaran **DP** → status menjadi **"Menunggu Pelunasan"**

### 3.5 Pengadaan Bahan Baku
- [x] Setelah pesanan dikonfirmasi, pemilik dapat membuat pengadaan bahan baku berdasarkan menu yang dipesan
- [x] Sistem menghitung otomatis kebutuhan bahan baku dari menu/paket yang dipesan
- [x] Sistem generate **rincian pembelian bahan baku format PDF** yang bisa diunduh
- [x] Setelah bahan baku tersedia, proses produksi dapat dimulai/ditandai selesai

### 3.6 Pelunasan (khusus status "Menunggu Pelunasan")
- [x] Tersedia halaman pelunasan yang bisa diakses konsumen tanpa login (guest)
- [x] Konsumen dapat mencari pesanan dengan **nomor bukti pemesanan** atau **nomor telepon**
- [x] Sistem menampilkan info pesanan + sisa tagihan
- [x] Konsumen dapat membayar pelunasan otomatis via **Midtrans** (menggantikan unggah bukti manual)
- [x] Setelah pembayaran divalidasi Midtrans → status pembayaran jadi **Lunas**, status pesanan otomatis ke **"Sedang Diproses"**

### 3.7 Produksi → Pengiriman → Selesai
- [x] Pemilik ubah status ke **"Menunggu Pengiriman"** setelah produksi selesai
- [x] Pemilik ubah status ke **"Pesanan Dikirim"** saat pesanan dikirim
- [x] Pemilik ubah status ke **"Selesai"** setelah diterima konsumen
- [x] Konsumen dapat memantau seluruh perubahan status di atas melalui fitur **pelacakan pesanan**

---

## 4. Diagram Status Pesanan (Order Lifecycle)

```
Pesanan Ditinjau
      │
      ▼ (pemilik verifikasi valid)
Pesanan Dikonfirmasi
      │
      ├── Bayar Lunas ──────────────► Sedang Diproses
      │
      └── Bayar DP ──► Menunggu Pelunasan ──(lunas diverifikasi)──► Sedang Diproses
                                                                          │
                                                                          ▼
                                                                Menunggu Pengiriman
                                                                          │
                                                                          ▼
                                                                  Pesanan Dikirim
                                                                          │
                                                                          ▼
                                                                       Selesai
```

## 5. Output Dokumen yang Harus Dihasilkan Sistem
| Dokumen | Kapan digenerate | Format |
|---|---|---|
| Bukti Pemesanan | Setelah form pemesanan & pembayaran awal disubmit | PDF |
| Rincian Pembelian Bahan Baku | Setelah pesanan dikonfirmasi & pengadaan dibuat | PDF |

## 6. Aturan Bisnis (Business Rules) yang Perlu Dicek
- [ ] Status tidak bisa "Sedang Diproses" jika pembayaran belum lunas & belum ada pelunasan
- [ ] Nomor bukti pemesanan / nomor telepon harus unik/valid untuk pencarian di halaman pelunasan
- [ ] Perhitungan kebutuhan bahan baku harus mengacu ke resep/komposisi tiap menu (bukan input manual pemilik)
- [ ] Tanggal acara/pengiriman tidak boleh kosong — dipakai sebagai acuan jadwal produksi & pengadaan

---

## 7. Checklist Verifikasi Halaman Admin (Konsistensi dengan Flow Pemesanan)

> Tujuan bagian ini: memastikan apa yang bisa dilakukan/dilihat pemilik di **halaman admin** benar-benar mencerminkan setiap langkah pada flow pemesanan konsumen (Bagian 3), bukan hanya "ada CRUD generik". Setiap item konsumen di atas harus punya pasangan aksi/tampilan di admin.

### 7.1 Dashboard / Daftar Pesanan
- [ ] Admin dapat melihat daftar seluruh pesanan masuk (catering & nasi box), bukan hanya salah satu jenis
- [ ] Daftar pesanan menampilkan status terkini sesuai 7 status pada diagram Bagian 4 (bukan status buatan lain yang tidak ada di diagram)
- [ ] Admin dapat memfilter/mencari pesanan berdasarkan status, tanggal acara, atau nama pemesan
- [ ] Data yang tampil di daftar admin (nama, kontak, tanggal acara, menu, total biaya) **cocok satu-satu** dengan data yang diinput konsumen di form (Bagian 3.2)

### 7.2 Detail Pesanan & Verifikasi Pembayaran
- [ ] Admin dapat membuka detail satu pesanan dan melihat seluruh isian form konsumen (venue, alamat, tanggal, catatan, dll — sesuai field di 3.2)
- [ ] Admin dapat melihat bukti pembayaran (DP/lunas) yang diunggah konsumen di 3.3
- [ ] Aksi "Konfirmasi" di admin memindahkan status persis mengikuti alur 3.4: **Ditinjau → Dikonfirmasi**, lalu otomatis ke **Sedang Diproses** (jika lunas) atau **Menunggu Pelunasan** (jika DP) — tidak ada jalur pintas lain
- [ ] Tidak ada opsi di admin yang memungkinkan status lompat langsung ke "Sedang Diproses" tanpa pesanan berstatus lunas (selaras dengan Business Rule di 6.1)

### 7.3 Pengadaan Bahan Baku
- [ ] Menu pengadaan bahan baku di admin hanya bisa dibuat untuk pesanan yang **sudah berstatus "Pesanan Dikonfirmasi"** ke atas (sesuai urutan 3.5), bukan dari pesanan yang masih "Ditinjau"
- [ ] Daftar kebutuhan bahan baku yang muncul di admin **dihitung otomatis dari menu/paket pesanan terkait**, bukan form input manual kosong
- [ ] Admin dapat mengunduh PDF rincian pembelian bahan baku (sesuai Bagian 5), dan isinya cocok dengan menu pesanan yang bersangkutan
- [ ] Ada penanda/aksi di admin untuk menandai bahan baku sudah tersedia / produksi bisa dimulai

### 7.4 Verifikasi Pelunasan
- [ ] Pesanan berstatus "Menunggu Pelunasan" di admin menampilkan sisa tagihan yang sama dengan yang dilihat konsumen di halaman pelunasan (3.6)
- [ ] Admin dapat melihat bukti pelunasan yang diunggah konsumen melalui halaman guest tersebut
- [ ] Aksi verifikasi pelunasan oleh admin mengubah status pembayaran jadi **Lunas** dan status pesanan jadi **Sedang Diproses** — sesuai urutan 3.6, tidak ke status lain

### 7.5 Update Status Produksi → Pengiriman → Selesai
- [ ] Admin hanya bisa mengubah status secara **berurutan** sesuai diagram Bagian 4 (tidak bisa lompat, misalnya dari "Sedang Diproses" langsung ke "Selesai" tanpa lewat "Menunggu Pengiriman" dan "Pesanan Dikirim")
- [ ] Setiap perubahan status oleh admin di 3.7 langsung tercermin di fitur pelacakan pesanan konsumen (real-time atau minimal setelah refresh)
- [ ] Tidak ada status yang bisa diset di admin selain 7 status resmi pada diagram Bagian 4

### 7.6 Konsistensi Data Umum
- [ ] Total biaya, rincian menu, dan jumlah porsi yang tampil di admin identik dengan yang tampil di ringkasan pesanan konsumen (3.3) — tidak ada selisih akibat perhitungan ganda
- [ ] PDF yang bisa diunduh admin (bukti pemesanan & rincian bahan baku) sama persis dengan/atau superset dari PDF yang diterima konsumen
- [ ] Riwayat/log perubahan status oleh admin tersimpan dan dapat ditelusuri (audit trail), agar bisa dicocokkan jika ada perbedaan status antara sisi admin dan sisi konsumen

---

## 8. Catatan
Dokumen ini fokus pada flow **Catering & Nasi Box**, termasuk kesesuaian sisi admin dengan sisi konsumen. Jika ada flow lain (Dine-In, manajemen bahan baku harian, laporan) yang juga ingin diverifikasi dengan cara yang sama, beri tahu saya alurnya dan akan dibuatkan PRD checklist terpisah agar mudah dicocokkan satu per satu dengan program yang sudah dibuat.
