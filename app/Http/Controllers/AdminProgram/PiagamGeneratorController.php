<?php

namespace App\Http\Controllers\AdminProgram;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Program;
use App\Models\PiagamCertificate;
use App\Services\Piagam\CertificateGeneratorService;

class PiagamGeneratorController extends Controller {
    public function index($programId) { 
        $program = Program::with('piagamTemplate')->findOrFail($programId);
        $participants = $program->registrations()->with(['user', 'piagamCertificate'])->get();
        return view('adminprogram.piagam.generator.index', compact('program', 'participants')); 
    }
    
    public function generate(Request $request, $programId, CertificateGeneratorService $generatorService) {
        $program = Program::with('piagamTemplate')->findOrFail($programId);
        
        if (!$program->piagamTemplate) {
            return back()->with('error', 'Program ini belum memiliki template piagam yang dipasangkan oleh Super Admin.');
        }

        $participants = $program->registrations()->with('user')->get();
        $generatedCount = 0;

        foreach ($participants as $participant) {
            // Check if already generated
            $existing = PiagamCertificate::where('participant_id', $participant->id)->first();
            if ($existing) {
                continue; // Skip if already generated (can add force regenerate later)
            }

            try {
                $generatorService->generateForParticipant($participant->id);
                $generatedCount++;
            } catch (\Exception $e) {
                \Log::error('Failed to generate certificate for participant ' . $participant->id . ': ' . $e->getMessage());
                // Continue to next participant
            }
        }

        return redirect()->route('adminprogram.piagam.generator.index', $program->id)->with('success', "Berhasil men-generate $generatedCount piagam baru.");
    }
    
    public function generateOne(Request $request, $programId, $participantId, CertificateGeneratorService $generatorService) {
        $program = Program::with('piagamTemplate')->findOrFail($programId);
        
        if (!$program->piagamTemplate) {
            return back()->with('error', 'Program ini belum memiliki template piagam.');
        }

        $existing = PiagamCertificate::where('participant_id', $participantId)->first();
        if ($existing) {
            return back()->with('error', 'Sertifikat untuk peserta ini sudah di-generate sebelumnya.');
        }

        try {
            $generatorService->generateForParticipant($participantId);
            return back()->with('success', 'Sertifikat berhasil di-generate untuk 1 peserta.');
        } catch (\Exception $e) {
            \Log::error('Failed to generate certificate: ' . $e->getMessage());
            return back()->with('error', 'Gagal men-generate sertifikat: ' . $e->getMessage());
        }
    }

    public function failOne(Request $request, $programId, $participantId) {
        $registration = \App\Models\Registration::findOrFail($participantId);
        $registration->update(['status' => 'failed']);
        
        // if they had a certificate, delete it?
        $existing = PiagamCertificate::where('participant_id', $participantId)->first();
        if ($existing) {
            if ($existing->file_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($existing->file_path);
            }
            $existing->delete();
        }

        return back()->with('success', 'Peserta ditandai sebagai TIDAK LOLOS.');
    }

    public function published($programId) { 
        $program = Program::findOrFail($programId);
        $certificates = PiagamCertificate::whereHas('registration', function($q) use ($programId) {
            $q->where('program_id', $programId);
        })->with('registration.user')->paginate(20);
        
        return view('adminprogram.piagam.generator.published', compact('program', 'certificates')); 
    }
}



