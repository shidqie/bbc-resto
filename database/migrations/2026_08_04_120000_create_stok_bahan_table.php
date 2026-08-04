<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Persediaan wajib dipisah menjadi dua kelompok operasional:
 *  - harian  : Dine-In dan Nasi Box
 *  - catering: Catering
 *
 * Master bahan baku tetap satu (bahan_baku). Saldo disimpan pada tabel baru
 * `stok_bahan` dengan unique constraint gabungan (bahan_baku_id, jenis_persediaan).
 * Stok minimum disimpan per jenis persediaan karena kebutuhan keduanya berbeda.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stok_bahan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bahan_baku_id')->constrained('bahan_baku')->cascadeOnDelete();
            $table->enum('jenis_persediaan', ['harian', 'catering'])->default('harian');
            $table->decimal('jumlah_stok', 15, 3)->default(0);
            $table->decimal('stok_minimal', 15, 3)->default(0);
            $table->dateTime('terakhir_diperbarui')->nullable();

            $table->unique(['bahan_baku_id', 'jenis_persediaan']);
        });

        // Saldo harian lama (stok_bahan_baku) dipindah menjadi stok harian.
        // Stok minimal per bahan diambil dari bahan_baku.stok_minimal sebagai nilai awal.
        $rows = DB::table('stok_bahan_baku')->get();
        foreach ($rows as $row) {
            $minimal = DB::table('bahan_baku')->where('id', $row->bahan_baku_id)->value('stok_minimal') ?? 0;
            DB::table('stok_bahan')->insert([
                'bahan_baku_id' => $row->bahan_baku_id,
                'jenis_persediaan' => 'harian',
                'jumlah_stok' => $row->jumlah_stok,
                'stok_minimal' => $minimal,
                'terakhir_diperbarui' => $row->terakhir_diperbarui,
            ]);
        }

        // Setiap bahan mendapat baris stok catering (saldo awal 0) agar
        // pemisahan berjalan penuh tanpa membuat duplikasi master bahan.
        $bahanIds = DB::table('bahan_baku')->orderBy('id')->pluck('id');
        foreach ($bahanIds as $bahanId) {
            $minimal = DB::table('bahan_baku')->where('id', $bahanId)->value('stok_minimal') ?? 0;
            DB::table('stok_bahan')->insert([
                'bahan_baku_id' => $bahanId,
                'jenis_persediaan' => 'catering',
                'jumlah_stok' => 0,
                'stok_minimal' => $minimal,
                'terakhir_diperbarui' => null,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('stok_bahan');
    }
};
