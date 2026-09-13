<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Registration;
use App\Models\Address;

// Cari 1 peserta di program 11 yang provinsinya masih kosong
$targetReg = Registration::where('program_id', 11)->where('status', 'passed')
    ->whereDoesntHave('user.address', fn($q) => $q->whereNotNull('provinsi')->where('provinsi', '!=', '')->where('provinsi', '!=', '-'))
    ->first();

if (!$targetReg) {
    echo "No target without provinsi found!\n";
    exit;
}

$u = $targetReg->user;
echo "Target User: ID {$u->id}, Name: {$u->name}, Email: {$u->email}\n";
echo "Current Provinsi: " . ($u->address?->provinsi ?: 'KOSONG') . PHP_EOL;

// Uji coba mapping header dinamis
$sampleText = "Nama Lengkap\tEmail\tProvinsi\tKabupaten\n{$u->name}\t{$u->email}\tAceh\tBanda Aceh";
echo "Sample Text to parse:\n" . $sampleText . PHP_EOL;
