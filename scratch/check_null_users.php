<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$nullVerifiedAll = User::whereNull('email_verified_at')->count();
echo "Total users in DB with null email_verified_at: " . $nullVerifiedAll . PHP_EOL;

$sampleNull = User::whereNull('email_verified_at')->take(5)->get(['id', 'name', 'email']);
echo "Sample null: " . json_encode($sampleNull) . PHP_EOL;
