<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Registration;
use App\Models\PiagamCertificate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PiagamController extends Controller {
    public function index() { 
        $registrations = Registration::with(['program.piagamTemplate', 'piagamCertificate.template', 'piagamGrades'])
                            ->where('user_id', auth()->id())
                            ->orderBy('created_at', 'desc')
                            ->get();
        return view('peserta.piagam.index', compact('registrations')); 
    }

    public function download($id) {
        $certificate = PiagamCertificate::with(['participant', 'template', 'program.piagamTemplate'])->findOrFail($id);

        if ($certificate->participant->user_id !== auth()->id()) {
            abort(403, 'Akses tidak diizinkan.');
        }

        $template = $certificate->template ?? $certificate->program->piagamTemplate;
        $requirements = $template ? ($template->download_requirements ?? []) : [];

        if (!empty($requirements) && !$certificate->requirements_completed_at) {
            return redirect()->route('peserta.piagam.index')->with('error', 'Silakan selesaikan syarat terlebih dahulu sebelum mengunduh sertifikat.');
        }

        if (!$certificate->file_path || !Storage::disk('public')->exists($certificate->file_path)) {
            abort(404, 'File sertifikat belum tersedia.');
        }

        $fileName = 'Sertifikat_' . Str::slug(auth()->user()->name ?? 'Peserta') . '_' . Str::slug($certificate->program->name ?? 'Program') . '.pdf';

        return response()->download(storage_path('app/public/' . $certificate->file_path), $fileName);
    }

    public function completeRequirements(Request $request, $id) {
        $certificate = PiagamCertificate::with('participant')->findOrFail($id);

        if ($certificate->participant->user_id !== auth()->id()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $certificate->update([
            'requirements_completed_at' => now()
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Syarat berhasil diselesaikan.',
            'file_url' => asset('storage/' . $certificate->file_path),
            'download_url' => route('peserta.piagam.download', $certificate->id),
        ]);
    }
}
