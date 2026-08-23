<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ProgramBiodataSchema;
use App\Models\ProgramBiodataSubmission;

class EnsureProgramBiodataFilled
{
    public function handle(Request $request, Closure $next)
    {
        // Ambil ID Program dari parameter rute URL
        $programId = $request->route('id');

        if ($programId) {
            // 1. Cek apakah admin membuat rancangan form kustom biodata tambahan?
            $hasSchema = ProgramBiodataSchema::where('program_id', $programId)->exists();

            if ($hasSchema) {
                // 2. Cek apakah user bersangkutan sudah mengisi berkas tersebut?
                $hasSubmitted = ProgramBiodataSubmission::where('user_id', auth()->id())
                    ->where('program_id', $programId)
                    ->exists();

                // Jika ada skema tapi dia BELUM ISI, tendang paksa ke form pengisian berkas kustom biodata program!
                if (!$hasSubmitted && !$request->is("*/biodataprogram*")) {
                    return redirect()->route('programs.internal.biodata', $programId)
                        ->with('error', 'Akses Ditangguhkan! Anda wajib melengkapi Formulir Biodata Tambahan Program terlebih dahulu.');
                }
            }
        }

        return $next($request);
    }
}
