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
        // 1. Tambah is_locked di program_stages jika belum ada
        if (Schema::hasTable('program_stages')) {
            Schema::table('program_stages', function (Blueprint $table) {
                if (!Schema::hasColumn('program_stages', 'is_locked')) {
                    $table->boolean('is_locked')->default(0)->after('sequence');
                }
                
                // Ubah announcements menjadi longText secara aman
                if (Schema::hasColumn('program_stages', 'announcements')) {
                    $table->longText('announcements')->nullable()->change();
                }
            });
        }

        // 2. Tambah is_announcement_read di registration_stage_data jika belum ada
        if (Schema::hasTable('registration_stage_data')) {
            Schema::table('registration_stage_data', function (Blueprint $table) {
                if (!Schema::hasColumn('registration_stage_data', 'is_announcement_read')) {
                    $table->boolean('is_announcement_read')->default(0)->after('status');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('program_stages')) {
            Schema::table('program_stages', function (Blueprint $table) {
                if (Schema::hasColumn('program_stages', 'is_locked')) {
                    $table->dropColumn('is_locked');
                }
            });
        }

        if (Schema::hasTable('registration_stage_data')) {
            Schema::table('registration_stage_data', function (Blueprint $table) {
                if (Schema::hasColumn('registration_stage_data', 'is_announcement_read')) {
                    $table->dropColumn('is_announcement_read');
                }
            });
        }
    }
};
