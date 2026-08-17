<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class GoogleController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            
            // 1. Simpan/Update User di Database Master
            $user = User::updateOrCreate([
                'email' => $googleUser->getEmail(),
            ], [
                'name' => $googleUser->getName(),
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'password' => Hash::make(uniqid()), // Random password
            ]);

            // 2. Ambil data program (Reuse logic dari AuthController)
            $myPrograms = DB::table('registrations')
                ->join('programs', 'registrations.program_id', '=', 'programs.id')
                ->where('registrations.user_id', $user->id)
                ->where('registrations.status', 'passed')
                ->select(
                    'programs.id as program_id', 
                    'programs.name as program_name', 
                    'programs.description as program_description',
                    'programs.banner_path', 
                    'programs.logo_path', 
                    'registrations.final_id_number as nomor_induk'
                )
                ->get();

            // 3. Generate Sanctum Token untuk LMS
            $token = $user->createToken('lms_access_token')->plainTextToken;

            // 4. Kirim balik data ke Frontend LMS (Redirect dengan parameter atau via API)
            // Opsi: Redirect ke dashboard LMS dengan membawa token (bisa via query param atau redirect sementara)
            // Contoh redirect ke frontend dengan token:
            return redirect(env('LMS_FRONTEND_URL') . '/auth/callback?token=' . $token . '&user=' . urlencode(json_encode($user)));

        } catch (\Exception $e) {
            \Log::error('Google Login Error: ' . $e->getMessage());
            return redirect(env('LMS_FRONTEND_URL') . '/login?error=google_failed');
        }
    }
}