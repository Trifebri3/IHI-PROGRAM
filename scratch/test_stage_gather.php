<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Registration;
use App\Models\Program;
use Illuminate\Support\Facades\DB;

$programId = 11;
$regIds = Registration::where('program_id', $programId)->pluck('id');

// 1. Gather all stage fields
$stageRows = DB::table('registration_stage_data')
    ->join('program_stages', 'registration_stage_data.program_stage_id', '=', 'program_stages.id')
    ->whereIn('registration_stage_data.registration_id', $regIds)
    ->whereNotNull('registration_stage_data.form_values')
    ->orderBy('program_stages.id', 'asc')
    ->orderBy('registration_stage_data.id', 'asc')
    ->select('registration_stage_data.registration_id', 'program_stages.name as stage_name', 'registration_stage_data.form_values')
    ->get();

$allStageFieldNames = [];
$stageDataByReg = [];

foreach ($stageRows as $sr) {
    $vals = json_decode($sr->form_values, true);
    if (is_array($vals)) {
        foreach ($vals as $item) {
            $fName = trim($item['field_name'] ?? '');
            if ($fName !== '') {
                if (!in_array($fName, $allStageFieldNames)) {
                    $allStageFieldNames[] = $fName;
                }
                $v = $item['value'] ?? '';
                if (is_array($v)) {
                    $v = implode(', ', $v);
                }
                $v = (string)$v;
                // If it looks like a relative file upload path, convert to full URL
                if (str_starts_with($v, 'program_submissions/') || str_starts_with($v, 'uploads/')) {
                    $v = asset('storage/' . $v);
                }
                $stageDataByReg[$sr->registration_id][$fName] = $v;
            }
        }
    }
}

echo "Total Program 11 Registrations: " . count($regIds) . PHP_EOL;
echo "Total Stage Form Questions Found: " . count($allStageFieldNames) . PHP_EOL;
foreach ($allStageFieldNames as $idx => $fn) {
    echo "  " . ($idx + 1) . ". $fn\n";
}
