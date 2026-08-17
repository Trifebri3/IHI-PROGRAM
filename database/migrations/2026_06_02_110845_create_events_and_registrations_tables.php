<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel Utama Event / Seminar
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('location'); // Cth: Google Meet Link / Aula Kampus Gedung Lantai 3
            $table->date('event_date');
            $table->time('event_time');
            $table->integer('quota')->default(100);
            $table->string('banner_path')->nullable();
            $table->timestamps();
        });

        // 2. Tabel Pivot Pendaftaran Event Peserta (One-Click Join)
        Schema::create('event_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_registrations');
        Schema::dropIfExists('events');
    }
};
