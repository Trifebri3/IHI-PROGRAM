<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'is_forum_restricted')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_forum_restricted')->default(false)->after('is_blocked');
            });
        }

        if (Schema::hasTable('discussion_reports')) {
            Schema::table('discussion_reports', function (Blueprint $table) {
                if (!Schema::hasColumn('discussion_reports', 'action_taken')) {
                    $table->string('action_taken')->nullable()->after('status');
                }
                if (!Schema::hasColumn('discussion_reports', 'action_taken_by')) {
                    $table->foreignId('action_taken_by')->nullable()->after('action_taken')->constrained('users')->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'is_forum_restricted')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_forum_restricted');
            });
        }

        if (Schema::hasTable('discussion_reports')) {
            Schema::table('discussion_reports', function (Blueprint $table) {
                if (Schema::hasColumn('discussion_reports', 'action_taken_by')) {
                    $table->dropForeign(['action_taken_by']);
                    $table->dropColumn('action_taken_by');
                }
                if (Schema::hasColumn('discussion_reports', 'action_taken')) {
                    $table->dropColumn('action_taken');
                }
            });
        }
    }
};
