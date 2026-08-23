<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('public_highlights', function (Blueprint $table) {
            $table->unsignedInteger('views_count')->default(0)->after('is_active');
            $table->unsignedInteger('clicks_count')->default(0)->after('views_count');
        });
    }

    public function down(): void
    {
        Schema::table('public_highlights', function (Blueprint $table) {
            $table->dropColumn(['views_count', 'clicks_count']);
        });
    }
};
