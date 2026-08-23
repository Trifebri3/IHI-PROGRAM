<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Registration;

class AlumniClientApiController extends Controller
{
    /**
     * Authenticate user and return their passed alumni details
     */
    public function login(Request $request)
    {
        // 1. Verify Client API Key
        $clientKey = $request->header('X-ALUMNI-CLIENT-KEY');
        $expectedKey = config('services.alumni_client.key');

        if (empty($clientKey) || $clientKey !== $expectedKey) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Invalid API Key.'
            ], 403);
        }

        // 2. Validate Request Body
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // 3. Attempt login
        if (Auth::attempt($request->only('email', 'password'))) {
            $user = Auth::user();

            // 4. Wajib Lulus: Check if user has at least one passed registration
            $passedRegistrations = $user->registrations()
                ->with('program')
                ->where('status', 'passed')
                ->get()
                ->map(function ($reg) {
                    return [
                        'registration_id' => $reg->id,
                        'program_id' => $reg->program_id,
                        'program_name' => $reg->program->name,
                        'program_slug' => $reg->program->slug,
                        'final_id_number' => $reg->final_id_number,
                        'status' => $reg->status,
                        'graduated_at' => $reg->updated_at->toIso8601String(),
                    ];
                });

            if ($passedRegistrations->isEmpty()) {
                Auth::logout();
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak: Anda belum dinyatakan LULUS (status passed) dari program manapun.'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil.',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'status' => $user->status,
                    ],
                    'graduated_programs' => $passedRegistrations
                ]
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Login gagal: Email atau password salah.'
        ], 401);
    }

    /**
     * Get specific alumni registration details by ID (with client key)
     */
    public function getAlumniDetails(Request $request, $registrationId)
    {
        // 1. Verify Client API Key
        $clientKey = $request->header('X-ALUMNI-CLIENT-KEY');
        $expectedKey = config('services.alumni_client.key');

        if (empty($clientKey) || $clientKey !== $expectedKey) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Invalid API Key.'
            ], 403);
        }

        // 2. Fetch Registration details
        $registration = Registration::with(['program', 'user'])->find($registrationId);

        if (!$registration) {
            return response()->json([
                'success' => false,
                'message' => 'Data alumni tidak ditemukan.'
            ], 404);
        }

        // 3. Wajib Lulus: Ensure status is passed
        if ($registration->status !== 'passed') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: User terdaftar belum lulus (status saat ini: ' . $registration->status . ').'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $registration->user->id,
                    'name' => $registration->user->name,
                    'email' => $registration->user->email,
                ],
                'program' => [
                    'id' => $registration->program->id,
                    'name' => $registration->program->name,
                    'slug' => $registration->program->slug,
                    'total_hours' => $registration->program->total_hours,
                ],
                'graduation' => [
                    'final_id_number' => $registration->final_id_number,
                    'status' => $registration->status,
                    'graduated_at' => $registration->updated_at->toIso8601String(),
                ]
            ]
        ], 200);
    }

    /**
     * Send/push alumni data of a passed registration to the Alumni Client API (outbound)
     */
    public function syncAlumniToClient(Request $request, $registrationId)
    {
        // 1. Verify Client API Key
        $clientKey = $request->header('X-ALUMNI-CLIENT-KEY');
        $expectedKey = config('services.alumni_client.key');

        if (empty($clientKey) || $clientKey !== $expectedKey) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Invalid API Key.'
            ], 403);
        }

        // 2. Fetch registration
        $registration = Registration::with(['program', 'user'])->find($registrationId);

        if (!$registration) {
            return response()->json([
                'success' => false,
                'message' => 'Data registrasi tidak ditemukan.'
            ], 404);
        }

        // 3. Wajib Lulus Validation
        if ($registration->status !== 'passed') {
            return response()->json([
                'success' => false,
                'message' => 'Gagal: Peserta belum dinyatakan LULUS (status saat ini: ' . $registration->status . '). Hanya data alumni lulus yang dapat dikirim.'
            ], 400);
        }

        // 4. Prepare payload
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

        // 5. Send API request to external Client Sync URL
        $syncUrl = env('ALUMNI_CLIENT_SYNC_URL', 'https://client-api.instituthijauindonesia.or.id/api/v1/alumni/receive');

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'X-ALUMNI-CLIENT-KEY' => $expectedKey,
                    'Accept'              => 'application/json'
                ])
                ->post($syncUrl, $payload);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data alumni berhasil dikirim dan disinkronkan ke Client API luar.',
                    'sync_url' => $syncUrl,
                    'response' => $response->json()
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'Client API luar menolak sinkronisasi data alumni.',
                'sync_url' => $syncUrl,
                'status_code' => $response->status(),
                'error' => $response->json() ?: $response->body()
            ], $response->status());

        } catch (\Exception $e) {
            Log::error('API Alumni Client Sync Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan jaringan saat mengirim data ke Client API: ' . $e->getMessage(),
                'sync_url' => $syncUrl
            ], 500);
        }
    }
}
