<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * POST /api/auth/login
     * Validasi kredensial dan kirimkan token Sanctum beserta data akses program
     */
    public function login(Request $request)
    {
        // 1. Validasi Input
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        // 2. Cari User Berdasarkan Email
        $user = User::where('email', $request->email)->first();

        // 3. Verifikasi Password
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email atau password salah.'
            ], 401);
        }

        // 4. Ambil Data Program yang Berstatus Lulus/Aktif ('passed')
// 4. Ambil Data Program yang Berstatus Lulus/Aktif ('passed')
        $myPrograms = DB::table('registrations')
            ->join('programs', 'registrations.program_id', '=', 'programs.id')
            ->where('registrations.user_id', $user->id)
            ->where('registrations.status', 'passed') // Hanya program yang lolos seleksi
            ->select(
                'programs.id as program_id', 
                'programs.name as program_name', 
                // --- TAMBAHKAN 3 BARIS INI ---
                'programs.description as program_description', 
                'programs.banner_path', 
                'programs.logo_path', 
                // -----------------------------
                'registrations.final_id_number as nomor_induk'
            )
            ->get();

        // 5. Generate Token Sanctum untuk Akses LMS
        $token = $user->createToken('lms_access_token')->plainTextToken;

        // 6. Response Payload
        return response()->json([
            'status' => 'success',
            'message' => 'Login berhasil',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar,
                ],
                'programs' => $myPrograms
            ]
        ], 200);
    }

    /**
     * GET /api/auth/me
     * Mengambil profil user yang sedang aktif berdasarkan token
     */
    public function me(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'data' => $request->user()
        ], 200);
    }

    /**
     * GET /api/user/programs
     * Mengambil ulang daftar program jika LMS membutuhkannya secara dinamis
     */
    public function myPrograms(Request $request)
    {
        $user = $request->user();

        $programs = DB::table('registrations')
            ->join('programs', 'registrations.program_id', '=', 'programs.id')
            ->where('registrations.user_id', $user->id)
            ->where('registrations.status', 'passed')
            ->select(
                'programs.id as program_id', 
                'programs.name as program_name', 
                'programs.description as program_description', // Tambahan
                'programs.banner_path', // Tambahan
                'programs.logo_path', // Tambahan
                'registrations.final_id_number as nomor_induk'
            )
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $programs
        ], 200);
    }
    /**
     * GET /api/programs
     * Mengambil semua daftar program untuk keperluan referensi LMS
     */
    public function allPrograms()
    {
        $programs = DB::table('programs')
            ->select('id', 'name')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $programs
        ], 200);
    }
    
    /**
     * GET /api/programs/{id}/participants
     * Mengirim daftar peserta yang lulus di suatu program ke LMS
     */
    public function programParticipants($id)
    {
        $participants = DB::table('registrations')
            ->join('users', 'registrations.user_id', '=', 'users.id')
            ->where('registrations.program_id', $id)
            ->where('registrations.status', 'passed')
            ->select('users.id', 'users.name', 'registrations.final_id_number as nip')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $participants
        ], 200);
    }
    
    // Tambahkan di App\Http\Controllers\API\AuthController
public function loginViaGoogle(Request $request)
{
    // Validasi bahwa email sudah ada di database (karena Google Callback sudah menyimpannya)
    $user = User::where('email', $request->email)->first();

    if (!$user) {
        return response()->json(['status' => 'error', 'message' => 'User tidak ditemukan'], 404);
    }

    // Ambil program seperti fungsi login biasa (Disamakan field-nya)
    $myPrograms = DB::table('registrations')
        ->join('programs', 'registrations.program_id', '=', 'programs.id')
        ->where('registrations.user_id', $user->id)
        ->where('registrations.status', 'passed')
        ->select([
            'programs.id as program_id', 
            'programs.name as program_name', 
            'programs.description as program_description', // SINKRONISASI
            'programs.banner_path',                          // SINKRONISASI
            'programs.logo_path',                            // SINKRONISASI
            'registrations.final_id_number as nomor_induk'   // Ini yang final_id_number!
        ])
        ->get();

    // Generate Token
    $token = $user->createToken('lms_access_token')->plainTextToken;

    return response()->json([
        'status' => 'success',
        'data' => [
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
            ],
            'programs' => $myPrograms
        ]
    ]);
}
    
}