<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('piagam_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('programs')->cascadeOnDelete();
            $table->foreignId('participant_id')->constrained('registrations')->cascadeOnDelete();
            $table->foreignId('template_id')->nullable()->constrained('piagam_templates')->nullOnDelete();
            $table->string('certificate_number')->unique()->nullable();
            $table->string('file_path')->nullable();
            $table->string('status')->default('draft'); // draft, generated, published
            $table->uuid('qr_token')->unique()->nullable();
            $table->decimal('final_grade', 5, 2)->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('piagam_certificates');
    }
};
