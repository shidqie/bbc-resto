<RULE[bbc_resto_ui_design]>
---
name: BBC Resto UI Design System
description: Standard UI guidelines for BBC Resto dashboard and modules
---

# UI Design System — BBC Resto

Gunakan panduan ini sebagai standar untuk seluruh halaman aplikasi agar tampilan Dashboard, Pesanan, Menu, Meja, Persediaan, Pengadaan, Pengiriman, Laporan, Pengguna, dan Manajemen Biaya konsisten.

---

# 1. Prinsip Utama

Gunakan tampilan:
* Minimalis
* Profesional
* Bersih
* Dominan putih
* Tidak terlalu banyak warna
* Tidak terlalu banyak *card*
* Jarak antar elemen konsisten
* Bentuk tombol, tabel, filter, formulir, dan modal harus sama di seluruh aplikasi

Jangan membuat desain berbeda untuk setiap modul.

---

# 2. Struktur Halaman

Semua halaman daftar data menggunakan struktur:
```text
Judul Halaman                         [ + Tambah Data ]
Deskripsi singkat
------------------------------------------------------
[ Filter / Pencarian ]
------------------------------------------------------
[ Tabel Data ]
------------------------------------------------------
Pagination
```

Jangan menempatkan filter berbeda-beda pada setiap halaman.

---

# 3. Header Halaman
* Judul di kiri
* Tombol aksi utama di kanan

---

# 4. Tombol
- **Tombol Utama**: + Tambah Data, Simpan, Konfirmasi, Proses
- **Tombol Sekunder**: Batal, Kembali, Reset
- **Tombol Aksi Data**: Detail, Edit, Hapus (Gunakan **Edit** bukan Ubah/Update/Perbarui).

---

# 5. Ukuran Tombol
- **Kecil**: Untuk tombol pada tabel (tinggi ±32 px)
- **Sedang**: Untuk form dan filter (tinggi ±40 px)
- **Besar**: Hanya untuk aksi utama tertentu (tinggi ±40 px)
- Sudut: `border-radius: 8px`

---

# 6. Ikon Tombol
- Tambah → Plus
- Detail → Eye
- Edit → Pencil
- Hapus → Trash2
- Filter → SlidersHorizontal
- Cari → Search
- Reset → RotateCcw
- Cetak → Printer
- Unduh → Download

---

# 7. Filter & Pencarian
Pencarian berada di bagian filter. Jangan membuat kolom pencarian terpisah.
Urutan filter: Pencarian -> Kategori/Jenis -> Status -> Tanggal -> Reset + Terapkan Filter
Gunakan **Terapkan Filter** sebagai standar tombol submit filter.

---

# 8. Tabel
- Kolom standar: `| No | Data Utama | Informasi | Status | Aksi |`
- Posisi: Teks/Tanggal/Status -> Kiri, Jumlah/Harga/Aksi -> Kanan
- Kolom Aksi: Detail, Edit, Hapus. Jika terlalu banyak, gunakan dropdown `[ ⋮ ]`.

---

# 9. Status
Selalu menggunakan *badge* (cth: `[ Menunggu Konfirmasi ]`). Ukuran dan bentuk harus sama di seluruh aplikasi.

---

# 10. Card
Gunakan *card* hanya untuk: Ringkasan, Statistik, Informasi penting, Konfigurasi.
Standar: border, radius 10–12px, padding 16–20px, background putih.

---

# 11. Form
- Pola: Label di atas Input.
- Bahasa label: Singkat (cth: "Nama Menu", bukan "Silakan Masukkan Nama Menu Anda").
- Required Field: `Nama Menu *`
- Aksi di bawah kanan: `[ Batal ] [ Simpan ]` atau `[ Batal ] [ Simpan Perubahan ]`
- Tinggi Input: `40px`
- Placeholder tidak boleh menggantikan label.

---

# 12. Modal & Konfirmasi Hapus
- Modal hanya untuk tindakan sederhana (Konfirmasi, Hapus, Batalkan).
- Semua aksi Hapus **wajib** menggunakan konfirmasi.

---

# 13. Pesan Empty State & Loading
- Gunakan teks standar seperti "Memuat data...", "Belum Ada Data", "Data Tidak Ditemukan".

---

# 14. Format Notifikasi, Tanggal, & Angka
- Notifikasi: Jelas dan seragam.
- Tanggal: `13 Agustus 2026` atau `13 Agustus 2026, 14:30`.
- Rupiah: `Rp25.000`.

---

# 15. Standar Warna, Spacing & Radius
- Warna: Putih (bg utama), Gelap (teks utama), Abu (teks sekunder/border), Merah (hapus/gagal), Hijau (selesai/berhasil), Kuning (menunggu), Biru (informasi/proses).
- Spacing: Tetap (4px, 8px, 12px, 16px, 24px, 32px).
- Radius: Input/Button (8px), Card/Modal (10-12px).

---

# 16. Komponen Reusable (Wajib Diterapkan)
Semua halaman harus menggunakan komponen Blade reusable di `resources/views/components/` untuk konsistensi:
- button, input, select, textarea, search, filter, table, pagination, badge, card, tabs, modal, alert, dropdown action, empty state, loading, breadcrumb.
</RULE[bbc_resto_ui_design]>

<RULE[drawer_ui_system]>
---
name: Drawer UI System — RM BBC
description: Standar wajib untuk seluruh Drawer / Side Panel Detail pada aplikasi RM BBC.
---

# 1. Prinsip Utama
Semua drawer wajib muncul dari sisi kanan, menggunakan overlay transparan, memiliki header tetap, body scrollable, dan footer tetap (jika ada aksi). Dilarang menumpuk banyak card.
Gunakan pola: Drawer -> Header -> Tabs (opsional) -> Body (Section, Field Grid, List, Timeline) -> Footer.

# 2. Ukuran
- Desktop: width 50vw, max 820px, min 680px.
- Mobile: 100vw, 100vh.
Posisi selalu fixed right-0 top-0.

# 3. Struktur Header & Judul
Format: "Detail + Nama Data". (Contoh: "Detail Menu", bukan "Lihat Menu").
Subtitle maksimal 1-2 baris (Contoh: "MN001 • Dine-In").
Tombol Close "X" di kanan atas (40x40px area). Padding: 20-24px X, 16-18px Y. Border bawah tipis.

# 4. Body & Komponen
- Body: padding 24px, section gap 32px.
- Field: LABEL (abu) di atas Value (gelap). Gunakan Grid 2-4 kolom untuk data pendek. Full width untuk data panjang.
- Harga: "Rp170.000", bukan "170000".
- Tanggal: "13 Agustus 2026, 14:30".
- Status Badge: soft colors (success, warning, info, danger, neutral).
- Item List: Qty | Nama Item -> Nilai (di kanan).
- Summary: Label di kiri, Nilai di kanan. Total dengan font lebih besar dan tebal.

# 5. Footer
Fixed di bawah, background putih, border-top, padding 16-24px. Tombol rata kanan: `[ Batal/Tutup ] [ Aksi Utama ]`.

# 6. Komponen Reusable
Wajib menggunakan (jangan duplikasi kode drawer!):
- `<x-drawer.drawer>`
- `<x-drawer.header>`
- `<x-drawer.tabs>`
- `<x-drawer.section>`
- `<x-drawer.field-grid>`
- `<x-drawer.field>`
- `<x-drawer.badge>`
- `<x-drawer.item-list>`
- `<x-drawer.item>`
- `<x-drawer.summary>`
- `<x-drawer.note>`
- `<x-drawer.timeline>`
- `<x-drawer.footer>`
</RULE[drawer_ui_system]>
