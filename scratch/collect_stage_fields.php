<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Registration;
use Illuminate\Support\Facades\DB;

$programId = 11;

// Collect all unique stage field names in this program, preserving stage order
$stageRows = DB::table('registration_stage_data')
    ->join('registrations', 'registration_stage_data.registration_id', '=', 'registrations.id')
    ->join('program_stages', 'registration_stage_data.program_stage_id', '=', 'program_stages.id')
    ->where('registrations.program_id', $programId)
    ->whereNotNull('registration_stage_data.form_values')
    ->orderBy('program_stages.id', 'asc')
    ->orderBy('registration_stage_data.id', 'asc')
    ->select('registration_stage_data.registration_id', 'program_stages.name as stage_name', 'registration_stage_data.form_values')
    ->get();

$allFieldNames = [];
foreach ($stageRows as $sr) {
    $vals = json_decode($sr->form_values, true);
    if (is_array($vals)) {
        foreach ($vals as $item) {
            $fName = trim($item['field_name'] ?? '');
            if ($fName !== '' && !in_array($fName, $allFieldNames)) {
                $allFieldNames[] = $fName;
            }
        }
    }
}

echo "Total Stage Form Fields found: " . count($allFieldNames) . PHP_EOL;
foreach ($allFieldNames as $i => $fn) {
    echo ($i + 1) . ". $fn" . PHP_EOL;
}
