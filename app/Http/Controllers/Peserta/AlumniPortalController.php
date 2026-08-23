<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use App\Models\AlumniProfile;
use App\Models\AlumniProgram;
use App\Models\UserAlumni;
use App\Models\AlumniCertificate;
use App\Models\AlumniVerificationRequest;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class AlumniPortalController extends Controller
{
    /**
     * Show Alumni Portal Dashboard for current user
     */
    public function index()
    {
        $user = Auth::user();
        
        // Eager load relationships
        $user->load(['alumniProfile', 'alumniCertificates.alumniProgram']);
        
        $graduatedPrograms = UserAlumni::with('alumniProgram')
            ->where('user_id', $user->id)
            ->where('verification_status', 'approved')
            ->get();

        $pendingRequests = AlumniVerificationRequest::with('alumniProgram')
            ->where('user_id', $user->id)
            ->whereIn('status', ['pending', 'revision'])
            ->get();

        return view('pesertabiasa.alumni.index', compact('user', 'graduatedPrograms', 'pendingRequests'));
    }

    /**
     * Show manual verification form
     */
    public function showVerifyForm()
    {
        $userId = Auth::id();
        
        // Get program IDs user is already registered and approved in
        $registeredProgramIds = UserAlumni::where('user_id', $userId)
            ->where('verification_status', 'approved')
            ->whereNotNull('alumni_program_id')
            ->get()
            ->map(function ($ua) {
                return $ua->alumniProgram->program_id;
            })
            ->filter()
            ->toArray();

        // Show all programs from programs table that the user has not verified yet
        $programs = Program::whereNotIn('id', $registeredProgramIds)->orderBy('name')->get();

        return view('pesertabiasa.alumni.verify-manual', compact('programs'));
    }

    /**
     * Submit manual verification request
     */
    public function submitVerifyRequest(Request $request)
    {
        $request->validate([
            'program_id' => 'required|exists:programs,id',
            'certificate_scan' => 'required|file|image|mimes:jpeg,png,jpg|max:5120', // Max 5MB
        ]);

        $userId = Auth::id();
        $program = Program::findOrFail($request->program_id);

        // Ensure AlumniProgram exists for this program
        $year = $program->start_date ? date('Y', strtotime($program->start_date)) : date('Y');
        $alumniProgram = AlumniProgram::firstOrCreate(
            ['program_id' => $program->id],
            [
                'name' => $program->name,
                'year' => $year,
            ]
        );

        // Check if there is already an active request or verified program
        $exists = AlumniVerificationRequest::where('user_id', $userId)
            ->where('alumni_program_id', $alumniProgram->id)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($exists) {
            return redirect()->route('peserta.alumni.index')
                ->with('error', 'Permohonan verifikasi untuk program ini sudah terkirim atau disetujui.');
        }

        // Store file
        $path = $request->file('certificate_scan')->store('verification_requests', 'public');

        AlumniVerificationRequest::create([
            'user_id' => $userId,
            'alumni_program_id' => $alumniProgram->id,
            'certificate_scan_path' => $path,
            'status' => 'pending',
        ]);

        return redirect()->route('peserta.alumni.index')
            ->with('success', 'Permohonan verifikasi berhasil dikirim dan sedang menunggu review admin!');
    }

    /**
     * Download digital certificate file
     */
    public function downloadCertificate($uuid)
    {
        $certificate = AlumniCertificate::where('uuid', $uuid)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $path = Storage::disk('public')->path($certificate->file_path);
        
        if (!file_exists($path)) {
            return redirect()->back()->with('error', 'File sertifikat tidak ditemukan di storage.');
        }

        return response()->download($path, 'Certificate_' . ($certificate->certificate_number ?? $uuid) . '.pdf');
    }
}
