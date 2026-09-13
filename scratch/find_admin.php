<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$u = \App\Models\User::whereHas('roles', fn($q)=>$q->where('name', 'Super Admin'))->first();
echo "Super Admin: " . $u->email . PHP_EOL;
