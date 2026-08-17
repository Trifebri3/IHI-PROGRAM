<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLmsApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
public function handle(Request $request, Closure $next)
{
    $apiKey = $request->header('X-LMS-API-KEY');
    if ($apiKey !== config('services.lms_api_key')) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }
    return $next($request);
}
}
