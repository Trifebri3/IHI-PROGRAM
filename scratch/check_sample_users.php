<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Registration;

$emails = [
    'hilyaaa264@gmail.com',
    'hariyatinurr@gmail.com',
    'intandaraphonna00@gmail.com',
    'rizalkurnia110@gmail.com',
    'fawziyaane1@gmail.com'
];

foreach ($emails as $email) {
    $u = User::where('email', $email)->first();
    if (!$u) {
        echo "Email: $email -> USER NOT FOUND\n";
        continue;
    }
    $reg = Registration::where('program_id', 11)->where('user_id', $u->id)->first();
    echo "Email: $email -> User: {$u->name} (ID {$u->id}) -> Reg Status: " . ($reg ? $reg->status : 'NO REG') . PHP_EOL;
}
