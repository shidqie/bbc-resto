# DOKUMEN USE CASE SKENARIO
**Sistem Informasi Rumah Makan Saung Babakan Cinta (BBC Resto)**

---

## USE CASE 1: PEMESANAN DINE-IN

| Keterangan / Aksi Aktor | Deskripsi / Aksi Sistem |
| :--- | :--- |
| **Nama Use Case** | **Pemesanan Dine-In** |
| **Nomor** | 1 |
| **Aktor** | Konsumen, Kasir, Tim Dapur, Pelayan |
| **Deskripsi** | Proses pemesanan dine-in mulai dari konsumen memilih menu melalui self-order, konfirmasi pesanan oleh Kasir, pemrosesan pesanan oleh Tim Dapur, penyajian pesanan oleh Pelayan, hingga proses pembayaran selesai di Kasir. |
| **Kondisi Awal** | Konsumen berada pada meja makan yang tersedia. Data menu dan meja telah aktif di sistem. Kasir dan Tim Dapur telah login ke sistem. |
| **Skenario Utama** | |
| **Aksi Aktor** | **Aksi Sistem** |
| 1. Konsumen melakukan scan QR Code pada meja makan. | |
| | 2. Sistem memvalidasi token QR meja dan menampilkan halaman daftar menu Dine-In. |
| 3. Konsumen memilih menu makanan/minuman dan menentukan jumlah porsi. | |
| | 4. Sistem memasukkan menu ke keranjang belanja dan menampilkan total harga sementara. |
| 5. Konsumen menekan tombol Pesan. | |
| | 6. Sistem menampilkan formulir data pemesan (Nama, Nomor WhatsApp, dan Catatan). |
| 7. Konsumen mengisi data pemesan lalu menekan tombol Pesan Menu. | |
| | 8. Sistem menyimpan data pesanan dengan status *Menunggu Konfirmasi*, mencatat data meja terisi, dan mengirimkan notifikasi pesanan baru ke layar Kasir. |
| 9. Kasir membuka pesanan baru tersebut pada daftar pesanan aktif. | |
| | 10. Sistem menampilkan rincian menu pesanan, nomor meja, dan data pemesan. |
| 11. Kasir memeriksa kesesuaian pesanan lalu menekan tombol Konfirmasi Pesanan. | |
| | 12. Sistem mengubah status pesanan menjadi *Sedang Diproses*, mencetak Struk Dapur Checker & Meja Checker, serta meneruskan pesanan ke layar Tim Dapur. |
| 13. Tim Dapur menerima struk pesanan dan menekan tombol Mulai Memasak. | |
| | 14. Sistem mengubah status proses dapur menjadi *Sedang Dimasak*. |
| 15. Tim Dapur menyiapkan makanan hingga selesai, lalu menekan tombol Pesanan Siap. | |
| | 16. Sistem mengubah status pesanan menjadi *Siap Disajikan* dan memberikan penanda visual di sistem. |
| 17. Pelayan membawa pesanan beserta Meja Checker ke meja konsumen, lalu melapor ke Kasir. | |
| | 18. Kasir menekan tombol Pesanan Disajikan pada sistem. |
| | 19. Sistem memperbarui status pesanan menjadi *Sudah Dihidangkan*. |
| 20. Setelah selesai makan, konsumen menuju meja kasir untuk membayar. Kasir memilih pesanan meja tersebut dan menekan tombol Bayar. | |
| | 21. Sistem menampilkan rincian total tagihan dan opsi metode pembayaran (Tunai / QRIS). |
| 22. Kasir memilih metode pembayaran (Tunai/QRIS), memasukkan nominal uang yang diterima dari konsumen, lalu menekan tombol Konfirmasi Pembayaran. | |
| | 23. Sistem memvalidasi pembayaran, menghitung kembalian, mengubah status pembayaran menjadi *Lunas*, status pesanan menjadi *Selesai*, status meja kembali menjadi *Tersedia*, dan mencetak Struk Pembayaran. |
| 24. Kasir menyerahkan struk pembayaran dan uang kembalian kepada konsumen. | |
| | 25. Sistem mengembalikan tampilan kasir ke status siap menerima pesanan berikutnya. |
| **Skenario Alternatif A1 – Konsumen Menambah Pesanan** | |
| **Aksi Aktor** | **Aksi Sistem** |
| 1. Konsumen melakukan scan QR Code kembali pada meja yang sama. | |
| | 2. Sistem mendeteksi adanya pesanan aktif pada meja tersebut dan menampilkan opsi tambah menu. |
| 3. Konsumen memilih menu tambahan dan menekan tombol Tambah Pesanan. | |
| | 4. Sistem menggabungkan menu tambahan ke pesanan aktif yang sedang berjalan dan mengirimkan notifikasi ke Kasir. |
| 5. Kasir menekan tombol Konfirmasi Pesanan Tambahan. | |
| | 6. Sistem meneruskan item tambahan ke Tim Dapur serta memperbarui total tagihan transaksi. |
| **Skenario Alternatif A2 – Pembayaran Menggunakan Tunai** | |
| **Aksi Aktor** | **Aksi Sistem** |
| 1. Kasir memilih metode pembayaran Tunai dan menginput nominal uang yang diserahkan konsumen. | |
| | 2. Sistem secara otomatis menghitung dan menampilkan jumlah uang kembalian. |
| 3. Kasir menekan tombol Konfirmasi Pembayaran. | |
| | 4. Sistem menyimpan transaksi pembayaran dan mengubah status menjadi *Lunas*. |
| **Skenario Alternatif A3 – Pembayaran Menggunakan QRIS** | |
| **Aksi Aktor** | **Aksi Sistem** |
| 1. Kasir memilih metode pembayaran QRIS. | |
| | 2. Sistem menampilkan barcode QRIS statis rumah makan dan total nominal pembayaran. |
| 3. Konsumen melakukan pemindaian QRIS via e-wallet/m-banking dan menunjukkan bukti sukses bayar ke Kasir. | |
| | 4. Kasir mencocokkan nominal pada bukti bayar konsumen lalu menekan tombol Konfirmasi Pembayaran. |
| | 5. Sistem menyimpan transaksi pembayaran QRIS dan mengubah status menjadi *Lunas*. |
| **Kondisi Akhir** | Seluruh pesanan telah dihidangkan dan dibayar lunas. Status pembayaran menjadi *Lunas*, status pesanan menjadi *Selesai*, dan status meja kembali menjadi *Tersedia*. |

---

## USE CASE 2: PEMESANAN NASI BOX DAN KATERING

| Keterangan / Aksi Aktor | Deskripsi / Aksi Sistem |
| :--- | :--- |
| **Nama Use Case** | **Pemesanan Nasi Box dan Katering** |
| **Nomor** | 2 |
| **Aktor** | Konsumen, Kasir / Admin, Tim Dapur, Tim Pengantaran (Kurir) |
| **Deskripsi** | Proses pemesanan paket katering dan nasi box mulai dari pemilihan paket menu dan variasi komponen, penentuan jadwal acara dan metode pengantaran, kalkulasi biaya ongkos kirim otomatis berdasarkan jarak, pembayaran uang muka (DP 50%) atau lunas (100%), verifikasi pembayaran oleh Kasir/Admin, pengadaan dan penyiapan makanan oleh Tim Dapur, hingga pengantaran pesanan oleh Tim Pengantaran (Kurir) ke lokasi konsumen. |
| **Kondisi Awal** | Data paket menu katering dan nasi box, komponen lauk, serta konfigurasi tarif ongkos kirim telah terdaftar di dalam sistem. Konsumen mengakses halaman pemesanan. Kasir/Admin, Tim Dapur, dan Tim Pengantaran telah masuk ke dalam sistem. |
| **Skenario Utama** | |
| **Aksi Aktor** | **Aksi Sistem** |
| 1. Konsumen membuka halaman pemesanan Nasi Box (`/pesan/nasi-box`) atau Katering (`/pesan/catering`). | |
| | 2. Sistem menampilkan katalog pilihan paket menu, ketentuan minimal jumlah porsi/box, dan batas waktu minimal pemesanan (H-4 Katering, H-2/H-3 Nasi Box). |
| 3. Konsumen memilih paket menu dan memasukkan jumlah porsi/box yang diinginkan. | |
| | 4. Sistem menampilkan opsi variasi komponen lauk/menu yang termasuk dalam paket (lauk utama, sayur, olahan pelengkap, sambal, kerupuk, dessert, dan minuman). |
| 5. Konsumen memilih variasi komponen lauk yang diinginkan. | |
| | 6. Sistem memvalidasi kelengkapan pilihan menu paket dan menampilkan formulir data pengiriman & jadwal acara. |
| 7. Konsumen mengisi data pemesan (Nama, No. WhatsApp), memilih tanggal & jam acara, serta memilih metode pengiriman *Delivery* dan mengisi alamat lokasi serta jarak tempuh (Km). | |
| | 8. Sistem menghitung biaya ongkir otomatis berdasarkan tingkatan gratis ongkir & tarif per km, lalu menampilkan rincian total tagihan beserta pilihan skema pembayaran (DP 50% atau Lunas 100%). |
| 9. Konsumen memilih skema pembayaran (DP / Lunas) lalu menekan tombol Pesan Sekarang. | |
| | 10. Sistem menyimpan data pemesan ke data pelanggan (mencegah duplikasi data), menyimpan data pesanan (status: *Menunggu Pembayaran*), jadwal acara, rincian komponen, data pengiriman, dan mengalihkan konsumen ke halaman instruksi pembayaran. |
| 11. Konsumen melakukan transfer bank/QRIS dan mengunggah foto bukti pembayaran ke sistem. | |
| | 12. Sistem menyimpan berkas bukti transfer, memperbarui status pembayaran menjadi *Menunggu Verifikasi Pembayaran*, dan mengirimkan notifikasi ke halaman Kasir/Admin. |
| 13. Kasir/Admin membuka menu Kelola Pesanan (Katering/Nasi Box) dan memeriksa rincian pesanan beserta foto bukti transfer. | |
| | 14. Sistem menampilkan rincian lengkap pesanan dan pratinjau bukti transfer pembayaran. |
| 15. Kasir/Admin menekan tombol Verifikasi Pembayaran. | |
| | 16. Sistem memvalidasi pembayaran, mengubah status pembayaran menjadi *DP Terverifikasi* / *Lunas*, mengubah status pesanan menjadi *Dikonfirmasi / Terjadwal*, menerbitkan Invoice resmi PDF, dan memasukkan jadwal ke antrean Tim Dapur. |
| 17. Pada hari pelaksanaan, Tim Dapur membuka jadwal pesanan, memasak hidangan, mengemas pesanan, lalu menekan tombol Pesanan Siap. | |
| | 18. Sistem mengubah status pesanan menjadi *Siap Dikirim* dan memunculkan pesanan pada menu Jadwal Pengiriman. |
| 19. Kasir/Admin membuka halaman Jadwal Pengiriman, memilih Tim Pengantaran (Kurir), lalu menekan tombol Tugaskan Kurir. | |
| | 20. Sistem mencatat penugasan kurir dan memperbarui status pengiriman menjadi *Siap Dikirim*. |
| 21. Tim Pengantaran (Kurir) mengambil pesanan makanan dan menekan tombol Dalam Pengantaran saat berangkat menuju lokasi acara. | |
| | 22. Sistem mengubah status pengiriman menjadi *Dalam Pengantaran*. |
| 23. Tim Pengantaran (Kurir) menyerahkan pesanan kepada konsumen di lokasi tujuan, mengambil foto serah terima pesanan, mengunggahnya ke sistem, lalu menekan tombol Selesai Pengantaran. | |
| | 24. Sistem menyimpan foto bukti penerimaan, mengubah status pengiriman menjadi *Selesai*, dan mengubah status pesanan menjadi *Selesai*. |
| **Skenario Alternatif A1 – Pembayaran Menggunakan Uang Muka (DP 50%) dan Pelunasan** | |
| **Aksi Aktor** | **Aksi Sistem** |
| 1. Konsumen memilih opsi pembayaran Uang Muka (DP 50%) pada saat pemesanan. | |
| | 2. Sistem menghitung nominal DP (50% dari total tagihan) dan sisa tagihan yang harus dilunasi sebelum pengiriman. |
| 3. Konsumen mengunggah bukti transfer pembayaran DP. | |
| | 4. Sistem menyimpan bukti bayar DP dan mengirimkan notifikasi verifikasi ke Kasir/Admin. |
| 5. Kasir/Admin menekan tombol Verifikasi Pembayaran DP. | |
| | 6. Sistem mengubah status pembayaran menjadi *DP Terverifikasi / Menunggu Pelunasan* dan pesanan tetap diproses. |
| 7. Menjelang hari acara (H-2 atau sebelum pengiriman), konsumen mengunggah bukti transfer pelunasan pada halaman Lacak Pesanan. | |
| | 8. Sistem menyimpan bukti pelunasan dan mengirimkan notifikasi ke Kasir/Admin. |
| 9. Kasir/Admin memeriksa bukti pelunasan lalu menekan tombol Verifikasi Pelunasan. | |
| | 10. Sistem mengubah status pembayaran menjadi *Lunas*. |
| **Skenario Alternatif A2 – Pembayaran Penuh (Langsung Lunas 100%)** | |
| **Aksi Aktor** | **Aksi Sistem** |
| 1. Konsumen memilih opsi Pembayaran Penuh (Lunas 100%) pada formulir pemesanan. | |
| | 2. Sistem menampilkan nominal penuh (subtotal paket + ongkos kirim). |
| 3. Konsumen melakukan transfer 100% dan mengunggah foto bukti pembayaran. | |
| | 4. Sistem menyimpan bukti bayar dan meneruskan notifikasi ke Kasir/Admin. |
| 5. Kasir/Admin memeriksa bukti transfer lalu menekan tombol Verifikasi Pembayaran. | |
| | 6. Sistem langsung mengubah status pembayaran menjadi *Lunas* dan status pesanan menjadi *Dikonfirmasi / Terjadwal*. |
| **Skenario Alternatif A3 – Konsumen Memilih Metode Pengambilan Sendiri di Toko (Pickup)** | |
| **Aksi Aktor** | **Aksi Sistem** |
| 1. Pada formulir pemesanan, konsumen memilih metode pengiriman *Pickup* (Ambil Sendiri). | |
| | 2. Sistem menonaktifkan isian alamat pengiriman dan menetapkan biaya ongkos kirim sebesar Rp 0. |
| 3. Konsumen memasukkan estimasi jam pengambilan pesanan di restoran. | |
| | 4. Sistem menyimpan jadwal waktu pengambilan pesanan oleh konsumen. |
| 5. Setelah makanan selesai disiapkan, Tim Dapur menandai status pesanan *Siap Diambil*. | |
| | 6. Sistem mengirimkan notifikasi kepada konsumen bahwa pesanan telah siap diambil di restoran. |
| 7. Konsumen datang ke restoran dan Kasir menyerahkan pesanan kepada konsumen lalu menekan tombol Pesanan Selesai. | |
| | 8. Sistem memperbarui status pesanan menjadi *Selesai*. |
| **Skenario Alternatif A4 – Bukti Pembayaran Ditolak oleh Kasir/Admin** | |
| **Aksi Aktor** | **Aksi Sistem** |
| 1. Kasir/Admin memeriksa bukti transfer dan menemukan bahwa nominal transfer tidak sesuai atau gambar bukti tidak valid/buram. | |
| | 2. Kasir/Admin menekan tombol Tolak Pembayaran dan memasukkan catatan alasan penolakan. |
| | 3. Sistem mengubah status pembayaran menjadi *Pembayaran Ditolak* dan menampilkan pesan penolakan pada halaman Lacak Pesanan konsumen. |
| 4. Konsumen membaca alasan penolakan dan mengunggah kembali foto bukti pembayaran yang valid. | |
| | 5. Sistem menyimpan bukti pembayaran baru dan memperbarui status menjadi *Menunggu Verifikasi Pembayaran* kembali. |
| **Skenario Alternatif A5 – Pembatalan Pesanan oleh Konsumen** | |
| **Aksi Aktor** | **Aksi Sistem** |
| 1. Konsumen menghubungi admin/mengajukan pembatalan pesanan sebelum batas waktu pembatalan yang ditentukan. | |
| | 2. Kasir/Admin memeriksa permohonan pembatalan pada halaman kelola pesanan lalu menekan tombol Batalkan Pesanan. |
| | 3. Sistem membatalkan pesanan, mengubah status pesanan menjadi *Dibatalkan*, dan membatalkan jadwal pengiriman serta persiapan dapur. |
| **Kondisi Akhir** | Pesanan paket katering / nasi box berhasil diproses, dimasak, dan diantar ke alamat konsumen. Status pembayaran menjadi *Lunas*, status pengiriman menjadi *Selesai*, dan status pesanan menjadi *Selesai*. |
