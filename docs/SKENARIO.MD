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
| **Skenario Utama** | |
| **Aksi Aktor** | **Aksi Sistem** |
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
| **Skenario Alternatif A1 – Konsumen Menambah Pesanan** | |
| **Aksi Aktor** | **Aksi Sistem** |
| 1. Konsumen melakukan scan QR Code kembali pada meja yang masih memiliki pesanan aktif. | |
| | 2. Sistem mendeteksi bahwa meja masih memiliki pesanan aktif. |
| | 3. Sistem menampilkan informasi pesanan aktif dan pilihan untuk menambah pesanan. |
| 4. Konsumen memilih menu tambahan. | |
| | 5. Sistem menambahkan menu tambahan ke dalam pesanan aktif pada meja yang sama. |
| | 6. Sistem mengirimkan informasi pesanan tambahan kepada Kasir. |
| 7. Kasir mengonfirmasi pesanan tambahan. | |
| | 8. Sistem meneruskan pesanan tambahan kepada Tim Dapur untuk diproses. |
| | 9. Sistem memperbarui total pesanan sehingga seluruh pesanan tetap dibayarkan dalam satu transaksi. |
| **Skenario Alternatif A2 – Pembayaran Tunai** | |
| **Aksi Aktor** | **Aksi Sistem** |
| 1. Kasir memilih metode pembayaran Tunai. | |
| 2. Kasir memasukkan jumlah uang yang diterima dari konsumen. | |
| | 3. Sistem menghitung dan menampilkan jumlah kembalian. |
| 4. Kasir menekan tombol Konfirmasi Pembayaran. | |
| | 5. Sistem menyimpan pembayaran dan mengubah status pembayaran menjadi *Lunas*. |
| **Skenario Alternatif A3 – Pembayaran QRIS** | |
| **Aksi Aktor** | **Aksi Sistem** |
| 1. Kasir memilih metode pembayaran QRIS. | |
| | 2. Sistem menampilkan QRIS rumah makan. |
| 3. Konsumen melakukan pembayaran sesuai dengan total pesanan. | |
| 4. Kasir memeriksa kesesuaian nominal pembayaran secara manual karena sistem tidak menggunakan payment gateway. | |
| 5. Kasir menekan tombol Konfirmasi Pembayaran. | |
| | 6. Sistem menyimpan pembayaran dan mengubah status pembayaran menjadi *Lunas*. |
| **Kondisi Akhir** | Pesanan telah selesai diproses dan dibayar. Status pembayaran menjadi *Lunas*, status pesanan menjadi *Selesai*, dan status meja kembali menjadi *Tersedia*. |

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
| | 2. Sistem menampilkan daftar paket menu yang tersedia beserta batas minimal porsi/box dan ketentuan batas pemesanan minimal (H-4 untuk Katering, H-2/H-3 untuk Nasi Box). |
| 3. Konsumen memilih salah satu paket menu yang diinginkan dan menentukan jumlah porsi/box yang dipesan. | |
| | 4. Sistem menampilkan pilihan variasi komponen menu yang termasuk dalam paket (lauk utama, sayur, olahan pelengkap, sambal, kerupuk, dessert, dan minuman). |
| 5. Konsumen memilih variasi komponen menu yang diinginkan sesuai ketentuan paket. | |
| 6. Konsumen mengisi data pemesan (Nama Pemesan dan Nomor WhatsApp). | |
| 7. Konsumen menentukan tanggal acara, jam pelaksanaan acara, dan jam estimasi pengiriman/pengambilan. | |
| | 8. Sistem memvalidasi tanggal acara memenuhi ketentuan batas waktu minimal pemesanan. |
| 9. Konsumen memilih metode pengiriman *Delivery* (Diantar ke Alamat) dan mengisikan alamat lengkap lokasi acara serta perkiraan jarak (Km). | |
| | 10. Sistem secara otomatis menghitung biaya ongkos kirim berdasarkan aturan tingkatan gratis ongkir (tier porsi) dan tarif tambahan per kilometer. |
| | 11. Sistem menampilkan rincian tagihan yang memuat subtotal paket menu, biaya ongkos kirim, dan total tagihan keseluruhan. |
| 12. Konsumen memilih opsi pembayaran (Uang Muka / DP 50% atau Pembayaran Penuh / Lunas 100%). | |
| 13. Konsumen menekan tombol Pesan Sekarang / Kirim Pesanan. | |
| | 14. Sistem menyimpan data pemesan ke data pelanggan (mencegah duplikasi data berdasarkan nomor WhatsApp), menyimpan data pesanan (`pesanan`), jadwal pengiriman (`jadwal_pesanan`), rincian menu (`detail_pesanan`), pilihan lauk (`pilihan_pesanan_catering`), dan data pengiriman (`pengiriman`). |
| | 15. Sistem memberikan status pesanan *Menunggu Pembayaran* dan menerbitkan Kode Pesanan resmi. |
| | 16. Sistem mengalihkan konsumen ke halaman Instruksi Pembayaran yang menampilkan nomor rekening bank, QRIS rumah makan, nominal yang harus dibayar, batas waktu pembayaran, dan formulir unggah bukti transfer. |
| 17. Konsumen melakukan transfer pembayaran dan mengunggah foto bukti pembayaran/struk transfer. | |
| | 18. Sistem menyimpan berkas bukti transfer dan mengubah status pembayaran menjadi *Menunggu Verifikasi Pembayaran*. |
| | 19. Sistem mengirimkan notifikasi pesanan dan pembayaran baru ke halaman Kasir/Admin. |
| 20. Kasir/Admin membuka halaman Kelola Pesanan (Katering / Nasi Box) dan memeriksa rincian pesanan serta foto bukti transfer yang diunggah. | |
| 21. Kasir/Admin menekan tombol Verifikasi Pembayaran. | |
| | 22. Sistem mengubah status pembayaran menjadi *DP Terverifikasi* (jika memilih DP) atau *Lunas* (jika pembayaran penuh), serta mengubah status pesanan menjadi *Dikonfirmasi / Terjadwal*. |
| | 23. Sistem menerbitkan Invoice / Bukti Pemesanan resmi berformat PDF yang dapat diunduh langsung oleh konsumen. |
| | 24. Sistem menampilkan jadwal pesanan ke dashboard Tim Dapur dan halaman Jadwal Pengiriman. |
| 25. Menjelang hari acara, Tim Dapur menyiapkan bahan baku dan memasak pesanan sesuai rincian menu dan jumlah porsi. | |
| 26. Setelah makanan selesai dimasak dan dikemas rapi, Tim Dapur menekan tombol Pesanan Siap. | |
| | 27. Sistem mengubah status pesanan menjadi *Siap Dikirim*. |
| 28. Kasir/Admin membuka halaman Jadwal Pengiriman dan memilih Tim Pengantaran (Kurir) untuk mengantar pesanan. | |
| 29. Kasir/Admin menekan tombol Tugaskan Kurir. | |
| | 30. Sistem memperbarui penugasan kurir dan mengubah status pengiriman menjadi *Siap Dikirim*. |
| 31. Tim Pengantaran (Kurir) membawa paket pesanan dan memulai perjalanan menuju alamat lokasi acara. | |
| 32. Tim Pengantaran (Kurir) menekan tombol Dalam Pengantaran. | |
| | 33. Sistem mengubah status pengiriman menjadi *Dalam Pengantaran*. |
| 34. Tim Pengantaran (Kurir) menyerahkan pesanan kepada konsumen di lokasi tujuan. | |
| 35. Tim Pengantaran (Kurir) mengambil foto bukti serah terima pesanan dan mengunggahnya ke sistem. | |
| | 36. Sistem menyimpan foto bukti pengiriman dan mengubah status pengiriman menjadi *Selesai*. |
| | 37. Sistem mengubah status pesanan menjadi *Selesai*. |
| **Skenario Alternatif A1 – Pembayaran Menggunakan Uang Muka (DP 50%) dan Pelunasan** | |
| **Aksi Aktor** | **Aksi Sistem** |
| 1. Konsumen memilih opsi pembayaran Uang Muka (DP 50%) pada saat pemesanan. | |
| | 2. Sistem menghitung nominal DP (50% dari total tagihan) dan sisa tagihan yang wajib dilunasi sebelum tanggal pengiriman. |
| 3. Konsumen melakukan transfer sebesar nominal DP dan mengunggah bukti transfer DP. | |
| 4. Kasir/Admin memverifikasi bukti transfer DP. | |
| | 5. Sistem mengubah status pembayaran menjadi *DP Terverifikasi / Menunggu Pelunasan* dan pesanan tetap dijadwalkan. |
| 6. Menjelang hari pelaksanaan acara (H-2 atau sebelum pengiriman), konsumen membuka halaman Lacak Pesanan dan mengunggah bukti transfer pelunasan sisa tagihan. | |
| 7. Kasir/Admin memeriksa bukti pelunasan dan menekan tombol Verifikasi Pelunasan. | |
| | 8. Sistem mengubah status pembayaran menjadi *Lunas*. |
| **Skenario Alternatif A2 – Pembayaran Penuh (Langsung Lunas 100%)** | |
| **Aksi Aktor** | **Aksi Sistem** |
| 1. Konsumen memilih opsi pembayaran Penuh (Lunas 100%) pada saat formulir pemesanan. | |
| | 2. Sistem menampilkan tagihan penuh (subtotal paket + ongkos kirim). |
| 3. Konsumen melakukan transfer 100% dan mengunggah bukti bayar. | |
| 4. Kasir/Admin memeriksa bukti transfer dan menekan tombol Verifikasi Pembayaran. | |
| | 5. Sistem langsung mengubah status pembayaran menjadi *Lunas* dan status pesanan menjadi *Dikonfirmasi / Terjadwal*. |
| **Skenario Alternatif A3 – Konsumen Memilih Metode Pengambilan di Tempat (Pickup / Ambil Sendiri)** | |
| **Aksi Aktor** | **Aksi Sistem** |
| 1. Pada formulir pemesanan, konsumen memilih metode pengiriman *Pickup* (Diambil Sendiri ke Restoran). | |
| | 2. Sistem menonaktifkan input alamat pengiriman dan menetapkan biaya ongkos kirim menjadi Rp 0. |
| 3. Konsumen menentukan jam pengambilan pesanan di restoran. | |
| 4. Setelah pesanan selesai dimasak, Tim Dapur menandai pesanan *Siap Diambil*. | |
| 5. Konsumen datang ke restoran untuk mengambil pesanan. | |
| 6. Kasir menyerahkan pesanan dan menandai status pesanan menjadi *Selesai*. | |
| **Skenario Alternatif A4 – Bukti Pembayaran Ditolak oleh Kasir/Admin** | |
| **Aksi Aktor** | **Aksi Sistem** |
| 1. Kasir/Admin memeriksa bukti pembayaran dan menemukan bahwa nominal transfer tidak sesuai atau gambar bukti buram/palsu. | |
| 2. Kasir/Admin menekan tombol Tolak Pembayaran dan memasukkan alasan penolakan. | |
| | 3. Sistem mengubah status pembayaran menjadi *Pembayaran Ditolak*. |
| | 4. Sistem menampilkan status penolakan beserta alasan catatan kasir pada halaman Lacak Pesanan konsumen. |
| 5. Konsumen melakukan perbaikan transfer dan mengunggah kembali foto bukti pembayaran yang valid. | |
| | 6. Sistem memperbarui status pembayaran menjadi *Menunggu Verifikasi Pembayaran* kembali. |
| **Skenario Alternatif A5 – Pembatalan Pesanan oleh Konsumen** | |
| **Aksi Aktor** | **Aksi Sistem** |
| 1. Konsumen mengajukan pembatalan pesanan sebelum batas waktu pembatalan yang ditentukan. | |
| 2. Kasir/Admin memeriksa alasan pembatalan pesanan. | |
| 3. Kasir/Admin menekan tombol Batalkan Pesanan. | |
| | 4. Sistem mengubah status pesanan menjadi *Dibatalkan*. |
| | 5. Sistem menghapus jadwal pengiriman dan membatalkan alur persiapan dapur. |
| **Kondisi Akhir** | Pesanan paket katering / nasi box berhasil diproses, dimasak, dan diantar ke alamat konsumen. Status pembayaran menjadi *Lunas*, status pengiriman menjadi *Selesai*, dan status pesanan menjadi *Selesai*. |
