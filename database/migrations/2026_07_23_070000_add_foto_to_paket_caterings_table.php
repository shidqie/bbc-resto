<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('paket_caterings', function (Blueprint $table) {
            if (!Schema::hasColumn('paket_caterings', 'foto')) {
                $table->string('foto')->nullable()->after('deskripsi');
            }
        });
    }

    public function down(): void
    {
        Schema::table('paket_caterings', function (Blueprint $table) {
            if (Schema::hasColumn('paket_caterings', 'foto')) {
                $table->dropColumn('foto');
            }
        });
    }
};
