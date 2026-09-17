<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('piagam_grading_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grading_format_id')->constrained('piagam_grading_formats')->cascadeOnDelete();
            $table->string('name');
            $table->string('type')->default('number'); // number, text, boolean
            $table->decimal('weight_percentage', 5, 2)->nullable();
            $table->string('default_value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('piagam_grading_components');
    }
};
