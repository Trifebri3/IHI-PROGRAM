<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Registration;

$u = User::where('email', 'muhamaddani11032002@gmail.com')->first();
echo "User: " . ($u ? "{$u->id} - {$u->name} ({$u->email})" : "NOT FOUND") . PHP_EOL;

if ($u) {
    $reg = Registration::where('program_id', 11)->where('user_id', $u->id)->first();
    echo "Reg: " . ($reg ? "Status: {$reg->status}" : "NO REGISTRATION") . PHP_EOL;
}
