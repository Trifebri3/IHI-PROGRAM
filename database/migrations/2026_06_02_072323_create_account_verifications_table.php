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
    Schema::create('account_verifications', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
        $table->text('nik'); // Di-set TEXT karena hasil enkripsi string-nya panjang
        $table->string('ktp_path');
        $table->string('photo_path');
        $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
        $table->text('rejection_reason')->nullable(); // Alasan jika ditolak
        $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
        $table->timestamp('verified_at')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_verifications');
    }
};
