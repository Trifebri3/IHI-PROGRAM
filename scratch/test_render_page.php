<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::whereHas('roles', fn($q) => $q->where('name', 'Super Admin'))->first();
auth()->login($user);

$req = Illuminate\Http\Request::create('/superadmin/program-participants', 'GET', ['program_id' => 11]);
$res = app()->handle($req);

$content = $res->getContent();
echo "HTTP Status: " . $res->getStatusCode() . PHP_EOL;
echo "Contains 'Semua Email Terverifikasi': " . (str_contains($content, 'Semua Email Terverifikasi') ? 'YES' : 'NO') . PHP_EOL;
echo "Contains 'Verifikasi Email': " . (str_contains($content, 'Verifikasi Email') ? 'YES' : 'NO') . PHP_EOL;
echo "Contains 'Terverifikasi': " . (str_contains($content, 'Terverifikasi') ? 'YES' : 'NO') . PHP_EOL;
