<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registration_stage_data', function (Blueprint $table) {
            $table->string('status')->default('pending')->change();
        });
    }

    public function down(): void
    {
        Schema::table('registration_stage_data', function (Blueprint $table) {
            // Revert to original enum if needed
            $table->enum('status', ['pending', 'passed', 'failed'])->default('pending')->change();
        });
    }
};
