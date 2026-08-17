<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureTermsAccepted
{
public function handle(Request $request, Closure $next)
{
    $user = auth()->user();

    if (!$user) return $next($request);

    // Admin bebas dari aturan ini
    if ($user->hasRole(['Super Admin', 'Admin Program'])) {
        return $next($request);
    }

    // Cek apakah user belum menyetujui S&K
    if (is_null($user->terms_accepted_at)) {
        // TAMBAHKAN rute-rute ini agar tidak terjadi looping
        if ($request->routeIs(['syarat-ketentuan', 'kebijakan-privasi', 'terms.show', 'terms.store'])) {
            return $next($request);
        }

        return redirect()->route('terms.show');
    }

    return $next($request);
}
}
