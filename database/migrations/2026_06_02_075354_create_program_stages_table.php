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
    Schema::create('program_stages', function (Blueprint $table) {
        $table->id();
        $table->foreignId('program_id')->constrained()->cascadeOnDelete();
        $table->string('name'); // Cth: "Seleksi Administrasi", "Wawancara"
        $table->integer('sequence')->default(1); // Urutan tahapan (1, 2, 3...)
        $table->date('start_date');
        $table->date('end_date');
        $table->json('form_schema')->nullable(); // Menyimpan struktur form kustom khusus tahap ini
        $table->text('pass_announcement')->nullable(); // Teks jika lolos tahap ini
        $table->text('fail_announcement')->nullable(); // Teks jika gagal tahap ini
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_stages');
    }
};
