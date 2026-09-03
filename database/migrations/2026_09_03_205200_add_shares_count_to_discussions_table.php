<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('discussions', 'shares_count')) {
            Schema::table('discussions', function (Blueprint $table) {
                $table->unsignedInteger('shares_count')->default(0)->after('content');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('discussions', 'shares_count')) {
            Schema::table('discussions', function (Blueprint $table) {
                $table->dropColumn('shares_count');
            });
        }
    }
};
