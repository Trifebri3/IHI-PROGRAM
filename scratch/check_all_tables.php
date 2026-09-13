<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = DB::select('SHOW TABLE STATUS');
echo "Total Tables in DB: " . count($tables) . PHP_EOL;

$totalRows = 0;
$brokenTables = [];
foreach ($tables as $t) {
    $tName = $t->Name;
    $engine = $t->Engine;
    $rows = $t->Rows;
    try {
        $actualRows = DB::table($tName)->count();
        $totalRows += $actualRows;
        // echo "$tName ($engine): $actualRows rows\n";
    } catch (\Throwable $e) {
        $brokenTables[] = ['name' => $tName, 'engine' => $engine, 'error' => $e->getMessage()];
        echo "BROKEN: $tName - " . $e->getMessage() . PHP_EOL;
    }
}

echo "Total Valid Records across all tables: $totalRows" . PHP_EOL;
echo "Broken tables count: " . count($brokenTables) . PHP_EOL;
