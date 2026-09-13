<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class DatabaseBackupService
{
    /**
     * Dapatkan path direktori penyimpanan backup
     */
    public function getBackupDirectory(): string
    {
        $dir = storage_path('app/backups');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return $dir;
    }

    /**
     * Deteksi lokasi binary mysqldump pada sistem
     */
    public function detectMysqldumpPath(): ?string
    {
        $candidates = [
            'C:\\xampp\\mysql\\bin\\mysqldump.exe',
            'C:\\laragon\\bin\\mysql\\current\\bin\\mysqldump.exe',
            '/usr/bin/mysqldump',
            '/usr/local/bin/mysqldump',
            '/opt/homebrew/bin/mysqldump',
        ];

        foreach ($candidates as $path) {
            if (file_exists($path) && is_executable($path)) {
                return $path;
            }
        }

        // Coba deteksi via shell command 'where' (Windows) atau 'which' (Linux)
        $lookupCmd = PHP_OS_FAMILY === 'Windows' ? 'where mysqldump 2>NUL' : 'which mysqldump 2>/dev/null';
        $discovered = trim((string) @shell_exec($lookupCmd));
        if (!empty($discovered)) {
            $lines = explode("\n", $discovered);
            $first = trim($lines[0]);
            if (file_exists($first)) {
                return $first;
            }
        }

        return null;
    }

    /**
     * Deteksi tabel yang rusak di engine database (misal error 1932) agar bisa di-skip secara aman
     */
    public function getBrokenTables(string $dbName): array
    {
        $broken = [];
        try {
            $tables = DB::select('SHOW TABLES');
            $colName = 'Tables_in_' . $dbName;

            foreach ($tables as $table) {
                $tableName = $table->$colName ?? null;
                if (!$tableName) continue;

                try {
                    DB::table($tableName)->limit(1)->count();
                } catch (\Throwable $e) {
                    $broken[] = $tableName;
                }
            }
        } catch (\Throwable $e) {
            Log::warning("Gagal mendeteksi status tabel database: " . $e->getMessage());
        }

        return $broken;
    }

    /**
     * Jalankan proses backup database
     *
     * @param string $type 'manual' atau 'auto'
     * @param string $format 'zip' atau 'sql'
     * @return array
     */
    public function createBackup(string $type = 'manual', string $format = 'zip'): array
    {
        $dir = $this->getBackupDirectory();
        $dbConfig = config('database.connections.mysql');
        $dbName = $dbConfig['database'] ?? 'database';
        $timestamp = date('Y-m-d_H-i-s');
        $baseName = "backup_{$type}_{$dbName}_{$timestamp}";
        $sqlPath = $dir . DIRECTORY_SEPARATOR . "{$baseName}.sql";

        $success = false;
        $methodUsed = '';

        // 1. Coba metode mysqldump terlebih dahulu
        $mysqldumpPath = $this->detectMysqldumpPath();
        if ($mysqldumpPath) {
            $brokenTables = $this->getBrokenTables($dbName);
            $ignoreArgs = '';
            foreach ($brokenTables as $bt) {
                $ignoreArgs .= " --ignore-table=\"{$dbName}.{$bt}\"";
            }

            $host = $dbConfig['host'] ?? '127.0.0.1';
            $port = $dbConfig['port'] ?? 3306;
            $username = $dbConfig['username'] ?? 'root';
            $password = $dbConfig['password'] ?? '';

            $passArg = $password !== '' ? "-p\"{$password}\"" : '';
            $cmd = "\"{$mysqldumpPath}\" --host=\"{$host}\" --port={$port} --user=\"{$username}\" {$passArg} --default-character-set=utf8mb4 --single-transaction --quick --routines --triggers {$ignoreArgs} \"{$dbName}\" --result-file=\"{$sqlPath}\"";

            $returnVar = 0;
            $output = [];
            exec($cmd . ' 2>&1', $output, $returnVar);

            if ($returnVar === 0 && file_exists($sqlPath) && filesize($sqlPath) > 0) {
                $success = true;
                $methodUsed = 'mysqldump';
            } else {
                Log::warning("mysqldump gagal atau menghasilkan file kosong, beralih ke PDO Dumper PHP: " . implode(' ', $output));
                if (file_exists($sqlPath)) {
                    @unlink($sqlPath);
                }
            }
        }

        // 2. Fallback: Dump via pure PHP PDO jika mysqldump tidak tersedia/gagal
        if (!$success) {
            $success = $this->dumpViaPdo($dbName, $sqlPath);
            $methodUsed = 'php_pdo';
        }

        if (!$success || !file_exists($sqlPath) || filesize($sqlPath) === 0) {
            throw new \RuntimeException("Gagal membuat backup database. Silakan periksa izin direktori storage atau koneksi database.");
        }

        $finalPath = $sqlPath;
        $finalFilename = basename($sqlPath);

        // 3. Kompresi ke ZIP jika diminta
        if ($format === 'zip' && class_exists(ZipArchive::class)) {
            $zipPath = $dir . DIRECTORY_SEPARATOR . "{$baseName}.zip";
            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                $zip->addFile($sqlPath, basename($sqlPath));
                $zip->close();

                if (file_exists($zipPath) && filesize($zipPath) > 0) {
                    @unlink($sqlPath); // Hapus .sql mentah untuk menghemat ruang disk
                    $finalPath = $zipPath;
                    $finalFilename = basename($zipPath);
                }
            }
        }

        // 4. Bersihkan file backup lama sesuai limit retensi
        $this->pruneOldBackups();

        // 5. Update timestamp jika tipe auto
        if ($type === 'auto') {
            SystemSetting::setVal('auto_backup_last_run', now()->toDateTimeString());
        }

        $sizeBytes = filesize($finalPath);

        return [
            'success'        => true,
            'filename'       => $finalFilename,
            'filepath'       => $finalPath,
            'size_bytes'     => $sizeBytes,
            'size_formatted' => $this->formatFileSize($sizeBytes),
            'method'         => $methodUsed,
            'type'           => $type,
            'format'         => pathinfo($finalFilename, PATHINFO_EXTENSION),
            'created_at'     => now()->toDateTimeString(),
        ];
    }

    /**
     * Fallback Dumper database berbasis PDO PHP (Portabel & Aman)
     */
    private function dumpViaPdo(string $dbName, string $outputPath): bool
    {
        $handle = fopen($outputPath, 'w');
        if (!$handle) return false;

        $timestamp = date('Y-m-d H:i:s');
        fwrite($handle, "-- IHI Database Backup (Pure PHP PDO Fallback)\n");
        fwrite($handle, "-- Database: {$dbName}\n");
        fwrite($handle, "-- Date: {$timestamp}\n");
        fwrite($handle, "--------------------------------------------------------\n\n");
        fwrite($handle, "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\n");
        fwrite($handle, "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\n");
        fwrite($handle, "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\n");
        fwrite($handle, "/*!40101 SET NAMES utf8mb4 */;\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n");
        fwrite($handle, "SET SQL_MODE=\"NO_AUTO_VALUE_ON_ZERO\";\n\n");

        $tables = DB::select('SHOW TABLES');
        $colName = 'Tables_in_' . $dbName;
        $pdo = DB::connection()->getPdo();

        foreach ($tables as $t) {
            $table = $t->$colName ?? null;
            if (!$table) continue;

            // Skip tabel rusak
            try {
                DB::table($table)->limit(1)->count();
            } catch (\Throwable $e) {
                continue;
            }

            fwrite($handle, "--\n-- Struktur tabel `{$table}`\n--\n\n");
            fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n");

            $createRow = DB::select("SHOW CREATE TABLE `{$table}`");
            if (!empty($createRow)) {
                $createSql = $createRow[0]->{'Create Table'} ?? null;
                if ($createSql) {
                    fwrite($handle, $createSql . ";\n\n");
                }
            }

            // Dump data baris secara bertahap (chunking)
            fwrite($handle, "--\n-- Data untuk tabel `{$table}`\n--\n\n");

            $query = DB::table($table)->orderBy(DB::raw('1'));
            $query->chunk(500, function ($rows) use ($handle, $table, $pdo) {
                if ($rows->isEmpty()) return;

                $inserts = [];
                foreach ($rows as $row) {
                    $rowArray = (array) $row;
                    $values = [];
                    foreach ($rowArray as $val) {
                        if (is_null($val)) {
                            $values[] = "NULL";
                        } elseif (is_numeric($val) && !preg_match('/^0[0-9]+/', (string)$val)) {
                            $values[] = $val;
                        } else {
                            $values[] = $pdo->quote((string)$val);
                        }
                    }
                    $inserts[] = "(" . implode(', ', $values) . ")";
                }

                if (!empty($inserts)) {
                    fwrite($handle, "INSERT INTO `{$table}` VALUES \n" . implode(",\n", $inserts) . ";\n");
                }
            });

            fwrite($handle, "\n");
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        fwrite($handle, "/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\n");
        fwrite($handle, "/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\n");
        fwrite($handle, "/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;\n");
        fclose($handle);

        return true;
    }

    /**
     * Dapatkan daftar seluruh file backup yang tersimpan di storage
     */
    public function getBackupFiles(): array
    {
        $dir = $this->getBackupDirectory();
        $files = scandir($dir);
        $backups = [];

        foreach ($files as $file) {
            if ($file === '.' || $file === '..' || $file === '.gitignore') continue;

            $fullPath = $dir . DIRECTORY_SEPARATOR . $file;
            if (!is_file($fullPath)) continue;

            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (!in_array($ext, ['sql', 'zip', 'gz'])) continue;

            $size = filesize($fullPath);
            $mtime = filemtime($fullPath);

            $type = 'manual';
            if (str_contains($file, '_auto_')) {
                $type = 'auto';
            }

            $backups[] = [
                'filename'       => $file,
                'path'           => $fullPath,
                'extension'      => $ext,
                'size_bytes'     => $size,
                'size_formatted' => $this->formatFileSize($size),
                'type'           => $type,
                'created_at'     => Carbon::createFromTimestamp($mtime),
                'timestamp'      => $mtime,
            ];
        }

        // Urutkan dari yang paling baru
        usort($backups, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);

        return $backups;
    }

    /**
     * Hapus file backup lama agar tidak melebihi batas retensi maksimum
     */
    public function pruneOldBackups(): int
    {
        $maxFiles = (int) SystemSetting::getVal('auto_backup_max_files', '10');
        if ($maxFiles < 1) $maxFiles = 10;

        $backups = $this->getBackupFiles();
        $deleted = 0;

        if (count($backups) > $maxFiles) {
            $toDelete = array_slice($backups, $maxFiles);
            foreach ($toDelete as $b) {
                if (file_exists($b['path'])) {
                    @unlink($b['path']);
                    $deleted++;
                }
            }
        }

        return $deleted;
    }

    /**
     * Hapus file backup spesifik secara aman
     */
    public function deleteBackup(string $filename): bool
    {
        // Sanitasi nama file untuk mencegah directory traversal
        $cleanName = basename($filename);
        $fullPath = $this->getBackupDirectory() . DIRECTORY_SEPARATOR . $cleanName;

        if (file_exists($fullPath) && is_file($fullPath)) {
            return @unlink($fullPath);
        }

        return false;
    }

    /**
     * Dapatkan path absolut file backup spesifik untuk proses unduh
     */
    public function getDownloadPath(string $filename): ?string
    {
        $cleanName = basename($filename);
        $fullPath = $this->getBackupDirectory() . DIRECTORY_SEPARATOR . $cleanName;

        if (file_exists($fullPath) && is_file($fullPath)) {
            return $fullPath;
        }

        return null;
    }

    /**
     * Evaluasi dan jalankan auto backup jika sudah saatnya sesuai jadwal
     */
    public function checkAndRunScheduledAutoBackup(): ?array
    {
        $isEnabled = SystemSetting::getVal('auto_backup_enabled', '1') === '1';
        if (!$isEnabled) return null;

        $frequency = SystemSetting::getVal('auto_backup_frequency', 'daily');
        $lastRunStr = SystemSetting::getVal('auto_backup_last_run');
        $format = SystemSetting::getVal('auto_backup_format', 'zip');

        $isDue = false;

        if (empty($lastRunStr)) {
            $isDue = true;
        } else {
            $lastRun = Carbon::parse($lastRunStr);
            $now = now();

            $isDue = match($frequency) {
                'every_12_hours' => $now->diffInHours($lastRun) >= 12,
                'weekly'         => $now->diffInDays($lastRun) >= 7,
                default          => $now->diffInHours($lastRun) >= 23, // 'daily'
            };
        }

        if ($isDue) {
            try {
                return $this->createBackup('auto', $format);
            } catch (\Throwable $e) {
                Log::error("Gagal menjalankan auto-backup terjadwal: " . $e->getMessage());
            }
        }

        return null;
    }

    /**
     * Format ukuran file ke format yang mudah dibaca (KB / MB)
     */
    public function formatFileSize(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        }

        return $bytes . ' B';
    }
}
