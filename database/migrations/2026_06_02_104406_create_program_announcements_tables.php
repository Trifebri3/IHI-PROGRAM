<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel Master Pengumuman Program Kerja
        Schema::create('program_announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('programs')->onDelete('cascade');
            $table->string('title');
            $table->longText('content');
            $table->string('type')->default('info'); // info, instruction (wajib isi), warning
            $table->timestamps();
        });

        // 2. Tabel Log Konfirmasi Baca Peserta (Pivot Log)
        Schema::create('program_announcement_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('program_announcement_id')->constrained('program_announcements')->onDelete('cascade');
            $table->timestamp('confirmed_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_announcement_views');
        Schema::dropIfExists('program_announcements');
    }
};
