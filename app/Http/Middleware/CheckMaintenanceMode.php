<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;

class CheckMaintenanceMode
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Cek bypass token pemulihan darurat sekali klik (Recovery Token)
        if ($request->has('recovery_token')) {
            $token = $request->query('recovery_token');
            $tokenHash = hash('sha256', $token);

            // A. Periksa recovery token untuk Maintenance Mode
            $maintenancePath = storage_path('app/maintenance_mode.json');
            if (file_exists($maintenancePath)) {
                $maintenance = json_decode(file_get_contents($maintenancePath), true);
                if (!empty($maintenance['token_hash']) && !empty($maintenance['token_expires_at'])) {
                    if (hash_equals($maintenance['token_hash'], $tokenHash) && time() < $maintenance['token_expires_at']) {
                        // NONAKTIFKAN MAINTENANCE MODE SECARA PERMANEN
                        $maintenance['is_active'] = false;
                        $maintenance['started_at'] = null;
                        $maintenance['reason'] = '';
                        $maintenance['token_hash'] = null;
                        $maintenance['token_expires_at'] = null;
                        file_put_contents($maintenancePath, json_encode($maintenance, JSON_PRETTY_PRINT));

                        $this->addActivityLog('EMERGENCY RECOVERY', 'Website dipulihkan kembali online secara normal (Maintenance Mode dimatikan via recovery link).', 'INFO', $request->ip());

                        return redirect('/superadmin/optimization')->with('success', 'Website berhasil dibuka kembali secara normal!');
                    }
                }
            }

            // B. Periksa recovery token untuk Secret Defense Mode
            $secretDefensePath = storage_path('app/secret_defense_mode.json');
            if (file_exists($secretDefensePath)) {
                $secretDefense = json_decode(file_get_contents($secretDefensePath), true);
                if (!empty($secretDefense['token_hash']) && !empty($secretDefense['token_expires_at'])) {
                    if (hash_equals($secretDefense['token_hash'], $tokenHash) && time() < $secretDefense['token_expires_at']) {
                        // NONAKTIFKAN SECRET DEFENSE MODE SECARA PERMANEN
                        $secretDefense['is_active'] = false;
                        $secretDefense['activated_at'] = null;
                        $secretDefense['token_hash'] = null;
                        $secretDefense['token_expires_at'] = null;
                        file_put_contents($secretDefensePath, json_encode($secretDefense, JSON_PRETTY_PRINT));

                        $this->addActivityLog('EMERGENCY RECOVERY', 'Layanan produksi dihidupkan kembali (Secret Defense Mode dimatikan via recovery link).', 'INFO', $request->ip());

                        return redirect('/superadmin/optimization')->with('success', 'Layanan produksi berhasil dihidupkan kembali!');
                    }
                }
            }
        }

        // Cek Pengecualian Aset Publik dan Health Check
        $isPublicAsset = $request->is('css*', 'js*', 'images*', 'assets*') || str_contains($request->url(), '/storage/');
        $isHealthCheck = $request->is('up');

        if (!$isPublicAsset && !$isHealthCheck) {
            // 2. Cek apakah Secret Defense Mode aktif (Seluruh web termasuk Admin diblokir)
            $secretDefensePath = storage_path('app/secret_defense_mode.json');
            if (file_exists($secretDefensePath)) {
                $secretDefense = json_decode(file_get_contents($secretDefensePath), true);
                if (!empty($secretDefense['is_active'])) {
                    return response()->view('errors.503', ['is_emergency' => true], 503);
                }
            }

            // 3. Cek apakah Maintenance Mode aktif (Seluruh web termasuk Admin diblokir)
            $maintenancePath = storage_path('app/maintenance_mode.json');
            if (file_exists($maintenancePath)) {
                $maintenance = json_decode(file_get_contents($maintenancePath), true);
                if (!empty($maintenance['is_active'])) {
                    return response()->view('errors.503', ['is_emergency' => false], 503);
                }
            }
        }

        return $next($request);
    }

    /**
     * Catat log aktivitas internal
     */
    private function addActivityLog($actor, $action, $severity, $ip)
    {
        $logsPath = storage_path('app/optimization_activity_logs.json');
        $logs = [];
        if (file_exists($logsPath)) {
            $logs = json_decode(file_get_contents($logsPath), true);
        }

        array_unshift($logs, [
            'timestamp' => date('H:i:s'),
            'date' => date('Y-m-d'),
            'actor' => $actor,
            'action' => $action,
            'ip' => $ip,
            'request_id' => 'req_' . Str::random(10),
            'severity' => $severity
        ]);

        file_put_contents($logsPath, json_encode(array_slice($logs, 0, 100), JSON_PRETTY_PRINT));
    }
}
