<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\DatabaseBackupService;

$svc = new DatabaseBackupService();
$res = $svc->createBackup('manual', 'sql'); // make .sql first to inspect tables in it
echo "SQL Backup created:\n";
print_r($res);

$content = file_get_contents($res['filepath']);
preg_match_all('/CREATE TABLE `?([a-zA-Z0-9_]+)`?/', $content, $matches);
$foundTables = array_unique($matches[1] ?? []);
echo "Found " . count($foundTables) . " tables in dumped SQL file:\n";
print_r($foundTables);

// Check if alumni_certificates is in it:
echo "Contains alumni_certificates? " . (in_array('alumni_certificates', $foundTables) ? 'YES' : 'NO') . PHP_EOL;
echo "Contains users? " . (in_array('users', $foundTables) ? 'YES' : 'NO') . PHP_EOL;
echo "Contains registrations? " . (in_array('registrations', $foundTables) ? 'YES' : 'NO') . PHP_EOL;

// Clean up test sql
@unlink($res['filepath']);
