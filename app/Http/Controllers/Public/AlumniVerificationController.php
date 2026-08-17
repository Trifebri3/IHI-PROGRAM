<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\UserAlumni;
use App\Models\AlumniCertificate;
use App\Models\QrVerificationLog;
use Illuminate\Http\Request;

class AlumniVerificationController extends Controller
{
    /**
     * Verify QR Code scan (Public, no auth)
     */
    public function verify($uuid)
    {
        $alumni = UserAlumni::with(['user.alumniProfile', 'alumniProgram'])
            ->where('uuid', $uuid)
            ->where('verification_status', 'approved')
            ->first();

        $certificate = AlumniCertificate::where('uuid', $uuid)->first();

        // Log scan event
        try {
            QrVerificationLog::create([
                'alumni_certificate_id' => $certificate?->id,
                'scanned_uuid' => $uuid,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'scanned_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Ignore logging errors to keep verification page alive
        }

        if (!$alumni) {
            return view('public.alumni.verify', [
                'isValid' => false,
                'error' => 'Dokumen Tidak Terdaftar'
            ]);
        }

        return view('public.alumni.verify', [
            'isValid' => true,
            'alumni' => $alumni,
            'certificate' => $certificate
        ]);
    }

    /**
     * Download certificate PDF file publicly (via QR code scan page)
     */
    public function downloadCertificate($uuid)
    {
        $certificate = AlumniCertificate::where('uuid', $uuid)->firstOrFail();

        $path = \Illuminate\Support\Facades\Storage::disk('public')->path($certificate->file_path);
        
        if (!file_exists($path)) {
            abort(404, 'File sertifikat tidak ditemukan.');
        }

        return response()->download($path, 'Certificate_' . ($certificate->certificate_number ?? $uuid) . '.pdf');
    }
}
