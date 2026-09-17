<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('piagam_participant_grades', function (Blueprint $table) {
            $table->dropForeign(['grading_component_id']);
            $table->dropColumn('grading_component_id');
            $table->string('variable_name')->after('participant_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('piagam_participant_grades', function (Blueprint $table) {
            $table->dropColumn('variable_name');
            $table->foreignId('grading_component_id')->nullable()->constrained('piagam_grading_components')->cascadeOnDelete();
        });
    }
};
