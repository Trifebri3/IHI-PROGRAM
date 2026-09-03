<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambahkan repost_of_id pada tabel discussions
        if (!Schema::hasColumn('discussions', 'repost_of_id')) {
            Schema::table('discussions', function (Blueprint $table) {
                $table->foreignId('repost_of_id')->nullable()->after('user_id')->constrained('discussions')->onDelete('cascade');
            });
        }

        // 2. Buat tabel discussion_favorites (Fitur Favorit / Bookmark)
        if (!Schema::hasTable('discussion_favorites')) {
            Schema::create('discussion_favorites', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('discussion_id')->constrained()->onDelete('cascade');
                $table->timestamps();

                $table->unique(['user_id', 'discussion_id']);
            });
        }

        // 3. Buat tabel discussion_reports (Fitur Laporkan Diskusi)
        if (!Schema::hasTable('discussion_reports')) {
            Schema::create('discussion_reports', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('discussion_id')->constrained()->onDelete('cascade');
                $table->string('reason');
                $table->text('notes')->nullable();
                $table->string('status')->default('pending'); // pending, reviewed, dismissed
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('discussion_reports');
        Schema::dropIfExists('discussion_favorites');
        if (Schema::hasColumn('discussions', 'repost_of_id')) {
            Schema::table('discussions', function (Blueprint $table) {
                $table->dropForeign(['repost_of_id']);
                $table->dropColumn('repost_of_id');
            });
        }
    }
};
