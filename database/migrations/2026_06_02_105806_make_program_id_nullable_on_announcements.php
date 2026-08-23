<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('program_announcements', function (Blueprint $table) {
            // Drop foreign key lama terlebih dahulu agar tidak mengunci saat diubah
            $table->dropForeign(['program_id']);

            // Ubah menjadi nullable
            $table->foreignId('program_id')->nullable()->change();

            // Pasang kembali foreign key constraint-nya
            $table->foreign('program_id')->references('id')->on('programs')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('program_announcements', function (Blueprint $table) {
            $table->dropForeign(['program_id']);
            $table->foreignId('program_id')->nullable(false)->change();
            $table->foreign('program_id')->references('id')->on('programs')->onDelete('cascade');
        });
    }
};
