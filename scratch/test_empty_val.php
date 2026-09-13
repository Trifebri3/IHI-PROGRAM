<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Registration;
use App\Models\Address;

// Cari 1 peserta di program 11 yang alamatnya masih kosong atau buat data uji
$reg = Registration::where('program_id', 11)->where('status', 'passed')->with('user.address')->first();
$u = $reg->user;
$addr = $u->address;

echo "Participant: {$u->name} ({$u->email})\n";
echo "Current Provinsi: " . ($addr?->provinsi ?: 'KOSONG') . PHP_EOL;

// Test helper isEmptyValue
function isEmptyValue($val): bool {
    if (is_null($val)) return true;
    $t = trim((string)$val);
    return ($t === '' || $t === '-' || strtolower($t) === 'null');
}

echo "isEmptyValue(''): " . (isEmptyValue('') ? 'YES' : 'NO') . PHP_EOL;
echo "isEmptyValue('-'): " . (isEmptyValue('-') ? 'YES' : 'NO') . PHP_EOL;
echo "isEmptyValue(null): " . (isEmptyValue(null) ? 'YES' : 'NO') . PHP_EOL;
echo "isEmptyValue('Aceh'): " . (isEmptyValue('Aceh') ? 'YES' : 'NO') . PHP_EOL;
