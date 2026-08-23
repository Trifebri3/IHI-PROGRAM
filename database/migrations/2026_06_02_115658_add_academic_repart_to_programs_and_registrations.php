<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Injeksi pengaturan transkrip nilai pada tabel induk programs
        Schema::table('programs', function (Blueprint $table) {
            $table->integer('total_hours')->default(32)->after('quota'); // Total Jam Pelajaran (JP)
            $table->text('score_schema')->nullable()->after('total_hours'); // Menyimpan judul kriteria nilai (Array JSON)
            $table->string('program_certificate_template')->nullable()->after('banner_path'); // Base gambar PNG Piagam Program
        });

        // 2. Injeksi penampung nilai individual peserta pada tabel registrations
        Schema::table('registrations', function (Blueprint $table) {
            $table->text('final_scores')->nullable()->after('status'); // Menyimpan nilai angka/huruf tiap kriteria (Array JSON)
            $table->string('secure_verification_token', 64)->nullable()->unique()->after('final_id_number'); // Token unik untuk QR Code validasi
        });
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn(['final_scores', 'secure_verification_token']);
        });

        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn(['total_hours', 'score_schema', 'program_certificate_template']);
        });
    }
};
