<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ProgramAnnouncement;
use App\Models\ProgramAnnouncementView;

class EnsureAnnouncementsRead
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            // =========================================================================
            // 🛡️ LAPIS 1: DETEKSI BLOCKER GLOBAL (PENGUMUMAN SUPER ADMIN)
            // =========================================================================
            $latestGlobalInstruction = ProgramAnnouncement::whereNull('program_id')
                ->where('type', 'instruction')
                ->orderBy('created_at', 'desc')
                ->first();

            if ($latestGlobalInstruction) {
                $hasConfirmedGlobal = ProgramAnnouncementView::where('user_id', auth()->id())
                    ->where('program_announcement_id', $latestGlobalInstruction->id)
                    ->exists();

                // Jika ada instruksi global tapi BELUM BACA, kunci total aplikasi!
                if (!$hasConfirmedGlobal && !$request->is('global-announcement-gate*')) {
                    return redirect()->route('announcements.global.gate')
                        ->with('warning', 'Pemberitahuan Pusat: Terdapat maklumat darurat Super Admin yang wajib Anda setujui.');
                }
            }

            // =========================================================================
            // 🛡️ LAPIS 2: DETEKSI BLOCKER INTERNAL PROGRAM (PENGUMUMAN ADMIN PROGRAM)
            // =========================================================================
            $programId = $request->route('id');
            if ($programId) {
                $latestProgramInstruction = ProgramAnnouncement::where('program_id', $programId)
                    ->where('type', 'instruction')
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($latestProgramInstruction) {
                    $hasConfirmedProgram = ProgramAnnouncementView::where('user_id', auth()->id())
                        ->where('program_announcement_id', $latestProgramInstruction->id)
                        ->exists();

                    if (!$hasConfirmedProgram && !$request->is("*/announcement-gate*")) {
                        return redirect()->route('programs.internal.announcement.gate', $programId);
                    }
                }
            }
        }

        return $next($request);
    }
}
