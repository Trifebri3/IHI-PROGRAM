<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->must_change_password) {
            // Hanya izinkan akses ke rute identitas (untuk ganti password) dan logout
            if (!$request->routeIs('identitas.index') &&
                !$request->routeIs('identitas.update') &&
                !$request->routeIs('identitas.password') &&
                !$request->routeIs('logout') &&
                !$request->is('logout')) {
                return redirect()->route('identitas.index')
                    ->with('warning', 'Anda wajib memperbarui password Anda terlebih dahulu sebelum dapat mengakses halaman lain.');
            }
        }

        return $next($request);
    }
}
