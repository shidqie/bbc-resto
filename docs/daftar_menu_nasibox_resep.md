Nasi Box — Paket dan Resep Menu

Dokumen ini digunakan sebagai acuan struktur paket Nasi Box dan relasi item paket ke resep menu pada sistem BBC Resto.

Catatan: Struktur paket mengikuti docs/daftar_menu_nasibox.md. Kuantitas resep di bawah merupakan rancangan implementasi per 1 porsi dan perlu disesuaikan kembali jika terdapat dokumen resep final yang lebih rinci.

Struktur Data

Relasi yang digunakan:

Paket Nasi Box
    ↓
Komponen Paket
    ↓
Pilihan Item Paket
    ↓
menu_id
    ↓
Menu
    ↓
Resep Menu
    ↓
Bahan Baku

Setiap pilihan item paket wajib terhubung ke menu_id yang valid apabila item tersebut memiliki resep.

Paket tidak menyimpan bahan baku secara langsung. Resep disimpan pada masing-masing item menu agar dapat digunakan ulang oleh beberapa paket.

Daftar 22 Item Menu/Resep Nasi Box

Nasi Putih

Nasi Liwet

Ayam Goreng

Ayam Bakar

Ikan Goreng

Lele Goreng

Telur Balado

Kentang Balado

Karedok

Lalapan

Sambal

Kerupuk

Melon

Semangka

Jeruk

Puding

Air Mineral

Tempe Goreng

Tahu Goreng

Tumis Buncis Wortel

Cah Brokoli Wortel

Capcay

Paket Nasi Box

Paket A — MN103

Pilihan Nasi — pilih satu

Nasi Putih → menu_id valid

Nasi Liwet → menu_id valid

Pilihan Lauk Ayam — pilih satu

Ayam Goreng → menu_id valid

Ayam Bakar → menu_id valid

Pilihan Lauk Ikan — pilih satu

Ikan Goreng → menu_id valid

Lele Goreng → menu_id valid

Pilihan Lauk Tambahan — pilih satu

Telur Balado → menu_id valid

Kentang Balado → menu_id valid

Sayuran

Karedok → menu_id valid

Lalapan

Lalapan → menu_id valid

Pelengkap

Sambal → menu_id valid

Kerupuk → menu_id valid

Pilihan Buah — pilih satu

Melon → menu_id valid

Semangka → menu_id valid

Jeruk → menu_id valid

Makanan Penutup

Puding → menu_id valid

Minuman

Air Mineral → menu_id valid

Paket B — MN104

Pilihan Nasi — pilih satu

Nasi Putih → menu_id valid

Nasi Liwet → menu_id valid

Pilihan Lauk Utama — pilih satu

Ayam Goreng → menu_id valid

Ayam Bakar → menu_id valid

Ikan Goreng → menu_id valid

Lauk Tambahan

Tempe Goreng → menu_id valid

Tahu Goreng → menu_id valid

Sayuran

Tumis Buncis Wortel → menu_id valid

Lalapan

Lalapan → menu_id valid

Pelengkap

Sambal → menu_id valid

Kerupuk → menu_id valid

Pilihan Buah — pilih satu

Melon → menu_id valid

Semangka → menu_id valid

Jeruk → menu_id valid

Minuman

Air Mineral → menu_id valid

Paket C — MN105

Nasi

Nasi Putih → menu_id valid

Pilihan Lauk Utama — pilih satu

Ayam Goreng → menu_id valid

Ayam Bakar → menu_id valid

Ikan Goreng → menu_id valid

Pilihan Lauk Tambahan — pilih satu

Tempe Goreng → menu_id valid

Tahu Goreng → menu_id valid

Sayuran

Cah Brokoli Wortel → menu_id valid

Lalapan

Lalapan → menu_id valid

Pelengkap

Sambal → menu_id valid

Kerupuk → menu_id valid

Pilihan Buah — pilih satu

Melon → menu_id valid

Semangka → menu_id valid

Jeruk → menu_id valid

Minuman

Air Mineral → menu_id valid

Paket D — MN106

Nasi

Nasi Putih → menu_id valid

Pilihan Lauk Utama — pilih satu

Ayam Goreng → menu_id valid

Ayam Bakar → menu_id valid

Pilihan Lauk Tambahan — pilih satu

Tempe Goreng → menu_id valid

Tahu Goreng → menu_id valid

Sayuran

Capcay → menu_id valid

Lalapan

Lalapan → menu_id valid

Pelengkap

Sambal → menu_id valid

Kerupuk → menu_id valid

Minuman

Air Mineral → menu_id valid

Paket E — MN107

Nasi

Nasi Putih → menu_id valid

Pilihan Lauk Utama — pilih satu

Ayam Goreng → menu_id valid

Ayam Bakar → menu_id valid

Lalapan

Lalapan → menu_id valid

Pelengkap

Sambal → menu_id valid

Kerupuk → menu_id valid

Minuman

Air Mineral → menu_id valid

Resep Menu Nasi Box

Semua resep berikut dihitung untuk 1 porsi.

1. Nasi Putih

Bahan Baku

Jumlah

Satuan

Beras Putih

150

gram

Air

200

ml

2. Nasi Liwet

Bahan Baku

Jumlah

Satuan

Beras Liwet

150

gram

Santan

50

ml

Air

150

ml

Bawang Merah

5

gram

Daun Salam

1

lembar

Serai

0,5

batang

Garam

2

gram

3. Ayam Goreng

Bahan Baku

Jumlah

Satuan

Ayam

200

gram

Bawang Putih

5

gram

Bawang Merah

5

gram

Ketumbar

2

gram

Kunyit

2

gram

Garam

2

gram

Minyak Goreng

20

ml

4. Ayam Bakar

Bahan Baku

Jumlah

Satuan

Ayam

200

gram

Bawang Putih

5

gram

Bawang Merah

5

gram

Ketumbar

2

gram

Kunyit

2

gram

Kecap Manis

15

ml

Garam

2

gram

Minyak

5

ml

5. Ikan Goreng

Bahan Baku

Jumlah

Satuan

Ikan

180

gram

Bawang Putih

5

gram

Kunyit

2

gram

Ketumbar

2

gram

Jeruk Nipis

5

ml

Garam

2

gram

Minyak Goreng

20

ml

6. Lele Goreng

Bahan Baku

Jumlah

Satuan

Ikan Lele

180

gram

Bawang Putih

5

gram

Kunyit

2

gram

Ketumbar

2

gram

Garam

2

gram

Minyak Goreng

20

ml

7. Telur Balado

Bahan Baku

Jumlah

Satuan

Telur Ayam

1

butir

Cabai Merah

10

gram

Bawang Merah

5

gram

Bawang Putih

3

gram

Tomat

5

gram

Garam

1

gram

Minyak Goreng

10

ml

8. Kentang Balado

Bahan Baku

Jumlah

Satuan

Kentang

80

gram

Cabai Merah

10

gram

Bawang Merah

5

gram

Bawang Putih

3

gram

Tomat

5

gram

Garam

1

gram

Minyak Goreng

10

ml

9. Karedok

Bahan Baku

Jumlah

Satuan

Kacang Panjang

20

gram

Tauge

15

gram

Kol

15

gram

Timun

20

gram

Kacang Tanah

15

gram

Kencur

2

gram

Cabai

2

gram

Gula Merah

5

gram

Air Asam Jawa

5

ml

10. Lalapan

Bahan Baku

Jumlah

Satuan

Timun

30

gram

Selada

15

gram

Kemangi

5

gram

11. Sambal

Bahan Baku

Jumlah

Satuan

Cabai Rawit

8

gram

Cabai Merah

5

gram

Tomat

10

gram

Bawang Merah

3

gram

Terasi

1

gram

Garam

1

gram

Gula

2

gram

Minyak Goreng

5

ml

12. Kerupuk

Bahan Baku

Jumlah

Satuan

Kerupuk Mentah

15

gram

Minyak Goreng

5

ml

13. Melon

Bahan Baku

Jumlah

Satuan

Melon

80

gram

14. Semangka

Bahan Baku

Jumlah

Satuan

Semangka

80

gram

15. Jeruk

Bahan Baku

Jumlah

Satuan

Jeruk

1

buah

16. Puding

Bahan Baku

Jumlah

Satuan

Puding Siap Pakai

1

cup

17. Air Mineral

Bahan Baku

Jumlah

Satuan

Air Mineral 330 ml

1

botol

18. Tempe Goreng

Bahan Baku

Jumlah

Satuan

Tempe

50

gram

Bawang Putih

2

gram

Ketumbar

1

gram

Garam

1

gram

Minyak Goreng

5

ml

19. Tahu Goreng

Bahan Baku

Jumlah

Satuan

Tahu

50

gram

Bawang Putih

2

gram

Garam

1

gram

Minyak Goreng

5

ml

20. Tumis Buncis Wortel

Bahan Baku

Jumlah

Satuan

Buncis

40

gram

Wortel

30

gram

Bawang Merah

3

gram

Bawang Putih

3

gram

Garam

1

gram

Minyak Goreng

5

ml

21. Cah Brokoli Wortel

Bahan Baku

Jumlah

Satuan

Brokoli

40

gram

Wortel

30

gram

Bawang Putih

3

gram

Garam

1

gram

Minyak Goreng

5

ml

22. Capcay

Bahan Baku

Jumlah

Satuan

Wortel

20

gram

Brokoli

20

gram

Kol

20

gram

Sawi

20

gram

Bawang Putih

3

gram

Garam

1

gram

Minyak Goreng

5

ml

Contoh Relasi Paket ke Resep

MN103 - Paket Nasi Box A
│
├── Nasi Putih
│   └── menu_id = [ID Menu Nasi Putih]
│       └── Resep
│           ├── Beras Putih 150 gram
│           └── Air 200 ml
│
├── Ayam Goreng
│   └── menu_id = [ID Menu Ayam Goreng]
│       └── Resep
│           ├── Ayam 200 gram
│           ├── Bawang Putih 5 gram
│           ├── Bawang Merah 5 gram
│           ├── Ketumbar 2 gram
│           ├── Kunyit 2 gram
│           ├── Garam 2 gram
│           └── Minyak Goreng 20 ml
│
├── Karedok
│   └── menu_id = [ID Menu Karedok]
│
├── Lalapan
│   └── menu_id = [ID Menu Lalapan]
│
├── Sambal
│   └── menu_id = [ID Menu Sambal]
│
└── dan seterusnya

Aturan Implementasi

pilihan_item_paket.menu_id tidak boleh NULL untuk item yang memiliki resep.

Satu item menu hanya memiliki satu resep utama.

Resep item tidak diduplikasi untuk setiap paket.

Paket A–E dapat menggunakan menu_id yang sama.

Jika resep suatu menu diubah, semua paket yang menggunakan menu tersebut otomatis menggunakan resep terbaru.

Resep dihitung per 1 porsi.

Kebutuhan bahan baku dihitung dengan rumus:

Kebutuhan Bahan = Jumlah Resep per Porsi × Jumlah Pesanan

Bahan yang sama dari beberapa item menu harus digabungkan sebelum dibandingkan dengan stok.

Kekurangan stok dapat menjadi dasar pembuatan permintaan pengadaan.

Stok tidak berkurang hanya karena paket dibuat; stok berkurang ketika proses bisnis yang ditentukan sistem dijalankan.

Contoh Perhitungan 100 Box

Misalnya konsumen memesan 100 porsi Paket A dengan pilihan:

Nasi Putih

Ayam Goreng

Ikan Goreng

Telur Balado

Karedok

Lalapan

Sambal

Kerupuk

Melon

Puding

Air Mineral

Contoh perhitungan:

Beras Putih
150 gram × 100 porsi
= 15.000 gram
= 15 kg

Ayam
200 gram × 100 porsi
= 20.000 gram
= 20 kg

Timun dari Lalapan
30 gram × 100 porsi
= 3.000 gram
= 3 kg

Alur sistem:

Pesanan Nasi Box
    ↓
Paket + Pilihan Item
    ↓
Ambil menu_id setiap pilihan
    ↓
Ambil resep setiap menu
    ↓
Resep × jumlah porsi
    ↓
Gabungkan bahan yang sama
    ↓
Total kebutuhan bahan baku
    ↓
Bandingkan dengan stok
    ↓
Hitung kekurangan
    ↓
Permintaan Pengadaan