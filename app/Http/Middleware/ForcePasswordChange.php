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
        if (auth()->check()) {
            $user = auth()->user();

            // 1. Bypass untuk Super Admin & Admin Program
            if ($user->hasRole(['Super Admin', 'Admin Program'])) {
                return $next($request);
            }

            // 2. Bypass untuk user yang sudah lama terdaftar (terdaftar sebelum tanggal 28 Agustus 2026)
            if ($user->created_at && $user->created_at->lt('2026-08-28 00:00:00')) {
                return $next($request);
            }

            if ($user->must_change_password) {
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
        }

        return $next($request);
    }
}
