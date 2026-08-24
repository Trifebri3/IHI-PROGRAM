<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PrivilegedAccessController extends Controller
{
    /**
     * Verifikasi Password Super Admin pada Security Gate (Step-up Auth)
     */
    public function verifySecurityGate(Request $request)
    {
        $request->validate([
            'password' => 'required|string'
        ]);

        $user = Auth::user();

        // 1. Ambil sandi gate dinamis dari security_policy.json
        $policyPath = storage_path('app/security_policy.json');
        $gatePasswordHash = '$2y$12$8Vg2JqMw.QHsKzOdsYvx3uw2YQ.kSSIMSi8HFxd2eaKGfzQXHzFm.'; // Default: "303303"

        if (file_exists($policyPath)) {
            $policy = json_decode(file_get_contents($policyPath), true);
            if (!empty($policy['gate_password_hash'])) {
                $gatePasswordHash = $policy['gate_password_hash'];
            }
        }

        // 2. Verifikasi Password terhadap Hash atau default plaintext
        $isMatched = Hash::check($request->password, $gatePasswordHash) || 
                     ($request->password === '303303' && Hash::check('303303', $gatePasswordHash));

        if ($isMatched) {
            // Simpan status verifikasi gerbang keamanan selama 5 menit (300 detik)
            session()->put('privileged_session_expires', time() + 300);

            // Log event sukses
            $this->logAuthEvent($user, $request, 'SUCCESS');

            return response()->json([
                'success' => true,
                'message' => 'Identitas terverifikasi. Otoritas konsol dibuka selama 5 menit!'
            ]);
        }

        // Log event gagal
        $this->logAuthEvent($user, $request, 'FAILED');

        return response()->json([
            'success' => false,
            'message' => 'Verifikasi Gagal! Kata sandi salah. Tindakan ini dicatat dalam log keamanan.'
        ], 422);
    }

    /**
     * Update Kata Sandi Security Gate Dinamis
     */
    public function updateGatePassword(Request $request)
    {
        // Pastikan gerbang keamanan aktif
        if (time() > session()->get('privileged_session_expires', 0)) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi otorisasi kedaluwarsa. Silakan verifikasi ulang di Security Gate.'
            ], 403);
        }

        $request->validate([
            'password' => 'required|string|min:4|confirmed'
        ]);

        $policyPath = storage_path('app/security_policy.json');
        $policy = [];
        if (file_exists($policyPath)) {
            $policy = json_decode(file_get_contents($policyPath), true);
        }

        // Simpan hash bcrypt sandi baru
        $policy['gate_password_hash'] = Hash::make($request->password);
        file_put_contents($policyPath, json_encode($policy, JSON_PRETTY_PRINT));

        // Catat aktivitas
        $this->addActivityLog('SUPER ADMIN', 'Memperbarui kata sandi Security Gate secara dinamis.', 'INFO', $request->ip());

        return response()->json([
            'success' => true,
            'message' => 'Kata sandi Security Gate berhasil diperbarui!'
        ]);
    }

    /**
     * Buat One-Time Emergency Recovery Link
     */
    public function generateRecoveryLink(Request $request)
    {
        // Pastikan gerbang keamanan aktif
        if (time() > session()->get('privileged_session_expires', 0)) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi otorisasi kedaluwarsa. Silakan verifikasi ulang di Security Gate.'
            ], 403);
        }

        $token = Str::random(40);
        $expiresAt = time() + 300; // Kedaluwarsa dalam 5 menit

        $maintenancePath = storage_path('app/maintenance_mode.json');
        $maintenance = ['is_active' => true, 'started_at' => date('d M Y — H:i'), 'reason' => 'System maintenance'];
        
        if (file_exists($maintenancePath)) {
            $maintenance = array_merge($maintenance, json_decode(file_get_contents($maintenancePath), true));
        }

        // Simpan token hash SHA-256
        $maintenance['token_hash'] = hash('sha256', $token);
        $maintenance['token_expires_at'] = $expiresAt;

        file_put_contents($maintenancePath, json_encode($maintenance, JSON_PRETTY_PRINT));

        // Catat di log aktivitas utama
        $this->addActivityLog('SUPER ADMIN', 'Membuat One-Time Recovery Token baru.', 'WARNING', $request->ip());

        return response()->json([
            'success' => true,
            'token' => $token,
            'url' => url('/?recovery_token=' . $token),
            'expires_in' => '5:00'
        ]);
    }

    /**
     * Log Authentication Event ke File JSON
     */
    private function logAuthEvent($user, Request $request, $result)
    {
        $path = storage_path('app/security_gates_logs.json');
        $logs = [];
        if (file_exists($path)) {
            $logs = json_decode(file_get_contents($path), true);
        }

        $userAgent = $request->header('User-Agent');
        $device = $this->parseUserAgent($userAgent);

        $location = 'Jakarta, Indonesia';
        $ip = $request->ip();
        if ($ip === '127.0.0.1') {
            $location = 'Bandung, Indonesia (Localhost Dev)';
        } elseif (str_starts_with($ip, '103.')) {
            $location = 'Surabaya, Indonesia';
        } elseif (str_starts_with($ip, '45.')) {
            $location = 'Singapore';
        }

        $newEvent = [
            'id' => 'auth_' . Str::random(8),
            'user' => 'Super Admin #' . $user->id . ' (' . $user->email . ')',
            'time' => date('d M Y — H:i:s'),
            'ip' => $ip,
            'device' => $device['device'],
            'os' => $device['os'],
            'browser' => $device['browser'],
            'session' => 'sess_' . substr(session()->getId(), 0, 8),
            'location' => $location,
            'auth_type' => 'Security Gate (Step-up)',
            'result' => $result
        ];

        array_unshift($logs, $newEvent);
        file_put_contents($path, json_encode(array_slice($logs, 0, 50), JSON_PRETTY_PRINT));
    }

    /**
     * Parser User-Agent Sederhana
     */
    private function parseUserAgent($ua)
    {
        $os = 'Windows 11';
        $browser = 'Chrome';
        $device = 'Desktop';

        if (stripos($ua, 'iphone') !== false) {
            $os = 'iOS 18.x';
            $browser = 'Safari';
            $device = 'iPhone';
        } elseif (stripos($ua, 'android') !== false) {
            $os = 'Android 14';
            $browser = 'Chrome Mobile';
            $device = 'Mobile Phone';
        } elseif (stripos($ua, 'macintosh') !== false) {
            $os = 'macOS Sequoia';
            $browser = 'Safari';
            $device = 'MacBook';
        }

        return compact('os', 'browser', 'device');
    }

    /**
     * Tambah log aktivitas umum
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
