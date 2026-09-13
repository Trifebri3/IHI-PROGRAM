<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;

class DatabaseBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup 
                            {--type=auto : Tipe backup (manual atau auto)}
                            {--format=zip : Format file (zip atau sql)}
                            {--force : Paksa eksekusi tanpa mengecek interval jadwal}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membuat backup database MySQL (dump schema & data terkompresi)';

    /**
     * Execute the console command.
     */
    public function handle(DatabaseBackupService $backupService): int
    {
        $type = $this->option('type') ?: 'auto';
        $format = $this->option('format') ?: 'zip';
        $isForce = $this->option('force');

        $this->info("Memulai proses backup database [Tipe: {$type}, Format: {$format}]...");

        try {
            if ($type === 'auto' && !$isForce) {
                $result = $backupService->checkAndRunScheduledAutoBackup();
                if (!$result) {
                    $this->comment("Auto-backup belum jatuh tempo jadwal atau dinonaktifkan di pengaturan.");
                    return Command::SUCCESS;
                }
            } else {
                $result = $backupService->createBackup($type, $format);
            }

            $this->info("Berhasil membuat backup database!");
            $this->table(
                ['File', 'Ukuran', 'Metode', 'Tipe'],
                [[$result['filename'], $result['size_formatted'], $result['method'], $result['type']]]
            );

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Gagal membuat backup database: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
