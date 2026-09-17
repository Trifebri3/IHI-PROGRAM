<?php
namespace App\Http\Controllers\Peserta;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PiagamController extends Controller {
    public function index() { 
        $registrations = \App\Models\Registration::with(['program', 'piagamCertificate'])
                            ->where('user_id', auth()->id())
                            ->orderBy('created_at', 'desc')
                            ->get();
        return view('peserta.piagam.index', compact('registrations')); 
    }
}
