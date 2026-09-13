<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Registration;

$total = Registration::where('program_id', 11)->count();
$passed = Registration::where('program_id', 11)->where('status', 'passed')->count();
$process = Registration::where('program_id', 11)->where('status', 'process')->count();
$other = Registration::where('program_id', 11)->whereNotIn('status', ['passed', 'process'])->pluck('status')->unique();

echo "Program 11 Registrations:\n";
echo "Total: $total\n";
echo "Passed: $passed\n";
echo "Process: $process\n";
echo "Other statuses: " . json_encode($other) . "\n";
