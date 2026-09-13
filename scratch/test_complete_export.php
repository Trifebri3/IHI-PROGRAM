<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Registration;
use Illuminate\Support\Facades\DB;

$programId = 11;

// 1. Get all stage field questions
$stageRows = DB::table('registration_stage_data')
    ->join('registrations', 'registration_stage_data.registration_id', '=', 'registrations.id')
    ->join('program_stages', 'registration_stage_data.program_stage_id', '=', 'program_stages.id')
    ->where('registrations.program_id', $programId)
    ->whereNotNull('registration_stage_data.form_values')
    ->orderBy('program_stages.id', 'asc')
    ->orderBy('registration_stage_data.id', 'asc')
    ->select('registration_stage_data.registration_id', 'program_stages.name as stage_name', 'registration_stage_data.form_values')
    ->get();

$allStageFieldNames = [];
$stageAnswersByRegId = [];

foreach ($stageRows as $sr) {
    $vals = json_decode($sr->form_values, true);
    if (is_array($vals)) {
        foreach ($vals as $item) {
            $fName = trim($item['field_name'] ?? '');
            if ($fName !== '') {
                if (!in_array($fName, $allStageFieldNames)) {
                    $allStageFieldNames[] = $fName;
                }
                $val = $item['value'] ?? '';
                if (is_array($val)) {
                    $val = implode(', ', $val);
                }
                $stageAnswersByRegId[$sr->registration_id][$fName] = $val;
            }
        }
    }
}

// 2. Program Biodata Submissions
$bioSubmissions = DB::table('program_biodata_submissions')
    ->where('program_id', $programId)
    ->get();
$progBioAnswersByUserId = [];
$allProgBioFieldNames = [];
foreach ($bioSubmissions as $bs) {
    $ans = json_decode($bs->submitted_answers, true);
    if (is_array($ans)) {
        foreach ($ans as $k => $v) {
            if (!in_array($k, $allProgBioFieldNames)) {
                $allProgBioFieldNames[] = $k;
            }
            $progBioAnswersByUserId[$bs->user_id][$k] = is_array($v) ? implode(', ', $v) : (string)$v;
        }
    }
}

echo "Total Stage Fields: " . count($allStageFieldNames) . PHP_EOL;
echo "Total Program Biodata Fields: " . count($allProgBioFieldNames) . PHP_EOL;

// 3. Test for Adinda (reg 2148)
$sampleAnswers = $stageAnswersByRegId[2148] ?? [];
echo "\nSample Stage Answers for Reg 2148:\n";
foreach ($allStageFieldNames as $fn) {
    $ans = $sampleAnswers[$fn] ?? '-';
    echo "- $fn: " . (strlen($ans) > 50 ? substr($ans, 0, 50) . '...' : $ans) . PHP_EOL;
}
