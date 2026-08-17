<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel Skema Form Kustom buatan Admin Program
        Schema::create('program_biodata_schemas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('programs')->onDelete('cascade');
            $table->string('field_name');
            $table->string('field_type'); // text, number, file
            $table->boolean('is_required')->default(true);
            $table->timestamps();
        });

        // 2. Tabel Penyimpan Jawaban Peserta Terdaftar
        Schema::create('program_biodata_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('program_id')->constrained('programs')->onDelete('cascade');
            $table->longText('submitted_answers'); // Simpan format JSON Array
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_biodata_submissions');
        Schema::dropIfExists('program_biodata_schemas');
    }
};
