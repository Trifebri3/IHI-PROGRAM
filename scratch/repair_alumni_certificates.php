<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

try {
    DB::statement('SET FOREIGN_KEY_CHECKS = 0');
    DB::statement('DROP TABLE IF EXISTS `alumni_certificates`');
    echo "Dropped orphaned table successfully!\n";
    
    Schema::create('alumni_certificates', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
        $table->foreignId('alumni_program_id')->constrained('alumni_programs')->cascadeOnDelete();
        $table->string('certificate_number')->unique()->nullable();
        $table->string('file_path');
        $table->uuid('uuid')->unique();
        $table->json('extra_info')->nullable();
        $table->timestamps();
    });
    echo "Recreated alumni_certificates table successfully!\n";
    DB::statement('SET FOREIGN_KEY_CHECKS = 1');
} catch (\Throwable $e) {
    DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
