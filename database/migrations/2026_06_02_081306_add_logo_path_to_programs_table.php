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
        Schema::table('programs', function (Blueprint $table) {
            // Menambahkan kolom logo_path, opsional (nullable), ditaruh setelah banner_path
            $table->string('logo_path')->nullable()->after('banner_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            // Drop kolom jika migrasi di-rollback
            $table->dropColumn('logo_path');
        });
    }
};
