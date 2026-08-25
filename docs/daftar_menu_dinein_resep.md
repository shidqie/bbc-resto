Dine-In — Daftar Menu dan Resep

Dokumen ini digunakan sebagai acuan struktur menu Dine-In dan resep bahan baku pada sistem BBC Resto.

Penting: docs/daftar_menu_dinein.md pada repository saat ini memuat 100 entri menu, sedangkan kondisi database yang disebut sebelumnya memiliki 86 menu Dine-In. Karena itu, dokumen ini mengikuti seluruh 100 entri pada dokumen menu agar dapat dipakai untuk audit: menu yang belum ada di database dapat ditandai dan tidak perlu dibuat otomatis tanpa verifikasi.

Takaran resep di bawah merupakan baseline implementasi per 1 porsi (kecuali Paket Nasi Liwet: 5 orang). Sesuaikan dengan resep operasional final rumah makan apabila terdapat takaran resmi.

Struktur Data

Menu Dine-In
    ↓
Resep Menu
    ↓
Bahan Baku
    ↓
Jumlah + Satuan

Untuk Dine-In tidak diperlukan pilihan_item_paket.menu_id seperti Catering/Nasi Box. Setiap menu Dine-In langsung memiliki resepnya sendiri.

Aturan implementasi:

Satu menu Dine-In memiliki satu kumpulan resep aktif.

Resep disimpan per menu, bukan ditulis ulang pada transaksi.

Jumlah resep menggunakan basis per 1 porsi.

Paket Nasi Liwet menggunakan basis 5 orang sesuai dokumen menu.

Bahan yang sama pada satu resep digabung sebelum disimpan.

Resep yang sudah ada di database harus dibandingkan dengan dokumen ini sebelum dilakukan pembaruan.

Ringkasan Menu

Kategori

Jumlah

Paket Nasi Liwet

3

Paket Nasi Ayam

10

Paket Nasi Ayam Kampung

10

Paket Nasi Bebek

10

Sate

4

Sop

2

Gorengan

2

Lauk Satuan

9

Sayur dan Lalapan

7

Tambahan

7

Cemilan

4

Minuman Jus

8

Minuman

13

Minuman Coffee

9

Minuman Non-Coffee

2

Total

100

Daftar Menu dan Resep

Paket Nasi Liwet

1. Paket Nasi Liwet 1

Harga: Rp170.000

Basis resep: 5 orang

Bahan Baku

Jumlah

Satuan

Beras Liwet

750

gram

Santan Kental

200

ml

Air

800

ml

Bawang Merah

90

gram

Daun Salam

5

lembar

Serai

2,5

batang

Garam

45

gram

Ayam Broiler

1000

gram

Bawang Putih

85

gram

Ketumbar Bubuk

25

gram

Kunyit Bubuk

20

gram

Minyak Goreng

365

ml

Ikan Nila / Mas

900

gram

Jeruk Nipis

25

ml

Tahu

250

gram

Tempe

250

gram

Jengkol

400

gram

Cabai Merah (giling)

40

gram

Kecap Manis

40

ml

Ikan Peda Asin

400

gram

Timun

250

gram

Selada

75

gram

Daun Kemangi

25

gram

Cabai Rawit Merah

50

gram

Cabai Merah Keriting

25

gram

Tomat

50

gram

Terasi

5

gram

Gula Pasir

10

gram

Kacang Panjang

100

gram

Tauge

75

gram

Kol

75

gram

Kacang Tanah (goreng)

100

gram

Kencur

10

gram

Gula Merah / Gula Aren

25

gram

Air Asam Jawa

25

ml

2. Paket Nasi Liwet 2

Harga: Rp205.000

Basis resep: 5 orang

Bahan Baku

Jumlah

Satuan

Beras Liwet

750

gram

Santan Kental

200

ml

Air

800

ml

Bawang Merah

90

gram

Daun Salam

5

lembar

Serai

2,5

batang

Garam

35

gram

Ayam Kampung

1000

gram

Bawang Putih

60

gram

Ketumbar Bubuk

15

gram

Kunyit Bubuk

10

gram

Minyak Goreng

265

ml

Tahu

250

gram

Tempe

250

gram

Jengkol

400

gram

Cabai Merah (giling)

40

gram

Kecap Manis

40

ml

Ikan Peda Asin

400

gram

Timun

250

gram

Selada

75

gram

Daun Kemangi

25

gram

Cabai Rawit Merah

50

gram

Cabai Merah Keriting

25

gram

Tomat

50

gram

Terasi

5

gram

Gula Pasir

10

gram

Kacang Panjang

100

gram

Tauge

75

gram

Kol

75

gram

Kacang Tanah (goreng)

100

gram

Kencur

10

gram

Gula Merah / Gula Aren

25

gram

Air Asam Jawa

25

ml

3. Paket Nasi Liwet 3

Harga: Rp255.000

Basis resep: 5 orang

Bahan Baku

Jumlah

Satuan

Beras Liwet

750

gram

Santan Kental

200

ml

Air

800

ml

Bawang Merah

95

gram

Daun Salam

5

lembar

Serai

2,5

batang

Garam

35

gram

Daging Bebek

1100

gram

Bawang Putih

65

gram

Ketumbar Bubuk

15

gram

Kunyit Bubuk

10

gram

Jahe

10

gram

Daun Jeruk

5

lembar

Minyak Goreng

290

ml

Tahu

250

gram

Tempe

250

gram

Jengkol

400

gram

Cabai Merah (giling)

40

gram

Kecap Manis

40

ml

Ikan Peda Asin

400

gram

Timun

250

gram

Selada

75

gram

Daun Kemangi

25

gram

Cabai Rawit Merah

50

gram

Cabai Merah Keriting

25

gram

Tomat

50

gram

Terasi

5

gram

Gula Pasir

10

gram

Kacang Panjang

100

gram

Tauge

75

gram

Kol

75

gram

Kacang Tanah (goreng)

100

gram

Kencur

10

gram

Gula Merah / Gula Aren

25

gram

Air Asam Jawa

25

ml

Paket Nasi Ayam

4. Nasi Ayam Goreng

Harga: Rp26.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Beras Putih

150

gram

Air

200

ml

Ayam Broiler

200

gram

Bawang Putih

5

gram

Bawang Merah

5

gram

Ketumbar Bubuk

2

gram

Kunyit Bubuk

2

gram

Garam

2

gram

Minyak Goreng

20

ml

5. Nasi Ayam Bakar

Harga: Rp26.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Beras Putih

150

gram

Air

200

ml

Ayam Broiler

200

gram

Bawang Putih

5

gram

Bawang Merah

5

gram

Ketumbar Bubuk

2

gram

Kunyit Bubuk

2

gram

Kecap Manis

15

ml

Garam

2

gram

Minyak Goreng

5

ml

6. Liwet Ayam Goreng

Harga: Rp27.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Beras Liwet

150

gram

Santan Kental

40

ml

Air

160

ml

Bawang Merah

10

gram

Daun Salam

1

lembar

Serai

0,5

batang

Garam

4

gram

Ayam Broiler

200

gram

Bawang Putih

5

gram

Ketumbar Bubuk

2

gram

Kunyit Bubuk

2

gram

Minyak Goreng

20

ml

7. Liwet Ayam Bakar

Harga: Rp27.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Beras Liwet

150

gram

Santan Kental

40

ml

Air

160

ml

Bawang Merah

10

gram

Daun Salam

1

lembar

Serai

0,5

batang

Garam

4

gram

Ayam Broiler

200

gram

Bawang Putih

5

gram

Ketumbar Bubuk

2

gram

Kunyit Bubuk

2

gram

Kecap Manis

15

ml

Minyak Goreng

5

ml

8. Nasi Ayam Penyet Goreng

Harga: Rp27.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Beras Putih

150

gram

Air

200

ml

Ayam Broiler

200

gram

Bawang Putih

5

gram

Bawang Merah

8

gram

Ketumbar Bubuk

2

gram

Kunyit Bubuk

2

gram

Garam

3

gram

Minyak Goreng

25

ml

Cabai Rawit Merah

8

gram

Cabai Merah Keriting

5

gram

Tomat

10

gram

Terasi

1

gram

Gula Pasir

2

gram

Timun

30

gram

Selada

15

gram

Daun Kemangi

5

gram

9. Nasi Ayam Penyet Bakar

Harga: Rp27.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Beras Putih

150

gram

Air

200

ml

Ayam Broiler

200

gram

Bawang Putih

5

gram

Bawang Merah

8

gram

Ketumbar Bubuk

2

gram

Kunyit Bubuk

2

gram

Kecap Manis

15

ml

Garam

3

gram

Minyak Goreng

10

ml

Cabai Rawit Merah

8

gram

Cabai Merah Keriting

5

gram

Tomat

10

gram

Terasi

1

gram

Gula Pasir

2

gram

Timun

30

gram

Selada

15

gram

Daun Kemangi

5

gram

10. Liwet Ayam Penyet Goreng

Harga: Rp28.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Beras Liwet

150

gram

Santan Kental

40

ml

Air

160

ml

Bawang Merah

13

gram

Daun Salam

1

lembar

Serai

0,5

batang

Garam

5

gram

Ayam Broiler

200

gram

Bawang Putih

5

gram

Ketumbar Bubuk

2

gram

Kunyit Bubuk

2

gram

Minyak Goreng

25

ml

Cabai Rawit Merah

8

gram

Cabai Merah Keriting

5

gram

Tomat

10

gram

Terasi

1

gram

Gula Pasir

2

gram

Timun

30

gram

Selada

15

gram

Daun Kemangi

5

gram

11. Liwet Ayam Penyet Bakar

Harga: Rp28.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Beras Liwet

150

gram

Santan Kental

40

ml

Air

160

ml

Bawang Merah

13

gram

Daun Salam

1

lembar

Serai

0,5

batang

Garam

5

gram

Ayam Broiler

200

gram

Bawang Putih

5

gram

Ketumbar Bubuk

2

gram

Kunyit Bubuk

2

gram

Kecap Manis

15

ml

Minyak Goreng

10

ml

Cabai Rawit Merah

8

gram

Cabai Merah Keriting

5

gram

Tomat

10

gram

Terasi

1

gram

Gula Pasir

2

gram

Timun

30

gram

Selada

15

gram

Daun Kemangi

5

gram

12. Nasi Tutug Oncom Ayam Goreng

Harga: Rp27.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Beras Putih

150

gram

Air

200

ml

Oncom

35

gram

Bawang Merah

9

gram

Bawang Putih

8

gram

Cabai Merah Keriting

3

gram

Kencur

2

gram

Daun Kemangi

3

gram

Garam

3

gram

Ayam Broiler

200

gram

Ketumbar Bubuk

2

gram

Kunyit Bubuk

2

gram

Minyak Goreng

20

ml

13. Nasi Tutug Oncom Ayam Bakar

Harga: Rp27.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Beras Putih

150

gram

Air

200

ml

Oncom

35

gram

Bawang Merah

9

gram

Bawang Putih

8

gram

Cabai Merah Keriting

3

gram

Kencur

2

gram

Daun Kemangi

3

gram

Garam

3

gram

Ayam Broiler

200

gram

Ketumbar Bubuk

2

gram

Kunyit Bubuk

2

gram

Kecap Manis

15

ml

Minyak Goreng

5

ml

Paket Nasi Ayam Kampung

14. Nasi Ayam Kampung Goreng

Harga: Rp32.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Beras Putih

150

gram

Air

200

ml

Ayam Kampung

200

gram

Bawang Putih

5

gram

Bawang Merah

5

gram

Ketumbar Bubuk

2

gram

Kunyit Bubuk

2

gram

Garam

2

gram

Minyak Goreng

20

ml

15. Nasi Ayam Kampung Bakar

Harga: Rp32.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Beras Putih

150

gram

Air

200

ml

Ayam Kampung

200

gram

Bawang Putih

5

gram

Bawang Merah

5

gram

Ketumbar Bubuk

2

gram

Kunyit Bubuk

2

gram

Kecap Manis

15

ml

Garam

2

gram

Minyak Goreng

5

ml

16. Liwet Ayam Kampung Goreng

Harga: Rp34.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Beras Liwet

150

gram

Santan Kental

40

ml

Air

160

ml

Bawang Merah

10

gram

Daun Salam

1

lembar

Serai

0,5

batang

Garam

4

gram

Ayam Kampung

200

gram

Bawang Putih

5

gram

Ketumbar Bubuk

2

gram

Kunyit Bubuk

2

gram

Minyak Goreng

20

ml

17. Liwet Ayam Kampung Bakar

Harga: Rp34.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Beras Liwet

150

gram

Santan Kental

40

ml

Air

160

ml

Bawang Merah

10

gram

Daun Salam

1

lembar

Serai

0,5

batang

Garam

4

gram

Ayam Kampung

200

gram

Bawang Putih

5

gram

Ketumbar Bubuk

2

gram

Kunyit Bubuk

2

gram

Kecap Manis

15

ml

Minyak Goreng

5

ml

18. Nasi Ayam Kampung Penyet Goreng

Harga: Rp33.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Beras Putih

150

gram

Air

200

ml

Ayam Kampung

200

gram

Bawang Putih

5

gram

Bawang Merah

8

gram

Ketumbar Bubuk

2

gram

Kunyit Bubuk

2

gram

Garam

3

gram

Minyak Goreng

25

ml

Cabai Rawit Merah

8

gram

Cabai Merah Keriting

5

gram

Tomat

10

gram

Terasi

1

gram

Gula Pasir

2

gram

Timun

30

gram

Selada

15

gram

Daun Kemangi

5

gram

19. Nasi Ayam Kampung Penyet Bakar

Harga: Rp33.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Beras Putih

150

gram

Air

200

ml

Ayam Kampung

200

gram

Bawang Putih

5

gram

Bawang Merah

8

gram

Ketumbar Bubuk

2

gram

Kunyit Bubuk

2

gram

Kecap Manis

15

ml

Garam

3

gram

Minyak Goreng

10

ml

Cabai Rawit Merah

8

gram

Cabai Merah Keriting

5

gram

Tomat

10

gram

Terasi

1

gram

Gula Pasir

2

gram

Timun

30

gram

Selada

15

gram

Daun Kemangi

5

gram

20. Liwet Ayam Kampung Penyet Goreng

Harga: Rp34.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Beras Liwet

150

gram

Santan Kental

40

ml

Air

160

ml

Bawang Merah

13

gram

Daun Salam

1

lembar

Serai

0,5

batang

Garam

5

gram

Ayam Kampung

200

gram

Bawang Putih

5

gram

Ketumbar Bubuk

2

gram

Kunyit Bubuk

2

gram

Minyak Goreng

25

ml

Cabai Rawit Merah

8

gram

Cabai Merah Keriting

5

gram

Tomat

10

gram

Terasi

1

gram

Gula Pasir

2

gram

Timun

30

gram

Selada

15

gram

Daun Kemangi

5

gram

21. Liwet Ayam Kampung Penyet Bakar

Harga: Rp34.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Beras Liwet

150

gram

Santan Kental

40

ml

Air

160

ml

Bawang Merah

13

gram

Daun Salam

1

lembar

Serai

0,5

batang

Garam

5

gram

Ayam Kampung

200

gram

Bawang Putih

5

gram

Ketumbar Bubuk

2

gram

Kunyit Bubuk

2

gram

Kecap Manis

15

ml

Minyak Goreng

10

ml

Cabai Rawit Merah

8

gram

Cabai Merah Keriting

5

gram

Tomat

10

gram

Terasi

1

gram

Gula Pasir

2

gram

Timun

30

gram

Selada

15

gram

Daun Kemangi

5

gram

22. Nasi Tutug Oncom Ayam Kampung Goreng

Harga: Rp34.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Beras Putih

150

gram

Air

200

ml

Oncom

35

gram

Bawang Merah

9

gram

Bawang Putih

8

gram

Cabai Merah Keriting

3

gram

Kencur

2

gram

Daun Kemangi

3

gram

Garam

3

gram

Ayam Kampung

200

gram

Ketumbar Bubuk

2

gram

Kunyit Bubuk

2

gram

Minyak Goreng

20

ml

23. Nasi Tutug Oncom Ayam Kampung Bakar

Harga: Rp34.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Beras Putih

150

gram

Air

200

ml

Oncom

35

gram

Bawang Merah

9

gram

Bawang Putih

8

gram

Cabai Merah Keriting

3

gram

Kencur

2

gram

Daun Kemangi

3

gram

Garam

3

gram

Ayam Kampung

200

gram

Ketumbar Bubuk

2

gram

Kunyit Bubuk

2

gram

Kecap Manis

15

ml

Minyak Goreng

5

ml

Paket Nasi Bebek

24. Nasi Bebek Goreng

Harga: Rp60.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Beras Putih

150

gram

Air

200

ml

Daging Bebek

220

gram

Bawang Putih

6

gram

Bawang Merah

6

gram

Ketumbar Bubuk

2

gram

Kunyit Bubuk

2

gram

Jahe

2

gram

Daun Jeruk

1

lembar

Garam

2

gram

Minyak Goreng

25

ml

25. Nasi Bebek Bakar

Harga: Rp60.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Beras Putih

150

gram

Air

200

ml

Daging Bebek

220

gram

Bawang Putih

6

gram

Bawang Merah

6

gram

Ketumbar Bubuk

2

gram

Kunyit Bubuk

2

gram

Jahe

2

gram

Daun Jeruk

1

lembar

Kecap Manis

15

ml

Garam

2

gram

Minyak Goreng

5

ml

26. Liwet Bebek Penyet Goreng

Harga: Rp61.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Beras Liwet

150

gram

Santan Kental

40

ml

Air

160

ml

Bawang Merah

14

gram

Daun Salam

1

lembar

Serai

0,5

batang

Garam

5

gram

Daging Bebek

220

gram

Bawang Putih

6

gram

Ketumbar Bubuk

2

gram

Kunyit Bubuk

2

gram

Jahe

2

gram

Daun Jeruk

1

lembar

Minyak Goreng

30

ml

Cabai Rawit Merah

8

gram

Cabai Merah Keriting

5

gram

Tomat

10

gram

Terasi

1

gram

Gula Pasir

2

gram

Timun

30

gram

Selada

15

gram

Daun Kemangi

5

gram

27. Liwet Bebek Penyet Bakar

Harga: Rp61.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Beras Liwet

150

gram

Santan Kental

40

ml

Air

160

ml

Bawang Merah

14

gram

Daun Salam

1

lembar

Serai

0,5

batang

Garam

5

gram

Daging Bebek

220

gram

Bawang Putih

6

gram

Ketumbar Bubuk

2

gram

Kunyit Bubuk

2

gram

Jahe

2

gram

Daun Jeruk

1

lembar

Kecap Manis

15

ml

Minyak Goreng

10

ml

Cabai Rawit Merah

8

gram

Cabai Merah Keriting

5

gram

Tomat

10

gram

Terasi

1

gram

Gula Pasir

2

gram

Timun

30

gram

Selada

15

gram

Daun Kemangi

5

gram

28. Nasi Bebek Penyet Goreng

Harga: Rp61.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Beras Putih

150

gram

Air

200

ml

Daging Bebek

220

gram

Bawang Putih

6

gram

Bawang Merah

9

gram

Ketumbar Bubuk

2

gram

Kunyit Bubuk

2

gram

Jahe

2

gram

Daun Jeruk

1

lembar

Garam

3

gram

Minyak Goreng

30

ml

Cabai Rawit Merah

8

gram

Cabai Merah Keriting

5

gram

Tomat

10

gram

Terasi

1

gram

Gula Pasir

2

gram

Timun

30

gram

Selada

15

gram

Daun Kemangi

5

gram

29. Nasi Bebek Penyet Bakar

Harga: Rp61.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Beras Putih

150

gram

Air

200

ml

Daging Bebek

220

gram

Bawang Putih

6

gram

Bawang Merah

9

gram

Ketumbar Bubuk

2

gram

Kunyit Bubuk

2

gram

Jahe

2

gram

Daun Jeruk

1

lembar

Kecap Manis

15

ml

Garam

3

gram

Minyak Goreng

10

ml

Cabai Rawit Merah

8

gram

Cabai Merah Keriting

5

gram

Tomat

10

gram

Terasi

1

gram

Gula Pasir

2

gram

Timun

30

gram

Selada

15

gram

Daun Kemangi

5

gram

30. Liwet Bebek Goreng

Harga: Rp63.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Beras Liwet

150

gram

Santan Kental

40

ml

Air

160

ml

Bawang Merah

11

gram

Daun Salam

1

lembar

Serai

0,5

batang

Garam

4

gram

Daging Bebek

220

gram

Bawang Putih

6

gram

Ketumbar Bubuk

2

gram

Kunyit Bubuk

2

gram

Jahe

2

gram

Daun Jeruk

1

lembar

Minyak Goreng

25

ml

31. Liwet Bebek Bakar

Harga: Rp63.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Beras Liwet

150

gram

Santan Kental

40

ml

Air

160

ml

Bawang Merah

11

gram

Daun Salam

1

lembar

Serai

0,5

batang

Garam

4

gram

Daging Bebek

220

gram

Bawang Putih

6

gram

Ketumbar Bubuk

2

gram

Kunyit Bubuk

2

gram

Jahe

2

gram

Daun Jeruk

1

lembar

Kecap Manis

15

ml

Minyak Goreng

5

ml

32. Nasi Tutug Oncom Bebek Goreng

Harga: Rp63.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Beras Putih

150

gram

Air

200

ml

Oncom

35

gram

Bawang Merah

10

gram

Bawang Putih

9

gram

Cabai Merah Keriting

3

gram

Kencur

2

gram

Daun Kemangi

3

gram

Garam

3

gram

Daging Bebek

220

gram

Ketumbar Bubuk

2

gram

Kunyit Bubuk

2

gram

Jahe

2

gram

Daun Jeruk

1

lembar

Minyak Goreng

25

ml

33. Nasi Tutug Oncom Bebek Bakar

Harga: Rp63.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Beras Putih

150

gram

Air

200

ml

Oncom

35

gram

Bawang Merah

10

gram

Bawang Putih

9

gram

Cabai Merah Keriting

3

gram

Kencur

2

gram

Daun Kemangi

3

gram

Garam

3

gram

Daging Bebek

220

gram

Ketumbar Bubuk

2

gram

Kunyit Bubuk

2

gram

Jahe

2

gram

Daun Jeruk

1

lembar

Kecap Manis

15

ml

Minyak Goreng

5

ml

Sate

34. Sate Sapi

Harga: Rp40.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Daging Sapi (has/potong dadu)

100

gram

Bawang Putih

3

gram

Ketumbar Bubuk

1

gram

Kecap Manis

10

ml

Bumbu Kacang / Saus Kacang

30

gram

Tusuk Sate

5

batang

35. Sate Kambing

Harga: Rp40.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Daging Kambing

100

gram

Bawang Putih

3

gram

Ketumbar Bubuk

1

gram

Kecap Manis

10

ml

Bumbu Kacang / Saus Kacang

30

gram

Tusuk Sate

5

batang

36. Sate Ayam

Harga: Rp28.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Dada Ayam Fillet

100

gram

Bawang Putih

3

gram

Ketumbar Bubuk

1

gram

Kecap Manis

10

ml

Bumbu Kacang / Saus Kacang

30

gram

Tusuk Sate

5

batang

37. Sate Jando

Harga: Rp40.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Daging Campur / Jeroan

100

gram

Sambal Oncom Pedas

25

gram

Bawang Putih

3

gram

Ketumbar Bubuk

1

gram

Kecap Manis

10

ml

Bumbu Kacang / Saus Kacang

30

gram

Tusuk Sate

5

batang

Sop

38. Sop Iga Sapi

Harga: Rp34.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Iga Sapi

180

gram

Wortel

30

gram

Kentang

40

gram

Bawang Putih

4

gram

Daun Bawang

4

gram

Seledri

3

gram

Daun Salam

1

lembar

Merica / Lada Bubuk

1

gram

Pala Bubuk

0,5

gram

Garam

2

gram

Air

350

ml

39. Sop Iga Sapi + Nasi

Harga: Rp40.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Iga Sapi

180

gram

Wortel

30

gram

Kentang

40

gram

Bawang Putih

4

gram

Daun Bawang

4

gram

Seledri

3

gram

Daun Salam

1

lembar

Merica / Lada Bubuk

1

gram

Pala Bubuk

0,5

gram

Garam

2

gram

Air

550

ml

Beras Putih

150

gram

Gorengan

40. Kulit Goreng Jumbo

Harga: Rp20.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Kulit Ayam

120

gram

Tepung Bumbu Serbaguna

30

gram

Bawang Putih Bubuk

2

gram

Garam

1

gram

Minyak Goreng

30

ml

41. Kulit Goreng Jumbo + Nasi

Harga: Rp22.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Kulit Ayam

120

gram

Tepung Bumbu Serbaguna

30

gram

Bawang Putih Bubuk

2

gram

Garam

1

gram

Minyak Goreng

30

ml

Beras Putih

150

gram

Air

200

ml

Lauk Satuan

42. Ayam Bakar

Harga: Rp23.000/pcs

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Ayam Broiler

200

gram

Bawang Putih

5

gram

Bawang Merah

5

gram

Ketumbar Bubuk

2

gram

Kunyit Bubuk

2

gram

Kecap Manis

15

ml

Garam

2

gram

Minyak Goreng

5

ml

43. Ayam Kampung

Harga: Rp28.000/pcs

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Ayam Kampung

200

gram

Bawang Putih

5

gram

Bawang Merah

5

gram

Ketumbar Bubuk

2

gram

Kunyit Bubuk

2

gram

Garam

2

gram

Minyak Goreng

20

ml

44. Ayam Broiler

Harga: Rp18.000/pcs

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Ayam Broiler

200

gram

Bawang Putih

5

gram

Bawang Merah

5

gram

Ketumbar Bubuk

2

gram

Kunyit Bubuk

2

gram

Garam

2

gram

Minyak Goreng

20

ml

45. Bebek

Harga: Rp60.000/pcs

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Daging Bebek

220

gram

Bawang Putih

6

gram

Bawang Merah

6

gram

Ketumbar Bubuk

2

gram

Kunyit Bubuk

2

gram

Jahe

2

gram

Daun Jeruk

1

lembar

Garam

2

gram

Minyak Goreng

25

ml

46. Ikan

Harga: Rp14.000/pcs

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Ikan Nila / Mas

180

gram

Bawang Putih

5

gram

Kunyit Bubuk

2

gram

Ketumbar Bubuk

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

47. Tahu

Harga: Rp4.000/pcs

Basis resep: 1 porsi

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

48. Tempe

Harga: Rp4.000/pcs

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Tempe

50

gram

Bawang Putih

2

gram

Ketumbar Bubuk

1

gram

Garam

1

gram

Minyak Goreng

5

ml

49. Peda

Harga: Rp13.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Ikan Peda Asin

80

gram

Minyak Goreng

10

ml

50. Sepat

Harga: Rp14.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Ikan Sepat Asin

80

gram

Minyak Goreng

10

ml

Sayur dan Lalapan

51. Jengkol

Harga: Rp13.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Jengkol

80

gram

Bawang Merah

5

gram

Bawang Putih

3

gram

Cabai Merah (giling)

8

gram

Kecap Manis

8

ml

Minyak Goreng

8

ml

52. Pete

Harga: Rp13.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Pete

80

gram

Bawang Merah

5

gram

Bawang Putih

3

gram

Cabai Merah (giling)

8

gram

Kecap Manis

8

ml

Minyak Goreng

8

ml

53. Kol Goreng

Harga: Rp13.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Kol

100

gram

Minyak Goreng

15

ml

Garam

1

gram

54. Jukut Goreng

Harga: Rp13.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Sayuran Hijau / Jukut

100

gram

Bawang Putih

3

gram

Garam

1

gram

Minyak Goreng

8

ml

55. Karedok

Harga: Rp15.000

Basis resep: 1 porsi

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

Kacang Tanah (goreng)

20

gram

Kencur

2

gram

Cabai Rawit Merah

2

gram

Gula Merah / Gula Aren

5

gram

Air Asam Jawa

5

ml

56. Lotek

Harga: Rp15.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Kangkung

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

Kacang Tanah (goreng)

25

gram

Kencur

2

gram

Gula Merah / Gula Aren

8

gram

Air Asam Jawa

5

ml

Cabai Rawit Merah

2

gram

57. Pencok Kacang

Harga: Rp15.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Kacang Panjang

80

gram

Kacang Tanah (goreng)

20

gram

Kencur

2

gram

Cabai Rawit Merah

3

gram

Gula Merah / Gula Aren

5

gram

Air Asam Jawa

5

ml

Tambahan

58. Nasi Putih

Harga: Rp7.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Beras Putih

150

gram

Air

200

ml

59. Nasi Liwet

Harga: Rp9.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Beras Liwet

150

gram

Santan Kental

40

ml

Air

160

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

60. Nasi Tutug Oncom

Harga: Rp9.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Beras Putih

150

gram

Air

200

ml

Oncom

35

gram

Bawang Merah

4

gram

Bawang Putih

3

gram

Cabai Merah Keriting

3

gram

Kencur

2

gram

Daun Kemangi

3

gram

Garam

1

gram

61. Nasi Liwet Pulen

Harga: Rp14.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Beras Pulen (Pandan Wangi)

150

gram

Santan Kental

40

ml

Air

160

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

62. Nasi Tutug Oncom Pulen

Harga: Rp14.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Beras Pulen (Pandan Wangi)

150

gram

Air

200

ml

Oncom

35

gram

Bawang Merah

4

gram

Bawang Putih

3

gram

Cabai Merah Keriting

3

gram

Kencur

2

gram

Daun Kemangi

3

gram

Garam

1

gram

63. Sambal

Harga: Rp6.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Cabai Rawit Merah

8

gram

Cabai Merah Keriting

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

Gula Pasir

2

gram

Minyak Goreng

5

ml

64. Lalapan + Sambal

Harga: Rp7.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Timun

30

gram

Selada

15

gram

Daun Kemangi

5

gram

Cabai Rawit Merah

8

gram

Cabai Merah Keriting

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

Gula Pasir

2

gram

Minyak Goreng

5

ml

Cemilan

65. Tahu Gejrot

Harga: Rp13.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Tahu Goreng

100

gram

Bawang Merah

5

gram

Cabai Rawit Merah

4

gram

Gula Merah / Gula Aren

10

gram

Kecap Manis

8

ml

Air Asam Jawa

10

ml

66. Tahu Sumedang

Harga: Rp13.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Tahu Sumedang

120

gram

Minyak Goreng

20

ml

67. Cireng Rujak

Harga: Rp15.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Tepung Tapioka

70

gram

Tepung Terigu

20

gram

Bawang Putih

3

gram

Daun Bawang

3

gram

Kaldu Bubuk

1

gram

Minyak Goreng

20

ml

Gula Merah / Gula Aren

10

gram

Air Asam Jawa

10

ml

Cabai Rawit Merah

3

gram

68. Mendoan

Harga: Rp14.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Tempe Tipis Khusus Mendoan

100

gram

Tepung Terigu

40

gram

Tepung Beras

10

gram

Bawang Putih

3

gram

Daun Bawang

4

gram

Garam

1

gram

Minyak Goreng

20

ml

Minuman Jus

69. Jus Sirsak

Harga: Rp10.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Sirsak

120

gram

Air

100

ml

Gula Cair (simple syrup)

15

ml

Es Batu

80

gram

70. Jus Mangga

Harga: Rp10.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Mangga

120

gram

Air

100

ml

Gula Cair (simple syrup)

15

ml

Es Batu

80

gram

71. Jus Jeruk

Harga: Rp10.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Jeruk

120

gram

Air

100

ml

Gula Cair (simple syrup)

15

ml

Es Batu

80

gram

72. Jus Melon

Harga: Rp10.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Melon

120

gram

Air

100

ml

Gula Cair (simple syrup)

15

ml

Es Batu

80

gram

73. Jus Jambu

Harga: Rp10.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Jambu

120

gram

Air

100

ml

Gula Cair (simple syrup)

15

ml

Es Batu

80

gram

74. Jus Stroberi

Harga: Rp12.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Stroberi

100

gram

Air

100

ml

Gula Cair (simple syrup)

15

ml

Es Batu

80

gram

75. Jus Buah Naga

Harga: Rp12.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Buah Naga

120

gram

Air

100

ml

Gula Cair (simple syrup)

15

ml

Es Batu

80

gram

76. Jus Alpukat

Harga: Rp12.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Alpukat

120

gram

Air

100

ml

Gula Cair (simple syrup)

15

ml

Es Batu

80

gram

Susu Kental Manis

15

gram

Minuman

77. Bandrek

Harga: Rp6.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Jahe

20

gram

Gula Merah / Gula Aren

20

gram

Serai

0,5

batang

Air

250

ml

78. Bajigur

Harga: Rp6.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Santan Kental

60

ml

Gula Merah / Gula Aren

20

gram

Jahe

8

gram

Air

190

ml

79. Bandrek Susu

Harga: Rp7.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Jahe

20

gram

Gula Merah / Gula Aren

20

gram

Serai

0,5

batang

Air

250

ml

Susu Kental Manis

15

gram

80. Bajigur Susu

Harga: Rp7.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Santan Kental

60

ml

Gula Merah / Gula Aren

20

gram

Jahe

8

gram

Air

190

ml

Susu Kental Manis

15

gram

81. Susu Putih

Harga: Rp7.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Susu Bubuk

25

gram

Air

200

ml

82. Susu Cokelat

Harga: Rp7.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Susu Bubuk

25

gram

Bubuk Milo

10

gram

Air

200

ml

83. Milo (Panas/Dingin)

Harga: Rp11.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Bubuk Milo

25

gram

Air

200

ml

Es Batu

80

gram

84. Kopi Kapal Api

Harga: Rp10.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Kopi Sachet

1

sachet

Air

200

ml

85. Kopi Good Day

Harga: Rp10.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Kopi Sachet

1

sachet

Air

200

ml

86. Kopi Luwak

Harga: Rp10.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Kopi Sachet

1

sachet

Air

200

ml

87. Kopi Indocafe

Harga: Rp10.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Kopi Sachet

1

sachet

Air

200

ml

88. Kopi ABC Susu

Harga: Rp10.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Kopi Sachet

1

sachet

Air

200

ml

89. Air Mineral

Harga: Rp5.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Air Mineral (kemasan)

1

botol

Minuman Coffee

90. Es Kopi Susu

Harga: Rp20.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Kopi Bubuk Espresso

18

gram

Susu Segar

120

ml

Gula Cair (simple syrup)

15

ml

Es Batu

80

gram

91. Es Kopi Susu Vanilla

Harga: Rp20.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Kopi Bubuk Espresso

18

gram

Susu Segar

120

ml

Sirup Vanilla

15

ml

Es Batu

80

gram

92. Es Kopi Susu Gula Aren

Harga: Rp20.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Kopi Bubuk Espresso

18

gram

Susu Segar

120

ml

Sirup Gula Aren (kopi)

15

ml

Es Batu

80

gram

93. Americano

Harga: Rp20.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Kopi Bubuk Espresso

18

gram

Air

180

ml

94. Cappuccino

Harga: Rp20.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Kopi Bubuk Espresso

18

gram

Susu Segar

150

ml

95. Café Latte

Harga: Rp20.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Kopi Bubuk Espresso

18

gram

Susu Segar

180

ml

96. Espresso

Harga: Rp15.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Kopi Bubuk Espresso

18

gram

Air

40

ml

97. Kopi Tubruk Arabika

Harga: Rp15.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Kopi Bubuk Arabika

15

gram

Air

200

ml

Gula Pasir

10

gram

98. Kopi Tubruk Robusta

Harga: Rp15.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Kopi Bubuk Robusta

15

gram

Air

200

ml

Gula Pasir

10

gram

Minuman Non-Coffee

99. Caramel Macchiato

Harga: Rp20.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Kopi Bubuk Espresso

18

gram

Susu Segar

150

ml

Sirup Karamel

15

ml

100. Hot Green Matcha

Harga: Rp20.000

Basis resep: 1 porsi

Bahan Baku

Jumlah

Satuan

Bubuk Matcha

15

gram

Susu Segar

180

ml

Gula Cair (simple syrup)

10

ml

Validasi Database

Sebelum mengubah data resep, lakukan audit berdasarkan nama/kode menu.

Target validasi:

Dokumen Dine-In : 100 entri
Database saat ini: 86 menu (berdasarkan kondisi yang disebutkan)

Lakukan:
1. Cocokkan 86 menu database dengan dokumen.
2. Tandai 14 entri yang belum ada / berbeda / sengaja tidak digunakan.
3. Jangan membuat menu baru hanya karena jumlah berbeda tanpa verifikasi.
4. Untuk menu yang sama, sinkronkan resep bahan bakunya.

Contoh query audit resep:

SELECT m.id_menu, m.nama_menu, COUNT(r.id_resep_menu) AS jumlah_bahan
FROM menu m
LEFT JOIN resep_menu r ON r.id_menu = m.id_menu
WHERE m.jenis_menu = 'dine_in'
GROUP BY m.id_menu, m.nama_menu
ORDER BY m.nama_menu;

Sesuaikan nama tabel/kolom query dengan skema database aktual.

Alur Pemakaian Resep Dine-In

Pesanan Dine-In
    ↓
Detail Menu
    ↓
Ambil Resep Menu
    ↓
Resep × Qty Pesanan
    ↓
Gabungkan bahan yang sama
    ↓
Kurangi Stok Harian
    ↓
Catat Riwayat Mutasi Stok

Contoh:

2 × Nasi Ayam Goreng

Beras Putih
150 gram × 2 = 300 gram

Ayam Broiler
200 gram × 2 = 400 gram

Catatan Final

Dokumen menu repository menjadi acuan nama menu dan harga.

Resep pada file ini adalah baseline implementasi dan harus diselaraskan dengan resep operasional final.

Jangan menghapus 86 resep yang sudah ada sebelum proses pencocokan selesai.

Prioritaskan koreksi resep yang tidak sesuai, bukan membuat duplikasi menu.

Setelah sinkronisasi, setiap menu aktif yang menggunakan stok harus memiliki minimal satu baris resep.