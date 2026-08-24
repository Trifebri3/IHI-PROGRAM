<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireSecretConsoleCode
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Check if user is authenticated and is a Super Admin
        if (!auth()->check() || !auth()->user()->hasRole('Super Admin')) {
            abort(403, 'Akses ditolak: Area khusus Super Admin.');
        }

        // 2. Check if the secret code is verified AND has not expired (hard 5-minute expiry)
        $isVerified = session()->get('secret_console_verified') === true;
        $expiresAt = session()->get('secret_console_verified_expires', 0);

        if (!$isVerified || time() > $expiresAt) {
            // Bersihkan sesi yang kedaluwarsa
            session()->forget(['secret_console_verified', 'secret_console_verified_expires']);
            
            // Redirect to the verification entry screen
            return redirect()->route('superadmin.secret-gate')->with('error', 'Sesi konsol rahasia Anda telah kedaluwarsa (Batas 5 menit). Silakan masukkan kode kembali.');
        }

        return $next($request);
    }
}
