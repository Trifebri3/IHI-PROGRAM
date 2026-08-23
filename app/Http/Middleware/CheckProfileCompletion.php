<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckProfileCompletion
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
public function handle(Request $request, Closure $next)
{
    // Cek apakah user sudah punya data profil & alamat
    if (!auth()->user()->profile || !auth()->user()->address) {
        return redirect()->route('identity.gate'); // Arahkan ke route baru
    }
    return $next($request);
}
}
