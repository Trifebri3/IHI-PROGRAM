<?php

namespace App\Http\Controllers\AdminProgram;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Program;
use App\Models\Registration;
use App\Models\PiagamCertificate;
use App\Services\Piagam\CertificateGeneratorService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class PiagamGeneratorController extends Controller {

    public function index(Request $request, $programId) { 
        $program = Program::with('piagamTemplate')->findOrFail($programId);
        
        $query = $program->registrations()->with(['user', 'piagamCertificate']);

        // Filter search (Nama / Email)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter status: 'all', 'pending', 'generated', 'failed'
        $currentStatus = $request->get('status', 'all');
        if ($currentStatus === 'pending') {
            $query->where('status', '!=', 'failed')
                  ->whereDoesntHave('piagamCertificate', function ($q) {
                      $q->whereNotNull('file_path');
                  });
        } elseif ($currentStatus === 'generated') {
            $query->whereHas('piagamCertificate', function ($q) {
                $q->whereNotNull('file_path');
            });
        } elseif ($currentStatus === 'failed') {
            $query->where('status', 'failed');
        }

        $participants = $query->get();

        // Calculate counts for quick-filter tabs
        $allRegistrations = $program->registrations()->with('piagamCertificate')->get();
        $counts = [
            'total' => $allRegistrations->count(),
            'pending' => $allRegistrations->where('status', '!=', 'failed')->filter(function($r) {
                return empty($r->piagamCertificate) || empty($r->piagamCertificate->file_path);
            })->count(),
            'generated' => $allRegistrations->filter(function($r) {
                return !empty($r->piagamCertificate) && !empty($r->piagamCertificate->file_path);
            })->count(),
            'failed' => $allRegistrations->where('status', 'failed')->count(),
        ];

        return view('adminprogram.piagam.generator.index', compact('program', 'participants', 'counts', 'currentStatus')); 
    }
    
    public function generate(Request $request, $programId, CertificateGeneratorService $generatorService) {
        $program = Program::with('piagamTemplate')->findOrFail($programId);
        
        if (!$program->piagamTemplate) {
            return back()->with('error', 'Program ini belum memiliki template piagam yang dipasangkan oleh Super Admin.');
        }

        $selectedIds = $request->input('participant_ids', []);
        
        // If participant_ids provided (Checkbox selection)
        if (!empty($selectedIds) && is_array($selectedIds)) {
            $participants = Registration::whereIn('id', $selectedIds)
                ->where('program_id', $programId)
                ->with('user')
                ->get();
        } else {
            // Mass generate for all eligible participants (not failed and not already generated)
            $participants = $program->registrations()
                ->where('status', '!=', 'failed')
                ->whereDoesntHave('piagamCertificate', function ($q) {
                    $q->whereNotNull('file_path');
                })
                ->with('user')
                ->get();
        }

        if ($participants->isEmpty()) {
            return back()->with('info', 'Tidak ada peserta yang dipilih atau memenuhi syarat untuk di-generate.');
        }

        $generatedCount = 0;
        $skippedCount = 0;
        $failedErrors = [];

        foreach ($participants as $participant) {
            // Check if already generated
            $existing = PiagamCertificate::where('participant_id', $participant->id)->first();
            if ($existing && $existing->file_path && file_exists(storage_path('app/public/' . $existing->file_path))) {
                $skippedCount++;
                continue;
            }

            try {
                $generatorService->generateForParticipant($participant->id);
                $generatedCount++;
            } catch (\Exception $e) {
                Log::error('Failed to generate certificate for participant ' . $participant->id . ': ' . $e->getMessage());
                $failedErrors[] = ($participant->user->name ?? 'Peserta #' . $participant->id) . ' (' . $e->getMessage() . ')';
            }
        }

        $message = "Berhasil men-generate $generatedCount piagam.";
        if ($skippedCount > 0) {
            $message .= " ($skippedCount peserta dilewati karena sudah memiliki sertifikat).";
        }

        if (!empty($failedErrors)) {
            $errorMessage = "Terdapat " . count($failedErrors) . " peserta gagal di-generate: " . implode(', ', array_slice($failedErrors, 0, 3));
            if (count($failedErrors) > 3) {
                $errorMessage .= " dan " . (count($failedErrors) - 3) . " lainnya.";
            }
            return redirect()->route('adminprogram.piagam.generator.index', $program->id)
                ->with('success', $message)
                ->with('error', $errorMessage);
        }

        return redirect()->route('adminprogram.piagam.generator.index', $program->id)->with('success', $message);
    }
    
    public function generateOne(Request $request, $programId, $participantId, CertificateGeneratorService $generatorService) {
        $program = Program::with('piagamTemplate')->findOrFail($programId);
        
        if (!$program->piagamTemplate) {
            return back()->with('error', 'Program ini belum memiliki template piagam.');
        }

        $participant = Registration::where('id', $participantId)->where('program_id', $programId)->firstOrFail();

        // If previously marked as failed, restore to active/passed
        if ($participant->status === 'failed') {
            $participant->update(['status' => 'passed']);
        }

        $existing = PiagamCertificate::where('participant_id', $participantId)->first();
        if ($existing && $existing->file_path && file_exists(storage_path('app/public/' . $existing->file_path))) {
            return back()->with('error', 'Sertifikat untuk peserta ini sudah di-generate sebelumnya.');
        }

        try {
            $generatorService->generateForParticipant($participantId);
            return back()->with('success', 'Sertifikat berhasil di-generate untuk ' . ($participant->user->name ?? 'peserta') . '.');
        } catch (\Exception $e) {
            Log::error('Failed to generate certificate: ' . $e->getMessage());
            return back()->with('error', 'Gagal men-generate sertifikat: ' . $e->getMessage());
        }
    }

    public function failOne(Request $request, $programId, $participantId) {
        $registration = Registration::where('id', $participantId)->where('program_id', $programId)->firstOrFail();
        $registration->update(['status' => 'failed']);
        
        // Remove existing certificate if any
        $existing = PiagamCertificate::where('participant_id', $participantId)->first();
        if ($existing) {
            if ($existing->file_path) {
                Storage::disk('public')->delete($existing->file_path);
            }
            $existing->delete();
        }

        return back()->with('success', 'Peserta ' . ($registration->user->name ?? '') . ' ditandai sebagai TIDAK LOLOS.');
    }

    public function passOne(Request $request, $programId, $participantId) {
        $registration = Registration::where('id', $participantId)->where('program_id', $programId)->firstOrFail();
        $registration->update(['status' => 'passed']);

        return back()->with('success', 'Status peserta ' . ($registration->user->name ?? '') . ' berhasil dipulihkan menjadi BERHAK/LOLOS.');
    }

    public function bulkFail(Request $request, $programId) {
        $selectedIds = $request->input('participant_ids', []);
        if (empty($selectedIds)) {
            return back()->with('error', 'Pilih minimal satu peserta untuk ditandai Tidak Lolos.');
        }

        $registrations = Registration::whereIn('id', $selectedIds)->where('program_id', $programId)->get();
        $count = 0;

        foreach ($registrations as $registration) {
            $registration->update(['status' => 'failed']);
            
            $existing = PiagamCertificate::where('participant_id', $registration->id)->first();
            if ($existing) {
                if ($existing->file_path) {
                    Storage::disk('public')->delete($existing->file_path);
                }
                $existing->delete();
            }
            $count++;
        }

        return back()->with('success', "Berhasil menandai $count peserta sebagai TIDAK LOLOS.");
    }

    public function bulkPass(Request $request, $programId) {
        $selectedIds = $request->input('participant_ids', []);
        if (empty($selectedIds)) {
            return back()->with('error', 'Pilih minimal satu peserta untuk dipulihkan.');
        }

        $count = Registration::whereIn('id', $selectedIds)
            ->where('program_id', $programId)
            ->update(['status' => 'passed']);

        return back()->with('success', "Berhasil memulihkan status $count peserta menjadi BERHAK/LOLOS.");
    }

    public function sendEmailOne(Request $request, $programId, $participantId) {
        $registration = Registration::where('id', $participantId)->where('program_id', $programId)->firstOrFail();
        $certificate = PiagamCertificate::where('participant_id', $participantId)->first();
        
        if (!$certificate || !$certificate->file_path) {
            return back()->with('error', 'Sertifikat belum di-generate untuk peserta ini.');
        }

        try {
            Mail::to($registration->user->email)->queue(new \App\Mail\PiagamCertificateMail($certificate));
            $certificate->update(['email_sent_at' => now()]);
            return back()->with('success', 'Sertifikat berhasil dimasukkan ke antrean pengiriman email untuk ' . $registration->user->email . '.');
        } catch (\Exception $e) {
            Log::error('Failed to send certificate email: ' . $e->getMessage());
            return back()->with('error', 'Gagal mengirim email: ' . $e->getMessage());
        }
    }

    public function bulkSendEmail(Request $request, $programId) {
        $selectedIds = $request->input('participant_ids', []);
        if (empty($selectedIds)) {
            return back()->with('error', 'Pilih minimal satu peserta untuk dikirimkan email sertifikat.');
        }

        $certificates = PiagamCertificate::whereIn('participant_id', $selectedIds)
            ->where('program_id', $programId)
            ->whereNotNull('file_path')
            ->with('participant.user')
            ->get();

        if ($certificates->isEmpty()) {
            return back()->with('error', 'Tidak ada sertifikat yang siap dikirim di antara peserta terpilih.');
        }

        $sentCount = 0;
        foreach ($certificates as $cert) {
            if ($cert->participant && $cert->participant->user && $cert->participant->user->email) {
                try {
                    Mail::to($cert->participant->user->email)->queue(new \App\Mail\PiagamCertificateMail($cert));
                    $cert->update(['email_sent_at' => now()]);
                    $sentCount++;
                } catch (\Exception $e) {
                    Log::error('Failed queueing certificate email for cert #' . $cert->id . ': ' . $e->getMessage());
                }
            }
        }

        return back()->with('success', "Berhasil memasukkan $sentCount email sertifikat ke antrean pengiriman.");
    }

    public function matchList(Request $request, $programId, CertificateGeneratorService $generatorService) {
        $request->validate([
            'raw_list' => 'required|string',
            'list_action' => 'required|in:mark_passed_only,mark_passed_others_failed,mark_failed,generate_matched',
        ]);

        $program = Program::with('piagamTemplate')->findOrFail($programId);
        $rawText = $request->input('raw_list');
        $lines = preg_split('/[\r\n,;]+/', $rawText);
        $items = [];
        foreach ($lines as $line) {
            $trimmed = trim(preg_replace('/^\s*\d+[\.\)\-]\s*/', '', trim($line)));
            if (!empty($trimmed)) {
                $items[] = $trimmed;
            }
        }
        $items = array_values(array_unique($items));

        $allRegistrations = $program->registrations()->with('user')->get();
        $matchedIds = [];
        $unmatchedItems = [];

        foreach ($items as $item) {
            $cleanItem = mb_strtolower($item);
            $isEmail = str_contains($cleanItem, '@');
            $found = $allRegistrations->first(function($reg) use ($cleanItem, $isEmail) {
                if (!$reg->user) return false;
                if ($isEmail) {
                    return mb_strtolower(trim($reg->user->email)) === $cleanItem;
                }
                $name = mb_strtolower(trim($reg->user->name));
                return $name === $cleanItem || str_contains($name, $cleanItem) || str_contains($cleanItem, $name);
            });

            if ($found && !in_array($found->id, $matchedIds)) {
                $matchedIds[] = $found->id;
            } else {
                $unmatchedItems[] = $item;
            }
        }

        $action = $request->input('list_action');
        $matchedCount = count($matchedIds);

        if ($action === 'mark_passed_only') {
            Registration::where('program_id', $programId)->whereIn('id', $matchedIds)->update(['status' => 'passed']);
            $msg = "Berhasil menandai $matchedCount peserta dari list sebagai LOLOS/BERHAK.";
        } elseif ($action === 'mark_passed_others_failed') {
            Registration::where('program_id', $programId)->whereIn('id', $matchedIds)->update(['status' => 'passed']);
            $othersCount = Registration::where('program_id', $programId)->whereNotIn('id', $matchedIds)->update(['status' => 'failed']);
            $msg = "Berhasil menandai $matchedCount peserta dari list sebagai LOLOS, dan $othersCount peserta lainnya sebagai TIDAK LOLOS.";
        } elseif ($action === 'mark_failed') {
            Registration::where('program_id', $programId)->whereIn('id', $matchedIds)->update(['status' => 'failed']);
            PiagamCertificate::whereIn('participant_id', $matchedIds)->delete();
            $msg = "Berhasil menandai $matchedCount peserta dari list sebagai TIDAK LOLOS.";
        } elseif ($action === 'generate_matched') {
            if (!$program->piagamTemplate) {
                return back()->with('error', 'Program belum memiliki template piagam.');
            }
            Registration::where('program_id', $programId)->whereIn('id', $matchedIds)->where('status', 'failed')->update(['status' => 'passed']);
            $genCount = 0;
            foreach ($matchedIds as $pId) {
                try {
                    $generatorService->generateForParticipant($pId);
                    $genCount++;
                } catch (\Throwable $t) {
                    Log::error("Match-list generate error for #$pId: " . $t->getMessage());
                }
            }
            $msg = "Berhasil men-generate $genCount piagam untuk peserta dari list.";
        }

        if (!empty($unmatchedItems)) {
            $unmatchedSample = implode(', ', array_slice($unmatchedItems, 0, 5));
            if (count($unmatchedItems) > 5) {
                $unmatchedSample .= ' dan ' . (count($unmatchedItems) - 5) . ' lainnya';
            }
            return redirect()->route('adminprogram.piagam.generator.index', $program->id)
                ->with('success', $msg)
                ->with('info', count($unmatchedItems) . " nama/email dalam list tidak ditemukan di database: " . $unmatchedSample);
        }

        return redirect()->route('adminprogram.piagam.generator.index', $program->id)->with('success', $msg);
    }

    public function published($programId) { 
        $program = Program::findOrFail($programId);
        $certificates = PiagamCertificate::whereHas('registration', function($q) use ($programId) {
            $q->where('program_id', $programId);
        })->with('registration.user')->paginate(20);
        
        return view('adminprogram.piagam.generator.published', compact('program', 'certificates')); 
    }
}
