<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('programs') && !Schema::hasColumn('programs', 'piagam_public_settings')) {
            Schema::table('programs', function (Blueprint $table) {
                $table->json('piagam_public_settings')->nullable()->after('piagam_template_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('programs') && Schema::hasColumn('programs', 'piagam_public_settings')) {
            Schema::table('programs', function (Blueprint $table) {
                $table->dropColumn('piagam_public_settings');
            });
        }
    }
};
