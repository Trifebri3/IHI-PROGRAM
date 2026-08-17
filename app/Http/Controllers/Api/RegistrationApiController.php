<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

// Asumsikan nama Model Anda adalah Registration
// Jika belum ada, buat dengan: php artisan make:model Registration
use App\Models\Registration; 

class RegistrationApiController extends Controller
{
    /**
     * Menerima sinkronisasi data registrasi dari project Program/Luar.
     */
    public function syncRegistration(Request $request)
    {
        // 1. Validasi Request Payload sesuai struktur tabel database
        $validator = Validator::make($request->all(), [
            'user_id'                    => 'required|integer',
            'program_id'                 => 'required|integer',
            'current_stage_id'           => 'nullable|integer',
            'status'                     => 'required|in:process,passed,failed',
            'final_scores'               => 'nullable|array', // Diubah ke array agar mempermudah pemrosesan JSON
            'final_id_number'            => 'nullable|string|max:255',
            'secure_verification_token'  => 'nullable|string|max:64',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors occurred.',
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            $validatedData = $validator->validated();

            // Skenario: Update jika user_id & program_id sudah ada, jika belum buat baru (Upsert)
            $registration = Registration::updateOrCreate(
                [
                    'user_id'    => $validatedData['user_id'],
                    'program_id' => $validatedData['program_id'],
                ],
                [
                    'current_stage_id'           => $validatedData['current_stage_id'] ?? null,
                    'status'                     => $validatedData['status'],
                    // Menyimpan data array skor sebagai JSON text di DB
                    'final_scores'               => $validatedData['final_scores'] ? json_encode($validatedData['final_scores']) : null, 
                    'final_id_number'            => $validatedData['final_id_number'] ?? null,
                    // Jika token kosong dari pengirim, generate token otomatis demi keamanan tambahan
                    'secure_verification_token'  => $validatedData['secure_verification_token'] ?? Str::random(40),
                ]
            );

            // Menentukan kode status HTTP (201 untuk data baru, 200 untuk update data lama)
            $statusCode = $registration->wasRecentlyCreated ? 201 : 200;
            $message    = $registration->wasRecentlyCreated ? 'Registration data created successfully.' : 'Registration data synchronized successfully.';

            // 2. Response JSON yang akan dibaca oleh project Program (pengirim)
            return response()->json([
                'success' => true,
                'message' => $message,
                'data'    => [
                    'id'                        => $registration->id,
                    'user_id'                   => (int) $registration->user_id,
                    'program_id'                => (int) $registration->program_id,
                    'current_stage_id'          => $registration->current_stage_id ? (int) $registration->current_stage_id : null,
                    'status'                    => $registration->status,
                    'final_scores'              => json_decode($registration->final_scores, true),
                    'final_id_number'           => $registration->final_id_number,
                    'secure_verification_token' => $registration->secure_verification_token,
                    'created_at'                => $registration->created_at,
                    'updated_at'                => $registration->updated_at,
                ]
            ], $statusCode);

        } catch (Exception $e) {
            // Log error internal jika terjadi kendala pada server LMS
            Log::error('LMS Registration Sync Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to synchronize registration data due to server error.'
            ], 500);
        }
    }
}