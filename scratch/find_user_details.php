<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Registration;
use Illuminate\Support\Facades\DB;

$userId = 1575; // Adinda Putri Afrilia

echo "=== USER_BIODATA_VALUES ===\n";
$bioValues = DB::table('user_biodata_values')
    ->join('biodata_fields', 'user_biodata_values.biodata_field_id', '=', 'biodata_fields.id')
    ->where('user_biodata_values.user_id', $userId)
    ->select('biodata_fields.name', 'user_biodata_values.value')
    ->get();
foreach ($bioValues as $bv) {
    echo "{$bv->name}: {$bv->value}\n";
}

echo "\n=== REGISTRATION_STAGE_DATA ===\n";
$stageData = DB::table('registration_stage_data')
    ->where('registration_id', 2148)
    ->get();
foreach ($stageData as $sd) {
    echo "Stage ID {$sd->program_stage_id}:\n";
    print_r(json_decode($sd->form_values, true));
}

echo "\n=== PROGRAM_BIODATA_SUBMISSIONS ===\n";
$progBio = DB::table('program_biodata_submissions')
    ->where('user_id', $userId)
    ->where('program_id', 11)
    ->get();
foreach ($progBio as $pb) {
    echo "Submitted Answers:\n";
    print_r(json_decode($pb->submitted_answers, true));
}
