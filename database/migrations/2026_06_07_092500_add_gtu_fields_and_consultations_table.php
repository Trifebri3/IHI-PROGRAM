<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah kolom gtu_email di tabel programs
        Schema::table('programs', function (Blueprint $table) {
            $table->string('gtu_email')->nullable()->after('program_certificate_template');
        });

        // 2. Buat tabel gtu_consultations untuk mencatat setiap pertanyaan/konsultasi
        Schema::create('gtu_consultations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('subject');
            $table->text('question');
            $table->text('reply')->nullable();
            $table->string('status')->default('pending'); // pending, answered
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gtu_consultations');

        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn('gtu_email');
        });
    }
};
