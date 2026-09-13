<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$u = \App\Models\User::where('email', 'adminihi@gmail.com')->first();
$passwords = ['password', 'admin123', 'admin', 'superadmin', 'ihi@2026', 'password123', 'adminihi'];
foreach ($passwords as $p) {
    if (Illuminate\Support\Facades\Hash::check($p, $u->password)) {
        echo "Password matched: " . $p . PHP_EOL;
        exit;
    }
}
echo "No match in common passwords\n";
