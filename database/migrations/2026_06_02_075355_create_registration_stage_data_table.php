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
    Schema::create('registrations', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->foreignId('program_id')->constrained()->cascadeOnDelete();
        $table->foreignId('current_stage_id')->nullable()->constrained('program_stages')->nullOnDelete();
        $table->enum('status', ['process', 'passed', 'failed'])->default('process');
        $table->string('final_id_number')->nullable()->unique(); // Nomor Induk Program (PRG202600001)
        $table->timestamps();

        $table->unique(['user_id', 'program_id']); // Peserta hanya bisa daftar 1 kali per program
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_stage_data');
    }
};
