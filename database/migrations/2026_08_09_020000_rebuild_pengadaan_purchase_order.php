<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_order', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_po', 30)->unique();
            $table->foreignId('pengadaan_bahan_id')
                ->constrained('pengadaan_bahan')
                ->cascadeOnDelete();
            $table->string('supplier', 150)->nullable();
            $table->date('tanggal_po')->nullable();
            $table->string('status', 30)->default('menunggu_barang');
            $table->text('catatan')->nullable();
            $table->foreignId('dibuat_oleh')->nullable()->constrained('pengguna');
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('diperbarui_pada')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('detail_purchase_order', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')
                ->constrained('purchase_order')
                ->cascadeOnDelete();
            $table->foreignId('detail_pengadaan_bahan_id')
                ->constrained('detail_pengadaan_bahan')
                ->cascadeOnDelete();
            $table->foreignId('bahan_baku_id')
                ->constrained('bahan_baku')
                ->cascadeOnDelete();
            $table->decimal('jumlah_dipesan', 15, 3);
            $table->decimal('jumlah_diterima', 15, 3)->default(0);
            $table->foreignId('satuan_id')->nullable()->constrained('satuan');
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('diperbarui_pada')->useCurrent()->useCurrentOnUpdate();
        });

        // Penautan penerimaan ke Purchase Order.
        Schema::table('penerimaan_bahan', function (Blueprint $table) {
            $table->foreignId('purchase_order_id')
                ->nullable()
                ->after('pengadaan_bahan_id')
                ->constrained('purchase_order')
                ->nullOnDelete();
        });

        Schema::table('detail_penerimaan_bahan', function (Blueprint $table) {
            $table->foreignId('detail_purchase_order_id')
                ->nullable()
                ->after('detail_pengadaan_bahan_id')
                ->constrained('detail_purchase_order')
                ->nullOnDelete();
        });

        // ─── Backfill data lama: bungkus permintaan yang ada menjadi PO 1:1 ───
        $this->backfillDataLama();
    }

    public function down(): void
    {
        Schema::table('detail_penerimaan_bahan', function (Blueprint $table) {
            $table->dropForeign(['detail_purchase_order_id']);
            $table->dropColumn('detail_purchase_order_id');
        });

        Schema::table('penerimaan_bahan', function (Blueprint $table) {
            $table->dropForeign(['purchase_order_id']);
            $table->dropColumn('purchase_order_id');
        });

        Schema::dropIfExists('detail_purchase_order');
        Schema::dropIfExists('purchase_order');
    }

    private function backfillDataLama(): void
    {
        if (! Schema::hasTable('pengadaan_bahan')) {
            return;
        }

        $pengadaans = DB::table('pengadaan_bahan')->orderBy('id')->get();

        // Fallback bila 'diajukan_oleh' menunjuk pengguna yang sudah tidak ada (mis. setelah re-seed).
        $validUserId = DB::table('pengguna')->min('id') ?? 1;

        foreach ($pengadaans as $pengadaan) {
            $supplier = DB::table('penerimaan_bahan')
                ->where('pengadaan_bahan_id', $pengadaan->id)
                ->whereNotNull('supplier')
                ->value('supplier');

            $detailRows = DB::table('detail_pengadaan_bahan')
                ->where('pengadaan_bahan_id', $pengadaan->id)
                ->get();

            $jmlDipesan = 0;
            $jmlDiterima = 0;
            $semuaTerpenuhi = true;

            foreach ($detailRows as $detail) {
                $diminta = (float) $detail->jumlah_dipesan;
                $diterima = (float) $detail->jumlah_diterima;
                $jmlDipesan += $diminta;
                $jmlDiterima += $diterima;
                if ($diterima < $diminta) {
                    $semuaTerpenuhi = false;
                }
            }

            // Row tanpa detail tidak perlu dibungkus PO.
            if ($detailRows->isEmpty()) {
                continue;
            }

            $statusPo = 'menunggu_barang';
            if ($pengadaan->status_pengadaan_id == 7) {
                $statusPo = 'dibatalkan';
            } elseif ($jmlDiterima > 0 && $semuaTerpenuhi) {
                $statusPo = 'selesai';
            } elseif ($jmlDiterima > 0) {
                $statusPo = 'diterima_sebagian';
            }

            $nomorPo = $this->kodePo();

            $poId = DB::table('purchase_order')->insertGetId([
                'nomor_po' => $nomorPo,
                'pengadaan_bahan_id' => $pengadaan->id,
                'supplier' => $supplier,
                'tanggal_po' => $pengadaan->tanggal_pengadaan,
                'status' => $statusPo,
                'catatan' => 'Dibuat otomatis saat migrasi dari sistem lama.',
                'dibuat_oleh' => DB::table('pengguna')->where('id', $pengadaan->diajukan_oleh)->exists()
                    ? $pengadaan->diajukan_oleh
                    : $validUserId,
                'dibuat_pada' => now(),
                'diperbarui_pada' => now(),
            ]);

            foreach ($detailRows as $detail) {
                $dpoId = DB::table('detail_purchase_order')->insertGetId([
                    'purchase_order_id' => $poId,
                    'detail_pengadaan_bahan_id' => $detail->id,
                    'bahan_baku_id' => $detail->bahan_baku_id,
                    'jumlah_dipesan' => $detail->jumlah_dipesan,
                    'jumlah_diterima' => $detail->jumlah_diterima,
                    'satuan_id' => $detail->satuan_id,
                    'dibuat_pada' => now(),
                    'diperbarui_pada' => now(),
                ]);

                DB::table('detail_penerimaan_bahan')
                    ->where('detail_pengadaan_bahan_id', $detail->id)
                    ->update(['detail_purchase_order_id' => $dpoId]);
            }

            DB::table('penerimaan_bahan')
                ->where('pengadaan_bahan_id', $pengadaan->id)
                ->update(['purchase_order_id' => $poId]);
        }
    }

    private function kodePo(): string
    {
        $date = now()->format('Ymd');
        $count = DB::table('purchase_order')->count() + 1;

        return 'PO-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
};