<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('piagam_participant_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_id')->constrained('registrations')->cascadeOnDelete();
            $table->foreignId('grading_component_id')->constrained('piagam_grading_components')->cascadeOnDelete();
            $table->string('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('piagam_participant_grades');
    }
};
