<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\DatabaseBackupService;

$service = new DatabaseBackupService();
$res = $service->createBackup('manual', 'zip');
echo "Backup Result:\n";
print_r($res);

// Verify ZIP contents
$zip = new ZipArchive();
if ($zip->open($res['filepath']) === true) {
    echo "ZIP Archive opened successfully!\n";
    echo "Total files in ZIP: " . $zip->numFiles . "\n";
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $stat = $zip->statIndex($i);
        echo " - " . $stat['name'] . " (Uncompressed Size: " . number_format($stat['size']) . " bytes)\n";
    }
    $zip->close();
} else {
    echo "Failed to open ZIP archive!\n";
}
