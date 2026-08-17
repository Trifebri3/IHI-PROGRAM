<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;



// Route Public (Tanpa Login)
Route::post('/auth/login', [AuthController::class, 'login']);
Route::get('/programs', [AuthController::class, 'allPrograms']);
Route::get('/programs/{id}/participants', [AuthController::class, 'programParticipants']);

// Route Protected (Wajib Menggunakan Bearer Token Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::get('/user/programs', [AuthController::class, 'myPrograms']);
    
    // Alumni API integration endpoints
    Route::get('/alumni/me', [\App\Http\Controllers\Api\AlumniApiController::class, 'me']);
    Route::get('/alumni/programs', [\App\Http\Controllers\Api\AlumniApiController::class, 'programs']);
    Route::get('/alumni/certificates', [\App\Http\Controllers\Api\AlumniApiController::class, 'certificates']);
});


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

use App\Http\Controllers\Api\SSOController;

Route::post('/sso/validate', [SSOController::class, 'validateToken']);

// Tambahan: Endpoint untuk LMS meminta token sebelum redirect
Route::post('/sso/generate-token', [SSOController::class, 'generateToken'])
     ->middleware('auth:sanctum');


Route::post('/login', function (Request $request) {
    // Validasi API Key dulu sebelum cek password
    if ($request->header('X-LMS-API-KEY') !== config('services.lms_api_key')) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    if (Auth::attempt($request->only('email', 'password'))) {
        $user = Auth::user();
        return response()->json([
            'user' => $user,
            'programs' => $user->registrations()->where('status', 'passed')->get()
        ]);
    }
    return response()->json(['message' => 'Login gagal'], 401);
});

use App\Http\Controllers\Api\RegistrationApiController;

Route::prefix('v1')->group(function () {
    // Endpoint untuk menerima atau mensinkronisasikan data registrasi
    Route::post('/registrations/sync', [RegistrationApiController::class, 'syncRegistration']);
    
    // Endpoint untuk mengirimkan data alumni ke LMS luar (wajib lulus)
    Route::post('/alumni/send-to-lms/{registrationId}', [\App\Http\Controllers\Api\AlumniIntegrationController::class, 'sendAlumniToLms']);
    
    // Endpoint integrasi eksternal untuk Alumni Client (login, detail & outbound sync)
    Route::post('/alumni-client/login', [\App\Http\Controllers\Api\AlumniClientApiController::class, 'login']);
    Route::get('/alumni-client/details/{registrationId}', [\App\Http\Controllers\Api\AlumniClientApiController::class, 'getAlumniDetails']);
    Route::post('/alumni-client/sync/{registrationId}', [\App\Http\Controllers\Api\AlumniClientApiController::class, 'syncAlumniToClient']);
});



// Endpoint instan untuk menerima data dari Project A
Route::post('/v1/sync-from-program', function (Request $request) {
    
    // 1. Validasi Token Sederhana demi Keamanan
// Gunakan env() untuk membaca dari .env
if ($request->header('X-INTEGRATION-KEY') !== env('LMS_INTEGRATION_KEY')) {
    return response()->json(['message' => 'Unauthorized'], 403);
}

    $data = $request->all();

    try {
        // 2. Amankan data Program terlebih dahulu di LMS
        $programDetails = $data['program_details'];
        $program = Program::updateOrCreate(
            ['id' => $data['program_id']],
            [
                'name'         => $programDetails['name'],
                'slug'         => $programDetails['slug'],
                'description'  => $programDetails['description'] ?? null,
                'quota'        => $programDetails['quota'] ?? 0,
                'total_hours'  => $programDetails['total_hours'] ?? 32,
                'score_schema' => $programDetails['score_schema'] ?? null,
                'status'       => $programDetails['status'] ?? 'published',
            ]
        );

        // 3. Simpan atau Update data Registrasinya di LMS
        $registration = Registration::updateOrCreate(
            [
                'user_id'    => $data['user_id'],
                'program_id' => $data['program_id'],
            ],
            [
                'current_stage_id' => $data['current_stage_id'] ?? null,
                'status'           => $data['status'],
                'final_id_number'  => $data['final_id_number'] ?? null,
                'final_scores'     => $data['final_scores'] ?? null,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Data otomatis terserap di Project B (LMS)',
            'registration_id' => $registration->id
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Gagal sinkronisasi: ' . $e->getMessage()
        ], 500);
    }

});


