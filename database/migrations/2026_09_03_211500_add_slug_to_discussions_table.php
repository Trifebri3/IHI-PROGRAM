<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('discussions', 'slug')) {
            Schema::table('discussions', function (Blueprint $table) {
                $table->string('slug')->nullable()->unique()->after('title');
            });

            // Generate unique slugs for all existing discussions
            $discussions = \App\Models\Discussion::all();
            foreach ($discussions as $d) {
                $base = Str::slug($d->title);
                if (empty($base)) {
                    $base = 'topik';
                }
                $slug = $base . '-' . $d->id;
                $d->updateQuietly(['slug' => $slug]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('discussions', 'slug')) {
            Schema::table('discussions', function (Blueprint $table) {
                $table->dropColumn('slug');
            });
        }
    }
};
