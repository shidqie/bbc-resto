<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add new string columns and other required fields
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->string('metode_pembayaran')->nullable()->after('pesanan_id');
            $table->string('jenis_pembayaran')->nullable()->after('metode_pembayaran');
            $table->string('status_verifikasi')->nullable()->after('bukti_pembayaran');
            $table->decimal('jumlah_tagihan', 12, 2)->nullable()->after('jenis_pembayaran');
            $table->datetime('tanggal_pembayaran')->nullable()->after('status_verifikasi');
            $table->unsignedBigInteger('diverifikasi_oleh')->nullable()->after('tanggal_pembayaran');
            $table->datetime('tanggal_verifikasi')->nullable()->after('diverifikasi_oleh');
            $table->text('catatan_verifikasi')->nullable()->after('tanggal_verifikasi');
            
            // Rename columns
            $table->renameColumn('nomor_pembayaran', 'kode_pembayaran');
            $table->renameColumn('jumlah_bayar', 'jumlah_dibayar');
        });

        // Migrate existing data (safely map IDs to strings)
        DB::table('pembayaran')->update([
            'metode_pembayaran' => DB::raw("
                CASE 
                    WHEN metode_pembayaran_id = 1 THEN 'tunai' 
                    WHEN metode_pembayaran_id = 2 THEN 'qris_manual' 
                    WHEN metode_pembayaran_id = 3 THEN 'transfer_bank' 
                    ELSE 'tunai' 
                END
            "),
            'jenis_pembayaran' => DB::raw("
                CASE 
                    WHEN jenis_pembayaran_id = 1 THEN 'uang_muka' 
                    WHEN jenis_pembayaran_id = 2 THEN 'pelunasan' 
                    WHEN jenis_pembayaran_id = 3 THEN 'pembayaran_penuh' 
                    ELSE 'pembayaran_penuh' 
                END
            "),
            'status_verifikasi' => DB::raw("
                CASE 
                    WHEN status_pembayaran_id = 1 THEN 'belum_dibayar'
                    WHEN status_pembayaran_id = 2 THEN 'menunggu_verifikasi'
                    WHEN status_pembayaran_id = 3 THEN 'diterima'
                    WHEN status_pembayaran_id = 4 THEN 'ditolak'
                    ELSE 'diterima'
                END
            "),
            'tanggal_pembayaran' => DB::raw('dibayar_pada')
        ]);

        // Drop foreign keys using raw queries to safely ignore missing keys
        try { DB::statement("ALTER TABLE pembayaran DROP FOREIGN KEY pembayaran_metode_pembayaran_id_foreign"); } catch (\Exception $e) {}
        try { DB::statement("ALTER TABLE pembayaran DROP FOREIGN KEY pembayaran_status_pembayaran_id_foreign"); } catch (\Exception $e) {}
        try { DB::statement("ALTER TABLE pembayaran DROP FOREIGN KEY pembayaran_jenis_pembayaran_id_foreign"); } catch (\Exception $e) {}
        try { DB::statement("ALTER TABLE pembayaran DROP FOREIGN KEY pembayaran_diproses_oleh_foreign"); } catch (\Exception $e) {}

        // Drop old columns
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->dropColumn([
                'metode_pembayaran_id', 
                'status_pembayaran_id', 
                'jenis_pembayaran_id', 
                'diproses_oleh',
                'dibayar_pada',
                
                // Midtrans fields
                'midtrans_order_id',
                'midtrans_transaction_id',
                'qr_code_url',
                'expired_at',
                'response_midtrans',
                'webhook_data',
                'auto_verified',
                'nomor_referensi'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->renameColumn('kode_pembayaran', 'nomor_pembayaran');
            $table->renameColumn('jumlah_dibayar', 'jumlah_bayar');
            
            $table->unsignedBigInteger('metode_pembayaran_id')->nullable();
            $table->unsignedBigInteger('status_pembayaran_id')->nullable();
            $table->unsignedBigInteger('jenis_pembayaran_id')->nullable();
            $table->unsignedBigInteger('diproses_oleh')->nullable();
            $table->datetime('dibayar_pada')->nullable();
            
            $table->string('midtrans_order_id')->nullable();
            $table->string('midtrans_transaction_id')->nullable();
            $table->text('qr_code_url')->nullable();
            $table->datetime('expired_at')->nullable();
            $table->text('response_midtrans')->nullable();
            $table->json('webhook_data')->nullable();
            $table->boolean('auto_verified')->default(false);
            $table->string('nomor_referensi')->nullable();
            
            $table->dropColumn([
                'metode_pembayaran',
                'jenis_pembayaran',
                'status_verifikasi',
                'jumlah_tagihan',
                'tanggal_pembayaran',
                'diverifikasi_oleh',
                'tanggal_verifikasi',
                'catatan_verifikasi'
            ]);
        });
    }
};
