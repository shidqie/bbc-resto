<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. peran
        Schema::create('peran', function (Blueprint $table) {
            $table->id();
            $table->string('nama_peran', 50)->unique();
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('diperbarui_pada')->useCurrent()->useCurrentOnUpdate();
        });

        // 2. pengguna
        Schema::create('pengguna', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peran_id')->constrained('peran');
            $table->string('nama', 100);
            $table->string('email', 150)->unique();
            $table->string('nomor_telepon', 20)->nullable();
            $table->string('kata_sandi', 255);
            $table->boolean('status_aktif')->default(true);
            $table->string('remember_token', 100)->nullable();
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('diperbarui_pada')->useCurrent()->useCurrentOnUpdate();
        });

        // 3. pelanggan
        Schema::create('pelanggan', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('nomor_telepon', 20)->nullable();
            $table->string('email', 150)->nullable();
            $table->text('alamat')->nullable();
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('diperbarui_pada')->useCurrent()->useCurrentOnUpdate();
        });

        // 4. status_meja
        Schema::create('status_meja', function (Blueprint $table) {
            $table->id();
            $table->string('kode_status', 30)->unique();
            $table->string('nama_status', 50)->unique();
        });

        // 5. meja
        Schema::create('meja', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_meja', 20)->unique();
            $table->integer('kapasitas');
            $table->foreignId('status_meja_id')->constrained('status_meja');
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('diperbarui_pada')->useCurrent()->useCurrentOnUpdate();
        });

        // 6. jenis_menu
        Schema::create('jenis_menu', function (Blueprint $table) {
            $table->id();
            $table->string('kode_jenis', 30)->unique();
            $table->string('nama_jenis', 50)->unique();
        });

        // 7. kategori_menu
        Schema::create('kategori_menu', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kategori', 100)->unique();
            $table->text('deskripsi')->nullable();
            $table->boolean('status_aktif')->default(true);
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('diperbarui_pada')->useCurrent()->useCurrentOnUpdate();
        });

        // 8. menu
        Schema::create('menu', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jenis_menu_id')->constrained('jenis_menu');
            $table->foreignId('kategori_menu_id')->nullable()->constrained('kategori_menu');
            $table->string('kode_menu', 30)->unique();
            $table->string('nama_menu', 150);
            $table->text('deskripsi')->nullable();
            $table->decimal('harga_jual', 15, 2);
            $table->string('foto', 255)->nullable();
            $table->boolean('status_aktif')->default(true);
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('diperbarui_pada')->useCurrent()->useCurrentOnUpdate();
        });

        // 9. ketentuan_paket
        Schema::create('ketentuan_paket', function (Blueprint $table) {
            $table->foreignId('menu_id')->primary()->constrained('menu');
            $table->integer('minimal_pemesanan')->default(1);
            $table->integer('minimal_hari_pemesanan')->default(0);
            $table->decimal('persentase_uang_muka', 5, 2)->default(0);
            $table->integer('batas_konfirmasi_hari')->default(0);
            $table->text('keterangan')->nullable();
        });

        // 10. satuan
        Schema::create('satuan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_satuan', 50);
            $table->string('singkatan', 20);
        });

        // 11. kategori_bahan_baku
        Schema::create('kategori_bahan_baku', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kategori', 100)->unique();
        });

        // 12. bahan_baku
        Schema::create('bahan_baku', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_bahan_baku_id')->nullable()->constrained('kategori_bahan_baku');
            $table->foreignId('satuan_id')->constrained('satuan');
            $table->string('kode_bahan', 30)->unique();
            $table->string('nama_bahan', 150);
            $table->decimal('stok_minimal', 15, 3)->default(0);
            $table->boolean('status_aktif')->default(true);
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('diperbarui_pada')->useCurrent()->useCurrentOnUpdate();
        });

        // 13. resep_menu
        Schema::create('resep_menu', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained('menu');
            $table->foreignId('bahan_baku_id')->constrained('bahan_baku');
            $table->decimal('jumlah', 15, 3);
            $table->foreignId('satuan_id')->constrained('satuan');
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('diperbarui_pada')->useCurrent()->useCurrentOnUpdate();
            $table->unique(['menu_id', 'bahan_baku_id']);
        });

        // 14. jenis_pesanan
        Schema::create('jenis_pesanan', function (Blueprint $table) {
            $table->id();
            $table->string('kode_jenis', 20)->unique();
            $table->string('nama_jenis', 50)->unique();
        });

        // 15. status_pesanan
        Schema::create('status_pesanan', function (Blueprint $table) {
            $table->id();
            $table->string('kode_status', 30)->unique();
            $table->string('nama_status', 50)->unique();
        });

        // 16. pesanan
        Schema::create('pesanan', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_pesanan', 50)->unique();
            $table->foreignId('jenis_pesanan_id')->constrained('jenis_pesanan');
            $table->foreignId('pelanggan_id')->nullable()->constrained('pelanggan');
            $table->foreignId('meja_id')->nullable()->constrained('meja');
            $table->foreignId('pelayan_id')->nullable()->constrained('pengguna');
            $table->foreignId('kasir_id')->nullable()->constrained('pengguna');
            $table->foreignId('status_pesanan_id')->constrained('status_pesanan');
            $table->dateTime('tanggal_pesanan');
            $table->decimal('jumlah_sebelum_potongan', 15, 2)->default(0);
            $table->decimal('jumlah_diskon', 15, 2)->default(0);
            $table->decimal('jumlah_pajak', 15, 2)->default(0);
            $table->decimal('biaya_pelayanan', 15, 2)->default(0);
            $table->decimal('total_tagihan', 15, 2)->default(0);
            $table->text('catatan')->nullable();
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('diperbarui_pada')->useCurrent()->useCurrentOnUpdate();
        });

        // 17. detail_pesanan
        Schema::create('detail_pesanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pesanan_id')->constrained('pesanan');
            $table->foreignId('menu_id')->constrained('menu');
            $table->integer('jumlah');
            $table->decimal('harga_satuan', 15, 2);
            $table->decimal('jumlah_diskon', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2);
            $table->text('catatan')->nullable();
            $table->string('status_item', 30)->nullable();
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('diperbarui_pada')->useCurrent()->useCurrentOnUpdate();
        });

        // 18. jadwal_pesanan
        Schema::create('jadwal_pesanan', function (Blueprint $table) {
            $table->foreignId('pesanan_id')->primary()->constrained('pesanan');
            $table->date('tanggal_acara');
            $table->time('waktu_pengantaran')->nullable();
            $table->text('alamat_pengantaran');
            $table->string('nama_penerima', 100);
            $table->string('nomor_telepon_penerima', 20);
            $table->integer('jumlah_tamu')->nullable();
            $table->string('nama_acara', 100)->nullable();
            $table->text('catatan')->nullable();
        });

        // 19. metode_pembayaran
        Schema::create('metode_pembayaran', function (Blueprint $table) {
            $table->id();
            $table->string('kode_metode', 30)->unique();
            $table->string('nama_metode', 50)->unique();
            $table->boolean('status_aktif')->default(true);
        });

        // 20. status_pembayaran
        Schema::create('status_pembayaran', function (Blueprint $table) {
            $table->id();
            $table->string('kode_status', 30)->unique();
            $table->string('nama_status', 50)->unique();
        });

        // 21. jenis_pembayaran
        Schema::create('jenis_pembayaran', function (Blueprint $table) {
            $table->id();
            $table->string('kode_jenis', 30)->unique();
            $table->string('nama_jenis', 50)->unique();
        });

        // 22. pembayaran
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_pembayaran', 50)->unique();
            $table->foreignId('pesanan_id')->constrained('pesanan');
            $table->foreignId('metode_pembayaran_id')->constrained('metode_pembayaran');
            $table->foreignId('status_pembayaran_id')->constrained('status_pembayaran');
            $table->foreignId('jenis_pembayaran_id')->constrained('jenis_pembayaran');
            $table->foreignId('diproses_oleh')->nullable()->constrained('pengguna');
            $table->decimal('jumlah_bayar', 15, 2);
            $table->dateTime('dibayar_pada')->nullable();
            $table->string('bukti_pembayaran', 255)->nullable();
            $table->string('nomor_referensi', 100)->nullable();
            $table->text('catatan')->nullable();
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('diperbarui_pada')->useCurrent()->useCurrentOnUpdate();
        });

        // 23. status_tiket_dapur
        Schema::create('status_tiket_dapur', function (Blueprint $table) {
            $table->id();
            $table->string('kode_status', 30)->unique();
            $table->string('nama_status', 50)->unique();
        });

        // 24. tiket_dapur
        Schema::create('tiket_dapur', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_tiket', 50)->unique();
            $table->foreignId('pesanan_id')->constrained('pesanan');
            $table->foreignId('status_tiket_dapur_id')->constrained('status_tiket_dapur');
            $table->dateTime('dicetak_pada')->nullable();
            $table->dateTime('diproses_pada')->nullable();
            $table->dateTime('diselesaikan_pada')->nullable();
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('diperbarui_pada')->useCurrent()->useCurrentOnUpdate();
        });

        // 25. detail_tiket_dapur
        Schema::create('detail_tiket_dapur', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tiket_dapur_id')->constrained('tiket_dapur');
            $table->foreignId('detail_pesanan_id')->constrained('detail_pesanan');
            $table->integer('jumlah');
            $table->text('catatan')->nullable();
        });

        // 26. pemasok
        Schema::create('pemasok', function (Blueprint $table) {
            $table->id();
            $table->string('kode_pemasok', 30)->unique();
            $table->string('nama_pemasok', 150);
            $table->string('nomor_telepon', 20)->nullable();
            $table->string('email', 150)->nullable();
            $table->text('alamat')->nullable();
            $table->string('nama_kontak', 100)->nullable();
            $table->boolean('status_aktif')->default(true);
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('diperbarui_pada')->useCurrent()->useCurrentOnUpdate();
        });

        // 27. status_pengadaan
        Schema::create('status_pengadaan', function (Blueprint $table) {
            $table->id();
            $table->string('kode_status', 30)->unique();
            $table->string('nama_status', 50)->unique();
        });

        // 28. pengadaan_bahan
        Schema::create('pengadaan_bahan', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_pengadaan', 50)->unique();
            $table->foreignId('pemasok_id')->nullable()->constrained('pemasok');
            $table->foreignId('pesanan_id')->nullable()->constrained('pesanan');
            $table->foreignId('diajukan_oleh')->constrained('pengguna');
            $table->foreignId('disetujui_oleh')->nullable()->constrained('pengguna');
            $table->foreignId('status_pengadaan_id')->constrained('status_pengadaan');
            $table->string('jenis_pengadaan', 30);
            $table->date('tanggal_pengadaan');
            $table->date('perkiraan_tanggal_datang')->nullable();
            $table->decimal('total_pengadaan', 15, 2)->default(0);
            $table->text('catatan')->nullable();
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('diperbarui_pada')->useCurrent()->useCurrentOnUpdate();
        });

        // 29. detail_pengadaan_bahan
        Schema::create('detail_pengadaan_bahan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengadaan_bahan_id')->constrained('pengadaan_bahan');
            $table->foreignId('bahan_baku_id')->constrained('bahan_baku');
            $table->decimal('jumlah_dipesan', 15, 3);
            $table->decimal('jumlah_diterima', 15, 3)->default(0);
            $table->foreignId('satuan_id')->constrained('satuan');
            $table->decimal('harga_satuan', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->text('catatan')->nullable();
            $table->unique(['pengadaan_bahan_id', 'bahan_baku_id']);
        });

        // 30. penerimaan_bahan
        Schema::create('penerimaan_bahan', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_penerimaan', 50)->unique();
            $table->foreignId('pengadaan_bahan_id')->constrained('pengadaan_bahan');
            $table->foreignId('diterima_oleh')->constrained('pengguna');
            $table->dateTime('diterima_pada');
            $table->string('nomor_nota', 100)->nullable();
            $table->string('berkas_nota', 255)->nullable();
            $table->text('catatan')->nullable();
        });

        // 31. detail_penerimaan_bahan
        Schema::create('detail_penerimaan_bahan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penerimaan_bahan_id')->constrained('penerimaan_bahan');
            $table->foreignId('detail_pengadaan_bahan_id')->constrained('detail_pengadaan_bahan');
            $table->decimal('jumlah_diterima', 15, 3);
            $table->decimal('harga_satuan', 15, 2);
            $table->date('tanggal_kedaluwarsa')->nullable();
            $table->text('catatan')->nullable();
        });

        // 32. jenis_mutasi_stok
        Schema::create('jenis_mutasi_stok', function (Blueprint $table) {
            $table->id();
            $table->string('kode_jenis', 30)->unique();
            $table->string('nama_jenis', 50)->unique();
            $table->enum('arah_stok', ['MASUK', 'KELUAR']);
        });

        // 33. mutasi_stok
        Schema::create('mutasi_stok', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bahan_baku_id')->constrained('bahan_baku');
            $table->foreignId('jenis_mutasi_stok_id')->constrained('jenis_mutasi_stok');
            $table->decimal('jumlah', 15, 3);
            $table->foreignId('satuan_id')->constrained('satuan');
            $table->dateTime('tanggal_mutasi');
            $table->foreignId('detail_pesanan_id')->nullable()->constrained('detail_pesanan');
            $table->foreignId('detail_penerimaan_bahan_id')->nullable()->constrained('detail_penerimaan_bahan');
            $table->unsignedBigInteger('detail_penyesuaian_stok_id')->nullable();
            $table->foreignId('dibuat_oleh')->constrained('pengguna');
            $table->text('catatan')->nullable();
            $table->timestamp('dibuat_pada')->useCurrent();
        });

        // 34. stok_bahan_baku
        Schema::create('stok_bahan_baku', function (Blueprint $table) {
            $table->foreignId('bahan_baku_id')->primary()->constrained('bahan_baku');
            $table->decimal('jumlah_stok', 15, 3)->default(0);
            $table->dateTime('terakhir_diperbarui');
        });

        // 35. penyesuaian_stok
        Schema::create('penyesuaian_stok', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_penyesuaian', 50)->unique();
            $table->dateTime('tanggal_penyesuaian');
            $table->foreignId('dibuat_oleh')->constrained('pengguna');
            $table->foreignId('disetujui_oleh')->nullable()->constrained('pengguna');
            $table->text('alasan');
            $table->string('status_penyesuaian', 30);
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('diperbarui_pada')->useCurrent()->useCurrentOnUpdate();
        });

        // 36. detail_penyesuaian_stok
        Schema::create('detail_penyesuaian_stok', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penyesuaian_stok_id')->constrained('penyesuaian_stok');
            $table->foreignId('bahan_baku_id')->constrained('bahan_baku');
            $table->decimal('jumlah_sistem', 15, 3);
            $table->decimal('jumlah_fisik', 15, 3);
            $table->decimal('jumlah_selisih', 15, 3);
            $table->foreignId('satuan_id')->constrained('satuan');
            $table->text('catatan')->nullable();
        });

        // Menambahkan foreign key untuk detail_penyesuaian_stok_id di mutasi_stok
        Schema::table('mutasi_stok', function (Blueprint $table) {
            $table->foreign('detail_penyesuaian_stok_id')->references('id')->on('detail_penyesuaian_stok');
        });

        // 37. status_pengantaran
        Schema::create('status_pengantaran', function (Blueprint $table) {
            $table->id();
            $table->string('kode_status', 30)->unique();
            $table->string('nama_status', 50)->unique();
        });

        // 38. pengantaran
        Schema::create('pengantaran', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_pengantaran', 50)->unique();
            $table->foreignId('pesanan_id')->unique()->constrained('pesanan');
            $table->foreignId('status_pengantaran_id')->constrained('status_pengantaran');
            $table->foreignId('ditugaskan_kepada')->nullable()->constrained('pengguna');
            $table->dateTime('jadwal_pengantaran');
            $table->dateTime('berangkat_pada')->nullable();
            $table->dateTime('diterima_pada')->nullable();
            $table->string('nama_penerima', 100);
            $table->string('nomor_telepon_penerima', 20);
            $table->text('alamat_pengantaran');
            $table->text('catatan')->nullable();
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('diperbarui_pada')->useCurrent()->useCurrentOnUpdate();
        });

    }

    public function down()
    {
        // Drop in reverse order to respect foreign keys constraints
        Schema::dropIfExists('pengantaran');
        Schema::dropIfExists('status_pengantaran');

        Schema::table('mutasi_stok', function (Blueprint $table) {
            $table->dropForeign(['detail_penyesuaian_stok_id']);
        });

        Schema::dropIfExists('detail_penyesuaian_stok');
        Schema::dropIfExists('penyesuaian_stok');
        Schema::dropIfExists('stok_bahan_baku');
        Schema::dropIfExists('mutasi_stok');
        Schema::dropIfExists('jenis_mutasi_stok');
        Schema::dropIfExists('detail_penerimaan_bahan');
        Schema::dropIfExists('penerimaan_bahan');
        Schema::dropIfExists('detail_pengadaan_bahan');
        Schema::dropIfExists('pengadaan_bahan');
        Schema::dropIfExists('status_pengadaan');
        Schema::dropIfExists('pemasok');
        Schema::dropIfExists('detail_tiket_dapur');
        Schema::dropIfExists('tiket_dapur');
        Schema::dropIfExists('status_tiket_dapur');
        Schema::dropIfExists('pembayaran');
        Schema::dropIfExists('jenis_pembayaran');
        Schema::dropIfExists('status_pembayaran');
        Schema::dropIfExists('metode_pembayaran');
        Schema::dropIfExists('jadwal_pesanan');
        Schema::dropIfExists('detail_pesanan');
        Schema::dropIfExists('pesanan');
        Schema::dropIfExists('status_pesanan');
        Schema::dropIfExists('jenis_pesanan');
        Schema::dropIfExists('resep_menu');
        Schema::dropIfExists('bahan_baku');
        Schema::dropIfExists('kategori_bahan_baku');
        Schema::dropIfExists('satuan');
        Schema::dropIfExists('ketentuan_paket');
        Schema::dropIfExists('menu');
        Schema::dropIfExists('kategori_menu');
        Schema::dropIfExists('jenis_menu');
        Schema::dropIfExists('meja');
        Schema::dropIfExists('status_meja');
        Schema::dropIfExists('pelanggan');
        Schema::dropIfExists('pengguna');
        Schema::dropIfExists('peran');
    }
};
