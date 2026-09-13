<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SystemSetting;
use App\Services\DatabaseBackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DatabaseBackupController extends Controller
{
    protected DatabaseBackupService $backupService;

    public function __construct(DatabaseBackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    /**
     * Memastikan hanya Super Admin yang memiliki akses
     */
    protected function authorizeSuperAdmin(): void
    {
        if (!Auth::check() || !Auth::user()->hasRole('Super Admin')) {
            abort(403, 'Akses terbatas hanya untuk Super Admin.');
        }
    }

    /**
     * Tampilkan halaman utama Backup & Auto-Backup Database
     */
    public function index()
    {
        $this->authorizeSuperAdmin();

        // Cek apakah ada jadwal auto backup yang jatuh tempo (Web-Cron opportunistik)
        $this->backupService->checkAndRunScheduledAutoBackup();

        // Ambil daftar file backup
        $backups = $this->backupService->getBackupFiles();

        // Pengaturan Auto-Backup
        $autoSettings = [
            'enabled'    => SystemSetting::getVal('auto_backup_enabled', '1') === '1',
            'frequency'  => SystemSetting::getVal('auto_backup_frequency', 'daily'),
            'max_files'  => (int) SystemSetting::getVal('auto_backup_max_files', '10'),
            'format'     => SystemSetting::getVal('auto_backup_format', 'zip'),
            'last_run'   => SystemSetting::getVal('auto_backup_last_run'),
        ];

        // Hitung estimasi jadwal berikutnya
        $nextScheduled = null;
        if ($autoSettings['enabled'] && !empty($autoSettings['last_run'])) {
            $last = Carbon::parse($autoSettings['last_run']);
            $nextScheduled = match($autoSettings['frequency']) {
                'every_12_hours' => $last->copy()->addHours(12),
                'weekly'         => $last->copy()->addDays(7),
                default          => $last->copy()->addDay(),
            };
        }

        // Metrik Database
        $dbConfig = config('database.connections.mysql');
        $dbName = $dbConfig['database'] ?? '-';
        
        $mysqlVersion = 'MySQL';
        $dbSizeFormatted = '-';
        $totalTables = 0;

        try {
            $versionRow = DB::select('SELECT VERSION() as ver');
            if (!empty($versionRow)) {
                $mysqlVersion = $versionRow[0]->ver;
            }

            $tables = DB::select('SHOW TABLES');
            $totalTables = count($tables);

            $sizeRow = DB::select("
                SELECT SUM(data_length + index_length) AS db_size 
                FROM information_schema.TABLES 
                WHERE table_schema = ?
            ", [$dbName]);

            if (!empty($sizeRow) && !empty($sizeRow[0]->db_size)) {
                $dbSizeFormatted = $this->backupService->formatFileSize((int) $sizeRow[0]->db_size);
            }
        } catch (\Throwable $e) {
            // Gunakan default jika information_schema dibatasi
        }

        // Total storage yang digunakan oleh file backup
        $totalBackupSizeBytes = array_sum(array_column($backups, 'size_bytes'));
        $totalBackupSizeFormatted = $this->backupService->formatFileSize($totalBackupSizeBytes);

        return view('superadmin.database_backups.index', compact(
            'backups',
            'autoSettings',
            'nextScheduled',
            'dbName',
            'mysqlVersion',
            'totalTables',
            'dbSizeFormatted',
            'totalBackupSizeFormatted'
        ));
    }

    /**
     * Buat backup database secara manual
     */
    public function create(Request $request)
    {
        $this->authorizeSuperAdmin();

        $request->validate([
            'format'       => 'nullable|in:zip,sql',
            'download_now' => 'nullable|boolean',
        ]);

        $format = $request->input('format', 'zip');
        $downloadNow = $request->boolean('download_now');

        try {
            $result = $this->backupService->createBackup('manual', $format);

            AuditLog::create([
                'user_id'    => Auth::id(),
                'action'     => 'database_backup_created',
                'details'    => "Super Admin membuat backup database manual: {$result['filename']} ({$result['size_formatted']}, Metode: {$result['method']})",
                'ip_address' => $request->ip()
            ]);

            if ($downloadNow && file_exists($result['filepath'])) {
                return response()->download($result['filepath'], $result['filename'], [
                    'Content-Type'  => ($format === 'zip') ? 'application/zip' : 'application/sql',
                    'Cache-Control' => 'no-store, no-cache'
                ]);
            }

            return back()->with('success', "Backup database berhasil dibuat: {$result['filename']} ({$result['size_formatted']})");
        } catch (\Throwable $e) {
            return back()->with('error', "Gagal membuat backup database: " . $e->getMessage());
        }
    }

    /**
     * Unduh file backup database
     */
    public function download(string $filename)
    {
        $this->authorizeSuperAdmin();

        $filePath = $this->backupService->getDownloadPath($filename);

        if (!$filePath || !file_exists($filePath)) {
            return back()->with('error', "File backup '{$filename}' tidak ditemukan atau telah dihapus.");
        }

        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'database_backup_downloaded',
            'details'    => "Super Admin mengunduh file backup database: {$filename}",
            'ip_address' => request()->ip()
        ]);

        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $contentType = match($ext) {
            'zip'   => 'application/zip',
            'gz'    => 'application/gzip',
            default => 'application/sql',
        };

        return response()->download($filePath, $filename, [
            'Content-Type'  => $contentType,
            'Cache-Control' => 'no-store, no-cache'
        ]);
    }

    /**
     * Hapus file backup database
     */
    public function destroy(string $filename)
    {
        $this->authorizeSuperAdmin();

        $deleted = $this->backupService->deleteBackup($filename);

        if ($deleted) {
            AuditLog::create([
                'user_id'    => Auth::id(),
                'action'     => 'database_backup_deleted',
                'details'    => "Super Admin menghapus file backup database: {$filename}",
                'ip_address' => request()->ip()
            ]);

            return back()->with('success', "File backup '{$filename}' berhasil dihapus.");
        }

        return back()->with('error', "Gagal menghapus file backup '{$filename}'.");
    }

    /**
     * Simpan pengaturan konfigurasi Auto-Backup
     */
    public function updateSettings(Request $request)
    {
        $this->authorizeSuperAdmin();

        $request->validate([
            'auto_backup_enabled'   => 'required|in:0,1',
            'auto_backup_frequency' => 'required|in:every_12_hours,daily,weekly',
            'auto_backup_max_files' => 'required|integer|min:1|max:100',
            'auto_backup_format'    => 'required|in:zip,sql',
        ]);

        SystemSetting::setVal('auto_backup_enabled', $request->auto_backup_enabled);
        SystemSetting::setVal('auto_backup_frequency', $request->auto_backup_frequency);
        SystemSetting::setVal('auto_backup_max_files', (string) $request->auto_backup_max_files);
        SystemSetting::setVal('auto_backup_format', $request->auto_backup_format);

        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'database_backup_settings_updated',
            'details'    => "Super Admin memperbarui konfigurasi auto-backup (Status: " . ($request->auto_backup_enabled == '1' ? 'Aktif' : 'Nonaktif') . ", Frekuensi: {$request->auto_backup_frequency}, Retensi: {$request->auto_backup_max_files} file)",
            'ip_address' => $request->ip()
        ]);

        return back()->with('success', 'Konfigurasi Auto-Backup Database berhasil disimpan.');
    }

    /**
     * Jalankan auto-backup secara langsung untuk pengetesan
     */
    public function runAutoBackupNow(Request $request)
    {
        $this->authorizeSuperAdmin();

        $format = SystemSetting::getVal('auto_backup_format', 'zip');

        try {
            $result = $this->backupService->createBackup('auto', $format);

            AuditLog::create([
                'user_id'    => Auth::id(),
                'action'     => 'database_auto_backup_triggered',
                'details'    => "Super Admin memicu auto-backup secara manual: {$result['filename']} ({$result['size_formatted']})",
                'ip_address' => $request->ip()
            ]);

            return back()->with('success', "Auto-Backup berhasil dieksekusi: {$result['filename']} ({$result['size_formatted']})");
        } catch (\Throwable $e) {
            return back()->with('error', "Gagal menjalankan auto-backup: " . $e->getMessage());
        }
    }
}
