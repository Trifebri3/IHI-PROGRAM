<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('piagam_layout_elements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('layout_id')->constrained('piagam_layouts')->cascadeOnDelete();
            $table->string('type'); // static, variable, qr_code
            $table->text('content')->nullable(); // static text or [VARIABLE_NAME]
            $table->decimal('x_pos', 8, 2)->default(0);
            $table->decimal('y_pos', 8, 2)->default(0);
            $table->integer('font_size')->default(12);
            $table->string('font_family')->default('Arial');
            $table->string('color')->default('#000000');
            $table->string('text_align')->default('left');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('piagam_layout_elements');
    }
};
