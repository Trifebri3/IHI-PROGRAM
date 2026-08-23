<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class Registration extends Model
{
    protected $fillable = [
        'user_id', 'program_id', 'current_stage_id', 'status', 'final_id_number', 'final_scores', 'motivation',
        'batch', 'location', 'region', 'participant_status'
    ];

    protected $casts = [
        'final_scores' => 'array'
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function program(): BelongsTo { return $this->belongsTo(Program::class); }


/**
 * Mendefinisikan relasi ke data tahap registrasi
 */
public function stageData(): HasMany
{
    // Pastikan nama modelnya benar (misal: RegistrationStageData)
    // Sesuaikan nama class dengan file model yang menyimpan stage data tersebut
    return $this->hasMany(RegistrationStageData::class, 'registration_id');
}

/**
 * Mendefinisikan relasi ke current stage (jika ada)
 */
public function currentStage(): BelongsTo
{
    return $this->belongsTo(ProgramStage::class, 'current_stage_id');
}


    protected static function booted()
    {
        $syncToLms = function ($registration) {
            // Eager load relasi yang diperlukan agar tidak memicu N+1 query
            $registration->loadMissing(['program', 'user']);

            if (!$registration->program || !$registration->user) {
                return;
            }

            // Membangun payload yang menyertakan data User untuk LMS
            $payload = [
                'user' => [
                    'id'    => (int) $registration->user_id,
                    'name'  => $registration->user->name,
                    'email' => $registration->user->email,
                ],
                'program_id'       => (int) $registration->program_id,
                'status'           => $registration->status,
                'final_id_number'  => $registration->final_id_number,
                'final_scores'     => $registration->final_scores,
                'program_details'  => [
                    'name'        => $registration->program->name,
                    'slug'        => $registration->program->slug,
                    'status'      => $registration->program->status,
                ]
            ];

            try {
                $response = Http::timeout(5)
                    ->retry(2, 500)
                    ->withHeaders([
                        'X-INTEGRATION-KEY' => Config::get('services.lms.integration_key'),
                        'Accept'            => 'application/json'
                    ])
                    ->post(Config::get('services.lms.sync_url'), $payload);

                if (!$response->successful()) {
                    Log::error('LMS Sync Failed', [
                        'status' => $response->status(),
                        'body'   => $response->body(),
                        'user_id'=> $registration->user_id
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('LMS Connection Error: ' . $e->getMessage());
            }
        };

        static::created($syncToLms);

        static::updated(function ($registration) use ($syncToLms) {
            // Memastikan sinkronisasi hanya terjadi jika field penting berubah
            if ($registration->wasChanged(['status', 'final_scores', 'final_id_number'])) {
                $syncToLms($registration);
            }

            // PENTING: Aktivasi alumni sekarang dikelola secara operasional melalui Admin Program (Bukan otomatis)
            /*
            if ($registration->wasChanged('status') && $registration->status === 'passed') {
                try {
                    resolve(\App\Services\AlumniService::class)->registerAutoAlumni($registration);
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error('Alumni registration failed: ' . $e->getMessage());
                }
            }
            */
        });
    }
}