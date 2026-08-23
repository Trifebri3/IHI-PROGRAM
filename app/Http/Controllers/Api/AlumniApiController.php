<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserAlumni;
use App\Models\AlumniCertificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlumniApiController extends Controller
{
    /**
     * Get profile of the logged-in alumni
     */
    public function me(Request $request)
    {
        $user = $request->user();
        $user->load('alumniProfile');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'status' => $user->status,
                'alumni_profile' => $user->alumniProfile ? [
                    'alumni_number' => $user->alumniProfile->alumni_number,
                    'uuid' => $user->alumniProfile->uuid,
                    'bio' => $user->alumniProfile->bio,
                    'photo_url' => $user->alumniProfile->photo_path ? asset('storage/' . $user->alumniProfile->photo_path) : null,
                ] : null
            ]
        ]);
    }

    /**
     * Get graduated programs history
     */
    public function programs(Request $request)
    {
        $user = $request->user();
        
        $programs = UserAlumni::with('alumniProgram')
            ->where('user_id', $user->id)
            ->where('verification_status', 'approved')
            ->get()
            ->map(function ($pivot) {
                return [
                    'uuid' => $pivot->uuid,
                    'program_name' => $pivot->alumniProgram->name,
                    'year' => $pivot->alumniProgram->year,
                    'extra_info' => $pivot->extra_info,
                    'verified_at' => $pivot->created_at->toIso8601String(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $programs
        ]);
    }

    /**
     * Get digital certificates list
     */
    public function certificates(Request $request)
    {
        $user = $request->user();

        $certificates = AlumniCertificate::with('alumniProgram')
            ->where('user_id', $user->id)
            ->get()
            ->map(function ($cert) {
                return [
                    'uuid' => $cert->uuid,
                    'certificate_number' => $cert->certificate_number,
                    'program_name' => $cert->alumniProgram->name,
                    'year' => $cert->alumniProgram->year,
                    'extra_info' => $cert->extra_info,
                    'download_url' => route('peserta.alumni.certificate.download', $cert->uuid),
                    'verification_url' => route('public.alumni.verify', $cert->uuid),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $certificates
        ]);
    }
}
