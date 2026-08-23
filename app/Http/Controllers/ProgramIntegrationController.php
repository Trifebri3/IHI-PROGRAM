<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Program;
use App\Models\Registration; // Asumsi ada model pendaftaran lokal di project Program

class ProgramIntegrationController extends Controller
{
    /**
     * Kirim data pendaftaran beserta detail program ke LMS
     */
    public function sendRegistrationToLms($registrationId)
    {
        // 1. Ambil data pendaftaran lokal beserta info programnya
        // Sesuaikan dengan relasi yang ada di project Program kamu
        $registration = Registration::with('program', 'user')->findOrFail($registrationId);

        // 2. Siapkan data kombinasi tabel Program & Registrations untuk dikirim
        $payload = [
            'user_id'                    => $registration->user_id,
            'program_id'                 => $registration->program_id,
            'current_stage_id'           => $registration->current_stage_id,
            'status'                     => $registration->status, // 'process', 'passed', 'failed'
            'final_scores'               => $registration->final_scores, // array / json
            'final_id_number'            => $registration->final_id_number,
            'secure_verification_token'  => $registration->secure_verification_token,
            
            // Sertakan info program sekalian jika LMS butuh validasi/pembuatan data program
            'program_details' => [
                'name'         => $registration->program->name,
                'slug'         => $registration->program->slug,
                'description'  => $registration->program->description,
                'total_hours'  => $registration->program->total_hours,
                'score_schema' => $registration->program->score_schema,
            ]
        ];

        // 3. Tembak ke API LMS yang sudah kita buat kemarin
        $lmsUrl = config('services.lms.url') . '/api/v1/registrations/sync';
        $apiKey = config('services.lms.api_key');

        $response = Http::withHeaders([
            'X-LMS-API-KEY' => $apiKey,
            'Accept'        => 'application/json'
        ])->post($lmsUrl, $payload);

        // 4. Cek respon dari LMS
        if ($response->successful()) {
            return response()->json([
                'success' => true,
                'message' => 'Data berhasil terkirim dan sinkron dengan LMS!',
                'lms_data'=> $response->json()
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Gagal sinkronisasi ke LMS.',
            'error'   => $response->json()
        ], $response->status());
    }
}