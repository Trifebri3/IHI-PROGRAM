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
        if (!Schema::hasTable('user_alumni')) {
            Schema::create('user_alumni', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('alumni_program_id')->constrained('alumni_programs')->cascadeOnDelete();
                $table->uuid('uuid')->unique();
                $table->string('verification_status')->default('approved'); // approved, pending, rejected, revision
                $table->json('extra_info')->nullable(); // Nilai Akhir, Predikat, Ranking, Jam Pelatihan, Kompetensi, dll
                $table->timestamps();

                $table->unique(['user_id', 'alumni_program_id']); // Satu user satu alumni_program
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_alumni');
    }
};
