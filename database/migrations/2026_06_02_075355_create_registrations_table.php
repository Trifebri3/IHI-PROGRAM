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
    Schema::create('registration_stage_data', function (Blueprint $table) {
        $table->id();
        $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
        $table->foreignId('program_stage_id')->constrained()->cascadeOnDelete();
        $table->json('form_values')->nullable(); // Menyimpan jawaban peserta untuk form kustom di tahap ini
        $table->enum('status', ['pending', 'passed', 'failed'])->default('pending');
        $table->text('reviewer_notes')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
