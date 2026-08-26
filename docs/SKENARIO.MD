# DOKUMEN USE CASE SKENARIO
**Sistem Informasi Rumah Makan Saung Babakan Cinta (BBC Resto)**

---

## USE CASE 1: PEMESANAN DINE-IN

| Keterangan / Aksi Aktor | Deskripsi / Aksi Sistem |
| :--- | :--- |
| **Nama Use Case** | **Pemesanan Dine-In** |
| **Nomor** | 1 |
| **Aktor** | Konsumen, Kasir, Tim Dapur, Pelayan |
| **Deskripsi** | Proses pemesanan dine-in mulai dari konsumen memilih menu melalui self-order, konfirmasi pesanan oleh Kasir, pemrosesan pesanan oleh Tim Dapur, penyajian pesanan oleh Pelayan, hingga proses pembayaran selesai. |
| **Kondisi Awal** | Konsumen berada pada meja yang tersedia. Data menu dan meja telah tersedia pada sistem. Kasir dan Tim Dapur telah masuk ke dalam sistem. |

### Skenario Utama
| Aksi Aktor | Aksi Sistem |
| :--- | :--- |
| 1. Konsumen melakukan scan QR Code pada meja. | |
| | 2. Sistem menampilkan daftar menu yang tersedia. |
| 3. Konsumen memilih menu dengan menekan tombol +. | |
| | 4. Sistem menambahkan menu yang dipilih ke dalam keranjang pesanan. |
| 5. Konsumen menekan tombol Pesan. | |
| | 6. Sistem menampilkan halaman Konfirmasi Pesanan yang berisi data pemesan, rincian pesanan, dan total pesanan. |
| 7. Konsumen mengisi data pemesan kemudian menekan tombol Pesan Menu. | |
| | 8. Sistem menyimpan pesanan dan memberikan status *Menunggu Konfirmasi*. |
| | 9. Sistem menampilkan informasi bahwa pesanan berhasil dibuat serta menyediakan Bukti Pesanan yang dapat diunduh. |
| | 10. Sistem mengirimkan informasi pesanan ke halaman Daftar Pesanan Dine-In Aktif milik Kasir. |
| 11. Kasir menerima notifikasi pesanan baru. | |
| 12. Kasir membuka dan memeriksa rincian pesanan. | |
| 13. Kasir menekan tombol Konfirmasi. | |
| | 14. Sistem mengubah status pesanan menjadi *Sedang Diproses* serta mencetak Struk Dapur Checker dan Meja Checker. |
| 15. Kasir memberikan Struk Dapur Checker kepada Tim Dapur sebagai acuan dalam menyiapkan pesanan. | |
| | 16. Sistem menampilkan pesanan pada halaman Tim Dapur dengan status *Sedang Diproses*. |
| 17. Tim Dapur menyiapkan makanan dan minuman berdasarkan pesanan. | |
| 18. Setelah pesanan selesai disiapkan, Tim Dapur menekan tombol Pesanan Siap. | |
| | 19. Sistem mengubah status pesanan menjadi *Siap Disajikan*. |
| 20. Pelayan mengambil pesanan yang telah siap dan mengantarkannya ke meja konsumen. | |
| 21. Pelayan meletakkan Meja Checker pada meja konsumen sebagai penanda dan media pencocokan pesanan. | |
| 22. Setelah pesanan dihidangkan, Pelayan memberikan informasi kepada Kasir. | |
| 23. Kasir mengubah status pesanan menjadi *Sudah Dihidangkan*. | |
| 24. Setelah selesai makan, konsumen melakukan pembayaran di Kasir. | |
| 25. Kasir membuka halaman Daftar Pesanan Dine-In Aktif dan memilih pesanan berdasarkan kode pesanan, nomor meja, atau nama konsumen. | |
| 26. Kasir menekan tombol Bayar. | |
| | 27. Sistem menampilkan rincian pesanan, total pembayaran, dan pilihan metode pembayaran. |
| 28. Konsumen menentukan metode pembayaran Tunai atau QRIS. | |
| 29. Jika pembayaran dilakukan secara tunai, Kasir memasukkan jumlah uang yang diterima. | |
| | 30. Sistem menghitung dan menampilkan jumlah kembalian. |
| 31. Jika pembayaran menggunakan QRIS, Kasir memilih metode pembayaran QRIS. | |
| | 32. Sistem menampilkan QRIS rumah makan. |
| 33. Konsumen melakukan pembayaran melalui QRIS. | |
| 34. Kasir memeriksa kesesuaian nominal pembayaran QRIS secara manual. | |
| 35. Kasir menekan tombol Konfirmasi Pembayaran. | |
| | 36. Sistem mengubah status pembayaran menjadi *Lunas* dan status pesanan menjadi *Selesai*. |
| | 37. Sistem mengubah status meja menjadi *Tersedia*. |
| | 38. Sistem mencetak Struk Pembayaran Dine-In. |
| 39. Kasir menyerahkan struk pembayaran kepada konsumen. | |

### Skenario Alternatif A1 – Konsumen Menambah Pesanan
| Aksi Aktor | Aksi Sistem |
| :--- | :--- |
| 1. Konsumen melakukan scan QR Code kembali pada meja yang masih memiliki pesanan aktif. | |
| | 2. Sistem mendeteksi bahwa meja masih memiliki pesanan aktif. |
| | 3. Sistem menampilkan informasi pesanan aktif dan pilihan untuk menambah pesanan. |
| 4. Konsumen memilih menu tambahan. | |
| | 5. Sistem menambahkan menu tambahan ke dalam pesanan aktif pada meja yang sama. |
| | 6. Sistem mengirimkan informasi pesanan tambahan kepada Kasir. |
| 7. Kasir mengonfirmasi pesanan tambahan. | |
| | 8. Sistem meneruskan pesanan tambahan kepada Tim Dapur untuk diproses. |
| | 9. Sistem memperbarui total pesanan sehingga seluruh pesanan tetap dibayarkan dalam satu transaksi. |

### Skenario Alternatif A2 – Pembayaran Tunai
| Aksi Aktor | Aksi Sistem |
| :--- | :--- |
| 1. Kasir memilih metode pembayaran Tunai. | |
| 2. Kasir memasukkan jumlah uang yang diterima dari konsumen. | |
| | 3. Sistem menghitung dan menampilkan jumlah kembalian. |
| 4. Kasir menekan tombol Konfirmasi Pembayaran. | |
| | 5. Sistem menyimpan pembayaran dan mengubah status pembayaran menjadi *Lunas*. |

### Skenario Alternatif A3 – Pembayaran QRIS
| Aksi Aktor | Aksi Sistem |
| :--- | :--- |
| 1. Kasir memilih metode pembayaran QRIS. | |
| | 2. Sistem menampilkan QRIS rumah makan. |
| 3. Konsumen melakukan pembayaran sesuai dengan total pesanan. | |
| 4. Kasir memeriksa kesesuaian nominal pembayaran secara manual karena sistem tidak menggunakan payment gateway. | |
| 5. Kasir menekan tombol Konfirmasi Pembayaran. | |
| | 6. Sistem menyimpan pembayaran dan mengubah status pembayaran menjadi *Lunas*. |

| Keterangan | Penjelasan |
| :--- | :--- |
| **Kondisi Akhir** | Pesanan telah selesai diproses dan dibayar. Status pembayaran menjadi *Lunas*, status pesanan menjadi *Selesai*, dan status meja kembali menjadi *Tersedia*. |

---

## USE CASE 2: PEMESANAN NASI BOX DAN KATERING

| Keterangan / Aksi Aktor | Deskripsi / Aksi Sistem |
| :--- | :--- |
| **Nama Use Case** | **Pemesanan Nasi Box dan Katering** |
| **Nomor** | 2 |
| **Aktor** | Konsumen, Kasir / Admin, Tim Dapur, Tim Pengantaran (Kurir) |
| **Deskripsi** | Proses pemesanan paket nasi box dan katering mulai dari pemilihan paket menu dan variasi komponen lauk, penentuan jadwal acara dan alamat pengiriman, kalkulasi ongkos kirim berdasarkan jarak dan porsi, pembayaran uang muka (DP) atau lunas, verifikasi pembayaran oleh Kasir/Admin, penyiapan pesanan oleh Tim Dapur, hingga pengantaran pesanan oleh Tim Pengantaran (Kurir) sampai ke lokasi konsumen. |
| **Kondisi Awal** | Data paket menu, komponen menu, dan aturan tarif pengiriman telah tersedia di dalam sistem. Konsumen mengakses halaman pemesanan katering atau nasi box. Kasir/Admin, Tim Dapur, dan Tim Pengantaran telah masuk ke dalam sistem. |

### Skenario Utama
| Aksi Aktor | Aksi Sistem |
| :--- | :--- |
| 1. Konsumen membuka halaman pemesanan Nasi Box atau Katering. | |
| | 2. Sistem menampilkan daftar paket menu yang tersedia beserta batas minimal jumlah pemesanan (porsi/box). |
| 3. Konsumen memilih paket menu yang diinginkan dan menentukan jumlah porsi/box. | |
| | 4. Sistem menampilkan opsi pilihan variasi/komponen menu yang termasuk dalam paket (lauk utama, sayur, pendamping, dessert, minuman). |
| 5. Konsumen memilih rincian menu komponen sesuai ketentuan paket. | |
| | 6. Sistem memvalidasi kelengkapan pilihan komponen menu. |
| 7. Konsumen mengisi data pemesan (Nama Pemesan, Nomor WhatsApp, dan Alamat Lengkap Pengiriman). | |
| 8. Konsumen menentukan tanggal acara, waktu acara, dan waktu pengiriman yang diinginkan. | |
| | 9. Sistem memeriksa kapasitas pesanan pada tanggal yang dipilih. |
| | 10. Sistem menghitung jarak pengiriman serta ongkos kirim berdasarkan aturan tingkatan gratis ongkir dan tarif per kilometer. |
| | 11. Sistem menampilkan ringkasan tagihan (subtotal item paket, biaya pengiriman/ongkir, dan total pembayaran). |
| 12. Konsumen memilih skema pembayaran (Pembayaran Penuh / Uang Muka DP) dan metode pembayaran (Transfer Bank / QRIS). | |
| 13. Konsumen menekan tombol Buat Pesanan. | |
| | 14. Sistem menyimpan data pesanan dengan status *Menunggu Pembayaran*, mencatat data konsumen otomatis, dan menerbitkan Kode Pesanan. |
| | 15. Sistem menampilkan halaman Pembayaran yang memuat instruksi transfer, nomor rekening/QRIS, rincian tagihan, batas waktu pembayaran, dan formulir unggah bukti transfer. |
| 16. Konsumen melakukan transfer pembayaran dan mengunggah foto bukti pembayaran ke sistem. | |
| | 17. Sistem menyimpan file bukti pembayaran dan mengubah status pembayaran menjadi *Menunggu Verifikasi Pembayaran*. |
| | 18. Sistem mengirimkan notifikasi pesanan dan bukti pembayaran baru ke halaman Kasir/Admin. |
| 19. Kasir/Admin membuka halaman Kelola Pesanan (Katering / Nasi Box) dan memeriksa rincian bukti transfer yang diunggah. | |
| 20. Kasir/Admin menekan tombol Verifikasi Pembayaran. | |
| | 21. Sistem memverifikasi pembayaran, mengubah status pembayaran menjadi *DP Terverifikasi* atau *Lunas*, dan mengubah status pesanan menjadi *Dikonfirmasi / Terjadwal*. |
| | 22. Sistem menerbitkan Bukti Pemesanan / Invoice resmi berformat PDF yang dapat diunduh oleh konsumen. |
| | 23. Sistem meneruskan data pesanan ke halaman Jadwal Tim Dapur dan Jadwal Pengiriman. |
| 24. Pada tanggal pelaksanaan, Tim Dapur menyiapkan dan memasak pesanan sesuai rincian menu dan jumlah porsi. | |
| 25. Setelah pesanan selesai dikemas, Tim Dapur menekan tombol Pesanan Siap Dikirim. | |
| | 26. Sistem mengubah status pesanan menjadi *Siap Dikirim*. |
| 27. Kasir/Admin membuka halaman Jadwal Pengiriman dan menugaskan Tim Pengantaran (Kurir) untuk mengantar pesanan. | |
| | 28. Sistem mencatat penugasan kurir dan memperbarui status pengiriman menjadi *Siap Dikirim*. |
| 29. Tim Pengantaran (Kurir) mengambil pesanan dan memulai perjalanan menuju lokasi pengiriman konsumen. | |
| 30. Tim Pengantaran (Kurir) menekan tombol Dalam Pengantaran. | |
| | 31. Sistem mengubah status pengiriman menjadi *Dalam Pengantaran*. |
| 32. Tim Pengantaran (Kurir) menyerahkan pesanan kepada konsumen di lokasi tujuan. | |
| 33. Tim Pengantaran (Kurir) mengambil foto bukti serah terima pesanan dan mengunggahnya ke sistem. | |
| | 34. Sistem mengubah status pengiriman menjadi *Selesai*. |
| | 35. Sistem mengubah status pesanan menjadi *Selesai*. |

### Skenario Alternatif A1 – Pembayaran Menggunakan Uang Muka (DP) dan Pelunasan
| Aksi Aktor | Aksi Sistem |
| :--- | :--- |
| 1. Konsumen memilih skema pembayaran Uang Muka (DP) pada saat pemesanan. | |
| | 2. Sistem menghitung nominal DP minimal dan sisa tagihan yang harus dilunasi sebelum tanggal pengiriman. |
| 3. Konsumen mengunggah bukti transfer pembayaran DP. | |
| 4. Kasir/Admin memverifikasi bukti transfer DP. | |
| | 5. Sistem mengubah status pembayaran menjadi *Menunggu Pelunasan* dan pesanan tetap dijadwalkan. |
| 6. Menjelang hari acara (sesuai batas H-minus pelunasan), konsumen mengakses halaman Lacak Pesanan / Pembayaran dan mengunggah bukti transfer pelunasan. | |
| 7. Kasir/Admin memverifikasi bukti transfer pelunasan. | |
| | 8. Sistem mengubah status pembayaran menjadi *Lunas*. |

### Skenario Alternatif A2 – Pembayaran Penuh (Langsung Lunas 100%)
| Aksi Aktor | Aksi Sistem |
| :--- | :--- |
| 1. Konsumen memilih skema Pembayaran Penuh (100%) pada saat pemesanan. | |
| | 2. Sistem menampilkan total tagihan penuh (subtotal paket + ongkos kirim). |
| 3. Konsumen melakukan transfer dan mengunggah bukti pembayaran penuh. | |
| 4. Kasir/Admin memeriksa dan menekan tombol Verifikasi Pembayaran. | |
| | 5. Sistem langsung mengubah status pembayaran menjadi *Lunas* dan status pesanan menjadi *Dikonfirmasi / Terjadwal*. |

### Skenario Alternatif A3 – Bukti Pembayaran Ditolak oleh Kasir/Admin
| Aksi Aktor | Aksi Sistem |
| :--- | :--- |
| 1. Kasir/Admin memeriksa bukti pembayaran yang diunggah dan menemukan ketidaksesuaian nominal atau bukti tidak valid. | |
| 2. Kasir/Admin menekan tombol Tolak Pembayaran disertai catatan alasan penolakan. | |
| | 3. Sistem mengubah status pembayaran menjadi *Pembayaran Ditolak*. |
| | 4. Sistem menampilkan informasi penolakan dan alasan kepada konsumen pada halaman Lacak Pesanan. |
| 5. Konsumen mengunggah ulang bukti transfer pembayaran yang valid. | |
| | 6. Sistem memperbarui status pembayaran menjadi *Menunggu Verifikasi Pembayaran* kembali. |

### Skenario Alternatif A4 – Pembatalan Pesanan oleh Konsumen
| Aksi Aktor | Aksi Sistem |
| :--- | :--- |
| 1. Konsumen mengajukan pembatalan pesanan sebelum batas waktu H-minus pembatalan. | |
| 2. Kasir/Admin memeriksa permohonan pembatalan. | |
| 3. Kasir/Admin menekan tombol Batalkan Pesanan. | |
| | 4. Sistem mengubah status pesanan menjadi *Dibatalkan*. |
| | 5. Sistem menghapus jadwal pengiriman dan menghentikan alur produksi pesanan di dapur. |

| Keterangan | Penjelasan |
| :--- | :--- |
| **Kondisi Akhir** | Pesanan nasi box / katering berhasil diproses, dimasak, dan diantar ke alamat tujuan. Status pembayaran menjadi *Lunas*, status pengiriman menjadi *Selesai*, dan status pesanan menjadi *Selesai*. |
