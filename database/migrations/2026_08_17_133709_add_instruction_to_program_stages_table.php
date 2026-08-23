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
        Schema::table('program_stages', function (Blueprint $table) {
            $table->longText('instruction')->nullable()->after('fail_announcement');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('program_stages', function (Blueprint $table) {
            $table->dropColumn('instruction');
        });
    }
};
