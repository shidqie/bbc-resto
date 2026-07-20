<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pesanan_caterings', function (Blueprint $table) {
            $table->string('snap_token')->nullable()->after('status');
        });

        Schema::table('pesanan_nasi_boxes', function (Blueprint $table) {
            $table->string('snap_token')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('pesanan_caterings', function (Blueprint $table) {
            $table->dropColumn('snap_token');
        });

        Schema::table('pesanan_nasi_boxes', function (Blueprint $table) {
            $table->dropColumn('snap_token');
        });
    }
};
