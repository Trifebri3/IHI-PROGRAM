<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$unverified = User::whereHas('registrations', function ($q) {
    $q->where('program_id', 11);
})->whereNull('email_verified_at')->count();

echo "Unverified count: " . $unverified . PHP_EOL;

$controller = app()->make(App\Http\Controllers\SuperAdmin\ProgramParticipantController::class);
$user = User::whereHas('roles', fn($q) => $q->where('name', 'Super Admin'))->first();
auth()->login($user);

$req = Illuminate\Http\Request::create('/superadmin/program-participants', 'GET', ['program_id' => 11]);
$view = $controller->index($req);
$stats = $view->getData()['stats'];
echo "Stats from controller: " . json_encode($stats) . PHP_EOL;
