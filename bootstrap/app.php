<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
->withMiddleware(function (Middleware $middleware) {
    $middleware->trustProxies(at: '*');

    $middleware->web(append: [
        \App\Http\Middleware\CheckBlockedUser::class,
        \App\Http\Middleware\CheckMaintenanceMode::class,
        \App\Http\Middleware\ForcePasswordChange::class,
    ]);

    $middleware->alias([
        'profile.completed' => \App\Http\Middleware\EnsureProfileCompleted::class,
        'program.biodata.completed' => \App\Http\Middleware\EnsureProgramBiodataFilled::class, // <-- PASANG INI
        'announcement.read' => \App\Http\Middleware\EnsureAnnouncementsRead::class, // <-- SUNTIKKAN INI BOSS
        'check.profile' => \App\Http\Middleware\CheckProfileCompletion::class,
        'terms.accepted' => \App\Http\Middleware\EnsureTermsAccepted::class,
        'lms.api.key' => \App\Http\Middleware\EnsureLmsApiKey::class, // <-- TAMBAHKAN INI
        'secret.console' => \App\Http\Middleware\RequireSecretConsoleCode::class,
    ]);
})
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->report(function (Throwable $e) {
            try {
                // Log exception to system_errors.json
                $logPath = storage_path('app/system_errors.json');
                $errors = [];
                if (file_exists($logPath)) {
                    $errors = json_decode(file_get_contents($logPath), true) ?: [];
                }
                
                // Batasi log error maksimal 500 entri
                if (count($errors) > 500) {
                    array_pop($errors);
                }

                $user = auth()->user();
                $userName = $user ? "User #{$user->id} ({$user->email})" : 'Guest (IP: ' . request()->ip() . ')';
                
                // Kategorisasi tingkat keparahan & HTTP Status
                $severity = 'ERROR';
                $httpStatus = '500';
                
                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    $severity = 'WARNING';
                    $httpStatus = '422';
                } elseif ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
                    $httpStatus = (string)$e->getStatusCode();
                    if ($e->getStatusCode() < 500) {
                        $severity = 'WARNING';
                    } else {
                        $severity = 'CRITICAL';
                    }
                } elseif ($e instanceof \Illuminate\Database\QueryException) {
                    $severity = 'CRITICAL';
                }

                $id = 'ERR-' . date('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(4));
                $newError = [
                    'id' => $id,
                    'severity' => $severity,
                    'time' => date('Y-m-d H:i:s'),
                    'service' => 'Laravel Application',
                    'environment' => config('app.env', 'Production'),
                    'http_status' => $httpStatus,
                    'message' => $e->getMessage() ?: get_class($e),
                    'endpoint' => request()->getRequestUri() ?: 'Console / Background Job',
                    'user' => $userName,
                    'device' => request()->userAgent() ?: 'Unknown Device',
                    'request_id' => 'req_' . \Illuminate\Support\Str::random(12),
                    'exception' => get_class($e),
                    'occurrences' => 1,
                    'first_seen' => date('Y-m-d H:i:s'),
                    'last_seen' => date('Y-m-d H:i:s'),
                    'status' => 'OPEN',
                    'stack_trace' => substr($e->getTraceAsString(), 0, 1000),
                ];

                // Cari apakah error serupa sudah terdaftar (berdasarkan kelas exception, pesan, dan endpoint)
                $found = false;
                foreach ($errors as &$err) {
                    if ($err['exception'] === $newError['exception'] && 
                        $err['message'] === $newError['message'] && 
                        $err['endpoint'] === $newError['endpoint']) {
                        $err['occurrences']++;
                        $err['last_seen'] = $newError['last_seen'];
                        $err['time'] = $newError['time'];
                        $found = true;
                        break;
                    }
                }
                
                if (!$found) {
                    array_unshift($errors, $newError);
                }

                file_put_contents($logPath, json_encode($errors, JSON_PRETTY_PRINT));
            } catch (\Exception $ex) {
                // Mencegah looping crash jika proses logging sendiri yang bermasalah
            }
        });
    })->create();
