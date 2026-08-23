<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Registration;
use Illuminate\Http\Request;

class SSOController extends Controller
{


public function loginApi(Request $request)
{
    // 1. Verifikasi API KEY dari LMS
    if ($request->header('X-LMS-API-KEY') !== config('services.lms_api_key')) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    // 2. Cek kredensial user
    if (Auth::attempt($request->only('email', 'password'))) {
        $user = Auth::user();
        
        // 3. Ambil data program (mengambil relasi dari tabel Registrations)
        $programs = \App\Models\Registration::where('user_id', $user->id)
            ->where('status', 'passed')
            ->with('program')
            ->get();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'programs' => $programs->map(fn($p) => [
                'program_id' => $p->program_id,
                'nomor_induk' => $p->final_id_number,
                'program_name' => $p->program->name
            ])
        ]);
    }

    return response()->json(['message' => 'Login gagal'], 401);
}

// app/Http/Controllers/Api/SSOController.php

public function generateToken(Request $request)
{
    $user = $request->user();

    // Generate token unik
    $token = bin2hex(random_bytes(32));

    $user->update([
        'sso_token' => $token,
        'sso_token_expires_at' => now()->addMinutes(2) // Token hanya berlaku 2 menit
    ]);

    return response()->json([
        'token' => $token,
        'redirect_url' => config('app.lms_url') . '/sso/auth?token=' . $token
    ]);
}

public function validateToken(Request $request)
{
    // 1. Validasi Input
    $request->validate(['token' => 'required']);

    // 2. Cari user dan pastikan token masih valid (belum expired)
    $user = User::where('sso_token', $request->token)
                ->where('sso_token_expires_at', '>', now())
                ->first();

    // 3. Jika token tidak ditemukan atau expired
    if (!$user) {
        return response()->json(['message' => 'Token invalid or expired'], 401);
    }

    // 4. Ambil data program yang sudah 'passed'
    $programs = Registration::where('user_id', $user->id)
        ->where('status', 'passed')
        ->with('program')
        ->get()
        ->map(fn($reg) => [
            'program_id'    => $reg->program_id,
            'program_name'  => $reg->program->name,
            'nomor_induk'   => $reg->final_id_number,
        ]);

    // 5. One-time usage: Hapus token agar tidak bisa dipakai ulang
    $user->update(['sso_token' => null, 'sso_token_expires_at' => null]);

    return response()->json([
        'user' => $user->only(['id', 'name', 'email']),
        'programs' => $programs
    ]);
}
}
