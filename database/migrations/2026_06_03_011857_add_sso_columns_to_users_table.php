<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
// File migrasi baru
public function up()
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('sso_token', 64)->nullable(); // Token sementara
        $table->timestamp('sso_token_expires_at')->nullable();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
