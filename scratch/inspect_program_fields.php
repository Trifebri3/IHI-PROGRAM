<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Program;
use App\Models\Registration;
use Illuminate\Support\Facades\DB;

$programId = 11;

// 1. Program Stages
$stages = DB::table('program_stages')->where('program_id', $programId)->orderBy('id')->get();
echo "Program Stages for Program $programId:\n";
foreach ($stages as $s) {
    $sArr = (array)$s;
    $name = $sArr['name'] ?? $sArr['stage_name'] ?? $sArr['title'] ?? 'Unknown';
    echo "- Stage [{$s->id}] {$name}\n";
}

// 2. Form fields in registration_stage_data
$allStageData = DB::table('registration_stage_data')
    ->join('registrations', 'registration_stage_data.registration_id', '=', 'registrations.id')
    ->where('registrations.program_id', $programId)
    ->whereNotNull('form_values')
    ->limit(50)
    ->get(['registration_stage_data.form_values']);

$stageFieldNames = [];
foreach ($allStageData as $row) {
    $vals = json_decode($row->form_values, true);
    if (is_array($vals)) {
        foreach ($vals as $item) {
            if (isset($item['field_name'])) {
                $stageFieldNames[$item['field_name']] = true;
            }
        }
    }
}
echo "\nUnique Stage Field Names found in submissions (" . count($stageFieldNames) . "):\n";
foreach (array_keys($stageFieldNames) as $fn) {
    echo " * $fn\n";
}

// 3. Program Biodata Schema
$bioSchemas = DB::table('program_biodata_schemas')->where('program_id', $programId)->get();
echo "\nProgram Biodata Schemas for Program $programId: " . count($bioSchemas) . "\n";
foreach ($bioSchemas as $bs) {
    echo " * Schema: {$bs->field_label} ({$bs->field_type})\n";
}

// 4. Program Biodata Submissions
$bioSubmissions = DB::table('program_biodata_submissions')
    ->where('program_id', $programId)
    ->limit(10)
    ->get();
echo "\nProgram Biodata Submissions: " . count($bioSubmissions) . "\n";
foreach ($bioSubmissions as $bs) {
    print_r(json_decode($bs->submitted_answers, true));
}
