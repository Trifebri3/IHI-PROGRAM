<?php
namespace App\Http\Controllers\Public;
use App\Http\Controllers\Controller;
use App\Models\PiagamCertificate;
use Illuminate\Http\Request;

class PiagamVerificationController extends Controller {
    public function verify($qr_token) { 
        $certificate = PiagamCertificate::with(['participant.user', 'program'])->where('qr_token', $qr_token)->firstOrFail();
        return view('public.piagam.verify', compact('certificate')); 
    }
}
