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
    Schema::create('biodata_fields', function (Blueprint $table) {
        $table->id();
        $table->string('name'); // Contoh: "Foto KTP", "Ukuran Baju"
        $table->string('type'); // Contoh: text, number, date, file, select
        $table->boolean('is_required')->default(true); // Wajib diisi atau tidak
        $table->json('options')->nullable(); // Untuk menyimpan pilihan dropdown (opsional)
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biodata_fields');
    }
};
