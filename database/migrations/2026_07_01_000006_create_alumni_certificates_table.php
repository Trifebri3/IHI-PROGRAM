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
        if (!Schema::hasTable('alumni_certificates')) {
            Schema::create('alumni_certificates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('alumni_program_id')->constrained('alumni_programs')->cascadeOnDelete();
                $table->string('certificate_number')->unique()->nullable();
                $table->string('file_path');
                $table->uuid('uuid')->unique(); // Unique identifier for QR validation URL
                $table->json('extra_info')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumni_certificates');
    }
};
