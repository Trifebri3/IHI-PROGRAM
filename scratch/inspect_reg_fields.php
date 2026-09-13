<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Registration;

$regs = Registration::where('program_id', 11)->limit(10)->get();
foreach ($regs as $r) {
    echo "ID: {$r->id}, NI: {$r->final_id_number}, Loc: {$r->location}, Region: {$r->region}, Motiv: " . substr($r->motivation ?? '', 0, 30) . ", Scores: {$r->final_scores}\n";
}
