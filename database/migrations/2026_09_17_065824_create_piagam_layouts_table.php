<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('piagam_layouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('piagam_templates')->cascadeOnDelete();
            $table->integer('page_number')->default(1);
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('piagam_layouts');
    }
};
