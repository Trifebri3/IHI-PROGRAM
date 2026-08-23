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
        if (!Schema::hasTable('qr_verification_logs')) {
            Schema::create('qr_verification_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('alumni_certificate_id')->nullable()->constrained('alumni_certificates')->nullOnDelete();
                $table->string('scanned_uuid');
                $table->string('ip_address')->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamp('scanned_at')->useCurrent();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qr_verification_logs');
    }
};
