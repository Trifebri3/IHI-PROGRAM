<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\PiagamCertificate;
use Illuminate\Http\Request;

class PiagamVerificationController extends Controller {
    public function verify($qr_token) { 
        $certificate = PiagamCertificate::with([
            'participant.user', 
            'participant.piagamGrades', 
            'program'
        ])->where('qr_token', $qr_token)->firstOrFail();

        $grades = ($certificate->participant && $certificate->participant->piagamGrades) 
            ? $certificate->participant->piagamGrades->filter(function($g) {
                return $g->value !== null && trim((string)$g->value) !== '';
            }) 
            : collect();

        return view('public.piagam.verify', compact('certificate', 'grades')); 
    }
}
