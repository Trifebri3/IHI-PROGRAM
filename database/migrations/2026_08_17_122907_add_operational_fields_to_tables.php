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
        Schema::table('programs', function (Blueprint $table) {
            $table->string('batch')->nullable()->after('quota');
            $table->string('location')->nullable()->after('batch');
            $table->string('region')->nullable()->after('location');
        });

        Schema::table('registrations', function (Blueprint $table) {
            $table->string('batch')->nullable()->after('secure_verification_token');
            $table->string('location')->nullable()->after('batch');
            $table->string('region')->nullable()->after('location');
            $table->string('participant_status')->default('active')->after('region'); // active, completed, withdrawn
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn(['batch', 'location', 'region']);
        });

        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn(['batch', 'location', 'region', 'participant_status']);
        });
    }
};
