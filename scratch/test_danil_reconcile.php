<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Http\Controllers\SuperAdmin\ProgramParticipantController;
use Illuminate\Http\Request;

$admin = User::whereHas('roles', fn($q) => $q->where('name', 'Super Admin'))->first();
auth()->login($admin);

$pastedText = <<<TXT
Tgk. Muhammad Danil S.E\tmuhamaddani11032002@gmail.com
Yuke luis adipsah\tyuke.230330003@mhs.unimal.ac.id
Yulizar Febry Ansyah\tyulizar082167@gmail.com
Zhafira Febrina\tfebrinazhafira5@gmail.com
Adrian Reinhard Pelupessy\tadrn21plpssy@gmail.com
TXT;

$req = Request::create('/superadmin/program-participants/11/reconciliation/process', 'POST', [
    'pasted_data' => $pastedText,
]);

$controller = app()->make(ProgramParticipantController::class);
$view = $controller->processReconciliation($req, 11);
$result = $view->getData()['comparisonResult'];

echo "Total Sheet: " . $result['stats']['total_sheet'] . PHP_EOL;
echo "Already Passed: " . $result['stats']['already_passed'] . PHP_EOL;
echo "Not Registered: " . $result['stats']['not_registered'] . PHP_EOL;
echo "Registered Not Passed: " . $result['stats']['registered_not_passed'] . PHP_EOL;
echo "Skipped Rows: " . count($result['skipped_rows']) . PHP_EOL;
echo "First Item Name: " . $result['items'][0]['name'] . " (" . $result['items'][0]['email'] . ") Status: " . $result['items'][0]['status'] . PHP_EOL;
echo "Last Item Name: " . end($result['items'])['name'] . " (" . end($result['items'])['email'] . ")" . PHP_EOL;
