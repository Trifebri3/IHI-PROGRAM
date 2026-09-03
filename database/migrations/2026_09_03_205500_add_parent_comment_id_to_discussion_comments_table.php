<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('discussion_comments', 'parent_comment_id')) {
            Schema::table('discussion_comments', function (Blueprint $table) {
                $table->foreignId('parent_comment_id')
                      ->nullable()
                      ->after('discussion_id')
                      ->constrained('discussion_comments')
                      ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('discussion_comments', 'parent_comment_id')) {
            Schema::table('discussion_comments', function (Blueprint $table) {
                $table->dropForeign(['parent_comment_id']);
                $table->dropColumn('parent_comment_id');
            });
        }
    }
};
