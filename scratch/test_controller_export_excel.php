<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Http\Controllers\SuperAdmin\ProgramParticipantController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

$admin = User::where('email', 'adminihi@gmail.com')->first();
Auth::login($admin);

$controller = new ProgramParticipantController();
$req = Request::create('/superadmin/program-participants/11/export-excel?scope=all', 'GET');

ob_start();
$response = $controller->exportExcel(11, $req);
$response->sendContent();
$csvOutput = ob_get_clean();

$lines = explode("\n", trim($csvOutput));
echo "Total lines generated: " . count($lines) . PHP_EOL;

// First line is BOM + sep=;
echo "Line 1: " . trim($lines[0]) . PHP_EOL;
// Line 2 is header
$headers = str_getcsv($lines[1], ';');
echo "Total Header Columns: " . count($headers) . PHP_EOL;
echo "First 10 Headers:\n";
print_r(array_slice($headers, 0, 10));
echo "Headers 20-30:\n";
print_r(array_slice($headers, 20, 10));
echo "Last 10 Headers:\n";
print_r(array_slice($headers, -10));

// Row 1 (first participant)
$firstParticipantRow = str_getcsv($lines[2], ';');
echo "Row 1 Columns: " . count($firstParticipantRow) . PHP_EOL;
echo "Row 1 Name: " . ($firstParticipantRow[2] ?? 'N/A') . PHP_EOL;
echo "Row 1 NI: " . ($firstParticipantRow[1] ?? 'N/A') . PHP_EOL;
echo "Row 1 Motivasi: " . substr($firstParticipantRow[28] ?? 'N/A', 0, 40) . PHP_EOL;

// Check how many data rows (excluding sep and header)
echo "Total Participants in CSV: " . (count($lines) - 2) . PHP_EOL;
