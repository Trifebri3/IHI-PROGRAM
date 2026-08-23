<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\BiodataField;
use Illuminate\Support\Facades\DB;

class EnsureProfileCompleted
{
public function handle(Request $request, Closure $next): Response
{
    $user = auth()->user();

    // 1. Bypass untuk Admin
    if ($user->hasRole(['Super Admin', 'Admin Program'])) {
        return $next($request);
    }

    // 2. BYPASS UNTUK HALAMAN BIODATA (Penting untuk menghindari redirect loop!)
    if ($request->routeIs('biodata.create') || $request->routeIs('biodata.store')) {
        return $next($request);
    }

    // 3. Logic Cek Biodata
    $requiredFieldsCount = BiodataField::where('is_required', true)->count();

    $userFilledCount = DB::table('user_biodata_values')
        ->where('user_id', $user->id)
        ->whereIn('biodata_field_id', function($query) {
            $query->select('id')->from('biodata_fields')->where('is_required', true);
        })
        ->count();

    if ($userFilledCount < $requiredFieldsCount) {
        return redirect()->route('biodata.create')
                         ->with('error', 'Anda WAJIB melengkapi biodata dan mengunggah dokumen sebelum melanjutkan!');
    }

    return $next($request);
}
}
