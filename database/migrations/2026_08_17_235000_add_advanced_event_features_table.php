<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->text('attendance_form_schema')->nullable()->after('form_schema');
            $table->string('certificate_link')->nullable()->after('certificate_template_path');
        });

        Schema::table('event_registrations', function (Blueprint $table) {
            $table->string('ticket_number')->nullable()->unique()->after('event_id');
            $table->text('attendance_form_values')->nullable()->after('form_values');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['attendance_form_schema', 'certificate_link']);
        });

        Schema::table('event_registrations', function (Blueprint $table) {
            $table->dropColumn(['ticket_number', 'attendance_form_values']);
        });
    }
};
