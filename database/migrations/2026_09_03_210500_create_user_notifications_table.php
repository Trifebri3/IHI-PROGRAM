<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('user_notifications')) {
            Schema::create('user_notifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('actor_id')->nullable()->constrained('users')->cascadeOnDelete();
                $table->foreignId('discussion_id')->nullable()->constrained('discussions')->cascadeOnDelete();
                $table->foreignId('comment_id')->nullable()->constrained('discussion_comments')->cascadeOnDelete();
                $table->string('type'); // reaction, comment, reply, mention, repost
                $table->text('data')->nullable(); // extra payload (emoji, snippet, etc.)
                $table->timestamp('read_at')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'read_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_notifications');
    }
};
