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

        $program = $certificate->program;
        $publicSettings = $program ? ($program->piagam_public_settings ?? []) : [];
        if (is_string($publicSettings)) {
            $publicSettings = json_decode($publicSettings, true) ?: [];
        }

        $publicTitle = !empty($publicSettings['public_title']) ? $publicSettings['public_title'] : 'Informasi Kelulusan & Nilai';
        $showPublic = array_key_exists('show_public', $publicSettings) ? (bool)$publicSettings['show_public'] : true;
        $variableLabels = $publicSettings['variable_labels'] ?? [];
        $variableVisibility = $publicSettings['variable_visibility'] ?? [];

        $grades = ($certificate->participant && $certificate->participant->piagamGrades) 
            ? $certificate->participant->piagamGrades->filter(function($g) use ($variableVisibility) {
                if ($g->value === null || trim((string)$g->value) === '') {
                    return false;
                }
                if (isset($variableVisibility[$g->variable_name]) && !$variableVisibility[$g->variable_name]) {
                    return false;
                }
                return true;
            }) 
            : collect();

        return view('public.piagam.verify', compact('certificate', 'grades', 'publicTitle', 'showPublic', 'variableLabels')); 
    }
}
