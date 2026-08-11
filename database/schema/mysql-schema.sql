/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `bahan_baku`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bahan_baku` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kategori_bahan_baku_id` bigint(20) unsigned DEFAULT NULL,
  `satuan_id` bigint(20) unsigned NOT NULL,
  `id_bahan_baku` varchar(30) NOT NULL,
  `nama_bahan` varchar(150) NOT NULL,
  `stok_minimal` decimal(15,3) NOT NULL DEFAULT 0.000,
  `harga_satuan` decimal(15,2) NOT NULL DEFAULT 0.00,
  `jenis_peruntukan` enum('Reguler','Catering','Semua') NOT NULL DEFAULT 'Semua',
  `status_aktif` tinyint(1) NOT NULL DEFAULT 1,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp(),
  `diperbarui_pada` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `bahan_baku_kode_bahan_unique` (`id_bahan_baku`),
  KEY `bahan_baku_kategori_bahan_baku_id_foreign` (`kategori_bahan_baku_id`),
  KEY `bahan_baku_satuan_id_foreign` (`satuan_id`),
  CONSTRAINT `bahan_baku_kategori_bahan_baku_id_foreign` FOREIGN KEY (`kategori_bahan_baku_id`) REFERENCES `kategori_bahan_baku` (`id`),
  CONSTRAINT `bahan_baku_satuan_id_foreign` FOREIGN KEY (`satuan_id`) REFERENCES `satuan` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=172 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `detail_penerimaan_bahan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `detail_penerimaan_bahan` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `penerimaan_bahan_id` bigint(20) unsigned NOT NULL,
  `detail_purchase_order_id` bigint(20) unsigned DEFAULT NULL,
  `bahan_baku_id` bigint(20) unsigned DEFAULT NULL,
  `jumlah_diterima` decimal(15,3) NOT NULL,
  `jumlah_diminta` decimal(15,3) DEFAULT NULL,
  `jumlah_kurang` decimal(15,3) DEFAULT NULL,
  `satuan_id` bigint(20) unsigned DEFAULT NULL,
  `kondisi` enum('Baik','Rusak','Kurang') DEFAULT 'Baik',
  `harga_satuan` decimal(15,2) NOT NULL,
  `nama_supplier` varchar(150) DEFAULT NULL,
  `tanggal_kedaluwarsa` date DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `detail_penerimaan_bahan_penerimaan_bahan_id_foreign` (`penerimaan_bahan_id`),
  KEY `detail_penerimaan_bahan_bahan_baku_id_foreign` (`bahan_baku_id`),
  KEY `detail_penerimaan_bahan_satuan_id_foreign` (`satuan_id`),
  KEY `detail_penerimaan_bahan_detail_purchase_order_id_foreign` (`detail_purchase_order_id`),
  CONSTRAINT `detail_penerimaan_bahan_bahan_baku_id_foreign` FOREIGN KEY (`bahan_baku_id`) REFERENCES `bahan_baku` (`id`),
  CONSTRAINT `detail_penerimaan_bahan_detail_purchase_order_id_foreign` FOREIGN KEY (`detail_purchase_order_id`) REFERENCES `detail_purchase_order` (`id`) ON DELETE SET NULL,
  CONSTRAINT `detail_penerimaan_bahan_penerimaan_bahan_id_foreign` FOREIGN KEY (`penerimaan_bahan_id`) REFERENCES `penerimaan_bahan` (`id`),
  CONSTRAINT `detail_penerimaan_bahan_satuan_id_foreign` FOREIGN KEY (`satuan_id`) REFERENCES `satuan` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `detail_pengadaan_bahan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `detail_pengadaan_bahan` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pengadaan_bahan_id` bigint(20) unsigned NOT NULL,
  `bahan_baku_id` bigint(20) unsigned NOT NULL,
  `jumlah_dipesan` decimal(15,3) NOT NULL,
  `stok_saat_ini` decimal(15,3) DEFAULT NULL,
  `stok_minimum` decimal(15,3) DEFAULT NULL,
  `jumlah_diterima` decimal(15,3) NOT NULL DEFAULT 0.000,
  `satuan_id` bigint(20) unsigned NOT NULL,
  `harga_satuan` decimal(15,2) NOT NULL,
  `subtotal` decimal(15,2) NOT NULL,
  `catatan` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `detail_pengadaan_bahan_pengadaan_bahan_id_bahan_baku_id_unique` (`pengadaan_bahan_id`,`bahan_baku_id`),
  KEY `detail_pengadaan_bahan_bahan_baku_id_foreign` (`bahan_baku_id`),
  KEY `detail_pengadaan_bahan_satuan_id_foreign` (`satuan_id`),
  CONSTRAINT `detail_pengadaan_bahan_bahan_baku_id_foreign` FOREIGN KEY (`bahan_baku_id`) REFERENCES `bahan_baku` (`id`),
  CONSTRAINT `detail_pengadaan_bahan_pengadaan_bahan_id_foreign` FOREIGN KEY (`pengadaan_bahan_id`) REFERENCES `pengadaan_bahan` (`id`),
  CONSTRAINT `detail_pengadaan_bahan_satuan_id_foreign` FOREIGN KEY (`satuan_id`) REFERENCES `satuan` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `detail_penyesuaian_stok`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `detail_penyesuaian_stok` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `penyesuaian_stok_id` bigint(20) unsigned NOT NULL,
  `bahan_baku_id` bigint(20) unsigned NOT NULL,
  `jumlah_sistem` decimal(15,3) NOT NULL,
  `jumlah_fisik` decimal(15,3) NOT NULL,
  `jumlah_selisih` decimal(15,3) NOT NULL,
  `jenis_persediaan` enum('harian','catering') NOT NULL DEFAULT 'harian',
  `satuan_id` bigint(20) unsigned NOT NULL,
  `catatan` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `detail_penyesuaian_stok_penyesuaian_stok_id_foreign` (`penyesuaian_stok_id`),
  KEY `detail_penyesuaian_stok_bahan_baku_id_foreign` (`bahan_baku_id`),
  KEY `detail_penyesuaian_stok_satuan_id_foreign` (`satuan_id`),
  CONSTRAINT `detail_penyesuaian_stok_bahan_baku_id_foreign` FOREIGN KEY (`bahan_baku_id`) REFERENCES `bahan_baku` (`id`),
  CONSTRAINT `detail_penyesuaian_stok_penyesuaian_stok_id_foreign` FOREIGN KEY (`penyesuaian_stok_id`) REFERENCES `penyesuaian_stok` (`id`),
  CONSTRAINT `detail_penyesuaian_stok_satuan_id_foreign` FOREIGN KEY (`satuan_id`) REFERENCES `satuan` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `detail_pesanan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `detail_pesanan` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pesanan_id` bigint(20) unsigned NOT NULL,
  `menu_id` bigint(20) unsigned NOT NULL,
  `jumlah` int(11) NOT NULL,
  `harga_satuan` decimal(15,2) NOT NULL,
  `jumlah_diskon` decimal(15,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(15,2) NOT NULL,
  `catatan` text DEFAULT NULL,
  `status_item` varchar(30) DEFAULT NULL,
  `stock_deducted_at` datetime DEFAULT NULL,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp(),
  `diperbarui_pada` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `detail_pesanan_pesanan_id_foreign` (`pesanan_id`),
  KEY `detail_pesanan_menu_id_foreign` (`menu_id`),
  CONSTRAINT `detail_pesanan_menu_id_foreign` FOREIGN KEY (`menu_id`) REFERENCES `menu` (`id`),
  CONSTRAINT `detail_pesanan_pesanan_id_foreign` FOREIGN KEY (`pesanan_id`) REFERENCES `pesanan` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `detail_purchase_order`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `detail_purchase_order` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `purchase_order_id` bigint(20) unsigned NOT NULL,
  `detail_pengadaan_bahan_id` bigint(20) unsigned NOT NULL,
  `bahan_baku_id` bigint(20) unsigned NOT NULL,
  `jumlah_dipesan` decimal(15,3) NOT NULL,
  `jumlah_diterima` decimal(15,3) NOT NULL DEFAULT 0.000,
  `satuan_id` bigint(20) unsigned DEFAULT NULL,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp(),
  `diperbarui_pada` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `detail_purchase_order_purchase_order_id_foreign` (`purchase_order_id`),
  KEY `detail_purchase_order_detail_pengadaan_bahan_id_foreign` (`detail_pengadaan_bahan_id`),
  KEY `detail_purchase_order_bahan_baku_id_foreign` (`bahan_baku_id`),
  KEY `detail_purchase_order_satuan_id_foreign` (`satuan_id`),
  CONSTRAINT `detail_purchase_order_bahan_baku_id_foreign` FOREIGN KEY (`bahan_baku_id`) REFERENCES `bahan_baku` (`id`) ON DELETE CASCADE,
  CONSTRAINT `detail_purchase_order_detail_pengadaan_bahan_id_foreign` FOREIGN KEY (`detail_pengadaan_bahan_id`) REFERENCES `detail_pengadaan_bahan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `detail_purchase_order_purchase_order_id_foreign` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_order` (`id`) ON DELETE CASCADE,
  CONSTRAINT `detail_purchase_order_satuan_id_foreign` FOREIGN KEY (`satuan_id`) REFERENCES `satuan` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `detail_tiket_dapur`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `detail_tiket_dapur` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tiket_dapur_id` bigint(20) unsigned NOT NULL,
  `detail_pesanan_id` bigint(20) unsigned NOT NULL,
  `jumlah` int(11) NOT NULL,
  `catatan` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `detail_tiket_dapur_tiket_dapur_id_foreign` (`tiket_dapur_id`),
  KEY `detail_tiket_dapur_detail_pesanan_id_foreign` (`detail_pesanan_id`),
  CONSTRAINT `detail_tiket_dapur_detail_pesanan_id_foreign` FOREIGN KEY (`detail_pesanan_id`) REFERENCES `detail_pesanan` (`id`),
  CONSTRAINT `detail_tiket_dapur_tiket_dapur_id_foreign` FOREIGN KEY (`tiket_dapur_id`) REFERENCES `tiket_dapur` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `item_paket`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `item_paket` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `menu_id` bigint(20) unsigned NOT NULL,
  `menu_id_terkait` bigint(20) unsigned DEFAULT NULL,
  `jumlah` decimal(10,2) NOT NULL DEFAULT 1.00,
  `satuan_sajian` varchar(50) DEFAULT NULL,
  `nama_item` varchar(255) NOT NULL,
  `tipe_item` enum('tetap','pilihan') NOT NULL DEFAULT 'tetap',
  `minimum_pilihan` int(11) NOT NULL DEFAULT 0,
  `maksimum_pilihan` int(11) NOT NULL DEFAULT 0,
  `urutan` int(11) NOT NULL DEFAULT 0,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp(),
  `diperbarui_pada` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `item_paket_menu_id_foreign` (`menu_id`),
  KEY `item_paket_menu_id_terkait_foreign` (`menu_id_terkait`),
  CONSTRAINT `item_paket_menu_id_foreign` FOREIGN KEY (`menu_id`) REFERENCES `menu` (`id`) ON DELETE CASCADE,
  CONSTRAINT `item_paket_menu_id_terkait_foreign` FOREIGN KEY (`menu_id_terkait`) REFERENCES `menu` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=123 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jadwal_pesanan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jadwal_pesanan` (
  `pesanan_id` bigint(20) unsigned NOT NULL,
  `tanggal_acara` date NOT NULL,
  `waktu_pengantaran` time DEFAULT NULL,
  `alamat_pengantaran` text NOT NULL,
  `nama_penerima` varchar(100) NOT NULL,
  `nomor_telepon_penerima` varchar(20) NOT NULL,
  `jumlah_tamu` int(11) DEFAULT NULL,
  `nama_acara` varchar(100) DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  PRIMARY KEY (`pesanan_id`),
  CONSTRAINT `jadwal_pesanan_pesanan_id_foreign` FOREIGN KEY (`pesanan_id`) REFERENCES `pesanan` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jenis_menu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jenis_menu` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kode_jenis` varchar(30) NOT NULL,
  `nama_jenis` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `jenis_menu_kode_jenis_unique` (`kode_jenis`),
  UNIQUE KEY `jenis_menu_nama_jenis_unique` (`nama_jenis`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jenis_mutasi_stok`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jenis_mutasi_stok` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kode_jenis` varchar(30) NOT NULL,
  `nama_jenis` varchar(50) NOT NULL,
  `arah_stok` enum('MASUK','KELUAR') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `jenis_mutasi_stok_kode_jenis_unique` (`kode_jenis`),
  UNIQUE KEY `jenis_mutasi_stok_nama_jenis_unique` (`nama_jenis`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jenis_pembayaran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jenis_pembayaran` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kode_jenis` varchar(30) NOT NULL,
  `nama_jenis` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `jenis_pembayaran_kode_jenis_unique` (`kode_jenis`),
  UNIQUE KEY `jenis_pembayaran_nama_jenis_unique` (`nama_jenis`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jenis_pesanan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jenis_pesanan` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kode_jenis` varchar(20) NOT NULL,
  `nama_jenis` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `jenis_pesanan_kode_jenis_unique` (`kode_jenis`),
  UNIQUE KEY `jenis_pesanan_nama_jenis_unique` (`nama_jenis`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `kategori_bahan_baku`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kategori_bahan_baku` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_kategori` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kategori_bahan_baku_nama_kategori_unique` (`nama_kategori`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `kategori_menu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kategori_menu` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_kategori` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `status_aktif` tinyint(1) NOT NULL DEFAULT 1,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp(),
  `diperbarui_pada` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `kategori_menu_nama_kategori_unique` (`nama_kategori`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ketentuan_paket`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ketentuan_paket` (
  `menu_id` bigint(20) unsigned NOT NULL,
  `minimal_pemesanan` int(11) NOT NULL DEFAULT 1,
  `minimal_hari_pemesanan` int(11) NOT NULL DEFAULT 0,
  `persentase_uang_muka` decimal(5,2) NOT NULL DEFAULT 0.00,
  `batas_konfirmasi_hari` int(11) NOT NULL DEFAULT 0,
  `keterangan` text DEFAULT NULL,
  PRIMARY KEY (`menu_id`),
  CONSTRAINT `ketentuan_paket_menu_id_foreign` FOREIGN KEY (`menu_id`) REFERENCES `menu` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `meja`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `meja` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kode_meja` varchar(255) DEFAULT NULL,
  `nomor_meja` varchar(20) NOT NULL,
  `kapasitas` int(11) NOT NULL,
  `area` varchar(255) NOT NULL DEFAULT 'Indoor',
  `qr_token` varchar(64) DEFAULT NULL,
  `status_meja_id` bigint(20) unsigned NOT NULL,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp(),
  `diperbarui_pada` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `meja_nomor_meja_unique` (`nomor_meja`),
  UNIQUE KEY `meja_kode_meja_unique` (`kode_meja`),
  UNIQUE KEY `meja_qr_token_unique` (`qr_token`),
  KEY `meja_status_meja_id_foreign` (`status_meja_id`),
  CONSTRAINT `meja_status_meja_id_foreign` FOREIGN KEY (`status_meja_id`) REFERENCES `status_meja` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `menu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `menu` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `jenis_menu_id` bigint(20) unsigned NOT NULL,
  `kategori_menu_id` bigint(20) unsigned DEFAULT NULL,
  `id_menu` varchar(30) NOT NULL,
  `nama_menu` varchar(150) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `harga_jual` decimal(15,2) NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `status_aktif` tinyint(1) NOT NULL DEFAULT 1,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp(),
  `diperbarui_pada` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `menu_kode_menu_unique` (`id_menu`),
  KEY `menu_jenis_menu_id_foreign` (`jenis_menu_id`),
  KEY `menu_kategori_menu_id_foreign` (`kategori_menu_id`),
  CONSTRAINT `menu_jenis_menu_id_foreign` FOREIGN KEY (`jenis_menu_id`) REFERENCES `jenis_menu` (`id`),
  CONSTRAINT `menu_kategori_menu_id_foreign` FOREIGN KEY (`kategori_menu_id`) REFERENCES `kategori_menu` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=112 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `metode_pembayaran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `metode_pembayaran` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kode_metode` varchar(30) NOT NULL,
  `nama_metode` varchar(50) NOT NULL,
  `status_aktif` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `metode_pembayaran_kode_metode_unique` (`kode_metode`),
  UNIQUE KEY `metode_pembayaran_nama_metode_unique` (`nama_metode`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mutasi_stok`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mutasi_stok` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `bahan_baku_id` bigint(20) unsigned NOT NULL,
  `jenis_mutasi_stok_id` bigint(20) unsigned NOT NULL,
  `jumlah` decimal(15,3) NOT NULL,
  `stok_sebelum` decimal(15,3) DEFAULT NULL,
  `stok_sesudah` decimal(15,3) DEFAULT NULL,
  `satuan_id` bigint(20) unsigned NOT NULL,
  `tanggal_mutasi` datetime NOT NULL,
  `jenis_persediaan` enum('harian','catering') NOT NULL DEFAULT 'harian',
  `jenis_stok` enum('OPERASIONAL','CATERING') NOT NULL DEFAULT 'OPERASIONAL',
  `referensi_id` varchar(50) DEFAULT NULL,
  `detail_pesanan_id` bigint(20) unsigned DEFAULT NULL,
  `detail_penerimaan_bahan_id` bigint(20) unsigned DEFAULT NULL,
  `detail_penyesuaian_stok_id` bigint(20) unsigned DEFAULT NULL,
  `dibuat_oleh` bigint(20) unsigned NOT NULL,
  `catatan` text DEFAULT NULL,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `mutasi_stok_bahan_baku_id_foreign` (`bahan_baku_id`),
  KEY `mutasi_stok_jenis_mutasi_stok_id_foreign` (`jenis_mutasi_stok_id`),
  KEY `mutasi_stok_satuan_id_foreign` (`satuan_id`),
  KEY `mutasi_stok_detail_pesanan_id_foreign` (`detail_pesanan_id`),
  KEY `mutasi_stok_detail_penerimaan_bahan_id_foreign` (`detail_penerimaan_bahan_id`),
  KEY `mutasi_stok_dibuat_oleh_foreign` (`dibuat_oleh`),
  KEY `mutasi_stok_detail_penyesuaian_stok_id_foreign` (`detail_penyesuaian_stok_id`),
  CONSTRAINT `mutasi_stok_bahan_baku_id_foreign` FOREIGN KEY (`bahan_baku_id`) REFERENCES `bahan_baku` (`id`),
  CONSTRAINT `mutasi_stok_detail_penerimaan_bahan_id_foreign` FOREIGN KEY (`detail_penerimaan_bahan_id`) REFERENCES `detail_penerimaan_bahan` (`id`),
  CONSTRAINT `mutasi_stok_detail_penyesuaian_stok_id_foreign` FOREIGN KEY (`detail_penyesuaian_stok_id`) REFERENCES `detail_penyesuaian_stok` (`id`),
  CONSTRAINT `mutasi_stok_detail_pesanan_id_foreign` FOREIGN KEY (`detail_pesanan_id`) REFERENCES `detail_pesanan` (`id`),
  CONSTRAINT `mutasi_stok_dibuat_oleh_foreign` FOREIGN KEY (`dibuat_oleh`) REFERENCES `pengguna` (`id`),
  CONSTRAINT `mutasi_stok_jenis_mutasi_stok_id_foreign` FOREIGN KEY (`jenis_mutasi_stok_id`) REFERENCES `jenis_mutasi_stok` (`id`),
  CONSTRAINT `mutasi_stok_satuan_id_foreign` FOREIGN KEY (`satuan_id`) REFERENCES `satuan` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `notifikasi_stoks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifikasi_stoks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `bahan_baku_id` bigint(20) unsigned NOT NULL,
  `jenis` varchar(20) NOT NULL DEFAULT 'menipis',
  `jenis_persediaan` enum('harian','catering') NOT NULL DEFAULT 'harian',
  `stok_saat_ini` decimal(10,3) NOT NULL DEFAULT 0.000,
  `stok_minimal` decimal(10,3) NOT NULL DEFAULT 0.000,
  `pesan` text DEFAULT NULL,
  `dibaca` tinyint(1) NOT NULL DEFAULT 0,
  `dibaca_pada` timestamp NULL DEFAULT NULL,
  `dibaca_oleh` bigint(20) unsigned DEFAULT NULL,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp(),
  `diperbarui_pada` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `notifikasi_stoks_bahan_baku_id_foreign` (`bahan_baku_id`),
  KEY `notifikasi_stoks_jenis_index` (`jenis`),
  KEY `notifikasi_stoks_dibaca_index` (`dibaca`),
  CONSTRAINT `notifikasi_stoks_bahan_baku_id_foreign` FOREIGN KEY (`bahan_baku_id`) REFERENCES `bahan_baku` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `payment_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment_sessions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `session_token` varchar(255) NOT NULL,
  `pesanan_id` bigint(20) unsigned NOT NULL,
  `payment_type` enum('dp','pelunasan') NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('active','completed','expired','cancelled') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_sessions_session_token_unique` (`session_token`),
  KEY `payment_sessions_pesanan_id_foreign` (`pesanan_id`),
  KEY `payment_sessions_payment_type_index` (`payment_type`),
  KEY `payment_sessions_expires_at_index` (`expires_at`),
  KEY `payment_sessions_status_index` (`status`),
  CONSTRAINT `payment_sessions_pesanan_id_foreign` FOREIGN KEY (`pesanan_id`) REFERENCES `pesanan` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `payment_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment_transactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` varchar(100) NOT NULL,
  `din_number` varchar(100) DEFAULT NULL,
  `gross_amount` bigint(20) unsigned NOT NULL DEFAULT 0,
  `payment_type` varchar(50) NOT NULL DEFAULT 'qris',
  `transaction_status` varchar(50) NOT NULL DEFAULT 'pending',
  `qr_url` text DEFAULT NULL,
  `raw_response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`raw_response`)),
  `signature_verified` tinyint(1) NOT NULL DEFAULT 0,
  `processed_at` timestamp NULL DEFAULT NULL,
  `webhook_received_at` timestamp NULL DEFAULT NULL,
  `retry_count` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_transactions_order_id_unique` (`order_id`),
  KEY `idx_din_number` (`din_number`),
  KEY `idx_transaction_status` (`transaction_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pelanggan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pelanggan` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `nomor_telepon` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp(),
  `diperbarui_pada` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `kata_sandi` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pemasok`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pemasok` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kode_pemasok` varchar(30) NOT NULL,
  `nama_pemasok` varchar(150) NOT NULL,
  `nomor_telepon` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `nama_kontak` varchar(100) DEFAULT NULL,
  `status_aktif` tinyint(1) NOT NULL DEFAULT 1,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp(),
  `diperbarui_pada` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `pemasok_kode_pemasok_unique` (`kode_pemasok`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pembayaran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pembayaran` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kode_pembayaran` varchar(50) NOT NULL,
  `pesanan_id` bigint(20) unsigned NOT NULL,
  `metode_pembayaran` varchar(255) DEFAULT NULL,
  `jenis_pembayaran` varchar(255) DEFAULT NULL,
  `jumlah_tagihan` decimal(12,2) DEFAULT NULL,
  `jumlah_dibayar` decimal(15,2) NOT NULL,
  `bukti_pembayaran` varchar(255) DEFAULT NULL,
  `status_verifikasi` varchar(255) DEFAULT NULL,
  `tanggal_pembayaran` datetime DEFAULT NULL,
  `diverifikasi_oleh` bigint(20) unsigned DEFAULT NULL,
  `tanggal_verifikasi` datetime DEFAULT NULL,
  `catatan_verifikasi` text DEFAULT NULL,
  `upload_progress` tinyint(4) NOT NULL DEFAULT 0,
  `file_hash` varchar(64) DEFAULT NULL,
  `verification_notes` text DEFAULT NULL,
  `payment_method_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payment_method_details`)),
  `catatan` text DEFAULT NULL,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp(),
  `diperbarui_pada` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `pembayaran_nomor_pembayaran_unique` (`kode_pembayaran`),
  KEY `pembayaran_pesanan_id_foreign` (`pesanan_id`),
  KEY `pembayaran_file_hash_index` (`file_hash`),
  CONSTRAINT `pembayaran_pesanan_id_foreign` FOREIGN KEY (`pesanan_id`) REFERENCES `pesanan` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `penerimaan_bahan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `penerimaan_bahan` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nomor_penerimaan` varchar(50) NOT NULL,
  `purchase_order_id` bigint(20) unsigned DEFAULT NULL,
  `kode_permintaan` varchar(30) DEFAULT NULL,
  `diterima_oleh` bigint(20) unsigned NOT NULL,
  `diverifikasi_oleh` bigint(20) unsigned DEFAULT NULL,
  `waktu_verifikasi` timestamp NULL DEFAULT NULL,
  `diterima_pada` datetime NOT NULL,
  `nomor_nota` varchar(100) DEFAULT NULL,
  `supplier` varchar(150) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'menunggu_penerimaan',
  `berkas_nota` varchar(255) DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `penerimaan_bahan_nomor_penerimaan_unique` (`nomor_penerimaan`),
  KEY `penerimaan_bahan_diterima_oleh_foreign` (`diterima_oleh`),
  KEY `penerimaan_bahan_diverifikasi_oleh_foreign` (`diverifikasi_oleh`),
  KEY `penerimaan_bahan_purchase_order_id_foreign` (`purchase_order_id`),
  CONSTRAINT `penerimaan_bahan_diterima_oleh_foreign` FOREIGN KEY (`diterima_oleh`) REFERENCES `pengguna` (`id`),
  CONSTRAINT `penerimaan_bahan_diverifikasi_oleh_foreign` FOREIGN KEY (`diverifikasi_oleh`) REFERENCES `pengguna` (`id`),
  CONSTRAINT `penerimaan_bahan_purchase_order_id_foreign` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_order` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pengadaan_bahan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pengadaan_bahan` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_pengadaan` varchar(50) NOT NULL,
  `pemasok_id` bigint(20) unsigned DEFAULT NULL,
  `pesanan_id` bigint(20) unsigned DEFAULT NULL,
  `diajukan_oleh` bigint(20) unsigned NOT NULL,
  `disetujui_oleh` bigint(20) unsigned DEFAULT NULL,
  `status_pengadaan_id` bigint(20) unsigned NOT NULL,
  `jenis_pengadaan` varchar(30) NOT NULL,
  `tanggal_pengadaan` date NOT NULL,
  `total_pengadaan` decimal(15,2) NOT NULL DEFAULT 0.00,
  `catatan` text DEFAULT NULL,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp(),
  `diperbarui_pada` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `pengadaan_bahan_nomor_pengadaan_unique` (`id_pengadaan`),
  KEY `pengadaan_bahan_pemasok_id_foreign` (`pemasok_id`),
  KEY `pengadaan_bahan_pesanan_id_foreign` (`pesanan_id`),
  KEY `pengadaan_bahan_diajukan_oleh_foreign` (`diajukan_oleh`),
  KEY `pengadaan_bahan_disetujui_oleh_foreign` (`disetujui_oleh`),
  KEY `pengadaan_bahan_status_pengadaan_id_foreign` (`status_pengadaan_id`),
  CONSTRAINT `pengadaan_bahan_diajukan_oleh_foreign` FOREIGN KEY (`diajukan_oleh`) REFERENCES `pengguna` (`id`),
  CONSTRAINT `pengadaan_bahan_disetujui_oleh_foreign` FOREIGN KEY (`disetujui_oleh`) REFERENCES `pengguna` (`id`),
  CONSTRAINT `pengadaan_bahan_pemasok_id_foreign` FOREIGN KEY (`pemasok_id`) REFERENCES `pemasok` (`id`),
  CONSTRAINT `pengadaan_bahan_pesanan_id_foreign` FOREIGN KEY (`pesanan_id`) REFERENCES `pesanan` (`id`),
  CONSTRAINT `pengadaan_bahan_status_pengadaan_id_foreign` FOREIGN KEY (`status_pengadaan_id`) REFERENCES `status_pengadaan` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pengantaran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pengantaran` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nomor_pengantaran` varchar(50) NOT NULL,
  `pesanan_id` bigint(20) unsigned NOT NULL,
  `status_pengantaran_id` bigint(20) unsigned NOT NULL,
  `ditugaskan_kepada` bigint(20) unsigned DEFAULT NULL,
  `jadwal_pengantaran` datetime NOT NULL,
  `berangkat_pada` datetime DEFAULT NULL,
  `diterima_pada` datetime DEFAULT NULL,
  `nama_penerima` varchar(100) NOT NULL,
  `nomor_telepon_penerima` varchar(20) NOT NULL,
  `alamat_pengantaran` text NOT NULL,
  `jarak_pengantaran` decimal(8,2) DEFAULT NULL COMMENT 'Jarak dalam km',
  `catatan` text DEFAULT NULL,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp(),
  `diperbarui_pada` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `biaya_pengantaran` decimal(15,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pengantaran_nomor_pengantaran_unique` (`nomor_pengantaran`),
  UNIQUE KEY `pengantaran_pesanan_id_unique` (`pesanan_id`),
  KEY `pengantaran_status_pengantaran_id_foreign` (`status_pengantaran_id`),
  KEY `pengantaran_ditugaskan_kepada_foreign` (`ditugaskan_kepada`),
  CONSTRAINT `pengantaran_ditugaskan_kepada_foreign` FOREIGN KEY (`ditugaskan_kepada`) REFERENCES `pengguna` (`id`),
  CONSTRAINT `pengantaran_pesanan_id_foreign` FOREIGN KEY (`pesanan_id`) REFERENCES `pesanan` (`id`),
  CONSTRAINT `pengantaran_status_pengantaran_id_foreign` FOREIGN KEY (`status_pengantaran_id`) REFERENCES `status_pengantaran` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pengguna`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pengguna` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_pengguna` varchar(20) DEFAULT NULL,
  `peran_id` bigint(20) unsigned NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `nomor_telepon` varchar(20) DEFAULT NULL,
  `kata_sandi` varchar(255) NOT NULL,
  `status_aktif` tinyint(1) NOT NULL DEFAULT 1,
  `terakhir_masuk` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp(),
  `diperbarui_pada` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `pengguna_email_unique` (`email`),
  UNIQUE KEY `pengguna_id_pengguna_unique` (`id_pengguna`),
  KEY `pengguna_peran_id_foreign` (`peran_id`),
  CONSTRAINT `pengguna_peran_id_foreign` FOREIGN KEY (`peran_id`) REFERENCES `peran` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `penyesuaian_stok`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `penyesuaian_stok` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nomor_penyesuaian` varchar(50) NOT NULL,
  `tanggal_penyesuaian` datetime NOT NULL,
  `dibuat_oleh` bigint(20) unsigned NOT NULL,
  `disetujui_oleh` bigint(20) unsigned DEFAULT NULL,
  `alasan` text NOT NULL,
  `status_penyesuaian` varchar(30) NOT NULL,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp(),
  `diperbarui_pada` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `penyesuaian_stok_nomor_penyesuaian_unique` (`nomor_penyesuaian`),
  KEY `penyesuaian_stok_dibuat_oleh_foreign` (`dibuat_oleh`),
  KEY `penyesuaian_stok_disetujui_oleh_foreign` (`disetujui_oleh`),
  CONSTRAINT `penyesuaian_stok_dibuat_oleh_foreign` FOREIGN KEY (`dibuat_oleh`) REFERENCES `pengguna` (`id`),
  CONSTRAINT `penyesuaian_stok_disetujui_oleh_foreign` FOREIGN KEY (`disetujui_oleh`) REFERENCES `pengguna` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `peran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `peran` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_peran` varchar(50) NOT NULL,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp(),
  `diperbarui_pada` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `peran_nama_peran_unique` (`nama_peran`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pesanan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pesanan` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_pesanan` varchar(50) NOT NULL,
  `jenis_pesanan_id` bigint(20) unsigned NOT NULL,
  `pelanggan_id` bigint(20) unsigned DEFAULT NULL,
  `meja_id` bigint(20) unsigned DEFAULT NULL,
  `pelayan_id` bigint(20) unsigned DEFAULT NULL,
  `kasir_id` bigint(20) unsigned DEFAULT NULL,
  `status_pesanan_id` bigint(20) unsigned NOT NULL,
  `tanggal_pesanan` datetime NOT NULL,
  `jumlah_sebelum_potongan` decimal(15,2) NOT NULL DEFAULT 0.00,
  `jumlah_diskon` decimal(15,2) NOT NULL DEFAULT 0.00,
  `jumlah_pajak` decimal(15,2) NOT NULL DEFAULT 0.00,
  `biaya_pelayanan` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_tagihan` decimal(15,2) NOT NULL DEFAULT 0.00,
  `catatan` text DEFAULT NULL,
  `metode_pengiriman` enum('ambil_sendiri','diantar') DEFAULT NULL,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp(),
  `diperbarui_pada` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `alasan_batal` text DEFAULT NULL,
  `status_pembayaran_id` bigint(20) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pesanan_nomor_pesanan_unique` (`id_pesanan`),
  KEY `pesanan_jenis_pesanan_id_foreign` (`jenis_pesanan_id`),
  KEY `pesanan_pelanggan_id_foreign` (`pelanggan_id`),
  KEY `pesanan_meja_id_foreign` (`meja_id`),
  KEY `pesanan_pelayan_id_foreign` (`pelayan_id`),
  KEY `pesanan_kasir_id_foreign` (`kasir_id`),
  KEY `pesanan_status_pesanan_id_foreign` (`status_pesanan_id`),
  KEY `pesanan_status_pembayaran_id_foreign` (`status_pembayaran_id`),
  CONSTRAINT `pesanan_jenis_pesanan_id_foreign` FOREIGN KEY (`jenis_pesanan_id`) REFERENCES `jenis_pesanan` (`id`),
  CONSTRAINT `pesanan_kasir_id_foreign` FOREIGN KEY (`kasir_id`) REFERENCES `pengguna` (`id`),
  CONSTRAINT `pesanan_meja_id_foreign` FOREIGN KEY (`meja_id`) REFERENCES `meja` (`id`),
  CONSTRAINT `pesanan_pelanggan_id_foreign` FOREIGN KEY (`pelanggan_id`) REFERENCES `pelanggan` (`id`),
  CONSTRAINT `pesanan_pelayan_id_foreign` FOREIGN KEY (`pelayan_id`) REFERENCES `pengguna` (`id`),
  CONSTRAINT `pesanan_status_pembayaran_id_foreign` FOREIGN KEY (`status_pembayaran_id`) REFERENCES `status_pembayaran` (`id`),
  CONSTRAINT `pesanan_status_pesanan_id_foreign` FOREIGN KEY (`status_pesanan_id`) REFERENCES `status_pesanan` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pilihan_item_paket`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pilihan_item_paket` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `item_paket_id` bigint(20) unsigned NOT NULL,
  `nama_pilihan` varchar(255) NOT NULL,
  `menu_id` bigint(20) unsigned DEFAULT NULL,
  `jumlah` decimal(10,2) NOT NULL DEFAULT 1.00,
  `satuan_sajian` varchar(50) DEFAULT NULL,
  `urutan` int(11) NOT NULL DEFAULT 0,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp(),
  `diperbarui_pada` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `pilihan_item_paket_item_paket_id_foreign` (`item_paket_id`),
  KEY `pilihan_item_paket_menu_id_foreign` (`menu_id`),
  CONSTRAINT `pilihan_item_paket_item_paket_id_foreign` FOREIGN KEY (`item_paket_id`) REFERENCES `item_paket` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pilihan_item_paket_menu_id_foreign` FOREIGN KEY (`menu_id`) REFERENCES `menu` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=195 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pilihan_pesanan_catering`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pilihan_pesanan_catering` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `detail_pesanan_id` bigint(20) unsigned NOT NULL,
  `item_paket_id` bigint(20) unsigned NOT NULL,
  `pilihan_item_paket_id` bigint(20) unsigned NOT NULL,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp(),
  `diperbarui_pada` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `pilihan_pesanan_catering_detail_pesanan_id_foreign` (`detail_pesanan_id`),
  KEY `pilihan_pesanan_catering_item_paket_id_foreign` (`item_paket_id`),
  KEY `pilihan_pesanan_catering_pilihan_item_paket_id_foreign` (`pilihan_item_paket_id`),
  CONSTRAINT `pilihan_pesanan_catering_detail_pesanan_id_foreign` FOREIGN KEY (`detail_pesanan_id`) REFERENCES `detail_pesanan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pilihan_pesanan_catering_item_paket_id_foreign` FOREIGN KEY (`item_paket_id`) REFERENCES `item_paket` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pilihan_pesanan_catering_pilihan_item_paket_id_foreign` FOREIGN KEY (`pilihan_item_paket_id`) REFERENCES `pilihan_item_paket` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `purchase_order`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchase_order` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nomor_po` varchar(30) NOT NULL,
  `pengadaan_bahan_id` bigint(20) unsigned NOT NULL,
  `supplier` varchar(150) DEFAULT NULL,
  `tanggal_po` date DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'menunggu_barang',
  `catatan` text DEFAULT NULL,
  `dibuat_oleh` bigint(20) unsigned DEFAULT NULL,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp(),
  `diperbarui_pada` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `purchase_order_nomor_po_unique` (`nomor_po`),
  KEY `purchase_order_pengadaan_bahan_id_foreign` (`pengadaan_bahan_id`),
  KEY `purchase_order_dibuat_oleh_foreign` (`dibuat_oleh`),
  CONSTRAINT `purchase_order_dibuat_oleh_foreign` FOREIGN KEY (`dibuat_oleh`) REFERENCES `pengguna` (`id`),
  CONSTRAINT `purchase_order_pengadaan_bahan_id_foreign` FOREIGN KEY (`pengadaan_bahan_id`) REFERENCES `pengadaan_bahan` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `resep_menu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `resep_menu` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `menu_id` bigint(20) unsigned NOT NULL,
  `bahan_baku_id` bigint(20) unsigned NOT NULL,
  `jumlah` decimal(15,3) NOT NULL,
  `satuan_id` bigint(20) unsigned NOT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `dikonfirmasi` tinyint(1) NOT NULL DEFAULT 0,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp(),
  `diperbarui_pada` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `resep_menu_menu_id_bahan_baku_id_unique` (`menu_id`,`bahan_baku_id`),
  UNIQUE KEY `resep_menu_menu_bahan_unique` (`menu_id`,`bahan_baku_id`),
  KEY `resep_menu_bahan_baku_id_foreign` (`bahan_baku_id`),
  KEY `resep_menu_satuan_id_foreign` (`satuan_id`),
  CONSTRAINT `resep_menu_bahan_baku_id_foreign` FOREIGN KEY (`bahan_baku_id`) REFERENCES `bahan_baku` (`id`),
  CONSTRAINT `resep_menu_menu_id_foreign` FOREIGN KEY (`menu_id`) REFERENCES `menu` (`id`),
  CONSTRAINT `resep_menu_satuan_id_foreign` FOREIGN KEY (`satuan_id`) REFERENCES `satuan` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=682 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `satuan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `satuan` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_satuan` varchar(50) NOT NULL,
  `singkatan` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `status_meja`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `status_meja` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kode_status` varchar(30) NOT NULL,
  `nama_status` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `status_meja_kode_status_unique` (`kode_status`),
  UNIQUE KEY `status_meja_nama_status_unique` (`nama_status`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `status_pembayaran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `status_pembayaran` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kode_status` varchar(30) NOT NULL,
  `nama_status` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `status_pembayaran_kode_status_unique` (`kode_status`),
  UNIQUE KEY `status_pembayaran_nama_status_unique` (`nama_status`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `status_pengadaan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `status_pengadaan` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kode_status` varchar(30) NOT NULL,
  `nama_status` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `status_pengadaan_kode_status_unique` (`kode_status`),
  UNIQUE KEY `status_pengadaan_nama_status_unique` (`nama_status`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `status_pengantaran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `status_pengantaran` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kode_status` varchar(30) NOT NULL,
  `nama_status` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `status_pengantaran_kode_status_unique` (`kode_status`),
  UNIQUE KEY `status_pengantaran_nama_status_unique` (`nama_status`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `status_pesanan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `status_pesanan` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kode_status` varchar(30) NOT NULL,
  `nama_status` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `status_pesanan_kode_status_unique` (`kode_status`),
  UNIQUE KEY `status_pesanan_nama_status_unique` (`nama_status`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `status_tiket_dapur`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `status_tiket_dapur` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kode_status` varchar(30) NOT NULL,
  `nama_status` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `status_tiket_dapur_kode_status_unique` (`kode_status`),
  UNIQUE KEY `status_tiket_dapur_nama_status_unique` (`nama_status`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `stok_bahan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stok_bahan` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `bahan_baku_id` bigint(20) unsigned NOT NULL,
  `jenis_persediaan` enum('harian','catering') NOT NULL DEFAULT 'harian',
  `jumlah_stok` decimal(15,3) NOT NULL DEFAULT 0.000,
  `stok_minimal` decimal(15,3) NOT NULL DEFAULT 0.000,
  `terakhir_diperbarui` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stok_bahan_bahan_baku_id_jenis_persediaan_unique` (`bahan_baku_id`,`jenis_persediaan`),
  CONSTRAINT `stok_bahan_bahan_baku_id_foreign` FOREIGN KEY (`bahan_baku_id`) REFERENCES `bahan_baku` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=343 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `stok_bahan_baku`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stok_bahan_baku` (
  `bahan_baku_id` bigint(20) unsigned NOT NULL,
  `jumlah_stok` decimal(15,3) NOT NULL DEFAULT 0.000,
  `terakhir_diperbarui` datetime NOT NULL,
  PRIMARY KEY (`bahan_baku_id`),
  CONSTRAINT `stok_bahan_baku_bahan_baku_id_foreign` FOREIGN KEY (`bahan_baku_id`) REFERENCES `bahan_baku` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `stok_catering`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stok_catering` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pesanan_id` bigint(20) unsigned NOT NULL,
  `bahan_baku_id` bigint(20) unsigned NOT NULL,
  `kebutuhan` decimal(15,3) NOT NULL DEFAULT 0.000,
  `diterima` decimal(15,3) NOT NULL DEFAULT 0.000,
  `digunakan` decimal(15,3) NOT NULL DEFAULT 0.000,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp(),
  `diperbarui_pada` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `stok_catering_pesanan_id_bahan_baku_id_unique` (`pesanan_id`,`bahan_baku_id`),
  KEY `stok_catering_bahan_baku_id_foreign` (`bahan_baku_id`),
  CONSTRAINT `stok_catering_bahan_baku_id_foreign` FOREIGN KEY (`bahan_baku_id`) REFERENCES `bahan_baku` (`id`) ON DELETE CASCADE,
  CONSTRAINT `stok_catering_pesanan_id_foreign` FOREIGN KEY (`pesanan_id`) REFERENCES `pesanan` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tiket_dapur`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tiket_dapur` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nomor_tiket` varchar(50) NOT NULL,
  `pesanan_id` bigint(20) unsigned NOT NULL,
  `meja_id` bigint(20) unsigned DEFAULT NULL,
  `nomor_meja` varchar(50) DEFAULT NULL,
  `nama_konsumen` varchar(150) DEFAULT NULL,
  `jumlah_tamu` int(11) NOT NULL DEFAULT 1,
  `sumber_pesanan` varchar(50) DEFAULT NULL,
  `status_tiket_dapur_id` bigint(20) unsigned NOT NULL,
  `dicetak_pada` datetime DEFAULT NULL,
  `diproses_pada` datetime DEFAULT NULL,
  `siap_pada` datetime DEFAULT NULL,
  `diproses_oleh` bigint(20) unsigned DEFAULT NULL,
  `selesai_pada` datetime DEFAULT NULL,
  `diselesaikan_oleh` bigint(20) unsigned DEFAULT NULL,
  `diselesaikan_pada` datetime DEFAULT NULL,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp(),
  `diperbarui_pada` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `tiket_dapur_nomor_tiket_unique` (`nomor_tiket`),
  KEY `tiket_dapur_pesanan_id_foreign` (`pesanan_id`),
  KEY `tiket_dapur_status_tiket_dapur_id_foreign` (`status_tiket_dapur_id`),
  KEY `tiket_dapur_meja_id_foreign` (`meja_id`),
  KEY `tiket_dapur_diproses_oleh_foreign` (`diproses_oleh`),
  KEY `tiket_dapur_diselesaikan_oleh_foreign` (`diselesaikan_oleh`),
  CONSTRAINT `tiket_dapur_diproses_oleh_foreign` FOREIGN KEY (`diproses_oleh`) REFERENCES `pengguna` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tiket_dapur_diselesaikan_oleh_foreign` FOREIGN KEY (`diselesaikan_oleh`) REFERENCES `pengguna` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tiket_dapur_meja_id_foreign` FOREIGN KEY (`meja_id`) REFERENCES `meja` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tiket_dapur_pesanan_id_foreign` FOREIGN KEY (`pesanan_id`) REFERENCES `pesanan` (`id`),
  CONSTRAINT `tiket_dapur_status_tiket_dapur_id_foreign` FOREIGN KEY (`status_tiket_dapur_id`) REFERENCES `status_tiket_dapur` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
