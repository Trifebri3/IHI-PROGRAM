<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

$unverified = User::whereHas('registrations', fn($q) => $q->where('program_id', 11))
    ->whereNull('email_verified_at')
    ->count();

$total = User::whereHas('registrations', fn($q) => $q->where('program_id', 11))->count();

$unverifiedPassed = User::whereHas('registrations', fn($q) => $q->where('program_id', 11)->where('status', 'passed'))
    ->whereNull('email_verified_at')
    ->count();

echo "Total users in Program 11: " . $total . PHP_EOL;
echo "Unverified users (all in prog 11): " . $unverified . PHP_EOL;
echo "Unverified users (passed in prog 11): " . $unverifiedPassed . PHP_EOL;
