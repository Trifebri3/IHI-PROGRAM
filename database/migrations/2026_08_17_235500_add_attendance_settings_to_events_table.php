<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('attendance_method')->default('scan')->after('is_attendance_open');
            $table->boolean('attendance_require_ticket')->default(true)->after('attendance_method');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['attendance_method', 'attendance_require_ticket']);
        });
    }
};
