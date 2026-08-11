<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pengantaran', function (Blueprint $table) {
            $table->decimal('jarak_pengantaran', 8, 2)->nullable()->after('alamat_pengantaran')->comment('Jarak dalam km');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengantaran', function (Blueprint $table) {
            $table->dropColumn('jarak_pengantaran');
        });
    }
};
