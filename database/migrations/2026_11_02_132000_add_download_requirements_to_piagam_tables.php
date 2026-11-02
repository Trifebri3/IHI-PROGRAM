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
        if (Schema::hasTable('piagam_templates') && !Schema::hasColumn('piagam_templates', 'download_requirements')) {
            Schema::table('piagam_templates', function (Blueprint $table) {
                $table->json('download_requirements')->nullable()->after('description');
            });
        }

        if (Schema::hasTable('piagam_certificates') && !Schema::hasColumn('piagam_certificates', 'requirements_completed_at')) {
            Schema::table('piagam_certificates', function (Blueprint $table) {
                $table->timestamp('requirements_completed_at')->nullable()->after('email_sent_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('piagam_templates') && Schema::hasColumn('piagam_templates', 'download_requirements')) {
            Schema::table('piagam_templates', function (Blueprint $table) {
                $table->dropColumn('download_requirements');
            });
        }

        if (Schema::hasTable('piagam_certificates') && Schema::hasColumn('piagam_certificates', 'requirements_completed_at')) {
            Schema::table('piagam_certificates', function (Blueprint $table) {
                $table->dropColumn('requirements_completed_at');
            });
        }
    }
};

