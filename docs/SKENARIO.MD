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

---

## USE CASE 3: PENGIRIMAN PESANAN KATERING DAN NASI BOX

| Keterangan / Aksi Aktor | Deskripsi / Aksi Sistem |
| :--- | :--- |
| **Nama Use Case** | **Pengiriman Pesanan Katering dan Nasi Box** |
| **Nomor** | 3 |
| **Aktor** | Kasir / Admin, Tim Dapur, Tim Pengantaran (Kurir), Konsumen |
| **Deskripsi** | Proses pengelolaan jadwal pengantaran pesanan katering dan nasi box, mulai dari penyiapan makanan oleh Tim Dapur, penugasan kurir oleh Kasir/Admin, keberangkatan pengantaran oleh Tim Pengantaran (Kurir), hingga konfirmasi penerimaan pesanan di lokasi konsumen disertai pengunggahan foto bukti serah terima. |
| **Kondisi Awal** | Pesanan katering atau nasi box telah terverifikasi/lunas, metode pengiriman tercatat *Delivery*, dan data pengiriman telah terdaftar pada Jadwal Pengiriman. Tim Dapur, Kasir/Admin, dan Tim Pengantaran telah login ke sistem. |
| **Skenario Utama** | |
| **Aksi Aktor** | **Aksi Sistem** |
| 1. Tim Dapur membuka jadwal pesanan hari ini, menyelesaikan proses memasak dan pengemasan, lalu menekan tombol Pesanan Siap Dikirim. | |
| | 2. Sistem mengubah status pesanan menjadi *Siap Dikirim*, mengubah status pengiriman menjadi *Siap Dikirim*, dan menampilkan data pada daftar siap antar. |
| 3. Kasir/Admin membuka halaman Jadwal Pengiriman (`/admin/jadwal-pengiriman`). | |
| | 4. Sistem menampilkan kalender dan daftar pengiriman pesanan beserta ringkasan status (Dijadwalkan, Siap Dikirim, Dalam Perjalanan, Terkirim). |
| 5. Kasir/Admin memilih pesanan yang siap dikirim, memilih nama Tim Pengantaran (Kurir) pada dropdown, lalu menekan tombol Tugaskan Kurir. | |
| | 6. Sistem menyimpan ID kurir yang ditugaskan, memperbarui status penugasan, dan mengirimkan notifikasi tugas pengantaran baru ke akun Kurir yang bersangkutan. |
| 7. Tim Pengantaran (Kurir) membuka menu Jadwal Pengiriman pada akunnya dan memilih pesanan yang ditugaskan kepadanya. | |
| | 8. Sistem menampilkan rincian lengkap pengantaran (alamat tujuan, nama penerima, kontak WhatsApp, jam kirim, dan rincian paket pesanan). |
| 9. Tim Pengantaran (Kurir) mengambil paket pesanan makanan dari dapur dan menekan tombol Mulai Antar / Dalam Perjalanan. | |
| | 10. Sistem mencatat waktu keberangkatan (`berangkat_pada`), memperbarui status pengiriman menjadi *Dalam Perjalanan*, dan mengirimkan notifikasi status pengantaran kepada konsumen. |
| 11. Tim Pengantaran (Kurir) melakukan perjalanan menuju alamat tujuan dan menyerahkan paket pesanan kepada konsumen/penerima. | |
| 12. Tim Pengantaran (Kurir) mengambil foto serah terima pesanan bersama penerima di lokasi acara, mengunggah file foto ke sistem, lalu menekan tombol Selesai Pengantaran. | |
| | 13. Sistem memvalidasi berkas foto bukti, mencatat waktu penerimaan (`diterima_pada`), mengubah status pengiriman menjadi *Terkirim / Diterima*, mengubah status pesanan menjadi *Selesai*, serta mengirimkan notifikasi sukses pengiriman kepada Pemilik dan Konsumen. |
| **Skenario Alternatif A1 – Pengiriman Gagal / Dijadwalkan Ulang (Konsumen Tidak Ditemukan / Kendala Lapangan)** | |
| **Aksi Aktor** | **Aksi Sistem** |
| 1. Tim Pengantaran (Kurir) tiba di lokasi namun konsumen tidak dapat dihubungi atau alamat tidak ditemukan. | |
| | 2. Tim Pengantaran (Kurir) memilih status *Gagal Dikirim* dan mengisi catatan kendala lapangan. |
| | 3. Sistem mengubah status pengiriman menjadi *Gagal Dikirim* dan mencatat riwayat kegagalan. |
| 4. Kasir/Admin menerima laporan kendala, menghubungi konsumen untuk koordinasi, lalu memperbarui jam/jadwal pengiriman baru pada sistem. | |
| | 5. Sistem memperbarui jadwal pengiriman dan mengembalikan status pengiriman menjadi *Dijadwalkan* kembali. |
| **Skenario Alternatif A2 – Pengalihan Penugasan Kurir oleh Admin** | |
| **Aksi Aktor** | **Aksi Sistem** |
| 1. Kasir/Admin mengetahui bahwa kurir yang ditugaskan sebelumnya berhalangan hadir. | |
| | 2. Kasir/Admin memilih nama kurir pengganti pada baris pesanan lalu menekan tombol Tugaskan Kurir. |
| | 3. Sistem memperbarui data kurir yang ditugaskan dan mengirimkan notifikasi tugas baru kepada kurir pengganti. |
| **Skenario Alternatif A3 – Konsumen Memantau Status Pengiriman Secara Mandiri (Tracking)** | |
| **Aksi Aktor** | **Aksi Sistem** |
| 1. Konsumen membuka tautan Lacak Pesanan pada halaman web. | |
| | 2. Sistem menampilkan status terkini pesanan (*Siap Dikirim*, *Dalam Perjalanan*, atau *Telah Diterima* beserta foto bukti serah terima jika sudah selesai). |
| **Kondisi Akhir** | Pesanan paket katering / nasi box telah berhasil diantar ke lokasi penerima, foto bukti serah terima tersimpan di sistem, status pengiriman menjadi *Terkirim / Diterima*, dan status pesanan menjadi *Selesai*. |

---

## USE CASE 4: PENGADAAN BAHAN BAKU KATERING

| Keterangan / Aksi Aktor | Deskripsi / Aksi Sistem |
| :--- | :--- |
| **Nama Use Case** | **Pengadaan Bahan Baku Katering** |
| **Nomor** | 4 |
| **Aktor** | Manajer / Admin / Pemilik, Pemasok (Supplier), Tim Dapur |
| **Deskripsi** | Proses pengadaan bahan baku khusus untuk pesanan katering (dan nasi box) berdasarkan kalkulasi kebutuhan resep menu pesanan dikurangi ketersediaan stok katering di gudang, penerbitan Purchase Order (PO) ke pemasok, hingga pencatatan penerimaan fisik bahan baku oleh Tim Dapur ke kartu stok katering. |
| **Kondisi Awal** | Pesanan katering berstatus aktif/terkonfirmasi, data resep menu paket katering telah terisi, dan data pemasok telah terdaftar. Manajer/Admin/Pemilik dan Tim Dapur telah login ke sistem. |
| **Skenario Utama** | |
| **Aksi Aktor** | **Aksi Sistem** |
| 1. Manajer/Admin membuka menu Buat Purchase Order (`/admin/pengadaan/purchase-order/create?tipe=Catering`). | |
| | 2. Sistem menampilkan formulir pembuatan Purchase Order pengadaan katering dan memuat daftar pesanan katering aktif. |
| 3. Manajer/Admin memilih nomor/kode pesanan katering yang akan diproses kebutuhan bahannya. | |
| | 4. Sistem secara otomatis menghitung total kebutuhan bahan baku berdasarkan resep menu paket katering, membandingkan dengan stok katering yang ada di gudang, dan menampilkan tabel daftar bahan baku yang kurang beserta rekomendasi jumlah yang harus dibeli. |
| 5. Manajer/Admin memilih nama Pemasok (Supplier), menentukan tanggal PO, memeriksa kuantitas dan perkiraan harga satuan, serta menambahkan catatan khusus. | |
| | 6. Sistem memvalidasi data dan menghitung estimasi total nilai tagihan Purchase Order (PO). |
| 7. Manajer/Admin menekan tombol Simpan & Terbitkan Purchase Order. | |
| | 8. Sistem menyimpan data PO dengan status *Dipesan (Menunggu Barang)*, menerbitkan Surat Pesanan (PO) resmi berformat PDF, dan memasukkan PO ke daftar tunggu penerimaan barang. |
| 9. Manajer/Admin mengunduh PDF Surat Pesanan dan mengirimkannya kepada pihak Pemasok. | |
| | 10. Sistem mencatat riwayat pemesanan ke pemasok pada log aktivitas pengadaan. |
| 11. Pemasok memproses pesanan dan mengirimkan bahan baku ke rumah makan disertai nota/surat jalan fisik. | |
| 12. Tim Dapur membuka menu Penerimaan Bahan Baku (`/admin/pengadaan/penerimaan/create/{po_id}`) pada nomor PO terkait. | |
| | 13. Sistem menampilkan formulir penerimaan barang dan daftar item bahan baku yang harus dicocokkan sesuai PO. |
| 14. Tim Dapur memeriksa kuantitas dan kualitas fisik barang yang datang, menginput jumlah yang diterima, mengisi nomor nota pemasok, lalu menekan tombol Simpan Penerimaan. | |
| | 15. Sistem memvalidasi input barang, menyimpan data penerimaan, menambah stok bahan baku secara otomatis ke kartu stok katering (`stok_bahan` dan `mutasi_stok`), serta memperbarui status Purchase Order menjadi *Diterima / Selesai*. |
| **Skenario Alternatif A1 – Bahan Baku Diterima Sebagian (Pengiriman Bertahap oleh Pemasok)** | |
| **Aksi Aktor** | **Aksi Sistem** |
| 1. Tim Dapur memeriksa kiriman pemasok dan mendapati sebagian bahan baku belum lengkap dikirim. | |
| | 2. Tim Dapur menginput jumlah fisik yang diterima saat ini (kurang dari jumlah pada PO) lalu menekan tombol Simpan Penerimaan. |
| | 3. Sistem memperbarui stok untuk barang yang telah diterima dan mengubah status PO menjadi *Diterima Sebagian*. |
| 4. Ketika sisa bahan baku dikirim oleh pemasok pada tahap berikutnya, Tim Dapur membuka kembali PO tersebut dan menginput penerimaan sisa barang. | |
| | 5. Sistem menambah stok sisa bahan baku dan memperbarui status PO menjadi *Diterima / Selesai*. |
| **Skenario Alternatif A2 – Menu Katering Belum Memiliki Resep Lengkap** | |
| **Aksi Aktor** | **Aksi Sistem** |
| 1. Manajer/Admin memilih pesanan katering yang mengandung variasi menu baru. | |
| | 2. Sistem mendeteksi bahwa terdapat menu yang belum memiliki formula resep dan menampilkan pesan peringatan *"Resep Belum Lengkap"*. |
| 3. Manajer/Admin membuka tautan data master resep dan melengkapi takaran bahan baku untuk menu tersebut. | |
| | 4. Sistem memperbarui kalkulasi otomatis kebutuhan bahan baku katering secara lengkap dan akurat. |
| **Skenario Alternatif A3 – Stok Bahan Baku di Gudang Katering Sudah Mencukupi** | |
| **Aksi Aktor** | **Aksi Sistem** |
| 1. Manajer/Admin memilih kode pesanan katering pada formulir pengadaan. | |
| | 2. Sistem mendeteksi bahwa seluruh kebutuhan bahan baku pesanan tersebut sudah tersedia cukup di stok gudang dan menampilkan tabel *"Semua Bahan Baku Cukup"*. |
| 3. Manajer/Admin tidak perlu menerbitkan PO baru dan langsung mengarahkan pesanan ke jadwal produksi dapur. | |
| | 4. Sistem menyimpan status kesiapan bahan baku untuk pesanan katering tersebut. |
| **Kondisi Akhir** | Seluruh bahan baku khusus pesanan katering telah berhasil diadakan, diverifikasi fisik, dicatat ke dalam stok katering rumah makan, dan siap digunakan oleh Tim Dapur untuk proses memasak. |

---

## USE CASE 5: PENGADAAN BAHAN BAKU HARIAN

| Keterangan / Aksi Aktor | Deskripsi / Aksi Sistem |
| :--- | :--- |
| **Nama Use Case** | **Pengadaan Bahan Baku Harian** |
| **Nomor** | 5 |
| **Aktor** | Tim Dapur / Manajer / Admin, Pemasok (Supplier), Pemilik |
| **Deskripsi** | Proses pengelolaan dan pengadaan kebutuhan bahan baku untuk kegiatan operasional harian restoran berdasarkan kondisi persediaan pada sistem. Ketika stok bahan baku operasional habis atau berada di bawah batas minimum, sistem secara otomatis menambahkan bahan baku tersebut ke dalam daftar kebutuhan pengadaan harian untuk diterbitkan Purchase Order (PO) ke pemasok hingga diterima oleh Tim Dapur. |
| **Kondisi Awal** | Data bahan baku operasional harian dan batas stok minimum telah tercatat di sistem. Stok beberapa bahan baku operasional harian telah berkurang akibat penjualan harian atau berada di bawah batas minimum (`stok_saat_ini <= stok_minimal`). Tim Dapur, Manajer/Admin, dan Pemilik telah login ke sistem. |
| **Skenario Utama** | |
| **Aksi Aktor** | **Aksi Sistem** |
| 1. Tim Dapur / Manajer membuka halaman Pengadaan Bahan Baku Harian (`/admin/pengadaan/purchase-order/create?tipe=Harian` atau `/admin/pengadaan/permintaan/harian`). | |
| | 2. Sistem memuat seluruh data bahan baku operasional, mendeteksi bahan baku yang berstatus kritis/menipis/habis (`stok_saat_ini <= stok_minimal`), menempatkan bahan baku tersebut di urutan teratas secara otomatis, mencentang barisnya secara default, dan mengisikan saran jumlah pengadaan yang harus dibeli. |
| 3. Tim Dapur / Manajer memeriksa item yang disarankan, menyesuaikan jumlah pengadaan bila diperlukan, serta menambahkan catatan pengadaan. | |
| | 4. Sistem memvalidasi kuantitas pengadaan dan menghitung total estimasi biaya pengadaan bahan baku harian. |
| 5. Manajer/Admin memilih nama Pemasok (Supplier) yang dituju, memilih tanggal PO, lalu menekan tombol Simpan & Terbitkan Purchase Order Harian. | |
| | 6. Sistem menyimpan data PO dengan status *Dipesan (Menunggu Barang)*, menerbitkan berkas resmi Surat Pesanan (PO) PDF, dan memasukkan PO ke daftar tunggu penerimaan barang. |
| 7. Manajer/Admin mengunduh PDF Surat Pesanan dan mengirimkannya ke pihak Pemasok. | |
| | 8. Sistem mencatat waktu dan status pengiriman pesanan ke pemasok pada log aktivitas. |
| 9. Pemasok memproses pesanan dan mengantarkan bahan baku ke restoran beserta nota/surat jalan fisik. | |
| 10. Tim Dapur membuka menu Penerimaan Bahan Baku (`/admin/pengadaan/penerimaan/create/{po_id}`) pada nomor PO terkait. | |
| | 11. Sistem menampilkan formulir penerimaan barang dan daftar bahan baku harian yang harus dicocokkan sesuai PO. |
| 12. Tim Dapur memeriksa fisik bahan baku (kuantitas & kesegaran/kondisi), menginput jumlah barang yang diterima, mengisi nomor nota/faktur pemasok, lalu menekan tombol Simpan Penerimaan. | |
| | 13. Sistem memvalidasi barang yang diterima, mencatat penerimaan, menambah kuantitas stok bahan baku secara otomatis ke kartu stok operasional harian (`stok_bahan` dan `mutasi_stok`), serta memperbarui status Purchase Order menjadi *Diterima / Selesai*. |
| **Skenario Alternatif A1 – Alur Pengajuan Permintaan Pengadaan oleh Tim Dapur** | |
| **Aksi Aktor** | **Aksi Sistem** |
| 1. Tim Dapur mengisi formulir Permintaan Pengadaan Harian (`/admin/pengadaan/permintaan/harian`) dan menekan tombol Ajukan Permintaan. | |
| | 2. Sistem menyimpan formulir permintaan dengan status *Menunggu Pembelian* dan mengirimkan notifikasi persetujuan ke Manajer/Admin. |
| 3. Manajer/Admin membuka daftar permintaan pengadaan, memilih pemasok yang sesuai, lalu menekan tombol Setujui & Buat PO. | |
| | 4. Sistem menerbitkan Surat Pesanan (PO) resmi ke pemasok berdasarkan permintaan yang disetujui. |
| **Skenario Alternatif A2 – Bahan Baku Harian Diterima Sebagian oleh Tim Dapur** | |
| **Aksi Aktor** | **Aksi Sistem** |
| 1. Tim Dapur mendapati beberapa bahan baku harian belum lengkap dikirim karena stok pemasok sedang kosong sebagian. | |
| | 2. Tim Dapur menginput jumlah fisik yang diterima saat ini lalu menekan tombol Simpan Penerimaan. |
| | 3. Sistem memperbarui stok untuk barang yang telah tiba dan mengubah status PO menjadi *Diterima Sebagian*. |
| 4. Saat sisa barang dikirim pada hari berikutnya, Tim Dapur membuka kembali PO tersebut dan menginput penerimaan sisa barang. | |
| | 5. Sistem memperbarui kartu stok untuk sisa barang dan memperbarui status PO menjadi *Diterima / Selesai*. |
| **Skenario Alternatif A3 – Penambahan Item Bahan Baku di Luar Rekomendasi Otomatis Sistem** | |
| **Aksi Aktor** | **Aksi Sistem** |
| 1. Tim Dapur ingin menambah stok bahan baku tertentu yang belum menyentuh batas minimum guna mengantisipasi lonjakan pengunjung akhir pekan. | |
| | 2. Tim Dapur mencentang baris bahan baku tersebut secara manual pada tabel dan memasukkan jumlah yang ingin ditambah. |
| | 3. Sistem menambahkan item tersebut ke daftar pengadaan dan memperbarui kalkulasi total biaya pengadaan. |
| **Kondisi Akhir** | Bahan baku operasional harian telah tiba di restoran, diverifikasi fisik oleh Tim Dapur, stok operasional harian di sistem bertambah secara otomatis, dan status pengadaan menjadi *Selesai*. |
