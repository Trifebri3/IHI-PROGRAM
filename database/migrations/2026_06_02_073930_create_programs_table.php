<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel Utama Program
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('banner_path')->nullable();
            $table->integer('quota')->default(0);
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['draft', 'published', 'closed', 'finished'])->default('draft');
            $table->timestamps();
        });

        // 2. Tabel Pivot Pendelegasian (Program Manager)
        Schema::create('program_manager', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // ID User yang ber-role Admin Program
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_manager');
        Schema::dropIfExists('programs');
    }
};
