<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$logs = App\Models\AuditLog::latest()->take(5)->get();
foreach ($logs as $l) {
    echo "Log {$l->id}: {$l->action} - {$l->details} at {$l->created_at}\n";
}
