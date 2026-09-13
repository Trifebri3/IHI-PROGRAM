<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Registration;

$regs = Registration::where('program_id', 11)->where('status', 'passed')->with('user.address')->get();

$noProvCount = 0;
$hasProvCount = 0;

foreach ($regs as $r) {
    $p = trim((string)($r->user?->address?->provinsi ?? ''));
    if ($p === '' || $p === '-' || strtolower($p) === 'null') {
        $noProvCount++;
    } else {
        $hasProvCount++;
    }
}

echo "Total passed in Program 11: " . $regs->count() . PHP_EOL;
echo "Passed with Provinsi: " . $hasProvCount . PHP_EOL;
echo "Passed WITHOUT Provinsi: " . $noProvCount . PHP_EOL;
