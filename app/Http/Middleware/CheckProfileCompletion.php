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
        $user = auth()->user();

        if ($user) {
            // Bypass untuk Super Admin & Admin Program
            if ($user->hasRole(['Super Admin', 'Admin Program'])) {
                return $next($request);
            }

            // Hindari redirect loop jika mengakses halaman identity-gate atau logout
            if ($request->routeIs('identity.gate') || 
                $request->routeIs('identity.store') || 
                $request->routeIs('logout') || 
                $request->is('logout')) {
                return $next($request);
            }

            // Pengecekan kelengkapan data: Foto Profil dan Alamat Lengkap (Provinsi s.d Desa)
            $hasPhoto = $user->profile && !empty($user->profile->profile_photo_path);
            $hasAddress = $user->address && 
                          !empty($user->address->provinsi) && 
                          !empty($user->address->kabupaten) && 
                          !empty($user->address->kecamatan) && 
                          !empty($user->address->desa);

            if (!$hasPhoto || !$hasAddress) {
                return redirect()->route('identity.gate')
                    ->with('warning', 'Anda wajib melengkapi foto profil dan data alamat lengkap sebelum melanjutkan.');
            }
        }

        return $next($request);
    }
}
