<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class AlumniIntegrationController extends Controller
{
    /**
     * Send/push alumni data of a passed registration to the LMS
     */
    public function sendAlumniToLms(Request $request, $registrationId)
    {
        // 1. Find registration with program and user details
        $registration = Registration::with(['program', 'user'])->find($registrationId);

        if (!$registration) {
            return response()->json([
                'success' => false,
                'message' => 'Data registrasi tidak ditemukan.'
            ], 404);
        }

        // 2. Wajib Lulus Validation
        if ($registration->status !== 'passed') {
            return response()->json([
                'success' => false,
                'message' => 'Gagal: Peserta belum dinyatakan LULUS (status saat ini: ' . $registration->status . '). Hanya data alumni lulus yang dapat dikirim.'
            ], 400);
        }

        // 3. Prepare payload mapping which program the user is alumni of
        $payload = [
            'user' => [
                'id'    => (int) $registration->user_id,
                'name'  => $registration->user->name,
                'email' => $registration->user->email,
            ],
            'program_id'       => (int) $registration->program_id,
            'status'           => $registration->status, // Must be 'passed'
            'final_id_number'  => $registration->final_id_number,
            'final_scores'     => is_string($registration->final_scores) 
                                    ? json_decode($registration->final_scores, true) 
                                    : $registration->final_scores,
            'program_details'  => [
                'name'        => $registration->program->name,
                'slug'        => $registration->program->slug,
                'status'      => $registration->program->status,
                'total_hours' => $registration->program->total_hours ?? 32,
            ]
        ];

        // 4. Send API request to external LMS endpoint
        $syncUrl = Config::get('services.lms.sync_url') ?: 'https://lms.instituthijauindonesia.or.id/api/v1/sync-from-program';
        $integrationKey = Config::get('services.lms.integration_key') ?: 'teriteriteriteriteriteri30330303033';

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'X-INTEGRATION-KEY' => $integrationKey,
                    'Accept'            => 'application/json'
                ])
                ->post($syncUrl, $payload);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data alumni berhasil dikirim dan disinkronkan ke LMS luar.',
                    'sync_url' => $syncUrl,
                    'response' => $response->json()
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'LMS luar menolak sinkronisasi data alumni.',
                'sync_url' => $syncUrl,
                'status_code' => $response->status(),
                'error' => $response->json() ?: $response->body()
            ], $response->status());

        } catch (\Exception $e) {
            Log::error('API Alumni Sync Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan jaringan saat mengirim data ke LMS: ' . $e->getMessage(),
                'sync_url' => $syncUrl
            ], 500);
        }
    }
}
