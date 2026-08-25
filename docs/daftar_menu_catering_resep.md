Catering — Paket dan Resep Menu

Dokumen ini digunakan sebagai acuan struktur Paket Catering dan relasi setiap item paket ke resep menu pada sistem BBC Resto.

Catatan: Struktur Paket A dan Paket B mengikuti docs/daftar_menu_catering.md. Daftar 24 item resep disusun dari seluruh pilihan unik pada kedua paket. Kuantitas bahan di bawah digunakan sebagai rancangan implementasi per 1 porsi dan perlu disamakan kembali apabila terdapat dokumen resep final dengan takaran resmi.

Struktur Data

Relasi yang digunakan:

Paket Catering
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

Aturan utama:

Paket tidak menyimpan bahan baku secara langsung.

Setiap item paket terhubung ke menu_id yang valid.

Resep disimpan pada item menu.

Item yang sama dapat digunakan oleh Paket A dan Paket B tanpa menduplikasi resep.

pilihan_item_paket.menu_id tidak boleh NULL untuk item yang memiliki resep.

Daftar 24 Item Menu/Resep Catering

Nasi Putih

Sup Kimlo

Sup Bakso

Sup Ayam Sosis

Sapi Teriyaki

Rendang

Bistik

Dori Asam Manis

Dori Saus Mentega

Sambal Goreng Ati Kentang

Salad Buah

Salad Sayuran

Gado-Gado

Rujak Buah

Kerupuk Udang

Air Mineral

Bakso Tahu

Mi Kocok

Buah Potong

Es Krim

Sup Sosis

Ayam Teriyaki

Ayam Suwir

Ayam Rica-Rica

Paket Catering

Paket A — MN101

Harga: Rp47.500 per porsi

Nasi

Nasi Putih → menu_id valid → Resep Nasi Putih

Aneka Sup — pilih satu

Sup Kimlo → menu_id valid → Resep Sup Kimlo

Sup Bakso → menu_id valid → Resep Sup Bakso

Sup Ayam Sosis → menu_id valid → Resep Sup Ayam Sosis

Aneka Olahan Daging Sapi — pilih satu

Sapi Teriyaki → menu_id valid → Resep Sapi Teriyaki

Rendang → menu_id valid → Resep Rendang

Bistik → menu_id valid → Resep Bistik

Aneka Olahan Tambahan — pilih satu

Dori Asam Manis → menu_id valid → Resep Dori Asam Manis

Dori Saus Mentega → menu_id valid → Resep Dori Saus Mentega

Sambal Goreng Ati Kentang → menu_id valid → Resep Sambal Goreng Ati Kentang

Sayuran — pilih satu

Salad Buah → menu_id valid → Resep Salad Buah

Salad Sayuran → menu_id valid → Resep Salad Sayuran

Gado-Gado → menu_id valid → Resep Gado-Gado

Rujak Buah → menu_id valid → Resep Rujak Buah

Pelengkap

Kerupuk Udang → menu_id valid → Resep Kerupuk Udang

Air Mineral → menu_id valid → Resep Air Mineral

Stall — pilih satu

Bakso Tahu → menu_id valid → Resep Bakso Tahu

Mi Kocok → menu_id valid → Resep Mi Kocok

Dessert — pilih satu

Buah Potong → menu_id valid → Resep Buah Potong

Es Krim → menu_id valid → Resep Es Krim

Paket B — MN102

Harga: Rp42.500 per porsi

Nasi

Nasi Putih → menu_id valid → Resep Nasi Putih

Aneka Sup — pilih satu

Sup Kimlo → menu_id valid → Resep Sup Kimlo

Sup Bakso → menu_id valid → Resep Sup Bakso

Sup Sosis → menu_id valid → Resep Sup Sosis

Aneka Olahan Ayam — pilih satu

Ayam Teriyaki → menu_id valid → Resep Ayam Teriyaki

Ayam Suwir → menu_id valid → Resep Ayam Suwir

Ayam Rica-Rica → menu_id valid → Resep Ayam Rica-Rica

Aneka Olahan Tambahan — pilih satu

Dori Asam Manis → menu_id valid → Resep Dori Asam Manis

Dori Saus Mentega → menu_id valid → Resep Dori Saus Mentega

Sambal Goreng Ati Kentang → menu_id valid → Resep Sambal Goreng Ati Kentang

Sayuran — pilih satu

Salad Buah → menu_id valid → Resep Salad Buah

Salad Sayuran → menu_id valid → Resep Salad Sayuran

Gado-Gado → menu_id valid → Resep Gado-Gado

Rujak Buah → menu_id valid → Resep Rujak Buah

Pelengkap

Kerupuk Udang → menu_id valid → Resep Kerupuk Udang

Air Mineral → menu_id valid → Resep Air Mineral

Stall — pilih satu

Bakso Tahu → menu_id valid → Resep Bakso Tahu

Mi Kocok → menu_id valid → Resep Mi Kocok

Dessert — pilih satu

Buah Potong → menu_id valid → Resep Buah Potong

Es Krim → menu_id valid → Resep Es Krim

Resep Menu Catering

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

2. Sup Kimlo

Bahan Baku

Jumlah

Satuan

Soun

20

gram

Jamur Kuping

15

gram

Wortel

20

gram

Bakso Ikan

30

gram

Telur Puyuh

2

butir

Kaldu Ayam

250

ml

Bawang Putih Goreng

3

gram

Daun Bawang

3

gram

Seledri

2

gram

Garam

1

gram

Merica

0,5

gram

3. Sup Bakso

Bahan Baku

Jumlah

Satuan

Bakso Sapi

60

gram

Wortel

20

gram

Kol

20

gram

Kaldu Sapi

250

ml

Bawang Putih Goreng

3

gram

Daun Bawang

3

gram

Seledri

2

gram

Garam

1

gram

Merica

0,5

gram

4. Sup Ayam Sosis

Bahan Baku

Jumlah

Satuan

Dada Ayam Fillet

40

gram

Sosis

40

gram

Wortel

20

gram

Buncis

20

gram

Kaldu Ayam

250

ml

Bawang Putih

3

gram

Daun Bawang

3

gram

Seledri

2

gram

Garam

1

gram

Merica

0,5

gram

5. Sapi Teriyaki

Bahan Baku

Jumlah

Satuan

Daging Sapi

100

gram

Bawang Bombay

15

gram

Bawang Putih

3

gram

Kecap Manis

10

ml

Kecap Asin

5

ml

Gula

2

gram

Wijen Sangrai

2

gram

Minyak Goreng

5

ml

6. Rendang

Bahan Baku

Jumlah

Satuan

Daging Sapi

100

gram

Santan Kental

80

ml

Cabai Merah Giling

10

gram

Bawang Merah

8

gram

Bawang Putih

5

gram

Lengkuas

3

gram

Daun Kunyit

1

lembar

Daun Salam

1

lembar

Serai

0,5

batang

Garam

2

gram

7. Bistik

Bahan Baku

Jumlah

Satuan

Daging Sapi

100

gram

Bawang Bombay

15

gram

Bawang Putih

3

gram

Saus Tomat

10

gram

Kecap Manis

10

ml

Margarin

5

gram

Merica

0,5

gram

Pala Bubuk

0,5

gram

Garam

1

gram

8. Dori Asam Manis

Bahan Baku

Jumlah

Satuan

Fillet Dori

100

gram

Tepung Serbaguna

20

gram

Tepung Maizena

5

gram

Bawang Bombay

10

gram

Paprika

10

gram

Saus Tomat

15

gram

Saus Sambal

5

gram

Gula

3

gram

Cuka

5

ml

Minyak Goreng

20

ml

9. Dori Saus Mentega

Bahan Baku

Jumlah

Satuan

Fillet Dori

100

gram

Tepung Serbaguna

20

gram

Margarin

10

gram

Bawang Putih

3

gram

Bawang Bombay

10

gram

Susu Cair

30

ml

Garam

1

gram

Merica

0,5

gram

Minyak Goreng

15

ml

10. Sambal Goreng Ati Kentang

Bahan Baku

Jumlah

Satuan

Ati Ampela Ayam

60

gram

Kentang

60

gram

Santan Kental

30

ml

Cabai Merah

8

gram

Bawang Merah

5

gram

Bawang Putih

3

gram

Lengkuas

2

gram

Daun Salam

1

lembar

Garam

1

gram

Minyak Goreng

10

ml

11. Salad Buah

Bahan Baku

Jumlah

Satuan

Melon

30

gram

Semangka

30

gram

Apel

20

gram

Mangga

20

gram

Anggur

10

gram

Mayones

15

gram

Susu Kental Manis

10

gram

Keju Parut

5

gram

12. Salad Sayuran

Bahan Baku

Jumlah

Satuan

Selada

25

gram

Wortel

20

gram

Timun

20

gram

Jagung Manis

20

gram

Tomat Ceri

15

gram

Mayones

15

gram

13. Gado-Gado

Bahan Baku

Jumlah

Satuan

Tahu Goreng

30

gram

Tempe

30

gram

Telur Ayam

0,5

butir

Kentang

30

gram

Kacang Panjang

20

gram

Tauge

20

gram

Kol

20

gram

Bumbu Kacang

40

gram

Kerupuk

10

gram

14. Rujak Buah

Bahan Baku

Jumlah

Satuan

Bengkuang

25

gram

Mangga Muda

25

gram

Kedondong

20

gram

Nanas

20

gram

Jambu

20

gram

Gula Merah

15

gram

Cabai Rawit

2

gram

Terasi

0,5

gram

Air Asam Jawa

5

ml

15. Kerupuk Udang

Bahan Baku

Jumlah

Satuan

Kerupuk Udang Mentah

15

gram

Minyak Goreng

5

ml

16. Air Mineral

Bahan Baku

Jumlah

Satuan

Air Mineral

1

botol

17. Bakso Tahu

Bahan Baku

Jumlah

Satuan

Bakso Sapi

60

gram

Tahu

50

gram

Kaldu Sapi

250

ml

Bawang Putih

3

gram

Bumbu Kacang

20

gram

Daun Bawang

3

gram

Seledri

2

gram

Garam

1

gram

18. Mi Kocok

Bahan Baku

Jumlah

Satuan

Mi Kuning Basah

100

gram

Kikil Sapi

60

gram

Tauge

30

gram

Kaldu Sapi

250

ml

Bawang Putih Goreng

3

gram

Daun Bawang

3

gram

Seledri

2

gram

Jeruk Limau

0,5

buah

Garam

1

gram

Merica

0,5

gram

19. Buah Potong

Bahan Baku

Jumlah

Satuan

Melon

40

gram

Semangka

40

gram

Pepaya / buah tersedia

40

gram

Komposisi buah dapat disesuaikan dengan buah yang digunakan oleh rumah makan.

20. Es Krim

Bahan Baku

Jumlah

Satuan

Es Krim Siap Pakai

100

ml

21. Sup Sosis

Bahan Baku

Jumlah

Satuan

Sosis

60

gram

Wortel

20

gram

Buncis

20

gram

Kaldu Ayam

250

ml

Bawang Putih

3

gram

Daun Bawang

3

gram

Seledri

2

gram

Garam

1

gram

Merica

0,5

gram

22. Ayam Teriyaki

Bahan Baku

Jumlah

Satuan

Dada Ayam Fillet

100

gram

Bawang Bombay

15

gram

Bawang Putih

3

gram

Kecap Manis

10

ml

Kecap Asin

5

ml

Gula

2

gram

Wijen Sangrai

2

gram

Minyak Goreng

5

ml

23. Ayam Suwir

Bahan Baku

Jumlah

Satuan

Dada Ayam Fillet

100

gram

Bawang Merah

5

gram

Bawang Putih

3

gram

Cabai Merah

5

gram

Daun Jeruk

1

lembar

Kecap Manis

5

ml

Garam

1

gram

Minyak Goreng

5

ml

24. Ayam Rica-Rica

Bahan Baku

Jumlah

Satuan

Ayam

100

gram

Cabai Merah

10

gram

Cabai Rawit

5

gram

Bawang Merah

5

gram

Bawang Putih

3

gram

Tomat

10

gram

Daun Jeruk

1

lembar

Serai

0,5

batang

Garam

1

gram

Minyak Goreng

5

ml

Contoh Relasi Paket ke Resep

MN101 - Paket Catering A
│
├── Nasi Putih
│   └── menu_id = [ID Menu Nasi Putih]
│       └── Resep Nasi Putih
│
├── Komponen Sup
│   ├── Sup Kimlo
│   │   └── menu_id = [ID Menu Sup Kimlo]
│   ├── Sup Bakso
│   │   └── menu_id = [ID Menu Sup Bakso]
│   └── Sup Ayam Sosis
│       └── menu_id = [ID Menu Sup Ayam Sosis]
│
├── Komponen Daging Sapi
│   ├── Sapi Teriyaki → menu_id valid
│   ├── Rendang → menu_id valid
│   └── Bistik → menu_id valid
│
└── dan seterusnya

Paket B menggunakan pola yang sama.

Contoh Penggunaan Menu yang Sama

Menu yang digunakan oleh Paket A dan Paket B tidak perlu dibuat ulang.

Dori Asam Manis
menu_id = [ID Menu Dori Asam Manis]

MN101 Paket Catering A
└── Dori Asam Manis
    └── menu_id = [ID Menu Dori Asam Manis]

MN102 Paket Catering B
└── Dori Asam Manis
    └── menu_id = [ID Menu Dori Asam Manis]

Hal yang sama berlaku untuk:

Nasi Putih

Sup Kimlo

Sup Bakso

Dori Asam Manis

Dori Saus Mentega

Sambal Goreng Ati Kentang

Salad Buah

Salad Sayuran

Gado-Gado

Rujak Buah

Kerupuk Udang

Air Mineral

Bakso Tahu

Mi Kocok

Buah Potong

Es Krim

Perhitungan Kebutuhan Bahan

Rumus utama:

Kebutuhan Bahan = Takaran Resep per Porsi × Jumlah Porsi

Contoh pesanan:

Paket Catering A
Jumlah: 100 porsi

Pilihan:
- Nasi Putih
- Sup Kimlo
- Rendang
- Dori Asam Manis
- Gado-Gado
- Bakso Tahu
- Buah Potong

Item tetap:
- Kerupuk Udang
- Air Mineral

Contoh perhitungan:

Nasi Putih
Beras Putih
150 gram × 100
= 15.000 gram
= 15 kg

Rendang
Daging Sapi
100 gram × 100
= 10.000 gram
= 10 kg

Sup Kimlo
Soun
20 gram × 100
= 2.000 gram
= 2 kg

Bahan yang sama dari beberapa resep harus digabungkan.

Contoh:

Bawang Putih:
Sup Kimlo       = ... gram
Rendang         = 500 gram
Dori Asam Manis = ... gram
Bakso Tahu      = 300 gram
--------------------------------
Total kebutuhan = jumlah seluruh kebutuhan bawang putih

Alur Sistem

Pesanan Catering
    ↓
Paket A / Paket B
    ↓
Pilihan item konsumen
    ↓
Ambil menu_id tiap item
    ↓
Ambil resep menu
    ↓
Resep × jumlah porsi
    ↓
Gabungkan bahan baku yang sama
    ↓
Total kebutuhan bahan baku
    ↓
Bandingkan dengan stok katering
    ↓
Hitung kekurangan bahan
    ↓
Permintaan Pengadaan
    ↓
Purchase Order
    ↓
Penerimaan Bahan Baku
    ↓
Stok Katering bertambah

Validasi Implementasi

Setelah pemetaan selesai, lakukan pengecekan:

SELECT *
FROM pilihan_item_paket
WHERE menu_id IS NULL;

Target untuk pilihan yang memang mempunyai resep:

0 rows

Namun validasi tidak cukup hanya memastikan menu_id terisi. Sistem juga harus memastikan setiap pilihan menunjuk ke menu yang benar.

Contoh:

Sup Kimlo       → menu_id Sup Kimlo
Rendang         → menu_id Rendang
Ayam Teriyaki   → menu_id Ayam Teriyaki
Mi Kocok        → menu_id Mi Kocok

Tidak boleh terjadi:

Sup Kimlo → menu_id Rendang

Target Akhir

CATERING
24 item menu/resep

├── Paket A (MN101)
│   ├── Nasi Putih
│   ├── Pilihan Sup
│   ├── Pilihan Daging Sapi
│   ├── Pilihan Olahan Tambahan
│   ├── Pilihan Sayuran
│   ├── Kerupuk Udang
│   ├── Air Mineral
│   ├── Pilihan Stall
│   └── Pilihan Dessert
│
└── Paket B (MN102)
    ├── Nasi Putih
    ├── Pilihan Sup
    ├── Pilihan Olahan Ayam
    ├── Pilihan Olahan Tambahan
    ├── Pilihan Sayuran
    ├── Kerupuk Udang
    ├── Air Mineral
    ├── Pilihan Stall
    └── Pilihan Dessert

Setiap item
    ↓
menu_id valid
    ↓
Menu
    ↓
Resep per porsi
    ↓
Bahan baku